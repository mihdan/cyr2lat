<?php
/**
 * Example local WordPress PHPUnit test config.
 *
 * Copy this file outside the repository, fill local environment variables,
 * and point WP_PHPUNIT__TESTS_CONFIG to that local copy.
 *
 * @package cyr-to-lat
 */

define( 'DB_NAME', getenv( 'CYR2LAT_TEST_DB_NAME' ) );
define( 'DB_USER', getenv( 'CYR2LAT_TEST_DB_USER' ) );
define( 'DB_PASSWORD', getenv( 'CYR2LAT_TEST_DB_PASSWORD' ) );
define( 'DB_HOST', getenv( 'CYR2LAT_TEST_DB_HOST' ) ?: 'localhost' );
define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', '' );

define( 'WP_TESTS_DOMAIN', getenv( 'CYR2LAT_TEST_DOMAIN' ) ?: 'example.org' );
define( 'WP_TESTS_EMAIL', getenv( 'CYR2LAT_TEST_EMAIL' ) ?: 'admin@example.org' );
define( 'WP_TESTS_TITLE', getenv( 'CYR2LAT_TEST_TITLE' ) ?: 'Cyr-To-Lat Tests' );
define( 'WP_PHP_BINARY', getenv( 'CYR2LAT_TEST_PHP_BINARY' ) ?: PHP_BINARY );

// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
$table_prefix = getenv( 'CYR2LAT_TEST_TABLE_PREFIX' ) ?: 'wptests_';

define( 'ABSPATH', getenv( 'CYR2LAT_TEST_WORDPRESS_DIR' ) );
