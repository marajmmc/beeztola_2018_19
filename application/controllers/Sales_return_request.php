<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Sales_return_request extends Root_Controller
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
        $this->config->load('sales_return');
        $this->language_labels();
    }
    private function language_labels()
    {
        $this->lang->language['LABEL_DATE_SALE']='Return date';

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

        elseif($action=="save")
        {
            $this->system_save();
        }
        /*elseif($action=="details")
        {
            $this->system_details($id);
        }*/

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
            $data['title']="List of Sales Returns";
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

        $this->db->from($this->config->item('table_pos_sale_return').' sale_return');
        $this->db->select('sale_return.*');
        $this->db->select('cus.name outlet_name');
        $this->db->select('f.name customer_name');
        $this->db->join($this->config->item('table_login_csetup_cus_info').' cus','cus.customer_id =sale_return.outlet_id AND cus.revision=1','INNER');
        $this->db->join($this->config->item('table_pos_setup_farmer_farmer').' f','f.id = sale_return.farmer_id','INNER');
        $this->db->where_in('sale_return.outlet_id',$this->user_outlet_ids);
        $this->db->order_by('sale_return.id DESC');
        $this->db->limit($pagesize,$current_records);
        $items=$this->db->get()->result_array();
        foreach($items as &$item)
        {
            $item['date_sale']=$item['date_sale']>0?System_helper::display_date_time($item['date_sale']):'N/A';
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
            $data['title']="New Return Request";
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
                $ajax['system_message']='This Customer is Inactive.<br>Please Contact with admin';
                $this->json_return($ajax);
            }
            else
            {
                $result=Query_helper::get_info($this->config->item('table_pos_setup_farmer_outlet'),'*',array('farmer_id ='.$farmer_id,'revision =1','outlet_id ='.$outlet_id),1);
                if(!$result)
                {
                    $ajax['status']=false;
                    $ajax['system_message']='This Customer Cannot Retrun Product from this outlet.<br>Please Contact with admin';
                    $this->json_return($ajax);
                }
                $this->system_load_return_from($farmer_id,$outlet_id);

            }
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']='Customer '.$this->lang->line("MSG_NOT_FOUND");
            $this->json_return($ajax);
        }
    }

    private function system_load_return_from($farmer_id,$outlet_id)
    {
        $data=array();
        $data['title']="New Return Request";
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
        $data['item']['amount_credit_limit']=$result['amount_credit_limit'];
        $data['item']['amount_credit_balance']=$result['amount_credit_balance'];

        $result=Query_helper::get_info($this->config->item('table_pos_setup_farmer_type_outlet_discount'),'*',array('farmer_type_id ='.$data['item']['farmer_type_id'],'expire_time >'.time(),'outlet_id ='.$outlet_id),1);
        if($result)
        {
            $data['item']['discount_self_percentage']=$result['discount_percentage'];
            $data['item']['discount_message']='Outlet Special Discount';
        }

        //calculate it from config
        $result=Query_helper::get_info($this->config->item('table_login_setup_system_configures'),array('config_value'),array('purpose ="' .$this->config->item('system_purpose_pos_max_product_return_days').'"','status ="'.$this->config->item('system_status_active').'"'),1);

        $date_start=System_helper::get_time(System_helper::display_date(time()))-3600*24*$result['config_value'];
        $data['item']['date_start']=$date_start;
        //$date_start=System_helper::get_time('01-Aug-2019');

        //getting current stock,price,discount of outlet
        //current stocks--purchase quantity
        $this->db->from($this->config->item('table_pos_sale_details').' details');
        $this->db->select('details.variety_id,details.pack_size_id,details.pack_size');
        $this->db->select('SUM(details.quantity) current_stock',false);


        $this->db->join($this->config->item('table_login_setup_classification_varieties').' v','v.id = details.variety_id','INNER');
        $this->db->select('v.name variety_name');
        $this->db->join($this->config->item('table_login_setup_classification_crop_types').' type','type.id = v.crop_type_id','INNER');
        $this->db->select('type.name crop_type_name,type.id crop_type_id');
        $this->db->join($this->config->item('table_login_setup_classification_crops').' crop','crop.id = type.crop_id','INNER');
        $this->db->select('crop.name crop_name,crop.id crop_id');

        $this->db->join($this->config->item('table_pos_sale').' sale','sale.id = details.sale_id','INNER');
        $this->db->where('sale.outlet_id',$outlet_id);
        $this->db->where('sale.farmer_id',$farmer_id);
        $this->db->where('sale.status',$this->config->item('system_status_active'));
        $this->db->where('sale.date_sale >=',$date_start);
        $this->db->group_by('details.variety_id');
        $this->db->group_by('details.pack_size_id');
        $results=$this->db->get()->result_array();
        $varieties=array();
        foreach($results as $result)
        {
            $result['price_unit_pack']=0;
            $result['discount_percentage_variety']=0;
            $varieties[$result['variety_id']][$result['pack_size_id']]=$result;
        }
        //price
        $results=Query_helper::get_info($this->config->item('table_login_setup_classification_variety_price'),'*',array());
        foreach($results as $result)
        {
            if(isset($varieties[$result['variety_id']][$result['pack_size_id']]))
            {
                $varieties[$result['variety_id']][$result['pack_size_id']]['price_unit_pack']=$result['price'];
            }
        }
        //discount
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
            System_helper::invalid_try(__FUNCTION__, 0,'outlet id '.$item['outlet_id'].' not assigned');
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
            die();
        }
        if(sizeof($items)==0)
        {
            $ajax['status']=false;
            $ajax['system_message']="No Return Item Added";
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
            $ajax['system_message']='Customer not found';
            $this->json_return($ajax);
            die();
        }
        $result=Query_helper::get_info($this->config->item('table_pos_setup_farmer_outlet'),'*',array('farmer_id ='.$item['farmer_id'],'revision =1','outlet_id ='.$item['outlet_id']),1);
        if(!$result)
        {
            $ajax['status']=false;
            $ajax['system_message']='This Customer Cannot Retrun Product from this outlet.<br>Please Contact with admin';
            $this->json_return($ajax);
        }
        //calculate it from config
        $result=Query_helper::get_info($this->config->item('table_login_setup_system_configures'),array('config_value'),array('purpose ="' .$this->config->item('system_purpose_pos_max_product_return_days').'"','status ="'.$this->config->item('system_status_active').'"'),1);
        $date_start=System_helper::get_time(System_helper::display_date(time()))-3600*24*$result['config_value'];
        $data['item']['date_start']=$date_start;

        //current stocks--purchase quantity
        $this->db->from($this->config->item('table_pos_sale_details').' details');
        $this->db->select('details.variety_id,details.pack_size_id,details.pack_size');
        $this->db->select('SUM(details.quantity) current_stock',false);


        $this->db->join($this->config->item('table_pos_sale').' sale','sale.id = details.sale_id','INNER');
        $this->db->where('sale.outlet_id',$item['outlet_id']);
        $this->db->where('sale.farmer_id',$item['farmer_id']);
        $this->db->where('sale.status',$this->config->item('system_status_active'));
        $this->db->where('sale.date_sale >=',$date_start);
        $this->db->group_by('details.variety_id');
        $this->db->group_by('details.pack_size_id');
        $results=$this->db->get()->result_array();
        $stocks_purchase=array();//customer stocks
        foreach($results as $result)
        {

            $stocks_purchase[$result['variety_id']][$result['pack_size_id']]=$result;
        }


        //$stocks=Stock_helper::get_variety_stock($item['outlet_id']);

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
                if($pack['quantity']>$stocks_purchase[$variety_id][$pack_size_id]['current_stock'])
                {
                    $ajax['status']=false;
                    $message='Not Enough Purchase('.$variety_id.'-'.$pack_size_id.')';
                    $message.='<br>Current Purchase('.$stocks_purchase[$variety_id][$pack_size_id]['current_stock'].')';
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

        $item_head['date_sale']=0;
        $item_head['status_approve']=$this->config->item('system_status_pending');
        $item_head['date_return_requested']=$time;
        $item_head['user_return_requested']=$user->user_id;
        $item_head['remarks_return_requested']=$item['remarks_return_requested'];
        //getting current stock,price,discount of outlet finished
        $this->db->trans_start();  //DB Transaction Handle START
        $return_id=Query_helper::add($this->config->item('table_pos_sale_return'),$item_head);
        if(!($return_id>0))
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("MSG_SAVED_FAIL");
            $this->json_return($ajax);
        }
        foreach($item_head_details as $data_details)
        {
            $data_details['return_id']=$return_id;
            Query_helper::add($this->config->item('table_pos_sale_return_details'),$data_details);
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
        $this->form_validation->set_rules('item[remarks_return_requested]','Return Reason','required');
        if($this->form_validation->run() == FALSE)
        {
            $this->message=validation_errors();
            return false;
        }
        return true;
    }
    private function system_details($id)
    {
        if(isset($this->permissions['action0'])&&($this->permissions['action0']==1))
        {
            if($id>0)
            {
                $manual_sale_id=$id;
            }
            else
            {
                $manual_sale_id=$this->input->post('id');
            }

            $this->db->from($this->config->item('table_pos_sale_manual').' sale_manual');
            $this->db->select('sale_manual.*');
            $this->db->join($this->config->item('table_login_csetup_cus_info').' cus','cus.customer_id =sale_manual.outlet_id AND cus.revision=1','INNER');
            $this->db->select('cus.name outlet_name');
            $this->db->join($this->config->item('table_pos_setup_farmer_farmer').' f','f.id = sale_manual.farmer_id','INNER');
            $this->db->select('f.name farmer_name,f.mobile_no');
            $this->db->where('sale_manual.id',$manual_sale_id);
            $data['item']=$this->db->get()->row_array();
            if(!$data['item'])
            {
                System_helper::invalid_try('edit',$manual_sale_id,'Trying to access Invalid Manual Sale id');
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
                $this->json_return($ajax);
                die();
            }
            if(!in_array($data['item']['outlet_id'],$this->user_outlet_ids))
            {
                System_helper::invalid_try('edit',$manual_sale_id,'Trying to access other Outlets data');
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
                $this->json_return($ajax);
                die();
            }
            $this->db->from($this->config->item('table_pos_sale_manual_details').' sd');
            $this->db->select('sd.*');

            $this->db->join($this->config->item('table_login_setup_classification_varieties').' v','v.id = sd.variety_id','INNER');
            $this->db->select('v.name variety_name');
            $this->db->join($this->config->item('table_login_setup_classification_crop_types').' type','type.id = v.crop_type_id','INNER');
            $this->db->select('type.name crop_type_name,type.id crop_type_id');
            $this->db->join($this->config->item('table_login_setup_classification_crops').' crop','crop.id = type.crop_id','INNER');
            $this->db->select('crop.name crop_name,crop.id crop_id');
            $this->db->where('sd.manual_sale_id',$manual_sale_id);
            $data['items']=$this->db->get()->result_array();

            $user_ids=array();

            $user_ids[$data['item']['user_manual_requested']]=$data['item']['user_manual_requested'];
            if($data['item']['user_manual_approved']>0)
            {
                $user_ids[$data['item']['user_manual_approved']]=$data['item']['user_manual_approved'];
            }
            $data['users']=System_helper::get_users_info($user_ids);
            if($data['item']['outlet_id_commission']>0)
            {
                $result=Query_helper::get_info($this->config->item('table_login_csetup_cus_info'),array('name outlet_name'),array('customer_id ='.$data['item']['outlet_id_commission']),1);
                $data['item']['outlet_name_commission']=$result['outlet_name'];
            }
            $data['title']='Details of Request Id('.$manual_sale_id.')';
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/details",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/details/'.$manual_sale_id);
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
