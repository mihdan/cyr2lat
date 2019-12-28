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
	public function __construct(
		$main, $settings, $process_all_posts = null, $process_all_terms = null, $admin_notices = null
	) {
		$this->main         = $main;
		$this->settings     = $settings;
		$this->option_group = Settings::OPTION_GROUP;

		$this->process_all_posts = $process_all_posts;
		if ( ! $this->process_all_posts ) {
			$this->process_all_posts = new Post_Conversion_Process( $main );
		}

		$this->process_all_terms = $process_all_terms;
		if ( ! $this->process_all_terms ) {
			$this->process_all_terms = new Term_Conversion_Process( $main );
		}

		$this->admin_notices = $admin_notices;
		if ( ! $this->admin_notices ) {
			$this->admin_notices = new Admin_Notices();
		}

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
		global $wpdb;

		$regexp = Main::PROHIBITED_CHARS_REGEX . '+';

		$defaults = [
			'post_type'   => get_post_types(),
			'post_status' => [ 'publish', 'future', 'private' ],
		];

		$args = wp_parse_args( $args, $defaults );

		// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$posts = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID, post_name FROM $wpdb->posts WHERE post_name REGEXP(%s) AND post_status IN (" .
				$this->main->ctl_prepare_in( $args['post_status'] ) . ') AND post_type IN (' .
				$this->main->ctl_prepare_in( $args['post_type'] ) . ')',
				$regexp
			)
		);
		// phpcs:enable WordPress.DB.PreparedSQL.NotPrepared

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
		} else {
			$this->admin_notices->add_notice(
				__( 'Cyr To Lat has not found existing post slugs for conversion.', 'cyr2lat' ),
				'notice notice-info is-dismissible'
			);
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$terms = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT t.term_id, slug, tt.taxonomy, tt.term_taxonomy_id FROM $wpdb->terms t, $wpdb->term_taxonomy tt
					WHERE t.slug REGEXP(%s) AND tt.term_id = t.term_id",
				$regexp
			)
		);

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
		} else {
			$this->admin_notices->add_notice(
				__( 'Cyr To Lat has not found existing term slugs for conversion.', 'cyr2lat' ),
				'notice notice-info is-dismissible'
			);
		}
	}

	/**
	 * Log
	 *
	 * @param string $message Message to log.
	 */
	protected function log( $message ) {
		if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			// @phpcs:disable WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( 'Cyr To Lat: ' . $message );
			// @phpcs:enable WordPress.PHP.DevelopmentFunctions.error_log_error_log
		}
	}
}
