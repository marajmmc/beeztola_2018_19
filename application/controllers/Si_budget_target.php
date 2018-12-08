<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Si_budget_target extends Root_Controller
{
    public $message;
    public $permissions;
    public $controller_url;
    public $common_view_location;
    public $user_outlets;
    public $user_outlet_ids;
    public function __construct()
    {
        parent::__construct();
        $this->message="";
        $this->permissions=User_helper::get_permission(get_class());
        $this->controller_url=strtolower(get_class());
        $this->common_view_location='si_budget_target';
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
        elseif($action=="budget_forward")
        {
            $this->system_budget_forward($id,$id1);
        }
        elseif($action=="get_items_budget_forward")
        {
            $this->system_get_items_budget_forward();
        }
        elseif($action=="save_forward_budget")
        {
            $this->system_save_forward_budget();
        }

        elseif($action=="list_assign_target_dealer")
        {
            $this->system_list_assign_target_dealer($id,$id1);
        }
        elseif($action=="get_items_list_assign_target_dealer")
        {
            $this->system_get_items_list_assign_target_dealer();
        }
        elseif($action=="assign_target_dealer")
        {
            $this->system_assign_target_dealer($id,$id1,$id2);
        }
        elseif($action=="get_items_assign_target_dealer")
        {
            $this->system_get_items_assign_target_dealer();
        }
        elseif($action=="save_assign_target_dealer")
        {
            $this->system_save_assign_target_dealer();
        }

        elseif($action=="assign_target_dealer_forward")
        {
            $this->system_assign_target_dealer_forward($id,$id1);
        }
        elseif($action=="get_items_assign_target_dealer_forward")
        {
            $this->system_get_items_assign_target_dealer_forward();
        }
        elseif($action=="save_assign_target_dealer_forward")
        {
            $this->system_save_assign_target_dealer_forward();
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

            $data['number_of_dealer_active']= 1;
            $data['number_of_dealer_budgeted']= 1;
            $data['number_of_dealer_budget_due']= 1;
            $data['number_of_dealer_targeted']= 1;
            $data['number_of_dealer_target_due']= 1;

            $data['status_budget_forward']= 1;
            $data['status_target_outlet_forward']= 1;
            $data['status_target_dealer_forward']= 1;
            $data['status_target_outlet_next_year_forward']= 1;
        }
        else if($method=='list_budget_dealer')
        {
            $data['farmer_id']= 1;
            $data['farmer_name']= 1;
            $data['mobile_no']= 1;
            $data['number_of_variety_active']= 1;
            $data['number_of_variety_budgeted']= 1;
            $data['number_of_variety_budget_due']= 1;
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
            $data['number_of_variety_active']= 1;
            $data['number_of_variety_budgeted']= 1;
            $data['number_of_variety_budget_due']= 1;
        }
        else if($method=='edit_budget_outlet')
        {
            $data['crop_type_name']= 1;
            $data['variety_name']= 1;
            $data['variety_id']= 1;
            $data['quantity_budget_dealer_total']= 1;
            $data['quantity_budget']= 1;
        }
        else if($method=='budget_forward')
        {
            $data['crop_name']= 1;
            $data['crop_type_name']= 1;
            $data['variety_name']= 1;
            $data['variety_id']= 1;
            $data['quantity_budget_dealer_total']= 1;
            $data['quantity_budget_outlet']= 1;
        }

        else if($method=='list_target_dealer')
        {
            $data['id']= 1;
            $data['crop_id']= 1;
            $data['crop_name']= 1;
            $data['number_of_variety_active']= 1;
            $data['number_of_variety_targeted']= 1;
            $data['number_of_variety_target_due']= 1;
        }
        else if($method=='assign_target_dealer')
        {
            $data['crop_type_name']= 1;
            $data['variety_name']= 1;
            $data['variety_id']= 1;
            $data['quantity_budget_outlet']= 1;
            $data['quantity_target_outlet']= 1;
            $data['quantity_target_dealer_total']= 1;
        }
        else if($method=='target_dealer_forward')
        {
            $data['crop_name']= 1;
            $data['crop_type_name']= 1;
            $data['variety_name']= 1;
            $data['variety_id']= 1;
            $data['quantity_budget_dealer_total']= 1;
            $data['quantity_budget_outlet']= 1;
        }
        return $data;
    }
    private function system_list()
    {
        //$user = User_helper::get_user();
        $method='list';
        if(isset($this->permissions['action0'])&&($this->permissions['action0']==1))
        {
            $data['system_preference_items']= $this->get_preference_headers($method);
            $data['title']="Yearly Dealer & Outlet Budget / Target";
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
        $this->db->from($this->config->item('table_pos_si_budget_target').' item');
        $this->db->select('item.status_budget_forward, item.status_target_dealer_forward, item.fiscal_year_id, item.outlet_id');

        $this->db->join($this->config->item('table_pos_si_budget_target_dealer').' dealer_budget','dealer_budget.fiscal_year_id=item.fiscal_year_id AND dealer_budget.outlet_id=item.outlet_id AND dealer_budget.quantity_budget>0','LEFT');
        $this->db->select('COUNT(DISTINCT dealer_budget.dealer_id) number_of_dealer_budgeted', false);

        $this->db->join($this->config->item('table_pos_si_budget_target_dealer').' dealer_target','dealer_target.fiscal_year_id=item.fiscal_year_id AND dealer_target.outlet_id=item.outlet_id AND dealer_target.quantity_target>0','LEFT');
        $this->db->select('COUNT(DISTINCT dealer_target.dealer_id) number_of_dealer_targeted', false);

        $this->db->join($this->config->item('table_login_csetup_cus_info').' cus_info','cus_info.customer_id = item.outlet_id AND cus_info.revision = 1','INNER');
        $this->db->join($this->config->item('table_login_setup_location_districts').' district','district.id = cus_info.district_id','INNER');
        $this->db->join($this->config->item('table_login_setup_location_territories').' territory','territory.id = district.territory_id','INNER');
        $this->db->join($this->config->item('table_bms_zi_budget_target').' zi_budget_target','zi_budget_target.fiscal_year_id=item.fiscal_year_id AND zi_budget_target.zone_id=territory.zone_id','LEFT');
        $this->db->select('zi_budget_target.status_target_outlet_forward, zi_budget_target.status_target_outlet_next_year_forward');

        $this->db->where_in('item.outlet_id',$this->user_outlet_ids);
        $this->db->group_by('item.fiscal_year_id, item.outlet_id');
        $results=$this->db->get()->result_array();

        $budget_target=array();
        foreach($results as $result)
        {
            $budget_target[$result['fiscal_year_id']][$result['outlet_id']]=$result;
        }

        $this->db->from($this->config->item('table_pos_setup_farmer_outlet').' farmer_outlet');
        $this->db->select('COUNT(farmer_outlet.farmer_id) number_of_dealer, farmer_outlet.outlet_id', false);
        $this->db->join($this->config->item('table_pos_setup_farmer_farmer').' farmer','farmer.id=farmer_outlet.farmer_id','INNER');
        //$this->db->select('farmer.name farmer_name,farmer.mobile_no,farmer.status');
        $this->db->where('farmer.status',$this->config->item('system_status_active'));
        $this->db->where('farmer.farmer_type_id > ',1);
        $this->db->where('farmer_outlet.revision',1);
        $this->db->where_in('farmer_outlet.outlet_id',$this->user_outlet_ids);
        $this->db->group_by('farmer_outlet.outlet_id');
        $results = $this->db->get()->result_array();
        $dealers=array();
        foreach($results as $result)
        {
            $dealers[$result['outlet_id']]=$result['number_of_dealer'];
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

                $data['number_of_dealer_active']=isset($dealers[$outlet['customer_id']])?$dealers[$outlet['customer_id']]:0;
                $data['number_of_dealer_budgeted']=0;
                $data['number_of_dealer_targeted']=0;

                $data['status_budget_forward']=$this->config->item('system_status_pending'); //SI budget forward
                $data['status_target_outlet_forward']=$this->config->item('system_status_pending'); // ZI outlet target forward
                $data['status_target_dealer_forward']=$this->config->item('system_status_pending'); // SI dealer target forward
                $data['status_target_outlet_next_year_forward']=$this->config->item('system_status_pending'); // ZI next 3y target forward
                if(isset($budget_target[$fy['id']][$outlet['customer_id']]))
                {
                    $data['number_of_dealer_budgeted']=$budget_target[$fy['id']][$outlet['customer_id']]['number_of_dealer_budgeted'];
                    $data['number_of_dealer_targeted']=$budget_target[$fy['id']][$outlet['customer_id']]['number_of_dealer_targeted'];

                    $data['status_budget_forward']=$budget_target[$fy['id']][$outlet['customer_id']]['status_budget_forward'];
                    $data['status_target_dealer_forward']=$budget_target[$fy['id']][$outlet['customer_id']]['status_target_dealer_forward'];
                }
                if(isset($budget_target[$fy['id']][$outlet['customer_id']]['status_target_outlet_forward'])  && $budget_target[$fy['id']][$outlet['customer_id']]['status_target_outlet_forward'])
                {
                    $data['status_target_outlet_forward']=$budget_target[$fy['id']][$outlet['customer_id']]['status_target_outlet_forward'];
                }
                if(isset($budget_target[$fy['id']][$outlet['customer_id']]['status_target_outlet_next_year_forward'])  && $budget_target[$fy['id']][$outlet['customer_id']]['status_target_outlet_next_year_forward'])
                {
                    $data['status_target_outlet_next_year_forward']=$budget_target[$fy['id']][$outlet['customer_id']]['status_target_outlet_next_year_forward'];
                }
                $data['number_of_dealer_budget_due']=($data['number_of_dealer_active']-$data['number_of_dealer_budgeted']);
                $data['number_of_dealer_target_due']=($data['number_of_dealer_active']-$data['number_of_dealer_targeted']);

                $items[]=$data;
            }
        }
        $this->json_return($items);
    }

    /* Dealer Budget*/
    private function system_list_budget_dealer($fiscal_year_id=0,$outlet_id=0)
    {
        //$user = User_helper::get_user();
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
                System_helper::invalid_try(__FUNCTION__,$fiscal_year_id,'Invalid Fiscal year');
                $ajax['status']=false;
                $ajax['system_message']='Invalid Fiscal Year';
                $this->json_return($ajax);
            }
            //validation assigned outlet
            if(!in_array($outlet_id, $this->user_outlet_ids))
            {
                System_helper::invalid_try(__FUNCTION__,$outlet_id,'Outlet Not Assigned');
                $ajax['status']=false;
                $ajax['system_message']='Invalid Outlet.';
                $this->json_return($ajax);
            }
            //validation forward
            $info_budget=$this->get_info_budget_target($fiscal_year_id,$outlet_id);
            if(!(isset($this->permissions['action3']) && ($this->permissions['action3']==1)))
            {
                if(($info_budget['status_budget_forward']==$this->config->item('system_status_forwarded')))
                {
                    $ajax['status']=false;
                    $ajax['system_message']='Budget Already Forwarded.';
                    $this->json_return($ajax);
                }
            }

            $data['system_preference_items']= $this->get_preference_headers($method);
            $data['fiscal_year_budget_target']=Query_helper::get_info($this->config->item('table_login_basic_setup_fiscal_year'),'*',array('id ='.$fiscal_year_id),1);
            $data['outlet']=Query_helper::get_info($this->config->item('table_login_csetup_cus_info'),'*',array('customer_id ='.$outlet_id,'revision =1'),1);
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
        //$this->db->select('SUM(budget_target_dealer.variety_id) number_of_variety_budgeted');
        $this->db->select('SUM(CASE WHEN budget_target_dealer.quantity_budget>0 then 1 ELSE 0 END) number_of_variety_budgeted',false);
        $this->db->where('budget_target_dealer.fiscal_year_id',$fiscal_year_id);
        $this->db->where('budget_target_dealer.outlet_id',$outlet_id);
        $this->db->group_by('budget_target_dealer.dealer_id');
        $results=$this->db->get()->result_array();
        $budgeted=array();
        foreach($results as $result)
        {
            $budgeted[$result['dealer_id']]=$result['number_of_variety_budgeted'];
        }
        $varieties=Budget_helper::get_crop_type_varieties();

        $dealers=$this->get_dealers($outlet_id);
        foreach($dealers as $dealer)
        {
            $dealer['number_of_variety_active']=sizeof($varieties);
            $dealer['number_of_variety_budgeted']=0;
            $dealer['number_of_variety_budget_due']=0;
            if(isset($budgeted[$dealer['farmer_id']]))
            {
                $dealer['number_of_variety_budgeted']=$budgeted[$dealer['farmer_id']];
            }
            $dealer['number_of_variety_budget_due']=($dealer['number_of_variety_active']-$dealer['number_of_variety_budgeted']);
            $items[]=$dealer;
        }
        $this->json_return($items);
    }
    private function system_edit_budget_dealer($fiscal_year_id=0,$outlet_id=0,$dealer_id=0)
    {
        //$user = User_helper::get_user();
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
                System_helper::invalid_try(__FUNCTION__,$fiscal_year_id,'Invalid Fiscal year');
                $ajax['status']=false;
                $ajax['system_message']='Invalid Fiscal Year';
                $this->json_return($ajax);
            }
            //validation assigned outlet
            if(!in_array($outlet_id, $this->user_outlet_ids))
            {
                System_helper::invalid_try(__FUNCTION__,$outlet_id,'Outlet Not Assigned');
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
                System_helper::invalid_try(__FUNCTION__,$outlet_id,'Invalid Dealer-'.$dealer_id);
                $ajax['status']=false;
                $ajax['system_message']='Invalid Dealer.';
                $this->json_return($ajax);
            }

            //validation forward
            $info_budget=$this->get_info_budget_target($fiscal_year_id,$outlet_id);
            if(!(isset($this->permissions['action3']) && ($this->permissions['action3']==1)))
            {
                if(($info_budget['status_budget_forward']==$this->config->item('system_status_forwarded')))
                {
                    $ajax['status']=false;
                    $ajax['system_message']='Budget Already Forwarded.';
                    $this->json_return($ajax);
                }
            }

            $data['fiscal_years_previous_sales']=Query_helper::get_info($this->config->item('table_login_basic_setup_fiscal_year'),'*',array('id <'.$fiscal_year_id),Budget_helper::$NUM_FISCAL_YEAR_PREVIOUS_SALE,0,array('id DESC'));
            $data['fiscal_year_budget_target']=Query_helper::get_info($this->config->item('table_login_basic_setup_fiscal_year'),'*',array('id ='.$fiscal_year_id),1);
            $data['outlet']=Query_helper::get_info($this->config->item('table_login_csetup_cus_info'),'*',array('customer_id ='.$outlet_id,'revision =1'),1);
            $data['dealer']=$dealer_current;
            $data['acres']=$this->get_acres($outlet_id);

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

        $fiscal_years_previous_sales=Query_helper::get_info($this->config->item('table_login_basic_setup_fiscal_year'),'*',array('id <'.$fiscal_year_id),Budget_helper::$NUM_FISCAL_YEAR_PREVIOUS_SALE,0,array('id DESC'));
        $sales_previous=$this->get_sales_previous_years_dealers($fiscal_years_previous_sales,array($dealer_id));
        //old items
        $results=Query_helper::get_info($this->config->item('table_pos_si_budget_target_dealer'),'*',array('fiscal_year_id ='.$fiscal_year_id,'outlet_id ='.$outlet_id,'dealer_id ='.$dealer_id));
        $items_old=array();
        foreach($results as $result)
        {
            $items_old[$result['variety_id']]=$result;
        }
        //variety lists
        $results=Budget_helper::get_crop_type_varieties();
        foreach($results as $result)
        {
            $info=$this->initialize_row_edit_budget_dealer($fiscal_years_previous_sales,$result);
            foreach($fiscal_years_previous_sales as $fy)
            {
                if(isset($sales_previous[$fy['id']][$dealer_id][$result['variety_id']]))
                {
                    $info['quantity_sale_'.$fy['id']]=$sales_previous[$fy['id']][$dealer_id][$result['variety_id']]/1000;
                }
            }
            if(isset($items_old[$result['variety_id']]))
            {
                $info['quantity_budget']=$items_old[$result['variety_id']]['quantity_budget'];
            }
            $items[]=$info;
        }

        $this->json_return($items);
    }
    private function initialize_row_edit_budget_dealer($fiscal_years,$info)
    {
        $row=$this->get_preference_headers('edit_budget_dealer');
        foreach($row  as $key=>$r)
        {
            $row[$key]=0;
        }
        $row['crop_name']=$info['crop_name'];
        $row['crop_type_name']=$info['crop_type_name'];
        $row['variety_name']=$info['variety_name'];
        $row['variety_id']=$info['variety_id'];
        foreach($fiscal_years as $fy)
        {
            $row['quantity_sale_'.$fy['id']]=0;
        }
        return $row;
    }
    private function system_save_budget_dealer()
    {
        $user = User_helper::get_user();
        $time=time();
        $item_head=$this->input->post('item');
        $items=$this->input->post('items');
        if(!((isset($this->permissions['action1']) && ($this->permissions['action1']==1))||(isset($this->permissions['action2']) && ($this->permissions['action2']==1))))
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
        //validation fiscal year
        if(!Budget_helper::check_validation_fiscal_year($item_head['fiscal_year_id']))
        {
            System_helper::invalid_try(__FUNCTION__,$item_head['fiscal_year_id'],'Invalid Fiscal year');
            $ajax['status']=false;
            $ajax['system_message']='Invalid Fiscal Year';
            $this->json_return($ajax);
        }
        //validation assigned outlet
        if(!in_array($item_head['outlet_id'], $this->user_outlet_ids))
        {
            System_helper::invalid_try(__FUNCTION__,$item_head['outlet_id'],'Outlet Not Assigned');
            $ajax['status']=false;
            $ajax['system_message']='Invalid Outlet.';
            $this->json_return($ajax);
        }
        //validation forward
        $info_budget=$this->get_info_budget_target($item_head['fiscal_year_id'],$item_head['outlet_id']);
        if(!(isset($this->permissions['action3']) && ($this->permissions['action3']==1)))
        {
            if(($info_budget['status_budget_forward']==$this->config->item('system_status_forwarded')))
            {
                $ajax['status']=false;
                $ajax['system_message']='Budget Already Forwarded.';
                $this->json_return($ajax);
            }
        }
        //validation dealer
        $dealers=$this->get_dealers($item_head['outlet_id']);
        $valid_dealer=false;
        foreach($dealers as $dealer)
        {
            if($item_head['dealer_id']==$dealer['farmer_id'])
            {
                $valid_dealer=true;
                break;
            }
        }
        if(!$valid_dealer)
        {
            System_helper::invalid_try(__FUNCTION__,$item_head['outlet_id'],'Invalid Dealer-'.$item_head['dealer_id']);
            $ajax['status']=false;
            $ajax['system_message']='Invalid Dealer.';
            $this->json_return($ajax);
        }
        //old items
        $results=Query_helper::get_info($this->config->item('table_pos_si_budget_target_dealer'),'*',array('fiscal_year_id ='.$item_head['fiscal_year_id'],'outlet_id ='.$item_head['outlet_id'],'dealer_id ='.$item_head['dealer_id']));
        $items_old=array();
        foreach($results as $result)
        {
            $items_old[$result['variety_id']]=$result;
        }

        $this->db->trans_start();  //DB Transaction Handle START

        foreach($items as $variety_id=>$quantity_budget)
        {
            if(isset($items_old[$variety_id]))
            {
                if($items_old[$variety_id]['quantity_budget']!=$quantity_budget)
                {
                    $data['quantity_budget']=$quantity_budget;
                    $data['date_updated_budget']=$time;
                    $data['user_updated_budget']=$user->user_id;
                    $this->db->set('revision_count_budget','revision_count_budget+1',false);
                    Query_helper::update($this->config->item('table_pos_si_budget_target_dealer'),$data,array('id='.$items_old[$variety_id]['id']),false);
                }
            }
            else
            {
                $data=array();
                $data['fiscal_year_id']=$item_head['fiscal_year_id'];
                $data['outlet_id']=$item_head['outlet_id'];
                $data['dealer_id']=$item_head['dealer_id'];
                $data['variety_id']=$variety_id;
                $data['quantity_budget']=0;
                if($quantity_budget>0)
                {
                    $data['quantity_budget']=$quantity_budget;
                    $data['revision_count_budget']=1;
                }

                $data['date_updated_budget'] = $time;
                $data['user_updated_budget'] = $user->user_id;
                Query_helper::add($this->config->item('table_pos_si_budget_target_dealer'),$data,false);
            }
        }

        $this->db->trans_complete();   //DB Transaction Handle END

        if ($this->db->trans_status() === TRUE)
        {
            $this->message=$this->lang->line("MSG_SAVED_SUCCESS");
            $this->system_list_budget_dealer($item_head['fiscal_year_id'],$item_head['outlet_id']);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("MSG_SAVED_FAIL");
            $this->json_return($ajax);
        }
    }

    /* Outlet Budget*/
    private function system_list_budget_outlet($fiscal_year_id=0,$outlet_id=0)
    {
        //$user = User_helper::get_user();
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
                System_helper::invalid_try(__FUNCTION__,$fiscal_year_id,'Invalid Fiscal year');
                $ajax['status']=false;
                $ajax['system_message']='Invalid Fiscal Year';
                $this->json_return($ajax);
            }
            //validation assigned outlet
            if(!in_array($outlet_id, $this->user_outlet_ids))
            {
                System_helper::invalid_try(__FUNCTION__,$outlet_id,'Outlet Not Assigned');
                $ajax['status']=false;
                $ajax['system_message']='Invalid Outlet.';
                $this->json_return($ajax);
            }
            //validation forward
            $info_budget=$this->get_info_budget_target($fiscal_year_id,$outlet_id);
            if(!(isset($this->permissions['action3']) && ($this->permissions['action3']==1)))
            {
                if(($info_budget['status_budget_forward']==$this->config->item('system_status_forwarded')))
                {
                    $ajax['status']=false;
                    $ajax['system_message']='Budget Already Forwarded.';
                    $this->json_return($ajax);
                }
            }

            $data['system_preference_items']= $this->get_preference_headers($method);
            $data['fiscal_year']=Query_helper::get_info($this->config->item('table_login_basic_setup_fiscal_year'),'*',array('id ='.$fiscal_year_id),1);
            $data['outlet']=Query_helper::get_info($this->config->item('table_login_csetup_cus_info'),'*',array('customer_id ='.$outlet_id,'revision =1'),1);
            $data['title']="Yearly Outlet Budget Crop list";
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

        //get budget revision
        $this->db->from($this->config->item('table_pos_si_budget_target_outlet').' budget_target_outlet');
        $this->db->select('SUM(CASE WHEN budget_target_outlet.quantity_budget>0 then 1 ELSE 0 END) number_of_variety_budgeted',false);
        $this->db->join($this->config->item('table_login_setup_classification_varieties').' v','v.id = budget_target_outlet.variety_id','INNER');
        $this->db->join($this->config->item('table_login_setup_classification_crop_types').' crop_type','crop_type.id = v.crop_type_id','INNER');
        $this->db->select('crop_type.crop_id');
        $this->db->where('budget_target_outlet.fiscal_year_id',$fiscal_year_id);
        $this->db->where('budget_target_outlet.outlet_id',$outlet_id);
        $this->db->group_by('crop_type.crop_id');
        $results=$this->db->get()->result_array();
        $budgeted=array();
        foreach($results as $result)
        {
            $budgeted[$result['crop_id']]=$result['number_of_variety_budgeted'];
        }

        $varieties=Budget_helper::get_crop_type_varieties();
        $crops=array();
        foreach($varieties as $variety)
        {
            $crops[$variety['crop_id']]['crop_id']=$variety['crop_id'];
            $crops[$variety['crop_id']]['crop_name']=$variety['crop_name'];
            if(isset($crops[$variety['crop_id']]['number_of_variety_active']))
            {
                $crops[$variety['crop_id']]['number_of_variety_active']+=1;
            }
            else
            {
                $crops[$variety['crop_id']]['number_of_variety_active']=1;
            }
        }
        //crop list
        //$results=Query_helper::get_info($this->config->item('table_login_setup_classification_crops'),array('id crop_id','name crop_name'),array('status ="'.$this->config->item('system_status_active').'"'),0,0,array('ordering ASC','id ASC'));
        foreach($crops as $crop)
        {
            $item=$crop;
            $item['number_of_variety_active']=$crop['number_of_variety_active'];
            $item['number_of_variety_budgeted']=0;
            $item['number_of_variety_budget_due']=0;
            if(isset($budgeted[$crop['crop_id']]))
            {
                $item['number_of_variety_budgeted']=$budgeted[$crop['crop_id']];
            }
            $item['number_of_variety_budget_due']=($item['number_of_variety_active']-$item['number_of_variety_budgeted']);
            $items[]=$item;
        }
        $this->json_return($items);
    }
    private function system_edit_budget_outlet($fiscal_year_id=0,$outlet_id=0,$crop_id=0)
    {
        //$user = User_helper::get_user();
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
                System_helper::invalid_try(__FUNCTION__,$fiscal_year_id,'Invalid Fiscal year');
                $ajax['status']=false;
                $ajax['system_message']='Invalid Fiscal Year';
                $this->json_return($ajax);
            }
            //validation assigned outlet
            if(!in_array($outlet_id, $this->user_outlet_ids))
            {
                System_helper::invalid_try(__FUNCTION__,$outlet_id,'Outlet Not Assigned');
                $ajax['status']=false;
                $ajax['system_message']='Invalid Outlet.';
                $this->json_return($ajax);
            }
            // valid crop check
            $crop=Query_helper::get_info($this->config->item('table_login_setup_classification_crops'),'*',array('id ='.$crop_id),1);
            if(!$crop)
            {
                $ajax['status']=false;
                $ajax['system_message']='Wrong crop id.';
                $this->json_return($ajax);
            }
            //validation forward
            $info_budget=$this->get_info_budget_target($fiscal_year_id,$outlet_id);
            if(!(isset($this->permissions['action3']) && ($this->permissions['action3']==1)))
            {
                if(($info_budget['status_budget_forward']==$this->config->item('system_status_forwarded')))
                {
                    $ajax['status']=false;
                    $ajax['system_message']='Budget Already Forwarded.';
                    $this->json_return($ajax);
                }
            }

            $data['fiscal_years_previous_sales']=Query_helper::get_info($this->config->item('table_login_basic_setup_fiscal_year'),'*',array('id <'.$fiscal_year_id),Budget_helper::$NUM_FISCAL_YEAR_PREVIOUS_SALE,0,array('id DESC'));
            $data['fiscal_year']=Query_helper::get_info($this->config->item('table_login_basic_setup_fiscal_year'),'*',array('id ='.$fiscal_year_id),1);
            $data['outlet']=Query_helper::get_info($this->config->item('table_login_csetup_cus_info'),'*',array('customer_id ='.$outlet_id,'revision =1'),1);
            $data['crop']=$crop;
            $data['dealers']=$this->get_dealers($outlet_id);
            $data['acres']=$this->get_acres($outlet_id,$crop_id);

            $data['system_preference_items']= $this->get_preference_headers($method);
            $data['title']="Yearly Budget for (".$data['crop']['name'].')';
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
        //dealers budget
        $results=Query_helper::get_info($this->config->item('table_pos_si_budget_target_dealer'),'*',array('fiscal_year_id ='.$fiscal_year_id,'outlet_id ='.$outlet_id));
        $budgeted_dealers=array();
        foreach($results as $result)
        {
            $budgeted_dealers[$result['dealer_id']][$result['variety_id']]=$result;
        }
        $fiscal_years_previous_sales=Query_helper::get_info($this->config->item('table_login_basic_setup_fiscal_year'),'*',array('id <'.$fiscal_year_id),Budget_helper::$NUM_FISCAL_YEAR_PREVIOUS_SALE,0,array('id DESC'));
        $sales_previous=$this->get_sales_previous_years_outlet($fiscal_years_previous_sales,$outlet_id);

        //old items
        $results=Query_helper::get_info($this->config->item('table_pos_si_budget_target_outlet'),'*',array('fiscal_year_id ='.$fiscal_year_id,'outlet_id ='.$outlet_id));
        $items_old=array();
        foreach($results as $result)
        {
            $items_old[$result['variety_id']]=$result;
        }

        //variety lists
        $results=Budget_helper::get_crop_type_varieties(array($crop_id));
        foreach($results as $result)
        {
            $info=$this->initialize_row_edit_budget_outlet($fiscal_years_previous_sales,$dealers,$result);
            foreach($fiscal_years_previous_sales as $fy)
            {
                if(isset($sales_previous[$fy['id']][$result['variety_id']]))
                {
                    $info['quantity_sale_'.$fy['id']]=$sales_previous[$fy['id']][$result['variety_id']]/1000;
                }
            }

            $quantity_budget_dealer_total=0;
            foreach($dealers as $dealer)
            {
                if(isset($budgeted_dealers[$dealer['farmer_id']][$result['variety_id']]))
                {
                    $info['quantity_budget_dealer_'.$dealer['farmer_id']]= $budgeted_dealers[$dealer['farmer_id']][$result['variety_id']]['quantity_budget'];
                    $quantity_budget_dealer_total+=$budgeted_dealers[$dealer['farmer_id']][$result['variety_id']]['quantity_budget'];
                }
            }
            $info['quantity_budget_dealer_total']= $quantity_budget_dealer_total;
            if(isset($items_old[$result['variety_id']]))
            {
                $info['quantity_budget']=$items_old[$result['variety_id']]['quantity_budget'];
            }
            $items[]=$info;
        }

        $this->json_return($items);
    }
    private function initialize_row_edit_budget_outlet($fiscal_years,$dealers,$info)
    {
        $row=$this->get_preference_headers('edit_budget_outlet');
        foreach($row  as $key=>$r)
        {
            $row[$key]=0;
        }
        $row['crop_type_name']=$info['crop_type_name'];
        $row['variety_name']=$info['variety_name'];
        $row['variety_id']=$info['variety_id'];
        foreach($fiscal_years as $fy)
        {
            $row['quantity_sale_'.$fy['id']]=0;
        }
        foreach($dealers as $dealer)
        {
            $info['quantity_budget_dealer_'.$dealer['farmer_id']]=0;
        }
        return $row;
    }
    private function system_save_budget_outlet()
    {
        $user = User_helper::get_user();
        $time=time();
        $item_head=$this->input->post('item');
        $items=$this->input->post('items');
        if(!((isset($this->permissions['action1']) && ($this->permissions['action1']==1))||(isset($this->permissions['action2']) && ($this->permissions['action2']==1))))
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
        //validation fiscal year
        if(!Budget_helper::check_validation_fiscal_year($item_head['fiscal_year_id']))
        {
            System_helper::invalid_try(__FUNCTION__,$item_head['fiscal_year_id'],'Invalid Fiscal year');
            $ajax['status']=false;
            $ajax['system_message']='Invalid Fiscal Year';
            $this->json_return($ajax);
        }
        //validation assigned outlet
        if(!in_array($item_head['outlet_id'], $this->user_outlet_ids))
        {
            System_helper::invalid_try(__FUNCTION__,$item_head['outlet_id'],'Outlet Not Assigned');
            $ajax['status']=false;
            $ajax['system_message']='Invalid Outlet.';
            $this->json_return($ajax);
        }
        //validation forward
        $info_budget=$this->get_info_budget_target($item_head['fiscal_year_id'],$item_head['outlet_id']);
        if(!(isset($this->permissions['action3']) && ($this->permissions['action3']==1)))
        {
            if(($info_budget['status_budget_forward']==$this->config->item('system_status_forwarded')))
            {
                $ajax['status']=false;
                $ajax['system_message']='Budget Already Forwarded.';
                $this->json_return($ajax);
            }
        }
        //old items
        $results=Query_helper::get_info($this->config->item('table_pos_si_budget_target_outlet'),'*',array('fiscal_year_id ='.$item_head['fiscal_year_id'],'outlet_id ='.$item_head['outlet_id']));
        $items_old=array();
        foreach($results as $result)
        {
            $items_old[$result['variety_id']]=$result;
        }
        $this->db->trans_start();  //DB Transaction Handle START
        foreach($items as $variety_id=>$quantity_budget)
        {
            if(isset($items_old[$variety_id]))
            {
                if($items_old[$variety_id]['quantity_budget']!=$quantity_budget)
                {
                    $data['quantity_budget']=$quantity_budget;
                    $data['date_updated_budget']=$time;
                    $data['user_updated_budget']=$user->user_id;
                    $this->db->set('revision_count_budget','revision_count_budget+1',false);
                    Query_helper::update($this->config->item('table_pos_si_budget_target_outlet'),$data,array('id='.$items_old[$variety_id]['id']),false);
                }
            }
            else
            {
                $data=array();
                $data['fiscal_year_id']=$item_head['fiscal_year_id'];
                $data['outlet_id']=$item_head['outlet_id'];
                $data['variety_id']=$variety_id;
                $data['quantity_budget']=0;
                if($quantity_budget>0)
                {
                    $data['quantity_budget']=$quantity_budget;
                    $data['revision_count_budget']=1;
                }
                $data['date_updated_budget'] = $time;
                $data['user_updated_budget'] = $user->user_id;
                Query_helper::add($this->config->item('table_pos_si_budget_target_outlet'),$data,false);
            }
        }
        $this->db->trans_complete();   //DB Transaction Handle END
        if ($this->db->trans_status() === TRUE)
        {
            $this->message=$this->lang->line("MSG_SAVED_SUCCESS");
            $this->system_list_budget_outlet($item_head['fiscal_year_id'],$item_head['outlet_id']);

        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("MSG_SAVED_FAIL");
            $this->json_return($ajax);
        }
    }

    /* Budget Forward*/
    private function system_budget_forward($fiscal_year_id=0,$outlet_id=0)
    {
        //$user = User_helper::get_user();
        $method='budget_forward';
        if(isset($this->permissions['action7'])&&($this->permissions['action7']==1))
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
                System_helper::invalid_try(__FUNCTION__,$fiscal_year_id,'Invalid Fiscal year');
                $ajax['status']=false;
                $ajax['system_message']='Invalid Fiscal Year';
                $this->json_return($ajax);
            }
            //validation assigned outlet
            if(!in_array($outlet_id, $this->user_outlet_ids))
            {
                System_helper::invalid_try(__FUNCTION__,$outlet_id,'Outlet Not Assigned');
                $ajax['status']=false;
                $ajax['system_message']='Invalid Outlet.';
                $this->json_return($ajax);
            }
            //validation forward
            $info_budget=$this->get_info_budget_target($fiscal_year_id,$outlet_id);
            if(($info_budget['status_budget_forward']==$this->config->item('system_status_forwarded')))
            {
                $ajax['status']=false;
                $ajax['system_message']='Budget Already Forwarded.';
                $this->json_return($ajax);
            }

            $results=Query_helper::get_info($this->config->item('table_pos_si_budget_target_dealer'),'*',array('fiscal_year_id ='.$fiscal_year_id,'outlet_id ='.$outlet_id));
            $budgeted_dealer_ids[0]=0;
            foreach($results as $result)
            {
                $budgeted_dealer_ids[$result['dealer_id']]=$result['dealer_id'];
            }

            $data['system_preference_items']= $this->get_preference_headers($method);
            $data['fiscal_years_previous_sales']=Query_helper::get_info($this->config->item('table_login_basic_setup_fiscal_year'),'*',array('id <'.$fiscal_year_id),Budget_helper::$NUM_FISCAL_YEAR_PREVIOUS_SALE,0,array('id DESC'));

            // get dealer list
            $this->db->from($this->config->item('table_pos_setup_farmer_outlet').' farmer_outlet');
            $this->db->select('farmer_outlet.farmer_id');
            $this->db->join($this->config->item('table_pos_setup_farmer_farmer').' farmer','farmer.id=farmer_outlet.farmer_id','INNER');
            $this->db->select('farmer.name farmer_name,farmer.mobile_no,farmer.status');
            //$this->db->where('farmer.farmer_type_id > ',1);
            $this->db->where('farmer_outlet.revision',1);
            $this->db->where_in('farmer_outlet.farmer_id',$budgeted_dealer_ids);
            $data['dealers']=$this->db->get()->result_array();

            $data['acres']=$this->get_acres($outlet_id);

            $data['fiscal_year_budget_target']=Query_helper::get_info($this->config->item('table_login_basic_setup_fiscal_year'),'*',array('id ='.$fiscal_year_id),1);
            $data['outlet']=Query_helper::get_info($this->config->item('table_login_csetup_cus_info'),'*',array('customer_id ='.$outlet_id,'revision =1'),1);
            $data['title']="Forward/Complete budget";
            $data['options']['fiscal_year_id']=$fiscal_year_id;
            $data['options']['outlet_id']=$outlet_id;

            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/budget_forward",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/budget_forward/'.$fiscal_year_id.'/'.$outlet_id);
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function system_get_items_budget_forward()
    {
        $items=array();
        $fiscal_year_id=$this->input->post('fiscal_year_id');
        $outlet_id=$this->input->post('outlet_id');

        $fiscal_years_previous_sales=Query_helper::get_info($this->config->item('table_login_basic_setup_fiscal_year'),'*',array('id <'.$fiscal_year_id),Budget_helper::$NUM_FISCAL_YEAR_PREVIOUS_SALE,0,array('id DESC'));
        $sales_previous=$this->get_sales_previous_years_outlet($fiscal_years_previous_sales,$outlet_id);

        //old items dealer
        $results=Query_helper::get_info($this->config->item('table_pos_si_budget_target_dealer'),'*',array('fiscal_year_id ='.$fiscal_year_id,'outlet_id ='.$outlet_id));
        $quantity_dealer_budgeted=array();
        $dealer_ids=array();
        foreach($results as $result)
        {
            $dealer_ids[$result['dealer_id']]=$result['dealer_id'];
            $quantity_dealer_budgeted[$result['dealer_id']][$result['variety_id']]=$result;
        }

        //old items outlet
        $results=Query_helper::get_info($this->config->item('table_pos_si_budget_target_outlet'),'*',array('fiscal_year_id ='.$fiscal_year_id,'outlet_id ='.$outlet_id));
        $quantity_outlet_budgeted=array();
        foreach($results as $result)
        {
            $quantity_outlet_budgeted[$result['variety_id']]=$result;
        }

        //variety lists
        $results=Budget_helper::get_crop_type_varieties();

        $prev_crop_name='';
        $prev_type_name='';
        $first_row=true;

        $type_total=$this->initialize_row_budget_forward($fiscal_years_previous_sales,$dealer_ids,'','','Total Type','');
        $crop_total=$this->initialize_row_budget_forward($fiscal_years_previous_sales,$dealer_ids,'','Total Crop','','');
        $grand_total=$this->initialize_row_budget_forward($fiscal_years_previous_sales,$dealer_ids,'Grand Total','','','');

        foreach($results as $result)
        {
            $info=$this->initialize_row_budget_forward($fiscal_years_previous_sales,$dealer_ids,$result['crop_name'],$result['crop_type_name'],$result['variety_name']);

            if(!$first_row)
            {
                if($prev_crop_name!=$result['crop_name'])
                {
                    $type_total['crop_name']=$prev_crop_name;
                    $type_total['crop_type_name']=$prev_type_name;
                    $crop_total['crop_name']=$prev_crop_name;
                    $items[]=$type_total;
                    $items[]=$crop_total;

                    $type_total=$this->reset_row($type_total);
                    $crop_total=$this->reset_row($crop_total);

                    $prev_crop_name=$result['crop_name'];
                    $prev_type_name=$result['crop_type_name'];

                }
                elseif($prev_type_name!=$result['crop_type_name'])
                {
                    $type_total['crop_name']=$prev_crop_name;
                    $type_total['crop_type_name']=$prev_type_name;
                    $items[]=$type_total;
                    $type_total=$this->reset_row($type_total);
                    //$info['crop_name']='';
                    $prev_type_name=$result['crop_type_name'];
                }
                else
                {
                    //$info['crop_name']='';
                    //$info['crop_type_name']='';
                }
            }
            else
            {
                $prev_crop_name=$result['crop_name'];
                $prev_type_name=$result['crop_type_name'];
                $first_row=false;
            }
            foreach($fiscal_years_previous_sales as $fy)
            {
                if(isset($sales_previous[$fy['id']][$result['variety_id']]))
                {
                    $info['quantity_sale_'.$fy['id']]=$sales_previous[$fy['id']][$result['variety_id']]/1000;
                }
            }
            if(isset($quantity_outlet_budgeted[$result['variety_id']]))
            {
                $info['quantity_budget_outlet']=$quantity_outlet_budgeted[$result['variety_id']]['quantity_budget'];
            }
            $quantity_budget_dealer_total=0;
            foreach($dealer_ids as $dealer_id)
            {
                if(isset($quantity_dealer_budgeted[$dealer_id][$result['variety_id']]))
                {
                    $info['quantity_budget_dealer_'.$dealer_id]=$quantity_dealer_budgeted[$dealer_id][$result['variety_id']]['quantity_budget'];
                    $quantity_budget_dealer_total+=$info['quantity_budget_dealer_'.$dealer_id];
                }
            }
            $info['quantity_budget_dealer_total']=$quantity_budget_dealer_total;

            foreach($info as $key=>$r)
            {
                if(!(($key=='crop_name')||($key=='crop_type_name')||($key=='variety_name')||($key=='pack_size')))
                {
                    $type_total[$key]+=$info[$key];
                    $crop_total[$key]+=$info[$key];
                    $grand_total[$key]+=$info[$key];
                }
            }

            $items[]=$info;
        }

        $items[]=$type_total;
        $items[]=$crop_total;
        $items[]=$grand_total;
        $this->json_return($items);

    }
    private function initialize_row_budget_forward($fiscal_years,$dealer_ids,$crop_name,$crop_type_name,$variety_name)
    {
        $row=$this->get_preference_headers('budget_forward');
        foreach($row  as $key=>$r)
        {
            $row[$key]=0;
        }
        $row['crop_name']=$crop_name;
        $row['crop_type_name']=$crop_type_name;
        $row['variety_name']=$variety_name;
        foreach($fiscal_years as $fy)
        {
            $row['quantity_sale_'.$fy['id']]=0;
        }
        foreach($dealer_ids as $dealer_id)
        {
            $row['quantity_budget_dealer_'.$dealer_id]= 0;
        }
        return $row;
    }
    private function system_save_forward_budget()
    {
        $user = User_helper::get_user();
        $time=time();
        $item_head=$this->input->post('item');
        if(!((isset($this->permissions['action7']) && ($this->permissions['action7']==1))))
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
        if($item_head['status_budget_forward']!=$this->config->item('system_status_forwarded'))
        {
            $ajax['status']=false;
            $ajax['system_message']='Select Forward Option.';
            $this->json_return($ajax);
        }
        //validation fiscal year
        if(!Budget_helper::check_validation_fiscal_year($item_head['fiscal_year_id']))
        {
            System_helper::invalid_try(__FUNCTION__,$item_head['fiscal_year_id'],'Invalid Fiscal year');
            $ajax['status']=false;
            $ajax['system_message']='Invalid Fiscal Year';
            $this->json_return($ajax);
        }
        //validation assigned outlet
        if(!in_array($item_head['outlet_id'], $this->user_outlet_ids))
        {
            System_helper::invalid_try(__FUNCTION__,$item_head['outlet_id'],'Outlet Not Assigned');
            $ajax['status']=false;
            $ajax['system_message']='Invalid Outlet.';
            $this->json_return($ajax);
        }
        //validation forward
        $info_budget=$this->get_info_budget_target($item_head['fiscal_year_id'],$item_head['outlet_id']);
        if(($info_budget['status_budget_forward']==$this->config->item('system_status_forwarded')))
        {
            $ajax['status']=false;
            $ajax['system_message']='Budget Already Forwarded.';
            $this->json_return($ajax);
        }

        $this->db->trans_start();  //DB Transaction Handle START

        $data=array();
        $data['status_budget_forward']=$item_head['status_budget_forward'];
        $data['date_budget_forwarded']=$time;
        $data['user_budget_forwarded']=$user->user_id;
        Query_helper::update($this->config->item('table_pos_si_budget_target'),$data,array('id='.$info_budget['id']),false);

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

    /* Dealer Target*/
    private function system_list_assign_target_dealer($fiscal_year_id=0,$outlet_id=0)
    {
        //$user = User_helper::get_user();
        $method='list_target_dealer';
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
                System_helper::invalid_try(__FUNCTION__,$fiscal_year_id,'Invalid Fiscal year');
                $ajax['status']=false;
                $ajax['system_message']='Invalid Fiscal Year';
                $this->json_return($ajax);
            }
            //validation assigned outlet
            if(!in_array($outlet_id, $this->user_outlet_ids))
            {
                System_helper::invalid_try(__FUNCTION__,$outlet_id,'Outlet Not Assigned');
                $ajax['status']=false;
                $ajax['system_message']='Invalid Outlet.';
                $this->json_return($ajax);
            }
            //validation forward
            $info_target=$this->get_info_budget_target($fiscal_year_id,$outlet_id);
            if(!(isset($this->permissions['action3']) && ($this->permissions['action3']==1)))
            {
                if(($info_target['status_target_dealer_forward']==$this->config->item('system_status_forwarded')))
                {
                    $ajax['status']=false;
                    $ajax['system_message']='Dealer Target Already Assigned.';
                    $this->json_return($ajax);
                }

                $info_target_zi=$this->get_info_target_zi($fiscal_year_id, $info_target['zone_id']);
                if(($info_target_zi['status_target_outlet_forward']!=$this->config->item('system_status_forwarded')))
                {
                    $ajax['status']=false;
                    $ajax['system_message']='Outlet Assign Target Not Forwarded From ZSC.';
                    $this->json_return($ajax);
                }
            }

            $data['system_preference_items']= $this->get_preference_headers($method);
            $data['fiscal_year']=Query_helper::get_info($this->config->item('table_login_basic_setup_fiscal_year'),'*',array('id ='.$fiscal_year_id),1);
            $data['outlet']=Query_helper::get_info($this->config->item('table_login_csetup_cus_info'),'*',array('customer_id ='.$outlet_id,'revision =1'),1);
            $data['title']="Yearly Target Dealer list";
            $data['options']['fiscal_year_id']=$fiscal_year_id;
            $data['options']['outlet_id']=$outlet_id;
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/list_assign_target_dealer",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/list_assign_target_dealer/'.$fiscal_year_id.'/'.$outlet_id);
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function system_get_items_list_assign_target_dealer()
    {
        $items=array();
        //$this->json_return($items);
        $fiscal_year_id=$this->input->post('fiscal_year_id');
        $outlet_id=$this->input->post('outlet_id');

        $this->db->from($this->config->item('table_pos_si_budget_target_dealer').' budget_target_dealer');
        $this->db->select('budget_target_dealer.dealer_id');
        //$this->db->select('SUM(CASE WHEN budget_target_dealer.quantity_target>0 then 1 ELSE 0 END) number_of_variety_targeted',false);
        $this->db->select('COUNT(DISTINCT budget_target_dealer.variety_id) number_of_variety_targeted',false);
        $this->db->join($this->config->item('table_login_setup_classification_varieties').' v','v.id = budget_target_dealer.variety_id','INNER');
        $this->db->join($this->config->item('table_login_setup_classification_crop_types').' crop_type','crop_type.id = v.crop_type_id','INNER');
        $this->db->select('crop_type.crop_id');
        $this->db->where('budget_target_dealer.fiscal_year_id',$fiscal_year_id);
        $this->db->where('budget_target_dealer.outlet_id',$outlet_id);
        $this->db->where('budget_target_dealer.quantity_target > ',0);
        $this->db->group_by('crop_type.crop_id');
        $results=$this->db->get()->result_array();
        $targeted=array();
        foreach($results as $result)
        {
            $targeted[$result['crop_id']]=$result['number_of_variety_targeted'];
        }
        $varieties=Budget_helper::get_crop_type_varieties();
        $crops=array();
        foreach($varieties as $variety)
        {
            $crops[$variety['crop_id']]['crop_id']=$variety['crop_id'];
            $crops[$variety['crop_id']]['crop_name']=$variety['crop_name'];
            if(isset($crops[$variety['crop_id']]['number_of_variety_active']))
            {
                $crops[$variety['crop_id']]['number_of_variety_active']+=1;
            }
            else
            {
                $crops[$variety['crop_id']]['number_of_variety_active']=1;
            }
        }
        foreach($crops as $crop)
        {
            $item=$crop;
            $item['number_of_variety_active']=$crop['number_of_variety_active'];
            $item['number_of_variety_targeted']=0;
            $item['number_of_variety_target_due']=0;
            if(isset($targeted[$crop['crop_id']]))
            {
                $item['number_of_variety_targeted']=$targeted[$crop['crop_id']];
            }
            $item['number_of_variety_target_due']=($item['number_of_variety_active']-$item['number_of_variety_targeted']);
            $items[]=$item;
        }
        $this->json_return($items);
    }
    private function system_assign_target_dealer($fiscal_year_id=0,$outlet_id,$crop_id=0)
    {
        //$user = User_helper::get_user();
        $method='assign_target_dealer';
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
                System_helper::invalid_try(__FUNCTION__,$fiscal_year_id,'Invalid Fiscal year');
                $ajax['status']=false;
                $ajax['system_message']='Invalid Fiscal Year';
                $this->json_return($ajax);
            }
            // validation assign division
            if(!in_array($outlet_id, $this->user_outlet_ids))
            {
                System_helper::invalid_try(__FUNCTION__,$outlet_id,'Outlet Not Assigned');
                $ajax['status']=false;
                $ajax['system_message']='Invalid Zone.';
                $this->json_return($ajax);
            }
            // valid crop check
            $crop=Query_helper::get_info($this->config->item('table_login_setup_classification_crops'),'*',array('id ='.$crop_id),1);
            if(!$crop)
            {
                $ajax['status']=false;
                $ajax['system_message']='Wrong crop id.';
                $this->json_return($ajax);
            }
            //validation ZSC Budget & Outlet Target forward status
            $info_target=$this->get_info_budget_target($fiscal_year_id,$outlet_id);
            if(!(isset($this->permissions['action3']) && ($this->permissions['action3']==1)))
            {
                if(($info_target['status_target_dealer_forward']==$this->config->item('system_status_forwarded')))
                {
                    $ajax['status']=false;
                    $ajax['system_message']='Dealer Target Already Assigned.';
                    $this->json_return($ajax);
                }

                $info_target_zi=$this->get_info_target_zi($fiscal_year_id, $info_target['zone_id']);
                if(($info_target_zi['status_target_outlet_forward']!=$this->config->item('system_status_forwarded')))
                {
                    $ajax['status']=false;
                    $ajax['system_message']='Outlet Assign Target Not Forwarded From ZSC.';
                    $this->json_return($ajax);
                }
            }

            $data['fiscal_year']=Query_helper::get_info($this->config->item('table_login_basic_setup_fiscal_year'),'*',array('id ='.$fiscal_year_id),1);
            $data['outlet']=Query_helper::get_info($this->config->item('table_login_csetup_cus_info'),'*',array('customer_id ='.$outlet_id,'revision =1'),1);
            $data['crop']=$crop;
            $data['dealers']=$this->get_dealers($outlet_id);
            $data['acres']=$this->get_acres($outlet_id,$crop_id);

            $data['system_preference_items']= $this->get_preference_headers($method);
            $data['title']="Outlet Yearly Target Assign To Dealer for (".$data['crop']['name'].')';
            $data['options']['fiscal_year_id']=$fiscal_year_id;
            $data['options']['outlet_id']=$outlet_id;
            $data['options']['crop_id']=$crop_id;
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/assign_target_dealer",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/assign_target_dealer/'.$fiscal_year_id.'/'.$outlet_id.'/'.$crop_id);
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function system_get_items_assign_target_dealer()
    {
        $items=array();
        //$this->json_return($items);
        $fiscal_year_id=$this->input->post('fiscal_year_id');
        $outlet_id=$this->input->post('outlet_id');
        $crop_id=$this->input->post('crop_id');

        //get zone target
        $this->db->from($this->config->item('table_pos_si_budget_target_outlet').' budget_target_outlet');
        $this->db->select('budget_target_outlet.*');

        $this->db->join($this->config->item('table_login_setup_classification_varieties').' v','v.id=budget_target_outlet.variety_id','INNER');
        $this->db->join($this->config->item('table_login_setup_classification_crop_types').' crop_type','crop_type.id = v.crop_type_id','INNER');

        $this->db->where('crop_type.crop_id',$crop_id);
        //$this->db->where('v.status',$this->config->item('system_status_active'));
        $this->db->where('v.whose','ARM');
        $this->db->where('budget_target_outlet.fiscal_year_id',$fiscal_year_id);
        $this->db->where('budget_target_outlet.outlet_id',$outlet_id);
        $results=$this->db->get()->result_array();
        $budget_target_info=array();
        foreach($results as $result)
        {
            $budget_target_info[$result['variety_id']]=$result;
        }

        $dealer_ids[0]=0;
        $dealers=$this->get_dealers($outlet_id);
        foreach ($dealers as $dealer)
        {
            $dealer_ids[$dealer['farmer_id']]=$dealer['farmer_id'];
        }

        // get old items
        $this->db->from($this->config->item('table_pos_si_budget_target_dealer').' budget_target_dealer');
        $this->db->select('budget_target_dealer.*');
        $this->db->where('budget_target_dealer.fiscal_year_id',$fiscal_year_id);
        $this->db->where_in('budget_target_dealer.dealer_id',$dealer_ids);
        $results=$this->db->get()->result_array();
        $items_old=array();
        foreach($results as $result)
        {
            $items_old[$result['dealer_id']][$result['variety_id']]=$result;
        }


        //variety lists
        $results=Budget_helper::get_crop_type_varieties(array($crop_id));
        foreach($results as $result)
        {
            $info=$this->initialize_row_assign_target_dealer($dealers,$result);
            if(isset($budget_target_info[$result['variety_id']]))
            {
                $info['quantity_budget_outlet']=$budget_target_info[$result['variety_id']]['quantity_budget'];
                $info['quantity_target_outlet']=$budget_target_info[$result['variety_id']]['quantity_target'];
            }
            $quantity_target_outlet_total=0;
            foreach($dealers as $dealer)
            {
                if(isset($items_old[$dealer['farmer_id']][$result['variety_id']]))
                {
                    $info['quantity_budget_dealer_'.$dealer['farmer_id']]=$items_old[$dealer['farmer_id']][$result['variety_id']]['quantity_budget'];
                    $info['quantity_target_dealer_'.$dealer['farmer_id']]=$items_old[$dealer['farmer_id']][$result['variety_id']]['quantity_target'];
                    $quantity_target_outlet_total+=$info['quantity_target_dealer_'.$dealer['farmer_id']];
                }
            }
            $info['quantity_target_dealer_total']= $quantity_target_outlet_total;
            $items[]=$info;
        }
        $this->json_return($items);
    }
    private function initialize_row_assign_target_dealer($dealers,$info)
    {
        $row=$this->get_preference_headers('assign_target_dealer');
        foreach($row  as $key=>$r)
        {
            $row[$key]=0;
        }
        $row['crop_type_name']=$info['crop_type_name'];
        $row['variety_name']=$info['variety_name'];
        $row['variety_id']=$info['variety_id'];
        foreach($dealers as $dealer)
        {
            $row['quantity_budget_dealer_'.$dealer['farmer_id']]= 0;
            $row['quantity_target_dealer_'.$dealer['farmer_id']]= 0;
        }
        return $row;
    }
    private function system_save_assign_target_dealer()
    {
        $user = User_helper::get_user();
        $time=time();
        $item_head=$this->input->post('item');
        $items=$this->input->post('items');

        if(!((isset($this->permissions['action1']) && ($this->permissions['action1']==1))||(isset($this->permissions['action2']) && ($this->permissions['action2']==1))))
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
        //validation fiscal year
        if(!Budget_helper::check_validation_fiscal_year($item_head['fiscal_year_id']))
        {
            System_helper::invalid_try(__FUNCTION__,$item_head['fiscal_year_id'],'Invalid Fiscal year');
            $ajax['status']=false;
            $ajax['system_message']='Invalid Fiscal Year';
            $this->json_return($ajax);
        }
        // validation assign zone
        if(!in_array($item_head['outlet_id'], $this->user_outlet_ids))
        {
            System_helper::invalid_try(__FUNCTION__,$item_head['outlet_id'],'Outlet Not Assigned');
            $ajax['status']=false;
            $ajax['system_message']='Invalid Zone.';
            $this->json_return($ajax);
        }
        //validation DI Budget & ZSC Target forward status
        $info_target=$this->get_info_budget_target($item_head['fiscal_year_id'],$item_head['outlet_id']);
        if(!(isset($this->permissions['action3']) && ($this->permissions['action3']==1)))
        {
            if(($info_target['status_target_dealer_forward']==$this->config->item('system_status_forwarded')))
            {
                $ajax['status']=false;
                $ajax['system_message']='Dealer Target Already Assigned.';
                $this->json_return($ajax);
            }

            $info_target_zi=$this->get_info_target_zi($item_head['fiscal_year_id'], $info_target['zone_id']);
            if(($info_target_zi['status_target_outlet_forward']!=$this->config->item('system_status_forwarded')))
            {
                $ajax['status']=false;
                $ajax['system_message']='Outlet Assign Target Not Forwarded From ZSC.';
                $this->json_return($ajax);
            }
        }
        // get dealer ids
        /*$dealer_ids[0]=0;
        $dealers=$this->get_dealers($item_head['outlet_id']);
        foreach ($dealers as $dealer)
        {
            $dealer_ids[$dealer['farmer_id']]=$dealer['farmer_id'];
        }*/

        //old items
        $this->db->from($this->config->item('table_pos_si_budget_target_dealer').' budget_target_dealer');
        $this->db->select('budget_target_dealer.*');

        $this->db->where('budget_target_dealer.fiscal_year_id',$item_head['fiscal_year_id']);
        $this->db->where('budget_target_dealer.outlet_id',$item_head['outlet_id']);
        //$this->db->where_in('budget_target_dealer.dealer_id',$dealer_ids);

        $results=$this->db->get()->result_array();
        $items_old=array();
        foreach($results as $result)
        {
            $items_old[$result['dealer_id']][$result['variety_id']]=$result;
        }

        $this->db->trans_start();  //DB Transaction Handle START

        foreach($items as $variety_id=>$variety_info)
        {
            foreach($variety_info as $dealer_id=>$quantity_info)
            {
                if(isset($items_old[$dealer_id][$variety_id]))
                {
                    if($items_old[$dealer_id][$variety_id]['quantity_target']!=$quantity_info['quantity_target'])
                    {
                        $data=array();
                        $data['quantity_target']=$quantity_info['quantity_target'];
                        $data['date_updated_target']=$time;
                        $data['user_updated_target']=$user->user_id;
                        $this->db->set('revision_count_target','revision_count_target+1',false);
                        Query_helper::update($this->config->item('table_pos_si_budget_target_dealer'),$data,array('id='.$items_old[$dealer_id][$variety_id]['id']),false);
                    }
                }
                else
                {
                    $data=array();
                    $data['fiscal_year_id']=$item_head['fiscal_year_id'];
                    $data['outlet_id']=$item_head['outlet_id'];
                    $data['dealer_id']=$dealer_id;
                    $data['variety_id']=$variety_id;
                    $data['quantity_target']=0;
                    if($quantity_info['quantity_target']>0)
                    {
                        $data['quantity_target']=$quantity_info['quantity_target'];
                        $data['revision_count_target']=1;
                    }
                    $data['date_updated_target']=$time;
                    $data['user_updated_target']=$user->user_id;
                    Query_helper::add($this->config->item('table_pos_si_budget_target_dealer'),$data,false);
                }
            }
        }
        $this->db->trans_complete();   //DB Transaction Handle END
        if ($this->db->trans_status() === TRUE)
        {
            $this->message=$this->lang->line("MSG_SAVED_SUCCESS");
            $this->system_list_assign_target_dealer($item_head['fiscal_year_id'],$item_head['outlet_id']);

        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("MSG_SAVED_FAIL");
            $this->json_return($ajax);
        }
    }

    /* Dealer Target Forward */
    private function system_assign_target_dealer_forward($fiscal_year_id=0,$outlet_id=0)
    {
        //$user = User_helper::get_user();
        $method='target_dealer_forward';
        if(isset($this->permissions['action7'])&&($this->permissions['action7']==1))
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
                System_helper::invalid_try(__FUNCTION__,$fiscal_year_id,'Invalid Fiscal year');
                $ajax['status']=false;
                $ajax['system_message']='Invalid Fiscal Year';
                $this->json_return($ajax);
            }
            //validation assigned outlet
            if(!in_array($outlet_id, $this->user_outlet_ids))
            {
                System_helper::invalid_try(__FUNCTION__,$outlet_id,'Outlet Not Assigned');
                $ajax['status']=false;
                $ajax['system_message']='Invalid Outlet.';
                $this->json_return($ajax);
            }
            //validation forward
            $info_target=$this->get_info_budget_target($fiscal_year_id,$outlet_id);
            if(($info_target['status_target_dealer_forward']==$this->config->item('system_status_forwarded')))
            {
                $ajax['status']=false;
                $ajax['system_message']='Dealer Target Already Assigned.';
                $this->json_return($ajax);
            }
            if(!(isset($this->permissions['action3']) && ($this->permissions['action3']==1)))
            {
                $info_target_zi=$this->get_info_target_zi($fiscal_year_id, $info_target['zone_id']);
                if(($info_target_zi['status_target_outlet_forward']!=$this->config->item('system_status_forwarded')))
                {
                    $ajax['status']=false;
                    $ajax['system_message']='Outlet Assign Target Not Forwarded From ZSC.';
                    $this->json_return($ajax);
                }
            }

            $results=Query_helper::get_info($this->config->item('table_pos_si_budget_target_dealer'),'*',array('fiscal_year_id ='.$fiscal_year_id,'outlet_id ='.$outlet_id));
            $targeted_dealer_ids[0]=0;
            foreach($results as $result)
            {
                $targeted_dealer_ids[$result['dealer_id']]=$result['dealer_id'];
            }

            $data['system_preference_items']= $this->get_preference_headers($method);
            $data['fiscal_years_previous_sales']=Query_helper::get_info($this->config->item('table_login_basic_setup_fiscal_year'),'*',array('id <'.$fiscal_year_id),Budget_helper::$NUM_FISCAL_YEAR_PREVIOUS_SALE,0,array('id DESC'));

            // get dealer list
            $this->db->from($this->config->item('table_pos_setup_farmer_outlet').' farmer_outlet');
            $this->db->select('farmer_outlet.farmer_id');
            $this->db->join($this->config->item('table_pos_setup_farmer_farmer').' farmer','farmer.id=farmer_outlet.farmer_id AND farmer_outlet.revision=1','INNER');
            $this->db->select('farmer.name farmer_name,farmer.mobile_no,farmer.status');
            //$this->db->where('farmer.farmer_type_id > ',1);
            $this->db->where_in('farmer_outlet.farmer_id',$targeted_dealer_ids);
            $data['dealers']=$this->db->get()->result_array();

            $data['acres']=$this->get_acres($outlet_id);

            $data['fiscal_year_budget_target']=Query_helper::get_info($this->config->item('table_login_basic_setup_fiscal_year'),'*',array('id ='.$fiscal_year_id),1);
            $data['outlet']=Query_helper::get_info($this->config->item('table_login_csetup_cus_info'),'*',array('customer_id ='.$outlet_id,'revision =1'),1);
            $data['title']="Forward/Complete budget";
            $data['options']['fiscal_year_id']=$fiscal_year_id;
            $data['options']['outlet_id']=$outlet_id;

            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/assign_target_dealer_forward",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/assign_target_dealer_forward/'.$fiscal_year_id.'/'.$outlet_id);
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function system_get_items_assign_target_dealer_forward()
    {
        $items=array();
        $fiscal_year_id=$this->input->post('fiscal_year_id');
        $outlet_id=$this->input->post('outlet_id');

        $fiscal_years_previous_sales=Query_helper::get_info($this->config->item('table_login_basic_setup_fiscal_year'),'*',array('id <'.$fiscal_year_id),Budget_helper::$NUM_FISCAL_YEAR_PREVIOUS_SALE,0,array('id DESC'));
        $sales_previous=$this->get_sales_previous_years_outlet($fiscal_years_previous_sales,$outlet_id);

        //old items dealer
        $results=Query_helper::get_info($this->config->item('table_pos_si_budget_target_dealer'),'*',array('fiscal_year_id ='.$fiscal_year_id,'outlet_id ='.$outlet_id));
        $quantity_dealer_targeted=array();
        $dealer_ids=array();
        foreach($results as $result)
        {
            $dealer_ids[$result['dealer_id']]=$result['dealer_id'];
            $quantity_dealer_targeted[$result['dealer_id']][$result['variety_id']]=$result;
        }

        //old items outlet
        $results=Query_helper::get_info($this->config->item('table_pos_si_budget_target_outlet'),'*',array('fiscal_year_id ='.$fiscal_year_id,'outlet_id ='.$outlet_id));
        $quantity_outlet_targeted=array();
        foreach($results as $result)
        {
            $quantity_outlet_targeted[$result['variety_id']]=$result;
        }

        //variety lists
        $results=Budget_helper::get_crop_type_varieties();

        $prev_crop_name='';
        $prev_type_name='';
        $first_row=true;

        $type_total=$this->initialize_row_assign_budget_forward($fiscal_years_previous_sales,$dealer_ids,'','','Total Type','');
        $crop_total=$this->initialize_row_assign_budget_forward($fiscal_years_previous_sales,$dealer_ids,'','Total Crop','','');
        $grand_total=$this->initialize_row_assign_budget_forward($fiscal_years_previous_sales,$dealer_ids,'Grand Total','','','');

        foreach($results as $result)
        {
            $info=$this->initialize_row_assign_budget_forward($fiscal_years_previous_sales,$dealer_ids,$result['crop_name'],$result['crop_type_name'],$result['variety_name']);

            if(!$first_row)
            {
                if($prev_crop_name!=$result['crop_name'])
                {
                    $type_total['crop_name']=$prev_crop_name;
                    $type_total['crop_type_name']=$prev_type_name;
                    $crop_total['crop_name']=$prev_crop_name;
                    $items[]=$type_total;
                    $items[]=$crop_total;

                    $type_total=$this->reset_row($type_total);
                    $crop_total=$this->reset_row($crop_total);

                    $prev_crop_name=$result['crop_name'];
                    $prev_type_name=$result['crop_type_name'];

                }
                elseif($prev_type_name!=$result['crop_type_name'])
                {
                    $type_total['crop_name']=$prev_crop_name;
                    $type_total['crop_type_name']=$prev_type_name;
                    $items[]=$type_total;
                    $type_total=$this->reset_row($type_total);
                    //$info['crop_name']='';
                    $prev_type_name=$result['crop_type_name'];
                }
                else
                {
                    //$info['crop_name']='';
                    //$info['crop_type_name']='';
                }
            }
            else
            {
                $prev_crop_name=$result['crop_name'];
                $prev_type_name=$result['crop_type_name'];
                $first_row=false;
            }
            foreach($fiscal_years_previous_sales as $fy)
            {
                if(isset($sales_previous[$fy['id']][$result['variety_id']]))
                {
                    $info['quantity_sale_'.$fy['id']]=$sales_previous[$fy['id']][$result['variety_id']]/1000;
                }
            }
            if(isset($quantity_outlet_targeted[$result['variety_id']]))
            {
                $info['quantity_budget_outlet']=$quantity_outlet_targeted[$result['variety_id']]['quantity_budget'];
            }
            $quantity_budget_dealer_total=0;
            foreach($dealer_ids as $dealer_id)
            {
                if(isset($quantity_dealer_targeted[$dealer_id][$result['variety_id']]))
                {
                    $info['quantity_budget_dealer_'.$dealer_id]=$quantity_dealer_targeted[$dealer_id][$result['variety_id']]['quantity_budget'];
                    $quantity_budget_dealer_total+=$info['quantity_budget_dealer_'.$dealer_id];
                }
            }
            $info['quantity_budget_dealer_total']=$quantity_budget_dealer_total;

            foreach($info as $key=>$r)
            {
                if(!(($key=='crop_name')||($key=='crop_type_name')||($key=='variety_name')||($key=='pack_size')))
                {
                    $type_total[$key]+=$info[$key];
                    $crop_total[$key]+=$info[$key];
                    $grand_total[$key]+=$info[$key];
                }
            }

            $items[]=$info;
        }

        $items[]=$type_total;
        $items[]=$crop_total;
        $items[]=$grand_total;
        $this->json_return($items);

    }
    private function initialize_row_assign_budget_forward($fiscal_years,$dealer_ids,$crop_name,$crop_type_name,$variety_name)
    {
        $row=$this->get_preference_headers('budget_forward');
        foreach($row  as $key=>$r)
        {
            $row[$key]=0;
        }
        $row['crop_name']=$crop_name;
        $row['crop_type_name']=$crop_type_name;
        $row['variety_name']=$variety_name;
        foreach($fiscal_years as $fy)
        {
            $row['quantity_sale_'.$fy['id']]=0;
        }
        foreach($dealer_ids as $dealer_id)
        {
            $row['quantity_budget_dealer_'.$dealer_id]= 0;
        }
        return $row;
    }
    private function system_save_assign_target_dealer_forward()
    {
        $user = User_helper::get_user();
        $time=time();
        $item_head=$this->input->post('item');
        if(!((isset($this->permissions['action7']) && ($this->permissions['action7']==1))))
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
        if($item_head['status_target_dealer_forward']!=$this->config->item('system_status_forwarded'))
        {
            $ajax['status']=false;
            $ajax['system_message']='Select Forward Option.';
            $this->json_return($ajax);
        }
        //validation fiscal year
        if(!Budget_helper::check_validation_fiscal_year($item_head['fiscal_year_id']))
        {
            System_helper::invalid_try(__FUNCTION__,$item_head['fiscal_year_id'],'Invalid Fiscal year');
            $ajax['status']=false;
            $ajax['system_message']='Invalid Fiscal Year';
            $this->json_return($ajax);
        }
        //validation assigned outlet
        if(!in_array($item_head['outlet_id'], $this->user_outlet_ids))
        {
            System_helper::invalid_try(__FUNCTION__,$item_head['outlet_id'],'Outlet Not Assigned');
            $ajax['status']=false;
            $ajax['system_message']='Invalid Outlet.';
            $this->json_return($ajax);
        }
        //validation forward
        $info_target=$this->get_info_budget_target($item_head['fiscal_year_id'],$item_head['outlet_id']);
        if(($info_target['status_target_dealer_forward']==$this->config->item('system_status_forwarded')))
        {
            $ajax['status']=false;
            $ajax['system_message']='Dealer Target Already Assigned.';
            $this->json_return($ajax);
        }
        if(!(isset($this->permissions['action3']) && ($this->permissions['action3']==1)))
        {
            $info_target_zi=$this->get_info_target_zi($item_head['fiscal_year_id'], $info_target['zone_id']);
            if(($info_target_zi['status_target_outlet_forward']!=$this->config->item('system_status_forwarded')))
            {
                $ajax['status']=false;
                $ajax['system_message']='Outlet Assign Target Not Forwarded From ZSC.';
                $this->json_return($ajax);
            }
        }

        $this->db->trans_start();  //DB Transaction Handle START

        $data=array();
        $data['status_target_dealer_forward']=$item_head['status_target_dealer_forward'];
        $data['date_target_dealer_forwarded']=$time;
        $data['user_target_dealer_forwarded']=$user->user_id;
        Query_helper::update($this->config->item('table_pos_si_budget_target'),$data,array('id='.$info_target['id']),false);

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

    private function reset_row($info)
    {
        foreach($info as $key=>$r)
        {
            if(!(($key=='crop_name')||($key=='crop_type_name')||($key=='variety_name')))
            {
                $info[$key]=0;
            }
        }
        return $info;
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
    private function get_sales_previous_years_dealers($fiscal_years,$dealer_ids)
    {
        $sales=array();
        foreach($fiscal_years as $fy)
        {
            $this->db->from($this->config->item('table_pos_sale_details').' details');
            $this->db->select('details.variety_id');
            $this->db->select('SUM(details.pack_size*details.quantity) quantity_sale');
            $this->db->join($this->config->item('table_pos_sale').' sale','sale.id = details.sale_id','INNER');
            $this->db->select('sale.farmer_id');

            $this->db->where('sale.date_sale >=',$fy['date_start']);
            $this->db->where('sale.date_sale <=',$fy['date_end']);
            $this->db->where('sale.status',$this->config->item('system_status_active'));
            $this->db->where_in('sale.farmer_id',$dealer_ids);
            $this->db->group_by('sale.farmer_id');
            $this->db->group_by('details.variety_id');
            $results=$this->db->get()->result_array();
            foreach($results as $result)
            {
                $sales[$fy['id']][$result['farmer_id']][$result['variety_id']]=$result['quantity_sale'];
            }
        }
        return $sales;
    }
    private function get_sales_previous_years_outlet($fiscal_years,$outlet_id)
    {
        $sales=array();
        foreach($fiscal_years as $fy)
        {
            $this->db->from($this->config->item('table_pos_sale_details').' details');
            $this->db->select('details.variety_id');
            $this->db->select('SUM(details.pack_size*details.quantity) quantity_sale');
            $this->db->join($this->config->item('table_pos_sale').' sale','sale.id = details.sale_id','INNER');
            $this->db->select('sale.outlet_id');

            $this->db->where('sale.date_sale >=',$fy['date_start']);
            $this->db->where('sale.date_sale <=',$fy['date_end']);
            $this->db->where('sale.status',$this->config->item('system_status_active'));
            $this->db->where('sale.outlet_id',$outlet_id);
            $this->db->group_by('details.variety_id');
            $results=$this->db->get()->result_array();
            foreach($results as $result)
            {
                $sales[$fy['id']][$result['variety_id']]=$result['quantity_sale'];
            }
        }
        return $sales;

    }
    private function get_info_budget_target($fiscal_year_id,$outlet_id)
    {
        //$info=Query_helper::get_info($this->config->item('table_pos_si_budget_target'),'*',array('fiscal_year_id ='.$fiscal_year_id,'outlet_id ='.$outlet_id),1);
        $this->db->from($this->config->item('table_pos_si_budget_target').' item');
        $this->db->select('item.*');
        $this->db->join($this->config->item('table_login_csetup_cus_info').' cus_info','cus_info.customer_id = item.outlet_id AND cus_info.revision = 1','INNER');
        $this->db->join($this->config->item('table_login_setup_location_districts').' district','district.id = cus_info.district_id','INNER');
        $this->db->join($this->config->item('table_login_setup_location_territories').' territory','territory.id = district.territory_id','INNER');
        $this->db->select('territory.zone_id');
        $this->db->where('item.fiscal_year_id',$fiscal_year_id);
        $this->db->where('item.outlet_id',$outlet_id);
        $info=$this->db->get()->row_array();
        if(!$info)
        {
            $user = User_helper::get_user();
            $data=array();
            $data['fiscal_year_id'] = $fiscal_year_id;
            $data['outlet_id'] = $outlet_id;
            $data['date_created'] = time();
            $data['user_created'] = $user->user_id;
            $id=Query_helper::add($this->config->item('table_pos_si_budget_target'),$data,false);
            //$info=Query_helper::get_info($this->config->item('table_pos_si_budget_target'),'*',array('id ='.$id),1);
            $this->db->from($this->config->item('table_pos_si_budget_target').' item');
            $this->db->select('item.*');
            $this->db->join($this->config->item('table_login_csetup_cus_info').' cus_info','cus_info.customer_id = item.outlet_id AND cus_info.revision = 1','INNER');
            $this->db->join($this->config->item('table_login_setup_location_districts').' district','district.id = cus_info.district_id','INNER');
            $this->db->join($this->config->item('table_login_setup_location_territories').' territory','territory.id = district.territory_id','INNER');
            $this->db->select('territory.zone_id');
            $this->db->where('item.id',$id);
            $info=$this->db->get()->row_array();
        }
        return $info;
    }
    private function get_info_target_zi($fiscal_year_id, $zone_id)
    {
        $info=Query_helper::get_info($this->config->item('table_bms_zi_budget_target'),'*',array('fiscal_year_id ='.$fiscal_year_id,'zone_id ='.$zone_id),1);
        return $info;
    }
    private function get_acres($outlet_id,$crop_id=0)
    {
        $results=Query_helper::get_info($this->config->item('table_login_csetup_cus_assign_upazillas'),array('upazilla_id'),array('customer_id ='.$outlet_id,'revision ='.'1'));
        $upazilla_ids[0]=0;
        foreach($results as $result)
        {
            $upazilla_ids[$result['upazilla_id']]=$result['upazilla_id'];
        }
        $this->db->from($this->config->item('table_login_setup_classification_type_acres').' acres');
        $this->db->select('SUM(acres.quantity_acres) quantity',false);
        $this->db->join($this->config->item('table_login_setup_classification_crop_types').' crop_type','crop_type.id=acres.type_id','INNER');
        $this->db->select('crop_type.id crop_type_id, crop_type.name crop_type_name,crop_type.quantity_kg_acre');
        $this->db->join($this->config->item('table_login_setup_classification_crops').' crop','crop.id=crop_type.crop_id','INNER');
        $this->db->select('crop.id crop_id, crop.name crop_name');
        $this->db->order_by('crop.ordering');
        $this->db->order_by('crop.id');
        $this->db->order_by('crop_type.ordering');
        $this->db->order_by('crop_type.id');
        if($crop_id>0)
        {
            $this->db->where('crop.id',$crop_id);
        }
        $this->db->where_in('acres.upazilla_id',$upazilla_ids);
        $this->db->group_by('crop_type.id');
        $results=$this->db->get()->result_array();
        $items=array();
        foreach($results as $result)
        {
            $items[$result['crop_id']][$result['crop_type_id']]['crop_name']=$result['crop_name'];
            $items[$result['crop_id']][$result['crop_type_id']]['crop_type_name']=$result['crop_type_name'];
            $items[$result['crop_id']][$result['crop_type_id']]['quantity']=$result['quantity'];
            $items[$result['crop_id']][$result['crop_type_id']]['quantity_kg_acre']=$result['quantity_kg_acre'];
        }
        return $items;
    }


}
