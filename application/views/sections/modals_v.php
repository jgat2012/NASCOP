<!-- Modal edit user profile-->
<div id="edit_user_profile" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<form action="<?php echo base_url().'user_management/profile_update' ?>" method="post">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
				×
			</button>
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
			<button class="btn" data-dismiss="modal" aria-hidden="true">
				Cancel
			</button>
			<input type="submit" class="btn btn-primary" value="Save changes">
		</div>
	</form>
</div>
<!-- Modal edit user profile end-->
<!-- Modal edit change password-->
<div id="user_change_pass" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<form action="<?php echo base_url().'user_management/profile_update' ?>" method="post">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
				×
			</button>
			<h3 id="myModalLabel">Change password</h3>
		</div>
		<div class="modal-body">
			<input type="hidden" name="base_url" id="base_url" value="<?php echo base_url() ?>" />
			<form id="fmChangePassword" action="<?php echo base_url().'user_management/save_new_password'?>" method="post" class="well">
				<span class="message error" id="error_msg_change_pass"></span>
				<br>
				<table>
					<tr>
						<td><label >Old Password</label></td><td>
						<input type="password" name="old_password" id="old_password" required="">
						</td>
					</tr>
					<tr>
						<td><label >New Password</label></td><td>
						<input type="password" name="new_password" id="new_password" required="">
						<span id="result"></span></td>
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
			<button class="btn" data-dismiss="modal" aria-hidden="true">
				Cancel
			</button>
			<input type="button" class="btn btn-primary" name="btn_submit_change_pass" id="btn_submit_change_pass" value="Save changes">
		</div>
	</form>
</div>
<!-- Modal edit change password end-->

<!-- Modal Historical Reports upload password-->
<div id="historical_upload" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<form action="<?php echo base_url().'order/historical_upload' ?>" method="post" enctype="multipart/form-data">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
				×
			</button>
			<h3 id="myModalLabel">Historical Reports Uploads</h3>
		</div>
		<div class="modal-body">
			<div class="form-group">
			     <label for="report_pipeline">Please Select a Pipeline</label>
			     <select name="report_pipeline" id="report_pipeline" required="required">
			     	<option value="kemsa">Kemsa</option>
			     	<option value="kenya_pharma">Kenya Pharma</option>
			     </select>
			</div>
		    <div class="form-group">
		      <label class="alert alert-info" >Please Select workbook(s) to upload. Kindly make sure that the file(s) your are uploading is in 1997-2003 excel format(e.g: example.xls)</label>
		      <input type="file"  name="file[]" size="30" multiple="multiple"  required="required" accept="application/vnd.ms-excel"/>
		    </div>
			  
		</div>
		<div class="modal-footer">
			<button class="btn" data-dismiss="modal" aria-hidden="true">
				Cancel
			</button>
			<input type="submit" class="btn btn-primary" name="btn_submit_change_pass" id="btn_submit_change_pass" value="Save ">
		</div>
	</form>
</div>
<!-- Modal Historical Reports upload end-->

<!-- Modal Historical Reports upload password-->
<div id="guideline_upload" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<form action="<?php echo base_url().'admin_management/guidelineUpload' ?>" method="post" enctype="multipart/form-data">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
				×
			</button>
			<h3 id="myModalLabel">Guidelines Uploads</h3>
		</div>
		<div class="modal-body">
		    <div class="form-group">
		      <label class="alert alert-info" >Please Select guidelines to upload (word,excel,pdf). You can select more than one file</label>
		      <input type="file"  name="files[]" size="30" multiple="multiple"  required="required" accept=".xls, .xlsx, .pdf, .doc, .docx"/>
		    </div>
			  
		</div>
		<div class="modal-footer">
			<button class="btn" data-dismiss="modal" aria-hidden="true">
				Cancel
			</button>
			<input type="submit" class="btn btn-primary" name="btn_submit" id="btn_submit" value="Save ">
		</div>
	</form>
</div>
<!-- Modal Historical Reports upload end-->

