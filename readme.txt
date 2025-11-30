=== Cyr-To-Lat ===
Contributors: SergeyBiryukov, mihdan, kaggdesign, karevn, webvitaly
Tags: cyrillic, slugs, translation, transliteration
Requires at least: 5.3
Tested up to: 6.9
Requires PHP: 7.2
Stable tag: 6.6.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Convert Non-Latin characters in post, page and term slugs to Latin characters.

== Description ==

Converts Cyrillic characters in post, page and term slugs to Latin characters. Useful for creating human-readable URLs.

= Features =
* The only plugin with a fully editable transliteration table. Allows to add/remove and edit pairs like 'Я' => 'Ya', or even 'Пиво' => 'Beer'
* Converts any number of existing post, page and term slugs in background processes
* Saves existing post and page permalinks integrity
* Performs transliteration of attachment file names
* The plugin supports Russian, Belorussian, Ukrainian, Bulgarian, Macedonian, Serbian, Greek, Armenian, Georgian, Kazakh, Hebrew, and Chinese characters
* [Has many advantages over similar plugins](https://kagg.eu/en/the-benefits-of-cyr-to-lat/)
* [Officially compatible with WPML](https://wpml.org/plugin/cyr-to-lat/)

<img src="https://ps.w.org/cyr2lat/assets/Cyr-To-Lat---WPML-Compatibility-Certificate-240x250.png" alt="WPML Certificate" />

Based on the original Rus-To-Lat plugin by Anton Skorobogatov.

Sponsored by [Blackfire](https://www.blackfire.io/).

<img src="https://ps.w.org/cyr2lat/assets/blackfire-io_secondary_horizontal_transparent-250x62.png" alt="Blackfire Logo" />

== Screenshots ==

1. Tables settings page
2. Converter settings page
3. Block editor with transliterated slug
4. WPML Certificate

== Plugin Support ==

* [Support Forum](https://wordpress.org/support/plugin/cyr2lat/)
* [Telegram Group](https://t.me/cyr2lat)

== Installation ==

1. Upload the ` cyr2lat ` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.

== Frequently Asked Questions ==

= How can I define my own substitutions? =

Add this code to your theme's `functions.php` file:

`
/**
 * Modify conversion table.
 *
 * @param array $table Conversion table.
 *
 * @return array
 */
function my_ctl_table( $table ) {
   $table['Ъ'] = 'U';
   $table['ъ'] = 'u';

   return $table;
}
add_filter( 'ctl_table', 'my_ctl_table' );
`

= How can I redefine non-standard locale? =

For instance, if your non-standard locale is uk_UA, you can redefine it to `uk` by adding the following code to your theme's `function.php` file:

`
/**
 * Use non-standard locale.
 *
 * @param string $locale Current locale.
 *
 * @return string
 */
function my_ctl_locale( $locale ) {
	if ( 'uk_UA' === $locale ) {
		return 'uk';
	}

	return $locale;
}
add_filter( 'ctl_locale', 'my_ctl_locale' );
`

= How can I define my own transliteration of titles? =

Add similar code to your theme's `functions.php` file:

`
/**
 * Filter title before sanitizing.
 *
 * @param string|false $result Sanitized title.
 * @param string       $title  Title.
 *
 * @return string|false
 */
function my_ctl_pre_sanitize_title( $result, $title ) {
	if ( 'пиво' === $title ) {
		return 'beer';
	}

	return $result;
}
add_filter( 'ctl_pre_sanitize_title', 10, 2 );
`

= How can I define my own transliteration of filenames? =

Add similar code to your theme's `functions.php` file:

`
/**
 * Filter filename before sanitizing.
 *
 * @param string|false $result   Sanitized filename.
 * @param string       $filename Title.
 *
 * @return string|false
 */
function my_ctl_pre_sanitize_filename( $result, $filename ) {
	if ( 'пиво' === $filename ) {
		return 'beer';
	}

	return $result;
}
add_filter( 'ctl_pre_sanitize_filename', 10, 2 );
`

= How can I allow the plugin to work on the frontend? =

Add the following code to your plugin's (or mu-plugin's) main file. This code won't work being added to a theme's functions.php file.

`
/**
 * Filter status allowed Cyr To Lat plugin to work.
 *
 * @param bool $allowed
 *
 * @return bool
 */
function my_ctl_allow( bool $allowed ): bool {
	$uri = isset( $_SERVER['REQUEST_URI'] ) ?
		sanitize_url( wp_unslash( $_SERVER['REQUEST_URI'] ) ) :
		'';

	if ( 0 === strpos( $uri, '/divi-comments' ) ) {
		return true;
	}

	return $allowed;
}

add_filter( 'ctl_allow', 'my_ctl_allow' );
`

= How can I limit post types for background conversion? =

Add similar code to your theme's `functions.php` file:

`
/**
 * Filter post types allowed for background conversion.
 *
 * @param array $post_types Allowed post types.
 *
 * @return array
 */
function my_ctl_post_types( $post_types ) {
	return [
		'post'          => 'post',
		'page'          => 'page',
		'attachment'    => 'attachment',
		'product'       => 'product',
		'nav_menu_item' => 'nav_menu_item',
	];
}
add_filter( 'ctl_post_types', 'my_ctl_post_types' );
`

= How can I convert many posts/terms using wp-cli? =

Use the following command in the console:

`
wp cyr2lat regenerate [--post_type=<post_type>] [--post_status=<post_status>]
`

Where
  `-post_type` is a list of post types,
  `-post_status` is a list of post statuses.

= How can I regenerate thumbnails safely? =

Regeneration of thumbnails with the command `wp media regenerate` can break links in old posts as file names become transliterated.

To avoid it, deactivate the cyr2lat plugin during regeneration:

`
wp media regenerate --skip-plugins=cyr2lat
`

= Can I contribute? =

Yes, you can!

* Join in on our [GitHub repository](https://github.com/mihdan/cyr2lat)
* Join in on our [Telegram Group](https://t.me/cyr2lat)

= Where do I report security bugs found in this plugin? =

Please report security bugs found in the source code of the undefined plugin through the [Patchstack Vulnerability Disclosure  Program](https://patchstack.com/database/vdp/b6b7bcdc-2668-4aed-9226-8e3b5e809ab0). The Patchstack team will assist you with verification, CVE assignment, and notify the developers of this plugin.

== Changelog ==

= 6.6.0 (30.11.2025) =
* Fixed the deprecated function message in Main.php with WordPress 6.9.
* Tested with PHP 8.4.
* Tested with WordPress 6.9.
* Tested with WooCommerce 10.3.

= 6.5.0 (24.10.2025) =
* Fixed transliteration of tags during editing.

= 6.4.1 (03.05.2025) =
* Fixed the layout of messages on the Tables page.
* Tested with WordPress 6.8.
* Tested with WooCommerce 9.8.

= 6.3.0 (22.12.2024) =
* Added a warning message on the Tables page when the active table does not match the site locale.
* Removed fix for translation after WordPress 6.5+ due to performance issues.

= 6.2.3 (24.11.2024) =
* Fixed deprecation error with PHP 8.4.
* Tested with PHP 8.4.

= 6.2.2 (15.11.2024) =
* Fixed _load_textdomain_just_in_time notice with WordPress 6.7.
* Some translations were empty with WordPress 6.5+.

= 6.2.1 (13.11.2024) =
* Fixed layout of the Converter page.
* Fixed issues reported by Plugin Check Plugin.

= 6.2.0 (13.11.2024) =
* Dropped support for PHP 7.0 and 7.1. The minimum required PHP version is now 7.2.
* The minimum required WordPress version is now 5.3.
* Fixed the notice about the _load_textdomain_just_in_time function being called incorrectly.
* Tested with WordPress 6.7.
* Tested with WooCommerce 9.4.

= 6.1.0 (09.03.2024) =
* Tested with WordPress 6.5.
* Tested with WooCommerce 8.6.
* Fixed error on the System Info tab when post types or post statuses are not set.

= 6.0.8 (14.02.2024) =
* Improved detection of the Gutenberg editor.
* Fixed processing of product attributes.

= 6.0.7 (11.02.2024) =
* Tested with WooCommerce 8.5.
* Added redirect from the cyrillic post title when creating a new post.
* Added description of post types and post statuses on the Converter page.
* Fixed displaying all file descriptions in the Theme Editor in the current locale.
* Fixed PHP warning in the SettingsBase.
* Fixed the output of variable product attributes.

= 6.0.6 (14.01.2024) =
* Tested with WordPress 6.4.
* Tested with WooCommerce 8.4.
* Tested with PHP 8.3.
* Fixed documentation on ctl_allow filter.
* Fixed the improper display of the "rate plugin" message on options.php.

= 6.0.5 (09.10.2023) =
* Fixed displaying file descriptions in the Theme Editor; now in the current locale.

= 6.0.4 (23.09.2023) =
* Fixed disappeared file descriptions on the Theme File Editor page.

= 6.0.3 (29.07.2023) =
* Fixed the fatal error with Jetpack sync.

= 6.0.2 (26.07.2023) =
* Fixed fatal error in admin_footer_text().

= 6.0.1 (26.07.2023) =
* Fixed the fatal error on the System Info page with empty options.

= 6.0.0 (26.07.2023) =
* Dropped support of PHP 5.6. The Minimum required PHP version is 7.0 now.
* Tested with WordPress 6.3.
* Tested with WooCommerce 7.9.
* Added System Info tab.
* Added filter 'ctl_allow'
* Fixed console error when saving table data.
* Fixed the current table setting on the Tables page with WPML.

[See changelog for all versions](https://plugins.svn.wordpress.org/cyr2lat/trunk/changelog.txt).
