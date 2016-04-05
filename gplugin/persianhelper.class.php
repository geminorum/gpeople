<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

if ( ! class_exists( 'gPluginPersianHelper' ) ) { class gPluginPersianHelper extends gPluginClassCore
{

	public static function l10n( $html, $strip = FALSE )
	{
		return self::do_string( ( $strip ? trim( strip_tags( $html ) ) : $html ) );
	}

	public static function do_string( $text )
	{
		if ( is_null( $text ) )
			return NULL;

		$pairs = array(
			'0' => chr(0xDB).chr(0xB0),
			'1' => chr(0xDB).chr(0xB1),
			'2' => chr(0xDB).chr(0xB2),
			'3' => chr(0xDB).chr(0xB3),
			'4' => chr(0xDB).chr(0xB4),
			'5' => chr(0xDB).chr(0xB5),
			'6' => chr(0xDB).chr(0xB6),
			'7' => chr(0xDB).chr(0xB7),
			'8' => chr(0xDB).chr(0xB8),
			'9' => chr(0xDB).chr(0xB9),

			chr(0xD9).chr(0xA0) => chr(0xDB).chr(0xB0),
			chr(0xD9).chr(0xA1) => chr(0xDB).chr(0xB1),
			chr(0xD9).chr(0xA2) => chr(0xDB).chr(0xB2),
			chr(0xD9).chr(0xA3) => chr(0xDB).chr(0xB3),
			chr(0xD9).chr(0xA4) => chr(0xDB).chr(0xB4),
			chr(0xD9).chr(0xA5) => chr(0xDB).chr(0xB5),
			chr(0xD9).chr(0xA6) => chr(0xDB).chr(0xB6),
			chr(0xD9).chr(0xA7) => chr(0xDB).chr(0xB7),
			chr(0xD9).chr(0xA8) => chr(0xDB).chr(0xB8),
			chr(0xD9).chr(0xA9) => chr(0xDB).chr(0xB9),
			chr(0xD9).chr(0x83) => chr(0xDA).chr(0xA9),
			chr(0xD9).chr(0x89) => chr(0xDB).chr(0x8C),
			chr(0xD9).chr(0x8A) => chr(0xDB).chr(0x8C),
			chr(0xDB).chr(0x80) => chr(0xD9).chr(0x87).chr(0xD9).chr(0x94)
		);

		return strtr( $text, $pairs );
	}

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
/// NOT USED YET ---------------------------------------------------------------

	// get unicode char by its code
	// http://php.net/manual/en/function.chr.php#88611
	public static function unichr( $u )
	{
		return mb_convert_encoding( '&#'.intval( $u ).';', 'UTF-8', 'HTML-ENTITIES' );
	}

	public static function fa_alphabet()
	{
		return array(
			self::unichr( 0622 ) => 'الف',
		);
	}

	// http://stackoverflow.com/a/23457484
	public static function ordinal($str)
	{
		$charString = mb_substr($str, 0, 1, 'utf-8');
		$size = strlen($charString);
		$ordinal = ord($charString[0]) & (0xFF >> $size);

		//Merge other characters into the value
		for($i = 1; $i < $size; $i++ )
			$ordinal = $ordinal << 6 | (ord($charString[$i]) & 127);

		return $ordinal;
	}

	// http://stackoverflow.com/questions/4764244/tinymce-blank-content-on-ajax-form-submit
	// http://wordpress.stackexchange.com/questions/73257/bridging-tinymce-js-and-wordpress-php
	// http://devwp.eu/overcome-the-wordpress-autosave-limitations/
	// http://stackoverflow.com/questions/14468129/using-jquerys-ajax-functionality-within-wordpress-tinymce-editor

	// http://wordpress.org/plugins/sem-autolink-uri/

	var $i = 0;
	var $urls = array();

	var $persian_numbers = "۱۲۳۴۵۶۷۸۹۰";
	var $arabic_numbers  = "١٢٣٤٥٦٧٨٩٠";
	var $english_numbers = "1234567890";
	var $bad_chars  = ",;كي%";
	var $good_chars = "،؛کی٪";

	function en_fa()
	{
		return array(
			'0' => chr(0xDB).chr(0xB0),
			'1' => chr(0xDB).chr(0xB1),
			'2' => chr(0xDB).chr(0xB2),
			'3' => chr(0xDB).chr(0xB3),
			'4' => chr(0xDB).chr(0xB4),
			'5' => chr(0xDB).chr(0xB5),
			'6' => chr(0xDB).chr(0xB6),
			'7' => chr(0xDB).chr(0xB7),
			'8' => chr(0xDB).chr(0xB8),
			'9' => chr(0xDB).chr(0xB9),
		);
	}

	function ar_fa()
	{
		return array(
			chr(0xD9).chr(0xA0) => chr(0xDB).chr(0xB0),
			chr(0xD9).chr(0xA1) => chr(0xDB).chr(0xB1),
			chr(0xD9).chr(0xA2) => chr(0xDB).chr(0xB2),
			chr(0xD9).chr(0xA3) => chr(0xDB).chr(0xB3),
			chr(0xD9).chr(0xA4) => chr(0xDB).chr(0xB4),
			chr(0xD9).chr(0xA5) => chr(0xDB).chr(0xB5),
			chr(0xD9).chr(0xA6) => chr(0xDB).chr(0xB6),
			chr(0xD9).chr(0xA7) => chr(0xDB).chr(0xB7),
			chr(0xD9).chr(0xA8) => chr(0xDB).chr(0xB8),
			chr(0xD9).chr(0xA9) => chr(0xDB).chr(0xB9),

			chr(0xD9).chr(0x83) => chr(0xDA).chr(0xA9),
			chr(0xD9).chr(0x89) => chr(0xDB).chr(0x8C),
			chr(0xD9).chr(0x8A) => chr(0xDB).chr(0x8C),
			chr(0xDB).chr(0x80) => chr(0xD9).chr(0x87).chr(0xD9).chr(0x94),

			'%' => '٪',
			';' => '؛',
			',' => '،',
		);
	}

	// modified version of WP core : make_clickable()
	function html_cleanup( $text )
	{
		$r = '';
		$textarr = preg_split( '/(<[^<>]+>)/', $text, -1, PREG_SPLIT_DELIM_CAPTURE ); // split out HTML tags
		foreach ( $textarr as $piece ) {
			if ( empty( $piece ) || ( $piece[0] == '<' && ! preg_match( '|^<\s*[\w]{1,20}+://|', $piece ) ) ) {
				$r .= $piece;
				continue;
			}

			// Long strings might contain expensive edge cases ...
			if ( 10000 < strlen( $piece ) ) {
				// ... break it up
				foreach ( $this->_split_str_by_whitespace( $piece, 2100 ) as $chunk ) { // 2100: Extra room for scheme and leading and trailing paretheses
					if ( 2101 < strlen( $chunk ) ) {
						$r .= $chunk; // Too big, no whitespace: bail.
					} else {
						//$r .= make_clickable( $chunk );
						$r .= $this->html_cleanup( $chunk );
					}
				}
			} else {

				$ret = " $piece "; // Pad with whitespace to simplify the regexes

				$url_replace = '~
					([\\s(<.,;:!?])                                        # 1: Leading whitespace, or punctuation
					(                                                      # 2: URL
						[\\w]{1,20}+://                                # Scheme and hier-part prefix
						(?=\S{1,2000}\s)                               # Limit to URLs less than about 2000 characters long
						[\\w\\x80-\\xff#%\\~/@\\[\\]*(+=&$-]*+         # Non-punctuation URL character
						(?:                                            # Unroll the Loop: Only allow puctuation URL character if followed by a non-punctuation URL character
							[\'.,;:!?)]                            # Punctuation URL character
							[\\w\\x80-\\xff#%\\~/@\\[\\]*(+=&$-]++ # Non-punctuation URL character
						)*
					)
					(\)?)                                                  # 3: Trailing closing parenthesis (for parethesis balancing post processing)
				~xS'; // The regex is a non-anchored pattern and does not have a single fixed starting character.
					  // Tell PCRE to spend more time optimizing since, when used on a page load, it will probably be used several times.

				$ret = preg_replace_callback( $url_replace, array( $this, 'repelace_url' ), $ret );
				$ret = $this->cleanup( $ret, FALSE );
				//$ret = preg_replace_callback( $url_re_replace, array( $this, 're_repelace_url' ), $ret );
				$ret = $this->re_repelace_urls( $ret );

				//$ret = preg_replace_callback( '#([\s>])((www|ftp)\.[\w\\x80-\\xff\#$%&~/.\-;:=,?@\[\]+]+)#is', '_make_web_ftp_clickable_cb', $ret );
				//$ret = preg_replace_callback( '#([\s>])([.0-9a-z_+-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,})#i', '_make_email_clickable_cb', $ret );

				$ret = substr( $ret, 1, -1 ); // Remove our whitespace padding.
				$r .= $ret;
			}
		}

		// Cleanup of accidental links within links
		$r = preg_replace( '#(<a( [^>]+?>|>))<a [^>]+?>([^>]+?)</a></a>#i', "$1$3</a>", $r );
		return $r;

	}

	// based on : https://github.com/aziz/virastar | http://virastar.heroku.com/
	function cleanup( $text, $urls = TRUE )
	{

		//gPluginUtils::dump( $matches ); die();

		// it's a joke!? right? :D
		# removing URLS bringing them back at the end of process
		// $text = preg_replace_callback( '/https?:\/\/([-\w\.]+)+(:\d+)?(\/([\w\/_\.]*(\?\S+)?)?)?/', array( $this, 'repelace_url' ), $text );

		# replace double dash to ndash and triple dash to mdash
		$text = preg_replace( '/-{3}/', '—', $text );
		$text = preg_replace( '/-{2}/', '–', $text );

		# replace three dots with ellipsis
		$text = preg_replace( '/\s*\.{3,}/', '…', $text );

		# replace English quotes with their Persian equivalent
		$text = preg_replace( '/(["\'`]+)(.+?)(\1)/u', '«$2»', $text );

		# should convert ه ی to ه
		$text = preg_replace( '/(\S)(ه[\s‌]+[یي])(\s)/u', '$1هٔ$3', $text );

		# remove unnecessary zwnj char that are succeeded/preceded by a space
		$text = preg_replace( '/\s+‌|‌\s+/u', ' ', $text );
		//$text = preg_replace( '/\s\s+/', ' ', $text ); // from php manual

		# character replacement
		$text = strtr( $text, $this->en_fa() );
		$text = strtr( $text, $this->ar_fa() );

		# should not replace exnglish chars in english phrases
		//$text = preg_replace_callback( '/([a-z\-_]{2,}[۰-۹]+|[۰-۹]+[a-z\-_]{2,})/i', array( $this, 'repelace_character' ), $text );

		# put zwnj between word and prefix (mi* nemi*)
		# there's a possible bug here: می and نمی could be separate nouns and not prefix
		$text = preg_replace( '/\s+(ن?می)\s+/u', ' $1‌', $text );

		# put zwnj between word and suffix (*tar *tarin *ha *haye)
		# there's a possible bug here: های and تر could be separate nouns and not suffix
		# in case you can not read it: \s+(tar(i(n)?)?|ha(ye)?)\s+
		$text = preg_replace( '/\s+(تر(ی(ن)?)?|ها(ی)?)\s+/u', '‌$1 ', $text );

		# replace more than one ! or ? mark with just one
		$text = preg_replace( '/(!){2,}/u', '$1', $text );
		$text = preg_replace( '/(؟){2,}/u', '$1', $text );

		# should remove all kashida
		$text = preg_replace( '/ـ+/u', "", $text );

		# should fix outside and inside spacing for () [] {}  “” «»
		$text = preg_replace( '/[   ‌]*(\()\s*([^)]+?)\s*?(\))[   ‌]*/u', ' $1$2$3 ', $text );
		$text = preg_replace( '/[   ‌]*(\[)\s*([^)]+?)\s*?(\])[   ‌]*/u', ' $1$2$3 ', $text );
		$text = preg_replace( '/[   ‌]*(\{)\s*([^)]+?)\s*?(\})[   ‌]*/u', ' $1$2$3 ', $text );
		$text = preg_replace( '/[   ‌]*(“)\s*([^)]+?)\s*?(”)[   ‌]*/u', ' $1$2$3 ', $text );
		$text = preg_replace( '/[   ‌]*(«)\s*([^)]+?)\s*?(»)[   ‌]*/u', ' $1$2$3 ', $text );

		# : ; , . ! ? and their persian equivalents should have one space after and no space before
		$text = preg_replace( '/[ ‌  ]*([:;,؛،.؟!]{1})[ ‌  ]*/u', '$1 ', $text );

		# do not put space after colon that separates time parts
		$text = preg_replace( '/([۰-۹]+):\s+([۰-۹]+)/u', '$1:$2', $text );

		# should fix inside spacing for () [] {}  “” «»
		$text = preg_replace( '/(\()\s*([^)]+?)\s*?(\))/u', '$1$2$3', $text );
		$text = preg_replace( '/(\[)\s*([^)]+?)\s*?(\])/u', '$1$2$3', $text );
		$text = preg_replace( '/(\{)\s*([^)]+?)\s*?(\})/u', '$1$2$3', $text );
		$text = preg_replace( '/(“)\s*([^)]+?)\s*?(”)/u', '$1$2$3', $text );
		$text = preg_replace( '/(«)\s*([^)]+?)\s*?(»)/u', '$1$2$3', $text );

		# should replace more than one space with just a single one
		$text = preg_replace( '/[ ]+/u', ' ', $text );
		$text = preg_replace( '/([\n]+)[   ‌]*/u', '$1', $text );

		# remove spaces, tabs, and new lines from the beginning and enf of file
		// $text = trim( $text ); // interfere with html_cleanup

	  /*

	  # removing URLS bringing them back at the end of process
	  urls = []
	  i = 0
	  text.gsub!(/https?:\/\/([-\w\.]+)+(:\d+)?(\/([\w\/_\.]*(\?\S+)?)?)?/) do |s|
		urls[i] = s.dup
		i += 1
		"__urls__#{i}__"
	  end


	  # bringing back urls
	  text.gsub!(/__urls__\d+__/) do |s|
		urls[s.split("__").last.to_i - 1]
	  end

	*/

		return $text;
	}

	function repelace_character( $matches )
	{
		return strtr( $text, $this->persian_numbers, $this->english_numbers );
	}

	function re_repelace_urls( $text )
	{
		if ( count( $this->urls ) )
			foreach ( $this->urls as $i => $url )
				$text = preg_replace( '/{__URL__'.$i.'__}/', $url, $text );

		$this->urls = array();
		return $text;
	}

	// modified version of WP core : _make_url_clickable_cb()
	function repelace_url( $matches )
	{
		$url = $matches[2];

		if ( ')' == $matches[3] && strpos( $url, '(' ) ) {
			// If the trailing character is a closing parethesis, and the URL has an opening parenthesis in it, add the closing parenthesis to the URL.
			// Then we can let the parenthesis balancer do its thing below.
			$url .= $matches[3];
			$suffix = '';
		} else {
			$suffix = $matches[3];
		}

		// Include parentheses in the URL only if paired
		while ( substr_count( $url, '(' ) < substr_count( $url, ')' ) ) {
			$suffix = strrchr( $url, ')' ) . $suffix;
			$url = substr( $url, 0, strrpos( $url, ')' ) );
		}

		$url = esc_url($url);
		if ( empty($url) )
			return $matches[0];

		//return $matches[1] . "<a href=\"$url\" rel=\"nofollow\">$url</a>" . $suffix;

		$this->urls[$this->i] = $matches[1].$url.$suffix;
		$key = '{__URL__'.$this->i.'__}';
		$this->i++;
		return $key;
	}


	// WP core exact function :

	/**
	 * Breaks a string into chunks by splitting at whitespace characters.
	 * The length of each returned chunk is as close to the specified length goal as possible,
	 * with the caveat that each chunk includes its trailing delimiter.
	 * Chunks longer than the goal are guaranteed to not have any inner whitespace.
	 *
	 * Joining the returned chunks with empty delimiters reconstructs the input string losslessly.
	 *
	 * Input string must have no NULL characters (or eventual transformations on output chunks must not care about NULL characters)
	 *
	 * <code>
	 * _split_str_by_whitespace( "1234 67890 1234 67890a cd 1234   890 123456789 1234567890a    45678   1 3 5 7 90 ", 10 ) ==
	 * array (
	 *   0 => '1234 67890 ',  // 11 characters: Perfect split
	 *   1 => '1234 ',        //  5 characters: '1234 67890a' was too long
	 *   2 => '67890a cd ',   // 10 characters: '67890a cd 1234' was too long
	 *   3 => '1234   890 ',  // 11 characters: Perfect split
	 *   4 => '123456789 ',   // 10 characters: '123456789 1234567890a' was too long
	 *   5 => '1234567890a ', // 12 characters: Too long, but no inner whitespace on which to split
	 *   6 => '   45678   ',  // 11 characters: Perfect split
	 *   7 => '1 3 5 7 9',    //  9 characters: End of $string
	 * );
	 * </code>
	 *
	 * @since 3.4.0
	 * @access private
	 *
	 * @param string $string The string to split.
	 * @param int $goal The desired chunk length.
	 * @return array Numeric array of chunks.
	 */
	function _split_str_by_whitespace( $string, $goal ) {
		$chunks = array();

		$string_nullspace = strtr( $string, "\r\n\t\v\f ", "\000\000\000\000\000\000" );

		while ( $goal < strlen( $string_nullspace ) ) {
			$pos = strrpos( substr( $string_nullspace, 0, $goal + 1 ), "\000" );

			if ( FALSE === $pos ) {
				$pos = strpos( $string_nullspace, "\000", $goal + 1 );
				if ( FALSE === $pos ) {
					break;
				}
			}

			$chunks[] = substr( $string, 0, $pos + 1 );
			$string = substr( $string, $pos + 1 );
			$string_nullspace = substr( $string_nullspace, $pos + 1 );
		}

		if ( $string ) {
			$chunks[] = $string;
		}

		return $chunks;
	}


	// https://groups.google.com/d/msg/persian-computing/UjtEQjyLjfY/NuZfFziU08wJ
	// I have a text that contains characters from the Arabic presentation form (U+FE70 - U+FEFF) like ﺵﺝﺭ
	// Does any body have a PHP code that converts this to a normal UTF-8 text?
	function purify_value( $v )
	{
		if ( $v < 0xFE70 )
			return $v;

		if ( $v < 0xFE8F )
			return $v;

		$cv_table = array(
			0xFE92 => 0x628,
			0xFE94 => 0x629,
			0xFE98 => 0x62A,
			0xFE9C => 0x62B,
			0xFEA0 => 0x62C,
			0xFEA4 => 0x62D,
			0xFEA8 => 0x62E,
			0xFEAA => 0x62F,
			0xFEAC => 0x630,
			0xFEAE => 0x631,
			0xFEB0 => 0x632,
			0xFEB4 => 0x633,
			0xFEB8 => 0x634,
			0xFEBC => 0x635,
			0xFEC0 => 0x636,
			0xFEC4 => 0x637,
			0xFEC8 => 0x638,
			0xFECC => 0x639,
			0xFED0 => 0x63A,
			0xFED4 => 0x641,
			0xFED8 => 0x642,
			0xFEDC => 0x6a9,
			0xFEE0 => 0x644,
			0xFEE4 => 0x645,
			0xFEE8 => 0x646,
			0xFEEC => 0x647,
			0xFEEE => 0x648,
			0xFEF4 => 0x649
		);

		foreach ( $cv_table as $fr => $t )
			if ( $v <= $fr )
				return $t;
	}
} }
