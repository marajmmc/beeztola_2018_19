<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$CI = & get_instance();
if((isset($CI->permissions['action1']) && ($CI->permissions['action1']==1)))
{
    $action_buttons[]=array(
        'type'=>'button',
        'label'=>$CI->lang->line("ACTION_SAVE"),
        'id'=>'button_action_save_jqx'
    );
}
/*$action_buttons[]=array(
    'label'=>$CI->lang->line("ACTION_BACK"),
    'href'=>site_url($CI->controller_url)
);*/
$CI->load->view('action_buttons',array('action_buttons'=>$action_buttons));

?>
<form id="save_form_jqx" action="<?php echo site_url($CI->controller_url.'/index/save');?>" method="post">
    <input type="hidden" name="item[outlet_id]" value="<?php echo $options['outlet_id']; ?>" />
    <input type="hidden" name="item[month_id]" value="<?php echo $options['month_id']; ?>" />
    <input type="hidden" name="item[year_id]" value="<?php echo $options['year_id']; ?>" />
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
                <?php
                foreach($dealers as $dealer)
                {
                ?>
                $('#save_form_jqx  #jqx_inputs').append('<input type="hidden" name="items['+data[i]['variety_id']+']['+data[i]['pack_size_id']+'][<?php echo $dealer['farmer_id']?>][amount_budget]" value="'+data[i]['amount_budget_<?php echo $dealer['farmer_id']?>']+'">');
                <?php
                }
                ?>
                //$('#save_form_jqx  #jqx_inputs').append('<input type="hidden" name="items['+data[i]['variety_id']+']['+data[i]['pack_size_id']+'][amount_budget]" value="'+data[i]['amount_budget']+'">');
            }
            var sure = confirm('<?php echo $CI->lang->line('MSG_CONFIRM_SAVE'); ?>');
            if(sure)
            {
                $("#save_form_jqx").submit();
                //return false;
            }

        });



        var url = "<?php echo site_url($CI->controller_url.'/index/get_items_variety/');?>";
        // prepare the data
        var source =
        {
            dataType: "json",
            datafields:
                [
                    { name: 'crop_type_name', type: 'string' },
                    { name: 'variety_id', type: 'string' },
                    { name: 'variety_name', type: 'string' },
                    { name: 'pack_size_id', type: 'string' },
                    { name: 'pack_size', type: 'string' },
                    { name: 'current_stock', type: 'string' },
                    { name: 'price_net', type: 'string' },
                    <?php
                foreach($dealers as $dealer)
                {
                ?>
                    { name: 'amount_budget_<?php echo $dealer['farmer_id']?>', type: 'string' },
                    <?php
                    }
                    ?>
                ],
            id: 'id',
            url: url,
            type: 'POST',
            data:JSON.parse('<?php echo json_encode($options);?>')
        };

        var dataAdapter = new $.jqx.dataAdapter(source);
        var cellsrenderer = function(row, column, value, defaultHtml, columnSettings, record)
        {
            var element = $(defaultHtml);
            var total_budget=0;
            var price_net=parseFloat(record['price_net']);

            if(column=='total_budget')
            {
                <?php
                foreach($dealers as $dealer)
                {
                ?>
                if(!isNaN(parseFloat(record['<?php echo 'amount_budget_'.$dealer['farmer_id'];?>'])))
                {
                    total_budget+=parseFloat(record['<?php echo 'amount_budget_'.$dealer['farmer_id'];?>']);
                }
                <?php
                }
                ?>
                if(total_budget==0)
                {
                    element.html('');
                    //element.css({'background-color': '#FF0000','margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
                }
                else
                {
                    element.html(total_budget);
                    //element.css({'background-color': '#00FF00','margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
                }
            }
            if(column=='total_price')
            {
                <?php
                foreach($dealers as $dealer)
                {
                ?>
                if(!isNaN(parseFloat(record['<?php echo 'amount_budget_'.$dealer['farmer_id'];?>'])))
                {
                    total_budget+=parseFloat(record['<?php echo 'amount_budget_'.$dealer['farmer_id'];?>']);
                }
                <?php
                }
                ?>

                if(total_budget==0)
                {
                    element.html('0');
                    //element.css({ 'background-color': '#FF0000','margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
                }
                else
                {
                    element.html(number_format(total_budget*price_net,2));
                    //element.css({ 'background-color': '#00FF00','margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
                }
            }
            <?php
            foreach($dealers as $dealer)
            {
            ?>
            if(column=='amount_budget_<?php echo $dealer['farmer_id']?>')
            {
                element.css({'margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
                element.html('<div class="jqxgrid_input">'+value+'</div>');
            }
            <?php
            }
            ?>
            return element[0].outerHTML;
        };
        // create jqxgrid.
        $("#system_jqx_container").jqxGrid(
            {
                width: '100%',
                height: '350px',
                source: dataAdapter,
                columnsresize: true,
                columnsreorder: true,
                enablebrowserselection: true,
                altrows: true,
                rowsheight: 35,
                columnsheight: 70,
                editable:true,
                columns: [
                    { text: '<?php echo $CI->lang->line('LABEL_CROP_TYPE_NAME'); ?>', dataField: 'crop_type_name',width:'100',pinned:true,editable:false},
                    { text: '<?php echo $CI->lang->line('LABEL_VARIETY_NAME'); ?>', dataField: 'variety_name',width:'150',pinned:true,editable:false},
                    { text: '<?php echo $CI->lang->line('LABEL_PACK_SIZE'); ?>', dataField: 'pack_size',width:'50',pinned:true,editable:false},
                    { text: '<?php echo $CI->lang->line('LABEL_CURRENT_STOCK'); ?>', dataField: 'current_stock',width:'100',pinned:true,cellsalign: 'right',editable:false},
                    { text: '<?php echo $CI->lang->line('LABEL_TOTAL'); ?> Budget', dataField: 'total_budget',width:'100',pinned:true,cellsrenderer: cellsrenderer,cellsalign: 'right',editable:false},
                    { text: '<?php echo $CI->lang->line('LABEL_TOTAL_PRICE'); ?>', dataField: 'total_price',width:'130',pinned:true,cellsrenderer: cellsrenderer,cellsalign: 'right',editable:false},
                    <?php
                    $serial=0;
                    foreach($dealers as $dealer)
                    {
                    ++$serial;
                    ?>
                    { text: '<?php echo $serial.'. '.$dealer['farmer_name']?>',renderer: function (text, align)
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
                    },datafield: 'amount_budget_<?php echo $dealer['farmer_id']?>', width: 100,cellsalign: 'right',cellsrenderer: cellsrenderer,columntype: 'custom',
                        cellbeginedit: function (row)
                        {
                            return <?php if((isset($CI->permissions['action2']) && ($CI->permissions['action2']==1))){ echo 'true';}else{echo 'false';}?>;
                        },
                        initeditor: function (row, cellvalue, editor, celltext, pressedkey)
                        {
                            editor.html('<div style="margin: 0px;width: 100%;height: 100%;padding: 5px;"><input type="text" value="'+cellvalue+'" class="jqxgrid_input float_type_positive"><div>');
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
