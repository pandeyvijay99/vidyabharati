<?php
require 'vendor/autoload.php';
$config = include('./helpers/config.php');

$client = new GuzzleHttp\Client();
$dropletId = '196552739'; //need droplet id that we want to delete
$dropletIp = '157.245.98.135'; //need droplet ip that we want to delete

$domainRecords = $client->get($config['url'].'/v2/domains/'.$config['domainName'].'/records', [
    'headers' => [
        'Content-Type' => 'application/json',
        'Authorization'=> $config['token']
    ],
]);

$domainRecordData = json_decode($domainRecords->getBody());
foreach ($domainRecordData->domain_records as $domainRecords) {
    if($domainRecords->data == $dropletIp) {
        $deleteDomainRecord = $client->delete($config['url'].'/v2/domains/'.$config['domainName'].'/records/'.$domainRecords->id, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization'=> $config['token']
            ],
        ]);
    }
}



$response = $client->delete($config['url'].'/v2/droplets/'.$dropletId, [
    'headers' => [
        'Accept' => 'application/json', 
        'Content-Type' => 'application/json',
        'Authorization'=> $config['token']
    ],
]);

echo $response->getBody();