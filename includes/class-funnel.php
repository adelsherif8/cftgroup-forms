<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class CFTG_Funnel {

    /* 1.1 adds page_id + the composite indexes the reports read by. Bumping
       this re-runs dbDelta on upgrade, so existing sites get the column
       without reactivating the plugin. */
    const DB_VERSION = '1.1';

    /* Events older than this are deleted. 18 months, not 12, so a full
       365-day lookback never loses its oldest edge and a year-over-year
       comparison stays possible. */
    const KEEP_MONTHS = 18;
    /* Deleted a slice at a time: this runs on shared hosting, where one big
       DELETE over a long table risks a lock or a timeout. */
    const PRUNE_BATCH       = 2000;
    const PRUNE_MAX_BATCHES = 5;
    const OPTION_KEY = 'cftg_funnel_db_version';

    public static function table_name(): string {
        global $wpdb;
        return $wpdb->prefix . 'cftg_funnel';
    }

    /* ── Create / upgrade table ── */
    public static function maybe_create_table(): void {
        if ( get_option( self::OPTION_KEY ) === self::DB_VERSION ) return;

        global $wpdb;
        $table   = self::table_name();
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            session_id VARCHAR(40) NOT NULL DEFAULT '',
            form_type VARCHAR(32) NOT NULL DEFAULT '',
            event VARCHAR(20) NOT NULL DEFAULT '',
            step_num SMALLINT NOT NULL DEFAULT 0,
            page_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
            page_url VARCHAR(500) NOT NULL DEFAULT '',
            ip VARCHAR(45) NOT NULL DEFAULT '',
            ua VARCHAR(255) NOT NULL DEFAULT '',
            created_at DATETIME NOT NULL,
            PRIMARY KEY  (id),
            KEY form_type (form_type),
            KEY session_id (session_id),
            KEY event (event),
            KEY created_at (created_at),
            KEY form_event_time (form_type,event,created_at),
            KEY page_event_time (page_id,event,created_at)
        ) $charset;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );

        update_option( self::OPTION_KEY, self::DB_VERSION );
    }

    /* ── Record an event. De-dupes view/step events per session ── */
    public static function track( string $session_id, string $form_type, string $event, int $step_num = 0, string $page_url = '', string $ip = '', string $ua = '', int $page_id = 0 ): void {
        if ( $session_id === '' || $form_type === '' || $event === '' ) return;

        global $wpdb;
        $table = self::table_name();

        /* Skip duplicate view/start/step events for same session — only count
           the first reach. Calls are never de-duped: two phone taps are two
           phone taps. */
        if ( in_array( $event, [ 'view', 'start', 'step' ], true ) ) {
            $exists = $wpdb->get_var( $wpdb->prepare(
                "SELECT id FROM $table WHERE session_id=%s AND form_type=%s AND event=%s AND step_num=%d LIMIT 1",
                $session_id, $form_type, $event, $step_num
            ) );
            if ( $exists ) return;
        }

        $wpdb->insert( $table, [
            'session_id' => substr( $session_id, 0, 40 ),
            'form_type'  => substr( $form_type, 0, 32 ),
            'event'      => substr( $event, 0, 20 ),
            'step_num'   => $step_num,
            'page_id'    => $page_id,
            'page_url'   => substr( $page_url, 0, 500 ),
            /* IP and user agent are deliberately not stored. Nothing in the
               plugin ever read them, so they were personal data collected for
               no purpose. The columns stay for now so no migration is needed;
               $ip and $ua are accepted and ignored. Submissions still record
               an IP in the entries table, where spam and fraud checks use it. */
            'ip'         => '',
            'ua'         => '',
            'created_at' => current_time( 'mysql' ),
        ], [ '%s','%s','%s','%d','%d','%s','%s','%s','%s' ] );
    }

    /* ── Funnel summary for a form type within a date range ──
       Returns: [ 'views' => N, 'steps' => [ 1 => N, 2 => N, ... ], 'submits' => N ] */
    public static function summary( string $form_type, string $start, string $end, string $page_url = '' ): array {
        global $wpdb;
        $table = self::table_name();

        $where = $wpdb->prepare( "form_type=%s AND created_at BETWEEN %s AND %s", $form_type, $start, $end );
        if ( $page_url !== '' ) {
            $where .= $wpdb->prepare( ' AND page_url=%s', $page_url );
        }

        $views = (int) $wpdb->get_var( "SELECT COUNT(DISTINCT session_id) FROM $table WHERE $where AND event='view'" );

        $step_rows = $wpdb->get_results( "SELECT step_num, COUNT(DISTINCT session_id) c FROM $table WHERE $where AND event='step' GROUP BY step_num ORDER BY step_num", ARRAY_A );
        $steps = [];
        foreach ( $step_rows ?: [] as $r ) $steps[ intval( $r['step_num'] ) ] = intval( $r['c'] );

        $submits = (int) $wpdb->get_var( "SELECT COUNT(DISTINCT session_id) FROM $table WHERE $where AND event='submit'" );

        return [ 'views' => $views, 'steps' => $steps, 'submits' => $submits ];
    }

    /* ── Drop-off summary — for each session that did NOT submit, find
       the last step they reached. Returns [ step_num => sessions_left ]. */
    public static function dropoffs( string $form_type, string $start, string $end, string $page_url = '' ): array {
        global $wpdb;
        $table = self::table_name();

        $page_clause = $page_url !== '' ? $wpdb->prepare( ' AND page_url=%s', $page_url ) : '';

        $rows = $wpdb->get_results( $wpdb->prepare(
            "SELECT MAX(step_num) AS last_step
             FROM $table
             WHERE form_type=%s AND event='step' AND created_at BETWEEN %s AND %s $page_clause
               AND session_id NOT IN (
                 SELECT DISTINCT session_id FROM $table
                 WHERE form_type=%s AND event='submit' AND created_at BETWEEN %s AND %s $page_clause
               )
             GROUP BY session_id",
            $form_type, $start, $end, $form_type, $start, $end
        ), ARRAY_A );

        $out = [];
        foreach ( $rows ?: [] as $r ) {
            $s = intval( $r['last_step'] );
            $out[ $s ] = ( $out[ $s ] ?? 0 ) + 1;
        }
        ksort( $out );
        return $out;
    }

    /* Visitors who actually engaged with the form, not just loaded the page. */
    public static function starts( string $form_type, string $start, string $end, string $page_url = '' ): int {
        global $wpdb;
        $table = self::table_name();
        $where = $wpdb->prepare( "form_type=%s AND created_at BETWEEN %s AND %s", $form_type, $start, $end );
        if ( $page_url !== '' ) $where .= $wpdb->prepare( ' AND page_url=%s', $page_url );
        return (int) $wpdb->get_var( "SELECT COUNT(DISTINCT session_id) FROM $table WHERE $where AND event='start'" );
    }

    /* ── Retention ──
       Deletes events past KEEP_MONTHS, oldest first, a batch at a time. Runs
       daily on WP-Cron; whatever is left over is picked up tomorrow. Returns
       how many rows went. ── */
    public static function prune_old_events(): int {
        global $wpdb;
        $table  = self::table_name();
        $cutoff = date( 'Y-m-d H:i:s', strtotime( '-' . self::KEEP_MONTHS . ' months', current_time( 'timestamp' ) ) );
        $gone   = 0;

        for ( $i = 0; $i < self::PRUNE_MAX_BATCHES; $i++ ) {
            /* Select then delete by id: portable, and it never locks more than
               the batch. DELETE ... ORDER BY ... LIMIT is MySQL-only. */
            $ids = $wpdb->get_col( $wpdb->prepare(
                "SELECT id FROM $table WHERE created_at < %s ORDER BY id ASC LIMIT %d",
                $cutoff, self::PRUNE_BATCH
            ) );
            if ( empty( $ids ) ) break;

            $ids = array_map( 'intval', $ids );
            $wpdb->query( "DELETE FROM $table WHERE id IN (" . implode( ',', $ids ) . ")" );
            $gone += count( $ids );

            if ( count( $ids ) < self::PRUNE_BATCH ) break;
        }
        return $gone;
    }

    /* Daily prune, scheduled once and left alone. */
    public static function schedule_prune(): void {
        if ( ! wp_next_scheduled( 'cftg_funnel_prune' ) ) {
            wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'cftg_funnel_prune' );
        }
    }

    public static function unschedule_prune(): void {
        wp_clear_scheduled_hook( 'cftg_funnel_prune' );
    }

    /* ── Phone clicks ──
       Every tap on a tel: link, which is how most mobile visitors actually
       get in touch. Not de-duped, so this is taps, not people. ── */
    public static function calls_total( string $start, string $end ): int {
        global $wpdb;
        $table = self::table_name();
        return (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE event='call' AND created_at BETWEEN %s AND %s",
            $start, $end
        ) );
    }

    public static function calls_people( string $start, string $end ): int {
        global $wpdb;
        $table = self::table_name();
        return (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(DISTINCT session_id) FROM $table WHERE event='call' AND created_at BETWEEN %s AND %s",
            $start, $end
        ) );
    }

    /* Pages that produced the most phone clicks. */
    public static function calls_by_page( string $start, string $end, int $limit = 10 ): array {
        global $wpdb;
        $table = self::table_name();
        $rows = $wpdb->get_results( $wpdb->prepare(
            "SELECT page_url, COUNT(*) AS taps, COUNT(DISTINCT session_id) AS people
             FROM $table
             WHERE event='call' AND created_at BETWEEN %s AND %s
             GROUP BY page_url
             ORDER BY taps DESC
             LIMIT %d",
            $start, $end, $limit
        ), ARRAY_A );
        return $rows ?: [];
    }

    /* ── List distinct page URLs that have seen events. Empty form_type
       means "across all forms". ── */
    public static function distinct_pages( string $form_type, string $start, string $end ): array {
        global $wpdb;
        $table = self::table_name();
        if ( $form_type !== '' ) {
            $rows = $wpdb->get_col( $wpdb->prepare(
                "SELECT DISTINCT page_url FROM $table WHERE form_type=%s AND created_at BETWEEN %s AND %s AND page_url<>'' ORDER BY page_url",
                $form_type, $start, $end
            ) );
        } else {
            $rows = $wpdb->get_col( $wpdb->prepare(
                "SELECT DISTINCT page_url FROM $table WHERE created_at BETWEEN %s AND %s AND page_url<>'' ORDER BY page_url",
                $start, $end
            ) );
        }
        return $rows ?: [];
    }
}
