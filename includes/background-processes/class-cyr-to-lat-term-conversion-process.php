<?php
/**
 * Background old term slugs converting process.
 *
 * @package cyr-to-lat
 */

/**
 * Class Cyr_To_Lat_Term_Conversion_Process
 */
class Cyr_To_Lat_Term_Conversion_Process extends WP_Background_Process {

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
	protected $action = CYR_TO_LAT_TERM_CONVERSION_ACTION;

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
	 * Task. Updates single term.
	 *
	 * @param stdClass $term Queue item to iterate over.
	 *
	 * @return mixed
	 */
	protected function task( $term ) {
		global $wpdb;

		$sanitized_slug = $this->main->ctl_sanitize_title( $term->slug );

		if ( $sanitized_slug !== $term->slug ) {
			// phpcs:disable WordPress.DB.DirectDatabaseQuery
			$wpdb->update( $wpdb->terms, array( 'slug' => $sanitized_slug ), array( 'term_id' => $term->term_id ) );
			// phpcs:enable
		}

		$this->log( $term->slug . ' => ' . $sanitized_slug );

		return false;
	}

	/**
	 * Complete
	 */
	protected function complete() {
		parent::complete();

		$this->log( 'Term slugs conversion completed.' );
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
