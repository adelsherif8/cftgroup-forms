<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class CFTG_GHL_API {

    private string $api_key;
    private string $location_id;
    private string $base    = 'https://services.leadconnectorhq.com/';
    private string $version = '2021-07-28';

    public function __construct() {
        $this->api_key     = get_option( 'cftg_ghl_api_key', '' );
        $this->location_id = get_option( 'cftg_ghl_location_id', '' );
    }

    /* ── Test connection by fetching location info ── */
    public function test_connection(): array {
        if ( empty( $this->api_key ) || empty( $this->location_id ) ) {
            return [ 'success' => false, 'message' => 'API key and Location ID are required.' ];
        }

        $response = wp_remote_get(
            $this->base . 'locations/' . rawurlencode( $this->location_id ),
            [
                'headers' => [ 'Authorization' => 'Bearer ' . $this->api_key, 'Version' => $this->version ],
                'timeout' => 15,
            ]
        );

        if ( is_wp_error( $response ) ) {
            return [ 'success' => false, 'message' => $response->get_error_message() ];
        }

        $code = wp_remote_retrieve_response_code( $response );
        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( $code === 200 ) {
            $name = $body['location']['name'] ?? 'Unknown';
            return [ 'success' => true, 'message' => "Connected to location: <strong>{$name}</strong>" ];
        }

        $err = $body['message'] ?? $body['msg'] ?? 'Unexpected error.';
        return [ 'success' => false, 'message' => "GHL error ({$code}): {$err}" ];
    }

    /* ── Upsert a contact (create or update by email) ── */
    public function upsert_contact( array $payload ): array {
        $payload['locationId'] = $this->location_id;

        $response = wp_remote_post(
            $this->base . 'contacts/upsert',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->api_key,
                    'Content-Type'  => 'application/json',
                    'Version'       => $this->version,
                ],
                'body'    => wp_json_encode( $payload ),
                'timeout' => 20,
            ]
        );

        if ( is_wp_error( $response ) ) {
            return [ 'success' => false, 'message' => $response->get_error_message() ];
        }

        $code = wp_remote_retrieve_response_code( $response );
        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( in_array( $code, [ 200, 201 ], true ) ) {
            return [ 'success' => true, 'contact_id' => $body['contact']['id'] ?? '' ];
        }

        $err = $body['message'] ?? $body['msg'] ?? 'Unknown GHL error.';
        return [ 'success' => false, 'message' => "GHL error ({$code}): {$err}" ];
    }

    /* ── Build custom fields array from option IDs or field keys ──
       The admin row accepts either a UUID or a field key like
       "contact.load_size" / "{{contact.load_size}}". We detect which
       and send GHL the matching property (`id` for UUID, `key` for key). */
    public static function build_custom_fields( array $map ): array {
        $fields = [];
        foreach ( $map as $option_key => $value ) {
            $stored = trim( get_option( $option_key, '' ) );
            if ( $stored === '' || $value === '' || $value === null ) continue;

            $entry = [ 'value' => sanitize_text_field( (string) $value ) ];

            /* If it contains a dot or merge-tag braces, treat as field key */
            if ( strpos( $stored, '.' ) !== false || strpos( $stored, '{{' ) !== false ) {
                $key = trim( str_replace( [ '{{', '}}' ], '', $stored ) );
                $entry['key'] = sanitize_text_field( $key );
            } else {
                $entry['id']  = sanitize_text_field( $stored );
            }
            $fields[] = $entry;
        }
        return $fields;
    }
}
