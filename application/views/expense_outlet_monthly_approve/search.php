<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$CI=& get_instance();
$action_buttons=array();
$action_buttons[]=array(
    'label'=>$CI->lang->line("ACTION_BACK"),
    'href'=>site_url($CI->controller_url)
);
$action_buttons[]=array(
    'label'=>$CI->lang->line("ACTION_REFRESH"),
    'href'=>site_url($CI->controller_url.'/index/search')
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
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_OUTLET');?><span style="color:#FF0000">*</span></label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <?php
            if(sizeof($CI->user_outlets)==1)
            {
                ?>
                <input type="hidden" name="item[outlet_id]" id="outlet_id" value="<?php echo $CI->user_outlets[0]['customer_id'] ?>" />
                <label class="control-label"><?php echo $CI->user_outlets[0]['name'] ?></label>
            <?php
            }
            else
            {
                ?>
                <select name="item[outlet_id]" id="outlet_id" class="form-control">
                    <?php
                    foreach($CI->user_outlets as $row)
                    {?>
                        <option value="<?php echo $row['customer_id']?>"><?php echo $row['name'];?></option>
                    <?php
                    }
                    ?>
                </select>
            <?php
            }
            ?>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_YEAR');?><span style="color:#FF0000">*</span></label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <?php
            $year_budget = date('Y', strtotime('-1 month'));
            $year_next=date('Y', strtotime('+1 year'));
            ?>
            <select id="year" class="form-control" name="item[year]" >
                <?php
                for($i=$year_budget;$i<=$year_next;$i++)
                {
                    ?>
                    <option value="<?php echo $i;?>" <?php if($i==$year_budget){echo "selected='selected'";}?>><?php echo date("Y",mktime(0,0,0,1,1,$i))?></option>
                <?php
                }
                ?>
            </select>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_MONTH');?><span style="color:#FF0000">*</span></label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <?php
            $month_budget = date('n', strtotime('-1 month'));
            ?>
            <select id="month" class="form-control" name="item[month]" >
                <?php
                for($i=1;$i<13;$i++)
                {
                    ?>
                    <option value="<?php echo $i;?>" <?php if($i==$month_budget){echo "selected='selected'";}?>><?php echo date("F", mktime(0, 0, 0,  $i,1, 2000));?></option>
                    <?php
                }
                ?>
            </select>
        </div>
    </div>
</div>

<div class="clearfix"></div>

<div id="system_report_container">

</div>
<script type="text/javascript">

    jQuery(document).ready(function()
    {
        $("#crop_id").html(get_dropdown_with_select(system_crops));
        $(document).off("change", "#outlet_id");
        $(document).on("change","#outlet_id",function()
        {
            $("#system_report_container").html("");
            add_edit();
        });
        $(document).off("change", "#year");
        $(document).on("change","#year",function()
        {
            $("#system_report_container").html("");
            add_edit();
        });
        $(document).off("change", "#month");
        $(document).on("change","#month",function()
        {
            $("#system_report_container").html("");
            add_edit();
        });

        add_edit();
    });
    function add_edit()
    {
        $("#system_report_container").html("");
        var outlet_id=$('#outlet_id').val();
        var year=$('#year').val();
        var month=$('#month').val();
        $.ajax({
            url:'<?php echo site_url($CI->controller_url.'/index/add_edit') ?>',
            type: 'POST',
            datatype: "JSON",
            data:{outlet_id:outlet_id,year:year,month:month},
            success: function (data, status)
            {

            },
            error: function (xhr, desc, err)
            {
                console.log("error");

            }
        });
    }
</script>
