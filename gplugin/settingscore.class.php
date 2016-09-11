<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

if ( ! class_exists( 'gPluginSettingsCore' ) ) { class gPluginSettingsCore extends gPluginClassCore
{

	public function setup_globals( $constants = array(), $args = array() )
	{
		$this->current_blog = get_current_blog_id();
		$this->option_group = isset( $args['option_group'] ) ? $args['option_group'] : 'gpluginsettings';
		$this->page         = isset( $args['page'] ) && $args['page'] ? $args['page'] : 'general';

		$this->constants = array_merge( array(
			'plugin_dir'    => GPLUGIN_DIR,
			'plugin_url'    => GPLUGIN_URL,
			'class_filters' => 'gPluginFiltersCore',
		), $constants );

		$this->args = array_merge( array(
			'plugin_class'      => FALSE,
			'plugin_args'       => array(),
			'settings_sanitize' => NULL, // NULL for default, FALSE for disable
			'field_callback'    => FALSE,
			'site_options'      => FALSE, // site wide or blog option storing
			'register_hook'     => FALSE, // hook that runs on settings page load
		), $args );

		$this->options = self::get_options();
		$this->enabled = isset( $this->options['enabled'] ) ? $this->options['enabled'] : FALSE;

		// for something like old gMember Restricted class
		// when we have to initiate a plugin module if enabled option
		if ( $this->enabled && class_exists( $this->args['plugin_class'] ) )
			gPluginFactory::get( $this->args['plugin_class'], $this->constants, $this->args['plugin_args'] );
	}

	public function setup_actions()
	{
		add_action( 'init', array( $this, 'init_late' ), 999 );

		if ( $this->args['register_hook'] )
			add_action( $this->args['register_hook'], array( $this, 'register_hook' ) );
		else
			add_action( 'admin_init', array( $this, 'register_hook' ) );
	}

	public function init_late()
	{
		// giving the plugin a chance to manipulate pre settings args
		$key = 'gplugin_settings_args_'.strtolower( get_class( $this ) );

		if ( has_filter( $key ) ) {
			$this->args = apply_filters( $key, $this->args );
			if ( isset( $this->args['page'] ) )
				$this->page = $this->args['page'];

			// recall for option defaults to avoid undefinded index notices
			$this->options = self::get_options();
		}
	}

	public function register_hook()
	{
		if ( is_array( $this->page ) ) {
			foreach ( $this->page as $page_name => $sections )
				$this->register_sections( $page_name, $sections );
		} else {
			if ( ! count( $this->args['sections'] ) )
				return;

			$this->register_sections( $this->page, $this->args['sections'] );
		}

		if ( $this->args['settings_sanitize'] && is_callable( $this->args['settings_sanitize'] ) )
			add_filter( 'sanitize_option_'.$this->option_group, $this->args['settings_sanitize'], 10, 2 );

		else if ( FALSE !== $this->args['settings_sanitize'] )
			add_filter( 'sanitize_option_'.$this->option_group, array( $this, 'settings_sanitize' ), 10, 2 );
	}

	public function register_sections( $page_name, $sections )
	{
		register_setting( $page_name, $this->option_group );  // we added the sanitization manually

		$field_callback = $this->args['field_callback'] ? $this->args['field_callback'] : array( $this, 'do_settings_field' );

		foreach ( $sections as $section_name => $section_args ) {

			if ( 'gplugin' == $section_name )
				add_settings_section( 'gplugin', '&nbsp;', FALSE, $page_name );

			else if ( FALSE !== $section_args['title'] )
				add_settings_section( $section_name, $section_args['title'], $section_args['callback'],	$page_name );

			foreach ( $section_args['fields'] as $field_name => $field_args ) {
				add_settings_field( $this->option_group.'_'.$field_name,
					$field_args['title'],
					( isset( $field_args['cb'] ) && $field_args['cb'] ? $field_args['cb'] : $field_callback ),
					$page_name,
					$section_name,
					array_merge( $field_args, array(
						'field'        => $field_name,
						'label_for'    => $this->option_group.'['.$field_name.']',
						'option_group' => $this->option_group,
					) )
				);
			}
		}
	}

	public function do_settings_field( $atts )
	{
		$args = self::atts( array(
			'title'        => '',
			'label_for'    => '',
			'type'         => 'enabled',
			'field'        => FALSE,
			'values'       => array(),
			'exclude'      => '',
			'none_title'   => NULL, // select option none title
			'none_value'   => NULL, // select option none value
			'filter'       => FALSE, // will use via sanitize
			'dir'          => FALSE,
			'default'      => '',
			'description'  => isset( $atts['desc'] ) ? $atts['desc'] : '',
			'before'       => '', // html to print before field
			'after'        => '', // html to print after field
			'field_class'  => '', // formally just class!
			'class'        => '', // now used on wrapper
			'option_group' => $this->option_group,
			'disabled'     => FALSE,
			'name_attr'    => FALSE, // override
			'id_attr'      => FALSE, // override
			'placeholder'  => FALSE,
			'constant'     => FALSE, // override value if constant defined / also disabling
			'rekey'        => FALSE, // use value as key // FIXME: DEPRECATED: use gPluginUtils::reKey()
		), $atts );

		if ( $args['rekey'] )
			self::__dep( 'gPluginUtils::sameKey()' );

		if ( ! $args['field'] )
			return;

		$html    = '';
		$value   = isset( $this->options[$args['field']] ) ? $this->options[$args['field']] : $args['default'];
		$name    = $args['name_attr'] ? $args['name_attr'] : $args['option_group'].'['.esc_attr( $args['field'] ).']';
		$id      = $args['id_attr'] ? $args['id_attr'] : $args['option_group'].'-'.esc_attr( $args['field'] );
		$exclude = $args['exclude'] && ! is_array( $args['exclude'] ) ? array_filter( explode( ',', $args['exclude'] ) ) : array();

		if ( $args['constant'] && defined( $args['constant'] ) ) {
			$value = constant( $args['constant'] );

			$args['disabled'] = TRUE;
			$args['after']    = '<code>'.$args['constant'].'</code>';
		}

		if ( $args['before'] )
			echo $args['before'].'&nbsp;';

		switch ( $args['type'] ) {

			case 'hidden' :

				echo gPluginHTML::tag( 'input', array(
					'type'  => 'hidden',
					'name'  => $name,
					'id'    => $id,
					'value' => $value,
				) );

				$args['description'] = FALSE;

			break;
			case 'enabled' :

				$html = gPluginHTML::tag( 'option', array(
					'value'    => '0',
					'selected' => '0' == $value,
				), ( isset( $args['values'][0] ) ? $args['values'][0] : esc_html__( 'Disabled' ) ) );

				$html .= gPluginHTML::tag( 'option', array(
					'value'    => '1',
					'selected' => '1' == $value,
				), ( isset( $args['values'][1] ) ? $args['values'][1] : esc_html__( 'Enabled' ) ) );

				echo gPluginHTML::tag( 'select', array(
					'class' => $args['field_class'],
					'name'  => $name,
					'id'    => $id,
					'dir'   => $args['dir'],
				), $html );

			break;
			case 'text' :

				if ( ! $args['field_class'] )
					$args['field_class'] = 'regular-text';

				echo gPluginHTML::tag( 'input', array(
					'type'        => 'text',
					'class'       => $args['field_class'],
					'name'        => $name,
					'id'          => $id,
					'value'       => $value,
					'dir'         => $args['dir'],
					'disabled'    => $args['disabled'],
					'placeholder' => $args['placeholder'],
				) );

			break;
			case 'number' :

				if ( ! $args['field_class'] )
					$args['field_class'] = 'small-text';

				if ( ! $args['dir'] )
					$args['dir'] = 'ltr';

				echo gPluginHTML::tag( 'input', array(
					'type'        => 'number',
					'class'       => $args['field_class'],
					'name'        => $name,
					'id'          => $id,
					'value'       => $value,
					'step'        => '1', // FIXME: get from args
					'min'         => '0', // FIXME: get from args
					'dir'         => $args['dir'],
					'disabled'    => $args['disabled'],
					'placeholder' => $args['placeholder'],
				) );

			break;
			case 'checkbox' :

				if ( count( $args['values'] ) ) {

					if ( ! is_null( $args['none_title'] ) ) {

						$html = gPluginHTML::tag( 'input', array(
							'type'     => 'checkbox',
							'class'    => $args['field_class'],
							'name'     => $name.( is_null( $args['none_value'] ) ? '' : '-'.$args['none_value'] ),
							'id'       => $id.( is_null( $args['none_value'] ) ? '' : '-'.$args['none_value'] ),
							'value'    => is_null( $args['none_value'] ) ? '1' : $args['none_value'],
							'checked'  => in_array( $args['none_value'], ( array ) $value ),
							'disabled' => $args['disabled'],
							'dir'      => $args['dir'],
						) );

						echo '<p>'.gPluginHTML::tag( 'label', array(
							'for' => $id.( is_null( $args['none_value'] ) ? '' : '-'.$args['none_value'] ),
						), $html.'&nbsp;'.esc_html( $args['none_title'] ) ).'</p>';
					}

					foreach ( $args['values'] as $value_name => $value_title ) {

						if ( in_array( $value_name, $exclude ) )
							continue;

						$html = gPluginHTML::tag( 'input', array(
							'type'     => 'checkbox',
							'class'    => $args['field_class'],
							'name'     => $name.'['.$value_name.']',
							'id'       => $id.'-'.$value_name,
							'value'    => '1',
							'checked'  => in_array( $value_name, ( array ) $value ),
							'disabled' => $args['disabled'],
							'dir'      => $args['dir'],
						) );

						echo '<p>'.gPluginHTML::tag( 'label', array(
							'for' => $id.'-'.$value_name,
						), $html.'&nbsp;'.esc_html( $value_title ) ).'</p>';
					}

				} else {

					$html = gPluginHTML::tag( 'input', array(
						'type'     => 'checkbox',
						'class'    => $args['field_class'],
						'name'     => $name,
						'id'       => $id,
						'value'    => '1',
						'checked'  => $value,
						'disabled' => $args['disabled'],
						'dir'      => $args['dir'],
					) );

					echo '<p>'.gPluginHTML::tag( 'label', array(
						'for' => $id,
					), $html.'&nbsp;'.$args['description'] ).'</p>';

					$args['description'] = FALSE;
				}

			break;
			case 'radio' :

				if ( count( $args['values'] ) ) {

					if ( ! is_null( $args['none_title'] ) ) {

						$html = gPluginHTML::tag( 'input', array(
							'type'     => 'radio',
							'class'    => $args['field_class'],
							'name'     => $name,
							'id'       => $id.( is_null( $args['none_value'] ) ? '' : '-'.$args['none_value'] ),
							'value'    => is_null( $args['none_value'] ) ? FALSE : $args['none_value'],
							'checked'  => in_array( $args['none_value'], ( array ) $value ),
							'disabled' => $args['disabled'],
							'dir'      => $args['dir'],
						) );

						echo '<p>'.gPluginHTML::tag( 'label', array(
							'for' => $id.( is_null( $args['none_value'] ) ? '' : '-'.$args['none_value'] ),
						), $html.'&nbsp;'.esc_html( $args['none_title'] ) ).'</p>';
					}

					foreach ( $args['values'] as $value_name => $value_title ) {

						if ( in_array( $value_name, $exclude ) )
							continue;

						$html = gPluginHTML::tag( 'input', array(
							'type'     => 'radio',
							'class'    => $args['field_class'],
							'name'     => $name,
							'id'       => $id.'-'.$value_name,
							'value'    => $value_name,
							'checked'  => in_array( $value_name, ( array ) $value ),
							'disabled' => $args['disabled'],
							'dir'      => $args['dir'],
						) );

						echo '<p>'.gPluginHTML::tag( 'label', array(
							'for' => $id.'-'.$value_name,
						), $html.'&nbsp;'.esc_html( $value_title ) ).'</p>';
					}
				}

			break;
			case 'select' :

				if ( FALSE !== $args['values'] ) { // alow hiding

					if ( ! is_null( $args['none_title'] ) ) {

						$html .= gPluginHTML::tag( 'option', array(
							'value'    => is_null( $args['none_value'] ) ? FALSE : $args['none_value'],
							'selected' => $value == $args['none_value'],
						), esc_html( $args['none_title'] ) );
					}

					foreach ( $args['values'] as $value_name => $value_title ) {

						if ( in_array( ( $args['rekey'] ? $value_title : $value_name ), $exclude ) )
							continue;

						$html .= gPluginHTML::tag( 'option', array(
							'value'    => $args['rekey'] ? $value_title : $value_name,
							'selected' => $value == $value_name,
						), esc_html( $value_title ) );
					}

					echo gPluginHTML::tag( 'select', array(
						'name'     => $name,
						'id'       => $id,
						'class'    => $args['field_class'],
						'disabled' => $args['disabled'],
						'dir'      => $args['dir'],
					), $html );
				}

			break;
			case 'textarea' :

				if ( ! $args['field_class'] )
					$args['field_class'] = 'large-text';

				echo gPluginHTML::tag( 'textarea', array(
					'name'        => $name,
					'id'          => $id,
					'rows'        => 5,
					'cols'        => 45,
					'class'       => $args['field_class'],
					'disabled'    => $args['disabled'],
					'placeholder' => $args['placeholder'],
				// ), esc_textarea( $value ) );
				), $value );

			break;
			case 'button' :

				echo get_submit_button(
					$value,
					( empty( $args['field_class'] ) ? 'secondary' : $args['field_class'] ),
					$args['field'], // $id,
					FALSE,
					$args['values']
				);

			break;
			case 'file' :

				echo gPluginHTML::tag( 'input', array(
					'type'     => 'file',
					'name'     => $id, //$name,
					'id'       => $id,
					// 'value' => $value,
					'class'    => $args['field_class'],
					'disabled' => $args['disabled'],
					'dir'      => $args['dir'],
				) );

			break;
			case 'posttypes' :

				foreach ( gPluginWPHelper::getPostTypes() as $value_name => $value_title ) {

					if ( in_array( $value_name, $exclude ) )
						continue;

					$html = gPluginHTML::tag( 'input', array(
						'type'     => 'checkbox',
						'class'    => $args['field_class'],
						'name'     => $name.'['.$value_name.']',
						'id'       => $id.'-'.$value_name,
						'value'    => '1',
						'checked'  => in_array( $value_name, ( array ) $value ),
						'disabled' => $args['disabled'],
						'dir'      => $args['dir'],
					) );

					echo '<p>'.gPluginHTML::tag( 'label', array(
						'for' => $id.'-'.$value_name,
					), $html.'&nbsp;'.esc_html( $value_title ) ).'</p>';
				}

			break;
			case 'custom' :

				if ( ! is_array( $args['values'] ) )
					echo $args['values'];
				else
					echo $value;

			break;
			case 'debug' :

				gPluginUtils::dump( $this->options );

			break;
			default :

				echo 'Error: setting type not defind';
		}

		if ( $args['after'] )
			echo '&nbsp;'.$args['after'];

		if ( $args['description'] && FALSE !== $args['values'] )
			echo gPluginHTML::tag( 'p', array(
				'class' => 'description',
			), $args['description'] );
	}

	public function get( $field, $default = FALSE )
	{
		return isset( $this->options[$field] ) ? $this->options[$field] : $default;
	}

	// FIXME: DEPRECATED
	public function get_option( $field, $default = FALSE )
	{
		self::__dep( 'gPluginSettingsCore::get()');
		return $this->get( $field, $default );
	}

	public function get_options()
	{
		if ( $this->args['site_options'] )
			$options = get_site_option( $this->option_group, array() );
		else
			$options = get_option( $this->option_group, array() );

		// return gPluginUtils::recursiveParseArgs( $options, self::get_option_defaults() );
		return array_merge( self::get_option_defaults(), $options );
	}

	public function update_options( $options = NULL )
	{
		if ( is_null( $options ) )
			$options = $this->options;

		if ( $this->args['site_options'] )
			return update_site_option( $this->option_group, $options );
		else
			return update_option( $this->option_group, $options, TRUE );
	}

	public function get_option_defaults()
	{
		$defaults = array();

		if ( is_array( $this->page ) ) {

			foreach ( $this->page as $page_name => $sections )
				foreach ( $sections as $section_name => $section_args )
					foreach ( $section_args['fields'] as $field_name => $field_args )
						$defaults[$field_name] = $this->get_field_defaults( $field_name, $field_args );

		} else {

			foreach ( $this->args['sections'] as $section_name => $section_args )
				foreach ( $section_args['fields'] as $field_name => $field_args )
					$defaults[$field_name] = $this->get_field_defaults( $field_name, $field_args );
		}

		return (array) apply_filters( $this->option_group.'_option_defaults', $defaults );
	}

	protected function get_field_defaults( $field_name, $field_args, $default = '' )
	{
		if ( isset( $field_args['constant'] )
			&& $field_args['constant']
			&& defined( $field_args['constant'] ) )
				return constant( $field_args['constant'] );

		if ( isset( $field_args['default'] ) )
			return $field_args['default'];

		return $default;
	}

	public function settings_sanitize( $input )
	{
		$output = array();

		if ( is_array( $this->page ) ) {
			foreach ( $this->page as $page_name => $sections ) {
				$output = $this->settings_sanitize_section( $sections, $input, $output, $this->options );
			}
		} else {
			$output = $this->settings_sanitize_section( $this->args['sections'], $input, $output, $this->options );
		}

		return $output;
	}

	public function settings_sanitize_section( $sections, $input, $output, $stored = array() )
	{
		foreach ( $sections as $section => $section_args ) {
			foreach ( $section_args['fields'] as $field => $field_args ) {

				if ( isset( $field_args['constant'] )
					&& $field_args['constant']
					&& defined( $field_args['constant'] ) ) {

						// do nothing

				// new value
				} else if ( isset( $input[$field] ) ) {

					// callback
					if ( isset( $field_args['filter'] ) && $field_args['filter'] && is_callable( $field_args['filter'] ) )
						$output[$field] = call_user_func_array( $field_args['filter'], array( $input[$field] ) );

					// disabled select
					else if ( isset( $field_args['values'] ) && FALSE === $field_args['values'] )
						$output[$field] = $field_args['default'];

					// filled multiple checkboxes
					else if ( is_array( $input[$field] ) )
						$output[$field] = gPluginUtils::getKeys( $input[$field] );

					// default
					else
						$output[$field] = $input[$field];

				// empty multiple checkboxes
				} else if ( isset( $field_args['values'] ) && FALSE !== $field_args['values'] ) {
					$output[$field] = array();

				// custom multiple checkboxes
				} else if ( in_array( $field_args['type'], array( 'posttypes' ) ) ) {
					$output[$field] = array();

				// previously stored value
				} else if ( isset( $stored[$field] ) ) {
					$output[$field] = $stored[$field];

				// default value
				} else {
					$output[$field] = $field_args['default'];
				}
			}
		}

		return $output;
	}
} }

/***
	SAMPLE ARGUMENTS :

	$args = array(
		'plugin_class'      => FALSE,
		'option_group'      => 'gpluginsettings',
		'settings_sanitize' => FALSE, // override sanitization
		'field_callback'    => FALSE, // oberride field print
		'page'              => 'general',
		'sections'          => array(
			'default' => array(
			//'date' => array(
				'title' => FALSE,
				//'title' => 'Section Title',
				'callback' => array( __CLASS__, 'section_callback' ), // '__return_false'
				'fields' => array(
					'enabled' => array(
						'title'   => 'gPlugin',
						'desc'    => '',
						'type'    => 'enabled',
						'dir'     => 'ltr',
						'default' => 0,
						'filter'  => FALSE, // 'esc_attr'
					),
				),
			),
		),
	);
*/

// ALSO : https://github.com/gilbitron/WordPress-Settings-Framework
