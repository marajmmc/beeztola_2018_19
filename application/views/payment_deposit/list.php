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
if(isset($CI->permissions['action1']) && ($CI->permissions['action1']==1))
{
    $action_buttons[]=array(
        'label'=>$CI->lang->line("ACTION_NEW"),
        'href'=>site_url($CI->controller_url.'/index/add')
    );
}
if(isset($CI->permissions['action2']) && ($CI->permissions['action2']==1))
{
    $action_buttons[]=array
    (
        'type'=>'button',
        'label'=>$CI->lang->line('ACTION_EDIT'),
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
if(isset($CI->permissions['action3']) && ($CI->permissions['action3']==1))
{
    $action_buttons[]=array
    (
        'type'=>'button',
        'label'=>$CI->lang->line('ACTION_DELETE'),
        'data-message-confirm'=>'Are you sure to delete this Payment?',
        'class'=>'button_jqx_action',
        'data-action-link'=>site_url($CI->controller_url.'/index/delete')
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
        'label'=>'Forward',
        'class'=>'button_jqx_action',
        'data-action-link'=>site_url($CI->controller_url.'/index/forward')
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
                { name: 'barcode', type: 'string' },
                { name: 'date_payment', type: 'string'},
                { name: 'date_sale', type: 'string'},
                { name: 'outlet_name', type: 'string'},
                { name: 'payment_way', type: 'string'},
                { name: 'reference_no', type: 'string'},
                { name: 'amount_payment', type: 'string'},
                { name: 'amount_cash_sale_payment', type: 'string'},
                { name: 'amount_credit_sale_payment', type: 'string'},
                { name: 'bank_payment_source', type: 'string'},
                { name: 'bank_branch_source', type: 'string'},
                { name: 'bank_account_number_destination', type: 'string'}
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
                        { text: '<?php echo $CI->lang->line('LABEL_BARCODE'); ?>', dataField: 'barcode',width:'80',hidden: <?php echo $system_preference_items['barcode']?0:1;?>},
                        { text: '<?php echo $CI->lang->line('LABEL_DATE_PAYMENT'); ?>', dataField: 'date_payment',width:'100',hidden: <?php echo $system_preference_items['date_payment']?0:1;?>},
                        { text: '<?php echo $CI->lang->line('LABEL_DATE_SALE'); ?>', dataField: 'date_sale',width:'100',hidden: <?php echo $system_preference_items['date_sale']?0:1;?>},
                        { text: '<?php echo $CI->lang->line('LABEL_OUTLET_NAME'); ?>',dataField: 'outlet_name',width:'100',filterType:'list',hidden: <?php echo $system_preference_items['outlet_name']?0:1;?>},
                        { text: '<?php echo $CI->lang->line('LABEL_PAYMENT_WAY'); ?>',dataField: 'payment_way',width:'100',filterType:'list',hidden: <?php echo $system_preference_items['payment_way']?0:1;?>},
                        { text: '<?php echo $CI->lang->line('LABEL_REFERENCE_NO'); ?>',dataField: 'reference_no',width:'100',hidden: <?php echo $system_preference_items['reference_no']?0:1;?>},
                        { text: '<?php echo $CI->lang->line('LABEL_AMOUNT_PAYMENT'); ?>',dataField: 'amount_payment',width:'150',cellsAlign:'right', hidden: <?php echo $system_preference_items['amount_payment']?0:1;?>},
                        { text: '<?php echo $CI->lang->line('LABEL_AMOUNT_CASH_SALE_PAYMENT'); ?>',dataField: 'amount_cash_sale_payment',width:'150',cellsAlign:'right',hidden: <?php echo $system_preference_items['amount_cash_sale_payment']?0:1;?>},
                        { text: '<?php echo $CI->lang->line('LABEL_AMOUNT_CREDIT_SALE_PAYMENT'); ?>',dataField: 'amount_credit_sale_payment',width:'150',cellsAlign:'right',hidden: <?php echo $system_preference_items['amount_credit_sale_payment']?0:1;?>},
                        { text: '<?php echo $CI->lang->line('LABEL_BANK_PAYMENT_SOURCE'); ?>',dataField: 'bank_payment_source',filterType:'list',width:'150',hidden: <?php echo $system_preference_items['bank_payment_source']?0:1;?>},
                        { text: '<?php echo $CI->lang->line('LABEL_BANK_BRANCH_SOURCE'); ?>',dataField: 'bank_branch_source',width:'150',filterType:'list',hidden: <?php echo $system_preference_items['bank_branch_source']?0:1;?>},
                        { text: '<?php echo $CI->lang->line('LABEL_BANK_ACCOUNT_NUMBER_DESTINATION'); ?>',dataField: 'bank_account_number_destination',filterType:'list',hidden: <?php echo $system_preference_items['bank_account_number_destination']?0:1;?>}
                    ]
            });
    });
</script>
