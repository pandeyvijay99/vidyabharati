<?php
require 'vendor/autoload.php';
use phpseclib\Net\SFTP;
use phpseclib\Crypt\RSA;

$key = new RSA();
$key->loadKey(file_get_contents('./helpers/rahul-ssh-key.ppk'));

$ssh = new SFTP('157.245.104.153');
if (!$ssh->login('root', $key)) {
    exit('Login Failed');
}
// ssh2_scp_recv($ssh, '/etc/jitsi/meet/server-name.conf.js', './temp/server-name.conf.js');
// $freeMemory = $ssh->exec('free -m | awk \'NR==2{printf "%.2f%%\t\t", $4*100/$2 }\'');
// $availableMemory = $ssh->exec('df -h | awk \'FNR==2{printf "%s\t\t", $4}\'');
// $cpuLoad = $ssh->exec('top -bn1 | grep load | awk \'{printf "%.2f%%\t\t\n", $(NF-2)}\'');

// echo $freeMemory.'<br>';
// echo $availableMemory.'<br>';
// echo $cpuLoad;

echo $ssh->get('/etc/prosody/conf.avail/server2.vidyabharatilms.com.cfg.lua', './temp/prosody/server2.vidyabharatilms.com.cfg.lua');

$path_to_file = './temp/prosody/server2.vidyabharatilms.com.cfg.lua';
$file_contents = file_get_contents($path_to_file);
$file_contents = str_replace("server2","server1",$file_contents);
file_put_contents($path_to_file,$file_contents);
echo 'done';