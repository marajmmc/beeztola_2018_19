<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Report_farmer extends Root_Controller
{
    public $message;
    public $permissions;
    public $controller_url;
    public function __construct()
    {
        parent::__construct();
        $this->message="";
        $this->permissions = User_helper::get_permission(get_class($this));
        $this->controller_url = strtolower(get_class($this));
        $this->user_outlets=User_helper::get_assigned_outlets();
        if(!(sizeof($this->user_outlets)>0))
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line('MSG_OUTLET_NOT_ASSIGNED');
            $this->json_return($ajax);
        }
    }

    public function index($action="search",$id=0)
    {
        if($action=="search")
        {
            $this->system_search();
        }
        elseif($action=="list")
        {
            $this->system_list();
        }
        elseif($action=="get_items")
        {
            $this->system_get_items();
        }
        elseif($action=="details")
        {
            $this->system_details($id);
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
            $this->system_search();
        }
    }

    private function system_search()
    {
        if(isset($this->permissions['action0'])&&($this->permissions['action0']==1))
        {
            $data['assigned_outlet']=$this->user_outlets;
            $data['title']="Farmer Report Search";
            $data['farmer_types']=Query_helper::get_info($this->config->item('table_pos_setup_farmer_type'),'*',array('status !="'.$this->config->item('system_status_delete').'"'),0,0,array('ordering ASC','id ASC'));
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/search",$data,true));
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

    private function get_preference_headers()
    {
        $data['sl_no']= 1;
        if(isset($this->permissions['action2'])&&($this->permissions['action2']==1))
        {
            $data['barcode']= 1;
        }
        $data['name']= 1;
        $data['mobile_no']= 1;
        $data['farmer_type_name']= 1;
        $data['status_card_require']= 1;
        $data['address']= 1;
        $data['total_invoice']= 1;
        $data['status']= 1;
        return $data;
    }

    private function get_preference()
    {
        $user = User_helper::get_user();
        $result=Query_helper::get_info($this->config->item('table_system_user_preference'),'*',array('user_id ='.$user->user_id,'controller ="' .$this->controller_url.'"','method ="search"'),1);
        $data=$this->get_preference_headers();
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

    private function system_list()
    {
        if(isset($this->permissions['action0'])&&($this->permissions['action0']==1))
        {
            $reports=$this->input->post('report');
            if(!$reports['outlet_id'])
            {
                $ajax['status']=false;
                $ajax['system_message']='This outlet field is required';
                $this->json_return($ajax);
            }
            $data['options']=$reports;
            $data['system_preference_items']= $this->get_preference();
            $data['title']="Farmer Info";
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>$this->load->view($this->controller_url."/list",$data,true));
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
        $outlet_id=$this->input->post('outlet_id');
        $farmer_type=$this->input->post('farmer_type');
        $status=$this->input->post('status');
        $mobile_no=$this->input->post('mobile_no');
        $this->db->from($this->config->item('table_pos_setup_farmer_farmer').' farmer');
        $this->db->select('farmer.*');
        $this->db->join($this->config->item('table_pos_setup_farmer_type').' farmer_type','farmer_type.id = farmer.farmer_type_id','INNER');
        $this->db->select('farmer_type.name farmer_type_name, farmer_type.discount_self_percentage');
        $this->db->join($this->config->item('table_pos_setup_farmer_outlet').' farmer_outlet','farmer_outlet.farmer_id = farmer.id and farmer_outlet.revision =1','LEFT');
        $this->db->join($this->config->item('table_pos_sale').' sale','sale.farmer_id = farmer.id','LEFT');
        $this->db->select('count(sale.id) total_invoice',true);
        $this->db->where('farmer_outlet.outlet_id',$outlet_id);
        if($farmer_type>0)
        {
            $this->db->where('farmer_type.id',$farmer_type);
        }
        if($status)
        {
            $this->db->where('farmer.status',$status);
        }

        if($mobile_no)
        {
            $this->db->where('farmer.mobile_no',$mobile_no);
        }
        $this->db->order_by('farmer.id DESC');
        $this->db->group_by('farmer.id');
        $items=$this->db->get()->result_array();
        $time=time();
        foreach($items as &$item)
        {
            $item['barcode']=Barcode_helper::get_barcode_farmer($item['id']);
            if(($item['discount_self_percentage']>0)&&($item['time_card_off_end']<$time))
            {
                $item['status_card_require']=$this->config->item('system_status_yes');
            }
            else
            {
                $item['status_card_require']=$this->config->item('system_status_no');
            }
        }
        $this->json_return($items);
    }

    private function system_set_preference()
    {
        if(isset($this->permissions['action6']) && ($this->permissions['action6']==1))
        {
            $data['system_preference_items']= $this->get_preference();
            $data['preference_method_name']='search';
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

    private function system_details($id)
    {
        if(isset($this->permissions['action7']) && ($this->permissions['action7']==1))
        {
            if($id>0)
            {
                $item_id=$id;
            }
            else
            {
                $item_id=$this->input->post('id');
            }
            $this->db->from($this->config->item('table_pos_setup_farmer_farmer').' farmer');
            $this->db->select('farmer.*');
            $this->db->join($this->config->item('table_pos_setup_farmer_type').' farmer_type','farmer_type.id = farmer.farmer_type_id','INNER');
            $this->db->select('farmer_type.name farmer_type_name,farmer_type.discount_self_percentage');
            $this->db->where('farmer.id',$item_id);
            $data['item']=$this->db->get()->row_array();
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
