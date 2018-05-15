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
    $action_buttons[]=array
    (
        'label'=>'Preference',
        'href'=>site_url($CI->controller_url.'/index/set_preference')
    );
}
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
        $(document).off("click", ".pop_up");
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

        var url="<?php echo site_url($CI->controller_url.'/index/get_items');?>";

        // prepare the data
        var source =
        {
            dataType: "json",
            dataFields: [
                { name: 'id', type: 'int' },
                { name: 'barcode', type: 'string' },
                { name: 'name', type: 'string' },
                { name: 'mobile_no', type: 'string' },
                { name: 'farmer_type_name', type: 'string' },
                { name: 'status_card_require', type: 'string' },
                { name: 'address', type: 'string' },
                { name: 'total_invoice', type: 'string'},
                { name: 'details_button', type: 'string' }
            ],
            id: 'id',
            type: 'POST',
            url: url,
            data:JSON.parse('<?php echo json_encode($options);?>')
        };
        var cellsrenderer = function(row, column, value, defaultHtml, columnSettings, record)
        {
            var element = $(defaultHtml);
            if(column=='details_button')
            {
                element.css({'margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
                <?php
                if(isset($CI->permissions['action7']) && ($CI->permissions['action7']==1))
                {
                    ?>
                element.html('<div><button class="btn btn-primary pop_up" data-action-link="<?php echo site_url($CI->controller_url.'/index/details'); ?>/'+record.id+'">View Bardcode</button></div>');
                <?php
            }
            else
            {
                ?>
                element.html('<div></div>');
                <?php
            }
            ?>

            }
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
                pagesize:50,
                pagesizeoptions: ['50', '100', '200','300','500','1000','5000'],
                selectionmode: 'singlerow',
                altrows: true,
                height: '350px',
                enablebrowserselection:true,
                columnsreorder: true,
                rowsheight: 40,
                columns: [
                    {
                        text: '<?php echo $CI->lang->line('LABEL_SL_NO'); ?>',datafield: 'sl_no',width:'30', columntype: 'number',cellsalign: 'right', sortable: false, menu: false,
                        cellsrenderer: function(row, column, value, defaultHtml, columnSettings, record)
                        {
                            var element = $(defaultHtml);
                            element.html(value+1);
                            return element[0].outerHTML;
                        }
                    },
                    <?php if(isset($CI->permissions['action2']) && ($CI->permissions['action2']==1)){?>
                    { text: '<?php echo $CI->lang->line('LABEL_BARCODE'); ?>', dataField: 'barcode',width:'100',hidden: <?php echo $system_preference_items['barcode']?0:1;?>,cellsAlign:'right'},
                    <?php } ?>
                    { text: '<?php echo $CI->lang->line('LABEL_NAME'); ?>', dataField: 'name',width:'300',hidden: <?php echo $system_preference_items['name']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_MOBILE_NO'); ?>', dataField: 'mobile_no',width:'110',hidden: <?php echo $system_preference_items['mobile_no']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_FARMER_TYPE_NAME'); ?>', dataField: 'farmer_type_name',filtertype: 'list',width:'110',hidden: <?php echo $system_preference_items['farmer_type_name']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_STATUS_CARD_REQUIRE'); ?>', dataField: 'status_card_require',width:'100',hidden: <?php echo $system_preference_items['status_card_require']?0:1;?>,filtertype: 'list'},
                    { text: '<?php echo $CI->lang->line('LABEL_ADDRESS'); ?>', dataField: 'address',hidden: <?php echo $system_preference_items['address']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_TOTAL_INVOICE'); ?>', dataField: 'total_invoice',width:'80',hidden: <?php echo $system_preference_items['total_invoice']?0:1;?>,cellsAlign:'right',filtertype: 'list'},
                    <?php if(isset($CI->permissions['action7']) && ($CI->permissions['action7']==1)){?>
                    { text: '<?php echo $CI->lang->line('ACTION_DETAILS'); ?>',dataField: 'details_button',width:'150',cellsrenderer:cellsrenderer}
                    <?php } ?>


                ]
            });
    });
</script>