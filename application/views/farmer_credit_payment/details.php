<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
$CI = & get_instance();

$action_buttons = array();
$action_buttons[] = array(
    'label' => $CI->lang->line("ACTION_BACK"),
    'href'=>site_url($CI->controller_url.'/index/list_payment/'.$item['farmer_id'])
);

$CI->load->view("action_buttons", array('action_buttons' => $action_buttons));

?>
<div class="row widget" style="margin-top:0">
    <div class="widget-header">
        <div class="title">
            <?php echo $title; ?>
        </div>
        <div class="clearfix"></div>
    </div>
    <?php
    echo $CI->load->view("info_basic", $info_basic, true);
    $info_payment['accordion']['collapse'] = 'in';
    $info_payment['accordion']['header']='+ Payment Information';
    $info_payment['accordion']['div_id']='info_payment';
    echo $CI->load->view("info_basic", $info_payment, true);
    ?>

    <?php
    if(sizeof($payment_histories)>0)
    {
        ?>
        <div class="widget-header">
            <div class="title">
                Payment Histories
            </div>
            <div class="clearfix"></div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-12">
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th>History ID</th>
                        <th>Credit Limit Old</th>
                        <th>Credit Limit New</th>
                        <th>Balance Old</th>
                        <th>Balance New</th>
                        <th>Adjust Amount</th>
                        <th>Reference No</th>
                        <th>Remarks</th>
                        <th>Reason</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach($payment_histories as $payment)
                    {
                        ?>
                        <tr>
                            <td><?php echo $payment['id']?></td>
                            <td><?php echo $payment['credit_limit_old']?></td>
                            <td><?php echo $payment['credit_limit_new']?></td>
                            <td><?php echo $payment['balance_old']?></td>
                            <td><?php echo $payment['balance_new']?></td>
                            <td><?php echo $payment['amount_adjust']?></td>
                            <td><?php echo $payment['reference_no']?></td>
                            <td><?php echo $payment['remarks']?></td>
                            <td><?php echo $payment['remarks_reason']?></td>
                        </tr>
                    <?php
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php
    }
    ?>

</div>

<script type="text/javascript">
    jQuery(document).ready(function ($) {
        system_off_events(); // Triggers
        system_preset({controller: '<?php echo $CI->router->class; ?>'});
    });
</script>
