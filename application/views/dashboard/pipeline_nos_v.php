<?php
include ('dashboard_header.php');
?>

<script type="text/javascript">
	$(document).ready(function(){
		
		function getChart(year,month,pipeline){
		    var chart= new FusionCharts("<?php echo base_url().'Scripts/FusionCharts/Bar2D.swf';?>","ChartId","100%","300%","0","0");	
	        chart.setDataURL("<?php echo base_url().'pipelineconsumption_management/plotgraph/';?>"+year+'/'+month+'/'+pipeline);
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
				<option value="1">Kemsa</option>
				<option value="2">Kenya Pharma</option>
			</select>
		</li>
		<li>
			<a href="#" class="go">GO</a>
		</li>
	</ul>
</div>
<div id="chart_area">
	
</div>
