<?php
/**
 * Bootstrap file for Cyr-To-Lat phpunit tests.
 *
 * @package cyr-to-lat
 */

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection RealpathInStreamContextInspection */
/** @noinspection PhpParamsInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

use tad\FunctionMocker\FunctionMocker;

/**
 * Plugin main file.
 */
define( 'PLUGIN_MAIN_FILE', realpath( __DIR__ . '/../../cyr-to-lat.php' ) );

/**
 * Plugin path.
 */
define( 'PLUGIN_PATH', realpath( dirname( PLUGIN_MAIN_FILE ) ) );

/**
 * Kilobytes in bytes.
 */
const KB_IN_BYTES = 1024;

require_once PLUGIN_PATH . '/vendor/autoload.php';

if ( ! defined( 'ABSPATH' ) ) {
	/**
	 * WordPress ABSPATH.
	 */
	define( 'ABSPATH', PLUGIN_PATH . '/../../../' );
}

/**
 * Plugin version.
 */
const CYR_TO_LAT_TEST_VERSION = '6.0.4';

/**
 * Path to the plugin dir.
 */
const CYR_TO_LAT_TEST_PATH = PLUGIN_PATH;

/**
 * Plugin dir url.
 */
const CYR_TO_LAT_TEST_URL = 'https://site.org/wp-content/plugins/cyr2lat';

/**
 * Main plugin file.
 */
const CYR_TO_LAT_TEST_FILE = PLUGIN_MAIN_FILE;

/**
 * Plugin prefix.
 */
const CYR_TO_LAT_TEST_PREFIX = 'cyr_to_lat';

/**
 * Post conversion action.
 */
const CYR_TO_LAT_TEST_POST_CONVERSION_ACTION = 'post_conversion_action';

/**
 * Term conversion action.
 */
const CYR_TO_LAT_TEST_TERM_CONVERSION_ACTION = 'term_conversion_action';

/**
 * Minimum required php version.
 */
const CYR_TO_LAT_TEST_MINIMUM_PHP_REQUIRED_VERSION = '7.0';

/**
 * Minimum required max_input_vars value.
 */
const CYR_TO_LAT_TEST_REQUIRED_MAX_INPUT_VARS = 1000;

FunctionMocker::init(
	[
		'blacklist'             => [
			realpath( PLUGIN_PATH ),
		],
		'whitelist'             => [
			realpath( PLUGIN_PATH . '/cyr-to-lat.php' ),
			realpath( PLUGIN_PATH . '/src/php' ),
			realpath( PLUGIN_PATH . '/tests/unit/Stubs' ),
		],
		'redefinable-internals' => [
			'class_exists',
			'define',
			'defined',
			'constant',
			'filter_input',
			'function_exists',
			'ini_get',
			'mb_strtolower',
			'phpversion',
			'realpath',
			'time',
			'error_log',
			'rename',
		],
	]
);

WP_Mock::bootstrap();
