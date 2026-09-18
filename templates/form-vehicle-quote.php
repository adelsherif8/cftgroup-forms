<?php if ( ! defined( 'ABSPATH' ) ) exit;
$uid    = 'cftg-' . wp_unique_id();
$d      = cftg_get_design( 'vehicle_quote' );
$styles = cftg_section_styles( 'vehicle_quote' );
?>
<div class="cftg-section" style="<?php echo $styles['section']; ?>">
  <div class="cftg-overlay" style="<?php echo $styles['overlay']; ?>"></div>

  <div class="cftg-wrap" id="<?php echo esc_attr( $uid ); ?>" data-form-type="vehicle_quote" data-total="9">

    <!-- ── Left panel ── -->
    <div class="cftg-left">
      <div class="cftg-logo">
        <img src="https://cftgroup.ca/wp-content/uploads/2024/09/cft-group-logo.png" alt="CFT Group" style="height:<?php echo intval( $d['logo_size'] ?? 80 ); ?>px">
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
        <a class="cftg-contact-item" href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $d['phone'] ) ); ?>"><i class="fa-solid fa-phone"></i> <?php echo esc_html( $d['phone'] ); ?></a>
        <a class="cftg-contact-item" href="mailto:<?php echo esc_attr( $d['email'] ?? 'info@cftgroup.ca' ); ?>"><i class="fa-solid fa-envelope"></i> <?php echo esc_html( $d['email'] ?? 'info@cftgroup.ca' ); ?></a>
        <div class="cftg-contact-item"><i class="fa-solid fa-clock"></i> <?php echo esc_html( $d['hours'] ); ?></div>
      </div>
    </div>

    <!-- ── Right: white card ── -->
    <div class="cftg-card">
      <div class="cftg-card-header">
        <div class="cftg-step-info">
          <span class="cftg-pct-label">0%</span>
        </div>
        <div class="cftg-prog-track"><div class="cftg-prog-fill" style="width:0%"></div></div>
      </div>

      <div class="cftg-card-body">
        <div class="cftg-hp" aria-hidden="true"><input type="text" name="website" tabindex="-1" autocomplete="off"></div>
        <input type="hidden" name="loaded_at" class="cftg-loaded-at">

        <!-- Step 1: Vehicle details -->
        <div class="cftg-step active" data-step="1">
          <div class="cftg-vehicle-banner">
            <div class="cftg-vehicle-banner-icon"><i class="fa-solid fa-car"></i></div>
            <div><strong>Tell us about your vehicle</strong><span>Year, make, model and trim — takes 60 seconds</span></div>
          </div>
          <div class="cftg-vehicle-grid">
            <div class="cftg-field"><label class="cftg-label">Vehicle Year</label><input type="text" class="cftg-input" name="vehicle_year" inputmode="numeric" placeholder="2018" required></div>
            <div class="cftg-field"><label class="cftg-label">Vehicle Make</label><input type="text" class="cftg-input" name="vehicle_make" placeholder="Honda" required></div>
            <div class="cftg-field"><label class="cftg-label">Vehicle Model</label><input type="text" class="cftg-input" name="vehicle_model" placeholder="Civic" required></div>
            <!-- Optional: plenty of sellers don't know their trim, and a missing
                 trim shouldn't cost us the lead. -->
            <div class="cftg-field"><label class="cftg-label">Vehicle Trim <span class="cftg-optional">(if you know it)</span></label><input type="text" class="cftg-input" name="vehicle_trim" placeholder="EX, LX, Sport…"></div>
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

        <!-- Step 3: Catalytic converter? -->
        <!-- "Not sure" is offered on purpose: sellers often can't tell, and a
             guessed "Yes" on a missing converter is worse than an honest unknown. -->
        <div class="cftg-step" data-step="3">
          <h2 class="cftg-q-title">Does it have a catalytic converter?</h2>
          <p class="cftg-q-sub">It sits under the car between the engine and the muffler</p>
          <div class="cftg-yn-grid cftg-yn-3">
            <label class="cftg-yn yes-card"><input type="radio" name="catalytic_converter" value="Yes">
              <div class="cftg-yn-body"><div class="cftg-yn-icon"><i class="fa-solid fa-circle-check"></i></div><span class="cftg-yn-label">Yes</span></div>
            </label>
            <label class="cftg-yn no-card"><input type="radio" name="catalytic_converter" value="No">
              <div class="cftg-yn-body"><div class="cftg-yn-icon"><i class="fa-solid fa-circle-xmark"></i></div><span class="cftg-yn-label">No</span></div>
            </label>
            <label class="cftg-yn gold-card"><input type="radio" name="catalytic_converter" value="Not sure">
              <div class="cftg-yn-body"><div class="cftg-yn-icon"><i class="fa-solid fa-circle-question"></i></div><span class="cftg-yn-label">Not sure</span></div>
            </label>
          </div>
          <div class="cftg-actions">
            <button class="cftg-btn-back" type="button"><i class="fa-solid fa-arrow-left"></i> Back</button>
            <button class="cftg-btn-next" type="button">Continue <i class="fa-solid fa-arrow-right"></i></button>
          </div>
        </div>

        <!-- Step 4: Battery? -->
        <div class="cftg-step" data-step="4">
          <h2 class="cftg-q-title">Does it have a battery?</h2>
          <p class="cftg-q-sub">Even a dead battery counts</p>
          <div class="cftg-yn-grid cftg-yn-3">
            <label class="cftg-yn yes-card"><input type="radio" name="has_battery" value="Yes">
              <div class="cftg-yn-body"><div class="cftg-yn-icon"><i class="fa-solid fa-circle-check"></i></div><span class="cftg-yn-label">Yes</span></div>
            </label>
            <label class="cftg-yn no-card"><input type="radio" name="has_battery" value="No">
              <div class="cftg-yn-body"><div class="cftg-yn-icon"><i class="fa-solid fa-circle-xmark"></i></div><span class="cftg-yn-label">No</span></div>
            </label>
            <label class="cftg-yn gold-card"><input type="radio" name="has_battery" value="Not sure">
              <div class="cftg-yn-body"><div class="cftg-yn-icon"><i class="fa-solid fa-circle-question"></i></div><span class="cftg-yn-label">Not sure</span></div>
            </label>
          </div>
          <div class="cftg-actions">
            <button class="cftg-btn-back" type="button"><i class="fa-solid fa-arrow-left"></i> Back</button>
            <button class="cftg-btn-next" type="button">Continue <i class="fa-solid fa-arrow-right"></i></button>
          </div>
        </div>

        <!-- Step 5: Mileage -->
        <div class="cftg-step" data-step="5">
          <h2 class="cftg-q-title">What is the current mileage?</h2>
          <p class="cftg-q-sub">The number on the odometer, in kilometres</p>
          <div class="cftg-field"><label class="cftg-label">Mileage (km)</label><input type="text" class="cftg-input" name="vehicle_mileage" inputmode="numeric" placeholder="e.g. 185,000" required></div>
          <p class="cftg-hint">An estimate is fine.</p>
          <div class="cftg-actions">
            <button class="cftg-btn-back" type="button"><i class="fa-solid fa-arrow-left"></i> Back</button>
            <button class="cftg-btn-next" type="button">Continue <i class="fa-solid fa-arrow-right"></i></button>
          </div>
        </div>

        <!-- Step 6: Parts missing? -->
        <div class="cftg-step" data-step="6">
          <h2 class="cftg-q-title">Are there any missing parts?</h2>
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
              <input type="text" class="cftg-input" name="whats_missing" placeholder="e.g. Engine, wheels, doors…" required>
            </div>
          </div>
          <div class="cftg-actions">
            <button class="cftg-btn-back" type="button"><i class="fa-solid fa-arrow-left"></i> Back</button>
            <button class="cftg-btn-next" type="button">Continue <i class="fa-solid fa-arrow-right"></i></button>
          </div>
        </div>

        <!-- Step 7: Rims -->
        <div class="cftg-step" data-step="7">
          <h2 class="cftg-q-title">Is it on steel or aluminium rims?</h2>
          <p class="cftg-q-sub">Aluminium rims are usually shiny and styled; steel rims are plain, often with hubcaps</p>
          <div class="cftg-yn-grid cftg-yn-3">
            <label class="cftg-yn gold-card"><input type="radio" name="rim_type" value="Steel">
              <div class="cftg-yn-body"><div class="cftg-yn-icon"><i class="fa-solid fa-circle-dot"></i></div><span class="cftg-yn-label">Steel</span></div>
            </label>
            <label class="cftg-yn gold-card"><input type="radio" name="rim_type" value="Aluminium">
              <div class="cftg-yn-body"><div class="cftg-yn-icon"><i class="fa-solid fa-life-ring"></i></div><span class="cftg-yn-label">Aluminium</span></div>
            </label>
            <label class="cftg-yn gold-card"><input type="radio" name="rim_type" value="Not sure">
              <div class="cftg-yn-body"><div class="cftg-yn-icon"><i class="fa-solid fa-circle-question"></i></div><span class="cftg-yn-label">Not sure</span></div>
            </label>
          </div>
          <div class="cftg-actions">
            <button class="cftg-btn-back" type="button"><i class="fa-solid fa-arrow-left"></i> Back</button>
            <button class="cftg-btn-next" type="button">Continue <i class="fa-solid fa-arrow-right"></i></button>
          </div>
        </div>

        <!-- Step 8: Pick-up or drop-off -->
        <div class="cftg-step" data-step="8">
          <h2 class="cftg-q-title">Does it need to be picked up?</h2>
          <p class="cftg-q-sub">Or can it be driven to one of our yards?</p>
          <div class="cftg-yn-grid">
            <label class="cftg-yn gold-card"><input type="radio" name="pickup_or_dropoff" value="Needs pick-up">
              <div class="cftg-yn-body"><div class="cftg-yn-icon"><i class="fa-solid fa-truck-pickup"></i></div><span class="cftg-yn-label">Needs pick-up</span></div>
            </label>
            <label class="cftg-yn gold-card"><input type="radio" name="pickup_or_dropoff" value="Will drive to a yard">
              <div class="cftg-yn-body"><div class="cftg-yn-icon"><i class="fa-solid fa-road"></i></div><span class="cftg-yn-label">I can drive it in</span></div>
            </label>
          </div>
          <div class="cftg-actions">
            <button class="cftg-btn-back" type="button"><i class="fa-solid fa-arrow-left"></i> Back</button>
            <button class="cftg-btn-next" type="button">Continue <i class="fa-solid fa-arrow-right"></i></button>
          </div>
        </div>

        <!-- Step 9: Contact + vehicle location -->
        <div class="cftg-step" data-step="9">
          <h2 class="cftg-q-title">Almost there!</h2>
          <p class="cftg-q-sub">Where should we send your quote?</p>
          <div class="cftg-row">
            <div class="cftg-field"><label class="cftg-label">First Name</label><input type="text" class="cftg-input" name="first_name" autocomplete="given-name" placeholder="John" required></div>
            <div class="cftg-field"><label class="cftg-label">Last Name</label><input type="text" class="cftg-input" name="last_name" autocomplete="family-name" placeholder="Smith" required></div>
          </div>
          <div class="cftg-row">
            <div class="cftg-field"><label class="cftg-label">Phone Number</label><input type="tel" class="cftg-input" name="phone" autocomplete="tel" placeholder="+1 (613) 555-0100" required></div>
            <div class="cftg-field"><label class="cftg-label">Email Address</label><input type="email" class="cftg-input" name="email" autocomplete="email" placeholder="john@example.com" required></div>
          </div>
          <p class="cftg-group-label">Where is the vehicle?</p>
          <div class="cftg-field"><label class="cftg-label">Street Address</label><input type="text" class="cftg-input" name="address" autocomplete="street-address" placeholder="123 Main St" required></div>
          <div class="cftg-row">
            <div class="cftg-field"><label class="cftg-label">City</label><input type="text" class="cftg-input" name="city" autocomplete="address-level2" placeholder="Ottawa" required></div>
            <div class="cftg-field"><label class="cftg-label">Postal Code</label><input type="text" class="cftg-input" name="postal" autocomplete="postal-code" placeholder="K1A 0B1" required></div>
          </div>
          <div class="cftg-actions">
            <button class="cftg-btn-back" type="button"><i class="fa-solid fa-arrow-left"></i> Back</button>
            <button class="cftg-btn-submit" type="button">Get My Quote <i class="fa-solid fa-paper-plane"></i></button>
          </div>
        </div>

        <!-- Success -->
        <div class="cftg-step" data-step="10">
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
