<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class CFTG_Form_Handler {

    public function __construct() {
        add_action( 'wp_ajax_cftg_submit',            [ $this, 'handle_submit' ] );
        add_action( 'wp_ajax_nopriv_cftg_submit',     [ $this, 'handle_submit' ] );
        add_action( 'wp_ajax_cftg_test_connection',   [ $this, 'handle_test_connection' ] );
        add_action( 'wp_ajax_cftg_fetch_fields',      [ $this, 'handle_fetch_fields' ] );
    }

    /* ── Nonce check ── */
    private function verify(): void {
        if ( ! check_ajax_referer( 'cftg_submit', 'nonce', false ) ) {
            wp_send_json_error( [ 'message' => 'Security check failed.' ], 403 );
        }
    }

    /* ── Spam checks: honeypot + timing.
       Logged-in admins bypass everything so they can test freely. */
    private function spam_check(): void {
        if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) return;

        /* 1. Honeypot — bots fill this, humans don't */
        if ( ! empty( $_POST['website'] ) ) {
            wp_send_json_error( [ 'message' => 'Spam detected.' ], 403 );
        }

        /* 2. Timing — must be at least 3 seconds after page load */
        $loaded_at = intval( $_POST['loaded_at'] ?? 0 );
        if ( $loaded_at > 0 && ( time() - $loaded_at ) < 3 ) {
            wp_send_json_error( [ 'message' => 'Please slow down.' ], 429 );
        }
    }

    private function get_ip(): string {
        foreach ( [ 'HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' ] as $key ) {
            if ( ! empty( $_SERVER[ $key ] ) ) {
                return sanitize_text_field( explode( ',', $_SERVER[ $key ] )[0] );
            }
        }
        return '0.0.0.0';
    }

    /* ── Test GHL connection (admin only) ── */
    public function handle_test_connection(): void {
        if ( ! check_ajax_referer( 'cftg_admin', 'nonce', false ) || ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Unauthorized.' ], 403 );
        }
        $tmp_key = sanitize_text_field( $_POST['api_key'] ?? '' );
        $tmp_loc = sanitize_text_field( $_POST['location_id'] ?? '' );
        update_option( 'cftg_ghl_api_key',     $tmp_key );
        update_option( 'cftg_ghl_location_id', $tmp_loc );
        $ghl    = new CFTG_GHL_API();
        $result = $ghl->test_connection();
        if ( $result['success'] ) {
            wp_send_json_success( $result );
        } else {
            wp_send_json_error( $result );
        }
    }

    /* ── Route form submission ── */
    public function handle_submit(): void {
        $this->verify();
        $this->spam_check();

        $type = sanitize_key( $_POST['form_type'] ?? '' );

        $payload = match ( $type ) {
            'bin_estimate'  => $this->build_bin_estimate(),
            'scrap_metal'   => $this->build_scrap_metal(),
            'vehicle_quote' => $this->build_vehicle_quote(),
            default         => null,
        };

        if ( ! $payload ) {
            wp_send_json_error( [ 'message' => 'Unknown form type.' ] );
        }

        /* Save entry locally before calling GHL so we never lose a lead */
        $form_data = $this->entry_data_from_post( $type );
        $entry_id  = CFTG_Entries::insert( [
            'form_type'  => $type,
            'first_name' => $payload['firstName']  ?? '',
            'last_name'  => $payload['lastName']   ?? '',
            'email'      => $payload['email']      ?? '',
            'phone'      => $payload['phone']      ?? '',
            'postal'     => $payload['postalCode'] ?? '',
            'tag'        => $payload['tags'][0]    ?? '',
            'data'       => $form_data,
            'ip'         => $this->get_ip(),
        ] );

        $ghl    = new CFTG_GHL_API();
        $result = $ghl->upsert_contact( $payload );

        /* Store the full GHL request + response in the entry's data so we
           can see exactly what was sent and what GHL replied with. */
        $form_data['_ghl_request']  = $result['request']  ?? '';
        $form_data['_ghl_response'] = $result['response'] ?? '';
        $form_data['_ghl_http']     = $result['http']     ?? 0;
        global $wpdb;
        $wpdb->update(
            CFTG_Entries::table_name(),
            [ 'data' => wp_json_encode( $form_data ) ],
            [ 'id' => $entry_id ],
            [ '%s' ],
            [ '%d' ]
        );

        CFTG_Entries::update_ghl_status(
            $entry_id,
            $result['success'] ? 'sent' : 'failed',
            $result['message'] ?? ''
        );

        if ( $result['success'] ) {
            wp_send_json_success( [ 'message' => 'Submitted successfully.' ] );
        } else {
            wp_send_json_error( [ 'message' => $result['message'] ?? 'Submission failed.' ] );
        }
    }

    /* ── Per-form-type data snapshot for the entries log ── */
    private function entry_data_from_post( string $type ): array {
        $f = $this->clean( $_POST );
        $shared_keys = [ 'utm_source','utm_medium','utm_campaign','utm_term','utm_content','gclid' ];
        $by_type = [
            'bin_estimate'  => [ 'dispose_types','delivery_date','bin_duration','bin_size' ],
            'scrap_metal'   => [ 'scrap_types','load_size','exact_weight','exact_weight_unit','city' ],
            'vehicle_quote' => [ 'vehicle_year','vehicle_make','vehicle_model','mileage','engine_running','catalytic_converter','parts_missing','whats_missing' ],
        ];
        $out = [];
        foreach ( array_merge( $by_type[ $type ] ?? [], $shared_keys ) as $k ) {
            if ( ! empty( $f[ $k ] ) ) $out[ $k ] = $f[ $k ];
        }
        return $out;
    }

    /* ── UTM + GCLID fields via saved option UUIDs ── */
    private function utm_custom_fields( array $f ): array {
        return CFTG_GHL_API::build_custom_fields( [
            'cftg_cf_utm_medium'       => $f['utm_medium']   ?? '',
            'cftg_cf_utm_campaign'     => $f['utm_campaign'] ?? '',
            'cftg_cf_utm_content'      => $f['utm_content']  ?? '',
            'cftg_cf_utm_keyword'      => $f['utm_term']     ?? '',
            'cftg_cf_utm_content_std'  => $f['utm_content']  ?? '',
            'cftg_cf_utm_campaign_std' => $f['utm_campaign'] ?? '',
            'cftg_cf_gclid'            => $f['gclid']        ?? '',
        ] );
    }

    /* ── Bin Estimate ── */
    private function build_bin_estimate(): array {
        $f = $this->clean( $_POST );
        $custom = array_merge(
            CFTG_GHL_API::build_custom_fields( [
                'cftg_cf_dispose_types'       => $f['dispose_types'] ?? '',
                'cftg_cf_delivery_date'       => $f['delivery_date'] ?? '',
                'cftg_cf_bin_duration'        => $f['bin_duration']  ?? '',
                'cftg_cf_bin_size'            => $f['bin_size']      ?? '',
                'cftg_cf_bin_delivery_postal' => $f['postal']        ?? '',
            ] ),
            $this->utm_custom_fields( $f )
        );
        return [
            'firstName'   => $f['first_name'] ?? '',
            'lastName'    => $f['last_name']  ?? '',
            'email'       => $f['email']      ?? '',
            'phone'       => $f['phone']      ?? '',
            'postalCode'  => $f['postal']     ?? '',
            'tags'        => [ 'CFT - Bin Estimate' ],
            'source'      => 'CFT Bin Estimate Form',
            'customFields' => $custom,
        ];
    }

    /* ── Scrap Metal ── */
    private function build_scrap_metal(): array {
        $f = $this->clean( $_POST );

        /* Compose "850 lbs" from the number + unit (empty if no number) */
        $weight_num = trim( $f['exact_weight']      ?? '' );
        $weight_unit = trim( $f['exact_weight_unit'] ?? 'lbs' );
        $exact_weight = $weight_num !== '' ? "{$weight_num} {$weight_unit}" : '';

        $custom = array_merge(
            CFTG_GHL_API::build_custom_fields( [
                'cftg_cf_scrap_types'           => $f['scrap_types'] ?? '',
                'cftg_cf_load_size'             => $f['load_size']   ?? '',
                'cftg_cf_exact_weight'          => $exact_weight,
                'cftg_cf_scrap_postal'          => $f['postal']      ?? '',
                /* Workflows use the same "pick-up postal" field for both
                   vehicle and scrap, so also write postal there. */
                'cftg_cf_vehicle_pickup_postal' => $f['postal']      ?? '',
            ] ),
            $this->utm_custom_fields( $f )
        );
        return [
            'firstName'    => $f['first_name'] ?? '',
            'lastName'     => $f['last_name']  ?? '',
            'email'        => $f['email']      ?? '',
            'phone'        => $f['phone']      ?? '',
            'city'         => $f['city']       ?? '',
            'postalCode'   => $f['postal']     ?? '',
            'tags'         => [ 'CFT - Scrap Metal' ],
            'source'       => 'CFT Scrap Metal Form',
            'customFields' => $custom,
        ];
    }

    /* ── Vehicle Quote ── */
    private function build_vehicle_quote(): array {
        $f = $this->clean( $_POST );

        /* Combine year + make + model into one string for the
           "What is the Make, Model, Year?" GHL field */
        $year  = trim( $f['vehicle_year']  ?? '' );
        $make  = trim( $f['vehicle_make']  ?? '' );
        $model = trim( $f['vehicle_model'] ?? '' );
        $make_model_year = trim( "{$year} {$make} {$model}" );

        $custom = array_merge(
            CFTG_GHL_API::build_custom_fields( [
                'cftg_cf_vehicle_year'          => $year,
                'cftg_cf_vehicle_make'          => $make,
                'cftg_cf_vehicle_model'         => $model,
                'cftg_cf_make_model_year'       => $make_model_year,
                'cftg_cf_mileage'               => $f['mileage']             ?? '',
                'cftg_cf_engine_running'        => $f['engine_running']      ?? '',
                'cftg_cf_catalytic_converter'   => $f['catalytic_converter'] ?? '',
                'cftg_cf_parts_missing'         => $f['parts_missing']       ?? '',
                'cftg_cf_missing_parts_notes'   => $f['whats_missing']       ?? '',
                'cftg_cf_vehicle_pickup_postal' => $f['postal']              ?? '',
            ] ),
            $this->utm_custom_fields( $f )
        );
        return [
            'firstName'    => $f['first_name'] ?? '',
            'lastName'     => $f['last_name']  ?? '',
            'email'        => $f['email']      ?? '',
            'phone'        => $f['phone']      ?? '',
            'postalCode'   => $f['postal']     ?? '',
            'tags'         => [ 'CFT - Vehicle Quote' ],
            'source'       => 'CFT Vehicle Quote Form',
            'customFields' => $custom,
        ];
    }

    /* ── Fetch GHL custom fields (admin only) ── */
    public function handle_fetch_fields(): void {
        if ( ! check_ajax_referer( 'cftg_admin', 'nonce', false ) || ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Unauthorized.' ], 403 );
        }

        $api_key     = get_option( 'cftg_ghl_api_key', '' );
        $location_id = get_option( 'cftg_ghl_location_id', '' );

        /* Hit BOTH the contact-filtered endpoint AND the unfiltered one,
           then merge results deduped by id. Some GHL accounts have new
           fields that the model filter drops. */
        $headers = [
            'Authorization' => 'Bearer ' . $api_key,
            'Version'       => '2021-07-28',
        ];
        $base    = 'https://services.leadconnectorhq.com/locations/' . rawurlencode( $location_id ) . '/customFields';

        $merged = [];
        $debug  = [];
        foreach ( [ '?model=contact', '' ] as $qs ) {
            $resp = wp_remote_get( $base . $qs, [ 'headers' => $headers, 'timeout' => 20 ] );
            if ( is_wp_error( $resp ) ) continue;
            $code = wp_remote_retrieve_response_code( $resp );
            $body = json_decode( wp_remote_retrieve_body( $resp ), true );
            $fields = $body['customFields'] ?? [];
            $debug[] = ( $qs ?: '(no filter)' ) . ' → ' . count( $fields ) . ' fields, HTTP ' . $code;
            if ( $code === 200 ) {
                foreach ( $fields as $f ) {
                    $id = $f['id'] ?? '';
                    if ( $id && ! isset( $merged[ $id ] ) ) $merged[ $id ] = $f;
                }
            }
        }

        if ( empty( $merged ) ) {
            wp_send_json_error( [ 'message' => 'GHL returned no fields. ' . implode( ' | ', $debug ) ] );
        }

        $code = 200;
        $body = [ 'customFields' => array_values( $merged ) ];

        $fields = array_map( fn( $f ) => [
            'name' => $f['name']     ?? '',
            'key'  => $f['fieldKey'] ?? '',
            'id'   => $f['id']       ?? '',
        ], $body['customFields'] ?? [] );

        wp_send_json_success( [ 'fields' => $fields, 'debug' => $debug ] );
    }

    /* ── Sanitize POST fields ── */
    private function clean( array $data ): array {
        $out = [];
        foreach ( $data as $k => $v ) {
            $out[ sanitize_key( $k ) ] = is_array( $v )
                ? implode( ', ', array_map( 'sanitize_text_field', $v ) )
                : sanitize_text_field( $v );
        }
        return $out;
    }
}

new CFTG_Form_Handler();
