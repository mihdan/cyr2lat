<?php
/**
 * TransliteratorTest class file.
 *
 * @package cyr-to-lat
 */

namespace CyrToLat\Tests\Unit\Transliteration;

use CyrToLat\Settings\Settings;
use CyrToLat\Tests\Unit\CyrToLatTestCase;
use CyrToLat\Transliteration\SlugContext;
use CyrToLat\Transliteration\Transliterator;
use Mockery;
use WP_Mock;

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
		$table   = [
			'П' => 'P',
			'р' => 'r',
			'и' => 'i',
			'в' => 'v',
			'е' => 'e',
			'т' => 't',
			'м' => 'm',
		];
		$subject = $this->create_subject( false, $table );

		WP_Mock::expectFilter( 'ctl_table', $table );

		self::assertSame(
			'Privet, mir!',
			$subject->transliterate( 'Привет, мир!' )
		);
	}

	/**
	 * Test transliterate() with empty string.
	 *
	 * @return void
	 */
	public function test_transliterate_with_empty_string(): void {
		$subject = $this->create_subject();

		self::assertSame( '', $subject->transliterate_with_table( '', [ 'я' => 'ya' ] ) );
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
			$subject->transliterate_with_table( urldecode( '%d0%95%cc%88' ), $table )
		);
	}

	/**
	 * Test transliterate() uses the filtered table.
	 *
	 * @return void
	 */
	public function test_transliterate_uses_filtered_table(): void {
		$default_table  = [ 'я' => 'ya' ];
		$filtered_table = [ 'я' => 'ja' ];
		$subject        = $this->create_subject( false, $default_table );

		WP_Mock::onFilter( 'ctl_table' )->with( $default_table )->reply( $filtered_table );

		self::assertSame( 'ja', $subject->transliterate( 'я' ) );
	}

	/**
	 * Test transliterate() accepts a context without changing current behavior.
	 *
	 * @return void
	 */
	public function test_transliterate_accepts_context(): void {
		$table   = [ 'я' => 'ya' ];
		$subject = $this->create_subject( false, $table );
		$context = new SlugContext( SlugContext::TYPE_POST, SlugContext::SOURCE_REST );

		WP_Mock::expectFilter( 'ctl_table', $table );

		self::assertSame( 'ya', $subject->transliterate( 'я', $context ) );
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
	private function create_subject( bool $is_chinese_locale = false, array $table = [] ): Transliterator {
		$settings = Mockery::mock( Settings::class );
		$settings->shouldReceive( 'is_chinese_locale' )->andReturn( $is_chinese_locale );

		if ( $table ) {
			$settings->shouldReceive( 'get_table' )->andReturn( $table );
		}

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
