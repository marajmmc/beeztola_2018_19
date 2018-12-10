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
                    if(isset($info['label_1']))
                    {
                        if(isset($info['value_1']))
                        {
                            if(isset($info['label_2']))
                            {
                                if(isset($info['value_2']))
                                {
                                    ?>
                                    <tr>
                                        <td class="widget-header header_caption"><label class="control-label pull-right"><?php echo $info['label_1'];?></label></td>
                                        <td class="warning header_value"><label class="control-label"><?php echo $info['value_1'];?></label></td>
                                        <td class="widget-header header_caption"><label class="control-label pull-right"><?php echo $info['label_2'];?></label></td>
                                        <td class="warning header_value"><label class="control-label"><?php echo $info['value_2'];?></label></td>
                                    </tr>
                                <?php
                                }
                                else
                                {
                                    ?>
                                    <tr>
                                        <td class="widget-header header_caption"><label class="control-label pull-right"><?php echo $info['label_1'];?></label></td>
                                        <td class="warning header_value"><label class="control-label"><?php echo $info['value_1'];?></label></td>
                                        <td class="widget-header header_caption" colspan="2"><label class="control-label"><?php echo $info['label_2'];?></label></td>
                                    </tr>
                                    <?php
                                }
                            }
                            else
                            {
                                ?>
                                <tr>
                                    <td class="widget-header header_caption"><label class="control-label pull-right"><?php echo $info['label_1'];?></label></td>
                                    <td class="warning header_value" colspan="3"><label class="control-label"><?php echo $info['value_1'];?></label></td>
                                </tr>
                            <?php
                            }
                        }
                        else
                        {
                            ?>
                            <tr><td colspan="4" class="bg-info"><?php echo $info['label_1'];?></td></tr>
                            <?php
                        }
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
</div>