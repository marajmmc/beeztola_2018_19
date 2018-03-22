<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Barcode_helper
{
    public static function get_barcode_farmer($id)
    {
        return 'F-'.str_pad($id,6,0,STR_PAD_LEFT);
    }
    public static function get_id_farmer($code)
    {
        $CI =& get_instance();
        //if((substr($code,0,2)=='F-'))
        if(Barcode_helper::get_farmer_code_type($code)=='barcode')
        {
            $result=Query_helper::get_info($CI->config->item('table_pos_setup_farmer_farmer'),'*',array('id ='.intval(substr($code,2))),1);
            if($result)
            {
                return $result['id'];
            }
            else
            {
                return 0;
            }
        }
        else if(Barcode_helper::get_farmer_code_type($code)=='invoice')
        {
            $CI->db->from($CI->config->item('table_pos_sale').' sale');
            $CI->db->select('sale.farmer_id');
            $CI->db->where('sale.id',intval(substr($code,2)));
            $result=$CI->db->get()->row_array();
            if($result)
            {
                return $result['farmer_id'];
            }
            else
            {
                return 0;
            }
        }
        else
        {
            $result=Query_helper::get_info($CI->config->item('table_pos_setup_farmer_farmer'),'*',array('mobile_no ="'.($code).'"'),1);
            if($result)
            {
                return $result['id'];
            }
            else
            {
                return 0;
            }
        }

    }
    public static function get_farmer_code_type($code)
    {
        if((substr($code,0,2)=='F-'))
        {
            return 'barcode';
        }
        else if((substr($code,0,2)=='I-'))
        {
            return 'invoice';
        }
        else
        {
            return 'mobile';
        }
    }
    public static function get_barcode_payment($id)
    {
        return 'P-'.str_pad($id,6,0,STR_PAD_LEFT);
    }
    public static function get_barcode_variety($crop_id,$variety_id,$pack_id)
    {
        return str_pad($crop_id,2,0,STR_PAD_LEFT).str_pad($variety_id,4,0,STR_PAD_LEFT).str_pad($pack_id,2,0,STR_PAD_LEFT);
    }
    public static function get_barcode_sales($id)
    {
        return 'I-'.str_pad($id,7,0,STR_PAD_LEFT);
    }

    /// SMS relational function
    public static function get_barcode_transfer_warehouse_to_outlet($increment_id)
    {
        return 'TS'.str_pad($increment_id,6,0,STR_PAD_LEFT);
    }
}
