<?php defined( 'ABSPATH' ) || die( header( 'HTTP/1.0 403 Forbidden' ) );

class gPeopleRootAdmin extends gPluginAdminCore
{

	protected $component = 'root';

	public function admin_init()
	{
		add_filter( 'dashboard_glance_items', array( $this, 'dashboard_glance_items' ), 8 );
	}

	public function current_screen( $screen )
	{
		if ( $this->constants['profile_cpt'] == $screen->post_type ) {

			if ( 'edit' == $screen->base ) {

				$this->enqueue_style( 'profile', $screen->base );

				add_filter( 'manage_'.$screen->post_type.'_posts_columns', array( $this, 'manage_posts_columns' ) );
				add_action( 'manage_'.$screen->post_type.'_posts_custom_column', array( $this, 'posts_custom_column' ), 10, 2 );

			} else if ( 'post' == $screen->base ) {

				$this->enqueue_style( 'profile', $screen->base );

				add_filter( 'enter_title_here', array( $this, 'enter_title_here' ), 10, 2 );
				add_filter( 'post_updated_messages', array( $this, 'post_updated_messages' ) );
				add_action( 'add_meta_boxes_'.$screen->post_type, array( $this, 'add_meta_boxes' ), 12 );
			}
		}
	}

	public function admin_settings_load()
	{
		global $gPeopleNetwork;

		$sub = isset( $_REQUEST['sub'] ) ? $_REQUEST['sub'] : NULL;

		$gPeopleNetwork->importer->root_settings_load( $sub );
	}

	public function manage_posts_columns( $posts_columns )
	{
		$new_columns = array();

		foreach ( $posts_columns as $key => $value ) {

			if ( 'title' == $key ) {

				// $new_columns['picture'] = __( 'Picture', GPEOPLE_TEXTDOMAIN );
				$new_columns['picture'] = '&nbsp;';
				$new_columns[$key] = __( 'Person', GPEOPLE_TEXTDOMAIN );
				// $new_columns['meta'] = __( 'Meta', GPEOPLE_TEXTDOMAIN );

			} else if ( in_array( $key, array( 'author', 'date', 'comments' ) ) ) {
				continue; // he he!

			} else {
				$new_columns[$key] = $value;
			}
		}

		return $new_columns;
	}

	public function posts_custom_column( $column_name, $post_id )
	{
		if ( 'picture' == $column_name )
			echo gPluginWPHelper::htmlFeaturedImage( $post_id, 'thumbnail' );
	}

	public function add_meta_boxes( $post )
	{
		$post_type_object = get_post_type_object( $this->constants['profile_cpt'] );

		if ( current_user_can( $post_type_object->cap->edit_others_posts ) ) {

			remove_meta_box( 'authordiv', $this->constants['profile_cpt'], 'normal' );

			add_meta_box( 'authordiv',
				__( 'Creator', GPEOPLE_TEXTDOMAIN ),
				'post_author_meta_box',
				$this->constants['profile_cpt'],
				'side'
			);
		}

		remove_meta_box( 'postexcerpt', $this->constants['profile_cpt'], 'normal' );

		add_meta_box( 'postexcerpt',
			__( 'Profile Summary', GPEOPLE_TEXTDOMAIN ),
			'post_excerpt_meta_box',
			$this->constants['profile_cpt'],
			'normal',
			'high'
		);
	}

	public function post_updated_messages( $messages )
	{
		global $post, $post_ID;

		if ( $this->constants['profile_cpt'] == $post->post_type ) {

			$link = get_permalink( $post_ID );

			$messages[$this->constants['profile_cpt']] = array(
				0  => '', // Unused. Messages start at index 1.
				1  => sprintf( __( 'Profile updated. <a href="%s">View profile</a>', GPEOPLE_TEXTDOMAIN ), esc_url( $link ) ),
				2  => __( 'Custom field updated.' ),
				3  => __( 'Custom field deleted.' ),
				4  => __( 'Profile updated.', GPEOPLE_TEXTDOMAIN ),
				5  => isset( $_GET['revision'] ) ? sprintf( __( 'Profile restored to revision from %s', GPEOPLE_TEXTDOMAIN ), wp_post_revision_title( (int) $_GET['revision'], FALSE ) ) : FALSE,
				6  => sprintf( __( 'Profile published. <a href="%s">View profile</a>', GPEOPLE_TEXTDOMAIN ), esc_url( $link ) ),
				7  => __( 'Profile saved.', GPEOPLE_TEXTDOMAIN ),
				8  => sprintf( __( 'Profile submitted. <a target="_blank" href="%s">Preview profile</a>', GPEOPLE_TEXTDOMAIN ), esc_url( add_query_arg( 'preview', 'true', $link ) ) ),
				9  => sprintf( __( 'Profile scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview profile</a>', GPEOPLE_TEXTDOMAIN ), date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( $link ) ),
				10 => sprintf( __( 'Profile draft updated. <a target="_blank" href="%s">Preview profile</a>', GPEOPLE_TEXTDOMAIN ), esc_url( add_query_arg( 'preview', 'true', $link ) ) ),
			);
		}

		return $messages;
	}

	public function enter_title_here( $title, $post )
	{
		return _x( 'Enter name here', 'Root: Admin: Profile Title Placeholder', GPEOPLE_TEXTDOMAIN );
	}

	public function dashboard_glance_items( $items )
	{
		$profiles = wp_count_posts( $this->constants['profile_cpt'] );

		if ( ! $profiles->publish )
			return $items;

		$object = get_post_type_object( $this->constants['profile_cpt'] );
		$text   = _nx( 'Person', 'People', $profiles->publish, 'Root: Admin: At a Glance', GPEOPLE_TEXTDOMAIN );

		$template = current_user_can( $object->cap->edit_posts )
			? '<a href="edit.php?post_type=%3$s">%1$s %2$s</a>'
			: '%1$s %2$s';

		$items[] = sprintf( $template, number_format_i18n( $profiles->publish ), $text, $this->constants['profile_cpt'] );

		return $items;
	}
}
