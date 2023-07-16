<?php
/**
 * TestACF class file
 *
 * @package cyr-to-lat
 */

// phpcs:disable PHPCompatibility.FunctionDeclarations.NewReturnTypeDeclarations.voidFound

namespace CyrToLat\Tests\Unit;

use Cyr_To_Lat\ACF;
use Cyr_To_Lat\Main;
use Cyr_To_Lat\Settings\Settings;
use Mockery;
use ReflectionClass;
use ReflectionException;
use WP_Mock;

/**
 * Class TestACF
 *
 * @group acf
 */
class ACFTest extends CyrToLatTestCase {

	/**
	 * Tear down.
	 *
	 * @noinspection PhpLanguageLevelInspection
	 * @noinspection PhpUndefinedClassInspection
	 */
	public function tearDown(): void {
		unset( $GLOBALS['cyr_to_lat_plugin'] );
		parent::tearDown();
	}

	/**
	 * Test constructor
	 *
	 * @throws ReflectionException Reflection Exception.
	 */
	public function test_constructor() {
		$classname = ACF::class;

		$settings = Mockery::mock( Settings::class );

		// Get mock, without the constructor being called.
		$mock = $this->getMockBuilder( $classname )->disableOriginalConstructor()->getMock();

		// Set expectations for constructor calls.
		$mock->expects( self::once() )->method( 'init_hooks' );

		// Now call the constructor.
		$reflected_class = new ReflectionClass( $classname );
		$constructor     = $reflected_class->getConstructor();
		$constructor->invoke( $mock, [ $settings ] );
	}

	/**
	 * Test init_hooks()
	 */
	public function test_init_hooks() {
		$settings = Mockery::mock( Settings::class );
		$subject  = new ACF( $settings );

		WP_Mock::expectActionAdded(
			'acf/field_group/admin_enqueue_scripts',
			[ $subject, 'enqueue_script' ]
		);

		$subject->init_hooks();
	}

	/**
	 * Test enqueue_script()
	 */
	public function test_enqueue_script() {
		$table  = [ 'я' => 'ya' ];
		$object = [ 'table' => $table ];

		$main = Mockery::mock( Main::class );
		$main->shouldReceive( 'min_suffix' )->andReturn( '' );
		$GLOBALS['cyr_to_lat_plugin'] = $main;

		$settings = Mockery::mock( Settings::class );
		$settings->shouldReceive( 'get_table' )->andReturn( $table );

		WP_Mock::userFunction(
			'wp_enqueue_script',
			[
				'args'  => [
					'cyr-to-lat-acf-field-group',
					$this->cyr_to_lat_url . '/assets/js/acf-field-group.js',
					[],
					$this->cyr_to_lat_version,
					true,
				],
				'times' => 1,
			]
		);
		WP_Mock::userFunction(
			'wp_localize_script',
			[
				'args'  => [
					'cyr-to-lat-acf-field-group',
					'CyrToLatAcfFieldGroup',
					$object,
				],
				'times' => 1,
			]
		);

		$subject = new ACF( $settings );
		$subject->enqueue_script();
	}
}
