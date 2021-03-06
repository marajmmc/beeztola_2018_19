<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Report_sale_variety extends Root_Controller
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
        $this->permissions=User_helper::get_permission('Report_sale_variety');
        $this->controller_url='report_sale_variety';
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
        elseif($action=="get_items_quantity")
        {
            $this->system_get_items_quantity();
        }
        elseif($action=="get_items_invoice")
        {
            $this->system_get_items_invoice();
        }
        elseif($action=="get_items_farmer")
        {
            $this->system_get_items_farmer();
        }
        elseif($action=="set_preference_quantity")
        {
            $this->system_set_preference_quantity();
        }
        elseif($action=="set_preference_invoice")
        {
            $this->system_set_preference_invoice();
        }
        elseif($action=="set_preference_farmer")
        {
            $this->system_set_preference_farmer();
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
            if($reports['report_name']=='quantity')
            {
                $data['system_preference_items']= $this->get_preference_quantity();
                $data['title']="Variety Quantity Wise Sales Report";
                $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>$this->load->view($this->controller_url."/list_quantity",$data,true));
            }
            else if($reports['report_name']=='invoice')
            {
                $data['system_preference_items']= $this->get_preference_invoice();
                $data['title']="Variety Invoice Wise Sales Report";
                $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>$this->load->view($this->controller_url."/list_invoice",$data,true));
            }
            else if($reports['report_name']=='farmer')
            {
                $data['system_preference_items']= $this->get_preference_farmer();
                $data['title']="Variety Customer Wise Sales Report";
                $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>$this->load->view($this->controller_url."/list_farmer",$data,true));
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
    private function get_preference_headers_quantity()
    {
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
        return $data;
    }

    private function get_preference_quantity()
    {
        $user = User_helper::get_user();
        $result=Query_helper::get_info($this->config->item('table_system_user_preference'),'*',array('user_id ='.$user->user_id,'controller ="' .$this->controller_url.'"','method ="search_quantity"'),1);
        $data=$this->get_preference_headers_quantity();
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
    private function system_get_items_quantity()
    {
        $outlet_id=$this->input->post('outlet_id');
        $crop_id=$this->input->post('crop_id');
        $crop_type_id=$this->input->post('crop_type_id');
        $variety_id=$this->input->post('variety_id');
        $pack_size_id=$this->input->post('pack_size_id');
        //remember pack_size_id replaced in final foreach loop
        $date_end=$this->input->post('date_end');
        $date_start=$this->input->post('date_start');

        $this->db->from($this->config->item('table_login_setup_classification_varieties').' v');
        $this->db->select('v.id variety_id,v.name variety_name');
        $this->db->join($this->config->item('table_login_setup_classification_crop_types').' crop_type','crop_type.id=v.crop_type_id','INNER');
        $this->db->select('crop_type.id crop_type_id, crop_type.name crop_type_name');
        $this->db->join($this->config->item('table_login_setup_classification_crops').' crop','crop.id=crop_type.crop_id','INNER');
        $this->db->select('crop.id crop_id, crop.name crop_name');
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
        $this->db->order_by('crop.ordering','ASC');
        $this->db->order_by('crop.id','ASC');
        $this->db->order_by('crop_type.ordering','ASC');
        $this->db->order_by('crop_type.id','ASC');
        $this->db->order_by('v.ordering','ASC');
        $this->db->order_by('v.id','ASC');

        $varieties=$this->db->get()->result_array();
        $variety_ids=array();
        $variety_ids[0]=0;
        foreach($varieties as $result)
        {
            $variety_ids[$result['variety_id']]=$result['variety_id'];
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

        $this->db->where('sale.outlet_id',$outlet_id);
        $this->db->where_in('details.variety_id',$variety_ids);
        if($pack_size_id>0)
        {
            $this->db->where('details.pack_size_id',$pack_size_id);
        }
        $this->db->group_by('details.variety_id');
        $this->db->group_by('details.pack_size_id');
        $results=$this->db->get()->result_array();
        $sales=array();
        foreach($results as $result)
        {
            $sales[$result['variety_id']][$result['pack_size_id']]=$result;
        }



        //final items
        $type_total=$this->initialize_row_quantity('','','Total Type','');
        $crop_total=$this->initialize_row_quantity('','Total Crop','','');
        $grand_total=$this->initialize_row_quantity('Grand Total','','','');

        $prev_crop_name='';
        $prev_type_name='';
        $first_row=true;
        $items=array();
        foreach($varieties as $variety)
        {
            if(isset($sales[$variety['variety_id']]))
            {
                foreach($sales[$variety['variety_id']] as $pack_size_id=>$sale_details)
                {
                    $info=$this->initialize_row_quantity($variety['crop_name'],$variety['crop_type_name'],$variety['variety_name'],$sale_details['pack_size']);
                    if(!$first_row)
                    {
                        if($prev_crop_name!=$variety['crop_name'])
                        {
                            $items[]=$this->get_row_quantity($type_total);
                            $items[]=$this->get_row_quantity($crop_total);
                            $type_total=$this->reset_row_quantity($type_total);
                            $crop_total=$this->reset_row_quantity($crop_total);

                            $prev_crop_name=$variety['crop_name'];
                            $prev_type_name=$variety['crop_type_name'];


                        }
                        elseif($prev_type_name!=$variety['crop_type_name'])
                        {
                            $items[]=$this->get_row_quantity($type_total);
                            $type_total=$this->reset_row_quantity($type_total);

                            $info['crop_name']='';
                            $prev_type_name=$variety['crop_type_name'];
                        }
                        else
                        {
                            $info['crop_name']='';
                            $info['crop_type_name']='';
                        }
                    }
                    else
                    {
                        $prev_crop_name=$variety['crop_name'];
                        $prev_type_name=$variety['crop_type_name'];
                        $first_row=false;
                    }
                    $result=$sale_details;
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
                        if(!(($key=='crop_name')||($key=='crop_type_name')||($key=='variety_name')||($key=='pack_size')))
                        {
                            $type_total[$key]+=$info[$key];
                            $crop_total[$key]+=$info[$key];
                            $grand_total[$key]+=$info[$key];
                        }
                    }
                    /*$info['quantity_pkt']=$sale_details['quantity'];
                    $info['quantity_kg']=$sale_details['quantity']*$sale_details['pack_size']/1000;

                    $type_total['quantity_pkt']+=$info['quantity_pkt'];
                    $type_total['quantity_kg']+=$info['quantity_kg'];
                    $crop_total['quantity_pkt']+=$info['quantity_pkt'];
                    $crop_total['quantity_kg']+=$info['quantity_kg'];
                    $grand_total['quantity_pkt']+=$info['quantity_pkt'];
                    $grand_total['quantity_kg']+=$info['quantity_kg'];*/
                    $items[]=$this->get_row_quantity($info);
                }
            }


        }
        $items[]=$this->get_row_quantity($type_total);
        $items[]=$this->get_row_quantity($crop_total);
        $items[]=$this->get_row_quantity($grand_total);
        $this->json_return($items);

    }
    private function initialize_row_quantity($crop_name,$crop_type_name,$variety_name,$pack_size)
    {
        $row=$this->get_preference_headers_quantity();
        foreach($row  as $key=>$r)
        {
            $row[$key]=0;
        }
        $row['crop_name']=$crop_name;
        $row['crop_type_name']=$crop_type_name;
        $row['variety_name']=$variety_name;
        $row['pack_size']=$pack_size;
        return $row;
    }
    private function reset_row_quantity($info)
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
    private function get_row_quantity($info)
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
    }
    private function system_set_preference_quantity()
    {
        if(isset($this->permissions['action6']) && ($this->permissions['action6']==1))
        {
            $data['system_preference_items']= $this->get_preference_quantity();
            $data['preference_method_name']='search_quantity';
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("preference_add_edit",$data,true));
            $ajax['system_page_url']=site_url($this->controller_url.'/index/set_preference_quantity');
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
        $data['crop_name']= 1;
        $data['crop_type_name']= 1;
        $data['variety_name']= 1;
        $data['pack_size']= 1;
        $data['invoice_no']= 1;
        $data['quantity_pkt']= 1;
        $data['quantity_kg']= 1;
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
        $crop_id=$this->input->post('crop_id');
        $crop_type_id=$this->input->post('crop_type_id');
        $variety_id=$this->input->post('variety_id');
        $pack_size_id=$this->input->post('pack_size_id');
        //remember pack_size_id replaced in final foreach loop
        $date_end=$this->input->post('date_end');
        $date_start=$this->input->post('date_start');

        $this->db->from($this->config->item('table_login_setup_classification_varieties').' v');
        $this->db->select('v.id variety_id,v.name variety_name');
        $this->db->join($this->config->item('table_login_setup_classification_crop_types').' crop_type','crop_type.id=v.crop_type_id','INNER');
        $this->db->select('crop_type.id crop_type_id, crop_type.name crop_type_name');
        $this->db->join($this->config->item('table_login_setup_classification_crops').' crop','crop.id=crop_type.crop_id','INNER');
        $this->db->select('crop.id crop_id, crop.name crop_name');
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
        $this->db->order_by('crop.ordering','ASC');
        $this->db->order_by('crop.id','ASC');
        $this->db->order_by('crop_type.ordering','ASC');
        $this->db->order_by('crop_type.id','ASC');
        $this->db->order_by('v.ordering','ASC');
        $this->db->order_by('v.id','ASC');

        $varieties=$this->db->get()->result_array();
        $variety_ids=array();
        $variety_ids[0]=0;
        foreach($varieties as $result)
        {
            $variety_ids[$result['variety_id']]=$result['variety_id'];
        }

        $this->db->from($this->config->item('table_pos_sale_details').' details');
        $this->db->select('details.variety_id,details.pack_size_id,details.pack_size,details.sale_id,details.quantity');
        $this->db->join($this->config->item('table_pos_sale').' sale','sale.id = details.sale_id','INNER');
        $this->db->where('sale.status',$this->config->item('system_status_active'));
        $this->db->where('sale.date_sale >=',$date_start);
        $this->db->where('sale.date_sale <=',$date_end);
        $this->db->where('sale.outlet_id',$outlet_id);
        $this->db->where_in('details.variety_id',$variety_ids);
        if($pack_size_id>0)
        {
            $this->db->where('details.pack_size_id',$pack_size_id);
        }
        $this->db->order_by('details.sale_id','DESC');
        $results=$this->db->get()->result_array();
        $sales=array();
        foreach($results as $result)
        {
            $sales[$result['variety_id']][$result['pack_size_id']][]=$result;
        }
        //final items
        $type_total=$this->initialize_row_invoice('','','Total Type','');
        $crop_total=$this->initialize_row_invoice('','Total Crop','','');
        $grand_total=$this->initialize_row_invoice('Grand Total','','','');

        $prev_crop_name='';
        $prev_type_name='';
        $first_row=true;
        $items=array();
        foreach($varieties as $variety)
        {
            if(isset($sales[$variety['variety_id']]))
            {
                foreach($sales[$variety['variety_id']] as $pack_size_id=>$sale_details)
                {
                    foreach($sale_details as $i=>$single_invoice)
                    {
                        $info=$this->initialize_row_invoice($variety['crop_name'],$variety['crop_type_name'],$variety['variety_name'],$single_invoice['pack_size']);
                        if(!$first_row)
                        {
                            if($prev_crop_name!=$variety['crop_name'])
                            {
                                $items[]=$this->get_row_invoice($type_total);
                                $items[]=$this->get_row_invoice($crop_total);
                                $type_total=$this->reset_row_invoice($type_total);
                                $crop_total=$this->reset_row_invoice($crop_total);

                                $prev_crop_name=$variety['crop_name'];
                                $prev_type_name=$variety['crop_type_name'];


                            }
                            elseif($prev_type_name!=$variety['crop_type_name'])
                            {
                                $items[]=$this->get_row_invoice($type_total);
                                $type_total=$this->reset_row_invoice($type_total);

                                $info['crop_name']='';
                                $prev_type_name=$variety['crop_type_name'];
                            }
                            else
                            {
                                $info['crop_name']='';
                                $info['crop_type_name']='';
                            }
                        }
                        else
                        {
                            $prev_crop_name=$variety['crop_name'];
                            $prev_type_name=$variety['crop_type_name'];
                            $first_row=false;
                        }
                        if($i>0)
                        {
                            $info['variety_name']='';
                            $info['pack_size']='';
                        }
                        $info['invoice_no']=Barcode_helper::get_barcode_sales($single_invoice['sale_id']);
                        $info['quantity_pkt']=$single_invoice['quantity'];
                        $info['quantity_kg']=$single_invoice['quantity']*$single_invoice['pack_size']/1000;

                        $type_total['invoice_no']++;
                        $crop_total['invoice_no']++;
                        $grand_total['invoice_no']++;
                        $type_total['quantity_pkt']+=$info['quantity_pkt'];
                        $type_total['quantity_kg']+=$info['quantity_kg'];
                        $crop_total['quantity_pkt']+=$info['quantity_pkt'];
                        $crop_total['quantity_kg']+=$info['quantity_kg'];
                        $grand_total['quantity_pkt']+=$info['quantity_pkt'];
                        $grand_total['quantity_kg']+=$info['quantity_kg'];


                        $items[]=$this->get_row_invoice($info);
                    }
                }
            }


        }
        $items[]=$this->get_row_invoice($type_total);
        $items[]=$this->get_row_invoice($crop_total);
        $items[]=$this->get_row_invoice($grand_total);
        $this->json_return($items);
    }
    private function initialize_row_invoice($crop_name,$crop_type_name,$variety_name,$pack_size)
    {
        $row=array();
        $row['crop_name']=$crop_name;
        $row['crop_type_name']=$crop_type_name;
        $row['variety_name']=$variety_name;
        $row['pack_size']=$pack_size;
        $row['invoice_no']=0;
        $row['quantity_pkt']=0;
        $row['quantity_kg']=0;
        return $row;
    }
    private function reset_row_invoice($row)
    {
        $row['invoice_no']=0;
        $row['quantity_pkt']=0;
        $row['quantity_kg']=0;
        return $row;
    }
    private function get_row_invoice($info)
    {
        $row=array();
        $row['crop_name']=$info['crop_name'];
        $row['crop_type_name']=$info['crop_type_name'];
        $row['variety_name']=$info['variety_name'];
        $row['pack_size']=$info['pack_size'];
        $row['invoice_no']=$info['invoice_no'];
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
    private function get_preference_farmer()
    {
        $user = User_helper::get_user();
        $result=Query_helper::get_info($this->config->item('table_system_user_preference'),'*',array('user_id ='.$user->user_id,'controller ="' .$this->controller_url.'"','method ="search_farmer"'),1);
        $data['crop_name']= 1;
        $data['crop_type_name']= 1;
        $data['variety_name']= 1;
        $data['pack_size']= 1;
        $data['customer_name']= 1;
        $data['mobile_no']= 1;
        $data['quantity_pkt']= 1;
        $data['quantity_kg']= 1;
        $data['num_invoice']= 1;
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
    private function system_get_items_farmer()
    {
        $outlet_id=$this->input->post('outlet_id');
        $crop_id=$this->input->post('crop_id');
        $crop_type_id=$this->input->post('crop_type_id');
        $variety_id=$this->input->post('variety_id');
        $pack_size_id=$this->input->post('pack_size_id');
        $farmer_type_id=$this->input->post('farmer_type_id');
        //remember pack_size_id replaced in final foreach loop
        $date_end=$this->input->post('date_end');
        $date_start=$this->input->post('date_start');

        $this->db->from($this->config->item('table_login_setup_classification_varieties').' v');
        $this->db->select('v.id variety_id,v.name variety_name');
        $this->db->join($this->config->item('table_login_setup_classification_crop_types').' crop_type','crop_type.id=v.crop_type_id','INNER');
        $this->db->select('crop_type.id crop_type_id, crop_type.name crop_type_name');
        $this->db->join($this->config->item('table_login_setup_classification_crops').' crop','crop.id=crop_type.crop_id','INNER');
        $this->db->select('crop.id crop_id, crop.name crop_name');
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
        $this->db->order_by('crop.ordering','ASC');
        $this->db->order_by('crop.id','ASC');
        $this->db->order_by('crop_type.ordering','ASC');
        $this->db->order_by('crop_type.id','ASC');
        $this->db->order_by('v.ordering','ASC');
        $this->db->order_by('v.id','ASC');

        $varieties=$this->db->get()->result_array();
        $variety_ids=array();
        $variety_ids[0]=0;
        foreach($varieties as $result)
        {
            $variety_ids[$result['variety_id']]=$result['variety_id'];
        }

        $this->db->from($this->config->item('table_pos_sale_details').' details');
        $this->db->select('details.variety_id,details.pack_size_id,details.pack_size');
        $this->db->select('SUM(details.quantity) quantity',false);
        $this->db->select('COUNT(details.id) num_invoice',false);
        $this->db->join($this->config->item('table_pos_sale').' sale','sale.id = details.sale_id','INNER');
        $this->db->select('sale.farmer_id');
        $this->db->join($this->config->item('table_pos_setup_farmer_farmer').' farmer','farmer.id = sale.farmer_id','INNER');
        $this->db->select('farmer.name customer_name,farmer.mobile_no');
        $this->db->where('sale.status',$this->config->item('system_status_active'));
        $this->db->where('sale.date_sale >=',$date_start);
        $this->db->where('sale.date_sale <=',$date_end);
        $this->db->where('sale.outlet_id',$outlet_id);
        $this->db->where_in('details.variety_id',$variety_ids);
        if($pack_size_id>0)
        {
            $this->db->where('details.pack_size_id',$pack_size_id);
        }
        if($farmer_type_id>0)
        {
           $this->db->where('farmer.farmer_type_id',$farmer_type_id);
        }
        $this->db->group_by('details.variety_id');
        $this->db->group_by('details.pack_size_id');
        $this->db->group_by('sale.farmer_id');
        $results=$this->db->get()->result_array();
        $sales=array();
        foreach($results as $result)
        {
            $sales[$result['variety_id']][$result['pack_size_id']][$result['farmer_id']]=$result;
        }
        //final items
        $type_total=$this->initialize_row_farmer('','','Total Type','');
        $crop_total=$this->initialize_row_farmer('','Total Crop','','');
        $grand_total=$this->initialize_row_farmer('Grand Total','','','');

        $prev_crop_name='';
        $prev_type_name='';
        $first_row=true;
        $items=array();
        foreach($varieties as $variety)
        {
            if(isset($sales[$variety['variety_id']]))
            {
                foreach($sales[$variety['variety_id']] as $pack_size_id=>$sale_details)
                {
                    $i=0;
                    foreach($sale_details as $single_customer)
                    {
                        $info=$this->initialize_row_farmer($variety['crop_name'],$variety['crop_type_name'],$variety['variety_name'],$single_customer['pack_size']);
                        if(!$first_row)
                        {
                            if($prev_crop_name!=$variety['crop_name'])
                            {
                                $items[]=$this->get_row_farmer($type_total);
                                $items[]=$this->get_row_farmer($crop_total);
                                $type_total=$this->reset_row_farmer($type_total);
                                $crop_total=$this->reset_row_farmer($crop_total);

                                $prev_crop_name=$variety['crop_name'];
                                $prev_type_name=$variety['crop_type_name'];


                            }
                            elseif($prev_type_name!=$variety['crop_type_name'])
                            {
                                $items[]=$this->get_row_farmer($type_total);
                                $type_total=$this->reset_row_farmer($type_total);

                                $info['crop_name']='';
                                $prev_type_name=$variety['crop_type_name'];
                            }
                            else
                            {
                                $info['crop_name']='';
                                $info['crop_type_name']='';
                            }
                        }
                        else
                        {
                            $prev_crop_name=$variety['crop_name'];
                            $prev_type_name=$variety['crop_type_name'];
                            $first_row=false;
                        }
                        if($i>0)
                        {
                            $info['variety_name']='';
                            $info['pack_size']='';
                        }
                        $i++;
                        $info['customer_name']=Barcode_helper::get_barcode_sales($single_customer['customer_name']);
                        $info['mobile_no']=$single_customer['mobile_no'];
                        $info['quantity_pkt']=$single_customer['quantity'];
                        $info['quantity_kg']=$single_customer['quantity']*$single_customer['pack_size']/1000;
                        $info['num_invoice']=$single_customer['num_invoice'];

                        $type_total['customer_name']++;
                        $crop_total['customer_name']++;
                        $grand_total['customer_name']++;
                        $type_total['quantity_pkt']+=$info['quantity_pkt'];
                        $type_total['quantity_kg']+=$info['quantity_kg'];
                        $crop_total['quantity_pkt']+=$info['quantity_pkt'];
                        $crop_total['quantity_kg']+=$info['quantity_kg'];
                        $grand_total['quantity_pkt']+=$info['quantity_pkt'];
                        $grand_total['quantity_kg']+=$info['quantity_kg'];
                        $type_total['num_invoice']+=$info['num_invoice'];
                        $crop_total['num_invoice']+=$info['num_invoice'];
                        $grand_total['num_invoice']+=$info['num_invoice'];
                        $items[]=$this->get_row_farmer($info);
                    }
                }
            }


        }
        $items[]=$this->get_row_farmer($type_total);
        $items[]=$this->get_row_farmer($crop_total);
        $items[]=$this->get_row_farmer($grand_total);
        $this->json_return($items);
    }
    private function initialize_row_farmer($crop_name,$crop_type_name,$variety_name,$pack_size)
    {
        $row=array();
        $row['crop_name']=$crop_name;
        $row['crop_type_name']=$crop_type_name;
        $row['variety_name']=$variety_name;
        $row['pack_size']=$pack_size;
        $row['customer_name']=0;
        $row['mobile_no']='';
        $row['quantity_pkt']=0;
        $row['quantity_kg']=0;
        $row['num_invoice']=0;
        return $row;
    }
    private function reset_row_farmer($row)
    {
        $row['customer_name']=0;
        $row['quantity_pkt']=0;
        $row['quantity_kg']=0;
        $row['num_invoice']=0;
        return $row;
    }
    private function get_row_farmer($info)
    {
        $row=array();
        $row['crop_name']=$info['crop_name'];
        $row['crop_type_name']=$info['crop_type_name'];
        $row['variety_name']=$info['variety_name'];
        $row['pack_size']=$info['pack_size'];
        $row['customer_name']=$info['customer_name'];
        $row['mobile_no']=$info['mobile_no'];
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
        return $row;
    }
    private function system_set_preference_farmer()
    {
        if(isset($this->permissions['action6']) && ($this->permissions['action6']==1))
        {
            $data['system_preference_items']= $this->get_preference_farmer();
            $data['preference_method_name']='search_farmer';
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("preference_add_edit",$data,true));
            $ajax['system_page_url']=site_url($this->controller_url.'/index/set_preference_farmer');
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
