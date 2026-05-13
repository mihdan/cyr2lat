<?php
/**
 * TermSlugServiceTest class file.
 *
 * @package cyr-to-lat
 */

namespace CyrToLat\Tests\Unit\Slugs;

use CyrToLat\Main;
use CyrToLat\Slugs\TermSlugService;
use CyrToLat\Tests\Unit\CyrToLatTestCase;
use Mockery;
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
		$subject = $this->get_subject();

		self::assertFalse( $subject->is_term_context() );
		self::assertSame( [], $subject->taxonomies() );
	}

	/**
	 * Test pre_insert_term_filter() captures term context.
	 *
	 * @return void
	 */
	public function test_pre_insert_term_filter_captures_context(): void {
		$subject = $this->get_subject();

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
		$subject = $this->get_subject();
		$args    = [ 'hide_empty' => false ];

		self::assertSame( $args, $subject->get_terms_args_filter( $args, [ 'category', 'post_tag' ] ) );
		self::assertTrue( $subject->is_term_context() );
		self::assertSame( [ 'category', 'post_tag' ], $subject->taxonomies() );
	}

	/**
	 * Test filter_term_slug() transliterates explicit Cyrillic slug.
	 *
	 * @return void
	 */
	public function test_filter_term_slug_transliterates_explicit_cyrillic_slug(): void {
		$main = Mockery::mock( Main::class )->makePartial();
		$main->shouldReceive( 'sanitize_explicit_slug' )
			->with( 'й' )
			->andReturn( 'j' );

		$subject = new TermSlugService( $main );

		self::assertSame( 'j', $subject->filter_term_slug( 'й' ) );
	}

	/**
	 * Test filter_term_slug() transliterates an empty insert slug from the raw term name.
	 *
	 * @return void
	 */
	public function test_filter_term_slug_transliterates_empty_insert_slug_from_cyrillic_term_name(): void {
		$main = Mockery::mock( Main::class )->makePartial();
		$main->shouldReceive( 'sanitize_explicit_slug' )
			->once()
			->with( 'й' )
			->andReturn( 'j' );

		$subject = new TermSlugService( $main );
		$subject->pre_insert_term_filter( 'й', 'category' );

		$this->expect_existing_encoded_slug_lookup( $main, 'й', [ 'category' ], '' );

		self::assertSame( 'j', $subject->filter_term_slug( '' ) );
		self::assertFalse( $subject->is_term_context() );
		self::assertSame( [], $subject->taxonomies() );
	}

	/**
	 * Test filter_term_slug() preserves an existing encoded slug from the raw term name.
	 *
	 * @return void
	 */
	public function test_filter_term_slug_preserves_existing_encoded_slug_from_cyrillic_term_name(): void {
		$main = Mockery::mock( Main::class )->makePartial();
		$main->shouldReceive( 'sanitize_explicit_slug' )->never();

		$subject = new TermSlugService( $main );
		$subject->pre_insert_term_filter( 'й', 'category' );

		$encoded_slug = strtolower( rawurlencode( 'й' ) );

		$this->expect_existing_encoded_slug_lookup( $main, 'й', [ 'category' ], $encoded_slug );

		self::assertSame( $encoded_slug, $subject->filter_term_slug( '' ) );
		self::assertFalse( $subject->is_term_context() );
		self::assertSame( [], $subject->taxonomies() );
	}

	/**
	 * Test filter_term_slug() preserves Latin slug.
	 *
	 * @return void
	 */
	public function test_filter_term_slug_preserves_latin_slug(): void {
		$subject = $this->get_subject();

		self::assertSame( 'manual-slug', $subject->filter_term_slug( 'manual-slug' ) );
	}

	/**
	 * Test filter_term_slug() consumes term insert context for explicit Latin slugs.
	 *
	 * @return void
	 */
	public function test_filter_term_slug_consumes_insert_context_for_explicit_latin_slug(): void {
		$subject = $this->get_subject();
		$subject->pre_insert_term_filter( 'й', 'category' );

		self::assertSame( 'manual-slug', $subject->filter_term_slug( 'manual-slug' ) );
		self::assertFalse( $subject->is_term_context() );
		self::assertSame( [], $subject->taxonomies() );
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

		$subject = $this->get_subject();

		self::assertFalse( $subject->should_transliterate_on_pre_term_slug_filter( 'й' ) );

		unset( $GLOBALS['wp_query'] );
	}

	/**
	 * Test should_transliterate_on_pre_term_slug_filter() skips encoded non-ASCII slugs.
	 *
	 * @return void
	 */
	public function test_should_transliterate_on_pre_term_slug_filter_skips_encoded_non_ascii_slug(): void {
		WP_Mock::userFunction( 'doing_filter' )->with( 'pre_term_slug' )->andReturn( true );

		$subject = $this->get_subject();

		self::assertFalse( $subject->should_transliterate_on_pre_term_slug_filter( '%d0%b9' ) );
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

		$subject = $this->get_subject();

		self::assertTrue( $subject->should_transliterate_on_pre_term_slug_filter( 'й' ) );

		unset( $GLOBALS['wp_query'] );
	}

	/**
	 * Test maybe_preserve_existing_encoded_slug() returns the title on the frontend with SitePress.
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

		$subject = $this->get_subject();
		$subject->pre_insert_term_filter( 'й', 'category' );

		self::assertSame( 'й', $subject->maybe_preserve_existing_encoded_slug( 'й', true ) );
		self::assertFalse( $subject->is_term_context() );
	}

	/**
	 * Get the subject under test.
	 *
	 * @return TermSlugService
	 */
	private function get_subject(): TermSlugService {
		$main = Mockery::mock( Main::class )->makePartial();

		return new TermSlugService( $main );
	}

	/**
	 * Expect a lookup for an existing encoded term slug.
	 *
	 * @param Main     $main       Main plugin class.
	 * @param string   $title      Title.
	 * @param string[] $taxonomies Taxonomies.
	 * @param string   $term       Term lookup result.
	 *
	 * @return void
	 */
	private function expect_existing_encoded_slug_lookup( Main $main, string $title, array $taxonomies, string $term ): void {
		global $wpdb;

		$encoded_slug = rawurlencode( $title );
		$prepared_tax = '\'' . implode( "','", $taxonomies ) . '\'';

		$main->shouldReceive( 'prepare_in' )
			->once()
			->with( $taxonomies )
			->andReturn( $prepared_tax );

		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$wpdb                = Mockery::mock( \wpdb::class );
		$wpdb->terms         = 'wp_terms';
		$wpdb->term_taxonomy = 'wp_term_taxonomy';

		$request          = "SELECT slug FROM $wpdb->terms t LEFT JOIN $wpdb->term_taxonomy tt
							ON t.term_id = tt.term_id
							WHERE LOWER(t.slug) = LOWER(%s)";
		$prepared_request = 'SELECT slug FROM ' . $wpdb->terms . " t LEFT JOIN $wpdb->term_taxonomy tt
							ON t.term_id = tt.term_id
							WHERE LOWER(t.slug) = LOWER(" . $encoded_slug . ')';
		$sql              = $prepared_request . ' AND tt.taxonomy IN (' . $prepared_tax . ')';

		$wpdb->shouldReceive( 'prepare' )->once()->with( $request, $encoded_slug )->andReturn( $prepared_request );
		$wpdb->shouldReceive( 'get_var' )->once()->with( $sql )->andReturn( $term );
	}
}
