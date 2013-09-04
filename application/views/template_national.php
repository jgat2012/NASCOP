
<!DOCTYPE html">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $title; ?></title>
<?php
$this -> load -> view('sections/head');
?> 

</head>

<body onload="set_interval()" onmousemove="set_interval()" onclick="set_interval" onkeypress="set_interval()" onscroll="set_interval()">


	<div id="top-panel" style="margin:0px;">

		<div class="logo">
			<a class="logo" href="<?php echo base_url(); ?>" ></a> 
</div>


				<div id="system_title">
					<?php
					$this -> load -> view('sections/banner');
					?>
					<div id="facility_name">							
						<span><?php echo "NASCOP"; ?></span>
					</div>
					
				</div>
				<div class="banner_text"><?php echo $banner_text; ?></div>	
				
 <div id="top_menu"> 
 <div class="welcome_msg">
	<span>Welcome <b style="font-weight: bolder;font-size: 20px;">National User</b>. <a id="logout_btn" href="<?php echo base_url().'user_management/login' ?>"><i class="icon-off"></i>Login</a></span>
	<br>
	<span class="date"><?php echo date('l, jS \of F Y') ?></span>
	<input type="hidden" id="facility_hidden" />
</div>
</div>

</div>

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
		    <li><a href="<?php echo base_url().'user_manual.pdf' ?>"><i class="icon-book"></i>User Manual</a></li>				
		</ul>
		<h3>Notifications</h3>
		<ul id="notification1" class="nav nav-list well">
		<li><a id='online' class='admin_link'><i class='icon-signal'></i>Online Facilities <div id="span2" class='badge badge-important'></div></a></li>
		<li><a id='inactive' class='admin_link'><i class='icon-th'></i>Deactivated Users <div id="span1" class='badge badge-important'></div></a></li>
		</ul>	
	</div>
	<?php
	}

	$this -> load -> view($content_view);

	//Load modals view
	$this -> load -> view('sections/modals_v');
	//Load modals view end
    ?>
 <div id="bottom_ribbon">
 	<div id="footer">
 		<?php $this -> load -> view('footer_v'); ?>
 	</div>
 </div> 
</body>

</html>