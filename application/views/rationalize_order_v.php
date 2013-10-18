
<script>
	$(document).ready(function() {
		
		//Set all input to be readonly
		$("#generate_order").find("input").attr("readonly","readonly");
		$(".research").find("input").attr("readonly","readonly");
		$(".resupply").removeAttr("readonly");
		//$(".research").find("input").attr("readonly","readonly");
		//$("#facility_info").find("input").attr("readonly","readonly");
		
		/*
		$(".accordion").accordion();

		var $research = $('.research');
		$research.find("tr").not('.accordion').hide();
		$research.find("tr").eq(0).show();

		$research.find(".accordion").click(function() {
			$research.find('.accordion').not(this).siblings().fadeOut(500);
			$(this).siblings().fadeToggle(500);
		}).eq(0).trigger('click');
		*/

		$('#accordion_collapse').click(function() {
			if($(this).val() == "+") {
				var $research = $('.research');
				$research.find("tr").show();
				$('#accordion_collapse').val("-");
			}else{
				var $research = $('.research');
				$research.find("tr").not('.accordion').hide();
				$research.find("tr").eq(0).show();
				$('#accordion_collapse').val("+");
			}
             
		});
		
		$("#reporting_period").datepicker({
			yearRange : "-120:+0",
			maxDate : "0D",
			changeMonth: true,
	        changeYear: true,
	        showButtonPanel: true,
	        dateFormat: 'MM-yy',
        	onClose: function(dateText, inst) { 
	            var month = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
	            var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
	            
	            month=parseInt(month);
	            var last_day_month=LastDayOfMonth(year,month+1);
	            
	            $("#period_start_date").val("01");
	            $("#period_end_date").val(last_day_month);
	            $(this).datepicker('setDate', new Date(year, month, 1));
	        }
		});
		function LastDayOfMonth(Year, Month){
		    return(new Date((new Date(Year, Month,1))-1)).getDate();
		}
		
		//Validate order before submitting
		$("#approve_order").click(function(){
			if($(".label-warning").is(':visible')){
				alert("Some drugs have a negative resupply quantity !");
			}
			else{
				$("#transaction_type").attr("value",'approved')
				$("#fmEditOrder").submit();
			}
		});
		$("#decline_order").click(function(){
			if($(".label-warning").is(':visible')){
				alert("Some drugs have a negative resupply quantity !");
			}
			else{
				$("#transaction_type").attr("value",'declined')
				$("#fmEditOrder").submit();
			}
		});
		
		$(".pack_size").live('change',function() {
			calculateResupply($(this));
		});
		$(".opening_balance").live('change',function() {
			calculateResupply($(this));
		});
		$(".quantity_received").live('change',function() {
			calculateResupply($(this));
		});
		$(".quantity_dispensed").live('change',function() {
			calculateResupply($(this));
		});
		$(".losses").live('change',function() {
			calculateResupply($(this));
		});
		$(".adjustments").live('change',function() {
			calculateResupply($(this));
		});
		$(".physical_count").live('change',function() {
			calculateResupply($(this));
		});
	});
	function calculateResupply(element) { 
		var row_element = element.closest("tr");
		var opening_balance = parseInt(row_element.find(".opening_balance").attr("value"));
		var quantity_received = parseInt(row_element.find(".quantity_received").attr("value"));
		var quantity_dispensed = parseInt(row_element.find(".quantity_dispensed").attr("value")); 
		var adjustments = parseInt(row_element.find(".adjustments").attr("value"));
		var physical_count = parseInt(row_element.find(".physical_count").attr("value"));
		var resupply = 0;
		if(!(opening_balance + 0)) {
			opening_balance = 0;
		}
		if(!(quantity_received + 0)) {
			quantity_received = 0;
		}
		if(!(quantity_dispensed + 0)) {
			quantity_dispensed = 0;
		} 

		if(!(adjustments + 0)) {
			adjustments = 0;
		}
		if(!(physical_count + 0)) {
			physical_count = 0;
		} 
		calculated_physical = (opening_balance + quantity_received - quantity_dispensed + adjustments);
		//console.log(calculated_physical);
		if(element.attr("class") == "physical_count") {
		 resupply = 0 - physical_count;
		 } else {
		 resupply = 0 - calculated_physical;
		 physical_count = calculated_physical;
		 }
		resupply = (quantity_dispensed * 3) - physical_count;
		resupply=parseInt(resupply);
		row_element.find('.label-warning').remove();
		if(resupply<0){
			row_element.find('.col_drug').append("<span class='label label-warning' style='display:block'>Warning! Resupply qty cannot be negative</<span>");
			row_element.find(".resupply").css("background-color","#f89406");
		}
		else{
			row_element.find(".resupply").css("background-color","#fff");
		}
		row_element.find(".physical_count").attr("value", physical_count);
		row_element.find(".resupply").attr("value", resupply);
	}
</script>
<div class="full-content" style='background:#9CF'>
		<div >
		<ul class="breadcrumb">
			<li>
				<a href="<?php echo site_url().'order_rationalization' ?>">Orders</a><span class="divider">/</span>
			</li>
			<li class="active" id="actual_page">
				Details for Order No: <?php echo $order_no;?>
			</li>
		</ul>
	</div>	
<form method="post" id="fmEditOrder" action="<?php echo site_url('order_rationalization/save')?>">
	<input type="hidden" id="transaction_type" name="transaction_type" >
	<input type="hidden" name="order_number" value="<?php echo $order_details->id;?>" />
	<div class="facility_info" class="header section">
		 <table class="table dataTable" >
			<tbody>
				<tr>
					<th>Order No</th>
					<td><span class="_green">
					<?php 
					$order_types = array(0=>"Central Order",1=>"Aggregated Order",2=>"Satellite Order"); 
					echo $order_no."(".@$order_types[$order_details->Code].")";?></span></td>
					<th width="160px">Facility code:</th>
					<td><span class="_green"><?php echo $order_details -> Facility_Object -> facilitycode;?></span></td>
					</tr>
				<tr>
					<th width="140px">Facility Name:</th>
					<td><span class="_green"><?php echo $order_details -> Facility_Object -> name;?></span></td>
					
				
					<th>Facility Type:</th>
					<td><span class="_green"><?php echo $order_details -> Facility_Object -> Type -> Name;?></span></td>
					</tr>
				<tr>
					<th>District / County:</th>
					<td><span class="_green"><?php echo $order_details -> Facility_Object -> Parent_District -> Name;?> / <?php echo $order_details -> Facility_Object -> County -> county;?></span></td>
					<th>Reporting Period : </th>
					<td colspan="3"><input name="reporting_period" id="reporting_period" type="text" placeholder="Click here to select period" value="<?php echo date('F-Y',strtotime($order_details->Period_Begin)); ?>" disabled="disabled"/></td>
					<input name="start_date" id="period_start_date" type="hidden" value="<?php echo $order_details->Period_Begin;?>">
					<input name="end_date" id="period_end_date" type="hidden" value="<?php echo $order_details->Period_End;?>">
					<input name="unique_id" id="unique_id" type="hidden" value="<?php echo $order_details->Unique_Id;?>">
					</td>
				</tr>
				
			</tbody>
		</table>
	</div>
	<?php
	$header_text = '<thead>
<tr>
<!-- label row -->
<th class="col_drug" rowspan="2">Drug Name</th>

<th class="number">Beginning Balance</th>
<th class="number">Quantity Received in this period</th>

<!-- dispensed_units -->
<th class="col_dispensed_units">Total Quantity Dispensed this period</th>
<!-- dispensed_packs -->

<th class="col_adjustments">Adjustments (Borrowed from or Issued out to Other Facilities)</th>
<th class="number">End of Month Physical Count</th>

<!-- aggr_consumed/on_hand -->
<th class="number">Quantity required for Resupply</th>
</tr>
<tr>
<!-- unit row -->
<th>In Packs</th> <!-- balance -->
<th>In Packs</th> <!-- received -->

<!-- dispensed_units -->
<th class="col_dispensed_units">In Packs</th>
<!-- dispensed_packs -->

<th>In Packs</th> <!-- adjustments -->
<th>In Packs</th> <!-- count -->

<!-- aggr_consumed/on_hand -->

<th>In Packs</th> <!-- resupply -->
</tr>
<tr>
<!-- letter row -->
<th></th> <!-- drug name -->
<th>A</th> <!-- balance -->
<th>B</th> <!-- received -->
<th>C</th> <!-- dispensed_units/packs -->
<th>D</th> <!-- losses -->
<th>E</th> <!-- adjustments -->
<th>F</th> <!-- count -->

<!-- aggr_consumed/on_hand -->

</tr>
</thead>';
	?>
<div id="commodity-table">
	<table class="table table-bordered table_order_details dataTables" id="generate_order">
		<?php echo $header_text;?>
		<tbody>
			<?php
			foreach($commodities as $commodity){
			?>
			<tr class="ordered_drugs" drug_id="<?php echo $commodity -> Drugcode_Object->id;?>">
				<td class="col_drug"><?php echo $commodity ->Drug_Id;?></td>
				<td class="number calc_count">
				<input name="opening_balance[]" id="opening_balance_<?php echo $commodity -> id;?>" type="text" class="opening_balance" value="<?php echo $commodity -> Balance;?>">
				</td>
				<td class="number calc_count">
				<input name="quantity_received[]" id="received_in_period_<?php echo $commodity -> id;?>" type="text" class="quantity_received" value="<?php echo $commodity -> Received;?>">
				</td>
				<!-- dispensed_units-->
				<td class="number col_dispensed_units calc_dispensed_packs  calc_resupply calc_count">
				<input name="quantity_dispensed[]" id="dispensed_in_period_<?php echo $commodity -> id;?>" type="text" class="quantity_dispensed" value="<?php echo $commodity -> Dispensed_Units;?>">
				</td>
				<td class="number calc_count">
				<input name="adjustments[]" id="CdrrItem_10_adjustments" type="text" class="adjustments" value="<?php echo $commodity -> Adjustments;?>">
				</td>
				<td class="number calc_resupply col_count">
				<input tabindex="-1" name="physical_count[]" id="CdrrItem_10_count" type="text" class="physical_count" value="<?php echo $commodity -> Count;?>">
				</td>
				<!-- aggregate -->
				<td class="number col_resupply">
				<input tabindex="-1" name="newresupply[]" id="CdrrItem_10_newresupply" type="text" class="resupply" value="<?php echo $commodity -> Resupply;?>">
				<input tabindex="-1" name="resupply[]" id="CdrrItem_10_resupply" type="hidden" class="resupply" value="<?php echo $commodity -> Resupply;?>">
				</td>
				<input type="hidden" name="commodity[]" value="<?php echo $commodity -> id;?>"/>
			</tr>
			<?php }?>
		</tbody>
	</table>
	<br />
	<hr size="1">
	
	<div class='comments'>
	<?php 
	$has_comment=0;
	foreach($comments as $comment){
		$has_comment=1;
		?>
	    
		<span class="label" style="vertical-align: bottom;background:#999;">Comment </span>
		<textarea style="width:98%" rows="3" name="o_comments" disabled="disbled"><?php echo $comment->Comment ?></textarea>
		<table class="table table-bordered">
			<thead>
				<tr><th>Last Update</th><th>Accessed By</th><th>Access Level</th></tr>
			</thead>
			<tbody>
				<tr><td><span class="green"><?php echo date('l d-M-Y h:i:s a', $comment -> Timestamp);?></span></td><td><span class="green"><?php if($comment -> User_Object -> Name){echo $comment -> User_Object -> Name;}else{echo $comment->User;}?></span></td><td><span class="green"><?php  if($comment -> User_Object -> Access -> Level_Name){echo $comment -> User_Object -> Access -> Level_Name;}else{ echo "Facility Administrator";}?></span></td></tr>
			</tbody>
		</table>
		
	<?php } 
	if($has_comment==0){
	?>
		<span class="label" style="vertical-align: bottom"> Add Comment </span>
		<textarea style="width:98%" rows="3" name="comments"></textarea>
		<textarea style="display:none;" rows="3" name="o_comments"></textarea>
	<?php	
	}else{
	?>
	  <span class="label" style="vertical-align: bottom;background:#999;">Comment </span>
		<textarea style="width:98%" rows="3" name="comments" ></textarea>
		<table class="table table-bordered">
			<thead>
				<tr><th>Last Update</th><th>Accessed By</th><th>Access Level</th></tr>
			</thead>
			<tbody>
				<tr><td><span class="green"><?php echo date('l d-M-Y h:i:s a');?></span></td><td><span class="green"><?php echo $this->session->userdata("full_name");?></td><td><span class="green"><?php echo "Nascop Pharmacist";  ?></td></tr>
			</tbody>
		</table>
	<?php }?>
	<input type="button" class="save_changes btn green" id="approve_order" value="Approve Order" name="approve_order" />
	<input type="button" class="save_changes btn red" id="decline_order" value="Decline Order" name="decline_order"/>
	</div>
</div>
	<table class=" table table-bordered regimen-table big-table research">
		<thead>
			<tr>
				<th class="col_drug" colspan="2"> Regimen </th>
				<th><input type="button" id="accordion_collapse" value="-"/></span>Patients<span></th>
			</tr>
		</thead>
		<tbody>
			<?php
			$counter = 1;
			foreach($regimens as $regimen){

				?>
				<tr>
				<td colspan="2" regimen_id="<?php echo $regimen -> id;?>" class="regimen_desc col_drug"><?php echo $regimen ->Regimen_Id;?></td>
				<td regimen_id="<?php echo $regimen -> id;?>" class="regimen_numbers">
					<input name="patient_numbers[]" id="patient_numbers_<?php echo $regimen ->id;?>" type="text" value="<?php echo $regimen ->Total;?>">
					<input name="patient_regimens[]" value="<?php echo $regimen -> id;?>" type="hidden">
				</td>	 
			</tr>
			<?php
			}
			?> 
		</tbody>
	</table> 
		
</form>
</div>