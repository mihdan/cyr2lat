=== Cyr-To-Lat ===
Contributors: SergeyBiryukov, mihdan, karevn, webvitaly, kaggdesign
Tags: cyrillic, belorussian, ukrainian, bulgarian, macedonian, georgian, kazakh, latin, l10n, russian, cyr-to-lat, cyr2lat, rustolat, slugs, translations, transliteration
Requires at least: 5.1
Tested up to: 6.2
Stable tag: 5.5.2
Requires PHP: 5.6.20
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Convert Non-Latin characters in post, page and term slugs to Latin characters.

== Description ==

Converts Cyrillic characters in post, page and term slugs to Latin characters. Useful for creating human-readable URLs.

= Features =
* The only plugin with fully editable transliteration table. Allows to add/remove and edit pairs like 'Я' => 'Ya', or even 'Пиво' => 'Beer'
* Converts any number of existing post, page and term slugs in background processes
* Saves existing post and page permalinks integrity
* Performs transliteration of attachment file names
* Includes Russian, Belorussian, Ukrainian, Bulgarian, Macedonian, Serbian, Greek, Armenian, Georgian, Kazakh, Hebrew, and Chinese characters
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

1. Upload `cyr2lat` folder to the `/wp-content/plugins/` directory.
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

= How can I define own transliteration of titles? =

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

= How can I define own transliteration of filenames? =

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
  `-post_type` is list of post types,
  `-post_status` is list of post statuses.

= How can I regenerate thumbnails safely? =

Regeneration of thumbnails with the command `wp media regenerate` can break links in old posts as file names become transliterated.

To avoid it, deactivate cyr2lat plugin during regeneration:

`
wp media regenerate --skip-plugins=cyr2lat
`

= Can I contribute? =

Yes you can!

* Join in on our [GitHub repository](https://github.com/mihdan/cyr2lat)
* Join in on our [Telegram Group](https://t.me/cyr2lat)

== Changelog ==

= 5.5.2 (31.03.2023) =
* Fixed transliteration of tags with Polylang and WPML.

= 5.5.1 (21.03.2023) =
* Fixed transliteration of attributes on WC frontend.

= 5.5.0 (18.03.2023) =
* Tested with WordPress 6.2.
* Tested with WooCommerce 7.5.
* Improved performance of Tables settings page.
* Fixed showing posts by tags on the frontend.
* Fixed showing non-transliterated cyrillic tags on the backend.

= 5.4.0 (15.12.2022) =
* Tested with WordPress 6.1 and WooCommerce 7.2.
* Added compatibility with WC High-Performance order storage (COT) feature.

= 5.3.0 (23.05.2022) =
* Tested with WordPress 6.0 and WooCommerce 6.5.

= 5.2.7 (14.02.2022) =
* Tested with WooCommerce 6.2.
* Added PHP 8.1 support.

= 5.2.6 (25.12.2021) =
* Revert fix made in 5.2.5 for 404 with WPML, as it created several issues on the frontend.
* Fix again 404 on archives created with WPML before activation of cyr2lat.

= 5.2.5 (19.12.2021) =
* Tested up to WordPress 5.9 and WooCommerce 6.0.
* Fix issue with Polylang - do not modify admin language when editing a post.
* Fix issue with JetPack - fatal error on synchronisation.
* Fix 404 on archives created with WPML before activation of cyr2lat.

= 5.2.4 (07.09.2021) =
* Fix issue with not showing WooCommerce variable product attributes.
* Fix issue with Elementor and WPML, endless loop.

= 5.2.3 (07.09.2021) =
* Fix issue with WP Foro plugin - transliterate topic slug when created on frontend.
* Fix bug with Polylang on REST request.

= 5.2.2 (06.09.2021) =
* Fix issue caused by the bug in Jetpack sync.
* Optimize code related to WPML locale filtering.
* Fix endless loading of a taxonomy page with WPML.
* Fix 'nothing found' on a taxonomy page with WPML.

= 5.2.1 (29.07.2021) =
* Determine WPML language only once to improve performance.
* Avoid notice on bad SQL request when taxonomies are empty.

= 5.2.0 (27.07.2021) =
* Add support for categories and tags in other languages with wpml.

= 5.1.0 (19.07.2021) =
* Fix issue-95 - 404 on localized terms created before plugin install.
* Add cache flushing after batch conversion.
* Tested with WordPress 5.8

= 5.0.4 (17.04.2021) =
* Fix bug in converter without saved options

= 5.0.3 (03.04.2021) =
* Add filter 'ctl_locale'
* Fix translation of tabs on settings pages
* Fix registered post types in conversion settings

= 5.0.2 (27.03.2021) =
* Fix bug creating tag with the same slug as category

= 5.0.1 (22.03.2021) =
* Fix fatal error during plugin load on some servers

= 5.0.0 (18.03.2021) =
* Introduce tabs on options page
* Add options to select post types and statuses for background conversion
* Make colors compatible to WP official palette
* Fix bug with Polylang when locale is not equal to language slug

= 4.6.4 (03.03.2021) =
* Tested up to WordPress 5.7

= 4.6.3 (21.02.2021) =
* Fix bug with attachment post type filtered by 'ctl_post_types'
* Fix bug with background conversion of product attribute terms

= 4.6.2 (11.02.2021) =
* Fix bug with non-existing function PLL().

= 4.6.1 (10.02.2021) =
* Fix bug with Polylang

= 4.6.0 (10.02.2021) =
* Add compatibility with Polylang
* Add confirmation popup before mass conversion of slugs
* Improve selection of posts and tags for conversion to avoid selection of excessive items for mass conversion
* Fix bug with redirection from the old slug to a new one after background slug conversion
* Fix js to run in old browsers like IE

= 4.5.2 (08.12.2020) =
* Fix bug with the deployment to wp.org

= 4.5.1 (07.12.2020) =
* Tested up to WordPress 5.6
* Tested on PHP 5.6 - 8.0

= 4.5.0 (18.05.2020) =
* Added Greek and Armenian languages
* Added background conversion of attachments and thumbnails
* Fixed background conversion of existing slugs

= 4.4.0 (18.04.2020) =
* Full flexibility to edit transliteration table: now it is possible to add/remove transliteration pairs on the settings page
* Ability to edit not only values in the transliteration table, but also keys
* Saving active table via ajax
* Watching changes in active table
* Auto-saving of changed table
* Info about the current locale on settings page
* Making table with current locale active at setting page load
* Chinese language added
* Fixed: slug not updated at woocommerce product duplication

= 4.3.5 (28.03.2020) =
* Tested up to WordPress 5.4
* Fixed bug with disappearing of WooCommerce attributes

= 4.3.4 (22.02.2020) =
* Fixed non-conversion of slugs with WPML
* Restricted conversion of post to public and nav_menu_item
* Introduced ctl_post_types filter

= 4.3.3 (20.02.2020) =
* Reworked main plugin filter
* Improved performance by minimizing number of calls
* Updated Georgian table
* Fixed slug duplication in taxonomies
* Fixed warnings with WooCommerce when mbstring is not loaded
* Fixed transliteration of draft post slug
* Tables sorted by local alphabets

= 4.3.2 (29.12.2019) =
* Fixed problems with setting of max_input_vars on some hosting

= 4.3.1 (27.12.2019) =
* Added requirement to have max_input_vars >= 5000
* Added automatic plugin deactivation if requirements are not met
* Added attempt to auto-fix max_input_variable value

= 4.3 (14.12.2019) =
* Added Chinese table
* Tested up to WordPress 5.3
* Tested up to PHP 7.4
* External library wp-background-processing scoped into own namespace to prevent errors in some cases

= 4.2.3 (29.08.2019) =
* Scoped Symfony Mbstring polyfill to avoid problems with composer autoloader on some sites.

= 4.2.2 (28.08.2019) =
* Added ACF (Advanced Custom Fields) plugin support
* Added Serbian table
* Added new filter `ctl_pre_sanitize_filename`
* Fixed improper encoding of `Ё`, `ё`, `Й`, `й` characters in file names on some Mac computers (old known problem on Mac's)

= 4.2.1 (23.06.2019) =
* Fixed problem with sessions
* Fixed message sequence for conversion of existing slugs.
* Added php version check to avoid fatal error on activation on old sites.
* Added vertical tabs in plugin settings.

= 4.2 (28.05.2019) =
* Bumped up required php version - to 5.6
* Added phpunit tests for all php versions from 5.6 to 7.3
* Fixed php warning during conversion of existing slugs
* Fixed locale selection during conversion of existing post slugs when WPML is activated
* Fixed bug with infinite redirection of some slugs after conversion of existing slugs

= 4.1.2 (22.05.2019) =
* Fixed bug with fatal error in Cyr_To_Lat_Converter with php 5.2

= 4.1.1 (22.05.2019) =
* Fixed bug with fatal error in Cyr_To_Lat_Converter with php 5.6

= 4.1 (21.05.2019) =
* Added he_IL Table
* Added plugin translation to Ukrainian
* Added plugin translation to Swedish
* Added phpunit tests. All plugin classes are 100% covered
* Added js tests. All plugin js code is 100% covered
* Fixed bug with Jetpack sync
* Fixed empty slug bug while using characters outside of locale

= 4.0 (24.04.2019) =
* Added button to convert existing slugs, instead of checkbox
* Added admin notices during conversion of existing slugs
* Added post_type and post_status parameters to wp-cli command
* Fixed text domain
* Simplified package.json to make final js even smaller
* Added phpunit tests to the plugin main class
* Added travis.yml for continuous integration on GitHub, and improvement of code reliability

= 3.7 (12.04.2019) =
* Added Belorussian, Macedonian, Kazakh tables
* Fixed bug with MariaDB during old slug conversion
* Fixed not saving of user modifications in default iso9 table

= 3.6.5 (11.02.2019) =
* Added queues for background slug conversion process

= 3.6.4 (06.02.2019) =
* Fixed bug with `_wp_old_slug` redirect.
* Fixed bug with `urldecode` in posts.

= 3.6.3 (04.02.2019) =
* Fixed bug with network activation on multisite

= 3.6.2 (01.02.2019) =
* Moved the menu in the settings section
* Text domain fixup.

= 3.6.1 (31.01.2019) =
* Text domain corrected.

= 3.6 (31.01.2019) =
* Plugin settings page added.
* Settings page allows user to edit conversion tables online.
* Code converted to OOP.
* Code refactored to conform WordPress Coding Standards.
* JS developed according to ECMA-6 script standards.
* Settings page also works if JS is switched off in the browser.
* Composer and yarn added.
* Assets (banner, icon, admin icon) are added.

= 3.4 (21.01.2019) =
* Tested up to WP 5.1
* Code formatting to follow WPCS.
* Strict comparisons.
* Braces {} removed from MySQL statements to allow checking of table names in PhpStorm.
* Updated .gitignore and README.md
* Added new filter `ctl_pre_sanitize_title`

= 3.3 (18.01.2019) =
* wpcs 1.0
* Fixed many bugs
* Added Gutenberg support

= 3.2 =
* Added transliteration when publishing via XML-RPC
* Fixed Invalid Taxonomy error when viewing the most used tags

= 3.1 =
* Fixed transliteration when saving a draft

= 3.0 =
* Added automatic conversion of existing post, page and term slugs
* Added saving of existing post and page permalinks integrity
* Added transliteration of attachment file names
* Adjusted transliteration table in accordance with ISO 9 standard
* Included Russian, Ukrainian, Bulgarian and Georgian characters
* Added filter for the transliteration table

= 2.1 =
* Optimized filter call

= 2.0 =
* Added check for existing terms

= 1.0.1 =
* Updated description

= 1.0 =
* Initial release
