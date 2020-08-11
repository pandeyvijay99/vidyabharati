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
    * Create droplet in digital ocean account
    *
    * @return void
    */
    public function createDroplet()
    {
        //need timetable id, $teacher detail to add in prosody & max number of people in the class
        $timetable_id = 1;
        $teacher='sumeet@lms.com'; //teacher detail to add in prosody
        $class_count = 60; 
        
        $listAllDroplets = $this->getAllDroplets();
        $dropletCount = count($listAllDroplets->droplets);
        $serverName= 'ezest'. ( $dropletCount + 1);
        
        $key = new RSA();
        $key->loadKey(file_get_contents('./application/helpers/demo-vidyabharti.ppk'));
        $dropletsData=[];
        $createNewServer = false;
        
        foreach ($listAllDroplets->droplets as $droplets) {
            $dropletIp = $droplets->networks->v4[0]->ip_address;
            if($dropletIp == '10.104.0.3' ||$dropletIp == '10.104.0.4' ||$dropletIp == '10.104.0.2'|| $dropletIp == '139.59.22.60') {
                continue;
            }
            $ssh = new SSH2($dropletIp);
            if (!$ssh->login('root', 'V!dy@Bh@rt!S3rv3r')) {
                exit('Login Failed for '. $dropletIp);
            }
            $freeMemory = $ssh->exec('free -m | awk \'NR==2{printf "%.2f%%\t\t", $4*100/$2 }\''); //get free memory details of droplet
            $freeMemory = substr($freeMemory, 0, -3);
            
            $availableMemory = $ssh->exec('df -h | awk \'FNR==2{printf "%s\t\t", $4}\''); //get available memory details of droplet
            $availableMemory =  substr($availableMemory, 0, -3);
            
            $cpuLoad = $ssh->exec('top -bn1 | grep load | awk \'{printf "%.2f%%\t\t\n", $(NF-2)}\''); //get cpu Load details of droplet
            $cpuLoad = substr($cpuLoad, 0, -4);
            
            $dropletsData[$dropletIp] = [
                'free_memory' => $freeMemory,
                'available_memory' => $availableMemory,
                'cpu_load' => $cpuLoad,
            ];
            
            if($freeMemory <= constant['free_memory_threshold'] && $availableMemory <= constant['available_memory_threshold'] && $cpuLoad >= constant['cpuLoad_threshold'] ) {
                // $createNewServer = true;
                echo('create server <br>');
            } else {
                $this->updateServerNameInTimetable($droplets->name);
                
                // redirect('/droplet'); 
                echo 'assign '.$droplets->name. 'at'. $dropletIp. '<br>';
            }
        }
    
        exit;
        if($createNewServer || $dropletCount == 0) {
            $createdDroplet = $this->createDropletonDigitalOcean($serverName, $teacher);
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
            $fullServerUrl = $serverName.'.'.constant['domainName'];
            $this->updateServerNameInTimetable($fullServerUrl);
        }
        
        redirect('/droplet'); 
    }

    public function destroy($dropletId, $dropletIp)
    {       
        $this->deleteDomainRecord($dropletIp);
        $this->deleteDroplet($dropletId);

        redirect('/droplet'); 
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

    private function updateServerNameInTimetable(string $serverName)
    {
        $this->load->model('timetable');
        $fullServerUrl = 'https://'. $serverName.'/';
        $this->timetable->updateServerName($fullServerUrl, 3);
    }
    /**
     * hit api that will create droplet on digital ocean
     *
     * @param string $serverName
     * @return void
     */
    private function createDropletonDigitalOcean(string $serverName,string $teacher)
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
                sed -i "s/server1/'.$serverName.'/g" /etc/jitsi/meet/server1.vidyabharatilms.com-config.js
                sed -i "s/server1/'.$serverName.'/g" /etc/prosody/conf.avail/server1.vidyabharatilms.com.cfg.lua
                sed -i "s/server1/'.$serverName.'/g" /etc/jitsi/jicofo/sip-communicator.properties
                prosodyctl register '.$teacher.' '.$serverName.'.vidyabharatilms.com password
                ',
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

    /**
     * Delete Domain record
     *
     * @param string $dropletIp
     * @return void
     */
    private function deleteDomainRecord(string $dropletIp)
    {
        //fetch all domain records from server
        $domainRecords = $this->client->get(constant['url'].'/v2/domains/'.constant['domainName'].'/records', [
            'headers' => $this->getHeaders(),
        ]);

        $domainRecordData = json_decode($domainRecords->getBody());

        //cycle through all available records and delete the requested domain record
        foreach ($domainRecordData->domain_records as $domainRecords) {
            if($domainRecords->data == $dropletIp) {
                $deleteDomainRecord = $this->client->delete(constant['url'].'/v2/domains/'.constant['domainName'].'/records/'.$domainRecords->id, [
                    'headers' => $this->getHeaders()
                ]);
            }
        }
    }

    /**
     * Drop droplet in digital ocean
     *
     * @param string $dropletId
     * @return void
     */
    private function deleteDroplet(string $dropletId)
    {
        $response = $this->client->delete(constant['url'].'/v2/droplets/'.$dropletId, [
            'headers' => $this->getHeaders()
        ]);

        return json_decode($response->getBody());
    }
}