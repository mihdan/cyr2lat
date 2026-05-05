<?php
/**
 * Transliterator class file.
 *
 * @package cyr-to-lat
 */

namespace CyrToLat\Transliteration;

use CyrToLat\ConversionTables;

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
		$str = $this->fix_mac_string( $str, $table );

		return strtr( $str, $table );
	}

	/**
	 * Fix string encoding on macOS.
	 *
	 * @param string $str   String.
	 * @param array  $table Conversion table.
	 *
	 * @return string
	 */
	private function fix_mac_string( string $str, array $table ): string {
		$fix_table = ConversionTables::get_fix_table_for_mac();

		$fix = [];
		foreach ( $fix_table as $key => $value ) {
			if ( isset( $table[ $key ] ) ) {
				$fix[ $value ] = $table[ $key ];
			}
		}

		return strtr( $str, $fix );
	}
}
