<?php
/**
 * ConversionProcessLocaleIntegrationTest class file.
 *
 * @package cyr-to-lat
 */

namespace CyrToLat\Tests\Integration\Slugs;

use CyrToLat\BackgroundProcesses\PostConversionProcess;
use CyrToLat\BackgroundProcesses\TermConversionProcess;
use WP_UnitTestCase;

/**
 * Verify that old slugs use the item's language despite WPML's request locale.
 *
 * @group integration
 */
class ConversionProcessLocaleIntegrationTest extends WP_UnitTestCase {

	/**
	 * A Ukrainian post uses the Ukrainian table on an English request.
	 *
	 * @param string $item_locale Post locale.
	 * @dataProvider dp_item_locales
	 * @return void
	 */
	public function test_post_conversion_uses_post_locale( string $item_locale ): void {
		global $wpdb;

		$post_id = self::factory()->post->create( [ 'post_name' => 'temporary-post' ] );
		$slug    = rawurlencode( 'Миргород старий' );
		$wpdb->update( $wpdb->posts, [ 'post_name' => $slug ], [ 'ID' => $post_id ] ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

		$request_locale = static function () {
			return 'en_US';
		};
		$post_language  = static function ( $details, $element_id ) use ( $post_id, $item_locale ) {
			return $post_id === $element_id ? [ 'locale' => $item_locale ] : $details;
		};
		$custom_locale  = static function ( $locale ) {
			return 'uk_UA' === $locale ? 'uk' : $locale;
		};

		add_filter( 'ctl_locale', $request_locale, - PHP_INT_MAX );
		add_filter( 'ctl_locale', $custom_locale );
		add_filter( 'wpml_post_language_details', $post_language, 10, 2 );

		try {
			$process = new class( cyr_to_lat() ) extends PostConversionProcess {
				/**
				 * Run a queued post conversion.
				 *
				 * @param object $post Queued post.
				 * @return bool
				 */
				public function convert( $post ): bool {
					return $this->task( $post );
				}
			};
			$process->convert(
				(object) [
					'ID'          => $post_id,
					'post_name'   => $slug,
					'post_type'   => 'post',
					'post_status' => 'publish',
				]
			);

			self::assertSame( 'myrgorod-staryj', $wpdb->get_var( $wpdb->prepare( "SELECT post_name FROM $wpdb->posts WHERE ID = %d", $post_id ) ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			self::assertSame( 'en_US', apply_filters( 'ctl_locale', 'en_US' ) );
		} finally {
			remove_filter( 'wpml_post_language_details', $post_language, 10 );
			remove_filter( 'ctl_locale', $custom_locale );
			remove_filter( 'ctl_locale', $request_locale, - PHP_INT_MAX );
		}
	}

	/**
	 * A Ukrainian term uses the Ukrainian table on an English request.
	 *
	 * @param string $item_locale Term locale.
	 * @dataProvider dp_item_locales
	 * @return void
	 */
	public function test_term_conversion_uses_term_locale( string $item_locale ): void {
		global $wpdb;

		$term = self::factory()->term->create_and_get(
			[
				'taxonomy' => 'category',
				'slug'     => 'temporary-term',
			]
		);
		$slug = rawurlencode( 'Миргород архів' );
		$wpdb->update( $wpdb->terms, [ 'slug' => $slug ], [ 'term_id' => $term->term_id ] ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

		$request_locale   = static function () {
			return 'en_US';
		};
		$term_language    = static function ( $details, $args ) use ( $term ) {
			return $term->term_taxonomy_id === $args['element_id'] ? (object) [ 'language_code' => 'uk' ] : $details;
		};
		$active_languages = static function () use ( $item_locale ) {
			return [ 'uk' => [ 'default_locale' => $item_locale ] ];
		};
		$custom_locale    = static function ( $locale ) {
			return 'uk_UA' === $locale ? 'uk' : $locale;
		};

		add_filter( 'ctl_locale', $request_locale, - PHP_INT_MAX );
		add_filter( 'ctl_locale', $custom_locale );
		add_filter( 'wpml_element_language_details', $term_language, 10, 2 );
		add_filter( 'wpml_active_languages', $active_languages );

		try {
			$process = new class( cyr_to_lat() ) extends TermConversionProcess {
				/**
				 * Run a queued term conversion.
				 *
				 * @param object $term Queued term.
				 * @return bool
				 */
				public function convert( $term ): bool {
					return $this->task( $term );
				}
			};
			$process->convert(
				(object) [
					'term_id'          => $term->term_id,
					'term_taxonomy_id' => $term->term_taxonomy_id,
					'taxonomy'         => 'category',
					'slug'             => $slug,
				]
			);

			self::assertSame( 'myrgorod-arhiv', $wpdb->get_var( $wpdb->prepare( "SELECT slug FROM $wpdb->terms WHERE term_id = %d", $term->term_id ) ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			self::assertSame( 'en_US', apply_filters( 'ctl_locale', 'en_US' ) );
		} finally {
			remove_filter( 'wpml_active_languages', $active_languages );
			remove_filter( 'wpml_element_language_details', $term_language, 10 );
			remove_filter( 'ctl_locale', $custom_locale );
			remove_filter( 'ctl_locale', $request_locale, - PHP_INT_MAX );
		}
	}

	/**
	 * Item locales, including the documented non-standard Ukrainian locale.
	 *
	 * @return array
	 */
	public static function dp_item_locales(): array {
		return [
			[ 'uk' ],
			[ 'uk_UA' ],
		];
	}
}
