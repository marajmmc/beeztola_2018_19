<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Sales_sale3 extends Root_Controller
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
            $this->system_set_preference('list');
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
    private function get_preference_headers($method)
    {
        $data=array();
        if($method=='list')
        {
            $data['id']= 1;
            $data['outlet_name']= 1;
            $data['date_sale']= 1;
            $data['invoice_no']= 1;
            $data['customer_name']= 1;
            $data['sales_payment_method']= 1;
            $data['amount_total']= 1;
            //$data['discount_slab_percentage']= 1;
            $data['amount_discount']= 1;
            $data['amount_actual']= 1;
        }
        return $data;
    }
    private function system_set_preference($method)
    {
        $user = User_helper::get_user();
        if(isset($this->permissions['action6']) && ($this->permissions['action6']==1))
        {
            $data['system_preference_items']=System_helper::get_preference($user->user_id,$this->controller_url,$method,$this->get_preference_headers($method));
            $data['preference_method_name']=$method;
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("preference_add_edit",$data,true));
            $ajax['system_page_url']=site_url($this->controller_url.'/index/set_preference_'.$method);
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
        if(isset($this->permissions['action0']) && ($this->permissions['action0']==1))
        {
            $data['system_preference_items']= System_helper::get_preference($user->user_id, $this->controller_url, $method, $this->get_preference_headers($method));
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

        $this->db->from($this->config->item('table_pos_sale').' sale');
        $this->db->select('sale.*');
        $this->db->select('cus.name outlet_name');
        $this->db->select('f.name customer_name');
        $this->db->join($this->config->item('table_login_csetup_cus_info').' cus','cus.customer_id =sale.outlet_id AND cus.revision=1','INNER');
        $this->db->join($this->config->item('table_pos_setup_farmer_farmer').' f','f.id = sale.farmer_id','INNER');
        $this->db->where_in('sale.outlet_id',$this->user_outlet_ids);
        $this->db->order_by('sale.id DESC');
        $this->db->limit($pagesize,$current_records);
        $items=$this->db->get()->result_array();
        foreach($items as &$item)
        {
            $item['date_sale']=System_helper::display_date_time($item['date_sale']);
            $item['invoice_no']=Barcode_helper::get_barcode_sales($item['id']);
            $item['discount_slab_percentage']=$item['discount_self_percentage'];
            $item['amount_discount']=$item['amount_discount_variety']+$item['amount_discount_self'];
            $item['amount_actual']=$item['amount_payable_actual'];
        }
        $this->json_return($items);
    }

    private function system_add()
    {
        if(isset($this->permissions['action1']) && ($this->permissions['action1']==1))
        {
            $data['title']="New Sale";
            $data['farmer_types']=Query_helper::get_info($this->config->item('table_pos_setup_farmer_type'),array('id value,name text'),array('status ="'.$this->config->item('system_status_active').'"','id >1'),0,0,array('ordering ASC'));
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
        $code_scan_type=$this->input->post("code_scan_type");
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
                $this->system_load_sale_from($farmer_id,$outlet_id,$code_scan_type);

            }
        }
        else
        {
            $this->db->from($this->config->item('table_login_csetup_cus_info').' cus_info');
            //$this->db->select('cus_info.name,cus_info.type,cus_info.customer_id id');
            $this->db->select('d.id district_id,d.territory_id territory_id');
            $this->db->join($this->config->item('table_login_setup_location_districts').' d','d.id = cus_info.district_id','INNER');
            $this->db->where('cus_info.customer_id',$outlet_id);
            $this->db->where('cus_info.revision',1);
            $outlet_info=$this->db->get()->row_array();

            $data['items']=Query_helper::get_info($this->config->item('table_login_setup_location_districts'),array('id value','name text'),array('territory_id ='.$outlet_info['territory_id'],'status ="'.$this->config->item('system_status_active').'"'),0,0,array('ordering ASC','id ASC'));


            $ajax['status']=false;
            $ajax['farmer_new']=true;
            if($code_type!='mobile')
            {
                $ajax['hide_code']=true;
            }
            $ajax['system_content'][]=array("id"=>'#district_id',"html"=>$this->load->view("dropdown_with_select",$data,true));
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
            $data['union_id'] = $this->input->post("union_id");
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
        //$this->form_validation->set_rules('union_id',$this->lang->line('LABEL_UNION_NAME'),'required');

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
    private function system_load_sale_from($farmer_id,$outlet_id,$code_scan_type='TYPE')
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
        $this->db->select('ft.name farmer_type_name,ft.allow_offer,ft.allow_discount');
        $this->db->where('f.id',$farmer_id);
        $result=$this->db->get()->row_array();
        $data['item']['farmer_name']=$result['name'];
        $data['item']['farmer_type_id']=$result['farmer_type_id'];
        $data['item']['mobile_no']=$result['mobile_no'];
        $data['item']['nid']=$result['nid'];
        $data['item']['address']=$result['address'];
        $data['item']['farmer_type_name']=$result['farmer_type_name'];
        $data['item']['code_scan_type']=$code_scan_type;
        $data['item']['amount_credit_limit']=$result['amount_credit_limit'];
        $data['item']['amount_credit_balance']=$result['amount_credit_balance'];

        $data['item']['allow_offer']=$result['allow_offer'];
        $data['item']['allow_discount']=$result['allow_discount'];

        //unregistered cannot buy product
        if(!($data['item']['farmer_type_id']>1))
        {
            $ajax['status']=false;
            if($this->message)
            {
                $ajax['system_message']=$this->message.'<br>'.$this->lang->line("MSG_NOT_DEALER_CANNOT_BUY");
            }
            else
            {
                $ajax['system_message']=$this->lang->line("MSG_NOT_DEALER_CANNOT_BUY");
            }

            $this->json_return($ajax);
        }
        //dealer cannot buy from other show room
        $result=Query_helper::get_info($this->config->item('table_pos_setup_farmer_outlet'),'*',array('farmer_id ='.$farmer_id,'revision =1','outlet_id ='.$outlet_id),1);
        if(!$result)
        {
            $data['item']['amount_credit_limit']=0;//if unregistered farmer
            $data['item']['amount_credit_balance']=0;//if unregistered farmer
            $ajax['status']=false;
            if($this->message)
            {
                $ajax['system_message']=$this->message.'<br>'.$this->lang->line("MSG_NOT_DEALER_CANNOT_BUY_OTHER_OUTLET");
            }
            else
            {
                $ajax['system_message']=$this->lang->line("MSG_NOT_DEALER_CANNOT_BUY_OTHER_OUTLET");
            }

            $this->json_return($ajax);
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
        $this->db->where('v.status',$this->config->item('system_status_active'));
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
                $price_farmers=(json_decode($result['price_farmers'],true));
                if(isset($price_farmers[$data['item']['farmer_type_id']]))
                {
                    $result['price']=$price_farmers[$data['item']['farmer_type_id']];
                }
                $varieties[$result['variety_id']][$result['pack_size_id']]['price_unit_pack']=$result['price'];

            }
        }
        //discount setups //Ignored

        $data['sale_varieties_info']=array();
        foreach($varieties as $pack_sizes)
        {
            foreach($pack_sizes as $info)
            {
                //$data['sale_varieties_info'][Barcode_helper::get_barcode_variety($info['crop_id'],$info['variety_id'],$info['pack_size_id'])]=$info;
                $data['sale_varieties_info'][Barcode_helper::get_barcode_variety($outlet_id,$info['variety_id'],$info['pack_size_id'])]=$info;
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
        $system_user_token = $this->input->post("system_user_token");
        $system_user_token_info = Token_helper::get_token($system_user_token);
        if($system_user_token_info['status'])
        {
            $this->message=$this->lang->line('MSG_SAVE_ALREADY');
            $this->system_list();
        }

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
            System_helper::invalid_try(__FUNCTION__,0,'outlet id '.$item['outlet_id'].' not assigned');
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

        //getting farmer info and discount validation

        $this->db->from($this->config->item('table_pos_setup_farmer_farmer').' f');
        $this->db->select('f.*');
        $this->db->join($this->config->item('table_pos_setup_farmer_type').' ft','ft.id = f.farmer_type_id','INNER');
        $this->db->select('ft.name farmer_type_name,ft.allow_offer,ft.allow_discount');
        $this->db->where('f.id',$item['farmer_id']);
        $result=$this->db->get()->row_array();

        if(!$result)
        {
            $ajax['status']=false;
            $ajax['system_message']='Invalid Farmer';
            $this->json_return($ajax);
            die();
        }
        $farmer_info=array();
        $farmer_info['farmer_id']=$result['id'];
        $farmer_info['farmer_name']=$result['name'];
        $farmer_info['farmer_type_id']=$result['farmer_type_id'];
        $farmer_info['mobile_no']=$result['mobile_no'];
        $farmer_info['nid']=$result['nid'];
        $farmer_info['address']=$result['address'];
        $farmer_info['amount_credit_limit']=$result['amount_credit_limit'];
        $farmer_info['amount_credit_balance']=$result['amount_credit_balance'];

        $farmer_info['allow_offer']=$result['allow_offer'];
        $farmer_info['allow_discount']=$result['allow_discount'];
        if($farmer_info['allow_discount']==$this->config->item('system_status_yes'))
        {
            if(!(strlen($item['amount_discount_self'])>0))
            {
                $ajax['status']=false;
                $ajax['system_message']="Please Enter discount amount.<br>Enter 0 if no discount";
                $this->json_return($ajax);
                die();
            }
        }
        //Unregistered farmer cannot buy
        if(!($farmer_info['farmer_type_id']>1))
        {
            $farmer_info['amount_credit_limit']=0;//if allow unregistered
            $farmer_info['amount_credit_balance']=0;//if allow unregistered

            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("MSG_NOT_DEALER_CANNOT_BUY");
            $this->json_return($ajax);

        }

        //dealer cannot buy from other show room
        $result=Query_helper::get_info($this->config->item('table_pos_setup_farmer_outlet'),'*',array('farmer_id ='.$item['farmer_id'],'revision =1','outlet_id ='.$item['outlet_id']),1);
        if(!$result)
        {
            $farmer_info['amount_credit_limit']=0;//if allow unregistered
            $farmer_info['amount_credit_balance']=0;//if allow unregistered
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("MSG_NOT_DEALER_CANNOT_BUY_OTHER_OUTLET");
            $this->json_return($ajax);
        }
        //discount slabs deleted

        //farmer info and discount validation finished

        //getting current stock,price,discount of outlet and preparing item head
        $this->db->from($this->config->item('table_pos_stock_summary_variety').' ssv');
        $this->db->select('ssv.variety_id,ssv.pack_size_id,ssv.current_stock,ssv.out_sale,ssv.id stock_id');
        $this->db->join($this->config->item('table_login_setup_classification_varieties').' v','v.id = ssv.variety_id','INNER');
        $this->db->select('v.name variety_name');
        $this->db->join($this->config->item('table_login_setup_classification_crop_types').' type','type.id = v.crop_type_id','INNER');
        $this->db->select('type.name crop_type_name,type.id crop_type_id');
        $this->db->join($this->config->item('table_login_setup_classification_crops').' crop','crop.id = type.crop_id','INNER');
        $this->db->select('crop.name crop_name,crop.id crop_id');
        $this->db->join($this->config->item('table_login_setup_classification_pack_size').' pack','pack.id = ssv.pack_size_id','INNER');
        $this->db->select('pack.name pack_size');

        $this->db->where('ssv.outlet_id',$item['outlet_id']);
        $results=$this->db->get()->result_array();
        $varieties=array();
        foreach($results as $result)
        {
            $result['price_unit_pack']=0;
            $result['discount_percentage_variety']=0;
            $varieties[$result['variety_id']][$result['pack_size_id']]=$result;
        }
        $this->db->from($this->config->item('table_login_setup_classification_variety_price').' price');
        $this->db->select('price.id,price.variety_id,price.pack_size_id,price.price price,price.price_farmers');
        $this->db->join($this->config->item('table_login_offer_setup_variety').' offer','offer.variety_id=price.variety_id AND offer.pack_size_id=price.pack_size_id AND offer.revision=1 AND offer.status="'.$this->config->item('system_status_active').'"','LEFT');
        $this->db->select('offer.status,offer.quantity_minimum,offer.amount_per_kg');

        $results=$this->db->get()->result_array();
        foreach($results as $result)
        {
            if(isset($varieties[$result['variety_id']][$result['pack_size_id']]))
            {
                $price_farmers=(json_decode($result['price_farmers'],true));
                if(isset($price_farmers[$farmer_info['farmer_type_id']]))
                {
                    $result['price']=$price_farmers[$farmer_info['farmer_type_id']];
                }

                $varieties[$result['variety_id']][$result['pack_size_id']]['price_unit_pack']=$result['price'];
                if($result['quantity_minimum']>0)
                {
                    $varieties[$result['variety_id']][$result['pack_size_id']]['offer_quantity_minimum']=$result['quantity_minimum'];
                }
                else
                {
                    $varieties[$result['variety_id']][$result['pack_size_id']]['offer_quantity_minimum']=0;
                }
                if($result['amount_per_kg']>0)
                {
                    $varieties[$result['variety_id']][$result['pack_size_id']]['offer_amount_per_kg']=$result['amount_per_kg'];
                }
                else
                {
                    $varieties[$result['variety_id']][$result['pack_size_id']]['offer_amount_per_kg']=0;
                }
            }
        }

        $item_head_details=array();
        $item_head=array();
        $item_head['outlet_id']=$item['outlet_id'];
        $item_head['outlet_id_commission']=$item['outlet_id'];
        $item_head['farmer_id']=$item['farmer_id'];
        $item_head['amount_discount_self']=$item['amount_discount_self'];
        //$item_head['discount_self_percentage']=$item['discount_self_percentage'];//calculate latter
        $item_head['amount_total']=0;
        $item_head['amount_discount_variety']=0;
        $item_head['code_scan_type']=$item['code_scan_type'];
        $item_head['offer_offered']=0;
        $item_head['offer_given']=0;
        if($farmer_info['allow_offer']==$this->config->item('system_status_yes'))
        {
            $item_head['offer_given']=$item['amount_discount_self'];
        }

        foreach($items as $variety_id=>$packs)
        {
            foreach($packs as $pack_size_id=>$pack)
            {
                if($pack['quantity']>$varieties[$variety_id][$pack_size_id]['current_stock'])
                {
                    $ajax['status']=false;
                    $message='Not Enough Stock('.$varieties[$variety_id][$pack_size_id]['variety_name'].'-'.$varieties[$variety_id][$pack_size_id]['pack_size'].')';
                    $message.='<br>Current Stock('.$varieties[$variety_id][$pack_size_id]['current_stock'].')';
                    $ajax['system_message']=$message;
                    $this->json_return($ajax);
                    die();
                }
                if($pack['price_unit_pack']!=$varieties[$variety_id][$pack_size_id]['price_unit_pack'])
                {
                    $ajax['status']=false;
                    $message='Price changed('.$varieties[$variety_id][$pack_size_id]['variety_name'].'-'.$varieties[$variety_id][$pack_size_id]['pack_size'].')';
                    $message.='<br>New Price('.$varieties[$variety_id][$pack_size_id]['price_unit_pack'].')';
                    $ajax['system_message']=$message;
                    $this->json_return($ajax);
                    die();
                }
                $info=array();
                $info['variety_id']=$variety_id;
                $info['pack_size_id']=$pack_size_id;
                $info['pack_size']=$varieties[$variety_id][$pack_size_id]['pack_size'];
                $info['price_unit_pack']=$pack['price_unit_pack'];
                $info['quantity']=$pack['quantity'];
                $info['amount_total']=$pack['quantity']*$pack['price_unit_pack'];
                //$info['discount_percentage_variety']=$pack['discount_percentage_variety'];//removed discount to offer
                $info['discount_percentage_variety']=0;
                $info['amount_discount_variety']=($info['amount_total']*$info['discount_percentage_variety']/100);
                $info['amount_payable_actual']=($info['amount_total']-$info['amount_discount_variety']);

                $info['offer_quantity_minimum']=$varieties[$variety_id][$pack_size_id]['offer_quantity_minimum'];
                $info['offer_amount_per_kg']=$varieties[$variety_id][$pack_size_id]['offer_amount_per_kg'];
                $info['offer_offered']=0;
                if($farmer_info['allow_offer']==$this->config->item('system_status_yes'))
                {
                    if(($info['quantity']*$info['pack_size']/1000)>=$info['offer_quantity_minimum'])
                    {
                        $info['offer_offered']=$info['quantity']*$info['pack_size']*$info['offer_amount_per_kg']/1000;
                    }
                }
                $item_head_details[]=$info;

                $item_head['amount_total']+=$info['amount_total'];
                $item_head['amount_discount_variety']+=($info['amount_total']*$info['discount_percentage_variety']/100);
                $item_head['offer_offered']+=$info['offer_offered'];
            }
        }
        $item_head['discount_self_percentage']=0;
        if($item_head['amount_total']>0)
        {
            $item_head['discount_self_percentage']=($item_head['amount_discount_self']*100/$item_head['amount_total']);
        }
        $item_head['amount_payable']=($item_head['amount_total']-$item_head['amount_discount_variety']-$item_head['amount_discount_self']);
        $item_head['amount_payable_actual']=ceil($item_head['amount_payable']);

        //offer validation
        if($item_head['amount_discount_self']>$item_head['amount_total'])
        {
            $ajax['status']=false;
            $ajax['system_message']="Discount amount cannot be higher than invoice amount.";
            $this->json_return($ajax);
            die();
        }
        $this->load->helper('offer');
        $offer_stat=Offer_helper::get_offer_stats(array($farmer_info['farmer_id']));
        if($farmer_info['allow_offer']==$this->config->item('system_status_yes'))
        {
            if(($offer_stat[$farmer_info['farmer_id']]['offer_balance']+$item_head['offer_offered'])<$item_head['offer_given'])
            {
                $ajax['status']=false;
                $ajax['system_message']="Reward Point amount is not valid.";
                $this->json_return($ajax);
            }
        }




        if($farmer_info['amount_credit_limit']>0)
        {
            if($item['amount_paid']>0)
            {
                $ajax['status']=false;
                $ajax['system_message']="Customer Credit setup changed.Please do New invoice";
                $this->json_return($ajax);
                die();
            }

            if($farmer_info['amount_credit_balance']<$item_head['amount_payable_actual'])
            {
                $ajax['status']=false;
                $ajax['system_message']="Customer does not have enough credit.";
                $this->json_return($ajax);
                die();
            }
            $item_head['amount_cash']=0;
            $item_head['sales_payment_method']='Credit';
        }
        else
        {
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
            $item_head['sales_payment_method']='Cash';
        }
        $item_head['date_sale']=$time;
        $item_head['status']=$this->config->item('system_status_active');
        $item_head['date_created']=$time;
        $item_head['user_created']=$user->user_id;

        //getting current stock,price,discount of outlet finished
        $this->load->helper('farmer_credit');
        $this->db->trans_start();  //DB Transaction Handle START
        $sale_id=Query_helper::add($this->config->item('table_pos_sale'),$item_head);
        if(!($sale_id>0))
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("MSG_SAVED_FAIL");
            $this->json_return($ajax);
        }
        foreach($item_head_details as $data_details)
        {

            $data_details['sale_id']=$sale_id;
            Query_helper::add($this->config->item('table_pos_sale_details'),$data_details);
            $data_stock=array();
            $data_stock['out_sale']=($varieties[$data_details['variety_id']][$data_details['pack_size_id']]['out_sale']+$data_details['quantity']);
            $data_stock['current_stock']=($varieties[$data_details['variety_id']][$data_details['pack_size_id']]['current_stock']-$data_details['quantity']);
            $data_stock['date_updated'] = $time;
            $data_stock['user_updated'] = $user->user_id;
            Query_helper::update($this->config->item('table_pos_stock_summary_variety'),$data_stock,array('id='.$varieties[$data_details['variety_id']][$data_details['pack_size_id']]['stock_id']));

        }
        if($farmer_info['amount_credit_limit']>0)
        {
            $data_history=array();
            $data_history['farmer_id']=$farmer_info['farmer_id'];
            $data_history['sale_id']=$sale_id;
            //$data_history['payment_id']=0
            $data_history['credit_limit_old']=$farmer_info['amount_credit_limit'];
            $data_history['credit_limit_new']=$farmer_info['amount_credit_limit'];
            $data_history['balance_old']=$farmer_info['amount_credit_balance'];
            $data_history['balance_new']=$farmer_info['amount_credit_balance']-$item_head['amount_payable_actual'];
            $data_history['amount_adjust']=$item_head['amount_payable_actual'];
            $data_history['remarks_reason']='New Sale';
            //$data_history['reference_no']
            //$data_history['remarks'];

            $data_credit=array();
            $data_credit['date_updated'] = $time;
            $data_credit['user_updated'] = $user->user_id;
            $data_credit['amount_credit_balance']=$data_history['balance_new'];
            Query_helper::update($this->config->item('table_pos_setup_farmer_farmer'),$data_credit, array('id='.$farmer_info['farmer_id']), false);
            Farmer_Credit_helper::add_credit_history($data_history);
        }
        Token_helper::update_token($system_user_token_info['id'], $system_user_token);
        $this->db->trans_complete();   //DB Transaction Handle END
        if ($this->db->trans_status() === TRUE)
        {
            $this->message=$this->lang->line("MSG_SAVED_SUCCESS");
            $result=Query_helper::get_info($this->config->item('table_login_setup_system_configures'),array('config_value'),array('purpose ="' .$this->config->item('system_purpose_status_sms_sales_invoice').'"','status ="'.$this->config->item('system_status_active').'"'),1);
            //if sms on and dealer
            if($result && ($result['config_value']==1) && ($farmer_info['farmer_type_id']>1))
            {
                $this->load->helper('mobile_sms');
                $this->lang->load('mobile_sms');
                $mobile_no=$farmer_info['mobile_no'];
                $invoice=Barcode_helper::get_barcode_sales($sale_id);
                $amount=System_helper::get_string_amount($item_head['amount_payable_actual']);
                if($item_head['sales_payment_method']=='Credit')
                {
                    Mobile_sms_helper::send_sms(Mobile_sms_helper::$API_SENDER_ID_BEEZTOLA,$mobile_no,sprintf($this->lang->line('SMS_SALES_INVOICE_CREDIT'),$amount,$invoice));
                }
                else
                {
                    Mobile_sms_helper::send_sms(Mobile_sms_helper::$API_SENDER_ID_BEEZTOLA,$mobile_no,sprintf($this->lang->line('SMS_SALES_INVOICE_CASH'),$amount,$invoice));
                }

            }
            $this->system_details($sale_id);
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
            if($data['item']['user_return_approved']>0)
            {
                $user_ids[$data['item']['user_return_approved']]=$data['item']['user_return_approved'];
            }

            $data['users']=System_helper::get_users_info($user_ids);

            $data['variety_pricing']=array();
            $results=Query_helper::get_info($this->config->item('table_login_setup_classification_variety_price'),'*',array());
            foreach($results as $result)
            {
                    $data['variety_pricing'][$result['variety_id']][$result['pack_size_id']]=$result;
            }

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

    public function get_dropdown_farmers_by_outlet_farmer_type_id()
    {
        $html_container_id='#farmer_id';
        if($this->input->post('html_container_id'))
        {
            $html_container_id=$this->input->post('html_container_id');
        }

        $farmer_type_id = $this->input->post('farmer_type_id');
        $outlet_id = $this->input->post('outlet_id');

        $this->db->from($this->config->item('table_pos_setup_farmer_outlet').' farmer_outlet');
        $this->db->where('farmer_outlet.outlet_id',$outlet_id);

        $this->db->join($this->config->item('table_pos_setup_farmer_farmer').' farmer','farmer.id = farmer_outlet.farmer_id','INNER');

        $this->db->select('farmer.mobile_no value,farmer.name text');
        $this->db->select('CONCAT_WS(" - ",farmer.mobile_no, farmer.name) text');

        $this->db->where('farmer.farmer_type_id',$farmer_type_id);
        $this->db->where('farmer.status',$this->config->item('system_status_active'));
        $this->db->where('farmer_outlet.revision',1);
        //$this->db->group_by('farmer.id');
        $this->db->order_by('farmer.ordering DESC');
        $this->db->order_by('farmer.id DESC');
        $data['items']=$this->db->get()->result_array();
        $ajax['status']=true;
        $ajax['system_content'][]=array("id"=>$html_container_id,"html"=>$this->load->view("dropdown_with_select",$data,true));
        $this->json_return($ajax);
    }

}
