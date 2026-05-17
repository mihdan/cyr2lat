<?php
/**
 * GlobalAttributeServiceTest class file.
 *
 * @package cyr-to-lat
 */

namespace CyrToLat\Tests\Unit\Slugs;

use CyrToLat\Main;
use CyrToLat\Slugs\GlobalAttributeService;
use CyrToLat\Tests\Unit\CyrToLatTestCase;
use Mockery;
use WP_Mock;

/**
 * Class GlobalAttributeServiceTest
 *
 * @group slugs
 */
class GlobalAttributeServiceTest extends CyrToLatTestCase {

	/**
	 * Test is_attribute_taxonomy() detects registered attribute slug.
	 *
	 * @return void
	 */
	public function test_is_attribute_taxonomy_detects_registered_attribute_slug(): void {
		WP_Mock::userFunction(
			'wc_get_attribute_taxonomies',
			[
				'return' => [
					(object) [
						'attribute_name' => 'razmer',
					],
				],
			]
		);

		$subject = $this->get_subject();

		self::assertTrue( $subject->is_attribute_taxonomy( 'razmer' ) );
		self::assertTrue( $subject->is_attribute_taxonomy( 'pa_razmer' ) );
	}

	/**
	 * Test is_attribute_taxonomy() rejects an unknown attribute slug.
	 *
	 * @return void
	 */
	public function test_is_attribute_taxonomy_rejects_unknown_attribute_slug(): void {
		WP_Mock::userFunction(
			'wc_get_attribute_taxonomies',
			[
				'return' => [
					(object) [
						'attribute_name' => 'razmer',
					],
				],
			]
		);

		$subject = $this->get_subject();

		self::assertFalse( $subject->is_attribute_taxonomy( 'cvet' ) );
	}

	/**
	 * Test should_preserve_attribute_title() rejects non-WooCommerce context.
	 *
	 * @return void
	 */
	public function test_should_preserve_attribute_title_rejects_non_woocommerce_context(): void {
		$subject = $this->get_subject();

		self::assertFalse( $subject->should_preserve_attribute_title( 'razmer' ) );
	}

	/**
	 * Test should_preserve_attribute_title() detects global attribute context.
	 *
	 * @return void
	 */
	public function test_should_preserve_attribute_title_detects_global_attribute_context(): void {
		WP_Mock::userFunction( 'WC' );
		WP_Mock::userFunction(
			'wc_get_attribute_taxonomies',
			[
				'return' => [
					(object) [
						'attribute_name' => 'razmer',
					],
				],
			]
		);

		$subject = $this->get_subject();

		self::assertTrue( $subject->should_preserve_attribute_title( 'pa_razmer' ) );
	}

	/**
	 * Test should_preserve_attribute_title() does not preserve local attribute context.
	 *
	 * @return void
	 */
	public function test_should_preserve_attribute_title_does_not_preserve_local_attribute_context(): void {
		WP_Mock::userFunction( 'WC' );
		WP_Mock::userFunction( 'wc_get_attribute_taxonomies', [ 'return' => [] ] );

		$subject = $this->get_subject();

		self::assertFalse( $subject->should_preserve_attribute_title( 'local-size' ) );
	}

	/**
	 * Test should_preserve_attribute_title() delegates not converted attribute context.
	 *
	 * @return void
	 */
	public function test_should_preserve_attribute_title_delegates_not_converted_attribute_context(): void {
		$product_id = 5;
		$title      = 'old-size';
		$attributes = [
			'old-size' => [ 'name' => 'old-size' ],
		];

		WP_Mock::userFunction( 'WC' );
		WP_Mock::userFunction( 'wc_get_attribute_taxonomies', [ 'return' => [] ] );
		WP_Mock::userFunction( 'get_post_meta' )
			->with( $product_id, '_product_attributes', true )
			->andReturn( $attributes );
		WP_Mock::passthruFunction( 'sanitize_title_with_dashes' );

		$product = Mockery::mock( 'WC_Product' );
		$product->shouldReceive( 'get_id' )->andReturn( $product_id );
		$GLOBALS['product'] = $product;

		$subject = $this->get_subject();

		self::assertTrue( $subject->should_preserve_attribute_title( $title ) );

		unset( $GLOBALS['product'] );
	}

	/**
	 * Test sanitize_title() does nothing in non-WooCommerce context.
	 *
	 * @return void
	 */
	public function test_sanitize_title_does_nothing_in_non_woocommerce_context(): void {
		WP_Mock::userFunction( 'wc_get_attribute_taxonomies', [ 'return' => [] ] );

		$subject = $this->get_subject();

		self::assertNull( $subject->sanitize_title( 'Цвет' ) );
	}

	/**
	 * Test sanitize_title() does nothing for ASCII titles.
	 *
	 * @return void
	 */
	public function test_sanitize_title_does_nothing_for_ascii_title(): void {
		WP_Mock::userFunction( 'WC' );

		$subject = $this->get_subject();

		self::assertNull( $subject->sanitize_title( 'razmer' ) );
	}

	/**
	 * Test sanitize_title() returns null when call stack does not match a WooCommerce attribute flow.
	 *
	 * @return void
	 */
	public function test_sanitize_title_returns_null_for_unknown_call_stack(): void {
		WP_Mock::userFunction( 'wc_get_attribute_taxonomies', [ 'return' => [] ] );

		WP_Mock::userFunction( 'WC' );

		$subject = $this->get_subject();

		self::assertNull( $subject->sanitize_title( 'Цвет' ) );
	}

	/**
	 * Test sanitize_title() bails out on `query` context.
	 *
	 * @return void
	 */
	public function test_sanitize_title_bails_out_on_query_context(): void {
		$subject = $this->get_subject();

		self::assertNull( $subject->sanitize_title( 'Цвет', 'Цвет', 'query' ) );
	}

	/**
	 * Get the subject under test.
	 *
	 * @return GlobalAttributeService
	 */
	private function get_subject(): GlobalAttributeService {
		return new GlobalAttributeService( Mockery::mock( Main::class )->makePartial() );
	}
}
