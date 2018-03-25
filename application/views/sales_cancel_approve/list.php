<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$CI=& get_instance();
$action_buttons=array();
$action_buttons[]=array(
    'label'=>'All List',
    'href'=>site_url($CI->controller_url.'/index/list_all')
);
if(isset($CI->permissions['action2']) && ($CI->permissions['action2']==1))
{
    $action_buttons[]=array
    (
        'type'=>'button',
        'label'=>'Approve/Reject',
        'class'=>'button_jqx_action',
        'data-action-link'=>site_url($CI->controller_url.'/index/edit')
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
$action_buttons[]=array(
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
        var source =
        {
            dataType: "json",
            dataFields: [
                { name: 'id', type: 'int' },
                { name: 'invoice_no', type: 'string' },
                { name: 'date_sale', type: 'string'},
                { name: 'date_cancel', type: 'string'},
                { name: 'outlet_name', type: 'string'},
                { name: 'customer_name', type: 'string'},
                { name: 'amount_actual', type: 'string'}
            ],
            id: 'id',
            type: 'POST',
            url: url
        };
        var dataAdapter = new $.jqx.dataAdapter(source);
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
                columns:
                    [
                        { text: '<?php echo $CI->lang->line('LABEL_ID'); ?>', dataField: 'id',pinned:true,width:'80',cellsAlign:'right',hidden: <?php echo $system_preference_items['id']?0:1;?>},
                        { text: '<?php echo $CI->lang->line('LABEL_INVOICE_NO'); ?>', dataField: 'invoice_no',pinned:true,width:'80',hidden: <?php echo $system_preference_items['invoice_no']?0:1;?>},
                        { text: '<?php echo $CI->lang->line('LABEL_DATE_SALE'); ?>', dataField: 'date_sale',width:'100',hidden: <?php echo $system_preference_items['date_sale']?0:1;?>},
                        { text: '<?php echo $CI->lang->line('LABEL_DATE_CANCEL'); ?>', dataField: 'date_cancel',width:'100',hidden: <?php echo $system_preference_items['date_cancel']?0:1;?>},
                        { text: '<?php echo $CI->lang->line('LABEL_OUTLET_NAME'); ?>', dataField: 'outlet_name',width:'200',filtertype: 'list', hidden: <?php echo $system_preference_items['outlet_name']?0:1;?>},
                        { text: '<?php echo $CI->lang->line('LABEL_CUSTOMER_NAME'); ?>', dataField: 'customer_name',width:'200', hidden: <?php echo $system_preference_items['customer_name']?0:1;?>},
                        { text: '<?php echo $CI->lang->line('LABEL_AMOUNT_ACTUAL'); ?>', dataField: 'amount_actual',width:'100',cellsAlign:'right',hidden: <?php echo $system_preference_items['amount_actual']?0:1;?>}

                    ]
            });
    });
</script>
