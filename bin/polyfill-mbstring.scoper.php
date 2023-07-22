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
	'finders'       => [
		Finder::create()
			->files()
			->notName( '/LICENSE|.*\\.md|composer\\.json/' )
			->in( POLYFILL_MBSTRING_BASE_DIR ),
	],
	'exclude-files' => [
		POLYFILL_MBSTRING_BASE_DIR . '/Resources/unidata/lowerCase.php',
		POLYFILL_MBSTRING_BASE_DIR . '/Resources/unidata/titleCaseRegexp.php',
		POLYFILL_MBSTRING_BASE_DIR . '/Resources/unidata/upperCase.php',
	],
];
