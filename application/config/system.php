<?php
$config['offline_controllers']=array('home','sys_site_offline');
$config['external_controllers']=array('home');//user can use them without login
$config['system_max_actions']=8;

$config['system_status_yes']='Yes';
$config['system_status_no']='No';
$config['system_status_active']='Active';
$config['system_status_inactive']='In-Active';
$config['system_status_delete']='Deleted';
$config['system_status_pending']='Pending';
$config['system_status_complete']='Complete';

$config['USER_TYPE_EMPLOYEE']=1;

$config['system_status_not_done']='Not Done';
$config['system_status_done']='Done';
$config['system_base_url_profile_picture']='http://localhost/beeztola_2018_19/';

/*$config['system_base_url_profile_picture']='http://50.116.76.180/login/';
//$config['system_base_url_profile_picture']='http://127.0.0.1/login_2018_19/';
$config['system_base_url_customer_profile_picture']='http://180.234.223.205/login_2018_19/';
$config['system_base_url_customer_document']='http://180.234.223.205/login_2018_19/';*/

// Outlet Type Config
$config['system_customer_type_outlet_id']=1;
$config['system_customer_type_customer_id']=2;

// Payment Type Config (added by saiful)

$config['system_payment_way_cash']='Cash';
$config['system_payment_way_pay_order']='Pay Order';
$config['system_payment_way_cheque']='Cheque';
$config['system_payment_way_tt']='TT';
$config['system_payment_way_dd']='DD';
$config['system_payment_way_online_payment']='Online Payment';
