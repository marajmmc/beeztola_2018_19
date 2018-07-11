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

?>

<form id="save_form" action="<?php echo site_url($CI->controller_url.'/index/save');?>" method="post">
    <input type="hidden" id="id" name="id" value="<?php echo $item['id']; ?>" />
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
                <thead>
                <tr>
                    <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_ID');?></label></th>
                    <th class=""><label class="control-label"><?php echo Barcode_helper::get_barcode_transfer_outlet_to_warehouse($item['id']);?></label></th>
                    <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DIVISION_NAME');?></label></th>
                    <th class=" header_value"><label class="control-label"><?php echo $item['division_name'];?></label></th>
                </tr>
                <tr>
                    <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DATE_REQUEST');?></label></th>
                    <th class=" header_value"><label class="control-label"><?php echo System_helper::display_date($item['date_request']);?></label></th>
                    <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_ZONE_NAME');?></label></th>
                    <th class=" header_value"><label class="control-label"><?php echo $item['zone_name'];?></label></th>
                </tr>
                <tr>
                    <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DATE_APPROVE');?></label></th>
                    <th class=" header_value"><label class="control-label"><?php echo System_helper::display_date($item['date_approve']);?></label></th>
                    <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_TERRITORY_NAME');?></label></th>
                    <th class=" header_value"><label class="control-label"><?php echo $item['territory_name'];?></label></th>
                </tr>
                <tr>
                    <th colspan="2">&nbsp;</th>
                    <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DISTRICT_NAME');?></label></th>
                    <th class=" header_value"><label class="control-label"><?php echo $item['district_name'];?></label></th>
                </tr>
                <tr>
                    <th colspan="2">&nbsp;</th>
                    <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_OUTLET_NAME_SOURCE');?></label></th>
                    <th class=" header_value"><label class="control-label"><?php echo $item['outlet_name_source'];?></label></th>
                </tr>
                <tr>
                    <th colspan="2">&nbsp;</th>
                    <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_OUTLET_NAME_DESTINATION');?></label></th>
                    <th class=" header_value"><label class="control-label"><?php echo $item['outlet_name_destination'];?></label></th>
                </tr>
                </thead>
            </table>
        </div>
        <div class="clearfix"></div>
        <div class="row show-grid">
            <div style="overflow-x: auto;" class="row show-grid">
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th colspan="21" class="text-center success"><?php echo $CI->lang->line('LABEL_ORDER_ITEMS');?></th>
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
                                    echo System_helper::get_string_quantity($stock_current);
                                    ?>
                                </label>
                            </td>
                            <td class="text-right">
                                <label class="control-label stock_current_kg " id="stock_current_kg_<?php echo $index+1;?>" data-current-id="<?php echo $index+1;?>">
                                    <?php
                                    echo System_helper::get_string_kg($stock_current_kg);
                                    ?>
                                </label>
                            </td>
                            <td class="text-right">
                                <label class="quantity_approve " id="quantity_approve_<?php echo $index+1;?>"><?php echo System_helper::get_string_quantity($quantity_approve); ?></label>
                            </td>
                            <td class="text-right">
                                <label class="quantity_approve_kg " id="quantity_approve_kg_<?php echo $index+1;?>"> <?php echo System_helper::get_string_kg($quantity_approve_kg);?> </label>
                            </td>
                            <td class="text-right">
                                <label class="control-label stock_quantity_new " id="stock_quantity_new_<?php echo $index+1;?>" data-current-id="<?php echo $index+1;?>">
                                    <?php
                                    echo System_helper::get_string_quantity($stock_quantity_new);
                                    ?>
                                </label>
                            </td>
                            <td class="text-right">
                                <label class="control-label stock_quantity_new_kg " id="stock_quantity_new_kg_<?php echo $index+1;?>" data-current-id="<?php echo $index+1;?>">
                                    <?php
                                    echo System_helper::get_string_kg($stock_quantity_new_kg);
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
                        <th class="text-right"><label class="control-label" id="stock_current_quantity_total"> <?php echo System_helper::get_string_quantity($stock_current_quantity_total);?></label></th>
                        <th class="text-right"><label class="control-label" id="stock_current_quantity_total_kg"> <?php echo System_helper::get_string_kg($stock_current_quantity_total_kg);?></label></th>
                        <th class="text-right"><label class="control-label" id="quantity_total_approve"> <?php echo System_helper::get_string_quantity($quantity_total_approve);?></label></th>
                        <th class="text-right"><label class="control-label" id="quantity_total_approve_kg"> <?php echo System_helper::get_string_kg($quantity_total_approve_kg);?></label></th>
                        <th class="text-right"><label class="control-label" id="stock_quantity_total_new"> <?php echo System_helper::get_string_quantity($stock_quantity_total_new);?></label></th>
                        <th class="text-right"><label class="control-label" id="stock_quantity_total_new_kg"> <?php echo System_helper::get_string_kg($stock_quantity_total_new_kg);?></label></th>
                    </tr>
                    </tfoot>
                </table>
            </div>
            <div class="row show-grid">
                <div class="col-xs-4">
                    <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_REMARKS_CHALLAN');?></label>
                </div>
                <div class="col-sm-4 col-xs-8">
                    <textarea name="item[remarks_challan]" id="remarks_challan" class="form-control"><?php echo $item['remarks_challan'];?></textarea>
                </div>
            </div>
        </div>
        <div class="clearfix"></div>
        <div class="widget-header">
            <div class="title">
                Delivery & Courier Information
            </div>
            <div class="clearfix"></div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DATE_DELIVERY');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <?php
                if($courier['date_delivery']>0)
                {
                    $date_courier_delivery=System_helper::display_date($courier['date_delivery']);
                }
                else
                {
                    $date_courier_delivery='';
                }
                ?>
                <input type="text" name="courier[date_delivery]" id="date_delivery" class="form-control datepicker" value="<?php echo $date_courier_delivery;?>" readonly="readonly" />
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DATE_CHALLAN');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <input type="text" name="courier[date_challan]" id="date_challan" class="form-control datepicker" value="<?php echo $courier['date_challan']?System_helper::display_date($courier['date_challan']):'';?>" readonly="readonly" />
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Challan No</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <input type="text" name="courier[challan_no]" id="challan_no" class="form-control" value="<?php echo $courier['challan_no'];?>" />
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Courier Name</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <select id="courier_id" class="form-control" name="courier[courier_id]" >
                    <option value=""><?php echo $CI->lang->line('SELECT');?></option>
                    <?php
                    foreach($couriers as $row)
                    {
                        ?>
                        <option value="<?php echo $row['id'];?>" <?php if($row['id']==$courier['courier_id']){echo "selected='selected'";}?>><?php echo $row['name'];?></option>
                    <?php
                    }
                    ?>
                </select>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Courier Tracing No</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <input type="text" name="courier[courier_tracing_no]" id="courier_tracing_no" class="form-control" value="<?php echo $courier['courier_tracing_no'];?>" />
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Booking Branch (Place)</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <textarea name="courier[place_booking_source]" id="place_booking_source" class="form-control"><?php echo $courier['place_booking_source'];?></textarea>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Receive Branch (Place)</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <textarea name="courier[place_destination]" id="place_destination" class="form-control"><?php echo $courier['place_destination'];?></textarea>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Booking Date</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <input type="text" name="courier[date_booking]" id="date_booking" class="form-control datepicker" value="<?php echo $courier['date_booking']?System_helper::display_date($courier['date_booking']):'';?>" readonly="readonly" />
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_REMARKS_COURIER');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <textarea name="courier[remarks]" id="remarks" class="form-control"><?php echo $courier['remarks'];?></textarea>
            </div>
        </div>
    </div>
</form>
<style>
    .quantity_exist_warning
    {
        background-color: red !important;
        color: #FFFFFF;
    }
</style>
<script>
    $(document).ready(function()
    {
        system_preset({controller:'<?php echo $CI->router->class; ?>'});
        $(".datepicker").datepicker({dateFormat : display_date_format});
    });
</script>