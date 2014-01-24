<style>
	.facility_info {
	width:100%;
	background:#FFF;
	margin-bottom:1em;
	}
	#commodity-table{
		width:63%;
	}
	.regimen-table {
	   width: 35%;   
    }
    .breadcrumb{
    	margin: 0 0 10px;
    }
    .table td {
    	padding:4px;
    }
</style>
<div class="full-content" style="width:98%;">
	<div>
		<ul class="breadcrumb">
			<li>
				<a href="<?php echo site_url().'order' ?>">CDRR</a><span class="divider">/</span>
			</li>
			<li class="active" id="actual_page">
				<?php echo $page_title;?>
			</li>
		</ul>
	</div>
	<?php 
	if($options=="update"){
	?>
	<form method="post" action="<?php echo site_url('order/update/cdrr/'.$cdrr_id)?>">	
	<?php	
	}else{
	?>
	<form method="post" action="<?php echo site_url('order/save/cdrr')?>">
	<?php
	}
	?>
	<div class="facility_info">
		<?php 
		 if($options=="view"){
		?>
		<ul class="nav nav-tabs">
         <?php echo $option_links; ?>
        </ul>	
		<h4><b><?php echo $cdrr_array[0]['cdrr_label']." ".$cdrr_array[0]['status_name'];?></b></h4>
		<a href='<?php echo site_url("order/download_cdrr/".$cdrr_id);?>'><?php echo $cdrr_array[0]['cdrr_label']." ".$cdrr_array[0]['facility_name']." ".$cdrr_array[0]['period_begin']." to ".$cdrr_array[0]['period_end'].".xls";?></a>
		<p></p>
		<?php 
		   $access_level = $this -> session -> userdata("user_indicator");
		      if($access_level=="facility_administrator"){
		      	if($status_name=="prepared"){
		?>
				<a href='<?php echo site_url("order/approve_cdrr/".$cdrr_id) ?>' class='btn'><b>Approve</b></a>
		<?php
		      } else if($status_name=="approved"){
		 ?>
		 		<a href='<?php echo site_url("order/archive_cdrr/".$cdrr_id) ?>' class='btn'><b>Archive</b></a>
		 <?php
		      }
		  ?>
		  <br/>	    	
		  <?php 
	if($this->session->flashdata('order_delete')){
		
		echo '<p class="message error">'.$this->session->flashdata('order_delete').'</p>';
	}
	else if ($this->session->flashdata('order_message')){
		echo '<p class="message info">'.$this->session->flashdata('order_message').'</p>';
	}	
	?>
		<?php
			  }
		?>	     
		<?php	
		 }else if($options=="update"){
		 ?>
		 <ul class="nav nav-tabs">
         <?php echo $option_links; ?>
        </ul>
		<h4><b>Update <?php echo $cdrr_array[0]['cdrr_label']." ".$cdrr_array[0]['status_name'];?></b></h4>
		 <?php	
		 }
		?>
		
			<input type="hidden" name="report_type" value="<?php echo $report_type;?>"/>
			<table cellpadding="5" border="1" width="100%" style="border:1px solid #DDD;">
				<thead>
					<tr>
						<td colspan='2' align="center"><b>
						<?php 
						if($hide_generate==2){
						?>
						CENTRAL SITE / DISTRICT STORE CONSUMPTION DATA REPORT AND REQUEST (D-CDRR) FOR ANTIRETROVIRAL AND OPPORTUNISTIC INFECTION MEDICINES
						<?php 
						}else{
						?>
						FACILITY CONSUMPTION DATA REPORT AND REQUEST (F-CDRR) FOR ANTIRETROVIRAL AND OPPORTUNISTIC INFECTION MEDICINES
						<?php 
						}
						?>
						</b></td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td style="width:50%;"><b>Facility Name: &nbsp;</b><?php echo $facility_object -> name;?></td>
						<td><b>Facility code: &nbsp;</b><?php echo $facility_object -> facilitycode;?>
							<input type="hidden" name="facility_code" value="<?php echo $facility_object ->facilitycode; ?>"/>
							<input type="hidden" name="facility_id" value="<?php echo $facility_id; ?>"/>
						</td>
					</tr>
					<tr>
						<td><b>County: &nbsp;</b><?php echo $facility_object -> County -> county;?></td>
						<td><b>District: &nbsp;</b><?php echo $facility_object -> Parent_District -> Name;?></td>
					</tr>
					<tr>
						<td colspan='2'><b>Programme Sponsor: &nbsp;</b><?php echo $facility_object -> supplier -> name;?>
							<input type="hidden" name="sponsor" value="<?php echo $facility_object -> supplier -> name; ?>"/>
						</td>
					</tr>
					<tr>
						<td><b>Type of Service provided at the Facility: &nbsp; </b><?php
						$type_of_service = array();
						if ($facility_object -> service_art == "1") {
							$type_of_service[] = "ART";
						}
						if ($facility_object -> service_pmtct == "1") {
							$type_of_service[] = "PMTCT";
						}
						if ($facility_object -> service_pep == "1") {
							$type_of_service[] = "PEP";
						}
						echo implode(",", $type_of_service);
						?><input type="hidden" name="type_of_service" value="<?php echo implode(",", $type_of_service); ?>"/></td>
						<td><b>Non-ARV: &nbsp;</b>
						<input type="checkbox" name="non_arv" id="non_arv" value="0"/>
						</td>
					</tr>
					<tr>
						<td colspan='2'><b>Period of Reporting: &nbsp;</b>
						<select readonly="readonly" name="period_start" id="period_start">
							<option selected="selected" value="<?php echo date('Y-m-01'); ?>"><?php echo date('F');?></option>
						</select>
						<select readonly="readonly" name="period_end" id="period_end">
							<option selected="selected" value="<?php echo date('Y-m-t'); ?>"><?php echo date('Y');?></option>
						</select>
						<?php 
						if($hide_generate==0 && $hide_btn==0){
						?>
						<input type="button" style="width:auto" name="generate" id="generate" class="btn" value="Generate Report" >
						<?php 
						}else if($hide_generate==2 && $hide_btn==0){
						?>
					    <input type="button" style="width:auto" name="generate" id="generate" class="btn" value="Update Aggregated Data" >
						<?php
						}
						?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div>
			<table class="table table-bordered" style="font-size:15px;background:#FFF;" id="generate_order">
	<?php
	if($hide_generate==2){
		$header_text = '<thead style="text-align:left;background:#c3d9ff;">
					<tr>
						<th class="col_drug" rowspan="3">Drug Name</th>
						<th class="number" rowspan="3">Unit Pack Size</th>
						<th class="number">Beginning Balance</th>
						<th class="number">Quantity <br/>Received in this period</th>
						<th class="col_dispensed_units">Total Quantity ISSUED to <br/> ARV dispensing sites <br/>(Satellite sites plus<br/>Central site dispensing <br/>point(s) where relevant)</th>
						<th class="col_losses_units">Losses<br/>(Damages, Expiries, Missing)</th>
						<th class="col_adjustments">Adjustments (Borrowed from <br/>or Issued out to Other Facilities)</th>
						<th class="number">End of Month Physical Count</th>
						<th class="number">Reported Aggregated Quantity CONSUMED in the reporting period (Satellite sites plus Central site dispensing point where relevant)</th>
						<th class="number">Reported Aggregated Physical Stock on Hand at end of reporting period (Satellite sites plus Central site dispensing point where relevant)</th>
						<th class="number" colspan="2">Drugs with less than 6 months to expiry(Central Site/District Store)</th>
						<th class="number">Days out of stock this Month</th>
						<th class="number">Quantity required for RESUPPLY</th>
					</tr>
					<tr>
						<th>In Packs</th>
						<th>In Packs</th>
						<th class="col_dispensed_units">In Packs</th>
						<th class="col_dispensed_units">In Packs</th>
						<th>In Packs</th>
						<th>In Packs</th>
						<th>In Packs</th>
						<th>In Packs</th>
						<th>Quantity</th>
						<th>Expiry Date</th>
						<th></th>
						<th>In Packs</th>
					</tr>
					<tr>
						<th>A</th>
						<th>B</th>
						<th>C</th>
						<th>D</th>
						<th>E</th>
						<th>F</th>
						<th>G</th>
						<th>H</th>
						<th>In Packs</th>
						<th>mm-yy</th>
						<th>I</th>
						<th>J</th>
					</tr>
			</thead>';
	}
			else{$header_text = '<thead style="text-align:left;background:#c3d9ff;">
					<tr>
						<th class="col_drug" rowspan="3">Drug Name</th>
						<th class="number" rowspan="3">Basic Unit</th>
						<th class="number">Beginning Balance</th>
						<th class="number">Quantity <br/>Received in this period</th>
						<th class="col_dispensed_units">Total Quantity Dispensed <br/>this period</th>
						<th class="col_losses_units">Losses <br/>(Damages, Expiries, Missing)</th>
						<th class="col_adjustments">Adjustments (Borrowed from <br/>or Issued out to Other Facilities)</th>
						<th class="number">End of Month Physical Count</th>
						<th class="number" colspan="2">Drugs with less than 6 months to expiry</th>
						<th class="number">Days out of stock this Month</th>
						<th class="number">Quantity required for RESUPPLY</th>
					</tr>
					<tr>
						<th>In Units</th>
						<th>In Units</th>
						<th class="col_dispensed_units">In Units</th>
						<th class="col_dispensed_units">In Units</th>
						<th>In Units</th>
						<th>In Units</th>
						<th>Quantity</th>
						<th>Expiry Date</th>
						<th></th>
						<th>In Units</th>
					</tr>
					<tr>
						<th>A</th>
						<th>B</th>
						<th>C</th>
						<th>D</th>
						<th>E</th>
						<th>F</th>
						<th>In Units</th>
						<th>mm-yy</th>
						<th>G</th>
						<th>H</th>
					</tr>
			</thead>';}echo $header_text;?>
				<tbody>					<?php 
					$counter =-1;
					$count_one=0;
					$count_two=0;
					$count_three=0;
					foreach($commodities as $commodity){
				         if($commodity -> Drug !=NULL){
				         	$counter++;
			                   if($counter ==10){
			                   echo $header_text;
			                   $counter = 0;
			                   }
						  if($hide_generate==2){
						  if($commodity->Category==1 && $count_one==0){
						  	  echo '<tr><td colspan="14" style="text-align:center;background:#999;">Adult Preparations</td></tr>';
							  $count_one++;
						  }	   
						  if($commodity->Category==2 && $count_two==0){
						  	  echo '<tr><td colspan="14" style="text-align:center;background:#999;">Pediatric Preparations</td></tr>';
							  $count_two++;
						  }
                          if($commodity->Category==3 && $count_three==0){
						  	  echo '<tr><td colspan="14" style="text-align:center;background:#999;">Drugs for OIs</td></tr>';
							  $count_three++;
						  }
						  }else{
						  if($commodity->Category==1 && $count_one==0){
						  	  echo '<tr><td colspan="13" style="text-align:center;background:#999;">Adult Preparations</td></tr>';
							  $count_one++;
						  }	   
						  if($commodity->Category==2 && $count_two==0){
						  	  echo '<tr><td colspan="13" style="text-align:center;background:#999;">Pediatric Preparations</td></tr>';
							  $count_two++;
						  }
                          if($commodity->Category==3 && $count_three==0){
						  	  echo '<tr><td colspan="13" style="text-align:center;background:#999;">Drugs for OIs</td></tr>';
							  $count_three++;
						  }
						  }
					?>
					<tr class="ordered_drugs" drug_id="<?php echo $commodity -> id;?>">
						<td class="col_drug"><?php echo $commodity -> Drug;?>
							<input type="hidden" name="pack_size[]" id="pack_size_<?php echo $commodity -> id;?>" value="<?php echo $commodity ->Pack_Size;?>"/>
						</td>
						<?php
	                    if($hide_generate==2){
	                    ?>
						<td class="number calc_count"><?php echo $commodity ->Pack_Size;?></td>
						<?php
						}else{
						?>
						<td class="number calc_count"><?php if($options=="view"){echo $commodity->Unit_Name;}else{echo $commodity->Drug_Unit->Name;}?></td>
						<?php	
						}
						?>
						<td> <input name="opening_balance[]" id="opening_balance_<?php echo $commodity -> id;?>" type="text" class="opening_balance"/></td>
						<td> <input name="quantity_received[]" id="received_in_period_<?php echo $commodity -> id;?>" type="text" class="quantity_received"/></td>
						<td> <input name="quantity_dispensed[]" id="dispensed_in_period_<?php echo $commodity -> id;?>" type="text" class="quantity_dispensed"/></td>
						<td> <input name="losses[]" id="losses_in_period_<?php echo $commodity->id;?>" type="text" class="losses"/></td>
						<td> <input name="adjustments[]" id="adjustments_in_period_<?php echo $commodity->id;?>" type="text" class="adjustments"/></td>
						<td> <input tabindex="-1" name="physical_count[]" id="physical_in_period_<?php echo $commodity->id;?>" type="text" class="physical_count"/></td>
						<?php
	                    if($hide_generate==2){
	                    ?>
						<td> <input tabindex="-1" name="aggregated_qty[]" id="aggregated_qty_<?php echo $commodity->id;?>" type="text" class="aggregated_qty"/></td>
						<td> <input tabindex="-1" name="aggregated_physical_qty[]" id="aggregated_physical_qty_<?php echo $commodity->id;?>" type="text" class="aggregated_physical_qty"/></td>
						<?php
						}
						?>
						<td> <input tabindex="-1" name="expire_qty[]" id="expire_qty_<?php echo $commodity -> id;?>" type="text" class="expire_qty"/></td>
						<td> <input tabindex="-1" name="expire_period[]" id="expire_period_<?php echo $commodity -> id;?>" type="text" class="expire_period"/></td>	
						<td> <input tabindex="-1" name="out_of_stock[]" id="out_of_stock_<?php echo $commodity -> id;?>" type="text" class="out_of_stock"/></td>
						<td> 
							<input tabindex="-1" name="old_resupply[]" id="old_resupply_<?php echo $commodity -> id;?>" type="hidden" class="resupply"/>
							<input tabindex="-1" name="resupply[]" id="resupply_<?php echo $commodity -> id;?>" type="text" class="resupply"/>
						</td>	
						<input type="hidden" name="commodity[]" value="<?php echo $commodity -> id;?>"/>					
					</tr>					
					<?php 
						  }
						 }
					?>
				</tbody>
			</table>
		</div>
		<div>
			<span style="vertical-align:bottom;font-size:1.2em;">Comments (Explain ALL Losses and Adjustments):</span>
			<textarea style="width:100%" rows="8" name="comments" id="comments"></textarea>
			<?php
	           if($hide_generate==2){
	        ?>
			<table border="0" cellpadding="5" style="padding:10px;" class="table-bordered ">
				<tr>
					<td><b>Central site Reporting rate:-</b> </td>
					<td><b>Total No. of Facility Reports Expected:</b><br/>(Total number of Satellite sites plus the Central site Dispensing point)</td>
					<td><input type="text" name="central_rate"  id="central_rate"/></td>
					<td>Actual No. of Facility Reports Received:</td>
					<td><input type="text" name="actual_report" id="actual_report"/></td>
				</tr>
			</table>
			<?php 
				 }if($options=="view" || $options=="update"){
		    ?>
		    <table style="width:100%;" class="table ">
		    	<tr ><td colspan="4"  maxlength="255">
		    		<b>Delivery Note</b>
		            <input type='text' name='delivery_note' id='delivery_note' style="width:100%;"/>
		    	</td></tr>
		    	<?php foreach($logs as $log){
		    		if($log->description =="approved"){
		    		?>
				<tr>
					<td><b>Report <?php echo $log->description;?> by:</b> </td>
					<td><?php echo $log->user->name; ?></td>
					<td><b>Designation:</b></td>
					<td><?php echo $log->user->role; ?></td>
				</tr>
				<tr>
					<td><b>Contact Telephone:</b></td>
					<td><?php echo $log->user->profile_id; ?></td>
					<td><b>Date:</b></td>
					<td><?php echo $log->created; ?></td>
				</tr>
				<?php }else{
				?>	
				<tr>
					<td><b>Report <?php echo $log->description;?> by:</b> 
					</td>
					<td><?php echo $log->n_user->Name; ?></td>
					<td><b>Designation:</b></td>
					<td><?php echo $log->n_user->Access->Level_Name; ?></td>
				</tr>
				<tr>
					<td><b>Contact Telephone:</b></td>
					<td><?php echo $log->n_user->Phone_Number; ?></td>
					<td><b>Date:</b></td>
					<td><?php echo $log->created; ?></td>
				</tr>
					
				<?php	
				}}?>
			</table>
		    <?php		 	
				 }
			?>
			<?php
                if($hide_save==0){
				?>
			<input type="submit" class="btn btn-info" name="save" value="Save"/>
			<?php
				}
			?>
		</div>
	</form>
</div>
<script type="text/javascript">
	$(document).ready(function(){
		$("#generate").click(function() {
			$.blockUI({ message: '<h3><img width="30" height="30" src="<?php echo asset_url().'images/loading_spin.gif' ?>" /> Generating...</h3>' }); 
            var period_start = $("#period_start").attr("value");
			var period_end = $("#period_end").attr("value");
			var count = 0;
			var totalcount =$(".ordered_drugs").length;
			$.each($(".ordered_drugs"), function(i, v) {
				count++;
				<?php
                if($hide_generate==2){
				?>
				getPeriodDrugBalance(count,$(this).attr("drug_id"), period_start, period_end,1);
				$(".physical_count").attr("readonly",true);
				$(".aggregated_qty").attr("readonly",true);	
				$(".aggregated_physical_qty").attr("readonly",true);
				$(".resupply").attr("readonly",true);		
				<?php
                }else{
				?>
				getPeriodDrugBalance(count,$(this).attr("drug_id"), period_start, period_end,2);	
				$(".physical_count").attr("readonly",true);
				$(".resupply").attr("readonly",true);
				<?php
				}
				?>
			});	
			setTimeout($.unblockUI,40000);	
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
		<?php
		if (!empty($cdrr_array)) {
			if($cdrr_array[0]['code']==0){
		?>
		$("#central_rate").val("<?php echo $cdrr_array[0]['reports_expected']; ?>");
		$("#actual_report").val("<?php echo $cdrr_array[0]['reports_actual']; ?>");
		$("#delivery_note").val("<?php echo $cdrr_array[0]['delivery_note']; ?>");
		<?php	
		    }
		foreach($cdrr_array as $cdrr){
		  	if($cdrr['non_arv']==1){
	    ?>
	    $("#non_arv").val("<?php echo $cdrr['non_arv']; ?>");
	    $("#non_arv").attr("checked",true);
	    <?php
			}
	    ?>
		  $("#comments").val("<?php echo $cdrr['comments']; ?>");
		  $("#period_start").val("<?php echo $cdrr['period_begin']; ?>");
		  $("#period_end").val("<?php echo $cdrr['period_end']; ?>");
		  $("#opening_balance_<?php echo $cdrr['drug_id']; ?>").val("<?php echo $cdrr['balance']; ?>");
		  $("#received_in_period_<?php echo $cdrr['drug_id']; ?>").val("<?php echo $cdrr['received']; ?>");
		  $("#dispensed_in_period_<?php echo $cdrr['drug_id']; ?>").val("<?php echo $cdrr['dispensed_units']; ?>"); 
		  $("#losses_in_period_<?php echo $cdrr['drug_id']; ?>").val("<?php echo $cdrr['losses']; ?>");
		  $("#adjustments_in_period_<?php echo $cdrr['drug_id']; ?>").val("<?php echo $cdrr['adjustments']; ?>");
		  $("#physical_in_period_<?php echo $cdrr['drug_id']; ?>").val("<?php echo $cdrr['count']; ?>");
		  <?php
		  if($cdrr_array[0]['code']==0){
		  ?>
		  $("#aggregated_qty_<?php echo $cdrr['drug_id']; ?>").val("<?php echo $cdrr['aggr_consumed']; ?>");
		  $("#aggregated_physical_qty_<?php echo $cdrr['drug_id']; ?>").val("<?php echo $cdrr['aggr_on_hand']; ?>");
		  <?php
		  }
		  ?>
		  $("#expire_qty_<?php echo $cdrr['drug_id']; ?>").val("<?php echo $cdrr['expiry_quant']; ?>");
		  $("#expire_period_<?php echo $cdrr['drug_id']; ?>").val("<?php echo $cdrr['expiry_date']; ?>");
		  $("#out_of_stock_<?php echo $cdrr['drug_id']; ?>").val("<?php echo $cdrr['out_of_stock']; ?>");
		  $("#old_resupply_<?php echo $cdrr['drug_id']; ?>").val("<?php echo $cdrr['resupply']; ?>");
		  $("#resupply_<?php echo $cdrr['drug_id']; ?>").val("<?php echo $cdrr['resupply']; ?>");
		<?php	
		   }
		}
	   ?>
	  <?php 
	  if($options=="view"){
	  ?>
	   $("input,textarea").attr("readonly","readonly");
	  <?php	
	  } 
	  ?>  	
	  
	});
	function calculateResupply(element) {
		var row_element = element.closest("tr");
		var opening_balance = parseInt(row_element.find(".opening_balance").attr("value"));
		var quantity_received = parseInt(row_element.find(".quantity_received").attr("value"));
		var quantity_dispensed = parseInt(row_element.find(".quantity_dispensed").attr("value"));
		var losses = parseInt(row_element.find(".losses").attr("value"));
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
		if(!(losses + 0)) {
			losses = 0;
		}

		if(!(adjustments + 0)) {
			adjustments = 0;
		}
		if(!(physical_count + 0)) {
			physical_count = 0;
		}
		calculated_physical = (opening_balance + quantity_received - quantity_dispensed - losses + adjustments);
		if(element.attr("class") == "physical_count") {
		 resupply = 0 - physical_count;
		 } else {
		 resupply = 0 - calculated_physical;
		 physical_count = calculated_physical;
		 }
		 
		resupply = (quantity_dispensed * 3) - physical_count+quantity_dispensed;
		resupply=parseInt(resupply);
		row_element.find('.label-warning').remove();

		row_element.find(".physical_count").attr("value", physical_count);
		row_element.find(".resupply").attr("value", resupply);
	}
</script>
