<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$CI=& get_instance();
$action_buttons=array();
$action_buttons[]=array
(
    'label'=>$CI->lang->line("ACTION_BACK"),
    'href'=>site_url($CI->controller_url)
);
if(isset($CI->permissions['action4']) && ($CI->permissions['action4']==1))
{
    $action_buttons[]=array(
        'type'=>'button',
        'label'=>$CI->lang->line("ACTION_PRINT"),
        'onClick'=>"window.print()"
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
                <th class=""><label class="control-label"><?php echo Barcode_helper::get_barcode_payment($item['id']);?></label></th>
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
                <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $this->lang->line('LABEL_AMOUNT_PAYMENT');?></label></th>
                <th class="bg-danger"><label class="control-label"><?php echo number_format($item['amount_payment'],2);?></label></th>
            </tr>
            <tr>
                <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $this->lang->line('LABEL_BANK_PAYMENT_SOURCE');?></label></th>
                <th class=" header_value"><label class="control-label"><?php echo $item['bank_name'];?></label></th>
                <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $this->lang->line('LABEL_BANK_PAYMENT_BRANCH_SOURCE');?></label></th>
                <th class=" header_value"><label class="control-label"><?php echo $item['bank_branch_source'];?></label></th>
            </tr>
            <tr>
                <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $this->lang->line('LABEL_BANK_ACCOUNT_NUMBER_DESTINATION');?></label></th>
                <th class=" header_value"><label class="control-label"><?php echo $item['account_number'].' ('.$item['bank_destination'].' -'.$item['branch_name'].')';?></label></th>
                <th colspan="2">&nbsp;</th>
            </tr>
            <tr>
                <th class="widget-header header_caption"><label class="control-label pull-right">Payment Entry By</label></th>
                <th class=" header_value"><label class="control-label"><?php echo $item['payment_by'];?></label></th>
                <th class="widget-header header_caption"><label class="control-label pull-right">Payment Entry Time</label></th>
                <th class="header_value"><label class="control-label"><?php echo System_helper::display_date_time($item['date_updated']);?></label></th>
            </tr>
            <tr>
                <th class="widget-header header_caption"><label class="control-label pull-right">Attachment(Document)</label></th>
                <th colspan="3" class=" header_value"><img style="max-width: 250px;" src="<?php echo $CI->config->item('system_base_url_payment_attachment').$item['image_location']; ?>" alt="<?php echo $item['image_name']; ?>"></th>
            </tr>
            <?php if($item['remarks_payment']){?>
                <tr>
                    <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $this->lang->line('LABEL_REMARKS');?></label></th>
                    <th colspan="3" class=" header_value"><label class="control-label"><?php echo nl2br($item['remarks_payment']);?></label></th>
                </tr>
            <?php } ?>
            </thead>
        </table>
    </div>
</div>
<div class="clearfix"></div>
