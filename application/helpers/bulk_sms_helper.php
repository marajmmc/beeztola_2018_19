<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Bulk_sms_helper
{
    public static function send_sms($contacts,$msg,$type='text')
    {
        $CI =& get_instance();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $CI->config->item('system_bulk_sms_api_url'));

        curl_setopt($ch, CURLOPT_POST,TRUE);
        $data = array();
        $data['api_key']=$CI->config->item('system_bulk_sms_api_key');
        $data['senderid']=$CI->config->item('system_bulk_sms_api_senderid');
        $data['type']=$type;
        $data['contacts']=$contacts;
        $data['msg']=$msg;
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,TRUE);
        $response = curl_exec($ch);
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $data=array();
        $data['contacts']=$contacts;
        $data['msg']=$msg;
        $data['status_http']=$http_status;
        $data['status_sms']=$response;
        $data['date_string']=System_helper::display_date_time(time());
        $CI->db->insert($CI->config->item('table_system_history_bulk_sms'), $data);
        return array('status_http'=>$http_status,'status_sms'=>$response);
    }
}
