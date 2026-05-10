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
	 * Slug sanitization callback.
	 *
	 * @var callable|null
	 */
	private $sanitize_slug_callback;

	/**
	 * Constructor.
	 *
	 * @param callable|null $sanitize_slug Slug sanitization callback.
	 */
	public function __construct( ?callable $sanitize_slug = null ) {
		$this->sanitize_slug_callback = is_callable( $sanitize_slug ) ? $sanitize_slug : null;
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
	 * Whether the value contains non-ASCII characters.
	 *
	 * @param string $value Value.
	 *
	 * @return bool
	 */
	private function has_non_ascii_chars( string $value ): bool {
		return (bool) preg_match( '/[^\x00-\x7F]/', $value );
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
		if ( $this->sanitize_slug_callback ) {
			return (string) call_user_func( $this->sanitize_slug_callback, $value );
		}

		return sanitize_title( $value );
	}
}
