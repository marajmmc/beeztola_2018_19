<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$CI=& get_instance();
$user = User_helper::get_user();
?>
<div class="row widget hidden-print">
    <div class="widget-header">
        <div class="title">
            <?php echo $title; ?>
        </div>
        <div class="clearfix"></div>
    </div>
    <table class="table table-bordered table-responsive system_table_details_view">
        <tbody>
        <tr>
            <td class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_OUTLET_NAME');?></label></td>
            <td class=""><label class="control-label"><?php echo $item['outlet_name'];?></td>
            <td class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_CUSTOMER_NAME');?></label></td>
            <td class=""><label class="control-label"><?php echo $item['farmer_name'];?></label></td>
        </tr>
        <tr>
            <td class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_INVOICE_NO');?></label></td>
            <td class=""><label class="control-label"><?php echo Barcode_helper::get_barcode_sales($item['id']);?></td>
            <td class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_MOBILE_NO');?></label></td>
            <td class=""><label class="control-label"><?php echo $item['mobile_no'];?></label></td>
        </tr>
        <tr>
            <td class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DATE');?></label></td>
            <td class=""><label class="control-label"><?php echo System_helper::display_date_time($item['date_sale']);?></td>
            <td class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_SALES_PAYMENT_METHOD');?></label></td>
            <td class=""><label class="control-label"><?php echo $item['sales_payment_method'];?></label></td>
        </tr>
        <tr>
            <td class="widget-header header_caption"><label class="control-label pull-right">Invoice Created Time</label></td>
            <td class=""><label class="control-label"><?php echo System_helper::display_date_time($item['date_created']);?></td>
            <td class="widget-header header_caption"><label class="control-label pull-right">Invoice Created By</label></td>
            <td class=""><label class="control-label"><?php echo $users[$item['user_created']]['name'];?></label></td>
        </tr>
        <?php
        if(strlen($item['remarks'])>0)
        {
            ?>
            <tr>
                <td class="widget-header header_caption"><label class="control-label pull-right">Remarks</label></td>
                <td class="" colspan="3"><label class="control-label"><?php echo nl2br($item['remarks']);?></td>
            </tr>
        <?php
        }
        ?>
        <?php
        if($item['status_manual_sale']==$CI->config->item('system_status_yes'))
        {
            ?>
            <tr>
                <td class="widget-header header_caption"><label class="control-label pull-right">Special Sale Approved Time</label></td>
                <td class=""><label class="control-label"><?php echo System_helper::display_date_time($item['date_manual_approved']);?></td>
                <td class="widget-header header_caption"><label class="control-label pull-right">Special Sale Approved By</label></td>
                <td class=""><label class="control-label"><?php echo $users[$item['user_manual_approved']]['name'];?></label></td>
            </tr>
            <tr>
                <td class="widget-header header_caption"><label class="control-label pull-right">Special Approval Remarks</label></td>
                <td class="" colspan="3"><label class="control-label"><?php echo nl2br($item['remarks_manual_approved']);?></td>
            </tr>
        <?php
        }
        ?>
        <?php
        if($item['status']==$CI->config->item('system_status_inactive'))
        {
            ?>
            <tr>
                <td class="widget-header header_caption"><label class="control-label pull-right">Cancel Date</label></td>
                <td class="" colspan="3"><label class="control-label"><?php echo System_helper::display_date($item['date_cancel']);?></td>
            </tr>
            <tr>
                <td class="widget-header header_caption"><label class="control-label pull-right">Invoice Cancel Approve Time</label></td>
                <td class=""><label class="control-label"><?php echo System_helper::display_date_time($item['date_cancel_approved']);?></td>
                <td class="widget-header header_caption"><label class="control-label pull-right">Invoice Cancel Approved By</label></td>
                <td class=""><label class="control-label"><?php echo $users[$item['user_cancel_approved']]['name'];?></label></td>
            </tr>
            <tr>
                <td class="widget-header header_caption"><label class="control-label pull-right">Cancel Approval Remarks</label></td>
                <td class="" colspan="3"><label class="control-label"><?php echo nl2br($item['remarks_cancel_approved']);?></td>
            </tr>
        <?php
        }
        ?>
        </tbody>
    </table>
    <div class="widget-header">
        <div class="title">
            Items
        </div>
        <div class="clearfix"></div>
    </div>
    <div style="overflow-x: auto;" class="row show-grid">
        <table class="table table-bordered">
            <thead>
            <tr>
                <th style="min-width: 100px;"><?php echo $CI->lang->line('LABEL_CROP_NAME'); ?></th>
                <th style="min-width: 100px;"><?php echo $CI->lang->line('LABEL_CROP_TYPE_NAME'); ?></th>
                <th style="min-width: 100px;"><?php echo $CI->lang->line('LABEL_VARIETY_NAME'); ?></th>
                <th style="min-width: 100px;"><?php echo $CI->lang->line('LABEL_PACK_SIZE_NAME'); ?></th>
                <th style="min-width: 100px;"><?php echo $CI->lang->line('LABEL_PRICE_PER_PACK'); ?></th>
                <th style="min-width: 100px;"><?php echo $CI->lang->line('LABEL_QUANTITY'); ?>(Packets)</th>
                <th style="min-width: 100px;"><?php echo $CI->lang->line('LABEL_WEIGHT_KG'); ?></th>
                <th style="min-width: 100px;">Price</th>
                <th style="min-width: 100px;">Variety Discount%</th>
                <th style="min-width: 100px;">Variety Discount</th>
                <th style="min-width: 100px;">Actual Price</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $total_quantity=0;
            $total_weight_kg=0;
            foreach($items as $row)
            {
                $total_quantity+=$row['quantity'];
                $total_weight_kg+=($row['quantity']*$row['pack_size']);

                ?>
                <tr>
                    <td><label><?php echo $row['crop_name'];?></label></td>
                    <td><label><?php echo $row['crop_type_name'];?></label></td>
                    <td><label><?php echo $row['variety_name'];?></label></td>
                    <td class="text-right"><label><?php echo $row['pack_size'];?></label></td>
                    <td class="text-right"><label><?php echo number_format($row['price_unit_pack'],2); ?></label></td>
                    <td class="text-right"><label><?php echo $row['quantity']; ?></label></td>
                    <td class="text-right" ><label><?php echo number_format($row['quantity']*$row['pack_size']/1000,3,'.','');?></label>
                    <td class="text-right"><label><?php echo number_format($row['amount_total'],2);?></label>
                    <td class="text-right"><label><?php echo number_format($row['discount_percentage_variety'],2);?></label>
                    <td class="text-right"><label><?php echo number_format($row['amount_discount_variety'],2);?></label>
                    <td class="text-right"><label><?php echo number_format($row['amount_payable_actual'],2);?></label>
                </tr>
            <?php
            }
            ?>

            </tbody>
            <tfoot>
            <tr>
                <td colspan="4">&nbsp;</td>
                <td><label><?php echo $CI->lang->line('LABEL_TOTAL'); ?></label></td>
                <td class="text-right"><label><?php echo $total_quantity; ?></label></td>
                <td class="text-right"><label><?php echo number_format($total_weight_kg/1000,3,'.','');?></label></td>
                <td class="text-right"><label><?php echo number_format($item['amount_total'],2); ?></label></td>
                <td>&nbsp;</td>
                <td class="text-right"><label><?php echo number_format($item['amount_discount_variety'],2); ?></label></td>
                <td class="text-right"><label><?php echo number_format($item['amount_total']-$item['amount_discount_variety'],2); ?></label></td>
            </tr>
            <tr>
                <td colspan="9">&nbsp;</td>
                <td><label><?php echo $CI->lang->line('LABEL_DISCOUNT'); ?>(<?php echo $item['discount_self_percentage'];?>%)</label></td>
                <td class="text-right"><label><?php echo number_format($item['amount_discount_self'],2); ?></label></td>
            </tr>
            <tr>
                <td colspan="9">&nbsp;</td>
                <td><label>Payable</label></td>
                <td class="text-right"><label><?php echo number_format($item['amount_payable'],2); ?></label></td>
            </tr>
            <tr>
                <td colspan="9">&nbsp;</td>
                <td><label>Payable(rounded)</label></td>
                <td class="text-right"><label><?php echo number_format($item['amount_payable_actual'],2); ?></label></td>
            </tr>
            <tr>
                <td colspan="9">&nbsp;</td>
                <td><label>Paid</label></td>
                <td class="text-right"><label><?php echo number_format($item['amount_cash'],2); ?></label></td>
            </tr>
            <tr>
                <td colspan="9">&nbsp;</td>
                <td><label>Change</label></td>
                <td class="text-right"><label><?php echo number_format($item['amount_cash']-$item['amount_payable_actual'],2); ?></label></td>
            </tr>
            </tfoot>
        </table>
    </div>
</div>
