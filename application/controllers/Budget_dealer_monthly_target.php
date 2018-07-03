<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Budget_dealer_monthly_target extends Root_Controller
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
        elseif($action=="edit_target")
        {
            $this->system_edit_target($id);
        }
        elseif($action=="save_target")
        {
            $this->system_save_target();
        }
        elseif($action=="approve_rollback")
        {
            $this->system_approve_rollback($id);
        }
        elseif($action=="save_approve_rollback")
        {
            $this->system_save_approve_rollback();
        }
        elseif($action=="details")
        {
            $this->system_details($id);
        }
        elseif($action=="get_variety")
        {
            $this->system_get_variety();
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
        $data['outlet_name']= 1;
        $data['year']= 1;
        $data['month']= 1;
        $data['total_quantity_budget']= 1;
        $data['total_quantity_budget_kg']= 1;
        $data['total_amount_price_net']= 1;
        $data['total_quantity_target']= 1;
        $data['total_quantity_target_kg']= 1;
        $data['total_amount_target_price_net']= 1;
        if($method=='list_all')
        {
            //$data['status_forward']= 1;
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
            $data['title']="Monthly Target (Dealer Budget) Pending List";
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

        $this->db->select('SUM(dealer_monthly_total.quantity_budget_target_total) total_quantity_target');
        $this->db->select('SUM((dealer_monthly_total.quantity_budget_target_total*dealer_monthly_total.pack_size)/1000) total_quantity_target_kg');
        $this->db->select('SUM(dealer_monthly_total.amount_price_net*dealer_monthly_total.quantity_budget_target_total) total_amount_target_price_net');

        $this->db->where('dealer_monthly.status !="'.$this->config->item('system_status_delete').'"');
        $this->db->where('dealer_monthly.status_forward',$this->config->item('system_status_forwarded'));
        $this->db->where('dealer_monthly.status_budget_target',$this->config->item('system_status_pending'));
        $this->db->where_in('dealer_monthly.outlet_id',$this->user_outlet_ids);
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

        $this->db->where('dealer_monthly.status !="'.$this->config->item('system_status_delete').'"');
        $this->db->where('dealer_monthly.status_forward',$this->config->item('system_status_forwarded'));
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
    private function system_edit_target($id)
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
            $this->db->from($this->config->item('table_pos_budget_dealer_monthly').' dealer_monthly');
            $this->db->select('dealer_monthly.*');
            $this->db->join($this->config->item('table_pos_setup_user_outlet').' user_outlet','user_outlet.customer_id=dealer_monthly.outlet_id AND user_outlet.revision=1','INNER');
            $this->db->join($this->config->item('table_login_csetup_customer').' outlet','outlet.id=user_outlet.customer_id','INNER');
            $this->db->join($this->config->item('table_login_csetup_cus_info').' outlet_info','outlet_info.id=outlet.id AND outlet_info.revision=1 AND outlet_info.type="'.$this->config->item('system_customer_type_outlet_id').'"','INNER');
            $this->db->select('outlet_info.name outlet_name');
            $this->db->where('dealer_monthly.status !="'.$this->config->item('system_status_delete').'"');
            $this->db->where('dealer_monthly.id',$item_id);
            $data['item']=$this->db->get()->row_array();
            if(!$data['item'])
            {
                System_helper::invalid_try('edit_target',$item_id,'Edit Target Non Exists');
                $ajax['status']=false;
                $ajax['system_message']='Invalid Try.';
                $this->json_return($ajax);
            }
            if(!in_array($data['item']['outlet_id'],$this->user_outlet_ids))
            {
                System_helper::invalid_try('edit_target',$item_id,'Outlet not assign.');
                $ajax['status']=false;
                $ajax['system_message']='Invalid Try.';
                $this->json_return($ajax);
            }
            if($data['item']['status_forward']!=$this->config->item('system_status_forwarded'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Budget for ('.date("F-Y", mktime(0, 0, 0,  $data['item']['month'],1, $data['item']['year'])).') is not forwarded.';
                $this->json_return($ajax);
            }
            if($data['item']['status_budget_target']==$this->config->item('system_status_approved'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Budget target already approved';
                $this->json_return($ajax);
            }
            if($data['item']['status_budget_target']==$this->config->item('system_status_rejected'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Budget target already rejected';
                $this->json_return($ajax);
            }

            $user_ids=array();
            $user_ids[$data['item']['user_created']]=$data['item']['user_created'];
            $user_ids[$data['item']['user_forwarded']]=$data['item']['user_forwarded'];
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
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/edit_target",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/edit_target/'.$item_id);
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function system_save_target()
    {
        $id = $this->input->post("id");
        $user = User_helper::get_user();
        $time=time();
        //$item_head=$this->input->post('item');
        $items=$this->input->post('items');
        if($id>0)
        {
            if(!(isset($this->permissions['action2']) && ($this->permissions['action2']==1)))
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
                $this->json_return($ajax);
            }
            $result['item']=Query_helper::get_info($this->config->item('table_pos_budget_dealer_monthly'),'*',array('id ='.$id,'status !="'.$this->config->item('system_status_delete').'"'),1);
            if(!$result['item'])
            {
                System_helper::invalid_try('save_target',$id,'Update Target Non Exists');
                $ajax['status']=false;
                $ajax['system_message']='Invalid Try.';
                $this->json_return($ajax);
            }
            if(!in_array($result['item']['outlet_id'],$this->user_outlet_ids))
            {
                System_helper::invalid_try('save_target',$id,'Outlet not assign.');
                $ajax['status']=false;
                $ajax['system_message']='Invalid Try.';
                $this->json_return($ajax);
            }
            if($result['item']['status_forward']!=$this->config->item('system_status_forwarded'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Budget for ('.date("F-Y", mktime(0, 0, 0,  $result['item']['month'],1, $result['item']['year'])).') is not forwarded.';
                $this->json_return($ajax);
            }
            if($result['item']['status_budget_target']==$this->config->item('system_status_approved'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Budget target already approved';
                $this->json_return($ajax);
            }
            if($result['item']['status_budget_target']==$this->config->item('system_status_rejected'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Budget target already rejected';
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

        $this->db->from($this->config->item('table_pos_budget_dealer_monthly_total').' details');
        $this->db->select('details.*');
        $this->db->join($this->config->item('table_login_setup_classification_varieties').' v','v.id = details.variety_id','INNER');
        $this->db->join($this->config->item('table_login_setup_classification_crop_types').' crop_type','crop_type.id = v.crop_type_id','INNER');
        //$this->db->where('crop_type.crop_id',$item_head['crop_id']);
        $this->db->where('details.budget_monthly_id',$id);
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
                $data_total['quantity_budget_target_total']=$quantity_details['quantity_budget_target_total'];
                if(isset($total_old[$variety_id][$pack_size_id]))
                {
                    if(!(($data_total['quantity_budget_target_total']==$total_old[$variety_id][$pack_size_id]['quantity_budget_target_total'])&&($total_old[$variety_id][$pack_size_id]['status']==$this->config->item('system_status_active'))))
                    {
                        $data_total['status']=$this->config->item('system_status_active');
                        $this->db->set('revision_count_target', 'revision_count_target+1', FALSE);
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

        foreach($total_old_rows as $result)
        {
            $data=array();
            $data['status']=$this->config->item('system_status_delete');
            Query_helper::update($this->config->item('table_pos_budget_dealer_monthly_total'),$data, array('id='.$result['id']), false);
        }

        $data=array();
        $data['date_updated_target']=$time;
        $data['user_updated_target']=$user->user_id;
        Query_helper::update($this->config->item('table_pos_budget_dealer_monthly'),$data, array('id='.$id), false);

        $this->db->trans_complete();

        if ($this->db->trans_status() === TRUE)
        {
            $ajax['status']=true;
            $this->system_list();
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
            $this->db->join($this->config->item('table_login_csetup_cus_info').' outlet_info','outlet_info.id=outlet.id AND outlet_info.revision=1 AND outlet_info.type="'.$this->config->item('system_customer_type_outlet_id').'"','INNER');
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
            if($data['item']['status_forward']!=$this->config->item('system_status_forwarded'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Budget for ('.date("F-Y", mktime(0, 0, 0,  $data['item']['month'],1, $data['item']['year'])).') is not forwarded.';
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
    private function system_approve_rollback($id)
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
            $this->db->join($this->config->item('table_login_csetup_cus_info').' outlet_info','outlet_info.id=outlet.id AND outlet_info.revision=1 AND outlet_info.type="'.$this->config->item('system_customer_type_outlet_id').'"','INNER');
            $this->db->select('outlet_info.name outlet_name');
            $this->db->where('dealer_monthly.status !="'.$this->config->item('system_status_delete').'"');
            $this->db->where('dealer_monthly.id',$item_id);
            $data['item']=$this->db->get()->row_array();
            if(!$data['item'])
            {
                System_helper::invalid_try('approve_rollback',$item_id,'Approve Rollback Non Exists');
                $ajax['status']=false;
                $ajax['system_message']='Invalid Try.';
                $this->json_return($ajax);
            }
            if(!in_array($data['item']['outlet_id'],$this->user_outlet_ids))
            {
                System_helper::invalid_try('approve_rollback',$item_id,'Outlet not assign.');
                $ajax['status']=false;
                $ajax['system_message']='Invalid Try.';
                $this->json_return($ajax);
            }
            if($data['item']['status_forward']!=$this->config->item('system_status_forwarded'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Budget for ('.date("F-Y", mktime(0, 0, 0,  $data['item']['month'],1, $data['item']['year'])).') is not forwarded.';
                $this->json_return($ajax);
            }
            if($data['item']['status_budget_target']==$this->config->item('system_status_approved'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Budget target already approved';
                $this->json_return($ajax);
            }
            if($data['item']['status_budget_target']==$this->config->item('system_status_rejected'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Budget target already rejected';
                $this->json_return($ajax);
            }

            $user_ids=array();
            $user_ids[$data['item']['user_created']]=$data['item']['user_created'];
            $user_ids[$data['item']['user_forwarded']]=$data['item']['user_forwarded'];
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
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/approve_rollback",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/approve_rollback/'.$item_id);
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function system_save_approve_rollback()
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
            if($item_head['status_budget_target']!=$this->config->item('system_status_approved') && $item_head['status_budget_target']!=$this->config->item('system_status_rollback'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Approve/Rollback field is required.';
                $this->json_return($ajax);
            }

            /*if($item_head['status_budget_target']!=$this->config->item('system_status_rollback'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Approve/Rollback field is required.';
                $this->json_return($ajax);
            }*/
            if($item_head['status_budget_target']==$this->config->item('system_status_rollback'))
            {
                if(!$item_head['remarks_budget_target'])
                {
                    $ajax['status']=false;
                    $ajax['system_message']='Rollback Remarks field is required.';
                    $this->json_return($ajax);
                }
            }

            $data['item']=Query_helper::get_info($this->config->item('table_pos_budget_dealer_monthly'),'*',array('id ='.$id, 'status !="'.$this->config->item('system_status_delete').'"'),1);
            if(!$data['item'])
            {
                System_helper::invalid_try('save_approve_rollback',$id,'Save Approve Rollback Non Exists');
                $ajax['status']=false;
                $ajax['system_message']='Invalid Try.';
                $this->json_return($ajax);
            }
            if(!in_array($data['item']['outlet_id'],$this->user_outlet_ids))
            {
                System_helper::invalid_try('save_approve_rollback',$id,'Outlet not assign.');
                $ajax['status']=false;
                $ajax['system_message']='Invalid Try.';
                $this->json_return($ajax);
            }
            if($data['item']['status_forward']!=$this->config->item('system_status_forwarded'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Budget for ('.date("F-Y", mktime(0, 0, 0,  $data['item']['month'],1, $data['item']['year'])).') is not Forwarded';
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

        $data=array();
        if($item_head['status_budget_target']==$this->config->item('system_status_rollback'))
        {
            $data['remarks_forward_rollback']=$item_head['remarks_budget_target'];
            $data['date_forwarded_rollback']=$time;
            $data['user_forwarded_rollback']=$user->user_id;
            $data['status_forward']=$this->config->item('system_status_pending');
            $data['status_budget_target']=$this->config->item('system_status_pending');
            $this->db->set('revision_forward_rollback_count', 'revision_forward_rollback_count+1', FALSE);
        }
        else
        {
            $data['date_approved_target']=$time;
            $data['user_approved_target']=$user->user_id;
            $data['status_budget_target']=$item_head['status_budget_target'];
            $data['remarks_budget_target']=$item_head['remarks_budget_target'];
        }

        Query_helper::update($this->config->item('table_pos_budget_dealer_monthly'),$data,array('id='.$id));

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
        $data['quantity_budget_target_total']= 1;
        foreach($dealers as $dealer)
        {
            $data['quantity_budget_'.$dealer['farmer_id']]= 1;
        }
        return $data;
    }
    private function system_get_variety()
    {
        $budget_monthly_id=$this->input->post('id');

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
            $item=$this->initialize_row($result['crop_name'],$result['crop_type_name'],$result['variety_name'],$result['variety_id'],$result['pack_size'],$result['pack_size_id'],$result['amount_price_net'],$result['quantity_budget_target_total']);
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
    private function initialize_row($crop_name,$crop_type_name,$variety_name,$variety_id,$pack_size,$pack_size_id,$amount_price_net,$quantity_budget_target_total)
    {
        $row['crop_name']=$crop_name;
        $row['crop_type_name']=$crop_type_name;
        $row['variety_name']=$variety_name;
        $row['variety_id']=$variety_id;
        $row['pack_size']=$pack_size;
        $row['pack_size_id']=$pack_size_id;
        $row['amount_price_net']=$amount_price_net;
        $row['quantity_budget_target_total']=$quantity_budget_target_total;
        return $row;
    }

}
