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
            $result=Query_helper::get_info($CI->config->item('table_pos_setup_farmer_farmer'),array('id'),array('id ='.intval(substr($code,2))),1);
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
            $result=Query_helper::get_info($CI->config->item('table_pos_setup_farmer_farmer'),array('id'),array('mobile_no ="'.($code).'"'),1);
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
    public static function get_id_payment($barcode)
    {
        $CI =& get_instance();
        if((substr($barcode,0,2)=='P-'))
        {
            $result=Query_helper::get_info($CI->config->item('table_pos_payment'),array('id'),array('id ='.intval(substr($barcode,2))),1);
            if($result)
            {
                return $result['id'];
            }
            else
            {
                return 0;
            }
        }
        else
        {
            return 0;
        }
    }
    /*public static function get_barcode_variety($crop_id,$variety_id,$pack_id)
    {
        return str_pad($crop_id,2,0,STR_PAD_LEFT).str_pad($variety_id,4,0,STR_PAD_LEFT).str_pad($pack_id,2,0,STR_PAD_LEFT);
    }*/
    public static function get_barcode_variety($outlet_id,$variety_id,$pack_id)
    {
        return str_pad($outlet_id,3,0,STR_PAD_LEFT).str_pad($variety_id,3,0,STR_PAD_LEFT).str_pad($pack_id,2,0,STR_PAD_LEFT);
    }
    public static function get_barcode_sales($id)
    {
        return 'I-'.str_pad($id,7,0,STR_PAD_LEFT);
    }
    public static function get_id_sales($barcode)
    {
        $CI =& get_instance();
        if((substr($barcode,0,2)=='I-'))
        {
            $result=Query_helper::get_info($CI->config->item('table_pos_sale'),'*',array('id ='.intval(substr($barcode,2))),1);
            if($result)
            {
                return $result['id'];
            }
            else
            {
                return 0;
            }
        }
        else
        {
            return 0;
        }

    }

    /// SMS relational function
    /* HQ to Outlet Transfer */
    public static function get_barcode_transfer_warehouse_to_outlet($increment_id)
    {
        return 'TO'.str_pad($increment_id,6,0,STR_PAD_LEFT);
    }
    /* Outlet to HQ Transfer */
    public static function get_barcode_transfer_outlet_to_warehouse($increment_id)
    {
        return 'TR'.str_pad($increment_id,6,0,STR_PAD_LEFT);
    }
    /* Outlet to Outlet Transfer */
    public static function get_barcode_transfer_outlet_to_outlet($increment_id)
    {
        return 'TS'.str_pad($increment_id,6,0,STR_PAD_LEFT);
    }
    /* Dealer Payemnt */
    public static function get_barcode_dealer_payment($increment_id)
    {
        return 'DP'.str_pad($increment_id,6,0,STR_PAD_LEFT);
    }
}
