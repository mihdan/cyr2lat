<?php
/**
 * PHP-Scoper configuration file.
 *
 * @package cyr-to-lat
 */

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpUndefinedClassInspection */
/** @noinspection PhpUndefinedNamespaceInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

declare( strict_types=1 );

use Symfony\Component\Finder\Finder;

/**
 * Dir to scope
 */
const POLYFILL_MBSTRING_BASE_DIR = __DIR__ . '/../vendor/symfony/polyfill-mbstring';

return [
	'finders'  => [
		Finder::create()
			->files()
			->notName( '/LICENSE|.*\\.md|composer\\.json/' )
			->in( POLYFILL_MBSTRING_BASE_DIR ),
	],
	'patchers' => [
		/**
		 * Patcher to remove prefix from global classes.
		 */
		static function ( string $file_path, string $prefix, string $contents ): string {
			if ( false !== strpos( $file_path, 'unidata' ) ) {
				// Do not touch files in unidata folder.
				return $contents;
			}

			// No blank line before file comment.
			// Blank line after file comment.
			return str_replace(
				[ "<?php\n\n/**", " */\nnamespace" ],
				[ "<?php\n/**", " */\n\nnamespace" ],
				$contents
			);
		},
	],
];
