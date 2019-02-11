=== Cyr-To-Lat ===
Contributors: SergeyBiryukov, mihdan, karevn, webvitaly, kaggdesign
Tags: cyrillic, georgian, latin, l10n, russian, rustolat, slugs, translations, transliteration
Requires at least: 2.3
Tested up to: 5.1
Stable tag: 3.6.5
Requires PHP: 5.2

Converts Cyrillic characters in post, page and term slugs to Latin characters.

== Description ==

Converts Cyrillic characters in post, page and term slugs to Latin characters. Useful for creating human-readable URLs.

= Features =
* Automatically converts existing post, page and term slugs on activation
* Saves existing post and page permalinks integrity
* Performs transliteration of attachment file names
* Includes Russian, Belarusian, Ukrainian, Bulgarian and Georgian characters
* Transliteration table can be customized without editing the plugin by itself

Based on the original Rus-To-Lat plugin by Anton Skorobogatov.

== Installation ==

1. Upload `cyr2lat` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.

== Frequently Asked Questions ==

= How can I define my own substitutions? =

Add this code to your theme's `functions.php` file:
`
function my_cyr_to_lat_table($ctl_table) {
   $ctl_table['Ъ'] = 'U';
   $ctl_table['ъ'] = 'u';
   return $ctl_table;
}
add_filter('ctl_table', 'my_cyr_to_lat_table');
`

= Can I contribute? =

Yes you can! Join in on our [GitHub repository](https://github.com/mihdan/cyr2lat)

== Changelog ==

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
