<?php
/**
 * WP_CLI stub file
 *
 * @package cyr-to-lat
 */

/**
 * Class WP_CLI
 */
class WP_CLI {

	/**
	 * @param string $message
	 */
	public static function success( $message ) {
	}

	/**
	 * @param string   $name     Name for the command (e.g. "post list" or "site empty").
	 * @param callable $callable Command implementation as a class, function or closure.
	 */
	public static function add_command( $name, $callable ) {
	}

}
