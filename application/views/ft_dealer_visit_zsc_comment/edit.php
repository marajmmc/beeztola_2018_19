<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$CI=& get_instance();
$action_buttons=array();
$action_buttons[]=array
(
    'label'=>$CI->lang->line("ACTION_BACK"),
    'href'=>site_url($CI->controller_url)
);
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
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_OUTLET');?><span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <input type="hidden" name="item[outlet_id]" id="outlet_id" value="<?php echo $outlets[0]['customer_id'] ?>" />
                <label class="control-label"><?php echo $outlets[0]['name'] ?></label>
            </div>
        </div>
        <div style="<?php if(!($item['dealer_id'])){echo 'display:none';} ?>" class="row show-grid" id="dealer_id_container">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DEALER');?><span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $item['dealer_name'];?></label>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_DATE');?><span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo System_helper::display_date($item['date']);?></label>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_REMARKS');?> </label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo nl2br($item['remarks']);?></label>
            </div>
        </div>
        <?php
        for($i=sizeof($item_previous)-1;$i>=0;$i--)
        {
            ?>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">
                        <label class=""><a class="external text-danger" data-toggle="collapse" data-target="#collapse_previous_visit_<?php echo $item_previous[$i]['id']?>" href="#">+ <?php echo System_helper::display_date($item_previous[$i]['date'])?></a></label>
                    </h4>
                </div>
                <div id="collapse_previous_visit_<?php echo $item_previous[$i]['id']?>" class="panel-collapse collapse ">
                    <div class="row show-grid">
                        <br/>
                        <table class="table table-responsive table-bordered">
                            <thead>
                            <tr>
                                <th style="width: 5px;">SL#</th>
                                <th style="width: 300px;">Visit Head</th>
                                <th><?php echo $CI->lang->line('LABEL_REMARKS');?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $serial=0;
                            $field_visits=json_decode($item_previous[$i]['field_visit_data'],true);
                            foreach($field_visits as $head_id=>$field_visit)
                            {
                                ++$serial;
                                ?>
                                <tr>
                                    <td><?php echo $serial;?></td>
                                    <td><?php echo $heads[$head_id]['name'];?></td>
                                    <td><?php echo nl2br($field_visit);?></td>
                                </tr>
                            <?php
                            }
                            ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php
        }
        ?>
        <div class="row show-grid">
            <table class="table table-bordered table-responsive">
                <thead>
                <tr>
                    <th style="width: 5px;">SL#</th>
                    <th style="width: 300px;">Visit Head</th>
                    <th><?php echo $CI->lang->line('LABEL_REMARKS');?></th>
                </tr>
                </thead>
                <tbody>
                <?php
                $field_visit_data=array();
                if($item['field_visit_data'])
                {
                    $field_visit_data=json_decode($item['field_visit_data'],true);
                }
                $serial=0;
                foreach($heads as $head)
                {
                    if($head['status']==$CI->config->item('system_status_active'))
                    {
                        ++$serial;
                        ?>
                        <tr>
                            <td><?php echo $serial?></td>
                            <td><?php echo $head['name'];?></td>
                            <td><?php echo isset($field_visit_data[$head['id']])?nl2br($field_visit_data[$head['id']]):'';?></td>
                        </tr>
                    <?php
                    }
                }
                ?>
                </tbody>
            </table>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">ZSC Comment</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <textarea name="item[zsc_comment]" id="zsc_comment" class="form-control" ><?php echo $item['zsc_comment'];?></textarea>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">

            </div>
            <div class="col-sm-4 col-xs-4">
                <div class="action_button pull-right">
                    <button id="button_action_save" type="button" class="btn" data-form="#save_form" data-message-confirm="Are You Sure?">Save</button>
                </div>
            </div>
            <div class="col-sm-4 col-xs-4">

            </div>
        </div>
    </div>
    <div class="clearfix"></div>
</form>

