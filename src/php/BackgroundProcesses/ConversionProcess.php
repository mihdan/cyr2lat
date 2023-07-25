<?php
/**
 * Background old slugs converting process
 *
 * @package cyr-to-lat
 */

namespace CyrToLat\BackgroundProcesses;

use CyrToLat\Main;
use CyrToLat\WP_Background_Processing\WP_Background_Process;

/**
 * Class ConversionProcess
 */
class ConversionProcess extends WP_Background_Process {

	/**
	 * Prefix
	 *
	 * @var string
	 */
	protected $prefix;

	/**
	 * Plugin main class
	 *
	 * @var Main
	 */
	protected $main;

	/**
	 * ConversionProcess constructor
	 *
	 * @param Main $main Plugin main class.
	 */
	public function __construct( Main $main ) {
		$this->main   = $main;
		$this->prefix = constant( 'CYR_TO_LAT_PREFIX' );

		parent::__construct();
	}

	/**
	 * Task. Updates single post or term.
	 *
	 * @param mixed $item Queue item to iterate over.
	 *
	 * @return boolean
	 * @noinspection PhpMissingReturnTypeInspection
	 * @noinspection ReturnTypeCanBeDeclaredInspection
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
	public function is_process_completed(): bool {
		if ( get_site_transient( $this->identifier . '_process_completed' ) ) {
			// Process is marked as completed.
			// Delete relevant site transient.
			delete_site_transient( $this->identifier . '_process_completed' );

			return true;
		}

		return false;
	}

	/**
	 * Write log
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
