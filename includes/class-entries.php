<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class CFTG_Entries {

    const DB_VERSION  = '1.1';
    const OPTION_KEY  = 'cftg_entries_db_version';

    public static function table_name(): string {
        global $wpdb;
        return $wpdb->prefix . 'cftg_entries';
    }

    /* ── Create / upgrade table ── */
    public static function maybe_create_table(): void {
        if ( get_option( self::OPTION_KEY ) === self::DB_VERSION ) return;

        global $wpdb;
        $table   = self::table_name();
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            form_type VARCHAR(32) NOT NULL,
            first_name VARCHAR(100) NOT NULL DEFAULT '',
            last_name VARCHAR(100) NOT NULL DEFAULT '',
            email VARCHAR(150) NOT NULL DEFAULT '',
            phone VARCHAR(50) NOT NULL DEFAULT '',
            postal VARCHAR(20) NOT NULL DEFAULT '',
            tag VARCHAR(100) NOT NULL DEFAULT '',
            data LONGTEXT NOT NULL,
            ghl_status VARCHAR(20) NOT NULL DEFAULT 'pending',
            ghl_message TEXT,
            ip VARCHAR(45) NOT NULL DEFAULT '',
            created_at DATETIME NOT NULL,
            PRIMARY KEY  (id),
            KEY form_type (form_type),
            KEY created_at (created_at),
            KEY email (email)
        ) $charset;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );

        update_option( self::OPTION_KEY, self::DB_VERSION );
    }

    /* ── Insert a new entry ── */
    public static function insert( array $row ): int {
        global $wpdb;
        $wpdb->insert(
            self::table_name(),
            [
                'form_type'   => $row['form_type']   ?? '',
                'first_name'  => $row['first_name']  ?? '',
                'last_name'   => $row['last_name']   ?? '',
                'email'       => $row['email']       ?? '',
                'phone'       => $row['phone']       ?? '',
                'postal'      => $row['postal']      ?? '',
                'tag'         => $row['tag']         ?? '',
                'data'        => wp_json_encode( $row['data'] ?? [] ),
                'ghl_status'  => $row['ghl_status']  ?? 'pending',
                'ghl_message' => $row['ghl_message'] ?? '',
                'ip'          => $row['ip']          ?? '',
                'created_at'  => current_time( 'mysql' ),
            ],
            [ '%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s' ]
        );
        return (int) $wpdb->insert_id;
    }

    /* ── Update GHL status on an existing entry ── */
    public static function update_ghl_status( int $id, string $status, string $message = '' ): void {
        global $wpdb;
        $wpdb->update(
            self::table_name(),
            [ 'ghl_status' => $status, 'ghl_message' => $message ],
            [ 'id' => $id ],
            [ '%s', '%s' ],
            [ '%d' ]
        );
    }

    /* ── Fetch entries (paginated, optional filter) ── */
    public static function fetch( int $page = 1, int $per_page = 25, string $form_filter = '' ): array {
        global $wpdb;
        $table   = self::table_name();
        $offset  = max( 0, ( $page - 1 ) * $per_page );
        $where   = '';
        $args    = [];

        if ( $form_filter !== '' ) {
            $where = 'WHERE form_type = %s';
            $args[] = $form_filter;
        }

        $args_with_pagination = array_merge( $args, [ $per_page, $offset ] );
        $rows = $wpdb->get_results(
            $wpdb->prepare( "SELECT * FROM $table $where ORDER BY id DESC LIMIT %d OFFSET %d", $args_with_pagination ),
            ARRAY_A
        );
        $total = (int) $wpdb->get_var(
            $args ? $wpdb->prepare( "SELECT COUNT(*) FROM $table $where", $args )
                  : "SELECT COUNT(*) FROM $table"
        );
        return [ 'rows' => $rows ?: [], 'total' => $total ];
    }

    public static function get( int $id ): ?array {
        global $wpdb;
        $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . self::table_name() . " WHERE id = %d", $id ), ARRAY_A );
        return $row ?: null;
    }

    public static function delete( int $id ): bool {
        global $wpdb;
        return (bool) $wpdb->delete( self::table_name(), [ 'id' => $id ], [ '%d' ] );
    }
}
