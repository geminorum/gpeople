<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gPluginCacheHelper extends gPluginClassCore
{

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
/// NOT USED YET ---------------------------------------------------------------

	// Get a unique ID key for current $wp_the_query object.
	// https://gist.github.com/tollmanz/2760476
	public static function getObjectKey()
	{
		$queried_object    = get_queried_object();
		$queried_object_id = get_queried_object_id();

		if ( ! is_null( $queried_object ) && $queried_object_id > 0 )
			return md5( serialize( $queried_object ) . $queried_object_id );
		else
			return 0;
	}

	// http://tollmanz.com/invalidation-schemes/
	// http://tollmanz.com/grokking-the-wp-object-cache/

	// https://gist.github.com/tollmanz/3882202
	// Refresh Google Analytics Data on an Interval
	function zt_schedule_ga_refresh()
	{
		if ( ! wp_next_scheduled( 'zt-refresh-top-posts' ) )
			wp_schedule_event( time(), 'zt-refresh-top-posts' );
		add_action( 'zt-refresh-top-posts', 'zt_refresh_top_posts' );
	} // add_action( 'init', 'zt_schedule_ga_refresh' );

	// https://gist.github.com/tollmanz/3882203
	// Cache Google Analytics Data with Variable Args

	function zt_generate_top_posts( $args )
	{
		// Queries GA, relates to internal posts
		$top_post_objects = zt_magic_GA_querier( $args );
		// If no objects are returned, exit the function
		if ( FALSE === $top_post_objects )
			return FALSE;

		// Start building the HTML
		$html = '<ul>';

		// Loop through post objects and generate HTML
		foreach ( $top_post_objects as $post_object ) {
			$html .= '<li>';
			$html .= '<a href="' . esc_url( $post_object['permalink'] ) . '">';
			$html .= esc_html( $post_object['post_title'] );
			$html .= '</a>';
			$html .= '</li>';
		}

		$html .= '</ul>';
		return $html;
	}

	// https://gist.github.com/tollmanz/3882208
	// Get Google Analytics Data With Variable Arguments
	function zt_get_top_posts_ANOTHER( $args, $force = FALSE )
	{
		// Define our cache key
		$cache_key = 'zt-top-posts';

		// Attempt to grab data from cache
		$top_posts = wp_cache_get( $cache_key );

		// If not found in cache or forced, regenerate
		if ( FALSE === $top_posts || TRUE === $force ) {

			// Get posts from GA
			$top_posts = zt_generate_top_posts( $args );

			// If none were found, save a dummy value so the caller knows that
			if ( FALSE === $top_posts )
			$top_posts = 'none-found';

			// Set the result to the cache
			wp_cache_set( $cache_key, $top_posts, 3600 );
		}

		return $top_posts;
	}

	// https://gist.github.com/tollmanz/3882215
	// Cache Google Analytics Data with Unique Key
	function zt_get_top_posts( $args, $force = FALSE )
	{
		// Define our cache key
		$identifier = md5( maybe_serialize( $args ) );
		$cache_key = 'top-posts-' . $identifier;

		// Define the cache group
		$cache_group = 'zt-ga-data';

		// Attempt to grab data from cache
		$top_posts = wp_cache_get( $cache_key, $cache_group );

		// If not found in cache or forced, regenerate
		if ( FALSE === $top_posts || TRUE === $force ) {

			// Get posts from GA
			$top_posts = zt_generate_top_posts( $args );

			// If none were found, save a dummy value so the caller knows that
			if ( FALSE === $top_posts )
				$top_posts = 'none-found';

			// Set the result to the cache
			wp_cache_set( $cache_key, $top_posts, $cache_group, 3600 );
		}

		return $top_posts;
	}

	// Basic Incrementor/Versioning Pattern
	// https://gist.github.com/tollmanz/3882524
	function get_object( $force = FALSE )
	{
		$cache_key = 'my-object-' . get_incrementor();
		$object = wp_cache_get( $cache_key );

		if ( FALSE === $object ) {
			$object = regenerate_cached_object();
			wp_cache_set( $cache_key, $object, 3600 );
		}

		return $object;
	}

	function get_incrementor( $refresh = FALSE )
	{
		$incrementor_key = 'my-incrementor';
		$incrementor_value = wp_cache_get( $incrementor_key );

		if ( FALSE === $incrementor_value || TRUE === $refresh ) {
			$incrementor_value = time();
			wp_cache_set( $incrementor_key, $incrementor_value );
		}

		return $incrementor_value;
	}

	// Get Google Analytics Data With Incrementor
	// https://gist.github.com/tollmanz/3882534
	function zt_get_top_posts_ANOTHERXX( $args, $force = FALSE )
	{
		// Define the cache key
		$identifier = md5( maybe_serialize( $args ) );
		$cache_key = 'top-posts-' . $identifier;

		// Define the cache group
		$cache_group = 'zt-ga-data-' . zt_get_incrementor();

		// Attempt to get data from cache
		$top_posts = wp_cache_get( $cache_key, $cache_group );

		// If not found in cache or forced, regenerate
		if ( FALSE === $top_posts || TRUE === $force ) {

			// Get posts from GA
			$top_posts = zt_generate_top_posts( $args );

			// If none were found, save a dummy value so the caller knows that
			if ( FALSE === $top_posts )
				$top_posts = 'none-found';

			// Set the result to the cache
			wp_cache_set( $cache_key, $top_posts, $cache_group, 3600 );
		}

		return $top_posts;
	}

	function zt_get_incrementor( $refresh = FALSE )
	{
		$incrementor_key = 'google-analytics';
		$incrementor_group = 'zt-incrementors';
		$incrementor_value = wp_cache_get( $incrementor_key, $incrementor_group );

		if ( FALSE === $incrementor_value || TRUE === $refresh ) {
			$incrementor_value = time();
			wp_cache_set( $incrementor_key, $incrementor_value, $incrementor_group );
		}

		return $incrementor_value;
	}

	// Basic Google Analytics Caching
	// https://gist.github.com/tollmanz/3882185
	function zt_get_top_posts_ANOTHERXXX()
	{
		// Define the cache key
		$cache_key = 'zt-top-posts';

		// Attempt to get data from cache
		$top_posts = wp_cache_get( $cache_key );

		// If not found in cache regenerate
		if ( FALSE === $top_posts ) {

			// Get posts from Google Analytics
			$top_posts = zt_generate_top_posts();

			// If none were found, save a dummy value so the caller knows that
			if ( FALSE === $top_posts )
				$top_posts = 'none-found';

			// Set the result to the cache
			wp_cache_set( $cache_key, $top_posts, 3600 );
		}

		return $top_posts;
	}

	// Cache Google Analytics Data with Force Argument
	// https://gist.github.com/tollmanz/3882195
	function zt_get_top_posts_ANOTHERXXXX( $force = FALSE )
	{
		// Define our cache key
		$cache_key = 'zt-top-posts';

		// Attempt to grab data from cache
		$top_posts = wp_cache_get( $cache_key );

		// If not found in cache or forced, regenerate
		if ( FALSE === $top_posts || TRUE === $force ) {

			// Get posts from GA
			$top_posts = zt_generate_top_posts();

			// If none were found, save a dummy value so the caller knows that
			if ( FALSE === $top_posts )
				$top_posts = 'none-found';

			// Set the result to the cache
			wp_cache_set( $cache_key, $top_posts, 3600 );
		}

		return $top_posts;
	}

	function zt_refresh_top_posts()
	{
		return zt_get_top_posts( TRUE );
	}

	// Get Google Analytics Data
	// https://gist.github.com/tollmanz/3882192
	function zt_generate_top_posts_ANOTHERXXXXXX() {
		// These URLs will not be be returned
		$blacklisted_paths = array(
			'/',
			'about-us',
			'that-page-no-one-needed-to-see'
		);

		// URLs with these components will not be returned
		$blacklisted_components = array(
			'twitter-accounts',
			'.html'
		);

		// Collect the arguments
		$args = array(
			'number' 	=> 5,
			'start-date' 	=> '2012-10-07',
			'end-date' 	=> '2012-10-13',
			'bl_paths' 	=> $blacklisted_paths,
			'bl_components'	=> $blacklisted_components,
		);

		// Queries GA, relates to internal posts
		$top_post_objects = zt_magic_GA_querier( $args );

		// If no objects are returned, exit the function
		if ( FALSE === $top_post_objects )
			return FALSE;

		// Start building the HTML
		$html = '<ul>';

		// Loop through post objects and generate HTML
		foreach ( $top_post_objects as $post_object ) {
			$html .= '<li>';
			$html .= '<a href="' . esc_url( $post_object['permalink'] ) . '">';
			$html .= esc_html( $post_object['post_title'] );
			$html .= '</a>';
			$html .= '</li>';
		}

		$html .= '</ul>';

		return $html;
	}
}
