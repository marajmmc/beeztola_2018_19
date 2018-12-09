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
                    <td class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_STATUS_BUDGET_FORWARD');?></label></td>
                    <td class="warning header_value"><label class="control-label"><?php echo $budget_target['status_budget_forward'];?></label></td>
                    <td class="widget-header header_caption"></td>
                    <td class="warning header_value"></td>
                </tr>
                <?php
                if($budget_target['status_budget_forward']==$CI->config->item('system_status_forwarded'))
                {
                    ?>
                    <tr>
                        <td class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_USER_BUDGET_FORWARDED');?></label></td>
                        <td class="warning header_value"><label class="control-label"><?php echo $users[$budget_target['user_budget_forwarded']]['name'];?></label></td>
                        <td class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DATE_BUDGET_FORWARDED');?></label></td>
                        <td class="warning header_value"><label class="control-label"><?php echo System_helper::display_date_time($budget_target['date_budget_forwarded']);?></label></td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
    </div>
</div>