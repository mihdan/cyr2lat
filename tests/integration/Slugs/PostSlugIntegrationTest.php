<?php
/**
 * PostSlugIntegrationTest class file.
 *
 * @package cyr-to-lat
 */

namespace CyrToLat\Tests\Integration\Slugs;

use WP_UnitTestCase;

/**
 * Class PostSlugIntegrationTest
 *
 * @group integration
 */
class PostSlugIntegrationTest extends WP_UnitTestCase {

	private const CPT = 'cyr2lat_book';

	/**
	 * Set up the allowed request context required for backend slug hooks.
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		register_post_type(
			self::CPT,
			[
				'public' => true,
				'label'  => 'Books',
			]
		);

		set_current_screen( 'post' );
		cyr_to_lat()->init_all();
		add_filter( 'ctl_enable_legacy_sanitize_title_bridge', '__return_false' );
	}

	/**
	 * Tear down test globals.
	 *
	 * @return void
	 */
	public function tearDown(): void {
		remove_filter( 'ctl_enable_legacy_sanitize_title_bridge', '__return_false' );
		unregister_post_type( self::CPT );

		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		unset( $GLOBALS['current_screen'] );

		parent::tearDown();
	}

	/**
	 * Test that the plugin registers post slug generation on wp_insert_post_data.
	 *
	 * @return void
	 */
	public function test_wp_insert_post_data_filter_is_registered(): void {
		self::assertSame( 10, has_filter( 'wp_insert_post_data', [ cyr_to_lat(), 'sanitize_post_name' ] ) );
	}

	/**
	 * Test that wp_insert_post_data generates post_name from Cyrillic post_title.
	 *
	 * @return void
	 */
	public function test_wp_insert_post_data_generates_post_name_from_cyrillic_title(): void {
		set_current_screen( 'post' );

		$filtered = apply_filters(
			'wp_insert_post_data',
			[
				'post_name'   => '',
				'post_title'  => 'й',
				'post_status' => 'publish',
			],
			[]
		);

		self::assertSame( 'j', $filtered['post_name'] );
	}

	/**
	 * Test that wp_insert_post() creates a post slug from a Cyrillic title.
	 *
	 * @return void
	 */
	public function test_wp_insert_post_creates_post_slug_from_cyrillic_title(): void {
		$post_id = wp_insert_post(
			[
				'post_type'   => 'post',
				'post_status' => 'publish',
				'post_title'  => 'й',
			],
			true
		);

		$this->assertNotWPError( $post_id );
		self::assertSame( 'j', get_post( $post_id )->post_name );
	}

	/**
	 * Test that wp_insert_post() creates a page slug from the Cyrillic title.
	 *
	 * @return void
	 */
	public function test_wp_insert_post_creates_page_slug_from_cyrillic_title(): void {
		$post_id = wp_insert_post(
			[
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post_title'  => 'й',
			],
			true
		);

		$this->assertNotWPError( $post_id );
		self::assertSame( 'j', get_post( $post_id )->post_name );
	}

	/**
	 * Test that wp_insert_post() creates a custom post type slug from a Cyrillic title.
	 *
	 * @return void
	 */
	public function test_wp_insert_post_creates_custom_post_type_slug_from_cyrillic_title(): void {
		$post_id = wp_insert_post(
			[
				'post_type'   => self::CPT,
				'post_status' => 'publish',
				'post_title'  => 'й',
			],
			true
		);

		$this->assertNotWPError( $post_id );
		self::assertSame( 'j', get_post( $post_id )->post_name );
	}

	/**
	 * Test that a Yoast Duplicate Post draft gets a slug from its edited title on publication.
	 */
	public function test_yoast_duplicate_post_keeps_slug_empty_until_publication(): void {
		$original_id = wp_insert_post(
			[
				'post_type'   => 'post',
				'post_status' => 'publish',
				'post_title'  => 'Исходный заголовок',
			],
			true
		);

		$this->assertNotWPError( $original_id );

		$duplicate_data = apply_filters(
			'duplicate_post_new_post',
			[
				'post_type'   => 'post',
				'post_status' => 'draft',
				'post_title'  => 'Исходный заголовок',
				'post_name'   => '',
			],
			get_post( $original_id )
		);
		$duplicate_id   = wp_insert_post( wp_slash( $duplicate_data ), true );

		$this->assertNotWPError( $duplicate_id );
		self::assertSame( '', get_post( $duplicate_id )->post_name );

		$updated_id = wp_update_post(
			[
				'ID'          => $duplicate_id,
				'post_title'  => 'Новый заголовок копии',
				'post_status' => 'publish',
			],
			true
		);

		$this->assertNotWPError( $updated_id );
		self::assertSame( 'novyj-zagolovok-kopii', get_post( $duplicate_id )->post_name );
	}

	/**
	 * Test that wp_insert_post_data preserves a manually supplied post_name.
	 *
	 * @return void
	 */
	public function test_wp_insert_post_data_preserves_manual_post_name(): void {
		set_current_screen( 'post' );

		$filtered = apply_filters(
			'wp_insert_post_data',
			[
				'post_name'   => 'manual-slug',
				'post_title'  => 'й',
				'post_status' => 'publish',
			],
			[]
		);

		self::assertSame( 'manual-slug', $filtered['post_name'] );
	}

	/**
	 * Test that wp_insert_post_data skips non-publishable transient post statuses.
	 *
	 * @param string $post_status Post status.
	 *
	 * @dataProvider data_non_publishable_post_statuses
	 *
	 * @return void
	 */
	public function test_wp_insert_post_data_skips_non_publishable_post_statuses( string $post_status ): void {
		set_current_screen( 'post' );

		$filtered = apply_filters(
			'wp_insert_post_data',
			[
				'post_name'   => '',
				'post_title'  => 'й',
				'post_status' => $post_status,
			],
			[]
		);

		self::assertSame( '', $filtered['post_name'] );
	}

	/**
	 * Data provider for non-publishable post statuses.
	 *
	 * @return array<string, array{string}>
	 */
	public static function data_non_publishable_post_statuses(): array {
		return [
			'auto-draft' => [ 'auto-draft' ],
			'revision'   => [ 'revision' ],
		];
	}
}
