<script type="text/javascript">
	$(document).ready(function(){
		  //Get Today's Date and Upto Saturday
		  var someDate = new Date();
		  var dd = ("0" + someDate.getDate()).slice(-2);
		  var mm = ("0" + (someDate.getMonth() + 1)).slice(-2);
		  var y = someDate.getFullYear();
		  var last_10="";
		  //Load the last 10 years in drop down
		  $(".nd_year").html("");
		  for(var x=0;x<10;x++){
			last_10=parseInt(y)-x;
			$(".nd_year").append("<option value='"+last_10+"'>"+last_10+"</option>");
		  }
		  //Load months till previous month by default
		  	//Create array of months
		 	var month=new Array();
		 	month[0]="--";
			month[1]="Jan";
			month[2]="Feb";
			month[3]="Mar";
			month[4]="Apr";
			month[5]="May";
			month[6]="Jun";
			month[7]="Jul";
			month[8]="Aug";
			month[9]="Sep";
			month[10]="Oct";
			month[11]="Nov";
			month[12]="Dec";
		 	var m=parseInt(mm);
		  for(var p=1;p<m;p++){
		  	var q=("0" + (p)).slice(-2);
		  	$(".nd_month").append("<option value='"+q+"'>"+month[p]+"</option>");
		  }
		  
		  //Load previous month in months drop down
		  $(".nd_month").val(m-1);
		  var def_pipeline=$("#nd_pr_pipeline").val();
		  //Load default charts
		  
		  /*
		   * When year changes, if not current year, load all months
		   */
		  $(".nd_year").change(function(){
		  	var current_year = someDate.getFullYear();
		  	var _year=$(this).val();
		  	loadMonths(_year,current_year);
		  })
		  
		 /*
		  * Click get button to generate charts -----------------------------
		  */
		 //Generate chart for commodity analysis by clicking get button
		 $(".commodity_analysis_btn").click(function(){
		 	$("#chart_area_ca").html('<div class="loadingDiv" style="margin:20% 0 20% 0;" ><img style="width: 30px;margin-left:50%" src="<?php echo asset_url().'img/loading_spin.gif' ?>"></div>');
		 	var id=$(this).attr("id");
		 	var year=$("#nd_ca_year").val();
		 	var month=$("#nd_ca_month").val();
		 	var pipeline=$("#nd_ca_pipeline").val();
		 	//Check which button was clicked
		 	if(id=="stock_status_btn"){
		 		stock_status(year,month,pipeline);
		 	}
		 	else if(id=="consumption_btn"){
		 		consumption(year,month,pipeline);
		 	}
		 	
		 });
		 //Generate chart for patient analysis by clicking get button
		 $(".patient_analysis_btn").click(function(){
		 	$("#chart_area_pr").html('<div class="loadingDiv" style="margin:20% 0 20% 0;" ><img style="width: 30px;margin-left:50%" src="<?php echo asset_url().'img/loading_spin.gif' ?>"></div>');
		 	var id=$(this).attr("id");
		 	var year=$("#nd_pr_year").val();
		 	var month=$("#nd_pr_month").val();
		 	var pipeline=$("#nd_pr_pipeline").val();
		 	//Check which button was clicked
		 	if(id=="patientregimen_btn"){
		 		patient_by_regimen(year,month,pipeline);
		 	}
		 	else if(id=="currentpatient_art_btn"){
		 		current_patient_art(year,month,pipeline);
		 	}
		 	else if(id=="patientscaleup_btn"){
		 		patient_scale_up(year,month,pipeline);
		 	}
		 	
		 });
		  
		 //Generate chart for patient analysis by clicking get button
		 $(".patient_analysis_btn").click(function(){
		 	$("#chart_area_pr").html('<div class="loadingDiv" style="margin:20% 0 20% 0;" ><img style="width: 30px;margin-left:50%" src="<?php echo asset_url().'img/loading_spin.gif' ?>"></div>');
		 	var id=$(this).attr("id");
		 	var year=$("#nd_pr_year").val();
		 	var month=$("#nd_pr_month").val();
		 	var pipeline=$("#nd_pr_pipeline").val();
		 	//Check which button was clicked
		 	if(id=="patientregimen_btn"){
		 		patient_by_regimen(year,month,pipeline);
		 	}
		 	else if(id=="currentpatient_art_btn"){
		 		current_patient_art(year,month,pipeline);
		 	}
		 	else if(id=="patientscaleup_btn"){
		 		patient_scale_up(year,month,pipeline);
		 	}
		 	
		 });
		 
		 //Generate chart for facility analysis by clicking get button
		 $(".facility_analysis_btn").click(function(){
		 	$("#chart_area_fa").html('<div class="loadingDiv" style="margin:20% 0 20% 0;" ><img style="width: 30px;margin-left:50%" src="<?php echo asset_url().'img/loading_spin.gif' ?>"></div>');
		 	var id=$(this).attr("id");
		 	var year=$("#nd_fa_year").val();
		 	var month=$("#nd_fa_month").val();
		 	var pipeline=$("#nd_fa_pipeline").val();
		 	//Check which button was clicked
		 	if(id=="orderingsite_l_btn"){
		 		ordering_site_list(year,month,pipeline);
		 		ordering_site_summary(year,month,pipeline);
		 	}
		 	else if(id=="orderingsite_s_btn"){
		 		ordering_site_summary(year,month,pipeline);
		 	}
		 	else if(id=="servicepoint_l_btn"){
		 		service_point_list(year,month,pipeline);
		 	}
		 	else if(id=="servicepoint_s_btn"){
		 		service_point_summary(year,month,pipeline);
		 	}
		 	
		 });
		 
		  /*
		  * Click get button to generate charts end -----------------------------------
		  */
		 
		 //-----------------------------What happens when someone clicks a submenu
		 //Commodity analysis
		$(".commodity_analysis_menus").click(function(){
			//Get Today's Date and Upto Saturday
			  var someDate = new Date();
			  var mm = ("0" + (someDate.getMonth() + 1)).slice(-2);
			  var y = someDate.getFullYear();
			  var def_pipeline=$("#nd_ca_pipeline").val();
			  //Load current month in months drop down
		  	  $("#nd_ca_month").val(mm);
		  	  $("#nd_ca_pipeline").val(1);
			  $("#chart_area_ca").html('<div class="loadingDiv" style="margin:0 auto;" ><img style="width: 30px;margin-left:50%" src="<?php echo asset_url().'img/loading_spin.gif' ?>"></div>');
			
			//Check which menu was selected
			var id=$(this).attr("id");
			var base_url=$("#base_url").val();
			//Patient By Regimen
			if(id=="stock_status_menu"){
				$("#tab1 .active").removeClass("active");
				$(this).addClass("active");
				//Change id of get button
				$(".commodity_analysis_btn").attr("id","stock_status_btn");
				stock_status(y,mm,def_pipeline);
			}
			else if(id=="consumption_menu"){
				$("#tab1 .active").removeClass("active");
				$(this).addClass("active");
				//Change id of get button
				$(".commodity_analysis_btn").attr("id","consumption_btn");
				consumption(y,mm,def_pipeline);
			}
			
		});
		
		 //Patient Analysis
		$(".patient_analysis_menus").click(function(){
			//Get Today's Date and Upto Saturday
			  var someDate = new Date();
			  var mm = ("0" + (someDate.getMonth() + 1)).slice(-2);
			  var y = someDate.getFullYear();
			  var def_pipeline=$("#nd_pr_pipeline").val();
			  //Load current month in months drop down
		  	   $("#nd_pr_month").val(mm);
		  	  $("#nd_pr_pipeline").val(1);
			$("#chart_area_pr").html('<div class="loadingDiv" style="margin:0 auto;" ><img style="width: 30px;margin-left:50%" src="<?php echo asset_url().'img/loading_spin.gif' ?>"></div>');
			
			//Check which menu was selected
			var id=$(this).attr("id");
			var base_url=$("#base_url").val();
			//Patient By Regimen
			if(id=="patient_by_reg_menu"){
				$("#tab2 .active").removeClass("active");
				$(this).addClass("active");
				//Change id of get button
				$(".patient_analysis_btn").attr("id","patientregimen_btn");
				patient_by_regimen(y,mm,def_pipeline);
			}
			else if(id=="current_patient_by_art_menu"){
				$("#tab2 .active").removeClass("active");
				$(this).addClass("active");
				//Change id of get button
				$(".patient_analysis_btn").attr("id","currentpatient_art_btn");
				current_patient_art(y,mm,def_pipeline);
			}
			else if(id=="patient_scale_up_menu"){
				$("#tab2 .active").removeClass("active");
				$(this).addClass("active");
				//Change id of get button
				$(".patient_analysis_btn").attr("id","patientscaleup_btn");
				patient_scale_up(y,mm,def_pipeline);
			}
			
		});
		
		 //Facility Analysis
		$(".facility_analysis_menus").click(function(){
			//Get Today's Date and Upto Saturday
			  var someDate = new Date();
			  var mm = ("0" + (someDate.getMonth() + 1)).slice(-2);
			  var y = someDate.getFullYear();
			  var def_pipeline=$("#nd_fa_pipeline").val();
			  //Load current month in months drop down
		  	  $("#nd_fa_month").val(mm);
		  	  $("#nd_fa_pipeline").val(1);
			  $("#chart_area_fa").html('<div class="loadingDiv" style="margin:0 auto;" ><img style="width: 30px;margin-left:50%" src="<?php echo asset_url().'img/loading_spin.gif' ?>"></div>');
			
			//Check which menu was selected
			var id=$(this).attr("id");
			var base_url=$("#base_url").val();
			//Patient By Regimen
			if(id=="ordering_site_list_menu"){
				$("#tab3 .active").removeClass("active");
				$(this).addClass("active");
				//Change id of get button
				$(".facility_analysis_btn").attr("id","orderingsite_l_btn");
				ordering_site_list(y,mm,def_pipeline);
			}
			else if(id=="ordering_site_sum_menu"){
				$("#tab3 .active").removeClass("active");
				$(this).addClass("active");
				//Change id of get button
				$(".facility_analysis_btn").attr("id","orderingsite_s_btn");
				ordering_site_summary(y,mm,def_pipeline);
			}
			else if(id=="service_point_list_menu"){
				$("#tab3 .active").removeClass("active");
				$(this).addClass("active");
				//Change id of get button
				$(".facility_analysis_btn").attr("id","servicepoint_l_btn");
				service_point_list(y,mm,def_pipeline);
			}
			else if(id=="service_point_sum_menu"){
				$("#tab3 .active").removeClass("active");
				$(this).addClass("active");
				//Change id of get button
				$(".facility_analysis_btn").attr("id","servicepoint_s_btn");
				service_point_summary(y,mm,def_pipeline);
			}
			
		});
		
		
		//-----------------------------What happens when someone clicks a menu end -------------
		function loadMonths(_year,current_year){
			//If year selected is different from current year,load all months, otherload till previous month
			if(current_year!=_year){
		  		$(".nd_month").find('option').remove();
		  		for(var p=1;p<13;p++){
		  		  var q=("0" + (p)).slice(-2);
				  $(".nd_month").append("<option value='"+q+"'>"+month[p]+"</option>");
				}
		  	}
		  	else if(current_year==_year){
		  		$(".nd_month").find('option').remove();
		  		for(var p=1;p<m;p++){
		  		  var q=("0" + (p)).slice(-2);
				  $(".nd_month").append("<option value='"+q+"'>"+month[p]+"</option>");
				}
		  	}
		}
	});
	
	/*
	 * Commodity Analysis functions
	 */
	//Stock status
	function stock_status(year,month,pipeline){
		var commodity_analysis_link="<?php echo base_url().'national_management/ca_stock_status/';?>"+year+'/'+month+'/'+pipeline;
		$('#chart_area_ca').load(commodity_analysis_link);
	}
	//Consumption
	function consumption(year,month,pipeline){
		var commodity_analysis_link="<?php echo base_url().'national_management/ca_consumption/';?>"+year+'/'+month+'/'+pipeline;
		$('#chart_area_ca').load(commodity_analysis_link);
	}
	/*
	 * Patient Analysis functions
	 */
	//Patient By Regimen
	function patient_by_regimen(year,month,pipeline){
		var patient_analysis_link="<?php echo base_url().'national_management/pa_patients_by_regimen/';?>"+year+'/'+month+'/'+pipeline;
		$('#chart_area_pr').load(patient_analysis_link);
	}
	//Current patient By ART Site
	function current_patient_art(year,month,pipeline){
		var patient_analysis_link="<?php echo base_url().'national_management/pa_patients_by_artsite/';?>"+year+'/'+month+'/'+pipeline;
		$('#chart_area_pr').load(patient_analysis_link);
	}
	
	//Patient Scale Up
	function patient_scale_up(year,month,pipeline){
		var patient_analysis_link="<?php echo base_url().'national_management/pa_patients_scaleup/';?>"+year+'/'+month+'/'+pipeline;
		$('#chart_area_pr').load(patient_analysis_link);
	}
	
	/*
	 * Facility Analysis functions
	 */
	function ordering_site_list(year,month,pipeline){
		var facility_analysis_link="<?php echo base_url().'national_management/fa_ordering_sites_list/';?>"+year+'/'+month+'/'+pipeline;
		$('#chart_area_fa').load(facility_analysis_link);
	}
	function ordering_site_summary(year,month,pipeline){
		var facility_analysis_link="<?php echo base_url().'national_management/fa_ordering_sites_summary/';?>"+year+'/'+month+'/'+pipeline;
		$('#chart_area_fa_sum').load(facility_analysis_link);
	}
	function service_point_list(year,month,pipeline){
		var facility_analysis_link="<?php echo base_url().'national_management/fa_service_points_list/';?>"+year+'/'+month+'/'+pipeline;
		$('#chart_area_fa').load(facility_analysis_link);
	}
	function service_point_summary(year,month,pipeline){
		var facility_analysis_link="<?php echo base_url().'national_management/fa_service_points_summary/';?>"+year+'/'+month+'/'+pipeline;
		$('#chart_area_fa').load(facility_analysis_link);
	}
</script>
<div class="tabbable"> <!-- Only required for left/right tabs -->
  <ul class="nav nav-tabs">
    <li class="active"><a href="#tab1" data-toggle="tab">Commodity Analysis</a></li>
    <li><a href="#tab2" data-toggle="tab">Patient Analysis</a></li>
    <li><a href="#tab3" data-toggle="tab">Facility Analysis</a></li>
    <li><a href="#tab4" data-toggle="tab">Order Analysis</a></li>
    <li><a href="#tab5" data-toggle="tab">Reporting Analysis</a></li>
  </ul>
  <div class="tab-content">
  	<!-- Commodity Analysis -->
    <div class="tab-pane active" id="tab1">
      <div class="navbar" style="width:100%">
		  <div class="navbar-inner">
		    <ul class="nav">
		      <li id="stock_status_menu" class="active commodity_analysis_menus"><a href="#">Stock Status</a></li>
		      <li id="consumption_menu" class="commodity_analysis_menus"><a  href="#">Consumption</a></li>
		    </ul>
		  </div>
		</div>	
      <div class="row-fluid">
		  <div class="span12">
		    <div id="ca_menus" class="nd_menus">
				<h3 class="font_responsive">
					<select id="nd_ca_month" class="nd_month nd_input_small">
						
					</select>
					<select id="nd_ca_year" class="nd_year nd_input_small">
					</select> 
					
					<select id="nd_ca_pipeline" class="nd_pipeline nd_input_medium">
						<?php //Load list of pipelines
							foreach ($supporter as $value) {
						?>
						<option value="<?php echo $value['id']?>"><?php echo $value['Name']?></option>
						<?php	
							} 
						?>
					</select>
					<button class="generate btn btn-warning nd_input_small commodity_analysis_btn" style="color:black" id="stock_status_btn">Get</button>
				</h3>
				
			</div>
			<div id="chart_area_ca" >
				
			</div>
		  </div>
		</div>
    </div>
    <!-- Patient Analysis -->
    <div class="tab-pane" id="tab2">
    	<div class="navbar" style="width:100%">
		  <div class="navbar-inner">
		    <ul class="nav">
		      <li id="patient_by_reg_menu" class="active patient_analysis_menus"><a href="#">Patient By Regimen</a></li>
		      <li id="current_patient_by_art_menu" class="patient_analysis_menus"><a  href="#">Current Patient By ART Site</a></li>
		      <li id="patient_scale_up_menu" class="patient_analysis_menus"><a  href="#">Patient Scale Up</a></li>
		    </ul>
		  </div>
		</div>
	  	<div class="row-fluid patient_analysis">
			    <div class="span12">
				  	<div id="pr_menus" class="nd_menus">
						<h3 class="font_responsive">
							<select id="nd_pr_month" class="nd_month nd_input_small">
								
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
							<button class="generate btn btn-warning nd_input_small patient_analysis_btn" style="color:black" id="patientregimen_btn">Get</button>
						</h3>
						
					</div>
					<div id="chart_area_pr" >
						
					</div>
			  </div>
		</div>
    </div>
    
    <!-- Facility Analysis -->
    <div class="tab-pane" id="tab3">
      <div class="navbar" style="width:100%">
		  <div class="navbar-inner">
		    <ul class="nav">
		      <li id="ordering_site_list_menu" class="active facility_analysis_menus"><a href="#">Ordering Sites List</a></li>
		      <li id="ordering_site_sum_menu" class="facility_analysis_menus"><a  href="#">Ordering Sites Summary</a></li>
		      <li id="service_point_list_menu" class="facility_analysis_menus"><a  href="#">Service Points List</a></li>
		      <li id="service_point_sum_menu" class="facility_analysis_menus"><a  href="#">Service Points Summary</a></li>
		    </ul>
		  </div>
	  </div>
      <div class="row-fluid">
		  <div class="span12">
		  		<div id="fa_menus" class="nd_menus">
							<h3 class="font_responsive">
								<select id="nd_fa_month" class="nd_month nd_input_small">
									
								</select>
								<select id="nd_fa_year" class="nd_year nd_input_small">
								</select> 
								
								<select id="nd_fa_pipeline" class="nd_pipeline nd_input_medium">
									<?php //Load list of pipelines
										foreach ($supporter as $value) {
									?>
									<option value="<?php echo $value['id']?>"><?php echo $value['Name']?></option>
									<?php	
										} 
									?>
								</select>
								<button class="generate btn btn-warning nd_input_small facility_analysis_btn" style="color:black" id="orderingsite_l_btn">Get</button>
							</h3>
				</div>
				<div class="row-fluid">
					<div id="chart_area_fa" class="span6"></div>
					<div id="chart_area_fa_sum" class="span6"></div>
		  		</div>
		  </div>
		</div>
    </div>
    <!-- Order Analysis -->
    <div class="tab-pane" id="tab4">
      <div class="row-fluid">
		  <div class="span12">
		    <div class="row-fluid">
		      <div class="span6 nd_order_a">
		       Order by commodity National Orders
		      </div>
		      <div class="span6 nd_order_a">
		      	Order reporting rate
		      </div>
		    </div>
		  </div>
		</div>
		<div class="row-fluid">
		  <div class="span12">
		    <div class="row-fluid">
		      <div class="span6 nd_order_a">
		       Maps-statistics for comparison with Patient Analysis
		      </div>
		      <div class="span6 nd_order_a">
		      	Pipeline delivery rates
		      </div>
		    </div>
		  </div>
		</div>
    </div>
    <div class="tab-pane" id="tab5">
      <div class="row-fluid">
		  <div class="span12">
		    <div class="row-fluid">
		      <div class="span4 nd_report_a">
		       Reporting rates per facility type
		      </div>
		      <div class="span4 nd_report_a">
		      	Reporting rate per facility
		      </div>
		      <div class="span4 nd_report_a">
		      	Non-reporting rates
		      </div>
		    </div>
		  </div>
		</div>
    </div>
  </div>
</div>