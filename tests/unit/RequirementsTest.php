<?php
/**
 * RequirementsTest class file
 *
 * @package cyr-to-lat
 */

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpUndefinedClassInspection */
/** @noinspection PhpArrayShapeAttributeCanBeAddedInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

// phpcs:disable PHPCompatibility.FunctionDeclarations.NewReturnTypeDeclarations.voidFound

namespace CyrToLat\Tests\Unit;

use Cyr_To_Lat\Requirements;
use Cyr_To_Lat\Settings\Settings;
use Cyr_To_Lat\Admin_Notices;
use Mockery;
use ReflectionClass;
use ReflectionException;
use tad\FunctionMocker\FunctionMocker;
use WP_Filesystem_Direct;
use WP_Mock;

/**
 * Class RequirementsTest
 *
 * @group requirements
 */
class RequirementsTest extends CyrToLatTestCase {

	/**
	 * Tear down.
	 *
	 * @noinspection PhpLanguageLevelInspection
	 */
	public function tearDown(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		unset( $_GET );

		parent::tearDown();
	}

	/**
	 * Test constructor
	 *
	 * @throws ReflectionException Reflection Exception.
	 */
	public function test_constructor() {
		$classname = Requirements::class;

		$settings = Mockery::mock( Settings::class );
		$ids      = [ 'settings_page_cyr-to-lat' ];
		$settings->shouldReceive( 'screen_ids' )->with()->andReturn( $ids );
		$screen_ids = [ 'screen_ids' => $ids ];

		$admin_notices = Mockery::mock( Admin_Notices::class );
		$wp_filesystem = Mockery::mock( WP_Filesystem_Direct::class );

		FunctionMocker::replace(
			'function_exists',
			static function ( $arg ) {
				return 'WP_Filesystem' === $arg;
			}
		);

		WP_Mock::userFunction( 'WP_Filesystem' )->andReturn( true );

		// Get mock, without the constructor being called.
		$mock = $this->getMockBuilder( $classname )->disableOriginalConstructor()->getMock();

		// Now call the constructor.
		$reflected_class = new ReflectionClass( $classname );
		$constructor     = $reflected_class->getConstructor();
		$constructor->invoke( $mock, $settings, $admin_notices, $wp_filesystem );

		self::assertSame( $settings, $this->get_protected_property( $mock, 'settings' ) );
		self::assertSame( $screen_ids, $this->get_protected_property( $mock, 'screen_ids' ) );
		self::assertSame( $admin_notices, $this->get_protected_property( $mock, 'admin_notices' ) );
		self::assertSame( $wp_filesystem, $this->get_protected_property( $mock, 'wp_filesystem' ) );

		// Get mock, without the constructor being called.
		$mock = $this->getMockBuilder( $classname )->disableOriginalConstructor()->getMock();

		// Now call the constructor.
		$reflected_class = new ReflectionClass( $classname );
		$constructor     = $reflected_class->getConstructor();
		$constructor->invoke( $mock, $settings, $admin_notices );

		self::assertSame( $settings, $this->get_protected_property( $mock, 'settings' ) );
		self::assertSame( $screen_ids, $this->get_protected_property( $mock, 'screen_ids' ) );
		self::assertSame( $admin_notices, $this->get_protected_property( $mock, 'admin_notices' ) );
		self::assertInstanceOf( WP_Filesystem_Direct::class, $this->get_protected_property( $mock, 'wp_filesystem' ) );
	}

	/**
	 * Test constructor when no WP_Filesystem is available
	 *
	 * @throws ReflectionException Reflection Exception.
	 */
	public function test_constructor_when_NO_wp_filesystem_is_available() {
		$classname = Requirements::class;

		$settings = Mockery::mock( Settings::class );
		$ids      = [ 'settings_page_cyr-to-lat' ];
		$settings->shouldReceive( 'screen_ids' )->with()->andReturn( $ids );
		$screen_ids = [ 'screen_ids' => $ids ];

		$admin_notices = Mockery::mock( Admin_Notices::class );

		FunctionMocker::replace(
			'function_exists',
			static function ( $arg ) {
				return 'WP_Filesystem' === $arg;
			}
		);

		WP_Mock::userFunction( 'WP_Filesystem' )->andReturn( false );

		// Get mock, without the constructor being called.
		$mock = $this->getMockBuilder( $classname )->disableOriginalConstructor()->getMock();

		// Now call the constructor.
		$reflected_class = new ReflectionClass( $classname );
		$constructor     = $reflected_class->getConstructor();
		$constructor->invoke( $mock, $settings, $admin_notices );

		self::assertSame( $settings, $this->get_protected_property( $mock, 'settings' ) );
		self::assertSame( $screen_ids, $this->get_protected_property( $mock, 'screen_ids' ) );
		self::assertSame( $admin_notices, $this->get_protected_property( $mock, 'admin_notices' ) );
		self::assertNull( $this->get_protected_property( $mock, 'wp_filesystem' ) );
	}

	/**
	 * Test if are_requirements_met() returns true when requirements met.
	 */
	public function test_requirements_met() {
		$settings = Mockery::mock( Settings::class );
		$settings->shouldReceive( 'screen_ids' )->with()->andReturn( [ 'settings_page_cyr-to-lat' ] );

		$admin_notices = Mockery::mock( Admin_Notices::class );
		$wp_filesystem = Mockery::mock( WP_Filesystem_Direct::class );

		FunctionMocker::replace(
			'function_exists',
			static function ( $arg ) {
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

		$subject = new Requirements( $settings, $admin_notices, $wp_filesystem );

		WP_Mock::expectActionNotAdded( 'admin_init', [ $subject, 'deactivate_plugin' ] );

		self::assertTrue( $subject->are_requirements_met() );
	}

	/**
	 * Test if are_requirements_met() returns false when php requirements not met.
	 */
	public function test_php_requirements_not_met() {
		$settings = Mockery::mock( Settings::class );
		$settings->shouldReceive( 'screen_ids' )->with()->andReturn( [ 'settings_page_cyr-to-lat' ] );

		$admin_notices = Mockery::mock( Admin_Notices::class );
		$wp_filesystem = Mockery::mock( WP_Filesystem_Direct::class );

		FunctionMocker::replace(
			'function_exists',
			static function ( $arg ) {
				return 'WP_Filesystem' === $arg;
			}
		);

		WP_Mock::userFunction( 'WP_Filesystem' )->andReturn( true );

		$required_version = explode( '.', $this->cyr_to_lat_minimum_php_required_version );
		$wrong_version    = array_slice( $required_version, 0, 2 );
		$wrong_version    = (float) implode( '.', $wrong_version );

		$wrong_version -= 0.1;

		$wrong_version = number_format( $wrong_version, 1, '.', '' );

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

		$admin_notices
			->shouldReceive( 'add_notice' )
			->with( 'Cyr To Lat plugin has been deactivated.', 'notice notice-info is-dismissible' );
		$admin_notices
			->shouldReceive( 'add_notice' )
			->with( 'Your server is running PHP version ' . $wrong_version . ' but Cyr To Lat ' . $this->cyr_to_lat_version . ' requires at least ' . $this->cyr_to_lat_minimum_php_required_version . '.', 'notice notice-error' );

		$subject = new Requirements( $settings, $admin_notices, $wp_filesystem );

		WP_Mock::expectActionAdded( 'admin_init', [ $subject, 'deactivate_plugin' ] );

		self::assertFalse( $subject->are_requirements_met() );
	}

	/**
	 * Test are_requirements_met() when max_input_vars requirements not met.
	 *
	 * @param bool   $within_timeout Within timeout.
	 * @param string $content        Content of init file.
	 * @param string $expected       Expected result.
	 *
	 * @dataProvider dp_test_vars_requirements_not_met
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_vars_requirements_not_met( bool $within_timeout, string $content, string $expected ) {
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

		$settings = Mockery::mock( Settings::class );
		$ids      = [ 'settings_page_cyr-to-lat' ];
		$settings->shouldReceive( 'screen_ids' )->with()->andReturn( $ids );

		$admin_notices = Mockery::mock( Admin_Notices::class );

		$screen_ids = [ 'screen_ids' => $ids ];

		if ( 0 < $time_left ) {
			$message = 'Your server is running PHP with max_input_vars=' . $max_input_vars . ' but Cyr To Lat ' . $this->cyr_to_lat_version . ' requires at least ' . $this->cyr_to_lat_required_max_input_vars . '.';

			$message .= '<br>';
			$message .= 'We have updated settings in ' . $user_ini_filename_with_path . '.';
			$message .= '<br>';
			$message .= 'Please try again in ' . $time_left . ' s.';
		} else {
			$message = 'Please increase max input vars limit up to 1500.';

			$message .= '<br>';
			$message .= 'See: <a href="https://sevenspark.com/docs/ubermenu-3/faqs/menu-item-limit" target="_blank">Increasing max input vars limit.</a>';
		}

		$admin_notices->shouldReceive( 'add_notice' )->with( $message, 'notice notice-error', $screen_ids );

		$wp_filesystem = Mockery::mock( WP_Filesystem_Direct::class );
		$wp_filesystem->shouldReceive( 'mtime' )->with( $user_ini_filename_with_path )->andReturn( $mtime );
		$wp_filesystem->shouldReceive( 'get_contents' )->with( $user_ini_filename_with_path )->andReturn( $content );
		$wp_filesystem->shouldReceive( 'put_contents' )->with( $user_ini_filename_with_path, $expected );

		FunctionMocker::replace(
			'function_exists',
			static function ( $arg ) {
				return 'WP_Filesystem' === $arg;
			}
		);

		FunctionMocker::replace(
			'realpath',
			static function ( $arg ) {
				return $arg;
			}
		);

		FunctionMocker::replace(
			'time',
			static function () use ( $time ) {
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
			static function ( $arg ) use ( $max_input_vars, $user_ini_filename, $ini_ttl ) {
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

		$subject = new Requirements( $settings, $admin_notices, $wp_filesystem );
		$this->set_protected_property( $subject, 'screen_ids', $screen_ids );

		WP_Mock::expectActionNotAdded( 'admin_init', [ $subject, 'deactivate_plugin' ] );

		self::assertTrue( $subject->are_requirements_met() );
	}

	/**
	 * Data provider for test_vars_requirements_not_met.
	 *
	 * @return array
	 */
	public static function dp_test_vars_requirements_not_met(): array {
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
	 *
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_vars_requirements_not_met_and_filesystem_not_available() {
		$max_input_vars    = $this->cyr_to_lat_required_max_input_vars - 1;
		$user_ini_filename = '.user.ini';
		$ini_ttl           = 300;

		$settings = Mockery::mock( Settings::class );
		$ids      = [ 'settings_page_cyr-to-lat' ];
		$settings->shouldReceive( 'screen_ids' )->with()->andReturn( $ids );

		$admin_notices = Mockery::mock( Admin_Notices::class );

		FunctionMocker::replace(
			'function_exists',
			static function ( $arg ) {
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
			static function ( $arg ) use ( $max_input_vars, $user_ini_filename, $ini_ttl ) {
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

		$message    = 'Unable to get filesystem access.';
		$screen_ids = [ 'screen_ids' => $ids ];

		$admin_notices->shouldReceive( 'add_notice' )->with( $message, 'notice notice-error', $screen_ids );

		$message = 'Please increase max input vars limit up to 1500.';

		$message .= '<br>';
		$message .= 'See: <a href="https://sevenspark.com/docs/ubermenu-3/faqs/menu-item-limit" target="_blank">Increasing max input vars limit.</a>';

		$admin_notices->shouldReceive( 'add_notice' )->with( $message, 'notice notice-error', $screen_ids );

		$subject = new Requirements( $settings, $admin_notices, null );
		$this->set_protected_property( $subject, 'screen_ids', $screen_ids );

		WP_Mock::expectActionNotAdded( 'admin_init', [ $subject, 'deactivate_plugin' ] );

		self::assertTrue( $subject->are_requirements_met() );
	}

	/**
	 * Test deactivate_plugin().
	 */
	public function test_deactivate_plugin() {
		$settings = Mockery::mock( Settings::class );
		$ids      = [ 'settings_page_cyr-to-lat' ];
		$settings->shouldReceive( 'screen_ids' )->with()->andReturn( $ids );

		$admin_notices = Mockery::mock( Admin_Notices::class );
		$admin_notices
			->shouldReceive( 'add_notice' )
			->with( 'Cyr To Lat plugin has been deactivated.', 'notice notice-info is-dismissible' );

		$wp_filesystem = Mockery::mock( WP_Filesystem_Direct::class );

		FunctionMocker::replace(
			'function_exists',
			static function ( $arg ) {
				return 'WP_Filesystem' === $arg;
			}
		);

		WP_Mock::userFunction( 'WP_Filesystem' )->andReturn( true );

		WP_Mock::passthruFunction( 'plugin_basename' );
		WP_Mock::userFunction( 'is_plugin_active' )->with( $this->cyr_to_lat_file )->andReturn( true );
		WP_Mock::userFunction( 'deactivate_plugins' )->with( $this->cyr_to_lat_file );

		$_GET['activate'] = 'some value';

		$subject = new Requirements( $settings, $admin_notices, $wp_filesystem );
		$subject->deactivate_plugin();

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		self::assertArrayNotHasKey( 'activate', $_GET );
	}

	/**
	 * Test deactivate_plugin() when it is not active.
	 */
	public function test_deactivate_plugin_when_it_is_not_active() {
		$settings = Mockery::mock( Settings::class );
		$settings->shouldReceive( 'screen_ids' )->with()->andReturn( [ 'settings_page_cyr-to-lat' ] );

		$admin_notices = Mockery::mock( Admin_Notices::class );
		$wp_filesystem = Mockery::mock( WP_Filesystem_Direct::class );

		FunctionMocker::replace(
			'function_exists',
			static function ( $arg ) {
				return 'WP_Filesystem' === $arg;
			}
		);

		WP_Mock::userFunction( 'WP_Filesystem' )->andReturn( true );

		WP_Mock::passthruFunction( 'plugin_basename' );
		WP_Mock::userFunction( 'is_plugin_active' )->with( $this->cyr_to_lat_file )->andReturn( false );

		$subject = new Requirements( $settings, $admin_notices, $wp_filesystem );
		$subject->deactivate_plugin();
	}
}
