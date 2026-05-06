<?php
/**
 * TermSlugIntegrationTest class file.
 *
 * @package cyr-to-lat
 */

namespace CyrToLat\Tests\Integration;

use WP_Term;
use WP_UnitTestCase;

/**
 * Class TermSlugIntegrationTest
 *
 * @group integration
 */
class TermSlugIntegrationTest extends WP_UnitTestCase {

	private const TAXONOMY = 'cyr2lat_topic';

	/**
	 * Set up an allowed admin term request context.
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		set_current_screen( 'edit-tags' );
		$this->register_test_taxonomy();
		cyr_to_lat()->init_all();
	}

	/**
	 * Tear down test globals.
	 *
	 * @return void
	 */
	public function tearDown(): void {
		unregister_taxonomy( self::TAXONOMY );

		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		unset( $GLOBALS['current_screen'] );

		parent::tearDown();
	}

	/**
	 * Test that the plugin registers current term slug filters.
	 *
	 * @return void
	 */
	public function test_term_slug_filters_are_registered(): void {
		self::assertSame( PHP_INT_MAX, has_filter( 'pre_insert_term', [ cyr_to_lat(), 'pre_insert_term_filter' ] ) );
		self::assertSame( 9, has_filter( 'sanitize_title', [ cyr_to_lat(), 'sanitize_title' ] ) );
	}

	/**
	 * Test that wp_insert_term creates a transliterated category slug.
	 *
	 * @return void
	 */
	public function test_wp_insert_term_generates_category_slug_from_cyrillic_name(): void {
		$term = $this->insert_term( 'й', 'category' );

		self::assertSame( 'j', $term->slug );
	}

	/**
	 * Test that wp_insert_term creates a transliterated post tag slug.
	 *
	 * @return void
	 */
	public function test_wp_insert_term_generates_post_tag_slug_from_cyrillic_name(): void {
		$term = $this->insert_term( 'й', 'post_tag' );

		self::assertSame( 'j', $term->slug );
	}

	/**
	 * Test that wp_insert_term creates a transliterated custom taxonomy slug.
	 *
	 * @return void
	 */
	public function test_wp_insert_term_generates_custom_taxonomy_slug_from_cyrillic_name(): void {
		$term = $this->insert_term( 'й', self::TAXONOMY );

		self::assertSame( 'j', $term->slug );
	}

	/**
	 * Test that wp_insert_term creates a transliterated WooCommerce product category slug when available.
	 *
	 * @return void
	 */
	public function test_wp_insert_term_generates_product_category_slug_from_cyrillic_name_when_available(): void {
		if ( ! taxonomy_exists( 'product_cat' ) ) {
			self::markTestSkipped( 'WooCommerce product_cat taxonomy is not registered.' );
		}

		$term = $this->insert_term( 'й', 'product_cat' );

		self::assertSame( 'j', $term->slug );
	}

	/**
	 * Test that an explicitly supplied Cyrillic term slug is transliterated.
	 *
	 * @return void
	 */
	public function test_wp_insert_term_transliterates_explicit_cyrillic_slug(): void {
		$term = $this->insert_term(
			'Manual',
			'category',
			[
				'slug' => 'й',
			]
		);

		self::assertSame( 'j', $term->slug );
	}

	/**
	 * Test that an explicitly supplied Latin term slug is preserved.
	 *
	 * @return void
	 */
	public function test_wp_insert_term_preserves_explicit_latin_slug(): void {
		$term = $this->insert_term(
			'Manual',
			'category',
			[
				'slug' => 'manual-slug',
			]
		);

		self::assertSame( 'manual-slug', $term->slug );
	}

	/**
	 * Test that an explicitly supplied Cyrillic custom taxonomy term slug is transliterated.
	 *
	 * @return void
	 */
	public function test_wp_insert_custom_taxonomy_term_transliterates_explicit_cyrillic_slug(): void {
		$term = $this->insert_term(
			'Manual',
			self::TAXONOMY,
			[
				'slug' => 'й',
			]
		);

		self::assertSame( 'j', $term->slug );
	}

	/**
	 * Test that an explicitly supplied Latin custom taxonomy term slug is preserved.
	 *
	 * @return void
	 */
	public function test_wp_insert_custom_taxonomy_term_preserves_explicit_latin_slug(): void {
		$term = $this->insert_term(
			'Manual',
			self::TAXONOMY,
			[
				'slug' => 'manual-slug',
			]
		);

		self::assertSame( 'manual-slug', $term->slug );
	}

	/**
	 * Test current behavior when an encoded Cyrillic slug already exists.
	 *
	 * @return void
	 */
	public function test_wp_insert_term_uses_unique_suffix_when_encoded_cyrillic_slug_already_exists(): void {
		$encoded_slug = strtolower( rawurlencode( 'й' ) );

		$this->insert_raw_term( 'Encoded й', self::TAXONOMY, $encoded_slug );

		$term = $this->insert_term( 'й', self::TAXONOMY );

		self::assertSame( $encoded_slug . '-2', $term->slug );
	}

	/**
	 * Register a custom taxonomy used by integration tests.
	 *
	 * @return void
	 */
	private function register_test_taxonomy(): void {
		register_taxonomy(
			self::TAXONOMY,
			'post',
			[
				'public' => true,
			]
		);
	}

	/**
	 * Insert a term and return the stored WP_Term object.
	 *
	 * @param string $name     Term name.
	 * @param string $taxonomy Taxonomy slug.
	 * @param array  $args     Optional term args.
	 *
	 * @return WP_Term
	 */
	private function insert_term( string $name, string $taxonomy, array $args = [] ): WP_Term {
		$result = wp_insert_term( $name, $taxonomy, $args );

		$this->assertNotWPError( $result );
		self::assertIsArray( $result );

		$term = get_term( (int) $result['term_id'], $taxonomy );

		$this->assertNotWPError( $term );
		self::assertInstanceOf( WP_Term::class, $term );

		return $term;
	}

	/**
	 * Insert a raw term row to represent a legacy encoded Cyrillic slug.
	 *
	 * @param string $name     Term name.
	 * @param string $taxonomy Taxonomy slug.
	 * @param string $slug     Raw term slug.
	 *
	 * @return void
	 * @noinspection PhpSameParameterValueInspection
	 */
	private function insert_raw_term( string $name, string $taxonomy, string $slug ): void {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$inserted = $wpdb->insert(
			$wpdb->terms,
			[
				'name'       => $name,
				'slug'       => $slug,
				'term_group' => 0,
			]
		);

		self::assertSame( 1, $inserted );

		$term_id = $wpdb->insert_id;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$inserted = $wpdb->insert(
			$wpdb->term_taxonomy,
			[
				'term_id'     => $term_id,
				'taxonomy'    => $taxonomy,
				'description' => '',
				'parent'      => 0,
				'count'       => 0,
			]
		);

		self::assertSame( 1, $inserted );

		clean_term_cache( $term_id, $taxonomy );
	}
}
