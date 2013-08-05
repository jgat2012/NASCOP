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