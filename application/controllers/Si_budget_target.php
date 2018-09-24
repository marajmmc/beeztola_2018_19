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
        elseif($action=="forward_budget")
        {
            $this->system_forward_budget($id,$id1);
        }
        elseif($action=="save_forward_budget")
        {
            $this->system_save_forward_budget();
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
            //more datas
            $data['quantity_budget']= 1;
        }
        else if($method=='list_budget_outlet')
        {
            $data['crop_id']= 1;
            $data['crop_name']= 1;
            $data['revision_count_budget']= 1;
        }
        else if($method=='edit_budget_outlet')
        {
            $data['crop_type_name']= 1;
            $data['variety_name']= 1;
            $data['variety_id']= 1;
            //more data
            $data['quantity_budget_dealer_total']= 1;
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
        $this->db->select('MAX(budget_target_dealer.revision_count_budget) revision_count_budget');
        $this->db->where('budget_target_dealer.fiscal_year_id',$fiscal_year_id);
        $this->db->where('budget_target_dealer.outlet_id',$outlet_id);
        $this->db->group_by('budget_target_dealer.dealer_id');
        $results=$this->db->get()->result_array();
        $budgeted=array();
        foreach($results as $result)
        {
            $budgeted[$result['dealer_id']]=$result['revision_count_budget'];
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
            foreach($fiscal_years_previous_sales as $fy)
            {
                if(isset($sales_previous[$fy['id']][$dealer_id][$result['variety_id']]))
                {
                    $item['quantity_sale_'.$fy['id']]=$sales_previous[$fy['id']][$dealer_id][$result['variety_id']]/1000;
                }
                else
                {
                    $item['quantity_sale_'.$fy['id']]=0;
                }
            }
            if(isset($items_old[$result['variety_id']]))
            {
                if($items_old[$result['variety_id']]['quantity_budget']>0)
                {
                    $item['quantity_budget']=$items_old[$result['variety_id']]['quantity_budget'];
                }
                else
                {
                    $item['quantity_budget']='';
                }
            }
            else
            {
                $item['quantity_budget']='';
            }
            $items[]=$item;
        }

        $this->json_return($items);
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
        $info_budget_target=$this->get_info_budget_target($item_head['fiscal_year_id'],$item_head['outlet_id']);
        if(($info_budget_target['status_budget_forward']==$this->config->item('system_status_forwarded')))
        {
            if(!(isset($this->permissions['action3']) && ($this->permissions['action3']==1)))
            {
                $ajax['status']=false;
                $ajax['system_message']='Budget Already Forwarded.';
                $this->json_return($ajax);
            }
        }
        //dealer validation is not checking
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
                        $this->db->where('id',$items_old[$variety_id]['id']);
                        $this->db->set('revision_count_budget','revision_count_budget+1',false);
                        $this->db->set('quantity_budget',$quantity_budget);
                        $this->db->set('date_updated_budget',$time);
                        $this->db->set('user_updated_budget',$user->user_id);
                        $this->db->update($this->config->item('table_pos_si_budget_target_dealer'));
                }
            }
            else
            {
                $data=array();
                $data['fiscal_year_id']=$item_head['fiscal_year_id'];
                $data['outlet_id']=$item_head['outlet_id'];
                $data['dealer_id']=$item_head['dealer_id'];
                $data['variety_id']=$variety_id;
                if($quantity_budget>0)
                {
                    $data['quantity_budget']=$quantity_budget;
                    $data['revision_count_budget']=1;
                }
                else
                {
                    $data['quantity_budget']=0;
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
            $data['fiscal_year']=Query_helper::get_info($this->config->item('table_login_basic_setup_fiscal_year'),'*',array('id ='.$fiscal_year_id),1);
            $data['outlet']=Query_helper::get_info($this->config->item('table_login_csetup_cus_info'),'*',array('customer_id ='.$outlet_id,'revision =1'),1);
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

        //get budget revision
        $this->db->from($this->config->item('table_pos_si_budget_target_outlet').' budget_target_outlet');
        $this->db->select('MAX(budget_target_outlet.revision_count_budget) revision_count_budget');

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
            $budgeted[$result['crop_id']]=$result['revision_count_budget'];
        }
        //crop list
        $results=Query_helper::get_info($this->config->item('table_login_setup_classification_crops'),array('id crop_id','name crop_name'),array('status ="'.$this->config->item('system_status_active').'"'),0,0,array('ordering ASC','id ASC'));
        foreach($results as $crop)
        {
            $item=$crop;
            if(isset($budgeted[$crop['crop_id']]))
            {
                $item['revision_count_budget']=$budgeted[$crop['crop_id']];
            }
            else
            {
                $item['revision_count_budget']=0;
            }
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
            $data['fiscal_years_previous_sales']=Query_helper::get_info($this->config->item('table_login_basic_setup_fiscal_year'),'*',array('id <'.$fiscal_year_id),Budget_helper::$NUM_FISCAL_YEAR_PREVIOUS_SALE,0,array('id DESC'));
            $data['fiscal_year']=Query_helper::get_info($this->config->item('table_login_basic_setup_fiscal_year'),'*',array('id ='.$fiscal_year_id),1);
            $data['outlet']=Query_helper::get_info($this->config->item('table_login_csetup_cus_info'),'*',array('customer_id ='.$outlet_id,'revision =1'),1);
            $data['crop']=Query_helper::get_info($this->config->item('table_login_setup_classification_crops'),'*',array('id ='.$crop_id),1);
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
        $budget_dealers=array();
        foreach($results as $result)
        {
            $budget_dealers[$result['dealer_id']][$result['variety_id']]=$result;
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
            foreach($fiscal_years_previous_sales as $fy)
            {
                if(isset($sales_previous[$fy['id']][$result['variety_id']]))
                {
                    $item['quantity_sale_'.$fy['id']]=$sales_previous[$fy['id']][$result['variety_id']]/1000;
                }
                else
                {
                    $item['quantity_sale_'.$fy['id']]=0;
                }
            }

            $quantity_budget_dealer_total=0;
            foreach($dealers as $dealer)
            {
                //$item['quantity_budget_dealer_'.$dealer['farmer_id']]= 1;
                if(isset($budget_dealers[$dealer['farmer_id']][$result['variety_id']]))
                {
                    $item['quantity_budget_dealer_'.$dealer['farmer_id']]= $budget_dealers[$dealer['farmer_id']][$result['variety_id']]['quantity_budget'];
                    $quantity_budget_dealer_total+=$budget_dealers[$dealer['farmer_id']][$result['variety_id']]['quantity_budget'];
                }
                else
                {
                    $item['quantity_budget_dealer_'.$dealer['farmer_id']]= 0;
                }
            }
            $item['quantity_budget_dealer_total']= $quantity_budget_dealer_total;
            if(isset($items_old[$result['variety_id']]))
            {
                if($items_old[$result['variety_id']]['quantity_budget']>0)
                {
                    $item['quantity_budget']=$items_old[$result['variety_id']]['quantity_budget'];
                }
                else
                {
                    $item['quantity_budget']='';
                }
            }
            else
            {
                $item['quantity_budget']='';
            }
            $items[]=$item;
        }

        $this->json_return($items);
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
        $info_budget_target=$this->get_info_budget_target($item_head['fiscal_year_id'],$item_head['outlet_id']);
        if(($info_budget_target['status_budget_forward']==$this->config->item('system_status_forwarded')))
        {
            if(!(isset($this->permissions['action3']) && ($this->permissions['action3']==1)))
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
                    $this->db->where('id',$items_old[$variety_id]['id']);
                    $this->db->set('revision_count_budget','revision_count_budget+1',false);
                    $this->db->set('quantity_budget',$quantity_budget);
                    $this->db->set('date_updated_budget',$time);
                    $this->db->set('user_updated_budget',$user->user_id);
                    $this->db->update($this->config->item('table_pos_si_budget_target_outlet'));
                }
            }
            else
            {
                $data=array();
                $data['fiscal_year_id']=$item_head['fiscal_year_id'];
                $data['outlet_id']=$item_head['outlet_id'];
                $data['variety_id']=$variety_id;
                if($quantity_budget>0)
                {
                    $data['quantity_budget']=$quantity_budget;
                    $data['revision_count_budget']=1;
                }
                else
                {
                    $data['quantity_budget']=0;
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
    private function system_forward_budget($fiscal_year_id=0,$outlet_id=0)
    {
        $user = User_helper::get_user();
        $method='list_budget_dealer';
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
           // $data['system_preference_items']= $this->get_preference_headers($method);
            $data['fiscal_year_budget_target']=Query_helper::get_info($this->config->item('table_login_basic_setup_fiscal_year'),'*',array('id ='.$fiscal_year_id),1);
            $data['outlet']=Query_helper::get_info($this->config->item('table_login_csetup_cus_info'),'*',array('customer_id ='.$outlet_id,'revision =1'),1);
            $data['title']="Forward/Complete budget";
            $data['options']['fiscal_year_id']=$fiscal_year_id;
            $data['options']['outlet_id']=$outlet_id;
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/forward_budget",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/forward_budget/'.$fiscal_year_id.'/'.$outlet_id);
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
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
        $info_budget_target=$this->get_info_budget_target($item_head['fiscal_year_id'],$item_head['outlet_id']);
        if(($info_budget_target['status_budget_forward']==$this->config->item('system_status_forwarded')))
        {
            if(!(isset($this->permissions['action3']) && ($this->permissions['action3']==1)))
            {
                $ajax['status']=false;
                $ajax['system_message']='Budget Already Forwarded.';
                $this->json_return($ajax);
            }
        }

        $this->db->trans_start();  //DB Transaction Handle START
        $data=array();
        $data['status_budget_forward']=$item_head['status_budget_forward'];
        $data['date_budget_forwarded']=$time;
        $data['user_budget_forwarded']=$user->user_id;
        Query_helper::update($this->config->item('table_pos_si_budget_target'),$data,array('id='.$info_budget_target['id']));

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
        return $results;
    }


}
