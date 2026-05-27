<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* ── CSV export handler ── */
add_action( 'admin_post_cftg_export_entries', 'cftg_export_entries' );
function cftg_export_entries(): void {
    if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );
    check_admin_referer( 'cftg_export_entries' );

    global $wpdb;
    $form_filter = sanitize_key( $_GET['form_filter'] ?? '' );
    $table       = CFTG_Entries::table_name();
    $where       = $form_filter ? $wpdb->prepare( 'WHERE form_type = %s', $form_filter ) : '';
    $rows        = $wpdb->get_results( "SELECT * FROM $table $where ORDER BY id DESC", ARRAY_A );

    nocache_headers();
    header( 'Content-Type: text/csv; charset=utf-8' );
    header( 'Content-Disposition: attachment; filename=cftg-entries-' . date( 'Y-m-d' ) . '.csv' );

    $out = fopen( 'php://output', 'w' );
    fputcsv( $out, [ 'ID','Date','Form','First Name','Last Name','Email','Phone','Postal','Tag','GHL Status','GHL Message','IP','Data (JSON)' ] );
    foreach ( $rows as $r ) {
        fputcsv( $out, [
            $r['id'], $r['created_at'], $r['form_type'],
            $r['first_name'], $r['last_name'], $r['email'], $r['phone'], $r['postal'],
            $r['tag'], $r['ghl_status'], $r['ghl_message'], $r['ip'], $r['data'],
        ] );
    }
    fclose( $out );
    exit;
}

/* ── Delete handler ── */
add_action( 'admin_post_cftg_delete_entry', 'cftg_delete_entry' );
function cftg_delete_entry(): void {
    if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );
    $id = intval( $_GET['entry'] ?? 0 );
    check_admin_referer( 'cftg_delete_entry_' . $id );
    if ( $id ) CFTG_Entries::delete( $id );
    wp_safe_redirect( admin_url( 'admin.php?page=cftg-entries&deleted=1' ) );
    exit;
}

/* ── Form labels + colours ── */
function cftg_form_meta(): array {
    return [
        'bin_estimate'  => [ 'label' => 'Bin Estimate',  'color' => '#0ea5e9', 'bg' => '#e0f2fe' ],
        'scrap_metal'   => [ 'label' => 'Scrap Metal',   'color' => '#f59e0b', 'bg' => '#fef3c7' ],
        'vehicle_quote' => [ 'label' => 'Vehicle Quote', 'color' => '#10b981', 'bg' => '#d1fae5' ],
    ];
}

/* ── Page router: detail view or list view ── */
function cftg_render_entries_page(): void {
    $view_id = isset( $_GET['view'] ) ? intval( $_GET['view'] ) : 0;
    if ( $view_id && ( $entry = CFTG_Entries::get( $view_id ) ) ) {
        cftg_render_entry_detail( $entry );
        return;
    }
    cftg_render_entries_list();
}

/* ── List view ── */
function cftg_render_entries_list(): void {
    $form_filter = sanitize_key( $_GET['form_filter'] ?? '' );
    $page        = max( 1, intval( $_GET['paged'] ?? 1 ) );
    $per_page    = 25;
    $result      = CFTG_Entries::fetch( $page, $per_page, $form_filter );
    $rows        = $result['rows'];
    $total       = $result['total'];
    $total_pages = max( 1, (int) ceil( $total / $per_page ) );
    $forms       = cftg_form_meta();
    ?>
    <style>
        .cftg-entries-wrap { max-width: 1400px; }
        .cftg-entries-header {
            display: flex; align-items: center; justify-content: space-between;
            gap: 16px; flex-wrap: wrap; margin: 18px 0 22px;
            padding: 18px 22px; background: #fff;
            border: 1px solid #e5e7eb; border-radius: 10px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }
        .cftg-entries-stats { display: flex; gap: 20px; align-items: center; }
        .cftg-stat { display: flex; flex-direction: column; }
        .cftg-stat-num { font-size: 28px; font-weight: 800; color: #111827; line-height: 1; }
        .cftg-stat-label { font-size: 11px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.08em; font-weight: 600; margin-top: 4px; }
        .cftg-stat-divider { width: 1px; height: 36px; background: #e5e7eb; }
        .cftg-entries-controls { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
        .cftg-entries-controls select { min-width: 160px; height: 36px; }
        .cftg-entries-controls .button { height: 36px; display: inline-flex; align-items: center; gap: 6px; }

        .cftg-entries-table {
            background: #fff; border: 1px solid #e5e7eb; border-radius: 10px;
            overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }
        .cftg-entries-table table { width: 100%; border-collapse: collapse; }
        .cftg-entries-table thead th {
            background: #f9fafb; border-bottom: 1px solid #e5e7eb;
            font-size: 11px; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.06em; color: #6b7280; text-align: left;
            padding: 12px 14px; white-space: nowrap;
        }
        .cftg-entries-table tbody td {
            padding: 14px; border-bottom: 1px solid #f3f4f6;
            font-size: 13px; color: #111827; vertical-align: middle;
        }
        .cftg-entries-table tbody tr { cursor: pointer; transition: background 0.12s ease; }
        .cftg-entries-table tbody tr:hover { background: #f9fafb; }
        .cftg-entries-table tbody tr:last-child td { border-bottom: none; }
        .cftg-entries-table tbody tr.cftg-empty { cursor: default; }
        .cftg-entries-table tbody tr.cftg-empty:hover { background: #fff; }
        .cftg-entries-table tbody tr.cftg-empty td { text-align: center; padding: 50px 20px; color: #9ca3af; }

        .cftg-form-pill {
            display: inline-block; padding: 3px 10px; border-radius: 12px;
            font-size: 11px; font-weight: 700;
        }
        .cftg-tag-pill {
            display: inline-block; padding: 3px 10px; border-radius: 12px;
            font-size: 11px; font-weight: 600; background: #fef3c7; color: #92400e;
            border: 1px solid #fde68a;
        }
        .cftg-ghl-badge { display: inline-flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 600; }
        .cftg-ghl-sent   { color: #16a34a; }
        .cftg-ghl-failed { color: #dc2626; }
        .cftg-ghl-pending{ color: #9ca3af; }

        .cftg-row-name { font-weight: 600; }
        .cftg-row-email a { color: #2563eb; text-decoration: none; }
        .cftg-row-email a:hover { text-decoration: underline; }

        .cftg-delete-btn {
            color: #dc2626 !important; border-color: #fecaca !important;
            background: #fef2f2 !important;
        }
        .cftg-delete-btn:hover { background: #fee2e2 !important; }

        .cftg-pagination { padding: 14px 16px; border-top: 1px solid #e5e7eb; background: #f9fafb; }
        .cftg-pagination .page-numbers {
            display: inline-block; padding: 6px 12px; margin: 0 2px;
            background: #fff; border: 1px solid #d1d5db; border-radius: 6px;
            color: #374151; text-decoration: none; font-size: 13px;
        }
        .cftg-pagination .page-numbers.current { background: #2563eb; color: #fff; border-color: #2563eb; }
        .cftg-pagination .page-numbers:hover { background: #f3f4f6; }
    </style>

    <div class="wrap cftg-admin cftg-entries-wrap">
        <h1 class="cftg-admin-title">
            <img src="https://cftgroup.ca/wp-content/uploads/2024/09/cft-group-logo.png" alt="CFT Group" style="height:36px;vertical-align:middle;margin-right:10px">
            Form Entries
        </h1>

        <?php if ( isset( $_GET['deleted'] ) ): ?>
            <div class="notice notice-success is-dismissible" style="margin-top:10px"><p>Entry deleted.</p></div>
        <?php endif; ?>

        <div class="cftg-entries-header">
            <div class="cftg-entries-stats">
                <div class="cftg-stat">
                    <span class="cftg-stat-num"><?php echo intval( $total ); ?></span>
                    <span class="cftg-stat-label"><?php echo $form_filter ? esc_html( $forms[ $form_filter ]['label'] ?? 'Filtered' ) : 'Total'; ?> entries</span>
                </div>
                <?php if ( ! $form_filter ):
                    global $wpdb;
                    $table = CFTG_Entries::table_name();
                    $by_form = $wpdb->get_results( "SELECT form_type, COUNT(*) c FROM $table GROUP BY form_type", ARRAY_A );
                    foreach ( $by_form as $bf ):
                        $m = $forms[ $bf['form_type'] ] ?? null;
                        if ( ! $m ) continue; ?>
                        <div class="cftg-stat-divider"></div>
                        <div class="cftg-stat">
                            <span class="cftg-stat-num" style="color:<?php echo esc_attr( $m['color'] ); ?>"><?php echo intval( $bf['c'] ); ?></span>
                            <span class="cftg-stat-label"><?php echo esc_html( $m['label'] ); ?></span>
                        </div>
                <?php endforeach; endif; ?>
            </div>

            <div class="cftg-entries-controls">
                <form method="get" style="margin:0">
                    <input type="hidden" name="page" value="cftg-entries">
                    <select name="form_filter" onchange="this.form.submit()">
                        <option value="">All forms</option>
                        <?php foreach ( $forms as $k => $m ): ?>
                            <option value="<?php echo esc_attr( $k ); ?>" <?php selected( $form_filter, $k ); ?>><?php echo esc_html( $m['label'] ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>
                <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=cftg_export_entries&form_filter=' . urlencode( $form_filter ) ), 'cftg_export_entries' ) ); ?>"
                   class="button button-secondary">
                    <span class="dashicons dashicons-download"></span> Export CSV
                </a>
            </div>
        </div>

        <div class="cftg-entries-table">
            <table>
                <thead>
                    <tr>
                        <th style="width:60px">ID</th>
                        <th style="width:150px">Date</th>
                        <th style="width:130px">Form</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th style="width:140px">Phone</th>
                        <th style="width:100px">GHL</th>
                        <th style="width:80px"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( empty( $rows ) ): ?>
                        <tr class="cftg-empty"><td colspan="8">
                            <div style="font-size:32px;margin-bottom:8px">📭</div>
                            <div style="font-size:14px;font-weight:600;color:#6b7280">No entries yet</div>
                            <div style="font-size:12px;margin-top:4px">Submissions will appear here as they come in.</div>
                        </td></tr>
                    <?php else: foreach ( $rows as $r ):
                        $m = $forms[ $r['form_type'] ] ?? [ 'label' => $r['form_type'], 'color' => '#6b7280', 'bg' => '#f3f4f6' ];
                        $view_url = esc_url( add_query_arg( [ 'view' => intval( $r['id'] ), 'form_filter' => $form_filter ?: null ] ) );
                        $del_url  = esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=cftg_delete_entry&entry=' . intval( $r['id'] ) ), 'cftg_delete_entry_' . intval( $r['id'] ) ) );
                    ?>
                    <tr data-href="<?php echo $view_url; ?>">
                        <td style="color:#9ca3af;font-family:monospace">#<?php echo intval( $r['id'] ); ?></td>
                        <td style="color:#6b7280;font-size:12px;white-space:nowrap"><?php echo esc_html( mysql2date( 'M j, g:i a', $r['created_at'] ) ); ?></td>
                        <td><span class="cftg-form-pill" style="background:<?php echo esc_attr( $m['bg'] ); ?>;color:<?php echo esc_attr( $m['color'] ); ?>"><?php echo esc_html( $m['label'] ); ?></span></td>
                        <td class="cftg-row-name"><?php echo esc_html( trim( $r['first_name'] . ' ' . $r['last_name'] ) ) ?: '<span style="color:#9ca3af">—</span>'; ?></td>
                        <td class="cftg-row-email"><a href="mailto:<?php echo esc_attr( $r['email'] ); ?>" onclick="event.stopPropagation()"><?php echo esc_html( $r['email'] ); ?></a></td>
                        <td style="color:#6b7280"><?php echo esc_html( $r['phone'] ) ?: '—'; ?></td>
                        <td>
                            <?php if ( $r['ghl_status'] === 'sent' ): ?>
                                <span class="cftg-ghl-badge cftg-ghl-sent">● Sent</span>
                            <?php elseif ( $r['ghl_status'] === 'failed' ): ?>
                                <span class="cftg-ghl-badge cftg-ghl-failed" title="<?php echo esc_attr( $r['ghl_message'] ); ?>">● Failed</span>
                            <?php else: ?>
                                <span class="cftg-ghl-badge cftg-ghl-pending">○ Pending</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align:right">
                            <a href="<?php echo $del_url; ?>" class="button button-small cftg-delete-btn"
                               onclick="event.stopPropagation(); return confirm('Delete this entry?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>

            <?php if ( $total_pages > 1 ): ?>
            <div class="cftg-pagination">
                <?php
                $base = add_query_arg( [ 'page' => 'cftg-entries', 'form_filter' => $form_filter ], admin_url( 'admin.php' ) );
                echo paginate_links( [
                    'base'      => add_query_arg( 'paged', '%#%', $base ),
                    'format'    => '',
                    'current'   => $page,
                    'total'     => $total_pages,
                    'prev_text' => '‹ Prev',
                    'next_text' => 'Next ›',
                ] );
                ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
    document.addEventListener( 'click', function( e ) {
        const row = e.target.closest( 'tr[data-href]' );
        if ( ! row ) return;
        if ( e.target.closest( 'a, button' ) ) return; // let links/buttons handle their own clicks
        window.location.href = row.dataset.href;
    } );
    </script>
    <?php
}

/* ── Detail view: full page, replaces the list ── */
function cftg_render_entry_detail( array $entry ): void {
    $forms = cftg_form_meta();
    $m     = $forms[ $entry['form_type'] ] ?? [ 'label' => $entry['form_type'], 'color' => '#6b7280', 'bg' => '#f3f4f6' ];
    $data  = json_decode( $entry['data'], true ) ?: [];
    $back  = esc_url( admin_url( 'admin.php?page=cftg-entries' ) );
    $del   = esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=cftg_delete_entry&entry=' . intval( $entry['id'] ) ), 'cftg_delete_entry_' . intval( $entry['id'] ) ) );

    /* Pretty field labels */
    $label_map = [
        'dispose_types'      => 'Items to dispose',
        'delivery_date'      => 'Delivery date',
        'bin_duration'       => 'Rental duration',
        'bin_size'           => 'Bin size',
        'scrap_types'        => 'Scrap types',
        'load_size'          => 'Load size',
        'exact_weight'       => 'Exact weight',
        'exact_weight_unit'  => 'Weight unit',
        'vehicle_year'       => 'Vehicle year',
        'vehicle_make'       => 'Vehicle make',
        'vehicle_model'      => 'Vehicle model',
        'engine_running'     => 'Engine running',
        'parts_missing'      => 'Parts missing',
        'whats_missing'      => 'Missing parts notes',
        'utm_source'         => 'UTM Source',
        'utm_medium'         => 'UTM Medium',
        'utm_campaign'       => 'UTM Campaign',
        'utm_term'           => 'UTM Term',
        'utm_content'        => 'UTM Content',
        'gclid'              => 'GCLID',
    ];
    $pretty = fn( $k ) => $label_map[ $k ] ?? ucwords( str_replace( '_', ' ', $k ) );
    ?>
    <style>
        .cftg-detail-wrap { max-width: 1100px; }
        .cftg-detail-top {
            display:flex; align-items:center; justify-content:space-between;
            gap:16px; margin:18px 0 22px; flex-wrap:wrap;
        }
        .cftg-detail-top .left { display:flex; align-items:center; gap:12px; }
        .cftg-back-btn {
            display:inline-flex; align-items:center; gap:6px;
            padding:8px 14px; background:#fff; border:1px solid #d1d5db;
            border-radius:8px; color:#374151; text-decoration:none; font-weight:600;
        }
        .cftg-back-btn:hover { background:#f9fafb; color:#111827; }

        .cftg-detail-card {
            background:#fff; border:1px solid #e5e7eb; border-radius:12px;
            overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,0.04);
        }
        .cftg-detail-hero {
            padding:24px 28px; border-bottom:1px solid #e5e7eb;
            display:flex; align-items:center; gap:18px; flex-wrap:wrap;
        }
        .cftg-detail-hero h2 { margin:0; font-size:22px; font-weight:800; color:#111827; }
        .cftg-detail-meta { color:#6b7280; font-size:13px; }
        .cftg-detail-meta strong { color:#111827; }

        .cftg-detail-section {
            padding:22px 28px; border-bottom:1px solid #f3f4f6;
        }
        .cftg-detail-section:last-child { border-bottom:none; }
        .cftg-detail-section h3 {
            margin:0 0 14px; font-size:11px; font-weight:700;
            text-transform:uppercase; letter-spacing:0.08em; color:#6b7280;
        }
        .cftg-detail-grid {
            display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr));
            gap:14px 24px;
        }
        .cftg-detail-grid .item { display:flex; flex-direction:column; gap:3px; }
        .cftg-detail-grid .label {
            font-size:11px; text-transform:uppercase; letter-spacing:0.05em;
            color:#9ca3af; font-weight:600;
        }
        .cftg-detail-grid .value { font-size:14px; color:#111827; word-break:break-word; }
        .cftg-detail-grid .value a { color:#2563eb; text-decoration:none; }
        .cftg-detail-grid .value a:hover { text-decoration:underline; }
        .cftg-detail-grid .value code {
            background:#f3f4f6; padding:2px 6px; border-radius:4px; font-size:12px;
        }

        .cftg-ghl-box {
            padding:14px 18px; border-radius:8px; display:flex;
            align-items:flex-start; gap:12px;
        }
        .cftg-ghl-box.sent    { background:#dcfce7; border:1px solid #86efac; color:#166534; }
        .cftg-ghl-box.failed  { background:#fee2e2; border:1px solid #fca5a5; color:#991b1b; }
        .cftg-ghl-box.pending { background:#f3f4f6; border:1px solid #d1d5db; color:#6b7280; }
        .cftg-ghl-box .icon { font-size:18px; line-height:1; }
        .cftg-ghl-box .info strong { display:block; margin-bottom:4px; }
    </style>

    <div class="wrap cftg-admin cftg-detail-wrap">
        <div class="cftg-detail-top">
            <div class="left">
                <a href="<?php echo $back; ?>" class="cftg-back-btn">
                    <span class="dashicons dashicons-arrow-left-alt" style="margin-top:1px"></span> Back to entries
                </a>
                <h1 style="margin:0;font-size:18px;color:#374151;">Entry <span style="color:#9ca3af">#<?php echo intval( $entry['id'] ); ?></span></h1>
            </div>
            <a href="<?php echo $del; ?>" class="button cftg-delete-btn" onclick="return confirm('Delete this entry?')">
                <span class="dashicons dashicons-trash" style="vertical-align:middle"></span> Delete
            </a>
        </div>

        <div class="cftg-detail-card">

            <!-- Hero: form + date + tag -->
            <div class="cftg-detail-hero">
                <span class="cftg-form-pill" style="background:<?php echo esc_attr( $m['bg'] ); ?>;color:<?php echo esc_attr( $m['color'] ); ?>;padding:6px 14px;font-size:12px"><?php echo esc_html( $m['label'] ); ?></span>
                <h2><?php echo esc_html( trim( $entry['first_name'] . ' ' . $entry['last_name'] ) ) ?: 'Anonymous'; ?></h2>
                <div class="cftg-detail-meta" style="margin-left:auto">
                    <?php echo esc_html( mysql2date( 'F j, Y', $entry['created_at'] ) ); ?>
                    &middot;
                    <strong><?php echo esc_html( mysql2date( 'g:i a', $entry['created_at'] ) ); ?></strong>
                </div>
            </div>

            <!-- Contact info -->
            <div class="cftg-detail-section">
                <h3>Contact</h3>
                <div class="cftg-detail-grid">
                    <div class="item"><span class="label">Name</span><span class="value"><?php echo esc_html( trim( $entry['first_name'] . ' ' . $entry['last_name'] ) ) ?: '—'; ?></span></div>
                    <div class="item"><span class="label">Email</span><span class="value"><?php if ( $entry['email'] ): ?><a href="mailto:<?php echo esc_attr( $entry['email'] ); ?>"><?php echo esc_html( $entry['email'] ); ?></a><?php else: ?>—<?php endif; ?></span></div>
                    <div class="item"><span class="label">Phone</span><span class="value"><?php if ( $entry['phone'] ): ?><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $entry['phone'] ) ); ?>"><?php echo esc_html( $entry['phone'] ); ?></a><?php else: ?>—<?php endif; ?></span></div>
                    <?php if ( $entry['postal'] ): ?>
                    <div class="item"><span class="label">Postal code</span><span class="value"><?php echo esc_html( $entry['postal'] ); ?></span></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Form answers -->
            <?php
            $form_keys = [
                'bin_estimate'  => [ 'dispose_types','delivery_date','bin_duration','bin_size' ],
                'scrap_metal'   => [ 'scrap_types','load_size','exact_weight','exact_weight_unit' ],
                'vehicle_quote' => [ 'vehicle_year','vehicle_make','vehicle_model','engine_running','parts_missing','whats_missing' ],
            ];
            $keys = $form_keys[ $entry['form_type'] ] ?? [];
            $has_form_data = false;
            foreach ( $keys as $k ) { if ( ! empty( $data[ $k ] ) ) { $has_form_data = true; break; } }
            ?>
            <?php if ( $has_form_data ): ?>
            <div class="cftg-detail-section">
                <h3><?php echo esc_html( $m['label'] ); ?> details</h3>
                <div class="cftg-detail-grid">
                    <?php foreach ( $keys as $k ): if ( empty( $data[ $k ] ) ) continue; ?>
                    <div class="item">
                        <span class="label"><?php echo esc_html( $pretty( $k ) ); ?></span>
                        <span class="value"><?php echo esc_html( is_array( $data[ $k ] ) ? implode( ', ', $data[ $k ] ) : (string) $data[ $k ] ); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- UTM / tracking -->
            <?php
            $utm_keys = [ 'utm_source','utm_medium','utm_campaign','utm_term','utm_content','gclid' ];
            $has_utm  = false;
            foreach ( $utm_keys as $k ) { if ( ! empty( $data[ $k ] ) ) { $has_utm = true; break; } }
            ?>
            <?php if ( $has_utm ): ?>
            <div class="cftg-detail-section">
                <h3>Tracking</h3>
                <div class="cftg-detail-grid">
                    <?php foreach ( $utm_keys as $k ): if ( empty( $data[ $k ] ) ) continue; ?>
                    <div class="item">
                        <span class="label"><?php echo esc_html( $pretty( $k ) ); ?></span>
                        <span class="value"><code><?php echo esc_html( (string) $data[ $k ] ); ?></code></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Metadata -->
            <div class="cftg-detail-section">
                <h3>Metadata</h3>
                <div class="cftg-detail-grid">
                    <div class="item"><span class="label">Tag sent to GHL</span><span class="value"><?php echo $entry['tag'] ? '<span class="cftg-tag-pill">' . esc_html( $entry['tag'] ) . '</span>' : '—'; ?></span></div>
                    <div class="item"><span class="label">IP address</span><span class="value"><code><?php echo esc_html( $entry['ip'] ) ?: '—'; ?></code></span></div>
                    <div class="item"><span class="label">Submitted</span><span class="value"><?php echo esc_html( mysql2date( 'M j, Y \a\t g:i:s a', $entry['created_at'] ) ); ?></span></div>
                </div>
            </div>

            <!-- GHL status -->
            <div class="cftg-detail-section">
                <h3>GoHighLevel</h3>
                <?php if ( $entry['ghl_status'] === 'sent' ): ?>
                    <div class="cftg-ghl-box sent">
                        <span class="icon">✓</span>
                        <div class="info">
                            <strong>Sent successfully</strong>
                            Contact created or updated in GoHighLevel.
                        </div>
                    </div>
                <?php elseif ( $entry['ghl_status'] === 'failed' ): ?>
                    <div class="cftg-ghl-box failed">
                        <span class="icon">✗</span>
                        <div class="info">
                            <strong>Failed to send</strong>
                            <?php echo esc_html( $entry['ghl_message'] ) ?: 'Unknown error.'; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="cftg-ghl-box pending">
                        <span class="icon">○</span>
                        <div class="info">
                            <strong>Pending</strong>
                            This entry was saved locally but the GHL status was never updated.
                        </div>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
    <?php
}
