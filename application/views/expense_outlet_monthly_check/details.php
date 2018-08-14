<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$CI=& get_instance();
$action_buttons=array();
$action_buttons[]=array(
    'label'=>$CI->lang->line("ACTION_BACK").' to Pending List',
    'href'=>site_url($CI->controller_url)
);
$action_buttons[]=array(
    'label'=>$CI->lang->line("ACTION_BACK").' to All list',
    'href'=>site_url($CI->controller_url.'/index/list_all')
);
if(isset($CI->permissions['action5']) && ($CI->permissions['action5']==1))
{
    $action_buttons[]=array(
        'type'=>'button',
        'label'=>$CI->lang->line("ACTION_DOWNLOAD"),
        'class'=>'button_action_download',
        'data-title'=>"Download"
    );
}
$action_buttons[]=array
(
    'label'=>$CI->lang->line("ACTION_REFRESH"),
    'href'=>site_url($CI->controller_url.'/index/details/'.$item['id'])
);
$CI->load->view('action_buttons',array('action_buttons'=>$action_buttons));
$status_approve=$item['status_approve'];
?>

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
                <label class=""><a class="external text-danger" data-toggle="collapse" data-target="#collapse3" href="#">+ Basic Information</a></label>
            </h4>
        </div>
        <div id="collapse3" class="panel-collapse collapse">
            <table class="table table-bordered table-responsive system_table_details_view">
                <thead>
                <tr>
                    <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_OUTLET');?></label></th>
                    <th class=" header_value"><label class="control-label"><?php echo $item['outlet_name'];?></label></th>
                    <th colspan="2">&nbsp;</th>
                </tr>
                <tr>
                    <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_YEAR');?></label></th>
                    <th class=" header_value"><label class="control-label"><?php echo date("Y", mktime(0, 0, 0,1,1, $item['year']));;?></label></th>
                    <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_MONTH');?></label></th>
                    <th class=" header_value"><label class="control-label"><?php echo date("F", mktime(0, 0, 0,  $item['month'],1, 2000));;?></label></th>
                </tr>
                <tr>
                    <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_CHECKED_BY');?></label></th>
                    <th class=" header_value"><label class="control-label"><?php echo $users[$item['user_updated_check']]['name'];?></label></th>
                    <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DATE_CHECKED_TIME');?></label></th>
                    <th class=""><label class="control-label"><?php echo System_helper::display_date_time($item['date_updated_check']);?></label></th>
                </tr>
                <tr>
                    <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_STATUS_FORWARD');?> (Monthly Checked)</label></th>
                    <th class=" header_value"><label class="control-label"><?php echo $item['status_forward_check'];?></label></th>
                    <th colspan="2">&nbsp;</th>
                </tr>
                <?php
                if($item['status_forward_check']==$this->config->item('system_status_forwarded'))
                {
                    ?>
                    <tr>
                        <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_FORWARDED_BY');?></label></th>
                        <th class=" header_value"><label class="control-label"><?php echo $users[$item['user_forward_checked']]['name'];?></label></th>
                        <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DATE_FORWARDED_TIME');?></label></th>
                        <th class=""><label class="control-label"><?php echo System_helper::display_date_time($item['date_forward_checked']);?></label></th>
                    </tr>
                    <tr>
                        <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_REMARKS_FORWARD');?></label></th>
                        <th colspan="3" class=" header_value"><label class="control-label"><?php echo nl2br($item['remarks_check']);?></label></th>
                    </tr>
                <?php
                }
                ?>
                <tr>
                    <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_STATUS_APPROVE');?></label></th>
                    <th class=" header_value"><label class="control-label"><?php echo $status_approve;?></label></th>
                    <th colspan="2">&nbsp;</th>
                </tr>
                <?php
                if($item['status_approve']==$this->config->item('system_status_approved'))
                {
                    ?>
                    <tr>
                        <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_APPROVED_BY');?></label></th>
                        <th class=" header_value"><label class="control-label"><?php echo $users[$item['user_approved']]['name'];?></label></th>
                        <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DATE_APPROVED_TIME');?></label></th>
                        <th class=""><label class="control-label"><?php echo System_helper::display_date_time($item['date_approved']);?></label></th>
                    </tr>
                    <tr>
                        <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_REMARKS_APPROVE');?></label></th>
                        <th class=" header_value" colspan="3"><label class="control-label"><?php echo nl2br($item['remarks_approve']);?></label></th>
                    </tr>
                <?php
                }
                ?>
                </thead>
            </table>
        </div>
    </div>
    <div class="clearfix"></div>

    <div class="clearfix"></div>
    <!--<div class="col-md-4"></div>
    <div class="col-md-4">
        <table class="table table-bordered table-responsive">
            <tbody>
            <tr>
                <td width="200"><strong>Total Request Amount: </strong></td>
                <td><?php /*echo '100';*/?></td>
            </tr>
            <tr>
                <td><strong>Total Checked Amount: </strong></td>
                <td><?php /*echo '100';*/?></td>
            </tr>
            <tr>
                <td><strong>Total Approve Amount: </strong></td>
                <td><?php /*echo '100';*/?></td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="col-md-4"></div>-->
    <div class="row show-grid">
        <div class="row widget">
            <div class="col-xs-12" id="system_jqx_container"></div>
        </div>
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
            <table class="table table-bordered table-responsive system_table_details_view">
                <thead>
                <tr class="bg-success">
                    <th width="120px">Expense Date</th>
                    <th>Expense Item</th>
                    <th width="200px" class="text-right">Amount</th>
                    <th>Remarks</th>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach($daily_expenses as $expense_date=>$expense)
                {
                    ?>
                    <tr>
                        <td colspan="5"><strong><?php echo $expense_date?></strong></td>
                    </tr>
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
                    <tr>
                        <th class="text-right" colspan="2">Total:</th>
                        <th class="text-right"><?php echo System_helper::get_string_amount($amount_total_daily);?></th>
                        <th>&nbsp;</th>
                    </tr>
                <?php
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php
$options=array
(
    'outlet_id'=>$item['outlet_id'],
    'month'=>$item['month'],
    'year'=>$item['year'],
    'grand_total_show'=>1
);
?>
<script type="text/javascript">
    $(document).ready(function ()
    {
        system_preset({controller:'<?php echo $CI->router->class; ?>'});

        var url = "<?php echo site_url($CI->controller_url.'/index/get_items_add_edit/');?>";
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
                    { text: '<?php echo $CI->lang->line('LABEL_AMOUNT_CHECK'); ?>', dataField: 'amount_check',width:'150', cellsAlign:'right',cellsrenderer: cellsrenderer,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer_amount}
                    <?php
                    if($status_approve==$this->config->item('system_status_approved'))
                    {
                    ?>
                    ,
                    { text: '<?php echo $CI->lang->line('LABEL_AMOUNT_APPROVE'); ?>', dataField: 'amount_approve',width:'150', cellsAlign:'right',cellsrenderer: cellsrenderer,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer_amount}
                    <?php
                    }
                    ?>
                ]
            });
    });
</script>