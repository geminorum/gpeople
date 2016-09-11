<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

if ( ! class_exists( 'gPluginPluginCore' ) ) { class gPluginPluginCore extends gPluginClassCore
{
	// DRAFT

	public function setup_globals( $constants = array(), $args = array() )
	{
		self::__dep();
	}
} }
