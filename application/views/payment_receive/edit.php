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
<form id="save_form" action="<?php echo site_url($CI->controller_url.'/index/save');?>" method="post">
    <input type="hidden" id="id" name="id" value="<?php echo $item['id']?>" />
    <input type="hidden" id="system_save_new_status" name="system_save_new_status" value="0" />
    <div class="row widget">
        <div class="widget-header">
            <div class="title">
                <?php echo $title; ?>
            </div>
            <div class="clearfix"></div>
        </div>
        <div class="col-md-12">
            <table class="table table-bordered table-responsive system_table_details_view">
                <tbody>
                <tr>
                    <td class="widget-header header_caption"><label class="control-label pull-right">Payment Entry By</label></td>
                    <td class="header_value"><label class="control-label"><?php echo $users[$item['user_deposit_updated']]['name'];?></label></td>
                    <td class="widget-header header_caption"><label class="control-label pull-right">Payment Entry Time</label></td>
                    <td class="header_value"><label class="control-label"><?php echo System_helper::display_date_time($item['date_deposit_updated']);?></label></td>
                </tr>
                <tr>
                    <td class="widget-header header_caption"><label class="control-label pull-right">Payment Forwarded By</label></td>
                    <td class="header_value"><label class="control-label"><?php echo $users[$item['user_deposit_forwarded']]['name'];?></label></td>
                    <td class="widget-header header_caption"><label class="control-label pull-right">Payment Forwarded Time</label></td>
                    <td class="header_value"><label class="control-label"><?php echo System_helper::display_date_time($item['date_deposit_forwarded']);?></label></td>
                </tr>
                <tr>
                    <td class="widget-header header_caption"><label class="control-label pull-right">Attachment(Document)</label></td>
                    <td colspan="3" class=" header_value"><img style="max-width: 250px;" src="<?php echo $CI->config->item('system_base_url_payment_attachment').$item['image_location']; ?>" alt="<?php echo $item['image_name']; ?>"></td>
                </tr>
                <tr>
                    <td class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_REMARKS');?></label></td>
                    <td colspan="3" class=" header_value"><label class="control-label"><?php echo nl2br($item['remarks_deposit']);?></label></td>
                </tr>
                </tbody>
            </table>
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
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DATE_RECEIVE');?><span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <input type="text" name="item[date_receive]" class="form-control datepicker" value="" readonly/>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_OUTLET_NAME');?>:</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <?php echo $item['outlet_name'];?>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_PAYMENT_WAY');?>:</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <?php echo $item['payment_way'];?>
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
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_BANK_PAYMENT_SOURCE');?>:</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <?php echo $item['bank_payment_source'];?>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_BANK_BRANCH_SOURCE');?>:</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <?php echo $item['bank_branch_source'];?>
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
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_AMOUNT_BANK_CHARGE');?><span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <input type="text" id="amount_bank_charge" name="item[amount_bank_charge]" class="form-control text-right float_type_positive" value="<?php echo $item['amount_bank_charge'];?>"/>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_AMOUNT_RECEIVE');?>:</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label id="amount_receive"><?php echo number_format(($item['amount_payment']-$item['amount_bank_charge']),2);?></label>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_BANK_ACCOUNT_NUMBER_DESTINATION');?>:</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <?php echo $item['account_number'].' ('.$item['bank_destination'].' -'.$item['branch_name'].')';?>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Receive/Back<span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <select class="form-control" name="item[status_payment_receive]">
                    <option value=""><?php echo $CI->lang->line('SELECT');?></option>
                    <option value="<?php echo $this->config->item('system_status_received')?>">Receive</option>
                    <option value="<?php echo $this->config->item('system_status_rejected')?>">Back To deposit</option>
                </select>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_REMARKS');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <textarea name="item[remarks_receive]" class="form-control"><?php echo $item['remarks_receive'] ?></textarea>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">

            </div>
            <div class="col-sm-4 col-xs-4">
                <div class="action_button">
                    <button id="button_action_save" type="button" class="btn" data-form="#save_form" data-message-confirm="Are you sure to Receive?">Receive/Back</button>
                </div>
            </div>
            <div class="col-sm-4 col-xs-4">

            </div>
        </div>
    </div>
    <div class="clearfix"></div>
</form>

<script type="text/javascript">
    jQuery(document).ready(function()
    {
        system_preset({controller:'<?php echo $CI->router->class; ?>'});
        $(".datepicker").datepicker({dateFormat : display_date_format});
        $(document).off('input','#amount_bank_charge');
        $(document).on('input', '#amount_bank_charge', function()
        {
            var amount_payment=<?php echo $item['amount_payment'];?>;
            var amount_bank_charge=$('#amount_bank_charge').val();
            var amount_receive=number_format((amount_payment-amount_bank_charge),2);
            $('#amount_receive').html(amount_receive);
        });
    });
</script>
