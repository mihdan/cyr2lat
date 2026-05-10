<?php
/**
 * Transliterator class file.
 *
 * @package cyr-to-lat
 */

// phpcs:ignore Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpInternalEntityUsedInspection */

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
	protected Settings $settings;

	/**
	 * Constructor.
	 *
	 * @param Settings $settings Plugin settings.
	 */
	public function __construct( Settings $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Transliterate string using the active table.
	 *
	 * @param string           $str     String.
	 * @param SlugContext|null $context Slug context.
	 *
	 * @return string
	 */
	public function transliterate( string $str, ?SlugContext $context = null ): string {
		$table = (array) apply_filters( 'ctl_table', $this->settings->get_table() );

		return $this->transliterate_with_table( $str, $table, $context );
	}

	/**
	 * Transliterate string using a provided table.
	 *
	 * @param string           $str     String.
	 * @param array            $table   Conversion table.
	 * @param SlugContext|null $context Slug context.
	 *
	 * @return string
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function transliterate_with_table( string $str, array $table, ?SlugContext $context = null ): string {
		$str = $this->fix_mac_string( $str, $table );
		$str = $this->split_chinese_string( $str, $table );

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

	/**
	 * Split Chinese string by hyphens.
	 *
	 * @param string $str   String.
	 * @param array  $table Conversion table.
	 *
	 * @return string
	 */
	public function split_chinese_string( string $str, array $table ): string {
		if ( ! $this->settings->is_chinese_locale() || Mbstring::mb_strlen( $str ) < 4 ) {
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
}
