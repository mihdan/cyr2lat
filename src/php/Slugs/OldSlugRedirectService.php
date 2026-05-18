<?php
/**
 * OldSlugRedirectService class file.
 *
 * @package cyr-to-lat
 */

namespace CyrToLat\Slugs;

use CyrToLat\Transliteration\Transliterator;

/**
 * Handles old slug redirect protection.
 */
class OldSlugRedirectService {

	/**
	 * Transliterator.
	 *
	 * @var Transliterator
	 */
	private Transliterator $transliterator;

	/**
	 * Constructor.
	 *
	 * @param Transliterator $transliterator Transliterator.
	 */
	public function __construct( Transliterator $transliterator ) {
		$this->transliterator = $transliterator;
	}

	/**
	 * Check for changed slugs.
	 *
	 * @param int   $post_id     Post ID.
	 * @param mixed $post        The post object.
	 * @param mixed $post_before The previous post object.
	 *
	 * @return void
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function check_for_changed_slugs( int $post_id, $post, $post_before ): void {
		// Don't bother if it hasn't changed.
		if ( $post->post_name === $post_before->post_name ) {
			return;
		}

		// We're only concerned with published, non-hierarchical objects.
		if ( ! ( 'publish' === $post->post_status || ( 'attachment' === get_post_type( $post ) && 'inherit' === $post->post_status ) ) || is_post_type_hierarchical( $post->post_type ) ) {
			return;
		}

		// Modify $post_before->post_name when cyr2lat converted the title.
		if (
			empty( $post_before->post_name ) &&
			$post->post_title !== $post->post_name &&
			$post->post_name === $this->transliterator->transliterate( $post->post_title )
		) {
			$post_before->post_name = rawurlencode( $post->post_title );
		}
	}
}
