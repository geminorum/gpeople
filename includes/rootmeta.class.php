<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gPeopleRootMeta extends gPluginMetaCore
{

	public static function geditorial_meta_include()
	{
		$gPeopleRootComponent = gPluginFactory( 'gPeopleRootComponent' );

		gPluginFactory( 'gPeopleRootMeta', $gPeopleRootComponent->getConstants(), $gPeopleRootComponent->getArgs() );
	}

	public function setup_actions()
	{
		add_action( 'init', array( $this, 'init' ) );

		add_filter( 'geditorial_meta_default_options', array( $this, 'geditorial_meta_default_options' ), 10 , 1 );
		add_filter( 'geditorial_meta_strings', array( $this, 'geditorial_meta_strings' ), 5 , 1 );

		add_filter( 'geditorial_meta_dbx_callback', array( $this, 'geditorial_meta_dbx_callback' ), 10 , 2 );
		add_action( 'add_meta_boxes_'.$this->constants['profile_cpt'], array( $this, 'add_meta_boxes_profile' ), 22 );
		add_filter( 'geditorial_meta_sanitize_post_meta', array( $this, 'geditorial_meta_sanitize_post_meta' ), 10 , 4 );
	}

	public function init()
	{
		$this->register_taxonomies();
	}

	public function register_taxonomies()
	{
		global $gPeopleNetwork, $gEditorial;

		$fields = $gEditorial->meta->get_post_type_fields( $gEditorial->meta->module, $this->constants['profile_cpt'] );
		if ( in_array( 'nationality', $fields ) && $gEditorial->meta->user_can( 'view', 'nationality' )  ) {

			register_taxonomy( $this->constants['profile_nationality_tax'], array( $this->constants['profile_cpt'] ), array(
				'labels'                => $gPeopleNetwork->getFilters( 'profile_nationality_tax_labels' ),
				'public'                => TRUE,
				'show_in_nav_menus'     => FALSE,
				'show_ui'               => TRUE,
				'show_tagcloud'         => FALSE,
				'show_admin_column'     => TRUE,
				'hierarchical'          => TRUE,
				'update_count_callback' => array( 'gPluginTaxonomyHelper', 'update_count_callback' ),
				'rewrite'               => array(
					'slug'         => $this->constants['profile_nationality_slug'],
					'hierarchical' => FALSE,
				),
				'query_var' => TRUE,
				'capabilities' => array(
					'manage_terms' => 'edit_others_posts', // 'manage_categories',
					'edit_terms'   => 'edit_others_posts', // 'manage_categories',
					'delete_terms' => 'edit_others_posts', // 'manage_categories',
					'assign_terms' => 'edit_posts', // 'edit_published_posts',
				),
			));
		}
	}

	function geditorial_meta_default_options( $default_options )
	{
		global $gPeopleNetwork;

		$fields = $gPeopleNetwork->getFilters( 'root_meta_fields' );
		$default_options['post_types'][$this->constants['profile_cpt']] = 'on';
		$default_options[$this->constants['profile_cpt'].'_fields'] = $fields[$this->constants['profile_cpt']];
		// $default_options['post_fields'] = array_merge( $default_options['post_fields'], $fields['post'] );
		return $default_options;
	}

	function geditorial_meta_strings( $strings )
	{
		global $gPeopleNetwork;

		$new = $gPeopleNetwork->getFilters( 'root_meta_strings' );
		return gPluginUtils::recursiveParseArgs( $new, $strings );
	}

	function geditorial_meta_dbx_callback( $func, $post_type )
	{
		if ( $this->constants['profile_cpt'] == $post_type )
			return array( $this, 'dbx_callback' );
		return $func;
	}

	function dbx_callback()
	{
		global $gEditorial, $post;
		$fields = $gEditorial->meta->get_post_type_fields( $gEditorial->meta->module, $post->post_type );

		if ( in_array( 'ot', $fields ) ) {
			?><input class="geditorial-meta-prompt" type="text" name="geditorial-meta-ot" id="geditorial-meta-ot" autocomplete="off"
				value="<?php echo esc_attr( $gEditorial->meta->get_postmeta( $post->ID, 'ot' ) ); ?>"
				title="<?php echo esc_attr( $gEditorial->meta->get_string( 'ot', $post->post_type ) ); ?>"
				placeholder="<?php echo esc_attr( $gEditorial->meta->get_string( 'ot', $post->post_type ) ); ?>" /><?php
		}

		if ( in_array( 'st', $fields ) ) {
			?><input class="geditorial-meta-prompt" type="text" name="geditorial-meta-st" id="geditorial-meta-st" autocomplete="off"
				value="<?php echo esc_attr( $gEditorial->meta->get_postmeta( $post->ID, 'st' ) ); ?>"
				title="<?php echo esc_attr( $gEditorial->meta->get_string( 'st', $post->post_type ) ); ?>"
				placeholder="<?php echo esc_attr( $gEditorial->meta->get_string( 'st', $post->post_type ) ); ?>" /><?php
		}

		wp_nonce_field( 'geditorial_meta_post_raw', '_geditorial_meta_post_raw' );
	}

	function add_meta_boxes_profile( $post )
	{
		global $gEditorial;
		$title = $gEditorial->meta->get_string( 'box_title', $this->constants['profile_cpt'], 'misc' );
		if ( current_user_can( 'edit_others_posts' ) ) {
			$url = add_query_arg( 'post_type', $this->constants['profile_cpt'], get_admin_url( NULL, 'edit.php' ) );
			$title .= ' <span class="gpeople-box-title-action"><a href="'.esc_url( $url ).'" class="edit-box open-box">'.__( 'Settings', GPEOPLE_TEXTDOMAIN ).'</a></span>';
		}
		add_meta_box( 'gpeople-meta', $title, array( $this, 'do_meta_box_profile' ), $this->constants['profile_cpt'], 'side', 'high' );
		//remove_meta_box( 'tagsdiv-'.$this->constants['profile_nationality_tax'], $this->constants['profile_cpt'], 'side' );
		remove_meta_box( $this->constants['profile_nationality_tax'].'div', $this->constants['profile_cpt'], 'side' );
	}

	function do_meta_box_profile( $post )
	{
		global $gEditorial;
		//$gBookRootPlugin = gPluginFactory( 'gBookRootPlugin' );
		$fields = $gEditorial->meta->get_post_type_fields( $gEditorial->meta->module, $post->post_type );
		//$meta = $gEditorial->meta->get_postmeta( $post->ID, $this->constants['profile_meta_key'], array() );

		gEditorialHelper::meta_admin_field( 'alt_name', $fields, $post );
		gEditorialHelper::meta_admin_field( 'born', $fields, $post );
		gEditorialHelper::meta_admin_field( 'died', $fields, $post );
		gEditorialHelper::meta_admin_field( 'occupation', $fields, $post );
		gEditorialHelper::meta_admin_field( 'site', $fields, $post, TRUE );
		gEditorialHelper::meta_admin_field( 'wikipedia', $fields, $post, TRUE );
		gEditorialHelper::meta_admin_field( 'mail', $fields, $post, TRUE );

		if ( in_array( 'nationality', $fields ) && $gEditorial->meta->user_can( 'view', 'nationality' )  ) {
			$this->select_profile_nationality(
				$this->get_profile_nationality( $post->ID ),
				$gEditorial->meta->get_string( 'nationality', $post->post_type ),
				'geditorial-meta-nationality',
				' style="width:99%;"'.( $gEditorial->meta->user_can( 'edit', 'nationality' ) ? '' : 'readonly="readonly"' )
			);
		}

		wp_nonce_field( 'geditorial_gpeople_meta_box', '_geditorial_gpeople_meta_box' );
	}

	function geditorial_meta_sanitize_post_meta( $postmeta, $fields, $post_id, $post_type )
	{
		global $gPeopleNetwork;
		//$gBookRootPlugin = gPluginFactory( 'gBookRootPlugin' );
		$fields = $gPeopleNetwork->getFilters( 'root_meta_fields' );
		if ( $this->constants['profile_cpt'] == $post_type
			&& wp_verify_nonce( @$_REQUEST['_geditorial_gpeople_meta_box'], 'geditorial_gpeople_meta_box' ) ) {
			foreach ( $fields[$post_type] as $field => $field_enabled ) {
				if ( in_array( $field, array( 'ot', 'st' ) ) )
					continue;
				switch ( $field ) {

					case 'nationality' :
						if ( isset( $_POST['geditorial-meta-'.$field] ) && '0' != $_POST['geditorial-meta-'.$field] )
							wp_set_object_terms( $post_id, intval( $_POST['geditorial-meta-'.$field] ), $this->constants['profile_nationality_tax'], FALSE );
						else if ( isset( $_POST['geditorial-meta-'.$field] ) && '0' == $_POST['geditorial-meta-'.$field] )
							wp_set_object_terms( $post_id, NULL, $this->constants['profile_nationality_tax'], FALSE );
					break;

					default :
						if ( isset( $_POST['geditorial-meta-'.$field] )
							&& strlen( $_POST['geditorial-meta-'.$field] ) > 0 )
							// && $gEditorial->meta->module->strings['titles'][$field] !== $_POST['geditorial-meta-'.$field] )
								$postmeta[$field] = strip_tags( $_POST['geditorial-meta-'.$field] );
						elseif ( isset( $postmeta[$field] ) )
							unset( $postmeta[$field] );
				}
			}
		} /**else if ( 'post' == $post_type && wp_verify_nonce( @$_REQUEST['_geditorial_gmag_post_raw'], 'geditorial_gmag_post_raw' )  ) {
			foreach ( $fields[$post_type] as $field => $field_enabled ) {
				switch ( $field ) {
					case 'in_issue_order' :
					case 'in_issue_page_start' :
					case 'in_issue_pages' :
						if ( isset( $_POST['geditorial-meta-'.$field] )
							&& strlen( $_POST['geditorial-meta-'.$field] ) > 0 )
							//&& $gEditorial->meta->module->strings['titles'][$field] !== $_POST['geditorial-meta-'.$field] )
								$postmeta[$field] = strip_tags( $_POST['geditorial-meta-'.$field] );
						elseif ( isset( $postmeta[$field] ) )
							unset( $postmeta[$field] );
				}
			}

		}**/
		return $postmeta;
	}

	function select_profile_nationality( $current, $label, $name, $inline = '' )
	{
		$terms = get_terms( $this->constants['profile_nationality_tax'], 'orderby=name&hide_empty=0&show_count=0' );
		if ( count( $terms ) ) {
			echo '<label title="'.$label.'"><select name="'.$name.'" id="'.$name.'"'.$inline.'>';
			echo '<option value="no" '.( 'no' == $current ? 'selected="selected"' : '').'>'.__( '-Not Set-', GPEOPLE_TEXTDOMAIN ).'</option>';
			foreach ( $terms as $key => $term )
				echo '<option value="'.$term->term_id.'" '.selected( $current, $term->term_id, FALSE ).'>'.esc_html( $term->name.' ('.number_format_i18n( $term->count ).') ' ).' </option>';
			echo '</select></label>';
		} else {
			echo '<span class="geditorial-meta-profile_nationality-notfound"'.$inline.'><a href="'.get_admin_url( NULL, 'edit-tags.php?taxonomy='.$this->constants['profile_nationality_tax'] ).'">'.__( '<em>Create nationalities first</em>' , GPEOPLE_TEXTDOMAIN ).'</a></span>';
		}
	}

	public static function get_profile_nationality( $post_ID, $object = FALSE )
	{
		global $gPeopleNetwork;

		$terms = get_the_terms( $post_ID, $gPeopleNetwork->constants['profile_nationality_tax'] );
		if ( $terms && ! is_wp_error( $terms ) )
			foreach ( $terms as $term )
				if ( $object )
					return $term;
				else
					return $term->term_id;
		return '0';
	}
}
