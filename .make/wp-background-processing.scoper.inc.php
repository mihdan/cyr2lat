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
const WP_BACKGROUND_PROCESSING_BASE_DIR = __DIR__ . '/../vendor/a5hleyrich/wp-background-processing';

return [
	'finders'  => [
		Finder::create()->files()->in( WP_BACKGROUND_PROCESSING_BASE_DIR . '/classes' ),
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
