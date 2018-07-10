<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$CI = & get_instance();
if((isset($CI->permissions['action1']) && ($CI->permissions['action1']==1))||(isset($CI->permissions['action2']) && ($CI->permissions['action2']==1)))
{
    $action_buttons[]=array(
        'type'=>'button',
        'label'=>$CI->lang->line("ACTION_SAVE"),
        'id'=>'button_action_save_jqx'
    );
}
$CI->load->view('action_buttons',array('action_buttons'=>$action_buttons));

?>
<form id="save_form_jqx" action="<?php echo site_url($CI->controller_url.'/index/save');?>" method="post">
    <input type="hidden" name="item[outlet_id]" value="<?php echo $options['outlet_id']; ?>" />
    <input type="hidden" name="item[year]" value="<?php echo $options['year']; ?>" />
    <input type="hidden" name="item[month]" value="<?php echo $options['month']; ?>" />
    <input type="hidden" name="item[crop_id]" value="<?php echo $options['crop_id']; ?>" />
    <div id="jqx_inputs">
    </div>
</form>
<div style="font-size: 12px;margin-top: -10px;font-style: italic; color: red;" class="row show-grid">
    <div class="col-xs-4"></div>
    <div class="col-sm-4 col-xs-8 text-center">
        <strong>Note:</strong> Budget input will be in packet.
    </div>
</div>
<div class="row widget">
    <div class="col-xs-12" id="system_jqx_container"></div>
</div>
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
                $('#save_form_jqx  #jqx_inputs').append('<input type="hidden" name="items['+data[i]['variety_id']+']['+data[i]['pack_size_id']+'][amount_price_net]" value="'+data[i]['amount_price_net']+'">');
                <?php
                foreach($dealers as $dealer)
                {
                ?>
                $('#save_form_jqx  #jqx_inputs').append('<input type="hidden" name="items['+data[i]['variety_id']+']['+data[i]['pack_size_id']+'][quantity_budget][<?php echo $dealer['farmer_id']?>]" value="'+data[i]['quantity_budget_<?php echo $dealer['farmer_id']?>']+'">');
                <?php
                }
                ?>

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
                        if(data.status_save=='<?php echo $this->lang->line("MSG_SAVED_SUCCESS")?>')
                        {
                            $("#crop_id").val("");
                            $("#system_report_container").html("");
                        }
                    },
                    error: function (xhr, desc, err)
                    {
                        console.log("error");

                    }
                });
            }
        });

        var url = "<?php echo site_url($CI->controller_url.'/index/get_items_add_edit/');?>";
        // prepare the data
        var source =
        {
            dataType: "json",
            dataFields: [
                <?php
                 foreach($system_preference_items as $key=>$item)
                 {
                     if(($key=='crop_type_name')||($key=='variety_id')||($key=='variety_name')||($key=='pack_size_id')||($key=='pack_size'))
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
            else if(column.substr(0,6)=='amount')
            {
                if(value==0)
                {
                    element.html('');
                }
                else
                {
                    element.html(get_string_amount(value));
                }
            }
            else if(column.substr(0,16)=='quantity_budget_')
            {
                if(value==0)
                {
                    element.css({'margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
                }
                else
                {
                    element.css({'margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
                }
                if(record['editable_'+column.substr(16)])
                {
                    element.html('<div class="jqxgrid_input">'+value+'</div>');
                }
                else
                {
                    element.html(value);
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
                    { text: '<?php echo $CI->lang->line('LABEL_CROP_TYPE_NAME'); ?>', dataField: 'crop_type_name',width:'100', filtertype:'list',pinned:true,renderer: header_render,cellsrenderer: cellsrenderer,editable:false},
                    { text: '<?php echo $CI->lang->line('LABEL_VARIETY_NAME'); ?>', dataField: 'variety_name',width:'150', filtertype:'list',pinned:true,renderer: header_render,cellsrenderer: cellsrenderer,editable:false},
                    { text: '<?php echo $CI->lang->line('LABEL_PACK_SIZE'); ?>', dataField: 'pack_size',width:'50', filtertype:'list',pinned:true,renderer: header_render,cellsrenderer: cellsrenderer,cellsalign: 'right',editable:false},
                    { text: '<?php echo $CI->lang->line('LABEL_PRICE_UNIT_NET'); ?>', dataField: 'amount_price_net',width:'80',filterable: false,pinned:true,renderer: header_render,cellsrenderer: cellsrenderer,cellsalign: 'right',editable:false},
                    { text: '<?php echo $CI->lang->line('LABEL_QUANTITY_BUDGET_TOTAL_PKT'); ?>', dataField: 'quantity_budget_total',width:'80',filterable: false,pinned:true,renderer: header_render,cellsrenderer: cellsrenderer,cellsalign: 'right',editable:false},
                    { text: '<?php echo $CI->lang->line('LABEL_AMOUNT_BUDGETED_TOTAL_NET'); ?>', dataField: 'amount_price_total',width:'130',filterable: false,pinned:true,renderer: header_render,cellsrenderer: cellsrenderer,cellsalign: 'right',editable:false},
                    <?php
                    $serial=0;
                    foreach($dealers as $dealer)
                    {
                    ++$serial;
                    ?>
                    { text: '<?php echo $serial.'. '.$dealer['farmer_name']?>',renderer: header_render,datafield: 'quantity_budget_<?php echo $dealer['farmer_id'];?>', width: 100,filterable: false,cellsalign: 'right',cellsrenderer: cellsrenderer,columntype: 'custom',
                        cellbeginedit: function (row)
                        {
                            var selectedRowData = $('#system_jqx_container').jqxGrid('getrowdata', row);//only last selected
                            return selectedRowData['editable_<?php echo $dealer['farmer_id'];?>'];
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
                    <?php
                    }
                     ?>
                ]
            });
    });
</script>
