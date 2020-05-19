=== Cyr-To-Lat ===
Contributors: SergeyBiryukov, mihdan, karevn, webvitaly, kaggdesign
Tags: cyrillic, belorussian, ukrainian, bulgarian, macedonian, georgian, kazakh, latin, l10n, russian, cyr-to-lat, cyr2lat, rustolat, slugs, translations, transliteration
Requires at least: 5.1
Tested up to: 5.4
Stable tag: 4.5.0
Requires PHP: 5.6.20

Converts Cyrillic characters in post, page and term slugs to Latin characters.

== Description ==

Converts Cyrillic characters in post, page and term slugs to Latin characters. Useful for creating human-readable URLs.

= Features =
* The only plugin with fully editable transliteration table. Allows add/remove and edit pairs like 'Я' => 'Ya', or even 'Пиво' => 'Beer'
* Converts any number of existing post, page and term slugs in background processes
* Saves existing post and page permalinks integrity
* Performs transliteration of attachment file names
* Includes Russian, Belorussian, Ukrainian, Bulgarian, Macedonian, Serbian, Greek, Armenian, Georgian, Kazakh, Hebrew, and Chinese characters
* [Has many advantages over similar plugins](https://kagg.eu/en/the-benefits-of-cyr-to-lat/)
* [Officially compatible with WPML](https://wpml.org/plugin/cyr-to-lat/)

![WPML Certificate](https://ps.w.org/cyr2lat/assets/Cyr-To-Lat---WPML-Compatibility-Certificate-240x250.png)

Based on the original Rus-To-Lat plugin by Anton Skorobogatov.

[](http://coderisk.com/wp/plugin/cyr2lat/RIPS-nt7iXCmzoc)

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

= How can I redefine non-standard locale ? =

For instance, if your non-standard locale is uk_UA, you can redefine it to `uk` by adding the following code to your theme's `function.php` file:

`
/**
 * Use conversion table for non-standard locale.
 *
 * @param array $table Conversion table.
 *
 * @return array
 */
function my_ctl_table( $table ) {
	if ( 'uk_UA' === get_locale() ) {
		$settings = new Cyr_To_Lat_Settings();
		$table    = $settings->get_option( 'uk' );
	}

	return $table;
}
add_filter( 'ctl_table', 'my_ctl_table' );
`

= How can I convert a large number of posts/terms using wp-cli? =

Use the following command in console:

`
wp cyr2lat regenerate [--post_type=<post_type>] [--post_status=<post_status>]
`

Where
  `-post_type` is list of post types,
  `-post_status` is list of post statuses.

= Can I contribute? =

Yes you can!

* Join in on our [GitHub repository](https://github.com/mihdan/cyr2lat)
* Join in on our [Telegram Channel](https://t.me/cyr2lat)

== Changelog ==

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
* Fixed problems with setting of max_input_vars on some hostings

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
