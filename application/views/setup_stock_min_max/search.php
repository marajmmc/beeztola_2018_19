<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$CI=& get_instance();
$action_buttons=array();
if((isset($CI->permissions['action1']) && ($CI->permissions['action1']==1)) || (isset($CI->permissions['action2']) && ($CI->permissions['action2']==1)))
{
    $action_buttons[]=array
    (
        'type'=>'button',
        'label'=>$CI->lang->line("ACTION_SAVE"),
        'id'=>'button_action_save',
        'data-form'=>'#save_form'
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
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_OUTLET_NAME');?></label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <?php
            if(sizeof($user_outlets)>1)
            {
                ?>
                <select id="customer_id" class="form-control">
                    <?php
                    foreach($user_outlets as $row)
                    {?>
                        <option value="<?php echo $row['id']?>"><?php echo $row['name'];?></option>
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
                <input type="hidden" id='customer_id' value="<?php echo $user_outlets[0]['id'];?>">
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
            <select id="crop_id" class="form-control">
                <option value=""><?php echo $this->lang->line('SELECT');?></option>
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
        $(document).off("change", "#customer_id");
        $(document).on("change","#customer_id",function()
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
                    url:'<?php echo site_url($CI->controller_url.'/index/list') ?>',
                    type: 'POST',
                    datatype: "JSON",
                    data:{customer_id:$("#customer_id").val(),crop_id:crop_id},
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
