<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

if ( ! class_exists( 'gPluginDateTimeHelper' ) ) { class gPluginDateTimeHelper extends gPluginClassCore
{

	// FROM: EDD
	// Retrieve timezone
	// edd_get_timezone_id()
	public static function get_timezone_id()
	{
		// if site timezone string exists, return it
		if ( $timezone = get_option( 'timezone_string' ) )
			return $timezone;

		// get UTC offset, if it isn't set return UTC
		if ( ! ( $utc_offset = 3600 * get_option( 'gmt_offset', 0 ) ) )
			return 'UTC';

		// attempt to guess the timezone string from the UTC offset
		$timezone = timezone_name_from_abbr( '', $utc_offset );

		// last try, guess timezone string manually
		if ( FALSE === $timezone ) {

			$is_dst = date( 'I' );

			foreach ( timezone_abbreviations_list() as $abbr )
				foreach ( $abbr as $city )
					if ( $city['dst'] == $is_dst
						&& $city['offset'] == $utc_offset )
							return $city['timezone_id'];
		}

		return 'UTC'; // fallback
	}

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
/// NOT USED YET ---------------------------------------------------------------

	// https://core.trac.wordpress.org/ticket/18146
	// http://weston.ruter.net/2013/04/02/do-not-change-the-default-timezone-from-utc-in-wordpress/

	// http://stackoverflow.com/questions/15827824/how-to-change-default-timezone-utc-to-local-timezone
	// http://stackoverflow.com/questions/15149186/php-show-time-based-on-users-timezone

	// http://codex.wordpress.org/Function_Reference/date_i18n

	public static function from_gmt()
	{
		// http://codex.wordpress.org/current_time
		// http://www.deluxeblogtips.com/2012/10/wordpress-date-time.html
		// http://codex.wordpress.org/Function_Reference/date_i18n
		// function get_date_from_gmt( $string, $format = 'Y-m-d H:i:s' )
	}
} }
