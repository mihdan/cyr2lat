<?php
/**
 * Cyr-To-Lat
 *
 * Plugin Name: Cyr-To-Lat
 * Plugin URI: https://wordpress.org/plugins/cyr2lat/
 * Description: Converts Cyrillic characters in post and term slugs to Latin characters. Useful for creating human-readable URLs. Based on the original plugin by Anton Skorobogatov.
 * Author: Sergey Biryukov, Mikhail Kobzarev, Igor Gergel
 * Author URI: https://profiles.wordpress.org/sergeybiryukov/
 * Requires at least: 5.1
 * Tested up to: 5.6
 * Version: 4.6.2
 * Stable tag: 4.6.2
 *
 * Text Domain: cyr2lat
 * Domain Path: /languages/
 *
 * @package cyr-to-lat
 * @author  Sergey Biryukov, Mikhail Kobzarev, Igor Gergel
 */

namespace Cyr_To_Lat;

if ( ! defined( 'ABSPATH' ) ) {
	// @codeCoverageIgnoreStart
	exit;
	// @codeCoverageIgnoreEnd
}

if ( defined( 'CYR_TO_LAT_VERSION' ) ) {
	return;
}

/**
 * Plugin version.
 */
define( 'CYR_TO_LAT_VERSION', '4.6.2' );

/**
 * Path to the plugin dir.
 */
define( 'CYR_TO_LAT_PATH', __DIR__ );

/**
 * Plugin dir url.
 */
define( 'CYR_TO_LAT_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );

/**
 * Main plugin file.
 */
define( 'CYR_TO_LAT_FILE', __FILE__ );

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

/**
 * Minimum required max_input_vars value.
 */
define( 'CYR_TO_LAT_REQUIRED_MAX_INPUT_VARS', 1000 );

/**
 * Init plugin on plugin load.
 */
require_once constant( 'CYR_TO_LAT_PATH' ) . '/vendor/autoload.php';

$cyr_to_lat_requirements = new Requirements();

if ( ! $cyr_to_lat_requirements->are_requirements_met() ) {
	return;
}

global $cyr_to_lat_plugin;

$cyr_to_lat_plugin = new Main();
