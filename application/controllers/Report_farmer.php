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
        $data['barcode']= 1;
        $data['name']= 1;
        $data['date_created_time']= 1;
        $data['mobile_no']= 1;
        $data['farmer_type_name']= 1;
        $data['status_card_require']= 1;
        $data['division_name']= 1;
        $data['zone_name']= 1;
        $data['territory_name']= 1;
        $data['district_name']= 1;
        $data['upazilla_name']= 1;
        $data['union_name']= 1;
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
            if(isset($reports['mobile_no']))
            {
                if(!$reports['mobile_no'])
                {
                    if(!$reports['outlet_id'])
                    {
                        $ajax['status']=false;
                        $ajax['system_message']='This outlet field is required';
                        $this->json_return($ajax);
                    }
                }
                if($reports['mobile_no'])
                {
                    $outlet_ids=array();
                    foreach($this->user_outlets as $outlet)
                    {
                        $outlet_ids[$outlet['customer_id']]=$outlet['customer_id'];
                    }
                    $this->db->from($this->config->item('table_pos_setup_farmer_outlet').' farmer_outlet');
                    $this->db->join($this->config->item('table_pos_setup_farmer_farmer').' farmer','farmer.id = farmer_outlet.farmer_id','INNER');
                    $this->db->select('farmer.mobile_no');
                    $this->db->where_in('farmer_outlet.outlet_id',$outlet_ids);
                    $this->db->where('farmer_outlet.revision',1);
                    $results=$this->db->get()->result_array();
                    $farmers_mobile_no=array();
                    foreach($results as $result)
                    {
                        $farmers_mobile_no[$result['mobile_no']]=$result['mobile_no'];
                    }
                    if(!in_array($reports['mobile_no'],$farmers_mobile_no))
                    {
                        $ajax['status']=false;
                        $ajax['system_message']='You can not search report for this mobile number.';
                        $this->json_return($ajax);
                        die();
                    }
                }
            }
            else
            {
                if(!$reports['outlet_id'])
                {
                    $ajax['status']=false;
                    $ajax['system_message']='This outlet field is required';
                    $this->json_return($ajax);
                }
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
        $mobile_no=$this->input->post('mobile_no');
        $this->db->from($this->config->item('table_pos_setup_farmer_farmer').' farmer');
        $this->db->select('farmer.*');
        $this->db->join($this->config->item('table_pos_setup_farmer_type').' farmer_type','farmer_type.id = farmer.farmer_type_id','INNER');
        $this->db->select('farmer_type.name farmer_type_name, farmer_type.discount_self_percentage');

        $this->db->join($this->config->item('table_login_setup_location_unions').' union','union.id = farmer.union_id','LEFT');
        $this->db->select('union.name union_name');
        $this->db->join($this->config->item('table_login_setup_location_upazillas').' u','u.id = union.upazilla_id','LEFT');
        $this->db->select('u.name upazilla_name');
        $this->db->join($this->config->item('table_login_setup_location_districts').' d','d.id = u.district_id','LEFT');
        $this->db->select('d.name district_name');
        $this->db->join($this->config->item('table_login_setup_location_territories').' t','t.id = d.territory_id','LEFT');
        $this->db->select('t.name territory_name');
        $this->db->join($this->config->item('table_login_setup_location_zones').' zone','zone.id = t.zone_id','LEFT');
        $this->db->select('zone.name zone_name');
        $this->db->join($this->config->item('table_login_setup_location_divisions').' division','division.id = zone.division_id','LEFT');
        $this->db->select('division.name division_name');

        if($mobile_no)
        {
            $this->db->where('farmer.mobile_no',$mobile_no);
            $this->db->join("(SELECT count(sale.id) total_invoice, sale.farmer_id FROM ".$this->config->item('table_pos_sale')." sale WHERE sale.status='".$this->config->item('system_status_active')."' GROUP BY sale.farmer_id) saleTbl",'saleTbl.farmer_id=farmer.id','LEFT');
            $this->db->select('saleTbl.total_invoice');
        }
        else
        {
            $this->db->join($this->config->item('table_pos_setup_farmer_outlet').' farmer_outlet','farmer_outlet.farmer_id = farmer.id and farmer_outlet.revision =1','INNER');
            $this->db->where('farmer_outlet.outlet_id',$outlet_id);
            $this->db->join("(SELECT count(sale.id) total_invoice, sale.farmer_id FROM ".$this->config->item('table_pos_sale')." sale WHERE sale.outlet_id = $outlet_id AND sale.status='".$this->config->item('system_status_active')."' GROUP BY sale.farmer_id) saleTbl",'saleTbl.farmer_id=farmer.id','LEFT');
            $this->db->select('saleTbl.total_invoice');
            if($farmer_type>0)
            {
                $this->db->where('farmer_type.id',$farmer_type);
            }
        }
        $this->db->order_by('farmer.id DESC');
        $this->db->group_by('farmer.id');
        $items=$this->db->get()->result_array();
        $time=time();
        foreach($items as &$item)
        {
            $item['barcode']=Barcode_helper::get_barcode_farmer($item['id']);
            $item['date_created_time']=System_helper::display_date_time($item['date_created']);
            if($item['time_card_off_end']>$time)
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
