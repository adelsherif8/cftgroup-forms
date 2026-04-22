( function () {
  'use strict';

  /* ── Test Connection ── */
  function initTestConnection() {
    const btn = document.getElementById( 'cftg-test-btn' );
    if ( ! btn ) return;
    btn.addEventListener( 'click', () => {
      const apiKey     = document.getElementById( 'cftg_ghl_api_key' )?.value.trim();
      const locationId = document.getElementById( 'cftg_ghl_location_id' )?.value.trim();
      const resultEl   = document.getElementById( 'cftg-test-result' );
      if ( ! apiKey || ! locationId ) { showResult( resultEl, 'error', 'Enter both API Key and Location ID first.' ); return; }
      btn.disabled = true; btn.textContent = 'Testing…';
      if ( resultEl ) resultEl.className = 'cftg-test-result';
      const data = new FormData();
      data.append( 'action', 'cftg_test_connection' );
      data.append( 'nonce',  cftgAdmin.nonce );
      data.append( 'api_key', apiKey );
      data.append( 'location_id', locationId );
      fetch( cftgAdmin.ajaxUrl, { method: 'POST', body: data } )
        .then( r => r.json() )
        .then( res => {
          if ( res.success ) showResult( resultEl, 'success', '✓ Connected! Location: ' + ( res.data.name || locationId ) );
          else showResult( resultEl, 'error', '✗ ' + ( res.data?.message || 'Connection failed.' ) );
        } )
        .catch( () => showResult( resultEl, 'error', '✗ Network error.' ) )
        .finally( () => { btn.disabled = false; btn.textContent = 'Test Connection'; } );
    } );
  }

  function showResult( el, type, msg ) {
    if ( ! el ) return;
    el.textContent = msg; el.className = 'cftg-test-result cftg-result-' + type; el.style.display = 'block';
  }

  /* ── Copy shortcode buttons ── */
  function initCopyButtons() {
    document.querySelectorAll( '.cftg-copy-btn' ).forEach( btn => {
      btn.addEventListener( 'click', () => {
        const code = btn.dataset.code;
        if ( ! code ) return;
        navigator.clipboard.writeText( code ).then( () => {
          const orig = btn.innerHTML;
          btn.innerHTML = '✓ Copied!'; btn.classList.add( 'copied' );
          setTimeout( () => { btn.innerHTML = orig; btn.classList.remove( 'copied' ); }, 1800 );
        } ).catch( () => {
          const ta = document.createElement( 'textarea' );
          ta.value = code; ta.style.cssText = 'position:fixed;opacity:0';
          document.body.appendChild( ta ); ta.select(); document.execCommand( 'copy' ); document.body.removeChild( ta );
          btn.textContent = '✓ Copied!';
          setTimeout( () => { btn.textContent = 'Copy'; }, 1800 );
        } );
      } );
    } );
  }

  /* ── WP Media Uploader ── */
  function initMediaUploader() {
    document.querySelectorAll( '.cftg-media-btn' ).forEach( btn => {
      btn.addEventListener( 'click', e => {
        e.preventDefault();
        const inputEl   = document.getElementById( btn.dataset.target );
        const previewEl = document.getElementById( btn.dataset.preview );

        const frame = wp.media( {
          title:    'Select Background Image',
          button:   { text: 'Use this image' },
          multiple: false,
          library:  { type: 'image' },
        } );

        frame.on( 'select', () => {
          const att = frame.state().get( 'selection' ).first().toJSON();
          if ( inputEl )   inputEl.value       = att.url;
          if ( previewEl ) { previewEl.src = att.url; previewEl.style.display = 'block'; }
        } );

        frame.open();
      } );
    } );

    document.querySelectorAll( '.cftg-media-clear' ).forEach( btn => {
      btn.addEventListener( 'click', () => {
        const inputEl   = document.getElementById( btn.dataset.target );
        const previewEl = document.getElementById( btn.dataset.preview );
        if ( inputEl )   inputEl.value        = '';
        if ( previewEl ) { previewEl.src = ''; previewEl.style.display = 'none'; }
      } );
    } );
  }

  /* ── Opacity range live label ── */
  function initOpacityRanges() {
    document.querySelectorAll( '.cftg-opacity-range' ).forEach( range => {
      const label = range.closest( '.cftg-opacity-field' )?.querySelector( '.cftg-opacity-val' );
      if ( label ) range.addEventListener( 'input', () => { label.textContent = range.value + '%'; } );
    } );
  }

  /* ── Colour input live hex label ── */
  function initColorLabels() {
    document.querySelectorAll( '.cftg-color-input' ).forEach( input => {
      const label = input.nextElementSibling;
      if ( label && label.classList.contains( 'cftg-color-label' ) ) {
        input.addEventListener( 'input', () => { label.textContent = input.value; } );
      }
    } );
  }

  document.addEventListener( 'DOMContentLoaded', () => {
    initTestConnection();
    initCopyButtons();
    initMediaUploader();
    initOpacityRanges();
    initColorLabels();
  } );

} )();
