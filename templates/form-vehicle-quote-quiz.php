<?php if ( ! defined( 'ABSPATH' ) ) exit;
$uid = 'cftg-quiz-' . wp_unique_id();
?>
<div id="<?php echo esc_attr( $uid ); ?>" class="cftg-quiz" data-form-type="vehicle_quote" data-total="5">

  <div class="qz-header">
    <div class="qz-header-brand">CFT Recycling</div>
  </div>

  <div class="qz-progress" style="display:none">
    <div class="qz-progress-row">
      <span class="qz-progress-text">Question 1 of 5</span>
      <span class="qz-progress-pct">20%</span>
    </div>
    <div class="qz-progress-track"><div class="qz-progress-fill"></div></div>
  </div>

  <div class="qz-main">
    <div class="qz-inner">

      <div style="position:absolute;left:-9999px" aria-hidden="true"><input type="text" name="website" tabindex="-1" autocomplete="off"></div>
      <input type="hidden" name="loaded_at" class="cftg-loaded-at">

      <!-- Step 0: Welcome -->
      <div class="qz-step active" data-step="0">
        <div class="qz-welcome">
          <div class="qz-welcome-icon"><i class="fa-solid fa-car"></i></div>
          <h1 class="qz-welcome-title">Get a cash offer for your vehicle</h1>
          <p class="qz-welcome-sub">Answer a few quick questions and we'll send you a fair, no-obligation cash offer for your scrap or used vehicle.</p>
          <p class="qz-welcome-note">Takes about 60 seconds. Free towing included.</p>
          <button type="button" class="qz-welcome-cta qz-btn-next">Start My Quote</button>
          <p class="qz-welcome-brand">CFT RECYCLING · OTTAWA, ON</p>
        </div>
      </div>

      <!-- Step 1: Vehicle year -->
      <div class="qz-step" data-step="1">
        <button type="button" class="qz-back qz-btn-back"><i class="fa-solid fa-arrow-left"></i> Back</button>
        <h2 class="qz-title no-sub">What year is the vehicle?</h2>
        <div class="qz-input-group">
          <input type="text" class="qz-input" name="vehicle_year" placeholder="e.g. 2015" inputmode="numeric" required>
        </div>
        <button type="button" class="qz-next qz-btn-next">Continue</button>
      </div>

      <!-- Step 2: Make & model -->
      <div class="qz-step" data-step="2">
        <button type="button" class="qz-back qz-btn-back"><i class="fa-solid fa-arrow-left"></i> Back</button>
        <h2 class="qz-title no-sub">What make and model?</h2>
        <div class="qz-input-group">
          <label class="qz-input-label">Make</label>
          <input type="text" class="qz-input" name="vehicle_make" placeholder="Honda" required>
        </div>
        <div class="qz-input-group">
          <label class="qz-input-label">Model</label>
          <input type="text" class="qz-input" name="vehicle_model" placeholder="Civic" required>
        </div>
        <button type="button" class="qz-next qz-btn-next">Continue</button>
      </div>

      <!-- Step 3: Engine running (auto-advance) -->
      <div class="qz-step" data-step="3">
        <button type="button" class="qz-back qz-btn-back"><i class="fa-solid fa-arrow-left"></i> Back</button>
        <h2 class="qz-title no-sub">Is the engine running?</h2>
        <div class="qz-options">
          <button type="button" class="qz-option" data-name="engine_running" data-value="Yes">
            <span class="qz-option-icon"><i class="fa-solid fa-circle-check"></i></span>
            <span class="qz-option-body"><span class="qz-option-name">Yes, it runs and drives</span></span>
          </button>
          <button type="button" class="qz-option" data-name="engine_running" data-value="No">
            <span class="qz-option-icon"><i class="fa-solid fa-circle-xmark"></i></span>
            <span class="qz-option-body"><span class="qz-option-name">No, it doesn't run</span></span>
          </button>
        </div>
      </div>

      <!-- Step 4: Parts missing (Yes reveals input) -->
      <div class="qz-step" data-step="4">
        <button type="button" class="qz-back qz-btn-back"><i class="fa-solid fa-arrow-left"></i> Back</button>
        <h2 class="qz-title no-sub">Are any parts missing?</h2>
        <div class="qz-options">
          <button type="button" class="qz-option" data-name="parts_missing" data-value="Yes" data-reveal="missing">
            <span class="qz-option-icon"><i class="fa-solid fa-wrench"></i></span>
            <span class="qz-option-body"><span class="qz-option-name">Yes, some parts are missing</span></span>
          </button>
          <button type="button" class="qz-option" data-name="parts_missing" data-value="No">
            <span class="qz-option-icon"><i class="fa-solid fa-circle-check"></i></span>
            <span class="qz-option-body"><span class="qz-option-name">No, everything is there</span></span>
          </button>
        </div>
        <div class="qz-reveal" data-reveal-id="missing">
          <div class="qz-input-group">
            <label class="qz-input-label">What's missing?</label>
            <input type="text" class="qz-input" name="whats_missing" placeholder="e.g. catalytic converter, tires…">
          </div>
          <button type="button" class="qz-next qz-btn-next">Continue</button>
        </div>
      </div>

      <!-- Step 5: Contact + postal -->
      <div class="qz-step" data-step="5">
        <button type="button" class="qz-back qz-btn-back"><i class="fa-solid fa-arrow-left"></i> Back</button>
        <h2 class="qz-title no-sub">Where should we send your offer?</h2>
        <div class="qz-row2">
          <div class="qz-input-group">
            <label class="qz-input-label">First name</label>
            <input type="text" class="qz-input" name="first_name" placeholder="John" required>
          </div>
          <div class="qz-input-group">
            <label class="qz-input-label">Last name</label>
            <input type="text" class="qz-input" name="last_name" placeholder="Smith" required>
          </div>
        </div>
        <div class="qz-input-group">
          <label class="qz-input-label">Email</label>
          <input type="email" class="qz-input" name="email" placeholder="john@example.com" required>
        </div>
        <div class="qz-input-group">
          <label class="qz-input-label">Phone</label>
          <input type="tel" class="qz-input" name="phone" placeholder="(613) 555-0100" required>
        </div>
        <div class="qz-input-group">
          <label class="qz-input-label">Postal code of pick-up location</label>
          <input type="text" class="qz-input" name="postal" placeholder="K1A 0B1" required>
        </div>
        <button type="button" class="qz-next qz-btn-submit">Get My Offer</button>
        <p class="qz-trust"><i class="fa-solid fa-lock"></i> No spam — we only use this to send your offer.</p>
        <div class="qz-error" style="display:none"></div>
      </div>

      <!-- Success -->
      <div class="qz-step" data-step="6">
        <div class="qz-success">
          <div class="qz-success-icon"><i class="fa-solid fa-check"></i></div>
          <h2 class="qz-success-title">Offer on its way!</h2>
          <p class="qz-success-text">We've received your details. Our team will review your vehicle and send a cash offer within one business day.</p>
        </div>
      </div>

    </div>
  </div>
</div>
