<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class CFTG_Form_Handler {

    public function __construct() {
        add_action( 'wp_ajax_cftg_submit',            [ $this, 'handle_submit' ] );
        add_action( 'wp_ajax_nopriv_cftg_submit',     [ $this, 'handle_submit' ] );
        add_action( 'wp_ajax_cftg_test_connection',   [ $this, 'handle_test_connection' ] );
        add_action( 'wp_ajax_cftg_fetch_fields',      [ $this, 'handle_fetch_fields' ] );
        /* Always-fresh nonce — bypass page caching so the inline nonce
           printed into cached HTML can never go stale. */
        add_action( 'wp_ajax_cftg_get_nonce',         [ $this, 'handle_get_nonce' ] );
        add_action( 'wp_ajax_nopriv_cftg_get_nonce',  [ $this, 'handle_get_nonce' ] );
        /* Funnel tracking — fire-and-forget from forms.js */
        add_action( 'wp_ajax_cftg_track',             [ $this, 'handle_track' ] );
        add_action( 'wp_ajax_nopriv_cftg_track',      [ $this, 'handle_track' ] );
    }

    /* ── Record a funnel event (no nonce — public tracking) ── */
    public function handle_track(): void {
        nocache_headers();
        $session   = sanitize_text_field( $_POST['session']   ?? '' );
        $form_type = sanitize_key( $_POST['form_type']        ?? '' );
        $event     = sanitize_key( $_POST['event']            ?? '' );
        $step      = intval( $_POST['step']                   ?? 0 );
        $page_url  = esc_url_raw( $_POST['page_url']          ?? '' );
        $ua        = sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ?? '' );

        if ( $session && $form_type && in_array( $event, [ 'view', 'step', 'submit' ], true ) ) {
            CFTG_Funnel::track( $session, $form_type, $event, $step, $page_url, $this->get_ip(), $ua );
        }
        wp_send_json_success();
    }

    /* ── Return a fresh submission nonce ── */
    public function handle_get_nonce(): void {
        nocache_headers();
        wp_send_json_success( [ 'nonce' => wp_create_nonce( 'cftg_submit' ) ] );
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
        $shared_keys = [ 'utmcampaign_custom','utmmedium_custom','utmcontent_custom','utmkeyword_custom','utmterm_custom','gclid_custom' ];
        $by_type = [
            'bin_estimate'  => [ 'dispose_types','delivery_date','bin_duration','bin_size' ],
            'scrap_metal'   => [ 'scrap_types','load_size','exact_weight','exact_weight_unit' ],
            'vehicle_quote' => [ 'vehicle_year','vehicle_make','vehicle_model','engine_running','parts_missing','whats_missing' ],
        ];
        $out = [];
        foreach ( array_merge( $by_type[ $type ] ?? [], $shared_keys ) as $k ) {
            if ( ! empty( $f[ $k ] ) ) $out[ $k ] = $f[ $k ];
        }
        return $out;
    }

    /* ── UTM + GCLID fields (new canonical _custom schema) ── */
    private function utm_custom_fields( array $f ): array {
        return CFTG_GHL_API::build_custom_fields( [
            'cftg_cf_utmcampaign_custom' => $f['utmcampaign_custom'] ?? '',
            'cftg_cf_utmmedium_custom'   => $f['utmmedium_custom']   ?? '',
            'cftg_cf_utmcontent_custom'  => $f['utmcontent_custom']  ?? '',
            'cftg_cf_utmkeyword_custom'  => $f['utmkeyword_custom']  ?? '',
            'cftg_cf_utmterm_custom'     => $f['utmterm_custom']     ?? '',
            'cftg_cf_gclid_custom'       => $f['gclid_custom']       ?? '',
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
                'cftg_cf_source_cft'          => 'Bin Estimate Form',
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
                'cftg_cf_source_cft'            => 'Metal Estimate Form',
            ] ),
            $this->utm_custom_fields( $f )
        );
        return [
            'firstName'    => $f['first_name'] ?? '',
            'lastName'     => $f['last_name']  ?? '',
            'email'        => $f['email']      ?? '',
            'phone'        => $f['phone']      ?? '',
            'postalCode'   => $f['postal']     ?? '',
            'tags'         => [ 'CFT - Scrap Metal' ],
            'source'       => 'CFT Scrap Metal Form',
            'customFields' => $custom,
        ];
    }

    /* ── Vehicle Quote ── */
    private function build_vehicle_quote(): array {
        $f = $this->clean( $_POST );
        $custom = array_merge(
            CFTG_GHL_API::build_custom_fields( [
                'cftg_cf_vehicle_year'          => $f['vehicle_year']   ?? '',
                'cftg_cf_vehicle_make'          => $f['vehicle_make']   ?? '',
                'cftg_cf_vehicle_model'         => $f['vehicle_model']  ?? '',
                'cftg_cf_engine_running'        => $f['engine_running'] ?? '',
                'cftg_cf_parts_missing'         => $f['parts_missing']  ?? '',
                'cftg_cf_missing_parts_notes'   => $f['whats_missing']  ?? '',
                'cftg_cf_vehicle_pickup_postal' => $f['postal']         ?? '',
                'cftg_cf_source_cft'            => 'Vehicle Estimate Form',
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
