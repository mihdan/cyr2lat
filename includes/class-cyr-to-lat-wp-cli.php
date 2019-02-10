<?php
/**
 * WP-CLI support.
 *
 * @package cyr-to-lat
 * @link    https://github.com/mihdan/wp-rocket-cli/blob/master/command.php
 */

/**
 * Class Cyr_To_Lat_WP_CLI
 *
 * @class Cyr_To_Lat_WP_CLI
 */
class Cyr_To_Lat_WP_CLI extends WP_CLI_Command {

	/**
	 * Converter class.
	 *
	 * @var Cyr_To_Lat_Converter
	 */
	private $converter;

	/**
	 * Cyr_To_Lat_WP_CLI constructor.
	 *
	 * @param Cyr_To_Lat_Converter $converter Converter.
	 */
	public function __construct( Cyr_To_Lat_Converter $converter ) {
		parent::__construct();
		$this->converter = $converter;
	}

	/**
	 * Regenerate old slugs.
	 *
	 * ## EXAMPLES
	 *
	 *     $ wp cyr2lat regenerate
	 *     Success: Regenerate Completed.
	 *
	 * @subcommand regenerate
	 *
	 * @param array $args       Arguments.
	 * @param array $assoc_args Arguments in associative array.
	 */
	public function regenerate( $args = array(), $assoc_args = array() ) {

		/**
		 * Notify instance.
		 *
		 * @var \cli\progress\Bar $notify
		 */
		$notify = \WP_CLI\Utils\make_progress_bar( 'Regenerate old slugs', 1 );

		$this->converter->convert_existing_slugs();
		$notify->tick();
		$notify->finish();

		WP_CLI::success( 'Regenerate Completed.' );
	}
}
