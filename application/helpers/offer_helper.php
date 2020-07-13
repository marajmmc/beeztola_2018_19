<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Offer_helper
{
    public static function get_offer_stats($dealer_ids)
    {
        $CI =& get_instance();
        $offers=array();
        foreach($dealer_ids as $dealer_id)
        {
            $offer=array();
            $offer['offer_offered']=0;
            $offer['offer_given']=0;
            $offer['offer_adjusted']=0;
            $offer['offer_balance']=0;
            $offers[$dealer_id]=$offer;
        }
        //offer offered and given from sales
        $CI->db->from($CI->config->item('table_pos_sale').' sale');
        $CI->db->select('sale.farmer_id');
        $CI->db->select('SUM(sale.offer_offered) offer_offered');
        $CI->db->select('SUM(sale.offer_given) offer_given');
        $CI->db->where('sale.status',$CI->config->item('system_status_active'));
        $CI->db->where_in('sale.farmer_id',$dealer_ids);
        $CI->db->group_by('sale.farmer_id');
        $results=$CI->db->get()->result_array();
        foreach($results as $result)
        {
            $offers[$result['farmer_id']]['offer_offered']+=$result['offer_offered'];
            $offers[$result['farmer_id']]['offer_given']+=$result['offer_given'];
            $offers[$result['farmer_id']]['offer_balance']+=($result['offer_offered']-$result['offer_given']);
        }

        //offer adjusted
        $CI->db->from($CI->config->item('table_login_offer_adjust_farmer').' offer_adjust');
        $CI->db->select('offer_adjust.farmer_id');
        $CI->db->select('SUM(offer_adjust.amount) offer_adjusted');
        $CI->db->where('offer_adjust.status !=',$CI->config->item('system_status_delete'));
        $CI->db->where_in('offer_adjust.farmer_id',$dealer_ids);
        $CI->db->group_by('offer_adjust.farmer_id');
        $results=$CI->db->get()->result_array();
        foreach($results as $result)
        {

            $offers[$result['farmer_id']]['offer_adjusted']+=$result['offer_adjusted'];
            $offers[$result['farmer_id']]['offer_balance']-=($result['offer_adjusted']);
        }

        return $offers;
    }

}
