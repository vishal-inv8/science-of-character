<?php
/*
$token = isset($_GET["access_token"])?$_GET["access_token"]:"1805596049727665%7Cfa1e99a32e9afd18c10c51724beb5726";
$fields = isset($_GET["fields"])?$_GET["fields"]:"message,link,permalink_url,created_time,type,name,id,comments.limit(0).summary(true),shares,likes.limit(0).summary(true),reactions.limit(0).summary(true)";
$limit = isset($_GET["limit"])?$_GET["limit"]:"100";
//$url = 'https://graph.facebook.com/me/?fields=' . $fields . '&access_token='.$token;
//$url = "https://graph.facebook.com/550729508456683/posts/?access_token='.$token.'&fields='.$fields.'&limit=".$limit;
*/


$url = 'https://graph.facebook.com/550729508456683/posts/?access_token=1805596049727665%7Cfa1e99a32e9afd18c10c51724beb5726&fields=message,link,permalink_url,created_time,type,name,id,comments.limit(0).summary(true),shares,likes.limit(0).summary(true),reactions.limit(0).summary(true)&limit=100';

//  Initiate curl
$ch = curl_init();

// Enable SSL verification
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $enable_ssl);

// Will return the response, if false it print the response
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Set the url
curl_setopt($ch, CURLOPT_URL, $url);

// Execute
$result = curl_exec($ch);

// Closing
curl_close($ch);
echo "<pre>";
//print_r($result);
print_r(json_decode($result,true));

/*
$result = json_decode($result, true);
//echo "<pre>";
echo json_encode($result);
*/

