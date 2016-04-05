<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

if ( ! class_exists( 'gPluginImageHelper' ) ) { class gPluginImageHelper extends gPluginClassCore
{

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
/// NOT USED YET ---------------------------------------------------------------

	// @SEE : http://css-tricks.com/data-uris/
	public static function toBase64( $path, $filetype )
	{
		return 'data:image/'.$filetype.';base64,'.base64_encode( file_get_contents( $path ) );
	}

	// http://davidwalsh.name/data-uri-php
	// http://www.catswhocode.com/blog/snippets/image-data-uris-with-php
	public static function getDataURI( $image, $mime = '' )
	{
		return 'data: '.( function_exists( 'mime_content_type' ) ? mime_content_type( $image ) : $mime ).';base64,'.base64_encode( file_get_contents( $image ) );
	}

	// NOTE: ONLY jpeg!
	// @SOURCE: http://snipplr.com/view/27513/
	public static function fromBase64( $data, $path )
	{
		$img = imagecreatefromstring( base64_decode( $data ) );

		if ( $img != FALSE )
			imagejpeg( $img, $path );
	}
} }
