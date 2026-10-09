<?php
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\Encoders\AutoEncoder;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\Encoders\GifEncoder;
use Intervention\Image\Encoders\PngEncoder;
use Lotgd\ErrorHandler;

global $imageInit;
$imageInit = 0;

/**
 * Output an image using the centralized image renderer.
 *
 * @param string $picLink Relative image path below modules/.
 * @param string $caption User-facing caption text.
 *
 * @return void
 */
function addimage($picLink, $caption = 'Original') {
	$picLink = "modules/".$picLink;
	// Module images are optional (some are not distributed); show nothing if one is missing.
	if (!file_exists($picLink)) return;
	$restrictSize = get_module_setting("restrict_size","addimages");
	$maxwidth = get_module_setting("maxwidth","addimages");
	$maxheight = get_module_setting("maxheight","addimages");
	if (is_module_active('addimages')) {
		if (get_module_pref('user_addimages','addimages')) {
			try {
				$imageLink = addimage_getimage($picLink, $caption,$restrictSize,$maxwidth,$maxheight);
			} catch (Exception $e) {
				// output an error image message to the user
				output("`n`c`bError while fetching picture %s`b`c`n`n",$picLink);
				return;
			}
			output_notl("`n`c".$imageLink."`c`n`n",true);
		}
	}
}

/**
 * Build image markup and return it to the caller.
 *
 * @param string $picname Local path or URL.
 * @param string $caption User-facing caption text.
 * @param bool $restrictsize Whether to generate/use a thumbnail.
 * @param int $maxwidth Max width for restricted output.
 * @param int $maxheight Max height for restricted output.
 * @param bool $aspectRatio Keep original image aspect ratio.
 *
 * @return string|null
 */
function addimage_getimage($picname, $caption = 'Original', $restrictsize = false, $maxwidth = 400, $maxheight = 400, $aspectRatio = true) {
	//wrapper for the raw function to implement error handling suppression here
	set_error_handler('count',0);
	try {
		// Yes, yucky, sue me, but this generates a lot of "type not supported messages that make no sense in decoder
		// No idea why, because the images work fine, resize, all good
		$image = @addimage_getimage_raw($picname, $caption, $restrictsize, $maxwidth, $maxheight, $aspectRatio);
	} catch (Exception $e) {
		// if there is an error with the image, leave it
		return;
	}
	ErrorHandler::register();
	return $image;
}

function addimage_getimage_raw($picname, $caption = 'Original', $restrictsize = false, $maxwidth = 400, $maxheight = 400, $aspectRatio = true) {
	global $imageInit;
	//only once
	if ($imageInit == 0) {
		$imageInit = 1;
		rawoutput("<script src=\"/modules/addimages/lightbox-plus-jquery.min.js\"></script>");
		rawoutput("<link href=\"/modules/addimages/css/lightbox.min.css\" rel=\"stylesheet\" />");
	}
	$imageTag = "";
	$caption = sanitize((string)$caption);
	if ($picname == "") return;
	// Resizing needs intervention/image (Composer) and the imagick extension.
	// Without them the original image is shown unscaled.
	$canResize = class_exists(ImageManager::class) && extension_loaded('imagick');
	if (!$canResize) $restrictsize = false;
	$manager = $canResize ? new ImageManager(new Driver()) : null; // or 'gd' if you prefer

	try {
		// Check if it is a local file or a remote one
		if (filter_var($picname, FILTER_VALIDATE_URL)) {
			// It is a remote file
			// Check if the file exists
			$check = addimage_checkremoteFile($picname);
			if (!$check['exists']) {
				return "<p>Error while fetching picture<br/>".$check['code']." -- ".$check['description']."!</p>";
			}
		} else {
			// It is a local file
			if (!file_exists($picname)) {
				return "<p>Error while fetching picture<br/>File does not exist!</p>";
			}
		}

		// Generate a unique filename for the thumbnail including a checksum
		$fileSize = @filesize($picname); // Get the size of the original file
		if ($fileSize === false) {
			// Assuming $picname could be a URL, fetch it to get the size
			$headers = @get_headers($picname, 1);
			if ($headers !== false && isset($headers['Content-Length'])) {
				$fileSize = $headers['Content-Length'];
			} else {
				$fileSize = 0; // Default size if unable to fetch
			}
		}
		$checksum = hash('crc32', $fileSize.$caption); // Generate checksum from file size and caption
		$filename = basename($picname);
		// In case of a URL with "?" in it, we only want the part before the "?"
		$filename = explode("?", $filename)[0];
		$extension = pathinfo($filename, PATHINFO_EXTENSION);
		$nameWithoutExtension = basename($filename, ".$extension");
		$thumbFilename = $nameWithoutExtension . '_' . $checksum . '.' . $extension;

		// Check if the thumbnail path exists and is writable
		$thumbPath = "/" . get_module_setting("thumbnail_path","addimages") . "/";
		if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $thumbPath)) {
			// Create the thumbnail directory if it doesn't exist
			if (!mkdir($_SERVER['DOCUMENT_ROOT'] . $thumbPath, 0755, true)) {
				// Unable to create the thumbnail directory
				return "<p>Unable to create the thumbnail directory: " . $thumbPath . "</p>";
			}
		} elseif (!is_writable($_SERVER['DOCUMENT_ROOT'] . $thumbPath)) {
			// The thumbnail directory is not writable
			return "<p>The thumbnail directory is not writable: " . $thumbPath . "</p>";
		}
		$thumbPath.= $thumbFilename;

		if ($restrictsize) {
			// Check if thumbnail already exists
			if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $thumbPath)) {
				// Whatever black magick this is, it will not read with a naked URL. Not even a local one from the server. so... go file_get_contents....
				$image = $manager->read(file_get_contents($picname));
				if ($aspectRatio == true) {
					// Resize the image to fit within the maximum dimensions if that option is set, maintaining aspect ratio
					$height = $image->height();
					$width = $image->width();
					$ratio = $height / $width;
					// Find out which way we need to scale
					if (($maxheight / $maxwidth) < $ratio) {
						// proportionally scale to width
						$maxwidth = null;
					} else {
						$maxheight = null;
					}
					// Could be more elegant, but works 
					$image->scaleDown($maxwidth, $maxheight, function ($constraint) {
							$constraint->aspectRatio();
							$constraint->upsize();
							});
				} else {
					// Resize the image to fit within the maximum dimensions if that option is set, not maintaining aspect ratio
					$image->resize($maxwidth, $maxheight);
				}
				set_error_handler('count',0);
				// Save the resized image as a thumbnail
				$image->save($_SERVER['DOCUMENT_ROOT'] . $thumbPath);
				ErrorHandler::register();
			} else {
				set_error_handler('count',0);
				$image = @$manager->read($_SERVER['DOCUMENT_ROOT'] . $thumbPath);
				ErrorHandler::register();
			}
			// Prepare image tag with a link for a pop-up to display the original image
			$imageTag = "<a href='".htmlentities(addslashes($picname))."' data-lightbox='image-1' data-title='".htmlentities($caption)."'><img src='".htmlentities($thumbPath)."' alt='".htmlentities($caption)."' height='{$image->height()}' width='{$image->width()}'></a>";

		} else {
			// Prepare image tag to display the original image, no restriction
			$imageTag = "<img src='".htmlentities(addslashes($picname))."' alt='".htmlentities($caption)."'></a>";
		}

		return $imageTag;
	} catch (Exception $e) {
		// Handle exception
		return "<p>Sorry, something went wrong processing the image: ".htmlentities($e->getMessage())."</p>";
	}
}

function addimage_checkRemoteFile($url) {
	// Check if the URL is relative and resolve it
	if (!filter_var($url, FILTER_VALIDATE_URL)) {
		$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
		$host = $_SERVER['HTTP_HOST'];
		$url = $protocol . $host . '/' . ltrim($url, '/');
	}

	// Initialize cURL session
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_NOBODY, true); // We don't need body
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // We want to fetch result as string
	curl_setopt($ch, CURLOPT_HEADER, true); // Include headers
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Follow redirects
	curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Set timeout

	// Execute cURL session
	$result = curl_exec($ch);

	// Prepare the response array
	$response = [
		'exists' => false,
		'code' => 0,
		'description' => 'An error occurred'
	];

	// Check if any error occurred
	if (!curl_errno($ch)) {
		$responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$response['code'] = $responseCode;
		if ($responseCode === 200) {
			// The file exists
			$response['exists'] = true;
			$response['description'] = 'File exists';
		} else {
			// The file does not exist or some other HTTP error
			$response['description'] = 'HTTP response code: ' . $responseCode;
		}
	} else {
		// Handle errors like timeouts and DNS resolution failures
		$error = curl_error($ch);
		$response['description'] = 'cURL error: ' . $error;
		switch (curl_errno($ch)) {
			case CURLE_OPERATION_TIMEOUTED:
				$response['code'] = CURLE_OPERATION_TIMEOUTED;
				$response['description'] = 'Operation timed out';
				break;
			case CURLE_COULDNT_RESOLVE_HOST:
				$response['code'] = CURLE_COULDNT_RESOLVE_HOST;
				$response['description'] = 'Could not resolve host';
				break;
		}
	}

	// Close cURL session
	curl_close($ch);

	return $response;
}

?>
