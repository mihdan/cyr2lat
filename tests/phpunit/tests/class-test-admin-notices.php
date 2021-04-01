<?php
/**
 * Test_Admin_Notices class file
 *
 * @package cyr-to-lat
 */

namespace Cyr_To_Lat;

use ReflectionClass;
use ReflectionException;
use WP_Mock;

/**
 * Class Test_Admin_Notices
 *
 * @group admin-notices
 */
class Test_Admin_Notices extends Cyr_To_Lat_TestCase {

	/**
	 * Test constructor
	 *
	 * @throws ReflectionException Reflection Exception.
	 * @noinspection NullPointerExceptionInspection
	 */
	public function test_constructor() {
		$classname = __NAMESPACE__ . '\Admin_Notices';

		// Get mock, without the constructor being called.
		$mock = $this->getMockBuilder( $classname )->disableOriginalConstructor()->getMock();

		// Set expectations for constructor calls.
		WP_Mock::expectActionAdded( 'admin_notices', [ $mock, 'show_notices' ] );

		// Now call the constructor.
		$reflected_class = new ReflectionClass( $classname );
		$constructor     = $reflected_class->getConstructor();
		$constructor->invoke( $mock );
	}

	/**
	 * Test add_notice() and show_notices()
	 */
	public function test_add_and_show_notices() {
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

		$subject = new Admin_Notices();

		WP_Mock::passthruFunction( 'wp_kses_post' );

		ob_start();
		$subject->show_notices();
		$result = ob_get_clean();
		self::assertEmpty( $result );

		$subject->add_notice( 'First message' );
		$subject->add_notice( 'Second message', 'error' );

		ob_start();
		$subject->show_notices();
		$result = ob_get_clean();

		self::assertSame( $expected, $result );
	}

	/**
	 * Test add_notice() and show_notices() when page is not allowed
	 */
	public function test_show_notices_when_page_is_not_allowed() {
		$page_slug = 'some_page';

		WP_Mock::userFunction( 'get_current_screen' )->andReturn( null );

		$subject = new Admin_Notices();

		$subject->add_notice( 'Message', 'notice', [ 'screen_ids' => $page_slug ] );

		ob_start();
		$subject->show_notices();
		$result = ob_get_clean();

		self::assertEmpty( $result );
	}

	/**
	 * Test add_notice() and show_notices() when page is allowed
	 */
	public function test_show_notices_when_page_is_allowed() {
		$expected = '			<div class="notice">
				<p>
					<strong>
						Message					</strong>
				</p>
			</div>
			';

		$page_slug      = 'some_page';
		$current_screen = (object) [ 'id' => $page_slug ];

		WP_Mock::userFunction( 'get_current_screen' )->andReturn( $current_screen );

		$subject = new Admin_Notices();

		WP_Mock::passthruFunction( 'wp_kses_post' );

		$subject->add_notice( 'Message', 'notice', [ 'screen_ids' => $page_slug ] );

		ob_start();
		$subject->show_notices();
		$result = ob_get_clean();

		self::assertSame( $expected, $result );
	}
}
