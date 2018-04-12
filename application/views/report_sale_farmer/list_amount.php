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
        'href'=>site_url($CI->controller_url.'/index/set_preference_amount')
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
        system_preset({controller:'<?php echo $CI->router->class; ?>'});
        var url = "<?php echo site_url($CI->controller_url.'/index/get_items_amount');?>";

        // prepare the data
        var source =
        {
            dataType: "json",
            dataFields: [
                { name: 'customer_name', type: 'string' },
                { name: 'mobile_no', type: 'string' },
                { name: 'amount_total_paid', type: 'number' },
                { name: 'amount_total_cancel', type: 'number' },
                { name: 'amount_actual_paid', type: 'number' }
            ],
            id: 'id',
            type: 'POST',
            url: url,
            data:JSON.parse('<?php echo json_encode($options);?>')
        };
        var cellsrenderer = function(row, column, value, defaultHtml, columnSettings, record)
        {
            var element = $(defaultHtml);
            //console.log(defaultHtml);
            if (record.customer_name=="Grand Total")
            {
                element.css({ 'background-color': system_report_color_grand,'margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
            }
            else
            {
                element.css({'margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
            }
            if((column=='amount_total_paid')||(column=='amount_total_cancel')||(column=='amount_actual_paid'))
            {
                if(value==0)
                {
                    element.html('');
                }
                else
                {
                    element.html(number_format(value,2));
                }
            }

            return element[0].outerHTML;

        };

        var aggregates=function (total, column, element, record)
        {
            //console.log(record);
            //console.log(record['warehouse_5_pkt']);
            if(record.customer_name=="Grand Total")
            {
                return record[element];

            }
            return total;
        };
        var aggregatesrenderer=function (aggregates)
        {
            //console.log('here');
            return '<div style="position: relative; margin: 0px;padding: 5px;width: 100%;height: 100%; overflow: hidden;background-color:'+system_report_color_grand+';">' +(aggregates['total']=='0.00'?'':number_format(aggregates['total'],2))+'</div>';

        };
        var dataAdapter = new $.jqx.dataAdapter(source);
        // create jqxgrid.
        $("#system_jqx_container").jqxGrid(
            {
                width: '100%',
                height:'350px',
                source: dataAdapter,
                sortable: true,
                columnsresize: true,
                columnsreorder: true,
                altrows: true,
                enabletooltips: true,
                showaggregates: true,
                showstatusbar: true,
                rowsheight: 35,
                enablebrowserselection:true,
                columns:
                    [
                        {
                            text: '<?php echo $CI->lang->line('LABEL_SL_NO'); ?>',datafield: 'sl_no',pinned:true,width:'30', columntype: 'number',cellsalign: 'right', sortable: false, menu: false,hidden: <?php echo $system_preference_items['sl_no']?0:1;?>,
                            cellsrenderer: function(row, column, value, defaultHtml, columnSettings, record)
                            {
                                var element = $(defaultHtml);
                                element.html(value);
                                return element[0].outerHTML;
                            }
                        },
                        { text: '<?php echo $CI->lang->line('LABEL_CUSTOMER_NAME'); ?>', dataField: 'customer_name',pinned:true,cellsrenderer: cellsrenderer,width:'200',sortable: false, menu: false,hidden: <?php echo $system_preference_items['customer_name']?0:1;?>},
                        { text: '<?php echo $CI->lang->line('LABEL_MOBILE_NO'); ?>', dataField: 'mobile_no',cellsrenderer: cellsrenderer,width:'150',sortable: false, menu: false,hidden: <?php echo $system_preference_items['mobile_no']?0:1;?>},
                        { text: '<?php echo $CI->lang->line('LABEL_AMOUNT_TOTAL_PAID'); ?>', dataField: 'amount_total_paid',width:'100',cellsrenderer: cellsrenderer,cellsalign: 'right',hidden: <?php echo $system_preference_items['amount_total_paid']?0:1;?>,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer},
                        { text: '<?php echo $CI->lang->line('LABEL_AMOUNT_TOTAL_CANCEL'); ?>', dataField: 'amount_total_cancel',cellsrenderer: cellsrenderer,width:'100',cellsalign: 'right',hidden: <?php echo $system_preference_items['amount_total_cancel']?0:1;?>,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer},
                        { text: '<?php echo $CI->lang->line('LABEL_AMOUNT_ACTUAL_PAID'); ?>', dataField: 'amount_actual_paid',cellsrenderer: cellsrenderer,width:'100',cellsalign: 'right',hidden: <?php echo $system_preference_items['amount_actual_paid']?0:1;?>,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer}
                    ]
            });
    });
</script>
