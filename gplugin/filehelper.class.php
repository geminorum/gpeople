<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

if ( ! class_exists( 'gPluginFileHelper' ) ) { class gPluginFileHelper extends gPluginClassCore
{

	public static function mime( $extension )
	{
		$mimes = array(
			'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
			'ppt'  => 'application/vnd.ms-powerpoint',
			'doc'  => 'application/msword',
			'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
			'xls'  => 'application/vnd.ms-excel',
			'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
			'csv'  => 'text/csv',
			'xml'  => 'text/xml',
			'webm' => 'video/webm',
			'flv'  => 'video/x-flv',
			'ac3'  => 'audio/ac3',
			'mpa'  => 'audio/MPA',
			'mp4'  => 'video/mp4',
			'mpg4' => 'video/mp4',
			'flv'  => 'video/x-flv',
			'svg'  => 'image/svg+xml',
		);

		if ( isset( $mimes[$extension] ) )
			return $mimes[$extension];

		return '';
	}

	// FIXME: DEPRECATED: obsolete, since we cannot use this before gPlugin!
	public static function require_array( $array, $base_path, $suffix = '.class.php', $folder = 'includes' )
	{
		self::__dep();

		foreach ( $array as $file )
			if ( file_exists( $base_path.$folder.DIRECTORY_SEPARATOR.$file.$suffix ) )
				require_once( $base_path.$folder.DIRECTORY_SEPARATOR.$file.$suffix );
	}

	// FIXME: DEPRECATED: use like : wp_ext2type() & wp_check_filetype()
	// Checks if the string (filename) provided is an image URL
	public static function is_image_url( $string )
	{
		self::__dep();

		switch ( strtolower( self::extension( $string ) ) ) {
			case 'jpg': return TRUE;
			case 'png': return TRUE;
			case 'gif': return TRUE;
		}
		return FALSE;
	}

	// FIXME: DEPRECATED: use like : wp_ext2type() & wp_check_filetype()
	// Returns the file extension of a filename.
	public static function extension( $string )
	{
		self::__dep();

		$parts = explode( '.', $string );
		return end( $parts );
	}

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
/// NOT USED YET ---------------------------------------------------------------

	// TESTED: not working very well with UTF
	// http://www.scriptville.in/parse-csv-data-to-array/
	// Parse csv data to array
	// Here, we have function to parse csv file into array. This function
	// return associative array of each line with all column name in array
	// as key and csv data for each line as key values.
	public static function csvToArray( $file )
	{
		$rows = $headers = array();

		if ( file_exists( $file ) && is_readable( $file ) ) {

			$handle = fopen( $file, 'r' );

			while ( ! feof( $handle ) ) {

				$row = fgetcsv( $handle, 10240, ',', '"' );

				if ( empty( $headers ) )
					$headers = $row;
				else if ( is_array( $row ) ) {
					array_splice( $row, count( $headers ) );
					$rows[] = array_combine( $headers, $row );
				}
			}

			fclose( $handle );

		} else {

			throw new Exception( $file.' doesn`t exist or not readable.' );
		}

		return $rows;
	}
} }
