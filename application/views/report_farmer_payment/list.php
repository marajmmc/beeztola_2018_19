<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');
$CI = & get_instance();

$action_buttons = array();
if (isset($CI->permissions['action4']) && ($CI->permissions['action4'] == 1)) {
    $action_buttons[] = array(
        'type' => 'button',
        'label' => $CI->lang->line("ACTION_PRINT"),
        'class' => 'button_action_download',
        'data-title' => "Print",
        'data-print' => true
    );
}
if (isset($CI->permissions['action5']) && ($CI->permissions['action5'] == 1)) {
    $action_buttons[] = array(
        'type' => 'button',
        'label' => $CI->lang->line("ACTION_DOWNLOAD"),
        'class' => 'button_action_download',
        'data-title' => "Download"
    );
}
if (isset($CI->permissions['action6']) && ($CI->permissions['action6'] == 1)) {
    $action_buttons[] = array
    (
        'label' => 'Preference',
        'href' => site_url($CI->controller_url . '/index/set_preference')
    );
}
$CI->load->view('action_buttons', array('action_buttons' => $action_buttons));
?>

<div class="row widget">
    <div class="widget-header">
        <div class="title">
            <?php echo $title; ?>
        </div>
        <div class="clearfix"></div>
    </div>
    <?php
    if (isset($CI->permissions['action6']) && ($CI->permissions['action6'] == 1)) {
        $CI->load->view('preference', array('system_preference_items' => $system_preference_items));
    }
    ?>

    <div class="col-xs-12" id="system_jqx_container">

    </div>

</div>

<div class="clearfix"></div>

<script type="text/javascript">
    $(document).ready(function () {
        system_preset({controller: '<?php echo $CI->router->class; ?>'});
        var url = "<?php echo site_url($CI->controller_url.'/index/get_items');?>";

        $(document).on("click", ".pop_up", function(event)
        {
            $('#popup_content').html('');
            var left=((($(window).width() - 550) / 2) +$(window).scrollLeft());
            var top=((($(window).height() - 550) / 2) +$(window).scrollTop());
            $("#popup_window").jqxWindow({position: { x: left, y: top  }});
            $.ajax(
                {
                    url: $(this).attr('data-action-link'),
                    type: 'POST',
                    datatype: "JSON",
                    success: function (data, status)
                    {
                    },
                    error: function (xhr, desc, err)
                    {
                        console.log("error");
                    }
                });
            $("#popup_window").jqxWindow('open');
        });

        // prepare the data
        var source ={
            dataType: "json",
            dataFields: [
                <?php
                foreach($system_preference_items as $key=>$item)
                {
                    if(($key=='id') || ($key=='amount'))
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
            id: 'id',
            type: 'POST',
            url: url,
            data: JSON.parse('<?php echo json_encode($options);?>')
        };

        var dataAdapter = new $.jqx.dataAdapter(source);
        var cellsrenderer = function (row, column, value, defaultHtml, columnSettings, record) {
            var element = $(defaultHtml);
            if(column=='button_details')
            {
                if(record.id>0)
                {
                    element.html('<div><button class="btn btn-sm btn-primary pop_up" data-action-link="<?php echo site_url($CI->controller_url.'/index/details_payment'); ?>/'+record.id+'">View Details</button></div>');
                    element.css({'margin': '0','width': '100%', 'height': '100%', padding:'5px'});
                }
                else
                {
                    element.html('');
                }
            }
            if (column.substr(0, 6) == 'amount') {
                if (value == 0) {
                    element.html('');
                }
                else {
                    element.html(get_string_amount(value));
                }
            }
            return element[0].outerHTML;

        };
        var aggregates = function (total, column, element, record) {
            if (record.amount == "Grand Total") {
                return record[element];
            }
            return total;
        };
        var aggregatesrenderer = function (aggregates) {
            //console.log('here');
            return '<div style="position: relative; margin: 0px;padding: 5px;width: 100%;height: 100%; overflow: hidden;background-color:' + system_report_color_grand + ';">' + aggregates['total'] + '</div>';
        };
        var aggregatesrenderer_amount = function (aggregates) {
            var text = '';
            if (!((aggregates['sum'] == '0.00') || (aggregates['sum'] == ''))) {
                text = get_string_amount(aggregates['sum']);
            }
            return '<div style="position:relative; margin:0px; padding:5px; width:100%; height:100%; overflow:hidden; background-color:' + system_report_color_grand + ';">' + text + '</div>';
        };
        // create jqxgrid.
        $("#system_jqx_container").jqxGrid(
            {
                width: '100%',
                height: '350px',
                source: dataAdapter,
                filterable: true,
                sortable: false,
                showfilterrow: true,
                columnsresize: true,
                selectionmode: 'singlerow',
                showaggregates: true,
                showstatusbar: true,
                altrows: true,
                rowsheight: 40,
                columnsreorder: true,
                enablebrowserselection: true,
                pageable: true,
                pagesize: 1000,
                pagesizeoptions: ['10', '20', '50', '100', '200', '300', '500', '1000'],
                columns: [
                    { text: '<?php echo $CI->lang->line('LABEL_ID'); ?>', dataField: 'id', width: 60, cellsrenderer: cellsrenderer, hidden: <?php echo $system_preference_items['id']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_BARCODE'); ?>', dataField: 'barcode', width: 100, cellsrenderer: cellsrenderer, hidden: <?php echo $system_preference_items['barcode']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_DATE_PAYMENT'); ?>', dataField: 'date_payment', width: 120, cellsrenderer: cellsrenderer, hidden: <?php echo $system_preference_items['date_payment']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_OUTLET'); ?>', dataField: 'outlet', width: 220, filtertype: 'list', cellsrenderer: cellsrenderer, hidden: <?php echo $system_preference_items['outlet']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_DEALER_NAME'); ?>', dataField: 'dealer_name', width: 220, cellsrenderer: cellsrenderer, hidden: <?php echo $system_preference_items['dealer_name']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_AMOUNT_PAID_TOTAL'); ?>', dataField: 'amount_paid_total', width: 140, cellsrenderer: cellsrenderer, cellsalign: 'right', aggregates: ['sum'], aggregatesrenderer:aggregatesrenderer_amount, hidden: <?php echo $system_preference_items['amount_paid_total']?0:1;?>},
                    { text: '<?php echo $CI->lang->line('LABEL_BUTTON_DETAILS'); ?>', dataField: 'button_details', width:'110', filtertype:'none', cellsrenderer: cellsrenderer,hidden: <?php echo $system_preference_items['button_details']?0:1;?>}
                ]
            }
        );
    });
</script>
