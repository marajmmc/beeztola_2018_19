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
        elseif($action=="set_preference_quantity")
        {
            $this->system_set_preference_quantity();
        }
        elseif($action=="set_preference_invoice")
        {
            $this->system_set_preference_invoice();
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
    private function get_preference_quantity()
    {
        $user = User_helper::get_user();
        $result=Query_helper::get_info($this->config->item('table_system_user_preference'),'*',array('user_id ='.$user->user_id,'controller ="' .$this->controller_url.'"','method ="search_quantity"'),1);
        $data['crop_name']= 1;
        $data['crop_type_name']= 1;
        $data['variety_name']= 1;
        $data['pack_size']= 1;
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
        $this->db->order_by('crop.id','ASC');
        $this->db->order_by('crop_type.id','ASC');
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
        $this->db->join($this->config->item('table_pos_sale').' sale','sale.id = details.sale_id','INNER');
        $this->db->where('sale.status',$this->config->item('system_status_active'));
        $this->db->where('sale.date_sale >=',$date_start);
        $this->db->where('sale.date_sale <=',$date_end);
        $this->db->where('sale.outlet_id',$outlet_id);
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
                    $info['quantity_pkt']=$sale_details['quantity'];
                    $info['quantity_kg']=$sale_details['quantity']*$sale_details['pack_size']/1000;

                    $type_total['quantity_pkt']+=$info['quantity_pkt'];
                    $type_total['quantity_kg']+=$info['quantity_kg'];
                    $crop_total['quantity_pkt']+=$info['quantity_pkt'];
                    $crop_total['quantity_kg']+=$info['quantity_kg'];
                    $grand_total['quantity_pkt']+=$info['quantity_pkt'];
                    $grand_total['quantity_kg']+=$info['quantity_kg'];
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
        $row=array();
        $row['crop_name']=$crop_name;
        $row['crop_type_name']=$crop_type_name;
        $row['variety_name']=$variety_name;
        $row['pack_size']=$pack_size;
        $row['quantity_pkt']=0;
        $row['quantity_kg']=0;
        return $row;
    }
    private function reset_row_quantity($row)
    {
        $row['quantity_pkt']=0;
        $row['quantity_kg']=0;
        return $row;
    }
    private function get_row_quantity($info)
    {
        $row=array();
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
        $data['sl_no']= 1;
        $data['invoice_no']= 1;
        $data['customer_name']= 1;
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
        $date_end=$this->input->post('date_end');
        $date_start=$this->input->post('date_start');

        $this->db->from($this->config->item('table_pos_sale').' sale');
        $this->db->select('sale.*');
        $this->db->join($this->config->item('table_pos_setup_farmer_farmer').' f','f.id = sale.farmer_id','INNER');
        $this->db->select('f.name customer_name');

        $this->db->where('sale.outlet_id',$outlet_id);

        $where='(sale.date_sale >='.$date_start.' AND sale.date_sale <='.$date_end.')';
        $where.=' OR (sale.date_cancel >='.$date_start.' AND sale.date_cancel <='.$date_end.')';
        $this->db->where('('.$where.')');
        $this->db->order_by('sale.date_sale','DESC');

        $items=$this->db->get()->result_array();
        $grand_total=array();
        $grand_total['sl_no']='';
        $grand_total['invoice_no']='Grand Total';
        $grand_total['customer_name']='';
        $grand_total['date_sale']='';
        $grand_total['date_cancel']='';
        $grand_total['amount_total']=0;
        $grand_total['amount_discount_variety']=0;
        $grand_total['amount_discount_self']=0;
        $grand_total['amount_payable']=0;
        $grand_total['amount_payable_actual']=0;
        $grand_total['amount_actual']=0;
        $grand_total['status']='';
        $i=0;
        foreach($items as &$item)
        {
            $i++;
            if($item['status']==$this->config->item('system_status_active'))
            {
                $item['amount_actual']=$item['amount_payable_actual'];
            }
            else
            {
                if($item['date_sale']<$date_start)
                {
                    $item['amount_actual']=0-$item['amount_payable'];

                }
                elseif($item['date_cancel']>$date_end)
                {
                    $item['amount_actual']=$item['amount_payable_actual'];
                }
                else
                {
                    $item['amount_actual']=0;
                }
            }

            $item['sl_no']=$i;
            $item['invoice_no']=Barcode_helper::get_barcode_sales($item['id']);
            $item['date_sale']=System_helper::display_date($item['date_sale']);
            if($item['date_cancel']>0)
            {
                $item['date_canceled']=System_helper::display_date_time($item['date_cancel']);
            }
            else
            {
                $item['date_canceled']='';
            }
            $grand_total['amount_total']+=$item['amount_total'];
            $grand_total['amount_discount_variety']+=$item['amount_discount_variety'];
            $grand_total['amount_discount_self']+=$item['amount_discount_self'];
            $grand_total['amount_payable']+=$item['amount_payable'];
            $grand_total['amount_payable_actual']+=$item['amount_payable_actual'];
            $grand_total['amount_actual']+=$item['amount_actual'];



            $item['amount_total']=number_format($item['amount_total'],2);
            $item['amount_discount_variety']=number_format($item['amount_discount_variety'],2);
            $item['amount_discount_self']=number_format($item['amount_discount_self'],2);
            $item['amount_payable']=number_format($item['amount_payable'],2);
            $item['amount_payable_actual']=number_format($item['amount_payable_actual'],2);
            $item['amount_actual']=number_format($item['amount_actual'],2);

        }
        $grand_total['sl_no']=$i;
        $grand_total['amount_total']=number_format($grand_total['amount_total'],2);
        $grand_total['amount_discount_variety']=number_format($grand_total['amount_discount_variety'],2);
        $grand_total['amount_discount_self']=number_format($grand_total['amount_discount_self'],2);
        $grand_total['amount_payable']=number_format($grand_total['amount_payable'],2);
        $grand_total['amount_payable_actual']=number_format($grand_total['amount_payable_actual'],2);
        $grand_total['amount_actual']=number_format($grand_total['amount_actual'],2);

        $items[]=$grand_total;

        $this->json_return($items);
    }
    private function system_set_preference_invoice()
    {
        if(isset($this->permissions['action6']) && ($this->permissions['action6']==1))
        {
            $data['system_preference_items']= $this->get_preference_invoice();
            $data['preference_method_name']='search_invoice';
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
}
