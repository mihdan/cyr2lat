<?php
/**
 * Transliterator class file.
 *
 * @package cyr-to-lat
 */

namespace CyrToLat\Transliteration;

/**
 * Converts strings using a Cyr-To-Lat conversion table.
 */
class Transliterator {

	/**
	 * Transliterate string using a table.
	 *
	 * @param string $str   String.
	 * @param array  $table Conversion table.
	 *
	 * @return string
	 */
	public function transliterate( string $str, array $table ): string {
		return strtr( $str, $table );
	}
}
