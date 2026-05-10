<?php
/**
 * BaseService class file.
 *
 * @package cyr-to-lat
 */

namespace CyrToLat\Slugs;

/**
 * Abstract class for slug services.
 */
abstract class BaseService {

	/**
	 * Whether the value contains non-ASCII characters.
	 *
	 * @param string $value Value.
	 *
	 * @return bool
	 */
	protected function has_non_ascii_chars( string $value ): bool {
		return (bool) preg_match( '/[^\x00-\x7F]/', $value );
	}
}
