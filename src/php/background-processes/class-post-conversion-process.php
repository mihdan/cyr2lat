<?php
/**
 * Background old post slugs converting process
 *
 * @package cyr-to-lat
 */

namespace Cyr_To_Lat;

use stdClass;

/**
 * Class Post_Conversion_Process
 */
class Post_Conversion_Process extends Conversion_Process {

	/**
	 * Site locale.
	 *
	 * @var string
	 */
	private $locale;

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
	protected $action;

	/**
	 * Post_Conversion_Process constructor.
	 *
	 * @param Main $main Plugin main class.
	 */
	public function __construct( $main ) {
		$this->action = constant( 'CYR_TO_LAT_POST_CONVERSION_ACTION' );
		$this->locale = get_locale();

		parent::__construct( $main );
	}

	/**
	 * Task. Updates single post
	 *
	 * @param stdClass $post Queue item to iterate over.
	 *
	 * @return boolean
	 * @noinspection PhpParameterNameChangedDuringInheritanceInspection
	 */
	protected function task( $post ) {
		global $wpdb;

		$this->post        = $post;
		$decoded_post_name = urldecode( $post->post_name );

		add_filter( 'locale', [ $this, 'filter_post_locale' ] );
		$transliterated_name = $this->main->transliterate( $decoded_post_name );
		remove_filter( 'locale', [ $this, 'filter_post_locale' ] );

		if ( $transliterated_name !== $decoded_post_name ) {
			update_post_meta( $post->ID, '_wp_old_slug', $post->post_name );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->update( $wpdb->posts, [ 'post_name' => rawurlencode( $transliterated_name ) ], [ 'ID' => $post->ID ] );

			$this->log( __( 'Post slug converted:', 'cyr2lat' ) . ' ' . $decoded_post_name . ' => ' . $transliterated_name );

			if ( 'attachment' === $post->post_type ) {
				$this->rename_attachment( $post->ID );
				$this->rename_thumbnails( $post->ID );
				$this->update_attachment_metadata( $post->ID );
			}
		}

		return false;
	}

	/**
	 * Rename attachment.
	 *
	 * @param int $post_id Post ID.
	 */
	protected function rename_attachment( $post_id ) {
		$file = get_attached_file( $post_id );

		if ( $file ) {
			$updated             = false;
			$transliterated_file = $this->get_transliterated_file( $file );
			$rename              = $this->rename_file( $file, $transliterated_file );
			if ( $rename ) {
				$updated = update_attached_file( $post_id, $transliterated_file );
			}

			if ( $updated ) {
				$this->log( __( 'Attachment file converted:', 'cyr2lat' ) . ' ' . $file . ' => ' . $transliterated_file );

				return;
			}
		}

		$this->log( __( 'Cannot convert attachment file for attachment id:', 'cyr2lat' ) . ' ' . $post_id );
	}

	/**
	 * Rename thumbnails.
	 *
	 * @param int $post_id Post ID.
	 */
	protected function rename_thumbnails( $post_id ) {
		$sizes = get_intermediate_image_sizes();

		foreach ( $sizes as $size ) {
			$url                 = wp_get_attachment_image_src( $post_id, $size )[0];
			$file                = untrailingslashit( constant( 'ABSPATH' ) ) . wp_make_link_relative( $url );
			$transliterated_file = $this->get_transliterated_file( $file );

			$rename = $this->rename_file( $file, $transliterated_file );
			if ( $rename ) {
				$this->log( __( 'Thumbnail file renamed:', 'cyr2lat' ) . ' ' . $file . ' => ' . $transliterated_file );
			}
			if ( false === $rename ) {
				$this->log( __( 'Cannot rename thumbnail file:', 'cyr2lat' ) . ' ' . $file );
			}
		}
	}

	/**
	 * Update attachment metadata.
	 *
	 * @param int $attachment_id Attachment ID.
	 */
	protected function update_attachment_metadata( $attachment_id ) {
		$meta = wp_get_attachment_metadata( $attachment_id );

		if ( isset( $meta['file'] ) ) {
			$meta['file'] = $this->main->transliterate( $meta['file'] );
		}

		if ( isset( $meta['sizes'] ) ) {
			foreach ( $meta['sizes'] as $key => $size ) {
				$meta['sizes'][ $key ]['file'] = $this->main->transliterate( $meta['sizes'][ $key ]['file'] );
			}
		}

		wp_update_attachment_metadata( $attachment_id, $meta );
	}

	/**
	 * Get transliterated filename with path.
	 *
	 * @param string $file Filename.
	 *
	 * @return string
	 */
	protected function get_transliterated_file( $file ) {
		$path                    = pathinfo( $file );
		$transliterated_filename = $this->main->transliterate( $path['filename'] );

		return $path['dirname'] . '/' . $transliterated_filename . '.' . $path['extension'];
	}

	/**
	 * Rename file.
	 * Return false if rename failed.
	 *
	 * @param string $file     Full filename.
	 * @param string $new_file New full filename.
	 *
	 * @return bool|null
	 */
	protected function rename_file( $file, $new_file ) {
		$path     = pathinfo( $file );
		$new_path = pathinfo( $new_file );

		$filename     = $path['filename'];
		$new_filename = $new_path['filename'];

		if ( $new_filename !== $filename ) {
			return rename( $file, $new_file );
		}

		return null;
	}

	/**
	 * Complete
	 */
	protected function complete() {
		parent::complete();

		wp_cache_flush();

		$this->log( __( 'Post slugs conversion completed.', 'cyr2lat' ) );
	}

	/**
	 * Filter post locale
	 *
	 * @return string
	 */
	public function filter_post_locale() {
		// This is common filter for WPML and Polylang, since Polylang supports wpml_post_language_details filter.
		$wpml_post_language_details = apply_filters( 'wpml_post_language_details', false, $this->post->ID );

		return isset( $wpml_post_language_details['locale'] ) ? $wpml_post_language_details['locale'] : $this->locale;
	}
}
