<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gPeopleImporter extends gPluginImportCore
{

	public function setup_actions()
	{
		add_action( 'admin_init', array( $this, 'admin_init' ) );
	}

	public function admin_init()
	{
		if ( current_user_can( 'import' ) ) {

			// MOVED TO: remote admin
			// must load module on root too
			// add_action( 'gpeople_root_settings_sub_import_root', array( $this, 'root_settings_sub' ), 10, 2 );
			// add_action( 'gpeople_remote_settings_sub_import_remote', array( $this, 'remote_settings_sub' ), 10, 2 );

			add_filter( 'gpeople_importer_meta_pre', array( $this, 'meta_pre' ), 10, 3 );
		}

		// people tax merging
		add_action( 'pre_delete_term', array( $this, 'pre_delete_term' ), 10, 2 );
		add_action( 'gnetwork_taxonomy_term_merged', array( $this, 'term_merged' ), 10, 3 );
	}

	// unsetting connected terms before deleting people tax
	public function pre_delete_term( $term, $taxonomy )
	{
		if ( $taxonomy != $this->constants['people_tax'] )
			return;

		wp_set_object_terms( $term, NULL, $this->constants['rel_post_tax'], FALSE );
		wp_set_object_terms( $term, NULL, $this->constants['affiliation_tax'], FALSE );
	}

	// resetting term_id for merged people taxes
	public function term_merged( $taxonomy, $to_term_obj, $old_term )
	{
		if ( $taxonomy != $this->constants['people_tax']
			|| is_wp_error( $old_term ) )
				return;

		global $gPeopleNetwork;

		$posts = get_objects_in_term( $to_term_obj->term_id, $this->constants['people_tax'] );
		if ( ! is_wp_error( $posts ) && count( $posts ) ) {
			foreach ( $posts as $post_id ) {

				$meta = $gPeopleNetwork->remote->get_postmeta( $post_id, FALSE, array() );

				if ( ! count( $meta ) )
					continue;

				$update = FALSE;

				foreach ( $meta as $key => $person ) {
					if ( $old_term->term_id == $person['id'] ) {
						$meta[$key]['id'] = $to_term_obj->term_id;
						$update = TRUE;
					}
				}

				if ( $update )
					$gPeopleNetwork->remote->update_postmeta( $post_id, $meta );

				// no need to update post terms
			}
		}
	}

	public function root_settings_load( $sub ) {}
	public function remote_settings_load( $sub )
	{
		if ( 'import_remote' == $sub ) {

			if ( ! empty( $_POST ) ) {

				// check_admin_referer( 'gnetwork_'.$sub.'-options' );

				$post = isset( $_REQUEST['gpeople_importer'] ) ? $_REQUEST['gpeople_importer'] : array();

				if ( isset( $_POST['custom_fields_import'] ) ) {
					if ( isset( $post['custom_field'] ) ) {

						$limit  = isset( $post['custom_field_limit'] ) ? $post['custom_field_limit'] : FALSE;
						$offset = isset( $post['custom_field_offset'] ) ? $post['custom_field_offset'] : 0;
						$result = $this->import_from_meta( $post['custom_field'], $limit, $offset, TRUE );

						if ( $result ) {
							wp_redirect( add_query_arg( array(
								'message'             => 'meta_imported',
								'custom_field'        => $post['custom_field'],
								'custom_field_limit'  => $limit,
								'custom_field_offset' => $offset,
							), wp_get_referer() ) );
							exit();
						}
					}

				} else if ( isset( $_POST['editorial_meta_import'] ) ) {

					$limit = isset( $post['editorial_meta_limit'] ) ? $post['editorial_meta_limit'] : 25;
					$paged = isset( $post['editorial_meta_paged'] ) ? $post['editorial_meta_paged'] : 1;

					if ( isset( $post['editorial_meta_post_type'] ) ) {
						$metas = $this->get_editorial_meta( stripslashes( $post['editorial_meta_post_type'] ), $limit, $paged );
						if ( count( $metas ) ) {
							$result = $this->import_array( $metas );
							if ( $result ) {
								wp_redirect( add_query_arg( array(
									'message' => 'gmeta_imported',
									'type'    => $post['editorial_meta_post_type'],
									'limit'   => $limit,
									'paged'   => $paged,
								), wp_get_referer() ) );
								exit();
							}
						}
					}

				} else if ( isset( $_POST['post_term_update'], $_POST['_cb'] ) ) {

					$count = 0;

					foreach ( $_POST['_cb'] as $post_id )
						if ( $this->import_from_terms( $post_id ) )
							$count++;

					wp_redirect( add_query_arg( array(
						'message' => 'terms_imported',
						'count'   => $count,
					), wp_get_referer() ) );

					exit();
				}
			}
		}
	}

	public function root_settings_sub( $settings_uri, $sub ) {}
	public function remote_settings_sub( $settings_uri, $sub )
	{
		global $gPeopleNetwork;

		$post  = isset( $_REQUEST['gpeople_importer'] ) ? $_REQUEST['gpeople_importer'] : array();
		$limit = isset( $post['limit'] ) ? stripslashes( $post['limit'] ) : 25;
		$paged = isset( $post['paged'] ) ? stripslashes( $post['paged'] ) : 1;
		$type  = isset( $post['type'] ) ? stripslashes( $post['type'] ) : 'post';

		echo '<form method="post" action="">';
			echo '<h3>'.__( 'Import People', GPEOPLE_TEXTDOMAIN ).'</h3>';
			echo '<table class="form-table">';

			echo '<tr><th scope="row">'.__( 'Import Custom Fields', GPEOPLE_TEXTDOMAIN ).'</th><td>';

			if ( isset( $_POST['custom_fields_check'] ) && isset( $post['custom_field'] ) ) {

				if ( isset( $post['custom_field_limit'] ) && $post['custom_field_limit'] )
					$custom_field_limit = stripslashes( $post['custom_field_limit'] );
				else
					$custom_field_limit = $limit;

				if ( isset( $post['custom_field_paged'] ) && $post['custom_field_paged'] )
					$custom_field_paged = stripslashes( $post['custom_field_paged'] );
				else
					$custom_field_paged = $paged;

				if ( class_exists( 'geminorum\\gNetwork\\HTML' ) )
					geminorum\gNetwork\HTML::tableList( array(
						'post_id' => __( 'ID', GPEOPLE_TEXTDOMAIN ),
						'meta'    => sprintf( __( 'Meta : %s', GPEOPLE_TEXTDOMAIN ), $post['custom_field'] ),
						'people'  => array(
							'title'    => _x( 'People', 'Importer', GPEOPLE_TEXTDOMAIN ),
							'callback' => array( $this, 'meta_pre_table' ),
						),
					), gPluginTaxonomyHelper::getMetaRows(
						stripslashes( $post['custom_field'] ),
						$custom_field_limit,
						$custom_field_paged
					) );

				echo '<br />';
			}

			$custom_fields = gPluginTaxonomyHelper::getMetaKeys();
			if ( count( $custom_fields ) ) {


				$gPeopleNetwork->components->do_settings_field( array(
					'type'         => 'select',
					'field'        => 'custom_field',
					'values'       => gPluginUtils::sameKey( $custom_fields ),
					'default'      => ( isset( $post['custom_field'] ) ? $post['custom_field'] : '' ),
					'option_group' => 'gpeople_importer',
				) );

				$gPeopleNetwork->components->do_settings_field( array(
					'type'         => 'text',
					'field'        => 'custom_field_limit',
					'default'      => ( isset( $post['custom_field_limit'] ) ? $post['custom_field_limit'] : $limit ),
					'option_group' => 'gpeople_importer',
					'field_class'  => 'small-text',
				) );

				$gPeopleNetwork->components->do_settings_field( array(
					'type'         => 'text',
					'field'        => 'custom_field_offset',
					'default'      => ( isset( $post['custom_field_offset'] ) ? $post['custom_field_offset'] : '0' ),
					'option_group' => 'gpeople_importer',
					'field_class'  => 'small-text',
				) );


				echo gPluginHTML::tag( 'p', array(
					'class' => 'description',
				), __( 'Check for Custom Fields and import them into People', GPEOPLE_TEXTDOMAIN ) );

				echo '<p class="submit">';

					submit_button( __( 'Check', GPEOPLE_TEXTDOMAIN ), 'secondary', 'custom_fields_check', FALSE, array( 'default' => 'default' ) ); echo '&nbsp;&nbsp;';
					submit_button( __( 'Import', GPEOPLE_TEXTDOMAIN ), 'secondary', 'custom_fields_import', FALSE ); //echo '&nbsp;&nbsp;';

				echo '</p>';


			} else {
				echo gPluginHTML::tag( 'p', array(
					'class' => 'description',
				), __( 'No custom fields!', GPEOPLE_TEXTDOMAIN ) );
			}

			echo '</td></tr>';

			echo '<tr><th scope="row">'.__( 'Import gEditorial Meta', GPEOPLE_TEXTDOMAIN ).'</th><td>';

			if ( isset( $_POST['editorial_meta_check'] ) ) {

				if ( class_exists( 'geminorum\\gNetwork\\HTML' ) )
					geminorum\gNetwork\HTML::tableList( array(
						'_cb'     => '_index',
						'post_id' => __( 'ID', GPEOPLE_TEXTDOMAIN ),
						'meta'    => array(
							'title' => __( 'Meta: Author Simple', GPEOPLE_TEXTDOMAIN ),
							'callback' => array( $this, 'meta_row_table' ),
						),
						'people' => array(
							'title' => _x( 'People', 'Importer', GPEOPLE_TEXTDOMAIN ),
							'callback' => array( $this, 'meta_pre_table' ),
						),
					), $this->get_editorial_meta(
						isset( $post['editorial_meta_post_type'] ) ? stripslashes( $post['editorial_meta_post_type'] ) : $type,
						isset( $post['editorial_meta_limit'] ) ? stripslashes( $post['editorial_meta_limit'] ) : $limit,
						isset( $post['editorial_meta_paged'] ) ? stripslashes( $post['editorial_meta_paged'] ) : $paged
					) );
				echo '<br />';
			}

			$gPeopleNetwork->components->do_settings_field( array(
				'type'         => 'select',
				'field'        => 'editorial_meta_post_type',
				'values'       => gPluginUtils::sameKey( $gPeopleNetwork->remote->supported_post_types ),
				'default'      => isset( $post['editorial_meta_post_type'] ) ? stripslashes( $post['editorial_meta_post_type'] ) : $type,
				'option_group' => 'gpeople_importer',
			) );

			$gPeopleNetwork->components->do_settings_field( array(
				'type'         => 'text',
				'field'        => 'editorial_meta_limit',
				'default'      => isset( $post['editorial_meta_limit'] ) ? stripslashes( $post['editorial_meta_limit'] ) : $limit,
				'option_group' => 'gpeople_importer',
				'field_class'  => 'small-text',
			) );

			$gPeopleNetwork->components->do_settings_field( array(
				'type'         => 'text',
				'field'        => 'editorial_meta_paged',
				'default'      => isset( $post['editorial_meta_paged'] ) ? stripslashes( $post['editorial_meta_paged'] ) : $paged,
				'option_group' => 'gpeople_importer',
				'field_class'  => 'small-text',
			) );

			echo gPluginHTML::tag( 'p', array(
				'class' => 'description',
			), __( 'Check for Editorial Meta Authors and import them into People', GPEOPLE_TEXTDOMAIN ) );

			echo '<p class="submit">';

				submit_button( __( 'Check', GPEOPLE_TEXTDOMAIN ), 'secondary', 'editorial_meta_check', FALSE, array( 'default' => 'default' ) ); echo '&nbsp;&nbsp;';
				submit_button( __( 'Import', GPEOPLE_TEXTDOMAIN ), 'secondary', 'editorial_meta_import', FALSE ); echo '&nbsp;&nbsp;';
				submit_button( __( 'Delete', GPEOPLE_TEXTDOMAIN ), 'secondary', 'editorial_meta_delete', FALSE ); //echo '&nbsp;&nbsp;';

			echo '</p>';
			echo '</td></tr>';

			echo '<tr><th scope="row">'.__( 'Update from Terms', GPEOPLE_TEXTDOMAIN ).'</th><td>';

			if ( isset( $_POST['post_term_check'] ) && isset( $post['post_term_post_type'] ) ) {

				if ( isset( $post['post_term_limit'] ) && $post['post_term_limit'] )
					$post_term_limit = stripslashes( $post['post_term_limit'] );
				else
					$post_term_limit = $limit;

				if ( isset( $post['post_term_paged'] ) && $post['post_term_paged'] )
					$post_term_paged = stripslashes( $post['post_term_paged'] );
				else
					$post_term_paged = $paged;

				if ( isset( $post['post_term_post_type'] ) && $post['post_term_post_type'] )
					$post_term_type = stripslashes( $post['post_term_post_type'] );
				else
					$post_term_type = $type;

				if ( class_exists( 'geminorum\\gNetwork\\HTML' ) )
					geminorum\gNetwork\HTML::tableList( array(
						'_cb'     => '_index',
						'post_id' => __( 'ID', GPEOPLE_TEXTDOMAIN ),
						'title'   => array(
							'title' => __( 'Title', GPEOPLE_TEXTDOMAIN ),
							'callback' => array( $this, 'meta_row_table' ),
						),
					), $this->get_empty_posts(
						$post_term_type,
						$post_term_limit
					) );

				echo '<br />';
			}

			$gPeopleNetwork->components->do_settings_field( array(
				'type'         => 'select',
				'field'        => 'post_term_post_type',
				'values'       => gPluginUtils::sameKey( $gPeopleNetwork->remote->supported_post_types ),
				'default'      => ( isset( $post['post_term_post_type'] ) ? $post['post_term_post_type'] : '' ),
				'option_group' => 'gpeople_importer',
			) );

			$gPeopleNetwork->components->do_settings_field( array(
				'type'         => 'text',
				'field'        => 'post_term_limit',
				'default'      => ( isset( $post['post_term_limit'] ) ? $post['post_term_limit'] : '' ),
				'option_group' => 'gpeople_importer',
				'field_class'  => 'small-text',
			) );

			echo gPluginHTML::tag( 'p', array(
				'class' => 'description',
			), __( 'Check for people terms and update people meta table for each post', GPEOPLE_TEXTDOMAIN ) );

			echo '<p class="submit">';

				submit_button( __( 'Check Posts', GPEOPLE_TEXTDOMAIN ), 'secondary', 'post_term_check', FALSE, array( 'default' => 'default' ) ); echo '&nbsp;&nbsp;';
				submit_button( __( 'Update Selected', GPEOPLE_TEXTDOMAIN ), 'secondary', 'post_term_update', FALSE ); echo '&nbsp;&nbsp;';

			echo '</p>';
			echo '</td></tr>';
			echo '</table>';

			wp_referer_field();
		echo '</form>';
	}

	public function get_editorial_meta( $post_type = 'post', $limit = -1, $paged = 1 )
	{
		$args = array(
			'post_type'        => $post_type,
			'post_status'      => array( 'publish', 'draft', 'pending' ),
			'posts_per_page'   => ( $limit ? intval( $limit ) : -1 ),
			'paged'            => ( $paged > 1 ? $paged : 1 ),
			'surpress_filters' => TRUE,
			'fields'           => 'ids',
			'order'            => 'ASC',
			'orderby'          => 'ID',
			'meta_query'       => array(
				'relation' => 'AND',
				array(
					'key'     => $this->constants['remote_meta_key'],
					'compare' => 'NOT EXISTS'
				),
				array(
					'key'     => '_gmeta',
					'compare' => 'EXISTS'
				)
			),
		);

		$query = new WP_Query( $args );
		$metas = array();

		if ( $query->have_posts() ) {
			foreach ( $query->posts as $post_id ) {
				$postmeta = get_metadata( 'post', $post_id, '_gmeta', TRUE );
				if ( ! empty( $postmeta ) ) {
					if ( isset( $postmeta['as'] ) && $postmeta['as'] ) {
						$metas[$post_id] = array(
							'post_id' => $post_id,
							'meta'    => $postmeta['as'],
						);
					}
				}
			}
		}

		return $metas;
	}

	public function get_empty_posts( $post_type = 'post', $limit = -1 )
	{
		global $post;

		$args = array(
			'post_type'        => $post_type,
			'post_status'      => array( 'publish', 'draft', 'pending' ),
			'posts_per_page'   => ( $limit ? intval( $limit ) : -1 ),
			// 'nopaging'         => TRUE,
			'surpress_filters' => TRUE,
			'meta_query'       => array(
				array(
					'key'     => $this->constants['remote_meta_key'],
					'compare' => 'NOT EXISTS'
				)
			),
			'tax_query' => array(
				array(
					'taxonomy' => $this->constants['people_tax'],
					'operator' => 'EXISTS', // https://core.trac.wordpress.org/ticket/29181
				),
			),
		);

		$query = new WP_Query( $args );
		$posts = array();

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$posts[$post->ID] = array(
					'post_id' => $post->ID,
					'title'   => $post->post_title,
				);
			}
		}

		return $posts;
	}

	public function meta_row_table( $metas, $row, $column )
	{
		$post_id = is_array( $row ) ? $row['post_id'] : $row->post_id;

		$url = add_query_arg( array(
			'action' => 'edit',
			'post'   => $post_id,
		), get_admin_url( NULL, 'post.php' ) );

		$terms = get_the_term_list( $post_id, $this->constants['people_tax'], '<br />', ', ', '' );
		return $metas.' <small>( <a href="'.$url.'" target="_blank">Edit</a> | <a href="#" target="_blank">View</a> )</small><br /><small>'.$terms.'</small>';
	}

	// wrapper
	public function meta_pre_table( $metas, $row, $column )
	{
		$people = $this->meta_pre( array( $row->meta ), 0, NULL );
		return gPluginUtils::dump_get( $people );
	}

	// FIXME: moved / globalize this!
	public function meta_pre( $meta, $post_id, $meta_key )
	{
		$same = array(
			':'       => '',
			// '/' => '| برگردان ', this is norm on firooze.net
			'/'   => '|',
			'،'   => '|',
			'؛'   => '|',
			';'   => '|',
			','   => '|',
			' و ' => '|',
		);

		$map = array(
			'تالیف'           => 'author',
			'مترجم'           => 'translator',
			'گفت‌وگو و ترجمه' => 'translator',
			'ترجمه و تالیف'   => 'translator',
			'ترجمهٔ'          => 'translator',
			'ترجمه از'        => 'translator',
			'ترجمه'           => 'translator',
			'برگردان از'      => 'translator',
			'برگردان'         => 'translator',
			'گفت‌و‌گو'        => 'interviewer',
			'انتخاب'          => 'selector',
			'طرح‌ها'          => 'illustrator',
			'طرح'             => 'illustrator',
			'عکس‌ها'          => 'photographer',
			'عکس'             => 'photographer',
			'متن‌ها'          => 'commentator',
			'متن'             => 'commentator',
		);

		$people = array();

		foreach ( (array) $meta as $string ) {

			$string = apply_filters( 'string_format_i18n', $string );

			foreach ( $same as $old => $new )
				$string = str_ireplace( $old, $new, $string );

			if ( FALSE !== strpos( $string, '|' ) )
				$parts = explode( '|', $string );
			else
				$parts = array( $string );

			foreach ( $parts as $part ) {
				$passed = FALSE;
				if ( $person = trim( $part ) ) {
					foreach ( $map as $look => $into ) {
						if ( FALSE !== strpos( $person, $look ) ) {
							if ( $name = trim( str_ireplace( $look, '', $person ) ) ) {
								$people[] = array(
									'name'   => apply_filters( 'string_format_i18n', $name ),
									'rel'    => $into,
									'filter' => $this->getFilterFromRel( $into, $name ),
								);
							}
							$passed = TRUE;
							break;
						}
					}
					if ( ! $passed && $person ) {
						$people[] = array(
							'name'   => apply_filters( 'string_format_i18n', $person ),
							'rel'    => 'author',
							'filter' => $this->getFilterFromRel( 'author', $person ),
						);
					}
				}
			}
		}

		return $people;
	}

	// FIXME: moved / globalize this!
	protected function getFilterFromRel( $rel, $name )
	{
		if ( 'author' == $rel )
			return FALSE;

		if ( 'translator' == $rel )
			return 'ترجمه:';

		if ( 'illustrator' == $rel )
			return 'طرح‌ها:';

		if ( 'selector' == $rel )
			return 'انتخاب:';

		if ( 'interviewer' == $rel )
			return 'گفت‌وگو:';

		if ( 'photographer' == $rel )
			return 'عکس‌ها:';

		if ( 'commentator' == $rel )
			return 'متن:';

		return FALSE;
	}

	protected function import_from_terms( $post_id )
	{
		global $gPeopleNetwork;

		$metas    = array();
		$terms    = get_the_terms( $post_id, $this->constants['people_tax'] );
		$pre_data = $gPeopleNetwork->getFilters( 'remote_meta_defaults' );

		if ( $terms && ! is_wp_error( $terms ) )
			foreach ( $terms as $term )
				$metas[] = $gPeopleNetwork->remote->get_remote_meta_data( $term, $pre_data );

		if ( count( $metas ) )
			return $gPeopleNetwork->remote->update_postmeta( $post_id, $metas );

		return FALSE;
	}

	protected function import_array( $metas )
	{
		$i = 0;

		foreach ( $metas as $post_id => $meta ) {
			$parts = (array) apply_filters( 'gpeople_importer_meta_pre', array( $meta['meta'] ), $post_id, NULL );
			$people = array();

			foreach ( $parts as $person ) {
				if ( is_array( $person ) ) {
					$people[] = $person;
				} else {
					$person = trim( $person );

					if ( empty( $person ) )
						continue;

					$people[] = array(
						'name' => apply_filters( 'string_format_i18n', $person ),
					);
				}
			}

			if ( count( $people ) ) {
				$this->set_post_people( $post_id, $people, TRUE );
				$i++;
			}
		}

		return $i;
	}

	protected function import_from_meta( $meta_key, $limit = FALSE, $offset = 0, $delete = FALSE )
	{
		foreach ( gPluginTaxonomyHelper::getMetaRows( $meta_key, $limit ) as $row ) {

			$post_id = $row->post_id;
			$meta = explode( ',', $row->meta );
			$meta = (array) apply_filters( 'gpeople_importer_meta_pre', $meta, $post_id, $meta_key );
			$people = array();

			foreach ( $meta as $person ) {
				if ( is_array( $person ) ) {
					$people[] = $person;
				} else {
					$person = trim( $person );

					if ( empty( $person ) )
						continue;

					$people[] = array(
						'name' => apply_filters( 'string_format_i18n', $person ),
					);
				}
			}

			if ( count( $people ) ) {
				$this->set_post_people( $post_id, $people, TRUE );
				if ( $delete )
					delete_post_meta( $post_id, $meta_key );
			}

		}

		// return gPluginTaxonomyHelper::deleteMetaKeys( $meta_key, $limit );
		return 1;
	}

	// get or create and return people tax object
	public function get_people_object( $name_or_id, $args = FALSE )
	{
		if ( is_numeric( $name_or_id ) )
			$term = get_term_by( 'id', $name_or_id, $this->constants['people_tax'] );
		else
			$term = get_term_by( 'name', $this->normalize( $name_or_id ), $this->constants['people_tax'] );

		if ( $term )
			return $term;

		if ( ! $term && FALSE === $args )
			return FALSE;

		if ( is_numeric( $name_or_id ) )
			return FALSE;

		$new_term = wp_insert_term( $this->normalize( $name_or_id ), $this->constants['people_tax'], $args );
		if ( is_wp_error( $new_term ) )
			return FALSE;

		return get_term_by( 'id', $new_term['term_id'], $this->constants['people_tax'] );
	}

	// programmatically sets people for a post
	public function set_post_people( $post_id, $people, $create = FALSE )
	{
		global $gPeopleNetwork;

		$post = get_post( $post_id );
		if ( ! $post )
			return FALSE;

		$defaults  = $gPeopleNetwork->getFilters( 'remote_meta_defaults' );
		$relations = gPluginTaxonomyHelper::prepareTerms( $gPeopleNetwork->constants['rel_people_tax'], array(), NULL, 'slug' );

		$counter = 1;
		$metas   = array();

		foreach ( $people as $person ) {

			$args = array();
			if ( $create ) {
				if ( isset( $person['slug'] ) )
					$args['slug'] = $person['slug'];
				if ( isset( $person['desc'] ) )
					$args['description'] = $person['desc'];
			} else {
				$args = FALSE;
			}

			$term = $this->get_people_object( $person['name'], $args );

			if ( $term ) {

				$row = $gPeopleNetwork->remote->get_remote_meta_data( $term, $defaults );

				if ( isset( $relations[$person['rel']] ) )
					$row['rel'] = $person['rel'];

				if ( isset( $person['vis'] ) )
					$row['vis'] = $person['vis'];

				// hiding none related persons
				else if ( isset( $person['rel'] ) && 'none' == $person['rel'] )
					$row['vis'] = 'hidden';

				if ( isset( $person['filter'] ) && $person['filter'] && trim( $person['filter'] ) )
					$row['filter'] = $person['filter'];

				if ( isset( $person['order'] ) )
					$row['o'] = $person['order'];
				else
					$row['o'] = $counter;

				$metas[$row['o']] = $row;
				$counter++;
			}
		}

		if ( count( $metas ) ) {

			$success = $gPeopleNetwork->remote->update_postmeta( $post_id, $metas );

			if ( $success == $post_id ) {

				if ( ! $gPeopleNetwork->remote->set_post_terms( $post_id, $metas ) )
					return FALSE;

				return $gPeopleNetwork->remote->get_people( $post_id );
			}
		}

		return FALSE;
	}

	// helper for external importers
	public static function getRelations( $object = FALSE )
	{
		global $gPeopleNetwork;

		if ( ! is_object( $gPeopleNetwork ) )
			return array();

		return gPluginTaxonomyHelper::prepareTerms( $gPeopleNetwork->constants['rel_people_tax'], array(), NULL, 'slug', $object );
	}

	// helper for external importers
	public static function setPeople( $post_id, $people, $create = FALSE )
	{
		global $gPeopleNetwork;

		if ( ! is_object( $gPeopleNetwork ) && ! is_object( $gPeopleNetwork->importer ) )
			return array();

		return $gPeopleNetwork->importer->set_post_people( $post_id, $people, $create );
	}
}
