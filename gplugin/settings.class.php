<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

if ( ! class_exists( 'gPluginSettings' ) ) { class gPluginSettings extends gPluginClassCore
{

	// using caps instead of roles
	public static function getUserCapList( $cap = NULL, $none_title = NULL, $none_value = NULL )
	{
		$caps = array(
			'edit_theme_options'   => _x( 'Administrator', 'User role' ),
			'edit_others_posts'    => _x( 'Editor', 'User role' ),
			'edit_published_posts' => _x( 'Author', 'User role' ),
			'edit_posts'           => _x( 'Contributor', 'User role' ),
			'read'                 => _x( 'Subscriber', 'User role' ),
		);

		if ( is_multisite() ) {
			$caps = array(
				'manage_network' => __( 'Super Admin' ),
			) + $caps + array(
				'logged_in_user' => __( 'Network User' ),
			);
		}

		if ( is_null( $none_title ) )
			$none_title = __( '&mdash; Select &mdash;' );

		if ( is_null( $none_value ) )
			$none_value = 'none';

		if ( $none_title )
			$caps[$none_value] = $none_title;

		if ( is_null( $cap ) )
			return $caps;
		else
			return $caps[$cap];
	}

	public static function priorityOptions( $format = TRUE )
	{
		return
			array_reverse( gPluginUtils::range( -100, -1000, 100, $format ), TRUE ) +
			array_reverse( gPluginUtils::range( -10, -100, 10, $format ), TRUE ) +
			gPluginUtils::range( 0, 100, 10, $format ) +
			gPluginUtils::range( 100, 1000, 100, $format );
	}

	public static function fieldType( $atts = array(), &$scripts )
	{
		$args = self::atts( array(
			'title'        => '&nbsp;',
			'label_for'    => '',
			'type'         => 'enabled',
			'field'        => FALSE,
			'values'       => array(),
			'exclude'      => '',
			'none_title'   => NULL, // select option none title
			'none_value'   => NULL, // select option none value
			'filter'       => FALSE, // will use via sanitize
			'callback'     => FALSE, // callable for `callback` type
			'dir'          => FALSE,
			'disabled'     => FALSE,
			'readonly'     => FALSE,
			'default'      => '',
			'defaults'     => array(), // default value to ignore && override the saved
			'description'  => isset( $atts['desc'] ) ? $atts['desc'] : '',
			'before'       => '', // html to print before field
			'after'        => '', // html to print after field
			'field_class'  => '', // formally just class!
			'class'        => '', // now used on wrapper
			'option_group' => 'general',
			'option_base'  => '', // NOT USED on gPlugin
			'options'      => array(), // saved options
			'id_name_cb'   => FALSE, // id/name generator callback
			'id_attr'      => FALSE, // override
			'name_attr'    => FALSE, // override
			'step_attr'    => '1', // for number type
			'min_attr'     => '0', // for number type
			'rows_attr'    => '5', // for textarea type
			'cols_attr'    => '45', // for textarea type
			'placeholder'  => FALSE,
			'constant'     => FALSE, // override value if constant defined & disabling
			'data'         => array(), // data attr
			'extra'        => array(), // extra args to pass to deeper generator
			'wrap'         => FALSE,
			'cap'          => NULL,

			'string_disabled' => __( 'Disabled' ),
			'string_enabled'  => __( 'Enabled' ),
			'string_select'   => __( '&mdash; Select &mdash;' ),
			'string_noaccess' => __( 'You do not have access to change this option.' ),
		), $atts );

		if ( $args['wrap'] ) {
			if ( ! empty( $args['label_for'] ) )
				echo '<tr class="'.$args['class'].'"><th scope="row"><label for="'.esc_attr( $args['label_for'] ).'">'.$args['title'].'</label></th><td>';
			else
				echo '<tr class="'.$args['class'].'"><th scope="row">'.$args['title'].'</th><td>';
		}

		if ( ! $args['field'] )
			return;

		$html  = '';
		$value = $args['default'];

		if ( is_array( $args['exclude'] ) )
			$exclude = array_filter( $args['exclude'] );
		else if ( $args['exclude'] )
			$exclude = array_filter( explode( ',', $args['exclude'] ) );
		else
			$exclude = array();

		if ( $args['id_name_cb'] ) {
			list( $id, $name ) = call_user_func( $args['id_name_cb'], $args );
		} else {
			$id   = $args['id_attr'] ? $args['id_attr'] : ( $args['option_base'] ? $args['option_base'].'-' : '' ).$args['option_group'].'-'.esc_attr( $args['field'] );
			$name = $args['name_attr'] ? $args['name_attr'] : ( $args['option_base'] ? $args['option_base'].'_' : '' ).$args['option_group'].'['.esc_attr( $args['field'] ).']';
		}

		if ( isset( $args['options'][$args['field']] ) ) {
			$value = $args['options'][$args['field']];

			if ( isset( $args['defaults'][$args['field']] )
				&& $value === $args['defaults'][$args['field']] )
					$value = $args['default'];
		}

		if ( $args['constant'] && defined( $args['constant'] ) ) {
			$value = constant( $args['constant'] );

			$args['disabled'] = TRUE;
			$args['after'] = '<code>'.$args['constant'].'</code>';
		}

		if ( is_null( $args['cap'] ) ) {

			if ( in_array( $args['type'], array( 'role', 'cap', 'user' ) ) )
				$args['cap'] = 'promote_users';
			else
				$args['cap'] = 'manage_options';
		}

		if ( ! current_user_can( $args['cap'] ) )
			$args['type'] = 'noaccess';

		if ( $args['before'] )
			echo $args['before'].'&nbsp;';

		switch ( $args['type'] ) {

			case 'hidden' :

				echo gPluginHTML::tag( 'input', array(
					'type'  => 'hidden',
					'id'    => $id,
					'name'  => $name,
					'value' => $value,
					'data'  => $args['data'],
				) );

				$args['description'] = FALSE;

			break;
			case 'enabled' :

				$html = gPluginHTML::tag( 'option', array(
					'value'    => '0',
					'selected' => '0' == $value,
				), esc_html( empty( $args['values'][0] ) ? $args['string_disabled'] : $args['values'][0] ) );

				$html .= gPluginHTML::tag( 'option', array(
					'value'    => '1',
					'selected' => '1' == $value,
				), esc_html( empty( $args['values'][1] ) ? $args['string_enabled'] : $args['values'][1] ) );

				echo gPluginHTML::tag( 'select', array(
					'id'       => $id,
					'name'     => $name,
					'class'    => gPluginHTML::attrClass( $args['field_class'], '-type-enabled' ),
					'disabled' => $args['disabled'],
					'readonly' => $args['readonly'],
					'dir'      => $args['dir'],
					'data'     => $args['data'],
				), $html );

			break;
			case 'text' :

				if ( ! $args['field_class'] )
					$args['field_class'] = 'regular-text';

				if ( ! count( $args['dir'] ) )
					$args['data'] = array( 'accept' => 'text' );

				echo gPluginHTML::tag( 'input', array(
					'type'        => 'text',
					'id'          => $id,
					'name'        => $name,
					'value'       => $value,
					'class'       => $args['field_class'],
					'placeholder' => $args['placeholder'],
					'disabled'    => $args['disabled'],
					'readonly'    => $args['readonly'],
					'dir'         => $args['dir'],
					'data'        => $args['data'],
				) );

			break;
			case 'number' :

				if ( ! $args['field_class'] )
					$args['field_class'] = 'small-text';

				if ( ! $args['dir'] )
					$args['dir'] = 'ltr';

				if ( ! count( $args['dir'] ) )
					$args['data'] = array( 'accept' => 'number' );

				echo gPluginHTML::tag( 'input', array(
					'type'        => 'number',
					'id'          => $id,
					'name'        => $name,
					'value'       => $value,
					'step'        => $args['step_attr'],
					'min'         => $args['min_attr'],
					'class'       => gPluginHTML::attrClass( $args['field_class'], '-type-number' ),
					'placeholder' => $args['placeholder'],
					'disabled'    => $args['disabled'],
					'readonly'    => $args['readonly'],
					'dir'         => $args['dir'],
					'data'        => $args['data'],
				) );

			break;
			case 'url' :

				if ( ! $args['field_class'] )
					$args['field_class'] = array( 'large-text', 'url-text' );

				if ( ! $args['dir'] )
					$args['dir'] = 'ltr';

				if ( ! count( $args['dir'] ) )
					$args['data'] = array( 'accept' => 'url' );

				echo gPluginHTML::tag( 'input', array(
					'type'        => 'url',
					'id'          => $id,
					'name'        => $name,
					'value'       => $value,
					'class'       => $args['field_class'],
					'placeholder' => $args['placeholder'],
					'disabled'    => $args['disabled'],
					'readonly'    => $args['readonly'],
					'dir'         => $args['dir'],
					'data'        => $args['data'],
				) );

			break;
			case 'checkbox' :

				if ( count( $args['values'] ) ) {

					if ( ! is_null( $args['none_title'] ) ) {

						$html = gPluginHTML::tag( 'input', array(
							'type'     => 'checkbox',
							'id'       => $id.( is_null( $args['none_value'] ) ? '' : '-'.$args['none_value'] ),
							'name'     => $name.( is_null( $args['none_value'] ) ? '' : '-'.$args['none_value'] ),
							'value'    => is_null( $args['none_value'] ) ? '1' : $args['none_value'],
							'checked'  => in_array( $args['none_value'], ( array ) $value ),
							'class'    => gPluginHTML::attrClass( $args['field_class'], '-type-checkbox', '-option-none' ),
							'disabled' => $args['disabled'],
							'readonly' => $args['readonly'],
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
							'id'       => $id.'-'.$value_name,
							'name'     => $name.'['.$value_name.']',
							'value'    => '1',
							'checked'  => in_array( $value_name, ( array ) $value ),
							'class'    => $args['field_class'],
							'disabled' => $args['disabled'],
							'readonly' => $args['readonly'],
							'dir'      => $args['dir'],
						) );

						echo '<p>'.gPluginHTML::tag( 'label', array(
							'for' => $id.'-'.$value_name,
						), $html.'&nbsp;'.$value_title ).'</p>';
					}

				} else {

					$html = gPluginHTML::tag( 'input', array(
						'type'     => 'checkbox',
						'id'       => $id,
						'name'     => $name,
						'value'    => '1',
						'checked'  => $value,
						'class'    => $args['field_class'],
						'disabled' => $args['disabled'],
						'readonly' => $args['readonly'],
						'dir'      => $args['dir'],
						'data'     => $args['data'],
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
							'id'       => $id.( is_null( $args['none_value'] ) ? '' : '-'.$args['none_value'] ),
							'name'     => $name,
							'value'    => is_null( $args['none_value'] ) ? FALSE : $args['none_value'],
							'checked'  => in_array( $args['none_value'], ( array ) $value ),
							'class'    => gPluginHTML::attrClass( $args['field_class'], '-type-radio', '-option-none' ),
							'disabled' => $args['disabled'],
							'readonly' => $args['readonly'],
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
							'id'       => $id.'-'.$value_name,
							'name'     => $name,
							'value'    => $value_name,
							'checked'  => in_array( $value_name, ( array ) $value ),
							'class'    => gPluginHTML::attrClass( $args['field_class'], '-type-radio' ),
							'disabled' => $args['disabled'],
							'readonly' => $args['readonly'],
							'dir'      => $args['dir'],
						) );

						echo '<p>'.gPluginHTML::tag( 'label', array(
							'for' => $id.'-'.$value_name,
						), $html.'&nbsp;'.$value_title ).'</p>';
					}
				}

			break;
			case 'select' :

				if ( FALSE !== $args['values'] ) {

					if ( ! is_null( $args['none_title'] ) ) {

						if ( is_null( $args['none_value'] ) )
							$args['none_value'] = '0';

						$html .= gPluginHTML::tag( 'option', array(
							'value'    => $args['none_value'],
							'selected' => $value == $args['none_value'],
						), esc_html( $args['none_title'] ) );
					}

					foreach ( $args['values'] as $value_name => $value_title ) {

						if ( in_array( $value_name, $exclude ) )
							continue;

						$html .= gPluginHTML::tag( 'option', array(
							'value'    => $value_name,
							'selected' => $value == $value_name,
						), esc_html( $value_title ) );
					}

					echo gPluginHTML::tag( 'select', array(
						'id'       => $id,
						'name'     => $name,
						'class'    => gPluginHTML::attrClass( $args['field_class'], '-type-select' ),
						'disabled' => $args['disabled'],
						'readonly' => $args['readonly'],
						'dir'      => $args['dir'],
						'data'     => $args['data'],
					), $html );
				}

			break;
			case 'textarea' :
			case 'textarea-quicktags' :

				if ( ! $args['field_class'] )
					$args['field_class'] = 'large-text';

				if ( 'textarea-quicktags' == $args['type'] ) {

					$args['field_class'] = gPluginHTML::attrClass( $args['field_class'], 'textarea-quicktags', 'code' );

					if ( ! $args['values'] )
						$args['values'] = array(
							'link',
							'em',
							'strong',
						);

					$scripts[] = 'quicktags({id:"'.$id.'",buttons:"'.implode( ',', $args['values'] ).'"});';

					wp_enqueue_script( 'quicktags' );
				}

				echo gPluginHTML::tag( 'textarea', array(
					'id'          => $id,
					'name'        => $name,
					'rows'        => $args['rows_attr'],
					'cols'        => $args['cols_attr'],
					'class'       => gPluginHTML::attrClass( $args['field_class'], '-type'.$args['type'] ),
					'placeholder' => $args['placeholder'],
					'disabled'    => $args['disabled'],
					'readonly'    => $args['readonly'],
					'dir'         => $args['dir'],
					'data'        => $args['data'],
				), $value );

			break;
			case 'page' :

				if ( ! $args['values'] )
					$args['values'] = 'page';

				if ( is_null( $args['none_title'] ) )
					$args['none_title'] = $args['string_select'];

				if ( is_null( $args['none_value'] ) )
					$args['none_value'] = '0';

				$query = array_merge( array(
					'post_type'   => $args['values'],
					'selected'    => $value,
					'exclude'     => implode( ',', $exclude ),
					'sort_column' => 'menu_order',
					'sort_order'  => 'asc',
					'post_status' => array( 'publish', 'future', 'draft' ),
				), $args['extra'] );

				$pages = get_pages( $query );

				if ( ! empty( $pages ) ) {

					$html .= gPluginHTML::tag( 'option', array(
						'value' => $args['none_value'],
					), esc_html( $args['none_title'] ) );

					$html .= walk_page_dropdown_tree( $pages, ( isset( $query['depth'] ) ? $query['depth'] : 0 ), $query );

					echo gPluginHTML::tag( 'select', array(
						'id'       => $id,
						'name'     => $name,
						'class'    => gPluginHTML::attrClass( $args['field_class'], '-type-page', '-posttype-'.$args['values'] ),
						'disabled' => $args['disabled'],
						'readonly' => $args['readonly'],
						'dir'      => $args['dir'],
						'data'     => $args['data'],
					), $html );

				} else {
					$args['description'] = FALSE;
				}

			break;
			case 'role' :

				if ( ! $args['values'] )
					$args['values'] = array_reverse( get_editable_roles() );

				if ( is_null( $args['none_title'] ) )
					$args['none_title'] = $args['string_select'];

				if ( is_null( $args['none_value'] ) )
					$args['none_value'] = '0';

				$html .= gPluginHTML::tag( 'option', array(
					'value' => $args['none_value'],
				), esc_html( $args['none_title'] ) );

				foreach ( $args['values'] as $value_name => $value_title ) {

					if ( in_array( $value_name, $exclude ) )
						continue;

					$html .= gPluginHTML::tag( 'option', array(
						'value'    => $value_name,
						'selected' => $value == $value_name,
					), esc_html( translate_user_role( $value_title['name'] ) ) );
				}

				echo gPluginHTML::tag( 'select', array(
					'id'       => $id,
					'name'     => $name,
					'class'    => gPluginHTML::attrClass( $args['field_class'], '-type-role' ),
					'disabled' => $args['disabled'],
					'readonly' => $args['readonly'],
					'dir'      => $args['dir'],
					'data'     => $args['data'],
				), $html );

			break;
			case 'cap' :

				if ( ! $args['values'] )
					$args['values'] = self::getUserCapList( NULL, $args['none_title'], $args['none_value'] );

				if ( count( $args['values'] ) ) {

					foreach ( $args['values'] as $value_name => $value_title ) {

						if ( in_array( $value_name, $exclude ) )
							continue;

						$html .= gPluginHTML::tag( 'option', array(
							'value'    => $value_name,
							'selected' => $value === $value_name,
						), esc_html( $value_title ) );
					}

					echo gPluginHTML::tag( 'select', array(
						'id'       => $id,
						'name'     => $name,
						'class'    => gPluginHTML::attrClass( $args['field_class'], '-type-cap' ),
						'disabled' => $args['disabled'],
						'readonly' => $args['readonly'],
						'dir'      => $args['dir'],
						'data'     => $args['data'],
					), $html );

				} else {

					$args['description'] = FALSE;
				}

			break;
			case 'user' :

				if ( ! $args['values'] )
					$args['values'] = gPluginWPHelper::getUsers( FALSE, FALSE, $args['extra'] );

				if ( ! is_null( $args['none_title'] ) ) {

					$html .= gPluginHTML::tag( 'option', array(
						'value'    => is_null( $args['none_value'] ) ? FALSE : $args['none_value'],
						'selected' => $value == $args['none_value'],
					), esc_html( $args['none_title'] ) );
				}

				foreach ( $args['values'] as $value_name => $value_title ) {

					if ( in_array( $value_name, $exclude ) )
						continue;

					$html .= gPluginHTML::tag( 'option', array(
						'value'    => $value_name,
						'selected' => $value == $value_name,
					), esc_html( sprintf( '%1$s (%2$s)', $value_title->display_name, $value_title->user_login ) ) );
				}

				echo gPluginHTML::tag( 'select', array(
					'id'       => $id,
					'name'     => $name,
					'class'    => gPluginHTML::attrClass( $args['field_class'], '-type-user' ),
					'disabled' => $args['disabled'],
					'readonly' => $args['readonly'],
					'dir'      => $args['dir'],
					'data'     => $args['data'],
				), $html );

			break;
			case 'priority' :

				if ( ! $args['values'] )
					$args['values'] = self::priorityOptions( FALSE );

				if ( ! $args['dir'] )
					$args['dir'] = 'ltr';

				foreach ( $args['values'] as $value_name => $value_title ) {

					if ( in_array( $value_name, $exclude ) )
						continue;

					$html .= gPluginHTML::tag( 'option', array(
						'value'    => $value_name,
						'selected' => $value == $value_name,
					), esc_html( $value_title ) );
				}

				echo gPluginHTML::tag( 'select', array(
					'id'       => $id,
					'name'     => $name,
					'class'    => gPluginHTML::attrClass( $args['field_class'], '-type-priority' ),
					'disabled' => $args['disabled'],
					'readonly' => $args['readonly'],
					'dir'      => $args['dir'],
					'data'     => $args['data'],
				), $html );

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
					'id'       => $id,
					'name'     => $id,
					'class'    => $args['field_class'],
					'disabled' => $args['disabled'],
					'dir'      => $args['dir'],
					'data'     => $args['data'],
				) );

			break;
			case 'posttypes' :

				if ( ! $args['values'] )
					$args['values'] = gPluginWPHelper::getPostTypes( 0,
						array_merge( array( 'public' => TRUE ), $args['extra'] ) );

				foreach ( $args['values'] as $value_name => $value_title ) {

					if ( in_array( $value_name, $exclude ) )
						continue;

					$html = gPluginHTML::tag( 'input', array(
						'type'     => 'checkbox',
						'id'       => $id.'-'.$value_name,
						'name'     => $name.'['.$value_name.']',
						'value'    => '1',
						'checked'  => in_array( $value_name, ( array ) $value ),
						'class'    => gPluginHTML::attrClass( $args['field_class'], '-type-posttypes' ),
						'disabled' => $args['disabled'],
						'readonly' => $args['readonly'],
						'dir'      => $args['dir'],
					) );

					echo '<p>'.gPluginHTML::tag( 'label', array(
						'for' => $id.'-'.$value_name,
					), $html.'&nbsp;'.esc_html( $value_title ) ).' &mdash; <code>'.$value_name.'</code>'.'</p>';
				}

			break;
			case 'taxonomies' :

				if ( ! $args['values'] )
					$args['values'] = gPluginWPHelper::getTaxonomies( 0, $args['extra'] );

				foreach ( $args['values'] as $value_name => $value_title ) {

					if ( in_array( $value_name, $exclude ) )
						continue;

					$html = gPluginHTML::tag( 'input', array(
						'type'     => 'checkbox',
						'id'       => $id.'-'.$value_name,
						'name'     => $name.'['.$value_name.']',
						'value'    => '1',
						'checked'  => in_array( $value_name, ( array ) $value ),
						'class'    => gPluginHTML::attrClass( $args['field_class'], '-type-taxonomies' ),
						'disabled' => $args['disabled'],
						'readonly' => $args['readonly'],
						'dir'      => $args['dir'],
					) );

					echo '<p>'.gPluginHTML::tag( 'label', array(
						'for' => $id.'-'.$value_name,
					), $html.'&nbsp;'.esc_html( $value_title ) ).' &mdash; <code>'.$value_name.'</code>'.'</p>';
				}

			break;
			case 'callback' :

				if ( is_callable( $args['callback'] ) ) {

					call_user_func_array( $args['callback'], array( &$args,
						compact( 'html', 'value', 'name', 'id', 'exclude' ) ) );

				} else if ( gPluginWPHelper::isDev() ) {

					echo 'Error: Setting is not callable!';
				}

			break;
			case 'noaccess' :

				echo gPluginHTML::tag( 'span', array(
					'class' => '-type-noaccess',
				), $args['string_noaccess'] );

			break;
			case 'custom' :

				if ( ! is_array( $args['values'] ) )
					echo $args['values'];
				else
					echo $value;

			break;
			case 'debug' :

				self::dump( $args['options'] );

			break;
			default :

				echo 'Error: setting type not defind!';
		}

		if ( $args['after'] )
			echo '&nbsp;'.$args['after'];

		if ( $args['description']
			&& FALSE !== $args['values'] )
				echo gPluginHTML::tag( 'p', array(
					'class' => 'description',
				), $args['description'] );

		if ( $args['wrap'] )
			echo '</td></tr>';
	}
} }
