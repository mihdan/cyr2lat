<?php
/**
 * WooCommerceTermSlugIntegrationTest class file.
 *
 * @package cyr-to-lat
 */

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpUndefinedClassInspection */
/** @noinspection PhpUndefinedFunctionInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

namespace CyrToLat\Tests\Integration\Slugs\WooCommerce;

use CyrToLat\Tests\Integration\WooCommerceWPTestCase;
use WP_Term;

/**
 * Class WooCommerceTermSlugIntegrationTest
 *
 * @group integration
 * @group woocommerce
 */
class WooCommerceTermSlugIntegrationTest extends WooCommerceWPTestCase {

	/**
	 * Set up an allowed admin term request context.
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		set_current_screen( 'edit-tags' );
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
	 * Test that wp_insert_term creates a transliterated WooCommerce product category slug.
	 *
	 * @return void
	 */
	public function test_wp_insert_term_generates_product_category_slug_from_cyrillic_name(): void {
		self::assertTrue( taxonomy_exists( 'product_cat' ) );

		$term = $this->insert_term( 'й', 'product_cat' );

		self::assertSame( 'j', $term->slug );
	}

	/**
	 * Insert a term and return the stored WP_Term object.
	 *
	 * @param string $name     Term name.
	 * @param string $taxonomy Taxonomy slug.
	 *
	 * @return WP_Term
	 */
	private function insert_term( string $name, string $taxonomy ): WP_Term {
		$result = wp_insert_term( $name, $taxonomy );

		$this->assertNotWPError( $result );
		self::assertIsArray( $result );

		$term = get_term( (int) $result['term_id'], $taxonomy );

		$this->assertNotWPError( $term );
		self::assertInstanceOf( WP_Term::class, $term );

		return $term;
	}
}
