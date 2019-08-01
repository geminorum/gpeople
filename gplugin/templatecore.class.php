<?php defined( 'ABSPATH' ) || die( header( 'HTTP/1.0 403 Forbidden' ) );

if ( ! class_exists( 'gPluginTemplateCore' ) ) { class gPluginTemplateCore extends gPluginClassCore
{

	public function setup_globals( $constants = array(), $args = array() )
	{
		self::__dep();
	}
} }
