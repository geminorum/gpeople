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
		foreach ( $array as $file )
			if ( file_exists( $base_path.$folder.DIRECTORY_SEPARATOR.$file.$suffix ) )
				require_once( $base_path.$folder.DIRECTORY_SEPARATOR.$file.$suffix );
	}

	// FIXME: MUST DEPRECATE: use like : wp_ext2type() & wp_check_filetype()
	// Checks if the string (filename) provided is an image URL
	public static function is_image_url( $string )
	{
		switch ( strtolower( self::extension( $string ) ) ) {
			case 'jpg': return TRUE;
			case 'png': return TRUE;
			case 'gif': return TRUE;
		}
		return FALSE;
	}

	// FIXME: MUST DEPRECATE: use like : wp_ext2type() & wp_check_filetype()
	// Returns the file extension of a filename.
	public static function extension( $string )
	{
		$parts = explode( '.', $string );
		return end( $parts );
	}

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
/// NOT USED YET ---------------------------------------------------------------

	// FIXME: DRAFT
	// http://php.net/manual/en/function.include.php#102731
	// Sometimes it will be usefull to include a string as a filename
	public static function getEncrypted( $file )
	{
		//get content
		$cFile = file_get_contents('crypted.file');
		//decrypt the content
		$content = decrypte($cFile);

		//include this
		include("data://text/plain;base64,".base64_encode($content));
		//or
		include("data://text/plain,".urlencode($content));
	}

	// FIXME: DRAFT
	// http://php.net/manual/en/function.glob.php#92710
	// Those of you with PHP 5 don't have to come up with these wild functions to scan a directory recursively: the SPL can do it.
	// Not to mention the fact that $file will be an SplFileInfo class, so you can do powerful stuff really easily:
	public static function scanDirRec( $path )
	{
		$dir_iterator = new RecursiveDirectoryIterator( $path );
		$iterator     = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST); // could use CHILD_FIRST if you so wish


		foreach ($iterator as $file) {
			echo $file, "\n";
		}


		$size = 0;
		foreach ($iterator as $file) {
			if ($file->isFile()) {
				echo substr($file->getPathname(), 27) . ": " . $file->getSize() . " B; modified " . date("Y-m-d", $file->getMTime()) . "\n";
				$size += $file->getSize();
			}
		}

		echo "\nTotal file size: ", $size, " bytes\n";

		// \Luna\luna.msstyles: 4190352 B; modified 2008-04-13
		// \Luna\Shell\Homestead\shellstyle.dll: 362496 B; modified 2006-02-28
		// \Luna\Shell\Metallic\shellstyle.dll: 362496 B; modified 2006-02-28
		// \Luna\Shell\NormalColor\shellstyle.dll: 361472 B; modified 2006-02-28
		// \Luna.theme: 1222 B; modified 2006-02-28
		// \Windows Classic.theme: 3025 B; modified 2006-02-28
		//
		// Total file size: 5281063 bytes
	}

	// FIXME: DRAFT
	// http://php.net/manual/en/function.glob.php#82539
	// Here is a function that returns specific files in an array, with all of the details. Includes some basic garbage checking.
	// $source_folder // the location of your files
	// $ext // file extension you want to limit to (i.e.: *.txt)
	// $sec // if you only want files that are at least so old.
	public static function glob_files($source_folder, $ext, $sec, $limit)
	{
		if( !is_dir( $source_folder ) ) {
			die ( "Invalid directory.\n\n" );
		}

		$FILES = glob($source_folder."\*.".$ext);
		$set_limit    = 0;

		foreach($FILES as $key => $file) {

			if( $set_limit == $limit )    break;

			if( filemtime( $file ) > $sec ){

				$FILE_LIST[$key]['path']    = substr( $file, 0, ( strrpos( $file, "\\" ) +1 ) );
				$FILE_LIST[$key]['name']    = substr( $file, ( strrpos( $file, "\\" ) +1 ) );
				$FILE_LIST[$key]['size']    = filesize( $file );
				$FILE_LIST[$key]['date']    = date('Y-m-d G:i:s', filemtime( $file ) );
				$set_limit++;

			}

		}
		if(!empty($FILE_LIST)){
			return $FILE_LIST;
		} else {
			die( "No files found!\n\n" );
		}

		// So....
		//
		// $source_folder = "c:\temp\my_videos";
		// $ext = "flv"; // flash video files
		// $sec = "7200"; // files older than 2 hours
		// $limit = 2;

	// 		Array
	// (
	//     [0] => Array
	//         (
	//             [path] => c:\temp\my_videos\
	//             [name] => fluffy_bunnies.flv
	//             [size] => 21160480
	//             [date] => 2007-10-30 16:48:05
	//         )
	//
	//     [1] => Array
	//         (
	//             [path] => c:\temp\my_videos\
	//             [name] => synergymx.com.flv
	//             [size] => 14522744
	//             [date] => 2007-10-25 15:34:45
	//         )
	}

	// TESTED : not working very well with UTF
	// http://www.scriptville.in/parse-csv-data-to-array/
	// Parse csv data to array
	// Here, we have function to parse csv file into array. This function return associative array of each line with all column name in array as key and csv data for each line as key values.
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

			throw new Exception( $file.' doesn`t exist or is not readable.' );

		}

		return $rows;
	}

	// http://www.php.net/manual/en/function.copy.php#103732
	// 'cp -R' written in PHP.
	public static function copy( $path, $dest, $ds = DIRECTORY_SEPARATOR )
	{
		if ( is_dir( $path ) ) {

			@ mkdir( $dest );
			$objects = scandir( $path );

			if ( sizeof( $objects ) > 0 ) {

				foreach ( $objects as $file ) {

					if ( $file == "." || $file == ".." )
						continue;

					if ( is_dir( $path.$ds.$file ) )
						self::copy( $path.$ds.$file, $dest.$ds.$file );
					else
						copy( $path.$ds.$file, $dest.$ds.$file );
				}
			}

			return TRUE;

		} else if ( is_file( $path ) ) {

			return copy( $path, $dest );

		}

		return FALSE;
	}

	// http://www.paulund.co.uk/html5-download-attribute
	function download( $file_path, $mime = 'application/octet-stream' )
	{
		if ( file_exists( $file_path ) ) {
			header( 'Content-Description: File Transfer' );
			header( 'Pragma: public' ); // required
			header( 'Expires: 0' );	// no cache
			header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
			header( 'Cache-Control: private', FALSE );
			header( 'Content-Type: '.$mime );
			header( 'Content-Length: '.filesize( $file_path ) );
			header( 'Content-Disposition: attachment; filename="'.basename( $file_path ).'"' );
			header( 'Content-Transfer-Encoding: binary' );
			header( 'Connection: close' );

			ob_clean();
			flush();

			readfile( $file_path );

			exit();
		}
	}

	// https://gist.github.com/chrisguitarguy/6096271
	// Remove BOM from a string
	public static function remove_bom( $filename )
	{
		$file = new \SplFileObject( $filename );

		// http://en.wikipedia.org/wiki/Byte_order_mark#UTF-8
		$bom = pack('CCC', 0xEF, 0xBB, 0xBF);

		$first = TRUE;
		foreach ($file as $line) {
			if ($first && substr($line, 0, 3) === $bom) {
				$line = substr($line, 3);
			}

			$first = FALSE;

			// your lines don't have a BOM, do you shit
		}
	}

	// http://christopherdavis.me/blog/extracting-gzip-files.html

} }
