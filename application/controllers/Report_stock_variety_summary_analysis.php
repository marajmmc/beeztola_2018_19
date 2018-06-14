<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Report_stock_variety_summary_analysis extends Root_Controller
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
        $this->permissions=User_helper::get_permission('Report_stock_variety_summary_analysis');
        $this->controller_url='report_stock_variety_summary_analysis';
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
            $data['title']="Current Stock Analysis Report Search";
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
            $data['options']=$reports;
            $data['system_preference_items']= $this->get_preference();
            $data['title']="Outlet Current Stock Analysis Report";
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
        $crop_type_id=$this->input->post('crop_type_id');
        $variety_id=$this->input->post('variety_id');
        $pack_size_id=$this->input->post('pack_size_id');
        $items=array();
        $this->db->from($this->config->item('table_pos_stock_summary_variety').' stock_summary_variety');
        $this->db->select('stock_summary_variety.*');
        $this->db->join($this->config->item('table_login_csetup_cus_info').' outlet_info','outlet_info.customer_id=stock_summary_variety.outlet_id AND outlet_info.revision=1 AND outlet_info.type="'.$this->config->item('system_customer_type_outlet_id').'"','INNER');
        $this->db->select('outlet_info.name outlet_name');
        $this->db->join($this->config->item('table_login_setup_classification_varieties').' v','v.id=stock_summary_variety.variety_id','INNER');
        $this->db->select('v.name variety_name');
        $this->db->join($this->config->item('table_login_setup_classification_crop_types').' crop_type','crop_type.id=v.crop_type_id','INNER');
        $this->db->select('crop_type.id crop_type_id, crop_type.name crop_type_name');
        $this->db->join($this->config->item('table_login_setup_classification_crops').' crop','crop.id=crop_type.crop_id','INNER');
        $this->db->select('crop.id crop_id, crop.name crop_name');
        $this->db->join($this->config->item('table_login_setup_classification_pack_size').' pack','pack.id=stock_summary_variety.pack_size_id','LEFT');
        $this->db->select('pack.name pack_size');
        $this->db->order_by('crop.ordering','ASC');
        $this->db->order_by('crop.id','ASC');
        $this->db->order_by('crop_type.ordering','ASC');
        $this->db->order_by('crop_type.id','ASC');
        $this->db->order_by('v.ordering','ASC');
        $this->db->order_by('v.id','ASC');
        $this->db->order_by('pack.id');

        $this->db->where('stock_summary_variety.outlet_id',$outlet_id);
        if($variety_id>0 && is_numeric($variety_id))
        {
            $this->db->where('stock_summary_variety.variety_id',$variety_id);
        }
        if($crop_type_id>0 && is_numeric($crop_type_id))
        {
            $this->db->where('v.crop_type_id',$crop_type_id);
        }

        if($crop_id>0 && is_numeric($crop_id))
        {
            $this->db->where('crop_type.crop_id',$crop_id);
        }
        if($pack_size_id>0 && is_numeric($pack_size_id))
        {
            $this->db->where('stock_summary_variety.pack_size_id',$pack_size_id);
        }
        $results=$this->db->get()->result_array();
        $varieties=array();
        foreach($results as $result)
        {
            $varieties[$result['variety_id']][$result['pack_size_id']]['crop_name']=$result['crop_name'];
            $varieties[$result['variety_id']][$result['pack_size_id']]['crop_type_name']=$result['crop_type_name'];
            $varieties[$result['variety_id']][$result['pack_size_id']]['variety_name']=$result['variety_name'];
            $varieties[$result['variety_id']][$result['pack_size_id']]['pack_size']=$result['pack_size'];
            $varieties[$result['variety_id']][$result['pack_size_id']]['in_wo_pkt']=$result['in_wo'];
            $varieties[$result['variety_id']][$result['pack_size_id']]['in_wo_kg']=($result['in_wo']*$result['pack_size'])/1000;
            $varieties[$result['variety_id']][$result['pack_size_id']]['in_wo_pkt']=$result['in_wo'];
            $varieties[$result['variety_id']][$result['pack_size_id']]['in_wo_kg']=($result['in_wo']*$result['pack_size'])/1000;
            $varieties[$result['variety_id']][$result['pack_size_id']]['out_ow_pkt']=$result['out_ow'];
            $varieties[$result['variety_id']][$result['pack_size_id']]['out_ow_kg']=($result['out_ow']*$result['pack_size'])/1000;
            $varieties[$result['variety_id']][$result['pack_size_id']]['out_sale_pkt']=$result['out_sale'];
            $varieties[$result['variety_id']][$result['pack_size_id']]['out_sale_kg']=($result['out_sale']*$result['pack_size'])/1000;
            $varieties[$result['variety_id']][$result['pack_size_id']]['current_stock_pkt']=$result['current_stock'];
            $varieties[$result['variety_id']][$result['pack_size_id']]['current_stock_kg']=($result['current_stock']*$result['pack_size'])/1000;
            $varieties[$result['variety_id']][$result['pack_size_id']]['current_stock_pkt_cal']=$result['in_wo']-$result['out_ow']-$result['out_sale'];
            $varieties[$result['variety_id']][$result['pack_size_id']]['current_stock_kg_cal']=(($result['in_wo']-$result['out_ow']-$result['out_sale'])*$result['pack_size'])/1000;;

        }
        $type_total=array();
        $crop_total=array();
        $grand_total=array();
        $type_total['crop_name']='';
        $type_total['crop_type_name']='';
        $type_total['variety_name']='Total Type';
        $crop_total['crop_name']='';
        $crop_total['crop_type_name']='Total Crop';
        $crop_total['variety_name']='';
        $grand_total['crop_name']='Grand Total';
        $grand_total['crop_type_name']='';
        $grand_total['variety_name']='';
        $grand_total['pack_size']=$crop_total['pack_size']=$type_total['pack_size']='';
        $grand_total['in_wo_pkt']=$crop_total['in_wo_pkt']=$type_total['in_wo_pkt']=0;
        $grand_total['in_wo_kg']=$crop_total['in_wo_kg']=$type_total['in_wo_kg']=0;
        $grand_total['out_ow_pkt']=$crop_total['out_ow_pkt']=$type_total['out_ow_pkt']=0;
        $grand_total['out_ow_kg']=$crop_total['out_ow_kg']=$type_total['out_ow_kg']=0;
        $grand_total['out_sale_pkt']=$crop_total['out_sale_pkt']=$type_total['out_sale_pkt']=0;
        $grand_total['out_sale_kg']=$crop_total['out_sale_kg']=$type_total['out_sale_kg']=0;
        $grand_total['current_stock_pkt']=$crop_total['current_stock_pkt']=$type_total['current_stock_pkt']=0;
        $grand_total['current_stock_pkt_cal']=$crop_total['current_stock_pkt_cal']=$type_total['current_stock_pkt_cal']=0;
        $grand_total['current_stock_kg']=$crop_total['current_stock_kg']=$type_total['current_stock_kg']=0;
        $grand_total['current_stock_kg_cal']=$crop_total['current_stock_kg_cal']=$type_total['current_stock_kg_cal']=0;
        $prev_crop_name='';
        $prev_type_name='';
        $first_row=true;
        foreach($varieties as $variety)
        {
            foreach($variety as $pack)
            {
                if(!$first_row)
                {
                    if($prev_crop_name!=$pack['crop_name'])
                    {
                        $items[]=$this->get_row($type_total);
                        $items[]=$this->get_row($crop_total);

                        $prev_crop_name=$pack['crop_name'];
                        $prev_type_name=$pack['crop_type_name'];
                        $type_total['current_stock_kg']=0;
                        $type_total['current_stock_pkt']=0;
                        $crop_total['current_stock_kg']=0;
                        $crop_total['current_stock_pkt']=0;
                    }
                    elseif($prev_type_name!=$pack['crop_type_name'])
                    {
                        $items[]=$this->get_row($type_total);
                        $pack['crop_name']='';
                        $prev_type_name=$pack['crop_type_name'];
                        $type_total['current_stock_kg']=0;
                        $type_total['current_stock_pkt']=0;
                    }
                    else
                    {
                        $pack['crop_name']='';
                        $pack['crop_type_name']='';
                    }
                }
                else
                {
                    $prev_crop_name=$pack['crop_name'];
                    $prev_type_name=$pack['crop_type_name'];
                    $first_row=false;
                }
                $type_total['in_wo_kg']+=$pack['in_wo_kg'];
                $type_total['in_wo_pkt']+=$pack['in_wo_pkt'];
                $crop_total['in_wo_kg']+=$pack['in_wo_kg'];
                $crop_total['in_wo_pkt']+=$pack['in_wo_pkt'];
                $grand_total['in_wo_kg']+=$pack['in_wo_kg'];
                $grand_total['in_wo_pkt']+=$pack['in_wo_pkt'];

                $type_total['out_ow_kg']+=$pack['out_ow_kg'];
                $type_total['out_ow_pkt']+=$pack['out_ow_pkt'];
                $crop_total['out_ow_kg']+=$pack['out_ow_kg'];
                $crop_total['out_ow_pkt']+=$pack['out_ow_pkt'];
                $grand_total['out_ow_kg']+=$pack['out_ow_kg'];
                $grand_total['out_ow_pkt']+=$pack['out_ow_pkt'];

                $type_total['out_sale_kg']+=$pack['out_sale_kg'];
                $type_total['out_sale_pkt']+=$pack['out_sale_pkt'];
                $crop_total['out_sale_kg']+=$pack['out_sale_kg'];
                $crop_total['out_sale_pkt']+=$pack['out_sale_pkt'];
                $grand_total['out_sale_kg']+=$pack['out_sale_kg'];
                $grand_total['out_sale_pkt']+=$pack['out_sale_pkt'];

                $type_total['current_stock_kg']+=$pack['current_stock_kg'];
                $type_total['current_stock_pkt']+=$pack['current_stock_pkt'];
                $crop_total['current_stock_kg']+=$pack['current_stock_kg'];
                $crop_total['current_stock_pkt']+=$pack['current_stock_pkt'];
                $grand_total['current_stock_kg']+=$pack['current_stock_kg'];
                $grand_total['current_stock_pkt']+=$pack['current_stock_pkt'];

                $type_total['current_stock_kg_cal']+=$pack['current_stock_kg_cal'];
                $type_total['current_stock_pkt_cal']+=$pack['current_stock_pkt_cal'];
                $crop_total['current_stock_kg_cal']+=$pack['current_stock_kg_cal'];
                $crop_total['current_stock_pkt_cal']+=$pack['current_stock_pkt_cal'];
                $grand_total['current_stock_kg_cal']+=$pack['current_stock_kg_cal'];
                $grand_total['current_stock_pkt_cal']+=$pack['current_stock_pkt_cal'];
                $items[]=$this->get_row($pack);
            }
        }
        $items[]=$this->get_row($type_total);
        $items[]=$this->get_row($crop_total);
        $items[]=$this->get_row($grand_total);
        $this->json_return($items);
        die();
    }
    private function get_row($info)
    {
        $row=array();
        $row['crop_name']=$info['crop_name'];
        $row['crop_type_name']=$info['crop_type_name'];
        $row['variety_name']=$info['variety_name'];
        $row['pack_size']=$info['pack_size'];
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

        if($info['current_stock_pkt']==0)
        {
            $row['current_stock_pkt']='';
        }
        else
        {
            $row['current_stock_pkt']=$info['current_stock_pkt'];
        }
        if($info['current_stock_kg']==0)
        {
            $row['current_stock_kg']='';
        }
        else
        {
            $row['current_stock_kg']=number_format($info['current_stock_kg'],3,'.','');
        }
        if($info['current_stock_pkt_cal']==0)
        {
            $row['current_stock_pkt_cal']='';
        }
        else
        {
            $row['current_stock_pkt_cal']=$info['current_stock_pkt_cal'];
        }
        if($info['current_stock_kg_cal']==0)
        {
            $row['current_stock_kg_cal']='';
        }
        else
        {
            $row['current_stock_kg_cal']=number_format($info['current_stock_kg_cal'],3,'.','');
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
    private function get_preference()
    {
        $user = User_helper::get_user();
        $result=Query_helper::get_info($this->config->item('table_system_user_preference'),'*',array('user_id ='.$user->user_id,'controller ="' .$this->controller_url.'"','method ="search"'),1);
        $data['crop_name']= 1;
        $data['crop_type_name']= 1;
        $data['variety_name']= 1;
        $data['pack_size']= 1;
        $data['in_wo_pkt']= 1;
        $data['in_wo_kg']= 1;
        $data['out_ow_pkt']= 1;
        $data['out_ow_kg']= 1;
        $data['out_sale_pkt']= 1;
        $data['out_sale_kg']= 1;
        $data['current_stock_pkt']= 1;
        $data['current_stock_pkt_cal']= 1;
        $data['current_stock_kg']= 1;
        $data['current_stock_kg_cal']= 1;
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
