<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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
        system_preset({controller:'<?php echo $CI->router->class; ?>'});
        var url = "<?php echo site_url($CI->controller_url.'/index/get_items');?>";

        // prepare the data
        var source =
        {
            dataType: "json",
            dataFields: [
                <?php
                foreach($system_preference_items as $key=>$item)
                {
                    if(($key=='id')||(substr($key,0,6)=='amount')||($key=='day_last_payment')||($key=='day_last_sale'))
                    {
                        ?>
                        { name: '<?php echo $key ?>', type: 'number' },
                        <?php
                    }
                    else
                    {
                        ?>
                        { name: '<?php echo $key ?>', type: 'string' },
                        <?php
                    }
                }
                ?>
            ],
            type: 'POST',
            url: url,
            sortcolumn: 'day_last_payment',
            sortdirection: 'desc',
            data:JSON.parse('<?php echo json_encode($options);?>')
        };

        var dataAdapter = new $.jqx.dataAdapter(source);
        var cellsrenderer = function(row, column, value, defaultHtml, columnSettings, record)
        {
            var element = $(defaultHtml);
            element.css({'margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
            <?php
            if($options['day_color_payment_start']>0)
            {
            ?>
                if(column=='day_last_payment')
                {
                    if(record['amount_credit_due']>0)
                    {
                        if(record['day_last_payment']>=<?php echo $options['day_color_payment_start'];?>)
                        {
                            if(record['day_last_payment']><?php echo ($options['day_color_payment_start']+$options['day_color_payment_interval']);?>)
                            {
                                element.css({ 'background-color': '#FF0000'});
                            }
                            else
                            {
                                element.css({ 'background-color': '#FFFF00'});
                            }

                        }
                    }
                }
            <?php
            }

            if($options['day_color_sales_start']>0)
            {
            ?>
                if(column=='day_last_sale')
                {
                    if(record['day_last_sale']>=<?php echo $options['day_color_sales_start']; ?>)
                    {
                        if(record['day_last_sale']><?php echo ($options['day_color_sales_start']+$options['day_color_sales_interval']);?>)
                        {
                            element.css({ 'background-color': '#FF0000'});
                        }
                        else
                        {
                            element.css({ 'background-color': '#FFFF00'});
                        }
                    }
                }
            <?php
            }
            ?>
            if(((column=='date_last_payment')&& (record['date_last_payment']==0))||((column=='day_last_payment')&& (record['day_last_payment']==0))||((column=='date_last_sale')&& (record['date_last_sale']==0))||((column=='day_last_sale')&& (record['day_last_sale']==0)))
            {
                element.html('');
            }
            if(column.substr(0,6)=='amount')
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
        var aggregatesrenderer_amount=function (aggregates)
        {
            var text='';
            if(!((aggregates['sum']=='0.00')||(aggregates['sum']=='')))
            {
                text=get_string_amount(aggregates['sum']);
            }
            return '<div style="position: relative; margin: 0px;padding: 5px;width: 100%;height: 100%; overflow: hidden;background-color:'+system_report_color_grand+';">' +text+'</div>';
        };
        // create jqxgrid.
        $("#system_jqx_container").jqxGrid(
            {
                width: '100%',
                height: '350px',
                source: dataAdapter,
                filterable: true,
                sortable: true,
                showfilterrow: true,
                columnsresize: true,
                selectionmode: 'singlerow',
                showaggregates: true,
                showstatusbar: true,
                altrows: true,
                rowsheight: 35,
                columnsreorder: true,
                enablebrowserselection: true,
                pageable: true,
                pagesize: 1000,
                pagesizeoptions: ['10', '20', '50', '100', '200', '300', '500','1000'],
                columns:
                [
                    { text: '<?php echo $CI->lang->line('LABEL_ID'); ?>', dataField: 'id', width:60, cellsrenderer: cellsrenderer, hidden: <?php echo $system_preference_items['id']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_OUTLET_NAME'); ?>', dataField: 'outlet_name', width:180, filtertype: 'list', cellsrenderer: cellsrenderer, hidden: <?php echo $system_preference_items['outlet_name']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_BARCODE'); ?>', dataField: 'barcode', width:80, cellsrenderer: cellsrenderer, hidden: <?php echo $system_preference_items['barcode']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_NAME'); ?>', dataField: 'name', width:220, cellsrenderer: cellsrenderer, hidden: <?php echo $system_preference_items['name']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_AMOUNT_CREDIT_LIMIT'); ?>', dataField: 'amount_credit_limit', width:120, cellsrenderer: cellsrenderer, cellsalign: 'right', hidden: <?php echo $system_preference_items['amount_credit_limit']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_AMOUNT_CREDIT_BALANCE'); ?>', dataField: 'amount_credit_balance', width:120, cellsrenderer: cellsrenderer, cellsalign: 'right', hidden: <?php echo $system_preference_items['amount_credit_balance']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_AMOUNT_CREDIT_DUE'); ?>', dataField: 'amount_credit_due', width:120, cellsrenderer: cellsrenderer, cellsalign: 'right', hidden: <?php echo $system_preference_items['amount_credit_due']?0:1;?>, aggregates: ['sum'], aggregatesrenderer:aggregatesrenderer_amount},
                    { text: '<?php echo $CI->lang->line('LABEL_AMOUNT_LAST_PAYMENT'); ?>', dataField: 'amount_last_payment', width:120, cellsrenderer: cellsrenderer, cellsalign: 'right', hidden: <?php echo $system_preference_items['amount_last_payment']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_DATE_LAST_PAYMENT'); ?>', dataField: 'date_last_payment', width:140, cellsrenderer: cellsrenderer, cellsalign: 'center', hidden: <?php echo $system_preference_items['date_last_payment']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_DAY_LAST_PAYMENT'); ?>', dataField: 'day_last_payment', width:140, cellsrenderer: cellsrenderer, cellsalign: 'center', hidden: <?php echo $system_preference_items['day_last_payment']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_AMOUNT_LAST_SALE'); ?>', dataField: 'amount_last_sale', width:120, cellsrenderer: cellsrenderer, cellsalign: 'right', hidden: <?php echo $system_preference_items['amount_last_sale']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_DATE_LAST_SALE'); ?>', dataField: 'date_last_sale', width:140, cellsrenderer: cellsrenderer, cellsalign: 'center', hidden: <?php echo $system_preference_items['date_last_sale']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_DAY_LAST_SALE'); ?>', dataField: 'day_last_sale', width:140, cellsrenderer: cellsrenderer, cellsalign: 'center', hidden: <?php echo $system_preference_items['day_last_sale']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_SALE_DUE_STATUS'); ?>', dataField: 'sale_due_status', width:200, filtertype: 'list', cellsrenderer: cellsrenderer, hidden: <?php echo $system_preference_items['sale_due_status']?0:1;?>}
                ]
            });
    });
</script>
