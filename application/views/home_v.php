<?php
$access_level = $this -> session -> userdata('user_indicator');
$user_is_administrator = false;
$user_is_nascop = false;
$user_is_pharmacist = false;
$user_is_facilityadmin = false;

if ($access_level == "nascop_administrator") {
	$user_is_administrator = true;
}
if ($access_level == "nascop_pharmacist") {
	$user_is_pharmacist = true;

}
if ($access_level == "nascop_staff") {
	$user_is_nascop = true;
}
if ($access_level == "facility_administrator") {
	$user_is_facilityadmin = true;
}



if($this->session->userdata("changed_password")){
	$message=$this->session->userdata("changed_password");
	echo "<p class='error'>".$message."</p>";
	$this->session->set_userdata("changed_password","");
}
?>

<script type="text/javascript">
	//Retrieve the Facility Code
	var facility_code = "<?php echo $this -> session -> userdata('facility');?>";
	var facility_name = "<?php echo $this -> session -> userdata('facility_name');?>";   
</script>



<script type="text/javascript">
$(document).ready(function() {

	 $('h3 .btn-danger').hide();
	 

      var period=30;
      var limit=5;
      
      var date = new Date(), y = date.getFullYear(), m = date.getMonth();
      var firstDay = new Date(y, m, 1);
      var lastDay = new Date(y, m + 1, 0);
      
      //Get Today's Date and Upto Saturday
      var someDate = new Date();
      var dd = ("0" + firstDay.getDate()).slice(-2);
      var mm = ("0" + (firstDay.getMonth() + 1)).slice(-2);
      var y = firstDay.getFullYear();
      var firstDay =y+'-'+mm+'-'+dd; 
      
      var numberOfDaysToAdd = 5;
      var to_date=new Date(someDate.setDate(someDate.getDate() + numberOfDaysToAdd)); 
      var dd = ("0" + lastDay.getDate()).slice(-2);
      var mm = ("0" + (lastDay.getMonth() + 1)).slice(-2);
      var y = lastDay.getFullYear();
      var lastDay =y+'-'+mm+'-'+dd;
      
	   $(".loadingDiv").show();
	  
	   var first_link="<?php echo base_url().'pharmacist_management/getTopCommodities/';?>"+limit+"/"+firstDay+"/"+lastDay;
	   var second_link="<?php echo base_url().'pharmacist_management/getFacilitiesUsing/';?>"+firstDay+"/"+lastDay;
	   var third_link="<?php echo base_url().'pharmacist_management/getPickingList/';?>"+firstDay+"/"+lastDay;
	   var fourth_link="<?php echo base_url().'pharmacist_management/getFacilitiesDelay/';?>"+firstDay+"/"+lastDay;
       $('#chart_area').load(first_link);
       $('#chart_area2').load(second_link, function() {
            $("#patient_listing").dataTable({
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
       $('#chart_area3').load(third_link);
       $('#chart_area4').load(fourth_link, function() {
            $("#facility_listing").dataTable({
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
       
       		
       
       $("#period_start_date_1").val(firstDay);
       $("#period_end_date_1").val(lastDay);
       $("#period_start_date_2").val(firstDay);
       $("#period_end_date_2").val(lastDay);
       $("#period_start_date_3").val(firstDay);
       $("#period_end_date_3").val(lastDay);
       $("#period_start_date_4").val(firstDay);
       $("#period_end_date_4").val(lastDay);
      
    //Toggle
var chartID;
var graphID;
var chartLink;
	$('.more').click(function(){
		$('h3 .btn-success').hide();
		$('h3 .btn-danger').show();
		var myID = $(this).attr('id');
		switch(myID){
		case'drugs-more':
		$('.tile').hide();
		$('#drugs-chart').show();
		chartID='#drugs-chart';
		graphID="#chart_area";
		chartLink="<?php echo base_url().'pharmacist_management/getTopCommodities/';?>"+period+'/'+location;
	
		break;
		case'enrollment-more':
		$('.tile').hide();
		$('#enrollment-chart').show();
		chartID='#enrollment-chart';
		graphID="#chart_area2";
		chartLink="<?php echo base_url().'pharmacist_management/getFacilitiesUsing/';?>"+fromDate+'/'+endDate;
	  
		break;
		case'appointment-more':
		$('.tile').hide();
		$('#appointments-chart').show();
		chartID='#appointments-chart';
		graphID="#chart_area3";
		chartLink="<?php echo base_url().'pharmacist_management/getPickingList/';?>"+fromDate+'/'+endDate;

		break;
		case'stock-more':
		$('.tile').hide();
		$('#stocks-chart').show();
		chartID='#appointments-chart';
		graphID="#chart_area3";
		chartLink="<?php echo base_url().'pharmacist_management/getFacilitiesDelay/';?>"+fromDate+'/'+endDate;
		break;
		}
		
		  $(chartID).animate({height:'80%',width:'100%'}, 500);
		  $(graphID).load(chartLink);

	});
	
	$('.less').click(function(){
		$('h3 .btn-success').show();
		$('h3 .btn-danger').hide();
		var myID = $(this).attr('id');
		
		switch(myID){
		case'drugs-less':
		$('.tile').show();
		 $(graphID).load(chartLink);
		break;
		case'enrollment-less':
		$('.tile').show();
		 $(graphID).load(chartLink);
		break;
		case'appointment-less':
		$('.tile').show();
		 $(graphID).load(chartLink);
		break;
		case'stock-less':
		$('.tile').show();
		break;
		
		}
        $(chartID).animate({height:'45%',width:'49%'}, 500);
	});
	
    
		    $('.generate').click(function(){
                 var button_id=$(this).attr("id");
                 if(button_id=="expiry_btn"){
                 	 period = $('.period').val();
		    	     var from_date = $('#period_start_date_1').val();
		    	     var to_date= $('#period_end_date_1').val();
		    	     var expiry_link="<?php echo base_url().'pharmacist_management/getTopCommodities/';?>"+period+'/'+from_date+'/'+to_date;
		    	 	 $('#chart_area').load(expiry_link);           	
                 }else if(button_id=="enrollment_btn"){
                 	 var from_date=$("#period_start_date_2").val();
                 	 var to_date=$("#period_end_date_2").val();
                 	 var enrollment_link="<?php echo base_url().'pharmacist_management/getFacilitiesUsing/';?>"+from_date+'/'+to_date;
                 	 $('#chart_area2').load(enrollment_link, function() {
            $("#patient_listing").dataTable({
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
                 }else if(button_id=="appointment_btn"){
                 	 var from_date=$("#period_start_date_3").val();
                 	 var to_date=$("#period_end_date_3").val();
                 	 var visits_link="<?php echo base_url().'pharmacist_management/getPickingList/';?>"+from_date+'/'+to_date;
                     $('#chart_area3').load(visits_link);
                 }else if(button_id=="stockout_btn"){
                 	 var from_date=$("#period_start_date_4").val();
                 	 var to_date=$("#period_end_date_4").val();
                 	 var visits_link="<?php echo base_url().'pharmacist_management/getFacilitiesDelay/';?>"+from_date+'/'+to_date;
                     $('#chart_area4').load(visits_link, function() {
            $("#facility_listing").dataTable({
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
                 } else if(button_id=="usage_btn"){                	
                 	 var from_date=$("#period_start_date_4").val();
                 	 var to_date=$("#period_end_date_4").val();
                 	 $('#chart_area77').load('<?php echo base_url().'admin_management/getSystemUsage/'?>'+period);
                 } else if(button_id=="access_btn"){                	
                 	 var from_date=$("#enrollment_start").val();
                 	 var to_date=$("#enrollment_end").val();
                 	 $('#chart_area78').load('<?php echo base_url().'admin_management/getWeeklySumary/'?>'+from_date+'/'+to_date);	
                 }
            });

		});
    </script>

<div class="main-content">

	<?php

	if($user_is_pharmacist){
		
		?>
		<div class="center-content">
		<?php
		$this->load->view('dashboard/pharmacist');
		?>
		</div>
		<?php
	 }
	 
	elseif($user_is_administrator){
		?>
		<div class="center-content">
		<?php
	 	 $this->load->view("dashboard/system_admin");
		 ?>
		</div>
		 <?php
	 }
	else{
		?>
		<div class="fullest-content">
		<?php
		$this->load->view("dashboard/main_v");
		?>
		</div>
		<?php
	}
	 ?>
	</div>

</div>

<script type="text/javascript">
$(document).ready(function(){
	var base_url="<?php echo base_url(); ?>";    		      	   
	        $("#enrollment_start").datepicker({
					yearRange : "-120:+0",
					maxDate : "0D",
					dateFormat : $.datepicker.ATOM,
					changeMonth : true,
					changeYear : true,
					beforeShowDay: function(date){ 
                                   var day = date.getDay(); 
                                   return [day == 1];
                                   }
			});			
			
			$("#visit_start").datepicker({
					yearRange : "-120:+0",
					maxDate : "0D",
					dateFormat : $.datepicker.ATOM,
					changeMonth : true,
					changeYear : true,
					beforeShowDay: function(date){ 
                                   var day = date.getDay(); 
                                   return [day == 1];
                                   }
			});
						
			//Visit Onchange Events
			$("#visit_start").change(function(){
				var from_date=$(this).val();
				var someDate = new Date(from_date);
                var numberOfDaysToAdd = 5;
                var to_date=new Date(someDate.setDate(someDate.getDate() + numberOfDaysToAdd)); 
                var dd = ("0" + to_date.getDate()).slice(-2);
                var mm = ("0" + (to_date.getMonth() + 1)).slice(-2);
                var y = to_date.getFullYear();
                var someFormattedDate =y+'-'+mm+'-'+dd;
				$("#visit_end").val(someFormattedDate);
			});
			
			//Enrollments Onchange Events
			$("#enrollment_start").change(function(){
				var from_date=$(this).val();
				var someDate = new Date(from_date);
                var numberOfDaysToAdd = 5;
                var to_date=new Date(someDate.setDate(someDate.getDate() + numberOfDaysToAdd)); 
                var dd = ("0" + to_date.getDate()).slice(-2);
                var mm = ("0" + (to_date.getMonth() + 1)).slice(-2);
                var y = to_date.getFullYear();
                var someFormattedDate =y+'-'+mm+'-'+dd;
				$("#enrollment_end").val(someFormattedDate);
			});
		$("#reporting_period_1").datepicker({
			yearRange : "-120:+0",
			maxDate : "0D",
			changeMonth : true,
			changeYear : true,
			showButtonPanel : true,
			dateFormat : 'MM-yy',
			onClose : function(dateText, inst) {
				var month = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
				var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
				month = parseInt(month);				
				var last_day_month = LastDayOfMonth(year, month + 1);
                var mm=("0" + (month + 1)).slice(-2);
				$("#period_start_date_1").val(year+"-"+mm+"-01");
				$("#period_end_date_1").val(year+"-"+mm+"-"+last_day_month);
				$(this).datepicker('setDate', new Date(year, month, 1));
			}
		});
		
		$("#reporting_period_1").datepicker('setDate', new Date());
		
		$("#reporting_period_2").datepicker({
			yearRange : "-120:+0",
			maxDate : "0D",
			changeMonth : true,
			changeYear : true,
			showButtonPanel : true,
			dateFormat : 'MM-yy',
			onClose : function(dateText, inst) {
				var month = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
				var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
				month = parseInt(month);
				var last_day_month = LastDayOfMonth(year, month + 1);
                var mm=("0" + (month + 1)).slice(-2);
				$("#period_start_date_2").val(year+"-"+mm+"-01");
				$("#period_end_date_2").val(year+"-"+mm+"-"+last_day_month);
				$(this).datepicker('setDate', new Date(year, month, 1));
			}
		});
		
		$("#reporting_period_2").datepicker('setDate', new Date());
		
		$("#reporting_period_3").datepicker({
			yearRange : "-120:+0",
			maxDate : "0D",
			changeMonth : true,
			changeYear : true,
			showButtonPanel : true,
			dateFormat : 'MM-yy',
			onClose : function(dateText, inst) {
				var month = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
				var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
				month = parseInt(month);
				var last_day_month = LastDayOfMonth(year, month + 1);
                var mm=("0" + (month + 1)).slice(-2);
				$("#period_start_date_3").val(year+"-"+mm+"-01");
				$("#period_end_date_3").val(year+"-"+mm+"-"+last_day_month);
				$(this).datepicker('setDate', new Date(year, month, 1));
			}
		});
		
		$("#reporting_period_3").datepicker('setDate', new Date());
		
		
		$("#reporting_period_4").datepicker({
			yearRange : "-120:+0",
			maxDate : "0D",
			changeMonth : true,
			changeYear : true,
			showButtonPanel : true,
			dateFormat : 'MM-yy',
			onClose : function(dateText, inst) {
				var month = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
				var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
				month = parseInt(month);
				var last_day_month = LastDayOfMonth(year, month + 1);
                var mm=("0" + (month + 1)).slice(-2);
				$("#period_start_date_4").val(year+"-"+mm+"-01");
				$("#period_end_date_4").val(year+"-"+mm+"-"+last_day_month);
				$(this).datepicker('setDate', new Date(year, month, 1));
			}
		});
		
		$("#reporting_period_4").datepicker('setDate', new Date());
			
	});
		function LastDayOfMonth(Year, Month) {
			return (new Date((new Date(Year, Month, 1)) - 1)).getDate();
		}
</script>
