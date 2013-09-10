<html>
	<head>
		<!-- JQuery -->
		<script src="<?php echo base_url().'Scripts/jquery-1.10.2.js'?>" type="text/javascript"></script>
		<script src="<?php echo base_url().'Scripts/jquery-1.7.2.min.js'?>" type="text/javascript"></script>
		<script src="<?php echo base_url().'Scripts/jquery-migrate-1.2.1.js'?>" type="text/javascript"></script>
		<script src="<?php echo base_url().'Scripts/jquery.form.js'?>" type="text/javascript"></script>
		<script src="<?php echo base_url().'Scripts/highcharts/highcharts.js'?>"></script>
		<!-- Jquery UI -->
	</head>
	<body>
	<script>
		$(document).ready(function() {
			var options = {
				chart: {
					renderTo: 'container',
					type: 'bar'
				},
				series: [{
					name: 'Jane',
					data: [1, 0, 4]
				}]
			};
			options.series.push({
				name: 'John',
				data: [3, 4, 2]
			})
			var chart = new Highcharts.Chart(options);
			
		});
		
		
	</script>
	<div id="container" style="width:100%; height:400px;"></div>
	
	</body>

</html>