<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$CI=& get_instance();
$action_buttons=array();
if(isset($CI->permissions['action1']) && ($CI->permissions['action1']==1))
{
    $action_buttons[]=array
    (
        'label'=>$CI->lang->line("ACTION_NEW"),
        'href'=>site_url($CI->controller_url.'/index/add')
    );
}
if(isset($CI->permissions['action2']) && ($CI->permissions['action2']==1))
{
    $action_buttons[]=array
    (
        'type'=>'button',
        'label'=>$CI->lang->line("ACTION_EDIT"),
        'class'=>'button_jqx_action',
        'data-action-link'=>site_url($CI->controller_url.'/index/edit')
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
        var url = "<?php echo site_url($CI->controller_url.'/index/get_items'); ?>";
        var source =
        {
            dataType: "json",
            dataFields: [
                { name: 'id', type: 'int' },
                { name: 'name', type: 'string' },
                { name: 'ordering', type: 'int' },
                { name: 'status', type: 'string' }
            ],
            id: 'id',
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
            pagesize:20,
            pagesizeoptions: ['20', '50', '100', '200','300','500'],
            selectionmode: 'singlerow',
            altrows: true,
            autoheight: true,
            enablebrowserselection:true,
            columnsreorder: true,
            columns: [
                { text: '<?php echo $CI->lang->line('LABEL_NAME'); ?>', dataField: 'name'},
                { text: '<?php echo $CI->lang->line('LABEL_ORDER'); ?>', dataField: 'ordering',width:'150',cellsalign: 'right'},
                { text: '<?php echo $CI->lang->line('STATUS'); ?>', dataField: 'status',width:'150',filtertype: 'list',cellsalign: 'right'}
            ]
        });
    });
</script>