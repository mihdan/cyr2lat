<?php
/**
 * Settings class file.
 *
 * @package cyr-to-lat
 */

namespace CyrToLat\Settings;

use CyrToLat\Settings\Abstracts\SettingsBase;
use CyrToLat\Settings\Abstracts\SettingsInterface;
use CyrToLat\Symfony\Polyfill\Mbstring\Mbstring;

/**
 * Class Settings
 *
 * The central point to get settings from.
 */
class Settings implements SettingsInterface {

	/**
	 * Menu pages class names.
	 *
	 * @var array
	 */
	protected $menu_pages_classes;

	/**
	 * Menu pages and tabs in one flat array.
	 *
	 * @var array
	 */
	protected $tabs = [];

	/**
	 * Screen ids of pages and tabs.
	 *
	 * @var array
	 */
	private $screen_ids = [];

	/**
	 * Settings constructor.
	 *
	 * @param array $menu_pages_classes Menu pages.
	 */
	public function __construct( array $menu_pages_classes = [] ) {
		// Allow to specify $menu_pages_classes item as one class, not an array.
		$this->menu_pages_classes = $menu_pages_classes;

		$this->init();
	}

	/**
	 * Init class.
	 */
	protected function init() {
		foreach ( $this->menu_pages_classes as $menu_page_classes ) {
			$tab_classes = (array) $menu_page_classes;

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
			$menu_page = new $page_class( $tabs );

			$this->tabs[] = [ $menu_page ];
			$this->tabs[] = $tabs;
		}

		$this->tabs = array_merge( [], ...$this->tabs );
	}

	/**
	 * Get tabs.
	 *
	 * @return array
	 */
	public function get_tabs(): array {
		return $this->tabs;
	}

	/**
	 * Get plugin option.
	 *
	 * @param string $key         Setting name.
	 * @param mixed  $empty_value Empty value for this setting.
	 *
	 * @return string|array The value specified for the option or a default value for the option.
	 */
	public function get( string $key, $empty_value = null ) {
		$value = '';

		foreach ( $this->tabs as $tab ) {
			/**
			 * Page / Tab.
			 *
			 * @var SettingsBase $tab
			 */
			$value = $tab->get( $key, $empty_value );

			if ( ! empty( $value ) ) {
				break;
			}
		}

		if ( '' === $value && ! is_null( $empty_value ) ) {
			$value = $empty_value;
		}

		return $value;
	}

	/**
	 * Check whether option value equals to the compared.
	 *
	 * @param string $key     Setting name.
	 * @param string $compare Compared value.
	 *
	 * @return bool
	 */
	public function is( string $key, string $compare ): bool {
		$value = $this->get( $key );

		if ( is_array( $value ) ) {
			return in_array( $compare, $value, true );
		}

		return $value === $compare;
	}

	/**
	 * Check whether option value is 'on' or just non-empty.
	 *
	 * @param string $key Setting name.
	 *
	 * @return bool
	 * @noinspection PhpUnused
	 */
	public function is_on( string $key ): bool {
		return ! empty( $this->get( $key ) );
	}

	/**
	 * Set field.
	 *
	 * @param string $key       Setting name.
	 * @param string $field_key Field key.
	 * @param mixed  $value     Value.
	 *
	 * @return void
	 * @noinspection PhpUnused
	 */
	public function set_field( string $key, string $field_key, $value ) {
		foreach ( $this->tabs as $tab ) {
			/**
			 * Page / Tab.
			 *
			 * @var SettingsBase $tab
			 */
			if ( $tab->set_field( $key, $field_key, $value ) ) {
				break;
			}
		}
	}

	/**
	 * Get screen ids of all settings pages and tabs.
	 *
	 * @return array
	 */
	public function screen_ids(): array {
		return $this->screen_ids;
	}

	/**
	 * Get transliteration table.
	 *
	 * @return array
	 */
	public function get_table(): array {
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
	public function is_chinese_locale(): bool {
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
	protected function transpose_chinese_table( array $table ): array {
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
