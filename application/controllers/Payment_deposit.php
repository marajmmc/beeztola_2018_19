<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Payment_deposit extends Root_Controller
{
    public $message;
    public $permissions;
    public $controller_url;
    public function __construct()
    {
        parent::__construct();
        $this->message="";
        $this->permissions=User_helper::get_permission('Payment_deposit');
        $this->controller_url='payment_deposit';
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
        elseif($action=="set_preference")
        {
            $this->system_set_preference();
        }
        elseif($action=="set_preference_all_payment")
        {
            $this->system_set_preference_all_payment();
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
            $data['title']="Pending Payment List";
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
        $this->db->select('outlet_info.name outlet');
        $this->db->join($this->config->item('table_login_csetup_cus_info').' outlet_info','outlet_info.customer_id=payment.outlet_id AND outlet_info.revision=1 AND outlet_info.type="'.$this->config->item('system_customer_type_outlet_id').'"','INNER');
        $this->db->select('bank_source.name bank_name');
        $this->db->join($this->config->item('table_login_setup_bank').' bank_source','bank_source.id=payment.bank_id_source','INNER');
        $this->db->where('payment.status !=',$this->config->item('system_status_delete'));
        $this->db->where('payment.status_payment_forward =',$this->config->item('system_status_pending'));
        $this->db->order_by('payment.id','DESC');
        $items=$this->db->get()->result_array();
        foreach($items as &$item)
        {
            $item['barcode']=Barcode_helper::get_barcode_payment($item['id']);
            $item['date_payment']=System_helper::display_date($item['date_payment']);
            $item['date_sale']=System_helper::display_date($item['date_sale']);
            $item['amount_payment']=number_format($item['amount_payment'],2,'.','');
            $item['branch_name']=$item['bank_branch_source'];
        }
        $this->json_return($items);
    }
    private function system_list_all()
    {
        if(isset($this->permissions['action0'])&&($this->permissions['action0']==1))
        {
            $data['system_preference_items']= $this->get_preference_all_payment();
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
        $this->db->select('outlet_info.name outlet');
        $this->db->join($this->config->item('table_login_csetup_cus_info').' outlet_info','outlet_info.customer_id=payment.outlet_id AND outlet_info.revision=1 AND outlet_info.type="'.$this->config->item('system_customer_type_outlet_id').'"','INNER');
        $this->db->select('bank_source.name bank_name');
        $this->db->join($this->config->item('table_login_setup_bank').' bank_source','bank_source.id=payment.bank_id_source','INNER');
        $this->db->where('payment.status !=',$this->config->item('system_status_delete'));
        $this->db->order_by('payment.id','DESC');
        $this->db->limit($pagesize,$current_records);
        $items=$this->db->get()->result_array();
        foreach($items as &$item)
        {
            $item['barcode']=Barcode_helper::get_barcode_payment($item['id']);
            $item['date_payment']=System_helper::display_date($item['date_payment']);
            $item['date_sale']=System_helper::display_date($item['date_sale']);
            $item['amount_payment']=number_format($item['amount_payment'],2,'.','');
            $item['branch_name']=$item['bank_branch_source'];
            $item['payment_status']=$item['status_payment_forward'];
        }
        $this->json_return($items);
    }
    private function system_add()
    {
        if(isset($this->permissions['action1'])&&($this->permissions['action1']==1))
        {
            $time=time();
            $data['title']="Create New Payment Deposit";
            $data["item"] = Array(
                'id' => 0,
                'date_payment'=>$time,
                'date_sale'=>'',
                'outlet_id'=>'',
                'type_payment'=>'',
                'reference_no'=>'',
                'amount_payment'=>'',
                'bank_id_source'=>'',
                'bank_branch_source'=>'',
                'remarks_payment'=>'',
                'image_location'=>'',
                'image_name'=>''

            );

            $user = User_helper::get_user();
            $this->db->from($this->config->item('table_pos_setup_user_outlet').' user_outlet');
            $this->db->select('user_outlet.customer_id outlet_id, outlet_info.name outlet_name');
            $this->db->join($this->config->item('table_login_csetup_customer').' outlet','outlet.id=user_outlet.customer_id AND outlet.status="'.$this->config->item('system_status_active').'"','INNER');
            $this->db->join($this->config->item('table_login_csetup_cus_info').' outlet_info','outlet_info.customer_id=outlet.id AND outlet_info.revision=1 AND outlet_info.type="'.$this->config->item('system_customer_type_outlet_id').'"','INNER');
            $this->db->where('user_outlet.revision',1);
            $this->db->where('user_outlet.user_id',$user->id);
            $this->db->order_by('user_outlet.customer_id','ASC');
            $data['assigned_outlet']=$this->db->get()->result_array();
            $data['bank_source']=Query_helper::get_info($this->config->item('table_login_setup_bank'),array('id bank_id_source, name bank_name_source'),array('status ="'.$this->config->item('system_status_active').'"'));


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
            $data['item']=Query_helper::get_info($this->config->item('table_pos_payment'),array('*'),array('id ='.$item_id,'status !="'.$this->config->item('system_status_delete').'"','status_payment_forward ="'.$this->config->item('system_status_pending').'"'),1);
            if(!$data['item'])
            {
                System_helper::invalid_try('Edit Non Exists',$item_id);
                $ajax['status']=false;
                $ajax['system_message']='Invalid Payment Deposit.';
                $this->json_return($ajax);
            }

            $user = User_helper::get_user();
            $this->db->from($this->config->item('table_pos_setup_user_outlet').' user_outlet');
            $this->db->select('user_outlet.customer_id outlet_id, outlet_info.name outlet_name');
            $this->db->join($this->config->item('table_login_csetup_customer').' outlet','outlet.id=user_outlet.customer_id AND outlet.status="'.$this->config->item('system_status_active').'"','INNER');
            $this->db->join($this->config->item('table_login_csetup_cus_info').' outlet_info','outlet_info.customer_id=outlet.id AND outlet_info.revision=1 AND outlet_info.type="'.$this->config->item('system_customer_type_outlet_id').'"','INNER');
            $this->db->where('user_outlet.revision',1);
            $this->db->where('user_outlet.user_id',$user->id);
            $this->db->order_by('user_outlet.customer_id','ASC');
            $data['assigned_outlet']=$this->db->get()->result_array();

            $data['bank_source']=Query_helper::get_info($this->config->item('table_login_setup_bank'),array('id bank_id_source, name bank_name_source'),array('status ="'.$this->config->item('system_status_active').'"'));


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

            $result=Query_helper::get_info($this->config->item('table_pos_payment'),array('*'),array('id ='.$id,'status !="'.$this->config->item('system_status_delete').'"','status_payment_forward ="'.$this->config->item('system_status_pending').'"'),1);
            if(!$result)
            {
                System_helper::invalid_try('Update Non Exists',$id);
                $ajax['status']=false;
                $ajax['system_message']='Invalid Payment Deposit.';
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
        
        // Checking Valid Outlet
        $user = User_helper::get_user();
        $this->db->from($this->config->item('table_pos_setup_user_outlet').' user_outlet');
        $this->db->select('user_outlet.customer_id outlet_id, outlet_info.name outlet_name');
        $this->db->join($this->config->item('table_login_csetup_customer').' outlet','outlet.id=user_outlet.customer_id AND outlet.status="'.$this->config->item('system_status_active').'"','INNER');
        $this->db->join($this->config->item('table_login_csetup_cus_info').' outlet_info','outlet_info.customer_id=outlet.id AND outlet_info.revision=1 AND outlet_info.type="'.$this->config->item('system_customer_type_outlet_id').'"','INNER');
        $this->db->where('user_outlet.revision',1);
        $this->db->where('user_outlet.user_id',$user->id);
        $this->db->order_by('user_outlet.customer_id','ASC');
        $result_outlet=$this->db->get()->result_array();
        $assigned_outlet=array();
        foreach($result_outlet as $outlet)
        {
            $assigned_outlet[]=$outlet['outlet_id'];
        }

        if(!(in_array($item['outlet_id'],$assigned_outlet)))
        {
            System_helper::invalid_try('Save Non Exists',$item['outlet_id']);
            $ajax['status']=false;
            $ajax['system_message']='Invalid Payment Deposit.';
            $this->json_return($ajax);
        }

        //Uploading attachment
        $path='images/payment/'.$item['date_payment'];
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
            $item['date_updated']=$time;
            $item['user_updated']=$user->user_id;
            $this->db->set('revision_count_payment', 'revision_count_payment+1', FALSE);
            Query_helper::update($this->config->item('table_pos_payment'),$item,array('id='.$id), true);
        }
        else
        {
            $item['date_payment']=System_helper::get_time($item['date_payment']);
            $item['date_sale']=System_helper::get_time($item['date_sale']);
            $item['date_updated']=$time;
            $item['user_updated']=$user->user_id;
            Query_helper::add($this->config->item('table_pos_payment'),$item, true);
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
        $this->form_validation->set_rules('item[outlet_id]',$this->lang->line('LABEL_OUTLET'),'required');
        $this->form_validation->set_rules('item[type_payment]',$this->lang->line('LABEL_TYPE_PAYMENT'),'required');
        $this->form_validation->set_rules('item[reference_no]',$this->lang->line('LABEL_REFERENCE_NO'),'required');
        $this->form_validation->set_rules('item[amount_payment]',$this->lang->line('LABEL_AMOUNT_PAYMENT'),'required');
        $this->form_validation->set_rules('item[bank_id_source]',$this->lang->line('LABEL_BANK_NAME'),'required');
        $this->form_validation->set_rules('item[bank_branch_source]',$this->lang->line('LABEL_BRANCH_NAME'),'required');
        if($this->form_validation->run() == FALSE)
        {
            $this->message=validation_errors();
            return false;
        }
        return true;
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
        $data['outlet']= 1;
        $data['type_payment']= 1;
        $data['reference_no']= 1;
        $data['amount_payment']= 1;
        $data['bank_name']= 1;
        $data['branch_name']= 1;

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
    private function system_set_preference_all_payment()
    {
        if(isset($this->permissions['action6']) && ($this->permissions['action6']==1))
        {
            $data['system_preference_items']=$this->get_preference_all_payment();
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
    private function get_preference_all_payment()
    {
        $user = User_helper::get_user();
        $result=Query_helper::get_info($this->config->item('table_system_user_preference'),'*',array('user_id ='.$user->user_id,'controller ="' .$this->controller_url.'"','method ="list_all"'),1);
        $data['barcode']= 1;
        $data['date_payment']= 1;
        $data['date_sale']= 1;
        $data['outlet']= 1;
        $data['type_payment']= 1;
        $data['reference_no']= 1;
        $data['amount_payment']= 1;
        $data['bank_name']= 1;
        $data['branch_name']= 1;
        $data['payment_status']= 1;

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
