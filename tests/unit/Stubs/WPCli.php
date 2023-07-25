<?php
/**
 * WP_CLI stub file
 *
 * @package cyr-to-lat
 */

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpUndefinedClassInspection */
/** @noinspection PhpIllegalPsrClassPathInspection */
/** @noinspection AutoloadingIssuesInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

/**
 * Class WP_CLI
 */
class WP_CLI {

	/**
	 * Success function.
	 *
	 * @param string $message Message.
	 */
	public static function success( string $message ) {
	}

	/**
	 * Add command.
	 *
	 * @param string        $name     Name for the command (e.g. "post list" or "site empty").
	 * @param callable|null $callable Command implementation as a class, function or closure.
	 */
	public static function add_command( string $name, $callable ) {
	}
}
