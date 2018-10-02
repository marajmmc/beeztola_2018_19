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
            <?php echo 'Details ::'.Barcode_helper::get_barcode_payment($item['id']); ?>
        </div>
        <div class="clearfix"></div>
    </div>
    <div class="col-md-12">
        <table class="table table-bordered table-responsive system_table_details_view">
            <tbody>
            <tr>
                <td class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_ID');?></label></td>
                <td class="header_value"><label class="control-label"><?php echo Barcode_helper::get_barcode_payment($item['id']);?></label></td>
                <td class="widget-header header_caption"><label class="control-label pull-right">Payment Entry By</label></td>
                <td class="header_value"><label class="control-label"><?php echo $users[$item['user_deposit_updated']]['name'];?></label></td>
            </tr>
            <tr>
                <td class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DATE_PAYMENT');?></label></td>
                <td class="header_value"><label class="control-label"><?php echo System_helper::display_date($item['date_payment']);?></label></td>
                <td class="widget-header header_caption"><label class="control-label pull-right">Payment Entry Time</label></td>
                <td class="header_value"><label class="control-label"><?php echo System_helper::display_date_time($item['date_deposit_updated']);?></label></td>
            </tr>
            <tr>
                <td class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DATE_SALE');?></label></td>
                <td class="header_value"><label class="control-label"><?php echo System_helper::display_date($item['date_sale']);?></label></td>
                <td colspan="2">&nbsp;</td>
            </tr>
            <tr>
                <td class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_OUTLET');?></label></td>
                <td class="header_value"><label class="control-label"><?php echo $item['outlet_name'];?></label></td>
                <td colspan="2">&nbsp;</td>
            </tr>
            <tr>
                <td class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_PAYMENT_WAY');?></label></td>
                <td class="header_value"><label class="control-label"><?php echo $item['payment_way'];?></label></td>
                <td colspan="2">&nbsp;</td>
            </tr>
            <tr>
                <td class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_REFERENCE_NO');?></label></td>
                <td class="header_value"><label class="control-label"><?php echo $item['reference_no'];?></label></td>
                <td colspan="2">&nbsp;</td>
            </tr>
            <tr>
                <td class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_AMOUNT_PAYMENT');?></label></td>
                <td class="header_value"><label class="control-label"><?php echo number_format($item['amount_payment'],2);?></label></td>
                <td colspan="2">&nbsp;</td>
            </tr>
            <tr>
                <td class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_BANK_PAYMENT_SOURCE');?></label></td>
                <td class=" header_value"><label class="control-label"><?php echo $item['bank_payment_source'];?></label></td>
                <td colspan="2">&nbsp;</td>
            </tr>
            <tr>
                <td class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_BANK_BRANCH_SOURCE');?></label></td>
                <td class=" header_value"><label class="control-label"><?php echo $item['bank_branch_source'];?></label></td>
                <td colspan="2">&nbsp;</td>
            </tr>
            <tr>
                <td class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_BANK_ACCOUNT_NUMBER_DESTINATION');?></label></td>
                <td class="header_value"><label class="control-label"><?php echo $item['account_number'].' ('.$item['bank_destination'].' -'.$item['branch_name'].')';?></label></td>
                <td colspan="2">&nbsp;</td>
            </tr>
            <tr>
                <td class="widget-header header_caption"><label class="control-label pull-right">Attachment(Document)</label></td>
                <td colspan="3" class=" header_value"><img style="max-width: 250px;" src="<?php echo $CI->config->item('system_base_url_picture').$item['image_location']; ?>" alt="<?php echo $item['image_name']; ?>"></td>
            </tr>
            <tr>
                <td class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_REMARKS');?></label></td>
                <td colspan="3" class=" header_value"><label class="control-label"><?php echo nl2br($item['remarks_deposit']);?></label></td>
            </tr>
            </tbody>
        </table>
    </div>
</div>

<form id="save_form" action="<?php echo site_url($CI->controller_url.'/index/save_forward');?>" method="post">
    <input type="hidden" id="id" name="id" value="<?php echo $item['id']; ?>" />
    <div class="row widget">
        <div class="widget-header">
            <div class="title">
                Forward Payment
            </div>
            <div class="clearfix"></div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Payment Forward<span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <select class="form-control" name="item[status_deposit_forward]">
                    <option value=""><?php echo $CI->lang->line('SELECT');?></option>
                    <option value="<?php echo $this->config->item('system_status_forwarded')?>">Forward</option>
                </select>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">

            </div>
            <div class="col-sm-4 col-xs-4">
                <div class="action_button">
                    <button id="button_action_save" type="button" class="btn" data-form="#save_form" data-message-confirm="Are you sure to Forward?">Forward</button>
                </div>
            </div>
            <div class="col-sm-4 col-xs-4">

            </div>
        </div>
    </div>
</form>