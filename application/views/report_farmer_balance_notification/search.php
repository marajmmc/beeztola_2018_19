<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$CI = & get_instance();

?>
<div class="row widget">
    <div class="widget-header">
        <div class="title">
            <?php echo $title; ?>
        </div>
        <div class="clearfix"></div>
    </div>
    <form class="form_valid" id="save_form" action="<?php echo site_url($CI->controller_url.'/index/list');?>" method="post">
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_OUTLET');?><span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <?php if(count($assigned_outlet)>1){?>
                    <select name="report[outlet_id]" class="form-control">
                        <?php
                        foreach($assigned_outlet as $outlet)
                        {?>
                            <option value="<?php echo $outlet['customer_id']?>"><?php echo $outlet['name'];?></option>
                        <?php
                        }
                        ?>
                    </select>
                <?php }
                else{?>
                    <?php
                    {?>
                        <label class="control-label"><?php echo $assigned_outlet[0]['name'];?></label>
                        <input type="hidden" name="report[outlet_id]" value="<?php echo $assigned_outlet[0]['customer_id']?>">
                    <?php } ?>
                <?php } ?>
            </div>
        </div>
        <div style="<?php if(!(isset($CI->permissions['action3']) && ($CI->permissions['action3']==1))){echo 'display:none';}?>">
            <div style="" class="row show-grid">
                <div class="col-xs-4">
                    <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DAY_COLOR_PAYMENT_START');?><span style="color:#FF0000">*</span></label>
                </div>
                <div class="col-sm-4 col-xs-8">
                    <select class="form-control" name="report[day_color_payment_start]">
                        <option value=""><?php echo $this->lang->line('SELECT');?></option>
                        <?php
                        for($i=10;$i<=90;$i=$i+10)
                        {
                            ?>
                            <option value="<?php echo $i;?>" <?php if($i==20){echo 'selected';} ?>><?php echo $i;?></option>
                        <?php
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div style="" class="row show-grid">
                <div class="col-xs-4">
                    <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DAY_COLOR_PAYMENT_INTERVAL');?><span style="color:#FF0000">*</span></label>
                </div>
                <div class="col-sm-4 col-xs-8">
                    <select class="form-control" name="report[day_color_payment_interval]">
                        <?php
                        for($i=10;$i<=30;$i=$i+10)
                        {
                            ?>
                            <option value="<?php echo $i;?>" <?php if($i==10){echo 'selected';} ?>><?php echo $i;?></option>
                        <?php
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div style="" class="row show-grid">
                <div class="col-xs-4">
                    <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DAY_COLOR_SALES_START');?><span style="color:#FF0000">*</span></label>
                </div>
                <div class="col-sm-4 col-xs-8">
                    <select class="form-control" name="report[day_color_sales_start]">
                        <option value=""><?php echo $this->lang->line('SELECT');?></option>
                        <?php
                        for($i=10;$i<=90;$i=$i+10)
                        {
                            ?>
                            <option value="<?php echo $i;?>" <?php if($i==20){echo 'selected';} ?>><?php echo $i;?></option>
                        <?php
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div style="" class="row show-grid">
                <div class="col-xs-4">
                    <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DAY_COLOR_SALES_INTERVAL');?><span style="color:#FF0000">*</span></label>
                </div>
                <div class="col-sm-4 col-xs-8">
                    <select class="form-control" name="report[day_color_sales_interval]">
                        <?php
                        for($i=10;$i<=30;$i=$i+10)
                        {
                            ?>
                            <option value="<?php echo $i;?>" <?php if($i==10){echo 'selected';} ?>><?php echo $i;?></option>
                        <?php
                        }
                        ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="row show-grid">
            <div class="col-xs-4">
                &nbsp;
            </div>
            <div class="col-xs-4">
                <div class="action_button pull-right">
                    <button id="button_action_report" type="button" class="btn" data-form="#save_form"><?php echo $CI->lang->line("ACTION_REPORT"); ?></button>
                </div>
            </div>
            <div class="col-xs-4">
                &nbsp;
            </div>
        </div>
    </form>
</div>
<div class="clearfix"></div>

<div id="system_report_container">
</div>

<script type="text/javascript">
    jQuery(document).ready(function()
    {
        system_preset({controller:'<?php echo $CI->router->class; ?>'});
        system_off_events();
        <?php if(count($assigned_outlet)==1)
        {
        ?>
            $('#save_form').submit();
        <?php
        }
         ?>


    });
</script>
