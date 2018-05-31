<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Budget_dealer_monthly extends Root_Controller
{
    public $message;
    public $permissions;
    public $controller_url;
    public function __construct()
    {
        parent::__construct();
        $this->message="";
        $this->permissions=User_helper::get_permission(get_class());
        $this->controller_url=strtolower(get_class());
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
        elseif($action=="add")
        {
            $this->system_add();
        }
        elseif($action=="variety_list")
        {
            $this->system_variety_list();
        }
        elseif($action=="get_items_variety")
        {
            $this->system_get_items_variety();
        }
        elseif($action=="save")
        {
            $this->system_save();
        }
        elseif($action=="details")
        {
            $this->system_details($id);
        }
        elseif($action=="forward")
        {
            $this->system_forward($id);
        }
        elseif($action=="save_forward")
        {
            $this->system_save_forward();
        }
        elseif($action=="set_preference")
        {
            $this->system_set_preference();
        }
        elseif($action=="set_preference_all")
        {
            $this->system_set_preference_all();
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
    private function system_list()
    {
        if(isset($this->permissions['action0'])&&($this->permissions['action0']==1))
        {
            $data['system_preference_items']= $this->get_preference();
            $data['title']="Monthly Dealer Budget List";
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
        //$items=Query_helper::get_info($this->config->item('table_pos_budget_dealer_monthly'),'*',array('status !="'.$this->config->item('system_status_delete').'"'));
        $user=User_helper::get_user();
        $this->db->from($this->config->item('table_pos_budget_dealer_monthly').' dealer_monthly');
        $this->db->select('dealer_monthly.*');
        $this->db->join($this->config->item('table_pos_setup_user_outlet').' user_outlet','user_outlet.customer_id=dealer_monthly.outlet_id AND user_outlet.revision=1','INNER');
        $this->db->join($this->config->item('table_login_csetup_customer').' outlet','outlet.id=user_outlet.customer_id AND outlet.status="'.$this->config->item('system_status_active').'"','INNER');
        $this->db->join($this->config->item('table_login_csetup_cus_info').' outlet_info','outlet_info.id=outlet.id AND outlet_info.revision=1 AND outlet_info.type="'.$this->config->item('system_customer_type_outlet_id').'"','INNER');
        $this->db->select('outlet_info.name outlet_name');
        $this->db->where('dealer_monthly.status !="'.$this->config->item('system_status_delete').'"');
        $this->db->where('dealer_monthly.status_forward',$this->config->item('system_status_pending'));
        $this->db->where('user_outlet.user_id',$user->user_id);
        $results=$this->db->get()->result_array();
        $items=array();
        foreach($results as $result)
        {
            $item=array();
            $item['id']=$result['id'];
            $item['outlet_name']=$result['outlet_name'];
            $item['month']=date("F", mktime(0, 0, 0,  $result['month_id'],1, 2000));
            $items[]=$item;
        }
        $this->json_return($items);
    }
    private function system_list_all()
    {
        if(isset($this->permissions['action0'])&&($this->permissions['action0']==1))
        {
            $data['system_preference_items']= $this->get_preference('list_all');
            $data['title']="Monthly Dealer Budget List";
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
        //$items=Query_helper::get_info($this->config->item('table_pos_budget_dealer_monthly'),'*',array('status !="'.$this->config->item('system_status_delete').'"'));
        $user=User_helper::get_user();
        $this->db->from($this->config->item('table_pos_budget_dealer_monthly').' dealer_monthly');
        $this->db->select('dealer_monthly.*');
        $this->db->join($this->config->item('table_pos_setup_user_outlet').' user_outlet','user_outlet.customer_id=dealer_monthly.outlet_id AND user_outlet.revision=1','INNER');
        $this->db->join($this->config->item('table_login_csetup_customer').' outlet','outlet.id=user_outlet.customer_id AND outlet.status="'.$this->config->item('system_status_active').'"','INNER');
        $this->db->join($this->config->item('table_login_csetup_cus_info').' outlet_info','outlet_info.id=outlet.id AND outlet_info.revision=1 AND outlet_info.type="'.$this->config->item('system_customer_type_outlet_id').'"','INNER');
        $this->db->select('outlet_info.name outlet_name');
        $this->db->where('dealer_monthly.status !="'.$this->config->item('system_status_delete').'"');
        $this->db->where('user_outlet.user_id',$user->user_id);
        $results=$this->db->get()->result_array();
        $items=array();
        foreach($results as $result)
        {
            $item=array();
            $item['id']=$result['id'];
            $item['outlet_name']=$result['outlet_name'];
            $item['month']=date("F", mktime(0, 0, 0,  $result['month_id'],1, 2000));
            $item['status_forward']=$result['status_forward'];
            $items[]=$item;
        }
        $this->json_return($items);
    }
    private function system_add()
    {
        if(isset($this->permissions['action0'])&&($this->permissions['action0']==1))
        {
            $user=User_helper::get_user();

            $data['user_outlet_ids']=array();
            //$data['user_outlets']=User_helper::get_assigned_outlets();
            $this->db->from($this->config->item('table_pos_setup_user_outlet').' user_outlet');
            $this->db->join($this->config->item('table_login_csetup_cus_info').' customer_info','customer_info.customer_id = user_outlet.customer_id','INNER');
            $this->db->select('customer_info.*');
            $this->db->where('user_outlet.revision',1);
            $this->db->where('customer_info.revision',1);
            $this->db->where('user_outlet.user_id',$user->user_id);
            $this->db->order_by('customer_info.ordering ASC');
            $data['user_outlets'] = $this->db->get()->result_array();

            if(sizeof($data['user_outlets'])>0)
            {
                foreach($data['user_outlets'] as $row)
                {
                    $data['user_outlet_ids'][]=$row['id'];
                }
            }
            else
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line('MSG_OUTLET_NOT_ASSIGNED');
                $this->json_return($ajax);
            }

            $data['item']['id']='';
            $data['title']="Monthly Dealer Budget Add";
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/add",$data,true));
            $ajax['status']=true;
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/add');
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function system_variety_list()
    {
        if(isset($this->permissions['action0'])&&($this->permissions['action0']==1))
        {
            $data['options']=$this->input->post();
            $outlet_id=$this->input->post('outlet_id');
            $month_id=$this->input->post('month_id');
            $crop_id=$this->input->post('crop_id');
            if(!$outlet_id || !$month_id || !$crop_id)
            {
                $ajax['status']=false;
                $ajax['system_message']='Invalid input. Following required field. Ex: Star mark (*)';
                $this->json_return($ajax);
            }

            $result=Query_helper::get_info($this->config->item('table_pos_budget_dealer_monthly'),'*',array('outlet_id ='.$outlet_id,'month_id ='.$month_id, 'status !="'.$this->config->item('system_status_delete').'"'),1);
            if($result['status_forward']==$this->config->item('system_status_forwarded'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Budget forwarded in this month ('.date("F", mktime(0, 0, 0,  $month_id,1, 2000)).')';
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

            $data['title']="Variety List";
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>$this->load->view($this->controller_url."/variety_list",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/add');
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function system_get_items_variety()
    {
        $outlet_id=$this->input->post('outlet_id');
        $month_id=$this->input->post('month_id');
        $crop_id=$this->input->post('crop_id');
        $items=$this->get_variety($outlet_id, $month_id, $crop_id);
        $this->json_return($items);
    }
    private function system_save()
    {
        $user = User_helper::get_user();
        $time=time();
        $item_head=$this->input->post('item');
        $items=$this->input->post('items');

        if(!(isset($this->permissions['action1']) && ($this->permissions['action1']==1)) || !(isset($this->permissions['action2']) && ($this->permissions['action2']==1)))
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }

        //$results=Query_helper::get_info($this->config->item('table_pos_budget_dealer_monthly'),'*',array('outlet_id ='.$item_head['outlet_id'],'month_id ='.$item_head['month_id'],'crop_id ='.$item_head['crop_id']));
        $this->db->from($this->config->item('table_pos_budget_dealer_monthly').' budget_dealer_monthly');
        $this->db->select('budget_dealer_monthly.*');
        $this->db->join($this->config->item('table_pos_budget_dealer_monthly_details').' details','budget_dealer_monthly.id=details.budget_dealer_monthly_id','INNER');
        $this->db->select('details.*, details.id details_id');
        $this->db->where('budget_dealer_monthly.status !="'.$this->config->item('system_status_delete').'"');
        $this->db->where('budget_dealer_monthly.outlet_id',$item_head['outlet_id']);
        $this->db->where('budget_dealer_monthly.month_id',$item_head['month_id']);
        $this->db->where('budget_dealer_monthly.crop_id',$item_head['crop_id']);
        $results=$this->db->get()->result_array();

        $old_items=array();
        foreach($results as $result)
        {
            $old_items[$result['variety_id']][$result['pack_size_id']][$result['dealer_id']]=$result;
        }

        $this->db->from($this->config->item('table_pos_setup_farmer_outlet').' farmer_outlet');
        $this->db->select('farmer_outlet.farmer_id');
        $this->db->join($this->config->item('table_pos_setup_farmer_farmer').' farmer_farmer','farmer_farmer.id=farmer_outlet.farmer_id','INNER');
        $this->db->select('farmer_farmer.name farmer_name');
        $this->db->where('farmer_farmer.status',$this->config->item('system_status_active'));
        $this->db->where('farmer_farmer.farmer_type_id > ',1);
        $this->db->where('farmer_outlet.revision',1);
        $this->db->where('farmer_outlet.outlet_id',$item_head['outlet_id']);
        $dealers=$this->db->get()->result_array();

        $this->db->trans_start();  //DB Transaction Handle START

        $budget_dealer_monthly_id=0;
        if(!$old_items)
        {
            $data=array();
            $data['outlet_id']=$item_head['outlet_id'];
            $data['month_id']=$item_head['month_id'];
            $data['crop_id']=$item_head['crop_id'];
            $data['revision_count']=1;
            $data['date_created']=$time;
            $data['user_created']=$user->user_id;
            $budget_dealer_monthly_id=Query_helper::add($this->config->item('table_pos_budget_dealer_monthly'),$data);
        }
        else
        {
            $data=array();
            $data['date_updated']=$time;
            $data['user_updated']=$user->user_id;
            $this->db->set('revision_count', 'revision_count+1', FALSE);
            Query_helper::update($this->config->item('table_pos_budget_dealer_monthly'),$data,array('outlet_id='.$item_head['outlet_id'], 'month_id='.$item_head['month_id'], 'crop_id='.$item_head['crop_id']));
        }

        $this->db->from($this->config->item('table_login_setup_classification_variety_price').' variety_price');
        $this->db->select('variety_price.id');
        $this->db->join($this->config->item('table_login_setup_classification_varieties').' v','v.id = variety_price.variety_id','INNER');
        $this->db->select('v.id variety_id');
        $this->db->join($this->config->item('table_login_setup_classification_crop_types').' crop_type','crop_type.id = v.crop_type_id','INNER');
        $this->db->select('crop_type.id crop_type_id');
        $this->db->join($this->config->item('table_login_setup_classification_pack_size').' pack','pack.id = variety_price.pack_size_id','INNER');
        $this->db->select('pack.id pack_size_id');
        $this->db->where('crop_type.crop_id',$item_head['crop_id']);
        $this->db->order_by('crop_type.ordering ASC');
        $this->db->order_by('v.ordering ASC');
        $results=$this->db->get()->result_array();

        foreach($results as $result)
        {
            foreach($dealers as $dealer)
            {
                if(isset($items[$result['variety_id']][$result['pack_size_id']][$dealer['farmer_id']]['amount_budget']))
                {
                    if(isset($old_items[$result['variety_id']][$result['pack_size_id']][$dealer['farmer_id']]['amount_budget']))
                    {
                        if($items[$result['variety_id']][$result['pack_size_id']][$dealer['farmer_id']]['amount_budget']!=$old_items[$result['variety_id']][$result['pack_size_id']][$dealer['farmer_id']]['amount_budget'])
                        {
                            $data=array();
                            $data['variety_id']=$result['variety_id'];
                            $data['pack_size_id']=$result['pack_size_id'];
                            $data['dealer_id']=$dealer['farmer_id'];
                            $data['amount_budget']=$items[$result['variety_id']][$result['pack_size_id']][$dealer['farmer_id']]['amount_budget'];
                            $this->db->set('revision_count', 'revision_count+1', FALSE);
                            Query_helper::update($this->config->item('table_pos_budget_dealer_monthly_details'),$data,array('id='.$old_items[$result['variety_id']][$result['pack_size_id']][$dealer['farmer_id']]['id']));
                        }
                    }
                    else
                    {
                        $data=array();
                        $data['budget_dealer_monthly_id']=$budget_dealer_monthly_id;
                        $data['variety_id']=$result['variety_id'];
                        $data['pack_size_id']=$result['pack_size_id'];
                        $data['dealer_id']=$dealer['farmer_id'];
                        $data['amount_budget']=$items[$result['variety_id']][$result['pack_size_id']][$dealer['farmer_id']]['amount_budget'];
                        $data['revision_count']=1;
                        Query_helper::add($this->config->item('table_pos_budget_dealer_monthly_details'),$data);
                    }
                }
            }
        }

        $this->db->trans_complete();   //DB Transaction Handle END
        if ($this->db->trans_status() === TRUE)
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("MSG_SAVED_SUCCESS");
            $this->json_return($ajax);
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
    private function get_variety($outlet_id, $month_id, $crop_id)
    {
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

        //$results=Query_helper::get_info($this->config->item('table_pos_budget_dealer_monthly_details'),'*',array('budget_dealer_monthly_id ='.$id));
        $this->db->from($this->config->item('table_pos_budget_dealer_monthly').' budget_dealer_monthly');
        $this->db->select('budget_dealer_monthly.*');
        $this->db->join($this->config->item('table_pos_budget_dealer_monthly_details').' details','budget_dealer_monthly.id=details.budget_dealer_monthly_id','INNER');
        $this->db->select('details.*, details.id details_id');
        $this->db->where('budget_dealer_monthly.status !="'.$this->config->item('system_status_delete').'"');
        $this->db->where('budget_dealer_monthly.outlet_id',$outlet_id);
        $this->db->where('budget_dealer_monthly.month_id',$month_id);
        //$this->db->where('budget_dealer_monthly.crop_id',$crop_id);
        $results=$this->db->get()->result_array();
        $crop_ids=array();
        foreach($results as $result)
        {
            $data[$result['variety_id']][$result['pack_size_id']][$result['dealer_id']]=$result;
            $crop_ids[$result['crop_id']]=$result['crop_id'];
        }

        $this->db->from($this->config->item('table_login_setup_classification_variety_price').' variety_price');
        $this->db->select('variety_price.id');
        $this->db->join($this->config->item('table_login_setup_classification_varieties').' v','v.id = variety_price.variety_id','INNER');
        $this->db->select('v.id variety_id,v.name variety_name');
        $this->db->join($this->config->item('table_login_setup_classification_crop_types').' crop_type','crop_type.id = v.crop_type_id','INNER');
        $this->db->select('crop_type.id crop_type_id,crop_type.name crop_type_name');
        $this->db->join($this->config->item('table_login_setup_classification_crops').' crop','crop.id=crop_type.crop_id','INNER');
        $this->db->select('crop.id crop_id, crop.name crop_name');
        $this->db->join($this->config->item('table_login_setup_classification_pack_size').' pack','pack.id = variety_price.pack_size_id','INNER');
        $this->db->select('pack.name pack_size,pack.id pack_size_id');
        $this->db->select('variety_price.variety_id quantity_min, variety_price.variety_id quantity_max');
        $this->db->where_in('crop_type.crop_id',$crop_ids);
        //$this->db->order_by('crop_type.ordering ASC');
        $this->db->order_by('crop.id, crop_type.id, v.id, pack.id ASC');
        $results=$this->db->get()->result_array();

        $items=array();
        foreach($results as $result)
        {
            $item=array();
            $item['id']=$result['id'];
            $item['crop_id']=$result['crop_id'];
            $item['crop_type_id']=$result['crop_type_id'];
            $item['variety_id']=$result['variety_id'];
            $item['pack_size_id']=$result['pack_size_id'];
            $item['crop_name']=$result['crop_name'];
            $item['crop_type_name']=$result['crop_type_name'];
            $item['variety_name']=$result['variety_name'];
            $item['pack_size']=$result['pack_size'];

            foreach($dealers as $dealer)
            {
                if(isset($data[$result['variety_id']][$result['pack_size_id']][$dealer['farmer_id']]))
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
    private function system_set_preference_all()
    {
        if(isset($this->permissions['action6']) && ($this->permissions['action6']==1))
        {
            $data['system_preference_items']=$this->get_preference('list_all');
            $data['preference_method_name']='list_all';
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("preference_add_edit",$data,true));
            $ajax['system_page_url']=site_url($this->controller_url.'/index/set_preference_all');
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function get_preference($method = 'list')
    {
        $user = User_helper::get_user();
        $result=Query_helper::get_info($this->config->item('table_system_user_preference'),'*',array('user_id ='.$user->user_id,'controller ="' .$this->controller_url.'"','method ="'.$method.'"'),1);
        $data['id']= 1;
        $data['outlet_name']= 1;
        $data['month']= 1;
        if($method=='list_all')
        {
            $data['status_forward']=1;
        }
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
