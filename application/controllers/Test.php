<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Test extends CI_Controller
{
    public function index()
    {
        $time=time();
        $time1=System_helper::get_time('06-Jul-2019'.substr(System_helper::display_date_time($time),11));
        echo $time1;

        echo System_helper::display_date_time($time1).'-'.System_helper::display_date_time($time).'<br>';
        //$time2=($time1%(60*60*24));
        //echo $time2.'-'. System_helper::display_date_time($time1+$time2);
    }
}
