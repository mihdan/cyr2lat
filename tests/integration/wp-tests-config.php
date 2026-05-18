<?php
/**
 * WordPress PHPUnit test config for Cyr-To-Lat integration tests.
 *
 * @package cyr-to-lat
 */

$params = require __DIR__ . '/_config/params.php';

define( 'DB_NAME', $params['DB_NAME'] );
define( 'DB_USER', $params['DB_USER'] );
define( 'DB_PASSWORD', $params['DB_PASSWORD'] );
define( 'DB_HOST', $params['DB_HOST'] );
define( 'DB_CHARSET', $params['DB_CHARSET'] ?? 'utf8' );
define( 'DB_COLLATE', $params['DB_COLLATE'] ?? '' );

define( 'WP_TESTS_DOMAIN', $params['WP_URL'] );
define( 'WP_TESTS_EMAIL', $params['WP_EMAIL'] ?? 'admin@example.org' );
define( 'WP_TESTS_TITLE', $params['WP_TITLE'] ?? 'Cyr-To-Lat Tests' );
define( 'WP_PHP_BINARY', $params['WP_PHP_BINARY'] ?? PHP_BINARY );

// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
$table_prefix = $params['DB_TABLE_PREFIX'];

define( 'ABSPATH', rtrim( $params['WP_ROOT_PATH'], '/\\' ) . '/' );
