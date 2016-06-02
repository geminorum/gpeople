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
			'ip'      => gPluginUtils::IP(),
			'message' => ( is_null( $wp_error ) ? '{NO WP_Error Object}' : $wp_error->get_error_message() ),
		), $data );

		error_log( print_r( $log, TRUE ) );
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

			} else if ( is_array( $_wp_theme_features[$feature][0] ) ){
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
		global $wpdb;

		if ( $url ) {
			$slug = rawurlencode( urldecode( $slug ) );
			$slug = sanitize_title( basename( $slug ) );
		}

		$post_id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT ID FROM $wpdb->posts WHERE post_name = %s AND post_type = %s",
				trim( $slug ),
				$post_type
			)
		);

		if ( is_array( $post_id ) )
			return $post_id[0];

		else if ( ! empty( $post_id ) )
			return $post_id;

		return FALSE;
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

	public static function notice( $notice, $class = 'updated fade', $echo = TRUE )
	{
		$html = sprintf( '<div id="message" class="%s notice is-dismissible"><p>%s</p></div>', $class, $notice );

		if ( ! $echo )
			return $html;

		echo $html;
	}

	// FIXME: use new wp core function
	public static function get_current_site_blog_id()
	{
		if ( ! is_multisite() )
			return get_current_blog_id();

		global $current_site;
		return absint( $current_site->blog_id );
	}

	// http://kovshenin.com/2011/attachments-filename-and-directory-in-wordpress/
	// an absolute path (filesystem path, not URL) to the location of your attachment file.
	public static function get_attachmnet_path( $post_id, $uploads = NULL )
	{
		if ( is_null( $uploads ) )
			$uploads = wp_upload_dir();

		return str_replace( $uploads['baseurl'], $uploads['basedir'], wp_get_attachment_url( $post_id ) );
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

	// TODO: use nonce
	public static function isFlush()
	{
		if ( isset( $_GET['flush'] ) )
			return TRUE;

		if ( defined( 'GTHEME_FLUSH' ) && GTHEME_FLUSH )
			return TRUE;

		return FALSE;
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
	public static function getEditTaxLink( $taxonomy, $term_id = FALSE )
	{
		if ( $term_id )
			return add_query_arg( array(
				'taxonomy' => $taxonomy,
				'tag_ID'   => $term_id,
			), admin_url( 'term.php' ) );

		else
			return add_query_arg( array(
				'taxonomy' => $taxonomy,
			), admin_url( 'edit-tags.php' ) );
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
	// Because AJAX requests are sent to wp-admin/admin-ajax.php, WordPress's is_admin() function returns TRUE when DOING_AJAX. This function contains logic to test whether AJAX requests are coming from the front end or from the Dashboard.
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
	* Core's ajaxurl uses admin_url() which returns *.wordpress.com which doesn't work for the front-end on domain-mapped sites.
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

	function redirect_whitelist( $request_uri = NULL )
	{
		if ( is_null( $request_uri ) )
			$request_uri = $_SERVER['REQUEST_URI'];

		return gPluginUtils::strposArray( $request_uri, array(
			'wp-login.php',
			'wp-signup.php',
			'wp-activate.php',
			'xmlrpc.php',
			'wp-admin',
		) );
	}

	public static function isMinWPv( $minimum_version )
	{
		return ( version_compare( get_bloginfo( 'version' ), $minimum_version ) >= 0 );
	}

	public static function getUsers( $all_fields = FALSE, $network = FALSE )
	{
		$users = get_users( array(
			'blog_id' => ( $network ? '' : $GLOBALS['blog_id'] ),
			'orderby' => 'display_name',
			'fields'  => ( $all_fields ? 'all_with_meta' : 'all' ),
		) );

		return gPluginUtils::reKey( $users, 'ID' );
	}

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
/// NOT USED YET ---------------------------------------------------------------

	// img : spinner / wpspin_light
	public static function imgSpin( $img = 'spinner', $large = FALSE  )
	{
		return esc_url( admin_url( 'images/'.$img.( $large ? '-2x' : '' ).'.gif' ) );
	}

	// based on bp
	public static function username_from_email( $email, $strict = TRUE )
	{
		return preg_replace( '/\s+/', '', sanitize_user( preg_replace( '/([^@]*).*/', '$1', $email ), $strict ) );
	}

	// UNFINISHED
	// update post meta by array
	// build for speed!
	public static function update_post_meta( $post_id, $meta_array )
	{
		// make sure meta is added to the post, not a revision
		if ( $the_post = wp_is_post_revision( $post_id ) )
			$post_id = $the_post;

		return update_metadata( 'post', $post_id, $meta_key, $meta_value, '' );
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

	// NOT WORKING!!!! ON ADMIN
	// http://kovshenin.com/2012/current-url-in-wordpress/
	// http://www.stephenharris.info/2012/how-to-get-the-current-url-in-wordpress/
	public static function getCurrentURL( $trailingslashit = FALSE )
	{
		global $wp;

		if ( is_admin() )
			$current_url = add_query_arg( $wp->query_string, '', home_url( $wp->request ) );
		else
			$current_url = home_url( add_query_arg( array(), ( empty( $wp->request ) ? FALSE : $wp->request ) ) );

		if ( $trailingslashit )
			return gPluginUtils::trail( $current_url );

		return $current_url;
	}

	public static function getRequestURI()
	{
		return stripslashes_deep( $_SERVER['REQUEST_URI'] );
	}

	// http://gilbert.pellegrom.me/wordpress-get_blog_url
	// Have you ever needed to find the WordPress blog URL? Using home_url() is fine but what if your Settings > Reading options
	// in WordPress are set to your blog having a static page (usually called �Blog�). You may need to know what the URL of that page is.
	// So here is a quick function I came up with to find it.
	public static function get_blog_url()
	{
		if ( $posts_page_id = get_option( 'page_for_posts' ) ){
			return home_url( get_page_uri( $posts_page_id ) );
		} else {
			return home_url();
		}
	}

	// http://webdevstudios.com/2013/04/03/how-to-quickly-grab-post-fields-outside-the-loop-with-get_post_field-in-wordpress/
	// http://codex.wordpress.org/Function_Reference/get_post#Return
	public static function get_post_field( $field, $post, $default = '', $context = 'display' )
	{
		$post = get_post( $post );

		if ( ! $post )
			return $default;

		if ( ! isset( $post->$field ) )
			return $default;

		return sanitize_post_field( $field, $post->$field, $post->ID, $context );
	}

	public static function get_updates( $basename )
	{
		$updates = get_plugin_updates();
		//$basename = plugin_basename(__FILE__);
		if ( isset( $updates[$basename] ) ) {
			$update = $updates[$basename];
			//echo '<div class="error"><p><strong>';
			//printf( __( 'A new version of this importer is available. Please update to version %s to ensure compatibility with newer export files.', 'wordpress-importer' ), $update->update->new_version );
			//echo '</strong></p></div>';
			return $update->update->new_version;
		}
		return FALSE;
	}

	// Originally from : http://wordpress.org/extend/plugins/categories-autolink/
	function linkify( $text, $terms )
	{
		foreach ( $terms as $name => $link )
			$text = preg_replace( "|(?!<[^<>]*?)(?<![?./&])\b($name)\b(?!:)(?![^<>]*?>)|imsU", "<a href=\"$link\">$1</a>", $text );
		return $text;
	}

	// UNFINISHED!!
	function hashify( $text, $callback = FALSE )
	{
		// http://stackoverflow.com/a/7408417/642752
		// Assuming your strings are common CSS names (alphanumeric + dash)
		// $text = preg_replace( '/(#[\w-]+)/', '$1' . $stringtoappend, $text );

		// http://stackoverflow.com/questions/11138191/preg-match-all-after-hash-tag-before-next-hash-tag-in-a-string

	}

	// UNFINISHED!!
	function mentionify( $text, $callback = FALSE )
	{
		// http://stackoverflow.com/a/10384173/642752
		// $text = preg_replace('/@([^@ ]+)/', '<a href="/$1">@$1</a> ', $text );

		// http://stackoverflow.com/a/10384251/642752
		// '@name1 kdfjd fkjd as@name2 @ lkjlkj @name3'
		// preg_match_all('/(^|\s)(@\w+)/', $text, $result );
		// var_dump($result[2]);
		// http://ideone.com/AcXO3


		// http://stackoverflow.com/questions/7150652/regex-valid-twitter-mention
		// http://stackoverflow.com/questions/6673944/regular-expression-to-find-and-replace-mentions-like-twitter

		// BETTER :
		// http://stackoverflow.com/questions/9465486/php-preg-replace-regex-mention-username

	}
} }
