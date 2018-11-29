<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$CI=& get_instance();
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <h4 class="panel-title">
            <label class=""><a class="external text-danger" data-toggle="collapse" data-target="#collapse3" href="#">+ Acres Information ( Number of crop {<?php echo sizeof($acres)?>})</a></label>
        </h4>
    </div>
    <div id="collapse3" class="panel-collapse  <?php if($acres){ echo 'collapse';}?>">
        <?php
        if(!$acres)
        {
            ?>
            <div class="alert alert-danger text-center"><strong>Acres not setup</strong></div>
        <?php
        }
        else
        {
            ?>
            <table class="table table-bordered table-responsive system_table_details_view">
                <thead>
                <tr>
                    <th><label class="control-label"><?php echo $CI->lang->line('LABEL_CROP_NAME');?></label></th>
                    <th><label class="control-label"><?php echo $CI->lang->line('LABEL_CROP_TYPE_NAME');?></label></th>
                    <th class="text-right"><label class="control-label">Acres</label></th>
                    <th class="text-right"><label class="control-label">Seeds per Acre(kg)</label></th>
                    <th class="text-right"><label class="control-label">Total Seeds(kg)</label></th>
                </tr>
                </thead>
                <tbody>
                <?php
                $quantity_crop_total=0;
                $quantity_crop_kg_acre_total=0;
                foreach($acres as $crop_id=>$crop)
                {
                    $quantity_crop_sub_total=0;
                    $quantity_crop_kg_acre_sub_total=0;
                    foreach($crop as $crop_type)
                    {
                        $quantity_crop_sub_total+=$crop_type['quantity'];
                        $quantity_crop_kg_acre_sub_total+=($crop_type['quantity']*$crop_type['quantity_kg_acre']);

                        $quantity_crop_total+=$crop_type['quantity'];
                        $quantity_crop_kg_acre_total+=($crop_type['quantity']*$crop_type['quantity_kg_acre']);
                        ?>
                        <tr>
                            <td><?php echo $crop_type['crop_name'];?></td>
                            <td><?php echo $crop_type['crop_type_name'];?></td>
                            <td class="text-right"><?php echo System_helper::get_string_kg($crop_type['quantity']);?></td>
                            <td class="text-right"><?php echo System_helper::get_string_kg($crop_type['quantity_kg_acre']);?></td>
                            <td class="text-right"><?php echo System_helper::get_string_kg($crop_type['quantity']*$crop_type['quantity_kg_acre']);?></td>
                        </tr>
                    <?php
                    }
                    ?>
                    <tr>
                        <td colspan="2" class="text-right bg-success"><strong>Crop (<?php echo $crop_type['crop_name'];?>) Total: </strong></td>
                        <td class="text-right bg-success"><strong><?php echo System_helper::get_string_kg($quantity_crop_sub_total);?></strong></td>
                        <td class="text-right bg-success"><strong>&nbsp;</strong></td>
                        <td class="text-right bg-success"><strong><?php echo System_helper::get_string_kg($quantity_crop_kg_acre_sub_total);?></strong></td>
                    </tr>
                <?php
                }
                ?>
                </tbody>
                <tfoot>
                <tr>
                    <th colspan="2" class="text-right bg-warning">Grand <?php echo $CI->lang->line('LABEL_TOTAL');?></th>
                    <th class="text-right bg-warning"><strong><?php echo System_helper::get_string_kg($quantity_crop_total);?></strong></th>
                    <th class="text-right bg-warning"><strong>&nbsp;</strong></th>
                    <th class="text-right bg-warning"><strong><?php echo System_helper::get_string_kg($quantity_crop_kg_acre_total);?></strong></th>
                </tr>
                </tfoot>
            </table>
        <?php
        }
        ?>
    </div>
</div>