<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$CI=& get_instance();


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
            <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_AMOUNT_PAYABLE');?></label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo System_helper::get_string_amount($item['amount_payable']);?></label>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_AMOUNT_SALE_CREDIT');?></label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo System_helper::get_string_amount($item['amount_sale_credit']);?></label>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_AMOUNT_SALE_CASH');?></label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo System_helper::get_string_amount($item['amount_sale_cash']);?></label>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_AMOUNT_CASH_PAYMENT');?></label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo System_helper::get_string_amount($item['amount_cash_payment']);?></label>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_AMOUNT_CASH_TOTAL');?></label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo System_helper::get_string_amount($item['amount_cash_total']);?></label>
        </div>
    </div>

</div>
<div class="clearfix"></div>

