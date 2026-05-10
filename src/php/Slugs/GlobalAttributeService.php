<?php
/**
 * GlobalAttributeService class file.
 *
 * @package cyr-to-lat
 */

namespace CyrToLat\Slugs;

/**
 * Handles WooCommerce global attribute slug decisions.
 */
class GlobalAttributeService {

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
	 * @param string        $title                              Title.
	 * @param callable|null $is_local_attribute                 Local attribute callback.
	 *
	 * @return bool
	 */
	public function should_preserve_attribute_title( string $title, ?callable $is_local_attribute = null ): bool {
		if ( ! function_exists( 'WC' ) ) {
			return false;
		}

		if ( $this->is_attribute_taxonomy( $title ) ) {
			return true;
		}

		if ( is_callable( $is_local_attribute ) && $is_local_attribute( $title ) ) {
			return true;
		}

		return $this->is_product_not_converted_attribute( $title );
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
}
