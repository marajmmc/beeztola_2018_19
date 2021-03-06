<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Transfer_oo_receive extends Root_Controller
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
        elseif($action=="challan_print")
        {
            $this->system_challan_print($id);
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
        $data['division_name']= 1;
        $data['zone_name']= 1;
        $data['territory_name']= 1;
        $data['district_name']= 1;
        $data['quantity_total_request']= 1;
        $data['quantity_total_approve']= 1;
        $data['quantity_total_receive']= 1;
        $data['quantity_total_difference']= 1;
        if($method=='list_all')
        {
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
            $data['system_preference_items']=System_helper::get_preference($user->user_id,$this->controller_url,$method,$this->get_preference_headers($method));
            $data['title']="Showroom to Showroom Transfer Receive";
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
        $this->db->select(
            '
            transfer_oo.id,
            transfer_oo.date_request,
            transfer_oo.outlet_id_source,
            transfer_oo.outlet_id_destination,
            transfer_oo.quantity_total_request_kg quantity_total_request,
            transfer_oo.quantity_total_approve_kg quantity_total_approve,
            transfer_oo.quantity_total_receive_kg quantity_total_receive
            ');
        $this->db->join($this->config->item('table_login_csetup_cus_info').' customer_info','customer_info.customer_id=transfer_oo.outlet_id_source AND customer_info.revision=1','LEFT');
        $this->db->select('customer_info.name outlet_name_source, customer_info.customer_code outlet_code_source');
        $this->db->where('transfer_oo.status !=',$this->config->item('system_status_delete'));
        $this->db->where('transfer_oo.status_delivery',$this->config->item('system_status_delivered'));
        $this->db->where('transfer_oo.status_receive',$this->config->item('system_status_pending'));
        $this->db->where('transfer_oo.status_receive_forward',$this->config->item('system_status_pending'));
        //$this->db->where_in('transfer_oo.outlet_id_source',$this->user_outlet_ids);
        $this->db->where_in('transfer_oo.outlet_id_destination',$this->user_outlet_ids);
        $this->db->order_by('transfer_oo.id','DESC');
        $results=$this->db->get()->result_array();
        $items=array();
        foreach($results as $result)
        {
            $item=array();
            $item['id']=$result['id'];
            $item['barcode']=Barcode_helper::get_barcode_transfer_outlet_to_outlet($result['id']);
            $item['outlet_name_source']=$result['outlet_name_source'].' ('.$result['outlet_code_source'].')';
            $item['outlet_name_destination']=$this->outlets[$result['outlet_id_destination']]['name'].' ('.$this->outlets[$result['outlet_id_destination']]['customer_code'].')';
            $item['date_request']=System_helper::display_date($result['date_request']);
            $item['quantity_total_approve']=number_format($result['quantity_total_approve'],3,'.','');
            $item['quantity_total_receive']=number_format($result['quantity_total_receive'],3,'.','');
            $item['quantity_total_difference']=number_format(($result['quantity_total_approve']-$result['quantity_total_receive']),3,'.','');
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
            $data['title']="Showroom to Showroom Transfer Receive All List";
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

        $user=User_helper::get_user();
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
        $this->db->join($this->config->item('table_login_csetup_cus_info').' customer_info','customer_info.customer_id=transfer_oo.outlet_id_source AND customer_info.revision=1','LEFT');
        $this->db->select('customer_info.name outlet_name_source, customer_info.customer_code outlet_code_source');
        $this->db->where('transfer_oo.status !=',$this->config->item('system_status_delete'));
        $this->db->where('transfer_oo.status_delivery',$this->config->item('system_status_delivered'));
        //$this->db->where_in('transfer_oo.outlet_id_source',$this->user_outlet_ids);
        $this->db->where_in('transfer_oo.outlet_id_destination',$this->user_outlet_ids);
        $this->db->order_by('transfer_oo.id','DESC');
        $this->db->limit($pagesize,$current_records);
        $results=$this->db->get()->result_array();
        $items=array();
        foreach($results as $result)
        {
            $item=array();
            $item['id']=$result['id'];
            $item['barcode']=Barcode_helper::get_barcode_transfer_outlet_to_outlet($result['id']);
            $item['outlet_name_source']=$result['outlet_name_source'].' ('.$result['outlet_code_source'].')';
            $item['outlet_name_destination']=$this->outlets[$result['outlet_id_destination']]['name'].' ('.$this->outlets[$result['outlet_id_destination']]['customer_code'].')';
            $item['date_request']=System_helper::display_date($result['date_request']);
            $item['quantity_total_request']=number_format($result['quantity_total_request'],3,'.','');
            $item['quantity_total_approve']=number_format($result['quantity_total_approve'],3,'.','');
            $item['quantity_total_receive']=number_format($result['quantity_total_receive'],3,'.','');
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
            $this->db->join($this->config->item('table_login_csetup_cus_info').' customer_info','customer_info.customer_id=transfer_oo.outlet_id_source AND customer_info.revision=1','LEFT');
            $this->db->select('customer_info.name outlet_name_source, customer_info.customer_code outlet_code_source');
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
            if(!in_array($data['item']['outlet_id_destination'], $this->user_outlet_ids))
            {
                System_helper::invalid_try('save',$id,'User Outlet Not Assign (Destination)');
                $ajax['status']=false;
                $ajax['system_message']='Invalid Try. Destination outlet not assign.';
                $this->json_return($ajax);
            }
            if($data['item']['status_delivery']!=$this->config->item('system_status_delivered'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Showroom to showroom transfer is not delivered.';
                $this->json_return($ajax);
            }
            if($data['item']['status_receive']==$this->config->item('system_status_received'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Showroom to showroom transfer already received.';
                $this->json_return($ajax);
            }
            if($data['item']['status_receive_forward']==$this->config->item('system_status_forwarded'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Showroom to showroom transfer already forwarded.';
                $this->json_return($ajax);
            }
            if($data['item']['status_receive_approve']==$this->config->item('system_status_approved'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Showroom to showroom transfer already approved.';
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
            $data['stocks']=Stock_helper::get_variety_stock($data['item']['outlet_id_destination'],$variety_ids);

            $data['title']=$data['item']['outlet_name_source']." to ".$this->outlets[$data['item']['outlet_id_destination']]['name']." Transfer Receive Edit :: ". Barcode_helper::get_barcode_transfer_outlet_to_outlet($data['item']['id']);
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
        if($item_head['status_receive']!=$this->config->item('system_status_received'))
        {
            $ajax['status']=false;
            $ajax['system_message']='Receive field is required.';
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
        if(!in_array($result['item']['outlet_id_destination'], $this->user_outlet_ids))
        {
            System_helper::invalid_try('save',$id,'User Outlet Not Assign (Destination)');
            $ajax['status']=false;
            $ajax['system_message']='Invalid Try. Destination outlet not assign.';
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
        if($result['item']['status_delivery']!=$this->config->item('system_status_delivered'))
        {
            $ajax['status']=false;
            $ajax['system_message']='Showroom to showroom transfer is not delivered.';
            $this->json_return($ajax);
        }
        if($result['item']['status_receive']==$this->config->item('system_status_received'))
        {
            $ajax['status']=false;
            $ajax['system_message']='Showroom to showroom transfer already received.';
            $this->json_return($ajax);
        }

        $this->db->from($this->config->item('table_sms_transfer_oo_details').' details');
        $this->db->select('details.*');
        $this->db->join($this->config->item('table_login_setup_classification_varieties').' v','v.id=details.variety_id','INNER');
        $this->db->join($this->config->item('table_login_setup_classification_crop_types').' crop_type','crop_type.id=v.crop_type_id','INNER');
        $this->db->join($this->config->item('table_login_setup_classification_crops').' crop','crop.id=crop_type.crop_id','INNER');
        $this->db->where('details.transfer_oo_id',$id);
        $this->db->where('details.status',$this->config->item('system_status_active'));
        $data['items']=$this->db->get()->result_array();

        //$quantity_total_receive_kg=0;
        $old_items=array();
        $variety_ids=array();
        foreach($data['items'] as $item)
        {
            $old_items[$item['variety_id']][$item['pack_size_id']]=$item;
            $variety_ids[$item['variety_id']]=$item['variety_id'];
        }

        $current_stocks=Stock_helper::get_variety_stock($result['item']['outlet_id_destination'],$variety_ids);
        $status_quantity_deference=false;
        $quantity_total_receive_kg=0;
        foreach($items as $item)
        {
            if(!isset($old_items[$item['variety_id']][$item['pack_size_id']]))
            {
                $ajax['status']=false;
                $ajax['system_message']='Invalid variety information :: ( Variety ID: '.$item['variety_id'].' )';
                $this->json_return($ajax);
            }
            if(!($old_items[$item['variety_id']][$item['pack_size_id']]['quantity_approve']==$item['quantity_receive']))
            {
                $status_quantity_deference=true;
            }

            $quantity_total_receive_kg+=(($item['quantity_receive']*$old_items[$item['variety_id']][$item['pack_size_id']]['pack_size'])/1000);
        }

        $this->db->trans_start();

        if($status_quantity_deference)
        {
            $data=array();
            $data['date_receive']=$time;
            $data['quantity_total_receive_kg']=$quantity_total_receive_kg;
            $data['status_receive']=$this->config->item('system_status_pending');
            $data['status_receive_forward']=$this->config->item('system_status_pending');
            $data['status_receive_approve']=$this->config->item('system_status_pending');
            $data['status_system_delivery_receive']=$this->config->item('system_status_no');
            $data['remarks_receive_forward']=$item_head['remarks_receive_forward'];

            $data['date_updated_receive_forward']=$time;
            $data['user_updated_receive_forward']=$user->user_id;

            $this->db->set('revision_count_receive', 'revision_count_receive+1', FALSE);
            Query_helper::update($this->config->item('table_sms_transfer_oo'),$data, array('id='.$id), false);
            foreach($items as $item)
            {
                if($old_items[$item['variety_id']][$item['pack_size_id']]['quantity_receive']!=$item['quantity_receive'])
                {
                    $data=array();
                    $data['quantity_receive']=$item['quantity_receive'];
                    Query_helper::update($this->config->item('table_sms_transfer_oo_details'),$data, array('transfer_oo_id='.$id, 'variety_id ='.$item['variety_id'], 'pack_size_id ='.$item['pack_size_id']), false);
                }
            }
            $this->message='Showroom to showroom transfer not received, Go to forward option and send for approval. Approve quantity is not equal to receive quantity.';
        }
        else
        {
            $data=array();
            $data['date_receive']=$time;
            $data['quantity_total_receive_kg']=$quantity_total_receive_kg;
            $data['status_receive']=$item_head['status_receive'];
            $data['status_receive_forward']=$this->config->item('system_status_forwarded');
            $data['status_receive_approve']=$this->config->item('system_status_approved');
            $data['status_system_delivery_receive']=$this->config->item('system_status_yes');
            $data['remarks_receive_forward']=$item_head['remarks_receive_forward'];

            $data['date_updated_receive']=$time;
            $data['user_updated_receive']=$user->user_id;

            $data['date_updated_receive_forward']=$time;
            $data['user_updated_receive_forward']=$user->user_id;

            $data['date_updated_receive_approve']=$time;
            $data['user_updated_receive_approve']=$user->user_id;

            Query_helper::update($this->config->item('table_sms_transfer_oo'),$data, array('id='.$id));
            foreach($items as $item)
            {
                if($old_items[$item['variety_id']][$item['pack_size_id']]['quantity_receive']!=$item['quantity_receive'])
                {
                    $data=array();
                    $data['quantity_receive']=$item['quantity_receive'];
                    Query_helper::update($this->config->item('table_sms_transfer_oo_details'),$data, array('transfer_oo_id='.$id, 'variety_id ='.$item['variety_id'], 'pack_size_id ='.$item['pack_size_id']), false);
                }

                if(isset($current_stocks[$item['variety_id']][$item['pack_size_id']]))
                {
                    $current_stock=$current_stocks[$item['variety_id']][$item['pack_size_id']]['current_stock'];
                    $data=array();
                    $data['current_stock']=($current_stock+$item['quantity_receive']);
                    $data['in_oo']=($current_stocks[$item['variety_id']][$item['pack_size_id']]['in_oo']+$item['quantity_receive']);
                    $data['date_updated'] = $time;
                    $data['user_updated'] = $user->user_id;
                    Query_helper::update($this->config->item('table_pos_stock_summary_variety'),$data,array('variety_id='.$item['variety_id'],'pack_size_id='.$item['pack_size_id'],'outlet_id='.$result['item']['outlet_id_destination']));
                }
                else
                {
                    $data=array();
                    $data['variety_id']=$item['variety_id'];
                    $data['pack_size_id']=$item['pack_size_id'];
                    $data['outlet_id']=$result['item']['outlet_id_destination'];
                    $data['in_oo']=$item['quantity_receive'];
                    $data['current_stock']=$item['quantity_receive'];
                    $data['date_updated'] = $time;
                    $data['user_updated'] = $user->user_id;
                    Query_helper::add($this->config->item('table_pos_stock_summary_variety'),$data);
                }
            }
            $this->message=$this->lang->line("MSG_SAVED_SUCCESS");
        }

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

            $this->db->from($this->config->item('table_sms_transfer_oo').' transfer_oo');
            $this->db->select('transfer_oo.*');
            $this->db->join($this->config->item('table_login_csetup_cus_info').' customer_info','customer_info.customer_id=transfer_oo.outlet_id_source AND customer_info.revision=1','LEFT');
            $this->db->select('customer_info.name outlet_name_source, customer_info.customer_code outlet_code_source');
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
                System_helper::invalid_try('forward',$item_id,'Forward Non Exists');
                $ajax['status']=false;
                $ajax['system_message']='Invalid Try.';
                $this->json_return($ajax);
            }
            if(!in_array($data['item']['outlet_id_destination'], $this->user_outlet_ids))
            {
                System_helper::invalid_try('save',$id,'User Outlet Not Assign (Destination)');
                $ajax['status']=false;
                $ajax['system_message']='Invalid Try. Destination outlet not assign.';
                $this->json_return($ajax);
            }
            if($data['item']['status_delivery']!=$this->config->item('system_status_delivered'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Showroom to showroom transfer is not delivered.';
                $this->json_return($ajax);
            }
            if($data['item']['status_receive']==$this->config->item('system_status_received'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Showroom to showroom transfer already received.';
                $this->json_return($ajax);
            }
            if($data['item']['status_receive_forward']==$this->config->item('system_status_forwarded'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Showroom to showroom transfer already forwarded.';
                $this->json_return($ajax);
            }
            if($data['item']['status_receive_approve']==$this->config->item('system_status_approved'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Showroom to showroom transfer already approved.';
                $this->json_return($ajax);
            }
            if($data['item']['status_system_delivery_receive']!=$this->config->item('system_status_no'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Receive showroom to showroom transfer first and after then forward.';
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
            $data['stocks']=Stock_helper::get_variety_stock($data['item']['outlet_id_destination'],$variety_ids);

            $data['title']=$data['item']['outlet_name_source']." to ".$this->outlets[$data['item']['outlet_id_destination']]['name']." Transfer Receive Forward :: ". Barcode_helper::get_barcode_transfer_outlet_to_outlet($data['item']['id']);
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
        //$items=$this->input->post('items');
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
        if($item_head['status_receive_forward']!=$this->config->item('system_status_forwarded'))
        {
            $ajax['status']=false;
            $ajax['system_message']='Receive forward field is required.';
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
            System_helper::invalid_try('save_forward',$id,'Update Forward Non Exists');
            $ajax['status']=false;
            $ajax['system_message']='Invalid Try.';
            $this->json_return($ajax);
        }
        if(!in_array($result['item']['outlet_id_destination'], $this->user_outlet_ids))
        {
            System_helper::invalid_try('save_forward',$id,'User Outlet Not Assign (Destination)');
            $ajax['status']=false;
            $ajax['system_message']='Invalid Try. Destination outlet not assign.';
            $this->json_return($ajax);
        }
        if($result['item']['status_delivery']!=$this->config->item('system_status_delivered'))
        {
            $ajax['status']=false;
            $ajax['system_message']='Showroom to showroom transfer is not delivered.';
            $this->json_return($ajax);
        }
        if($result['item']['status_receive']==$this->config->item('system_status_received'))
        {
            $ajax['status']=false;
            $ajax['system_message']='Showroom to showroom transfer already received.';
            $this->json_return($ajax);
        }
        if($result['item']['status_receive_forward']==$this->config->item('system_status_forwarded'))
        {
            $ajax['status']=false;
            $ajax['system_message']='Showroom to showroom transfer already forwarded.';
            $this->json_return($ajax);
        }
        if($result['item']['status_receive_approve']==$this->config->item('system_status_approved'))
        {
            $ajax['status']=false;
            $ajax['system_message']='Showroom to showroom transfer already receive approved.';
            $this->json_return($ajax);
        }
        if($result['item']['status_system_delivery_receive']!=$this->config->item('system_status_no'))
        {
            $ajax['status']=false;
            $ajax['system_message']='Receive showroom to showroom transfer after then forward.';
            $this->json_return($ajax);
        }

        $this->db->trans_start();

        $data=array();
        $data['date_receive']=$time;
        $data['status_receive']=$this->config->item('system_status_pending');
        $data['status_receive_forward']=$item_head['status_receive_forward'];
        $data['status_receive_approve']=$this->config->item('system_status_pending');
        $data['status_system_delivery_receive']=$this->config->item('system_status_no');
        $data['remarks_receive_forward']=$item_head['remarks_receive_forward'];
        $data['date_updated_receive_forward']=$time;
        $data['user_updated_receive_forward']=$user->user_id;
        //$this->db->set('revision_count_receive', 'revision_count_receive+1', FALSE);
        Query_helper::update($this->config->item('table_sms_transfer_oo'),$data, array('id='.$id), false);

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

            $this->db->join($this->config->item('table_login_csetup_cus_info').' customer_info','customer_info.customer_id=transfer_oo.outlet_id_source AND customer_info.revision=1','LEFT');
            $this->db->select('customer_info.name outlet_name_source, customer_info.customer_code outlet_code_source');

            $this->db->join($this->config->item('table_pos_setup_user_info').' pos_user_receive','pos_user_receive.user_id=transfer_oo.user_updated_receive AND pos_user_receive.revision=1','LEFT');
            $this->db->select('pos_user_receive.name full_name_receive');
            $this->db->join($this->config->item('table_pos_setup_user_info').' pos_user_receive_forward','pos_user_receive_forward.user_id=transfer_oo.user_updated_receive_forward AND pos_user_receive_forward.revision=1','LEFT');
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
            if(!in_array($data['item']['outlet_id_destination'], $this->user_outlet_ids))
            {
                System_helper::invalid_try('save',$id,'User Outlet Not Assign (Destination)');
                $ajax['status']=false;
                $ajax['system_message']='Invalid Try. Destination outlet not assign.';
                $this->json_return($ajax);
            }

            $user_ids=array();
            $user_ids[$data['item']['user_created_request']]=$data['item']['user_created_request'];
            $user_ids[$data['item']['user_updated_request']]=$data['item']['user_updated_request'];
            $user_ids[$data['item']['user_updated_forward']]=$data['item']['user_updated_forward'];
            $user_ids[$data['item']['user_updated_approve']]=$data['item']['user_updated_approve'];
            $user_ids[$data['item']['user_updated_approve_forward']]=$data['item']['user_updated_approve_forward'];
            $user_ids[$data['item']['user_updated_delivery']]=$data['item']['user_updated_delivery'];
            $user_ids[$data['item']['user_updated_delivery_forward']]=$data['item']['user_updated_delivery_forward'];
            $user_ids[$data['item']['user_updated_receive_approve']]=$data['item']['user_updated_receive_approve'];
            //$data['users']=$this->get_sms_users_info($user_ids);
            $data['users_login']=$this->get_sms_users_info($user_ids);
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

            $data['title']=$data['item']['outlet_name_source']." to ".$this->outlets[$data['item']['outlet_id_destination']]['name']." Transfer Details :: ". Barcode_helper::get_barcode_transfer_outlet_to_outlet($data['item']['id']);
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
            /*outlet_info_source.customer_id outlet_id,
                outlet_info_source.customer_code outlet_code,
                outlet_info_source.address outlet_address,*/
            $this->db->join($this->config->item('table_login_csetup_cus_info').' outlet_info_destination','outlet_info_destination.customer_id=transfer_oo.outlet_id_destination AND outlet_info_destination.type="'.$this->config->item('system_customer_type_outlet_id').'"','INNER');
            $this->db->select(
                '
                outlet_info_destination.name outlet_destination_name,
                outlet_info_destination.phone outlet_destination_phone
                ');

            /*$this->db->join($this->config->item('table_login_setup_location_districts').' districts','districts.id = destination_outlet_info.district_id','INNER');
            $this->db->select('districts.id district_id, districts.name district_name');
            $this->db->join($this->config->item('table_login_setup_location_territories').' territories','territories.id = districts.territory_id','INNER');
            $this->db->select('territories.id territory_id, territories.name territory_name');
            $this->db->join($this->config->item('table_login_setup_location_zones').' zones','zones.id = territories.zone_id','INNER');
            $this->db->select('zones.id zone_id, zones.name zone_name');
            $this->db->join($this->config->item('table_login_setup_location_divisions').' divisions','divisions.id = zones.division_id','INNER');
            $this->db->select('divisions.id division_id, divisions.name division_name');*/
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
            $this->db->where('outlet_info_source.revision',1);
            $this->db->where('outlet_info_destination.revision',1);
            $this->db->order_by('transfer_oo.id','DESC');
            $data['item']=$this->db->get()->row_array();
            if(!$data['item'])
            {
                System_helper::invalid_try('challan_print',$item_id,'Challan Print Non Exists');
                $ajax['status']=false;
                $ajax['system_message']='Invalid Try.';
                $this->json_return($ajax);
            }
            if(!in_array($data['item']['outlet_id_destination'], $this->user_outlet_ids))
            {
                System_helper::invalid_try('save',$id,'User Outlet Not Assign (Destination)');
                $ajax['status']=false;
                $ajax['system_message']='Invalid Try. Destination outlet not assign.';
                $this->json_return($ajax);
            }
            if($data['item']['status_request']!=$this->config->item('system_status_forwarded'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Invalid Try. TO request not forwarded.';
                $this->json_return($ajax);
            }
            if($data['item']['status_approve']!=$this->config->item('system_status_approved'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Invalid Try. TO not approve & forwarded.';
                $this->json_return($ajax);
            }
            if($data['item']['status_approve']==$this->config->item('system_status_rejected'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Invalid Try. TO already rejected.';
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

            $data['title']=$data['item']['outlet_source_name']." to ".$data['item']['outlet_destination_name']." Transfer Challan Print View :: ". Barcode_helper::get_barcode_transfer_outlet_to_outlet($data['item']['id']);
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
