<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$CI=& get_instance();
$action_buttons=array();
if((isset($CI->permissions['action1']) && ($CI->permissions['action1']==1))||(isset($CI->permissions['action2']) && ($CI->permissions['action2']==1)))
{
    $action_buttons[]=array
    (
        'type'=>'button',
        'label'=>$CI->lang->line('ACTION_EDIT').' Dealer Budget',
        'class'=>'button_jqx_action',
        'data-action-link'=>site_url($CI->controller_url.'/index/list_budget_dealer')

    );
    $action_buttons[]=array
    (
        'type'=>'button',
        'label'=>$CI->lang->line('ACTION_EDIT').' Showroom Budget',
        'class'=>'button_jqx_action',
        'data-action-link'=>site_url($CI->controller_url.'/index/list_budget_outlet')

    );
}
if((isset($CI->permissions['action7']) && ($CI->permissions['action7']==1)))
{
    $action_buttons[]=array
    (
        'type'=>'button',
        'label'=>'Forward Budget',
        'class'=>'button_jqx_action',
        'data-action-link'=>site_url($CI->controller_url.'/index/budget_forward')
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
    <div class="col-xs-12" id="system_jqx_container">

    </div>
</div>
<div class="clearfix"></div>
<script type="text/javascript">
    $(document).ready(function ()
    {
        system_off_events();
        system_preset({controller:'<?php echo $CI->router->class; ?>'});
        var url = "<?php echo site_url($CI->controller_url.'/index/get_items');?>";

        // prepare the data
        var source =
        {
            dataType: "json",
            dataFields: [
                <?php
                foreach($system_preference_items as $key => $value)
                {
                    if($key=='id')
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
            id: 'id',
            type: 'POST',
            url: url
        };
        var cellsrenderer = function(row, column, value, defaultHtml, columnSettings, record)
        {
            var element = $(defaultHtml);
            var number_of_dealer_budget_due=0;
            if(column=='number_of_dealer_active' || column=='number_of_dealer_budgeted')
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
            else if(column=='number_of_dealer_budget_due')
            {
                number_of_dealer_budget_due=(parseFloat(record['number_of_dealer_active'])-parseFloat(record['number_of_dealer_budgeted']));
                if(number_of_dealer_budget_due==0)
                {
                    element.html('');
                }
                else if(number_of_dealer_budget_due>0)
                {
                    element.html(get_string_quantity(number_of_dealer_budget_due));
                }
            }
            element.css({'margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
            return element[0].outerHTML;
        };
        var dataAdapter = new $.jqx.dataAdapter(source);
        // create jqxgrid.
        $("#system_jqx_container").jqxGrid(
            {
                width: '100%',
                source: dataAdapter,
                filterable: true,
                sortable: true,
                showfilterrow: true,
                columnsresize: true,
                selectionmode: 'singlerow',
                altrows: true,
                height: '250px',
                rowsheight: 35,
                columnsreorder: true,
                enablebrowserselection: true,
                columns:
                [
                    { text: '<?php echo $CI->lang->line('LABEL_FISCAL_YEAR'); ?>', dataField: 'fiscal_year',width:'80',filtertype: 'list',cellsrenderer: cellsrenderer},
                    { text: '<?php echo $CI->lang->line('LABEL_OUTLET_NAME'); ?>', dataField: 'outlet_name',width:'200',filtertype: 'list',cellsrenderer: cellsrenderer},
                    { columngroup: 'number_of_dealer',text: 'Active', dataField: 'number_of_dealer_active',width:'100', cellsalign:'right', align:'right',cellsrenderer: cellsrenderer},
                    { columngroup: 'number_of_dealer',text: 'Budgeted', dataField: 'number_of_dealer_budgeted',width:'100', cellsalign:'right', align:'right',cellsrenderer: cellsrenderer},
                    { columngroup: 'number_of_dealer',text: 'Due Budget', dataField: 'number_of_dealer_budget_due',width:'100', cellsalign:'right', align:'right',cellsrenderer: cellsrenderer},
                    { text: '<?php echo $CI->lang->line('LABEL_STATUS_BUDGET_FORWARD'); ?>', dataField: 'status_budget_forward', width:'100',filtertype: 'list',cellsrenderer: cellsrenderer}
                ],
                columngroups:
                [
                    { text: 'Number of Dealer', align: 'center', name: 'number_of_dealer' }
                ]
            });
    });
</script>
