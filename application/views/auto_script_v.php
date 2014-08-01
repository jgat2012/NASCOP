<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>NASCOP | AutoScript</title>
		<?php
		 $this -> load -> view('sections/head');
		?>
		<script type="text/javascript">
			 $(document).ready(function(){
			 	var base_url="<?php echo base_url();?>";
		         //run eid_sync to get eid data
		         var link=base_url+"settings/eid_sync";
		         $.ajax({
		         	url:link,
		         	success:function(){
		              $("#eid_data").html("<span class='alert alert-success'>finished</span>");
		         	}
		         });
		         //run monthly_eid_update to send monthly eid updates
		         var link=base_url+"settings/monthly_eid_update";
		         $.ajax({
		         	url:link,
		         	success:function(){
		              $("#eid_email").html("<span class='alert alert-success'>finished</span>");
		         	}
		         });
		         //run get_updates to get kenya pharma data
		         var link=base_url+"settings/get_updates";
		         $.ajax({
		         	url:link,
		         	success:function(){
		              $("#escm_data").html("<span class='alert alert-success'>finished</span>");
		         	}
		         });
			 });
		</script>
    </head>
    <body>
	<div class="container-fluid">
	  <div class="row-fluid">
	      <div class="span12">
	         <div class="table-responsive">
	            <table id="task_data" class="table table-bordered table-striped table-hover ">
	              <thead>
	              <tr>
	               <th>Task</th>
	               <th>Status</th>
	               </tr>
	              </thead>
	              <tbody>
	               <tr>
	               <td>EID Data Download</td>
	               <td id="eid_data"><span class='alert alert-danger'>in progress...</span></td>
	               </tr>
	               <tr>
	               <td>EID Email Updates</td>
	               <td id="eid_email"><span class='alert alert-danger'>in progress...</span></td>
	               </tr>
	               <tr>
	               <td>eSCM Data Download</td>
	               <td id="escm_data"><span class='alert alert-danger'>in progress...</span></td>
	               </tr>
	              </tbody>
	            </table>
	         </div>
	      </div>
	  </div>
	</div>
	</body>
</html>