<?php
/**
 * TermSlugServiceTest class file.
 *
 * @package cyr-to-lat
 */

namespace CyrToLat\Tests\Unit\Slugs;

use CyrToLat\Slugs\TermSlugService;
use CyrToLat\Tests\Unit\CyrToLatTestCase;

/**
 * Class TermSlugServiceTest
 *
 * @group slugs
 */
class TermSlugServiceTest extends CyrToLatTestCase {

	/**
	 * Test default context.
	 *
	 * @return void
	 */
	public function test_default_context(): void {
		$subject = new TermSlugService();

		self::assertFalse( $subject->is_term_context() );
		self::assertSame( [], $subject->taxonomies() );
	}

	/**
	 * Test pre_insert_term_filter() captures term context.
	 *
	 * @return void
	 */
	public function test_pre_insert_term_filter_captures_context(): void {
		$subject = new TermSlugService();

		self::assertSame( 'й', $subject->pre_insert_term_filter( 'й', 'category' ) );
		self::assertTrue( $subject->is_term_context() );
		self::assertSame( [ 'category' ], $subject->taxonomies() );
	}

	/**
	 * Test get_terms_args_filter() captures term query context.
	 *
	 * @return void
	 */
	public function test_get_terms_args_filter_captures_context(): void {
		$subject = new TermSlugService();
		$args    = [ 'hide_empty' => false ];

		self::assertSame( $args, $subject->get_terms_args_filter( $args, [ 'category', 'post_tag' ] ) );
		self::assertTrue( $subject->is_term_context() );
		self::assertSame( [ 'category', 'post_tag' ], $subject->taxonomies() );
	}
}
