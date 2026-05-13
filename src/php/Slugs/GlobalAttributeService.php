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
	 * Main plugin class.
	 *
	 * @var Main
	 */
	private Main $main;

	/**
	 * Local attribute service.
	 *
	 * @var LocalAttributeService
	 */
	private LocalAttributeService $local_attribute_service;

	/**
	 * Constructor.
	 *
	 * @param Main                  $main                    Main plugin class.
	 * @param LocalAttributeService $local_attribute_service Local attribute service.
	 */
	public function __construct( Main $main, LocalAttributeService $local_attribute_service ) {
		$this->main                    = $main;
		$this->local_attribute_service = $local_attribute_service;
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
	 * Whether a sanitize_title() call belongs to an explicit WooCommerce attribute flow.
	 *
	 * @param string $title Title.
	 *
	 * @return bool
	 */
	public function should_handle_sanitize_title( string $title ): bool {
		if ( ! function_exists( 'WC' ) ) {
			return false;
		}

		return $this->is_attribute_taxonomy( $title ) ||
			$this->local_attribute_service->should_handle_sanitize_title( $title ) ||
			$this->is_product_not_converted_attribute( $title );
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
