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
		  //Load current month in months drop down
		  $(".nd_month").val(mm);
		  var def_pipeline=$("#nd_pr_pipeline").val();
		  //Load default charts
		  
		  
		 //Generate chart for patient analysis
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
		 	else if(id==""){
		 		
		 	}
		 	else if(id=="patientscaleup_btn"){
		 		patient_scale_up(year,month,pipeline);
		 	}
		 	
		 });
		 
		 //-----------------------------What happens when someone clicks a menu
		$(".patient_analysis_menus").click(function(){
			//Get Today's Date and Upto Saturday
			  var someDate = new Date();
			  var mm = ("0" + (someDate.getMonth() + 1)).slice(-2);
			  var y = someDate.getFullYear();
			  var def_pipeline=$("#nd_pr_pipeline").val();
			  //Load current month in months drop down
		  	  $(".nd_month").val(mm);
		  	  $(".nd_pipeline").val(1);
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
			}
			else if(id=="patient_scale_up_menu"){
				$("#tab2 .active").removeClass("active");
				$(this).addClass("active");
				//Change id of get button
				$(".patient_analysis_btn").attr("id","patientscaleup_btn");
				patient_scale_up(y,mm,def_pipeline);
			}
			
		});
		//-----------------------------What happens when someone clicks a menu end -------------
	});
	
	
	
	//Patient By Regimen
	function patient_by_regimen(year,month,pipeline){
		var patient_regimen_link="<?php echo base_url().'national_management/pa_patients_by_regimen/';?>"+year+'/'+month+'/'+pipeline;
		$('#chart_area_pr').load(patient_regimen_link);
	}
	
	//Patient Scale Up
	function patient_scale_up(year,month,pipeline){
		var patient_scaleup_link="<?php echo base_url().'national_management/pa_patients_scaleup/';?>"+year+'/'+month+'/'+pipeline;
		$('#chart_area_pr').load(patient_scaleup_link);
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
      <div class="row-fluid">
		  <div class="span12">
		    <div class="row-fluid">
		      <div class="span6 nd_commodity_a">
		        Stock Status
		      </div>
		      <div class="span6 nd_commodity_a">
		      	Consumption
		      </div>
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
      <div class="row-fluid">
		  <div class="span12 nd_facility_a">
		    Ordering sites lost/ summary service points
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