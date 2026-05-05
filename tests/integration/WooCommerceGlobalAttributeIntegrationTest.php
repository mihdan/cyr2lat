<?php
/**
 * WooCommerceGlobalAttributeIntegrationTest class file.
 *
 * @package cyr-to-lat
 */

namespace CyrToLat\Tests\Integration;

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
	 * Set up an allowed admin request context with isolated WooCommerce stubs.
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		require_once __DIR__ . '/fixtures/woocommerce-global-functions.php';

		set_current_screen( 'edit-tags' );
		cyr_to_lat()->init_all();
	}

	/**
	 * Tear down test globals.
	 *
	 * @return void
	 */
	public function tearDown(): void {
		unset( $GLOBALS['cyr2lat_wc_attribute_taxonomies'], $GLOBALS['current_screen'] );

		parent::tearDown();
	}

	/**
	 * Test that a registered Cyrillic global attribute taxonomy key is not transliterated by Cyr-To-Lat.
	 *
	 * @return void
	 */
	public function test_registered_cyrillic_global_attribute_taxonomy_key_is_not_transliterated(): void {
		$this->set_registered_attribute_taxonomies( [ 'цвет' ] );

		self::assertSame(
			'pa_' . strtolower( rawurlencode( 'цвет' ) ),
			sanitize_title( 'pa_цвет' )
		);
	}

	/**
	 * Test that a registered Cyrillic global attribute name is not transliterated by Cyr-To-Lat.
	 *
	 * @return void
	 */
	public function test_registered_cyrillic_global_attribute_name_is_not_transliterated(): void {
		$this->set_registered_attribute_taxonomies( [ 'цвет' ] );

		self::assertSame(
			strtolower( rawurlencode( 'цвет' ) ),
			sanitize_title( 'цвет' )
		);
	}

	/**
	 * Test that an unregistered Cyrillic value is still transliterated normally.
	 *
	 * @return void
	 */
	public function test_unregistered_cyrillic_value_is_transliterated(): void {
		$this->set_registered_attribute_taxonomies( [ 'color' ] );

		self::assertSame( 'czvet', sanitize_title( 'цвет' ) );
	}

	/**
	 * Test that an already-Latin registered global attribute taxonomy key is preserved.
	 *
	 * @return void
	 */
	public function test_registered_latin_global_attribute_taxonomy_key_is_preserved(): void {
		$this->set_registered_attribute_taxonomies( [ 'color' ] );

		self::assertSame( 'pa_color', sanitize_title( 'pa_color' ) );
	}

	/**
	 * Set registered WooCommerce attribute taxonomy stubs.
	 *
	 * @param string[] $attribute_names Attribute names.
	 *
	 * @return void
	 */
	private function set_registered_attribute_taxonomies( array $attribute_names ): void {
		$GLOBALS['cyr2lat_wc_attribute_taxonomies'] = array_map(
			static function ( string $attribute_name ): object {
				return (object) [
					'attribute_id'      => '1',
					'attribute_name'    => $attribute_name,
					'attribute_label'   => $attribute_name,
					'attribute_type'    => 'select',
					'attribute_orderby' => 'menu_order',
					'attribute_public'  => '1',
				];
			},
			$attribute_names
		);
	}
}
