<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

// Mock-up of gPluginListTableCore ( from : 3.7-alpha-25343 )
// mainly because it's a private class for wp core : http://wpengineer.com/2426/gPluginListTableCore-a-step-by-step-guide/#comment-9617
// SEE: https://core.trac.wordpress.org/ticket/30914

if ( ! class_exists( 'gPluginListTableCore' ) ) { class gPluginListTableCore extends gPluginClassCore
{

	public function setup_globals( $constants = array(), $args = array() )
	{
		self::__dep();

		$this->args = gPluginUtils::recursiveParseArgs( $args, array(
			'domain'   => 'gplugin',
			'title'    => 'gPlugin',
			'plural'   => '',
			'singular' => '',
			'ajax'     => FALSE,
			'screen'   => NULL,
			'options'  => array(),
		) );

		// $this->screen = convert_to_screen( $args['screen'] );

		if ( $this->args['ajax'] )
			add_action( 'admin_footer', array( $this, '_js_vars' ) );

		$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
		$this->current_url = remove_query_arg( 'paged', $current_url );
	}

	public static function get_column_headers( $screen_id )
	{
		static $column_headers = array();

		if ( ! isset( $column_headers[$screen_id] ) )
			$column_headers[$screen_id] = apply_filters( 'manage_'.$screen_id.'_columns', array() );

		return $column_headers[$screen_id];
	}

	public static function get_hidden_columns( $screen_id )
	{
		return (array) get_user_option( 'manage'.$screen_id.'columnshidden' );
	}

	public static function search_query()
	{
		// echo isset( $_REQUEST['s'] ) ? esc_attr( gPluginUtils::unslash( $_REQUEST['s'] ) ) : '';
		echo get_search_query();
	}

	// Checks the current user's permissions
	function ajax_user_can()
	{
		die( 'function gPluginListTableCore::ajax_user_can() must be over-ridden in a sub-class.' );
	}

	// Prepares the list of items for displaying.
	function prepare_items()
	{
		die( 'function gPluginListTableCore::prepare_items() must be over-ridden in a sub-class.' );
	}

	// An internal method that sets all the necessary pagination arguments
	function set_pagination_args( $args )
	{
		$args = wp_parse_args( $args, array(
			'total_items' => 0,
			'total_pages' => 0,
			'per_page'    => 0,
		) );

		if ( ! $args['total_pages'] && $args['per_page'] > 0 )
			$args['total_pages'] = ceil( $args['total_items'] / $args['per_page'] );

		// redirect if page number is invalid and headers are not already sent
		if ( ! headers_sent() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) && $args['total_pages'] > 0 && $this->get_pagenum() > $args['total_pages'] ) {
			wp_redirect( add_query_arg( 'paged', $args['total_pages'] ) );
			exit;
		}

		$this->_pagination_args = $args;
	}

	// Access the pagination args
	function get_pagination_arg( $key )
	{
		if ( 'page' == $key )
			return $this->get_pagenum();

		if ( isset( $this->_pagination_args[$key] ) )
			return $this->_pagination_args[$key];
	}

	// Display the table
	function display()
	{
		$this->display_tablenav( 'top' );
		?><table class="wp-list-table gplugin-list-table <?php echo implode( ' ', $this->get_table_classes() ); ?>" cellspacing="0">
			<thead><tr><?php $this->print_column_headers(); ?></tr></thead>
			<tfoot><tr><?php $this->print_column_headers( FALSE ); ?></tr></tfoot>
			<tbody id="the-list"<?php if ( $this->args['singular'] ) echo " data-wp-lists='list:".$this->args['singular']."'"; ?>>
				<?php $this->display_rows_or_placeholder(); ?>
			</tbody>
		</table> <?php
		$this->display_tablenav( 'bottom' );
	}

	// Get a list of CSS classes for the <table> tag
	function get_table_classes()
	{
		return array( 'widefat', 'fixed', $this->args['plural'] );
	}

	// Generate the table navigation above or below the table
	function display_tablenav( $which = 'top' )
	{
		if ( 'top' == $which )
			wp_nonce_field( 'bulk-' . $this->_args['plural'] );
		?><div class="tablenav <?php echo esc_attr( $which ); ?>">
			<div class="alignleft actions bulkactions"><?php $this->bulk_actions(); ?></div><?php

			$this->extra_tablenav( $which );
			$this->pagination( $which );

		?><br class="clear" /></div> <?php

	}

	// Print column headers, accounting for hidden and sortable columns.
	function print_column_headers( $with_id = TRUE )
	{
		list( $columns, $hidden, $sortable ) = $this->get_column_info();

		if ( isset( $_GET['orderby'] ) )
			$current_orderby = $_GET['orderby'];
		else
			$current_orderby = '';

		if ( isset( $_GET['order'] ) && 'desc' == $_GET['order'] )
			$current_order = 'desc';
		else
			$current_order = 'asc';

		if ( ! empty( $columns['cb'] ) ) {
			static $cb_counter = 1;
			$columns['cb'] = '<label class="screen-reader-text" for="cb-select-all-'.$cb_counter.'">'.__( 'Select All' ).'</label>'
				.'<input id="cb-select-all-'.$cb_counter.'" type="checkbox" />';
			$cb_counter++;
		}

		foreach ( $columns as $column_key => $column_display_name ) {
			$class = array( 'manage-column', "column-$column_key" );

			$style = '';
			if ( in_array( $column_key, $hidden ) )
				$style = 'display:none;';

			$style = ' style="' . $style . '"';

			if ( 'cb' == $column_key )
				$class[] = 'check-column';
			else if ( in_array( $column_key, array( 'posts', 'comments', 'links' ) ) )
				$class[] = 'num';

			if ( isset( $sortable[$column_key] ) ) {
				list( $orderby, $desc_first ) = $sortable[$column_key];

				if ( $current_orderby == $orderby ) {
					$order = 'asc' == $current_order ? 'desc' : 'asc';
					$class[] = 'sorted';
					$class[] = $current_order;
				} else {
					$order = $desc_first ? 'desc' : 'asc';
					$class[] = 'sortable';
					$class[] = $desc_first ? 'asc' : 'desc';
				}

				$column_display_name = '<a href="'.esc_url( add_query_arg( compact( 'orderby', 'order' ), $this->current_url ) ) . '"><span>'.$column_display_name.'</span><span class="sorting-indicator"></span></a>';
			}

			$id = $with_id ? "id='$column_key'" : '';

			if ( !empty( $class ) )
				$class = "class='".join( ' ', $class )."'";

			echo "<th scope='col' $id $class $style>$column_display_name</th>";
		}
	}

	// Get a list of all, hidden and sortable columns, with filter applied
	function get_column_info()
	{
		if ( isset( $this->_column_headers ) )
			return $this->_column_headers;

		$columns = self::get_column_headers( $this->args['screen'] );
		$hidden = self::get_hidden_columns( $this->args['screen'] );

		$sortable = array();
		foreach ( $this->get_sortable_columns() as $id => $data ) {
			if ( empty( $data ) )
				continue;

			$data = (array) $data;
			if ( !isset( $data[1] ) )
				$data[1] = FALSE;

			$sortable[$id] = $data;
		}

		$this->_column_headers = array( $columns, $hidden, $sortable );

		return $this->_column_headers;
	}

	// Get a list of columns. The format is:
	// 'internal-name' => 'Title'
	function get_columns()
	{
		die( 'function gPluginListTableCore::get_columns() must be over-ridden in a sub-class.' );
	}

	/**
	 * Get a list of sortable columns. The format is:
	 * 'internal-name' => 'orderby'
	 * or
	 * 'internal-name' => array( 'orderby', TRUE )
	 * The second format will make the initial sorting order be descending
	 */
	function get_sortable_columns()
	{
		return array();
	}

	// Return number of visible columns
	function get_column_count()
	{
		list ( $columns, $hidden ) = $this->get_column_info();
		$hidden = array_intersect( array_keys( $columns ), array_filter( $hidden ) );
		return count( $columns ) - count( $hidden );
	}


	// Extra controls to be displayed between bulk actions and pagination
	function extra_tablenav( $which ) {}

	// Generate the <tbody> part of the table
	function display_rows_or_placeholder()
	{
		if ( $this->has_items() ) {
			$this->display_rows();
		} else {
			list( $columns, $hidden ) = $this->get_column_info();
			echo '<tr class="no-items"><td class="colspanchange" colspan="'.$this->get_column_count().'">';
			$this->no_items();
			echo '</td></tr>';
		}
	}

	// Whether the table has items to display or not
	function has_items()
	{
		return ! empty( $this->items );
	}

	// Message to be displayed when there are no items
	function no_items()
	{
		_e( 'No items found.' );
	}

	// Generate the table rows
	function display_rows()
	{
		foreach ( $this->items as $item )
			$this->single_row( $item );
	}

	// Generates content for a single row of the table
	function single_row( $item )
	{
		static $row_class = '';
		$row_class = ( $row_class == '' ? ' class="alternate"' : '' );

		echo '<tr'.$row_class.'>';
		$this->single_row_columns( $item );
		echo '</tr>';
	}

	// Generates the columns for a single row of the table
	function single_row_columns( $item )
	{
		list( $columns, $hidden ) = $this->get_column_info();

		foreach ( $columns as $column_name => $column_display_name ) {
			$class = "class='$column_name column-$column_name'";

			$style = '';
			if ( in_array( $column_name, $hidden ) )
				$style = ' style="display:none;"';

			$attributes = "$class$style";

			if ( 'cb' == $column_name ) {

				echo '<th scope="row" class="check-column">';
				echo $this->column_cb( $item );
				echo '</th>';

			} else if ( method_exists( $this, 'column_' . $column_name ) ) {

				echo "<td $attributes>";
				echo call_user_func( array( $this, 'column_'.$column_name ), $item );
				echo "</td>";

			} else {

				echo "<td $attributes>";
				echo $this->column_default( $item, $column_name );
				echo "</td>";

			}
		}
	}

	// Display the search box.
	function search_box( $text, $input_id )
	{
		if ( empty( $_REQUEST['s'] ) && ! $this->has_items() )
			return;

		$input_id = $input_id.'-search-input';

		if ( ! empty( $_REQUEST['orderby'] ) )
			echo '<input type="hidden" name="orderby" value="'.esc_attr( $_REQUEST['orderby'] ).'" />';
		if ( ! empty( $_REQUEST['order'] ) )
			echo '<input type="hidden" name="order" value="'.esc_attr( $_REQUEST['order'] ).'" />';
		if ( ! empty( $_REQUEST['post_mime_type'] ) )
			echo '<input type="hidden" name="post_mime_type" value="'.esc_attr( $_REQUEST['post_mime_type'] ).'" />';
		if ( ! empty( $_REQUEST['detached'] ) )
			echo '<input type="hidden" name="detached" value="'.esc_attr( $_REQUEST['detached'] ).'" />';

		?><p class="search-box">
			<label class="screen-reader-text" for="<?php echo $input_id ?>"><?php echo $text; ?>:</label>
			<input type="search" id="<?php echo $input_id ?>" name="s" value="<?php self::search_query(); ?>" />
			<?php submit_button( $text, 'button', FALSE, FALSE, array( 'id' => 'search-submit' ) ); ?>
		</p> <?php
	}

	// Get an associative array ( id => link ) with the list of views available on this table.
	function get_views()
	{
		return array();
	}

	// Display the list of views available on this table.
	function views()
	{
		$views = $this->get_views();
		$views = apply_filters( 'views_'.$this->args['screen'], $views );

		if ( empty( $views ) )
			return;

		echo "<ul class='subsubsub'>\n";
		foreach ( $views as $class => $view ) {
			$views[ $class ] = "\t<li class='$class'>$view";
		}
		echo implode( " |</li>\n", $views ) . "</li>\n";
		echo "</ul>";
	}

	// Get an associative array ( option_name => option_title ) with the list of bulk actions available on this table.
	function get_bulk_actions()
	{
		return isset( $this->_bulk_actions ) ? $this->_bulk_actions : array();
	}

	// Display the bulk actions dropdown.
	function bulk_actions()
	{
		if ( is_null( $this->_actions ) ) {
			$no_new_actions = $this->_actions = $this->get_bulk_actions();
			// This filter can currently only be used to remove actions.
			$this->_actions = apply_filters( 'bulk_actions-'.$this->args['screen'], $this->_actions );
			$this->_actions = array_intersect_assoc( $this->_actions, $no_new_actions );
			$two = '';
		} else {
			$two = '2';
		}

		if ( empty( $this->_actions ) )
			return;

		echo "<select name='action$two'>\n";
		echo "<option value='-1' selected='selected'>" . __( 'Bulk Actions' ) . "</option>\n";

		foreach ( $this->_actions as $name => $title ) {
			$class = 'edit' == $name ? ' class="hide-if-no-js"' : '';

			echo "\t<option value='$name'$class>$title</option>\n";
		}

		echo "</select>\n";

		submit_button( __( 'Apply' ), 'action', FALSE, FALSE, array( 'id' => "doaction$two" ) );
		echo "\n";
	}

	// Get the current action selected from the bulk actions dropdown.
	function current_action()
	{
		if ( isset( $_REQUEST['action'] ) && -1 != $_REQUEST['action'] )
			return $_REQUEST['action'];

		if ( isset( $_REQUEST['action2'] ) && -1 != $_REQUEST['action2'] )
			return $_REQUEST['action2'];

		return FALSE;
	}

	// Generate row actions div
	function row_actions( $actions, $always_visible = FALSE )
	{
		$action_count = count( $actions );
		$i = 0;

		if ( !$action_count )
			return '';

		$out = '<div class="'.( $always_visible ? 'row-actions-visible' : 'row-actions' ).'">';
		foreach ( $actions as $action => $link ) {
			++$i;
			( $i == $action_count ) ? $sep = '' : $sep = ' | ';
			$out .= "<span class='$action'>$link$sep</span>";
		}
		$out .= '</div>';

		return $out;
	}

	// Display a view switcher
	function view_switcher( $current_mode )
	{
		$modes = array(
			'list'    => __( 'List View' ),
			'excerpt' => __( 'Excerpt View' )
		);

	?><input type="hidden" name="mode" value="<?php echo esc_attr( $current_mode ); ?>" />
		<div class="view-switch"><?php
			foreach ( $modes as $mode => $title ) {
				$class = ( $current_mode == $mode ) ? 'class="current"' : '';
				echo "<a href='" . esc_url( add_query_arg( 'mode', $mode, $_SERVER['REQUEST_URI'] ) ) . "' $class><img id='view-switch-$mode' src='" . esc_url( includes_url( 'images/blank.gif' ) ) . "' width='20' height='20' title='$title' alt='$title' /></a>\n";
			}
		?></div> <?php
	}

	// Get the current page number
	function get_pagenum()
	{
		$pagenum = isset( $_REQUEST['paged'] ) ? absint( $_REQUEST['paged'] ) : 0;

		if ( isset( $this->_pagination_args['total_pages'] ) && $pagenum > $this->_pagination_args['total_pages'] )
			$pagenum = $this->_pagination_args['total_pages'];

		return max( 1, $pagenum );
	}

	// Get number of items to display on a single page
	function get_items_per_page( $option, $default = 20 )
	{
		$per_page = (int) get_user_option( $option );
		if ( empty( $per_page ) || $per_page < 1 )
			$per_page = $default;

		return (int) apply_filters( $option, $per_page );
	}

	// Display the pagination.
	function pagination( $which )
	{
		if ( empty( $this->_pagination_args ) )
			return;

		$_pagination_args = $this->_pagination_args;
		extract( $_pagination_args, EXTR_SKIP );

		$output = '<span class="displaying-num">' . sprintf( _n( '1 item', '%s items', $total_items ), number_format_i18n( $total_items ) ) . '</span>';

		$current = $this->get_pagenum();

		$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );

		$current_url = remove_query_arg( array( 'hotkeys_highlight_last', 'hotkeys_highlight_first' ), $current_url );

		$page_links = array();

		$disable_first = $disable_last = '';
		if ( $current == 1 )
			$disable_first = ' disabled';
		if ( $current == $total_pages )
			$disable_last = ' disabled';

		$page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
			'first-page' . $disable_first,
			esc_attr__( 'Go to the first page' ),
			esc_url( remove_query_arg( 'paged', $current_url ) ),
			'&laquo;'
		);

		$page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
			'prev-page' . $disable_first,
			esc_attr__( 'Go to the previous page' ),
			esc_url( add_query_arg( 'paged', max( 1, $current-1 ), $current_url ) ),
			'&lsaquo;'
		);

		if ( 'bottom' == $which )
			$html_current_page = $current;
		else
			$html_current_page = sprintf( "<input class='current-page' title='%s' type='text' name='paged' value='%s' size='%d' />",
				esc_attr__( 'Current page' ),
				$current,
				strlen( $total_pages )
			);

		$html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );
		$page_links[] = '<span class="paging-input">' . sprintf( _x( '%1$s of %2$s', 'paging' ), $html_current_page, $html_total_pages ) . '</span>';

		$page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
			'next-page' . $disable_last,
			esc_attr__( 'Go to the next page' ),
			esc_url( add_query_arg( 'paged', min( $total_pages, $current+1 ), $current_url ) ),
			'&rsaquo;'
		);

		$page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
			'last-page' . $disable_last,
			esc_attr__( 'Go to the last page' ),
			esc_url( add_query_arg( 'paged', $total_pages, $current_url ) ),
			'&raquo;'
		);

		$pagination_links_class = 'pagination-links';
		if ( ! empty( $infinite_scroll ) )
			$pagination_links_class = ' hide-if-js';
		$output .= "\n<span class='$pagination_links_class'>" . join( "\n", $page_links ) . '</span>';

		if ( $total_pages )
			$page_class = $total_pages < 2 ? ' one-page' : '';
		else
			$page_class = ' no-pages';

		$this->_pagination = "<div class='tablenav-pages{$page_class}'>$output</div>";

		echo $this->_pagination;
	}

	// Handle an incoming ajax request (called from admin-ajax.php)
	function ajax_response()
	{
		$this->prepare_items();

		extract( $this->_args );
		extract( $this->_pagination_args, EXTR_SKIP );

		ob_start();
		if ( ! empty( $_REQUEST['no_placeholder'] ) )
			$this->display_rows();
		else
			$this->display_rows_or_placeholder();

		$rows = ob_get_clean();

		$response = array( 'rows' => $rows );

		if ( isset( $total_items ) )
			$response['total_items_i18n'] = sprintf( _n( '1 item', '%s items', $total_items ), number_format_i18n( $total_items ) );

		if ( isset( $total_pages ) ) {
			$response['total_pages'] = $total_pages;
			$response['total_pages_i18n'] = number_format_i18n( $total_pages );
		}

		die( wp_json_encode( $response ) );
	}

	// Send required variables to JavaScript land
	function _js_vars()
	{
		$args = array(
			'class'  => get_class( $this ),
			'screen' => array(
				'id'   => $this->args['screen'],
				//'base' => $this->screen->base,
			)
		);

		printf( "<script type='text/javascript'>list_args = %s;</script>\n", wp_json_encode( $args ) );
	}

	// mine
	// for ajax use
	function get_single_row( $item )
	{
		ob_start();
		$this->single_row( $item );
		return ob_get_clean();
	}
} }
