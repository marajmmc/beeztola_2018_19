<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$CI=& get_instance();
$action_buttons=array();
if(isset($CI->permissions['action0']) && ($CI->permissions['action0']==1))
{
    $action_buttons[]=array(
        'label'=>'All List',
        'href'=>site_url($CI->controller_url.'/index/list_all')
    );
}
if(isset($CI->permissions['action2']) && ($CI->permissions['action2']==1))
{
    $action_buttons[]=array
    (
        'type'=>'button',
        'label'=>$CI->lang->line('ACTION_RECEIVE'),
        'class'=>'button_jqx_action',
        'data-action-link'=>site_url($CI->controller_url.'/index/edit')
    );
}
if(isset($CI->permissions['action0']) && ($CI->permissions['action0']==1))
{
    $action_buttons[]=array
    (
        'type'=>'button',
        'label'=>$CI->lang->line('ACTION_DETAILS'),
        'class'=>'button_jqx_action',
        'data-action-link'=>site_url($CI->controller_url.'/index/details')
    );
}
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
        'href'=>site_url($CI->controller_url.'/index/set_preference')
    );
}
$action_buttons[]=array(
    'label'=>$CI->lang->line("ACTION_REFRESH"),
    'href'=>site_url($CI->controller_url.'/index/list')
);
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
        ?>
        <div class="row show-grid">
            <?php $CI->load->view('preference',array('system_preference_items'=>$system_preference_items)); ?>
        </div>
        <?php
    }
    if(isset($CI->permissions['action2']) && ($CI->permissions['action2']==1))
    {
        ?>
        <div class="row show-grid">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">
                        <label class=""><a class="external text-danger" data-toggle="collapse" data-target="#payment_receive_save" href="#">+Instant Save</a></label>
                    </h4>
                </div>
                <div id="payment_receive_save" class="panel-collapse collapse out" style="padding-top: 10px;">
                    <div class="row show-grid">
                        <div class="col-xs-2">
                            <label class="control-label pull-right">Number Of Item Selected</label>
                        </div>
                        <div class="col-xs-2" id="payment_selected_items">0</div>
                        <div class="col-xs-2">
                            <label class="control-label pull-right">Total Receive Amount</label>
                        </div>
                        <div class="col-xs-1" id="payment_amount_receive_total">0.00</div>
                        <div class="col-xs-2">
                            <label class="control-label pull-right">Total Bank Charge</label>
                        </div>
                        <div class="col-xs-1" id="payment_amount_bank_charge_total">0.00</div>
                    </div>
                    <div class="row show-grid">
                        <div class="col-xs-2">
                            <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DATE_RECEIVE');?><span style="color:#FF0000">*</span></label>
                        </div>
                        <div class="col-xs-2">
                            <input type="text" id="payment_date_receive" class="form-control datepicker" value="" readonly/>
                        </div>
                        <div class="col-xs-2">
                            <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_AMOUNT_BANK_CHARGE');?><span style="color:#FF0000">*</span></label>
                        </div>
                        <div class="col-xs-1">
                            <input type="text" id="payment_amount_bank_charge" class="form-control text-right float_type_positive" value=""/>
                        </div>
                        <div class="col-xs-2">
                            <div class="action_button">
                                <button id="button_save_payment" type="button" class="btn"><?php echo $CI->lang->line("ACTION_SAVE"); ?></button>
                            </div>
                        </div>
                        <div class="col-xs-2">
                            <div class="action_button">
                                <button id="button_clear_payment" type="button" class="btn">Deselect ALL</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
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
        $(".datepicker").datepicker({dateFormat : display_date_format});
        var url = "<?php echo site_url($CI->controller_url.'/index/get_items');?>";
        var source =
        {
            dataType: "json",
            datafields: [
                { name: 'id', type: 'int' },
                { name: 'barcode', type: 'string' },
                { name: 'date_payment', type: 'string'},
                { name: 'date_sale', type: 'string'},
                { name: 'outlet_name', type: 'string'},
                { name: 'payment_way', type: 'string'},
                { name: 'reference_no', type: 'string'},
                { name: 'amount_payment', type: 'string'},
                { name: 'bank_payment_source', type: 'string'},
                { name: 'bank_branch_source', type: 'string'},
                { name: 'bank_account_number_destination', type: 'string'}
            ],
            deleterow: function (rowid, commit)
            {
                commit(true);
            },
            id: 'id',
            type: 'POST',
            url: url
        };

        var cellsrenderer = function(row, column, value, defaultHtml, columnSettings, record)
        {
            var element = $(defaultHtml);
            if(column=='amount_payment')
            {
                element.html(get_string_amount(value));
            }

            return element[0].outerHTML;

        };
        var dataAdapter = new $.jqx.dataAdapter(source);
        $("#system_jqx_container").jqxGrid(
            {
                width: '100%',
                source: dataAdapter,
                pageable: true,
                filterable: true,
                sortable: true,
                showfilterrow: true,
                columnsresize: true,
                pagesize:50,
                pagesizeoptions: ['50', '100', '200','300','500','1000','5000'],
                selectionmode: 'multiplerows',
                altrows: true,
                height: '350px',
                enablebrowserselection:true,
                columnsreorder: true,
                columns:
                    [
                        { text: '<?php echo $CI->lang->line('LABEL_BARCODE'); ?>', dataField: 'barcode',width:'80',hidden: <?php echo $system_preference_items['barcode']?0:1;?>},
                        { text: '<?php echo $CI->lang->line('LABEL_DATE_PAYMENT'); ?>', dataField: 'date_payment',width:'100',hidden: <?php echo $system_preference_items['date_payment']?0:1;?>},
                        { text: '<?php echo $CI->lang->line('LABEL_DATE_SALE'); ?>', dataField: 'date_sale',width:'100',hidden: <?php echo $system_preference_items['date_sale']?0:1;?>},
                        { text: '<?php echo $CI->lang->line('LABEL_OUTLET_NAME'); ?>',dataField: 'outlet_name',width:'100',filterType:'list',hidden: <?php echo $system_preference_items['outlet_name']?0:1;?>},
                        { text: '<?php echo $CI->lang->line('LABEL_PAYMENT_WAY'); ?>',dataField: 'payment_way',width:'100',filterType:'list',hidden: <?php echo $system_preference_items['payment_way']?0:1;?>},
                        { text: '<?php echo $CI->lang->line('LABEL_REFERENCE_NO'); ?>',dataField: 'reference_no',width:'100',hidden: <?php echo $system_preference_items['reference_no']?0:1;?>},
                        { text: '<?php echo $CI->lang->line('LABEL_AMOUNT_PAYMENT'); ?>',dataField: 'amount_payment',width:'150',cellsAlign:'right',cellsrenderer: cellsrenderer, hidden: <?php echo $system_preference_items['amount_payment']?0:1;?>},
                        { text: '<?php echo $CI->lang->line('LABEL_BANK_PAYMENT_SOURCE'); ?>',dataField: 'bank_payment_source',filterType:'list',width:'150',hidden: <?php echo $system_preference_items['bank_payment_source']?0:1;?>},
                        { text: '<?php echo $CI->lang->line('LABEL_BANK_BRANCH_SOURCE'); ?>',dataField: 'bank_branch_source',width:'150',filterType:'list',hidden: <?php echo $system_preference_items['bank_branch_source']?0:1;?>},
                        { text: '<?php echo $CI->lang->line('LABEL_BANK_ACCOUNT_NUMBER_DESTINATION'); ?>',dataField: 'bank_account_number_destination',filterType:'list',hidden: <?php echo $system_preference_items['bank_account_number_destination']?0:1;?>}
                    ]
            });
        $(document).off('input','#payment_amount_bank_charge');
        $(document).on('input', '#payment_amount_bank_charge', function()
        {
            calculate_total();
        });

        $("#system_jqx_container").on('rowselect', function (event)
        {
            calculate_total();
        });
        // display unselected row index.
        $("#system_jqx_container").on('rowunselect', function (event)
        {   calculate_total();
        });
        $(document).off("click", "#button_clear_payment");
        $(document).on("click", "#button_clear_payment", function(event)
        {
            var jqx_grid_id='#system_jqx_container';
            $(jqx_grid_id).jqxGrid('clearselection');
            calculate_total();
        });
        $(document).off("click", "#button_save_payment");
        $(document).on("click", "#button_save_payment", function(event)
        {
            var selected_ids=',';
            var jqx_grid_id='#system_jqx_container';
            var selected_row_indexes = $(jqx_grid_id).jqxGrid('getselectedrowindexes');
            var total=0;
            for( var i=0;i<selected_row_indexes.length;i++)
            {
                var selectedRowData = $(jqx_grid_id).jqxGrid('getrowdata', selected_row_indexes[i]);//only last selected
                total=total+parseFloat(selectedRowData.amount_payment.replace(/,/g,''));
                selected_ids+=selectedRowData.id+',';
            }
            var payment_amount_bank_charge_total='0.00';
            if($('#payment_amount_bank_charge').val()==parseFloat($('#payment_amount_bank_charge').val()))
            {
                payment_amount_bank_charge_total=get_string_amount(parseFloat($('#payment_amount_bank_charge').val()*selected_row_indexes.length));
            }
            var message="Total Selected :"+selected_row_indexes.length;
            message+="\nTotal Amount :"+get_string_amount(total);
            message+="\nTotal Bank Charge :"+payment_amount_bank_charge_total;
            message+="\nAre You Sure to Save?";
            var sure = confirm(message);
            if(!sure)
            {
                return;
            }
            else
            {
                $.ajax({
                    url: '<?php echo site_url($CI->controller_url.'/index/save_multiple');?>',
                    type: 'POST',
                    dataType: "JSON",
                    data:{ids:selected_ids,date_receive:$('#payment_date_receive').val(),amount_bank_charge:$('#payment_amount_bank_charge').val()},
                    success: function (data, status)
                    {
                        if(data.status)
                        {
                            $.each(data.ids, function( index, id ) {
                                //console.log( index + ": " + id );
                                $(jqx_grid_id).jqxGrid('deleterow', id);
                            });
                            $(jqx_grid_id).jqxGrid('clearselection');
                            $('#payment_amount_bank_charge').val('');
                            calculate_total();
                        }

                    },
                    error: function (xhr, desc, err)
                    {
                        console.log("error");

                    }
                });
            }
        });
    });
    function calculate_total()
    {
        var jqx_grid_id='#system_jqx_container';
        var selected_row_indexes = $(jqx_grid_id).jqxGrid('getselectedrowindexes');
        var total=0;
        for( var i=0;i<selected_row_indexes.length;i++)
        {
            var selectedRowData = $(jqx_grid_id).jqxGrid('getrowdata', selected_row_indexes[i]);//only last selected
            total=total+parseFloat(selectedRowData.amount_payment.replace(/,/g,''));
        }
        $('#payment_selected_items').html(selected_row_indexes.length);
        $('#payment_amount_receive_total').html(get_string_amount(total));
        if($('#payment_amount_bank_charge').val()==parseFloat($('#payment_amount_bank_charge').val()))
        {
            $('#payment_amount_bank_charge_total').html(get_string_amount(parseFloat($('#payment_amount_bank_charge').val()*selected_row_indexes.length)));
        }
        else
        {
            $('#payment_amount_bank_charge_total').html('0.00');
        }
        //console.log(selected_row_indexes.length+' '+total);
    }
</script>
