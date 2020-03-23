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
                                {?>
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
                            <input type="text" id="date_start" name="report[date_start]" class="form-control date_large" value="<?php echo date('01-M-Y',time()); ?>">
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
                            <select name="report[outlet_id]" class="form-control pull-right">
                                <?php
                                foreach($assigned_outlet as $outlet)
                                {?>
                                    <option value="<?php echo $outlet['customer_id']?>"><?php echo $outlet['name'];?></option>
                                <?php
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-xs-6">
                            <label class="control-label"><?php echo $CI->lang->line('LABEL_OUTLET');?><span style="color:#FF0000">*</span></label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row show-grid">
                <div class="col-xs-4">

                </div>
                <div class="col-xs-4">
                    <div class="action_button pull-right">
                        <button id="button_action_report" type="button" class="btn" data-form="#save_form"><?php echo $CI->lang->line("ACTION_REPORT"); ?></button>
                    </div>

                </div>
                <div class="col-xs-4">

                </div>
            </div>
    </form>
</div>
<div class="clearfix"></div>


<div id="system_report_container">

</div>
<script type="text/javascript">

    jQuery(document).ready(function()
    {
        $(".date_large").datepicker({dateFormat : display_date_format,changeMonth: true,changeYear: true,yearRange: "2015:c+2"});
        $(document).off("change", "#division_id");
        $(document).on('change','#division_id',function()
        {
            $('#zone_id').val('');
            $('#territory_id').val('');
            var division_id=$('#division_id').val();
            $('#zone_id_container').hide();
            $('#territory_id_container').hide();
            $("#system_report_container").html('');
            if(division_id>0)
            {
                if(system_zones[division_id]!==undefined)
                {
                    $('#zone_id_container').show();
                    $('#zone_id').html(get_dropdown_with_select(system_zones[division_id]));
                }
            }

        });
        $(document).off("change", "#zone_id");
        $(document).on('change','#zone_id',function()
        {
            $('#territory_id').val('');
            var zone_id=$('#zone_id').val();
            $('#territory_id_container').hide();
            $("#system_report_container").html('');
            if(zone_id>0)
            {
                if(system_territories[zone_id]!==undefined)
                {
                    $('#territory_id_container').show();
                    $('#territory_id').html(get_dropdown_with_select(system_territories[zone_id]));
                }
            }
        });
        $(document).off("change", "#fiscal_year_id");
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
        $(document).off("change", "#report_name");
        $(document).on("change","#report_name",function()
        {
            $("#system_report_container").html("");
            var report_name=$('#report_name').val();
            if((report_name=='variety_amount_quantity')||(report_name=='variety_amount_quantity_sortable')||(report_name=='outlets_dealers_varieties'))
            {
                $('#container_product').show();
            }
            else
            {
                $('#container_product').hide();
            }

        });
    });
</script>
