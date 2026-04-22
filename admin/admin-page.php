<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* ── Save settings ── */
add_action( 'admin_post_cftg_save_settings', 'cftg_handle_save_settings' );
function cftg_handle_save_settings() {
    if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );
    check_admin_referer( 'cftg_save_settings' );

    $text_fields = [
        'cftg_ghl_api_key', 'cftg_ghl_location_id',
        // Bin Estimate
        'cftg_cf_dispose_types', 'cftg_cf_delivery_date', 'cftg_cf_bin_duration', 'cftg_cf_bin_size',
        // Scrap Metal
        'cftg_cf_scrap_types',
        // Vehicle Quote
        'cftg_cf_vehicle_year', 'cftg_cf_vehicle_make', 'cftg_cf_vehicle_model',
        'cftg_cf_engine_running', 'cftg_cf_parts_missing', 'cftg_cf_missing_parts_notes',
    ];

    foreach ( $text_fields as $key ) {
        if ( isset( $_POST[ $key ] ) ) {
            update_option( $key, sanitize_text_field( $_POST[ $key ] ) );
        }
    }

    wp_redirect( admin_url( 'admin.php?page=cftg-settings&saved=1' ) );
    exit;
}

/* ── Render settings page ── */
function cftg_render_settings_page() {
    $saved = isset( $_GET['saved'] );
    $tab   = sanitize_key( $_GET['tab'] ?? 'connection' );
    ?>
    <div class="wrap cftg-admin">
        <h1 class="cftg-admin-title">
            <img src="https://cftgroup.ca/wp-content/uploads/2024/09/cft-group-logo.png" alt="CFT Group" style="height:36px;vertical-align:middle;margin-right:10px">
            CFT Group Forms
        </h1>

        <?php if ( $saved ): ?>
            <div class="notice notice-success is-dismissible"><p><strong>Settings saved.</strong></p></div>
        <?php endif; ?>

        <!-- Tab Nav -->
        <nav class="cftg-tabs nav-tab-wrapper">
            <a href="?page=cftg-settings&tab=connection" class="nav-tab <?php echo $tab === 'connection' ? 'nav-tab-active' : ''; ?>">
                <span class="dashicons dashicons-admin-network"></span> GHL Connection
            </a>
            <a href="?page=cftg-settings&tab=fields" class="nav-tab <?php echo $tab === 'fields' ? 'nav-tab-active' : ''; ?>">
                <span class="dashicons dashicons-list-view"></span> Custom Fields
            </a>
            <a href="?page=cftg-settings&tab=shortcodes" class="nav-tab <?php echo $tab === 'shortcodes' ? 'nav-tab-active' : ''; ?>">
                <span class="dashicons dashicons-shortcode"></span> Shortcodes
            </a>
        </nav>

        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <?php wp_nonce_field( 'cftg_save_settings' ); ?>
            <input type="hidden" name="action" value="cftg_save_settings">
            <input type="hidden" name="tab" value="<?php echo esc_attr( $tab ); ?>">

            <?php if ( $tab === 'connection' ) cftg_tab_connection(); ?>
            <?php if ( $tab === 'fields' )      cftg_tab_fields(); ?>
            <?php if ( $tab === 'shortcodes' )  cftg_tab_shortcodes(); ?>

            <?php if ( $tab !== 'shortcodes' ): ?>
                <p class="submit">
                    <button type="submit" class="button button-primary button-hero">Save Settings</button>
                </p>
            <?php endif; ?>
        </form>
    </div>
    <?php
}

/* ──────────────────────────────────────────────
   TAB: GHL Connection
   ────────────────────────────────────────────── */
function cftg_tab_connection() {
    $api_key     = get_option( 'cftg_ghl_api_key', '' );
    $location_id = get_option( 'cftg_ghl_location_id', '' );
    ?>
    <div class="cftg-section">
        <div class="cftg-section-header">
            <h2>GoHighLevel Connection</h2>
            <p>Connect your forms to your GHL account. Submissions will be sent as contacts with the data mapped to your custom fields.</p>
        </div>

        <table class="form-table cftg-form-table">
            <tr>
                <th scope="row"><label for="cftg_ghl_location_id">Location ID</label></th>
                <td>
                    <input type="text" id="cftg_ghl_location_id" name="cftg_ghl_location_id"
                        value="<?php echo esc_attr( $location_id ); ?>"
                        class="regular-text" placeholder="e.g. AbCdEf1234567890">
                    <p class="description">Found in GHL → Settings → Business Info → Location ID.</p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="cftg_ghl_api_key">Private API Key</label></th>
                <td>
                    <input type="password" id="cftg_ghl_api_key" name="cftg_ghl_api_key"
                        value="<?php echo esc_attr( $api_key ); ?>"
                        class="regular-text" placeholder="eyJ...">
                    <button type="button" class="button" id="cftg-toggle-key" style="margin-left:8px">Show</button>
                    <p class="description">Found in GHL → Settings → Business Info → Private Integration → API Key.</p>
                </td>
            </tr>
        </table>

        <!-- Test Connection -->
        <div class="cftg-test-box">
            <button type="button" id="cftg-test-btn" class="button button-secondary">
                <span class="dashicons dashicons-update" style="vertical-align:middle;margin-right:4px"></span>
                Test Connection
            </button>
            <span id="cftg-test-result" class="cftg-test-result"></span>
        </div>

        <div class="cftg-info-box">
            <span class="dashicons dashicons-info-outline"></span>
            <div>
                <strong>How submissions work:</strong>
                <ul>
                    <li>Each form submission creates or updates a GHL contact (matched by email).</li>
                    <li>Standard fields (name, email, phone, postal code) are mapped automatically.</li>
                    <li>Form-specific data is stored in the custom fields you configure in the <strong>Custom Fields</strong> tab.</li>
                    <li>Each form adds a tag so you can segment contacts: <code>CFT - Bin Estimate</code>, <code>CFT - Scrap Metal</code>, <code>CFT - Vehicle Quote</code>.</li>
                </ul>
            </div>
        </div>
    </div>
    <?php
}

/* ──────────────────────────────────────────────
   TAB: Custom Fields
   ────────────────────────────────────────────── */
function cftg_tab_fields() {
    ?>
    <div class="cftg-section">
        <div class="cftg-section-header">
            <h2>GHL Custom Field IDs</h2>
            <p>
                Create the custom fields listed below inside GHL, then paste each field's ID here.
                Go to <strong>GHL → Settings → Custom Fields → Contacts</strong>, click a field, and copy its ID from the URL or field detail panel.
                See the <a href="<?php echo admin_url( 'admin.php?page=cftg-instructions' ); ?>">Instructions page</a> for step-by-step guidance.
            </p>
        </div>

        <!-- Bin Estimate -->
        <div class="cftg-field-group">
            <h3><span class="cftg-form-badge cftg-badge-bin">Bin Estimate</span> Custom Fields</h3>
            <p class="description">Used by shortcode <code>[cftg_bin_estimate]</code></p>
            <table class="form-table cftg-form-table">
                <?php
                cftg_field_row( 'cftg_cf_dispose_types',  'Dispose Types',    'TEXT (Multi-line)',  'Items to dispose: Garbage, Metal, Construction Debris…' );
                cftg_field_row( 'cftg_cf_delivery_date',  'Delivery Date',    'DATE',              'When the bin is needed' );
                cftg_field_row( 'cftg_cf_bin_duration',   'Rental Duration',  'TEXT',              'How long: One day, One week, etc.' );
                cftg_field_row( 'cftg_cf_bin_size',       'Bin Size',         'TEXT',              '10 yard, 20 yard, 30 yard, 40 yard, or Not sure' );
                ?>
            </table>
        </div>

        <!-- Scrap Metal -->
        <div class="cftg-field-group">
            <h3><span class="cftg-form-badge cftg-badge-scrap">Scrap Metal</span> Custom Fields</h3>
            <p class="description">Used by shortcode <code>[cftg_scrap_metal]</code></p>
            <table class="form-table cftg-form-table">
                <?php
                cftg_field_row( 'cftg_cf_scrap_types', 'Scrap Types', 'TEXT (Multi-line)', 'Materials selected: Copper, Brass, Wire, Steel…' );
                ?>
            </table>
        </div>

        <!-- Vehicle Quote -->
        <div class="cftg-field-group">
            <h3><span class="cftg-form-badge cftg-badge-vehicle">Vehicle Quote</span> Custom Fields</h3>
            <p class="description">Used by shortcode <code>[cftg_vehicle_quote]</code></p>
            <table class="form-table cftg-form-table">
                <?php
                cftg_field_row( 'cftg_cf_vehicle_year',        'Vehicle Year',             'TEXT',          'e.g. 2018' );
                cftg_field_row( 'cftg_cf_vehicle_make',        'Vehicle Make',             'TEXT',          'e.g. Honda' );
                cftg_field_row( 'cftg_cf_vehicle_model',       'Vehicle Model',            'TEXT',          'e.g. Civic' );
                cftg_field_row( 'cftg_cf_engine_running',      'Engine Running',           'TEXT / RADIO',  'Yes or No' );
                cftg_field_row( 'cftg_cf_parts_missing',       'Parts Missing',            'TEXT / RADIO',  'Yes or No' );
                cftg_field_row( 'cftg_cf_missing_parts_notes', 'Missing Parts Description','TEXT',          'Description of missing parts' );
                ?>
            </table>
        </div>
    </div>
    <?php
}

function cftg_field_row( string $option, string $label, string $type, string $desc ) {
    $val = get_option( $option, '' );
    ?>
    <tr>
        <th scope="row">
            <label for="<?php echo esc_attr( $option ); ?>"><?php echo esc_html( $label ); ?></label>
            <span class="cftg-type-badge"><?php echo esc_html( $type ); ?></span>
        </th>
        <td>
            <input type="text" id="<?php echo esc_attr( $option ); ?>"
                   name="<?php echo esc_attr( $option ); ?>"
                   value="<?php echo esc_attr( $val ); ?>"
                   class="regular-text cftg-field-id-input"
                   placeholder="Paste GHL custom field ID here">
            <p class="description"><?php echo esc_html( $desc ); ?></p>
        </td>
    </tr>
    <?php
}

/* ──────────────────────────────────────────────
   TAB: Shortcodes
   ────────────────────────────────────────────── */
function cftg_tab_shortcodes() {
    $forms = [
        [
            'name'      => 'Bin Dumpster Estimate',
            'shortcode' => '[cftg_bin_estimate]',
            'desc'      => '7-step form: dispose type, delivery date, duration, bin size, postal code, contact info.',
            'badge'     => 'cftg-badge-bin',
        ],
        [
            'name'      => 'Scrap Metal Estimate',
            'shortcode' => '[cftg_scrap_metal]',
            'desc'      => '3-step form: material selection, contact info.',
            'badge'     => 'cftg-badge-scrap',
        ],
        [
            'name'      => 'Vehicle Quote',
            'shortcode' => '[cftg_vehicle_quote]',
            'desc'      => '5-step form: vehicle details, engine status, missing parts, contact info.',
            'badge'     => 'cftg-badge-vehicle',
        ],
    ];
    ?>
    <div class="cftg-section">
        <div class="cftg-section-header">
            <h2>Shortcodes</h2>
            <p>Copy a shortcode and paste it into any page or post content area.</p>
        </div>
        <div class="cftg-shortcode-cards">
            <?php foreach ( $forms as $form ): ?>
            <div class="cftg-shortcode-card">
                <div class="cftg-sc-header">
                    <span class="cftg-form-badge <?php echo esc_attr( $form['badge'] ); ?>"><?php echo esc_html( $form['name'] ); ?></span>
                </div>
                <p><?php echo esc_html( $form['desc'] ); ?></p>
                <div class="cftg-sc-copy-row">
                    <code class="cftg-sc-code"><?php echo esc_html( $form['shortcode'] ); ?></code>
                    <button type="button" class="button cftg-copy-btn" data-code="<?php echo esc_attr( $form['shortcode'] ); ?>">
                        <span class="dashicons dashicons-clipboard"></span> Copy
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
}
