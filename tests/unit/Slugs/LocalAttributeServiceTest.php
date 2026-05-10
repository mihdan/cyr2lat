<?php
/**
 * LocalAttributeServiceTest class file.
 *
 * @package cyr-to-lat
 */

namespace CyrToLat\Tests\Unit\Slugs;

use CyrToLat\Tests\Unit\CyrToLatTestCase;

/**
 * Class LocalAttributeServiceTest
 *
 * @group slugs
 */
class LocalAttributeServiceTest extends CyrToLatTestCase {

	/**
	 * Test is_local_attribute() rejects global attribute keys.
	 *
	 * @return void
	 */
	public function test_is_local_attribute_rejects_global_attribute_key(): void {
		$subject = new TestLocalAttributeService();

		self::assertFalse( $subject->is_local_attribute( 'pa_razmer' ) );
	}

	/**
	 * Test is_local_attribute() rejects import action.
	 *
	 * @return void
	 */
	public function test_is_local_attribute_rejects_import_action(): void {
		$subject = new TestLocalAttributeService(
			[
				'action' => 'woocommerce_do_ajax_product_import',
			]
		);

		self::assertFalse( $subject->is_local_attribute( 'Размер' ) );
	}

	/**
	 * Test is_local_attribute() detects AJAX attribute save action.
	 *
	 * @return void
	 */
	public function test_is_local_attribute_detects_ajax_attribute_save_action(): void {
		$subject = new TestLocalAttributeService(
			[
				'action' => 'woocommerce_save_attributes',
				'data'   => 'attribute_names%5B0%5D=%D0%A0%D0%B0%D0%B7%D0%BC%D0%B5%D1%80',
			]
		);

		self::assertTrue( $subject->is_local_attribute( 'Размер' ) );
	}

	/**
	 * Test is_local_attribute() detects edit post action.
	 *
	 * @return void
	 */
	public function test_is_local_attribute_detects_edit_post_action(): void {
		$subject = new TestLocalAttributeService(
			[
				'action'          => 'editpost',
				'attribute_names' => [ 'Размер' ],
			]
		);

		self::assertTrue( $subject->is_local_attribute( 'Размер' ) );
	}

	/**
	 * Test is_local_attribute() detects variable add to cart action.
	 *
	 * @return void
	 */
	public function test_is_local_attribute_detects_variable_add_to_cart_action(): void {
		$subject = new TestLocalAttributeService(
			[],
			[
				'woocommerce_variable_add_to_cart' => true,
			],
			[],
			[
				'%d1%80%d0%b0%d0%b7%d0%bc%d0%b5%d1%80' => true,
			]
		);

		self::assertTrue( $subject->is_local_attribute( 'Размер' ) );
	}

	/**
	 * Test is_local_attribute() detects cart session loading.
	 *
	 * @return void
	 */
	public function test_is_local_attribute_detects_cart_session_loading(): void {
		$subject = new TestLocalAttributeService(
			[],
			[],
			[
				'woocommerce_load_cart_from_session' => 1,
			]
		);

		self::assertTrue( $subject->is_local_attribute( 'Размер' ) );
	}

	/**
	 * Test is_local_attribute() detects the frontend request attribute key.
	 *
	 * @return void
	 */
	public function test_is_local_attribute_detects_frontend_request_attribute_key(): void {
		$subject = new TestLocalAttributeService(
			[
				'attribute_%d1%80%d0%b0%d0%b7%d0%bc%d0%b5%d1%80' => 'XL',
			]
		);

		self::assertTrue( $subject->is_local_attribute( 'Размер' ) );
	}
}
