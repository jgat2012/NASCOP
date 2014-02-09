<?php
$chartSize=0;
if($resultArraySize<=6){
	$chartSize='250';
}
if($resultArraySize>6){
	$chartSize='350';
}
if($resultArraySize>10){
	$chartSize='700';
}
if($resultArraySize>15){
	$chartSize='1000';
}
if($resultArraySize>20){
	$chartSize='1200';
}
if($resultArraySize>25){
	$chartSize='1400';
}

?>

<script>
	$(function () {
		$('<?php echo "#" . $container; ?>').highcharts({
			colors: [
			'#66aaf7',
			'#f66c6f',
			'#8bbc21',
			'#910000',
			'#1aadce',
			'#492970',
			'#f28f43',
			'#77a1e5',
			'#c42525',
			'#a6c96a'
			],
			chart: {
				height:<?php echo $chartSize;?>,
				type: '<?php echo $chartType ?>'
			},
			title: {
			text: '<?php echo $chartTitle; ?>'
			},
			xAxis:
			{
			categories:  <?php echo $categories; ?>,
			title: {
			text: 'Regimens'
			}
			},
			yAxis: {
				min: 0,
				title: {
					text: '<?php echo $yAxix; ?>',
					align: 'left'
					},
				labels: {
				overflow: 'justify'
				}
			},
			tooltip: {
			valueSuffix: ''
			},
			plotOptions: {
				bar: {
					dataLabels: {
						enabled: true
					}
				}
			},
			credits: {
			enabled: false
			},
			series:<?php echo $resultArray?>
		});
	});
</script>
<div class="graph">
	<div id="<?php echo $container?>"  style="width:97%;height: 100%"  '>
</div>
</div>

