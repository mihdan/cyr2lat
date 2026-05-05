<?php
/**
 * Minimal WooCommerce global function stubs for isolated integration tests.
 *
 * @package cyr-to-lat
 */

if ( ! function_exists( 'WC' ) ) {
	// phpcs:disable WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	/**
	 * Stub WooCommerce's main function.
	 *
	 * @return object
	 */
	function WC(): object {
		return (object) [];
	}
	// phpcs:enable WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
}

if ( ! function_exists( 'wc_get_attribute_taxonomies' ) ) {
	/**
	 * Stub WooCommerce's registered attribute taxonomy lookup.
	 *
	 * @return object[]
	 */
	function wc_get_attribute_taxonomies(): array {
		return $GLOBALS['cyr2lat_wc_attribute_taxonomies'] ?? [];
	}
}
