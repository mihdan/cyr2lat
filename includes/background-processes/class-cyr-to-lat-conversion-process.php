<?php
/**
 * Background old slugs converting process
 *
 * @package cyr-to-lat
 */

/**
 * Class Cyr_To_Lat_Conversion_Process
 */
class Cyr_To_Lat_Conversion_Process extends WP_Background_Process {

	/**
	 * Prefix
	 *
	 * @var string
	 */
	protected $prefix = CYR_TO_LAT_PREFIX;

	/**
	 * Plugin main class
	 *
	 * @var Cyr_To_Lat_Main
	 */
	protected $main;

	/**
	 * Cyr_To_Lat_Conversion_Process constructor
	 *
	 * @param Cyr_To_Lat_Main $main Plugin main class.
	 */
	public function __construct( $main ) {
		$this->main = $main;

		parent::__construct();
	}

	/**
	 * Task. Updates single post or term.
	 *
	 * @param mixed $item Queue item to iterate over.
	 *
	 * @return mixed
	 */
	protected function task( $item ) {
		return false;
	}

	/**
	 * Complete
	 */
	protected function complete() {
		parent::complete();

		set_site_transient( $this->identifier . '_process_completed', microtime() );
	}

	/**
	 * Check if process is completed.
	 * Delete relevant transient.
	 */
	public function is_process_completed() {
		if ( get_site_transient( $this->identifier . '_process_completed' ) ) {
			// Process is marked as completed.
			// Delete relevant site transient.
			delete_site_transient( $this->identifier . '_process_completed' );

			return true;
		}

		return false;
	}

	/**
	 * Is process running
	 *
	 * Check whether the current process is already running
	 * in a background process.
	 */
	public function is_process_running() {
		$is_process_running = parent::is_process_running();

		return $is_process_running;
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
