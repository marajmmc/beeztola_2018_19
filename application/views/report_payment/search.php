<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$CI = & get_instance();
$action_buttons=array();
$action_buttons[]=array(
    'label'=>$CI->lang->line("ACTION_REFRESH"),
    'href'=>site_url($CI->controller_url.'/index')
);
$CI->load->view('action_buttons',array('action_buttons'=>$action_buttons));

?>
<form class="form_valid" id="save_form" action="<?php echo site_url($CI->controller_url.'/index/list');?>" method="post">
    <div class="row widget">
        <div class="widget-header">
            <div class="title">
                <?php echo $title; ?>
            </div>
            <div class="clearfix"></div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-6">
                <div class="row show-grid">
                    <div class="col-xs-6">
                        <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_OUTLET');?><span style="color:#FF0000">*</span></label>
                    </div>
                    <div class="col-xs-6">
                        <select name="report[outlet_id]" class="form-control">
                            <option value=""><?php echo $CI->lang->line('SELECT');?></option>
                            <?php
                            foreach($assigned_outlet as $outlet)
                            {?>
                                <option value="<?php echo $outlet['customer_id']?>"><?php echo $outlet['name'];?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="row show-grid">
                    <div class="col-xs-6">
                        <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_FISCAL_YEAR');?></label>
                    </div>
                    <div class="col-xs-6">
                        <select id="fiscal_year_id" name="report[fiscal_year_id]" class="form-control">
                            <option value=""><?php echo $CI->lang->line('SELECT');?></option>
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
            </div>
            <div class="col-xs-6">

                <div class="row show-grid">
                    <div class="col-xs-6">
                        <input type="text" id="date_start_payment" name="report[date_start_payment]" class="form-control date_large" value="<?php echo $date_start_payment; ?>">
                    </div>
                    <div class="col-xs-6">
                        <label class="control-label"><?php echo $CI->lang->line('LABEL_DATE_START').' (Payment)';?></label>
                    </div>
                </div>
                <div class="row show-grid">
                    <div class="col-xs-6">
                        <input type="text" id="date_end_payment" name="report[date_end_payment]" class="form-control date_large" value="<?php echo $date_end_payment; ?>">
                    </div>
                    <div class="col-xs-6">
                        <label class="control-label"><?php echo $CI->lang->line('LABEL_DATE_END').' (Payment)';?></label>
                    </div>
                </div>
                <div class="row show-grid">
                    <div class="col-xs-6">
                        <input type="text" id="date_start_sale" name="report[date_start_sale]" class="form-control date_large" value="<?php echo $date_start_sale; ?>">
                    </div>
                    <div class="col-xs-6">
                        <label class="control-label"><?php echo $CI->lang->line('LABEL_DATE_START').' (Sale)';?></label>
                    </div>
                </div>
                <div class="row show-grid">
                    <div class="col-xs-6">
                        <input type="text" id="date_end_sale" name="report[date_end_sale]" class="form-control date_large" value="<?php echo $date_end_sale; ?>">
                    </div>
                    <div class="col-xs-6">
                        <label class="control-label"><?php echo $CI->lang->line('LABEL_DATE_END').' (Sale)';?></label>
                    </div>
                </div>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">

            </div>
            <div class="col-xs-4">
                <div class="action_button pull-right">
                    <button id="button_action_report" type="button" class="btn" data-form="#save_form"><?php echo $CI->lang->line("ACTION_REPORT_VIEW"); ?></button>
                </div>

            </div>
            <div class="col-xs-4">

            </div>
        </div>
    </div>

    <div class="clearfix"></div>
</form>

<div id="system_report_container">

</div>
<script type="text/javascript">

    jQuery(document).ready(function()
    {
        system_preset({controller:'<?php echo $CI->router->class; ?>'});

        $(".date_large").datepicker({dateFormat : display_date_format,changeMonth: true,changeYear: true,yearRange: "c-2:c+2"});
        $(document).off("change","#fiscal_year_id");

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
    });
</script>
