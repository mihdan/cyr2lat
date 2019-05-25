<?php
/**
 * Background old post slugs converting process
 *
 * @package cyr-to-lat
 */

/**
 * Class Cyr_To_Lat_Post_Conversion_Process
 */
class Cyr_To_Lat_Post_Conversion_Process extends Cyr_To_Lat_Conversion_Process {

	/**
	 * Current post to convert.
	 *
	 * @var stdClass
	 */
	private $post;

	/**
	 * Process action name
	 *
	 * @var string
	 */
	protected $action = CYR_TO_LAT_POST_CONVERSION_ACTION;

	/**
	 * Task. Updates single post
	 *
	 * @param stdClass $post Queue item to iterate over.
	 *
	 * @return mixed
	 */
	protected function task( $post ) {
		global $wpdb;

		$this->post = $post;

		add_filter( 'locale', array( $this, 'filter_post_locale' ) );

		$sanitized_name = $this->main->ctl_sanitize_title( $post->post_name );

		remove_filter( 'locale', array( $this, 'filter_post_locale' ) );

		if ( $sanitized_name !== $post->post_name ) {
			add_post_meta( $post->ID, '_wp_old_slug', $post->post_name );
			// phpcs:disable WordPress.DB.DirectDatabaseQuery
			$wpdb->update( $wpdb->posts, array( 'post_name' => $sanitized_name ), array( 'ID' => $post->ID ) );
			// phpcs:enable
		}

		$this->log( __( 'Post slug converted:', 'cyr2lat' ) . ' ' . urldecode( $post->post_name ) . ' => ' . $sanitized_name );

		return false;
	}

	/**
	 * Complete
	 */
	protected function complete() {
		parent::complete();

		$this->log( __( 'Post slugs conversion completed.', 'cyr2lat' ) );
	}

	/**
	 * Filter post locale.
	 *
	 * @return string
	 */
	public function filter_post_locale() {
		$wpml_post_language_details = apply_filters( 'wpml_post_language_details', null, $this->post->ID );

		return isset( $wpml_post_language_details['locale'] ) ? $wpml_post_language_details['locale'] : get_locale();
	}
}
