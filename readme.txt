=== Cyr-To-Lat ===
Contributors: SergeyBiryukov, mihdan, karevn, webvitaly, kaggdesign
Tags: cyrillic, belorussian, ukrainian, bulgarian, macedonian, georgian, kazakh, latin, l10n, russian, cyr-to-lat, cyr2lat, rustolat, slugs, translations, transliteration
Requires at least: 2.3
Tested up to: 5.2
Stable tag: 4.2.1
Requires PHP: 5.6

Converts Cyrillic characters in post, page and term slugs to Latin characters.

== Description ==

Converts Cyrillic characters in post, page and term slugs to Latin characters. Useful for creating human-readable URLs.

= Features =
* Converts any number of existing post, page and term slugs in background processes
* Saves existing post and page permalinks integrity
* Performs transliteration of attachment file names
* Includes Russian, Belorussian, Ukrainian, Bulgarian, Macedonian, Georgian, and Kazakh characters
* Transliteration table can be customized without editing the plugin by itself

Based on the original Rus-To-Lat plugin by Anton Skorobogatov.

[](http://coderisk.com/wp/plugin/cyr2lat/RIPS-nt7iXCmzoc)

== Installation ==

1. Upload `cyr2lat` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.

== Frequently Asked Questions ==

= How can I define my own substitutions? =

Add this code to your theme's `functions.php` file:
`
function my_cyr_to_lat_table( $ctl_table ) {
   $ctl_table['Ъ'] = 'U';
   $ctl_table['ъ'] = 'u';

   return $ctl_table;
}
add_filter( 'ctl_table', 'my_cyr_to_lat_table' );
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
* Fixed locale selection during conversion of existing term slugs when WPML is activated
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
