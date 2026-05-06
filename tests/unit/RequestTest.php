<?php
/**
 * RequestTest class file
 *
 * @package cyr-to-lat
 */

// phpcs:disable PHPCompatibility.FunctionDeclarations.NewReturnTypeDeclarations.voidFound

namespace CyrToLat\Tests\Unit;

use CyrToLat\Request;
use Mockery;
use ReflectionException;
use tad\FunctionMocker\FunctionMocker;
use WP_Mock;

/**
 * Class RequestTest
 *
 * @group request
 */
class RequestTest extends CyrToLatTestCase {

	/**
	 * Tear down the test.
	 */
	public function tearDown(): void {
		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		unset( $_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD'], $GLOBALS['wp_rewrite'] );

		parent::tearDown();
	}

	/**
	 * Test is_allowed().
	 *
	 * @param bool $frontend Is frontend.
	 * @param bool $post     Is POST.
	 * @param bool $cli      Is CLI.
	 * @param bool $expected Expected value.
	 *
	 * @return void
	 * @dataProvider dp_test_is_allowed
	 */
	public function test_is_allowed( bool $frontend, bool $post, bool $cli, bool $expected ): void {
		$subject = Mockery::mock( Request::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'is_frontend' )->with()->andReturn( $frontend );
		$subject->shouldReceive( 'is_post' )->with()->andReturn( $post );
		$subject->shouldReceive( 'is_cli' )->with()->andReturn( $cli );

		self::assertSame( $expected, $subject->is_allowed() );
	}

	/**
	 * Data provider for test_is_allowed().
	 *
	 * @return array
	 */
	public static function dp_test_is_allowed(): array {
		return [
			[ false, false, false, true ],
			[ false, false, true, true ],
			[ false, true, false, true ],
			[ false, true, true, true ],
			[ true, false, false, false ],
			[ true, false, true, true ],
			[ true, true, false, true ],
			[ true, true, true, true ],
		];
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
	 */
	public function test_is_frontend( bool $ajax, bool $admin, bool $cli, bool $rest, bool $expected ): void {
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
	public static function dp_test_is_frontend(): array {
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
	 * @param bool|null $defined  Is constant WP_CLI defined.
	 * @param bool|null $constant Its value.
	 * @param bool|null $expected Expected.
	 *
	 * @dataProvider dp_test_is_cli
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function test_is_cli( $defined, $constant, $expected ): void {
		FunctionMocker::replace( 'defined', $defined );

		FunctionMocker::replace( 'constant', $constant );
		FunctionMocker::replace( 'class_exists', static fn( $class_name, $autoload = true ) => 'WP_CLI' === $class_name );

		$subject = new Request();

		self::assertSame( $expected, $subject->is_cli() );
	}

	/**
	 * Data provider for test_is_cli().
	 *
	 * @return array
	 */
	public static function dp_test_is_cli(): array {
		return [
			[ false, null, false ],
			[ true, false, false ],
			[ true, true, true ],
		];
	}

	/**
	 * Test is_rest() when no request_uri.
	 */
	public function test_is_rest_no_request_uri(): void {
		$subject = new Request();

		self::assertFalse( $subject->is_rest() );
	}

	/**
	 * Test is_rest(), case 1.
	 */
	public function test_is_rest_case_1(): void {
		$subject = new Request();

		$_SERVER['REQUEST_URI'] = '/wp-json/wp/v2/some-route';

		FunctionMocker::replace(
			'defined',
			static function ( $constant_name ) {
				return 'REST_REQUEST' === $constant_name;
			}
		);

		FunctionMocker::replace(
			'constant',
			static function ( $name ) {
				return 'REST_REQUEST' === $name;
			}
		);

		self::assertTrue( $subject->is_rest() );
	}

	/**
	 * Test is_rest(), case 2.
	 */
	public function test_is_rest_case_2(): void {
		$subject = new Request();

		$_SERVER['REQUEST_URI'] = '/wp-json/wp/v2/some-route';
		$_SERVER['SCRIPT_NAME'] = '/index.php';
		$_GET['rest_route']     = '/wp/v2/posts';

		WP_Mock::userFunction( 'sanitize_text_field' )->andReturnArg( 0 );
		WP_Mock::passthruFunction( 'wp_unslash' );

		self::assertTrue( $subject->is_rest() );

		unset( $_GET['rest_route'], $_SERVER['SCRIPT_NAME'] );
	}

	/**
	 * Test is_rest(), case 3 and 4.
	 */
	public function test_is_rest_case_3_and_4(): void {
		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$GLOBALS['wp_rewrite'] = Mockery::mock( 'WP_Rewrite' );

		$_SERVER['REQUEST_URI'] = '/wp-json/wp/v2/posts';

		WP_Mock::userFunction( 'sanitize_text_field' )->andReturnArg( 0 );
		WP_Mock::passthruFunction( 'wp_unslash' );
		WP_Mock::userFunction( 'add_query_arg' )->with( [] )->andReturn( '/wp-json/wp/v2/posts' );
		WP_Mock::userFunction( 'wp_parse_url' )->with( '/wp-json/wp/v2/posts', PHP_URL_PATH )->andReturn( '/wp-json/wp/v2/posts' );
		WP_Mock::userFunction( 'rest_url' )->andReturn( 'https://test.test/wp-json/' );
		WP_Mock::userFunction( 'trailingslashit' )->andReturnUsing(
			function ( $str ) {
				return rtrim( $str, '/' ) . '/';
			}
		);
		WP_Mock::userFunction( 'wp_parse_url' )->with( 'https://test.test/wp-json/', PHP_URL_PATH )->andReturn( '/wp-json/' );

		$subject = new Request();

		self::assertTrue( $subject->is_rest() );
	}

	/**
	 * Test is_post().
	 */
	public function test_is_post(): void {
		WP_Mock::passthruFunction( 'wp_unslash' );

		$subject = new Request();
		self::assertFalse( $subject->is_post() );

		$_SERVER['REQUEST_METHOD'] = 'some';
		self::assertFalse( $subject->is_post() );

		$_SERVER['REQUEST_METHOD'] = 'POST';
		self::assertTrue( $subject->is_post() );
	}
}
