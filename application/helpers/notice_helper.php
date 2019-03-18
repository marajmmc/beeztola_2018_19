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

        $data[] = array
        (
            'label_1' => $CI->lang->line('LABEL_NOTICE_TYPE'),
            'value_1' => $result['notice_type'],
            'label_2' => $CI->lang->line('LABEL_EXPIRE_DAY'),
            'value_2' => Notice_helper::get_expire_day($result['date_publish'],$result['expire_time']).', <span class="bg-info">Remaining Days: '.Notice_helper::get_expire_day_by_current_time($result['expire_time']).'</span>'
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
        if($result['status_approve']==$CI->config->item('system_status_approved'))
        {
            $label_approve=$CI->config->item('system_status_approved');
        }
        else if($result['status_approve']==$CI->config->item('system_status_rejected'))
        {
            $label_approve='Reject';
        }
        else if($result['status_approve']==$CI->config->item('system_status_rollback'))
        {
            $label_approve=$CI->config->item('system_status_approved');
        }
        else
        {
            $label_approve=$CI->config->item('system_status_approved');
        }
        $data[] = array
        (
            'label_1' => $label_approve.' Status',
            'value_1' => $result['status_approve'],
            'label_2' => 'Revision ('.$label_approve.')',
            'value_2' => $result['revision_count_approved'],
        );
        if($result['status_approve']!=$CI->config->item('system_status_pending'))
        {
            $data[] = array
            (
                'label_1' => $label_approve.' By',
                'value_1' => $user_info[$result['user_approved']]['name'] . ' ( ' . $user_info[$result['user_approved']]['employee_id'] . ' )',
                'label_2' => $label_approve.' Time',
                'value_2' => System_helper::display_date_time($result['date_approved'])
            );
            $data[] = array
            (
                'label_1' => $label_approve.' Remarks',
                'value_1' => $result['remarks_approve']
            );
        }
        return $data;
    }
    public static function get_expire_day($publish_time,$expire_time)
    {
        $expire_day=0;
        if($publish_time && $expire_time)
        {
            if($expire_time>$publish_time)
            {
                $expire_day=ceil(($expire_time-$publish_time)/(3600*24));
            }
        }
        return $expire_day;
    }
    public static function get_expire_day_by_current_time($expire_time)
    {
        $expire_day=0;
        $time=time();
        if($expire_time>$time)
        {
            $expire_day=ceil(($expire_time-$time)/(3600*24));
        }
        return $expire_day;
    }
}
