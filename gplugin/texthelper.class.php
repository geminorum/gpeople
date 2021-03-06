<?php defined( 'ABSPATH' ) || die( header( 'HTTP/1.0 403 Forbidden' ) );

if ( ! class_exists( 'gPluginTextHelper' ) ) { class gPluginTextHelper extends gPluginClassCore
{

	public static function formatName( $string, $separator = ', ' )
	{
		// already formatted
		if ( FALSE !== stripos( $string, trim( $separator ) ) )
			return $string;

		// remove NULL, FALSE and empty strings (""), but leave values of 0
		$parts = array_filter( explode( ' ', $string, 2 ), 'strlen' );

		if ( 1 == count( $parts ) )
			return $string;

		return $parts[1].$separator.$parts[0];
	}

	public static function reFormatName( $string, $separator = ', ' )
	{
		return preg_replace( '/(.*), (.*)/', '$2 $1', $string );
		// return preg_replace( '/(.*)([,،;؛]) (.*)/u', '$3'.$separator.'$1', $string ); // Wrong!
	}

	// simpler version of `wpautop()`
	// @REF: https://stackoverflow.com/a/5240825
	// @SEE: https://stackoverflow.com/a/7409591
	public static function autoP( $string )
	{
		$string = (string) $string;

		if ( 0 === strlen( $string ) )
			return '';

		// standardize newline characters to "\n"
		$string = str_replace( array( "\r\n", "\r" ), "\n", $string );

		// remove more than two contiguous line breaks
		$string = preg_replace( "/\n\n+/", "\n\n", $string );

		$paraphs = preg_split( "/[\n]{2,}/", $string );

		foreach ( $paraphs as $key => $p )
			$paraphs[$key] = '<p>'.str_replace( "\n", '<br />'."\n", $paraphs[$key] ).'</p>'."\n";

		$string = implode( '', $paraphs );

		// remove a P of entirely whitespace
		$string = preg_replace( '|<p>\s*</p>|', '', $string );

		return trim( $string );
	}

	// @REF: https://github.com/michelf/php-markdown/issues/230#issuecomment-303023862
	public static function removeP( $string )
	{
		return str_replace( array(
			"</p>\n\n<p>",
			'<p>',
			'</p>',
		), array(
			"\n\n",
			"",
		), $string );
	}

	public static function email2username( $email, $strict = TRUE )
	{
		return preg_replace( '/\s+/', '', sanitize_user( preg_replace( '/([^@]*).*/', '$1', $email ), $strict ) );
	}

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

	/**
	 * @link	https://gist.github.com/geminorum/fe2a9ba25db5cf2e5ad6718423d00f8a
	 *
	 * original Title Case script © John Gruber <daringfireball.net>
	 * javascript port © David Gouch <individed.com>
	 * PHP port of the above by Kroc Camen <camendesign.com>
	 */
	public static function titleCase( $title )
	{
		// remove HTML, storing it for later
		// HTML elements to ignore | tags | entities
		$regx = '/<(code|var)[^>]*>.*?<\/\1>|<[^>]+>|&\S+;/';
		preg_match_all( $regx, $title, $html, PREG_OFFSET_CAPTURE );
		$title = preg_replace( $regx, '', $title );

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
} }
