<?php
/**
 * This is just an example of how a file could be processed from the
 * upload script. It should be tailored to your own requirements.
 */

// Only accept files with these extensions
$whitelist = array('tgz','gz');
$name      = null;
$error     = 'No file uploaded.';
$code      = 200;
$upload_dir = "../upload";
$tempdir = "";

# ------------------------------------------------------------------------
function create_temp_dir()
# ------------------------------------------------------------------------
# Create a temp directory to store the files in
{
    global $tempdir;

    $tempdir = tempnam(sys_get_temp_dir(),'');
    if( file_exists($tempdir) )
        unlink( $tempdir );
    mkdir( $tempdir );
    if( ! is_dir( $tempdir )) {
        $retval = array();
        $retval["error"] = "Could not create '$tempdir'";
        $retval["code"] = 400;
        print( json_encode($retval) );
        exit( 0 );
    }
}

if (isset($_FILES)) {
	if (isset($_FILES['file'])) {
		$tmp_name = $_FILES['file']['tmp_name'];
		$name     = basename($_FILES['file']['name']);
		$error    = $_FILES['file']['error'];
		
		if ($error === UPLOAD_ERR_OK) {
			$extension = pathinfo($name, PATHINFO_EXTENSION);

			if (!in_array($extension, $whitelist)) {
				$error = 'Invalid file type uploaded.';
                $code = 400;
			} else {
                if( ! is_dir( $upload_dir ) ) {
                    $error = 'Upload directory, \''.$upload_dir.
                        '\', does not exist. Cannot upload.';
                    $code = 400;
                } else {
                    move_uploaded_file($tmp_name, $upload_dir."/".$name);
                    create_temp_dir( );
                    $cmd = "tar xzf \"$upload_dir/$name\" -C $tempdir";
                    exec( $cmd . ' >/dev/stdout 2>&1', $output, $exit_status );
                    if( $exit_status != 0 )
                    {
                        $code = 400;
                        $error = 'There was a problem unpacking the package'.
                                 " into '$tempdir'.";
                    } else {
                        $d = dir( $tempdir );
                        while (false !== ($entry = $d->read())) {
                           if( $entry == "." || $entry == ".." ) continue;
                           $tables[]=$entry;
                        }
                        $d->close();
                        $code = 200;
                    }
                }
			}
		}
	}
}

echo json_encode(array(
    'code'  => $code,
	'name'  => $name,
    'unpack_dir' => $tempdir,
    'tables' => $tables,
	'error' => $error,
));
die();
?>
