<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Report_farmer_payment extends Root_Controller
{
    public $message;
    public $permissions;
    public $controller_url;
    public $user_outlets;

    public function __construct()
    {
        parent::__construct();
        $this->message = "";
        $this->permissions = User_helper::get_permission(get_class($this));
        $this->controller_url = strtolower(get_class($this));
        $this->user_outlets = User_helper::get_assigned_outlets();
        if (!(sizeof($this->user_outlets) > 0)) {
            $ajax['status'] = false;
            $ajax['system_message'] = $this->lang->line('MSG_OUTLET_NOT_ASSIGNED');
            $this->json_return($ajax);
        }
        $this->language_labels();
    }

    private function language_labels()
    {
        $this->lang->language['LABEL_DEALER_NAME'] = 'Dealer';
        $this->lang->language['LABEL_DATE_PAYMENT'] = 'Payment Date';
        $this->lang->language['LABEL_AMOUNT_PAID_TOTAL'] = 'Total Paid Amount';
        $this->lang->language['LABEL_AMOUNT_PAYMENT'] = 'Payment Amount';
        $this->lang->language['LABEL_PAYMENT_WAY'] = 'Payment Way';
        $this->lang->language['LABEL_REFERENCE_NO'] = 'Reference No';
        $this->lang->language['LABEL_CREATED_BY'] = 'Created By';
        $this->lang->language['LABEL_DATE_CREATED_TIME'] = 'Created Time';
        $this->lang->language['LABEL_UPDATED_BY'] = 'Updated By';
        $this->lang->language['LABEL_DATE_UPDATED_TIME'] = 'Updated Time';
    }

    public function index($action = "search", $id = 0)
    {
        if ($action == "search") {
            $this->system_search();
        } elseif ($action == "list") {
            $this->system_list();
        } elseif ($action == "get_items") {
            $this->system_get_items();
        } elseif ($action == "get_dealers") {
            $this->system_get_dealers();
        } elseif ($action == "details_payment") {
            $this->system_details_payment($id);
        } elseif ($action == "set_preference") {
            $this->system_set_preference();
        } elseif ($action == "save_preference") {
            System_helper::save_preference();
        } else {
            $this->system_search();
        }
    }

    private function get_preference_headers($method)
    {
        $data = array();
        if ($method == 'search_list') {
            $data['id'] = 1;
            $data['barcode'] = 1;
            $data['date_payment'] = 1;
            $data['outlet'] = 1;
            $data['dealer_name'] = 1;
            $data['amount_paid_total'] = 1;
            $data['button_details'] = 1;
        }
        return $data;
    }

    private function system_set_preference()
    {
        $user = User_helper::get_user();
        $method = 'search_list';
        if (isset($this->permissions['action6']) && ($this->permissions['action6'] == 1)) {
            $data['system_preference_items'] = System_helper::get_preference($user->user_id, $this->controller_url, $method, $this->get_preference_headers($method));
            $data['preference_method_name'] = $method;
            $ajax['status'] = true;
            $ajax['system_content'][] = array("id" => "#system_content", "html" => $this->load->view("preference_add_edit", $data, true));
            $ajax['system_page_url'] = site_url($this->controller_url . '/index/set_preference_' . $method);
            $this->json_return($ajax);
        } else {
            $ajax['status'] = false;
            $ajax['system_message'] = $this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }

    private function system_search()
    {
        if (isset($this->permissions['action0']) && ($this->permissions['action0'] == 1)) {
            $data = array();
            $data['outlets'] = $this->user_outlets;

            $fiscal_years = Query_helper::get_info($this->config->item('table_login_basic_setup_fiscal_year'), '*', array());
            $data['fiscal_years'] = array();
            foreach ($fiscal_years as $year) {
                $data['fiscal_years'][] = array('text' => $year['name'], 'value' => System_helper::display_date($year['date_start']) . '/' . System_helper::display_date($year['date_end']));
            }

            $data['title'] = "Dealer Payment Report";
            $ajax['status'] = true;
            $ajax['system_content'][] = array("id" => "#system_content", "html" => $this->load->view($this->controller_url . "/search", $data, true));
            $ajax['system_page_url'] = site_url($this->controller_url);
            if ($this->message) {
                $ajax['system_message'] = $this->message;
            }

            $this->json_return($ajax);
        } else {
            $ajax['status'] = false;
            $ajax['system_message'] = $this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }

    private function system_list()
    {
        if (isset($this->permissions['action0']) && ($this->permissions['action0'] == 1)) {
            $user = User_helper::get_user();
            $method = 'search_list';

            $data = array();
            $data['options'] = $this->input->post('report');

            $data['title'] = "Dealers Payment Report";
            $ajax['status'] = true;
            $data['system_preference_items'] = System_helper::get_preference($user->user_id, $this->controller_url, $method, $this->get_preference_headers($method));
            $ajax['system_content'][] = array("id" => "#system_report_container", "html" => $this->load->view($this->controller_url . "/list", $data, true));
            if ($this->message) {
                $ajax['system_message'] = $this->message;
            }
            $ajax['system_page_url'] = site_url($this->controller_url);
            $this->json_return($ajax);
        } else {
            $ajax['status'] = false;
            $ajax['system_message'] = $this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }

    private function system_get_items()
    {
        // Post Input
        // $fiscal_year_id = $this->input->post('fiscal_year_id');
        $date_start = System_helper::get_time($this->input->post('date_start'));
        $date_end = System_helper::get_time($this->input->post('date_end'));

        $outlet_id = $this->input->post('outlet_id');
        $farmer_id = $this->input->post('farmer_id');

        $this->db->from($this->config->item('table_login_csetup_cus_info') . ' outlet_info');
        $this->db->select('outlet_info.customer_id outlet_id, outlet_info.name outlet');

        $this->db->join($this->config->item('table_login_setup_location_districts') . ' districts', 'districts.id = outlet_info.district_id', 'INNER');
        $this->db->join($this->config->item('table_login_setup_location_territories') . ' territories', 'territories.id = districts.territory_id', 'INNER');
        $this->db->join($this->config->item('table_login_setup_location_zones') . ' zones', 'zones.id = territories.zone_id', 'INNER');

        $this->db->where('outlet_info.revision', 1);
        $this->db->where('outlet_info.type', $this->config->item('system_customer_type_outlet_id'));

        if ($outlet_id > 0) {
            $this->db->where('outlet_info.customer_id', $outlet_id);
        }
        $this->db->order_by('outlet_info.customer_id');
        $results = $this->db->get()->result_array();

        $outlet_ids = array();
        foreach ($results as $result) {
            $outlet_ids[] = $result['outlet_id'];
        }

        $this->db->from($this->config->item('table_pos_farmer_credit_payment') . ' payment');
        $this->db->select('payment.id, payment.date_payment, payment.amount amount_paid_total');

        $this->db->join($this->config->item('table_login_csetup_cus_info') . ' customer_info', 'customer_info.customer_id = payment.outlet_id AND customer_info.revision =1', 'LEFT');
        $this->db->select('customer_info.customer_id outlet_id, customer_info.name outlet');

        $this->db->join($this->config->item('table_pos_setup_farmer_farmer') . ' farmer', 'farmer.id = payment.farmer_id', 'INNER');
        $this->db->select('farmer.id farmer_id, farmer.name dealer_name');

        $this->db->where('payment.status !=', $this->config->item('system_status_delete'));
        $this->db->where('farmer.status', $this->config->item('system_status_active'));
        $this->db->where('farmer.farmer_type_id > ', 1);
        $this->db->where('farmer.amount_credit_limit > ', 0);
        if ($date_start) {
            $this->db->where('payment.date_payment >=', $date_start);
        }
        if ($date_end) {
            $this->db->where('payment.date_payment <=', $date_end);
        }

        if ($farmer_id > 0) {
            $this->db->where('payment.farmer_id', $farmer_id);
        } elseif (sizeof($outlet_ids) > 0) {
            $this->db->where_in('payment.outlet_id', $outlet_ids);
        }
        $this->db->order_by('payment.farmer_id', 'DESC');
        $this->db->order_by('payment.id', 'DESC');
        $items = $this->db->get()->result_array();
        foreach ($items as &$item) {
            $item['barcode'] = Barcode_helper::get_barcode_dealer_payment($item['id']);
            $item['date_payment'] = System_helper::display_date($item['date_payment']);
        }

        $this->json_return($items);
    }

    private function system_get_dealers()
    {
        $outlet_id = $this->input->post('outlet_id');

        $this->db->from($this->config->item('table_pos_setup_farmer_outlet') . ' farmer_outlet');
        $this->db->select('farmer_outlet.farmer_id value');

        $this->db->join($this->config->item('table_pos_setup_farmer_farmer') . ' farmer', 'farmer.id=farmer_outlet.farmer_id', 'INNER');
        $this->db->select('farmer.name text');

        $this->db->where('farmer.status', $this->config->item('system_status_active'));
        $this->db->where('farmer_outlet.revision', 1);

        $this->db->where('farmer_outlet.outlet_id', $outlet_id);
        $this->db->where('farmer.farmer_type_id > ', 1);
        $this->db->where('farmer.amount_credit_limit > ', 0);

        $this->db->order_by('farmer.id');
        $data['items'] = $this->db->get()->result_array();
        $ajax['status'] = true;
        $ajax['system_content'][] = array("id" => '#farmer_id', "html" => $this->load->view("dropdown_with_select", $data, true));
        $this->json_return($ajax);
    }

    private function system_details_payment($id)
    {
        if (isset($this->permissions['action0']) && ($this->permissions['action0'] == 1)) {
            if ($id > 0) {
                $item_id = $id;
            } else {
                $item_id = $this->input->post('id');
            }

            $this->db->from($this->config->item('table_pos_farmer_credit_payment') . ' payment');
            $this->db->select('payment.*, payment.amount amount_paid_total');

            $this->db->join($this->config->item('table_login_csetup_cus_info') . ' customer_info', 'customer_info.customer_id = payment.outlet_id AND customer_info.revision =1', 'LEFT');
            $this->db->select('customer_info.customer_id outlet_id, customer_info.name outlet');

            $this->db->join($this->config->item('table_pos_setup_farmer_farmer') . ' farmer', 'farmer.id = payment.farmer_id', 'INNER');
            $this->db->select('farmer.id farmer_id, farmer.name dealer_name, farmer.mobile_no, farmer.address');

            $this->db->join($this->config->item('table_login_setup_payment_way') . ' payment_way', 'payment_way.id = payment.payment_way_id', 'INNER');
            $this->db->select('payment_way.name payment_way');

            $this->db->where('payment.status !=', $this->config->item('system_status_delete'));
            $this->db->where('payment.id', $item_id);
            $result = $this->db->get()->row_array();
            if (!$result) {
                System_helper::invalid_try(__FUNCTION__, $item_id, 'ID Not Exist');
                $ajax['status'] = false;
                $ajax['system_message'] = 'Invalid Try.';
                $this->json_return($ajax);
            }

            //--------- System User Info ------------
            $user_ids = array();
            $user_ids[$result['user_created']] = $result['user_created'];
            if ($result['user_updated'] > 0) {
                $user_ids[$result['user_updated']] = $result['user_updated'];
            }
            if ($result['user_deleted'] > 0) {
                $user_ids[$result['user_deleted']] = $result['user_deleted'];
            }
            $user_info = System_helper::get_users_info($user_ids);

            //---------------- Basic Info ----------------
            $data = $item = array();
            $item[] = array('label_1' => 'Basic Information');
            $item[] = array
            (
                'label_1' => $this->lang->line('LABEL_DEALER_NAME'),
                'value_1' => $result['dealer_name']
            );
            $item[] = array
            (
                'label_1' => $this->lang->line('LABEL_MOBILE_NO'),
                'value_1' => $result['mobile_no']
            );
            $item[] = array
            (
                'label_1' => $this->lang->line('LABEL_ADDRESS'),
                'value_1' => nl2br($result['address'])
            );

            //---------------- Payment Info ----------------
            $item[] = array('label_1' => 'Payment Information');
            $item[] = array
            (
                'label_1' => $this->lang->line('LABEL_BARCODE'),
                'value_1' => '<span style="display:inline-block; text-align:center"><img src="' . site_url('barcode/index/dealer_payment/' . $item_id) . '"> <br/>' . (Barcode_helper::get_barcode_dealer_payment($result['id'])) . '</span>'
            );
            $item[] = array
            (
                'label_1' => $this->lang->line('LABEL_OUTLET'),
                'value_1' => $result['outlet']
            );
            $item[] = array
            (
                'label_1' => $this->lang->line('LABEL_DATE_PAYMENT'),
                'value_1' => System_helper::display_date($result['date_payment'])
            );
            $item[] = array
            (
                'label_1' => $this->lang->line('LABEL_AMOUNT_PAYMENT'),
                'value_1' => System_helper::get_string_amount($result['amount'])
            );
            $item[] = array
            (
                'label_1' => $this->lang->line('LABEL_PAYMENT_WAY'),
                'value_1' => $result['payment_way']
            );
            $item[] = array
            (
                'label_1' => $this->lang->line('LABEL_REFERENCE_NO'),
                'value_1' => $result['reference_no']
            );
            $item[] = array
            (
                'label_1' => $this->lang->line('LABEL_REMARKS'),
                'value_1' => nl2br($result['remarks']),
            );

            $item[] = array
            (
                'label_1' => $this->lang->line('LABEL_CREATED_BY'),
                'value_1' => $user_info[$result['user_created']]['name']
            );
            $item[] = array
            (
                'label_1' => $this->lang->line('LABEL_DATE_CREATED_TIME'),
                'value_1' => System_helper::display_date_time($result['date_created'])
            );
            if ($result['user_updated']) {
                $item[] = array
                (
                    'label_1' => $this->lang->line('LABEL_UPDATED_BY'),
                    'value_1' => $user_info[$result['user_updated']]['name']
                );
                $item[] = array
                (
                    'label_1' => $this->lang->line('LABEL_DATE_UPDATED_TIME'),
                    'value_1' => System_helper::display_date_time($result['date_updated'])
                );
            }

            $data['accordion'] = array(
                'header' => "Payment Details (Payment ID: " . $item_id . ")",
                'collapse' => 'in',
                'data' => $item
            );

            $ajax['status'] = true;
            $ajax['system_content'][] = array("id" => "#popup_content", "html" => $this->load->view("info_basic", $data, true));
            if ($this->message) {
                $ajax['system_message'] = $this->message;
            }
            $this->json_return($ajax);
        } else {
            $ajax['status'] = false;
            $ajax['system_message'] = $this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
}
