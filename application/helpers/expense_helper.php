<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Expense_helper
{
    public static function get_between_date_by_month($month='', $year='')
    {
        if($month && $year)
        {
            $date_start=date("21-m-Y", mktime(0, 0, 0,  $month,-1, $year));
            $date_end=date("20-m-Y", mktime(0, 0, 0,  $month,1, $year));
        }
        else
        {
            /*$date_start=date("21-m-Y", mktime(0, 0, 0,  date('m', time()),-1, date('Y', time())));
            $date_end=date("20-m-Y", mktime(0, 0, 0,  date('m', time()),1, date('Y', time())));*/
            $date_start=0;
            $date_end=0;
        }
        $return_date['date_start']=$date_start;
        $return_date['date_end']=$date_end;
        return $return_date;
    }
}
