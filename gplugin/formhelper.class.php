<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

if ( ! class_exists( 'gPluginFormHelper' ) ) { class gPluginFormHelper extends gPluginClassCore
{

	public static function hideIf( $print = TRUE )
	{
		if ( $print )
			echo ' style="display:none;"';
	}

	// FIXME: DEPRECATED
	public static function wpSettingsHeaderNav( $settings_uri = '', $active = '', $sub_pages = array(), $class_prefix = 'nav-tab-', $tag = 'h3' )
	{
		self::__dep( 'gPluginFormHelper::headerNav()' );
		self::headerNav( $settings_uri, $active, $sub_pages, $class_prefix, $tag );
	}

	public static function headerNav( $uri = '', $active = '', $subs = array(), $prefix = 'nav-tab-', $tag = 'h3' )
	{
		if ( ! count( $subs ) )
			return;

		$html = '';

		foreach ( $subs as $slug => $page )
			$html .= gPluginHTML::tag( 'a', array(
				'class' => 'nav-tab '.$prefix.$slug.( $slug == $active ? ' nav-tab-active' : '' ),
				'href'  => add_query_arg( 'sub', $slug, $uri ),
			), $page );

		echo gPluginHTML::tag( $tag, array(
			'class' => 'nav-tab-wrapper',
		), $html );
	}

	public static function headerTabs( $tabs, $active = 'manual', $prefix = 'nav-tab-', $tag = 'h3' )
	{
		if ( ! count( $tabs ) )
			return;

		$html = '';

		foreach ( $tabs as $tab => $title )
			$html .= gPluginHTML::tag( 'a', array(
				'class'    => 'nav-tab '.$prefix.$tab.( $tab == $active ? ' nav-tab-active' : '' ),
				'href'     => '#',
				'data-tab' => $tab,
				'rel'      => $tab, // back comp
			), $title );

		echo gPluginHTML::tag( $tag, array(
			'class' => 'nav-tab-wrapper',
		), $html );
	}

	// FIXME: DEPRECATED
	public static function wpNavTabs( $tabs, $active = 'manual', $class_prefix = 'nav-tab-', $tag = 'h3' )
	{
		self::__dep( 'gPluginFormHelper::headerTabs()' );

		echo '<'.$tag.' class="nav-tab-wrapper">';
		foreach ( $tabs as $tab => $title )
			echo '<a href="#" class="nav-tab '.$class_prefix.$tab.( $active == $tab ? ' nav-tab-active' : '' ).'" rel="'.$tab.'" >'.$title.'</a>';
		echo '</'.$tag.'>';
	}

	// FIXME: DROP THIS
	// DEPRICATED: use `gPluginHTML::linkStyleSheet()`
	public static function linkStyleSheet( $url, $version = NULL, $media = 'all' )
	{
		self::__dep( 'gPluginHTML::linkStyleSheet()' );
		gPluginHTML::linkStyleSheet( $url, $version, $media );
	}

	// FIXME: DROP THIS
	// DEPRICATED: use `gPluginHTML::sanitizeClass()`
	public static function sanitizeHTMLClass( $class )
	{
		self::__dep( 'gPluginHTML::sanitizeClass()' );

		// strip out any % encoded octets
		$sanitized = preg_replace( '|%[a-fA-F0-9][a-fA-F0-9]|', '', $class );

		// limit to A-Z,a-z,0-9,_,-
		$sanitized = preg_replace( '/[^A-Za-z0-9_-]/', '', $sanitized );

		return $sanitized;
	}

	// FIXME: DROP THIS
	// DEPRICATED: use `gPluginHTML::sanitizeTag()`
	public static function sanitizeHTMLTag( $tag )
	{
		self::__dep( 'gPluginHTML::sanitizeTag()' );

		return strtolower( preg_replace('/[^a-zA-Z0-9_:]/', '', $tag ) );
	}

	private static function _tag_open( $tag, $atts, $content = TRUE )
	{
		$html = '<'.$tag;

		foreach ( $atts as $key => $att ) {

			$sanitized = FALSE;

			if ( is_array( $att ) && count( $att ) ) {

				if ( 'data' == $key ) {

					foreach ( $att as $data_key => $data_val ) {

						if ( is_array( $data_val ) )
							$html .= ' data-'.$data_key.'=\''.wp_json_encode( $data_val ).'\'';

						else if ( FALSE === $data_val )
							continue;

						else
							$html .= ' data-'.$data_key.'="'.esc_attr( $data_val ).'"';
					}

					continue;

				} else if ( 'class' == $key ) {
					$att = implode( ' ', array_unique( array_filter( $att, array( __CLASS__, 'sanitizeHTMLClass' ) ) ) );

				} else {
					$att = implode( ' ', array_unique( array_filter( $att, 'trim' ) ) );
				}

				$sanitized = TRUE;
			}

			if ( 'selected' == $key )
				$att = ( $att ? 'selected' : FALSE );

			if ( 'checked' == $key )
				$att = ( $att ? 'checked' : FALSE );

			if ( 'readonly' == $key )
				$att = ( $att ? 'readonly' : FALSE );

			if ( 'disabled' == $key )
				$att = ( $att ? 'disabled' : FALSE );

			if ( FALSE === $att )
				continue;

			if ( 'class' == $key && ! $sanitized )
				$att = implode( ' ', array_unique( array_filter( explode( ' ', $att ), array( __CLASS__, 'sanitizeHTMLClass' ) ) ) );

			else if ( 'class' == $key )
				$att = $att;

			else if ( 'href' == $key && '#' != $att )
				$att = esc_url( $att );

			else if ( 'src' == $key )
				$att = esc_url( $att );

			else
				$att = esc_attr( $att );

			$html .= ' '.$key.'="'.trim( $att ).'"';
		}

		if ( FALSE === $content )
			return $html.' />';

		return $html.'>';
	}

	// FIXME: DROP THIS
	// DEPRICATED: use `gPluginHTML::tag()`
	public static function html( $tag, $atts = array(), $content = FALSE, $sep = '' )
	{
		self::__dep( 'gPluginHTML::tag()' );

		$tag = self::sanitizeHTMLTag( $tag );

		if ( is_array( $atts ) )
			$html = self::_tag_open( $tag, $atts, $content );
		else
			return '<'.$tag.'>'.$atts.'</'.$tag.'>'.$sep;

		if ( FALSE === $content )
			return $html.$sep;

		if ( is_null( $content ) )
			return $html.'</'.$tag.'>'.$sep;

		return $html.$content.'</'.$tag.'>'.$sep;
	}

	public static function genDropdown( $list, $atts = array(), $object = FALSE )
	{
		$args = self::atts( array(
			'id'         => '',
			'name'       => '',
			'none_title' => NULL, // select option none title
			'none_value' => 0, // select option none value
			'class'      => FALSE,
			'selected'   => 0,
			'disabled'   => FALSE,
			'dir'        => FALSE,
			'property'   => FALSE,
			'exclude'    => array(),
		), $atts );

		$html = '';

		if ( FALSE !== $list ) { // alow hiding

			if ( ! is_null( $args['none_title'] ) ) {

				$html .= gPluginHTML::tag( 'option', array(
					'value'    => $args['none_value'],
					'selected' => $args['selected'] == $args['none_value'],
				), esc_html( $args['none_title'] ) );
			}

			foreach ( $list as $key => $value ) {

				if ( in_array( $key, $args['exclude'] ) )
					continue;

				if ( $args['property'] )
					$title = $object ? $value->{$args['property']} : $value[$args['property']];
				else
					$title = $value;

				$html .= gPluginHTML::tag( 'option', array(
					'value'    => $key,
					'selected' => $args['selected'] == $key,
				), esc_html( $title ) );
			}

			$html = gPluginHTML::tag( 'select', array(
				'name'     => $args['name'],
				'id'       => $args['id'],
				'class'    => $args['class'],
				'disabled' => $args['disabled'],
				'dir'      => $args['dir'],
			), $html );
		}

		return $html;
	}

	// FIXME: DEPRECATED
	public static function select( $list, $atts = array(), $selected = 0, $prop = FALSE, $none = FALSE, $none_val = 0 )
	{
		self::__dep( 'gPluginFormHelper::genDropdown()' );

		$html = self::_tag_open( 'select', $atts, TRUE );

		if ( $none )
			$html .= '<option value="'.$none_val.'" '.selected( $selected, $none_val, FALSE ).'>'.esc_html( $none ).'</option>';

		foreach ( $list as $key => $item )
			$html .= '<option value="'.$key.'" '.selected( $selected, $key, FALSE ).'>'
				.esc_html( ( $prop ? $item[$prop] : $item ) ).'</option>';

		return $html.'</select>';
	}

	// FIXME: DEPRECATED
	function getFieldTitle( $column )
	{
		self::__dep();

		if ( isset( $field['type'] ) ) {
			if ( 'delete' == $field['type'] )
				return '<span class="field-delete-all" title="'.$field['title'].'"></span>';
		}
		return ( isset( $field['title'] ) ? $field['title'] : '' );
	}

	// FIXME: DEPRECATED
	public static function reKey( $list, $key )
	{
		self::__dep( 'gPluginUtils::reKey()' );
		return gPluginUtils::reKey( $list, $key );
	}

	public static function data_dropdown( $list, $name, $prop = FALSE, $selected = 0, $none = FALSE, $none_val = 0 )
	{
		$data = array(
			'select-id'   => $name,
			'select-name' => $name,
			'select-atts' => '',
		);

		$options = array();

		if ( $none )
			$options[] = array(
				'option-val'  => $none_val,
				'option-atts' => selected( $selected, $none_val, FALSE ),
				'option-html' => esc_html( $none )
			);

		foreach ( $list as $key => $item )
			$options[] = array(
				'option-val'  => $key,
				'option-atts' => selected( $selected, $key, FALSE ),
				'option-html' => esc_html( ( $prop ? $item->$prop : $item ) )
			);

		$data['options'] = $options;
		return $data;
	}

	public static function dropdown( $list, $name, $prop = FALSE, $selected = 0, $none = FALSE, $none_val = 0, $obj = FALSE )
	{
		self::__dep( 'gPluginFormHelper::genDropdown()' );

		$html = '';

		if ( $none ) {
			$html .= gPluginHTML::tag( 'option', array(
				'value'    => $none_val,
				'selected' => $selected == $none_val,
			), esc_html( $none ) );
		}

		foreach ( $list as $key => $item ) {
			$html .= gPluginHTML::tag( 'option', array(
				'value'    => $key,
				'selected' => $selected == $key,
			), esc_html( ( $prop ? ( $obj ? $item->{$prop} : $item[$prop] ) : $item ) ) );
		}

		return gPluginHTML::tag( 'select', array(
			'id'   => $name,
			'name' => $name,
		), $html );
	}

	// FIXME: DEPRECATED
	public static function dropdown_e( $list, $name, $prop = FALSE, $selected = 0, $none = FALSE, $none_val = 0 )
	{
		self::__dep( 'gPluginFormHelper::genDropdown()' );

		?><select name="<?php echo $name; ?>" id="<?php echo $name; ?>"><?php
		if ( $none )
			echo '<option value="'.$none_val.'" '.selected( $selected, $none_val, FALSE ).'>'.esc_html( $none ).'</option>';
		foreach ( $list as $key => $item )
			echo '<option value="'.$key.'" '.selected( $selected, $key, FALSE ).'>'.esc_html( ( $prop ? $item->$prop : $item ) ).'</option>';
		?></select><?php
	}
} }
