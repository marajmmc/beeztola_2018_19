<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$CI=& get_instance();
$action_buttons=array();
if((isset($CI->permissions['action1']) && ($CI->permissions['action1']==1))||(isset($CI->permissions['action2']) && ($CI->permissions['action2']==1)))
{
    $action_buttons[]=array(
        'type'=>'button',
        'label'=>$CI->lang->line("ACTION_SAVE"),
        'id'=>'button_action_save_jqx'
    );
}
$action_buttons[]=array
(
    'label'=>$CI->lang->line("ACTION_BACK"),
    'href'=>site_url($CI->controller_url.'/index/list_budget_dealer/'.$options['fiscal_year_id'].'/'.$options['outlet_id'])
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
    <div style="" class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_FISCAL_YEAR');?></label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo $item['fiscal_year']['name'];?></label>
        </div>
    </div>
    <div style="" class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_OUTLET');?></label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo $item['outlet']['name'];?></label>
        </div>
    </div>
    <div style="" class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DEALER_NAME');?></label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo $item['dealer']['farmer_name'];?></label>
        </div>
    </div>
    <form id="save_form_jqx" action="<?php echo site_url($CI->controller_url.'/index/save_budget_dealer');?>" method="post">
        <input type="hidden" name="item[fiscal_year_id]" value="<?php echo $options['fiscal_year_id']; ?>" />
        <input type="hidden" name="item[outlet_id]" value="<?php echo $options['outlet_id']; ?>" />
        <input type="hidden" name="item[dealer_id]" value="<?php echo $options['dealer_id']; ?>" />
        <div id="jqx_inputs">
        </div>
    </form>
    <div class="col-xs-12" id="system_jqx_container">

    </div>
</div>

<div class="clearfix"></div>
<script type="text/javascript">
    $(document).ready(function ()
    {
        system_off_events();
        system_preset({controller:'<?php echo $CI->router->class; ?>'});
        $(document).on("click", "#button_action_save_jqx", function(event)
        {
            $('#save_form_jqx #jqx_inputs').html('');
            var data=$('#system_jqx_container').jqxGrid('getrows');
            for(var i=0;i<data.length;i++)
            {
                $('#save_form_jqx  #jqx_inputs').append('<input type="hidden" name="items['+data[i]['variety_id']+']" value="'+data[i]['quantity_budget']+'">');


            }
            var sure = confirm('<?php echo $CI->lang->line('MSG_CONFIRM_SAVE'); ?>');
            if(sure)
            {
                $("#save_form_jqx").submit();
            }
        });

        var url = "<?php echo site_url($CI->controller_url.'/index/get_items_edit_budget_dealer');?>";

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
            url: url,
            data:JSON.parse('<?php echo json_encode($options);?>')
        };
        var cellsrenderer = function(row, column, value, defaultHtml, columnSettings, record)
        {
            var element = $(defaultHtml);
            if(column=='quantity_budget')
            {
                /*if(value==0)
                {
                    element.css({'margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
                }
                else
                {
                    element.css({'margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
                }*/
                element.css({'margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
                element.html('<div class="jqxgrid_input">'+value+'</div>');
            }
            return element[0].outerHTML;
        };

        var dataAdapter = new $.jqx.dataAdapter(source);
        // create jqxgrid.
        $("#system_jqx_container").jqxGrid(
            {
                source: dataAdapter,
                width: '100%',
                height: '350px',
                filterable: true,
                sortable: true,
                showfilterrow: true,
                columnsresize: true,
                columnsreorder: true,
                enablebrowserselection: true,
                selectionmode: 'singlerow',
                altrows: true,
                rowsheight: 35,
                editable:true,
                columns:
                [
                    { text: '<?php echo $CI->lang->line('LABEL_CROP_NAME'); ?>', dataField: 'crop_name',width:'100', filtertype:'list',pinned:true,editable:false},
                    { text: '<?php echo $CI->lang->line('LABEL_CROP_TYPE_NAME'); ?>', dataField: 'crop_type_name',width:'100', filtertype:'list',pinned:true,editable:false},
                    { text: '<?php echo $CI->lang->line('LABEL_VARIETY_NAME'); ?>', dataField: 'variety_name',width:'150', filtertype:'list',pinned:true,editable:false},
                    { text: '<?php echo $CI->lang->line('LABEL_QUANTITY_BUDGET_KG'); ?>',datafield: 'quantity_budget', width: 100,filterable: false,cellsrenderer: cellsrenderer,cellsalign: 'right',columntype: 'custom',
                        initeditor: function (row, cellvalue, editor, celltext, pressedkey)
                        {
                            editor.html('<div style="margin: 0px;width: 100%;height: 100%;padding: 5px;"><input style="z-index: 1 !important;" type="text" value="'+cellvalue+'" class="jqxgrid_input float_type_positive"><div>');
                        },
                        geteditorvalue: function (row, cellvalue, editor)
                        {
                            // return the editor's value.
                            var value=editor.find('input').val();
                            var selectedRowData = $('#system_jqx_container').jqxGrid('getrowdata', row);
                            return editor.find('input').val();
                        }
                    }
                ]
            });
    });
</script>
