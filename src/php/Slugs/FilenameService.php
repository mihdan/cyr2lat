<?php
/**
 * FilenameService class file.
 *
 * @package cyr-to-lat
 */

namespace CyrToLat\Slugs;

use CyrToLat\Transliteration\SlugContext;
use CyrToLat\Transliteration\Transliterator;
use CyrToLat\Symfony\Polyfill\Mbstring\Mbstring;

/**
 * Handles filename transliteration.
 */
class FilenameService {

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
	 * Transliterate a filename.
	 *
	 * @param string $filename Filename.
	 *
	 * @return string
	 */
	public function transliterate_filename( string $filename ): string {
		return $this->transliterator->transliterate(
			$filename,
			new SlugContext( SlugContext::TYPE_FILENAME )
		);
	}

	/**
	 * Sanitize filename.
	 *
	 * @param string|mixed $filename     Sanitized filename.
	 * @param string|mixed $filename_raw The filename prior to sanitization.
	 *
	 * @return string
	 * @noinspection PhpUnusedParameterInspection
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function sanitize_filename( $filename, $filename_raw ): string {
		global $wp_version;

		$pre = apply_filters( 'ctl_pre_sanitize_filename', false, $filename );

		if ( false !== $pre ) {
			return (string) $pre;
		}

		$filename = (string) $filename;
		$is_utf8  = version_compare( (string) $wp_version, '6.9-RC1', '>=' ) ? 'wp_is_valid_utf8' : 'seems_utf8';

		if ( $is_utf8( $filename ) ) {
			$filename = (string) Mbstring::mb_strtolower( $filename );
		}

		return $this->transliterate_filename( $filename );
	}
}
