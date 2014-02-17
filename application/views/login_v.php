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
						<?=img('nascop.jpg')?>
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
				<div id="signup_form">
					<div class="short_title" >
						Login
					</div>
					<form class="login-form" action="<?php echo base_url().'user_management/authenticate'?>" method="post" style="margin:0 auto " >
						<label> <strong >Please Enter Your Email/Username</strong>
							<br>
							<input type="text" name="username" class="input-xlarge" id="username" value="" placeholder="user@example.com">
						</label>
						<label> <strong >Password</strong>
							<br>
							<input type="password" name="password" class="input-xlarge" id="password" placeholder="********">
						</label>
						<input type="submit" class="btn" name="register" id="register" value="Login" >
						<div style="margin:auto;width:auto" class="anchor">
							<strong><a href="<?php echo base_url().'user_management/resetPassword' ?>" >Forgot Password?</a></strong>
						</div>
					</form>
				</div>
			</div>
			<div class="row-fluid">
				<footer id="bottom_ribbon2">
					<div class="span4">
					</div>
						<div id="footer_text2" class="span8">
							Government of Kenya &copy; <?php echo date('Y');?>.All Rights Reserved
						</div>
				</footer>
			</div>
		</div>
		</div>
	</body>
</html>
