<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

// http://wp-snippet.com/snippets/author-list-with-avatar-thumbnail/
// http://vip.wordpress.com/documentation/incorporate-co-authors-plus-template-tags-into-your-theme/

// http://codex.wordpress.org/Template_Tags/get_the_author
// http://codex.wordpress.org/Function_Reference/the_author
// http://codex.wordpress.org/Function_Reference/the_author_posts_link

class gPeopleRemoteTemplate extends gPluginTemplateCore
{

	public static function post_byline( $post_id = NULL, $atts = array(), $cache = TRUE, $walker = NULL )
	{
		global $gPeopleNetwork, $post;

		if ( is_null( $post_id ) )
			$post_id = $post->ID;

		// if on non-enabled remote blog
		if ( FALSE == $gPeopleNetwork->remote ) {
			if ( isset( $atts['echo'] ) ) {
				if ( ! $atts['echo'] ) {
					if ( isset( $atts['default'] ) )
						return $atts['default'];
					return FALSE;
				}
			}
			return;
		}

		$byline = $gPeopleNetwork->remote->get_people( $post_id, $atts, $walker );

		if ( isset( $atts['echo'] ) ) {
			if ( ! $atts['echo'] )
				return $byline;
		}

		echo $byline;
	}

	public static function person_image( $term_id = NULL, $atts = array(), $cache = TRUE )
	{
		global $gPeopleNetwork;

		if ( is_null( $term_id ) ) {
			$term = get_queried_object();

			if ( $term && is_tax() )
				$term_id = $term->term_id;
		}

		if ( $term_id ) {

			if ( ! isset( $atts['size'] ) )
				$atts['size'] = 'thumbnail';

			if ( $src = $gPeopleNetwork->picture->get( $term_id, $atts['size'] ) ) {

				if ( isset( $atts['atts'] ) )
					$tag_atts = $atts['atts'];
				else
					$tag_atts = array();

				$tag_atts['src'] = $src;
				$tag = gPluginHTML::tag( 'img', $tag_atts );

				if ( isset( $atts['echo'] ) ) {
					if ( ! $atts['echo'] )
						return $tag;
				}

				echo $tag;

			} else if ( isset( $atts['default'] ) ) {

				if ( isset( $atts['echo'] ) ) {
					if ( ! $atts['echo'] )
						return FALSE;
					echo $atts['default'];
				}
			}
		} else if ( isset( $atts['default'] ) ) {

			if ( isset( $atts['echo'] ) ) {
				if ( ! $atts['echo'] )
					return FALSE;
				echo $atts['default'];
			}
		}
	}

	// JUSTACOPY
	// http://www.billerickson.net/wordpress-post-multiple-authors/
	public static function person_box( $id = FALSE )
	{
		if ( ! $id )
			return;

		$authordata    = get_userdata( $id );
		$gravatar_size = apply_filters( 'genesis_author_box_gravatar_size', 70, $context );
		$gravatar      = get_avatar( get_the_author_meta( 'email', $id ), $gravatar_size );
		$title         = apply_filters( 'genesis_author_box_title', sprintf( '<strong>%s %s</strong>', __( 'About', GPEOPLE_TEXTDOMAIN ), get_the_author_meta( 'display_name', $id ) ), $context );
		$description   = wpautop( get_the_author_meta( 'description', $id ) );

		/** The author box markup, contextual */
		$pattern = $context == 'single' ? '<div class="author-box"><div>%s %s<br />%s</div></div><!-- end .authorbox-->' : '<div class="author-box">%s<h1>%s</h1><div>%s</div></div><!-- end .authorbox-->';

		echo apply_filters( 'genesis_author_box', sprintf( $pattern, $gravatar, $title, $description ), $context, $pattern, $gravatar, $title, $description );

	}

	// JUSTACOPY
	// http://www.billerickson.net/wordpress-post-multiple-authors/
	public static function people_box()
	{
		if ( ! is_single() )
			return;

		if ( function_exists( 'get_coauthors' ) ) {
			$authors = get_coauthors();
			foreach ( $authors as $author )
				self::person_box( $author->data->ID );
		} else {
			self::person_box( get_the_author_ID() );
		}
	}

	// JUSTACOPY
	// http://www.billerickson.net/wordpress-post-multiple-authors/
	function be_post_authors_post_link_shortcode( $atts )
	{
		$atts = shortcode_atts( array(
			'between'      => NULL,
			'between_last' => NULL,
			'before'       => NULL,
			'after'        => NULL
		), $atts );

		$authors = function_exists( 'coauthors_posts_links' ) ? coauthors_posts_links( $atts['between'], $atts['between_last'], $atts['before'], $atts['after'], FALSE ) : $atts['before'] . get_author_posts_url() . $atts['after'];
		return $authors;
	}
	//add_shortcode( 'post_authors_post_link', 'be_post_authors_post_link_shortcode' );
}

if ( ! function_exists( 'gpeople_byline' ) ) : function gpeople_byline( $post_id = NULL, $atts = array(), $cache = TRUE ) {
	return gPeopleRemoteTemplate::post_byline( $post_id, $atts, $cache );
} endif;

if ( ! function_exists( 'gpeople_person_image' ) ) : function gpeople_person_image( $term_id = NULL, $atts = array(), $cache = TRUE ) {
	return gPeopleRemoteTemplate::person_image( $term_id, $atts, $cache );
} endif;
