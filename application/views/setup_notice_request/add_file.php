<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$CI=& get_instance();
$action_buttons=array();
$action_buttons[]=array
(
    'label'=>$CI->lang->line("ACTION_BACK"),
    'href'=>site_url($CI->controller_url.'/index/'.strtolower('list_'.$this->file_type).'/'.$item['notice_id'])
);
if((isset($CI->permissions['action2']) && ($CI->permissions['action2']==1)))
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
<form id="save_form" action="<?php echo site_url($CI->controller_url.'/index/save_file');?>" method="post">
    <input type="hidden" id="id" name="id" value="<?php echo $item['id']?>" />
    <input type="hidden" id="notice_id" name="notice_id" value="<?php echo $item['notice_id']?>" />
    <input type="hidden" id="file_type" name="file_type" value="<?php echo $item['file_type']?>" />
    <input type="hidden" id="system_save_new_status" name="system_save_new_status" value="0" />
    <div class="row widget">
        <div class="widget-header">
            <div class="title">
                <?php echo $title; ?>
            </div>
            <div class="clearfix"></div>
        </div>
        <?php echo $CI->load->view("info_basic", '', true); ?>
        <?php
        if($CI->file_type==$this->config->item('system_file_type_image'))
        {
            ?>
            <div class="row show-grid">
                <div class="col-xs-4">
                    <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_FILE_IMAGE');?><span style="color:#FF0000">*</span></label>
                </div>
                <div class="col-sm-4 col-xs-8">
                    <input type="file" class="browse_button" data-preview-container="#file_name" name="file_name" style="text-align:right"/>
                    <small class="bg-info">File format jpg, jpeg, pnd, bmp, gif, pdf, ms(word, excel)</small>
                    <div id="file_name">
                        <a href="<?php echo $CI->config->item('system_base_url_picture') . $item['file_location']; ?>" target="_blank" class="external blob">
                            <img src="<?php echo $CI->config->item('system_base_url_picture') . $item['file_location']; ?>" style="height:200px" alt="<?php echo $item['file_name']?>"/>
                        </a>
                    </div>

                </div>
            </div>
        <?php
        }
        else if($this->file_type==$this->config->item('system_file_type_video'))
        {
            ?>
            <div class="row show-grid">
                <div class="col-xs-4">
                    <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_FILE_VIDEO');?><span style="color:#FF0000">*</span></label>
                </div>
                <div class="col-sm-4 col-xs-8">
                    <video controls id="" style="max-width:300px;height:200px">
                        <source src="<?php echo $CI->config->item('system_base_url_picture') . $item['file_location']; ?>" id="file_location"/>
                    </video>
                    <br/>
                    <small class="bg-info">Only wmv,mp4,mov,ftv,mkv,3gp,avi</small>
                    <div id="file_name">
                        <!--<a href="<?php /*echo $CI->config->item('system_base_url_picture') . $item['file_location']; */?>" target="_blank" class="external blob">
                            <img src="<?php /*echo $CI->config->item('system_base_url_picture') . $item['file_location']; */?>" style="height:200px" alt="<?php /*echo $item['file_name']*/?>"/>
                        </a>-->
                        <input type="file" class="browse_button file_type_video" name="file_name" accept="video/*">
                    </div>
                </div>
            </div>
        <?php
        }
        else
        {

        }
        ?>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_REMARKS');?> </label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <textarea name="item[remarks]" id="remarks" class="form-control" ><?php echo $item['remarks'];?></textarea>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_LINK_URL');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <input type="text" name="item[link_url]" id="link_url" class="form-control " value="<?php echo $item['link_url'];?>" />
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
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_STATUS');?><span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <select name="item[status]" id="status" class="form-control">
                    <option value=""><?php echo $CI->lang->line('SELECT');?></option>
                    <option value="<?php echo $CI->config->item('system_status_active');?>" <?php if($CI->config->item('system_status_active')==$item['status']){ echo "selected";}?>><?php echo $CI->config->item('system_status_active');?></option>
                    <option value="<?php echo $CI->config->item('system_status_inactive');?>" <?php if($CI->config->item('system_status_inactive')==$item['status']){ echo "selected";}?>><?php echo $CI->config->item('system_status_inactive');?></option>
                </select>
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
    });
</script>

