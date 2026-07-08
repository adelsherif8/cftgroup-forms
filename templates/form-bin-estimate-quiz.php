<?php if ( ! defined( 'ABSPATH' ) ) exit;
$uid = 'cftg-quiz-' . wp_unique_id();
?>
<div id="<?php echo esc_attr( $uid ); ?>" class="cftg-quiz" data-form-type="bin_estimate" data-total="5">

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
          <div class="qz-welcome-icon"><i class="fa-solid fa-dumpster"></i></div>
          <h1 class="qz-welcome-title">Get your bin rental estimate</h1>
          <p class="qz-welcome-sub">Tell us what you're clearing out and we'll size the right bin and give you a fast, fair estimate.</p>
          <p class="qz-welcome-note">Takes about 60 seconds. Prompt delivery, no hidden fees.</p>
          <button type="button" class="qz-welcome-cta qz-btn-next">Start My Estimate</button>
          <p class="qz-welcome-brand">CFT RECYCLING · OTTAWA, ON</p>
        </div>
      </div>

      <!-- Step 1: Dispose types (multi-select) -->
      <div class="qz-step" data-step="1">
        <button type="button" class="qz-back qz-btn-back"><i class="fa-solid fa-arrow-left"></i> Back</button>
        <h2 class="qz-title">What do you need to dispose?</h2>
        <p class="qz-sub">Select all that apply</p>
        <div class="qz-options">
          <?php
          $items = [
            [ 'Garbage', 'fa-trash-can' ],
            [ 'Metal', 'fa-gear' ],
            [ 'Construction Debris', 'fa-helmet-safety' ],
            [ 'Dirt', 'fa-hill-rockslide' ],
            [ 'Concrete', 'fa-cubes' ],
            [ 'Other', 'fa-box-open' ],
          ];
          foreach ( $items as [ $label, $icon ] ) : ?>
          <button type="button" class="qz-option" data-multi data-name="dispose_types" data-value="<?php echo esc_attr( $label ); ?>">
            <span class="qz-option-icon"><i class="fa-solid <?php echo esc_attr( $icon ); ?>"></i></span>
            <span class="qz-option-body"><span class="qz-option-name"><?php echo esc_html( $label ); ?></span></span>
            <span class="qz-check"><i class="fa-solid fa-check"></i></span>
          </button>
          <?php endforeach; ?>
        </div>
        <button type="button" class="qz-next qz-btn-next">Continue</button>
        <div class="qz-error" style="display:none"></div>
      </div>

      <!-- Step 2: Delivery date -->
      <div class="qz-step" data-step="2">
        <button type="button" class="qz-back qz-btn-back"><i class="fa-solid fa-arrow-left"></i> Back</button>
        <h2 class="qz-title no-sub">When do you need your bin?</h2>
        <div class="qz-input-group">
          <label class="qz-input-label">Preferred delivery date</label>
          <input type="date" class="qz-input" name="delivery_date" required>
        </div>
        <button type="button" class="qz-next qz-btn-next">Continue</button>
      </div>

      <!-- Step 3: Duration (auto-advance) -->
      <div class="qz-step" data-step="3">
        <button type="button" class="qz-back qz-btn-back"><i class="fa-solid fa-arrow-left"></i> Back</button>
        <h2 class="qz-title no-sub">How long do you need the bin for?</h2>
        <div class="qz-options">
          <?php
          $durations = [
            [ 'One day', 'fa-sun' ],
            [ 'Couple of days', 'fa-calendar-day' ],
            [ 'One week', 'fa-calendar-week' ],
            [ 'Couple of weeks', 'fa-calendar-days' ],
            [ 'More than a month', 'fa-infinity' ],
            [ 'Not sure', 'fa-circle-question' ],
          ];
          foreach ( $durations as [ $label, $icon ] ) : ?>
          <button type="button" class="qz-option" data-name="bin_duration" data-value="<?php echo esc_attr( $label ); ?>">
            <span class="qz-option-icon"><i class="fa-solid <?php echo esc_attr( $icon ); ?>"></i></span>
            <span class="qz-option-body"><span class="qz-option-name"><?php echo esc_html( $label ); ?></span></span>
          </button>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Step 4: Bin size (auto-advance) -->
      <div class="qz-step" data-step="4">
        <button type="button" class="qz-back qz-btn-back"><i class="fa-solid fa-arrow-left"></i> Back</button>
        <h2 class="qz-title no-sub">What size do you need?</h2>
        <div class="qz-options">
          <?php
          $sizes = [
            [ '10 yard', '10ft × 8ft × 4ft' ],
            [ '20 yard', '20ft × 8ft × 3.5ft' ],
            [ '30 yard', '22ft × 8ft × 5ft' ],
            [ '40 yard', '23ft × 8ft × 7ft' ],
          ];
          foreach ( $sizes as [ $label, $dims ] ) : ?>
          <button type="button" class="qz-option" data-name="bin_size" data-value="<?php echo esc_attr( $label ); ?>">
            <span class="qz-option-icon"><i class="fa-solid fa-dumpster"></i></span>
            <span class="qz-option-body">
              <span class="qz-option-name"><?php echo esc_html( $label ); ?></span>
              <span class="qz-option-tag"><?php echo esc_html( $dims ); ?></span>
            </span>
          </button>
          <?php endforeach; ?>
          <button type="button" class="qz-option" data-name="bin_size" data-value="Not sure">
            <span class="qz-option-icon"><i class="fa-solid fa-circle-question"></i></span>
            <span class="qz-option-body"><span class="qz-option-name">Not sure — help me decide</span></span>
          </button>
        </div>
      </div>

      <!-- Step 5: Contact + postal -->
      <div class="qz-step" data-step="5">
        <button type="button" class="qz-back qz-btn-back"><i class="fa-solid fa-arrow-left"></i> Back</button>
        <h2 class="qz-title no-sub">Where should we send your estimate?</h2>
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
          <label class="qz-input-label">Postal code (delivery location)</label>
          <input type="text" class="qz-input" name="postal" placeholder="K1A 0B1" required>
        </div>
        <button type="button" class="qz-next qz-btn-submit">Get My Estimate</button>
        <p class="qz-trust"><i class="fa-solid fa-lock"></i> No spam — we only use this to send your estimate.</p>
        <div class="qz-error" style="display:none"></div>
      </div>

      <!-- Success -->
      <div class="qz-step" data-step="6">
        <div class="qz-success">
          <div class="qz-success-icon"><i class="fa-solid fa-check"></i></div>
          <h2 class="qz-success-title">Estimate on its way!</h2>
          <p class="qz-success-text">We've received your details. Our team will reach out with a personalized bin estimate shortly.</p>
        </div>
      </div>

    </div>
  </div>
</div>
