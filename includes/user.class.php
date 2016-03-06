<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gPeopleUser extends gPluginModuleCore
{

	public function setup_actions()
	{
        $this->switch = GPEOPLE_ROOT_BLOG != $this->current_blog;
	}

	// before : get_remote_user_data()
	public function get_data( $user, $pre_data = array() )
	{
		global $gPeopleNetwork;

		$user_images = array();
		$user_thumbnail = isset( $user_images['thumbnail'] ) ? $user_images['thumbnail'][0] : $gPeopleNetwork->picture->get_default();
		$has_excerpt = ( ( isset( $user->description ) && ! empty( $user->description )  )? $user->description : FALSE );

		$data = array_merge( $pre_data, array(
			'id'          => $user->ID,
			'title'       => $user->display_name,
			'name'        => $user->user_nicename,
			'has_excerpt' => $has_excerpt,
			'excerpt'     => $has_excerpt
				? wpautop( apply_filters( 'get_the_excerpt', $has_excerpt ) )
				: '<p>'.__( '<i>No Profile Summary</i>', GPEOPLE_TEXTDOMAIN ).'</p>',
			'link'        => $user->user_url,
			'edit'        => gPluginWPHelper::getUserEditLink( $user->ID ),
			'images'      => $user_images,
			'thumbnail'   => $user_thumbnail,
			// 'groups'      => get_the_term_list( $profile->ID, $this->constants['group_tax'], __( 'Groups: ', GPEOPLE_TEXTDOMAIN ), __( ', ', GPEOPLE_TEXTDOMAIN ), '' ),
			'role'        => $user->user_level, // translate_user_role( $role['name'] );
		) );

		return $data;
	}

	public function get_link( $term_id, $tag = FALSE )
	{
		global $gPeopleNetwork;

		$term_meta = $gPeopleNetwork->remote->get_termmeta( $term_id, FALSE, array() );
		$link      = '';

		if ( isset( $term_meta['user-id'] ) && $term_meta['user-id'] )
			$user_id = $term_meta['user-id'];

		else if ( $term_meta_user_id = get_term_meta( $term_id, 'people_user_id', TRUE ) )
			$user_id = $term_meta_user_id;

		else
			return $link;

		$link = gPluginWPHelper::getUserEditLink( $user_id );

		if ( FALSE === $tag ) {
			return $link;
		} else if ( TRUE === $tag ) {
			$tag = __( 'User', GPEOPLE_TEXTDOMAIN );
		} else {
			$userdata = get_user_by( 'id', intval( $user_id ) );
			$tag = $userdata ? $userdata->display_name : _x( '(Not Found)', 'User not found', GPEOPLE_TEXTDOMAIN );
		}

		return '<a href="'.esc_url( $link ).'" class="user-link" target="_blank">'.$tag.'</a>';
	}
}
