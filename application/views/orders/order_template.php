<div class="full-content" style="width:98%;">
	<div style="margin-top:1%">
		<ul class="breadcrumb">
			<li>
				<a href="<?php echo site_url().'order' ?>">Orders</a><span class="divider">/</span>
			</li>
			<li class="active" id="actual_page">
				<?php echo $page_title;?>
			</li>
		</ul>
	</div>
<div  class="facility_info header section" style="width:100%;">
	<div style="float:right;">
		<ul class="nav nav-tabs">
         <?php echo $option_links; ?>
        </ul>
        </div>
	<h4><b><?php echo $order_array[0]['cdrr_label'] . " " . $order_array[0]['status_name'];?></b></h4>
	<a href='<?php echo site_url("order/download_cdrr/" . $cdrr_id);?>'><?php echo $order_array[0]['cdrr_label'] . " " . $order_array[0]['facility_name'] . " " . $order_array[0]['period_begin'] . " to " . $order_array[0]['period_end'] . ".xls";?></a>
    <p>&nbsp;</p>
    <?php 
    if($order_array[0]['status_name']!="dispatched"){
    ?>
    <a href="<?php echo site_url("order/move_order/review/" . $cdrr_id."/".$maps_id);?>" class="btn">Send back for Review</a>
    <?php
    if($order_array[0]['status_name']=="approved"){
    ?>
    <a href="<?php echo site_url("order/move_order/received/" . $cdrr_id."/".$maps_id);?>" class="btn">Received</a>
    <?php
	} else if($order_array[0]['status_name']=="received"){
    ?>
    <a href="<?php echo site_url("order/move_order/rationalized/" . $cdrr_id."/".$maps_id);?>" class="btn">Rationalized</a>
     <?php
	} else if($order_array[0]['status_name']=="rationalized"){
    ?>
    <a href="<?php echo site_url("order/move_order/packed/" . $cdrr_id."/".$maps_id);?>" class="delivery btn">Delivery Note Entered</a>
     <?php
	} else if($order_array[0]['status_name']=="packed"){
    ?>
    <a href="<?php echo site_url("order/move_order/dispatched/" . $cdrr_id."/".$maps_id);?>" class="btn">Dispatched</a>
    <?php
	}}
    ?>
    <p></p>
    	<?php 
	if($this->session->flashdata('order_delete')){
		
		echo '<p class="message error">'.$this->session->flashdata('order_delete').'</p>';
	}
	else if ($this->session->flashdata('order_message')){
		echo '<p class="message info">'.$this->session->flashdata('order_message').'</p>';
	}	
	?>
	
		<table  cellpadding="4" border="1" width="100%" style="border:1px solid #DDD;font-size:15px;">
			<tbody>
				<tr>
					<td style="width:50%;"><b>Facility Name: &nbsp;</b><?php echo $order_array[0]['facility_name'];?></td>
					<td><b>Facility code: &nbsp;</b><?php echo $order_array[0]['facilitycode'];?></td>
				</tr>
				<tr>
					<td><b>County: &nbsp;</b><?php echo $order_array[0]['county_name'];?></td>
					<td><b>District: &nbsp;</b><?php echo $order_array[0]['district_name'];?></td>
				</tr>
				<tr>
					<td colspan='2'><b>Programme Sponsor: &nbsp;</b><?php echo $order_array[0]['sponsors'];?></td>
				</tr>
				<tr>
					<td><b>Type of Service provided at the Facility: &nbsp; </b><?php echo $order_array[0]['services'];?></td>
					<td><b>Non-ARV: &nbsp;</b>
					<input type="checkbox" name="non_arv" id="non_arv" value="0"/>
					</td>
				</tr>
				<tr>
					<td colspan='2'><b>Period of Reporting: &nbsp;</b><b>Beginning:</b> &nbsp;
					<input type="text" value="<?php echo date('d-M-Y', strtotime($order_array[0]['period_begin']));?>" readonly="readonly"/>
					<b>Ending:</b> &nbsp;
					<input type="text" value="<?php echo date('d-M-Y', strtotime($order_array[0]['period_end']));?>" readonly="readonly"/>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
<div class="row-fluid">
	<div class="span9">	
	<div id="commodity-table1">
			<form method="post" action="<?php echo site_url('order/rationalize_cdrr/'.$cdrr_id."/".$maps_id)?>">	
		<table class="table table-bordered"  id="generate_order" style="background:#FFF;">
		<?php
		if($order_code=="D-CDRR"){
		  $header_text = '<thead style="text-align:left;background:#c3d9ff;">
					<tr>
						<th class="col_drug" style="font-size:15px;" rowspan="3">Drug Name</th>
						<th class="number" rowspan="3">Unit Pack Size</th>
						<th class="number">Beginning Balance</th>
						<th class="number">Quantity <br/>Received in this period</th>
						<th class="number">Total Qty ISSUED <br>to ARV dispensing sites <br>(Satellite sites plus Central site <br>dispensing point(s) where relevant)</th>
						<th class="number">End of Month Physical Count</th>
						<th class="number">Reported Aggregated <br/>Quantity CONSUMED <br/>in the reporting period<br/> (Satellite sites plus <br/>Central site dispensing point where relevant)</th>
						<th class="number">Reported Aggregated <br/>Physical Stock on Hand <br/>at end of reporting period <br/>(Satellite sites plus <br/>Central site dispensing point where relevant)</th>
						<!--<th class="number">Average Monthly Consumption</th>-->
						<!--<th class="number">Average Monthly Issues</th> -->
						<th class="number">Quantity required for RESUPPLY</th>
						<th class="number">Calculated Quantity</th>
						<th class="number">Rationalized Quantity</th>
					</tr>
					<tr>
						<th>In Packs</th>
						<th>In Packs</th>
						<th>In Packs</th>
						<th>In Packs</th>
						<th>In Packs</th>
						<th>In Packs</th>
						<!-- <th>In Packs</th> -->
						<!-- <th>In Packs</th> -->
						<th>In Packs</th>
						<th>In Packs</th>
						<th>In Packs</th>
					</tr>
					<tr>
						<th>A</th>
						<th>B</th>
						<th>C</th>
						<th>F</th>
						<th>G</th>
						<th>H</th>
						<!-- <th></th> -->
						<!-- <th></th> -->
						<th>J</th>
						<th>K</th>
						<th>L</th>
					</tr>
			</thead>';	
		}else{
			$header_text = '<thead style="text-align:left;background:#c3d9ff;">
					<tr>
						<th class="col_drug" style="font-size:15px;" rowspan="3">Drug Name</th>
						<th class="number" rowspan="3">Unit Pack Size</th>
						<th class="number">Beginning Balance</th>
						<th class="number">Quantity <br/>Received in this period</th>
						<th class="number">Total Quantity Dispensed <br/>this period</th>
						<th class="number">End of Month Physical Count</th>
						<!-- <th class="number">Average Monthly Consumption</th> -->
						<!-- <th class="number">Average Monthly Issues</th> -->
						<th class="number">Quantity required for RESUPPLY</th>
						<th class="number">Calculated Quantity</th>
						<th class="number">Rationalized Quantity</th>
					</tr>
					<tr>
						<th>In Packs</th>
						<th>In Packs</th>
						<th>In Packs</th>
						<th>In Packs</th>
						<!-- <th>In Packs</th> -->
						<!-- <th>In Packs</th> -->
						<th>In Packs</th>
						<th>In Packs</th>
						<th>In Packs</th>
					</tr>
					<tr>
						<th>A</th>
						<th>B</th>
						<th>C</th>
						<th>F</th>
						<!-- <th></th> -->
						<!-- <th></th> -->
						<th>J</th>
						<th>K</th>
						<th>L</th>
					</tr>
			</thead>';	
			
		}
					echo $header_text;	
			?>
		<tbody>
			<?php 
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
							    if($commodity->Category==1 && $count_one==0){
						  	  echo '<tr><td colspan="11" style="text-align:center;background:#999;">Adult Preparations</td></tr>';
							  $count_one++;
						  }	   
						  if($commodity->Category==2 && $count_two==0){
						  	  echo '<tr><td colspan="11" style="text-align:center;background:#999;">Pediatric Preparations</td></tr>';
							  $count_two++;
						  }
                          if($commodity->Category==3 && $count_three==0){
						  	  echo '<tr><td colspan="11" style="text-align:center;background:#999;">Drugs for OIs</td></tr>';
							  $count_three++;
						  }
						 
			?>
			<tr class="ordered_drugs" drug_id="<?php echo $commodity -> id;?>">
						<td class="col_drug" style="font-size:15px;"><?php if($commodity->Unit_Name !=""){ echo strtoupper($commodity -> Drug." [".$commodity->Unit_Name."]");}else{ echo strtoupper($commodity -> Drug);}?>
							<input type="hidden" name="pack_size[]" id="pack_size_<?php echo $commodity -> id;?>" value="<?php echo $commodity ->Pack_Size;?>"/>
						</td>
						<td class="number calc_count"><?php echo $commodity ->Pack_Size;?></td>
						<td> <input name="opening_balance[]" id="opening_balance_<?php echo $commodity -> id;?>" type="text" class="opening_balance"/></td>
						<td> <input name="quantity_received[]" id="received_in_period_<?php echo $commodity -> id;?>" type="text" class="quantity_received"/></td>
						<?php if($order_code=="D-CDRR"){?>
						<td> <input tabindex="-1" name="quantity_dispensed[]" id="dispensed_in_period_<?php echo $commodity -> id;?>" type="text" class="quantity_dispensed"/></td>	
						<td> <input tabindex="-1" name="physical_in_period[]" id="physical_in_period_<?php echo $commodity->id;?>" type="text" class="physical_in_period"/></td>
						<td> <input tabindex="-1" name="aggregated_qty[]" id="aggregated_qty_<?php echo $commodity->id;?>" type="text" class="aggregated_qty"/></td>
						<td> <input tabindex="-1" name="aggregated_physical_qty[]" id="aggregated_physical_qty_<?php echo $commodity->id;?>" type="text" class="aggregated_physical_qty"/></td>
						<?php }else{?>
							<td> <input tabindex="-1" name="quantity_dispensed_packs[]" id="dispensed_in_period_packs_<?php echo $commodity->id;?>" type="text" class="quantity_dispensed_packs"/></td>	
						    <td> <input tabindex="-1" name="physical_in_period[]" id="physical_in_period_<?php echo $commodity->id;?>" type="text" class="physical_in_period"/></td>
						<?php } ?>	
						<!-- 
						<td> <input tabindex="-1" name="avg_consumption[]" id="avg_consumption_<?php echo $commodity->id;?>" type="text" class="avg_consumption"/></td>
						<td> <input tabindex="-1" name="avg_issues[]" id="avg_issues_<?php echo $commodity->id;?>" type="text" class="avg_issues"/></td>
						-->
						<td> 
							<input tabindex="-1" name="new_resupply[]" id="new_resupply_<?php echo $commodity -> id;?>" type="text" class="resupply"/>
						</td>
						<td> 
							<input tabindex="-1" name="calc_resupply[]" id="calc_resupply_<?php echo $commodity -> id;?>" type="text" class="resupply calc"/>
						</td>
						<td>
							<input tabindex="-1" name="resupply[]" id="resupply_<?php echo $commodity -> id;?>" class="resupply rationalized" type="text"/>
						</td>	
						<input type="hidden" name="commodity[]" value="<?php echo $commodity -> id;?>"/>					
					</tr>					
					<?php 
						  }
						 }
					?>
		</tbody>
	</table>
		<input type="submit" class="btn btn-info rationalized" id="update_btn" name="update" value="Update"/>
		</form>
	</div>
	</div>
<div class="span3">	
<table class=" table table-bordered regimen-table1  research" style="background:#FFF;" >
			<thead>
				<tr>
					<th class="col_drug" colspan="2" style="background:#c3d9ff;color:000;"> Regimen </th>
					<th style="background:#c3d9ff;color:000;">
					<input type="button" id="accordion_collapse" value="+"/>
					</span>Patients<span></th>
				</tr>
			</thead>
			<?php
				$counter = 1;
				foreach($regimen_categories as $category){
			?>
			<tbody>
				<?php
				$regimens = $category -> Regimens;
				?><tr class="accordion"><th colspan="3" style="background:#c3d9ff;color:000;" ><?php echo $category -> Name;?></th></tr><?php
                foreach($regimens as $regimen){
				?>
				<tr>
					<td style="border-right:2px solid #DDD;padding-right:2em;"><?php echo $regimen -> regimen_code;?></td>
					<td regimen_id="<?php echo $regimen -> id;?>" class="regimen_desc col_drug"><?php echo $regimen -> regimen_desc;?></td>
					<td regimen_id="<?php echo $regimen -> id;?>" class="regimen_numbers">
					<input name="patient_numbers[]" id="patient_numbers_<?php echo $regimen -> n_map;?>" type="text">
					<input name="patient_regimens[]" value="<?php echo $regimen -> regimen_code." | ".$regimen -> regimen_desc;?>" type="hidden">
					</td>
				</tr>
				<?php
				}
				?>
			</tbody>
			<?php
			}
			?>
	</table>
	</div>
	</div>
	<div>
			<table style="width:100%;" class="table ">
		    	<tr ><td colspan="4"  maxlength="255">
		    		<b>Delivery Note</b>
		            <input type='text' name='delivery_note' id='delivery_note' style='width:100%;' value='<?php echo @$order_array[0]['delivery_note']; ?>'/>
		    	</td></tr>
		    	<?php foreach($logs as $log){
		    		if($log->description =="prepared" || $log->description =="approved"){
		    		?>
				<tr>
					<td><b>Report <?php echo $log->description;?> by:</b> </td>
					<td><?php echo $log->user->name; ?></td>
					<td><b>Designation:</b></td>
					<td><?php echo $log->user->role; ?></td>
				</tr>
				<tr>
					<td><b>Contact Telephone:</b></td>
					<td><?php echo $log->user->username; ?></td>
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
	</div>
	
</div>
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

<script>
	$(document).ready(function() {
		var $research = $('.research');
		$research.find("tr").not('.accordion').hide();
		$research.find("tr").eq(0).show();

		$research.find(".accordion").click(function() {
			$research.find('.accordion').not(this).siblings().fadeOut(500);
			$(this).siblings().fadeToggle(500);
		}).eq(0).trigger('click');

		$('#accordion_collapse').click(function() {
			if($(this).val() == "+") {
				var $research = $('.research');
				$research.find("tr").show();
				$('#accordion_collapse').val("-");
			} else {
				var $research = $('.research');
				$research.find("tr").not('.accordion').hide();
				$research.find("tr").eq(0).show();
				$('#accordion_collapse').val("+");
			}
	  });
	  
	     $(".delivery").click(function(){
	     	/*
         	  var del_value=$("#delivery_note").val();
         	  if(del_value==""){
         	  	alert("Delivery Note is Blank");
         	  	return false;
         	  }*/
         });
	  
	  <?php
		if (!empty($order_array)) {
		  foreach($order_array as $cdrr){
		  	$resupply=0;
			  if($resupply_array[$cdrr['drug_id']]){
			  	$resupply=$resupply_array[$cdrr['drug_id']];
			  }
		  	if($cdrr['non_arv']==1){
	    ?>
	    $("#non_arv").val("<?php echo $cdrr['non_arv']; ?>");
	    $("#non_arv").attr("checked",true);
	    <?php
			}
	    ?>
		  $("#opening_balance_<?php echo $cdrr['drug_id']; ?>").val("<?php echo $cdrr['balance']; ?>");
		  $("#received_in_period_<?php echo $cdrr['drug_id']; ?>").val("<?php echo $cdrr['received']; ?>");
		  $("#physical_in_period_<?php echo $cdrr['drug_id']; ?>").val("<?php echo $cdrr['count']; ?>");
		  <?php if($cdrr['code']=="D-CDRR"){?>
		  $("#dispensed_in_period_<?php echo $cdrr['drug_id']; ?>").val("<?php echo $cdrr['dispensed_packs']; ?>"); 	
		  $("#aggregated_qty_<?php echo $cdrr['drug_id']; ?>").val("<?php echo $cdrr['aggr_consumed']; ?>");
		  $("#aggregated_physical_qty_<?php echo $cdrr['drug_id']; ?>").val("<?php echo $cdrr['aggr_on_hand']; ?>");	
		  $("#calc_resupply_<?php echo $cdrr['drug_id']; ?>").val("<?php echo (($cdrr['aggr_consumed']*3)-$cdrr['count']); ?>");
		  <?php }else{?>
		  $("#dispensed_in_period_packs_<?php echo $cdrr['drug_id']; ?>").val("<?php echo $cdrr['dispensed_packs']; ?>");
		  $("#calc_resupply_<?php echo $cdrr['drug_id']; ?>").val("<?php echo (($cdrr['dispensed_packs']*3)-$cdrr['count']); ?>");
		  <?php }?>
		  $("#avg_consumption_<?php echo $cdrr['drug_id']; ?>").val("<?php echo ceil($cdrr['aggr_consumed']/$amc); ?>");
		  $("#avg_issues_<?php echo $cdrr['drug_id']; ?>").val("<?php echo ceil($cdrr['received']/$amc); ?>");
		  $("#resupply_<?php echo $cdrr['drug_id']; ?>").val("<?php if($cdrr['resupply']<0){ echo 0;}else{ echo $cdrr['resupply']; }?>");
		  <?php if( $cdrr['old_resupply']==''){?>
		  $("#new_resupply_<?php echo $cdrr['drug_id']; ?>").val("<?php echo $cdrr['resupply']; ?>");
		  <?php }else{?>
	      $("#new_resupply_<?php echo $cdrr['drug_id']; ?>").val("<?php echo $cdrr['old_resupply']; ?>");
		  <?php
		    }	
		   }
		}
	   ?>
	   
	   <?php 
	   	if (!empty($order_array)) {
		  foreach($fmaps_array as $maps){
	   ?>
	   $("#patient_numbers_<?php echo $maps['regimen_id']; ?>").val("<?php echo $maps['total']; ?>");
	   <?php
		  }
		}if($order_array[0]['status_name']=="received"){
		?>
	   $("input").attr("readonly","readonly");
	   $(".rationalized").attr("readonly",false); 
	   $("#update_btn").show();
	   <?php
	   }else{
	   ?>	
	   $("input").attr("readonly","readonly");
	   $("#update_btn").hide();
	   <?php
	   }?>
});
</script>