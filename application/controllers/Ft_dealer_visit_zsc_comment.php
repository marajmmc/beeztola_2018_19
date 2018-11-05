<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Ft_dealer_visit_zsc_comment extends Root_Controller
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
        elseif($action=="edit_admin")
        {
            $this->system_edit_admin($id);
        }
        elseif($action=="save_admin")
        {
            $this->system_save_admin();
        }
        elseif($action=="details")
        {
            $this->system_details($id);
        }
        elseif($action=="set_preference")
        {
            $this->system_set_preference('list');
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
    private function get_preference_headers($method)
    {
        $data['id']= 1;
        $data['date']= 1;
        $data['outlet']= 1;
        $data['created_by']= 1;
        $data['dealer']= 1;
        $data['remarks']= 1;
        if($method=='list_all')
        {
            $data['status_zsc_comment']= 1;
        }
        return $data;
    }
    private function system_set_preference($method='list')
    {
        $user = User_helper::get_user();
        if(isset($this->permissions['action6']) && ($this->permissions['action6']==1))
        {
            $data['system_preference_items']=System_helper::get_preference($user->user_id,$this->controller_url,$method,$this->get_preference_headers($method));
            $data['preference_method_name']=$method;
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
    private function system_list()
    {
        if(isset($this->permissions['action0'])&&($this->permissions['action0']==1))
        {
            $user = User_helper::get_user();
            $method='list';
            $data['system_preference_items']= System_helper::get_preference($user->user_id,$this->controller_url,$method,$this->get_preference_headers($method));
            $data['title']="Field Visit (ZSC Comment) List";
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
        $this->db->from($this->config->item('table_pos_ft_dealer_visit').' item');
        $this->db->select('item.*');

        $this->db->join($this->config->item('table_login_csetup_cus_info').' outlet_info','outlet_info.customer_id=item.outlet_id AND outlet_info.type="'.$this->config->item('system_customer_type_outlet_id').'" AND outlet_info.revision = 1','INNER');
        $this->db->select('outlet_info.name outlet, outlet_info.customer_code outlet_code');

        $this->db->join($this->config->item('table_pos_setup_user_info').' pos_setup_user_info','pos_setup_user_info.user_id=item.user_created  AND pos_setup_user_info.revision = 1','LEFT');
        $this->db->select('pos_setup_user_info.name created_by');

        $this->db->join($this->config->item('table_pos_setup_farmer_outlet').' farmer_outlet','farmer_outlet.farmer_id=item.dealer_id AND farmer_outlet.revision=1','INNER');
        $this->db->join($this->config->item('table_pos_setup_farmer_farmer').' farmer_farmer','farmer_farmer.id=farmer_outlet.farmer_id','INNER');
        $this->db->select('farmer_farmer.name dealer');

        $this->db->where('item.zsc_comment',null);
        $this->db->where('item.status !=',$this->config->item('system_status_delete'));
        $this->db->where_in('item.outlet_id',$this->user_outlet_ids);
        $this->db->order_by('item.id','DESC');
        $items=$this->db->get()->result_array();
        foreach($items as &$item)
        {
            $item['date']=System_helper::display_date($item['date']);
        }
        $this->json_return($items);
    }
    private function system_list_all()
    {
        if(isset($this->permissions['action0'])&&($this->permissions['action0']==1))
        {
            $user = User_helper::get_user();
            $method='list_all';
            $data['system_preference_items']= System_helper::get_preference($user->user_id,$this->controller_url,$method,$this->get_preference_headers($method));
            $data['title']="Field Visit (ZSC Comment) All List";
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/list_all",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/list_all');
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
        $this->db->from($this->config->item('table_pos_ft_dealer_visit').' item');
        $this->db->select('item.*');
        $this->db->select("IF(item.zsc_comment is null, 'NO', 'YES') status_zsc_comment");

        $this->db->join($this->config->item('table_login_csetup_cus_info').' outlet_info','outlet_info.customer_id=item.outlet_id AND outlet_info.type="'.$this->config->item('system_customer_type_outlet_id').'" AND outlet_info.revision = 1','INNER');
        $this->db->select('outlet_info.name outlet, outlet_info.customer_code outlet_code');

        $this->db->join($this->config->item('table_pos_setup_user_info').' pos_setup_user_info','pos_setup_user_info.user_id=item.user_created AND pos_setup_user_info.revision = 1','LEFT');
        $this->db->select('pos_setup_user_info.name created_by');

        $this->db->join($this->config->item('table_pos_setup_farmer_outlet').' farmer_outlet','farmer_outlet.farmer_id=item.dealer_id AND farmer_outlet.revision=1','INNER');
        $this->db->join($this->config->item('table_pos_setup_farmer_farmer').' farmer_farmer','farmer_farmer.id=farmer_outlet.farmer_id','INNER');
        $this->db->select('farmer_farmer.name dealer');

        $this->db->where('item.status !=',$this->config->item('system_status_delete'));
        $this->db->where_in('item.outlet_id',$this->user_outlet_ids);
        $this->db->order_by('item.id','DESC');
        $items=$this->db->get()->result_array();
        foreach($items as &$item)
        {
            $item['date']=System_helper::display_date($item['date']);
        }
        $this->json_return($items);
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

            //$data['item']=Query_helper::get_info($this->config->item('table_pos_ft_dealer_visit'),array('*'),array('id ='.$item_id,'status !="'.$this->config->item('system_status_delete').'"'),1,0,array('id ASC'));
            $this->db->from($this->config->item('table_pos_ft_dealer_visit').' item');
            $this->db->select('item.*');

            $this->db->join($this->config->item('table_login_csetup_cus_info').' outlet_info','outlet_info.customer_id=item.outlet_id AND outlet_info.type="'.$this->config->item('system_customer_type_outlet_id').'"','INNER');
            $this->db->select('outlet_info.name outlet, outlet_info.customer_code outlet_code');

            $this->db->join($this->config->item('table_pos_setup_farmer_outlet').' farmer_outlet','farmer_outlet.farmer_id=item.dealer_id AND farmer_outlet.revision=1','INNER');
            $this->db->join($this->config->item('table_pos_setup_farmer_farmer').' farmer_farmer','farmer_farmer.id=farmer_outlet.farmer_id AND farmer_farmer.farmer_type_id > 1 AND farmer_farmer.status="'.$this->config->item('system_status_active').'"','INNER');
            $this->db->select('farmer_farmer.name dealer_name');

            $this->db->where('item.status !=',$this->config->item('system_status_delete'));
            $this->db->where('item.id',$item_id);
            $this->db->order_by('item.id','ASC');
            $data['item']=$this->db->get()->row_array();
            if(!$data['item'])
            {
                System_helper::invalid_try('Edit',$item_id,'Edit Non Exists');
                $ajax['status']=false;
                $ajax['system_message']='Invalid Item.';
                $this->json_return($ajax);
            }
            if($data['item']['zsc_comment'])
            {
                $ajax['status']=false;
                $ajax['system_message']="Already zsc commented this visit so you can't edit it.";
                $this->json_return($ajax);
            }

            $outlet_id=$data['item']['outlet_id'];
            $dealer_id=$data['item']['dealer_id'];
            $date=$data['item']['date'];
            $data['item_previous']=Query_helper::get_info($this->config->item('table_pos_ft_dealer_visit'),array('*'),array('outlet_id='.$outlet_id,'dealer_id='.$dealer_id,'date < '.$date),1,0,array('id DESC'));

            $data['heads']=Query_helper::get_info($this->config->item('table_pos_ft_dealer_visit_setup_heads'),array('*'),array('status !="'.$this->config->item('system_status_delete').'"'),0,0,array('ordering ASC'));
            if(!$data['heads'])
            {
                $ajax['status']=false;
                $ajax['system_message']='Field visit head is empty';
                $this->json_return($ajax);
            }

            $data['outlets']=Query_helper::get_info($this->config->item('table_login_csetup_cus_info'),array('customer_id','name'),array('customer_id='.$outlet_id, 'revision = 1'));
            $data['dealers']=Query_helper::get_info($this->config->item('table_pos_setup_farmer_farmer'),array('id value','name text'),array('id='.$dealer_id));


            $data['title']="Field Visit (ZSC Comment)";
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
        $id = $this->input->post("id");
        $user = User_helper::get_user();
        $time=time();
        $item=$this->input->post('item');

        if(!(isset($this->permissions['action2']) && ($this->permissions['action2']==1)))
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
        if(!($id>0))
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
        $result=Query_helper::get_info($this->config->item('table_pos_ft_dealer_visit'),'*',array('id ='.$id, 'status != "'.$this->config->item('system_status_delete').'"'),1);
        if(!$result)
        {
            System_helper::invalid_try('Update',$id,'Update Non Exists');
            $ajax['status']=false;
            $ajax['system_message']='Invalid Item.';
            $this->json_return($ajax);
        }
        if($result['zsc_comment'])
        {
            $ajax['status']=false;
            $ajax['system_message']="Already zsc commented this visit so you can't edit it.";
            $this->json_return($ajax);
        }

        if(!$this->check_validation())
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->message;
            $this->json_return($ajax);
        }

        $this->db->trans_start();  //DB Transaction Handle START

        $item['date_update_zsc_comment']=$time;
        $item['user_update_zsc_comment']=$user->user_id;
        Query_helper::update($this->config->item('table_pos_ft_dealer_visit'),$item,array('id='.$id));

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

    private function system_edit_admin($id)
    {
        if(isset($this->permissions['action7'])&&($this->permissions['action7']==1))
        {
            if($id>0)
            {
                $item_id=$id;
            }
            else
            {
                $item_id=$this->input->post('id');
            }

            //$data['item']=Query_helper::get_info($this->config->item('table_pos_ft_dealer_visit'),array('*'),array('id ='.$item_id,'status !="'.$this->config->item('system_status_delete').'"'),1,0,array('id ASC'));
            $this->db->from($this->config->item('table_pos_ft_dealer_visit').' item');
            $this->db->select('item.*');

            $this->db->join($this->config->item('table_login_csetup_cus_info').' outlet_info','outlet_info.customer_id=item.outlet_id AND outlet_info.type="'.$this->config->item('system_customer_type_outlet_id').'"','INNER');
            $this->db->select('outlet_info.name outlet, outlet_info.customer_code outlet_code');

            $this->db->join($this->config->item('table_pos_setup_farmer_outlet').' farmer_outlet','farmer_outlet.farmer_id=item.dealer_id AND farmer_outlet.revision=1','INNER');
            $this->db->join($this->config->item('table_pos_setup_farmer_farmer').' farmer_farmer','farmer_farmer.id=farmer_outlet.farmer_id AND farmer_farmer.farmer_type_id > 1 AND farmer_farmer.status="'.$this->config->item('system_status_active').'"','INNER');
            $this->db->select('farmer_farmer.name dealer_name');

            $this->db->where('item.status !=',$this->config->item('system_status_delete'));
            $this->db->where('item.id',$item_id);
            $this->db->order_by('item.id','ASC');
            $data['item']=$this->db->get()->row_array();
            if(!$data['item'])
            {
                System_helper::invalid_try('Edit',$item_id,'Edit Non Exists');
                $ajax['status']=false;
                $ajax['system_message']='Invalid Item.';
                $this->json_return($ajax);
            }

            $outlet_id=$data['item']['outlet_id'];
            $dealer_id=$data['item']['dealer_id'];
            $date=$data['item']['date'];
            $data['item_previous']=Query_helper::get_info($this->config->item('table_pos_ft_dealer_visit'),array('*'),array('outlet_id='.$outlet_id,'dealer_id='.$dealer_id,'date < '.$date),1,0,array('id DESC'));


            $data['heads']=Query_helper::get_info($this->config->item('table_pos_ft_dealer_visit_setup_heads'),array('*'),array('status !="'.$this->config->item('system_status_delete').'"'),0,0,array('ordering ASC'));
            if(!$data['heads'])
            {
                $ajax['status']=false;
                $ajax['system_message']='Field visit head is empty';
                $this->json_return($ajax);
            }

            $data['outlets']=Query_helper::get_info($this->config->item('table_login_csetup_cus_info'),array('customer_id','name'),array('customer_id='.$outlet_id, 'revision = 1'));
            $data['dealers']=Query_helper::get_info($this->config->item('table_pos_setup_farmer_farmer'),array('id value','name text'),array('id='.$dealer_id));


            $data['title']="Field Visit (Admin Edit)";
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/edit_admin",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/edit_admin/'.$item_id);
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function system_save_admin()
    {
        $id = $this->input->post("id");
        $user = User_helper::get_user();
        $time=time();
        $item=$this->input->post('item');
        $heads=$this->input->post('heads');
        $date_visit=System_helper::get_time($item['date']);
        $date_current=System_helper::get_time(System_helper::display_date(time()));

        if(!(isset($this->permissions['action7']) && ($this->permissions['action7']==1)))
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
        if(!($id>0))
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
        $result=Query_helper::get_info($this->config->item('table_pos_ft_dealer_visit'),'*',array('id ='.$id, 'status != "'.$this->config->item('system_status_delete').'"'),1);
        if(!$result)
        {
            System_helper::invalid_try('save_admin',$id,'Update Non Exists');
            $ajax['status']=false;
            $ajax['system_message']='Invalid Item.';
            $this->json_return($ajax);
        }
        if(!$this->check_validation())
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->message;
            $this->json_return($ajax);
        }

        $this->db->trans_start();  //DB Transaction Handle START
        $field_visit_data='';
        if(sizeof($heads)>0)
        {
            $field_visit_data=json_encode($heads);
        }
        $item['field_visit_data']=$field_visit_data;
        if($date_visit)
        {
            $item['date']=$date_visit;
        }
        else
        {
            $item['date']=$date_current;
        }
        if($result['zsc_comment']!=$item['zsc_comment'])
        {
            $item['date_update_zsc_comment']=$time;
            $item['user_update_zsc_comment']=$user->user_id;
        }
        $item['date_updated_admin']=$time;
        $item['user_updated_admin']=$user->user_id;
        $this->db->set('revision_count_admin', 'revision_count_admin+1', FALSE);
        Query_helper::update($this->config->item('table_pos_ft_dealer_visit'),$item,array('id='.$id));

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
    private function system_details($id)
    {
        if(isset($this->permissions['action0'])&&($this->permissions['action0']==1))
        {
            if($id>0)
            {
                $item_id=$id;
            }
            else
            {
                $item_id=$this->input->post('id');
            }

            //$data['item']=Query_helper::get_info($this->config->item('table_pos_ft_dealer_visit'),array('*'),array('id ='.$item_id,'status !="'.$this->config->item('system_status_delete').'"'),1,0,array('id ASC'));
            $this->db->from($this->config->item('table_pos_ft_dealer_visit').' item');
            $this->db->select('item.*');
            $this->db->select("IF(item.zsc_comment is null, 'NO', 'YES') status_zsc_comment");

            $this->db->join($this->config->item('table_login_csetup_cus_info').' outlet_info','outlet_info.customer_id=item.outlet_id AND outlet_info.type="'.$this->config->item('system_customer_type_outlet_id').'"','INNER');
            $this->db->select('outlet_info.name outlet, outlet_info.customer_code outlet_code');

            $this->db->join($this->config->item('table_pos_setup_farmer_outlet').' farmer_outlet','farmer_outlet.farmer_id=item.dealer_id AND farmer_outlet.revision=1','INNER');
            $this->db->join($this->config->item('table_pos_setup_farmer_farmer').' farmer_farmer','farmer_farmer.id=farmer_outlet.farmer_id AND farmer_farmer.farmer_type_id > 1 AND farmer_farmer.status="'.$this->config->item('system_status_active').'"','INNER');
            $this->db->select('farmer_farmer.name dealer_name');

            $this->db->join($this->config->item('table_pos_setup_user_info').' user_info_created','user_info_created.user_id=item.user_created','LEFT');
            $this->db->select('user_info_created.name full_name_created');

            $this->db->join($this->config->item('table_pos_setup_user_info').' user_info_updated','user_info_updated.user_id=item.user_updated','LEFT');
            $this->db->select('user_info_updated.name full_name_updated');

            $this->db->where('item.status !=',$this->config->item('system_status_delete'));
            $this->db->where('item.id',$item_id);
            $this->db->order_by('item.id','ASC');
            $data['item']=$this->db->get()->row_array();
            if(!$data['item'])
            {
                System_helper::invalid_try('Edit',$item_id,'Edit Non Exists');
                $ajax['status']=false;
                $ajax['system_message']='Invalid Item.';
                $this->json_return($ajax);
            }

            $user_ids=array();
            $user_ids[$data['item']['user_update_zsc_comment']]=$data['item']['user_update_zsc_comment'];
            $user_ids[$data['item']['user_updated_admin']]=$data['item']['user_updated_admin'];
            $data['users']=$this->get_sms_users_info($user_ids);

            $outlet_id=$data['item']['outlet_id'];
            $dealer_id=$data['item']['dealer_id'];
            $date=$data['item']['date'];
            $data['item_previous']=Query_helper::get_info($this->config->item('table_pos_ft_dealer_visit'),array('*'),array('outlet_id='.$outlet_id,'dealer_id='.$dealer_id,'date < '.$date),1,0,array('id DESC'));


            $data['heads']=Query_helper::get_info($this->config->item('table_pos_ft_dealer_visit_setup_heads'),array('*'),array('status !="'.$this->config->item('system_status_delete').'"'),0,0,array('ordering ASC'));
            if(!$data['heads'])
            {
                $ajax['status']=false;
                $ajax['system_message']='Field visit head is empty';
                $this->json_return($ajax);
            }

            $data['outlets']=Query_helper::get_info($this->config->item('table_login_csetup_cus_info'),array('customer_id','name'),array('customer_id='.$outlet_id, 'revision = 1'));
            $data['dealers']=Query_helper::get_info($this->config->item('table_pos_setup_farmer_farmer'),array('id value','name text'),array('id='.$dealer_id));


            $data['title']='Field Visit Details';
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
    private function check_validation()
    {
        $this->load->library('form_validation');
        $id=$this->input->post('item');
        $this->form_validation->set_rules('item[zsc_comment]','ZSC Comment','required');
        if($this->form_validation->run() == FALSE)
        {
            $this->message=validation_errors();
            return false;
        }
        return true;
    }
    private function get_sms_users_info($user_ids)
    {
        $this->db->from($this->config->item('table_login_setup_user').' user');
        $this->db->select('user.id,user.employee_id,user.user_name,user.status');
        $this->db->join($this->config->item('table_login_setup_user_info').' user_info','user.id = user_info.user_id','INNER');
        $this->db->select('user_info.name,user_info.ordering,user_info.blood_group,user_info.mobile_no');
        $this->db->where('user_info.revision',1);
        if(sizeof($user_ids)>0)
        {
            $this->db->where_in('user.id',$user_ids);
        }
        $results=$this->db->get()->result_array();
        $users=array();
        foreach($results as $result)
        {
            $users[$result['id']]=$result;
        }
        return $users;
    }
}
