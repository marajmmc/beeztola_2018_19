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
        elseif($action=="list_budget_outlet")
        {
            $this->system_list_budget_outlet($id,$id1);
        }
        elseif($action=="get_items_budget_outlet")
        {
            $this->system_get_items_budget_outlet();
        }
        elseif($action=="edit_budget_outlet")
        {
            $this->system_edit_budget_outlet($id,$id1,$id2);
        }
        elseif($action=="get_items_edit_budget_outlet")
        {
            $this->system_get_items_edit_budget_outlet();
        }
        elseif($action=="save_budget_outlet")
        {
            $this->system_save_budget_outlet();
        }
        else
        {
            $this->system_list();
        }
    }
    private function get_preference_headers($method,$dealers=array())
    {
        $data=array();
        if($method=='list')
        {
            $data['fiscal_year_id']= 1;
            $data['fiscal_year']= 1;
            $data['outlet_id']= 1;
            $data['outlet_name']= 1;
            $data['status_budget_forward']= 1;
        }
        else if($method=='list_budget_dealer')
        {
            $data['farmer_id']= 1;
            $data['farmer_name']= 1;
            $data['mobile_no']= 1;
            $data['status']= 1;
            $data['revision_count_budget']= 1;
        }
        else if($method=='edit_budget_dealer')
        {
            $data['crop_name']= 1;
            $data['crop_type_name']= 1;
            $data['variety_name']= 1;
            $data['variety_id']= 1;
            $data['quantity_budget']= 1;
        }
        else if($method=='list_budget_outlet')
        {
            $data['crop_id']= 1;
            $data['crop_name']= 1;
            $data['status_budget']= 1;
        }
        else if($method=='edit_budget_outlet')
        {
            $data['crop_type_name']= 1;
            $data['variety_name']= 1;
            $data['variety_id']= 1;
            foreach($dealers as $dealer)
            {
                $data['quantity_budget_dealer_'.$dealer['farmer_id']]= 1;
            }
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
        $this->db->from($this->config->item('table_pos_si_budget_target').' budget_target');
        $this->db->select('budget_target.status_budget_forward');
        $this->db->select('budget_target.fiscal_year_id');
        $this->db->select('budget_target.outlet_id');
        $this->db->where_in('budget_target.outlet_id',$this->user_outlet_ids);
        $results=$this->db->get()->result_array();
        $budget_target=array();
        foreach($results as $result)
        {
            $budget_target[$result['fiscal_year_id']][$result['outlet_id']]=$result;
        }

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
                if(isset($budget_target[$fy['id']][$outlet['customer_id']]))
                {
                    $data['status_budget_forward']=$budget_target[$fy['id']][$outlet['customer_id']]['status_budget_forward'];
                }
                else
                {
                    $data['status_budget_forward']=$this->config->item('system_status_pending');
                }
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
            //validation forward
            $info_budget_target=$this->get_info_budget_target($fiscal_year_id,$outlet_id);
            if(($info_budget_target['status_budget_forward']==$this->config->item('system_status_forwarded')))
            {
                if(!(isset($this->permissions['action3']) && ($this->permissions['action3']==1)))
                {
                    $ajax['status']=false;
                    $ajax['system_message']='Budget Already Forwarded.';
                    $this->json_return($ajax);
                }
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

        $this->db->from($this->config->item('table_pos_si_budget_target_dealer').' budget_target_dealer');
        $this->db->select('budget_target_dealer.dealer_id');
        $this->db->select('MAX(budget_target_dealer.revision_count_budget) revision_count_budget');
        $this->db->where('budget_target_dealer.fiscal_year_id',$fiscal_year_id);
        $this->db->where('budget_target_dealer.outlet_id',$outlet_id);
        $this->db->group_by('budget_target_dealer.dealer_id');
        $results=$this->db->get()->result_array();
        $budgeted=array();
        foreach($results as $result)
        {
            $budgeted[$result['dealer_id']]=$result['budget_target_dealer'];
        }


        $dealers=$this->get_dealers($outlet_id);


        foreach($dealers as $dealer)
        {
            if(isset($budgeted[$dealer['farmer_id']]))
            {
                $dealer['revision_count_budget']=$budgeted[$dealer['farmer_id']];
            }
            else
            {
                $dealer['revision_count_budget']=0;
            }
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
    private function system_list_budget_outlet($fiscal_year_id=0,$outlet_id=0)
    {
        $user = User_helper::get_user();
        $method='list_budget_outlet';
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
            $data['title']="Yearly Budget Crop list";
            $data['options']['fiscal_year_id']=$fiscal_year_id;
            $data['options']['outlet_id']=$outlet_id;
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/list_budget_outlet",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/list_budget_outlet/'.$fiscal_year_id.'/'.$outlet_id);
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function system_get_items_budget_outlet()
    {
        $items=array();
        $fiscal_year_id=$this->input->post('fiscal_year_id');
        $outlet_id=$this->input->post('outlet_id');

        $results=Query_helper::get_info($this->config->item('table_login_setup_classification_crops'),array('id crop_id','name crop_name'),array('status ="'.$this->config->item('system_status_active').'"'),0,0,array('ordering ASC','id ASC'));
        foreach($results as $crop)
        {
            $item=$crop;
            $item['status_budget']=$this->lang->line('LABEL_STATUS_NOT_DONE');
            $items[]=$item;
        }
        $this->json_return($items);
    }
    private function system_edit_budget_outlet($fiscal_year_id=0,$outlet_id=0,$crop_id=0)
    {
        $user = User_helper::get_user();
        $method='edit_budget_outlet';
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
            if(!($crop_id>0))
            {
                $crop_id=$this->input->post('crop_id');
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
            $data['item']['fiscal_year']=Query_helper::get_info($this->config->item('table_login_basic_setup_fiscal_year'),'*',array('id ='.$fiscal_year_id),1);
            $data['item']['outlet']=Query_helper::get_info($this->config->item('table_login_csetup_cus_info'),'*',array('customer_id ='.$outlet_id,'revision =1'),1);
            $data['item']['crop']=Query_helper::get_info($this->config->item('table_login_setup_classification_crops'),'*',array('id ='.$crop_id),1);
            $data['dealers']=$this->get_dealers($outlet_id);

            $data['system_preference_items']= $this->get_preference_headers($method,$data['dealers']);
            $data['title']="Yearly Budget for (".$data['item']['crop']['name'].')';
            $data['options']['fiscal_year_id']=$fiscal_year_id;
            $data['options']['outlet_id']=$outlet_id;
            $data['options']['crop_id']=$crop_id;
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/edit_budget_outlet",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/edit_budget_outlet/'.$fiscal_year_id.'/'.$outlet_id.'/'.$crop_id);
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function system_get_items_edit_budget_outlet()
    {
        $items=array();
        $fiscal_year_id=$this->input->post('fiscal_year_id');
        $outlet_id=$this->input->post('outlet_id');
        $crop_id=$this->input->post('crop_id');

        $dealers=$this->get_dealers($outlet_id);

        $this->db->from($this->config->item('table_login_setup_classification_varieties').' v');
        $this->db->select('v.id variety_id,v.name variety_name');
        $this->db->join($this->config->item('table_login_setup_classification_crop_types').' crop_type','crop_type.id = v.crop_type_id','INNER');
        $this->db->select('crop_type.name crop_type_name');

        $this->db->where('crop_type.crop_id',$crop_id);
        $this->db->where('v.status',$this->config->item('system_status_active'));
        $this->db->where('v.whose','ARM');

        $this->db->order_by('crop_type.ordering','ASC');
        $this->db->order_by('crop_type.id','ASC');
        $this->db->order_by('v.ordering','ASC');
        $this->db->order_by('v.id','ASC');
        $results=$this->db->get()->result_array();
        foreach($results as $result)
        {
            $item=$result;
            foreach($dealers as $dealer)
            {
                $item['quantity_budget_dealer_'.$dealer['farmer_id']]= 1;
            }
            $item['quantity_budget']='';
            $items[]=$item;
        }

        $this->json_return($items);
    }
    private function system_save_budget_outlet()
    {
        {
            $ajax['status']=false;
            $ajax['system_message']="under progress2";
            $this->json_return($ajax);
        }
    }
    private function get_info_budget_target($fiscal_year_id,$outlet_id)
    {

        $info=Query_helper::get_info($this->config->item('table_pos_si_budget_target'),'*',array('fiscal_year_id ='.$fiscal_year_id,'outlet_id ='.$outlet_id),1);
        if(!$info)
        {
            $user = User_helper::get_user();
            $data=array();
            $data['fiscal_year_id'] = $fiscal_year_id;
            $data['outlet_id'] = $outlet_id;
            $data['date_created'] = time();
            $data['user_created'] = $user->user_id;
            $id=Query_helper::add($this->config->item('table_pos_si_budget_target'),$data);
            $info=Query_helper::get_info($this->config->item('table_pos_si_budget_target'),'*',array('id ='.$id),1);
        }
        return $info;
    }

}
