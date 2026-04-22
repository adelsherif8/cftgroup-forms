( function () {
  'use strict';

  class CftgForm {
    constructor( wrap ) {
      this.wrap     = wrap;
      this.formType = wrap.dataset.formType;
      this.total    = parseInt( wrap.dataset.total, 10 );
      this.current  = 1;

      this.progFill  = wrap.querySelector( '.cftg-prog-fill' );
      this.progPct   = wrap.querySelector( '.cftg-pct-label' );
      this.stepLabel = wrap.querySelector( '.cftg-step-label' );
      this.errorEl   = wrap.querySelector( '.cftg-error-msg' );

      this._bind();
      this._refresh();
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

      const pct = isSuccess ? 100 : Math.round( ( this.current / this.total ) * 100 );
      if ( this.progFill )  this.progFill.style.width  = pct + '%';
      if ( this.progPct )   this.progPct.textContent   = pct + '%';
      if ( this.stepLabel ) this.stepLabel.textContent = 'Step ' + ( isSuccess ? this.total : this.current ) + ' of ' + this.total;
    }

    /* ── Navigation ── */
    next() {
      if ( ! this._validate() ) return;
      if ( this.current < this.total ) {
        this.current++;
        this._refresh();
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
        if ( ! inp.value.trim() ) {
          this._showError( 'Please fill in all required fields.' );
          inp.focus();
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

    /* ── Submit ── */
    submit() {
      if ( ! this._validate() ) return;

      const btn = this.wrap.querySelector( '.cftg-btn-submit' );
      if ( btn ) { btn.disabled = true; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Sending…'; }

      const data = new FormData();
      data.append( 'action',    'cftg_submit' );
      data.append( 'nonce',     cftgData.nonce );
      data.append( 'form_type', this.formType );

      this.wrap.querySelectorAll( 'input, select, textarea' ).forEach( el => {
        if ( ! el.name ) return;
        if ( el.type === 'checkbox' )      { if ( el.checked ) data.append( el.name, el.value ); }
        else if ( el.type === 'radio' )    { if ( el.checked ) data.set( el.name, el.value ); }
        else                               { data.set( el.name, el.value ); }
      } );

      fetch( cftgData.ajaxUrl, { method: 'POST', body: data } )
        .then( r => r.json() )
        .then( res => {
          if ( res.success ) {
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

        /* Auto-advance all other radio buttons */
        if ( el.type === 'radio' ) {
          setTimeout( () => this.next(), 320 );
        }
      } );
    }
  }

  document.addEventListener( 'DOMContentLoaded', () => {
    document.querySelectorAll( '.cftg-wrap[data-total]' ).forEach( wrap => new CftgForm( wrap ) );
  } );

} )();
