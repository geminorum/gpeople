<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gPeopleRootComponent extends gPluginComponentCore
{

	public function setup_actions()
	{
		parent::setup_actions();

		add_action( 'geditorial_meta_init', array( $this, 'meta_init' ) );
		add_filter( 'geditorial_tweaks_strings', array( $this, 'tweaks_strings' ) );
	}

	public function init()
	{
		$this->register_post_types();
		$this->register_taxonomies();
	}

	// to use on remote, when switch blog
	public static function switch_setup( $constants )
	{
		register_post_type( $constants['profile_cpt'], array( 'show_ui' => FALSE ) );
		register_taxonomy( $constants['profile_group_tax'], $constants['profile_cpt'], array( 'show_ui' => FALSE ) );
	}

	private function register_post_types()
	{
		register_post_type( $this->constants['profile_cpt'], array(
			'labels'              => self::getFilters( 'profile_cpt_labels' ),
			'hierarchical'        => FALSE,
			'supports'            => gPluginUtils::getKeys( $this->options['profile_supports'] ),
			'taxonomies'          => gPluginUtils::getKeys( $this->options['profile_taxonomies'] ),
			'public'              => TRUE,
			'show_ui'             => TRUE,
			'show_in_menu'        => TRUE,
			'menu_position'       => 5,
			'menu_icon'           => 'dashicons-groups',
			'show_in_nav_menus'   => TRUE,
			'publicly_queryable'  => TRUE,
			'exclude_from_search' => FALSE,
			'has_archive'         => $this->constants['profile_archives'],
			'query_var'           => $this->constants['profile_cpt'],
			'can_export'          => TRUE,
			'capabilities'        => $this->options['profile_capabilities'],
			'map_meta_cap'        => TRUE,
			'rewrite'             => array(
				'slug'       => $this->constants['profile_cpt'],
				'with_front' => FALSE,
			),
		) );
	}

	private function register_taxonomies()
	{
		register_taxonomy( $this->constants['profile_group_tax'], $this->constants['profile_cpt'], array(
			'labels'                => self::getFilters( 'profile_group_tax_labels' ),
			'public'                => TRUE,
			'show_admin_column'     => TRUE,
			'show_in_nav_menus'     => FALSE,
			'show_ui'               => TRUE, // current_user_can( 'edit_others_posts' ),
			'show_tagcloud'         => FALSE,
			'hierarchical'          => FALSE,
			'update_count_callback' => array( 'gPluginTaxonomyHelper', 'update_count_callback' ),
			'query_var'             => TRUE,
			'rewrite'               => array(
				'slug'         => $this->constants['profile_group_tax_slug'],
				'hierarchical' => FALSE,
				'with_front'   => FALSE,
			),
			'capabilities' => array(
				'manage_terms' => 'edit_others_posts', // 'manage_categories',
				'edit_terms'   => 'edit_others_posts', // 'manage_categories',
				'delete_terms' => 'edit_others_posts', // 'manage_categories',
				'assign_terms' => 'edit_posts', // 'edit_published_posts',
			),
		) );
	}

	public function meta_init()
	{
		add_post_type_support(
			$this->constants['profile_cpt'],
			array( 'meta_fields' ),
			self::getFilters( 'profile_cpt_meta_fields' ) );

		// FIXME: move to current_screen
		add_filter( 'geditorial_meta_box_callback', array( $this, 'meta_box_callback' ), 10, 2 );
		add_filter( 'geditorial_meta_dbx_callback', array( $this, 'meta_box_callback' ), 10, 2 );

		register_taxonomy( $this->constants['profile_nationality_tax'], $this->constants['profile_cpt'], array(
			'labels'                => self::getFilters( 'profile_nationality_tax_labels' ),
			'public'                => TRUE,
			'show_in_nav_menus'     => FALSE,
			'show_ui'               => TRUE,
			'show_tagcloud'         => FALSE,
			'show_admin_column'     => TRUE,
			'hierarchical'          => TRUE,
			'meta_box_cb'           => FALSE,
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

	public function meta_box_callback( $callback, $post_type )
	{
		if ( $post_type == $this->constants['profile_cpt'] )
			return TRUE;

		return $callback;
	}

	public function tweaks_strings( $strings )
	{
		$new = self::getFilters( 'root_tweaks_strings' );
		return gPluginUtils::recursiveParseArgs( $new, $strings );
	}
}
