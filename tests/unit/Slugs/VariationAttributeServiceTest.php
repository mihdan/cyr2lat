<?php
/**
 * VariationAttributeServiceTest class file.
 *
 * @package cyr-to-lat
 */

namespace CyrToLat\Tests\Unit\Slugs;

use CyrToLat\Main;
use CyrToLat\Slugs\VariationAttributeService;
use CyrToLat\Tests\Unit\CyrToLatTestCase;
use Mockery;

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
		$subject = new VariationAttributeService( Mockery::mock( Main::class ) );

		self::assertTrue( $subject->is_global_variation_attribute_key( 'pa_color' ) );
		self::assertTrue( $subject->is_global_variation_attribute_key( 'attribute_pa_color' ) );
	}

	/**
	 * Test is_global_variation_attribute_key() rejects local keys.
	 *
	 * @return void
	 */
	public function test_is_global_variation_attribute_key_rejects_local_keys(): void {
		$subject = new VariationAttributeService( Mockery::mock( Main::class ) );

		self::assertFalse( $subject->is_global_variation_attribute_key( 'color' ) );
		self::assertFalse( $subject->is_global_variation_attribute_key( 'attribute_color' ) );
	}

	/**
	 * Test encoded_product_attribute_key().
	 *
	 * @return void
	 */
	public function test_encoded_product_attribute_key(): void {
		$subject = new VariationAttributeService( Mockery::mock( Main::class ) );

		self::assertSame( '%d1%86%d0%b2%d0%b5%d1%82', $subject->encoded_product_attribute_key( 'Цвет' ) );
	}

	/**
	 * Test local_variation_request_key().
	 *
	 * @return void
	 */
	public function test_local_variation_request_key(): void {
		$subject = new VariationAttributeService( Mockery::mock( Main::class ) );

		self::assertSame( 'attribute_цвет', $subject->local_variation_request_key( 'Цвет' ) );
		self::assertSame( 'attribute_цвет', $subject->local_variation_request_key( 'attribute_Цвет' ) );
	}

	/**
	 * Test encoded_local_variation_request_keys().
	 *
	 * @return void
	 */
	public function test_encoded_local_variation_request_keys(): void {
		$subject = new VariationAttributeService( Mockery::mock( Main::class ) );

		self::assertSame(
			[ 'attribute_%D1%86%D0%B2%D0%B5%D1%82', 'attribute_%d1%86%d0%b2%d0%b5%d1%82' ],
			$subject->encoded_local_variation_request_keys( 'Цвет' )
		);
	}

	/**
	 * Test normalized_local_variation_request_key().
	 *
	 * @return void
	 */
	public function test_normalized_local_variation_request_key(): void {
		$main = Mockery::mock( Main::class );
		$main->shouldReceive( 'transliterate' )->andReturnUsing( [ $this, 'normalize_key' ] );
		$main->shouldReceive( 'sanitize_explicit_slug' )->andReturnUsing( [ $this, 'sanitize_key' ] );

		$subject = new VariationAttributeService( $main );

		self::assertSame( 'attribute_czvet', $subject->normalized_local_variation_request_key( 'Цвет' ) );
		self::assertSame( 'attribute_czvet', $subject->normalized_local_variation_request_key( 'attribute_Цвет' ) );
	}

	/**
	 * Test normalize_variation_attribute_key().
	 *
	 * @return void
	 */
	public function test_normalize_variation_attribute_key(): void {
		$main = Mockery::mock( Main::class );
		$main->shouldReceive( 'transliterate' )->andReturnUsing( [ $this, 'normalize_key' ] );
		$main->shouldReceive( 'sanitize_explicit_slug' )->andReturnUsing( [ $this, 'sanitize_key' ] );

		$subject = new VariationAttributeService( $main );

		self::assertSame( 'czvet', $subject->normalize_variation_attribute_key( 'Цвет' ) );
		self::assertSame( 'czvet', $subject->normalize_variation_attribute_key( 'attribute_Цвет' ) );
		self::assertSame( 'czvet', $subject->normalize_variation_attribute_key( '%D1%86%D0%B2%D0%B5%D1%82' ) );
		self::assertSame( 'pa_color', $subject->normalize_variation_attribute_key( 'attribute_pa_color' ) );
	}

	/**
	 * Test normalize_available_variation_attributes().
	 *
	 * @return void
	 */
	public function test_normalize_available_variation_attributes_uses_legacy_raw_meta_value(): void {
		$main = Mockery::mock( Main::class );
		$main->shouldReceive( 'transliterate' )->andReturnUsing( [ $this, 'normalize_key' ] );
		$main->shouldReceive( 'sanitize_explicit_slug' )->andReturnUsing( [ $this, 'sanitize_key' ] );

		$variation = new class() {
			/**
			 * Get ID.
			 *
			 * @return int
			 */
			public function get_id(): int {
				return 123;
			}
		};

		\WP_Mock::userFunction( 'get_post_meta' )
			->with( 123 )
			->andReturn(
				[
					'attribute_%d1%86%d0%b2%d0%b5%d1%82' => [ 'Красный' ],
				]
			);

		$subject = new VariationAttributeService( $main );

		$result = $subject->normalize_available_variation_attributes(
			[
				'attributes' => [
					'attribute_%d1%86%d0%b2%d0%b5%d1%82' => '',
				],
			],
			$variation
		);

		self::assertSame(
			[
				'attributes' => [
					'attribute_czvet' => 'Красный',
				],
			],
			$result
		);
	}

	/**
	 * Test normalize_read_variation_attributes().
	 *
	 * @return void
	 */
	public function test_normalize_read_variation_attributes_uses_legacy_raw_meta_value(): void {
		$main = Mockery::mock( Main::class );
		$main->shouldReceive( 'transliterate' )->andReturnUsing( [ $this, 'normalize_key' ] );
		$main->shouldReceive( 'sanitize_explicit_slug' )->andReturnUsing( [ $this, 'sanitize_key' ] );

		$variation = new class() {
			/**
			 * Data.
			 *
			 * @var array
			 */
			private array $data = [
				'attributes' => [
					'czvet' => '',
				],
			];

			/**
			 * Changes.
			 *
			 * @var array
			 */
			private array $changes = [
				'attributes' => [
					'czvet' => '',
				],
			];

			/**
			 * Get ID.
			 *
			 * @return int
			 */
			public function get_id(): int {
				return 123;
			}

			/**
			 * Get type.
			 *
			 * @return string
			 */
			public function get_type(): string {
				return 'variation';
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
			->with( 123 )
			->andReturn(
				[
					'attribute_%d1%86%d0%b2%d0%b5%d1%82' => [ 'Красный' ],
				]
			);

		$subject = new VariationAttributeService( $main );

		self::assertTrue( $subject->normalize_read_variation_attributes( $variation ) );
		self::assertSame( [ 'czvet' => 'Красный' ], $variation->get_attributes() );
		self::assertSame( [], $variation->get_changes() );
	}

	/**
	 * Test normalize_read_variation_attribute_array().
	 *
	 * @return void
	 */
	public function test_normalize_read_variation_attribute_array_uses_legacy_raw_meta_value(): void {
		$main = Mockery::mock( Main::class );
		$main->shouldReceive( 'transliterate' )->andReturnUsing( [ $this, 'normalize_key' ] );
		$main->shouldReceive( 'sanitize_explicit_slug' )->andReturnUsing( [ $this, 'sanitize_key' ] );

		$variation = new class() {
			/**
			 * Get ID.
			 *
			 * @return int
			 */
			public function get_id(): int {
				return 5954;
			}
		};

		\WP_Mock::userFunction( 'get_post_meta' )
			->with( 5954 )
			->andReturn(
				[
					'attribute_%d0%b0%d1%80%d1%82' => [ '00074-1' ],
				]
			);

		$subject = new VariationAttributeService( $main );

		self::assertSame(
			[ 'art' => '00074-1' ],
			$subject->normalize_read_variation_attribute_array(
				$variation,
				[
					'art' => '',
				]
			)
		);
	}

	/**
	 * Test is_saved_local_variation_attribute_name().
	 *
	 * @return void
	 */
	public function test_is_saved_local_variation_attribute_name(): void {
		$subject = new VariationAttributeService( Mockery::mock( Main::class ) );

		\WP_Mock::userFunction( 'get_post_meta' )
			->with( 123, '_product_attributes', true )
			->andReturn(
				[
					'czvet' => [
						'name'         => 'Цвет',
						'is_taxonomy'  => 0,
						'is_variation' => 1,
					],
				]
			);

		self::assertTrue( $subject->is_saved_local_variation_attribute_name( 'Цвет', 123 ) );
	}

	/**
	 * Test is_saved_local_variation_attribute_name() rejects non-variation local attributes.
	 *
	 * @return void
	 */
	public function test_is_saved_local_variation_attribute_name_rejects_non_variation_attribute(): void {
		$subject = new VariationAttributeService( Mockery::mock( Main::class ) );

		\WP_Mock::userFunction( 'get_post_meta' )
			->with( 123, '_product_attributes', true )
			->andReturn(
				[
					'czvet' => [
						'name'         => 'Цвет',
						'is_taxonomy'  => 0,
						'is_variation' => 0,
					],
				]
			);

		self::assertFalse( $subject->is_saved_local_variation_attribute_name( 'Цвет', 123 ) );
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
				'а' => 'a',
				'р' => 'r',
				'т' => 't',
				'Ц' => 'CZ',
				'ц' => 'cz',
				'в' => 'v',
				'е' => 'e',
			]
		);
	}

	/**
	 * Sanitize key.
	 *
	 * @param string $key Key.
	 *
	 * @return string
	 */
	public function sanitize_key( string $key ): string {
		return strtolower( preg_replace( '/[^a-zA-Z0-9_-]+/', '', $this->normalize_key( rawurldecode( $key ) ) ) );
	}
}
