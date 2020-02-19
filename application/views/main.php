<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$CI = & get_instance();
$system_crops=Query_helper::get_info($CI->config->item('table_login_setup_classification_crops'),array('id value','name text'),array('status ="'.$CI->config->item('system_status_active').'"'),0,0,array('ordering'));
$results=Query_helper::get_info($CI->config->item('table_login_setup_classification_crop_types'),array('id value','name text','crop_id'),array('status ="'.$CI->config->item('system_status_active').'"'),0,0,array('ordering'));
$system_types=array();
foreach($results as $result)
{
    $system_types[$result['crop_id']][]=$result;
}
$results=Query_helper::get_info($CI->config->item('table_login_setup_classification_varieties'),array('id value','name text','crop_type_id'),array('status ="'.$CI->config->item('system_status_active').'"','whose ="ARM"'),0,0,array('ordering'));
$system_varieties=array();
foreach($results as $result)
{
    $system_varieties[$result['crop_type_id']][]=$result;
}
$menu_odd_color='#fee3b4';
$result=Query_helper::get_info($this->config->item('table_login_setup_system_configures'),array('config_value'),array('purpose ="' .$CI->config->item('system_purpose_pos_menu_odd_color').'"','status ="'.$CI->config->item('system_status_active').'"'),1);
if($result)
{
    $menu_odd_color=$result['config_value'];
}
$menu_even_color='#e0dff6';
$result=Query_helper::get_info($this->config->item('table_login_setup_system_configures'),array('config_value'),array('purpose ="' .$CI->config->item('system_purpose_pos_menu_even_color').'"','status ="'.$CI->config->item('system_status_active').'"'),1);
if($result)
{
    $menu_even_color=$result['config_value'];
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>POS  2018_19.2 </title>
        <link rel="icon" type="image/ico" href="http://malikseeds.com/favicon.ico"/>
        <meta charset="utf-8">
        <link rel="stylesheet" href="<?php echo str_replace('beeztola_2018_19','login_2018_19',base_url('css/bootstrap.min.css'));?>">
        <link rel="stylesheet" href="<?php echo str_replace('beeztola_2018_19','login_2018_19',base_url('css/style.css?version='.time()));?>">
        <link rel="stylesheet" href="<?php echo str_replace('beeztola_2018_19','login_2018_19',base_url('css/jquery-ui/jquery-ui.min.css')); ?>">
        <link rel="stylesheet" href="<?php echo str_replace('beeztola_2018_19','login_2018_19',base_url('css/jquery-ui/jquery-ui.theme.css')); ?>">

        <link rel="stylesheet" href="<?php echo str_replace('beeztola_2018_19','login_2018_19',base_url('css/jqx/jqx.base.css')); ?>">
        <link rel="stylesheet" href="<?php echo str_replace('beeztola_2018_19','login_2018_19',base_url('css/print.css')); ?>">
        <style>
            .navbar-nav > li {
                background-color: <?php echo $menu_odd_color ?>;
            }
            .navbar-nav > li:nth-child(even){
                background-color: <?php echo $menu_even_color ?>;
            }
        </style>
    </head>
    <body>
        <script src="<?php echo str_replace('beeztola_2018_19','login_2018_19',base_url('js/jquery-2.1.1.js')); ?>"></script>
        <script src="<?php echo str_replace('beeztola_2018_19','login_2018_19',base_url('js/bootstrap.min.js')); ?>"></script>
        <script src="<?php echo str_replace('beeztola_2018_19','login_2018_19',base_url('js/bootstrap-filestyle.min.js')); ?>"></script>
        <script src="<?php echo str_replace('beeztola_2018_19','login_2018_19',base_url('js/jquery-ui.min.js')); ?>"></script>

        <!--    for jqx grid finish-->
        <script type="text/javascript" src="<?php echo str_replace('beeztola_2018_19','login_2018_19',base_url('js/jqx/jqxcore.js')); ?>"></script>
        <script type="text/javascript" src="<?php echo str_replace('beeztola_2018_19','login_2018_19',base_url('js/jqx/jqxgrid.js')); ?>"></script>
        <script type="text/javascript" src="<?php echo str_replace('beeztola_2018_19','login_2018_19',base_url('js/jqx/jqxscrollbar.js')); ?>"></script>
        <script type="text/javascript" src="<?php echo str_replace('beeztola_2018_19','login_2018_19',base_url('js/jqx/jqxgrid.edit.js')); ?>"></script>
        <script type="text/javascript" src="<?php echo str_replace('beeztola_2018_19','login_2018_19',base_url('js/jqx/jqxgrid.sort.js')); ?>"></script>
        <script type="text/javascript" src="<?php echo str_replace('beeztola_2018_19','login_2018_19',base_url('js/jqx/jqxgrid.pager.js')); ?>"></script>
        <script type="text/javascript" src="<?php echo str_replace('beeztola_2018_19','login_2018_19',base_url('js/jqx/jqxbuttons.js')); ?>"></script>
        <script type="text/javascript" src="<?php echo str_replace('beeztola_2018_19','login_2018_19',base_url('js/jqx/jqxcheckbox.js')); ?>"></script>
        <script type="text/javascript" src="<?php echo str_replace('beeztola_2018_19','login_2018_19',base_url('js/jqx/jqxlistbox.js')); ?>"></script>
        <script type="text/javascript" src="<?php echo str_replace('beeztola_2018_19','login_2018_19',base_url('js/jqx/jqxdropdownlist.js')); ?>"></script>
        <script type="text/javascript" src="<?php echo str_replace('beeztola_2018_19','login_2018_19',base_url('js/jqx/jqxmenu.js')); ?>"></script>
        <script type="text/javascript" src="<?php echo str_replace('beeztola_2018_19','login_2018_19',base_url('js/jqx/jqxgrid.filter.js')); ?>"></script>
        <script type="text/javascript" src="<?php echo str_replace('beeztola_2018_19','login_2018_19',base_url('js/jqx/jqxgrid.selection.js')); ?>"></script>
        <script type="text/javascript" src="<?php echo str_replace('beeztola_2018_19','login_2018_19',base_url('js/jqx/jqxgrid.columnsresize.js')); ?>"></script>
        <script type="text/javascript" src="<?php echo str_replace('beeztola_2018_19','login_2018_19',base_url('js/jqx/jqxdata.js')); ?>"></script>
        <script type="text/javascript" src="<?php echo str_replace('beeztola_2018_19','login_2018_19',base_url('js/jqx/jqxdatatable.js')); ?>"></script>
        <!--    only for color picker-->
        <script type="text/javascript" src="<?php echo str_replace('beeztola_2018_19','login_2018_19',base_url('js/jqx/jqxcolorpicker.js')); ?>"></script>
        <!--    For column reorder-->
        <script type="text/javascript" src="<?php echo str_replace('beeztola_2018_19','login_2018_19',base_url('js/jqx/jqxgrid.columnsreorder.js')); ?>"></script>
        <!--    For print-->
        <script type="text/javascript" src="<?php echo str_replace('beeztola_2018_19','login_2018_19',base_url('js/jqx/jqxdata.export.js')); ?>"></script>
        <script type="text/javascript" src="<?php echo str_replace('beeztola_2018_19','login_2018_19',base_url('js/jqx/jqxgrid.export.js')); ?>"></script>
        <!--        for footer sum-->
        <script type="text/javascript" src="<?php echo str_replace('beeztola_2018_19','login_2018_19',base_url('js/jqx/jqxgrid.aggregates.js')); ?>"></script>
        <!-- for header tool tip-->
        <script type="text/javascript" src="<?php echo str_replace('beeztola_2018_19','login_2018_19',base_url('js/jqx/jqxtooltip.js')); ?>"></script>
        <!-- popup-->
        <script type="text/javascript" src="<?php echo str_replace('beeztola_2018_19','login_2018_19',base_url('js/jqx/jqxwindow.js')); ?>"></script>

        <!--    for jqx grid end-->

        <?php
        /*$system_divisions=array();
        $system_zones=array();
        $system_territories=array();
        $system_districts=array();*/
        ?>
        <script type="text/javascript">
            var base_url = "<?php echo base_url(); ?>";
            var display_date_format = "dd-M-yy";
            var SELECT_ONE_ITEM = "<?php echo $CI->lang->line('SELECT_ONE_ITEM'); ?>";
            var DELETE_CONFIRM = "<?php echo $CI->lang->line('DELETE_CONFIRM'); ?>";
            var SYSTEM_IMAGE_SIZE_TO_RESIZE=409600;//1372022=1.3mb,409600=400KB
            var SYSTEM_IMAGE_MAX_WIDTH=400;
            var SYSTEM_IMAGE_MAX_HEIGHT=300;
            var resized_image_files=[];
            var system_crops=JSON.parse('<?php echo json_encode($system_crops);?>');
            var system_types=JSON.parse('<?php echo json_encode($system_types);?>');
            var system_varieties=JSON.parse('<?php echo json_encode($system_varieties);?>');
            var system_report_color_grand='#AEC2DD';
            var system_report_color_crop='#0CA2C5';
            var system_report_color_type='#6CAB44';
        </script>
        <header class="hidden-print">

            <img alt="Logo" height="40" class="site_logo pull-left" src="<?php echo str_replace('beeztola_2018_19','login_2018_19',base_url('images/logo.png'));?>">
            <div class="site_title pull-left">
                <div style="font-size: 25px;line-height: 40px;">
                    A R MALIK SEEDS PVT. LTD.
                </div>
                <div style="font-size: 15px;line-height: 15px;">
                    ( ..:: Beeztola POS System ::..)
                </div>
            </div>

        </header>

        <div class="container-fluid" style="margin-bottom: 40px;">
            <div id="system_menus">
                <?php
                $CI->load->view('menu');
                ?>
            </div>

            <div class="row dashboard-wrapper">
                <div class="col-sm-12" id="system_content">

                </div>
            </div>
        </div>
        <div id="system_loading"><img src="<?php echo str_replace('beeztola_2018_19','login_2018_19',base_url('images/spinner.gif'));?>"></div>
        <div id="system_message" class="hidden-print"></div>
        <div id="popup_window">
            <div id="popup_window_title">Details</div>
            <div id="popup_content" style="overflow: auto;">
            </div>
        </div>
        <script type="text/javascript" src="<?php echo str_replace('beeztola_2018_19','login_2018_19',base_url('js/system_common.js?version='.time())); ?>"></script>
        <script type="text/javascript" src="<?php echo str_replace('beeztola_2018_19','login_2018_19',base_url('js/system_triggers.js?version='.time())); ?>"></script>
    </body>
</html>
