<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$CI=& get_instance();
$action_buttons=array();
$action_buttons[]=array(
    'label'=>$CI->lang->line("ACTION_BACK").' to Pending List',
    'href'=>site_url($CI->controller_url)
);
$action_buttons[]=array(
    'label'=>$CI->lang->line("ACTION_BACK").' to All list',
    'href'=>site_url($CI->controller_url.'/index/list_all')
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
<div class="row widget">
    <div class="widget-header">
        <div class="title">
            <?php echo $title; ?>
        </div>
        <div class="clearfix"></div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4 class="panel-title">
                <label class=""><a class="external text-danger" data-toggle="collapse" data-target="#collapse3" href="#">+ Basic Information</a></label>
            </h4>
        </div>
        <div id="collapse3" class="panel-collapse collapse">
            <table class="table table-bordered table-responsive system_table_details_view">
                <thead>
                <tr>
                    <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_OUTLET');?></label></th>
                    <th class=""><label class="control-label"><?php echo $outlets[0]['name'] ?></label></th>
                    <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DATE');?></label></th>
                    <th class=" header_value"><label class="control-label"><?php echo System_helper::display_date($item['date']);?></label></th>
                </tr>
                <tr>
                    <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DEALER');?></label></th>
                    <th class=" header_value"><label class="control-label"><?php echo $item['dealer_name'];?></label></th>
                    <th colspan="2">&nbsp;</th>
                </tr>
                <tr>
                    <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_REMARKS');?></label></th>
                    <th class=" header_value" colspan="3"><label class="control-label"><?php echo nl2br($item['remarks']);?></label></th>
                </tr>
                <tr>
                    <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_CREATED_BY');?></label></th>
                    <th class=" header_value"><label class="control-label"><?php echo $users[$item['user_created']]['name'];?></label></th>
                    <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DATE_CREATED_TIME');?></label></th>
                    <th class=""><label class="control-label"><?php echo System_helper::display_date_time($item['date_created']);?></label></th>
                </tr>
                <?php
                if($item['user_updated'])
                {
                    ?>
                    <tr>
                        <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_UPDATED_BY');?></label></th>
                        <th class=" header_value"><label class="control-label"><?php echo $users[$item['user_updated']]['name'];?></label></th>
                        <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DATE_UPDATED_TIME');?> </label></th>
                        <th class=""><label class="control-label"><?php echo System_helper::display_date_time($item['date_updated']);?></label></th>
                    </tr>
                <?php
                }
                ?>
                <tr>
                    <th class="widget-header header_caption"><label class="control-label pull-right">ZSC Comment Status</label></th>
                    <th class="warning header_value"><label class="control-label"><?php echo $item['status_zsc_comment'];?></label></th>
                    <th class="widget-header header_caption"><label class="control-label pull-right">Number of Edit</label></th>
                    <th class="warning"><label class="control-label"><?php echo $item['revision_count'];?></label></th>
                </tr>
                <tr>
                    <th class="widget-header header_caption"><label class="control-label pull-right">ZSC Comment</label></th>
                    <th class=" header_value" colspan="3"><label class="control-label"><?php echo nl2br($item['zsc_comment']);?></label></th>
                </tr>
                <?php
                if($item['user_update_zsc_comment'])
                {
                    ?>
                    <tr>
                        <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_UPDATED_BY');?> (ZSC Comment)</label></th>
                        <th class=" header_value"><label class="control-label"><?php echo $users[$item['user_update_zsc_comment']]['name'];?></label></th>
                        <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DATE_UPDATED_TIME');?>  (ZSC Comment)</label></th>
                        <th class=""><label class="control-label"><?php echo System_helper::display_date_time($item['date_update_zsc_comment']);?></label></th>
                    </tr>
                <?php
                }
                ?>
                <?php
                if($item['user_updated_admin'])
                {
                    ?>
                    <tr>
                        <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_UPDATED_BY');?> (Admin)</label></th>
                        <th class=" header_value"><label class="control-label"><?php echo $users[$item['user_updated_admin']]['name'];?></label></th>
                        <th class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DATE_UPDATED_TIME');?>  (Admin)</label></th>
                        <th class=""><label class="control-label"><?php echo System_helper::display_date_time($item['date_updated_admin']);?></label></th>
                    </tr>
                    <tr>
                        <th class="widget-header header_caption"><label class="control-label pull-right">Number of Edit (Admin)</label></th>
                        <th class="warning"><label class="control-label"><?php echo $item['revision_count_admin'];?></label></th>
                        <th colspan="2">&nbsp;</th>
                    </tr>

                <?php
                }
                ?>
                </thead>
            </table>
        </div>
    </div>
    <hr/>
    <div class="row show-grid">
        <table class="table table-bordered table-responsive">
            <thead>
            <tr>
                <th style="width: 5px;">SL#</th>
                <th style="width: 300px;">Visit Head</th>
                <th>Previous Visit (<?php echo System_helper::display_date($item_previous['date'])?>)</th>
                <th><?php echo $CI->lang->line('LABEL_REMARKS');?></th>
            </tr>
            </thead>
            <tbody>
            <?php
            $field_visit_data_previous=array();
            if($item_previous['field_visit_data'])
            {
                $field_visit_data_previous=json_decode($item_previous['field_visit_data'],true);
            }
            $field_visit_data=array();
            if($item['field_visit_data'])
            {
                $field_visit_data=json_decode($item['field_visit_data'],true);
            }

            $serial=0;
            foreach($heads as $head)
            {
                ++$serial;
                ?>
                <tr>
                    <td><?php echo $serial?></td>
                    <td><?php echo $head['name'];?></td>
                    <td><?php echo isset($field_visit_data_previous[$head['id']])?$field_visit_data_previous[$head['id']]:'';?></td>
                    <td><?php echo isset($field_visit_data[$head['id']])?$field_visit_data[$head['id']]:'';?></td>
                </tr>
            <?php
            }
            ?>
            </tbody>
        </table>
    </div>
</div>

