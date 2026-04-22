<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* ── Hex → rgba() ── */
function cftg_hex_to_rgba( $hex, $opacity = 1.0 ) {
  $hex = ltrim( $hex, '#' );
  if ( strlen( $hex ) === 3 ) {
    $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
  }
  $r = hexdec( substr( $hex, 0, 2 ) );
  $g = hexdec( substr( $hex, 2, 2 ) );
  $b = hexdec( substr( $hex, 4, 2 ) );
  return "rgba($r,$g,$b,$opacity)";
}

/* ── Lighten a hex colour ── */
function cftg_lighten_hex( $hex, $amount = 25 ) {
  $hex = ltrim( $hex, '#' );
  if ( strlen( $hex ) === 3 ) {
    $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
  }
  $r = min( 255, hexdec( substr( $hex, 0, 2 ) ) + $amount );
  $g = min( 255, hexdec( substr( $hex, 2, 2 ) ) + $amount );
  $b = min( 255, hexdec( substr( $hex, 4, 2 ) ) + $amount );
  return sprintf( '#%02x%02x%02x', $r, $g, $b );
}

/* ── Default design per form ── */
function cftg_design_defaults( $form_type ) {
  $all = [
    'vehicle_quote' => [
      'bg_image'        => '',
      'overlay_color_l' => '#0a0a0a',
      'overlay_color_r' => '#0f1520',
      'overlay_opacity' => 75,
      'accent_color'    => '#eeae00',
      'badge'           => 'Free Quote',
      'title'           => 'Earn Money on Your',
      'title_accent'    => 'Scrap or Used Vehicle',
      'desc'            => "At CFT Recycling, we've made selling your car simple and hassle-free. Get a cash offer in minutes.",
      'feat_1'          => 'Free towing included',
      'feat_2'          => 'Same-day quotes',
      'feat_3'          => 'Cash on the spot',
      'feat_4'          => 'Zero paperwork',
      'phone'           => '613-831-2900',
      'email'           => 'info@cftgroup.ca',
      'hours'           => 'Mon–Fri 8am–6pm',
    ],
    'scrap_metal' => [
      'bg_image'        => '',
      'overlay_color_l' => '#0a0a0a',
      'overlay_color_r' => '#0f1520',
      'overlay_opacity' => 75,
      'accent_color'    => '#eeae00',
      'badge'           => 'Instant Offer',
      'title'           => 'Earn Money for Your',
      'title_accent'    => 'Scrap Metal',
      'desc'            => 'At CFT Recycling, turning your scrap metal into cash is quick and easy. Drop off your materials and get paid on the spot.',
      'feat_1'          => 'Top market prices',
      'feat_2'          => 'Multiple locations',
      'feat_3'          => 'Cash on the spot',
      'feat_4'          => 'All metals accepted',
      'phone'           => '613-831-2900',
      'email'           => 'info@cftgroup.ca',
      'hours'           => 'Mon–Fri 8am–6pm',
    ],
    'bin_estimate' => [
      'bg_image'        => '',
      'overlay_color_l' => '#0a0a0a',
      'overlay_color_r' => '#0f1520',
      'overlay_opacity' => 75,
      'accent_color'    => '#eeae00',
      'badge'           => 'Free Estimate',
      'title'           => 'Get Your',
      'title_accent'    => 'Bin Dumpster Estimate',
      'desc'            => "At CFT Recycling, we make renting a dumpster fast and friendly. Choose your bin size and we'll handle the rest.",
      'feat_1'          => 'Prompt delivery',
      'feat_2'          => 'Competitive pricing',
      'feat_3'          => 'Eco-responsible disposal',
      'feat_4'          => 'Commercial & residential',
      'phone'           => '613-831-2900',
      'email'           => 'info@cftgroup.ca',
      'hours'           => 'Mon–Fri 8am–6pm',
    ],
  ];
  return $all[ $form_type ] ?? [];
}

/* ── Get merged design (saved + defaults) ── */
function cftg_get_design( $form_type ) {
  $saved = get_option( "cftg_design_{$form_type}", [] );
  return wp_parse_args( $saved, cftg_design_defaults( $form_type ) );
}

/* ── Build inline style strings for a section ── */
function cftg_section_styles( $form_type ) {
  $d       = cftg_get_design( $form_type );
  $accent  = sanitize_hex_color( $d['accent_color'] ) ?: '#eeae00';
  $acc_l   = cftg_lighten_hex( $accent, 20 );
  $acc_dim = cftg_hex_to_rgba( $accent, 0.10 );
  $acc_soft= cftg_hex_to_rgba( $accent, 0.08 );
  $acc_bdr = cftg_hex_to_rgba( $accent, 0.30 );

  $op   = max( 0, min( 100, intval( $d['overlay_opacity'] ) ) ) / 100;
  $op_r = round( $op * 0.45, 2 );

  $l_rgba = cftg_hex_to_rgba( $d['overlay_color_l'], $op );
  $r_rgba = cftg_hex_to_rgba( $d['overlay_color_r'], $op_r );

  $bg_img = $d['bg_image'] ? 'background-image:url(' . esc_url( $d['bg_image'] ) . ');' : '';

  return [
    'section' => $bg_img . "--cftg-gold:{$accent};--cftg-gold-l:{$acc_l};--cftg-gold-dim:{$acc_dim};--cftg-gold-soft:{$acc_soft};--cftg-gold-border:{$acc_bdr};",
    'overlay' => "background:linear-gradient(to right,{$l_rgba},{$r_rgba});",
  ];
}
