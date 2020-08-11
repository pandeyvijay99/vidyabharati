<?php
require 'vendor/autoload.php';
$config = include('./helpers/config.php');

$client = new GuzzleHttp\Client();
$response = $client->get($config['url'].'/v2/images?private=true', [
    'headers' => [
        'Content-Type' => 'application/json',
        'Authorization'=> $config['token']
    ],
]);

//need to add droplet id in seesion as per the LMS standard's
echo $response->getBody();