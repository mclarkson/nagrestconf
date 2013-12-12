<?php
/**
 * This is just an example of how a file could be processed from the
 * upload script. It should be tailored to your own requirements.
 */

// Only accept files with these extensions
$whitelist = array('csv');
$name      = null;
$error     = 'No file uploaded.';
$code      = 200;

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
				move_uploaded_file($tmp_name, "upload/".$name);
                $code = 200;
			}
		}
	}
}

echo json_encode(array(
    'code'  => $code,
	'name'  => $name,
	'error' => $error,
));
die();
?>
