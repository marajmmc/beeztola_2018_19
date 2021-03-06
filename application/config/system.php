<?php
$config['offline_controllers']=array('home','sys_site_offline');
$config['external_controllers']=array('home');//user can use them without login
$config['system_max_actions']=8;

$config['system_site_root_folder']='beeztola_2018_19';
$config['system_upload_image_auth_key']='ems_2018_19';
$config['system_upload_api_url']='http://45.251.59.5/api_file_server/upload';

$config['system_status_yes']='Yes';
$config['system_status_no']='No';
$config['system_status_active']='Active';
$config['system_status_inactive']='In-Active';
$config['system_status_delete']='Deleted';
$config['system_status_closed']='Closed';
$config['system_status_pending']='Pending';
$config['system_status_forwarded']='Forwarded';
$config['system_status_complete']='Complete';
$config['system_status_approved']='Approved';
$config['system_status_delivered']='Delivered';
$config['system_status_received']='Received';
$config['system_status_rejected']='Rejected';
$config['system_status_rollback']='Rollback';

$config['USER_TYPE_EMPLOYEE']=1;

$config['system_status_not_done']='Not Done';
$config['system_status_done']='Done';
$config['system_base_url_picture']='http://45.251.59.5/beeztola_2018_19/';

// Outlet Type Config
$config['system_customer_type_outlet_id']=1;
$config['system_customer_type_customer_id']=2;
/*Bank & Account Config*/
// purpose
$config['system_bank_account_purpose_sale_receive']='sale_receive';

//System Configuration

$config['system_purpose_pos_max_wrong_password']='pos_max_wrong_password';//maximum password wrong allow
$config['system_purpose_pos_status_mobile_verification']='pos_status_mobile_verification';//on off mobile verification
$config['system_purpose_pos_menu_odd_color']='pos_menu_odd_color';
$config['system_purpose_pos_menu_even_color']='pos_menu_even_color';
$config['system_purpose_status_sms_sales_invoice']='pos_status_sms_sales_invoice';//for sms after new invoice to dealer

//System File Type
$config['system_file_type_image']='Image';
$config['system_file_type_video']='Video';
$config['system_file_type_video_ext']='wmv|mp4|mov|ftv|mkv|3gp|avi';
$config['system_file_type_video_max_size']=512000;//100mb