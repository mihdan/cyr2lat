<?php
/**
 * Test_Cyr_To_Lat_Admin_Notices class file
 *
 * @package cyr-to-lat
 */

use PHPUnit\Framework\TestCase;

/**
 * Class Test_Cyr_To_Lat_Admin_Notices
 *
 * @group admin-notices
 */
class Test_Cyr_To_Lat_Admin_Notices extends TestCase {

	/**
	 * Setup test
	 */
	public function setUp() {
		parent::setUp();
		\WP_Mock::setUp();
	}

	/**
	 * End test
	 */
	public function tearDown() {
		\WP_Mock::tearDown();
		parent::tearDown();
	}

	/**
	 * Test constructor
	 *
	 * @throws ReflectionException Reflection Exception.
	 */
	public function test_constructor() {
		$classname = 'Cyr_To_Lat_Admin_Notices';

		// Get mock, without the constructor being called.
		$mock = $this->getMockBuilder( $classname )->disableOriginalConstructor()->getMock();

		// Set expectations for constructor calls.
		\WP_Mock::expectActionAdded( 'admin_notices', array( $mock, 'show_notices' ) );

		// Now call the constructor.
		$reflected_class = new ReflectionClass( $classname );
		$constructor     = $reflected_class->getConstructor();
		$constructor->invoke( $mock );

		$this->assertTrue( true );
	}

	/**
	 * Test add_notice() and show_notices()
	 */
	public function test_add_show_notices() {
		$expected = '			<div class="notice">
				<p>
					<strong>
						First message					</strong>
				</p>
			</div>
						<div class="error">
				<p>
					<strong>
						Second message					</strong>
				</p>
			</div>
			';

		$subject = new Cyr_To_Lat_Admin_Notices();

		\WP_Mock::passthruFunction( 'wp_kses_post' );

		ob_start();
		$subject->show_notices();
		$result = ob_get_clean();
		$this->assertEmpty( $result );

		$subject->add_notice( 'First message' );
		$subject->add_notice( 'Second message', 'error' );

		ob_start();
		$subject->show_notices();
		$result = ob_get_clean();

		$this->assertSame( $expected, $result );
	}
}
