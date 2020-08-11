<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use phpseclib\Net\SSH2;
use phpseclib\Crypt\RSA;

class Droplet extends MY_Controller 
{
    function __construct()
    {
        parent::__construct();
        $this->load->helper('form');
        $this->load->helper('url');
        $this->load->model('timetable');
        $this->load->model('server_details');
        $this->load->model('server_detail_log');
        $this->load->helper('date');
        $this->client = new GuzzleHttp\Client();
    }

    /**
     * Client class object
     *
     * @var client Client
     */
    protected $client;

    public function index()
    {
        $data['dropletData'] =  $this->getAllDroplets();
        // print("<pre>".print_r(count($data['dropletData']->droplets),true)."</pre>"); exit;

        $this->load->view('droplet_index', $data);
    }

    /**
     * Time table CRON
     *
     * @return void
     */
    public function timetableCron()
    {
        $format = "%H:%i";
        $currentTime = mdate($format);
        $fullTimeTable = $this->timetable->getAllTimetableData();
        foreach ($fullTimeTable as $data) {
            $datetime = (strtotime($data->start_time) - strtotime($currentTime)) / 60; //subtract class time with current time in miuntes
            if(0 <= $datetime && $datetime <= 30) { //if time left is less then 30 min
                if($data->server_updated_at !== date('Y-m-d')) { // checking if record is already updated or not
                    $this->assignServerToClass($data);
                } 
            } 
        }
    }

    /**
    * Create droplet in digital ocean account
    *
    * @return void
    */
    public function assignServerToClass($timeTableData)
    {
        $listAllDroplets = $this->getServerNamesFromDb();
        $dropletCount = count($listAllDroplets);
        $serverName= 'ezest'. ( $dropletCount + 1);
        
        $dropletsData=[];
        $createNewServer = false;
        
        foreach ($listAllDroplets as $droplets) {
            $createNewServer = false;
            if($droplets->name == '10.104.0.2') { // if in case any droplets that need to ommit
                continue;
            }

            if(($droplets->participant_count + $timeTableData->participant_count + constant['buffer_participant_count']) <= constant['server_participants_limit']) {
                $this->updateServerNameInDatabase($droplets->name,$timeTableData);
                $createNewServer = false;
                return;
            } else {
                $createNewServer = true;
            }
        }

        if($createNewServer || $dropletCount == 0) {
            $createdDroplet = $this->createDropletonDigitalOcean($serverName, $timeTableData);
            $dropletId = $createdDroplet->droplet->id;
            
            sleep(20); // sleep for 30 seconds
            $dropletData = $this->fetchDropletDetail($dropletId);
            
            // check if the droplet is ready or not
            while (empty($dropletData->droplet->networks->v4)) {
                sleep(10);
                $dropletData = $this->fetchDropletDetail($dropletId);
            }
            $createdDropletIpAddress = $dropletData->droplet->networks->v4[0]->ip_address;
            $addDomainRecord = $this->addDomainRecord($serverName, $createdDropletIpAddress);
            $fullServerUrl = 'https://'.$serverName.'.'.constant['domainName'].'/';

            $this->updateServerNameInDatabase($fullServerUrl, $timeTableData, $createdDropletIpAddress, $createdDroplet, $addDomainRecord);
        }
        
        return;
    }
    
    /**
     * Get headers for the Guzzle client
     *
     * @return void
     */
    private function getHeaders()
    {
        return [
            'Content-Type' => 'application/json',
            'Authorization'=> constant['token']
        ];
    }

    /**
     * get all the droplets of the account
     */
    public function getAllDroplets()
    {
        $dropletData =  $this->client->get(constant['url'].'/v2/droplets', [
            'headers' => $this->getHeaders()
            ]
        );
        // print("<pre>".print_r(json_decode($dropletData->getBody()),true)."</pre>"); exit;
        return json_decode($dropletData->getBody());
    }

    /**
     * get Server name form server_details table
     *
     * @return array
     */
    public function getServerNamesFromDb(): array
    {
        return $this->server_details->getAllServerDetailsData();
    }

    /**
     * update server in database
     *
     * @param string $serverName
     * @param [type] $timeTableData
     * @return void
     */
    private function updateServerNameInDatabase(string $serverName, $timeTableData, $createdDropletIpAddress = null, $createdDroplet = null, $domainRecord =null)
    {
        $this->timetable->updateServerName($serverName, $timeTableData->id);
        $this->server_details->updateServerDetail($serverName, $timeTableData,$createdDropletIpAddress, $createdDroplet, $domainRecord);
        $this->server_detail_log->logDetail($serverName, $timeTableData);
    }

    /**
     * hit api that will create droplet on digital ocean
     *
     * @param string $serverName
     * @return void
     */
    private function createDropletonDigitalOcean(string $serverName, $timeTableData)
    {
        $createdDroplet = $this->client->post(constant['url'].'/v2/droplets', [
            'headers' => $this->getHeaders(),
            'body' => json_encode([
                "name"=> $serverName,
                "region"=> constant['region'],
                "size"=> constant['size'],
                "image"=> constant['image'],
                "ssh_keys" => [
                    constant['ssh_keys']
                ],
                "backups"=> false,
                "ipv6"=> true,
                "user_data"=> '#!/bin/bash
                sed -i "s/server2/'.$serverName.'/g" /etc/jitsi/meet/server2.vidyabharatilms.com-config.js
                sed -i "s/server2/'.$serverName.'/g" /etc/prosody/conf.avail/server2.vidyabharatilms.com.cfg.lua
                sed -i "s/server2/'.$serverName.'/g" /etc/jitsi/jicofo/sip-communicator.properties
                sed -i "s/server2/'.$serverName.'/g" /etc/jitsi/meet/server2.vidyabharatilms.com-config.js
                prosodyctl register '.$timeTableData->teacher_username.' '.$serverName.'.vidyabharatilms.com '. $timeTableData->teacher_password.'',
                "private_networking"=> null,
                "volumes"=> null,
                "tags"=> ["web"]
            ])
        ]);

        return json_decode($createdDroplet->getBody());
    }

    /**
     * fetch the droplet data as per the id
     *
     * @param integer $dropletId
     * @return void
     */
    private function fetchDropletDetail(int $dropletId)
    {
        $dropletData =  $this->client->get(constant['url'].'/v2/droplets/'.$dropletId, [
            'headers' => $this->getHeaders()
            ]
        );

        return json_decode($dropletData->getBody());
    }

    /**
     * Add the domain record as per the droplet created
     *
     * @param string $serverName
     * @param string $createdDropletIpAddress
     * @return void
     */
    private function addDomainRecord(string $serverName, string $createdDropletIpAddress)
    {
        $addDomainRecord = $this->client->post(constant['url'].'/v2/domains/'.constant['domainName'].'/records', [
            'headers' => $this->getHeaders(),
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
        
            return json_decode($addDomainRecord->getBody());
    }
}