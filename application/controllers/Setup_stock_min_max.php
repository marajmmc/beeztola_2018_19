<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Setup_stock_min_max extends Root_Controller
{
    public $message;
    public $permissions;
    public $controller_url;
    public function __construct()
    {
        parent::__construct();
        $this->message="";
        $this->permissions=User_helper::get_permission('Setup_stock_min_max');
        $this->controller_url='setup_stock_min_max';
    }
    public function index($action="search")
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
        else
        {
            $this->system_search();
        }
    }
    private function system_search()
    {
        if(isset($this->permissions['action0'])&&($this->permissions['action0']==1))
        {
            $data['user_outlet_ids']=array();
            $data['user_outlets']=User_helper::get_assigned_outlets();
            if(sizeof($data['user_outlets'])>0)
            {
                foreach($data['user_outlets'] as $row)
                {
                    $data['user_outlet_ids'][]=$row['id'];
                }
            }
            else
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line('MSG_OUTLET_NOT_ASSIGNED');
                $this->json_return($ajax);
            }


            $data['title']="Min Max Stock Setup";
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/search",$data,true));
            $ajax['status']=true;
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
    private function system_list()
    {
        if(isset($this->permissions['action0'])&&($this->permissions['action0']==1))
        {
            $data['customer_id']=$this->input->post('customer_id');
            $data['crop_id']=$this->input->post('crop_id');

            $data['title']="Variety Current Stock Report";
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
        /*$this->db->from($this->config->item('table_login_setup_bank').' bank');
        $this->db->where('bank.status !=',$this->config->item('system_status_delete'));
        $this->db->order_by('bank.name','ASC');
        $items=$this->db->get()->result_array();*/
        $this->db->from($this->config->item('table_ems_setup_classification_variety_price').' variety_price');
        $this->db->join($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_setup_classification_varieties').' v','v.id = vp.variety_id','INNER');
        $this->db->join($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_setup_classification_crop_types').' type','type.id = v.crop_type_id','INNER');
        $this->db->join($this->config->item('system_db_ems').'.'.$this->config->item('table_ems_setup_classification_vpack_size').' pack','pack.id = vp.pack_size_id','INNER');
        $this->db->where('vp.revision',1);
        $this->db->where('type.crop_id',$data['crop_id']);
        $this->db->order_by('type.ordering ASC');
        $this->db->order_by('v.ordering ASC');
        $data['varieties']=$this->db->get()->result_array();
        $this->db->select('v.id variety_id,v.name variety_name');
        $this->db->select('pack.name pack_name,pack.id pack_size_id');
        $items=array();
        $this->json_return($items);
    }
}
