<?php
/**
 * GitHub Actions integration test parameters.
 *
 * @package cyr-to-lat
 */

return [
	'WP_URL'          => 'test.test',
	'WP_ROOT_PATH'    => getenv( 'CYR2LAT_TEST_WORDPRESS_DIR' ) ?: dirname( __DIR__, 6 ),
	'DB_HOST'         => getenv( 'CYR2LAT_TEST_DB_HOST' ) ?: 'localhost',
	'DB_NAME'         => getenv( 'CYR2LAT_TEST_DB_NAME' ) ?: 'wp_tests',
	'DB_USER'         => getenv( 'CYR2LAT_TEST_DB_USER' ) ?: 'root',
	'DB_PASSWORD'     => getenv( 'CYR2LAT_TEST_DB_PASSWORD' ) ?: 'root',
	'DB_TABLE_PREFIX' => getenv( 'CYR2LAT_TEST_TABLE_PREFIX' ) ?: 'wptests_',
];
