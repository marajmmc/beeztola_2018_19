<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$CI=& get_instance();
$action_buttons=array();
$action_buttons[]=array
(
    'label'=>$CI->lang->line("ACTION_BACK"),
    'href'=>site_url($CI->controller_url));
if((isset($CI->permissions['action7']) && ($CI->permissions['action7']==1)))
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
    <div class="col-md-12">
        <table class="table table-bordered table-responsive system_table_details_view">
            <thead>
            <tr>
                <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $this->lang->line('LABEL_ID');?></label></th>
                <th class=""><label class="control-label"><?php echo Barcode_helper::get_barcode_payment($item['payment_id']);?></label></th>
                <th colspan="2">&nbsp;</th>
            </tr>
            <tr>
                <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $this->lang->line('LABEL_OUTLET');?></label></th>
                <th class="bg-danger"><label class="control-label"><?php echo $item['outlet'];?></label></th>
                <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $this->lang->line('LABEL_DATE_PAYMENT');?></label></th>
                <th class="bg-danger"><label class="control-label"><?php echo System_helper::display_date($item['date_payment']);?></label></th>
            </tr>
            <tr>
                <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $this->lang->line('LABEL_PAYMENT_WAY');?></label></th>
                <th class="header_value"><label class="control-label"><?php echo $item['payment_way'];?></label></th>
                <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $this->lang->line('LABEL_DATE_SALE');?></label></th>
                <th class="bg-danger"><label class="control-label"><?php echo System_helper::display_date($item['date_sale']);?></label></th>
            </tr>
            <tr>
                <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $this->lang->line('LABEL_REFERENCE_NO');?></label></th>
                <th class=" header_value"><label class="control-label"><?php echo $item['reference_no'];?></label></th>
                <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $this->lang->line('LABEL_DATE_RECEIVE');?></label></th>
                <th class="bg-danger"><label class="control-label"><?php echo System_helper::display_date($item['date_receive']);?></label></th>

            </tr>
            <tr>
                <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $this->lang->line('LABEL_BANK_ACCOUNT_NUMBER_DESTINATION');?></label></th>
                <th class=" header_value"><label class="control-label"><?php echo $item['account_number'].' ('.$item['bank_destination'].' -'.$item['branch_name'].')';?></label></th>
                <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $this->lang->line('LABEL_AMOUNT_PAYMENT');?></label></th>
                <th class="bg-danger"><label class="control-label"><?php echo number_format($item['amount_payment'],2);?></label></th>
            </tr>
            <tr>
                <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $this->lang->line('LABEL_BANK_PAYMENT_SOURCE');?></label></th>
                <th class=" header_value"><label class="control-label"><?php echo $item['bank_name'];?></label></th>
                <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $this->lang->line('LABEL_AMOUNT_BANK_CHARGE');?></label></th>
                <th class="bg-danger"><label class="control-label"><?php echo number_format($item['amount_bank_charge'],2);?></label></th>

            </tr>
            <tr>
                <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $this->lang->line('LABEL_BANK_PAYMENT_BRANCH_SOURCE');?></label></th>
                <th class=" header_value"><label class="control-label"><?php echo $item['bank_branch_source'];?></label></th>
                <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $this->lang->line('LABEL_AMOUNT_RECEIVE');?></label></th>
                <th class=" bg-danger"><label class="control-label"><?php echo number_format($item['amount_receive'],2);?></label></th>
            </tr>
            <tr>
                <th class="widget-header header_caption"><label class="control-label pull-right">Edit Payment Request By</label></th>
                <th class=" header_value"><label class="control-label"><?php echo $item['edit_payment_request_by'];?></label></th>
                <th class="widget-header header_caption"><label class="control-label pull-right">Edit Payment Request Time</label></th>
                <th class="header_value"><label class="control-label"><?php echo System_helper::display_date_time($item['date_updated']);?></label></th>
            </tr>
            <tr>
                <th class="widget-header header_caption"><label class="control-label pull-right">Edit Payment Request Forwarded By</label></th>
                <th class="header_value"><label class="control-label"><?php echo $item['edit_payment_request_forward_by'];?></label></th>
                <th class="widget-header header_caption"><label class="control-label pull-right">Edit Payment Request Forward Time</label></th>
                <th class=" header_value"><label class="control-label"><?php echo System_helper::display_date_time($item['date_updated_forward']);?></label></th>
            </tr>
            <tr>
                <th class="widget-header header_caption"><label class="control-label pull-right">Attachment (Document)</label></th>
                <th colspan="3" class=" header_value"><img style="max-width: 250px;" src="<?php echo $CI->config->item('system_base_url_payment_attachment').$item['image_location']; ?>" alt="<?php echo $item['image_name']; ?>"></th>

            </tr>
            <?php if($item['remarks_payment']){?>
                <tr>
                    <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $this->lang->line('LABEL_REMARKS').' (Payment)';?></label></th>
                    <th colspan="3" class=" header_value"><label class="control-label"><?php echo nl2br($item['remarks_payment']);?></label></th>

                </tr>
            <?php }?>
            <?php if($item['remarks_receive']){?>
                <tr>
                    <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $this->lang->line('LABEL_REMARKS').' (Receive)';?></label></th>
                    <th colspan="3" class=" header_value"><label class="control-label"><?php echo nl2br($item['remarks_receive']);?></label></th>
                </tr>
            <?php } ?>
            </thead>
        </table>
    </div>
</div>
<form id="save_form" action="<?php echo site_url($CI->controller_url.'/index/save_approve');?>" method="post">
    <input type="hidden" id="id" name="id" value="<?php echo $item['id']; ?>" />
    <div class="row widget">
        <div class="widget-header">
            <div class="title">
                <?php echo $title; ?>
            </div>
            <div class="clearfix"></div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Edit Payment Request Approve<span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <select class="form-control" name="item[status_approve]">
                    <option value=""><?php echo $this->lang->line('SELECT');?></option>
                    <option value="<?php echo $this->config->item('system_status_approved')?>">Approve</option>
                    <option value="<?php echo $this->config->item('system_status_rejected')?>">Reject</option>
                </select>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>

    <div class="clearfix"></div>
</form>

