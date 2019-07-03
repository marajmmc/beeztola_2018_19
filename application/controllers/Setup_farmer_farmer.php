<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Setup_farmer_farmer extends Root_Controller
{
    public $message;
    public $permissions;
    public $controller_url;
    public function __construct()
    {
        parent::__construct();
        $this->message="";
        $this->permissions=User_helper::get_permission('Setup_farmer_farmer');
        $this->controller_url='setup_farmer_farmer';
    }

    public function index($action="list",$id=0)
    {
        if($action=="list")
        {
            $this->system_list($id);
        }
        elseif($action=='get_items')
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
        elseif($action=="edit_outlet")
        {
            $this->system_edit_outlet($id);
        }
        elseif($action=="save_outlet")
        {
            $this->system_save_outlet();
        }
        elseif($action=="edit_credit_limit")
        {
            $this->system_edit_credit_limit($id);
        }
        elseif($action=="save_credit_limit")
        {
            $this->system_save_credit_limit();
        }
        elseif($action=="details")
        {
            $this->system_details($id);
        }


        elseif($action=="set_preference")
        {
            $this->system_set_preference();
        }
        elseif($action=="save_preference")
        {
            System_helper::save_preference();
        }
        else
        {
            $this->system_list($id);
        }
    }

    private function system_list()
    {
        if(isset($this->permissions['action0']) && ($this->permissions['action0']==1))
        {
            $data['system_preference_items']= $this->get_preference();
            $data['title']="List of Farmers/Customers";
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

        $this->db->from($this->config->item('table_pos_setup_farmer_farmer').' f');
        $this->db->select('f.*');
        $this->db->join($this->config->item('table_pos_setup_farmer_type').' ft','ft.id = f.farmer_type_id','INNER');
        $this->db->select('ft.name farmer_type_name');

        $this->db->join($this->config->item('table_login_setup_location_unions').' union','union.id = f.union_id','LEFT');
        $this->db->select('union.name union_name');
        $this->db->join($this->config->item('table_login_setup_location_upazillas').' u','u.id = union.upazilla_id','LEFT');
        $this->db->select('u.name upazilla_name');
        $this->db->join($this->config->item('table_login_setup_location_districts').' d','d.id = u.district_id','LEFT');
        $this->db->select('d.name district_name');
        $this->db->join($this->config->item('table_login_setup_location_territories').' t','t.id = d.territory_id','LEFT');
        $this->db->select('t.name territory_name');
        $this->db->join($this->config->item('table_login_setup_location_zones').' zone','zone.id = t.zone_id','LEFT');
        $this->db->select('zone.name zone_name');
        $this->db->join($this->config->item('table_login_setup_location_divisions').' division','division.id = zone.division_id','LEFT');
        $this->db->select('division.name division_name');


        /*$this->db->join($this->config->item('table_pos_setup_farmer_outlet').' fo','fo.farmer_id = f.id and fo.revision =1','LEFT');
        $this->db->select('count(outlet_id) total_outlet',true);

        $this->db->join($this->config->item('table_login_csetup_cus_info').' outlet_info','outlet_info.customer_id=fo.outlet_id and outlet_info.revision =1','LEFT');
        $this->db->select('outlet_info.name outlet_name');*/

        $this->db->order_by('f.id DESC');
        $this->db->group_by('f.id');
        $this->db->limit($pagesize,$current_records);
        $items=$this->db->get()->result_array();
        $time=time();
        foreach($items as &$item)
        {
            $item['barcode']=Barcode_helper::get_barcode_farmer($item['id']);
            $item['date_created_time']=System_helper::display_date_time($item['date_created']);
            if($item['time_card_off_end']>$time)
            {
                //echo 'here '.$item['id'];
                $item['status_card_require']=$this->config->item('system_status_no');
            }
            $item['amount_credit_due']=$item['amount_credit_limit']-$item['amount_credit_balance'];
        }
        $this->json_return($items);
    }

    private function system_add()
    {
        if(isset($this->permissions['action1']) && ($this->permissions['action1']==1))
        {
            $data['title']="Create Farmer/customer";
            $data["item"] = Array(
                'id' => 0,
                'name' => '',
                'farmer_type_id' => '',
                'status_card_require' => $this->config->item('system_status_no'),
                'mobile_no' => '',
                'nid' => '',
                'address' => '',
                'district_id' => 0,
                'upazilla_id' => 0,
                'union_id' => 0,
                'status' => $this->config->item('system_status_active'),
                'ordering' => 999
            );
            $data['districts']=Query_helper::get_info($this->config->item('table_login_setup_location_districts'),array('id value','name text'),array());
            $data['upazillas']=array();
            $data['unions']=array();
            $data['farmer_types']=Query_helper::get_info($this->config->item('table_pos_setup_farmer_type'),array('id value,name text'),array('status ="'.$this->config->item('system_status_active').'"'),0,0,array('ordering ASC'));
            $ajax['system_page_url']=site_url($this->controller_url."/index/add");
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/add_edit",$data,true));
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
        if(isset($this->permissions['action2']) && ($this->permissions['action2']==1))
        {
            if($id>0)
            {
                $item_id=$id;
            }
            else
            {
                $item_id=$this->input->post('id');
            }
            $this->db->from($this->config->item('table_pos_setup_farmer_farmer').' f');
            $this->db->select('f.*');
            $this->db->join($this->config->item('table_login_setup_location_unions').' union','union.id = f.union_id','LEFT');
            $this->db->select('union.id union_id');
            $this->db->join($this->config->item('table_login_setup_location_upazillas').' u','u.id = union.upazilla_id','LEFT');
            $this->db->select('u.id upazilla_id');
            $this->db->join($this->config->item('table_login_setup_location_districts').' d','d.id = u.district_id','LEFT');
            $this->db->select('d.id district_id');
            $this->db->where('f.id',$item_id);
            $data['item']=$this->db->get()->row_array();

            if(!$data['item'])
            {
                System_helper::invalid_try('Edit Non Exists',$item_id);
                $ajax['status']=false;
                $ajax['system_message']='Invalid Farmer Selection.';
                $this->json_return($ajax);
            }
            $data['districts']=Query_helper::get_info($this->config->item('table_login_setup_location_districts'),array('id value','name text'),array());
            $data['upazillas']=array();
            $data['unions']=array();
            if($data['item']['district_id']>0)
            {
                $data['upazillas']=Query_helper::get_info($this->config->item('table_login_setup_location_upazillas'),array('id value','name text'),array('district_id ='.$data['item']['district_id']));
                if($data['item']['upazilla_id']>0)
                {
                    $data['unions']=Query_helper::get_info($this->config->item('table_login_setup_location_unions'),array('id value','name text'),array('upazilla_id ='.$data['item']['upazilla_id']));
                }

            }
            $data['title']="Edit Farmer (".$data['item']['name'].')';
            $data['farmer_types']=Query_helper::get_info($this->config->item('table_pos_setup_farmer_type'),array('id value,name text'),array('status ="'.$this->config->item('system_status_active').'"'),0,0,array('ordering ASC'));
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
        $item=$this->input->post('item');
        $time=time();
        if($id>0)
        {
            if(!(isset($this->permissions['action2']) && ($this->permissions['action2']==1)))
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
                $this->json_return($ajax);
                die();
            }
            $result=Query_helper::get_info($this->config->item('table_pos_setup_farmer_farmer'),'*',array('id ='.$id, 'status != "'.$this->config->item('system_status_delete').'"'),1);
            if(!$result)
            {
                System_helper::invalid_try('Update Non Exists',$id);
                $ajax['status']=false;
                $ajax['system_message']='Invalid Farmer.';
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
                die();

            }
        }
        if(!$this->check_validation())
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->message;
            $this->json_return($ajax);
        }
        else
        {
            $duration_card_off=$this->input->post('duration_card_off');
            if($duration_card_off>0)
            {
                $item['time_card_off_end']=$time+$duration_card_off*60*60;//hour
            }
            else
            {
                $item['time_card_off_end']=0;
            }
            $this->db->trans_start();  //DB Transaction Handle START
            if($id>0)
            {
                $item['user_updated'] = $user->user_id;
                $item['date_updated'] = $time;

                Query_helper::update($this->config->item('table_pos_setup_farmer_farmer'),$item,array("id = ".$id));

            }
            else
            {

                $item['user_created'] = $user->user_id;
                $item['date_created'] = $time;
                Query_helper::add($this->config->item('table_pos_setup_farmer_farmer'),$item);
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
    }
    private function check_validation()
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('item[name]',$this->lang->line('LABEL_NAME'),'required');
        $this->form_validation->set_rules('item[farmer_type_id]',$this->lang->line('LABEL_FARMER_TYPE_NAME'),'required');
        $this->form_validation->set_rules('item[status_card_require]',$this->lang->line('LABEL_STATUS_CARD_REQUIRE'),'required');
        $this->form_validation->set_rules('item[mobile_no]',$this->lang->line('LABEL_MOBILE_NO'),'required');

        if($this->form_validation->run() == FALSE)
        {
            $this->message=validation_errors();
            return false;
        }
        $item=$this->input->post('item');
        $id = $this->input->post("id");
        $exists=Query_helper::get_info($this->config->item('table_pos_setup_farmer_farmer'),array('id'),array('mobile_no ="'.$item['mobile_no'].'"','id !='.$id),1);
        if($exists)
        {
            $this->message="Mobile No already Exists";
            return false;
        }
        $duration_card_off=$this->input->post('duration_card_off');
        if($duration_card_off>0 && $item['status_card_require']==$this->config->item('system_status_no'))
        {
            $this->message="If you allow without card you must Set Card Required?= Yes";
            return false;
        }
        return true;
    }

    private function system_edit_outlet($id)
    {
        if(isset($this->permissions['action2']) && ($this->permissions['action2']==1))
        {
            if($id>0)
            {
                $item_id=$id;
            }
            else
            {
                $item_id=$this->input->post('id');
            }
            $data['item']=Query_helper::get_info($this->config->item('table_pos_setup_farmer_farmer'),'*',array('id ='.$item_id),1);
            if(!$data['item'])
            {
                System_helper::invalid_try('edit outlet Non Exists',$item_id);
                $ajax['status']=false;
                $ajax['system_message']='Invalid Farmer.';
                $this->json_return($ajax);
            }
            $data['title']="Assign Outlet For(".$data['item']['name'].')';
            $ajax['status']=true;

            $this->db->from($this->config->item('table_login_csetup_customer').' outlet');
            $this->db->join($this->config->item('table_login_csetup_cus_info').' outlet_info','outlet_info.customer_id = outlet.id','INNER');
            $this->db->select('outlet.id value');
            $this->db->select('CONCAT(customer_code," - ",name) text');
            $this->db->where('outlet.status',$this->config->item('system_status_active'));
            $this->db->where('outlet_info.type',$this->config->item('system_customer_type_outlet_id'));
            $this->db->where('outlet_info.revision',1);
            $this->db->order_by('outlet.id','ASC');
            $data['outlets']=$this->db->get()->result_array();

            //$data['outlets']=Query_helper::get_info($this->config->item('table_ems_csetup_customers'),array('id value','CONCAT(customer_code," - ",name) text'),array('status ="'.$this->config->item('system_status_active').'"','type ="Outlet"'));
            $results=Query_helper::get_info($this->config->item('table_pos_setup_farmer_outlet'),array('outlet_id'),array('farmer_id ='.$item_id,'revision =1'));
            $data['assigned_outlets']=array();
            foreach($results as $result)
            {
                $data['assigned_outlets'][]=$result['outlet_id'];
            }
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/edit_outlet",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/edit_outlet/'.$item_id);
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function system_save_outlet()
    {
        $id = $this->input->post("id");
        $user = User_helper::get_user();
        $time=time();
        if(!(isset($this->permissions['action2']) && ($this->permissions['action2']==1)))
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);

            $result=Query_helper::get_info($this->config->item('table_pos_setup_farmer_farmer'),'*',array('id ='.$id, 'status != "'.$this->config->item('system_status_delete').'"'),1);
            if(!$result)
            {
                System_helper::invalid_try('Svae Outlet Non Exists',$id);
                $ajax['status']=false;
                $ajax['system_message']='Invalid Farmer.';
                $this->json_return($ajax);
            }
        }
        {

            $this->db->trans_start();  //DB Transaction Handle START

            $this->db->where('farmer_id',$id);
            $this->db->where('revision',1);
            $this->db->set('user_updated',$user->user_id);
            $this->db->set('date_updated',$time);
            $this->db->update($this->config->item('table_pos_setup_farmer_outlet'));

            $this->db->where('farmer_id',$id);
            $this->db->set('revision', 'revision+1', FALSE);
            $this->db->update($this->config->item('table_pos_setup_farmer_outlet'));

            $items=$this->input->post('items');
            if(is_array($items))
            {
                foreach($items as $outlet_id)
                {
                    $data=array();
                    $data['farmer_id']=$id;
                    $data['outlet_id']=$outlet_id;
                    $data['user_created'] = $user->user_id;
                    $data['date_created'] = $time;
                    $data['revision'] = 1;
                    Query_helper::add($this->config->item('table_pos_setup_farmer_outlet'),$data,false);
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
    }
    private function system_edit_credit_limit($id)
    {
        if(isset($this->permissions['action2']) && ($this->permissions['action2']==1))
        {
            if($id>0)
            {
                $item_id=$id;
            }
            else
            {
                $item_id=$this->input->post('id');
            }
            $this->db->from($this->config->item('table_pos_setup_farmer_farmer').' f');
            $this->db->select('f.*');
            $this->db->select('ft.name farmer_type_name,ft.discount_self_percentage');
            $this->db->join($this->config->item('table_pos_setup_farmer_type').' ft','ft.id = f.farmer_type_id','INNER');
            $this->db->join($this->config->item('table_login_setup_location_unions').' union','union.id = f.union_id','LEFT');
            $this->db->select('union.name union_name');
            $this->db->join($this->config->item('table_login_setup_location_upazillas').' u','u.id = union.upazilla_id','LEFT');
            $this->db->select('u.name upazilla_name');
            $this->db->join($this->config->item('table_login_setup_location_districts').' d','d.id = u.district_id','LEFT');
            $this->db->select('d.name district_name');
            $this->db->join($this->config->item('table_login_setup_location_territories').' t','t.id = d.territory_id','LEFT');
            $this->db->select('t.name territory_name');
            $this->db->join($this->config->item('table_login_setup_location_zones').' zone','zone.id = t.zone_id','LEFT');
            $this->db->select('zone.name zone_name');
            $this->db->join($this->config->item('table_login_setup_location_divisions').' division','division.id = zone.division_id','LEFT');
            $this->db->select('division.name division_name');
            $this->db->where('f.id',$item_id);
            $data['item']=$this->db->get()->row_array();
            if(!$data['item'])
            {
                System_helper::invalid_try(__FUNCTION__,$item_id,'Farmer not Exists');
                $ajax['status']=false;
                $ajax['system_message']='Invalid Farmer.';
                $this->json_return($ajax);
            }
            if(!($data['item']['farmer_type_id']>1))
            {
                $ajax['status']=false;
                $ajax['system_message']='Make the customer Dealer first.';
                $this->json_return($ajax);
                die();
            }

            $this->db->from($this->config->item('table_pos_setup_farmer_outlet').' farmer_outlet');
            $this->db->join($this->config->item('table_login_csetup_cus_info').' outlet_info','outlet_info.customer_id = farmer_outlet.outlet_id','INNER');
            $this->db->select('CONCAT(customer_code," - ",name) text');
            $this->db->where('farmer_outlet.revision',1);
            $this->db->where('farmer_outlet.farmer_id',$item_id);
            $this->db->where('outlet_info.revision',1);
            $data['assigned_outlets']=$this->db->get()->result_array();

            $data['title']="Edit Farmer Credit Limit (".$data['item']['name'].')';
            $data['farmer_types']=Query_helper::get_info($this->config->item('table_pos_setup_farmer_type'),array('id value,name text'),array('status ="'.$this->config->item('system_status_active').'"'),0,0,array('ordering ASC'));
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/edit_credit_limit",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/edit_credit_limit/'.$item_id);
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function system_save_credit_limit()
    {
        $id = $this->input->post("id");//farmer_id
        $user = User_helper::get_user();
        $item=$this->input->post('item');
        $time=time();
        if(!(isset($this->permissions['action2']) && ($this->permissions['action2']==1)))
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
            die();
        }
        $result=Query_helper::get_info($this->config->item('table_pos_setup_farmer_farmer'),'*',array('id ='.$id, 'status != "'.$this->config->item('system_status_delete').'"'),1);
        if(!$result)
        {
            System_helper::invalid_try(__FUNCTION__,$id,'Update Credit Non Exists');
            $ajax['status']=false;
            $ajax['system_message']='Invalid Farmer.';
            $this->json_return($ajax);
        }
        if(!($result['farmer_type_id']>1))
        {
            $ajax['status']=false;
            $ajax['system_message']='Make the customer Dealer first.';
            $this->json_return($ajax);
            die();
        }

        if((trim($item['amount_credit_limit'])=='')|| (!($item['amount_credit_limit']>=0)))
        {
            $ajax['status']=false;
            $ajax['system_message']='New Credit Limit field is required.';
            $this->json_return($ajax);
        }
        $this->load->helper('farmer_credit');
        $data_history=array();
        $data_history['farmer_id']=$id;
        //$data_history['sale_id']=0;
        //$data_history['payment_id']=0;
        $data_history['credit_limit_old']=$result['amount_credit_limit'];
        $data_history['credit_limit_new']=$item['amount_credit_limit'];

        //$credit_limit_old=$result['amount_credit_limit'];
        //$credit_limit_new=$item['amount_credit_limit'];
        $credit_difference=($data_history['credit_limit_new']-$data_history['credit_limit_old']);
        $data_history['balance_old']=$result['amount_credit_balance'];
        $data_history['balance_new']=$result['amount_credit_balance']+$credit_difference;

        if($data_history['balance_new']<0)
        {
            $ajax['status']=false;
            $ajax['system_message']='New Balance will be negative.';
            $this->json_return($ajax);
        }
        $data_history['amount_adjust']=$item['amount_credit_limit'];
        $data_history['remarks_reason']="limit Changed.";
        //$data_history['reference_no'];
        $data_history['remarks']=$item['remarks_credit_limit'];

        $item['amount_credit_limit'] = $data_history['credit_limit_new'];
        $item['amount_credit_balance'] = $data_history['balance_new'];

        $this->db->trans_start();  //DB Transaction Handle START
        Query_helper::update($this->config->item('table_pos_setup_farmer_farmer'),$item,array("id = ".$id));
        $remarks_reason='Update Set Credit.';
        Farmer_Credit_helper::add_credit_history($data_history);
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

            $this->db->from($this->config->item('table_pos_setup_farmer_farmer').' f');
            $this->db->select('f.*');
            $this->db->select('ft.name farmer_type_name,ft.discount_self_percentage');
            $this->db->join($this->config->item('table_pos_setup_farmer_type').' ft','ft.id = f.farmer_type_id','INNER');
            $this->db->join($this->config->item('table_login_setup_location_unions').' union','union.id = f.union_id','LEFT');
            $this->db->select('union.name union_name');
            $this->db->join($this->config->item('table_login_setup_location_upazillas').' u','u.id = union.upazilla_id','LEFT');
            $this->db->select('u.name upazilla_name');
            $this->db->join($this->config->item('table_login_setup_location_districts').' d','d.id = u.district_id','LEFT');
            $this->db->select('d.name district_name');
            $this->db->join($this->config->item('table_login_setup_location_territories').' t','t.id = d.territory_id','LEFT');
            $this->db->select('t.name territory_name');
            $this->db->join($this->config->item('table_login_setup_location_zones').' zone','zone.id = t.zone_id','LEFT');
            $this->db->select('zone.name zone_name');
            $this->db->join($this->config->item('table_login_setup_location_divisions').' division','division.id = zone.division_id','LEFT');
            $this->db->select('division.name division_name');
            $this->db->where('f.id',$item_id);
            $data['item']=$this->db->get()->row_array();
            if(!$data['item'])
            {
                System_helper::invalid_try('Details Non Exists',$item_id);
                $ajax['status']=false;
                $ajax['system_message']='Invalid Farmer.';
                $this->json_return($ajax);
            }
            $data['title']="Details of Framer (".$data['item']['name'].')';

            $this->db->from($this->config->item('table_pos_setup_farmer_outlet').' farmer_outlet');

            $this->db->join($this->config->item('table_login_csetup_cus_info').' outlet_info','outlet_info.customer_id = farmer_outlet.outlet_id','INNER');
            $this->db->select('CONCAT(customer_code," - ",name) text');
            $this->db->where('farmer_outlet.revision',1);
            $this->db->where('farmer_outlet.farmer_id',$item_id);
            $this->db->where('outlet_info.revision',1);
            $data['assigned_outlets']=$this->db->get()->result_array();

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
        $data['name']= 1;
        $data['amount_credit_limit']= 1;
        $data['amount_credit_balance']= 1;
        $data['amount_credit_due']= 1;
        $data['date_created_time']= 1;
        $data['farmer_type_name']= 1;
        $data['status_card_require']= 1;
        //$data['outlet_name']= 1;
        //$data['total_outlet']= 1;
        $data['mobile_no']= 1;
        $data['nid']= 1;
        $data['address']= 1;
        $data['division_name']= 1;
        $data['zone_name']= 1;
        $data['territory_name']= 1;
        $data['district_name']= 1;
        $data['upazilla_name']= 1;
        $data['union_name']= 1;
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
