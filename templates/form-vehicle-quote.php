<?php if ( ! defined( 'ABSPATH' ) ) exit;
$uid = 'cftg-' . wp_unique_id();
?>
<div class="cftg-wrap" id="<?php echo esc_attr( $uid ); ?>" data-form-type="vehicle_quote" data-total="4">

  <!-- ── Left panel: marketing content ── -->
  <div class="cftg-left">
    <div class="cftg-logo">
      <img src="https://cftgroup.ca/wp-content/uploads/2024/09/cft-group-logo.png" alt="CFT Group">
    </div>
    <div class="cftg-badge"><i class="fa-solid fa-tag"></i> Free Quote</div>
    <h2 class="cftg-title">Earn Money on Your <span class="cftg-accent">Scrap or Used Vehicle</span></h2>
    <p class="cftg-desc">At CFT Recycling, we've made selling your car simple and hassle-free. Get a cash offer in minutes.</p>
    <div class="cftg-feat-grid">
      <div class="cftg-feat-item"><i class="fa-solid fa-truck"></i> Free towing included</div>
      <div class="cftg-feat-item"><i class="fa-solid fa-bolt"></i> Same-day quotes</div>
      <div class="cftg-feat-item"><i class="fa-solid fa-dollar-sign"></i> Cash on the spot</div>
      <div class="cftg-feat-item"><i class="fa-solid fa-file-circle-xmark"></i> Zero paperwork</div>
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
        <span class="cftg-step-label">Step 1 of 4</span>
        <span class="cftg-pct-label">0%</span>
      </div>
      <div class="cftg-prog-track"><div class="cftg-prog-fill" style="width:0%"></div></div>
    </div>

    <div class="cftg-card-body">

      <!-- Step 1: Vehicle details -->
      <div class="cftg-step active" data-step="1">
        <h2 class="cftg-q-title">Tell us about your vehicle</h2>
        <p class="cftg-q-sub">Fill in the year, make, and model below</p>
        <div class="cftg-vehicle-grid">
          <div class="cftg-field"><label class="cftg-label">Vehicle Year</label><input type="text" class="cftg-input" name="vehicle_year" placeholder="2018"></div>
          <div class="cftg-field"><label class="cftg-label">Vehicle Make</label><input type="text" class="cftg-input" name="vehicle_make" placeholder="Honda"></div>
          <div class="cftg-field"><label class="cftg-label">Vehicle Model</label><input type="text" class="cftg-input" name="vehicle_model" placeholder="Civic"></div>
        </div>
        <div class="cftg-actions">
          <button class="cftg-btn-next" type="button">Continue <i class="fa-solid fa-arrow-right"></i></button>
        </div>
      </div>

      <!-- Step 2: Engine running? -->
      <div class="cftg-step" data-step="2">
        <h2 class="cftg-q-title">Is the engine running?</h2>
        <p class="cftg-q-sub">Let us know the current condition of your vehicle</p>
        <div class="cftg-yn-grid">
          <label class="cftg-yn yes-card">
            <input type="radio" name="engine_running" value="Yes">
            <div class="cftg-yn-body">
              <div class="cftg-yn-icon"><i class="fa-solid fa-circle-check"></i></div>
              <span class="cftg-yn-label">Yes</span>
            </div>
          </label>
          <label class="cftg-yn no-card">
            <input type="radio" name="engine_running" value="No">
            <div class="cftg-yn-body">
              <div class="cftg-yn-icon"><i class="fa-solid fa-circle-xmark"></i></div>
              <span class="cftg-yn-label">No</span>
            </div>
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
          <label class="cftg-yn gold-card">
            <input type="radio" name="parts_missing" value="Yes" class="cftg-toggle-missing">
            <div class="cftg-yn-body">
              <div class="cftg-yn-icon"><i class="fa-solid fa-wrench"></i></div>
              <span class="cftg-yn-label">Yes</span>
            </div>
          </label>
          <label class="cftg-yn no-card">
            <input type="radio" name="parts_missing" value="No" class="cftg-toggle-missing">
            <div class="cftg-yn-body">
              <div class="cftg-yn-icon"><i class="fa-solid fa-circle-check"></i></div>
              <span class="cftg-yn-label">No</span>
            </div>
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
