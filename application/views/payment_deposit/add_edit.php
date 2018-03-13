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
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DATE_PAYMENT');?><span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <input type="text" name="item[date_payment]" class="form-control datepicker" value="<?php echo System_helper::display_date($item['date_payment']);?>" readonly/>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DATE_SALE');?><span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <input type="text" name="item[date_sale]" class="form-control datepicker" value="<?php echo System_helper::display_date($item['date_sale']);?>" readonly/>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_OUTLET');?><span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <select name="item[outlet_id]" class="form-control">
                    <option value=""><?php echo $this->lang->line('SELECT');?></option>
                    <?php
                    foreach($assigned_outlet as $outlet)
                    {?>
                        <option value="<?php echo $outlet['outlet_id']?>" <?php if($outlet['outlet_id']==$item['outlet_id']){ echo "selected";}?>><?php echo $outlet['outlet_name'];?></option>
                    <?php
                    }
                    ?>
                </select>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_TYPE_PAYMENT');?><span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <select name="item[type_payment]" class="form-control">
                    <option value=""><?php echo $this->lang->line('SELECT');?></option>
                    <option value="<?php echo $CI->config->item('system_payment_way_cash');?>" <?php if(isset($item['type_payment'])){if($item['type_payment']==$CI->config->item('system_payment_way_cash')){echo "selected";}}?>><?php echo $CI->config->item('system_payment_way_cash');?></option>
                    <option value="<?php echo $CI->config->item('system_payment_way_pay_order');?>" <?php if(isset($item['type_payment'])){if($item['type_payment']==$CI->config->item('system_payment_way_pay_order')){echo "selected";}}?>><?php echo $CI->config->item('system_payment_way_pay_order');?></option>
                    <option value="<?php echo $CI->config->item('system_payment_way_cheque');?>" <?php if(isset($item['type_payment'])){if($item['type_payment']==$CI->config->item('system_payment_way_cheque')){echo "selected";}}?>><?php echo $CI->config->item('system_payment_way_cheque');?></option>
                    <option value="<?php echo $CI->config->item('system_payment_way_tt');?>" <?php if(isset($item['type_payment'])){if($item['type_payment']==$CI->config->item('system_payment_way_tt')){echo "selected";}}?>><?php echo $CI->config->item('system_payment_way_tt');?></option>
                    <option value="<?php echo $CI->config->item('system_payment_way_dd');?>" <?php if(isset($item['type_payment'])){if($item['type_payment']==$CI->config->item('system_payment_way_dd')){echo "selected";}}?>><?php echo $CI->config->item('system_payment_way_dd');?></option>
                    <option value="<?php echo $CI->config->item('system_payment_way_online_payment');?>" <?php if(isset($item['type_payment'])){if($item['type_payment']==$CI->config->item('system_payment_way_online_payment')){echo "selected";}}?>><?php echo $CI->config->item('system_payment_way_online_payment');?></option>
                </select>
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
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_AMOUNT_PAYMENT');?><span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <input type="text" name="item[amount_payment]" class="form-control text-right float_type_positive" value="<?php echo $item['amount_payment'];?>"/>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_BANK_NAME');?><span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <select name="item[bank_id_source]" class="form-control">
                    <option value=""><?php echo $this->lang->line('SELECT');?></option>
                    <?php
                    foreach($bank_source as $bank)
                    {?>
                        <option value="<?php echo $bank['bank_id_source']?>" <?php if($bank['bank_id_source']==$item['bank_id_source']){ echo "selected";}?>><?php echo $bank['bank_name_source'];?></option>
                    <?php
                    }
                    ?>
                </select>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_BRANCH_NAME');?><span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <input type="text" name="item[bank_branch_source]" class="form-control" value="<?php echo $item['bank_branch_source'];?>"/>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_REMARKS');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <textarea name="item[remarks_payment]" class="form-control"><?php echo $item['remarks_payment'] ?></textarea>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Attachment</label>
            </div>
            <div class="col-xs-4">
                <input type="file" class="browse_button" data-preview-container="#image_payment" name="image_payment">
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
            </div>
            <div class="col-xs-4" id="image_payment">
                <img style="max-width: 250px;" src="<?php echo $CI->config->item('system_base_url_profile_picture').$item['image_location']; ?>" alt="<?php echo $item['image_name']; ?>">
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
        $(":file").filestyle({input: false,buttonText: "<?php echo $CI->lang->line('UPLOAD');?>", buttonName: "btn-danger"});
    });
</script>