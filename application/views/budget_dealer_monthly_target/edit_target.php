<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$CI=& get_instance();
$action_buttons=array();
$action_buttons[]=array
(
    'label'=>$CI->lang->line("ACTION_BACK"),
    'href'=>site_url($CI->controller_url)
);
if((isset($CI->permissions['action2']) && ($CI->permissions['action2']==1)))
{
    $action_buttons[]=array(
        'type'=>'button',
        'label'=>$CI->lang->line("ACTION_SAVE"),
        'id'=>'button_action_save_jqx'
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
                <th colspan="5" class="text-center bg-success">Crop Wise Information</th>
            </tr>
            <tr>
                <th rowspan="2" width="2%"><?php echo $CI->lang->line('LABEL_SL_NO');?></th>
                <th rowspan="2"><?php echo $CI->lang->line('LABEL_CROP_NAME');?></th>
                <th colspan="3" class="text-center">Total Budget</th>
            </tr>
            <tr>
                <th class="text-right"><?php echo $CI->lang->line('LABEL_PACK');?></th>
                <th class="text-right"><?php echo $CI->lang->line('LABEL_KG');?></th>
                <th class="text-right">Net Price</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $serial=0;
            $quantity_budget_total=0;
            $quantity_budget_total_kg=0;
            $amount_budget_price_net=0;
            $quantity_budget_total_total=0;
            $quantity_budget_total_total_kg=0;
            $amount_budget_price_net_total=0;
            foreach($total_crops as $crop)
            {
                ++$serial;
                $quantity_budget_total=$crop['quantity_budget_total'];
                $quantity_budget_total_kg=$crop['quantity_budget_total_kg'];
                $amount_budget_price_net=$crop['amount_budget_price_net'];
                $quantity_budget_total_total+=$quantity_budget_total;
                $quantity_budget_total_total_kg+=$quantity_budget_total_kg;
                $amount_budget_price_net_total+=$amount_budget_price_net;
                ?>
                <tr>
                    <td class="text-right"><?php echo $serial;?></td>
                    <td><?php echo $crop['crop_name'];?></td>
                    <td class="text-right"><?php echo System_helper::get_string_quantity($quantity_budget_total);?></td>
                    <td class="text-right"><?php echo System_helper::get_string_kg($quantity_budget_total_kg);?></td>
                    <td class="text-right"><?php echo System_helper::get_string_amount($amount_budget_price_net);?></td>
                </tr>
            <?php
            }
            ?>
            </tbody>
            <tfoot>
            <tr>
                <th class="text-right" colspan="2"><?php echo $CI->lang->line('LABEL_TOTAL');?></th>
                <th class="text-right"><?php echo System_helper::get_string_quantity($quantity_budget_total_total);?></th>
                <th class="text-right"><?php echo System_helper::get_string_kg($quantity_budget_total_total_kg);?></th>
                <th class="text-right"><?php echo System_helper::get_string_amount($amount_budget_price_net_total);?></th>
            </tr>
            </tfoot>
        </table>
    </div>
    <div class="clearfix"></div>
    <div class="col-md-12">
        <table class="table table-bordered table-responsive system_table_details_view">
            <thead>
            <tr>
                <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_OUTLET');?></label></th>
                <th class=" header_value"><label class="control-label"><?php echo $item['outlet_name'];?></label></th>
                <th colspan="2">&nbsp;</th>
            </tr>
            <tr>
                <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_YEAR');?></label></th>
                <th class=" header_value"><label class="control-label"><?php echo date("Y", mktime(0, 0, 0,1,1, $item['year']));;?></label></th>
                <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_MONTH');?></label></th>
                <th class=" header_value"><label class="control-label"><?php echo date("F", mktime(0, 0, 0,  $item['month'],1, 2000));;?></label></th>
            </tr>
            <tr>
                <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_CREATED_BY');?></label></th>
                <th class=" header_value"><label class="control-label"><?php echo $users[$item['user_created']]['name'];?></label></th>
                <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DATE_CREATED_TIME');?></label></th>
                <th class=""><label class="control-label"><?php echo System_helper::display_date_time($item['date_created']);?></label></th>
            </tr>
            <tr>
                <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_STATUS_FORWARD');?></label></th>
                <th class=" header_value"><label class="control-label"><?php echo $item['status_forward'];?></label></th>
                <th colspan="2">&nbsp;</th>
            </tr>
            <tr>
                <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_FORWARDED_BY');?></label></th>
                <th class=" header_value"><label class="control-label"><?php echo $users[$item['user_forwarded']]['name'];?></label></th>
                <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DATE_FORWARDED_TIME');?></label></th>
                <th class=""><label class="control-label"><?php echo System_helper::display_date_time($item['date_forwarded']);?></label></th>
            </tr>
            <tr>
                <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_REMARKS_FORWARD');?></label></th>
                <th class=" header_value"><label class="control-label"><?php echo nl2br($item['remarks_forward']);?></label></th>
                <th colspan="2">&nbsp;</th>
            </tr>
            </thead>
        </table>
    </div>
    <div class="clearfix"></div>
    <div style="font-size: 12px;margin-top: -10px;font-style: italic; color: red;" class="row show-grid">
        <div class="col-xs-4"></div>
        <div class="col-sm-4 col-xs-8 text-center">
            <strong>Note:</strong> Budget quantity in packet. Give input & click outside the grid.
        </div>
    </div>
    <div class="row show-grid">
        <div class="row widget">
            <div class="col-xs-12" id="system_jqx_container"></div>
        </div>
    </div>
</div>
<!-- jqx grid generated form -->
<form id="save_form_jqx" action="<?php echo site_url($CI->controller_url.'/index/save_target');?>" method="post">
    <input type="hidden" id="id" name="id" value="<?php echo $item['id']; ?>" />
    <input type="hidden" id="system_save_new_status" name="system_save_new_status" value="0" />
    <div id="jqx_inputs"> </div>
</form>
<!-- jqx grid generated form -->
<?php
$options=array(
    'id'=>$item['id'],
    'outlet_id'=>$item['outlet_id']
);
?>
<script type="text/javascript">
    $(document).ready(function ()
    {
        system_preset({controller:'<?php echo $CI->router->class; ?>'});

        $(document).off('click', '#button_action_save_jqx');
        $(document).on("click", "#button_action_save_jqx", function(event)
        {
            $('#save_form_jqx #jqx_inputs').html('');
            var data=$('#system_jqx_container').jqxGrid('getrows');

            for(var i=0;i<data.length;i++)
            {
                //console.log(data[i]['quantity_budget_target_total']);
                $('#save_form_jqx  #jqx_inputs').append('<input type="hidden" name="items['+data[i]['variety_id']+']['+data[i]['pack_size_id']+'][quantity_budget_target_total]" value="'+data[i]['quantity_budget_target_total']+'">');
            }
            var sure = confirm('<?php echo $CI->lang->line('MSG_CONFIRM_SAVE'); ?>');
            if(sure)
            {
                //$("#save_form_jqx").submit();
                //return false;
                var form=$("#save_form_jqx");
                $.ajax({
                    url:form.attr("action"),
                    type: 'POST',
                    datatype: "JSON",
                    data:form.serialize(),
                    success: function (data, status)
                    {

                    },
                    error: function (xhr, desc, err)
                    {
                        console.log("error");

                    }
                });
            }

        });

        var url = "<?php echo site_url($CI->controller_url.'/index/get_variety/');?>";
        // prepare the data
        var source =
        {
            dataType: "json",
            dataFields: [
                <?php
                 foreach($system_preference_items as $key=>$item)
                 {
                     if(($key=='crop_name')||($key=='crop_type_name')||($key=='variety_id')||($key=='variety_name')||($key=='pack_size_id')||($key=='pack_size'))
                     {
                         ?>
                        { name: '<?php echo $key ?>', type: 'string' },
                        <?php
                     }
                     else
                     {
                        ?>
                        { name: '<?php echo $key ?>', type: 'number' },
                        <?php
                    }
                 }
                ?>
            ],
            id: 'id',
            url: url,
            type: 'POST',
            data:JSON.parse('<?php echo json_encode($options);?>')
        };

        var dataAdapter = new $.jqx.dataAdapter(source);
        var header_render=function (text, align)
        {
            var words = text.split(" ");
            var label=words[0];
            var count=words[0].length;
            for (i = 1; i < words.length; i++)
            {
                if((count+words[i].length)>10)
                {
                    label=label+'</br>'+words[i];
                    count=words[i].length;
                }
                else
                {
                    label=label+' '+words[i];
                    count=count+words[i].length;
                }

            }
            return '<div style="margin: 5px;">'+label+'</div>';
        };
        var cellsrenderer = function(row, column, value, defaultHtml, columnSettings, record)
        {
            var element = $(defaultHtml);
            var price_net=parseFloat(record['price_net']);

            if(column=='quantity_budget_total')
            {
                var total_quantity=0;
                <?php
                foreach($dealers as $dealer)
                {
                ?>
                if(!isNaN(parseFloat(record['<?php echo 'quantity_budget_'.$dealer['farmer_id'];?>'])))
                {
                    total_quantity+=parseFloat(record['<?php echo 'quantity_budget_'.$dealer['farmer_id'];?>']);
                }
                <?php
                }
                ?>
                if(total_quantity==0)
                {
                    element.html('');
                }
                else
                {
                    element.html(total_quantity);
                }
            }
            else if(column=='amount_price_total')
            {
                var total_quantity=0;
                <?php
                foreach($dealers as $dealer)
                {
                ?>
                if(!isNaN(parseFloat(record['<?php echo 'quantity_budget_'.$dealer['farmer_id'];?>'])))
                {
                    total_quantity+=parseFloat(record['<?php echo 'quantity_budget_'.$dealer['farmer_id'];?>']);
                }
                <?php
                }
                ?>
                if((total_quantity==0)||(record['amount_price_net']==0))
                {
                    element.html('');
                }
                else
                {
                    element.html(get_string_amount(total_quantity*record['amount_price_net']));
                }
            }
            else if(column=='quantity_budget_target_total')
            {

                element.css({'margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
                element.html('<div class="jqxgrid_input">'+value+'</div>');
                //console.log(value);
            }
            else if(column=='amount_price_total_target')
            {
                var total_quantity_target=0;
                total_quantity_target=record['quantity_budget_target_total'];
                if((total_quantity_target==0)||(record['amount_price_net']==0))
                {
                    element.html('');
                }
                else
                {
                    element.html(get_string_amount(total_quantity_target*record['amount_price_net']));
                }

            }
            else if(column=='current_stock_pkt')
            {
                if(value==0)
                {
                    element.html('');
                }
            }
            else if(column=='current_stock_kg')
            {
                if(value==0)
                {
                    element.html('');
                }
                else
                {
                    element.html(get_string_kg(value))
                }
            }
            return element[0].outerHTML;
        };
        // create jqxgrid.
        $("#system_jqx_container").jqxGrid(
            {
                width: '100%',
                height: '350px',
                filterable: true,
                sortable: true,
                showfilterrow: true,
                source: dataAdapter,
                columnsresize: true,
                columnsreorder: true,
                enablebrowserselection: true,
                altrows: true,
                rowsheight: 35,
                columnsheight: 70,
                editable:true,
                columns: [
                    { text: '<?php echo $CI->lang->line('LABEL_CROP_NAME'); ?>', dataField: 'crop_name',width:'100', filtertype:'list',pinned:true,editable:false},
                    { text: '<?php echo $CI->lang->line('LABEL_CROP_TYPE_NAME'); ?>', dataField: 'crop_type_name',width:'100', filtertype:'list',pinned:true,renderer: header_render,cellsrenderer: cellsrenderer,editable:false},
                    { text: '<?php echo $CI->lang->line('LABEL_VARIETY_NAME'); ?>', dataField: 'variety_name',width:'150', filtertype:'list',pinned:true,renderer: header_render,cellsrenderer: cellsrenderer,editable:false},
                    { text: '<?php echo $CI->lang->line('LABEL_PACK_SIZE'); ?>', dataField: 'pack_size',width:'50', filtertype:'list',pinned:true,renderer: header_render,cellsrenderer: cellsrenderer,cellsalign: 'right',editable:false},
                    { text: 'Current Net Price', dataField: 'amount_price_net',width:'80', filterable:false,pinned:true,renderer: header_render,cellsrenderer: cellsrenderer,cellsalign: 'right',editable:false},
                    { text: 'Budgeted Total Quantity', dataField: 'quantity_budget_total',width:'80', filterable:false,pinned:true,renderer: header_render,cellsrenderer: cellsrenderer,cellsalign: 'right',editable:false},
                    { text: 'Budgeted <?php echo $CI->lang->line('LABEL_TOTAL_PRICE'); ?>', dataField: 'amount_price_total',width:'130', filterable:false,pinned:true,renderer: header_render,cellsrenderer: cellsrenderer,cellsalign: 'right',editable:false},
                    { text: 'Targeted Total Quantity', dataField: 'quantity_budget_target_total',width:'80', filterable:false,pinned:true,renderer: header_render,cellsrenderer: cellsrenderer,cellsalign: 'right', columntype: 'custom', editable:true,
                        cellbeginedit: function (row)
                        {
                            var selectedRowData = $('#system_jqx_container').jqxGrid('getrowdata', row);//only last selected
                            //return selectedRowData['editable_<?php echo $dealer['farmer_id'];?>'];
                            return true;
                        },
                        initeditor: function (row, cellvalue, editor, celltext, pressedkey)
                        {
                            editor.html('<div style="margin: 0px;width: 100%;height: 100%;padding: 5px;"><input style="z-index: 1 !important;" type="text" value="'+cellvalue+'" class="jqxgrid_input integer_type_positive"><div>');
                        },
                        geteditorvalue: function (row, cellvalue, editor)
                        {
                            // return the editor's value.
                            var value=editor.find('input').val();
                            var selectedRowData = $('#system_jqx_container').jqxGrid('getrowdata', row);
                            return editor.find('input').val();
                        }
                    },
                    { text: 'Targeted <?php echo $CI->lang->line('LABEL_TOTAL_PRICE'); ?>', dataField: 'amount_price_total_target',width:'130', filterable:false,pinned:true,renderer: header_render,cellsrenderer: cellsrenderer,cellsalign: 'right',editable:false},
                    { text: '<?php echo $CI->lang->line('LABEL_CURRENT_STOCK_PKT'); ?>', dataField: 'current_stock_pkt',width:'80', filterable: false,pinned:true,renderer: header_render,cellsrenderer: cellsrenderer,cellsalign: 'right',editable:false},
                    { text: '<?php echo $CI->lang->line('LABEL_CURRENT_STOCK_KG'); ?>', dataField: 'current_stock_kg',width:'80', filterable: false,pinned:true,renderer: header_render,cellsrenderer: cellsrenderer,cellsalign: 'right',editable:false},
                    <?php
                    $serial=0;
                    foreach($dealers as $dealer)
                    {
                    ++$serial;
                    ?>
                    { text: '<?php echo $serial.'. '.$dealer['farmer_name']?>',renderer: header_render, dataField: 'quantity_budget_<?php echo $dealer['farmer_id']?>',width:'100', filterable:false,cellsalign: 'right',editable:false,cellsrenderer: cellsrenderer},
                    <?php
                    }
                    ?>
                ]
            });
    });
</script>