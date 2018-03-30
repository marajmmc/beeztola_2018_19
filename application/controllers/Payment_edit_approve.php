<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Payment_edit_approve extends Root_Controller
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
        $this->permissions=User_helper::get_permission('Payment_edit_approve');
        $this->controller_url='payment_edit_approve';
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
        elseif($action=="details_all")
        {
            $this->system_details_all($id);
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
            $data['title']="Payment Edit Approve List(Pending)";
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
    private function get_preference()
    {
        $user = User_helper::get_user();
        $result=Query_helper::get_info($this->config->item('table_system_user_preference'),'*',array('user_id ='.$user->user_id,'controller ="' .$this->controller_url.'"','method ="list"'),1);
        $data['id']= 1;
        $data['barcode']= 1;
        $data['date_payment']= 1;
        $data['date_sale']= 1;
        $data['date_receive']= 1;
        $data['outlet_name']= 1;
        $data['payment_way']= 1;
        $data['reference_no']= 1;
        $data['amount_payment']= 1;
        $data['amount_bank_charge']= 1;
        $data['amount_receive']= 1;
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
    private function system_get_items()
    {
        $this->db->from($this->config->item('table_pos_payment_edit').' payment_edit');
        $this->db->select('payment_edit.*');
        $this->db->select('outlet_info.name outlet_name');
        $this->db->join($this->config->item('table_login_csetup_cus_info').' outlet_info','outlet_info.customer_id=payment_edit.outlet_id AND outlet_info.revision=1','INNER');
        $this->db->select('payment_way.name payment_way');
        $this->db->join($this->config->item('table_login_setup_payment_way').' payment_way','payment_way.id=payment_edit.payment_way_id','INNER');
        $this->db->select('bank_source.name bank_payment_source');
        $this->db->join($this->config->item('table_login_setup_bank').' bank_source','bank_source.id=payment_edit.bank_id_source','INNER');
        $this->db->join($this->config->item('table_login_setup_bank_account').' ba','ba.id=payment_edit.bank_account_id_destination','LEFT');
        $this->db->select('bank_destination.name bank_destination, ba.account_number, ba.branch_name');
        $this->db->join($this->config->item('table_login_setup_bank').' bank_destination','bank_destination.id=ba.bank_id','LEFT');
        $this->db->where('payment_edit.status !=',$this->config->item('system_status_delete'));
        $this->db->where('payment_edit.status_request_forward',$this->config->item('system_status_forwarded'));
        $this->db->where('payment_edit.status_approve',$this->config->item('system_status_pending'));
        $this->db->where_in('payment_edit.outlet_id',$this->user_outlet_ids);
        $this->db->order_by('payment_edit.id','DESC');
        $items=$this->db->get()->result_array();
        foreach($items as &$item)
        {
            $item['barcode']=Barcode_helper::get_barcode_payment($item['payment_id']);
            $item['date_payment']=System_helper::display_date($item['date_payment']);
            $item['date_sale']=System_helper::display_date($item['date_sale']);
            $item['date_receive']=System_helper::display_date($item['date_receive']);
            $item['amount_payment']=number_format($item['amount_payment'],2);
            $item['amount_bank_charge']=number_format($item['amount_bank_charge'],2);
            $item['amount_receive']=number_format($item['amount_receive'],2);
            $item['bank_account_number_destination']=$item['account_number'].'('.$item['bank_destination'].' -'.$item['branch_name'].')';
        }
        $this->json_return($items);

    }
    private function system_list_all()
    {
        if(isset($this->permissions['action0'])&&($this->permissions['action0']==1))
        {
            $data['system_preference_items']= $this->get_preference_all();
            $data['title']="All Payment Edit List";
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
    private function get_preference_all()
    {
        $user = User_helper::get_user();
        $result=Query_helper::get_info($this->config->item('table_system_user_preference'),'*',array('user_id ='.$user->user_id,'controller ="' .$this->controller_url.'"','method ="list_all"'),1);
        $data['id']= 1;
        $data['barcode']= 1;
        $data['date_payment']= 1;
        $data['date_sale']= 1;
        $data['date_receive']= 1;
        $data['outlet_name']= 1;
        $data['payment_way']= 1;
        $data['reference_no']= 1;
        $data['amount_payment']= 1;
        $data['amount_bank_charge']= 1;
        $data['amount_receive']= 1;
        $data['bank_payment_source']= 1;
        $data['bank_branch_source']= 1;
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
        $this->db->from($this->config->item('table_pos_payment_edit').' payment_edit');
        $this->db->select('payment_edit.*');
        $this->db->select('outlet_info.name outlet_name');
        $this->db->join($this->config->item('table_login_csetup_cus_info').' outlet_info','outlet_info.customer_id=payment_edit.outlet_id AND outlet_info.revision=1','INNER');
        $this->db->select('payment_way.name payment_way');
        $this->db->join($this->config->item('table_login_setup_payment_way').' payment_way','payment_way.id=payment_edit.payment_way_id','INNER');
        $this->db->select('bank_source.name bank_payment_source');
        $this->db->join($this->config->item('table_login_setup_bank').' bank_source','bank_source.id=payment_edit.bank_id_source','INNER');
        $this->db->join($this->config->item('table_login_setup_bank_account').' ba','ba.id=payment_edit.bank_account_id_destination','LEFT');
        $this->db->select('bank_destination.name bank_destination, ba.account_number, ba.branch_name');
        $this->db->join($this->config->item('table_login_setup_bank').' bank_destination','bank_destination.id=ba.bank_id','LEFT');
        $this->db->where('payment_edit.status !=',$this->config->item('system_status_delete'));
        $this->db->where('payment_edit.status_request_forward',$this->config->item('system_status_forwarded'));
        $this->db->where_in('payment_edit.outlet_id',$this->user_outlet_ids);
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
            $item['amount_bank_charge']=number_format($item['amount_bank_charge'],2);
            $item['amount_receive']=number_format($item['amount_receive'],2);
            $item['bank_account_number_destination']=$item['account_number'].'('.$item['bank_destination'].' -'.$item['branch_name'].')';
        }
        $this->json_return($items);
    }
    private function system_edit($id)
    {
        if((isset($this->permissions['action2']) && ($this->permissions['action2']==1)))
        {
            if($id>0)
            {
                $edit_id=$id;
            }
            else
            {
                $edit_id=$this->input->post('id');
            }
            $this->db->from($this->config->item('table_pos_payment_edit').' payment_edit');
            $this->db->select('payment_edit.*');
            $this->db->select('outlet_info.name outlet_name');
            $this->db->join($this->config->item('table_login_csetup_cus_info').' outlet_info','outlet_info.customer_id=payment_edit.outlet_id AND outlet_info.revision=1','INNER');
            $this->db->select('payment_way.name payment_way');
            $this->db->join($this->config->item('table_login_setup_payment_way').' payment_way','payment_way.id=payment_edit.payment_way_id','INNER');
            $this->db->select('bank_source.name bank_payment_source');
            $this->db->join($this->config->item('table_login_setup_bank').' bank_source','bank_source.id=payment_edit.bank_id_source','INNER');
            $this->db->join($this->config->item('table_login_setup_bank_account').' ba','ba.id=payment_edit.bank_account_id_destination','LEFT');
            $this->db->select('bank_destination.name bank_destination, ba.account_number, ba.branch_name');
            $this->db->join($this->config->item('table_login_setup_bank').' bank_destination','bank_destination.id=ba.bank_id','LEFT');
            $this->db->where('payment_edit.status !=',$this->config->item('system_status_delete'));
            $this->db->where('payment_edit.id',$edit_id);
            $data['item']=$this->db->get()->row_array();

            if(!$data['item'])
            {
                System_helper::invalid_try('Edit',$edit_id,'Id Not Exists');
                $ajax['status']=false;
                $ajax['system_message']="Invalid Request";
                $this->json_return($ajax);
            }
            if($data['item']['status_request_forward']!=$this->config->item('system_status_forwarded'))
            {
                $ajax['status']=false;
                $ajax['system_message']='This Edit Request Not forwarded Yet.';
                $this->json_return($ajax);
            }
            if($data['item']['status_approve']!=$this->config->item('system_status_pending'))
            {
                $ajax['status']=false;
                $ajax['system_message']='This Edit already Approved/Rejected';
                $this->json_return($ajax);
            }
            if(!in_array($data['item']['outlet_id'],$this->user_outlet_ids))
            {
                System_helper::invalid_try('Forward',$edit_id,'outlet id '.$data['item']['outlet_id'].' not assigned');
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
                $this->json_return($ajax);
                die();
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
            $this->db->where('payment.id',$data['item']['payment_id']);
            $data['item_current']=$this->db->get()->row_array();

            $user_ids=array();
            $user_ids[$data['item']['user_request_updated']]=$data['item']['user_request_updated'];
            $user_ids[$data['item']['user_request_forwarded']]=$data['item']['user_request_forwarded'];
            $data['users']=System_helper::get_users_info($user_ids);
            $data['title']='Approve/Reject Payment Edit';
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/edit",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/edit/'.$edit_id);
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
        $edit_id = $this->input->post("id");
        $user = User_helper::get_user();
        $time=time();
        $item=$this->input->post('item');
        if(!((isset($this->permissions['action2']) && ($this->permissions['action2']==1))))
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
        if(!$this->check_validation())
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->message;
            $this->json_return($ajax);
        }
        $item_new=Query_helper::get_info($this->config->item('table_pos_payment_edit'),'*',array('id ='.$edit_id,'status !="'.$this->config->item('system_status_delete').'"'),1);
        if(!$item_new)
        {
            System_helper::invalid_try('Save',$edit_id,'Id Not Exists');
            $ajax['status']=false;
            $ajax['system_message']="Invalid Request";
            $this->json_return($ajax);
        }
        if($item_new['status_request_forward']!=$this->config->item('system_status_forwarded'))
        {
            $ajax['status']=false;
            $ajax['system_message']='This Edit Request Not forwarded Yet.';
            $this->json_return($ajax);
        }
        if($item_new['status_approve']!=$this->config->item('system_status_pending'))
        {
            $ajax['status']=false;
            $ajax['system_message']='This Edit already Approved/Rejected';
            $this->json_return($ajax);
        }
        if(!in_array($item_new['outlet_id'],$this->user_outlet_ids))
        {
            System_helper::invalid_try('Forward',$edit_id,'outlet id '.$item_new['outlet_id'].' not assigned');
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
            die();
        }
        $item_current=Query_helper::get_info($this->config->item('table_pos_payment'),'*',array('id ='.$item_new['payment_id'],'status !="'.$this->config->item('system_status_delete').'"'),1);
        if(!$item_current)
        {
            System_helper::invalid_try('Save',$edit_id,'Payment Id ('.$item_new['payment_id'].') Not exits');
            $ajax['status']=false;
            $ajax['system_message']="Invalid Request";
            $this->json_return($ajax);
        }

        $this->db->trans_start();  //DB Transaction Handle START
        $item['date_manual_edit_approved']=$time;
        $item['user_manual_edit_approved']=$user->user_id;
        Query_helper::update($this->config->item('table_pos_payment_edit'),$item,array('id='.$edit_id));
        if($item['status_approve']==$this->config->item('system_status_approved'))
        {
            //get old value
            //insert into history table
            //update new value
        }

        //This item from pos_payment table and temporary data for inserting edit history table
        /*$temp_payment_edit_history=Query_helper::get_info($this->config->item('table_pos_payment'),'*',array('id ='.$result['payment_id']),1);

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
        Query_helper::update($this->config->item('table_pos_payment'),$item_payment,array('id='.$result['payment_id']));*/
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
    private function check_validation()
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('item[status_approve]',"Approve/Reject",'required');
        $this->form_validation->set_rules('item[remarks_manual_edit_approved]','Remarks','required');
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
