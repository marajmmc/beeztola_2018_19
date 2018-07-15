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
<form id="save_form" action="<?php echo site_url($CI->controller_url.'/index/save_delivery');?>" method="post">
    <input type="hidden" id="id" name="id" value="<?php echo $item['id']; ?>" />
    <input type="hidden" id="system_save_new_status" name="system_save_new_status" value="0" />
    <div class="row widget">
        <div class="widget-header">
            <div class="title">
                <?php echo $title; ?>
            </div>
            <div class="clearfix"></div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <label class=""><a class="external text-danger" data-toggle="collapse" data-target="#collapse3" href="#">+ Basic Information</a></label>
                </h4>
            </div>
            <div id="collapse3" class="panel-collapse collapse">
                <table class="table table-bordered table-responsive system_table_details_view">
                    <thead>
                    <tr>
                        <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_ID');?></label></th>
                        <th class=""><label class="control-label"><?php echo Barcode_helper::get_barcode_transfer_outlet_to_warehouse($item['id']);?></label></th>
                        <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_OUTLET_NAME_SOURCE');?></label></th>
                        <th class=" header_value"><label class="control-label"><?php echo $CI->outlets[$item['outlet_id_source']]['name'];?></label></th>
                    </tr>
                    <tr>
                        <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DATE_REQUEST');?></label></th>
                        <th class=" header_value"><label class="control-label"><?php echo System_helper::display_date($item['date_request']);?></label></th>
                        <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_OUTLET_NAME_DESTINATION');?></label></th>
                        <th class=" header_value"><label class="control-label"><?php echo $CI->outlets[$item['outlet_id_destination']]['name'];?></label></th>
                    </tr>
                    <tr>
                        <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DATE_APPROVE');?></label></th>
                        <th class=" header_value"><label class="control-label"><?php echo System_helper::display_date($item['date_approve']);?></label></th>
                        <th colspan="2">&nbsp;</th>
                    </tr>
                    </thead>
                </table>
                <div class="clearfix"></div>
                <table class="table table-bordered table-responsive system_table_details_view">
                    <thead>
                    <tr><th colspan="21" class="bg-info">Delivery & Courier Information</th></tr>
                    <tr>
                        <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DATE_DELIVERY');?></label></th>
                        <th class="alert-warning header_value"><label class="control-label"><?php echo System_helper::display_date($item['courier_date_delivery']);?></label></th>
                        <th class="widget-header header_caption"><label class="control-label pull-right">Challan No</label></th>
                        <th class=""><label class="control-label"><?php echo $item['challan_no'];?></label></th>
                    </tr>
                    <tr>
                        <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DATE_CHALLAN');?></label></th>
                        <th class=" header_value"><label class="control-label"><?php echo System_helper::display_date($item['date_challan']);?></label></th>
                        <th class="widget-header header_caption"><label class="control-label pull-right">Courier Name</label></th>
                        <th class="alert-warning"><label class="control-label"><?php echo $item['courier_name'];?></label></th>
                    </tr>
                    <tr>
                        <th class="widget-header header_caption"><label class="control-label pull-right">Booking Date</label></th>
                        <th class=" header_value"><label class="control-label"><?php echo System_helper::display_date($item['date_booking']);?></label></th>
                        <th class="widget-header header_caption"><label class="control-label pull-right">Courier Tracing No</label></th>
                        <th class=""><label class="control-label"><?php echo $item['courier_tracing_no'];?></label></th>
                    </tr>
                    <tr>
                        <th class="widget-header header_caption" style="vertical-align: top"><label class="control-label pull-right">Booking Branch (Place)</label></th>
                        <th class=" header_value" colspan="3"><label class="control-label"><?php echo nl2br($item['place_booking_source']);?></label></th>
                    </tr>
                    <tr>
                        <th class="widget-header header_caption" style="vertical-align: top"><label class="control-label pull-right">Receive Branch (Place)</label></th>
                        <th class=" header_value" colspan="3"><label class="control-label"><?php echo nl2br($item['place_destination']);?></label></th>
                    </tr>
                    <tr>
                        <th class="widget-header header_caption" style="vertical-align: top"><label class="control-label pull-right">Remarks for Courier</label></th>
                        <th class=" header_value" colspan="3"><label class="control-label"><?php echo nl2br($item['remarks_couriers']);?></label></th>
                    </tr>
                    </thead>
                </table>
            </div>
        </div>
        <div class="clearfix"></div>
        <div class="row show-grid">
            <div style="overflow-x: auto;" class="row show-grid">
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th colspan="21" class="text-center success"><?php echo $CI->lang->line('LABEL_RETURN_ITEMS');?></th>
                    </tr>
                    <tr>
                        <th rowspan="2" style="width: 200px;"><?php echo $CI->lang->line('LABEL_CROP_NAME'); ?></th>
                        <th rowspan="2" style="width: 150px;"><?php echo $CI->lang->line('LABEL_CROP_TYPE_NAME'); ?></th>
                        <th rowspan="2" style="width: 150px;"><?php echo $CI->lang->line('LABEL_VARIETY_NAME'); ?></th>
                        <th rowspan="2" class="text-right" style="width: 150px;"><?php echo $CI->lang->line('LABEL_PACK_SIZE'); ?></th>
                        <th colspan="2" class="text-center" style="width: 300px;"><?php echo $CI->lang->line('LABEL_CURRENT_STOCK'); ?></th>
                        <th colspan="2" class="text-center" style="width: 300px;">Approve Return Quantity</th>
                        <th colspan="2" class="text-center" style="width: 150px;">Available Stock<?php //echo $CI->lang->line('LABEL_CURRENT_STOCK'); ?></th>
                    </tr>
                    <tr>
                        <th style="width: 150px;" class="text-right"><?php echo $CI->lang->line('LABEL_PACK');?></th>
                        <th style="width: 150px;" class="text-right"><?php echo $CI->lang->line('LABEL_KG');?></th>
                        <th style="width: 150px;" class="text-right"><?php echo $CI->lang->line('LABEL_PACK');?></th>
                        <th style="width: 150px;" class="text-right"><?php echo $CI->lang->line('LABEL_KG');?></th>
                        <th style="width: 150px;" class="text-right"><?php echo $CI->lang->line('LABEL_PACK');?></th>
                        <th style="width: 150px;" class="text-right"><?php echo $CI->lang->line('LABEL_KG');?></th>
                    </tr>
                    </thead>
                    <tbody id="items_container">
                    <?php
                    $stock_current_kg=0;

                    $quantity_approve=0;
                    $quantity_total_approve=0;
                    $quantity_total_approve_kg=0;

                    $stock_current_quantity_total=0;
                    $stock_current_quantity_total_kg=0;

                    $stock_quantity_new=0;
                    $stock_quantity_new_kg=0;
                    $stock_quantity_total_new=0;
                    $stock_quantity_total_new_kg=0;

                    foreach($items as $index=>$value)
                    {
                        $quantity_approve=$value['quantity_approve'];
                        $quantity_approve_kg=(($quantity_approve*$value['pack_size'])/1000);
                        $quantity_total_approve+=$quantity_approve;
                        $quantity_total_approve_kg+=$quantity_approve_kg;

                        if(isset($stocks[$value['variety_id']][$value['pack_size_id']]))
                        {
                            $stock_current=$stocks[$value['variety_id']][$value['pack_size_id']]['current_stock'];
                        }
                        else
                        {
                            $stock_current=0;
                        }
                        $stock_current_kg=(($stock_current*$value['pack_size'])/1000);

                        $stock_current_quantity_total+=$stock_current;
                        $stock_current_quantity_total_kg+=$stock_current_kg;

                        $stock_quantity_new=($stock_current-$quantity_approve);
                        $stock_quantity_new_kg=($stock_current_kg-$quantity_approve_kg);
                        $stock_quantity_total_new+=$stock_quantity_new;
                        $stock_quantity_total_new_kg+=$stock_quantity_new_kg;
                        if($quantity_approve>$stock_current)
                        {
                            $class_bg_warning='bg-danger';
                        }
                        else
                        {
                            $class_bg_warning='';
                        }
                        ?>
                        <tr id="item_rows_<?php echo $index+1;?>" class="<?php echo $class_bg_warning;?>">
                            <td>
                                <label><?php echo $value['crop_name']; ?></label>
                            </td>
                            <td>
                                <label><?php echo $value['crop_type_name']; ?></label>
                            </td>
                            <td>
                                <label><?php echo $value['variety_name']; ?></label>
                                <input type="hidden" name="items[<?php echo $index+1;?>][variety_id]" id="variety_id_<?php echo $index+1;?>" value="<?php echo $value['variety_id']; ?>" data-current-id="<?php echo $index+1;?>" />
                            </td>
                            <td class="text-right">
                                <label><?php echo $value['pack_size']; ?></label>
                                <input type="hidden" name="items[<?php echo $index+1;?>][pack_size_id]" id="pack_size_id_<?php echo $index+1;?>" value="<?php echo $value['pack_size_id']; ?>" class="pack_size_id" data-current-id="<?php echo $index+1;?>" data-pack-size-name="<?php echo $value['pack_size']; ?>">
                            </td>
                            <td class="text-right">
                                <label class="control-label stock_current " id="stock_current_<?php echo $index+1;?>" data-current-id="<?php echo $index+1;?>">
                                    <?php
                                    echo $stock_current;
                                    ?>
                                </label>
                            </td>
                            <td class="text-right">
                                <label class="control-label stock_current_kg " id="stock_current_kg_<?php echo $index+1;?>" data-current-id="<?php echo $index+1;?>">
                                    <?php
                                    echo number_format($stock_current_kg,3,'.','');
                                    ?>
                                </label>
                            </td>
                            <td class="text-right">
                                <label class="quantity_approve " id="quantity_approve_<?php echo $index+1;?>"><?php echo $quantity_approve; ?></label>
                            </td>
                            <td class="text-right">
                                <label class="quantity_approve_kg " id="quantity_approve_kg_<?php echo $index+1;?>"> <?php echo number_format($quantity_approve_kg,3,'.','');?> </label>
                            </td>
                            <td class="text-right">
                                <label class="control-label stock_quantity_new " id="stock_quantity_new_<?php echo $index+1;?>" data-current-id="<?php echo $index+1;?>">
                                    <?php
                                    echo $stock_quantity_new;
                                    ?>
                                </label>
                            </td>
                            <td class="text-right">
                                <label class="control-label stock_quantity_new_kg " id="stock_quantity_new_kg_<?php echo $index+1;?>" data-current-id="<?php echo $index+1;?>">
                                    <?php
                                    echo number_format($stock_quantity_new_kg,3,'.','');
                                    ?>
                                </label>
                            </td>
                        </tr>
                    <?php
                    }
                    ?>
                    </tbody>
                    <tfoot>
                    <tr>
                        <th colspan="4" class="text-right"><?php echo $CI->lang->line('LABEL_TOTAL');?></th>
                        <th class="text-right"><label class="control-label" id="stock_current_quantity_total"> <?php echo $stock_current_quantity_total;?></label></th>
                        <th class="text-right"><label class="control-label" id="stock_current_quantity_total_kg"> <?php echo number_format($stock_current_quantity_total_kg,3,'.','');?></label></th>
                        <th class="text-right"><label class="control-label" id="quantity_total_approve"> <?php echo $quantity_total_approve;?></label></th>
                        <th class="text-right"><label class="control-label" id="quantity_total_approve_kg"> <?php echo number_format($quantity_total_approve_kg,3,'.','');?></label></th>
                        <th class="text-right"><label class="control-label" id="stock_quantity_total_new"> <?php echo $stock_quantity_total_new;?></label></th>
                        <th class="text-right"><label class="control-label" id="stock_quantity_total_new_kg"> <?php echo number_format($stock_quantity_total_new_kg,3,'.','');?></label></th>
                    </tr>
                    </tfoot>
                </table>
            </div>
            <?php
            if($item['remarks_challan'])
            {
                ?>
                <div class="row show-grid">
                    <div class="col-xs-4">
                        <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_REMARKS_CHALLAN');?></label>
                    </div>
                    <div class="col-sm-4 col-xs-8">
                        <?php echo nl2br($item['remarks_challan']);?>
                    </div>
                </div>
            <?php
            }
            ?>
        </div>
        <div class="widget-header">
            <div class="title">
                Delivery Confirmation
            </div>
            <div class="clearfix"></div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DELIVERED');?> <span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <select id="status_delivery" class="form-control" name="item[status_delivery]">
                    <option value=""><?php echo $CI->lang->line('SELECT');?></option>
                    <option value="<?php echo $this->config->item('system_status_delivered')?>"><?php echo $this->config->item('system_status_delivered')?></option>
                </select>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_REMARKS_DELIVERY');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <textarea name="item[remarks_delivery]" id="remarks_delivery" class="form-control"><?php echo $item['remarks_delivery'];?></textarea>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">

            </div>
            <div class="col-sm-4 col-xs-4">
                <div class="action_button pull-right">
                    <button id="button_action_save" type="button" class="btn" data-form="#save_form" data-message-confirm="Are You want Showroom to showroom transfer delivery?">Save</button>
                </div>
            </div>
            <div class="col-sm-4 col-xs-4">

            </div>
        </div>
    </div>
</form>
