<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Sales_manual_request extends Root_Controller
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
        $this->permissions=User_helper::get_permission('Sales_manual_request');
        $this->controller_url='sales_manual_request';
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

    public function index($action="list",$id=0)
    {
        if($action=="list")
        {
            $this->system_list($id);
        }
        elseif($action=='get_items')
        {
            $this->system_get_items();
        }
        elseif($action=="add")
        {
            $this->system_add();
        }
        elseif($action=="search_farmer")
        {
            $this->system_search_farmer();
        }
        elseif($action=="save_farmer")
        {
            $this->system_save_farmer();
        }
        elseif($action=="save")
        {
            $this->system_save();
        }
        elseif($action=="details")
        {
            $this->system_details($id);
        }

        elseif($action=="set_preference")
        {
            $this->system_set_preference();
        }
        elseif($action=="save_preference")
        {
            System_helper::save_preference();
        }
        else
        {
            $this->system_list($id);
        }
    }

    private function system_list()
    {
        if(isset($this->permissions['action0']) && ($this->permissions['action0']==1))
        {
            $data['system_preference_items']= $this->get_preference();
            $data['title']="List of Sales";
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

        $this->db->from($this->config->item('table_pos_sale_manual').' sale_manual');
        $this->db->select('sale_manual.*');
        $this->db->select('cus.name outlet_name');
        $this->db->select('f.name customer_name');
        $this->db->join($this->config->item('table_login_csetup_cus_info').' cus','cus.customer_id =sale_manual.outlet_id AND cus.revision=1','INNER');
        $this->db->join($this->config->item('table_pos_setup_farmer_farmer').' f','f.id = sale_manual.farmer_id','INNER');
        $this->db->where_in('sale_manual.outlet_id',$this->user_outlet_ids);
        $this->db->order_by('sale_manual.id DESC');
        $this->db->limit($pagesize,$current_records);
        $items=$this->db->get()->result_array();
        foreach($items as &$item)
        {
            $item['date_sale']=System_helper::display_date_time($item['date_sale']);
            if($item['status_approve']==$this->config->item('system_status_approved'))
            {
                $item['invoice_no']=Barcode_helper::get_barcode_sales($item['sale_id']);
            }
            else
            {
                $item['invoice_no']='N/A';
            }
            $item['amount_discount']=number_format($item['amount_discount_variety']+$item['amount_discount_self'],2);
            $item['amount_total']=number_format($item['amount_total'],2);
            $item['amount_actual']=number_format($item['amount_payable_actual'],2);

        }
        $this->json_return($items);
    }

    private function system_add()
    {
        if(isset($this->permissions['action1']) && ($this->permissions['action1']==1))
        {
            $data['title']="New Sale";
            $ajax['system_page_url']=site_url($this->controller_url."/index/add");
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/search",$data,true));
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
    private function system_search_farmer()
    {
        $outlet_id=$this->input->post("outlet_id");
        $code=$this->input->post("code");
        $code_type=Barcode_helper::get_farmer_code_type($code);
        $farmer_id=Barcode_helper::get_id_farmer($code);
        if($farmer_id>0)
        {

            $info=Query_helper::get_info($this->config->item('table_pos_setup_farmer_farmer'),'*',array('id ='.$farmer_id),1);
            if($info['status']==$this->config->item('system_status_inactive'))
            {
                $ajax['status']=false;
                if($code_type!='mobile')
                {
                    $ajax['hide_code']=true;
                }
                $ajax['system_message']='This Customer Cannot Buy Product.<br>Please Contact with admin';
                $this->json_return($ajax);
            }
            else
            {
                if(($info['status_card_require']==$this->config->item('system_status_yes'))&&($info['time_card_off_end']<=time())&&($code!=Barcode_helper::get_barcode_farmer($farmer_id)))
                {
                    $ajax['status']=false;
                    if($code_type!='mobile')
                    {
                        $ajax['hide_code']=true;
                    }
                    $ajax['system_message']='Scan Dealers Card';
                    $this->json_return($ajax);
                }
                if($info['farmer_type_id']>1)
                {
                    $result=Query_helper::get_info($this->config->item('table_pos_setup_farmer_outlet'),'*',array('farmer_id ='.$farmer_id,'revision =1','outlet_id ='.$outlet_id),1);
                    if(!$result)
                    {
                        $ajax['status']=false;
                        if($code_type!='mobile')
                        {
                            $ajax['hide_code']=true;
                        }
                        $ajax['system_message']='This Customer Cannot Buy Product from this outlet.<br>Please Contact with admin';
                        $this->json_return($ajax);
                    }
                }
                $this->system_load_sale_from($farmer_id,$outlet_id);

            }
        }
        else
        {
            $ajax['status']=false;
            $ajax['farmer_new']=true;
            if($code_type!='mobile')
            {
                $ajax['hide_code']=true;
            }
            $ajax['system_message']='Customer '.$this->lang->line("MSG_NOT_FOUND");
            $this->json_return($ajax);
        }
    }
    private function system_save_farmer()
    {
        $user = User_helper::get_user();
        $time=time();

        if(!(isset($this->permissions['action1']) && ($this->permissions['action1']==1)))
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
            die();
        }

        if(!$this->check_validation_save_farmer())
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->message;
            $this->json_return($ajax);
        }
        else
        {
            $this->db->trans_start();  //DB Transaction Handle START
            $data=array();
            $data['name'] = $this->input->post("name");
            $data['farmer_type_id'] = 1;
            $data['status_card_require'] = $this->config->item('system_status_no');
            $data['mobile_no'] = $this->input->post("mobile_no");
            $data['nid'] = $this->input->post("nid");
            $data['address'] = $this->input->post("address");
            $data['time_card_off_end'] = 0;
            $data['user_created'] = $user->user_id;
            $data['date_created'] = $time;
            $farmer_id=Query_helper::add($this->config->item('table_pos_setup_farmer_farmer'),$data);
            if(!$farmer_id)
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("MSG_SAVED_FAIL");
                $this->json_return($ajax);
            }

            $data=array();
            $data['farmer_id'] = $farmer_id;
            $data['outlet_id'] = $this->input->post("outlet_id");
            $data['revision'] = 1;
            $data['user_created'] = $user->user_id;
            $data['date_created'] = $time;
            Query_helper::add($this->config->item('table_pos_setup_farmer_outlet'),$data);

            $this->db->trans_complete();   //DB Transaction Handle END
            if ($this->db->trans_status() === TRUE)
            {

                $this->message=$this->lang->line("MSG_SAVED_SUCCESS");
                $this->system_load_sale_from($farmer_id,$this->input->post("outlet_id"));
            }
            else
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("MSG_SAVED_FAIL");
                $this->json_return($ajax);
            }
        }
    }
    private function check_validation_save_farmer()
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('outlet_id',$this->lang->line('LABEL_OUTLET_NAME'),'required');
        $this->form_validation->set_rules('name',$this->lang->line('LABEL_NAME'),'required');
        $this->form_validation->set_rules('mobile_no',$this->lang->line('LABEL_MOBILE_NO'),'required');

        if($this->form_validation->run() == FALSE)
        {
            $this->message=validation_errors();
            return false;
        }
        $mobile_no=$this->input->post("mobile_no");
        $exists=Query_helper::get_info($this->config->item('table_pos_setup_farmer_farmer'),array('id'),array('mobile_no ="'.$mobile_no.'"'),1);
        if($exists)
        {
            $this->message="Mobile No already Exists";
            return false;
        }
        return true;
    }
    private function system_load_sale_from($farmer_id,$outlet_id)
    {
        $data=array();
        $data['title']="New Sale";
        $result=Query_helper::get_info($this->config->item('table_login_csetup_cus_info'),'*',array('customer_id ='.$outlet_id,'revision =1'),1);
        $data['item']['outlet_id']=$outlet_id;
        $data['item']['outlet_name']=$result['name'];
        $data['item']['farmer_id']=$farmer_id;

        $this->db->from($this->config->item('table_pos_setup_farmer_farmer').' f');
        $this->db->select('f.*');
        $this->db->join($this->config->item('table_pos_setup_farmer_type').' ft','ft.id = f.farmer_type_id','INNER');
        $this->db->select('ft.name farmer_type_name,ft.discount_self_percentage');
        $this->db->where('f.id',$farmer_id);
        $result=$this->db->get()->row_array();
        $data['item']['farmer_name']=$result['name'];
        $data['item']['farmer_type_id']=$result['farmer_type_id'];
        $data['item']['mobile_no']=$result['mobile_no'];
        $data['item']['nid']=$result['nid'];
        $data['item']['address']=$result['address'];
        $data['item']['farmer_type_name']=$result['farmer_type_name'];
        $data['item']['discount_self_percentage']=$result['discount_self_percentage'];
        $data['item']['discount_message']='';

        $result=Query_helper::get_info($this->config->item('table_pos_setup_farmer_type_outlet_discount'),'*',array('farmer_type_id ='.$data['item']['farmer_type_id'],'expire_time >'.time(),'outlet_id ='.$outlet_id),1);
        if($result)
        {
            $data['item']['discount_self_percentage']=$result['discount_percentage'];
            $data['item']['discount_message']='Outlet Special Discount';
        }

        //getting current stock,price,discount of outlet
        $this->db->from($this->config->item('table_pos_stock_summary_variety').' ssv');
        $this->db->select('ssv.variety_id,ssv.pack_size_id,ssv.current_stock');
        $this->db->join($this->config->item('table_login_setup_classification_varieties').' v','v.id = ssv.variety_id','INNER');
        $this->db->select('v.name variety_name');
        $this->db->join($this->config->item('table_login_setup_classification_crop_types').' type','type.id = v.crop_type_id','INNER');
        $this->db->select('type.name crop_type_name,type.id crop_type_id');
        $this->db->join($this->config->item('table_login_setup_classification_crops').' crop','crop.id = type.crop_id','INNER');
        $this->db->select('crop.name crop_name,crop.id crop_id');
        $this->db->join($this->config->item('table_login_setup_classification_pack_size').' pack','pack.id = ssv.pack_size_id','INNER');
        $this->db->select('pack.name pack_size');

        $this->db->where('ssv.outlet_id',$outlet_id);
        $results=$this->db->get()->result_array();
        $varieties=array();
        foreach($results as $result)
        {
            $result['price_unit_pack']=0;
            $result['discount_percentage_variety']=0;
            $varieties[$result['variety_id']][$result['pack_size_id']]=$result;
        }
        $results=Query_helper::get_info($this->config->item('table_login_setup_classification_variety_price'),'*',array());
        foreach($results as $result)
        {
            if(isset($varieties[$result['variety_id']][$result['pack_size_id']]))
            {
                $varieties[$result['variety_id']][$result['pack_size_id']]['price_unit_pack']=$result['price'];
            }
        }
        $this->db->from($this->config->item('table_login_setup_classification_variety_outlet_discount').' outlet_discount');
        $this->db->select('variety_id,pack_size_id,discount_percentage');
        $this->db->where_in('outlet_id',array(0,$outlet_id));
        $this->db->where('farmer_type_id',$data['item']['farmer_type_id']);
        $this->db->where('expire_time >',time());
        $this->db->order_by('outlet_id','ASC');
        $results=$this->db->get()->result_array();
        foreach($results as $result)
        {
            if(isset($varieties[$result['variety_id']][$result['pack_size_id']]))
            {
                $varieties[$result['variety_id']][$result['pack_size_id']]['discount_percentage_variety']=$result['discount_percentage'];
            }
        }

        $data['sale_varieties_info']=array();
        foreach($varieties as $pack_sizes)
        {
            foreach($pack_sizes as $info)
            {
                $data['sale_varieties_info'][Barcode_helper::get_barcode_variety($info['crop_id'],$info['variety_id'],$info['pack_size_id'])]=$info;
            }
        }


        $ajax['status']=true;
        $ajax['system_page_url']=site_url($this->controller_url."/index/add");
        $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/add",$data,true));
        if($this->message)
        {
            $ajax['system_message']=$this->message;
        }
        $this->json_return($ajax);



    }
    private function system_save()
    {
        $user = User_helper::get_user();
        $time=time();
        $item=$this->input->post('item');
        $items=$this->input->post('items');
        if(!(isset($this->permissions['action1']) && ($this->permissions['action1']==1)))
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
            die();
        }
        if(!in_array($item['outlet_id'],$this->user_outlet_ids))
        {
            System_helper::invalid_try('Save',0,'outlet id '.$item['outlet_id'].' not assigned');
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
            die();
        }
        if(sizeof($items)==0)
        {
            $ajax['status']=false;
            $ajax['system_message']="No Item Added For Sale";
            $this->json_return($ajax);
            die();
        }
        if(!$this->check_validation())
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->message;
            $this->json_return($ajax);
        }
        //getting farmer info and discount validation

        $this->db->from($this->config->item('table_pos_setup_farmer_farmer').' f');
        $this->db->select('f.*');
        $this->db->join($this->config->item('table_pos_setup_farmer_type').' ft','ft.id = f.farmer_type_id','INNER');
        $this->db->select('ft.name farmer_type_name,ft.discount_self_percentage');
        $this->db->where('f.id',$item['farmer_id']);
        $this->db->where('f.status',$this->config->item('system_status_active'));
        $result=$this->db->get()->row_array();
        if(!$result)
        {
            $ajax['status']=false;
            $ajax['system_message']='Invalid Farmer';
            $this->json_return($ajax);
            die();
        }

        $stocks=Stock_helper::get_variety_stock($item['outlet_id']);
        $pack_sizes=array();
        $results=Query_helper::get_info($this->config->item('table_login_setup_classification_pack_size'),array('id value','name text'),array());
        foreach($results as $result)
        {
            $pack_sizes[$result['value']]=$result['text'];
        }

        $item_head_details=array();
        $item_head=array();
        $item_head['outlet_id']=$item['outlet_id'];
        $item_head['farmer_id']=$item['farmer_id'];
        $item_head['discount_self_percentage']=$item['discount_self_percentage'];
        $item_head['amount_total']=0;
        $item_head['amount_discount_variety']=0;
        foreach($items as $variety_id=>$packs)
        {
            foreach($packs as $pack_size_id=>$pack)
            {
                if($pack['quantity']>$stocks[$variety_id][$pack_size_id]['current_stock'])
                {
                    $ajax['status']=false;
                    $message='Not Enough Stock('.$variety_id.'-'.$pack_size_id.')';
                    $message.='<br>Current Stock('.$stocks[$variety_id][$pack_size_id]['current_stock'].')';
                    $ajax['system_message']=$message;
                    $this->json_return($ajax);
                    die();
                }

                $info=array();
                $info['variety_id']=$variety_id;
                $info['pack_size_id']=$pack_size_id;
                $info['pack_size']=$pack_sizes[$pack_size_id];
                $info['price_unit_pack']=$pack['price_unit_pack'];
                $info['quantity']=$pack['quantity'];
                $info['amount_total']=$pack['quantity']*$pack['price_unit_pack'];
                $info['discount_percentage_variety']=$pack['discount_percentage_variety'];
                $info['amount_discount_variety']=($info['amount_total']*$pack['discount_percentage_variety']/100);
                $info['amount_payable_actual']=($info['amount_total']-$info['amount_discount_variety']);
                $item_head_details[]=$info;

                $item_head['amount_total']+=($pack['price_unit_pack']*$pack['quantity']);
                $item_head['amount_discount_variety']+=($pack['price_unit_pack']*$pack['quantity']*$pack['discount_percentage_variety']/100);
            }
        }
        $item_head['amount_discount_self']=(($item_head['amount_total']-$item_head['amount_discount_variety'])*$item_head['discount_self_percentage']/100);
        $item_head['amount_payable']=($item_head['amount_total']-$item_head['amount_discount_variety']-$item_head['amount_discount_self']);
        $item_head['amount_payable_actual']=ceil($item_head['amount_payable']);
        if($item['amount_paid']>0)
        {
            $item_head['amount_cash']=$item['amount_paid'];
        }
        else
        {
            $item_head['amount_cash']=0;
        }
        if($item_head['amount_cash']<$item_head['amount_payable_actual'])
        {
            $ajax['status']=false;
            $ajax['system_message']="Payment amount cannot be less than purchase amount";
            $this->json_return($ajax);
            die();
        }


        $item_head['date_sale']=System_helper::get_time($item['date_sale']);
        $item_head['status_approve']=$this->config->item('system_status_pending');
        $item_head['date_manual_requested']=$time;
        $item_head['user_manual_requested']=$user->user_id;
        $item_head['remarks_manual_requested']=$item['remarks_manual_requested'];
        //getting current stock,price,discount of outlet finished
        $this->db->trans_start();  //DB Transaction Handle START
        $manual_sale_id=Query_helper::add($this->config->item('table_pos_sale_manual'),$item_head);
        if(!($manual_sale_id>0))
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("MSG_SAVED_FAIL");
            $this->json_return($ajax);
        }
        foreach($item_head_details as $data_details)
        {
            $data_details['manual_sale_id']=$manual_sale_id;
            Query_helper::add($this->config->item('table_pos_sale_manual_details'),$data_details);
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
    private function check_validation()
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('item[discount_self_percentage]',"Customer Discount",'required');
        $this->form_validation->set_rules('item[date_sale]',"Sales Date",'required');
        $this->form_validation->set_rules('item[remarks_manual_requested]','Manual Sales Reason','required');
        if($this->form_validation->run() == FALSE)
        {
            $this->message=validation_errors();
            return false;
        }
        return true;
    }
    private function system_details($id)
    {
        if(isset($this->permissions['action0']) && ($this->permissions['action0']==1))
        {
            if($id>0)
            {
                $item_id=$id;
            }
            else
            {
                $item_id=$this->input->post('id');
            }
            $this->db->from($this->config->item('table_pos_sale').' sale');
            $this->db->select('sale.*');
            $this->db->select('cus.name outlet_name,cus.name_short outlet_short_name');
            $this->db->select('f.name farmer_name,f.mobile_no,f.nid,f.address');
            $this->db->select('ft.name type_name');
            $this->db->join($this->config->item('table_login_csetup_cus_info').' cus','cus.customer_id =sale.outlet_id AND cus.revision=1','INNER');
            $this->db->join($this->config->item('table_pos_setup_farmer_farmer').' f','f.id = sale.farmer_id','INNER');
            $this->db->join($this->config->item('table_pos_setup_farmer_type').' ft','ft.id = f.farmer_type_id','INNER');
            $this->db->where('sale.id',$item_id);
            $data['item']=$this->db->get()->row_array();
            if(!$data['item'])
            {
                System_helper::invalid_try('Details',$item_id,'Trying to access Invalid Sale id');
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
                $this->json_return($ajax);
                die();
            }
            if(!in_array($data['item']['outlet_id'],$this->user_outlet_ids))
            {
                System_helper::invalid_try('Details',$item_id,'Trying to access other Outlets data');
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
                $this->json_return($ajax);
                die();
            }
            $this->db->from($this->config->item('table_pos_sale_details').' sd');
            $this->db->select('sd.*');

            $this->db->join($this->config->item('table_login_setup_classification_varieties').' v','v.id = sd.variety_id','INNER');
            $this->db->select('v.name variety_name');
            $this->db->join($this->config->item('table_login_setup_classification_crop_types').' type','type.id = v.crop_type_id','INNER');
            $this->db->select('type.name crop_type_name,type.id crop_type_id');
            $this->db->join($this->config->item('table_login_setup_classification_crops').' crop','crop.id = type.crop_id','INNER');
            $this->db->select('crop.name crop_name,crop.id crop_id');
            $this->db->where('sd.sale_id',$item_id);

            $data['items']=$this->db->get()->result_array();
            $data['has_variety_discount']=false;
            foreach($data['items'] as $row)
            {
                if($row['amount_discount_variety']>0)
                {
                    $data['has_variety_discount']=true;
                    break;
                }
            }

            $user_ids=array();
            $user_ids[$data['item']['user_created']]=$data['item']['user_created'];
            if($data['item']['user_cancel_approved']>0)
            {
                $user_ids[$data['item']['user_cancel_approved']]=$data['item']['user_cancel_approved'];
            }
            if($data['item']['user_manual_approved']>0)
            {
                $user_ids[$data['item']['user_manual_approved']]=$data['item']['user_manual_approved'];
            }

            $data['users']=System_helper::get_users_info($user_ids);
            $data['title']='Sale Details of ('.Barcode_helper::get_barcode_sales($item_id).')';

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
    private function get_preference()
    {
        $user = User_helper::get_user();
        $result=Query_helper::get_info($this->config->item('table_system_user_preference'),'*',array('user_id ='.$user->user_id,'controller ="' .$this->controller_url.'"','method ="list"'),1);
        $data['id']= 1;
        $data['outlet_name']= 1;
        $data['date_sale']= 1;
        $data['invoice_no']= 1;
        $data['customer_name']= 1;
        $data['amount_total']= 1;
        $data['amount_discount']= 1;
        $data['amount_actual']= 1;
        $data['status_approve']= 1;

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
}
