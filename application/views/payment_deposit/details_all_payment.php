<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$CI=& get_instance();
$action_buttons=array();
$action_buttons[]=array
(
    'label'=>$CI->lang->line("ACTION_BACK"),
    'href'=>site_url($CI->controller_url.'/index/list_all')
);
if(isset($CI->permissions['action4']) && ($CI->permissions['action4']==1))
{
    $action_buttons[]=array(
        'type'=>'button',
        'label'=>$CI->lang->line("ACTION_PRINT"),
        'onClick'=>"window.print()"
    );
}
$CI->load->view('action_buttons',array('action_buttons'=>$action_buttons));
?>
<div class="row widget">
    <div class="widget-header">
        <div class="title">
            <?php echo $title; ?>
        </div>
        <div class="clearfix"></div>
    </div>

    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DATE_PAYMENT');?>:</label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <?php echo System_helper::display_date($item['date_payment']);?>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DATE_SALE');?>:</label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <?php echo System_helper::display_date($item['date_sale']);?>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_OUTLET');?>:</label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <?php echo $item['outlet'];?>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_TYPE_PAYMENT');?>:</label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <?php echo $item['type_payment'];?>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_REFERENCE_NO');?>:</label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <?php echo $item['reference_no'];?>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_AMOUNT_PAYMENT');?>:</label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <?php echo $item['amount_payment'];?>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_BANK_NAME');?>:</label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <?php echo $item['bank_name'];?>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_BRANCH_NAME');?>:</label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <?php echo $item['bank_branch_source'];?>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_REMARKS');?>:</label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <?php echo nl2br($item['remarks_payment']);?>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right">Attachment:</label>
        </div>
        <div class="col-xs-4" id="image_payment">
            <img style="max-width: 250px;" src="<?php echo $CI->config->item('system_base_url_profile_picture').$item['image_location']; ?>" alt="<?php echo $item['image_name']; ?>">
        </div>
    </div>
</div>
<div class="clearfix"></div>
