<?php
/**
 * Bootstrap file for Cyr-To-Lat WordPress integration tests.
 *
 * @package cyr-to-lat
 */

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpUndefinedFunctionInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

$project_root = dirname( __DIR__, 2 );

require_once $project_root . '/vendor/autoload.php';

$polyfills_autoload = $project_root . '/vendor/yoast/phpunit-polyfills/phpunitpolyfills-autoload.php';

if ( file_exists( $polyfills_autoload ) ) {
	require_once $polyfills_autoload;
}

$composer_tests_dir  = $project_root . '/vendor/wp-phpunit/wp-phpunit';
$test_dir            = getenv( 'WP_TESTS_DIR' ) ?: getenv( 'WP_PHPUNIT__DIR' );
$tests_dir           = file_exists( $composer_tests_dir . '/includes/functions.php' )
	? $composer_tests_dir
	: $test_dir;
$tests_config        = getenv( 'WP_PHPUNIT__TESTS_CONFIG' ) ?: getenv( 'WP_TESTS_CONFIG_FILE_PATH' );
$uses_wp_phpunit_dir = $tests_dir === $composer_tests_dir;

unset( $composer_tests_dir );

if ( ! $tests_config && file_exists( 'C:/tmp/cyr2lat-wp-tests-config.php' ) ) {
	$tests_config = 'C:/tmp/cyr2lat-wp-tests-config.php';
}

$tests_dir = rtrim( (string) $tests_dir, '/\\' );

if ( ! file_exists( $tests_dir . '/includes/functions.php' ) ) {
	echo 'Could not find WordPress PHPUnit test suite.' . PHP_EOL;
	echo 'Install wp-phpunit/wp-phpunit with Composer or set WP_TESTS_DIR.' . PHP_EOL;
	exit( 1 );
}

if ( $tests_config ) {
	// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv
	putenv( 'WP_PHPUNIT__TESTS_CONFIG=' . $tests_config );
} elseif ( $uses_wp_phpunit_dir ) {
	echo 'Could not find WordPress PHPUnit test configuration.' . PHP_EOL;
	echo 'Set WP_PHPUNIT__TESTS_CONFIG to a local wp-tests-config.php file.' . PHP_EOL;
	exit( 1 );
}

require_once $tests_dir . '/includes/functions.php';

/**
 * Load the plugin during WordPress test bootstrap.
 *
 * @return void
 * @noinspection PhpUnused
 */
function cyr2lat_load_plugin_for_integration_tests(): void {
	require dirname( __DIR__, 2 ) . '/cyr-to-lat.php';
}

tests_add_filter( 'muplugins_loaded', 'cyr2lat_load_plugin_for_integration_tests' );

require $tests_dir . '/includes/bootstrap.php';
