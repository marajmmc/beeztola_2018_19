<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$CI=& get_instance();
$action_buttons=array();
if(isset($CI->permissions['action0']) && ($CI->permissions['action0']==1))
{
    $action_buttons[]=array(
        'label'=>'Pending List',
        'href'=>site_url($CI->controller_url)
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
$action_buttons[]=array
(
    'label'=>$CI->lang->line("ACTION_REFRESH"),
    'href'=>site_url($CI->controller_url.'/index/list_all')
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
        var url = "<?php echo site_url($CI->controller_url.'/index/get_items_all');?>";

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
                        { text: '<?php echo $CI->lang->line('LABEL_TOTAL_QUANTITY_BUDGET'); ?>', dataField: 'total_quantity_budget', width:'100', cellsAlign:'right', hidden: <?php echo $system_preference_items['total_quantity_budget']?0:1;?>},
                        { text: '<?php echo $CI->lang->line('LABEL_TOTAL_AMOUNT_PRICE_NET'); ?>', dataField: 'total_amount_price_net', width:'100', cellsAlign:'right', hidden: <?php echo $system_preference_items['total_amount_price_net']?0:1;?>},
                        { text: '<?php echo $CI->lang->line('LABEL_STATUS_FORWARD'); ?>', dataField: 'status_forward', width:'100',filtertype: 'list',hidden: <?php echo $system_preference_items['status_forward']?0:1;?>},
                        { text: '<?php echo $CI->lang->line('LABEL_STATUS_FINALIZE'); ?>', dataField: 'status_finalize', width:'100',filtertype: 'list',hidden: <?php echo $system_preference_items['status_finalize']?0:1;?>}
                    ]
            });
    });
</script>