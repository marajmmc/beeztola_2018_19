<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Test extends CI_Controller
{
    public function index()
    {
        echo System_helper::get_time('09-SEP-2017 03:14:49 PM');

    }
}
