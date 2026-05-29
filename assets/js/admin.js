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

  /* ── Fetch GHL custom fields ── */
  /* Map GHL field key/name → plugin option name, so we can autofill UUIDs */
  const KEY_MAP = {
    /* Bin Estimate */
    'contact.what_do_you_need_to_dispose':              'cftg_cf_dispose_types',
    'contact.when_do_you_need_your_bin':                'cftg_cf_delivery_date',
    'contact.how_long_do_you_need_the_bin_for':         'cftg_cf_bin_duration',
    'contact.what_size_do_you_need':                    'cftg_cf_bin_size',
    'contact.where_would_you_like_the_bin_delivered':   'cftg_cf_bin_delivery_postal',
    /* Scrap Metal */
    'contact.what_do_you_need_to_scrap':                'cftg_cf_scrap_types',
    'contact.load_size':                                'cftg_cf_load_size',
    'contact.exact_weight':                             'cftg_cf_exact_weight',
    'contact.scrap_postal':                             'cftg_cf_scrap_postal',
    /* Vehicle Quote */
    'contact.vehicle_year':                             'cftg_cf_vehicle_year',
    'contact.vehicle_make':                             'cftg_cf_vehicle_make',
    'contact.vehicle_model':                            'cftg_cf_vehicle_model',
    'contact.is_the_engine_running':                    'cftg_cf_engine_running',
    'contact.are_parts_missing':                        'cftg_cf_parts_missing',
    'contact.whats_missing':                            'cftg_cf_missing_parts_notes',
    'contact.what_is_the_postal_code_of_the_pickup_location': 'cftg_cf_vehicle_pickup_postal',
    /* UTM custom — canonical lowercase _custom schema */
    'contact.utmcampaign_custom':                       'cftg_cf_utmcampaign_custom',
    'contact.utmmedium_custom':                         'cftg_cf_utmmedium_custom',
    'contact.utmcontent_custom':                        'cftg_cf_utmcontent_custom',
    'contact.utmkeyword_custom':                        'cftg_cf_utmkeyword_custom',
    'contact.utmterm_custom':                           'cftg_cf_utmterm_custom',
    'contact.gclid_custom':                             'cftg_cf_gclid_custom',
    /* Source CFT */
    'contact.source_cft':                               'cftg_cf_source_cft',
  };
  /* Fallback by normalised field name (lowercase, alphanumerics) */
  const NAME_MAP = {
    'load size':            'cftg_cf_load_size',
    'exact weight':         'cftg_cf_exact_weight',
    'scrap metal postal':   'cftg_cf_scrap_postal',
    'source cft':           'cftg_cf_source_cft',
    'utmcampaign_custom':   'cftg_cf_utmcampaign_custom',
    'utmmedium_custom':     'cftg_cf_utmmedium_custom',
    'utmcontent_custom':    'cftg_cf_utmcontent_custom',
    'utmkeyword_custom':    'cftg_cf_utmkeyword_custom',
    'utmterm_custom':       'cftg_cf_utmterm_custom',
    'gclid_custom':         'cftg_cf_gclid_custom',
  };

  function matchOption( field ) {
    const key  = ( field.key  || '' ).toLowerCase().trim();
    const name = ( field.name || '' ).toLowerCase().trim().replace( /[?]/g, '' );
    return KEY_MAP[ key ] || NAME_MAP[ name ] || null;
  }

  function initFetchFields() {
    const btn = document.getElementById( 'cftg-fetch-fields-btn' );
    if ( ! btn ) return;

    btn.addEventListener( 'click', () => {
      const resultEl = document.getElementById( 'cftg-fields-result' );

      btn.disabled    = true;
      btn.textContent = 'Fetching…';

      const data = new FormData();
      data.append( 'action', 'cftg_fetch_fields' );
      data.append( 'nonce',  cftgAdmin.nonce );

      fetch( cftgAdmin.ajaxUrl, { method: 'POST', body: data } )
        .then( r => r.json() )
        .then( res => {
          if ( res.success && res.data.fields.length ) {
            /* Auto-fill matching UUID inputs. Process exact KEY_MAP matches
               first, then NAME_MAP fallbacks, so primary keys win over loose
               name matches and we never overwrite a slot already filled by
               an exact key. */
            let filled = 0;
            const filledFields = [];
            const filledSlots  = new Set();

            const fill = ( f, opt ) => {
              if ( ! opt || filledSlots.has( opt ) ) return;
              const input = document.getElementById( opt );
              if ( ! input ) return;
              input.value = f.id;
              input.style.background = '#dcfce7';
              filledSlots.add( opt );
              filled++;
              filledFields.push( f.name );
            };

            /* Pass 1: exact key matches (KEY_MAP) */
            res.data.fields.forEach( f => {
              const key = ( f.key || '' ).toLowerCase().trim();
              fill( f, KEY_MAP[ key ] );
            } );
            /* Pass 2: name fallbacks (NAME_MAP) */
            res.data.fields.forEach( f => {
              const name = ( f.name || '' ).toLowerCase().trim().replace( /[?]/g, '' );
              fill( f, NAME_MAP[ name ] );
            } );

            let html = '';
            if ( filled > 0 ) {
              html += `<div style="background:#dcfce7;border:1px solid #16a34a;color:#166534;padding:10px 14px;border-radius:6px;margin-bottom:10px;font-weight:600">
                ✓ Auto-filled ${ filled } field${ filled !== 1 ? 's' : '' }: ${ filledFields.join( ', ' ) }
                <div style="font-weight:400;margin-top:4px;font-size:12px">Click <strong>Save Settings</strong> below to save these mappings.</div>
              </div>`;
            }

            if ( res.data.debug && res.data.debug.length ) {
              html += '<div style="background:#fef3c7;border:1px solid #fbbf24;color:#78350f;padding:8px 12px;border-radius:6px;margin-bottom:10px;font-size:11px;font-family:monospace">';
              html += '<strong>GHL API responses:</strong><br>' + res.data.debug.join( '<br>' );
              html += '</div>';
            }
            html += '<div style="font-size:12px;color:#666;margin-bottom:6px">All custom fields returned by GHL (' + res.data.fields.length + ' total):</div>';
            html += '<table style="width:100%;border-collapse:collapse;font-size:12px">';
            html += '<tr style="background:#f0f0f0"><th style="padding:6px 8px;text-align:left;border:1px solid #ddd">Field Name</th><th style="padding:6px 8px;text-align:left;border:1px solid #ddd">Key</th><th style="padding:6px 8px;text-align:left;border:1px solid #ddd">UUID</th><th style="padding:6px 8px;text-align:left;border:1px solid #ddd">Status</th></tr>';
            res.data.fields.forEach( f => {
              const opt = matchOption( f );
              const matched = opt ? '<span style="color:#16a34a;font-weight:600">✓ matched</span>' : '<span style="color:#999">—</span>';
              html += `<tr>
                <td style="padding:5px 8px;border:1px solid #ddd">${ f.name }</td>
                <td style="padding:5px 8px;border:1px solid #ddd"><code style="background:#fff8e1;padding:2px 4px">${ f.key }</code></td>
                <td style="padding:5px 8px;border:1px solid #ddd"><code style="font-size:10px">${ f.id }</code></td>
                <td style="padding:5px 8px;border:1px solid #ddd">${ matched }</td>
              </tr>`;
            } );
            html += '</table>';
            resultEl.innerHTML    = html;
            resultEl.className    = 'cftg-test-result';
            resultEl.style.display = 'block';
          } else {
            showResult( resultEl, 'error', res.data?.message || 'No fields found.' );
          }
        } )
        .catch( () => showResult( resultEl, 'error', 'Network error.' ) )
        .finally( () => {
          btn.disabled    = false;
          btn.innerHTML   = '<span class="dashicons dashicons-download" style="vertical-align:middle;margin-right:4px"></span> Fetch Fields from GHL';
        } );
    } );
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
    initFetchFields();
    initCopyButtons();
    initMediaUploader();
    initOpacityRanges();
    initColorLabels();
  } );

} )();
