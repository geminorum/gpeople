<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gPeopleRemoteAdmin extends gPluginAdminCore
{

	protected $component = 'remote';

	public function admin_settings_load()
	{
		global $gPeopleNetwork;
		$sub = isset( $_REQUEST['sub'] ) ? $_REQUEST['sub'] : 'general';

		if ( ! empty( $_POST ) ) {

			if ( 'general' == $sub && 'update' == $_POST['action'] ) {

				check_admin_referer( 'gpeople_remote_'.$sub.'-options' );

				if ( isset( $_POST['gpeople_remote'] ) && is_array( $_POST['gpeople_remote'] ) ) {

					$options = $gPeopleNetwork->remote->settings->settings_sanitize( $_POST['gpeople_remote'] );
					$result  = $gPeopleNetwork->remote->settings->update_options( $options );

					wp_redirect( add_query_arg( 'message', ( $result ? 'updated' : 'error' ), wp_get_referer() ) );
					exit();
				}
			}
		}

		if ( 'general' == $sub ) {
			add_action( 'gpeople_remote_settings_sub_general', array( $this, 'admin_settings_html' ), 10, 2 );

		} else if ( 'import_remote' == $sub ) {
			$gPeopleNetwork->importer->remote_settings_load( $sub );
			add_action( 'gpeople_remote_settings_sub_import_remote', array( $gPeopleNetwork->importer, 'remote_settings_sub' ), 10, 2 );
		}
	}

	public function admin_init()
	{
		add_filter( 'parent_file', array( $this, 'parent_file' ) );
		add_filter( 'dashboard_glance_items', array( $this, 'dashboard_glance_items' ), 8 );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 20, 2 );

		add_action( 'create_term', array( $this, 'edit_term' ), 10, 3 );
		add_action( 'edit_term', array( $this, 'edit_term' ), 10, 3 );

		// FIXME:
		// removing people tax on attachment edit screen
		// add_filter( 'attachment_fields_to_edit', array( $this, 'attachment_fields_to_edit' ), 8, 2 );
	}

	// @REF: http://codex.wordpress.org/Plugin_API/Action_Reference
	public function current_screen( $screen )
	{
		global $gPeopleNetwork;

		if ( $this->constants['affiliation_tax'] == $screen->taxonomy ) {

			if ( 'edit-tags' == $screen->base ) {

				add_filter( 'manage_edit-'.$screen->taxonomy.'_columns', array( $this, 'manage_edit_affiliation_columns' ) );
				add_action( 'manage_'.$screen->taxonomy.'_custom_column', array( $this, 'manage_affiliation_custom_column' ), 10, 3 );

			}

		} else if ( $this->constants['rel_people_tax'] == $screen->taxonomy ) {

			if ( 'edit-tags' == $screen->base ) {

				$gPeopleNetwork->relation->rel_table_action( 'gpeople_action' );

				add_action( 'after-'.$screen->taxonomy.'-table', array( $gPeopleNetwork->relation, 'after_rel_table' ) );

			}

		} else if ( $this->constants['people_tax'] == $screen->taxonomy ) {

			if ( 'edit-tags' == $screen->base ) {

				$gPeopleNetwork->colorbox();
				$this->enqueue_style( 'people', $screen->base );

				$gPeopleNetwork->remote_ajax->asset_config( 'remoteAdd', __( 'Search for People', GPEOPLE_TEXTDOMAIN ) );
				wp_enqueue_script( 'gpeople-remote-people-add', GPEOPLE_URL.'assets/js/remote.people.add.js', array( 'jquery' ), GPEOPLE_VERSION, TRUE );

				add_filter( 'manage_edit-'.$screen->taxonomy.'_columns', array( $this, 'manage_edit_people_columns' ) );
				add_action( 'manage_'.$screen->taxonomy.'_custom_column', array( $this, 'manage_people_custom_column' ), 10, 3 );
				add_action( 'quick_edit_custom_box', array( $this, 'quick_edit_custom_box' ), 10, 3 );
				add_filter( 'term_name', array( $this, 'people_term_name' ), 10, 2 );
				add_filter( $screen->taxonomy.'_row_actions', array( $this, 'people_row_actions' ), 12, 2 );
				add_action( 'after-'.$screen->taxonomy.'-table', array( $this, 'after_people_table' ) );

				// remote: people tax bulk actions with gNetworkTaxonomy
				add_filter( 'gnetwork_taxonomy_bulk_actions', array( $gPeopleNetwork->profile, 'taxonomy_bulk_actions' ), 12, 2 );
				add_filter( 'gnetwork_taxonomy_bulk_callback', array( $gPeopleNetwork->profile, 'taxonomy_bulk_callback' ), 12, 3 );

				add_action( $screen->taxonomy.'_pre_add_form', array( $gPeopleNetwork->people, 'people_pre_add_form' ) );
				add_action( $screen->taxonomy.'_add_form_fields', array( $gPeopleNetwork->people, 'people_add_form_fields' ) );

				add_action( 'admin_footer', array( $this, 'modal_html_edit' ) );

			} else if ( 'term' == $screen->base ) {

				$gPeopleNetwork->colorbox();
				$this->enqueue_style( 'people', $screen->base );

				$gPeopleNetwork->remote_ajax->asset_config( 'remoteEdit', __( 'Search for People', GPEOPLE_TEXTDOMAIN ) );
				wp_enqueue_script( 'gpeople-remote-people-edit', GPEOPLE_URL.'assets/js/remote.people.edit.js', array( 'jquery' ), GPEOPLE_VERSION, TRUE );

				add_action( $screen->taxonomy.'_edit_form_fields', array( $gPeopleNetwork->people, 'people_edit_form_fields' ) );

				add_action( 'admin_footer', array( $this, 'modal_html_edit' ) );
			}

		} else if ( in_array( $screen->post_type, $gPeopleNetwork->remote->supported_post_types ) ) {

			if ( 'edit' == $screen->base ) {

				// $gPeopleNetwork->colorbox();
				$this->enqueue_style( 'people', $screen->base );

				add_filter( 'manage_'.$screen->post_type.'_posts_columns', array( $this, 'manage_posts_columns' ), 20 );
				add_filter( 'manage_'.$screen->post_type.'_posts_custom_column', array( $this, 'custom_column'), 10, 2 );

				add_action( 'geditorial_tweaks_column_row', array( $this, 'column_row_people' ), -100 );

			} else if ( 'post' == $screen->base ) {

				$gPeopleNetwork->colorbox();
				$this->enqueue_style( 'people', $screen->base );

				$gPeopleNetwork->remote_ajax->asset_config( 'remotePost', __( 'People Manegment', GPEOPLE_TEXTDOMAIN ) );

				wp_deregister_script( 'jquery-form' );
				wp_register_script( 'jquery-form', GPEOPLE_URL.'assets/js/jquery.form.min.js', array( 'jquery' ), '3.51', TRUE );
				wp_enqueue_script( 'gpeople-remote-post-meta', GPEOPLE_URL.'assets/js/remote.people.post.js', array( 'jquery', 'jquery-form' ), GPEOPLE_VERSION, TRUE );

				add_action( 'admin_footer', array( $this, 'modal_html_post' ) );
			}
		}
	}

	public function admin_menu()
	{
		parent::admin_menu();

		$affiliation_tax = get_taxonomy( $this->constants['affiliation_tax'] );
		add_users_page(
			esc_attr( $affiliation_tax->labels->menu_name ),
			esc_attr( $affiliation_tax->labels->menu_name ),
			$affiliation_tax->cap->manage_terms,
			'edit-tags.php?taxonomy='.$affiliation_tax->name
		);

		$rel_people_tax = get_taxonomy( $this->constants['rel_people_tax'] );
		add_users_page(
			esc_attr( $rel_people_tax->labels->menu_name ),
			esc_attr( $rel_people_tax->labels->menu_name ),
			$rel_people_tax->cap->manage_terms,
			'edit-tags.php?taxonomy='.$rel_people_tax->name
		);
	}

	public function parent_file( $parent_file = '' )
	{
		global $pagenow;

		if ( ! empty( $_GET['taxonomy'] )
			&& ( $_GET['taxonomy'] == $this->constants['affiliation_tax']
				|| $_GET['taxonomy'] == $this->constants['rel_people_tax'] )
			&& ( $pagenow == 'edit-tags.php'
				|| $pagenow == 'term.php' ) )
					$parent_file = 'users.php';

		return $parent_file;
	}

	public function attachment_fields_to_edit( $form_fields, $post )
	{
		unset( $form_fields[$this->constants['people_tax']] );
		return $form_fields;
	}

	public function add_meta_boxes( $post_type, $post )
	{
		global $gPeopleNetwork;

		if ( in_array( $post_type, $gPeopleNetwork->remote->supported_post_types ) ) {

			$title = _x( 'People', 'add_meta_boxes', GPEOPLE_TEXTDOMAIN );

			if ( current_user_can( 'manage_categories' ) )
				$title .= ' <span class="postbox-title-action gpeople-postbox-title-action"><a href="'
					.esc_url( gPluginWPHelper::getEditTaxLink( $this->constants['people_tax'] ) )
					.'" target="_blank">'._x( 'Management', 'add_meta_boxes', GPEOPLE_TEXTDOMAIN ).'</a></span>';

			add_meta_box( 'gpeople-people', $title, array( $this, 'do_meta_box' ), $post_type, 'side', 'high' );
		}
	}

	public function do_meta_box( $post )
	{
		global $gPeopleNetwork;

		echo '<div class="gpeople-admin-wrap-metabox remote-post">';

		$byline = $gPeopleNetwork->remote->get_people( $post->ID );

		echo gPluginHTML::tag( 'div', array(
			'id'    => 'gpeople_saved_byline',
			'class' => 'metabox-row byline',
			'title' => _x( 'Byline as Appears on Your Site', 'Remote: Admin Meta Box: Title Attr', GPEOPLE_TEXTDOMAIN ),
			'style' => $byline ? FALSE : 'display:none;',
		), $byline );

		$html = gPluginHTML::tag( 'a', array(
			'id'    => 'gpeople-meta-add-people',
			'href'  => '#',
			'class' => 'gpeople-modal-open button',
			'title' => _x( 'Add or Modify People for This Post', 'Remote: Admin Meta Box: Title Attr', GPEOPLE_TEXTDOMAIN ),
		), '<span class="dashicons dashicons-groups"></span>'
			._x( 'Add People', 'Remote: Admin Meta Box: Button Text', GPEOPLE_TEXTDOMAIN ) );

		echo gPluginHTML::tag( 'div', array(
			'class' => 'metabox-row metabox-action',
		), $html );

		if ( gPluginWPHelper::isDev() )
			echo get_the_term_list( $post->ID, $this->constants['people_tax'],
				'<ul class="metabox-row metabox-list"><li>', '</li><li>', '</li></ul>' );

		echo '</div>';
	}

	public function dashboard_glance_items( $items )
	{
		$people = wp_count_terms( $this->constants['people_tax'] );
		$text   = _nx( 'Person', 'People', $people, 'Remote: Admin: At a Glance', GPEOPLE_TEXTDOMAIN );
		$count  = number_format_i18n( $people );
		$template = current_user_can( 'manage_categories' ) ? '<a href="edit-tags.php?taxonomy=%3$s">%1$s %2$s</a>' : '%1$s %2$s';

		$items[] = sprintf( $template, $count, $text, $this->constants['people_tax'] );

		return $items;
	}

	// affiliation tax edit screen
	public function manage_edit_affiliation_columns( $columns )
	{
		unset( $columns['posts'] );
		$columns['people'] = _x( 'People', 'Column', GPEOPLE_TEXTDOMAIN );
		return $columns;
	}

	// affiliation tax edit screen
	public function manage_affiliation_custom_column( $empty, $column, $term_id )
	{
		if ( 'people' === $column )
			if ( $term = get_term( $term_id, $this->constants['affiliation_tax'] ) )
				return $term->count;

		return $empty;
	}

	public function manage_posts_columns( $posts_columns )
	{
		$new_columns = array();

		foreach ( $posts_columns as $key => $value ) {

			if ( 'author' == $key )
				$new_columns['gpeople'] = _x( 'People', 'admin post column', GPEOPLE_TEXTDOMAIN );
			else
				$new_columns[$key] = $value;
		}

		return $new_columns;
	}

	public function custom_column( $column_name, $post_id )
	{
		if ( 'gpeople' != $column_name )
			return;

		global $gPeopleNetwork, $post;

		echo $gPeopleNetwork->remote->get_people( $post->ID );

		echo '<br />';

		printf( _x( '<small><a href="%s">%s</a></small> ', 'post people column', GPEOPLE_TEXTDOMAIN ),
			esc_url( add_query_arg( array(
				'post_type' => $post->post_type,
				'author' => get_the_author_meta( 'ID' )
			), 'edit.php' ) ),
			get_the_author()
		);
	}

	public function column_row_people( $post )
	{
		global $gPeopleNetwork;

		if ( $people = $gPeopleNetwork->remote->get_people( $post->ID ) ) {
			echo '<li class="-row people">';

				echo '<span class="-icon" title="'
					.esc_attr_x( 'People', 'Remote Admin: Row Icon Title', GPEOPLE_TEXTDOMAIN )
					.'"><span class="dashicons dashicons-admin-users"></span></span>';

					echo $people;
			echo '</li>';
		}
	}

	// people tax edit screen
	public function manage_edit_people_columns( $columns )
	{
		$new_columns = array();

		foreach ( $columns as $key => $value ) {

			if ( 'name' == $key ) {

				$new_columns[$key] = _x( 'Person', 'Root: Admin: Column Title', GPEOPLE_TEXTDOMAIN );

			} else if ( 'description' == $key
				|| 'gnetwork_description'  == $key ) {

				$new_columns[$key] = _x( 'Short Bio', 'Root: Admin: Column Title', GPEOPLE_TEXTDOMAIN );

			} else if ( 'slug' == $key ) {

				$new_columns['people-extra'] = _x( 'Extra', 'Root: Admin: Column Title', GPEOPLE_TEXTDOMAIN );

			} else {
				$new_columns[$key] = $value;
			}
		}

		return $new_columns;
	}

	public function manage_people_custom_column( $display, $column, $term_id )
	{
		global $gPeopleNetwork;

		if ( 'people-extra' == $column ) {

			if ( $term = get_term( $term_id, $this->constants['people_tax'] ) )
				echo '<div><code class="-slug code">'.apply_filters( 'editable_slug', $term->slug, $term ).'</code></div>';

			$affiliations = wp_get_object_terms( $term_id, $this->constants['affiliation_tax'] );
			$rel_people   = wp_get_object_terms( $term_id, $this->constants['rel_people_tax'] );

			if ( ! empty( $affiliations ) )
				echo '<div>'.$affiliations[0]->name.'</div>';
			else
				_e( '&mdash;', GPEOPLE_TEXTDOMAIN );

			foreach ( $rel_people as $rel_people_term )
				echo '<div>'.$rel_people_term->name.'</div>';


			$first = get_term_meta( $term_id, $this->constants['metakey_people_firstname'], TRUE );
			$last  = get_term_meta( $term_id, $this->constants['metakey_people_lastname'], TRUE );
			$alt   = get_term_meta( $term_id, $this->constants['metakey_people_altname'], TRUE );

			echo '<span class="firstname" data-firstname="'.$first.'"></span>';
			echo '<span class="lastname" data-lastname="'.$last.'"></span>';
			echo '<span class="altname" data-altname="'.$alt.'"></span>';
		}
	}

	public function quick_edit_custom_box( $column, $screen, $taxonomy )
	{
		if ( 'people-extra' != $column )
			return;

		// TODO: add affiliation / must move out of mustache

		echo '<fieldset><div class="inline-edit-col"><label><span class="title">';
			_ex( 'First Name', 'Root: Admin: Column Title', GPEOPLE_TEXTDOMAIN );
		echo '</span><span class="input-text-wrap">';
			echo '<input type="text" class="ptitle" name="term-firstname" value="" />';
		echo '</span></label></div></fieldset>';

		echo '<fieldset><div class="inline-edit-col"><label><span class="title">';
			_ex( 'Last Name', 'Root: Admin: Column Title', GPEOPLE_TEXTDOMAIN );
		echo '</span><span class="input-text-wrap">';
			echo '<input type="text" class="ptitle" name="term-lastname" value="" />';
		echo '</span></label></div></fieldset>';

		echo '<fieldset><div class="inline-edit-col"><label><span class="title">';
			_ex( 'Alternative', 'Root: Admin: Column Title', GPEOPLE_TEXTDOMAIN );
		echo '</span><span class="input-text-wrap">';
			echo '<input type="text" class="ptitle" name="term-altname" value="" />';
		echo '</span></label></div></fieldset>';
	}

	public function edit_term( $term_id, $tt_id, $taxonomy )
	{
		if ( $this->constants['people_tax'] != $taxonomy )
			return;

		$fields = [
			'firstname' => $this->constants['metakey_people_firstname'],
			'lastname'  => $this->constants['metakey_people_lastname'],
			'altname'   => $this->constants['metakey_people_altname'],
		];

		foreach ( $fields as $field => $constant ) {

			if ( ! array_key_exists( 'term-'.$field, $_REQUEST ) )
				continue;

			$meta = empty( $_REQUEST['term-'.$field] ) ? FALSE : trim( $_REQUEST['term-'.$field] );

			if ( $meta ) {
				update_term_meta( $term_id, $constant, $meta );
			} else {
				delete_term_meta( $term_id, $constant );
			}

			// FIXME: experiment: since the action may trigger twice
			unset( $_REQUEST['term-'.$field] );
		}
	}

	public function people_term_name( $name, $term )
	{
		// WTF: this filter called twice with different args!
		if ( ! is_object( $term ) )
			return $name;

		$formatted = gPluginTextHelper::reFormatName( $term->name );

		if ( $formatted == $term->name )
			return $name;

		return $formatted.' ['.$term->name.']';
	}

	public function people_row_actions( $actions, $term )
	{
		global $gPeopleNetwork;

		if ( $profile = $gPeopleNetwork->profile->get_link( $term->term_id, TRUE ) )
			$actions['profile'] = $profile;

		if ( $user = $gPeopleNetwork->user->get_link( $term->term_id, TRUE ) )
			$actions['user'] = $user;

		return $actions;
	}

	public function after_people_table( $taxonomy )
	{
		?><div class="form-wrap" style="text-align:right;">
			<p><?php printf( __( '&#8220;<a href="%1$s" title="People, the way involved in your site" >gPeople</a>&#8221; is a <a href="%2$s">geminorum</a> project', GPEOPLE_TEXTDOMAIN ), 'http://geminorum.ir/wordpress/gpeople/', 'http://geminorum.ir' ); ?></p>
		</div><?php
	}

	// modal on edit people tax screen
	public function modal_html_edit()
	{
		global $gPeopleNetwork;

		$tabs = array(
			'profiles' => __( 'Profiles', GPEOPLE_TEXTDOMAIN ),
			'users'    => __( 'Users', GPEOPLE_TEXTDOMAIN ),
		);

		echo '<div style="display:none"><div id="gpeople-remote-people-edit-modal" class="gpeople-modal-wrap">';

			gPluginFormHelper::headerTabs( $tabs, 'profiles', 'gpeople-modal-tab gpeople-modal-tab-' );

			echo $gPeopleNetwork->people->get_tab_profiles();
			echo $gPeopleNetwork->people->get_tab_users();

		echo '</div></div>';
	}

	// modal on edit post screen
	public function modal_html_post()
	{
		global $gPeopleNetwork, $post, $post_ID;

		$meta = $gPeopleNetwork->remote->get_postmeta( $post_ID, FALSE, array() );
		$saved = count( $meta );

		$tabs = array(
			'saved'    => __( 'Current', GPEOPLE_TEXTDOMAIN ),
			'terms'    => __( 'Add People', GPEOPLE_TEXTDOMAIN ),
			'profiles' => __( 'Add Profiles', GPEOPLE_TEXTDOMAIN ),
			'users'    => __( 'Add Users', GPEOPLE_TEXTDOMAIN ),
			'manual'   => __( 'Manual Add', GPEOPLE_TEXTDOMAIN ),
		);

		$gPeopleRemoteMetaTable = new gPeopleRemoteMetaTable( $post_ID );
		$gPeopleRemoteMetaTable->prepare_items();

		echo '<div style="display:none"><div id="gpeople-remote-people-post-modal" class="gpeople-modal-wrap">';

			gPluginFormHelper::headerTabs( $tabs, ( $saved ? 'saved' : 'terms' ), 'gpeople-modal-tab gpeople-modal-tab-' );

			echo '<div id="gpeople-tab-content-saved" class="gpeople-modal-tab-content"'. ( $saved ? ' style="display:block"' : '' ).'>';
			echo '<div id="gpeople-people-saved-messages" class="form-messages"></div>';
				echo '<form id="gpeople-meta-modal-saved-form" method="post"><div class="wrap">';
					echo '<input type="hidden" name="gpeople_post_id" value="'.$post_ID.'" />';

					// $gPeopleRemoteMetaTable->search_box( __( 'Search', GPEOPLE_TEXTDOMAIN ), 'search_id' );

					$gPeopleRemoteMetaTable->display();

			echo '</div></form></div>';

			echo $gPeopleNetwork->people->get_tab_terms( ( $saved ? '' : 'style="display:block"' ) );
			echo $gPeopleNetwork->people->get_tab_profiles();
			echo $gPeopleNetwork->people->get_tab_users();
			echo $gPeopleNetwork->people->get_tab_manual();

			// FIXME: add a debug panel for editor and above

		echo '</div></div>';
	}
}
