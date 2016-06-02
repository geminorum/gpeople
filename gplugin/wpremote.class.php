<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

if ( ! class_exists( 'gPluginWPRemote' ) ) { class gPluginWPRemote extends gPluginClassCore
{

	public static function getJSON( $url, $atts = array(), $assoc = FALSE )
	{
		self::__dep( 'gPluginHTTP::getJSON()' );
		return gPluginHTTP::getJSON( $url, $atts, $assoc );
	}

	public static function getHTML( $url, $atts = array() )
	{
		self::__dep( 'gPluginHTTP::getHTML()' );
		return gPluginHTTP::getHTML( $url, $atts );
	}
} }
