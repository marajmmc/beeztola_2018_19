<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$CI=& get_instance();
$action_buttons=array();
if(isset($CI->permissions['action0']) && ($CI->permissions['action0']==1))
{
    $action_buttons[]=array(
        'label'=>'All List',
        'href'=>site_url($CI->controller_url.'/index/list_all')
    );
}
if((isset($CI->permissions['action2']) && ($CI->permissions['action2']==1)))
{
    $action_buttons[]=array
    (
        'type'=>'button',
        'label'=>'Target Edit',
        'class'=>'button_jqx_action',
        'data-action-link'=>site_url($CI->controller_url.'/index/edit_target')
    );
}
if(isset($CI->permissions['action0']) && ($CI->permissions['action0']==1))
{
    $action_buttons[]=array
    (
        'type'=>'button',
        'label'=>$CI->lang->line('ACTION_DETAILS'),
        'class'=>'button_jqx_action',
        'data-action-link'=>site_url($CI->controller_url.'/index/details')
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
if(isset($CI->permissions['action6']) && ($CI->permissions['action6']==1))
{
    $action_buttons[]=array
    (
        'label'=>'Preference',
        'href'=>site_url($CI->controller_url.'/index/set_preference')
    );
}
if((isset($CI->permissions['action7']) && ($CI->permissions['action7']==1)))
{
    $action_buttons[]=array
    (
        'type'=>'button',
        'label'=>'Approve & Rollback',
        'class'=>'button_jqx_action',
        'data-action-link'=>site_url($CI->controller_url.'/index/approve_rollback')
    );
}
$action_buttons[]=array
(
    'label'=>$CI->lang->line("ACTION_REFRESH"),
    'href'=>site_url($CI->controller_url.'/index/list')
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
    <?php
    if(isset($CI->permissions['action6']) && ($CI->permissions['action6']==1))
    {
        $CI->load->view('preference',array('system_preference_items'=>$system_preference_items));
    }
    ?>
    <div class="col-xs-12" id="system_jqx_container">

    </div>
</div>
<div class="clearfix"></div>
<script type="text/javascript">
    $(document).ready(function ()
    {
        system_preset({controller:'<?php echo $CI->router->class; ?>'});
        var url = "<?php echo site_url($CI->controller_url.'/index/get_items');?>";

        // prepare the data
        var source =
        {
            dataType: "json",
            dataFields: [
                <?php
                 foreach($system_preference_items as $key=>$item)
                 {
                    ?>
                { name: '<?php echo $key ?>', type: 'string' },
                <?php
             }
            ?>
            ],
            id: 'id',
            type: 'POST',
            url: url
        };

        var dataAdapter = new $.jqx.dataAdapter(source);
        var cellsrenderer = function(row, column, value, defaultHtml, columnSettings, record)
        {
            var element = $(defaultHtml);
            if(column=='total_quantity_budget')
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
            else if(column=='total_quantity_budget_kg')
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
            else if(column=='total_amount_price_net')
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
            else if(column=='total_quantity_target')
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
            else if(column=='total_quantity_target_kg')
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
            else if(column=='total_amount_target_price_net')
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
        // create jqxgrid.
        $("#system_jqx_container").jqxGrid(
            {
                width: '100%',
                source: dataAdapter,
                pageable: true,
                filterable: true,
                sortable: true,
                showfilterrow: true,
                columnsresize: true,
                pagesize:50,
                pagesizeoptions: ['50', '100', '200','300','500','1000','5000'],
                selectionmode: 'singlerow',
                altrows: true,
                height: '350px',
                columnsreorder: true,
                enablebrowserselection: true,
                columns:
                [
                    { text: '<?php echo $CI->lang->line('LABEL_ID'); ?>', dataField: 'id', width:'50', cellsAlign:'right',hidden: <?php echo $system_preference_items['id']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_OUTLET_NAME'); ?>',dataField: 'outlet_name',width:'200',filterType:'list',hidden: <?php echo $system_preference_items['outlet_name']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_YEAR'); ?>', dataField: 'year',width:'80',filtertype: 'list', hidden: <?php echo $system_preference_items['year']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_MONTH'); ?>', dataField: 'month', width:'100',filtertype: 'list',hidden: <?php echo $system_preference_items['month']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_TOTAL_QUANTITY_BUDGET'); ?>', dataField: 'total_quantity_budget', width:'150', cellsAlign:'right',cellsrenderer: cellsrenderer, hidden: <?php echo $system_preference_items['total_quantity_budget']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_TOTAL_QUANTITY_BUDGET_KG'); ?>', dataField: 'total_quantity_budget_kg', width:'150', cellsAlign:'right',cellsrenderer: cellsrenderer, hidden: <?php echo $system_preference_items['total_quantity_budget_kg']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_TOTAL_AMOUNT_PRICE_NET'); ?>', dataField: 'total_amount_price_net', width:'200', cellsAlign:'right',cellsrenderer: cellsrenderer, hidden: <?php echo $system_preference_items['total_amount_price_net']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_TOTAL_QUANTITY_TARGET'); ?>', dataField: 'total_quantity_target', width:'150', cellsAlign:'right',cellsrenderer: cellsrenderer, hidden: <?php echo $system_preference_items['total_quantity_target']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_TOTAL_QUANTITY_TARGET_KG'); ?>', dataField: 'total_quantity_target_kg', width:'150', cellsAlign:'right',cellsrenderer: cellsrenderer, hidden: <?php echo $system_preference_items['total_quantity_target_kg']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_TOTAL_AMOUNT_TARGET_PRICE_NET'); ?>', dataField: 'total_amount_target_price_net', width:'200', cellsAlign:'right',cellsrenderer: cellsrenderer, hidden: <?php echo $system_preference_items['total_amount_target_price_net']?0:1;?>}
                ]
            });
    });
</script>
