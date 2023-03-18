<?php
/**
 * WP-CLI support.
 *
 * @package cyr-to-lat
 * @link    https://github.com/mihdan/wp-rocket-cli/blob/master/command.php
 */

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpUndefinedNamespaceInspection */
/** @noinspection PhpUndefinedClassInspection */
/** @noinspection PhpUndefinedFunctionInspection */
// phpcs:disable Generic.Commenting.DocComment.MissingShort

namespace Cyr_To_Lat;

use cli\progress\Bar;
use WP_CLI\NoOp;
use WP_CLI_Command;
use function WP_CLI\Utils\make_progress_bar;

/**
 * Class WP_CLI
 *
 * @class WP_CLI
 */
class WP_CLI extends WP_CLI_Command {

	/**
	 * Converter class.
	 *
	 * @var Converter
	 */
	private $converter;

	/**
	 * WP_CLI constructor.
	 *
	 * @param Converter $converter Converter.
	 */
	public function __construct( Converter $converter ) {
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
	 *
	 * @noinspection PhpUnusedParameterInspection
	 * @noinspection PhpUndefinedMethodInspection
	 */
	public function regenerate( $args = [], $assoc_args = [] ) {

		/**
		 * Notify instance.
		 *
		 * @var Bar $notify
		 */
		$notify = $this->make_progress_bar();

		$result = [];

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

		\WP_CLI::success( 'Regenerate Completed.' );
	}

	/**
	 * Make progress bar.
	 *
	 * @return Bar|NoOp
	 */
	public function make_progress_bar() {
		return make_progress_bar( 'Regenerate existing slugs', 1 );
	}
}
