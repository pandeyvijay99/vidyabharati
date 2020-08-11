<?php
require 'vendor/autoload.php';
$config = include('./helpers/config.php');

$serverName= 'ezest1';
$client = new GuzzleHttp\Client();

$createDropletApi = $client->post($config['url'].'/v2/droplets', [
    'headers' => [
        'Content-Type' => 'application/json',
        'Authorization'=> $config['token']
    ],
    'body' => json_encode([
        "name"=> $serverName,
        "region"=> $config['region'],
        "size"=> $config['size'],
        "image"=> $config['image'],
        "ssh_keys" => [
            $config['ssh_keys']
        ],
        "backups"=> false,
        "ipv6"=> true,
        "user_data"=> '#!/bin/bash
        sed -i "s/server2/'.$serverName.'/g" /etc/jitsi/meet/server2.vidyabharatilms.com-config.js
        sed -i "s/server2/'.$serverName.'/g" /etc/prosody/conf.avail/server2.vidyabharatilms.com.cfg.lua
        sed -i "s/server2/'.$serverName.'/g" /etc/jitsi/jicofo/sip-communicator.properties
        ',
        "private_networking"=> null,
        "volumes"=> null,
        "tags"=> [
            "web"
        ]
    ])
]);

$data = json_decode($createDropletApi->getBody());
$dropletId = $data->droplet->id;

// sleep for 30 seconds
sleep(20);
/**
 * Fetch Droplet Details
 */
$fetchDropletDetail = $client->get($config['url'].'/v2/droplets/'.$dropletId, [
    'headers' => [
        'Content-Type' => 'application/json',
        'Authorization'=> $config['token']
    ],
]);
$dropletData = json_decode($fetchDropletDetail->getBody());

// check if the droplet is ready or not
while (empty($dropletData->droplet->networks->v4)) {
    sleep(10);
    $fetchDropletDetail = $client->get($config['url'].'/v2/droplets/'.$dropletId, [
        'headers' => [
            'Content-Type' => 'application/json',
            'Authorization'=> $config['token']
        ],
    ]);
    $dropletData = json_decode($fetchDropletDetail->getBody());
}

$createdDropletIpAddress = $dropletData->droplet->networks->v4[0]->ip_address;
/**
 * Create A record for the domain
 */
$addDomainRecord = $client->post($config['url'].'/v2/domains/'.$config['domainName'].'/records', [
    'headers' => [
        'Content-Type' => 'application/json',
        'Authorization'=> $config['token']
    ],
    'body' => json_encode([
        "type" => "A",
        "name" => $serverName,
        "data" => $createdDropletIpAddress,
        "priority" => null,
        "port" => null,
        "ttl" => 1800,
        "weight" => null,
        "flags" => null,
        "tag" => null
    ])
]);

print("<pre>".print_r($addDomainRecord,true)."</pre>");
//need to add droplet id in seesion as per the LMS standard's
// echo $response->getBody();