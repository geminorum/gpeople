<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

if ( ! class_exists( 'gPluginClassCore' ) ) {

// http://stackoverflow.com/a/6386309
interface gPluginClassCoreInterface
{
	static function instance( $constants = array(), $args = array() );
}

class gPluginClassCore implements gPluginClassCoreInterface
{
	protected $data;

	public static final function instance( $class = 'gPluginClassCore', $constants = array(), $args = array() )
	{
		static $instance;
		if ( ! isset( $instance ) )	{
			$instance = new $class();
			$instance->setup_globals( $constants, $args );
			$instance->setup_actions();
		}
		return $instance;
	}

	// A dummy constructor to prevent loading more than once.
	protected function __construct() { /* Do nothing here */ }
	protected function __distruct() { $this->data = NULL; }
	// A dummy magic method to prevent cloning
	// public function __clone() { _doing_it_wrong( __FUNCTION__, 'Doing it wrong, chief.', GPLUGIN_VERSION ); }
	// A dummy magic method to prevent unserialization
	// public function __wakeup() { _doing_it_wrong( __FUNCTION__, 'Doing it wrong, chief.', GPLUGIN_VERSION ); }
	// Magic method for checking the existence of a certain custom field
	public function __isset( $key ) { return isset( $this->data[$key] ); }
	// Magic method for getting varibles
	public function __get( $key ) { return isset( $this->data[$key] ) ? $this->data[$key] : NULL; }
	// Magic method for setting varibles
	public function __set( $key, $value ) { $this->data[$key] = $value; }
	public function __unset( $key ) { if ( isset( $this->data[$key] ) ) unset( $this->data[$key] ); }
	// Magic method to prevent notices and errors from invalid method calls
	public function __call( $name = '', $args = array() ) { unset( $name, $args ); return NULL; }

	// workaround to avoid : "Indirect modification of overloaded property"
	public function inject( $key, $value )
	{
		$this->{$key} = array_merge( $this->{$key}, (array) $value );
	}

	public function append( $key, $arr_key, $arr_val )
	{
		$temp           = $this->{$key};
		$temp[$arr_key] = $arr_val;
		$this->{$key}   = $temp;
	}

	// must dep
	// shortcode_atts() mock-up
	public function set_args( $defaults, $args ) {
		$args = (array) $args;
		$out = array();
		foreach ( $defaults as $name => $default ) {
			if ( array_key_exists( $name, $args ) )
				$out[$name] = $args[$name];
			else
				$out[$name] = $default;
		}
		$this->args = $out;
	}

	// Set some smart defaults to class variables. Allow some of them to be filtered to allow for early overriding.
	public function setup_globals( $constants = array(), $args = array() )
	{
		$this->constants = $constants;
		$this->args      = $args;
	}

	// Setup the default hooks and actions
	public function setup_actions() { }

	////////////////////////////////////////////////////
	// helpers

	// ANCESTOR : shortcode_atts()
	public static function atts( $pairs, $atts )
	{
		$atts = (array) $atts;
		$out  = array();

		foreach ( $pairs as $name => $default ) {
			if ( array_key_exists( $name, $atts ) )
				$out[$name] = $atts[$name];
			else
				$out[$name] = $default;
		}

		return $out;
	}

	// ANCESTOR: is_wp_error()
	public static function isError( $thing )
	{
		return ( ( $thing instanceof \WP_Error ) || ( $thing instanceof Error ) );
	}

	public static function dump( $var, $safe = TRUE, $echo = TRUE )
	{
		$export = var_export( $var, TRUE );
		if ( $safe ) $export = htmlspecialchars( $export );
		$export = '<pre dir="ltr" style="text-align:left;direction:ltr;">'.$export.'</pre>';
		if ( ! $echo ) return $export;
		echo $export;
	}

	public static function kill()
	{
		foreach ( func_get_args() as $arg )
			self::dump( $arg );
		echo self::stat();
		die();
	}

	public static function stat( $format = NULL )
	{
		if ( is_null( $format ) )
			$format = '%d queries in %.3f seconds, using %.2fMB memory.';

		return sprintf( $format,
			@$GLOBALS['wpdb']->num_queries,
			self::timerStop( FALSE, 3 ),
			memory_get_peak_usage() / 1024 / 1024
		);
	}

	// WP core function without number_format_i18n
	public static function timerStop( $echo = FALSE, $precision = 3 )
	{
		global $timestart;
		$total = number_format( ( microtime( TRUE ) - $timestart ), $precision );
		if ( $echo ) echo $total;
		return $total;
	}

	// INTERNAL
	public static function __log( $log )
	{
		if ( defined( 'WP_DEBUG_LOG' ) && ! WP_DEBUG_LOG )
			return;

		if ( is_array( $log ) || is_object( $log ) )
			error_log( print_r( $log, TRUE ) );
		else
			error_log( $log );
	}

	// INTERNAL: used on anything deprecated
	protected static function __dep( $note = '', $prefix = 'DEP: ', $offset = 1 )
	{
		if ( defined( 'WP_DEBUG_LOG' ) && ! WP_DEBUG_LOG )
			return;

		$trace = debug_backtrace();

		$log = $prefix;

		if ( isset( $trace[$offset]['object'] ) )
			$log .= get_class( $trace[$offset]['object'] ).'::';
		else if ( isset( $trace[$offset]['class'] ) )
			$log .= $trace[$offset]['class'].'::';

		$log .= $trace[$offset]['function'].'()';

		$offset++;

		if ( isset( $trace[$offset]['function'] ) ) {
			$log .= '|FROM: ';
			if ( isset( $trace[$offset]['object'] ) )
				$log .= get_class( $trace[$offset]['object'] ).'::';
			else if ( isset( $trace[$offset]['class'] ) )
				$log .= $trace[$offset]['class'].'::';
			$log .= $trace[$offset]['function'].'()';
		}

		if ( $note )
			$log .= '|'.$note;

		self::__log( $log );
	}
} }
