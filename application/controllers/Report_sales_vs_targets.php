<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Report_sales_vs_targets extends Root_Controller
{
    public $message;
    public $permissions;
    public $controller_url;
    public $user_outlets;
    public $user_outlet_ids;

    public function __construct()
    {
        parent::__construct();
        $this->message = "";
        $this->permissions = User_helper::get_permission(get_class($this));
        $this->user_outlets = User_helper::get_assigned_outlets();
        if (sizeof($this->user_outlets) > 0) {
            foreach ($this->user_outlets as $row) {
                $this->user_outlet_ids[] = $row['customer_id'];
            }
        }
        else {
            $ajax = array();
            $ajax['status'] = false;
            $ajax['system_message'] = $this->lang->line('MSG_OUTLET_NOT_ASSIGNED');
            $this->json_return($ajax);
        }
        $this->controller_url = strtolower(get_class($this));
        $this->lang->load('report_sale');
        //$this->config->load('table_bms');
        $this->language_labels();
    }

    private function language_labels()
    {
        $this->lang->language['LABEL_AMOUNT_TARGET'] = 'Target Amount';
        $this->lang->language['LABEL_AMOUNT_SALES_CASH'] = 'Cash Sale';
        $this->lang->language['LABEL_AMOUNT_SALES_CREDIT'] = 'Credit Sale';
        $this->lang->language['LABEL_AMOUNT_SALES'] = 'Total Sales';
        $this->lang->language['LABEL_AMOUNT_SALES_CASH_AVERAGE'] = 'Cash Sale (%)';
        $this->lang->language['LABEL_AMOUNT_SALES_CREDIT_AVERAGE'] = 'Credit Sale (%)';
        $this->lang->language['LABEL_AMOUNT_DEFERENCE'] = 'Variance';
        $this->lang->language['LABEL_AMOUNT_AVERAGE'] = 'Achievement (%)';
        $this->lang->language['LABEL_AREA'] = 'Territory'; // Only for this Task!
    }

    public function index($action = "search", $id = 0)
    {
        if ($action == "search") {
            $this->system_search();
        }
        elseif ($action == "list") {
            $this->system_list();
        }
        elseif ($action == "set_preference") {
            $this->system_set_preference('list');
        }
        elseif ($action == "get_items") {
            $this->system_get_items();
        }
        elseif ($action == "save_preference") {
            System_helper::save_preference();
        }
        else {
            $this->system_search();
        }
    }

    private function get_preference_headers($method)
    {
        $data = array();
        if ($method == 'list') {
            $data['sl_no'] = 1;
            $data['area'] = 1;
            $data['amount_target'] = 1;
            $data['amount_sales_cash'] = 1;
            $data['amount_sales_credit'] = 1;
            $data['amount_sales'] = 1;
            $data['amount_sales_cash_average'] = 1;
            $data['amount_sales_credit_average'] = 1;
            $data['amount_deference'] = 1;
            $data['amount_average'] = 1;
        }
        else {

        }
        return $data;
    }

    private function system_set_preference($method)
    {
        $user = User_helper::get_user();
        if (isset($this->permissions['action6']) && ($this->permissions['action6'] == 1)) {
            $data = array();
            $data['system_preference_items'] = System_helper::get_preference($user->user_id, $this->controller_url, $method, $this->get_preference_headers($method));
            $data['preference_method_name'] = $method;

            $ajax = array();
            $ajax['status'] = true;
            $ajax['system_content'][] = array("id" => "#system_content", "html" => $this->load->view("preference_add_edit", $data, true));
            $ajax['system_page_url'] = site_url($this->controller_url . '/index/set_preference_' . $method);
            $this->json_return($ajax);
        }
        else {
            $ajax = array();
            $ajax['status'] = false;
            $ajax['system_message'] = $this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }

    private function system_search()
    {
        if (isset($this->permissions['action0']) && ($this->permissions['action0'] == 1)) {
            $data = array();

            $fiscal_years=Query_helper::get_info($this->config->item('table_login_basic_setup_fiscal_year'),'*',array());
            foreach($fiscal_years as $year)
            {
                $data['fiscal_years'][]=array('text'=>$year['name'],'value'=>System_helper::display_date($year['date_start']).'/'.System_helper::display_date($year['date_end']));
            }
            $data['assigned_outlet'] = $this->user_outlets;
            $data['title'] = "Sales Vs Targets Report";

            $ajax = array();
            $ajax['status'] = true;
            $ajax['system_content'][] = array("id" => "#system_content", "html" => $this->load->view($this->controller_url . "/search", $data, true));
            $ajax['system_page_url'] = site_url($this->controller_url);
            if ($this->message) {
                $ajax['system_message'] = $this->message;
            }
            $this->json_return($ajax);
        }
        else {
            $ajax = array();
            $ajax['status'] = false;
            $ajax['system_message'] = $this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }

    private function system_list()
    {
        if (isset($this->permissions['action0']) && ($this->permissions['action0'] == 1)) {
            $user = User_helper::get_user();
            $method = 'list';

            $reports = $this->input->post('report');
            $reports['date_end'] = System_helper::get_time($reports['date_end']);
            $reports['date_end'] = $reports['date_end'] + 3600 * 24 - 1;
            $reports['date_start'] = System_helper::get_time($reports['date_start']);
            if ($reports['date_start'] >= $reports['date_end']) {
                $ajax = array();
                $ajax['status'] = false;
                $ajax['system_message'] = 'Starting Date should be less than End date';
                $this->json_return($ajax);
            }

            $data = array();
            $data['options'] = $reports;
            $data['system_preference_items'] = System_helper::get_preference($user->user_id, $this->controller_url, $method, $this->get_preference_headers($method));
            $data['areas'] = 'Territories';
            $data['title'] = "Sales Vs Achievement Report";

            $ajax = array();
            $ajax['status'] = true;
            $ajax['system_content'][] = array("id" => "#system_report_container", "html" => $this->load->view($this->controller_url . "/list", $data, true));
            if ($this->message) {
                $ajax['system_message'] = $this->message;
            }
            $this->json_return($ajax);
        }
        else {
            $ajax = array();
            $ajax['status'] = false;
            $ajax['system_message'] = $this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }

    }

    private function system_get_items()
    {
        $items = array();

        $date_end = $this->input->post('date_end');
        $date_start = $this->input->post('date_start');
        $date_start_target = System_helper::get_time('01-' . date('m-Y', $date_start));
        $date_end_target = System_helper::get_time(date('t-m-Y', $date_end));

        $outlet_id = $this->input->post('outlet_id');

        $areas = $this->get_territory_ids_by_outlet($outlet_id);
        $territory_ids = array(0);
        foreach ($areas as $territory) {
            $territory_ids[] = $territory['value'];
        }
        $location_type = 'territory_id';

        $this->db->from($this->config->item('table_bms_target_territory') . ' items');
        $this->db->select($location_type . ', items.amount_target');
        $this->db->select("TIMESTAMPDIFF(SECOND, '1970-01-01', CONCAT_WS('-', items.year, lpad(items.month,2,'0'), '01')) AS date_target ");

        $this->db->join($this->config->item('table_bms_target_zone') . ' zone_target', 'zone_target.id = items.target_zone_id', 'INNER');

        $this->db->where_in('items.territory_id', $territory_ids);
        $this->db->where('items.status', $this->config->item('system_status_active'));
        $this->db->having(array('date_target >=' => $date_start_target, 'date_target <=' => $date_end_target));
        $queries = $this->db->get()->result_array();

        $area_initial = array();
        foreach ($areas as $area) {
            $area_initial[$area['value']] = $this->initialize_row_area_amount($area['text']);
        }

        foreach ($queries as $result) {
            if (isset($area_initial[$result[$location_type]]['amount_target'])) {
                $area_initial[$result[$location_type]]['amount_target'] += $result['amount_target'];
            }
            else {
                $area_initial[$result[$location_type]]['amount_target'] = $result['amount_target'];
            }
        }

        $this->db->from($this->config->item('table_pos_sale') . ' sale');
        $this->db->select('SUM(sale.amount_payable) sale_amount, sale.sales_payment_method');

        $this->db->join($this->config->item('table_login_csetup_cus_info') . ' outlet_info', 'outlet_info.customer_id = sale.outlet_id and outlet_info.revision =1', 'INNER');
        $this->db->join($this->config->item('table_login_setup_location_districts') . ' d', 'd.id = outlet_info.district_id', 'INNER');
        $this->db->join($this->config->item('table_login_setup_location_territories') . ' t', 't.id = d.territory_id', 'INNER');
        $this->db->join($this->config->item('table_login_setup_location_zones') . ' zone', 'zone.id = t.zone_id', 'INNER');

        $this->db->select('sale.outlet_id');
        $this->db->select('d.id district_id');
        $this->db->select('t.id territory_id');
        $this->db->select('zone.id zone_id');
        $this->db->select('zone.division_id division_id');

        $this->db->where_in('t.id', $territory_ids);

        $this->db->where('sale.date_sale >=', $date_start);
        $this->db->where('sale.date_sale <=', $date_end);
        $this->db->where('sale.status', $this->config->item('system_status_active'));
        $this->db->group_by('sales_payment_method');
        $this->db->group_by($location_type);
        $results = $this->db->get()->result_array();
        foreach ($results as $result) {
            if (isset($area_initial[$result[$location_type]]['amount_target']) && $area_initial[$result[$location_type]]['amount_target']) {
                if (isset($area_initial[$result[$location_type]][$result['sales_payment_method']])) {
                    $area_initial[$result[$location_type]][$result['sales_payment_method']] += $result['sale_amount'];
                }
                else {
                    $area_initial[$result[$location_type]][$result['sales_payment_method']] = $result['sale_amount'];
                }
                if (isset($area_initial[$result[$location_type]]['amount_sales'])) {
                    $area_initial[$result[$location_type]]['amount_sales'] += $result['sale_amount'];
                }
                else {
                    $area_initial[$result[$location_type]]['amount_sales'] = $result['sale_amount'];
                }
            }
        }

        $grand_total = $this->initialize_row_area_amount('Grand Total');
        $method = 'list';
        $headers = $this->get_preference_headers($method);
        foreach ($area_initial as $info) {
            $amount_target = isset($info['amount_target']) ? $info['amount_target'] : 0;

            if ($amount_target) {
                $info['amount_deference'] = ($info['amount_sales'] - $amount_target);
                $info['amount_average'] = ($info['amount_sales'] / $amount_target) * 100;
                if (isset($info['Cash'])) {
                    $info['amount_sales_cash'] = $info['Cash'];
                    $info['amount_sales_cash_average'] = ($info['amount_sales_cash'] / $amount_target) * 100;
                }
                if (isset($info['Credit'])) {
                    $info['amount_sales_credit'] = $info['Credit'];
                    $info['amount_sales_credit_average'] = ($info['amount_sales_credit'] / $amount_target) * 100;
                }
            }

            foreach ($headers as $key => $r) {
                if (!(($key == 'area') || ($key == 'sl_no'))) {
                    $grand_total[$key] += $info[$key];
                    if ($key == 'amount_sales_cash_average' || $key == 'amount_sales_credit_average' || $key == 'amount_average') {
                        $amount_target_total = isset($grand_total['amount_target']) ? $grand_total['amount_target'] : 0;
                        if ($amount_target_total) {
                            $grand_total['amount_average'] = ($grand_total['amount_sales'] / $amount_target_total) * 100;
                            if (isset($grand_total['amount_sales_cash'])) {
                                $grand_total['amount_sales_cash_average'] = ($grand_total['amount_sales_cash'] / $amount_target_total) * 100;
                            }
                            if (isset($grand_total['amount_sales_credit'])) {
                                $grand_total['amount_sales_credit_average'] = ($grand_total['amount_sales_credit'] / $amount_target_total) * 100;
                            }

                        }
                    }

                }
            }
            $items[] = $info;
        }
        $items[] = $grand_total;
        $this->json_return($items);
    }

    private function get_territory_ids_by_outlet($outlet_id = 0)
    {
        $this->db->from($this->config->item('table_login_setup_location_territories') . ' territory');
        $this->db->select('territory.id value, territory.name text');
        $this->db->join($this->config->item('table_login_setup_location_districts') . ' district', 'district.territory_id = territory.id', 'INNER');
        $this->db->join($this->config->item('table_login_csetup_cus_info') . ' outlet', 'outlet.district_id = district.id AND outlet.revision =1', 'INNER');
        $this->db->where('territory.status', $this->config->item('system_status_active'));
        if (is_array($outlet_id)) {
            $this->db->where_in('outlet.customer_id', $outlet_id);
        }
        elseif ($outlet_id > 0) {
            $this->db->where('outlet.customer_id', $outlet_id);
        }
        $results = $this->db->get()->result_array();
        return $results;
    }

    private function initialize_row_area_amount($area_name)
    {
        $method = 'list';
        $row = $this->get_preference_headers($method);
        foreach ($row as $key => $r) {
            $row[$key] = 0;
        }
        $row['area'] = $area_name;
        $row['sl_no'] = '';
        return $row;
    }
}
