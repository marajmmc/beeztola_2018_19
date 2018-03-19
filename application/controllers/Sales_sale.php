<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Sales_sale extends Root_Controller
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
        $this->permissions=User_helper::get_permission('Sales_sale');
        $this->controller_url='sales_sale';
        $this->user_outlet_ids=array();
        $this->user_outlets=User_helper::get_assigned_outlets();
        if(sizeof($this->user_outlets)>0)
        {
            foreach($this->user_outlets as $row)
            {
                $this->user_outlet_ids[]=$row['id'];
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
        elseif($action=="search_farmer")
        {
            $this->system_search_farmer();
        }
        elseif($action=="save_farmer")
        {
            $this->system_save_farmer();
        }
        /*elseif($action=="edit")
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

        elseif($action=="details")
        {
            $this->system_details($id);
        }*/


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
            if($item['time_card_off_end']>$time)
            {
                //echo 'here '.$item['id'];
                $item['status_card_require']=$this->config->item('system_status_no');
            }
        }
        $this->json_return($items);
    }

    private function system_add()
    {
        if(isset($this->permissions['action1']) && ($this->permissions['action1']==1))
        {
            $data['title']="New Sale";
            $ajax['system_page_url']=site_url($this->controller_url."/index/add");
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/search",$data,true));
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
    private function system_search_farmer()
    {
        $outlet_id=$this->input->post("outlet_id");
        $code=$this->input->post("code");
        $farmer_id=Barcode_helper::get_id_farmer($code);
        if($farmer_id>0)
        {
            $info=Query_helper::get_info($this->config->item('table_pos_setup_farmer_farmer'),'*',array('id ='.$farmer_id),1);
            if($info['status']==$this->config->item('system_status_inactive'))
            {
                $ajax['status']=false;
                $ajax['system_message']='This Customer Cannot Buy Product.<br>Please Contact with admin';
                $this->json_return($ajax);
            }
            else
            {
                if(($info['status_card_require']==$this->config->item('system_status_yes'))&&($info['time_card_off_end']<=time())&&($code!=Barcode_helper::get_barcode_farmer($farmer_id)))
                {
                    $ajax['status']=false;
                    $ajax['system_message']='Scan Dealers Card';
                    $this->json_return($ajax);
                }
                if($info['farmer_type_id']>1)
                {
                    $result=Query_helper::get_info($this->config->item('table_pos_setup_farmer_outlet'),'*',array('farmer_id ='.$farmer_id,'revision =1','outlet_id ='.$outlet_id),1);
                    if(!$result)
                    {
                        $ajax['status']=false;
                        $ajax['system_message']='This Customer Cannot Buy Product from this outlet.<br>Please Contact with admin';
                        $this->json_return($ajax);
                    }
                }
                $this->system_load_sale_from($farmer_id,$outlet_id);

            }
        }
        else
        {
            $ajax['status']=false;
            $ajax['farmer_new']=true;
            $ajax['system_message']='Customer '.$this->lang->line("MSG_NOT_FOUND");
            $this->json_return($ajax);
        }
    }
    private function system_save_farmer()
    {
        $user = User_helper::get_user();
        $time=time();

        if(!(isset($this->permissions['action1']) && ($this->permissions['action1']==1)))
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
            die();
        }

        if(!$this->check_validation_save_farmer())
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->message;
            $this->json_return($ajax);
        }
        else
        {
            $this->db->trans_start();  //DB Transaction Handle START
            $data=array();
            $data['name'] = $this->input->post("name");
            $data['farmer_type_id'] = 1;
            $data['status_card_require'] = $this->config->item('system_status_no');
            $data['mobile_no'] = $this->input->post("mobile_no");
            $data['nid'] = $this->input->post("nid");
            $data['address'] = $this->input->post("address");
            $data['time_card_off_end'] = 0;
            $data['user_created'] = $user->user_id;
            $data['date_created'] = $time;
            $farmer_id=Query_helper::add($this->config->item('table_pos_setup_farmer_farmer'),$data);
            if(!$farmer_id)
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("MSG_SAVED_FAIL");
                $this->json_return($ajax);
            }

            $data=array();
            $data['farmer_id'] = $farmer_id;
            $data['outlet_id'] = $this->input->post("outlet_id");
            $data['revision'] = 1;
            $data['user_created'] = $user->user_id;
            $data['date_created'] = $time;
            Query_helper::add($this->config->item('table_pos_setup_farmer_outlet'),$data);

            $this->db->trans_complete();   //DB Transaction Handle END
            if ($this->db->trans_status() === TRUE)
            {

                $this->message=$this->lang->line("MSG_SAVED_SUCCESS");
                $this->system_load_sale_from($farmer_id,$this->input->post("outlet_id"));
            }
            else
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("MSG_SAVED_FAIL");
                $this->json_return($ajax);
            }
        }
    }
    private function check_validation_save_farmer()
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('outlet_id',$this->lang->line('LABEL_OUTLET_NAME'),'required');
        $this->form_validation->set_rules('name',$this->lang->line('LABEL_NAME'),'required');
        $this->form_validation->set_rules('mobile_no',$this->lang->line('LABEL_MOBILE_NO'),'required');

        if($this->form_validation->run() == FALSE)
        {
            $this->message=validation_errors();
            return false;
        }
        $mobile_no=$this->input->post("mobile_no");
        $exists=Query_helper::get_info($this->config->item('table_pos_setup_farmer_farmer'),array('id'),array('mobile_no ="'.$mobile_no.'"'),1);
        if($exists)
        {
            $this->message="Mobile No already Exists";
            return false;
        }
        return true;
    }
    private function system_load_sale_from($farmer_id,$outlet_id)
    {
        $data=array();
        $data['title']="New Sale";
        $data['item']['outlet_id']=$outlet_id;
        $data['item']['farmer_id']=$farmer_id;

        $this->db->from($this->config->item('table_pos_setup_farmer_farmer').' f');
        $this->db->select('f.*');
        $this->db->join($this->config->item('table_pos_setup_farmer_type').' ft','ft.id = f.farmer_type_id','INNER');
        $this->db->select('ft.name farmer_type_name,ft.discount_self_percentage');
        $this->db->where('f.id',$farmer_id);
        $result=$this->db->get()->row_array();
        $data['item']['farmer_name']=$result['name'];
        $data['item']['farmer_type_id']=$result['farmer_type_id'];
        $data['item']['mobile_no']=$result['mobile_no'];
        $data['item']['nid']=$result['nid'];
        $data['item']['address']=$result['address'];
        $data['item']['farmer_type_name']=$result['farmer_type_name'];
        $data['item']['discount_self_percentage']=$result['discount_self_percentage'];
        $data['item']['discount_message']='';

        $result=Query_helper::get_info($this->config->item('table_pos_setup_farmer_type_outlet_discount'),'*',array('farmer_type_id ='.$data['item']['farmer_type_id'],'expire_time >'.time(),'outlet_id ='.$outlet_id),1);
        if($result)
        {
            $data['item']['discount_self_percentage']=$result['discount_percentage'];
            $data['item']['discount_message']='Outlet Special Discount';
        }

        $ajax['status']=true;
        $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/add",$data,true));
        if($this->message)
        {
            $ajax['system_message']=$this->message;
        }
        $this->json_return($ajax);



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
        $data['farmer_type_name']= 1;
        $data['status_card_require']= 1;
        $data['outlet_name']= 1;
        $data['total_outlet']= 1;
        $data['mobile_no']= 1;
        $data['nid']= 1;
        $data['address']= 1;
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
