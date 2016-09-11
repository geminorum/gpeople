<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

if ( ! class_exists( 'gPluginFilteredCore' ) ) { class gPluginFilteredCore extends gPluginClassCore
{

	public function setup_globals( $constants = array(), $args = array() )
	{
		$this->args = gPluginUtils::recursiveParseArgs( $args, array(
			'domain'        => 'gplugin',
			'title'         => 'gPlugin',
			'filter_prefix' => FALSE,
		) );

		if ( FALSE === $this->args['filter_prefix'] )
			$this->inject( 'args', array( 'filter_prefix' => $this->args['domain'] ) );

		$this->constants = gPluginUtils::recursiveParseArgs( $constants, array(
			'plugin_dir' => GPLUGIN_DIR,
			'plugin_url' => GPLUGIN_URL,
		) );

		$this->filtered = array();
	}

	public function get( $group, $fallback = array() )
	{
		if ( isset( $this->filtered[$group] )
			&& count( $this->filtered[$group] ) )
				return $this->filtered[$group];

		if ( ! method_exists( $this, $group ) ) {

			if ( ! count( $fallback ) )
				self::__log( 'GROUP FILTER NOT EXISTS: '.get_class( $this ).'::'.$group );

			return $fallback;
		}

		$group_defaults = call_user_func( array( $this, $group ) );
		$this->inject( 'filtered', array( $group => apply_filters( $this->args['filter_prefix'].'_'.$group, $group_defaults ) ) );

		return $this->filtered[$group];
	}

	public static function error( $message )
	{
		return gPluginHTML::error( $message );
	}

	public static function success( $message )
	{
		return gPluginHTML::success( $message );
	}

	public static function warning( $message )
	{
		return gPluginHTML::warning( $message );
	}

	public static function info( $message )
	{
		return gPluginHTML::info( $message );
	}

	// FIXME: DEPRECATED
	public static function updated( $message )
	{
		self::__dep( 'self::success()' );
		return gPluginHTML::success( $message );
	}

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
/// gPlugin default group filters

	// protected function admin_settings_subs() { return array(); }
	// protected function admin_settings_messages() { return array(); }
	// protected function root_settings_subs() { return array(); }
	// protected function root_settings_messages() { return array(); }
	// protected function remote_settings_subs() { return array(); }
	// protected function remote_settings_messages() { return array(); }
	// protected function network_settings_subs() { return array(); }
	// protected function network_settings_messages() { return array(); }

	// protected function network_settings_args() { return array(); }
	// protected function component_settings_args() { return array(); }
	// protected function module_settings_args() { return array(); }

	// protected function root_settings_titles() { return array(); }
	// protected function remote_settings_titles() { return array(); }
	// protected function network_settings_titles() { return array(); }
} }
