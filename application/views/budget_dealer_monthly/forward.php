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

<form id="save_form" action="<?php echo site_url($CI->controller_url.'/index/save_forward');?>" method="post">
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
                    <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_ID');?></label></th>
                    <th class=""><label class="control-label"><?php echo $item['id'];?></label></th>
                    <th colspan="2">&nbsp;</th>
                </tr>
                <tr>
                    <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_OUTLET');?></label></th>
                    <th class=" header_value"><label class="control-label"><?php echo $item['outlet_name'];?></label></th>
                    <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_MONTH');?></label></th>
                    <th class=" header_value"><label class="control-label"><?php echo date("F", mktime(0, 0, 0,  $item['month_id'],1, 2000));;?></label></th>
                </tr>
                <tr>
                    <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_CROP_NAME');?></label></th>
                    <th class=" header_value"><label class="control-label"><?php echo $item['crop_name'];?></label></th>
                    <th colspan="2">&nbsp;</th>
                </tr>
                <tr>
                    <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_CREATED_BY');?></label></th>
                    <th class=" header_value"><label class="control-label"><?php echo $users[$item['user_created']]['name'];?></label></th>
                    <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DATE_CREATED_TIME');?></label></th>
                    <th class=""><label class="control-label"><?php echo System_helper::display_date_time($item['date_created']);?></label></th>
                </tr>
                <?php
                if($item['user_updated'])
                {
                    ?>
                    <tr>
                        <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_UPDATED_BY');?></label></th>
                        <th class=" header_value"><label class="control-label"><?php echo $users[$item['user_updated']]['name'];?></label></th>
                        <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DATE_UPDATED_TIME');?></label></th>
                        <th class=""><label class="control-label"><?php echo System_helper::display_date_time($item['date_updated']);?></label></th>
                    </tr>
                <?php
                }
                ?>
                <tr>
                    <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_STATUS_FORWARD');?></label></th>
                    <th class=" header_value"><label class="control-label"><?php echo $item['status_forward'];?></label></th>
                    <th class="widget-header header_caption"><label class="control-label pull-right">Number of Edit</label></th>
                    <th class=" header_value"><label class="control-label"><?php echo $item['revision_count']?></label></th>
                </tr>
                </thead>
            </table>
        </div>
        <div class="clearfix"></div>
        <div class="row show-grid">
            <div class="row widget">
                <div class="col-xs-12" id="system_jqx_container"></div>
            </div>
        </div>
        <div class="widget-header">
            <div class="title">
                Forward Confirmation
            </div>
            <div class="clearfix"></div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_FORWARD');?> <span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <select id="status_forward" class="form-control" name="item[status_forward]">
                    <option value=""><?php echo $CI->lang->line('SELECT');?></option>
                    <option value="<?php echo $this->config->item('system_status_forwarded')?>"><?php echo $this->config->item('system_status_forwarded')?></option>
                </select>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_REMARKS_FORWARD');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <textarea name="item[remarks_forward]" id="remarks_forward" class="form-control"><?php echo $item['remarks_forward'];?></textarea>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">

            </div>
            <div class="col-sm-4 col-xs-4">
                <div class="action_button pull-right">
                    <button id="button_action_save" type="button" class="btn" data-form="#save_form" data-message-confirm="Are You Sure Forward to Dealer Monthly Budget?">Save</button>
                </div>
            </div>
            <div class="col-sm-4 col-xs-4">

            </div>
        </div>
    </div>
</form>
<?php
$options=array(
    'outlet_id'=>$item['outlet_id'],
    'month_id'=>$item['month_id'],
    'crop_id'=>$item['crop_id']
);
?>
<script type="text/javascript">
    $(document).ready(function ()
    {
        system_preset({controller:'<?php echo $CI->router->class; ?>'});

        var url = "<?php echo site_url($CI->controller_url.'/index/get_items_variety/');?>";
        // prepare the data
        var source =
        {
            dataType: "json",
            dataFields: [
                { name: 'id', type: 'int' },
                { name: 'crop_id', type: 'string' },
                { name: 'crop_name', type: 'string' },
                { name: 'crop_type_name', type: 'string' },
                { name: 'variety_id', type: 'string' },
                { name: 'variety_name', type: 'string' },
                { name: 'pack_size_id', type: 'string' },
                { name: 'pack_size', type: 'string' },
                <?php
                foreach($dealers as $dealer)
                {
                ?>
                { name: 'amount_budget_<?php echo $dealer['farmer_id']?>', type: 'string' },
                <?php
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

            if(column=='total_budget')
            {
                var total_budget=0;
                <?php
                foreach($dealers as $dealer)
                {
                ?>
                if(!isNaN(parseFloat(record['<?php echo 'amount_budget_'.$dealer['farmer_id'];?>'])))
                {
                    total_budget+=parseFloat(record['<?php echo 'amount_budget_'.$dealer['farmer_id'];?>']);
                }
                <?php
                }
                ?>

                if(total_budget==0)
                {
                    element.html('');
                    element.css({ 'background-color': '#FF0000','margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
                }
                else
                {
                    element.html(total_budget);
                    element.css({ 'background-color': '#00FF00','margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
                }
            }
            return element[0].outerHTML;
        };
        // create jqxgrid.
        $("#system_jqx_container").jqxGrid(
            {
                width: '100%',
                height: '350px',
                source: dataAdapter,
                columnsresize: true,
                columnsreorder: true,
                enablebrowserselection: true,
                altrows: true,
                rowsheight: 35,
                editable:true,
                columns: [
                    { text: '<?php echo $CI->lang->line('LABEL_CROP_NAME'); ?>', dataField: 'crop_name',pinned:true,width:'200',editable:false},
                    { text: '<?php echo $CI->lang->line('LABEL_CROP_TYPE_NAME'); ?>', dataField: 'crop_type_name',pinned:true,width:'200',editable:false},
                    { text: '<?php echo $CI->lang->line('LABEL_VARIETY_NAME'); ?>', dataField: 'variety_name',pinned:true,width:'200',editable:false},
                    { text: '<?php echo $CI->lang->line('LABEL_PACK_SIZE'); ?>', dataField: 'pack_size',pinned:true,width:'200',editable:false},
                    <?php
                    $serial=0;
                    foreach($dealers as $dealer)
                    {
                    ++$serial;
                    ?>
                    { text: '<?php echo $serial.'. '.$dealer['farmer_name']?>', dataField: 'amount_budget_<?php echo $dealer['farmer_id']?>',width:'200',cellsalign: 'right',editable:false},
                    <?php
                    }
                    ?>
                    { text: '<?php echo $CI->lang->line('LABEL_TOTAL'); ?>', dataField: 'total_budget',width:'200',cellsrenderer: cellsrenderer,cellsalign: 'right',editable:false}
                ]
            });
    });
</script>