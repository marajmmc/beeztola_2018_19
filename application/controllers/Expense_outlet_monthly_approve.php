<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Expense_outlet_monthly_approve extends Root_Controller
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
        $this->lang->load('expense_outlet');
        $this->load->helper('expense_helper');
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
        elseif($action=="edit")
        {
            $this->system_edit($id);
        }
        elseif($action=="get_items_edit")
        {
            $this->system_get_items_edit();
        }
        elseif($action=="set_preference")
        {
            $this->system_set_preference('list');
        }
        elseif($action=="save")
        {
            $this->system_save();
        }
        elseif($action=="approve")
        {
            $this->system_approve($id);
        }
        elseif($action=="save_approve")
        {
            $this->system_save_approve();
        }
        elseif($action=="details")
        {
            $this->system_details($id);
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
        $data['amount_request']= 1;
        $data['amount_check']= 1;
        $data['amount_approve']= 1;
        if($method=='list_all')
        {
            $data['status_approve']= 1;
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
            $data['title']="Outlet Monthly Expense Approve Pending List";
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
        $this->db->from($this->config->item('table_pos_expense_outlet_monthly').' item');
        $this->db->select('item.*');
        $this->db->join($this->config->item('table_login_csetup_cus_info').' outlet_info','outlet_info.customer_id=item.outlet_id AND outlet_info.revision=1 AND outlet_info.type="'.$this->config->item('system_customer_type_outlet_id').'"','INNER');
        $this->db->select('outlet_info.name outlet_name');
        $this->db->where('item.status !=',$this->config->item('system_status_delete'));
        $this->db->where('item.status_forward_check',$this->config->item('system_status_forwarded'));
        $this->db->where('item.status_approve',$this->config->item('system_status_pending'));
        $this->db->where_in('item.outlet_id',$this->user_outlet_ids);
        $this->db->order_by('item.id','DESC');
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
            $data['title']="Outlet Monthly Expense Approve  All List";
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
        $this->db->from($this->config->item('table_pos_expense_outlet_monthly').' item');
        $this->db->select('item.*, item.status_forward_check status_forward');
        $this->db->join($this->config->item('table_login_csetup_cus_info').' outlet_info','outlet_info.customer_id=item.outlet_id AND outlet_info.revision=1 AND outlet_info.type="'.$this->config->item('system_customer_type_outlet_id').'"','INNER');
        $this->db->select('outlet_info.name outlet_name');
        $this->db->where('item.status !=',$this->config->item('system_status_delete'));
        $this->db->where('item.status_forward_check',$this->config->item('system_status_forwarded'));
        $this->db->where_in('item.outlet_id',$this->user_outlet_ids);
        $this->db->order_by('item.id','DESC');
        $this->db->limit($pagesize,$current_records);
        $items=$this->db->get()->result_array();
        foreach($items as &$item)
        {
            $item['month']=date("F", mktime(0, 0, 0,  $item['month'],1, 2000));
        }
        $this->json_return($items);
    }
    private function get_headers_edit()
    {
        $data['expense_item_id']= 1;
        $data['expense_item_name']= 1;
        $data['amount_request']= 1;
        $data['amount_check']= 1;
        $data['amount_approve']= 1;
        return $data;
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
            $this->db->from($this->config->item('table_pos_expense_outlet_monthly').' item');
            $this->db->select('item.*');
            $this->db->join($this->config->item('table_login_csetup_cus_info').' outlet_info','outlet_info.customer_id=item.outlet_id AND outlet_info.revision=1 AND outlet_info.type="'.$this->config->item('system_customer_type_outlet_id').'"','INNER');
            $this->db->select('outlet_info.name outlet_name');
            $this->db->where('item.id',$item_id);
            $this->db->where('item.status !=',$this->config->item('system_status_delete'));
            $data['item']=$this->db->get()->row_array();

            if(!$data['item'])
            {
                System_helper::invalid_try('edit',$id,'Edit Non Exists');
                $ajax['status']=false;
                $ajax['system_message']='Invalid Try.';
                $this->json_return($ajax);
            }
            if($data['item'] && ($data['item']['status_forward_check']!=$this->config->item('system_status_forwarded')))
            {
                $ajax['status']=false;
                $ajax['system_message']='Expense for ('.date("F-Y", mktime(0, 0, 0,  $data['item']['month'],1, $data['item']['year'])).') not forwarded';
                $this->json_return($ajax);
            }
            if($data['item'] && ($data['item']['status_approve']==$this->config->item('system_status_approved')))
            {
                $ajax['status']=false;
                $ajax['system_message']='Expense already Approved';
                $this->json_return($ajax);
            }
            if(!in_array($data['item']['outlet_id'],$this->user_outlet_ids))
            {
                System_helper::invalid_try('forward',$data['item']['outlet_id'],'Outlet not assign. (outlet id)');
                $ajax['status']=false;
                $ajax['system_message']='Invalid Try.';
                $this->json_return($ajax);
            }
            $date=Expense_helper::get_between_date_by_month($data['item']['month'], $data['item']['year']);
            $this->db->from($this->config->item('table_pos_expense_outlet_daily').' item');
            $this->db->select('item.*');
            $this->db->join($this->config->item('table_login_setup_expense_item_outlet').' items','items.id=item.expense_id','INNER');
            $this->db->select('items.name');
            $this->db->where('item.status',$this->config->item('system_status_active'));
            $this->db->where('item.outlet_id',$data['item']['outlet_id']);
            $this->db->where('item.date_expense >=',$date['date_start']);
            $this->db->where('item.date_expense <=',$date['date_end']);
            $this->db->order_by('item.date_expense','ASC');
            $results=$this->db->get()->result_array();
            $daily_expenses=array();
            foreach($results as $result)
            {
                $expense_date=System_helper::display_date($result['date_expense']);
                $daily_expenses[$expense_date][]=$result;
            }
            $data['daily_expenses']=$daily_expenses;
            $data['system_preference_items']=$this->get_headers_edit();

            $data['title']="Monthly Expense Approve";
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
    private function system_get_items_edit()
    {
        $outlet_id=$this->input->post('outlet_id');
        $year=$this->input->post('year');
        $month=$this->input->post('month');
        $date=Expense_helper::get_between_date_by_month($month, $year);


        $results=Query_helper::get_info($this->config->item('table_login_setup_expense_item_outlet'),'*',array('status !="'.$this->config->item('system_status_delete').'"'),0,0,array('ordering'));
        $expense_items=array();
        foreach($results as $result)
        {
            $expense_items[$result['id']]['expense_item_id']=$result['id'];
            $expense_items[$result['id']]['expense_item_name']=$result['name'].' ('.$result['status'].')';
            $expense_items[$result['id']]['amount_request']='';
            $expense_items[$result['id']]['amount_check']='';
            $expense_items[$result['id']]['amount_approve']='';
        }

        $outlet_monthly_id=0;
        $result=Query_helper::get_info($this->config->item('table_pos_expense_outlet_monthly'),'*',array('outlet_id ='.$outlet_id,'year ='.$year,'month ='.$month, 'status !="'.$this->config->item('system_status_delete').'"'),1);
        if($result)
        {
            $outlet_monthly_id=$result['id'];
        }

        $date_end=$date['date_end'];
        $date_start=$date['date_start'];
        $results=Query_helper::get_info($this->config->item('table_pos_expense_outlet_daily'),'*',array('date_expense >='.$date_start,' date_expense <= '.$date_end, 'outlet_id ='.$outlet_id,'status !="'.$this->config->item('system_status_delete').'"'));
        foreach($results as $result)
        {
            $expense_items[$result['expense_id']]['amount_request']+=$result['amount'];
            $expense_items[$result['expense_id']]['amount_check']+=$result['amount'];
            $expense_items[$result['expense_id']]['amount_approve']+=$result['amount'];
        }

        $this->db->from($this->config->item('table_pos_expense_outlet_monthly_details').' details');
        $this->db->select('details.*');
        $this->db->where('details.outlet_monthly_id',$outlet_monthly_id);
        $this->db->where('details.status',$this->config->item('system_status_active'));
        $results=$this->db->get()->result_array();

        foreach($results as $result)
        {
            $expense_items[$result['expense_id']]['amount_check']=$result['amount_check'];
            $expense_items[$result['expense_id']]['amount_approve']=$result['amount_approve']?$result['amount_approve']:$result['amount_check'];
        }

        $items=array();
        foreach($expense_items as $result)
        {
            $item['expense_item_id']=$result['expense_item_id'];
            $item['expense_item_name']=$result['expense_item_name'];
            $item['amount_request']=$result['amount_request'];
            $item['amount_check']=$result['amount_check'];
            $item['amount_approve']=$result['amount_approve'];
            $items[]=$item;
        }

        $this->json_return($items);
    }
    private function system_save()
    {
        $id = $this->input->post("id");
        $user = User_helper::get_user();
        $time=time();
        $items=$this->input->post('items');

        if(!($id>0))
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
        if(!(isset($this->permissions['action2']) && ($this->permissions['action2']==1)))
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }

        $this->db->from($this->config->item('table_pos_expense_outlet_monthly').' item');
        $this->db->select('item.*');
        $this->db->join($this->config->item('table_login_csetup_cus_info').' outlet_info','outlet_info.customer_id=item.outlet_id AND outlet_info.revision=1 AND outlet_info.type="'.$this->config->item('system_customer_type_outlet_id').'"','INNER');
        $this->db->select('outlet_info.name outlet_name');
        $this->db->where('item.id',$id);
        $this->db->where('item.status !=',$this->config->item('system_status_delete'));
        $data['item']=$this->db->get()->row_array();

        if(!$data['item'])
        {
            System_helper::invalid_try('edit',$id,'Edit Non Exists');
            $ajax['status']=false;
            $ajax['system_message']='Invalid Try.';
            $this->json_return($ajax);
        }
        if($data['item'] && ($data['item']['status_forward_check']!=$this->config->item('system_status_forwarded')))
        {
            $ajax['status']=false;
            $ajax['system_message']='Expense Not Forwarded';
            $this->json_return($ajax);
        }
        if($data['item'] && ($data['item']['status_approve']==$this->config->item('system_status_approved')))
        {
            $ajax['status']=false;
            $ajax['system_message']='Expense already Approved';
            $this->json_return($ajax);
        }
        if(!in_array($data['item']['outlet_id'],$this->user_outlet_ids))
        {
            System_helper::invalid_try('forward',$data['item']['outlet_id'],'Outlet not assign. (outlet id)');
            $ajax['status']=false;
            $ajax['system_message']='Invalid Try.';
            $this->json_return($ajax);
        }

        $this->db->trans_start();
        $amount_total_approve=0;
        foreach($items as $item)
        {
            $amount_total_approve+=$item['amount_approve'];
        }

        $data=array();
        $data['amount_approve']=$amount_total_approve;
        $data['date_update_approved']=$time;
        $data['user_update_approved']=$user->user_id;
        Query_helper::update($this->config->item('table_pos_expense_outlet_monthly'),$data,array('id='.$id),false);

        $results=Query_helper::get_info($this->config->item('table_pos_expense_outlet_monthly_details'),'*',array('outlet_monthly_id ='.$id));
        $details_old=array();
        $details_old_rows=array();
        foreach($results as $result)
        {
            $details_old[$result['expense_id']]=$result;
            $details_old_rows[$result['id']]=$result;
        }

        foreach($items as $expense_id=>$detail)
        {
            $data=array();
            $data['amount_approve']=$detail['amount_approve'];
            if(isset($details_old[$expense_id]))
            {
                if(!(($detail['amount_approve']==$details_old[$expense_id]['amount_approve'])&&($details_old[$expense_id]['status']==$this->config->item('system_status_active'))))
                {
                    $data['status']=$this->config->item('system_status_active');
                    Query_helper::update($this->config->item('table_pos_expense_outlet_monthly_details'),$data, array('id='.$details_old[$expense_id]['id']), false);
                }
                unset($details_old_rows[$details_old[$expense_id]['id']]);
            }
            else
            {
                Query_helper::add($this->config->item('table_pos_expense_outlet_monthly_details'),$data,false);
            }
        }
        foreach($details_old_rows as $result)
        {
            $data=array();
            $data['status']=$this->config->item('system_status_delete');
            Query_helper::update($this->config->item('table_pos_expense_outlet_monthly_details'),$data, array('id='.$result['id']), false);
        }

        $this->db->trans_complete();
        if ($this->db->trans_status() === TRUE)
        {
            $ajax['status']=false;
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
        $ajax['status']=false;
        $ajax['system_message']='Details not ready';
        $this->json_return($ajax);
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

            $data['title']='Outlet Monthly Expense Details';
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
    private function system_approve($id)
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

            $this->db->from($this->config->item('table_pos_expense_outlet_monthly').' item');
            $this->db->select('item.*');
            $this->db->join($this->config->item('table_login_csetup_cus_info').' outlet_info','outlet_info.customer_id=item.outlet_id AND outlet_info.revision=1 AND outlet_info.type="'.$this->config->item('system_customer_type_outlet_id').'"','INNER');
            $this->db->select('outlet_info.name outlet_name');
            $this->db->where('item.id',$item_id);
            $this->db->where('item.status !=',$this->config->item('system_status_delete'));
            $data['item']=$this->db->get()->row_array();
            if(!$data['item'])
            {
                System_helper::invalid_try('approve',$id,'Approve Non Exists');
                $ajax['status']=false;
                $ajax['system_message']='Invalid Try.';
                $this->json_return($ajax);
            }
            if($data['item'] && ($data['item']['status_forward_check']!=$this->config->item('system_status_forwarded')))
            {
                $ajax['status']=false;
                $ajax['system_message']='Expense for ('.date("F-Y", mktime(0, 0, 0,  $data['item']['month'],1, $data['item']['year'])).') not forwarded';
                $this->json_return($ajax);
            }
            if($data['item'] && ($data['item']['status_approve']==$this->config->item('system_status_approved')))
            {
                $ajax['status']=false;
                $ajax['system_message']='Expense already Approved';
                $this->json_return($ajax);
            }
            if(!in_array($data['item']['outlet_id'],$this->user_outlet_ids))
            {
                System_helper::invalid_try('forward',$data['item']['outlet_id'],'Outlet not assign. (outlet id)');
                $ajax['status']=false;
                $ajax['system_message']='Invalid Try.';
                $this->json_return($ajax);
            }
            $date=Expense_helper::get_between_date_by_month($data['item']['month'], $data['item']['year']);

            $this->db->from($this->config->item('table_pos_expense_outlet_daily').' item');
            $this->db->select('item.*');
            $this->db->join($this->config->item('table_login_setup_expense_item_outlet').' items','items.id=item.expense_id','INNER');
            $this->db->select('items.name');
            $this->db->where('item.status',$this->config->item('system_status_active'));
            $this->db->where('item.outlet_id',$data['item']['outlet_id']);
            $this->db->where('item.date_expense >=',$date['date_start']);
            $this->db->where('item.date_expense <=',$date['date_end']);
            $this->db->order_by('item.date_expense','ASC');
            $results=$this->db->get()->result_array();
            $daily_expenses=array();
            foreach($results as $result)
            {
                $expense_date=System_helper::display_date($result['date_expense']);
                $daily_expenses[$expense_date][]=$result;
            }
            $data['daily_expenses']=$daily_expenses;

            $data['system_preference_items']=$this->get_headers_edit();

            $data['title']="Monthly Expense Approved";
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/approve",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/approve/'.$item_id);
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function system_save_approve()
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
            if($item_head['status_approve']!=$this->config->item('system_status_approved') && $item_head['status_approve']!=$this->config->item('system_status_rollback'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Approved/Rollback field is required.';
                $this->json_return($ajax);
            }
            if($item_head['status_approve']==$this->config->item('system_status_rollback'))
            {
                if(!$item_head['remarks_approve'])
                {
                    $ajax['status']=false;
                    $ajax['system_message']='Remarks for Approved/Rollback field is required.';
                    $this->json_return($ajax);
                }
            }
            $data['item']=Query_helper::get_info($this->config->item('table_pos_expense_outlet_monthly'),'*',array('id ='.$id, 'status !="'.$this->config->item('system_status_delete').'"'),1);
            if(!$data['item'])
            {
                System_helper::invalid_try('save_approve',$id,'Save Approve Non Exists');
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
            if($data['item']['status_forward_check']!=$this->config->item('system_status_forwarded'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Expense for ('.date("F-Y", mktime(0, 0, 0,  $data['item']['month'],1, $data['item']['year'])).') Not Forwarded';
                $this->json_return($ajax);
            }
            if($data['item']['status_approve']==$this->config->item('system_status_approved'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Expense for ('.date("F-Y", mktime(0, 0, 0,  $data['item']['month'],1, $data['item']['year'])).') Already Approved';
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

        if($item_head['status_approve']==$this->config->item('system_status_rollback'))
        {
            $item_head['status_forward_check']=$this->config->item('system_status_pending');
            $item_head['status_approve']=$this->config->item('system_status_pending');
            $item_head['remarks_approve_rollback']=$item_head['remarks_approve'];
            $item_head['date_rollbacked']=$time;
            $item_head['user_rollbacked']=$user->user_id;
            Query_helper::update($this->config->item('table_pos_expense_outlet_monthly'),$item_head,array('id='.$id));
        }
        else
        {
            $item_head['remarks_approve']=$item_head['remarks_approve'];
            $item_head['date_approved']=$time;
            $item_head['user_approved']=$user->user_id;
            Query_helper::update($this->config->item('table_pos_expense_outlet_monthly'),$item_head,array('id='.$id));
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
