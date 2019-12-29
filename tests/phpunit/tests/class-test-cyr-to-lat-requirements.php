<?php
/**
 * Test_Requirements class file
 *
 * @package cyr-to-lat
 */

namespace Cyr_To_Lat;

use Mockery;
use ReflectionClass;
use ReflectionException;
use tad\FunctionMocker\FunctionMocker;
use WP_Filesystem_Direct;
use WP_Mock;

/**
 * Class Test_Requirements
 *
 * @group requirements
 */
class Test_Requirements extends Cyr_To_Lat_TestCase {

	public function tearDown() {
		unset ( $_GET['activate'] );

		parent::tearDown();
	}

	/**
	 * Test constructor
	 *
	 * @throws ReflectionException Reflection Exception.
	 */
	public function test_constructor() {
		$classname = __NAMESPACE__ . '\Requirements';

		Mockery::mock( Admin_Notices::class );
		Mockery::mock( WP_Filesystem_Direct::class );

		FunctionMocker::replace(
			'function_exists',
			function ( $arg ) {
				return 'WP_Filesystem' === $arg;
			}
		);

		WP_Mock::userFunction( 'WP_Filesystem' )->andReturn( true );

		// Get mock, without the constructor being called.
		$mock = $this->getMockBuilder( $classname )->disableOriginalConstructor()->getMock();

		// Now call the constructor.
		$reflected_class = new ReflectionClass( $classname );
		$constructor     = $reflected_class->getConstructor();
		$constructor->invoke( $mock );
	}

	/**
	 * Test constructor
	 *
	 * @throws ReflectionException Reflection Exception.
	 */
	public function test_constructor_with_exception() {
		$classname = __NAMESPACE__ . '\Requirements';

		Mockery::mock( Admin_Notices::class );
		Mockery::mock( WP_Filesystem_Direct::class );

		FunctionMocker::replace(
			'function_exists',
			function ( $arg ) {
				return 'WP_Filesystem' === $arg;
			}
		);

		WP_Mock::userFunction( 'WP_Filesystem' )->andReturn( false );

		// Get mock, without the constructor being called.
		$mock = $this->getMockBuilder( $classname )->disableOriginalConstructor()->getMock();

		// Now call the constructor.
		$reflected_class = new ReflectionClass( $classname );
		$constructor     = $reflected_class->getConstructor();
		$constructor->invoke( $mock );
	}

	/**
	 * Test if are_requirements_met() returns true when requirements met.
	 */
	public function test_requirements_met() {
		$admin_notices = Mockery::mock( 'Admin_Notices' );
		$wp_filesystem = Mockery::mock( 'WP_Filesystem_Direct' );

		FunctionMocker::replace(
			'function_exists',
			function ( $arg ) {
				return 'WP_Filesystem' === $arg;
			}
		);

		WP_Mock::userFunction( 'WP_Filesystem' )->andReturn( true );

		FunctionMocker::replace(
			'phpversion',
			CYR_TO_LAT_MINIMUM_PHP_REQUIRED_VERSION
		);

		FunctionMocker::replace(
			'ini_get',
			function ( $arg ) {
				switch ( $arg ) {
					case 'max_input_vars':
						return CYR_TO_LAT_REQUIRED_MAX_INPUT_VARS;
					case 'user_ini.cache_ttl':
						return 300;
					default:
						return null;
				}
			}
		);

		$subject = new Requirements( $admin_notices, $wp_filesystem );

		WP_Mock::expectActionNotAdded( 'admin_init', [ $subject, 'deactivate_plugin' ] );

		$this->assertTrue( $subject->are_requirements_met() );
	}

	/**
	 * Test if are_requirements_met() returns false when php requirements not met.
	 */
	public function test_php_requirements_not_met() {
		$admin_notices = Mockery::mock( 'Admin_Notices' );
		$wp_filesystem = Mockery::mock( 'WP_Filesystem_Direct' );

		FunctionMocker::replace(
			'function_exists',
			function ( $arg ) {
				return 'WP_Filesystem' === $arg;
			}
		);

		WP_Mock::userFunction( 'WP_Filesystem' )->andReturn( true );

		$required_version = explode( '.', CYR_TO_LAT_MINIMUM_PHP_REQUIRED_VERSION );
		$wrong_version    = array_slice( $required_version, 0, 2 );
		$wrong_version    = (float) implode( '.', $wrong_version );
		$wrong_version    = $wrong_version - 0.1;
		$wrong_version    = number_format( $wrong_version, 1, '.', '' );

		FunctionMocker::replace(
			'phpversion',
			$wrong_version
		);

		FunctionMocker::replace(
			'ini_get',
			function ( $arg ) {
				switch ( $arg ) {
					case 'max_input_vars':
						return CYR_TO_LAT_REQUIRED_MAX_INPUT_VARS;
					case 'user_ini.cache_ttl':
						return 300;
					default:
						return null;
				}
			}
		);

		$admin_notices->shouldReceive( 'add_notice' )
		              ->with( 'Cyr To Lat plugin has been deactivated.', 'notice notice-info is-dismissible' );
		$admin_notices->shouldReceive( 'add_notice' )
		              ->with( 'Your server is running PHP version ' . $wrong_version . ' but Cyr To Lat ' . CYR_TO_LAT_VERSION . ' requires at least ' . CYR_TO_LAT_MINIMUM_PHP_REQUIRED_VERSION . '.', 'notice notice-error' );

		$subject = new Requirements( $admin_notices, $wp_filesystem );

		WP_Mock::expectActionAdded( 'admin_init', [ $subject, 'deactivate_plugin' ] );

		$this->assertFalse( $subject->are_requirements_met() );
	}

	/**
	 * Test if are_requirements_met() returns false when php requirements not met.
	 *
	 * @param $within_timeout
	 * @param $content
	 * @param $expected
	 *
	 * @dataProvider dp_test_vars_requirements_not_met
	 */
	public function test_vars_requirements_not_met( $within_timeout, $content, $expected ) {
		$this->markTestSkipped( 'Temporary skipped for 4.3.2' );

		$max_input_vars              = CYR_TO_LAT_REQUIRED_MAX_INPUT_VARS - 1;
		$user_ini_filename           = '.user.ini';
		$user_ini_filename_with_path = ABSPATH . 'wp-admin/' . $user_ini_filename;
		$ini_ttl                     = 300;
		$time                        = time();
		if ( $within_timeout ) {
			$mtime = $time - $ini_ttl + 1;
		} else {
			$mtime = $time - $ini_ttl - 1;
		}
		$time_left = ( $mtime + $ini_ttl ) - $time;

		$message = 'Your server is running PHP with max_input_vars=' . $max_input_vars . ' but Cyr To Lat ' . CYR_TO_LAT_VERSION . ' requires at least ' . CYR_TO_LAT_REQUIRED_MAX_INPUT_VARS . '.';

		$message .= '<br>';
		$message .= 'We have updated settings in ' . $user_ini_filename_with_path . '.';
		$message .= '<br>';
		if ( 0 < $time_left ) {
			$message .= 'Please try again in ' . $time_left . ' s.';
		} else {
			$message .= 'Please try again.';
		}

		$admin_notices = Mockery::mock( 'Admin_Notices' );
		$admin_notices->shouldReceive( 'add_notice' )->with( $message, 'notice notice-error' );

		$wp_filesystem = Mockery::mock( 'WP_Filesystem_Direct' );
		$wp_filesystem->shouldReceive( 'mtime' )->with( $user_ini_filename_with_path )->andReturn( $mtime );
		$wp_filesystem->shouldReceive( 'get_contents' )->with( $user_ini_filename_with_path )->andReturn( $content );
		$wp_filesystem->shouldReceive( 'put_contents' )->with( $user_ini_filename_with_path, $expected );

		FunctionMocker::replace(
			'function_exists',
			function ( $arg ) {
				return 'WP_Filesystem' === $arg;
			}
		);

		FunctionMocker::replace(
			'realpath',
			function ( $arg ) {
				return $arg;
			}
		);

		FunctionMocker::replace(
			'time',
			function () use ( $time ) {
				return $time;
			}
		);

		WP_Mock::userFunction( 'WP_Filesystem' )->andReturn( true );

		FunctionMocker::replace(
			'phpversion',
			CYR_TO_LAT_MINIMUM_PHP_REQUIRED_VERSION
		);

		FunctionMocker::replace(
			'ini_get',
			function ( $arg ) use ( $max_input_vars, $user_ini_filename, $ini_ttl ) {
				switch ( $arg ) {
					case 'max_input_vars':
						return $max_input_vars;
					case 'user_ini.cache_ttl':
						return $ini_ttl;
					case 'user_ini.filename':
						return $user_ini_filename;
					default:
						return null;
				}
			}

		);

		$subject = new Requirements( $admin_notices, $wp_filesystem );

		WP_Mock::expectActionNotAdded( 'admin_init', [ $subject, 'deactivate_plugin' ] );

		$this->assertFalse( $subject->are_requirements_met() );
	}

	/**
	 * Data provider for test_vars_requirements_not_met.
	 *
	 * @return array
	 */
	public function dp_test_vars_requirements_not_met() {
		$expected_line = 'max_input_vars = ' . CYR_TO_LAT_REQUIRED_MAX_INPUT_VARS;

		return [
			'within timeout' => [
				true,
				'',
				$expected_line,
			],
			'after timeout'  => [ false, '', $expected_line ],
			'some content'   => [
				false,
				"\nline 1\r\nline 2\n",
				PHP_EOL . 'line 1' . PHP_EOL . 'line 2' . PHP_EOL . PHP_EOL . $expected_line,
			],
			'commented out'  => [ false, ';max_input_vars = 17000', $expected_line ],
			'take last only' => [
				false,
				"line 1\nmax_input_vars=17000\nmax_input_vars=4000\n ; max_input_vars = 27000\nlast line",
				'line 1' . PHP_EOL . 'last line' . PHP_EOL . $expected_line,
			],
			'no changes'     => [
				false,
				'max_input_vars=17000',
				'max_input_vars=17000',
			],
		];
	}

	public function test_deactivate_plugin() {
		$admin_notices = Mockery::mock( 'Admin_Notices' );
		$admin_notices->shouldReceive( 'add_notice' )
		              ->with( 'Cyr To Lat plugin has been deactivated.', 'notice notice-info is-dismissible' );

		$wp_filesystem = Mockery::mock( 'WP_Filesystem_Direct' );

		FunctionMocker::replace(
			'function_exists',
			function ( $arg ) {
				return 'WP_Filesystem' === $arg;
			}
		);

		WP_Mock::userFunction( 'WP_Filesystem' )->andReturn( true );

		WP_Mock::passthruFunction( 'plugin_basename' );
		WP_Mock::userFunction( 'is_plugin_active' )->with( CYR_TO_LAT_FILE )->andReturn( true );
		WP_Mock::userFunction( 'deactivate_plugins' )->with( CYR_TO_LAT_FILE );

		$_GET['activate'] = 'some value';

		$subject = new Requirements( $admin_notices, $wp_filesystem );
		$subject->deactivate_plugin();

		self::assertArrayNotHasKey( 'activate', $_GET );
	}

	public function test_deactivate_plugin_when_it_is_not_active() {
		$admin_notices = Mockery::mock( 'Admin_Notices' );
		$wp_filesystem = Mockery::mock( 'WP_Filesystem_Direct' );

		FunctionMocker::replace(
			'function_exists',
			function ( $arg ) {
				return 'WP_Filesystem' === $arg;
			}
		);

		WP_Mock::userFunction( 'WP_Filesystem' )->andReturn( true );

		WP_Mock::passthruFunction( 'plugin_basename' );
		WP_Mock::userFunction( 'is_plugin_active' )->with( CYR_TO_LAT_FILE )->andReturn( false );

		$subject = new Requirements( $admin_notices, $wp_filesystem );
		$subject->deactivate_plugin();
	}
}
