<?php
$chartSize=0;
if($resultArraySize<=6){
	$chartSize='300';
}
if($resultArraySize>6){
	$chartSize='450';
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
			text: 'Period'
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
			legend: {
				layout: 'horizontal',
				align: 'middle',
				verticalAlign: 'bottom',
				floating: true,
				borderWidth: 1,
				backgroundColor: '#FFFFFF',
				shadow: true
			},
			credits: {
			enabled: false
			},
			series:[{
                name: 'Reporting Sites(By 10th)',
                data: [49, 71, 106, 129, 144, 176]
    
            }, {
                name: 'Total Reporting Sites Sites',
                data: [69, 101, 106, 139, 154, 186]
    
            }]
		});
	});
</script>
<div class="graph">
	<div id="<?php echo $container?>"  style="width:97%;height: 100%"  '>
</div>
</div>

