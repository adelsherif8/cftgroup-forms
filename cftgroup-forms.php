<?php
/**
 * Plugin Name:  CFT Group Forms
 * Plugin URI:   https://upwork.com/freelancers/adelsherif8
 * Description:  Multi-step quote forms (Bin Estimate, Scrap Metal, Vehicle Quote) with GoHighLevel CRM integration.
 * Version:      1.2.2
 * Author:       Adel Emad
 * Author URI:   https://upwork.com/freelancers/adelsherif8
 * License:      GPL-2.0+
 * Text Domain:  cftgroup-forms
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'CFTG_VERSION',     '1.2.2' );
define( 'CFTG_DIR',         plugin_dir_path( __FILE__ ) );
define( 'CFTG_URL',         plugin_dir_url( __FILE__ ) );
define( 'CFTG_BASENAME',    plugin_basename( __FILE__ ) );
define( 'CFTG_GITHUB_REPO', 'adelsherif8/cftgroup-forms' );

/* ── Includes ─────────────────────────────────────────────── */
require_once CFTG_DIR . 'includes/helpers.php';
require_once CFTG_DIR . 'includes/class-ghl-api.php';
require_once CFTG_DIR . 'includes/class-form-handler.php';
require_once CFTG_DIR . 'includes/class-updater.php';
require_once CFTG_DIR . 'admin/admin-page.php';
require_once CFTG_DIR . 'admin/instructions-page.php';

/* ── Auto-updater (checks GitHub releases) ────────────────── */
new CFTG_Updater( CFTG_GITHUB_REPO, __FILE__, CFTG_VERSION );

/* ── Shortcodes ───────────────────────────────────────────── */
add_shortcode( 'cftg_bin_estimate',  'cftg_shortcode_bin_estimate' );
add_shortcode( 'cftg_scrap_metal',   'cftg_shortcode_scrap_metal' );
add_shortcode( 'cftg_vehicle_quote', 'cftg_shortcode_vehicle_quote' );

function cftg_shortcode_bin_estimate( $atts ) {
    ob_start();
    include CFTG_DIR . 'templates/form-bin-estimate.php';
    return ob_get_clean();
}
function cftg_shortcode_scrap_metal( $atts ) {
    ob_start();
    include CFTG_DIR . 'templates/form-scrap-metal.php';
    return ob_get_clean();
}
function cftg_shortcode_vehicle_quote( $atts ) {
    ob_start();
    include CFTG_DIR . 'templates/form-vehicle-quote.php';
    return ob_get_clean();
}

/* ── Front-end assets ─────────────────────────────────────── */
add_action( 'wp_enqueue_scripts', 'cftg_enqueue_frontend' );
function cftg_enqueue_frontend() {
    wp_enqueue_style( 'cftg-fa',    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css', [], null );
    wp_enqueue_style( 'cftg-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap', [], null );
    wp_enqueue_style( 'cftg-forms', CFTG_URL . 'assets/css/forms.css', [ 'cftg-fa' ], CFTG_VERSION );
    wp_enqueue_script( 'cftg-forms', CFTG_URL . 'assets/js/forms.js', [], CFTG_VERSION, true );
    wp_localize_script( 'cftg-forms', 'cftgData', [
        'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce( 'cftg_submit' ),
    ]);
}

/* ── Admin assets ─────────────────────────────────────────── */
add_action( 'admin_enqueue_scripts', 'cftg_enqueue_admin' );
function cftg_enqueue_admin( $hook ) {
    if ( strpos( $hook, 'cftg' ) === false ) return;
    wp_enqueue_media();
    wp_enqueue_style( 'cftg-admin', CFTG_URL . 'assets/css/admin.css', [], CFTG_VERSION );
    wp_enqueue_script( 'cftg-admin', CFTG_URL . 'assets/js/admin.js', [ 'jquery' ], CFTG_VERSION, true );
    wp_localize_script( 'cftg-admin', 'cftgAdmin', [
        'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce( 'cftg_admin' ),
    ]);
}

/* ── Admin menu ───────────────────────────────────────────── */
add_action( 'admin_menu', 'cftg_register_admin_menu' );
function cftg_register_admin_menu() {
    add_menu_page(
        'CFT Group Forms',
        'CFT Forms',
        'manage_options',
        'cftg-settings',
        'cftg_render_settings_page',
        'dashicons-feedback',
        30
    );
    add_submenu_page( 'cftg-settings', 'Settings',     'Settings',     'manage_options', 'cftg-settings',     'cftg_render_settings_page' );
    add_submenu_page( 'cftg-settings', 'Instructions', 'Instructions', 'manage_options', 'cftg-instructions', 'cftg_render_instructions_page' );
}

/* ── Plugin action links ──────────────────────────────────── */
add_filter( 'plugin_action_links_' . CFTG_BASENAME, 'cftg_action_links' );
function cftg_action_links( $links ) {
    $links[] = '<a href="' . admin_url( 'admin.php?page=cftg-settings' ) . '">Settings</a>';
    return $links;
}
