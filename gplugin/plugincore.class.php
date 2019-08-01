<?php defined( 'ABSPATH' ) || die( header( 'HTTP/1.0 403 Forbidden' ) );

if ( ! class_exists( 'gPluginPluginCore' ) ) { class gPluginPluginCore extends gPluginClassCore
{
	// DRAFT

	public function setup_globals( $constants = array(), $args = array() )
	{
		self::__dep();
	}
} }
