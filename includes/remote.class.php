<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gPeopleRemoteComponent extends gPluginComponentCore
{

	protected $priority_plugins_loaded = 20;

	private $added = FALSE;
	private $rels  = array();

	public function init()
	{
		global $gPeopleNetwork;

		// FIXME: DEPRECATED
		$this->supported_post_types = $gPeopleNetwork->getFilters( 'remote_support_post_types' );

		$this->supported_post_types = $this->settings->get( 'supported_posttypes', $this->supported_post_types );

		$this->register_taxonomies();


		if ( is_admin() ) {

			add_filter( 'pre_term_name', array( $this, 'pre_term_name' ), 12, 2 );

		} else {

			add_filter( 'single_term_title', array( $this, 'single_term_title' ), 8 );
			add_filter( $this->constants['people_tax'].'_name', array( $this, 'people_term_name' ), 8, 3 );

			if ( $this->settings->get( 'before_content', FALSE ) )
				add_filter( 'the_content', array( $this, 'the_content' ), 25 );

			if ( $this->settings->get( 'content_actions', FALSE ) )
				add_action( 'gnetwork_themes_content_before', array( $this, 'content_before' ), 20 );

			if ( $this->settings->get( 'author_link', FALSE ) )
				add_filter( 'the_author_posts_link', array( $this, 'the_author_posts_link' ) );

			if ( constant( 'GPEOPLE_ROOT_BLOG' ) == $this->current_blog
				|| $this->getOption( 'term_to_profile', FALSE ) ) {
					add_filter( 'term_link', array( $this, 'term_link' ), 10, 3 );
					add_action( 'template_redirect', array( $this, 'template_redirect' ) );
			}
		}
	}

	public function register_taxonomies()
	{
		global $gPeopleNetwork;

		register_taxonomy(
			$this->constants['people_tax'],
			$this->supported_post_types,
			array(
				'labels'                => $gPeopleNetwork->getFilters( 'people_tax_labels' ),
				'public'                => TRUE,
				'show_admin_column'     => FALSE,
				'show_in_nav_menus'     => FALSE,
				'show_in_quick_edit'    => FALSE,
				'show_ui'               => TRUE,
				'show_tagcloud'         => FALSE,
				'hierarchical'          => FALSE,
				'meta_box_cb'           => FALSE,
				'query_var'             => TRUE,
				'update_count_callback' => array( 'gPluginTaxonomyHelper', 'update_count_callback' ),
				'rewrite'               => array(
					'slug'         => $this->constants['people_slug'],
					'hierarchical' => FALSE,
					'with_front'   => FALSE,
				),
				'capabilities' => array(
					'manage_terms' => 'edit_others_posts', // 'manage_categories',
					'edit_terms'   => 'edit_others_posts', // 'manage_categories',
					'delete_terms' => 'edit_others_posts', // 'manage_categories',
					'assign_terms' => 'edit_posts', // 'edit_published_posts',
				),
			)
		);

		register_taxonomy(
			$this->constants['affiliation_tax'],
			$this->constants['people_tax'],
			array(
				'labels'                => $gPeopleNetwork->getFilters( 'affiliation_tax_labels' ),
				'public'                => TRUE,
				// 'show_admin_column'     => TRUE,
				'show_in_nav_menus'     => FALSE,
				'show_ui'               => current_user_can( 'edit_others_posts' ),
				'show_tagcloud'         => FALSE,
				'hierarchical'          => FALSE,
				'query_var'             => TRUE,
				'update_count_callback' => array( 'gPluginTaxonomyHelper', 'update_count_callback' ),
				'rewrite'               => array(
					'slug'         => $this->constants['affiliation_slug'],
					'hierarchical' => FALSE,
					'with_front'   => FALSE,
				),
				'capabilities' => array(
					'manage_terms' => 'edit_others_posts',
					'edit_terms'   => 'edit_others_posts',
					'delete_terms' => 'edit_others_posts',
					'assign_terms' => 'edit_others_posts'
				),
			)
		);

		register_taxonomy(
			$this->constants['rel_people_tax'],
			$this->constants['people_tax'],
			array(
				'labels'                => $gPeopleNetwork->getFilters( 'rel_tax_labels' ),
				'public'                => FALSE,
				'show_admin_column'     => FALSE,
				'show_in_nav_menus'     => FALSE,
				'show_ui'               => current_user_can( 'edit_others_posts' ),
				'show_tagcloud'         => FALSE,
				'hierarchical'          => FALSE,
				'update_count_callback' => array( 'gPluginTaxonomyHelper', 'update_count_callback' ),
				'rewrite'               => FALSE,
				'query_var'             => TRUE,
				'capabilities'          => array(
					'manage_terms' => 'edit_others_posts',
					'edit_terms'   => 'edit_others_posts',
					'delete_terms' => 'edit_others_posts',
					'assign_terms' => 'edit_others_posts'
				),
			)
		);

		register_taxonomy(
			$this->constants['rel_post_tax'],
			$this->supported_post_types,
			array(
				'labels'                => $gPeopleNetwork->getFilters( 'rel_tax_labels' ),
				'public'                => FALSE,
				'show_admin_column'     => FALSE,
				'show_in_nav_menus'     => FALSE,
				'show_in_quick_edit'    => FALSE,
				// 'show_ui'               => ( gPluginWPHelper::isDebug() || gPluginWPHelper::isDev() ),
				// 'show_ui'               => gPluginWPHelper::isDev(),
				'show_ui'               => FALSE,
				'show_tagcloud'         => FALSE,
				'hierarchical'          => FALSE,
				'update_count_callback' => array( 'gPluginTaxonomyHelper', 'update_count_callback' ),
				'rewrite'               => FALSE,
				'query_var'             => TRUE,
				'capabilities'          => array(
					'manage_terms' => 'edit_others_posts', // 'manage_categories',
					'edit_terms'   => 'edit_others_posts', // 'manage_categories',
					'delete_terms' => 'edit_others_posts', // 'manage_categories',
					'assign_terms' => 'edit_posts', // 'edit_published_posts',
				),
			)
		);
	}

	public function content_before( $content )
	{
		if ( $people = $this->get_people( get_the_ID() ) )
			echo '<div class="people byline -before">'.$people.'</div>';
	}

	public function the_content( $content )
	{
		if ( ! is_singular() )
			return $content;

		if ( $this->added )
			return $content;

		if ( $people = $this->get_people( get_the_ID() ) )
			$content = '<div class="people byline">'.$people.'</div>'.$content;

		$this->added = TRUE;

		return $content;
	}

	public function the_author_posts_link( $link )
	{
		if ( $people = $this->get_people( get_the_ID() ) )
			return $people;

		return $link;
	}

	public function term_link( $link, $term, $taxonomy )
	{
		global $gPeopleNetwork;

		if ( $this->constants['people_tax'] == $taxonomy )
			if ( $term_link = $gPeopleNetwork->profile->get_link( $term->term_id ) )
				return $term_link;

		return $link;
	}

	public function template_redirect()
	{
		if ( is_tax() ) {

			global $wp_query;
			$term = $wp_query->get_queried_object();

			if ( $this->constants['people_tax'] == $term->taxonomy ) {
				if ( $term_link = $gPeopleNetwork->profile->get_link( $term->term_id ) ) {
					wp_redirect( $term_link, 301 );
					exit();
				}
			}
		}
	}

	public function pre_term_name( $term, $taxonomy )
	{
		return $this->constants['people_tax'] == $taxonomy ? gPluginTextHelper::formatName( $term ) : $term;
	}

	public function single_term_title( $title )
	{
		return is_tax( $this->constants['people_tax'] ) ? gPluginTextHelper::reFormatName( $title ) : $title;
	}

	public function people_term_name( $value, $term_id, $context )
	{
		return 'display' == $context ? gPluginTextHelper::reFormatName( $value ) : $value;
	}

	public function get_people( $post_id, $atts = array(), $walker = NULL )
	{
		$meta = $this->get_postmeta( $post_id, FALSE, array() );

		if ( ! count( $meta ) )
			return '';

		if ( is_null( $walker ) )
			$walker = array( __CLASS__, 'peopleBylineWalker' );

		$defaults = array(
			'name'      => FALSE,
			'link'      => FALSE,
			'filter'    => FALSE,
			'vis'       => TRUE,
			'rel'       => 'none',
			'rel_title' => '',
			'rel_link'  => FALSE,
			'feat'      => FALSE,
		);

		$people = array();

		foreach ( $meta as $item ) {
			$new_people = $defaults;

			if ( isset( $item['id'] ) && $item['id'] ) {
				if ( $term = get_term_by( 'id', $item['id'], $this->constants['people_tax'] ) ) {
					$new_people['name'] = gPluginTextHelper::reFormatName( $term->name );
					$new_people['link'] = get_term_link( $term, $this->constants['people_tax'] );
				}
			}

			if ( ( isset( $item['override'] ) && $item['override'] ) )
				$new_people['name'] = $item['override'];

			if ( ! $new_people['name'] && ( isset( $item['temp'] ) && $item['temp'] ) )
				$new_people['name'] = gPluginTextHelper::reFormatName( $item['temp'] );

			if ( ! $new_people['name'] )
				// $new_people['name'] = __( 'Unknown Person', GPEOPLE_TEXTDOMAIN );
				continue; // no name, no person!!

			if ( isset( $item['rel'] ) && $item['rel'] && 'none' != $item['rel'] ) {

				// WORKING: no need, not used
				// if ( ! array_key_exists( $item['rel'], $this->rels )
				// 	$this->rels[$item['rel']] = get_term_by( 'slug', $item['rel'], $this->constants['rel_people_tax'] );
				//
				// if ( $this->rels[$item['rel']] ) {
				// 	$new_people['rel'] = $this->rels[$item['rel']]->slug;
				// 	$new_people['rel_title'] = $this->rels[$item['rel']]->name;
				// 	$new_people['rel_link'] = get_term_link( $this->rels[$item['rel']], $this->constants['rel_people_tax'] );
				// }

				$new_people['rel'] = $item['rel'];
			}

			if ( isset( $item['vis'] ) && ( 'hidden' == $item['vis'] || 'none' == $item['vis'] ) )
				$new_people['vis'] = FALSE;

			if ( isset( $item['feat'] ) && $item['feat'] )
				$new_people['feat'] = TRUE;

			if ( isset( $item['filter'] ) && $item['filter'] )
				$new_people['filter'] = $item['filter'];

			$people[] = $new_people;
		}

		return call_user_func_array( $walker, array( $people, $post_id, $atts ) );
	}

	public static function peopleBylineWalker( $people, $post_id = NULL, $atts = array() )
	{
		$args = self::atts( apply_filters( 'people_byline_walker_defaults', array(
			'default'      => '',
			'between'      => _x( ', ', 'people byline walker between delimiter', GPEOPLE_TEXTDOMAIN ),
			'between_last' => _x( ' and ', 'people byline walker between last delimiter', GPEOPLE_TEXTDOMAIN ),
			'pre'          => _x( 'Written by ', 'people byline walker pre', GPEOPLE_TEXTDOMAIN ),
			'before'       => '',
			'after'        => '',
			'link'         => TRUE,
			'visible'      => current_user_can( 'edit_posts' ),
		), $people, $post_id ), $atts );

		if ( ! $people || ! count( $people ) )
			return $args['default'];

		$links = array();

		foreach ( $people as $person ) {

			$person = apply_filters( 'people_byline_walker_person', $person, $args, $people, $atts );

			if ( $person['vis'] || $args['visible'] ) {

				if ( $args['link'] && $person['link'] ) {
					$attr = array(
						'href'  => $person['link'],
						'class' => 'gpeople-people-link',
					);
				} else {
					$attr = array(
						'class' => 'gpeople-people-span',
					);
				}

				if ( 'none' != $person['rel'] )
					$attr['rel'] = $person['rel'];

				if ( ! $person['vis'] ) {
					$attr['style'] = 'opacity:0.6;';
					$attr['class'] .= ' gpeople-people-hidden';
				}

				$attr = apply_filters( 'people_byline_walker_attr', $attr, $person, $args, $people, $atts );

				$tag = gPluginHTML::tag( ( $args['link'] && $person['link'] ? 'a' : 'span' ) , $attr, $person['name'] );

				if ( $person['filter'] ) {
					if ( FALSE === strpos( $person['filter'], '%s' ) )
						$tag = $person['filter'].' '.$tag;
					else
						$tag = sprintf( $person['filter'], $tag );
				}

				$links[] = apply_filters( 'people_byline_walker_link', $tag, $person, $args, $people, $atts );
			}
		}

		$count = count( $links );
		if ( ! $count )
			return $args['default'];
		else if ( $count > 1 )
			$html = gPluginTextHelper::joinString( $links, $args['between'], $args['between_last'] );
		else
			$html = $links[0];

		return $args['before'].$args['pre'].$html.$args['after'];
	}

	public function get_remote_meta_data( $term_or_id, $pre_data = array() )
	{
		if ( is_object( $term_or_id ) )
			$term = $term_or_id;
		else
			$term = get_term_by( 'id', $term_or_id, $this->constants['people_tax'] );

		$data = array_merge( $pre_data, array(
			'id'   => $term->term_id,
			'temp' => $term->name,
			// 'o'    => 0,

			// commented out in pref of the pre_data filter
			// 'vis'      => 'public',
			// 'filter'   => '',
			// 'override' => '',
			// 'rel'      => 'none',
		) );

		return $data;
	}

	public function get_remote_term_data( $term_or_id, $pre_data = array() )
	{
		global $gPeopleNetwork;

		if ( is_object( $term_or_id ) )
			$term = $term_or_id;
		else
			$term = get_term_by( 'id', $term_or_id, $this->constants['people_tax'] );

		$term_meta         = $this->get_termmeta( $term->term_id, FALSE, array() );
		$term_affiliations = wp_get_object_terms( $term->term_id, $this->constants['affiliation_tax'] );

		$data = array_merge( $pre_data, array(
			'term'         => $term,
			'term_id'      => $term->term_id,
			'title'        => $term->name,
			'name'         => urldecode( $term->slug ),
			'has_excerpt'  => ! empty( $term->description ),
			'excerpt'      => ( $term->description ? wpautop( $term->description ) : '<p>'.__( '<i>No Profile Summary</i>', GPEOPLE_TEXTDOMAIN ).'</p>' ), //  wpautop( $founded_profile->excerpt
			'link'         => get_term_link( $term, $this->constants['people_tax'] ),

			// FIXME: DEPRECATED
			'images'       => array(),
			'thumbnail'    => $gPeopleNetwork->picture->get_default(),

			'affiliations' => count( $term_affiliations ) ? $term_affiliations[0]->name : FALSE,
			'edit'         => add_query_arg( array(
				'action'   => 'edit',
				'taxonomy' => $this->constants['people_tax'],
				'tag_ID'   => $term->term_id,
			), admin_url( 'edit-tags.php' ) ),
		) );

		// FIXME: DEPRECATED
		if ( isset( $term_meta['profile-images'] ) )
			$data['images'] = $term_meta['profile-images'];

		if ( isset( $term_meta['profile-images']['thumbnail'] ) )
			$data['thumbnail'] = $term_meta['profile-images']['thumbnail'][0];

		else if ( $term_meta_picture = get_term_meta( $term->term_id, 'people_picture_id', TRUE ) )
			$data['thumbnail'] = wp_get_attachment_thumb_url( $term_meta_picture );

		if ( isset ( $term_meta['user-id'] ) && $term_meta['user-id'] )
			$user_id = $term_meta['user-id'];

		else if ( $term_meta_user_id = get_term_meta( $term->term_id, 'people_user_id', TRUE ) )
			$user_id = $term_meta_user_id;

		else
			$user_id = FALSE;

		if ( $user_id ) {
			$data['user-id'] = $user_id;
			$data['user-name'] = get_user_by( 'id', $user_id )->display_name;

			if ( current_user_can( 'manage_network_users' ) )
				$data['user-edit'] = gPluginWPHelper::getUserEditLink( $user_id );

		} else {
			$data['user-no'] = TRUE;
		}

		if ( isset ( $term_meta['profile-id'] ) && $term_meta['profile-id'] )
			$profile_id = $term_meta['profile-id'];

		else if ( $term_meta_profile_id = get_term_meta( $term->term_id, 'people_profile_id', TRUE ) )
			$profile_id = $term_meta_profile_id;

		else
			$profile_id = FALSE;

		if ( $profile_id ) {
			$data['profile-id'] = $profile_id;

			$data['profile-link'] = add_query_arg( array(
					'p' => $profile_id,
				), get_blogaddress_by_id( GPEOPLE_ROOT_BLOG ) );

		} else {
			$data['profile-no'] = TRUE;
		}

		return $data;
	}

	// INTERNAL: set terms for each posts: people & rel_post
	public function set_post_terms( $post_id, $meta )
	{
		$people_terms = $rel_people_terms = $rel_post_terms = array();
		$rel_posts_terms = gPluginTaxonomyHelper::prepareTerms( $this->constants['rel_post_tax'], array(), NULL, 'slug' );

		foreach ( $meta as $person ) {

			$people_term = $people_rel = FALSE;

			if ( isset( $person['id'] ) && $person['id'] ) {

				if ( isset( $person['vis'] ) ) {

					if ( 'tagged' == $person['vis']
						|| 'hidden' == $person['vis'] )
							$people_terms[] = $people_term = intval( $person['id'] );

				} else {
					$people_terms[] = $people_term = intval( $person['id'] );
				}
			}

			if ( isset( $person['rel'] ) && 'none' != $person['rel'] ) {

				if ( isset( $rel_posts_terms[$this->constants['rel_post_tax_pre'].$person['rel']] ) )
					$rel_post_terms[] = intval( $rel_posts_terms[$this->constants['rel_post_tax_pre'].$person['rel']]->id );

				if ( $people_term )
					$rel_people_terms[$people_term][] = $person['rel'];
			}
		}

		$people_tax   = wp_set_object_terms( intval( $post_id ), ( count( $people_terms ) ? array_values( $people_terms ) : NULL ), $this->constants['people_tax'], FALSE );
		$rel_post_tax = wp_set_object_terms( intval( $post_id ), ( count( $rel_post_terms ) ? array_values( $rel_post_terms ) : NULL ), $this->constants['rel_post_tax'], FALSE );

		if ( is_wp_error( $people_tax ) || is_wp_error( $rel_post_tax ) )
			return FALSE;

		foreach ( $rel_people_terms as $rel_people_term => $rel_people_rel ) {
			if ( count( $rel_people_rel ) ) {

				$rel_people_tax = wp_set_object_terms( intval( $rel_people_term ), $rel_people_rel, $this->constants['rel_people_tax'], TRUE );

				if ( is_wp_error( $rel_people_tax ) )
					return FALSE;
			}
		}

		return TRUE;
	}
}
