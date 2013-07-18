<?php
include ("dashboard_header.php");
?>
<head>
<title>Web-ADT</title>
    <script type="text/javascript">
		$(document).ready(function() {
			// create jqxtabs.
			$('#jqxtabs').jqxTabs({
				width : 1200,
				height : 1000
			});
			$('#jqxtabs').bind('selected', function(event) {
				var item = event.args.item;
				var title = $('#jqxtabs').jqxTabs('getTitleAt', item);

			});

			$("#month_display").text($.datepicker.formatDate('MM-yy', new Date()));
			
		 function getChart(year,month,pipeline){
		    var chart= new FusionCharts("<?php echo base_url().'Scripts/FusionCharts/Column3D.swf';?>","ChartId","100%","100%","0","0");	
	        chart.setDataURL("<?php echo base_url().'patientbyline_management/plotgraph/';?>"+year+'/'+month+'/'+pipeline);
	        chart.render("chart_area");	
		 }
	
	
	$(".go").click(function(){
		var year=$("#chart_year").attr("value");
		var month=$("#chart_month").attr("value");
		var pipeline=$("#chart_pipeline").attr("value");
		getChart(year,month,pipeline);
	});

		});

    </script>
</head>
<body id="top">

<div id="network">
	<div class="center-wrapper">

		<div class="left"><?php echo "<b>" . date("l, d F Y") . "</b>";?> <span class="text-separator">|</span> <span class="quiet">Welcome </span></div>
		<div class="right">

			<ul class="tabbed" id="network-tabs">
				<li class="current-tab"><a href="#"><b>ADT</b></a></li>
				<li><a href="<?php echo base_url() . 'User_Management/login';?>">Login</a></li>
				
			</ul>

			<div class="clearer">&nbsp;</div>
		
		</div>
		
		<div class="clearer">&nbsp;</div>

	</div>
</div>

<div id="site">
	<div class="center-wrapper">

		<div id="header">

		

			<div class="clearer">&nbsp;</div>

			<div id="site-title">

				<div align="center"> <h1><img src="<?php echo base_url() . 'Images/dashboard_logo.jpg';?>" alt="" /></h1></div>

			</div>

			<div id="navigation">
				
				<div id="main-nav">

					<ul class="tabbed">
						<li class="current-tab"><a href="<?php echo base_url() . 'test_management';?>">Stocks Situation</a></li>
						<li><a href="<?php echo base_url() . 'patientbyregimen_management';?>">Patients on ART</a></li>
						<li><a href="<?php echo base_url() . 'patientscaleup_management';?>">Patients Scale Up Trend</a></li>
						<li><a href="<?php echo base_url() . 'orderingsite_management';?>">Ordering Sites </a></li>
						<li><a href="<?php echo base_url() . 'servicepoint_management';?>">Service Points</a></li>
						<li><a href="<?php echo base_url() . 'reportingrate_management';?>">Reporting Rates </a></li>
						
					</ul>

					<div class="clearer">&nbsp;</div>

				</div>

				<div id="sub-nav" style="width:420px">

					<ul class="tabbed" >
						<li class="current-tab" ><a href="adt_national.php">National Stock Status</a></li>
						<li><a href="adt_facilitystatus.php">Facility Stock Status</a></li>						
					</ul>


					<div class="clearer">&nbsp;</div>

				</div>

			</div>

		</div>
		<small>  <font color="#ABC"> *Default View show summaries from both pipelines.  Refine your view  by selecting the choices below </font> </small>
		<div class="navigation" >
			
			<div id="chart_menu">
	<ul>
		<li>
			<b>Year</b>
			<select id="chart_year">
				<option value="2013">2013</option>
				<option value="2012">2012</option>
				<option value="2011">2011</option>
				<option value="2010">2010</option>
			</select>
		</li>
		<li>
			&nbsp;<b>Month</b>
			<select id="chart_month">
				<option value="01">Jan</option>
				<option value="02">Feb</option>
				<option value="03">Mar</option>
				<option value="04">Apr</option>
				<option value="05">May</option>
				<option value="06">Jun</option>
				<option value="07">Jul</option>
				<option value="08">Aug</option>
				<option value="09">Sep</option>
				<option value="10">Oct</option>
				<option value="11">Nov</option>
				<option value="12">Dec</option>	
			</select>
		</li>
		<li>
			&nbsp;<b>Pipeline</b>
			<select id="chart_pipeline">
				<option value="0">All</option>
				<option value="1">Kemsa</option>
				<option value="2">Kenya Pharma</option>
			</select>
		</li>
		<li>
			<a href="#" class="go">GO</a>
		</li>
	</ul>
</div>
</div>
<div class="section-title" style="padding:2px;"> National Stock Status</div>
<!---
<div id='jqxtabs'>
        <ul style='margin-left: 20px;'>
            <li>Summary Graphs </li>
            <li>Stocks </li>
            <li>Consumption </li>
          
        </ul>
</div>
-->
<div id="chart_area">
	
</div>
<div id="bottom_ribbon">
<?php
include ("footer_v.php");
?>
</div>
</body>