<?php
/**
 * VariationAttributeService class file.
 *
 * @package cyr-to-lat
 */

namespace CyrToLat\Slugs;

/**
 * Handles WooCommerce variation attribute key decisions.
 */
class VariationAttributeService {

	/**
	 * Check if the variation attribute key belongs to a global attribute taxonomy.
	 *
	 * @param string $key Key.
	 *
	 * @return bool
	 */
	public function is_global_variation_attribute_key( string $key ): bool {
		return 0 === strpos( $key, 'attribute_pa_' ) || 0 === strpos( $key, 'pa_' );
	}

	/**
	 * Get encoded product attribute key used by WooCommerce during variation form rendering.
	 *
	 * @param string $title Title.
	 *
	 * @return string
	 */
	public function encoded_product_attribute_key( string $title ): string {
		return strtolower( rawurlencode( mb_strtolower( $title ) ) );
	}

	/**
	 * Get local variation request key.
	 *
	 * @param string $title Title.
	 *
	 * @return string
	 */
	public function local_variation_request_key( string $title ): string {
		$attr_name = str_replace( 'attribute_', '', mb_strtolower( $title ) );

		return 'attribute_' . $attr_name;
	}

	/**
	 * Get encoded local variation request keys.
	 *
	 * @param string $title Title.
	 *
	 * @return array
	 */
	public function encoded_local_variation_request_keys( string $title ): array {
		$encoded_attr_name = rawurlencode( $this->local_variation_request_key( $title ) );

		return array_values( array_unique( [ $encoded_attr_name, strtolower( $encoded_attr_name ) ] ) );
	}
}
