<?php if ( ! defined( 'ABSPATH' ) ) exit;
$uid = 'cftg-quiz-' . wp_unique_id();
$d   = cftg_get_design( 'scrap_metal' );
?>
<div id="<?php echo esc_attr( $uid ); ?>" class="cftg-quiz" data-form-type="scrap_metal" data-total="3">

  <div class="qz-header">
    <div class="qz-header-brand">CFT Recycling</div>
  </div>

  <div class="qz-progress" style="display:none">
    <div class="qz-progress-row">
      <span class="qz-progress-text">Question 1 of 3</span>
      <span class="qz-progress-pct">33%</span>
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
          <div class="qz-welcome-icon"><i class="fa-solid fa-coins"></i></div>
          <h1 class="qz-welcome-title">Turn your scrap metal into cash</h1>
          <p class="qz-welcome-sub">Tell us what you've got and we'll give you top market value — paid on the spot.</p>
          <p class="qz-welcome-note">Takes about 45 seconds. All metals accepted.</p>
          <button type="button" class="qz-welcome-cta qz-btn-next">Start My Offer</button>
          <p class="qz-welcome-brand">CFT RECYCLING · OTTAWA, ON</p>
        </div>
      </div>

      <!-- Step 1: Materials (multi-select) -->
      <div class="qz-step" data-step="1">
        <button type="button" class="qz-back qz-btn-back"><i class="fa-solid fa-arrow-left"></i> Back</button>
        <h2 class="qz-title">What do you need to scrap?</h2>
        <p class="qz-sub">Select all that apply</p>
        <div class="qz-options">
          <?php
          $metals = [
            [ 'Copper', 'fa-bolt', 'Non-Ferrous' ],
            [ 'Brass', 'fa-medal', 'Non-Ferrous' ],
            [ 'Radiator', 'fa-temperature-half', 'Mixed' ],
            [ 'Stainless and Zinc', 'fa-screwdriver-wrench', 'Non-Ferrous' ],
            [ 'Wire', 'fa-plug', 'Copper' ],
            [ 'Appliances & Electronics', 'fa-laptop', 'E-Scrap' ],
            [ 'Ferrous / Steel', 'fa-link', 'Ferrous' ],
            [ 'Heavy Machinery / Equipment', 'fa-tractor', 'Industrial' ],
            [ 'Other (please specify)', 'fa-box-open', 'Other' ],
          ];
          foreach ( $metals as [ $label, $icon, $tag ] ) : ?>
          <button type="button" class="qz-option" data-multi data-name="scrap_types" data-value="<?php echo esc_attr( $label ); ?>">
            <span class="qz-option-icon"><i class="fa-solid <?php echo esc_attr( $icon ); ?>"></i></span>
            <span class="qz-option-body">
              <span class="qz-option-name"><?php echo esc_html( $label ); ?></span>
              <span class="qz-option-tag"><?php echo esc_html( $tag ); ?></span>
            </span>
            <span class="qz-check"><i class="fa-solid fa-check"></i></span>
          </button>
          <?php endforeach; ?>
        </div>
        <?php if ( ! empty( $d['scrap_price_list_url'] ) ) : ?>
        <p class="qz-pricelink">
          <i class="fa-solid fa-circle-info"></i> Not sure what your metal is worth?
          <a href="<?php echo esc_url( $d['scrap_price_list_url'] ); ?>" target="_blank" rel="noopener">See our live price list →</a>
        </p>
        <?php endif; ?>
        <button type="button" class="qz-next qz-btn-next">Continue</button>
        <div class="qz-error" style="display:none"></div>
      </div>

      <!-- Step 2: Load size (single, "exact weight" reveals input) -->
      <div class="qz-step" data-step="2">
        <button type="button" class="qz-back qz-btn-back"><i class="fa-solid fa-arrow-left"></i> Back</button>
        <h2 class="qz-title">Roughly how much do you have?</h2>
        <p class="qz-sub">Just a rough estimate — we'll confirm at pickup or drop-off</p>
        <div class="qz-options">
          <?php
          $loads = [
            [ 'Bag / Trunk-load', 'fa-shopping-bag', '~50–200 lbs' ],
            [ 'Pickup-load', 'fa-truck-pickup', '~500–1,500 lbs' ],
            [ 'Trailer / Van-load', 'fa-trailer', '~2,000–5,000 lbs' ],
            [ 'Multiple loads / Industrial', 'fa-industry', '5,000+ lbs' ],
            [ 'Not sure', 'fa-circle-question', "I'll need help estimating" ],
          ];
          foreach ( $loads as [ $label, $icon, $sub ] ) : ?>
          <button type="button" class="qz-option" data-name="load_size" data-value="<?php echo esc_attr( $label ); ?>">
            <span class="qz-option-icon"><i class="fa-solid <?php echo esc_attr( $icon ); ?>"></i></span>
            <span class="qz-option-body">
              <span class="qz-option-name"><?php echo esc_html( $label ); ?></span>
              <span class="qz-option-tag"><?php echo esc_html( $sub ); ?></span>
            </span>
          </button>
          <?php endforeach; ?>
          <button type="button" class="qz-option" data-name="load_size" data-value="I know the exact weight" data-reveal="weight">
            <span class="qz-option-icon"><i class="fa-solid fa-weight-scale"></i></span>
            <span class="qz-option-body"><span class="qz-option-name">I know the exact weight</span></span>
          </button>
        </div>
        <div class="qz-reveal" data-reveal-id="weight">
          <div class="qz-input-group">
            <label class="qz-input-label">Exact weight</label>
            <div style="display:flex;gap:8px;align-items:center">
              <input type="number" min="1" step="1" class="qz-input" name="exact_weight" placeholder="e.g. 850" style="flex:1" required>
              <select class="qz-input" name="exact_weight_unit" style="max-width:100px">
                <option value="lbs" selected>lbs</option>
                <option value="kg">kg</option>
              </select>
            </div>
          </div>
          <button type="button" class="qz-next qz-btn-next">Continue</button>
        </div>
      </div>

      <!-- Step 3: Contact + postal -->
      <div class="qz-step" data-step="3">
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
          <label class="qz-input-label">Postal code</label>
          <input type="text" class="qz-input" name="postal" placeholder="K1A 0B1" required>
        </div>
        <button type="button" class="qz-next qz-btn-submit">Get My Offer</button>
        <div class="qz-error" style="display:none"></div>
      </div>

      <!-- Success -->
      <div class="qz-step" data-step="4">
        <div class="qz-success">
          <div class="qz-success-icon"><i class="fa-solid fa-check"></i></div>
          <h2 class="qz-success-title">Offer on its way!</h2>
          <p class="qz-success-text">We've received your scrap details. Our team will reach out with a cash offer shortly.</p>
        </div>
      </div>

    </div>
  </div>
</div>
