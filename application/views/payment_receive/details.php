<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$CI = & get_instance();
$action_buttons=array();
$action_buttons[]=array(
    'label'=>$CI->lang->line("ACTION_BACK").' to Pending List',
    'href'=>site_url($CI->controller_url)
);
$action_buttons[]=array(
    'label'=>$CI->lang->line("ACTION_BACK").' to All list',
    'href'=>site_url($CI->controller_url.'/index/list_all')
);
$CI->load->view('action_buttons',array('action_buttons'=>$action_buttons));
?>
<div class="row widget">

    <div class="widget-header">
        <div class="title">
            <?php echo 'Payment Details of:'.Barcode_helper::get_barcode_payment($item['id']); ?>
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
                <td class="widget-header header_caption"><label class="control-label pull-right">Deposit Remarks</label></td>
                <td colspan="3" class=" header_value"><label class="control-label"><?php echo nl2br($item['remarks_deposit']);?></label></td>
            </tr>
            <?php
            if($item['status_deposit_forward']!=$CI->config->item('system_status_pending'))
            {
                ?>
                <tr>
                    <td class="widget-header header_caption"><label class="control-label pull-right">Payment Forwarded By</label></td>
                    <td class="header_value"><label class="control-label"><?php echo $users[$item['user_deposit_forwarded']]['name'];?></label></td>
                    <td class="widget-header header_caption"><label class="control-label pull-right">Payment Forwarded Time</label></td>
                    <td class="header_value"><label class="control-label"><?php echo System_helper::display_date_time($item['date_deposit_forwarded']);?></label></td>
                </tr>
            <?php
            }
            else
            {
                ?>
                <tr>
                    <td class="widget-header header_caption"><label class="control-label pull-right">Forward Status</label></td>
                    <td class="header_value"><label class="control-label"><?php echo $item['status_deposit_forward'];?></label></td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            <?php
            }
            ?>
            <?php
            if($item['status_payment_receive']!=$CI->config->item('system_status_pending'))
            {
                ?>
                <tr>
                    <td class="widget-header header_caption"><label class="control-label pull-right">Payment Received By</label></td>
                    <td class="header_value"><label class="control-label"><?php echo $users[$item['user_payment_received']]['name'];?></label></td>
                    <td class="widget-header header_caption"><label class="control-label pull-right">Payment Received Time</label></td>
                    <td class="header_value"><label class="control-label"><?php echo System_helper::display_date_time($item['date_payment_received']);?></label></td>
                </tr>
                <tr>
                    <td class="widget-header header_caption"><label class="control-label pull-right">Received Remarks</label></td>
                    <td colspan="3" class=" header_value"><label class="control-label"><?php echo nl2br($item['remarks_receive']);?></label></td>
                </tr>
            <?php
            }
            else
            {
                ?>
                <tr>
                    <td class="widget-header header_caption"><label class="control-label pull-right">Receive Status</label></td>
                    <td class="header_value"><label class="control-label"><?php echo $item['status_payment_receive'];?></label></td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            <?php
            }
            ?>
            <?php
            if($item['revision_count_edit_approve']>0)
            {
                ?>
                <tr>
                    <td class="widget-header header_caption"><label class="control-label pull-right">Manual Edit Approved By</label></td>
                    <td class="header_value"><label class="control-label"><?php echo $users[$item['user_manual_edit_approved']]['name'];?></label></td>
                    <td class="widget-header header_caption"><label class="control-label pull-right">Manual Edit Approved Time</label></td>
                    <td class="header_value"><label class="control-label"><?php echo System_helper::display_date_time($item['date_manual_edit_approved']);?></label></td>
                </tr>
                <tr>
                    <td class="widget-header header_caption"><label class="control-label pull-right">Last Manual Edit Request ID</label></td>
                    <td class="header_value"><label class="control-label"><?php echo $item['id_last_edit_approve'];?></label></td>
                    <td class="widget-header header_caption"><label class="control-label pull-right">Manually Edited</label></td>
                    <td class="header_value"><label class="control-label"><?php echo ($item['revision_count_edit_approve']).' Times';?></label></td>
                </tr>
                <tr>
                    <td class="widget-header header_caption"><label class="control-label pull-right">Manual Edit Approved Remarks</label></td>
                    <td colspan="3" class=" header_value"><label class="control-label"><?php echo nl2br($item['remarks_manual_edit_approved']);?></label></td>
                </tr>
            <?php
            }
            ?>
            <tr>
                <td class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_ID');?></label></td>
                <td class="header_value"><label class="control-label"><?php echo Barcode_helper::get_barcode_payment($item['id']);?></label></td>

            </tr>
            <tr>
                <td class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DATE_PAYMENT');?></label></td>
                <td class="header_value"><label class="control-label"><?php echo System_helper::display_date($item['date_payment']);?></label></td>

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
                <td class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_AMOUNT_CASH_SALE_PAYMENT');?></label></td>
                <td class="header_value"><label class="control-label"><?php echo number_format($item['amount_payment']-$item['amount_credit_sale_payment'],2);?></label></td>
                <td colspan="2">&nbsp;</td>
            </tr>
            <tr>
                <td class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_AMOUNT_CREDIT_SALE_PAYMENT');?></label></td>
                <td class="header_value"><label class="control-label"><?php echo number_format($item['amount_credit_sale_payment'],2);?></label></td>
                <td colspan="2">&nbsp;</td>
            </tr>
            <?php
            if($item['status_payment_receive']==$CI->config->item('system_status_received'))
            {
                ?>
                <tr>
                    <td class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_AMOUNT_BANK_CHARGE');?></label></td>
                    <td class=""><label class="control-label"><?php echo number_format($item['amount_bank_charge'],2);?></label></td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_AMOUNT_RECEIVE');?></label></td>
                    <td class=""><label class="control-label"><?php echo number_format(($item['amount_receive']),2);?></label></td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            <?php
            }
            ?>
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

            </tbody>
        </table>
    </div>
</div>
