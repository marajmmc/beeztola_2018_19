<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$CI=& get_instance();
?>
<div style="width: 200px;font-size: 10px;text-align: center; font-weight: bold;line-height: 10px;margin-left:10px; ">
    <img src="<?php echo site_url('barcode/index/farmer/'.($item['id']));  ?>">
    <div><?php echo Barcode_helper::get_barcode_farmer($item['id']);?></div>
    <div><?php echo $item['name'];?></div>
    <div><?php echo $item['farmer_type_name'];?></div>
    <div>Mobile No: <?php echo $item['mobile_no'];?></div>
</div>

