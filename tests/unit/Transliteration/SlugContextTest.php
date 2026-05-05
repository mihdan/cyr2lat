<?php
/**
 * SlugContextTest class file.
 *
 * @package cyr-to-lat
 */

namespace CyrToLat\Tests\Unit\Transliteration;

use CyrToLat\Tests\Unit\CyrToLatTestCase;
use CyrToLat\Transliteration\SlugContext;

/**
 * Class SlugContextTest
 *
 * @group transliteration
 */
class SlugContextTest extends CyrToLatTestCase {

	/**
	 * Test default context.
	 *
	 * @return void
	 */
	public function test_default_context(): void {
		$subject = new SlugContext();

		self::assertSame( SlugContext::TYPE_UNKNOWN, $subject->type() );
		self::assertSame( SlugContext::SOURCE_UNKNOWN, $subject->source() );
		self::assertNull( $subject->object_id() );
		self::assertSame( '', $subject->object_type() );
		self::assertSame( '', $subject->locale() );
		self::assertSame( '', $subject->source_label() );
	}

	/**
	 * Test context accessors.
	 *
	 * @return void
	 */
	public function test_context_accessors(): void {
		$subject = new SlugContext(
			SlugContext::TYPE_POST,
			SlugContext::SOURCE_REST,
			123,
			'post',
			'ru_RU',
			'/wp/v2/posts'
		);

		self::assertSame( SlugContext::TYPE_POST, $subject->type() );
		self::assertSame( SlugContext::SOURCE_REST, $subject->source() );
		self::assertSame( 123, $subject->object_id() );
		self::assertSame( 'post', $subject->object_type() );
		self::assertSame( 'ru_RU', $subject->locale() );
		self::assertSame( '/wp/v2/posts', $subject->source_label() );
	}

	/**
	 * Test required context constants.
	 *
	 * @return void
	 */
	public function test_required_context_constants(): void {
		self::assertSame( 'post', SlugContext::TYPE_POST );
		self::assertSame( 'term', SlugContext::TYPE_TERM );
		self::assertSame( 'filename', SlugContext::TYPE_FILENAME );
		self::assertSame( 'wc_global_attribute', SlugContext::TYPE_WC_GLOBAL_ATTRIBUTE );
		self::assertSame( 'wc_local_attribute', SlugContext::TYPE_WC_LOCAL_ATTRIBUTE );
		self::assertSame( 'wc_variation_attribute', SlugContext::TYPE_WC_VARIATION_ATTRIBUTE );

		self::assertSame( 'admin', SlugContext::SOURCE_ADMIN );
		self::assertSame( 'frontend', SlugContext::SOURCE_FRONTEND );
		self::assertSame( 'ajax', SlugContext::SOURCE_AJAX );
		self::assertSame( 'rest', SlugContext::SOURCE_REST );
		self::assertSame( 'cli', SlugContext::SOURCE_CLI );
	}
}
