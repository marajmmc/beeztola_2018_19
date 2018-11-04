<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$CI=& get_instance();
$action_buttons=array();
$action_buttons[]=array(
    'label'=>$CI->lang->line("ACTION_BACK"),
    'href'=>site_url($CI->controller_url)
);
if(isset($CI->permissions['action2']) && ($CI->permissions['action2']==1))
{
    $action_buttons[]=array(
        'label'=>$CI->lang->line("ACTION_EDIT"),
        'href'=>site_url($CI->controller_url.'/index/edit/'.$user_info['user_id'])
    );
}
$CI->load->view('action_buttons',array('action_buttons'=>$action_buttons));
?>

<div class="row widget">
<div class="widget-header">
    <div class="title">
        <?php echo $title; ?>
    </div>
    <div class="clearfix"></div>
</div>
<div class="panel-group" id="accordion">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4 class="panel-title">
                <a class="accordion-toggle external" data-toggle="collapse" data-target="#collapse1" href="#">
                    Credentials
                </a>
            </h4>
        </div>
        <div id="collapse1" class="panel-collapse collapse in">
            <div class="row show-grid">
                <div class="col-xs-4">
                    <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_EMPLOYEE_ID');?></label>
                </div>
                <div class="col-sm-4 col-xs-8">
                    <label class="control-label"><?php echo $user_info['employee_id'];?></label>
                </div>
            </div>
            <div class="row show-grid">
                <div class="col-xs-4">
                    <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_USERNAME');?></label>
                </div>
                <div class="col-sm-4 col-xs-8">
                    <label class="control-label"><?php echo $user_info['user_name'];?></label>
                </div>
            </div>
            <div class="row show-grid">
                <div class="col-xs-4">
                    <label class="control-label pull-right">User Creation Date</label>
                </div>
                <div class="col-sm-4 col-xs-8">
                    <label class="control-label"><?php echo System_helper::display_date($user_info['user_date_created']);?></label>
                </div>
            </div>
            <div class="row show-grid">
                <div class="col-xs-4">
                    <label class="control-label pull-right">User Created By</label>
                </div>
                <div class="col-sm-4 col-xs-8">
                    <label class="control-label"><?php echo $users[$user_info['user_created']]['name'];?></label>
                </div>
            </div>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4 class="panel-title">
                <a class="accordion-toggle external" data-toggle="collapse" data-target="#authentication_setup" href="#">
                    Authentication Setup</a>
            </h4>
        </div>
        <div id="authentication_setup" class="panel-collapse collapse">
            <div class="row show-grid">
                <div class="col-xs-4">
                    <label class="control-label pull-right">Maximum Allowed Browser to login</label>
                </div>
                <div class="col-sm-4 col-xs-8">
                    <label class="control-label"><?php echo $user_info['max_logged_browser'];?></label>
                </div>
            </div>
            <div class="row show-grid">
                <div class="col-xs-4">
                    <label class="control-label pull-right">Number of Days for inactive mobile verification</label>
                </div>
                <div class="col-sm-4 col-xs-8">
                    <label class="control-label">
                        <?php
                        $time=time();
                        if($user_info['time_mobile_authentication_off_end']>$time)
                        {
                            echo ceil(($user_info['time_mobile_authentication_off_end']-$time)/(3600*24)).' day(s)';
                        }
                        else
                        {
                            echo 'Global setup.';
                        }
                        ?>
                    </label>
                </div>
            </div>
            <?php
            if($user_info['user_authentication_setup_changed']>0)
            {
                ?>
                <div style="" class="row show-grid">
                    <div class="col-xs-4">
                        <label class="control-label pull-right">Last Setup Changed Time</label>
                    </div>
                    <div class="col-sm-4 col-xs-8">
                        <label class="control-label"><?php echo System_helper::display_date_time($user_info['date_authentication_setup_changed']);?></label>
                    </div>
                </div>
                <div style="" class="row show-grid">
                    <div class="col-xs-4">
                        <label class="control-label pull-right">Last Setup Changed By</label>
                    </div>
                    <div class="col-sm-4 col-xs-8">
                        <label class="control-label">
                            <?php echo ($user_info['user_status_changed']==-1)? 'System': $users[$user_info['user_authentication_setup_changed']]['name'];?>
                        </label>
                    </div>
                </div>

            <?php
            }
            ?>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4 class="panel-title">
                <a class="external" data-toggle="collapse" data-target="#collapse3" href="#">
                    User Group,Designation </a>
            </h4>
        </div>
        <div id="collapse3" class="panel-collapse collapse">
            <div class="row show-grid">
                <div class="col-xs-4">
                    <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_USER_GROUP');?></label>
                </div>
                <div class="col-sm-4 col-xs-8">
                    <label class="control-label"><?php echo $user_info['group_name'];?></label>
                </div>
            </div>
            <div class="row show-grid">
                <div class="col-xs-4">
                    <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DESIGNATION_NAME');?></label>
                </div>
                <div class="col-sm-4 col-xs-8">
                    <label class="control-label"><?php echo $user_info['designation_name'];?></label>
                </div>
            </div>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4 class="panel-title">
                <a class="accordion-toggle external" data-toggle="collapse" data-target="#assigned_outlets" href="#">
                    User's Outlet</a>
            </h4>
        </div>
        <div id="assigned_outlets" class="panel-collapse collapse">
            <div style="" class="row show-grid">
                <div class="col-xs-4">
                    <label class="control-label pull-right">Assigned Outlets</label>
                </div>
                <div class="col-sm-4 col-xs-8">
                    <?php
                    foreach($assigned_outlets as $outlet)
                    {
                        ?>
                        <label class="control-label"><?php echo $outlet['outlet_name']; ?></label>
                    <?php
                    }
                    ?>

                </div>
            </div>

        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4 class="panel-title">
                <a class="external" data-toggle="collapse" data-target="#collapse6" href="#">
                    Employee Personal Information</a>
            </h4>
        </div>
        <div id="collapse6" class="panel-collapse collapse">
            <div class="row show-grid">
                <div class="col-xs-4">
                    <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_NAME');?></label>
                </div>
                <div class="col-sm-4 col-xs-8">
                    <label class="control-label"><?php echo $user_info['name'];?></label>
                </div>
            </div>
            <div class="row show-grid">
                <div class="col-xs-4">
                    <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_DATE_BIRTH');?></label>
                </div>
                <div class="col-sm-4 col-xs-8">
                    <label class="control-label"><?php echo System_helper::display_date($user_info['date_birth']);?></label>
                </div>
            </div>
            <div class="row show-grid">
                <div class="col-xs-4">
                    <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_GENDER');?></label>
                </div>
                <div class="col-sm-4 col-xs-8">
                    <label class="control-label"><?php echo $user_info['gender'];?></label>
                </div>
            </div>
            <div class="row show-grid">
                <div class="col-xs-4">
                    <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_MARITAL_STATUS');?></label>
                </div>
                <div class="col-sm-4 col-xs-8">
                    <label class="control-label"><?php echo $user_info['status_marital'];?></label>
                </div>
            </div>
            <div class="row show-grid">
                <div class="col-xs-4">
                    <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_NID');?></label>
                </div>
                <div class="col-sm-4 col-xs-8">
                    <label class="control-label"><?php echo $user_info['nid'];?></label>
                </div>
            </div>

        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4 class="panel-title">
                <a class="external" data-toggle="collapse" data-target="#contact" href="#">
                    Contact Info</a>
            </h4>
        </div>
        <div id="contact" class="panel-collapse collapse">
            <div class="row show-grid">
                <div class="col-xs-4">
                    <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_MOBILE_NO');?></label>
                </div>
                <div class="col-sm-4 col-xs-8">
                    <label class="control-label"><?php echo $user_info['mobile_no'];?></label>
                </div>
            </div>
            <div class="row show-grid">
                <div class="col-xs-4">
                    <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_ADDRESS');?></label>
                </div>
                <div class="col-sm-4 col-xs-8">
                    <label class="control-label"><?php echo $user_info['address'];?></label>
                </div>
            </div>
            <div class="row show-grid">
                <div class="col-xs-4">
                    <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_BLOOD_GROUP');?></label>
                </div>
                <div class="col-sm-4 col-xs-8">
                    <label class="control-label"><?php echo $user_info['blood_group'];?></label>
                </div>
            </div>
        </div>
    </div>
</div>
    <div style="" class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $CI->lang->line('STATUS');?></label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo $user_info['status'];?></label>
        </div>
    </div>
    <?php
    if(($user_info['user_status_changed']==-1)||($user_info['user_status_changed']>0))
    {
        ?>
        <div style="" class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Last Status Change Reason</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $user_info['remarks_status_change'];?></label>
            </div>
        </div>
        <div style="" class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Last Status Changed Time</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo System_helper::display_date_time($user_info['date_status_changed']);?></label>
            </div>
        </div>
        <div style="" class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Last Status Changed By</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label">
                    <?php echo ($user_info['user_status_changed']==-1)? 'System': $users[$user_info['user_status_changed']]['name'];?>
                </label>
            </div>
        </div>

    <?php
    }
    ?>
<div class="clearfix"></div>
<script type="text/javascript">
    jQuery(document).ready(function()
    {
        system_preset({controller:'<?php echo $CI->router->class; ?>'});
    });
</script>
