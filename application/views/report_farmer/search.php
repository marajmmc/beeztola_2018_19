<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$CI = & get_instance();
$action_buttons=array();
$action_buttons[]=array(
    'label'=>$CI->lang->line("ACTION_REFRESH"),
    'href'=>site_url($CI->controller_url.'/index')
);
$CI->load->view('action_buttons',array('action_buttons'=>$action_buttons));

?>
<form class="form_valid" id="save_form" action="<?php echo site_url($CI->controller_url.'/index/list');?>" method="post">
    <div class="row widget">
        <div class="widget-header">
            <div class="title">
                <?php echo $title; ?>
            </div>
            <div class="clearfix"></div>
        </div>
        <div class="row show-grid">
            <div class="row show-grid">
                <div class="col-xs-4">
                    <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_OUTLET');?><span style="color:#FF0000">*</span></label>
                </div>
                <div class="col-sm-4 col-xs-8">
                    <?php if(count($assigned_outlet)>1){?>
                        <select name="report[outlet_id]" class="form-control">
                            <option value=""><?php echo $this->lang->line('SELECT');?></option>
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
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Farmer Type</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <select id="farmer_type" name="report[farmer_type]" class="form-control">
                    <option value=""><?php echo $this->lang->line('SELECT');?></option>
                    <?php
                    foreach($farmer_types as $row)
                    {?>
                        <option value="<?php echo $row['id']?>"><?php echo $row['name'];?></option>
                    <?php
                    }
                    ?>
                </select>
            </div>
        </div>
        <?php if(isset($CI->permissions['action1']) && ($CI->permissions['action1']==1))
        {
            ?>
            <div class="row show-grid">
                <span class="text-center"><p><label class="control-label">OR</label></p></span>
                <div class="col-xs-4">
                    <label class="control-label pull-right">
                        <?php echo $CI->lang->line('LABEL_MOBILE_NO');?>
                    </label>
                </div>
                <div class="col-sm-4 col-xs-8">
                    <input type="text" name="report[mobile_no]" class="form-control" value="" />
                </div>
            </div>
        <?php
        }
        ?>
        <div class="row show-grid">
            <div class="col-xs-4">

            </div>
            <div class="col-xs-4">
                <div class="action_button pull-right">
                    <button id="button_action_report" type="button" class="btn" data-form="#save_form"><?php echo $CI->lang->line("ACTION_REPORT_VIEW"); ?></button>
                </div>

            </div>
            <div class="col-xs-4">

            </div>
        </div>
    </div>
    <div class="clearfix"></div>
</form>
<div id="system_report_container">
</div>

