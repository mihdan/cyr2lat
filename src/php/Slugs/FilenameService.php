<?php
/**
 * FilenameService class file.
 *
 * @package cyr-to-lat
 */

// phpcs:ignore Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpInternalEntityUsedInspection */

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
	 */
	public function sanitize_filename( $filename, $filename_raw ): string {
		global $wp_version;

		$pre = apply_filters( 'ctl_pre_sanitize_filename', false, $filename );

		if ( false !== $pre ) {
			return (string) $pre;
		}

		$filename = (string) $filename;
		$is_utf8  = version_compare( (string) $wp_version, '6.9', '>=' ) ? 'wp_is_valid_utf8' : 'seems_utf8';

		if ( $is_utf8( $filename ) ) {
			$filename = (string) Mbstring::mb_strtolower( $filename );
		}

		return $this->sanitize_transliterated_filename(
			$this->transliterate_filename( $filename ),
			$filename
		);
	}

	/**
	 * Sanitize a transliterated filename without invoking the WordPress filename filter again.
	 *
	 * The plugin runs on the final sanitize_file_name filter. A custom conversion table can
	 * therefore introduce unsafe characters after WordPress has completed its own checks.
	 * Preserve the extension WordPress already sanitized and repeat the relevant filename
	 * hardening on the transliteration result.
	 *
	 * @param string $filename        Transliterated filename.
	 * @param string $source_filename Filename sanitized by WordPress before transliteration.
	 *
	 * @return string
	 */
	public function sanitize_transliterated_filename( string $filename, string $source_filename ): string {
		$filename = $this->preserve_source_extension( $filename, $source_filename );
		$filename = $this->remove_unsafe_filename_chars( $filename );

		return $this->harden_multiple_extensions( $filename );
	}

	/**
	 * Preserve the source extension or the lack of one.
	 *
	 * @param string $filename        Transliterated filename.
	 * @param string $source_filename Filename before transliteration.
	 *
	 * @return string
	 */
	private function preserve_source_extension( string $filename, string $source_filename ): string {
		$source_dot = strrpos( $source_filename, '.' );

		if ( false === $source_dot || strlen( $source_filename ) - 1 === $source_dot ) {
			// A conversion table must not be able to add an extension to an extensionless file.
			return str_replace( '.', '-', $filename );
		}

		$extension    = substr( $source_filename, $source_dot + 1 );
		$filename_dot = strrpos( $filename, '.' );
		$filename     = false === $filename_dot ? $filename : (string) substr( $filename, 0, $filename_dot );

		return $filename . '.' . $extension;
	}

	/**
	 * Remove unsafe characters introduced during transliteration.
	 *
	 * @param string $filename Filename.
	 *
	 * @return string
	 * @noinspection UnnecessaryCastingInspection
	 */
	private function remove_unsafe_filename_chars( string $filename ): string {
		$special_chars = [
			'?',
			'[',
			']',
			'/',
			'\\',
			'=',
			'<',
			'>',
			':',
			';',
			',',
			"'",
			'"',
			'&',
			'$',
			'#',
			'*',
			'(',
			')',
			'|',
			'~',
			'`',
			'!',
			'{',
			'}',
			'%',
			'+',
			'’',
			'«',
			'»',
			'”',
			'“',
			chr( 0 ),
		];

		$filename = (string) str_replace( $special_chars, '', $filename );
		$filename = preg_replace( '/[\x00-\x1F\x7F]/', '', $filename ) ?? $filename;
		$filename = preg_replace( '#\p{Zs}#u', ' ', $filename ) ?? $filename;
		$filename = preg_replace( '/\.{2,}/', '.', $filename ) ?? $filename;
		$filename = preg_replace( '/[\r\n\t -]+/', '-', $filename ) ?? $filename;

		return trim( $filename, '.-_' );
	}

	/**
	 * Harden executable-looking intermediate extensions.
	 *
	 * @param string $filename Filename.
	 *
	 * @return string
	 */
	private function harden_multiple_extensions( string $filename ): string {
		$parts = explode( '.', $filename );

		if ( count( $parts ) <= 2 ) {
			return $filename;
		}

		$filename  = (string) array_shift( $parts );
		$extension = (string) array_pop( $parts );
		$mimes     = get_allowed_mime_types();

		foreach ( $parts as $part ) {
			$filename .= '.' . $part;

			if ( ! preg_match( '/^[a-zA-Z]{2,5}\d?$/', $part ) ) {
				continue;
			}

			$allowed = false;

			foreach ( array_keys( $mimes ) as $ext_preg ) {
				if ( preg_match( '!^(' . $ext_preg . ')$!i', $part ) ) {
					$allowed = true;
					break;
				}
			}

			if ( ! $allowed ) {
				$filename .= '_';
			}
		}

		return $filename . '.' . $extension;
	}
}
