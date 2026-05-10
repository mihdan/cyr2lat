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
		$post           = (object) [ 'post_name' => 'j' ];
		$post_before    = (object) [ 'post_name' => 'j' ];

		$subject->check_for_changed_slugs( 5, $post, $post_before );

		self::assertSame( 'j', $post_before->post_name );
	}

	/**
	 * Test check_for_changed_slugs() stores the encoded title when cyr2lat generated the new slug.
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

	/**
	 * Test check_for_changed_slugs() skips unsupported post shapes.
	 *
	 * @param object $post        The post object.
	 * @param object $post_before The previous post object.
	 *
	 * @return void
	 * @dataProvider dp_test_check_for_changed_slugs_skips_unsupported_post_shapes
	 */
	public function test_check_for_changed_slugs_skips_unsupported_post_shapes( object $post, object $post_before ): void {
		$transliterator = Mockery::mock( Transliterator::class );
		$transliterator->shouldNotReceive( 'transliterate' );

		$subject = new OldSlugRedirectService( $transliterator );

		WP_Mock::userFunction( 'get_post_type' )->with( $post )->andReturn( $post->post_type );
		WP_Mock::userFunction( 'is_post_type_hierarchical' )->with( $post->post_type )
			->andReturn( 'page' === $post->post_type );

		$subject->check_for_changed_slugs( 5, $post, $post_before );

		self::assertSame( 'й', $post_before->post_name );
	}

	/**
	 * Data provider for test_check_for_changed_slugs_skips_unsupported_post_shapes().
	 *
	 * @return array
	 */
	public static function dp_test_check_for_changed_slugs_skips_unsupported_post_shapes(): array {
		return [
			'non-published post'  => [
				(object) [
					'post_name'   => 'j',
					'post_status' => 'draft',
					'post_type'   => 'post',
				],
				(object) [
					'post_name' => 'й',
				],
			],
			'hierarchical post'   => [
				(object) [
					'post_name'   => 'j',
					'post_status' => 'publish',
					'post_type'   => 'page',
				],
				(object) [
					'post_name' => 'й',
				],
			],
			'attachment mismatch' => [
				(object) [
					'post_name'   => 'j',
					'post_status' => 'draft',
					'post_type'   => 'attachment',
				],
				(object) [
					'post_name' => 'й',
				],
			],
		];
	}

	/**
	 * Test check_for_changed_slugs() handles attachments with inherited status.
	 *
	 * @return void
	 */
	public function test_check_for_changed_slugs_handles_attachment_inherit_status(): void {
		$transliterator = Mockery::mock( Transliterator::class );
		$transliterator->shouldReceive( 'transliterate' )->with( 'й' )->andReturn( 'j' );

		$subject = new OldSlugRedirectService( $transliterator );

		$post = (object) [
			'post_title'  => 'й',
			'post_name'   => 'j',
			'post_status' => 'inherit',
			'post_type'   => 'attachment',
		];

		$post_before = (object) [
			'post_name' => '',
		];

		WP_Mock::userFunction( 'get_post_type' )->with( $post )->andReturn( 'attachment' );
		WP_Mock::userFunction( 'is_post_type_hierarchical' )->with( 'attachment' )->andReturn( false );

		$subject->check_for_changed_slugs( 5, $post, $post_before );

		self::assertSame( '%D0%B9', $post_before->post_name );
	}
}
