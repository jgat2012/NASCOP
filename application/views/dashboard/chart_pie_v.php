
<script>
	$(function () {
    $('<?php echo "#" . $container; ?>').highcharts({
        chart: {
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false,
            height:300
        },
        title: {
            text: '<?php echo $chartTitle;?>'
        },
        tooltip: {
    	   
        },
        credits: {
			   enabled: false
			},
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                showInLegend: true,
                dataLabels: {
                    enabled: true,
                    color: '#000000',
                    connectorColor: '#000000',
                    format: '<b>{point.name}</b>: {point.percentage:.1f} %'
                }
            }
        },
        series: <?php echo $myData;?>
    });
});
</script>


<div id="<?php echo $container;?>" style="min-width: 310px; height: 400px; margin: 0 auto"></div>