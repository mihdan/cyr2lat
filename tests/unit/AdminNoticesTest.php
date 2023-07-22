<?php
/**
 * AdminNoticesTest class file
 *
 * @package cyr-to-lat
 */

namespace CyrToLat\Tests\Unit;

use CyrToLat\Admin_Notices;
use ReflectionClass;
use ReflectionException;
use WP_Mock;

/**
 * Class AdminNoticesTest
 *
 * @group admin-notices
 */
class AdminNoticesTest extends CyrToLatTestCase {

	/**
	 * Test constructor
	 *
	 * @throws ReflectionException Reflection Exception.
	 */
	public function test_constructor() {
		$classname = Admin_Notices::class;

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
