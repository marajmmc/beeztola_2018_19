<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notice_helper
{
    public static function get_basic_info($result)
    {
        $CI = & get_instance();
        //--------- System User Info ------------
        $user_ids=array();
        $user_ids[$result['user_created']]=$result['user_created'];
        if($result['user_updated']>0)
        {
            $user_ids[$result['user_updated']]=$result['user_updated'];
        }
        if($result['user_forwarded']>0)
        {
            $user_ids[$result['user_forwarded']]=$result['user_forwarded'];
        }
        if($result['user_approved']>0)
        {
            $user_ids[$result['user_approved']]=$result['user_approved'];
        }
        $user_info = System_helper::get_users_info($user_ids);

        //---------------- Basic Info ----------------
        $data = array();
        $data[] = array
        (
            'label_1' => $CI->lang->line('LABEL_TITLE'),
            'value_1' => $result['title']
        );
        $data[] = array
        (
            'label_1' => $CI->lang->line('LABEL_NOTICE_ID'),
            'value_1' => $result['id'],
            'label_2' => $CI->lang->line('LABEL_DATE_PUBLISH'),
            'value_2' => System_helper::display_date($result['date_publish'])
        );

        $time=time();
        if($result['expire_time']>$time)
        {
            $result['expire_day']=ceil(($result['expire_time']-$time)/(3600*24));
        }
        else
        {
            $result['expire_day']=0;
        }
        $data[] = array
        (
            'label_1' => $CI->lang->line('LABEL_NOTICE_TYPE'),
            'value_1' => $result['notice_type'],
            'label_2' => $CI->lang->line('LABEL_EXPIRE_DAY'),
            'value_2' => $result['expire_day']
        );
        $data[] = array
        (
            'label_1' => $CI->lang->line('LABEL_ORDER'),
            'value_1' => $result['ordering'],
            'label_2' => 'Revision (Edit)',
            'value_2' => $result['revision_count']
        );
        $data[] = array
        (
            'label_1' => 'Created By',
            'value_1' => $user_info[$result['user_created']]['name'] . ' ( ' . $user_info[$result['user_created']]['employee_id'] . ' )',
            'label_2' => 'Created Time',
            'value_2' => System_helper::display_date_time($result['date_created'])
        );
        if($result['user_updated']>0)
        {
            $inactive_update_by='Updated By';
            $inactive_update_time='Updated Time';
            if($result['status']==$CI->config->item('system_status_inactive'))
            {
                $inactive_update_by='In-Active By';
                $inactive_update_time='In-Active Time';
            }
            $data[] = array(
                'label_1' => $inactive_update_by,
                'value_1' => $user_info[$result['user_updated']]['name'] . ' ( ' . $user_info[$result['user_updated']]['employee_id'] . ' )',
                'label_2' => $inactive_update_time,
                'value_2' => System_helper::display_date_time($result['date_updated'])
            );
        }
        $data[] = array
        (
            'label_1' => $CI->lang->line('LABEL_STATUS_FORWARD'),
            'value_1' => $result['status_forward'],
            'label_2' => 'Revision (Forward)',
            'value_2' => $result['revision_count_forwarded'],
        );
        if($result['status_forward']==$CI->config->item('system_status_forwarded'))
        {
            $data[] = array
            (
                'label_1' => 'Forwarded By',
                'value_1' => $user_info[$result['user_forwarded']]['name'] . ' ( ' . $user_info[$result['user_forwarded']]['employee_id'] . ' )',
                'label_2' => 'Forwarded Time',
                'value_2' => System_helper::display_date_time($result['date_forwarded'])
            );
        }
        $data[] = array
        (
            'label_1' => $CI->lang->line('LABEL_STATUS_APPROVE'),
            'value_1' => $result['status_approve'],
            'label_2' => 'Revision (Approve)',
            'value_2' => $result['revision_count_approved'],
        );
        if($result['status_approve']==$CI->config->item('system_status_approved'))
        {
            $data[] = array
            (
                'label_1' => 'Approved By',
                'value_1' => $user_info[$result['user_approved']]['name'] . ' ( ' . $user_info[$result['user_approved']]['employee_id'] . ' )',
                'label_2' => 'Approved Time',
                'value_2' => System_helper::display_date_time($result['date_approved'])
            );
        }

        /*if($result['status']==$CI->config->item('system_status_inactive'))
        {
            $data[] = array(
                'label_1' => 'i By',
                'value_1' => $user_info[$result['user_updated']]['name'] . ' ( ' . $user_info[$result['user_updated']]['employee_id'] . ' )',
                'label_2' => 'Created Time',
                'value_2' => System_helper::display_date_time($result['date_updated'])
            );
        }
        else
        {

        }*/

        /*$data[] = array(
            'label_1' => $CI->lang->line('LABEL_FARMER_NAME'),
            'value_1' => $result['lead_farmer_name'],
            'label_2' => 'Farmer Type',
            'value_2' => ($result['lead_farmer_id'] > 0) ? $CI->lang->line('LABEL_LEAD_FARMER_NAME') : $CI->lang->line('LABEL_OTHER_FARMER_NAME')
        );
        $data[] = array(
            'label_1' => $CI->lang->line('LABEL_CROP_NAME'),
            'value_1' => $result['crop_name'],
            'label_2' => $CI->lang->line('LABEL_CROP_TYPE'),
            'value_2' => $result['crop_type_name']
        );
        $data[] = array(
            'label_1' => $CI->lang->line('LABEL_VARIETY1_NAME'),
            'value_1' => $result['variety1_name'],
            'label_2' => $CI->lang->line('LABEL_VARIETY2_NAME'),
            'value_2' => ($result['variety2_name']) ? $result['variety2_name'] : '<i style="font-weight:normal">- No Variety Selected -</i>'
        );
        if (!($result['variety2_id'] > 0))
        {
            $data[] = array(
                'label_1' => $CI->lang->line('LABEL_DATE_SOWING_VARIETY1'),
                'value_1' => System_helper::display_date($result['date_sowing_variety1'])
            );
            $data[] = array(
                'label_1' => $CI->lang->line('LABEL_DATE_TRANSPLANTING_VARIETY1'),
                'value_1' => ($result['date_transplanting_variety1']) ? System_helper::display_date($result['date_transplanting_variety1']) : '<i style="font-weight:normal">- No Date Selected -</i>'
            );
        }
        else
        {
            $data[] = array(
                'label_1' => $CI->lang->line('LABEL_DATE_SOWING_VARIETY1'),
                'value_1' => System_helper::display_date($result['date_sowing_variety1']),
                'label_2' => $CI->lang->line('LABEL_DATE_SOWING_VARIETY2'),
                'value_2' => ($result['date_sowing_variety2']) ? System_helper::display_date($result['date_sowing_variety2']) : '<i style="font-weight:normal">- No Date Selected -</i>'
            );
            $data[] = array(
                'label_1' => $CI->lang->line('LABEL_DATE_TRANSPLANTING_VARIETY1'),
                'value_1' => ($result['date_transplanting_variety1']) ? System_helper::display_date($result['date_transplanting_variety1']) : '<i style="font-weight:normal">- No Date Selected -</i>',
                'label_2' => $CI->lang->line('LABEL_DATE_TRANSPLANTING_VARIETY2'),
                'value_2' => ($result['date_transplanting_variety2']) ? System_helper::display_date($result['date_transplanting_variety2']) : '<i style="font-weight:normal">- No Date Selected -</i>'
            );
        }
        $data[] = array(
            'label_1' => $CI->lang->line('LABEL_DATE_EXPECTED_EVALUATION'),
            'value_1' => System_helper::display_date($result['date_expected_evaluation']),
            'label_2' => $CI->lang->line('LABEL_DATE_ACTUAL_EVALUATION'),
            'value_2' => ($result['date_actual_evaluation']) ? System_helper::display_date($result['date_actual_evaluation']) : '<i style="font-weight:normal;color:#FF0000">- No Date Selected -</i>'
        );
        $data[] = array(
            'label_1' => 'Created By',
            'value_1' => $user_info[$result['user_created']]['name'] . ' ( ' . $user_info[$result['user_created']]['employee_id'] . ' )',
            'label_2' => 'Created Time',
            'value_2' => System_helper::display_date_time($result['date_created'])
        );*/
        return $data;
    }
}
