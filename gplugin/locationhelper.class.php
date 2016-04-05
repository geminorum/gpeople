<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

if ( ! class_exists( 'gPluginLocationHelper' ) ) { class gPluginLocationHelper extends gPluginClassCore
{

	// NOTE: ISO 3166-1 alpha-2
	// @SOURCE: http://www.wikiwand.com/en/ISO_3166-1_alpha-2#Current_codes
	// @SEE: http://countrycode.org/
	// @SEE: https://www.iso.org/obp/ui/
	public static function getCountries()
	{
		return array(
			'AD' => 'Andorra',
			'AE' => 'United Arab Emirates',
			'AF' => 'Afghanistan',
			'AG' => 'Antigua and Barbuda',
			'AI' => 'Anguilla',
			'AL' => 'Albania',
			'AM' => 'Armenia',
			'AO' => 'Angola',
			'AQ' => 'Antarctica',
			'AR' => 'Argentina',
			'AS' => 'American Samoa',
			'AT' => 'Austria',
			'AU' => 'Australia',
			'AW' => 'Aruba',
			'AX' => 'Åland Islands',
			'AZ' => 'Azerbaijan',
			'BA' => 'Bosnia and Herzegovina',
			'BB' => 'Barbados',
			'BD' => 'Bangladesh',
			'BE' => 'Belgium',
			'BF' => 'Burkina Faso',
			'BG' => 'Bulgaria',
			'BH' => 'Bahrain',
			'BI' => 'Burundi',
			'BJ' => 'Benin',
			'BL' => 'Saint Barthélemy',
			'BM' => 'Bermuda',
			'BN' => 'Brunei Darussalam',
			'BO' => 'Bolivia, Plurinational State of',
			'BQ' => 'Bonaire, Sint Eustatius and Saba',
			'BR' => 'Brazil',
			'BS' => 'Bahamas',
			'BT' => 'Bhutan',
			'BV' => 'Bouvet Island',
			'BW' => 'Botswana',
			'BY' => 'Belarus',
			'BZ' => 'Belize',
			'CA' => 'Canada',
			'CC' => 'Cocos (Keeling) Islands',
			'CD' => 'Congo, the Democratic Republic of the',
			'CF' => 'Central African Republic',
			'CG' => 'Congo',
			'CH' => 'Switzerland',
			'CI' => 'Côte d\'Ivoire',
			'CK' => 'Cook Islands',
			'CL' => 'Chile',
			'CM' => 'Cameroon',
			'CN' => 'China',
			'CO' => 'Colombia',
			'CR' => 'Costa Rica',
			'CU' => 'Cuba',
			'CV' => 'Cabo Verde',
			'CW' => 'Curaçao',
			'CX' => 'Christmas Island',
			'CY' => 'Cyprus',
			'CZ' => 'Czech Republic',
			'DE' => 'Germany',
			'DJ' => 'Djibouti',
			'DK' => 'Denmark',
			'DM' => 'Dominica',
			'DO' => 'Dominican Republic',
			'DZ' => 'Algeria',
			'EC' => 'Ecuador',
			'EE' => 'Estonia',
			'EG' => 'Egypt',
			'EH' => 'Western Sahara',
			'ER' => 'Eritrea',
			'ES' => 'Spain',
			'ET' => 'Ethiopia',
			'FI' => 'Finland',
			'FJ' => 'Fiji',
			'FK' => 'Falkland Islands (Malvinas)',
			'FM' => 'Micronesia, Federated States of',
			'FO' => 'Faroe Islands',
			'FR' => 'France',
			'GA' => 'Gabon',
			'GB' => 'United Kingdom of Great Britain and Northern Ireland',
			'GD' => 'Grenada',
			'GE' => 'Georgia',
			'GF' => 'French Guiana',
			'GG' => 'Guernsey',
			'GH' => 'Ghana',
			'GI' => 'Gibraltar',
			'GL' => 'Greenland',
			'GM' => 'Gambia',
			'GN' => 'Guinea',
			'GP' => 'Guadeloupe',
			'GQ' => 'Equatorial Guinea',
			'GR' => 'Greece',
			'GS' => 'South Georgia and the South Sandwich Islands',
			'GT' => 'Guatemala',
			'GU' => 'Guam',
			'GW' => 'Guinea-Bissau',
			'GY' => 'Guyana',
			'HK' => 'Hong Kong',
			'HM' => 'Heard Island and McDonald Islands',
			'HN' => 'Honduras',
			'HR' => 'Croatia',
			'HT' => 'Haiti',
			'HU' => 'Hungary',
			'ID' => 'Indonesia',
			'IE' => 'Ireland',
			// 'IL' => 'Israel',
			'IM' => 'Isle of Man',
			'IN' => 'India',
			'IO' => 'British Indian Ocean Territory',
			'IQ' => 'Iraq',
			'IR' => 'Iran, Islamic Republic of',
			'IS' => 'Iceland',
			'IT' => 'Italy',
			'JE' => 'Jersey',
			'JM' => 'Jamaica',
			'JO' => 'Jordan',
			'JP' => 'Japan',
			'KE' => 'Kenya',
			'KG' => 'Kyrgyzstan',
			'KH' => 'Cambodia',
			'KI' => 'Kiribati',
			'KM' => 'Comoros',
			'KN' => 'Saint Kitts and Nevis',
			'KP' => 'Korea, Democratic People\'s Republic of',
			'KR' => 'Korea, Republic of',
			'KW' => 'Kuwait',
			'KY' => 'Cayman Islands',
			'KZ' => 'Kazakhstan',
			'LA' => 'Lao People\'s Democratic Republic',
			'LB' => 'Lebanon',
			'LC' => 'Saint Lucia',
			'LI' => 'Liechtenstein',
			'LK' => 'Sri Lanka',
			'LR' => 'Liberia',
			'LS' => 'Lesotho',
			'LT' => 'Lithuania',
			'LU' => 'Luxembourg',
			'LV' => 'Latvia',
			'LY' => 'Libya',
			'MA' => 'Morocco',
			'MC' => 'Monaco',
			'MD' => 'Moldova, Republic of',
			'ME' => 'Montenegro',
			'MF' => 'Saint Martin (French part)',
			'MG' => 'Madagascar',
			'MH' => 'Marshall Islands',
			'MK' => 'Macedonia, the former Yugoslav Republic of',
			'ML' => 'Mali',
			'MM' => 'Myanmar',
			'MN' => 'Mongolia',
			'MO' => 'Macao',
			'MP' => 'Northern Mariana Islands',
			'MQ' => 'Martinique',
			'MR' => 'Mauritania',
			'MS' => 'Montserrat',
			'MT' => 'Malta',
			'MU' => 'Mauritius',
			'MV' => 'Maldives',
			'MW' => 'Malawi',
			'MX' => 'Mexico',
			'MY' => 'Malaysia',
			'MZ' => 'Mozambique',
			'NA' => 'Namibia',
			'NC' => 'New Caledonia',
			'NE' => 'Niger',
			'NF' => 'Norfolk Island',
			'NG' => 'Nigeria',
			'NI' => 'Nicaragua',
			'NL' => 'Netherlands',
			'NO' => 'Norway',
			'NP' => 'Nepal',
			'NR' => 'Nauru',
			'NU' => 'Niue',
			'NZ' => 'New Zealand',
			'OM' => 'Oman',
			'PA' => 'Panama',
			'PE' => 'Peru',
			'PF' => 'French Polynesia',
			'PG' => 'Papua New Guinea',
			'PH' => 'Philippines',
			'PK' => 'Pakistan',
			'PL' => 'Poland',
			'PM' => 'Saint Pierre and Miquelon',
			'PN' => 'Pitcairn',
			'PR' => 'Puerto Rico',
			'PS' => 'Palestine, State of',
			'PT' => 'Portugal',
			'PW' => 'Palau',
			'PY' => 'Paraguay',
			'QA' => 'Qatar',
			'RE' => 'Réunion',
			'RO' => 'Romania',
			'RS' => 'Serbia',
			'RU' => 'Russian Federation',
			'RW' => 'Rwanda',
			'SA' => 'Saudi Arabia',
			'SB' => 'Solomon Islands',
			'SC' => 'Seychelles',
			'SD' => 'Sudan',
			'SE' => 'Sweden',
			'SG' => 'Singapore',
			'SH' => 'Saint Helena, Ascension and Tristan da Cunha',
			'SI' => 'Slovenia',
			'SJ' => 'Svalbard and Jan Mayen',
			'SK' => 'Slovakia',
			'SL' => 'Sierra Leone',
			'SM' => 'San Marino',
			'SN' => 'Senegal',
			'SO' => 'Somalia',
			'SR' => 'Suriname',
			'SS' => 'South Sudan',
			'ST' => 'Sao Tome and Principe',
			'SV' => 'El Salvador',
			'SX' => 'Sint Maarten (Dutch part)',
			'SY' => 'Syrian Arab Republic',
			'SZ' => 'Swaziland',
			'TC' => 'Turks and Caicos Islands',
			'TD' => 'Chad',
			'TF' => 'French Southern Territories',
			'TG' => 'Togo',
			'TH' => 'Thailand',
			'TJ' => 'Tajikistan',
			'TK' => 'Tokelau',
			'TL' => 'Timor-Leste',
			'TM' => 'Turkmenistan',
			'TN' => 'Tunisia',
			'TO' => 'Tonga',
			'TR' => 'Turkey',
			'TT' => 'Trinidad and Tobago',
			'TV' => 'Tuvalu',
			'TW' => 'Taiwan, Province of China',
			'TZ' => 'Tanzania, United Republic of',
			'UA' => 'Ukraine',
			'UG' => 'Uganda',
			'UM' => 'United States Minor Outlying Islands',
			'US' => 'United States of America',
			'UY' => 'Uruguay',
			'UZ' => 'Uzbekistan',
			'VA' => 'Holy See',
			'VC' => 'Saint Vincent and the Grenadines',
			'VE' => 'Venezuela, Bolivarian Republic of',
			'VG' => 'Virgin Islands, British',
			'VI' => 'Virgin Islands, U.S.',
			'VN' => 'Viet Nam',
			'VU' => 'Vanuatu',
			'WF' => 'Wallis and Futuna',
			'WS' => 'Samoa',
			'YE' => 'Yemen',
			'YT' => 'Mayotte',
			'ZA' => 'South Africa',
			'ZM' => 'Zambia',
			'ZW' => 'Zimbabwe',
		);
	}

	public static function validate_zipcode( $zip = 0, $country_code = '' )
	{
		if ( empty( $zip )
			|| empty( $country_code ) )
				return FALSE;

		$zip_regex = array(
			"AD" => "AD\d{3}",
			"AM" => "(37)?\d{4}",
			"AR" => "^([A-HJ-TP-Z]{1}\d{4}[A-Z]{3}|[a-z]{1}\d{4}[a-hj-tp-z]{3})$",
			"AS" => "96799",
			"AT" => "\d{4}",
			"AU" => "^(0[289][0-9]{2})|([1345689][0-9]{3})|(2[0-8][0-9]{2})|(290[0-9])|(291[0-4])|(7[0-4][0-9]{2})|(7[8-9][0-9]{2})$",
			"AX" => "22\d{3}",
			"AZ" => "\d{4}",
			"BA" => "\d{5}",
			"BB" => "(BB\d{5})?",
			"BD" => "\d{4}",
			"BE" => "^[1-9]{1}[0-9]{3}$",
			"BG" => "\d{4}",
			"BH" => "((1[0-2]|[2-9])\d{2})?",
			"BM" => "[A-Z]{2}[ ]?[A-Z0-9]{2}",
			"BN" => "[A-Z]{2}[ ]?\d{4}",
			"BR" => "\d{5}[\-]?\d{3}",
			"BY" => "\d{6}",
			"CA" => "^([ABCEGHJKLMNPRSTVXY]\d[ABCEGHJKLMNPRSTVWXYZ])\ {0,1}(\d[ABCEGHJKLMNPRSTVWXYZ]\d)$",
			"CC" => "6799",
			"CH" => "^[1-9][0-9][0-9][0-9]$",
			"CK" => "\d{4}",
			"CL" => "\d{7}",
			"CN" => "\d{6}",
			"CR" => "\d{4,5}|\d{3}-\d{4}",
			"CS" => "\d{5}",
			"CV" => "\d{4}",
			"CX" => "6798",
			"CY" => "\d{4}",
			"CZ" => "\d{3}[ ]?\d{2}",
			"DE" => "\b((?:0[1-46-9]\d{3})|(?:[1-357-9]\d{4})|(?:[4][0-24-9]\d{3})|(?:[6][013-9]\d{3}))\b",
			"DK" => "^([D-d][K-k])?( |-)?[1-9]{1}[0-9]{3}$",
			"DO" => "\d{5}",
			"DZ" => "\d{5}",
			"EC" => "([A-Z]\d{4}[A-Z]|(?:[A-Z]{2})?\d{6})?",
			"EE" => "\d{5}",
			"EG" => "\d{5}",
			"ES" => "^([1-9]{2}|[0-9][1-9]|[1-9][0-9])[0-9]{3}$",
			"ET" => "\d{4}",
			"FI" => "\d{5}",
			"FK" => "FIQQ 1ZZ",
			"FM" => "(9694[1-4])([ \-]\d{4})?",
			"FO" => "\d{3}",
			"FR" => "^(F-)?((2[A|B])|[0-9]{2})[0-9]{3}$",
			"GE" => "\d{4}",
			"GF" => "9[78]3\d{2}",
			"GL" => "39\d{2}",
			"GN" => "\d{3}",
			"GP" => "9[78][01]\d{2}",
			"GR" => "\d{3}[ ]?\d{2}",
			"GS" => "SIQQ 1ZZ",
			"GT" => "\d{5}",
			"GU" => "969[123]\d([ \-]\d{4})?",
			"GW" => "\d{4}",
			"HM" => "\d{4}",
			"HN" => "(?:\d{5})?",
			"HR" => "\d{5}",
			"HT" => "\d{4}",
			"HU" => "\d{4}",
			"ID" => "\d{5}",
			"IE" => "((D|DUBLIN)?([1-9]|6[wW]|1[0-8]|2[024]))?",
			"IL" => "\d{5}",
			"IN" => "^[1-9][0-9][0-9][0-9][0-9][0-9]$", //india
			"IO" => "BBND 1ZZ",
			"IQ" => "\d{5}",
			"IS" => "\d{3}",
			"IT" => "^(V-|I-)?[0-9]{5}$",
			"JO" => "\d{5}",
			"JP" => "\d{3}-\d{4}",
			"KE" => "\d{5}",
			"KG" => "\d{6}",
			"KH" => "\d{5}",
			"KR" => "\d{3}[\-]\d{3}",
			"KW" => "\d{5}",
			"KZ" => "\d{6}",
			"LA" => "\d{5}",
			"LB" => "(\d{4}([ ]?\d{4})?)?",
			"LI" => "(948[5-9])|(949[0-7])",
			"LK" => "\d{5}",
			"LR" => "\d{4}",
			"LS" => "\d{3}",
			"LT" => "\d{5}",
			"LU" => "\d{4}",
			"LV" => "\d{4}",
			"MA" => "\d{5}",
			"MC" => "980\d{2}",
			"MD" => "\d{4}",
			"ME" => "8\d{4}",
			"MG" => "\d{3}",
			"MH" => "969[67]\d([ \-]\d{4})?",
			"MK" => "\d{4}",
			"MN" => "\d{6}",
			"MP" => "9695[012]([ \-]\d{4})?",
			"MQ" => "9[78]2\d{2}",
			"MT" => "[A-Z]{3}[ ]?\d{2,4}",
			"MU" => "(\d{3}[A-Z]{2}\d{3})?",
			"MV" => "\d{5}",
			"MX" => "\d{5}",
			"MY" => "\d{5}",
			"NC" => "988\d{2}",
			"NE" => "\d{4}",
			"NF" => "2899",
			"NG" => "(\d{6})?",
			"NI" => "((\d{4}-)?\d{3}-\d{3}(-\d{1})?)?",
			"NL" => "^[1-9][0-9]{3}\s?([a-zA-Z]{2})?$",
			"NO" => "\d{4}",
			"NP" => "\d{5}",
			"NZ" => "\d{4}",
			"OM" => "(PC )?\d{3}",
			"PF" => "987\d{2}",
			"PG" => "\d{3}",
			"PH" => "\d{4}",
			"PK" => "\d{5}",
			"PL" => "\d{2}-\d{3}",
			"PM" => "9[78]5\d{2}",
			"PN" => "PCRN 1ZZ",
			"PR" => "00[679]\d{2}([ \-]\d{4})?",
			"PT" => "\d{4}([\-]\d{3})?",
			"PW" => "96940",
			"PY" => "\d{4}",
			"RE" => "9[78]4\d{2}",
			"RO" => "\d{6}",
			"RS" => "\d{6}",
			"RU" => "\d{6}",
			"SA" => "\d{5}",
			"SE" => "^(s-|S-){0,1}[0-9]{3}\s?[0-9]{2}$",
			"SG" => "\d{6}",
			"SH" => "(ASCN|STHL) 1ZZ",
			"SI" => "\d{4}",
			"SJ" => "\d{4}",
			"SK" => "\d{3}[ ]?\d{2}",
			"SM" => "4789\d",
			"SN" => "\d{5}",
			"SO" => "\d{5}",
			"SZ" => "[HLMS]\d{3}",
			"TC" => "TKCA 1ZZ",
			"TH" => "\d{5}",
			"TJ" => "\d{6}",
			"TM" => "\d{6}",
			"TN" => "\d{4}",
			"TR" => "\d{5}",
			"TW" => "\d{3}(\d{2})?",
			"UA" => "\d{5}",
			"UK" => "^(GIR|[A-Z]\d[A-Z\d]??|[A-Z]{2}\d[A-Z\d]??)[ ]??(\d[A-Z]{2})$",
			"US" => "^\d{5}([\-]?\d{4})?$",
			"UY" => "\d{5}",
			"UZ" => "\d{6}",
			"VA" => "00120",
			"VE" => "\d{4}",
			"VI" => "008(([0-4]\d)|(5[01]))([ \-]\d{4})?",
			"WF" => "986\d{2}",
			"YT" => "976\d{2}",
			"YU" => "\d{5}",
			"ZA" => "\d{4}",
			"ZM" => "\d{5}"
		);

		if ( ! isset ( $zip_regex[ $country_code ] )
			|| preg_match( "/" . $zip_regex[ $country_code ] . "/i", $zip ) )
				$ret = TRUE;

		return FALSE;
	}

	public static function get_states( $country = NULL )
	{
		if ( ! empty( $country ) && method_exists( 'gPluginLocationHelper', 'get_states_'.$country ) )
			return call_user_func( array( 'gPluginLocationHelper', 'get_states_'.$country ) );

		return array();
	}

	public static function get_cities( $country = NULL, $state = NULL )
	{
		if ( ! empty( $country ) && ! empty( $state )
			&& method_exists( 'gPluginLocationHelper', 'get_cities_'.$country ) ) {
				$cities = call_user_func( array( 'gPluginLocationHelper', 'get_cities_'.$country ) );
				if ( isset( $cities[$state] ) )
					return $cities[$state];
			}

		return array();
	}

	public static function get_states_US()
	{
		return array(
			'AL' => 'Alabama',
			'AK' => 'Alaska',
			'AZ' => 'Arizona',
			'AR' => 'Arkansas',
			'CA' => 'California',
			'CO' => 'Colorado',
			'CT' => 'Connecticut',
			'DE' => 'Delaware',
			'DC' => 'District of Columbia',
			'FL' => 'Florida',
			'GA' => 'Georgia',
			'HI' => 'Hawaii',
			'ID' => 'Idaho',
			'IL' => 'Illinois',
			'IN' => 'Indiana',
			'IA' => 'Iowa',
			'KS' => 'Kansas',
			'KY' => 'Kentucky',
			'LA' => 'Louisiana',
			'ME' => 'Maine',
			'MD' => 'Maryland',
			'MA' => 'Massachusetts',
			'MI' => 'Michigan',
			'MN' => 'Minnesota',
			'MS' => 'Mississippi',
			'MO' => 'Missouri',
			'MT' => 'Montana',
			'NE' => 'Nebraska',
			'NV' => 'Nevada',
			'NH' => 'New Hampshire',
			'NJ' => 'New Jersey',
			'NM' => 'New Mexico',
			'NY' => 'New York',
			'NC' => 'North Carolina',
			'ND' => 'North Dakota',
			'OH' => 'Ohio',
			'OK' => 'Oklahoma',
			'OR' => 'Oregon',
			'PA' => 'Pennsylvania',
			'RI' => 'Rhode Island',
			'SC' => 'South Carolina',
			'SD' => 'South Dakota',
			'TN' => 'Tennessee',
			'TX' => 'Texas',
			'UT' => 'Utah',
			'VT' => 'Vermont',
			'VA' => 'Virginia',
			'WA' => 'Washington',
			'WV' => 'West Virginia',
			'WI' => 'Wisconsin',
			'WY' => 'Wyoming',
			'AS' => 'American Samoa',
			'CZ' => 'Canal Zone',
			'CM' => 'Commonwealth of the Northern Mariana Islands',
			'FM' => 'Federated States of Micronesia',
			'GU' => 'Guam',
			'MH' => 'Marshall Islands',
			'MP' => 'Northern Mariana Islands',
			'PW' => 'Palau',
			'PI' => 'Philippine Islands',
			'PR' => 'Puerto Rico',
			'TT' => 'Trust Territory of the Pacific Islands',
			'VI' => 'Virgin Islands',
			'AA' => 'Armed Forces - Americas',
			'AE' => 'Armed Forces - Europe, Canada, Middle East, Africa',
			'AP' => 'Armed Forces - Pacific'
		);
	}

	// Canada Provinces
	public static function get_states_CA()
	{
		return array(
			'AB' => 'Alberta',
			'BC' => 'British Columbia',
			'MB' => 'Manitoba',
			'NB' => 'New Brunswick',
			'NL' => 'Newfoundland and Labrador',
			'NS' => 'Nova Scotia',
			'NT' => 'Northwest Territories',
			'NU' => 'Nunavut',
			'ON' => 'Ontario',
			'PE' => 'Prince Edward Island',
			'QC' => 'Quebec',
			'SK' => 'Saskatchewan',
			'YT' => 'Yukon',
		);
	}

	// Australian States
	public static function get_states_AU()
	{
		return array(
			'ACT' => 'Australian Capital Territory',
			'NSW' => 'New South Wales',
			'NT'  => 'Northern Territory',
			'QLD' => 'Queensland',
			'SA'  => 'South Australia',
			'TAS' => 'Tasmania',
			'VIC' => 'Victoria',
			'WA'  => 'Western Australia',
		);
	}

	// Brazil States
	public static function get_states_BR()
	{
		return array(
			'AC' => 'Acre',
			'AL' => 'Alagoas',
			'AP' => 'Amap&aacute;',
			'AM' => 'Amazonas',
			'BA' => 'Bahia',
			'CE' => 'Cear&aacute;',
			'DF' => 'Distrito Federal',
			'ES' => 'Esp&iacute;rito Santo',
			'GO' => 'Goi&aacute;s',
			'MA' => 'Maranh&atilde;o',
			'MT' => 'Mato Grosso',
			'MS' => 'Mato Grosso do Sul',
			'MG' => 'Minas Gerais',
			'PA' => 'Par&aacute;',
			'PB' => 'Para&iacute;ba',
			'PR' => 'Paran&aacute;',
			'PE' => 'Pernambuco',
			'PI' => 'Piau&iacute;',
			'RJ' => 'Rio de Janeiro',
			'RN' => 'Rio Grande do Norte',
			'RS' => 'Rio Grande do Sul',
			'RO' => 'Rond&ocirc;nia',
			'RR' => 'Roraima',
			'SC' => 'Santa Catarina',
			'SP' => 'S&atilde;o Paulo',
			'SE' => 'Sergipe',
			'TO' => 'Tocantins'
		);
	}

	// Chinese States
	public static function get_states_CN()
	{
		return array(
			'CN1'  => 'Yunnan / &#20113;&#21335;',
			'CN2'  => 'Beijing / &#21271;&#20140;',
			'CN3'  => 'Tianjin / &#22825;&#27941;',
			'CN4'  => 'Hebei / &#27827;&#21271;',
			'CN5'  => 'Shanxi / &#23665;&#35199;',
			'CN6'  => 'Inner Mongolia / &#20839;&#33945;&#21476;',
			'CN7'  => 'Liaoning / &#36797;&#23425;',
			'CN8'  => 'Jilin / &#21513;&#26519;',
			'CN9'  => 'Heilongjiang / &#40657;&#40857;&#27743;',
			'CN10' => 'Shanghai / &#19978;&#28023;',
			'CN11' => 'Jiangsu / &#27743;&#33487;',
			'CN12' => 'Zhejiang / &#27993;&#27743;',
			'CN13' => 'Anhui / &#23433;&#24509;',
			'CN14' => 'Fujian / &#31119;&#24314;',
			'CN15' => 'Jiangxi / &#27743;&#35199;',
			'CN16' => 'Shandong / &#23665;&#19996;',
			'CN17' => 'Henan / &#27827;&#21335;',
			'CN18' => 'Hubei / &#28246;&#21271;',
			'CN19' => 'Hunan / &#28246;&#21335;',
			'CN20' => 'Guangdong / &#24191;&#19996;',
			'CN21' => 'Guangxi Zhuang / &#24191;&#35199;&#22766;&#26063;',
			'CN22' => 'Hainan / &#28023;&#21335;',
			'CN23' => 'Chongqing / &#37325;&#24198;',
			'CN24' => 'Sichuan / &#22235;&#24029;',
			'CN25' => 'Guizhou / &#36149;&#24030;',
			'CN26' => 'Shaanxi / &#38485;&#35199;',
			'CN27' => 'Gansu / &#29976;&#32899;',
			'CN28' => 'Qinghai / &#38738;&#28023;',
			'CN29' => 'Ningxia Hui / &#23425;&#22799;',
			'CN30' => 'Macau / &#28595;&#38376;',
			'CN31' => 'Tibet / &#35199;&#34255;',
			'CN32' => 'Xinjiang / &#26032;&#30086;'
		);
	}

	// Hong Kong States
	public static function get_states_HK()
	{
		return array(
			'HONG KONG'       => 'Hong Kong Island',
			'KOWLOON'         => 'Kowloon',
			'NEW TERRITORIES' => 'New Territories'
		);
	}

	// Hungary States
	public static function get_states_HU()
	{
		return array(
			'BK' => 'Bács-Kiskun',
			'BE' => 'Békés',
			'BA' => 'Baranya',
			'BZ' => 'Borsod-Abaúj-Zemplén',
			'BU' => 'Budapest',
			'CS' => 'Csongrád',
			'FE' => 'Fejér',
			'GS' => 'Győr-Moson-Sopron',
			'HB' => 'Hajdú-Bihar',
			'HE' => 'Heves',
			'JN' => 'Jász-Nagykun-Szolnok',
			'KE' => 'Komárom-Esztergom',
			'NO' => 'Nógrád',
			'PE' => 'Pest',
			'SO' => 'Somogy',
			'SZ' => 'Szabolcs-Szatmár-Bereg',
			'TO' => 'Tolna',
			'VA' => 'Vas',
			'VE' => 'Veszprém',
			'ZA' => 'Zala',
		);
	}

	// Indonesian States
	public static function get_states_ID()
	{
		return array(
			'AC' => 'Daerah Istimewa Aceh',
			'SU' => 'Sumatera Utara',
			'SB' => 'Sumatera Barat',
			'RI' => 'Riau',
			'KR' => 'Kepulauan Riau',
			'JA' => 'Jambi',
			'SS' => 'Sumatera Selatan',
			'BB' => 'Bangka Belitung',
			'BE' => 'Bengkulu',
			'LA' => 'Lampung',
			'JK' => 'DKI Jakarta',
			'JB' => 'Jawa Barat',
			'BT' => 'Banten',
			'JT' => 'Jawa Tengah',
			'JI' => 'Jawa Timur',
			'YO' => 'Daerah Istimewa Yogyakarta',
			'BA' => 'Bali',
			'NB' => 'Nusa Tenggara Barat',
			'NT' => 'Nusa Tenggara Timur',
			'KB' => 'Kalimantan Barat',
			'KT' => 'Kalimantan Tengah',
			'KI' => 'Kalimantan Timur',
			'KS' => 'Kalimantan Selatan',
			'KU' => 'Kalimantan Utara',
			'SA' => 'Sulawesi Utara',
			'ST' => 'Sulawesi Tengah',
			'SG' => 'Sulawesi Tenggara',
			'SR' => 'Sulawesi Barat',
			'SN' => 'Sulawesi Selatan',
			'GO' => 'Gorontalo',
			'MA' => 'Maluku',
			'MU' => 'Maluku Utara',
			'PA' => 'Papua',
			'PB' => 'Papua Barat',
		);
	}

	// Indian States
	public static function get_states_IN()
	{
		return array(
			'AP' => 'Andra Pradesh',
			'AR' => 'Arunachal Pradesh',
			'AS' => 'Assam',
			'BR' => 'Bihar',
			'CT' => 'Chhattisgarh',
			'GA' => 'Goa',
			'GJ' => 'Gujarat',
			'HR' => 'Haryana',
			'HP' => 'Himachal Pradesh',
			'JK' => 'Jammu and Kashmir',
			'JH' => 'Jharkhand',
			'KA' => 'Karnataka',
			'KL' => 'Kerala',
			'MP' => 'Madhya Pradesh',
			'MH' => 'Maharashtra',
			'MN' => 'Manipur',
			'ML' => 'Meghalaya',
			'MZ' => 'Mizoram',
			'NL' => 'Nagaland',
			'OR' => 'Orissa',
			'PB' => 'Punjab',
			'RJ' => 'Rajasthan',
			'SK' => 'Sikkim',
			'TN' => 'Tamil Nadu',
			'TR' => 'Tripura',
			'UT' => 'Uttaranchal',
			'UP' => 'Uttar Pradesh',
			'WB' => 'West Bengal',
			'AN' => 'Andaman and Nicobar Islands',
			'CH' => 'Chandigarh',
			'DN' => 'Dadar and Nagar Haveli',
			'DD' => 'Daman and Diu',
			'DL' => 'Delhi',
			'LD' => 'Lakshadeep',
			'PY' => 'Pondicherry (Puducherry)',
		);
	}

	// Malaysian States
	public static function get_states_MY()
	{
		return array(
			'JHR' => 'Johor',
			'KDH' => 'Kedah',
			'KTN' => 'Kelantan',
			'MLK' => 'Melaka',
			'NSN' => 'Negeri Sembilan',
			'PHG' => 'Pahang',
			'PRK' => 'Perak',
			'PLS' => 'Perlis',
			'PNG' => 'Pulau Pinang',
			'SBH' => 'Sabah',
			'SWK' => 'Sarawak',
			'SGR' => 'Selangor',
			'TRG' => 'Terengganu',
			'KUL' => 'W.P. Kuala Lumpur',
			'LBN' => 'W.P. Labuan',
			'PJY' => 'W.P. Putrajaya',
		);
	}

	// New Zealand States
	public static function get_states_NZ()
	{
		return array(
			'AK' => 'Auckland',
			'BP' => 'Bay of Plenty',
			'CT' => 'Canterbury',
			'HB' => 'Hawke&rsquo;s Bay',
			'MW' => 'Manawatu-Wanganui',
			'MB' => 'Marlborough',
			'NS' => 'Nelson',
			'NL' => 'Northland',
			'OT' => 'Otago',
			'SL' => 'Southland',
			'TK' => 'Taranaki',
			'TM' => 'Tasman',
			'WA' => 'Waikato',
			'WE' => 'Wellington',
			'WC' => 'West Coast',
		);
	}

	public static function get_states_TH()
	{
		return array(
			'TH-37' => 'Amnat Charoen (&#3629;&#3635;&#3609;&#3634;&#3592;&#3648;&#3592;&#3619;&#3636;&#3597;)',
			'TH-15' => 'Ang Thong (&#3629;&#3656;&#3634;&#3591;&#3607;&#3629;&#3591;)',
			'TH-14' => 'Ayutthaya (&#3614;&#3619;&#3632;&#3609;&#3588;&#3619;&#3624;&#3619;&#3637;&#3629;&#3618;&#3640;&#3608;&#3618;&#3634;)',
			'TH-10' => 'Bangkok (&#3585;&#3619;&#3640;&#3591;&#3648;&#3607;&#3614;&#3617;&#3627;&#3634;&#3609;&#3588;&#3619;)',
			'TH-38' => 'Bueng Kan (&#3610;&#3638;&#3591;&#3585;&#3634;&#3628;)',
			'TH-31' => 'Buri Ram (&#3610;&#3640;&#3619;&#3637;&#3619;&#3633;&#3617;&#3618;&#3660;)',
			'TH-24' => 'Chachoengsao (&#3593;&#3632;&#3648;&#3594;&#3636;&#3591;&#3648;&#3607;&#3619;&#3634;)',
			'TH-18' => 'Chai Nat (&#3594;&#3633;&#3618;&#3609;&#3634;&#3607;)',
			'TH-36' => 'Chaiyaphum (&#3594;&#3633;&#3618;&#3616;&#3641;&#3617;&#3636;)',
			'TH-22' => 'Chanthaburi (&#3592;&#3633;&#3609;&#3607;&#3610;&#3640;&#3619;&#3637;)',
			'TH-50' => 'Chiang Mai (&#3648;&#3594;&#3637;&#3618;&#3591;&#3651;&#3627;&#3617;&#3656;)',
			'TH-57' => 'Chiang Rai (&#3648;&#3594;&#3637;&#3618;&#3591;&#3619;&#3634;&#3618;)',
			'TH-20' => 'Chonburi (&#3594;&#3621;&#3610;&#3640;&#3619;&#3637;)',
			'TH-86' => 'Chumphon (&#3594;&#3640;&#3617;&#3614;&#3619;)',
			'TH-46' => 'Kalasin (&#3585;&#3634;&#3628;&#3626;&#3636;&#3609;&#3608;&#3640;&#3660;)',
			'TH-62' => 'Kamphaeng Phet (&#3585;&#3635;&#3649;&#3614;&#3591;&#3648;&#3614;&#3594;&#3619;)',
			'TH-71' => 'Kanchanaburi (&#3585;&#3634;&#3597;&#3592;&#3609;&#3610;&#3640;&#3619;&#3637;)',
			'TH-40' => 'Khon Kaen (&#3586;&#3629;&#3609;&#3649;&#3585;&#3656;&#3609;)',
			'TH-81' => 'Krabi (&#3585;&#3619;&#3632;&#3610;&#3637;&#3656;)',
			'TH-52' => 'Lampang (&#3621;&#3635;&#3611;&#3634;&#3591;)',
			'TH-51' => 'Lamphun (&#3621;&#3635;&#3614;&#3641;&#3609;)',
			'TH-42' => 'Loei (&#3648;&#3621;&#3618;)',
			'TH-16' => 'Lopburi (&#3621;&#3614;&#3610;&#3640;&#3619;&#3637;)',
			'TH-58' => 'Mae Hong Son (&#3649;&#3617;&#3656;&#3630;&#3656;&#3629;&#3591;&#3626;&#3629;&#3609;)',
			'TH-44' => 'Maha Sarakham (&#3617;&#3627;&#3634;&#3626;&#3634;&#3619;&#3588;&#3634;&#3617;)',
			'TH-49' => 'Mukdahan (&#3617;&#3640;&#3585;&#3604;&#3634;&#3627;&#3634;&#3619;)',
			'TH-26' => 'Nakhon Nayok (&#3609;&#3588;&#3619;&#3609;&#3634;&#3618;&#3585;)',
			'TH-73' => 'Nakhon Pathom (&#3609;&#3588;&#3619;&#3611;&#3600;&#3617;)',
			'TH-48' => 'Nakhon Phanom (&#3609;&#3588;&#3619;&#3614;&#3609;&#3617;)',
			'TH-30' => 'Nakhon Ratchasima (&#3609;&#3588;&#3619;&#3619;&#3634;&#3594;&#3626;&#3637;&#3617;&#3634;)',
			'TH-60' => 'Nakhon Sawan (&#3609;&#3588;&#3619;&#3626;&#3623;&#3619;&#3619;&#3588;&#3660;)',
			'TH-80' => 'Nakhon Si Thammarat (&#3609;&#3588;&#3619;&#3624;&#3619;&#3637;&#3608;&#3619;&#3619;&#3617;&#3619;&#3634;&#3594;)',
			'TH-55' => 'Nan (&#3609;&#3656;&#3634;&#3609;)',
			'TH-96' => 'Narathiwat (&#3609;&#3619;&#3634;&#3608;&#3636;&#3623;&#3634;&#3626;)',
			'TH-39' => 'Nong Bua Lam Phu (&#3627;&#3609;&#3629;&#3591;&#3610;&#3633;&#3623;&#3621;&#3635;&#3616;&#3641;)',
			'TH-43' => 'Nong Khai (&#3627;&#3609;&#3629;&#3591;&#3588;&#3634;&#3618;)',
			'TH-12' => 'Nonthaburi (&#3609;&#3609;&#3607;&#3610;&#3640;&#3619;&#3637;)',
			'TH-13' => 'Pathum Thani (&#3611;&#3607;&#3640;&#3617;&#3608;&#3634;&#3609;&#3637;)',
			'TH-94' => 'Pattani (&#3611;&#3633;&#3605;&#3605;&#3634;&#3609;&#3637;)',
			'TH-82' => 'Phang Nga (&#3614;&#3633;&#3591;&#3591;&#3634;)',
			'TH-93' => 'Phatthalung (&#3614;&#3633;&#3607;&#3621;&#3640;&#3591;)',
			'TH-56' => 'Phayao (&#3614;&#3632;&#3648;&#3618;&#3634;)',
			'TH-67' => 'Phetchabun (&#3648;&#3614;&#3594;&#3619;&#3610;&#3641;&#3619;&#3603;&#3660;)',
			'TH-76' => 'Phetchaburi (&#3648;&#3614;&#3594;&#3619;&#3610;&#3640;&#3619;&#3637;)',
			'TH-66' => 'Phichit (&#3614;&#3636;&#3592;&#3636;&#3605;&#3619;)',
			'TH-65' => 'Phitsanulok (&#3614;&#3636;&#3625;&#3603;&#3640;&#3650;&#3621;&#3585;)',
			'TH-54' => 'Phrae (&#3649;&#3614;&#3619;&#3656;)',
			'TH-83' => 'Phuket (&#3616;&#3641;&#3648;&#3585;&#3655;&#3605;)',
			'TH-25' => 'Prachin Buri (&#3611;&#3619;&#3634;&#3592;&#3637;&#3609;&#3610;&#3640;&#3619;&#3637;)',
			'TH-77' => 'Prachuap Khiri Khan (&#3611;&#3619;&#3632;&#3592;&#3623;&#3610;&#3588;&#3637;&#3619;&#3637;&#3586;&#3633;&#3609;&#3608;&#3660;)',
			'TH-85' => 'Ranong (&#3619;&#3632;&#3609;&#3629;&#3591;)',
			'TH-70' => 'Ratchaburi (&#3619;&#3634;&#3594;&#3610;&#3640;&#3619;&#3637;)',
			'TH-21' => 'Rayong (&#3619;&#3632;&#3618;&#3629;&#3591;)',
			'TH-45' => 'Roi Et (&#3619;&#3657;&#3629;&#3618;&#3648;&#3629;&#3655;&#3604;)',
			'TH-27' => 'Sa Kaeo (&#3626;&#3619;&#3632;&#3649;&#3585;&#3657;&#3623;)',
			'TH-47' => 'Sakon Nakhon (&#3626;&#3585;&#3621;&#3609;&#3588;&#3619;)',
			'TH-11' => 'Samut Prakan (&#3626;&#3617;&#3640;&#3607;&#3619;&#3611;&#3619;&#3634;&#3585;&#3634;&#3619;)',
			'TH-74' => 'Samut Sakhon (&#3626;&#3617;&#3640;&#3607;&#3619;&#3626;&#3634;&#3588;&#3619;)',
			'TH-75' => 'Samut Songkhram (&#3626;&#3617;&#3640;&#3607;&#3619;&#3626;&#3591;&#3588;&#3619;&#3634;&#3617;)',
			'TH-19' => 'Saraburi (&#3626;&#3619;&#3632;&#3610;&#3640;&#3619;&#3637;)',
			'TH-91' => 'Satun (&#3626;&#3605;&#3641;&#3621;)',
			'TH-17' => 'Sing Buri (&#3626;&#3636;&#3591;&#3627;&#3660;&#3610;&#3640;&#3619;&#3637;)',
			'TH-33' => 'Sisaket (&#3624;&#3619;&#3637;&#3626;&#3632;&#3648;&#3585;&#3625;)',
			'TH-90' => 'Songkhla (&#3626;&#3591;&#3586;&#3621;&#3634;)',
			'TH-64' => 'Sukhothai (&#3626;&#3640;&#3650;&#3586;&#3607;&#3633;&#3618;)',
			'TH-72' => 'Suphan Buri (&#3626;&#3640;&#3614;&#3619;&#3619;&#3603;&#3610;&#3640;&#3619;&#3637;)',
			'TH-84' => 'Surat Thani (&#3626;&#3640;&#3619;&#3634;&#3625;&#3598;&#3619;&#3660;&#3608;&#3634;&#3609;&#3637;)',
			'TH-32' => 'Surin (&#3626;&#3640;&#3619;&#3636;&#3609;&#3607;&#3619;&#3660;)',
			'TH-63' => 'Tak (&#3605;&#3634;&#3585;)',
			'TH-92' => 'Trang (&#3605;&#3619;&#3633;&#3591;)',
			'TH-23' => 'Trat (&#3605;&#3619;&#3634;&#3604;)',
			'TH-34' => 'Ubon Ratchathani (&#3629;&#3640;&#3610;&#3621;&#3619;&#3634;&#3594;&#3608;&#3634;&#3609;&#3637;)',
			'TH-41' => 'Udon Thani (&#3629;&#3640;&#3604;&#3619;&#3608;&#3634;&#3609;&#3637;)',
			'TH-61' => 'Uthai Thani (&#3629;&#3640;&#3607;&#3633;&#3618;&#3608;&#3634;&#3609;&#3637;)',
			'TH-53' => 'Uttaradit (&#3629;&#3640;&#3605;&#3619;&#3604;&#3636;&#3605;&#3606;&#3660;)',
			'TH-95' => 'Yala (&#3618;&#3632;&#3621;&#3634;)',
			'TH-35' => 'Yasothon (&#3618;&#3650;&#3626;&#3608;&#3619;)'
		);
	}

	// South African States
	public static function get_states_ZA()
	{
		return array(
			'EC'  => 'Eastern Cape',
			'FS'  => 'Free State',
			'GP'  => 'Gauteng',
			'KZN' => 'KwaZulu-Natal',
			'LP'  => 'Limpopo',
			'MP'  => 'Mpumalanga',
			'NC'  => 'Northern Cape',
			'NW'  => 'North West',
			'WC'  => 'Western Cape',
		);
	}

	public static function get_countries( $pre = array() )
	{
		self::__dep( 'gPluginLocationHelper::getCountries()' );

		return array_merge( $pre, array(
			'US' => 'United States',
			'CA' => 'Canada',
			'GB' => 'United Kingdom',
			'AF' => 'Afghanistan',
			'AL' => 'Albania',
			'DZ' => 'Algeria',
			'AS' => 'American Samoa',
			'AD' => 'Andorra',
			'AO' => 'Angola',
			'AI' => 'Anguilla',
			'AQ' => 'Antarctica',
			'AG' => 'Antigua and Barbuda',
			'AR' => 'Argentina',
			'AM' => 'Armenia',
			'AW' => 'Aruba',
			'AU' => 'Australia',
			'AT' => 'Austria',
			'AZ' => 'Azerbaijan',
			'BS' => 'Bahamas',
			'BH' => 'Bahrain',
			'BD' => 'Bangladesh',
			'BB' => 'Barbados',
			'BY' => 'Belarus',
			'BE' => 'Belgium',
			'BZ' => 'Belize',
			'BJ' => 'Benin',
			'BM' => 'Bermuda',
			'BT' => 'Bhutan',
			'BO' => 'Bolivia',
			'BA' => 'Bosnia and Herzegovina',
			'BW' => 'Botswana',
			'BV' => 'Bouvet Island',
			'BR' => 'Brazil',
			'IO' => 'British Indian Ocean Territory',
			'BN' => 'Brunei Darrussalam',
			'BG' => 'Bulgaria',
			'BF' => 'Burkina Faso',
			'BI' => 'Burundi',
			'KH' => 'Cambodia',
			'CM' => 'Cameroon',
			'CV' => 'Cape Verde',
			'KY' => 'Cayman Islands',
			'CF' => 'Central African Republic',
			'TD' => 'Chad',
			'CL' => 'Chile',
			'CN' => 'China',
			'CX' => 'Christmas Island',
			'CC' => 'Cocos Islands',
			'CO' => 'Colombia',
			'KM' => 'Comoros',
			'CD' => 'Congo, Democratic People\'s Republic',
			'CG' => 'Congo, Republic of',
			'CK' => 'Cook Islands',
			'CR' => 'Costa Rica',
			'CI' => 'Cote d\'Ivoire',
			'HR' => 'Croatia/Hrvatska',
			'CU' => 'Cuba',
			'CY' => 'Cyprus Island',
			'CZ' => 'Czech Republic',
			'DK' => 'Denmark',
			'DJ' => 'Djibouti',
			'DM' => 'Dominica',
			'DO' => 'Dominican Republic',
			'TP' => 'East Timor',
			'EC' => 'Ecuador',
			'EG' => 'Egypt',
			'GQ' => 'Equatorial Guinea',
			'SV' => 'El Salvador',
			'ER' => 'Eritrea',
			'EE' => 'Estonia',
			'ET' => 'Ethiopia',
			'FK' => 'Falkland Islands',
			'FO' => 'Faroe Islands',
			'FJ' => 'Fiji',
			'FI' => 'Finland',
			'FR' => 'France',
			'GF' => 'French Guiana',
			'PF' => 'French Polynesia',
			'TF' => 'French Southern Territories',
			'GA' => 'Gabon',
			'GM' => 'Gambia',
			'GE' => 'Georgia',
			'DE' => 'Germany',
			'GR' => 'Greece',
			'GH' => 'Ghana',
			'GI' => 'Gibraltar',
			'GL' => 'Greenland',
			'GD' => 'Grenada',
			'GP' => 'Guadeloupe',
			'GU' => 'Guam',
			'GT' => 'Guatemala',
			'GG' => 'Guernsey',
			'GN' => 'Guinea',
			'GW' => 'Guinea-Bissau',
			'GY' => 'Guyana',
			'HT' => 'Haiti',
			'HM' => 'Heard and McDonald Islands',
			'VA' => 'Holy See (City Vatican State)',
			'HN' => 'Honduras',
			'HK' => 'Hong Kong',
			'HU' => 'Hungary',
			'IS' => 'Iceland',
			'IN' => 'India',
			'ID' => 'Indonesia',
			'IR' => 'ایران', // 'Iran',
			'IQ' => 'Iraq',
			'IE' => 'Ireland',
			'IM' => 'Isle of Man',
			// 'IL' => 'Israel',
			'IT' => 'Italy',
			'JM' => 'Jamaica',
			'JP' => 'Japan',
			'JE' => 'Jersey',
			'JO' => 'Jordan',
			'KZ' => 'Kazakhstan',
			'KE' => 'Kenya',
			'KI' => 'Kiribati',
			'KW' => 'Kuwait',
			'KG' => 'Kyrgyzstan',
			'LA' => 'Lao People\'s Democratic Republic',
			'LV' => 'Latvia',
			'LB' => 'Lebanon',
			'LS' => 'Lesotho',
			'LR' => 'Liberia',
			'LY' => 'Libyan Arab Jamahiriya',
			'LI' => 'Liechtenstein',
			'LT' => 'Lithuania',
			'LU' => 'Luxembourgh',
			'MO' => 'Macau',
			'MK' => 'Macedonia',
			'MG' => 'Madagascar',
			'MW' => 'Malawi',
			'MY' => 'Malaysia',
			'Mv' => 'Maldives',
			'ML' => 'Mali',
			'MT' => 'Malta',
			'MH' => 'Marshall Islands',
			'MQ' => 'Martinique',
			'MR' => 'Mauritania',
			'MU' => 'Mauritius',
			'YT' => 'Mayotte',
			'MX' => 'Mexico',
			'FM' => 'Micronesia',
			'MD' => 'Moldova, Republic of',
			'MC' => 'Monaco',
			'MN' => 'Mongolia',
			'ME' => 'Montenegro',
			'MS' => 'Montserrat',
			'MA' => 'Morocco',
			'MZ' => 'Mozambique',
			'MM' => 'Myanmar',
			'NA' => 'Namibia',
			'NR' => 'Nauru',
			'NP' => 'Nepal',
			'NL' => 'Netherlands',
			'AN' => 'Netherlands Antilles',
			'NC' => 'New Caledonia',
			'NZ' => 'New Zealand',
			'NI' => 'Nicaragua',
			'NE' => 'Niger',
			'NG' => 'Nigeria',
			'NU' => 'Niue',
			'NF' => 'Norfolk Island',
			'KR' => 'North Korea',
			'MP' => 'Northern Mariana Islands',
			'NO' => 'Norway',
			'OM' => 'Oman',
			'PK' => 'Pakistan',
			'PW' => 'Palau',
			'PS' => 'Palestinian Territories',
			'PA' => 'Panama',
			'PG' => 'Papua New Guinea',
			'PY' => 'Paraguay',
			'PE' => 'Peru',
			'PH' => 'Phillipines',
			'PN' => 'Pitcairn Island',
			'PL' => 'Poland',
			'PT' => 'Portugal',
			'PR' => 'Puerto Rico',
			'QA' => 'Qatar',
			'RE' => 'Reunion Island',
			'RO' => 'Romania',
			'RU' => 'Russian Federation',
			'RW' => 'Rwanda',
			'SH' => 'Saint Helena',
			'KN' => 'Saint Kitts and Nevis',
			'LC' => 'Saint Lucia',
			'PM' => 'Saint Pierre and Miquelon',
			'VC' => 'Saint Vincent and the Grenadines',
			'SM' => 'San Marino',
			'ST' => 'Sao Tome and Principe',
			'SA' => 'Saudi Arabia',
			'SN' => 'Senegal',
			'RS' => 'Serbia',
			'SC' => 'Seychelles',
			'SL' => 'Sierra Leone',
			'SG' => 'Singapore',
			'SK' => 'Slovak Republic',
			'SI' => 'Slovenia',
			'SB' => 'Solomon Islands',
			'SO' => 'Somalia',
			'ZA' => 'South Africa',
			'GS' => 'South Georgia',
			'KP' => 'South Korea',
			'ES' => 'Spain',
			'LK' => 'Sri Lanka',
			'SD' => 'Sudan',
			'SR' => 'Suriname',
			'SJ' => 'Svalbard and Jan Mayen Islands',
			'SZ' => 'Swaziland',
			'SE' => 'Sweden',
			'CH' => 'Switzerland',
			'SY' => 'Syrian Arab Republic',
			'TW' => 'Taiwan',
			'TJ' => 'Tajikistan',
			'TZ' => 'Tanzania',
			'TG' => 'Togo',
			'TK' => 'Tokelau',
			'TO' => 'Tonga',
			'TH' => 'Thailand',
			'TT' => 'Trinidad and Tobago',
			'TN' => 'Tunisia',
			'TR' => 'Turkey',
			'TM' => 'Turkmenistan',
			'TC' => 'Turks and Caicos Islands',
			'TV' => 'Tuvalu',
			'UG' => 'Uganda',
			'UA' => 'Ukraine',
			'AE' => 'United Arab Emirates',
			'UY' => 'Uruguay',
			'UM' => 'US Minor Outlying Islands',
			'UZ' => 'Uzbekistan',
			'VU' => 'Vanuatu',
			'VE' => 'Venezuela',
			'VN' => 'Vietnam',
			'VG' => 'Virgin Islands (British)',
			'VI' => 'Virgin Islands (USA)',
			'WF' => 'Wallis and Futuna Islands',
			'EH' => 'Western Sahara',
			'WS' => 'Western Samoa',
			'YE' => 'Yemen',
			'YU' => 'Yugoslavia',
			'ZM' => 'Zambia',
			'ZW' => 'Zimbabwe',
		) );
	}

	// Iran
	public static function get_states_IR()
	{
		return array(
			'TEH' => 'تهران',
			'QOM' => 'قم',
			'MKZ' => 'مرکزی',
			'QAZ' => 'قزوین',
			'GIL' => 'گیلان',
			'ARD' => 'اردبیل',
			'ZAN' => 'زنجان',
			'EAZ' => 'آذربایجان شرقی',
			'WEZ' => 'آذربایجان غربی',
			'KRD' => 'کردستان',
			'HMD' => 'همدان',
			'KRM' => 'کرمانشاه',
			'ILM' => 'ایلام',
			'LRS' => 'لرستان',
			'KZT' => 'خوزستان',
			'CMB' => 'چهار محال و بختیاری',
			'KBA' => 'کهگیلویه و بویر احمد',
			'BSH' => 'بوشهر',
			'FAR' => 'فارس',
			'HRM' => 'هرمزگان',
			'SBL' => 'سیستان و بلوچستان',
			'KRB' => 'کرمان',
			'YZD' => 'یزد',
			'EFH' => 'اصفهان',
			'SMN' => 'سمنان',
			'MZD' => 'مازندران',
			'GLS' => 'گلستان',
			'NKH' => 'خراسان شمالی',
			'RKH' => 'خراسان رضوی',
			'SKH' => 'خراسان جنوبی',
			'ALZ' => 'البرز',
		);
	}

	public static function get_next_states_IR( $state = 'TEH' )
	{
		$array = array(
			'EAZ' => array( 'WEZ', 'ARD', 'ZAN', ),
			'WEZ' => array( 'EAZ', 'ZAN', 'KRD', ),
			'ARD' => array( 'EAZ', 'ZAN', 'GIL', ),
			'EFH' => array( 'CMB', 'KZT', 'SMN', 'FAR', 'QOM', 'KBA', 'LRS', 'MKZ', 'YZD', ),
			'ALZ' => array( 'TEH', 'QAZ', 'MZD', 'MKZ', ),
			'ILM' => array( 'KZT', 'KRM', 'LRS', ),
			'BSH' => array( 'KZT', 'FAR', 'KBA', ),
			'TEH' => array( 'ALZ', 'SMN', 'QOM', 'MZD', 'MKZ', ),
			'CMB' => array( 'EFH', 'KZT', 'KBA', ),
			'SKH' => array( 'RKH', 'SBL', 'KRB', 'YZD', ),
			'RKH' => array( 'SKH', 'NKH', 'SMN', 'YZD', ),
			'NKH' => array( 'RKH', 'SMN', 'GLS', ),
			'KZT' => array( 'EFH', 'ILM', 'BSH', 'CMB', 'KBA', 'LRS', ),
			'ZAN' => array( 'EAZ', 'WEZ', 'ARD', 'QAZ', 'KRD', 'GIL', 'HMD', ),
			'SMN' => array( 'EFH', 'TEH', 'RKH', 'NKH', 'QOM', 'GLS', 'MZD', 'YZD', ),
			'SBL' => array( 'SKH', 'KRB', 'HRM', ),
			'FAR' => array( 'EFH', 'BSH', 'KRB', 'KBA', 'HRM', 'YZD', ),
			'QAZ' => array( 'ALZ', 'ZAN', 'GIL', 'MZD', 'MKZ', 'HMD', ),
			'QOM' => array( 'EFH', 'TEH', 'SMN', 'MKZ', ),
			'KRD' => array( 'WEZ', 'ZAN', 'KRM', 'HMD', ),
			'KRB' => array( 'SKH', 'SBL', 'FAR', 'HRM', 'YZD', ),
			'KRM' => array( 'ILM', 'KRD', 'LRS', 'HMD', ),
			'KBA' => array( 'EFH', 'BSH', 'CMB', 'KZT', 'FAR', ),
			'GLS' => array( 'NKH', 'SMN', 'MZD', ),
			'GIL' => array( 'ARD', 'ZAN', 'QAZ', 'MZD', ),
			'LRS' => array( 'EFH', 'ILM', 'KZT', 'KRM', 'MKZ', 'HMD', ),
			'MZD' => array( 'ALZ', 'TEH', 'SMN', 'QAZ', 'GLS', 'GIL', ),
			'MKZ' => array( 'EFH', 'ALZ', 'TEH', 'QAZ', 'QOM', 'LRS', 'HMD', ),
			'HRM' => array( 'SBL', 'FAR', 'KRB', ),
			'HMD' => array( 'ZAN', 'QAZ', 'KRD', 'KRM', 'LRS', 'MKZ', ),
			'YZD' => array( 'EFH', 'SKH', 'RKH', 'SMN', 'FAR', 'KRB', ),
		);

		if ( isset( $array[$state] ) )
			return $array[$state];

		return FALSE;
	}

	public static function get_cities_IR()
	{
		return array(
			'EAZ' => array(
				'آذرشهر',
				'اسکو',
				'اهر',
				'بستان‌آباد',
				'بناب',
				'تبریز',
				'جلفا',
				'چاراویماق',
				'سراب',
				'شبستر',
				'عجب‌شیر',
				'کلیبر',
				'مراغه',
				'مرند',
				'ملکان',
				'میانه',
				'ورزقان',
				'هریس',
				'هشترود',
			),
			'WEZ' => array(
				'ارومیه',
				'اشنویه',
				'بوکان',
				'پیرانشهر',
				'تکاب',
				'چالدران',
				'خوی',
				'سردشت',
				'سلماس',
				'شاهین‌دژ',
				'ماکو',
				'مهاباد',
				'میاندوآب',
				'نقده',
			),
			'ARD' => array(
				'اردبیل',
				'بیله‌سوار',
				'پارس‌آباد',
				'خلخال',
				'کوثر',
				'گِرمی',
				'مِشگین‌شهر',
				'نَمین',
				'نیر',
			),
			'EFH' => array(
				'آران و بیدگل',
				'اردستان',
				'اصفهان',
				'برخوار و میمه',
				'تیران و کرون',
				'چادگان',
				'خمینی‌شهر',
				'خوانسار',
				'سمیرم',
				'شهرضا',
				'سمیرم سفلی',
				'فریدن',
				'فریدون‌شهر',
				'فلاورجان',
				'کاشان',
				'گلپایگان',
				'لنجان',
				'مبارکه',
				'نائین',
				'نجف‌آباد',
				'نطنز',
			),
			'ILM' => array(
				'آبدانان',
				'ایلام',
				'ایوان',
				'دره‌شهر',
				'دهلران',
				'شیروان و چرداول',
				'مهران',
			),
			'BSH' => array(
				'بوشهر',
				'تنگستان',
				'جم',
				'دشتستان',
				'دشتی,دیر',
				'دیلم',
				'کنگان',
				'گناوه',
			),
			'TEH' => array(
				'اسلام‌شهر',
				'پاکدشت',
				'تهران',
				'دماوند',
				'رباط‌کریم',
				'ری',
				'ساوجبلاغ',
				'شمیرانات',
				'شهریار',
				'فیروزکوه',
				'کرج',
				'نظرآباد',
				'ورامین',
			),
			'CMB' => array(
				'اردل',
				'بروجن',
				'شهرکرد',
				'فارسان',
				'کوهرنگ',
				'لردگان',
			),
			'SKH' => array(
				'بیرجند',
				'درمیان',
				'سرایان',
				'سربیشه',
				'فردوس',
				'قائنات,نهبندان',
			),
			'RKH' => array(
				'بردسکن',
				'تایباد',
				'تربت جام',
				'تربت حیدریه',
				'چناران',
				'خلیل‌آباد',
				'خواف',
				'درگز',
				'رشتخوار',
				'سبزوار',
				'سرخس',
				'فریمان',
				'قوچان',
				'کاشمر',
				'کلات',
				'گناباد',
				'مشهد',
				'مه ولات',
				'نیشابور',
			 ),
			'NKH' => array(
				'اسفراین',
				'بجنورد',
				'جاجرم',
				'شیروان',
				'فاروج',
				'مانه و سملقان',
			 ),
			'KZT' => array(
				'آبادان',
				'امیدیه',
				'اندیمشک',
				'اهواز',
				'ایذه',
				'باغ‌ملک',
				'بندر ماهشهر',
				'بهبهان',
				'خرمشهر',
				'دزفول',
				'دشت آزادگان',
				'رامشیر',
				'رامهرمز',
				'شادگان',
				'شوش',
				'شوشتر',
				'گتوند',
				'لالی',
				'مسجد سلیمان',
				'هندیجان',
			 ),
			'ZAN' => array(
				'ابهر',
				'ایجرود',
				'خدابنده',
				'خرمدره',
				'زنجان',
				'طارم',
				'ماه‌نشان',
			 ),
			'SMN' => array(
				'دامغان',
				'سمنان',
				'شاهرود',
				'گرمسار',
				'مهدی‌شهر',
			),
			'SBL' => array(
				'ایرانشهر',
				'چابهار',
				'خاش',
				'دلگان',
				'زابل',
				'زاهدان',
				'زهک',
				'سراوان',
				'سرباز',
				'کنارک',
				'نیک‌شهر',
			),
			'FAR' => array(
				'آباده',
				'ارسنجان',
				'استهبان',
				'اقلید',
				'بوانات',
				'پاسارگاد',
				'جهرم',
				'خرم‌بید',
				'خنج',
				'داراب',
				'زرین‌دشت',
				'سپیدان',
				'شیراز',
				'فراشبند',
				'فسا',
				'فیروزآباد',
				'قیر و کارزین',
				'کازرون',
				'لارستان',
				'لامِرد',
				'مرودشت',
				'ممسنی',
				'مهر',
				'نی‌ریز',
				 ),
			'QAZ' => array(
				'آبیک',
				'البرز',
				'بوئین‌زهرا',
				'تاکستان',
				'قزوین',
			),
			'QOM' => array(
				'قم',
			),
			'KRD' => array(
				'بانه',
				'بیجار',
				'دیواندره',
				'سروآباد',
				'سقز',
				'سنندج',
				'قروه',
				'کامیاران',
				'مریوان',
			),
			'KRB' => array(
				'بافت',
				'بردسیر',
				'بم',
				'جیرفت',
				'راور',
				'رفسنجان',
				'رودبار جنوب',
				'زرند',
				'سیرجان',
				'شهر بابک',
				'عنبرآباد',
				'قلعه گنج',
				'کرمان',
				'کوهبنان',
				'کهنوج',
				'منوجان',
			),
			'KRM' => array(
				'اسلام‌آباد غرب',
				'پاوه',
				'ثلاث باباجانی',
				'جوانرود',
				'دالاهو',
				'روانسر',
				'سرپل ذهاب',
				'سنقر',
				'صحنه',
				'قصر شیرین',
				'کرمانشاه',
				'کنگاور',
				'گیلان غرب',
				'هرسین',
			),
			'KBA' => array(
				'بویراحمد',
				'بهمئی',
				'دنا',
				'کهگیلویه',
				'گچساران',
			),
			'GLS' => array(
				'آزادشهر',
				'آق‌قلا',
				'بندر گز',
				'ترکمن',
				'رامیان',
				'علی‌آباد',
				'کردکوی',
				'کلاله',
				'گرگان',
				'گنبد کاووس',
				'مراوه‌تپه',
				'مینودشت',
			),
			'GIL' => array(
				'آستارا',
				'آستانه اشرفیه',
				'اَملَش',
				'بندر انزلی',
				'رشت',
				'رضوانشهر',
				'رودبار',
				'رودسر',
				'سیاهکل',
				'شَفت',
				'صومعه‌سرا',
				'طوالش',
				'فومَن',
				'لاهیجان',
				'لنگرود',
				'ماسال',
			),
			'LRS' => array(
				'ازنا',
				'الیگودرز',
				'بروجرد',
				'پل‌دختر',
				'خرم‌آباد',
				'دورود',
				'دلفان',
				'سلسله',
				'کوهدشت',
			),
			'MZD' => array(
				'آمل',
				'بابل',
				'بابلسر',
				'بهشهر',
				'تنکابن',
				'جویبار',
				'چالوس',
				'رامسر',
				'ساری',
				'سوادکوه',
				'قائم‌شهر',
				'گلوگاه',
				'محمودآباد',
				'نکا',
				'نور',
				'نوشهر',
			),
			'MKZ' => array(
				'آشتیان',
				'اراک',
				'تفرش',
				'خمین',
				'دلیجان',
				'زرندیه',
				'ساوه',
				'شازند',
				'کمیجان',
				'محلات',
			),
			'HRM' => array(
				'ابوموسی',
				'بستک',
				'بندر عباس',
				'بندر لنگه',
				'جاسک',
				'حاجی‌آباد',
				'شهرستان خمیر',
				'رودان ',
				'قشم',
				'گاوبندی',
				'میناب',
			),
			'HMD' => array(
				'اسدآباد',
				'بهار',
				'تویسرکان',
				'رزن',
				'کبودرآهنگ',
				'ملایر',
				'نهاوند',
				'همدان',
			),
			'YZD' => array(
				'ابرکوه',
				'اردکان',
				'بافق',
				'تفت',
				'خاتم',
				'صدوق',
				'طبس',
				'مهریز',
				'مِیبُد',
				'یزد',
			),
			'ALZ' => array(
				'کرج',
				'ساوجبلاغ',
				'نظرآباد',
				'طالقان',
				'اشتهارد',
				'فردیس',
			),
		);
	}

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
/// NOT USED YET ---------------------------------------------------------------

	// Geo-Toolkit for PHP: https://github.com/jsor/geokit

	// http://ip.codehelper.io/
	// http://stackoverflow.com/a/7767055
	// http://ipinfo.io/developers
	// http://jsfiddle.net/zK5FN/2/

	// https://gist.github.com/phpdistiller/8067330
	// http://www.catswhocode.com/blog/snippets/detect-location-by-ip
	// http://ipinfodb.com/
	// http://ipinfodb.com/ip_location_api.php

	// ALSO SEE : https://github.com/BeingTomGreen/IP-User-Location

	function get_location_by_ip( $ip )
	{
		if ( ! is_string( $ip )
			|| strlen( $ip ) < 1
			|| $ip == '127.0.0.1'
			|| $ip == 'localhost' )
				$ip = '8.8.8.8';

		$ch = curl_init();

		curl_setopt_array( $ch, array(
			CURLOPT_FOLLOWLOCATION => 1,
			CURLOPT_HEADER         => 0,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.2) Gecko/20100115 Firefox/3.6 (.NET CLR 3.5.30729)',
			CURLOPT_URL            => 'http://ipinfodb.com/ip_locator.php?ip='.urlencode( $ip ),
			CURLOPT_TIMEOUT        => 1,
			CURLOPT_REFERER        => 'http://'.$_SERVER['HTTP_HOST'],
		) );

		$content = curl_exec( $ch );

		// if ( ! is_null( $curl_info ) )
		// 	$curl_info = curl_getinfo( $ch );

		curl_close( $ch );

		if ( preg_match( '{<li>City : ([^<]*)</li>}i', $content, $regs ) )
			$city = $regs[1];

		if ( preg_match( '{<li>State/Province : ([^<]*)</li>}i', $content, $regs ) )
			$state = $regs[1];

		if ( preg_match( '{<li>Country : ([^<]*)</li>}i', $content, $regs ) )
			$country = $regs[1];

		if ( $city && $state && $country )
			return array( $city, $state, preg_replace( '/<img[^>]+\>/i', '', $country ) );

		return FALSE;
	}

	// http://www.catswhocode.com/blog/snippets/calculate-the-distance-between-two-points-in-php
	// http://www.inkplant.com/code/calculate-the-distance-between-two-points.php
	/*
		Usage:
		$point1 = array('lat' => 40.770623, 'long' => -73.964367);
		$point2 = array('lat' => 40.758224, 'long' => -73.917404);
		$distance = getDistanceBetweenPointsNew($point1['lat'], $point1['long'], $point2['lat'], $point2['long']);
		foreach ($distance as $unit => $value) {
			echo $unit.': '.number_format($value,4).'<br />';
		}
	*/
	function getDistanceBetweenPointsNew( $latitude1, $longitude1, $latitude2, $longitude2 )
	{
		$theta      = $longitude1 - $longitude2;
		$miles      = (sin(deg2rad($latitude1)) * sin(deg2rad($latitude2))) + (cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * cos(deg2rad($theta)));
		$miles      = acos($miles);
		$miles      = rad2deg($miles);
		$miles      = $miles * 60 * 1.1515;
		$feet       = $miles * 5280;
		$yards      = $feet / 3;
		$kilometers = $miles * 1.609344;
		$meters     = $kilometers * 1000;

		return compact( 'miles','feet','yards','kilometers','meters' );
	}

	// SEE : http://en.wikipedia.org/wiki/Haversine_formula
	// http://www.codecodex.com/wiki/Calculate_Distance_Between_Two_Points_on_a_Globe
	// http://www.movable-type.co.uk/scripts/latlong.html

	// SEE : https://developers.google.com/maps/articles/phpsqlsearch_v3
	// SEE: http://www.geodatasource.com/world-cities-database/free

	// http://www.geodatasource.com/developers/php
	/*::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::*/
	/*::                                                                         :*/
	/*::  This routine calculates the distance between two points (given the     :*/
	/*::  latitude/longitude of those points). It is being used to calculate     :*/
	/*::  the distance between two locations using GeoDataSource(TM) Products    :*/
	/*::                     													 :*/
	/*::  Definitions:                                                           :*/
	/*::    South latitudes are negative, east longitudes are positive           :*/
	/*::                                                                         :*/
	/*::  Passed to function:                                                    :*/
	/*::    lat1, lon1 = Latitude and Longitude of point 1 (in decimal degrees)  :*/
	/*::    lat2, lon2 = Latitude and Longitude of point 2 (in decimal degrees)  :*/
	/*::    unit = the unit you desire for results                               :*/
	/*::           where: 'M' is statute miles                                   :*/
	/*::                  'K' is kilometers (default)                            :*/
	/*::                  'N' is nautical miles                                  :*/
	/*::  Worldwide cities and other features databases with latitude longitude  :*/
	/*::  are available at http://www.geodatasource.com                          :*/
	/*::                                                                         :*/
	/*::  For enquiries, please contact sales@geodatasource.com                  :*/
	/*::                                                                         :*/
	/*::  Official Web site: http://www.geodatasource.com                        :*/
	/*::                                                                         :*/
	/*::         GeoDataSource.com (C) All Rights Reserved 2013		   		     :*/
	/*::                                                                         :*/
	/*::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::*/

	// echo distance(32.9697, -96.80322, 29.46786, -98.53506, "M") . " Miles<br>";
	// echo distance(32.9697, -96.80322, 29.46786, -98.53506, "K") . " Kilometers<br>";
	// echo distance(32.9697, -96.80322, 29.46786, -98.53506, "N") . " Nautical Miles<br>";

	function distance( $lat1, $lon1, $lat2, $lon2, $unit = 'K' )
	{

		$theta = $lon1 - $lon2;
		$dist  = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
		$dist  = acos($dist);
		$dist  = rad2deg($dist);
		$miles = $dist * 60 * 1.1515;
		$unit  = strtoupper($unit);

		if ( $unit == "K" )
			return ( $miles * 1.609344 );

		if ( $unit == "N" )
			return ( $miles * 0.8684 );

		return $miles;
	}

	// http://stackoverflow.com/a/9046008
	// http://en.wikipedia.org/wiki/Geographical_distance
	// echo distanceGeoPoints(22,50,22.1,50.1);
	function distanceGeoPoints( $lat1, $lng1, $lat2, $lng2 )
	{
		$earthRadius = 3958.75;

		$dLat = deg2rad($lat2-$lat1);
		$dLng = deg2rad($lng2-$lng1);


		$a = sin($dLat/2) * sin($dLat/2) +
		   cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
		   sin($dLng/2) * sin($dLng/2);
		$c = 2 * atan2(sqrt($a), sqrt(1-$a));
		$dist = $earthRadius * $c;

		// from miles
		$meterConversion = 1609;
		$geopointDistance = $dist * $meterConversion;

		return $geopointDistance;
	}
} }
