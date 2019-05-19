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
        'href'=>site_url($CI->controller_url.'/index/set_preference_variety_amount_quantity')
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

        <div class="col-xs-2 "><div class="checkbox"><label><input type="checkbox" class="system_jqx_column_pkt" <?php if($system_preference_items['quantity_pkt']){echo 'checked';}?>><span class=""><?php echo $CI->lang->line('LABEL_QUANTITY_PKT'); ?></span></label></div></div>
        <div class="col-xs-2 "><div class="checkbox"><label><input type="checkbox" class="system_jqx_column_kg" <?php if($system_preference_items['quantity_kg']){echo 'checked';}?>><span class=""><?php echo $CI->lang->line('LABEL_QUANTITY_KG'); ?></span></label></div></div>
        <div class="col-xs-2 "><div class="checkbox"><label><input type="checkbox" class="system_jqx_column_amount" <?php if($system_preference_items['sales_amount']){echo 'checked';}?>><span class=""><?php echo $CI->lang->line('LABEL_SALES_AMOUNT'); ?></span></label></div></div>
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
        $(document).off("click", ".system_jqx_column_pkt");
        $(document).on("click", ".system_jqx_column_pkt", function(event)
        {
            var jqx_grid_id='#system_jqx_container';
            $(jqx_grid_id).jqxGrid('beginupdate');
            if($(this).is(':checked'))
            {
                <?php
                foreach($fiscal_years as $fy)
                {
                ?>
                $(jqx_grid_id).jqxGrid('showcolumn', '<?php echo 'quantity_'.$fy['id'].'_pkt'; ?>');
                <?php
                }
                ?>
            }
            else
            {
                <?php
                foreach($fiscal_years as $fy)
                {
                ?>
                $(jqx_grid_id).jqxGrid('hidecolumn', '<?php echo 'quantity_'.$fy['id'].'_pkt'; ?>');
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
                foreach($fiscal_years as $fy)
                {
                ?>
                $(jqx_grid_id).jqxGrid('showcolumn', '<?php echo 'quantity_'.$fy['id'].'_kg'; ?>');
                <?php
                }
                ?>
            }
            else
            {
                <?php
                foreach($fiscal_years as $fy)
                {
                ?>
                $(jqx_grid_id).jqxGrid('hidecolumn', '<?php echo 'quantity_'.$fy['id'].'_kg'; ?>');
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
                foreach($fiscal_years as $fy)
                {
                ?>
                $(jqx_grid_id).jqxGrid('showcolumn', '<?php echo 'sales_'.$fy['id'].'_amount'; ?>');
                <?php
                }
                ?>
            }
            else
            {
                <?php
                foreach($fiscal_years as $fy)
                {
                ?>
                $(jqx_grid_id).jqxGrid('hidecolumn', '<?php echo 'sales_'.$fy['id'].'_amount'; ?>');
                <?php
                }
                ?>
            }
            $(jqx_grid_id).jqxGrid('endupdate');
        });

        var url = "<?php echo site_url($CI->controller_url.'/index/get_items_variety_amount_quantity');?>";
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

                foreach($fiscal_years as $fy)
                {
                    ?>
                    { name: 'quantity_<?php echo $fy['id']; ?>_pkt', type: 'number' },
                    { name: 'quantity_<?php echo $fy['id']; ?>_kg', type: 'number' },
                    { name: 'sales_<?php echo $fy['id']; ?>_amount', type: 'number' },
                    <?php
                }
             ?>
            ],
            type: 'POST',
            url: url,
            data:JSON.parse('<?php echo json_encode($options);?>')
        };
        var cellsrenderer = function(row, column, value, defaultHtml, columnSettings, record)
        {
            var element = $(defaultHtml);

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
            else if(column.substr(-6)=='amount')
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
            return element[0].outerHTML;

        };
        var aggregates=function (total, column, element, record)
        {
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
        var aggregatesrenderer_kg=function (aggregates)
        {
            var text='';
            if(!((aggregates['total']=='0.000')||(aggregates['total']=='')))
            {
                text=get_string_kg(aggregates['total'])
            }

            return '<div style="position: relative; margin: 0px;padding: 5px;width: 100%;height: 100%; overflow: hidden;background-color:'+system_report_color_grand+';">' +text+'</div>';

        };
        var aggregatesrenderer_quantity=function (aggregates)
        {
            var text='';
            if(!((aggregates['total']=='0.000')||(aggregates['total']=='')))
            {
                text=get_string_quantity(aggregates['total'])
            }
            return '<div style="position: relative; margin: 0px;padding: 5px;width: 100%;height: 100%; overflow: hidden;background-color:'+system_report_color_grand+';">' +text+'</div>';
        };
        var aggregatesrenderer_amount=function (aggregates)
        {
            var text='';
            if(!((aggregates['total']=='0.000')||(aggregates['total']=='')))
            {
                text=get_string_amount(aggregates['total'])
            }
            return '<div style="position: relative; margin: 0px;padding: 5px;width: 100%;height: 100%; overflow: hidden;background-color:'+system_report_color_grand+';">' +text+'</div>';
        };
        var dataAdapter = new $.jqx.dataAdapter(source);
        // create jqxgrid.
        $("#system_jqx_container").jqxGrid(
            {
                source: dataAdapter,
                width: '100%',
                height: '350px',
                filterable: true,
                sortable: true,
                columnsresize: true,
                columnsreorder: true,
                enablebrowserselection: true,
                selectionmode: 'singlerow',
                showaggregates: true,
                showstatusbar: true,
                altrows: true,
                rowsheight: 35,
                editable:false,
                columns:
                    [
                        { text: '<?php echo $CI->lang->line('LABEL_CROP_NAME'); ?>', dataField: 'crop_name',pinned:true,width:'100',hidden: <?php echo $system_preference_items['crop_name']?0:1;?>,editable:false,cellsrenderer: cellsrenderer,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer},
                        { text: '<?php echo $CI->lang->line('LABEL_CROP_TYPE_NAME'); ?>', dataField: 'crop_type_name',pinned:true,width:'80',hidden: <?php echo $system_preference_items['crop_type_name']?0:1;?>,editable:false,cellsrenderer: cellsrenderer,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer},
                        { text: '<?php echo $CI->lang->line('LABEL_VARIETY_NAME'); ?>', dataField: 'variety_name',pinned:true,width:'130',hidden: <?php echo $system_preference_items['variety_name']?0:1;?>,editable:false,cellsrenderer: cellsrenderer,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer},
                        { text: '<?php echo $CI->lang->line('LABEL_PACK_SIZE'); ?>', dataField: 'pack_size',cellsalign: 'right',width:'60',hidden: <?php echo $system_preference_items['pack_size']?0:1;?>,editable:false,cellsrenderer: cellsrenderer,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer},
                            <?php
                            for($i=sizeof($fiscal_years)-1;$i>=0;$i--)
                            {?>
                            {columngroup: 'fiscal_year_<?php echo $fiscal_years[$i]['id']; ?>',text: 'pkt', dataField: 'quantity_<?php echo $fiscal_years[$i]['id']; ?>_pkt',width:'80',align:'center',cellsAlign:'right',hidden: <?php echo $system_preference_items['quantity_pkt']?0:1;?>,editable:false,cellsrenderer: cellsrenderer,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer_quantity},
                            {columngroup: 'fiscal_year_<?php echo $fiscal_years[$i]['id']; ?>',text: 'kg', dataField: 'quantity_<?php echo $fiscal_years[$i]['id']; ?>_kg',width:'100',align:'center',cellsAlign:'right',hidden: <?php echo $system_preference_items['quantity_kg']?0:1;?>,editable:false,cellsrenderer: cellsrenderer,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer_kg},
                            {columngroup: 'fiscal_year_<?php echo $fiscal_years[$i]['id']; ?>',text: 'Amount', dataField: 'sales_<?php echo $fiscal_years[$i]['id']; ?>_amount',width:'120',align:'center',cellsAlign:'right',hidden: <?php echo $system_preference_items['sales_amount']?0:1;?>,editable:false,cellsrenderer: cellsrenderer,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer_amount},
                            <?php
                            }
                            ?>
                    ],
                columngroups:
                    [
                        <?php
                            for($i=sizeof($fiscal_years)-1;$i>=0;$i--)
                            {?>
                            { text: '<?php echo $fiscal_years[$i]['name']; ?>', parentgroup: 'fiscal_years', align: 'center', name: 'fiscal_year_<?php echo $fiscal_years[$i]['id']; ?>' },
                                <?php
                            }
                            ?>
                        { text: '<?php echo $CI->lang->line('LABEL_FISCAL_YEARS'); ?> Achieved', align: 'center', name: 'fiscal_years' }
                    ]
            });
    });
</script>