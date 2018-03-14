<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$CI=& get_instance();
$action_buttons=array();
$action_buttons[]=array
(
    'label'=>$CI->lang->line("ACTION_BACK"),
    'href'=>site_url($CI->controller_url)
);
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
            <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DATE_RECEIVE');?>:</label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <?php echo System_helper::display_date($item['date_receive']);?>
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
            <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_BANK_NAME');?>:</label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <?php echo $item['bank_name_source'];?>
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
            <label class="control-label pull-right">Payment Entry Time:</span></label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <?php echo System_helper::display_date_time($item['date_updated']);?>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right">Payment Entry By:</span></label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <?php echo $item['payment_by'];?>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right">Payment Forward Entry Time:</span></label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <?php echo System_helper::display_date_time($item['date_updated_forward']);?>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right">Payment Forwarded By:</span></label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <?php echo $item['payment_forwarded_by'];?>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_AMOUNT_PAYMENT');?>:</label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <?php echo number_format($item['amount_payment'],2);?>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_AMOUNT_RECEIVE');?>:</label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <?php echo number_format($item['amount_receive'],2);?>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_AMOUNT_BANK_CHARGE');?>:</label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <?php echo number_format($item['amount_bank_charge'],2);?>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_AMOUNT_ACTUAL');?>:</label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <?php echo number_format($item['amount_actual'],2);?>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right">Payment Receive Bank:</label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <?php if($item['bank_account_id_destination']){echo $item['account_number'].'('.$item['bank_destination'].' -'.$item['branch_name'].')';}?>
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
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_REMARKS');?></label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <?php echo nl2br($item['remarks_receive']);?>
        </div>
    </div>

</div>
<div class="clearfix"></div>

<script type="text/javascript">
    jQuery(document).ready(function()
    {
        system_preset({controller:'<?php echo $CI->router->class; ?>'});
        $(".datepicker").datepicker({dateFormat : display_date_format});
    });
</script>
