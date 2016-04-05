<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

if ( ! class_exists( 'gPluginTextHelper' ) ) { class gPluginTextHelper extends gPluginClassCore
{

	// https://gist.github.com/geminorum/5eec57816adb003ccefb
	public static function joinString( $parts, $between, $last )
	{
		return join( $last, array_filter( array_merge( array( join( $between, array_slice( $parts, 0, -1 ) ) ), array_slice( $parts, -1 ) ) ) );
	}

	// http://stackoverflow.com/a/3161830
	public static function truncateString( $string, $length = 15, $dots = '&hellip;' )
	{
		return ( strlen( $string ) > $length ) ? substr( $string, 0, $length - strlen( $dots ) ).$dots : $string;
	}

	// http://camendesign.com/code/title-case
	// http://daringfireball.net/2008/05/title_case
	// https://github.com/gouch/to-title-case
	// http://wordpress.org/plugins/to-title-case/
	// original Title Case script © John Gruber <daringfireball.net>
	// javascript port © David Gouch <individed.com>
	// PHP port of the above by Kroc Camen <camendesign.com>
	public static function titleCase( $title )
	{
		// remove HTML, storing it for later
		// HTML elements to ignore | tags | entities
		$regx = '/<(code|var)[^>]*>.*?<\/\1>|<[^>]+>|&\S+;/';
		preg_match_all( $regx, $title, $html, PREG_OFFSET_CAPTURE );
		$title = preg_replace ( $regx, '', $title );

		// find each word (including punctuation attached)
		preg_match_all( '/[\w\p{L}&`\'‘’"“\.@:\/\{\(\[<>_]+-? */u', $title, $m1, PREG_OFFSET_CAPTURE );

		foreach ( $m1[0] as &$m2 ) {

			// shorthand these- "match" and "index"
			list( $m, $i ) = $m2;

			// correct offsets for multi-byte characters (`PREG_OFFSET_CAPTURE` returns *byte*-offset)
			// we fix this by recounting the text before the offset using multi-byte aware `strlen`
			$i = mb_strlen( substr( $title, 0, $i ), 'UTF-8' );

			// find words that should always be lowercase…
			// (never on the first word, and never if preceded by a colon)
			$m = $i > 0 && mb_substr( $title, max ( 0, $i - 2 ), 1, 'UTF-8' ) !== ':' &&
				! preg_match( '/[\x{2014}\x{2013}] ?/u', mb_substr( $title, max( 0, $i - 2 ), 2, 'UTF-8' ) ) &&
				preg_match( '/^(a(nd?|s|t)?|b(ut|y)|en|for|i[fn]|o[fnr]|t(he|o)|vs?\.?|via)[ \-]/i', $m )
			?	// …and convert them to lowercase
				mb_strtolower( $m, 'UTF-8' )

			// else: brackets and other wrappers
			: (	preg_match( '/[\'"_{(\[‘“]/u', mb_substr( $title, max ( 0, $i - 1 ), 3, 'UTF-8' ) )
			?	// convert first letter within wrapper to uppercase
				mb_substr( $m, 0, 1, 'UTF-8' ).
				mb_strtoupper( mb_substr( $m, 1, 1, 'UTF-8' ), 'UTF-8' ).
				mb_substr( $m, 2, mb_strlen( $m, 'UTF-8' ) - 2, 'UTF-8' )

			// else: do not uppercase these cases
			: (	preg_match( '/[\])}]/', mb_substr( $title, max ( 0, $i - 1 ), 3, 'UTF-8' ) ) ||
				preg_match( '/[A-Z]+|&|\w+[._]\w+/u', mb_substr( $m, 1, mb_strlen( $m, 'UTF-8' ) - 1, 'UTF-8' ) )
			?	$m
				// if all else fails, then no more fringe-cases; uppercase the word
			:	mb_strtoupper( mb_substr( $m, 0, 1, 'UTF-8' ), 'UTF-8' ).
				mb_substr( $m, 1, mb_strlen( $m, 'UTF-8' ), 'UTF-8' )
			) );

			// resplice the title with the change (`substr_replace` is not multi-byte aware)
			$title = mb_substr( $title, 0, $i, 'UTF-8' ).$m.
				mb_substr( $title, $i + mb_strlen( $m, 'UTF-8' ), mb_strlen( $title, 'UTF-8' ), 'UTF-8' )
			;
		}

		// restore the HTML
		foreach ( $html[0] as &$tag )
			$title = substr_replace( $title, $tag[0], $tag[1], 0 );

		return $title;
	}

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
/// NOT USED YET ---------------------------------------------------------------

	// FIXME: DRAFT : works fine
	// NOTE: indent is 4 spaces not a tab!
	// http://codepad.org/L089YbgZ
	// $str = 'Array ( [0] => foo [1] => bar [2] => baz)';
	// $arr = print_r_reverse($str);
	// print_r($arr);
	public static function printRReverse( $in )
	{
		$lines = explode( "\n", trim( $in ) );

		if ( trim( $lines[0] ) != 'Array' ) {

			// bottomed out to something that isn\'t an array
			return $in;

		} else {

			// this is an array, lets parse it
			if ( preg_match( "/(\s{5,})\(/", $lines[1], $match ) ) {

				// this is a tested array/recursive call to this function
				// take a set of spaces off the beginning
				$spaces        = $match[1];
				$spaces_length = strlen( $spaces );
				$lines_total   = count( $lines );

				for ( $i = 0; $i < $lines_total; $i++ )
					if ( $spaces == substr( $lines[$i], 0, $spaces_length ) )
						$lines[$i] = substr( $lines[$i], $spaces_length );
			}

			array_shift( $lines ); // Array
			array_shift( $lines ); // (
			array_pop( $lines ); // )
			$in = implode( "\n", $lines );

			// make sure we only match stuff with 4 preceding spaces (stuff for this array and not a nested one)
			preg_match_all( "/^\s{4}\[(.+?)\] \=\> /m", $in, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER );

			$previous_key = '';
			$pos          = array();
			$in_length    = strlen( $in );

			// store the following in $pos:
			// array with key = key of the parsed array's item
			// value = array(start position in $in, $end position in $in)
			foreach ( $matches as $match ) {
				$key       = $match[1][0];
				$start     = $match[0][1] + strlen( $match[0][0] );
				$pos[$key] = array( $start, $in_length );

				if ( $previous_key != '' )
					$pos[$previous_key][1] = $match[0][1] - 1;

				$previous_key = $key;
			}

			$ret = array();

			foreach ( $pos as $key => $where )
				// recursively see if the parsed out value is an array too
				$ret[$key] = self::printRReverse( substr( $in, $where[0], $where[1] - $where[0] ) );

			return $ret;
		}
	}

	// Removing JavaScript tags
	// https://gist.github.com/tommcfarlin/2959778
	public static function removeScript( $html )
	{
		return preg_replace( '/<script\b[^>]*>(.*?)<\/script>/is', '', $html );
	}

	// https://www.addedbytes.com/blog/code/php-querystring-functions/
	public static function removeQueryVar( $url, $key )
	{
		return substr( preg_replace( '/(.*)(?|&)'.$key.'=[^&]+?(&)(.*)/i', '$1$2$4', $url.'&' ), 0, -1 );
	}

	public static function addQueryVar( $url, $key, $value )
	{
		$url = self::removeQueryVar( $url, $key );

		if ( FALSE === strpos( $url, '?' ) )
			return $url.'?'.$key.'='.$value;

		return $url.'&'.$key.'='.$value;
	}

	// create username from email address
	// ALSO SEE : http://php.net/manual/en/function.mailparse-rfc822-parse-addresses.php
	public static function email_to_username( $email )
	{
		return preg_replace( '/([^@]*).*/', '$1', $email ); // before @ // http://stackoverflow.com/a/956584
		// return preg_replace( '/@.*?$/', '', $email ); // without @ // http://stackoverflow.com/a/6333658
	}

	// http://php.net/manual/en/function.strrev.php#62422
	public static function utf8_strrev($str)
	{
		preg_match_all( '/./us', $str, $ar );
		return join( '', array_reverse( $ar[0] ) );
	}

	// Uppercase the first character of each word in a string except 'and', 'to', etc
	// http://stackoverflow.com/a/17817669
	public static function titleCaseUTF($string, $delimiters = array(" ", "-", ".", "'", "O'", "Mc"), $exceptions = array("and", "to", "of", "das", "dos", "I", "II", "III", "IV", "V", "VI"))
	{
		/*
		 * Exceptions in lower case are words you don't want converted
		 * Exceptions all in upper case are any words you don't want converted to title case
		 *   but should be converted to upper case, e.g.:
		 *   king henry viii or king henry Viii should be King Henry VIII
		 */
		$string = mb_convert_case($string, MB_CASE_TITLE, "UTF-8");
		foreach ($delimiters as $dlnr => $delimiter) {
			$words = explode($delimiter, $string);
			$newwords = array();
			foreach ($words as $wordnr => $word) {
				if (in_array(mb_strtoupper($word, "UTF-8"), $exceptions)) {
					// check exceptions list for any words that should be in upper case
					$word = mb_strtoupper($word, "UTF-8");
				} else if (in_array(mb_strtolower($word, "UTF-8"), $exceptions)) {
					// check exceptions list for any words that should be in upper case
					$word = mb_strtolower($word, "UTF-8");
				} else if (!in_array($word, $exceptions)) {
					// convert to uppercase (non-utf8 only)
					$word = ucfirst($word);
				}
				array_push($newwords, $word);
			}
			$string = join($delimiter, $newwords);
	   }//foreach
	   return $string;
	}

	// http://www.w3.org/International/questions/qa-forms-utf-8.en.php
	// http://wpkrauts.com/2013/enforce-utf-8-with-php/
	/**
	 * Check for UTF-8 compatibility
	 *
	 * Regex from Martin Dürst
	 * @source http://www.w3.org/International/questions/qa-forms-utf-8.en.php
	 * @param string $str String to check
	 * @return boolean
	 */
	public static function is_utf8( $str )
	{
		return preg_match( "/^(
			 [\x09\x0A\x0D\x20-\x7E]            # ASCII
		   | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
		   |  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
		   | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
		   |  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
		   |  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
		   | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
		   |  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
		  )*$/x",
		  $str
		);
	}

	// http://wpkrauts.com/2013/enforce-utf-8-with-php/
	/**
	 * Try to convert a string to UTF-8.
	 *
	 * @author Thomas Scholz <http://toscho.de>
	 * @param string $str String to encode
	 * @param string $inputEnc Maybe the source encoding.
	 *               Set to NULL if you are not sure. iconv() will fail then.
	 * @return string
	 */
	public static function force_utf8( $str, $inputEnc = 'WINDOWS-1252' )
	{
		if ( self::is_utf8( $str ) ) // Nothing to do.
			return $str;

		if ( strtoupper( $inputEnc ) === 'ISO-8859-1' )
			return utf8_encode( $str );

		if ( function_exists( 'mb_convert_encoding' ) )
			return mb_convert_encoding( $str, 'UTF-8', $inputEnc );

		if ( function_exists( 'iconv' ) )
			return iconv( $inputEnc, 'UTF-8', $str );

		// You could also just return the original string.
		trigger_error(
			'Cannot convert string to UTF-8 in file '
				. __FILE__ . ', line ' . __LINE__ . '!',
			E_USER_ERROR
		);
	}

	// https://gist.github.com/phpdistiller/8067353
	// This snippet sanitizes database inputs.
	// Source : http://css-tricks.com/snippets/php/sanitize-database-inputs/
	/*
		// Usage:
		$bad_string = "Hi! <script src='http://www.evilsite.com/bad_script.js'></script> It's a good day!";
		$good_string = sanitize($bad_string);
		// $good_string returns "Hi! It\'s a good day!"

		// Also use for getting POST/GET variables
		$_POST = sanitize($_POST);
		$_GET  = sanitize($_GET);
	*/

	// Function for stripping out malicious bits
	public static function cleanInput($input)
	{

	  $search = array(
		'@<script[^>]*?>.*?</script>@si',   // Strip out javascript
		'@<[\/\!]*?[^<>]*?>@si',            // Strip out HTML tags
		'@<style[^>]*?>.*?</style>@siU',    // Strip style tags properly
		'@<![\s\S]*?--[ \t\n\r]*>@'         // Strip multi-line comments
	  );

		$output = preg_replace($search, '', $input);
		return $output;
	}

	// Sanitization function
	public static function sanitize( $input )
	{
		if ( is_array( $input ) ) {
			foreach ( $input as $var=>$val ) {
				$output[$var] = sanitize( $val );
			}
		} else {
			if (get_magic_quotes_gpc()) {
				$input = stripslashes($input);
			}
			$input  = cleanInput($input);
			$output = mysql_real_escape_string($input);
		}
		return $output;
	}

	/////////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////

	// https://gist.github.com/chrisguitarguy/6562266
	// PHP regex to match a "string"
	public static function a_string()
	{
		$regex = '(?<!\\\\)"' // any " not preceded by a backslash
			. '(?:[^"]|(?<=\\\\)")*' // 0 or more of anything that's NOT a " OR a " that's preceded by a backslash
			. '"' // followed by a closing "
			. '|' // OR
			. '(?<!\\\\)\'' // any ' not preceded by a backslash
			. '(?:[^\']|(?<=\\\\)\')*' // 0 or more of anything that's NOT a ' or a ' preceded by a backslash
			. '\''; // followed by a closing '

		// all togther
		$confusing = '(?<!\\\\)"(?:[^"]|(?<=\\\\)")*"|(?<!\\\\)\'(?:[^\']|(?<=\\\\)\')*\'';
	}

	// originally from WP core
	/**
	 * Strip any new lines from the HTML.
	 *
	 * @param string $html Existing HTML.
	 * @return string Possibly modified $html
	 */
	public static function strip_newlines( $html )
	{
		if ( FALSE !== strpos( $html, "\n" ) )
			$html = str_replace( array( "\r\n", "\n" ), '', $html );
		return $html;
	}


	// https://gist.github.com/ericmann/5400972
	public static function add_img_titles( $content, $title )
	{
		$replacements = array();
		preg_match_all( '/<img[^>]*>/', $content, $matches );

		foreach ( $matches[0] as $match ) {

			if ( preg_match( '/title=\s*/', $match ) )
				continue;

			$newimg = str_replace( '<img ', '<img title="'.$title.'" ', $match );
			$replacements[$match] = $newimg;
		}

		foreach ( $replacements as $original => $replacement )
			$content = str_replace( $original, $replacement, $content );

		return $content;
	}



	// http://bavotasan.com/2012/convert-pre-tag-contents-to-html-entities-in-wordpress/
	public static function pre_content_filter( $content )
	{
		return preg_replace_callback( '|<pre.*>(.*)</pre|isU' , array( __CLASS__, 'convert_pre_entities' ), $content );
	}
	public static function convert_pre_entities( $matches )
	{
		return str_replace( $matches[1], htmlentities( $matches[1] ), $matches[0] );
	}

	// http://bavotasan.com/2012/trim-characters-using-php/
	public static function trim_characters( $text, $length = 45, $append = '&hellip;' ) {

		$length = (int) $length;
		$text = trim( strip_tags( $text ) );

		if ( strlen( $text ) > $length ) {
			$text = substr( $text, 0, $length + 1 );
			$words = preg_split( "/[\s]|&nbsp;/", $text, -1, PREG_SPLIT_NO_EMPTY );
			preg_match( "/[\s]|&nbsp;/", $text, $lastchar, 0, $length );
			if ( empty( $lastchar ) )
				array_pop( $words );

			$text = implode( ' ', $words ) . $append;
		}

		return $text;
	}

	// UTF 8 String remove all invisible characters except newline
	// http://stackoverflow.com/a/12545470/642752
	public static function remove_non_printable( $string )
	{
		return preg_replace( '/[^\P{C}\n]+/u', '', $string );
	}

	// https://gist.github.com/boonebgorges/3657745
	/**
	 * Turn email-style quotes into blockquotes
	 *
	 * For example:
	 *
	 *   My friend said:
	 *
	 *   > You are handsome and also
	 *   > you are very great
	 *
	 *   Then she said:
	 *
	 *   > You are my hero
	 *
	 * becomes
	 *
	 *   My friend said:
	 *
	 *   <blockquote>You are handsome and also you are very great</blockquote>
	 *
	 *   Then she said:
	 *
	 *   <blockquote>You are my hero</blockquote>
	 *
	 * This method is neither elegant nor efficient, but it works.
	 */
	public static function process_quotes( $content ) {
		// Find blank lines
		$content = preg_replace( '/\n\s*\n/m', '<BBG_EMPTY_LINE>', $content );

		// Explode on the blank lines
		$content = explode( '<BBG_EMPTY_LINE>', $content );

		foreach ( $content as &$c ) {
			$c = trim( $c );

			// Reduce multiple-line quotes to a single line
			// This works because the first > in a block will not have a
			// line break before it
			$c = preg_replace( '/\n(>|&gt;)(.*)/m', '$2', $c );

			// Blockquote 'em
			$c = preg_replace( '/^(>|&gt;) (.*)/m', '<blockquote>$2</blockquote>', $c );
		}

		// Put everything back as we found it
		$content = implode( "\n\n", $content );

		return $content;
	}
	//add_filter( 'the_content', 'teleogistic_process_quotes', 5 );
	//add_filter( 'get_comment_text', 'teleogistic_process_quotes', 5 );

	/*

	http://daringfireball.net/2009/11/liberal_regex_for_matching_urls
	http://daringfireball.net/2010/07/improved_regex_for_matching_urls

	--
	// Link, URL and maching word
	// http://stackoverflow.com/a/2175491/642752

	'/<a.*?href\s*=\s*["\']([^"\'>]+)["\'][^>]*>.*?<\/a>/si'

	// To look for word inside of the link url, use:
	'/<a.*?href\s*=\s*["\']([^"\'>]*word[^"\'>]*)["\'][^>]*>.*?<\/a>/si'

	// To look for word inside of the link text, use:
	'/<a.*?href\s*=\s*["\']([^"\'>]+)["\'][^>]*>.*?word.*?<\/a>/si'

	// if you want to match either "red" or "blue", do it like this: (red|blue); if you don't want to match that part itself, you can also use (?:red|blue).


	// http://gilbert.pellegrom.me/php-strip-non-alphanumeric-chars-from-a-string
	// PHP Strip Non-Alphanumeric Chars from a String
	// $string = preg_replace("/[^a-z0-9]+/i", "", $string);

	**/

	// NOT WORKINGG! DO NOT USE
	// http://stackoverflow.com/a/10539617/642752
	public static function strip_whitespace_between_tags( $text )
	{
		return preg_replace(
			'/\s+     # Match one or more whitespace characters
			(?!       # but only if it is impossible to match...
			 [^<>]*   # any characters except angle brackets
			 >        # followed by a closing bracket.
			)         # End of lookahead
			/x',
			'', $text );
	}

	// Use this for compressing output
	// Replacing multiple spaces with a single space
	// http://stackoverflow.com/a/2368546/642752
	public static function strip_multiple_spaces( $text )
	{
		return preg_replace( '!\s+!', ' ', $text );
		// retun preg_replace( "/[[:blank:]]+/", " ", $text );
	}

	// Adding space after periods
	// http://stackoverflow.com/a/2866454/642752
	// TODO : make utf compatible
	public static function space_after_periods( $text )
	{
		return preg_replace( '#(\.|,|\?|!)(\w)#', '\1 \2', $text );
	}

	/*
	// Replace replacement array
	// http://stackoverflow.com/a/8611495/642752
	// http://codepad.viper-7.com/2ZNNYZ
	// SYNTAX : {FIELD}
	function replacement_array( $text, $arr )
	{
		return preg_replace_callback( "/\{([a-zA-Z0-9_]+)\}/", function( $match ) use ( $arr ) {
				return isset( $vars[$match[1]] ) ? $vars[$match[1]] : $match[0];
			}, $text );
	}
	*/

	// ?? : ONLY IMG TAG with "/>"
	// http://bavotasan.com/2009/using-php-to-remove-an-html-tag-from-a-string/
	public static function strip_closed_tag( $text, $tag = 'img' )
	{
		return preg_replace( "/<".$tag."[^>]+\>/i", "", $text );
	}

	// Strip entire html link (including text)
	// http://stackoverflow.com/a/4421552/642752
	public static function strip_entire_link( $text )
	{
		return preg_replace( '#(<a[^>]*?>.*?</a>)#i', '', $text );
	}

	// http://stackoverflow.com/a/9022442/642752
	// http://codepad.org/FTNikw8g
	// EXAMPLE : http://www.yelp.com/biz/my-business-name > yelp.com
	public static function get_domain( $url )
	{
		return preg_replace( '~^www.~', '', parse_url( $url, PHP_URL_HOST ) );
		// return preg_replace( '/^www./', '', parse_url($url, PHP_URL_HOST ) );
	}

	// http://gilbert.pellegrom.me/php-make_clickable
	// So you have string that contains URL’s and you want to make them “clickable”. This is simple and it works:
	// Supports normal, ftp, file and email URL’s as well as subdomains. Also it doesn’t mess with HTML a tags that already exist in the string.
	public static function make_clickable($text)
	{
		return preg_replace( '@(?<![.*">])\b(?:(?:https?|ftp|file)://|[a-z]\.)[-A-Z0-9+&#/%=~_|$?!:,.]*[A-Z0-9+&#/%=~_|$]@i', '<a href="\0">\0</a>', $text );
	}

	// http://gilbert.pellegrom.me/php-quick-convert-string-to-slug
	// PHP Quick Convert String to Slug
	// Input: This is My Title
	// Returns: this-is-my-title
	public static function to_slug($string)
	{
		return strtolower( trim( preg_replace( '/[^A-Za-z0-9-]+/', '-', $string ) ) );
	}

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
/// DEPRECATED -----------------------------------------------------------------

	// http://www.sitepoint.com/title-case-in-php/
	// Converts $title to Title Case, and returns the result.
	public static function titleCase_DEPRECATED( $title )
	{
		// Our array of 'small words' which shouldn't be capitalised if they aren't the first word. Add your own words to taste.
		$small_words = array(
			'of','a','the','and','an','or','nor','but','is','if','then','else','when',
			'at','from','by','on','off','for','in','out','over','to','into','with'
		);

		$words = explode( ' ', $title );

		// If this word is the first, or it's not one of our small words, capitalise it with ucwords().
		foreach ( $words as $key => $word )
			if ( $key == 0 || ! in_array( $word, $small_words ) )
				$words[$key] = ucwords( $word );

		return implode( ' ', $words );
	}
} }
