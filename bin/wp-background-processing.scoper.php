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
	'exclude-constants' => [ 'MINUTE_IN_SECONDS' ],
];
