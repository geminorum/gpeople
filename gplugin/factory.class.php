<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

defined( 'GPLUGIN_VERSION' ) or define( 'GPLUGIN_VERSION', '32' );
defined( 'GPLUGIN_TEXTDOMAIN' ) or define( 'GPLUGIN_TEXTDOMAIN', 'gplugin' );
defined( 'GPLUGIN_DEBUG' ) or define( 'GPLUGIN_DEBUG', constant( 'WP_DEBUG' ) );

// MAYBE : adding current directory
defined( 'GPLUGIN_DIR' ) or define( 'GPLUGIN_DIR', '' );
defined( 'GPLUGIN_URL' ) or define( 'GPLUGIN_URL', '' );

// FIXME: DEPRECATED: use gPluginFactory::get()
if ( ! function_exists( 'gPluginFactory' ) ) :
	function gPluginFactory( $class = 'gPluginClassCore', $constants = array(), $args = array() ) {

		gPluginClassCore::__log( 'DEPRECATED: gPluginFactory - while init '.$class );

		if ( class_exists( $class ) )
			return call_user_func_array( array( $class, 'instance' ), array( $class, $constants, $args ) );

		gPluginClassCore::__log( 'CLASS NOT EXISTS: '.$class );

		return FALSE;
} endif;

// FIXME: DEPRECATED: use gPluginFactory::log()
if ( ! function_exists( 'gPluginError' ) ) : function gPluginError() {
	gPluginClassCore::__log( 'DEP: gPluginError()' );
} endif;

if ( ! class_exists( 'gPluginFactory' ) ) { class gPluginFactory
{
	public static function get( $class = 'gPluginClassCore', $constants = array(), $args = array() )
	{
		if ( class_exists( $class ) ) {

			try {

				return call_user_func_array( array( $class, 'instance' ), array( $class, $constants, $args ) );

			} catch ( \Exception $e ) {

				self::log( 'CLASS '.$class.' INIT EXCEPTION: '.$e->getMessage() );

				return FALSE;
			}
		} else {

			self::log( 'CLASS '.$class.' NOT EXISTS' );

		}

		return FALSE;
	}

	public static function done( $class = 'gPluginClassCore', $constants = array(), $args = array() )
	{
		if ( class_exists( $class ) ) {

			try {

				$object = call_user_func_array( array( $class, 'instance' ), array( $class, $constants, $args ) );

				unset( $object );

				return TRUE;

			} catch ( \Exception $e ) {

				self::log( 'CLASS '.$class.' DONE EXCEPTION: '.$e->getMessage() );

				return FALSE;
			}
		} else {

			self::log( 'CLASS '.$class.' NOT EXISTS' );

		}

		return FALSE;
	}

	public static function log( $data )
	{
		if ( class_exists( 'gPluginClassCore' ) )
			gPluginClassCore::__log( $data );

		else if ( is_array( $data ) )
			error_log( print_r( $data, TRUE ) );

		else
			error_log( $data );
	}

	// A dummy constructor to prevent loading more than once.
	protected function __construct() { /* Do nothing here */ }
} }
