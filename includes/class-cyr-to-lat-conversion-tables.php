<?php
/**
 * Conversion tables.
 *
 * @package cyr-to-lat
 */

use Cyr_To_Lat\Symfony\Polyfill\Mbstring\Mbstring;

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
			// Serbian.
			case 'sr_RS':
				$table['Ђ'] = 'Dj';
				$table['ђ'] = 'dj';
				unset( $table['Ё'] );
				unset( $table['ё'] );
				$table['Ж'] = 'Z';
				$table['ж'] = 'z';
				unset( $table['Й'] );
				unset( $table['й'] );
				unset( $table['І'] );
				unset( $table['і'] );
				$table['J'] = 'J';
				$table['j'] = 'j';
				$table['Љ'] = 'Lj';
				$table['љ'] = 'lj';
				$table['Њ'] = 'Nj';
				$table['њ'] = 'nj';
				$table['Ћ'] = 'C';
				$table['ћ'] = 'c';
				$table['Ц'] = 'C';
				$table['ц'] = 'c';
				$table['Ч'] = 'C';
				$table['ч'] = 'c';
				$table['Џ'] = 'Dz';
				$table['џ'] = 'dz';
				$table['Ш'] = 'S';
				$table['ш'] = 's';
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
			// Hebrew.
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
					$table[ Mbstring::mb_chr( $code ) ] = '';
				}
				for ( $code = 0x05F0; $code <= 0x05F5; $code ++ ) {
					$table[ Mbstring::mb_chr( $code ) ] = '';
				}
				for ( $code = 0xFB1D; $code <= 0xFB4F; $code ++ ) {
					$table[ Mbstring::mb_chr( $code ) ] = '';
				}
				break;
			default:
		}

		return $table;
	}

	/**
	 * Get fix table for MacOS.
	 * On MacOS, files containing characters in the table, are sometimes encoded improperly.
	 *
	 * @return array
	 */
	public static function get_fix_table_for_mac() {
		/**
		 * Keys in the table are standard ISO9 characters.
		 *
		 * Example of wrong encoding on Mac:
		 * берЁзовыЙ-белозёрский - original input,
		 * берЁзовыЙ-белозёрский.png - actual filename created on Mac (ЁёЙй are already wrongly encoded),
		 * ber%d0%95%cc%88zovy%d0%98%cc%86-beloz%d0%B5%cc%88rski%d0%B8%cc%86.png - urlencode() of the above,
		 * berËzovyĬ-belozërskiĭ.png - actual filename passed via standard ISO9 transliteration table,
		 * berE%CC%88zovyI%CC%86-beloze%CC%88rskii%CC%86.png - urlencode() of the above.
		 *
		 * To avoid misunderstanding, we use urldecode() here.
		 */
		return [
			'Ё' => urldecode( '%d0%95%cc%88' ),
			'ё' => urldecode( '%d0%B5%cc%88' ),
			'Й' => urldecode( '%d0%98%cc%86' ),
			'й' => urldecode( '%d0%B8%cc%86' ),
		];
	}
}
