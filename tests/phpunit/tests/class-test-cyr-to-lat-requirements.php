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
	 * Test constructor when no WP_Filesystem is available
	 *
	 * @throws ReflectionException Reflection Exception.
	 */
	public function test_constructor_with_exception() {
		$classname = __NAMESPACE__ . '\Requirements';

		Mockery::mock( Admin_Notices::class );

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
			$this->cyr_to_lat_minimum_php_required_version
		);

		FunctionMocker::replace(
			'ini_get',
			function ( $arg ) {
				switch ( $arg ) {
					case 'max_input_vars':
						return $this->cyr_to_lat_required_max_input_vars;
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

		$required_version = explode( '.', $this->cyr_to_lat_minimum_php_required_version );
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
						return $this->cyr_to_lat_required_max_input_vars;
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
		              ->with( 'Your server is running PHP version ' . $wrong_version . ' but Cyr To Lat ' . $this->cyr_to_lat_version . ' requires at least ' . $this->cyr_to_lat_minimum_php_required_version . '.', 'notice notice-error' );

		$subject = new Requirements( $admin_notices, $wp_filesystem );

		WP_Mock::expectActionAdded( 'admin_init', [ $subject, 'deactivate_plugin' ] );

		$this->assertFalse( $subject->are_requirements_met() );
	}

	/**
	 * Test are_requirements_met() when max_input_vars requirements not met.
	 *
	 * @param $within_timeout
	 * @param $content
	 * @param $expected
	 *
	 * @dataProvider dp_test_vars_requirements_not_met
	 */
	public function test_vars_requirements_not_met( $within_timeout, $content, $expected ) {
		$max_input_vars              = $this->cyr_to_lat_required_max_input_vars - 1;
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

		$admin_notices = Mockery::mock( 'Admin_Notices' );

		$cyr2lat_page = [ 'page' => Settings::SCREEN_ID ];

		if ( 0 < $time_left ) {
			$message = 'Your server is running PHP with max_input_vars=' . $max_input_vars . ' but Cyr To Lat ' . $this->cyr_to_lat_version . ' requires at least ' . $this->cyr_to_lat_required_max_input_vars . '.';

			$message .= '<br>';
			$message .= 'We have updated settings in ' . $user_ini_filename_with_path . '.';
			$message .= '<br>';
			$message .= 'Please try again in ' . $time_left . ' s.';
		} else {
			$message = 'Please increase max input vars limit up to 1500.';

			$message .= '<br>';
			$message .= 'See: <a href="http://sevenspark.com/docs/ubermenu-3/faqs/menu-item-limit" target="_blank">Increasing max input vars limit.</a>';
		}

		$admin_notices->shouldReceive( 'add_notice' )->with( $message, 'notice notice-error', $cyr2lat_page );

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
			$this->cyr_to_lat_minimum_php_required_version
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

		$this->assertTrue( $subject->are_requirements_met() );
	}

	/**
	 * Data provider for test_vars_requirements_not_met.
	 *
	 * @return array
	 */
	public function dp_test_vars_requirements_not_met() {
		$expected_line = 'max_input_vars = ' . 1000;

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

	/**
	 * Test are_requirements_met() when max_input_vars requirements not met and filesystem not available.
	 */
	public function test_vars_requirements_not_met_and_filesystem_not_available() {
		$max_input_vars              = $this->cyr_to_lat_required_max_input_vars - 1;
		$user_ini_filename           = '.user.ini';
		$user_ini_filename_with_path = ABSPATH . 'wp-admin/' . $user_ini_filename;
		$ini_ttl                     = 300;

		$admin_notices = Mockery::mock( 'Admin_Notices' );

		FunctionMocker::replace(
			'function_exists',
			function ( $arg ) {
				return 'WP_Filesystem' === $arg;
			}
		);

		WP_Mock::userFunction( 'WP_Filesystem' )->andReturn( false );

		FunctionMocker::replace(
			'phpversion',
			$this->cyr_to_lat_minimum_php_required_version
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

		$message      = 'Unable to get filesystem access.';
		$cyr2lat_page = [ 'page' => Settings::SCREEN_ID ];

		$admin_notices->shouldReceive( 'add_notice' )->with( $message, 'notice notice-error', $cyr2lat_page );

		$message = 'Please increase max input vars limit up to 1500.';

		$message .= '<br>';
		$message .= 'See: <a href="http://sevenspark.com/docs/ubermenu-3/faqs/menu-item-limit" target="_blank">Increasing max input vars limit.</a>';

		$admin_notices->shouldReceive( 'add_notice' )->with( $message, 'notice notice-error', $cyr2lat_page );

		$subject = new Requirements( $admin_notices, null );

		WP_Mock::expectActionNotAdded( 'admin_init', [ $subject, 'deactivate_plugin' ] );

		$this->assertTrue( $subject->are_requirements_met() );
	}

	/**
	 * Test deactivate_plugin()
	 */
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
		WP_Mock::userFunction( 'is_plugin_active' )->with( $this->cyr_to_lat_file )->andReturn( true );
		WP_Mock::userFunction( 'deactivate_plugins' )->with( $this->cyr_to_lat_file );

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
		WP_Mock::userFunction( 'is_plugin_active' )->with( $this->cyr_to_lat_file )->andReturn( false );

		$subject = new Requirements( $admin_notices, $wp_filesystem );
		$subject->deactivate_plugin();
	}
}
