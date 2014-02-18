<style>
	.row{
		margin-left:0px;
	}
	#tab2 .three_block{
		height:50%;
	}
</style>
<script type="text/javascript">
	$(document).ready(function(){
		//If pipeline has just logged in, display pipeline upload page
		var check_login = "<?php echo $this->session->userdata("pipeline_upload")?>";
		if($.trim(check_login)==1){
			$("#tab5").hide();
			$("#tab8").show();
			$("#pu_menu").addClass("active");
			$("#ra_menu").removeClass("active");
			"<?php echo $this->session->unset_userdata("pipeline_upload")?>";
		}
		
		
		  $(".order_link").click(function() {
			var upload_type = $(this).attr("order_type");
			$("#modal_header").text(upload_type);
			$("#upload_type").val(upload_type);
			$("#inputIcon").val("");
		  }); 
		 
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
		 
		  var report_analysis_table_link="<?php echo base_url().'dashboard_management/reportSummary/table';?>";//Table Reporting Sites Summary
		  var report_analysis_link="<?php echo base_url().'dashboard_management/getReport';?>";
		  var report_analysis_summary_link="<?php echo base_url().'dashboard_management/reportSummary';?>";//ARV Sites
		  var chart_area_report_analysis_link = "<?php echo base_url().'dashboard_management/reportSummary/site_reporting';?>";//Reporting Sites Analysis
		
		
       $("#chart_area_report_summary").load(report_analysis_summary_link);
	   $("#chart_area_report").load(report_analysis_link);
	   $("#chart_area_report_analysis").load(chart_area_report_analysis_link);
       
       $("#report_summary_table").load(report_analysis_table_link);
       
       
       
	
		 
		 //Update breadcrumbs
		 var active_sub=$(".main_menu.active").find("a").text();
		 var active_menu=$(".commodity_analysis_menus.active").find("a").text();
		 $("#sub_active").text(active_sub);
		 $("#active_menu").text(active_menu);
		  //Get Today's Date and Upto Saturday
		  var someDate = new Date();
		  var dd = ("0" + someDate.getDate()).slice(-2);
		  var mm = ("0" + (someDate.getMonth() + 2)).slice(-2);//Next month
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
		  
		  //By default, load current month as default selected
		  var prev_m = ("0" + (someDate.getMonth() + 1)).slice(-2);
		  $(".nd_month").val(prev_m);
		  
		  //Load default chart
		  var def_month=$("#nd_ca_month").val();
		  var def_year=$("#nd_ca_year").val();
		  var def_pipeline=$("#nd_ca_pipeline").val();
		  $("#chart_area_ca").html('<div class="loadingDiv" style="margin:20% 0 20% 0;" ><img style="width: 30px;margin-left:50%" src="<?php echo asset_url().'img/loading_spin.gif' ?>"></div>');
		  
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
		 
		 
		  /*
		  * Click get button to generate charts end -----------------------------------
		  */
		 
		 /*
		  * What happens when someone clicks a menu -----------------------------------
		  */
		 $(".main_menu").click(function(){
		 	//Update breadcrumbs
		 	var selected_menu=$(this).find("a").text();
		 	$("#sub_active").text(selected_menu);
		 	
		 	var id=$(this).attr("id");
		 	var someDate = new Date();
			var dd = ("0" + someDate.getDate()).slice(-2);
			var prev_m = ("0" + (someDate.getMonth() + 0)).slice(-2);
			var y = someDate.getFullYear();
			//Commodity analysis
			if(id=="ca_menu"){
				//Update breadcrumbs
				var active_menu=$(".commodity_analysis_menus.active").find("a").text();
				$("#active_menu").text(active_menu);
		 		$("#chart_area_ca").html('<div class="loadingDiv" style="margin:0 auto;" ><img style="width: 30px;margin-left:50%" src="<?php echo asset_url().'img/loading_spin.gif' ?>"></div>');
				commodity_analysis();
		 	}
		 	else if(id=="pa_menu"){//Patient Analysis
		 		//Update breadcrumbs
				var active_menu=$(".patient_analysis_menus.active").find("a").text();
				$("#active_menu").text(active_menu);
		 		//patient_by_regimen(y,mm,1);
		 		allPatientAnalysis();
		 	}
		 	else if(id=="fa_menu"){
		 		//Update breadcrumbs
				var active_menu=$(".facility_analysis_menus.active").find("a").text();
				$("#active_menu").text(active_menu);
		 		ordering_site_summary(y,mm,1);
		 	}
		 	else if(id=="oa_menu"){
		 		//Update breadcrumbs
				var active_menu=$(".order_analysis_menus.active").find("a").text();
				$("#active_menu").text(active_menu);
		 		order_analysis(y,mm,1);
		 	}
		 	else if(id=="ra_menu"){
		 		  // if($("#chart_area_report_summary").children().length == 0){
		 		  	   $("#report_summary_table").load(report_analysis_table_link);
		 		   	   $("#chart_area_report_summary").load(report_analysis_summary_link);
				       $("#chart_area_report").load(report_analysis_link);
				       $("#chart_area_report_analysis").load(chart_area_report_analysis_link);
		 		  // }
		 		   
		 	}
		 })
		 /*
		  * What happens when someone clicks a menu end-----------------------------------
		  */
		 
		 //-----------------------------What happens when someone clicks a submenu
		 //Commodity analysis
		$(".commodity_analysis_menus").click(function(){
			
			//Update breadcrumbs
			var active_menu=$(this).find("a").text();
			$("#active_menu").text(active_menu);
			
			//Get Today's Date and Upto Saturday
			  var someDate = new Date();
			  var mm = ("0" + (someDate.getMonth() + 1)).slice(-2);
			  var y = someDate.getFullYear();
			  var def_pipeline=$("#nd_ca_pipeline").val();
			  //Load current month in months drop down
		  	  $("#nd_ca_month").val(mm);
		  	  $("#nd_ca_pipeline").val(1);
			  
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
			//Update breadcrumbs
			var active_menu=$(this).find("a").text();
			$("#active_menu").text(active_menu);
			
			//Get Today's Date and Upto Saturday
			  var someDate = new Date();
			  var mm = ("0" + (someDate.getMonth() + 1)).slice(-2);
			  var y = someDate.getFullYear();
			  var def_pipeline=$("#nd_pr_pipeline").val();
			  //Load current month in months drop down
		  	  $("#nd_pr_month").val(mm);
		  	  $("#nd_pr_pipeline").val(1);
			 
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
			 //Update breadcrumbs
			 var active_menu=$(this).find("a").text();
			 $("#active_menu").text(active_menu);
			 
			  var someDate = new Date();
			  var mm = ("0" + (someDate.getMonth() + 1)).slice(-2);
			  var y = someDate.getFullYear();
			  var def_pipeline=$("#nd_fa_pipeline").val();
			  //Load current month in months drop down
		  	  $("#nd_fa_month").val(mm);
		  	  $("#nd_fa_pipeline").val(1);
			  
			//Check which menu was selected
			var id=$(this).attr("id");
			var base_url=$("#base_url").val();
			//Patient By Regimen
			if(id=="ordering_site_list_menu"){
				$("#tab3 .active").removeClass("active");
				$(this).addClass("active");
				//Change id of get button
				$(".facility_analysis_btn").attr("id","orderingsite_l_btn");
				ordering_site_summary(y,mm,def_pipeline);
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
				service_point_summary(y,mm,def_pipeline);
			}
			else if(id=="service_point_sum_menu"){
				$("#tab3 .active").removeClass("active");
				$(this).addClass("active");
				//Change id of get button
				$(".facility_analysis_btn").attr("id","servicepoint_s_btn");
				service_point_summary(y,mm,def_pipeline);
			}
			
		});
		
		//Order Analysis
		$(".order_analysis_menus").click(function(){
			//Update breadcrumbs
			var active_menu=$(this).find("a").text();
			$("#active_menu").text(active_menu);
			
			  var someDate = new Date();
			  var mm = ("0" + (someDate.getMonth() + 1)).slice(-2);
			  var y = someDate.getFullYear();
			  var def_pipeline=$("#nd_fa_pipeline").val();
			  //Load current month in months drop down
		  	  $("#nd_oa_month").val(mm);
		  	  $("#nd_oa_pipeline").val(1);
			  order_analysis(y,mm,def_pipeline);
		});
		
		//-----------------------------What happens when someone clicks a menu end -------------
		function loadMonths(_year,current_year){
			//If year selected is different from current year,load all months, otherwise load till previous month
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
		
		//Reset css
		$(".navbar").css("margin-bottom","0");
	});
	
	//What happens when one clicks the generate button
	$(".generate").live("click",function(){
		//Check which generate button was clicked
		var id = $(this).attr("id");
		if(id=='rs_analysis_btn'){//If button is for reporting sites analysis
			$(".rs_period_display").text($("#nd_ra_period").val());
			$("#chart_area_report_analysis").html('<div class="loadingDiv" style="margin:20% 0 20% 0;" ><img style="width: 30px;margin-left:50%" src="<?php echo asset_url().'img/loading_spin.gif' ?>"></div>');
			var chart_area_report_analysis_link = "<?php echo base_url().'dashboard_management/reportSummary/site_reporting';?>";//Reporting Sites Analysis
			$("#chart_area_report_analysis").load(chart_area_report_analysis_link);
		}
		else if(id=='pa_bypipeline_btn'){//Patients on ART By Pipeline
			var period = $("#nd_pa_bypipeline_period").val();
			$(".pa_period_display").text(period);
			$("#ART_PATIENT_PIPELINE_graph").html('<div class="loadingDiv" style="margin:20% 0 20% 0;" ><img style="width: 30px;margin-left:50%" src="<?php echo asset_url().'img/loading_spin.gif' ?>"></div>');
			var art_bypipeline_link="<?php echo base_url().'dashboard_management/getPatients/BYPIPELINE_ART/';?>"+period;
			$("#ART_PATIENT_PIPELINE_graph").load(art_bypipeline_link);
			
		}
		else if(id=='adult_art_btn'){//Adult patients ON ART
			var period = $("#nd_adult_art_period").val();
			$(".ad_pa_period_display").text(period);
			$("#ART_ADULT_PATIENT_graph").html('<div class="loadingDiv" style="margin:20% 0 20% 0;" ><img style="width: 30px;margin-left:50%" src="<?php echo asset_url().'img/loading_spin.gif' ?>"></div>');
			var art_adult_patient_link="<?php echo base_url().'dashboard_management/getPatients/ADULT_ART/';?>"+period;
			$("#ART_ADULT_PATIENT_graph").load(art_adult_patient_link);
		}
		else if(id=='paed_art_btn'){//Adult patients ON ART
			var period = $("#nd_paed_art_period").val();
			$(".paed_pa_period_display").text(period);
			$("#ART_PAED_PATIENT_graph").html('<div class="loadingDiv" style="margin:20% 0 20% 0;" ><img style="width: 30px;margin-left:50%" src="<?php echo asset_url().'img/loading_spin.gif' ?>"></div>');
			var art_paed_patient_link="<?php echo base_url().'dashboard_management/getPatients/PAED_ART/';?>"+period;
			$("#ART_PAED_PATIENT_graph").load(art_adult_patient_link);
		}
		
	});
	
	/*
	 * Pipeline Upload
	 */
	//check which menu is clicked
	$(".main_menu").live("click",function(){
		var id = $(this).attr("id");
		if(id=="pu_menu"){
			$("#tab5").hide();
			$("#tab8").show();
		}
		else if(id=="ra_menu"){
			$("#tab8").hide();
			$("#tab5").show();
		}
		else{//Hide reporting analysis and pipeline upload tab
			$("#tab5").hide();
			$("#tab8").hide();
		}
    });
	/*
	 * Patient Analysis functions
	 */
	
	// Load patient analysis grids and graphs
	function allPatientAnalysis(){
		var art_patient_link="<?php echo base_url().'dashboard_management/getPatients/ART_PATIENT';?>";
	    var byregimen_patient_link="<?php echo base_url().'dashboard_management/getPatients/BYREG_PATIENT';?>";
	    var art_bypipeline_link="<?php echo base_url().'dashboard_management/getPatients/BYPIPELINE_ART';?>";
	    var art_adult_patient_link="<?php echo base_url().'dashboard_management/getPatients/ADULT_ART';?>";
	    var art_paed_patient_link="<?php echo base_url().'dashboard_management/getPatients/PAED_ART';?>";
		$("#ART_PATIENT_grid").load(art_patient_link, function() {
            $("#ART_PATIENT_listing").dataTable({
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
		});
		$("#BYREG_PATIENT_grid").load(byregimen_patient_link, function() {
            $("#BYREG_PATIENT_listing").dataTable({
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
       });
       $("#ART_PATIENT_PIPELINE_graph").load(art_bypipeline_link);
       $("#ART_ADULT_PATIENT_graph").load(art_adult_patient_link);
       $("#ART_PAED_PATIENT_graph").load(art_paed_patient_link);
	}
	
	
	/*
	 * Commodity Analysis functions
	 */
	function commodity_analysis(){
		  var soh_link="<?php echo base_url().'dashboard_management/getCommodity/SOH';?>";
		  var cons_link="<?php echo base_url().'dashboard_management/getCommodity/CONS';?>";
		  $("#SOH_grid").load(soh_link, function() {
	            $("#SOH_listing").dataTable({
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
	       });
	       
	       $("#CONS_grid").load(cons_link, function() {
	            $("#CONS_listing").dataTable({
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
	       });
	       
	       
	}
	
	/*
	 * Reporting Analysis Functions
	 */
	
	
	
</script>

<div class="tabbable national_dashboard_content" style="margin-top:1%"> <!-- Only required for left/right tabs -->
  <ul class="nav nav-tabs " style="width:60%; float: left">
  	<li id="ra_menu" class="active main_menu"><a href="#tab5" data-toggle="tab">Reporting Analysis</a></li>
  	<li id="pa_menu" class="main_menu"><a href="#tab2" data-toggle="tab">Patient Analysis</a></li>
    <li id="ca_menu" class="main_menu"><a href="#tab1" data-toggle="tab">Commodity Analysis</a></li>
    <li id="ca_menu" class="main_menu"><a href="#tab8" data-toggle="tab">EID Analysis</a></li>
    <li id="ca_menu" class="main_menu"><a href="#tab7" data-toggle="tab">Upload</a></li>
    
    <!-- <li id="fa_menu" class="main_menu"><a href="#tab3" data-toggle="tab">Facility Analysis</a></li> -->
    <!-- <li id="oa_menu" class="order_analysis_menus main_menu"><a href="#tab4" data-toggle="tab">Order Analysis</a></li>-->
    
  </ul>
  <div >
  	<ol id="nd_breadcrumb" class="breadcrumb" style="text-align: right">
	  <li><a href="#">National Dashboard</a><span class="divider">/</span></li>
	  <li><a id="sub_active" href="#"></a><span class="divider">/</span></li>
	  <li id="active_menu" class="active"></li>
	</ol>
  </div>
  <div class="tab-content nat_dashboard_rep" style="clear:left">
  	<!--Ordering-->
  	<div class="tab-pane" id="tab7">
  		<div class="container">
  			<?php echo $this->session->flashdata("order_message");?>
  			<div class="row-fluid" style="height:50%;">
  			<?php 
  			 if($this->session->userdata("upload_valid") !=""){
  			?>
  				<div class="span6">
  	                   		<h3>CDRR Upload</h3>
						      <div class="accordion-inner">
						        <a href="#modal_template" role="button" data-toggle="modal" class="order_link" order_type="D-CDRR"><i class="icon-upload"></i> D-CDRR for Central Sites</a>
						      </div>
						      <div class="accordion-inner">
						        <a href="#modal_template" role="button" data-toggle="modal" class="order_link" order_type="F-CDRR_packs"> <i class="icon-upload"></i> F-CDRR for Stand-alone Sites</a>
						      </div>
						        <h3>MAPS Upload</h3>
						      <div class="accordion-inner">
						        <a href="#modal_template" role="button" data-toggle="modal" class="order_link" order_type="D-MAPS"><i class="icon-upload"></i> D-MAPS for Central Sites</a>
						      </div>
						      <div class="accordion-inner">
						        <a href="#modal_template" role="button" data-toggle="modal" class="order_link" order_type="F-MAPS"><i class="icon-upload"></i> F-MAPS for Stand-alone Sites</a>
						      </div>
						     <!-- Modal -->
							<div id="modal_template" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
							<form id='modal_action' class="form-horizontal" method="post" enctype="multipart/form-data" action="<?php echo base_url()."order/import_order"?>">	
									<div class="modal-header">
										<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
											×
										</button>
										<h3 id="myModalLabel">Upload Order(<b><span id="modal_header"></span></b>)</h3>
									</div>
									<div class="modal-body">
										<div class="control-group">
										  <label class="control-label" for="inputIcon">Upload File <i class="icon-file"></i></label>
										  <div class="controls">
										      <input type="hidden"  name="upload_type" id="upload_type" />
				                              <input type="file"  name="file" size="30" id="inputIcon"  required="required" accept="application/vnd.ms-excel"/>
										  </div>
										</div>
									</div>
									<div class="modal-footer">
										<button class="btn" data-dismiss="modal" aria-hidden="true">
											Close
										</button>
										<button class="btn btn-primary">
											Upload
										</button>
									</div>
								</form>
							</div>
  				</div>
  			    <div class="span6" style="height:50%;">
						      <h3>CDRR Templates <i><img class="img-rounded" style="height:30px;" src="<?php echo base_url().'assets/img/excel.gif';?>"/> </i></h3>
						      <div class="accordion-inner">
						        <a href="<?php echo base_url().'downloads/D-CDRR for Central Sites and District Stores.xls' ;?>"><i class="icon-download-alt"></i> D-CDRR for Central Sites.xls</a>
						      </div>
						      <div class="accordion-inner">
						        <a href="<?php echo base_url().'downloads/F-CDRR for Standalone Sites.xls' ;?>"> <i class="icon-download-alt"></i> F-CDRR for Stand-alone Sites.xls</a>
						      </div>
						        <h3>MAPS Templates <i><img class="img-rounded" style="height:30px;" src="<?php echo base_url().'assets/img/excel.gif';?>"/> </i></h3>
						      <div class="accordion-inner">
						        <a href="<?php echo base_url().'downloads/D-MAPS for Central Sites and District Stores.xls' ;?>"><i class="icon-download-alt"></i> D-MAPS for Central Sites.xls</a>
						      </div>
						      <div class="accordion-inner">
						        <a href="<?php echo base_url().'downloads/F-MAPS for Standalone Sites.xls' ;?>"><i class="icon-download-alt"></i> F-MAPS for Stand-alone Sites.xls</a>
						      </div>
  			    </div><!--End of second Span-->
  			    <?php }else{?>
  			       <div class="span4">
	                       	<?php echo $this -> session -> flashdata('login_message');?>
							<?php echo form_open('order/authenticate_upload');?>
							<?php echo form_fieldset('', array('id' => 'login_legend'));?>
							<legend id="login_legend">
								<i class="fa fa-info-circle" style="padding-right:5px"></i>Upload Log In
							</legend>
							<?php echo $this -> session -> flashdata('error_message');?>
							    <div class="item">
								<?php echo form_error('email', '<div class="error_message">', '</div>');?>
								<?php echo form_label('Email Address:', 'username');?>
								<div class="input-group">
									<span class="input-group-addon"><i class="fa fa-user"></i></span>
									<?php echo form_input(array('type' => 'email', 'name' => 'email', 'required' => 'required', 'id' => 'email', 'size' => '24', 'class' => 'textfield form-control', 'placeholder' => 'mail@yourmail.com'));?>
								</div>
			                    </div>
			                    <div class="item">
								<?php echo form_error('password', '<div class="error_message">', '</div>');?>
								<?php echo form_label('Password:', 'password');?>
								<div class="input-group">
									<span class="input-group-addon"><i class="fa fa-key"></i></span>
									<?php echo form_password(array('name' => 'password', 'required' => 'required', 'id' => 'password', 'size' => '24', 'class' => 'textfield form-control', 'placeholder' => '********'));?>
								</div>
			                    </div>
	                        	<div style="margin-top:1em;">
	                        		<?php echo form_fieldset('', array('class' => 'tblFooters'));?>
								    <?php echo form_submit(array("name"=>'input_go',"class"=>'btn',"style"=>'width:20%;'), 'Go');?> 
								    <?php echo form_fieldset_close();?>
	                        	</div>
	                        </form>
		           </div>
  		       <?php }?>
  		   </div>
  		</div>
  		<!--
      <div class="two_block" id="s_consumption">
			<h3 class="dashboard_title">Order Upload</h3>
	  </div>-->
    </div>
  	<!-- Commodity Analysis -->
    <div class="tab-pane" id="tab1">
		<div class="row-fluid">
			<div class="two_block span6" id="s_consumption">
				<h3 class="dashboard_title">Stock Consumption</h3>
				<div id="CONS_grid"></div>
		    </div>
		    <div class="two_block span6" id="s_consumption">
				<h3 class="dashboard_title">Stock Status</h3>
				<div id="SOH_grid"></div>
		    </div>
		</div>
      
    </div>
    <!-- Patient Analysis -->
    <div class="tab-pane nat_dashboard_rep" id="tab2">
    	<div class="row-fluid">
		  <div class="three_block span4" id="patient_by_art_by_pipeline">
    		<h3 class="dashboard_title">Number of Patients on ART By Pipeline for <span class="pa_period_display"><?php echo date('F-Y');?></span></h3>
    		<div id="" class="nd_menus">
				<span>Select a period</span>
				<select id="nd_pa_bypipeline_period" class="nd_period nd_input_small">
					<?php foreach ($maps_report_period as $value) {
						echo "<option value='".date('F-Y',strtotime($value['period_begin']))."'>".date('F-Y',strtotime($value['period_begin']))."</option>";
					}?>
				</select>
				
				<button class="generate btn btn-warning nd_input_small" style="color:black" id="pa_bypipeline_btn">Get</button>
			</div>
			<hr size="2"><p></p>
    		<div id="ART_PATIENT_PIPELINE_graph"></div>
    	  </div>
    	  <div class="three_block span4" id="adult_patient_on_art">
    		<h3 class="dashboard_title">Current Adult Patients on ART as of <span class="ad_pa_period_display"><?php echo date('F-Y');?></span></h3>
    		<div id="" class="nd_menus">
				<span>Select a period</span>
				<select id="nd_adult_art_period" class="nd_period nd_input_small">
					<?php foreach ($maps_report_period as $value) {
						echo "<option value='".date('F-Y',strtotime($value['period_begin']))."'>".date('F-Y',strtotime($value['period_begin']))."</option>";
					}?>
				</select>
				
				<button class="generate btn btn-warning nd_input_small" style="color:black" id="adult_art_btn">Get</button>
			</div>
    		<div id="ART_ADULT_PATIENT_graph"></div>
    	  </div>
    	  <div class="three_block span4" id="paed_patient_on_art">
    		<h3 class="dashboard_title">Current Paedriatic Patients on ART as of <span class="paed_pa_period_display"><?php echo date('F-Y');?></span></h3>
    		<div id="" class="nd_menus">
				<span>Select a period</span>
				<select id="nd_paed_art_period" class="nd_period nd_input_small">
					<?php foreach ($maps_report_period as $value) {
						echo "<option value='".date('F-Y',strtotime($value['period_begin']))."'>".date('F-Y',strtotime($value['period_begin']))."</option>";
					}?>
				</select>
				
				<button class="generate btn btn-warning nd_input_small" style="color:black" id="paed_art_btn">Get</button>
			</div>
    		<div id="ART_PAED_PATIENT_graph"></div>
    	  </div>
		</div>
		<div class="row-fluid">
			<div class="three_block span4" id="patient_by_art" style="height:auto;">
	    		<h3 class="dashboard_title">Current Patients By ART Sites</h3>
	    		<div id="ART_PATIENT_grid"></div>
	    	</div>
	    	<div class="three_block span4" id="patient_by_regimen" style="height:auto;">
	    		<h3 class="dashboard_title">Patients By Regimen</h3>
	    		<div id="BYREG_PATIENT_grid"></div>
	    	</div>
	    	<div class="three_block span4" id="patient_scale_up" style="height:auto;">
	    		<h3 class="dashboard_title">Patients Scale Up</h3>
	    		<table class="table table-bordered table-striped tbl_nat_dashboard">
	    			<thead>
	    				
	    				<tr><th>Pipeline</th><th>Action</th></tr>
	    			</thead>
	    			<tbody>
	    				<tr><td>Kemsa</td><td><?php echo anchor('dashboard_management/download/PATIENT_SCALE/1/kemsa','<i class="icon-download-alt"></i>Download');?></td></tr>
	    				<tr><td>Kenya Pharma</td><td><?php echo anchor('dashboard_management/download/PATIENT_SCALE/1/kenya_pharma','<i class="icon-download-alt"></i>Download');?></td></tr>
	    			</tbody>
	    		</table>
	    		
	    	</div>
		</div>
    	
    </div>
    <!-- Reporting Analysis -->
    <div class="tab-pane nat_dashboard_rep active" id="tab5">
    	<div class="row-fluid">
    		<div class="two_block span6" id="">
	    		<h3 class="dashboard_title">ARV Sites</h3>
	    		
	    		<div id="chart_area_report_summary"></div>
	    	</div>
	    	<div class="two_block span6" id="">
	    		<h3 class="dashboard_title">Reporting Site Rates</h3>
	    		<div id="chart_area_report"></div>
	    	</div>
    	</div>
    	<div class="row-fluid">
    		<div class="two_block span6" id="">
	    		<h3 class="dashboard_title">Reporting Sites Analysis for <span class="rs_period_display"><?php echo  date('F-Y');?></span></h3>
	    			<div id="ra_menus" class="nd_menus">
	    				<span>Select a period</span>
						<select id="nd_ra_period" class="nd_period nd_input_small">
							<?php foreach ($report_period as $value) {
								echo "<option value='".date('F-Y',strtotime($value['period_begin']))."'>".date('F-Y',strtotime($value['period_begin']))."</option>";
							}?>
						</select>
						
						<button class="generate btn btn-warning nd_input_small" style="color:black" id="rs_analysis_btn">Get</button>
					</div>
					<hr size="2">
	    		<div id="chart_area_report_analysis"></div>
	    	</div>
	    	<div class="two_block span6" id="">
	    		<h3 class="dashboard_title">Reporting Sites Rate Summary for <?php echo  date('F-Y');?></h3>
	    		<div id="report_summary_table"></div>
	    	</div>
    	</div>
    	
    	
    </div>
    <!-- Reporting Analysis -->
    
    <div class="tab-pane nat_dashboard_rep" id="tab6">
    	<div class="two_block" id="">
    		<h3 class="dashboard_title">F-Maps</h3>
    	</div>
    	
    	<div class="two_block" id="">
    		<h3 class="dashboard_title">Cdrr</h3>
    	</div>
    </div>
    
    <!-- Facility Analysis -->
    <div class="tab-pane" id="tab3">
      <div class="navbar" style="width:100%">
		  <div class="navbar-inner">
		    <ul class="nav">
		      <li id="ordering_site_list_menu" class="active facility_analysis_menus"><a href="#">Ordering Sites List</a></li>
		      <!--<li id="ordering_site_sum_menu" class="facility_analysis_menus"><a  href="#">Ordering Sites Summary</a></li>-->
		      <li id="service_point_list_menu" class="facility_analysis_menus"><a  href="#">Service Points List</a></li>
		     <!-- <li id="service_point_sum_menu" class="facility_analysis_menus"><a  href="#">Service Points Summary</a></li>-->
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
					<div id="chart_area_fa" class="span6 os"></div>
					<div id="chart_area_fa_sum" class="span6 os"></div>
		  		</div>
		  </div>
		</div>
    </div>
    
    
    <div class="tab-pane nat_dashboard_rep" id="tab5">
    	<div class="three_block" id="patient_by_art">
    		<h3 class="dashboard_title">Current Patients By ART</h3>
    		<table class="table table-bordered table-striped tbl_nat_dashboard">
    			<thead>
    				<tr><th>No</th><th>Reporting Period</th><th>Pipeline</th><th>Action</th></tr>
    			</thead>
    			<tbody>
    				<tr><td>1</td><td>February - 2010</td><td>Kenya Pharma</td><td><a href="">Download</a></td></tr>
    				<tr><td>1</td><td>February - 2013</td><td>Kenya Pharma</td><td><a href="">Download</a></td></tr>
    				<tr><td>1</td><td>March - 2013</td><td>Kenya Pharma</td><td><a href="">Download</a></td></tr>
    				<tr><td>1</td><td>March - 2013</td><td>Kemsa</td><td><a href="">Download</a></td></tr>
    				<tr><td>1</td><td>June - 2013</td><td>Kemsa</td><td><a href="">Download</a></td></tr>
    				<tr><td>1</td><td>August - 2013</td><td>Kenya Pharma</td><td><a href="">Download</a></td></tr>
    				<tr><td>1</td><td>September - 2013</td><td>Kemsa</td><td><a href="">Download</a></td></tr>
    				<tr><td>1</td><td>December - 2013</td><td>Kenya Pharma</td><td><a href="">Download</a></td></tr>
    				<tr><td>1</td><td>December - 2012</td><td>Kemsa</td><td><a href="">Download</a></td></tr>
    				<tr><td>1</td><td>January - 2013</td><td>Kenya Pharma</td><td><a href="">Download</a></td></tr>
    				<tr><td>1</td><td>December - 2013</td><td>Kenya Pharma</td><td><a href="">Download</a></td></tr>
    				<tr><td>1</td><td>December - 2012</td><td>Kemsa</td><td><a href="">Download</a></td></tr>
    				<tr><td>1</td><td>January - 2013</td><td>Kenya Pharma</td><td><a href="">Download</a></td></tr>
    			</tbody>
    		</table>
    	</div>
    	<div class="three_block" id="patient_by_regimen">
    		<h3 class="dashboard_title">Patients By Regimen</h3>
    		
    	</div>
    	<div class="three_block" id="patient_scale_up">
    		<h3 class="dashboard_title">Patients Scale Up</h3>
    		
    	</div>
    </div>
    <!-- Pipelines Upload -->
    <div class="tab-pane nat_dashboard_rep" id="tab8">
    	<?php
    	if(!$this->session->userdata("pipeline_logged_in")){
    		$this ->load->view("dashboard/pipeline_login_v");
    	}
    	else{
    		?>
    	<div class="container" style="width: 40%">
	    	<div class="row-fluid">
	    		<div class="span12"><span >Welcome  <b><?php echo $this->session->userdata("pipeline_logged_in");?></b></span>, <?php echo anchor("user_management/logout/pipeline_logout","<i class='icon-off'></i>Logout");?></div>
			</div>
			<div class="row-fluid">
	    		<div class="span12">	
					<h3> Pipeline Upload</h3>
					<?php echo form_open_multipart("order/import_order/pipeline_upload");?>
					<input type="file" name="cms_file" id="cms_file" required="" accept="application/vnd.ms-excel" />
					<div class="control-group">
					    <div class="controls">
					      <button type="submit" class="btn btn-primary"><i class="icon-upload"></i>Upload File</button>
					    </div>
					</div>
					<?php echo form_close();?>
				</div>
			</div>
			<div class="row-fluid">	
				
				<div class="span12">	
					<h3>Central Medical Store and Pending Orders Template <i><img class="img-rounded" style="height:30px;" src="<?php echo base_url().'assets/img/excel.gif';?>"/> </i></h3>
					<?php echo anchor("assets/template/pipeline_upload_template.xls","<i class='icon-download-alt'></i>Central Medical Store and Pending Orders Template");?>
				</div>
	    	</div>
	    </div>	
    	<?php
    	}?>
    </div>
  </div>
</div>