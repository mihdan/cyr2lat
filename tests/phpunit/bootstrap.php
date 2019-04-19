<?php
define( 'PLUGIN_TESTS_DIR', __DIR__ );

define( 'PLUGIN_MAIN_FILE', __DIR__ . '/../../cyr-to-lat.php' );
define( 'PLUGIN_PATH', dirname( PLUGIN_MAIN_FILE ) );

require_once PLUGIN_PATH . '/vendor/autoload.php';

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', PLUGIN_PATH . '/../../' );
}

// Now call the bootstrap method of WP Mock.
\WP_Mock::bootstrap();
