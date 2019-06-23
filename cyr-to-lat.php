<?php
/**
 * Cyr-To-Lat
 *
 * Plugin Name: Cyr-To-Lat
 * Plugin URI: https://wordpress.org/plugins/cyr2lat/
 * Description: Converts Cyrillic characters in post and term slugs to Latin characters. Useful for creating human-readable URLs. Based on the original plugin by Anton Skorobogatov.
 * Author: Sergey Biryukov, Mikhail Kobzarev, Igor Gergel
 * Author URI: https://profiles.wordpress.org/sergeybiryukov/
 * Requires at least: 2.3
 * Tested up to: 5.2
 * Version: 4.2.1
 * Stable tag: 4.2.1
 *
 * Text Domain: cyr2lat
 * Domain Path: /languages/
 *
 * @package cyr-to-lat
 * @author  Sergey Biryukov, Mikhail Kobzarev, Igor Gergel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Plugin version.
 */
define( 'CYR_TO_LAT_VERSION', '4.2.1' );

/**
 * Path to the plugin dir.
 */
define( 'CYR_TO_LAT_PATH', dirname( __FILE__ ) );

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
 * Init plugin class on plugin load.
 */

static $plugin;

if ( ! isset( $plugin ) ) {
	require_once CYR_TO_LAT_PATH . '/includes/class-cyr-to-lat-requirements.php'; // We cannot use composer autoloader here.
	$requirements = new Cyr_To_Lat_Requirements();
	if ( ! $requirements->are_requirements_met() ) {
		$plugin = false;
		return;
	}

	require_once CYR_TO_LAT_PATH . '/vendor/autoload.php';

	$plugin = new Cyr_To_Lat_Main();
}

