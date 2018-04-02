<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Sales_cancel_request extends Root_Controller
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
        $this->permissions=User_helper::get_permission('Sales_cancel_request');
        $this->controller_url='sales_cancel_request';
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
    }
    public function index($action="list",$id=0)
    {
        if($action=="list")
        {
            $this->system_list();
        }
        elseif($action=="get_items")
        {
            $this->system_get_items();
        }
        elseif($action=="search")
        {
            $this->system_search();
        }
        elseif($action=="add")
        {
            $this->system_add();
        }
        elseif($action=="save")
        {
            $this->system_save();
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
            $this->system_list();
        }
    }
    private function system_list()
    {
        if(isset($this->permissions['action0'])&&($this->permissions['action0']==1))
        {
            $data['system_preference_items']= $this->get_preference();
            $data['title']="List of Sale Cancel Requests";
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/list",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url."/index/list");
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
        $current_records = $this->input->post('total_records');
        if(!$current_records)
        {
            $current_records=0;
        }
        $pagesize = $this->input->post('pagesize');
        if(!$pagesize)
        {
            $pagesize=100;
        }
        else
        {
            $pagesize=$pagesize*2;
        }
        $this->db->from($this->config->item('table_pos_sale_cancel').' cancel');
        $this->db->select('cancel.*');

        $this->db->join($this->config->item('table_pos_sale').' sale','sale.id=cancel.sale_id','INNER');
        $this->db->select('sale.date_sale,sale.amount_payable_actual amount_actual');
        $this->db->select('cus.name outlet_name');
        $this->db->select('f.name customer_name');
        $this->db->join($this->config->item('table_login_csetup_cus_info').' cus','cus.customer_id =sale.outlet_id AND cus.revision=1','INNER');
        $this->db->join($this->config->item('table_pos_setup_farmer_farmer').' f','f.id = sale.farmer_id','INNER');
        $this->db->where_in('sale.outlet_id',$this->user_outlet_ids);
        $this->db->order_by('cancel.id DESC');
        $this->db->limit($pagesize,$current_records);
        $items=$this->db->get()->result_array();
        foreach($items as &$item)
        {
            $item['invoice_no']=Barcode_helper::get_barcode_sales($item['sale_id']);
            $item['date_sale']=System_helper::display_date($item['date_sale']);
            $item['date_cancel']=System_helper::display_date($item['date_cancel']);
            $item['amount_actual']=number_format($item['amount_actual'],2);
        }
        $this->json_return($items);
    }
    private function system_search()
    {
        if(isset($this->permissions['action1'])&&($this->permissions['action1']==1))
        {
            $data['title']="Sale Cancel Request";
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/search",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/search');
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function system_add()
    {
        if(isset($this->permissions['action1'])&&($this->permissions['action1']==1))
        {
            $item=$this->input->post('item');
            if(!$item['barcode'])
            {
                $ajax['status']=false;
                $ajax['system_message']='Invoice No field is required.';
                $this->json_return($ajax);
            }
            $sale_id=Barcode_helper::get_id_sales($item['barcode']);
            if(!($sale_id>0))
            {
                $ajax['status']=false;
                $ajax['system_message']='Invoice Not Found.';
                $this->json_return($ajax);
            }
            $this->db->from($this->config->item('table_pos_sale').' sale');
            $this->db->select('sale.*');
            $this->db->select('cus.name outlet_name,cus.name_short outlet_short_name');
            $this->db->select('f.name farmer_name,f.mobile_no,f.nid,f.address');
            $this->db->select('ft.name type_name');
            $this->db->join($this->config->item('table_login_csetup_cus_info').' cus','cus.customer_id =sale.outlet_id AND cus.revision=1','INNER');
            $this->db->join($this->config->item('table_pos_setup_farmer_farmer').' f','f.id = sale.farmer_id','INNER');
            $this->db->join($this->config->item('table_pos_setup_farmer_type').' ft','ft.id = f.farmer_type_id','INNER');
            $this->db->where('sale.id',$sale_id);
            $data['item']=$this->db->get()->row_array();

            if($data['item']['status']!=$this->config->item('system_status_active'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Invoice already Canceled';
                $this->json_return($ajax);
            }

            if(!in_array($data['item']['outlet_id'],$this->user_outlet_ids))
            {
                System_helper::invalid_try('Search',0,'outlet id '.$data['item']['outlet_id'].' not assigned');
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
                $this->json_return($ajax);
                die();
            }
            //check if it is already in requested
            $result=Query_helper::get_info($this->config->item('table_pos_sale_cancel'),'*',array('sale_id ='.$sale_id,'status_approve ="'.$this->config->item('system_status_pending').'"'),1);
            if($result)
            {
                $ajax['status']=false;
                $ajax['system_message']='Invoice already Requested for Cancel';
                $this->json_return($ajax);
            }


            $this->db->from($this->config->item('table_pos_sale_details').' sd');
            $this->db->select('sd.*');

            $this->db->join($this->config->item('table_login_setup_classification_varieties').' v','v.id = sd.variety_id','INNER');
            $this->db->select('v.name variety_name');
            $this->db->join($this->config->item('table_login_setup_classification_crop_types').' type','type.id = v.crop_type_id','INNER');
            $this->db->select('type.name crop_type_name,type.id crop_type_id');
            $this->db->join($this->config->item('table_login_setup_classification_crops').' crop','crop.id = type.crop_id','INNER');
            $this->db->select('crop.name crop_name,crop.id crop_id');
            $this->db->where('sd.sale_id',$sale_id);

            $data['items']=$this->db->get()->result_array();
            $data['has_variety_discount']=false;
            foreach($data['items'] as $row)
            {
                if($row['amount_discount_variety']>0)
                {
                    $data['has_variety_discount']=true;
                    break;
                }
            }

            $user_ids=array();
            $user_ids[$data['item']['user_created']]=$data['item']['user_created'];
            if($data['item']['user_manual_approved']>0)
            {
                $user_ids[$data['item']['user_manual_approved']]=$data['item']['user_manual_approved'];
            }

            $data['users']=System_helper::get_users_info($user_ids);
            $data['title']='Sale Details of ('.Barcode_helper::get_barcode_sales($sale_id).')';

            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>$this->load->view($this->controller_url."/add",$data,true));
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
    private function system_save()
    {
        $item=$this->input->post('item');
        $sale_id=$item['id'];
        $user = User_helper::get_user();
        $time=time();
        if(!(isset($this->permissions['action1']) && ($this->permissions['action1']==1)))
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
        $sale_info=Query_helper::get_info($this->config->item('table_pos_sale'),'*',array('id ='.$sale_id),1);
        if(!$sale_info)
        {
            System_helper::invalid_try('save',$item['id'],'Trying to access Invalid Sale id');
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
            die();
        }
        if(!in_array($sale_info['outlet_id'],$this->user_outlet_ids))
        {
            System_helper::invalid_try('save',$item['id'],'Trying to access other Outlets data');
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
            die();
        }
        //check if it is already in requested
        $result=Query_helper::get_info($this->config->item('table_pos_sale_cancel'),'*',array('sale_id ='.$sale_id,'status_approve ="'.$this->config->item('system_status_pending').'"'),1);
        if($result)
        {
            $ajax['status']=false;
            $ajax['system_message']='Invoice already Requested for Cancel';
            $this->json_return($ajax);
        }

        if(!$this->check_validation())
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->message;
            $this->json_return($ajax);
        }
        if(System_helper::get_time(System_helper::display_date($sale_info['date_sale']))>System_helper::get_time($item['date_cancel']))
        {
            $ajax['status']=false;
            $ajax['system_message']='Cancel Date Can not be less than Sale date';
            $this->json_return($ajax);
        }
        $this->db->trans_start();  //DB Transaction Handle START
        {
            $data['sale_id']=$sale_id;
            $data['date_cancel']=System_helper::get_time($item['date_cancel']);
            $data['date_cancel_requested']=$time;
            $data['remarks_cancel_requested']=$item['remarks_cancel_requested'];
            $data['user_cancel_requested']=$user->user_id;
            Query_helper::add($this->config->item('table_pos_sale_cancel'),$data, true);
        }
        $this->db->trans_complete();   //DB Transaction Handle END
        if ($this->db->trans_status() === TRUE)
        {
            $this->message=$this->lang->line("MSG_SAVED_SUCCESS");
            $this->system_list();
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
        $this->form_validation->set_rules('item[date_cancel]',$this->lang->line('LABEL_DATE_CANCEL'),'required');
        $this->form_validation->set_rules('item[remarks_cancel_requested]','Cancel Reason','required');
        if($this->form_validation->run() == FALSE)
        {
            $this->message=validation_errors();
            return false;
        }
        return true;
    }
    private function system_details($id)
    {
        if(isset($this->permissions['action0'])&&($this->permissions['action0']==1))
        {
            if($id>0)
            {
                $cancel_id=$id;
            }
            else
            {
                $cancel_id=$this->input->post('id');
            }

            $cancel_request_info=Query_helper::get_info($this->config->item('table_pos_sale_cancel'),'*',array('id ='.$cancel_id),1);
            if(!($cancel_request_info))
            {
                $ajax['status']=false;
                $ajax['system_message']='Invalid Cancel Request';
                $this->json_return($ajax);
            }
            $data['cancel_info']=$cancel_request_info;
            $sale_id=$cancel_request_info['sale_id'];

            $this->db->from($this->config->item('table_pos_sale').' sale');
            $this->db->select('sale.*');
            $this->db->select('cus.name outlet_name,cus.name_short outlet_short_name');
            $this->db->select('f.name farmer_name,f.mobile_no,f.nid,f.address');
            $this->db->select('ft.name type_name');
            $this->db->join($this->config->item('table_login_csetup_cus_info').' cus','cus.customer_id =sale.outlet_id AND cus.revision=1','INNER');
            $this->db->join($this->config->item('table_pos_setup_farmer_farmer').' f','f.id = sale.farmer_id','INNER');
            $this->db->join($this->config->item('table_pos_setup_farmer_type').' ft','ft.id = f.farmer_type_id','INNER');
            $this->db->where('sale.id',$sale_id);
            $data['item']=$this->db->get()->row_array();
            if(!$data['item'])
            {
                System_helper::invalid_try('details',$cancel_id,'Trying to access Invalid Sale id');
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
                $this->json_return($ajax);
                die();
            }

            if(!in_array($data['item']['outlet_id'],$this->user_outlet_ids))
            {
                System_helper::invalid_try('details',0,'outlet id '.$data['item']['outlet_id'].' not assigned');
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
                $this->json_return($ajax);
                die();
            }
            $this->db->from($this->config->item('table_pos_sale_details').' sd');
            $this->db->select('sd.*');

            $this->db->join($this->config->item('table_login_setup_classification_varieties').' v','v.id = sd.variety_id','INNER');
            $this->db->select('v.name variety_name');
            $this->db->join($this->config->item('table_login_setup_classification_crop_types').' type','type.id = v.crop_type_id','INNER');
            $this->db->select('type.name crop_type_name,type.id crop_type_id');
            $this->db->join($this->config->item('table_login_setup_classification_crops').' crop','crop.id = type.crop_id','INNER');
            $this->db->select('crop.name crop_name,crop.id crop_id');
            $this->db->where('sd.sale_id',$sale_id);

            $data['items']=$this->db->get()->result_array();
            $data['has_variety_discount']=false;
            foreach($data['items'] as $row)
            {
                if($row['amount_discount_variety']>0)
                {
                    $data['has_variety_discount']=true;
                    break;
                }
            }

            $user_ids=array();
            $user_ids[$data['item']['user_created']]=$data['item']['user_created'];
            if($data['item']['user_manual_approved']>0)
            {
                $user_ids[$data['item']['user_manual_approved']]=$data['item']['user_manual_approved'];
            }
            $user_ids[$data['cancel_info']['user_cancel_requested']]=$data['cancel_info']['user_cancel_requested'];
            if($data['cancel_info']['user_cancel_approved']>0)
            {
                $user_ids[$data['cancel_info']['user_cancel_approved']]=$data['cancel_info']['user_cancel_approved'];
            }

            $data['users']=System_helper::get_users_info($user_ids);
            $data['title']='Sale Details of ('.Barcode_helper::get_barcode_sales($sale_id).')';

            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/details",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/details/'.$cancel_id);
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
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
        $data['invoice_no']= 1;
        $data['date_sale']= 1;
        $data['date_cancel']= 1;
        $data['outlet_name']= 1;
        $data['customer_name']= 1;
        $data['amount_actual']= 1;
        $data['status_approve']= 1;
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
