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

<form id="save_form" action="<?php echo site_url($CI->controller_url.'/index/save_approve_rollback');?>" method="post">
    <input type="hidden" id="id" name="id" value="<?php echo $item['id']; ?>" />
    <input type="hidden" id="system_save_new_status" name="system_save_new_status" value="0" />
    <div class="row widget">
        <div class="widget-header">
            <div class="title">
                <?php echo $title; ?>
            </div>
            <div class="clearfix"></div>
        </div>
        <div class="col-md-12">
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
                    <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_CREATED_BY');?></label></th>
                    <th class=" header_value"><label class="control-label"><?php echo $users[$item['user_created']]['name'];?></label></th>
                    <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DATE_CREATED_TIME');?></label></th>
                    <th class=""><label class="control-label"><?php echo System_helper::display_date_time($item['date_created']);?></label></th>
                </tr>
                <tr>
                    <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_STATUS_FORWARD');?></label></th>
                    <th class=" header_value"><label class="control-label"><?php echo $item['status_forward'];?></label></th>
                    <th colspan="2">&nbsp;</th>
                </tr>
                <tr>
                    <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_FORWARDED_BY');?></label></th>
                    <th class=" header_value"><label class="control-label"><?php echo $users[$item['user_forwarded']]['name'];?></label></th>
                    <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DATE_FORWARDED_TIME');?></label></th>
                    <th class=""><label class="control-label"><?php echo System_helper::display_date_time($item['date_forwarded']);?></label></th>
                </tr>
                <tr>
                    <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_REMARKS_FORWARD');?></label></th>
                    <th class=" header_value"><label class="control-label"><?php echo nl2br($item['remarks_forward']);?></label></th>
                    <th colspan="2">&nbsp;</th>
                </tr>
                </thead>
            </table>
        </div>
        <div class="clearfix"></div>
        <div style="font-size: 12px;margin-top: -10px;font-style: italic; color: red;" class="row show-grid">
            <div class="col-xs-4"></div>
            <div class="col-sm-4 col-xs-8 text-center">
                <strong>Note:</strong> Budget quantity in packet.
            </div>
        </div>
        <div class="row show-grid">
            <div class="row widget">
                <div class="col-xs-12" id="system_jqx_container"></div>
            </div>
        </div>
        <div class="clearfix"></div>
        <div class="col-md-12">
            <table class="table table-bordered table-responsive system_table_details_view">
                <thead>
                <tr>
                    <th colspan="8" class="text-center bg-success">Crop Wise Information</th>
                </tr>
                <tr>
                    <th rowspan="2" width="2%"><?php echo $CI->lang->line('LABEL_SL_NO');?></th>
                    <th rowspan="2"><?php echo $CI->lang->line('LABEL_CROP_NAME');?></th>
                    <th colspan="3" class="text-center bg-danger">Total Budget</th>
                    <th colspan="3" class="text-center bg-warning">Total Target</th>
                </tr>
                <tr>
                    <th class="text-right bg-danger"><?php echo $CI->lang->line('LABEL_PACK');?></th>
                    <th class="text-right bg-danger"><?php echo $CI->lang->line('LABEL_KG');?></th>
                    <th class="text-right bg-danger">Net Price</th>

                    <th class="text-right bg-warning"><?php echo $CI->lang->line('LABEL_PACK');?></th>
                    <th class="text-right bg-warning"><?php echo $CI->lang->line('LABEL_KG');?></th>
                    <th class="text-right bg-warning">Net Price</th>
                </tr>
                </thead>
                <tbody>
                <?php
                $serial=0;
                $quantity_budget_total=0;
                $quantity_budget_total_kg=0;
                $amount_budget_price_net=0;
                $quantity_budget_total_total=0;
                $quantity_budget_total_total_kg=0;
                $amount_budget_price_net_total=0;

                $quantity_target_total=0;
                $quantity_target_total_kg=0;
                $amount_target_price_net=0;
                $quantity_target_total_total=0;
                $quantity_target_total_total_kg=0;
                $amount_target_price_net_total=0;
                foreach($total_crops as $crop)
                {
                    ++$serial;
                    $quantity_budget_total=$crop['quantity_budget_total'];
                    $quantity_budget_total_kg=$crop['quantity_budget_total_kg'];
                    $amount_budget_price_net=$crop['amount_budget_price_net'];
                    $quantity_budget_total_total+=$quantity_budget_total;
                    $quantity_budget_total_total_kg+=$quantity_budget_total_kg;
                    $amount_budget_price_net_total+=$amount_budget_price_net;

                    $quantity_target_total=$crop['quantity_target_total'];
                    $quantity_target_total_kg=$crop['quantity_target_total_kg'];
                    $amount_target_price_net=$crop['amount_target_price_net'];
                    $quantity_target_total_total+=$quantity_target_total;
                    $quantity_target_total_total_kg+=$quantity_target_total_kg;
                    $amount_target_price_net_total+=$amount_target_price_net;
                    ?>
                    <tr>
                        <td class="text-right"><?php echo $serial;?></td>
                        <td><?php echo $crop['crop_name'];?></td>
                        <td class="text-right"><?php echo System_helper::get_string_quantity($quantity_budget_total);?></td>
                        <td class="text-right"><?php echo System_helper::get_string_kg($quantity_budget_total_kg);?></td>
                        <td class="text-right"><?php echo System_helper::get_string_amount($amount_budget_price_net);?></td>
                        <td class="text-right"><?php echo System_helper::get_string_quantity($quantity_target_total);?></td>
                        <td class="text-right"><?php echo System_helper::get_string_kg($quantity_target_total_kg);?></td>
                        <td class="text-right"><?php echo System_helper::get_string_amount($amount_target_price_net);?></td>
                    </tr>
                <?php
                }
                ?>
                </tbody>
                <tfoot>
                <tr>
                    <th class="text-right" colspan="2"><?php echo $CI->lang->line('LABEL_TOTAL');?></th>
                    <th class="text-right"><?php echo System_helper::get_string_quantity($quantity_budget_total_total);?></th>
                    <th class="text-right"><?php echo System_helper::get_string_kg($quantity_budget_total_total_kg);?></th>
                    <th class="text-right"><?php echo System_helper::get_string_amount($amount_budget_price_net_total);?></th>
                    <th class="text-right"><?php echo System_helper::get_string_quantity($quantity_target_total_total);?></th>
                    <th class="text-right"><?php echo System_helper::get_string_kg($quantity_target_total_total_kg);?></th>
                    <th class="text-right"><?php echo System_helper::get_string_amount($amount_target_price_net_total);?></th>
                </tr>
                </tfoot>
            </table>
        </div>
        <div class="clearfix"></div>
        <div class="widget-header">
            <div class="title">
                Approve or Rollback Confirmation
            </div>
            <div class="clearfix"></div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Approve/Rollback <span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <select id="status_budget_target" class="form-control" name="item[status_budget_target]">
                    <option value=""><?php echo $CI->lang->line('SELECT');?></option>
                    <option value="<?php echo $this->config->item('system_status_approved')?>"><?php echo $this->config->item('system_status_approved')?></option>
                    <option value="<?php echo $this->config->item('system_status_rollback')?>"><?php echo $this->config->item('system_status_rollback')?></option>
                </select>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Approve/Rollback <?php echo $CI->lang->line('LABEL_REMARKS');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <textarea name="item[remarks_budget_target]" id="remarks_budget_target" class="form-control"><?php echo $item['remarks_budget_target'];?></textarea>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">

            </div>
            <div class="col-sm-4 col-xs-4">
                <div class="action_button pull-right">
                    <button id="button_action_save" type="button" class="btn" data-form="#save_form" data-message-confirm="Are You Sure Approve/Rollback to Dealer Monthly Target?">Save</button>
                </div>
            </div>
            <div class="col-sm-4 col-xs-4">

            </div>
        </div>
    </div>
</form>
<?php
$options=array(
    'id'=>$item['id']
);
?>
<script type="text/javascript">
    $(document).ready(function ()
    {
        system_preset({controller:'<?php echo $CI->router->class; ?>'});

        var url = "<?php echo site_url($CI->controller_url.'/index/get_variety/');?>";
        // prepare the data
        var source =
        {
            dataType: "json",
            dataFields: [
                <?php
                 foreach($system_preference_items as $key=>$item)
                 {
                     if(($key=='crop_name')||($key=='crop_type_name')||($key=='variety_id')||($key=='variety_name')||($key=='pack_size_id')||($key=='pack_size'))
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
        var header_render=function (text, align)
        {
            var words = text.split(" ");
            var label=words[0];
            var count=words[0].length;
            for (i = 1; i < words.length; i++)
            {
                if((count+words[i].length)>10)
                {
                    label=label+'</br>'+words[i];
                    count=words[i].length;
                }
                else
                {
                    label=label+' '+words[i];
                    count=count+words[i].length;
                }

            }
            return '<div style="margin: 5px;">'+label+'</div>';
        };
        var cellsrenderer = function(row, column, value, defaultHtml, columnSettings, record)
        {
            var element = $(defaultHtml);
            var price_net=parseFloat(record['price_net']);

            if(column=='quantity_budget_total')
            {
                var total_quantity=0;
                <?php
                foreach($dealers as $dealer)
                {
                ?>
                if(!isNaN(parseFloat(record['<?php echo 'quantity_budget_'.$dealer['farmer_id'];?>'])))
                {
                    total_quantity+=parseFloat(record['<?php echo 'quantity_budget_'.$dealer['farmer_id'];?>']);
                }
                <?php
                }
                ?>
                if(total_quantity==0)
                {
                    element.html('');
                }
                else
                {
                    element.html(total_quantity);
                }
            }
            else if(column=='amount_price_total')
            {
                var total_quantity=0;
                <?php
                foreach($dealers as $dealer)
                {
                ?>
                if(!isNaN(parseFloat(record['<?php echo 'quantity_budget_'.$dealer['farmer_id'];?>'])))
                {
                    total_quantity+=parseFloat(record['<?php echo 'quantity_budget_'.$dealer['farmer_id'];?>']);
                }
                <?php
                }
                ?>
                if((total_quantity==0)||(record['amount_price_net']==0))
                {
                    element.html('');
                }
                else
                {
                    element.html(get_string_amount(total_quantity*record['amount_price_net']));
                }
            }
            else if(column=='quantity_budget_target_total')
            {
                if(value==0)
                {
                    element.html('');
                }
                else
                {
                    element.html(get_string_quantity(value));
                }
            }
            else if(column=='amount_price_total_target')
            {
                var total_quantity_target=0;
                total_quantity_target=record['quantity_budget_target_total'];
                if((total_quantity_target==0)||(record['amount_price_net']==0))
                {
                    element.html('');
                }
                else
                {
                    element.html(get_string_amount(total_quantity_target*record['amount_price_net']));
                }

            }
            return element[0].outerHTML;
        };
        // create jqxgrid.
        $("#system_jqx_container").jqxGrid(
        {
            width: '100%',
            height: '350px',
            filterable: true,
            sortable: true,
            showfilterrow: true,
            source: dataAdapter,
            columnsresize: true,
            columnsreorder: true,
            enablebrowserselection: true,
            altrows: true,
            rowsheight: 35,
            columnsheight: 70,
            columns: [
                { text: '<?php echo $CI->lang->line('LABEL_CROP_NAME'); ?>', dataField: 'crop_name',width:'100', filtertype:'list',pinned:true,editable:false},
                { text: '<?php echo $CI->lang->line('LABEL_CROP_TYPE_NAME'); ?>', dataField: 'crop_type_name',width:'100', filtertype:'list',pinned:true,renderer: header_render,cellsrenderer: cellsrenderer,editable:false},
                { text: '<?php echo $CI->lang->line('LABEL_VARIETY_NAME'); ?>', dataField: 'variety_name',width:'150', filtertype:'list',pinned:true,renderer: header_render,cellsrenderer: cellsrenderer,editable:false},
                { text: '<?php echo $CI->lang->line('LABEL_PACK_SIZE'); ?>', dataField: 'pack_size',width:'50', filtertype:'list',pinned:true,renderer: header_render,cellsrenderer: cellsrenderer,cellsalign: 'right',editable:false},
                { text: '<?php echo $CI->lang->line('LABEL_PRICE_UNIT_NET'); ?>', dataField: 'amount_price_net',width:'80',filterable:false,pinned:true,renderer: header_render,cellsrenderer: cellsrenderer,cellsalign: 'right',editable:false},
                { text: '<?php echo $CI->lang->line('LABEL_QUANTITY_BUDGET_TOTAL_PKT'); ?>', dataField: 'quantity_budget_total',width:'80',filterable:false,pinned:true,renderer: header_render,cellsrenderer: cellsrenderer,cellsalign: 'right',editable:false},
                { text: '<?php echo $CI->lang->line('LABEL_AMOUNT_BUDGETED_TOTAL_NET'); ?>', dataField: 'amount_price_total',width:'130',filterable:false,pinned:true,renderer: header_render,cellsrenderer: cellsrenderer,cellsalign: 'right',editable:false},
                { text: '<?php echo $CI->lang->line('LABEL_QUANTITY_TARGET_TOTAL_PKT'); ?>', dataField: 'quantity_budget_target_total',width:'130',filterable:false,pinned:true,renderer: header_render,cellsrenderer: cellsrenderer,cellsalign: 'right',editable:false},
                { text: '<?php echo $CI->lang->line('LABEL_AMOUNT_TARGETED_TOTAL_NET'); ?>', dataField: 'amount_price_total_target',width:'130',filterable:false,pinned:true,renderer: header_render,cellsrenderer: cellsrenderer,cellsalign: 'right',editable:false},
                <?php
                $serial=0;
                foreach($dealers as $dealer)
                {
                ++$serial;
                ?>
                { text: '<?php echo $serial.'. '.$dealer['farmer_name']?>',renderer: header_render, dataField: 'quantity_budget_<?php echo $dealer['farmer_id']?>',width:'100',filterable:false,cellsalign: 'right',editable:false,cellsrenderer: cellsrenderer},
                <?php
                }
                ?>
            ]
        });
    });
</script>