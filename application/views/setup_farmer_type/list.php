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
    $action_buttons[]=array
    (
        'type'=>'button',
        'label'=>$CI->lang->line('ACTION_EDIT'),
        'class'=>'button_jqx_action',
        'data-action-link'=>site_url($CI->controller_url.'/index/edit')
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
        'label'=>'Outlet Discount',
        'class'=>'button_jqx_action',
        'data-action-link'=>site_url($CI->controller_url.'/index/outlet_discount_list')
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

        // prepare the data
        var source =
        {
            dataType: "json",
            dataFields: [
                <?php
                 foreach($system_preference_items as $key=>$item)
                 {
                     if(($key=='name'))
                     {
                         ?>
                    { name: '<?php echo $key ?>', type: 'string' },
                    <?php
                 }
                 else
                 {
                    ?>
                    { name: '<?php echo $key ?>', type: 'number' },
                    <?php
                    }
                }
                ?>
            ],
            id: 'id',
            url: url
        };

        var dataAdapter = new $.jqx.dataAdapter(source);
        // create jqxgrid.
        $("#system_jqx_container").jqxGrid(
            {
                width: '100%',
                height: '350px',
                source: dataAdapter,
                pageable: true,
                filterable: true,
                sortable: true,
                showfilterrow: true,
                columnsresize: true,
                pagesize:50,
                pagesizeoptions: ['20', '50', '100', '200','300','500'],
                selectionmode: 'singlerow',
                altrows: true,
                columnsreorder: true,
                columns:
                [
                    { text: '<?php echo $CI->lang->line('LABEL_ID'); ?>', dataField: 'id',width:'50',cellsAlign:'right',hidden: <?php echo $system_preference_items['id']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_NAME'); ?>', dataField: 'name', hidden: <?php echo $system_preference_items['name']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_DISCOUNT_SELF_PERCENTAGE'); ?>',dataField: 'discount_self_percentage',filterType:'list',width:'100', cellsAlign:'right',hidden: <?php echo $system_preference_items['discount_self_percentage']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_DISCOUNT_REFERRAL_PERCENTAGE'); ?>',dataField: 'discount_referral_percentage',filterType:'list',width:'100', cellsAlign:'right',hidden: <?php echo $system_preference_items['discount_referral_percentage']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_COMMISSION_DISTRIBUTOR'); ?>',dataField: 'commission_distributor',filterType:'list',width:'100', cellsAlign:'right', hidden: <?php echo $system_preference_items['commission_distributor']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_ALLOW_OFFER'); ?>',dataField: 'allow_offer',filterType:'list',width:'100', cellsAlign:'right', hidden: <?php echo $system_preference_items['allow_offer']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_ALLOW_DISCOUNT'); ?>',dataField: 'allow_discount',filterType:'list',width:'100', cellsAlign:'right', hidden: <?php echo $system_preference_items['allow_discount']?0:1;?>}
                ]
            });
    });
</script>
