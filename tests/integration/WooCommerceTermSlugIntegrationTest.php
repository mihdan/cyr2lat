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

namespace CyrToLat\Tests\Integration;

use WC_Install;
use WC_Post_Types;
use WP_Term;

/**
 * Class WooCommerceTermSlugIntegrationTest
 *
 * @group integration
 * @group woocommerce
 */
class WooCommerceTermSlugIntegrationTest extends PluginWPTestCase {

	/**
	 * WooCommerce plugin path relative to WP_PLUGIN_DIR.
	 *
	 * @var string
	 */
	protected static string $plugin = 'woocommerce/woocommerce.php';

	/**
	 * Set up an allowed admin term request context.
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		if ( ! function_exists( 'WC' ) || ! class_exists( WC_Post_Types::class ) ) {
			self::markTestSkipped( 'WooCommerce taxonomy classes are not loaded in the integration test environment.' );
		}

		$this->install_woocommerce_tables();
		$this->init_woocommerce();

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

		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		unset( $GLOBALS['current_screen'] );

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
	 * Install WooCommerce database tables needed by taxonomy flows.
	 *
	 * @return void
	 */
	private function install_woocommerce_tables(): void {
		if ( class_exists( WC_Install::class ) ) {
			WC_Install::create_tables();
			update_option( 'woocommerce_version', WC()->version );
		}
	}

	/**
	 * Initialize WooCommerce and restore post type/taxonomy lifecycle actions in the PHPUnit process.
	 *
	 * @return void
	 */
	private function init_woocommerce(): void {
		WC()->init();

		WC_Post_Types::register_taxonomies();
		WC_Post_Types::register_post_types();

		if ( ! did_action( 'woocommerce_after_register_taxonomy' ) ) {
			do_action( 'woocommerce_after_register_taxonomy' );
		}

		if ( ! did_action( 'woocommerce_after_register_post_type' ) ) {
			do_action( 'woocommerce_after_register_post_type' );
		}
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
