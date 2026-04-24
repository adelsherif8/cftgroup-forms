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

    /* ── Spam checks: honeypot + timing + rate limit ── */
    private function spam_check(): void {
        /* 1. Honeypot — bots fill this, humans don't */
        if ( ! empty( $_POST['website'] ) ) {
            wp_send_json_error( [ 'message' => 'Spam detected.' ], 403 );
        }

        /* 2. Timing — must be at least 3 seconds after page load */
        $loaded_at = intval( $_POST['loaded_at'] ?? 0 );
        if ( $loaded_at > 0 && ( time() - $loaded_at ) < 3 ) {
            wp_send_json_error( [ 'message' => 'Please slow down.' ], 429 );
        }

        /* 3. Rate limit — max 5 submissions per IP per hour */
        $ip  = $this->get_ip();
        $key = 'cftg_rl_' . md5( $ip );
        $count = (int) get_transient( $key );
        if ( $count >= 5 ) {
            wp_send_json_error( [ 'message' => 'Too many submissions. Please try again later.' ], 429 );
        }
        set_transient( $key, $count + 1, HOUR_IN_SECONDS );
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

        $ghl    = new CFTG_GHL_API();
        $result = $ghl->upsert_contact( $payload );

        if ( $result['success'] ) {
            wp_send_json_success( [ 'message' => 'Submitted successfully.' ] );
        } else {
            wp_send_json_error( [ 'message' => $result['message'] ?? 'Submission failed.' ] );
        }
    }

    /* ── Build hardcoded UTM custom field entries ── */
    private function utm_custom_fields( array $f ): array {
        $fields = [];
        $map = [
            'UTMContent_custom'  => $f['utm_content']  ?? '',
            'UTMCampaign_Custom' => $f['utm_campaign'] ?? '',
            'UTMmedium_custom'   => $f['utm_medium']   ?? '',
            'utm Keyword'        => $f['utm_term']      ?? '',
            'utm Content'        => $f['utm_content']  ?? '',
            'utm Campaign'       => $f['utm_campaign'] ?? '',
        ];
        foreach ( $map as $field_id => $value ) {
            if ( $value !== '' ) {
                $fields[] = [ 'id' => sanitize_text_field( $field_id ), 'value' => sanitize_text_field( $value ) ];
            }
        }
        return $fields;
    }

    /* ── Bin Estimate ── */
    private function build_bin_estimate(): array {
        $f = $this->clean( $_POST );
        $custom = array_merge(
            CFTG_GHL_API::build_custom_fields( [
                'cftg_cf_dispose_types' => $f['dispose_types'] ?? '',
                'cftg_cf_delivery_date' => $f['delivery_date'] ?? '',
                'cftg_cf_bin_duration'  => $f['bin_duration']  ?? '',
                'cftg_cf_bin_size'      => $f['bin_size']      ?? '',
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
        $custom = array_merge(
            CFTG_GHL_API::build_custom_fields( [
                'cftg_cf_scrap_types' => $f['scrap_types'] ?? '',
            ] ),
            $this->utm_custom_fields( $f )
        );
        return [
            'firstName'   => $f['first_name'] ?? '',
            'lastName'    => $f['last_name']  ?? '',
            'email'       => $f['email']      ?? '',
            'phone'       => $f['phone']      ?? '',
            'postalCode'  => $f['postal']     ?? '',
            'tags'        => [ 'CFT - Scrap Metal' ],
            'source'      => 'CFT Scrap Metal Form',
            'customFields' => $custom,
        ];
    }

    /* ── Vehicle Quote ── */
    private function build_vehicle_quote(): array {
        $f = $this->clean( $_POST );
        $custom = array_merge(
            CFTG_GHL_API::build_custom_fields( [
                'cftg_cf_vehicle_year'        => $f['vehicle_year']   ?? '',
                'cftg_cf_vehicle_make'        => $f['vehicle_make']   ?? '',
                'cftg_cf_vehicle_model'       => $f['vehicle_model']  ?? '',
                'cftg_cf_engine_running'      => $f['engine_running'] ?? '',
                'cftg_cf_parts_missing'       => $f['parts_missing']  ?? '',
                'cftg_cf_missing_parts_notes' => $f['whats_missing']  ?? '',
            ] ),
            $this->utm_custom_fields( $f )
        );
        return [
            'firstName'   => $f['first_name'] ?? '',
            'lastName'    => $f['last_name']  ?? '',
            'email'       => $f['email']      ?? '',
            'phone'       => $f['phone']      ?? '',
            'postalCode'  => $f['postal']     ?? '',
            'tags'        => [ 'CFT - Vehicle Quote' ],
            'source'      => 'CFT Vehicle Quote Form',
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

        $response = wp_remote_get(
            'https://services.leadconnectorhq.com/locations/' . rawurlencode( $location_id ) . '/customFields',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $api_key,
                    'Version'       => '2021-07-28',
                ],
                'timeout' => 15,
            ]
        );

        if ( is_wp_error( $response ) ) {
            wp_send_json_error( [ 'message' => $response->get_error_message() ] );
        }

        $code = wp_remote_retrieve_response_code( $response );
        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( $code !== 200 ) {
            wp_send_json_error( [ 'message' => $body['message'] ?? "HTTP $code" ] );
        }

        $fields = array_map( fn( $f ) => [
            'name' => $f['name']     ?? '',
            'key'  => $f['fieldKey'] ?? '',
            'id'   => $f['id']       ?? '',
        ], $body['customFields'] ?? [] );

        wp_send_json_success( [ 'fields' => $fields ] );
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
