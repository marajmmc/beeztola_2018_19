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
$CI->load->view('action_buttons',array('action_buttons'=>$action_buttons));
?>

<div class="row widget">
    <div class="widget-header">
        <div class="title">
            <?php echo $title; ?>
        </div>
        <div class="clearfix"></div>
    </div>
    <div class="col-xs-12" id="system_jqx_container">

    </div>
</div>
<div class="clearfix"></div>
<script type="text/javascript">
    $(document).ready(function ()
    {
        var url = "<?php echo site_url($CI->controller_url.'/index/get_items_outlets_amount');?>";

        // prepare the data
        var source =
        {
            dataType: "json",
            dataFields: [
                { name: 'area', type: 'string' },
                <?php
                foreach($fiscal_years as $fy)
                {
                    ?>
                    { name: 'sales_<?php echo $fy['id']; ?>_amount', type: 'number' },
                    <?php
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
            if(column.substr(-6)=='amount')
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
        var tooltiprenderer = function (element) {
            $(element).jqxTooltip({position: 'mouse', content: $(element).text() });
        };
        var aggregates=function (total, column, element, record)
        {
            if(record.area=="Grand Total")
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
        var aggregatesrenderer_amount=function (aggregates)
        {
            var text='';
            if(!((aggregates['total']=='0.000')||(aggregates['total']=='')))
            {
                text=get_string_amount(aggregates['total'])
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
                rowsheight: 35,
                columns: [
                    {
                        text: '<?php echo $CI->lang->line('LABEL_SL_NO'); ?>',datafield: 'sl_no',pinned:true,width:'30', columntype: 'number',cellsalign: 'right',
                        cellsrenderer: function(row, column, value, defaultHtml, columnSettings, record)
                        {
                            var element = $(defaultHtml);
                            element.html(value+1);
                            return element[0].outerHTML;
                        }
                    },
                    { text: 'Outlet', dataField: 'area',pinned:true,width:'200',sortable: false,menu: false,cellsrenderer: cellsrenderer,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer},
                    <?php
                    for($i=sizeof($fiscal_years)-1;$i>=0;$i--)
                    {?>
                        {text: '<?php echo $fiscal_years[$i]['name']; ?>', dataField: 'sales_<?php echo $fiscal_years[$i]['id']; ?>_amount',width:'120',align:'center',cellsAlign:'right',cellsrenderer: cellsrenderer,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer_amount},
                        <?php
                    }
                    ?>

                ]
            });
    });
</script>