<?php
/**
 * @package cyr-to-lat
 */

class Cyr_To_Lat_Converter {
	/**
	 * Convert Existing Slugs
	 */
	public function convert_existing_slugs() {
		global $wpdb;

		// phpcs:disable WordPress.DB.DirectDatabaseQuery
		$posts = $wpdb->get_results( "SELECT ID, post_name FROM $wpdb->posts WHERE post_name REGEXP('[^A-Za-z0-9\-]+') AND post_status IN ('publish', 'future', 'private')" );
		// phpcs:enable

		foreach ( (array) $posts as $post ) {
			$sanitized_name = $this->ctl_sanitize_title( urldecode( $post->post_name ) );

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
			$sanitized_slug = $this->ctl_sanitize_title( urldecode( $term->slug ) );

			if ( $term->slug !== $sanitized_slug ) {
				// phpcs:disable WordPress.DB.DirectDatabaseQuery
				$wpdb->update( $wpdb->terms, array( 'slug' => $sanitized_slug ), array( 'term_id' => $term->term_id ) );
				// phpcs:enable
			}
		}
	}
}