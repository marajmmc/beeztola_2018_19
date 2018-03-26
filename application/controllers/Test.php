<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Test extends CI_Controller
{
    public function index()
    {
        echo System_helper::display_date_time(System_helper::get_time('26-Mar-2018 1:9:00'));

        //$a=Query_helper::get_info($this->config->item('table_pos_setup_farmer_type_outlet_discount'),array('*'),array('outlet_id =-1'),1);

        /*$this->db->where('id',0);
        $a=$this->db->get('pos_setup_farmer_type_outlet_discount')->row();

        //print_r($a);
        //exit;
        var_dump($a);
        if(($a))
        {
            echo 'yes';
        }
        else
        {
            echo 'no';
        }*/

    }

}
