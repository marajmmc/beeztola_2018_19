<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Offer_helper
{
    public static function get_offer_stats($dealer_ids)
    {
        $offers=array();
        foreach($dealer_ids as $dealer_id)
        {
            $offer=array();
            $offer['offer_earned']=0;
            $offer['offer_given']=0;
            $offer['offer_adjusted']=0;
            $offer['offer_balance']=$dealer_id;
            $offers[$dealer_id]=$offer;
        }
        return $offers;
    }

}
