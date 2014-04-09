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
	<div class="container">
		<div class="span10">
			<div class="row-fluid">
					
					
					<div class="span12">
						<a class="btn btn-success" href="#twopager_upload" data-toggle="modal"> Upload 2 Pager</a>
						<h3> Kenya Anti-Retroviral medicines (ARVs) Stock Situation</h3>
						<table class="table table-bordered table-striped tbl_nat_dashboard">
			    			<thead>
			    				<tr><th>No</th><th> Period</th><th>Action</th></tr>
			    			</thead>
			    			<tbody>
			    				<?php
			    				$x=1;
			    				foreach ($files as $value) {
									echo '<tr><td>'.$x.'</td><td>Stock Situation '.$value['period'].'</td><td><a href="'.base_url().'order/twopager_upload/delete/'.$value['id'].'">Delete</a></td></tr>';
									$x++;
								}
			    				?>
			    				
			    			</tbody>
			    		</table>
					</div>
			</div>
		</div>
	</div>
	
</div>
<div id="twopager_upload" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<?php echo form_open_multipart("order/twopager_upload/upload");?>
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
				×
			</button>
			<h4 id="myModalLabel">Kenya Anti-Retroviral medicines (ARVs) Stock Situation</h4>
		</div>
		<div class="modal-body">
				<p>
					<select name="period_selected" id="period_selected">
						<option value="0">-- Select Period --</option>
						<?php
							$x=0;
							while ($x<12) {
								$previous = date('F-Y', strtotime(date('F-Y') . "-".$x." month"));
								echo '<option value="'.$previous.'">'.$previous.'</option>';
								$x++;
							}
							
						?>
						
					</select>
				</p>
				
				<input type="file" name="cms_file" id="cms_file" required="" accept="application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/msword" />
				<div class="control-group">
					<div class="controls">
						<button type="submit" class="btn btn-primary">
							<i class="icon-upload"></i>Upload File
						</button>
					</div>
				</div>
		</div>
	<?php echo form_close();?>
</div>

					

<script>
	$(document).ready(function(){
		$(".tbl_nat_dashboard").dataTable({
		 		 "bJQueryUI" : true,
				"sPaginationType" : "full_numbers",
				"sDom" : '<"H"Tfr>t<"F"ip>',
				"oTableTools" : {
					"sSwfPath" : base_url + "scripts/datatable/copy_csv_xls_pdf.swf",
					"aButtons" : ["copy", "print", "xls", "pdf"]
				},
				"bProcessing" : true,
				"bServerSide" : false,
		 });
	})
</script>