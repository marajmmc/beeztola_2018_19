<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Setup_farmer_type extends Root_Controller
{
    public $message;
    public $permissions;
    public $controller_url;
    public function __construct()
    {
        parent::__construct();
        $this->message="";
        $this->permissions=User_helper::get_permission('Setup_farmer_type');
        $this->controller_url='setup_farmer_type';
    }
    public function index($action="list",$id=0,$id1=0)
    {
        if($action=="list")
        {
            $this->system_list();
        }
        elseif($action=="get_items")
        {
            $this->system_get_items();
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
        elseif($action=="outlet_discount_list")
        {
            $this->system_outlet_discount_list($id);
        }
        elseif($action=="get_outlet_discount_items")
        {
            $this->system_get_outlet_discount_items();
        }
        elseif($action=='outlet_discount_edit')
        {
            $this->system_outlet_discount_edit($id,$id1);
        }
        elseif($action=="save_outlet_discount")
        {
            $this->system_save_outlet_discount();
        }
        elseif($action=="set_preference")
        {
            $this->system_set_preference();
        }
        elseif($action=="save_preference")
        {
            System_helper::save_preference();
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
            $data['title']="Farmer Type List";
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
        $this->db->from($this->config->item('table_pos_setup_farmer_type').' farmer_type');
        $this->db->where('farmer_type.status !=',$this->config->item('system_status_delete'));
        $this->db->order_by('farmer_type.ordering','ASC');
        $this->db->order_by('farmer_type.id','ASC');
        $items=$this->db->get()->result_array();
        $this->json_return($items);
    }
    private function system_add()
    {
        if(isset($this->permissions['action1'])&&($this->permissions['action1']==1))
        {
            $data['title']="Create New Farmer Type";
            $data['item']['id']=0;
            $data['item']['name']='';
            $data['item']['discount_self_percentage']='';
            $data['item']['discount_referral_percentage']='';
            $data['item']['commission_distributor']='';
            $data['item']['remarks']='';
            $data['item']['ordering']=99;

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

            $data['item']=Query_helper::get_info($this->config->item('table_pos_setup_farmer_type'),array('*'),array('id ='.$item_id,'status !="'.$this->config->item('system_status_delete').'"'),1,0,array('id ASC'));
            if(!$data['item'])
            {
                System_helper::invalid_try('Edit Non Exists',$item_id);
                $ajax['status']=false;
                $ajax['system_message']='Invalid Farmer Type.';
                $this->json_return($ajax);
            }

            $data['title']="Edit Farmer Type :: ". $data['item']['name'];
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

            $result=Query_helper::get_info($this->config->item('table_pos_setup_farmer_type'),'*',array('id ='.$id, 'status != "'.$this->config->item('system_status_delete').'"'),1);
            if(!$result)
            {
                System_helper::invalid_try('Update Non Exists',$id);
                $ajax['status']=false;
                $ajax['system_message']='Invalid Farmer Type.';
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

        $this->db->trans_start();  //DB Transaction Handle START

        if($id>0)
        {
            $data=array();
            $data['date_updated'] = $time;
            $data['user_updated'] = $user->user_id;
            Query_helper::update($this->config->item('table_pos_setup_farmer_type_histories'),$data, array('id='.$id,'revision=1'), false);

            $this->db->where('id',$id);
            $this->db->set('revision', 'revision+1', FALSE);
            $this->db->update($this->config->item('table_pos_setup_farmer_type_histories'));

            $item['date_updated']=$time;
            $item['user_updated']=$user->user_id;
            $this->db->set('revision_count', 'revision_count+1', FALSE);
            Query_helper::update($this->config->item('table_pos_setup_farmer_type'),$item,array('id='.$id), false);

            $item['revision']=1;
            $item['date_created']=$time;
            $item['user_created']=$user->user_id;
            Query_helper::add($this->config->item('table_pos_setup_farmer_type_histories'),$item, false);

        }
        else
        {
            $item['date_created']=$time;
            $item['user_created']=$user->user_id;
            Query_helper::add($this->config->item('table_pos_setup_farmer_type'),$item, false);

            Query_helper::add($this->config->item('table_pos_setup_farmer_type_histories'),$item, false);
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
        $this->form_validation->set_rules('item[name]',$this->lang->line('LABEL_NAME'),'required');
        $this->form_validation->set_rules('item[discount_self_percentage]',$this->lang->line('LABEL_DISCOUNT_SELF_PERCENTAGE'),'required');
        $this->form_validation->set_rules('item[discount_referral_percentage]',$this->lang->line('LABEL_DISCOUNT_REFERRAL_PERCENTAGE'),'required');
        $this->form_validation->set_rules('item[commission_distributor]',$this->lang->line('LABEL_COMMISSION_DISTRIBUTOR'),'required');
        $this->form_validation->set_rules('item[ordering]',$this->lang->line('LABEL_ORDER'),'required');
        if($this->form_validation->run() == FALSE)
        {
            $this->message=validation_errors();
            return false;
        }
        return true;
    }
    private function system_outlet_discount_list($id)
    {
        if(isset($this->permissions['action7'])&&($this->permissions['action7']==1))
        {
            if($id>0)
            {
                $data['farmer_type_id']=$id;
            }
            else
            {
                $data['farmer_type_id']=$this->input->post('id');
            }

            $valid_farmer_type=Query_helper::get_info($this->config->item('table_pos_setup_farmer_type'),'*',array('id ='.$data['farmer_type_id']),1);
            if(!$valid_farmer_type)
            {
                System_helper::invalid_try('List Outlet Discount Non Exists',$data['farmer_type_id']);
                $ajax['status']=false;
                $ajax['system_message']='Invalid Try.';
                $this->json_return($ajax);
            }
            //$data['system_preference_items']= $this->get_preference();
            $data['title']="Outlet Discount for-".$valid_farmer_type['name'];
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/outlet_discount_list",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/outlet_discount_list/'.$data['farmer_type_id']);

            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function system_get_outlet_discount_items()
    {
        $farmer_type_id=$this->input->post('farmer_type_id');
        $time=time();
        $user = User_helper::get_user();
        $this->db->from($this->config->item('table_pos_setup_user_outlet').' user_outlet');
        $this->db->select('user_outlet.customer_id id, outlet_info.name');
        $this->db->join($this->config->item('table_login_csetup_customer').' outlet','outlet.id=user_outlet.customer_id AND outlet.status="'.$this->config->item('system_status_active').'"','INNER');
        $this->db->join($this->config->item('table_login_csetup_cus_info').' outlet_info','outlet_info.customer_id=outlet.id AND outlet_info.type="'.$this->config->item('system_customer_type_outlet_id').'"','INNER');
        $this->db->select('outlet_discount.discount_percentage, outlet_discount.expire_day, outlet_discount.expire_time, outlet_discount.farmer_type_id');
        $this->db->join($this->config->item('table_pos_setup_farmer_type_outlet_discount').' outlet_discount','outlet_discount.outlet_id=user_outlet.customer_id AND outlet_discount.farmer_type_id='.$farmer_type_id,'LEFT');
        $this->db->where('user_outlet.revision',1);
        $this->db->where('outlet_info.revision',1);
        $this->db->where('user_outlet.user_id',$user->user_id);
        $this->db->order_by('user_outlet.customer_id','ASC');
        $items=$this->db->get()->result_array();
        foreach($items as &$item)
        {
            if($item['expire_time']>$time)
            {
                $item['expire_day']=ceil(($item['expire_time']-$time)/(3600*24));
            }
            else
            {
                $item['expire_day']=0;
                $item['discount_percentage']=0;
            }

        }
        $this->json_return($items);
    }
    private function system_outlet_discount_edit($farmer_type_id,$id)
    {
        if(isset($this->permissions['action7']) && ($this->permissions['action7']==1))
        {
            if($id>0)
            {
                $outlet_id=$id;
            }
            else
            {
                $outlet_id=$this->input->post('id');
            }
            $user = User_helper::get_user();
            $time=time();
            $valid_farmer_type=Query_helper::get_info($this->config->item('table_pos_setup_farmer_type'),'*',array('id ='.$farmer_type_id),1);
            if(!$valid_farmer_type)
            {
                $ajax['status']=false;
                $ajax['system_message']='Invalid Farmer Type Try.';
                $this->json_return($ajax);
            }

            $this->db->from($this->config->item('table_pos_setup_user_outlet').' user_outlet');
            $this->db->select('outlet_info.name outlet_name');
            $this->db->join($this->config->item('table_login_csetup_cus_info').' outlet_info','outlet_info.customer_id=user_outlet.customer_id','INNER');
            $this->db->where('user_outlet.revision',1);
            $this->db->where('outlet_info.revision',1);
            $this->db->where('user_outlet.user_id',$user->user_id);
            $this->db->where('user_outlet.customer_id',$outlet_id);
            $valid_outlet=$this->db->get()->row_array();
            if(!$valid_outlet)
            {
                System_helper::invalid_try('Edit Outlet Discount Non Assigned',$outlet_id);
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
                $this->json_return($ajax);
            }

            $this->db->from($this->config->item('table_pos_setup_farmer_type_outlet_discount').' outlet_discount');
            $this->db->select('outlet_discount.*');
            $this->db->where('outlet_discount.outlet_id',$outlet_id);
            $this->db->where('outlet_discount.farmer_type_id',$farmer_type_id);
            $data['item']=$this->db->get()->row_array();

            if(!$data['item'])
            {
                $data['item']['outlet_id']=$outlet_id;
                $data['item']['farmer_type_id']=$farmer_type_id;
                $data['item']['discount_percentage']=0;
                $data['item']['expire_day']=0;
            }
            else
            {
                if($data['item']['expire_time']>$time)
                {
                    $item['expire_day']=ceil(($data['item']['expire_time']-$time)/(3600*24));
                }
                else
                {
                    $data['item']['discount_percentage']=0;
                    $data['item']['expire_day']=0;
                }
            }
            $data['item']['outlet_name']=$valid_outlet['outlet_name'];
            $data['title']="Outlet Discount for-".$valid_farmer_type['name'];
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/outlet_discount_edit",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/outlet_discount_edit/'.$farmer_type_id.'/'.$outlet_id);
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function system_save_outlet_discount()
    {
        $item=$this->input->post('item');
        $user = User_helper::get_user();
        $this->db->from($this->config->item('table_pos_setup_user_outlet').' user_outlet');
        $this->db->where('user_outlet.revision',1);
        $this->db->where('user_outlet.user_id',$user->user_id);
        $this->db->where('user_outlet.customer_id',$item['outlet_id']);
        $valid_outlet=$this->db->get()->result_array();
        if(!$valid_outlet)
        {
            System_helper::invalid_try('Save Outlet Discount Non Assigned',$item['outlet_id']);
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
        $old_item=Query_helper::get_info($this->config->item('table_pos_setup_farmer_type_outlet_discount'),array('*'),array('outlet_id ='.$item['outlet_id'],'farmer_type_id ='.$item['farmer_type_id']),1);
        $time=time();

        /*--Start-- Permission Checking */
        if(!(isset($this->permissions['action7']) && ($this->permissions['action7']==1)))
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
        if(!$this->check_validation_discount())
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->message;
            $this->json_return($ajax);
        }
        else
        {
            $this->db->trans_start();  //DB Transaction Handle START

            $data_history=array();
            $data_history['date_updated'] = $time;
            $data_history['user_updated'] = $user->user_id;
            Query_helper::update($this->config->item('table_pos_setup_farmer_type_outlet_discount_histories'),$data_history,array('outlet_id='.$item['outlet_id'],'farmer_type_id='.$item['farmer_type_id'],'revision=1'));

            $this->db->where('outlet_id',$item['outlet_id']);
            $this->db->where('farmer_type_id',$item['farmer_type_id']);
            $this->db->set('revision', 'revision+1', FALSE);
            $this->db->update($this->config->item('table_pos_setup_farmer_type_outlet_discount_histories'));
            if($old_item)
            {
                $data=array();
                $data['discount_percentage']=$item['discount_percentage'];
                $data['expire_day']=$item['expire_day'];
                $data['expire_time']=$time+$item['expire_day']*3600*24;
                $data['user_updated']=$user->user_id;
                $data['date_updated']=$time;
                $this->db->set('revision_count', 'revision_count+1', FALSE);
                Query_helper::update($this->config->item('table_pos_setup_farmer_type_outlet_discount'),$data,array('outlet_id='.$old_item['outlet_id'],'farmer_type_id='.$old_item['farmer_type_id']),false);
            }
            $data=array();
            $data['outlet_id']=$item['outlet_id'];
            $data['farmer_type_id']=$item['farmer_type_id'];
            $data['discount_percentage']=$item['discount_percentage'];
            $data['expire_day']=$item['expire_day'];
            $data['expire_time']=$time+$item['expire_day']*3600*24;
            $data['revision']=1;
            $data['user_created']=$user->user_id;
            $data['date_created']=$time;
            Query_helper::add($this->config->item('table_pos_setup_farmer_type_outlet_discount_histories'),$data,false);
            if(!($old_item))
            {
                unset($data['revision']);
                $data['revision_count']=1;
                Query_helper::add($this->config->item('table_pos_setup_farmer_type_outlet_discount'),$data,false);
            }

        }
        $this->db->trans_complete();   //DB Transaction Handle END
        if ($this->db->trans_status() === TRUE)
        {
            $this->message=$this->lang->line("MSG_SAVED_SUCCESS");
            $this->system_outlet_discount_list($item['farmer_type_id']);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("MSG_SAVED_FAIL");
            $this->json_return($ajax);
        }
    }
    private function check_validation_discount()
    {
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
        $data['id']= 1;
        $data['name']= 1;
        $data['discount_self_percentage']= 1;
        $data['discount_referral_percentage']= 1;
        $data['commission_distributor']= 1;
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
