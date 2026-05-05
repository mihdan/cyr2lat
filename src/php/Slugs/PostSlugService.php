<?php
/**
 * PostSlugService class file.
 *
 * @package cyr-to-lat
 */

namespace CyrToLat\Slugs;

/**
 * Handles post slug generation.
 */
class PostSlugService {

	/**
	 * Filter post data before it is inserted.
	 *
	 * @param array|mixed $data                 An array of slashed, sanitized, and processed post data.
	 * @param array|mixed $postarr              An array of sanitized but otherwise unmodified post data.
	 * @param array|mixed $unsanitized_postarr  An array of slashed yet unsanitized and unprocessed post data.
	 * @param bool        $update               Whether this is an existing post update.
	 *
	 * @return array|mixed
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function filter_post_data( $data, $postarr = [], $unsanitized_postarr = [], bool $update = false ) {
		if ( ! is_array( $data ) ) {
			return $data;
		}

		if (
			empty( $data['post_name'] ) &&
			! empty( $data['post_title'] ) &&
			! $this->is_skipped_post_data( $data, $postarr )
		) {
			$data['post_name'] = sanitize_title( $data['post_title'] );
		}

		if (
			! empty( $data['post_name'] ) &&
			$this->has_non_ascii_chars( (string) $data['post_name'] ) &&
			! $this->is_skipped_post_data( $data, $postarr )
		) {
			$data['post_name'] = sanitize_title( $data['post_name'] );
		}

		return $data;
	}

	/**
	 * Whether post data should be skipped.
	 *
	 * @param array       $data    Post data.
	 * @param array|mixed $postarr Original post array.
	 *
	 * @return bool
	 */
	private function is_skipped_post_data( array $data, $postarr = [] ): bool {
		if ( in_array( $data['post_status'] ?? '', [ 'auto-draft', 'revision' ], true ) ) {
			return true;
		}

		if ( 'revision' === ( $data['post_type'] ?? '' ) ) {
			return true;
		}

		$postarr = (array) $postarr;
		$post_id = (int) ( $postarr['ID'] ?? $data['ID'] ?? 0 );

		if ( $post_id <= 0 ) {
			return false;
		}

		if ( function_exists( 'wp_is_post_autosave' ) && wp_is_post_autosave( $post_id ) ) {
			return true;
		}

		return function_exists( 'wp_is_post_revision' ) && (bool) wp_is_post_revision( $post_id );
	}

	/**
	 * Whether the value contains non-ASCII characters.
	 *
	 * @param string $value Value.
	 *
	 * @return bool
	 */
	private function has_non_ascii_chars( string $value ): bool {
		return (bool) preg_match( '/[^\x00-\x7F]/', $value );
	}
}
