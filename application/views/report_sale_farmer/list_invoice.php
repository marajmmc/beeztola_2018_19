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
        'href'=>site_url($CI->controller_url.'/index/set_preference_invoice')
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
        var url = "<?php echo site_url($CI->controller_url.'/index/get_items_invoice');?>";

        // prepare the data
        var source =
        {
            dataType: "json",
            dataFields: [
                { name: 'customer_name', type: 'string' },
                { name: 'mobile_no', type: 'string' },
                { name: 'invoice_no', type: 'string' },
                { name: 'date_sale', type: 'string'},
                { name: 'date_cancel', type: 'string'},
                { name: 'amount_total', type: 'string'},
                { name: 'amount_discount_variety', type: 'string'},
                { name: 'amount_discount_self', type: 'string'},
                { name: 'amount_payable', type: 'string'},
                { name: 'amount_payable_actual', type: 'string'},
                { name: 'amount_actual', type: 'string'},
                { name: 'status', type: 'string'}
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
            if ((record.status=='In-Active')&& (column!="mobile_no")&& (column!="invoice_no")&& (column!="customer_name")&& (column!="date_sale"))
            {
                element.css({ 'background-color': '#FF0000','margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
            }
            else if ((record.mobile_no=="Customer Total")&& (column!="customer_name"))
            {
                element.css({ 'background-color': system_report_color_crop,'margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
            }
            else if (record.customer_name=="Grand Total")
            {
                element.css({ 'background-color': system_report_color_grand,'margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
            }
            else
            {
                element.css({'margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
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
            return '<div style="position: relative; margin: 0px;padding: 5px;width: 100%;height: 100%; overflow: hidden;background-color:'+system_report_color_grand+';">' +aggregates['total']+'</div>';

        };
        var dataAdapter = new $.jqx.dataAdapter(source);
        // create jqxgrid.
        $("#system_jqx_container").jqxGrid(
            {
                width: '100%',
                height:'350px',
                source: dataAdapter,
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
                        { text: '<?php echo $CI->lang->line('LABEL_CUSTOMER_NAME'); ?>', dataField: 'customer_name',pinned:true,width:'200',cellsrenderer: cellsrenderer,hidden: <?php echo $system_preference_items['customer_name']?0:1;?>,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer},
                        { text: '<?php echo $CI->lang->line('LABEL_MOBILE_NO'); ?>', dataField: 'mobile_no',pinned:true,width:'200',cellsrenderer: cellsrenderer,hidden: <?php echo $system_preference_items['mobile_no']?0:1;?>,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer},
                        { text: '<?php echo $CI->lang->line('LABEL_INVOICE_NO'); ?>', dataField: 'invoice_no',width:'100',cellsrenderer: cellsrenderer,hidden: <?php echo $system_preference_items['invoice_no']?0:1;?>,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer},
                        { text: '<?php echo $CI->lang->line('LABEL_DATE_SALE'); ?>', dataField: 'date_sale',width:'100',cellsrenderer: cellsrenderer,hidden: <?php echo $system_preference_items['date_sale']?0:1;?>,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer},
                        { text: '<?php echo $CI->lang->line('LABEL_DATE_CANCEL'); ?>', dataField: 'date_cancel',width:'100',cellsrenderer: cellsrenderer,hidden: <?php echo $system_preference_items['date_cancel']?0:1;?>,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer},
                        { text: '<?php echo $CI->lang->line('LABEL_AMOUNT_TOTAL'); ?>', dataField: 'amount_total',width:'100',cellsAlign:'right',cellsrenderer: cellsrenderer,hidden: <?php echo $system_preference_items['amount_total']?0:1;?>,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer},
                        { text: '<?php echo $CI->lang->line('LABEL_AMOUNT_DISCOUNT_VARIETY'); ?>', dataField: 'amount_discount_variety',width:'100',cellsAlign:'right',cellsrenderer: cellsrenderer,hidden: <?php echo $system_preference_items['amount_discount_variety']?0:1;?>,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer},
                        { text: '<?php echo $CI->lang->line('LABEL_AMOUNT_DISCOUNT_SELF'); ?>', dataField: 'amount_discount_self',width:'100',cellsAlign:'right',cellsrenderer: cellsrenderer,hidden: <?php echo $system_preference_items['amount_discount_self']?0:1;?>,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer},
                        { text: '<?php echo $CI->lang->line('LABEL_AMOUNT_PAYABLE'); ?>', dataField: 'amount_payable',width:'100',cellsAlign:'right',cellsrenderer: cellsrenderer,hidden: <?php echo $system_preference_items['amount_payable']?0:1;?>,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer},
                        { text: '<?php echo $CI->lang->line('LABEL_AMOUNT_PAYABLE_ACTUAL'); ?>', dataField: 'amount_payable_actual',width:'100',cellsAlign:'right',cellsrenderer: cellsrenderer,hidden: <?php echo $system_preference_items['amount_payable_actual']?0:1;?>,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer},
                        { text: '<?php echo $CI->lang->line('LABEL_AMOUNT_ACTUAL'); ?>', dataField: 'amount_actual',width:'100',cellsAlign:'right',cellsrenderer: cellsrenderer,hidden: <?php echo $system_preference_items['amount_actual']?0:1;?>,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer}
                    ]
            });
    });
</script>
