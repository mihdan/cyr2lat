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
	 *
	 * @noinspection ReturnTypeCanBeDeclaredInspection
	 */
	public static function success( string $message ) {
	}

	/**
	 * Add command.
	 *
	 * @param string        $name     Name for the command (e.g. "post list" or "site empty").
	 * @param callable|null $callback Command implementation as a class, function or closure.
	 *
	 * @noinspection ReturnTypeCanBeDeclaredInspection
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public static function add_command( string $name, $callback ) {
	}
}
