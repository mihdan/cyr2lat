<?php
/**
 * Cyr-To-Lat
 *
 * Plugin Name: Cyr-To-Lat
 * Plugin URI: https://wordpress.org/plugins/cyr2lat/
 * Description: Converts Cyrillic characters in post and term slugs to Latin characters. Useful for creating human-readable URLs. Based on the original plugin by Anton Skorobogatov.
 * Author: Sergey Biryukov, Mikhail Kobzarev
 * Author URI: https://profiles.wordpress.org/sergeybiryukov/
 * Requires at least: 2.3
 * Tested up to: 5.1
 * Version: 3.6.4
 * Stable tag: 3.6.4
 *
 * Text Domain: cyr-to-lat
 * Domain Path: /languages/
 *
 * @package cyr-to-lat
 * @author  Sergey Biryukov, Mikhail Kobzarev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

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
 * Plugin version.
 */
define( 'CYR_TO_LAT_VERSION', '3.6.4' );

/**
 * Init plugin class on plugin load.
 */

static $plugin;

if ( ! isset( $plugin ) ) {
	if ( version_compare( PHP_VERSION, '5.3.0' ) >= 0 ) {
		require_once CYR_TO_LAT_PATH . '/vendor/autoload.php';
	} else {
		require_once CYR_TO_LAT_PATH . '/vendor/autoload_52.php';
	}

	$plugin = new Cyr_To_Lat_Main();
}

// eof.
