<?php
/**
 * WP-CLI support
 *
 * @package cyr-to-lat
 * @link https://github.com/mihdan/wp-rocket-cli/blob/master/command.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cyr_To_Lat_WP_CLI extends WP_CLI_Command {

	private $converter;

	public function __construct( Cyr_To_Lat_Converter $converter ) {
		$this->converter = $converter;
	}

	/**
	 * Regenerate old slugs.
	 *
	 * ## EXAMPLES
	 *
	 *     $ wp cli cyr2lat regenerate
	 *     Success: Regenerate Complete.
	 *
	 * @subcommand regenerate
	 */
	public function regenerate( $args = array(), $assoc_args = array() ) {

		$notify = \WP_CLI\Utils\make_progress_bar( 'Regenerate old slugs', 5 );

		for ( $i = 0; $i < 5; $i++ ) {
			sleep( rand( 1, 2 ) );
			$notify->tick();
		}
		$notify->finish();
		WP_CLI::success( 'Regenerate Complete' );
	}
}

WP_CLI::add_command( 'cyr2lat', 'Cyr_To_Lat_WP_CLI' );

// eof;
