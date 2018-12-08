<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Budget_helper
{
    public static $BUDGET_ID_FISCAL_YEAR_START=4;
    public static $NUM_FISCAL_YEAR_PREVIOUS_SALE=3;
    public static $NUM_FISCAL_YEAR_NEXT_BUDGET_TARGET=3;
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
    public static function get_crop_type_varieties($crop_ids=array(), $crop_type_ids=array(), $variety_ids=array())
    {
        $CI =& get_instance();
        $CI->db->from($CI->config->item('table_login_setup_classification_varieties').' v');
        $CI->db->select('v.id variety_id,v.name variety_name');
        $CI->db->join($CI->config->item('table_login_setup_classification_crop_types').' crop_type','crop_type.id = v.crop_type_id','INNER');
        $CI->db->select('crop_type.id crop_type_id, crop_type.name crop_type_name');
        $CI->db->join($CI->config->item('table_login_setup_classification_crops').' crop','crop.id = crop_type.crop_id','INNER');
        $CI->db->select('crop.id crop_id,crop.name crop_name');
        if($crop_ids)
        {
            $CI->db->where_in('crop.id',$crop_ids);
        }
        if($crop_type_ids)
        {
            $CI->db->where_in('crop_type.id',$crop_type_ids);
        }
        if($variety_ids)
        {
            $CI->db->where_in('v.id',$variety_ids);
        }
        $CI->db->where('v.status',$CI->config->item('system_status_active'));
        $CI->db->where('v.whose','ARM');
        $CI->db->order_by('crop.ordering','ASC');
        $CI->db->order_by('crop.id','ASC');
        $CI->db->order_by('crop_type.ordering','ASC');
        $CI->db->order_by('crop_type.id','ASC');
        $CI->db->order_by('v.ordering','ASC');
        $CI->db->order_by('v.id','ASC');
        return $CI->db->get()->result_array();
    }
}
