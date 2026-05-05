<?php
/**
 * PostSlugServiceTest class file.
 *
 * @package cyr-to-lat
 */

namespace CyrToLat\Tests\Unit\Slugs;

use CyrToLat\Slugs\PostSlugService;
use CyrToLat\Tests\Unit\CyrToLatTestCase;

/**
 * Class PostSlugServiceTest
 *
 * @group slugs
 */
class PostSlugServiceTest extends CyrToLatTestCase {

	/**
	 * Test filter_post_data().
	 *
	 * @return void
	 */
	public function test_filter_post_data_returns_data(): void {
		$subject = new PostSlugService();
		$data    = [
			'post_name'  => '',
			'post_title' => 'й',
		];

		self::assertSame( $data, $subject->filter_post_data( $data ) );
	}
}
