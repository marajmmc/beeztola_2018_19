<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$CI = & get_instance();
?>
<form id="save_form" action="<?php echo site_url($CI->controller_url.'/index/save');?>" method="post">
    <input type="hidden" name="customer_id" value="<?php //echo $customer_id; ?>" />
    <div style="overflow-x: auto;" class="row show-grid" id="order_items_container">
        <div id="system_jqx_container"></div>
    </div>
</form>
<script type="text/javascript">
    $(document).ready(function ()
    {
        system_preset({controller:'<?php echo $CI->router->class; ?>'});

        var url = "<?php echo site_url($CI->controller_url.'/index/get_items/');?>";
        // prepare the data
        var source =
        {
            dataType: "json",
            type:'POST',
            dataFields: [
                { name: 'id', type: 'int' },
                { name: 'division_name', type: 'string' },
                { name: 'zone_name', type: 'string' },
                { name: 'territory_name', type: 'string' },
                { name: 'district_name', type: 'string' },
                { name: 'name', type: 'string' },
                { name: 'quantity_acres', type: 'string' }
            ],
            id: 'id',
            url: url,
            data:{id:<?php echo $id; ?>}
        };

        var dataAdapter = new $.jqx.dataAdapter(source);
        var cellsrenderer = function(row, column, value, defaultHtml, columnSettings, record)
        {
            var element = $(defaultHtml);
            element.css({'margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
            if(column=='quantity_acres' && <?php if((isset($CI->permissions['action2']) && ($CI->permissions['action2']==1))){echo 'true';}else{echo 'false';} ?>)
            {
                element.html('<div class="jqxgrid_input">'+value+'</div>');
            }

            return element[0].outerHTML;

        };
        // create jqxgrid.
        $("#system_jqx_container").jqxGrid(
            {
                width: '100%',
                source: dataAdapter,
                filterable: true,
                sortable: true,
                showfilterrow: true,
                columnsresize: true,
                selectionmode: 'singlerow',
                enablebrowserselection: true,
                columnsreorder: true,
                altrows: true,
                autoheight: true,
                rowsheight: 35,
                editable:true,
                columns: [
                    { text: '<?php echo $CI->lang->line('LABEL_VARIETY_NAME'); ?>', dataField: 'division_name',filtertype: 'list',width:'200',editable:false},
                    { text: '<?php echo $CI->lang->line('LABEL_PACK_SIZE'); ?>', dataField: 'zone_name',filtertype: 'list',width:'200',editable:false},
                    { text: 'Min <?php echo $CI->lang->line('LABEL_QUANTITY'); ?>', dataField: 'territory_name',filtertype: 'list',width:'200',editable:false},
                    { text: 'Max <?php echo $CI->lang->line('LABEL_QUANTITY'); ?>', dataField: 'district_name',filtertype: 'list',width:'200',editable:false},
                    { text: 'Quantity Acres', dataField: 'quantity_acres',cellsalign: 'right',cellsrenderer: cellsrenderer
                    <?php
                        if((isset($CI->permissions['action2']) && ($CI->permissions['action2']==1)))
                        {
                        ?>
                        ,columntype:'custom',
                        initeditor: function (row, cellvalue, editor, celltext, pressedkey)
                        {
                            editor.html('<div style="margin: 0px;width: 100%;height: 100%;padding: 5px;"><input type="text" value="'+cellvalue+'" class="jqxgrid_input float_type_positive"><div>');
                        },
                        geteditorvalue: function (row, cellvalue, editor) {
                            // return the editor's value.
                            var value=editor.find('input').val();
                            var selectedRowData = $('#system_jqx_container').jqxGrid('getrowdata', row);
                            return editor.find('input').val();
                        }
                    <?php
                    }
                    else
                    {
                        ?>
                        ,editable:false
                        <?php
                    }
                    ?>
                    }
                ]
            });
    });
</script>
