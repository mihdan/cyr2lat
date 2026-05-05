<?php
/**
 * TransliteratorTest class file.
 *
 * @package cyr-to-lat
 */

namespace CyrToLat\Tests\Unit\Transliteration;

use CyrToLat\Settings\Settings;
use CyrToLat\Tests\Unit\CyrToLatTestCase;
use CyrToLat\Transliteration\Transliterator;
use Mockery;

/**
 * Class TransliteratorTest
 *
 * @group transliteration
 */
class TransliteratorTest extends CyrToLatTestCase {

	/**
	 * Test transliterate().
	 *
	 * @return void
	 */
	public function test_transliterate(): void {
		$subject = $this->create_subject();

		self::assertSame(
			'Privet, mir!',
			$subject->transliterate(
				'Привет, мир!',
				[
					'П' => 'P',
					'р' => 'r',
					'и' => 'i',
					'в' => 'v',
					'е' => 'e',
					'т' => 't',
					'м' => 'm',
				]
			)
		);
	}

	/**
	 * Test transliterate() with empty string.
	 *
	 * @return void
	 */
	public function test_transliterate_with_empty_string(): void {
		$subject = $this->create_subject();

		self::assertSame( '', $subject->transliterate( '', [ 'я' => 'ya' ] ) );
	}

	/**
	 * Test transliterate() fixes macOS decomposed Cyrillic characters.
	 *
	 * @return void
	 */
	public function test_transliterate_fixes_mac_string(): void {
		$subject = $this->create_subject();
		$table   = $this->get_conversion_table( 'ru_RU' );

		self::assertSame(
			'YO',
			$subject->transliterate( urldecode( '%d0%95%cc%88' ), $table )
		);
	}

	/**
	 * Test split_chinese_string().
	 *
	 * @param string $str      String.
	 * @param string $expected Expected result.
	 *
	 * @dataProvider dp_test_split_chinese_string
	 */
	public function test_split_chinese_string( string $str, string $expected ): void {
		$table = $this->get_conversion_table( 'zh_CN' );
		$table = $this->transpose_chinese_table( $table );

		$subject = $this->create_subject( true );

		self::assertSame( $expected, $subject->split_chinese_string( $str, $table ) );
	}

	/**
	 * Data provider for test_split_chinese_string().
	 *
	 * @return array
	 */
	public static function dp_test_split_chinese_string(): array {
		return [
			'general'     => [
				'我是俄罗斯人',
				'-我--是--俄--罗--斯--人-',
			],
			'less than 4' => [
				'俄罗斯',
				'俄罗斯',
			],
			'with Latin'  => [
				'我是 cool 俄罗斯 bool 人',
				'-我--是- cool -俄--罗--斯- bool -人-',
			],
		];
	}

	/**
	 * Create a test subject.
	 *
	 * @param bool $is_chinese_locale Whether current locale is Chinese.
	 *
	 * @return Transliterator
	 */
	private function create_subject( bool $is_chinese_locale = false ): Transliterator {
		$settings = Mockery::mock( Settings::class );
		$settings->shouldReceive( 'is_chinese_locale' )->andReturn( $is_chinese_locale );

		return new Transliterator( $settings );
	}

	/**
	 * Transpose Chinese table.
	 *
	 * @param array $table Table.
	 *
	 * @return array
	 */
	private function transpose_chinese_table( array $table ): array {
		$transposed_table = [];
		foreach ( $table as $key => $item ) {
			$hieroglyphs = mb_str_split( $item );
			foreach ( $hieroglyphs as $hieroglyph ) {
				$transposed_table[ $hieroglyph ] = $key;
			}
		}

		return $transposed_table;
	}
}
