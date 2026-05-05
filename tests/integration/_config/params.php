<?php
/**
 * Load integration test configuration parameters.
 *
 * @package cyr-to-lat
 */

global $argv;

$config = getenv( 'CYR2LAT_TEST_PARAMS' );

if ( $config && file_exists( $config ) ) {
	return include $config;
}

$config = __DIR__ . '/params.github-actions.php';

if ( ( getenv( 'GITHUB_ACTIONS' ) || in_array( 'github-actions', $argv, true ) ) && file_exists( $config ) ) {
	return include $config;
}

$config = __DIR__ . '/params.local.php';

if ( file_exists( $config ) ) {
	return include $config;
}

die( "No valid integration test config provided.\nPlease use 'params.example.php' as a template to create 'params.local.php'.\n" );
