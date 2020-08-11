<?php

class Timetable extends CI_Model 
{

    /**
     * Update is_active field on server
     *
     * @param string $serverName
     * @param integer $timetableId
     * @return bool
     */
    public function updateServerName(string $serverName, int $timetableId)
    {
        $this->load->database();
        $this->db->where('id', $timetableId);
        $this->db->update('timetables', [
            'server_path' => $serverName,
            'server_updated_at' => date('Y-m-d')
        ]);
    }

    /**
     * Get all time table of present dat
     */
    public function getAllTimetableData()
    {
        $today = date("l");// Get current day of week
        $this->load->database();
        $this->db->select('*');
        $this->db->from('timetables'); 
        $this->db->where('day_name',$today);
        $query = $this->db->get();
        return $query->result(); 
    }
   
    /**
     * Update server deleted at once server get deleted
     */
    public function updateServerDeletedAt(string $server_path)
    {
        $this->db->where('server_path', $server_path);
        $this->db->update('timetables', [
            'server_deleted_at' => date('Y-m-d')
        ]);
    }
}