<script>
$(function () {
        $('#<?php echo $container?>').highcharts({
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
            	height:330,
                type: 'line'
            },
            title: {
                text: '<?php echo $chartTitle?> '
            },
            xAxis: {
                categories: <?php echo $categories?>
            },
            yAxis: {
                title: {
                    text: '<?php echo $yAxix?>'
                }
            },
            tooltip: {
                valueSuffix: '%'
            },
            plotOptions: {
                line: {
                    dataLabels: {
                        enabled: true
                    },
                    enableMouseTracking: false
                }
            },
            credits: {
				enabled: false
			},
            series: <?php echo $resultArray?>
        });
    });
    
</script>
<div class="graph">
	<div id="<?php echo $container?>"  style="width:97%;"  '>
</div>
</div>
