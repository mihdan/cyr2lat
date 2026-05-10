<?php
/**
 * PostSlugService class file.
 *
 * @package cyr-to-lat
 */

namespace CyrToLat\Slugs;

use CyrToLat\Main;

/**
 * Handles post slug generation.
 */
class PostSlugService extends BaseService {

	/**
	 * Main plugin class.
	 *
	 * @var Main
	 */
	private Main $main;

	/**
	 * Constructor.
	 *
	 * @param Main $main Main plugin class.
	 */
	public function __construct( Main $main ) {
		$this->main = $main;
	}

	/**
	 * Filter post data before it is inserted.
	 *
	 * @param array $data                      An array of slashed, sanitized, and processed post data.
	 * @param array $postarr                   An array of sanitized (and slashed) but otherwise unmodified post data.
	 * @param array $unsanitized_postarr       An array of slashed yet *unsanitized* and unprocessed post data as
	 *                                         originally passed to wp_insert_post().
	 * @param bool  $update                    Whether this is an existing post being updated.
	 *
	 * @return array
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function filter_post_data( array $data, array $postarr = [], array $unsanitized_postarr = [], bool $update = false ): array {
		if (
			empty( $data['post_name'] ) &&
			! empty( $data['post_title'] ) &&
			! $this->is_skipped_post_data( $data, $postarr )
		) {
			$data['post_name'] = $this->sanitize_slug( (string) $data['post_title'] );
		}

		if (
			! empty( $data['post_name'] ) &&
			$this->requires_sanitization( (string) $data['post_name'] ) &&
			! $this->is_skipped_post_data( $data, $postarr )
		) {
			$data['post_name'] = $this->sanitize_slug( rawurldecode( (string) $data['post_name'] ) );
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

		return function_exists( 'wp_is_post_revision' ) && wp_is_post_revision( $post_id );
	}

	/**
	 * Whether the value requires explicit sanitization.
	 *
	 * @param string $value Value.
	 *
	 * @return bool
	 */
	private function requires_sanitization( string $value ): bool {
		if ( $this->has_non_ascii_chars( $value ) ) {
			return true;
		}

		$decoded = rawurldecode( $value );

		return $decoded !== $value && $this->has_non_ascii_chars( $decoded );
	}

	/**
	 * Sanitize a slug value.
	 *
	 * @param string $value Value.
	 *
	 * @return string
	 */
	private function sanitize_slug( string $value ): string {
		return $this->main->sanitize_explicit_slug( $value );
	}
}
