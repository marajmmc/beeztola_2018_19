<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Test extends CI_Controller
{
    public function index()
    {
        die;
        $this->load->helper('bulk_sms');
        $this->lang->load('bulk_sms');
        echo '<pre>';
        print_r(Bulk_sms_helper::send_sms('01713090962',sprintf($this->lang->line('SMS_SALES_INVOICE'), 'I-123','12,345.00')));
        echo '</pre>';

    }
}
