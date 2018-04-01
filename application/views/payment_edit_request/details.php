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
            <?php echo $title; ?>
        </div>
        <div class="clearfix"></div>

    </div>
    <div class="col-xs-12">
        <table class="table table-bordered table-responsive system_table_details_view">
            <tbody>
            <tr>
                <td class="widget-header header_caption"><label class="control-label pull-right">Edit Requested By</label></td>
                <td class="header_value"><label class="control-label"><?php echo $users[$item['user_request_updated']]['name'];?></label></td>
                <td class="widget-header header_caption"><label class="control-label pull-right">Edit Requested Time</label></td>
                <td class="header_value"><label class="control-label"><?php echo System_helper::display_date_time($item['date_request_updated']);?></label></td>
            </tr>
            <tr>
                <td class="widget-header header_caption"><label class="control-label pull-right">Edit Reason</label></td>
                <td colspan="3" class=" header_value"><label class="control-label"><?php echo nl2br($item['remarks_request']);?></label></td>
            </tr>
            <?php
            if($item['status_request_forward']!=$CI->config->item('system_status_pending'))
            {
            ?>
                <tr>
                    <td class="widget-header header_caption"><label class="control-label pull-right">Edit Request Forwarded By</label></td>
                    <td class="header_value"><label class="control-label"><?php echo $users[$item['user_request_forwarded']]['name'];?></label></td>
                    <td class="widget-header header_caption"><label class="control-label pull-right">Edit Request Forwarded Time</label></td>
                    <td class="header_value"><label class="control-label"><?php echo System_helper::display_date_time($item['date_request_forwarded']);?></label></td>
                </tr>
            <?php
            }
            else
            {
                ?>
                <tr>
                    <td class="widget-header header_caption"><label class="control-label pull-right">Forward Status</label></td>
                    <td class="header_value"><label class="control-label"><?php echo $item['status_request_forward'];?></label></td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
                <?php
            }
            ?>
            <tr>
                <td class="widget-header header_caption"><label class="control-label pull-right">Approve Status</label></td>
                <td class="header_value"><label class="control-label"><?php echo $item['status_approve'];?></label></td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
            <?php
            if($item['status_approve']!=$CI->config->item('system_status_pending'))
            {
                ?>
                <tr>
                    <td class="widget-header header_caption"><label class="control-label pull-right">Edit Approved/Rejected By</label></td>
                    <td class="header_value"><label class="control-label"><?php echo $users[$item['user_manual_edit_approved']]['name'];?></label></td>
                    <td class="widget-header header_caption"><label class="control-label pull-right">Edit Approved/Rejected Time</label></td>
                    <td class="header_value"><label class="control-label"><?php echo System_helper::display_date_time($item['date_manual_edit_approved']);?></label></td>
                </tr>
                <tr>
                    <td class="widget-header header_caption"><label class="control-label pull-right">Approved/Rejected Reason</label></td>
                    <td colspan="3" class=" header_value"><label class="control-label"><?php echo nl2br($item['remarks_manual_edit_approved']);?></label></td>
                </tr>
            <?php
            }
            ?>
            <tr>
                <td class="widget-header header_caption"><label class="control-label pull-right">Edit Request Id</label></td>
                <td class=""><label class="control-label"><?php echo ($item['id']);?></label></td>
                <td class="widget-header header_caption"><label class="control-label pull-right">Payment <?php echo $CI->lang->line('LABEL_ID');?></label></td>
                <td class=""><label class="control-label"><?php echo Barcode_helper::get_barcode_payment($item['payment_id']);?></label></td>

            </tr>
            <tr>
                <td class="widget-header header_caption text-center" colspan="2"><label class="control-label"><?php echo ($item['status_approve']==$CI->config->item('system_status_approved'))?'Approved Value':'New Value';?></label></td>
                <td class="widget-header header_caption text-center" colspan="2"><label class="control-label"><?php echo ($item['status_approve']==$CI->config->item('system_status_approved'))?'Previous Value':'Current Value';?></label></td>
            </tr>
            <tr>
                <td class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DATE_PAYMENT');?></label></td>
                <td class=""><label class="control-label"><?php echo System_helper::display_date($item['date_payment']);?></label></td>
                <td class=""><label class="control-label"><?php echo System_helper::display_date($item_current['date_payment']);?></label></td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DATE_SALE');?></label></td>
                <td class=""><label class="control-label"><?php echo System_helper::display_date($item['date_sale']);?></label></td>
                <td class=""><label class="control-label"><?php echo System_helper::display_date($item_current['date_sale']);?></label></td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DATE_RECEIVE');?></label></td>
                <td class=""><label class="control-label"><?php echo System_helper::display_date($item['date_receive']);?></label></td>
                <td class=""><label class="control-label"><?php echo System_helper::display_date($item_current['date_receive']);?></label></td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_OUTLET');?></label></td>
                <td class=""><label class="control-label"><?php echo $item['outlet_name'];?></label></td>
                <td class=""><label class="control-label"><?php echo $item_current['outlet_name'];?></label></td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_PAYMENT_WAY');?></label></td>
                <td class="header_value"><label class="control-label"><?php echo $item['payment_way'];?></label></td>
                <td class="header_value"><label class="control-label"><?php echo $item_current['payment_way'];?></label></td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_REFERENCE_NO');?></label></td>
                <td class=" header_value"><label class="control-label"><?php echo $item['reference_no'];?></label></td>
                <td class=" header_value"><label class="control-label"><?php echo $item_current['reference_no'];?></label></td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_AMOUNT_PAYMENT');?></label></td>
                <td class=""><label class="control-label"><?php echo number_format($item['amount_payment'],2);?></label></td>
                <td class=""><label class="control-label"><?php echo number_format($item_current['amount_payment'],2);?></label></td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_AMOUNT_BANK_CHARGE');?></label></td>
                <td class=""><label class="control-label"><?php echo number_format($item['amount_bank_charge'],2);?></label></td>
                <td class=""><label class="control-label"><?php echo number_format($item_current['amount_bank_charge'],2);?></label></td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_AMOUNT_RECEIVE');?></label></td>
                <td class=""><label class="control-label"><?php echo number_format(($item['amount_receive']),2);?></label></td>
                <td class=""><label class="control-label"><?php echo number_format(($item_current['amount_receive']),2);?></label></td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_BANK_PAYMENT_SOURCE');?></label></td>
                <td class="header_value"><label class="control-label"><?php echo $item['bank_payment_source'];?></label></td>
                <td class="header_value"><label class="control-label"><?php echo $item_current['bank_payment_source'];?></label></td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_BANK_BRANCH_SOURCE');?></label></td>
                <td class="header_value"><label class="control-label"><?php echo $item['bank_branch_source'];?></label></td>
                <td class="header_value"><label class="control-label"><?php echo $item_current['bank_branch_source'];?></label></td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_BANK_ACCOUNT_NUMBER_DESTINATION');?></label></td>
                <td class=" header_value"><label class="control-label"><?php echo $item['account_number'].' ('.$item['bank_destination'].' -'.$item['branch_name'].')';?></label></td>
                <td class=" header_value"><label class="control-label"><?php echo $item_current['account_number'].' ('.$item_current['bank_destination'].' -'.$item_current['branch_name'].')';?></label></td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td class="widget-header header_caption"><label class="control-label pull-right">Attachment (Document)</label></td>
                <td class="header_value"><img style="max-width: 250px;" src="<?php echo $CI->config->item('system_base_url_payment_attachment').$item['image_location']; ?>" alt="<?php echo $item['image_name']; ?>"></td>
                <td class="header_value"><img style="max-width: 250px;" src="<?php echo $CI->config->item('system_base_url_payment_attachment').$item_current['image_location']; ?>" alt="<?php echo $item_current['image_name']; ?>"></td>
            </tr>

            </tbody>
        </table>
    </div>
</div>
<div class="clearfix"></div>
