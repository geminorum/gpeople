<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gPeoplePicture extends gPluginModuleCore
{

	public function setup_actions()
	{
		$this->switch = GPEOPLE_ROOT_BLOG != $this->current_blog;
	}

	// before: get_people_image_default()
	// before: get_default_image()
	public function get_default( $tag = FALSE )
	{
		// return '<span class="dashicons dashicons-admin-users"></span>';

		// MUST DEP FILTER
		$src = apply_filters( 'gpeople_remote_default_profile_image', GPEOPLE_URL.'assets/images/default_avatar.png' );

		if ( $tag )
			return gPluginHTML::tag( 'img', array( 'src' => $src ) );

		return $src;
	}

	// MAYBE: move to gPlugin
	// FIXME: disable duplications!!
	// FIXME: clean this up!
	// mockup of core : media_sideload_image(), media_handle_sideload()
	// CHECK: https://core.trac.wordpress.org/ticket/19629
	public function sideload( $file, $desc = NULL )
	{
		if ( ! $file )
			return FALSE;

		// Set variables for storage, fix file filename for query strings.
		preg_match( '/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $file, $matches );
		$file_array = array();
		$file_array['name'] = basename( $matches[0] );

		// Download file to temp location.
		$file_array['tmp_name'] = download_url( $file );

		// If error storing temporarily, return the error.
		if ( is_wp_error( $file_array['tmp_name'] ) )
			return $file_array['tmp_name'];

		// Do the validation and storage stuff.
		// FIXME: must upload to profile picture folder!
		$new_file = wp_handle_sideload( $file_array, array( 'test_form' => FALSE ) );

		if ( isset( $new_file['error'] ) )
			return new WP_Error( 'upload_error', $new_file['error'] );

		$url      = $new_file['url'];
		$type     = $new_file['type'];
		$new_file = $new_file['file'];
		$title    = preg_replace('/\.[^.]+$/', '', basename( $new_file ) );
		$content  = '';

		// Use image exif/iptc data for title and caption defaults if possible.
		if ( $image_meta = @ wp_read_image_metadata( $new_file ) ) {

			if ( trim( $image_meta['title'] ) && ! is_numeric( sanitize_title( $image_meta['title'] ) ) )
				$title = $image_meta['title'];

			if ( trim( $image_meta['caption'] ) )
				$content = $image_meta['caption'];
		}

		if ( $desc )
			$title = $desc;

		$attachment = array(
			'post_mime_type' => $type,
			'guid'           => $url,
			'post_parent'    => 0,
			'post_title'     => $title,
			'post_content'   => $content,
		);

		$id = wp_insert_attachment( $attachment, $new_file );

		if ( is_wp_error( $id ) ) {

			@ unlink( $file_array['tmp_name'] );
			return $id;

		} else {

			$metadata = wp_generate_attachment_metadata( $id, $file );
			wp_update_attachment_metadata( $id, $metadata );
		}

		// $src = wp_get_attachment_url( $id );

		return $id;
	}

	// FIXME: DEPRECATED
	// for remote blog by term id or term_meta
	// OLD: get_people_image()
	public function get( $term_id_or_meta, $size = 'thumbnail', $tag = FALSE, $atts = '' )
	{
		self::__dep();

		global $gPeopleNetwork;

		if ( is_array( $term_id_or_meta ) )
			$images = ( isset( $term_id_or_meta['profile-images'] ) ? $term_id_or_meta['profile-images'] : array() );
		else
			$images = $gPeopleNetwork->remote->get_termmeta( $term_id_or_meta, 'profile-images', array() );

		if ( count ( $images ) && isset( $images[$size] ) ) {
			$image = $images[$size][0];
			if ( $tag )
				return '<img src="'.$images[$size][0].'" style="width:'.$images[$size][1].'px;height:'.$images[$size][2].'px" '.$atts.'/>';
			else
				return $images[$size][0];
		}

		return FALSE;
	}
}
