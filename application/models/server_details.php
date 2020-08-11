<?php

class Server_Details extends CI_Model 
{
    public function getAllServerDetailsData()
    {
        $this->load->database();
        return $this->db->get('server_details')->result();
    }
	
	public function getServerDetails($serverPath)
	{	
		$query  = $this->db->select('*')
                   ->from('server_details')
                   ->where('name', $serverPath) 
                   ->get();
		$result =  $query->result();
		
		if(!empty($result))
		return $result[0];
	    else
		return 0;
	}
	
	public function updateServerParticipantCount($name, $participantCnt)
	{	
		$this->db->set('participant_count', $participantCnt);
		$this->db->where('name', $name);
		$this->db->update('server_details');
	}


    public function updateServerDetail($fullServerUrl, $timeTableData, $createdDropletIpAddress=null, $createdDroplet = null, $domainRecord =null)
    {
        $this->load->database();
        $this->db->where('name', $fullServerUrl);
        $final = ($this->db->get('server_details')->result()); 
        if (count($final) == 1 && is_null($createdDroplet)) { //record exits & droplet is not created, so add the participant_count
            $this->db->where('name', $fullServerUrl);
            $this->db->update('server_details', [
                'participant_count' => $final[0]->participant_count + $timeTableData->participant_count + constant['buffer_participant_count']
                ]);
        } else { // if entry not present for that server name, create new one
            $this->db->insert('server_details', [
                'name' => $fullServerUrl,
                'participant_count' => $timeTableData->participant_count + constant['buffer_participant_count'],
                'droplet_IP' => $createdDropletIpAddress,
                'droplet_ID' => $createdDroplet->droplet->id,
                'domain_record_id' => $domainRecord->domain_record->id,
            ]);
        }
    }

    public function deleteServerEntry($name)
    {
        $this->db->where('name', $name);
        $this->db->delete('server_details');
    }
}