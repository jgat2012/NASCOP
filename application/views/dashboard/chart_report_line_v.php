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
            	height:350,
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
                enabled: false,
                formatter: function() {
                    return '<b>'+ this.series.name +'</b><br/>'+
                        this.x +': '+ this.y +'%';
                }
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
            series: [
            	{
	                name: 'Kemsa/LMU - Reporting timeliness (By 10th)',
	                data: [53, 83, 65, 68, 82, 59]
	            }, 
	            {
	                name: 'Kenya Pharma - Reporting timeliness (By 10th)',
	                data: [39, 42, 75, 85, 67, 63]
	            }, 
	            {
	                name: 'National Reporting Rate',
	                data: [84, 85, 86, 89, 96, 94]
	            }
	        ]
        });
    });
    
</script>
<div class="graph">
	<div id="<?php echo $container?>"  style="width:97%;"  '>
</div>
</div>
