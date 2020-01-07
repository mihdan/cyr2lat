<?php
/**
 * PHP-Scoper configuration file.
 *
 * @package cyr-to-lat
 */

declare( strict_types=1 );

use Symfony\Component\Finder\Finder;

/**
 * Dir to scope
 */
const POLYFILL_MBSTRING_BASE_DIR = __DIR__ . '/../vendor/symfony/polyfill-mbstring';

return [
	'finders'  => [
		Finder::create()->files()->in( POLYFILL_MBSTRING_BASE_DIR ),
	],
	'patchers' => [
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
			$contents = str_replace(
				"<?php\n\n/**",
				"<?php\n/**",
				$contents
			);

			// Blank line after file comment.
			$contents = str_replace(
				" */\nnamespace",
				" */\n\nnamespace",
				$contents
			);

			return $contents;
		},
	],
];
