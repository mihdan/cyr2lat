<?php
/**
 * PostSlugIntegrationTest class file.
 *
 * @package cyr-to-lat
 */

namespace CyrToLat\Tests\Integration;

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
	 * Test that wp_insert_post() creates a post slug from Cyrillic title.
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
	 * Test that wp_insert_post() creates a page slug from Cyrillic title.
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
	 * Test that wp_insert_post() creates a custom post type slug from Cyrillic title.
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
	 * Test that wp_insert_post() creates a product slug from Cyrillic title when WooCommerce is available.
	 *
	 * @return void
	 */
	public function test_wp_insert_post_creates_product_slug_from_cyrillic_title_when_available(): void {
		if ( ! post_type_exists( 'product' ) ) {
			self::markTestSkipped( 'WooCommerce product post type is not registered.' );
		}

		$post_id = wp_insert_post(
			[
				'post_type'   => 'product',
				'post_status' => 'publish',
				'post_title'  => 'й',
			],
			true
		);

		$this->assertNotWPError( $post_id );
		self::assertSame( 'j', get_post( $post_id )->post_name );
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
