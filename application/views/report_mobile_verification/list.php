<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$CI=& get_instance();
$action_buttons=array();
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
    $action_buttons[]=array(
        'label'=>'Preference',
        'href'=>site_url($CI->controller_url.'/index/set_preference_list')
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
        system_off_events();
        system_preset({controller:'<?php echo $CI->router->class; ?>'});
        $(document).on("click", ".pop_up", function(event)
        {
            $('#popup_content').html('');
            var left=((($(window).width() - 550) / 2) +$(window).scrollLeft());
            var top=((($(window).height() - 550) / 2) +$(window).scrollTop());
            $("#popup_window").jqxWindow({position: { x: left, y: top  }});
            $.ajax(
                {
                    url: $(this).attr('data-action-link'),
                    type: 'POST',
                    datatype: "JSON",
                    success: function (data, status)
                    {
                    },
                    error: function (xhr, desc, err)
                    {
                        console.log("error");
                    }
                });
            $("#popup_window").jqxWindow('open');
        });

        var url = "<?php echo base_url($CI->controller_url.'/index/get_items');?>";

        // prepare the data
        var source =
        {
            dataType: "json",
            dataFields: [
                <?php
                foreach($system_preference_items as $key => $value)
                {
                    if(($key=='id')||($key=='total_try')||($key=='total_used')||($key=='total_unused'))
                    {
                    ?> { name: '<?php echo $key; ?>', type: 'number' }, <?php
                    }
                    else
                    {
                    ?> { name: '<?php echo $key; ?>', type: 'string' }, <?php
                    }
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
            element.css({'margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
            if(column=='button_details')
            {
                if(record.id>0)
                {
                    element.html('<div><button class="btn btn-primary pop_up" data-action-link="<?php echo site_url($CI->controller_url.'/index/details'); ?>/'+record.id+'">View Details</button></div>');
                }
                else
                {
                    element.html('');
                }
            }

            return element[0].outerHTML;

        };
        // create jqxgrid.
        $("#system_jqx_container").jqxGrid(
            {
                width: '100%',
                height:'400px',
                source: dataAdapter,
                pageable: true,
                filterable: true,
                sortable: true,
                showfilterrow: true,
                columnsresize: true,
                pagesize:50,
                pagesizeoptions: ['20', '50', '100', '200','300','500'],
                selectionmode: 'singlerow',
                enablebrowserselection: true,
                columnsreorder: true,

                rowsheight: 45,
                columns: [
                    { text: '<?php echo $CI->lang->line('ID'); ?>', dataField: 'id',pinned:true,width:'40',cellsAlign:'right', hidden: <?php echo $system_preference_items['id']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_EMPLOYEE_ID'); ?>', dataField: 'employee_id',pinned:true,width:'40', hidden: <?php echo $system_preference_items['employee_id']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_USERNAME'); ?>', dataField: 'username',pinned:true,width:'100', hidden: <?php echo $system_preference_items['username']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_NAME'); ?>', dataField: 'name',width:'150', hidden: <?php echo $system_preference_items['name']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_MOBILE_NO'); ?>', dataField: 'mobile_no',width:'120', hidden: <?php echo $system_preference_items['mobile_no']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_TOTAL_TRY'); ?>', dataField: 'total_try',cellsAlign:'right',width:'60', hidden: <?php echo $system_preference_items['total_try']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_TOTAL_USED'); ?>', dataField: 'total_used',filtertype: 'list',cellsAlign:'right',width:'60', hidden: <?php echo $system_preference_items['total_used']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_TOTAL_UNUSED'); ?>', dataField: 'total_unused',filtertype: 'list',cellsAlign:'right',width:'60', hidden: <?php echo $system_preference_items['total_unused']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('STATUS'); ?>', dataField: 'status',filtertype: 'list',width:'70',cellsalign: 'right', hidden: <?php echo $system_preference_items['status']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_BUTTON_DETAILS'); ?>', dataField: 'button_details',width: '120',cellsrenderer: cellsrenderer,filterable:false,hidden: <?php echo $system_preference_items['button_details']?0:1;?>}

                ]
            });
    });
</script>
