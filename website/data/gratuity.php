<?php 
require 'config_files.php';
require 'int_debug.php';
require 'gratuity_get.php';
require 'gratuity_post.php';

$response = '';

$link = @mysqli_connect($DB_SERVER, $DB_USER, $DB_PASS, $DB_DATABASE_NAME);
if (!$link) {
	// can't open DB so return an error
	// this line only works on PHP > 5.4.0, which not everyone seems to have.
	//   http_response_code(500);
	// this works on PHP > 4.3 (or so)
	$errData['status'] = 500;
	$errData['message'] = 'Can\'t connect to database server: '.$DB_SERVER;
	$response['error'] = $errData;
	$response['debug']['module'] = __FILE__;
	$response['debug']['user'] = $DB_USER;
	$response['debug']['database'] = $DB_DATABASE_NAME;
} else {
	$debugState = int_GetDebug($link, 'gratuity', '');
	$postData = '';
	if ($_SERVER['REQUEST_METHOD'] == 'GET') {
		// get the request data
		// if the data is not in the the post form, try the query string
		if (empty($postData)) {
			$postData = $_GET;
		} 
		if (empty($postData)) {
			$postData = $_POST;
		}
		$response = _gratuity_get($link, $postData);
	} else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		// get the request data
		if (!empty($HTTP_RAW_POST_DATA)) {
			$postData = json_decode($HTTP_RAW_POST_DATA,true);
		}		
		// if the data is not in the raw post data, try the post form
		if (empty($postData)) {
			$postData = $_POST;
		}
		if (empty($postData)) {
			$postData = $_GET;
		} 
		$response = _gratuity_post($link, $postData);
	} else {
		// method not supported
		$errData['status'] = 405;
		$errData['message'] = 'HTTP method not allowed.';
		$response['error'] = $errData;
		if ($debugState) {
			$response['debug']['module'] = __FILE__;
		}
	}
	mysqli_close($link);	
}

require 'format_response.php';
print ($fnResponse);
?>