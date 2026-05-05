<?php
/**
 * WooCommerceVariationAddToCartIntegrationTest class file.
 *
 * @package cyr-to-lat
 */

namespace CyrToLat\Tests\Integration;

use WC_Form_Handler;
use WC_Install;
use WC_Product_Attribute;
use WC_Product_Variable;
use WC_Product_Variation;

/**
 * Class WooCommerceVariationAddToCartIntegrationTest
 *
 * @group integration
 * @group woocommerce
 */
class WooCommerceVariationAddToCartIntegrationTest extends PluginWPTestCase {

	/**
	 * WooCommerce plugin path relative to WP_PLUGIN_DIR.
	 *
	 * @var string
	 */
	protected static string $plugin = 'woocommerce/woocommerce.php';

	/**
	 * Set up an allowed admin product request context.
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		if (
			! function_exists( 'WC' ) ||
			! function_exists( 'wc_load_cart' ) ||
			! class_exists( WC_Form_Handler::class ) ||
			! class_exists( WC_Product_Variable::class ) ||
			! class_exists( WC_Product_Variation::class ) ||
			! class_exists( WC_Product_Attribute::class )
		) {
			self::markTestSkipped( 'WooCommerce product and cart classes are not loaded in the integration test environment.' );
		}

		$this->install_woocommerce_tables();
		$this->init_woocommerce();
		$this->load_woocommerce_template_functions();
		wp_cache_flush();

		set_current_screen( 'post' );
		cyr_to_lat()->init_all();

		update_option( 'woocommerce_cart_redirect_after_add', 'no' );
	}

	/**
	 * Tear down test globals.
	 *
	 * @return void
	 */
	public function tearDown(): void {
		if ( WC()->cart ) {
			WC()->cart->empty_cart();
		}

		wc_clear_notices();

		unset( $GLOBALS['product'] );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$_REQUEST = [];

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$_POST = [];

		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		unset( $GLOBALS['current_screen'] );

		wp_cache_flush();

		parent::tearDown();
	}

	/**
	 * Test that the frontend variation form uses the current transliterated local attribute request key.
	 *
	 * @return void
	 */
	public function test_variation_form_uses_transliterated_local_attribute_request_key(): void {
		[ $product_id ] = $this->create_variable_product_with_cyrillic_local_attribute();

		$html = $this->render_variable_add_to_cart_form( $product_id );

		self::assertStringContainsString( 'name="attribute_czvet"', $html );
		self::assertStringContainsString( 'data-attribute_name="attribute_czvet"', $html );
		self::assertStringNotContainsString( 'name="attribute_%d1%86', strtolower( $html ) );
	}

	/**
	 * Test current behavior: WooCommerce frontend add-to-cart rejects the rendered local attribute request key.
	 *
	 * @return void
	 */
	public function test_frontend_add_to_cart_currently_rejects_rendered_cyrillic_local_attribute_key(): void {
		[ $product_id, $variation_id ] = $this->create_variable_product_with_cyrillic_local_attribute();
		$request_key                   = $this->get_rendered_variation_attribute_request_key( $product_id );

		$this->load_cart();

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$_REQUEST = [
			'add-to-cart'  => (string) $product_id,
			'variation_id' => (string) $variation_id,
			'quantity'     => '1',
			$request_key   => 'Красный',
		];

		WC_Form_Handler::add_to_cart_action( false );

		self::assertSame( 'attribute_czvet', $request_key );
		self::assertSame( 1, wc_notice_count( 'error' ) );
		$error_notices = wc_get_notices( 'error' );
		self::assertSame( 'Цвет is a required field', $error_notices[0]['notice'] );
		self::assertSame( 0, WC()->cart->get_cart_contents_count() );
	}

	/**
	 * Install WooCommerce database tables needed by product and cart flows.
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

		if ( class_exists( 'WC_Post_Types' ) ) {
			\WC_Post_Types::register_taxonomies();
			\WC_Post_Types::register_post_types();
		}

		if ( ! did_action( 'woocommerce_after_register_taxonomy' ) ) {
			do_action( 'woocommerce_after_register_taxonomy' );
		}

		if ( ! did_action( 'woocommerce_after_register_post_type' ) ) {
			do_action( 'woocommerce_after_register_post_type' );
		}
	}

	/**
	 * Load WooCommerce cart objects.
	 *
	 * @return void
	 */
	private function load_cart(): void {
		wc_load_cart();
		WC()->cart->empty_cart();
		wc_clear_notices();
	}

	/**
	 * Load WooCommerce frontend template functions needed by the variation form action.
	 *
	 * @return void
	 */
	private function load_woocommerce_template_functions(): void {
		if ( ! function_exists( 'woocommerce_variable_add_to_cart' ) ) {
			WC()->include_template_functions();
		}

		if ( ! has_action( 'woocommerce_variable_add_to_cart', 'woocommerce_variable_add_to_cart' ) ) {
			add_action( 'woocommerce_variable_add_to_cart', 'woocommerce_variable_add_to_cart', 30 );
		}
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
		$product->set_name( 'Variable local attribute product' );
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
	 * Render WooCommerce's variable add-to-cart form for a product.
	 *
	 * @param int $product_id Product ID.
	 *
	 * @return string
	 */
	private function render_variable_add_to_cart_form( int $product_id ): string {
		$GLOBALS['product'] = new WC_Product_Variable( $product_id );

		cyr_to_lat()->woocommerce_before_template_part_filter();

		ob_start();
		do_action( 'woocommerce_variable_add_to_cart' );
		$html = (string) ob_get_clean();

		cyr_to_lat()->woocommerce_after_template_part_filter();

		return $html;
	}

	/**
	 * Get the local attribute request key rendered by WooCommerce's variable add-to-cart form.
	 *
	 * @param int $product_id Product ID.
	 *
	 * @return string
	 */
	private function get_rendered_variation_attribute_request_key( int $product_id ): string {
		$html = $this->render_variable_add_to_cart_form( $product_id );

		self::assertMatchesRegularExpression( '/name="(attribute_[^"]+)"/', $html );
		preg_match( '/name="(attribute_[^"]+)"/', $html, $matches );

		return $matches[1];
	}
}
