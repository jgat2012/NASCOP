<div class="full-content" style="width:98%;">
	<div>
		<ul class="breadcrumb">
			<li>
				<a href="<?php echo site_url().'order' ?>">Orders</a><span class="divider">/</span>
			</li>
			<li class="active" id="actual_page">
				<?php echo $page_title;?>
			</li>
		</ul>
	</div>
<div  class="facility_info header section">
	<h4><b><?php echo $order_array[0]['cdrr_label'] . " " . $order_array[0]['status_name'];?></b></h4>
	<a href='<?php echo site_url("order/download_order/" . $cdrr_id);?>'><?php echo $order_array[0]['cdrr_label'] . " " . $order_array[0]['facility_name'] . " " . $order_array[0]['period_begin'] . " to " . $order_array[0]['period_end'] . ".xls";?></a>
    <p>&nbsp;</p>
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
					<input type="checkbox" name="non_arv" />
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
	<div id="commodity-table">
		<table class="table table-bordered"  id="generate_order" style="background:#FFF;">
		<?php
				$header_text = '<thead style="text-align:left;background:#c3d9ff;">
					<tr>
						<th class="col_drug" style="font-size:15px;" rowspan="3">Drug Name</th>
						<th class="number" rowspan="3">Unit Pack Size</th>
						<th class="number">Beginning Balance</th>
						<th class="number">Quantity <br/>Received in this period</th>
						<th class="number">Reported Aggregated Quantity CONSUMED in the reporting period (Satellite sites plus Central site dispensing point where relevant)</th>
						<th class="number">Reported Aggregated Physical Stock on Hand at end of reporting period (Satellite sites plus Central site dispensing point where relevant)</th>
						<th class="number">Quantity required for RESUPPLY</th>
					</tr>
					<tr>
						<th>In Packs</th>
						<th>In Packs</th>
						<th>In Packs</th>
						<th>In Packs</th>
						<th>In Packs</th>
					</tr>
					<tr>
						<th>A</th>
						<th>B</th>
						<th>G</th>
						<th>H</th>
						<th>J</th>
					</tr>
			</thead>';	echo $header_text;	
			?>
		<tbody>
			<?php 
				$counter = 0;
					foreach($commodities as $commodity){
				         if($commodity -> Drug !=NULL){
				         	$counter++;
			                   if($counter ==10){
			                   echo $header_text;
			                   $counter = 0;
			             }
			?>
			<tr class="ordered_drugs" drug_id="<?php echo $commodity -> id;?>">
						<td class="col_drug" style="font-size:15px;"><?php echo strtoupper($commodity -> Drug);?>
							<input type="hidden" name="pack_size[]" id="pack_size_<?php echo $commodity -> id;?>" value="<?php echo $commodity ->Pack_Size;?>"/>
						</td>
						<td class="number calc_count"><?php echo $commodity ->Pack_Size;?></td>
						<td> <input name="opening_balance[]" id="opening_balance_<?php echo $commodity -> id;?>" type="text" class="opening_balance"/></td>
						<td> <input name="quantity_received[]" id="received_in_period_<?php echo $commodity -> id;?>" type="text" class="quantity_received"/></td>
						<td> <input tabindex="-1" name="aggregated_qty[]" id="aggregated_qty_<?php echo $commodity->id;?>" type="text" class="aggregated_qty"/></td>
						<td> <input tabindex="-1" name="aggregated_physical_qty[]" id="aggregated_physical_qty_<?php echo $commodity->id;?>" type="text" class="aggregated_physical_qty"/></td>
						<td> 
							<input tabindex="-1" name="new_resupply[]" id="new_resupply_<?php echo $commodity -> id;?>" type="text" class="resupply"/>
							<input tabindex="-1" name="resupply[]" id="resupply_<?php echo $commodity -> id;?>" class="resupply" type="hidden"/>
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
<table class=" table table-bordered regimen-table  research" style="background:#FFF;" >
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
					<td style="border-right:2px solid #DDD;padding-right:2em;"><?php echo $regimen -> Regimen_Code;?></td>
					<td regimen_id="<?php echo $regimen -> id;?>" class="regimen_desc col_drug"><?php echo $regimen -> Regimen_Desc;?></td>
					<td regimen_id="<?php echo $regimen -> id;?>" class="regimen_numbers">
					<input name="patient_numbers[]" id="patient_numbers_<?php echo $regimen -> id;?>" type="text">
					<input name="patient_regimens[]" value="<?php echo $regimen -> Regimen_Code." | ".$regimen -> Regimen_Desc;?>" type="hidden">
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
	  
	  <?php
		if (!empty($order_array)) {
		  foreach($order_array as $cdrr){
	    ?>
		  $("#opening_balance_<?php echo $cdrr['drug_id']; ?>").val("<?php echo $cdrr['balance']; ?>");
		  $("#received_in_period_<?php echo $cdrr['drug_id']; ?>").val("<?php echo $cdrr['received']; ?>");
		  $("#aggregated_qty_<?php echo $cdrr['drug_id']; ?>").val("<?php echo $cdrr['aggr_consumed']; ?>");
		  $("#aggregated_physical_qty_<?php echo $cdrr['drug_id']; ?>").val("<?php echo $cdrr['aggr_on_hand']; ?>");
		  $("#resupply_<?php echo $cdrr['drug_id']; ?>").val("<?php echo $cdrr['resupply']; ?>");
		  $("#new_resupply_<?php echo $cdrr['drug_id']; ?>").val("<?php echo $cdrr['resupply']; ?>");
		<?php	
		   }
		}
	   ?>
	   $("input").attr("readonly","readonly");
	   $(".resupply").attr("readonly",false); 	
});
</script>