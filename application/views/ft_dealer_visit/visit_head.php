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
            $serial=0;
            $field_visit_data=array();
            if($item['field_visit_data'])
            {
                $field_visit_data=json_decode($item['field_visit_data'],true);
            }
            foreach($heads as $head)
            {
                if($head['status']==$CI->config->item('system_status_active'))
                {
                    ++$serial;
                    ?>
                    <tr>
                        <td><?php echo $serial?></td>
                        <td><?php echo $head['name'];?></td>
                        <td><textarea name="heads[<?php echo $head['id'];?>]" id="" class="form-control" ><?php echo isset($field_visit_data[$head['id']])?$field_visit_data[$head['id']]:'';?></textarea></td>
                    </tr>
                <?php
                }
            }
            ?>
            </tbody>
        </table>
    </div>
</div>

