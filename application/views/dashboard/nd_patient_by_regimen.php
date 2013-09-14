<!-- ------------ Patient By Regimen ------------------------------ -->	
<div class="row-fluid patient_analysis">
    <div class="span12">
	  	<div id="pr_menus" class="nd_menus">
			<h3 class="font_responsive">
				<select id="nd_pr_month" class="nd_month nd_input_small">
					<option value='01'>Jan</option>
					<option value='02'>Feb</option>
					<option value='03'>Mar</option>
					<option value='04'>Apr</option>
					<option value='05'>May</option>
					<option value='06'>Jun</option>
					<option value='07'>Jul</option>
					<option value='08'>Aug</option>
					<option value='09'>Sep</option>
					<option value='10'>Oct</option>
					<option value='11'>Nov</option>
					<option value='12'>Dec</option>
				</select>
				<select id="nd_pr_year" class="nd_year nd_input_small">
				</select> 
				
				<select id="nd_pr_pipeline" class="nd_pipeline nd_input_medium">
					<?php //Load list of pipelines
						foreach ($supporter as $value) {
					?>
					<option value="<?php echo $value['id']?>"><?php echo $value['Name']?></option>
					<?php	
						} 
					?>
				</select>
				<button class="generate btn btn-warning nd_input_small" style="color:black" id="patientregimen_btn">Get</button>
			</h3>
			
		</div>
	<div id="chart_area_pr">
		<div class="loadingDiv" style="margin:20% 0 20% 0;" ><img style="width: 30px;margin-left:50%" src="<?php echo asset_url().'img/loading_spin.gif' ?>"></div>
	</div>
  </div>
</div>