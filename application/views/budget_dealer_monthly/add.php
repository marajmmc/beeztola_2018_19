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
    'href'=>site_url($CI->controller_url.'/index/add')
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
            <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_OUTLET_NAME');?></label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <?php
            if(sizeof($user_outlets)>1)
            {
                ?>
                <select id="outlet_id" name="item['outlet_id']" class="form-control">
                    <?php
                    foreach($user_outlets as $row)
                    {?>
                        <option value="<?php echo $row['customer_id']?>"><?php echo $row['name'];?></option>
                    <?php
                    }
                    ?>
                </select>
            <?php
            }
            else
            {
                ?>
                <label class="control-label"><?php echo $user_outlets[0]['name'];?></label>
                <input type="hidden" id='outlet_id' name="item['outlet_id']" value="<?php echo $user_outlets[0]['id'];?>">
            <?php
            }
            ?>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_MONTH');?><span style="color:#FF0000">*</span></label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <?php
            if($item['id']>0)
            {
                ?>
                <label class="control-label"><?php echo date("F", mktime(0, 0, 0,  $item['month_id'],1, 2000));?></label>
            <?php
            }
            else
            {
                ?>
                <select id="month_id" class="form-control" name="item[month_id]" >
                    <option value=""><?php echo $CI->lang->line('SELECT');?></option>
                    <?php
                    for($i=1;$i<13;$i++)
                    {
                        ?>
                        <option value="<?php echo $i;?>"><?php echo date("F", mktime(0, 0, 0,  $i,1, 2000));?></option>
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
            <label class="control-label pull-right">Dealer <span style="color:#FF0000">*</span></label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <?php
            if($item['id']>0)
            {
                ?>
                <label class="control-label"><?php echo '';?></label>
            <?php
            }
            else
            {
                ?>
                <select id="dealer_id" class="form-control" name="item[dealer_id]" >
                    <option value=""><?php echo $CI->lang->line('SELECT');?></option>
                    <option value="1">Test</option>

                </select>
            <?php
            }
            ?>
        </div>
    </div>
    <div style="" class="row show-grid" id="crop_id_container">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_CROP_NAME');?></label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <select id="crop_id" name="item['crop_id']" class="form-control">
                <option value=""><?php echo $CI->lang->line('SELECT');?></option>
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
            $("#month_id").val("");
            $("#dealer_id").val("");
            $("#crop_id").val("");
        });
        $(document).off("change", "#month_id");
        $(document).on("change","#month_id",function()
        {
            $("#system_report_container").html("");
            $("#dealer_id").val("");
            $("#crop_id").val("");
        });
        $(document).off("change", "#dealer_id");
        $(document).on("change","#dealer_id",function()
        {
            $("#system_report_container").html("");
            $("#crop_id").val("");
        });
        $(document).off("change", "#crop_id");
        $(document).on("change","#crop_id",function()
        {
            $("#system_report_container").html("");
            var crop_id=$('#crop_id').val();
            if(crop_id>0)
            {
                $.ajax({
                    url:'<?php echo site_url($CI->controller_url.'/index/variety_list') ?>',
                    type: 'POST',
                    datatype: "JSON",
                    data:{outlet_id:$("#outlet_id").val(),month_id:$("#month_id").val(),dealer_id:$("#dealer_id").val(),crop_id:crop_id},
                    success: function (data, status)
                    {

                    },
                    error: function (xhr, desc, err)
                    {
                        console.log("error");

                    }
                });
            }
        });

    });
</script>
