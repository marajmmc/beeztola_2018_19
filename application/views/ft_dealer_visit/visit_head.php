<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$CI=& get_instance();
?>
<div class="row widget">
    <div class="widget-header">
        <div class="title">
            <?php echo $title; ?>
        </div>
        <div class="clearfix"></div>
    </div>
    <div class="row show-grid">
        <table class="table table-bordered table-responsive">
            <thead>
            <tr>
                <th style="width: 5px;">SL#</th>
                <th style="width: 300px;">Visit Head</th>
                <th>Previous Visit (<?php echo System_helper::display_date($item_previous['date'])?>)</th>
                <th>Discussion</th>
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
                    <td><textarea name="heads[<?php echo $head['id'];?>]" id="" class="form-control" ><?php echo isset($field_visit_data[$head['id']])?$field_visit_data[$head['id']]:'';?></textarea></td>
                </tr>
            <?php
            }
            ?>
            </tbody>
        </table>
    </div>
</div>

