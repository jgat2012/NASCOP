<style>
	.row{
		margin-left:0px;
	}
	#tab2 .three_block{
		height:50%;
	}
	.nat_dashboard_rep h3{
		font-size:0.8em;
	}
</style>
<script type="text/javascript">
	$(document).ready(function(){
		$(".forgot").click(function(){
			$("#forgot_frm").show();
			$("#login_frm").hide();
		});
		
		$(".login").click(function(){
			$("#forgot_frm").hide();
			$("#login_frm").show();
		});
		
		$(".password").click(function(){
			$("#order_frm").hide();
			$("#change_frm").show();
		});
		
		$(".home").click(function(){
			$("#order_frm").show();
			$("#change_frm").hide();
		});
		
		//Check default tab
		var type = "<?php if($this -> session -> userdata("tab_session") !=""){echo $this -> session -> userdata("tab_session");}else{echo "ra_menu";} ?>";
		$(".main_menu").removeClass("active");
		$("#"+type).addClass("active");
		var _function = $("#"+type).attr("data-function");//Call function to load data
		_function;
		var a_href = $("#"+type).find("a").attr("href");
		$(".tab-pane").hide();
		$(a_href).show();
		if(type=="pa_menu"){//Patient Analysis
	 		allPatientAnalysis();
	 	}
	 	else if(type=="ra_menu"){//Reporting Analysis
	 		  reporting_analysis();
	 	}
	 	else if(type=="ca_menu"){//Reporting Analysis
	 		  commodity_analysis();
	 	} 	
	 	else if(type=="eid_menu"){//Eid Analysis
	 		  eid_analysis();
	 	}
	 	else if(type=="two_p_menu"){//Two pager
	 		  two_pager();
	 	}else if(type=="county_r_menu"){//County report
			  county_report();
		}
		//When one clicks tab, keep it in session
		$(".main_menu").live("click",function(){
			$(".tab-pane").hide();
			//Update breadcrumbs
		 	var selected_menu=$(this).find("a").text();
		 	$("#sub_active").text(selected_menu);
		 	var id=$(this).attr("id");
		 	var someDate = new Date();
			var dd = ("0" + someDate.getDate()).slice(-2);
			var prev_m = ("0" + (someDate.getMonth() + 0)).slice(-2);
			var y = someDate.getFullYear();
			
			
			
			var url = "<?php echo base_url().'dashboard_management/set_tab_session';?>";
			var tab_id = $(this).attr("id");
			$.ajax({
				url : url,
				type : 'POST',
				data: {"tab_id":tab_id},
				success : function(data) {
					var type = data;
					var a_href = $(type).find("a").attr("href");
					$(a_href).show();
					if(id=="pa_menu"){//Patient Analysis
				 		//Update breadcrumbs
						var active_menu=$(".patient_analysis_menus.active").find("a").text();
						$("#active_menu").text(active_menu);
				 		//patient_by_regimen(y,mm,1);
				 		allPatientAnalysis();
				 	}
				 	else if(id=="ra_menu"){//Reporting Analysis
				 		  reporting_analysis();
				 	}
				 	else if(id=="ca_menu"){//Reporting Analysis
				 		  commodity_analysis();
				 	}
				 	else if(id=="eid_menu"){//Eid Analysis
	 		              eid_analysis();
	 	            }
	 	            else if(id=="two_p_menu"){//Two pager
	 	            	 two_pager();
	 	            }
	 	            else if(id=="county_r_menu"){//County report
	 	            	 county_report();
				}
				}
			});
			
			
		});
		
		
		
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
			var county = $("#nd_adult_art_county").val();
			var facility = $("#nd_adult_art_facility").val();
			$(".ad_pa_period_display").text(period);
			$("#ART_ADULT_PATIENT_graph").html('<div class="loadingDiv" style="margin:20% 0 20% 0;" ><img style="width: 30px;margin-left:50%" src="<?php echo asset_url().'img/loading_spin.gif' ?>"></div>');
			var art_adult_patient_link="<?php echo base_url().'dashboard_management/adult_patients/';?>"+period+"/"+facility+"/"+county;
			$("#ART_ADULT_PATIENT_graph").load(art_adult_patient_link);
		}
		else if(id=='paed_art_btn'){//Adult patients ON ART
			var period = $("#nd_paed_art_period").val();
			var county = $("#nd_paed_art_county").val();
			var facility = $("#nd_paed_art_facility").val();
			$(".paed_pa_period_display").text(period);
			$("#ART_PAED_PATIENT_graph").html('<div class="loadingDiv" style="margin:20% 0 20% 0;" ><img style="width: 30px;margin-left:50%" src="<?php echo asset_url().'img/loading_spin.gif' ?>"></div>');
			var art_paed_patient_link="<?php echo base_url().'dashboard_management/paed_patients/';?>"+period+"/"+facility+"/"+county;
			$("#ART_PAED_PATIENT_graph").load(art_paed_patient_link);
		}else if(id=="rs_satellite_btn"){
			var period = $("#satellite_period").val();
			$("#chart_area_report_analysis").html('<div class="loadingDiv" style="margin:20% 0 20% 0;" ><img style="width: 30px;margin-left:50%" src="<?php echo asset_url().'img/loading_spin.gif' ?>"></div>');
			var satellite_link="<?php echo base_url().'dashboard_management/reportingSatellites/';?>"+period;
			$("#chart_area_report_analysis").load(satellite_link);
		}else if(id=="rs_ordering_btn"){
			var period = $("#ordering_period").val();
			$("#report_summary_table").html('<div class="loadingDiv" style="margin:20% 0 20% 0;" ><img style="width: 30px;margin-left:50%" src="<?php echo asset_url().'img/loading_spin.gif' ?>"></div>');
			var ordering_link="<?php echo base_url().'dashboard_management/reportSummary/table/';?>"+period;
			$("#report_summary_table").load(ordering_link);
		}else if(id=="eid_source_btn"){
		  var eid_period=$("#source_eid_period").val();
		  var eid_county=$("#source_eid_county").val();
		  var eid_facility=$("#source_eid_facility").val();
	  	  var chart_area_eid_source_link = "<?php echo base_url().'dashboard_management/eid/source/';?>"+eid_period+"/"+eid_facility+"/"+eid_county;
          $("#chart_area_eid_source").load(chart_area_eid_source_link);
		}else if(id=="eid_comparison_btn"){
		  var eid_period=$("#comparison_eid_period").val();
		  var eid_county=$("#comparison_eid_county").val();
		  var eid_facility=$("#comparison_eid_facility").val();
	  	  var chart_area_eid_source_link = "<?php echo base_url().'dashboard_management/eid/comparison/';?>"+eid_period+"/"+eid_facility+"/"+eid_county;
          $("#chart_area_eid_comparison").load(chart_area_eid_source_link);
		}else if(id=="eid_retention_btn"){
		  var eid_period=$("#retention_eid_period").val();
		  var eid_range=$("#retention_eid_range").val();
		  var eid_facility=$("#retention_eid_facility").val();
	  	  var chart_area_eid_source_link = "<?php echo base_url().'dashboard_management/eid/retention/';?>"+eid_period+"/"+eid_facility+"/"+eid_range;
          $("#chart_area_eid_retention").load(chart_area_eid_source_link);
		}else if(id=="btn_download_county_report"){//Button to download county reports
			var period = $("#sel_period_cr").val();
			var county = $("#sel_county_cr").val();
			var link   = "<?php echo base_url().'dashboard_management/download/county_report/';?>"+period+"/0/"+county;
			window.location = link;
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
	    var art_adult_patient_link="<?php echo base_url().'dashboard_management/adult_patients';?>";
	    var art_paed_patient_link="<?php echo base_url().'dashboard_management/paed_patients';?>";
		$("#ART_PATIENT_grid").load(art_patient_link, function() {
            var oTable1=$("#ART_PATIENT_listing").dataTable({
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
			oTable1.fnSort([[0,'desc']]);
		});
		$("#BYREG_PATIENT_grid").load(byregimen_patient_link, function() {
            var oTable2=$("#BYREG_PATIENT_listing").dataTable({
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
			oTable2.fnSort([[0,'desc']]);
       });
       $("#ART_PATIENT_PIPELINE_graph").load(art_bypipeline_link);
       $("#ART_ADULT_PATIENT_graph").load(art_adult_patient_link);
       $("#ART_PAED_PATIENT_graph").load(art_paed_patient_link);
	}
	
	
	
	/*
	 * Commodity Analysis functions
	 */
	function commodity_analysis(){
		  //var soh_link="<?php echo base_url().'dashboard_management/getCommodity/SOH';?>";
		  var mos_link="<?php echo base_url().'dashboard_management/getCommodity/MOS';?>";
		  var cons_link="<?php echo base_url().'dashboard_management/getCommodity/CONS';?>";
		  /*$("#SOH_grid").load(soh_link, function() {
	           var oTable1= $("#SOH_listing").dataTable({
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
			    oTable1.fnSort([[0,'desc']]);
	       });*/
	       $("#MOS_grid").load(mos_link, function() {
	           var oTable1= $("#MOS_listing").dataTable({
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
			    oTable1.fnSort([[0,'desc']]);
	       });
	       
	       $("#CONS_grid").load(cons_link, function() {
	            var oTable2=$("#CONS_listing").dataTable({
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
			    oTable2.fnSort([[0,'desc']]);
	       });
	       
	       
	}
	
	/*
	 * Reporting Analysis Functions
	 */
	
	function reporting_analysis(){
		var report_analysis_table_link="<?php echo base_url().'dashboard_management/reportSummary/table';?>";//Table Reporting Sites Summary
		var report_analysis_link="<?php echo base_url().'dashboard_management/getReport';?>";
	  	var report_analysis_summary_link="<?php echo base_url().'dashboard_management/reportSummary';?>";//ARV Sites
	  	var chart_area_report_analysis_link = "<?php echo base_url().'dashboard_management/reportingSatellites';?>";//Reporting Sites Analysis
	    $("#report_summary_table").load(report_analysis_table_link);
   	    $("#chart_area_report_summary").load(report_analysis_summary_link);
        $("#chart_area_report").load(report_analysis_link);
        $("#chart_area_report_analysis").load(chart_area_report_analysis_link);
	}
	function eid_analysis(){
	  	var chart_area_eid_source_link = "<?php echo base_url().'dashboard_management/eid/source';?>";
	  	var chart_area_eid_comaprison_link= "<?php echo base_url().'dashboard_management/eid/comparison';?>";
        var chart_area_eid_summary_link="<?php echo base_url().'dashboard_management/eid/summary';?>";
        var chart_area_eid_retention_link="<?php echo base_url().'dashboard_management/eid/retention';?>";
        
        $("#chart_area_eid_source").load(chart_area_eid_source_link);
        $("#chart_area_eid_comparison").load(chart_area_eid_comaprison_link);
        $("#chart_area_eid_retention").load(chart_area_eid_retention_link);
        $("#eid_summary").load(chart_area_eid_summary_link);
	}
	function two_pager(){
		$("#two_pager_area").html('<div class="loadingDiv" style="margin:20% 0 20% 0;" ><img style="width: 30px;margin-left:50%" src="<?php echo asset_url().'img/loading_spin.gif' ?>"></div>');
		
	  	var two_pager_link = "<?php echo base_url().'dashboard_management/two_pager';?>";
        $("#two_pager_area").load(two_pager_link,function(){
        	
        	$("#TWO_PAGER_listing").dataTable({
		 		"bJQueryUI" : true,
				"sPaginationType" : "full_numbers",
				"sDom" : '<"H"Tfr>t<"F"ip>',
				"oTableTools" : {
					"sSwfPath" : base_url + "scripts/datatable/copy_csv_xls_pdf.swf",
					"aButtons" : ["copy", "print", "xls", "pdf"]
				},
				"bProcessing" : true,
				"bServerSide" : false,
			}).fnSort([[0,'desc']]);
        });
        
	}
	
	function county_report(){
		$("#sel_period_cr").html("<option value=''>Loading Period...</option>");
		var load_period = "<?php echo base_url().'dashboard_management/county_report/period';?>";
		$("#sel_period_cr").load(load_period,function(){//After loading period, load counties
        	$("#sel_county_cr").html("<option value=''>Loading Counties...</option>");
        	var load_counties = "<?php echo base_url().'dashboard_management/county_report/counties';?>";
        	$("#sel_period_cr").load(load_period,function(){//After loading period, load counties
	        	$("#sel_county_cr").load(load_counties,function(){
	        		 $('body').prepend( $('<link rel="stylesheet" type="text/css" />').attr('href', '<?php echo base_url() ?>assets/CSS/select2-3.4.8/select2.css') );
					 $.getScript( "<?php echo base_url();?>assets/js/select2-3.4.8/select2.js",function(){
					 	$("#sel_period_cr").select2({
					 		width: 'resolve',
					 		placeholder: "Select a Period",
    						allowClear: true
					 	});
					 	$("#sel_county_cr").select2({
					 		width: 'resolve',
					 		placeholder: "Select a County",
    						allowClear: true
					 	});
					 });
	        	});
	        });
        });
		
	}
	$(document).ready(function(){
		
	})
</script>

<div class="tabbable national_dashboard_content" style="margin-top:2%"> <!-- Only required for left/right tabs -->
  <ul class="nav nav-tabs " style="width:80%; float: left">
  	<li id="ra_menu" class="active main_menu"><a href="#tab5" data-toggle="tab">Reporting Analysis</a></li>
  	<li id="pa_menu" class="main_menu" ><a href="#tab2" data-toggle="tab">Patient Analysis</a></li>
    <li id="ca_menu" class="main_menu" ><a href="#tab1" data-toggle="tab">Commodity Analysis</a></li>
    <li id="eid_menu" class="main_menu" ><a href="#tab8" data-toggle="tab">EID Analysis</a></li>
    <li id="up_menu" class="main_menu" ><a href="#tab7" data-toggle="tab">Ordering Upload</a></li>
    <li id="two_p_menu" class="main_menu" ><a href="#tab6" data-toggle="tab">2 Pager Download</a></li>
    <li id="county_r_menu" class="main_menu" ><a href="#tab3" data-toggle="tab">County Report</a></li>
  </ul>
  <div>
  	<ol id="nd_breadcrumb" class="breadcrumb" style="text-align: right">
	  <li><a href="<?php echo base_url();?>">National Dashboard</a><span class="divider">/</span></li>
	  <li><a id="sub_active" href="#"></a><span class="divider">/</span></li>
	  <li id="active_menu" class="active"></li>
	</ol>
  </div>

    <div class="tab-content nat_dashboard_rep" style="clear:left">
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
		    		<h3 class="dashboard_title">Reporting Satellite Sites Summary for 
							<select id="satellite_period" class="nd_period nd_input_small span3">
								<?php foreach ($report_period as $value) {
									echo "<option value='".date('F-Y',strtotime($value['period_begin']))."'>".strtoupper(date('F-Y',strtotime($value['period_begin'])))."</option>";
								}?>
							</select>
							<button class="generate btn btn-warning" style="color:black" id="rs_satellite_btn">Get</button>
						</h3>
						<hr size="2">
		    		<div id="chart_area_report_analysis"></div>
		    	</div>
		    	<div class="two_block span6" id="">
		    		<h3 class="dashboard_title">Reporting Ordering Sites Rate Summary for 
							<select id="ordering_period" class="nd_period nd_input_small span3">
								<?php foreach ($report_period as $value) {
									echo "<option value='".date('F-Y',strtotime($value['period_begin']))."'>".strtoupper(date('F-Y',strtotime($value['period_begin'])))."</option>";
								}?>
							</select>
							
							<button class="generate btn btn-warning" style="color:black" id="rs_ordering_btn">Get</button>
					</h3>
					<hr size="2">
		    		<div id="report_summary_table"></div>
		    	</div>
	    	</div>
	    </div>
	    <!-- Patient Analysis -->
	    <div class="tab-pane nat_dashboard_rep" id="tab2">
	    	<div class="row-fluid">
			  <div class="two_block span4" id="patient_by_art_by_pipeline">
	    		<h3 class="dashboard_title">Number of Patients on ART By Pipeline<br/> For
					<select id="nd_pa_bypipeline_period" class="nd_period nd_input_small span4">
						<?php foreach ($maps_report_period as $value) {
							echo "<option value='".date('F-Y',strtotime($value['period_begin']))."'>".date('F-Y',strtotime($value['period_begin']))."</option>";
						}?>
					</select>	
					<button class="generate btn btn-warning" style="color:black" id="pa_bypipeline_btn">Get</button>
					</h3>
				<hr size="2"><p></p>
	    		<div id="ART_PATIENT_PIPELINE_graph"></div>
	    	  </div>
	    	  <div class="three_block span4" id="adult_patient_on_art">
	    		<h3 class="dashboard_title">Current Adult Patients on ART as of 
					<select id="nd_adult_art_period" class="nd_period nd_input_small span4">
						<?php foreach ($maps_report_period as $value) {
							echo "<option value='".date('F-Y',strtotime($value['period_begin']))."'>".date('F-Y',strtotime($value['period_begin']))."</option>";
						}?>
					</select>
					<br/>
					<span>County</span>
					<select id="nd_adult_art_county" class="nd_period nd_input_small span3">
						<option value="0">All</option>
						<?php foreach ($county_period as $index=>$value) {
							echo "<option value='".$index."'>".$value."</option>";
						}?>
					</select>
					<span>Facility</span>
					<select id="nd_adult_art_facility" class="nd_period nd_input_small span4">
						<option value="0">All</option>
						<?php foreach ($facility_period as $index=>$value) {
							echo "<option value='".$index."'>".$value."</option>";
						}?>
					</select>
					
					<button class="generate btn btn-warning" style="color:black" id="adult_art_btn">Get</button>
				</h3>
	    		<div id="ART_ADULT_PATIENT_graph"></div>
	    	  </div>
	    	  <div class="three_block span4" id="paed_patient_on_art">
	    		<h3 class="dashboard_title">Current Paedriatic Patients on ART as of 
					<select id="nd_paed_art_period" class="nd_period nd_input_small span4">
						<?php foreach ($maps_report_period as $value) {
							echo "<option value='".date('F-Y',strtotime($value['period_begin']))."'>".date('F-Y',strtotime($value['period_begin']))."</option>";
						}?>
					</select>
					<br/>
					<span>County</span>
					<select id="nd_paed_art_county" class="nd_period nd_input_small span3">
						<option value="0">All</option>
						<?php foreach ($county_period as $index=>$value) {
							echo "<option value='".$index."'>".$value."</option>";
						}?>
					</select>
					<span>Facility</span>
					<select id="nd_paed_art_facility" class="nd_period nd_input_small span4">
						<option value="0">All</option>
						<?php foreach ($facility_period as $index=>$value) {
							echo "<option value='".$index."'>".$value."</option>";
						}?>
					</select>
					<button class="generate btn btn-warning" style="color:black" id="paed_art_btn">Get</button>
				</h3>
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
	    <!-- Commodity Analysis -->
	    <div class="tab-pane" id="tab1">
			<div class="row-fluid">
				<div class="two_block span6" id="s_consumption">
					<h3 class="dashboard_title">Stock Consumption</h3>
					<div id="CONS_grid"></div>
			    </div>
			    <div class="two_block span6" id="s_consumption">
					<h3 class="dashboard_title">Facility MOS</h3>
					<div id="MOS_grid"></div>
			    </div>
			</div>
		</div>
		<!--EID Analysis-->
	    <div class="tab-pane nat_dashboard_rep active" id="tab8">
	    	<div class="row-fluid">
	    		  <div class="two_block span4">
		    		<h3 class="dashboard_title">EID Source:
		    			Period
		    		<select id="source_eid_period" class="nd_period nd_input_small span4">
						<?php foreach ($eid_period as $value) {
							echo "<option value='".date('F-Y',strtotime($value['period_begin']))."'>".date('F-Y',strtotime($value['period_begin']))."</option>";
						}?>
					</select>
					<br/>
		    		<span>County</span>
					<select id="source_eid_county" class="nd_period nd_input_small span3">
						<option value="0">All</option>
						<?php foreach ($eid_county as $index=>$value) {
							echo "<option value='".$index."'>".$value."</option>";
						}?>
					</select>
					<span>Facility</span>
					<select id="source_eid_facility" class="nd_period nd_input_small span4">
						<option value="0">All</option>
						<?php foreach ($eid_facility as $index=>$value) {
							echo "<option value='".$index."'>".$value."</option>";
						}?>
					</select>
					
					<button class="generate btn btn-warning" style="color:black" id="eid_source_btn">Get</button>	
		    		</h3>   
		    		<div id="chart_area_eid_source"></div>
		    	</div>

		    	<div class="two_block span4">
		    		<h3 class="dashboard_title">WebADT/EID Comparison:
		    		Period
		    		<select id="comparison_eid_period" class="nd_period nd_input_small span4">
						<?php foreach ($eid_adt_period as $value) {
							echo "<option value='".date('F-Y',strtotime($value['period_begin']))."'>".date('F-Y',strtotime($value['period_begin']))."</option>";
						}?>
					</select>
					<br/>	
		    		<span>County</span>
					<select id="comparison_eid_county" class="nd_period nd_input_small span3">
						<option value="">All</option>
						<?php foreach ($eid_adt_county as $index=>$value) {
							echo "<option value='".$index."'>".$value."</option>";
						}?>
					</select>
					<span>Facility</span>
					<select id="comparison_eid_facility" class="nd_period nd_input_small span4">
						<option value="">All</option>
						<?php foreach ($eid_adt_facility as $index=>$value) {
							echo "<option value='".$index."'>".$value."</option>";
						}?>
					</select>
					
					<button class="generate btn btn-warning" style="color:black" id="eid_comparison_btn">Get</button>	
		    			
		    		</h3>
		    		<div id="chart_area_eid_comparison"></div>
		    	</div>
		    	<div class="two_block span4">
					<h3 class="dashboard_title">EID Retention:Period
		    		<select id="retention_eid_period" class="nd_period nd_input_small span4">
						<?php foreach ($eid_adt_period as $value) {
							echo "<option value='".date('F-Y',strtotime($value['period_begin']))."'>".date('F-Y',strtotime($value['period_begin']))."</option>";
						}?>
					</select>
					<br/>	
					<span>Facility</span>
					<select id="retention_eid_facility" class="nd_period nd_input_small span4">
						<option value="">All</option>
						<?php foreach ($eid_adt_facility as $index=>$value) {
							echo "<option value='".$index."'>".$value."</option>";
						}?>
					</select>
					<span>Period</span>
					<select id="retention_eid_range" class="nd_period nd_input_small span3">
						<option value="90">3 Months</option>
						<option value="180">6 Months</option>
						<option value="360">1 Year</option>
					</select>
					
					<button class="generate btn btn-warning" style="color:black" id="eid_retention_btn">Get</button>
					</h3>
					<div id="chart_area_eid_retention"></div>
		    	</div>
	    	</div>
	    	<div class="row-fluid">
	    		<div class="three_block span12">
		    		<div class="table-responsive">
		    			<table class="table table-bordered table-hover table-condensed table-striped">
		    				<thead style="background: #2B597E;color: #FFF;font-weight:bold;font-size:0.8em;">
		    					<tr>
		    						<td rowspan="3">
		    						 Unit of interest <br/>
		    						 <span>County</span>
					<select id="comparison_eid_county" class="nd_period nd_input_small span3">
						<option value="">All</option>
						<?php foreach ($eid_adt_county as $index=>$value) {
							echo "<option value='".$index."'>".$value."</option>";
						}?>
					</select>
					<span>Facility</span>
					<select id="comparison_eid_facility" class="nd_period nd_input_small span4">
						<option value="">All</option>
						<?php foreach ($eid_adt_facility as $index=>$value) {
							echo "<option value='".$index."'>".$value."</option>";
						}?>
					</select>
					<button class="generate btn btn-warning" style="color:black" id="eid_comparison_btn">Get</button>	
		    						</td>
		    						<td>Period</td>
		    						<td colspan="2">EID Data</td>
		    						<td>Service Data DHIS</td>
		    						<td colspan="4">Actual Patient and Commodity data</td>
		    					</tr>
		    					<tr>
		    						<td rowspan="2">2013</td>
		    						<td rowspan="2">EID Positives</td>
		    						<td rowspan="2">EID Enrolled</td>
		    						<td rowspan="2">Patients Enrolled(via PMTCT)</td>
		    						<td>Patient Scale Up</td>
		    						<td>Patient Scale Up</td>
		    						<td>Commodity Scale Up</td>
		    						<td>Commodity Scale Up</td>
		    					</tr>
		    					<tr>
		    					<td>All Paeds</td>	
		    					<td>vs. ABC/NVP peads</td>	
		    					<td>PMTCT exit NVP</td>	
		    					<td>Peads Entry: ABC</td>	
		    					</tr>
		    				</thead>
		    				<tbody id="eid_summary">
		    					
		    				</tbody>
		    			</table>
		    		</div>
		    	</div>
	    	</div>
	    </div>
	  	<!--Ordering Upload-->
	  	<div class="tab-pane" id="tab7">
	  		<div class="container">
	  			<div class="row-fluid" style="height:50%;">
	  			<?php 
	  			 if($this->session->userdata("upload_valid") !=""){
	  			?>
	  			<div id="order_frm">
	  				<div class="row-fluid">
	  					<div class="span6">
	  						<?php if($this -> session -> flashdata('login_message') !=""){?>
	                		  	<div class="alert alert-info">
								    <button type='button' class='close' data-dismiss='alert'>&times;</button>
								     <?php echo $this -> session -> flashdata('login_message');?>
								</div>
								<?php }?>
	  					</div>
	  				 <div class="span6">
	  			      	<label style="float:right;border 1px solid #000;">Welcome <b class='home'><?php echo $this->session->userdata("order_user"); ?></b>
	  			      	<div class="dropdown" style="display:inline;">
						  <a class="dropdown-toggle" data-toggle="dropdown" href="#"><i class="icon-wrench"></i></a>
						  <ul class="dropdown-menu" role="menu" aria-labelledby="dLabel">
						  	<li><a tabindex="-1" href='<?php echo base_url()."order/show_log"; ?>'>upload log</a></li>
						    <li><a tabindex="-1" href='#' class="password">change password</a></li>
						  </ul>
						</div>
						<a href='<?php echo site_url("order/upload_logout"); ?>'>logout</a>
						</label>			
	                 </div>
	                 </div>
	                <div class="row-fluid">
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
												
											</button>
											<h3 id="myModalLabel">Upload Order(<b><span id="modal_header"></span></b>)</h3>
										</div>
										<div class="modal-body">
											<span class="alert-info">Kindly make sure that the file your are uploading is in 1997-2003 excel format(e.g: example.xls)</span>
											<br>
											<div class="control-group">
											  <label class="control-label" for="inputIcon">Upload File <i class="icon-file"></i></label>
											  <div class="controls">
											      <input type="hidden"  name="upload_type" id="upload_type" />
					                              <input type="file"  name="file[]" multiple="multiple" size="30" id="inputIcon"  required="required" accept="application/vnd.ms-excel"/>
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
	  			    </div>
	  			    </div>
	  			    <div id="change_frm" style="display:none;">
	  				<div class="row-fluid">
	  				 <div class="span12">
	  			      	<label style="float:right;border 1px solid #000;">Welcome <b class='home'><?php echo $this->session->userdata("order_user"); ?></b>
	  			      	<div class="dropdown" style="display:inline;">
						  <a class="dropdown-toggle" data-toggle="dropdown" href="#"><i class="icon-wrench"></i></a>
						  <ul class="dropdown-menu" role="menu" aria-labelledby="dLabel">
						    <li><a tabindex="-1" href='#' class="password">change password</a></li>
						  </ul>
						</div>
						<a href='<?php echo site_url("order/upload_logout"); ?>'>logout</a>
						</label>			
	                 </div>
	                 </div>
	                <div class="row-fluid">
	                	<div class="span6">
								<?php echo form_open('order/upload_password');?>
								<?php echo form_fieldset('', array('id' => 'login_legend'));?>
								<legend id="login_legend">
									<i class="fa fa-info-circle" style="padding-right:5px"></i>Upload Change Password
								</legend>
								<?php echo $this -> session -> flashdata('error_message');?>
									<div class="item">
									<?php echo form_error('current_password', '<div class="error_message">', '</div>');?>
									<?php echo form_label('Current Password:', 'current_password');?>
									<div class="input-group">
										<span class="input-group-addon"><i class="fa fa-key"></i></span>
										<?php echo form_password(array('name' => 'current_password', 'required' => 'required', 'id' => 'current_password', 'size' => '24', 'class' => 'textfield form-control', 'placeholder' => '********'));?>
									</div>
				                    </div>
								   	<div class="item">
									<?php echo form_error('new_password', '<div class="error_message">', '</div>');?>
									<?php echo form_label('New Password:', 'new_password');?>
									<div class="input-group">
										<span class="input-group-addon"><i class="fa fa-key"></i></span>
										<?php echo form_password(array('name' => 'new_password', 'required' => 'required', 'id' => 'new_password', 'size' => '24', 'class' => 'textfield form-control', 'placeholder' => '********'));?>
									</div>
				                    </div>
				                    <div class="item">
									<?php echo form_error('confirm_password', '<div class="error_message">', '</div>');?>
									<?php echo form_label('Confirm Password:', 'confirm_password');?>
									<div class="input-group">
										<span class="input-group-addon"><i class="fa fa-key"></i></span>
										<?php echo form_password(array('name' => 'confirm_password', 'required' => 'required', 'id' => 'confirm_password', 'size' => '24', 'class' => 'textfield form-control', 'placeholder' => '********'));?>
									</div>
				                    </div>
		                        	<div style="margin-top:1em;">
		                        		<?php echo form_fieldset('', array('class' => 'tblFooters'));?>
									    <?php echo form_submit(array("name"=>'input_go',"class"=>'btn',"style"=>'width:20%;'), 'Save');?> 
									    <?php echo form_fieldset_close();?>
		                        	</div>
		                        </form>
	                	</div>
	                </div>
	                </div>
	  			    <?php }else{?>
	  			       <div class="span4" id="login_frm">
	  			                <?php if($this -> session -> flashdata('login_message') !=""){?>
	  			       			<div class="alert alert-info">
								    <button type='button' class='close' data-dismiss='alert'>&times;</button>
								     <?php echo $this -> session -> flashdata('login_message');?>
								</div>
								<?php } echo form_open('order/upload_authenticate');?>
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
				                    <div class="item">
				                    	<div class="input-group">
				                    	<strong><a href="#" class="forgot" >Forgot Password?</a></strong>
				                        </div>
				                    </div>
		                        	<div style="margin-top:1em;">
		                        		<?php echo form_fieldset('', array('class' => 'tblFooters'));?>
									    <?php echo form_submit(array("name"=>'input_go',"class"=>'btn',"style"=>'width:20%;'), 'Go');?> 
									    <?php echo form_fieldset_close();?>
		                        	</div>
		                        </form>
			           </div>
			           <div class="span4" id="forgot_frm" style="display:none;">
								<?php echo form_open('order/Upload_forgot');?>
								<?php echo form_fieldset('', array('id' => 'login_legend'));?>
								<legend id="login_legend">
									<i class="fa fa-info-circle" style="padding-right:5px"></i>Upload Forgot Password
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
				                    	<div class="input-group">
				                    	<strong><a href="#" class="login">Back to Login</a></strong></strong>
				                        </div>
				                    </div>
		                        	<div style="margin-top:1em;">
		                        		<?php echo form_fieldset('', array('class' => 'tblFooters'));?>
									    <?php echo form_submit(array("name"=>'input_go',"class"=>'btn',"style"=>'width:30%;'), 'Submit');?> 
									    <?php echo form_fieldset_close();?>
		                        	</div>
		                        </form>
			           </div>
	  		       <?php }?>
	  		   </div>
	  		</div>
	    </div>
	    <!-- 2 Pager Download -->
	    <div class="tab-pane nat_dashboard_rep" id="tab6">
	    	<div class="container-fluid" style="width: 50%; margin: 0 auto">
	  			<div class="row-fluid" >
	  				<div class="span12">
	  					<h3 class="dashboard_title">Kenya Anti-Retroviral medicines (ARVs) Stock Situation</h3>
			    		<div id="two_pager_area"></div>
			    	</div>
	  			</div>
			</div>  
			
	    </div> 
	    <!-- County Report -->
	    <div class="tab-pane nat_dashboard_rep" id="tab3">
	    	<div class="container-fluid">
	  			<div class="row-fluid" >
	  				<div class="span6">
	  					<h3 class="dashboard_title"></h3>
			    		<div id="county_report_area_graph"></div>
			    	</div>
			    	<div class="span6">
			    		<form class="form-horizontal">
			    			<h3>County Report Download</h3>
			    			  <div class="control-group">
							    <label class="control-label" for="sel_period_cr">Select period</label>
							    <div class="controls">
							      <select name="sel_period_cr" id="sel_period_cr" class="big"></select>
							    </div>
							  </div>
							  <div class="control-group">
							    <label class="control-label" for="sel_county_cr">Select county</label>
							    <div class="controls">
							      <select name="sel_county_cr" id="sel_county_cr" class="big"></select>
							    </div>
							  </div>
							 
							  
							  <div class="control-group">
							    <div class="controls">
							      <button type="button" id="btn_download_county_report" class="generate btn btn-warning">Download Report</button>
							    </div>
							  </div>
						</form>
			    		
			    	</div>
	  			</div>
			</div>  
			
	    </div> 
    </div>
</div>