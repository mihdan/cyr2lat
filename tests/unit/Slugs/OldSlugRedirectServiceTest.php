<?php
/**
 * OldSlugRedirectServiceTest class file.
 *
 * @package cyr-to-lat
 */

namespace CyrToLat\Tests\Unit\Slugs;

use CyrToLat\Slugs\OldSlugRedirectService;
use CyrToLat\Tests\Unit\CyrToLatTestCase;
use CyrToLat\Transliteration\Transliterator;
use Mockery;
use WP_Mock;

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
		$transliterator = Mockery::mock( Transliterator::class );
		$subject        = new OldSlugRedirectService( $transliterator );
		$post        = (object) [ 'post_name' => 'j' ];
		$post_before = (object) [ 'post_name' => 'j' ];

		$subject->check_for_changed_slugs( 5, $post, $post_before );

		self::assertSame( 'j', $post_before->post_name );
	}

	/**
	 * Test check_for_changed_slugs() stores encoded title when cyr2lat generated the new slug.
	 *
	 * @return void
	 */
	public function test_check_for_changed_slugs_stores_encoded_title_for_generated_slug(): void {
		$transliterator = Mockery::mock( Transliterator::class );
		$transliterator->shouldReceive( 'transliterate' )->with( 'й' )->andReturn( 'j' );

		$subject = new OldSlugRedirectService( $transliterator );

		$post = (object) [
			'post_title'  => 'й',
			'post_name'   => 'j',
			'post_status' => 'publish',
			'post_type'   => 'post',
		];

		$post_before = (object) [
			'post_name' => '',
			'post_type' => 'post',
		];

		WP_Mock::userFunction( 'get_post_type' )->with( $post )->andReturn( 'post' );
		WP_Mock::userFunction( 'is_post_type_hierarchical' )->with( 'post' )->andReturn( false );

		$subject->check_for_changed_slugs( 5, $post, $post_before );

		self::assertSame( '%D0%B9', $post_before->post_name );
	}
}
