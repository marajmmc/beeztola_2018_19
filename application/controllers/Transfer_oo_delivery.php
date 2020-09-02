<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Transfer_oo_delivery extends Root_Controller
{
    public $message;
    public $permissions;
    public $controller_url;
    public $outlets;
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
                $this->outlets[$row['customer_id']]=$row;
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
        elseif($action=="save")
        {
            $this->system_save();
        }
        elseif($action=="details")
        {
            $this->system_details($id);
        }
        elseif($action=="challan_print")
        {
            $this->system_challan_print($id);
        }
        elseif($action=="delivery")
        {
            $this->system_delivery($id);
        }
        elseif($action=="save_delivery")
        {
            $this->system_save_delivery();
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
    private function get_preference_headers($method)
    {
        $data['id']= 1;
        $data['barcode']= 1;
        $data['outlet_name_source']= 1;
        $data['outlet_name_destination']= 1;
        $data['date_request']= 1;
        $data['quantity_total_request']= 1;
        $data['quantity_total_approve']= 1;
        if($method=='list_all')
        {
            $data['quantity_total_receive']= 1;
            $data['status_delivery']= 1;
            $data['status_receive']= 1;
            $data['status_receive_forward']= 1;
            $data['status_receive_approve']= 1;
            $data['status_system_delivery_receive']= 1;
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
            $data['title']="Showroom to Showroom Transfer Delivery";
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
        $this->db->from($this->config->item('table_sms_transfer_oo').' transfer_oo');
        $this->db->select('
        transfer_oo.id,
        transfer_oo.date_request,
        transfer_oo.outlet_id_source,
        transfer_oo.outlet_id_destination,
        transfer_oo.quantity_total_request_kg quantity_total_request,
        transfer_oo.quantity_total_approve_kg quantity_total_approve
        ');
        $this->db->join($this->config->item('table_login_csetup_cus_info').' customer_info','customer_info.customer_id=transfer_oo.outlet_id_destination AND customer_info.revision=1','LEFT');
        $this->db->select('customer_info.name outlet_name_destination');
        $this->db->where('transfer_oo.status !=',$this->config->item('system_status_delete'));
        $this->db->where('transfer_oo.status_approve',$this->config->item('system_status_approved'));
        $this->db->where('transfer_oo.status_delivery',$this->config->item('system_status_pending'));
        $this->db->where_in('transfer_oo.outlet_id_source',$this->user_outlet_ids);
        //$this->db->where_in('transfer_oo.outlet_id_destination',$this->user_outlet_ids);
        $this->db->order_by('transfer_oo.id','DESC');
        $results=$this->db->get()->result_array();
        $items=array();
        foreach($results as $result)
        {
            $item=array();
            $item['id']=$result['id'];
            $item['barcode']=Barcode_helper::get_barcode_transfer_outlet_to_outlet($result['id']);
            $item['outlet_name_source']=$this->outlets[$result['outlet_id_source']]['name'].' ('.$this->outlets[$result['outlet_id_source']]['customer_code'].')';
            $item['outlet_name_destination']=$result['outlet_name_destination'];
            $item['date_request']=System_helper::display_date($result['date_request']);
            $item['quantity_total_approve']=System_helper::get_string_kg($result['quantity_total_approve']);
            $items[]=$item;
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
            $data['title']="Showroom to Showroom Transfer Delivery All List";
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

        $this->db->from($this->config->item('table_sms_transfer_oo').' transfer_oo');
        $this->db->select(
            '
            transfer_oo.id,
            transfer_oo.date_request,
            transfer_oo.outlet_id_source,
            transfer_oo.outlet_id_destination,
            transfer_oo.quantity_total_request_kg quantity_total_request,
            transfer_oo.quantity_total_approve_kg quantity_total_approve,
            transfer_oo.quantity_total_receive_kg quantity_total_receive,
            transfer_oo.status, transfer_oo.status_request,
            transfer_oo.status_approve,
            transfer_oo.status_delivery,
            transfer_oo.status_receive,
            transfer_oo.status_receive_forward,
            transfer_oo.status_receive_approve,
            transfer_oo.status_system_delivery_receive
            ');
        $this->db->join($this->config->item('table_login_csetup_cus_info').' customer_info','customer_info.customer_id=transfer_oo.outlet_id_destination AND customer_info.revision=1','LEFT');
        $this->db->select('customer_info.name outlet_name_destination');
        $this->db->where('transfer_oo.status !=',$this->config->item('system_status_delete'));
        $this->db->where('transfer_oo.status_approve',$this->config->item('system_status_approved'));
        $this->db->where_in('transfer_oo.outlet_id_source',$this->user_outlet_ids);
        //$this->db->where_in('transfer_oo.outlet_id_destination',$this->user_outlet_ids);
        $this->db->order_by('transfer_oo.id','DESC');
        $this->db->limit($pagesize,$current_records);
        $results=$this->db->get()->result_array();
        $items=array();
        foreach($results as $result)
        {
            $item=array();
            $item['id']=$result['id'];
            $item['barcode']=Barcode_helper::get_barcode_transfer_outlet_to_outlet($result['id']);
            $item['outlet_name_source']=$this->outlets[$result['outlet_id_source']]['name'].' ('.$this->outlets[$result['outlet_id_source']]['customer_code'].')';
            $item['outlet_name_destination']=$result['outlet_name_destination'];
            $item['date_request']=System_helper::display_date($result['date_request']);
            $item['quantity_total_request']=System_helper::get_string_kg($result['quantity_total_request']);
            $item['quantity_total_approve']=System_helper::get_string_kg($result['quantity_total_approve']);
            $item['quantity_total_receive']=System_helper::get_string_kg($result['quantity_total_receive']);
            $item['status']=$result['status'];
            $item['status_request']=$result['status_request'];
            $item['status_approve']=$result['status_approve'];
            $item['status_delivery']=$result['status_delivery'];
            $item['status_receive']=$result['status_receive'];
            $item['status_receive_forward']=$result['status_receive_forward'];
            $item['status_receive_approve']=$result['status_receive_approve'];
            $item['status_system_delivery_receive']=$result['status_system_delivery_receive'];
            if($result['status_approve']==$this->config->item('system_status_rejected'))
            {
                $item['status_delivery']='';
                $item['status_receive']='';
                $item['status_receive_forward']='';
                $item['status_receive_approve']='';
                $item['status_system_delivery_receive']='';
            }
            if($result['status_system_delivery_receive']==$this->config->item('system_status_yes'))
            {
                $item['status_receive_forward']='';
                $item['status_receive_approve']='';
            }
            $items[]=$item;
        }
        $this->json_return($items);
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

            $this->db->from($this->config->item('table_sms_transfer_oo').' transfer_oo');
            $this->db->select('transfer_oo.*');
            $this->db->join($this->config->item('table_login_csetup_cus_info').' customer_info','customer_info.customer_id=transfer_oo.outlet_id_destination AND customer_info.revision=1','INNER');
            $this->db->select('customer_info.name outlet_name_destination');
            $this->db->join($this->config->item('table_sms_transfer_oo_courier_details').' wo_courier_details','wo_courier_details.transfer_oo_id=transfer_oo.id','LEFT');
            $this->db->select('
                                wo_courier_details.date_delivery courier_date_delivery,
                                wo_courier_details.date_challan,
                                wo_courier_details.challan_no,
                                wo_courier_details.courier_tracing_no,
                                wo_courier_details.place_booking_source,
                                wo_courier_details.place_destination,
                                wo_courier_details.date_booking,
                                wo_courier_details.remarks remarks_couriers
                                ');
            $this->db->join($this->config->item('table_login_basic_setup_couriers').' courier','courier.id=wo_courier_details.courier_id','LEFT');
            $this->db->select('courier.name courier_name');
            $this->db->join($this->config->item('table_login_setup_user_info').' ui_created','ui_created.user_id = transfer_oo.user_created_request AND ui_created.revision=1','LEFT');
            $this->db->select('ui_created.name user_created_full_name');
            $this->db->join($this->config->item('table_login_setup_user_info').' ui_updated','ui_updated.user_id = transfer_oo.user_updated_request AND ui_updated.revision=1','LEFT');
            $this->db->select('ui_updated.name user_updated_full_name');
            $this->db->join($this->config->item('table_login_setup_user_info').' ui_updated_approve','ui_updated_approve.user_id = transfer_oo.user_updated_approve AND ui_updated_approve.revision=1','LEFT');
            $this->db->select('ui_updated_approve.name user_updated_approve_full_name');
            $this->db->where('transfer_oo.status !=',$this->config->item('system_status_delete'));
            $this->db->where('transfer_oo.id',$item_id);
            $this->db->order_by('transfer_oo.id','DESC');
            $data['item']=$this->db->get()->row_array();

            if(!$data['item'])
            {
                System_helper::invalid_try('edit',$item_id,'Edit Non Exists');
                $ajax['status']=false;
                $ajax['system_message']='Invalid Try.';
                $this->json_return($ajax);
            }

            if(!in_array($data['item']['outlet_id_source'], $this->user_outlet_ids))
            {
                System_helper::invalid_try('edit',$id,'User Outlet Not Assign (Source)');
                $ajax['status']=false;
                $ajax['system_message']='Invalid Try. Source outlet not assign.';
                $this->json_return($ajax);
            }
            /*if(!in_array($data['item']['outlet_id_destination'], $this->user_outlet_ids))
            {
                System_helper::invalid_try('edit',$id,'User Outlet Not Assign (Destination)');
                $ajax['status']=false;
                $ajax['system_message']='Invalid Try. Destination outlet not assign.';
                $this->json_return($ajax);
            }*/
            if($data['item']['status_approve']!=$this->config->item('system_status_approved'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Showroom to showroom transfer is not approved.';
                $this->json_return($ajax);
            }
            if($data['item']['status_delivery']==$this->config->item('system_status_delivered'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Showroom to showroom transfer already delivered.';
                $this->json_return($ajax);
            }
            if($data['item']['status_receive']==$this->config->item('system_status_received'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Showroom to showroom transfer already received.';
                $this->json_return($ajax);
            }

            $this->db->from($this->config->item('table_sms_transfer_oo_details').' details');
            $this->db->select('details.*');
            $this->db->join($this->config->item('table_login_setup_classification_varieties').' v','v.id=details.variety_id','INNER');
            $this->db->select('v.name variety_name');
            $this->db->join($this->config->item('table_login_setup_classification_crop_types').' crop_type','crop_type.id=v.crop_type_id','INNER');
            $this->db->select('crop_type.id crop_type_id, crop_type.name crop_type_name');
            $this->db->join($this->config->item('table_login_setup_classification_crops').' crop','crop.id=crop_type.crop_id','INNER');
            $this->db->select('crop.id crop_id, crop.name crop_name');
            $this->db->where('details.transfer_oo_id',$item_id);
            $this->db->where('details.status',$this->config->item('system_status_active'));
            $data['items']=$this->db->get()->result_array();

            $variety_ids=array();
            foreach($data['items'] as $row)
            {
                $variety_ids[$row['variety_id']]=$row['variety_id'];
            }
            $data['stocks']=Stock_helper::get_variety_stock($data['item']['outlet_id_source'],$variety_ids);

            $data['couriers']=Query_helper::get_info($this->config->item('table_login_basic_setup_couriers'),array('id, name'),array('status="'.$this->config->item('system_status_active').'"'), '','',array('ordering'));
            $data['courier']=Query_helper::get_info($this->config->item('table_sms_transfer_oo_courier_details'),array('*'),array('transfer_oo_id='.$item_id),1);
            if(!$data['courier'])
            {
                $data['courier']['date_delivery']='';
                $data['courier']['date_challan']='';
                $data['courier']['challan_no']=Barcode_helper::get_barcode_transfer_outlet_to_outlet($data['item']['id']);
                $data['courier']['courier_id']='';
                $data['courier']['courier_tracing_no']='';
                $data['courier']['place_booking_source']='';
                $data['courier']['place_destination']='';
                $data['courier']['date_booking']='';
                $data['courier']['remarks']='';
            }

            $data['title']=$this->outlets[$data['item']['outlet_id_source']]['name']." to ".$data['item']['outlet_name_destination']." Transfer Delivery Edit :: ". Barcode_helper::get_barcode_transfer_outlet_to_outlet($data['item']['id']);
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
    private function system_save()
    {
        $id = $this->input->post("id");
        $user = User_helper::get_user();
        $time=time();
        $item_head=$this->input->post('item');
        $courier=$this->input->post('courier');
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

        $this->db->from($this->config->item('table_sms_transfer_oo').' transfer_oo');
        $this->db->select('transfer_oo.*');
        $this->db->where('transfer_oo.status !=',$this->config->item('system_status_delete'));
        $this->db->where('transfer_oo.id',$id);
        $this->db->order_by('transfer_oo.id','DESC');
        $result['item']=$this->db->get()->row_array();
        if(!$result['item'])
        {
            System_helper::invalid_try('save',$id,'Update Non Exists');
            $ajax['status']=false;
            $ajax['system_message']='Invalid Try.';
            $this->json_return($ajax);
        }
        if(!in_array($result['item']['outlet_id_source'], $this->user_outlet_ids))
        {
            System_helper::invalid_try('save',$id,'User Outlet Not Assign (Source)');
            $ajax['status']=false;
            $ajax['system_message']='Invalid Try. Source outlet not assign.';
            $this->json_return($ajax);
        }
        if($result['item']['status_approve']!=$this->config->item('system_status_approved'))
        {
            $ajax['status']=false;
            $ajax['system_message']='Showroom to showroom transfer not approved & forwarded.';
            $this->json_return($ajax);
        }
        if($result['item']['status_approve']==$this->config->item('system_status_rejected'))
        {
            $ajax['status']=false;
            $ajax['system_message']='Showroom to showroom transfer already rejected.';
            $this->json_return($ajax);
        }
        if($result['item']['status_delivery']==$this->config->item('system_status_delivered'))
        {
            $ajax['status']=false;
            $ajax['system_message']='Showroom to showroom transfer already delivered.';
            $this->json_return($ajax);
        }

        /*date validation*/
        if(System_helper::get_time($courier['date_delivery'])>0)
        {
            if(!(System_helper::get_time($courier['date_delivery'])>=System_helper::get_time(System_helper::display_date($result['item']['date_approve']))))
            {
                $ajax['status']=false;
                $ajax['system_message']='Delivery date should be is greater than approval date.';
                $this->json_return($ajax);
            }
        }

        $this->db->from($this->config->item('table_sms_transfer_oo_details').' details');
        $this->db->select('details.*');
        $this->db->join($this->config->item('table_login_setup_classification_varieties').' v','v.id=details.variety_id','INNER');
        $this->db->select('v.name variety_name');
        $this->db->join($this->config->item('table_login_setup_classification_crop_types').' crop_type','crop_type.id=v.crop_type_id','INNER');
        $this->db->select('crop_type.id crop_type_id, crop_type.name crop_type_name');
        $this->db->join($this->config->item('table_login_setup_classification_crops').' crop','crop.id=crop_type.crop_id','INNER');
        $this->db->select('crop.id crop_id, crop.name crop_name');
        $this->db->where('details.transfer_oo_id',$id);
        $this->db->where('details.status',$this->config->item('system_status_active'));
        $result['items']=$this->db->get()->result_array();

        $variety_ids=array();
        foreach($result['items'] as $item)
        {
            $variety_ids[$item['variety_id']]=$item['variety_id'];
        }

        $current_stocks=Stock_helper::get_variety_stock($result['item']['outlet_id_source'],$variety_ids);
        $quantity_total_approve_kg=0;
        foreach($result['items'] as $item)
        {
            if(!isset($current_stocks[$item['variety_id']][$item['pack_size_id']]) || !($current_stocks[$item['variety_id']][$item['pack_size_id']]['current_stock']>0))
            {
                $ajax['status']=false;
                $ajax['system_message']='Stock not available :: ( Variety ID: '.$item['variety_id'].' )';
                $this->json_return($ajax);
            }
            if($item['quantity_approve']>$current_stocks[$item['variety_id']][$item['pack_size_id']]['current_stock'])
            {
                $ajax['status']=false;
                $ajax['system_message']='Current Stock Exceed :: ( Variety ID: '.$item['variety_id'].' )';
                $this->json_return($ajax);
            }

            $quantity_total_approve_kg+=(($item['quantity_approve']*$item['pack_size'])/1000);
        }

        $this->db->trans_start();

        $result['courier']=Query_helper::get_info($this->config->item('table_sms_transfer_oo_courier_details'),array('*'),array('transfer_oo_id='.$id));
        if($result['courier'])
        {
            if(strtotime($courier['date_challan']))
            {
                $courier['date_challan']=System_helper::get_time($courier['date_challan']);
            }
            else
            {
                unset($courier['date_challan']);
            }
            if(strtotime($courier['date_booking']))
            {
                $courier['date_booking']=System_helper::get_time($courier['date_booking']);
            }
            else
            {
                unset($courier['date_booking']);
            }
            $courier['date_delivery']=System_helper::get_time($courier['date_delivery']);
            $courier['date_updated']=$time;
            $courier['user_updated']=$user->user_id;
            $this->db->set('revision_count', 'revision_count+1', FALSE);
            Query_helper::update($this->config->item('table_sms_transfer_oo_courier_details'),$courier, array('transfer_oo_id='.$id));
        }
        else
        {
            if(strtotime($courier['date_challan']))
            {
                $courier['date_challan']=System_helper::get_time($courier['date_challan']);
            }
            else
            {
                unset($courier['date_challan']);
            }
            if(strtotime($courier['date_booking']))
            {
                $courier['date_booking']=System_helper::get_time($courier['date_booking']);
            }
            else
            {
                unset($courier['date_booking']);
            }
            $courier['transfer_oo_id']=$id;
            $courier['date_delivery']=System_helper::get_time($courier['date_delivery']);
            $courier['revision_count']=1;
            $courier['date_updated']=$time;
            $courier['user_updated']=$user->user_id;
            Query_helper::add($this->config->item('table_sms_transfer_oo_courier_details'),$courier);
        }

        /* variety relational table insert & update*/
        $data=array();
        $data['quantity_total_receive_kg']=$quantity_total_approve_kg;
        $data['remarks_challan']=$item_head['remarks_challan'];
        $data['date_updated_delivery']=$time;
        $data['user_updated_delivery']=$user->user_id;
        Query_helper::update($this->config->item('table_sms_transfer_oo'),$data, array('id='.$id), false);

        $this->db->trans_complete();   //DB Transaction Handle END
        if ($this->db->trans_status() === TRUE)
        {
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
            $this->db->from($this->config->item('table_sms_transfer_oo').' transfer_oo');
            $this->db->select('transfer_oo.*');
            $this->db->join($this->config->item('table_login_csetup_cus_info').' customer_info','customer_info.customer_id=transfer_oo.outlet_id_destination AND customer_info.revision=1','INNER');
            $this->db->select('customer_info.name outlet_name_destination');
            $this->db->join($this->config->item('table_pos_setup_user_info').' pos_setup_user_info','pos_setup_user_info.user_id=transfer_oo.user_updated_delivery and pos_setup_user_info.revision=1','LEFT');
            $this->db->select('pos_setup_user_info.name full_name_delivery_edit');
            $this->db->join($this->config->item('table_pos_setup_user_info').' pos_setup_user_info_forward','pos_setup_user_info_forward.user_id=transfer_oo.user_updated_delivery_forward and pos_setup_user_info_forward.revision=1','LEFT');
            $this->db->select('pos_setup_user_info_forward.name full_name_delivery_forward');

            $this->db->join($this->config->item('table_pos_setup_user_info').' pos_user_receive','pos_user_receive.user_id=transfer_oo.user_updated_receive and pos_user_receive.revision=1','LEFT');
            $this->db->select('pos_user_receive.name full_name_receive');
            $this->db->join($this->config->item('table_pos_setup_user_info').' pos_user_receive_forward','pos_user_receive_forward.user_id=transfer_oo.user_updated_receive_forward and pos_user_receive_forward.revision=1','LEFT');
            $this->db->select('pos_user_receive_forward.name full_name_receive_forward');

            $this->db->join($this->config->item('table_sms_transfer_oo_courier_details').' wo_courier_details','wo_courier_details.transfer_oo_id=transfer_oo.id','LEFT');
            $this->db->select('
                                wo_courier_details.date_delivery courier_date_delivery,
                                wo_courier_details.date_challan,
                                wo_courier_details.challan_no,
                                wo_courier_details.courier_tracing_no,
                                wo_courier_details.place_booking_source,
                                wo_courier_details.place_destination,
                                wo_courier_details.date_booking,
                                wo_courier_details.remarks remarks_couriers
                                ');
            $this->db->join($this->config->item('table_login_basic_setup_couriers').' courier','courier.id=wo_courier_details.courier_id','LEFT');
            $this->db->select('courier.name courier_name');
            $this->db->where('transfer_oo.status !=',$this->config->item('system_status_delete'));
            $this->db->where('transfer_oo.id',$item_id);
            $this->db->order_by('transfer_oo.id','DESC');
            $data['item']=$this->db->get()->row_array();
            if(!$data['item'])
            {
                System_helper::invalid_try('details',$item_id,'View Non Exists');
                $ajax['status']=false;
                $ajax['system_message']='Invalid Try.';
                $this->json_return($ajax);
            }
            if(!in_array($data['item']['outlet_id_source'], $this->user_outlet_ids))
            {
                System_helper::invalid_try('save',$id,'User Outlet Not Assign (Source)');
                $ajax['status']=false;
                $ajax['system_message']='Invalid Try. Source outlet not assign.';
                $this->json_return($ajax);
            }

            $user_ids=array();
            $user_ids[$data['item']['user_created_request']]=$data['item']['user_created_request'];
            if($data['item']['user_updated_request']>0)
            {
                $user_ids[$data['item']['user_updated_request']]=$data['item']['user_updated_request'];
            }
            if($data['item']['user_updated_forward']>0)
            {
                $user_ids[$data['item']['user_updated_forward']]=$data['item']['user_updated_forward'];
            }
            if($data['item']['user_updated_approve']>0)
            {
                $user_ids[$data['item']['user_updated_approve']]=$data['item']['user_updated_approve'];
            }
            if($data['item']['user_updated_approve_forward']>0)
            {
                $user_ids[$data['item']['user_updated_approve_forward']]=$data['item']['user_updated_approve_forward'];
            }
            if($data['item']['user_updated_receive']>0)
            {
                $user_ids[$data['item']['user_updated_receive']]=$data['item']['user_updated_receive'];
            }
            if($data['item']['user_updated_receive_forward']>0)
            {
                $user_ids[$data['item']['user_updated_receive_forward']]=$data['item']['user_updated_receive_forward'];
            }
            $data['users']=$this->get_sms_users_info($user_ids);
            $data['users']=System_helper::get_users_info(($user_ids));

            $this->db->from($this->config->item('table_sms_transfer_oo_details').' details');
            $this->db->select('details.*');
            $this->db->join($this->config->item('table_login_setup_classification_varieties').' v','v.id=details.variety_id','INNER');
            $this->db->select('v.name variety_name');
            $this->db->join($this->config->item('table_login_setup_classification_crop_types').' crop_type','crop_type.id=v.crop_type_id','INNER');
            $this->db->select('crop_type.id crop_type_id, crop_type.name crop_type_name');
            $this->db->join($this->config->item('table_login_setup_classification_crops').' crop','crop.id=crop_type.crop_id','INNER');
            $this->db->select('crop.id crop_id, crop.name crop_name');
            $this->db->where('details.transfer_oo_id',$item_id);
            $this->db->where('details.status',$this->config->item('system_status_active'));
            $this->db->order_by('details.id');
            $data['items']=$this->db->get()->result_array();

            $data['title']=$this->outlets[$data['item']['outlet_id_source']]['name']." to ".$data['item']['outlet_name_destination']." Transfer Details :: ". Barcode_helper::get_barcode_transfer_outlet_to_outlet($data['item']['id']);
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
    private function system_challan_print($id)
    {
        if(isset($this->permissions['action4'])&&($this->permissions['action4']==1))
        {
            if($id>0)
            {
                $item_id=$id;
            }
            else
            {
                $item_id=$this->input->post('id');
            }

            $this->db->from($this->config->item('table_sms_transfer_oo').' transfer_oo');
            $this->db->select('transfer_oo.*');
            $this->db->join($this->config->item('table_login_csetup_cus_info').' outlet_info_source','outlet_info_source.customer_id=transfer_oo.outlet_id_source AND outlet_info_source.type="'.$this->config->item('system_customer_type_outlet_id').'"','INNER');
            $this->db->select(
                '
                outlet_info_source.name outlet_source_name,
                outlet_info_source.phone outlet_source_phone
                ');
            $this->db->join($this->config->item('table_login_csetup_cus_info').' outlet_info_destination','outlet_info_destination.customer_id=transfer_oo.outlet_id_destination AND outlet_info_destination.type="'.$this->config->item('system_customer_type_outlet_id').'"','INNER');
            $this->db->select(
                '
                outlet_info_destination.name outlet_destination_name,
                outlet_info_destination.phone outlet_destination_phone
                ');
            $this->db->join($this->config->item('table_sms_transfer_oo_courier_details').' wo_courier_details','wo_courier_details.transfer_oo_id=transfer_oo.id','LEFT');
            $this->db->select('
                                wo_courier_details.date_delivery courier_date_delivery,
                                wo_courier_details.date_challan,
                                wo_courier_details.challan_no,
                                wo_courier_details.courier_tracing_no,
                                wo_courier_details.place_booking_source,
                                wo_courier_details.place_destination,
                                wo_courier_details.date_booking,
                                wo_courier_details.remarks remarks_couriers
                                ');
            $this->db->join($this->config->item('table_login_basic_setup_couriers').' courier','courier.id=wo_courier_details.courier_id','LEFT');
            $this->db->select('courier.name courier_name');
            $this->db->join($this->config->item('table_login_setup_user_info').' ui_created','ui_created.user_id = transfer_oo.user_created_request and ui_created.revision=1','LEFT');
            $this->db->select('ui_created.name user_created_full_name');
            $this->db->join($this->config->item('table_login_setup_user_info').' ui_updated','ui_updated.user_id = transfer_oo.user_updated_request and ui_updated.revision=1','LEFT');
            $this->db->select('ui_updated.name user_updated_full_name');
            $this->db->join($this->config->item('table_login_setup_user_info').' ui_updated_approve','ui_updated_approve.user_id = transfer_oo.user_updated_approve and ui_updated_approve.revision=1','LEFT');
            $this->db->select('ui_updated_approve.name user_updated_approve_full_name');
            $this->db->where('transfer_oo.status !=',$this->config->item('system_status_delete'));
            $this->db->where('outlet_info_source.revision',1);
            $this->db->where('outlet_info_destination.revision',1);
            $this->db->where('transfer_oo.id',$item_id);
            $this->db->order_by('transfer_oo.id','DESC');
            $data['item']=$this->db->get()->row_array();
            if(!$data['item'])
            {
                System_helper::invalid_try('challan_print',$item_id,'Challan Print Non Exists');
                $ajax['status']=false;
                $ajax['system_message']='Invalid Try.';
                $this->json_return($ajax);
            }
            if(!in_array($data['item']['outlet_id_source'], $this->user_outlet_ids))
            {
                System_helper::invalid_try('save',$id,'User Outlet Not Assign (Source)');
                $ajax['status']=false;
                $ajax['system_message']='Invalid Try. Source outlet not assign.';
                $this->json_return($ajax);
            }
            if($data['item']['status_approve']!=$this->config->item('system_status_approved'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Invalid Try. TR not approve & forwarded.';
                $this->json_return($ajax);
            }
            if($data['item']['status_approve']==$this->config->item('system_status_rejected'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Invalid Try. TR already rejected.';
                $this->json_return($ajax);
            }

            $this->db->from($this->config->item('table_sms_transfer_oo_details').' details');
            $this->db->select('details.*');
            $this->db->join($this->config->item('table_login_setup_classification_varieties').' v','v.id=details.variety_id','INNER');
            $this->db->select('v.name variety_name');
            $this->db->join($this->config->item('table_login_setup_classification_crop_types').' crop_type','crop_type.id=v.crop_type_id','INNER');
            $this->db->select('crop_type.id crop_type_id, crop_type.name crop_type_name');
            $this->db->join($this->config->item('table_login_setup_classification_crops').' crop','crop.id=crop_type.crop_id','INNER');
            $this->db->select('crop.id crop_id, crop.name crop_name');
            $this->db->where('details.transfer_oo_id',$item_id);
            $this->db->where('details.status',$this->config->item('system_status_active'));
            $data['items']=$this->db->get()->result_array();

            $variety_ids=array();
            foreach($data['items'] as $row)
            {
                $variety_ids[$row['variety_id']]=$row['variety_id'];
            }
            $data['stocks']='';//Stock_helper::get_variety_stock($variety_ids);

            $data['title']=$data['item']['outlet_source_name']." to ".$data['item']['outlet_destination_name']." Transfer Delivery Challan Print View :: ". Barcode_helper::get_barcode_transfer_outlet_to_outlet($data['item']['id']);
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/challan_print",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/challan_print/'.$item_id);
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function system_delivery($id)
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

            $this->db->from($this->config->item('table_sms_transfer_oo').' transfer_oo');
            $this->db->select('transfer_oo.*');
            $this->db->join($this->config->item('table_login_csetup_cus_info').' customer_info','customer_info.customer_id=transfer_oo.outlet_id_destination AND customer_info.revision=1','INNER');
            $this->db->select('customer_info.name outlet_name_destination');
            $this->db->join($this->config->item('table_sms_transfer_oo_courier_details').' wo_courier_details','wo_courier_details.transfer_oo_id=transfer_oo.id','LEFT');
            $this->db->select('
                                wo_courier_details.date_delivery courier_date_delivery,
                                wo_courier_details.date_challan,
                                wo_courier_details.challan_no,
                                wo_courier_details.courier_tracing_no,
                                wo_courier_details.place_booking_source,
                                wo_courier_details.place_destination,
                                wo_courier_details.date_booking,
                                wo_courier_details.remarks remarks_couriers
                                ');
            $this->db->join($this->config->item('table_login_basic_setup_couriers').' courier','courier.id=wo_courier_details.courier_id','LEFT');
            $this->db->select('courier.name courier_name');
            $this->db->join($this->config->item('table_login_setup_user_info').' ui_created','ui_created.user_id = transfer_oo.user_created_request and ui_created.revision=1','LEFT');
            $this->db->select('ui_created.name user_created_full_name');
            $this->db->join($this->config->item('table_login_setup_user_info').' ui_updated','ui_updated.user_id = transfer_oo.user_updated_request and ui_updated.revision=1','LEFT');
            $this->db->select('ui_updated.name user_updated_full_name');
            $this->db->join($this->config->item('table_login_setup_user_info').' ui_updated_approve','ui_updated_approve.user_id = transfer_oo.user_updated_approve and ui_updated_approve.revision=1','LEFT');
            $this->db->select('ui_updated_approve.name user_updated_approve_full_name');
            $this->db->where('transfer_oo.status !=',$this->config->item('system_status_delete'));
            $this->db->where('transfer_oo.id',$item_id);
            $this->db->order_by('transfer_oo.id','DESC');
            $data['item']=$this->db->get()->row_array();
            if(!$data['item'])
            {
                System_helper::invalid_try('delivery',$item_id,'Delivery Non Exists');
                $ajax['status']=false;
                $ajax['system_message']='Invalid Try.';
                $this->json_return($ajax);
            }
            if(!in_array($data['item']['outlet_id_source'], $this->user_outlet_ids))
            {
                System_helper::invalid_try('save',$id,'User Outlet Not Assign (Source)');
                $ajax['status']=false;
                $ajax['system_message']='Invalid Try. Source outlet not assign.';
                $this->json_return($ajax);
            }
            if($data['item']['status_approve']!=$this->config->item('system_status_approved'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Showroom to showroom transfer is not approved.';
                $this->json_return($ajax);
            }
            if($data['item']['status_receive']==$this->config->item('system_status_received'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Showroom to showroom transfer already received.';
                $this->json_return($ajax);
            }
            if($data['item']['status_delivery']==$this->config->item('system_status_delivered'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Showroom to showroom transfer already delivered.';
                $this->json_return($ajax);
            }
            if(!($data['item']['courier_date_delivery']>0))
            {
                $ajax['status']=false;
                $ajax['system_message']='At first edit showroom to showroom transfer delivery & provide required information';
                $this->json_return($ajax);
            }
            if(!($data['item']['courier_date_delivery']>=System_helper::get_time(System_helper::display_date($data['item']['date_approve']))))
            {
                $ajax['status']=false;
                $ajax['system_message']='At first edit showroom to showroom transfer delivery & provide required information';
                $this->json_return($ajax);
            }

            $this->db->from($this->config->item('table_sms_transfer_oo_details').' details');
            $this->db->select('details.*');
            $this->db->join($this->config->item('table_login_setup_classification_varieties').' v','v.id=details.variety_id','INNER');
            $this->db->select('v.name variety_name');
            $this->db->join($this->config->item('table_login_setup_classification_crop_types').' crop_type','crop_type.id=v.crop_type_id','INNER');
            $this->db->select('crop_type.id crop_type_id, crop_type.name crop_type_name');
            $this->db->join($this->config->item('table_login_setup_classification_crops').' crop','crop.id=crop_type.crop_id','INNER');
            $this->db->select('crop.id crop_id, crop.name crop_name');
            $this->db->where('details.transfer_oo_id',$item_id);
            $this->db->where('details.status',$this->config->item('system_status_active'));
            $data['items']=$this->db->get()->result_array();

            $variety_ids=array();
            foreach($data['items'] as $row)
            {
                $variety_ids[$row['variety_id']]=$row['variety_id'];
            }
            $data['stocks']=Stock_helper::get_variety_stock($data['item']['outlet_id_source'],$variety_ids);

            $data['couriers']=Query_helper::get_info($this->config->item('table_login_basic_setup_couriers'),array('id, name'),array('status="'.$this->config->item('system_status_active').'"'), '','',array('ordering'));
            $data['courier']=Query_helper::get_info($this->config->item('table_sms_transfer_oo_courier_details'),array('*'),array('transfer_oo_id='.$item_id),1);
            if(!$data['courier'])
            {
                $data['courier']['date_delivery']='';
                $data['courier']['date_challan']='';
                $data['courier']['challan_no']=Barcode_helper::get_barcode_transfer_outlet_to_outlet($data['item']['id']);
                $data['courier']['courier_id']='';
                $data['courier']['courier_tracing_no']='';
                $data['courier']['place_booking_source']='';
                $data['courier']['place_destination']='';
                $data['courier']['date_booking']='';
                $data['courier']['remarks']='';
            }

            $data['title']=$this->outlets[$data['item']['outlet_id_source']]['name']." to ".$data['item']['outlet_name_destination']." Transfer Delivery Forward :: ". Barcode_helper::get_barcode_transfer_outlet_to_outlet($data['item']['id']);
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/delivery",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/delivery/'.$item_id);
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function system_save_delivery()
    {
        $id = $this->input->post("id");
        $user = User_helper::get_user();
        $time=time();
        $item_head=$this->input->post('item');
        if(!($id>0))
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
        if(!(isset($this->permissions['action7']) && ($this->permissions['action7']==1)))
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
        if($item_head['status_delivery']!=$this->config->item('system_status_delivered'))
        {
            $ajax['status']=false;
            $ajax['system_message']='Delivery field is required.';
            $this->json_return($ajax);
        }

        $this->db->from($this->config->item('table_sms_transfer_oo').' transfer_oo');
        $this->db->select('transfer_oo.*');
        $this->db->join($this->config->item('table_sms_transfer_oo_courier_details').' courier_details','courier_details.transfer_oo_id=transfer_oo.id','LEFT');
        $this->db->select('
                                courier_details.date_delivery courier_date_delivery,
                                courier_details.date_challan,
                                courier_details.challan_no,
                                courier_details.courier_tracing_no,
                                courier_details.place_booking_source,
                                courier_details.place_destination,
                                courier_details.date_booking,
                                courier_details.remarks remarks_couriers
                                ');
        $this->db->where('transfer_oo.status !=',$this->config->item('system_status_delete'));
        $this->db->where('transfer_oo.id',$id);
        $result['item']=$this->db->get()->row_array();
        if(!$result['item'])
        {
            System_helper::invalid_try('save_delivery',$id,'Update Delivery Non Exists');
            $ajax['status']=false;
            $ajax['system_message']='Invalid Try.';
            $this->json_return($ajax);
        }
        if(!in_array($result['item']['outlet_id_source'], $this->user_outlet_ids))
        {
            System_helper::invalid_try('save_delivery',$id,'User Outlet Not Assign (Source)');
            $ajax['status']=false;
            $ajax['system_message']='Invalid Try. Source outlet not assign.';
            $this->json_return($ajax);
        }
        if($result['item']['status_request']!=$this->config->item('system_status_forwarded'))
        {
            $ajax['status']=false;
            $ajax['system_message']='Showroom to showroom transfer is not request forwarded.';
            $this->json_return($ajax);
        }
        if($result['item']['status_approve']!=$this->config->item('system_status_approved'))
        {
            $ajax['status']=false;
            $ajax['system_message']='Showroom to showroom transfer not approved & forwarded.';
            $this->json_return($ajax);
        }
        if($result['item']['status_approve']==$this->config->item('system_status_rejected'))
        {
            $ajax['status']=false;
            $ajax['system_message']='Showroom to showroom transfer already rejected.';
            $this->json_return($ajax);
        }
        if($result['item']['status_delivery']==$this->config->item('system_status_delivered'))
        {
            $ajax['status']=false;
            $ajax['system_message']='Showroom to showroom transfer already delivered.';
            $this->json_return($ajax);
        }
        if(!($result['item']['courier_date_delivery']>0))
        {
            $ajax['status']=false;
            $ajax['system_message']=' Courier information is empty. '.$this->lang->line('LABEL_DATE_DELIVERY'). ' field is required.';
            $this->json_return($ajax);
        }
        if(!($result['item']['courier_date_delivery']>=System_helper::get_time(System_helper::display_date($result['item']['date_approve']))))
        {
            $ajax['status']=false;
            $ajax['system_message']=System_helper::display_date($result['item']['courier_date_delivery']).' Delivery date should be is greater than approval ('.System_helper::display_date($result['item']['date_approve']).') date.';
            $this->json_return($ajax);
        }

        /*if(System_helper::get_time($result['item']['date_challan'])>0)
        {
            if(!(System_helper::get_time(System_helper::display_date($result['item']['date_challan']))>=System_helper::get_time(System_helper::display_date($result['item']['courier_date_delivery']))))
            {
                $ajax['status']=false;
                $ajax['system_message']='Challan date should be is greater than delivery date.';
                $this->json_return($ajax);
            }
        }*/
        $this->db->from($this->config->item('table_sms_transfer_oo_details').' details');
        $this->db->select('details.*');
        $this->db->join($this->config->item('table_login_setup_classification_varieties').' v','v.id=details.variety_id','INNER');
        $this->db->join($this->config->item('table_login_setup_classification_crop_types').' crop_type','crop_type.id=v.crop_type_id','INNER');
        $this->db->join($this->config->item('table_login_setup_classification_crops').' crop','crop.id=crop_type.crop_id','INNER');
        $this->db->where('details.transfer_oo_id',$id);
        $this->db->where('details.status',$this->config->item('system_status_active'));
        $result['items']=$this->db->get()->result_array();

        $variety_ids=array();
        foreach($result['items'] as $item)
        {
            $variety_ids[$item['variety_id']]=$item['variety_id'];
        }

        $current_stocks=Stock_helper::get_variety_stock($result['item']['outlet_id_source'],$variety_ids);
        $quantity_total_approve_kg=0;
        foreach($result['items'] as $item)
        {
            if(!isset($current_stocks[$item['variety_id']][$item['pack_size_id']]) || !($current_stocks[$item['variety_id']][$item['pack_size_id']]['current_stock']>0))
            {
                $ajax['status']=false;
                $ajax['system_message']='Stock not available :: ( Variety ID: '.$item['variety_id'].' )';
                $this->json_return($ajax);
            }
            if($item['quantity_approve']>$current_stocks[$item['variety_id']][$item['pack_size_id']]['current_stock'])
            {
                $ajax['status']=false;
                $ajax['system_message']='Current Stock Exceed :: ( Variety ID: '.$item['variety_id'].' )';
                $this->json_return($ajax);
            }

            $quantity_total_approve_kg+=(($item['quantity_approve']*$item['pack_size'])/1000);
        }

        $this->db->trans_start();

        /* variety relational table insert & update*/
        $data=array();
        $data['date_delivery']=$time;
        $data['status_delivery']=$item_head['status_delivery'];
        $data['remarks_delivery']=$item_head['remarks_delivery'];
        $data['date_updated_delivery_forward']=$time;
        $data['user_updated_delivery_forward']=$user->user_id;
        Query_helper::update($this->config->item('table_sms_transfer_oo'),$data, array('id='.$id), false);

        /* this query execute just for getting revision count number match (revision_count_delivery)*/
        foreach($result['items'] as $item)
        {
            $current_stock=$current_stocks[$item['variety_id']][$item['pack_size_id']]['current_stock'];
            $data=array();
            $data['current_stock']=($current_stock-$item['quantity_approve']);
            $data['out_oo']=($current_stocks[$item['variety_id']][$item['pack_size_id']]['out_oo']+$item['quantity_approve']);
            $data['date_updated'] = $time;
            $data['user_updated'] = $user->user_id;
            Query_helper::update($this->config->item('table_pos_stock_summary_variety'),$data,array('variety_id='.$item['variety_id'],'pack_size_id='.$item['pack_size_id'],'outlet_id='.$result['item']['outlet_id_source']));
        }

        $this->db->trans_complete();   //DB Transaction Handle END
        if ($this->db->trans_status() === TRUE)
        {
            //$this->message=$this->message;
            $this->system_list();
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("MSG_SAVED_FAIL");
            $this->json_return($ajax);
        }
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
