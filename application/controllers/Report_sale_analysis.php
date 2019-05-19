<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Report_sale_analysis extends Root_Controller
{
    public $message;
    public $permissions;
    public $controller_url;
    public $common_view_location;
    public $user_outlets;
    public $user_outlet_ids;

    public function __construct()
    {
        parent::__construct();
        $this->message="";
        $this->permissions=User_helper::get_permission(get_class());
        $this->controller_url=strtolower(get_class());
        $this->common_view_location='report_sale_analysis';
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
        $this->language_labels();
    }
    private function language_labels()
    {
        $this->lang->language['LABEL_FISCAL_YEARS']='Fiscal Years';
        $this->lang->language['LABEL_QUANTITY_PKT']='Quantity (pkt)';
        $this->lang->language['LABEL_QUANTITY_KG']='Quantity (kg)';
        $this->lang->language['LABEL_SALES_AMOUNT']='Sales Amount';
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
        elseif($action=="get_items_outlets_amount")
        {
            $this->system_get_items_outlets_amount();
        }
        elseif($action=="get_items_variety_amount_quantity")
        {
            $this->system_get_items_variety_amount_quantity();
        }
        elseif($action=="set_preference_variety_amount_quantity")
        {
            $this->system_set_preference('list_variety_amount_quantity');
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
    private function get_preference_headers($method)
    {
        $data=array();
        if($method=='list_variety_amount_quantity')
        {
            $data['crop_name']= 1;
            $data['crop_type_name']= 1;
            $data['variety_name']= 1;
            $data['pack_size']= 1;
            $data['quantity_pkt']= 1;
            $data['quantity_kg']= 1;
            $data['sales_amount']= 1;
        }
        return $data;
    }
    private function system_set_preference($method)
    {
        $user = User_helper::get_user();
        if(isset($this->permissions['action6']) && ($this->permissions['action6']==1))
        {
            $data['system_preference_items']=System_helper::get_preference($user->user_id,$this->controller_url,$method,$this->get_preference_headers($method));
            $data['preference_method_name']=$method;
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("preference_add_edit",$data,true));
            $ajax['system_page_url']=site_url($this->controller_url.'/index/set_preference_'.$method);
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function system_search()
    {
        if(isset($this->permissions['action0'])&&($this->permissions['action0']==1))
        {
            $data['assigned_outlet']=$this->user_outlets;
            $data['pack_sizes']=Query_helper::get_info($this->config->item('table_login_setup_classification_pack_size'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"'));
            $data['fiscal_years']=Query_helper::get_info($this->config->item('table_login_basic_setup_fiscal_year'),array('id value','name text','date_start','date_end'),array());


            $data['title']="Sales Analysis Report";
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
    private function system_list()
    {
        if(isset($this->permissions['action0'])&&($this->permissions['action0']==1))
        {
            $user = User_helper::get_user();
            $reports=$this->input->post('report');
            $fiscal_year_id=$reports['fiscal_year_id'];

            if(!($reports['fiscal_year_id']))
            {
                $ajax['status']=false;
                $ajax['system_message']='Fiscal year field is required.';
                $this->json_return($ajax);
            }
            if(!($reports['fiscal_year_number']))
            {
                $ajax['status']=false;
                $ajax['system_message']='Number of Previous Year field is required.';
                $this->json_return($ajax);
            }
            if(!($reports['outlet_id']))
            {
                $ajax['status']=false;
                $ajax['system_message']='Outlet field is required.';
                $this->json_return($ajax);
            }
            $data['options']=$reports;
            $data['fiscal_years']=Query_helper::get_info($this->config->item('table_login_basic_setup_fiscal_year'),'*',array('id <='.$fiscal_year_id),$reports['fiscal_year_number']+1,0,array('id DESC'));

            $ajax['status']=true;
            if($reports['report_name']=='outlets_amount')
            {
                $data['title']="Sales Analysis Report";
                $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>$this->load->view($this->controller_url."/list_outlets_amount",$data,true));

            }
            elseif($reports['report_name']=='variety_amount_quantity')
            {
                $method='list_variety_amount_quantity';
                $data['title']="Product Sales Analysis Report";
                $data['system_preference_items']= System_helper::get_preference($user->user_id,$this->controller_url,$method,$this->get_preference_headers($method));
                $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>$this->load->view($this->controller_url."/list_variety_amount_quantity",$data,true));

            }
            else
            {
                $this->message='Invalid Report type';
            }

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

    private function system_get_items_outlets_amount()
    {
        $items=array();
        $fiscal_year_id=$this->input->post('fiscal_year_id');
        $month=$this->input->post('month');
        $fiscal_year_number=$this->input->post('fiscal_year_number');
        $fiscal_years=$this->get_fiscal_years($fiscal_year_id, $fiscal_year_number, $month);
        $outlet_id=$this->input->post('outlet_id');



        $this->db->from($this->config->item('table_login_csetup_cus_info').' outlet_info');
        $this->db->select('outlet_info.customer_id outlet_id, outlet_info.name outlet_name');
        $this->db->join($this->config->item('table_login_setup_location_districts').' districts','districts.id = outlet_info.district_id','INNER');
        $this->db->join($this->config->item('table_login_setup_location_territories').' territories','territories.id = districts.territory_id','INNER');
        $this->db->join($this->config->item('table_login_setup_location_zones').' zones','zones.id = territories.zone_id','INNER');
        $this->db->order_by('outlet_info.ordering');
        $this->db->where('outlet_info.revision',1);
        $this->db->where('outlet_info.type',$this->config->item('system_customer_type_outlet_id'));
        $this->db->where('outlet_info.customer_id',$outlet_id);

        $results=$this->db->get()->result_array();
        $outlets=array();
        $outlet_ids[0]=0;
        foreach($results as $result)
        {
            $outlets[$result['outlet_id']]=$this->initialize_row($fiscal_years,array('area'=>$result['outlet_name']));
            $outlet_ids[$result['outlet_id']]=$result['outlet_id'];
        }

        foreach($fiscal_years as $fy)
        {
            $this->db->from($this->config->item('table_pos_sale').' sale');
            $this->db->select('SUM(sale.amount_payable) sale_amount');
            $this->db->select('sale.outlet_id');
            $this->db->where('sale.date_sale >=',$fy['date_start']);
            $this->db->where('sale.date_sale <=',$fy['date_end']);
            $this->db->where('sale.status',$this->config->item('system_status_active'));
            $this->db->where('sale.outlet_id',$outlet_id);
            $this->db->group_by('sale.outlet_id');
            $results=$this->db->get()->result_array();
            foreach($results as $result)
            {
                $outlets[$result['outlet_id']]['sales_'.$fy['id'].'_amount']=$result['sale_amount'];
            }
        }
        $grand_total=$this->initialize_row($fiscal_years,array('area'=>'Grand Total'));
        foreach($outlets as $row)
        {
            foreach($row  as $key=>$r)
            {
                if(!($key=='area'))
                {
                    $grand_total[$key]+=$row[$key];
                }
            }
            $items[]=$row;
        }
        $items[]=$grand_total;
        $this->json_return($items);
    }

    private function system_get_items_variety_amount_quantity()
    {
        $items=array();

        $crop_id=$this->input->post('crop_id');
        $crop_type_id=$this->input->post('crop_type_id');
        $variety_id=$this->input->post('variety_id');
        $pack_size_id=$this->input->post('pack_size_id');
        $fiscal_year_id=$this->input->post('fiscal_year_id');
        $month=$this->input->post('month');
        $fiscal_year_number=$this->input->post('fiscal_year_number');


        /*get variety*/
        $this->db->from($this->config->item('table_login_setup_classification_varieties').' v');
        $this->db->select('v.id variety_id,v.name variety_name');
        $this->db->join($this->config->item('table_login_setup_classification_crop_types').' crop_type','crop_type.id = v.crop_type_id','INNER');
        $this->db->select('crop_type.id crop_type_id, crop_type.name crop_type_name');
        $this->db->join($this->config->item('table_login_setup_classification_crops').' crop','crop.id = crop_type.crop_id','INNER');
        $this->db->select('crop.id crop_id,crop.name crop_name');
        if($crop_id)
        {
            $this->db->where('crop.id',$crop_id);
            if($crop_type_id)
            {
                $this->db->where('crop_type.id',$crop_type_id);
                if($variety_id)
                {
                    $this->db->where('v.id',$variety_id);
                }
            }
        }
        //$this->db->where('v.status',$this->config->item('system_status_active'));
        $this->db->where('v.whose','ARM');
        $this->db->order_by('crop.ordering','ASC');
        $this->db->order_by('crop.id','ASC');
        $this->db->order_by('crop_type.ordering','ASC');
        $this->db->order_by('crop_type.id','ASC');
        $this->db->order_by('v.ordering','ASC');
        $this->db->order_by('v.id','ASC');
        $varieties = $this->db->get()->result_array();


        /*fiscal year*/
        $fiscal_years=$this->get_fiscal_years($fiscal_year_id, $fiscal_year_number, $month);

        $sales=$this->get_sales_variety_amount_quantity($fiscal_years);

        $type_total=$this->initialize_row($fiscal_years,array('crop_name'=>'','crop_type_name'=>'','variety_name'=>'Total Type','pack_size'=>''));
        $crop_total=$this->initialize_row($fiscal_years,array('crop_name'=>'','crop_type_name'=>'Total Crop','variety_name'=>'','pack_size'=>''));
        $grand_total=$this->initialize_row($fiscal_years,array('crop_name'=>'Grand Total','crop_type_name'=>'','variety_name'=>'','pack_size'=>''));

        $prev_crop_name='';
        $prev_type_name='';
        $first_row=true;

        foreach($varieties as $variety)
        {
            if(isset($sales[$variety['variety_id']]))
            {
                foreach($sales[$variety['variety_id']] as $pack_size=>$yearly_sale)
                {
                    $row=$this->initialize_row($fiscal_years,array('crop_name'=>$variety['crop_name'],'crop_type_name'=>$variety['crop_type_name'],'variety_name'=>$variety['variety_name'],'pack_size'=>$pack_size));
                    //$info=$this->initialize_row($fiscal_years,$variety['crop_name'],$variety['crop_type_name'],$variety['variety_name'],$pack_size);
                    if(!$first_row)
                    {
                        if($prev_crop_name!=$variety['crop_name'])
                        {
                            $items[]=$type_total;
                            $items[]=$crop_total;
                            $type_total=$this->reset_row($type_total);
                            $crop_total=$this->reset_row($crop_total);

                            $prev_crop_name=$variety['crop_name'];
                            $prev_type_name=$variety['crop_type_name'];
                        }
                        elseif($prev_type_name!=$variety['crop_type_name'])
                        {
                            $items[]=$type_total;
                            $type_total=$this->reset_row($type_total);
                            $row['crop_name']='';
                            $prev_type_name=$variety['crop_type_name'];
                        }
                        else
                        {
                            $row['crop_name']='';
                            $row['crop_type_name']='';
                        }
                    }
                    else
                    {
                        $prev_crop_name=$variety['crop_name'];
                        $prev_type_name=$variety['crop_type_name'];
                        $first_row=false;
                    }
                    foreach($yearly_sale as $fy_id=>$details_sale)
                    {
                        $row['quantity_'.$fy_id.'_pkt']=$details_sale['quantity_sale_pkt'];
                        $row['quantity_'.$fy_id.'_kg']=$details_sale['quantity_sale_kg'];
                        $row['sales_'.$fy_id.'_amount']=$details_sale['amount_total'];
                    }
                    foreach($row as $key=>$r)
                    {
                        if(!(($key=='crop_name')||($key=='crop_type_name')||($key=='variety_name')||($key=='pack_size')))
                        {
                            $type_total[$key]+=$row[$key];
                            $crop_total[$key]+=$row[$key];
                            $grand_total[$key]+=$row[$key];
                        }
                    }
                    $items[]=$row;
                }
            }
        }
        $items[]=$type_total;
        $items[]=$crop_total;
        $items[]=$grand_total;
        $this->json_return($items);
    }
    private function get_sales_variety_amount_quantity($fiscal_years)
    {
        $crop_id=$this->input->post('crop_id');
        $crop_type_id=$this->input->post('crop_type_id');
        $variety_id=$this->input->post('variety_id');
        $pack_size_id=$this->input->post('pack_size_id');

        $outlet_id=$this->input->post('outlet_id');

        $sales=array();
        foreach($fiscal_years as $fy)
        {
            $this->db->from($this->config->item('table_pos_sale_details').' details');
            $this->db->select('details.variety_id, details.pack_size, details.pack_size_id');
            $this->db->select('SUM(details.quantity) quantity_sale_pkt');
            $this->db->select('SUM((details.pack_size*details.quantity)/1000) quantity_sale_kg');
            $this->db->select('SUM(details.amount_payable_actual-((details.amount_payable_actual*sale.discount_self_percentage)/100)) amount_total');

            $this->db->join($this->config->item('table_pos_sale').' sale','sale.id = details.sale_id','INNER');



            $this->db->join($this->config->item('table_login_setup_classification_varieties').' v','v.id=details.variety_id', 'INNER');
            $this->db->join($this->config->item('table_login_setup_classification_crop_types').' crop_type','crop_type.id = v.crop_type_id','INNER');


            $this->db->where('sale.date_sale >=',$fy['date_start']);
            $this->db->where('sale.date_sale <=',$fy['date_end']);
            $this->db->where('sale.status',$this->config->item('system_status_active'));
            if($crop_id>0)
            {
                $this->db->where('crop_type.crop_id',$crop_id);
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
            $this->db->where('sale.outlet_id',$outlet_id);
            $this->db->group_by('details.variety_id, details.pack_size_id');
            $results=$this->db->get()->result_array();
            foreach($results as $result)
            {
                $sales[$result['variety_id']][$result['pack_size']][$fy['id']]=$result;
            }
        }

        return $sales;

    }

    private function get_fiscal_years($fiscal_year_id, $fiscal_year_number, $month)
    {

        $fiscal_years=Query_helper::get_info($this->config->item('table_login_basic_setup_fiscal_year'),'*',array('id <='.$fiscal_year_id),$fiscal_year_number+1,0,array('id DESC'));
        if($month>0)
        {
            $num_of_months=$this->input->post('num_of_months');
            foreach($fiscal_years as &$fy)
            {
                $year_start=date('Y', $fy['date_start']);
                $month_start=date('m', $fy['date_start']);
                $year_end=date('Y', $fy['date_end']);
                $month_end=date('m', $fy['date_end']);
                if($month>$month_end)
                {
                    $fy['date_start']=strtotime('01-'.$month.'-'.$year_start);
                    if(($month+$num_of_months)>12)
                    {
                        $fy['date_end']=strtotime('01-'.($month+$num_of_months-12).'-'.$year_end)-1;
                    }
                    else
                    {
                        $fy['date_end']=strtotime('01-'.($month+$num_of_months).'-'.$year_start)-1;
                    }
                }
                else
                {
                    $fy['date_start']=strtotime('01-'.$month.'-'.$year_end);

                    if(($month+$num_of_months)>12)
                    {
                        $fy['date_end']=strtotime('01-'.($month+$num_of_months-12).'-'.($year_end+1))-1;
                    }
                    else
                    {
                        $fy['date_end']=strtotime('01-'.($month+$num_of_months).'-'.$year_end)-1;
                    }
                }
            }
        }
        return $fiscal_years;
    }
    private function initialize_row($fiscal_years,$row)
    {
        foreach($fiscal_years as $fy)
        {
            $row['quantity_'.$fy['id'].'_pkt']=0;
            $row['quantity_'.$fy['id'].'_kg']=0;
            $row['sales_'.$fy['id'].'_amount']=0;
        }
        return $row;
    }
    private function reset_row($row)
    {
        foreach($row  as $key=>$r)
        {
            if(!(($key=='crop_name')||($key=='crop_type_name')||($key=='variety_name')||($key=='pack_size')))
            {
                $row[$key]=0;
            }
        }
        return $row;
    }



}
