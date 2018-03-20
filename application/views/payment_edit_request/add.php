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
    <input type="hidden" id="id" name="id" value="<?php echo $id?>" />
    <input type="hidden" id="id" name="payment_id" value="<?php echo $payment_id?>" />
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
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DATE_RECEIVE');?><span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <input type="text" name="item[date_receive]" class="form-control datepicker" value="<?php echo System_helper::display_date($item['date_receive']);?>" readonly/>
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
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_PAYMENT_WAY');?><span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <select name="item[payment_way_id]" class="form-control">
                    <option value=""><?php echo $this->lang->line('SELECT');?></option>
                    <?php
                    foreach($payment_way as $way)
                    {?>
                        <option value="<?php echo $way['value']?>" <?php if($way['value']==$item['payment_way_id']){ echo "selected";}?>><?php echo $way['text'];?></option>
                    <?php
                    }
                    ?>
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
                <input type="text" id="amount_payment" name="item[amount_payment]" class="form-control text-right float_type_positive" value="<?php echo $item['amount_payment'];?>"/>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_AMOUNT_BANK_CHARGE');?><span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <input type="text" id="amount_bank_charge" name="item[amount_bank_charge]" class="form-control text-right float_type_positive" value="<?php echo $item['amount_bank_charge'];?>"/>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_AMOUNT_RECEIVE');?>:</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label id="amount_receive"><?php echo number_format(($item['amount_payment']-$item['amount_bank_charge']),2);?></label>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_BANK_PAYMENT_SOURCE');?><span style="color:#FF0000">*</span></label>
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
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_BANK_PAYMENT_BRANCH_SOURCE');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <input type="text" name="item[bank_branch_source]" class="form-control" value="<?php echo $item['bank_branch_source'];?>"/>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_BANK_ACCOUNT_NUMBER').' (Receive)';?><span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <select name="item[bank_account_id_destination]" class="form-control">
                    <option value=""><?php echo $this->lang->line('SELECT');?></option>
                    <?php
                    foreach($bank_account_number_destination as $account_destination)
                    {?>
                        <option value="<?php echo $account_destination['value']?>" <?php if($account_destination['value']==$item['bank_account_id_destination']){ echo "selected";}?>><?php echo $account_destination['text'];?></option>
                    <?php
                    }
                    ?>
                </select>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Attachment(Document)</label>
            </div>
            <div class="col-xs-4">
                <input type="file" class="browse_button" data-preview-container="#image_payment" data-preview-width="300" name="image_payment">
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
            </div>
            <div class="col-xs-4" id="image_payment">
                <img style="max-width: 250px;" src="<?php echo $CI->config->item('system_base_url_payment_attachment').$item['image_location']; ?>" alt="<?php echo $item['image_name']; ?>">
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_REMARKS').'(Payment)';?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <textarea name="item[remarks_payment]" class="form-control"><?php echo $item['remarks_payment'] ?></textarea>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_REMARKS').'(Receive)';?></label>
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
        $(document).off('input','#amount_bank_charge');
        $(document).on('input', '#amount_bank_charge', function()
        {
            var amount_payment=$('#amount_payment').val();
            var amount_bank_charge=$('#amount_bank_charge').val();
            var amount_receive=number_format((amount_payment-amount_bank_charge),2);
            $('#amount_receive').html(amount_receive);
        });
        $(":file").filestyle({input: false,buttonText: "<?php echo $CI->lang->line('UPLOAD');?>", buttonName: "btn-danger"});
    });
</script>
