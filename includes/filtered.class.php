<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gPeopleFiltered extends gPluginFilteredCore
{

	protected function root_settings_subs()
	{
		return array(
			'overview'    => __( 'Overview', GPEOPLE_TEXTDOMAIN ),
			'general'     => __( 'General', GPEOPLE_TEXTDOMAIN ),
			'import_root' => __( 'Import', GPEOPLE_TEXTDOMAIN ),
		);
	}

	protected function root_settings_messages() { return array(); }
	protected function root_settings_titles() { return array(); }

	protected function remote_settings_titles()
	{
		return array(
			'title' => __( 'People Settings', GPEOPLE_TEXTDOMAIN ),
			'menu'  => __( 'People Settings', GPEOPLE_TEXTDOMAIN ),
		);
	}

	protected function remote_settings_subs()
	{
		return array(
			'overview'      => __( 'Overview', GPEOPLE_TEXTDOMAIN ),
			'general'       => __( 'General', GPEOPLE_TEXTDOMAIN ),
			'import_remote' => __( 'Import', GPEOPLE_TEXTDOMAIN ),
		);
	}

	protected function remote_settings_messages()
	{
		$field = isset( $_GET['field'] ) ? $_GET['field'] : '';
		return array(
			'meta_imported' => self::updated( sprintf( __( 'Meta Field %s Imported', GPEOPLE_TEXTDOMAIN ), $field ) ),
		);
	}

	protected function network_settings_subs()
	{
		return array();
	}

	protected function network_settings_messages()
	{
		return array(
			'error'   => self::error( __( 'There was an error durring updating proccess', GPEOPLE_TEXTDOMAIN ) ),
			'updated' => self::updated( __( 'Settings successfully updated.', GPEOPLE_TEXTDOMAIN ) ),
		);
	}

	protected function gpeople_network_options_defaults()
	{
		return array(
			'plugin_version' => constant( 'GPEOPLE_VERSION' ),
			'db_version'     => constant( 'GPEOPLE_VERSION_DB' ),
		);
	}

	protected function component_settings_args()
	{
		$args = array(
			'option_group' => 'gpeople_network',
			'sections' => array(
				'gplugin' => array(
					'title' => FALSE,
					'callback' => '__return_false',
					'fields' => array(
						'gpeople_remote' => array(
                            'title'       => _x( 'People', 'Settings: Field Name', GPEOPLE_TEXTDOMAIN ),
                            'description' => _x( 'Select to enable gPeople remote tools on this site', 'Settings: Field Description', GPEOPLE_TEXTDOMAIN ),
                            'type'        => 'enabled',
                            'default'     => FALSE,
						),
					),
				),
			),
		);

		// if ( defined( 'GPEOPLE_ENABLE_MULTIROOTBLOG' ) && constant( 'GPEOPLE_ENABLE_MULTIROOTBLOG' ) )
		// 	$args['sections']['gplugin']['fields']['gpeople_root'] = array(
        //         'title'       => __( 'People Home', GPEOPLE_TEXTDOMAIN ),
        //         'description' => __( 'select to enable gPeople profile management on this site', GPEOPLE_TEXTDOMAIN ),
        //         'type'        => 'enabled',
        //         'default'     => FALSE,
		// 	);

		return $args;
	}

	protected function remote_settings_args()
	{
		return array(
			'page'         => 'gpeople_remote_general',
			'option_group' => 'gpeople_remote',
			'sections'     => array(
				'default' => array(
					'title' => NULL,
					'callback' => '__return_false',
					'fields' => array(
						'before_content' => array(
                            'title'       => __( 'Before Content', GPEOPLE_TEXTDOMAIN ),
                            'description' => __( 'Add people byline before content, for each post.', GPEOPLE_TEXTDOMAIN ),
                            'type'        => 'enabled',
                            'default'     => FALSE,
						),
						'author_link' => array(
                            'title'       => __( 'Link Author', GPEOPLE_TEXTDOMAIN ),
                            'description' => __( 'Select to replace author link with people, for each post.', GPEOPLE_TEXTDOMAIN ),
                            'type'        => 'enabled',
                            'default'     => FALSE,
						),
					),
				),
			),
		);
	}

	protected function gpeople_root_options_defaults()
	{
		return array(
			'profile_supports' => array(
				'title'         => TRUE,
				'editor'        => TRUE,
				'excerpt'       => TRUE,
				'author'        => FALSE,
				'thumbnail'     => TRUE,
				'trackbacks'    => TRUE,
				'custom-fields' => TRUE,
				'comments'      => FALSE,
				'revisions'     => FALSE,
				'post-formats'  => FALSE,
			),
			'profile_taxonomies' => array(
				'category'      => FALSE, // shared cats with posts
				'post_tag'      => TRUE, // shared tags with posts
				'profile_group' => TRUE, // find a way to sync with plugin constants
			),
			'profile_capabilities' => array(
				'edit_post'          => 'edit_post',
				'edit_posts'         => 'edit_posts',
				'edit_others_posts'  => 'edit_others_posts',
				'publish_posts'      => 'publish_posts',
				'read_post'          => 'read_post',
				'read_private_posts' => 'read_private_posts',
				'delete_post'        => 'delete_post'
			),

		);
	}

	protected function gpeople_remote_options_defaults()
	{
		return array(
			'term_to_profile' => FALSE, // redirects remote terms to people profile post on the root blog
			'transit_time'    => 60 * 60 * 12, // 12 hours
		);
	}

	protected function remote_support_post_types()
	{
		return array(
			'post',
			'attachment',
		);
	}

	protected function profile_cpt_labels()
	{
		return array(
			'name'                  => _x( 'People', 'Profile CPT Labels: Name', GPEOPLE_TEXTDOMAIN ),
			'menu_name'             => _x( 'People', 'Profile CPT Labels: Menu Name', GPEOPLE_TEXTDOMAIN ),
			'singular_name'         => _x( 'Person', 'Profile CPT Labels: Singular Name', GPEOPLE_TEXTDOMAIN ),
			'add_new'               => _x( 'Add New', 'Profile CPT Labels', GPEOPLE_TEXTDOMAIN ),
			'add_new_item'          => _x( 'Add New Person', 'Profile CPT Labels', GPEOPLE_TEXTDOMAIN ),
			'edit_item'             => _x( 'Edit Person', 'Profile CPT Labels', GPEOPLE_TEXTDOMAIN ),
			'new_item'              => _x( 'New Person', 'Profile CPT Labels', GPEOPLE_TEXTDOMAIN ),
			'view_item'             => _x( 'View Person', 'Profile CPT Labels', GPEOPLE_TEXTDOMAIN ),
			'search_items'          => _x( 'Search People', 'Profile CPT Labels', GPEOPLE_TEXTDOMAIN ),
			'not_found'             => _x( 'No people found.', 'Profile CPT Labels', GPEOPLE_TEXTDOMAIN ),
			'not_found_in_trash'    => _x( 'No people found in Trash.', 'Profile CPT Labels', GPEOPLE_TEXTDOMAIN ),
			'all_items'             => _x( 'All People', 'Profile CPT Labels', GPEOPLE_TEXTDOMAIN ),
			'archives'              => _x( 'People Archives', 'Profile CPT Labels', GPEOPLE_TEXTDOMAIN ),
			'insert_into_item'      => _x( 'Insert into person', 'Profile CPT Labels', GPEOPLE_TEXTDOMAIN ),
			'uploaded_to_this_item' => _x( 'Uploaded to this person', 'Profile CPT Labels', GPEOPLE_TEXTDOMAIN ),
			'featured_image'        => _x( 'Profile Picture', 'Profile CPT Labels', GPEOPLE_TEXTDOMAIN ),
			'set_featured_image'    => _x( 'Set profile picture', 'Profile CPT Labels', GPEOPLE_TEXTDOMAIN ),
			'remove_featured_image' => _x( 'Remove profile picture', 'Profile CPT Labels', GPEOPLE_TEXTDOMAIN ),
			'use_featured_image'    => _x( 'Use as profile picture', 'Profile CPT Labels', GPEOPLE_TEXTDOMAIN ),
			'filter_items_list'     => _x( 'Filter people list', 'Profile CPT Labels', GPEOPLE_TEXTDOMAIN ),
			'items_list_navigation' => _x( 'People list navigation', 'Profile CPT Labels', GPEOPLE_TEXTDOMAIN ),
			'items_list'            => _x( 'People list', 'Profile CPT Labels', GPEOPLE_TEXTDOMAIN ),
		);
	}

	protected function profile_group_tax_labels()
	{
		return array(
            'name'                       => _x( 'Profile Groups', 'Profile Group Tax Labels: Name', GPEOPLE_TEXTDOMAIN ),
            'menu_name'                  => _x( 'Profile Groups', 'Profile Group Tax Labels: Menu Name', GPEOPLE_TEXTDOMAIN ),
            'singular_name'              => _x( 'Profile Group', 'Profile Group Tax Labels: Singular Name', GPEOPLE_TEXTDOMAIN ),
            'search_items'               => _x( 'Search Profile Groups', 'Profile Group Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'popular_items'              => NULL, // _x( 'Popular Groups', 'Profile Group Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'all_items'                  => _x( 'All Profile Groups', 'Profile Group Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'edit_item'                  => _x( 'Edit Profile Group', 'Profile Group Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'view_item'                  => _x( 'View Profile Group', 'Profile Group Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'update_item'                => _x( 'Update Profile Group', 'Profile Group Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'add_new_item'               => _x( 'Add New Profile Group', 'Profile Group Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'new_item_name'              => _x( 'New Profile Group Name', 'Profile Group Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'separate_items_with_commas' => _x( 'Separate profile groups with commas', 'Profile Group Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'add_or_remove_items'        => _x( 'Add or remove profile groups', 'Profile Group Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'choose_from_most_used'      => _x( 'Choose from the most used profile groups', 'Profile Group Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'not_found'                  => _x( 'No profile groups found.', 'Profile Group Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'no_terms'                   => _x( 'No profile groups', 'Profile Group Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'items_list_navigation'      => _x( 'Profile groups list navigation', 'Profile Group Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'items_list'                 => _x( 'Profile groups list', 'Profile Group Tax Labels', GPEOPLE_TEXTDOMAIN ),
		);
	}

	protected function people_tax_labels()
	{
		return array(
            'name'                       => _x( 'People', 'People Tax Labels: Name', GPEOPLE_TEXTDOMAIN ),
            'menu_name'                  => _x( 'People', 'People Tax Labels: Menu Name', GPEOPLE_TEXTDOMAIN ),
            'singular_name'              => _x( 'Person', 'People Tax Labels: Singular Name', GPEOPLE_TEXTDOMAIN ),
            'search_items'               => _x( 'Search People', 'People Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'popular_items'              => NULL, // _x( 'Popular People', 'People Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'all_items'                  => _x( 'All People', 'People Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'edit_item'                  => _x( 'Edit Person', 'People Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'view_item'                  => _x( 'View Person', 'People Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'update_item'                => _x( 'Update Person', 'People Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'add_new_item'               => _x( 'Add New Person', 'People Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'new_item_name'              => _x( 'New Person Name', 'People Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'separate_items_with_commas' => _x( 'Separate people with commas', 'People Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'add_or_remove_items'        => _x( 'Add or remove people', 'People Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'choose_from_most_used'      => _x( 'Choose from the most used people', 'People Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'not_found'                  => _x( 'No people found.', 'People Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'no_terms'                   => _x( 'No people', 'People Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'items_list_navigation'      => _x( 'People list navigation', 'People Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'items_list'                 => _x( 'People list', 'People Tax Labels', GPEOPLE_TEXTDOMAIN ),
		);
	}

	protected function affiliation_tax_labels()
	{
		return array(
            'name'                       => _x( 'Affiliations', 'Affiliations Tax Labels: Name', GPEOPLE_TEXTDOMAIN ),
            'menu_name'                  => _x( 'Affiliations', 'Affiliations Tax Labels: Menu Name', GPEOPLE_TEXTDOMAIN ),
            'singular_name'              => _x( 'Affiliation', 'Affiliations Tax Labels: Singular Name', GPEOPLE_TEXTDOMAIN ),
            'search_items'               => _x( 'Search Affiliations', 'Affiliations Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'popular_items'              => NULL, // _x( 'Popular Affiliations', 'Affiliations Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'all_items'                  => _x( 'All Affiliations', 'Affiliations Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'edit_item'                  => _x( 'Edit Affiliation', 'Affiliations Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'view_item'                  => _x( 'View Affiliation', 'Affiliations Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'update_item'                => _x( 'Update Affiliation', 'Affiliations Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'add_new_item'               => _x( 'Add New Affiliation', 'Affiliations Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'new_item_name'              => _x( 'New Affiliation Name', 'Affiliations Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'separate_items_with_commas' => _x( 'Separate affiliations with commas', 'Affiliations Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'add_or_remove_items'        => _x( 'Add or remove affiliations', 'Affiliations Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'choose_from_most_used'      => _x( 'Choose from the most used affiliations', 'Affiliations Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'not_found'                  => _x( 'No affiliations found.', 'Affiliations Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'no_terms'                   => _x( 'No affiliations', 'Affiliations Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'items_list_navigation'      => _x( 'Affiliations list navigation', 'Affiliations Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'items_list'                 => _x( 'Affiliations list', 'Affiliations Tax Labels', GPEOPLE_TEXTDOMAIN ),
		);
	}

	protected function rel_tax_labels()
	{
		return array(
            'name'                       => _x( 'Relations', 'Relation Tax Labels: Name', GPEOPLE_TEXTDOMAIN ),
            'menu_name'                  => _x( 'Relations', 'Relation Tax Labels: Menu Name', GPEOPLE_TEXTDOMAIN ),
            'singular_name'              => _x( 'Relation', 'Relation Tax Labels: Singular Name', GPEOPLE_TEXTDOMAIN ),
            'search_items'               => _x( 'Search Relations', 'Relation Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'popular_items'              => NULL, // _x( 'Popular Relations', 'Relation Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'all_items'                  => _x( 'All Relations', 'Relation Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'edit_item'                  => _x( 'Edit Relation', 'Relation Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'view_item'                  => _x( 'View Relation', 'Relation Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'update_item'                => _x( 'Update Relation', 'Relation Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'add_new_item'               => _x( 'Add New Relation', 'Relation Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'new_item_name'              => _x( 'New Relation Name', 'Relation Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'separate_items_with_commas' => _x( 'Separate relations with commas', 'Relation Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'add_or_remove_items'        => _x( 'Add or remove relations', 'Relation Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'choose_from_most_used'      => _x( 'Choose from the most used relations', 'Relation Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'not_found'                  => _x( 'No relations found.', 'Relation Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'no_terms'                   => _x( 'No relations', 'Relation Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'items_list_navigation'      => _x( 'Relations list navigation', 'Relation Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'items_list'                 => _x( 'Relations list', 'Relation Tax Labels', GPEOPLE_TEXTDOMAIN ),
		);
	}

	protected function rel_tax_defaults()
	{
		return array(
			'author'       => _x( 'Author', 'Relation Tax Defaults', GPEOPLE_TEXTDOMAIN ),
			'co_author'    => _x( 'Coauthor', 'Relation Tax Defaults', GPEOPLE_TEXTDOMAIN ),
			'editor'       => _x( 'Editor', 'Relation Tax Defaults', GPEOPLE_TEXTDOMAIN ),
			'photographer' => _x( 'Photographer', 'Relation Tax Defaults', GPEOPLE_TEXTDOMAIN ),
			'translator'   => _x( 'Translator', 'Relation Tax Defaults', GPEOPLE_TEXTDOMAIN ),
			'reporter'     => _x( 'Reporter', 'Relation Tax Defaults', GPEOPLE_TEXTDOMAIN ),
			'commentator'  => _x( 'Commentator', 'Relation Tax Defaults', GPEOPLE_TEXTDOMAIN ),
			'artist'       => _x( 'Artist', 'Relation Tax Defaults', GPEOPLE_TEXTDOMAIN ),
			'co_artist'    => _x( 'Coartist', 'Relation Tax Defaults', GPEOPLE_TEXTDOMAIN ),
			'illustrator'  => _x( 'Illustrator', 'Relation Tax Defaults', GPEOPLE_TEXTDOMAIN ),
			'collected_by' => _x( 'Collected by', 'Relation Tax Defaults', GPEOPLE_TEXTDOMAIN ),
			'poet'         => _x( 'Poet', 'Relation Tax Defaults', GPEOPLE_TEXTDOMAIN ),
			'interviewer'  => _x( 'Interviewer', 'Relation Tax Defaults', GPEOPLE_TEXTDOMAIN ),
			'director'     => _x( 'Director', 'Relation Tax Defaults', GPEOPLE_TEXTDOMAIN ),
			'selector'     => _x( 'Selector', 'Relation Tax Defaults', GPEOPLE_TEXTDOMAIN ),
			'conductor'    => _x( 'Conductor', 'Relation Tax Defaults', GPEOPLE_TEXTDOMAIN ),
		);
	}

	// must updated according to template : remote-people-add-before.html
	protected function people_edit_data()
	{
		return array(
			'affiliation-title'       => esc_html__( 'Affiliation', GPEOPLE_TEXTDOMAIN ),
			'affiliation-desc'        => esc_html__( 'Affiliation to this site', GPEOPLE_TEXTDOMAIN ),
			'user-title'              => esc_html__( 'User', GPEOPLE_TEXTDOMAIN ),
			'user-desc'               => esc_html__( 'User linked to this person', GPEOPLE_TEXTDOMAIN ),
			'user-att-title'          => esc_html__( 'Edit the linked user profile', GPEOPLE_TEXTDOMAIN ),
			'profile-title'           => esc_html__( 'Profile', GPEOPLE_TEXTDOMAIN ),
			'profile-desc'            => esc_html__( 'Profile linked to this person', GPEOPLE_TEXTDOMAIN ),
			'profile-att-title'       => esc_html__( 'Visit the linked person profile', GPEOPLE_TEXTDOMAIN ),
			'image-title'             => esc_html__( 'Avatar', GPEOPLE_TEXTDOMAIN ),
			'user-change'             => __( 'Select User', GPEOPLE_TEXTDOMAIN ),
			'user-unlink'             => __( 'Unlink User', GPEOPLE_TEXTDOMAIN ),
			'profile-change'          => __( 'Select Profile', GPEOPLE_TEXTDOMAIN ),
			'profile-unlink'          => __( 'Unlink Profile', GPEOPLE_TEXTDOMAIN ),
			'user-edit-action'        => __( 'Edit This User', GPEOPLE_TEXTDOMAIN ),
			'profile-edit-action'     => __( 'Edit this Profile', GPEOPLE_TEXTDOMAIN ),
			'user-no-title'           => __( 'No user linked to this person.', GPEOPLE_TEXTDOMAIN ),
			'profile-no-title'        => __( 'No profile linked to this person.', GPEOPLE_TEXTDOMAIN ),
			'affiliation-empty-title' => __( 'There are no affiliations available.', GPEOPLE_TEXTDOMAIN ),
			'user-empty-title'        => __( 'There are no users available.', GPEOPLE_TEXTDOMAIN ),
			'profile-empty-title'     => __( 'There are no people available.', GPEOPLE_TEXTDOMAIN ),
		);
	}

	protected function people_edit_search_profiles()
	{
		return array(
			'override-profile'       => esc_html__( 'Overwrite', GPEOPLE_TEXTDOMAIN ),
			'override-profile-title' => esc_html__( 'Overwrite with this Profile', GPEOPLE_TEXTDOMAIN ),
			'change-profile'         => esc_html__( 'Change', GPEOPLE_TEXTDOMAIN ),
			'change-profile-title'   => esc_html__( 'Change to this Profile', GPEOPLE_TEXTDOMAIN ),
			'add-profile'            => esc_html__( 'Add', GPEOPLE_TEXTDOMAIN ),
			'add-profile-title'      => esc_html__( 'Add This Profile', GPEOPLE_TEXTDOMAIN ),
			'view-root'              => esc_html__( 'View', GPEOPLE_TEXTDOMAIN ),
			'view-root-title'        => esc_html__( 'View on root site', GPEOPLE_TEXTDOMAIN ),
			'edit-root'              => esc_html__( 'Edit', GPEOPLE_TEXTDOMAIN ),
			'edit-root-title'        => esc_html__( 'Edit on root site', GPEOPLE_TEXTDOMAIN ),
		);
	}

	protected function people_edit_search_terms()
	{
		return array(
			'add-term'        => esc_html__( 'Add', GPEOPLE_TEXTDOMAIN ),
			'add-term-title'  => esc_html__( 'Add this person', GPEOPLE_TEXTDOMAIN ),
			'view-term'       => esc_html__( 'View', GPEOPLE_TEXTDOMAIN ),
			'view-term-title' => esc_html__( 'View this person', GPEOPLE_TEXTDOMAIN ),
			'edit-term'       => esc_html__( 'Edit', GPEOPLE_TEXTDOMAIN ),
			'edit-term-title' => esc_html__( 'Edit this person', GPEOPLE_TEXTDOMAIN ),
		);
	}

	protected function people_edit_search_users()
	{
		return array(
			'add-user'        => esc_html__( 'Add', GPEOPLE_TEXTDOMAIN ),
			'add-user-title'  => esc_html__( 'Add this user', GPEOPLE_TEXTDOMAIN ),
			'view-user'       => esc_html__( 'View', GPEOPLE_TEXTDOMAIN ),
			'view-user-title' => esc_html__( 'View this user', GPEOPLE_TEXTDOMAIN ),
			'edit-user'       => esc_html__( 'Edit', GPEOPLE_TEXTDOMAIN ),
			'edit-user-title' => esc_html__( 'Edit this user', GPEOPLE_TEXTDOMAIN ),
		);
	}

	protected function remote_meta_fields()
	{
		return array(
			'post' => array(
				'people' => TRUE,
		) );
	}

	protected function remote_meta_titles()
	{
		return array(
			'people' => _x( 'People', 'remote_meta_titles', GPEOPLE_TEXTDOMAIN ),
		);
	}

	protected function remote_meta_descriptions()
	{
		return array(
			'people' => _x( 'People Involved in the post', 'remote_meta_descriptions', GPEOPLE_TEXTDOMAIN ),
		);
	}

	protected function remote_meta_visibility()
	{
		return array(
			'tagged' => __( 'Public (Tagged)', GPEOPLE_TEXTDOMAIN ),
			'public' => __( 'Public', GPEOPLE_TEXTDOMAIN ),
			'hidden' => __( 'Hidden (Tagged)', GPEOPLE_TEXTDOMAIN ),
			'none'   => __( 'Hidden', GPEOPLE_TEXTDOMAIN ),
		);
	}

	protected function remote_meta_data()
	{
		return array(
            'o'        => 0, // order
            'id'       => 0, // term id
            'feat'     => 0, // featured
            'vis'      => 'tagged', // visibility string
            'filter'   => '', // filter
            'override' => '', // override
            'rel'      => 'none', // rel tax term
            'temp'     => '', // temporary title, in case there's no people term available.
		);
	}

	protected function remote_meta_defaults()
	{
		return array();
	}

	protected function remote_meta_columns()
	{
		return array(
			'people_o'         => _x( '<span title="Order">O</span>', 'remote_meta_columns', GPEOPLE_TEXTDOMAIN ),
			'people_id'        => _x( 'Person', 'remote_meta_columns', GPEOPLE_TEXTDOMAIN ),
			'people_filt_over' => _x( 'Filter / Override', 'remote_meta_columns', GPEOPLE_TEXTDOMAIN ),
			'people_rel_vis'   => _x( 'Visibility / Relations', 'remote_meta_columns', GPEOPLE_TEXTDOMAIN ),
		);


		return array(
			'id' => array(
				'title' => _x( 'Person', 'remote_meta_columns', GPEOPLE_TEXTDOMAIN ),
				'type'  => 'link',
				'ref'   => 'term',
			),
			'override' => array(
				'title' => _x( 'Override', 'remote_meta_columns', GPEOPLE_TEXTDOMAIN ),
				'type'  => 'text',
			),
			'filter' => array(
				'title' => _x( 'Filter', 'remote_meta_columns', GPEOPLE_TEXTDOMAIN ),
				'type'  => 'text',
				'value' => '%s',
			),
			'visibility' => array(
				'title'  => _x( 'Visibility', 'remote_meta_columns', GPEOPLE_TEXTDOMAIN ),
				'type'   => 'select',
				'values' => array(
					'tagged' => __( 'Public (Tagged)', GPEOPLE_TEXTDOMAIN ),
					'public' => __( 'Public', GPEOPLE_TEXTDOMAIN ),
					'hidden' => __( 'Hidden (Tagged)', GPEOPLE_TEXTDOMAIN ),
					'none'   => __( 'Hidden', GPEOPLE_TEXTDOMAIN ),
				),
				'default'    => 'public',
				'none_title' => FALSE,
				'none_value' => 0,
			),
		);

		return array(
			'delete' => array(
				'title' => _x( 'Delete', 'remote_meta_columns', GPEOPLE_TEXTDOMAIN ),
				'type'  => 'delete',
			),
			'id' => array(
				'title' => FALSE,
				'type'  => 'link',
				'ref'   => 'term',
			),
		);
	}

	protected function root_meta_fields()
	{
		return array(
			$this->constants['profile_cpt'] => array(
				'ot'          => FALSE,
				'st'          => TRUE,
				'alt_name'    => FALSE,
				'born'        => FALSE,
				'died'        => FALSE,
				'nationality' => TRUE,
				'occupation'  => TRUE,
				'site'        => TRUE,
				'wikipedia'   => FALSE,
				'mail'        => FALSE,
			),
		);
	}

	protected function root_meta_strings()
	{
		return array(
			'titles' => array(
				$this->constants['profile_cpt'] => array(
					'ot'          => __( 'Honorific', GPEOPLE_TEXTDOMAIN ),
					'st'          => __( 'Aliases', GPEOPLE_TEXTDOMAIN ),
					'alt_name'    => __( 'Alternative Name', GPEOPLE_TEXTDOMAIN ),
					'born'        => __( 'Born', GPEOPLE_TEXTDOMAIN ),
					'died'        => __( 'Died', GPEOPLE_TEXTDOMAIN ),
					'nationality' => __( 'Nationality', GPEOPLE_TEXTDOMAIN ),
					'occupation'  => __( 'Occupation', GPEOPLE_TEXTDOMAIN ),
					'site'        => __( 'Website', GPEOPLE_TEXTDOMAIN ),
					'wikipedia'   => __( 'Wikipedia Page', GPEOPLE_TEXTDOMAIN ),
					'mail'        => __( 'Email', GPEOPLE_TEXTDOMAIN ),
				),
			),
			'descriptions' => array(
				$this->constants['profile_cpt'] => array(
					'ot'          => __( 'Honorific Title', GPEOPLE_TEXTDOMAIN ),
					'st'          => __( 'Aliases', GPEOPLE_TEXTDOMAIN ),
					'alt_name'    => __( 'Alternative Name', GPEOPLE_TEXTDOMAIN ),
					'born'        => __( 'Born', GPEOPLE_TEXTDOMAIN ),
					'died'        => __( 'Died', GPEOPLE_TEXTDOMAIN ),
					'nationality' => __( 'Nationality', GPEOPLE_TEXTDOMAIN ),
					'occupation'  => __( 'Occupation', GPEOPLE_TEXTDOMAIN ),
					'site'        => __( 'Website', GPEOPLE_TEXTDOMAIN ),
					'wikipedia'   => __( 'Wikipedia Page', GPEOPLE_TEXTDOMAIN ),
					'mail'        => __( 'Email', GPEOPLE_TEXTDOMAIN ),
				),
			),
			'misc' => array(
				$this->constants['profile_cpt'] => array(
					'box_title' => _x( 'Meta', 'add_meta_boxes', GPEOPLE_TEXTDOMAIN ),
				),
			),
		);
	}

	protected function profile_nationality_tax_labels()
	{
		return array(
            'name'                  => _x( 'Nationalities', 'Nationality Tax Labels: Name', GPEOPLE_TEXTDOMAIN ),
            'menu_name'             => _x( 'Nationalities', 'Nationality Tax Labels: Menu Name', GPEOPLE_TEXTDOMAIN ),
            'singular_name'         => _x( 'Nationality', 'Nationality Tax Labels: Singular Name', GPEOPLE_TEXTDOMAIN ),
            'search_items'          => _x( 'Search Nationalities', 'Nationality Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'all_items'             => _x( 'All Nationalities', 'Nationality Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'parent_item'           => _x( 'Parent Nationality', 'Nationality Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'parent_item_colon'     => _x( 'Parent Nationality:', 'Nationality Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'edit_item'             => _x( 'Edit Nationality', 'Nationality Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'view_item'             => _x( 'View Nationality', 'Nationality Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'update_item'           => _x( 'Update Nationality', 'Nationality Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'add_new_item'          => _x( 'Add New Nationality', 'Nationality Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'new_item_name'         => _x( 'New Nationality Name', 'Nationality Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'not_found'             => _x( 'No nationalities found.', 'Nationality Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'no_terms'              => _x( 'No nationalities', 'Nationality Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'items_list_navigation' => _x( 'Nationalities list navigation', 'Nationality Tax Labels', GPEOPLE_TEXTDOMAIN ),
            'items_list'            => _x( 'Nationalities list', 'Nationality Tax Labels', GPEOPLE_TEXTDOMAIN ),
		);
	}

	protected function remote_tab_terms_data()
	{
		return array(
			'term-desc'               => esc_html__( 'Search in people and add them as contributor to this post', GPEOPLE_TEXTDOMAIN ),
			'term-placeholder'        => esc_attr__( 'John Doe', GPEOPLE_TEXTDOMAIN ),
			'affiliation-empty-title' => __( 'There are no affiliations available.', GPEOPLE_TEXTDOMAIN ),
			'submit-value'            => __( 'Search People', GPEOPLE_TEXTDOMAIN ),
		);
	}

	protected function remote_tab_profiles_data()
	{
		return array(
			'profile-desc'        => esc_html__( 'Search in profiles and add them as a person on this site', GPEOPLE_TEXTDOMAIN ),
			'profile-placeholder' => esc_attr__( 'John Doe', GPEOPLE_TEXTDOMAIN ),
			'submit-value'        => __( 'Search Profiles', GPEOPLE_TEXTDOMAIN ),
		);
	}

	protected function remote_tab_users_data()
	{
		return array(
			'user-desc'        => esc_html__( 'Search in users and add them as a person on this site', GPEOPLE_TEXTDOMAIN ),
			'user-placeholder' => esc_attr__( 'John Doe', GPEOPLE_TEXTDOMAIN ),
			'submit-value'     => __( 'Search Users', GPEOPLE_TEXTDOMAIN ),
		);
	}

	protected function remote_tab_manual_data()
	{
		return array(
			'manual-desc'             => esc_html__( 'Manually add a person on this site', GPEOPLE_TEXTDOMAIN ),
			'name-label'              => __( 'Name', GPEOPLE_TEXTDOMAIN ),
			'name-placeholder'        => __( 'John Doe', GPEOPLE_TEXTDOMAIN ),
			'name-desc'               => __( 'The name is how it appears on your site.', GPEOPLE_TEXTDOMAIN ),
			'slug-label'              => __( 'Slug', GPEOPLE_TEXTDOMAIN ),
			'slug-placeholder'        => __( 'john-doe', GPEOPLE_TEXTDOMAIN ),
			'slug-desc'               => __( 'The &#8220;slug&#8221; is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.', GPEOPLE_TEXTDOMAIN ),
			'description-label'       => __( 'Description', GPEOPLE_TEXTDOMAIN ),
			'description-placeholder' => __( 'John Doe is a very talented guy...', GPEOPLE_TEXTDOMAIN ),
			'description-desc'        => __( 'The description is not prominent by default; however, some themes may show it.', GPEOPLE_TEXTDOMAIN ),
			'affiliation-label'       => esc_html__( 'Affiliation', GPEOPLE_TEXTDOMAIN ),
			'affiliation-desc'        => esc_html__( 'Affiliation to this site', GPEOPLE_TEXTDOMAIN ),
			'affiliation-empty-title' => __( 'There are no affiliations available.', GPEOPLE_TEXTDOMAIN ),
			'submit-value'            => __( 'Add New Person', GPEOPLE_TEXTDOMAIN ),
		);
	}
}
