<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$CI=& get_instance();
$action_buttons=array();
$action_buttons[]=array
(
    'label'=>$CI->lang->line("ACTION_BACK"),
    'href'=>site_url($CI->controller_url)
);
$action_buttons[]=array(
    'type'=>'button',
    'label'=>$CI->lang->line("ACTION_CLEAR"),
    'id'=>'button_action_clear',
    'data-form'=>'#save_form'
);
$CI->load->view('action_buttons',array('action_buttons'=>$action_buttons));
?>

<form id="save_form" action="<?php echo site_url($CI->controller_url.'/index/save_approve');?>" method="post">
    <input type="hidden" id="id" name="id" value="<?php echo $item['id']; ?>" />
    <input type="hidden" id="system_save_new_status" name="system_save_new_status" value="0" />

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
        <div class="widget-header">
            <div class="title">
                <?php echo $title; ?>
            </div>
            <div class="clearfix"></div>
        </div>
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
        <div class="clearfix"></div>
        <div class="widget-header">
            <div class="title">
                Approved Confirmation
            </div>
            <div class="clearfix"></div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_APPROVED');?> <span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <select id="status_approve" class="form-control" name="item[status_approve]">
                    <option value=""><?php echo $CI->lang->line('SELECT');?></option>
                    <option value="<?php echo $this->config->item('system_status_approved')?>"><?php echo $this->config->item('system_status_approved')?></option>
                    <option value="<?php echo $this->config->item('system_status_rollback')?>"><?php echo $this->config->item('system_status_rollback')?></option>
                </select>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Remarks for Approve/Rollback <span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <textarea name="item[remarks_approve]" id="remarks_approve" class="form-control"><?php echo $item['remarks_approve'];?></textarea>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">

            </div>
            <div class="col-sm-4 col-xs-4">
                <div class="action_button pull-right">
                    <button id="button_action_save" type="button" class="btn" data-form="#save_form" data-message-confirm="Are You want showroom expense approved/rollback?">Save</button>
                </div>
            </div>
            <div class="col-sm-4 col-xs-4">

            </div>
        </div>
    </div>
</form>
<?php
$options=array
(
    'outlet_id'=>$item['outlet_id'],
    'month'=>$item['month'],
    'year'=>$item['year'],
    'grand_total_show'=>true
);
?>
<script type="text/javascript">
    $(document).ready(function ()
    {
        system_preset({controller:'<?php echo $CI->router->class; ?>'});

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
        var aggregates=function (total, column, element, record)
        {
            if(record.expense_item_name=="Grand Total")
            {
                return record[element];
            }
            return total;
        };
        var aggregatesrenderer=function (aggregates)
        {
            var text=aggregates['total'];
            if(((aggregates['total']=='0.00')||(aggregates['total']=='')))
            {
                text='';
            }
            return '<div style="position: relative; margin: 0px;padding: 5px;width: 100%;height: 100%; overflow: hidden;background-color:'+system_report_color_grand+';">' +text+'</div>';

        };
        var aggregatesrenderer_amount=function (aggregates)
        {
            var text='';
            if(!((aggregates['total']=='0.00')||(aggregates['total']=='')))
            {
                text=get_string_amount(aggregates['total']);
            }

            return '<div style="position: relative; margin: 0px;padding: 5px;width: 100%;height: 100%; overflow: hidden;background-color:'+system_report_color_grand+';">' +text+'</div>';

        };
        var cellsrenderer = function(row, column, value, defaultHtml, columnSettings, record)
        {
            var element = $(defaultHtml);

            if(column=='amount_request' || column=='amount_check' || column=='amount_approve')
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
                showaggregates: true,
                showstatusbar: true,
                altrows: true,
                rowsheight: 35,
                columnsheight: 70,
                columns: [
                    { text: '<?php echo $CI->lang->line('LABEL_EXPENSE_ITEM_NAME'); ?>', dataField: 'expense_item_name',width:'250',cellsrenderer: cellsrenderer,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer},
                    { text: '<?php echo $CI->lang->line('LABEL_AMOUNT_REQUEST'); ?>', dataField: 'amount_request',width:'150', cellsAlign:'right',cellsrenderer: cellsrenderer,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer_amount},
                    { text: '<?php echo $CI->lang->line('LABEL_AMOUNT_CHECK'); ?>', dataField: 'amount_check',width:'150', cellsAlign:'right',cellsrenderer: cellsrenderer,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer_amount},
                    { text: '<?php echo $CI->lang->line('LABEL_AMOUNT_APPROVE'); ?>', dataField: 'amount_approve',width:'150', cellsAlign:'right',cellsrenderer: cellsrenderer,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer_amount}
                ]
            });
    });
</script>