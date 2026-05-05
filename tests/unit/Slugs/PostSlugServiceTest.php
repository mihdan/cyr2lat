<?php
/**
 * PostSlugServiceTest class file.
 *
 * @package cyr-to-lat
 */

namespace CyrToLat\Tests\Unit\Slugs;

use CyrToLat\Slugs\PostSlugService;
use CyrToLat\Tests\Unit\CyrToLatTestCase;
use WP_Mock;

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
		$data    = 'not-array';

		self::assertSame( $data, $subject->filter_post_data( $data ) );
	}

	/**
	 * Test filter_post_data() generates post_name from title.
	 *
	 * @return void
	 */
	public function test_filter_post_data_generates_empty_post_name_from_title(): void {
		$subject = new PostSlugService();
		$data    = [
			'post_name'   => '',
			'post_title'  => 'й',
			'post_status' => 'publish',
		];

		WP_Mock::userFunction(
			'sanitize_title',
			[
				'args'   => [ 'й' ],
				'return' => 'j',
			]
		);

		$filtered = $subject->filter_post_data( $data );

		self::assertSame( 'j', $filtered['post_name'] );
	}

	/**
	 * Test filter_post_data() keeps empty post_name without title.
	 *
	 * @return void
	 */
	public function test_filter_post_data_keeps_empty_post_name_without_title(): void {
		$subject = new PostSlugService();
		$data    = [
			'post_name'   => '',
			'post_title'  => '',
			'post_status' => 'publish',
		];

		self::assertSame( $data, $subject->filter_post_data( $data ) );
	}

	/**
	 * Test filter_post_data() normalizes explicit Cyrillic post_name.
	 *
	 * @return void
	 */
	public function test_filter_post_data_normalizes_explicit_cyrillic_post_name(): void {
		$subject = new PostSlugService();
		$data    = [
			'post_name'   => 'й',
			'post_title'  => 'Title',
			'post_status' => 'publish',
		];

		WP_Mock::userFunction(
			'sanitize_title',
			[
				'args'   => [ 'й' ],
				'return' => 'j',
			]
		);

		$filtered = $subject->filter_post_data( $data );

		self::assertSame( 'j', $filtered['post_name'] );
	}
}
