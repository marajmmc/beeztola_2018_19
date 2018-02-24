<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$CI=& get_instance();
$action_buttons=array();
$action_buttons[]=array(
    'label'=>$CI->lang->line("ACTION_BACK"),
    'href'=>site_url($CI->controller_url)
);
if(isset($CI->permissions['action2']) && ($CI->permissions['action2']==1))
{
    $action_buttons[]=array(
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
<form id="save_form" action="<?php echo site_url($CI->controller_url.'/index/save_outlet');?>" method="post">
    <input type="hidden" id="id" name="id" value="<?php echo $user_info['user_id']; ?>" />
    <input type="hidden" id="system_save_new_status" name="system_save_new_status" value="0" />
    <div class="row widget">
        <div class="widget-header">
            <div class="title">
                <?php echo $title; ?>
            </div>
            <div class="clearfix"></div>
        </div>
        <div style="overflow-x: auto;" class="row show-grid">
            <table class="table table-bordered" style="width: 600px;">
                <thead>
                <tr>
                    <th>
                        <label for="allSelectCheckbox">
                            <input type="checkbox" class="allSelectCheckbox" id="allSelectCheckbox" <?php if(sizeof($assigned_outlets)>0){echo 'checked="checked"';}?>>
                            Select All
                        </label>
                    </th>
                    <th><?php echo $CI->lang->line('LABEL_OUTLET_NAME');?></th>
                    <th class="text-right">Commission %</th>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach($outlets as $item)
                {
                    ?>
                    <tr>
                        <td><input type="checkbox" name="items[]" value="<?php echo $item['outlet_id']; ?>" <?php if(isset($assigned_outlets[$item['outlet_id']])){echo 'checked';} ?>></td>
                        <td>
                            <label title="<?php echo $item['outlet_name']; ?>"><?php echo $item['outlet_name']; ?></label>
                        </td>
                        <td>
                            <input type="text" name="commission[<?php echo $item['outlet_id']; ?>]" class="form-control float_type_positive" value="<?php if(isset($assigned_outlets[$item['outlet_id']])){echo $assigned_outlets[$item['outlet_id']]['commission'];}else{echo '0';} ?>"/>
                        </td>
                    </tr>
                <?php
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="clearfix"></div>
</form>
<script type="text/javascript">
    jQuery(document).ready(function()
    {
        system_preset({controller:'<?php echo $CI->router->class; ?>'});

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