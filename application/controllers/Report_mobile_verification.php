<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Report_mobile_verification extends Root_Controller
{
    public $message;
    public $permissions;
    public $controller_url;
    public function __construct()
    {
        parent::__construct();
        $this->message='';
        $this->permissions = User_helper::get_permission(get_class($this));
        $this->controller_url = strtolower(get_class($this));
        //extra language
        $this->lang->language['LABEL_TOTAL_TRY']='Total Try';
        $this->lang->language['LABEL_TOTAL_USED']='used';
        $this->lang->language['LABEL_TOTAL_UNUSED']='unused';
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
        elseif($action=='details')
        {
            $this->system_details($id);
        }
        elseif ($action == "set_preference_list")
        {
            $this->system_set_preference('list');
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
    private function get_preference_headers($method)
    {
        $data=array();
        if($method=='list')
        {
            $data['id']= 1;
            $data['employee_id']= 1;
            $data['username']= 1;
            $data['name']= 1;
            $data['mobile_no']= 1;
            $data['total_try']= 1;
            $data['total_used']= 1;
            $data['total_unused']= 1;
            $data['status']= 1;
            $data['button_details']= 1;
        }
        return $data;
    }
    private function system_set_preference($method = 'list')
    {
        $user = User_helper::get_user();
        if (isset($this->permissions['action6']) && ($this->permissions['action6'] == 1))
        {
            $data = array();
            $data['system_preference_items'] = System_helper::get_preference($user->user_id, $this->controller_url, $method, $this->get_preference_headers($method));
            $data['preference_method_name'] = $method;
            $ajax['status'] = true;
            $ajax['system_content'][] = array("id" => "#system_content", "html" => $this->load->view("preference_add_edit", $data, true));
            $ajax['system_page_url'] = site_url($this->controller_url . '/index/set_preference_' . $method);
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status'] = false;
            $ajax['system_message'] = $this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function system_list()
    {
        $user = User_helper::get_user();
        $method = 'list';
        if(isset($this->permissions['action0']) && ($this->permissions['action0']==1))
        {
            $data['system_preference_items'] = System_helper::get_preference($user->user_id, $this->controller_url, $method, $this->get_preference_headers($method));
            $data['title']='Users Mobile verification using report';
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

        $this->db->from($this->config->item('table_system_history_login_verification_code').' vc');

        $this->db->select('COUNT(vc.id) total_try',false);
        $this->db->select('SUM(CASE WHEN vc.status_used="'.$this->config->item('system_status_yes').'" then 1 ELSE 0 END) total_used',false);
        $this->db->select('SUM(CASE WHEN vc.status_used="'.$this->config->item('system_status_no').'" then 1 ELSE 0 END) total_unused',false);

        $this->db->join($this->config->item('table_pos_setup_user').' user','user.id=vc.user_id','INNER');
        $this->db->select('user.id,user.employee_id,user.user_name username,user.status');
        $this->db->join($this->config->item('table_pos_setup_user_info').' user_info','user.id = user_info.user_id','INNER');
        $this->db->select('user_info.name,user_info.mobile_no');
        $this->db->where('user_info.revision',1);
        $this->db->order_by('user.id','ASC');
        $this->db->group_by('user.id');
        $items=$this->db->get()->result_array();



        $this->json_return($items);
    }



    private function system_details($user_id)
    {
        if(isset($this->permissions['action0']) && ($this->permissions['action0']==1))
        {
            if(!($user_id>0))
            {
                $user_id=$this->input->post('id');
            }
            $this->db->from($this->config->item('table_pos_setup_user').' user');
            $this->db->select('user.employee_id');
            $this->db->join($this->config->item('table_pos_setup_user_info').' user_info','user_info.user_id=user.id');
            $this->db->select('user_info.name');
            $this->db->where('user.id',$user_id);
            $this->db->where('user_info.revision',1);
            $user_info=$this->db->get()->row_array();
            $data['title']=$user_info['name'].'(Emp id: '.$user_info['employee_id'].')';
            $data['histories']=Query_helper::get_info($this->config->item('table_system_history_login_verification_code'),'*',array('user_id ='.$user_id),0,0,array('date_created DESC'));

            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#popup_content","html"=>$this->load->view($this->controller_url."/details",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }

}
