<?php
/**
 * TransliteratorTest class file.
 *
 * @package cyr-to-lat
 */

namespace CyrToLat\Tests\Unit\Transliteration;

use CyrToLat\Tests\Unit\CyrToLatTestCase;
use CyrToLat\Transliteration\Transliterator;

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
		$subject = new Transliterator();

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
		$subject = new Transliterator();

		self::assertSame( '', $subject->transliterate( '', [ 'я' => 'ya' ] ) );
	}
}
