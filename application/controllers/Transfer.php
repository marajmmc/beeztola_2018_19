<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Transfer extends CI_Controller
{
    public function index()
    {
        $this->users();
    }
    private function users()
    {
        $source_tables=array(
            'setup_user'=>'arm_pos.pos_setup_user',
            'setup_user_info'=>'arm_pos.pos_setup_user_info',
            'setup_user_outlet'=>'arm_pos.pos_setup_user_outlet'
        );
        $destination_tables=array(
            'setup_user'=>$this->config->item('table_pos_setup_user'),
            'setup_user_info'=>$this->config->item('table_pos_setup_user_info'),
            'setup_user_outlet'=>$this->config->item('table_pos_setup_user_outlet')
        );

        $users=Query_helper::get_info($source_tables['setup_user'],'*',array());

        $results=Query_helper::get_info($source_tables['setup_user_info'],'*',array('revision=1'));
        $user_infos=array();
        foreach($results as $result)
        {
            $user_infos[$result['user_id']]=$result;
        }

        $results=Query_helper::get_info($source_tables['setup_user_outlet'],'*',array('revision=1'));
        $user_outlets=array();
        foreach($results as $result)
        {
            $user_outlets[$result['user_id']][]=$result;
        }


        $this->db->trans_start();  //DB Transaction Handle START

        foreach($users as $user)
        {
//            if($user['id']==1)
//            {
//                $user['password']=md5("Arm!@#$");
//            }
            if(strlen($user['user_name'])==4)
            {
                $user['employee_id']=$user['user_name'];
            }
            Query_helper::add($destination_tables['setup_user'],$user,false);
            $data_user_info=$user_infos[$user['id']];
            unset($data_user_info['id']);
            $data_user_info['designation_id']=$data_user_info['designation'];
            unset($data_user_info['designation']);
            Query_helper::add($destination_tables['setup_user_info'],$data_user_info,false);
            if(isset($user_outlets[$user['id']]))
            {
                foreach($user_outlets[$user['id']] as $data)
                {
                    unset($data['id']);
                    Query_helper::add($destination_tables['setup_user_outlet'],$data,false);
                }
            }
        }

        $this->db->trans_complete();   //DB Transaction Handle END
        if ($this->db->trans_status() === TRUE)
        {
            echo 'Success';
        }
        else
        {
            echo 'Failed';
        }
    }

}
