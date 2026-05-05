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

	/**
	 * Set up the allowed request context required for backend slug hooks.
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		set_current_screen( 'post' );
		cyr_to_lat()->init_all();
	}

	/**
	 * Tear down test globals.
	 *
	 * @return void
	 */
	public function tearDown(): void {
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
