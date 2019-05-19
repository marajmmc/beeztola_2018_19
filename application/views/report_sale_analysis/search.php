<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$CI = & get_instance();
$action_buttons=array();
$action_buttons[]=array(
    'label'=>$CI->lang->line("ACTION_REFRESH"),
    'href'=>site_url($CI->controller_url.'/index')
);
$CI->load->view('action_buttons',array('action_buttons'=>$action_buttons));
$time=time();
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
                            <label class="control-label pull-right">Report Type<span style="color:#FF0000">*</span></label>
                        </div>
                        <div class="col-xs-6">
                            <select id="report_name" name="report[report_name]" class="form-control">
                                <option value="variety_amount_quantity">Product wise Sales</option>
                                <option value="outlets_amount">Sales Amount</option>
                                <!--<option value="area_amount">Area wise Sales Amount</option>-->
                            </select>
                        </div>
                    </div>
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
                                    <option value="<?php echo $year['value']?>" <?php if(($time>=$year['date_start'])&&($time<=$year['date_end'])){echo "selected='selected'";}?>><?php echo $year['text'];?></option>
                                <?php
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="row show-grid">
                        <div class="col-xs-6">
                            <label class="control-label pull-right">Number of Previous Year <span style="color:#FF0000">*</span></label>
                        </div>
                        <div class="col-xs-6">
                            <select id="fiscal_year_number" name="report[fiscal_year_number]" class="form-control">
                                <?php
                                for($i=1;$i<4;$i++)
                                {
                                    ?>
                                    <option value="<?php echo $i;?>" <?php if($i==2){echo "selected='selected'";}?>><?php echo $i;?></option>
                                <?php
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="row show-grid">
                        <div class="col-xs-6">
                            <label class="control-label pull-right">Month<span style="color:#FF0000">*</span></label>
                        </div>
                        <div class="col-xs-6">
                            <select id="month" name="report[month]" class="form-control">
                                <option value="">Select</option>
                                <?php
                                for($i=1;$i<13;$i++)
                                {
                                    ?>
                                    <option value="<?php echo $i;?>"><?php echo date("F", mktime(0, 0, 0,  $i,1, 2000));?></option>
                                <?php
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="row show-grid" style="display: none;" id="container_num_of_months">
                        <div class="col-xs-6">
                            <label class="control-label pull-right">Number of Months <span style="color:#FF0000">*</span></label>
                        </div>
                        <div class="col-xs-6">
                            <select id="num_of_months" name="report[num_of_months]" class="form-control">
                                <?php
                                for($i=1;$i<13;$i++)
                                {
                                    ?>
                                    <option value="<?php echo $i;?>"><?php echo $i;?></option>
                                <?php
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div id="container_product" style="display: none;">
                        <div style="" class="row show-grid" id="crop_id_container">
                            <div class="col-xs-6">
                                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_CROP_NAME');?></label>
                            </div>
                            <div class="col-xs-6">
                                <select id="crop_id" name="report[crop_id]" class="form-control">
                                    <option value=""><?php echo $this->lang->line('SELECT');?></option>
                                </select>
                            </div>
                        </div>
                        <div style="display: none;" class="row show-grid" id="crop_type_id_container">
                            <div class="col-xs-6">
                                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_CROP_TYPE_NAME');?></label>
                            </div>
                            <div class="col-xs-6">
                                <select id="crop_type_id" name="report[crop_type_id]" class="form-control">
                                    <option value=""><?php echo $this->lang->line('SELECT');?></option>
                                </select>
                            </div>
                        </div>
                        <div style="display: none;" class="row show-grid" id="variety_id_container">
                            <div class="col-xs-6">
                                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_VARIETY_NAME');?></label>
                            </div>
                            <div class="col-xs-6">
                                <select id="variety_id" name="report[variety_id]" class="form-control">
                                    <option value=""><?php echo $this->lang->line('SELECT');?></option>
                                </select>
                            </div>
                        </div>
                        <div class="row show-grid" id="pack_size_id_container">
                            <div class="col-xs-6">
                                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_PACK_SIZE');?></label>
                            </div>
                            <div class="col-xs-6">
                                <select id="pack_size_id" name="report[pack_size_id]" class="form-control">
                                    <option value=""><?php echo $CI->lang->line('SELECT');?></option>
                                    <?php
                                    foreach($pack_sizes as $pack_size)
                                    {?>
                                        <option value="<?php echo $pack_size['value']?>"><?php echo $pack_size['text'];?></option>
                                    <?php
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xs-6">
                    <!-- Assign Outlet-->
                    <div class="row show-grid" id="outlet_id_container">
                        <div class="col-xs-6">
                            <?php
                            if(sizeof($assigned_outlet)==1)
                            {
                                ?>
                                <label class="control-label pull-right"><?php echo $assigned_outlet[0]['name'] ?> : </label>
                                <input type="hidden" name="report[outlet_id]" id="outlet_id" value="<?php echo $assigned_outlet[0]['customer_id'] ?>" />
                            <?php
                            }
                            else
                            {
                                ?>
                                <select name="report[outlet_id]" id="outlet_id" class="form-control">
                                    <option value=""><?php echo $CI->lang->line('SELECT');?></option>
                                    <?php
                                    foreach($assigned_outlet as $row)
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
                        <div class="col-xs-6">
                            <label class="control-label"> <?php echo $CI->lang->line('LABEL_OUTLET_NAME');?><span style="color:#FF0000">*</span></label>
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
        $("#crop_id").html(get_dropdown_with_select(system_crops));
        $(document).off("change", "#crop_id");
        $(document).on("change","#crop_id",function()
        {
            $('#system_report_container').html('');
            $('#crop_type_id').val("");
            $('#variety_id').val("");

            var crop_id=$('#crop_id').val();
            $('#crop_type_id_container').hide();
            $('#variety_id_container').hide();
            if(crop_id>0)
            {
                $('#crop_type_id_container').show();
                if(system_types[crop_id]!==undefined)
                {
                    $('#crop_type_id').html(get_dropdown_with_select(system_types[crop_id]));
                }
            }
        });
        $(document).off("change", "#crop_type_id");
        $(document).on("change","#crop_type_id",function()
        {
            $('#system_report_container').html('');
            $('#variety_id').val("");
            var crop_type_id=$('#crop_type_id').val();
            $('#variety_id_container').hide();
            if(crop_type_id>0)
            {
                $('#variety_id_container').show();
                if(system_varieties[crop_type_id]!==undefined)
                {
                    $('#variety_id').html(get_dropdown_with_select(system_varieties[crop_type_id]));
                }
            }
        });
        $(document).off("change", "#variety_id");
        $(document).off("change", "#division_id");
        $(document).on('change','#division_id',function()
        {
            $('#zone_id').val('');
            $('#territory_id').val('');
            $('#district_id').val('');
            $('#outlet_id').val('');
            var division_id=$('#division_id').val();
            $('#zone_id_container').hide();
            $('#territory_id_container').hide();
            $('#district_id_container').hide();
            $('#outlet_id_container').hide();
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
            $('#district_id').val('');
            $('#outlet_id').val('');
            var zone_id=$('#zone_id').val();
            $('#territory_id_container').hide();
            $('#district_id_container').hide();
            $('#outlet_id_container').hide();
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
        $(document).off("change", "#territory_id");
        $(document).on('change','#territory_id',function()
        {
            $('#district_id').val('');
            $('#outlet_id').val('');
            $('#outlet_id_container').hide();
            $('#district_id_container').hide();
            $("#system_report_container").html('');
            var territory_id=$('#territory_id').val();
            if(territory_id>0)
            {
                if(system_districts[territory_id]!==undefined)
                {
                    $('#district_id_container').show();
                    $('#district_id').html(get_dropdown_with_select(system_districts[territory_id]));
                }

            }
        });
        $(document).off("change", "#district_id");
        $(document).on('change','#district_id',function()
        {
            $('#outlet_id').val('');
            $('#upazilla_id').val('');
            $("#system_report_container").html('');
            var district_id=$('#district_id').val();
            $('#outlet_id_container').hide();
            if(district_id>0)
            {
                if(system_outlets[district_id]!==undefined)
                {
                    $('#outlet_id_container').show();
                    $('#outlet_id').html(get_dropdown_with_select(system_outlets[district_id]));
                }
            }
        });
        $(document).off("change", "#outlet_id");
        $(document).on('change','#outlet_id',function()
        {
            $("#system_report_container").html('');

        });
        $(document).off("change", "#report_name");
        $(document).on("change","#report_name",function()
        {
            $("#system_report_container").html("");
            var report_name=$('#report_name').val();
            if((report_name=='variety_amount_quantity'))
            {
                $('#container_product').show();
            }
            else
            {
                $('#container_product').hide();
            }

        });
        $(document).off("change", "#month");
        $(document).on("change","#month",function()
        {
            $("#system_report_container").html("");
            if($('#month').val()>0)
            {
                $('#container_num_of_months').show();
            }
            else
            {
                $('#num_of_months').val(1);
                $('#container_num_of_months').hide();
            }
        });
    });
</script>
