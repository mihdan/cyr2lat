<?php
/**
 * Cyr-To-Lat
 *
 * @package           cyr-to-lat
 * @author            Sergey Biryukov, Mikhail Kobzarev, Igor Gergel
 * @license           GPL-2.0-or-later
 * @wordpress-plugin
 *
 * Plugin Name:       Cyr-To-Lat
 * Plugin URI:        https://wordpress.org/plugins/cyr2lat/
 * Description:       Convert Non-Latin characters in post and term slugs to Latin characters. Useful for creating human-readable URLs. Based on the original plugin by Anton Skorobogatov.
 * Version:           6.1.0
 * Requires at least: 5.1
 * Requires PHP:      7.0.0
 * Author:            Sergey Biryukov, Mikhail Kobzarev, Igor Gergel
 * Author URI:        https://profiles.wordpress.org/sergeybiryukov/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       cyr2lat
 * Domain Path:       /languages/
 *
 *
 * WC requires at least: 3.0
 * WC tested up to:      8.6
 */

// phpcs:ignore Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpDefineCanBeReplacedWithConstInspection */

use CyrToLat\Main;

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
define( 'CYR_TO_LAT_VERSION', '6.1.0' );

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
define( 'CYR_TO_LAT_MINIMUM_PHP_REQUIRED_VERSION', '7.0' );

/**
 * Minimum required max_input_vars value.
 */
define( 'CYR_TO_LAT_REQUIRED_MAX_INPUT_VARS', 1000 );

require_once constant( 'CYR_TO_LAT_PATH' ) . '/vendor/autoload.php';
require_once constant( 'CYR_TO_LAT_PATH' ) . '/libs/polyfill-mbstring/bootstrap.php';

/**
 * Get main class instance.
 *
 * @return Main
 */
function cyr_to_lat(): Main {
	// Global for backwards compatibility.
	global $cyr_to_lat_plugin;

	if ( ! $cyr_to_lat_plugin ) {
		$cyr_to_lat_plugin = new Main();
	}

	return $cyr_to_lat_plugin;
}

cyr_to_lat()->init();
