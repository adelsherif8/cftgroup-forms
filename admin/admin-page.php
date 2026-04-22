<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* ── Save settings ── */
add_action( 'admin_post_cftg_save_settings', 'cftg_handle_save_settings' );
function cftg_handle_save_settings() {
  if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );
  check_admin_referer( 'cftg_save_settings' );

  /* GHL + custom field text options */
  $text_fields = [
    'cftg_ghl_api_key', 'cftg_ghl_location_id',
    'cftg_cf_dispose_types', 'cftg_cf_delivery_date', 'cftg_cf_bin_duration', 'cftg_cf_bin_size',
    'cftg_cf_scrap_types',
    'cftg_cf_vehicle_year', 'cftg_cf_vehicle_make', 'cftg_cf_vehicle_model',
    'cftg_cf_engine_running', 'cftg_cf_parts_missing', 'cftg_cf_missing_parts_notes',
  ];
  foreach ( $text_fields as $key ) {
    if ( isset( $_POST[ $key ] ) ) update_option( $key, sanitize_text_field( $_POST[ $key ] ) );
  }

  /* Form design settings */
  foreach ( [ 'vehicle_quote', 'scrap_metal', 'bin_estimate' ] as $ft ) {
    $key = "cftg_design_{$ft}";
    if ( isset( $_POST[ $key ] ) && is_array( $_POST[ $key ] ) ) {
      $v = $_POST[ $key ];
      update_option( $key, [
        'bg_image'        => esc_url_raw( $v['bg_image'] ?? '' ),
        'overlay_color_l' => sanitize_hex_color( $v['overlay_color_l'] ?? '#0a0a0a' ) ?: '#0a0a0a',
        'overlay_color_r' => sanitize_hex_color( $v['overlay_color_r'] ?? '#0f1520' ) ?: '#0f1520',
        'overlay_opacity' => max( 0, min( 100, intval( $v['overlay_opacity'] ?? 75 ) ) ),
        'accent_color'    => sanitize_hex_color( $v['accent_color'] ?? '#eeae00' ) ?: '#eeae00',
        'badge'           => sanitize_text_field( $v['badge'] ?? '' ),
        'title'           => sanitize_text_field( $v['title'] ?? '' ),
        'title_accent'    => sanitize_text_field( $v['title_accent'] ?? '' ),
        'desc'            => sanitize_textarea_field( $v['desc'] ?? '' ),
        'feat_1'          => sanitize_text_field( $v['feat_1'] ?? '' ),
        'feat_2'          => sanitize_text_field( $v['feat_2'] ?? '' ),
        'feat_3'          => sanitize_text_field( $v['feat_3'] ?? '' ),
        'feat_4'          => sanitize_text_field( $v['feat_4'] ?? '' ),
        'phone'           => sanitize_text_field( $v['phone'] ?? '' ),
        'email'           => sanitize_email( $v['email'] ?? '' ),
        'hours'           => sanitize_text_field( $v['hours'] ?? '' ),
      ] );
    }
  }

  wp_redirect( admin_url( 'admin.php?page=cftg-settings&saved=1&tab=' . sanitize_key( $_POST['tab'] ?? 'connection' ) ) );
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

    <nav class="cftg-tabs nav-tab-wrapper">
      <?php
      $tabs = [
        'connection' => [ 'dashicons-admin-network', 'GHL Connection' ],
        'fields'     => [ 'dashicons-list-view',     'Custom Fields'  ],
        'design'     => [ 'dashicons-admin-customizer','Form Design'  ],
        'shortcodes' => [ 'dashicons-shortcode',     'Shortcodes'     ],
      ];
      foreach ( $tabs as $slug => [ $icon, $label ] ):
      ?>
      <a href="?page=cftg-settings&tab=<?php echo $slug; ?>" class="nav-tab <?php echo $tab === $slug ? 'nav-tab-active' : ''; ?>">
        <span class="dashicons <?php echo $icon; ?>"></span> <?php echo $label; ?>
      </a>
      <?php endforeach; ?>
    </nav>

    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
      <?php wp_nonce_field( 'cftg_save_settings' ); ?>
      <input type="hidden" name="action" value="cftg_save_settings">
      <input type="hidden" name="tab"    value="<?php echo esc_attr( $tab ); ?>">

      <?php if ( $tab === 'connection' ) cftg_tab_connection(); ?>
      <?php if ( $tab === 'fields' )     cftg_tab_fields();      ?>
      <?php if ( $tab === 'design' )     cftg_tab_design();      ?>
      <?php if ( $tab === 'shortcodes' ) cftg_tab_shortcodes();  ?>

      <?php if ( $tab !== 'shortcodes' ): ?>
        <p class="submit">
          <button type="submit" class="button button-primary button-hero">Save Settings</button>
        </p>
      <?php endif; ?>
    </form>
  </div>
  <?php
}

/* ── TAB: GHL Connection ── */
function cftg_tab_connection() {
  $api_key     = get_option( 'cftg_ghl_api_key', '' );
  $location_id = get_option( 'cftg_ghl_location_id', '' );
  ?>
  <div class="cftg-section">
    <div class="cftg-section-header">
      <h2>GoHighLevel Connection</h2>
      <p>Connect your forms to your GHL account.</p>
    </div>
    <table class="form-table cftg-form-table">
      <tr>
        <th><label for="cftg_ghl_location_id">Location ID</label></th>
        <td>
          <input type="text" id="cftg_ghl_location_id" name="cftg_ghl_location_id" value="<?php echo esc_attr( $location_id ); ?>" class="regular-text" placeholder="e.g. AbCdEf1234567890">
          <p class="description">GHL → Settings → Business Info → Location ID</p>
        </td>
      </tr>
      <tr>
        <th><label for="cftg_ghl_api_key">Private API Key</label></th>
        <td>
          <input type="password" id="cftg_ghl_api_key" name="cftg_ghl_api_key" value="<?php echo esc_attr( $api_key ); ?>" class="regular-text" placeholder="eyJ...">
          <button type="button" class="button" id="cftg-toggle-key" style="margin-left:8px">Show</button>
          <p class="description">GHL → Settings → Business Info → Private Integration → API Key</p>
        </td>
      </tr>
    </table>
    <div class="cftg-test-box">
      <button type="button" id="cftg-test-btn" class="button button-secondary">
        <span class="dashicons dashicons-update" style="vertical-align:middle;margin-right:4px"></span> Test Connection
      </button>
      <span id="cftg-test-result" class="cftg-test-result"></span>
    </div>
  </div>
  <?php
}

/* ── TAB: Custom Fields ── */
function cftg_tab_fields() { ?>
  <div class="cftg-section">
    <div class="cftg-section-header">
      <h2>GHL Custom Field IDs</h2>
      <p>Paste each field's ID from GHL → Settings → Custom Fields → Contacts.</p>
    </div>
    <div class="cftg-field-group">
      <h3><span class="cftg-form-badge cftg-badge-bin">Bin Estimate</span></h3>
      <table class="form-table cftg-form-table">
        <?php
        cftg_field_row( 'cftg_cf_dispose_types', 'Dispose Types',   'TEXT', 'Items to dispose' );
        cftg_field_row( 'cftg_cf_delivery_date', 'Delivery Date',   'DATE', 'When the bin is needed' );
        cftg_field_row( 'cftg_cf_bin_duration',  'Rental Duration', 'TEXT', 'How long: One day, One week…' );
        cftg_field_row( 'cftg_cf_bin_size',      'Bin Size',        'TEXT', '10 yard, 20 yard…' );
        ?>
      </table>
    </div>
    <div class="cftg-field-group">
      <h3><span class="cftg-form-badge cftg-badge-scrap">Scrap Metal</span></h3>
      <table class="form-table cftg-form-table">
        <?php cftg_field_row( 'cftg_cf_scrap_types', 'Scrap Types', 'TEXT', 'Materials selected' ); ?>
      </table>
    </div>
    <div class="cftg-field-group">
      <h3><span class="cftg-form-badge cftg-badge-vehicle">Vehicle Quote</span></h3>
      <table class="form-table cftg-form-table">
        <?php
        cftg_field_row( 'cftg_cf_vehicle_year',        'Vehicle Year',              'TEXT', 'e.g. 2018' );
        cftg_field_row( 'cftg_cf_vehicle_make',        'Vehicle Make',              'TEXT', 'e.g. Honda' );
        cftg_field_row( 'cftg_cf_vehicle_model',       'Vehicle Model',             'TEXT', 'e.g. Civic' );
        cftg_field_row( 'cftg_cf_engine_running',      'Engine Running',            'TEXT', 'Yes or No' );
        cftg_field_row( 'cftg_cf_parts_missing',       'Parts Missing',             'TEXT', 'Yes or No' );
        cftg_field_row( 'cftg_cf_missing_parts_notes', 'Missing Parts Description', 'TEXT', 'What is missing' );
        ?>
      </table>
    </div>
  </div>
  <?php
}

function cftg_field_row( $option, $label, $type, $desc ) {
  $val = get_option( $option, '' ); ?>
  <tr>
    <th><label for="<?php echo esc_attr( $option ); ?>"><?php echo esc_html( $label ); ?></label>
        <span class="cftg-type-badge"><?php echo esc_html( $type ); ?></span></th>
    <td>
      <input type="text" id="<?php echo esc_attr( $option ); ?>" name="<?php echo esc_attr( $option ); ?>"
             value="<?php echo esc_attr( $val ); ?>" class="regular-text cftg-field-id-input" placeholder="Paste GHL custom field ID here">
      <p class="description"><?php echo esc_html( $desc ); ?></p>
    </td>
  </tr>
  <?php
}

/* ── TAB: Form Design ── */
function cftg_tab_design() {
  $forms = [
    'vehicle_quote' => 'Vehicle Quote',
    'scrap_metal'   => 'Scrap Metal',
    'bin_estimate'  => 'Bin Estimate',
  ];
  $active = sanitize_key( $_GET['design_form'] ?? 'vehicle_quote' );
  if ( ! array_key_exists( $active, $forms ) ) $active = 'vehicle_quote';
  ?>
  <div class="cftg-design-tab-nav">
    <?php foreach ( $forms as $ft => $label ): ?>
    <a href="?page=cftg-settings&tab=design&design_form=<?php echo $ft; ?>"
       class="cftg-design-tab-btn <?php echo $active === $ft ? 'active' : ''; ?>">
      <?php echo esc_html( $label ); ?>
    </a>
    <?php endforeach; ?>
  </div>

  <?php foreach ( $forms as $ft => $label ): ?>
    <?php if ( $active === $ft ) cftg_design_form_panel( $ft, $label ); ?>
  <?php endforeach; ?>
  <?php
}

function cftg_design_form_panel( $ft, $label ) {
  $d = cftg_get_design( $ft );
  $n = "cftg_design_{$ft}";
  ?>
  <div class="cftg-section">
    <div class="cftg-section-header">
      <h2><?php echo esc_html( $label ); ?> — Design</h2>
      <p>Set the background, colours, and left-panel text for this form.</p>
    </div>

    <!-- Background Image -->
    <div class="cftg-design-row">
      <div class="cftg-design-label">
        <strong>Background Image</strong>
        <span>Displayed behind the whole form section</span>
      </div>
      <div class="cftg-design-control">
        <div class="cftg-img-picker">
          <?php if ( $d['bg_image'] ): ?>
          <img id="cftg-preview-<?php echo $ft; ?>" src="<?php echo esc_url( $d['bg_image'] ); ?>" class="cftg-img-preview">
          <?php else: ?>
          <img id="cftg-preview-<?php echo $ft; ?>" src="" class="cftg-img-preview" style="display:none">
          <?php endif; ?>
          <div class="cftg-img-actions">
            <input type="hidden" name="<?php echo $n; ?>[bg_image]" id="cftg-img-<?php echo $ft; ?>" value="<?php echo esc_attr( $d['bg_image'] ); ?>">
            <button type="button" class="button cftg-media-btn" data-target="cftg-img-<?php echo $ft; ?>" data-preview="cftg-preview-<?php echo $ft; ?>">
              <span class="dashicons dashicons-format-image"></span> Choose Image
            </button>
            <button type="button" class="button cftg-media-clear" data-target="cftg-img-<?php echo $ft; ?>" data-preview="cftg-preview-<?php echo $ft; ?>">Remove</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Overlay -->
    <div class="cftg-design-row">
      <div class="cftg-design-label">
        <strong>Overlay Gradient</strong>
        <span>Colour overlay on top of the image</span>
      </div>
      <div class="cftg-design-control cftg-overlay-row">
        <div class="cftg-color-field">
          <label>Left colour</label>
          <input type="color" name="<?php echo $n; ?>[overlay_color_l]" value="<?php echo esc_attr( $d['overlay_color_l'] ); ?>" class="cftg-color-input">
        </div>
        <div class="cftg-color-field">
          <label>Right colour</label>
          <input type="color" name="<?php echo $n; ?>[overlay_color_r]" value="<?php echo esc_attr( $d['overlay_color_r'] ); ?>" class="cftg-color-input">
        </div>
        <div class="cftg-color-field cftg-opacity-field">
          <label>Opacity <span class="cftg-opacity-val"><?php echo intval( $d['overlay_opacity'] ); ?>%</span></label>
          <input type="range" name="<?php echo $n; ?>[overlay_opacity]" min="0" max="100" value="<?php echo intval( $d['overlay_opacity'] ); ?>" class="cftg-opacity-range">
        </div>
      </div>
    </div>

    <!-- Accent colour -->
    <div class="cftg-design-row">
      <div class="cftg-design-label">
        <strong>Accent Colour</strong>
        <span>Buttons, progress bar, highlighted text</span>
      </div>
      <div class="cftg-design-control">
        <div class="cftg-color-field">
          <input type="color" name="<?php echo $n; ?>[accent_color]" value="<?php echo esc_attr( $d['accent_color'] ); ?>" class="cftg-color-input">
          <span class="cftg-color-label"><?php echo esc_html( $d['accent_color'] ); ?></span>
        </div>
      </div>
    </div>

    <hr class="cftg-divider">

    <!-- Left panel text -->
    <div class="cftg-design-row">
      <div class="cftg-design-label">
        <strong>Badge Text</strong>
        <span>Small pill above the title</span>
      </div>
      <div class="cftg-design-control">
        <input type="text" name="<?php echo $n; ?>[badge]" value="<?php echo esc_attr( $d['badge'] ); ?>" class="regular-text" placeholder="Free Quote">
      </div>
    </div>

    <div class="cftg-design-row">
      <div class="cftg-design-label">
        <strong>Title</strong>
        <span>Main heading (normal text)</span>
      </div>
      <div class="cftg-design-control">
        <input type="text" name="<?php echo $n; ?>[title]" value="<?php echo esc_attr( $d['title'] ); ?>" class="large-text" placeholder="Earn Money on Your">
      </div>
    </div>

    <div class="cftg-design-row">
      <div class="cftg-design-label">
        <strong>Title Accent</strong>
        <span>Highlighted part of the title (accent colour)</span>
      </div>
      <div class="cftg-design-control">
        <input type="text" name="<?php echo $n; ?>[title_accent]" value="<?php echo esc_attr( $d['title_accent'] ); ?>" class="large-text" placeholder="Scrap or Used Vehicle">
      </div>
    </div>

    <div class="cftg-design-row">
      <div class="cftg-design-label">
        <strong>Description</strong>
      </div>
      <div class="cftg-design-control">
        <textarea name="<?php echo $n; ?>[desc]" rows="3" class="large-text"><?php echo esc_textarea( $d['desc'] ); ?></textarea>
      </div>
    </div>

    <div class="cftg-design-row">
      <div class="cftg-design-label">
        <strong>Feature Items</strong>
        <span>4 bullet points on the left</span>
      </div>
      <div class="cftg-design-control cftg-feat-inputs">
        <input type="text" name="<?php echo $n; ?>[feat_1]" value="<?php echo esc_attr( $d['feat_1'] ); ?>" class="regular-text" placeholder="Feature 1">
        <input type="text" name="<?php echo $n; ?>[feat_2]" value="<?php echo esc_attr( $d['feat_2'] ); ?>" class="regular-text" placeholder="Feature 2">
        <input type="text" name="<?php echo $n; ?>[feat_3]" value="<?php echo esc_attr( $d['feat_3'] ); ?>" class="regular-text" placeholder="Feature 3">
        <input type="text" name="<?php echo $n; ?>[feat_4]" value="<?php echo esc_attr( $d['feat_4'] ); ?>" class="regular-text" placeholder="Feature 4">
      </div>
    </div>

    <div class="cftg-design-row">
      <div class="cftg-design-label">
        <strong>Contact Row</strong>
        <span>Phone and hours at the bottom of the left panel</span>
      </div>
      <div class="cftg-design-control cftg-contact-inputs">
        <div>
          <label>Phone</label>
          <input type="text" name="<?php echo $n; ?>[phone]" value="<?php echo esc_attr( $d['phone'] ); ?>" class="regular-text" placeholder="613-831-2900">
        </div>
        <div>
          <label>Email</label>
          <input type="email" name="<?php echo $n; ?>[email]" value="<?php echo esc_attr( $d['email'] ?? 'info@cftgroup.ca' ); ?>" class="regular-text" placeholder="info@cftgroup.ca">
        </div>
        <div>
          <label>Hours</label>
          <input type="text" name="<?php echo $n; ?>[hours]" value="<?php echo esc_attr( $d['hours'] ); ?>" class="regular-text" placeholder="Mon–Fri 8am–6pm">
        </div>
      </div>
    </div>

  </div>
  <?php
}

/* ── TAB: Shortcodes ── */
function cftg_tab_shortcodes() {
  $forms = [
    [ 'name' => 'Bin Dumpster Estimate', 'shortcode' => '[cftg_bin_estimate]',  'badge' => 'cftg-badge-bin' ],
    [ 'name' => 'Scrap Metal Estimate',  'shortcode' => '[cftg_scrap_metal]',   'badge' => 'cftg-badge-scrap' ],
    [ 'name' => 'Vehicle Quote',         'shortcode' => '[cftg_vehicle_quote]', 'badge' => 'cftg-badge-vehicle' ],
  ];
  ?>
  <div class="cftg-section">
    <div class="cftg-section-header">
      <h2>Shortcodes</h2>
      <p>Paste into any page or post content area.</p>
    </div>
    <div class="cftg-shortcode-cards">
      <?php foreach ( $forms as $form ): ?>
      <div class="cftg-shortcode-card">
        <span class="cftg-form-badge <?php echo esc_attr( $form['badge'] ); ?>"><?php echo esc_html( $form['name'] ); ?></span>
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
