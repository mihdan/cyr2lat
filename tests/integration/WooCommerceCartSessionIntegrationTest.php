<?php
/**
 * WooCommerceCartSessionIntegrationTest class file.
 *
 * @package cyr-to-lat
 */

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpUndefinedClassInspection */
/** @noinspection PhpUndefinedFunctionInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

namespace CyrToLat\Tests\Integration;

use WC_Product_Attribute;
use WC_Product_Variable;
use WC_Product_Variation;

/**
 * Class WooCommerceCartSessionIntegrationTest
 *
 * @group integration
 * @group woocommerce
 */
class WooCommerceCartSessionIntegrationTest extends WooCommerceWPTestCase {

	/**
	 * Set up an allowed product and cart request context.
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		if (
			! function_exists( 'WC' ) ||
			! function_exists( 'wc_load_cart' ) ||
			! class_exists( WC_Product_Variable::class ) ||
			! class_exists( WC_Product_Variation::class ) ||
			! class_exists( WC_Product_Attribute::class )
		) {
			self::markTestSkipped( 'WooCommerce product and cart classes are not loaded in the integration test environment.' );
		}

		$this->load_cart();

		set_current_screen( 'post' );
		cyr_to_lat()->init_all();
	}

	/**
	 * Test that WooCommerce restores a local attribute variation item from a session.
	 *
	 * @return void
	 */
	public function test_cart_session_reload_restores_cyrillic_local_attribute_variation_item(): void {
		[ $product_id, $variation_id ] = $this->create_variable_product_with_cyrillic_local_attribute();

		$variation = [
			'attribute_czvet' => 'Красный',
		];

		$cart_item_key = $this->store_cart_item_in_session( $product_id, $variation_id, $variation );
		$action_ran    = false;
		$action_spy    = static function () use ( &$action_ran ): void {
			$action_ran = true;
		};

		add_action( 'woocommerce_load_cart_from_session', $action_spy, 99 );

		$cart = WC()->cart->get_cart();

		remove_action( 'woocommerce_load_cart_from_session', $action_spy, 99 );

		self::assertTrue( $action_ran );
		self::assertArrayHasKey( $cart_item_key, $cart );
		self::assertSame( $product_id, $cart[ $cart_item_key ]['product_id'] );
		self::assertSame( $variation_id, $cart[ $cart_item_key ]['variation_id'] );
		self::assertSame( 1, $cart[ $cart_item_key ]['quantity'] );
		self::assertSame( $variation, $cart[ $cart_item_key ]['variation'] );
		self::assertSame( 1, WC()->cart->get_cart_contents_count() );
	}

	/**
	 * Create a variable product with a Cyrillic local attribute and one variation.
	 *
	 * @return array{int, int}
	 */
	private function create_variable_product_with_cyrillic_local_attribute(): array {
		$attribute = new WC_Product_Attribute();
		$attribute->set_id( 0 );
		$attribute->set_name( 'Цвет' );
		$attribute->set_options( [ 'Красный', 'Синий' ] );
		$attribute->set_position( 0 );
		$attribute->set_visible( true );
		$attribute->set_variation( true );

		$product = new WC_Product_Variable();
		$product->set_name( 'Variable local attribute session product' );
		$product->set_status( 'publish' );
		$product->set_attributes( [ $attribute ] );

		$product_id = $product->save();

		$variation = new WC_Product_Variation();
		$variation->set_parent_id( $product_id );
		$variation->set_status( 'publish' );
		$variation->set_regular_price( '10' );
		$variation->set_attributes(
			[
				'czvet' => 'Красный',
			]
		);

		$variation_id = $variation->save();

		WC_Product_Variable::sync( $product_id );
		wc_delete_product_transients( $product_id );

		return [ $product_id, $variation_id ];
	}

	/**
	 * Store a WooCommerce cart item in session and clear the in-memory cart.
	 *
	 * @param int                  $product_id   Parent product ID.
	 * @param int                  $variation_id Variation product ID.
	 * @param array<string,string> $variation    Cart item variation data.
	 *
	 * @return string
	 */
	private function store_cart_item_in_session( int $product_id, int $variation_id, array $variation ): string {
		$cart_item_key = WC()->cart->generate_cart_id( $product_id, $variation_id, $variation );
		$product_data  = new WC_Product_Variation( $variation_id );

		WC()->session->set(
			'cart',
			[
				$cart_item_key => [
					'key'               => $cart_item_key,
					'product_id'        => $product_id,
					'variation_id'      => $variation_id,
					'variation'         => $variation,
					'quantity'          => 1,
					'data_hash'         => wc_get_cart_item_data_hash( $product_data ),
					'line_tax_data'     => [
						'subtotal' => [],
						'total'    => [],
					],
					'line_subtotal'     => 10,
					'line_subtotal_tax' => 0,
					'line_total'        => 10,
					'line_tax'          => 0,
				],
			]
		);

		WC()->cart->set_cart_contents( [] );

		return $cart_item_key;
	}
}
