<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$CI = & get_instance();
$action_buttons=array();
if(isset($CI->permissions['action4'])&&($CI->permissions['action4']==1))
{
    $action_buttons[]=array(
        'type'=>'button',
        'label'=>$CI->lang->line("ACTION_PRINT"),
        'class'=>'button_action_download',
        'data-title'=>"Print",
        'data-print'=>true
    );
}
if(isset($CI->permissions['action5'])&&($CI->permissions['action5']==1))
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
        'href'=>site_url($CI->controller_url.'/index/set_preference_area_amount')
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
        var url = "<?php echo site_url($CI->controller_url.'/index/get_items');?>";

        // prepare the data
        var source =
        {
            dataType: "json",
            dataFields: [
                <?php
                 foreach($system_preference_items as $key=>$item)
                 {
                 if($key=='area')
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
            url: url,
            type: 'POST',
            data:JSON.parse('<?php echo json_encode($options);?>')
        };
        var cellsrenderer = function(row, column, value, defaultHtml, columnSettings, record)
        {
            var element = $(defaultHtml);
           // console.log(defaultHtml);
            if (record.area=="Grand Total")
            {

                element.css({ 'background-color': system_report_color_grand,'margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});

            }
            else
            {
                element.css({'margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
            }
            if((column!='area'))
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
        var tooltiprenderer = function (element) {
            $(element).jqxTooltip({position: 'mouse', content: $(element).text() });
        };
        var aggregates=function (total, column, element, record)
        {
            if(record.area=="Grand Total")
            {
                //console.log(element);
                return record[element];

            }
            return total;
            //return grand_starting_stock;
        };
        var aggregatesrenderer=function (aggregates)
        {
            var text=aggregates['total'];
            if(text!="Grand Total")
            {
                if((aggregates['total']=='0.00')||(aggregates['total']==''))
                {
                    text='';
                }
                else
                {
                    text=number_format(aggregates['total'],2);
                }
            }
            return '<div style="position: relative; margin: 0px;padding: 5px;width: 100%;height: 100%; overflow: hidden;background-color:'+system_report_color_grand+';">' +text+'</div>';

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
                rowsheight: 40,
                columns: [
                    {
                        text: '<?php echo $CI->lang->line('LABEL_SL_NO'); ?>',datafield: 'sl_no',pinned:true,width:'30', columntype: 'number',cellsalign: 'right',hidden: <?php echo $system_preference_items['sl_no']?0:1;?>,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer,
                        cellsrenderer: function(row, column, value, defaultHtml, columnSettings, record)
                        {
                            var element = $(defaultHtml);
                            element.html(value);
                            return element[0].outerHTML;
                        }
                    },
                    { text: '<?php echo $areas; ?>', dataField: 'area',pinned:true,width:'200',sortable: false,menu: false,cellsrenderer: cellsrenderer,hidden: <?php echo $system_preference_items['area']?0:1;?>,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer},
                    { text: '<?php echo $CI->lang->line('LABEL_AMOUNT_TARGET'); ?>', dataField: 'amount_target',width:'120',cellsalign: 'right',cellsrenderer: cellsrenderer,hidden: <?php echo $system_preference_items['amount_target']?0:1;?>,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer},
                    { text: '<?php echo $CI->lang->line('LABEL_AMOUNT_SALES_CASH'); ?>', dataField: 'amount_sales_cash',width:'120',cellsalign: 'right',cellsrenderer: cellsrenderer,hidden: <?php echo $system_preference_items['amount_sales_cash']?0:1;?>,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer},
                    { text: '<?php echo $CI->lang->line('LABEL_AMOUNT_SALES_CREDIT'); ?>', dataField: 'amount_sales_credit',width:'120',cellsalign: 'right',cellsrenderer: cellsrenderer,hidden: <?php echo $system_preference_items['amount_sales_credit']?0:1;?>,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer},
                    { text: '<?php echo $CI->lang->line('LABEL_AMOUNT_SALES'); ?>', dataField: 'amount_sales',width:'120',cellsalign: 'right',cellsrenderer: cellsrenderer,hidden: <?php echo $system_preference_items['amount_sales']?0:1;?>,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer},
                    { text: '<?php echo $CI->lang->line('LABEL_AMOUNT_SALES_CASH_AVERAGE'); ?>', dataField: 'amount_sales_cash_average',width:'120',cellsalign: 'right',cellsrenderer: cellsrenderer,hidden: <?php echo $system_preference_items['amount_sales_cash_average']?0:1;?>,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer},
                    { text: '<?php echo $CI->lang->line('LABEL_AMOUNT_SALES_CREDIT_AVERAGE'); ?>', dataField: 'amount_sales_credit_average',width:'120',cellsalign: 'right',cellsrenderer: cellsrenderer,hidden: <?php echo $system_preference_items['amount_sales_credit_average']?0:1;?>,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer},
                    { text: '<?php echo $CI->lang->line('LABEL_AMOUNT_DEFERENCE'); ?>', dataField: 'amount_deference',width:'120',cellsalign: 'right',cellsrenderer: cellsrenderer,hidden: <?php echo $system_preference_items['amount_deference']?0:1;?>,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer},
                    { text: '<?php echo $CI->lang->line('LABEL_AMOUNT_AVERAGE'); ?>', dataField: 'amount_average',width:'120',cellsalign: 'right',cellsrenderer: cellsrenderer,hidden: <?php echo $system_preference_items['amount_average']?0:1;?>,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer}
                ]
            });
    });
</script>