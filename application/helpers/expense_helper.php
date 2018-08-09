<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Expense_helper
{
    public static function get_between_date_by_month($month, $year)
    {
        $return_date=array();
        $return_date['date_start']=0;
        $return_date['date_end']=0;
        if($month && $year)
        {
            $date_start=date("21-m-Y", mktime(0, 0, 0,  $month,-1, $year));
            $return_date['date_start']=System_helper::get_time($date_start);
            $date_end=date("20-m-Y", mktime(0, 0, 0,  $month,1, $year));
            $return_date['date_end']=System_helper::get_time($date_end);
        }
        return $return_date;
    }
}
