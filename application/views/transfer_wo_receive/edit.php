<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$CI=& get_instance();
$action_buttons=array();
$action_buttons[]=array
(
    'label'=>$CI->lang->line("ACTION_BACK"),
    'href'=>site_url($CI->controller_url)
);
/*if((isset($CI->permissions['action1']) && ($CI->permissions['action1']==1)) || (isset($CI->permissions['action2']) && ($CI->permissions['action2']==1)))
{
    $action_buttons[]=array
    (
        'type'=>'button',
        'label'=>$CI->lang->line("ACTION_SAVE"),
        'id'=>'button_action_save',
        'data-form'=>'#save_form'
    );
}*/
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
                    <th class=""><label class="control-label"><?php echo Barcode_helper::get_barcode_transfer_warehouse_to_outlet($item['id']);?></label></th>
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
                    <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DATE_DELIVERY');?></label></th>
                    <th class=" header_value"><label class="control-label"><?php echo $item['date_delivery']?System_helper::display_date($item['date_delivery']):System_helper::display_date(time());?></label></th>
                    <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DISTRICT_NAME');?></label></th>
                    <th class=" header_value"><label class="control-label"><?php echo $item['district_name'];?></label></th>
                </tr>
                <tr>
                    <th colspan="2">&nbsp;</th>
                    <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_OUTLET_NAME');?></label></th>
                    <th class=" header_value"><label class="control-label"><?php echo $item['outlet_name'];?></label></th>
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
                        <th colspan="21" class="text-center success">Variety Receive Items Information</th>
                    </tr>
                    <tr>
                        <th rowspan="2" style="width: 200px;"><?php echo $CI->lang->line('LABEL_CROP_NAME'); ?></th>
                        <th rowspan="2" style="width: 150px;"><?php echo $CI->lang->line('LABEL_CROP_TYPE_NAME'); ?></th>
                        <th rowspan="2" style="width: 150px;"><?php echo $CI->lang->line('LABEL_VARIETY_NAME'); ?></th>
                        <th rowspan="2" class="text-right" style="width: 150px;"><?php echo $CI->lang->line('LABEL_PACK_SIZE'); ?></th>
                        <th colspan="2" class="text-center" style="width: 300px;"><?php echo $CI->lang->line('LABEL_CURRENT_STOCK'); ?></th>
                        <th colspan="2" class="text-center" style="width: 300px;"><?php echo $CI->lang->line('LABEL_QUANTITY_APPROVE'); ?></th>
                        <th colspan="2" class="text-center" style="width: 150px;"><?php echo $CI->lang->line('LABEL_QUANTITY_RECEIVE'); ?></th>
                        <th colspan="2" class="text-center" style="width: 150px;">New Stock<?php //echo $CI->lang->line('LABEL_CURRENT_STOCK'); ?></th>
                    </tr>
                    <tr>
                        <th style="width: 150px;" class="text-right"><?php echo $CI->lang->line('LABEL_PACK');?></th>
                        <th style="width: 150px;" class="text-right"><?php echo $CI->lang->line('LABEL_KG');?></th>
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

                    $quantity_receive=0;
                    $quantity_total_receive=0;
                    $quantity_total_receive_kg=0;

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

                        $quantity_receive=$value['quantity_receive'];
                        $quantity_receive_kg=(($quantity_receive*$value['pack_size'])/1000);
                        $quantity_total_receive+=$quantity_receive;
                        $quantity_total_receive_kg+=$quantity_receive_kg;

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

                        $stock_quantity_new=($stock_current+$quantity_receive);
                        $stock_quantity_new_kg=($stock_current_kg+$quantity_receive_kg);
                        $stock_quantity_total_new+=$stock_quantity_new;
                        $stock_quantity_total_new_kg+=$stock_quantity_new_kg;

                        ?>
                        <tr id="item_rows_<?php echo $index+1;?>" class='<?php if($quantity_approve!=$quantity_receive){echo 'quantity_exist_warning';}?>'>
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
                            <td>
                                <input type="text" value="<?php echo $quantity_receive; ?>" id="quantity_receive_<?php echo $index+1;?>" data-current-id="<?php echo $index+1;?>" name="items[<?php echo $index+1;?>][quantity_receive]" class="form-control integer_type_positive quantity_receive" />
                            </td>
                            <td class="text-right">
                                <label class="" id="quantity_receive_kg_<?php echo $index+1;?>"> <?php echo number_format($quantity_receive_kg,3,'.','');?> </label>
                            </td>
                            <td class="text-right">
                                <label class="control-label stock_quantity_new " id="stock_quantity_new_<?php echo $index+1;?>" data-current-id="<?php echo $index+1;?>">
                                    <?php echo $stock_quantity_new; ?>
                                </label>
                            </td>
                            <td class="text-right">
                                <label class="control-label stock_quantity_new_kg " id="stock_quantity_new_kg_<?php echo $index+1;?>" data-current-id="<?php echo $index+1;?>">
                                    <?php echo number_format($stock_quantity_new_kg,3,'.',''); ?>
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
                        <th class="text-right"><label class="control-label" id="quantity_total_receive"> <?php echo $quantity_total_receive;?></label></th>
                        <th class="text-right"><label class="control-label" id="quantity_total_receive_kg"> <?php echo number_format($quantity_total_receive_kg,3,'.','');?></label></th>
                        <th class="text-right"><label class="control-label" id="stock_quantity_total_new"> <?php echo $stock_quantity_total_new;?></label></th>
                        <th class="text-right"><label class="control-label" id="stock_quantity_total_new_kg"> <?php echo number_format($stock_quantity_total_new_kg,3,'.','');?></label></th>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <div class="clearfix"></div>
        <div class="widget-header">
            <div class="title">
                Courier Information
            </div>
            <div class="clearfix"></div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Challan No:</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $item['challan_no'];?></label>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DATE_CHALLAN');?>:</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo System_helper::display_date($item['date_challan']);?></label>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Courier Name:</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $item['courier_name'];?></label>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Receive Branch (Place):</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $item['place_destination'];?></label>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Remarks for Courier:</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $item['remarks_couriers'];?></label>
            </div>
        </div>
        <div class="widget-header">
            <div class="title">
                Receive Confirmation
            </div>
            <div class="clearfix"></div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_RECEIVED');?> <span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <select id="status_receive" class="form-control" name="item[status_receive]">
                    <option value=""><?php echo $CI->lang->line('SELECT');?></option>
                    <option value="<?php echo $this->config->item('system_status_received')?>"><?php echo $this->config->item('system_status_received')?></option>
                </select>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_REMARKS_RECEIVE');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <textarea name="item[remarks_receive_forward]" id="remarks_receive_forward" class="form-control"><?php echo $item['remarks_receive_forward'];?></textarea>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">

            </div>
            <div class="col-sm-4 col-xs-4">
                <div class="action_button pull-right">
                    <button id="button_action_save" type="button" class="btn" data-form="#save_form" data-message-confirm="Are You Sure HQ to Outlet Receive TO?">Save</button>
                </div>
            </div>
            <div class="col-sm-4 col-xs-4">

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
        $(document).off('input', '#items_container .quantity_receive');
        $(document).on('input','#items_container .quantity_receive',function()
        {
            var current_id=$(this).attr('data-current-id');
            var quantity_receive_kg=0;
            var stock_quantity_new=0;
            var stock_quantity_new_kg=0;

            var quantity_approve = parseFloat($('#quantity_approve_'+current_id).html().replace(/,/g,''));
            if(isNaN(quantity_approve))
            {
                quantity_approve=0;
            }
            var quantity_receive=parseFloat($(this).val());
            if(isNaN(quantity_receive))
            {
                quantity_receive=0;
            }
            var stock_current = parseFloat($('#stock_current_'+current_id).html().replace(/,/g,''));
            if(isNaN(stock_current))
            {
                stock_current=0;
            }
            var stock_current_kg = parseFloat($('#stock_current_kg_'+current_id).html().replace(/,/g,''));
            if(isNaN(stock_current_kg))
            {
                stock_current_kg=0;
            }
            var pack_size=parseFloat($("#pack_size_id_"+current_id).attr('data-pack-size-name'));
            quantity_receive_kg=parseFloat((pack_size*quantity_receive)/1000);
            if(isNaN(quantity_receive_kg))
            {
                quantity_receive_kg=0;
            }

            stock_quantity_new=(stock_current+quantity_receive);
            stock_quantity_new_kg=(stock_current_kg+quantity_receive_kg);

            $("#quantity_receive_kg_"+current_id).html(number_format(quantity_receive_kg,3,'.',''));
            $("#stock_quantity_new_"+current_id).html(stock_quantity_new);
            $("#stock_quantity_new_kg_"+current_id).html(number_format(stock_quantity_new_kg,3,'.',''));

            $('#item_rows_'+current_id).removeClass('quantity_exist_warning');
            if(quantity_approve!=quantity_receive)
            {
                $('#item_rows_'+current_id).addClass('quantity_exist_warning');
            }
            calculate_total();
        });

        function calculate_total()
        {
            var quantity_total_receive=0;
            var quantity_total_receive_kg=0;

            var stock_quantity_total_new=0;
            var stock_quantity_total_new_kg=0;

            $('#items_container .quantity_receive').each(function(index, element)
            {
                var current_id=parseInt($(this).attr('data-current-id'));
                var quantity_receive=parseFloat($(this).val());
                if(isNaN(quantity_receive))
                {
                    quantity_receive=0;
                }
                quantity_total_receive+=quantity_receive;
                var quantity_receive_kg=parseFloat($('#quantity_receive_kg_'+current_id).html().replace(/,/g,''));
                if(isNaN(quantity_receive_kg))
                {
                    quantity_receive_kg=0;
                }
                quantity_total_receive_kg+=quantity_receive_kg;

                var stock_quantity_new=parseFloat($('#stock_quantity_new_'+current_id).html().replace(/,/g,''));
                if(isNaN(stock_quantity_new))
                {
                    stock_quantity_new=0;
                }
                stock_quantity_total_new+=stock_quantity_new;

                var stock_quantity_new_kg=parseFloat($('#stock_quantity_new_kg_'+current_id).html().replace(/,/g,''));
                if(isNaN(stock_quantity_new_kg))
                {
                    stock_quantity_new_kg=0;
                }
                stock_quantity_total_new_kg+=stock_quantity_new_kg;
            });
            $('#quantity_total_receive').html(quantity_total_receive);
            $('#quantity_total_receive_kg').html(number_format((quantity_total_receive_kg),3,'.',''));

            $('#stock_quantity_total_new').html(stock_quantity_total_new);
            $('#stock_quantity_total_new_kg').html(number_format((stock_quantity_total_new_kg),3,'.',''));
        }
    });
</script>