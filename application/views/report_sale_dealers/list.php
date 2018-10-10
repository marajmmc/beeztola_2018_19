<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$CI = & get_instance();
$action_buttons=array();
if(isset($CI->permissions['action4'])&&($CI->permissions['action4']==1))
{
    $action_buttons[]=array(
        'type'=>'button',
        'label'=>$CI->lang->line("ACTION_PRINT"),
        'class'=>'button_action_download',
        'data-title'=>"Print",
        'data-print'=>true
    );
}
if(isset($CI->permissions['action5'])&&($CI->permissions['action5']==1))
{
    $action_buttons[]=array(
        'type'=>'button',
        'label'=>$CI->lang->line("ACTION_DOWNLOAD"),
        'class'=>'button_action_download',
        'data-title'=>"Download"
    );
}
if(isset($CI->permissions['action6']) && ($CI->permissions['action6']==1))
{
    $action_buttons[]=array
    (
        'label'=>'Preference',
        'href'=>site_url($CI->controller_url.'/index/set_preference')
    );
}
$CI->load->view('action_buttons',array('action_buttons'=>$action_buttons));
?>

<div class="row widget">
    <div class="widget-header">
        <div class="title">
            <?php echo $title; ?>
        </div>
        <div class="clearfix"></div>
    </div>
    <?php
    if(isset($CI->permissions['action6']) && ($CI->permissions['action6']==1))
    {

        ?>
        <div class="col-xs-2 "><div class="checkbox"><label><input type="checkbox" class="system_jqx_column" value="crop_name" <?php if($system_preference_items['crop_name']){echo 'checked';}?>><span class=""><?php echo $CI->lang->line('LABEL_CROP_NAME'); ?></span></label></div></div>
        <div class="col-xs-2 "><div class="checkbox"><label><input type="checkbox" class="system_jqx_column" value="crop_type_name" <?php if($system_preference_items['crop_type_name']){echo 'checked';}?>><span class=""><?php echo $CI->lang->line('LABEL_CROP_TYPE_NAME'); ?></span></label></div></div>
        <div class="col-xs-2 "><div class="checkbox"><label><input type="checkbox" class="system_jqx_column" value="variety_name" <?php if($system_preference_items['variety_name']){echo 'checked';}?>><span class=""><?php echo $CI->lang->line('LABEL_VARIETY_NAME'); ?></span></label></div></div>
        <div class="col-xs-2 "><div class="checkbox"><label><input type="checkbox" class="system_jqx_column" value="pack_size" <?php if($system_preference_items['pack_size']){echo 'checked';}?>><span class=""><?php echo $CI->lang->line('LABEL_PACK_SIZE'); ?></span></label></div></div>
        <div class="col-xs-2 "><div class="checkbox"><label><input type="checkbox" class="system_jqx_column" value="quantity_total_pkt" <?php if($system_preference_items['quantity_total_pkt']){echo 'checked';}?>><span class=""><?php echo $CI->lang->line('LABEL_QUANTITY_TOTAL_PKT'); ?></span></label></div></div>
        <div class="col-xs-2 "><div class="checkbox"><label><input type="checkbox" class="system_jqx_column" value="quantity_total_kg" <?php if($system_preference_items['quantity_total_kg']){echo 'checked';}?>><span class=""><?php echo $CI->lang->line('LABEL_QUANTITY_TOTAL_KG'); ?></span></label></div></div>
        <div class="col-xs-2 "><div class="checkbox"><label><input type="checkbox" class="system_jqx_column" value="amount_total" <?php if($system_preference_items['amount_total']){echo 'checked';}?>><span class=""><?php echo $CI->lang->line('LABEL_AMOUNT_TOTAL'); ?></span></label></div></div>
        <div class="col-xs-2 "><div class="checkbox"><label><input type="checkbox" class="system_jqx_column_pkt" value="quantity_pkt" <?php if($system_preference_items['quantity_pkt']){echo 'checked';}?>><span class=""><?php echo $CI->lang->line('LABEL_QUANTITY_PKT'); ?></span></label></div></div>
        <div class="col-xs-2 "><div class="checkbox"><label><input type="checkbox" class="system_jqx_column_kg" value="quantity_kg" <?php if($system_preference_items['quantity_kg']){echo 'checked';}?>><span class=""><?php echo $CI->lang->line('LABEL_QUANTITY_KG'); ?></span></label></div></div>
        <div class="col-xs-2 "><div class="checkbox"><label><input type="checkbox" class="system_jqx_column_amount" value="amount" <?php if($system_preference_items['amount']){echo 'checked';}?>><span class=""><?php echo $CI->lang->line('LABEL_AMOUNT'); ?></span></label></div></div>

    <?php
    }
    ?>
    <div class="col-xs-12" id="system_jqx_container">

    </div>
</div>
<div class="clearfix"></div>
<script type="text/javascript">
    $(document).ready(function ()
    {
        var url = "<?php echo site_url($CI->controller_url.'/index/get_items');?>";
        $(document).off("click", ".system_jqx_column_pkt");
        $(document).on("click", ".system_jqx_column_pkt", function(event)
        {
            var jqx_grid_id='#system_jqx_container';
            $(jqx_grid_id).jqxGrid('beginupdate');
            if($(this).is(':checked'))
            {
                <?php
                foreach($dealers as $dealer)
                {?>
                $(jqx_grid_id).jqxGrid('showcolumn', '<?php echo 'quantity_'.$dealer['farmer_id'].'_pkt';?>');
                <?php
                }
                ?>

            }
            else
            {
                <?php
                foreach($dealers as $dealer)
                {?>
                $(jqx_grid_id).jqxGrid('hidecolumn', '<?php echo 'quantity_'.$dealer['farmer_id'].'_pkt';?>');
                <?php
                }
                ?>
            }
            $(jqx_grid_id).jqxGrid('endupdate');
        });
        $(document).off("click", ".system_jqx_column_kg");
        $(document).on("click", ".system_jqx_column_kg", function(event)
        {
            var jqx_grid_id='#system_jqx_container';
            $(jqx_grid_id).jqxGrid('beginupdate');
            if($(this).is(':checked'))
            {
                <?php
                foreach($dealers as $dealer)
                {?>
                $(jqx_grid_id).jqxGrid('showcolumn', '<?php echo 'quantity_'.$dealer['farmer_id'].'_kg';?>');
                <?php
                }
                ?>

            }
            else
            {
                <?php
                foreach($dealers as $dealer)
                {?>
                $(jqx_grid_id).jqxGrid('hidecolumn', '<?php echo 'quantity_'.$dealer['farmer_id'].'_kg';?>');
                <?php
                }
                ?>
            }
            $(jqx_grid_id).jqxGrid('endupdate');
        });

        $(document).off("click", ".system_jqx_column_amount");
        $(document).on("click", ".system_jqx_column_amount", function(event)
        {
            var jqx_grid_id='#system_jqx_container';
            $(jqx_grid_id).jqxGrid('beginupdate');
            if($(this).is(':checked'))
            {
                <?php
                foreach($dealers as $dealer)
                {?>
                $(jqx_grid_id).jqxGrid('showcolumn', '<?php echo 'amount_'.$dealer['farmer_id'];?>');
                <?php
                }
                ?>

            }
            else
            {
                <?php
                foreach($dealers as $dealer)
                {?>
                $(jqx_grid_id).jqxGrid('hidecolumn', '<?php echo 'amount_'.$dealer['farmer_id'];?>');
                <?php
                }
                ?>
            }
            $(jqx_grid_id).jqxGrid('endupdate');
        });

        // prepare the data
        var source =
        {
            dataType: "json",
            dataFields: [
                { name: 'crop_name', type: 'string' },
                { name: 'crop_type_name', type: 'string' },
                { name: 'variety_name', type: 'string' },
                { name: 'pack_size', type: 'string' },
                    <?php
                        foreach($dealers as $dealer)
                        {?>{ name: '<?php echo 'quantity_'.$dealer['farmer_id'].'_pkt';?>', type: 'string' },
                        { name: '<?php echo 'quantity_'.$dealer['farmer_id'].'_kg';?>', type: 'string' },
                        { name: '<?php echo 'amount_'.$dealer['farmer_id'];?>', type: 'string' },
                <?php
                    }
                ?>
                { name: 'quantity_total_pkt', type: 'string' },
                { name: 'quantity_total_kg', type: 'string' },
                { name: 'amount_total', type: 'string' }
            ],
            url: url,
            type: 'POST',
            data:JSON.parse('<?php echo json_encode($options);?>')
        };
        var cellsrenderer = function(row, column, value, defaultHtml, columnSettings, record)
        {
            var element = $(defaultHtml);
            //console.log(defaultHtml);
            if (record.variety_name=="Total Type")
            {
                if(!((column=='crop_name')||(column=='crop_type_name')))
                {
                    element.css({ 'background-color': system_report_color_type,'margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
                }
            }
            else if (record.crop_type_name=="Total Crop")
            {
                if(column!='crop_name')
                {
                    element.css({ 'background-color': system_report_color_crop,'margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
                }
            }
            else if (record.crop_name=="Grand Total")
            {

                element.css({ 'background-color': system_report_color_grand,'margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});

            }
            else
            {
                element.css({'margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
            }

            if(column.substr(-3)=='pkt')
            {
                if(value==0)
                {
                    element.html('');
                }
                else
                {
                    element.html(get_string_quantity(value));
                }
            }
            else if(column.substr(-2)=='kg')
            {
                if(value==0)
                {
                    element.html('');
                }
                else
                {
                    element.html(get_string_kg(value));
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

            return element[0].outerHTML;



        };
        var tooltiprenderer = function (element) {
            $(element).jqxTooltip({position: 'mouse', content: $(element).text() });
        };
        var aggregates=function (total, column, element, record)
        {
            //console.log(record);
            //console.log(record['warehouse_5_pkt']);
            if(record.crop_name=="Grand Total")
            {
                return record[element];

            }
            return total;
        };
        var aggregatesrenderer=function (aggregates)
        {
            //console.log('here');
            return '<div style="position: relative; margin: 0px;padding: 5px;width: 100%;height: 100%; overflow: hidden;background-color:'+system_report_color_grand+';">' +aggregates['total']+'</div>';

        };
        var aggregatesrenderer_pkt=function (aggregates)
        {
            var text='';
            if(!((aggregates['total']=='0')||(aggregates['total']=='')))
            {
                text=get_string_quantity(aggregates['total']);
            }

            return '<div style="position: relative; margin: 0px;padding: 5px;width: 100%;height: 100%; overflow: hidden;background-color:'+system_report_color_grand+';">' +text+'</div>';

        };
        var aggregatesrenderer_kg=function (aggregates)
        {
            var text='';
            if(!((aggregates['total']=='0.000')||(aggregates['total']=='')))
            {
                text=get_string_kg(aggregates['total']);
            }
            return '<div style="position: relative; margin: 0px;padding: 5px;width: 100%;height: 100%; overflow: hidden;background-color:'+system_report_color_grand+';">' +text+'</div>';
        };
        var aggregatesrenderer_amount=function (aggregates)
        {
            var text='';
            if(!((aggregates['total']=='0.00')||(aggregates['total']=='')))
            {
                text=get_string_amount(aggregates['total'],2);
            }

            return '<div style="position: relative; margin: 0px;padding: 5px;width: 100%;height: 100%; overflow: hidden;background-color:'+system_report_color_grand+';">' +text+'</div>';

        };

        var dataAdapter = new $.jqx.dataAdapter(source);
        // create jqxgrid.
        $("#system_jqx_container").jqxGrid(
            {
                width: '100%',
                height:'350px',
                source: dataAdapter,
                columnsresize: true,
                columnsreorder: true,
                altrows: true,
                enabletooltips: true,
                showaggregates: true,
                showstatusbar: true,
                rowsheight: 40,
                columns: [
                    { text: '<?php echo $CI->lang->line('LABEL_CROP_NAME'); ?>', dataField: 'crop_name',pinned:true,width:'100',cellsrenderer: cellsrenderer,hidden: <?php echo $system_preference_items['crop_name']?0:1;?>,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer},
                    { text: '<?php echo $CI->lang->line('LABEL_CROP_TYPE_NAME'); ?>', dataField: 'crop_type_name',pinned:true,width:'100',cellsrenderer: cellsrenderer,hidden: <?php echo $system_preference_items['crop_type_name']?0:1;?>,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer},
                    { text: '<?php echo $CI->lang->line('LABEL_VARIETY_NAME'); ?>', dataField: 'variety_name',pinned:true,width:'100',cellsrenderer: cellsrenderer,hidden: <?php echo $system_preference_items['variety_name']?0:1;?>,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer},
                    { text: '<?php echo $CI->lang->line('LABEL_PACK_SIZE'); ?>', dataField: 'pack_size',pinned:true,width:'100',cellsrenderer: cellsrenderer,hidden: <?php echo $system_preference_items['pack_size']?0:1;?>,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer},
                    { columngroup: 'Total',text: 'pkt',dataField: '<?php echo 'quantity_total_pkt';?>',align:'center',cellsalign: 'right',width:'80',cellsrenderer: cellsrenderer,hidden: <?php echo $system_preference_items['quantity_total_pkt']?0:1;?>,rendered: tooltiprenderer,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer_pkt},
                    { columngroup: 'Total',text: 'kg', dataField: '<?php echo 'quantity_total_kg';?>',align:'center',cellsalign: 'right',width:'80',cellsrenderer: cellsrenderer,hidden: <?php echo $system_preference_items['quantity_total_kg']?0:1;?>,rendered: tooltiprenderer,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer_kg},
                    { columngroup: 'Total',text: 'Amount', dataField: '<?php echo 'amount_total';?>',align:'center',cellsalign: 'right',width:'120',cellsrenderer: cellsrenderer,hidden: <?php echo $system_preference_items['amount_total']?0:1;?>,rendered: tooltiprenderer,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer_amount},
                    <?php
                        foreach($dealers as $dealer)
                        {   ?>{ columngroup: '<?php echo $dealer['farmer_name']; ?>',text: 'pkt',dataField: '<?php echo 'quantity_'.$dealer['farmer_id'].'_pkt';?>',align:'center',cellsalign: 'right',width:'80',cellsrenderer: cellsrenderer,hidden: <?php echo $system_preference_items['quantity_pkt']?0:1;?>,rendered: tooltiprenderer,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer_pkt},
                            { columngroup: '<?php echo $dealer['farmer_name']; ?>',text: 'kg', dataField: '<?php echo 'quantity_'.$dealer['farmer_id'].'_kg';?>',align:'center',cellsalign: 'right',width:'80',cellsrenderer: cellsrenderer,hidden: <?php echo $system_preference_items['quantity_kg']?0:1;?>,rendered: tooltiprenderer,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer_kg},
                            { columngroup: '<?php echo $dealer['farmer_name']; ?>',text: 'Amount', dataField: '<?php echo 'amount_'.$dealer['farmer_id'];?>',align:'center',cellsalign: 'right',width:'120',cellsrenderer: cellsrenderer,hidden: <?php echo $system_preference_items['amount']?0:1;?>,rendered: tooltiprenderer,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer_amount},
                            <?php
                        }
                    ?>

                ],
                columngroups:
                    [
                            <?php
                                foreach($dealers as $dealer)
                                {?>{ text: '<?php echo $dealer['farmer_name']; ?>', align: 'center', name: '<?php echo $dealer['farmer_name']; ?>' },
                        <?php
                            }
                        ?>
                        { text: 'Total', align: 'center', name: 'Total' }
                    ]
            });
    });
</script>