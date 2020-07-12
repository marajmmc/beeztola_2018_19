<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$user=User_helper::get_user();
$CI=& get_instance();
$action_buttons=array();
$action_buttons[]=array(
    'label'=>$CI->lang->line("ACTION_BACK"),
    'href'=>site_url($CI->controller_url)
);

$action_buttons[]=array(
    'label'=>$CI->lang->line("ACTION_CANCEL"),
    'href'=>site_url($CI->controller_url.'/index/add')
);
$CI->load->view('action_buttons',array('action_buttons'=>$action_buttons));
?>
<form id="sale_form" action="<?php echo site_url($CI->controller_url.'/index/save');?>" method="post">
    <input type="hidden" name="item[outlet_id]" id="outlet_id" value="<?php echo $item['outlet_id']; ?>" />
    <input type="hidden" name="item[farmer_id]" value="<?php echo $item['farmer_id']; ?>" />
    <input type="hidden" name="item[code_scan_type]" id="code_scan_type" value="<?php echo $item['code_scan_type'];?>">
    <input type="hidden" id="system_user_token" name="system_user_token" value="<?php echo time().'_'.$user->id; ?>" />
    <div class="row widget">
        <div class="widget-header">
            <div class="title">
                <?php echo $title; ?>
            </div>
            <div class="clearfix"></div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_OUTLET_NAME');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $item['outlet_name'];?></label>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_NAME');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $item['farmer_name'];?></label>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Credit Limit</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo System_helper::get_string_amount($item['amount_credit_limit']);?></label>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Credit Balance</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo System_helper::get_string_amount($item['amount_credit_balance']);?></label>
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
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_FARMER_TYPE_NAME');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $item['farmer_type_name'];?></label>
            </div>
        </div>
        <?php
        if($item['amount_credit_limit']>0)
        {
            ?>
            <div class="row show-grid">
                <div class="col-xs-4">

                </div>
                <div class="col-sm-4 col-xs-8">

                        <a class="btn btn-primary" href="<?php echo site_url('farmer_credit_payment/index/list_payment/'.$item['farmer_id']); ?>">Make Payment</a>

                </div>

            </div>
        <?php
        }
        ?>
        <div class="clearfix"></div>
        <div class="widget-header">
            <div class="title">
                Items
            </div>
            <div class="clearfix"></div>
        </div>
        <div style="" class="row show-grid" id="crop_id_container">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_CROP_NAME');?></label>
            </div>
            <div class="col-sm-4 col-xs-4">
                <select id="crop_id" class="form-control">
                    <option value=""><?php echo $CI->lang->line('SELECT');?></option>
                </select>
            </div>
        </div>
        <div style="display: none;" class="row show-grid" id="variety_id_container">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_VARIETY_NAME');?></label>
            </div>
            <div class="col-sm-4 col-xs-4">
                <select id="variety_id" class="form-control">
                    <option value=""><?php echo $CI->lang->line('SELECT');?></option>
                </select>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo 'Variety '.$CI->lang->line('LABEL_BARCODE');?></label>
            </div>
            <div class="col-sm-4 col-xs-4">
                <input type="text" id="variety_barcode" class="form-control" value=""/>
            </div>
            <div class="col-sm-4 col-xs-4">
                <div class="action_button">
                    <button id="button_action_variety_add" type="button" class="btn"><?php echo $CI->lang->line('LABEL_ACTION1');?></button>
                </div>
            </div>
        </div>
        <div style="overflow-x: auto;" class="row show-grid" id="container_sale_items">
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th style="min-width: 100px;"><?php echo $CI->lang->line('LABEL_CROP_NAME'); ?></th>
                    <th style="min-width: 100px;"><?php echo $CI->lang->line('LABEL_CROP_TYPE_NAME'); ?></th>
                    <th style="min-width: 100px;"><?php echo $CI->lang->line('LABEL_VARIETY_NAME'); ?></th>
                    <th style="min-width: 100px;"><?php echo $CI->lang->line('LABEL_PACK_SIZE_NAME'); ?></th>
                    <th style="min-width: 100px;"><?php echo $CI->lang->line('LABEL_PRICE_PER_PACK'); ?></th>
                    <th style="min-width: 100px;"><?php echo $CI->lang->line('LABEL_CURRENT_STOCK_PKT'); ?></th>
                    <th style="min-width: 100px;"><?php echo $CI->lang->line('LABEL_QUANTITY'); ?>(Packets)</th>
                    <th style="min-width: 100px;"><?php echo $CI->lang->line('LABEL_WEIGHT_KG'); ?></th>
                    <th style="min-width: 100px;">Price</th>
                    <th style="min-width: 150px;"><?php echo $CI->lang->line('ACTION'); ?></th>
                </tr>
                </thead>
                <tbody>

                </tbody>
                <tfoot>
                <tr>
                    <td colspan="5">&nbsp;</td>
                    <td><label><?php echo $CI->lang->line('LABEL_TOTAL'); ?></label></td>
                    <td class="text-right"><label id="total_quantity">0</label></td>
                    <td class="text-right"><label id="total_weight_kg">0.000</label></td>
                    <td class="text-right"><label id="total_price_actual">0.00</label></td>
                    <td>&nbsp;</td>
                </tr>
                <tr <?php if($item['allow_discount']!=$CI->config->item('system_status_yes')){echo 'style="display: none;"';}?>>
                    <td colspan="7">&nbsp;</td>
                    <td><label><?php echo $CI->lang->line('LABEL_DISCOUNT'); ?></label></td>
                    <td class="text-right"><input id="amount_discount_self" name="item[amount_discount_self]" type="text"class="form-control text-right float_type_positive" value=""/></td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="7">&nbsp;</td>
                    <td><label>Payable</label></td>
                    <td class="text-right"><label id="total_payable">0.00</label></td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="7">&nbsp;</td>
                    <td><label>Payable(rounded)</label></td>
                    <td class="text-right"><label id="total_payable_celling">0.00</label></td>
                    <td>&nbsp;</td>
                </tr>
                <tr <?php if($item['amount_credit_limit']>0){echo 'style="display: none;"';}?>>
                    <td colspan="7">&nbsp;</td>
                    <td><label>Paid</label></td>
                    <td class="text-right"><input id="amount_paid" name="item[amount_paid]" type="text"class="form-control text-right float_type_positive" value=""/></td>
                    <td>&nbsp;</td>
                </tr>
                <tr <?php if($item['amount_credit_limit']>0){echo 'style="display: none;"';}?>>
                    <td colspan="7">&nbsp;</td>
                    <td><label>Change</label></td>
                    <td class="text-right"><label id="amount_change">&nbsp;</label></td>
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
                    <button id="button_action_save" type="button" class="btn" data-form="#sale_form" data-message-confirm="Are you sure Save?"><?php echo $CI->lang->line('ACTION_SAVE');?></button>
                </div>
            </div>
            <div class="col-sm-4 col-xs-4">

            </div>
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
                <label class="crop_type_name">&nbsp;</label>
            </td>
            <td>
                <label class="variety_name">&nbsp;</label>
            </td>
            <td>
                <label class="pack_size">&nbsp;</label>
            </td>
            <td class="text-right">
                <label class="price_unit_pack_label">&nbsp;</label>
                <input class="price_unit_pack" type="hidden" value="0"/>
            </td>
            <td class="text-right">
                <label class="current_stock_pkt">&nbsp;</label>
            </td>
            <td class="text-right">
                <input type="text"class="form-control text-right quantity integer_type_positive" value="1"/>
            </td>
            <td class="text-right">
                <label class="weight_kg">&nbsp;</label>
            </td>
            <td class="text-right">
                <label class="price_actual">&nbsp;</label>
            </td>
            <td><button type="button" class="btn btn-danger system_button_add_delete"><?php echo $CI->lang->line('ACTION_DELETE'); ?></button></td>
        </tr>
        </tbody>
    </table>
</div>
<script type="text/javascript">
    <?php
    if(sizeof($sale_varieties_info)>0)
    {
        ?>
        var sale_varieties_info=JSON.parse('<?php echo json_encode($sale_varieties_info);?>');
        <?php
    }
    else
    {
        ?>
        var sale_varieties_info={};
        <?php
    }
    ?>
    function calculate_sale_total()
    {
        var total_quantity=0;
        var total_weight_kg=0;
        var total_price_actual=0;
        $("#container_sale_items tbody .quantity").each( function( index, element )
        {
            var variety_barcode=$(this).attr('id');
            variety_barcode=variety_barcode.substr(9);
            var quantity=0;
            if($(this).val()==parseFloat($(this).val()))
            {
                quantity=parseFloat($(this).val());
            }
            total_quantity+=quantity;

            var weight_kg=quantity*sale_varieties_info[variety_barcode]['pack_size']/1000;
            total_weight_kg+=weight_kg;
            $('#'+'weight_kg_'+variety_barcode).html(number_format(weight_kg,3,'.',''));

            var price_actual=quantity*sale_varieties_info[variety_barcode]['price_unit_pack'];
            total_price_actual+=price_actual;
            $('#'+'price_actual_'+variety_barcode).html(number_format(price_actual,2));
        });
        $('#total_quantity').html(number_format(total_quantity,'0','.',''));
        $('#total_weight_kg').html(number_format(total_weight_kg,3,'.',''));

        $('#total_price_actual').html(number_format(total_price_actual,2));

        // will be calculated discounts



        var total_discount_farmer=0;
        if($('#amount_discount_self').val()==parseFloat($('#amount_discount_self').val()))
        {
            total_discount_farmer=parseFloat($('#amount_discount_self').val());
        }

        var total_payable=total_price_actual-total_discount_farmer;
        $('#total_payable').html(number_format(total_payable,2));
        var total_payable_celling=Math.ceil(total_payable);
        $('#total_payable_celling').html(number_format(total_payable_celling,2));
        var amount_paid=0;
        if($('#amount_paid').val()==parseFloat($('#amount_paid').val()))
        {
            amount_paid=parseFloat($('#amount_paid').val());
        }
        var amount_change=amount_paid-total_payable_celling;
        $('#amount_change').html(number_format(amount_change,2));
    }
    function add_variety()
    {
        var scanned_code=$('#variety_barcode').val();
        var outlet_id='<?php echo $item['outlet_id'];?>';
        if(scanned_code.length!=8)//validation of barcode length
        {
            animate_message("Invalid Barcode length.");
            return;
        }
        var variety_barcode=outlet_id.concat(scanned_code.substr(3));
        if(sale_varieties_info[variety_barcode]===undefined)
        {
            animate_message("Invalid Barcode.");
        }
        else
        {
            if(($('#'+'quantity_'+variety_barcode).length)>0)
            {
                var cur_quantity=parseFloat($('#'+'quantity_'+variety_barcode).val());
                cur_quantity=cur_quantity+1;
                $('#'+'quantity_'+variety_barcode).val(cur_quantity);
            }
            else
            {
                var content_id='#system_content_add_more table tbody';
                $(content_id+' .crop_name').html(sale_varieties_info[variety_barcode]['crop_name']);
                $(content_id+' .crop_type_name').html(sale_varieties_info[variety_barcode]['crop_type_name']);
                $(content_id+' .variety_name').html(sale_varieties_info[variety_barcode]['variety_name']);

                $(content_id+' .pack_size').html(sale_varieties_info[variety_barcode]['pack_size']);

                $(content_id+' .price_unit_pack_label').html(sale_varieties_info[variety_barcode]['price_unit_pack']);
                $(content_id+' .price_unit_pack').val(sale_varieties_info[variety_barcode]['price_unit_pack']);
                $(content_id+' .price_unit_pack').attr('name','items['+sale_varieties_info[variety_barcode]['variety_id']+']['+sale_varieties_info[variety_barcode]['pack_size_id']+'][price_unit_pack]');

                $(content_id+' .current_stock_pkt').html(sale_varieties_info[variety_barcode]['current_stock']);

                $(content_id+' .quantity').attr('id','quantity_'+variety_barcode);
                $(content_id+' .quantity').attr('name','items['+sale_varieties_info[variety_barcode]['variety_id']+']['+sale_varieties_info[variety_barcode]['pack_size_id']+'][quantity]');


                $(content_id+' .weight_kg').attr('id','weight_kg_'+variety_barcode);

                $(content_id+' .price_actual').attr('id','price_actual_'+variety_barcode);

                var html=$(content_id).html();
                $("#container_sale_items tbody").append(html);
                //$(content_id+' .pack_size').removeAttr('id');
                //$(content_id+' .pack_size_price').removeAttr('id');
                $(content_id+' .quantity').removeAttr('id');
                $(content_id+' .quantity').removeAttr('name');
                $(content_id+' .price_unit_pack').removeAttr('name');
                $(content_id+' .weight_kg').removeAttr('id');
                $(content_id+' .price_actual').removeAttr('id');
            }
            calculate_sale_total();
            $('#variety_barcode').val('');
        }

    }
    jQuery(document).ready(function()
    {
        $('#crop_id').html(get_dropdown_with_select(system_crops));
        $(document).off('change','#crop_id');
        $(document).on("change","#crop_id",function()
        {

            $('#variety_id').val('');
            $('#variety_barcode').val('');
            var crop_id=$('#crop_id').val();
            $('#variety_id_container').hide();
            if(crop_id>0)
            {
                var items=[];
                $.each( sale_varieties_info, function( key, value )
                {
                    if(value['crop_id']==crop_id)
                    {
                        items.push({'value':key, 'text':value['variety_name'].concat('- ',value['pack_size'],'gm')})
                    }

                });
                if(items.length>0)
                {
                    $('#variety_id_container').show();
                    $('#variety_id').html(get_dropdown_with_select(items));
                }

            }
        });
        $(document).off('change','#variety_id');
        $(document).on("change","#variety_id",function()
        {
            $('#variety_barcode').val($('#variety_id').val());

        });
        $(document).off("keypress", "#variety_barcode");
        $(document).on("keypress","#variety_barcode",function(event)
        {
            if(event.which == 13)
            {
                add_variety();
                return false;
            }

        });
        $(document).off("click", "#button_action_variety_add");
        $(document).on("click", "#button_action_variety_add", function(event)
        {
            add_variety();
        });
        $(document).off("click", ".system_button_add_delete");
        $(document).on("click", ".system_button_add_delete", function(event)
        {
            $(this).closest('tr').remove();
            calculate_sale_total();

        });
        $(document).off("input", ".quantity");
        $(document).on("input", ".quantity", function(event)
        {
            calculate_sale_total();
        });
        $(document).off("input", "#amount_paid");
        $(document).on("input", "#amount_paid", function(event)
        {
            calculate_sale_total();
        });
        $(document).off("input", "#amount_discount_self");
        $(document).on("input", "#amount_discount_self", function(event)
        {
            calculate_sale_total();
        });


    });
</script>