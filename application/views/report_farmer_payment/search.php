<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$CI = & get_instance();

?>
<div class="row widget">
    <div class="widget-header">
        <div class="title">
            <?php echo $title; ?>
        </div>
        <div class="clearfix"></div>
    </div>
    <form class="form_valid" id="save_form" action="<?php echo site_url($CI->controller_url.'/index/list');?>" method="post">

        <div class="row show-grid">
            <div class="col-xs-6">
                <div class="row show-grid">
                    <div class="col-xs-6">
                        <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_FISCAL_YEAR');?></label>
                    </div>
                    <div class="col-xs-6">
                        <select id="fiscal_year_id" name="report[fiscal_year_id]" class="form-control">
                            <option value=""><?php echo $this->lang->line('SELECT');?></option>
                            <?php
                            foreach($fiscal_years as $year)
                            {
                            ?>
                                <option value="<?php echo $year['value']?>"><?php echo $year['text'];?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="row show-grid">
                    <div class="col-xs-6">
                        <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_DATE_START');?></label>
                    </div>
                    <div class="col-xs-6">
                        <input type="text" id="date_start" name="report[date_start]" class="form-control date_large" value="<?php echo System_helper::display_date(time()); ?>">
                    </div>

                </div>
                <div class="row show-grid">
                    <div class="col-xs-6">
                        <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_DATE_END');?></label>
                    </div>
                    <div class="col-xs-6">
                        <input type="text" id="date_end" name="report[date_end]" class="form-control date_large" value="<?php echo System_helper::display_date(time()); ?>">
                    </div>

                </div>
            </div>

            <div class="col-xs-6">
                <!-- Location Section-->
                <div class="row show-grid">
                    <div class="col-xs-6">
                        <?php
                        if(sizeof($outlets) > 1)
                        {
                        ?>
                            <select id="outlet_id" name="report[outlet_id]" class="form-control">
                                <?php foreach($outlets as $outlet){ ?>
                                    <option value="<?php echo $outlet['customer_id']?>"><?php echo $outlet['name'];?></option>
                                <?php } ?>
                            </select>
                        <?php
                        }
                        else
                        {
                        ?>
                            <label class="control-label"><?php echo $outlets[0]['name'];?></label>
                            <input type="hidden" name="report[outlet_id]" value="<?php echo $outlets[0]['customer_id']?>">
                        <?php
                        }
                        ?>
                    </div>
                    <div class="col-xs-6">
                        <label class="control-label"><?php echo $CI->lang->line('LABEL_OUTLET');?></label>
                    </div>
                </div>

                <div style="display:none" class="row show-grid" id="farmer_id_container">
                    <div class="col-xs-6">
                        <select id="farmer_id" name="report[farmer_id]" class="form-control">
                            <option value=""><?php echo $this->lang->line('SELECT');?></option>
                        </select>
                    </div>
                    <div class="col-xs-6">
                        <label class="control-label"><?php echo $CI->lang->line('LABEL_DEALER_NAME');?></label>
                    </div>
                </div>
            </div>
        </div>

        <div class="row show-grid">
            <div class="col-xs-12">
                <div class="row show-grid">
                    <div class="col-xs-6">
                        &nbsp;
                    </div>
                    <div class="col-xs-6">
                        <div class="row show-grid">
                            <div class="col-xs-12">
                                <div class="action_button">
                                    <button id="button_action_report" type="button" class="btn" data-form="#save_form"><?php echo $CI->lang->line("ACTION_REPORT"); ?></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </form>
</div>
<div class="clearfix"></div>

<div id="system_report_container">

</div>

<script type="text/javascript">

    function get_dealer_by_outlet_dropdown(outlet_id = 0){
        if(outlet_id>0)
        {
            $.ajax({
                url: '<?php echo site_url($CI->controller_url.'/index/get_dealers');?>',
                type: 'POST',
                datatype: "JSON",
                data:{outlet_id:outlet_id},
                success: function (data, status)
                {
                    $('#farmer_id_container').show();
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
        system_preset({controller:'<?php echo $CI->router->class; ?>'});
        system_off_events();

        $(".date_large").datepicker({
            dateFormat : display_date_format,
            changeMonth: true,
            changeYear: true,
            yearRange: "c-2:c+2"
        });

        <?php
        $outlets_count = sizeof($outlets);
        if($outlets_count == 1){ ?>
            get_dealer_by_outlet_dropdown(<?php echo $outlets[0]['customer_id']?>);
        <?php } elseif ($outlets_count > 1) ?>
            get_dealer_by_outlet_dropdown($('#outlet_id').val());
        <?php ?>

        $(document).on("change","#fiscal_year_id",function()
        {
            var fiscal_year_ranges=$('#fiscal_year_id').val();
            if(fiscal_year_ranges!='')
            {
                var dates = fiscal_year_ranges.split("/");
                $("#date_start").val(dates[0]);
                $("#date_end").val(dates[1]);
            }
        });
        $(document).on("change","#outlet_id",function()
        {
            $("#farmer_id").val('');
            var outlet_id=$('#outlet_id').val();
            $('#farmer_id_container').hide();
            if(outlet_id > 0)
            {
                get_dealer_by_outlet_dropdown(outlet_id);
            }
            else
            {
                $('#farmer_id_container').hide();
            }
        });
    });
</script>
