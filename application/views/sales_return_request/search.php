<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$CI=& get_instance();
$action_buttons=array();
$action_buttons[]=array(
    'label'=>$CI->lang->line("ACTION_BACK"),
    'href'=>site_url($CI->controller_url)
);
$CI->load->view('action_buttons',array('action_buttons'=>$action_buttons));
?>
<div class="row widget">
    <div class="widget-header">
        <div class="title">
            <?php echo $title; ?>
        </div>
        <div class="clearfix"></div>
    </div>
    <div id="container_farmer_search">
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_OUTLET_NAME');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <?php
                if(sizeof($CI->user_outlets)==1)
                {
                    ?>
                    <input type="hidden" id="outlet_id" value="<?php echo $CI->user_outlets[0]['customer_id'] ?>" />
                    <label class="control-label"><?php echo $CI->user_outlets[0]['name'] ?></label>
                    <?php
                }
                else
                {
                    ?>
                    <select id="outlet_id" class="form-control">
                        <?php
                        foreach($CI->user_outlets as $row)
                        {?>
                            <option value="<?php echo $row['customer_id']?>"><?php echo $row['name'];?></option>
                        <?php
                        }
                        ?>
                    </select>
                    <?php
                }
                ?>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">
                    <?php echo $CI->lang->line('LABEL_MOBILE_NO');?> |<br>
                    Scan Dealer Card |<br>
                    Scan Old Invoice
                </label>
            </div>
            <div class="col-sm-4 col-xs-4">
                <input type="text" id="code" class="form-control" value=""/>
            </div>
            <div class="col-sm-4 col-xs-4">
                <div class="action_button">
                    <button id="button_action_farmer_search" type="button" class="btn"><?php echo $CI->lang->line('LABEL_SEARCH');?></button>
                </div>
            </div>
        </div>

    </div>
</div>
<script type="text/javascript">
    function search_farmer()
    {
        var outlet_id=$('#outlet_id').val();
        var code=$('#code').val();
        if((outlet_id>0)&& (code.length>0))
        {
            $.ajax({
                url:'<?php echo site_url($CI->controller_url.'/index/search_farmer') ?>',
                type: 'POST',
                datatype: "JSON",
                data:{outlet_id:outlet_id,code:code},
                success: function (data, status)
                {

                },
                error: function (xhr, desc, err)
                {
                    console.log("error");

                }
            });
        }
    }
    jQuery(document).ready(function()
    {
        $(document).off("click", "#button_action_farmer_search");
        $(document).on("click","#button_action_farmer_search",function()
        {
            search_farmer();

        });
        $(document).off("keypress", "#code");
        $(document).on("keypress","#code",function(event)
        {
            $('#container_farmer_new').hide();
            if(event.which == 13)
            {
                search_farmer();
            }


        });

    });
</script>