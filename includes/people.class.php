<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gPeoplePeople extends gPluginModuleCore
{

	public function setup_actions()
	{
		$this->switch = GPEOPLE_ROOT_BLOG != $this->current_blog;

		add_action( 'created_'.$this->constants['people_tax'], array( $this, 'edited_people' ), 10, 2 );
		add_action( 'edited_'.$this->constants['people_tax'], array( $this, 'edited_people' ),10, 2 );
	}

	// people tax edit screen : before wp default form
	public function people_pre_add_form( $taxonomy )
	{
		global $gPeopleNetwork;
		$tax = get_taxonomy( $taxonomy );

		$data = array(
			'form-title' => esc_html( $tax->labels->add_new_item ),
		);

		echo gPeopleMustache::render( 'remote-people-add-before', array_merge(
			$gPeopleNetwork->getFilters( 'people_edit_data' ), $data ) );
	}

	public function people_add_form_fields( $taxonomy )
	{
		echo gPeopleMustache::render( 'remote-people-add-fields', $this->get_people_edit_data() );
	}

	public function people_edit_form_fields( $the_term )
	{
		echo gPeopleMustache::render( 'remote-people-edit-fields', $this->get_people_edit_data( $the_term->term_id ) );

		if ( gPluginWPHelper::isDev() ) {
			global $gPeopleNetwork;

			$term_meta            = $gPeopleNetwork->remote->get_termmeta( $the_term->term_id, FALSE, array() );
			$term_meta_profile_id = get_term_meta( $the_term->term_id, 'people_profile_id', TRUE );
			$term_meta_user_id    = get_term_meta( $the_term->term_id, 'people_user_id', TRUE );

			?><tr class="form-field"><th scope="row" valign="top"><label>DEBUG</label></th><td><?php

				gPluginUtils::dump( $term_meta_profile_id );
				gPluginUtils::dump( $term_meta_user_id );
				gPluginUtils::dump( $term_meta );

			?></td></tr><?php
		}
	}

	private function get_people_edit_data( $term_id = FALSE )
	{
		global $gPeopleNetwork;

		$data = array(
			'user-id'    => '0',
			'profile-id' => '0',
		);

		$affiliation_terms = gPluginTaxonomyHelper::prepareTerms( $this->constants['affiliation_tax'] );

		// TODO : set default term
		$affiliation_selected = '0';

		if ( FALSE !== $term_id ) {

			$term_meta         = $gPeopleNetwork->remote->get_termmeta( $term_id, FALSE, array() );
			$term_affiliations = wp_get_object_terms( $term_id, $this->constants['affiliation_tax'] );

			if ( count( $term_affiliations ) )
				$affiliation_selected = $term_affiliations[0]->term_id;

			if ( isset ( $term_meta['user-id'] ) && $term_meta['user-id'] )
				$user_id = $term_meta['user-id'];

			else if ( $term_meta_user_id = get_term_meta( $term_id, 'people_user_id', TRUE ) )
				$user_id = $term_meta_user_id;

			else
				$user_id = FALSE;

			if ( $user_id ) {
				$data['user-id']   = $user_id;
				$data['user-name'] = get_user_by( 'id', $user_id )->display_name;

				if ( current_user_can( 'manage_network_users' ) )
					$data['user-edit'] = gPluginWPHelper::getUserEditLink( $user_id );

			} else {
				$data['user-no'] = TRUE;
			}

			if ( isset ( $term_meta['profile-id'] ) && $term_meta['profile-id'] )
				$profile_id = $term_meta['profile-id'];

			else if ( $term_meta_profile_id = get_term_meta( $term_id, 'people_profile_id', TRUE ) )
				$profile_id = $term_meta_profile_id;

			else
				$profile_id = FALSE;

			if ( $profile_id ) {
				$data['profile-id'] = $profile_id;

				// FIXME: must get the title
				$data['profile-name'] = __( 'Profile', GPEOPLE_TEXTDOMAIN );

				if ( current_user_can( 'manage_network_users' ) )
					// FIXME: must get profile edit link too
					$data['profile-link'] = $data['profile-edit'] = add_query_arg( array(
							'p' => $profile_id,
						), get_blogaddress_by_id( GPEOPLE_ROOT_BLOG ) );

			} else {
				$data['profile-no'] = TRUE;
			}

			// DEPRECATED
			$data['profile-image'] = $gPeopleNetwork->picture->get_people_image( $term_id, 'thumbnail' );
		}

		if ( empty( $affiliation_terms ) ) {
			$data['affiliation-empty-link'] = gPluginWPHelper::getEditTaxLink( $this->constants['affiliation_tax'] );
			$data['affiliation-empty'] = TRUE;
		} else {
			$data['affiliations'] = array(
				gPluginFormHelper::data_dropdown(
					$affiliation_terms,
					'affiliation-term-id',
					'name',
					$affiliation_selected,
					__( '&mdash; With no affiliations &mdash;', GPEOPLE_TEXTDOMAIN ),
					'0'
			) );
		}

		return array_merge( $gPeopleNetwork->getFilters( 'people_edit_data' ), $data );
	}

	public function edited_people( $term_id, $tt_id )
	{
		if ( isset( $_POST['affiliation-term-id'] ) ) {
			if ( 0 == $_POST['affiliation-term-id'] ) {
				wp_set_object_terms( $term_id, NULL, $this->constants['affiliation_tax'], FALSE );
			} else {
				wp_set_object_terms( $term_id, array( intval( $_POST['affiliation-term-id'] ) ), $this->constants['affiliation_tax'], FALSE );
			}
			clean_object_term_cache( $term_id, $this->constants['affiliation_tax'] );
		}

		$this->edit_people( $term_id );
	}

	private function edit_people( $term_id, $post = NULL )
	{
		global $gPeopleNetwork;

		$term_meta = $gPeopleNetwork->remote->get_termmeta( $term_id, FALSE, array() );

		if ( is_null( $post ) )
			$post = stripslashes_deep( $_POST );

		if ( isset( $post['gpeople_people']['profile_id'] ) ) {
			unset(
				$term_meta['profile-id'],
				$term_meta['profile-link'],
				$term_meta['profile-images']
			);

			if ( $post['gpeople_people']['profile_id'] ) {
				update_term_meta( $term_id, 'people_profile_id', intval( $post['gpeople_people']['profile_id'] ) );

				// DEPRECATED
				// $term_meta['profile-id'] = (int) $post['gpeople_people']['profile_id'];
				// $profile_data = $gPeopleNetwork->profile->get( $term_meta['profile-id'] );
				// $term_meta['profile-link'] = $profile_data['link'];
				// $term_meta['profile-images'] = $profile_data['images'];

				// TODO: save term id on root blog profile meta: must switch
				// maybe : it's better to save the people term feed url into user meta / or on the remote people as custom field

			} else {
				delete_term_meta( $term_id, 'people_profile_id' );
			}
		}

		if ( isset( $post['gpeople_people']['user_id'] ) ) {
			unset( $term_meta['user-id'] );

			if ( $post['gpeople_people']['user_id'] ) {
				update_term_meta( $term_id, 'people_user_id', intval( $post['gpeople_people']['user_id'] ) );
			} else {
				delete_term_meta( $term_id, 'people_user_id' );
			}
		}

		// FIXME: MUST DROP THIS
		$gPeopleNetwork->remote->update_meta( 'term', $term_id, ( count( $term_meta ) ? $term_meta : FALSE ), FALSE );

		return $term_id;
	}

	// modal tab: new people tax
	public function get_tab_manual( $wrap_atts = '' )
	{
		global $gPeopleNetwork;

		$data = $gPeopleNetwork->getFilters( 'remote_tab_manual_data' );
		$data['wrap-atts'] = $wrap_atts;
		$data['form-action'] = admin_url( 'admin-ajax.php' );

		$affiliations_terms = gPluginTaxonomyHelper::prepareTerms( $this->constants['affiliation_tax'] );
		if ( count( $affiliations_terms ) ) {
			$affiliations = gPluginFormHelper::data_dropdown(
				$affiliations_terms,
				'affiliation-term-id',
				'name',
				'0',
				__( '&mdash; With no affiliations &mdash;', GPEOPLE_TEXTDOMAIN )
			);
			$data['affiliations'] = array( $affiliations );
		} else {
			$data['affiliation-empty-link'] = gPluginWPHelper::getEditTaxLink( $this->constants['affiliation_tax'] );
			$data['affiliation-empty'] = TRUE;
		}

		return gPeopleMustache::render( 'remote-people-tab-manual', $data );
	}

	// modal tab: existing people tax
	public function get_tab_terms( $wrap_atts = '' )
	{
		global $gPeopleNetwork;

		$data = $gPeopleNetwork->getFilters( 'remote_tab_terms_data' );
		$data['wrap-atts'] = $wrap_atts;
		$data['form-action'] = admin_url( 'admin-ajax.php' );

		$affiliations_terms = gPluginTaxonomyHelper::prepareTerms( $this->constants['affiliation_tax'] );

		if ( count( $affiliations_terms ) ) {
			$affiliations = gPluginFormHelper::data_dropdown(
				$affiliations_terms,
				'affiliation-term-id',
				'name',
				'0',
				__( '&mdash; People Affiliations &mdash;', GPEOPLE_TEXTDOMAIN )
			);
			$data['dropdown-affiliations'] = array( $affiliations );
		} else {
			$data['affiliation-empty-link'] = gPluginWPHelper::getEditTaxLink( $this->constants['affiliation_tax'] );
			$data['affiliation-empty'] = TRUE;
		}

		return gPeopleMustache::render( 'remote-people-tab-terms', $data );
	}

	// modal tab: new people tax from users
	public function get_tab_users( $wrap_atts = '' )
	{
		global $gPeopleNetwork;

		$data = $gPeopleNetwork->getFilters( 'remote_tab_users_data' );

		$data['wrap-atts']   = $wrap_atts;
		$data['form-action'] = admin_url( 'admin-ajax.php' );

		$roles = gPluginFormHelper::data_dropdown(
			gPluginWPHelper::getUserRoles(),
			'gpeople-user-roles',
			FALSE,
			'0',
			__( '&mdash; User Roles &mdash;', GPEOPLE_TEXTDOMAIN )
		);

		$data['dropdown-roles'] = array( $roles );

		return gPeopleMustache::render( 'remote-people-tab-users', $data );
	}

	// modal tab: new people tax from root profiles
	public function get_tab_profiles( $wrap_atts = '' )
	{
		global $gPeopleNetwork;

		$data = $gPeopleNetwork->getFilters( 'remote_tab_profiles_data' );

		$data['wrap-atts']   = $wrap_atts;
		$data['form-action'] = admin_url( 'admin-ajax.php' );

		$groups = gPluginFormHelper::data_dropdown(
			$gPeopleNetwork->profile->get_groups(),
			'gpeople-profile-groups',
			'name',
			'0',
			__( '&mdash; Profile Groups &mdash;', GPEOPLE_TEXTDOMAIN )
		);

		$data['dropdown-groups'] = array( $groups );

		return gPeopleMustache::render( 'remote-people-tab-profiles', $data );
	}
}
