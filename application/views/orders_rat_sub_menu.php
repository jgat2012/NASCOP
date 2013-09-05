<?php
if(!isset($quick_link)){
$quick_link = null;
}  
?>
<div id="quick_menu" class="btn-group">
	<a class="btn" href="<?php echo site_url("order_rationalization/submitted_orders/0");?>"><img  src="<?php echo base_url().'assets/img/pending_icon.png'?>">Pending</a>
	<a class="btn" href="<?php echo site_url("order_rationalization/submitted_orders/1");?>"><img src="<?php echo base_url().'assets/img/approved_icon.png'?>">Approved</a>
	<a class="btn" href="<?php echo site_url("order_rationalization/submitted_orders/2");?>"><img src="<?php echo base_url().'assets/img/delete_icon.png'?>">Declined</a>
	<a class="btn" href="<?php echo site_url("order_rationalization/submitted_orders/3");?>"><img src="<?php echo base_url().'assets/img/dispatch_icon.png'?>">Dispatched</a>
</div>

