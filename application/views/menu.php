<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$user=User_helper::get_user();
if($user)
{
    ?>
    <div class="navbar navbar-default" role="navigation">
        <div class="container">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
            </div>
            <div class="collapse navbar-collapse navbar-ex1-collapse">
                <ul class="nav navbar-nav">
                    <li class="menu-item"><a href="<?php echo site_url(); ?>">Dashboard</a></li>
                    <?php
                        $menu=User_helper::get_html_menu();
                        echo $menu;
                    ?>
                    <?php
                    $notification_sub_menus=Query_helper::get_info($this->config->item('table_pos_setup_notice_types'),array('*'),array('status ="'.$this->config->item('system_status_active').'"'));
                    if($notification_sub_menus)
                    {
                      ?>
                        <li class="menu-item dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown">Notice<b class="caret"></b></a>
                            <ul class="dropdown-menu">
                                <?php
                                foreach($notification_sub_menus as $sub_menu)
                                {
                                    ?>
                                    <li><a href="<?php echo site_url('notices/index/list/'.$sub_menu['id'])?>"><?php echo $sub_menu['name']?></a></li>
                                <?php
                                }
                                ?>
                            </ul>
                        </li>
                    <?php
                    }
                    ?>
                    <li class="menu-item"><a href="<?php echo site_url('home/logout'); ?>">Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
    <?php
}
?>
<script type="text/javascript">

    jQuery(document).ready(function()
    {
        ///$(document).on("click", ".dropdown-submenu", function(event)
        $( ".dropdown-submenu" ).click(function(event)
        {
            var target = $( event.target );
            // stop bootstrap.js to hide the parents
            if(target.attr('class')=='dropdown-toggle')
            {
                event.preventDefault();
                event.stopPropagation();
            }
            // hide the open children
            $( this ).find(".dropdown-submenu").removeClass('open');
            // add 'open' class to all parents with class 'dropdown-submenu'
            $( this ).parents(".dropdown-submenu").addClass('open');
            // this is also open (or was)
            $( this ).toggleClass('open');
        });
    });
</script>