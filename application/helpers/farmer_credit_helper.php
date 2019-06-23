<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Farmer_Credit_helper
{
    //$data_history['farmer_id'];----required
    //$data_history['sale_id'];----default:0
    //$data_history['payment_id'];----default:0
    //$data_history['credit_limit_old'];----required
    //$data_history['credit_limit_new'];----required
    //$data_history['balance_old'];----required
    //$data_history['balance_new'];----required
    //$data_history['amount_adjust'];----required
    //$data_history['remarks_reason'];----required-for system message
    //$data_history['reference_no'];----default:empty
    //$data_history['remarks'];----default:empty-from user
    public static function add_credit_history($data_history)
    {
        $CI =& get_instance();
        $user = User_helper::get_user();
        $time=time();
        $data_history['date_created']=$time;
        $data_history['user_created']=$user->user_id;
        $CI->db->insert($CI->config->item('table_pos_farmer_credit_balance_history'), $data_history);
    }
}
