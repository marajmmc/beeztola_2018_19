<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$CI=& get_instance();
$user=User_helper::get_user();
$action_buttons=array();
$action_buttons[]=array
(
    'label'=>$CI->lang->line("ACTION_BACK"),
    'href'=>site_url($CI->controller_url.'/index/list_payment/'.$item['farmer_id'])
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
    <input type="hidden" id="farmer_id" name="farmer_id" value="<?php echo $item['farmer_id']?>" />
    <input type="hidden" id="system_save_new_status" name="system_save_new_status" value="0" />
    <input type="hidden" id="system_form_token" name="system_form_token" value="<?php echo time().'_'.$user->user_id; ?>" />
    <div class="row widget">
        <?php
        echo $CI->load->view("info_basic", $info_basic, true);
        ?>
        <div class="row">
            <div class="col-xs-6">
                <button type="button" class="btn btn-success btn-md" style="font-weight: bold;color: #000000;background-color: lightyellow">Credit Limit: <?php echo System_helper::get_string_amount($amount_credit_limit);?></button>
                <button type="button" class="btn btn-warning btn-md" style="font-weight: bold;color: #000000;background-color: lightblue">Balance: <?php echo System_helper::get_string_amount($amount_credit_balance);?></button>
                <button type="button" class="btn btn-danger btn-md" style="font-weight: bold;color: #000000;background-color: lightgreen">Total Payment: <?php echo System_helper::get_string_amount($amount_total);?></button>
                <button type="button" class="btn btn-primary btn-md" style="font-weight: bold;color: #000000;background-color: lightcoral">Total Due: <?php echo System_helper::get_string_amount($amount_credit_limit-$amount_credit_balance);?></button>
            </div>
        </div>
        <div class="widget-header">
            <div class="title">
                <?php echo $title; ?>
            </div>
            <div class="clearfix"></div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_OUTLET_NAME');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <?php
                if(sizeof($farmer_outlets)==1)
                {
                    ?>
                    <input type="hidden" name="outlet_id" value="<?php echo $farmer_outlets[0]['outlet_id'] ?>" />
                    <label class="control-label"><?php echo $farmer_outlets[0]['outlet_name'] ?></label>
                <?php
                }
                else
                {
                    ?>
                    <select id="outlet_id" class="form-control" name="outlet_id">
                        <?php
                        foreach($farmer_outlets as $row)
                        {?>
                            <option value="<?php echo $row['outlet_id']?>"><?php echo $row['outlet_name'];?></option>
                        <?php
                        }
                        ?>
                    </select>
                <?php
                }
                ?>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DATE_PAYMENT');?><span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <?php
                if(!(isset($this->permissions['action2']) && ($this->permissions['action2']==1)))
                {
                    ?>
                    <label class="control-label"><?php echo System_helper::display_date($item['date_payment']);?></label>
                    <input type="hidden" name="item[date_payment]" value="<?php echo System_helper::display_date($item['date_payment']);?>"/>
                    <?php
                }
                else
                {
                    ?>
                    <input type="text" name="item[date_payment]" id="date_payment" class="form-control datepicker" value="<?php echo System_helper::display_date($item['date_payment']);?>" readonly />
                <?php
                }
                ?>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_PAYMENT_WAY');?><span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <select name="item[payment_way_id]" class="form-control">
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
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_AMOUNT');?><span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <input type="text" name="item[amount]" id="amount" class="form-control float_type_positive " value="<?php echo $item['amount'];?>" />
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_REFERENCE_NO');?>  </label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <input type="text" name="item[reference_no]" id="reference_no" class="form-control " value="<?php echo $item['reference_no'];?>" />
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Attachment(Document)<span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-xs-4">
                <input type="file" class="browse_button" data-resize-width="800" data-resize-height="600" data-resize-size="1372022" data-preview-container="#image_payment" data-preview-width="300" name="image_payment">
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
            </div>
            <div class="col-xs-4" id="image_payment">
                <img style="max-width: 250px;" src="<?php echo $CI->config->item('system_base_url_picture').$item['image_location']; ?>" alt="<?php echo $item['image_name']; ?>">
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
<script type="text/javascript">
    $(document).ready(function ()
    {
        system_off_events();
        system_preset({controller:'<?php echo $CI->router->class; ?>'});
        $(":file").filestyle({input: false,buttonText: "<?php echo $CI->lang->line('UPLOAD');?>", buttonName: "btn-danger"});
        $(".datepicker").datepicker({dateFormat : display_date_format});
    });
</script>

