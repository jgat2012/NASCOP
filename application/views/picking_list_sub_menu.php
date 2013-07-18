<?php
if(!isset($quick_link)){
$quick_link = null;
}  
?>
<div id="quick_menu" class="btn-group">
	<a class="btn btn-primary btn-quickmenu" href="<?php echo site_url("picking_list_management/submitted_lists/0");?>"><img  src="<?php echo base_url().'Images/open-icon.png'?>">Open Lists</a>
	<a class="btn btn-primary btn-quickmenu" href="<?php echo site_url("picking_list_management/submitted_lists/1");?>"> <img  src="<?php echo base_url().'Images/close-icon.png'?>"> Closed Lists</a> 
</div>
