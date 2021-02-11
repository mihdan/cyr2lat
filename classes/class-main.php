<?php
/**
 * Main class of the plugin.
 *
 * @package cyr-to-lat
 */

namespace Cyr_To_Lat;

use wpdb;
use Exception;
use Cyr_To_Lat\Symfony\Polyfill\Mbstring\Mbstring;

/**
 * Class Main
 */
class Main {

	/**
	 * Plugin settings.
	 *
	 * @var Settings
	 */
	protected $settings;

	/**
	 * Process posts instance.
	 *
	 * @var Post_Conversion_Process
	 */
	protected $process_all_posts;

	/**
	 * Process terms instance.
	 *
	 * @var Term_Conversion_Process
	 */
	protected $process_all_terms;

	/**
	 * Admin Notices instance.
	 *
	 * @var Admin_Notices
	 */
	protected $admin_notices;

	/**
	 * Converter instance.
	 *
	 * @var Converter
	 */
	protected $converter;

	/**
	 * WP_CLI instance.
	 *
	 * @var WP_CLI
	 */
	protected $cli;

	/**
	 * ACF instance.
	 *
	 * @var ACF
	 */
	protected $acf;

	/**
	 * Polylang locale.
	 *
	 * @var string
	 */
	private $pll_locale = false;

	/**
	 * Main constructor.
	 */
	public function __construct() {
		$this->settings          = new Settings();
		$this->process_all_posts = new Post_Conversion_Process( $this );
		$this->process_all_terms = new Term_Conversion_Process( $this );
		$this->admin_notices     = new Admin_Notices();
		$this->converter         = new Converter(
			$this,
			$this->process_all_posts,
			$this->process_all_terms,
			$this->admin_notices
		);

		if ( defined( 'WP_CLI' ) && constant( 'WP_CLI' ) ) {
			$this->cli = new WP_CLI( $this->converter );
		}

		$this->acf = new ACF( $this->settings );

		$this->init();
	}

	/**
	 * Init class.
	 *
	 * @noinspection PhpUndefinedClassInspection
	 */
	public function init() {
		if ( defined( 'WP_CLI' ) && constant( 'WP_CLI' ) ) {
			try {
				/**
				 * Method WP_CLI::add_command() accepts class as callable.
				 *
				 * @noinspection PhpParamsInspection
				 */
				\WP_CLI::add_command( 'cyr2lat', $this->cli );
			} catch ( Exception $e ) {
				return;
			}
		}

		$this->init_hooks();
	}

	/**
	 * Init class hooks.
	 */
	public function init_hooks() {
		add_filter( 'sanitize_title', [ $this, 'sanitize_title' ], 9, 3 );
		add_filter( 'sanitize_file_name', [ $this, 'sanitize_filename' ], 10, 2 );
		add_filter( 'wp_insert_post_data', [ $this, 'sanitize_post_name' ], 10, 2 );

		if ( class_exists( 'Polylang' ) ) {
			add_filter( 'locale', [ $this, 'pll_locale_filter' ] );
		}
	}

	/**
	 * Sanitize title.
	 *
	 * @param string $title     Sanitized title.
	 * @param string $raw_title The title prior to sanitization.
	 * @param string $context   The context for which the title is being sanitized.
	 *
	 * @return string
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function sanitize_title( $title, $raw_title = '', $context = '' ) {
		global $wpdb;

		if ( ! $title ) {
			return $title;
		}

		// Fixed bug with `_wp_old_slug` redirect.
		if ( 'query' === $context ) {
			return $title;
		}

		$title = urldecode( $title );
		$pre   = apply_filters( 'ctl_pre_sanitize_title', false, $title );

		if ( false !== $pre ) {
			return $pre;
		}

		$is_term = false;
		// phpcs:disable WordPress.PHP.DevelopmentFunctions.error_log_debug_backtrace
		$backtrace = debug_backtrace( ~DEBUG_BACKTRACE_PROVIDE_OBJECT | DEBUG_BACKTRACE_IGNORE_ARGS );
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
			$title = $this->is_wc_attribute_taxonomy( $title ) ? $title : $this->transliterate( $title );
		}

		return $title;
	}

	/**
	 * Check if title is an attribute taxonomy.
	 *
	 * @param string $title Title.
	 *
	 * @return bool
	 */
	protected function is_wc_attribute_taxonomy( $title ) {
		if ( ! function_exists( 'wc_get_attribute_taxonomies' ) ) {
			return false;
		}

		$attribute_taxonomies = wc_get_attribute_taxonomies();

		foreach ( $attribute_taxonomies as $attribute_taxonomy ) {
			if ( $title === $attribute_taxonomy->attribute_name ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Sanitize filename.
	 *
	 * @param string $filename     Sanitized filename.
	 * @param string $filename_raw The filename prior to sanitization.
	 *
	 * @return string
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function sanitize_filename( $filename, $filename_raw ) {
		$pre = apply_filters( 'ctl_pre_sanitize_filename', false, $filename );

		if ( false !== $pre ) {
			return $pre;
		}

		if ( seems_utf8( $filename ) ) {
			$filename = mb_strtolower( $filename );
		}

		return $this->transliterate( $filename );
	}

	/**
	 * Fix string encoding on MacOS.
	 *
	 * @param string $string String.
	 *
	 * @return string
	 */
	private function fix_mac_string( $string ) {
		$table     = $this->get_filtered_table();
		$fix_table = Conversion_Tables::get_fix_table_for_mac();

		$fix = [];
		foreach ( $fix_table as $key => $value ) {
			if ( isset( $table[ $key ] ) ) {
				$fix[ $value ] = $table[ $key ];
			}
		}

		return strtr( $string, $fix );
	}

	/**
	 * Split Chinese string by hyphens.
	 *
	 * @param string $string String.
	 * @param array  $table  Conversion table.
	 *
	 * @return string
	 */
	protected function split_chinese_string( $string, $table ) {
		if ( ! $this->settings->is_chinese_locale() || mb_strlen( $string ) < 4 ) {
			return $string;
		}

		$chars  = Mbstring::mb_str_split( $string );
		$string = '';

		foreach ( $chars as $char ) {
			if ( isset( $table[ $char ] ) ) {
				$string .= '-' . $char . '-';
			} else {
				$string .= $char;
			}
		}

		return $string;
	}

	/**
	 * Get transliteration table.
	 *
	 * @return array
	 */
	private function get_filtered_table() {
		return (array) apply_filters( 'ctl_table', $this->settings->get_table() );
	}

	/**
	 * Transliterate string using a table.
	 *
	 * @param string $string String.
	 *
	 * @return string
	 */
	public function transliterate( $string ) {
		$table = $this->get_filtered_table();

		$string = $this->fix_mac_string( $string );
		$string = $this->split_chinese_string( $string, $table );
		$string = strtr( $string, $table );

		if ( function_exists( 'iconv' ) ) {
			$new_string = iconv( 'UTF-8', 'UTF-8//TRANSLIT//IGNORE', $string );
			return $new_string ?: $string;
		}

		return $string;
	}

	/**
	 * Check if Classic Editor plugin is active.
	 *
	 * @link https://kagg.eu/how-to-catch-gutenberg/
	 *
	 * @return bool
	 * @noinspection PhpIncludeInspection
	 */
	private function is_classic_editor_plugin_active() {
		// @codeCoverageIgnoreStart
		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		// @codeCoverageIgnoreEnd

		return is_plugin_active( 'classic-editor/classic-editor.php' );
	}

	/**
	 * Check if Block Editor is active.
	 * Must only be used after plugins_loaded action is fired.
	 *
	 * @link https://kagg.eu/how-to-catch-gutenberg/
	 *
	 * @return bool
	 */
	private function is_gutenberg_editor_active() {

		// Gutenberg plugin is installed and activated.
		$gutenberg = ! ( false === has_filter( 'replace_editor', 'gutenberg_init' ) );

		// Block editor since 5.0.
		$block_editor = version_compare( $GLOBALS['wp_version'], '5.0-beta', '>' );

		if ( ! $gutenberg && ! $block_editor ) {
			return false;
		}

		if ( $this->is_classic_editor_plugin_active() ) {
			$editor_option       = get_option( 'classic-editor-replace' );
			$block_editor_active = [ 'no-replace', 'block' ];

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
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function sanitize_post_name( $data, $postarr = [] ) {
		global $current_screen;

		if ( ! $this->is_gutenberg_editor_active() ) {
			return $data;
		}

		// Run code only on post edit screen.
		if ( ! ( $current_screen && 'post' === $current_screen->base ) ) {
			return $data;
		}

		if (
			! $data['post_name'] && $data['post_title'] &&
			! in_array( $data['post_status'], [ 'auto-draft', 'revision' ], true )
		) {
			$data['post_name'] = sanitize_title( $data['post_title'] );
		}

		return $data;
	}

	/**
	 * Locale filter for Polylang.
	 *
	 * @param string $locale Locale.
	 *
	 * @return string
	 */
	public function pll_locale_filter( $locale ) {
		if ( $this->pll_locale ) {
			return $this->pll_locale;
		}

		$rest_locale = $this->pll_locale_filter_with_rest();
		if ( false === $rest_locale ) {
			return $locale;
		}
		if ( $rest_locale ) {
			$this->pll_locale = $rest_locale;

			return $this->pll_locale;
		}

		if ( ! is_admin() ) {
			return $locale;
		}

		$pll_get_post_language = $this->pll_locale_filter_with_classic_editor();
		if ( $pll_get_post_language ) {
			$this->pll_locale = $pll_get_post_language;

			return $this->pll_locale;
		}

		$pll_get_term_language = $this->pll_locale_filter_with_term();
		if ( $pll_get_term_language ) {
			$this->pll_locale = $pll_get_term_language;

			return $this->pll_locale;
		}

		return $locale;
	}

	/**
	 * Locale filter for Polylang with REST request.
	 *
	 * @return false|null|string
	 */
	private function pll_locale_filter_with_rest() {
		if ( ! defined( 'REST_REQUEST' ) || ! constant( 'REST_REQUEST' ) ) {
			return null;
		}

		$rest_server = rest_get_server();
		$data        = json_decode( $rest_server::get_raw_data(), false );
		if ( isset( $data->lang ) ) {
			return $data->lang;
		}

		return false;
	}

	/**
	 * Locale filter for Polylang with classic editor.
	 *
	 * @return bool|string
	 */
	private function pll_locale_filter_with_classic_editor() {
		if ( ! function_exists( 'pll_get_post_language' ) ) {
			return false;
		}

		$pll_get_post_language = false;

		// phpcs:disable WordPress.Security.NonceVerification.Missing
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( isset( $_POST['post_ID'] ) ) {
			$pll_get_post_language = pll_get_post_language(
				(int) filter_input( INPUT_POST, 'post_ID', FILTER_SANITIZE_STRING )
			);
		}
		if ( isset( $_POST['pll_post_id'] ) ) {
			$pll_get_post_language = pll_get_post_language(
				(int) filter_input( INPUT_POST, 'pll_post_id', FILTER_SANITIZE_STRING )
			);
		}
		if ( isset( $_GET['post'] ) ) {
			$pll_get_post_language = pll_get_post_language(
				(int) filter_input( INPUT_GET, 'post', FILTER_SANITIZE_STRING )
			);
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		return $pll_get_post_language;
	}

	/**
	 * Locale filter for Polylang with term.
	 *
	 * @return false|string
	 */
	private function pll_locale_filter_with_term() {
		if ( ! function_exists( 'PLL' ) ) {
			return false;
		}

		$pll_get_term_language = false;

		// phpcs:disable WordPress.Security.NonceVerification.Missing
		if ( isset( $_POST['term_lang_choice'] ) ) {
			$pll_get_language = PLL()->model->get_language(
				filter_input( INPUT_POST, 'term_lang_choice', FILTER_SANITIZE_STRING )
			);

			if ( $pll_get_language ) {
				$pll_get_term_language = $pll_get_language->slug;
			}
		}
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		return $pll_get_term_language;
	}

	/**
	 * Changes array of items into string of items, separated by comma and sql-escaped
	 *
	 * @see https://coderwall.com/p/zepnaw
	 * @global wpdb       $wpdb
	 *
	 * @param mixed|array $items  item(s) to be joined into string.
	 * @param string      $format %s or %d.
	 *
	 * @return string Items separated by comma and sql-escaped
	 */
	public function prepare_in( $items, $format = '%s' ) {
		global $wpdb;

		$items    = (array) $items;
		$how_many = count( $items );
		if ( $how_many > 0 ) {
			$placeholders    = array_fill( 0, $how_many, $format );
			$prepared_format = implode( ',', $placeholders );
			$prepared_in     = $wpdb->prepare( $prepared_format, $items ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		} else {
			$prepared_in = '';
		}

		return $prepared_in;
	}
}
