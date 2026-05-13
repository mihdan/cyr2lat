<?php
/**
 * TermSlugService class file.
 *
 * @package cyr-to-lat
 */

namespace CyrToLat\Slugs;

use CyrToLat\Main;

/**
 * Handles term slug context.
 */
class TermSlugService extends BaseService {

	/**
	 * Main plugin class.
	 *
	 * @var Main
	 */
	private Main $main;

	/**
	 * Term context flag.
	 *
	 * @var bool
	 */
	private bool $is_term = false;

	/**
	 * Current taxonomies.
	 *
	 * @var string[]
	 */
	private array $taxonomies = [];

	/**
	 * Raw term name captured during insertion.
	 *
	 * @var string|null
	 */
	private ?string $raw_term = null;

	/**
	 * Constructor.
	 *
	 * @param Main $main Main plugin class.
	 */
	public function __construct( Main $main ) {
		$this->main = $main;
	}

	/**
	 * Whether a term context is active.
	 *
	 * @return bool
	 */
	public function is_term_context(): bool {
		return $this->is_term;
	}

	/**
	 * Get captured taxonomies.
	 *
	 * @return string[]
	 */
	public function taxonomies(): array {
		return $this->taxonomies;
	}

	/**
	 * Filters a term before it is sanitized and inserted into the database.
	 *
	 * @param string|int|mixed $term     The term name to add.
	 * @param string           $taxonomy Taxonomy slug.
	 *
	 * @return string|int|mixed
	 */
	public function pre_insert_term_filter( $term, string $taxonomy ) {
		if (
			0 === $term ||
			( function_exists( 'is_wp_error' ) && is_wp_error( $term ) ) ||
			'' === trim( $term )
		) {
			return $term;
		}

		$this->is_term    = true;
		$this->taxonomies = [ $taxonomy ];
		$this->raw_term   = (string) $term;

		return $term;
	}

	/**
	 * Filters the term query arguments.
	 *
	 * @param array|mixed $args       An array of get_terms() arguments.
	 * @param string[]    $taxonomies An array of taxonomy names.
	 *
	 * @return array|mixed
	 */
	public function get_terms_args_filter( $args, array $taxonomies ) {
		$this->is_term    = true;
		$this->taxonomies = $taxonomies;
		$this->raw_term   = null;

		return $args;
	}

	/**
	 * Filter a term slug before WordPress default sanitize_title() fallback.
	 *
	 * @param string $slug Term slug.
	 *
	 * @return string
	 */
	public function filter_term_slug( string $slug ): string {
		if ( null !== $this->raw_term ) {
			return $this->filter_insert_term_slug( $slug );
		}

		if ( '' === $slug || ! $this->has_non_ascii_chars( $slug ) ) {
			return $slug;
		}

		return $this->main->sanitize_explicit_slug( $slug );
	}

	/**
	 * Preserve existing encoded term slug when the current context requires it.
	 *
	 * @param string $title       Title.
	 * @param bool   $is_frontend Whether current request is frontend.
	 *
	 * @return false|string
	 */
	public function maybe_preserve_existing_encoded_slug( string $title, bool $is_frontend ) {
		if ( ! $this->is_term ) {
			return false;
		}

		$taxonomies = $this->taxonomies;

		// Make sure we search in the db only once being called from wp_insert_term().
		$this->reset_term_context();

		// Fix a case when showing previously created categories in cyrillic with WPML.
		if ( $is_frontend && class_exists( 'SitePress' ) ) {
			return $title;
		}

		return $this->find_existing_encoded_slug( $title, $taxonomies );
	}

	/**
	 * Check if we should transliterate the tag on pre_term_slug filter.
	 *
	 * @param string $title Title.
	 *
	 * @return bool
	 */
	public function should_transliterate_on_pre_term_slug_filter( string $title ): bool {
		global $wp_query;

		$doing_pre_term_slug = function_exists( 'doing_filter' ) && doing_filter( 'pre_term_slug' );

		if ( $doing_pre_term_slug && $this->is_encoded_non_ascii_slug( $title ) ) {
			return false;
		}

		$tag_var = $wp_query->query_vars['tag'] ?? null;

		return ! (
			$tag_var === $title &&
			$doing_pre_term_slug &&
			// Transliterate on pre_term_slug with Polylang and WPML only.
			! ( class_exists( 'Polylang' ) || class_exists( 'SitePress' ) )
		);
	}

	/**
	 * Filter a slug while WordPress is sanitizing a term insert payload.
	 *
	 * @param string $slug Term slug.
	 *
	 * @return string
	 */
	private function filter_insert_term_slug( string $slug ): string {
		$raw_term   = (string) $this->raw_term;
		$taxonomies = $this->taxonomies;

		$this->reset_term_context();

		if ( '' !== $slug ) {
			if ( $this->has_non_ascii_chars( $slug ) ) {
				return $this->main->sanitize_explicit_slug( $slug );
			}

			return $slug;
		}

		if ( ! $this->has_non_ascii_chars( $raw_term ) ) {
			return $slug;
		}

		$term = $this->find_existing_encoded_slug( $raw_term, $taxonomies );

		if ( false !== $term ) {
			return $term;
		}

		return $this->main->sanitize_explicit_slug( $raw_term );
	}

	/**
	 * Find an existing URL-encoded term slug for the provided title.
	 *
	 * @param string   $title      Title.
	 * @param string[] $taxonomies Taxonomies.
	 *
	 * @return false|string
	 */
	private function find_existing_encoded_slug( string $title, array $taxonomies ) {
		global $wpdb;

		$sql = $wpdb->prepare(
			"SELECT slug FROM $wpdb->terms t LEFT JOIN $wpdb->term_taxonomy tt
							ON t.term_id = tt.term_id
							WHERE LOWER(t.slug) = LOWER(%s)",
			rawurlencode( $title )
		);

		if ( $taxonomies ) {
			$sql .= ' AND tt.taxonomy IN (' . $this->main->prepare_in( $taxonomies ) . ')';
		}

		// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$term = $wpdb->get_var( $sql );
		// phpcs:enable WordPress.DB.PreparedSQL.NotPrepared

		return ! empty( $term ) ? $term : false;
	}

	/**
	 * Whether a slug is a percent-encoded non-ASCII value.
	 *
	 * @param string $slug Slug.
	 *
	 * @return bool
	 */
	private function is_encoded_non_ascii_slug( string $slug ): bool {
		return false !== strpos( $slug, '%' ) && $this->has_non_ascii_chars( rawurldecode( $slug ) );
	}

	/**
	 * Reset captured term context.
	 *
	 * @return void
	 */
	private function reset_term_context(): void {
		$this->is_term    = false;
		$this->taxonomies = [];
		$this->raw_term   = null;
	}
}
