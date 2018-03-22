<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Payment_edit_approve extends Root_Controller
{
    public $message;
    public $permissions;
    public $controller_url;
    public function __construct()
    {
        parent::__construct();
        $this->message="";
        $this->permissions=User_helper::get_permission('Payment_edit_approve');
        $this->controller_url='payment_edit_approve';
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
        elseif($action=="details")
        {
            $this->system_details($id);
        }
        elseif($action=="details_all")
        {
            $this->system_details_all($id);
        }
        elseif($action=="approve")
        {
            $this->system_approve($id);
        }
        elseif($action=="save_approve")
        {
            $this->system_save_approve();
        }
        elseif($action=="set_preference")
        {
            $this->system_set_preference();
        }
        elseif($action=="set_preference_all_edit_payment_approve")
        {
            $this->system_set_preference_all_edit_payment_approve();
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
            $data['title']="Pending Edit Payment Approve List";
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/list",$data,true));
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
        //Getting Assigned Outlet
        $user = User_helper::get_user();
        $this->db->from($this->config->item('table_pos_setup_user_outlet').' user_outlet');
        $this->db->select('user_outlet.customer_id outlet_id');
        $this->db->join($this->config->item('table_login_csetup_customer').' outlet','outlet.id=user_outlet.customer_id AND outlet.status="'.$this->config->item('system_status_active').'"','INNER');
        $this->db->join($this->config->item('table_login_csetup_cus_info').' outlet_info','outlet_info.customer_id=outlet.id AND outlet_info.revision=1 AND outlet_info.type="'.$this->config->item('system_customer_type_outlet_id').'"','INNER');
        $this->db->where('user_outlet.revision',1);
        $this->db->where('user_outlet.user_id',$user->user_id);
        $this->db->order_by('user_outlet.customer_id','ASC');
        $result_outlet=$this->db->get()->result_array();
        $assigned_outlet=array();
        foreach($result_outlet as $outlet)
        {
            $assigned_outlet[]=$outlet['outlet_id'];
        }
        $this->db->from($this->config->item('table_pos_payment_edit').' payment_edit');
        $this->db->select('payment_edit.*');
        $this->db->select('outlet_info.name outlet');
        $this->db->join($this->config->item('table_login_csetup_cus_info').' outlet_info','outlet_info.customer_id=payment_edit.outlet_id AND outlet_info.revision=1 AND outlet_info.type="'.$this->config->item('system_customer_type_outlet_id').'"','INNER');
        $this->db->select('payment_way.name payment_way');
        $this->db->join($this->config->item('table_login_setup_payment_way').' payment_way','payment_way.id=payment_edit.payment_way_id','INNER');
        $this->db->select('bank_source.name bank_payment_source');
        $this->db->join($this->config->item('table_login_setup_bank').' bank_source','bank_source.id=payment_edit.bank_id_source','INNER');
        $this->db->join($this->config->item('table_login_setup_bank_account').' ba','ba.id=payment_edit.bank_account_id_destination','LEFT');
        $this->db->select('bank_destination.name bank_destination, ba.account_number, ba.branch_name');
        $this->db->join($this->config->item('table_login_setup_bank').' bank_destination','bank_destination.id=ba.bank_id','LEFT');
        $this->db->where('payment_edit.status !=',$this->config->item('system_status_delete'));
        $this->db->where('payment_edit.status_forward =',$this->config->item('system_status_forwarded'));
        $this->db->where('payment_edit.status_approve =',$this->config->item('system_status_pending'));
        $this->db->where_in('payment_edit.outlet_id',$assigned_outlet);
        $this->db->order_by('payment_edit.id','DESC');
        $items=$this->db->get()->result_array();
        foreach($items as &$item)
        {
            $item['barcode']=Barcode_helper::get_barcode_payment($item['payment_id']);
            $item['date_payment']=System_helper::display_date($item['date_payment']);
            $item['date_sale']=System_helper::display_date($item['date_sale']);
            $item['date_receive']=System_helper::display_date($item['date_receive']);
            $item['amount_payment']=number_format($item['amount_payment'],2);
            $item['amount_receive']=number_format($item['amount_receive'],2);
            $item['amount_bank_charge']=number_format($item['amount_bank_charge'],2);
            $item['bank_payment_branch_source']=$item['bank_branch_source'];
            if($item['bank_account_id_destination'])
            {
                $item['bank_account_number_destination']=$item['account_number'].'('.$item['bank_destination'].' -'.$item['branch_name'].')';
            }
        }
        $this->json_return($items);
    }
    private function system_list_all()
    {
        if(isset($this->permissions['action0'])&&($this->permissions['action0']==1))
        {
            $data['system_preference_items']= $this->get_preference_all_edit_payment_approve();
            $data['title']="All Edit Payment Approve List";
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
        //Getting Assigned Outlet
        $user = User_helper::get_user();
        $this->db->from($this->config->item('table_pos_setup_user_outlet').' user_outlet');
        $this->db->select('user_outlet.customer_id outlet_id');
        $this->db->join($this->config->item('table_login_csetup_customer').' outlet','outlet.id=user_outlet.customer_id AND outlet.status="'.$this->config->item('system_status_active').'"','INNER');
        $this->db->join($this->config->item('table_login_csetup_cus_info').' outlet_info','outlet_info.customer_id=outlet.id AND outlet_info.revision=1 AND outlet_info.type="'.$this->config->item('system_customer_type_outlet_id').'"','INNER');
        $this->db->where('user_outlet.revision',1);
        $this->db->where('user_outlet.user_id',$user->user_id);
        $this->db->order_by('user_outlet.customer_id','ASC');
        $result_outlet=$this->db->get()->result_array();
        $assigned_outlet=array();
        foreach($result_outlet as $outlet)
        {
            $assigned_outlet[]=$outlet['outlet_id'];
        }
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
        $this->db->from($this->config->item('table_pos_payment_edit').' payment_edit');
        $this->db->select('payment_edit.*');
        $this->db->select('outlet_info.name outlet');
        $this->db->join($this->config->item('table_login_csetup_cus_info').' outlet_info','outlet_info.customer_id=payment_edit.outlet_id AND outlet_info.revision=1 AND outlet_info.type="'.$this->config->item('system_customer_type_outlet_id').'"','INNER');
        $this->db->select('payment_way.name payment_way');
        $this->db->join($this->config->item('table_login_setup_payment_way').' payment_way','payment_way.id=payment_edit.payment_way_id','INNER');
        $this->db->select('bank_source.name bank_payment_source');
        $this->db->join($this->config->item('table_login_setup_bank').' bank_source','bank_source.id=payment_edit.bank_id_source','INNER');
        $this->db->join($this->config->item('table_login_setup_bank_account').' ba','ba.id=payment_edit.bank_account_id_destination','LEFT');
        $this->db->select('bank_destination.name bank_destination, ba.account_number, ba.branch_name');
        $this->db->join($this->config->item('table_login_setup_bank').' bank_destination','bank_destination.id=ba.bank_id','LEFT');
        $this->db->where_in('payment_edit.outlet_id',$assigned_outlet);
        $this->db->order_by('payment_edit.id','DESC');
        $this->db->limit($pagesize,$current_records);
        $items=$this->db->get()->result_array();
        foreach($items as &$item)
        {
            $item['barcode']=Barcode_helper::get_barcode_payment($item['payment_id']);
            $item['date_payment']=System_helper::display_date($item['date_payment']);
            $item['date_sale']=System_helper::display_date($item['date_sale']);
            $item['date_receive']=System_helper::display_date($item['date_receive']);
            $item['amount_payment']=number_format($item['amount_payment'],2);
            $item['amount_receive']=number_format($item['amount_receive'],2);
            $item['amount_bank_charge']=number_format($item['amount_bank_charge'],2);
            $item['bank_payment_branch_source']=$item['bank_branch_source'];
            if($item['bank_account_id_destination'])
            {
                $item['bank_account_number_destination']=$item['account_number'].'('.$item['bank_destination'].' -'.$item['branch_name'].')';
            }
        }
        $this->json_return($items);
    }
    private function system_details($id)
    {
        if(isset($this->permissions['action0']) && ($this->permissions['action0']==1))
        {
            if($id>0)
            {
                $item_id=$id;
            }
            else
            {
                $item_id=$this->input->post('id');
            }
            $this->db->from($this->config->item('table_pos_payment_edit').' payment_edit');
            $this->db->select('payment_edit.*');
            $this->db->select('outlet_info.name outlet');
            $this->db->join($this->config->item('table_login_csetup_cus_info').' outlet_info','outlet_info.customer_id=payment_edit.outlet_id AND outlet_info.revision=1 AND outlet_info.type="'.$this->config->item('system_customer_type_outlet_id').'"','INNER');
            $this->db->select('payment_way.name payment_way');
            $this->db->join($this->config->item('table_login_setup_payment_way').' payment_way','payment_way.id=payment_edit.payment_way_id','INNER');
            $this->db->select('bank_source.name bank_name');
            $this->db->join($this->config->item('table_login_setup_bank').' bank_source','bank_source.id=payment_edit.bank_id_source','INNER');
            $this->db->join($this->config->item('table_login_setup_bank_account').' ba','ba.id=payment_edit.bank_account_id_destination','LEFT');
            $this->db->select('bank_destination.name bank_destination, ba.account_number, ba.branch_name');
            $this->db->join($this->config->item('table_login_setup_bank').' bank_destination','bank_destination.id=ba.bank_id','LEFT');
            $this->db->select('user_info.name edit_payment_request_by');
            $this->db->join($this->config->item('table_login_setup_user_info').' user_info','user_info.user_id = payment_edit.user_updated','LEFT');
            $this->db->select('user_info_forwarded.name edit_payment_request_forward_by');
            $this->db->join($this->config->item('table_login_setup_user_info').' user_info_forwarded','user_info_forwarded.user_id = payment_edit.user_updated_forward','LEFT');
            $this->db->where('payment_edit.id',$item_id);
            $data['item']=$this->db->get()->row_array();
            if(!$data['item'])
            {
                System_helper::invalid_try('Details Edit Payment Request Approve Non Exists',$item_id);
                $ajax['status']=false;
                $ajax['system_message']='Invalid Edit Payment Request Approve Details.';
                $this->json_return($ajax);
            }
            if($data['item']['status']==$this->config->item('system_status_delete'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Edit Payment Request Deleted. You can not view details';
                $this->json_return($ajax);
            }
            //Checking Valid Outlet
            if(!$this->check_valid_outlet($data['item']['outlet_id'],$invalid_try='Details Outlet Non Assigned',$message='You are trying to view details of edit payment request approve from an outlet which is not assigned to you.'))
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->message;
                $this->json_return($ajax);
            }
            $data['title']="Edit Payment Request Approve Details :: ".Barcode_helper::get_barcode_payment($data['item']['payment_id']);
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/details",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/details/'.$item_id);
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function system_details_all($id)
    {
        if(isset($this->permissions['action0']) && ($this->permissions['action0']==1))
        {
            if($id>0)
            {
                $item_id=$id;
            }
            else
            {
                $item_id=$this->input->post('id');
            }
            $this->db->from($this->config->item('table_pos_payment_edit').' payment_edit');
            $this->db->select('payment_edit.*');
            $this->db->select('outlet_info.name outlet');
            $this->db->join($this->config->item('table_login_csetup_cus_info').' outlet_info','outlet_info.customer_id=payment_edit.outlet_id AND outlet_info.revision=1 AND outlet_info.type="'.$this->config->item('system_customer_type_outlet_id').'"','INNER');
            $this->db->select('payment_way.name payment_way');
            $this->db->join($this->config->item('table_login_setup_payment_way').' payment_way','payment_way.id=payment_edit.payment_way_id','INNER');
            $this->db->select('bank_source.name bank_name');
            $this->db->join($this->config->item('table_login_setup_bank').' bank_source','bank_source.id=payment_edit.bank_id_source','INNER');
            $this->db->join($this->config->item('table_login_setup_bank_account').' ba','ba.id=payment_edit.bank_account_id_destination','LEFT');
            $this->db->select('bank_destination.name bank_destination, ba.account_number, ba.branch_name');
            $this->db->join($this->config->item('table_login_setup_bank').' bank_destination','bank_destination.id=ba.bank_id','LEFT');
            $this->db->select('user_info.name edit_payment_request_by');
            $this->db->join($this->config->item('table_login_setup_user_info').' user_info','user_info.user_id = payment_edit.user_updated','LEFT');
            $this->db->select('user_info_forwarded.name edit_payment_request_forward_by');
            $this->db->join($this->config->item('table_login_setup_user_info').' user_info_forwarded','user_info_forwarded.user_id = payment_edit.user_updated_forward','LEFT');
            $this->db->select('user_info_approved.name edit_payment_request_approve_by');
            $this->db->join($this->config->item('table_login_setup_user_info').' user_info_approved','user_info_approved.user_id = payment_edit.user_updated_approve_edit','LEFT');
            $this->db->where('payment_edit.id',$item_id);
            $data['item']=$this->db->get()->row_array();
            if(!$data['item'])
            {
                System_helper::invalid_try('Details_all Edit Payment Request Approve Non Exists',$item_id);
                $ajax['status']=false;
                $ajax['system_message']='Invalid Edit Payment Request Approve Details.';
                $this->json_return($ajax);
            }
            if($data['item']['status']==$this->config->item('system_status_delete'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Edit Payment Request Approve Deleted. You can not view details';
                $this->json_return($ajax);
            }
            //Checking Valid Outlet
            if(!$this->check_valid_outlet($data['item']['outlet_id'],$invalid_try='Details_all Outlet Non Assigned',$message='You are trying to view details of edit payment request approve from an outlet which is not assigned to you.'))
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->message;
                $this->json_return($ajax);
            }
            $data['title']="Edit Payment Request Approve Details :: ".Barcode_helper::get_barcode_payment($data['item']['payment_id']);
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/details_all",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/details_all/'.$item_id);
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function system_approve($id)
    {
        if((isset($this->permissions['action7']) && ($this->permissions['action7']==1)))
        {
            if($id>0)
            {
                $item_id=$id;
            }
            else
            {
                $item_id=$this->input->post('id');
            }
            $this->db->from($this->config->item('table_pos_payment_edit').' payment_edit');
            $this->db->select('payment_edit.*');
            $this->db->select('outlet_info.name outlet');
            $this->db->join($this->config->item('table_login_csetup_cus_info').' outlet_info','outlet_info.customer_id=payment_edit.outlet_id AND outlet_info.revision=1 AND outlet_info.type="'.$this->config->item('system_customer_type_outlet_id').'"','INNER');
            $this->db->select('payment_way.name payment_way');
            $this->db->join($this->config->item('table_login_setup_payment_way').' payment_way','payment_way.id=payment_edit.payment_way_id','INNER');
            $this->db->select('bank_source.name bank_name');
            $this->db->join($this->config->item('table_login_setup_bank').' bank_source','bank_source.id=payment_edit.bank_id_source','INNER');
            $this->db->join($this->config->item('table_login_setup_bank_account').' ba','ba.id=payment_edit.bank_account_id_destination','LEFT');
            $this->db->select('bank_destination.name bank_destination, ba.account_number, ba.branch_name');
            $this->db->join($this->config->item('table_login_setup_bank').' bank_destination','bank_destination.id=ba.bank_id','LEFT');
            $this->db->select('user_info.name edit_payment_request_by');
            $this->db->join($this->config->item('table_login_setup_user_info').' user_info','user_info.user_id = payment_edit.user_updated','LEFT');
            $this->db->select('user_info_forwarded.name edit_payment_request_forward_by');
            $this->db->join($this->config->item('table_login_setup_user_info').' user_info_forwarded','user_info_forwarded.user_id = payment_edit.user_updated_forward','LEFT');
            $this->db->where('payment_edit.id',$item_id);
            $data['item']=$this->db->get()->row_array();
            if(!$data['item'])
            {
                System_helper::invalid_try('Approve Non Exists',$item_id);
                $ajax['status']=false;
                $ajax['system_message']='Invalid Edit Payment Approve.';
                $this->json_return($ajax);
            }
            if($data['item']['status']==$this->config->item('system_status_delete'))
            {
                $ajax['status']=false;
                $ajax['system_message']='This Item is deleted. You can not approve it';
                $this->json_return($ajax);
            }
            if($data['item']['status_approve']==$this->config->item('system_status_approved'))
            {
                $ajax['status']=false;
                $ajax['system_message']='This Item is already approved. You can not approve or reject it';
                $this->json_return($ajax);
            }
            if($data['item']['status_approve']==$this->config->item('system_status_rejected'))
            {
                $ajax['status']=false;
                $ajax['system_message']='This Item is already rejected. You can not approve or reject it';
                $this->json_return($ajax);
            }
            //Checking Valid Outlet
            if(!$this->check_valid_outlet($data['item']['outlet_id'],$invalid_try='Approve Outlet Non Assigned',$message='You are trying to approve edit payment request from an outlet which is not assigned to you.'))
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->message;
                $this->json_return($ajax);
            }
            $data['title']="Edit Payment Approve:: ".Barcode_helper::get_barcode_payment($data['item']['payment_id']);
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/approve",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/approve/'.$item_id);
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function system_save_approve()
    {
        $id = $this->input->post("id");
        $user = User_helper::get_user();
        $time=time();
        $item=$this->input->post('item');
        if(!((isset($this->permissions['action7']) && ($this->permissions['action7']==1))))
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
        if(!$item['status_approve'])
        {
            $ajax['status']=false;
            $ajax['system_message']='Edit Payment Request Approve is required.';
            $this->json_return($ajax);
        }
        $result=Query_helper::get_info($this->config->item('table_pos_payment_edit'),'*',array('id ='.$id),1);
        if(!$result)
        {
            System_helper::invalid_try('Save_approve Non Exists',$id);
            $ajax['status']=false;
            $ajax['system_message']='Invalid Edit Payment Request Approve.';
            $this->json_return($ajax);
        }
        if($result['status']==$this->config->item('system_status_delete'))
        {
            $ajax['status']=false;
            $ajax['system_message']='This Item is deleted. You can not approve or reject it';
            $this->json_return($ajax);
        }
        if($result['status_approve']==$this->config->item('system_status_approved'))
        {
            $ajax['status']=false;
            $ajax['system_message']='This Item is already approved. You can not approve or reject it';
            $this->json_return($ajax);
        }
        if($result['status_approve']==$this->config->item('system_status_rejected'))
        {
            $ajax['status']=false;
            $ajax['system_message']='This Item is already rejected. You can not approve or reject it';
            $this->json_return($ajax);
        }
        //Checking Valid Outlet
        if(!$this->check_valid_outlet($result['outlet_id'],$invalid_try='Save_approve Outlet Non Assigned',$message='You are trying to approve edit payment request from an outlet which is not assigned to you.'))
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->message;
            $this->json_return($ajax);
        }
        $this->db->trans_start();  //DB Transaction Handle START
        $item['date_updated_approve_edit']=$time;
        $item['user_updated_approve_edit']=$user->user_id;
        Query_helper::update($this->config->item('table_pos_payment_edit'),$item,array('id='.$id));

        //This item from pos_payment table and temporary data for inserting edit history table
        $temp_payment_edit_history=Query_helper::get_info($this->config->item('table_pos_payment'),'*',array('id ='.$result['payment_id']),1);

        $result_history=Query_helper::get_info($this->config->item('table_pos_payment_edit_history'),'*',array('payment_id ='.$result['payment_id'],'revision =1'),1);
        if($result_history)
        {
            $data=array();
            $data['user_updated']=$result_history['user_updated'];
            $this->db->set('revision', 'revision+1', FALSE);
            Query_helper::update($this->config->item('table_pos_payment_edit_history'),$data,array('id='.$result['payment_id']),false);
        }
        $item_payment_edit_history=$temp_payment_edit_history;
        $item_payment_edit_history['payment_id']=$result['payment_id'];
        $item_payment_edit_history['revision']=1;
        Query_helper::add($this->config->item('table_pos_payment_edit_history'),$item_payment_edit_history, false);

        //This item from pos_payment_edit table and will update into pos_payment table
        $item_payment=$result;
        unset($item_payment['payment_id']);
        unset($item_payment['revision_count']);
        unset($item_payment['status_forward']);
        unset($item_payment['date_updated']);
        unset($item_payment['user_updated']);
        unset($item_payment['status_approve']);
        unset($item_payment['status']);
        $item_pos_payment['revision_count_payment']=$temp_payment_edit_history['revision_count_payment']+1;
        $item_pos_payment['revision_count_receive']=$temp_payment_edit_history['revision_count_receive']+1;
        $item_pos_payment['date_updated_approve_edit']=$item['date_updated_approve_edit'];
        $item_pos_payment['user_updated_approve_edit']=$user->user_id;
        Query_helper::update($this->config->item('table_pos_payment'),$item_payment,array('id='.$result['payment_id']));
        $this->db->trans_complete();   //DB Transaction Handle END
        if($this->db->trans_status() === TRUE)
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
    private function check_valid_outlet($outlet_id,$invalid_try,$message)
    {
        $user = User_helper::get_user();
        $this->db->from($this->config->item('table_pos_setup_user_outlet').' user_outlet');
        $this->db->select('user_outlet.customer_id outlet_id');
        $this->db->join($this->config->item('table_login_csetup_customer').' outlet','outlet.id=user_outlet.customer_id AND outlet.status="'.$this->config->item('system_status_active').'"','INNER');
        $this->db->join($this->config->item('table_login_csetup_cus_info').' outlet_info','outlet_info.customer_id=outlet.id AND outlet_info.revision=1 AND outlet_info.type="'.$this->config->item('system_customer_type_outlet_id').'"','INNER');
        $this->db->where('user_outlet.revision',1);
        $this->db->where('user_outlet.user_id',$user->user_id);
        $this->db->order_by('user_outlet.customer_id','ASC');
        $result_outlet=$this->db->get()->result_array();
        $assigned_outlet=array();
        foreach($result_outlet as $outlet)
        {
            $assigned_outlet[]=$outlet['outlet_id'];
        }
        if(!(in_array($outlet_id,$assigned_outlet)))
        {
            System_helper::invalid_try($invalid_try,$outlet_id);
            $this->message=$message;
            return false;
        }
        else
        {
            return true;
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
        $data['barcode']= 1;
        $data['date_payment']= 1;
        $data['date_sale']= 1;
        $data['date_receive']= 1;
        $data['outlet']= 1;
        $data['payment_way']= 1;
        $data['reference_no']= 1;
        $data['amount_payment']= 1;
        $data['amount_bank_charge']= 1;
        $data['amount_receive']= 1;
        $data['bank_payment_source']= 1;
        $data['bank_payment_branch_source']= 1;
        $data['bank_account_number_destination']= 1;
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
    private function system_set_preference_all_edit_payment_approve()
    {
        if(isset($this->permissions['action6']) && ($this->permissions['action6']==1))
        {
            $data['system_preference_items']=$this->get_preference_all_edit_payment_approve();
            $data['preference_method_name']='list_all';
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("preference_add_edit",$data,true));
            $ajax['system_page_url']=site_url($this->controller_url.'/index/set_preference_all_edit_payment_approve');
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function get_preference_all_edit_payment_approve()
    {
        $user = User_helper::get_user();
        $result=Query_helper::get_info($this->config->item('table_system_user_preference'),'*',array('user_id ='.$user->user_id,'controller ="' .$this->controller_url.'"','method ="list_all"'),1);
        $data['barcode']= 1;
        $data['date_payment']= 1;
        $data['date_sale']= 1;
        $data['date_receive']= 1;
        $data['outlet']= 1;
        $data['payment_way']= 1;
        $data['reference_no']= 1;
        $data['amount_payment']= 1;
        $data['amount_bank_charge']= 1;
        $data['amount_receive']= 1;
        $data['bank_payment_source']= 1;
        $data['bank_payment_branch_source']= 1;
        $data['bank_account_number_destination']= 1;
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
