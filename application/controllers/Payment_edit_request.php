<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Payment_edit_request extends Root_Controller
{
    public $message;
    public $permissions;
    public $controller_url;
    public function __construct()
    {
        parent::__construct();
        $this->message="";
        $this->permissions=User_helper::get_permission('Payment_edit_request');
        $this->controller_url='payment_edit_request';
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
        elseif($action=="search")
        {
            $this->system_search();
        }
        elseif($action=="add")
        {
            $this->system_add();
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
        elseif($action=="details_all")
        {
            $this->system_details_all($id);
        }
        elseif($action=="delete")
        {
            $this->system_delete($id);
        }
        elseif($action=="forward")
        {
            $this->system_forward($id);
        }
        elseif($action=="save_forward")
        {
            $this->system_save_forward();
        }
        elseif($action=="set_preference")
        {
            $this->system_set_preference();
        }
        elseif($action=="set_preference_all_payment_edit_request")
        {
            $this->system_set_preference_all_payment_edit_request();
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
            $data['title']="Pending Edit Payment Request List";
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
        $this->db->where('payment_edit.status_forward =',$this->config->item('system_status_pending'));
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
            $data['system_preference_items']= $this->get_preference_all_payment_edit_request();
            $data['title']="All Payment Edit Request List";
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
            $item['status_payment_edit']=$item['status_forward'];
            if($item['bank_account_id_destination'])
            {
                $item['bank_account_number_destination']=$item['account_number'].'('.$item['bank_destination'].' -'.$item['branch_name'].')';
            }
        }
        $this->json_return($items);
    }
    private function system_search()
    {
        if(isset($this->permissions['action1'])&&($this->permissions['action1']==1))
        {
            $data['title']="New Payment Edit Request";
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
            if($this->input->post('item'))
            {
                $item=$this->input->post('item');

                if(!$item['barcode'])
                {
                    $ajax['status']=false;
                    $ajax['system_message']='This Barcode field is required.';
                    $this->json_return($ajax);
                }
            }
            else
            {
                $this->system_search();
            }
            $item_id=intval(substr($item['barcode'],2));
            $data['title']="New Payment Edit Request";
            $data['item']=Query_helper::get_info($this->config->item('table_pos_payment'),array('*'),array('id ='.$item_id),1);
            if(!$data['item'])
            {
                System_helper::invalid_try('Add Non Exists',$item_id);
                $ajax['status']=false;
                $ajax['system_message']='Invalid Edit Payment Request.';
                $this->json_return($ajax);
            }
            if($data['item']['status']==$this->config->item('system_status_delete'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Payment is deleted. You can not request to edit it';
                $this->json_return($ajax);
            }
            if($data['item']['status_receive']!==$this->config->item('system_status_complete'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Payment is not received yet. You can not request to edit it';
                $this->json_return($ajax);
            }
            $data['payment_id']=$data['item']['id'];
            $data['item_payment_edit']=Query_helper::get_info($this->config->item('table_pos_payment_edit'),array('*'),array('payment_id ='.$item_id),1);
            if($data['item_payment_edit']['status_approve']==$this->config->item('system_status_pending'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Your previous edit request for this barcode is waiting for approval';
                $this->json_return($ajax);
            }
            if($data['item_payment_edit']['id'])
            {
                $data['id']=$data['item_payment_edit']['id'];
            }
            else
            {
                $data['id']=0;
            }
            $user = User_helper::get_user();
            // Checking Valid Outlet
            if(!$this->check_valid_outlet($data['item']['outlet_id'],$invalid_try='Edit Outlet Non Assigned',$message='You are trying to send edit payment request from an outlet which is not assigned to you.'))
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->message;
                $this->json_return($ajax);
            }
            //Getting Assigned Outlet
            $this->db->from($this->config->item('table_pos_setup_user_outlet').' user_outlet');
            $this->db->select('user_outlet.customer_id outlet_id, outlet_info.name outlet_name');
            $this->db->join($this->config->item('table_login_csetup_customer').' outlet','outlet.id=user_outlet.customer_id AND outlet.status="'.$this->config->item('system_status_active').'"','INNER');
            $this->db->join($this->config->item('table_login_csetup_cus_info').' outlet_info','outlet_info.customer_id=outlet.id AND outlet_info.revision=1 AND outlet_info.type="'.$this->config->item('system_customer_type_outlet_id').'"','INNER');
            $this->db->where('user_outlet.revision',1);
            $this->db->where('user_outlet.user_id',$user->user_id);
            $this->db->order_by('user_outlet.customer_id','ASC');
            $data['assigned_outlet']=$this->db->get()->result_array();
            $data['payment_way']=Query_helper::get_info($this->config->item('table_login_setup_payment_way'),array('id value, name text'),array('status ="'.$this->config->item('system_status_active').'"'));
            $data['bank_source']=Query_helper::get_info($this->config->item('table_login_setup_bank'),array('id bank_id_source, name bank_name_source'),array('status !="'.$this->config->item('system_status_delete').'"'),0,0,array('ordering'));
            //getting bank account
            $this->db->from($this->config->item('table_login_setup_bank_account').' ba');
            $this->db->select('ba.id value');
            $this->db->select("CONCAT_WS(' ( ',ba.account_number,  CONCAT_WS('', bank.name,' - ',ba.branch_name,')')) text");
            $this->db->join($this->config->item('table_login_setup_bank').' bank','bank.id=ba.bank_id','INNER');
            $this->db->join($this->config->item('table_login_setup_bank_account_purpose').' bap','bap.bank_account_id=ba.id AND bap.revision=1 AND bap.purpose ="sale_receive"','INNER');
            $this->db->where('ba.status !=',$this->config->item('system_status_delete'));
            $this->db->where('ba.account_type_receive = 1');
            $this->db->where('bank.status !=',$this->config->item('system_status_delete'));
            $this->db->order_by('bank.ordering','ASC');
            $data['bank_account_number_destination']=$this->db->get()->result_array();
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>$this->load->view($this->controller_url."/add",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/add');
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function system_edit($id)
    {
        if(isset($this->permissions['action2'])&&($this->permissions['action2']==1))
        {
            if($id>0)
            {
                $item_id=$id;
            }
            else
            {
                $item_id=$this->input->post('id');
            }
            $data['item']=Query_helper::get_info($this->config->item('table_pos_payment_edit'),array('*'),array('id ='.$item_id),1);
            if(!$data['item'])
            {
                System_helper::invalid_try('Edit Non Exists',$item_id);
                $ajax['status']=false;
                $ajax['system_message']='Invalid Edit Payment Request.';
                $this->json_return($ajax);
            }
            if($data['item']['status']==$this->config->item('system_status_delete'))
            {
                $ajax['status']=false;
                $ajax['system_message']='This Payment Edit Request deleted. You can not Edit it';
                $this->json_return($ajax);
            }
            if($data['item']['status_forward']==$this->config->item('system_status_forwarded'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Edit Payment Request already forwarded. You can not Edit it';
                $this->json_return($ajax);
            }
            $user = User_helper::get_user();
            // Checking Valid Outlet
            if(!$this->check_valid_outlet($data['item']['outlet_id'],$invalid_try='Edit Outlet Non Assigned',$message='You are trying to send edit payment request from an outlet which is not assigned to you.'))
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->message;
                $this->json_return($ajax);
            }
            //Getting Assigned Outlet
            $this->db->from($this->config->item('table_pos_setup_user_outlet').' user_outlet');
            $this->db->select('user_outlet.customer_id outlet_id, outlet_info.name outlet_name');
            $this->db->join($this->config->item('table_login_csetup_customer').' outlet','outlet.id=user_outlet.customer_id AND outlet.status="'.$this->config->item('system_status_active').'"','INNER');
            $this->db->join($this->config->item('table_login_csetup_cus_info').' outlet_info','outlet_info.customer_id=outlet.id AND outlet_info.revision=1 AND outlet_info.type="'.$this->config->item('system_customer_type_outlet_id').'"','INNER');
            $this->db->where('user_outlet.revision',1);
            $this->db->where('user_outlet.user_id',$user->user_id);
            $this->db->order_by('user_outlet.customer_id','ASC');
            $data['assigned_outlet']=$this->db->get()->result_array();
            $data['payment_way']=Query_helper::get_info($this->config->item('table_login_setup_payment_way'),array('id value, name text'),array('status ="'.$this->config->item('system_status_active').'"'));
            $data['bank_source']=Query_helper::get_info($this->config->item('table_login_setup_bank'),array('id bank_id_source, name bank_name_source'),array('status !="'.$this->config->item('system_status_delete').'"'),0,0,array('ordering'));
            //getting bank account
            $this->db->from($this->config->item('table_login_setup_bank_account').' ba');
            $this->db->select('ba.id value');
            $this->db->select("CONCAT_WS(' ( ',ba.account_number,  CONCAT_WS('', bank.name,' - ',ba.branch_name,')')) text");
            $this->db->join($this->config->item('table_login_setup_bank').' bank','bank.id=ba.bank_id','INNER');
            $this->db->join($this->config->item('table_login_setup_bank_account_purpose').' bap','bap.bank_account_id=ba.id AND bap.revision=1 AND bap.purpose ="sale_receive"','INNER');
            $this->db->where('ba.status !=',$this->config->item('system_status_delete'));
            $this->db->where('ba.account_type_receive = 1');
            $this->db->where('bank.status !=',$this->config->item('system_status_delete'));
            $this->db->order_by('bank.ordering','ASC');
            $data['bank_account_number_destination']=$this->db->get()->result_array();
            $data['title']="Edit (Edit Payment Request):: ". Barcode_helper::get_barcode_payment($data['item']['payment_id']);
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/edit",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/edit/'.$item_id);
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
        $id = $this->input->post("id"); //Payment edit id
        $payment_id = $this->input->post("payment_id");
        $user = User_helper::get_user();
        $time=time();
        $item=$this->input->post('item');
        $result_payment_edit='';
        if($id>0)
        {
            if(!(isset($this->permissions['action2']) && ($this->permissions['action2']==1)))
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
                $this->json_return($ajax);
            }
            $result_payment_edit=Query_helper::get_info($this->config->item('table_pos_payment_edit'),array('*'),array('id ='.$id),1);
            if(!$result_payment_edit)
            {
                System_helper::invalid_try('Update Non Exists',$id);
                $ajax['status']=false;
                $ajax['system_message']='Invalid Edit Payment Request.';
                $this->json_return($ajax);
            }
            if($result_payment_edit['status']==$this->config->item('system_status_delete'))
            {
                $ajax['status']=false;
                $ajax['system_message']='This edit payment request deleted. You can not Save it';
                $this->json_return($ajax);
            }
            if($result_payment_edit['status_forward']==$this->config->item('system_status_forwarded'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Edit Payment Request already forwarded. You can not Save it';
                $this->json_return($ajax);
            }
        }
        else
        {
            if(!(isset($this->permissions['action1']) && ($this->permissions['action1']==1)))
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
                $this->json_return($ajax);
            }
        }

        //Payment Item Checking(by $payment_id)
        $result=Query_helper::get_info($this->config->item('table_pos_payment'),array('*'),array('id ='.$payment_id),1);
        if(!$result)
        {
            System_helper::invalid_try('Update Non Exists',$id);
            $ajax['status']=false;
            $ajax['system_message']='Invalid Payment Edit Request.';
            $this->json_return($ajax);
        }
        if($result['status']==$this->config->item('system_status_delete'))
        {
            $ajax['status']=false;
            $ajax['system_message']='Your requested payment is deleted. You can not save it';
            $this->json_return($ajax);
        }
        if($result['status_receive']!==$this->config->item('system_status_complete'))
        {
            $ajax['status']=false;
            $ajax['system_message']='Payment is not received yet. You can not save it';
            $this->json_return($ajax);
        }

        if(!$this->check_validation())
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->message;
            $this->json_return($ajax);
        }

        // Checking Valid Outlet
        if(!$this->check_valid_outlet($item['outlet_id'],$invalid_try='Save Outlet Non Assigned',$message='You are trying to save payment edit request from an outlet which is not assigned to you.'))
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->message;
            $this->json_return($ajax);
        }
        //Uploading attachment
        $date_payment=str_replace('-','_',$item['date_payment']);
        $path='images/payment/'.$date_payment;
        $dir=(FCPATH).$path;
        if(!is_dir($dir))
        {
            mkdir($dir, 0777);
        }
        $uploaded_images = System_helper::upload_file($path);
        if(array_key_exists('image_payment',$uploaded_images))
        {
            if($uploaded_images['image_payment']['status'])
            {
                $item['image_name']=$uploaded_images['image_payment']['info']['file_name'];
                $item['image_location']=$path.'/'.$uploaded_images['image_payment']['info']['file_name'];
            }
            else
            {
                $ajax['status']=false;
                $ajax['system_message']=$uploaded_images['image_payment']['message'];
                $this->json_return($ajax);
            }
        }
        if(!(isset($item['image_name'])))
        {
            if($id>0)
            {
                $item['image_name']=$result_payment_edit['image_name'];
                $item['image_location']=$result_payment_edit['image_location'];
            }
            else
            {
                $item['image_name']=$result['image_name'];
                $item['image_location']=$result['image_location'];
            }
        }
        $this->db->trans_start();  //DB Transaction Handle START
        if($id>0)
        {
            $item['payment_id']=$payment_id;
            $item['date_payment']=System_helper::get_time($item['date_payment']);
            $item['date_sale']=System_helper::get_time($item['date_sale']);
            $item['date_receive']=System_helper::get_time($item['date_receive']);
            $item['amount_receive']=$item['amount_payment']-$item['amount_bank_charge'];
            $item['date_updated']=$time;
            $item['user_updated']=$user->user_id;
            $this->db->set('revision_count', 'revision_count+1', FALSE);
            Query_helper::update($this->config->item('table_pos_payment_edit'),$item,array('id='.$id), true);
        }
        else
        {
            $item['payment_id']=$payment_id;
            $item['date_payment']=System_helper::get_time($item['date_payment']);
            $item['date_sale']=System_helper::get_time($item['date_sale']);
            $item['date_receive']=System_helper::get_time($item['date_receive']);
            $item['amount_receive']=$item['amount_payment']-$item['amount_bank_charge'];
            $item['date_updated']=$time;
            $item['user_updated']=$user->user_id;
            Query_helper::add($this->config->item('table_pos_payment_edit'),$item, true);
        }
        $this->db->trans_complete();   //DB Transaction Handle END
        if ($this->db->trans_status() === TRUE)
        {
            $save_and_new=$this->input->post('system_save_new_status');
            $this->message=$this->lang->line("MSG_SAVED_SUCCESS");
            if($save_and_new==1)
            {
                $this->system_add();
            }
            else
            {
                $this->system_list();
            }
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
        $this->form_validation->set_rules('item[date_payment]',$this->lang->line('LABEL_DATE_PAYMENT'),'required');
        $this->form_validation->set_rules('item[date_sale]',$this->lang->line('LABEL_DATE_SALE'),'required');
        $this->form_validation->set_rules('item[date_receive]',$this->lang->line('LABEL_DATE_RECEIVE'),'required');
        $this->form_validation->set_rules('item[outlet_id]',$this->lang->line('LABEL_OUTLET'),'required');
        $this->form_validation->set_rules('item[payment_way_id]',$this->lang->line('LABEL_PAYMENT_WAY'),'required');
        $this->form_validation->set_rules('item[reference_no]',$this->lang->line('LABEL_REFERENCE_NO'),'required');
        $this->form_validation->set_rules('item[amount_payment]',$this->lang->line('LABEL_AMOUNT_PAYMENT'),'required');
        $this->form_validation->set_rules('item[amount_bank_charge]',$this->lang->line('LABEL_AMOUNT_BANK_CHARGE'),'required');
        $this->form_validation->set_rules('item[bank_id_source]',$this->lang->line('LABEL_BANK_NAME'),'required');
        $this->form_validation->set_rules('item[bank_account_id_destination]',$this->lang->line('LABEL_BANK_ACCOUNT_NUMBER'),'required');
        if($this->form_validation->run() == FALSE)
        {
            $this->message=validation_errors();
            return false;
        }
        return true;
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
            $this->db->where('payment_edit.id',$item_id);
            $data['item']=$this->db->get()->row_array();
            if(!$data['item'])
            {
                System_helper::invalid_try('Details Edit Payment Request Non Exists',$item_id);
                $ajax['status']=false;
                $ajax['system_message']='Invalid Edit Payment Request Details.';
                $this->json_return($ajax);
            }
            if($data['item']['status']==$this->config->item('system_status_delete'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Edit Payment Request Deleted. You can not view details';
                $this->json_return($ajax);
            }
            //Checking Valid Outlet
            if(!$this->check_valid_outlet($data['item']['outlet_id'],$invalid_try='Details Outlet Non Assigned',$message='You are trying to view details of edit payment request from an outlet which is not assigned to you.'))
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->message;
                $this->json_return($ajax);
            }
            $data['title']="Edit Payment Request Details :: ".Barcode_helper::get_barcode_payment($data['item']['payment_id']);
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
            $this->db->where('payment_edit.id',$item_id);
            $data['item']=$this->db->get()->row_array();
            if(!$data['item'])
            {
                System_helper::invalid_try('Details_all Edit Payment Request Non Exists',$item_id);
                $ajax['status']=false;
                $ajax['system_message']='Invalid Edit Payment Request Details.';
                $this->json_return($ajax);
            }
            if($data['item']['status']==$this->config->item('system_status_delete'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Edit Payment Request Deleted. You can not view details';
                $this->json_return($ajax);
            }
            //Checking Valid Outlet
            if(!$this->check_valid_outlet($data['item']['outlet_id'],$invalid_try='Details Outlet Non Assigned',$message='You are trying to view details of edit payment request from an outlet which is not assigned to you.'))
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->message;
                $this->json_return($ajax);
            }
            $data['title']="Edit Payment Request Details :: ".Barcode_helper::get_barcode_payment($data['item']['payment_id']);
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
    private function system_delete($id)
    {
        if(isset($this->permissions['action3']) && ($this->permissions['action3']==1))
        {
            if($id>0)
            {
                $item_id=$id;
            }
            else
            {
                $item_id=$this->input->post('id');
            }
            $result=Query_helper::get_info($this->config->item('table_pos_payment_edit'),'*',array('id ='.$item_id),1);
            if(!$result)
            {
                System_helper::invalid_try('Delete Non Exists',$item_id);
                $ajax['status']=false;
                $ajax['system_message']='Invalid Payment.';
                $this->json_return($ajax);
            }
            if($result['status']==$this->config->item('system_status_delete'))
            {
                $ajax['status']=false;
                $ajax['system_message']='This item already deleted.';
                $this->json_return($ajax);
            }
            if($result['status_forward']==$this->config->item('system_status_forwarded'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Edit payment request already forwarded. You can not delete it';
                $this->json_return($ajax);
            }
            //Checking Valid Outlet
            if(!$this->check_valid_outlet($result['outlet_id'],$invalid_try='Delete Outlet Non Assigned',$message='You are trying to delete edit payment request from an outlet which is not assigned to you.'))
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->message;
                $this->json_return($ajax);
            }
            $this->db->trans_start();  //DB Transaction Handle START
            Query_helper::update($this->config->item('table_pos_payment_edit'),array('status'=>$this->config->item('system_status_delete')),array("id = ".$item_id));
            $this->db->trans_complete();   //DB Transaction Handle END

            if ($this->db->trans_status() === TRUE)
            {
                $this->message=$this->lang->line("MSG_DELETED_SUCCESS");
                $this->system_list();
            }
            else
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("MSG_SAVED_FAIL");
                $this->json_return($ajax);
            }
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function system_forward($id)
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
            $this->db->select('user_info_forwarded.name payment_edit_forwarded_by');
            $this->db->join($this->config->item('table_login_setup_user_info').' user_info_forwarded','user_info_forwarded.user_id = payment_edit.user_updated_forward','LEFT');
            $this->db->where('payment_edit.id',$item_id);
            $data['item']=$this->db->get()->row_array();
            if(!$data['item'])
            {
                System_helper::invalid_try('Edit_payment_forward Non Exists',$item_id);
                $ajax['status']=false;
                $ajax['system_message']='Invalid Edit Payment Request Forward.';
                $this->json_return($ajax);
            }
            if($data['item']['status']==$this->config->item('system_status_delete'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Edit payment request deleted. You can not forward it';
                $this->json_return($ajax);
            }
            if($data['item']['status_forward']==$this->config->item('system_status_forwarded'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Edit payment request already forwarded.';
                $this->json_return($ajax);
            }
            //Checking Valid Outlet
            if(!$this->check_valid_outlet($data['item']['outlet_id'],$invalid_try='Edit_payment_forward Outlet Non Assigned',$message='You are trying to forward edit payment request from an outlet which is not assigned to you.'))
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->message;
                $this->json_return($ajax);
            }
            $data['title']="Edit Payment Request Forward :: ".Barcode_helper::get_barcode_payment($data['item']['payment_id']);
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/forward",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/forward/'.$item_id);
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function system_save_forward()
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
        if($item['status_forward']!=$this->config->item('system_status_forwarded'))
        {
            $ajax['status']=false;
            $ajax['system_message']='Forward Payment is required.';
            $this->json_return($ajax);
        }
        $result=Query_helper::get_info($this->config->item('table_pos_payment_edit'),'*',array('id ='.$id),1);
        if(!$result)
        {
            System_helper::invalid_try('Save_forward Non Exists',$id);
            $ajax['status']=false;
            $ajax['system_message']='Invalid Edit Payment Request Forward.';
            $this->json_return($ajax);
        }
        if($result['status']==$this->config->item('system_status_delete'))
        {
            $ajax['status']=false;
            $ajax['system_message']='Edit payment request deleted. You can not forward it.';
            $this->json_return($ajax);
        }
        if($result['status_forward']==$this->config->item('system_status_forwarded'))
        {
            $ajax['status']=false;
            $ajax['system_message']='Already Forwarded.';
            $this->json_return($ajax);
        }
        //Checking Valid Outlet
        if(!$this->check_valid_outlet($result['outlet_id'],$invalid_try='Save_forward Outlet Non Assigned',$message='You are trying to forward edit payment request from an outlet which is not assigned to you.'))
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->message;
            $this->json_return($ajax);
        }
        $this->db->trans_start();  //DB Transaction Handle START
        $item['date_updated_forward']=$time;
        $item['user_updated_forward']=$user->user_id;
        Query_helper::update($this->config->item('table_pos_payment_edit'),$item,array('id='.$id));
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
    private function system_set_preference_all_payment_edit_request()
    {
        if(isset($this->permissions['action6']) && ($this->permissions['action6']==1))
        {
            $data['system_preference_items']=$this->get_preference_all_payment_edit_request();
            $data['preference_method_name']='list_all';
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("preference_add_edit",$data,true));
            $ajax['system_page_url']=site_url($this->controller_url.'/index/set_preference_all_payment');
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function get_preference_all_payment_edit_request()
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
        $data['status_payment_edit']= 1;
        $data['status']= 1;
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
