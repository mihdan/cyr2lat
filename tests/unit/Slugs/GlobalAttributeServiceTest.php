<?php
/**
 * GlobalAttributeServiceTest class file.
 *
 * @package cyr-to-lat
 */

namespace CyrToLat\Tests\Unit\Slugs;

use CyrToLat\Main;
use CyrToLat\Slugs\GlobalAttributeService;
use CyrToLat\Slugs\LocalAttributeService;
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

		$subject = $this->get_subject( Mockery::mock( LocalAttributeService::class ) );

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

		$subject = $this->get_subject( Mockery::mock( LocalAttributeService::class ) );

		self::assertFalse( $subject->is_attribute_taxonomy( 'cvet' ) );
	}

	/**
	 * Test should_preserve_attribute_title() rejects non-WooCommerce context.
	 *
	 * @return void
	 */
	public function test_should_preserve_attribute_title_rejects_non_woocommerce_context(): void {
		$local_attribute_service = Mockery::mock( LocalAttributeService::class );
		$local_attribute_service->shouldNotReceive( 'is_local_attribute' );

		$subject = $this->get_subject( $local_attribute_service );

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

		$local_attribute_service = Mockery::mock( LocalAttributeService::class );

		$subject = $this->get_subject( $local_attribute_service );

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

		$local_attribute_service = Mockery::mock( LocalAttributeService::class );
		$local_attribute_service->shouldNotReceive( 'is_local_attribute' );

		$subject = $this->get_subject( $local_attribute_service );

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

		$local_attribute_service = Mockery::mock( LocalAttributeService::class );
		$local_attribute_service->shouldNotReceive( 'is_local_attribute' );

		$subject = $this->get_subject( $local_attribute_service );

		self::assertTrue( $subject->should_preserve_attribute_title( $title ) );

		unset( $GLOBALS['product'] );
	}

	/**
	 * Get the subject under test.
	 *
	 * @param LocalAttributeService $local_attribute_service Local attribute service.
	 *
	 * @return GlobalAttributeService
	 */
	private function get_subject( LocalAttributeService $local_attribute_service ): GlobalAttributeService {
		return new GlobalAttributeService( Mockery::mock( Main::class )->makePartial(), $local_attribute_service );
	}
}
