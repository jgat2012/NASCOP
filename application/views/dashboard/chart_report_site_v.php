<?php
$chartSize=300;

?>

<script>
	$(function () {
		    var total = 0;
	$('#<?php echo $container;?>').highcharts({
			chart:{
				 height:'<?php echo $chartSize;?>',	
				 type:'pie',
                  events: {
                    load: function(event) {
                        $('#<?php echo $container;?>').find('.highcharts-legend-item').last().append('<br/><br/><div style="width:220px"><hr/> <span style="float:left"> Total Reporting Sites</span><span style="float:right"> ' + <?php echo $tot_reportsites;?> + '</span> </div>')
                    	
                    }
                  }
                  
                  },
			credits:{enabled: false},
            colors:[
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
            title:{text: 'Reporting Sites'},
			tooltip:{
				enabled: true,
				animation: true
			},
			plotOptions: {
                pie: {
                    allowPointSelect: true,
					animation: true,
                    cursor: 'pointer',
                    showInLegend: true,
                    dataLabels: {
                        enabled: false,                        
                        formatter: function() {
                            return this.percentage.toFixed(2) + '%';
                        }
                    } 									
                }
            },
            legend: {
                enabled: true,
                layout: 'vertical',
                align: 'right',
                width: 220,
                verticalAlign: 'top',
				borderWidth: 0,
                useHTML: true,
				labelFormatter: function() {
                    total += this.y;
					return '<div style="width:200px"><span style="float:left">' + this.name + ' </span><span style="float:right"> <b>' + this.y + '</b></span></div>';
				},
				title: {
					text: 'Description',
					style: {
						fontWeight: 'bold'
					}
				}
            },
			series: [{
				type: 'pie',
				dataLabels:{
				
				},
				data: [
					{
                        name: 'Sites Reported by 10th ',
                        y: <?php echo $tot_tenth;?>,
                        sliced: true,
                        selected: true
                   },
					['Sites that Reported After 10th ', <?php echo $tot_reportsites-$tot_tenth;?>]
				]
			}]
			
			});
	});
</script>
<div class="graph">
	<div id="<?php echo $container?>"  style="width:98%;"  '>
</div>
</div>

