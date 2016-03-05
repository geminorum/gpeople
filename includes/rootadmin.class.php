<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

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

				add_filter( 'manage_'.$this->constants['profile_cpt'].'_posts_columns', array( $this, 'manage_posts_columns' ) );
				add_action( 'manage_'.$this->constants['profile_cpt'].'_posts_custom_column', array( $this, 'posts_custom_column' ), 10, 2 );

			} else if ( 'post' == $screen->base ) {

				add_filter( 'enter_title_here', array( $this, 'enter_title_here' ), 10, 2 );
				add_filter( 'post_updated_messages', array( $this, 'post_updated_messages' ) );
				add_action( 'add_meta_boxes_'.$this->constants['profile_cpt'], array( $this, 'add_meta_boxes' ), 12 );
			}
		}
	}

	public function admin_settings_load()
	{
		global $gPeopleNetwork;
		$sub = isset( $_REQUEST['sub'] ) ? $_REQUEST['sub'] : NULL;
		$gPeopleNetwork->importer->root_settings_load( $sub );
	}

	public function admin_print_styles()
	{
		$screen = get_current_screen();
		if ( $screen->post_type == $this->constants['profile_cpt'] )
			if ( in_array( $screen->base, array( 'edit', 'post' ) ) )
				$this->linkStyleSheet( 'profile', $screen->base );
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
			} elseif ( in_array( $key, array( 'author', 'date', 'comments' ) ) ) {
				continue; // he he!
			} else {
				$new_columns[$key] = $value;
			}
		}
		return $new_columns;
	}

	public function posts_custom_column( $column_name, $post_id )
	{
		if ( 'picture' == $column_name ) {
			if ( $picture = gPluginWPHelper::get_featured_image_src( $post_id, 'thumbnail', FALSE ) )
				echo gPluginFormHelper::html( 'img', array(
					'src' => $picture,
					// 'style' => 'max-width:50px;max-height:60px;',
				) );
		}
	}

	public function add_meta_boxes( $post )
	{
		$post_type_object = get_post_type_object( $this->constants['profile_cpt'] );

		if ( is_super_admin() || current_user_can( $post_type_object->cap->edit_others_posts ) ) {
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
		if ( $this->constants['profile_cpt'] == $post->post_type )
			return __( 'Enter name here', GPEOPLE_TEXTDOMAIN );

		return $title;
	}

	public function dashboard_glance_items( $items )
	{
		$profiles = wp_count_posts( $this->constants['profile_cpt'] );
		$count    = number_format_i18n( $profiles->publish );
		$text     = _n( 'Person', 'People', $profiles->publish, GPEOPLE_TEXTDOMAIN );
		$template = current_user_can( 'edit_posts' ) ? '<a href="edit.php?post_type=%3$s">%1$s %2$s</a>' : '%2$s %3$s';

		$items[] = sprintf( $template, $count, $text, $this->constants['profile_cpt'] );

		return $items;
	}
}
