<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Sales_return_approve extends Root_Controller
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
        $this->permissions=User_helper::get_permission(get_class());
        $this->controller_url=strtolower(get_class());
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
        $this->config->load('sales_return');
        $this->language_labels();
    }
    private function language_labels()
    {
        $this->lang->language['LABEL_DATE_SALE']='Return date';

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
            $data['title']="Pending Sale Return Requests";
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
        $this->db->from($this->config->item('table_pos_sale_return').' sale_return');
        $this->db->select('sale_return.*');
        $this->db->select('cus.name outlet_name');
        $this->db->select('f.name customer_name');
        $this->db->join($this->config->item('table_login_csetup_cus_info').' cus','cus.customer_id =sale_return.outlet_id AND cus.revision=1','INNER');
        $this->db->join($this->config->item('table_pos_setup_farmer_farmer').' f','f.id = sale_return.farmer_id','INNER');
        $this->db->where_in('sale_return.outlet_id',$this->user_outlet_ids);
        $this->db->order_by('sale_return.id DESC');
        $this->db->where('sale_return.status_approve',$this->config->item('system_status_pending'));
        $items=$this->db->get()->result_array();
        foreach($items as &$item)
        {
            $item['date_sale']=$item['date_sale']>0?System_helper::display_date_time($item['date_sale']):'N/A';
            $item['amount_discount']=number_format($item['amount_discount_variety']+$item['amount_discount_self'],2);
            $item['amount_total']=number_format($item['amount_total'],2);
            $item['amount_actual']=number_format($item['amount_payable_actual'],2);

        }
        $this->json_return($items);
    }
    private function system_list_all()
    {
        if(isset($this->permissions['action0'])&&($this->permissions['action0']==1))
        {
            $data['system_preference_items']= $this->get_preference_all();
            $data['title']="All Sale Return Requests";
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

        $this->db->from($this->config->item('table_pos_sale_return').' sale_return');
        $this->db->select('sale_return.*');
        $this->db->select('cus.name outlet_name');
        $this->db->select('f.name customer_name');
        $this->db->join($this->config->item('table_login_csetup_cus_info').' cus','cus.customer_id =sale_return.outlet_id AND cus.revision=1','INNER');
        $this->db->join($this->config->item('table_pos_setup_farmer_farmer').' f','f.id = sale_return.farmer_id','INNER');
        $this->db->where_in('sale_return.outlet_id',$this->user_outlet_ids);
        $this->db->order_by('sale_return.id DESC');
        $this->db->limit($pagesize,$current_records);
        $items=$this->db->get()->result_array();
        foreach($items as &$item)
        {
            $item['date_sale']=$item['date_sale']>0?System_helper::display_date_time($item['date_sale']):'N/A';
            if($item['status_approve']==$this->config->item('system_status_approved'))
            {
                $item['invoice_no']=Barcode_helper::get_barcode_sales($item['sale_id']);
            }
            else
            {
                $item['invoice_no']='N/A';
            }
            $item['amount_discount']=number_format($item['amount_discount_variety']+$item['amount_discount_self'],2);
            $item['amount_total']=number_format($item['amount_total'],2);
            $item['amount_actual']=number_format($item['amount_payable_actual'],2);

        }
        $this->json_return($items);
    }
    private function system_edit($id)
    {
        if(isset($this->permissions['action2'])&&($this->permissions['action2']==1))
        {
            if($id>0)
            {
                $return_id=$id;
            }
            else
            {
                $return_id=$this->input->post('id');
            }
            $this->db->from($this->config->item('table_pos_sale_return').' sale_return');
            $this->db->select('sale_return.*');
            $this->db->join($this->config->item('table_login_csetup_cus_info').' cus','cus.customer_id =sale_return.outlet_id AND cus.revision=1','INNER');
            $this->db->select('cus.name outlet_name');
            $this->db->join($this->config->item('table_pos_setup_farmer_farmer').' f','f.id = sale_return.farmer_id','INNER');
            $this->db->select('f.name farmer_name,f.mobile_no,f.amount_credit_limit,f.amount_credit_balance');
            $this->db->where('sale_return.id',$return_id);
            $data['item']=$this->db->get()->row_array();
            if(!$data['item'])
            {
                System_helper::invalid_try(__FUNCTION__, $return_id,'Trying to access Invalid Return id');
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
                $this->json_return($ajax);
                die();
            }
            if(!in_array($data['item']['outlet_id'],$this->user_outlet_ids))
            {
                System_helper::invalid_try(__FUNCTION__, $return_id,'Trying to access other Outlets data');
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
                $this->json_return($ajax);
                die();
            }
            if($data['item']['status_approve']!=$this->config->item('system_status_pending'))
            {
                $ajax['status']=false;
                $ajax['system_message']='This Return already Approved/Rejected';
                $this->json_return($ajax);
            }
            $result=Query_helper::get_info($this->config->item('table_pos_setup_farmer_outlet'),'*',array('farmer_id ='.$data['item']['farmer_id'],'revision =1','outlet_id ='.$data['item']['outlet_id']),1);
            if(!$result)
            {
                $ajax['status']=false;
                $ajax['system_message']='This Customer Cannot Retrun Product from this outlet.<br>Please Contact with admin';
                $this->json_return($ajax);
            }

            $this->db->from($this->config->item('table_pos_sale_return_details').' sd');
            $this->db->select('sd.*');

            $this->db->join($this->config->item('table_login_setup_classification_varieties').' v','v.id = sd.variety_id','INNER');
            $this->db->select('v.name variety_name');
            $this->db->join($this->config->item('table_login_setup_classification_crop_types').' type','type.id = v.crop_type_id','INNER');
            $this->db->select('type.name crop_type_name,type.id crop_type_id');
            $this->db->join($this->config->item('table_login_setup_classification_crops').' crop','crop.id = type.crop_id','INNER');
            $this->db->select('crop.name crop_name,crop.id crop_id');
            $this->db->where('sd.return_id',$return_id);
            $data['items']=$this->db->get()->result_array();

            $user_ids=array();

            $user_ids[$data['item']['user_return_requested']]=$data['item']['user_return_requested'];
            if($data['item']['user_return_approved']>0)
            {
                $user_ids[$data['item']['user_return_approved']]=$data['item']['user_return_approved'];
            }
            $data['users']=System_helper::get_users_info($user_ids);
            //stock_farmer
            $result=Query_helper::get_info($this->config->item('table_login_setup_system_configures'),array('config_value'),array('purpose ="' .$this->config->item('system_purpose_pos_sale_return_starting_date').'"','status ="'.$this->config->item('system_status_active').'"'),1);
            $date_start=System_helper::get_time($result['config_value']);
            $data['item']['date_start']=$date_start;

            //current stocks--purchase quantity
            $this->db->from($this->config->item('table_pos_sale_details').' details');
            $this->db->select('details.variety_id,details.pack_size_id,details.pack_size');
            $this->db->select('SUM(details.quantity) current_stock',false);


            $this->db->join($this->config->item('table_pos_sale').' sale','sale.id = details.sale_id','INNER');
            $this->db->where('sale.outlet_id',$data['item']['outlet_id']);
            $this->db->where('sale.farmer_id',$data['item']['farmer_id']);
            $this->db->where('sale.status',$this->config->item('system_status_active'));
            $this->db->where('sale.date_sale >=',$date_start);
            $this->db->group_by('details.variety_id');
            $this->db->group_by('details.pack_size_id');
            $results=$this->db->get()->result_array();
            $data['stocks_purchase']=array();//customer stocks
            foreach($results as $result)
            {

                $data['stocks_purchase'][$result['variety_id']][$result['pack_size_id']]=$result;
            }

            //$data['stocks']=Stock_helper::get_variety_stock($data['item']['outlet_id']);

            $this->db->from($this->config->item('table_login_csetup_customer').' customer');
            $this->db->join($this->config->item('table_login_csetup_cus_info').' customer_info','customer_info.customer_id=customer.id','INNER');
            $this->db->select('customer_info.customer_id outlet_id, CONCAT_WS(" - ",customer_info.customer_code, customer_info.name) outlet_name');
            $this->db->where('customer.status',$this->config->item('system_status_active'));
            $this->db->where('customer_info.type',$this->config->item('system_customer_type_outlet_id'));
            $this->db->where(' customer_info.revision',1);
            $this->db->order_by('customer.id');
            $data['outlets']=$this->db->get()->result_array();


            $data['title']='Approve/reject of Request Id('.$return_id.')';
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/edit",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/edit/'.$return_id);
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
        $return_id=$item['return_id'];
        $user = User_helper::get_user();
        $time=time();
        if(!(isset($this->permissions['action2']) && ($this->permissions['action2']==1)))
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
        $return_info=Query_helper::get_info($this->config->item('table_pos_sale_return'),'*',array('id ='.$return_id),1);
        if(!($return_id))
        {
            $ajax['status']=false;
            $ajax['system_message']='Invalid Return Approval';
            $this->json_return($ajax);
        }
        if($return_info['status_approve']!=$this->config->item('system_status_pending'))
        {
            $ajax['status']=false;
            $ajax['system_message']='This Request already Approved/Rejected';
            $this->json_return($ajax);
        }
        if(!in_array($return_info['outlet_id'],$this->user_outlet_ids))
        {
            System_helper::invalid_try(__FUNCTION__, $return_id,'Trying to access other Outlets data');
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
            die();
        }
        $result=Query_helper::get_info($this->config->item('table_pos_setup_farmer_outlet'),'*',array('farmer_id ='.$return_info['farmer_id'],'revision =1','outlet_id ='.$return_info['outlet_id']),1);
        if(!$result)
        {
            $ajax['status']=false;
            $ajax['system_message']='This Customer Cannot Retrun Product from this outlet.<br>Please Contact with admin';
            $this->json_return($ajax);
        }
        $farmer_info=Query_helper::get_info($this->config->item('table_pos_setup_farmer_farmer'),'*',array('id ='.$return_info['farmer_id']),1);
        if(!$this->check_validation())
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->message;
            $this->json_return($ajax);
        }

        $stocks=Stock_helper::get_variety_stock($return_info['outlet_id']);//outlet stock

        //calculate it from config
        $result=Query_helper::get_info($this->config->item('table_login_setup_system_configures'),array('config_value'),array('purpose ="' .$this->config->item('system_purpose_pos_sale_return_starting_date').'"','status ="'.$this->config->item('system_status_active').'"'),1);
        $date_start=System_helper::get_time($result['config_value']);


        //current stocks--purchase quantity
        $this->db->from($this->config->item('table_pos_sale_details').' details');
        $this->db->select('details.variety_id,details.pack_size_id,details.pack_size');
        $this->db->select('SUM(details.quantity) current_stock',false);


        $this->db->join($this->config->item('table_pos_sale').' sale','sale.id = details.sale_id','INNER');
        $this->db->where('sale.outlet_id',$return_info['outlet_id']);
        $this->db->where('sale.farmer_id',$return_info['farmer_id']);
        $this->db->where('sale.status',$this->config->item('system_status_active'));
        $this->db->where('sale.date_sale >=',$date_start);
        $this->db->group_by('details.variety_id');
        $this->db->group_by('details.pack_size_id');
        $results=$this->db->get()->result_array();
        $stocks_purchase=array();//customer stocks
        foreach($results as $result)
        {

            $stocks_purchase[$result['variety_id']][$result['pack_size_id']]=$result;
        }

        $return_info_details=Query_helper::get_info($this->config->item('table_pos_sale_return_details'),'*',array('return_id ='.$return_id));

        if($item['status_approve']==$this->config->item('system_status_approved'))
        {

            foreach($return_info_details as $row)
            {
                if(!(isset($stocks_purchase[$row['variety_id']][$row['pack_size_id']])))
                {
                    $ajax['status']=false;
                    $message='Invalid Product('.$row['variety_id'].'-'.$row['pack_size_id'].')';
                    $ajax['system_message']=$message;
                    $this->json_return($ajax);
                    die();
                }
                if($row['quantity']>$stocks_purchase[$row['variety_id']][$row['pack_size_id']]['current_stock'])
                {
                    $ajax['status']=false;
                    $message='Not Enough Purchase('.$row['variety_id'].'-'.$row['pack_size_id'].')';
                    $message.='<br>Current Purchase('.$stocks_purchase[$row['variety_id']][$row['pack_size_id']]['current_stock'].')';
                    $ajax['system_message']=$message;
                    $this->json_return($ajax);
                    die();
                }
            }
            //check stock validation
        }


        $this->db->trans_start();  //DB Transaction Handle START
        {
            if($item['status_approve']==$this->config->item('system_status_rejected'))
            {
                $data=array();
                $data['status_approve']=$item['status_approve'];
                $data['date_return_approved']=$time;
                $data['user_return_approved']=$user->user_id;
                $data['remarks_return_approved']=$item['remarks_return_approved'];
                Query_helper::update($this->config->item('table_pos_sale_return'),$data,array('id='.$return_id));
            }
            else if($item['status_approve']==$this->config->item('system_status_approved'))
            {
                $item_head=array();
                $item_head['outlet_id']=$return_info['outlet_id'];
                $item_head['outlet_id_commission']=$item['outlet_id_commission'];
                $item_head['farmer_id']=$return_info['farmer_id'];
                $item_head['discount_self_percentage']=$return_info['discount_self_percentage'];
                $item_head['amount_total']=0-$return_info['amount_total'];
                $item_head['amount_discount_variety']=0-$return_info['amount_discount_variety'];
                $item_head['amount_discount_self']=0-$return_info['amount_discount_self'];
                $item_head['amount_payable']=0-$return_info['amount_payable'];
                $item_head['amount_payable_actual']=0-$return_info['amount_payable_actual'];
                if($farmer_info['amount_credit_limit']>0)
                {
                    $item_head['amount_cash']=0;
                    $item_head['sales_payment_method']='Credit';
                }
                else
                {
                    $item_head['amount_cash']=0-$return_info['amount_payable_actual'];
                    $item_head['sales_payment_method']='Cash';
                }
                $item_head['date_sale']=$time;
                $item_head['remarks']=$return_info['remarks_return_requested'];
                $item_head['status']=$this->config->item('system_status_active');
                $item_head['invoice_type']='Return';
                $item_head['date_return_approved']=$time;
                $item_head['user_return_approved']=$user->user_id;
                $item_head['remarks_return_approved']=$item['remarks_return_approved'];
                $item_head['date_created']=$return_info['date_return_requested'];
                $item_head['user_created']=$return_info['user_return_requested'];
                $sale_id=Query_helper::add($this->config->item('table_pos_sale'),$item_head);
                if(!($sale_id>0))
                {
                    $ajax['status']=false;
                    $ajax['system_message']=$this->lang->line("MSG_SAVED_FAIL");
                    $this->json_return($ajax);
                }
                foreach($return_info_details as $data_details)
                {
                    //unset($data_details['id']);
                    //unset($data_details['manual_sale_id']);
                    //$data_details['sale_id']=$sale_id;
                    $details=array();
                    $details['sale_id']=$sale_id;
                    $details['variety_id']=$data_details['variety_id'];
                    $details['pack_size_id']=$data_details['pack_size_id'];
                    $details['pack_size']=$data_details['pack_size'];
                    $details['price_unit_pack']=$data_details['price_unit_pack'];
                    $details['quantity']=0-$data_details['quantity'];
                    $details['amount_total']=0-$data_details['amount_total'];
                    $details['discount_percentage_variety']=$data_details['discount_percentage_variety'];
                    $details['amount_discount_variety']=0-$data_details['amount_discount_variety'];
                    $details['amount_payable_actual']=0-$data_details['amount_payable_actual'];
                    Query_helper::add($this->config->item('table_pos_sale_details'),$details);

                    $data_stock=array();
                    $data_stock['out_sale']=($stocks[$data_details['variety_id']][$data_details['pack_size_id']]['out_sale']-$data_details['quantity']);
                    $data_stock['current_stock']=($stocks[$data_details['variety_id']][$data_details['pack_size_id']]['current_stock']+$data_details['quantity']);
                    $data_stock['date_updated'] = $time;
                    $data_stock['user_updated'] = $user->user_id;
                    Query_helper::update($this->config->item('table_pos_stock_summary_variety'),$data_stock,array('id='.$stocks[$data_details['variety_id']][$data_details['pack_size_id']]['id']));
                }
                $data=array();
                $data['outlet_id_commission']=$item['outlet_id_commission'];
                $data['date_sale']=$time;
                $data['status_approve']=$item['status_approve'];
                $data['date_return_approved']=$time;
                $data['user_return_approved']=$user->user_id;
                $data['remarks_return_approved']=$item['remarks_return_approved'];
                $data['sale_id']=$sale_id;
                Query_helper::update($this->config->item('table_pos_sale_return'),$data,array('id='.$return_id));

                if($farmer_info['amount_credit_limit']>0)
                {
                    $this->load->helper('farmer_credit');
                    $data_history=array();
                    $data_history['farmer_id']=$farmer_info['id'];
                    $data_history['sale_id']=$sale_id;
                    //$data_history['payment_id']=0
                    $data_history['credit_limit_old']=$farmer_info['amount_credit_limit'];
                    $data_history['credit_limit_new']=$farmer_info['amount_credit_limit'];
                    $data_history['balance_old']=$farmer_info['amount_credit_balance'];
                    $data_history['balance_new']=$farmer_info['amount_credit_balance']-$item_head['amount_payable_actual'];
                    $data_history['amount_adjust']=0-$item_head['amount_payable_actual'];
                    $data_history['remarks_reason']='Sales Return';
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
            $this->system_list_all();
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
        $item=$this->input->post('item');
        $this->load->library('form_validation');
        $this->form_validation->set_rules('item[status_approve]',"Approve/Reject",'required');
        $this->form_validation->set_rules('item[remarks_return_approved]','Remarks','required');
        if($item['status_approve']==$this->config->item('system_status_approved'))
        {
            $this->form_validation->set_rules('item[outlet_id_commission]','Outlet For Commission','required');
        }
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
                $return_id=$id;
            }
            else
            {
                $return_id=$this->input->post('id');
            }

            $this->db->from($this->config->item('table_pos_sale_return').' sale_return');
            $this->db->select('sale_return.*');
            $this->db->join($this->config->item('table_login_csetup_cus_info').' cus','cus.customer_id =sale_return.outlet_id AND cus.revision=1','INNER');
            $this->db->select('cus.name outlet_name');
            $this->db->join($this->config->item('table_pos_setup_farmer_farmer').' f','f.id = sale_return.farmer_id','INNER');
            $this->db->select('f.name farmer_name,f.mobile_no,f.amount_credit_limit,f.amount_credit_balance');
            $this->db->where('sale_return.id',$return_id);
            $data['item']=$this->db->get()->row_array();
            if(!$data['item'])
            {
                System_helper::invalid_try(__FUNCTION__, $return_id,'Trying to access Invalid Return id');
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
                $this->json_return($ajax);
                die();
            }
            if(!in_array($data['item']['outlet_id'],$this->user_outlet_ids))
            {
                System_helper::invalid_try(__FUNCTION__, $return_id,'Trying to access other Outlets data');
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
                $this->json_return($ajax);
                die();
            }
            $this->db->from($this->config->item('table_pos_sale_return_details').' sd');
            $this->db->select('sd.*');

            $this->db->join($this->config->item('table_login_setup_classification_varieties').' v','v.id = sd.variety_id','INNER');
            $this->db->select('v.name variety_name');
            $this->db->join($this->config->item('table_login_setup_classification_crop_types').' type','type.id = v.crop_type_id','INNER');
            $this->db->select('type.name crop_type_name,type.id crop_type_id');
            $this->db->join($this->config->item('table_login_setup_classification_crops').' crop','crop.id = type.crop_id','INNER');
            $this->db->select('crop.name crop_name,crop.id crop_id');
            $this->db->where('sd.return_id',$return_id);
            $data['items']=$this->db->get()->result_array();

            $user_ids=array();

            $user_ids[$data['item']['user_return_requested']]=$data['item']['user_return_requested'];
            if($data['item']['user_return_approved']>0)
            {
                $user_ids[$data['item']['user_return_approved']]=$data['item']['user_return_approved'];
            }
            $data['users']=System_helper::get_users_info($user_ids);

            if($data['item']['outlet_id_commission']>0)
            {
                $result=Query_helper::get_info($this->config->item('table_login_csetup_cus_info'),array('name outlet_name'),array('customer_id ='.$data['item']['outlet_id_commission']),1);
                $data['item']['outlet_name_commission']=$result['outlet_name'];
            }
            $data['title']='Details of Return Request Id('.$return_id.')';
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/details",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/details/'.$return_id);
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
        $data['outlet_name']= 1;
        $data['date_sale']= 1;
        $data['customer_name']= 1;
        $data['amount_total']= 1;
        $data['amount_discount']= 1;
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
        $data['outlet_name']= 1;
        $data['date_sale']= 1;
        $data['invoice_no']= 1;
        $data['customer_name']= 1;
        $data['amount_total']= 1;
        $data['amount_discount']= 1;
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
