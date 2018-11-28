<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$CI=& get_instance();
?>

<div class="row widget">
    <div class="widget-header">
        <div class="title">
            <?php echo $title; ?>
        </div>
        <div class="clearfix"></div>
    </div>
    <div style="overflow-x: auto;" class="row show-grid">
        <table class="table table-bordered">
            <thead>
            <tr>
                <th style="min-width: 100px;">Code</th>
                <th style="min-width: 50px;">Used?</th>
                <th style="min-width: 100px;">Created Date</th>
            </tr>
            </thead>
            <tbody>
            <?php

            foreach($histories as $row)
            {
                ?>
                <tr>
                    <td><label><?php echo $row['code_verification'];?></label></td>
                    <td><label><?php echo $row['status_used'];?></label></td>
                    <td><label><?php echo System_helper::display_date_time($row['date_created']);?></label></td>
                </tr>
            <?php
            }
            ?>
            </tbody>
        </table>
    </div>
</div>
<div class="clearfix"></div>
