<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Test extends CI_Controller
{
    public function index()
    {
        //echo System_helper::get_time('09-SEP-2017 03:14:49 PM');

        /*$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
        //$foo = $this->cache->get('foo');
        $result[]=array(1,3,4,6,);
        $this->cache->delete('foo');
        if ( ! $foo = $this->cache->get('foo'))
        {
            echo 'Saving to the cache!<br />';
            $foo = $result;

            // Save into the cache for 5 minutes
            $this->cache->save('foo', $foo, 300);
        }

        echo "<pre>";
        print_r($foo);
        echo "</pre>";*/
        $this->load->helper('Expense_helper');

        $a=Expense_helper::get_between_date_by_month(1,2018);
        echo System_helper::display_date($a['date_start']);
        echo '<br />';
        echo System_helper::display_date($a['date_end']);


    }
    public function viewCache()
    {
        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
        $foo = $this->cache->get('foo');
        echo "<pre>";
        print_r($foo);
        echo "</pre>";

    }
}
