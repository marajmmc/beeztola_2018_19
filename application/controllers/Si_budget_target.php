<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Si_budget_target extends Root_Controller
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
        $this->lang->load('budget');
        $this->load->helper('budget');
    }
    public function index($action="list", $id=0,$id1=0,$id2=0)
    {
        if($action=="list")
        {
            $this->system_list();
        }
        elseif($action=="get_items")
        {
            $this->system_get_items();
        }
        elseif($action=="list_budget_dealer")
        {
            $this->system_list_budget_dealer($id,$id1);
        }
        elseif($action=="get_items_budget_dealer")
        {
            $this->system_get_items_budget_dealer();
        }
        elseif($action=="edit_budget_dealer")
        {
            $this->system_edit_budget_dealer($id,$id1,$id2);
        }
        elseif($action=="get_items_edit_budget_dealer")
        {
            $this->system_get_items_edit_budget_dealer();
        }
        elseif($action=="save_budget_dealer")
        {
            $this->system_save_budget_dealer();
        }
        else
        {
            $this->system_list();
        }
    }
    private function get_preference_headers($method)
    {
        $data=array();
        if($method=='list')
        {
            $data['fiscal_year_id']= 1;
            $data['fiscal_year']= 1;
            $data['outlet_id']= 1;
            $data['outlet_name']= 1;
        }
        else if($method=='list_budget_dealer')
        {
            $data['farmer_id']= 1;
            $data['farmer_name']= 1;
            $data['mobile_no']= 1;
            $data['status']= 1;
        }
        else if($method=='edit_budget_dealer')
        {
            $data['crop_name']= 1;
            $data['crop_type_name']= 1;
            $data['variety_name']= 1;
            $data['variety_id']= 1;
            $data['quantity_budget']= 1;
        }
        return $data;
    }
    private function system_list()
    {
        $user = User_helper::get_user();
        $method='list';
        if(isset($this->permissions['action0'])&&($this->permissions['action0']==1))
        {
            $data['system_preference_items']= $this->get_preference_headers($method);
            $data['title']="Yearly Budget and Target";
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
        $fiscal_years=Budget_helper::get_fiscal_years();
        $items=array();
        foreach($fiscal_years as $fy)
        {
            foreach($this->user_outlets as $outlet)
            {
                $data=array();
                $data['fiscal_year_id']=$fy['id'];
                $data['fiscal_year']=$fy['text'];
                $data['outlet_id']=$outlet['customer_id'];
                $data['outlet_name']=$outlet['name'];
                $items[]=$data;
            }
        }
        $this->json_return($items);
    }
    private function get_dealers($outlet_id)
    {
        $this->db->from($this->config->item('table_pos_setup_farmer_outlet').' farmer_outlet');
        $this->db->select('farmer_outlet.farmer_id');
        $this->db->join($this->config->item('table_pos_setup_farmer_farmer').' farmer','farmer.id=farmer_outlet.farmer_id','INNER');
        $this->db->select('farmer.name farmer_name,farmer.mobile_no,farmer.status');
        if(!(isset($this->permissions['action3'])&&($this->permissions['action3']==1)))
        {
            $this->db->where('farmer.status',$this->config->item('system_status_active'));
        }
        $this->db->where('farmer.farmer_type_id > ',1);
        $this->db->where('farmer_outlet.revision',1);
        $this->db->where('farmer_outlet.outlet_id',$outlet_id);
        return $this->db->get()->result_array();
    }
    private function system_list_budget_dealer($fiscal_year_id=0,$outlet_id=0)
    {
        $user = User_helper::get_user();
        $method='list_budget_dealer';
        if((isset($this->permissions['action1']) && ($this->permissions['action1']==1))||(isset($this->permissions['action2']) && ($this->permissions['action2']==1)))
        {
            if(!($fiscal_year_id>0))
            {
                $fiscal_year_id=$this->input->post('fiscal_year_id');
            }
            if(!($outlet_id>0))
            {
                $outlet_id=$this->input->post('outlet_id');
            }
            //validation fiscal year
            if(!Budget_helper::check_validation_fiscal_year($fiscal_year_id))
            {
                System_helper::invalid_try('list_budget_dealer',$fiscal_year_id,'Invalid Fiscal year');
                $ajax['status']=false;
                $ajax['system_message']='Invalid Fiscal Year';
                $this->json_return($ajax);
            }
            //validation assigned outlet
            if(!in_array($outlet_id, $this->user_outlet_ids))
            {
                System_helper::invalid_try('list_budget_dealer',$outlet_id,'Outlet Not Assigned');
                $ajax['status']=false;
                $ajax['system_message']='Invalid Outlet.';
                $this->json_return($ajax);
            }

            $data['system_preference_items']= $this->get_preference_headers($method);
            $data['item']['fiscal_year']=Query_helper::get_info($this->config->item('table_login_basic_setup_fiscal_year'),'*',array('id ='.$fiscal_year_id),1);
            $data['item']['outlet']=Query_helper::get_info($this->config->item('table_login_csetup_cus_info'),'*',array('customer_id ='.$outlet_id,'revision =1'),1);
            $data['title']="Yearly Budget Dealer list";
            $data['options']['fiscal_year_id']=$fiscal_year_id;
            $data['options']['outlet_id']=$outlet_id;
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/list_budget_dealer",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/list_budget_dealer/'.$fiscal_year_id.'/'.$outlet_id);
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function system_get_items_budget_dealer()
    {
        $items=array();
        $fiscal_year_id=$this->input->post('fiscal_year_id');
        $outlet_id=$this->input->post('outlet_id');
        $dealers=$this->get_dealers($outlet_id);
        foreach($dealers as $dealer)
        {
            $items[]=$dealer;
        }
        $this->json_return($items);
    }
    private function system_edit_budget_dealer($fiscal_year_id=0,$outlet_id=0,$dealer_id=0)
    {
        $user = User_helper::get_user();
        $method='edit_budget_dealer';
        if((isset($this->permissions['action1']) && ($this->permissions['action1']==1))||(isset($this->permissions['action2']) && ($this->permissions['action2']==1)))
        {
            if(!($fiscal_year_id>0))
            {
                $fiscal_year_id=$this->input->post('fiscal_year_id');
            }
            if(!($outlet_id>0))
            {
                $outlet_id=$this->input->post('outlet_id');
            }
            if(!($dealer_id>0))
            {
                $dealer_id=$this->input->post('farmer_id');
            }
            //validation fiscal year
            if(!Budget_helper::check_validation_fiscal_year($fiscal_year_id))
            {
                System_helper::invalid_try('list_budget_dealer',$fiscal_year_id,'Invalid Fiscal year');
                $ajax['status']=false;
                $ajax['system_message']='Invalid Fiscal Year';
                $this->json_return($ajax);
            }
            //validation assigned outlet
            if(!in_array($outlet_id, $this->user_outlet_ids))
            {
                System_helper::invalid_try('list_budget_dealer',$outlet_id,'Outlet Not Assigned');
                $ajax['status']=false;
                $ajax['system_message']='Invalid Outlet.';
                $this->json_return($ajax);
            }
            //validation dealer
            $dealers=$this->get_dealers($outlet_id);
            $dealer_current=array();
            $valid_dealer=false;
            foreach($dealers as $dealer)
            {
                if($dealer_id==$dealer['farmer_id'])
                {
                    $dealer_current=$dealer;
                    $valid_dealer=true;
                    break;
                }
            }
            if(!$valid_dealer)
            {
                System_helper::invalid_try('edit_budget_dealer',$outlet_id,'Invalid Dealer-'.$dealer_id);
                $ajax['status']=false;
                $ajax['system_message']='Invalid Dealer.';
                $this->json_return($ajax);
            }
            $data['item']['fiscal_year']=Query_helper::get_info($this->config->item('table_login_basic_setup_fiscal_year'),'*',array('id ='.$fiscal_year_id),1);
            $data['item']['outlet']=Query_helper::get_info($this->config->item('table_login_csetup_cus_info'),'*',array('customer_id ='.$outlet_id,'revision =1'),1);
            $data['item']['dealer']=$dealer_current;

            $data['system_preference_items']= $this->get_preference_headers($method);
            $data['title']="Yearly Budget for (".$dealer_current['farmer_name'].')';
            $data['options']['fiscal_year_id']=$fiscal_year_id;
            $data['options']['outlet_id']=$outlet_id;
            $data['options']['dealer_id']=$dealer_id;
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/edit_budget_dealer",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/edit_budget_dealer/'.$fiscal_year_id.'/'.$outlet_id.'/'.$dealer_id);
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function system_get_items_edit_budget_dealer()
    {
        $items=array();
        $fiscal_year_id=$this->input->post('fiscal_year_id');
        $outlet_id=$this->input->post('outlet_id');
        $dealer_id=$this->input->post('dealer_id');

        $this->db->from($this->config->item('table_login_setup_classification_varieties').' v');
        $this->db->select('v.id variety_id,v.name variety_name');
        $this->db->join($this->config->item('table_login_setup_classification_crop_types').' crop_type','crop_type.id = v.crop_type_id','INNER');
        $this->db->select('crop_type.name crop_type_name');
        $this->db->join($this->config->item('table_login_setup_classification_crops').' crop','crop.id = crop_type.crop_id','INNER');
        $this->db->select('crop.name crop_name');
        $this->db->where('v.status',$this->config->item('system_status_active'));
        $this->db->where('v.whose','ARM');
        $this->db->order_by('crop.ordering','ASC');
        $this->db->order_by('crop.id','ASC');
        $this->db->order_by('crop_type.ordering','ASC');
        $this->db->order_by('crop_type.id','ASC');
        $this->db->order_by('v.ordering','ASC');
        $this->db->order_by('v.id','ASC');
        $results=$this->db->get()->result_array();
        foreach($results as $result)
        {
            $item=$result;
            $item['quantity_budget']='';
            $items[]=$item;
        }

        $this->json_return($items);
    }
    private function system_save_budget_dealer()
    {
        {
            $ajax['status']=false;
            $ajax['system_message']="under progress";
            $this->json_return($ajax);
        }
    }
    private function system_details($id)
    {
        //$user=User_helper::get_user();
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

            $this->db->from($this->config->item('table_pos_budget_dealer_monthly').' dealer_monthly');
            $this->db->select('dealer_monthly.*');
            $this->db->join($this->config->item('table_pos_setup_user_outlet').' user_outlet','user_outlet.customer_id=dealer_monthly.outlet_id AND user_outlet.revision=1','INNER');
            $this->db->join($this->config->item('table_login_csetup_customer').' outlet','outlet.id=user_outlet.customer_id AND outlet.status="'.$this->config->item('system_status_active').'"','INNER');
            $this->db->join($this->config->item('table_login_csetup_cus_info').' outlet_info','outlet_info.id=outlet.id AND outlet_info.revision=1 AND outlet_info.type="'.$this->config->item('system_customer_type_outlet_id').'"','INNER');
            $this->db->join($this->config->item('table_login_setup_classification_crops').' crop','crop.id=dealer_monthly.crop_id','INNER');
            $this->db->select('crop.name crop_name');
            $this->db->select('outlet_info.name outlet_name');
            $this->db->where('dealer_monthly.status !="'.$this->config->item('system_status_delete').'"');
            $this->db->where('dealer_monthly.id',$item_id);
            $data['item']=$this->db->get()->row_array();
            if(!$data['item'])
            {
                System_helper::invalid_try('details',$item_id,'View Non Exists');
                $ajax['status']=false;
                $ajax['system_message']='Invalid Try.';
                $this->json_return($ajax);
            }

            $user_ids=array();
            $user_ids[$data['item']['user_created']]=$data['item']['user_created'];
            $user_ids[$data['item']['user_updated']]=$data['item']['user_updated'];
            $user_ids[$data['item']['user_updated_forward']]=$data['item']['user_updated_forward'];
            $data['users']=$this->get_sms_users_info($user_ids);

            $this->db->from($this->config->item('table_pos_setup_farmer_outlet').' farmer_outlet');
            $this->db->select('farmer_outlet.farmer_id');
            $this->db->join($this->config->item('table_pos_setup_farmer_farmer').' farmer_farmer','farmer_farmer.id=farmer_outlet.farmer_id','INNER');
            $this->db->select('farmer_farmer.name farmer_name');
            $this->db->where('farmer_farmer.status',$this->config->item('system_status_active'));
            $this->db->where('farmer_farmer.farmer_type_id > ',1);
            $this->db->where('farmer_outlet.revision',1);
            $this->db->where('farmer_outlet.outlet_id',$data['item']['outlet_id']);
            $data['dealers']=$this->db->get()->result_array();

            $data['title']='Monthly Dealer Budget Forward';
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
    private function system_forward($id)
    {
        //$user=User_helper::get_user();
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

            $this->db->from($this->config->item('table_pos_budget_dealer_monthly').' dealer_monthly');
            $this->db->select('dealer_monthly.*');
            $this->db->join($this->config->item('table_pos_setup_user_outlet').' user_outlet','user_outlet.customer_id=dealer_monthly.outlet_id AND user_outlet.revision=1','INNER');
            $this->db->join($this->config->item('table_login_csetup_customer').' outlet','outlet.id=user_outlet.customer_id AND outlet.status="'.$this->config->item('system_status_active').'"','INNER');
            $this->db->join($this->config->item('table_login_csetup_cus_info').' outlet_info','outlet_info.id=outlet.id AND outlet_info.revision=1 AND outlet_info.type="'.$this->config->item('system_customer_type_outlet_id').'"','INNER');
            $this->db->join($this->config->item('table_login_setup_classification_crops').' crop','crop.id=dealer_monthly.crop_id','INNER');
            $this->db->select('crop.name crop_name');
            $this->db->select('outlet_info.name outlet_name');
            $this->db->where('dealer_monthly.status !="'.$this->config->item('system_status_delete').'"');
            $this->db->where('dealer_monthly.id',$item_id);
            $data['item']=$this->db->get()->row_array();
            if(!$data['item'])
            {
                System_helper::invalid_try('forward',$item_id,'Forward Non Exists');
                $ajax['status']=false;
                $ajax['system_message']='Invalid Try.';
                $this->json_return($ajax);
            }
            if($data['item']['status_forward']==$this->config->item('system_status_forwarded'))
            {
                $ajax['status']=false;
                $ajax['system_message']='All ready forwarded.';
                $this->json_return($ajax);
            }

            $user_ids=array();
            $user_ids[$data['item']['user_created']]=$data['item']['user_created'];
            $user_ids[$data['item']['user_updated']]=$data['item']['user_updated'];
            $user_ids[$data['item']['user_updated_forward']]=$data['item']['user_updated_forward'];
            $data['users']=$this->get_sms_users_info($user_ids);

            $this->db->from($this->config->item('table_pos_setup_farmer_outlet').' farmer_outlet');
            $this->db->select('farmer_outlet.farmer_id');
            $this->db->join($this->config->item('table_pos_setup_farmer_farmer').' farmer_farmer','farmer_farmer.id=farmer_outlet.farmer_id','INNER');
            $this->db->select('farmer_farmer.name farmer_name');
            $this->db->where('farmer_farmer.status',$this->config->item('system_status_active'));
            $this->db->where('farmer_farmer.farmer_type_id > ',1);
            $this->db->where('farmer_outlet.revision',1);
            $this->db->where('farmer_outlet.outlet_id',$data['item']['outlet_id']);
            $data['dealers']=$this->db->get()->result_array();

            $data['title']='Monthly Dealer Budget Forward';
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
        $item_head=$this->input->post('item');
        if($id>0)
        {
            if(!(isset($this->permissions['action7']) && ($this->permissions['action7']==1)))
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
                $this->json_return($ajax);
            }
            if($item_head['status_forward']!=$this->config->item('system_status_forwarded'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Forward field is required.';
                $this->json_return($ajax);
            }
            $this->db->from($this->config->item('table_pos_budget_dealer_monthly').' dealer_monthly');
            $this->db->select('dealer_monthly.*');
            $this->db->join($this->config->item('table_pos_setup_user_outlet').' user_outlet','user_outlet.customer_id=dealer_monthly.outlet_id AND user_outlet.revision=1','INNER');
            $this->db->join($this->config->item('table_login_csetup_customer').' outlet','outlet.id=user_outlet.customer_id AND outlet.status="'.$this->config->item('system_status_active').'"','INNER');
            $this->db->join($this->config->item('table_login_csetup_cus_info').' outlet_info','outlet_info.id=outlet.id AND outlet_info.revision=1 AND outlet_info.type="'.$this->config->item('system_customer_type_outlet_id').'"','INNER');
            $this->db->join($this->config->item('table_login_setup_classification_crops').' crop','crop.id=dealer_monthly.crop_id','INNER');
            $this->db->select('crop.name crop_name');
            $this->db->select('outlet_info.name outlet_name');
            $this->db->where('dealer_monthly.status !="'.$this->config->item('system_status_delete').'"');
            $this->db->where('dealer_monthly.id',$id);
            $data['item']=$this->db->get()->row_array();
            if(!$data['item'])
            {
                System_helper::invalid_try('save_forward',$id,'Save Forward Non Exists');
                $ajax['status']=false;
                $ajax['system_message']='Invalid Try.';
                $this->json_return($ajax);
            }
            if($data['item']['status_forward']==$this->config->item('system_status_forwarded'))
            {
                $ajax['status']=false;
                $ajax['system_message']='All ready forwarded.';
                $this->json_return($ajax);
            }
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }


        $this->db->trans_start();

        $item_head['date_updated_forward']=$time;
        $item_head['user_updated_forward']=$user->user_id;
        Query_helper::update($this->config->item('table_pos_budget_dealer_monthly'),$item_head,array('id='.$id));

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
    private function system_get_variety()
    {
        $outlet_id=$this->input->post('outlet_id');
        $year_id=$this->input->post('year_id');
        $month_id=$this->input->post('month_id');
        $crop_id=$this->input->post('crop_id');
        $data=array();

        $this->db->from($this->config->item('table_pos_setup_farmer_outlet').' farmer_outlet');
        $this->db->select('farmer_outlet.farmer_id');
        $this->db->join($this->config->item('table_pos_setup_farmer_farmer').' farmer_farmer','farmer_farmer.id=farmer_outlet.farmer_id','INNER');
        $this->db->select('farmer_farmer.name farmer_name');
        $this->db->where('farmer_farmer.status',$this->config->item('system_status_active'));
        $this->db->where('farmer_farmer.farmer_type_id > ',1);
        $this->db->where('farmer_outlet.revision',1);
        $this->db->where('farmer_outlet.outlet_id',$outlet_id);
        $dealers=$this->db->get()->result_array();


        //$results=Query_helper::get_info($this->config->item('table_pos_budget_dealer_monthly_details'),'*',array('budget_monthly_id ='.$id));
        $this->db->from($this->config->item('table_pos_budget_dealer_monthly').' budget_dealer_monthly');
        $this->db->select('budget_dealer_monthly.*');
        $this->db->join($this->config->item('table_pos_budget_dealer_monthly_details').' details','budget_dealer_monthly.id=details.budget_monthly_id','INNER');
        $this->db->select('details.*, details.id details_id');
        $this->db->where('budget_dealer_monthly.status !="'.$this->config->item('system_status_delete').'"');
        $this->db->where('budget_dealer_monthly.outlet_id',$outlet_id);
        $this->db->where('budget_dealer_monthly.year_id',$year_id);
        $this->db->where('budget_dealer_monthly.month_id',$month_id);
        //$this->db->where('budget_dealer_monthly.crop_id',$crop_id);
        $results=$this->db->get()->result_array();

        $crop_ids[0]=0;
        $variety_ids=array();
        $budget_stocks=array();
        foreach($results as $result)
        {
            $data[$result['variety_id']][$result['pack_size_id']][$result['dealer_id']]=$result;
            $crop_ids[$result['crop_id']]=$result['crop_id'];

            $budget_stocks[$result['variety_id']][$result['pack_size_id']]=$result;
            $variety_ids[$result['variety_id']]=$result['variety_id'];
        }

        $current_stocks=Stock_helper::get_variety_stock($outlet_id, $variety_ids);

        $this->db->from($this->config->item('table_login_setup_classification_variety_price').' variety_price');
        $this->db->select('variety_price.id, variety_price.price_net');
        $this->db->join($this->config->item('table_login_setup_classification_varieties').' v','v.id = variety_price.variety_id','INNER');
        $this->db->select('v.id variety_id,v.name variety_name');
        $this->db->join($this->config->item('table_login_setup_classification_crop_types').' crop_type','crop_type.id = v.crop_type_id','INNER');
        $this->db->select('crop_type.id crop_type_id,crop_type.name crop_type_name');
        $this->db->join($this->config->item('table_login_setup_classification_crops').' crop','crop.id=crop_type.crop_id','INNER');
        $this->db->select('crop.id crop_id, crop.name crop_name');
        $this->db->join($this->config->item('table_login_setup_classification_pack_size').' pack','pack.id = variety_price.pack_size_id','INNER');
        $this->db->select('pack.name pack_size,pack.id pack_size_id');
        $this->db->where_in('crop_type.crop_id',$crop_ids);
        $this->db->where('v.status !=',$this->config->item('system_status_delete'));
        $this->db->where('pack.status !=',$this->config->item('system_status_delete'));
        //$this->db->order_by('crop_type.ordering ASC');
        $this->db->order_by('crop.id, crop_type.id, v.id, pack.id ASC');
        $results=$this->db->get()->result_array();
        //echo $this->db->last_query();

        $items=array();
        $first_row=true;
        $prev_crop_name='';
        $prev_crop_type_name='';
        foreach($results as $result)
        {
            $item=array();
            if(!$first_row)
            {
                if($prev_crop_name!=$result['crop_name'])
                {
                    $prev_crop_name=$result['crop_name'];
                }
                elseif($prev_crop_type_name!=$result['crop_type_name'])
                {
                    $result['crop_name']='';
                    $prev_crop_type_name=$result['crop_type_name'];
                }
                else
                {
                    $result['crop_name']='';
                    $result['crop_type_name']='';
                }
            }
            else
            {
                $prev_crop_name=$result['crop_name'];
                $prev_crop_type_name=$result['crop_type_name'];
                $first_row=false;
            }
            $item['id']=$result['id'];
            $item['crop_id']=$result['crop_id'];
            $item['crop_type_id']=$result['crop_type_id'];
            $item['variety_id']=$result['variety_id'];
            $item['pack_size_id']=$result['pack_size_id'];
            $item['crop_name']=$result['crop_name'];
            $item['crop_type_name']=$result['crop_type_name'];
            $item['variety_name']=$result['variety_name'];
            $item['pack_size']=$result['pack_size'];
            $item['price_net']=$result['price_net'];

            $item['current_stock']=0;
            if(isset($budget_stocks[$result['variety_id']][$result['pack_size_id']]) && $budget_stocks[$result['variety_id']][$result['pack_size_id']]['current_stock'])
            {
                $item['current_stock']=$budget_stocks[$result['variety_id']][$result['pack_size_id']]['current_stock'];
            }
            else
            {
                if(isset($current_stocks[$result['variety_id']][$result['pack_size_id']]) && $current_stocks[$result['variety_id']][$result['pack_size_id']]['current_stock'])
                {
                    $item['current_stock']=$current_stocks[$result['variety_id']][$result['pack_size_id']]['current_stock'];
                }
            }

            foreach($dealers as $dealer)
            {
                if(isset($data[$result['variety_id']][$result['pack_size_id']][$dealer['farmer_id']]) && $data[$result['variety_id']][$result['pack_size_id']][$dealer['farmer_id']]['amount_budget'])
                {
                    $item['amount_budget_'.$dealer['farmer_id']]=$data[$result['variety_id']][$result['pack_size_id']][$dealer['farmer_id']]['amount_budget'];
                }
                else
                {
                    $item['amount_budget_'.$dealer['farmer_id']]='';
                }
            }

            $items[]=$item;
        }
        $this->json_return($items);

    }
    /*private function check_validation()
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('item[outlet_id]',$this->lang->line('LABEL_OUTLET_NAME'),'required');
        $this->form_validation->set_rules('item[crop_id]',$this->lang->line('LABEL_CROP_NAME'),'required');
        if($this->form_validation->run() == FALSE)
        {
            $this->message=validation_errors();
            return false;
        }
        return true;
    }*/

}
