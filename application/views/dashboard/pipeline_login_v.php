<div class="row-fluid">
	<div class="span3">
		<?php echo $this -> session -> flashdata('login_message');?>
		<?php echo form_open('user_management/authenticate_pipeline');?>
		<?php echo form_fieldset('', array('id' => 'login_legend'));?>
		<legend id="login_legend">
			<i class="fa fa-info-circle" style="padding-right:5px"></i>Pipeline Log in
		</legend>
		<?php echo $this -> session -> flashdata('error_message');?>
		<div>
			<span class="msg_info"></span>
			<p></p>
		</div>
		<div class="item">
			<?php echo form_error('username', '<div class="error_message">', '</div>');?>
			<?php echo form_label('Username:', 'username');?>
			<div class="input-group">
				<span class="input-group-addon"><i class="fa fa-user"></i></span>
				<?php echo form_input(array('name' => 'username', 'required' => 'required', 'id' => 'p_username', 'size' => '24', 'class' => 'input input-large form-control', 'placeholder' => 'username'));?>
			</div>
		</div>
		<div class="item">
			<?php echo form_error('password', '<div class="error_message">', '</div>');?>
			<?php echo form_label('Password:', 'password');?>
			<div class="input-group">
				<span class="input-group-addon"><i class="fa fa-key"></i></span>
				<?php echo form_password(array('name' => 'password', 'required' => 'required', 'id' => 'p_password', 'size' => '24', 'class' => 'input input-large form-control', 'placeholder' => '********'));?>
			</div>
		</div>
		<div style="margin-top:1em;width:40%">
			<input type="button" class="btn " value="Log in" id="btn_login_pipeline" name="btn_login_pipeline">
			<?php echo form_close();?>
		</div>
	</div>
</div>

<script>
	$(document).ready(function(){
		
		$("#btn_login_pipeline").live("click",function(){
			var username =$.trim($("#p_username").val());
			var password = $.trim($('#p_password').val());
			if(username=="" || password==""){
				$(".msg_info").addClass("message error");
				$(".msg_info").text("All fields are required !");
			}
			
			else{
				var base_url = $("#base_url").val();
				$(".msg_info").removeClass("message error");
				$(".msg_info").show("");
				$(".msg_info").html('<div class="loadingDiv" style="margin:0 auto;" ><img style="width: 30px;" src="<?php echo base_url().'assets/img/loading_spin.gif' ?>"></div>');
				var _url = base_url + "user_management/authenticate_pipeline";
				var request = $.ajax({
					url : _url,
					type : 'post',
					data : {
						"username" : username,
						"password" : password
					},
					dataType : "json"
				});
				request.done(function(data) {
					if(data.invalid){
						$(".msg_info").addClass("message error");
						$(".msg_info").text("Your username and/or password are invalid !");
					}
					else{
						location.reload();
					}
					
				});
				request.fail(function(jqXHR, textStatus) {
					alert("An error occured while authenticating you ! " + textStatus + ". Please try again or contact your system administrator!");
				});
			}
			});
	})
</script>