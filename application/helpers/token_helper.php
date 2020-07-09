<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Token_helper
{
    public static function get_token($token)
    {
        $CI =& get_instance();
        $user = User_helper::get_user();
        $result=Query_helper::get_info(TABLE_SYSTEM_TOKEN,array('id, token'),array('user_id='.$user->id),1);
        if($result)
        {
            if($result['token']==$token)
            {
                return array('status'=>true, 'id'=>$result['id']);
            }
            else
            {
                return array('status'=>false, 'id'=>$result['id']);
            }
        }

        return array('status'=>false, 'id'=>0);
    }
    public static function update_token($id, $token)
    {
        $CI =& get_instance();
        $user = User_helper::get_user();
        if($id>0)
        {
            $data=array();
            $data['token']=$token;
            $data['date_updated']=time();
            $CI->db->set('revision_count', 'revision_count+1', FALSE);
            Query_helper::update(TABLE_SYSTEM_TOKEN, $data,array("id = ".$id),false);
        }
        else
        {
            $data=array();
            $data['user_id']=$user->id;
            $data['token']=$token;
            $data['date_updated']=time();
            Query_helper::add(TABLE_SYSTEM_TOKEN, $data,false);
        }
    }
}
