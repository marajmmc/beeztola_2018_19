<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Payment_edit_request extends Root_Controller
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
        $this->permissions=User_helper::get_permission('Payment_edit_request');
        $this->controller_url='payment_edit_request';
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
            $data['title']="Pending Edit List";
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
        $this->db->where('payment_edit.status_request_forward',$this->config->item('system_status_pending'));
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
        $data['status_forward']= 1;
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
        $this->db->select('payment_edit.status_request_forward status_forward');
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
    private function system_search()
    {
        if(isset($this->permissions['action1'])&&($this->permissions['action1']==1))
        {
            $data['title']="Search Payment For edit";
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
                $ajax['system_message']='This Barcode field is required.';
                $this->json_return($ajax);
            }

            $item_id=Barcode_helper::get_id_payment($item['barcode']);
            if(!($item_id>0))
            {
                $ajax['status']=false;
                $ajax['system_message']='Invalid Payment Barcode.';
                $this->json_return($ajax);
            }
            $data['item']=Query_helper::get_info($this->config->item('table_pos_payment'),array('*'),array('id ='.$item_id),1);
            if(!$data['item'])
            {
                $ajax['status']=false;
                $ajax['system_message']='Invalid Payment Barcode.';
                $this->json_return($ajax);
            }
            if($data['item']['status']==$this->config->item('system_status_delete'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Invalid Payment Barcode.';
                $this->json_return($ajax);
            }
            if($data['item']['status_payment_receive']!==$this->config->item('system_status_received'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Payment is not received yet.';
                $this->json_return($ajax);
            }
            if(!in_array($data['item']['outlet_id'],$this->user_outlet_ids))
            {
                System_helper::invalid_try('add',$item_id,'outlet id '.$data['item']['outlet_id'].' not assigned');
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
                $this->json_return($ajax);
                die();
            }

            $data['item']['payment_id']=$data['item']['id'];

            //check already requested for another edit

            $result=Query_helper::get_info($this->config->item('table_pos_payment_edit'),array('*'),array('payment_id ='.$item_id,'status_approve="'.$this->config->item('system_status_pending').'"','status !="'.$this->config->item('system_status_delete').'"'),1);
            if($result)
            {
                $ajax['status']=false;
                $ajax['system_message']='There is another Edit for this payment on Process';
                $this->json_return($ajax);
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

            $data['title']="New Edit Request for Payment(".Barcode_helper::get_barcode_payment($data['item']['id'].')');
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
    private function system_edit($id)
    {

        if(isset($this->permissions['action2'])&&($this->permissions['action2']==1))
        {
            if($id>0)
            {
                $edit_id=$id;
            }
            else
            {
                $edit_id=$this->input->post('id');
            }
            $data['item']=Query_helper::get_info($this->config->item('table_pos_payment_edit'),array('*'),array('id ='.$edit_id),1);
            if(!$data['item'])
            {
                System_helper::invalid_try('add',$edit_id,'Id Not Exists');
                $ajax['status']=false;
                $ajax['system_message']="Invalid Request";
                $this->json_return($ajax);
            }
            if($data['item']['status']==$this->config->item('system_status_delete'))
            {
                $ajax['status']=false;
                $ajax['system_message']="Invalid Request";
                $this->json_return($ajax);
            }
            if($data['item']['status_request_forward']!=$this->config->item('system_status_pending'))
            {
                $ajax['status']=false;
                $ajax['system_message']='This Request already forwarded. You can not Edit it';
                $this->json_return($ajax);
            }
            if(!in_array($data['item']['outlet_id'],$this->user_outlet_ids))
            {
                System_helper::invalid_try('edit',$edit_id,'outlet id '.$data['item']['outlet_id'].' not assigned');
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
            $data['title']="Edit (Payment Request): ". Barcode_helper::get_barcode_payment($data['item']['payment_id']);
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
        $edit_id = $this->input->post("id"); //Payment edit id
        $user = User_helper::get_user();
        $time=time();
        $item=$this->input->post('item');
        if($edit_id>0)
        {
            //edit pending
            //check lots of validaion like already forward??or deleted
            if(!(isset($this->permissions['action2']) && ($this->permissions['action2']==1)))
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
                $this->json_return($ajax);
            }
            $result=Query_helper::get_info($this->config->item('table_pos_payment_edit'),array('*'),array('id ='.$edit_id),1);

            if(!$result)
            {
                System_helper::invalid_try('Update',$edit_id,'Edit Id Not Exists');
                $ajax['status']=false;
                $ajax['system_message']="Invalid Request";
                $this->json_return($ajax);
            }
            if($result['status']==$this->config->item('system_status_delete'))
            {
                $ajax['status']=false;
                $ajax['system_message']="Invalid Request";
                $this->json_return($ajax);
            }
            if($result['status_request_forward']!=$this->config->item('system_status_pending'))
            {
                $ajax['status']=false;
                $ajax['system_message']='This Request already forwarded. You can not Edit it';
                $this->json_return($ajax);
            }
            //do not need to check outlet id
            //it got checked bellow from sales
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
        $result=Query_helper::get_info($this->config->item('table_pos_payment'),array('*'),array('id ='.$item['payment_id']),1);
        if(!$result)
        {
            System_helper::invalid_try('Save',$edit_id,'Payment id('.$item['payment_id'].') not exits');
            $ajax['status']=false;
            $ajax['system_message']='Invalid Payment.';
            $this->json_return($ajax);
        }
        if($result['status']==$this->config->item('system_status_delete'))
        {
            $ajax['status']=false;
            $ajax['system_message']='Invalid Payment.';
            $this->json_return($ajax);
        }
        if($result['status_payment_receive']!==$this->config->item('system_status_received'))
        {
            $ajax['status']=false;
            $ajax['system_message']='Payment is not received yet.';
            $this->json_return($ajax);
        }
        if(!in_array($result['outlet_id'],$this->user_outlet_ids))
        {
            System_helper::invalid_try('Save',$edit_id,'outlet id '.$result['outlet_id'].' not assigned');
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
            die();
        }
        if($edit_id==0)
        {
            $item['image_name']=$result['image_name'];
            $item['image_location']=$result['image_location'];
        }

        if(!in_array($item['outlet_id'],$this->user_outlet_ids))
        {
            System_helper::invalid_try('Save',$edit_id,'New outlet id '.$item['outlet_id'].' not assigned');
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
            die();
        }
        if(!$this->check_validation())
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->message;
            $this->json_return($ajax);
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
        if($edit_id>0)
        {

            $item['date_payment']=System_helper::get_time($item['date_payment']);
            $item['date_sale']=System_helper::get_time($item['date_sale']);
            $item['date_receive']=System_helper::get_time($item['date_receive']);
            $item['amount_receive']=$item['amount_payment']-$item['amount_bank_charge'];
            $item['date_request_updated']=$time;
            $item['user_request_updated']=$user->user_id;
            $this->db->set('revision_count_edit_request', 'revision_count_edit_request+1', FALSE);
            Query_helper::update($this->config->item('table_pos_payment_edit'),$item,array('id='.$edit_id), true);

        }
        else
        {
            $item['date_payment']=System_helper::get_time($item['date_payment']);
            $item['date_sale']=System_helper::get_time($item['date_sale']);
            $item['date_receive']=System_helper::get_time($item['date_receive']);
            $item['amount_receive']=$item['amount_payment']-$item['amount_bank_charge'];
            $item['date_request_updated']=$time;
            $item['user_request_updated']=$user->user_id;
            Query_helper::add($this->config->item('table_pos_payment_edit'),$item, true);
        }
        $this->db->trans_complete();   //DB Transaction Handle END
        if ($this->db->trans_status() === TRUE)
        {
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
        $this->form_validation->set_rules('item[date_receive]',$this->lang->line('LABEL_DATE_RECEIVE'),'required');
        $this->form_validation->set_rules('item[outlet_id]',$this->lang->line('LABEL_OUTLET'),'required');
        $this->form_validation->set_rules('item[payment_way_id]',$this->lang->line('LABEL_PAYMENT_WAY'),'required');
        $this->form_validation->set_rules('item[reference_no]',$this->lang->line('LABEL_REFERENCE_NO'),'required');
        $this->form_validation->set_rules('item[amount_payment]',$this->lang->line('LABEL_AMOUNT_PAYMENT'),'required');
        $this->form_validation->set_rules('item[amount_bank_charge]',$this->lang->line('LABEL_AMOUNT_BANK_CHARGE'),'required');
        $this->form_validation->set_rules('item[bank_id_source]',$this->lang->line('LABEL_BANK_NAME'),'required');
        $this->form_validation->set_rules('item[bank_account_id_destination]',$this->lang->line('LABEL_BANK_ACCOUNT_NUMBER'),'required');
        $this->form_validation->set_rules('item[remarks_request]','Edit Reason','required');
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
        if((System_helper::get_time($item['date_payment']))>(System_helper::get_time($item['date_receive'])))
        {
            $ajax['status']=false;
            $ajax['system_message']='Receive Date Must be same or greater than Payment Date';
            $this->json_return($ajax);
        }

        return true;
    }
    private function system_details($id)
    {
        if(isset($this->permissions['action0']) && ($this->permissions['action0']==1))
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
            if($data['item']['status_approve']==$this->config->item('system_status_approved'))
            {
                $this->db->from($this->config->item('table_pos_payment_edit_history').' payment_history');
                $this->db->select('payment_history.*');
                $this->db->select('outlet_info.name outlet_name');
                $this->db->join($this->config->item('table_login_csetup_cus_info').' outlet_info','outlet_info.customer_id=payment_history.outlet_id AND outlet_info.revision=1','INNER');
                $this->db->select('payment_way.name payment_way');
                $this->db->join($this->config->item('table_login_setup_payment_way').' payment_way','payment_way.id=payment_history.payment_way_id','INNER');
                $this->db->select('bank_source.name bank_payment_source');
                $this->db->join($this->config->item('table_login_setup_bank').' bank_source','bank_source.id=payment_history.bank_id_source','INNER');
                $this->db->join($this->config->item('table_login_setup_bank_account').' ba','ba.id=payment_history.bank_account_id_destination','LEFT');
                $this->db->select('bank_destination.name bank_destination, ba.account_number, ba.branch_name');
                $this->db->join($this->config->item('table_login_setup_bank').' bank_destination','bank_destination.id=ba.bank_id','LEFT');
                $this->db->where('payment_history.edit_id',$edit_id);
                $data['item_current']=$this->db->get()->row_array();
            }
            else
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
                $this->db->where('payment.id',$data['item']['payment_id']);
                $data['item_current']=$this->db->get()->row_array();
            }


            if(!$data['item'])
            {
                System_helper::invalid_try('Details',$edit_id,'Id Not Exists');
                $ajax['status']=false;
                $ajax['system_message']="Invalid Request";
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
            $user_ids=array();
            $user_ids[$data['item']['user_request_updated']]=$data['item']['user_request_updated'];
            if($data['item']['user_request_forwarded']>0)
            {
                $user_ids[$data['item']['user_request_forwarded']]=$data['item']['user_request_forwarded'];
            }
            if($data['item']['user_manual_edit_approved']>0)
            {
                $user_ids[$data['item']['user_manual_edit_approved']]=$data['item']['user_manual_edit_approved'];
            }
            $data['users']=System_helper::get_users_info($user_ids);

            $data['title']="Details Of Edit Request: ".Barcode_helper::get_barcode_payment($data['item']['payment_id']);
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/details",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/details/'.$edit_id);
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
                $edit_id=$id;
            }
            else
            {
                $edit_id=$this->input->post('id');
            }
            $result=Query_helper::get_info($this->config->item('table_pos_payment_edit'),array('*'),array('id ='.$edit_id),1);
            if(!$result)
            {
                System_helper::invalid_try('Delete',$edit_id,'Id Not Exists');
                $ajax['status']=false;
                $ajax['system_message']="Invalid Request";
                $this->json_return($ajax);
            }
            if($result==$this->config->item('system_status_delete'))
            {
                $ajax['status']=false;
                $ajax['system_message']="This Request already Deleted";
                $this->json_return($ajax);
            }
            if($result['status_request_forward']!=$this->config->item('system_status_pending'))
            {
                $ajax['status']=false;
                $ajax['system_message']='This Request already forwarded. You can not Delete it';
                $this->json_return($ajax);
            }
            if(!in_array($result['outlet_id'],$this->user_outlet_ids))
            {
                System_helper::invalid_try('Delete',$edit_id,'outlet id '.$result['outlet_id'].' not assigned');
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
                $this->json_return($ajax);
                die();
            }

            $this->db->trans_start();  //DB Transaction Handle START
            Query_helper::update($this->config->item('table_pos_payment_edit'),array('status'=>$this->config->item('system_status_delete')),array("id = ".$edit_id));
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
                System_helper::invalid_try('Forward',$edit_id,'Id Not Exists');
                $ajax['status']=false;
                $ajax['system_message']="Invalid Request";
                $this->json_return($ajax);
            }
            if($data['item']['status_request_forward']!=$this->config->item('system_status_pending'))
            {
                $ajax['status']=false;
                $ajax['system_message']='This Request already forwarded.';
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
            $user_ids=array();
            $user_ids[$data['item']['user_request_updated']]=$data['item']['user_request_updated'];
            $data['users']=System_helper::get_users_info($user_ids);
            $data['title']="Forward Payment Request :".Barcode_helper::get_barcode_payment($data['item']['payment_id']);
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/forward",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/forward/'.$edit_id);
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
        $edit_id = $this->input->post("id");
        $user = User_helper::get_user();
        $time=time();
        $item=$this->input->post('item');
        if(!((isset($this->permissions['action7']) && ($this->permissions['action7']==1))))
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
        if($item['status_request_forward']!=$this->config->item('system_status_forwarded'))
        {
            $ajax['status']=false;
            $ajax['system_message']='Please Select Forward.';
            $this->json_return($ajax);
        }
        $result=Query_helper::get_info($this->config->item('table_pos_payment_edit'),'*',array('id ='.$edit_id),1);
        if(!$result)
        {
            System_helper::invalid_try('Save_forward',$edit_id,'Id Not Exists');
            $ajax['status']=false;
            $ajax['system_message']='Invalid Edit Payment Request Forward.';
            $this->json_return($ajax);
        }
        if($result['status']==$this->config->item('system_status_delete'))
        {
            $ajax['status']=false;
            $ajax['system_message']='Invalid Forward Request.';
            $this->json_return($ajax);
        }
        if($result['status_request_forward']!=$this->config->item('system_status_pending'))
        {
            $ajax['status']=false;
            $ajax['system_message']='This Request already forwarded.';
            $this->json_return($ajax);
        }
        if(!in_array($result['outlet_id'],$this->user_outlet_ids))
        {
            System_helper::invalid_try('Save_forward',$edit_id,'outlet id '.$result['outlet_id'].' not assigned');
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
            die();
        }
        $this->db->trans_start();  //DB Transaction Handle START
        $item['date_request_forwarded']=$time;
        $item['user_request_forwarded']=$user->user_id;
        Query_helper::update($this->config->item('table_pos_payment_edit'),$item,array('id='.$edit_id));
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
