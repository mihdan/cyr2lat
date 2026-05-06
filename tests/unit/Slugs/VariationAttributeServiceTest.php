<?php
/**
 * VariationAttributeServiceTest class file.
 *
 * @package cyr-to-lat
 */

namespace CyrToLat\Tests\Unit\Slugs;

use CyrToLat\Slugs\VariationAttributeService;
use CyrToLat\Tests\Unit\CyrToLatTestCase;

/**
 * Class VariationAttributeServiceTest
 *
 * @group slugs
 */
class VariationAttributeServiceTest extends CyrToLatTestCase {

	/**
	 * Test is_global_variation_attribute_key() detects global keys.
	 *
	 * @return void
	 */
	public function test_is_global_variation_attribute_key_detects_global_keys(): void {
		$subject = new VariationAttributeService();

		self::assertTrue( $subject->is_global_variation_attribute_key( 'pa_color' ) );
		self::assertTrue( $subject->is_global_variation_attribute_key( 'attribute_pa_color' ) );
	}

	/**
	 * Test is_global_variation_attribute_key() rejects local keys.
	 *
	 * @return void
	 */
	public function test_is_global_variation_attribute_key_rejects_local_keys(): void {
		$subject = new VariationAttributeService();

		self::assertFalse( $subject->is_global_variation_attribute_key( 'color' ) );
		self::assertFalse( $subject->is_global_variation_attribute_key( 'attribute_color' ) );
	}

	/**
	 * Test encoded_product_attribute_key().
	 *
	 * @return void
	 */
	public function test_encoded_product_attribute_key(): void {
		$subject = new VariationAttributeService();

		self::assertSame( '%d1%86%d0%b2%d0%b5%d1%82', $subject->encoded_product_attribute_key( 'Цвет' ) );
	}

	/**
	 * Test local_variation_request_key().
	 *
	 * @return void
	 */
	public function test_local_variation_request_key(): void {
		$subject = new VariationAttributeService();

		self::assertSame( 'attribute_цвет', $subject->local_variation_request_key( 'Цвет' ) );
		self::assertSame( 'attribute_цвет', $subject->local_variation_request_key( 'attribute_Цвет' ) );
	}

	/**
	 * Test encoded_local_variation_request_keys().
	 *
	 * @return void
	 */
	public function test_encoded_local_variation_request_keys(): void {
		$subject = new VariationAttributeService();

		self::assertSame(
			[ 'attribute_%D1%86%D0%B2%D0%B5%D1%82', 'attribute_%d1%86%d0%b2%d0%b5%d1%82' ],
			$subject->encoded_local_variation_request_keys( 'Цвет' )
		);
	}

	/**
	 * Test normalize_variation_attribute_key().
	 *
	 * @return void
	 */
	public function test_normalize_variation_attribute_key(): void {
		$subject = new VariationAttributeService();

		self::assertSame( 'czvet', $subject->normalize_variation_attribute_key( 'Цвет', [ $this, 'normalize_key' ] ) );
		self::assertSame( 'czvet', $subject->normalize_variation_attribute_key( 'attribute_Цвет', [ $this, 'normalize_key' ] ) );
		self::assertSame( 'czvet', $subject->normalize_variation_attribute_key( '%D1%86%D0%B2%D0%B5%D1%82', [ $this, 'normalize_key' ] ) );
		self::assertSame( 'pa_color', $subject->normalize_variation_attribute_key( 'attribute_pa_color', [ $this, 'normalize_key' ] ) );
	}

	/**
	 * Normalize key.
	 *
	 * @param string $key Key.
	 *
	 * @return string
	 */
	public function normalize_key( string $key ): string {
		return strtr(
			$key,
			[
				'Ц' => 'CZ',
				'ц' => 'cz',
				'в' => 'v',
				'е' => 'e',
				'т' => 't',
			]
		);
	}
}
