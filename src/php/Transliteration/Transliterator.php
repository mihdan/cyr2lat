<?php
/**
 * Transliterator class file.
 *
 * @package cyr-to-lat
 */

namespace CyrToLat\Transliteration;

use CyrToLat\ConversionTables;
use CyrToLat\Settings\Settings;
use CyrToLat\Symfony\Polyfill\Mbstring\Mbstring;

/**
 * Converts strings using a Cyr-To-Lat conversion table.
 */
class Transliterator {

	/**
	 * Plugin settings.
	 *
	 * @var Settings
	 */
	private Settings $settings;

	/**
	 * Constructor.
	 *
	 * @param Settings $settings Plugin settings.
	 */
	public function __construct( Settings $settings ) {
		$this->settings = $settings;
	}

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
		$str = $this->split_chinese_string( $str, $table );

		return strtr( $str, $table );
	}

	/**
	 * Split Chinese string by hyphens.
	 *
	 * @param string $str   String.
	 * @param array  $table Conversion table.
	 *
	 * @return string
	 */
	public function split_chinese_string( string $str, array $table ): string {
		if ( ! $this->settings->is_chinese_locale() || mb_strlen( $str ) < 4 ) {
			return $str;
		}

		$chars = Mbstring::mb_str_split( $str );
		$str   = '';

		foreach ( $chars as $char ) {
			if ( isset( $table[ $char ] ) ) {
				$str .= '-' . $char . '-';
			} else {
				$str .= $char;
			}
		}

		return $str;
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
