<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$CI = & get_instance();
$action_buttons[]=array
(
    'label'=>$CI->lang->line("ACTION_BACK"),
    'href'=>site_url($CI->controller_url)
);
if((isset($CI->permissions['action1']) && ($CI->permissions['action1']==1))||(isset($CI->permissions['action2']) && ($CI->permissions['action2']==1)))
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
    <input type="hidden" name="id" value="<?php echo $item['id']; ?>" />
    <div id="jqx_inputs">

    </div>
</form>

<div class="row widget">
    <div class="widget-header">
        <div class="title">
            <?php echo $title; ?>
        </div>
        <div class="clearfix"></div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_OUTLET');?> </label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo $item['outlet_name'] ?></label>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_YEAR');?> </label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo $item['year'] ?></label>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_MONTH');?> </label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo date("F", mktime(0, 0, 0,  $item['month'],1, 2000)); ?></label>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right">Expense Duration Date</label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo System_helper::display_date($item['date_start']);?> To <?php echo System_helper::display_date($item['date_end']);?></label>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_CHECKED_BY');?> </label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo $item['user_updated_full_name'];?></label>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DATE_CHECKED_TIME');?> </label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo System_helper::display_date_time($item['date_updated_check']);?></label>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_FORWARDED_BY');?> </label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo $item['user_forward_full_name'];?></label>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DATE_CHECKED_FORWARD_TIME');?> </label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo System_helper::display_date_time($item['date_forward_checked']);?></label>
        </div>
    </div>
    <?php
    if($item['remarks_check'])
    {
        ?>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_REMARKS_FORWARD');?> </label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo nl2br($item['remarks_check']);?></label>
            </div>
        </div>
    <?php
    }
    ?>
</div>

<div class="clearfix"></div>

<div id="system_report_container">

</div>
<div class="row widget">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4 class="panel-title">
                <?php
                if($daily_expenses)
                {
                    ?>
                    <label class=""><a class="external text-danger" data-toggle="collapse" data-target="#collapse_main" href="#">+ Daily Expense Histories</a></label>
                <?php
                }
                else
                {
                    ?>
                    <label class=""><a class="text-danger" href="#">No Daily Expense</a></label>
                <?php
                }
                ?>
            </h4>
        </div>
        <div id="collapse_main" class="panel-collapse collapse">
            <br/>
            <?php
            foreach($daily_expenses as $expense_date=>$expense)
            {
                ?>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <label class=""><a class="external text-danger" data-toggle="collapse" data-target="#collapse_<?php echo $expense_date?>" href="#">+ <?php echo $expense_date?></a></label>
                        </h4>
                    </div>
                    <div id="collapse_<?php echo $expense_date?>" class="panel-collapse collapse">
                        <table class="table table-bordered table-responsive system_table_details_view">
                            <thead>
                            <tr class="bg-success">
                                <th width="5px">SL#</th>
                                <th>Expense Item</th>
                                <th width="200px" class="text-right">Amount</th>
                                <th>Remarks</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $serial=1;
                            $amount_total_daily=0;
                            foreach($expense as $detail)
                            {
                                $amount_total_daily+=$detail['amount'];
                                ?>
                                <tr>
                                    <td><?php echo $serial;?></td>
                                    <td><?php echo $detail['name'];?></td>
                                    <td class="text-right"><?php echo System_helper::get_string_amount($detail['amount']);?></td>
                                    <td><?php echo $detail['remarks'];?></td>
                                </tr>
                                <?php
                                $serial++;
                            }
                            ?>
                            </tbody>
                            <tfoot>
                            <tr>
                                <th class="text-right" colspan="2">Total:</th>
                                <th class="text-right"><?php echo System_helper::get_string_amount($amount_total_daily);?></th>
                                <th>&nbsp;</th>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            <?php
            }
            ?>
        </div>
    </div>
    <div class="clearfix"></div>
    <div class="col-xs-12" id="system_jqx_container"></div>
</div>
<?php
$options=array
(
    'outlet_id'=>$item['outlet_id'],
    'month'=>$item['month'],
    'year'=>$item['year'],
    'grand_total_show'=>''
);
?>
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
                $('#save_form_jqx  #jqx_inputs').append('<input type="hidden" name="items['+data[i]['expense_item_id']+'][amount_approve]" value="'+data[i]['amount_approve']+'">');
            }
            var sure = confirm('<?php echo $CI->lang->line('MSG_CONFIRM_SAVE'); ?>');
            if(sure)
            {
                $("#save_form_jqx").submit();
                return false;
            }
        });

        var url = "<?php echo site_url($CI->controller_url.'/index/get_items_edit/');?>";
        // prepare the data
        var source =
        {
            dataType: "json",
            dataFields: [
                <?php
                 foreach($system_preference_items as $key=>$item)
                 {
                     if($key=='expense_item_name')
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
            url: url,
            type: 'POST',
            data:JSON.parse('<?php echo json_encode($options);?>')
        };

        var dataAdapter = new $.jqx.dataAdapter(source);
        var cellsrenderer = function(row, column, value, defaultHtml, columnSettings, record)
        {
            var element = $(defaultHtml);

            if(column=='amount_request' || column=='amount_check')
            {
                if(value==0)
                {
                    element.html('');
                }
                else
                {
                    element.html(get_string_amount(value));
                }
            }
            if(column=='amount_approve')
            {
                element.css({'margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
                if(value==0)
                {
                    value='';
                }
                element.html('<div class="jqxgrid_input">'+value+'</div>');
            }
            return element[0].outerHTML;
        };
        // create jqxgrid.
        $("#system_jqx_container").jqxGrid(
            {
                width: '100%',
                height: '350px',
                filterable: false,
                sortable: true,
                showfilterrow: false,
                source: dataAdapter,
                columnsresize: true,
                columnsreorder: true,
                enablebrowserselection: true,
                altrows: true,
                rowsheight: 35,
                columnsheight: 70,
                editable:true,
                columns: [
                    { text: '<?php echo $CI->lang->line('LABEL_EXPENSE_ITEM_NAME'); ?>', dataField: 'expense_item_name',width:'250', pinned:true,cellsrenderer: cellsrenderer,editable:false},
                    { text: '<?php echo $CI->lang->line('LABEL_AMOUNT_REQUEST'); ?>', dataField: 'amount_request',width:'150', cellsAlign:'right',pinned:true,cellsrenderer: cellsrenderer,editable:false},
                    { text: '<?php echo $CI->lang->line('LABEL_AMOUNT_CHECK'); ?>', dataField: 'amount_check',width:'150', cellsAlign:'right',pinned:true,cellsrenderer: cellsrenderer,editable:false},
                    { text: '<?php echo $CI->lang->line('LABEL_AMOUNT_APPROVE'); ?>',datafield: 'amount_approve', width: 150, cellsAlign:'right',cellsalign: 'right',cellsrenderer: cellsrenderer,columntype: 'custom',
                        cellbeginedit: function (row)
                        {
                            var selectedRowData = $('#system_jqx_container').jqxGrid('getrowdata', row);//only last selected
                            return true;
                        },
                        initeditor: function (row, cellvalue, editor, celltext, pressedkey)
                        {
                            editor.html('<div style="margin: 0px;width: 100%;height: 100%;padding: 5px;"><input style="z-index: 1 !important;" type="text" value="'+cellvalue+'" class="jqxgrid_input integer_type_positive"><div>');
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
