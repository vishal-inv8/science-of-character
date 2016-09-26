<?php

$token = $_GET["token"];
$fields = 'id,name,first_name,last_name,email,friends';
$url = 'https://graph.facebook.com/me/?fields=' . $fields . '&access_token='.$token;

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

$result = json_decode($result, true);

echo "<pre>";
print_r($result);
echo json_encode($result);
