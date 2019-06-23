<?php
/**
 * Bootstrap file for Cyr-To-Lat phpunit tests.
 *
 * @package cyr-to-lat
 */

define( 'PLUGIN_TESTS_DIR', __DIR__ );

define( 'PLUGIN_MAIN_FILE', __DIR__ . '/../../cyr-to-lat.php' );
define( 'PLUGIN_PATH', dirname( PLUGIN_MAIN_FILE ) );

require_once PLUGIN_PATH . '/vendor/autoload.php';

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', PLUGIN_PATH . '/../../' );
}

/**
 * Plugin version.
 */
define( 'CYR_TO_LAT_VERSION', 'test-version' );

/**
 * Plugin dir url.
 */
define( 'CYR_TO_LAT_URL', 'http://site.org/wp-content/plugins/cyr2lat' );

/**
 * Main plugin file.
 */
define( 'CYR_TO_LAT_FILE', PLUGIN_MAIN_FILE );

/**
 * Plugin prefix.
 */
define( 'CYR_TO_LAT_PREFIX', 'cyr_to_lat' );

/**
 * Post conversion action.
 */
define( 'CYR_TO_LAT_POST_CONVERSION_ACTION', 'post_conversion_action' );

/**
 * Term conversion action.
 */
define( 'CYR_TO_LAT_TERM_CONVERSION_ACTION', 'term_conversion_action' );

/**
 * Minimum required php version.
 */
define( 'CYR_TO_LAT_MINIMUM_PHP_REQUIRED_VERSION', '5.6' );

// Now call the bootstrap method of WP Mock.
\WP_Mock::bootstrap();
