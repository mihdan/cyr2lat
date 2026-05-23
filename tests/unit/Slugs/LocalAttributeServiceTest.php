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

	/**
	 * Test is_local_attribute() detects the normalized frontend request attribute key.
	 *
	 * @return void
	 */
	public function test_is_local_attribute_detects_frontend_normalized_request_attribute_key(): void {
		$subject = new TestLocalAttributeService(
			[
				'attribute_razmer' => 'XL',
			]
		);

		self::assertTrue( $subject->is_local_attribute( 'Размер' ) );
	}

	/**
	 * Test normalize_product_attribute_array() prefers a legacy stored key over a display name.
	 *
	 * @return void
	 */
	public function test_normalize_product_attribute_array_prefers_legacy_stored_key(): void {
		$subject = new TestLocalAttributeService();

		$attribute = new class() {
			/**
			 * Check if attribute is a taxonomy.
			 *
			 * @return bool
			 */
			public function is_taxonomy(): bool {
				return false;
			}

			/**
			 * Get name.
			 *
			 * @return string
			 */
			public function get_name(): string {
				return 'Арт.';
			}
		};

		self::assertSame(
			[ 'art' => $attribute ],
			$subject->normalize_product_attribute_array(
				[
					'%d0%b0%d1%80%d1%82' => $attribute,
				]
			)
		);
	}

	/**
	 * Test normalize_read_product_attributes() restores a legacy stored key lost by WooCommerce read.
	 *
	 * @return void
	 */
	public function test_normalize_read_product_attributes_restores_legacy_stored_key(): void {
		$subject = new TestLocalAttributeService();

		$attribute = new class() {
			/**
			 * Check if attribute is a taxonomy.
			 *
			 * @return bool
			 */
			public function is_taxonomy(): bool {
				return false;
			}

			/**
			 * Get name.
			 *
			 * @return string
			 */
			public function get_name(): string {
				return 'Арт.';
			}
		};

		$product = new class( $attribute ) {
			/**
			 * Data.
			 *
			 * @var array
			 */
			private array $data;

			/**
			 * Changes.
			 *
			 * @var array
			 */
			private array $changes = [
				'attributes' => [],
			];

			/**
			 * Constructor.
			 *
			 * @param object $attribute Attribute.
			 */
			public function __construct( object $attribute ) {
				$this->data = [
					'attributes' => [
						'art.' => $attribute,
					],
				];
			}

			/**
			 * Get ID.
			 *
			 * @return int
			 */
			public function get_id(): int {
				return 5953;
			}

			/**
			 * Get attributes.
			 *
			 * @return array
			 */
			public function get_attributes(): array {
				return $this->data['attributes'];
			}

			/**
			 * Get changes.
			 *
			 * @return array
			 */
			public function get_changes(): array {
				return $this->changes;
			}
		};

		\WP_Mock::userFunction( 'get_post_meta' )
			->with( 5953, '_product_attributes', true )
			->andReturn(
				[
					'%d0%b0%d1%80%d1%82' => [
						'name'        => 'Арт.',
						'is_taxonomy' => 0,
					],
				]
			);

		self::assertTrue( $subject->normalize_read_product_attributes( $product ) );
		self::assertSame( [ 'art' => $attribute ], $product->get_attributes() );
		self::assertSame( [], $product->get_changes() );
	}
}
