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
const WP_BACKGROUND_PROCESSING_BASE_DIR = __DIR__ . '/../vendor/deliciousbrains/wp-background-processing';

return [
	'finders'           => [
		Finder::create()->files()->in( WP_BACKGROUND_PROCESSING_BASE_DIR . '/classes' ),
	],
	'exclude-constants' => [ 'MINUTE_IN_SECONDS', '/regex/' ],
	'patchers'          => [
		/**
		 * Patcher to remove prefix from global classes.
		 */
		static function ( string $file_path, string $prefix, string $contents ): string {
			// "Use" statements.
			$contents = preg_replace(
				'/use\s+' . $prefix . '\\\(.+)/m',
				'use $1',
				$contents
			);

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
