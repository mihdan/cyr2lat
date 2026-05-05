<?php
/**
 * WooCommerceGlobalAttributeIntegrationTest class file.
 *
 * @package cyr-to-lat
 */

namespace CyrToLat\Tests\Integration;

use WC_Cache_Helper;
use WC_Install;
use WP_UnitTestCase;

/**
 * Class WooCommerceGlobalAttributeIntegrationTest
 *
 * @group integration
 * @group woocommerce
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class WooCommerceGlobalAttributeIntegrationTest extends WP_UnitTestCase {

	/**
	 * Set up an allowed admin request context with real WooCommerce loaded.
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		$this->load_woocommerce();

		if ( ! function_exists( 'WC' ) || ! function_exists( 'wc_create_attribute' ) ) {
			self::markTestSkipped( 'WooCommerce is not loaded in the integration test environment.' );
		}

		set_current_screen( 'edit-tags' );
		$this->install_woocommerce_tables();
		WC()->init();
		$this->delete_woocommerce_attribute_taxonomies();
		$this->reset_woocommerce_attribute_taxonomies();
		cyr_to_lat()->init_all();
	}

	/**
	 * Tear down test globals.
	 *
	 * @return void
	 */
	public function tearDown(): void {
		$this->delete_woocommerce_attribute_taxonomies();
		$this->reset_woocommerce_attribute_taxonomies();

		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		unset( $GLOBALS['current_screen'] );

		parent::tearDown();
	}

	/**
	 * Test that WooCommerce creates the current transliterated global attribute slug from a Cyrillic name.
	 *
	 * @return void
	 */
	public function test_wc_create_attribute_uses_sanitize_title_for_cyrillic_attribute_name(): void {
		$sanitize_title_calls = [];
		$spy                  = $this->add_sanitize_title_spy( $sanitize_title_calls );

		$attribute_id = wc_create_attribute(
			[
				'name' => 'Цвет',
			]
		);
		remove_filter( 'sanitize_title', $spy, 1 );

		$this->assertNotWPError( $attribute_id );
		self::assertIsInt( $attribute_id );

		$attribute = $this->get_woocommerce_attribute_taxonomy( $attribute_id );

		self::assertContains(
			[
				'title'     => 'Цвет',
				'raw_title' => 'Цвет',
				'context'   => 'save',
			],
			$sanitize_title_calls
		);
		self::assertSame( 'czvet', $attribute->attribute_name );
		self::assertSame( 'Цвет', $attribute->attribute_label );
	}

	/**
	 * Test that WooCommerce creates the current transliterated global attribute slug from an explicit Cyrillic slug.
	 *
	 * @return void
	 */
	public function test_wc_create_attribute_uses_sanitize_title_for_explicit_cyrillic_slug(): void {
		$sanitize_title_calls = [];
		$spy                  = $this->add_sanitize_title_spy( $sanitize_title_calls );

		$attribute_id = wc_create_attribute(
			[
				'name' => 'Material',
				'slug' => 'материал',
			]
		);
		remove_filter( 'sanitize_title', $spy, 1 );

		$this->assertNotWPError( $attribute_id );
		self::assertIsInt( $attribute_id );

		$attribute = $this->get_woocommerce_attribute_taxonomy( $attribute_id );

		self::assertContains(
			[
				'title'     => 'материал',
				'raw_title' => 'материал',
				'context'   => 'save',
			],
			$sanitize_title_calls
		);
		self::assertSame( 'material', $attribute->attribute_name );
		self::assertSame( 'Material', $attribute->attribute_label );
	}

	/**
	 * Test that WooCommerce preserves an explicit Latin global attribute slug.
	 *
	 * @return void
	 */
	public function test_wc_create_attribute_preserves_explicit_latin_slug(): void {
		$attribute_id = wc_create_attribute(
			[
				'name' => 'Цвет',
				'slug' => 'color',
			]
		);

		$this->assertNotWPError( $attribute_id );
		self::assertIsInt( $attribute_id );

		$attribute = $this->get_woocommerce_attribute_taxonomy( $attribute_id );

		self::assertSame( 'color', $attribute->attribute_name );
		self::assertSame( 'Цвет', $attribute->attribute_label );
	}

	/**
	 * Test that a registered global attribute taxonomy key is not transliterated by Cyr-To-Lat.
	 *
	 * @return void
	 */
	public function test_registered_global_attribute_taxonomy_key_is_not_transliterated(): void {
		$attribute_id = wc_create_attribute(
			[
				'name' => 'Цвет',
			]
		);

		$this->assertNotWPError( $attribute_id );
		self::assertIsInt( $attribute_id );

		$attribute = $this->get_woocommerce_attribute_taxonomy( $attribute_id );
		$this->reset_woocommerce_attribute_taxonomies();

		$taxonomy                             = wc_attribute_taxonomy_name( $attribute->attribute_name );
		$attribute_taxonomy_filter_was_called = false;
		$spy                                  = static function ( array $attribute_taxonomies ) use ( &$attribute_taxonomy_filter_was_called ): array {
			$attribute_taxonomy_filter_was_called = true;

			return $attribute_taxonomies;
		};

		add_filter( 'woocommerce_attribute_taxonomies', $spy );
		$this->reset_woocommerce_attribute_taxonomies();

		self::assertSame( 'pa_czvet', $taxonomy );
		self::assertSame( 'pa_czvet', sanitize_title( $taxonomy ) );
		self::assertTrue( $attribute_taxonomy_filter_was_called );

		remove_filter( 'woocommerce_attribute_taxonomies', $spy );
	}

	/**
	 * Load the real WooCommerce plugin for this isolated test process.
	 *
	 * @return void
	 */
	private function load_woocommerce(): void {
		if ( function_exists( 'WC' ) ) {
			return;
		}

		$plugin_file = getenv( 'CYR2LAT_WC_PLUGIN_FILE' );
		$plugin_file = $plugin_file && file_exists( $plugin_file )
			? $plugin_file
			: 'C:/laragon/www/test/wp-content/plugins/woocommerce/woocommerce.php';

		if ( file_exists( $plugin_file ) ) {
			require_once $plugin_file;
		}
	}

	/**
	 * Install WooCommerce database tables needed by wc_create_attribute().
	 *
	 * @return void
	 */
	private function install_woocommerce_tables(): void {
		if ( class_exists( 'WC_Install' ) ) {
			WC_Install::create_tables();
			update_option( 'woocommerce_version', WC()->version );
		}
	}

	/**
	 * Delete WooCommerce attribute taxonomies created by tests.
	 *
	 * @return void
	 */
	private function delete_woocommerce_attribute_taxonomies(): void {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}woocommerce_attribute_taxonomies" );
	}

	/**
	 * Reset WooCommerce attribute taxonomy caches.
	 *
	 * @return void
	 */
	private function reset_woocommerce_attribute_taxonomies(): void {
		delete_transient( 'wc_attribute_taxonomies' );
		WC_Cache_Helper::invalidate_cache_group( 'woocommerce-attributes' );
	}

	/**
	 * Add a spy for WordPress' sanitize_title filter.
	 *
	 * @param array<int, array{title: string, raw_title: string, context: string}> $calls Recorded filter calls.
	 *
	 * @return callable
	 */
	private function add_sanitize_title_spy( array &$calls ): callable {
		$spy = static function ( $title, $raw_title, $context ) use ( &$calls ) {
			$calls[] = [
				'title'     => (string) $title,
				'raw_title' => (string) $raw_title,
				'context'   => (string) $context,
			];

			return $title;
		};

		add_filter( 'sanitize_title', $spy, 1, 3 );

		return $spy;
	}

	/**
	 * Get a WooCommerce attribute taxonomy row.
	 *
	 * @param int $attribute_id Attribute ID.
	 *
	 * @return object
	 */
	private function get_woocommerce_attribute_taxonomy( int $attribute_id ): object {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$attribute = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}woocommerce_attribute_taxonomies WHERE attribute_id = %d",
				$attribute_id
			)
		);

		self::assertIsObject( $attribute );

		return $attribute;
	}
}
