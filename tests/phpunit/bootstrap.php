<?php
define( 'PLUGIN_TESTS_DIR', __DIR__ );

define( 'PLUGIN_MAIN_FILE', __DIR__ . '/../../cyr-to-lat.php' );
define( 'PLUGIN_PATH', dirname( PLUGIN_MAIN_FILE ) );

require_once PLUGIN_PATH . '/vendor/autoload.php';

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', PLUGIN_PATH . '/../../' );
}

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

// Now call the bootstrap method of WP Mock.
\WP_Mock::bootstrap();
