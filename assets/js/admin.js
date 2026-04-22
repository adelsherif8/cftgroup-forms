( function () {
  'use strict';

  /* ── Tab switching ── */
  function initTabs() {
    const nav = document.querySelector( '.cftg-tab-nav' );
    if ( ! nav ) return;

    nav.addEventListener( 'click', e => {
      const btn = e.target.closest( '.cftg-tab-btn' );
      if ( ! btn ) return;

      const target = btn.dataset.tab;

      nav.querySelectorAll( '.cftg-tab-btn' ).forEach( b => b.classList.remove( 'active' ) );
      btn.classList.add( 'active' );

      document.querySelectorAll( '.cftg-tab-panel' ).forEach( p => {
        p.classList.toggle( 'active', p.dataset.panel === target );
      } );

      /* persist active tab in URL hash so page reload keeps the tab */
      history.replaceState( null, '', '#' + target );
    } );

    /* restore tab from hash on load */
    const hash = location.hash.replace( '#', '' );
    if ( hash ) {
      const restore = nav.querySelector( `[data-tab="${ hash }"]` );
      if ( restore ) restore.click();
    }
  }

  /* ── Test Connection ── */
  function initTestConnection() {
    const btn = document.getElementById( 'cftg-test-conn-btn' );
    if ( ! btn ) return;

    btn.addEventListener( 'click', () => {
      const apiKey    = document.getElementById( 'cftg_ghl_api_key' )?.value.trim();
      const locationId = document.getElementById( 'cftg_ghl_location_id' )?.value.trim();
      const resultEl  = document.getElementById( 'cftg-test-result' );

      if ( ! apiKey || ! locationId ) {
        showResult( resultEl, 'error', 'Please enter both your API Key and Location ID first.' );
        return;
      }

      btn.disabled = true;
      btn.textContent = 'Testing…';
      if ( resultEl ) resultEl.className = 'cftg-test-result';

      const data = new FormData();
      data.append( 'action',      'cftg_test_connection' );
      data.append( 'nonce',       cftgAdmin.nonce );
      data.append( 'api_key',     apiKey );
      data.append( 'location_id', locationId );

      fetch( cftgAdmin.ajaxUrl, { method: 'POST', body: data } )
        .then( r => r.json() )
        .then( res => {
          if ( res.success ) {
            showResult( resultEl, 'success', '✓ Connected successfully! Location: ' + ( res.data.name || locationId ) );
          } else {
            const msg = res.data?.message || 'Connection failed. Check your credentials.';
            showResult( resultEl, 'error', '✗ ' + msg );
          }
        } )
        .catch( () => showResult( resultEl, 'error', '✗ Network error. Could not reach the server.' ) )
        .finally( () => {
          btn.disabled = false;
          btn.textContent = 'Test Connection';
        } );
    } );
  }

  function showResult( el, type, msg ) {
    if ( ! el ) return;
    el.textContent  = msg;
    el.className    = 'cftg-test-result cftg-result-' + type;
    el.style.display = 'block';
  }

  /* ── Copy shortcode buttons ── */
  function initCopyButtons() {
    document.querySelectorAll( '.cftg-copy-btn' ).forEach( btn => {
      btn.addEventListener( 'click', () => {
        const code = btn.dataset.code;
        if ( ! code ) return;
        navigator.clipboard.writeText( code ).then( () => {
          const orig = btn.textContent;
          btn.textContent = 'Copied!';
          btn.classList.add( 'copied' );
          setTimeout( () => {
            btn.textContent = orig;
            btn.classList.remove( 'copied' );
          }, 1800 );
        } ).catch( () => {
          /* fallback for older browsers */
          const ta = document.createElement( 'textarea' );
          ta.value = code;
          ta.style.position = 'fixed';
          ta.style.opacity  = '0';
          document.body.appendChild( ta );
          ta.select();
          document.execCommand( 'copy' );
          document.body.removeChild( ta );
          btn.textContent = 'Copied!';
          setTimeout( () => { btn.textContent = 'Copy'; }, 1800 );
        } );
      } );
    } );
  }

  document.addEventListener( 'DOMContentLoaded', () => {
    initTabs();
    initTestConnection();
    initCopyButtons();
  } );

} )();
