#!/usr/bin/php
<?php
$projectIdentifier='calcpvautonome';
$projectKey=rtrim(file_get_contents('.crowdin-key'));
$pot='./messages.pot';

if (!is_file($pot)) {
	exit('Le fichier '.$pot.' n\'existe pas');
}

ini_set('auto_detect_line_endings', 1);
ini_set('default_socket_timeout', 5); // socket timeout, just in case

$post_params = array();
$request_url = 'https://api.crowdin.com/api/project/'.$projectIdentifier.'/add-file?key='.$projectKey;
if(function_exists('curl_file_create')) {
  $post_params['files[messages.pot]'] = curl_file_create($pot);
} else {
  $post_params['files[messages.pot]'] = '@'.$pot;
}
/*
$post_params['type'] = 'gettext,source_phrase,translation';

if (function_exists('curl_file_create')) {
  $post_params['files[messages.pot]'] = curl_file_create('./messages.pot');
} else {
  $post_params['files[messages.pot]'] = '@./messages.pot';
}*/

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $request_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_params);
$result = curl_exec($ch);
echo $result;
curl_close($ch);


// Build
/*
$request_url = 'https://api.crowdin.com/api/project/'.$projectIdentifier.'/export?key='.$projectKey;
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $request_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
curl_close($ch);
// CHECK si le résultat dit <success status="built"/> ?
echo $result;
*/



?>
