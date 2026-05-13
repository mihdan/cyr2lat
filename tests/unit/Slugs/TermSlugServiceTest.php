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
	 * Test get_terms_args_filter() leaves term context unchanged.
	 *
	 * @return void
	 */
	public function test_get_terms_args_filter_leaves_context_unchanged(): void {
		$subject = $this->get_subject();
		$args    = [ 'hide_empty' => false ];

		self::assertSame( $args, $subject->get_terms_args_filter( $args, [ 'category', 'post_tag' ] ) );
		self::assertFalse( $subject->is_term_context() );
		self::assertSame( [], $subject->taxonomies() );
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

		$subject = new TermSlugService( $main, $this->get_wp_insert_term_backtrace_provider() );

		self::assertSame( 'j', $subject->filter_term_slug( 'й' ) );
	}

	/**
	 * Test filter_term_slug() preserves empty insert slug for generated slug handling.
	 *
	 * @return void
	 */
	public function test_filter_term_slug_preserves_empty_insert_slug_for_generated_slug_handling(): void {
		$main = Mockery::mock( Main::class )->makePartial();
		$main->shouldReceive( 'sanitize_explicit_slug' )->never();

		$subject = new TermSlugService( $main, $this->get_wp_insert_term_backtrace_provider() );
		$subject->pre_insert_term_filter( 'й', 'category' );

		self::assertSame( '', $subject->filter_term_slug( '' ) );
		self::assertTrue( $subject->is_term_context() );
		self::assertSame( [ 'category' ], $subject->taxonomies() );
	}

	/**
	 * Test filter_term_slug() does not look up encoded duplicates for an empty generated slug.
	 *
	 * @return void
	 */
	public function test_filter_term_slug_does_not_lookup_encoded_duplicate_for_empty_generated_slug(): void {
		$main = Mockery::mock( Main::class )->makePartial();
		$main->shouldReceive( 'sanitize_explicit_slug' )->never();

		$subject = new TermSlugService( $main, $this->get_wp_insert_term_backtrace_provider() );
		$subject->pre_insert_term_filter( 'й', 'category' );

		self::assertSame( '', $subject->filter_term_slug( '' ) );
		self::assertTrue( $subject->is_term_context() );
		self::assertSame( [ 'category' ], $subject->taxonomies() );
	}

	/**
	 * Test filter_term_slug() transliterates an encoded insert slug when no encoded duplicate exists.
	 *
	 * @return void
	 */
	public function test_filter_term_slug_transliterates_encoded_insert_slug_when_no_duplicate_exists(): void {
		$main = Mockery::mock( Main::class )->makePartial();
		$main->shouldReceive( 'sanitize_explicit_slug' )
			->once()
			->with( 'й' )
			->andReturn( 'j' );

		$subject = new TermSlugService( $main );
		$subject->pre_insert_term_filter( 'й', 'category' );

		$this->expect_existing_encoded_slug_lookup( $main, 'й', [ 'category' ], '' );

		self::assertSame( 'j', $subject->filter_term_slug( strtolower( rawurlencode( 'й' ) ) ) );
		self::assertFalse( $subject->is_term_context() );
		self::assertSame( [], $subject->taxonomies() );
	}

	/**
	 * Test filter_term_slug() preserves an existing encoded insert slug.
	 *
	 * @return void
	 */
	public function test_filter_term_slug_preserves_existing_encoded_insert_slug(): void {
		$main = Mockery::mock( Main::class )->makePartial();
		$main->shouldReceive( 'sanitize_explicit_slug' )->never();

		$subject = new TermSlugService( $main );
		$subject->pre_insert_term_filter( 'й', 'category' );

		$encoded_slug = strtolower( rawurlencode( 'й' ) );

		$this->expect_existing_encoded_slug_lookup( $main, 'й', [ 'category' ], $encoded_slug );

		self::assertSame( $encoded_slug, $subject->filter_term_slug( $encoded_slug ) );
		self::assertFalse( $subject->is_term_context() );
		self::assertSame( [], $subject->taxonomies() );
	}

	/**
	 * Test filter_sanitize_title() returns false without a term insert context.
	 *
	 * @return void
	 */
	public function test_filter_sanitize_title_returns_false_without_term_insert_context(): void {
		$subject = $this->get_subject();

		self::assertFalse( $subject->filter_sanitize_title( 'й' ) );
	}

	/**
	 * Test filter_sanitize_title() preserves an existing encoded slug before WordPress makes it unique.
	 *
	 * @return void
	 */
	public function test_filter_sanitize_title_preserves_existing_encoded_slug_before_unique_step(): void {
		$main = Mockery::mock( Main::class )->makePartial();
		$main->shouldReceive( 'sanitize_explicit_slug' )->never();

		$subject = new TermSlugService( $main, $this->get_wp_insert_term_backtrace_provider() );
		$subject->pre_insert_term_filter( 'й', 'category' );

		$encoded_slug = strtolower( rawurlencode( 'й' ) );

		WP_Mock::onFilter( 'ctl_pre_sanitize_title' )->with( false, 'й' )->reply( false );
		$this->expect_existing_encoded_slug_lookup( $main, 'й', [ 'category' ], $encoded_slug );

		self::assertSame( $encoded_slug, $subject->filter_sanitize_title( 'й' ) );
		self::assertFalse( $subject->is_term_context() );
		self::assertSame( [], $subject->taxonomies() );
	}

	/**
	 * Test filter_sanitize_title() transliterates a generated term slug when no encoded duplicate exists.
	 *
	 * @return void
	 */
	public function test_filter_sanitize_title_transliterates_generated_slug_when_no_duplicate_exists(): void {
		$main = Mockery::mock( Main::class )->makePartial();
		$main->shouldReceive( 'sanitize_explicit_slug' )
			->once()
			->with( 'й' )
			->andReturn( 'j' );

		$subject = new TermSlugService( $main, $this->get_wp_insert_term_backtrace_provider() );
		$subject->pre_insert_term_filter( 'й', 'category' );

		WP_Mock::onFilter( 'ctl_pre_sanitize_title' )->with( false, 'й' )->reply( false );
		$this->expect_existing_encoded_slug_lookup( $main, 'й', [ 'category' ], '' );

		self::assertSame( 'j', $subject->filter_sanitize_title( 'й' ) );
		self::assertFalse( $subject->is_term_context() );
		self::assertSame( [], $subject->taxonomies() );
	}

	/**
	 * Test filter_sanitize_title() ignores term context outside wp_insert_term().
	 *
	 * @return void
	 */
	public function test_filter_sanitize_title_ignores_term_context_outside_wp_insert_term(): void {
		$main = Mockery::mock( Main::class )->makePartial();
		$main->shouldReceive( 'sanitize_explicit_slug' )->never();

		$subject = new TermSlugService(
			$main,
			static function (): array {
				return [
					[ 'function' => 'sanitize_title' ],
				];
			}
		);
		$subject->pre_insert_term_filter( 'й', 'category' );

		self::assertFalse( $subject->filter_sanitize_title( 'й' ) );
		self::assertTrue( $subject->is_term_context() );
		self::assertSame( [ 'category' ], $subject->taxonomies() );
	}

	/**
	 * Test filter_unique_term_slug_is_bad_slug() preserves an existing bad slug decision.
	 *
	 * @return void
	 */
	public function test_filter_unique_term_slug_is_bad_slug_preserves_existing_bad_slug_decision(): void {
		$subject = $this->get_subject();

		self::assertTrue(
			$subject->filter_unique_term_slug_is_bad_slug(
				true,
				strtolower( rawurlencode( 'й' ) ),
				(object) [ 'taxonomy' => 'category' ]
			)
		);
	}

	/**
	 * Test filter_unique_term_slug_is_bad_slug() marks existing encoded slug as bad.
	 *
	 * @return void
	 */
	public function test_filter_unique_term_slug_is_bad_slug_marks_existing_encoded_slug_as_bad(): void {
		$main = Mockery::mock( Main::class )->makePartial();

		$subject      = new TermSlugService( $main );
		$encoded_slug = strtolower( rawurlencode( 'й' ) );

		$this->expect_existing_slug_lookups(
			$main,
			[ 'category' ],
			[
				$encoded_slug => $encoded_slug,
			]
		);

		self::assertTrue(
			$subject->filter_unique_term_slug_is_bad_slug(
				false,
				$encoded_slug,
				(object) [ 'taxonomy' => 'category' ]
			)
		);
	}

	/**
	 * Test filter_unique_term_slug_is_bad_slug() preserves a unique encoded slug.
	 *
	 * @return void
	 */
	public function test_filter_unique_term_slug_is_bad_slug_preserves_unique_encoded_slug(): void {
		$main = Mockery::mock( Main::class )->makePartial();

		$subject      = new TermSlugService( $main );
		$encoded_slug = strtolower( rawurlencode( 'й' ) );

		$this->expect_existing_slug_lookups(
			$main,
			[ 'category' ],
			[
				$encoded_slug => '',
			]
		);

		self::assertFalse(
			$subject->filter_unique_term_slug_is_bad_slug(
				false,
				$encoded_slug,
				(object) [ 'taxonomy' => 'category' ]
			)
		);
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
		$this->expect_existing_slug_lookups( $main, $taxonomies, [ rawurlencode( $title ) => $term ] );
	}

	/**
	 * Expect lookups for existing term slugs.
	 *
	 * @param Main                 $main       Main plugin class.
	 * @param string[]             $taxonomies Taxonomies.
	 * @param array<string,string> $slugs      Slugs and lookup results.
	 *
	 * @return void
	 */
	private function expect_existing_slug_lookups( Main $main, array $taxonomies, array $slugs ): void {
		global $wpdb;

		$prepared_tax = '\'' . implode( "','", $taxonomies ) . '\'';

		$main->shouldReceive( 'prepare_in' )
			->times( count( $slugs ) )
			->with( $taxonomies )
			->andReturn( $prepared_tax );

		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$wpdb                = Mockery::mock( \wpdb::class );
		$wpdb->terms         = 'wp_terms';
		$wpdb->term_taxonomy = 'wp_term_taxonomy';

		$request = "SELECT slug FROM $wpdb->terms t LEFT JOIN $wpdb->term_taxonomy tt
							ON t.term_id = tt.term_id
							WHERE LOWER(t.slug) = LOWER(%s)";

		foreach ( $slugs as $slug => $term ) {
			$prepared_request = 'SELECT slug FROM ' . $wpdb->terms . " t LEFT JOIN $wpdb->term_taxonomy tt
							ON t.term_id = tt.term_id
							WHERE LOWER(t.slug) = LOWER(" . $slug . ')';
			$sql              = $prepared_request . ' AND tt.taxonomy IN (' . $prepared_tax . ')';

			$wpdb->shouldReceive( 'prepare' )->once()->with( $request, $slug )->andReturn( $prepared_request );
			$wpdb->shouldReceive( 'get_var' )->once()->with( $sql )->andReturn( $term );
		}
	}

	/**
	 * Get a minimal debug_backtrace() result for wp_insert_term().
	 *
	 * @return callable
	 */
	private function get_wp_insert_term_backtrace_provider(): callable {
		return static function ( int $options, int $limit ): array {
			self::assertSame( DEBUG_BACKTRACE_IGNORE_ARGS, $options );
			self::assertSame( 8, $limit );

			return [
				[ 'function' => 'filter_sanitize_title' ],
				[ 'function' => 'sanitize_title' ],
				[ 'function' => 'wp_insert_term' ],
			];
		};
	}
}
