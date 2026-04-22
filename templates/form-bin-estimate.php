<?php if ( ! defined( 'ABSPATH' ) ) exit;
$uid = 'cftg-' . wp_unique_id();
?>
<div class="cftg-wrap" id="<?php echo esc_attr( $uid ); ?>" data-form-type="bin_estimate" data-total="6">

  <!-- ── Left panel: marketing content ── -->
  <div class="cftg-left">
    <div class="cftg-logo">
      <img src="https://cftgroup.ca/wp-content/uploads/2024/09/cft-group-logo.png" alt="CFT Group">
    </div>
    <div class="cftg-badge"><i class="fa-solid fa-dumpster"></i> Free Estimate</div>
    <h2 class="cftg-title">Get Your <span class="cftg-accent">Bin Dumpster</span> Estimate</h2>
    <p class="cftg-desc">At CFT Recycling, we make renting a dumpster fast and friendly. Choose your bin size and we'll handle the rest.</p>
    <div class="cftg-feat-grid">
      <div class="cftg-feat-item"><i class="fa-solid fa-truck-fast"></i> Prompt delivery</div>
      <div class="cftg-feat-item"><i class="fa-solid fa-tag"></i> Competitive pricing</div>
      <div class="cftg-feat-item"><i class="fa-solid fa-leaf"></i> Eco-responsible disposal</div>
      <div class="cftg-feat-item"><i class="fa-solid fa-building"></i> Commercial & residential</div>
    </div>
    <div class="cftg-contact-row">
      <div class="cftg-contact-item"><i class="fa-solid fa-phone"></i> (416) 800-0000</div>
      <div class="cftg-contact-item"><i class="fa-solid fa-clock"></i> Mon–Fri 8am–6pm</div>
    </div>
  </div>

  <!-- ── Right panel: white card ── -->
  <div class="cftg-card">
    <div class="cftg-card-header">
      <div class="cftg-step-info">
        <span class="cftg-step-label">Step 1 of 6</span>
        <span class="cftg-pct-label">0%</span>
      </div>
      <div class="cftg-prog-track"><div class="cftg-prog-fill" style="width:0%"></div></div>
    </div>

    <div class="cftg-card-body">

      <!-- Step 1: Dispose type -->
      <div class="cftg-step active" data-step="1">
        <h2 class="cftg-q-title">What do you need to dispose?</h2>
        <p class="cftg-q-sub">Select all that apply</p>
        <div class="cftg-grid g3">
          <?php
          $items = [
            ['Garbage','fa-trash-can'],['Metal','fa-gear'],['Construction Debris','fa-helmet-safety'],
            ['Dirt','fa-hill-rockslide'],['Concrete','fa-cubes'],['Other','fa-box-open'],
          ];
          foreach ( $items as [$label, $icon] ):
          ?>
          <label class="cftg-choice">
            <input type="checkbox" name="dispose_types[]" value="<?php echo esc_attr( $label ); ?>">
            <div class="cftg-choice-body">
              <span class="cftg-choice-icon"><i class="fa-solid <?php echo esc_attr( $icon ); ?>"></i></span>
              <span class="cftg-choice-name"><?php echo esc_html( $label ); ?></span>
            </div>
          </label>
          <?php endforeach; ?>
        </div>
        <div class="cftg-actions">
          <button class="cftg-btn-next" type="button">Continue <i class="fa-solid fa-arrow-right"></i></button>
        </div>
      </div>

      <!-- Step 2: Date -->
      <div class="cftg-step" data-step="2">
        <h2 class="cftg-q-title">When do you need your bin?</h2>
        <p class="cftg-q-sub">Pick your preferred delivery date</p>
        <div class="cftg-field">
          <label class="cftg-label">Delivery Date</label>
          <input type="date" class="cftg-input" name="delivery_date">
        </div>
        <div class="cftg-actions">
          <button class="cftg-btn-back" type="button"><i class="fa-solid fa-arrow-left"></i> Back</button>
          <button class="cftg-btn-next" type="button">Continue <i class="fa-solid fa-arrow-right"></i></button>
        </div>
      </div>

      <!-- Step 3: Duration -->
      <div class="cftg-step" data-step="3">
        <h2 class="cftg-q-title">How long do you need the bin for?</h2>
        <p class="cftg-q-sub">Select the rental duration</p>
        <div class="cftg-grid g3">
          <?php
          $durations = [
            ['One day','fa-sun'],['Couple of days','fa-calendar-day'],['One week','fa-calendar-week'],
            ['Couple of weeks','fa-calendar-days'],['More than a month','fa-infinity'],['Not sure','fa-circle-question'],
          ];
          foreach ( $durations as [$label, $icon] ):
          ?>
          <label class="cftg-choice">
            <input type="radio" name="bin_duration" value="<?php echo esc_attr( $label ); ?>">
            <div class="cftg-choice-body">
              <span class="cftg-choice-icon"><i class="fa-solid <?php echo esc_attr( $icon ); ?>"></i></span>
              <span class="cftg-choice-name"><?php echo esc_html( $label ); ?></span>
            </div>
          </label>
          <?php endforeach; ?>
        </div>
        <div class="cftg-actions">
          <button class="cftg-btn-back" type="button"><i class="fa-solid fa-arrow-left"></i> Back</button>
          <button class="cftg-btn-next" type="button">Continue <i class="fa-solid fa-arrow-right"></i></button>
        </div>
      </div>

      <!-- Step 4: Bin size -->
      <div class="cftg-step" data-step="4">
        <h2 class="cftg-q-title">What size do you need?</h2>
        <p class="cftg-q-sub">Select your bin size</p>
        <div class="cftg-grid g2">
          <?php
          $sizes = [
            ['10 yard','10 yd','10ft × 8ft × 4ft'],['20 yard','20 yd','20ft × 8ft × 3.5ft'],
            ['30 yard','30 yd','22ft × 8ft × 5ft'],['40 yard','40 yd','23ft × 8ft × 7ft'],
          ];
          $widths  = ['38','52','58','64'];
          $heights = ['26','30','36','44'];
          foreach ( $sizes as $i => [$val, $short, $dims] ):
          ?>
          <label class="cftg-choice">
            <input type="radio" name="bin_size" value="<?php echo esc_attr( $val ); ?>">
            <div class="cftg-choice-body">
              <div class="cftg-bin-visual"><div class="cftg-bin-rect" style="width:<?php echo $widths[$i]; ?>px;height:<?php echo $heights[$i]; ?>px"></div></div>
              <span class="cftg-bin-label"><?php echo esc_html( $short ); ?></span>
              <span class="cftg-choice-detail"><?php echo esc_html( $dims ); ?></span>
            </div>
          </label>
          <?php endforeach; ?>
          <label class="cftg-choice cftg-full">
            <input type="radio" name="bin_size" value="Not sure">
            <div class="cftg-choice-body">
              <span class="cftg-choice-icon"><i class="fa-solid fa-circle-question"></i></span>
              <span class="cftg-choice-name">Not sure — help me decide</span>
            </div>
          </label>
        </div>
        <div class="cftg-actions">
          <button class="cftg-btn-back" type="button"><i class="fa-solid fa-arrow-left"></i> Back</button>
          <button class="cftg-btn-next" type="button">Continue <i class="fa-solid fa-arrow-right"></i></button>
        </div>
      </div>

      <!-- Step 5: Postal -->
      <div class="cftg-step" data-step="5">
        <h2 class="cftg-q-title">Where would you like the bin delivered?</h2>
        <p class="cftg-q-sub">Enter your postal code for an accurate estimate</p>
        <div class="cftg-field">
          <label class="cftg-label">Postal Code</label>
          <input type="text" class="cftg-input" name="postal" placeholder="e.g. M5V 3A8">
        </div>
        <div class="cftg-actions">
          <button class="cftg-btn-back" type="button"><i class="fa-solid fa-arrow-left"></i> Back</button>
          <button class="cftg-btn-next" type="button">Continue <i class="fa-solid fa-arrow-right"></i></button>
        </div>
      </div>

      <!-- Step 6: Contact -->
      <div class="cftg-step" data-step="6">
        <h2 class="cftg-q-title">Almost there!</h2>
        <p class="cftg-q-sub">Enter your contact details to receive your estimate</p>
        <div class="cftg-row">
          <div class="cftg-field"><label class="cftg-label">First Name</label><input type="text" class="cftg-input" name="first_name" placeholder="John"></div>
          <div class="cftg-field"><label class="cftg-label">Last Name</label><input type="text" class="cftg-input" name="last_name" placeholder="Smith"></div>
        </div>
        <div class="cftg-field"><label class="cftg-label">Email Address</label><input type="email" class="cftg-input" name="email" placeholder="john@example.com"></div>
        <div class="cftg-field"><label class="cftg-label">Phone Number</label><input type="tel" class="cftg-input" name="phone" placeholder="+1 (416) 555-0100"></div>
        <div class="cftg-actions">
          <button class="cftg-btn-back" type="button"><i class="fa-solid fa-arrow-left"></i> Back</button>
          <button class="cftg-btn-submit" type="button">Get My Estimate <i class="fa-solid fa-paper-plane"></i></button>
        </div>
      </div>

      <!-- Success -->
      <div class="cftg-step" data-step="7">
        <div class="cftg-success">
          <div class="cftg-success-ring"><i class="fa-solid fa-check"></i></div>
          <h2>Estimate Request Sent!</h2>
          <p>Our team will review your request and reach out with a personalized dumpster estimate shortly.</p>
        </div>
      </div>

      <div class="cftg-error-msg" style="display:none"></div>
    </div>
  </div>
</div>
