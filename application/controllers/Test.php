<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Test extends CI_Controller
{
    public function index()
    {
        $a['1']='10';
        $a['2']='20';
        $b=$a;
        unset($b[1]);
       echo '<pre>';
       print_r($a);
       print_r($b);
       echo '</pre>';

    }

}
