<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Report_stock_variety_details extends Root_Controller
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
        $this->permissions=User_helper::get_permission('Report_stock_variety_details');
        $this->controller_url='report_stock_variety_details';
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
        $this->lang->load('report_stock_variety_details');
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
            $data['pack_sizes']=Query_helper::get_info($this->config->item('table_login_setup_classification_pack_size'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"'),0,0,array('name ASC'));
            $data['title']="Outlet Current Stock Report Search";
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
            $data['system_preference_items']= $this->get_preference();
            $data['title']="Outlet Current Stock Report";
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
    private function get_preference()
    {
        $user = User_helper::get_user();
        $result=Query_helper::get_info($this->config->item('table_system_user_preference'),'*',array('user_id ='.$user->user_id,'controller ="' .$this->controller_url.'"','method ="search"'),1);
        $data['crop_name']= 1;
        $data['crop_type_name']= 1;
        $data['variety_name']= 1;
        $data['pack_size']= 1;
        $data['opening_stock_pkt']= 1;
        $data['opening_stock_kg']= 1;
        $data['in_wo_pkt']= 1;
        $data['in_wo_kg']= 1;
        $data['out_ow_pkt']= 1;
        $data['out_ow_kg']= 1;
        $data['out_sale_pkt']= 1;
        $data['out_sale_kg']= 1;
        $data['current_stock_pkt']= 1;
        $data['current_stock_kg']= 1;
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
    private function system_get_items()
    {
        //if transfer oo includes insert value in $stock_in
        //in initialize all values
        $outlet_id=$this->input->post('outlet_id');
        $crop_id=$this->input->post('crop_id');
        $crop_type_id=$this->input->post('crop_type_id');
        $variety_id=$this->input->post('variety_id');
        $pack_size_id=$this->input->post('pack_size_id');
        //remember pack_size_id replaced in final foreach loop
        $date_end=$this->input->post('date_end');
        $date_start=$this->input->post('date_start');
        //get variety ids form input

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

        $pack_sizes=array();
        $results=Query_helper::get_info($this->config->item('table_login_setup_classification_pack_size'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"'));
        foreach($results as $result)
        {
            $pack_sizes[$result['value']]=$result['text'];
        }
        //to wo
        $this->db->from($this->config->item('table_sms_transfer_wo_details').' details');
        $this->db->select('details.variety_id,details.pack_size_id');

        $this->db->select('SUM(CASE WHEN wo.date_receive<'.$date_start.' then details.quantity_receive ELSE 0 END) in_wo_opening',false);

        $this->db->select('SUM(CASE WHEN wo.date_receive>='.$date_start.' and wo.date_receive<='.$date_end.' then details.quantity_receive ELSE 0 END) in_wo',false);



        $this->db->join($this->config->item('table_sms_transfer_wo').' wo','wo.id=details.transfer_wo_id','INNER');
        $this->db->where('wo.status !=',$this->config->item('system_status_delete'));
        $this->db->where('details.status !=',$this->config->item('system_status_delete'));
        $this->db->where('wo.status_receive',$this->config->item('system_status_received'));
        $this->db->where_in('details.variety_id',$variety_ids);
        $this->db->where('wo.outlet_id',$outlet_id);
        if($pack_size_id>0)
        {
            $this->db->where('details.pack_size_id',$pack_size_id);
        }
        $this->db->group_by('details.variety_id');
        $this->db->group_by('details.pack_size_id');
        $results=$this->db->get()->result_array();
        $stock_in=array();
        foreach($results as $result)
        {
            $stock_in[$result['variety_id']][$result['pack_size_id']]['in_wo_opening']=$result['in_wo_opening'];
            $stock_in[$result['variety_id']][$result['pack_size_id']]['in_wo']=$result['in_wo'];
            //initialize in oo if includes
        }
        //write oo code
        //if not in $stock_in array initialize in_wo as 0
        //return hq ow
        $this->db->from($this->config->item('table_sms_transfer_ow_details').' details');
        $this->db->select('details.variety_id,details.pack_size_id');

        $this->db->select('SUM(CASE WHEN ow.date_delivery<'.$date_start.' then details.quantity_approve ELSE 0 END) out_ow_opening',false);

        $this->db->select('SUM(CASE WHEN ow.date_delivery>='.$date_start.' and ow.date_delivery<='.$date_end.' then details.quantity_approve ELSE 0 END) out_ow',false);



        $this->db->join($this->config->item('table_sms_transfer_ow').' ow','ow.id=details.transfer_ow_id','INNER');
        $this->db->where('ow.status !=',$this->config->item('system_status_delete'));
        $this->db->where('details.status !=',$this->config->item('system_status_delete'));
        $this->db->where('ow.status_delivery',$this->config->item('system_status_delivered'));
        $this->db->where_in('details.variety_id',$variety_ids);
        $this->db->where('ow.outlet_id',$outlet_id);
        if($pack_size_id>0)
        {
            $this->db->where('details.pack_size_id',$pack_size_id);
        }
        $this->db->group_by('details.variety_id');
        $this->db->group_by('details.pack_size_id');
        $results=$this->db->get()->result_array();
        $out_ow=array();
        foreach($results as $result)
        {
            $out_ow[$result['variety_id']][$result['pack_size_id']]['out_ow_opening']=$result['out_ow_opening'];
            $out_ow[$result['variety_id']][$result['pack_size_id']]['out_ow']=$result['out_ow'];
            //initialize in oo if includes
        }
        //sales
        $this->db->from($this->config->item('table_pos_sale_details').' details');
        $this->db->select('details.variety_id,details.pack_size_id');

        $this->db->select('SUM(CASE WHEN sale.date_sale<'.$date_start.' then details.quantity ELSE 0 END) sale_opening',false);
        $this->db->select('SUM(CASE WHEN sale.date_sale<'.$date_start.' and sale.status="'.$this->config->item('system_status_inactive').'" then details.quantity ELSE 0 END) sale_cancel_opening',false);

        $this->db->select('SUM(CASE WHEN sale.date_sale>='.$date_start.' and sale.date_sale<='.$date_end.' then details.quantity ELSE 0 END) sale',false);
        $this->db->select('SUM(CASE WHEN sale.date_sale>='.$date_start.' and sale.date_sale<='.$date_end.' and sale.status="'.$this->config->item('system_status_inactive').'" then details.quantity ELSE 0 END) sale_cancel',false);


        $this->db->join($this->config->item('table_pos_sale').' sale','sale.id=details.sale_id','INNER');
        $this->db->where('sale.status !=',$this->config->item('system_status_delete'));
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
            $sales[$result['variety_id']][$result['pack_size_id']]['out_sale_opening']=($result['sale_opening']-$result['sale_cancel_opening']);
            $sales[$result['variety_id']][$result['pack_size_id']]['out_sale']=($result['sale']-$result['sale_cancel']);
        }

        $type_total=$this->initialize_row('','','Total Type','');
        $crop_total=$this->initialize_row('','Total Crop','','');
        $grand_total=$this->initialize_row('Grand Total','','','');

        $prev_crop_name='';
        $prev_type_name='';
        $first_row=true;
        $items=array();
        foreach($varieties as $variety)
        {
            if(isset($stock_in[$variety['variety_id']]))
            {
                foreach($stock_in[$variety['variety_id']] as $pack_size_id=>$stock_in_details)
                {
                    $info=$this->initialize_row($variety['crop_name'],$variety['crop_type_name'],$variety['variety_name'],$pack_sizes[$pack_size_id]);
                    if(!$first_row)
                    {
                        if($prev_crop_name!=$variety['crop_name'])
                        {
                            $items[]=$this->get_row($type_total);
                            $items[]=$this->get_row($crop_total);
                            $type_total=$this->reset_row($type_total);
                            $crop_total=$this->reset_row($crop_total);

                            $prev_crop_name=$variety['crop_name'];
                            $prev_type_name=$variety['crop_type_name'];


                        }
                        elseif($prev_type_name!=$variety['crop_type_name'])
                        {
                            $items[]=$this->get_row($type_total);
                            $type_total=$this->reset_row($type_total);

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
                    $info['opening_stock_pkt']=$stock_in[$variety['variety_id']][$pack_size_id]['in_wo_opening'];
                    $info['in_wo_pkt']=$stock_in[$variety['variety_id']][$pack_size_id]['in_wo'];
                    $info['out_ow_pkt']=0;
                    if(isset($out_ow[$variety['variety_id']][$pack_size_id]))
                    {
                        $info['opening_stock_pkt']-=$out_ow[$variety['variety_id']][$pack_size_id]['out_ow_opening'];
                        $info['out_ow_pkt']+=$out_ow[$variety['variety_id']][$pack_size_id]['out_ow'];
                    }
                    $info['out_sale_pkt']=0;

                    if(isset($sales[$variety['variety_id']][$pack_size_id]))
                    {
                        $info['opening_stock_pkt']-=($sales[$variety['variety_id']][$pack_size_id]['out_sale_opening']);
                        $info['out_sale_pkt']=($sales[$variety['variety_id']][$pack_size_id]['out_sale']);
                    }

                    $info['opening_stock_kg']=$info['opening_stock_pkt']*$pack_sizes[$pack_size_id]/1000;
                    $type_total['opening_stock_pkt']+=$info['opening_stock_pkt'];
                    $type_total['opening_stock_kg']+=$info['opening_stock_kg'];
                    $crop_total['opening_stock_pkt']+=$info['opening_stock_pkt'];
                    $crop_total['opening_stock_kg']+=$info['opening_stock_kg'];
                    $grand_total['opening_stock_pkt']+=$info['opening_stock_pkt'];
                    $grand_total['opening_stock_kg']+=$info['opening_stock_kg'];

                    $info['in_wo_kg']=$info['in_wo_pkt']*$pack_sizes[$pack_size_id]/1000;
                    $type_total['in_wo_pkt']+=$info['in_wo_pkt'];
                    $type_total['in_wo_kg']+=$info['in_wo_kg'];
                    $crop_total['in_wo_pkt']+=$info['in_wo_pkt'];
                    $crop_total['in_wo_kg']+=$info['in_wo_kg'];
                    $grand_total['in_wo_pkt']+=$info['in_wo_pkt'];
                    $grand_total['in_wo_kg']+=$info['in_wo_kg'];

                    $info['out_ow_kg']=$info['out_ow_pkt']*$pack_sizes[$pack_size_id]/1000;
                    $type_total['out_ow_pkt']+=$info['out_ow_pkt'];
                    $type_total['out_ow_kg']+=$info['out_ow_kg'];
                    $crop_total['out_ow_pkt']+=$info['out_ow_pkt'];
                    $crop_total['out_ow_kg']+=$info['out_ow_kg'];
                    $grand_total['out_ow_pkt']+=$info['out_ow_pkt'];
                    $grand_total['out_ow_kg']+=$info['out_ow_kg'];

                    $info['out_sale_kg']=$info['out_sale_pkt']*$pack_sizes[$pack_size_id]/1000;
                    $type_total['out_sale_pkt']+=$info['out_sale_pkt'];
                    $type_total['out_sale_kg']+=$info['out_sale_kg'];
                    $crop_total['out_sale_pkt']+=$info['out_sale_pkt'];
                    $crop_total['out_sale_kg']+=$info['out_sale_kg'];
                    $grand_total['out_sale_pkt']+=$info['out_sale_pkt'];
                    $grand_total['out_sale_kg']+=$info['out_sale_kg'];

                    $items[]=$this->get_row($info);
                }
            }

        }
        $items[]=$this->get_row($type_total);
        $items[]=$this->get_row($crop_total);
        $items[]=$this->get_row($grand_total);
        $this->json_return($items);
        die();
    }
    private function initialize_row($crop_name,$crop_type_name,$variety_name,$pack_size)
    {
        $row=array();
        $row['crop_name']=$crop_name;
        $row['crop_type_name']=$crop_type_name;
        $row['variety_name']=$variety_name;
        $row['pack_size']=$pack_size;
        $row['opening_stock_pkt']=0;
        $row['opening_stock_kg']=0;
        $row['in_wo_pkt']=0;
        $row['in_wo_kg']=0;
        $row['out_ow_pkt']=0;
        $row['out_ow_kg']=0;
        $row['out_sale_pkt']=0;
        $row['out_sale_kg']=0;
        //$row['current_stock_pkt']=0;
        //$row['current_stock_kg']=0;
        return $row;
    }
    private function reset_row($row)
    {
        $row['opening_stock_pkt']=0;
        $row['opening_stock_kg']=0;
        $row['in_wo_pkt']=0;
        $row['in_wo_kg']=0;
        $row['out_ow_pkt']=0;
        $row['out_ow_kg']=0;
        $row['out_sale_pkt']=0;
        $row['out_sale_kg']=0;
        //$row['current_stock_pkt']=0;
        //$row['current_stock_kg']=0;
        return $row;
    }
    private function get_row($info)
    {
        $row=array();
        $row['crop_name']=$info['crop_name'];
        $row['crop_type_name']=$info['crop_type_name'];
        $row['variety_name']=$info['variety_name'];
        $row['pack_size']=$info['pack_size'];
        $row['current_stock_pkt']=$info['opening_stock_pkt']+$info['in_wo_pkt']-$info['out_ow_pkt']-$info['out_sale_pkt'];
        $row['current_stock_kg']=$info['opening_stock_kg']+$info['in_wo_kg']-$info['out_ow_kg']-$info['out_sale_kg'];
        if($info['opening_stock_pkt']==0)
        {
            $row['opening_stock_pkt']='';
        }
        else
        {
            $row['opening_stock_pkt']=$info['opening_stock_pkt'];
        }
        if($info['opening_stock_kg']==0)
        {
            $row['opening_stock_kg']='';
        }
        else
        {
            $row['opening_stock_kg']=number_format($info['opening_stock_kg'],3,'.','');
        }

        if($info['in_wo_pkt']==0)
        {
            $row['in_wo_pkt']='';
        }
        else
        {
            $row['in_wo_pkt']=$info['in_wo_pkt'];
        }
        if($info['in_wo_kg']==0)
        {
            $row['in_wo_kg']='';
        }
        else
        {
            $row['in_wo_kg']=number_format($info['in_wo_kg'],3,'.','');
        }
        if($info['out_ow_pkt']==0)
        {
            $row['out_ow_pkt']='';
        }
        else
        {
            $row['out_ow_pkt']=$info['out_ow_pkt'];
        }
        if($info['out_ow_kg']==0)
        {
            $row['out_ow_kg']='';
        }
        else
        {
            $row['out_ow_kg']=number_format($info['out_ow_kg'],3,'.','');
        }
        if($info['out_sale_pkt']==0)
        {
            $row['out_sale_pkt']='';
        }
        else
        {
            $row['out_sale_pkt']=$info['out_sale_pkt'];
        }
        if($info['out_sale_kg']==0)
        {
            $row['out_sale_kg']='';
        }
        else
        {
            $row['out_sale_kg']=number_format($info['out_sale_kg'],3,'.','');
        }
        if($row['current_stock_pkt']==0)
        {
            $row['current_stock_pkt']='';
        }
        if($row['current_stock_kg']==0)
        {
            $row['current_stock_kg']='';
        }
        else
        {
            $row['current_stock_kg']=number_format($row['current_stock_kg'],3,'.','');
        }
        return $row;
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

}
