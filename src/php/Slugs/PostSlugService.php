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
		return $data;
	}
}
