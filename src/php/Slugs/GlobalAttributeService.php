<?php
/**
 * GlobalAttributeService class file.
 *
 * @package cyr-to-lat
 */

namespace CyrToLat\Slugs;

use CyrToLat\Main;

/**
 * Handles WooCommerce global attribute slug decisions.
 */
class GlobalAttributeService {

	/**
	 * Backtrace depth used to detect WooCommerce-originated sanitize_title() calls.
	 */
	private const SANITIZE_TITLE_STACK_DEPTH = 12;

	/**
	 * Whitelist of WooCommerce functions/methods that legitimately call
	 * sanitize_title() on attribute-related values without a dedicated narrow hook.
	 *
	 * Each entry is a function name; class methods are matched by method name only,
	 * which is enough because these identifiers are unique within the WC code base.
	 *
	 * @var array<string, true>
	 */
	private const WC_SANITIZE_TITLE_FRAMES = [
		// Frontend layered nav / `?filter_*=...`.
		'wc_attribute_taxonomy_name'        => true,
		'get_layered_nav_chosen_attributes' => true,
		// Variation reload via URL `?attribute_*=...`.
		'find_matching_product_variation'   => true,
		'get_matching_variation'            => true,
		// Frontend add-to-cart for variable products.
		'add_to_cart_handler_variable'      => true,
		// Cart/session restore.
		'get_cart_from_session'             => true,
		// Legacy non-converted product attribute reads.
		'wc_get_product_attribute'          => true,
	];

	/**
	 * Main plugin class.
	 *
	 * @var Main
	 */
	private Main $main;

	/**
	 * Constructor.
	 *
	 * @param Main $main Main plugin class.
	 */
	public function __construct( Main $main ) {
		$this->main = $main;
	}

	/**
	 * Check if title is an attribute taxonomy.
	 *
	 * @param string $title Title.
	 *
	 * @return bool
	 * @noinspection PhpUndefinedFunctionInspection
	 * @noinspection UnnecessaryCastingInspection
	 */
	public function is_attribute_taxonomy( string $title ): bool {
		if ( ! function_exists( 'wc_get_attribute_taxonomies' ) ) {
			return false;
		}

		$title = (string) preg_replace( '/^pa_/', '', $title );

		foreach ( wc_get_attribute_taxonomies() as $attribute_taxonomy ) {
			if ( $title === $attribute_taxonomy->attribute_name ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if the title should be preserved as a WooCommerce attribute slug.
	 *
	 * @param string $title Title.
	 *
	 * @return bool
	 */
	public function should_preserve_attribute_title( string $title ): bool {
		if ( ! function_exists( 'WC' ) ) {
			return false;
		}

		if ( $this->is_attribute_taxonomy( $title ) ) {
			return true;
		}

		return $this->is_product_not_converted_attribute( $title );
	}

	/**
	 * Sanitize a title that belongs to a WooCommerce attribute flow.
	 *
	 * Routes the call away from the legacy broad sanitize_title bridge: when the
	 * current call originates from a known WooCommerce attribute flow, this
	 * service handles the value itself by either preserving the attribute title
	 * or transliterating it. Otherwise, `null` is returned so the caller can fall
	 * back to the legacy bridge or leave the value untouched.
	 *
	 * @param string $title     Sanitized title.
	 * @param string $raw_title Raw title prior to sanitization.
	 * @param string $context   Sanitization context.
	 *
	 * @return string|null Final title when handled, `null` otherwise.
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function sanitize_title( string $title, string $raw_title = '', string $context = '' ): ?string {
		if ( '' === $title || 'query' === $context ) {
			return null;
		}

		if ( ! function_exists( 'WC' ) ) {
			return null;
		}

		if ( ! $this->has_non_ascii_chars( rawurldecode( $title ) ) ) {
			return null;
		}

		if ( ! $this->is_wc_attribute_sanitize_title_call() ) {
			return null;
		}

		$decoded = urldecode( $title );

		if ( $this->should_preserve_attribute_title( $decoded ) ) {
			return $decoded;
		}

		return $this->main->transliterate( $decoded );
	}

	/**
	 * Whether the current sanitize_title() call originates from a known WooCommerce attribute flow.
	 *
	 * Inspects a shallow `debug_backtrace()` and matches frames against the whitelist of
	 * WooCommerce functions/methods that invoke sanitize_title() directly
	 * (i.e., where no narrow filter is available). Mirrors the approach used in
	 * {@see TermSlugService::is_wp_insert_term_sanitize_title_call()}.
	 *
	 * @return bool
	 */
	private function is_wc_attribute_sanitize_title_call(): bool {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_debug_backtrace -- Intentional limited stack inspection for WooCommerce attribute flow detection.
		$backtrace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, self::SANITIZE_TITLE_STACK_DEPTH );

		foreach ( $backtrace as $call ) {
			$function = $call['function'] ?? '';

			if ( isset( self::WC_SANITIZE_TITLE_FRAMES[ $function ] ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Filter WooCommerce taxonomy names without the broad sanitize_title bridge.
	 *
	 * @param string|mixed $taxonomy     Sanitized taxonomy.
	 * @param string|mixed $raw_taxonomy Raw taxonomy.
	 *
	 * @return string|mixed
	 */
	public function filter_taxonomy_name( $taxonomy, $raw_taxonomy ) {
		if ( ! function_exists( 'WC' ) || ! is_string( $taxonomy ) || ! is_string( $raw_taxonomy ) ) {
			return $taxonomy;
		}

		$raw_taxonomy = rawurldecode( $raw_taxonomy );

		if ( '' === $raw_taxonomy || ! $this->has_non_ascii_chars( $raw_taxonomy ) ) {
			return $taxonomy;
		}

		return $this->main->sanitize_explicit_slug( $raw_taxonomy );
	}

	/**
	 * Check if title is a product not converted attribute.
	 *
	 * @param string $title Title.
	 *
	 * @return bool
	 * @noinspection PhpUndefinedMethodInspection
	 */
	private function is_product_not_converted_attribute( string $title ): bool {

		global $product;

		if ( ! is_a( $product, 'WC_Product' ) ) {
			return false;
		}

		// We have to get attributes from postmeta here to see the converted slug.
		$attributes = (array) get_post_meta( $product->get_id(), '_product_attributes', true );

		foreach ( $attributes as $slug => $attribute ) {
			$name = $attribute['name'] ?? '';

			if ( $name === $title && sanitize_title_with_dashes( $title ) === $slug ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Whether the value contains non-ASCII characters.
	 *
	 * @param string $value Value.
	 *
	 * @return bool
	 */
	private function has_non_ascii_chars( string $value ): bool {
		return (bool) preg_match( '/[^\x00-\x7F]/', $value );
	}
}
