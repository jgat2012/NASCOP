<?php?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title><?php echo $title;?></title>
		<?php

		$this -> load -> view('sections/head');
		?>
	</head>
	<body>
		<div class="container" style="min-width:600px;width:70%;">
			<header>
				<div class="row-fluid">
					<div class="span11">
						<?=img('nascop.jpg')
						?>
					</div>
					<div class="span1">
						<a href='<?php echo base_url();?>' class="btn btn-success" style="float:right;">Dashboard</a>
					</div>
				</div>
			</header>
			<script>
				$(document).ready(function() {
					$(".error").css("display", "block");
				})
			</script>
			<?php
			echo validation_errors('<span class="message error">', '</span>');
			if ($this -> session -> userdata("changed_password")) {
				$message = $this -> session -> userdata("changed_password");
				echo "<p class='message error'>" . $message . "</p>";
				$this -> session -> set_userdata("changed_password", "");
			}
			if (isset($invalid)) {
				echo "<p class='message error'>Invalid Credentials. Please try again " . @$login_attempt . "</p>";
			} else if (isset($inactive)) {
				echo "<p class='message error'>The Account is not active. Seek help from the Administrator</p>";
			} else if (isset($unactivated)) {
				echo "<p class='message error'>Your Account Has Not Been Activated.<br/>Please Check your Email to Activate Account</p>";
			} else if (isset($expired)) {
				echo "<p class='message error'>" . @$login_attempt . "</p>";
			}
			?>
			<div class="row-fluid">
				<div id="signup_form" style="color:#000">
					<div class="short_title" >
						Forgot Your Password
					</div>
					<form action="<?php echo base_url().'user_management/resendPassword'?>" method="post" class="form-inline">
						<div class="row-fluid">
							<div class="span12">
								<div class="alert alert-info">
									<button type='button' class='close' data-dismiss='alert'>&times;</button>
									To reset your password, please enter your email address
								</div>
							</div>
						</div>
						<div class="row-fluid">
							<div class="span12">
								<div class="input-prepend" id='div_email'>
									<span class="add-on">@</span>
									<input type="hidden" name="type" value="email" />
									<input style="height:31px;" type="text" name="contact_email" class="input-xlarge" id="contact_email" value="" placeholder="youremail@example.com" required="">
								</div>
							</div>
						</div>
						<div class="row-fluid">
							<div class="span3">
								<span><a href="<?php echo base_url().'user_management/login' ?>" class='btn btn-warning'> Go to login</a></span>
							</div>
							<div class="span4">
								<input type="submit" class="btn btn-success" name="resendPassword" id="register" value="Submit" style="margin-left:50px; padding-left:30px; padding-right:30px;margin-right:50px ">
							</div>
						</div>
					</form>
				</div>
			</div>
			<div class="row-fluid">
				<footer id="bottom_ribbon2">
					<div class="span4"></div>
					<div id="footer_text2" class="span8">
						Government of Kenya &copy; <?php echo date('Y');?>.
						All Rights Reserved
					</div>
				</footer>
			</div>
		</div>
		</div>
	</body>
</html>
