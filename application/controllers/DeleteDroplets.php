<?php
defined('BASEPATH') OR exit('No direct script access allowed');
date_default_timezone_set("Asia/Kolkata");

class DeleteDroplets extends CI_Controller 
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('timetable');
        $this->load->model('server_details');
        $this->load->model('server_detail_log');
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
        $fullTimeToday = $this->timetable->getAllTimetableData();
		
        $currentTime = date('H:i'); 
        $currentDate = date('Y-m-d');
		foreach ($fullTimeToday as $timetable){
            $serverDtls = $this->server_details->getServerDetails($timetable->server_path);
          
			if($serverDtls)
			{
				if($serverDtls->participant_count == 0)
				{
					$this->deleteDomainRecord($serverDtls->droplet_IP, $serverDtls->domain_record_id);
                    $this->deleteDroplet($serverDtls->droplet_ID);
                    $this->timetable->updateServerDeletedAt($timetable->server_path);
                    $this->server_details->deleteServerEntry($timetable->server_path);
					continue;
				}
            }
     
			if(strtotime($currentTime) > strtotime($timetable->end_time) && $timetable->server_deleted_at != $currentDate)// checking greater or equal and if record is already deleted or not
			{
                $serverDtls = $this->server_details->getServerDetails($timetable->server_path);
  
				if($serverDtls){
                    $totalClassParticipant = (int)$timetable->participant_count + (int)constant['buffer_participant_count'];
                    $newParticipantCount = (int)$serverDtls->participant_count - (int)$totalClassParticipant;
                    $newParticipantCount  = ($newParticipantCount < 0) ? 0 : $newParticipantCount;
                        
                    $this->server_details->updateServerParticipantCount($timetable->server_path, $newParticipantCount);
				}
				$serverDtls = $this->server_details->getServerDetails($timetable->server_path);
				if($serverDtls){
                    if($serverDtls->participant_count == 0)
                        {
                            $this->deleteDomainRecord($serverDtls->droplet_IP, $serverDtls->domain_record_id);
                            $this->deleteDroplet($serverDtls->droplet_ID);
                            $this->timetable->updateServerDeletedAt($timetable->server_path);
                            $this->server_details->deleteServerEntry($timetable->server_path);
                            $this->server_detail_log->logDeleteDetail($timetable->server_path, $timetable);
                            continue;
                        }
				}
			}

		}
		
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
     * Delete Domain record
     *
     * @param string $dropletIp
     * @return void
     */
    private function deleteDomainRecord(string $dropletIp, string $domain_record_id)
    {
        //fetch all domain records from server
        $domainRecordsDtls = $this->client->get(constant['url'].'/v2/domains/'.constant['domainName'].'/records/'.$domain_record_id, [
            'headers' => $this->getHeaders(),
        ]);

        $domainRecordDtls = json_decode($domainRecordsDtls->getBody());
            if($domainRecordDtls->domain_record->data == $dropletIp) {
                $deleteDomainRecord = $this->client->delete(constant['url'].'/v2/domains/'.constant['domainName'].'/records/'.$domain_record_id, [
                    'headers' => $this->getHeaders()
                ]);
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