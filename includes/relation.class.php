<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gPeopleRelation extends gPluginModuleCore
{

	public function setup_actions()
	{
		$this->switch = GPEOPLE_ROOT_BLOG != $this->current_blog;
		$this->before_edit_rel_terms = array();

		add_filter( 'wp_update_term_data', array( $this, 'wp_update_term_data' ), 10, 4 );
		add_action( 'edited_'.$this->constants['rel_people_tax'], array( $this, 'edited_term' ), 10, 2 );
		add_action( 'created_'.$this->constants['rel_people_tax'], array( $this, 'created_term' ), 10, 2 );
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
	// before edit: keeping the old term object
	public function wp_update_term_data( $data, $term_id, $taxonomy, $args )
	{
		if ( $taxonomy != $this->constants['rel_people_tax'] )
			return $data;

		if ( ! $term = get_term_by( 'id', $term_id, $this->constants['rel_people_tax'] ) )
			return $data;

		$this->append( 'before_edit_rel_terms', $term_id, $term );

		return $data;
	}

	// used when a rel people tax info updated
	// after edit: change the rel_post by the old term
	public function edited_term( $term_id, $tt_id )
	{
		if ( ! isset( $this->before_edit_rel_terms[$term_id] ) )
			return;

		$before = $this->before_edit_rel_terms[$term_id];
		$edited = get_term_by( 'id', $term_id, $this->constants['rel_people_tax'] );

		if ( $mirrored = get_term_by( 'slug', $this->constants['rel_post_tax_pre'].$before->slug, $this->constants['rel_post_tax'] ) )
			wp_update_term( $mirrored->term_id, $this->constants['rel_post_tax'], array(
				'name' => $edited->name,
				'slug' => $this->constants['rel_post_tax_pre'].$edited->slug,
			) );
	}

	// after create new rel term
	public function created_term( $term_id, $tt_id )
	{
		if ( $term = get_term_by( 'id', $term_id, $this->constants['rel_people_tax'] ) )
			wp_insert_term( $term->name, $this->constants['rel_post_tax'], array(
				'slug' => $this->constants['rel_post_tax_pre'].$term->slug,
			) );
	}
}
