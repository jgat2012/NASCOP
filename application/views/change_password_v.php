<style>
	#fmChangePassword .short {
		color: #FF0000;
	}

	#fmChangePassword .weak {
		color: #E66C2C;
	}

	#fmChangePassword .good {
		color: #2D98F3;
	}

	legend {
		font-size: 22px;
	}
	table tr {
		line-height: 40px;
	}
	label {
		margin-right: 20px;
	}

	#main_wrapper {
		height: auto;
	}
</style>

<script>
	$(document).ready(function() {
		$("#m_error_msg_change_pass").css("display","none");
		$('#m_new_password').keyup(function() {
			$('#m_result').html(checkStrength($('#m_new_password').val()))
		})
		function checkStrength(password) {

			//initial strength
			var strength = 0

			//if the password length is less than 6, return message.
			if (password.length < 6) {
				$('#m_result').removeClass()
				$('#m_result').addClass('short')
				return 'Too short'
			}

			//length is ok, lets continue.

			//if length is 8 characters or more, increase strength value
			if (password.length > 7)
				strength += 1

			//if password contains both lower and uppercase characters, increase strength value
			if (password.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/))
				strength += 1

			//if it has numbers and characters, increase strength value
			if (password.match(/([a-zA-Z])/) && password.match(/([0-9])/))
				strength += 1

			//if it has one special character, increase strength value
			if (password.match(/([!,%,&,@,#,$,^,*,?,_,~])/))
				strength += 1

			//if it has two special characters, increase strength value
			if (password.match(/(.*[!,%,&,@,#,$,^,*,?,_,~].*[!,",%,&,@,#,$,^,*,?,_,~])/))
				strength += 1

			//now we have calculated strength value, we can return messages

			//if value is less than 2
			if (strength < 2) {
				$('#m_result').removeClass()
				$('#m_result').addClass('weak')
				return 'Weak'
			} else if (strength == 2) {
				$('#m_result').removeClass()
				$('#m_result').addClass('good')
				return 'Good'
			} else {
				$('#m_result').removeClass()
				$('#m_result').addClass('strong')
				return 'Strong'
			}
		}


		$("#m_btn_submit_change_pass").click(function(event) {
			var base_url = $("#base_url").val();
			$(".error").css("display", "none");
			$('#m_result_confirm').html("");
			event.preventDefault();
			var old_password = $("#m_old_password").attr("value");
			var new_password = $("#m_new_password").attr("value");
			var new_password_confirm = $("#m_new_password_confirm").attr("value");
	
			if(new_password == "" || new_password_confirm == "" || old_password == "") {
				$(".error").css("display", "block");
				$("#m_error_msg_change_pass").html("All fields are required !");
			} else if(new_password.length < 6) {
				$(".error").css("display", "block");
				$("#m_error_msg_change_pass").html("Your password must have more than 6 characters!");
			} else if($("#m_result").attr("class") == "weak") {
				$(".error").css("display", "block");
				$("#m_error_msg_change_pass").html("Please enter a strong password!");
			} else if(new_password != new_password_confirm) {
				$(".error").css("display", "block");
				$('#m_result_confirm').removeClass();
				$('#m_result_confirm').addClass('short');
				$("#m_error_msg_change_pass").html("You passwords do not match !");
			} else {
				$(".error").css("display", "none");
				$("#loadingDiv").css("display", "block");
				//$("#fmChangePassword").submit();
				var _url = base_url + "user_management/save_new_password";
				var request = $.ajax({
					url : _url,
					type : 'post',
					data : {
						"old_password" : old_password,
						"new_password" : new_password
					},
					dataType : "json"
				});
				request.done(function(data) {
					$("#loadingDiv").css("display", "none");
					$.each(data, function(key, value) {
						if(value == "password_no_exist") {
							$("#m_error_msg_change_pass").css("display", "block");
							$("#m_error_msg_change_pass").html("You entered a wrong password!");
						} else if(value == "password_exist") {
							$("#m_error_msg_change_pass").css("display", "block");
							$("#m_error_msg_change_pass").html("Your new password matches one of your three pevious passwords!");
						} else if(value == "password_changed") {
							$("#m_error_msg_change_pass").css("display", "block");
							$("#m_error_msg_change_pass").removeClass("error");
							$("#m_error_msg_change_pass").addClass("success");
							$("#m_error_msg_change_pass").html("Your password was successfully updated!");
							<?php delete_cookie('actual_page') ?>
							window.setTimeout('location="login"', 3000);
						} else {
							alert(value);
						}
					});
				});
				request.fail(function(jqXHR, textStatus) {
					alert("An error occured while updating your password : " + textStatus + ". Please try again or contact your system administrator!");
				});
			}
		});

	});

</script>

<div class="center-content">
	<form id="fmChangePassword" action="<?php echo base_url().'user_management/save_new_password'?>" method="post" class="well">
	<legend>Change Password</legend>
	<span class="message error" id="m_error_msg_change_pass"></span>
	<div id="loadingDiv" style="display: none"><img style="width: 30px" src="<?php echo base_url().'Images/loading_spin.gif' ?>"></div>
	<br>
	<br>
	<table>
	<tr>
	<td><label >Old Password</label></td><td><input type="password" name="old_password" id="m_old_password" required=""></td>
	</tr>
	<tr>
	<td><label >New Password</label></td><td><input type="password" name="new_password" id="m_new_password" required=""><span id="m_result"></span></td>
	</tr>
	<tr>
	<td><label >Confirm New Password</label></td><td>
	<input type="password" name="new_password_confirm" id="m_new_password_confirm" required="">
	<span id="m_result_confirm"></span></td>
	</tr>
	<tr>
		<td colspan="2">
		<input type="button" class="btn btn_submit_pass" name="register" id="m_btn_submit_change_pass" value=" Submit ">
		</td>
	</tr>
	</table>

	</form>

</div>