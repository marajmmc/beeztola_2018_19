<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Report_payment extends Root_Controller
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
        $this->permissions=User_helper::get_permission('Report_payment');
        $this->controller_url='report_payment';
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
    public function index($action="search")
    {
        if($action=="search")
        {
            $this->system_search();
        }
        elseif($action=="list")
        {
            $this->system_list();
        }
        elseif($action=="get_items")
        {
            $this->system_get_items();
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
            $this->system_search();
        }
    }
    private function system_search()
    {
        if(isset($this->permissions['action0'])&&($this->permissions['action0']==1))
        {
            $data['assigned_outlet']=$this->user_outlets;
            $fiscal_years=Query_helper::get_info($this->config->item('table_login_basic_setup_fiscal_year'),'*',array());
            $data['fiscal_years']=array();
            foreach($fiscal_years as $year)
            {
                $data['fiscal_years'][]=array('text'=>$year['name'],'value'=>System_helper::display_date($year['date_start']).'/'.System_helper::display_date($year['date_end']));
            }
            $data['title']="Payment Report Search";
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/search",$data,true));
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
    private function system_list()
    {
        if(isset($this->permissions['action0'])&&($this->permissions['action0']==1))
        {
            $reports=$this->input->post('report');
            if(!$reports['outlet_id'])
            {
                $ajax['status']=false;
                $ajax['system_message']='This outlet field is required';
                $this->json_return($ajax);
            }
            $reports['date_end']=System_helper::get_time($reports['date_end'])+3600*24-1;
            $reports['date_start']=System_helper::get_time($reports['date_start']);
            if($reports['date_start']>=$reports['date_end'])
            {
                $ajax['status']=false;
                $ajax['system_message']='Starting Date should be less than End date';
                $this->json_return($ajax);
            }
            $data['options']=$reports;
            $data['system_preference_items']= $this->get_preference();
            $data['title']="Outlet Payment Report";
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>$this->load->view($this->controller_url."/list",$data,true));
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
    private function get_preference()
    {
        $user = User_helper::get_user();
        $result=Query_helper::get_info($this->config->item('table_system_user_preference'),'*',array('user_id ='.$user->user_id,'controller ="' .$this->controller_url.'"','method ="search"'),1);
        $data['sl_no']= 1;
        $data['barcode']= 1;
        $data['date_payment']= 1;
        $data['date_sale']= 1;
        $data['payment_way']= 1;
        $data['reference_no']= 1;
        $data['amount_payment']= 1;
        $data['amount_bank_charge']= 1;
        $data['amount_receive']= 1;
        $data['bank_payment_source']= 1;
        $data['bank_branch_source']= 1;
        $data['bank_account_number_destination']= 1;
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
    private function system_get_items()
    {
        $outlet_id=$this->input->post('outlet_id');
        $status_deposit_forward=$this->input->post('status_deposit_forward');
        $status_payment_receive=$this->input->post('status_payment_receive');
        $search_by=$this->input->post('search_by');
        $date_end=$this->input->post('date_end');
        $date_start=$this->input->post('date_start');
        $this->db->from($this->config->item('table_pos_payment').' payment');
        $this->db->select('payment.*');


        $this->db->select('payment_way.name payment_way');
        $this->db->join($this->config->item('table_login_setup_payment_way').' payment_way','payment_way.id=payment.payment_way_id','INNER');
        $this->db->select('bank_source.name bank_payment_source');
        $this->db->join($this->config->item('table_login_setup_bank').' bank_source','bank_source.id=payment.bank_id_source','INNER');
        $this->db->join($this->config->item('table_login_setup_bank_account').' ba','ba.id=payment.bank_account_id_destination','LEFT');
        $this->db->select('bank_destination.name bank_destination, ba.account_number, ba.branch_name');
        $this->db->join($this->config->item('table_login_setup_bank').' bank_destination','bank_destination.id=ba.bank_id','LEFT');

        $this->db->where('payment.status !=',$this->config->item('system_status_delete'));
        $this->db->where('payment.status_deposit_forward',$status_deposit_forward);
        $this->db->where('payment.status_payment_receive',$status_payment_receive);
        if($search_by=='search_by_sale_date')
        {
            $this->db->where('payment.date_sale >=',$date_start);
            $this->db->where('payment.date_sale <=',$date_end);
        }
        else if($search_by=='search_by_payment_date')
        {
            $this->db->where('payment.date_payment >=',$date_start);
            $this->db->where('payment.date_payment <=',$date_end);
        }
        $this->db->where('payment.outlet_id',$outlet_id);
        $this->db->order_by('payment.id','DESC');
        $items=$this->db->get()->result_array();
        $grand_total=array();
        $grand_total['sl_no']='';
        $grand_total['barcode']='Grand Total';
        $grand_total['date_payment']='';
        $grand_total['date_sale']='';
        $grand_total['payment_way']='';
        $grand_total['reference_no']='';
        $grand_total['amount_payment']=0;
        $grand_total['amount_bank_charge']=0;
        $grand_total['amount_receive']=0;
        $grand_total['bank_payment_source']='';
        $grand_total['bank_branch_source']='';
        $grand_total['bank_account_number_destination']='';
        $i=0;
        foreach($items as &$item)
        {
            $i++;
            $item['sl_no']=$i;
            $item['barcode']=Barcode_helper::get_barcode_payment($item['id']);
            $item['date_payment']=System_helper::display_date($item['date_payment']);
            $item['date_sale']=System_helper::display_date($item['date_sale']);
            $grand_total['amount_payment']+=$item['amount_payment'];
            $grand_total['amount_bank_charge']+=$item['amount_bank_charge'];
            $grand_total['amount_receive']+=$item['amount_receive'];
            $item['amount_payment']=number_format($item['amount_payment'],2);
            $item['amount_bank_charge']=number_format($item['amount_bank_charge'],2);
            $item['amount_receive']=number_format($item['amount_receive'],2);
            $item['bank_account_number_destination']=$item['account_number'].'('.$item['bank_destination'].' -'.$item['branch_name'].')';
        }
        $grand_total['sl_no']=$i;
        $grand_total['amount_payment']=number_format($grand_total['amount_payment'],2);
        $grand_total['amount_bank_charge']=number_format($grand_total['amount_bank_charge'],2);
        $grand_total['amount_receive']=number_format($grand_total['amount_receive'],2);
        $items[]=$grand_total;

        $this->json_return($items);
    }
    private function system_set_preference()
    {
        if(isset($this->permissions['action6']) && ($this->permissions['action6']==1))
        {
            $data['system_preference_items']= $this->get_preference();
            $data['preference_method_name']='search';
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
}
