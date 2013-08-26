<script type="text/javascript">
		$(document).ready(function(){
		
		
		$("#book_type").change(function(){
			
			var selected_value=$(this).attr("value");
			if(selected_value==1){
				$(".show_worksheets").slideDown('slow', function() {

		         });
			}else{
				$(".show_worksheets").slideUp('slow', function() {

		        });
		        $('#test_type>option:eq(0)').attr('selected', true);
			}
			
		});	
				
	var count='<?php echo @$this -> session -> userdata['upload_counter']?>';

		if(count == 2) {
		var message='Data Upload Success!<br/> ';
		var final_message=message;
		$(".passmessage").slideDown('slow', function() {

		});
		$(".passmessage").append(message);

		var fade_out = function() {
		$(".passmessage").fadeOut().empty();
		}
		setTimeout(fade_out, 5000);
	<?php $this -> session -> set_userdata('upload_counter', "0");?>

			}

	if(count == 1) {
	var message='Data for this Period Exists! <br/> ';
	var final_message=message;

	$(".errormessage").slideDown('slow', function() {

	});
	$(".errormessage").append(final_message);

	var fade_out = function() {
	$(".errormessage").fadeOut().empty();
	}
	setTimeout(fade_out, 5000);<?php $this -> session -> set_userdata('upload_counter', "0");?>
		}

		$("#upload_date").datepicker({
			yearRange : "-50:+0",
			dateFormat : $.datepicker.ATOM,
			changeMonth : true,
			maxDate : "+0",
			dateFormat : 'MM-yy',
			changeYear : true,
			onClose : function() {

				var iMonth = $("#ui-datepicker-div .ui-datepicker-month :selected").val();

				var iYear = $("#ui-datepicker-div .ui-datepicker-year :selected").val();

				$(this).datepicker('setDate', new Date(iYear, iMonth, 1));

			},
			beforeShow : function() {

				if(( selDate = $(this).val()).length > 0) {
					iYear = selDate.substring(selDate.length - 4, selDate.length);
					iMonth = jQuery.inArray(selDate.substring(0, selDate.length - 5), $(this).datepicker('option', 'monthNames'));

					$(this).datepicker('option', 'defaultDate', new Date(iYear, iMonth, 1));

					$(this).datepicker('setDate', new Date(iYear, iMonth, 1));

				}

			}
		});

		});
</script>
<style type="text/css">
	.pipeline_import {
		width: 60%;
		border: 1px solid #DDD;
		height: 340px;
		margin-bottom: 10px;
		margin-left: 15%;
		float: left;
		padding: 20px;
		text-align: left;
		background-color: #CCFFFF;
	}
	.import_title {
		text-align: left;
		font-weight: bold;
		margin-bottom: 10px;
	}
	.button {

		margin: 5px;
		height: 40px;
		width: 90px;
	}
	.passmessage {

		display: none;
		background: #00CC33;
		color: black;
		text-align: center;
		height: 20px;
		padding: 5px;
		font: bold 1px;
		border-radius: 8px;
		width: 30%;
		margin-left: 30%;
		margin-right: 10%;
		font-size: 16px;
		font-weight: bold;
	}
	.errormessage {

		display: none;
		background: #C00000;
		color: black;
		text-align: center;
		height: 20px;
		padding: 5px;
		font: bold 1px;
		border-radius: 8px;
		width: 30%;
		margin-left: 30%;
		margin-right: 10%;
		font-size: 16px;
		font-weight: bold;
	}
	legend {
		font-size: 1.4em;
		height:70px;
	}
	h3 {
		font-size: 1.2em;
	}
	.ui-datepicker-calendar {
		display: none;
	}
	
	.show_worksheets{
		display:none;
		width:300px;
		float:right;
	}​
	

</style>
<div id="view_content" class="full-content">
	    <div>
		<ul class="breadcrumb">
		  <li><a href="<?php echo site_url().'home_controller/home' ?>"  id='goHome'><i class="icon-home"></i><strong>Home</strong></a> 
		  	<span class="divider">/</span></li>
		  <li class="active" id="actual_page">Pipeline Upload</li>
		</ul>
	</div>
	<div class="container-fluid" >
		<div class="row-fluid row" >
			<!-- Side bar menus -->
			<?php //echo $this -> load -> view('settings_side_bar_menus_v.php');?>
			<!-- SIde bar menus end -->
			<div class="span9 span-fixed-sidebar" >
				<div class="hero-unit" >
					<div class="passmessage"></div>
					<div class="errormessage"></div>
					<div class="well" style="background:#9CF">
						
						<form name="frm" method="post" enctype="multipart/form-data" id="frm" action="<?php echo base_url()."pipeline_management/upload"?>">
							<legend>
							Pipeline Data Upload 
							
							<select name="book_type" id="book_type" style="width:150px;margin:0 auto;">
								<option value="0">Workbook</option>
								<option value="1">Single Sheet</option>
							</select>
							<label class="show_worksheets">
								<span>Select Worksheet</span>
							<select name="test_type" id="test_type" style="width:300px;float:right;">
								<option value="0">-Select One--</option>
								<option value="1">Current Patients By Regimen</option>
								<option value="2" >Facility Consumption</option>
								<option value="3" >Facility Stock on Hand(SOH)</option>
								<option value="4" >Pipeline Commodity Consumption</option>
								<option value="5" >Patient Scale-Up Trends</option>
							</select>
							</label>
							<p>
					
							</p>
						  </legend>
							
							<b><u><h4>Pipeline</h4></u></b>
							<p>
								<label class="checkbox">
									<input type="radio" name="pipeline_name" value="1" required="required"/>
									<span style="margin-right:20px;">KEMSA</span>
									<input type="radio" name="pipeline_name" value="2" required="required"/>
									KENYA PHARMA </label>
								<b><u><h4>Upload Period</h4></u></b>
							<p>
								<input type="text" name="upload_date" id="upload_date" class="input-xlarge" required="required" />
							</p>
							<b><u><h4>Select File</h4></u></b>
							<p>
								<input type="file"  name="file" size="30" required="required" accept="application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"/>
								<input name="btn_save" class="btn" type="submit"  value="Save" style="padding-left:30px;padding-right: 30px"/>
							</p>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
