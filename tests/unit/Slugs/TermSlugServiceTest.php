<?php
/**
 * TermSlugServiceTest class file.
 *
 * @package cyr-to-lat
 */

namespace CyrToLat\Tests\Unit\Slugs;

use CyrToLat\Slugs\TermSlugService;
use CyrToLat\Tests\Unit\CyrToLatTestCase;
use tad\FunctionMocker\FunctionMocker;
use WP_Mock;

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

	/**
	 * Test should_transliterate_on_pre_term_slug_filter() skips tag query context.
	 *
	 * @return void
	 */
	public function test_should_transliterate_on_pre_term_slug_filter_skips_tag_query_context(): void {
		global $wp_query;

		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$wp_query = (object) [
			'query_vars' => [
				'tag' => 'й',
			],
		];

		WP_Mock::userFunction( 'doing_filter' )->with( 'pre_term_slug' )->andReturn( true );

		$subject = new TermSlugService();

		self::assertFalse( $subject->should_transliterate_on_pre_term_slug_filter( 'й' ) );

		unset( $GLOBALS['wp_query'] );
	}

	/**
	 * Test should_transliterate_on_pre_term_slug_filter() allows multilingual term handling.
	 *
	 * @return void
	 */
	public function test_should_transliterate_on_pre_term_slug_filter_allows_multilingual_plugins(): void {
		global $wp_query;

		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$wp_query = (object) [
			'query_vars' => [
				'tag' => 'й',
			],
		];

		WP_Mock::userFunction( 'doing_filter' )->with( 'pre_term_slug' )->andReturn( true );
		FunctionMocker::replace(
			'class_exists',
			static function ( string $class_name ): bool {
				return 'Polylang' === $class_name;
			}
		);

		$subject = new TermSlugService();

		self::assertTrue( $subject->should_transliterate_on_pre_term_slug_filter( 'й' ) );

		unset( $GLOBALS['wp_query'] );
	}

	/**
	 * Test maybe_preserve_existing_encoded_slug() returns title on frontend with SitePress.
	 *
	 * @return void
	 */
	public function test_maybe_preserve_existing_encoded_slug_returns_title_on_frontend_with_sitepress(): void {
		FunctionMocker::replace(
			'class_exists',
			static function ( string $class_name ): bool {
				return 'SitePress' === $class_name;
			}
		);

		$subject = new TermSlugService();
		$subject->pre_insert_term_filter( 'й', 'category' );

		self::assertSame( 'й', $subject->maybe_preserve_existing_encoded_slug( 'й', true ) );
		self::assertFalse( $subject->is_term_context() );
	}
}
