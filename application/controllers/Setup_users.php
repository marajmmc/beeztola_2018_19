<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Setup_users extends Root_Controller
{
    public $message;
    public $permissions;
    public $controller_url;
    public function __construct()
    {
        parent::__construct();
        $this->message='';
        $this->permissions=User_helper::get_permission('Setup_users');
        $this->controller_url='setup_users';
    }

    public function index($action='list',$id=0)
    {
        if($action=='list')
        {
            $this->system_list();
        }
        elseif($action=='get_items')
        {
            $this->system_get_items();
        }
        elseif($action=='add')
        {
            $this->system_add();
        }
        elseif($action=='edit')
        {
            $this->system_edit($id);
        }
        elseif($action=="edit_username")
        {
            $this->system_edit_username($id);
        }
        elseif($action=="edit_password")
        {
            $this->system_edit_password($id);
        }
        elseif($action=="edit_status")
        {
            $this->system_edit_status($id);
        }
        elseif($action=='save')
        {
            $this->system_save();
        }
        elseif($action=="save_password")
        {
            $this->system_save_password();
        }
        elseif($action=="save_username")
        {
            $this->system_save_username();
        }
        elseif($action=="save_status")
        {
            $this->system_save_status();
        }
        elseif($action=="change_user_group")
        {
            $this->system_change_user_group($id);
        }
        elseif($action=="save_change_user_group")
        {
            $this->system_save_change_user_group();
        }
        elseif($action=="edit_employee_id")
        {
            $this->system_edit_employee_id($id);
        }
        elseif($action=="save_employee_id")
        {
            $this->system_save_employee_id();
        }
        elseif($action=="set_preference")
        {
            $this->system_set_preference();
        }
        elseif($action=="save_preference")
        {
            System_helper::save_preference();
        }
        else
        {
            $this->system_list();
        }
    }
    private function system_list()
    {
        if(isset($this->permissions['action0']) && ($this->permissions['action0']==1))
        {
            $data['system_preference_items']= $this->get_preference();
            $data['title']='List of Users';
            $ajax['status']=true;
            $ajax['system_content'][]=array('id'=>'#system_content','html'=>$this->load->view($this->controller_url.'/list',$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url);
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line('YOU_DONT_HAVE_ACCESS');
            $this->json_return($ajax);
        }
    }
    private function system_get_items()
    {
        /*$user = User_helper::get_user();
        $this->db->from($this->config->item('table_pos_setup_user').' user');
        $this->db->select('user.id,user.employee_id,user.user_name,user.status');
        $this->db->select('user_info.name');
        $this->db->select('ug.name group_name');
        $this->db->join($this->config->item('table_pos_setup_user_info').' user_info','user.id = user_info.user_id','INNER');
        $this->db->join($this->config->item('table_system_user_group').' ug','ug.id = user_info.user_group','LEFT');
        $this->db->where('user_info.revision',1);
        $this->db->order_by('user_info.ordering','ASC');
        if($user->user_group!=1)
        {
            $this->db->where('user_info.user_group !=',1);
        }

        $items=$this->db->get()->result_array();
        foreach($items as &$item)
        {
            if($item['group_name']==null)
            {
                $item['group_name']='Not Assigned';
            }
        }

        //$items=Query_helper::get_info($this->config->item('table_setup_user'),array('id','name','status','ordering'),array('status !="'.$this->config->item('system_status_delete').'"'));
        $this->json_return($items);*/

        $user = User_helper::get_user();

        $this->db->from($this->config->item('table_pos_setup_user').' user');
        $this->db->select('user.id,user.user_name,user.status');

        $this->db->join($this->config->item('table_pos_setup_user_info').' user_info','user.id = user_info.user_id','INNER');
        $this->db->select('user_info.name,user_info.ordering,user_info.blood_group,user_info.mobile_no');

        $this->db->join($this->config->item('table_system_user_group').' ug','ug.id = user_info.user_group','INNER');
        $this->db->select('ug.name user_group ');

        $this->db->join($this->config->item('table_pos_setup_designation').' designation','designation.id = user_info.designation_id','INNER');
        $this->db->select('designation.name designation_name');
        $this->db->join($this->config->item('table_pos_setup_user_outlet').' uo','uo.user_id = user.id and uo.revision =1','LEFT');
        $this->db->select('count(customer_id) total_outlet',true);
        $this->db->where('user_info.revision',1);
        if($user->user_group!=1)
        {
            $this->db->where('user_info.user_group !=',1);
        }
        $this->db->order_by('user_info.ordering ASC');
        $this->db->group_by('user.id');
        $items=$this->db->get()->result_array();
        $this->json_return($items);
    }
    private function system_add()
    {
        if(isset($this->permissions['action1']) && ($this->permissions['action1']==1))
        {
            $user=User_helper::get_user();
            $data['title']='Create New User';
            $data['user'] = array
            (
                'id' => 0,
                'employee_id' => '',
                'user_name' => ''
            );
            $data['user_info'] = array
            (
                'name' => '',
                'designation_id' => '',
                'user_group' => '',
                'date_birth' => '',
                'gender' => 'Male',
                'status_marital' => 'Un-Married',
                'nid' => '',
                'address' => '',
                'blood_group' => '',
                'mobile_no' => '',
                'ordering' => 999
            );
            $data['designations']=Query_helper::get_info($this->config->item('table_pos_setup_designation'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"'));
            if($user->user_group==1)
            {
                $data['user_groups']=Query_helper::get_info($this->config->item('table_system_user_group'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"'));
            }
            else
            {
                $data['user_groups']=Query_helper::get_info($this->config->item('table_system_user_group'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"','id !=1'));
            }

            $ajax['system_content'][]=array('id'=>'#system_content','html'=>$this->load->view($this->controller_url.'/add_edit',$data,true));
            $ajax['status']=true;
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/add');
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line('YOU_DONT_HAVE_ACCESS');
            $this->json_return($ajax);
        }
    }
    private function system_edit($id)
    {
        if(isset($this->permissions['action2']) && ($this->permissions['action2']==1))
        {
            if($id>0)
            {
                $user_id=$id;
            }
            else
            {
                $user_id=$this->input->post('id');
            }
            $user=User_helper::get_user();

            $data['user']=Query_helper::get_info($this->config->item('table_pos_setup_user'),array('id','employee_id','user_name','status'),array('id ='.$user_id),1);
            if(!$data['user'])
            {
                System_helper::invalid_try('Edit Non Exists',$user_id);
                $ajax['status']=false;
                $ajax['system_message']='Wrong input. You use illegal way.';
                $this->json_return($ajax);
            }
            $data['user_info']=Query_helper::get_info($this->config->item('table_pos_setup_user_info'),'*',array('user_id ='.$user_id,'revision =1'),1);
            $data['designations']=Query_helper::get_info($this->config->item('table_pos_setup_designation'),array('id value','name text'),array('status !="'.$this->config->item('system_status_delete').'"'));
            if($user->user_group==1)
            {
                $data['user_groups']=Query_helper::get_info($this->config->item('table_system_user_group'),array('id value','name text'),array('status !="'.$this->config->item('system_status_delete').'"'));
            }
            else
            {
                $data['user_groups']=Query_helper::get_info($this->config->item('table_system_user_group'),array('id value','name text'),array('status ="'.$this->config->item('system_status_delete').'"','id !=1'));
            }

            $data['title']="Edit User (".$data['user_info']['name'].')';
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url.'/add_edit',$data,true));
            $ajax['status']=true;
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/edit/'.$user_id);
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function system_save()
    {
        $id = $this->input->post('id');
        $user = User_helper::get_user();
        $time=time();
        $data_user=$this->input->post('user');
        $data_user_info=$this->input->post('user_info');
        if($id>0)
        {
            if(!(isset($this->permissions['action2']) && ($this->permissions['action2']==1)))
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
                $this->json_return($ajax);
            }

            $result=Query_helper::get_info($this->config->item('table_pos_setup_user'),'*',array('id ='.$id, 'status != "'.$this->config->item('system_status_delete').'"'),1);
            if(!$result)
            {
                System_helper::invalid_try('Update Non Exists',$id);
                $ajax['status']=false;
                $ajax['system_message']='Invalid User.';
                $this->json_return($ajax);
            }
        }
        else
        {
            if(!(isset($this->permissions['action1']) && ($this->permissions['action1']==1)))
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
                $this->json_return($ajax);
            }
        }
        if(!$this->check_validation())
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->message;
            $this->json_return($ajax);
        }

        if(isset($data_user_info['date_birth']))
        {
            $data_user_info['date_birth']=System_helper::get_time($data_user_info['date_birth']);
            if($data_user_info['date_birth']===0)
            {
                unset($data_user_info['date_birth']);
            }
        }

        $this->db->trans_start();  //DB Transaction Handle START
        // new user or user update - revision information
        if($id>0)
        {
            $data=array();
            $data['date_updated'] = $time;
            $data['user_updated'] = $user->user_id;
            Query_helper::update($this->config->item('table_pos_setup_user_info'),$data, array('user_id='.$id,'revision=1'), false);

            $this->db->where('user_id',$id);
            $this->db->set('revision', 'revision+1', FALSE);
            $this->db->update($this->config->item('table_pos_setup_user_info'));

            $data_user_info['revision'] = 1;
            $data_user_info['user_id']=$id;
            $data_user_info['user_created'] = $user->user_id;
            $data_user_info['date_created'] = $time;
            Query_helper::add($this->config->item('table_pos_setup_user_info'),$data_user_info,false);
        }
        else
        {
            $data_user['password']=md5($data_user['password']);
            $data_user['status']=$this->config->item('system_status_active');
            $data_user['user_created'] = $user->user_id;
            $data_user['date_created'] = $time;
            $user_id=Query_helper::add($this->config->item('table_pos_setup_user'),$data_user);

            $data_user_info['user_id']=$user_id;
            $data_user_info['user_created'] = $user->user_id;
            $data_user_info['date_created'] = $time;
            $data_user_info['revision'] = 1;
            Query_helper::add($this->config->item('table_pos_setup_user_info'),$data_user_info,false);
        }

        $this->db->trans_complete();   //DB Transaction Handle END
        if ($this->db->trans_status() === TRUE)
        {
            $save_and_new=$this->input->post('system_save_new_status');
            $this->message=$this->lang->line("MSG_SAVED_SUCCESS");
            if($save_and_new==1)
            {
                $this->system_add();
            }
            else
            {
                $this->system_list();
            }
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("MSG_SAVED_FAIL");
            $this->json_return($ajax);
        }
    }
    private function system_edit_password($id)
    {
        if(isset($this->permissions['action2']) && ($this->permissions['action2']==1))
        {
            if(($this->input->post('id')))
            {
                $user_id=$this->input->post('id');
            }
            else
            {
                $user_id=$id;
            }
            $data['user_info']=Query_helper::get_info($this->config->item('table_pos_setup_user_info'),'*',array('user_id ='.$user_id,'revision =1'),1);
            if(!$data['user_info'])
            {
                System_helper::invalid_try('Edit Non Exists (Change Password)',$user_id);
                $ajax['status']=false;
                $ajax['system_message']='Invalid User.';
                $this->json_return($ajax);
            }
            $data['title']="Reset Password of (".$data['user_info']['name'].')';
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/edit_password",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/edit_password/'.$user_id);
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function system_save_password()
    {
        $id = $this->input->post("id");
        $user = User_helper::get_user();
        if(!(isset($this->permissions['action2']) && ($this->permissions['action2']==1)))
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
            die();
        }
        if(!$this->check_validation_password())
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->message;
            $this->json_return($ajax);
        }
        else
        {
            $result=Query_helper::get_info($this->config->item('table_pos_setup_user'),'*',array('id ='.$id, 'status !="'.$this->config->item('system_status_delete').'"'),1);
            if(!$result)
            {
                System_helper::invalid_try('Update Non Exists (Change Password)',$id);
                $ajax['status']=false;
                $ajax['system_message']='Invalid User.';
                $this->json_return($ajax);
            }
            $this->db->trans_start();  //DB Transaction Handle START
            $data['password']=md5($this->input->post('new_password'));
            $data['user_updated'] = $user->user_id;
            $data['date_updated'] = time();
            Query_helper::update($this->config->item('table_pos_setup_user'),$data,array("id = ".$id));
            $this->db->trans_complete();   //DB Transaction Handle END
            if ($this->db->trans_status() === TRUE)
            {
                $ajax['status']=true;
                $this->message=$this->lang->line("MSG_SAVED_SUCCESS");
                $this->system_list();
            }
            else
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("MSG_SAVED_FAIL");
                $this->json_return($ajax);
            }
        }
    }
    private function system_edit_username($id)
    {
        if(isset($this->permissions['action3']) && ($this->permissions['action3']==1))
        {
            if(($this->input->post('id')))
            {
                $user_id=$this->input->post('id');
            }
            else
            {
                $user_id=$id;
            }
            $data['user_info']=Query_helper::get_info($this->config->item('table_pos_setup_user_info'),'*',array('user_id ='.$user_id,'revision =1'),1);
            if(!$data['user_info'])
            {
                System_helper::invalid_try('Edit Non Exists (User Name)',$user_id);
                $ajax['status']=false;
                $ajax['system_message']='Invalid User.';
                $this->json_return($ajax);
                die();
            }
            $data['user']=Query_helper::get_info($this->config->item('table_pos_setup_user'),'*',array('id ='.$user_id),1);
            $data['title']="Reset Username of (".$data['user_info']['name'].')';
            $data['user_name']=$data['user']['user_name'];
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/edit_username",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/edit_username/'.$user_id);
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function system_save_username()
    {
        $id = $this->input->post("id");
        $user = User_helper::get_user();
        if(!(isset($this->permissions['action3']) && ($this->permissions['action3']==1)))
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
            die();
        }
        if(!$this->check_validation_username())
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->message;
            $this->json_return($ajax);
        }
        else
        {
            $result=Query_helper::get_info($this->config->item('table_pos_setup_user'),array('id','employee_id','user_name'),array('id ='.$id, 'status !="'.$this->config->item('system_status_delete').'"'),1);
            if(!$result)
            {
                System_helper::invalid_try('Update Non Exists (User Name)',$id);
                $ajax['status']=false;
                $ajax['system_message']='Invalid User.';
                $this->json_return($ajax);
                die();
            }
            $this->db->trans_start();  //DB Transaction Handle START
            $data['user_name']=$this->input->post('new_username');
            $data['user_updated'] = $user->user_id;
            $data['date_updated'] = time();
            Query_helper::update($this->config->item('table_pos_setup_user'),$data,array("id = ".$id));

            $this->db->trans_complete();   //DB Transaction Handle END
            if ($this->db->trans_status() === TRUE)
            {
                $this->message=$this->lang->line("MSG_SAVED_SUCCESS");
                $this->system_list();
            }
            else
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("MSG_SAVED_FAIL");
                $this->json_return($ajax);
            }
        }
    }
    private function system_edit_status($id)
    {
        if(isset($this->permissions['action3']) && ($this->permissions['action3']==1))
        {
            if(($this->input->post('id')))
            {
                $user_id=$this->input->post('id');
            }
            else
            {
                $user_id=$id;
            }
            $result=Query_helper::get_info($this->config->item('table_pos_setup_user'),'*',array('id ='.$user_id, 'status !="'.$this->config->item('system_status_delete').'"'),1);
            if(!$result)
            {
                System_helper::invalid_try('Edit Non Exists (User Status)',$user_id);
                $ajax['status']=false;
                $ajax['system_message']='Invalid User.';
                $this->json_return($ajax);
                die();
            }
            $status=$this->config->item('system_status_inactive');
            if($result['status']==$this->config->item('system_status_inactive'))
            {
                $status=$this->config->item('system_status_active');
            }

            $this->db->trans_start();  //DB Transaction Handle START
            Query_helper::update($this->config->item('table_pos_setup_user'),array('status'=>$status),array("id = ".$user_id));
            $this->db->trans_complete();   //DB Transaction Handle END

            if ($this->db->trans_status() === TRUE)
            {
                $this->message='Status Changed to '.$status;
                $this->system_list();
            }
            else
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("MSG_SAVED_FAIL");
                $this->jsonReturn($ajax);
            }
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function system_save_status()
    {
        $time=time();
        $id = $this->input->post("id");
        $user = User_helper::get_user();
        if(!(isset($this->permissions['action3']) && ($this->permissions['action3']==1)))
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
            die();
        }
        if(!$this->check_validation_status())
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->message;
            $this->json_return($ajax);
        }
        else
        {
            $result=Query_helper::get_info($this->config->item('table_pos_setup_user'),array('id','employee_id','user_name'),array('id ='.$id, 'status !="'.$this->config->item('system_status_delete').'"'),1);
            if(!$result)
            {
                System_helper::invalid_try('Update Non Exists (User Status)',$id);
                $ajax['status']=false;
                $ajax['system_message']='Invalid User.';
                $this->json_return($ajax);
                die();
            }
            $this->db->trans_start();  //DB Transaction Handle START
            $data['status']=$this->input->post('status');
            $data['user_updated'] = $user->user_id;
            $data['date_updated'] = $time;
            if($this->input->post('status')==$this->config->item('system_status_inactive'))
            {
                $data['date_deactivated'] = $time;
            }
            Query_helper::update($this->config->item('table_pos_setup_user'),$data,array("id = ".$id));

            $this->db->trans_complete();   //DB Transaction Handle END
            if ($this->db->trans_status() === TRUE)
            {
                $this->message=$this->lang->line("MSG_SAVED_SUCCESS");
                $this->system_list();
            }
            else
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("MSG_SAVED_FAIL");
                $this->json_return($ajax);
            }
        }
    }
    private function system_change_user_group($id)
    {
        if(isset($this->permissions['action2']) && ($this->permissions['action2']==1))
        {
            if(($this->input->post('id')))
            {
                $user_id=$this->input->post('id');
            }
            else
            {
                $user_id=$id;
            }
            $data['user_info']=Query_helper::get_info($this->config->item('table_pos_setup_user_info'),'*',array('user_id ='.$user_id,'revision =1'),1);
            if(!$data['user_info'])
            {
                System_helper::invalid_try('Edit Non Exists (User Group)',$user_id);
                $ajax['status']=false;
                $ajax['system_message']='Invalid User.';
                $this->json_return($ajax);
                die();
            }
            $data['title']="Assign User Group for ".$data['user_info']['name'];
            $data['user_groups']=Query_helper::get_info($this->config->item('table_system_user_group'),'*',array('status ="'.$this->config->item('system_status_active').'"'));
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/change_user_group",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/change_user_group/'.$user_id);
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function system_save_change_user_group()
    {
        $id = $this->input->post("id");
        $user = User_helper::get_user();
        if(!(isset($this->permissions['action2']) && ($this->permissions['action2']==1)))
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
            die();
        }
        if(!$this->check_validation_for_assigned_user_group())
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->message;
            $this->json_return($ajax);
        }
        else
        {
            $time=time();
            $this->db->trans_start();  //DB Transaction Handle START

            $data=Query_helper::get_info($this->config->item('table_pos_setup_user_info'),'*',array('user_id ='.$id,'revision =1'),1);
            if(!$data)
            {
                System_helper::invalid_try('Update Non Exists (User Group)',$id);
                $ajax['status']=false;
                $ajax['system_message']='Invalid User.';
                $this->json_return($ajax);
                die();
            }
            $revision_history_data=array();
            $revision_history_data['date_updated']=$time;
            $revision_history_data['user_updated']=$user->user_id;
            Query_helper::update($this->config->item('table_pos_setup_user_info'),$revision_history_data,array('revision=1','user_id='.$id),false);

            $this->db->where('user_id',$id);
            $this->db->set('revision', 'revision+1', FALSE);
            $this->db->update($this->config->item('table_pos_setup_user_info'));

            $user_group_id=$this->input->post('user_group_id');

            unset($data['id']);
            unset($data['date_updated']);
            unset($data['user_updated']);
            $data['user_group']=$user_group_id;
            $data['user_created'] = $user->user_id;
            $data['date_created'] = $time;
            $data['revision'] = 1;
            Query_helper::add($this->config->item('table_pos_setup_user_info'),$data, false);
            $this->db->trans_complete();   //DB Transaction Handle END
            if ($this->db->trans_status() === TRUE)
            {
                $this->message=$this->lang->line("MSG_SAVED_SUCCESS");
                $this->system_list();
            }
            else
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("MSG_SAVED_FAIL");
                $this->json_return($ajax);
            }
        }
    }
    private function system_edit_employee_id($id)
    {
        if(isset($this->permissions['action3']) && ($this->permissions['action3']==1))
        {
            if(($this->input->post('id')))
            {
                $user_id=$this->input->post('id');
            }
            else
            {
                $user_id=$id;
            }
            $data['user_info']=Query_helper::get_info($this->config->item('table_login_setup_user_info'),'*',array('user_id ='.$user_id,'revision =1'),1);
            if(!$data['user_info'])
            {
                System_helper::invalid_try('Edit Non Exists (Employee ID)',$user_id);
                $ajax['status']=false;
                $ajax['system_message']='Invalid User.';
                $this->json_return($ajax);
                die();
            }
            $data['user']=Query_helper::get_info($this->config->item('table_login_setup_user'),'*',array('id ='.$user_id),1);
            $data['title']="Reset Employee ID of (".$data['user_info']['name'].')';
            $data['employee_id']=$data['user']['employee_id'];
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/edit_employee_id",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/edit_employee_id/'.$user_id);
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function system_save_employee_id()
    {
        $id = $this->input->post("id");
        $user = User_helper::get_user();
        if(!(isset($this->permissions['action3']) && ($this->permissions['action3']==1)))
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
            die();
        }
        if(!$this->check_validation_employee_id())
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->message;
            $this->json_return($ajax);
        }
        else
        {
            $result=Query_helper::get_info($this->config->item('table_login_setup_user'),array('id','employee_id','user_name'),array('id ='.$id, 'status !="'.$this->config->item('system_status_delete').'"'),1);
            if(!$result)
            {
                System_helper::invalid_try('Edit Non Exists (Employee ID)',$id);
                $ajax['status']=false;
                $ajax['system_message']='Invalid User.';
                $this->json_return($ajax);
                die();
            }
            $this->db->trans_start();  //DB Transaction Handle START
            $data['employee_id']=$this->input->post('new_employee_id');
            $data['user_updated'] = $user->user_id;
            $data['date_updated'] = time();
            Query_helper::update($this->config->item('table_login_setup_user'),$data,array("id = ".$id));

            $this->db->trans_complete();   //DB Transaction Handle END
            if ($this->db->trans_status() === TRUE)
            {
                $this->message=$this->lang->line("MSG_SAVED_SUCCESS");
                $this->system_list();
            }
            else
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("MSG_SAVED_FAIL");
                $this->json_return($ajax);
            }
        }
    }
    private function check_validation()
    {
        $id = $this->input->post("id");
        $this->load->library('form_validation');
        if($id>0)
        {
            $this->form_validation->set_rules('user_info[name]',$this->lang->line('LABEL_NAME'),'required');
        }
        else
        {
            $this->form_validation->set_rules('user[user_name]',$this->lang->line('LABEL_USERNAME'),'required');
            $this->form_validation->set_rules('user[password]',$this->lang->line('LABEL_PASSWORD'),'required');
            $this->form_validation->set_rules('user[employee_id]',$this->lang->line('LABEL_EMPLOYEE_ID'),'required');
            $this->form_validation->set_rules('user_info[name]',$this->lang->line('LABEL_NAME'),'required');
            $this->form_validation->set_rules('user_info[user_group]',$this->lang->line('LABEL_USER_GROUP'),'required');
            $this->form_validation->set_rules('user_info[ordering]',$this->lang->line('LABEL_ORDER'),'required');

            $data_user=$this->input->post('user');
            if(!preg_match('/^[a-z0-9][a-z0-9_]*[a-z0-9]$/',$data_user['user_name']))
            {
                $ajax['status']=false;
                $ajax['system_message']='Username create rules violation.';
                $this->json_return($ajax);
            }
            $duplicate_username_check=Query_helper::get_info($this->config->item('table_pos_setup_user'),array('user_name'),array('user_name ="'.$data_user['user_name'].'"'),1);
            if($duplicate_username_check)
            {
                $ajax['status']=false;
                $ajax['system_message']='This Username is already exists.';
                $this->json_return($ajax);
            }
            if($data_user['employee_id'])
            {
                $duplicate_employee_id_check=Query_helper::get_info($this->config->item('table_pos_setup_user'),array('employee_id'),array('employee_id ="'.$data_user['employee_id'].'"'),1);
                if($duplicate_employee_id_check)
                {
                    $ajax['status']=false;
                    $ajax['system_message']='This Employee ID is already exists';
                    $this->json_return($ajax);
                }
            }
        }
        if($this->form_validation->run() == FALSE)
        {
            $this->message=validation_errors();
            return false;
        }
        return true;
    }
    private function check_validation_password()
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('new_password',$this->lang->line('LABEL_PASSWORD'),'required');
        $this->form_validation->set_rules('re_password',$this->lang->line('LABEL_RE_PASSWORD'),'required');
        if($this->input->post('new_password')!=$this->input->post('re_password'))
        {
            $this->message="Password did not Match";
            return false;
        }
        if($this->form_validation->run() == FALSE)
        {
            $this->message=validation_errors();
            return false;
        }
        return true;
    }
    private function check_validation_username()
    {
        $id = $this->input->post("id");
        $this->load->library('form_validation');
        $this->form_validation->set_rules('new_username',$this->lang->line('LABEL_USERNAME'),'required');

        if(!preg_match('/^[a-z0-9][a-z0-9_]*[a-z0-9]$/',$this->input->post('new_username')))
        {
            $ajax['system_message']='Username create rules violation';
            $this->json_return($ajax);
        }
        if($this->input->post('new_username'))
        {
            $duplicate_username_check=Query_helper::get_info($this->config->item('table_pos_setup_user'),array('user_name'),array('id!='.$id,'user_name ="'.$this->input->post('new_username').'"'),1);
            if($duplicate_username_check)
            {
                $ajax['system_message']='This username is already exists';
                $this->json_return($ajax);
            }
        }
        if($this->form_validation->run() == FALSE)
        {
            $this->message=validation_errors();
            return false;
        }
        return true;
    }
    private function check_validation_status()
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('status',$this->lang->line('STATUS'),'required');
        if($this->form_validation->run() == FALSE)
        {
            $this->message=validation_errors();
            return false;
        }
        return true;
    }
    private function check_validation_for_assigned_user_group()
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('user_group_id',$this->lang->line('LABEL_USER_GROUP'),'required');
        if($this->form_validation->run() == FALSE)
        {
            $this->message=validation_errors();
            return false;
        }
        return true;
    }
    private function system_set_preference()
    {
        if(isset($this->permissions['action6']) && ($this->permissions['action6']==1))
        {
            $data['system_preference_items']= $this->get_preference();
            $data['preference_method_name']='list';
            $data['title']="Set Preference";
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("preference_add_edit",$data,true));
            $ajax['system_page_url']=site_url($this->controller_url.'/index/set_preference');
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function get_preference()
    {
        $user = User_helper::get_user();
        $result=Query_helper::get_info($this->config->item('table_system_user_preference'),'*',array('user_id ='.$user->user_id,'controller ="' .$this->controller_url.'"','method ="list"'),1);
        $data['id']= 1;
        $data['user_name']= 1;
        $data['name']= 1;
        $data['user_group']= 1;
        $data['outlet_total']= 1;
        $data['designation_name']= 1;
        $data['mobile_no']= 1;
        $data['blood_group']= 1;
        $data['order']= 1;
        $data['status']= 1;
        $data['status']= 1;
        if($result)
        {
            if($result['preferences']!=null)
            {
                $preferences=json_decode($result['preferences'],true);
                foreach($data as $key=>$value)
                {

                    if(isset($preferences[$key]))
                    {
                        $data[$key]=$value;
                    }
                    else
                    {
                        $data[$key]=0;
                    }
                }
            }
        }
        return $data;
    }
}
