<?php
/**
 * Bootstrap file for Cyr-To-Lat phpunit tests.
 *
 * @package cyr-to-lat
 */

// phpcs:ignore Generic.Commenting.DocComment.MissingShort
/** @noinspection RealpathInStreamContextInspection */

use tad\FunctionMocker\FunctionMocker;

/**
 * Plugin test dir.
 */
define( 'PLUGIN_TESTS_DIR', __DIR__ );

/**
 * Plugin main file.
 */
define( 'PLUGIN_MAIN_FILE', realpath( __DIR__ . '/../../cyr-to-lat.php' ) );

/**
 * Plugin path.
 */
define( 'PLUGIN_PATH', realpath( dirname( PLUGIN_MAIN_FILE ) ) );

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
define( 'CYR_TO_LAT_TEST_VERSION', '5.0.2' );

/**
 * Path to the plugin dir.
 */
define( 'CYR_TO_LAT_TEST_PATH', PLUGIN_PATH );

/**
 * Plugin dir url.
 */
define( 'CYR_TO_LAT_TEST_URL', 'http://site.org/wp-content/plugins/cyr2lat' );

/**
 * Main plugin file.
 */
define( 'CYR_TO_LAT_TEST_FILE', PLUGIN_MAIN_FILE );

/**
 * Plugin prefix.
 */
define( 'CYR_TO_LAT_TEST_PREFIX', 'cyr_to_lat' );

/**
 * Post conversion action.
 */
define( 'CYR_TO_LAT_TEST_POST_CONVERSION_ACTION', 'post_conversion_action' );

/**
 * Term conversion action.
 */
define( 'CYR_TO_LAT_TEST_TERM_CONVERSION_ACTION', 'term_conversion_action' );

/**
 * Minimum required php version.
 */
define( 'CYR_TO_LAT_TEST_MINIMUM_PHP_REQUIRED_VERSION', '5.6' );

/**
 * Minimum required max_input_vars value.
 */
define( 'CYR_TO_LAT_TEST_REQUIRED_MAX_INPUT_VARS', 1000 );

FunctionMocker::init(
	[
		'blacklist'             => [
			realpath( PLUGIN_PATH ),
		],
		'whitelist'             => [
			realpath( PLUGIN_PATH . '/cyr-to-lat.php' ),
			realpath( PLUGIN_PATH . '/src/php' ),
			realpath( PLUGIN_PATH . '/tests/phpunit/stubs' ),
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
