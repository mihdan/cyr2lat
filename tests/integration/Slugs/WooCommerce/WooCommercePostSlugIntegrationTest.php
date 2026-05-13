<?php
/**
 * WooCommercePostSlugIntegrationTest class file.
 *
 * @package cyr-to-lat
 */

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpUndefinedClassInspection */
/** @noinspection PhpUndefinedFunctionInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

namespace CyrToLat\Tests\Integration\Slugs\WooCommerce;

use CyrToLat\Tests\Integration\WooCommerceWPTestCase;

/**
 * Class WooCommercePostSlugIntegrationTest
 *
 * @group integration
 * @group woocommerce
 */
class WooCommercePostSlugIntegrationTest extends WooCommerceWPTestCase {

	/**
	 * Set up an allowed admin product request context.
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

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

		parent::tearDown();
	}

	/**
	 * Test that wp_insert_post() creates a product slug from a Cyrillic title.
	 *
	 * @return void
	 */
	public function test_wp_insert_post_creates_product_slug_from_cyrillic_title(): void {
		self::assertTrue( post_type_exists( 'product' ) );

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
}
