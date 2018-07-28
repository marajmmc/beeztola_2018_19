<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Expense_outlet_daily extends Root_Controller
{
    public $message;
    public $permissions;
    public $controller_url;
    public $user_outlets;
    public $user_outlet_ids;
    public function __construct()
    {
        parent::__construct();
        $this->message="";
        $this->permissions=User_helper::get_permission(get_class());
        $this->controller_url=strtolower(get_class());

        $this->user_outlet_ids=array();
        $this->user_outlets=User_helper::get_assigned_outlets();
        if(sizeof($this->user_outlets)>0)
        {
            foreach($this->user_outlets as $row)
            {
                $this->user_outlet_ids[]=$row['customer_id'];
            }
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line('MSG_OUTLET_NOT_ASSIGNED');
            $this->json_return($ajax);
        }
    }
    public function index($action="list",$id=0)
    {
        if($action=="list")
        {
            $this->system_list();
        }
        elseif($action=="get_items")
        {
            $this->system_get_items();
        }
        elseif($action=="add")
        {
            $this->system_add();
        }
        elseif($action=="edit")
        {
            $this->system_edit($id);
        }
        elseif($action=="save")
        {
            $this->system_save();
        }
        elseif($action=="set_preference")
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
        $data['id']= 1;
        $data['outlet_name']= 1;
        $data['date_expense']= 1;
        $data['expense_item']= 1;
        $data['amount_expense']= 1;
        $data['remarks']= 1;
        return $data;
    }
    private function system_set_preference($method='list')
    {
        $user = User_helper::get_user();
        if(isset($this->permissions['action6']) && ($this->permissions['action6']==1))
        {
            $data['system_preference_items']=System_helper::get_preference($user->user_id,$this->controller_url,$method,$this->get_preference_headers($method));
            $data['preference_method_name']=$method;
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
    private function system_list()
    {
        if(isset($this->permissions['action0'])&&($this->permissions['action0']==1))
        {
            $user = User_helper::get_user();
            $method='list';
            $data['system_preference_items']= System_helper::get_preference($user->user_id,$this->controller_url,$method,$this->get_preference_headers($method));
            $data['title']="Daily Outlet Expense List";
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/list",$data,true));
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
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function system_get_items()
    {
        $this->db->from($this->config->item('table_pos_expense_outlet_daily').' item');
        $this->db->select('item.*');
        $this->db->join($this->config->item('table_login_setup_expense_item_outlet').' items','items.id=item.expense_id','INNER');
        $this->db->select('items.name');
        $this->db->join($this->config->item('table_login_csetup_cus_info').' outlet_info','outlet_info.customer_id=item.outlet_id AND outlet_info.revision=1 AND outlet_info.type="'.$this->config->item('system_customer_type_outlet_id').'"','INNER');
        $this->db->select('outlet_info.name outlet_name');
        $this->db->where('item.status !=',$this->config->item('system_status_delete'));
        $this->db->where_in('item.outlet_id',$this->user_outlet_ids);
        $this->db->order_by('item.id','DESC');
        // checking pos_expense_outlet_monthly table date start & date end -> status not forwarded. (checking start date & end where status forward=pending)
        $results=$this->db->get()->result_array();
        $items=array();
        foreach($results as $result)
        {
            $item['id']=$result['id'];
            $item['outlet_name']=$result['outlet_name'];
            $item['date_expense']=System_helper::display_date($result['date_expense']);
            $item['expense_item']=$result['name'];
            $item['amount_expense']=$result['amount'];
            $item['remarks']=$result['remarks'];
            $items[]=$item;
        }
        $this->json_return($items);
    }
    private function system_add()
    {
        if(isset($this->permissions['action1'])&&($this->permissions['action1']==1))
        {

            $data['title']="Create New Daily Outlet Expense";
            $data['item']['id']=0;
            $data['item']['outlet_id']='';
            $data['item']['date_expense']=time();
            $data['item']['expense_name']='';
            $data['item']['expense_id']='';
            $data['item']['amount']='';
            $data['item']['remarks']='';
            $data['item']['status']='Active';

            $data['expense_items']=Query_helper::get_info($this->config->item('table_login_setup_expense_item_outlet'), array('id', 'name'),array('status ="'.$this->config->item('system_status_active').'"'),0,0,array('ordering ASC'));

            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/add_edit",$data,true));
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
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function system_edit($id)
    {
        if(isset($this->permissions['action2'])&&($this->permissions['action2']==1))
        {
            if($id>0)
            {
                $item_id=$id;
            }
            else
            {
                $item_id=$this->input->post('id');
            }

            $data['item']=Query_helper::get_info($this->config->item('table_pos_expense_outlet_daily'),array('*'),array('id ='.$item_id,'status !="'.$this->config->item('system_status_delete').'"'),1,0,array('id ASC'));
            if(!$data['item'])
            {
                System_helper::invalid_try('Edit Non Exists',$item_id);
                $ajax['status']=false;
                $ajax['system_message']='Invalid Item.';
                $this->json_return($ajax);
            }
            if(!in_array($data['item']['outlet_id'],$this->user_outlet_ids))
            {
                System_helper::invalid_try('Edit',$item_id,'outlet id '.$data['item']['outlet_id'].' not assigned');
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
                $this->json_return($ajax);
            }
            $monthly_check=Query_helper::get_info($this->config->item('table_pos_expense_outlet_monthly'),array('*'),array('date_start <='.$data['item']['date_expense'],' date_end >= '.$data['item']['date_expense'],'status_forward_check="'.$this->config->item('system_status_forwarded').'"'),1,0,array('id ASC'));
            if($monthly_check)
            {
                $ajax['status']=false;
                $ajax['system_message']='You can not be updated expense. This expense already forwarded for this month.';
                $this->json_return($ajax);
            }

            $data['expense_items']=Query_helper::get_info($this->config->item('table_login_setup_expense_item_outlet'), array('id', 'name'),array(),0,0,array('ordering ASC'));
            $data['title']='Edit Daily Outlet Expense';
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/add_edit",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/edit/'.$item_id);
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
        $id = $this->input->post("id");
        $user = User_helper::get_user();
        $time=time();
        $item=$this->input->post('item');
        if($id>0)
        {
            if(!(isset($this->permissions['action2']) && ($this->permissions['action2']==1)))
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
                $this->json_return($ajax);
            }

            $result=Query_helper::get_info($this->config->item('table_pos_expense_outlet_daily'),'*',array('id ='.$id, 'status != "'.$this->config->item('system_status_delete').'"'),1);
            if(!$result)
            {
                System_helper::invalid_try('Update Non Exists',$id);
                $ajax['status']=false;
                $ajax['system_message']='Invalid Item.';
                $this->json_return($ajax);
            }
            if(!in_array($result['outlet_id'],$this->user_outlet_ids))
            {
                System_helper::invalid_try('Save',$id,'outlet id '.$result['item']['outlet_id'].' not assigned');
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
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

        if((isset($this->permissions['action2']) && ($this->permissions['action2']==1)))
        {
            $item['date_expense']=System_helper::get_time($item['date_expense']);
        }
        else
        {
            $item['date_expense']=$time;
        }

        $monthly_check=Query_helper::get_info($this->config->item('table_pos_expense_outlet_monthly'),array('*'),array('date_start <='.$item['date_expense'],' date_end >= '.$item['date_expense'],'status_forward_check="'.$this->config->item('system_status_forwarded').'"'),1,0,array('id ASC'));
        if($monthly_check)
        {
            $ajax['status']=false;
            $ajax['system_message']='You can not be use expense date. This expense date already forwarded for this month.';
            $this->json_return($ajax);
        }

        $this->db->trans_start();  //DB Transaction Handle START
        if($id>0)
        {
            $item['date_updated']=$time;
            $item['user_updated']=$user->user_id;
            Query_helper::update($this->config->item('table_pos_expense_outlet_daily'),$item,array('id='.$id));
        }
        else
        {
            $item['date_created']=$time;
            $item['user_created']=$user->user_id;
            Query_helper::add($this->config->item('table_pos_expense_outlet_daily'),$item);
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
    private function check_validation()
    {
        $id=$this->input->post('id');
        $this->load->library('form_validation');
        if(isset($this->permissions['action2'])&&($this->permissions['action2']==1))
        {
            $this->form_validation->set_rules('item[date_expense]',$this->lang->line('LABEL_DATE_EXPENSE'),'required');
        }
        $this->form_validation->set_rules('item[outlet_id]',$this->lang->line('LABEL_OUTLET'),'required');
        $this->form_validation->set_rules('item[expense_id]',$this->lang->line('LABEL_EXPENSE_ITEM'),'required');
        $this->form_validation->set_rules('item[amount]',$this->lang->line('LABEL_AMOUNT_EXPENSE'),'required');
        if($this->form_validation->run() == FALSE)
        {
            $this->message=validation_errors();
            return false;
        }
        return true;
    }
}
