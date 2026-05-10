<?php
/**
 * Main class of the plugin.
 *
 * @package cyr-to-lat
 */

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpUndefinedNamespaceInspection */
/** @noinspection PhpUndefinedClassInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

namespace CyrToLat;

use Automattic\WooCommerce\Utilities\FeaturesUtil;
use CyrToLat\BackgroundProcesses\PostConversionProcess;
use CyrToLat\BackgroundProcesses\TermConversionProcess;
use CyrToLat\Settings\Converter as SettingsConverter;
use CyrToLat\Settings\SystemInfo as SettingsSystemInfo;
use CyrToLat\Settings\Tables as SettingsTables;
use CyrToLat\Slugs\FilenameService;
use CyrToLat\Slugs\GlobalAttributeService;
use CyrToLat\Slugs\LegacySanitizeTitleBridge;
use CyrToLat\Slugs\LocalAttributeService;
use CyrToLat\Slugs\OldSlugRedirectService;
use CyrToLat\Slugs\PostSlugService;
use CyrToLat\Slugs\TermSlugService;
use CyrToLat\Slugs\VariationAttributeService;
use CyrToLat\Transliteration\Transliterator;
use JsonException;
use Polylang;
use SitePress;
use stdClass;
use WP_CLI;
use WP_Error;
use WP_Post;
use wpdb;
use CyrToLat\Settings\Settings;

/**
 * Class Main
 */
class Main {

	/**
	 * Request type.
	 *
	 * @var Request
	 */
	protected Request $request;

	/**
	 * Plugin settings.
	 *
	 * @var Settings
	 */
	protected Settings $settings;

	/**
	 * Transliterator instance.
	 *
	 * @var Transliterator
	 */
	protected Transliterator $transliterator;

	/**
	 * Process posts instance.
	 *
	 * @var PostConversionProcess|null
	 */
	protected ?PostConversionProcess $process_all_posts = null;

	/**
	 * Process terms instance.
	 *
	 * @var TermConversionProcess|null
	 */
	protected ?TermConversionProcess $process_all_terms = null;

	/**
	 * Admin Notices instance.
	 *
	 * @var AdminNotices
	 */
	protected AdminNotices $admin_notices;

	/**
	 * Converter instance.
	 *
	 * @var Converter|null
	 */
	protected ?Converter $converter = null;

	/**
	 * WP_CLI instance.
	 *
	 * @var WPCli|null
	 */
	protected ?WPCli $cli = null;

	/**
	 * ACF instance.
	 *
	 * @var ACF|null
	 */
	protected ?ACF $acf = null;

	/**
	 * Term slug service.
	 *
	 * @var TermSlugService|null
	 */
	private ?TermSlugService $term_slug_service = null;

	/**
	 * Global attribute service.
	 *
	 * @var GlobalAttributeService|null
	 */
	protected ?GlobalAttributeService $global_attribute_service = null;

	/**
	 * Local attribute service.
	 *
	 * @var LocalAttributeService|null
	 */
	protected ?LocalAttributeService $local_attribute_service = null;

	/**
	 * Variation attribute service.
	 *
	 * @var VariationAttributeService|null
	 */
	private ?VariationAttributeService $variation_attribute_service = null;

	/**
	 * Legacy sanitize title bridge.
	 *
	 * @var LegacySanitizeTitleBridge|null
	 */
	private ?LegacySanitizeTitleBridge $legacy_sanitize_title_bridge = null;

	/**
	 * Polylang locale.
	 *
	 * @var string|null
	 */
	private ?string $pll_locale = null;

	/**
	 * WPML locale.
	 *
	 * @var string|null
	 */
	protected ?string $wpml_locale = null;

	/**
	 * WPML languages.
	 *
	 * @var array
	 */
	protected array $wpml_languages = [];

	/**
	 * The current request is frontend.
	 *
	 * @var bool|null
	 */
	protected ?bool $is_frontend = null;

	/**
	 * Init plugin.
	 *
	 * @return void
	 */
	public function init(): void {
		add_action( 'plugins_loaded', [ $this, 'init_all' ], - PHP_INT_MAX );
	}

	/**
	 * Init all plugin stuffs.
	 *
	 * @return void
	 */
	public function init_all(): void {
		$this->load_textdomain();

		$this->init_multilingual();
		$this->init_classes();
		$this->init_hooks();
	}

	/**
	 * Load plugin text domain.
	 *
	 * @return void
	 */
	public function load_textdomain(): void {
		load_default_textdomain();
		load_plugin_textdomain(
			'cyr2lat',
			false,
			dirname( plugin_basename( constant( 'CYR_TO_LAT_FILE' ) ) ) . '/languages/'
		);
	}

	/**
	 * Init multilingual features.
	 * It must be first in the init sequence, as we use defined filters internally in our classes.
	 *
	 * @return void
	 */
	protected function init_multilingual(): void {
		if ( class_exists( Polylang::class ) ) {
			add_filter( 'locale', [ $this, 'pll_locale_filter' ] );
		}

		if ( class_exists( SitePress::class ) ) {
			$this->wpml_locale = $this->get_wpml_locale();

			// We cannot use locale filter here
			// as WPML reverts locale at PHP_INT_MAX in \WPML\ST\MO\Hooks\LanguageSwitch::filterLocale.
			add_filter( 'ctl_locale', [ $this, 'wpml_locale_filter' ], - PHP_INT_MAX );

			add_action( 'wpml_language_has_switched', [ $this, 'wpml_language_has_switched' ], 10, 3 );
		}
	}

	/**
	 * Init other classes.
	 *
	 * @return void
	 */
	protected function init_classes(): void {
		( new ErrorHandler() )->init();

		$this->request  = new Request();
		$this->settings = new Settings(
			[
				'Cyr To Lat' => [
					SettingsTables::class,
					SettingsConverter::class,
					SettingsSystemInfo::class,
				],
			]
		);

		$this->admin_notices = new AdminNotices();
		$requirements        = new Requirements( $this->settings, $this->admin_notices );

		if ( ! $requirements->are_requirements_met() ) {
			return;
		}

		$this->transliterator = new Transliterator( $this->settings );

		$this->process_all_posts = new PostConversionProcess( $this );
		$this->process_all_terms = new TermConversionProcess( $this );
		$this->converter         = new Converter(
			$this,
			$this->settings,
			$this->process_all_posts,
			$this->process_all_terms,
			$this->admin_notices
		);

		$this->acf         = new ACF( $this->settings );
		$this->is_frontend = $this->request->is_frontend();
	}

	/**
	 * Init hooks.
	 */
	protected function init_hooks(): void {
		if ( $this->is_frontend ) {
			add_action( 'woocommerce_before_template_part', [ $this, 'woocommerce_before_template_part_filter' ] );
			add_action( 'woocommerce_after_template_part', [ $this, 'woocommerce_after_template_part_filter' ] );
		}

		if ( ! $this->request->is_allowed() ) {
			return;
		}

		add_filter( 'sanitize_title', [ $this, 'sanitize_title' ], 9, 3 );
		add_filter( 'sanitize_file_name', [ $this, 'sanitize_filename' ], 10, 2 );
		add_filter( 'wp_insert_post_data', [ $this, 'sanitize_post_name' ], 10, 4 );
		add_filter( 'pre_insert_term', [ $this, 'pre_insert_term_filter' ], PHP_INT_MAX, 2 );
		add_filter( 'pre_term_slug', [ $this, 'sanitize_term_slug' ], 8 );
		add_filter( 'post_updated', [ $this, 'check_for_changed_slugs' ], 10, 3 );
		add_action( 'woocommerce_before_product_object_save', [ $this, 'normalize_wc_product_attribute_keys' ] );

		if ( ! $this->is_frontend || class_exists( SitePress::class ) ) {
			add_filter( 'get_terms_args', [ $this, 'get_terms_args_filter' ], PHP_INT_MAX, 2 );
		}

		add_action( 'before_woocommerce_init', [ $this, 'declare_wc_compatibility' ] );

		if ( $this->request->is_cli() ) {
			add_action( 'cli_init', [ $this, 'action_cli_init' ] );
		}
	}

	/**
	 * Action cli init.
	 *
	 * @return void
	 */
	public function action_cli_init(): void {
		$this->cli = new WPCli( $this->converter );

		/**
		 * Method WP_CLI::add_command() accepts a class as callable.
		 *
		 * @noinspection PhpParamsInspection
		 */
		WP_CLI::add_command( 'cyr2lat', $this->cli );
	}

	/**
	 * Get a Settings instance.
	 *
	 * @return Settings
	 */
	public function settings(): Settings {
		return $this->settings;
	}

	/**
	 * Sanitize title.
	 *
	 * @param string|mixed $title     Sanitized title.
	 * @param string|mixed $raw_title The title prior to sanitization.
	 * @param string|mixed $context   The context for which the title is being sanitized.
	 *
	 * @return string
	 * @noinspection PhpUnusedParameterInspection
	 * @noinspection PhpMissingReturnTypeInspection
	 * @noinspection ReturnTypeCanBeDeclaredInspection
	 */
	public function sanitize_title( $title, $raw_title = '', $context = '' ): string {
		$title     = (string) $title;
		$raw_title = (string) $raw_title;
		$context   = (string) $context;

		return $this->legacy_sanitize_title_bridge()->sanitize_title( $title, $raw_title, $context );
	}

	/**
	 * WC before template part filter.
	 * Add the sanitize_title filter to support transliteration of WC attributes on the frontend.
	 *
	 * @return void
	 */
	public function woocommerce_before_template_part_filter(): void {
		add_filter( 'sanitize_title', [ $this, 'sanitize_title' ], 9, 3 );
	}

	/**
	 * WC after the template part filter.
	 * Remove the sanitize_title filter after supporting transliteration of WC attributes on the frontend.
	 *
	 * @return void
	 */
	public function woocommerce_after_template_part_filter(): void {
		if ( $this->request->is_allowed() ) {
			return;
		}

		remove_filter( 'sanitize_title', [ $this, 'sanitize_title' ], 9 );
	}

	/**
	 * Check if the title is a local attribute.
	 *
	 * @param string $title Title.
	 *
	 * @return bool
	 */
	public function is_local_attribute( string $title ): bool {
		return $this->local_attribute_service()->is_local_attribute( $title );
	}

	/**
	 * Normalize WooCommerce product attribute keys.
	 *
	 * @param object $product Product.
	 *
	 * @return void
	 */
	public function normalize_wc_product_attribute_keys( object $product ): void {
		$this->local_attribute_service()->normalize_product_attributes( $product, [ $this, 'transliterate' ] );
		$this->variation_attribute_service()->normalize_variation_attributes( $product, [ $this, 'transliterate' ] );
	}

	/**
	 * Check if title is an attribute.
	 *
	 * @param string $title Title.
	 *
	 * @return bool
	 * @noinspection PhpUndefinedFunctionInspection
	 */
	protected function is_wc_attribute( string $title ): bool {
		return $this->global_attribute_service()->should_preserve_attribute_title(
			$title,
			[ $this, 'is_local_attribute' ]
		);
	}

	/**
	 * Get global attribute service.
	 *
	 * @return GlobalAttributeService
	 */
	private function global_attribute_service(): GlobalAttributeService {
		if ( null === $this->global_attribute_service ) {
			$this->global_attribute_service = new GlobalAttributeService();
		}

		return $this->global_attribute_service;
	}

	/**
	 * Get local attribute service.
	 *
	 * @return LocalAttributeService
	 */
	private function local_attribute_service(): LocalAttributeService {
		if ( null === $this->local_attribute_service ) {
			$this->local_attribute_service = new LocalAttributeService( $this->variation_attribute_service() );
		}

		return $this->local_attribute_service;
	}

	/**
	 * Get variation attribute service.
	 *
	 * @return VariationAttributeService
	 */
	private function variation_attribute_service(): VariationAttributeService {
		if ( null === $this->variation_attribute_service ) {
			$this->variation_attribute_service = new VariationAttributeService();
		}

		return $this->variation_attribute_service;
	}

	/**
	 * Get legacy sanitize title bridge.
	 *
	 * @return LegacySanitizeTitleBridge
	 */
	private function legacy_sanitize_title_bridge(): LegacySanitizeTitleBridge {
		if ( null === $this->legacy_sanitize_title_bridge ) {
			$this->legacy_sanitize_title_bridge = new LegacySanitizeTitleBridge(
				$this,
				$this->term_slug_service(),
				(bool) $this->is_frontend,
				function ( string $title ): bool {
					return $this->is_wc_attribute( $title );
				}
			);
		}

		return $this->legacy_sanitize_title_bridge;
	}

	/**
	 * Sanitize filename.
	 *
	 * @param string|mixed $filename     Sanitized filename.
	 * @param string|mixed $filename_raw The filename prior to sanitization.
	 *
	 * @return string
	 * @noinspection PhpUnusedParameterInspection
	 * @noinspection PhpMissingReturnTypeInspection
	 * @noinspection ReturnTypeCanBeDeclaredInspection
	 */
	public function sanitize_filename( $filename, $filename_raw ) {
		return ( new FilenameService( $this->transliterator ) )->sanitize_filename( $filename, $filename_raw );
	}

	/**
	 * Get min suffix.
	 *
	 * @return string
	 */
	public function min_suffix(): string {
		return defined( 'SCRIPT_DEBUG' ) && constant( 'SCRIPT_DEBUG' ) ? '' : '.min';
	}

	/**
	 * Transliterate string using a table.
	 *
	 * @param string $str String.
	 *
	 * @return string
	 */
	public function transliterate( string $str ): string {
		return $this->transliterator->transliterate( $str );
	}

	/**
	 * Sanitize post name.
	 *
	 * @param array|mixed $data                An array of slashed, sanitized, and processed post data.
	 * @param array       $postarr             An array of sanitized (and slashed) but otherwise unmodified post data.
	 * @param array       $unsanitized_postarr An array of slashed yet *unsanitized* and unprocessed post data as
	 *                                         originally passed to wp_insert_post().
	 * @param bool        $update              Whether this is an existing post being updated.
	 *
	 * @return array
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function sanitize_post_name( $data, array $postarr = [], array $unsanitized_postarr = [], bool $update = false ): array {
		$data = (array) $data;

		return (
		( new PostSlugService( [ $this, 'sanitize_explicit_slug' ] ) )
			->filter_post_data( $data, $postarr, $unsanitized_postarr, $update )
		);
	}

	/**
	 * Filters a term before it is sanitized and inserted into the database.
	 *
	 * @param string|int|WP_Error $term     The term name to add, or a WP_Error object if there's an error.
	 * @param string              $taxonomy Taxonomy slug.
	 *
	 * @return string|int|WP_Error
	 */
	public function pre_insert_term_filter( $term, string $taxonomy ) {
		return $this->term_slug_service()->pre_insert_term_filter( $term, $taxonomy );
	}

	/**
	 * Sanitize term slug through the explicit term slug service.
	 *
	 * @param string|mixed $slug Term slug.
	 *
	 * @return string|mixed
	 */
	public function sanitize_term_slug( $slug ) {
		if ( ! is_string( $slug ) ) {
			return $slug;
		}

		return $this->term_slug_service()->filter_term_slug( $slug, [ $this, 'sanitize_explicit_slug' ] );
	}

	/**
	 * Sanitize an explicit slug value without using the broad legacy bridge.
	 *
	 * @param string $slug Slug.
	 *
	 * @return string
	 */
	public function sanitize_explicit_slug( string $slug ): string {
		$slug = $this->transliterate( $slug );

		return sanitize_title_with_dashes( $slug );
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
		return $this->term_slug_service()->get_terms_args_filter( $args, $taxonomies );
	}

	/**
	 * Get term slug service.
	 *
	 * @return TermSlugService
	 */
	private function term_slug_service(): TermSlugService {
		if ( null === $this->term_slug_service ) {
			$this->term_slug_service = new TermSlugService( $this );
		}

		return $this->term_slug_service;
	}

	/**
	 * Locale filter for Polylang.
	 *
	 * @param string|mixed $locale Locale.
	 *
	 * @return string|mixed
	 */
	public function pll_locale_filter( $locale ) {
		if ( $this->pll_locale ) {
			return $this->pll_locale;
		}

		$rest_locale = $this->pll_locale_filter_with_rest();

		if ( false === $rest_locale ) {
			return $locale;
		}

		if ( $rest_locale ) {
			$this->pll_locale = $rest_locale;

			return $this->pll_locale;
		}

		if ( ! is_admin() ) {
			return $locale;
		}

		if ( ! $this->request->is_post() ) {
			return $locale;
		}

		$pll_get_post_language = $this->pll_locale_filter_with_classic_editor();

		if ( $pll_get_post_language ) {
			$this->pll_locale = $pll_get_post_language;

			return $this->pll_locale;
		}

		$pll_get_term_language = $this->pll_locale_filter_with_term();

		if ( $pll_get_term_language ) {
			$this->pll_locale = $pll_get_term_language;

			return $this->pll_locale;
		}

		return $locale;
	}

	/**
	 * Locale filter for Polylang with REST request.
	 *
	 * @return false|null|string
	 */
	private function pll_locale_filter_with_rest() {
		if ( ! defined( 'REST_REQUEST' ) || ! constant( 'REST_REQUEST' ) ) {
			return null;
		}

		/**
		 * REST Server.
		 *
		 * @var WP_REST_Server $rest_server
		 */
		$rest_server = rest_get_server();

		try {
			$data = json_decode( $rest_server::get_raw_data(), false, 512, JSON_THROW_ON_ERROR );
		} catch ( JsonException $e ) {
			$data = new stdClass();
		}

		return $data->lang ?? false;
	}

	/**
	 * Locale filter for Polylang with the classic editor.
	 *
	 * @return bool|string
	 * @noinspection PhpUndefinedFunctionInspection
	 */
	private function pll_locale_filter_with_classic_editor() {
		if ( ! function_exists( 'pll_get_post_language' ) ) {
			return false;
		}

		$pll_get_post_language = false;

		// phpcs:disable WordPress.Security.NonceVerification.Missing
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( isset( $_POST['post_ID'] ) ) {
			$pll_get_post_language = pll_get_post_language(
				(int) filter_input( INPUT_POST, 'post_ID', FILTER_SANITIZE_FULL_SPECIAL_CHARS ),
				'locale'
			);
		}
		if ( isset( $_POST['pll_post_id'] ) ) {
			$pll_get_post_language = pll_get_post_language(
				(int) filter_input( INPUT_POST, 'pll_post_id', FILTER_SANITIZE_FULL_SPECIAL_CHARS ),
				'locale'
			);
		}
		if ( isset( $_GET['post'] ) ) {
			$pll_get_post_language = pll_get_post_language(
				(int) filter_input( INPUT_GET, 'post', FILTER_SANITIZE_FULL_SPECIAL_CHARS ),
				'locale'
			);
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		return $pll_get_post_language;
	}

	/**
	 * Locale filter for Polylang with term.
	 *
	 * @return false|string
	 * @noinspection PhpUndefinedFunctionInspection
	 */
	private function pll_locale_filter_with_term() {
		if ( ! function_exists( 'PLL' ) ) {
			return false;
		}

		$pll_get_term_language = false;

		// phpcs:disable WordPress.Security.NonceVerification.Missing
		if ( isset( $_POST['term_lang_choice'] ) ) {
			$pll_get_language = PLL()->model->get_language(
				filter_input( INPUT_POST, 'term_lang_choice', FILTER_SANITIZE_FULL_SPECIAL_CHARS )
			);

			if ( $pll_get_language ) {
				$pll_get_term_language = $pll_get_language->locale;
			}
		}

		// phpcs:enable WordPress.Security.NonceVerification.Missing

		return $pll_get_term_language;
	}

	/**
	 * Locale filter for WPML.
	 *
	 * @param string|mixed $locale Locale.
	 *
	 * @return string|null|mixed
	 */
	public function wpml_locale_filter( $locale ) {
		if ( $this->wpml_locale ) {
			return $this->wpml_locale;
		}

		return $locale;
	}

	/**
	 * Get wpml locale.
	 *
	 * @return string|null
	 * @noinspection PhpUndefinedFunctionInspection
	 */
	protected function get_wpml_locale(): ?string {
		$language_code        = wpml_get_current_language();
		$this->wpml_languages = (array) apply_filters( 'wpml_active_languages', [] );

		return $this->wpml_languages[ $language_code ]['default_locale'] ?? null;
	}

	/**
	 * Save switched locale.
	 *
	 * @param null|string $language_code     Language code to switch into.
	 * @param bool|string $cookie_lang       Optionally also switch the cookie language to the value given.
	 * @param string      $original_language Original language.
	 *
	 * @return void
	 * @noinspection PhpUnusedParameterInspection
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function wpml_language_has_switched( $language_code, $cookie_lang, string $original_language ): void {
		$language_code = (string) $language_code;

		$this->wpml_locale = $this->wpml_languages[ $language_code ]['default_locale'] ?? null;
	}

	/**
	 * Checks for changed slugs for published post objects to save the old slug.
	 *
	 * @param int     $post_id     Post ID.
	 * @param WP_Post $post        The post object.
	 * @param WP_Post $post_before The previous post object.
	 *
	 * @noinspection PhpMissingParamTypeInspection
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function check_for_changed_slugs( $post_id, $post, $post_before ): void {
		$service = new OldSlugRedirectService( $this->transliterator );

		$service->check_for_changed_slugs( (int) $post_id, $post, $post_before );
	}

	/**
	 * Declare compatibility with custom order tables for WooCommerce.
	 *
	 * @return void
	 */
	public function declare_wc_compatibility(): void {
		if ( class_exists( FeaturesUtil::class ) ) {
			FeaturesUtil::declare_compatibility(
				'custom_order_tables',
				constant( 'CYR_TO_LAT_FILE' )
			);
		}
	}

	/**
	 * Changes an array of items into a string of items, separated by comma and sql-escaped.
	 *
	 * @see https://coderwall.com/p/zepnaw
	 * @global wpdb       $wpdb
	 *
	 * @param mixed|array $items  item(s) to be joined into string.
	 * @param string      $format %s or %d.
	 *
	 * @return string Items separated by comma and sql-escaped.
	 */
	public function prepare_in( $items, string $format = '%s' ): string {
		global $wpdb;

		$prepared_in = '';
		$items       = (array) $items;
		$how_many    = count( $items );

		if ( $how_many > 0 ) {
			$placeholders    = array_fill( 0, $how_many, $format );
			$prepared_format = implode( ',', $placeholders );
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$prepared_in = $wpdb->prepare( $prepared_format, $items );
		}

		return $prepared_in;
	}
}
