<?php
/**
 * Background old term slugs converting process
 *
 * @package cyr-to-lat
 */

/**
 * Class Cyr_To_Lat_Term_Conversion_Process
 */
class Cyr_To_Lat_Term_Conversion_Process extends Cyr_To_Lat_Conversion_Process {

	/**
	 * Process action name
	 *
	 * @var string
	 */
	protected $action = CYR_TO_LAT_TERM_CONVERSION_ACTION;

	/**
	 * Task. Updates single term
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

		$this->log( __( 'Term slug converted:', 'cyr2lat' ) . ' ' . urldecode( $term->slug ) . ' => ' . $sanitized_slug );

		return false;
	}

	/**
	 * Complete
	 */
	protected function complete() {
		parent::complete();

		$this->log( __( 'Term slugs conversion completed.', 'cyr2lat' ) );
	}
}
