<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class CFTG_Updater {

  private $repo;
  private $slug;
  private $file;
  private $version;

  public function __construct( $repo, $plugin_file, $version ) {
    $this->repo    = $repo;          // e.g. "adel/cftgroup-forms"
    $this->file    = $plugin_file;   // absolute path to main plugin file
    $this->slug    = plugin_basename( $plugin_file );
    $this->version = $version;

    add_filter( 'pre_set_site_transient_update_plugins', [ $this, 'check_update' ] );
    add_filter( 'plugins_api',                           [ $this, 'plugin_info'  ], 20, 3 );
  }

  /* ── Check GitHub for a newer release ── */
  public function check_update( $transient ) {
    if ( empty( $transient->checked ) ) return $transient;

    $release = $this->get_release();
    if ( ! $release ) return $transient;

    $latest = ltrim( $release->tag_name, 'v' );
    if ( version_compare( $this->version, $latest, '<' ) ) {
      $package = $this->get_zip_url( $release );
      if ( $package ) {
        $transient->response[ $this->slug ] = (object) [
          'slug'        => dirname( $this->slug ),
          'plugin'      => $this->slug,
          'new_version' => $latest,
          'url'         => "https://github.com/{$this->repo}",
          'package'     => $package,
        ];
      }
    }
    return $transient;
  }

  /* ── Info shown in the WP "View details" popup ── */
  public function plugin_info( $result, $action, $args ) {
    if ( $action !== 'plugin_information' ) return $result;
    if ( $args->slug !== dirname( $this->slug ) ) return $result;

    $release = $this->get_release();
    if ( ! $release ) return $result;

    return (object) [
      'name'          => 'CFT Group Forms',
      'slug'          => dirname( $this->slug ),
      'version'       => ltrim( $release->tag_name, 'v' ),
      'author'        => 'CFT Group',
      'download_link' => $this->get_zip_url( $release ),
      'sections'      => [ 'changelog' => nl2br( esc_html( $release->body ?? 'Improvements and bug fixes.' ) ) ],
    ];
  }

  /* ── Fetch latest release from GitHub API ── */
  private function get_release() {
    $transient_key = 'cftg_gh_release_' . md5( $this->repo );
    $cached = get_transient( $transient_key );
    if ( $cached !== false ) return $cached;

    $response = wp_remote_get(
      "https://api.github.com/repos/{$this->repo}/releases/latest",
      [ 'headers' => [ 'User-Agent' => 'WordPress/CFTG-Updater' ], 'timeout' => 10 ]
    );

    if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
      return null;
    }

    $release = json_decode( wp_remote_retrieve_body( $response ) );
    set_transient( $transient_key, $release, HOUR_IN_SECONDS * 6 );
    return $release;
  }

  /* ── Get download URL from release assets ── */
  private function get_zip_url( $release ) {
    // Prefer a named asset (cftgroup-forms.zip) over the raw zipball
    if ( ! empty( $release->assets ) ) {
      foreach ( $release->assets as $asset ) {
        if ( str_ends_with( $asset->name, '.zip' ) ) {
          return $asset->browser_download_url;
        }
      }
    }
    return null;
  }
}
