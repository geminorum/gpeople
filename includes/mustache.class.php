<?php defined( 'ABSPATH' ) || die( header( 'HTTP/1.0 403 Forbidden' ) );

// modified from P2P_Mustache by http://scribu.net
// http://plugins.svn.wordpress.org/posts-to-posts/trunk/admin/mustache.php

abstract class gPeopleMustache
{

	private static $loader;
	private static $loader_custom;
	private static $mustache;

	public static function init()
	{
		if ( ! class_exists( 'Mustache' ) )
			require_once( GPEOPLE_DIR.'assets/libs/mustache/Mustache.php');

		if ( ! class_exists( 'MustacheLoader' ) )
			require_once( GPEOPLE_DIR.'assets/libs/mustache/MustacheLoader.php' );

		self::$loader = new MustacheLoader( GPEOPLE_DIR.'assets/templates', 'html' );

		if ( is_dir( WP_CONTENT_DIR.'/templates' ) )
			self::$loader_custom = new MustacheLoader( WP_CONTENT_DIR.'/templates', 'html' );

		self::$mustache = new Mustache( NULL, NULL, self::$loader );
	}

	public static function renderOLD( $template, $data )
	{
		return self::$mustache->render( self::$loader[$template], $data );
	}

	public static function render( $file, $data )
	{
		// WHAT IF: we merge partials(loaders) ?
		if ( file_exists( WP_CONTENT_DIR.'/templates/gpeople-'.$file.'.html' ) )
			return self::$mustache->render( file_get_contents( WP_CONTENT_DIR.'/templates/gpeople-'.$file.'.html' ), $data, self::$loader_custom );

		return self::$mustache->render( self::$loader[$file], $data );
	}
}

// gPeopleMustache::init();
