<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Budget_dealer_monthly_budget extends Root_Controller
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
        $this->lang->load('budget_dealer');
    }
    public function index($action="list", $id=0)
    {
        if($action=="list")
        {
            $this->system_list();
        }
        elseif($action=="get_items")
        {
            $this->system_get_items();
        }
        if($action=="list_all")
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
        elseif($action=="add_edit")
        {
            $this->system_add_edit();
        }
        elseif($action=="get_items_add_edit")
        {
            $this->system_get_items_add_edit();
        }
        elseif($action=="set_preference")
        {
            $this->system_set_preference('list');
        }
        elseif($action=="save")
        {
            $this->system_save();
        }
        elseif($action=="forward")
        {
            $this->system_forward($id);
        }
        elseif($action=="save_forward")
        {
            $this->system_save_forward();
        }
        elseif($action=="details")
        {
            $this->system_details($id);
        }
        elseif($action=="get_variety")
        {
            $this->system_get_variety();
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
        $data['outlet_name']= 1;
        $data['year']= 1;
        $data['month']= 1;
        $data['total_quantity_budget']= 1;
        $data['total_quantity_budget_kg']= 1;
        $data['total_amount_price_net']= 1;
        if($method=='list_all')
        {
            $data['total_quantity_target']= 1;
            $data['total_quantity_target_kg']= 1;
            $data['total_amount_target_price_net']= 1;
            $data['status_forward']= 1;
            $data['status_budget_target']= 1;
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
        $user = User_helper::get_user();
        $method='list';
        if(isset($this->permissions['action0'])&&($this->permissions['action0']==1))
        {
            $data['system_preference_items']= System_helper::get_preference($user->user_id,$this->controller_url,$method,$this->get_preference_headers($method));
            $data['title']="Monthly Dealer Budget Pending List";
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
        $this->db->from($this->config->item('table_pos_budget_dealer_monthly').' dealer_monthly');
        $this->db->select('dealer_monthly.*');
        $this->db->join($this->config->item('table_login_csetup_cus_info').' outlet_info','outlet_info.customer_id=dealer_monthly.outlet_id AND outlet_info.revision=1 AND outlet_info.type="'.$this->config->item('system_customer_type_outlet_id').'"','INNER');
        $this->db->select('outlet_info.name outlet_name');
        $this->db->join($this->config->item('table_pos_budget_dealer_monthly_total').' dealer_monthly_total','dealer_monthly_total.budget_monthly_id=dealer_monthly.id','INNER');

        $this->db->select('SUM(dealer_monthly_total.quantity_budget_total) total_quantity_budget');
        $this->db->select('SUM((dealer_monthly_total.quantity_budget_total*dealer_monthly_total.pack_size)/1000) total_quantity_budget_kg');
        $this->db->select('SUM(dealer_monthly_total.amount_price_net*dealer_monthly_total.quantity_budget_total) total_amount_price_net');

        $this->db->where('dealer_monthly.status != ',$this->config->item('system_status_delete'));
        $this->db->where('dealer_monthly_total.status',$this->config->item('system_status_active'));
        $this->db->where('dealer_monthly.status_forward',$this->config->item('system_status_pending'));
        $this->db->where_in('dealer_monthly.outlet_id',$this->user_outlet_ids);
        //$this->db->group_by('dealer_monthly.outlet_id,dealer_monthly.month,dealer_monthly.year');
        $this->db->group_by('dealer_monthly.id');
        $this->db->order_by('dealer_monthly.id', 'DESC');

        $items=$this->db->get()->result_array();

        foreach($items as &$item)
        {
            $item['month']=date("F", mktime(0, 0, 0,  $item['month'],1, 2000));
        }
        $this->json_return($items);
    }
    private function system_list_all()
    {
        $user = User_helper::get_user();
        $method='list_all';
        if(isset($this->permissions['action0'])&&($this->permissions['action0']==1))
        {
            $data['system_preference_items']= System_helper::get_preference($user->user_id,$this->controller_url,$method,$this->get_preference_headers($method));
            $data['title']="Monthly Dealer Budget All List";
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
        $this->db->from($this->config->item('table_pos_budget_dealer_monthly').' dealer_monthly');
        $this->db->select('dealer_monthly.*');
        $this->db->join($this->config->item('table_login_csetup_cus_info').' outlet_info','outlet_info.customer_id=dealer_monthly.outlet_id AND outlet_info.revision=1 AND outlet_info.type="'.$this->config->item('system_customer_type_outlet_id').'"','INNER');
        $this->db->select('outlet_info.name outlet_name');
        $this->db->join($this->config->item('table_pos_budget_dealer_monthly_total').' dealer_monthly_total','dealer_monthly_total.budget_monthly_id=dealer_monthly.id','INNER');

        $this->db->select('SUM(dealer_monthly_total.quantity_budget_total) total_quantity_budget');
        $this->db->select('SUM((dealer_monthly_total.quantity_budget_total*dealer_monthly_total.pack_size)/1000) total_quantity_budget_kg');
        $this->db->select('SUM(dealer_monthly_total.amount_price_net*dealer_monthly_total.quantity_budget_total) total_amount_price_net');

        $this->db->select('SUM(dealer_monthly_total.quantity_budget_target_total) total_quantity_target');
        $this->db->select('SUM((dealer_monthly_total.quantity_budget_target_total*dealer_monthly_total.pack_size)/1000) total_quantity_target_kg');
        $this->db->select('SUM(dealer_monthly_total.amount_price_net*dealer_monthly_total.quantity_budget_target_total) total_amount_target_price_net');
        $this->db->where('dealer_monthly.status !=',$this->config->item('system_status_delete'));
        $this->db->where('dealer_monthly_total.status',$this->config->item('system_status_active'));
        $this->db->where_in('dealer_monthly.outlet_id',$this->user_outlet_ids);
        $this->db->group_by('dealer_monthly.id');
        $this->db->order_by('dealer_monthly.id', 'DESC');
        $this->db->limit($pagesize,$current_records);

        $items=$this->db->get()->result_array();

        foreach($items as &$item)
        {
            $item['month']=date("F", mktime(0, 0, 0,  $item['month'],1, 2000));
        }
        $this->json_return($items);
    }
    private function system_search()
    {
        if(isset($this->permissions['action0'])&&($this->permissions['action0']==1))
        {

            $data['title']="Monthly Dealer Budget Add";
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/search",$data,true));
            $ajax['status']=true;
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
    private function get_headers_add_edit($dealers)
    {
        $data['crop_type_name']= 1;
        $data['variety_id']= 1;
        $data['variety_name']= 1;
        $data['pack_size_id']= 1;
        $data['pack_size']= 1;
        $data['amount_price_net']= 1;
        $data['current_stock_pkt']= 1;
        $data['current_stock_kg']= 1;
        foreach($dealers as $dealer)
        {
            $data['quantity_budget_'.$dealer['farmer_id']]= 1;
            $data['editable_'.$dealer['farmer_id']]= 1;
        }
        return $data;
    }
    private function system_add_edit()
    {
        if(isset($this->permissions['action0'])&&($this->permissions['action0']==1))
        {
            $data['options']=$this->input->post();
            $outlet_id=$this->input->post('outlet_id');
            $year=$this->input->post('year');
            $month=$this->input->post('month');
            $crop_id=$this->input->post('crop_id');
            if(!$outlet_id || !$year || !$month || !$crop_id)
            {
                $ajax['status']=false;
                $ajax['system_message']='Invalid input. Following required field. Ex: Star mark (*)';
                $this->json_return($ajax);
            }
            if(!in_array($outlet_id,$this->user_outlet_ids))
            {
                System_helper::invalid_try('add_edit',$outlet_id,'Outlet not assign. (outlet id)');
                $ajax['status']=false;
                $ajax['system_message']='Invalid Try.';
                $this->json_return($ajax);
            }
            $result=Query_helper::get_info($this->config->item('table_pos_budget_dealer_monthly'),'*',array('outlet_id ='.$outlet_id,'year ='.$year,'month ='.$month, 'status !="'.$this->config->item('system_status_delete').'"'),1);
            if($result && ($result['status_forward']==$this->config->item('system_status_forwarded')))
            {
                $ajax['status']=false;
                $ajax['system_message']='Budget for ('.date("F-Y", mktime(0, 0, 0,  $month,1, $year)).') already Forwarded';
                $this->json_return($ajax);
            }

            //$data['dealers']=Query_helper::get_info($this->config->item('table_pos_setup_farmer_farmer'),'*',array('status ="Active" AND farmer_type_id>1'));
            $this->db->from($this->config->item('table_pos_setup_farmer_outlet').' farmer_outlet');
            $this->db->select('farmer_outlet.farmer_id');
            $this->db->join($this->config->item('table_pos_setup_farmer_farmer').' farmer_farmer','farmer_farmer.id=farmer_outlet.farmer_id','INNER');
            $this->db->select('farmer_farmer.name farmer_name');
            $this->db->where('farmer_farmer.status',$this->config->item('system_status_active'));
            $this->db->where('farmer_farmer.farmer_type_id > ',1);
            $this->db->where('farmer_outlet.revision',1);
            $this->db->where('farmer_outlet.outlet_id',$outlet_id);
            $data['dealers']=$this->db->get()->result_array();
            $data['system_preference_items']=$this->get_headers_add_edit($data['dealers']);

            $data['title']="Budget For Varieties";
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>$this->load->view($this->controller_url."/add_edit",$data,true));
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
    private function system_get_items_add_edit()
    {
        $outlet_id=$this->input->post('outlet_id');
        $year=$this->input->post('year');
        $month=$this->input->post('month');
        $crop_id=$this->input->post('crop_id');

        $budget_monthly_id=0;
        $result=Query_helper::get_info($this->config->item('table_pos_budget_dealer_monthly'),'*',array('outlet_id ='.$outlet_id,'year ='.$year,'month ='.$month),1);
        if($result)
        {
            $budget_monthly_id=$result['id'];
            //check status forwarded or not
        }
        $this->db->from($this->config->item('table_pos_budget_dealer_monthly_details').' details');
        $this->db->select('details.*');
        $this->db->join($this->config->item('table_login_setup_classification_varieties').' v','v.id = details.variety_id','INNER');
        $this->db->join($this->config->item('table_login_setup_classification_crop_types').' crop_type','crop_type.id = v.crop_type_id','INNER');
        $this->db->where('crop_type.crop_id',$crop_id);
        $this->db->where('details.budget_monthly_id',$budget_monthly_id);
        $this->db->where('details.status',$this->config->item('system_status_active'));
        $results=$this->db->get()->result_array();
        $details_old=array();
        foreach($results as $result)
        {
            $details_old[$result['variety_id']][$result['pack_size_id']][$result['dealer_id']]=$result;
        }

        $this->db->from($this->config->item('table_pos_setup_farmer_outlet').' farmer_outlet');
        $this->db->select('farmer_outlet.farmer_id');
        $this->db->join($this->config->item('table_pos_setup_farmer_farmer').' farmer_farmer','farmer_farmer.id=farmer_outlet.farmer_id','INNER');
        $this->db->select('farmer_farmer.name farmer_name');
        $this->db->where('farmer_farmer.status',$this->config->item('system_status_active'));
        $this->db->where('farmer_farmer.farmer_type_id > ',1);
        $this->db->where('farmer_outlet.revision',1);
        $this->db->where('farmer_outlet.outlet_id',$outlet_id);
        $dealers=$this->db->get()->result_array();

        $this->db->from($this->config->item('table_login_setup_classification_variety_price').' variety_price');
        $this->db->select('variety_price.price_net amount_price_net');
        $this->db->join($this->config->item('table_login_setup_classification_varieties').' v','v.id = variety_price.variety_id','INNER');
        $this->db->select('v.id variety_id,v.name variety_name');
        $this->db->join($this->config->item('table_login_setup_classification_crop_types').' crop_type','crop_type.id = v.crop_type_id','INNER');
        $this->db->select('crop_type.id crop_type_id,crop_type.name crop_type_name');

        $this->db->join($this->config->item('table_login_setup_classification_pack_size').' pack','pack.id = variety_price.pack_size_id','INNER');
        $this->db->select('pack.name pack_size,pack.id pack_size_id');
        $this->db->where('crop_type.crop_id',$crop_id);
        $this->db->where('v.status',$this->config->item('system_status_active'));
        $this->db->where('pack.status',$this->config->item('system_status_active'));
        $this->db->order_by('crop_type.ordering','ASC');
        $this->db->order_by('crop_type.id','ASC');
        $this->db->order_by('v.ordering','ASC');
        $this->db->order_by('v.id','ASC');
        $this->db->order_by('pack.id');
        $results=$this->db->get()->result_array();

        $items=array();
        foreach($results as $result)
        {
            $item=$this->initialize_row_add_edit($result['crop_type_name'],$result['variety_name'],$result['variety_id'],$result['pack_size'],$result['pack_size_id'],$result['amount_price_net'],$dealers);
            if(isset($details_old[$result['variety_id']][$result['pack_size_id']]))
            {
                foreach($details_old[$result['variety_id']][$result['pack_size_id']] as $dealer_id=>$info)
                {
                    if($info['quantity_budget']>0)
                    {
                        $item['quantity_budget_'.$dealer_id]=$info['quantity_budget'];
                    }
                    else
                    {
                        $item['quantity_budget_'.$dealer_id]='';
                    }

                    if((!(isset($this->permissions['action2']) && ($this->permissions['action2']==1)))&& ($info['quantity_budget']>0))
                    {
                        $item['editable_'.$dealer_id]=false;
                    }
                }
            }

            //check if data already exits
            //check editable
            $items[]=$item;
        }
        $this->json_return($items);
    }
    private function initialize_row_add_edit($crop_type_name,$variety_name,$variety_id,$pack_size,$pack_size_id,$amount_price_net,$dealers)
    {
        $row=$this->get_headers_add_edit($dealers);
        foreach($row  as $key=>$r)
        {
            if(substr($key,0,9)=='editable_')
            {
                if((isset($this->permissions['action1']) && ($this->permissions['action1']==1))||(isset($this->permissions['action2']) && ($this->permissions['action2']==1)))
                {
                    $row[$key]= true;
                }
                else
                {
                    $row[$key]= false;
                }
            }
            else
            {
                $row[$key]='';
            }
        }
        $row['crop_type_name']=$crop_type_name;
        $row['variety_name']=$variety_name;
        $row['variety_id']=$variety_id;
        $row['pack_size']=$pack_size;
        $row['pack_size_id']=$pack_size_id;
        $row['amount_price_net']=$amount_price_net;
        return $row;
    }
    private function system_save()
    {
        $user = User_helper::get_user();
        $time=time();

        $item_head=$this->input->post('item');
        $items=$this->input->post('items');

        if(!((isset($this->permissions['action1']) && ($this->permissions['action1']==1)) || (isset($this->permissions['action2']) && ($this->permissions['action2']==1))))
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
        if(!in_array($item_head['outlet_id'],$this->user_outlet_ids))
        {
            System_helper::invalid_try('save',$item_head['outlet_id'],'Outlet not assign. (outlet id)');
            $ajax['status']=false;
            $ajax['system_message']='Invalid Try.';
            $this->json_return($ajax);
        }
        $pack_sizes=array();
        $results=Query_helper::get_info($this->config->item('table_login_setup_classification_pack_size'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"'));
        foreach($results as $result)
        {
            $pack_sizes[$result['value']]=$result['text'];
        }

        //$budget_monthly_id=0;
        $this->db->trans_start();
        $result=Query_helper::get_info($this->config->item('table_pos_budget_dealer_monthly'),'*',array('outlet_id ='.$item_head['outlet_id'],'year ='.$item_head['year'],'month ='.$item_head['month']),1);
        if($result)
        {
            $budget_monthly_id=$result['id'];
            //check status forwarded or not
        }
        else
        {
            $data=$item_head;
            unset($data['crop_id']);
            $data['date_budget_year_month']=System_helper::get_time('01-'.$item_head['month'].'-'.$item_head['year']);
            $data['date_created']=$time;
            $data['user_created']=$user->user_id;
            $budget_monthly_id=Query_helper::add($this->config->item('table_pos_budget_dealer_monthly'),$data,false);
        }
        $this->db->from($this->config->item('table_pos_budget_dealer_monthly_details').' details');
        $this->db->select('details.*');
        $this->db->join($this->config->item('table_login_setup_classification_varieties').' v','v.id = details.variety_id','INNER');
        $this->db->join($this->config->item('table_login_setup_classification_crop_types').' crop_type','crop_type.id = v.crop_type_id','INNER');
        $this->db->where('crop_type.crop_id',$item_head['crop_id']);
        $this->db->where('details.budget_monthly_id',$budget_monthly_id);
        $results=$this->db->get()->result_array();
        $details_old=array();
        $details_old_rows=array();
        foreach($results as $result)
        {
            $details_old[$result['variety_id']][$result['pack_size_id']][$result['dealer_id']]=$result;
            $details_old_rows[$result['id']]=$result;
        }

        $this->db->from($this->config->item('table_pos_budget_dealer_monthly_total').' details');
        $this->db->select('details.*');
        $this->db->join($this->config->item('table_login_setup_classification_varieties').' v','v.id = details.variety_id','INNER');
        $this->db->join($this->config->item('table_login_setup_classification_crop_types').' crop_type','crop_type.id = v.crop_type_id','INNER');
        $this->db->where('crop_type.crop_id',$item_head['crop_id']);
        $this->db->where('details.budget_monthly_id',$budget_monthly_id);
        $results=$this->db->get()->result_array();
        $total_old=array();
        $total_old_rows=array();
        foreach($results as $result)
        {
            $total_old[$result['variety_id']][$result['pack_size_id']]=$result;
            $total_old_rows[$result['id']]=$result;
        }
        foreach($items as $variety_id=>$packs_detail)
        {
            foreach($packs_detail as $pack_size_id=>$quantity_details)
            {
                $data_total=array();
                $data_total['budget_monthly_id']=$budget_monthly_id;
                $data_total['variety_id']=$variety_id;
                $data_total['pack_size_id']=$pack_size_id;
                $data_total['pack_size']=$pack_sizes[$pack_size_id];
                $data_total['quantity_budget_total']=0;
                $data_total['amount_price_net']=$quantity_details['amount_price_net'];
                $data_total['quantity_budget_target_total']=0;
                foreach($quantity_details['quantity_budget'] as $dealer_id=>$quantity)
                {
                    if(!($quantity>0))
                    {
                        $quantity=0;
                    }
                    $data=array();
                    $data['budget_monthly_id']=$budget_monthly_id;
                    $data['variety_id']=$variety_id;
                    $data['pack_size_id']=$pack_size_id;
                    $data['pack_size']=$pack_sizes[$pack_size_id];
                    $data['dealer_id']=$dealer_id;
                    $data['quantity_budget']=$quantity;

                    $data_total['quantity_budget_total']+=$quantity;
                    if(isset($details_old[$variety_id][$pack_size_id][$dealer_id]))
                    {
                        if(!(($quantity==$details_old[$variety_id][$pack_size_id][$dealer_id]['quantity_budget'])&&($details_old[$variety_id][$pack_size_id][$dealer_id]['status']==$this->config->item('system_status_active'))))
                        {
                            $data['status']=$this->config->item('system_status_active');
                            $this->db->set('revision_count', 'revision_count+1', FALSE);
                            Query_helper::update($this->config->item('table_pos_budget_dealer_monthly_details'),$data, array('id='.$details_old[$variety_id][$pack_size_id][$dealer_id]['id']), false);
                        }
                        unset($details_old_rows[$details_old[$variety_id][$pack_size_id][$dealer_id]['id']]);
                    }
                    else
                    {
                        Query_helper::add($this->config->item('table_pos_budget_dealer_monthly_details'),$data,false);
                    }

                }
                $data_total['quantity_budget_target_total']=$data_total['quantity_budget_total'];
                if(isset($total_old[$variety_id][$pack_size_id]))
                {
                    if(!(($data_total['quantity_budget_total']==$total_old[$variety_id][$pack_size_id]['quantity_budget_total'])&&($total_old[$variety_id][$pack_size_id]['status']==$this->config->item('system_status_active'))))
                    {
                        $data_total['status']=$this->config->item('system_status_active');
                        Query_helper::update($this->config->item('table_pos_budget_dealer_monthly_total'),$data_total, array('id='.$total_old[$variety_id][$pack_size_id]['id']), false);
                    }
                    unset($total_old_rows[$total_old[$variety_id][$pack_size_id]['id']]);
                }
                else
                {
                    Query_helper::add($this->config->item('table_pos_budget_dealer_monthly_total'),$data_total,false);
                }
            }
        }
        foreach($details_old_rows as $result)
        {
            $data=array();
            $data['status']=$this->config->item('system_status_delete');
            Query_helper::update($this->config->item('table_pos_budget_dealer_monthly_details'),$data, array('id='.$result['id']), false);
        }
        foreach($total_old_rows as $result)
        {
            $data=array();
            $data['status']=$this->config->item('system_status_delete');
            Query_helper::update($this->config->item('table_pos_budget_dealer_monthly_total'),$data, array('id='.$result['id']), false);
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === TRUE)
        {
            $ajax['status']=true;
            $ajax['status_save']=$this->lang->line("MSG_SAVED_SUCCESS");
            $ajax['system_message']=$this->lang->line("MSG_SAVED_SUCCESS");
            //$ajax['system_content'][]=array("id"=>"#system_report_container","html"=>'');
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['status_save']=$this->lang->line("MSG_SAVED_FAIL");
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
            $this->db->from($this->config->item('table_pos_budget_dealer_monthly').' dealer_monthly');
            $this->db->select('dealer_monthly.*');
            $this->db->join($this->config->item('table_pos_setup_user_outlet').' user_outlet','user_outlet.customer_id=dealer_monthly.outlet_id AND user_outlet.revision=1','INNER');
            $this->db->join($this->config->item('table_login_csetup_customer').' outlet','outlet.id=user_outlet.customer_id','INNER');
            $this->db->join($this->config->item('table_login_csetup_cus_info').' outlet_info','outlet_info.customer_id=outlet.id AND outlet_info.revision=1 AND outlet_info.type="'.$this->config->item('system_customer_type_outlet_id').'"','INNER');
            $this->db->select('outlet_info.name outlet_name');
            $this->db->where('dealer_monthly.status !="'.$this->config->item('system_status_delete').'"');
            $this->db->where('dealer_monthly.id',$item_id);
            $data['item']=$this->db->get()->row_array();
            if(!$data['item'])
            {
                System_helper::invalid_try('details',$item_id,'Details Non Exists');
                $ajax['status']=false;
                $ajax['system_message']='Invalid Try.';
                $this->json_return($ajax);
            }
            if(!in_array($data['item']['outlet_id'],$this->user_outlet_ids))
            {
                System_helper::invalid_try('details',$item_id,'Outlet not assign.');
                $ajax['status']=false;
                $ajax['system_message']='Invalid Try.';
                $this->json_return($ajax);
            }
            $user_ids=array();
            $user_ids[$data['item']['user_created']]=$data['item']['user_created'];
            $user_ids[$data['item']['user_forwarded']]=$data['item']['user_forwarded'];
            $user_ids[$data['item']['user_updated_target']]=$data['item']['user_updated_target'];
            $user_ids[$data['item']['user_approved_target']]=$data['item']['user_approved_target'];
            $user_ids[$data['item']['user_forwarded_rollback']]=$data['item']['user_forwarded_rollback'];
            $data['users']=System_helper::get_users_info($user_ids);

            $this->db->from($this->config->item('table_pos_budget_dealer_monthly_total').' details');

            $this->db->select('SUM(details.quantity_budget_total) quantity_budget_total');
            $this->db->select('SUM((details.quantity_budget_total*details.pack_size)/1000) quantity_budget_total_kg');
            $this->db->select('SUM(details.amount_price_net*details.quantity_budget_total) amount_budget_price_net');

            $this->db->select('SUM(details.quantity_budget_target_total) quantity_target_total');
            $this->db->select('SUM((details.quantity_budget_target_total*details.pack_size)/1000) quantity_target_total_kg');
            $this->db->select('SUM(details.amount_price_net*details.quantity_budget_target_total) amount_target_price_net');

            $this->db->join($this->config->item('table_login_setup_classification_varieties').' v','v.id = details.variety_id','INNER');
            $this->db->join($this->config->item('table_login_setup_classification_crop_types').' crop_type','crop_type.id = v.crop_type_id','INNER');
            $this->db->join($this->config->item('table_login_setup_classification_crops').' crop','crop.id = crop_type.crop_id','INNER');
            $this->db->select('crop.id crop_id, crop.name crop_name');
            $this->db->where('details.budget_monthly_id',$item_id);
            $this->db->where('details.status',$this->config->item('system_status_active'));
            $this->db->group_by('crop.id');
            $data['total_crops']=$this->db->get()->result_array();

            $this->db->from($this->config->item('table_pos_budget_dealer_monthly_details').' details');
            $this->db->join($this->config->item('table_pos_setup_farmer_outlet').' farmer_outlet','farmer_outlet.farmer_id=details.dealer_id','INNER');
            $this->db->select('farmer_outlet.farmer_id');
            $this->db->join($this->config->item('table_pos_setup_farmer_farmer').' farmer_farmer','farmer_farmer.id=farmer_outlet.farmer_id','INNER');
            $this->db->select('farmer_farmer.name farmer_name');
            $this->db->where('farmer_farmer.farmer_type_id > ',1);
            $this->db->where('farmer_outlet.revision',1);
            $this->db->where('details.budget_monthly_id',$data['item']['id']);
            $this->db->where('details.status',$this->config->item('system_status_active'));
            $this->db->group_by('details.dealer_id');
            $data['dealers']=$this->db->get()->result_array();

            $data['system_preference_items']=$this->get_headers($data['dealers']);

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
            $this->db->join($this->config->item('table_login_csetup_customer').' outlet','outlet.id=user_outlet.customer_id','INNER');
            $this->db->join($this->config->item('table_login_csetup_cus_info').' outlet_info','outlet_info.customer_id=outlet.id AND outlet_info.revision=1 AND outlet_info.type="'.$this->config->item('system_customer_type_outlet_id').'"','INNER');
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
            if(!in_array($data['item']['outlet_id'],$this->user_outlet_ids))
            {
                System_helper::invalid_try('forward',$item_id,'Outlet not assign.');
                $ajax['status']=false;
                $ajax['system_message']='Invalid Try.';
                $this->json_return($ajax);
            }
            if($data['item']['status_forward']==$this->config->item('system_status_forwarded'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Budget for ('.date("F-Y", mktime(0, 0, 0,  $data['item']['month'],1, $data['item']['year'])).') already Forwarded';
                $this->json_return($ajax);
            }

            $user_ids=array();
            $user_ids[$data['item']['user_created']]=$data['item']['user_created'];
            $user_ids[$data['item']['user_forwarded']]=$data['item']['user_forwarded'];
            /*$user_ids[$data['item']['user_forward_rollbacked']]=$data['item']['user_forward_rollbacked'];
            $user_ids[$data['item']['user_targeted']]=$data['item']['user_targeted'];
            $data['users']=$this->get_sms_users_info($user_ids);*/
            $data['users']=System_helper::get_users_info($user_ids);

            $this->db->from($this->config->item('table_pos_budget_dealer_monthly_total').' details');

            $this->db->select('SUM(details.quantity_budget_total) quantity_budget_total');
            $this->db->select('SUM((details.quantity_budget_total*details.pack_size)/1000) quantity_budget_total_kg');
            $this->db->select('SUM(details.amount_price_net*details.quantity_budget_total) amount_budget_price_net');

            $this->db->join($this->config->item('table_login_setup_classification_varieties').' v','v.id = details.variety_id','INNER');
            $this->db->join($this->config->item('table_login_setup_classification_crop_types').' crop_type','crop_type.id = v.crop_type_id','INNER');
            $this->db->join($this->config->item('table_login_setup_classification_crops').' crop','crop.id = crop_type.crop_id','INNER');
            $this->db->select('crop.id crop_id, crop.name crop_name');
            $this->db->where('details.budget_monthly_id',$item_id);
            $this->db->where('details.status',$this->config->item('system_status_active'));
            $this->db->group_by('crop.id');
            $data['total_crops']=$this->db->get()->result_array();

            $this->db->from($this->config->item('table_pos_budget_dealer_monthly_details').' details');
            $this->db->join($this->config->item('table_pos_setup_farmer_outlet').' farmer_outlet','farmer_outlet.farmer_id=details.dealer_id','INNER');
            $this->db->select('farmer_outlet.farmer_id');
            $this->db->join($this->config->item('table_pos_setup_farmer_farmer').' farmer_farmer','farmer_farmer.id=farmer_outlet.farmer_id','INNER');
            $this->db->select('farmer_farmer.name farmer_name');
            $this->db->where('farmer_farmer.farmer_type_id > ',1);
            $this->db->where('farmer_outlet.revision',1);
            $this->db->where('details.budget_monthly_id',$data['item']['id']);
            $this->db->where('details.status',$this->config->item('system_status_active'));
            $this->db->group_by('details.dealer_id');
            $data['dealers']=$this->db->get()->result_array();

            $data['system_preference_items']=$this->get_headers($data['dealers']);

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

            $data['item']=Query_helper::get_info($this->config->item('table_pos_budget_dealer_monthly'),'*',array('id ='.$id, 'status !="'.$this->config->item('system_status_delete').'"'),1);
            if(!$data['item'])
            {
                System_helper::invalid_try('save_forward',$id,'Save Forward Non Exists');
                $ajax['status']=false;
                $ajax['system_message']='Invalid Try.';
                $this->json_return($ajax);
            }
            if(!in_array($data['item']['outlet_id'],$this->user_outlet_ids))
            {
                System_helper::invalid_try('save_forward',$id,'Outlet not assign.');
                $ajax['status']=false;
                $ajax['system_message']='Invalid Try.';
                $this->json_return($ajax);
            }
            if($data['item']['status_forward']==$this->config->item('system_status_forwarded'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Budget for ('.date("F-Y", mktime(0, 0, 0,  $data['item']['month'],1, $data['item']['year'])).') already Forwarded';
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

        $item_head['date_forwarded']=$time;
        $item_head['user_forwarded']=$user->user_id;
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
    private function get_headers($dealers)
    {
        $data['crop_name']= 1;
        $data['crop_type_name']= 1;
        $data['variety_id']= 1;
        $data['variety_name']= 1;
        $data['pack_size_id']= 1;
        $data['pack_size']= 1;
        $data['amount_price_net']= 1;
        $data['quantity_budget_total']= 1;
        $data['amount_price_total']= 1;
        $data['quantity_budget_target_total']= 1;
        $data['amount_price_total_target']= 1;
        foreach($dealers as $dealer)
        {
            $data['quantity_budget_'.$dealer['farmer_id']]= 1;
        }
        return $data;
    }
    private function system_get_variety()
    {
        $outlet_id=$this->input->post('outlet_id');
        $year=$this->input->post('year');
        $month=$this->input->post('month');

        $budget_monthly_id=0;
        $result=Query_helper::get_info($this->config->item('table_pos_budget_dealer_monthly'),'*',array('outlet_id ='.$outlet_id,'year ='.$year,'month ='.$month),1);
        if($result)
        {
            $budget_monthly_id=$result['id'];
            //check status forwarded or not
        }

        $this->db->from($this->config->item('table_pos_budget_dealer_monthly_details').' details');
        $this->db->select('details.*');
        $this->db->where('details.budget_monthly_id',$budget_monthly_id);
        $this->db->where('details.status',$this->config->item('system_status_active'));
        $results=$this->db->get()->result_array();
        $details_old=array();
        foreach($results as $result)
        {
            $details_old[$result['variety_id']][$result['pack_size_id']][$result['dealer_id']]=$result;
        }

        $this->db->from($this->config->item('table_pos_budget_dealer_monthly_total').' details');
        $this->db->select('details.*');
        $this->db->join($this->config->item('table_login_setup_classification_varieties').' v','v.id = details.variety_id','INNER');
        $this->db->select('v.id variety_id, v.name variety_name');
        $this->db->join($this->config->item('table_login_setup_classification_crop_types').' crop_type','crop_type.id = v.crop_type_id','INNER');
        $this->db->select('crop_type.id crop_type_id, crop_type.name crop_type_name');
        $this->db->join($this->config->item('table_login_setup_classification_crops').' crop','crop.id = crop_type.crop_id','INNER');
        $this->db->select('crop.id crop_id, crop.name crop_name');
        $this->db->where('details.budget_monthly_id',$budget_monthly_id);
        $this->db->where('details.status',$this->config->item('system_status_active'));
        $results=$this->db->get()->result_array();

        $items=array();
        foreach($results as $result)
        {
            $item=$this->initialize_row($result['crop_name'],$result['crop_type_name'],$result['variety_name'],$result['variety_id'],$result['pack_size'],$result['pack_size_id'],$result['amount_price_net'],$result['quantity_budget_total'],$result['quantity_budget_target_total']);
            if(isset($details_old[$result['variety_id']][$result['pack_size_id']]))
            {
                foreach($details_old[$result['variety_id']][$result['pack_size_id']] as $dealer_id=>$info)
                {
                    $item['quantity_budget_'.$dealer_id]=$info['quantity_budget'];
                }
            }
            $items[]=$item;
        }
        $this->json_return($items);
    }
    private function initialize_row($crop_name,$crop_type_name,$variety_name,$variety_id,$pack_size,$pack_size_id,$amount_price_net,$quantity_budget_total,$quantity_budget_target_total)
    {
        $row['crop_name']=$crop_name;
        $row['crop_type_name']=$crop_type_name;
        $row['variety_name']=$variety_name;
        $row['variety_id']=$variety_id;
        $row['pack_size']=$pack_size;
        $row['pack_size_id']=$pack_size_id;
        $row['amount_price_net']=$amount_price_net;
        $row['quantity_budget_total']=$quantity_budget_total;
        $row['amount_price_total']=($quantity_budget_total*$amount_price_net);
        $row['quantity_budget_target_total']=$quantity_budget_target_total;
        $row['amount_price_total_target']=($quantity_budget_target_total*$amount_price_net);
        return $row;
    }
}
