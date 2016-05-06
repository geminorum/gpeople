<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gPeopleRemoteAjax extends gPluginAjaxCore
{

	protected $component = 'remote';

	protected $ajax_action = 'gpeople_remote_people';
	protected $ajax_nonce  = 'gpeople-remote-people';

	public function setup_actions()
	{
		$this->switch = GPEOPLE_ROOT_BLOG != $this->current_blog;

		parent::setup_actions();
	}

	public function asset_config( $scope = NULL, $title = NULL )
	{
		global $gPeopleNetwork;

		if ( is_null( $title ) )
			$title = _x( 'People', 'Modal Title', GPEOPLE_TEXTDOMAIN );

		$gPeopleNetwork->enqueue_asset_config( array(
			'nonce'            => wp_create_nonce( $this->ajax_nonce ),
			'loading'          => gPluginWPHelper::notice( __( 'Please wait &hellip;', GPEOPLE_TEXTDOMAIN ), 'updated fade', FALSE ),
			'spinner'          => __( 'Adding &hellip;', GPEOPLE_TEXTDOMAIN ), //'<span class="spinner"></span>',
			'added'            => __( 'Added', GPEOPLE_TEXTDOMAIN ),
			'error'            => __( 'Error', GPEOPLE_TEXTDOMAIN ),
			'perpage'          => 5,
			'modal_title'      => $title,
			'modal_innerWidth' => 720,
			'modal_maxHeight'  => '100%',
			'adding_resource'  => __( 'Adding &hellip;', GPEOPLE_TEXTDOMAIN ),
		), $scope );
	}

	private function get_table_row( $meta )
	{
		$GLOBALS['hook_suffix'] = '';
		$table = new gPeopleRemoteMetaTable( array( $meta ) );

		// $table->prepare_items();
		return $table->get_single_row( $meta );
	}

	// on post modal
	protected function sub_store_meta( $post )
	{
		global $gPeopleNetwork;

		if ( isset( $post['gpeople_post_id'] ) && $post['gpeople_post_id']  ) {

			$post_id = $post['gpeople_post_id'];

			if ( isset( $post['people_internal'] ) && count( $post['people_internal'] ) ) {

	            $data     = $gPeopleNetwork->getFilters( 'remote_meta_data' );
	            $defaults = $gPeopleNetwork->getFilters( 'remote_meta_defaults' );

				$counter = 1;
	            $meta    = array();

				foreach ( $post['people_internal'] as $internal ) {

					$row = array();

					foreach ( $data as $data_key => $data_default )

						if ( isset( $post['people_'.$data_key][$internal] )
							&& $post['people_'.$data_key][$internal] )
								$row[$data_key] = $post['people_'.$data_key][$internal];

						else if ( isset( $defaults[$data_key] ) )
							$row[$data_key] = $defaults[$data_key];

					if ( empty( $row['o'] ) )
						$row['o'] = $counter;

					$meta[$row['o']] = $row;
					$counter++;
				}

				ksort( $meta, SORT_NUMERIC );

				$success = $gPeopleNetwork->remote->update_postmeta( $post_id, $meta );

				if ( $success == $post_id ) {

					if ( count( $meta ) ) {

						if ( ! $gPeopleNetwork->remote->set_post_terms( $post_id, $meta ) )
							self::sendError( __( 'Errors while setting the terms!', GPEOPLE_TEXTDOMAIN ) );

					} else {

						// if no meta then reset the terms
						wp_set_object_terms( $post_id, NULL, $this->constants['people_tax'], FALSE );
						wp_set_object_terms( $post_id, NULL, $this->constants['rel_post_tax'], FALSE );
					}

					wp_send_json_success( $gPeopleNetwork->remote->get_people( $post_id ) );

				} else {
					self::sendError( __( 'Cannot save meta', GPEOPLE_TEXTDOMAIN ) );
				}

			} else {
				// wp_send_json_error( '<div class="error"><p>'.__( 'The form is empty', GPEOPLE_TEXTDOMAIN ).'</p></div>' );
				$gPeopleNetwork->remote->update_postmeta( $post_id, array() );
				wp_send_json_success( '' );
			}

		} else {
			self::sendError( __( 'No Post ID!', GPEOPLE_TEXTDOMAIN ) );
		}

		return FALSE;
	}

	// on post modal
	protected function sub_insert_term( $post )
	{
		global $gPeopleNetwork;

		if ( isset( $post['rel'] ) && $post['rel'] ) {

			$term = get_term_by( 'id', (int) $post['rel'], $this->constants['people_tax'] );

			if ( $term ) {

				$defaults = $gPeopleNetwork->getFilters( 'remote_meta_defaults' );
				$meta     = $gPeopleNetwork->remote->get_remote_meta_data( $term, $defaults );

				$data = array(
					'row'     => $this->get_table_row( $meta ),
					'message' => '<div class="updated"><p>'.sprintf( __( '%s added.', GPEOPLE_TEXTDOMAIN ), $term->name ).'</p></div>',
				);

				wp_send_json_success( $data );
			} else {
				self::sendError( __( 'Term Not found', GPEOPLE_TEXTDOMAIN ) );
			}
		} else {
			self::sendError( __( 'No term ID', GPEOPLE_TEXTDOMAIN ) );
		}

		return FALSE;
	}

	// on post modal
	protected function sub_insert_manual( $post )
	{
		global $gPeopleNetwork;

		$term = isset( $post['term'] ) ? gPluginUtils::parseJSArray( $post['term'] ) : array();
		if ( count( $term ) ) {

			$term_args = apply_filters( 'gpeople_remote_insert_people_term', array(
				'name'        => $term['gpeople_manual_name'],
				'slug'        => $term['gpeople_manual_slug'],
				'description' => $term['gpeople_manual_description'],
			) );

			// SEE:
			// add_filter( 'pre_insert_term', array( $this, 'pre_insert_term' ), 10, 2 );
			// add_filter( 'pre_term_name', array( $this, 'translate_numbers' ) );
			// add_filter( 'pre_term_description', array( $this, 'translate_numbers_html' ) );

			if ( $term_args['name'] && ! term_exists( $term_args['name'], $this->constants['people_tax'] ) ) {

				$term_id = wp_insert_term( $term_args['name'], $this->constants['people_tax'], $term_args );

				if ( ! is_wp_error( $term_id ) ) {

					$new_term = get_term_by( 'id', $term_id['term_id'], $this->constants['people_tax'] );

					if ( $new_term ) {

						if ( isset( $term['affiliation-term-id'] ) && $term['affiliation-term-id'] )
							wp_set_object_terms( $term_id['term_id'], array( intval( $term['affiliation-term-id'] ) ), $this->constants['affiliation_tax'], FALSE );

						// DEPRECATED
						// to add the profile on root blog with this info
						// do_action( 'gpeople_remote_after_insert_people', $new_term, $term_args );

						$pre_data = $gPeopleNetwork->getFilters( 'remote_meta_defaults' );
						$meta     = $gPeopleNetwork->remote->get_remote_meta_data( $new_term, $pre_data );

						$data = array(
							'row'     => $this->get_table_row( $meta ),
							'message' => gPluginWPHelper::notice(
								sprintf( __( '%s added.', GPEOPLE_TEXTDOMAIN ), $new_term->name ), 'updated', FALSE ),
						);

						wp_send_json_success( $data );

					} else {
						self::sendError( __( 'Can not save the term.', GPEOPLE_TEXTDOMAIN ) );
					}
				} else {
					self::sendError( $term_id->get_error_message() );
				}
			} else {
				self::sendError( __( 'The term already exists.', GPEOPLE_TEXTDOMAIN ) );
			}
		} else {
			self::sendError( __( 'The form is empty.', GPEOPLE_TEXTDOMAIN ) );
		}

		return FALSE;
	}

	// on post modal
	protected function sub_search_users( $post )
	{
		global $gPeopleNetwork;

		$user_args = array(
			// 'blog_id'     => '',
			'number'      => intval( $post['per_page'] ),
			'offset'      => intval( $post['paged'] ),
			'who'         => 'authors',
			'fields'      => 'all_with_meta',
			'orderby'     => 'registered',
			'order'       => 'DESC',
			'count_total' => TRUE,
		);

		if ( isset( $post['criteria'] ) && $post['criteria'] )
			$user_args['search'] = $post['criteria'];

		if ( isset( $post['roles'] ) && $post['roles'] )
			$user_args['role'] = $post['roles'];

		add_filter( 'user_search_columns', array( $this, 'user_search_columns' ), 10, 3 );
		$users = get_users( $user_args );
		// $users = gPluginUtils::reKey( $users, 'ID' );
		// $user_search = new WP_User_Query( $user_args ); // useing the query because of total_users
		// $users = (array) $user_search->get_results();

		if ( count( $users ) ) {

			// $gPeopleRemoteComponent = gPluginFactory::get( 'gPeopleRemoteComponent' );
			$data_html = $gPeopleNetwork->getFilters( 'people_edit_search_users' );
			$pre_data  = $data_raw = array();

			foreach ( $users as $user ) {
				$data_html['list'][] = $data_raw[$user->ID] = $gPeopleNetwork->user->get_data( $user, $pre_data );
			}

			$data = array(
				'list'    => $data_raw,
				'html'    => gPeopleMustache::render( 'remote-people-search-users', $data_html ),
				'message' => gPluginWPHelper::notice( sprintf( __( '%s users found', GPEOPLE_TEXTDOMAIN ),
					number_format_i18n( count( $users ) ) ), 'updated', FALSE ),
			);

			wp_send_json_success( $data );

		} else {
			self::sendError( __( 'No user matches your criteria.', GPEOPLE_TEXTDOMAIN ) );
		}

		return FALSE;
	}

	// modifying the WP_User_Query for our ajax search
	public function user_search_columns( $search_columns, $search, $query )
	{
		return array( 'display_name' );
	}

	// on post modal
	protected function sub_search_terms( $post )
	{
		global $gPeopleNetwork;

		$term_args = array(
			'hide_empty' => 0,
			'fields'     => 'count',
			'get'        => 'all',
		);

		if ( isset( $post['criteria'] ) && $post['criteria'] )
			$term_args['search'] = $post['criteria'];

		if ( isset( $post['affiliation'] ) && $post['affiliation'] ) {
			$affiliations = get_objects_in_term( intval( $post['affiliation'] ), $this->constants['affiliation_tax'] );
			if ( ! is_wp_error( $affiliations ) && count( $affiliations ) ) {
				$term_args['include'] = array_filter( array_values( $affiliations ), 'intval' );
			} else {
				self::sendError( __( 'Nobody matches your criteria.', GPEOPLE_TEXTDOMAIN ) );
			}
		}

		$terms_count = get_terms( array( $this->constants['people_tax'] ) ,$term_args );

		if ( $terms_count ) {

			$term_args['number'] = (int) $post['per_page'];
			$term_args['offset'] = ( (int) $post['paged'] * $term_args['number'] );
			$term_args['fields'] = 'all';

			$terms = get_terms( array( $this->constants['people_tax'] ) ,$term_args );

			$data_html = $gPeopleNetwork->getFilters( 'people_edit_search_terms' );
			$pre_data = $data_raw = array();

			foreach ( $terms as $term ) {
				$data_html['list'][] = $data_raw[$term->term_id] = $gPeopleNetwork->remote->get_remote_term_data( $term, $pre_data );
			}

			$from  = ( 0 == $post['paged'] ? 1 : ( $post['paged'] * $term_args['number'] )+1 );
			$till  = ( 0 == $post['paged'] ? $term_args['number'] : ( $post['paged'] * $term_args['number'] ) + $term_args['number'] );
			$till  = ( $till > $terms_count ? $terms_count : $till );
			$next  = ( $till < $terms_count ? sprintf( __( '<a href="#" rel="%1$s" class="%2$s">Next results</a>', GPEOPLE_TEXTDOMAIN ), $post['paged']+1, 'gpeople-data-list-terms-next' ) : '' );
			$prev  = ( 0 == $post['paged'] ? '' : sprintf( __( '<a href="#" rel="%1$s" class="%2$s">Previous results</a>', GPEOPLE_TEXTDOMAIN ), $post['paged']-1, 'gpeople-data-list-terms-next' ) );
			$after = ( $next && $prev ? $prev.' / '.$next : $prev.$next );
			$after = ( $after ? ' &mdash; '.$after : '' );

			$data = array(
				'list' => $data_raw,
				'html' => gPeopleMustache::render( 'remote-people-search-terms', $data_html ),

				// 'message' => sprintf( __( 'Founded %s profile', GPEOPLE_TEXTDOMAIN ), count( $data_raw ) ),
				// 'message' => '<div class="updated"><p>'.sprintf( __( '%s people found', GPEOPLE_TEXTDOMAIN ), number_format_i18n( count( $terms ) ) ).'</p></div>',

				'message' => '<div class="updated"><p>'.sprintf(
					__( 'Showing %1$s-%2$s results out of %3$s people found.%4$s', GPEOPLE_TEXTDOMAIN ),
						number_format_i18n( $from ),
						number_format_i18n( $till ),
						number_format_i18n( $terms_count ),
						$after
						//number_format_i18n( $term_args['number'] )
						//number_format_i18n( count( $terms ) )
				).'</p></div>',
				// gpeople-data-list-terms-next
				// Showing 1-20 results out of 45,015,599 people found

			);
			wp_send_json_success( $data );

		} else {
			self::sendError( __( 'Nobody matches your criteria.', GPEOPLE_TEXTDOMAIN ) );
		}

		return FALSE;
	}

	// case 'search_profiles' :
	// case 'search_profiles_add' :
	// case 'search_profiles_edit' :
	protected function sub_search_profiles( $post )
	{
		global $gPeopleNetwork;

		if ( $this->switch ) {
			switch_to_blog( constant( 'GPEOPLE_ROOT_BLOG' ) );
			gPeopleRootComponent::switch_setup( $this->constants );
		}

		// http://www.billerickson.net/code/wp_query-arguments/
		$profile_args = array(
			'post_type'              => $this->constants['profile_cpt'],
			// 'post_status'            => 'future',
			// 'order'                  => 'ASC', // orderby default is date
			'posts_per_page'         => -1,
			// 'offset'                 => (int) $post['paged'],
			'nopaging'               => TRUE,
			'update_post_term_cache' => FALSE,
			'update_post_meta_cache' => FALSE,
		);

		if ( isset( $post['criteria'] ) && $post['criteria'] )
			$profile_args['s'] = $post['criteria'];

		if ( isset( $post['group'] ) && $post['group'] ) {
			$profile_args['tax_query'] = array( array(
				'taxonomy' => $this->constants['group_tax'],
				'field'    => 'id',
				'terms'    => array( intval( $post['group'] ) ),
				'operator' => 'IN',
			) );
		}

		$profile_query = new WP_Query( $profile_args );

		if ( $profile_query->have_posts() ) {

			$profile_count                  = (int) $profile_query->found_posts;
			$profile_args['posts_per_page'] = (int) $post['per_page'];
			$profile_args['offset']         = (int) $post['paged'];
			$profile_args['nopaging']       = FALSE;

			$profile_query = new WP_Query( $profile_args );

			global $post;
			$pre_data  = $data_raw = array();
			$data_html = $gPeopleNetwork->getFilters( 'people_edit_search_profiles' );

			if ( isset( $post['subsub'] ) && 'edit' == $post['subsub'] )
				$pre_data['override'] = TRUE;
			else
				$pre_data['new'] = TRUE;

			while ( $profile_query->have_posts() ) {
				$profile_query->the_post();
				$data_html['list'][] = $data_raw[$post->ID] = $gPeopleNetwork->profile->get_data( $post, $pre_data );
			}
			// wp_reset_postdata(); // NO NEED : probably!

			$from  = ( 0 == $profile_args['offset'] ? 1 : ( $profile_args['offset'] * $profile_args['posts_per_page'] )+1 );
			$till  = ( 0 == $profile_args['offset'] ? $profile_args['posts_per_page'] : ( $profile_args['offset'] * $profile_args['posts_per_page'] ) + $profile_args['posts_per_page'] );
			$till  = ( $till > $profile_count ? $profile_count : $till );
			$next  = ( $till < $profile_count ? sprintf( __( '<a href="#" rel="%1$s" class="%2$s">Next results</a>', GPEOPLE_TEXTDOMAIN ), $profile_args['offset']+1, 'gpeople-data-list-profiles-next' ) : '' );
			$prev  = ( 0 == $profile_args['offset'] ? '' : sprintf( __( '<a href="#" rel="%1$s" class="%2$s">Previous results</a>', GPEOPLE_TEXTDOMAIN ), $profile_args['offset']-1, 'gpeople-data-list-profiles-next' ) );
			$after = ( $next && $prev ? $prev.' / '.$next : $prev.$next );
			$after = ( $after ? ' &mdash; '.$after : '' );

			$data = array(
				'list' => $data_raw,
				'html' => gPeopleMustache::render( 'remote-people-search-profile', $data_html ),
				// 'message' => sprintf( __( 'Founded %s profile', GPEOPLE_TEXTDOMAIN ), count( $data_raw ) ),
				// Showing 1-20 results out of 45,015,599 people found
				// 'message' => '<div class="updated"><p>'.sprintf( __( '%s profile found', GPEOPLE_TEXTDOMAIN ), number_format_i18n( $profile_query->found_posts ) ).'</p></div>',
				'message' => '<div class="updated"><p>'.sprintf(
					__( 'Showing %1$s-%2$s results out of %3$s profile found.%4$s', GPEOPLE_TEXTDOMAIN ),
						number_format_i18n( $from ),
						number_format_i18n( $till ),
						number_format_i18n( $profile_count ),
						$after
				).'</p></div>',

			);

			if ( $this->switch ) // NO NEED : probably!
				restore_current_blog();

			wp_send_json_success( $data );
		} else {
			self::sendError( __( 'No profile matches your criteria.', GPEOPLE_TEXTDOMAIN ) );
		}

		return FALSE;
	}

	protected function sub_nosub( $post )
	{
		self::sendError( __( 'dont know what to do?', GPEOPLE_TEXTDOMAIN ) );

		return FALSE;
	}
}
