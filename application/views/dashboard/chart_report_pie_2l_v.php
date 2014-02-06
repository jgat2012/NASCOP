<?php
$chartSize=320;

?>
<script>
	$(function () {
    
        var colors = [
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
            categories = ['Kenya Pharma', 'Kemsa'],
            name = 'Pipelines',
            data = [{
	                    y: 55,
	                    color: colors[0],
	                    drilldown: {
	                        name: 'Kenya Pharma Categories',
	                        categories: ['Adults', 'Peads'],
	                        data: [10, 45],
	                        color: colors[0]
	                    }
	                }, {
	                    y: 45,
	                    color: colors[4],
	                    drilldown: {
	                        name: 'Kemsa Categories',
	                        categories: ['Adults', 'Peads'],
	                        data: [ 20,25],
	                        color: colors[6]
	                    }
                	}];
    
    
        // Build the data arrays
        var browserData = [];
        var versionsData = [];
        for (var i = 0; i < data.length; i++) {
    
            // add browser data
            browserData.push({
                name: categories[i],
                y: data[i].y,
                color: data[i].color
            });
    
            // add version data
            for (var j = 0; j < data[i].drilldown.data.length; j++) {
                var brightness = 0.2 - (j / data[i].drilldown.data.length) / 5 ;
                versionsData.push({
                    name: data[i].drilldown.categories[j],
                    y: data[i].drilldown.data[j],
                    color: Highcharts.Color(data[i].color).brighten(brightness).get()
                });
            }
        }
    
        // Create the chart
        $('#<?php echo $container?>').highcharts({
            chart: {
                type: 'pie',
                height:'<?php echo $chartSize;?>'
            },
            title: {
                text: '<?php echo $chartTitle;?>'
            },
            yAxis: {
                title: {
                    text: '<?php echo $title;?>'
                }
            },
            plotOptions: {
                pie: {
                    shadow: false,
                    center: ['50%', '50%']
                },
                 showInLegend: true
            },
            tooltip: {
        	    valueSuffix: '%'
            },
            credits: {
			   enabled: false
			},
            series: [{
                name: 'Patients',
                data: browserData,
                size: '60%',
                dataLabels: {
                    formatter: function() {
                        return this.y > 5 ? this.point.name : null;
                    },
                    color: 'white',
                    distance: -30
                }
            }, {
                name: 'Patients',
                data: versionsData,
                size: '80%',
                innerSize: '60%',
                dataLabels: {
                    formatter: function() {
                        // display only if larger than 1
                        return this.y > 1 ? '<b>'+ this.point.name +':</b> '+ this.y +'%'  : null;
                    }
                }
            }]
        });
    
});
</script>
<div class="graph">
	<div id="<?php echo $container?>"  style="width:98%;"  '></div>
</div>