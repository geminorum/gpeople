<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gPeopleRemoteMetaTable extends WP_List_Table
{

	private $people_tax = 'people';

	private $meta           = array();
	private $meta_columns   = array();
	private $visibility     = array();
	private $relations      = array();
	private $pre_data       = array();

	private $internal = 1000;
	private $current  = 0;

	public function __construct( $post_id_or_meta )
	{
		parent::__construct( array(
			'singular' => _x( 'Person', 'Meta Table', GPEOPLE_TEXTDOMAIN ),
			'plural'   => _x( 'People', 'Meta Table', GPEOPLE_TEXTDOMAIN ),
			'ajax'     => FALSE,
		) );

		global $gPeopleNetwork;

		$this->people_tax   = $gPeopleNetwork->constants['people_tax'];
		$this->meta_columns = $gPeopleNetwork->getFilters( 'remote_meta_columns' );;
		$this->visibility   = $gPeopleNetwork->getFilters( 'remote_meta_visibility' );;
		$this->pre_data     = $gPeopleNetwork->getFilters( 'remote_meta_defaults' );;
		$this->relations    = gPluginTaxonomyHelper::prepareTerms( $gPeopleNetwork->constants['rel_people_tax'], array(), NULL, 'slug' );

		if ( is_array( $post_id_or_meta ) )
			$this->meta = $post_id_or_meta;
		else
			$this->meta = $gPeopleNetwork->remote->get_postmeta( $post_id_or_meta, FALSE, array() );
	}

	public function get_columns()
	{
		return array_merge(
			array( 'cb' => '<input type="checkbox" />' ),
			$this->meta_columns
		);
	}

	protected function column_cb( $item )
	{
		if ( isset( $item['o'] ) && $item['o'] ) {
			$this->current = $item['o'];
		} else {
			$key = $this->internal + (int) $item['id'];
			$this->current = $this->internal = $key;
			$this->internal++;
		}

		$output = sprintf( '<input type="hidden" name="people_internal[%1$s]" value="%2$s" />', $this->current, $this->current );
		$output .= sprintf( '<input type="checkbox" name="people_cb[%1$s]" />', $this->current );
		return $output;
	}

	protected function get_default_primary_column_name()
	{
		return 'people_id';
	}

	protected function column_default( $item, $column_name )
	{
		switch ( $column_name ) {

			case 'people_id' :

				if ( isset( $item['temp'] ) && $item['temp'] )
					$name = $item['temp']; // must alert this!!!
				else
					$name = __( '&mdash; Not Definded &mdash;', GPEOPLE_TEXTDOMAIN );

				if ( ! isset( $item['id'] ) )
					$item['id'] = '0';

				if ( $item['id'] && $term = get_term_by( 'id', $item['id'], $this->people_tax ) )
					$name = $term->name;

				// FIXME
				$actions = array(
					// 'edit' => '',//sprintf('<a href="?page=%s&action=%s&book=%s">Edit</a>',$_REQUEST['page'],'edit',$item['ID']),
					'edit' => sprintf('<a href="#" class="gpeople-people-edit" rel="%s">Edit</a>', $item['id'] ),
					// 'delete' => '', //sprintf('<a href="?page=%s&action=%s&book=%s">Delete</a>',$_REQUEST['page'],'delete',$item['ID']),
					'delete' => sprintf('<a href="#" class="gpeople-people-delete" rel="%s">Delete</a>', $item['id'] ),
				);

				$output = sprintf('%1$s %2$s', $name, $this->row_actions( $actions ) );

				$output .= sprintf( '<input type="hidden" name="people_id[%1$s]" value="%2$s" />', $this->current, $item['id'] );
				$output .= sprintf( '<input type="hidden" name="people_temp[%1$s]" value="%2$s" />', $this->current, ( isset( $item['temp'] ) ? $item['temp'] : '' ) );

				return $output;

			break;
			case 'people_o' :

				return vsprintf( '<input type="text" name="people_o[%1$s]" value="%2$s" />', array(
					$this->current,
					( isset( $item['o'] ) ? $item['o'] : ( isset( $this->pre_data['o'] ) ? $this->pre_data['o'] : '0' ) ),
				) );

			break;
			case 'people_filt_over' :

				$output = vsprintf( '<input type="text" name="people_filter[%1$s]" value="%2$s" />', array(
					$this->current,
					( isset( $item['filter'] ) ? $item['filter'] : ( isset( $this->pre_data['filter'] ) ? $this->pre_data['filter'] : '' ) ),
				) );

				$output .= '<br />';

				$output .= vsprintf( '<input type="text" name="people_override[%1$s]" value="%2$s" />', array(
					$this->current,
					( isset( $item['override'] ) ? $item['override'] : ( isset( $this->pre_data['override'] ) ? $this->pre_data['override'] : '' ) ),
				) );

				return $output;

			break;
			case 'people_rel_vis' :

				$output = gPluginHTML::dropdown( $this->relations, array(
					'name'       => sprintf( 'people_rel[%s]', $this->current ),
					'prop'       => 'name',
					'selected'   => isset( $item['rel'] ) ? $item['rel'] : ( isset( $this->pre_data['rel'] ) ? $this->pre_data['rel'] : 'none' ),
					'none_title' => __( '&mdash; No Relations &mdash;', GPEOPLE_TEXTDOMAIN ),
					'none_value' => 'none',
				), TRUE );

				$output .= '<br />';

				$output .= gPluginHTML::dropdown( $this->visibility, array(
					'name'     => sprintf( 'people_vis[%s]', $this->current ),
					'selected' => isset( $item['vis'] ) ? $item['vis'] : ( isset( $this->pre_data['vis'] ) ? $this->pre_data['vis'] : 'tagged' ),
				), TRUE );

				return $output;

			break;
		}

		return print_r( $item, TRUE );
	}

	public function prepare_items()
	{
		$this->_column_headers = array(
			$this->get_columns(),
			array(),
			$this->get_sortable_columns(),
			array(),
		);

		// usort( $this->example_data, array( $this, 'usort_reorder' ) );

		/*
		$gPeopleRemoteComponent = gPluginFactory::get( 'gPeopleRemoteComponent' );
		// $meta = $gPeopleRemoteComponent->get_postmeta( $this->_post_id, FALSE, array(
		$meta = $gPeopleRemoteComponent->get_postmeta( $this->_post_id, FALSE, array(
			'1' => array(
				'o' => '1',
				'id' => 9,
				'vis' => 'public',
				'filter' => 'FFFFF %s',
				'override' => 'Ham',
				'rel' => 'none',
				'temp' => '',
			),
			'6' => array(
				'o' => '2',
				'id' => 0,
				'vis' => 'public',
				'filter' => 'FFFFF %s',
				'override' => 'Ham',
				'rel' => 'none',
				'temp' => '',

			),
			'5' => array(
				'o' => '3',
				'id' => 4,
				'vis' => 'tagged',
				'filter' => 'FFFFasdasdF %s',
				'override' => 'Hassssssss',
				'rel' => 'none',
				'temp' => '',

			) ) );
		**/

		$per_page    = 10;
		$this->items = array_slice( $this->meta,( ( $this->get_pagenum() - 1 ) * $per_page ), $per_page );

		$this->set_pagination_args( array(
			'total_items' => count( $this->meta ),
			'per_page'    => $per_page,
		) );
	}

	public function single_row( $item )
	{
		static $alternate_class = '';

		$alternate_class = ( $alternate_class == '' ? ' alternate' : '' );
		$row_class       = ' class="gpeople_meta_row' . $alternate_class . '"';

		echo '<tr id="gpeople_people_term-'.$this->current.'" rel="'.( isset( $item['id'] ) ? $item['id'] : '0' ).'"'.$row_class.'>';
			$this->single_row_columns( $item );
		echo '</tr>';
	}

	public function get_single_row( $item )
	{
		ob_start();
		$this->single_row( $item );
		return ob_get_clean();
	}

	public function no_items()
	{
		_e( 'Nobody assigned yet.', GPEOPLE_TEXTDOMAIN );
	}

	protected function get_sortable_columns()
	{
		return array();

		$sortable_columns = array(
			'booktitle' => array( 'booktitle',FALSE ),
			'author'    => array( 'author',FALSE ),
			'isbn'      => array( 'isbn',FALSE ),
		);

		return $sortable_columns;
	}

	public function usort_reorder( $a, $b )
	{
		// if no sort, default to title
		$orderby = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'booktitle';

		// if no order, default to asc
		$order = ( ! empty($_GET['order'] ) ) ? $_GET['order'] : 'asc';

		// determine sort order
		$result = strcmp( $a[$orderby], $b[$orderby] );

		// send final sort direction to usort
		return ( $order === 'asc' ) ? $result : -$result;
	}

	public function get_bulk_actions()
	{
		return array(
			'delete' => __( 'Delete', GPEOPLE_TEXTDOMAIN ),
		);
	}

	protected function display_tablenav( $which )
	{
		if ( 'top' == $which ) {
			wp_nonce_field( 'bulk-'.$this->_args['plural'] );
			return;
		}
		parent::display_tablenav( $which );
	}

	public function extra_tablenav( $which )
	{
		echo '<div class="alignleft actions">';
			submit_button( __( 'Save & Back to Post', GPEOPLE_TEXTDOMAIN ), 'secondary', 'gpeople_meta_save_close', FALSE );
		echo '</div>';
	}

	////////////////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////////////////
	// net.tutsplus.com/tutorials/javascript-ajax/submit-a-form-without-page-refresh-using-jquery/
	// see gMeta .ajaxform()
}
