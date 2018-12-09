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
    <div id="accordion_basic" class="panel-collapse collapse out">

        <table class="table table-bordered table-responsive system_table_details_view">
            <tbody>
                <?php
                foreach($info_basic as $info)
                {
                    ?>
                    <tr>
                        <td class="widget-header header_caption"><label class="control-label pull-right"><?php echo $info['label_prefix'].' Status';?></label></td>
                        <td class="warning header_value"><label class="control-label"><?php echo $info['status'];?></label></td>
                        <td class="widget-header header_caption"></td>
                        <td class="header_value"></td>
                    </tr>
                    <?php
                    if($info['by'])
                    {
                        ?>
                        <tr>
                            <td class="widget-header header_caption"><label class="control-label pull-right"><?php echo $info['label_prefix'].' By';?></label></td>
                            <td class="header_value"><label class="control-label"><?php echo $info['by'];?></label></td>
                            <td class="widget-header header_caption"><label class="control-label pull-right"><?php echo $info['label_prefix'].' Time';?></label></td>
                            <td class="header_value"><label class="control-label"><?php echo $info['time'];?></label></td>
                        </tr>
                        <?php
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
</div>