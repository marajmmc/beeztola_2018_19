<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Report_sale_invoice extends Root_Controller
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
        $this->permissions=User_helper::get_permission('Report_sale_invoice');
        $this->controller_url='report_sale_invoice';
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
            $data['title']="Invoice wise sales Report Search";
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
        $data['invoice_no']= 1;
        $data['customer_name']= 1;
        $data['date_sale']= 1;
        $data['date_cancel']= 1;
        $data['amount_total']= 1;
        $data['amount_discount_variety']= 1;
        $data['amount_discount_self']= 1;
        $data['amount_payable']= 1;
        $data['amount_payable_actual']= 1;
        $data['amount_actual']= 1;
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
        $grand_total=array();
        $grand_total['sl_no']='';
        $grand_total['invoice_no']='Grand Total';
        $grand_total['customer_name']='';
        $grand_total['date_sale']='';
        $grand_total['date_cancel']='';
        $grand_total['amount_total']=0;
        $grand_total['amount_discount_variety']=0;
        $grand_total['amount_discount_self']=0;
        $grand_total['amount_payable']=0;
        $grand_total['amount_payable_actual']=0;
        $grand_total['amount_actual']=0;
        $grand_total['status']='';
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
            $grand_total['amount_total']+=$item['amount_total'];
            $grand_total['amount_discount_variety']+=$item['amount_discount_variety'];
            $grand_total['amount_discount_self']+=$item['amount_discount_self'];
            $grand_total['amount_payable']+=$item['amount_payable'];
            $grand_total['amount_payable_actual']+=$item['amount_payable_actual'];
            $grand_total['amount_actual']+=$item['amount_actual'];



            $item['amount_total']=number_format($item['amount_total'],2);
            $item['amount_discount_variety']=number_format($item['amount_discount_variety'],2);
            $item['amount_discount_self']=number_format($item['amount_discount_self'],2);
            $item['amount_payable']=number_format($item['amount_payable'],2);
            $item['amount_payable_actual']=number_format($item['amount_payable_actual'],2);
            $item['amount_actual']=number_format($item['amount_actual'],2);

        }
        $grand_total['sl_no']=$i;
        $grand_total['amount_total']=number_format($grand_total['amount_total'],2);
        $grand_total['amount_discount_variety']=number_format($grand_total['amount_discount_variety'],2);
        $grand_total['amount_discount_self']=number_format($grand_total['amount_discount_self'],2);
        $grand_total['amount_payable']=number_format($grand_total['amount_payable'],2);
        $grand_total['amount_payable_actual']=number_format($grand_total['amount_payable_actual'],2);
        $grand_total['amount_actual']=number_format($grand_total['amount_actual'],2);

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
