<?php
/**
 * Test_Request class file
 *
 * @package cyr-to-lat
 */

// phpcs:disable PHPCompatibility.FunctionDeclarations.NewReturnTypeDeclarations.voidFound

namespace Cyr_To_Lat;

use Mockery;
use tad\FunctionMocker\FunctionMocker;
use WP_Mock;

/**
 * Class Test_Request
 *
 * @group request
 */
class Test_Request extends Cyr_To_Lat_TestCase {

	/**
	 * Tear down the test.
	 *
	 * @noinspection PhpLanguageLevelInspection
	 * @noinspection PhpUndefinedClassInspection
	 */
	public function tearDown(): void {
		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		unset( $_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD'], $GLOBALS['wp_rewrite'] );

		parent::tearDown();
	}

	/**
	 * Test is_frontend().
	 *
	 * @param bool $ajax     Is ajax.
	 * @param bool $admin    Is admin.
	 * @param bool $cli      Is CLI.
	 * @param bool $rest     Is REST.
	 * @param bool $expected Expected.
	 *
	 * @dataProvider dp_test_is_frontend
	 * @noinspection PhpUndefinedMethodInspection
	 */
	public function test_is_frontend( $ajax, $admin, $cli, $rest, $expected ) {
		WP_Mock::userFunction( 'wp_doing_ajax' )->with()->andReturn( $ajax );
		WP_Mock::userFunction( 'is_admin' )->with()->andReturn( $admin );

		$subject = Mockery::mock( Request::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'is_cli' )->with()->andReturn( $cli );
		$subject->shouldReceive( 'is_rest' )->with()->andReturn( $rest );

		self::assertSame( $expected, $subject->is_frontend() );
	}

	/**
	 * Data provider for test_is_frontend().
	 *
	 * @return array
	 */
	public function dp_test_is_frontend() {
		return [
			[ false, false, false, false, true ],
			[ false, false, false, true, false ],
			[ false, false, true, false, false ],
			[ false, false, true, true, false ],
			[ false, true, false, false, false ],
			[ false, true, false, true, false ],
			[ false, true, true, false, false ],
			[ false, true, true, true, false ],
			[ true, false, false, false, false ],
			[ true, false, false, true, false ],
			[ true, false, true, false, false ],
			[ true, false, true, true, false ],
			[ true, true, false, false, false ],
			[ true, true, false, true, false ],
			[ true, true, true, false, false ],
			[ true, true, true, true, false ],
		];
	}

	/**
	 * Test is_cli().
	 *
	 * @param bool $defined  Is constant WP_CLI defined.
	 * @param bool $constant Its value.
	 * @param bool $expected Expected.
	 *
	 * @dataProvider dp_test_is_cli
	 */
	public function test_is_cli( $defined, $constant, $expected ) {
		FunctionMocker::replace( 'defined', $defined );

		FunctionMocker::replace( 'constant', $constant );

		$subject = new Request();

		self::assertSame( $expected, $subject->is_cli() );
	}

	/**
	 * Data provider for test_is_cli().
	 *
	 * @return array
	 */
	public function dp_test_is_cli() {
		return [
			[ false, null, false ],
			[ true, false, false ],
			[ true, true, true ],
		];
	}

	/**
	 * Test is_rest() when no request_uri.
	 */
	public function test_is_rest_no_request_uri() {
		$subject = new Request();

		self::assertFalse( $subject->is_rest() );
	}

	/**
	 * Test is_rest(), case 1.
	 */
	public function test_is_rest_case_1() {
		$subject = new Request();

		$_SERVER['REQUEST_URI'] = '/wp-json/wp/v2/some-route';

		FunctionMocker::replace(
			'defined',
			function ( $constant_name ) {
				return 'REST_REQUEST' === $constant_name;
			}
		);

		FunctionMocker::replace(
			'constant',
			function ( $name ) {
				return 'REST_REQUEST' === $name;
			}
		);

		self::assertTrue( $subject->is_rest() );
	}

	/**
	 * Test is_rest(), case 2.
	 */
	public function test_is_rest_case_2() {
		$subject = new Request();

		$_SERVER['REQUEST_URI'] = '/wp-json/wp/v2/some-route';

		FunctionMocker::replace(
			'filter_input',
			function ( $type, $var_name, $filter ) {
				return (
					INPUT_GET === $type &&
					'rest_route' === $var_name &&
					FILTER_SANITIZE_STRING === $filter
				);
			}
		);

		self::assertTrue( $subject->is_rest() );
	}

	/**
	 * Test is_rest(), case 3 and 4.
	 *
	 * @noinspection PhpUndefinedMethodInspection
	 */
	public function test_is_rest_case_3_and_4() {
		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$GLOBALS['wp_rewrite'] = Mockery::mock( 'WP_Rewrite' );

		$rest_route             = '/wp/v2/posts';
		$_SERVER['REQUEST_URI'] = '/wp-json' . $rest_route;

		$subject = Mockery::mock( Request::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'get_rest_route' )->andReturnUsing(
			function () use ( &$rest_route ) {
				return $rest_route;
			}
		);

		self::assertTrue( $subject->is_rest() );

		$rest_route             = '';
		$_SERVER['REQUEST_URI'] = '/' . $rest_route;

		self::assertFalse( $subject->is_rest() );
	}

	/**
	 * Test get_rest_route().
	 *
	 * @param string $current_path Current path.
	 * @param string $expected     Expected.
	 *
	 * @dataProvider dp_test_get_rest_route
	 * @noinspection PhpUndefinedMethodInspection
	 */
	public function test_get_rest_route( $current_path, $expected ) {
		$current_url = 'https://test.test' . $current_path;

		$rest_path = '/wp-json';
		$rest_url  = 'https://test.test' . $rest_path . '/';

		WP_Mock::userFunction( 'add_query_arg' )->with( [] )->andReturn( $current_url );
		WP_Mock::userFunction( 'wp_parse_url' )->with( $current_url, PHP_URL_PATH )->andReturn( $current_path );

		WP_Mock::userFunction( 'rest_url' )->andReturn( $rest_url );
		WP_Mock::userFunction( 'trailingslashit' )->andReturnUsing(
			function ( $string ) {
				return rtrim( $string, '/' ) . '/';
			}
		);
		WP_Mock::userFunction( 'wp_parse_url' )->with( $rest_url, PHP_URL_PATH )->andReturn( $rest_path );

		$subject = Mockery::mock( Request::class )->makePartial();

		self::assertSame( $expected, $subject->get_rest_route() );
	}

	/**
	 * Data provider for it_gets_rest_route.
	 *
	 * @return array
	 */
	public function dp_test_get_rest_route() {
		return [
			'rest request' => [ '/wp-json/wp/v2/posts', '/wp/v2/posts' ],
			'some request' => [ '/some-request', '' ],
		];
	}
}