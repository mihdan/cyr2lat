<?php
/**
 * Conversion tables.
 *
 * @package cyr-to-lat
 */

/**
 * Class Cyr_To_Lat_Conversion_Tables
 *
 * @class Cyr_To_Lat_Conversion_Tables
 */
class Cyr_To_Lat_Conversion_Tables {

	/**
	 * Get conversion table by locale.
	 *
	 * @link https://ru.wikipedia.org/wiki/ISO_9
	 *
	 * @param string $locale WordPress locale.
	 *
	 * @return array
	 */
	public static function get( $locale = '' ) {
		$table = array(
			'А' => 'A',
			'Б' => 'B',
			'В' => 'V',
			'Г' => 'G',
			'Д' => 'D',
			'Е' => 'E',
			'Ё' => 'YO',
			'Ж' => 'ZH',
			'З' => 'Z',
			'И' => 'I',
			'Й' => 'J',
			'І' => 'I',
			'К' => 'K',
			'Л' => 'L',
			'М' => 'M',
			'Н' => 'N',
			'О' => 'O',
			'П' => 'P',
			'Р' => 'R',
			'С' => 'S',
			'Т' => 'T',
			'У' => 'U',
			'Ф' => 'F',
			'Х' => 'H',
			'Ц' => 'CZ',
			'Ч' => 'CH',
			'Ш' => 'SH',
			'Щ' => 'SHH',
			'Ъ' => '',
			'Ы' => 'Y',
			'Ь' => '',
			'Э' => 'E',
			'Ю' => 'YU',
			'Я' => 'YA',
			'Ѣ' => 'YE',
			'Ѳ' => 'FH',
			'Ѵ' => 'YH',
			'а' => 'a',
			'б' => 'b',
			'в' => 'v',
			'г' => 'g',
			'д' => 'd',
			'е' => 'e',
			'ё' => 'yo',
			'ж' => 'zh',
			'з' => 'z',
			'и' => 'i',
			'й' => 'j',
			'і' => 'i',
			'к' => 'k',
			'л' => 'l',
			'м' => 'm',
			'н' => 'n',
			'о' => 'o',
			'п' => 'p',
			'р' => 'r',
			'с' => 's',
			'т' => 't',
			'у' => 'u',
			'ф' => 'f',
			'х' => 'h',
			'ц' => 'cz',
			'ч' => 'ch',
			'ш' => 'sh',
			'щ' => 'shh',
			'ъ' => '',
			'ы' => 'y',
			'ь' => '',
			'э' => 'e',
			'ю' => 'yu',
			'я' => 'ya',
			'ѣ' => 'ye',
			'ѳ' => 'fh',
			'ѵ' => 'yh',
		);
		switch ( $locale ) {
			// Belorussian.
			case 'bel':
				unset( $table['И'] );
				unset( $table['и'] );
				$table['Ў'] = 'U';
				$table['ў'] = 'u';
				unset( $table['Щ'] );
				unset( $table['щ'] );
				unset( $table['Ъ'] );
				unset( $table['ъ'] );
				unset( $table['Ѣ'] );
				unset( $table['ѣ'] );
				unset( $table['Ѳ'] );
				unset( $table['ѳ'] );
				unset( $table['Ѵ'] );
				unset( $table['ѵ'] );
				break;
			// Ukrainian.
			case 'uk':
				$table['Ґ'] = 'G';
				$table['ґ'] = 'g';
				unset( $table['Ё'] );
				unset( $table['ё'] );
				$table['Є'] = 'YE';
				$table['є'] = 'ye';
				$table['И'] = 'Y';
				$table['и'] = 'y';
				$table['Ї'] = 'YI';
				$table['ї'] = 'yi';
				unset( $table['Ъ'] );
				unset( $table['ъ'] );
				unset( $table['Ы'] );
				unset( $table['ы'] );
				unset( $table['Э'] );
				unset( $table['э'] );
				unset( $table['Ѣ'] );
				unset( $table['ѣ'] );
				unset( $table['Ѳ'] );
				unset( $table['ѳ'] );
				unset( $table['Ѵ'] );
				unset( $table['ѵ'] );
				break;
			// Bulgarian.
			case 'bg_BG':
				unset( $table['Ё'] );
				unset( $table['ё'] );
				$table['Щ'] = 'STH';
				$table['щ'] = 'sth';
				$table['Ъ'] = 'A';
				$table['ъ'] = 'a';
				unset( $table['Ы'] );
				unset( $table['ы'] );
				unset( $table['Э'] );
				unset( $table['э'] );
				$table['Ѫ'] = 'О';
				$table['ѫ'] = 'о';
				break;
			// Macedonian.
			case 'mk_MK':
				$table['Ѓ'] = 'G';
				$table['ѓ'] = 'g';
				unset( $table['Ё'] );
				unset( $table['ё'] );
				$table['Ѕ'] = 'Z';
				$table['ѕ'] = 'z';
				unset( $table['Й'] );
				unset( $table['й'] );
				$table['Ј'] = 'J';
				$table['ј'] = 'j';
				unset( $table['I'] );
				unset( $table['i'] );
				$table['Ќ'] = 'K';
				$table['ќ'] = 'k';
				$table['Љ'] = 'L';
				$table['љ'] = 'l';
				$table['Њ'] = 'N';
				$table['њ'] = 'n';
				$table['Џ'] = 'DH';
				$table['џ'] = 'dh';
				unset( $table['Щ'] );
				unset( $table['щ'] );
				unset( $table['Ъ'] );
				unset( $table['ъ'] );
				unset( $table['Ы'] );
				unset( $table['ы'] );
				unset( $table['Ь'] );
				unset( $table['ь'] );
				unset( $table['Э'] );
				unset( $table['э'] );
				unset( $table['Ю'] );
				unset( $table['ю'] );
				unset( $table['Я'] );
				unset( $table['я'] );
				unset( $table['Ѣ'] );
				unset( $table['ѣ'] );
				unset( $table['Ѳ'] );
				unset( $table['ѳ'] );
				unset( $table['Ѵ'] );
				unset( $table['ѵ'] );
				break;
			// Georgian.
			case 'ka_GE':
				$table['áƒ'] = 'a';
				$table['áƒ‘'] = 'b';
				$table['áƒ’'] = 'g';
				$table['áƒ“'] = 'd';
				$table['áƒ”'] = 'e';
				$table['áƒ•'] = 'v';
				$table['áƒ–'] = 'z';
				$table['áƒ—'] = 'th';
				$table['áƒ˜'] = 'i';
				$table['áƒ™'] = 'k';
				$table['áƒš'] = 'l';
				$table['áƒ›'] = 'm';
				$table['áƒœ'] = 'n';
				$table['áƒ'] = 'o';
				$table['áƒž'] = 'p';
				$table['áƒŸ'] = 'zh';
				$table['áƒ '] = 'r';
				$table['áƒ¡'] = 's';
				$table['áƒ¢'] = 't';
				$table['áƒ£'] = 'u';
				$table['áƒ¤'] = 'ph';
				$table['áƒ¥'] = 'q';
				$table['áƒ¦'] = 'gh';
				$table['áƒ§'] = 'qh';
				$table['áƒ¨'] = 'sh';
				$table['áƒ©'] = 'ch';
				$table['áƒª'] = 'ts';
				$table['áƒ«'] = 'dz';
				$table['áƒ¬'] = 'ts';
				$table['áƒ­'] = 'tch';
				$table['áƒ®'] = 'kh';
				$table['áƒ¯'] = 'j';
				$table['áƒ°'] = 'h';
				break;
			// Kazakh.
			case 'kk':
				$table['Ә'] = 'Ae';
				$table['ә'] = 'ae';
				$table['Ғ'] = 'Gh';
				$table['ғ'] = 'gh';
				unset( $table['Ё'] );
				unset( $table['ё'] );
				$table['Қ'] = 'Q';
				$table['қ'] = 'q';
				$table['Ң'] = 'Ng';
				$table['ң'] = 'ng';
				$table['Ө'] = 'Oe';
				$table['ө'] = 'oe';
				$table['У'] = 'W';
				$table['у'] = 'w';
				$table['Ұ'] = 'U';
				$table['ұ'] = 'u';
				$table['Ү'] = 'Ue';
				$table['ү'] = 'ue';
				$table['Һ'] = 'H';
				$table['һ'] = 'h';
				$table['Ц'] = 'C';
				$table['ц'] = 'c';
				unset( $table['Щ'] );
				unset( $table['щ'] );
				unset( $table['Ъ'] );
				unset( $table['ъ'] );
				unset( $table['Ь'] );
				unset( $table['ь'] );
				unset( $table['Э'] );
				unset( $table['э'] );
				unset( $table['Ю'] );
				unset( $table['ю'] );
				unset( $table['Я'] );
				unset( $table['я'] );

				// Kazakh 2018 latin.
				$table['Á'] = 'A';
				$table['á'] = 'a';
				$table['Ǵ'] = 'G';
				$table['ǵ'] = 'g';
				$table['I'] = 'I';
				$table['ı'] = 'i';
				$table['Ń'] = 'N';
				$table['ń'] = 'n';
				$table['Ó'] = 'O';
				$table['ó'] = 'o';
				$table['Ú'] = 'O';
				$table['ú'] = 'o';
				$table['Ý'] = 'O';
				$table['ý'] = 'o';
				break;
			case 'he_IL':
				$table = array(
					'א' => '',
					'ב' => 'b',
					'ג' => 'g',
					'ד' => 'd',
					'ה' => 'h',
					'ו' => 'w',
					'ז' => 'z',
					'ח' => 'x',
					'ט' => 't',
					'י' => 'y',
					'ך' => '',
					'כ' => 'kh',
					'ל' => 'l',
					'ם' => '',
					'מ' => 'm',
					'ן' => '',
					'נ' => 'n',
					'ס' => 's',
					'ע' => '',
					'ף' => '',
					'פ' => 'ph',
					'ץ' => '',
					'צ' => 's',
					'ק' => 'k',
					'ר' => 'r',
					'ש' => 'sh',
					'ת' => 'th',
				);
				for ( $code = 0x0590; $code <= 0x05CF; $code ++ ) {
					$table[ self::mb_chr( $code ) ] = '';
				}
				for ( $code = 0x05F0; $code <= 0x05F5; $code ++ ) {
					$table[ self::mb_chr( $code ) ] = '';
				}
				for ( $code = 0xFB1D; $code <= 0xFB4F; $code ++ ) {
					$table[ self::mb_chr( $code ) ] = '';
				}
				break;
			default:
		}

		return $table;
	}

	/**
	 * Simplified polyfill of mb_chr() function, to be used without mbstring extension.
	 *
	 * @link https://github.com/symfony/polyfill-mbstring/blob/master/Mbstring.php
	 *
	 * @param int $code Character code.
	 *
	 * @return string
	 */
	public static function mb_chr( $code ) {
		$code = $code % 0x200000;
		if ( 0x80 > $code ) {
			$s = \chr( $code );
		} elseif ( 0x800 > $code ) {
			$s = \chr( 0xC0 | $code >> 6 ) . \chr( 0x80 | $code & 0x3F );
		} elseif ( 0x10000 > $code ) {
			$s = \chr( 0xE0 | $code >> 12 ) . \chr( 0x80 | $code >> 6 & 0x3F ) . \chr( 0x80 | $code & 0x3F );
		} else {
			$s = \chr( 0xF0 | $code >> 18 ) . \chr( 0x80 | $code >> 12 & 0x3F ) . \chr( 0x80 | $code >> 6 & 0x3F ) . \chr( 0x80 | $code & 0x3F );
		}

		return $s;
	}
}
