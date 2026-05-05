<?php
/**
 * OldSlugRedirectServiceTest class file.
 *
 * @package cyr-to-lat
 */

namespace CyrToLat\Tests\Unit\Slugs;

use CyrToLat\Slugs\OldSlugRedirectService;
use CyrToLat\Tests\Unit\CyrToLatTestCase;

/**
 * Class OldSlugRedirectServiceTest
 *
 * @group slugs
 */
class OldSlugRedirectServiceTest extends CyrToLatTestCase {

	/**
	 * Test check_for_changed_slugs() service boundary.
	 *
	 * @return void
	 */
	public function test_check_for_changed_slugs_boundary(): void {
		$subject     = new OldSlugRedirectService();
		$post        = (object) [ 'post_name' => 'j' ];
		$post_before = (object) [ 'post_name' => '' ];

		$subject->check_for_changed_slugs( 5, $post, $post_before );

		self::assertSame( '', $post_before->post_name );
	}
}
