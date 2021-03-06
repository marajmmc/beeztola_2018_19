<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$CI=& get_instance();
$action_buttons=array();
$action_buttons[]=array
(
    'label'=>$CI->lang->line("ACTION_BACK"),
    'href'=>site_url($CI->controller_url)
);
if((isset($CI->permissions['action1']) && ($CI->permissions['action1']==1))||(isset($CI->permissions['action2']) && ($CI->permissions['action2']==1)))
{
    $action_buttons[]=array
    (
        'type'=>'button',
        'label'=>$CI->lang->line('ACTION_EDIT'),
        'class'=>'button_jqx_action',
        'data-action-link'=>site_url($CI->controller_url.'/index/edit_budget_dealer/'.$options['fiscal_year_id'].'/'.$options['outlet_id'])

    );
}
if(isset($CI->permissions['action4']) && ($CI->permissions['action4']==1))
{
    $action_buttons[]=array(
        'type'=>'button',
        'label'=>$CI->lang->line("ACTION_PRINT"),
        'class'=>'button_action_download',
        'data-title'=>"Print",
        'data-print'=>true
    );
}
if(isset($CI->permissions['action5']) && ($CI->permissions['action5']==1))
{
    $action_buttons[]=array(
        'type'=>'button',
        'label'=>$CI->lang->line("ACTION_DOWNLOAD"),
        'class'=>'button_action_download',
        'data-title'=>"Download"
    );
}
$action_buttons[]=array
(
    'label'=>$CI->lang->line("ACTION_REFRESH"),
    'href'=>site_url($CI->controller_url.'/index/list_budget_dealer/'.$options['fiscal_year_id'].'/'.$options['outlet_id'])
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
            <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_OUTLET');?></label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo $outlet['name'];?></label>
        </div>
    </div>
    <div class="col-xs-12" id="system_jqx_container">

    </div>
</div>
<div class="clearfix"></div>
<script type="text/javascript">
    $(document).ready(function ()
    {
        system_off_events();
        system_preset({controller:'<?php echo $CI->router->class; ?>'});
        var url = "<?php echo site_url($CI->controller_url.'/index/get_items_budget_dealer');?>";

        // prepare the data
        var source =
        {
            dataType: "json",
            dataFields: [
                <?php
                foreach($system_preference_items as $key => $value)
                {
                    if($key=='id' ||(substr($key, 0, 10)=='number_of_'))
                    {
                        ?>
                        { name: '<?php echo $key ?>', type: 'number' },
                        <?php
                    }
                    else
                    {
                        ?>
                        { name: '<?php echo $key ?>', type: 'string' },
                        <?php
                    }
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
            if(column.substr(0,10)=='number_of_')
            {
                if(value==0)
                {
                    element.html('');
                }
                else if(value>0)
                {
                    element.html(get_string_quantity(value));
                }
            }
            element.css({'margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
            return element[0].outerHTML;
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
            showfilterrow: true,
            columnsresize: true,
            pageable: true,
            pagesize:50,
            pagesizeoptions: ['50', '100', '200','300','500','1000','5000'],
            selectionmode: 'singlerow',
            altrows: true,
            rowsheight: 35,
            /*columnsheight: 40,*/
            columnsreorder: true,
            enablebrowserselection: true,
            columns:
            [
                { text: '<?php echo $CI->lang->line('LABEL_NAME'); ?>', dataField: 'farmer_name', width:200,cellsrenderer: cellsrenderer},
                { text: '<?php echo $CI->lang->line('LABEL_MOBILE_NO'); ?>', dataField: 'mobile_no', width:120,cellsrenderer: cellsrenderer},
                { columngroup: 'number_of_variety',text: 'Active', dataField: 'number_of_variety_active',width:'50', cellsalign:'right', align:'right',cellsrenderer: cellsrenderer},
                { columngroup: 'number_of_variety',text: 'Budgeted', dataField: 'number_of_variety_budgeted',width:'70', cellsalign:'right', align:'right',cellsrenderer: cellsrenderer},
                { columngroup: 'number_of_variety',text: 'Due', dataField: 'number_of_variety_budget_due',width:'50', cellsalign:'right', align:'right',cellsrenderer: cellsrenderer}
            ],
            columngroups:
            [
                { text: 'Number of Variety', align: 'center', name: 'number_of_variety' }
            ]
        });
    });
</script>
