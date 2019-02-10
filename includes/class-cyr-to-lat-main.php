<?php
/**
 * Main class of the plugin.
 *
 * @package cyr-to-lat
 */

/**
 * Class Cyr_To_Lat_Main
 */
class Cyr_To_Lat_Main {

	/**
	 * Plugin settings.
	 *
	 * @var Cyr_To_Lat_Settings
	 */
	private $settings;

	/**
	 * WP-CLI
	 *
	 * @var Cyr_To_Lat_WP_CLI
	 */
	private $cli;

	/**
	 * Cyr_To_Lat constructor.
	 */
	public function __construct() {
		$this->init();
		$this->init_hooks();
	}

	/**
	 * Init class.
	 */
	public function init() {
		$this->settings = new Cyr_To_Lat_Settings();
		$converter      = new Cyr_To_Lat_Converter( $this, $this->settings );

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			$this->cli = new Cyr_To_Lat_WP_CLI( $converter );
			try {
				/**
				 * Method WP_CLI::add_command() accepts class as callable.
				 *
				 * @noinspection PhpParamsInspection
				 */
				WP_CLI::add_command( 'cyr2lat', $this->cli );
			} catch ( Exception $e ) {
				return;
			}
		}
	}

	/**
	 * Init class hooks.
	 */
	public function init_hooks() {
		add_filter( 'sanitize_title', array( $this, 'ctl_sanitize_title' ), 9, 3 );
		add_filter( 'sanitize_file_name', array( $this, 'ctl_sanitize_title' ), 10, 2 );
		add_filter( 'wp_insert_post_data', array( $this, 'ctl_sanitize_post_name' ), 10, 2 );
	}

	/**
	 * Sanitize title.
	 *
	 * @param string $title     Sanitized title.
	 * @param string $raw_title The title prior to sanitization.
	 * @param string $context   The context for which the title is being sanitized.
	 *
	 * @return string
	 */
	public function ctl_sanitize_title( $title, $raw_title = '', $context = '' ) {
		global $wpdb;

		// Fixed bug with `_wp_old_slug` redirect.
		if ( 'query' === $context ) {
			return $title;
		}

		$title = urldecode( $title );
		$pre   = apply_filters( 'ctl_pre_sanitize_title', false, $title );

		if ( false !== $pre ) {
			return $pre;
		}

		// Locales list - https://make.wordpress.org/polyglots/teams/.
		$locale     = get_locale();
		$iso9_table = $this->settings->get_option( $locale );
		$iso9_table = ! empty( $iso9_table ) ? $iso9_table : Cyr_To_Lat_Conversion_Tables::get( $locale );

		$is_term = false;
		// phpcs:disable WordPress.PHP.DevelopmentFunctions.error_log_debug_backtrace
		$backtrace = debug_backtrace();
		// phpcs:enable
		foreach ( $backtrace as $backtrace_entry ) {
			if ( 'wp_insert_term' === $backtrace_entry['function'] ) {
				$is_term = true;
				break;
			}
		}

		// phpcs:disable WordPress.DB.DirectDatabaseQuery
		$term = $is_term ? $wpdb->get_var( $wpdb->prepare( "SELECT slug FROM $wpdb->terms WHERE name = %s", $title ) ) : '';
		// phpcs:enable

		if ( ! empty( $term ) ) {
			$title = $term;
		} else {
			$title = strtr( $title, apply_filters( 'ctl_table', $iso9_table ) );

			if ( function_exists( 'iconv' ) ) {
				$title = iconv( 'UTF-8', 'UTF-8//TRANSLIT//IGNORE', $title );
			}

			$title = preg_replace( "/[^A-Za-z0-9'_\-\.]/", '-', $title );
			$title = preg_replace( '/\-+/', '-', $title );
			$title = trim( $title, '-' );
		}

		return $title;
	}

	/**
	 * Check if Classic Editor plugin is active.
	 *
	 * @link https://kagg.eu/how-to-catch-gutenberg/
	 *
	 * @return bool
	 */
	private function ctl_is_classic_editor_plugin_active() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if ( is_plugin_active( 'classic-editor/classic-editor.php' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if Block Editor is active.
	 * Must only be used after plugins_loaded action is fired.
	 *
	 * @link https://kagg.eu/how-to-catch-gutenberg/
	 *
	 * @return bool
	 */
	private function ctl_is_gutenberg_editor_active() {

		// Gutenberg plugin is installed and activated.
		$gutenberg = ! ( false === has_filter( 'replace_editor', 'gutenberg_init' ) );

		// Block editor since 5.0.
		$block_editor = version_compare( $GLOBALS['wp_version'], '5.0-beta', '>' );

		if ( ! $gutenberg && ! $block_editor ) {
			return false;
		}

		if ( $this->ctl_is_classic_editor_plugin_active() ) {
			$editor_option       = get_option( 'classic-editor-replace' );
			$block_editor_active = array( 'no-replace', 'block' );

			return in_array( $editor_option, $block_editor_active, true );
		}

		return true;
	}

	/**
	 * Gutenberg support
	 *
	 * @param array $data    An array of slashed post data.
	 * @param array $postarr An array of sanitized, but otherwise unmodified post data.
	 *
	 * @return mixed
	 */
	public function ctl_sanitize_post_name( $data, $postarr = array() ) {

		if ( ! $this->ctl_is_gutenberg_editor_active() ) {
			return $data;
		}

		if (
			! $data['post_name'] && $data['post_title'] &&
			! in_array( $data['post_status'], array( 'auto-draft', 'revision' ), true )
		) {
			$data['post_name'] = sanitize_title( $data['post_title'] );
		}

		return $data;
	}
}

// eof.
