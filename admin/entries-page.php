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
    fputcsv( $out, [ 'ID','Date','Form','First Name','Last Name','Email','Phone','Postal','GHL Status','GHL Message','IP','Data (JSON)' ] );
    foreach ( $rows as $r ) {
        fputcsv( $out, [
            $r['id'], $r['created_at'], $r['form_type'],
            $r['first_name'], $r['last_name'], $r['email'], $r['phone'], $r['postal'],
            $r['ghl_status'], $r['ghl_message'], $r['ip'], $r['data'],
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

/* ── List page ── */
function cftg_render_entries_page(): void {
    $form_filter = sanitize_key( $_GET['form_filter'] ?? '' );
    $page        = max( 1, intval( $_GET['paged'] ?? 1 ) );
    $per_page    = 25;
    $result      = CFTG_Entries::fetch( $page, $per_page, $form_filter );
    $rows        = $result['rows'];
    $total       = $result['total'];
    $total_pages = max( 1, (int) ceil( $total / $per_page ) );

    $view_id = isset( $_GET['view'] ) ? intval( $_GET['view'] ) : 0;
    $viewing = $view_id ? CFTG_Entries::get( $view_id ) : null;

    $form_labels = [
        'bin_estimate'  => 'Bin Estimate',
        'scrap_metal'   => 'Scrap Metal',
        'vehicle_quote' => 'Vehicle Quote',
    ];
    ?>
    <div class="wrap cftg-admin">
        <h1 class="cftg-admin-title">
            <img src="https://cftgroup.ca/wp-content/uploads/2024/09/cft-group-logo.png" alt="CFT Group" style="height:36px;vertical-align:middle;margin-right:10px">
            Form Entries
            <span style="font-size:13px;color:#666;font-weight:400;margin-left:8px">(<?php echo intval( $total ); ?> total)</span>
        </h1>

        <?php if ( isset( $_GET['deleted'] ) ): ?>
            <div class="notice notice-success is-dismissible"><p>Entry deleted.</p></div>
        <?php endif; ?>

        <?php if ( $viewing ): ?>
            <?php cftg_render_entry_detail( $viewing, $form_labels ); ?>
        <?php endif; ?>

        <!-- Filters + export -->
        <div style="margin:18px 0;display:flex;align-items:center;gap:10px;flex-wrap:wrap">
            <form method="get" style="display:flex;gap:8px;align-items:center;margin:0">
                <input type="hidden" name="page" value="cftg-entries">
                <select name="form_filter" onchange="this.form.submit()">
                    <option value="">All forms</option>
                    <?php foreach ( $form_labels as $k => $label ): ?>
                        <option value="<?php echo esc_attr( $k ); ?>" <?php selected( $form_filter, $k ); ?>><?php echo esc_html( $label ); ?></option>
                    <?php endforeach; ?>
                </select>
            </form>

            <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=cftg_export_entries&form_filter=' . urlencode( $form_filter ) ), 'cftg_export_entries' ) ); ?>"
               class="button button-secondary">
                <span class="dashicons dashicons-download" style="vertical-align:middle"></span> Export CSV
            </a>
        </div>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th style="width:60px">ID</th>
                    <th style="width:140px">Date</th>
                    <th style="width:120px">Form</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th style="width:180px">Tag</th>
                    <th style="width:90px">GHL</th>
                    <th style="width:130px">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ( empty( $rows ) ): ?>
                    <tr><td colspan="9" style="text-align:center;padding:24px;color:#666">No entries yet.</td></tr>
                <?php else: foreach ( $rows as $r ): ?>
                <tr>
                    <td><?php echo intval( $r['id'] ); ?></td>
                    <td><?php echo esc_html( mysql2date( 'M j, Y g:i a', $r['created_at'] ) ); ?></td>
                    <td><?php echo esc_html( $form_labels[ $r['form_type'] ] ?? $r['form_type'] ); ?></td>
                    <td><?php echo esc_html( trim( $r['first_name'] . ' ' . $r['last_name'] ) ) ?: '—'; ?></td>
                    <td><a href="mailto:<?php echo esc_attr( $r['email'] ); ?>"><?php echo esc_html( $r['email'] ); ?></a></td>
                    <td><?php echo esc_html( $r['phone'] ); ?></td>
                    <td><?php echo $r['tag'] ? '<span style="background:#fef3c7;color:#92400e;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600">' . esc_html( $r['tag'] ) . '</span>' : '—'; ?></td>
                    <td>
                        <?php if ( $r['ghl_status'] === 'sent' ): ?>
                            <span style="color:#16a34a;font-weight:600">✓ Sent</span>
                        <?php elseif ( $r['ghl_status'] === 'failed' ): ?>
                            <span style="color:#dc2626;font-weight:600" title="<?php echo esc_attr( $r['ghl_message'] ); ?>">✗ Failed</span>
                        <?php else: ?>
                            <span style="color:#999">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="?page=cftg-entries&view=<?php echo intval( $r['id'] ); ?><?php echo $form_filter ? '&form_filter=' . urlencode( $form_filter ) : ''; ?>" class="button button-small">View</a>
                        <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=cftg_delete_entry&entry=' . intval( $r['id'] ) ), 'cftg_delete_entry_' . intval( $r['id'] ) ) ); ?>"
                           class="button button-small" style="color:#dc2626"
                           onclick="return confirm('Delete this entry?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>

        <?php if ( $total_pages > 1 ): ?>
        <div class="tablenav" style="margin-top:14px">
            <div class="tablenav-pages">
                <span class="displaying-num"><?php echo intval( $total ); ?> items</span>
                <?php
                $base = add_query_arg( [ 'page' => 'cftg-entries', 'form_filter' => $form_filter ], admin_url( 'admin.php' ) );
                echo paginate_links( [
                    'base'      => add_query_arg( 'paged', '%#%', $base ),
                    'format'    => '',
                    'current'   => $page,
                    'total'     => $total_pages,
                    'prev_text' => '‹',
                    'next_text' => '›',
                ] );
                ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php
}

function cftg_render_entry_detail( array $entry, array $form_labels ): void {
    $data = json_decode( $entry['data'], true ) ?: [];
    ?>
    <div class="notice notice-info" style="padding:16px">
        <h2 style="margin:0 0 12px"><?php echo esc_html( $form_labels[ $entry['form_type'] ] ?? $entry['form_type'] ); ?> — Entry #<?php echo intval( $entry['id'] ); ?></h2>
        <table class="widefat striped" style="max-width:780px">
            <tbody>
                <tr><th style="width:180px">Date</th><td><?php echo esc_html( mysql2date( 'F j, Y g:i a', $entry['created_at'] ) ); ?></td></tr>
                <tr><th>Name</th><td><?php echo esc_html( trim( $entry['first_name'] . ' ' . $entry['last_name'] ) ); ?></td></tr>
                <tr><th>Email</th><td><a href="mailto:<?php echo esc_attr( $entry['email'] ); ?>"><?php echo esc_html( $entry['email'] ); ?></a></td></tr>
                <tr><th>Phone</th><td><?php echo esc_html( $entry['phone'] ); ?></td></tr>
                <?php if ( $entry['postal'] ): ?>
                <tr><th>Postal</th><td><?php echo esc_html( $entry['postal'] ); ?></td></tr>
                <?php endif; ?>
                <?php foreach ( $data as $k => $v ): ?>
                    <tr><th><?php echo esc_html( str_replace( '_', ' ', ucfirst( $k ) ) ); ?></th><td><?php echo esc_html( is_array( $v ) ? implode( ', ', $v ) : (string) $v ); ?></td></tr>
                <?php endforeach; ?>
                <tr><th>IP</th><td><code><?php echo esc_html( $entry['ip'] ); ?></code></td></tr>
                <tr><th>GHL Status</th>
                    <td>
                        <?php if ( $entry['ghl_status'] === 'sent' ): ?>
                            <span style="color:#16a34a;font-weight:600">✓ Sent successfully</span>
                        <?php elseif ( $entry['ghl_status'] === 'failed' ): ?>
                            <span style="color:#dc2626;font-weight:600">✗ Failed</span>
                            <?php if ( $entry['ghl_message'] ): ?>
                                <div style="margin-top:4px;color:#666"><?php echo esc_html( $entry['ghl_message'] ); ?></div>
                            <?php endif; ?>
                        <?php else: ?>
                            <span style="color:#999">Pending</span>
                        <?php endif; ?>
                    </td>
                </tr>
            </tbody>
        </table>
        <p style="margin-top:12px"><a href="?page=cftg-entries" class="button">← Back to list</a></p>
    </div>
    <?php
}
