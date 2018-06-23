<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Report_sale_farmer extends Root_Controller
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
        $this->permissions=User_helper::get_permission('Report_sale_farmer');
        $this->controller_url='report_sale_farmer';
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
        elseif($action=="get_items_amount")
        {
            $this->system_get_items_amount();
        }
        elseif($action=="get_items_invoice")
        {
            $this->system_get_items_invoice();
        }
        elseif($action=="get_items_variety")
        {
            $this->system_get_items_variety();
        }
        elseif($action=="set_preference_amount")
        {
            $this->system_set_preference_amount();
        }
        elseif($action=="set_preference_invoice")
        {
            $this->system_set_preference_invoice();
        }
        elseif($action=="set_preference_variety")
        {
            $this->system_set_preference_variety();
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
            $data['pack_sizes']=Query_helper::get_info($this->config->item('table_login_setup_classification_pack_size'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"'),0,0,array('name ASC'));
            $data['farmer_types']=Query_helper::get_info($this->config->item('table_pos_setup_farmer_type'),array('id value,name text'),array('status ="'.$this->config->item('system_status_active').'"'),0,0,array('ordering ASC'));
            $data['title']="Variety wise sales Report Search";
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
            $reports=$this->input->post('report');
            if(!$reports['outlet_id'])
            {
                $ajax['status']=false;
                $ajax['system_message']='This outlet field is required';
                $this->json_return($ajax);
            }
            $reports['date_end']=System_helper::get_time($reports['date_end'])+3600*24-1;
            $reports['date_start']=System_helper::get_time($reports['date_start']);
            if($reports['date_start']>=$reports['date_end'])
            {
                $ajax['status']=false;
                $ajax['system_message']='Starting Date should be less than End date';
                $this->json_return($ajax);
            }
            $data['options']=$reports;
            if($reports['report_name']=='amount')
            {
                $data['system_preference_items']= $this->get_preference_amount();
                $data['title']="Customer Total Sales Report";
                $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>$this->load->view($this->controller_url."/list_amount",$data,true));
            }
            else if($reports['report_name']=='invoice')
            {
                $data['system_preference_items']= $this->get_preference_invoice();
                $data['title']="Customer Invoice Wise Sales Report";
                $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>$this->load->view($this->controller_url."/list_invoice",$data,true));
            }
            else if($reports['report_name']=='variety')
            {
                $data['system_preference_items']= $this->get_preference_variety();
                $data['title']="Customer Variety Wise Sales Report";
                $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>$this->load->view($this->controller_url."/list_variety",$data,true));
            }
            else
            {
                $this->system_search();
            }

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
    private function get_preference_amount()
    {
        $user = User_helper::get_user();
        $result=Query_helper::get_info($this->config->item('table_system_user_preference'),'*',array('user_id ='.$user->user_id,'controller ="' .$this->controller_url.'"','method ="search_amount"'),1);

        $data['sl_no']= 1;
        $data['customer_name']= 1;
        $data['mobile_no']= 1;
        $data['amount_total_paid']= 1;
        $data['amount_total_cancel']= 1;
        $data['amount_actual_paid']= 1;
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
    private function system_get_items_amount()
    {
        $outlet_id=$this->input->post('outlet_id');
        $farmer_type_id=$this->input->post('farmer_type_id');
        $farmer_id=$this->input->post('farmer_id');
        $date_end=$this->input->post('date_end');
        $date_start=$this->input->post('date_start');

        $this->db->from($this->config->item('table_pos_sale').' sale');

        $this->db->select('SUM(CASE WHEN sale.date_sale>='.$date_start.' and sale.date_sale<='.$date_end.' then sale.amount_payable_actual ELSE 0 END) amount_total_paid',false);
        $this->db->select('SUM(CASE WHEN sale.date_cancel>='.$date_start.' and sale.date_cancel<='.$date_end.' and sale.status="'.$this->config->item('system_status_inactive').'" then sale.amount_payable_actual ELSE 0 END) amount_total_cancel',false);
        $this->db->join($this->config->item('table_pos_setup_farmer_farmer').' farmer','farmer.id = sale.farmer_id','INNER');
        $this->db->select('farmer.name customer_name,farmer.id farmer_id,farmer.mobile_no');
        $this->db->where('sale.outlet_id',$outlet_id);
        if($farmer_type_id>0)
        {
            $this->db->where('farmer.farmer_type_id',$farmer_type_id);
            if($farmer_id>0)
            {
                $this->db->where('farmer.id',$farmer_id);
            }
        }
        $where='(sale.date_sale >='.$date_start.' AND sale.date_sale <='.$date_end.')';
        $where.=' OR (sale.date_cancel >='.$date_start.' AND sale.date_cancel <='.$date_end.')';
        $this->db->where('('.$where.')');
        $this->db->group_by('farmer.id');
        $this->db->order_by('farmer.id','DESC');
        $results=$this->db->get()->result_array();
        $grand_total=$this->initialize_row_amount();
        $grand_total['customer_name']='Grand Total';
        $items=array();
        foreach($results as $result)
        {
            $item=$this->initialize_row_amount();
            $item['customer_name']=$result['customer_name'];
            $item['mobile_no']=$result['mobile_no'];
            $item['amount_total_paid']=$result['amount_total_paid'];
            $item['amount_total_cancel']=$result['amount_total_cancel'];
            $grand_total['amount_total_paid']+=$result['amount_total_paid'];
            $grand_total['amount_total_cancel']+=$result['amount_total_cancel'];
            $items[]=$this->get_row_amount($item);
        }
        $items[]=$this->get_row_amount($grand_total);
        $this->json_return($items);

    }
    private function initialize_row_amount()
    {
        $row=array();
        $row['customer_name']='';
        $row['mobile_no']='';
        $row['amount_total_paid']=0;
        $row['amount_total_cancel']=0;
        return $row;
    }
    private function get_row_amount($info)
    {
        $row=array();
        $row['customer_name']=$info['customer_name'];
        $row['mobile_no']=$info['mobile_no'];
        $row['amount_total_paid']=$info['amount_total_paid'];
        $row['amount_total_cancel']=$info['amount_total_cancel'];
        $row['amount_actual_paid']=$info['amount_total_paid']-$info['amount_total_cancel'];
        return $row;
    }

    private function system_set_preference_amount()
    {
        if(isset($this->permissions['action6']) && ($this->permissions['action6']==1))
        {
            $data['system_preference_items']= $this->get_preference_amount();
            $data['preference_method_name']='search_amount';
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("preference_add_edit",$data,true));
            $ajax['system_page_url']=site_url($this->controller_url.'/index/set_preference_amount');
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function get_preference_invoice()
    {
        $user = User_helper::get_user();
        $result=Query_helper::get_info($this->config->item('table_system_user_preference'),'*',array('user_id ='.$user->user_id,'controller ="' .$this->controller_url.'"','method ="search_invoice"'),1);
        $data['customer_name']= 1;
        $data['mobile_no']= 1;
        $data['invoice_no']= 1;
        $data['date_sale']= 1;
        $data['date_cancel']= 1;
        $data['amount_total']= 1;
        $data['amount_discount_variety']= 1;
        $data['amount_discount_self']= 1;
        $data['amount_payable']= 1;
        $data['amount_payable_actual']= 1;
        $data['amount_actual']= 1;
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
    private function system_get_items_invoice()
    {
        $outlet_id=$this->input->post('outlet_id');
        $farmer_type_id=$this->input->post('farmer_type_id');
        $farmer_id=$this->input->post('farmer_id');
        $date_end=$this->input->post('date_end');
        $date_start=$this->input->post('date_start');

        $this->db->from($this->config->item('table_pos_sale').' sale');
        $this->db->select('sale.*');
        $this->db->join($this->config->item('table_pos_setup_farmer_farmer').' f','f.id = sale.farmer_id','INNER');
        $this->db->select('f.name customer_name,f.mobile_no');

        $this->db->where('sale.outlet_id',$outlet_id);
        if($farmer_type_id>0)
        {
            $this->db->where('f.farmer_type_id',$farmer_type_id);
            if($farmer_id>0)
            {
                $this->db->where('f.id',$farmer_id);
            }
        }
        $where='(sale.date_sale >='.$date_start.' AND sale.date_sale <='.$date_end.')';
        $where.=' OR (sale.date_cancel >='.$date_start.' AND sale.date_cancel <='.$date_end.')';
        $this->db->where('('.$where.')');
        $this->db->order_by('f.id','DESC');
        $this->db->order_by('sale.date_sale','DESC');
        $results=$this->db->get()->result_array();
        $customer_total=$this->initialize_row_invoice();
        $customer_total['mobile_no']='Customer Total';
        $grand_total=$this->initialize_row_invoice();
        $grand_total['customer_name']='Grand Total';
        $prev_customer_name='';
        $first_row=true;
        $items=array();
        foreach($results as $result)
        {
            $info=$this->initialize_row_invoice();
            $info['customer_name']=$result['customer_name'];
            $info['mobile_no']=$result['mobile_no'];
            if(!$first_row)
            {
                if($prev_customer_name!=$result['customer_name'])
                {
                    $items[]=$this->get_row_invoice($customer_total);
                    $customer_total=$this->reset_row_invoice($customer_total);
                    $prev_customer_name=$result['customer_name'];
                }
                else
                {
                    $info['customer_name']='';
                    $info['mobile_no']='';
                }
            }
            else
            {
                $prev_customer_name=$result['customer_name'];
                $first_row=false;
            }
            $info['invoice_no']=Barcode_helper::get_barcode_sales($result['id']);
            $info['date_sale']=System_helper::display_date($result['date_sale']);
            if($result['date_cancel']>0)
            {
                $info['date_cancel']=System_helper::display_date_time($result['date_cancel']);
            }
            else
            {
                $info['date_cancel']='';
            }
            $info['amount_total']=$result['amount_total'];
            $info['amount_discount_variety']=$result['amount_discount_variety'];
            $info['amount_discount_self']=$result['amount_discount_self'];
            $info['amount_payable']=$result['amount_payable'];
            $info['amount_payable_actual']=$result['amount_payable_actual'];
            if($result['status']==$this->config->item('system_status_active'))
            {
                $info['amount_actual']=$result['amount_payable_actual'];
            }
            else
            {
                if($result['date_sale']<$date_start)
                {
                    $info['amount_actual']=0-$result['amount_payable'];

                }
                elseif($result['date_cancel']>$date_end)
                {
                    $info['amount_actual']=$result['amount_payable_actual'];
                }
                else
                {
                    $info['amount_actual']=0;
                }
            }

            $customer_total['amount_total']+=$info['amount_total'];
            $customer_total['amount_discount_variety']+=$info['amount_discount_variety'];
            $customer_total['amount_discount_self']+=$info['amount_discount_self'];
            $customer_total['amount_payable']+=$info['amount_payable'];
            $customer_total['amount_payable_actual']+=$info['amount_payable_actual'];
            $customer_total['amount_actual']+=$info['amount_actual'];

            $grand_total['amount_total']+=$info['amount_total'];
            $grand_total['amount_discount_variety']+=$info['amount_discount_variety'];
            $grand_total['amount_discount_self']+=$info['amount_discount_self'];
            $grand_total['amount_payable']+=$info['amount_payable'];
            $grand_total['amount_payable_actual']+=$info['amount_payable_actual'];
            $grand_total['amount_actual']+=$info['amount_actual'];

            $info['status']=$result['status'];
            $items[]=$this->get_row_invoice($info);
        }
        $items[]=$this->get_row_invoice($customer_total);
        $items[]=$this->get_row_invoice($grand_total);
        $this->json_return($items);
    }
    private function initialize_row_invoice()
    {
        $row=array();
        $row['customer_name']='';
        $row['mobile_no']='';
        $row['invoice_no']='';
        $row['date_sale']='';
        $row['date_cancel']='';
        $row['amount_total']=0;
        $row['amount_discount_variety']=0;
        $row['amount_discount_self']=0;
        $row['amount_payable']=0;
        $row['amount_payable_actual']=0;
        $row['amount_actual']=0;
        $row['status']=$this->config->item('system_status_active');
        return $row;
    }
    private function reset_row_invoice($row)
    {
        $row['amount_total']=0;
        $row['amount_discount_variety']=0;
        $row['amount_discount_self']=0;
        $row['amount_payable']=0;
        $row['amount_payable_actual']=0;
        $row['amount_actual']=0;
        return $row;
    }
    private function get_row_invoice($info)
    {
        $row=array();
        $row['customer_name']=$info['customer_name'];
        $row['mobile_no']=$info['mobile_no'];
        $row['invoice_no']=$info['invoice_no'];
        $row['date_sale']=$info['date_sale'];
        $row['date_cancel']=$info['date_cancel'];
        if($info['amount_total']==0)
        {
            $row['amount_total']='';
        }
        else
        {
            $row['amount_total']=number_format($info['amount_total'],2);
        }
        if($info['amount_discount_variety']==0)
        {
            $row['amount_discount_variety']='';
        }
        else
        {
            $row['amount_discount_variety']=number_format($info['amount_discount_variety'],2);
        }
        if($info['amount_discount_self']==0)
        {
            $row['amount_discount_self']='';
        }
        else
        {
            $row['amount_discount_self']=number_format($info['amount_discount_self'],2);
        }
        if($info['amount_payable']==0)
        {
            $row['amount_payable']='';
        }
        else
        {
            $row['amount_payable']=number_format($info['amount_payable'],2);
        }
        if($info['amount_payable_actual']==0)
        {
            $row['amount_payable_actual']='';
        }
        else
        {
            $row['amount_payable_actual']=number_format($info['amount_payable_actual'],2);
        }
        if($info['amount_actual']==0)
        {
            $row['amount_actual']='';
        }
        else
        {
            $row['amount_actual']=number_format($info['amount_actual'],2);
        }
        $row['status']=$info['status'];
        return $row;
    }
    private function system_set_preference_invoice()
    {
        if(isset($this->permissions['action6']) && ($this->permissions['action6']==1))
        {
            $data['system_preference_items']= $this->get_preference_invoice();
            $data['preference_method_name']='search_invoice';
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("preference_add_edit",$data,true));
            $ajax['system_page_url']=site_url($this->controller_url.'/index/set_preference_invoice');
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function get_preference_headers_variety()
    {
        $data['customer_name']= 1;
        $data['mobile_no']= 1;
        $data['crop_name']= 1;
        $data['crop_type_name']= 1;
        $data['variety_name']= 1;
        $data['pack_size']= 1;
        $data['quantity_total_pkt']= 1;
        $data['quantity_total_kg']= 1;
        $data['quantity_cancel_pkt']= 1;
        $data['quantity_cancel_kg']= 1;
        $data['quantity_actual_pkt']= 1;
        $data['quantity_actual_kg']= 1;
        $data['amount_total']= 1;
        $data['amount_discount_variety']= 1;
        $data['amount_discount_self']= 1;
        $data['amount_discount_total']= 1;
        $data['amount_payable_all']= 1;
        $data['amount_payable_cancel']= 1;
        $data['amount_payable_paid']= 1;
        $data['num_invoice']= 1;
        return $data;
    }
    private function get_preference_variety()
    {
        $user = User_helper::get_user();
        $result=Query_helper::get_info($this->config->item('table_system_user_preference'),'*',array('user_id ='.$user->user_id,'controller ="' .$this->controller_url.'"','method ="search_variety"'),1);
        $data=$this->get_preference_headers_variety();
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
    private function system_get_items_variety()
    {
        $outlet_id=$this->input->post('outlet_id');
        $crop_id=$this->input->post('crop_id');
        $crop_type_id=$this->input->post('crop_type_id');
        $variety_id=$this->input->post('variety_id');
        $pack_size_id=$this->input->post('pack_size_id');
        $farmer_type_id=$this->input->post('farmer_type_id');
        $farmer_id=$this->input->post('farmer_id');
        $date_end=$this->input->post('date_end');
        $date_start=$this->input->post('date_start');


        $this->db->from($this->config->item('table_pos_sale_details').' details');
        $this->db->select('details.variety_id,details.pack_size_id,details.pack_size');



        $this->db->join($this->config->item('table_pos_sale').' sale','sale.id = details.sale_id','INNER');

        //$this->db->select('COUNT(details.id) num_invoice',false);
        $this->db->select('SUM(CASE WHEN sale.date_sale>='.$date_start.' and sale.date_sale<='.$date_end.' and sale.status="'.$this->config->item('system_status_active').'" then 1 ELSE 0 END) num_invoice',false);


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
        $this->db->select('farmer.name customer_name,farmer.mobile_no');

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
        if($farmer_type_id>0)
        {
           $this->db->where('farmer.farmer_type_id',$farmer_type_id);
            if($farmer_id>0)
            {
                $this->db->where('farmer.id',$farmer_id);
            }
        }
        $this->db->group_by('sale.farmer_id');
        $this->db->group_by('details.variety_id');
        $this->db->group_by('details.pack_size_id');

        $this->db->order_by('farmer.id','ASC');
        $this->db->order_by('crop.ordering','ASC');
        $this->db->order_by('crop.id','ASC');
        $this->db->order_by('crop_type.ordering','ASC');
        $this->db->order_by('crop_type.id','ASC');
        $this->db->order_by('v.ordering','ASC');
        $this->db->order_by('v.id','ASC');
        $results=$this->db->get()->result_array();
        $crop_total=$this->initialize_row_variety('','','','Total Crop','','');
        $customer_total=$this->initialize_row_variety('','','Customer Total','','','');
        $grand_total=$this->initialize_row_variety('Grand Total','','','','','');
        $prev_customer_name='';
        $prev_crop_name='';
        $prev_type_name='';
        $first_row=true;
        $items=array();
        foreach($results as $result)
        {
            if(($result['quantity_total']==0)&&($result['quantity_cancel']==0))
            {
                continue;
                //exclude 0 values;
            }
            $info=$this->initialize_row_variety($result['customer_name'],$result['mobile_no'],$result['crop_name'],$result['crop_type_name'],$result['variety_name'],$result['pack_size']);
            if(!$first_row)
            {
                if($prev_customer_name!=$result['customer_name'])
                {
                    $items[]=$this->get_row_variety($crop_total);
                    $items[]=$this->get_row_variety($customer_total);
                    $crop_total=$this->reset_row_variety($crop_total);
                    $customer_total=$this->reset_row_variety($customer_total);
                    $prev_customer_name=$result['customer_name'];
                    $prev_crop_name=$result['crop_name'];
                    $prev_type_name=$result['crop_type_name'];

                }
                elseif($prev_crop_name!=$result['crop_name'])
                {
                    $items[]=$this->get_row_variety($crop_total);
                    $crop_total=$this->reset_row_variety($crop_total);
                    $info['customer_name']='';
                    $info['mobile_no']='';
                    $prev_crop_name=$result['crop_name'];
                    $prev_type_name=$result['crop_type_name'];
                }
                elseif($prev_type_name!=$result['crop_type_name'])
                {
                    $info['customer_name']='';
                    $info['mobile_no']='';
                    $info['crop_name']='';
                    $prev_type_name=$result['crop_type_name'];
                }
                else
                {
                    $info['customer_name']='';
                    $info['mobile_no']='';
                    $info['crop_name']='';
                    $info['crop_type_name']='';
                }
            }
            else
            {
                $prev_customer_name=$result['customer_name'];
                $prev_crop_name=$result['crop_name'];
                $prev_type_name=$result['crop_type_name'];
                $first_row=false;
            }
            $info['num_invoice']=$result['num_invoice'];
            $result['quantity_actual']=$result['quantity_total']-$result['quantity_cancel'];
            $result['amount_discount_total']=$result['amount_discount_variety']+$result['amount_discount_self'];
            $result['amount_payable_all']=$result['amount_total']-$result['amount_discount_total'];
            $result['amount_payable_cancel']=$result['amount_total_cancel']-$result['amount_discount_variety_cancel']-$result['amount_discount_self_cancel'];
            $result['amount_payable_paid']=$result['amount_payable_all']-$result['amount_payable_cancel'];
            foreach($info  as $key=>$r)
            {
                if(substr($key,-3)=='pkt')
                {
                    $info[$key]=$result[substr($key, 0, -4)];
                }
                elseif(substr($key,-2)=='kg')
                {
                    $info[$key]=$result[substr($key, 0, -3)]*$result['pack_size']/1000;
                }
                elseif(substr($key,0,6)=='amount')
                {
                    $info[$key]=$result[$key];
                }
            }
            foreach($info  as $key=>$r)
            {
                if(!(($key=='crop_name')||($key=='crop_type_name')||($key=='variety_name')||($key=='pack_size')||($key=='customer_name')||($key=='mobile_no')))
                {
                    $crop_total[$key]+=$info[$key];
                    $customer_total[$key]+=$info[$key];
                    $grand_total[$key]+=$info[$key];
                }
            }
            $items[]=$this->get_row_variety($info);
        }
        $items[]=$this->get_row_variety($crop_total);
        $items[]=$this->get_row_variety($customer_total);
        $items[]=$this->get_row_variety($grand_total);
        $this->json_return($items);
    }
    private function initialize_row_variety($customer_name,$mobile_no,$crop_name,$crop_type_name,$variety_name,$pack_size)
    {
        $row=$this->get_preference_headers_variety();
        foreach($row  as $key=>$r)
        {
            $row[$key]=0;
        }
        $row['customer_name']=$customer_name;
        $row['mobile_no']=$mobile_no;
        $row['crop_name']=$crop_name;
        $row['crop_type_name']=$crop_type_name;
        $row['variety_name']=$variety_name;
        $row['pack_size']=$pack_size;
        return $row;
    }
    private function reset_row_variety($info)
    {
        foreach($info  as $key=>$r)
        {
            if(!(($key=='crop_name')||($key=='crop_type_name')||($key=='variety_name')||($key=='pack_size')||($key=='customer_name')||($key=='mobile_no')))
            {
                $info[$key]=0;
            }
        }
        return $info;
    }
    private function get_row_variety($info)
    {
        $row=array();
        foreach($info  as $key=>$r)
        {
            if(substr($key,-3)=='pkt')
            {
                if($info[$key]==0)
                {
                    $row[$key]='';
                }
                else
                {
                    $row[$key]=$info[$key];
                }
            }
            elseif(substr($key,-2)=='kg')
            {
                if($info[$key]==0)
                {
                    $row[$key]='';
                }
                else
                {
                    $row[$key]=number_format($info[$key],3,'.','');
                }
            }
            elseif(substr($key,0,6)=='amount')
            {
                if($info[$key]==0)
                {
                    $row[$key]='';
                }
                else
                {
                    $row[$key]=number_format($info[$key],2);
                }
            }
            else
            {
                $row[$key]=$info[$key];
            }

        }
        return $row;

        /*$row=array();
        $row['customer_name']=$info['customer_name'];
        $row['mobile_no']=$info['mobile_no'];
        $row['crop_name']=$info['crop_name'];
        $row['crop_type_name']=$info['crop_type_name'];
        $row['variety_name']=$info['variety_name'];
        $row['pack_size']=$info['pack_size'];
        if($info['quantity_pkt']==0)
        {
            $row['quantity_pkt']='';
        }
        else
        {
            $row['quantity_pkt']=$info['quantity_pkt'];
        }
        if($info['quantity_kg']==0)
        {
            $row['quantity_kg']='';
        }
        else
        {
            $row['quantity_kg']=number_format($info['quantity_kg'],3,'.','');
        }
        $row['num_invoice']=$info['num_invoice'];
        return $row;*/
    }
    private function system_set_preference_variety()
    {
        if(isset($this->permissions['action6']) && ($this->permissions['action6']==1))
        {
            $data['system_preference_items']= $this->get_preference_variety();
            $data['preference_method_name']='search_variety';
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("preference_add_edit",$data,true));
            $ajax['system_page_url']=site_url($this->controller_url.'/index/set_preference_variety');
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
