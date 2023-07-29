<?php
/**
 * Old slugs converter.
 *
 * @package cyr-to-lat
 */

namespace CyrToLat;

use CyrToLat\BackgroundProcesses\PostConversionProcess;
use CyrToLat\BackgroundProcesses\TermConversionProcess;
use CyrToLat\Settings\Settings;

/**
 * Class Converter
 *
 * @class Converter
 */
class Converter {

	/**
	 * Query arg in url to start conversion.
	 */
	const QUERY_ARG = 'cyr-to-lat-convert';

	/**
	 * Regex of allowed chars in lower-cased slugs.
	 *
	 * Allowed chars are a-z, 0-9, [.apostrophe.], [.hyphen.], [.period.], [.underscore.],
	 * or any url-encoded character in the range \x20-x7F.
	 *
	 * @link https://dev.mysql.com/doc/refman/5.6/en/regexp.html
	 */
	const ALLOWED_CHARS_REGEX = "^([a-z0-9\'-._]|%[2-7][0-F])+$";

	/**
	 * Plugin main class.
	 *
	 * @var Main
	 */
	protected $main;

	/**
	 * Plugin settings class.
	 *
	 * @var Settings
	 */
	protected $settings;

	/**
	 * Background process to convert posts.
	 *
	 * @var PostConversionProcess
	 */
	protected $process_all_posts;

	/**
	 * Background process to convert terms.
	 *
	 * @var TermConversionProcess
	 */
	protected $process_all_terms;

	/**
	 * Admin notices.
	 *
	 * @var AdminNotices
	 */
	protected $admin_notices;

	/**
	 * Converter constructor.
	 *
	 * @param Main                  $main              Plugin main class.
	 * @param Settings              $settings          Plugin settings.
	 * @param PostConversionProcess $process_all_posts Post conversion process.
	 * @param TermConversionProcess $process_all_terms Term conversion process.
	 * @param AdminNotices          $admin_notices     Admin notices.
	 */
	public function __construct( Main $main, Settings $settings, PostConversionProcess $process_all_posts, TermConversionProcess $process_all_terms, AdminNotices $admin_notices ) {
		$this->main              = $main;
		$this->settings          = $settings;
		$this->process_all_posts = $process_all_posts;
		$this->process_all_terms = $process_all_terms;
		$this->admin_notices     = $admin_notices;

		$this->init_hooks();
	}

	/**
	 * Init class hooks.
	 */
	public function init_hooks() {
		add_action( 'admin_init', [ $this, 'process_handler' ] );
		add_action( 'admin_init', [ $this, 'conversion_notices' ] );
	}

	/**
	 * Show conversion notices.
	 *
	 * @return void
	 */
	public function conversion_notices() {
		$posts_process_running = $this->process_all_posts->is_processing();
		$terms_process_running = $this->process_all_terms->is_processing();

		if ( ! $posts_process_running && ! $terms_process_running ) {
			add_action( 'admin_init', [ $this, 'start_conversion' ], 20 );
		}

		if ( $posts_process_running ) {
			$this->admin_notices->add_notice(
				__( 'Cyr To Lat converts existing post slugs in the background process.', 'cyr2lat' ),
				'notice notice-info is-dismissible'
			);
		}

		if ( $terms_process_running ) {
			$this->admin_notices->add_notice(
				__( 'Cyr To Lat converts existing term slugs in the background process.', 'cyr2lat' ),
				'notice notice-info is-dismissible'
			);
		}

		if ( $this->process_all_posts->is_process_completed() ) {
			$this->admin_notices->add_notice(
				__( 'Cyr To Lat completed conversion of existing post slugs.', 'cyr2lat' ),
				'notice notice-success is-dismissible'
			);
		}

		if ( $this->process_all_terms->is_process_completed() ) {
			$this->admin_notices->add_notice(
				__( 'Cyr To Lat completed conversion of existing term slugs.', 'cyr2lat' ),
				'notice notice-success is-dismissible'
			);
		}
	}

	/**
	 * Check if we have to start conversion and start it.
	 *
	 * @return void
	 */
	public function start_conversion() {
		if ( ! isset( $_POST['ctl-convert'] ) ) {
			return;
		}
		check_admin_referer( \CyrToLat\Settings\Converter::NONCE );
		$this->convert_existing_slugs();
	}

	/**
	 * Process handler.
	 *
	 * @return void
	 */
	public function process_handler() {
		if ( ! isset( $_GET[ self::QUERY_ARG ], $_GET['_wpnonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), self::QUERY_ARG ) ) {
			return;
		}

		$this->convert_existing_slugs();
	}

	/**
	 * Convert Existing Slugs.
	 *
	 * @param array $args Arguments for query.
	 */
	public function convert_existing_slugs( array $args = [] ) {
		$this->convert_existing_post_slugs( $args );
		$this->convert_existing_term_slugs();
	}

	/**
	 * Convert existing post slugs.
	 *
	 * @param array $args Arguments for query.
	 */
	protected function convert_existing_post_slugs( array $args = [] ) {
		global $wpdb;

		$post_types    = array_intersect(
			\CyrToLat\Settings\Converter::get_convertible_post_types(),
			array_filter( (array) $this->settings->get( 'background_post_types' ) )
		);
		$post_statuses = array_filter( (array) $this->settings->get( 'background_post_statuses' ) );

		$defaults = [
			'post_type'   => array_filter( (array) apply_filters( 'ctl_post_types', $post_types ) ),
			'post_status' => $post_statuses,
		];

		$parsed_args = wp_parse_args( $args, $defaults );

		$regexp = $wpdb->prepare( '%s', self::ALLOWED_CHARS_REGEX );

		$post_status_in = $this->main->prepare_in( $parsed_args['post_status'] );
		$post_status_in = $post_status_in ? 'post_status IN (' . $post_status_in . ')' : '';
		$post_type_in   = $this->main->prepare_in( $parsed_args['post_type'] );
		$post_type_in   = $post_type_in ? 'post_type IN (' . $post_type_in . ')' : '';
		$and            = $post_status_in && $post_type_in ? ' AND ' : '';
		$post_sql       = $post_status_in . $and . $post_type_in;
		$post_sql       = $post_sql ? 'AND (' . $post_sql . ')' : '';

		if ( in_array( 'attachment', $parsed_args['post_type'], true ) ) {
			$media_sql = "post_status = 'inherit' AND post_type = 'attachment'";
			$post_sql  = $post_sql ? $post_sql . ' OR (' . $media_sql . ')' : 'AND (' . $media_sql . ')';
		}

		$sql = "SELECT ID, post_name, post_type FROM $wpdb->posts WHERE LOWER(post_name) NOT REGEXP($regexp) $post_sql";

		// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$posts = $wpdb->get_results( $sql );
		// phpcs:enable WordPress.DB.PreparedSQL.NotPrepared

		if ( ! $posts ) {
			$this->admin_notices->add_notice(
				__( 'Cyr To Lat has not found existing post slugs for conversion.', 'cyr2lat' ),
				'notice notice-info is-dismissible'
			);

			return;
		}

		foreach ( (array) $posts as $post ) {
			$this->process_all_posts->push_to_queue( $post );
		}

		$this->log( __( 'Post slugs conversion started.', 'cyr2lat' ) );
		$this->admin_notices->add_notice(
			__( 'Cyr To Lat started conversion of existing post slugs.', 'cyr2lat' ),
			'notice notice-info is-dismissible'
		);

		$this->process_all_posts->save()->dispatch();
	}

	/**
	 * Convert existing term slugs.
	 */
	protected function convert_existing_term_slugs() {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$terms = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT t.term_id, slug, tt.taxonomy, tt.term_taxonomy_id FROM $wpdb->terms t, $wpdb->term_taxonomy tt
					WHERE LOWER(t.slug) NOT REGEXP(%s) AND tt.taxonomy NOT REGEXP ('^pa_.*$') AND tt.term_id = t.term_id",
				self::ALLOWED_CHARS_REGEX
			)
		);

		if ( ! $terms ) {
			$this->admin_notices->add_notice(
				__( 'Cyr To Lat has not found existing term slugs for conversion.', 'cyr2lat' ),
				'notice notice-info is-dismissible'
			);

			return;
		}

		foreach ( (array) $terms as $term ) {
			$this->process_all_terms->push_to_queue( $term );
		}

		$this->log( __( 'Term slugs conversion started.', 'cyr2lat' ) );
		$this->admin_notices->add_notice(
			__( 'Cyr To Lat started conversion of existing term slugs.', 'cyr2lat' ),
			'notice notice-info is-dismissible'
		);

		$this->process_all_terms->save()->dispatch();
	}

	/**
	 * Log.
	 *
	 * @param string $message Message to log.
	 *
	 * @noinspection ForgottenDebugOutputInspection
	 */
	protected function log( string $message ) {
		if ( defined( 'WP_DEBUG_LOG' ) && constant( 'WP_DEBUG_LOG' ) ) {
			// @phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( 'Cyr To Lat: ' . $message );
		}
	}
}
