<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Stock_helper
{
    public static function get_variety_stock($outlet_id, $variety_ids=array())
    {
        $CI =& get_instance();
        $CI->db->from($CI->config->item('table_pos_stock_summary_variety').' pos_stock_summary_variety');
        $CI->db->where('pos_stock_summary_variety.outlet_id',$outlet_id);
        if(sizeof($variety_ids)>0)
        {
            $CI->db->where_in('variety_id',$variety_ids);
        }
        $results=$CI->db->get()->result_array();
        $stocks=array();
        foreach($results as $result)
        {
            $stocks[$result['variety_id']][$result['pack_size_id']]=$result;
        }
        return $stocks;
    }
}
