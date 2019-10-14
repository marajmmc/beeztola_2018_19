<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Sales_cancel_approve extends Root_Controller
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
        $this->permissions=User_helper::get_permission('Sales_cancel_approve');
        $this->controller_url='sales_cancel_approve';
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
        elseif($action=="list_all")
        {
            $this->system_list_all();
        }
        elseif($action=="get_items_all")
        {
            $this->system_get_items_all();
        }

        elseif($action=="edit")
        {
            $this->system_edit($id);
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
        elseif($action=="set_preference_all")
        {
            $this->system_set_preference_all();
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
            $data['title']="List of Sale Cancel Approval Pending";
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
        $this->db->from($this->config->item('table_pos_sale_cancel').' cancel');
        $this->db->select('cancel.*');

        $this->db->join($this->config->item('table_pos_sale').' sale','sale.id=cancel.sale_id','INNER');
        $this->db->select('sale.date_sale,sale.amount_payable_actual amount_actual,sale.sales_payment_method');
        $this->db->select('cus.name outlet_name');
        $this->db->select('f.name customer_name');
        $this->db->join($this->config->item('table_login_csetup_cus_info').' cus','cus.customer_id =sale.outlet_id AND cus.revision=1','INNER');
        $this->db->join($this->config->item('table_pos_setup_farmer_farmer').' f','f.id = sale.farmer_id','INNER');
        $this->db->where_in('sale.outlet_id',$this->user_outlet_ids);
        $this->db->where('cancel.status_approve',$this->config->item('system_status_pending'));
        $this->db->order_by('cancel.id DESC');
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
    private function system_list_all()
    {
        if(isset($this->permissions['action0'])&&($this->permissions['action0']==1))
        {
            $data['system_preference_items']= $this->get_preference_all();
            $data['title']="List of All Sale Cancel Requests";
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/list_all",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url."/index/list_all");
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function system_get_items_all()
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
        $this->db->select('sale.date_sale,sale.amount_payable_actual amount_actual,sale.sales_payment_method');
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
    private function system_edit($id)
    {
        if(isset($this->permissions['action2'])&&($this->permissions['action2']==1))
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
            if($cancel_request_info['status_approve']!=$this->config->item('system_status_pending'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Cancel Request already Approve/Rejected';
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
                System_helper::invalid_try('edit',$cancel_id,'Trying to access Invalid Sale id');
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
                $this->json_return($ajax);
                die();
            }
            if($data['item']['status']!=$this->config->item('system_status_active'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Invoice already Canceled';
                $this->json_return($ajax);
            }

            if(!in_array($data['item']['outlet_id'],$this->user_outlet_ids))
            {
                System_helper::invalid_try('edit',0,'outlet id '.$data['item']['outlet_id'].' not assigned');
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

            $data['users']=System_helper::get_users_info($user_ids);
            $data['title']='Sale Details of ('.Barcode_helper::get_barcode_sales($sale_id).')';

            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/edit",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/edit/'.$cancel_id);
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
        $cancel_id=$item['cancel_id'];
        $user = User_helper::get_user();
        $time=time();
        if(!(isset($this->permissions['action2']) && ($this->permissions['action2']==1)))
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
        $cancel_request_info=Query_helper::get_info($this->config->item('table_pos_sale_cancel'),'*',array('id ='.$cancel_id),1);
        if(!($cancel_request_info))
        {
            $ajax['status']=false;
            $ajax['system_message']='Invalid Cancel Request';
            $this->json_return($ajax);
        }
        if($cancel_request_info['status_approve']!=$this->config->item('system_status_pending'))
        {
            $ajax['status']=false;
            $ajax['system_message']='Cancel Request already Approved/Rejected';
            $this->json_return($ajax);
        }
        $sale_id=$cancel_request_info['sale_id'];
        $sale_info=Query_helper::get_info($this->config->item('table_pos_sale'),'*',array('id ='.$sale_id),1);
        if(!$sale_info)
        {
            System_helper::invalid_try(__FUNCTION__,$cancel_id,'Invalid Sale id in cancel id');
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
            die();
        }
        if($sale_info['status']!=$this->config->item('system_status_active'))
        {
            $ajax['status']=false;
            $ajax['system_message']='Invoice already Canceled';
            $this->json_return($ajax);
        }
        if(!in_array($sale_info['outlet_id'],$this->user_outlet_ids))
        {
            System_helper::invalid_try(__FUNCTION__,$cancel_id,'Trying to access other Outlets data');
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
            die();
        }
        $farmer_info=Query_helper::get_info($this->config->item('table_pos_setup_farmer_farmer'),'*',array('id ='.$sale_info['farmer_id']),1);
        if(!$farmer_info)
        {
            $ajax['status']=false;
            $ajax['system_message']="Invalid Customer";
            $this->json_return($ajax);
            die();
        }
        $stocks=Stock_helper::get_variety_stock($sale_info['outlet_id']);
        $item_head_details=Query_helper::get_info($this->config->item('table_pos_sale_details'),'*',array('sale_id ='.$sale_id));
        if($item['status_approve']==$this->config->item('system_status_approved'))
        {
            foreach($item_head_details as $row)
            {
                if(($stocks[$row['variety_id']][$row['pack_size_id']]['current_stock']+$row['quantity'])<0)
                {
                    $ajax['status']=false;
                    $message='Stock Will be negative('.$row['variety_id'].'-'.$row['pack_size_id'].')';
                    $message.='<br>Current Stock('.$stocks[$row['variety_id']][$row['pack_size_id']]['current_stock'].')';
                    $ajax['system_message']=$message;
                    $this->json_return($ajax);
                    die();
                }
            }
            if($sale_info['sales_payment_method']=='Credit')
            {
                if($farmer_info['amount_credit_balance']+$sale_info['amount_payable_actual']<0)
                {
                    $ajax['status']=false;
                    $ajax['system_message']="Customer Credit balance will exceed.<br>Please contact with admin";
                    $this->json_return($ajax);
                    die();
                }
            }
        }
        if(!$this->check_validation())
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->message;
            $this->json_return($ajax);
        }
        $this->load->helper('farmer_credit');
        $this->db->trans_start();  //DB Transaction Handle START
        {
            $data=array();
            $data['date_cancel_approved']=$time;
            $data['user_cancel_approved']=$user->user_id;
            $data['remarks_cancel_approved']=$item['remarks_cancel_approved'];
            $data['status_approve']=$item['status_approve'];
            Query_helper::update($this->config->item('table_pos_sale_cancel'),$data,array('id='.$cancel_id));
            if($item['status_approve']==$this->config->item('system_status_approved'))
            {
                $item_head=array();
                $item_head['date_cancel']=$cancel_request_info['date_cancel'];
                $item_head['date_cancel_approved']=$time;
                $item_head['user_cancel_approved']=$user->user_id;
                $item_head['remarks_cancel_approved']=$item['remarks_cancel_approved'];
                $item_head['status']=$this->config->item('system_status_inactive');
                Query_helper::update($this->config->item('table_pos_sale'),$item_head,array('id='.$sale_id));
                //update stock
                foreach($item_head_details as $data_details)
                {
                    $data_stock=array();
                    $data_stock['out_sale']=($stocks[$data_details['variety_id']][$data_details['pack_size_id']]['out_sale']-$data_details['quantity']);
                    $data_stock['current_stock']=($stocks[$data_details['variety_id']][$data_details['pack_size_id']]['current_stock']+$data_details['quantity']);
                    $data_stock['date_updated'] = $time;
                    $data_stock['user_updated'] = $user->user_id;
                    Query_helper::update($this->config->item('table_pos_stock_summary_variety'),$data_stock,array('id='.$stocks[$data_details['variety_id']][$data_details['pack_size_id']]['id']));

                }
                //update farmer credit if credit sale
                if($sale_info['sales_payment_method']=='Credit')
                {
                    $data_history=array();
                    $data_history['farmer_id']=$farmer_info['id'];
                    $data_history['sale_id']=$sale_id;
                    //$data_history['payment_id']=0
                    $data_history['credit_limit_old']=$farmer_info['amount_credit_limit'];
                    $data_history['credit_limit_new']=$farmer_info['amount_credit_limit'];
                    $data_history['balance_old']=$farmer_info['amount_credit_balance'];
                    $data_history['balance_new']=$farmer_info['amount_credit_balance']+$sale_info['amount_payable_actual'];
                    $data_history['amount_adjust']=$sale_info['amount_payable_actual'];
                    $data_history['remarks_reason']='Sale Cancel';
                    //$data_history['reference_no']
                    //$data_history['remarks'];

                    $data_credit=array();
                    $data_credit['date_updated'] = $time;
                    $data_credit['user_updated'] = $user->user_id;
                    $data_credit['amount_credit_balance']=$data_history['balance_new'];
                    Query_helper::update($this->config->item('table_pos_setup_farmer_farmer'),$data_credit, array('id='.$farmer_info['id']), false);
                    Farmer_Credit_helper::add_credit_history($data_history);
                }
            }
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
        $this->form_validation->set_rules('item[status_approve]',"Approve/Reject",'required');
        $this->form_validation->set_rules('item[remarks_cancel_approved]','Remarks','required');
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
        $data['sales_payment_method']= 1;
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
    private function system_set_preference_all()
    {
        if(isset($this->permissions['action6']) && ($this->permissions['action6']==1))
        {
            $data['system_preference_items']=$this->get_preference_all();
            $data['preference_method_name']='list_all';
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
    private function get_preference_all()
    {
        $user = User_helper::get_user();
        $result=Query_helper::get_info($this->config->item('table_system_user_preference'),'*',array('user_id ='.$user->user_id,'controller ="' .$this->controller_url.'"','method ="list_all"'),1);
        $data['id']= 1;
        $data['invoice_no']= 1;
        $data['date_sale']= 1;
        $data['date_cancel']= 1;
        $data['outlet_name']= 1;
        $data['customer_name']= 1;
        $data['sales_payment_method']= 1;
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
