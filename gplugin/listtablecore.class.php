<?php defined( 'ABSPATH' ) || die( header( 'HTTP/1.0 403 Forbidden' ) );

if ( ! class_exists( 'gPluginListTableCore' ) ) { class gPluginListTableCore extends gPluginClassCore
{

	public function setup_globals( $constants = array(), $args = array() )
	{
		self::__dep();
	}
} }
