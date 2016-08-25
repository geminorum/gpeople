<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gPeopleRelation extends gPluginModuleCore
{

	public function setup_actions()
	{
		$this->switch = GPEOPLE_ROOT_BLOG != $this->current_blog;
		$this->before_edit_rel_terms = array();

		add_action( 'edit_terms', array( $this, 'edit_terms' ), 10, 1 );
		add_action( 'edited_term', array( $this, 'edited_term' ), 10, 3 );
		add_action( 'created_term', array( $this, 'created_term' ), 10, 3 );
	}

	public function rel_table_action( $action_name )
	{
		if ( ! isset( $_REQUEST[$action_name] ) )
			return FALSE;

		if ( 'install_default_relations' == $_REQUEST[$action_name] ) {
			global $gPeopleNetwork;

			$added = gPluginTaxonomyHelper::insert_default_terms(
				$this->constants['rel_people_tax'],
				$gPeopleNetwork->getFilters( 'rel_tax_defaults' )
			);

			if ( $added )
				$action = 'added_default_relations';
			else
				$action = 'error_adding_default_relations';

			wp_redirect( add_query_arg( $action_name, $action ) );
			exit;
		}
	}

	public function after_rel_table( $taxonomy )
	{
		$button = __( 'Install Default Relations', GPEOPLE_TEXTDOMAIN );
		$action = 'gpeople_action';
		$url    = add_query_arg( $action, 'install_default_relations' );

		if ( isset( $_REQUEST[$action] ) ) {

			if ( 'error_adding_default_relations' == $_REQUEST[$action] )
				$button = __( 'Error while adding default relations.', GPEOPLE_TEXTDOMAIN );

			else if ( 'added_default_relations' == $_REQUEST[$action] )
				$button = __( 'Default relations added.', GPEOPLE_TEXTDOMAIN );
		}

		?><div class="form-wrap"><p>
			<a href="<?php echo esc_url( $url ); ?>" title="" class="button"><?php echo $button; ?></a>
		</p></div><?php
	}

	// used when a rel people tax info updated
	// before edit : will save the old term and it's slug
	public function edit_terms( $term_id )
	{
		$before_term = get_term_by( 'id', $term_id, $this->constants['rel_people_tax'] );
		if ( ! $before_term )
			return;

		$this->append( 'before_edit_rel_terms', $term_id, $before_term );
	}

	// used when a rel people tax info updated
	// after edit : change the rel_post by the saved old term
	public function edited_term( $term_id, $tt_id, $taxonomy )
	{
		if ( $taxonomy != $this->constants['rel_people_tax'] )
			return;

		if ( ! isset( $this->before_edit_rel_terms[$term_id] ) )
			return;

		$before_term = $this->before_edit_rel_terms[$term_id];
		$after_term  = get_term_by( 'id', $term_id, $this->constants['rel_people_tax'] );
		$post_term   = get_term_by( 'slug', $this->constants['rel_post_tax_pre'].$before_term->slug, $this->constants['rel_post_tax'] );

		wp_update_term( $post_term->term_id, $this->constants['rel_post_tax'], array( 'name' => $after_term->name, 'slug' => $this->constants['rel_post_tax_pre'].$after_term->slug ) );
	}

	// after create new rel term
	public function created_term( $term_id, $tt_id, $taxonomy )
	{
		if ( $taxonomy != $this->constants['rel_people_tax'] )
			return;

		$term = get_term_by( 'id', $term_id, $this->constants['rel_people_tax'] );
		wp_insert_term( $term->name, $this->constants['rel_post_tax'], array( 'slug' => $this->constants['rel_post_tax_pre'].$term->slug ) );
	}
}
