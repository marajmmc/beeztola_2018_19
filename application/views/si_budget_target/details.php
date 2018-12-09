<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$CI = & get_instance();
$action_buttons=array();
$action_buttons[]=array
(
    'label'=>$CI->lang->line("ACTION_BACK"),
    'href'=>site_url($CI->controller_url)
);
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
        'href'=>site_url($CI->controller_url.'/index/set_preference_details')
    );
}
$action_buttons[]=array
(
    'label'=>$CI->lang->line("ACTION_REFRESH"),
    'href'=>site_url($CI->controller_url.'/index/details/'.$options['fiscal_year_id'].'/'.$options['outlet_id'])
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
    <div style="" class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_FISCAL_YEAR');?></label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo $fiscal_year_budget_target['name'];?></label>
        </div>
    </div>
    <div style="" class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_AREA_SUPERIOR');?></label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo $area_superior['name'];?></label>
        </div>
    </div>
    <div style="" class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_AREA');?></label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo $area['name'];?></label>
        </div>
    </div>

    <?php
    echo $CI->load->view($this->common_view_location."/info_basic",'',true);
    echo $CI->load->view($this->common_view_location."/info_acres",'',true);
    ?>
    <?php
    if(isset($CI->permissions['action6']) && ($CI->permissions['action6']==1))
    {
        //$CI->load->view('preference',array('system_preference_items'=>$system_preference_items));
    ?>
        <div class="col-xs-2 "><div class="checkbox"><label><input type="checkbox" class="system_jqx_column" value="crop_name" <?php if($system_preference_items['crop_name']){echo 'checked';}?>><span class=""><?php echo $CI->lang->line('LABEL_CROP_NAME'); ?></span></label></div></div>
        <div class="col-xs-2 "><div class="checkbox"><label><input type="checkbox" class="system_jqx_column" value="crop_type_name" <?php if($system_preference_items['crop_type_name']){echo 'checked';}?>><span class=""><?php echo $CI->lang->line('LABEL_CROP_TYPE_NAME'); ?></span></label></div></div>
        <div class="col-xs-2 "><div class="checkbox"><label><input type="checkbox" class="system_jqx_column" value="variety_name" <?php if($system_preference_items['variety_name']){echo 'checked';}?>><span class=""><?php echo $CI->lang->line('LABEL_VARIETY_NAME'); ?></span></label></div></div>
        <div class="col-xs-2 "><div class="checkbox"><label><input type="checkbox" class="system_jqx_column" value="price_unit_kg_amount" <?php if($system_preference_items['price_unit_kg_amount']){echo 'checked';}?>><span class=""><?php echo $CI->lang->line('LABEL_PRICE_UNIT_KG_AMOUNT'); ?></span></label></div></div>
        <div class="col-xs-2 "><div class="checkbox"><label><input type="checkbox" class="system_jqx_column" value="budget_kg" <?php if($system_preference_items['budget_kg']){echo 'checked';}?>><span class=""><?php echo $CI->lang->line('LABEL_BUDGET_KG'); ?></span></label></div></div>
        <div class="col-xs-2 "><div class="checkbox"><label><input type="checkbox" class="system_jqx_column" value="budget_amount" <?php if($system_preference_items['budget_amount']){echo 'checked';}?>><span class=""><?php echo $CI->lang->line('LABEL_BUDGET_AMOUNT'); ?></span></label></div></div>
        <div class="col-xs-2 "><div class="checkbox"><label><input type="checkbox" class="system_jqx_column" value="target_kg" <?php if($system_preference_items['target_kg']){echo 'checked';}?>><span class=""><?php echo $CI->lang->line('LABEL_TARGET_KG'); ?></span></label></div></div>
        <div class="col-xs-2 "><div class="checkbox"><label><input type="checkbox" class="system_jqx_column" value="target_amount" <?php if($system_preference_items['target_amount']){echo 'checked';}?>><span class=""><?php echo $CI->lang->line('LABEL_TARGET_AMOUNT'); ?></span></label></div></div>
        <div class="col-xs-2 "><div class="checkbox"><label><input type="checkbox" class="system_jqx_column_budget_sub_kg" value="budget_sub_kg" <?php if($system_preference_items['budget_sub_kg']){echo 'checked';}?>><span class=""><?php echo $CI->lang->line('LABEL_BUDGET_SUB_KG'); ?></span></label></div></div>
        <div class="col-xs-2 "><div class="checkbox"><label><input type="checkbox" class="system_jqx_column_budget_sub_amount" value="budget_sub_amount" <?php if($system_preference_items['budget_sub_amount']){echo 'checked';}?>><span class=""><?php echo $CI->lang->line('LABEL_BUDGET_SUB_AMOUNT'); ?></span></label></div></div>
        <div class="col-xs-2 "><div class="checkbox"><label><input type="checkbox" class="system_jqx_column_target_sub_kg" value="target_sub_kg" <?php if($system_preference_items['target_sub_kg']){echo 'checked';}?>><span class=""><?php echo $CI->lang->line('LABEL_TARGET_SUB_KG'); ?></span></label></div></div>
        <div class="col-xs-2 "><div class="checkbox"><label><input type="checkbox" class="system_jqx_column_target_sub_amount" value="target_sub_amount" <?php if($system_preference_items['target_sub_amount']){echo 'checked';}?>><span class=""><?php echo $CI->lang->line('LABEL_TARGET_SUB_AMOUNT'); ?></span></label></div></div>
        <div class="col-xs-2 "><div class="checkbox"><label><input type="checkbox" class="system_jqx_column_prediction_kg" value="prediction_kg" <?php if($system_preference_items['prediction_kg']){echo 'checked';}?>><span class=""><?php echo $CI->lang->line('LABEL_PREDICTION_KG'); ?></span></label></div></div>
        <div class="col-xs-2 "><div class="checkbox"><label><input type="checkbox" class="system_jqx_column_prediction_amount" value="prediction_amount" <?php if($system_preference_items['prediction_amount']){echo 'checked';}?>><span class=""><?php echo $CI->lang->line('LABEL_PREDICTION_AMOUNT'); ?></span></label></div></div>
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
        $(document).off("click", ".system_jqx_column_budget_sub_kg");
        $(document).on("click", ".system_jqx_column_budget_sub_kg", function(event)
        {
            var jqx_grid_id='#system_jqx_container';
            $(jqx_grid_id).jqxGrid('beginupdate');
            if($(this).is(':checked'))
            {
                <?php
                foreach($areas as $area)
                {
                ?>
                $(jqx_grid_id).jqxGrid('showcolumn', '<?php echo 'budget_sub_'.$area['value'].'_kg'; ?>');
                <?php
                }
                ?>
            }
            else
            {
                <?php
                foreach($areas as $area)
                {
                ?>
                $(jqx_grid_id).jqxGrid('hidecolumn', '<?php echo 'budget_sub_'.$area['value'].'_kg'; ?>');
                <?php
                }
                ?>
            }
            $(jqx_grid_id).jqxGrid('endupdate');
        });
        $(document).off("click", ".system_jqx_column_budget_sub_amount");
        $(document).on("click", ".system_jqx_column_budget_sub_amount", function(event)
        {
            var jqx_grid_id='#system_jqx_container';
            $(jqx_grid_id).jqxGrid('beginupdate');
            if($(this).is(':checked'))
            {
                <?php
                foreach($areas as $area)
                {
                ?>
                $(jqx_grid_id).jqxGrid('showcolumn', '<?php echo 'budget_sub_'.$area['value'].'_amount'; ?>');
                <?php
                }
                ?>
            }
            else
            {
                <?php
                foreach($areas as $area)
                {
                ?>
                $(jqx_grid_id).jqxGrid('hidecolumn', '<?php echo 'budget_sub_'.$area['value'].'_amount'; ?>');
                <?php
                }
                ?>
            }
            $(jqx_grid_id).jqxGrid('endupdate');
        });
        $(document).off("click", ".system_jqx_column_target_sub_kg");
        $(document).on("click", ".system_jqx_column_target_sub_kg", function(event)
        {
            var jqx_grid_id='#system_jqx_container';
            $(jqx_grid_id).jqxGrid('beginupdate');
            if($(this).is(':checked'))
            {
                <?php
                foreach($areas as $area)
                {
                ?>
                $(jqx_grid_id).jqxGrid('showcolumn', '<?php echo 'target_sub_'.$area['value'].'_kg'; ?>');
                <?php
                }
                ?>
            }
            else
            {
                <?php
                foreach($areas as $area)
                {
                ?>
                $(jqx_grid_id).jqxGrid('hidecolumn', '<?php echo 'target_sub_'.$area['value'].'_kg'; ?>');
                <?php
                }
                ?>
            }
            $(jqx_grid_id).jqxGrid('endupdate');
        });
        $(document).off("click", ".system_jqx_column_target_sub_amount");
        $(document).on("click", ".system_jqx_column_target_sub_amount", function(event)
        {
            var jqx_grid_id='#system_jqx_container';
            $(jqx_grid_id).jqxGrid('beginupdate');
            if($(this).is(':checked'))
            {
                <?php
                foreach($areas as $area)
                {
                ?>
                $(jqx_grid_id).jqxGrid('showcolumn', '<?php echo 'target_sub_'.$area['value'].'_amount'; ?>');
                <?php
                }
                ?>
            }
            else
            {
                <?php
                foreach($areas as $area)
                {
                ?>
                $(jqx_grid_id).jqxGrid('hidecolumn', '<?php echo 'target_sub_'.$area['value'].'_amount'; ?>');
                <?php
                }
                ?>
            }
            $(jqx_grid_id).jqxGrid('endupdate');
        });

        $(document).off("click", ".system_jqx_column_prediction_kg");
        $(document).on("click", ".system_jqx_column_prediction_kg", function(event)
        {
            var jqx_grid_id='#system_jqx_container';
            $(jqx_grid_id).jqxGrid('beginupdate');
            if($(this).is(':checked'))
            {
                <?php
                $serial=0;
                foreach($fiscal_years_next_predictions as $budget)
                {
                ++$serial;
                ?>
                $(jqx_grid_id).jqxGrid('showcolumn', 'prediction_<?php echo $serial; ?>_kg');
                <?php
                }
                ?>
            }
            else
            {
                <?php
                $serial=0;
                foreach($fiscal_years_next_predictions as $budget)
                {
                ++$serial;
                ?>
                $(jqx_grid_id).jqxGrid('hidecolumn', 'prediction_<?php echo $serial; ?>_kg');
                <?php
                }
                ?>
            }
            $(jqx_grid_id).jqxGrid('endupdate');
        });
        $(document).off("click", ".system_jqx_column_prediction_amount");
        $(document).on("click", ".system_jqx_column_prediction_amount", function(event)
        {
            var jqx_grid_id='#system_jqx_container';
            $(jqx_grid_id).jqxGrid('beginupdate');
            if($(this).is(':checked'))
            {
                <?php
                $serial=0;
                foreach($fiscal_years_next_predictions as $budget)
                {
                ++$serial;
                ?>
                $(jqx_grid_id).jqxGrid('showcolumn', 'prediction_<?php echo $serial; ?>_amount');
                <?php
                }
                ?>
            }
            else
            {
                <?php
                $serial=0;
                foreach($fiscal_years_next_predictions as $budget)
                {
                ++$serial;
                ?>
                $(jqx_grid_id).jqxGrid('hidecolumn', 'prediction_<?php echo $serial; ?>_amount');
                <?php
                }
                ?>
            }
            $(jqx_grid_id).jqxGrid('endupdate');
        });

        var url = "<?php echo site_url($CI->controller_url.'/index/get_items_details');?>";
        // prepare the data
        var source =
        {
            dataType: "json",
            dataFields: [
                { name: 'crop_name', type: 'string' },
                { name: 'crop_type_name', type: 'string' },
                { name: 'variety_name', type: 'string' },
                { name: 'price_unit_kg_amount', type: 'number' },
                { name: 'budget_kg', type: 'number' },
                { name: 'budget_amount', type: 'number' },
                { name: 'target_kg', type: 'number' },
                { name: 'target_amount', type: 'number' },
                <?php
                foreach ($areas as $area)
                {
                    ?>
                    { name: '<?php echo 'budget_sub_'.$area['value'].'_kg'; ?>', type: 'number' },
                    { name: '<?php echo 'budget_sub_'.$area['value'].'_amount'; ?>', type: 'number' },
                    { name: '<?php echo 'target_sub_'.$area['value'].'_kg'; ?>', type: 'number' },
                    { name: '<?php echo 'target_sub_'.$area['value'].'_amount'; ?>', type: 'number' },
                    <?php
                }
                ?>
                { name: 'prediction_1_kg', type: 'number' },
                { name: 'prediction_2_kg', type: 'number' },
                { name: 'prediction_3_kg', type: 'number' },
                { name: 'prediction_1_amount', type: 'number' },
                { name: 'prediction_2_amount', type: 'number' },
                { name: 'prediction_3_amount', type: 'number' }
            ],
            url: url,
            type: 'POST',
            data:JSON.parse('<?php echo json_encode($options);?>')
        };
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
        var tooltiprenderer = function (element) {
            $(element).jqxTooltip({position: 'mouse', content: $(element).text() });
        };
        var cellsrenderer = function(row, column, value, defaultHtml, columnSettings, record)
        {
            var element = $(defaultHtml);

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
            if(column.substr(-2)=='kg')
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
                text=get_string_kg(aggregates['total']);
            }

            return '<div style="position: relative; margin: 0px;padding: 5px;width: 100%;height: 100%; overflow: hidden;background-color:'+system_report_color_grand+';">' +text+'</div>';
        };
        var aggregatesrenderer_amount=function (aggregates)
        {
            var text='';
            if(!((aggregates['total']=='0.000')||(aggregates['total']=='')))
            {
                text=get_string_amount(aggregates['total']);
            }

            return '<div style="position: relative; margin: 0px;padding: 5px;width: 100%;height: 100%; overflow: hidden;background-color:'+system_report_color_grand+';">' +text+'</div>';
        };

        var dataAdapter = new $.jqx.dataAdapter(source);
        // create jqxgrid.
        $("#system_jqx_container").jqxGrid(
            {
                source: dataAdapter,
                width: '100%',
                height: '450px',
                filterable: true,
                sortable: true,
                showfilterrow: true,
                columnsresize: true,
                columnsreorder: true,
                enablebrowserselection: true,
                selectionmode: 'singlerow',
                showaggregates: true,
                showstatusbar: true,
                altrows: true,
                rowsheight: 35,
                columnsheight: 70,
                columns: [
                    { text: '<?php echo $CI->lang->line('LABEL_CROP_NAME'); ?>', dataField: 'crop_name',pinned:true,width:'100',filtertype: 'list',cellsrenderer: cellsrenderer,hidden: <?php echo $system_preference_items['crop_name']?0:1;?>,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer},
                    { text: '<?php echo $CI->lang->line('LABEL_CROP_TYPE_NAME'); ?>', dataField: 'crop_type_name',pinned:true,width:'100',cellsrenderer: cellsrenderer,hidden: <?php echo $system_preference_items['crop_type_name']?0:1;?>,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer},
                    { text: '<?php echo $CI->lang->line('LABEL_VARIETY_NAME'); ?>', dataField: 'variety_name',pinned:true,width:'100',cellsrenderer: cellsrenderer,hidden: <?php echo $system_preference_items['variety_name']?0:1;?>,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer},
                    { text: '<?php echo $CI->lang->line('LABEL_PRICE_UNIT_KG_AMOUNT'); ?>', dataField: 'price_unit_kg_amount',width:'100',cellsAlign:'right',hidden: <?php echo $system_preference_items['price_unit_kg_amount']?0:1;?>,cellsrenderer: cellsrenderer,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer_amount},
                    { text: '<?php echo $CI->lang->line('LABEL_BUDGET_KG'); ?>', dataField: 'budget_kg',width:'100',cellsAlign:'right',hidden: <?php echo $system_preference_items['budget_kg']?0:1;?>,cellsrenderer: cellsrenderer,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer_kg},
                    { text: '<?php echo $CI->lang->line('LABEL_BUDGET_AMOUNT'); ?>', dataField: 'budget_amount',width:'120',cellsAlign:'right',hidden: <?php echo $system_preference_items['budget_amount']?0:1;?>,cellsrenderer: cellsrenderer,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer_amount},
                    { text: '<?php echo $CI->lang->line('LABEL_TARGET_KG'); ?>', dataField: 'target_kg',width:'100',cellsAlign:'right',hidden: <?php echo $system_preference_items['target_kg']?0:1;?>,cellsrenderer: cellsrenderer,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer_kg},
                    { text: '<?php echo $CI->lang->line('LABEL_TARGET_AMOUNT'); ?>', dataField: 'target_amount',width:'120',cellsAlign:'right',hidden: <?php echo $system_preference_items['target_amount']?0:1;?>,cellsrenderer: cellsrenderer,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer_amount},
                    <?php
                    foreach ($areas as $area)
                    {
                        ?>
                            {  columngroup: 'sub_area_budget',text: '<?php echo $area['text'].'(kg)'; ?> ', dataField: '<?php echo 'budget_sub_'.$area['value'].'_kg'; ?>',width:'100',cellsAlign:'right',hidden: <?php echo $system_preference_items['budget_sub_kg']?0:1;?>,renderer: header_render,cellsrenderer: cellsrenderer,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer_kg},
                            {  columngroup: 'sub_area_budget',text: '<?php echo $area['text'].'(amount)'; ?> ', dataField: '<?php echo 'budget_sub_'.$area['value'].'_amount'; ?>',width:'120',cellsAlign:'right',hidden: <?php echo $system_preference_items['budget_sub_amount']?0:1;?>,renderer: header_render,cellsrenderer: cellsrenderer,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer_amount},
                        <?php
                    }
                    foreach ($areas as $area)
                    {
                        ?>
                        {  columngroup: 'sub_area_target',text: '<?php echo $area['text'].'(kg)'; ?> ', dataField: '<?php echo 'target_sub_'.$area['value'].'_kg'; ?>',width:'100',cellsAlign:'right',hidden: <?php echo $system_preference_items['target_sub_kg']?0:1;?>,renderer: header_render,cellsrenderer: cellsrenderer,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer_kg},
                        {  columngroup: 'sub_area_target',text: '<?php echo $area['text'].'(amount)'; ?> ', dataField: '<?php echo 'target_sub_'.$area['value'].'_amount'; ?>',width:'120',cellsAlign:'right',hidden: <?php echo $system_preference_items['target_sub_amount']?0:1;?>,renderer: header_render,cellsrenderer: cellsrenderer,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer_amount},
                        <?php
                    }
                    $serial=0;
                    foreach ($fiscal_years_next_predictions as $fy)
                    {
                        ++$serial;
                        ?>
                        {  columngroup: 'next_years_prediction',text: '<?php echo $fy['name'].'(kg)'; ?> ', dataField: '<?php echo 'prediction_'.$serial.'_kg'; ?>',width:'100',cellsAlign:'right',hidden: <?php echo $system_preference_items['prediction_kg']?0:1;?>,cellsrenderer: cellsrenderer,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer_kg},
                        {  columngroup: 'next_years_prediction',text: '<?php echo $fy['name'].'(amount)'; ?> ', dataField: '<?php echo 'prediction_'.$serial.'_amount'; ?>',width:'120',cellsAlign:'right',hidden: <?php echo $system_preference_items['prediction_amount']?0:1;?>,cellsrenderer: cellsrenderer,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer_amount},
                    <?php
                    }
                ?>
                ],
                columngroups:
                    [
                        { text: '<?php echo $sub_column_group_name.' Budget'; ?>', align: 'center', name: 'sub_area_budget' },
                        { text: '<?php echo $sub_column_group_name.' Target'; ?>', align: 'center', name: 'sub_area_target' },
                        { text: 'Next Years Prediction', align: 'center', name: 'next_years_prediction' }
                    ]

            });
    });
</script>