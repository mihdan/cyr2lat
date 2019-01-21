=== Cyr-To-Lat ===
Contributors: SergeyBiryukov, mihdan, karevn, webvitaly, kagg-design
Tags: cyrillic, georgian, latin, l10n, russian, rustolat, slugs, translations, transliteration
Requires at least: 2.3
Tested up to: 5.1
Stable tag: 3.4

Converts Cyrillic characters in post, page and term slugs to Latin characters.

== Description ==

Converts Cyrillic characters in post, page and term slugs to Latin characters. Useful for creating human-readable URLs.

= Features =
* Automatically converts existing post, page and term slugs on activation
* Saves existing post and page permalinks integrity
* Performs transliteration of attachment file names
* Includes Russian, Belarusian, Ukrainian, Bulgarian and Macedonian characters
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

== Changelog ==

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
* Included Russian, Belarusian, Ukrainian, Bulgarian and Macedonian characters
* Added filter for the transliteration table

= 2.1 =
* Optimized filter call

= 2.0 =
* Added check for existing terms

= 1.0.1 =
* Updated description

= 1.0 =
* Initial release
