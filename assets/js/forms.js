( function () {
  'use strict';

  /* ── Funnel tracking ──
     Persistent session ID lives in localStorage so we can re-identify
     the same visitor across page reloads in the same browser. */
  function getSessionId() {
    try {
      let id = localStorage.getItem( 'cftg_session_id' );
      if ( ! id ) {
        id = 'cftg-' + Date.now().toString( 36 ) + '-' +
             Math.random().toString( 36 ).slice( 2, 10 );
        localStorage.setItem( 'cftg_session_id', id );
      }
      return id;
    } catch ( e ) {
      return 'cftg-anon-' + Math.floor( Math.random() * 1e9 );
    }
  }
  function track( formType, event, step ) {
    const d = new FormData();
    d.append( 'action',    'cftg_track' );
    d.append( 'session',   getSessionId() );
    d.append( 'form_type', formType );
    d.append( 'event',     event );
    d.append( 'step',      String( step || 0 ) );
    d.append( 'page_url',  window.location.origin + window.location.pathname );
    /* Fire-and-forget. Keep alive so it survives navigation. */
    fetch( cftgData.ajaxUrl, { method: 'POST', body: d, keepalive: true } ).catch( () => {} );
  }

  class CftgForm {
    constructor( wrap ) {
      this.wrap     = wrap;
      this.formType = wrap.dataset.formType;
      this.total    = parseInt( wrap.dataset.total, 10 );
      this.current  = 1;
      this._seenSteps = new Set();

      this.progFill  = wrap.querySelector( '.cftg-prog-fill' );
      this.progPct   = wrap.querySelector( '.cftg-pct-label' );
      this.errorEl   = wrap.querySelector( '.cftg-error-msg' );

      this._bind();
      this._refresh();

      /* Funnel: form rendered = landing page visit.
         Step 1 fires only when the user actually engages (focus or click). */
      track( this.formType, 'view', 0 );
      const firstStep = () => {
        this._trackStep( 1 );
        this.wrap.removeEventListener( 'focusin', firstStep );
        this.wrap.removeEventListener( 'click',   firstStep );
        this.wrap.removeEventListener( 'change',  firstStep );
      };
      this.wrap.addEventListener( 'focusin', firstStep );
      this.wrap.addEventListener( 'click',   firstStep );
      this.wrap.addEventListener( 'change',  firstStep );
    }

    _trackStep( n ) {
      if ( this._seenSteps.has( n ) ) return;
      this._seenSteps.add( n );
      track( this.formType, 'step', n );
    }

    /* ── Step visibility ── */
    _showStep( n ) {
      this.wrap.querySelectorAll( '.cftg-step' ).forEach( s => {
        s.classList.toggle( 'active', parseInt( s.dataset.step, 10 ) === n );
      } );
    }

    _refresh() {
      const isSuccess = this.current > this.total;
      this._showStep( isSuccess ? this.total + 1 : this.current );

      const pct = isSuccess ? 100 : Math.round( ( ( this.current - 1 ) / this.total ) * 100 );
      if ( this.progFill ) this.progFill.style.width = pct + '%';
      if ( this.progPct )  this.progPct.textContent  = pct + '%';
    }

    /* ── Navigation ── */
    next() {
      if ( ! this._validate() ) return;
      if ( this.current < this.total ) {
        this.current++;
        this._refresh();
        this._trackStep( this.current );
        this._scrollTop();
      }
    }

    back() {
      if ( this.current > 1 ) {
        this.current--;
        this._refresh();
        this._scrollTop();
      }
    }

    _scrollTop() {
      this.wrap.scrollIntoView( { behavior: 'smooth', block: 'nearest' } );
    }

    /* ── Validation ── */
    _validate() {
      const step = this.wrap.querySelector( `.cftg-step[data-step="${ this.current }"]` );
      if ( ! step ) return true;

      for ( const inp of step.querySelectorAll( 'input[required], select[required], textarea[required]' ) ) {
        /* Skip required inputs hidden inside a collapsed conditional block */
        if ( inp.offsetParent === null ) continue;
        if ( ! inp.value.trim() ) {
          this._showError( 'Please fill in all required fields.' );
          inp.focus();
          return false;
        }
      }

      /* Required radio groups (at least one option must be selected) */
      const radios = step.querySelectorAll( 'input[type="radio"]' );
      const radioNames = new Set();
      radios.forEach( r => radioNames.add( r.name ) );
      for ( const name of radioNames ) {
        const checked = step.querySelector( `input[type="radio"][name="${ name }"]:checked` );
        if ( ! checked ) {
          this._showError( 'Please make a selection before continuing.' );
          return false;
        }
      }

      const checkboxes = step.querySelectorAll( '.cftg-grid input[type="checkbox"]' );
      if ( checkboxes.length && ! Array.from( checkboxes ).some( cb => cb.checked ) ) {
        this._showError( 'Please select at least one option.' );
        return false;
      }

      this._clearError();
      return true;
    }

    _showError( msg ) {
      if ( this.errorEl ) { this.errorEl.textContent = msg; this.errorEl.style.display = 'block'; }
    }
    _clearError() {
      if ( this.errorEl ) { this.errorEl.textContent = ''; this.errorEl.style.display = 'none'; }
    }

    /* Fetch a fresh nonce so cached pages with a stale nonce still submit */
    _freshNonce() {
      const d = new FormData();
      d.append( 'action', 'cftg_get_nonce' );
      return fetch( cftgData.ajaxUrl, { method: 'POST', body: d, cache: 'no-store' } )
        .then( r => r.json() )
        .then( res => ( res && res.success && res.data && res.data.nonce ) ? res.data.nonce : cftgData.nonce )
        .catch( () => cftgData.nonce );
    }

    /* ── Submit ── */
    submit() {
      if ( ! this._validate() ) return;

      const btn = this.wrap.querySelector( '.cftg-btn-submit' );
      if ( btn ) { btn.disabled = true; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Sending…'; }

      this._freshNonce().then( nonce => {
        const data = new FormData();
        data.append( 'action',    'cftg_submit' );
        data.append( 'nonce',     nonce );
        data.append( 'form_type', this.formType );

        this.wrap.querySelectorAll( 'input, select, textarea' ).forEach( el => {
          if ( ! el.name ) return;
          if ( el.type === 'checkbox' )      { if ( el.checked ) data.append( el.name, el.value ); }
          else if ( el.type === 'radio' )    { if ( el.checked ) data.set( el.name, el.value ); }
          else                               { data.set( el.name, el.value ); }
        } );

        appendUtms( data );

        return fetch( cftgData.ajaxUrl, { method: 'POST', body: data } );
      } )
        .then( r => r.json() )
        .then( res => {
          if ( res.success ) {
            track( this.formType, 'submit', 0 );
            this.current = this.total + 1;
            this._refresh();
          } else {
            this._showError( res.data?.message || 'Something went wrong. Please try again.' );
            if ( btn ) { btn.disabled = false; btn.innerHTML = 'Submit <i class="fa-solid fa-paper-plane"></i>'; }
          }
        } )
        .catch( () => {
          this._showError( 'Network error. Please try again.' );
          if ( btn ) { btn.disabled = false; btn.innerHTML = 'Submit <i class="fa-solid fa-paper-plane"></i>'; }
        } );
    }

    /* ── Event binding ── */
    _bind() {
      this.wrap.addEventListener( 'click', e => {
        if ( e.target.closest( '.cftg-btn-next' ) )   this.next();
        if ( e.target.closest( '.cftg-btn-back' ) )   this.back();
        if ( e.target.closest( '.cftg-btn-submit' ) ) this.submit();
      } );

      this.wrap.addEventListener( 'change', e => {
        const el = e.target;

        /* Parts-missing toggle — show conditional; auto-advance only on "No" */
        if ( el.classList.contains( 'cftg-toggle-missing' ) ) {
          const step = el.closest( '.cftg-step' );
          const cf   = step?.querySelector( '.cftg-conditional-field' );
          if ( cf ) cf.style.display = el.value === 'Yes' ? 'block' : 'none';
          if ( el.value === 'No' ) setTimeout( () => this.next(), 320 );
          return;
        }

        /* Load-size: "I know the exact weight" reveals input, no auto-advance */
        if ( el.type === 'radio' && el.name === 'load_size' ) {
          const step = el.closest( '.cftg-step' );
          const cf   = step?.querySelector( '.cftg-conditional-weight' );
          if ( el.hasAttribute( 'data-reveal-weight' ) ) {
            if ( cf ) cf.style.display = 'block';
            return; // stay on step so user can type the weight
          }
          if ( cf ) cf.style.display = 'none';
          setTimeout( () => this.next(), 320 );
          return;
        }

        /* Auto-advance all other radio buttons */
        if ( el.type === 'radio' ) {
          setTimeout( () => this.next(), 320 );
        }
      } );
    }
  }

  /* ── UTM / GCLID: read whatever the site-wide tracking script
        stashed in sessionStorage['scad_tracking_params'] and append
        the canonical lowercase keys to the form submission. ── */
  const CUSTOM_KEYS = [
    'utmcampaign_custom', 'utmmedium_custom', 'utmcontent_custom',
    'utmkeyword_custom',  'utmterm_custom',    'gclid_custom'
  ];

  function appendUtms( data ) {
    let stored = {};
    try { stored = JSON.parse( sessionStorage.getItem( 'scad_tracking_params' ) || '{}' ); } catch ( e ) {}
    CUSTOM_KEYS.forEach( k => {
      if ( stored[ k ] ) data.append( k, stored[ k ] );
    } );
  }

  document.addEventListener( 'DOMContentLoaded', () => {
    const ts = Math.floor( Date.now() / 1000 );
    document.querySelectorAll( '.cftg-loaded-at' ).forEach( el => el.value = ts );
    document.querySelectorAll( '.cftg-wrap[data-total]' ).forEach( wrap => new CftgForm( wrap ) );
  } );

} )();
