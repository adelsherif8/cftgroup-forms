<?php if ( ! defined( 'ABSPATH' ) ) exit;
$uid = 'cftg-quiz-' . wp_unique_id();
$d   = cftg_get_design( 'vehicle_quote' );
$brand_name = 'CFT Recycling';
?>
<style>
  #<?php echo esc_attr( $uid ); ?> {
    --qz-bg:          #faf9f8;
    --qz-fg:          #1c1917;
    --qz-muted:       #78716c;
    --qz-muted-soft:  #a8a29e;
    --qz-card:        #ffffff;
    --qz-border:      #e7e5e4;
    --qz-primary:     #0a0a0a;
    --qz-primary-fg:  #ffffff;
    --qz-accent:      #fef3c7;
    --qz-accent-fg:   #78350f;
    --qz-radius:      12px;
    --qz-serif:       'Playfair Display', Georgia, 'Times New Roman', serif;
    --qz-sans:        'Inter', -apple-system, BlinkMacSystemFont, sans-serif;

    background: var(--qz-bg);
    min-height: 100vh;
    width: 100vw;
    position: relative;
    left: 50%;
    transform: translateX(-50%);
    padding: 0;
    margin: 0;
    font-family: var(--qz-sans);
    color: var(--qz-fg);
  }
  #<?php echo esc_attr( $uid ); ?> * { box-sizing: border-box; }

  #<?php echo esc_attr( $uid ); ?> .qz-header {
    padding: 22px 24px;
    text-align: center;
  }
  #<?php echo esc_attr( $uid ); ?> .qz-header-brand {
    font-family: var(--qz-serif);
    font-size: 20px;
    letter-spacing: 0.02em;
    color: var(--qz-fg);
  }

  #<?php echo esc_attr( $uid ); ?> .qz-progress {
    max-width: 520px;
    margin: 0 auto;
    padding: 8px 24px 20px;
  }
  #<?php echo esc_attr( $uid ); ?> .qz-progress-row {
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 8px;
    font-size: 12px; color: var(--qz-muted); letter-spacing: 0.02em;
  }
  #<?php echo esc_attr( $uid ); ?> .qz-progress-track {
    background: #e7e5e4;
    border-radius: 999px; height: 6px; overflow: hidden;
  }
  #<?php echo esc_attr( $uid ); ?> .qz-progress-fill {
    background: var(--qz-primary);
    height: 100%; width: 0%;
    transition: width 0.35s cubic-bezier(0.4,0,0.2,1);
  }

  #<?php echo esc_attr( $uid ); ?> .qz-main {
    display: flex; justify-content: center;
    padding: 12px 24px 80px;
  }
  #<?php echo esc_attr( $uid ); ?> .qz-inner {
    width: 100%; max-width: 540px;
  }

  #<?php echo esc_attr( $uid ); ?> .qz-step { display: none; }
  #<?php echo esc_attr( $uid ); ?> .qz-step.active {
    display: block; animation: qzFadeIn 0.4s ease-out both;
  }
  @keyframes qzFadeIn {
    from { opacity: 0; transform: translateX(24px); }
    to   { opacity: 1; transform: translateX(0); }
  }

  #<?php echo esc_attr( $uid ); ?> .qz-back {
    display: inline-flex; align-items: center; gap: 6px;
    background: none; border: none; padding: 4px 0;
    margin-bottom: 28px;
    font-size: 14px; color: var(--qz-muted); cursor: pointer;
    font-family: var(--qz-sans);
    transition: color 0.15s;
  }
  #<?php echo esc_attr( $uid ); ?> .qz-back:hover { color: var(--qz-fg); }

  #<?php echo esc_attr( $uid ); ?> .qz-title {
    font-family: var(--qz-serif);
    font-size: 28px; font-weight: 600;
    line-height: 1.25;
    margin: 0 0 30px;
    color: var(--qz-fg);
  }
  @media (min-width: 640px) {
    #<?php echo esc_attr( $uid ); ?> .qz-title { font-size: 34px; }
  }

  #<?php echo esc_attr( $uid ); ?> .qz-options {
    display: flex; flex-direction: column; gap: 12px;
  }
  #<?php echo esc_attr( $uid ); ?> .qz-option {
    background: var(--qz-card);
    border: 2px solid var(--qz-border);
    border-radius: var(--qz-radius);
    padding: 20px 24px;
    font-size: 17px; text-align: left;
    color: var(--qz-fg);
    cursor: pointer;
    transition: all 0.18s ease;
    font-family: var(--qz-sans);
    line-height: 1.35;
  }
  #<?php echo esc_attr( $uid ); ?> .qz-option:hover {
    background: #fdfaf6;
    border-color: rgba(238, 174, 0, 0.5);
    transform: translateY(-1px);
  }
  #<?php echo esc_attr( $uid ); ?> .qz-option.selected {
    background: var(--qz-accent);
    border-color: #eeae00;
    color: var(--qz-accent-fg);
  }

  /* Text/date/tel inputs */
  #<?php echo esc_attr( $uid ); ?> .qz-input-group {
    display: flex; flex-direction: column; gap: 6px;
    margin-bottom: 20px;
  }
  #<?php echo esc_attr( $uid ); ?> .qz-input-label {
    font-size: 13px; color: var(--qz-muted); font-weight: 500;
  }
  #<?php echo esc_attr( $uid ); ?> .qz-input {
    background: var(--qz-card);
    border: 2px solid var(--qz-border);
    border-radius: var(--qz-radius);
    padding: 16px 18px;
    font-size: 16px;
    color: var(--qz-fg);
    font-family: var(--qz-sans);
    width: 100%;
    outline: none;
    transition: border-color 0.18s;
  }
  #<?php echo esc_attr( $uid ); ?> .qz-input:focus { border-color: #eeae00; }

  #<?php echo esc_attr( $uid ); ?> .qz-next {
    background: var(--qz-primary); color: var(--qz-primary-fg);
    border: none; border-radius: var(--qz-radius);
    padding: 18px 32px;
    font-size: 16px; font-weight: 500;
    letter-spacing: 0.02em;
    cursor: pointer; width: 100%;
    font-family: var(--qz-sans);
    margin-top: 8px;
    transition: box-shadow 0.18s, transform 0.18s;
  }
  #<?php echo esc_attr( $uid ); ?> .qz-next:hover { box-shadow: 0 6px 24px rgba(0,0,0,0.18); transform: translateY(-1px); }
  #<?php echo esc_attr( $uid ); ?> .qz-next:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }

  /* Welcome step */
  #<?php echo esc_attr( $uid ); ?> .qz-welcome {
    display: flex; flex-direction: column; align-items: center;
    text-align: center; padding: 40px 24px 24px;
  }
  #<?php echo esc_attr( $uid ); ?> .qz-welcome-icon {
    width: 84px; height: 84px; border-radius: 50%;
    background: var(--qz-accent); color: var(--qz-accent-fg);
    display: flex; align-items: center; justify-content: center;
    font-size: 34px;
    margin-bottom: 32px;
  }
  #<?php echo esc_attr( $uid ); ?> .qz-welcome-title {
    font-family: var(--qz-serif);
    font-size: 38px; font-weight: 600;
    line-height: 1.1; margin: 0 0 20px;
    max-width: 480px;
  }
  @media (min-width: 640px) {
    #<?php echo esc_attr( $uid ); ?> .qz-welcome-title { font-size: 52px; }
  }
  #<?php echo esc_attr( $uid ); ?> .qz-welcome-sub {
    font-size: 17px; color: var(--qz-muted);
    max-width: 460px; line-height: 1.55; margin: 0 0 10px;
  }
  #<?php echo esc_attr( $uid ); ?> .qz-welcome-note {
    font-size: 13px; color: var(--qz-muted-soft);
    max-width: 320px; margin: 0 0 36px;
  }
  #<?php echo esc_attr( $uid ); ?> .qz-welcome-cta {
    background: var(--qz-primary); color: var(--qz-primary-fg);
    border: none; border-radius: var(--qz-radius);
    padding: 18px 48px;
    font-size: 17px; font-weight: 500;
    letter-spacing: 0.02em;
    cursor: pointer;
    font-family: var(--qz-sans);
    transition: box-shadow 0.18s, transform 0.18s;
  }
  #<?php echo esc_attr( $uid ); ?> .qz-welcome-cta:hover { box-shadow: 0 8px 32px rgba(0,0,0,0.2); transform: translateY(-1px); }
  #<?php echo esc_attr( $uid ); ?> .qz-welcome-brand {
    margin-top: 36px;
    font-size: 12px; color: var(--qz-muted-soft);
    letter-spacing: 0.06em;
  }

  /* Contact step (multi-input) */
  #<?php echo esc_attr( $uid ); ?> .qz-row2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
  @media (max-width: 480px) { #<?php echo esc_attr( $uid ); ?> .qz-row2 { grid-template-columns: 1fr; } }

  /* Success */
  #<?php echo esc_attr( $uid ); ?> .qz-success {
    text-align: center; padding: 40px 24px;
  }
  #<?php echo esc_attr( $uid ); ?> .qz-success-icon {
    width: 84px; height: 84px; border-radius: 50%;
    background: #dcfce7; color: #166534;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 36px; margin-bottom: 24px;
  }
  #<?php echo esc_attr( $uid ); ?> .qz-success-title {
    font-family: var(--qz-serif);
    font-size: 32px; font-weight: 600;
    margin: 0 0 12px;
  }
  #<?php echo esc_attr( $uid ); ?> .qz-success-text {
    color: var(--qz-muted); font-size: 16px; max-width: 400px; margin: 0 auto;
  }

  #<?php echo esc_attr( $uid ); ?> .qz-error {
    background: #fee2e2; border: 1px solid #fca5a5; color: #991b1b;
    border-radius: 10px; padding: 10px 14px; font-size: 13px;
    margin-top: 12px;
  }
</style>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">

<div id="<?php echo esc_attr( $uid ); ?>" class="cftg-quiz-wrap" data-form-type="vehicle_quote" data-total="5">

  <div class="qz-header">
    <div class="qz-header-brand"><?php echo esc_html( $brand_name ); ?></div>
  </div>

  <div class="qz-progress" style="display:none">
    <div class="qz-progress-row">
      <span class="qz-progress-text">Question 1 of 5</span>
      <span class="qz-progress-pct">20%</span>
    </div>
    <div class="qz-progress-track">
      <div class="qz-progress-fill"></div>
    </div>
  </div>

  <div class="qz-main">
    <div class="qz-inner">

      <!-- Honeypot + timing -->
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
        <button type="button" class="qz-back qz-btn-back">← Back</button>
        <h2 class="qz-title">What year is the vehicle?</h2>
        <div class="qz-input-group">
          <input type="text" class="qz-input" name="vehicle_year" placeholder="e.g. 2015" inputmode="numeric" required>
        </div>
        <button type="button" class="qz-next qz-btn-next">Continue</button>
      </div>

      <!-- Step 2: Make & Model -->
      <div class="qz-step" data-step="2">
        <button type="button" class="qz-back qz-btn-back">← Back</button>
        <h2 class="qz-title">What make and model?</h2>
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
        <button type="button" class="qz-back qz-btn-back">← Back</button>
        <h2 class="qz-title">Is the engine running?</h2>
        <div class="qz-options">
          <button type="button" class="qz-option" data-name="engine_running" data-value="Yes">Yes, it runs and drives</button>
          <button type="button" class="qz-option" data-name="engine_running" data-value="No">No, it doesn't run</button>
        </div>
      </div>

      <!-- Step 4: Parts missing (auto-advance if No, reveals input if Yes) -->
      <div class="qz-step" data-step="4">
        <button type="button" class="qz-back qz-btn-back">← Back</button>
        <h2 class="qz-title">Are any parts missing?</h2>
        <div class="qz-options">
          <button type="button" class="qz-option" data-name="parts_missing" data-value="Yes" data-reveal="qz-missing">Yes, some parts are missing</button>
          <button type="button" class="qz-option" data-name="parts_missing" data-value="No">No, everything is there</button>
        </div>
        <div class="qz-missing-wrap" style="display:none;margin-top:20px">
          <div class="qz-input-group">
            <label class="qz-input-label">What's missing?</label>
            <input type="text" class="qz-input" name="whats_missing" placeholder="e.g. catalytic converter, tires…">
          </div>
          <button type="button" class="qz-next qz-btn-next">Continue</button>
        </div>
      </div>

      <!-- Step 5: Postal + Contact combined -->
      <div class="qz-step" data-step="5">
        <button type="button" class="qz-back qz-btn-back">← Back</button>
        <h2 class="qz-title">Where should we send your offer?</h2>
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
        <div class="qz-error cftg-error-msg" style="display:none"></div>
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

<script>
( function () {
  const wrap = document.getElementById( '<?php echo esc_js( $uid ); ?>' );
  if ( ! wrap ) return;

  const total    = 5;
  const progress = wrap.querySelector( '.qz-progress' );
  const progText = wrap.querySelector( '.qz-progress-text' );
  const progPct  = wrap.querySelector( '.qz-progress-pct' );
  const progFill = wrap.querySelector( '.qz-progress-fill' );
  const errorEl  = wrap.querySelector( '.cftg-error-msg' );
  let current    = 0;

  /* Loaded_at + session for funnel */
  wrap.querySelectorAll( '.cftg-loaded-at' ).forEach( el => el.value = Math.floor( Date.now() / 1000 ) );

  function getSessionId() {
    try {
      let id = localStorage.getItem( 'cftg_session_id' );
      if ( ! id ) { id = 'cftg-' + Date.now().toString(36) + '-' + Math.random().toString(36).slice(2,10); localStorage.setItem( 'cftg_session_id', id ); }
      return id;
    } catch ( e ) { return 'cftg-anon'; }
  }
  function track( event, step ) {
    const d = new FormData();
    d.append( 'action', 'cftg_track' );
    d.append( 'session', getSessionId() );
    d.append( 'form_type', 'vehicle_quote' );
    d.append( 'event', event );
    d.append( 'step', String( step || 0 ) );
    d.append( 'page_url', window.location.origin + window.location.pathname );
    fetch( cftgData.ajaxUrl, { method: 'POST', body: d, keepalive: true } ).catch( () => {} );
  }
  track( 'view', 0 );
  let firstInteractionFired = false;

  function show( n ) {
    wrap.querySelectorAll( '.qz-step' ).forEach( s => s.classList.toggle( 'active', parseInt( s.dataset.step, 10 ) === n ) );
    if ( n === 0 ) {
      progress.style.display = 'none';
    } else if ( n <= total ) {
      progress.style.display = '';
      progText.textContent = 'Question ' + n + ' of ' + total;
      progPct.textContent  = Math.round( ( n / total ) * 100 ) + '%';
      progFill.style.width = ( n / total ) * 100 + '%';
    } else {
      progress.style.display = 'none';
    }
    window.scrollTo( { top: 0, behavior: 'smooth' } );
    if ( n >= 1 && n <= total ) {
      if ( ! firstInteractionFired && n === 1 ) { firstInteractionFired = true; track( 'step', 1 ); }
      else track( 'step', n );
    }
  }

  function validateCurrent() {
    const step = wrap.querySelector( '.qz-step[data-step="' + current + '"]' );
    if ( ! step ) return true;
    /* Skip validation on step 4 unless the "yes / what's missing" input is required */
    for ( const inp of step.querySelectorAll( 'input[required]' ) ) {
      if ( inp.offsetParent === null ) continue;
      if ( ! inp.value.trim() ) {
        errorEl.textContent = 'Please fill in this field before continuing.';
        errorEl.style.display = 'block';
        inp.focus();
        return false;
      }
    }
    errorEl.style.display = 'none';
    return true;
  }

  function next() {
    if ( ! validateCurrent() ) return;
    if ( current < total ) { current++; show( current ); }
  }
  function back() {
    if ( current > 0 ) { current--; show( current ); }
  }

  wrap.addEventListener( 'click', e => {
    if ( e.target.closest( '.qz-btn-next' ) )   { next(); return; }
    if ( e.target.closest( '.qz-btn-back' ) )   { back(); return; }
    if ( e.target.closest( '.qz-btn-submit' ) ) { submit(); return; }

    const opt = e.target.closest( '.qz-option' );
    if ( opt ) {
      const name    = opt.dataset.name;
      const value   = opt.dataset.value;
      const reveal  = opt.dataset.reveal;
      /* Set a hidden input so submit picks it up */
      let hidden = wrap.querySelector( 'input[type="hidden"][name="' + name + '"]' );
      if ( ! hidden ) {
        hidden = document.createElement( 'input' );
        hidden.type = 'hidden'; hidden.name = name;
        wrap.appendChild( hidden );
      }
      hidden.value = value;
      opt.parentElement.querySelectorAll( '.qz-option' ).forEach( o => o.classList.remove( 'selected' ) );
      opt.classList.add( 'selected' );

      /* Auto-advance unless this option reveals a conditional block */
      if ( reveal ) {
        const container = opt.closest( '.qz-step' ).querySelector( '.' + reveal + '-wrap' );
        if ( container ) container.style.display = 'block';
      } else {
        setTimeout( next, 300 );
      }
    }
  } );

  const CUSTOM_KEYS = [ 'utmcampaign_custom','utmmedium_custom','utmcontent_custom','utmkeyword_custom','utmterm_custom','gclid_custom' ];
  function appendUtms( data ) {
    let stored = {};
    try { stored = JSON.parse( sessionStorage.getItem( 'scad_tracking_params' ) || '{}' ); } catch ( e ) {}
    CUSTOM_KEYS.forEach( k => { if ( stored[ k ] ) data.append( k, stored[ k ] ); } );
  }

  function freshNonce() {
    const d = new FormData();
    d.append( 'action', 'cftg_get_nonce' );
    return fetch( cftgData.ajaxUrl, { method:'POST', body:d, cache:'no-store' } )
      .then( r => r.json() )
      .then( r => ( r && r.success && r.data && r.data.nonce ) ? r.data.nonce : cftgData.nonce )
      .catch( () => cftgData.nonce );
  }

  function submit() {
    if ( ! validateCurrent() ) return;
    const btn = wrap.querySelector( '.qz-btn-submit' );
    if ( btn ) { btn.disabled = true; btn.textContent = 'Sending…'; }
    freshNonce().then( nonce => {
      const data = new FormData();
      data.append( 'action', 'cftg_submit' );
      data.append( 'nonce', nonce );
      data.append( 'form_type', 'vehicle_quote' );
      wrap.querySelectorAll( 'input, select, textarea' ).forEach( el => {
        if ( ! el.name ) return;
        data.set( el.name, el.value );
      } );
      appendUtms( data );
      return fetch( cftgData.ajaxUrl, { method:'POST', body:data } );
    } )
      .then( r => r.json() )
      .then( res => {
        if ( res.success ) { track( 'submit', 0 ); current = total + 1; show( current ); }
        else {
          errorEl.textContent = res.data?.message || 'Something went wrong. Please try again.';
          errorEl.style.display = 'block';
          if ( btn ) { btn.disabled = false; btn.textContent = 'Get My Offer'; }
        }
      } )
      .catch( () => {
        errorEl.textContent = 'Network error. Please try again.';
        errorEl.style.display = 'block';
        if ( btn ) { btn.disabled = false; btn.textContent = 'Get My Offer'; }
      } );
  }
} )();
</script>
