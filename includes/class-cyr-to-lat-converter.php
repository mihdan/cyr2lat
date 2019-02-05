<?php
/**
 * Old slugs converter.
 *
 * @package cyr-to-lat
 */

/**
 * Class Cyr_To_Lat_Converter
 *
 * @class Cyr_To_Lat_Converter
 */
class Cyr_To_Lat_Converter {

	/**
	 * Plugin main class.
	 *
	 * @var Cyr_To_Lat_Main
	 */
	private $main;

	/**
	 * Plugin settings.
	 *
	 * @var Cyr_To_Lat_Settings
	 */
	private $settings;

	/**
	 * Cyr_To_Lat_Converter constructor.
	 *
	 * @param Cyr_To_Lat_Main     $main     Plugin main class.
	 * @param Cyr_To_Lat_Settings $settings Plugin settings.
	 */
	public function __construct( Cyr_To_Lat_Main $main, Cyr_To_Lat_Settings $settings ) {
		$this->main     = $main;
		$this->settings = $settings;
		$this->init_hooks();
	}

	/**
	 * Init class hooks.
	 */
	public function init_hooks() {
		if ( 'yes' === $this->settings->get_option( 'convert_existing_slugs' ) ) {
			$this->settings->set_option( 'convert_existing_slugs', 'no' );
			add_action( 'shutdown', array( $this, 'convert_existing_slugs' ) );
		}
	}

	/**
	 * Convert Existing Slugs
	 */
	public function convert_existing_slugs() {
		global $wpdb;

		// phpcs:disable WordPress.DB.DirectDatabaseQuery
		$posts = $wpdb->get_results( "SELECT ID, post_name FROM $wpdb->posts WHERE post_name REGEXP('[^A-Za-z0-9\-]+') AND post_status IN ('publish', 'future', 'private')" );
		// phpcs:enable

		foreach ( (array) $posts as $post ) {
			$sanitized_name = $this->main->ctl_sanitize_title( urldecode( $post->post_name ) );

			if ( $post->post_name !== $sanitized_name ) {
				add_post_meta( $post->ID, '_wp_old_slug', $post->post_name );
				// phpcs:disable WordPress.DB.DirectDatabaseQuery
				$wpdb->update( $wpdb->posts, array( 'post_name' => $sanitized_name ), array( 'ID' => $post->ID ) );
				// phpcs:enable
			}
		}

		// phpcs:disable WordPress.DB.DirectDatabaseQuery
		$terms = $wpdb->get_results( "SELECT term_id, slug FROM $wpdb->terms WHERE slug REGEXP('[^A-Za-z0-9\-]+') " );
		// phpcs:enable

		foreach ( (array) $terms as $term ) {
			$sanitized_slug = $this->main->ctl_sanitize_title( urldecode( $term->slug ) );

			if ( $term->slug !== $sanitized_slug ) {
				// phpcs:disable WordPress.DB.DirectDatabaseQuery
				$wpdb->update( $wpdb->terms, array( 'slug' => $sanitized_slug ), array( 'term_id' => $term->term_id ) );
				// phpcs:enable
			}
		}
	}
}
