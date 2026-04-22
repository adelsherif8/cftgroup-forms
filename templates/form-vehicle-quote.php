<?php if ( ! defined( 'ABSPATH' ) ) exit;
$uid    = 'cftg-' . wp_unique_id();
$d      = cftg_get_design( 'vehicle_quote' );
$styles = cftg_section_styles( 'vehicle_quote' );
?>
<div class="cftg-section" style="<?php echo $styles['section']; ?>">
  <div class="cftg-overlay" style="<?php echo $styles['overlay']; ?>"></div>

  <div class="cftg-wrap" id="<?php echo esc_attr( $uid ); ?>" data-form-type="vehicle_quote" data-total="4">

    <!-- ── Left panel ── -->
    <div class="cftg-left">
      <div class="cftg-logo">
        <img src="https://cftgroup.ca/wp-content/uploads/2024/09/cft-group-logo.png" alt="CFT Group">
      </div>
      <div class="cftg-badge"><i class="fa-solid fa-tag"></i> <?php echo esc_html( $d['badge'] ); ?></div>
      <h2 class="cftg-title"><?php echo esc_html( $d['title'] ); ?> <span class="cftg-accent"><?php echo esc_html( $d['title_accent'] ); ?></span></h2>
      <p class="cftg-desc"><?php echo esc_html( $d['desc'] ); ?></p>
      <div class="cftg-feat-grid">
        <div class="cftg-feat-item"><i class="fa-solid fa-check"></i> <?php echo esc_html( $d['feat_1'] ); ?></div>
        <div class="cftg-feat-item"><i class="fa-solid fa-check"></i> <?php echo esc_html( $d['feat_2'] ); ?></div>
        <div class="cftg-feat-item"><i class="fa-solid fa-check"></i> <?php echo esc_html( $d['feat_3'] ); ?></div>
        <div class="cftg-feat-item"><i class="fa-solid fa-check"></i> <?php echo esc_html( $d['feat_4'] ); ?></div>
      </div>
      <div class="cftg-contact-row">
        <div class="cftg-contact-item"><i class="fa-solid fa-phone"></i> <?php echo esc_html( $d['phone'] ); ?></div>
        <div class="cftg-contact-item"><i class="fa-solid fa-envelope"></i> <?php echo esc_html( $d['email'] ?? 'info@cftgroup.ca' ); ?></div>
        <div class="cftg-contact-item"><i class="fa-solid fa-clock"></i> <?php echo esc_html( $d['hours'] ); ?></div>
      </div>
    </div>

    <!-- ── Right: white card ── -->
    <div class="cftg-card">
      <div class="cftg-card-header">
        <div class="cftg-step-info">
          <span class="cftg-step-label">Step 1 of 4</span>
          <span class="cftg-pct-label">0%</span>
        </div>
        <div class="cftg-prog-track"><div class="cftg-prog-fill" style="width:0%"></div></div>
      </div>

      <div class="cftg-card-body">

        <!-- Step 1: Vehicle details -->
        <div class="cftg-step active" data-step="1">
          <div class="cftg-vehicle-banner">
            <div class="cftg-vehicle-banner-icon"><i class="fa-solid fa-car"></i></div>
            <div><strong>Tell us about your vehicle</strong><span>Year, make and model — takes 60 seconds</span></div>
          </div>
          <div class="cftg-vehicle-grid">
            <div class="cftg-field"><label class="cftg-label">Vehicle Year</label><input type="text" class="cftg-input" name="vehicle_year" placeholder="2018"></div>
            <div class="cftg-field"><label class="cftg-label">Vehicle Make</label><input type="text" class="cftg-input" name="vehicle_make" placeholder="Honda"></div>
            <div class="cftg-field"><label class="cftg-label">Vehicle Model</label><input type="text" class="cftg-input" name="vehicle_model" placeholder="Civic"></div>
          </div>
          <div class="cftg-actions">
            <button class="cftg-btn-next" type="button">Get My Estimate <i class="fa-solid fa-arrow-right"></i></button>
          </div>
        </div>

        <!-- Step 2: Engine running? -->
        <div class="cftg-step" data-step="2">
          <h2 class="cftg-q-title">Is the engine running?</h2>
          <p class="cftg-q-sub">Let us know the current condition of your vehicle</p>
          <div class="cftg-yn-grid">
            <label class="cftg-yn yes-card"><input type="radio" name="engine_running" value="Yes">
              <div class="cftg-yn-body"><div class="cftg-yn-icon"><i class="fa-solid fa-circle-check"></i></div><span class="cftg-yn-label">Yes</span></div>
            </label>
            <label class="cftg-yn no-card"><input type="radio" name="engine_running" value="No">
              <div class="cftg-yn-body"><div class="cftg-yn-icon"><i class="fa-solid fa-circle-xmark"></i></div><span class="cftg-yn-label">No</span></div>
            </label>
          </div>
          <div class="cftg-actions">
            <button class="cftg-btn-back" type="button"><i class="fa-solid fa-arrow-left"></i> Back</button>
            <button class="cftg-btn-next" type="button">Continue <i class="fa-solid fa-arrow-right"></i></button>
          </div>
        </div>

        <!-- Step 3: Parts missing? -->
        <div class="cftg-step" data-step="3">
          <h2 class="cftg-q-title">Are parts missing?</h2>
          <p class="cftg-q-sub">This helps us give you the most accurate quote</p>
          <div class="cftg-yn-grid">
            <label class="cftg-yn gold-card"><input type="radio" name="parts_missing" value="Yes" class="cftg-toggle-missing">
              <div class="cftg-yn-body"><div class="cftg-yn-icon"><i class="fa-solid fa-wrench"></i></div><span class="cftg-yn-label">Yes</span></div>
            </label>
            <label class="cftg-yn no-card"><input type="radio" name="parts_missing" value="No" class="cftg-toggle-missing">
              <div class="cftg-yn-body"><div class="cftg-yn-icon"><i class="fa-solid fa-circle-check"></i></div><span class="cftg-yn-label">No</span></div>
            </label>
          </div>
          <div class="cftg-conditional-field" style="display:none">
            <div class="cftg-field">
              <label class="cftg-label">What's missing?</label>
              <input type="text" class="cftg-input" name="whats_missing" placeholder="e.g. Engine, catalytic converter, tires…">
            </div>
          </div>
          <div class="cftg-actions">
            <button class="cftg-btn-back" type="button"><i class="fa-solid fa-arrow-left"></i> Back</button>
            <button class="cftg-btn-next" type="button">Continue <i class="fa-solid fa-arrow-right"></i></button>
          </div>
        </div>

        <!-- Step 4: Contact -->
        <div class="cftg-step" data-step="4">
          <h2 class="cftg-q-title">Almost there!</h2>
          <p class="cftg-q-sub">Enter your contact details to receive your quote</p>
          <div class="cftg-row">
            <div class="cftg-field"><label class="cftg-label">First Name</label><input type="text" class="cftg-input" name="first_name" placeholder="John"></div>
            <div class="cftg-field"><label class="cftg-label">Last Name</label><input type="text" class="cftg-input" name="last_name" placeholder="Smith"></div>
          </div>
          <div class="cftg-field"><label class="cftg-label">Email Address</label><input type="email" class="cftg-input" name="email" placeholder="john@example.com"></div>
          <div class="cftg-field"><label class="cftg-label">Phone Number</label><input type="tel" class="cftg-input" name="phone" placeholder="+1 (416) 555-0100"></div>
          <div class="cftg-field"><label class="cftg-label">Postal Code</label><input type="text" class="cftg-input" name="postal" placeholder="e.g. M5V 3A8"></div>
          <div class="cftg-actions">
            <button class="cftg-btn-back" type="button"><i class="fa-solid fa-arrow-left"></i> Back</button>
            <button class="cftg-btn-submit" type="button">Get My Quote <i class="fa-solid fa-paper-plane"></i></button>
          </div>
        </div>

        <!-- Success -->
        <div class="cftg-step" data-step="5">
          <div class="cftg-success">
            <div class="cftg-success-ring"><i class="fa-solid fa-check"></i></div>
            <h2>Quote Request Sent!</h2>
            <p>Our team will review your vehicle details and reach out with a cash quote shortly.</p>
          </div>
        </div>

        <div class="cftg-error-msg" style="display:none"></div>
      </div>
    </div>

  </div>
</div>
