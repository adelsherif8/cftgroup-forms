<?php if ( ! defined( 'ABSPATH' ) ) exit;
$uid    = 'cftg-' . wp_unique_id();
$d      = cftg_get_design( 'scrap_metal' );
$styles = cftg_section_styles( 'scrap_metal' );
?>
<div class="cftg-section" style="<?php echo $styles['section']; ?>">
  <div class="cftg-overlay" style="<?php echo $styles['overlay']; ?>"></div>

  <div class="cftg-wrap" id="<?php echo esc_attr( $uid ); ?>" data-form-type="scrap_metal" data-total="3">

    <!-- ── Left panel ── -->
    <div class="cftg-left">
      <div class="cftg-logo">
        <img src="https://cftgroup.ca/wp-content/uploads/2024/09/cft-group-logo.png" alt="CFT Group">
      </div>
      <div class="cftg-badge"><i class="fa-solid fa-coins"></i> <?php echo esc_html( $d['badge'] ); ?></div>
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
          <span class="cftg-pct-label">0%</span>
        </div>
        <div class="cftg-prog-track"><div class="cftg-prog-fill" style="width:0%"></div></div>
      </div>

      <div class="cftg-card-body">
        <div class="cftg-hp" aria-hidden="true"><input type="text" name="website" tabindex="-1" autocomplete="off"></div>
        <input type="hidden" name="loaded_at" class="cftg-loaded-at">

        <!-- Step 1: Scrap type -->
        <div class="cftg-step active" data-step="1">
          <h2 class="cftg-q-title">What do you need to scrap?</h2>
          <p class="cftg-q-sub">Select all that apply — takes 60 seconds</p>
          <div class="cftg-grid g3">
            <?php
            $metals = [
              ['Copper','fa-bolt','Non-Ferrous'],
              ['Brass','fa-medal','Non-Ferrous'],
              ['Radiator','fa-temperature-half','Mixed'],
              ['Stainless and Zinc','fa-screwdriver-wrench','Non-Ferrous'],
              ['Wire','fa-plug','Copper'],
              ['Appliances & Electronics','fa-laptop','E-Scrap'],
              ['Ferrous / Steel','fa-link','Ferrous'],
              ['Heavy Machinery / Equipment','fa-tractor','Industrial'],
              ['Other (please specify)','fa-box-open','Other'],
            ];
            foreach ( $metals as [$label, $icon, $tag] ):
            ?>
            <label class="cftg-choice">
              <input type="checkbox" name="scrap_types[]" value="<?php echo esc_attr( $label ); ?>">
              <div class="cftg-choice-body">
                <span class="cftg-choice-icon"><i class="fa-solid <?php echo esc_attr( $icon ); ?>"></i></span>
                <span class="cftg-choice-text">
                  <span class="cftg-choice-name"><?php echo esc_html( $label ); ?></span>
                  <span class="cftg-metal-tag"><?php echo esc_html( $tag ); ?></span>
                </span>
              </div>
            </label>
            <?php endforeach; ?>
          </div>
          <div class="cftg-actions">
            <button class="cftg-btn-next" type="button">Get My Offer <i class="fa-solid fa-arrow-right"></i></button>
          </div>
        </div>

        <!-- Step 2: Postal code -->
        <div class="cftg-step" data-step="2">
          <h2 class="cftg-q-title">Where are you located?</h2>
          <p class="cftg-q-sub">Enter your postal code so we can serve you better</p>
          <div class="cftg-field"><label class="cftg-label">Postal Code</label><input type="text" class="cftg-input" name="postal" placeholder="e.g. M5V 3A8"></div>
          <div class="cftg-actions">
            <button class="cftg-btn-back" type="button"><i class="fa-solid fa-arrow-left"></i> Back</button>
            <button class="cftg-btn-next" type="button">Continue <i class="fa-solid fa-arrow-right"></i></button>
          </div>
        </div>

        <!-- Step 3: Contact -->
        <div class="cftg-step" data-step="3">
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
            <button class="cftg-btn-submit" type="button">Get My Offer <i class="fa-solid fa-paper-plane"></i></button>
          </div>
        </div>

        <!-- Success -->
        <div class="cftg-step" data-step="4">
          <div class="cftg-success">
            <div class="cftg-success-ring"><i class="fa-solid fa-check"></i></div>
            <h2>Offer Request Sent!</h2>
            <p>Our team will review your scrap metal details and reach out with a cash offer shortly.</p>
          </div>
        </div>

        <div class="cftg-error-msg" style="display:none"></div>
      </div>
    </div>

  </div>
</div>
