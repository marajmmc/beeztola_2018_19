<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Budget_dealer_monthly extends Root_Controller
{
    public $message;
    public $permissions;
    public $controller_url;
    public function __construct()
    {
        parent::__construct();
        $this->message="";
        $this->permissions=User_helper::get_permission('Budget_dealer_monthly');
        $this->controller_url='budget_dealer_monthly';
    }
    public function index($action="list", $id=0)
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
        elseif($action=="variety_list")
        {
            $this->system_variety_list();
        }
        elseif($action=="get_items_variety")
        {
            $this->system_get_items_variety();
        }
        elseif($action=="save")
        {
            $this->system_save();
        }
        else
        {
            $this->system_list();
        }
    }
    private function system_list()
    {
        if(isset($this->permissions['action0'])&&($this->permissions['action0']==1))
        {
            $data['system_preference_items']= $this->get_preference();
            $data['title']="Monthly Dealer Budget List";
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
        /*$user=User_helper::get_user();
        $this->db->from($this->config->item('table_sms_transfer_wo').' transfer_wo');
        $this->db->select(
            '
            transfer_wo.id,
            transfer_wo.date_request,
            transfer_wo.quantity_total_request_kg quantity_total_request,
            transfer_wo.quantity_total_approve_kg quantity_total_approve,
            transfer_wo.quantity_total_receive_kg quantity_total_receive
            ');
        $this->db->join($this->config->item('table_login_csetup_cus_info').' outlet_info','outlet_info.customer_id=transfer_wo.outlet_id AND outlet_info.type="'.$this->config->item('system_customer_type_outlet_id').'"','INNER');
        $this->db->select('outlet_info.name outlet_name, outlet_info.customer_code outlet_code');
        $this->db->join($this->config->item('table_login_setup_location_districts').' districts','districts.id = outlet_info.district_id','INNER');
        $this->db->select('districts.name district_name');
        $this->db->join($this->config->item('table_login_setup_location_territories').' territories','territories.id = districts.territory_id','INNER');
        $this->db->select('territories.name territory_name');
        $this->db->join($this->config->item('table_login_setup_location_zones').' zones','zones.id = territories.zone_id','INNER');
        $this->db->select('zones.name zone_name');
        $this->db->join($this->config->item('table_login_setup_location_divisions').' divisions','divisions.id = zones.division_id','INNER');
        $this->db->select('divisions.name division_name');
        $this->db->where('transfer_wo.status !=',$this->config->item('system_status_delete'));
        $this->db->where('transfer_wo.status_delivery',$this->config->item('system_status_delivered'));
        $this->db->where('transfer_wo.status_receive',$this->config->item('system_status_pending'));
        $this->db->where('transfer_wo.status_receive_forward',$this->config->item('system_status_pending'));
        $this->db->where('outlet_info.revision',1);
        $this->db->where('transfer_wo.outlet_id IN (select user_outlet.customer_id from '.$this->config->item('table_pos_setup_user_outlet').' user_outlet'.' where user_outlet.user_id='.$user->user_id.' AND revision=1)');
        $this->db->order_by('transfer_wo.id','DESC');
        $results=$this->db->get()->result_array();
        $items=array();
        foreach($results as $result)
        {
            $item=array();
            $item['id']=$result['id'];
            $item['barcode']=Barcode_helper::get_barcode_transfer_warehouse_to_outlet($result['id']);
            $item['outlet_name']=$result['outlet_name'];
            $item['date_request']=System_helper::display_date($result['date_request']);
            $item['outlet_code']=$result['outlet_code'];
            $item['division_name']=$result['division_name'];
            $item['zone_name']=$result['zone_name'];
            $item['territory_name']=$result['territory_name'];
            $item['district_name']=$result['district_name'];
            $item['quantity_total_approve']=number_format($result['quantity_total_approve'],3,'.','');
            $item['quantity_total_receive']=number_format($result['quantity_total_receive'],3,'.','');
            $item['quantity_total_difference']=number_format(($result['quantity_total_approve']-$result['quantity_total_receive']),3,'.','');
            $items[]=$item;
        }*/
        $items=array();
        $this->json_return($items);
    }
    private function system_add()
    {
        if(isset($this->permissions['action0'])&&($this->permissions['action0']==1))
        {
            $user=User_helper::get_user();

            $data['user_outlet_ids']=array();
            //$data['user_outlets']=User_helper::get_assigned_outlets();
            $this->db->from($this->config->item('table_pos_setup_user_outlet').' user_outlet');
            $this->db->join($this->config->item('table_login_csetup_cus_info').' customer_info','customer_info.customer_id = user_outlet.customer_id','INNER');
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

            $data['item']['id']='';
            $data['title']="Monthly Dealer Budget Add";
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/add",$data,true));
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
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function system_variety_list()
    {
        if(isset($this->permissions['action0'])&&($this->permissions['action0']==1))
        {
            $data['options']=$this->input->post();
            $outlet_id=$this->input->post('outlet_id');
            $month_id=$this->input->post('month_id');
            $crop_id=$this->input->post('crop_id');
            if(!$outlet_id || !$month_id || !$crop_id)
            {
                $ajax['status']=false;
                $ajax['system_message']='Invalid input. Following required field. Ex: Star mark (*)';
                $this->json_return($ajax);
                die();
            }
            //$data['dealers']=Query_helper::get_info($this->config->item('table_pos_setup_farmer_farmer'),'*',array('status ="Active" AND farmer_type_id>1'));
            $this->db->from($this->config->item('table_pos_setup_farmer_outlet').' farmer_outlet');
            $this->db->select('farmer_outlet.farmer_id');
            $this->db->join($this->config->item('table_pos_setup_farmer_farmer').' farmer_farmer','farmer_farmer.id=farmer_outlet.farmer_id','INNER');
            $this->db->select('farmer_farmer.name farmer_name');
            $this->db->where('farmer_farmer.status',$this->config->item('system_status_active'));
            $this->db->where('farmer_farmer.farmer_type_id > ',1);
            $this->db->where('farmer_outlet.revision',1);
            $this->db->where('farmer_outlet.outlet_id',$outlet_id);
            $data['dealers']=$this->db->get()->result_array();

            $data['title']="Variety List";
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>$this->load->view($this->controller_url."/variety_list",$data,true));
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
    private function system_get_items_variety()
    {
        $id=$this->input->post('id');
        $outlet_id=$this->input->post('outlet_id');
        $month_id=$this->input->post('month_id');
        $crop_id=$this->input->post('crop_id');

        $this->db->from($this->config->item('table_pos_setup_farmer_outlet').' farmer_outlet');
        $this->db->select('farmer_outlet.farmer_id');
        $this->db->join($this->config->item('table_pos_setup_farmer_farmer').' farmer_farmer','farmer_farmer.id=farmer_outlet.farmer_id','INNER');
        $this->db->select('farmer_farmer.name farmer_name');
        $this->db->where('farmer_farmer.status',$this->config->item('system_status_active'));
        $this->db->where('farmer_farmer.farmer_type_id > ',1);
        $this->db->where('farmer_outlet.revision',1);
        $this->db->where('farmer_outlet.outlet_id',$outlet_id);
        $dealers=$this->db->get()->result_array();


        $data=array();
        $results=Query_helper::get_info($this->config->item('table_pos_budget_dealer_monthly_details'),'*',array('budget_dealer_monthly_id ='.$id));
        foreach($results as $result)
        {
            $data[$result['variety_id']][$result['pack_size_id']]=$result;
        }
        $this->db->from($this->config->item('table_login_setup_classification_variety_price').' variety_price');
        $this->db->select('variety_price.id');
        $this->db->join($this->config->item('table_login_setup_classification_varieties').' v','v.id = variety_price.variety_id','INNER');
        $this->db->select('v.id variety_id,v.name variety_name');
        $this->db->join($this->config->item('table_login_setup_classification_crop_types').' crop_type','crop_type.id = v.crop_type_id','INNER');
        $this->db->select('crop_type.id crop_type_id,crop_type.name crop_type_name');
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
            $item['crop_type_id']=$result['crop_type_id'];
            $item['variety_id']=$result['variety_id'];
            $item['pack_size_id']=$result['pack_size_id'];
            $item['crop_type_name']=$result['crop_type_name'];
            $item['variety_name']=$result['variety_name'];
            $item['pack_size']=$result['pack_size'];

            foreach($dealers as $dealer)
            {
                $item['amount_budget_'.$dealer['farmer_id']]='--';
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
    private function system_set_preference()
    {
        if(isset($this->permissions['action6']) && ($this->permissions['action6']==1))
        {
            $data['system_preference_items']=$this->get_preference();
            $data['preference_method_name']='list';
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
        $data['outlet_name']= 1;
        $data['month']= 1;
        $data['crop_name']= 1;
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
