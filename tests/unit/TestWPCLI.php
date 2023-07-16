<?php
/**
 * Test_WP_CLI class file
 *
 * @package cyr-to-lat
 */

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpUndefinedClassInspection */
/** @noinspection PhpUndefinedNamespaceInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

namespace CyrToLat\Tests\Unit;

use cli\progress\Bar;
use Cyr_To_Lat\Converter;
use Cyr_To_Lat\WP_CLI;
use Mockery;
use tad\FunctionMocker\FunctionMocker;
use WP_Mock;

/**
 * Class Test_WP_CLI
 *
 * @group wp-cli
 */
class TestWPCLI extends CyrToLatTestCase {

	/**
	 * Test regenerate()
	 *
	 * @param array $args           Arguments.
	 * @param array $assoc_args     Arguments in associative array.
	 * @param array $convert_params Params for conversion of existing slugs.
	 *
	 * @dataProvider        dp_test_regenerate
	 * @noinspection        PhpRedundantOptionalArgumentInspection
	 */
	public function test_regenerate( $args, $assoc_args, $convert_params ) {
		$converter = Mockery::mock( Converter::class );

		$subject = Mockery::mock( WP_CLI::class, [ $converter ] )->makePartial()->shouldAllowMockingProtectedMethods();

		$notify = Mockery::mock( Bar::class );
		$notify->shouldReceive( 'tick' );
		$notify->shouldReceive( 'finish' );

		$subject->shouldReceive( 'make_progress_bar' )->andReturn( $notify );

		$converter->shouldReceive( 'convert_existing_slugs' )->with( $convert_params );

		$success = FunctionMocker::replace( '\WP_CLI::success', null );

		$subject->regenerate( $args, $assoc_args );

		$success->wasCalledWithOnce( [ 'Regenerate Completed.' ] );
	}

	/**
	 * Data provider for test_regenerate()
	 */
	public function dp_test_regenerate() {
		return [
			[ [], [], [] ],
			[
				[],
				[
					'post_status' => 'status1,status2',
					'post_type'   => 'type1,type2',
				],
				[
					'post_status' => [ 'status1', 'status2' ],
					'post_type'   => [ 'type1', 'type2' ],
				],
			],
			[
				[],
				[
					'post_status' => 'status1, ,, status2',
					'post_type'   => 'type1,type2',
				],
				[
					'post_status' => [ 'status1', 'status2' ],
					'post_type'   => [ 'type1', 'type2' ],
				],
			],
		];
	}

	/**
	 * Test make_progress_bar()
	 */
	public function test_make_progress_bar() {
		$converter = Mockery::mock( Converter::class );

		$subject = Mockery::mock( WP_CLI::class, [ $converter ] )->makePartial()->shouldAllowMockingProtectedMethods();

		$notify = Mockery::Mock( Bar::class );

		WP_Mock::userFunction( 'WP_CLI\Utils\make_progress_bar' )->with( 'Regenerate existing slugs', 1 )->once()
			->andReturn( $notify );

		self::assertSame( $notify, $subject->make_progress_bar() );
	}
}
