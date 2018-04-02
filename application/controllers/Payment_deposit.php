<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Payment_deposit extends Root_Controller
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
        $this->permissions=User_helper::get_permission('Payment_deposit');
        $this->controller_url='payment_deposit';
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
        elseif($action=="delete")
        {
            $this->system_delete($id);
        }
        elseif($action=="forward")
        {
            $this->system_payment_forward($id);
        }
        elseif($action=="save_forward")
        {
            $this->system_save_payment_forward();
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
            $data['title']="Pending List";
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
        $this->db->from($this->config->item('table_pos_payment').' payment');
        $this->db->select('payment.*');
        $this->db->select('outlet_info.name outlet_name');
        $this->db->join($this->config->item('table_login_csetup_cus_info').' outlet_info','outlet_info.customer_id=payment.outlet_id AND outlet_info.revision=1','INNER');
        $this->db->select('payment_way.name payment_way');
        $this->db->join($this->config->item('table_login_setup_payment_way').' payment_way','payment_way.id=payment.payment_way_id','INNER');
        $this->db->select('bank_source.name bank_payment_source');
        $this->db->join($this->config->item('table_login_setup_bank').' bank_source','bank_source.id=payment.bank_id_source','INNER');
        $this->db->join($this->config->item('table_login_setup_bank_account').' ba','ba.id=payment.bank_account_id_destination','LEFT');
        $this->db->select('bank_destination.name bank_destination, ba.account_number, ba.branch_name');
        $this->db->join($this->config->item('table_login_setup_bank').' bank_destination','bank_destination.id=ba.bank_id','LEFT');
        $this->db->where('payment.status !=',$this->config->item('system_status_delete'));
        $this->db->where('payment.status_deposit_forward',$this->config->item('system_status_pending'));
        $this->db->where_in('payment.outlet_id',$this->user_outlet_ids);
        $this->db->order_by('payment.id','DESC');
        $items=$this->db->get()->result_array();
        foreach($items as &$item)
        {
            $item['barcode']=Barcode_helper::get_barcode_payment($item['id']);
            $item['date_payment']=System_helper::display_date($item['date_payment']);
            $item['date_sale']=System_helper::display_date($item['date_sale']);
            $item['amount_payment']=number_format($item['amount_payment'],2);
            $item['bank_account_number_destination']=$item['account_number'].'('.$item['bank_destination'].' -'.$item['branch_name'].')';
        }
        $this->json_return($items);
    }
    private function get_preference()
    {
        $user = User_helper::get_user();
        $result=Query_helper::get_info($this->config->item('table_system_user_preference'),'*',array('user_id ='.$user->user_id,'controller ="' .$this->controller_url.'"','method ="list"'),1);
        $data['barcode']= 1;
        $data['date_payment']= 1;
        $data['date_sale']= 1;
        $data['outlet_name']= 1;
        $data['payment_way']= 1;
        $data['reference_no']= 1;
        $data['amount_payment']= 1;
        $data['bank_payment_source']= 1;
        $data['bank_branch_source']= 1;
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
    private function system_list_all()
    {
        if(isset($this->permissions['action0'])&&($this->permissions['action0']==1))
        {
            $data['system_preference_items']= $this->get_preference_all();
            $data['title']="All Payment List";
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
        $this->db->from($this->config->item('table_pos_payment').' payment');
        $this->db->select('payment.*');
        $this->db->select('payment.status_deposit_forward status_forward,payment.status_payment_receive status_receive');
        $this->db->select('outlet_info.name outlet_name');
        $this->db->join($this->config->item('table_login_csetup_cus_info').' outlet_info','outlet_info.customer_id=payment.outlet_id AND outlet_info.revision=1','INNER');
        $this->db->select('payment_way.name payment_way');
        $this->db->join($this->config->item('table_login_setup_payment_way').' payment_way','payment_way.id=payment.payment_way_id','INNER');
        $this->db->select('bank_source.name bank_payment_source');
        $this->db->join($this->config->item('table_login_setup_bank').' bank_source','bank_source.id=payment.bank_id_source','INNER');
        $this->db->join($this->config->item('table_login_setup_bank_account').' ba','ba.id=payment.bank_account_id_destination','LEFT');
        $this->db->select('bank_destination.name bank_destination, ba.account_number, ba.branch_name');
        $this->db->join($this->config->item('table_login_setup_bank').' bank_destination','bank_destination.id=ba.bank_id','LEFT');
        $this->db->where('payment.status !=',$this->config->item('system_status_delete'));
        $this->db->where_in('payment.outlet_id',$this->user_outlet_ids);
        $this->db->order_by('payment.id','DESC');
        $this->db->limit($pagesize,$current_records);
        $items=$this->db->get()->result_array();
        foreach($items as &$item)
        {
            $item['barcode']=Barcode_helper::get_barcode_payment($item['id']);
            $item['date_payment']=System_helper::display_date($item['date_payment']);
            $item['date_sale']=System_helper::display_date($item['date_sale']);
            $item['amount_payment']=number_format($item['amount_payment'],2);
            $item['bank_account_number_destination']=$item['account_number'].'('.$item['bank_destination'].' -'.$item['branch_name'].')';
        }
        $this->json_return($items);
    }
    private function get_preference_all()
    {
        $user = User_helper::get_user();
        $result=Query_helper::get_info($this->config->item('table_system_user_preference'),'*',array('user_id ='.$user->user_id,'controller ="' .$this->controller_url.'"','method ="list_all"'),1);
        $data['barcode']= 1;
        $data['date_payment']= 1;
        $data['date_sale']= 1;
        $data['outlet_name']= 1;
        $data['payment_way']= 1;
        $data['reference_no']= 1;
        $data['amount_payment']= 1;
        $data['bank_payment_source']= 1;
        $data['bank_branch_source']= 1;
        $data['bank_account_number_destination']= 1;
        $data['status_forward']= 1;
        $data['status_receive']= 1;
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
    private function system_add()
    {
        if(isset($this->permissions['action1'])&&($this->permissions['action1']==1))
        {
            $data['title']="New Payment";
            $data["item"] = Array(
                'id' => 0,
                'date_payment'=>'',
                'date_sale'=>'',
                'outlet_id'=>'',
                'payment_way_id'=>'',
                'reference_no'=>'',
                'amount_payment'=>'',
                'bank_id_source'=>'',
                'bank_branch_source'=>'',
                'bank_account_id_destination'=>'',
                'image_location'=>'',
                'image_name'=>'',
                'remarks_deposit'=>'',
                'revision_count_receive_reject'=>0
            );
            $data['payment_way']=Query_helper::get_info($this->config->item('table_login_setup_payment_way'),array('id value, name text'),array('status ="'.$this->config->item('system_status_active').'"'));
            $data['bank_source']=Query_helper::get_info($this->config->item('table_login_setup_bank'),array('id bank_id_source, name bank_name_source'),array('status ="'.$this->config->item('system_status_active').'"'),0,0,array('ordering'));

            //getting bank account
            $this->db->from($this->config->item('table_login_setup_bank_account').' ba');
            $this->db->select('ba.id value');
            $this->db->select("CONCAT_WS(' ( ',ba.account_number,  CONCAT_WS('', bank.name,' - ',ba.branch_name,')')) text");
            $this->db->join($this->config->item('table_login_setup_bank').' bank','bank.id=ba.bank_id','INNER');
            $this->db->join($this->config->item('table_login_setup_bank_account_purpose').' bap','bap.bank_account_id=ba.id AND bap.revision=1 AND bap.purpose ="'.$this->config->item('system_bank_account_purpose_sale_receive').'"','INNER');
            $this->db->where('ba.status',$this->config->item('system_status_active'));
            $this->db->where('ba.account_type_receive',1);
            $this->db->where('bank.status',$this->config->item('system_status_active'));
            $this->db->order_by('bank.ordering','ASC');
            $data['bank_accounts_destination']=$this->db->get()->result_array();
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/add_edit",$data,true));
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
            $data['item']=Query_helper::get_info($this->config->item('table_pos_payment'),array('*'),array('id ='.$item_id,'status !="'.$this->config->item('system_status_delete').'"'),1);
            if(!$data['item'])
            {
                System_helper::invalid_try('Edit',$item_id,'Id Not Exists');
                $ajax['status']=false;
                $ajax['system_message']='Invalid Payment Deposit.';
                $this->json_return($ajax);
            }
            if($data['item']['status_deposit_forward']==$this->config->item('system_status_forwarded'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Payment already forwarded. You can not Edit it';
                $this->json_return($ajax);
            }
            $user = User_helper::get_user();
            // Checking Valid Outlet
            if(!in_array($data['item']['outlet_id'],$this->user_outlet_ids))
            {
                System_helper::invalid_try('Edit',$item_id,'outlet id '.$data['item']['outlet_id'].' not assigned');
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
                $this->json_return($ajax);
                die();
            }

            $data['payment_way']=Query_helper::get_info($this->config->item('table_login_setup_payment_way'),array('id value, name text'),array('status ="'.$this->config->item('system_status_active').'"'));
            $data['bank_source']=Query_helper::get_info($this->config->item('table_login_setup_bank'),array('id bank_id_source, name bank_name_source'),array('status !="'.$this->config->item('system_status_delete').'"'),0,0,array('ordering'));

            //getting bank account
            $this->db->from($this->config->item('table_login_setup_bank_account').' ba');
            $this->db->select('ba.id value');
            $this->db->select("CONCAT_WS(' ( ',ba.account_number,  CONCAT_WS('', bank.name,' - ',ba.branch_name,')')) text");
            $this->db->join($this->config->item('table_login_setup_bank').' bank','bank.id=ba.bank_id','INNER');
            $this->db->join($this->config->item('table_login_setup_bank_account_purpose').' bap','bap.bank_account_id=ba.id AND bap.revision=1 AND bap.purpose ="'.$this->config->item('system_bank_account_purpose_sale_receive').'"','INNER');
            $this->db->where('ba.status !=',$this->config->item('system_status_delete'));
            $this->db->where('ba.account_type_receive',1);
            $this->db->where('bank.status !=',$this->config->item('system_status_delete'));
            $this->db->order_by('bank.ordering','ASC');
            $data['bank_accounts_destination']=$this->db->get()->result_array();

            $user_ids=array();
            $user_ids[$data['item']['user_deposit_updated']]=$data['item']['user_deposit_updated'];
            if($data['item']['user_receive_rejected']>0)
            {
                $user_ids[$data['item']['user_receive_rejected']]=$data['item']['user_receive_rejected'];
            }
            $data['users']=System_helper::get_users_info($user_ids);

            $data['title']="Edit Payment:: ". Barcode_helper::get_barcode_payment($data['item']['id']);
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/add_edit",$data,true));
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
        $id = $this->input->post("id");
        $user = User_helper::get_user();
        $time=time();
        $item=$this->input->post('item');
        if($id>0)
        {
            if(!(isset($this->permissions['action2']) && ($this->permissions['action2']==1)))
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
                $this->json_return($ajax);
            }
            $result=Query_helper::get_info($this->config->item('table_pos_payment'),array('*'),array('id ='.$id,'status !="'.$this->config->item('system_status_delete').'"'),1);
            if(!$result)
            {
                System_helper::invalid_try('Save',$id,'Id not exists');
                $ajax['status']=false;
                $ajax['system_message']='Invalid Payment.';
                $this->json_return($ajax);
            }
            if($result['status_deposit_forward']==$this->config->item('system_status_forwarded'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Payment already forwarded. You can not edit it';
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
        if(!$this->check_validation())
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->message;
            $this->json_return($ajax);
        }
        if(!in_array($item['outlet_id'],$this->user_outlet_ids))
        {
            System_helper::invalid_try('Save',$id,'outlet id '.$item['outlet_id'].' not assigned');
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
            die();
        }
        //Uploading attachment
        $date_payment=str_replace('-','_',strtolower($item['date_payment']));
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
        $this->db->trans_start();  //DB Transaction Handle START
        if($id>0)
        {
            $item['date_payment']=System_helper::get_time($item['date_payment']);
            $item['date_sale']=System_helper::get_time($item['date_sale']);
            $item['date_deposit_updated']=$time;
            $item['user_deposit_updated']=$user->user_id;
            $this->db->set('revision_count_deposit', 'revision_count_deposit+1', FALSE);
            Query_helper::update($this->config->item('table_pos_payment'),$item,array('id='.$id), true);
        }
        else
        {
            $item['date_payment']=System_helper::get_time($item['date_payment']);
            $item['date_sale']=System_helper::get_time($item['date_sale']);
            $item['date_deposit_updated']=$time;
            $item['user_deposit_updated']=$user->user_id;
            Query_helper::add($this->config->item('table_pos_payment'),$item, true);
        }
        $this->db->trans_complete();   //DB Transaction Handle END
        if ($this->db->trans_status() === TRUE)
        {
            $save_and_new=$this->input->post('system_save_new_status');
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
        $this->form_validation->set_rules('item[date_payment]',$this->lang->line('LABEL_DATE_PAYMENT'),'required');
        $this->form_validation->set_rules('item[date_sale]',$this->lang->line('LABEL_DATE_SALE'),'required');
        $this->form_validation->set_rules('item[outlet_id]',$this->lang->line('LABEL_OUTLET'),'required');
        $this->form_validation->set_rules('item[payment_way_id]',$this->lang->line('LABEL_PAYMENT_WAY'),'required');
        $this->form_validation->set_rules('item[amount_payment]',$this->lang->line('LABEL_AMOUNT_PAYMENT'),'required');
        $this->form_validation->set_rules('item[bank_id_source]',$this->lang->line('LABEL_BANK_NAME'),'required');
        $this->form_validation->set_rules('item[bank_account_id_destination]',$this->lang->line('LABEL_BANK_ACCOUNT_NUMBER'),'required');
        if($this->form_validation->run() == FALSE)
        {
            $this->message=validation_errors();
            return false;
        }
        $item=$this->input->post('item');
        if((System_helper::get_time($item['date_sale']))>(System_helper::get_time($item['date_payment'])))
        {
            $this->message='Sale Date Must be less than Payment Date';
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
            $this->db->from($this->config->item('table_pos_payment').' payment');
            $this->db->select('payment.*');
            $this->db->select('outlet_info.name outlet_name');
            $this->db->join($this->config->item('table_login_csetup_cus_info').' outlet_info','outlet_info.customer_id=payment.outlet_id AND outlet_info.revision=1','INNER');
            $this->db->select('payment_way.name payment_way');
            $this->db->join($this->config->item('table_login_setup_payment_way').' payment_way','payment_way.id=payment.payment_way_id','INNER');
            $this->db->select('bank_source.name bank_payment_source');
            $this->db->join($this->config->item('table_login_setup_bank').' bank_source','bank_source.id=payment.bank_id_source','INNER');
            $this->db->join($this->config->item('table_login_setup_bank_account').' ba','ba.id=payment.bank_account_id_destination','LEFT');
            $this->db->select('bank_destination.name bank_destination, ba.account_number, ba.branch_name');
            $this->db->join($this->config->item('table_login_setup_bank').' bank_destination','bank_destination.id=ba.bank_id','LEFT');
            $this->db->where('payment.status !=',$this->config->item('system_status_delete'));
            $this->db->where('payment.id',$item_id);
            $data['item']=$this->db->get()->row_array();
            if(!$data['item'])
            {
                System_helper::invalid_try('Details',$item_id,'Id Not Exists');
                $ajax['status']=false;
                $ajax['system_message']='Invalid Payment.';
                $this->json_return($ajax);
            }
            if(!in_array($data['item']['outlet_id'],$this->user_outlet_ids))
            {
                System_helper::invalid_try('Forward',$item_id,'outlet id '.$data['item']['outlet_id'].' not assigned');
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
                $this->json_return($ajax);
                die();
            }
            $user_ids=array();
            $user_ids[$data['item']['user_deposit_updated']]=$data['item']['user_deposit_updated'];
            if($data['item']['user_deposit_forwarded']>0)
            {
                $user_ids[$data['item']['user_deposit_forwarded']]=$data['item']['user_deposit_forwarded'];
            }
            if($data['item']['user_payment_received']>0)
            {
                $user_ids[$data['item']['user_payment_received']]=$data['item']['user_payment_received'];
            }
            if($data['item']['user_manual_edit_approved']>0)
            {
                $user_ids[$data['item']['user_manual_edit_approved']]=$data['item']['user_manual_edit_approved'];
            }
            $data['users']=System_helper::get_users_info($user_ids);

            $data['title']="Payment Details :: ".Barcode_helper::get_barcode_payment($item_id);

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
            $result=Query_helper::get_info($this->config->item('table_pos_payment'),'*',array('id ='.$item_id, 'status != "'.$this->config->item('system_status_delete').'"'),1);
            if(!$result)
            {
                System_helper::invalid_try('Delete',$item_id.'Id not exists');
                $ajax['status']=false;
                $ajax['system_message']='Invalid Payment.';
                $this->json_return($ajax);
            }
            if($result['status_deposit_forward']==$this->config->item('system_status_forwarded'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Payment already forwarded. You can not delete it';
                $this->json_return($ajax);
            }
            $user = User_helper::get_user();
            // Checking Valid Outlet
            if(!in_array($result['outlet_id'],$this->user_outlet_ids))
            {
                System_helper::invalid_try('Delete',$item_id,'outlet id '.$result['outlet_id'].' not assigned');
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
                $this->json_return($ajax);
                die();
            }

            $this->db->trans_start();  //DB Transaction Handle START
            $item['status']=$this->config->item('system_status_delete');
            $item['date_deposit_updated']=time();
            $item['user_deposit_updated']=$user->user_id;
            Query_helper::update($this->config->item('table_pos_payment'),$item,array('id='.$item_id), true);
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
    private function system_payment_forward($id)
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
            $this->db->from($this->config->item('table_pos_payment').' payment');
            $this->db->select('payment.*');
            $this->db->select('outlet_info.name outlet_name');
            $this->db->join($this->config->item('table_login_csetup_cus_info').' outlet_info','outlet_info.customer_id=payment.outlet_id AND outlet_info.revision=1','INNER');
            $this->db->select('payment_way.name payment_way');
            $this->db->join($this->config->item('table_login_setup_payment_way').' payment_way','payment_way.id=payment.payment_way_id','INNER');
            $this->db->select('bank_source.name bank_payment_source');
            $this->db->join($this->config->item('table_login_setup_bank').' bank_source','bank_source.id=payment.bank_id_source','INNER');
            $this->db->join($this->config->item('table_login_setup_bank_account').' ba','ba.id=payment.bank_account_id_destination','LEFT');
            $this->db->select('bank_destination.name bank_destination, ba.account_number, ba.branch_name');
            $this->db->join($this->config->item('table_login_setup_bank').' bank_destination','bank_destination.id=ba.bank_id','LEFT');
            $this->db->where('payment.status !=',$this->config->item('system_status_delete'));
            $this->db->where('payment.id',$item_id);
            $data['item']=$this->db->get()->row_array();
            if(!$data['item'])
            {
                System_helper::invalid_try('Forward',$item_id,'Id Not Exists');
                $ajax['status']=false;
                $ajax['system_message']='Invalid Payment.';
                $this->json_return($ajax);
            }
            if($data['item']['status_deposit_forward']==$this->config->item('system_status_forwarded'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Payment already forwarded.';
                $this->json_return($ajax);
            }
            if(!in_array($data['item']['outlet_id'],$this->user_outlet_ids))
            {
                System_helper::invalid_try('Forward',$item_id,'outlet id '.$data['item']['outlet_id'].' not assigned');
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
                $this->json_return($ajax);
                die();
            }
            $user_ids=array();
            $user_ids[$data['item']['user_deposit_updated']]=$data['item']['user_deposit_updated'];
            $data['users']=System_helper::get_users_info($user_ids);

            $data['title']="Payment Forward :: ".Barcode_helper::get_barcode_payment($item_id);
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
    private function system_save_payment_forward()
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
        if($item['status_deposit_forward']!=$this->config->item('system_status_forwarded'))
        {
            $ajax['status']=false;
            $ajax['system_message']='Select Forward Option.';
            $this->json_return($ajax);
        }
        $result=Query_helper::get_info($this->config->item('table_pos_payment'),'*',array('id ='.$id, 'status != "'.$this->config->item('system_status_delete').'"'),1);
        if(!$result)
        {
            System_helper::invalid_try('Save Forward',$id,'Id Not Exists');
            $ajax['status']=false;
            $ajax['system_message']='Invalid Payment Forward.';
            $this->json_return($ajax);
        }
        if($result['status_deposit_forward']==$this->config->item('system_status_forwarded'))
        {
            $ajax['status']=false;
            $ajax['system_message']='Already Payment Forwarded.';
            $this->json_return($ajax);
        }
        //Checking Valid Outlet
        if(!in_array($result['outlet_id'],$this->user_outlet_ids))
        {
            System_helper::invalid_try('Save Forward',$id,'outlet id '.$result['outlet_id'].' not assigned');
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
            die();
        }
        $this->db->trans_start();  //DB Transaction Handle START
        $item['date_deposit_forwarded']=$time;
        $item['user_deposit_forwarded']=$user->user_id;
        //$item['status_deposit_forward']=$this->config->item('system_status_forwarded');

        Query_helper::update($this->config->item('table_pos_payment'),$item,array('id='.$id));

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
    private function system_set_preference_all()
    {
        if(isset($this->permissions['action6']) && ($this->permissions['action6']==1))
        {
            $data['system_preference_items']=$this->get_preference_all();
            $data['preference_method_name']='list_all';
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("preference_add_edit",$data,true));
            $ajax['system_page_url']=site_url($this->controller_url.'/index/set_preference_all');
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
