<?php
/**
 * Plugin Name: Cyr-To-Lat
 * Plugin URI: http://wordpress.org/extend/plugins/cyr2lat/
 * Description: Converts Cyrillic characters in post and term slugs to Latin characters. Useful for creating human-readable URLs. Based on the original plugin by Anton Skorobogatov.
 * Author: Sol, Sergey Biryukov, Mikhail Kobzarev
 * Author URI: http://ru.wordpress.org/
 * Version: 3.3
 */

/**
 * @param string $title post title.
 *
 * @return string|string[]|null
 */
function ctl_sanitize_title( $title ) {
	global $wpdb;

	$iso9_table = array(
		'А' => 'A',
		'Б' => 'B',
		'В' => 'V',
		'Г' => 'G',
		'Ѓ' => 'G`',
		'Ґ' => 'G`',
		'Д' => 'D',
		'Е' => 'E',
		'Ё' => 'YO',
		'Є' => 'YE',
		'Ж' => 'ZH',
		'З' => 'Z',
		'Ѕ' => 'Z',
		'И' => 'I',
		'Й' => 'J',
		'Ј' => 'J',
		'І' => 'I',
		'Ї' => 'YI',
		'К' => 'K',
		'Ќ' => 'K`',
		'Л' => 'L',
		'Љ' => 'L',
		'М' => 'M',
		'Н' => 'N',
		'Њ' => 'N`',
		'О' => 'O',
		'П' => 'P',
		'Р' => 'R',
		'С' => 'S',
		'Т' => 'T',
		'У' => 'U',
		'Ў' => 'U`',
		'Ф' => 'F',
		'Х' => 'H',
		'Ц' => 'TS',
		'Ч' => 'CH',
		'Џ' => 'DH',
		'Ш' => 'SH',
		'Щ' => 'SHH',
		'Ъ' => '',
		'Ы' => 'Y`',
		'Ь' => '',
		'Э' => 'E`',
		'Ю' => 'YU',
		'Я' => 'YA',
		'а' => 'a',
		'б' => 'b',
		'в' => 'v',
		'г' => 'g',
		'ѓ' => 'g',
		'ґ' => 'g',
		'д' => 'd',
		'е' => 'e',
		'ё' => 'yo',
		'є' => 'ye',
		'ж' => 'zh',
		'з' => 'z',
		'ѕ' => 'z',
		'и' => 'i',
		'й' => 'j',
		'ј' => 'j',
		'і' => 'i',
		'ї' => 'yi',
		'к' => 'k',
		'ќ' => 'k`',
		'л' => 'l',
		'љ' => 'l',
		'м' => 'm',
		'н' => 'n',
		'њ' => 'n`',
		'о' => 'o',
		'п' => 'p',
		'р' => 'r',
		'с' => 's',
		'т' => 't',
		'у' => 'u',
		'ў' => 'u`',
		'ф' => 'f',
		'х' => 'h',
		'ц' => 'ts',
		'ч' => 'ch',
		'џ' => 'dh',
		'ш' => 'sh',
		'щ' => 'shh',
		'ъ' => '',
		'ы' => 'y`',
		'ь' => '',
		'э' => 'e`',
		'ю' => 'yu',
		'я' => 'ya',
	);

	// Georgian table.
	$geo2lat = array(
		'áƒ' => 'a',
		'áƒ‘' => 'b',
		'áƒ’' => 'g',
		'áƒ“' => 'd',
		'áƒ”' => 'e',
		'áƒ•' => 'v',
		'áƒ–' => 'z',
		'áƒ—' => 'th',
		'áƒ˜' => 'i',
		'áƒ™' => 'k',
		'áƒš' => 'l',
		'áƒ›' => 'm',
		'áƒœ' => 'n',
		'áƒ' => 'o',
		'áƒž' => 'p',
		'áƒŸ' => 'zh',
		'áƒ ' => 'r',
		'áƒ¡' => 's',
		'áƒ¢' => 't',
		'áƒ£' => 'u',
		'áƒ¤' => 'ph',
		'áƒ¥' => 'q',
		'áƒ¦' => 'gh',
		'áƒ§' => 'qh',
		'áƒ¨' => 'sh',
		'áƒ©' => 'ch',
		'áƒª' => 'ts',
		'áƒ«' => 'dz',
		'áƒ¬' => 'ts',
		'áƒ­' => 'tch',
		'áƒ®' => 'kh',
		'áƒ¯' => 'j',
		'áƒ°' => 'h',
	);

	$iso9_table = array_merge( $iso9_table, $geo2lat );

	$locale = get_locale();
	switch ( $locale ) {
		case 'bg_BG':
			$iso9_table['Щ'] = 'SHT';
			$iso9_table['щ'] = 'sht';
			$iso9_table['Ъ'] = 'A';
			$iso9_table['ъ'] = 'a';
			break;
		case 'uk':
			$iso9_table['И'] = 'Y';
			$iso9_table['и'] = 'y';
			break;
	}

	$is_term   = false;
	$backtrace = debug_backtrace();
	foreach ( $backtrace as $backtrace_entry ) {
		if ( 'wp_insert_term' == $backtrace_entry['function'] ) {
			$is_term = true;
			break;
		}
	}

	$term = $is_term ? $wpdb->get_var( "SELECT slug FROM {$wpdb->terms} WHERE name = '$title'" ) : '';

	if ( ! empty( $term ) ) {
		$title = $term;
	} else {
		$title = strtr( $title, apply_filters( 'ctl_table', $iso9_table ) );

		if ( function_exists( 'iconv' ) ) {
			$title = iconv( 'UTF-8', 'UTF-8//TRANSLIT//IGNORE', $title );
		}

		$title = preg_replace( "/[^A-Za-z0-9'_\-\.]/", '-', $title );
		$title = preg_replace( '/\-+/', '-', $title );
		$title = preg_replace( '/^-+/', '', $title );
		$title = preg_replace( '/-+$/', '', $title );
	}

	return $title;
}
add_filter( 'sanitize_title', 'ctl_sanitize_title', 9 );
add_filter( 'sanitize_file_name', 'ctl_sanitize_title' );

/**
 * Convert Existing Slugs
 */
function ctl_convert_existing_slugs() {
	global $wpdb;

	$posts = $wpdb->get_results( "SELECT ID, post_name FROM {$wpdb->posts} WHERE post_name REGEXP('[^A-Za-z0-9\-]+') AND post_status IN ('publish', 'future', 'private')" );

	foreach ( (array) $posts as $post ) {
		$sanitized_name = ctl_sanitize_title( urldecode( $post->post_name ) );

		if ( $post->post_name != $sanitized_name ) {
			add_post_meta( $post->ID, '_wp_old_slug', $post->post_name );
			$wpdb->update( $wpdb->posts, array( 'post_name' => $sanitized_name ), array( 'ID' => $post->ID ) );
		}
	}

	$terms = $wpdb->get_results( "SELECT term_id, slug FROM {$wpdb->terms} WHERE slug REGEXP('[^A-Za-z0-9\-]+') " );

	foreach ( (array) $terms as $term ) {
		$sanitized_slug = ctl_sanitize_title( urldecode( $term->slug ) );

		if ( $term->slug != $sanitized_slug ) {
			$wpdb->update( $wpdb->terms, array( 'slug' => $sanitized_slug ), array( 'term_id' => $term->term_id ) );
		}
	}
}

function ctl_schedule_conversion() {
	add_action( 'shutdown', 'ctl_convert_existing_slugs' );
}
register_activation_hook( __FILE__, 'ctl_schedule_conversion' );

// eof;
