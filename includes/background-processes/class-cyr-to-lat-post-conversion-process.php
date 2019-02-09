<?php
/**
 * Background old post slugs converting process.
 *
 * @package cyr-to-lat
 */

/**
 * Class Cyr_To_Lat_Post_Conversion_Process
 */
class Cyr_To_Lat_Post_Conversion_Process extends WP_Background_Process {

	/**
	 * Prefix.
	 *
	 * @var string
	 */
	protected $prefix = CYR_TO_LAT_PREFIX;

	/**
	 * Process action name.
	 *
	 * @var string
	 */
	protected $action = CYR_TO_LAT_POST_CONVERSION_ACTION;

	/**
	 * Plugin main class.
	 *
	 * @var Cyr_To_Lat_Main
	 */
	private $main;

	/**
	 * Cyr_To_Lat_Post_Conversion_Process constructor.
	 *
	 * @param Cyr_To_Lat_Main $main Plugin main class.
	 */
	public function __construct( $main ) {
		$this->main = $main;

		parent::__construct();
	}

	/**
	 * Task. Updates single post.
	 *
	 * @param stdClass $post Queue item to iterate over.
	 *
	 * @return mixed
	 */
	protected function task( $post ) {
		global $wpdb;

		$sanitized_name = $this->main->ctl_sanitize_title( $post->post_name );

		if ( $sanitized_name !== $post->post_name ) {
			add_post_meta( $post->ID, '_wp_old_slug', $post->post_name );
			// phpcs:disable WordPress.DB.DirectDatabaseQuery
			$wpdb->update( $wpdb->posts, array( 'post_name' => $sanitized_name ), array( 'ID' => $post->ID ) );
			// phpcs:enable
		}

		$this->log( $post->post_name . ' => ' . $sanitized_name );

		return false;
	}

	/**
	 * Complete
	 */
	protected function complete() {
		parent::complete();

		$this->log( 'Post slugs conversion completed.' );
	}

	/**
	 * Log.
	 *
	 * @param string $message Message to log.
	 */
	public function log( $message ) {
		if ( WP_DEBUG_LOG ) {
			error_log( $message );
		}
	}
}
