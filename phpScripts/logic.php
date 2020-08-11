<?php
require 'vendor/autoload.php';
use phpseclib\Net\SSH2;
use phpseclib\Crypt\RSA;

$config = include('./helpers/config.php');
$key = new RSA();
$key->loadKey(file_get_contents('./helpers/rahul-ssh-key.ppk'));

/**List all Droplets */
$client = new GuzzleHttp\Client();
$listAllDroplets = $client->get($config['url'].'/v2/droplets', [
    'headers' => [
        'Content-Type' => 'application/json',
        'Authorization'=> $config['token']
    ],
    ]);
    
    $listAllDropletsString = json_decode($listAllDroplets->getBody());
    $dropletsData=[];
    foreach ($listAllDropletsString->droplets as $droplets) {
        $dropletIp = $droplets->networks->v4[0]->ip_address;
        $ssh = new SSH2($dropletIp);
        if (!$ssh->login('root', $key)) {
            exit('Login Failed');
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
        
        if($freeMemory <= $config['free_memory_threshold'] && $availableMemory <= $config['available_memory_threshold'] && $cpuLoad >= $config['cpuLoad_threshold'] ) {
            echo 'creating new server and assigning class to that server'.'<br>';
        } else {
            echo 'assiging class to '. $dropletIp.' server'.'<br>';
        }
    }
    
    print("<pre>".print_r($dropletsData,true)."</pre>");