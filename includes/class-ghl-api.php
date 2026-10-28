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
        $raw_body              = wp_json_encode( $payload );

        $response = wp_remote_post(
            $this->base . 'contacts/upsert',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->api_key,
                    'Content-Type'  => 'application/json',
                    'Version'       => $this->version,
                ],
                'body'    => $raw_body,
                'timeout' => 20,
            ]
        );

        if ( is_wp_error( $response ) ) {
            return [
                'success'  => false,
                'message'  => $response->get_error_message(),
                'request'  => $raw_body,
                'response' => '',
                'http'     => 0,
            ];
        }

        $code     = wp_remote_retrieve_response_code( $response );
        $raw_resp = wp_remote_retrieve_body( $response );
        $body     = json_decode( $raw_resp, true );

        $base_return = [
            'request'  => $raw_body,
            'response' => $raw_resp,
            'http'     => $code,
        ];

        if ( in_array( $code, [ 200, 201 ], true ) ) {
            return array_merge( $base_return, [
                'success'    => true,
                'contact_id' => $body['contact']['id'] ?? '',
                'is_new'     => ! empty( $body['new'] ),
                /* Upsert matches on email/phone: an existing contact is updated
                   in place, which does not fire "contact created" workflows. */
                'message'    => empty( $body['new'] ) ? 'Updated existing contact' : 'Created new contact',
            ] );
        }

        $err = $body['message'] ?? $body['msg'] ?? 'Unknown GHL error.';
        return array_merge( $base_return, [
            'success' => false,
            'message' => "GHL error ({$code}): {$err}",
        ] );
    }

    /* ── Remove then re-add tags on an existing contact ──
       GHL lead workflows trigger on "tag added". A returning contact already
       has the form tag, so without this their new submission notifies nobody. */
    public function retag( string $contact_id, array $tags ): array {
        if ( $contact_id === '' || ! $tags ) {
            return [ 'success' => false, 'http' => 0 ];
        }
        $url  = $this->base . 'contacts/' . rawurlencode( $contact_id ) . '/tags';
        $args = [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type'  => 'application/json',
                'Version'       => $this->version,
            ],
            'body'    => wp_json_encode( [ 'tags' => array_values( $tags ) ] ),
            'timeout' => 15,
        ];
        wp_remote_request( $url, $args + [ 'method' => 'DELETE' ] );
        $response = wp_remote_post( $url, $args );
        $code     = is_wp_error( $response ) ? 0 : wp_remote_retrieve_response_code( $response );
        return [ 'success' => in_array( $code, [ 200, 201 ], true ), 'http' => $code ];
    }

    /* ── Add a plain-text note to a contact ──
       Custom fields only reach GHL once someone has created them there and
       pasted their IDs into the plugin settings; until then they are silently
       dropped. A note needs no mapping, so every answer is visible on the
       contact from the first submission. */
    public function add_note( string $contact_id, string $body ): array {
        if ( $contact_id === '' || trim( $body ) === '' ) {
            return [ 'success' => false, 'message' => 'No contact ID or empty note.', 'http' => 0 ];
        }

        $response = wp_remote_post(
            $this->base . 'contacts/' . rawurlencode( $contact_id ) . '/notes',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->api_key,
                    'Content-Type'  => 'application/json',
                    'Version'       => $this->version,
                ],
                'body'    => wp_json_encode( [ 'body' => $body ] ),
                'timeout' => 15,
            ]
        );

        if ( is_wp_error( $response ) ) {
            return [ 'success' => false, 'message' => $response->get_error_message(), 'http' => 0 ];
        }

        $code = wp_remote_retrieve_response_code( $response );
        return [
            'success' => in_array( $code, [ 200, 201 ], true ),
            'message' => in_array( $code, [ 200, 201 ], true ) ? 'OK' : wp_remote_retrieve_body( $response ),
            'http'    => $code,
        ];
    }

    /* ── Build custom fields array from option IDs ──
       GHL v2 contact upsert only accepts { id, value } pairs.
       If the user pasted a key (contains a dot) instead of a UUID we
       silently skip that field rather than send a malformed entry —
       sending a key causes GHL to 400 the entire request. */
    public static function build_custom_fields( array $map ): array {
        $fields = [];
        foreach ( $map as $option_key => $value ) {
            $stored = trim( get_option( $option_key, '' ) );
            if ( $stored === '' || $value === '' || $value === null ) continue;

            /* Skip key-format entries — GHL only accepts UUIDs */
            if ( strpos( $stored, '.' ) !== false || strpos( $stored, '{{' ) !== false ) continue;

            $fields[] = [
                'id'    => sanitize_text_field( $stored ),
                'value' => sanitize_text_field( (string) $value ),
            ];
        }
        return $fields;
    }
}
