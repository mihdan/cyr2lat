<?php
/**
 * Reproduce WooCommerce local attribute behavior across Cyr-To-Lat upgrades.
 *
 * Run from CLI against a real WordPress install:
 *
 * php tests/manual/woocommerce-local-attribute-upgrade-repro.php seed --wp-load=C:/path/to/wordpress/wp-load.php
 * php tests/manual/woocommerce-local-attribute-upgrade-repro.php probe --wp-load=C:/path/to/wordpress/wp-load.php
 * php tests/manual/woocommerce-local-attribute-upgrade-repro.php seed-any --wp-load=C:/path/to/wordpress/wp-load.php
 * php tests/manual/woocommerce-local-attribute-upgrade-repro.php probe-any --wp-load=C:/path/to/wordpress/wp-load.php
 * php tests/manual/woocommerce-local-attribute-upgrade-repro.php synthetic --wp-load=C:/path/to/wordpress/wp-load.php
 * php tests/manual/woocommerce-local-attribute-upgrade-repro.php cleanup --wp-load=C:/path/to/wordpress/wp-load.php
 *
 * Add --activate on a disposable test site to activate WooCommerce and the
 * default cyr2lat/cyr-to-lat.php plugin path before running the command.
 * Add --integration-bootstrap to run against this checkout through the
 * repository's WordPress PHPUnit bootstrap instead of a site's active plugin.
 *
 * Intended flow:
 * 1. Activate WooCommerce and Cyr-To-Lat 6.8.0.
 * 2. Run `seed` to create a variable product with a Cyrillic local attribute.
 * 3. Switch the same site to the current Cyr-To-Lat 7.0.x code.
 * 4. Run `probe` and inspect the report/add-to-cart result.
 *
 * The `synthetic` command creates the legacy encoded metadata shape directly
 * under the currently active plugin, which is useful when switching plugin
 * versions is inconvenient.
 *
 * @package cyr-to-lat
 */

declare(strict_types=1);

// phpcs:disable WordPress.WP.AlternativeFunctions.file_system_operations_fwrite, Generic.Metrics.CyclomaticComplexity.TooHigh

const CYR2LAT_WC_REPRO_OPTION = 'cyr2lat_wc_local_attribute_upgrade_repro';

main( $argv );

/**
 * Run the script.
 *
 * @param array<int, string> $argv CLI arguments.
 *
 * @return void
 */
function main( array $argv ): void {
	$args    = parse_args( $argv );
	$command = $args['_'][1] ?? '';

	$require_any_variation = false;

	if ( in_array( $command, [ 'seed-any', 'synthetic-any', 'probe-any' ], true ) ) {
		$args['any-variation'] = true;
		$require_any_variation = true;
		$command               = str_replace( '-any', '', $command );
	}

	$GLOBALS['cyr2lat_wc_repro_any_variation']         = ! empty( $args['any-variation'] );
	$GLOBALS['cyr2lat_wc_repro_require_any_variation'] = $require_any_variation || ! empty( $args['require-any-variation'] );

	if ( '' === $command || isset( $args['help'] ) ) {
		print_usage();
		exit( 0 );
	}

	bootstrap_wordpress( (string) ( $args['wp-load'] ?? '' ), ! empty( $args['integration-bootstrap'] ) );
	ensure_runtime( $args );

	switch ( $command ) {
		case 'seed':
			report( seed_product_with_active_plugin( 'seed' ) );
			break;
		case 'synthetic':
			report( seed_synthetic_legacy_product() );
			break;
		case 'probe':
			report( probe_product() );
			break;
		case 'cleanup':
			report( cleanup_product() );
			break;
		default:
			fwrite( STDERR, "Unknown command: $command\n\n" );
			print_usage();
			exit( 1 );
	}
}

/**
 * Parse simple CLI arguments.
 *
 * @param array<int, string> $argv CLI arguments.
 *
 * @return array<string, mixed>
 */
function parse_args( array $argv ): array {
	$args = [ '_' => [] ];

	foreach ( $argv as $arg ) {
		if ( 0 !== strpos( $arg, '--' ) ) {
			$args['_'][] = $arg;
			continue;
		}

		$arg = substr( $arg, 2 );

		if ( false === strpos( $arg, '=' ) ) {
			$args[ $arg ] = true;
			continue;
		}

		[$key, $value] = explode( '=', $arg, 2 );
		$args[ $key ]  = $value;
	}

	return $args;
}

/**
 * Print usage.
 *
 * @return void
 */
function print_usage(): void {
	echo "Usage:\n";
	echo "  php tests/manual/woocommerce-local-attribute-upgrade-repro.php seed --wp-load=/path/to/wp-load.php\n";
	echo "  php tests/manual/woocommerce-local-attribute-upgrade-repro.php probe --wp-load=/path/to/wp-load.php\n";
	echo "  php tests/manual/woocommerce-local-attribute-upgrade-repro.php seed-any --wp-load=/path/to/wp-load.php\n";
	echo "  php tests/manual/woocommerce-local-attribute-upgrade-repro.php probe-any --wp-load=/path/to/wp-load.php\n";
	echo "  php tests/manual/woocommerce-local-attribute-upgrade-repro.php synthetic --wp-load=/path/to/wp-load.php\n";
	echo "  php tests/manual/woocommerce-local-attribute-upgrade-repro.php synthetic-any --wp-load=/path/to/wp-load.php\n";
	echo "  php tests/manual/woocommerce-local-attribute-upgrade-repro.php cleanup --wp-load=/path/to/wp-load.php\n";
	echo "\nOptions:\n";
	echo "  --activate\n";
	echo "  --integration-bootstrap\n";
	echo "  --any-variation\n";
	echo "  --require-any-variation\n";
	echo "  --woocommerce-plugin=woocommerce/woocommerce.php\n";
	echo "  --cyr2lat-plugin=cyr2lat/cyr-to-lat.php\n";
}

/**
 * Load WordPress.
 *
 * @param string $wp_load               Path to wp-load.php.
 * @param bool   $integration_bootstrap Whether to use the repository integration bootstrap.
 *
 * @return void
 */
function bootstrap_wordpress( string $wp_load, bool $integration_bootstrap ): void {
	if ( $integration_bootstrap ) {
		require_once dirname( __DIR__ ) . '/integration/bootstrap.php';
		return;
	}

	if ( '' === $wp_load ) {
		$wp_load = locate_wp_load( getcwd() );
	}

	if ( ! file_exists( $wp_load ) ) {
		fwrite( STDERR, "wp-load.php was not found. Pass --wp-load=/absolute/path/to/wp-load.php\n" );
		exit( 1 );
	}

	require_once $wp_load;
}

/**
 * Locate wp-load.php by walking upward from a directory.
 *
 * @param string|false $start Start directory.
 *
 * @return string
 */
function locate_wp_load( $start ): string {
	if ( false === $start ) {
		return '';
	}

	$dir = $start;

	while ( is_string( $dir ) && '' !== $dir && dirname( $dir ) !== $dir ) {
		$candidate = $dir . DIRECTORY_SEPARATOR . 'wp-load.php';

		if ( file_exists( $candidate ) ) {
			return $candidate;
		}

		$dir = dirname( $dir );
	}

	return '';
}

/**
 * Ensure WordPress, WooCommerce, and Cyr-To-Lat are available.
 *
 * @param array<string, mixed> $args CLI arguments.
 *
 * @return void
 * @noinspection PhpUndefinedFunctionInspection
 */
function ensure_runtime( array $args ): void {
	if ( ! function_exists( 'is_plugin_active' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	if ( ! empty( $args['activate'] ) ) {
		if ( ! function_exists( 'WC' ) ) {
			activate_required_plugin( (string) ( $args['woocommerce-plugin'] ?? 'woocommerce/woocommerce.php' ) );
		}

		if ( ! function_exists( 'cyr_to_lat' ) ) {
			activate_required_plugin( (string) ( $args['cyr2lat-plugin'] ?? 'cyr2lat/cyr-to-lat.php' ) );
		}
	}

	if ( ! function_exists( 'WC' ) ) {
		fwrite( STDERR, "WooCommerce is not active. Activate WooCommerce before running this script.\n" );
		exit( 1 );
	}

	if ( ! function_exists( 'cyr_to_lat' ) ) {
		fwrite( STDERR, "Cyr-To-Lat is not active. Activate Cyr-To-Lat before running this script.\n" );
		exit( 1 );
	}

	if ( function_exists( 'cyr_to_lat' ) && did_action( 'plugins_loaded' ) ) {
		cyr_to_lat()->init_all();
	}

	if ( function_exists( 'set_current_screen' ) ) {
		set_current_screen( 'post' );
	}

	if ( function_exists( 'WC' ) && is_object( WC() ) ) {
		WC()->init();
	}

	if ( class_exists( 'WC_Post_Types' ) ) {
		WC_Post_Types::register_taxonomies();
		WC_Post_Types::register_post_types();
	}

	if ( ! function_exists( 'woocommerce_variable_add_to_cart' ) && is_object( WC() ) ) {
		WC()->include_template_functions();
	}

	if ( function_exists( 'woocommerce_variable_add_to_cart' ) && ! has_action( 'woocommerce_variable_add_to_cart', 'woocommerce_variable_add_to_cart' ) ) {
		add_action( 'woocommerce_variable_add_to_cart', 'woocommerce_variable_add_to_cart', 30 );
	}
}

/**
 * Activate a plugin if needed.
 *
 * @param string $plugin Relative plugin path.
 *
 * @return void
 */
function activate_required_plugin( string $plugin ): void {
	if ( '' === $plugin || is_plugin_active( $plugin ) ) {
		return;
	}

	$result = activate_plugin( $plugin );

	if ( is_wp_error( $result ) ) {
		fwrite( STDERR, 'Could not activate ' . $plugin . ': ' . $result->get_error_message() . "\n" );
		exit( 1 );
	}
}

/**
 * Create a variable product through WooCommerce's admin attribute preparation flow.
 *
 * @param string $mode Report mode.
 *
 * @return array<string, mixed>
 * @noinspection PhpUndefinedFunctionInspection
 * @noinspection PhpUndefinedClassInspection
 * @noinspection PhpArrayWriteIsNotUsedInspection
 */
function seed_product_with_active_plugin( string $mode ): array {
	if ( ! class_exists( 'WC_Meta_Box_Product_Data' ) ) {
		require_once WC()->plugin_path() . '/includes/admin/meta-boxes/class-wc-meta-box-product-data.php';
	}

	$data = [
		'attribute_names'      => [ 'Цвет' ],
		'attribute_values'     => [ 'Красный | Синий' ],
		'attribute_position'   => [ 0 ],
		'attribute_visibility' => [ 1 ],
		'attribute_variation'  => [ 1 ],
	];

	$_POST = [
		'action'               => 'editpost',
		'post_type'            => 'product',
		'attribute_names'      => $data['attribute_names'],
		'attribute_values'     => $data['attribute_values'],
		'attribute_position'   => $data['attribute_position'],
		'attribute_visibility' => $data['attribute_visibility'],
		'attribute_variation'  => $data['attribute_variation'],
	];

	$attributes = WC_Meta_Box_Product_Data::prepare_attributes( $data );

	$product = new WC_Product_Variable();
	$product->set_name( 'Cyr-To-Lat WC local attribute upgrade repro' );
	$product->set_status( 'publish' );
	$product->set_catalog_visibility( 'hidden' );
	$product->set_attributes( $attributes );

	$product_id = $product->save();

	$saved_product    = new WC_Product_Variable( $product_id );
	$saved_attributes = $saved_product->get_attributes( 'edit' );
	$attribute_key    = (string) array_key_first( $saved_attributes );

	$variation = new WC_Product_Variation();
	$variation->set_parent_id( $product_id );
	$variation->set_status( 'publish' );
	$variation->set_regular_price( '10' );
	$variation->set_attributes(
		[
			$attribute_key => ! empty( $GLOBALS['cyr2lat_wc_repro_any_variation'] ) ? '' : 'Красный',
		]
	);

	$variation_id = $variation->save();

	WC_Product_Variable::sync( $product_id );
	wc_delete_product_transients( $product_id );

	update_option(
		CYR2LAT_WC_REPRO_OPTION,
		[
			'product_id'    => $product_id,
			'variation_id'  => $variation_id,
			'created_by'    => $mode,
			'version'       => cyr2lat_version(),
			'any_variation' => ! empty( $GLOBALS['cyr2lat_wc_repro_any_variation'] ),
		],
		false
	);

	return build_report( $mode, $product_id, $variation_id );
}

/**
 * Create legacy encoded metadata directly.
 *
 * @return array<string, mixed>
 * @noinspection PhpUndefinedClassInspection
 * @noinspection PhpUndefinedFunctionInspection
 */
function seed_synthetic_legacy_product(): array {
	$product = new WC_Product_Variable();
	$product->set_name( 'Cyr-To-Lat WC synthetic legacy local attribute repro' );
	$product->set_status( 'publish' );
	$product->set_catalog_visibility( 'hidden' );

	$product_id = $product->save();

	$legacy_key = strtolower( rawurlencode( 'цвет' ) );

	update_post_meta(
		$product_id,
		'_product_attributes',
		[
			$legacy_key => [
				'name'         => 'Цвет',
				'value'        => 'Красный | Синий',
				'position'     => 0,
				'is_visible'   => 1,
				'is_variation' => 1,
				'is_taxonomy'  => 0,
			],
		]
	);

	$variation = new WC_Product_Variation();
	$variation->set_parent_id( $product_id );
	$variation->set_status( 'publish' );
	$variation->set_regular_price( '10' );

	$variation_id = $variation->save();

	update_post_meta( $variation_id, 'attribute_' . $legacy_key, ! empty( $GLOBALS['cyr2lat_wc_repro_any_variation'] ) ? '' : 'Красный' );

	WC_Product_Variable::sync( $product_id );
	wc_delete_product_transients( $product_id );

	update_option(
		CYR2LAT_WC_REPRO_OPTION,
		[
			'product_id'    => $product_id,
			'variation_id'  => $variation_id,
			'created_by'    => 'synthetic',
			'version'       => cyr2lat_version(),
			'any_variation' => ! empty( $GLOBALS['cyr2lat_wc_repro_any_variation'] ),
		],
		false
	);

	return build_report( 'synthetic', $product_id, $variation_id );
}

/**
 * Probe the previously seeded product.
 *
 * @return array<string, mixed>
 */
function probe_product(): array {
	$state = get_option( CYR2LAT_WC_REPRO_OPTION, [] );

	if ( ! is_array( $state ) || empty( $state['product_id'] ) || empty( $state['variation_id'] ) ) {
		fwrite( STDERR, "No repro product stored. Run seed or synthetic first.\n" );
		exit( 1 );
	}

	return build_report( 'probe', (int) $state['product_id'], (int) $state['variation_id'] );
}

/**
 * Delete the repro product.
 *
 * @return array<string, mixed>
 */
function cleanup_product(): array {
	$state = get_option( CYR2LAT_WC_REPRO_OPTION, [] );

	if ( is_array( $state ) ) {
		foreach ( [ 'variation_id', 'product_id' ] as $key ) {
			if ( ! empty( $state[ $key ] ) ) {
				wp_delete_post( (int) $state[ $key ], true );
			}
		}
	}

	delete_option( CYR2LAT_WC_REPRO_OPTION );

	return [
		'mode'    => 'cleanup',
		'deleted' => $state,
	];
}

/**
 * Build a diagnostic report for a product.
 *
 * @param string $mode         Report mode.
 * @param int    $product_id   Product ID.
 * @param int    $variation_id Variation ID.
 *
 * @return array<string, mixed>
 * @noinspection PhpUndefinedClassInspection
 */
function build_report( string $mode, int $product_id, int $variation_id ): array {
	$product   = new WC_Product_Variable( $product_id );
	$variation = new WC_Product_Variation( $variation_id );

	$rendered_key  = rendered_variation_request_key( $product );
	$cart_result   = try_add_to_cart( $product_id, $variation_id, $rendered_key );
	$reload_result = reload_cart_from_session_result();

	$raw_product_attributes = get_post_meta( $product_id, '_product_attributes', true );
	$raw_variation_meta     = variation_attribute_meta( $variation_id );
	$available_variations   = normalize_available_variations( $product->get_available_variations() );
	$is_any_variation       = array_key_exists( 'attribute_' . strtolower( rawurlencode( 'цвет' ) ), $raw_variation_meta ) && '' === $raw_variation_meta[ 'attribute_' . strtolower( rawurlencode( 'цвет' ) ) ];
	$possible_problem       = [
		'cart_rejected_rendered_key'           => 1 !== (int) $cart_result['cart_count'],
		'cart_dropped_on_reload'               => (int) $reload_result['cart_count'] !== (int) $cart_result['cart_count'],
		'any_variation_cart_dropped_on_reload' => $is_any_variation && 1 === (int) $cart_result['cart_count'] && 1 !== (int) $reload_result['cart_count'],
		'empty_available_value'                => ! $is_any_variation && has_empty_available_variation_value( $available_variations, $rendered_key ),
		'frontend_key_mismatch'                => has_frontend_variation_key_mismatch( $available_variations, $rendered_key ),
		'wrong_repro_scenario'                 => ! empty( $GLOBALS['cyr2lat_wc_repro_require_any_variation'] ) && ! $is_any_variation,
		'legacy_parent_meta'                   => in_array( strtolower( rawurlencode( 'цвет' ) ), is_array( $raw_product_attributes ) ? array_keys( $raw_product_attributes ) : [], true ),
		'legacy_variation_meta'                => array_key_exists( 'attribute_' . strtolower( rawurlencode( 'цвет' ) ), $raw_variation_meta ),
		'any_variation'                        => $is_any_variation,
	];
	$failed_checks          = failed_checks( $possible_problem );

	return [
		'mode'                         => $mode,
		'scenario'                     => $is_any_variation ? 'any_variation' : 'concrete_variation',
		'result'                       => [] === $failed_checks ? 'pass' : 'fail',
		'failed_checks'                => $failed_checks,
		'cyr2lat_version'              => cyr2lat_version(),
		'product_id'                   => $product_id,
		'variation_id'                 => $variation_id,
		'sanitize_title_color'         => sanitize_title( 'Цвет' ),
		'raw_product_attribute_keys'   => is_array( $raw_product_attributes ) ? array_keys( $raw_product_attributes ) : [],
		'product_get_attribute_keys'   => array_keys( $product->get_attributes( 'edit' ) ),
		'product_get_attributes'       => normalize_product_attributes( $product->get_attributes( 'edit' ) ),
		'variation_attributes'         => $product->get_variation_attributes(),
		'available_variations'         => $available_variations,
		'raw_variation_attribute_meta' => $raw_variation_meta,
		'variation_get_attribute_keys' => array_keys( $variation->get_attributes( 'edit' ) ),
		'variation_get_attributes'     => $variation->get_attributes( 'edit' ),
		'rendered_request_key'         => $rendered_key,
		'cart_result'                  => $cart_result,
		'cart_reload_result'           => $reload_result,
		'possible_problem'             => $possible_problem,
	];
}

/**
 * Return problem keys that should make this repro fail.
 *
 * Legacy metadata flags are informational: old products are expected to keep
 * encoded database keys after an upgrade.
 *
 * @param array<string, bool> $possible_problem Problem map.
 *
 * @return array<int, string>
 */
function failed_checks( array $possible_problem ): array {
	$informational = [
		'legacy_parent_meta'    => true,
		'legacy_variation_meta' => true,
		'any_variation'         => true,
	];

	$failed = [];

	foreach ( $possible_problem as $key => $value ) {
		if ( ! $value || isset( $informational[ $key ] ) ) {
			continue;
		}

		$failed[] = $key;
	}

	return $failed;
}

/**
 * Check whether available variations lost the concrete value for the rendered request key.
 *
 * @param array<int, array<string, mixed>> $available_variations Available variations.
 * @param string                           $request_key          Rendered request key.
 *
 * @return bool
 */
function has_empty_available_variation_value( array $available_variations, string $request_key ): bool {
	if ( '' === $request_key ) {
		return false;
	}

	foreach ( $available_variations as $variation ) {
		$attributes = (array) ( $variation['attributes'] ?? [] );

		if ( array_key_exists( $request_key, $attributes ) && '' === $attributes[ $request_key ] ) {
			return true;
		}
	}

	return false;
}

/**
 * Check whether the rendered form key is absent from available variation attributes.
 *
 * @param array<int, array<string, mixed>> $available_variations Available variations.
 * @param string                           $request_key          Rendered request key.
 *
 * @return bool
 */
function has_frontend_variation_key_mismatch( array $available_variations, string $request_key ): bool {
	if ( '' === $request_key || [] === $available_variations ) {
		return false;
	}

	foreach ( $available_variations as $variation ) {
		$attributes = (array) ( $variation['attributes'] ?? [] );

		if ( array_key_exists( $request_key, $attributes ) ) {
			return false;
		}
	}

	return true;
}

/**
 * Normalize product attributes for JSON output.
 *
 * @param array<string, mixed> $attributes Product attributes.
 *
 * @return array<string, array<string, mixed>>
 * @noinspection PhpCastIsUnnecessaryInspection
 * @noinspection UnnecessaryCastingInspection
 */
function normalize_product_attributes( array $attributes ): array {
	$result = [];

	foreach ( $attributes as $key => $attribute ) {
		if ( ! is_object( $attribute ) ) {
			$result[ (string) $key ] = [ 'value' => $attribute ];
			continue;
		}

		$result[ (string) $key ] = [
			'name'         => method_exists( $attribute, 'get_name' ) ? $attribute->get_name() : null,
			'options'      => method_exists( $attribute, 'get_options' ) ? $attribute->get_options() : null,
			'is_taxonomy'  => method_exists( $attribute, 'is_taxonomy' ) ? $attribute->is_taxonomy() : null,
			'is_variation' => method_exists( $attribute, 'get_variation' ) ? $attribute->get_variation() : null,
		];
	}

	return $result;
}

/**
 * Normalize available variation data for JSON output.
 *
 * @param array<int, array<string, mixed>> $variations Available variations.
 *
 * @return array<int, array<string, mixed>>
 */
function normalize_available_variations( array $variations ): array {
	return array_map(
		static function ( array $variation ): array {
			return [
				'variation_id' => (int) ( $variation['variation_id'] ?? 0 ),
				'attributes'   => (array) ( $variation['attributes'] ?? [] ),
				'is_in_stock'  => (bool) ( $variation['is_in_stock'] ?? false ),
			];
		},
		$variations
	);
}

/**
 * Render the variable product add-to-cart form and extract the local attribute request key.
 *
 * @param WC_Product_Variable $product Product.
 *
 * @return string
 * @noinspection PhpUndefinedClassInspection
 */
function rendered_variation_request_key( WC_Product_Variable $product ): string {
	$GLOBALS['product'] = $product;

	if ( function_exists( 'cyr_to_lat' ) && method_exists( cyr_to_lat(), 'woocommerce_before_template_part_filter' ) ) {
		cyr_to_lat()->woocommerce_before_template_part_filter();
	}

	ob_start();
	do_action( 'woocommerce_variable_add_to_cart' );
	$html = (string) ob_get_clean();

	if ( function_exists( 'cyr_to_lat' ) && method_exists( cyr_to_lat(), 'woocommerce_after_template_part_filter' ) ) {
		cyr_to_lat()->woocommerce_after_template_part_filter();
	}

	unset( $GLOBALS['product'] );

	if ( preg_match( '/name="(attribute_[^"]+)"/', $html, $matches ) ) {
		return $matches[1];
	}

	return '';
}

/**
 * Try adding the variation to the cart using the key rendered by WooCommerce.
 *
 * @param int    $product_id   Product ID.
 * @param int    $variation_id Variation ID.
 * @param string $request_key  Rendered request key.
 *
 * @return array<string, mixed>
 * @noinspection PhpUndefinedFunctionInspection
 * @noinspection PhpArrayWriteIsNotUsedInspection
 */
function try_add_to_cart( int $product_id, int $variation_id, string $request_key ): array {
	if ( ! function_exists( 'wc_load_cart' ) || ! class_exists( 'WC_Form_Handler' ) ) {
		return [
			'skipped' => 'WooCommerce cart classes are unavailable.',
		];
	}

	wc_load_cart();
	WC()->cart->empty_cart();
	wc_clear_notices();

	$_REQUEST = [
		'add-to-cart'  => (string) $product_id,
		'variation_id' => (string) $variation_id,
		'quantity'     => '1',
	];

	if ( '' !== $request_key ) {
		$_REQUEST[ $request_key ] = 'Красный';
	}

	if ( function_exists( 'cyr_to_lat' ) && method_exists( cyr_to_lat(), 'normalize_wc_add_to_cart_request_attributes' ) ) {
		cyr_to_lat()->normalize_wc_add_to_cart_request_attributes();
	}

	WC_Form_Handler::add_to_cart_action();

	$notices = function_exists( 'wc_get_notices' ) ? wc_get_notices( 'error' ) : [];

	return [
		'cart_count' => WC()->cart->get_cart_contents_count(),
		'errors'     => normalize_notices( $notices ),
		'cart'       => normalize_cart( WC()->cart->get_cart() ),
	];
}

/**
 * Reload cart contents from the WooCommerce session.
 *
 * @return array<string, mixed>
 * @noinspection PhpUndefinedFunctionInspection
 */
function reload_cart_from_session_result(): array {
	if ( ! function_exists( 'wc_load_cart' ) || ! is_object( WC()->cart ) || ! is_object( WC()->session ) ) {
		return [
			'skipped'    => 'WooCommerce cart/session classes are unavailable.',
			'cart_count' => 0,
		];
	}

	WC()->session->set( 'cart', WC()->cart->get_cart_for_session() );
	WC()->cart->set_cart_contents( [] );

	$sanitize_title_priority = function_exists( 'cyr_to_lat' ) ? has_filter( 'sanitize_title', [ cyr_to_lat(), 'sanitize_title' ] ) : false;

	if ( false !== $sanitize_title_priority ) {
		remove_filter( 'sanitize_title', [ cyr_to_lat(), 'sanitize_title' ], (int) $sanitize_title_priority );
	}

	try {
		// phpcs:disable PHPCompatibility.FunctionDeclarations.NewClosure.ThisFoundOutsideClass -- Bound to WC_Cart below to access the protected session handler.
		$cart_session = function () {
			return $this->session;
		};
		// phpcs:enable PHPCompatibility.FunctionDeclarations.NewClosure.ThisFoundOutsideClass

		$cart_session = $cart_session->call( WC()->cart );
		$cart_session->get_cart_from_session();
	} finally {
		if ( false !== $sanitize_title_priority ) {
			add_filter( 'sanitize_title', [ cyr_to_lat(), 'sanitize_title' ], (int) $sanitize_title_priority, 3 );
		}
	}

	return [
		'cart_count' => WC()->cart->get_cart_contents_count(),
		'errors'     => normalize_notices( function_exists( 'wc_get_notices' ) ? wc_get_notices( 'error' ) : [] ),
		'notices'    => normalize_notices( function_exists( 'wc_get_notices' ) ? wc_get_notices( 'notice' ) : [] ),
		'cart'       => normalize_cart( WC()->cart->get_cart() ),
	];
}

/**
 * Get raw variation attribute meta.
 *
 * @param int $variation_id Variation ID.
 *
 * @return array<string, string>
 */
function variation_attribute_meta( int $variation_id ): array {
	$meta   = get_post_meta( $variation_id );
	$result = [];

	foreach ( $meta as $key => $values ) {
		if ( 0 !== strpos( (string) $key, 'attribute_' ) ) {
			continue;
		}

		$result[ (string) $key ] = (string) ( $values[0] ?? '' );
	}

	return $result;
}

/**
 * Normalize WooCommerce notices for JSON output.
 *
 * @param mixed $notices Notices.
 *
 * @return array<int, string>
 */
function normalize_notices( $notices ): array {
	$result = [];

	foreach ( (array) $notices as $notice ) {
		if ( is_array( $notice ) ) {
			$result[] = wp_strip_all_tags( (string) ( $notice['notice'] ?? '' ) );
			continue;
		}

		$result[] = wp_strip_all_tags( (string) $notice );
	}

	return $result;
}

/**
 * Normalize cart contents for JSON output.
 *
 * @param array $cart Cart contents.
 *
 * @return array<int, array<string, mixed>>
 */
function normalize_cart( array $cart ): array {
	$result = [];

	foreach ( $cart as $item ) {
		$result[] = [
			'product_id'   => (int) ( $item['product_id'] ?? 0 ),
			'variation_id' => (int) ( $item['variation_id'] ?? 0 ),
			'variation'    => (array) ( $item['variation'] ?? [] ),
			'quantity'     => (int) ( $item['quantity'] ?? 0 ),
		];
	}

	return $result;
}

/**
 * Return the active Cyr-To-Lat version.
 *
 * @return string
 */
function cyr2lat_version(): string {
	return defined( 'CYR_TO_LAT_VERSION' ) ? CYR_TO_LAT_VERSION : 'unknown';
}

/**
 * Print report.
 *
 * @param array<string, mixed> $report Report.
 *
 * @return void
 */
function report( array $report ): void {
	echo wp_json_encode( $report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . PHP_EOL;
}
