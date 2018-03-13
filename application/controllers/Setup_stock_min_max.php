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
        elseif($action=="save")
        {
            $this->system_save();
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
            $user=User_helper::get_user();

            $data['user_outlet_ids']=array();
            //$data['user_outlets']=User_helper::get_assigned_outlets();
            $this->db->from($this->config->item('table_pos_setup_user_outlet').' user_outlet');
            $this->db->join($this->config->item('table_login_csetup_cus_info').' customer_info','customer_info.customer_id = user_outlet.customer_id AND customer_info.revision=1','INNER');
            $this->db->select('customer_info.*');
            $this->db->where('user_outlet.revision',1);
            $this->db->where('customer_info.revision',1);
            $this->db->where('user_outlet.user_id',$user->user_id);
            $this->db->order_by('customer_info.ordering ASC');
            $data['user_outlets'] = $this->db->get()->result_array();

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
            $data['options']=$this->input->post();

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
        $outlet_id=$this->input->post('outlet_id');
        $crop_id=$this->input->post('crop_id');
        $data=array();
        $results=Query_helper::get_info($this->config->item('table_pos_setup_stock_min_max'),'*',array('customer_id ='.$outlet_id));
        foreach($results as $result)
        {
            $data[$result['variety_id']][$result['pack_size_id']]=$result;
        }
        $this->db->from($this->config->item('table_login_setup_classification_variety_price').' variety_price');
        $this->db->select('variety_price.id');
        $this->db->join($this->config->item('table_login_setup_classification_varieties').' v','v.id = variety_price.variety_id','INNER');
        $this->db->select('v.id variety_id,v.name variety_name');
        $this->db->join($this->config->item('table_login_setup_classification_crop_types').' crop_type','crop_type.id = v.crop_type_id','INNER');
        $this->db->join($this->config->item('table_login_setup_classification_pack_size').' pack','pack.id = variety_price.pack_size_id','INNER');
        $this->db->select('pack.name pack_size,pack.id pack_size_id');
        $this->db->select('variety_price.variety_id quantity_min, variety_price.variety_id quantity_max');
        $this->db->where('crop_type.crop_id',$crop_id);
        $this->db->order_by('crop_type.ordering ASC');
        $this->db->order_by('v.ordering ASC');
        $results=$this->db->get()->result_array();
        $items=array();
        foreach($results as $result)
        {
            $item=array();
            $item['id']=$result['id'];
            $item['variety_id']=$result['variety_id'];
            $item['pack_size_id']=$result['pack_size_id'];
            $item['variety_name']=$result['variety_name'];
            $item['pack_size']=$result['pack_size'];
            $item['quantity_min']=$result['quantity_min'];
            if(isset($data[$result['variety_id']][$result['pack_size_id']]))
            {
                $item['quantity_min']=$data[$result['variety_id']][$result['pack_size_id']]['quantity_min'];
                $item['quantity_max']=$data[$result['variety_id']][$result['pack_size_id']]['quantity_max'];
            }
            else
            {
                $item['quantity_min']=0;
                $item['quantity_max']=0;
            }
            $items[]=$item;
        }
        $this->json_return($items);
    }
    private function system_save()
    {
        $user = User_helper::get_user();
        $time=time();
        $item_head=$this->input->post('item');
        $items=$this->input->post('items');
        if(!(isset($this->permissions['action1']) && ($this->permissions['action1']==1)) || !(isset($this->permissions['action2']) && ($this->permissions['action2']==1)))
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
        if(!$this->check_validation())
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->message;
            $this->json_return($ajax);
        }
        $stock_old=array();
        $results=Query_helper::get_info($this->config->item('table_pos_setup_stock_min_max'),'*',array('customer_id ='.$item_head['outlet_id']));
        foreach($results as $result)
        {
            $stock_old[$result['variety_id']][$result['pack_size_id']]=$result;
        }
        $this->db->trans_start();  //DB Transaction Handle START

        foreach($items as $variety_id=>$pack_sizes)
        {
            foreach($pack_sizes as $pack_size_id=>$quantity)
            {
                if(isset($stock_old[$variety_id][$pack_size_id]))
                {
                    $data=array();
                    $data['quantity_min']=$quantity['quantity_min'];
                    $data['quantity_max']=$quantity['quantity_max'];
                    $data['date_updated']=$time;
                    $data['user_updated']=$user->user_id;
                    $this->db->set('revision_count', 'revision_count+1', FALSE);
                    Query_helper::update($this->config->item('table_pos_setup_stock_min_max'),$data,array('id='.$stock_old[$variety_id][$pack_size_id]['id']));
                }
                else
                {
                    $data=array();
                    $data['customer_id']=$item_head['outlet_id'];
                    $data['variety_id']=$variety_id;
                    $data['pack_size_id']=$pack_size_id;
                    $data['quantity_min']=$quantity['quantity_min'];
                    $data['quantity_max']=$quantity['quantity_max'];
                    $data['revision_count']=1;
                    $data['date_updated']=$time;
                    $data['user_updated']=$user->user_id;
                    Query_helper::add($this->config->item('table_pos_setup_stock_min_max'),$data);
                }
            }
        }

        $this->db->trans_complete();   //DB Transaction Handle END
        if ($this->db->trans_status() === TRUE)
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("MSG_SAVED_SUCCESS");
            $this->json_return($ajax);
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
        $this->load->library('form_validation');
        $this->form_validation->set_rules('item[outlet_id]',$this->lang->line('LABEL_OUTLET_NAME'),'required');
        $this->form_validation->set_rules('item[crop_id]',$this->lang->line('LABEL_CROP_NAME'),'required');
        if($this->form_validation->run() == FALSE)
        {
            $this->message=validation_errors();
            return false;
        }
        return true;
    }
}
