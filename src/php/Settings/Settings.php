<?php
/**
 * Settings class file.
 *
 * @package cyr-to-lat
 */

namespace Cyr_To_Lat\Settings;

use Cyr_To_Lat\Settings\Abstracts\SettingsBase;
use Cyr_To_Lat\Settings\Abstracts\SettingsInterface;
use Cyr_To_Lat\Symfony\Polyfill\Mbstring\Mbstring;

/**
 * Class Settings
 *
 * Central point to get settings from.
 */
class Settings implements SettingsInterface {

	/**
	 * Menu pages classes.
	 */
	const MENU_PAGES = [
		[ Tables::class, Converter::class ],
	];

	/**
	 * Menu pages class instances.
	 *
	 * @var array
	 */
	protected $menu_pages = [];

	/**
	 * Screen ids of pages and tabs.
	 *
	 * @var array
	 */
	private $screen_ids = [];

	/**
	 * Settings constructor.
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Init class.
	 *
	 * @noinspection UnnecessaryCastingInspection
	 * @noinspection PhpCastIsUnnecessaryInspection
	 */
	protected function init() {
		// Allow to specify MENU_PAGES item as one class, not an array.
		$menu_pages = (array) self::MENU_PAGES;

		foreach ( $menu_pages as $menu_page ) {
			$tab_classes = (array) $menu_page;

			// Allow to specify menu page as one class, without tabs.
			$page_class  = $tab_classes[0];
			$tab_classes = array_slice( $tab_classes, 1 );

			$tabs = [];
			foreach ( $tab_classes as $tab_class ) {
				/**
				 * Tab.
				 *
				 * @var PluginSettingsBase $tab
				 */
				$tab                = new $tab_class( null );
				$tabs[]             = $tab;
				$this->screen_ids[] = $tab->screen_id();
			}

			/**
			 * Page.
			 *
			 * @var PluginSettingsBase $page_class
			 */
			$this->menu_pages[] = new $page_class( $tabs );
		}
	}

	/**
	 * Get plugin option.
	 *
	 * @param string $key         Setting name.
	 * @param mixed  $empty_value Empty value for this setting.
	 *
	 * @return string|array The value specified for the option or a default value for the option.
	 */
	public function get( $key, $empty_value = null ) {
		$value = '';

		foreach ( $this->menu_pages as $menu_page ) {
			/**
			 * Menu page.
			 *
			 * @var SettingsBase $menu_page
			 */
			$value = $menu_page->get( $key, $empty_value );
			if ( ! empty( $value ) ) {
				break;
			}

			$tabs = $menu_page->get_tabs();

			foreach ( $tabs as $tab ) {
				/**
				 * Tab.
				 *
				 * @var SettingsBase $tab
				 */
				$value = $tab->get( $key, $empty_value );
				if ( ! empty( $value ) ) {
					break 2;
				}
			}
		}

		if ( '' === $value && ! is_null( $empty_value ) ) {
			$value = $empty_value;
		}

		return $value;
	}

	/**
	 * Get screen ids of all settings pages and tabs.
	 *
	 * @return array
	 */
	public function screen_ids() {
		return $this->screen_ids;
	}

	/**
	 * Get transliteration table.
	 *
	 * @return array
	 */
	public function get_table() {
		// List of locales: https://make.wordpress.org/polyglots/teams/.
		$locale = (string) apply_filters( 'ctl_locale', get_locale() );
		$table  = $this->get( $locale );
		if ( empty( $table ) ) {
			$table = $this->get( 'iso9' );
		}

		return $this->transpose_chinese_table( $table );
	}

	/**
	 * Is current locale a Chinese one.
	 *
	 * @return bool
	 */
	public function is_chinese_locale() {
		$chinese_locales = [ 'zh_CN', 'zh_HK', 'zh_SG', 'zh_TW' ];

		return in_array( get_locale(), $chinese_locales, true );
	}

	/**
	 * Transpose Chinese table.
	 *
	 * Chinese tables are stored in different way, to show them compact.
	 *
	 * @param array $table Table.
	 *
	 * @return array
	 */
	protected function transpose_chinese_table( $table ) {
		if ( ! $this->is_chinese_locale() ) {
			return $table;
		}

		$transposed_table = [];
		foreach ( $table as $key => $item ) {
			$hieroglyphs = Mbstring::mb_str_split( $item );
			foreach ( $hieroglyphs as $hieroglyph ) {
				$transposed_table[ $hieroglyph ] = $key;
			}
		}

		return $transposed_table;
	}
}
