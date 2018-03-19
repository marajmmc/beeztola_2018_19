<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$CI=& get_instance();
?>
<form id="sale_form" class="external" action="<?php echo site_url($CI->controller_url.'/index/save');?>" method="post">
    <input type="hidden" name="item[outlet_id]" id="outlet_id" value="<?php echo $item['outlet_id']; ?>" />
    <input type="hidden" name="item[farmer_id]" value="<?php echo $item['farmer_id']; ?>" />
    <input type="hidden" name="item[discount_self_percentage]" id="discount_self_percentage" value="<?php echo $item['discount_self_percentage'];?>">
    <div class="row widget">
        <div class="widget-header">
            <div class="title">
                <?php echo $title; ?>
            </div>
            <div class="clearfix"></div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_NAME');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $item['farmer_name'];?></label>
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
        <?php
        if(strlen($item['nid'])>0)
        {
            ?>
            <div class="row show-grid">
                <div class="col-xs-4">
                    <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_NID');?></label>
                </div>
                <div class="col-sm-4 col-xs-8">
                    <label class="control-label"><?php echo $item['nid'];?></label>
                </div>
            </div>
        <?php
        }
        ?>
        <?php
        if(strlen($item['address'])>0)
        {
            ?>
            <div class="row show-grid">
                <div class="col-xs-4">
                    <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_ADDRESS');?></label>
                </div>
                <div class="col-sm-4 col-xs-8">
                    <label class="control-label"><?php echo $item['address'];?></label>
                </div>
            </div>
        <?php
        }
        ?>
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
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DISCOUNT');?></label>
            </div>
            <div class="col-xs-4">
                <label class="control-label" id="discount"><?php echo $item['discount_self_percentage'];?></label>%<br>
                <?php
                if(strlen($item['discount_message'])>0)
                {
                    echo $item['discount_message'];
                }
                ?>
            </div>
        </div>
    </div>
</form>
