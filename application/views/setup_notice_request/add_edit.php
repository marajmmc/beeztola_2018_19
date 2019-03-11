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
    $action_buttons[]=array
    (
        'type'=>'button',
        'label'=>$CI->lang->line("ACTION_SAVE_NEW"),
        'id'=>'button_action_save_new',
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
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_NOTICE_TYPE');?><span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <select name="item[type_id]" id="name" class="form-control ">
                    <option value=""><?php echo $CI->lang->line('SELECT')?></option>
                    <?php
                    foreach($notice_types as $type)
                    {
                        ?>
                        <option value="<?php echo $type['id']?>" <?php if($type['id']==$item['type_id']){echo "selected='selected'";}?>><?php echo $type['name']?></option>
                    <?php
                    }
                    ?>
                </select>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DATE_PUBLISH');?><span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <div class='input-group date' id='datetimepicker1'>
                    <input type="text" name="item[date_publish]" id="date_publish" class="form-control datepicker" value="<?php echo System_helper::display_date($item['date_publish']);?>" readonly />
                    <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
                </div>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_EXPIRE_DAY');?><span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <input type="text" name="item[expire_day]" id="expire_day" class="form-control integer_type_positive" value="<?php echo $item['expire_day'];?>" />
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_TITLE');?><span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <input type="text" name="item[title]" id="name" class="form-control " value="<?php echo $item['title'];?>" />
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DESCRIPTION');?> </label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <textarea name="item[description]" id="remarks" class="form-control" ><?php echo $item['description'];?></textarea>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_ORDER');?><span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <input type="text" name="item[ordering]" id="ordering" class="form-control integer_type_positive " value="<?php echo $item['ordering'];?>"/>
            </div>
        </div>
        <!--<div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php /*echo $CI->lang->line('LABEL_STATUS');*/?><span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <select name="item[status]" id="status" class="form-control">
                    <option value=""><?php /*echo $CI->lang->line('SELECT');*/?></option>
                    <option value="<?php /*echo $CI->config->item('system_status_active');*/?>" <?php /*if($CI->config->item('system_status_active')==$item['status']){ echo "selected";}*/?>><?php /*echo $CI->config->item('system_status_active');*/?></option>
                    <option value="<?php /*echo $CI->config->item('system_status_inactive');*/?>" <?php /*if($CI->config->item('system_status_inactive')==$item['status']){ echo "selected";}*/?>><?php /*echo $CI->config->item('system_status_inactive');*/?></option>
                </select>
            </div>
        </div>-->

        <div class="row show-grid">
            <div class="col-xs-4">&nbsp;</div>
            <div class="col-xs-8">
                <table class="table table-responsive table-bordered">
                    <thead>
                    <tr>
                        <th style="width: 100px">
                            <input type="checkbox" class="allSelectCheckbox" id="allSelect">
                            <label for="allSelect" style="cursor: pointer">All Select</label>
                        </th>
                        <th>User Group</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach($user_groups as $user_group)
                    {
                        ?>
                        <tr>
                            <td>
                                <input type="checkbox" name="user_groups[]" id="user_group_ids_<?php echo $user_group['id']?>" value="<?php echo $user_group['id']?>" <?php if(in_array($user_group['id'],$user_group_ids)){echo "checked='true'";}?>/>
                            </td>
                            <td>
                                <label for="user_group_ids_<?php echo $user_group['id']?>" style="cursor: pointer"><?php echo $user_group['name']?></label>
                            </td>
                        </tr>
                    <?php
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
        <hr/>
        <div class="row show-grid">
            <div class="col-xs-4">

            </div>
            <div class="col-xs-8">
                <small class="btn-sm btn-info pull-left">Any Link (Url) Added Click to Add More <i class="fa fa-users"></i></small>
                <button type="button" class="btn btn-sm btn-warning system_button_add_more pull-right" data-current-id="<?php echo sizeof($urls);?>"> <strong>+ <?php echo $CI->lang->line('LABEL_ADD_MORE');?></strong></button>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">&nbsp;</div>
            <div class="col-xs-8">
                <table class="table table-responsive table-bordered">
                    <thead>
                    <tr>
                        <th style="width: 100px">&nbsp;</th>
                        <th>Link (Url)</th>
                        <th style="width: 100px">Action</th>
                    </tr>
                    </thead>
                    <tbody id="urls_container">
                    <?php
                    if(sizeof($urls)>0)
                    {
                        foreach($urls as $key=>$value)
                        {
                            ?>
                            <tr>
                                <td>
                                    <label class="control-label label_url_link">Link (Url): <?php echo $key+1?></label>
                                </td>
                                <td>
                                    <input type="text" id="url_links2" name="urls[]" data-current-id="2" class="form-control url_links" value="<?php echo $value?>">
                                </td>
                                <td>
                                    <button type="button" class="btn btn-danger system_button_add_delete">Delete</button>
                                </td>
                            </tr>
                        <?php
                        }
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="clearfix"></div>
</form>
<div id="system_content_add_more" style="display: none;">
    <table>
        <tbody>
        <tr>
            <td class="text-right">
                <label class="control-label label_url_link">Link</label>
            </td>
            <td class="text-right">
                <input type="text" class="form-control url_links" value="" />
            </td>
            <td>
                <button type="button" class="btn btn-danger system_button_add_delete"><?php echo $CI->lang->line('DELETE'); ?></button>
            </td>
        </tr>
        </tbody>
    </table>
</div>
<script type="text/javascript">
    $(document).ready(function ()
    {
        system_off_events();
        system_preset({controller:'<?php echo $CI->router->class; ?>'});
        $(".datepicker").datepicker({dateFormat : display_date_format});

        $(document).off("click", ".system_button_add_more");
        $(document).on("click", ".system_button_add_more", function(event)
        {
            var current_id=parseInt($(this).attr('data-current-id'));
            current_id=current_id+1;
            $(this).attr('data-current-id',current_id);
            var content_id='#system_content_add_more table tbody';

            $(content_id+' .label_url_link').html('Link (Url): '+current_id);

            $(content_id+' .url_links').attr('id','url_links'+current_id);
            $(content_id+' .url_links').attr('data-current-id',current_id);
            $(content_id+' .url_links').attr('name','urls[]');

            var html=$(content_id).html();
            $("#urls_container").append(html);

        });

        $(document).off('click','.system_button_add_delete');
        $(document).on("click", ".system_button_add_delete", function(event)
        {
            $(this).closest('tr').remove();
        });

        $(document).on("click",'.allSelectCheckbox',function()
        {
            if($(this).is(':checked'))
            {
                $('input:checkbox').prop('checked', true);
            }
            else
            {
                $('input:checkbox').prop('checked', false);
            }
        });
    });
</script>

