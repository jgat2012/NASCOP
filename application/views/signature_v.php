<div id="update_signature" class="center-content">
	<div>
		<ul class="breadcrumb">
			<li>
				<a href="<?php echo site_url().'user_management/update_signature' ?>">Users</a><span class="divider">/</span>
			</li>
			<li class="active" id="actual_page">
				<?php echo $page_title;?>
			</li>
		</ul>
	</div>
	<div>
		<?php echo form_open_multipart('user_management/save_signature');?>
		<label>Update Signature(<?php echo $this -> session -> userdata('image_link'); ?>)</label>
		<p>
			<?php echo $this -> session -> flashdata('sign_message');?>
		</p>
		<input type="file" name="userfile" size="20" accept="image/*" required="required" />
		<br />
		<br />
		<input type="submit" value="upload" />
		</form>
	</div>
</div>