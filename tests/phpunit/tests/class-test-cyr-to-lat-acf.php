<?php
/**
 * Test_ACF class file
 *
 * @package cyr-to-lat
 */

namespace Cyr_To_Lat;

use Mockery;
use ReflectionClass;
use ReflectionException;

/**
 * Class Test_ACF
 *
 * @group acf
 */
class Test_ACF extends Cyr_To_Lat_TestCase {

	/**
	 * Test constructor
	 *
	 * @throws ReflectionException Reflection Exception.
	 */
	public function test_constructor() {
		$classname = __NAMESPACE__ . '\ACF';

		$settings = Mockery::mock( Settings::class );

		// Get mock, without the constructor being called.
		$mock = $this->getMockBuilder( $classname )->disableOriginalConstructor()->getMock();

		// Set expectations for constructor calls.
		$mock->expects( $this->once() )->method( 'init_hooks' );

		// Now call the constructor.
		$reflected_class = new ReflectionClass( $classname );
		$constructor     = $reflected_class->getConstructor();
		$constructor->invoke( $mock, [ $settings ] );

		$this->assertTrue( true );
	}

	/**
	 * Test init_hooks()
	 */
	public function test_init_hooks() {
		$settings = Mockery::mock( Settings::class );
		$subject  = new ACF( $settings );

		\WP_Mock::expectActionAdded(
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

		$settings = Mockery::mock( Settings::class );
		$settings->shouldReceive( 'get_table' )->andReturn( $table );

		\WP_Mock::userFunction(
			'wp_enqueue_script',
			[
				'args'  => [
					'cyr-to-lat-acf-field-group',
					CYR_TO_LAT_URL . '/js/acf-field-group.js',
					[],
					CYR_TO_LAT_VERSION,
					true,
				],
				'times' => 1,
			]
		);
		\WP_Mock::userFunction(
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
