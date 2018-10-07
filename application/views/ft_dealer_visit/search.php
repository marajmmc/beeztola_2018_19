<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$CI=& get_instance();
$action_buttons=array();
$action_buttons[]=array
(
    'label'=>$CI->lang->line("ACTION_BACK"),
    'href'=>site_url($CI->controller_url)
);
if((isset($CI->permissions['action1']) && ($CI->permissions['action1']==1)) || (isset($CI->permissions['action2']) && ($CI->permissions['action2']==1)))
{
    $action_buttons[]=array
    (
        'type'=>'button',
        'label'=>$CI->lang->line("ACTION_SAVE"),
        'id'=>'button_action_save',
        'data-form'=>'#save_form'
    );
    $action_buttons[]=array
    (
        'type'=>'button',
        'label'=>$CI->lang->line("ACTION_SAVE_NEW"),
        'id'=>'button_action_save_new',
        'data-form'=>'#save_form'
    );
}
$action_buttons[]=array(
    'type'=>'button',
    'label'=>$CI->lang->line("ACTION_CLEAR"),
    'id'=>'button_action_clear',
    'data-form'=>'#save_form'
);
$CI->load->view('action_buttons',array('action_buttons'=>$action_buttons));
?>
<form id="save_form" action="<?php echo site_url($CI->controller_url.'/index/save');?>" method="post">
    <input type="hidden" id="id" name="id" value="<?php echo $item['id']?>" />
    <input type="hidden" id="system_save_new_status" name="system_save_new_status" value="0" />
    <div class="row widget">
        <div class="widget-header">
            <div class="title">
                <?php echo $title; ?>
            </div>
            <div class="clearfix"></div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_DATE');?><span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <?php
                if(isset($this->permissions['action7'])&&($this->permissions['action7']==1))
                {
                    ?>
                    <input type="text" name="item[date]" id="date" class="form-control datepicker" value="<?php echo System_helper::display_date($item['date']);?>" readonly />
                <?php
                }
                else
                {
                    ?>
                    <label class="control-label"><?php echo System_helper::display_date($item['date']);?></label>
                    <input type="hidden" name="item[date]" id="date" value="<?php echo System_helper::display_date($item['date']);?>" />
                <?php
                }
                ?>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_OUTLET');?><span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <?php
                if(sizeof($outlets)==1)
                {
                    ?>
                        <input type="hidden" name="item[outlet_id]" id="outlet_id" value="<?php echo $outlets[0]['customer_id'] ?>" />
                        <label class="control-label"><?php echo $outlets[0]['name'] ?></label>
                    <?php 
                }
                else
                {
                    ?>
                    <select name="item[outlet_id]" id="outlet_id" class="form-control">
                        <option value=""><?php echo $CI->lang->line('SELECT');?></option>
                        <?php
                        foreach($outlets as $row)
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
        <div style="<?php if(sizeof($outlets)>1 && !($item['id']>0)){echo 'display:none';} ?>" class="row show-grid" id="dealer_id_container">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DEALER');?><span style="color:#FF0000">*</span></label>
            </div>
            <?php 
            if(sizeof($dealers)==1)
            {
                ?>
                    <input type="hidden" name="item[dealer_id]" id="dealer_id" value="<?php echo $dealers[0]['value'] ?>" />
                    <label class="control-label"><?php echo $dealers[0]['text'] ?></label>
                <?php 
            }
            else
            {
                ?>
                <div class="col-sm-4 col-xs-8">
                    <select id="dealer_id" name="item[dealer_id]" class="form-control">
                        <option value=""><?php echo $CI->lang->line('SELECT');?></option>
                        <?php
                        foreach($dealers as $dealer)
                        {?>
                            <option value="<?php echo $dealer['value']?>"><?php echo $dealer['text'];?></option>
                        <?php
                        }
                        ?>
                    </select>
                </div>
                <?php
            }
            ?>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_REMARKS');?> </label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <textarea name="item[remarks]" id="description" class="form-control" ><?php echo $item['remarks'];?></textarea>
            </div>
        </div>
    </div>
    <div class="clearfix"></div>
    <div id="visit_head_container">
        
    </div>
</form>
<script>
    jQuery(document).ready(function()
    {

        system_off_events();
        system_preset({controller:'<?php echo $CI->router->class; ?>'});
        $(".datepicker").datepicker({dateFormat : display_date_format});
        load_head();

        $(document).on('change','.datepicker',function() 
        {
            $("#outlet_id").val("");
            $("#dealer_id").val("");
            $('#dealer_id_container').hide();
            $("#visit_head_container").html("");
        });

        $(document).on('change','#outlet_id',function()
        {
            $('#dealer_id_container').hide();
            $("#visit_head_container").html("");
            $("#dealer_id").val("");
            var outlet_id=$('#outlet_id').val();
            if(outlet_id>0)
            {
                $.ajax(
                    {
                        url: '<?php echo site_url('common_controller/get_dropdown_dealers_by_outlet_id'); ?>',
                        type: 'POST',
                        datatype: "JSON",
                        data:
                        {
                            html_container_id:'#dealer_id',
                            outlet_id:outlet_id
                        },
                        success: function (data, status)
                        {
                            $('#dealer_id_container').show();
                        },
                        error: function (xhr, desc, err)
                        {
                            console.log("error");
                        }
                    });
            }
        });
        $(document).on('change','#dealer_id',function()
        {
            $("#visit_head_container").html("");
            load_head();
        });
    });
    function load_head()
    {
        var outlet_id=$('#outlet_id').val();
        var dealer_id=$('#dealer_id').val();
        var date=$('#date').val();
        if(outlet_id>0 && dealer_id>0)
        {
            $.ajax(
            {
                url: '<?php echo site_url($CI->controller_url.'/visit_head'); ?>',
                type: 'POST',
                datatype: "JSON",
                data:
                {
                    outlet_id:outlet_id,dealer_id:dealer_id,date:date
                },
                success: function (data, status)
                {

                },
                error: function (xhr, desc, err)
                {
                    console.log("error");
                }
            });
        }
    }
</script>
