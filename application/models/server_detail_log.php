<?php

class Server_Detail_Log extends CI_Model 
{

    /**
     * Update server_detail_log table field on server
     *
     * @param string $serverName
     * @param integer $timetableId
     * @return bool
     */
    public function logDetail(string $serverName, $timeTableData)
    {
        $this->load->database();
        $this->db->insert('server_detail_log', [
            'school_code' => $timeTableData->school_code,
            'timetable_id' => $timeTableData->id,
            'class_id' => $timeTableData->class_id,
            'server_path' => $serverName,
            'created_date' => date('Y-m-d'),
            'created_time' => date('H:i:s')
        ]);
    }

    /**
     * Update server_detail_log delete table field on server
     *
     * @param string $serverName
     * @param integer $timetableId
     * @return bool
     */
    public function logDeleteDetail(string $serverName, $timeTableData)
    {
        $this->db->from('server_detail_log'); 
        $this->db->where('timetable_id', $timeTableData->id);
        $this->db->where('class_id', $timeTableData->class_id);
        $this->db->where('school_code', $timeTableData->school_code);
        $this->db->where("created_date", date('Y-m-d'));

        $this->db->update('server_detail_log', [
            'deleted_date' => date('Y-m-d'),
            'deleted_time' => date('H:i:s')
        ]);
    }
}