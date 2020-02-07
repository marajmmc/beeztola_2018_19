<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Report_farmer_balance_notification_csv extends CI_Controller
{
    public $current_user;
    public $controller_url;
    public $controller_main_url;

    public function __construct()
    {
        parent::__construct();
        $this->controller_url = strtolower(get_class($this));
        $this->controller_main_url = strtolower(str_replace("_csv", "", $this->controller_url));
        $this->permissions = User_helper::get_permission('Report_farmer_balance_notification');
        $user = User_helper::get_user();
        if (!$user) {
            echo 'Please Login and Try Again';
            die();
        }
        $this->language_labels();
        $this->load->helper('csv');
    }

    private function language_labels()
    {
        $this->lang->language['LABEL_AMOUNT_CREDIT_LIMIT'] = 'Credit Limit';
        $this->lang->language['LABEL_AMOUNT_CREDIT_BALANCE'] = 'Available Credit';
        $this->lang->language['LABEL_AMOUNT_CREDIT_DUE'] = 'Due';
        $this->lang->language['LABEL_AMOUNT_LAST_PAYMENT'] = 'Last payment amount';
        $this->lang->language['LABEL_DATE_LAST_PAYMENT'] = 'Last Payment Date';
        $this->lang->language['LABEL_DAY_LAST_PAYMENT'] = 'Last Payment days';
        $this->lang->language['LABEL_AMOUNT_LAST_SALE'] = 'Last Invoice amount';
        $this->lang->language['LABEL_DATE_LAST_SALE'] = 'Last Invoice Date';
        $this->lang->language['LABEL_DAY_LAST_SALE'] = 'Last Invoice days';
        $this->lang->language['LABEL_DAY_COLOR_PAYMENT_START'] = 'Payment warning color start(days)';
        $this->lang->language['LABEL_DAY_COLOR_PAYMENT_INTERVAL'] = 'Payment warning color interval(days)';

        $this->lang->language['LABEL_DAY_COLOR_SALES_START'] = 'Invoice warning color start(days)';
        $this->lang->language['LABEL_DAY_COLOR_SALES_INTERVAL'] = 'Invoice warning color interval(days)';

        $this->lang->language['LABEL_SALE_DUE_STATUS'] = 'Last Invoice due status';
    }

    private function get_preference_headers($method)
    {
        $data = array();
        $data['id'] = 0;
        $data['outlet_name'] = 1;
        $data['barcode'] = 0;
        $data['name'] = 1;
        $data['amount_credit_limit'] = 0;
        $data['amount_credit_balance'] = 0;
        $data['amount_credit_due'] = 1;

        $data['amount_last_payment'] = 0;
        $data['date_last_payment'] = 1;
        $data['day_last_payment'] = 1;

        $data['amount_last_sale'] = 0;
        $data['date_last_sale'] = 1;
        $data['day_last_sale'] = 1;
        if(isset($this->permissions['action7']) && ($this->permissions['action7']==1))
        {
            $data['sale_due_status'] = 1;
        }
        return $data;
    }

    public function system_list_farmer_balance_notification($params = array())
    {
        $user = User_helper::get_user();
        $time = time();
        $options = json_decode(urldecode($params), true);

        $outlet_id = $options['outlet_id'];

        $this->db->from($this->config->item('table_login_csetup_cus_info') . ' outlet_info');
        $this->db->select('outlet_info.customer_id outlet_id, outlet_info.name outlet_name');

        $this->db->join($this->config->item('table_login_setup_location_districts') . ' districts', 'districts.id = outlet_info.district_id', 'INNER');
        $this->db->join($this->config->item('table_login_setup_location_territories') . ' territories', 'territories.id = districts.territory_id', 'INNER');
        $this->db->join($this->config->item('table_login_setup_location_zones') . ' zones', 'zones.id = territories.zone_id', 'INNER');

        $this->db->where('outlet_info.revision', 1);
        $this->db->where('outlet_info.type', $this->config->item('system_customer_type_outlet_id'));
        $this->db->where('outlet_info.customer_id', $outlet_id);
        $this->db->order_by('outlet_info.ordering');
        $results = $this->db->get()->result_array();

        $outlets = array();
        $outlet_ids = array(0);
        foreach ($results as $result) {
            $outlets[$result['outlet_id']] = $result['outlet_name'];
            $outlet_ids[$result['outlet_id']] = $result['outlet_id'];
        }
        //payment
        $this->db->from($this->config->item('table_pos_farmer_credit_payment') . ' payment');
        $this->db->select('MAX( payment.date_payment ) AS date_last_payment');
        $this->db->select('payment.farmer_id');
        $this->db->where_in('payment.outlet_id', $outlet_ids);
        $this->db->group_by('payment.farmer_id');
        $sub_query = $this->db->get_compiled_select();

        $this->db->from($this->config->item('table_pos_farmer_credit_payment') . ' payment');
        $this->db->select('payment.farmer_id');
        $this->db->select('payment.amount amount_last_payment');
        $this->db->select('payment.date_payment date_last_payment');
        $this->db->join('(' . $sub_query . ') payment_max', 'payment_max.farmer_id = payment.farmer_id AND payment_max.date_last_payment= payment.date_payment', 'INNER');
        $results = $this->db->get()->result_array();
        $payment = array();
        foreach ($results as $result) {
            $payment[$result['farmer_id']] = $result;
        }
        //sales

        $this->db->from($this->config->item('table_pos_sale') . ' sale');
        $this->db->select('MAX( sale.date_sale ) AS date_last_sale');
        $this->db->select('sale.farmer_id');
        $this->db->where_in('sale.outlet_id', $outlet_ids);
        $this->db->where('sale.status', $this->config->item('system_status_active'));
        $this->db->where('sale.sales_payment_method', 'Credit');
        $this->db->group_by('sale.farmer_id');
        $sub_query = $this->db->get_compiled_select();

        $this->db->from($this->config->item('table_pos_sale') . ' sale');
        $this->db->select('sale.farmer_id');
        $this->db->select('sale.amount_payable_actual amount_last_sale');
        $this->db->select('sale.date_sale date_last_sale');
        $this->db->join('(' . $sub_query . ') sale_max', 'sale_max.farmer_id = sale.farmer_id AND sale_max.date_last_sale= sale.date_sale', 'INNER');
        $this->db->where('sale.status', $this->config->item('system_status_active'));
        $this->db->where('sale.sales_payment_method', 'Credit');
        $results = $this->db->get()->result_array();

        $sales = array();
        foreach ($results as $result) {
            $sales[$result['farmer_id']] = $result;
        }

        //dealers
        $this->db->from($this->config->item('table_pos_setup_farmer_farmer') . ' farmer');
        $this->db->select('farmer.id, farmer.name, farmer.amount_credit_limit, farmer.amount_credit_balance');

        $this->db->join($this->config->item('table_pos_setup_farmer_outlet') . ' farmer_outlet', 'farmer_outlet.farmer_id = farmer.id AND farmer_outlet.revision =1', 'INNER');
        $this->db->select('farmer_outlet.outlet_id');
        $this->db->where('farmer.amount_credit_limit > ', 0);
        $this->db->where_in('farmer_outlet.outlet_id', $outlet_ids);
        $this->db->order_by('farmer_outlet.id');
        $this->db->order_by('farmer.id DESC');
        $items = $this->db->get()->result_array();
        foreach ($items as &$item) {
            $item['outlet_name'] = $outlets[$item['outlet_id']];
            $item['barcode'] = Barcode_helper::get_barcode_farmer($item['id']);
            $item['amount_credit_due'] = $item['amount_credit_limit'] - $item['amount_credit_balance'];
            if (isset($payment[$item['id']])) {
                $item['amount_last_payment'] = $payment[$item['id']]['amount_last_payment'];
                $item['date_last_payment'] = System_helper::display_date($payment[$item['id']]['date_last_payment']);
                $item['day_last_payment'] = intval(($time - $payment[$item['id']]['date_last_payment']) / (3600 * 24));
            } else {
                $item['amount_last_payment'] = 0;
                $item['date_last_payment'] = 0;
                $item['day_last_payment'] = 0;
            }

            if (isset($sales[$item['id']])) {
                $item['amount_last_sale'] = $sales[$item['id']]['amount_last_sale'];
                $item['date_last_sale'] = System_helper::display_date($sales[$item['id']]['date_last_sale']);
                $item['day_last_sale'] = intval(($time - $sales[$item['id']]['date_last_sale']) / (3600 * 24));
            } else {
                $item['amount_last_sale'] = 0;
                $item['date_last_sale'] = 0;
                $item['day_last_sale'] = 0;
            }
            $item['sale_due_status'] = '--';
            if ($item['amount_credit_due'] == 0) {
                $item['sale_due_status'] = 'No Due';
            } else if ($item['amount_credit_due'] == $item['amount_last_sale']) {
                $item['sale_due_status'] = 'Due was Cleared';
            } else if ($item['amount_credit_due'] > $item['amount_last_sale']) {
                $item['sale_due_status'] = 'Due was not Cleared';
            } else {
                if ($item['date_last_payment'] <= $item['date_last_sale']) {
                    $item['sale_due_status'] = 'Due was Cleared';
                } else {
                    $item['sale_due_status'] = 'Partial paid after invoice';
                }
            }

        }

        uasort($items, function($a, $b) {
            return $b['day_last_payment'] - $a['day_last_payment'];
        });

        $method = 'search';
        $preferences = System_helper::get_preference($user->user_id, $this->controller_main_url, $method, $this->get_preference_headers($method));
        $fields_price = array('amount_credit_limit', 'amount_credit_balance', 'amount_credit_due');
        $fields_kg = array('amount_last_payment', 'amount_last_sale');
        Csv_helper::get_csv($items, $preferences, 'dealer_due_report.csv', $fields_price, $fields_kg);
    }
}
