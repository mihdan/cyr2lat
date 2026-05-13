<?php
/**
 * WooCommerceLocalAttributeIntegrationTest class file.
 *
 * @package cyr-to-lat
 */

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpUndefinedClassInspection */
/** @noinspection PhpUndefinedFunctionInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

namespace CyrToLat\Tests\Integration\Slugs\WooCommerce;

use CyrToLat\Tests\Integration\WooCommerceWPTestCase;
use WC_Product_Attribute;
use WC_Product_Simple;

/**
 * Class WooCommerceLocalAttributeIntegrationTest
 *
 * @group integration
 * @group woocommerce
 */
class WooCommerceLocalAttributeIntegrationTest extends WooCommerceWPTestCase {

	/**
	 * Set up an allowed admin product request context.
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		if (
			! function_exists( 'WC' ) ||
			! class_exists( WC_Product_Simple::class ) ||
			! class_exists( WC_Product_Attribute::class )
		) {
			self::markTestSkipped( 'WooCommerce product classes are not loaded in the integration test environment.' );
		}

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
	 * Test that a WooCommerce product save stores a Cyrillic local attribute under the current transliterated key.
	 *
	 * @return void
	 * @noinspection PhpUndefinedClassInspection
	 */
	public function test_product_save_stores_cyrillic_local_attribute_under_transliterated_key(): void {
		$sanitize_title_calls = [];
		$spy                  = $this->add_sanitize_title_spy( $sanitize_title_calls );

		$product_id = $this->create_product_with_local_attribute(
			'Цвет',
			[
				'Красный',
				'Синий',
			]
		);

		remove_filter( 'sanitize_title', $spy, 1 );

		$product = new WC_Product_Simple( $product_id );

		$attributes = $product->get_attributes( 'edit' );

		self::assertArrayHasKey( 'czvet', $attributes );
		self::assertArrayNotHasKey( 'цвет', $attributes );
		self::assertSame( 'Цвет', $attributes['czvet']->get_name() );
		self::assertSame( [ 'Красный', 'Синий' ], $attributes['czvet']->get_options() );
		self::assertFalse( $attributes['czvet']->is_taxonomy() );

		$stored_attributes = get_post_meta( $product_id, '_product_attributes', true );

		self::assertIsArray( $stored_attributes );
		self::assertArrayHasKey( 'czvet', $stored_attributes );
		self::assertSame( 'Цвет', $stored_attributes['czvet']['name'] );
		self::assertSame( 0, $stored_attributes['czvet']['is_taxonomy'] );

		self::assertContains(
			[
				'title'     => 'Цвет',
				'raw_title' => 'Цвет',
				'context'   => 'save',
			],
			$sanitize_title_calls
		);
	}

	/**
	 * Test that a Latin local attribute key is preserved by WooCommerce product save.
	 *
	 * @return void
	 */
	public function test_product_save_preserves_latin_local_attribute_key(): void {
		$product_id = $this->create_product_with_local_attribute(
			'color',
			[
				'Красный',
				'Синий',
			]
		);

		$product = new WC_Product_Simple( $product_id );

		$attributes = $product->get_attributes( 'edit' );

		self::assertArrayHasKey( 'color', $attributes );
		self::assertSame( 'color', $attributes['color']->get_name() );
		self::assertSame( [ 'Красный', 'Синий' ], $attributes['color']->get_options() );
	}

	/**
	 * Test that explicit local attribute normalization does not require broad sanitize_title bridge.
	 *
	 * @return void
	 */
	public function test_product_save_normalizes_cyrillic_local_attribute_without_sanitize_title_bridge(): void {
		remove_filter( 'sanitize_title', [ cyr_to_lat(), 'sanitize_title' ], 9 );

		try {
			$product_id = $this->create_product_with_local_attribute(
				'Цвет',
				[
					'Красный',
					'Синий',
				]
			);
		} finally {
			add_filter( 'sanitize_title', [ cyr_to_lat(), 'sanitize_title' ], 9, 3 );
		}

		$product = new WC_Product_Simple( $product_id );

		$attributes = $product->get_attributes( 'edit' );

		self::assertArrayHasKey( 'czvet', $attributes );
		self::assertArrayNotHasKey( '%d1%86%d0%b2%d0%b5%d1%82', $attributes );
		self::assertSame( 'Цвет', $attributes['czvet']->get_name() );

		$stored_attributes = get_post_meta( $product_id, '_product_attributes', true );

		self::assertIsArray( $stored_attributes );
		self::assertArrayHasKey( 'czvet', $stored_attributes );
		self::assertArrayNotHasKey( '%d1%86%d0%b2%d0%b5%d1%82', $stored_attributes );
	}

	/**
	 * Create a simple product with a local attribute.
	 *
	 * @param string        $attribute_name Attribute name.
	 * @param array<string> $options        Attribute options.
	 *
	 * @return int
	 * @noinspection PhpSameParameterValueInspection
	 */
	private function create_product_with_local_attribute( string $attribute_name, array $options ): int {
		$attribute = new WC_Product_Attribute();
		$attribute->set_id( 0 );
		$attribute->set_name( $attribute_name );
		$attribute->set_options( $options );
		$attribute->set_position( 0 );
		$attribute->set_visible( true );
		$attribute->set_variation( false );

		$product = new WC_Product_Simple();
		$product->set_name( 'Local attribute product' );
		$product->set_status( 'publish' );
		$product->set_regular_price( '10' );
		$product->set_attributes( [ $attribute ] );

		return $product->save();
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
}
