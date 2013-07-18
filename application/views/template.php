<?php
/**
 * Using Session Data
 */
if (!$this -> session -> userdata('user_id')) {
	redirect("User_Management/login");
}
if (!isset($link)) {
	$link = null;
}
$actual_page = $this -> uri -> segment(1);

if ($this -> uri -> segment(2)) {
	$actual_page .= "/" . $this -> uri -> segment(2);
}
if ($this -> uri -> segment(3)) {
	$actual_page .= "/" . $this -> uri -> segment(3);
}
if ($this -> uri -> segment(4)) {
	$actual_page .= "/" . $this -> uri -> segment(4);
}
if ($this -> uri -> segment(5)) {
	$actual_page .= "/" . $this -> uri -> segment(5);
}
if ($this -> uri -> segment(6)) {
	$actual_page .= "/" . $this -> uri -> segment(6);
}
$this -> input -> set_cookie("actual_page", $actual_page, 3600);
//setcookie("actual_page",$actual_page,3600);

$access_level = $this -> session -> userdata('user_indicator');
$user_is_administrator = false;
$user_is_facility_administrator = false;
$user_is_nascop = false;
$user_is_pharmacist = false;

if ($access_level == "system_administrator") {
	$user_is_administrator = true;
} else if ($access_level == "facility_administrator") {
	$user_is_facility_administrator = true;
} else if ($access_level == "pharmacist") {
	$user_is_pharmacist = true;

} else if ($access_level == "nascop_staff") {
	$user_is_nascop = true;
}
?>


<!DOCTYPE html">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $title; ?></title>
<link rel="SHORTCUT ICON" href="<?php echo base_url().'Images/favicon.ico'?>">


<?php
$this -> load -> view('sections/head');
if ($user_is_pharmacist || $user_is_facility_administrator || $user_is_administrator) {
	echo "<script src=\"" . base_url() . "Scripts/offline_database.js\" type=\"text/javascript\"></script>";

}
/**
 * Load View with Head Section
 */

if (isset($script_urls)) {
	foreach ($script_urls as $script_url) {
		echo "<script src=\"" . $script_url . "\" type=\"text/javascript\"></script>";
	}
}
?>

<?php
if (isset($scripts)) {
	foreach ($scripts as $script) {
		echo "<script src=\"" . base_url() . "Scripts/" . $script . "\" type=\"text/javascript\"></script>";
	}
}

if (isset($styles)) {
	foreach ($styles as $style) {
		echo "<link href=\"" . base_url() . "CSS/" . $style . "\" type=\"text/css\" rel=\"stylesheet\"/>";
	}
}
?> 

<script>
   $(document).ready(function(){
   	  <?php 
		if($user_is_pharmacist){
	   ?>
	    $('#notification1').load('<?php echo base_url().'facilitydashboard_management/order_notification';?>');
	  <?php
		}
        if($user_is_facility_administrator){
	   ?>
		$('#notification1').load('<?php echo base_url().'facilitydashboard_management/order_notification';?>');
      <?php
        }
	   ?>});
</script>
<script>
	  $(document).ready(function(){
		 $(".error").css("display","block");
		 $("#inactive_users").click(function(){
		 	<?php
			$this -> session -> set_userdata("link_id", "index");
			$this -> session -> set_userdata("linkSub", "user_management");
			$this -> session -> set_userdata("linkTitle","Users Management");
		 	?>
		 });
	  });
</script>
<?php 
//Load tableTools for datatables printing and exporting
if(isset($report_title)){
	?>
	<style type="text/css" title="currentStyle">
		@import "../../media/css/demo_page.css";
		@import "../../media/css/demo_table.css";
		@import "<?php echo base_url().'css/datatable/TableTools.css' ?>";
	</style>
	<script type="text/javascript" charset="utf-8" src="<?php echo base_url().'Scripts/datatable/ZeroClipboard.js' ?>"></script>
	<script type="text/javascript" charset="utf-8"  src="<?php echo base_url().'Scripts/datatable/TableTools.js' ?>"></script>
	<?php
}
?>      
<style>
	.setting_table {
		font-size: 0.8em;
	}
</style>
</head>

<body>


	<div id="top-panel" style="margin:0px;">

		<div class="logo">
			<a class="logo" href="<?php echo base_url(); ?>" ></a> 
</div>


				<div id="system_title">
					<?php
					$this -> load -> view('sections/banner');
					?>
					<div id="facility_name">							
						<span><?php echo $this -> session -> userdata('facility_name'); ?></span>
					</div>
					
				</div>
				<div class="banner_text"><?php echo $banner_text; ?></div>	
				
 <div id="top_menu"> 

 	<?php
	//Code to loop through all the menus available to this user!
	//Fet the current domain
	$menus = $this -> session -> userdata('menu_items');
	$current = $this -> router -> class;
	$counter = 0;
	if($menus){
?>
 	<a href="<?php  echo site_url('home_controller'); ?>" class="top_menu_link  first_link <?php
	if ($current == "home_controller") {echo " top_menu_active ";
	}
?>">Home </a><?php } ?>
<?php
if($menus){
foreach($menus as $menu){?>
	<a href = "<?php echo site_url($menu['url']); ?>" class="top_menu_link <?php
	if ($current == $menu['url'] || $menu['url'] == $link) {echo " top_menu_active ";
	}
?>"><?php echo $menu['text']; if($menu['offline'] == "1"){?>
	 <!-- Offline -->
	 <span class=" red_"></span></a>
	
<?php } else{ ?>
	<!-- Online -->
	 <span class=" green_"></span></a>
<?php } ?>



<?php
$counter++;
}}
if($menus){
	?>

<div  class="btn-group" id="div_profile" >
	<a href="#" class="top_menu_link btn dropdown-toggle" data-toggle="dropdown"  id="my_profile"><i class="icon-user icon-black"></i> Profile  <span class="caret"></span></a>
	<ul class="dropdown-menu" id="profile_list" role="menu">
		<li><a href="#edit_user_profile" data-toggle="modal"><i class="icon-edit"></i> Edit Profile</a></li>
		<li id="change_password_link"><a href="#user_change_pass" data-toggle="modal"><i class=" icon-asterisk"></i> Change Password</a></li>
	</ul>
</div>
<div class="welcome_msg">
	<span>Welcome <b style="font-weight: bolder;font-size: 20px;"><?php echo $this -> session -> userdata('full_name'); ?></b>. <a href="<?php echo base_url().'user_management/logout' ?>">Logout</a></span>
	<br>
	<span class="date"><?php echo date('l, jS \of F Y') ?></span>
</div>
<?php } ?>
 </div>

</div>


<?php
//Load validation settings for reports
if(isset($reports)|| isset($report_title)){
?>

<?php

}
	?>


	<?php
	if($this->session->userdata("message_user_update_success")){
		?>
		<script type="text/javascript">
			setTimeout(function() {
				$("#msg_user_update").fadeOut("2000");
			}, 6000)
		</script>
		<div id="msg_user_update"><?php  echo $this -> session -> userdata("message_user_update_success"); ?></div>
		<?php
		$this -> session -> unset_userdata('message_user_update_success');
		}
		if(!isset($hide_side_menu)){
	?>
	<div class="left-content" style="float: left">


		<h3>Quick Links</h3>
		<ul class="nav nav-list well">

			
			
			
		</ul>
		<h3>Notifications</h3>
		<ul id="notification1" class="nav nav-list well">
		
		</ul>	
	</div>
	<?php
	}

	$this -> load -> view($content_view);
?>
     
    <!-- Modal edit user profile-->
    <div id="edit_user_profile" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	  <form action="<?php echo base_url().'user_management/profile_update' ?>" method="post">
	  <div class="modal-header">
	    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
	    <h3 id="myModalLabel">User details</h3>
	  </div>
	  <div class="modal-body">
	   
			<table>
				<tr>

					<td><label >Full Name</label></td><td>
					<div class="input-prepend">
						<span class="add-on"><i class="icon-user"></i></span>
						<input type="text" class="input-xlarge" name="u_fullname" id="u_fullname" required="" value="<?php echo $this->session->userdata('full_name') ?>" />
					</div></td>
				</tr>
				<tr>
					<td><label >Username</label></td><td>
					<div class="input-prepend">
						<span class="add-on"><i class="icon-user"></i></span>
						<input type="text" class="input-xlarge" name="u_username" id="u_username" required="" value="<?php echo $this->session->userdata('username') ?>" />
					</div></td>
				</tr>
				<tr>
					<td><label>Email Address</label></td><td>
					<div class="input-prepend">
						<span class="add-on"><i class="icon-envelope"></i></span>
						<input type="email" class="input-xlarge" name="u_email" id="u_email" value="<?php echo $this->session->userdata('Email_Address') ?>" />
					</div></td>
				</tr>
				<tr>
					<td><label>Phone Number</label></td><td>
					<div class="input-prepend">
						<span class="add-on"><i class="icon-plus"></i>254</span>
						<input type="tel" class="input-large" name="u_phone" id="u_phone" value="<?php echo $this->session->userdata('Phone_Number') ?>"/>
					</div></td>
				</tr>
			</table>
		
	  </div>
	  
	  <div class="modal-footer">
	    <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
	    <input type="submit" class="btn btn-primary" value="Save changes">
	  </div>
	  </form>
	</div>
	<!-- Modal edit user profile end-->
	<!-- Modal edit change password-->
	<div id="user_change_pass" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	  <form action="<?php echo base_url().'user_management/profile_update' ?>" method="post">
	  <div class="modal-header">
	    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
	    <h3 id="myModalLabel">Change password</h3>
	  </div>
	  <div class="modal-body">
	   <input type="hidden" name="base_url" id="base_url" value="<?php echo base_url() ?>" />
	   <form id="fmChangePassword" action="<?php echo base_url().'user_management/save_new_password'?>" method="post" class="well">
			<span class="message error" id="error_msg_change_pass"></span>
			<br>
			<table>
				<tr>
				<td><label >Old Password</label></td><td><input type="password" name="old_password" id="old_password" required=""></td>
				</tr>
				<tr>
				<td><label >New Password</label></td><td><input type="password" name="new_password" id="new_password" required=""><span id="result"></span></td>
				</tr>
				<tr>
				<td><label >Confirm New Password</label></td><td>
				<input type="password" name="new_password_confirm" id="new_password_confirm" required="">
				</td>
				</tr>
			</table>
	
		</form>
		
	  </div>
	  
	  <div class="modal-footer">
	    <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
	    <input type="button" class="btn btn-primary" name="btn_submit_change_pass" id="btn_submit_change_pass" value="Save changes">
	  </div>
	  </form>
	</div>
	<!-- Modal edit change password end-->
 <div id="bottom_ribbon">
 	<div id="footer">
 		<?php $this->load->view('footer_v');?>
 	</div>
 </div> 
 <script></script>  
</body>

</html>