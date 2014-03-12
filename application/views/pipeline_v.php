<div id="order_frm" class="center-content">
	<div>
		<ul class="breadcrumb">
			<li>
				<a href="<?php echo site_url().'order/pipeline_upload' ?>">Upload</a><span class="divider">/</span>
			</li>
			<li class="active" id="actual_page">
				<?php echo $page_title;?>
			</li>
		</ul>
	</div>
	<div class="row-fluid">
		<div class="span12">
			<?php
			if ($this -> session -> flashdata('order_message')) {
				echo "<div class='alert alert-info'><button type='button' class='close' data-dismiss='alert'>&times;</button>" . $this -> session -> flashdata('order_message') . "</div>";
			}
			?>
			<?php if($this -> session -> flashdata('login_message') !=""){
			?>
			<div class="alert alert-info">
				<button type='button' class='close' data-dismiss='alert'>
					&times;
				</button>
				<?php echo $this -> session -> flashdata('login_message');?>
			</div>
			<?php }?>
		</div>
	</div>
	<div class="row-fluid">
		<div class="span12">
			<div class="row-fluid">
				<div class="span5">
					<h3> Pipeline Upload</h3>
					<?php echo form_open_multipart("order/import_order/pipeline_upload");?>
					<input type="file" name="cms_file" id="cms_file" required="" accept="application/vnd.ms-excel" />
					<div class="control-group">
						<div class="controls">
							<button type="submit" class="btn btn-primary">
								<i class="icon-upload"></i>Upload File
							</button>
						</div>
					</div>
					<?php echo form_close();?>
				</div>
				<div class="span7">
					<h3>Central Medical Store and Pending Orders Template <i><img class="img-rounded" style="height:30px;" src="<?php echo base_url() . 'assets/img/excel.gif';?>"/> </i></h3>
					<?php echo anchor("assets/template/pipeline_upload_template.xls", "<i class='icon-download-alt'></i>Central Medical Store and Pending Orders Template");?>
				</div>
			</div>
		</div>
	</div>
</div>