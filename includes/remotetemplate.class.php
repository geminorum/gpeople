<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gPeopleRemoteTemplate extends gPluginTemplateCore
{

	public static function post_byline( $post = NULL, $atts = array(), $cache = TRUE, $walker = NULL )
	{
		global $gPeopleNetwork;

		$post = get_post( $post );

		// if on non-enabled remote blog
		if ( ! $post || ! $gPeopleNetwork->remote ) {

			if ( isset( $atts['echo'] ) ) {
				if ( ! $atts['echo'] ) {
					if ( isset( $atts['default'] ) )
						return $atts['default'];
					return FALSE;
				}
			}

			return;
		}

		$byline = $gPeopleNetwork->remote->get_people( $post->ID, $atts, $walker );

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
}

if ( ! function_exists( 'gpeople_byline' ) ) : function gpeople_byline( $post = NULL, $atts = array(), $cache = TRUE ) {
	return gPeopleRemoteTemplate::post_byline( $post, $atts, $cache );
} endif;

if ( ! function_exists( 'gpeople_person_image' ) ) : function gpeople_person_image( $term_id = NULL, $atts = array(), $cache = TRUE ) {
	return gPeopleRemoteTemplate::person_image( $term_id, $atts, $cache );
} endif;
