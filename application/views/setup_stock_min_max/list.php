<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$CI = & get_instance();
if((isset($CI->permissions['action1']) && ($CI->permissions['action1']==1)) || (isset($CI->permissions['action2']) && ($CI->permissions['action2']==1)))
{
    $action_buttons[]=array(
        'type'=>'button',
        'label'=>$CI->lang->line("ACTION_SAVE"),
        'id'=>'button_action_save_jqx'
    );
}
$CI->load->view('action_buttons',array('action_buttons'=>$action_buttons));
?>
<form id="save_form_jqx" action="<?php echo site_url($CI->controller_url.'/index/save');?>" method="post">
    <input type="hidden" name="item[outlet_id]" value="<?php echo $options['outlet_id']; ?>" />
    <input type="hidden" name="item[crop_id]" value="<?php echo $options['crop_id']; ?>" />
    <div id="jqx_inputs">

    </div>
</form>
<div class="row widget">
    <div class="col-xs-12" id="system_jqx_container"></div>
</div>
<script type="text/javascript">
    $(document).ready(function ()
    {
        system_preset({controller:'<?php echo $CI->router->class; ?>'});

        $(document).off('click', '#button_action_save_jqx');
        $(document).on("click", "#button_action_save_jqx", function(event)
        {
            $('#save_form_jqx #jqx_inputs').html('');
            var data=$('#system_jqx_container').jqxGrid('getrows');
            for(var i=0;i<data.length;i++)
            {
                $('#save_form_jqx  #jqx_inputs').append('<input type="hidden" name="items['+data[i]['variety_id']+']['+data[i]['pack_size_id']+'][quantity_min]" value="'+data[i]['quantity_min']+'">');
                $('#save_form_jqx  #jqx_inputs').append('<input type="hidden" name="items['+data[i]['variety_id']+']['+data[i]['pack_size_id']+'][quantity_max]" value="'+data[i]['quantity_max']+'">');
            }
            var sure = confirm('<?php echo $CI->lang->line('MSG_CONFIRM_SAVE'); ?>');
            if(sure)
            {
                $("#save_form_jqx").submit();
                //return false;
            }

        });



        var url = "<?php echo site_url($CI->controller_url.'/index/get_items/');?>";
        // prepare the data
        var source =
        {
            dataType: "json",
            dataFields: [
                { name: 'id', type: 'int' },
                { name: 'variety_id', type: 'string' },
                { name: 'variety_name', type: 'string' },
                { name: 'pack_size_id', type: 'string' },
                { name: 'pack_size', type: 'string' },
                { name: 'quantity_min', type: 'string' },
                { name: 'quantity_max', type: 'string' }
            ],
            id: 'id',
            url: url,
            type: 'POST',
            data:JSON.parse('<?php echo json_encode($options);?>')
        };

        var dataAdapter = new $.jqx.dataAdapter(source);
        var cellsrenderer = function(row, column, value, defaultHtml, columnSettings, record)
        {
            var element = $(defaultHtml);
            element.css({'margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
            //if(record[column+'_editable'])
            if(column=='quantity_min' || column=='quantity_max')
            {
                element.html('<div class="jqxgrid_input">'+value+'</div>');
                //console.log(value);
            }
            return element[0].outerHTML;
        };
        // create jqxgrid.
        $("#system_jqx_container").jqxGrid(
        {
            width: '100%',
            height: '350px',
            source: dataAdapter,
            columnsresize: true,
            columnsreorder: true,
            enablebrowserselection: true,
            altrows: true,
            rowsheight: 35,
            editable:true,
            columns: [
                { text: '<?php echo $CI->lang->line('LABEL_VARIETY_NAME'); ?>', dataField: 'variety_name',width:'200',editable:false},
                { text: '<?php echo $CI->lang->line('LABEL_PACK_SIZE'); ?>', dataField: 'pack_size',width:'200',editable:false},
                {
                    text: 'Min <?php echo $CI->lang->line('LABEL_QUANTITY'); ?> (<?php echo $CI->lang->line('LABEL_KG'); ?>)', dataField: 'quantity_min', width:'200',cellsalign: 'right', cellsrenderer: cellsrenderer, columntype:'custom',
                    editable:true,
                    cellbeginedit: function (row)
                    {
                        return <?php if((isset($CI->permissions['action2']) && ($CI->permissions['action2']==1))){ echo 'true';}else{echo 'false';}?>;
                    },
                    initeditor: function (row, cellvalue, editor, celltext, pressedkey)
                    {
                        editor.html('<div style="margin: 0px;width: 100%;height: 100%;padding: 5px;"><input type="text" value="'+cellvalue+'" class="jqxgrid_input float_type_positive"><div>');
                    },
                    geteditorvalue: function (row, cellvalue, editor)
                    {
                        // return the editor's value.
                        var value=editor.find('input').val();
                        var selectedRowData = $('#system_jqx_container').jqxGrid('getrowdata', row);
                        return editor.find('input').val();
                    }
                },
                {
                    text: 'Max <?php echo $CI->lang->line('LABEL_QUANTITY'); ?> (<?php echo $CI->lang->line('LABEL_KG'); ?>)', dataField: 'quantity_max',width:'200',cellsalign: 'right',cellsrenderer: cellsrenderer, columntype:'custom',
                    editable:true,
                    cellbeginedit: function (row)
                    {
                        return <?php if((isset($CI->permissions['action2']) && ($CI->permissions['action2']==1))){ echo 'true';}else{echo 'false';}?>;
                    },
                    initeditor: function (row, cellvalue, editor, celltext, pressedkey)
                    {
                        editor.html('<div style="margin: 0px;width: 100%;height: 100%;padding: 5px;"><input type="text" value="'+cellvalue+'" class="jqxgrid_input float_type_positive"><div>');
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
