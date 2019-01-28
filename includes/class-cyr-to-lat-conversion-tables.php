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
			'Ѓ' => 'G',
			'Ґ' => 'G',
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
			'Ќ' => 'K',
			'Л' => 'L',
			'Љ' => 'L',
			'М' => 'M',
			'Н' => 'N',
			'Њ' => 'N',
			'О' => 'O',
			'П' => 'P',
			'Р' => 'R',
			'С' => 'S',
			'Т' => 'T',
			'У' => 'U',
			'Ў' => 'U',
			'Ф' => 'F',
			'Х' => 'H',
			'Ц' => 'TS',
			'Ч' => 'CH',
			'Џ' => 'DH',
			'Ш' => 'SH',
			'Щ' => 'SHH',
			'Ъ' => '',
			'Ы' => 'Y',
			'Ь' => '',
			'Э' => 'E',
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
			'ќ' => 'k',
			'л' => 'l',
			'љ' => 'l',
			'м' => 'm',
			'н' => 'n',
			'њ' => 'n',
			'о' => 'o',
			'п' => 'p',
			'р' => 'r',
			'с' => 's',
			'т' => 't',
			'у' => 'u',
			'ў' => 'u',
			'ф' => 'f',
			'х' => 'h',
			'ц' => 'ts',
			'ч' => 'ch',
			'џ' => 'dh',
			'ш' => 'sh',
			'щ' => 'shh',
			'ъ' => '',
			'ы' => 'y',
			'ь' => '',
			'э' => 'e',
			'ю' => 'yu',
			'я' => 'ya',
		);
		switch ( $locale ) {
			case 'bg_BG':
				$table['Щ'] = 'SHT';
				$table['щ'] = 'sht';
				$table['Ъ'] = 'A';
				$table['ъ'] = 'a';
				break;
			case 'uk':
				$table['И'] = 'Y';
				$table['и'] = 'y';
				break;
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
			default:
		}

		return $table;
	}
}
