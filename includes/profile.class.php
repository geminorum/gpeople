<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gPeopleProfile extends gPluginModuleCore
{

	public function setup_actions()
	{
        $this->switch   = GPEOPLE_ROOT_BLOG != $this->current_blog;
        $this->root_url = get_blogaddress_by_id( GPEOPLE_ROOT_BLOG );
        $this->groups   = array();

		// ON REMOTE
		if ( $this->switch ) {
			if ( is_admin() ) {

				// remote: people tax bulk actions with gNetworkTaxonomy
				add_filter( 'gnetwork_taxonomy_bulk_actions', array( $this, 'taxonomy_bulk_actions' ), 12, 2 );
				add_filter( 'gnetwork_taxonomy_bulk_callback', array( $this, 'taxonomy_bulk_callback' ), 12, 3 );
			}
		}
	}

	// before: get_root_groups()
	public function get_groups()
	{
		if ( count( $this->groups ) )
			return $this->groups;

		$groups = array();
		$key    = 'gpeople_root_groups';

		if ( FALSE === ( $groups = get_site_transient( $key ) ) ) {

			if ( $this->switch ) {
				switch_to_blog( constant( 'GPEOPLE_ROOT_BLOG' ) );
				gPeopleRootComponent::switch_setup( $this->constants );
			}

			$groups = gPluginTaxonomyHelper::prepareTerms( $this->constants['group_tax'] );

			if ( $this->switch )
				restore_current_blog();

			set_site_transient( $key, $groups, 12 * HOUR_IN_SECONDS );
		}

		if ( gPluginWPHelper::isDev() || gPluginWPHelper::isFlush() )
			delete_site_transient( $key );

		$this->groups = $groups;

		return $this->groups;
	}

	// before: get_root_profile()
	public function get( $post_ID, $field = FALSE, $default = '' )
	{
		if ( $this->switch ) {
			switch_to_blog( constant( 'GPEOPLE_ROOT_BLOG' ) );
			gPeopleRootComponent::switch_setup( $this->constants );
		}

		$profile = get_post( $post_ID );
		$data = $this->get_data( $profile );

		if ( $this->switch )
			restore_current_blog();

		if ( FALSE === $field )
			return $data;

		if ( isset( $data[$field] ) )
			return $data[$field];

		return $default;
	}

	// CAUTION: must call while switched to root
	// before: get_root_profile_data()
	public function get_data( $profile, $pre_data = array() )
	{
		global $gPeopleNetwork;

		$profile = get_post( $profile );

		// DEPRECATED: we upload the attachment again in remote
		$profile_images    = $this->get_images( $profile );
		$profile_thumbnail = isset( $profile_images['thumbnail'] ) ? $profile_images['thumbnail'][0] : $gPeopleNetwork->picture->get_default();


		return array_merge( $pre_data, array(
			'id'          => $profile->ID,
			'title'       => apply_filters( 'the_title', $profile->post_title, $profile->ID ),
			'name'        => urldecode( $profile->post_name ),
			'has_excerpt' => empty( $profile->post_excerpt ) ? FALSE : $profile->post_excerpt,
			'excerpt'     => ( $profile->post_excerpt ? wpautop( apply_filters( 'get_the_excerpt', $profile->post_excerpt ) ) : '<p>'.__( '<i>No Profile Summary</i>', GPEOPLE_TEXTDOMAIN ).'</p>' ), //  wpautop( $founded_profile->excerpt

			// DEPRECATED: must use short link with post id
			'link'        => get_permalink( $profile->ID ),
			'edit'        => get_edit_post_link( $profile->ID ),

			// DEPRECATED: we upload the attachment again in remote
			'images'      => $profile_images,
			'thumbnail'   => $profile_thumbnail,

			'picture'     => $this->get_root_thumbnail( $profile->ID ),

			'groups'      => get_the_term_list(
				$profile->ID, $this->constants['group_tax'],
				__( 'Groups: ', GPEOPLE_TEXTDOMAIN ),
				__( ', ', GPEOPLE_TEXTDOMAIN ), ''
			),
		) );
	}

	// CAUTION: must call while switched to root
	public function get_root_thumbnail( $post_id )
	{
		if ( has_post_thumbnail( $post_id ) ) {
			// $picture_src = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'full' );
			// return $picture_src[0];

			return wp_get_attachment_url( get_post_thumbnail_id( $post_id ) );
		}

		return FALSE;
	}

	// DEPRICATED
	// CAUTION: must call while switched to root
	// before: get_profile_images()
	public function get_images( $post, $sizes = NULL )
	{
		$images = array();
		if ( ! has_post_thumbnail( $post->ID ) )
			return $images;

		if ( is_null( $sizes ) )
			$sizes = array( 'thumbnail', 'medium', 'large', 'full' );

		$thumbnail_id = get_post_thumbnail_id( $post->ID );
		foreach ( $sizes as $size )
			$images[$size] = wp_get_attachment_image_src( $thumbnail_id, $size );

		return $images;
	}

	public function get_link( $term_id, $tag = FALSE )
	{
		global $gPeopleNetwork;

		$term_meta = $gPeopleNetwork->remote->get_termmeta( $term_id, FALSE, array() );
		$link      = '';

		if ( isset( $term_meta['profile-link'] ) ) {
			$link =  $term_meta['profile-link'];

		} else if ( isset( $term_meta['profile-id'] ) ) {
			$link =  add_query_arg( array(
				'p' => $term_meta['profile-id'],
			), $this->root_url );

		} else if ( $term_meta_profile_id = get_term_meta( $term_id, 'people_profile_id', TRUE ) ) {
			$link =  add_query_arg( array(
				'p' => $term_meta_profile_id,
			), $this->root_url );
		} else {
			return $link;
		}

		if ( FALSE === $tag )
			return $link;

		if ( $link ) {
			if ( TRUE === $tag )
				$tag = __( 'Profile', GPEOPLE_TEXTDOMAIN );

			return '<a href="'.esc_url( $link ).'" class="profile-link" target="_blank">'.$tag.'</a>';
		}

		return '';
	}

	public function taxonomy_bulk_actions( $actions, $taxonomy )
	{
		if ( $this->constants['people_tax'] == $taxonomy ) {
			$actions['build_profile']    = __( 'Build Profile', GPEOPLE_TEXTDOMAIN );
			$actions['pic_from_profile'] = __( 'Picture from Profile', GPEOPLE_TEXTDOMAIN );
			// $actions['update_from_profile'] = __( 'Update from Profile', GPEOPLE_TEXTDOMAIN );
			// $actions['update_profile']   = __( 'Update Remote Profile', GPEOPLE_TEXTDOMAIN );
		}

		return $actions;
	}

	public function taxonomy_bulk_callback( $callback, $action, $taxonomy )
	{
		if ( $this->constants['people_tax'] == $taxonomy ) {
			if ( 'build_profile' == $action )
				return array( $this, 'bulk_build_profile' );
			else if ( 'pic_from_profile' == $action )
				return array( $this, 'bulk_pic_from_profile' );
		}

		return $callback;
	}

	public function bulk_pic_from_profile( $term_ids, $taxonomy )
	{
		global $gPeopleNetwork;

		$profiles = $pictures = array();

		if ( $this->constants['people_tax'] != $taxonomy )
			return FALSE;

		$terms = gPluginTaxonomyHelper::prepareTerms( $this->constants['people_tax'], array( 'include' => $term_ids ) );

		if ( ! count( $terms ) )
			return FALSE;

		foreach ( $terms as $term_id => $term ) {
			if ( $term_meta_profile_id = get_term_meta( $term_id, 'people_profile_id', TRUE ) )
				$profiles[$term_id] = $term_meta_profile_id;
		}

		if ( ! count( $profiles ) )
			return FALSE;

		if ( $this->switch ) {
			switch_to_blog( constant( 'GPEOPLE_ROOT_BLOG' ) );
			gPeopleRootComponent::switch_setup( $this->constants );
		}

		foreach ( $profiles as $term_id => $profile_id ) {
			if ( $picture = $this->get_root_thumbnail( $profile_id ) )
				$pictures[$term_id] = $picture;
		}

		if ( $this->switch )
			restore_current_blog();

		if ( ! count( $pictures ) )
			return FALSE;

		// http://codex.wordpress.org/media_sideload_image
		// require_once(ABSPATH . 'wp-admin/includes/media.php');
		// require_once(ABSPATH . 'wp-admin/includes/file.php');
		// require_once(ABSPATH . 'wp-admin/includes/image.php');

		foreach ( $pictures as $term_id => $picture ) {

			$attachment = $gPeopleNetwork->picture->sideload( $picture, $terms[$term_id]->name );

			if ( $attachment && ! is_wp_error( $attachment ) )
				update_term_meta( $term_id, 'people_picture_id', intval( $attachment ) );
		}

		return TRUE;
	}

	public function bulk_build_profile( $term_ids, $taxonomy )
	{
		global $gPeopleNetwork;

		if ( $this->constants['people_tax'] != $taxonomy )
			return FALSE;

		$terms = gPluginTaxonomyHelper::prepareTerms( $this->constants['people_tax'], array( 'include' => $term_ids ) );

		if ( ! count( $terms ) )
			return FALSE;

		if ( $this->switch ) {
			switch_to_blog( constant( 'GPEOPLE_ROOT_BLOG' ) );
			gPeopleRootComponent::switch_setup( $this->constants );
		}

		$profiles = array();

		foreach ( $terms as $term_id => $term ) {

			$post_id = gPluginWPHelper::getPostIDbySlug( $term->slug, $this->constants['profile_cpt'] );

			if ( ! $post_id ) {

				$new_post = array(
					'post_title'   => $term->name,
					'post_name'    => $term->slug,
					'post_content' => $term->description,
					'post_status'  => 'draft',
					'post_author'  => $gPeopleNetwork->get_site_user_id(),
					'post_type'    => $this->constants['profile_cpt'],
				);

				$post_id = wp_insert_post( $new_post );

				// TODO: must map term_id to profile post meta
			}

			$profiles[$term_id] = $this->get_data( $post_id );
		}

		if ( $this->switch )
			restore_current_blog();

		foreach ( $profiles as $term_id => $profile ) {

			// FIXME: CHECK if we have to override people_profile_id
			update_term_meta( $term_id, 'people_profile_id', intval( $profile['id'] ) );

			// cleanup old mess!!
			$term_meta = $gPeopleNetwork->remote->get_termmeta( $term_id, FALSE, array() );

			unset(
				$term_meta['profile-link'],
				$term_meta['profile-id']
			);

			$gPeopleNetwork->remote->update_meta( 'term', $term_id, ( count( $term_meta ) ? $term_meta : FALSE ), FALSE );
		}

		return TRUE;
	}

	// FIXME: must move to gplugin taxonomy helper
	public static function newPostFromTerm( $term, $taxonomy = 'category', $post_type = 'post' )
	{
		if ( ! is_object( $term ) && ! is_array( $term ) )
			$term = get_term( $term, $taxonomy );

		$new_post = array(
			'post_title'   => $term->name,
			'post_name'    => $term->slug,
			'post_content' => $term->description,
			'post_status'  => 'draft',
			'post_author'  => self::getEditorialUserID(),
			'post_type'    => $post_type,
		);

		$this->_import = TRUE;
		return wp_insert_post( $new_post );
	}
}
