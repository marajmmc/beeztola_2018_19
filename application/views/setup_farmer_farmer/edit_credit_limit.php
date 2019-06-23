<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$CI=& get_instance();
$action_buttons=array();
$action_buttons[]=array(
    'label'=>$CI->lang->line("ACTION_BACK"),
    'href'=>site_url($CI->controller_url)
);
$action_buttons[]=array(
    'type'=>'button',
    'label'=>$CI->lang->line("ACTION_SAVE"),
    'id'=>'button_action_save',
    'data-form'=>'#save_form'
);
$action_buttons[]=array(
    'type'=>'button',
    'label'=>$CI->lang->line("ACTION_CLEAR"),
    'id'=>'button_action_clear',
    'data-form'=>'#save_form'
);
$CI->load->view('action_buttons',array('action_buttons'=>$action_buttons));
?>
<form id="save_form" action="<?php echo site_url($CI->controller_url.'/index/save_credit_limit');?>" method="post">
    <input type="hidden" id="id" name="id" value="<?php echo $item['id']; ?>" />
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
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_FARMER_TYPE_NAME');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $item['farmer_type_name'];?></label>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DISCOUNT_SELF_PERCENTAGE');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $item['discount_self_percentage'];?></label>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_MOBILE_NO');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $item['mobile_no'];?></label>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_NID');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $item['nid'];?></label>
            </div>
        </div>

        <div style="" class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DIVISION_NAME');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $item['division_name'];?></label>
            </div>
        </div>

        <div style="" class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_ZONE_NAME');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $item['zone_name'];?></label>
            </div>
        </div>
        <div style="" class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_TERRITORY_NAME');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $item['territory_name'];?></label>
            </div>
        </div>
        <div style="" class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DISTRICT_NAME');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $item['district_name'];?></label>
            </div>
        </div>
        <div style="" class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_UPAZILLA_NAME');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $item['upazilla_name'];?></label>
            </div>
        </div>
        <div style="" class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_UNION_NAME');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $item['union_name'];?></label>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_ADDRESS');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $item['address'];?></label>
            </div>
        </div>

        <div style="" class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Assigned Outlet(s)</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <?php
                if(sizeof($assigned_outlets)>0)
                {
                    foreach($assigned_outlets as $outlet)
                    {
                        ?>
                        <label class="control-label"><?php echo $outlet['text']; ?></label><br>
                    <?php
                    }
                }
                else
                {
                    ?>
                    <label class="control-label">No Outlet Assigned Yet.</label>
                <?php
                }
                ?>

            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_NAME');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $item['name'];?></label>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_AMOUNT_CREDIT_BALANCE');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo System_helper::get_string_amount($item['amount_credit_balance']);?></label>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Current <?php echo $CI->lang->line('LABEL_AMOUNT_CREDIT_LIMIT');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo System_helper::get_string_amount($item['amount_credit_limit']);?></label>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">New <?php echo $CI->lang->line('LABEL_AMOUNT_CREDIT_LIMIT');?><span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <input type="text" name="item[amount_credit_limit]" id="amount_credit_limit" class="form-control float_type_positive" value=""/>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_REMARKS');?> </label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <textarea name="item[remarks_credit_limit]" id="remarks" class="form-control" ><?php echo $item['remarks_credit_limit'];?></textarea>
            </div>
        </div>
    </div>

    <div class="clearfix"></div>
</form>
<script type="text/javascript">

    jQuery(document).ready(function()
    {
        system_off_events();
    });
</script>