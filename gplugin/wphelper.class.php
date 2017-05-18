<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

if ( ! class_exists( 'gPluginWPHelper' ) ) { class gPluginWPHelper extends gPluginClassCore
{

	public static function log( $error = '{NO Error Code}', $data = array(), $wp_error = NULL )
	{
		if ( ! WP_DEBUG_LOG )
			return;

		$log = array_merge( array(
			'error'   => $error,
			'time'    => current_time( 'mysql' ),
			'ip'      => gPluginHTTP::IP(),
			'message' => ( is_null( $wp_error ) ? '{NO WP_Error Object}' : $wp_error->get_error_message() ),
		), $data );

		error_log( print_r( $log, TRUE ) );
	}

	// EDITED: 12/25/2016, 1:27:21 PM
	public static function getPostTypes( $mod = 0, $args = array( 'public' => TRUE ) )
	{
		$list = array();

		foreach ( get_post_types( $args, 'objects' ) as $post_type => $post_type_obj ) {

			// label
			if ( 0 === $mod )
				$list[$post_type] = $post_type_obj->label ? $post_type_obj->label : $post_type_obj->name;

			// plural
			else if ( 1 === $mod )
				$list[$post_type] = $post_type_obj->labels->name;

			// singular
			else if ( 2 === $mod )
				$list[$post_type] = $post_type_obj->labels->singular_name;

			// nooped
			else if ( 3 === $mod )
				$list[$post_type] = array(
					0          => $post_type_obj->labels->singular_name,
					1          => $post_type_obj->labels->name,
					'singular' => $post_type_obj->labels->singular_name,
					'plural'   => $post_type_obj->labels->name,
					'context'  => NULL,
					'domain'   => NULL,
				);

			// object
			else if ( 4 === $mod )
				$list[$post_type] = $post_type_obj;
		}

		return $list;
	}

	// EDITED: 12/27/2016, 6:36:20 AM
	public static function getTaxonomies( $mod = 0, $args = array() )
	{
		$list = array();

		foreach ( get_taxonomies( $args, 'objects' ) as $taxonomy => $taxonomy_obj ) {

			// label
			if ( 0 === $mod )
				$list[$taxonomy] = $taxonomy_obj->label ? $taxonomy_obj->label : $taxonomy_obj->name;

			// plural
			else if ( 1 === $mod )
				$list[$taxonomy] = $taxonomy_obj->labels->name;

			// singular
			else if ( 2 === $mod )
				$list[$taxonomy] = $taxonomy_obj->labels->singular_name;

			// nooped
			else if ( 3 === $mod )
				$list[$taxonomy] = array(
					0          => $taxonomy_obj->labels->singular_name,
					1          => $taxonomy_obj->labels->name,
					'singular' => $taxonomy_obj->labels->singular_name,
					'plural'   => $taxonomy_obj->labels->name,
					'context'  => NULL,
					'domain'   => NULL,
				);

			// object
			else if ( 4 === $mod )
				$list[$taxonomy] = $taxonomy_obj;

			// with object_type
			else if ( 5 === $mod )
				$list[$taxonomy] = $taxonomy_obj->labels->name.gPluginHTML::joined( $taxonomy_obj->object_type, ' (', ')' );

			// with name
			else if ( 6 === $mod )
				$list[$taxonomy] = $taxonomy_obj->labels->menu_name.' ('.$taxonomy_obj->name.')';
		}

		return $list;
	}

	// this must be wp core future!!
	// support post-thumbnails for CPT
	// call this late on after_setup_theme
	public static function themeThumbnails( $post_types )
	{
		global $_wp_theme_features;

		$feature    = 'post-thumbnails';
		$post_types = (array) $post_types;

		if ( isset( $_wp_theme_features[$feature] ) ) {

			// registered for all types
			if ( TRUE === $_wp_theme_features[$feature] ) {

				// WORKING: but if it is TRUE, it's TRUE!
				// $post_types[] = 'post';
				// $_wp_theme_features[$feature] = array( $post_types );

			} else if ( is_array( $_wp_theme_features[$feature][0] ) ) {
				$_wp_theme_features[$feature][0] = array_merge( $_wp_theme_features[$feature][0], $post_types );
			}

		} else {
			$_wp_theme_features[$feature] = array( $post_types );
		}
	}

	// this must be wp core future!!
	// core duplication with post_type & title : add_image_size()
	public static function registerImageSize( $name, $atts = array() )
	{
		global $_wp_additional_image_sizes;

		$args = self::atts( array(
			'n' => 'Undefined',
			'w' => 0,
			'h' => 0,
			'c' => 0,
			'p' => array( 'post' ),
		), $atts );

		$_wp_additional_image_sizes[$name] = array(
			'width'     => absint( $args['w'] ),
			'height'    => absint( $args['h'] ),
			'crop'      => $args['c'],
			'post_type' => $args['p'],
			'title'     => $args['n'],
		);
	}

	// FIXME: DEPRECATED
	// core duplication with post_type : add_image_size()
	public static function addImageSize( $name, $width = 0, $height = 0, $crop = FALSE, $post_type = array( 'post' ) )
	{
		global $_wp_additional_image_sizes;

		$_wp_additional_image_sizes[ $name ] = array(
			'width'     => absint( $width ),
			'height'    => absint( $height ),
			'crop'      => $crop,
			'post_type' => $post_type,
		);

		self::__dep( 'gPluginWPHelper::registerImageSize' );
	}

	// FROM: gEditorialHelper
	public static function getPostIDbySlug( $slug, $post_type, $url = FALSE )
	{
		static $strings = array();

		if ( $url ) {
			$slug = rawurlencode( urldecode( $slug ) );
			$slug = sanitize_title( basename( $slug ) );
		}

		$slug = trim( $slug );

		if ( isset( $strings[$post_type][$slug] ) )
			return $strings[$post_type][$slug];

		global $wpdb;

		$post_id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT ID FROM $wpdb->posts WHERE post_name = %s AND post_type = %s",
				$slug,
				$post_type
			)
		);

		if ( is_array( $post_id ) )
			return $strings[$post_type][$slug] = $post_id[0];

		else if ( ! empty( $post_id ) )
			return $post_id;

		return $strings[$post_type][$slug] = FALSE;
	}

	public static function getCurrentPostType()
	{
		global $post, $typenow, $pagenow, $current_screen;

		if ( $post && $post->post_type )
			return $post->post_type;

		if ( $typenow )
			return $typenow;

		if ( $current_screen && isset( $current_screen->post_type ) )
			return $current_screen->post_type;

		if ( isset( $_REQUEST['post_type'] ) )
			return sanitize_key( $_REQUEST['post_type'] );

		return NULL;
	}

	// FIXME: DEPRECATED
	public static function notice( $notice, $class = 'notice-success fade', $echo = TRUE )
	{
		self::__dep( 'gPluginHTML::notice()' );
		return gPluginHTML::notice( $notice, $class, $echo );
	}

	public static function getCurrentSiteBlogID()
	{
		if ( ! is_multisite() )
			return get_current_blog_id();

		return absint( get_current_site()->blog_id );
	}

	// FIXME: DEPRECATED
	public static function get_current_site_blog_id()
	{
		self::__dep( 'gPluginWPHelper::get_attachment_path()');
		return self::getCurrentSiteBlogID();
	}

	// http://kovshenin.com/2011/attachments-filename-and-directory-in-wordpress/
	// an absolute path (filesystem path, not URL) to the location of your attachment file.
	public static function get_attachment_path( $post_id, $uploads = NULL )
	{
		if ( is_null( $uploads ) )
			$uploads = wp_upload_dir();

		return str_replace( $uploads['baseurl'], $uploads['basedir'], wp_get_attachment_url( $post_id ) );
	}

	// FIXME: DROP THIS
	public static function get_attachmnet_path( $post_id, $uploads = NULL )
	{
		self::__dep( 'gPluginWPHelper::get_attachment_path()');
		return self::get_attachment_path( $post_id, $uploads );
	}

	// http://www.stephenharris.info/2012/get-post-content-by-id/
	// Display the post content. Optinally allows post ID to be passed
	/**
	 * @param int $id Optional. Post ID.
	 * @param string $more_link_text Optional. Content for when there is more text.
	 * @param bool $stripteaser Optional. Strip teaser content before the more text. Default is FALSE.
	 */
	public static function the_content_by_id( $post_id = 0, $more_link_text = NULL, $stripteaser = FALSE )
	{
		global $post;
		$post = get_post( $post_id );
		setup_postdata( $post, $more_link_text, $stripteaser );
		the_content();
		wp_reset_postdata( $post );
	}


	// PROBABLY : the infinite loop!!
	// FROM: EDD
	// _edd_die_handler()
	public static function _die_handler()
	{
		if ( defined( 'GPLUGIN_UNIT_TESTS' ) )
			return array( __CLASS__, '_die_handler' );
		else
			die();
	}

	/**
	 * Wrapper function for wp_die(). This function adds filters for wp_die() which
	 * kills execution of the script using wp_die(). This allows us to then to work
	 * with functions using edd_die() in the unit tests.
	 * @author Sunny Ratilal
	 */
	// Originally from Easy Digital Downloads
	// edd_die()
	public static function _die()
	{
		add_filter( 'wp_die_ajax_handler', array( __CLASS__, '_die_handler' ), 10, 3 );
		add_filter( 'wp_die_handler', array( __CLASS__, '_die_handler' ), 10, 3 );
		wp_die('');
	}

	// Originally from Easy Digital Downloads
	// Get the current page URL
	// edd_get_current_page_url()
	public static function get_current_page_url()
	{
		if ( is_front_page() ) {
			$page_url = home_url();
		} else {
			$page_url = 'http';

		if ( isset( $_SERVER["HTTPS"] ) && $_SERVER["HTTPS"] == "on" )
			$page_url .= "s";

		$page_url .= "://";

		if ( $_SERVER["SERVER_PORT"] != "80" )
			$page_url .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		else
			$page_url .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		}

		return esc_url( $page_url );
	}

	public static function isDebug()
	{
		if ( WP_DEBUG && WP_DEBUG_DISPLAY && ! self::isDev() )
			return TRUE;

		return FALSE;
	}

	public static function isDev()
	{
		if ( defined( 'WP_STAGE' )
			&& 'development' == constant( 'WP_STAGE' ) )
				return TRUE;

		return FALSE;
	}

	public static function isFlush()
	{
		if ( isset( $_GET['flush'] ) )
			return did_action( 'init' ) && current_user_can( 'publish_posts' );

		return FALSE;
	}

	public static function isAJAX()
	{
		return defined( 'DOING_AJAX' ) && DOING_AJAX;
	}

	public static function isCRON()
	{
		return defined( 'DOING_CRON' ) && DOING_CRON;
	}

	public static function isCLI()
	{
		return defined( 'WP_CLI' ) && WP_CLI;
	}

	// FIXME: DEPRECATED
	public static function is_debug()
	{
		self::__dep( 'gPluginWPHelper::isDebug()' );

		if ( WP_DEBUG && WP_DEBUG_DISPLAY && ! self::is_dev() )
			return TRUE;

		return FALSE;
	}

	// FIXME: DEPRECATED
	public static function is_dev()
	{
		self::__dep( 'gPluginWPHelper::isDev()' );

		if ( defined( 'WP_STAGE' )
			&& 'development' == constant( 'WP_STAGE' ) )
				return TRUE;

		return FALSE;
	}

	// FROM: EDD
	function is_caching()
	{
		return ( function_exists( 'wpsupercache_site_admin' ) || defined( 'W3TC' ) );
		//return apply_filters( 'edd_is_caching_plugin_active', $caching );
	}

	// FIXME: DEPRECATED: TYPO
	public static function getUserRols( $object = FALSE )
	{
		self::__dep( 'gPluginWPHelper::getUserRoles()' );
		return self::getUserRoles( $object );
	}

	public static function getUserRoles( $object = FALSE )
	{
		$roles = $object ? new stdClass : array();

		foreach ( get_editable_roles() as $role_name => $role )

			if ( $object )
				$roles->{$role_name} = translate_user_role( $role['name'] );

			else
				$roles[$role_name] = translate_user_role( $role['name'] );

		return $roles;
	}

	// must add `add_thickbox()` for thickbox
	public static function getFeaturedImageHTML( $post_id, $size = 'thumbnail', $link = TRUE )
	{
		if ( ! $post_thumbnail_id = get_post_thumbnail_id( $post_id ) )
			return '';

		if ( ! $post_thumbnail_img = wp_get_attachment_image_src( $post_thumbnail_id, $size ) )
			return '';

		$image = gPluginHTML::tag( 'img', array( 'src' => $post_thumbnail_img[0] ) );

		if ( ! $link )
			return $image;

		return gPluginHTML::tag( 'a', array(
			'href'   => wp_get_attachment_url( $post_thumbnail_id ),
			'title'  => get_the_title( $post_thumbnail_id ),
			'class'  => 'thickbox',
			'target' => '_blank',
		), $image );
	}

	public static function getFeaturedImage( $post_id, $size = 'thumbnail', $default = FALSE )
	{
		if ( ! $post_thumbnail_id = get_post_thumbnail_id( $post_id ) )
			return $default;

		$post_thumbnail_img = wp_get_attachment_image_src( $post_thumbnail_id, $size );
		return $post_thumbnail_img[0];
	}

	// FIXME: DEPRECATED
	public static function get_featured_image_src( $post_id, $size = 'thumbnail', $default = FALSE )
	{
		self::__dep( 'gPluginWPHelper::getFeaturedImage()' );
		return self::getFeaturedImage( $post_id, $size, $default );
	}

	// @SEE: get_edit_term_link()
	public static function getEditTaxLink( $taxonomy, $term_id = FALSE, $extra = array() )
	{
		if ( $term_id )
			return add_query_arg( array_merge( array(
				'taxonomy' => $taxonomy,
				'tag_ID'   => $term_id,
			), $extra ), admin_url( 'term.php' ) );

		else
			return add_query_arg( array_merge( array(
				'taxonomy' => $taxonomy,
			), $extra ), admin_url( 'edit-tags.php' ) );
	}

	// @SEE: get_search_link()
	public static function getSearchLink( $query = FALSE )
	{
		if ( defined( 'GNETWORK_SEARCH_REDIRECT' ) && GNETWORK_SEARCH_REDIRECT )
			return $query ? add_query_arg( GNETWORK_SEARCH_QUERYID, urlencode( $query ), GNETWORK_SEARCH_URL ) : GNETWORK_SEARCH_URL;

		return $query ? add_query_arg( 's', urlencode( $query ), get_option( 'home' ) ) : get_option( 'home' );
	}

	public static function getUserEditLink( $user_ID )
	{
		return add_query_arg( 'wp_http_referer', urlencode( stripslashes( $_SERVER['REQUEST_URI'] ) ), 'user-edit.php?user_id='.$user_ID );
	}

	// FIXME: DEPRECATED
	public static function get_user_edit_link( $user_ID )
	{
		self::__dep( 'gPluginWPHelper::getUserEditLink()' );
		return self::getUserEditLink( $user_ID );
	}

	// https://gist.github.com/boonebgorges/4165099
	// Are we looking at the WordPress admin?
	// Because AJAX requests are sent to wp-admin/admin-ajax.php,
	// WordPress's is_admin() function returns TRUE when DOING_AJAX.
	// This function contains logic to test whether AJAX requests are
	// coming from the front end or from the Dashboard.
	public static function is_admin()
	{
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			// wp_get_referer() should contain the URL of the requesting
			// page. Check to see whether it's an admin page
			$is_admin = 0 === strpos( wp_get_referer(), admin_url() );
		} else {
			$is_admin = is_admin();
		}

		return $is_admin;
	}

	/**
	* Generates a domain-mapping safe URL on WordPress.com
	* Core's ajaxurl uses admin_url() which returns *.wordpress.com which
	* doesn't work for the front-end on domain-mapped sites.
	* This works around that and generates the correct URL based on context.
	*/
	// https://gist.github.com/tollmanz/1986992
	function admin_ajax_url( $path = '' )
	{
		if ( is_admin() )
			$url = admin_url( 'admin-ajax.php' );
		else
			$url = home_url( 'wp-admin/admin-ajax.php' );

		$url .= ltrim( $path, '/' );

		return $url;
	}

	public static function whiteListed( $request_uri = NULL )
	{
		if ( is_null( $request_uri ) )
			$request_uri = $_SERVER['REQUEST_URI'];

		return gPluginUtils::strposArray( $request_uri, array(
			'wp-admin',
			'wp-activate.php',
			'wp-comments-post.php',
			'wp-cron.php',
			'wp-links-opml.php',
			'wp-login.php',
			'wp-mail.php',
			'wp-signup.php',
			'wp-trackback.php',
			'xmlrpc.php',
		) );
	}

	// FIXME: DEPRECATED
	function redirect_whitelist( $request_uri = NULL )
	{
		self::__dep( 'gPluginWPHelper::whiteListed()' );
		return self::whiteListed( $request_uri );
	}

	public static function isMinWPv( $minimum_version )
	{
		return ( version_compare( get_bloginfo( 'version' ), $minimum_version ) >= 0 );
	}

	public static function getUsers( $all_fields = FALSE, $network = FALSE, $extra = array() )
	{
		$users = get_users( array_merge( array(
			'blog_id' => ( $network ? '' : $GLOBALS['blog_id'] ),
			'orderby' => 'display_name',
			'fields'  => ( $all_fields ? 'all_with_meta' : 'all' ),
		), $extra ) );

		return gPluginUtils::reKey( $users, 'ID' );
	}

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
/// NOT USED YET ---------------------------------------------------------------

	// http://austin.passy.co/2014/native-wordpress-loading-gifs/
	// https://make.wordpress.org/core/2015/04/23/spinners-and-dismissible-admin-notices-in-4-2/
	// img : spinner / wpspin_light
	public static function imgSpin( $img = 'spinner', $large = FALSE  )
	{
		return esc_url( admin_url( 'images/'.$img.( $large ? '-2x' : '' ).'.gif' ) );
	}

	public static function is_plugin_active( $plugin )
	{
		return in_array( $plugin, (array) get_option( 'active_plugins', array() ) ) || self::is_plugin_active_for_network( $plugin );
	}

	public static function is_plugin_active_for_network( $plugin )
	{
		if ( ! is_multisite() )
			return FALSE;

		$plugins = get_site_option( 'active_sitewide_plugins' );
		if ( isset( $plugins[$plugin] ) )
			return TRUE;

		return FALSE;
	}

	public static function getRequestURI()
	{
		return stripslashes_deep( $_SERVER['REQUEST_URI'] );
	}
} }
