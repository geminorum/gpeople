<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

if ( ! class_exists( 'gPluginUtils' ) ) { class gPluginUtils extends gPluginClassCore
{

	// FIXME: DEPRECATED
	public static function IP( $pad = FALSE )
	{
		self::__dep( 'gPluginHTTP::IP()' );
		return gPluginHTTP::IP( $pad );
	}

	// FIXME: DEPRECATED
	public static function getIP()
	{
		self::__dep( 'gPluginHTTP::IP()' );
		return gPluginHTTP::IP();
	}

	// FIXME: DEPRECATED
	public static function dump_get( $var, $htmlsafe = TRUE )
	{
		self::__dep( 'self::dump()' );
		return self::dump( $var, $htmlsafe, FALSE );
	}

	// FIXME: DEPRECATED
	public static function dump_n( $var, $htmlsafe = TRUE )
	{
		self::__dep( 'self::dump()' );
		self::dump( $var, $htmlsafe );
	}

	public static function roundArray( $array, $precision = -3, $mode = PHP_ROUND_HALF_UP )
	{
		$rounded = array();

		foreach ( (array) $array as $key => $value )
			$rounded[$key] = round( (float) $value, $precision, $mode );

		return $rounded;
	}

	public static function strposArray( $needles, $haystack )
	{
		foreach ( (array) $needles as $key => $needle )
			if ( FALSE !== strpos( $haystack, $needle ) )
				return $key;

		return FALSE;
	}

	// FIXME: DEPRECATED
	public static function strpos_arr( $haystack, $needle )
	{
		self::__dep( 'gPluginUtils::strposArray()' );
		return self::strposArray( $haystack, $needle );
	}

	// deep array_filter()
	public static function filterArray( $input, $callback = NULL )
	{
		foreach ( $input as &$value )
			if ( is_array( $value ) )
				$value = self::filterArray( $value, $callback );

		return $callback ? array_filter( $input, $callback ) : array_filter( $input );
	}

	// Maps a function to all non-iterable elements of an array or an object.
	// This is similar to `array_walk_recursive()` but acts upon objects too.
	// ANCESTOR: map_deep()
	public static function mapArray( $value, $callback )
	{
		if ( is_array( $value )
			|| is_object( $value ) ) {

			foreach ( $value as &$item )
				$item = self::mapArray( $item, $callback );

			return $value;
		}

		return call_user_func( $callback, $value );
	}

	// for useing with $('form').serializeArray();
	// http://api.jquery.com/serializeArray/
	// @INPUT: [{name:"a",value:"1"},{name:"b",value:"2"}]
	public static function parseJSArray( $array )
	{
		$parsed = array();

		foreach ( $array as $part )
			$parsed[$part['name']] = $part['value'];

		return $parsed;
	}

	public static function range( $start, $end, $step = 1, $format = TRUE )
	{
		$array = array();

		foreach ( range( $start, $end, $step ) as $number )
			$array[$number] = $format ? gPluginNumber::format( $number ) : $number;

		return $array;
	}

	// USE: `array_keys()` on posted checkboxes
	public static function getKeys( $options, $if = TRUE )
	{
		$keys = array();

		foreach ( (array) $options as $key => $value )
			if ( $value == $if )
				$keys[] = $key;

		return $keys;
	}

	// http://wordpress.mfields.org/2011/rekey-an-indexed-array-of-post-objects-by-post-id/
	public static function reKey( $list, $key )
	{
		if ( ! empty( $list ) ) {
			$ids = wp_list_pluck( $list, $key );
			$list = array_combine( $ids, $list );
		}

		return $list;
	}

	public static function sameKey( $old )
	{
		$same = array();

		foreach ( $old as $key => $value )
			if ( FALSE !== $value && NULL !== $value )
				$same[$value] = $value;

		return $same;
	}

	/**
	 * recursive argument parsing
	 * @link: https://gist.github.com/boonebgorges/5510970
	 *
	 * Values from $a override those from $b; keys in $b that don't exist
	 * in $a are passed through.
	 *
	 * This is different from array_merge_recursive(), both because of the
	 * order of preference ($a overrides $b) and because of the fact that
	 * array_merge_recursive() combines arrays deep in the tree, rather
	 * than overwriting the b array with the a array.
	*/
	public static function recursiveParseArgs( &$a, $b )
	{
		$a = (array) $a;
		$b = (array) $b;
		$r = $b;

		foreach ( $a as $k => &$v )
			if ( is_array( $v ) && isset( $r[$k] ) )
				$r[$k] = self::recursiveParseArgs( $v, $r[$k] );
			else
				$r[$k] = $v;

		return $r;
	}

	// FIXME: DEPRECATED
	public static function parse_args_r( &$a, $b )
	{
		self::__dep( 'gPluginUtils::recursiveParseArgs()' );
		return self::parseArgs( $a, $b );
	}

	public static function parseArgs( $args, $defaults = '' )
	{
		if ( is_object( $args ) )
			$r = get_object_vars( $args );

		else if ( is_array( $args ) )
			$r = &$args;

		else
			self::parse_str( $args, $r );

		if ( is_array( $defaults ) )
			return array_merge( $defaults, $r );

		return $r;
	}

	// FIXME: DEPRECATED
	public static function parse_args( $args, $defaults = '' )
	{
		self::__dep( 'gPluginUtils::parseArgs()' );
		return self::parseArgs( $args, $defaults );
	}

	public static function parse_str( $string, &$array )
	{
		parse_str( $string, $array );

		if ( get_magic_quotes_gpc() )
			$array = self::stripslashes( $array );
	}

	public static function unslash( $array )
	{
		return self::stripslashes( $array );
	}

	public static function stripslashes( $array )
	{
		return self::mapArray( $array, function( $value ){
			return is_string( $value ) ? stripslashes( $value ) : $value;
		} );
	}

	public static function stripslashes_deep( $value )
	{
		self::__dep( 'gPluginUtils::stripslashes()' );

		if ( is_array( $value ) ) {
			$value = array_map( array( __CLASS__, 'stripslashes_deep' ), $value );
		} else if ( is_object( $value ) ) {
			$vars = get_object_vars( $value );
			foreach ( $vars as $key => $data ) {
				$value->{$key} = self::stripslashes_deep( $data );
			}
		} else if ( is_string( $value ) ) {
			$value = stripslashes( $value );
		}

		return $value;
	}

	public static function urlencode( $array )
	{
		return self::mapArray( $array, 'urlencode' );
	}

	public static function urldecode( $array )
	{
		return self::mapArray( $array, 'urldecode' );
	}

	// will remove trailing forward and backslashes if it exists already before adding
	// a trailing forward slash. This prevents double slashing a string or path.
	// ANCESTOR: trailingslashit()
	public static function trail( $string )
	{
		return self::untrail( $string ).'/';
	}

	// removes trailing forward slashes and backslashes if they exist.
	// ANCESTOR: untrailingslashit()
	public static function untrail( $string )
	{
		return rtrim( $string, '/\\' );
	}

	// @SOURCE: http://php.net/manual/en/function.array-multisort.php#100534
	/***
	* I came up with an easy way to sort database-style results. This does what
	* example 3 does, except it takes care of creating those intermediate
	* arrays for you before passing control on to array_multisort().
	*
	* The sorted array is now in the return value of the function instead of
	* being passed by reference.
	* Pass the array, followed by the column names and sort flags
	*
		$data[] = array('volume' => 67, 'edition' => 2);
		$data[] = array('volume' => 86, 'edition' => 1);
		$data[] = array('volume' => 85, 'edition' => 6);
		$data[] = array('volume' => 98, 'edition' => 2);
		$data[] = array('volume' => 86, 'edition' => 6);
		$data[] = array('volume' => 67, 'edition' => 7);

		$sorted = gPluginUtils::arrayOrderBy( $data, 'volume', SORT_DESC, 'edition', SORT_ASC );
	*/
	public static function arrayOrderBy()
	{
		$args = func_get_args();
		$data = array_shift( $args );

		foreach ( $args as $n => $field ) {

			if ( is_string( $field ) ) {

				$tmp = array();

				foreach ( $data as $key => $row )
					$tmp[$key] = $row[$field];

				$args[$n] = $tmp;
			}
		}

		$args[] = &$data;

		call_user_func_array( 'array_multisort', $args );

		return array_pop( $args );
	}

	// recursively sort multidimentional arrays by key
	// @SOURCE: http://www.php.net/manual/en/function.ksort.php#105399
	public static function arrayKSort( &$array )
	{
		ksort( $array );

		foreach ( $array as &$a )
			if ( is_array( $a ) && ! empty( $a ) )
				self::arrayKSort( $a );
	}

	// sort multidimensional array by value
	// @SOURCE: http://stackoverflow.com/a/2699110/4864081
	// gPluginUtils::arrayASort( $array, 'order' );
	public static function arrayASort( &$array, $key )
	{
		$sorter = $ret = array();
		reset( $array );

		foreach ( $array as $ii => $va )
			$sorter[$ii] = $va[$key];

		asort( $sorter );

		foreach ( $sorter as $ii => $va )
			$ret[$ii] = $array[$ii];

		$array = $ret;
	}

	// If you need to prepend something to the array without the keys
	// being reindexed and/or need to prepend a key value pair
	// @SOURCE: http://www.php.net/manual/en/function.array-unshift.php#14358
	// CAUTION: not on big arrays
	public static function arrayUnShift( &$array, $key, $val )
	{
		$array = array_reverse( $array, TRUE );
		$array[$key] = $val;
		$array = array_reverse( $array, TRUE );

		return count( $array );
	}
} }
