<?php
/**
 * Old slugs converter.
 *
 * @package cyr-to-lat
 */

namespace Cyr_To_Lat;

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
	 * Regex of prohibited chars in slugs
	 * [^A-Za-z0-9[.apostrophe.][.underscore.][.period.][.hyphen.]]+
	 * So, allowed chars are A-Za-z0-9[.apostrophe.][.underscore.][.period.][.hyphen.]
	 * % is not allowed in the slug, but could present if slug is url_encoded
	 *
	 * @link https://dev.mysql.com/doc/refman/5.6/en/regexp.html
	 */
	const PROHIBITED_CHARS_REGEX = "[^A-Za-z0-9'_\.\-]+";

	/**
	 * Plugin main class.
	 *
	 * @var Main
	 */
	private $main;

	/**
	 * Plugin settings.
	 *
	 * @var Settings
	 */
	private $settings;

	/**
	 * Background process to convert posts.
	 *
	 * @var Post_Conversion_Process
	 */
	private $process_all_posts;

	/**
	 * Background process to convert terms.
	 *
	 * @var Term_Conversion_Process
	 */
	private $process_all_terms;

	/**
	 * Admin notices.
	 *
	 * @var Admin_Notices
	 */
	private $admin_notices;

	/**
	 * Option group.
	 *
	 * @var string
	 */
	private $option_group = '';

	/**
	 * Converter constructor.
	 *
	 * @param Main                    $main              Plugin main class.
	 * @param Settings                $settings          Plugin settings.
	 * @param Post_Conversion_Process $process_all_posts Post conversion process.
	 * @param Term_Conversion_Process $process_all_terms Term conversion process.
	 * @param Admin_Notices           $admin_notices     Admin notices.
	 */
	public function __construct( $main, $settings, $process_all_posts, $process_all_terms, $admin_notices ) {
		$this->main              = $main;
		$this->settings          = $settings;
		$this->option_group      = Settings::OPTION_GROUP;
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
	 */
	public function conversion_notices() {
		$posts_process_running = $this->process_all_posts->is_process_running();
		$terms_process_running = $this->process_all_terms->is_process_running();

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
	 */
	public function start_conversion() {
		if ( ! isset( $_POST['cyr2lat-convert'] ) ) {
			return;
		}
		check_admin_referer( $this->option_group . '-options' );
		$this->convert_existing_slugs();
	}

	/**
	 * Process handler.
	 */
	public function process_handler() {
		if ( ! isset( $_GET[ self::QUERY_ARG ] ) || ! isset( $_GET['_wpnonce'] ) ) {
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
	public function convert_existing_slugs( $args = [] ) {
		$this->convert_existing_post_slugs( $args );
		$this->convert_existing_term_slugs();
	}

	/**
	 * Convert existing post slugs.
	 *
	 * @param array $args Arguments for query.
	 */
	protected function convert_existing_post_slugs( $args = [] ) {
		global $wpdb;

		$post_types = get_post_types( [ 'public' => true ] );

		$post_types += [ 'nav_menu_item' => 'nav_menu_item' ];

		$defaults = [
			'post_type'   => apply_filters( 'ctl_post_types', $post_types ),
			'post_status' => [ 'publish', 'future', 'private' ],
		];

		$args = wp_parse_args( $args, $defaults );

		$regexp = $wpdb->prepare( '%s', self::PROHIBITED_CHARS_REGEX );

		$post_sql      =
			'post_status IN (' . $this->main->prepare_in( $args['post_status'] ) . ')' .
			' AND post_type IN (' . $this->main->prepare_in( $args['post_type'] ) . ')';
		$media_sql     = "post_status = 'inherit' AND post_type = 'attachment'";
		$all_posts_sql = '(' . $post_sql . ') OR (' . $media_sql . ')';

		$sql = "SELECT ID, post_name, post_type FROM $wpdb->posts WHERE post_name REGEXP($regexp) AND ($all_posts_sql)";

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

		if ( $posts ) {
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
					WHERE t.slug REGEXP(%s) AND tt.term_id = t.term_id",
				self::PROHIBITED_CHARS_REGEX
			)
		);

		if ( ! $terms ) {
			$this->admin_notices->add_notice(
				__( 'Cyr To Lat has not found existing term slugs for conversion.', 'cyr2lat' ),
				'notice notice-info is-dismissible'
			);

			return;
		}

		if ( $terms ) {
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
	}

	/**
	 * Log.
	 *
	 * @param string $message Message to log.
	 */
	protected function log( $message ) {
		if ( defined( 'WP_DEBUG_LOG' ) && constant( 'WP_DEBUG_LOG' ) ) {
			// @phpcs:disable WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( 'Cyr To Lat: ' . $message );
			// @phpcs:enable WordPress.PHP.DevelopmentFunctions.error_log_error_log
		}
	}
}
