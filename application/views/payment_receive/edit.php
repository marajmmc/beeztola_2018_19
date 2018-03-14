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
}
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
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DATE_PAYMENT');?>:</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <?php echo System_helper::display_date($item['date_payment']);?>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DATE_SALE');?>:</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <?php echo System_helper::display_date($item['date_sale']);?>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DATE_RECEIVE');?><span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <input type="text" name="item[date_receive]" class="form-control datepicker" value="<?php echo System_helper::display_date($item['date_receive']);?>" readonly/>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_OUTLET');?>:</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <?php echo $item['outlet'];?>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_TYPE_PAYMENT');?>:</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <?php echo $item['type_payment'];?>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_REFERENCE_NO');?><span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <input type="text" name="item[reference_no]" class="form-control" value="<?php echo $item['reference_no'];?>"/>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_BANK_NAME');?>:</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <?php echo $item['bank_name_source'];?>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_BRANCH_NAME');?>:</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <?php echo $item['bank_branch_source'];?>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Payment Entry Time:</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <?php echo System_helper::display_date_time($item['date_updated']);?>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Payment Entry By:</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <?php echo $item['payment_by'];?>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Payment Forward Entry Time:</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <?php echo System_helper::display_date_time($item['date_updated_forward']);?>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Payment Forwarded By:</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <?php echo $item['payment_forwarded_by'];?>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_AMOUNT_PAYMENT');?>:</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <?php echo number_format($item['amount_payment'],2);?>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_AMOUNT_RECEIVE');?><span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <input type="text" name="item[amount_receive]" class="form-control text-right float_type_positive" value="<?php echo $item['amount_receive'];?>"/>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_AMOUNT_BANK_CHARGE');?><span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <input type="text" name="item[amount_bank_charge]" class="form-control text-right float_type_positive" value="<?php echo $item['amount_bank_charge'];?>"/>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Payment Receive Bank<span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <select name="item[bank_account_id_destination]" class="form-control">
                    <option value=""><?php echo $this->lang->line('SELECT');?></option>
                    <?php
                    foreach($bank_accounts as $bank)
                    {?>
                        <option value="<?php echo $bank['value']?>" <?php if($bank['value']==$item['bank_account_id_destination']){ echo "selected";}?>><?php echo $bank['text'];?></option>
                    <?php
                    }
                    ?>
                </select>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Attachment:</label>
            </div>
            <div class="col-xs-4" id="image_payment">
                <img style="max-width: 250px;" src="<?php echo $CI->config->item('system_base_url_profile_picture').$item['image_location']; ?>" alt="<?php echo $item['image_name']; ?>">
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_REMARKS');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <textarea name="item[remarks_receive]" class="form-control"><?php echo $item['remarks_receive'] ?></textarea>
            </div>
        </div>

    </div>
    <div class="clearfix"></div>
</form>

<script type="text/javascript">
    jQuery(document).ready(function()
    {
        system_preset({controller:'<?php echo $CI->router->class; ?>'});
        $(".datepicker").datepicker({dateFormat : display_date_format});
    });
</script>
