<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Report_sale_cash extends Root_Controller
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
        $this->permissions = User_helper::get_permission(get_class($this));
        $this->controller_url = strtolower(get_class($this));
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
        $this->lang->load('report_sale');
        $this->language_labels();
    }
    private function language_labels()
    {
        $this->lang->language['LABEL_AMOUNT_PAYABLE']='payable(actual)';
        $this->lang->language['LABEL_AMOUNT_SALE_CREDIT']='Credit Sale';
        $this->lang->language['LABEL_AMOUNT_SALE_CASH']='Cash Sale';
        $this->lang->language['LABEL_AMOUNT_CASH_PAYMENT']='Cash Payment';
        $this->lang->language['LABEL_AMOUNT_CASH_TOTAL']='Total Cash';
    }
    public function index($action="search",$id=0)
    {
        if($action=="search")
        {
            $this->system_search();
        }
        elseif($action=="list")
        {
            $this->system_list();
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
            $data['title']="Sales and Cash Collection Report Search";
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
        $user = User_helper::get_user();
        $method='list';
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
            $outlet_id=$reports['outlet_id'];
            $date_end=$reports['date_end'];
            $date_start=$reports['date_start'];

            $data=array();

            //total sales
            $this->db->from($this->config->item('table_pos_sale').' sale');
            $this->db->select('sale.outlet_id');

            $this->db->select('SUM(CASE WHEN sale.date_sale>='.$date_start.' and sale.date_sale<='.$date_end.' then sale.amount_payable ELSE 0 END) amount_payable',false);
            $this->db->select('SUM(CASE WHEN sale.date_sale>='.$date_start.' and sale.date_sale<='.$date_end.' and sale.sales_payment_method="Credit" then sale.amount_payable ELSE 0 END) amount_sale_credit',false);
            $this->db->select('SUM(CASE WHEN sale.date_sale>='.$date_start.' and sale.date_sale<='.$date_end.' and sale.sales_payment_method="Cash" then sale.amount_payable ELSE 0 END) amount_sale_cash',false);

            $this->db->where('sale.outlet_id',$outlet_id);
            $this->db->where_in('sale.status',$this->config->item('system_status_active'));
            $result=$this->db->get()->row_array();
            $data['item']['amount_payable']=$result['amount_payable'];
            $data['item']['amount_sale_credit']=$result['amount_sale_credit'];
            $data['item']['amount_sale_cash']=$result['amount_sale_cash'];
            //total cash payment
            $this->db->from($this->config->item('table_pos_farmer_credit_payment').' payment');
            $this->db->select('payment.outlet_id');
            $this->db->select('SUM(CASE WHEN payment.date_payment>='.$date_start.' and payment.date_payment<='.$date_end.' then payment.amount ELSE 0 END) amount_cash_payment',false);
            $this->db->where('payment.outlet_id',$outlet_id);
            $this->db->where_in('payment.status',$this->config->item('system_status_active'));
            $result=$this->db->get()->row_array();
            $data['item']['amount_cash_payment']=$result['amount_cash_payment'];
            $data['item']['amount_cash_total']=$data['item']['amount_sale_cash']+$result['amount_cash_payment'];

            $data['title']="Sales and Cash Collection Report";
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
    private function system_get_items()
    {
        $outlet_id=$this->input->post('outlet_id');
        $date_end=$this->input->post('date_end');
        $date_start=$this->input->post('date_start');


        $this->db->from($this->config->item('table_pos_sale').' sale');
        $this->db->select('sale.*');
        $this->db->join($this->config->item('table_pos_setup_farmer_farmer').' f','f.id = sale.farmer_id','INNER');
        $this->db->select('f.name customer_name');

        $this->db->where('sale.outlet_id',$outlet_id);

        $where='(sale.date_sale >='.$date_start.' AND sale.date_sale <='.$date_end.')';
        $where.=' OR (sale.date_cancel >='.$date_start.' AND sale.date_cancel <='.$date_end.')';
        $this->db->where('('.$where.')');
        $this->db->order_by('sale.date_sale','DESC');

        $items=$this->db->get()->result_array();
        $i=0;
        foreach($items as &$item)
        {
            $i++;
            if($item['status']==$this->config->item('system_status_active'))
            {
                $item['amount_actual']=$item['amount_payable_actual'];
            }
            else
            {
                if($item['date_sale']<$date_start)
                {
                    $item['amount_actual']=0-$item['amount_payable'];

                }
                elseif($item['date_cancel']>$date_end)
                {
                    $item['amount_actual']=$item['amount_payable_actual'];
                }
                else
                {
                    $item['amount_actual']=0;
                }
            }

            $item['sl_no']=$i;
            $item['invoice_no']=Barcode_helper::get_barcode_sales($item['id']);
            $item['date_sale']=System_helper::display_date($item['date_sale']);
            if($item['date_cancel']>0)
            {
                $item['date_cancel']=System_helper::display_date_time($item['date_cancel']);
            }
            else
            {
                $item['date_cancel']='';
            }
            if($item['sales_payment_method']=='Credit')
            {
                $item['amount_sale_cash']=0;
                $item['amount_sale_credit']=$item['amount_actual'];
            }
            else if($item['sales_payment_method']=='Cash')
            {
                $item['amount_sale_cash']=$item['amount_actual'];
                $item['amount_sale_credit']=0;
            }
            $item['discount_slab_percentage']=$item['discount_self_percentage'];
        }

        $this->json_return($items);
    }
}
