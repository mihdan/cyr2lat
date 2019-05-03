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
		$notify = $this->make_progress_bar();

		$result = array();

		if ( ! empty( $assoc_args['post_status'] ) ) {
			$result['post_status'] = explode( ',', $assoc_args['post_status'] );
			$result['post_status'] = array_values( array_filter( array_map( 'trim', $result['post_status'] ) ) );
		}

		if ( ! empty( $assoc_args['post_type'] ) ) {
			$result['post_type'] = explode( ',', $assoc_args['post_type'] );
			$result['post_type'] = array_values( array_filter( array_map( 'trim', $result['post_type'] ) ) );
		}

		$this->converter->convert_existing_slugs( $result );
		$notify->tick();
		$notify->finish();

		WP_CLI::success( 'Regenerate Completed.' );
	}

	/**
	 * Make progress bar.
	 *
	 * @return \cli\progress\Bar|\WP_CLI\NoOp
	 */
	protected function make_progress_bar() {
		return \WP_CLI\Utils\make_progress_bar( 'Regenerate existing slugs', 1 );
	}
}
