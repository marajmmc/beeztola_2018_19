<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Report_sale_dealers extends Root_Controller
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
        $this->permissions = User_helper::get_permission(get_class($this));
        $this->controller_url = strtolower(get_class($this));
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
        $this->lang->load('report_sale');
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
            $data['title']="Dealer wise outlet Sale Report Search";
            $ajax['status']=true;
            $data['pack_sizes']=Query_helper::get_info($this->config->item('table_login_setup_classification_pack_size'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"'),0,0,array('name ASC'));
            $data['assigned_outlet']=$this->user_outlets;
            $fiscal_years=Query_helper::get_info($this->config->item('table_login_basic_setup_fiscal_year'),'*',array());
            $data['fiscal_years']=array();
            foreach($fiscal_years as $year)
            {
                $data['fiscal_years'][]=array('text'=>$year['name'],'value'=>System_helper::display_date($year['date_start']).'/'.System_helper::display_date($year['date_end']));
            }
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/search",$data,true));
            $ajax['system_page_url']=site_url($this->controller_url);

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
    private function get_preference_headers($method)
    {
        $data=array();
        if($method=='search_list')
        {
            $data['crop_name']= 1;
            $data['crop_type_name']= 1;
            $data['variety_name']= 1;
            $data['pack_size']= 1;
            $data['quantity_total_pkt']=1;
            $data['quantity_total_kg']=1;
            $data['amount_total']=1;
            $data['quantity_pkt']=1;
            $data['quantity_kg']=1;
            $data['amount']=1;
            return $data;
        }
        return $data;
    }
    private function system_list()
    {
        $user = User_helper::get_user();
        $method='search_list';
        if(isset($this->permissions['action0'])&&($this->permissions['action0']==1))
        {
            $reports=$this->input->post('report');
            $reports['date_end']=System_helper::get_time($reports['date_end']);
            $reports['date_end']=$reports['date_end']+3600*24-1;
            $reports['date_start']=System_helper::get_time($reports['date_start']);
            if($reports['date_start']>=$reports['date_end'])
            {
                $ajax['status']=false;
                $ajax['system_message']='Starting Date should be less than End date';
                $this->json_return($ajax);
            }
            if(!($reports['outlet_id']>0))
            {
                $ajax['status']=false;
                $ajax['system_message']='Select a showroom';
                $this->json_return($ajax);
            }
            $data['options']=$reports;
            $data['dealers']=$this->get_dealers($reports['outlet_id']);
            $ajax['status']=true;
            $data['title']="Outlets Payment Sales Report";
            $data['system_preference_items']= System_helper::get_preference($user->user_id,$this->controller_url,$method,$this->get_preference_headers($method));
            $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>$this->load->view($this->controller_url."/list",$data,true));

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
    private function system_get_items()
    {
        $items=array();
        $outlet_id=$this->input->post('outlet_id');
        $date_end=$this->input->post('date_end');
        $date_start=$this->input->post('date_start');

        $crop_id=$this->input->post('crop_id');
        $crop_type_id=$this->input->post('crop_type_id');
        $variety_id=$this->input->post('variety_id');
        $pack_size_id=$this->input->post('pack_size_id');
        $dealers=$this->get_dealers($outlet_id);
        $dealer_ids=array();
        $dealer_ids[0]=0;
        foreach($dealers as $dealer)
        {
            $dealer_ids[$dealer['farmer_id']]=$dealer['farmer_id'];
        }
        $this->db->from($this->config->item('table_pos_sale_details').' details');
        $this->db->select('details.variety_id,details.pack_size_id,details.pack_size');

        $this->db->join($this->config->item('table_pos_sale').' sale','sale.id = details.sale_id','INNER');


        $this->db->select('SUM(CASE WHEN sale.date_sale>='.$date_start.' and sale.date_sale<='.$date_end.' then details.quantity ELSE 0 END) quantity_total',false);
        $this->db->select('SUM(CASE WHEN sale.date_cancel>='.$date_start.' and sale.date_cancel<='.$date_end.' and sale.status="'.$this->config->item('system_status_inactive').'" then details.quantity ELSE 0 END) quantity_cancel',false);

        $this->db->select('SUM(CASE WHEN sale.date_sale>='.$date_start.' and sale.date_sale<='.$date_end.' then details.amount_total ELSE 0 END) amount_total',false);
        $this->db->select('SUM(CASE WHEN sale.date_sale>='.$date_start.' and sale.date_sale<='.$date_end.' then details.amount_discount_variety ELSE 0 END) amount_discount_variety',false);
        $this->db->select('SUM(CASE WHEN sale.date_sale>='.$date_start.' and sale.date_sale<='.$date_end.' then (details.amount_payable_actual*sale.discount_self_percentage/100) ELSE 0 END) amount_discount_self',false);



        $this->db->select('SUM(CASE WHEN sale.date_cancel>='.$date_start.' and sale.date_cancel<='.$date_end.' and sale.status="'.$this->config->item('system_status_inactive').'" then details.amount_total ELSE 0 END) amount_total_cancel',false);
        $this->db->select('SUM(CASE WHEN sale.date_cancel>='.$date_start.' and sale.date_cancel<='.$date_end.' and sale.status="'.$this->config->item('system_status_inactive').'" then details.amount_discount_variety ELSE 0 END) amount_discount_variety_cancel',false);
        $this->db->select('SUM(CASE WHEN sale.date_cancel>='.$date_start.' and sale.date_cancel<='.$date_end.' and sale.status="'.$this->config->item('system_status_inactive').'" then (details.amount_payable_actual*sale.discount_self_percentage/100) ELSE 0 END) amount_discount_self_cancel',false);

        $this->db->select('sale.farmer_id');
        $this->db->join($this->config->item('table_pos_setup_farmer_farmer').' farmer','farmer.id = sale.farmer_id','INNER');


        $this->db->join($this->config->item('table_login_setup_classification_varieties').' v','v.id = details.variety_id','INNER');
        $this->db->select('v.id variety_id,v.name variety_name');
        $this->db->join($this->config->item('table_login_setup_classification_crop_types').' crop_type','crop_type.id=v.crop_type_id','INNER');
        $this->db->select('crop_type.id crop_type_id, crop_type.name crop_type_name');
        $this->db->join($this->config->item('table_login_setup_classification_crops').' crop','crop.id=crop_type.crop_id','INNER');
        $this->db->select('crop.id crop_id, crop.name crop_name');

        $this->db->where('sale.outlet_id',$outlet_id);

        if($crop_id>0)
        {
            $this->db->where('crop.id',$crop_id);
            if($crop_type_id>0)
            {
                $this->db->where('crop_type.id',$crop_type_id);
                if($variety_id>0)
                {
                    $this->db->where('v.id',$variety_id);
                }
            }
        }
        if($pack_size_id>0)
        {
            $this->db->where('details.pack_size_id',$pack_size_id);
        }
        $this->db->where('farmer.farmer_type_id >',1);

        $this->db->group_by('sale.farmer_id');
        $this->db->group_by('details.variety_id');
        $this->db->group_by('details.pack_size_id');

        $this->db->order_by('crop.ordering','ASC');
        $this->db->order_by('crop.id','ASC');
        $this->db->order_by('crop_type.ordering','ASC');
        $this->db->order_by('crop_type.id','ASC');
        $this->db->order_by('v.ordering','ASC');
        $this->db->order_by('v.id','ASC');
        $results=$this->db->get()->result_array();

        $sales=array();
        foreach($results as $result)
        {
            $sales[$result['variety_id']][$result['pack_size_id']]['crop_name']=$result['crop_name'];
            $sales[$result['variety_id']][$result['pack_size_id']]['crop_type_name']=$result['crop_type_name'];
            $sales[$result['variety_id']][$result['pack_size_id']]['variety_name']=$result['variety_name'];
            $sales[$result['variety_id']][$result['pack_size_id']]['pack_size']=$result['pack_size'];
            $sales[$result['variety_id']][$result['pack_size_id']]['dealers'][$result['farmer_id']]=$result;

        }
        $type_total=$this->initialize_row('','','Total Type','',$dealers);
        $crop_total=$this->initialize_row('','Total Crop','','',$dealers);
        $grand_total=$this->initialize_row('Grand Total','','','',$dealers);
        $prev_crop_name='';
        $prev_type_name='';
        $first_row=true;

        foreach($sales as $variety_id=>$variety_sale)
        {
            foreach($variety_sale as $pack_size_id=>$pack_sale)
            {

                $info=$this->initialize_row($pack_sale['crop_name'],$pack_sale['crop_type_name'],$pack_sale['variety_name'],$pack_sale['pack_size'],$dealers);
                foreach($pack_sale['dealers'] as $dealer_id=>$dealer_sale)
                {
                    if(in_array($dealer_id,$dealer_ids))
                    {
                        $info['quantity_'.$dealer_id.'_pkt']=$dealer_sale['quantity_total']-$dealer_sale['quantity_cancel'];
                        $info['quantity_'.$dealer_id.'_kg']=$info['quantity_'.$dealer_id.'_pkt']*$pack_sale['pack_size']/1000;

                        $amount_payable_all=$dealer_sale['amount_total']-$dealer_sale['amount_discount_variety']-$dealer_sale['amount_discount_self'];
                        $amount_payable_cancel=$dealer_sale['amount_total_cancel']-$dealer_sale['amount_discount_variety_cancel']-$dealer_sale['amount_discount_self_cancel'];
                        $info['amount_'.$dealer_id]=$amount_payable_all-$amount_payable_cancel;

                        $info['quantity_total_pkt']+=$info['quantity_'.$dealer_id.'_pkt'];
                        $info['quantity_total_kg']+=$info['quantity_'.$dealer_id.'_kg'];
                        $info['amount_total']+=$info['amount_'.$dealer_id];
                    }
                }
                if($info['quantity_total_pkt']==0)
                {
                    continue;
                    //exclude 0 values;
                }
                if(!$first_row)
                {
                    if($prev_crop_name!=$pack_sale['crop_name'])
                    {
                        $items[]=$type_total;
                        $items[]=$crop_total;
                        $type_total=$this->reset_row($type_total);
                        $crop_total=$this->reset_row($crop_total);

                        $prev_crop_name=$pack_sale['crop_name'];
                        $prev_type_name=$pack_sale['crop_type_name'];
                    }
                    elseif($prev_type_name!=$pack_sale['crop_type_name'])
                    {
                        $items[]=$type_total;
                        $type_total=$this->reset_row($type_total);
                        $info['crop_name']='';
                        $prev_type_name=$pack_sale['crop_type_name'];
                    }
                    else
                    {
                        $info['crop_name']='';
                        $info['crop_type_name']='';
                    }
                }
                else
                {
                    $prev_crop_name=$pack_sale['crop_name'];
                    $prev_type_name=$pack_sale['crop_type_name'];
                    $first_row=false;
                }

                foreach($pack_sale['dealers'] as $dealer_id=>$dealer_sale)
                {
                    if(in_array($dealer_id,$dealer_ids))
                    {
                        $type_total['quantity_'.$dealer_id.'_pkt']+=$info['quantity_'.$dealer_id.'_pkt'];
                        $crop_total['quantity_'.$dealer_id.'_pkt']+=$info['quantity_'.$dealer_id.'_pkt'];
                        $grand_total['quantity_'.$dealer_id.'_pkt']+=$info['quantity_'.$dealer_id.'_pkt'];

                        $type_total['quantity_'.$dealer_id.'_kg']+=$info['quantity_'.$dealer_id.'_kg'];
                        $crop_total['quantity_'.$dealer_id.'_kg']+=$info['quantity_'.$dealer_id.'_kg'];
                        $grand_total['quantity_'.$dealer_id.'_kg']+=$info['quantity_'.$dealer_id.'_kg'];

                        $type_total['amount_'.$dealer_id]+=$info['amount_'.$dealer_id];
                        $crop_total['amount_'.$dealer_id]+=$info['amount_'.$dealer_id];
                        $grand_total['amount_'.$dealer_id]+=$info['amount_'.$dealer_id];
                    }
                }
                $type_total['quantity_total_pkt']+=$info['quantity_total_pkt'];
                $crop_total['quantity_total_pkt']+=$info['quantity_total_pkt'];
                $grand_total['quantity_total_pkt']+=$info['quantity_total_pkt'];

                $type_total['quantity_total_kg']+=$info['quantity_total_kg'];
                $crop_total['quantity_total_kg']+=$info['quantity_total_kg'];
                $grand_total['quantity_total_kg']+=$info['quantity_total_kg'];

                $type_total['amount_total']+=$info['amount_total'];
                $crop_total['amount_total']+=$info['amount_total'];
                $grand_total['amount_total']+=$info['amount_total'];

                $items[]=$info;

            }
        }
        $items[]=$type_total;
        $items[]=$crop_total;
        $items[]=$grand_total;
        $this->json_return($items);



    }
    private function initialize_row($crop_name,$crop_type_name,$variety_name,$pack_size,$dealers)
    {
        $row=array();
        $row['crop_name']=$crop_name;
        $row['crop_type_name']=$crop_type_name;
        $row['variety_name']=$variety_name;
        $row['pack_size']=$pack_size;
        $row['quantity_total_pkt']=0;
        $row['quantity_total_kg']=0;
        $row['amount_total']=0;
        foreach($dealers  as $dealer)
        {
            $row['quantity_'.$dealer['farmer_id'].'_pkt']=0;
            $row['quantity_'.$dealer['farmer_id'].'_kg']=0;
            $row['amount_'.$dealer['farmer_id']]=0;
        }
        return $row;
    }
    private function reset_row($info)
    {
        foreach($info  as $key=>$r)
        {
            if(!(($key=='crop_name')||($key=='crop_type_name')||($key=='variety_name')||($key=='pack_size')))
            {
                $info[$key]=0;
            }
        }
        return $info;
    }
    private function system_set_preference()
    {
        $user = User_helper::get_user();
        $method='search_list';
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
    private function get_dealers($outlet_id)
    {
        $this->db->from($this->config->item('table_pos_setup_farmer_outlet').' farmer_outlet');
        $this->db->select('farmer_outlet.farmer_id');
        $this->db->join($this->config->item('table_pos_setup_farmer_farmer').' farmer','farmer.id=farmer_outlet.farmer_id','INNER');
        $this->db->select('farmer.name farmer_name,farmer.mobile_no,farmer.status');
        $this->db->where('farmer.status',$this->config->item('system_status_active'));
        $this->db->where('farmer.farmer_type_id > ',1);
        $this->db->where('farmer_outlet.revision',1);
        $this->db->where('farmer_outlet.outlet_id',$outlet_id);
        return $this->db->get()->result_array();
    }
}
