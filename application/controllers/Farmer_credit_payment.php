<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Farmer_credit_payment extends Root_Controller
{
    public $message;
    public $permissions;
    public $controller_url;
    //public $common_view_location;
    public $user_outlets;
    public $user_outlet_ids;

    public function __construct()
    {
        parent::__construct();
        $this->message="";
        $this->permissions=User_helper::get_permission(get_class());
        $this->controller_url=strtolower(get_class());
        //$this->common_view_location='credit_farmer_payment_deposit';
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
        //$this->load->helper('credit');
        $this->language_labels();
    }
    private function language_labels()
    {
        $this->lang->language['LABEL_REVISION_COUNT']='Revision Count (Edit)';
        //$this->lang->language['LABEL_DELETE']='Delete';
        //$this->lang->language['LABEL_REASON_DELETE']='Delete Reason';
    }
    public function index($action="list",$id=0,$id1=0)
    {
        if($action=="list")
        {
            $this->system_list();
        }
        elseif($action=="get_items")
        {
            $this->system_get_items();
        }
        elseif($action=="list_payment")
        {
            $this->system_list_payment($id);
        }
        elseif($action=="get_items_payment")
        {
            $this->system_get_items_payment($id);
        }
        elseif($action=="add")
        {
            $this->system_add($id);
        }
        elseif($action=="edit")
        {
            $this->system_edit($id,$id1);
        }
        elseif($action=="save")
        {
            $this->system_save();
        }
        elseif ($action == "delete")
        {
            $this->system_delete($id,$id1);
        }
        elseif ($action == "save_delete")
        {
            $this->system_save_delete();
        }
        elseif($action=="details")
        {
            $this->system_details($id,$id1);
        }
        elseif($action=="set_preference")
        {
            $this->system_set_preference('list');
        }
        elseif($action=="set_preference_list_payment")
        {
            $this->system_set_preference('list_payment');
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
        $data=array();
        if($method=='list')
        {
            $data['id']= 1;
            $data['barcode']= 1;
            $data['name']= 1;
            $data['amount_credit_limit']= 1;
            $data['amount_credit_balance']= 1;
            $data['date_created_time']= 1;
            $data['farmer_type_name']= 1;
            //$data['status_card_require']= 1;
            $data['mobile_no']= 1;
            $data['nid']= 1;
            $data['address']= 1;
            $data['division_name']= 1;
            $data['zone_name']= 1;
            $data['territory_name']= 1;
            $data['district_name']= 1;
            $data['upazilla_name']= 1;
            $data['union_name']= 1;
            $data['status']= 1;
        }
        else if($method=='list_payment')
        {
            $data['id']= 1;
            $data['date_payment']= 1;
            $data['amount']= 1;
            $data['reference_no']= 1;
            $data['remarks']= 1;
            $data['revision_count']= 1;
        }
        else
        {

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
        if(isset($this->permissions['action0'])&&($this->permissions['action0']==1))
        {
            $data['system_preference_items']= System_helper::get_preference($user->user_id, $this->controller_url, $method, $this->get_preference_headers($method));
            $data['title']="Dealer List(Those who can buy on credit)";
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
        //$user=User_helper::get_user();
        $this->db->from($this->config->item('table_pos_setup_farmer_farmer').' f');
        $this->db->select('f.*');
        $this->db->join($this->config->item('table_pos_setup_farmer_type').' ft','ft.id = f.farmer_type_id','INNER');
        $this->db->select('ft.name farmer_type_name');

        $this->db->join($this->config->item('table_login_setup_location_unions').' union','union.id = f.union_id','LEFT');
        $this->db->select('union.name union_name');
        $this->db->join($this->config->item('table_login_setup_location_upazillas').' u','u.id = union.upazilla_id','LEFT');
        $this->db->select('u.name upazilla_name');
        $this->db->join($this->config->item('table_login_setup_location_districts').' d','d.id = u.district_id','LEFT');
        $this->db->select('d.name district_name');
        $this->db->join($this->config->item('table_login_setup_location_territories').' t','t.id = d.territory_id','LEFT');
        $this->db->select('t.name territory_name');
        $this->db->join($this->config->item('table_login_setup_location_zones').' zone','zone.id = t.zone_id','LEFT');
        $this->db->select('zone.name zone_name');
        $this->db->join($this->config->item('table_login_setup_location_divisions').' division','division.id = zone.division_id','LEFT');
        $this->db->select('division.name division_name');
        $this->db->join($this->config->item('table_pos_setup_farmer_outlet').' farmer_outlet','farmer_outlet.farmer_id = f.id and farmer_outlet.revision =1','INNER');
        $this->db->where_in('farmer_outlet.outlet_id',$this->user_outlet_ids);
        $this->db->where('f.amount_credit_limit > ',0);
        $this->db->order_by('f.id DESC');
        $this->db->group_by('f.id');
        $items=$this->db->get()->result_array();
        //echo $this->db->last_query();
        $time=time();
        foreach($items as &$item)
        {
            $item['barcode']=Barcode_helper::get_barcode_farmer($item['id']);
            $item['date_created_time']=System_helper::display_date_time($item['date_created']);
            /*if($item['time_card_off_end']>$time)
            {
                $item['status_card_require']=$this->config->item('system_status_no');
            }*/
        }
        $this->json_return($items);
    }
    private function system_list_payment($id)
    {
        if($id>0)
        {
            $data['item_id']=$id;
        }
        else
        {
            $data['item_id']=$this->input->post('id');
        }
        //for fixing back button of preference
        if(!($data['item_id']>0))
        {
            $this->system_list();
        }
        $user = User_helper::get_user();

        if((isset($this->permissions['action0']) && ($this->permissions['action0']==1)) ||(isset($this->permissions['action1']) && ($this->permissions['action1']==1)) || (isset($this->permissions['action2']) && ($this->permissions['action2']==1)))
        {
            $this->check_validation_farmer($data['item_id'],__FUNCTION__);
            $farmer_info=Query_helper::get_info($this->config->item('table_pos_setup_farmer_farmer'),array('*'),array('id ='.$data['item_id'],'status!="'.$this->config->item('system_status_delete').'"'),1);
            $data['amount_credit_limit']=$farmer_info['amount_credit_limit'];
            $data['amount_credit_balance']=$farmer_info['amount_credit_balance'];


            $result=Query_helper::get_info($this->config->item('table_pos_farmer_credit_payment'),array('SUM(amount) amount_total'),array('farmer_id ='.$data['item_id'],'status!="'.$this->config->item('system_status_delete').'"'),1);
            $data['amount_total']=$result['amount_total']?$result['amount_total']:0;

            $data['info_basic']=$this->get_farmer_info($data['item_id']);
            $method='list_payment';
            $data['system_preference_items']= System_helper::get_preference($user->user_id, $this->controller_url, $method, $this->get_preference_headers($method));
            $data['title']='Payment List ::'.$farmer_info['name'].'-'.$farmer_info['mobile_no'].' ('.Barcode_helper::get_barcode_farmer($farmer_info['id']).')';
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/list_payment",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/list_payment/'.$data['item_id']);
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function system_get_items_payment($item_id)
    {
        $this->db->from($this->config->item('table_pos_farmer_credit_payment').' payment');
        $this->db->where('payment.farmer_id',$item_id);
        $this->db->where('payment.status !=',$this->config->item('system_status_delete'));
        $this->db->order_by('payment.id','DESC');
        $items=$this->db->get()->result_array();
        foreach($items as &$item)
        {
            $item['date_payment']=System_helper::display_date($item['date_payment']);
        }
        $this->json_return($items);
    }
    private function system_add($farmer_id)
    {
        if(isset($this->permissions['action1'])&&($this->permissions['action1']==1))
        {
            $this->check_validation_farmer($farmer_id,__FUNCTION__);
            $data['payment_way']=Query_helper::get_info($this->config->item('table_login_setup_payment_way'),array('id value, name text'),array('status ="'.$this->config->item('system_status_active').'"'));

            $farmer_info=Query_helper::get_info($this->config->item('table_pos_setup_farmer_farmer'),array('*'),array('id ='.$farmer_id,'status!="'.$this->config->item('system_status_delete').'"'),1);
            $data['amount_credit_limit']=$farmer_info['amount_credit_limit'];
            $data['amount_credit_balance']=$farmer_info['amount_credit_balance'];


            $result=Query_helper::get_info($this->config->item('table_pos_farmer_credit_payment'),array('SUM(amount) amount_total'),array('farmer_id ='.$farmer_id,'status!="'.$this->config->item('system_status_delete').'"'),1);
            $data['amount_total']=$result['amount_total']?$result['amount_total']:0;

            $data['info_basic']=$this->get_farmer_info($farmer_id);

            $data['title']='New Payment ::'.$farmer_info['name'].'-'.$farmer_info['mobile_no'].' ('.Barcode_helper::get_barcode_farmer($farmer_info['id']).')';
            $data['item']['id']=0;
            $data['item']['farmer_id']=$farmer_id;
            $data['item']['date_payment']=time();
            $data['item']['payment_way_id']=1;
            $data['item']['amount']='';
            $data['item']['reference_no']='';
            $data['item']['remarks']='';



            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/add_edit",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/add/'.$farmer_id);
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function system_edit($farmer_id, $id1)
    {
        if(isset($this->permissions['action2'])&&($this->permissions['action2']==1))
        {
            if($id1>0)
            {
                $item_id=$id1;
            }
            else
            {
                $item_id=$this->input->post('id');
            }
            $this->check_validation_farmer($farmer_id,__FUNCTION__);

            $data['item']=Query_helper::get_info($this->config->item('table_pos_farmer_credit_payment'),array('*'),array('id ='.$item_id,'farmer_id ='.$farmer_id),1);
            if(!$data['item'])
            {
                System_helper::invalid_try(__FUNCTION__,$data['item']['id'],'Non Exists');
                $ajax['status']=false;
                $ajax['system_message']='Invalid Try.';
                $this->json_return($ajax);
            }
            if ($data['item']['status'] == $this->config->item('system_status_delete'))
            {
                $ajax['status'] = false;
                $ajax['system_message'] = 'Payment already deleted.';
                $this->json_return($ajax);
            }
            $data['item']['farmer_id']=$farmer_id;
            if($data['item']['revision_count']>1)
            {
                $data['item']['remarks']=$data['item']['remarks_update'];
            }
            $data['payment_way']=Query_helper::get_info($this->config->item('table_login_setup_payment_way'),array('id value, name text'),array('status ="'.$this->config->item('system_status_active').'"'));

            $farmer_info=Query_helper::get_info($this->config->item('table_pos_setup_farmer_farmer'),array('*'),array('id ='.$farmer_id,'status!="'.$this->config->item('system_status_delete').'"'),1);
            $data['amount_credit_limit']=$farmer_info['amount_credit_limit'];
            $data['amount_credit_balance']=$farmer_info['amount_credit_balance'];
            $result=Query_helper::get_info($this->config->item('table_pos_farmer_credit_payment'),array('SUM(amount) amount_total'),array('farmer_id ='.$farmer_id,'status!="'.$this->config->item('system_status_delete').'"'),1);
            $data['amount_total']=$result['amount_total']?$result['amount_total']:0;

            $data['info_basic']=$this->get_farmer_info($farmer_id);
            $data['title']='Edit Payment('.$item_id.') ::'.$farmer_info['name'].'-'.$farmer_info['mobile_no'].' ('.Barcode_helper::get_barcode_farmer($farmer_info['id']).')';

            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/add_edit",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/edit/'.$farmer_id.'/'.$item_id);
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
        $farmer_id = $this->input->post("farmer_id");
        $user = User_helper::get_user();
        $time=time();
        $item=$this->input->post('item');
        $this->check_validation_farmer($farmer_id,__FUNCTION__);
        $this->load->helper('farmer_credit');

        $amount_old=0;
        if($id>0)
        {
            if(!(isset($this->permissions['action2']) && ($this->permissions['action2']==1)))
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
                $this->json_return($ajax);
            }
            $result=Query_helper::get_info($this->config->item('table_pos_farmer_credit_payment'),'*',array('id ='.$id,'farmer_id ='.$farmer_id),1);
            if(!$result)
            {
                System_helper::invalid_try(__FUNCTION__,$id,'Update Non Exists');
                $ajax['status']=false;
                $ajax['system_message']='Invalid Try.';
                $this->json_return($ajax);
            }
            if ($result['status'] == $this->config->item('system_status_delete'))
            {
                $ajax['status'] = false;
                $ajax['system_message'] = 'Payment already deleted.';
                $this->json_return($ajax);
            }
            $amount_old=isset($result['amount'])?$result['amount']:0;
        }
        else
        {
            if(!(isset($this->permissions['action1']) && ($this->permissions['action1']==1)))
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
                $this->json_return($ajax);
            }
        }
        if(!$this->check_validation_payment())
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->message;
            $this->json_return($ajax);
        }
        $farmer_info=Query_helper::get_info($this->config->item('table_pos_setup_farmer_farmer'),array('*'),array('id ='.$farmer_id,'status!="'.$this->config->item('system_status_delete').'"'),1);

        $data_history=array();
        $data_history['farmer_id']=$farmer_id;
        //$data_history['sale_id']=0;
        //$data_history['payment_id'] will be set bellow

        $data_history['credit_limit_old']=$farmer_info['amount_credit_limit'];
        $data_history['credit_limit_new']=$farmer_info['amount_credit_limit'];

        $data_history['balance_old']=$farmer_info['amount_credit_balance'];
        $data_history['balance_new']=$farmer_info['amount_credit_balance']+($item['amount']-$amount_old);
        $data_history['amount_adjust']=$item['amount'];
        //$data_history['remarks_reason'] set bellow add or edit

        $data_history['reference_no']=$item['reference_no'];
        $data_history['remarks']=$item['remarks'];
        $this->db->trans_start();  //DB Transaction Handle START

        if($id>0)
        {
            $data=array();
            $data['date_payment']= System_helper::get_time($item['date_payment']);
            $data['payment_way_id']= $item['payment_way_id'];
            $data['amount']= $item['amount'];
            $this->db->set('revision_count', 'revision_count+1', FALSE);
            $data['reference_no']= $item['reference_no'];
            $data['remarks_update']= $item['remarks'];
            $data['date_updated'] = $time;
            $data['user_updated'] = $user->user_id;
            Query_helper::update($this->config->item('table_pos_farmer_credit_payment'),$data, array('id='.$id), false);
            $data_history['payment_id']=$id;
            $data_history['remarks_reason']='Edit Payment. Amount Old: '.$amount_old.' Amount New: '.$data['amount'];
        }
        else
        {
            $data=array();
            $data['farmer_id'] = $farmer_id;
            if((isset($this->permissions['action2']) && ($this->permissions['action2']==1)))
            {
                $data['date_payment']= System_helper::get_time($item['date_payment']);
            }
            else
            {
                $data['date_payment']=$time;
            }
            $data['payment_way_id']= $item['payment_way_id'];
            $data['amount']= $item['amount'];
            $item['revision_count']=1;
            $data['reference_no']= $item['reference_no'];
            $data['remarks']= $item['remarks'];
            $data['date_created'] = $time;
            $data['user_created'] = $user->user_id;
            $payment_id=Query_helper::add($this->config->item('table_pos_farmer_credit_payment'),$data, false);
            $data_history['payment_id']=$payment_id;
            $data_history['remarks_reason']='Add Payment';
        }
        $data_credit=array();
        $data_credit['date_updated'] = $time;
        $data_credit['user_updated'] = $user->user_id;
        $data_credit['amount_credit_balance']=$data_history['balance_new'];
        Query_helper::update($this->config->item('table_pos_setup_farmer_farmer'),$data_credit, array('id='.$farmer_id), false);
        Farmer_Credit_helper::add_credit_history($data_history);

        $this->db->trans_complete();   //DB Transaction Handle END
        if ($this->db->trans_status() === TRUE)
        {
            $this->message=$this->lang->line("MSG_SAVED_SUCCESS");
            $this->system_list_payment($farmer_id);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("MSG_SAVED_FAIL");
            $this->json_return($ajax);
        }
    }
    private function system_delete($id, $id1)
    {
        if (isset($this->permissions['action3']) && ($this->permissions['action3'] == 1))
        {
            if ($id1 > 0)
            {
                $item_id = $id1;
            }
            else
            {
                $item_id = $this->input->post('id');
            }
            $farmer_id=$id;
            $get_farmer=$this->get_farmer($farmer_id);
            $farmer_info=$this->farmer_info($get_farmer);
            $data = $basic_info = array();

            $this->db->from($this->config->item('table_pos_credit_payment') . ' credit_payment');
            $this->db->select('credit_payment.*, credit_payment.amount amount_payment');

            $this->db->join($this->config->item('table_login_setup_payment_way') . ' payment_way', 'payment_way.id = credit_payment.payment_way_id', 'INNER');
            $this->db->select('payment_way.name payment_way');

            $this->db->where('credit_payment.id', $item_id);
            $this->db->where('credit_payment.status', $this->config->item('system_status_active'));
            $result = $this->db->get()->row_array();
            if (!$result)
            {
                System_helper::invalid_try(__FUNCTION__, $item_id, 'Payment Not Exists');
                $ajax['status'] = false;
                $ajax['system_message'] = 'Invalid Try.';
                $this->json_return($ajax);
            }
            if ($result['status'] == $this->config->item('system_status_delete'))
            {
                $ajax['status'] = false;
                $ajax['system_message'] = 'Payment already deleted.';
                $this->json_return($ajax);
            }

            $basic_info['accordion']['header'] = 'Payment Information';
            $basic_info['accordion']['div_id'] = 'payment_info';
            $basic_info['accordion']['collapse'] = 'in';
            $basic_info['info_basic'][] = array(
                'label_1' => $this->lang->line('LABEL_DATE_PAYMENT'),
                'value_1' => System_helper::display_date_time($result['date_payment']),
                'label_2' => $this->lang->line('LABEL_AMOUNT_PAYMENT'),
                'value_2' => System_helper::get_string_amount($result['amount'])
            );
            $basic_info['info_basic'][] = array(
                'label_1' => $this->lang->line('LABEL_PAYMENT_WAY'),
                'value_1' => $result['payment_way'],
                'label_2' => $this->lang->line('LABEL_REFERENCE_NO'),
                'value_2' => $result['reference_no']
            );
            $basic_info['info_basic'][] = array(
                'label_1' => 'Credit Limit',
                'value_1' => System_helper::get_string_amount($get_farmer['amount_credit_limit']),
                'label_2' => 'Old Balance',
                'value_2' => System_helper::get_string_amount($get_farmer['amount_credit_balance'])
            );
            $basic_info['info_basic'][] = array(
                'label_1' => 'Deleted Amount',
                'value_1' => System_helper::get_string_amount($result['amount']),
                'label_2' => 'New Balance',
                'value_2' => (System_helper::get_string_amount($get_farmer['amount_credit_balance']-$result['amount']))
            );
            $basic_info['info_basic'][] = array(
                'label_1' => $this->lang->line('LABEL_REMARKS'),
                'value_1' => nl2br($result['remarks'])
            );
            $data['details'][] = $this->load->view("info_basic", $farmer_info, true);
            $data['details'][] = $this->load->view("info_basic", $basic_info, true);

            $data['item'] = $result;
            $data['title'] = "Payment Delete (Payment ID:" . $result['id'] . " )";
            $ajax['status'] = true;
            $ajax['system_content'][] = array("id" => "#system_content", "html" => $this->load->view($this->controller_url . "/delete", $data, true));
            if ($this->message)
            {
                $ajax['system_message'] = $this->message;
            }
            $ajax['system_page_url'] = site_url($this->controller_url . '/index/delete/'.$farmer_id.'/'. $item_id);
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status'] = false;
            $ajax['system_message'] = $this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function system_save_delete()
    {
        $item_id = $this->input->post('id');
        $farmer_id = $this->input->post('farmer_id');
        $item = $this->input->post('item');
        $user = User_helper::get_user();
        $time = time();
        //Permission Checking
        if (!(isset($this->permissions['action3']) && ($this->permissions['action3'] == 1)))
        {
            $ajax['status'] = false;
            $ajax['system_message'] = $this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
        //validation
        if($item['status']!=$this->config->item('system_status_delete'))
        {
            $ajax['status'] = false;
            $ajax['system_message'] = ($this->lang->line('LABEL_DELETE')) . ' field is required.';
            $this->json_return($ajax);
        }
        if(!($item['remarks_delete']))
        {
            $ajax['status'] = false;
            $ajax['system_message'] = ($this->lang->line('LABEL_REASON_DELETE')) . ' field is required.';
            $this->json_return($ajax);
        }
        $result = Query_helper::get_info($this->config->item('table_pos_credit_payment'), array('*'), array('id =' . $item_id), 1);
        if (!$result)
        {
            System_helper::invalid_try(__FUNCTION__, $item_id, 'Payment Delete Not Exists');
            $ajax['status'] = false;
            $ajax['system_message'] = 'Invalid Try.';
            $this->json_return($ajax);
        }
        if ($result['status'] == $this->config->item('system_status_delete'))
        {
            $ajax['status'] = false;
            $ajax['system_message'] = 'Payment already deleted.';
            $this->json_return($ajax);
        }
        $result_amount=$result['amount'];
        $reference_no=$result['reference_no'];

        $result=Query_helper::get_info($this->config->item('table_pos_setup_farmer_farmer'),array('*'),array('id ='.$farmer_id,'status!="'.$this->config->item('system_status_delete').'"'),1,0,array('id ASC'));
        if(!$result)
        {
            System_helper::invalid_try(__FUNCTION__,$farmer_id,'Payment Delete Non Exists');
            $ajax['status']=false;
            $ajax['system_message']='Invalid Try.';
            $this->json_return($ajax);
        }
        $credit_limit_old=$result['amount_credit_limit'];
        $credit_limit_new=$result['amount_credit_limit'];
        $balance_old=$result['amount_credit_balance'];
        $balance_new=($result['amount_credit_balance']-$result_amount);
        $amount_adjust_old=$result_amount;
        $amount_adjust=0;
        if($balance_new<0)
        {
            $ajax['status'] = false;
            $ajax['system_message'] = 'Insufficient balance. Your new balance is: '.$balance_new;
            $this->json_return($ajax);
        }

        $this->db->trans_start(); //DB Transaction Handle START

        $item['date_deleted'] = $time;
        $item['user_deleted'] = $user->user_id;
        $item['amount'] = 0;
        Query_helper::update($this->config->item('table_pos_credit_payment'), $item, array("id = " . $item_id), FALSE);
        $payment_id=$item_id;
        $remarks_reason='Delete Payment. Adjust Amount Old: '.$amount_adjust_old.' Adjust Amount New: '.$amount_adjust;

        $data_credit['date_updated'] = $time;
        $data_credit['user_updated'] = $user->user_id;
        $data_credit['amount_credit_balance']=$balance_new;
        Query_helper::update($this->config->item('table_pos_setup_farmer_farmer'),$data_credit, array('id='.$farmer_id), false);

        Credit_helper::add_credit_history($farmer_id,$amount_adjust,$credit_limit_old,$credit_limit_new, $balance_old,$balance_new,$remarks_reason,$reference_no,0,$payment_id,$item['remarks_delete']);

        $this->db->trans_complete(); //DB Transaction Handle END

        if ($this->db->trans_status() === TRUE)
        {
            $ajax['status'] = true;
            $this->message = $this->lang->line("MSG_SAVED_SUCCESS");
            $this->system_list_payment($farmer_id);
        }
        else
        {
            $ajax['status'] = false;
            $ajax['system_message'] = $this->lang->line("MSG_SAVED_FAIL");
            $this->json_return($ajax);
        }
    }
    private function check_validation_payment()
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('item[amount]',$this->lang->line('LABEL_AMOUNT'),'required');
        if($this->form_validation->run() == FALSE)
        {
            $this->message=validation_errors();
            return false;
        }
        return true;
    }
    private function system_details($farmer_id,$id1)
    {
        if(isset($this->permissions['action0'])&&($this->permissions['action0']==1))
        {
            if ($id1 > 0)
            {
                $item_id = $id1;
            }
            else
            {
                $item_id = $this->input->post('id');
            }
            $this->check_validation_farmer($farmer_id,__FUNCTION__);
            $data=array();
            $data['info_basic']=$this->get_farmer_info($farmer_id);
            $data['item']=Query_helper::get_info($this->config->item('table_pos_farmer_credit_payment'),array('*'),array('id ='.$item_id,'farmer_id ='.$farmer_id),1);
            if(!$data['item'])
            {
                $ajax['status']=false;
                $ajax['system_message']='Invalid Try.';
                $this->json_return($ajax);
            }
            if ($data['item']['status'] == $this->config->item('system_status_delete'))
            {
                $ajax['status'] = false;
                $ajax['system_message'] = 'Payment already deleted.';
                $this->json_return($ajax);
            }
            $data['info_payment']=$this->get_payment_info($item_id);
            $data['payment_histories']=array();
            if($data['item']['revision_count']>1)
            {
                $data['payment_histories']=Query_helper::get_info($this->config->item('table_pos_farmer_credit_balance_history'),array('*'),array('farmer_id ='.$farmer_id,'payment_id ='.$item_id),0,0,array('id DESC'));
            }

            /*$this->db->from($this->config->item('table_pos_credit_payment') . ' credit_payment');
            $this->db->select('credit_payment.*, credit_payment.amount amount_payment');

            $this->db->join($this->config->item('table_login_setup_payment_way') . ' payment_way', 'payment_way.id = credit_payment.payment_way_id', 'INNER');
            $this->db->select('payment_way.name payment_way');

            $this->db->where('credit_payment.id', $item_id);
            $this->db->where('credit_payment.status', $this->config->item('system_status_active'));
            $result = $this->db->get()->row_array();
            if (!$result)
            {
                System_helper::invalid_try(__FUNCTION__, $item_id, 'Payment Not Exists');
                $ajax['status'] = false;
                $ajax['system_message'] = 'Invalid Try.';
                $this->json_return($ajax);
            }
            if ($result['status'] == $this->config->item('system_status_delete'))
            {
                $ajax['status'] = false;
                $ajax['system_message'] = 'Payment already deleted.';
                $this->json_return($ajax);
            }
            $user_ids = array(
                $result['user_created'] => $result['user_created'],
                $result['user_updated'] => $result['user_updated'],
            );
            $user_info = System_helper::get_users_info($user_ids);

            $basic_info['accordion']['header'] = 'Payment Information';
            $basic_info['accordion']['div_id'] = 'payment_info';
            $basic_info['accordion']['collapse'] = 'in';
            $basic_info['info_basic'][] = array(
                'label_1' => $this->lang->line('LABEL_DATE_PAYMENT'),
                'value_1' => System_helper::display_date_time($result['date_payment']),
                'label_2' => $this->lang->line('LABEL_AMOUNT_PAYMENT'),
                'value_2' => System_helper::get_string_amount($result['amount'])
            );
            $basic_info['info_basic'][] = array(
                'label_1' => $this->lang->line('LABEL_PAYMENT_WAY'),
                'value_1' => $result['payment_way'],
                'label_2' => $this->lang->line('LABEL_REFERENCE_NO'),
                'value_2' => $result['reference_no']
            );
            $basic_info['info_basic'][] = array(
                'label_1' => 'Credit Limit',
                'value_1' => System_helper::get_string_amount($get_farmer['amount_credit_limit']),
                'label_2' => 'Balance',
                'value_2' => System_helper::get_string_amount($get_farmer['amount_credit_balance'])
            );
            $basic_info['info_basic'][] = array
            (
                'label_1' => $this->lang->line('LABEL_REMARKS'),
                'value_1' => nl2br($result['remarks']),
                'label_2' => $this->lang->line('LABEL_REVISION_COUNT'),
                'value_2' => $result['revision_count'],
            );
            $basic_info['info_basic'][] = array(
                'label_1' => $this->lang->line('LABEL_CREATED_BY'),
                'value_1' => $user_info[$result['user_created']]['name'],
                'label_2' => $this->lang->line('LABEL_DATE_CREATED_TIME'),
                'value_2' => System_helper::display_date_time($result['date_created'])
            );
            if ($result['user_updated'])
            {
                $basic_info['info_basic'][] = array(
                    'label_1' => $this->lang->line('LABEL_UPDATED_BY'),
                    'value_1' => $user_info[$result['user_updated']]['name'],
                    'label_2' => $this->lang->line('LABEL_DATE_UPDATED_TIME'),
                    'value_2' => System_helper::display_date_time($result['date_updated'])
                );
            }
            $data['details'][] = $this->load->view("info_basic", $farmer_info, true);
            $data['details'][] = $this->load->view("info_basic", $basic_info, true);



            $data['item'] = $result;*/

            $data['title'] = "Payment Details (Payment ID:" . $item_id . " )";
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/details",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/details/'.$farmer_id.'/'. $item_id);
            $this->json_return($ajax);


        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }

    }
    protected function get_farmer_info($farmer_id)
    {

        $this->db->from($this->config->item('table_pos_setup_farmer_farmer') . ' f');
        $this->db->select('f.*');
        $this->db->join($this->config->item('table_pos_setup_farmer_type') . ' ft', 'ft.id = f.farmer_type_id', 'INNER');
        $this->db->select('ft.name farmer_type_name, ft.discount_self_percentage');

        $this->db->join($this->config->item('table_login_setup_location_unions') . ' union', 'union.id = f.union_id', 'LEFT');
        $this->db->select('union.name union_name');

        $this->db->join($this->config->item('table_login_setup_location_upazillas') . ' u', 'u.id = union.upazilla_id', 'LEFT');
        $this->db->select('u.name upazilla_name');

        $this->db->join($this->config->item('table_login_setup_location_districts') . ' d', 'd.id = u.district_id', 'LEFT');
        $this->db->select('d.name district_name');

        $this->db->join($this->config->item('table_login_setup_location_territories') . ' t', 't.id = d.territory_id', 'LEFT');
        $this->db->select('t.name territory_name');

        $this->db->join($this->config->item('table_login_setup_location_zones') . ' z', 'z.id = t.zone_id', 'LEFT');
        $this->db->select('z.name zone_name');

        $this->db->join($this->config->item('table_login_setup_location_divisions') . ' division', 'division.id = z.division_id', 'LEFT');
        $this->db->select('division.name division_name');

        $this->db->join($this->config->item('table_pos_farmer_credit_payment') . ' cp', 'cp.farmer_id = f.id', 'LEFT');
        $this->db->select('cp.date_created deposit_date_created, cp.user_created deposit_user_created');
        $this->db->select('cp.date_updated deposit_date_updated, cp.user_created deposit_user_updated');
        $this->db->where('f.id', $farmer_id);

        $farmer_info = $this->db->get()->row_array();
        $data = array();
        $data['info_basic'][] = array(
            'label_1' => $this->lang->line('LABEL_NAME'),
            'value_1' => $farmer_info['name'] . ' ( ' .Barcode_helper::get_barcode_farmer($farmer_info['id']) . ' )',
            'label_2' => $this->lang->line('LABEL_MOBILE_NO'),
            'value_2' => $farmer_info['mobile_no']
        );
        $data['info_basic'][] = array(
            'label_1' => $this->lang->line('LABEL_FARMER_TYPE_NAME'),
            'value_1' => $farmer_info['farmer_type_name']
        );
        $data['info_basic'][] = array(
            'label_1' => $this->lang->line('LABEL_ADDRESS'),
            'value_1' => nl2br($farmer_info['address'])
        );
        // Location
        $data['info_basic'][] = array(
            'label_1' => $this->lang->line('LABEL_DIVISION_NAME'),
            'value_1' => $farmer_info['division_name'],
            'label_2' => $this->lang->line('LABEL_ZONE_NAME'),
            'value_2' => $farmer_info['zone_name']
        );
        $data['info_basic'][] = array(
            'label_1' => $this->lang->line('LABEL_TERRITORY_NAME'),
            'value_1' => $farmer_info['territory_name'],
            'label_2' => $this->lang->line('LABEL_DISTRICT_NAME'),
            'value_2' => $farmer_info['district_name']
        );
        $data['info_basic'][] = array(
            'label_1' => $this->lang->line('LABEL_UPAZILLA_NAME'),
            'value_1' => $farmer_info['upazilla_name'],
            'label_2' => $this->lang->line('LABEL_UNION_NAME'),
            'value_2' => $farmer_info['union_name']
        );
       return $data;


    }
    protected function check_validation_farmer($farmer_id,$function_name)
    {
        $farmer_info=Query_helper::get_info($this->config->item('table_pos_setup_farmer_farmer'),array('*'),array('id ='.$farmer_id,'status!="'.$this->config->item('system_status_delete').'"'),1);
        if(!$farmer_info)
        {
            $ajax['status']=false;
            $ajax['system_message']='Invalid Try.';
            $this->json_return($ajax);
        }
        if(!($farmer_info['amount_credit_limit']>0))
        {
            System_helper::invalid_try($function_name,$farmer_id,'Not Credit Customer');
            $ajax['status']=false;
            $ajax['system_message']='This customer is not allowed to buy on Credit';
            $this->json_return($ajax);
        }
        $this->db->from($this->config->item('table_pos_setup_farmer_outlet').' farmer_outlet');
        $this->db->select('farmer_outlet.outlet_id');
        $this->db->where('farmer_outlet.farmer_id',$farmer_id);
        $this->db->where('farmer_outlet.revision',1);
        $this->db->where_in('farmer_outlet.outlet_id',$this->user_outlet_ids);
        $results=$this->db->get()->result_array();
        if(!$results)
        {
            System_helper::invalid_try($function_name,$farmer_id,'Not my Outlet customer.');
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    protected function get_payment_info($payment_id)
    {
        $this->db->from($this->config->item('table_pos_farmer_credit_payment') . ' credit_payment');
        $this->db->select('credit_payment.*, credit_payment.amount amount_payment');

        $this->db->join($this->config->item('table_login_setup_payment_way') . ' payment_way', 'payment_way.id = credit_payment.payment_way_id', 'INNER');
        $this->db->select('payment_way.name payment_way');
        $this->db->where('credit_payment.id', $payment_id);
        $result = $this->db->get()->row_array();
        $user_ids = array(
            $result['user_created'] => $result['user_created'],
            $result['user_updated'] => $result['user_updated'],
        );
        $user_info = System_helper::get_users_info($user_ids);
        $data = array();
        $data['info_basic'][] = array(
            'label_1' => $this->lang->line('LABEL_DATE_PAYMENT'),
            'value_1' => System_helper::display_date($result['date_payment']),
            'label_2' => $this->lang->line('LABEL_AMOUNT_PAYMENT'),
            'value_2' => System_helper::get_string_amount($result['amount'])
        );
        $data['info_basic'][] = array(
            'label_1' => $this->lang->line('LABEL_PAYMENT_WAY'),
            'value_1' => $result['payment_way'],
            'label_2' => $this->lang->line('LABEL_REFERENCE_NO'),
            'value_2' => $result['reference_no']
        );
        $data['info_basic'][] = array
        (
            'label_1' => $this->lang->line('LABEL_REMARKS'),
            'value_1' => nl2br($result['remarks']),
            'label_2' => $this->lang->line('LABEL_REVISION_COUNT'),
            'value_2' => $result['revision_count'],
        );
        $data['info_basic'][] = array(
            'label_1' => $this->lang->line('LABEL_CREATED_BY'),
            'value_1' => $user_info[$result['user_created']]['name'],
            'label_2' => $this->lang->line('LABEL_DATE_CREATED_TIME'),
            'value_2' => System_helper::display_date_time($result['date_created'])
        );
        if ($result['user_updated'])
        {
            $data['info_basic'][] = array(
                'label_1' => $this->lang->line('LABEL_UPDATED_BY'),
                'value_1' => $user_info[$result['user_updated']]['name'],
                'label_2' => $this->lang->line('LABEL_DATE_UPDATED_TIME'),
                'value_2' => System_helper::display_date_time($result['date_updated'])
            );
        }
        return $data;
    }
}
