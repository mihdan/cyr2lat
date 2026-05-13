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
	 * Filter a generated term slug while WordPress sanitizes the term name.
	 *
	 * WordPress calls sanitize_title( $name ) before wp_unique_term_slug() when no
	 * explicit slug was provided. This is the point where encoded legacy slugs
	 * must be restored so WordPress can add the unique suffix itself.
	 *
	 * @param string $title Sanitized title.
	 *
	 * @return false|string
	 */
	public function filter_sanitize_title( string $title ) {
		if ( ! $this->is_term || null === $this->raw_term || '' === $title ) {
			return false;
		}

		if ( ! $this->is_wp_insert_term_sanitize_title_call() ) {
			return false;
		}

		$taxonomies = $this->taxonomies;

		$this->reset_term_context();

		$title = urldecode( $title );
		$pre   = apply_filters( 'ctl_pre_sanitize_title', false, $title );

		if ( false !== $pre ) {
			return (string) $pre;
		}

		if ( ! $this->has_non_ascii_chars( $title ) ) {
			return $title;
		}

		$term = $this->find_existing_encoded_slug( $title, $taxonomies );

		if ( false !== $term ) {
			return $term;
		}

		return $this->main->sanitize_explicit_slug( $title );
	}

	/**
	 * Filter whether WordPress should add a unique suffix to a term slug.
	 *
	 * @param bool   $is_bad_slug Whether WordPress already decided the slug needs a suffix.
	 * @param string $slug        Term slug.
	 * @param object $term        Term object.
	 *
	 * @return bool
	 */
	public function filter_unique_term_slug_is_bad_slug( bool $is_bad_slug, string $slug, object $term ): bool {
		if ( $is_bad_slug || ! $this->is_encoded_non_ascii_slug( $slug ) ) {
			return $is_bad_slug;
		}

		$taxonomy = isset( $term->taxonomy ) ? (string) $term->taxonomy : '';

		return false !== $this->find_existing_slug( $slug, $taxonomy ? [ $taxonomy ] : [] );
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
		$taxonomies = $this->taxonomies;

		if ( '' === $slug ) {
			return $slug;
		}

		$this->reset_term_context();

		if ( $this->is_encoded_non_ascii_slug( $slug ) ) {
			$decoded_slug = rawurldecode( $slug );
			$term         = $this->find_existing_encoded_slug( $decoded_slug, $taxonomies );

			if ( false !== $term ) {
				return $term;
			}

			return $this->main->sanitize_explicit_slug( $decoded_slug );
		}

		if ( $this->has_non_ascii_chars( $slug ) ) {
			return $this->main->sanitize_explicit_slug( $slug );
		}

		return $slug;
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
		return $this->find_existing_slug( rawurlencode( $title ), $taxonomies );
	}

	/**
	 * Find an existing term slug.
	 *
	 * @param string   $slug       Slug.
	 * @param string[] $taxonomies Taxonomies.
	 *
	 * @return false|string
	 */
	private function find_existing_slug( string $slug, array $taxonomies ) {
		global $wpdb;

		$sql = $wpdb->prepare(
			"SELECT slug FROM $wpdb->terms t LEFT JOIN $wpdb->term_taxonomy tt
							ON t.term_id = tt.term_id
							WHERE LOWER(t.slug) = LOWER(%s)",
			$slug
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
	 * Whether the current sanitize_title() call belongs to wp_insert_term() generated slug handling.
	 *
	 * @return bool
	 */
	private function is_wp_insert_term_sanitize_title_call(): bool {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_debug_backtrace -- Intentional limited stack inspection for WordPress term flow detection.
		$backtrace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 8 );

		foreach ( $backtrace as $call ) {
			if ( 'wp_insert_term' === ( $call['function'] ?? '' ) ) {
				return true;
			}
		}

		return false;
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
