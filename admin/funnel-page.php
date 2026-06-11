<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* ── Form meta (label, colour) — mirror what entries-page uses ── */
function cftg_funnel_meta(): array {
    return [
        'bin_estimate'  => [ 'label' => 'Bin Estimate',  'color' => '#0ea5e9', 'bg' => '#e0f2fe', 'steps' => 6 ],
        'scrap_metal'   => [ 'label' => 'Scrap Metal',   'color' => '#f59e0b', 'bg' => '#fef3c7', 'steps' => 4 ],
        'vehicle_quote' => [ 'label' => 'Vehicle Quote', 'color' => '#10b981', 'bg' => '#d1fae5', 'steps' => 5 ],
    ];
}

/* ── Step labels per form (for readable funnel rows) ── */
function cftg_funnel_step_labels(): array {
    return [
        'bin_estimate'  => [
            1 => 'What to dispose',
            2 => 'Delivery date',
            3 => 'Rental duration',
            4 => 'Bin size',
            5 => 'Postal code',
            6 => 'Contact info',
        ],
        'scrap_metal'   => [
            1 => 'Materials',
            2 => 'Load size / weight',
            3 => 'Postal code',
            4 => 'Contact info',
        ],
        'vehicle_quote' => [
            1 => 'Vehicle details',
            2 => 'Engine running?',
            3 => 'Parts missing?',
            4 => 'Postal code',
            5 => 'Contact info',
        ],
    ];
}

function cftg_render_funnel_page(): void {
    $forms   = cftg_funnel_meta();
    $labels  = cftg_funnel_step_labels();

    $form_filter = sanitize_key( $_GET['form_filter'] ?? '' );
    if ( $form_filter !== '' && ! isset( $forms[ $form_filter ] ) ) $form_filter = '';

    $page_filter = esc_url_raw( $_GET['page_filter'] ?? '' );

    /* Default range: last 30 days */
    $end_default   = current_time( 'Y-m-d' );
    $start_default = date( 'Y-m-d', strtotime( '-29 days', current_time( 'timestamp' ) ) );
    $start = sanitize_text_field( $_GET['start'] ?? $start_default );
    $end   = sanitize_text_field( $_GET['end']   ?? $end_default );

    /* Use full-day boundaries for the BETWEEN comparison */
    $start_dt = $start . ' 00:00:00';
    $end_dt   = $end   . ' 23:59:59';

    $shown_forms = $form_filter ? [ $form_filter => $forms[ $form_filter ] ] : $forms;
    ?>
    <style>
        .cftg-funnel-wrap { max-width: 1200px; }
        .cftg-funnel-controls {
            display:flex; gap:10px; align-items:flex-end; flex-wrap:wrap;
            background:#fff; border:1px solid #e5e7eb; border-radius:10px;
            padding:14px 18px; margin:18px 0; box-shadow:0 1px 3px rgba(0,0,0,0.04);
        }
        .cftg-funnel-controls label {
            font-size:11px; font-weight:700; color:#6b7280;
            text-transform:uppercase; letter-spacing:0.05em;
            display:block; margin-bottom:4px;
        }
        .cftg-funnel-controls input[type="date"],
        .cftg-funnel-controls select {
            height:34px; min-width:160px;
        }
        .cftg-funnel-controls .button { height:34px; }

        .cftg-funnel-card {
            background:#fff; border:1px solid #e5e7eb; border-radius:12px;
            padding:22px 26px; margin-bottom:20px; box-shadow:0 1px 3px rgba(0,0,0,0.04);
        }
        .cftg-funnel-head {
            display:flex; align-items:center; gap:14px; margin-bottom:18px;
            padding-bottom:14px; border-bottom:1px solid #f3f4f6;
        }
        .cftg-funnel-head h2 { margin:0; font-size:18px; font-weight:800; color:#111827; }
        .cftg-funnel-pill {
            display:inline-block; padding:3px 12px; border-radius:12px;
            font-size:11px; font-weight:700;
        }

        .cftg-funnel-row {
            display:grid; grid-template-columns:200px 1fr 130px 90px;
            align-items:center; gap:14px;
            padding:10px 0; border-bottom:1px solid #f3f4f6;
        }
        .cftg-funnel-row:last-child { border-bottom:none; }
        .cftg-funnel-label { font-size:13px; font-weight:600; color:#111827; }
        .cftg-funnel-label .num { color:#6b7280; font-weight:500; margin-left:6px; font-size:11px; }
        .cftg-funnel-bar-wrap {
            background:#f3f4f6; border-radius:6px; height:24px; position:relative; overflow:hidden;
        }
        .cftg-funnel-bar {
            height:100%; border-radius:6px;
            display:flex; align-items:center; padding-left:10px;
            color:#fff; font-size:11px; font-weight:700;
            transition:width 0.3s ease;
        }
        .cftg-funnel-count { font-size:14px; font-weight:700; color:#111827; text-align:right; }
        .cftg-funnel-pct { font-size:12px; font-weight:600; color:#6b7280; text-align:right; }

        .cftg-funnel-empty {
            padding:32px; text-align:center; color:#9ca3af; font-size:13px;
        }
        .cftg-funnel-drop {
            font-size:11px; color:#dc2626; font-weight:600; margin-left:6px;
        }
    </style>

    <div class="wrap cftg-admin cftg-funnel-wrap">
        <h1 class="cftg-admin-title">
            <img src="https://cftgroup.ca/wp-content/uploads/2024/09/cft-group-logo.png" alt="CFT Group" style="height:36px;vertical-align:middle;margin-right:10px">
            Form Funnel
        </h1>

        <!-- Controls -->
        <form method="get" class="cftg-funnel-controls">
            <input type="hidden" name="page" value="cftg-funnel">

            <div>
                <label>Form</label>
                <select name="form_filter">
                    <option value="">All forms</option>
                    <?php foreach ( $forms as $k => $m ): ?>
                        <option value="<?php echo esc_attr( $k ); ?>" <?php selected( $form_filter, $k ); ?>><?php echo esc_html( $m['label'] ); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label>From</label>
                <input type="date" name="start" value="<?php echo esc_attr( $start ); ?>">
            </div>

            <div>
                <label>To</label>
                <input type="date" name="end" value="<?php echo esc_attr( $end ); ?>">
            </div>

            <?php
            /* Show landing-page filter only when a single form is selected */
            if ( $form_filter ):
                $pages = CFTG_Funnel::distinct_pages( $form_filter, $start_dt, $end_dt );
                if ( $pages ):
            ?>
                <div style="flex:1;min-width:240px">
                    <label>Landing page</label>
                    <select name="page_filter">
                        <option value="">All pages</option>
                        <?php foreach ( $pages as $p ): ?>
                            <option value="<?php echo esc_attr( $p ); ?>" <?php selected( $page_filter, $p ); ?>><?php echo esc_html( $p ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; endif; ?>

            <button type="submit" class="button button-primary">Apply</button>
            <?php if ( $form_filter || $page_filter || $start !== $start_default || $end !== $end_default ): ?>
                <a href="?page=cftg-funnel" class="button">Reset</a>
            <?php endif; ?>
        </form>

        <?php foreach ( $shown_forms as $ft => $meta ):
            $summary = CFTG_Funnel::summary( $ft, $start_dt, $end_dt, $form_filter ? $page_filter : '' );
            $views   = $summary['views'];
            $submits = $summary['submits'];
            $steps   = $summary['steps'];

            /* Build the funnel rows in order: view → each step → submit */
            $rows = [];
            $rows[] = [ 'key' => 'view', 'label' => 'Page / form view', 'count' => $views ];
            for ( $i = 1; $i <= ( $meta['steps'] ?? 1 ); $i++ ) {
                $rows[] = [
                    'key'   => 'step_' . $i,
                    'label' => 'Step ' . $i,
                    'sub'   => $labels[ $ft ][ $i ] ?? '',
                    'count' => $steps[ $i ] ?? 0,
                ];
            }
            $rows[] = [ 'key' => 'submit', 'label' => 'Submitted', 'count' => $submits ];

            $max = max( 1, $views );
        ?>
        <div class="cftg-funnel-card">
            <div class="cftg-funnel-head">
                <span class="cftg-funnel-pill" style="background:<?php echo esc_attr( $meta['bg'] ); ?>;color:<?php echo esc_attr( $meta['color'] ); ?>">
                    <?php echo esc_html( $meta['label'] ); ?>
                </span>
                <h2><?php echo esc_html( $meta['label'] ); ?> funnel</h2>
                <div style="margin-left:auto;color:#6b7280;font-size:12px">
                    <?php echo esc_html( $start ); ?> → <?php echo esc_html( $end ); ?>
                </div>
            </div>

            <?php if ( $views === 0 && $submits === 0 && empty( $steps ) ): ?>
                <div class="cftg-funnel-empty">
                    <div style="font-size:28px;margin-bottom:6px">📊</div>
                    No tracked events for this form in the selected range yet.
                </div>
            <?php else:
                $prev_count = null;
                foreach ( $rows as $r ):
                    $count = intval( $r['count'] );
                    $pct   = $max > 0 ? round( ( $count / $max ) * 100, 1 ) : 0;
                    $drop  = ( $prev_count !== null && $prev_count > 0 )
                        ? round( ( ( $prev_count - $count ) / $prev_count ) * 100, 1 ) : 0;
                ?>
                <div class="cftg-funnel-row">
                    <div class="cftg-funnel-label">
                        <?php echo esc_html( $r['label'] ); ?>
                        <?php if ( ! empty( $r['sub'] ) ): ?>
                            <span class="num"><?php echo esc_html( $r['sub'] ); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="cftg-funnel-bar-wrap">
                        <div class="cftg-funnel-bar" style="width:<?php echo esc_attr( $pct ); ?>%;background:<?php echo esc_attr( $meta['color'] ); ?>">
                            <?php echo $pct >= 8 ? esc_html( $count ) : ''; ?>
                        </div>
                    </div>
                    <div class="cftg-funnel-count"><?php echo intval( $count ); ?></div>
                    <div class="cftg-funnel-pct">
                        <?php echo esc_html( $pct ); ?>%
                        <?php if ( $prev_count !== null && $drop > 0 ): ?>
                            <div class="cftg-funnel-drop">−<?php echo esc_html( $drop ); ?>%</div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php $prev_count = $count; endforeach; ?>

                <?php
                $conv = $views > 0 ? round( ( $submits / $views ) * 100, 1 ) : 0;
                ?>
                <div style="margin-top:14px;padding-top:14px;border-top:1px solid #f3f4f6;display:flex;justify-content:space-between;align-items:center">
                    <div style="font-size:12px;color:#6b7280">Overall conversion (view → submit)</div>
                    <div style="font-size:20px;font-weight:800;color:<?php echo esc_attr( $meta['color'] ); ?>">
                        <?php echo esc_html( $conv ); ?>%
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php
}
