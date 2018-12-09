<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$CI=& get_instance();
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <h4 class="panel-title">
            <label class=""><a class="external text-danger" data-toggle="collapse" data-target="#accordion_basic" href="#">+ Basic Information</a></label>
        </h4>
    </div>
    <div id="accordion_basic" class="panel-collapse  <?php if($acres){ echo 'collapse out';}else{ echo 'collapse in';}?>">

        <table class="table table-bordered table-responsive system_table_details_view">
            <tbody>
                <tr>
                    <td class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_USER_CREATED_AREA');?></label></td>
                    <td class="header_value"><label class="control-label"><?php echo $users[$budget_target['user_created_area']]['name'];?></label></td>
                    <td class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DATE_CREATED_AREA');?></label></td>
                    <td class="header_value"><label class="control-label"><?php echo System_helper::display_date_time($budget_target['date_created_area']);?></label></td>
                </tr>
                <tr>
                    <td class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_STATUS_BUDGET_FORWARD_AREA');?></label></td>
                    <td class="warning header_value"><label class="control-label"><?php echo $budget_target['status_budget_forward_area'];?></label></td>
                    <td class="widget-header header_caption"></td>
                    <td class="header_value"></td>
                </tr>
                <?php
                if($budget_target['status_budget_forward_area']==$CI->config->item('system_status_forwarded'))
                {
                    ?>
                    <tr>
                        <td class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_USER_BUDGET_FORWARDED_AREA');?></label></td>
                        <td class="header_value"><label class="control-label"><?php echo $users[$budget_target['user_budget_forwarded_area']]['name'];?></label></td>
                        <td class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DATE_BUDGET_FORWARDED_AREA');?></label></td>
                        <td class="header_value"><label class="control-label"><?php echo System_helper::display_date_time($budget_target['date_budget_forwarded_area']);?></label></td>
                    </tr>
                    <?php
                }
                ?>

                <tr>
                    <td class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_STATUS_TARGET_FORWARD_AREA_SUB');?></label></td>
                    <td class="warning header_value"><label class="control-label"><?php echo $budget_target['status_target_forward_area_sub'];?></label></td>
                    <td class="widget-header header_caption"></td>
                    <td class="warning header_value"></td>
                </tr>
                <?php
                if($budget_target['status_target_forward_area_sub']==$CI->config->item('system_status_forwarded'))
                {
                    ?>
                    <tr>
                        <td class="widget-header "><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_USER_TARGET_FORWARDED_AREA_SUB');?></label></td>
                        <td class="header_value"><label class="control-label"><?php echo $users[$budget_target['user_target_forwarded_area_sub']]['name'];?></label></td>
                        <td class="widget-header "><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DATE_TARGET_FORWARDED_AREA_SUB');?></label></td>
                        <td class="header_value"><label class="control-label"><?php echo System_helper::display_date_time($budget_target['date_target_forwarded_area_sub']);?></label></td>
                    </tr>
                    <?php
                }
                ?>
                <tr>
                    <td class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_STATUS_TARGET_FORWARD_AREA_SUPERIOR');?></label></td>
                    <td class="warning header_value"><label class="control-label"><?php echo $budget_target['status_target_forward_area_superior'];?></label></td>
                    <td class="widget-header header_caption"></td>
                    <td class="warning header_value"></td>
                </tr>
                <?php
                if($budget_target['status_target_forward_area_superior']==$CI->config->item('system_status_forwarded'))
                {
                    ?>
                    <tr>
                        <td class="widget-header "><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_USER_TARGET_FORWARDED_AREA_SUPERIOR');?></label></td>
                        <td class="header_value"><label class="control-label"><?php echo $users_area_superior[$budget_target['user_target_forwarded_area_superior']]['name'];?></label></td>
                        <td class="widget-header "><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DATE_TARGET_FORWARDED_AREA_SUPERIOR');?></label></td>
                        <td class="header_value"><label class="control-label"><?php echo System_helper::display_date_time($budget_target['date_target_forwarded_area_superior']);?></label></td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
    </div>
</div>