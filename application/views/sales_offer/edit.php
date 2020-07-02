<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$CI=& get_instance();
$action_buttons=array();
$action_buttons[]=array
(
    'label'=>$CI->lang->line("ACTION_BACK"),
    'href'=>site_url($CI->controller_url)
);
if((isset($CI->permissions['action1']) && ($CI->permissions['action1']==1)) || (isset($CI->permissions['action2']) && ($CI->permissions['action2']==1)))
{
    $action_buttons[]=array
    (
        'type'=>'button',
        'label'=>$CI->lang->line("ACTION_SAVE"),
        'id'=>'button_action_save',
        'data-form'=>'#save_form'
    );
}
$action_buttons[]=array(
    'type'=>'button',
    'label'=>$CI->lang->line("ACTION_CLEAR"),
    'id'=>'button_action_clear',
    'data-form'=>'#save_form'
);
$CI->load->view('action_buttons',array('action_buttons'=>$action_buttons));
/*echo '<pre>';
print_r($offers);
echo '</pre>';*/
?>
<form id="save_form" action="<?php echo site_url($CI->controller_url.'/index/save');?>" method="post">
    <input type="hidden" id="id" name="id" value="<?php echo $item['id']?>" />
    <input type="hidden" id="system_save_new_status" name="system_save_new_status" value="0" />
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
                <td class="widget-header header_caption"><label class="control-label pull-right">Invoice Type</label></td>
                <td class=""><label class="control-label"><?php echo $item['invoice_type'];?></td>
                <td class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_STATUS');?></label></td>
                <td class=""><label class="control-label"><?php echo $item['status'];?></label></td>
            </tr>
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
            if($item['invoice_type']=='Return')
            {
                ?>
                <tr>
                    <td class="widget-header header_caption"><label class="control-label pull-right">Return Approved Time</label></td>
                    <td class=""><label class="control-label"><?php echo System_helper::display_date_time($item['date_return_approved']);?></td>
                    <td class="widget-header header_caption"><label class="control-label pull-right">Return Approved By</label></td>
                    <td class=""><label class="control-label"><?php echo $users[$item['user_return_approved']]['name'];?></label></td>
                </tr>
                <tr>
                    <td class="widget-header header_caption"><label class="control-label pull-right">Return Approval Remarks</label></td>
                    <td class="" colspan="3"><label class="control-label"><?php echo nl2br($item['remarks_return_approved']);?></td>
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
                    <th class="text-right">Offer Per kg</th>
                    <th class="text-center">#</th>
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
                $total_amount_offer=0;
                foreach($items as $row)
                {
                    $total_quantity+=$row['quantity'];
                    $total_weight_kg+=($row['quantity']*$row['pack_size']);
                    $quantity_kg=$row['quantity']*$row['pack_size']/1000;
                    //$quantity_kg+=.999;
                    $amount_offer=0;
                    if(isset($offers[$row['variety_id']]))
                    {
                        if($quantity_kg>=$offers[$row['variety_id']]['quantity_minimum'])
                        {
                            if($offers[$row['variety_id']]['is_floor']==$this->config->item('system_status_yes'))
                            {
                                $amount_offer=(floor($quantity_kg)*$offers[$row['variety_id']]['amount_per_kg']);
                            }
                            else
                            {
                                $amount_offer=($quantity_kg*$offers[$row['variety_id']]['amount_per_kg']);
                            }
                            $total_amount_offer+=$amount_offer;
                        }
                        //echo $row['variety_name'].' => '.$offers[$row['variety_id']]['quantity_minimum'].' => '.$quantity_kg.'<br />';
                    }
                    ?>
                    <tr>
                        <td><label><?php echo $row['crop_name'];?></label></td>
                        <td><label><?php echo $row['crop_type_name'];?></label></td>
                        <td><label><?php echo $row['variety_name'];?> <?php echo $row['variety_id'];?></label></td>
                        <td class="text-right"><label><?php echo $amount_offer?number_format($amount_offer,2,'.',','):'';?></label></td>
                        <td class="text-center">
                        <label>
                            <?php
                            if($amount_offer)
                            {
                              ?>
                                <input type="checkbox" id="items_<?php echo $row['id'];?>" name="items_<?php echo $row['id'];?>" value="<?php echo $row['id'];?>"  />
                            <?php
                            }
                            ?>
                        </label>
                        </td>
                        <td class="text-right"><label><?php echo $row['pack_size'];?></label></td>
                        <td class="text-right"><label><?php echo number_format($row['price_unit_pack'],2); ?></label></td>
                        <td class="text-right"><label><?php echo $row['quantity']; ?></label></td>
                        <td class="text-right" ><label><?php echo number_format($quantity_kg,3,'.','');?></label></td>
                        <td class="text-right"><label><?php echo number_format($row['amount_total'],2);?></label></td>
                        <td class="text-right"><label><?php echo number_format($row['discount_percentage_variety'],2);?></label></td>
                        <td class="text-right"><label><?php echo number_format($row['amount_discount_variety'],2);?></label></td>
                        <td class="text-right"><label><?php echo number_format($row['amount_payable_actual'],2);?></label></td>
                    </tr>
                <?php
                }
                ?>
                </tbody>
                <tfoot>
                <tr>
                    <td colspan="2">&nbsp;</td>
                    <td><label><?php echo $CI->lang->line('LABEL_TOTAL'); ?></label></td>
                    <td class="text-right"><label><?php echo $total_amount_offer?number_format($total_amount_offer,2,'.',','):'';?></label></td>
                    <td colspan="2">&nbsp;</td>
                    <td class="text-right"><label><?php echo $total_quantity; ?></label></td>
                    <td class="text-right"><label><?php echo number_format($total_weight_kg/1000,3,'.','');?></label></td>
                    <td class="text-right"><label><?php echo number_format($item['amount_total'],2); ?></label></td>
                    <td>&nbsp;</td>
                    <td class="text-right"><label><?php echo number_format($item['amount_discount_variety'],2); ?></label></td>
                    <td class="text-right"><label><?php echo number_format($item['amount_total']-$item['amount_discount_variety'],2); ?></label></td>
                </tr>
                <tr>
                    <td colspan="11">&nbsp;</td>
                    <td><label><?php echo $CI->lang->line('LABEL_DISCOUNT'); ?>(<?php echo $item['discount_self_percentage'];?>%)</label></td>
                    <td class="text-right"><label><?php echo number_format($item['amount_discount_self'],2); ?></label></td>
                </tr>
                <tr>
                    <td colspan="11">&nbsp;</td>
                    <td><label>Payable</label></td>
                    <td class="text-right"><label><?php echo number_format($item['amount_payable'],2); ?></label></td>
                </tr>
                <tr>
                    <td colspan="11">&nbsp;</td>
                    <td><label>Payable(rounded)</label></td>
                    <td class="text-right"><label><?php echo number_format($item['amount_payable_actual'],2); ?></label></td>
                </tr>
                <tr>
                    <td colspan="11">&nbsp;</td>
                    <td><label>Paid</label></td>
                    <td class="text-right"><label><?php echo number_format($item['amount_cash'],2); ?></label></td>
                </tr>
                <tr>
                    <td colspan="11">&nbsp;</td>
                    <td><label>Change</label></td>
                    <td class="text-right"><label><?php echo number_format($item['amount_cash']-$item['amount_payable_actual'],2); ?></label></td>
                </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <div class="clearfix"></div>
</form>

<script type="text/javascript">
    jQuery(document).ready(function()
    {
        system_preset({controller:'<?php echo $CI->router->class; ?>'});

    });
</script>
