<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

if ( ! class_exists( 'gPluginTemplateCore' ) ) { class gPluginTemplateCore extends gPluginClassCore
{

	public function setup_globals( $constants = array(), $args = array() )
	{
		self::__dep();
	}
} }
