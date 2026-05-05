<?php
/**
 * OldSlugRedirectService class file.
 *
 * @package cyr-to-lat
 */

namespace CyrToLat\Slugs;

/**
 * Handles old slug redirect protection.
 */
class OldSlugRedirectService {

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
	}
}
