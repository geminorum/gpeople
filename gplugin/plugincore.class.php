<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

if ( ! class_exists( 'gPluginPluginCore' ) ) {

// http://stackoverflow.com/a/6386309
interface gPluginPluginCoreInterface
{
	static function instance( $constants = array(), $args = array() );
}

class gPluginPluginCore implements gPluginPluginCoreInterface
{
	public static final function instance( $class = 'gPluginPluginCore', $constants = array(), $args = array() )
	{
		static $instance;
		if ( ! isset( $instance ) )	{
			$instance = new $class();
			$instance->setup_globals( $constants, $args );
			$instance->setup_actions();
			//gPeoplePluginCore::dump( $class ); die();

			// Backwards compat for when we promoted use of the $edit_flow global
			//global $$class;
			//$$class = self::$instance;
		}
		return $instance;
	}

	// A dummy constructor to prevent loading more than once.
	protected function __construct() { /* Do nothing here */ }

	public function setup_globals( $constants = array(), $args = array() )
	{
		$this->modules   = new stdClass();
		$this->constants = $constants;
		$this->args      = $args; // TODO : set defaults
	}

	public function setup_actions()
	{
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'init', array( $this, 'init_after' ), 1000 );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
	}

	private function load_modules() { }
	private function load_textdomain() {}
	private function admin_init() {}
	private function init_after() {}

	function init()
	{
		if ( ! is_textdomain_loaded( $this->args['domain'] ) )
			$this->load_textdomain();

		$this->load_modules();
		$this->load_module_options(); // Load all of the module options

		// Load all of the modules that are enabled.
		// Modules won't have an options value if they aren't enabled
		foreach ( $this->modules as $mod_name => $mod_data )
			if ( isset( $mod_data->options->enabled ) && $mod_data->options->enabled == 'on' )
				$this->$mod_name->init();
	}

	// must dep
	// shortcode_atts() mock-up
	public function set_args( $defaults, $args )
	{
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
} }
