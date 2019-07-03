<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$CI=& get_instance();
$action_buttons=array();
if(isset($CI->permissions['action1']) && ($CI->permissions['action1']==1))
{
    $action_buttons[]=array(
        'label'=>$CI->lang->line("ACTION_NEW"),
        'href'=>site_url($CI->controller_url.'/index/add')
    );
}
if(isset($CI->permissions['action2']) && ($CI->permissions['action2']==1))
{
    $action_buttons[]=array(
        'type'=>'button',
        'label'=>$CI->lang->line("ACTION_EDIT"),
        'class'=>'button_jqx_action',
        'data-action-link'=>site_url($CI->controller_url.'/index/edit')
    );
    $action_buttons[]=array(
        'type'=>'button',
        'label'=>'Assign outlet',
        'class'=>'button_jqx_action',
        'data-action-link'=>site_url($CI->controller_url.'/index/edit_outlet')
    );
    $action_buttons[]=array(
        'type'=>'button',
        'label'=>'Change Credit Limit',
        'class'=>'button_jqx_action',
        'data-action-link'=>site_url($CI->controller_url.'/index/edit_credit_limit')
    );
}
$action_buttons[]=array(
    'type'=>'button',
    'label'=>$CI->lang->line("ACTION_DETAILS"),
    'class'=>'button_jqx_action',
    'data-action-link'=>site_url($CI->controller_url.'/index/details')
);
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
$action_buttons[]=array(
    'label'=>$CI->lang->line("ACTION_REFRESH"),
    'href'=>site_url($CI->controller_url.'/index/list')

);
$action_buttons[]=array(
    'type'=>'button',
    'label'=>$CI->lang->line("ACTION_LOAD_MORE"),
    'id'=>'button_jqx_load_more'
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
        var url="<?php echo site_url($CI->controller_url.'/index/get_items');?>";

        // prepare the data
        var source =
        {
            dataType: "json",
            dataFields: [
                { name: 'id', type: 'int' },
                { name: 'barcode', type: 'string' },
                { name: 'name', type: 'string' },
                { name: 'amount_credit_limit', type: 'number' },
                { name: 'amount_credit_balance', type: 'number' },
                { name: 'amount_credit_due', type: 'number' },
                { name: 'date_created_time', type: 'string' },
                { name: 'farmer_type_name', type: 'string' },
                { name: 'status_card_require', type: 'string' },
                { name: 'mobile_no', type: 'string' },
                { name: 'nid', type: 'string' },
                { name: 'address', type: 'string' },
                { name: 'division_name', type: 'string' },
                { name: 'zone_name', type: 'string' },
                { name: 'territory_name', type: 'string' },
                { name: 'district_name', type: 'string' },
                { name: 'upazilla_name', type: 'string' },
                { name: 'union_name', type: 'string' },
                { name: 'status', type: 'string' }
            ],
            id: 'id',
            type: 'POST',
            url: url
        };

        var dataAdapter = new $.jqx.dataAdapter(source);
        var cellsrenderer = function(row, column, value, defaultHtml, columnSettings, record)
        {
            var element = $(defaultHtml);
            if(column.substr(0,6)=='amount')
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
                enablebrowserselection:true,
                columnsreorder: true,
                columns: [
                    { text: '<?php echo $CI->lang->line('LABEL_BARCODE'); ?>', dataField: 'barcode', width:80, hidden: <?php echo $system_preference_items['barcode']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_NAME'); ?>', dataField: 'name', width:200, hidden: <?php echo $system_preference_items['name']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_AMOUNT_CREDIT_LIMIT'); ?>', dataField: 'amount_credit_limit', width:100,cellsrenderer: cellsrenderer,cellsalign: 'right',hidden: <?php echo $system_preference_items['amount_credit_limit']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_AMOUNT_CREDIT_BALANCE'); ?>', dataField: 'amount_credit_balance', width:100,cellsrenderer: cellsrenderer,cellsalign: 'right',hidden: <?php echo $system_preference_items['amount_credit_balance']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_AMOUNT_CREDIT_DUE'); ?>', dataField: 'amount_credit_due', width:100,cellsrenderer: cellsrenderer,cellsalign: 'right',hidden: <?php echo $system_preference_items['amount_credit_due']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_DATE_CREATED_TIME'); ?>', dataField: 'date_created_time', width:200, hidden: <?php echo $system_preference_items['date_created_time']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_FARMER_TYPE_NAME'); ?>', dataField: 'farmer_type_name', width:150,filtertype: 'list', hidden: <?php echo $system_preference_items['farmer_type_name']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_STATUS_CARD_REQUIRE'); ?>', dataField: 'status_card_require', width:50,filtertype: 'list', hidden: <?php echo $system_preference_items['status_card_require']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_MOBILE_NO'); ?>', dataField: 'mobile_no', width:150,hidden: <?php echo $system_preference_items['mobile_no']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_NID'); ?>', dataField: 'nid', width:150,hidden: <?php echo $system_preference_items['nid']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_ADDRESS'); ?>', dataField: 'address', width:150,hidden: <?php echo $system_preference_items['address']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_DIVISION_NAME'); ?>', dataField: 'division_name',filtertype: 'list', width:100,hidden: <?php echo $system_preference_items['division_name']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_ZONE_NAME'); ?>', dataField: 'zone_name', width:100,hidden: <?php echo $system_preference_items['zone_name']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_TERRITORY_NAME'); ?>', dataField: 'territory_name', width:100,hidden: <?php echo $system_preference_items['territory_name']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_DISTRICT_NAME'); ?>', dataField: 'district_name', width:100,hidden: <?php echo $system_preference_items['district_name']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_UPAZILLA_NAME'); ?>', dataField: 'upazilla_name', width:100,hidden: <?php echo $system_preference_items['upazilla_name']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_UNION_NAME'); ?>', dataField: 'union_name', width:100,hidden: <?php echo $system_preference_items['union_name']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_STATUS'); ?>', dataField: 'status', width:100,filtertype: 'list', hidden: <?php echo $system_preference_items['status']?0:1;?>}
                ]
            });
    });
</script>