<?php
defined('BASEPATH') OR exit('No direct script access allowed');

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
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DISCOUNT');?> %</label>
            </div>
            <div class="col-xs-4">
                <input type="text" id="discount_self_percentage" class="form-control text-right quantity float_type_positive" name="item[discount_self_percentage]" value="<?php echo $item['discount_self_percentage'];?>"/>
                <?php
                if(strlen($item['discount_message'])>0)
                {
                    echo $item['discount_message'];
                }
                ?>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Return Reason<span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <textarea name="item[remarks_return_requested]" class="form-control"></textarea>
            </div>
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
                    <th style="min-width: 100px;">Purchase(pkt)<br>from<br> <?php echo System_helper::display_date($item['date_start']) ; ?></th>
                    <th style="min-width: 100px;"><?php echo $CI->lang->line('LABEL_QUANTITY'); ?>(Packets)</th>
                    <th style="min-width: 100px;"><?php echo $CI->lang->line('LABEL_WEIGHT_KG'); ?></th>
                    <th style="min-width: 100px;">Price</th>
                    <th style="min-width: 100px;">Variety Discount%</th>
                    <th style="min-width: 100px;">Variety Discount</th>
                    <th style="min-width: 100px;">Actual Price</th>
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
                    <td class="text-right"><label id="total_price">0.00</label></td>
                    <td>&nbsp;</td>
                    <td class="text-right"><label id="total_discount_variety">0.00</label></td>
                    <td class="text-right"><label id="total_price_actual">0.00</label></td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="10">&nbsp;</td>
                    <td><label><?php echo $CI->lang->line('LABEL_DISCOUNT'); ?></label></td>
                    <td class="text-right"><label id="total_discount_farmer">0.00</label></td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="10">&nbsp;</td>
                    <td><label>Payable</label></td>
                    <td class="text-right"><label id="total_payable">0.00</label></td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="10">&nbsp;</td>
                    <td><label>Payable(rounded)</label></td>
                    <td class="text-right"><label id="total_payable_celling">0.00</label></td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="10">&nbsp;</td>
                    <td colspan="2">
                        <?php
                        if($item['amount_credit_limit']>0)
                        {
                            echo 'Will adjust From credit balance.';
                        }
                        else
                        {
                            echo 'Return payable amount Cash to this customer after approval';
                        }
                        ?>
                    </td>
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
                    <button id="button_action_save" type="button" class="btn" data-form="#sale_form" data-message-confirm="Are you sure to Request?">Request For Approval</button>
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
                <input class="form-control text-right price_unit_pack float_type_positive" type="text" value="0"/>
            </td>
            <td class="text-right">
                <label class="current_stock_pkt">&nbsp;</label>
            </td>
            <td class="text-right">
                <input type="text" class="form-control text-right quantity integer_type_positive" value="1"/>
            </td>
            <td class="text-right">
                <label class="weight_kg">&nbsp;</label>
            </td>
            <td class="text-right">
                <label class="price">&nbsp;</label>
            </td>
            <td class="text-right">
                <input type="text" class="form-control text-right discount_percentage_variety float_type_positive" value="0"/>
            </td>
            <td class="text-right">
                <label class="discount_variety">&nbsp;</label>
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
        var total_price=0;
        var total_discount_variety=0;
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
            var price_unit_pack=0;
            if($('#'+'price_unit_pack_'+variety_barcode).val()==parseFloat($('#'+'price_unit_pack_'+variety_barcode).val()))
            {
                price_unit_pack=parseFloat($('#'+'price_unit_pack_'+variety_barcode).val());
            }
            var price=quantity*price_unit_pack;
            total_price+=price;
            $('#'+'price_'+variety_barcode).html(number_format(price,2));
            var discount_percentage_variety=0;
            if($('#'+'discount_percentage_variety_'+variety_barcode).val()==parseFloat($('#'+'discount_percentage_variety_'+variety_barcode).val()))
            {
                discount_percentage_variety=parseFloat($('#'+'discount_percentage_variety_'+variety_barcode).val());
            }
            var discount_variety=(price*discount_percentage_variety/100);
            total_discount_variety+=discount_variety;
            $('#'+'discount_variety_'+variety_barcode).html(number_format(discount_variety,2));

            var price_actual=price-discount_variety;
            total_price_actual+=price_actual;
            $('#'+'price_actual_'+variety_barcode).html(number_format(price_actual,2));
        });
        $('#total_quantity').html(number_format(total_quantity,'0','.',''));
        $('#total_weight_kg').html(number_format(total_weight_kg,3,'.',''));
        $('#total_price').html(number_format(total_price,2));
        $('#total_discount_variety').html(number_format(total_discount_variety,2));
        $('#total_price_actual').html(number_format(total_price_actual,2));
        var discount_self_percentage=0;
        if($('#discount_self_percentage').val()==parseFloat($('#discount_self_percentage').val()))
        {
            discount_self_percentage=parseFloat($('#discount_self_percentage').val());
        }
        var total_discount_farmer=(total_price_actual*discount_self_percentage)/100;
        $('#total_discount_farmer').html(number_format(total_discount_farmer,2));
        var total_payable=total_price_actual-total_discount_farmer;
        $('#total_payable').html(number_format(total_payable,2));
        var total_payable_celling=Math.ceil(total_payable);
        $('#total_payable_celling').html(number_format(total_payable_celling,2));
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

                //val works but not set as value
                $(content_id+' .price_unit_pack').attr('value',sale_varieties_info[variety_barcode]['price_unit_pack']);
                //$(content_id+' .price_unit_pack').val(sale_varieties_info[variety_barcode]['price_unit_pack']);
                $(content_id+' .price_unit_pack').attr('id','price_unit_pack_'+variety_barcode);
                $(content_id+' .price_unit_pack').attr('name','items['+sale_varieties_info[variety_barcode]['variety_id']+']['+sale_varieties_info[variety_barcode]['pack_size_id']+'][price_unit_pack]');

                $(content_id+' .current_stock_pkt').html(sale_varieties_info[variety_barcode]['current_stock']);

                $(content_id+' .quantity').attr('id','quantity_'+variety_barcode);
                $(content_id+' .quantity').attr('name','items['+sale_varieties_info[variety_barcode]['variety_id']+']['+sale_varieties_info[variety_barcode]['pack_size_id']+'][quantity]');


                $(content_id+' .weight_kg').attr('id','weight_kg_'+variety_barcode);
                $(content_id+' .price').attr('id','price_'+variety_barcode);

                $(content_id+' .discount_percentage_variety').attr('value',sale_varieties_info[variety_barcode]['discount_percentage_variety']);
                $(content_id+' .discount_percentage_variety').attr('id','discount_percentage_variety_'+variety_barcode);
                $(content_id+' .discount_percentage_variety').attr('name','items['+sale_varieties_info[variety_barcode]['variety_id']+']['+sale_varieties_info[variety_barcode]['pack_size_id']+'][discount_percentage_variety]');

                $(content_id+' .discount_variety').attr('id','discount_variety_'+variety_barcode);
                $(content_id+' .price_actual').attr('id','price_actual_'+variety_barcode);

                var html=$(content_id).html();
                $("#container_sale_items tbody").append(html);
                $(content_id+' .quantity').removeAttr('id');
                $(content_id+' .quantity').removeAttr('name');
                $(content_id+' .price_unit_pack').removeAttr('name');
                $(content_id+' .price_unit_pack').removeAttr('id');
                $(content_id+' .weight_kg').removeAttr('id');
                $(content_id+' .price').removeAttr('id');
                $(content_id+' .discount_percentage_variety').removeAttr('name');
                $(content_id+' .discount_percentage_variety').removeAttr('id');
                $(content_id+' .discount_variety').removeAttr('id');
                $(content_id+' .price_actual').removeAttr('id');
            }
            calculate_sale_total();
            $('#variety_barcode').val('');
        }

    }
    jQuery(document).ready(function()
    {
        $(".datepicker").datepicker({dateFormat : display_date_format});
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
        $(document).off("input", "#discount_self_percentage");
        $(document).on("input", "#discount_self_percentage", function(event)
        {
            calculate_sale_total();
        });
        $(document).off("input", ".price_unit_pack");
        $(document).on("input", ".price_unit_pack", function(event)
        {
            calculate_sale_total();
        });
        $(document).off("input", ".quantity");
        $(document).on("input", ".quantity", function(event)
        {
            calculate_sale_total();
        });
        $(document).off("input", ".discount_percentage_variety");
        $(document).on("input", ".discount_percentage_variety", function(event)
        {
            calculate_sale_total();
        });


    });
</script>