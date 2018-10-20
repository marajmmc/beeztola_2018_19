<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Test extends CI_Controller
{
    public function index()
    {
        $this->load->helper('mobile_sms');
        $this->lang->load('mobile_sms');
        echo '<pre>';
        print_r(Mobile_sms_helper::send_sms(Mobile_sms_helper::$API_SENDER_ID_MALIK_SEEDS,'01713090962',sprintf($this->lang->line('SMS_SALES_INVOICE'),'2,050,000','I-0039107')));
        echo '</pre>';

    }
}
