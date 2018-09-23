<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Budget_helper
{
    public static $BUDGET_ID_FISCAL_YEAR_START=5;
    public static $NUM_FISCAL_YEAR_PREVIOUS_SALE=3;
    public static function get_fiscal_years($ordering='DESC')
    {
        $time=time()+3600*24*365;
        $CI =& get_instance();
        $CI->db->from($CI->config->item('table_login_basic_setup_fiscal_year').' fy');
        $CI->db->select('fy.id,fy.name,fy.date_start,fy.date_end');
        $CI->db->where('fy.id >=',Budget_helper::$BUDGET_ID_FISCAL_YEAR_START);
        $CI->db->where('fy.date_start <',$time);
        $CI->db->order_by('fy.id',$ordering);
        $results=$CI->db->get()->result_array();
        $fiscal_years=array();
        foreach($results as $result)
        {
            $data=array();
            $data['id']=$result['id'];
            $data['text']=$result['name'];
            $data['date_start']=$result['date_start'];
            $data['date_end']=$result['date_end'];
            $data['value']=System_helper::display_date($result['date_start']).'/'.System_helper::display_date($result['date_end']);
            $fiscal_years[$result['id']]=$data;
        }
        return $fiscal_years;
    }
    public static function check_validation_fiscal_year($fiscal_year_id)
    {
        if($fiscal_year_id<Budget_helper::$BUDGET_ID_FISCAL_YEAR_START)
        {
            return false;
        }
        $fiscal_years=Budget_helper::get_fiscal_years();
        if($fiscal_year_id>=(sizeof($fiscal_years)+Budget_helper::$BUDGET_ID_FISCAL_YEAR_START))
        {
            return false;
        }
        return true;
    }
}
