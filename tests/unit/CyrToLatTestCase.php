<?php
/**
 * CyrToLatTestCase class file.
 *
 * @package cyr-to-lat
 */

// phpcs:ignore Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpInternalEntityUsedInspection */

// phpcs:disable PHPCompatibility.FunctionDeclarations.NewReturnTypeDeclarations.voidFound

namespace CyrToLat\Tests\Unit;

use CyrToLat\Settings\Abstracts\SettingsBase;
use CyrToLat\Symfony\Polyfill\Mbstring\Mbstring;
use Mockery;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use tad\FunctionMocker\FunctionMocker;
use WP_Mock;

/**
 * Class CyrToLatTestCase
 */
abstract class CyrToLatTestCase extends TestCase {

	/**
	 * Cyr-To-Lat version.
	 *
	 * @var string
	 */
	protected $cyr_to_lat_version;

	/**
	 * Cyr-To-Lat path.
	 *
	 * @var string
	 */
	protected $cyr_to_lat_path;

	/**
	 * Cyr-To-Lat url.
	 *
	 * @var string
	 */
	protected $cyr_to_lat_url;

	/**
	 * Cyr-To-Lat main file.
	 *
	 * @var string
	 */
	protected $cyr_to_lat_file;

	/**
	 * Cyr-To-Lat prefix.
	 *
	 * @var string
	 */
	protected $cyr_to_lat_prefix;

	/**
	 * Cyr-To-Lat post conversion action.
	 *
	 * @var string
	 */
	protected $cyr_to_lat_post_conversion_action;

	/**
	 * Cyr-To-Lat term conversion action.
	 *
	 * @var string
	 */
	protected $cyr_to_lat_term_conversion_action;

	/**
	 * Cyr-To-Lat required version.
	 *
	 * @var string
	 */
	protected $cyr_to_lat_minimum_php_required_version;

	/**
	 * Cyr-To-Lat required max input vars.
	 *
	 * @var int
	 */
	protected $cyr_to_lat_required_max_input_vars;

	/**
	 * Setup test
	 */
	public function setUp(): void {
		FunctionMocker::setUp();
		parent::setUp();
		WP_Mock::setUp();

		$this->cyr_to_lat_version = CYR_TO_LAT_TEST_VERSION;

		$this->cyr_to_lat_path = CYR_TO_LAT_TEST_PATH;

		$this->cyr_to_lat_url = CYR_TO_LAT_TEST_URL;

		$this->cyr_to_lat_file = CYR_TO_LAT_TEST_FILE;

		$this->cyr_to_lat_prefix = CYR_TO_LAT_TEST_PREFIX;

		$this->cyr_to_lat_post_conversion_action = CYR_TO_LAT_TEST_POST_CONVERSION_ACTION;

		$this->cyr_to_lat_term_conversion_action = CYR_TO_LAT_TEST_TERM_CONVERSION_ACTION;

		$this->cyr_to_lat_minimum_php_required_version = CYR_TO_LAT_TEST_MINIMUM_PHP_REQUIRED_VERSION;

		$this->cyr_to_lat_required_max_input_vars = CYR_TO_LAT_TEST_REQUIRED_MAX_INPUT_VARS;

		FunctionMocker::replace(
			'constant',
			function ( $name ) {
				if ( strtoupper( $name ) !== $name ) {
					return null;
				}

				$lc_name = strtolower( $name );
				if ( property_exists( $this, $lc_name ) ) {
					return $this->{$lc_name};
				}

				return null;
			}
		);
	}

	/**
	 * End test
	 */
	public function tearDown(): void {
		WP_Mock::tearDown();
		Mockery::close();
		parent::tearDown();
		FunctionMocker::tearDown();
	}

	/**
	 * Get an object protected property.
	 *
	 * @param object $obj           Object.
	 * @param string $property_name Property name.
	 *
	 * @return mixed
	 *
	 * @throws ReflectionException Reflection exception.
	 */
	protected function get_protected_property( object $obj, string $property_name ) {
		$reflection_class = new ReflectionClass( $obj );

		$property = $reflection_class->getProperty( $property_name );
		$property->setAccessible( true );
		$value = $property->getValue( $obj );
		$property->setAccessible( false );

		return $value;
	}

	/**
	 * Set an object protected property.
	 *
	 * @param object $obj           Object.
	 * @param string $property_name Property name.
	 * @param mixed  $value         Property vale.
	 *
	 * @throws ReflectionException Reflection exception.
	 */
	protected function set_protected_property( object $obj, string $property_name, $value ): void {
		$reflection_class = new ReflectionClass( $obj );

		$property = $reflection_class->getProperty( $property_name );
		$property->setAccessible( true );
		$property->setValue( $obj, $value );
		$property->setAccessible( false );
	}

	/**
	 * Set an object protected method accessibility.
	 *
	 * @param object $obj         Object.
	 * @param string $method_name Property name.
	 * @param bool   $accessible  Property vale.
	 *
	 * @return ReflectionMethod
	 *
	 * @throws ReflectionException Reflection exception.
	 */
	protected function set_method_accessibility( object $obj, string $method_name, bool $accessible = true ): ReflectionMethod {
		$reflection_class = new ReflectionClass( $obj );

		$method = $reflection_class->getMethod( $method_name );
		$method->setAccessible( $accessible );

		return $method;
	}

	/**
	 * Plucks a certain field out of each object or array in an array.
	 * Taken from WP Core.
	 *
	 * @param mixed      $input_list List of objects or arrays.
	 * @param int|string $field      Field from the object to place instead of the entire object.
	 * @param int|string $index_key  Optional. Field from the object to use as keys for the new array.
	 *                               Default null.
	 *
	 * @return array Array of found values. If `$index_key` is set, an array of found values with keys
	 *               corresponding to `$index_key`. If `$index_key` is null, array keys from the original
	 *               `$input_list` will be preserved in the results.
	 */
	protected function wp_list_pluck( $input_list, $field, $index_key = null ): array {
		if ( ! is_array( $input_list ) ) {
			return [];
		}

		return $this->pluck( $input_list, $field, $index_key );
	}

	/**
	 * Plucks a certain field out of each element in the input array.
	 * Taken from WP Core.
	 *
	 * @param array      $input_list List of objects or arrays.
	 * @param int|string $field      Field to fetch from the object or array.
	 * @param int|string $index_key  Optional. Field from the element to use as keys for the new array.
	 *                               Default null.
	 *
	 * @return array Array of found values. If `$index_key` is set, an array of found values with keys
	 *               corresponding to `$index_key`. If `$index_key` is null, array keys from the original
	 *               `$list` will be preserved in the results.
	 */
	private function pluck( array $input_list, $field, $index_key = null ): array {
		$output   = $input_list;
		$new_list = [];

		if ( ! $index_key ) {
			/*
			 * This is simple. Could at some point wrap array_column()
			 * if we knew we had an array of arrays.
			 */
			foreach ( $output as $key => $value ) {
				if ( is_object( $value ) ) {
					$new_list[ $key ] = $value->$field;
				} elseif ( is_array( $value ) ) {
					$new_list[ $key ] = $value[ $field ];
				} else {
					// Error.
					return [];
				}
			}

			return $new_list;
		}

		/*
		 * When index_key is not set for a particular item, push the value
		 * to the end of the stack. This is how array_column() behaves.
		 */
		foreach ( $output as $value ) {
			if ( is_object( $value ) ) {
				if ( isset( $value->$index_key ) ) {
					$new_list[ $value->$index_key ] = $value->$field;
				} else {
					$new_list[] = $value->$field;
				}
			} elseif ( is_array( $value ) ) {
				if ( isset( $value[ $index_key ] ) ) {
					$new_list[ $value[ $index_key ] ] = $value[ $field ];
				} else {
					$new_list[] = $value[ $field ];
				}
			} else {
				// Error.
				return [];
			}
		}

		return $new_list;
	}

	/**
	 * Get conversion table by locale.
	 *
	 * @link https://ru.wikipedia.org/wiki/ISO_9
	 *
	 * @param string $locale WordPress locale.
	 *
	 * @return array
	 */
	protected function get_conversion_table( string $locale = '' ): array {
		$table = [
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
		];
		switch ( $locale ) {
			// Belorussian.
			case 'bel':
				unset( $table['И'], $table['и'] );
				$table['Ў'] = 'U';
				$table['ў'] = 'u';
				unset(
					$table['Щ'],
					$table['щ'],
					$table['Ъ'],
					$table['ъ'],
					$table['Ѣ'],
					$table['ѣ'],
					$table['Ѳ'],
					$table['ѳ'],
					$table['Ѵ'],
					$table['ѵ']
				);
				break;
			// Ukrainian.
			case 'uk':
				$table['Ґ'] = 'G';
				$table['ґ'] = 'g';
				unset( $table['Ё'], $table['ё'] );
				$table['Є'] = 'YE';
				$table['є'] = 'ye';
				$table['И'] = 'Y';
				$table['и'] = 'y';
				$table['Ї'] = 'YI';
				$table['ї'] = 'yi';
				unset(
					$table['Ъ'],
					$table['ъ'],
					$table['Ы'],
					$table['ы'],
					$table['Э'],
					$table['э'],
					$table['Ѣ'],
					$table['ѣ'],
					$table['Ѳ'],
					$table['ѳ'],
					$table['Ѵ'],
					$table['ѵ']
				);
				break;
			// Bulgarian.
			case 'bg_BG':
				unset( $table['Ё'], $table['ё'] );
				$table['Щ'] = 'STH';
				$table['щ'] = 'sth';
				$table['Ъ'] = 'A';
				$table['ъ'] = 'a';
				unset( $table['Ы'], $table['ы'], $table['Э'], $table['э'] );
				$table['Ѫ'] = 'О';
				$table['ѫ'] = 'о';
				break;
			// Macedonian.
			case 'mk_MK':
				$table['Ѓ'] = 'G';
				$table['ѓ'] = 'g';
				unset( $table['Ё'], $table['ё'] );
				$table['Ѕ'] = 'Z';
				$table['ѕ'] = 'z';
				unset( $table['Й'], $table['й'] );
				$table['Ј'] = 'J';
				$table['ј'] = 'j';
				unset( $table['I'], $table['i'] );
				$table['Ќ'] = 'K';
				$table['ќ'] = 'k';
				$table['Љ'] = 'L';
				$table['љ'] = 'l';
				$table['Њ'] = 'N';
				$table['њ'] = 'n';
				$table['Џ'] = 'DH';
				$table['џ'] = 'dh';
				unset(
					$table['Щ'],
					$table['щ'],
					$table['Ъ'],
					$table['ъ'],
					$table['Ы'],
					$table['ы'],
					$table['Ь'],
					$table['ь'],
					$table['Э'],
					$table['э'],
					$table['Ю'],
					$table['ю'],
					$table['Я'],
					$table['я'],
					$table['Ѣ'],
					$table['ѣ'],
					$table['Ѳ'],
					$table['ѳ'],
					$table['Ѵ'],
					$table['ѵ']
				);
				break;
			// Serbian.
			case 'sr_RS':
				$table['Ђ'] = 'Dj';
				$table['ђ'] = 'dj';
				unset( $table['Ё'], $table['ё'] );
				$table['Ж'] = 'Z';
				$table['ж'] = 'z';
				unset( $table['Й'], $table['й'], $table['І'], $table['і'] );
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
				unset(
					$table['Щ'],
					$table['щ'],
					$table['Ъ'],
					$table['ъ'],
					$table['Ы'],
					$table['ы'],
					$table['Ь'],
					$table['ь'],
					$table['Э'],
					$table['э'],
					$table['Ю'],
					$table['ю'],
					$table['Я'],
					$table['я'],
					$table['Ѣ'],
					$table['ѣ'],
					$table['Ѳ'],
					$table['ѳ'],
					$table['Ѵ'],
					$table['ѵ']
				);
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
				unset( $table['Ё'], $table['ё'] );
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
				unset(
					$table['Щ'],
					$table['щ'],
					$table['Ъ'],
					$table['ъ'],
					$table['Ь'],
					$table['ь'],
					$table['Э'],
					$table['э'],
					$table['Ю'],
					$table['ю'],
					$table['Я'],
					$table['я']
				);

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
				$table = [
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
				];
				for ( $code = 0x0590; $code <= 0x05CF; $code++ ) {
					$table[ Mbstring::mb_chr( $code ) ] = '';
				}
				for ( $code = 0x05F0; $code <= 0x05F5; $code++ ) {
					$table[ Mbstring::mb_chr( $code ) ] = '';
				}
				for ( $code = 0xFB1D; $code <= 0xFB4F; $code++ ) {
					$table[ Mbstring::mb_chr( $code ) ] = '';
				}
				break;
			// phpcs:disable PSR2.ControlStructures.SwitchDeclaration.TerminatingComment
			case 'zh_CN':
			case 'zh_HK':
			case 'zh_SG':
			case 'zh_TW':
				// phpcs:enable PSR2.ControlStructures.SwitchDeclaration.TerminatingComment

				// Chinese dictionary copied from Pinyin Permalinks plugin.
				$table = [
					'A'      => '啊阿吖嗄锕',
					'Ai'     => '埃挨哎唉哀皑癌蔼矮艾碍爱隘捱嗳嫒瑷暧砹锿霭',
					'An'     => '鞍氨安俺按暗岸胺案谙埯揞庵桉铵鹌黯',
					'Ang'    => '肮昂盎',
					'Ao'     => '凹敖熬翱袄傲奥懊澳坳拗嗷岙廒遨媪骜獒聱螯鏊鳌鏖',
					'Ba'     => '芭捌扒叭吧笆八疤巴拔跋靶把坝霸罢爸茇菝岜灞钯粑鲅魃',
					'Bai'    => '白柏百摆佰败拜稗捭掰',
					'Ban'    => '斑班搬扳般颁板版扮拌伴瓣半办绊阪坂钣瘢癍舨',
					'Bang'   => '邦帮梆榜膀绑棒磅蚌镑傍谤蒡浜',
					'Bao'    => '苞胞包褒薄雹保堡饱宝抱报暴豹鲍爆曝勹葆孢煲鸨褓趵龅',
					'Bei'    => '杯碑悲卑北辈背贝钡倍狈备惫焙被孛陂邶蓓呗悖碚鹎褙鐾鞴',
					'Ben'    => '奔苯本笨畚坌锛',
					'Beng'   => '崩绷甭泵蹦迸嘣甏',
					'Bi'     => '逼鼻比鄙笔彼碧蓖蔽毕毙毖币庇痹闭敝弊必壁臂避陛匕俾荜荸萆薜吡哔狴庳愎滗濞弼妣婢嬖璧贲睥畀铋秕裨筚箅篦舭襞跸髀',
					'Bian'   => '鞭边编贬扁便变卞辨辩辫遍匾弁苄忭汴缏煸砭碥窆褊蝙笾鳊',
					'Biao'   => '标彪膘表婊骠飑飙飚镖镳瘭裱鳔',
					'Bie'    => '鳖憋别瘪蹩',
					'Bin'    => '彬斌濒滨宾摈傧豳缤玢殡膑镔髌鬓',
					'Bing'   => '兵冰柄丙秉饼炳病并禀邴摒槟',
					'Bo'     => '剥玻菠播拨钵波博勃搏铂箔伯帛舶脖膊渤泊驳卜亳啵饽檗擘礴钹鹁簸跛踣',
					'Bu'     => '捕哺补埠不布步簿部怖卟逋瓿晡钚钸醭',
					'Ca'     => '擦礤',
					'Cai'    => '猜裁材才财睬踩采彩菜蔡',
					'Can'    => '餐参蚕残惭惨灿孱骖璨粲黪',
					'Cang'   => '苍舱仓沧藏伧',
					'Cao'    => '操糙槽曹草嘈漕螬艚',
					'Ce'     => '厕策侧册测恻',
					'Cen'    => '岑涔',
					'Ceng'   => '层蹭曾噌',
					'Cha'    => '插叉茬茶查碴搽察岔差诧嚓猹馇汊姹杈槎檫锸镲衩',
					'Chai'   => '拆柴豺侪钗瘥虿',
					'Chan'   => '搀掺蝉馋谗缠铲产阐颤冁谄蒇廛忏潺澶羼婵骣觇禅蟾躔',
					'Chang'  => '昌猖场尝常长偿肠厂敞畅唱倡伥鬯苌菖徜怅阊娼嫦昶氅鲳',
					'Chao'   => '超抄钞朝嘲潮巢吵炒怊晁焯耖',
					'Che'    => '车扯撤掣彻澈坼砗',
					'Chen'   => '郴臣辰尘晨忱沉陈趁衬谌谶抻嗔宸琛榇碜龀',
					'Cheng'  => '撑称城橙成呈乘程惩澄诚承逞骋秤丞埕枨柽塍瞠铖裎蛏酲',
					'Chi'    => '吃痴持池迟弛驰耻齿侈尺赤翅斥炽傺墀茌叱哧啻嗤彳饬媸敕眵鸱瘛褫蚩螭笞篪豉踟魑',
					'Chong'  => '充冲虫崇宠茺忡憧铳舂艟',
					'Chou'   => '抽酬畴踌稠愁筹仇绸瞅丑臭俦帱惆瘳雠',
					'Chu'    => '初出橱厨躇锄雏滁除楚础储矗搐触处畜亍刍怵憷绌杵楮樗褚蜍蹰黜',
					'Chuai'  => '揣搋啜膪踹',
					'Chuan'  => '川穿椽传船喘串舛遄氚钏舡',
					'Chuang' => '疮窗床闯创怆',
					'Chui'   => '吹炊捶锤垂陲棰槌',
					'Chun'   => '春椿醇唇淳纯蠢莼鹑蝽',
					'Chuo'   => '戳绰辍踔龊',
					'Ci'     => '疵茨磁雌辞慈瓷词此刺赐次茈祠鹚糍',
					'Cong'   => '聪葱囱匆从丛苁淙骢琮璁枞',
					'Cou'    => '凑楱辏腠',
					'Cu'     => '粗醋簇促蔟徂猝殂蹙蹴',
					'Cuan'   => '蹿篡窜汆撺爨镩',
					'Cui'    => '摧崔催脆瘁粹淬翠萃啐悴璀榱毳',
					'Cun'    => '村存寸忖皴',
					'Cuo'    => '磋撮搓措挫错厝嵯脞锉矬痤鹾蹉',
					'Da'     => '搭达答瘩打大耷哒嗒怛妲沓褡笪靼鞑',
					'Dai'    => '呆歹傣戴带殆代贷袋待逮怠埭甙呔岱迨绐玳黛',
					'Dan'    => '耽担丹单郸掸胆旦氮但惮淡诞弹蛋儋凼萏菪啖澹宕殚赕眈疸瘅聃箪',
					'Dang'   => '当挡党荡档谠砀铛裆',
					'Dao'    => '刀捣蹈倒岛祷导到稻悼道盗叨氘焘纛',
					'De'     => '德得的锝',
					'Deng'   => '蹬灯登等瞪凳邓噔嶝戥磴镫簦',
					'Di'     => '堤低滴迪敌笛狄涤翟嫡抵底地蒂第帝弟递缔氐籴诋谛邸坻荻嘀娣柢棣觌祗砥碲睇镝羝骶',
					'Dia'    => '嗲',
					'Dian'   => '颠掂滇碘点典靛垫电佃甸店惦奠淀殿丶阽坫巅玷钿癜癫簟踮',
					'Diao'   => '碉叼雕凋刁掉吊钓调铞貂鲷',
					'Die'    => '跌爹碟蝶迭谍叠垤堞揲喋牒瓞耋鲽',
					'Ding'   => '丁盯叮钉顶鼎锭定订仃啶玎腚碇町疔耵酊',
					'Diu'    => '丢铥',
					'Dong'   => '东冬董懂动栋侗恫冻洞垌咚岽峒氡胨胴硐鸫',
					'Dou'    => '兜抖斗陡豆逗痘都蔸钭窦蚪篼',
					'Du'     => '督毒犊独读堵睹赌杜镀肚度渡妒芏嘟渎椟牍蠹笃髑黩',
					'Duan'   => '端短锻段断缎椴煅簖',
					'Dui'    => '堆兑队对怼憝碓镦',
					'Dun'    => '墩吨蹲敦顿钝盾遁沌炖砘礅盹趸',
					'Duo'    => '掇哆多夺垛躲朵跺舵剁惰堕咄哚沲缍铎裰踱',
					'E'      => '蛾峨鹅俄额讹娥恶厄扼遏鄂饿噩谔垩苊莪萼呃愕屙婀轭腭锇锷鹗颚鳄',
					'Ei'     => '诶',
					'En'     => '恩蒽摁嗯',
					'Er'     => '而儿耳尔饵洱二贰迩珥铒鸸鲕',
					'Fa'     => '发罚筏伐乏阀法珐垡砝',
					'Fan'    => '藩帆番翻樊矾钒繁凡烦反返范贩犯饭泛蕃蘩幡梵燔畈蹯',
					'Fang'   => '坊芳方肪房防妨仿访纺放邡枋钫舫鲂',
					'Fei'    => '菲非啡飞肥匪诽吠肺废沸费芾狒悱淝妃绯榧腓斐扉砩镄痱蜚篚翡霏鲱',
					'Fen'    => '芬酚吩氛分纷坟焚汾粉奋份忿愤粪偾瀵棼鲼鼢',
					'Feng'   => '丰封枫蜂峰锋风疯烽逢冯缝讽奉凤俸酆葑唪沣砜',
					'Fo'     => '佛',
					'Fou'    => '否缶',
					'Fu'     => '夫敷肤孵扶拂辐幅氟符伏俘服浮涪福袱弗甫抚辅俯釜斧脯腑府腐赴副覆赋复傅付阜父腹负富讣附妇缚咐匐凫郛芙苻茯莩菔拊呋呒幞怫滏艴孚驸绂绋桴赙祓黻黼罘稃馥蚨蜉蝠蝮麸趺跗鲋鳆',
					'Ga'     => '噶嘎垓尬尕尜旮钆',
					'Gai'    => '该改概钙盖溉丐陔戤赅',
					'Gan'    => '干甘杆柑竿肝赶感秆敢赣坩苷尴擀泔淦澉绀橄旰矸疳酐',
					'Gang'   => '冈刚钢缸肛纲岗港杠戆罡筻',
					'Gao'    => '篙皋高膏羔糕搞镐稿告睾诰郜藁缟槔槁杲锆',
					'Ge'     => '哥歌搁戈鸽胳疙割革葛格蛤阁隔铬个各咯鬲仡哿圪塥嗝纥搿膈硌镉袼虼舸骼',
					'Gei'    => '给',
					'Gen'    => '根跟亘茛哏艮',
					'Geng'   => '耕更庚羹埂耿梗哽赓绠鲠',
					'Gong'   => '工攻功恭龚供躬公宫弓巩汞拱贡共珙肱蚣觥',
					'Gou'    => '钩勾沟苟狗垢构购够佝诟岣遘媾缑枸觏彀笱篝鞲',
					'Gu'     => '辜菇咕箍估沽孤姑鼓古蛊骨谷股故顾固雇嘏诂菰崮汩梏轱牯牿臌毂瞽罟钴锢鸪痼蛄酤觚鲴',
					'Gua'    => '刮瓜剐寡挂褂卦诖呱栝胍鸹',
					'Guai'   => '乖拐怪',
					'Guan'   => '棺关官冠观管馆罐惯灌贯倌莞掼涫盥鹳鳏',
					'Guang'  => '光广逛咣犷桄胱',
					'Gui'    => '瑰规圭硅归龟闺轨鬼诡癸桂柜跪贵刽匦刿庋宄妫炅晷皈簋鲑鳜',
					'Gun'    => '辊滚棍衮绲磙鲧',
					'Guo'    => '锅郭国果裹过馘埚掴呙帼崞猓椁虢聒蜾蝈',
					'Ha'     => '哈铪',
					'Hai'    => '骸孩海氦亥害骇嗨胲醢',
					'Han'    => '酣憨邯韩含涵寒函喊罕翰撼捍旱憾悍焊汗汉邗菡撖犴阚瀚晗焓顸颔蚶鼾',
					'Hang'   => '夯杭航沆绗颃',
					'Hao'    => '壕嚎豪毫郝好耗号浩蒿薅嗥嚆濠灏昊皓颢蚝',
					'He'     => '呵喝荷菏核禾和何合盒貉阂河涸赫褐鹤贺诃劾壑嗬阖曷盍颌蚵翮',
					'Hei'    => '嘿黑',
					'Hen'    => '痕很狠恨',
					'Heng'   => '哼亨横衡恒蘅珩桁',
					'Hong'   => '轰哄烘虹鸿洪宏弘红黉訇讧荭蕻薨闳泓',
					'Hou'    => '喉侯猴吼厚候后堠後逅瘊篌糇鲎骺',
					'Hu'     => '呼乎忽瑚壶葫胡蝴狐糊湖弧虎唬护互沪户冱唿囫岵猢怙惚浒滹琥槲轷觳烀煳戽扈祜瓠鹄鹕鹱笏醐斛鹘',
					'Hua'    => '花哗华猾滑画划化话骅桦砉铧',
					'Huai'   => '槐徊怀淮坏踝',
					'Huan'   => '欢环桓还缓换患唤痪豢焕涣宦幻奂擐獾洹浣漶寰逭缳锾鲩鬟',
					'Huang'  => '荒慌黄磺蝗簧皇凰惶煌晃幌恍谎隍徨湟潢遑璜肓癀蟥篁鳇',
					'Hui'    => '灰挥辉徽恢蛔回毁悔慧卉惠晦贿秽会烩汇讳诲绘诙茴荟蕙咴喙隳洄彗缋桧晖恚虺蟪麾',
					'Hun'    => '荤昏婚魂浑混诨馄阍溷珲',
					'Huo'    => '豁活伙火获或惑霍货祸劐藿攉嚯夥钬锪镬耠蠖',
					'Ji'     => '击圾基机畸稽积箕肌饥迹激讥鸡姬绩缉吉极棘辑籍集及急疾汲即嫉级挤几脊己蓟技冀季伎祭剂悸济寄寂计记既忌际妓继纪藉亟乩剞佶偈墼芨芰荠蒺蕺掎叽咭哜唧岌嵴洎屐骥畿玑楫殛戟戢赍觊犄齑矶羁嵇稷瘠虮笈笄暨跻跽霁鲚鲫髻麂',
					'Jia'    => '嘉枷夹佳家加荚颊贾甲钾假稼价架驾嫁伽郏葭岬浃迦珈戛胛恝铗镓痂瘕蛱笳袈跏',
					'Jian'   => '歼监坚尖笺间煎兼肩艰奸缄茧检柬碱硷拣捡简俭剪减荐槛鉴践贱见键箭件健舰剑饯渐溅涧建僭谏谫菅蒹搛湔蹇謇缣枧楗戋戬牮犍毽腱睑锏鹣裥笕翦踺鲣鞯',
					'Jiang'  => '僵姜将浆江疆蒋桨奖讲匠酱降茳洚绛缰犟礓耩糨豇',
					'Jiao'   => '蕉椒礁焦胶交郊浇骄娇嚼搅铰矫侥脚狡角饺缴绞剿教酵轿较叫窖佼僬艽茭挢噍徼姣敫皎鹪蛟醮跤鲛',
					'Jie'    => '揭接皆秸街阶截劫节桔杰捷睫竭洁结解姐戒芥界借介疥诫届讦诘拮喈嗟婕孑桀碣疖颉蚧羯鲒骱',
					'Jin'    => '巾筋斤金今津襟紧锦仅谨进靳晋禁近烬浸尽劲卺荩堇噤馑廑妗缙瑾槿赆觐衿矜',
					'Jing'   => '荆兢茎睛晶鲸京惊精粳经井警景颈静境敬镜径痉靖竟竞净刭儆阱菁獍憬泾迳弪婧肼胫腈旌箐',
					'Jiong'  => '炯窘迥扃',
					'Jiu'    => '揪究纠玖韭久灸九酒厩救旧臼舅咎就疚僦啾阄柩桕鸠鹫赳鬏',
					'Ju'     => '鞠拘狙疽居驹菊局咀矩举沮聚拒据巨具距踞锯俱句惧炬剧倨讵苣苴莒掬遽屦琚椐榘榉橘犋飓钜锔窭裾醵踽龃雎鞫',
					'Juan'   => '捐鹃娟倦眷卷绢鄄狷涓桊蠲锩镌隽',
					'Jue'    => '撅攫抉掘倔爵觉决诀绝厥劂谲矍蕨噘崛獗孓珏桷橛爝镢蹶觖',
					'Jun'    => '均菌钧军君峻俊竣浚郡骏捃皲',
					'Ka'     => '喀咖卡佧咔胩',
					'Kai'    => '开揩楷凯慨剀垲蒈忾恺铠锎锴',
					'Kan'    => '刊堪勘坎砍看侃莰戡龛瞰',
					'Kang'   => '康慷糠扛抗亢炕伉闶钪',
					'Kao'    => '考拷烤靠尻栲犒铐',
					'Ke'     => '坷苛柯棵磕颗科壳咳可渴克刻客课嗑岢恪溘骒缂珂轲氪瞌钶锞稞疴窠颏蝌髁',
					'Ken'    => '肯啃垦恳裉',
					'Keng'   => '坑吭铿',
					'Kong'   => '空恐孔控倥崆箜',
					'Kou'    => '抠口扣寇芤蔻叩囗眍筘',
					'Ku'     => '枯哭窟苦酷库裤刳堀喾绔骷',
					'Kua'    => '夸垮挎跨胯侉',
					'Kuai'   => '块筷侩快蒯郐哙狯浍脍',
					'Kuan'   => '宽款髋',
					'Kuang'  => '匡筐狂框矿眶旷况诓诳邝圹夼哐纩贶',
					'Kui'    => '亏盔岿窥葵奎魁傀馈愧溃馗匮夔隗蒉揆喹喟悝愦逵暌睽聩蝰篑跬',
					'Kun'    => '坤昆捆困悃阃琨锟醌鲲髡',
					'Kuo'    => '括扩廓阔蛞',
					'La'     => '垃拉喇蜡腊辣啦剌邋旯砬瘌',
					'Lai'    => '莱来赖崃徕涞濑赉睐铼癞籁',
					'Lan'    => '蓝婪栏拦篮阑兰澜谰揽览懒缆烂滥岚漤榄斓罱镧褴',
					'Lang'   => '琅榔狼廊郎朗浪蒗啷阆稂螂',
					'Lao'    => '捞劳牢老佬姥酪烙涝唠崂忉栳铑铹痨耢醪',
					'Le'     => '勒乐了仂叻泐鳓',
					'Lei'    => '雷镭蕾磊累儡垒擂肋类泪羸诔嘞嫘缧檑耒酹',
					'Leng'   => '棱楞冷塄愣',
					'Li'     => '厘梨犁黎篱狸离漓理李里鲤礼莉荔吏栗丽厉励砾历利傈例俐痢立粒沥隶力璃哩俪俚郦坜苈莅蓠藜呖唳喱猁溧澧逦娌嫠骊缡枥栎轹膦戾砺詈罹锂鹂疠疬蛎蜊蠡笠篥粝醴跞雳鲡鳢黧',
					'Lian'   => '联莲连镰廉怜涟帘敛脸链恋炼练蔹奁潋濂琏楝殓臁裢裣蠊鲢',
					'Liang'  => '俩粮凉梁粱良两辆量晾亮谅墚莨椋锒踉靓魉',
					'Liao'   => '撩聊僚疗燎寥辽潦撂镣廖料蓼尥嘹獠寮缭钌鹩',
					'Lie'    => '列裂烈劣猎冽埒捩咧洌趔躐鬣',
					'Lin'    => '琳林磷霖临邻鳞淋凛赁吝拎蔺啉嶙廪懔遴檩辚瞵粼躏麟',
					'Ling'   => '玲菱零龄铃伶羚凌灵陵岭领另令酃苓呤囹泠绫柃棂瓴聆蛉翎鲮',
					'Liu'    => '溜琉榴硫馏留刘瘤流柳六浏遛骝绺旒熘锍镏鹨鎏',
					'Long'   => '龙聋咙笼窿隆垄拢陇垅茏泷珑栊胧砻癃',
					'Lou'    => '楼娄搂篓漏陋偻蒌喽嵝镂瘘耧蝼髅',
					'Lu'     => '芦卢颅庐炉掳卤虏鲁麓碌露路赂鹿潞禄录陆戮垆撸噜泸渌漉逯璐栌橹轳辂辘氇胪镥鸬鹭簏舻鲈',
					'Luan'   => '峦挛孪滦卵乱脔娈栾鸾銮',
					'Lue'    => '掠略锊',
					'Lun'    => '抡轮伦仑沦纶论囵',
					'Luo'    => '萝螺罗逻锣箩骡裸落洛骆络倮蠃荦摞猡泺漯珞椤脶镙瘰雒',
					'Lv'     => '驴吕铝侣旅履屡缕虑氯律滤绿捋闾榈膂稆褛',
					'Ma'     => '妈麻玛码蚂马骂嘛吗唛犸杩蟆',
					'Mai'    => '埋买麦卖迈脉劢荬霾',
					'Man'    => '瞒馒蛮满蔓曼慢漫谩墁幔缦熳镘颟螨鳗鞔',
					'Mang'   => '芒茫盲氓忙莽邙漭硭蟒',
					'Mao'    => '猫茅锚毛矛铆卯茂冒帽貌贸袤茆峁泖瑁昴牦耄旄懋瞀蝥蟊髦',
					'Me'     => '么麽',
					'Mei'    => '玫枚梅酶霉煤没眉媒镁每美昧寐妹媚莓嵋猸浼湄楣镅鹛袂魅',
					'Men'    => '门闷们扪焖懑钔',
					'Meng'   => '萌蒙檬盟锰猛梦孟勐甍瞢懵朦礞虻蜢蠓艋艨',
					'Mi'     => '眯醚靡糜迷谜弥米秘觅泌蜜密幂芈谧咪嘧猕汨宓弭脒祢敉縻麋',
					'Mian'   => '棉眠绵冕免勉娩缅面沔渑湎腼眄',
					'Miao'   => '苗描瞄藐秒渺庙妙喵邈缈杪淼眇鹋',
					'Mie'    => '蔑灭乜咩蠛篾',
					'Min'    => '民抿皿敏悯闽苠岷闵泯缗玟珉愍黾鳘',
					'Ming'   => '明螟鸣铭名命冥茗溟暝瞑酩',
					'Miu'    => '谬缪',
					'Mo'     => '摸摹蘑模膜磨摩魔抹末莫墨默沫漠寞陌谟茉蓦馍嫫嬷殁镆秣瘼耱貊貘',
					'Mou'    => '谋牟某侔哞眸蛑鍪',
					'Mu'     => '拇牡亩姆母墓暮幕募慕木目睦牧穆仫坶苜沐毪钼',
					'Na'     => '拿哪呐钠那娜纳讷捺肭镎衲',
					'Nai'    => '氖乃奶耐奈鼐佴艿萘柰',
					'Nan'    => '南男难喃囝囡楠腩蝻赧',
					'Nang'   => '囊攮囔馕曩',
					'Nao'    => '挠脑恼闹淖孬垴呶猱瑙硇铙蛲',
					'Ne'     => '呢',
					'Nei'    => '馁内',
					'Nen'    => '嫩恁',
					'Neng'   => '能',
					'Ni'     => '妮霓倪泥尼拟你匿腻逆溺伲坭蘼猊怩昵旎睨铌鲵',
					'Nian'   => '蔫拈年碾撵捻念廿埝辇黏鲇鲶',
					'Niang'  => '娘酿',
					'Niao'   => '鸟尿茑嬲脲袅',
					'Nie'    => '捏聂孽啮镊镍涅陧蘖嗫颞臬蹑',
					'Nin'    => '您',
					'Ning'   => '柠狞凝宁拧泞佞咛甯聍',
					'Niu'    => '牛扭钮纽狃忸妞',
					'Nong'   => '脓浓农弄侬哝',
					'Nou'    => '耨',
					'Nu'     => '奴努怒弩胬孥驽',
					'Nuan'   => '暖',
					'Nue'    => '虐疟挪',
					'Nuo'    => '懦糯诺傩搦喏锘',
					'Nv'     => '女恧钕衄',
					'O'      => '哦噢',
					'Ou'     => '欧鸥殴藕呕偶沤讴怄瓯耦',
					'Pa'     => '耙啪趴爬帕怕琶葩杷筢',
					'Pai'    => '拍排牌徘湃派俳蒎哌',
					'Pan'    => '攀潘盘磐盼畔判叛拚爿泮袢襻蟠蹒',
					'Pang'   => '乓庞旁耪胖彷滂逄螃',
					'Pao'    => '抛咆刨炮袍跑泡匏狍庖脬疱',
					'Pei'    => '呸胚培裴赔陪配佩沛辔帔旆锫醅霈',
					'Pen'    => '喷盆湓',
					'Peng'   => '砰抨烹澎彭蓬棚硼篷膨朋鹏捧碰堋嘭怦蟛',
					'Pi'     => '辟坯砒霹批披劈琵毗啤脾疲皮匹痞僻屁譬丕仳陴邳郫圮埤鼙芘擗噼庀淠媲纰枇甓罴铍癖疋蚍蜱貔',
					'Pian'   => '篇偏片骗谝骈犏胼翩蹁',
					'Piao'   => '飘漂瓢票剽嘌嫖缥殍瞟螵',
					'Pie'    => '撇瞥丿苤氕',
					'Pin'    => '拼频贫品聘姘嫔榀牝颦',
					'Ping'   => '乒坪苹萍平凭瓶评屏俜娉枰鲆',
					'Po'     => '坡泼颇婆破魄迫粕叵鄱珀钋钷皤笸',
					'Pou'    => '剖裒掊',
					'Pu'     => '扑铺仆莆葡菩蒲埔朴圃普浦谱瀑匍噗溥濮璞氆镤镨蹼',
					'Qi'     => '期欺戚妻七凄漆柒沏其棋奇歧畦崎脐齐旗祈祁骑起岂乞企启契砌器气迄弃汽泣讫亓圻芑芪萁萋葺蕲嘁屺岐汔淇骐绮琪琦杞桤槭耆祺憩碛颀蛴蜞綦鳍麒',
					'Qia'    => '掐恰洽葜袷髂',
					'Qian'   => '牵扦钎铅千迁签仟谦乾黔钱钳前潜遣浅谴堑嵌欠歉倩佥阡芊芡掮岍悭慊骞搴褰缱椠肷愆钤虔箝',
					'Qiang'  => '枪呛腔羌墙蔷强抢戕嫱樯戗炝锖锵镪襁蜣羟跄',
					'Qiao'   => '橇锹敲悄桥瞧乔侨巧鞘撬翘峭俏窍劁诮谯荞峤愀憔缲樵硗跷鞒',
					'Qie'    => '切茄且怯窃郄惬妾挈锲箧趄',
					'Qin'    => '钦侵亲秦琴勤芹擒禽寝沁芩揿吣嗪噙溱檎锓螓衾',
					'Qing'   => '青轻氢倾卿清擎晴氰情顷请庆苘圊檠磬蜻罄綮謦鲭黥',
					'Qiong'  => '琼穷邛茕穹蛩筇跫銎',
					'Qiu'    => '秋丘邱球求囚酋泅俅巯犰湫逑遒楸赇虬蚯蝤裘糗鳅鼽',
					'Qu'     => '趋区蛆曲躯屈驱渠取娶龋趣去诎劬蕖蘧岖衢阒璩觑氍朐祛磲鸲癯蛐蠼麴瞿黢',
					'Quan'   => '圈颧权醛泉全痊拳犬券劝诠荃犭悛绻辁畎铨蜷筌鬈',
					'Que'    => '缺炔瘸却鹊榷确雀阕阙悫',
					'Qun'    => '裙群逡麇',
					'Ran'    => '然燃冉染苒蚺髯',
					'Rang'   => '瓤壤攘嚷让禳穰',
					'Rao'    => '饶扰绕荛娆桡',
					'Re'     => '惹热',
					'Ren'    => '壬仁人忍韧任认刃妊纫仞荏饪轫稔衽',
					'Reng'   => '扔仍',
					'Ri'     => '日',
					'Rong'   => '戎茸蓉荣融熔溶容绒冗嵘狨榕肜蝾',
					'Rou'    => '揉柔肉糅蹂鞣',
					'Ru'     => '茹蠕儒孺如辱乳汝入褥蓐薷嚅洳溽濡缛铷襦颥',
					'Ruan'   => '软阮朊',
					'Rui'    => '蕊瑞锐芮蕤枘睿蚋',
					'Run'    => '闰润',
					'Ruo'    => '若弱偌箬',
					'Sa'     => '撒洒萨卅挲脎飒',
					'Sai'    => '腮鳃塞赛噻',
					'San'    => '三叁伞散仨彡馓毵',
					'Sang'   => '桑嗓丧搡磉颡',
					'Sao'    => '搔骚扫嫂埽缫臊瘙鳋',
					'Se'     => '瑟色涩啬铯穑',
					'Sen'    => '森',
					'Seng'   => '僧',
					'Sha'    => '莎砂杀刹沙纱傻啥煞唼歃铩痧裟霎鲨',
					'Shai'   => '筛晒酾',
					'Shan'   => '珊苫杉山删煽衫闪陕擅赡膳善汕扇缮栅讪鄯芟潸姗嬗骟膻钐疝蟮舢跚鳝',
					'Shang'  => '墒伤商赏晌上尚裳垧绱殇熵觞',
					'Shao'   => '梢捎稍烧芍勺韶少哨邵绍劭潲杓蛸筲艄',
					'She'    => '奢赊蛇舌舍赦摄射慑涉社设厍佘猞滠畲麝',
					'Shen'   => '砷申呻伸身深娠绅神沈审婶甚肾慎渗什诜谂莘葚哂渖胂矧蜃糁',
					'Sheng'  => '声生甥牲升绳省盛剩胜圣嵊晟眚笙',
					'Shi'    => '匙师失狮施湿诗尸虱十石拾时食蚀实识史矢使屎驶始式示士世柿事拭誓逝势是嗜噬适仕侍释饰氏市恃室视试谥埘莳蓍弑轼贳炻礻铈舐筮豕鲥鲺',
					'Shou'   => '收手首守寿授售受瘦兽狩绶艏',
					'Shu'    => '蔬枢梳殊抒输叔舒淑疏书赎孰熟薯暑曙署蜀黍鼠属术述树束戍竖墅庶数漱恕丨倏塾菽摅沭澍姝纾毹腧殳秫',
					'Shua'   => '刷耍唰',
					'Shuai'  => '率摔衰甩帅蟀',
					'Shuan'  => '栓拴闩涮',
					'Shuang' => '霜双爽孀',
					'Shui'   => '谁水睡税',
					'Shun'   => '吮瞬顺舜',
					'Shuo'   => '说硕朔烁蒴搠妁槊铄',
					'Si'     => '斯撕嘶思私司丝死肆寺嗣四伺似饲巳厮俟兕厶咝汜泗澌姒驷缌祀锶鸶耜蛳笥',
					'Song'   => '松耸怂颂送宋讼诵凇菘崧嵩忪悚淞竦',
					'Sou'    => '搜艘擞嗽叟薮嗖嗾馊溲飕瞍锼螋',
					'Su'     => '苏酥俗素速粟僳塑溯宿诉肃夙谡蔌嗉愫涑簌觫稣',
					'Suan'   => '酸蒜算狻',
					'Sui'    => '虽隋随绥髓碎岁穗遂隧祟谇荽濉邃燧眭睢',
					'Sun'    => '孙损笋荪狲飧榫隼',
					'Suo'    => '蓑梭唆缩琐索锁所唢嗦嗍娑桫睃羧',
					'Ta'     => '塌他它她塔獭挞蹋踏闼溻遢榻铊趿鳎',
					'Tai'    => '胎苔抬台泰酞太态汰邰薹骀肽炱钛跆鲐',
					'Tan'    => '坍摊贪瘫滩坛檀痰潭谭谈坦毯袒碳探叹炭郯昙忐钽锬覃',
					'Tang'   => '汤塘搪堂棠膛唐糖倘躺淌趟烫傥帑惝溏瑭樘铴镗耥螗螳羰醣',
					'Tao'    => '掏涛滔绦萄桃逃淘陶讨套鼗啕洮韬饕',
					'Te'     => '特忒忑慝铽',
					'Teng'   => '藤腾疼誊滕',
					'Ti'     => '梯剔踢锑提题蹄啼体替嚏惕涕剃屉倜悌逖绨缇鹈醍',
					'Tian'   => '天添填田甜恬舔腆掭忝阗殄畋',
					'Tiao'   => '挑条迢眺跳佻苕祧窕蜩笤粜龆鲦髫',
					'Tie'    => '贴铁帖萜餮',
					'Ting'   => '厅听烃汀廷停亭庭挺艇莛葶婷梃铤蜓霆',
					'Tong'   => '通桐酮瞳同铜彤童桶捅筒统痛佟仝茼嗵恸潼砼',
					'Tou'    => '偷投头透骰',
					'Tu'     => '凸秃突图徒途涂屠土吐兔堍荼菟钍酴',
					'Tuan'   => '湍团抟彖疃',
					'Tui'    => '推颓腿蜕褪退煺',
					'Tun'    => '囤吞屯臀氽饨暾豚',
					'Tuo'    => '拖托脱鸵陀驮驼椭妥拓唾佗坨庹沱柝柁橐砣箨酡跎鼍',
					'Wa'     => '挖哇蛙洼娃瓦袜佤娲腽',
					'Wai'    => '歪外崴',
					'Wan'    => '豌弯湾玩顽丸烷完碗挽晚皖惋宛婉万腕剜芄菀纨绾琬脘畹蜿',
					'Wang'   => '汪王亡枉网往旺望忘妄罔惘辋魍',
					'Wei'    => '威巍微危韦违桅围唯惟为潍维苇萎委伟伪尾纬未蔚味畏胃喂魏位渭谓尉慰卫偎诿隈圩葳薇帏帷嵬猥猬闱沩洧涠逶娓玮韪軎炜煨痿艉鲔',
					'Wen'    => '瘟温蚊文闻纹吻稳紊问刎夂阌汶璺攵雯',
					'Weng'   => '嗡翁瓮蓊蕹',
					'Wo'     => '挝蜗涡窝我斡卧握沃倭莴喔幄渥肟硪龌',
					'Wu'     => '巫呜钨乌污诬屋无芜梧吾吴毋武五捂午舞伍侮坞戊雾晤物勿务悟误兀仵阢邬圬芴唔庑怃忤寤迕妩婺骛杌牾焐鹉鹜痦蜈鋈鼯',
					'Xi'     => '栖昔熙析西硒矽晰嘻吸锡牺稀息希悉膝夕惜熄烯溪汐犀檄袭席习媳喜铣洗系隙戏细僖兮隰郗茜菥葸蓰奚唏徙饩阋浠淅屣嬉玺樨曦觋欷歙熹禊禧皙穸裼蜥螅蟋舄舾羲粞翕醯蹊鼷',
					'Xia'    => '瞎虾匣霞辖暇峡侠狭下厦夏吓呷狎遐瑕柙硖罅黠',
					'Xian'   => '掀锨先仙鲜纤咸贤衔舷闲涎弦嫌显险现献县腺馅羡宪陷限线冼苋莶藓岘猃暹娴氙燹祆鹇痫蚬筅籼酰跣跹霰',
					'Xiang'  => '相厢镶香箱襄湘乡翔祥详想响享项巷橡像向象芗葙饷庠骧缃蟓鲞飨',
					'Xiao'   => '萧硝霄削哮嚣销消宵淆晓小孝校肖啸笑效哓崤潇逍骁绡枭枵筱箫魈',
					'Xie'    => '楔些歇蝎鞋协挟携邪斜胁谐写械卸蟹懈泄泻谢屑偕亵勰燮薤撷獬廨渫瀣邂绁缬榭榍蹀躞',
					'Xin'    => '薪芯锌欣辛新忻心信衅囟馨昕歆镡鑫',
					'Xing'   => '星腥猩惺兴刑型形邢行醒幸杏性姓陉荇荥擤饧悻硎',
					'Xiong'  => '兄凶胸匈汹雄熊芎',
					'Xiu'    => '休修羞朽嗅锈秀袖绣咻岫馐庥溴鸺貅髹',
					'Xu'     => '墟戌需虚嘘须徐许蓄酗叙旭序恤絮婿绪续吁诩勖蓿洫溆顼栩煦盱胥糈醑',
					'Xuan'   => '轩喧宣悬旋玄选癣眩绚儇谖萱揎泫渲漩璇楦暄炫煊碹铉镟痃',
					'Xue'    => '靴薛学穴雪血谑噱泶踅鳕',
					'Xun'    => '勋熏循旬询寻驯巡殉汛训讯逊迅巽郇埙荀荨蕈薰峋徇獯恂洵浔曛醺鲟',
					'Ya'     => '压押鸦鸭呀丫芽牙蚜崖衙涯雅哑亚讶伢垭揠岈迓娅琊桠氩砑睚痖',
					'Yan'    => '焉咽阉烟淹盐严研蜒岩延言颜阎炎沿奄掩眼衍演艳堰燕厌砚雁唁彦焰宴谚验厣赝剡俨偃兖谳郾鄢埏菸崦恹闫阏湮滟妍嫣琰檐晏胭腌焱罨筵酽趼魇餍鼹',
					'Yang'   => '殃央鸯秧杨扬佯疡羊洋阳氧仰痒养样漾徉怏泱炀烊恙蛘鞅',
					'Yao'    => '邀腰妖瑶摇尧遥窑谣姚咬舀药要耀钥夭爻吆崾徭幺珧杳轺曜肴铫鹞窈繇鳐',
					'Ye'     => '椰噎耶爷野冶也页掖业叶曳腋夜液靥谒邺揶晔烨铘',
					'Yi'     => '一壹医揖铱依伊衣颐夷遗移仪胰疑沂宜姨彝椅蚁倚已乙矣以艺抑易邑屹亿役臆逸肄疫亦裔意毅忆义益溢诣议谊译异翼翌绎刈劓佚佾诒圯埸懿苡荑薏弈奕挹弋呓咦咿嗌噫峄嶷猗饴怿怡悒漪迤驿缢殪轶贻欹旖熠眙钇镒镱痍瘗癔翊蜴舣羿翳酏黟',
					'Yin'    => '茵荫因殷音阴姻吟银淫寅饮尹引隐印胤鄞垠堙茚吲喑狺夤洇氤铟瘾窨蚓霪龈',
					'Ying'   => '英樱婴鹰应缨莹萤营荧蝇迎赢盈影颖硬映嬴郢茔莺萦蓥撄嘤膺滢潆瀛瑛璎楹媵鹦瘿颍罂',
					'Yo'     => '哟唷',
					'Yong'   => '拥佣臃痈庸雍踊蛹咏泳涌永恿勇用俑壅墉喁慵邕镛甬鳙饔',
					'You'    => '幽优悠忧尤由邮铀犹油游酉有友右佑釉诱又幼卣攸侑莠莜莸尢呦囿宥柚猷牖铕疣蚰蚴蝣鱿黝鼬',
					'Yu'     => '迂淤于盂榆虞愚舆余俞逾鱼愉渝渔隅予娱雨与屿禹宇语羽玉域芋郁遇喻峪御愈欲狱育誉浴寓裕预豫驭禺毓伛俣谀谕萸蓣揄圄圉嵛狳饫馀庾阈鬻妪妤纡瑜昱觎腴欤於煜燠聿钰鹆鹬瘐瘀窬窳蜮蝓竽臾舁雩龉',
					'Yuan'   => '鸳渊冤元垣袁原援辕园员圆猿源缘远苑愿怨院垸塬芫掾圜沅媛瑗橼爰眢鸢螈箢鼋',
					'Yue'    => '曰约越跃岳粤月悦阅龠哕瀹樾刖钺',
					'Yun'    => '耘云郧匀陨允运蕴酝晕韵孕郓芸狁恽愠纭韫殒昀氲熨筠',
					'Za'     => '匝砸杂咋拶咂',
					'Zai'    => '栽哉灾宰载再在崽甾',
					'Zan'    => '咱攒暂赞瓒昝簪糌趱錾',
					'Zang'   => '赃脏葬奘驵臧',
					'Zao'    => '遭糟凿藻枣早澡蚤躁噪造皂灶燥唣',
					'Ze'     => '责择则泽仄赜啧帻迮昃笮箦舴',
					'Zei'    => '贼',
					'Zen'    => '怎谮',
					'Zeng'   => '增憎赠缯甑罾锃',
					'Zha'    => '扎喳渣札轧铡闸眨榨乍炸诈柞揸吒咤哳楂砟痄蚱齄',
					'Zhai'   => '摘斋宅窄债寨砦瘵',
					'Zhan'   => '瞻毡詹粘沾盏斩辗崭展蘸栈占战站湛绽谵搌旃',
					'Zhang'  => '樟章彰漳张掌涨杖丈帐账仗胀瘴障仉鄣幛嶂獐嫜璋蟑',
					'Zhao'   => '招昭找沼赵照罩兆肇召诏棹钊笊',
					'Zhe'    => '遮折哲蛰辙者锗蔗这浙着乇谪摺柘辄磔鹧褶蜇螫赭',
					'Zhen'   => '珍斟真甄砧臻贞针侦枕疹诊震振镇阵帧圳蓁浈缜桢椹榛轸赈胗朕祯畛稹鸩箴',
					'Zheng'  => '蒸挣睁征狰争怔整拯正政症郑证诤峥徵钲铮筝',
					'Zhi'    => '芝枝支吱蜘知肢脂汁之织职直植殖执值侄址指止趾只旨纸志挚掷至致置帜峙制智秩稚质炙痔滞治窒卮陟郅埴芷摭帙忮彘咫骘栉枳栀桎轵轾贽胝膣祉黹雉鸷痣蛭絷酯跖踬踯豸觯',
					'Zhong'  => '中盅忠钟衷终种肿重仲众冢锺螽舯踵',
					'Zhou'   => '舟周州洲诌粥轴肘帚咒皱宙昼骤荮啁妯纣绉胄碡籀酎',
					'Zhu'    => '珠株蛛朱猪诸诛逐竹烛煮拄瞩嘱主著柱助蛀贮铸筑住注祝驻伫侏邾苎茱洙渚潴杼槠橥炷铢疰瘃竺箸舳翥躅麈',
					'Zhua'   => '抓爪',
					'Zhuai'  => '拽',
					'Zhuan'  => '专砖转撰赚篆啭馔颛',
					'Zhuang' => '幢桩庄装妆撞壮状僮',
					'Zhui'   => '椎锥追赘坠缀萑惴骓缒隹',
					'Zhun'   => '谆准肫窀',
					'Zhuo'   => '捉拙卓桌琢茁酌啄灼浊倬诼擢浞涿濯禚斫镯',
					'Zi'     => '兹咨资姿滋淄孜紫仔籽滓子自渍字谘呲嵫姊孳缁梓辎赀恣眦锱秭耔笫粢趑訾龇鲻髭',
					'Zong'   => '鬃棕踪宗综总纵偬腙粽',
					'Zou'    => '邹走奏揍诹陬鄹驺鲰',
					'Zu'     => '租足卒族祖诅阻组俎菹镞',
					'Zuan'   => '钻纂攥缵躜',
					'Zui'    => '嘴醉最罪蕞觜',
					'Zun'    => '尊遵撙樽鳟',
					'Zuo'    => '昨左佐做作坐座阼唑嘬怍胙祚酢',
				];
			default:
		}

		return $table;
	}

	/**
	 * Get test form fields.
	 *
	 * @param string $locale Current locale.
	 *
	 * @return array
	 */
	protected static function get_test_form_fields( string $locale = 'iso9' ): array {
		$form_fields = [
			'iso9'  => [
				'label'        => 'ISO9 Table',
				'section'      => 'iso9_section',
				'type'         => 'table',
				'placeholder'  => '',
				'helper'       => '',
				'supplemental' => '',
				'default'      => [ 'iso9' ],
			],
			'bel'   => [
				'label'        => 'bel Table',
				'section'      => 'bel_section',
				'type'         => 'table',
				'placeholder'  => '',
				'helper'       => '',
				'supplemental' => '',
				'default'      => [ 'bel' ],
			],
			'uk'    => [
				'label'        => 'uk Table',
				'section'      => 'uk_section',
				'type'         => 'table',
				'placeholder'  => '',
				'helper'       => '',
				'supplemental' => '',
				'default'      => [ 'uk' ],
			],
			'bg_BG' => [
				'label'        => 'bg_BG Table',
				'section'      => 'bg_BG_section',
				'type'         => 'table',
				'placeholder'  => '',
				'helper'       => '',
				'supplemental' => '',
				'default'      => [ 'bg_BG' ],
			],
			'mk_MK' => [
				'label'        => 'mk_MK Table',
				'section'      => 'mk_MK_section',
				'type'         => 'table',
				'placeholder'  => '',
				'helper'       => '',
				'supplemental' => '',
				'default'      => [ 'mk_MK' ],
			],
			'sr_RS' => [
				'label'        => 'sr_RS Table',
				'section'      => 'sr_RS_section',
				'type'         => 'table',
				'placeholder'  => '',
				'helper'       => '',
				'supplemental' => '',
				'default'      => [ 'sr_RS' ],
			],
			'el'    => [
				'label'        => 'el Table',
				'section'      => 'el_section',
				'type'         => 'table',
				'placeholder'  => '',
				'helper'       => '',
				'supplemental' => '',
				'default'      => [ 'el' ],
			],
			'hy'    => [
				'label'        => 'hy Table',
				'section'      => 'hy_section',
				'type'         => 'table',
				'placeholder'  => '',
				'helper'       => '',
				'supplemental' => '',
				'default'      => [ 'hy' ],
			],
			'ka_GE' => [
				'label'        => 'ka_GE Table',
				'section'      => 'ka_GE_section',
				'type'         => 'table',
				'placeholder'  => '',
				'helper'       => '',
				'supplemental' => '',
				'default'      => [ 'ka_GE' ],
			],
			'kk'    => [
				'label'        => 'kk Table',
				'section'      => 'kk_section',
				'type'         => 'table',
				'placeholder'  => '',
				'helper'       => '',
				'supplemental' => '',
				'default'      => [ 'kk' ],
			],
			'he_IL' => [
				'label'        => 'he_IL Table',
				'section'      => 'he_IL_section',
				'type'         => 'table',
				'placeholder'  => '',
				'helper'       => '',
				'supplemental' => '',
				'default'      => [ 'he_IL' ],
			],
			'zh_CN' => [
				'label'        => 'zh_CN Table',
				'section'      => 'zh_CN_section',
				'type'         => 'table',
				'placeholder'  => '',
				'helper'       => '',
				'supplemental' => '',
				'default'      => [ 'zh_CN' ],
			],
		];

		$locale = isset( $form_fields[ $locale ] ) ? $locale : 'iso9';

		$form_fields[ $locale ]['label'] .= '<br>(current)';

		array_walk( $form_fields, '\CyrToLat\Tests\Unit\CyrToLatTestCase::set_defaults' );

		$is_multisite = function_exists( 'is_multisite' ) && is_multisite();

		if ( ! $is_multisite ) {
			unset( $form_fields[ SettingsBase::NETWORK_WIDE ] );
		}

		return $form_fields;
	}

	/**
	 * Set default required properties for each field.
	 *
	 * @param array  $field Settings field.
	 * @param string $id    Settings field id.
	 *
	 * @return void
	 * @noinspection PhpUnusedParameterInspection
	 */
	protected static function set_defaults( array &$field, string $id ): void {
		$field = array_merge(
			[
				'default'  => '',
				'disabled' => false,
				'field_id' => '',
				'label'    => '',
				'section'  => '',
				'title'    => '',
			],
			$field
		);
	}

	/**
	 * Get test settings.
	 *
	 * @return array
	 */
	protected static function get_test_settings(): array {
		return [
			'iso9'  => [ 'iso9' ],
			'bel'   => [ 'bel' ],
			'uk'    => [ 'uk' ],
			'bg_BG' => [ 'bg_BG' ],
			'mk_MK' => [ 'mk_MK' ],
			'ka_GE' => [ 'ka_GE' ],
			'kk'    => [ 'kk' ],
		];
	}

	/**
	 * Retrieve metadata from a file.
	 *
	 * Searches for metadata in the first 8 KB of a file, such as a plugin or theme.
	 * Each piece of metadata must be on its own line. Fields can not span multiple
	 * lines, the value will get cut at the end of the first line.
	 *
	 * If the file data is not within that first 8 KB, then the author should correct
	 * their plugin file and move the data headers to the top.
	 *
	 * @link  https://codex.wordpress.org/File_Header
	 *
	 * @since 2.9.0
	 *
	 * @param string $file            Absolute path to the file.
	 * @param array  $default_headers List of headers, in the format `array( 'HeaderKey' => 'Header Name' )`.
	 * @param string $context         Optional. If specified adds filter hook {@see 'extra_$context_headers'}.
	 *                                Default empty.
	 *
	 * @return string[] Array of file header values keyed by header name.
	 */
	protected function get_file_data( string $file, array $default_headers, string $context = '' ): array {
		// We don't need to write to the file, so just open for reading.
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
		$fp = fopen( $file, 'rb' );

		if ( $fp ) {
			// Pull only the first 8 KB of the file in.
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fread
			$file_data = fread( $fp, 8 * KB_IN_BYTES );

			// PHP will close file handle, but we are good citizens.
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
			fclose( $fp );
		} else {
			$file_data = '';
		}

		// Make sure we catch CR-only line endings.
		$file_data = str_replace( "\r", "\n", $file_data );

		/**
		 * Filters extra file headers by context.
		 *
		 * The dynamic portion of the hook name, `$context`, refers to
		 * the context where extra headers might be loaded.
		 *
		 * @param array $extra_context_headers Empty array by default.
		 *
		 * @since 2.9.0
		 */
		$extra_headers = $context ? apply_filters( "extra_{$context}_headers", [] ) : [];
		if ( $extra_headers ) {
			$extra_headers = array_combine( $extra_headers, $extra_headers ); // Keys equal values.
			$all_headers   = array_merge( $extra_headers, $default_headers );
		} else {
			$all_headers = $default_headers;
		}

		foreach ( $all_headers as $field => $regex ) {
			if ( preg_match( '/^(?:[ \t]*<\?php)?[ \t\/*#@]*' . preg_quote( $regex, '/' ) . ':(.*)$/mi', $file_data, $match ) && $match[1] ) {
				$all_headers[ $field ] = $this->cleanup_header_comment( $match[1] );
			} else {
				$all_headers[ $field ] = '';
			}
		}

		return $all_headers;
	}

	/**
	 * Strip close comment and close php tags from file headers used by WP.
	 *
	 * @param string $str Header comment to clean up.
	 *
	 * @return string
	 *
	 * @see https://core.trac.wordpress.org/ticket/8497
	 */
	private function cleanup_header_comment( string $str ): string {
		return trim( preg_replace( '/\s*(?:\*\/|\?>).*/', '', $str ) );
	}
}
