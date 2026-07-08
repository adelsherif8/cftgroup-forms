/* ================================================================
   CFT Group Forms — Quiz engine (generic, data-attribute driven)
   Works for any .cftg-quiz wrapper. Steps:
     data-step="0"           welcome
     data-step="1..total"    questions
     data-step="total+1"     success
   ================================================================ */
( function () {
  'use strict';

  const CUSTOM_KEYS = [
    'utmcampaign_custom', 'utmmedium_custom', 'utmcontent_custom',
    'utmkeyword_custom',  'utmterm_custom',    'gclid_custom'
  ];

  function sessionId() {
    try {
      let id = localStorage.getItem( 'cftg_session_id' );
      if ( ! id ) {
        id = 'cftg-' + Date.now().toString( 36 ) + '-' + Math.random().toString( 36 ).slice( 2, 10 );
        localStorage.setItem( 'cftg_session_id', id );
      }
      return id;
    } catch ( e ) { return 'cftg-anon'; }
  }

  class Quiz {
    constructor( wrap ) {
      this.wrap     = wrap;
      this.formType = wrap.dataset.formType;
      this.total    = parseInt( wrap.dataset.total, 10 );
      this.current  = 0;
      this._seen    = new Set();
      this._saveKey = 'cftg_quiz_' + this.formType;

      this.progress = wrap.querySelector( '.qz-progress' );
      this.progText = wrap.querySelector( '.qz-progress-text' );
      this.progPct  = wrap.querySelector( '.qz-progress-pct' );
      this.progFill = wrap.querySelector( '.qz-progress-fill' );
      this.errorEl  = wrap.querySelector( '.qz-error' );

      wrap.querySelectorAll( '.cftg-loaded-at' ).forEach( el => el.value = Math.floor( Date.now() / 1000 ) );

      this._bind();
      this._initPhone();
      this._restore();
      this._show( this.current );
      this._track( 'view', 0 );
    }

    /* ── International phone field ── */
    _initPhone() {
      const el = this.wrap.querySelector( 'input[type="tel"]' );
      if ( ! el || ! window.intlTelInput ) return;
      this.iti = window.intlTelInput( el, {
        initialCountry:     'ca',
        preferredCountries: [ 'ca', 'us' ],
        separateDialCode:   true,
        utilsScript:        'https://cdn.jsdelivr.net/npm/intl-tel-input@18.5.3/build/js/utils.js',
      } );
    }

    /* ── Save / restore progress across page reloads ── */
    _save() {
      try {
        const answers = {};
        this.wrap.querySelectorAll( 'input, select, textarea' ).forEach( el => {
          if ( el.name && el.value && el.type !== 'hidden' ) answers[ el.name ] = el.value;
          else if ( el.name && el.type === 'hidden' && el.value ) answers[ el.name ] = el.value;
        } );
        localStorage.setItem( this._saveKey, JSON.stringify( { step: this.current, answers, ts: Date.now() } ) );
      } catch ( e ) {}
    }
    _restore() {
      let saved;
      try { saved = JSON.parse( localStorage.getItem( this._saveKey ) || 'null' ); } catch ( e ) { saved = null; }
      /* Expire saved progress after 24h */
      if ( ! saved || ! saved.answers || ( Date.now() - ( saved.ts || 0 ) ) > 864e5 ) return;

      Object.entries( saved.answers ).forEach( ( [ name, value ] ) => {
        const field = this.wrap.querySelector( '[name="' + name + '"]' );
        if ( field && field.type !== 'hidden' ) field.value = value;
        /* Restore selected option state + hidden values */
        this.wrap.querySelectorAll( '.qz-option[data-name="' + name + '"]' ).forEach( opt => {
          const vals = String( value ).split( ', ' );
          if ( vals.includes( opt.dataset.value ) ) opt.classList.add( 'selected' );
        } );
        if ( this.wrap.querySelector( '.qz-option[data-name="' + name + '"]' ) ) this._setHidden( name, value );
      } );
      /* Resume at the saved step (but never on the success screen) */
      if ( typeof saved.step === 'number' && saved.step >= 1 && saved.step <= this.total ) {
        this.current = saved.step;
      }
    }
    _clearSave() { try { localStorage.removeItem( this._saveKey ); } catch ( e ) {} }

    _track( event, step ) {
      const d = new FormData();
      d.append( 'action', 'cftg_track' );
      d.append( 'session', sessionId() );
      d.append( 'form_type', this.formType );
      d.append( 'event', event );
      d.append( 'step', String( step || 0 ) );
      d.append( 'page_url', window.location.origin + window.location.pathname );
      fetch( cftgData.ajaxUrl, { method: 'POST', body: d, keepalive: true } ).catch( () => {} );
    }

    _show( n ) {
      this.wrap.querySelectorAll( '.qz-step' ).forEach( s => {
        s.classList.toggle( 'active', parseInt( s.dataset.step, 10 ) === n );
      } );
      if ( n >= 1 && n <= this.total ) {
        this.progress.style.display = '';
        this.progText.textContent = 'Question ' + n + ' of ' + this.total;
        this.progPct.textContent  = Math.round( ( n / this.total ) * 100 ) + '%';
        this.progFill.style.width = ( n / this.total ) * 100 + '%';
        if ( ! this._seen.has( n ) ) { this._seen.add( n ); this._track( 'step', n ); }
        this._save();
        /* Autofocus first visible text input for faster completion */
        const firstInput = this.wrap.querySelector( '.qz-step[data-step="' + n + '"] input:not([type=hidden]):not([type=radio])' );
        if ( firstInput ) setTimeout( () => firstInput.focus( { preventScroll: true } ), 420 );
      } else {
        this.progress.style.display = 'none';
      }
      window.scrollTo( { top: 0, behavior: 'smooth' } );
    }

    _validate() {
      const step = this.wrap.querySelector( '.qz-step[data-step="' + this.current + '"]' );
      if ( ! step ) return true;
      for ( const inp of step.querySelectorAll( 'input[required], select[required]' ) ) {
        if ( inp.offsetParent === null ) continue; // skip hidden conditionals
        if ( ! inp.value.trim() ) {
          this._error( 'Please fill in this field before continuing.' );
          inp.focus();
          return false;
        }
      }
      /* Multi-select steps: at least one option chosen */
      const multi = step.querySelector( '.qz-option[data-multi]' );
      if ( multi ) {
        const name = multi.dataset.name;
        const any  = this.wrap.querySelector( 'input[type="hidden"][name="' + name + '"]' );
        if ( ! any || ! any.value ) {
          this._error( 'Please select at least one option.' );
          return false;
        }
      }
      this._clearError();
      return true;
    }

    _error( msg )   { if ( this.errorEl ) { this.errorEl.textContent = msg; this.errorEl.style.display = 'block'; } }
    _clearError()   { if ( this.errorEl ) { this.errorEl.style.display = 'none'; } }

    next() {
      if ( ! this._validate() ) return;
      if ( this.current < this.total ) { this.current++; this._show( this.current ); }
    }
    back() {
      if ( this.current > 0 ) { this.current--; this._show( this.current ); }
    }

    _setHidden( name, value ) {
      let h = this.wrap.querySelector( 'input[type="hidden"][name="' + name + '"]' );
      if ( ! h ) {
        h = document.createElement( 'input' );
        h.type = 'hidden'; h.name = name;
        this.wrap.appendChild( h );
      }
      h.value = value;
      return h;
    }

    _onOption( opt ) {
      const name  = opt.dataset.name;
      const value = opt.dataset.value;

      /* Multi-select: toggle in a comma-joined hidden field, no auto-advance */
      if ( opt.hasAttribute( 'data-multi' ) ) {
        opt.classList.toggle( 'selected' );
        const chosen = [ ...this.wrap.querySelectorAll( '.qz-option[data-multi][data-name="' + name + '"].selected' ) ]
          .map( o => o.dataset.value );
        this._setHidden( name, chosen.join( ', ' ) );
        this._clearError();
        return;
      }

      /* Single-select */
      opt.parentElement.querySelectorAll( '.qz-option' ).forEach( o => o.classList.remove( 'selected' ) );
      opt.classList.add( 'selected' );
      this._setHidden( name, value );

      const reveal = opt.dataset.reveal;
      const step   = opt.closest( '.qz-step' );
      /* Hide any sibling reveals first */
      step.querySelectorAll( '.qz-reveal' ).forEach( r => r.classList.remove( 'visible' ) );
      if ( reveal ) {
        const box = step.querySelector( '.qz-reveal[data-reveal-id="' + reveal + '"]' );
        if ( box ) { box.classList.add( 'visible' ); return; } // stay so user can fill it
      }
      setTimeout( () => this.next(), 300 );
    }

    _bind() {
      this.wrap.addEventListener( 'click', e => {
        if ( e.target.closest( '.qz-btn-next' ) )   { this.next(); return; }
        if ( e.target.closest( '.qz-btn-back' ) )   { this.back(); return; }
        if ( e.target.closest( '.qz-btn-submit' ) ) { this.submit(); return; }
        const opt = e.target.closest( '.qz-option' );
        if ( opt ) this._onOption( opt );
      } );
    }

    _appendUtms( data ) {
      let stored = {};
      try { stored = JSON.parse( sessionStorage.getItem( 'scad_tracking_params' ) || '{}' ); } catch ( e ) {}
      CUSTOM_KEYS.forEach( k => { if ( stored[ k ] ) data.append( k, stored[ k ] ); } );
    }

    _freshNonce() {
      const d = new FormData();
      d.append( 'action', 'cftg_get_nonce' );
      return fetch( cftgData.ajaxUrl, { method: 'POST', body: d, cache: 'no-store' } )
        .then( r => r.json() )
        .then( r => ( r && r.success && r.data && r.data.nonce ) ? r.data.nonce : cftgData.nonce )
        .catch( () => cftgData.nonce );
    }

    submit() {
      if ( ! this._validate() ) return;
      const btn = this.wrap.querySelector( '.qz-btn-submit' );
      const label = btn ? btn.textContent : '';
      if ( btn ) { btn.disabled = true; btn.textContent = 'Sending…'; }

      this._freshNonce().then( nonce => {
        const data = new FormData();
        data.append( 'action', 'cftg_submit' );
        data.append( 'nonce', nonce );
        data.append( 'form_type', this.formType );
        this.wrap.querySelectorAll( 'input, select, textarea' ).forEach( el => {
          if ( ! el.name ) return;
          if ( el.type === 'tel' && this.iti ) { data.set( el.name, this.iti.getNumber() || el.value ); }
          else { data.set( el.name, el.value ); }
        } );
        this._appendUtms( data );
        return fetch( cftgData.ajaxUrl, { method: 'POST', body: data } );
      } )
        .then( r => r.json() )
        .then( res => {
          if ( res.success ) {
            this._track( 'submit', 0 );
            this._clearSave();
            this.current = this.total + 1;
            this._show( this.current );
          } else {
            this._error( res.data?.message || 'Something went wrong. Please try again.' );
            if ( btn ) { btn.disabled = false; btn.textContent = label; }
          }
        } )
        .catch( () => {
          this._error( 'Network error. Please try again.' );
          if ( btn ) { btn.disabled = false; btn.textContent = label; }
        } );
    }
  }

  document.addEventListener( 'DOMContentLoaded', () => {
    document.querySelectorAll( '.cftg-quiz[data-form-type]' ).forEach( w => new Quiz( w ) );
  } );

} )();
