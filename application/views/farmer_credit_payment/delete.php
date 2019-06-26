<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
$CI = & get_instance();

$action_buttons = array();
$action_buttons[] = array(
    'label' => $CI->lang->line("ACTION_BACK"),
    'href'=>site_url($CI->controller_url.'/index/list_payment/'.$item['farmer_id'])
);
$action_buttons[] = array(
    'type' => 'button',
    'label' => $CI->lang->line("ACTION_CLEAR"),
    'id' => 'button_action_clear',
    'data-form' => '#save_form'
);
$CI->load->view("action_buttons", array('action_buttons' => $action_buttons));

?>
<div class="row widget" style="margin-top:0">
    <div class="widget-header">
        <div class="title">
            <?php echo $title; ?>
        </div>
        <div class="clearfix"></div>
    </div>
    <?php
    echo $CI->load->view("info_basic", $info_basic, true);
    $info_payment['accordion']['collapse'] = 'in';
    $info_payment['accordion']['header']='+ Payment Information';
    $info_payment['accordion']['div_id']='info_payment';
    echo $CI->load->view("info_basic", $info_payment, true);
    ?>

    <form class="form_valid" id="save_form" action="<?php echo site_url($CI->controller_url . '/index/save_delete'); ?>" method="post">
        <input type="hidden" id="farmer_id" name="farmer_id" value="<?php echo $item['farmer_id']; ?>"/>
        <input type="hidden" id="id" name="id" value="<?php echo $item['id']; ?>"/>

        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DELETE'); ?>
                    <span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-xs-4">
                <select name="item[status]" class="form-control">
                    <option value=""><?php echo $CI->lang->line('SELECT'); ?></option>
                    <option value="<?php echo $CI->config->item('system_status_delete'); ?>"><?php echo $CI->config->item('system_status_delete'); ?></option>
                </select>
            </div>
        </div>

        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_REASON_DELETE'); ?>  <span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-xs-4">
                <textarea name="item[remarks_delete]" class="form-control"></textarea>
            </div>
        </div>

        <div class="row show-grid">
            <div class="col-xs-4">
                &nbsp;
            </div>
            <div class="col-xs-4">
                <div class="action_button pull-right">
                    <button id="button_action_save" type="button" class="btn" data-form="#save_form" data-message-confirm="Are you sure delete payment?">Save</button>
                </div>
            </div>
        </div>
    </form>
</div>

<script type="text/javascript">
    jQuery(document).ready(function ($) {
        system_off_events(); // Triggers
        system_preset({controller: '<?php echo $CI->router->class; ?>'});
    });
</script>
