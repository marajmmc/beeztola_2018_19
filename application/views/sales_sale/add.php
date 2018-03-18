<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$CI=& get_instance();
?>
<form id="sale_form" class="external" action="<?php echo site_url($CI->controller_url.'/index/save');?>" method="post">
    <input type="hidden" name="item[customer_id]" id="customer_id" value="<?php echo $item['customer_id']; ?>" />
    <input type="hidden" name="item[farmer_id]" value="<?php echo $item['id']; ?>" />
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_NAME');?></label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo $item['name'];?></label>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_MOBILE_NO');?></label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo $item['mobile_no'];?></label>
        </div>
    </div>
    <?php
    if(strlen($item['nid'])>0)
    {
        ?>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_NID');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $item['nid'];?></label>
            </div>
        </div>
        <?php
    }
    ?>
    <?php
    if(strlen($item['address'])>0)
    {
        ?>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_ADDRESS');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $item['address'];?></label>
            </div>
        </div>
    <?php
    }
    ?>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_TYPE');?></label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo $farmer_type['name'];?></label>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DISCOUNT');?></label>
        </div>
        <div class="col-xs-4">
            <label class="control-label" id="discount"><?php echo $farmer_type['discount_non_coupon'];?></label>%
            <input type="hidden" id="discount_non_coupon" value="<?php echo $farmer_type['discount_non_coupon'];?>">
        </div>
        <div class="col-xs-4">
            <div class="action_button">
                <button id="button_action_discount_clear" style="display: none;" type="button" class="btn">Remove Coupon Discount</button>
            </div>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right">Scan Coupon</label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <input type="text" id="coupon_barcode" class="form-control" value=""/>
        </div>
    </div>
    <div id="container_discount_info">

    </div>
    <div class="clearfix"></div>
    <div class="widget-header">
        <div class="title">
            Items
        </div>
        <div class="clearfix"></div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo 'Variety '.$this->lang->line('LABEL_BARCODE');?></label>
        </div>
        <div class="col-sm-4 col-xs-4">
            <input type="text" id="variety_barcode" class="form-control" value=""/>
        </div>
        <div class="col-sm-4 col-xs-4">
            <div class="action_button">
                <button id="button_action_variety_add" type="button" class="btn"><?php echo $this->lang->line('LABEL_ACTION1');?></button>
            </div>
        </div>
    </div>
    <div style="overflow-x: auto;" class="row show-grid" id="order_items_container">
        <table class="table table-bordered">
            <thead>
            <tr>
                <th style="min-width: 100px;"><?php echo $CI->lang->line('LABEL_CROP_NAME'); ?></th>
                <th style="min-width: 100px;"><?php echo $CI->lang->line('LABEL_CROP_TYPE'); ?></th>
                <th style="min-width: 100px;"><?php echo $CI->lang->line('LABEL_VARIETY_NAME'); ?></th>
                <th style="min-width: 100px;"><?php echo $CI->lang->line('LABEL_PACK_NAME'); ?></th>
                <th style="min-width: 100px;"><?php echo $CI->lang->line('LABEL_PRICE_PACK'); ?></th>
                <th style="min-width: 100px;"><?php echo $CI->lang->line('LABEL_CURRENT_STOCK_PIECES'); ?></th>
                <th style="min-width: 100px;"><?php echo $CI->lang->line('LABEL_QUANTITY_PIECES'); ?></th>
                <th style="min-width: 100px;"><?php echo $CI->lang->line('LABEL_WEIGHT_KG'); ?></th>
                <th style="min-width: 100px;"><?php echo $CI->lang->line('LABEL_TOTAL_PRICE'); ?></th>
                <th style="min-width: 150px;"><?php echo $CI->lang->line('ACTION'); ?></th>
            </tr>
            </thead>
            <tbody>

            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5">&nbsp;</td>
                    <td><label><?php echo $CI->lang->line('LABEL_TOTAL'); ?></label></td>
                    <td class="text-right"><label id="total_quantity">&nbsp;</label></td>
                    <td class="text-right"><label id="total_weight">&nbsp;</label></td>
                    <td class="text-right"><label id="total_price">&nbsp;</label></td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="7">&nbsp;</td>
                    <td><label><?php echo $CI->lang->line('LABEL_DISCOUNT'); ?></label></td>
                    <td class="text-right"><label id="total_discount">&nbsp;</label></td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="7">&nbsp;</td>
                    <td><label>Payable</label></td>
                    <td class="text-right"><label id="total_payable">&nbsp;</label></td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="7">&nbsp;</td>
                    <td><label>Paid</label></td>
                    <td class="text-right"><input id="total_paid" name="amount_paid" type="text"class="form-control text-right float_type_positive" value=""/></td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="7">&nbsp;</td>
                    <td><label>Change</label></td>
                    <td class="text-right"><label id="total_change">&nbsp;</label></td>
                    <td>&nbsp;</td>
                </tr>
            </tfoot>
        </table>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">

        </div>
        <div class="col-sm-4 col-xs-4">
            <div class="action_button">
                <button id="button_action_save" type="button" class="btn" data-form="#sale_form" data-message-confirm="Are you sure?"><?php echo $this->lang->line('ACTION_SAVE');?></button>
            </div>
        </div>
        <div class="col-sm-4 col-xs-4">

        </div>
    </div>
</form>
<div id="system_content_add_more" style="display: none;">
    <table>
        <tbody>
        <tr>
            <td>
                <label class="crop_name">&nbsp;</label>
            </td>
            <td>
                <label class="type_name">&nbsp;</label>
            </td>
            <td>
                <label class="variety_name">&nbsp;</label>
            </td>
            <td>
                <label class="pack_size">&nbsp;</label>
            </td>
            <td class="text-right">
                <label class="pack_size_price">&nbsp;</label>
            </td>
            <td class="text-right">
                <label class="current_stock">&nbsp;</label>
            </td>
            <td class="text-right">
                <input type="text"class="form-control text-right quantity integer_type_positive" value="1"/>
            </td>
            <td class="text-right">
                <label class="weight">&nbsp;</label>
            </td>
            <td class="text-right">
                <label class="price">&nbsp;</label>
            </td>
            <td><button type="button" class="btn btn-danger system_button_add_delete"><?php echo $CI->lang->line('ACTION_DELETE'); ?></button></td>
        </tr>
        </tbody>
    </table>
</div>