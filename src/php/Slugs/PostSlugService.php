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
			! $this->is_skipped_post_data( $data )
		) {
			$data['post_name'] = sanitize_title( $data['post_title'] );
		}

		if (
			! empty( $data['post_name'] ) &&
			$this->has_non_ascii_chars( (string) $data['post_name'] ) &&
			! $this->is_skipped_post_data( $data )
		) {
			$data['post_name'] = sanitize_title( $data['post_name'] );
		}

		return $data;
	}

	/**
	 * Whether post data should be skipped.
	 *
	 * @param array $data Post data.
	 *
	 * @return bool
	 */
	private function is_skipped_post_data( array $data ): bool {
		return in_array( $data['post_status'] ?? '', [ 'auto-draft', 'revision' ], true );
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
