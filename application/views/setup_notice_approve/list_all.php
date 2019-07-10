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
if(isset($CI->permissions['action3']) && ($CI->permissions['action3']==1))
{
    $action_buttons[]=array(
        'type'=>'button',
        'label'=>'Delete',
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
        'href'=>site_url($CI->controller_url.'/index/set_preference_all')
    );
}
$action_buttons[]=array(
    'label'=>$CI->lang->line("ACTION_REFRESH"),
    'href'=>site_url($CI->controller_url.'/index/list_all')
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
        system_off_events();
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
                    if(($key=='id'))
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
            url: url
        };

        var dataAdapter = new $.jqx.dataAdapter(source);
        // create jqxgrid.
        $("#system_jqx_container").jqxGrid(
            {
                width: '100%',
                height: '350px',
                source: dataAdapter,
                filterable: true,
                sortable: true,
                showfilterrow: true,
                columnsresize: true,
                pageable: true,
                pagesize:50,
                pagesizeoptions: ['50', '100', '200','300','500','1000','5000'],
                selectionmode: 'singlerow',
                altrows: true,
               /* rowsheight: 35,
                columnsheight: 40,*/
                columnsreorder: true,
                enablebrowserselection: true,
                columns:
                [
                    { text: '<?php echo $CI->lang->line('LABEL_NOTICE_ID'); ?>', dataField: 'id',width:'50',cellsAlign:'right',hidden: <?php echo $system_preference_items['id']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_NOTICE_TYPE'); ?>', dataField: 'notice_type',width:'150', hidden: <?php echo $system_preference_items['notice_type']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_DATE_PUBLISH'); ?>', dataField: 'date_publish',width:'100', hidden: <?php echo $system_preference_items['date_publish']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_EXPIRE_DAY'); ?>', dataField: 'expire_day',width:'50', hidden: <?php echo $system_preference_items['expire_day']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_REMAINING_DAY'); ?>', dataField: 'remaining_day',width:'50', hidden: <?php echo $system_preference_items['remaining_day']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_TITLE'); ?>', dataField: 'title',width:'500', hidden: <?php echo $system_preference_items['title']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_DESCRIPTION'); ?>',dataField: 'description',width:'500',hidden: <?php echo $system_preference_items['description']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_REVISION_COUNT'); ?>',dataField: 'revision_count',width:'50',cellsAlign:'right',hidden: <?php echo $system_preference_items['revision_count']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_ORDERING'); ?>',dataField: 'ordering',width:'50',cellsAlign:'right',hidden: <?php echo $system_preference_items['ordering']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_STATUS'); ?>',dataField: 'status',width:'80',filtertype: 'list',hidden: <?php echo $system_preference_items['status']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_STATUS_FORWARD'); ?>',dataField: 'status_forward',width:'80',filtertype: 'list',hidden: <?php echo $system_preference_items['status_forward']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_STATUS_APPROVE'); ?>',dataField: 'status_approve',width:'80',filtertype: 'list',hidden: <?php echo $system_preference_items['status_approve']?0:1;?>}
                ]
            });
    });
</script>
