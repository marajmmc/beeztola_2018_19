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
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_NAME');?><span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <input type="text" name="item[name]" id="name" class="form-control " value="<?php echo $item['name'];?>" />
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DISCOUNT_SELF_PERCENTAGE');?><span style="color:#FF0000">*</span><br><small>For Sales 1</small></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <input type="text" name="item[discount_self_percentage]" id="discount_self_percentage" class="form-control float_type_positive " value="<?php echo $item['discount_self_percentage'];?>"/>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Unused <?php echo $CI->lang->line('LABEL_DISCOUNT_REFERRAL_PERCENTAGE');?><span style="color:#FF0000">*</span><br><small>Unused</small></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <input type="text" name="item[discount_referral_percentage]" id="discount_referral_percentage" class="form-control float_type_positive " value="<?php echo $item['discount_referral_percentage'];?>"/>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_COMMISSION_DISTRIBUTOR');?><span style="color:#FF0000">*</span><br><small>For Sales 1</small></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <input type="text" name="item[commission_distributor]" id="commission_distributor" class="form-control float_type_positive " value="<?php echo $item['commission_distributor'];?>"/>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_ALLOW_OFFER');?><span style="color:#FF0000">*</span><br><small>For Sales 3</small></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <select name="item[allow_offer]" id="allow_offer" class="form-control">
                    <option value="<?php echo $CI->config->item('system_status_yes');?>" <?php if($CI->config->item('system_status_yes')==$item['allow_offer']){ echo "selected";}?>><?php echo $CI->config->item('system_status_yes');?></option>
                    <option value="<?php echo $CI->config->item('system_status_no');?>" <?php if($CI->config->item('system_status_no')==$item['allow_offer']){ echo "selected";}?>><?php echo $CI->config->item('system_status_no');?></option>
                </select>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_ALLOW_DISCOUNT');?><span style="color:#FF0000">*</span><br><small>For Sales 3</small></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <select name="item[allow_discount]" id="allow_discount" class="form-control">
                    <option value="<?php echo $CI->config->item('system_status_yes');?>" <?php if($CI->config->item('system_status_yes')==$item['allow_discount']){ echo "selected";}?>><?php echo $CI->config->item('system_status_yes');?></option>
                    <option value="<?php echo $CI->config->item('system_status_no');?>" <?php if($CI->config->item('system_status_no')==$item['allow_discount']){ echo "selected";}?>><?php echo $CI->config->item('system_status_no');?></option>
                </select>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_ORDER');?><span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <input type="text" name="item[ordering]" id="ordering" class="form-control float_type_positive " value="<?php echo $item['ordering'];?>"/>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_REMARKS');?> </label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <textarea name="item[remarks]" id="remarks" class="form-control" ><?php echo $item['remarks'];?></textarea>
            </div>
        </div>
    </div>
    <div class="clearfix"></div>
</form>
