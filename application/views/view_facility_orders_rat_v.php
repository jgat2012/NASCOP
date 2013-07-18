<style>
	.dataTables_wrapper{
		width: 100%;
	}
	#DataTables_Table_0{
		font-size:15px;
	}
</style>
<script type="text/javascript">
		var url = "";
	$(function() {
	$("#confirm_delete").dialog( {
	height: 150,
	width: 450,
	modal: true,
	autoOpen: false,
	buttons: {
	"Delete Order": function() {
	delete_record();
	},
	Cancel: function() {
	$( this ).dialog( "close" );
	}
	}

	} );
	
	$("#confirm_delete_aggregated").dialog( {
	height: 180,
	width: 900,
	modal: true,
	autoOpen: false,
	buttons: {
	"Delete Order": function() {
	delete_record();
	},
	Cancel: function() {
	$( this ).dialog( "close" );
	}
	}

	} );

	$(".delete").live('click',function(){ 
		var x=0;
		var agg_ord_id="";
		var order_id=$(this).attr("order");
		$.ajax({
		  url:"<?php echo base_url().'order_rationalization/order_aggregate/'?>" +$(this).attr("order"),
		  dataType: "json",
		  type: 'GET',
		  statusCode: {
		    404: function() {
		      alert("The page you are requesting was not found !");
		    }
		  }
		}).done(function (data) {
			for (var key in data){
      			if (data.hasOwnProperty(key)){
      				x=1;
      				agg_ord_id=data[key].aggregated_order_id;	
      			}
      		}
      		
      		if(x==0){
      			url = "<?php echo base_url().'order_rationalization/delete_order/'?>" +order_id;
      			$("#confirm_delete").dialog('open');
      		}
      		else{
      			<?php if($parent->parent!=$central_facility){?> alert("This order is linked to an aggregated order. You do not have enough privileges to delete it !");  
      			<?php }
				else{
				?>	
				url = "<?php echo base_url().'order_rationalization/delete_order/'?>" +order_id+"/"+agg_ord_id;
      			$("#confirm_delete_aggregated").dialog('open');
				<?php } ?>
      			
      		}
			
		});
		
	});
	});
	function delete_record(){
	window.location = url;
	}
	
	
</script>
<div class="center-content">
	<?php
	$this->load->view('orders_rat_sub_menu');
	?>
<table class="dataTables" border="1">
	<thead>
		<tr>
			<th width="80px">Order No</th>
			<th >Facility Name</th>
			<th>Type of Order</th>
			<th>Beginning Period</th>
			<th>Ending Period</th>
			<th>
				<?php
				if (isset($parent)) {
					if($parent -> parent != $central_facility){
					?>
					Days since submission
					<?php
					}else{
					?>
					Days pending <p style="<?php if($days_pending=="Approval"){ ?> color:rgb(255, 167, 11);  <?php } elseif ($days_pending=="Dispatched") { ?> color:green; <?php } elseif ($days_pending=="Resubmission") { ?> color:red; <?php } elseif ($days_pending=="Delivery") { ?> color:rgb(1, 167, 146); <?php } ?>">(<?php echo $days_pending ?>)</p></th>	
					<?php } 
				} ?>
				
			<th>Action</th>
		</tr>
	</thead>
	<tbody>
		<?php 
		$order_types = array(0=>"Central",1=>"Aggregated",2=>"Satellite");
		foreach($orders as $order){
			$period_begin=$order->Period_Begin;
			$period_end=$order->Period_End;
			$startTimeStamp = $order->Updated;
			$endTimeStamp = strtotime("now");
			
			$timeDiff = abs($endTimeStamp - $startTimeStamp);
			$numberDays = $timeDiff/86400;  // 86400 seconds in one day
			// and you might want to convert to integer
			$numberDays = intval($numberDays);
			?>
			<tr>
				<td><?php echo $order->id;?></td>
				<td><?php echo $order->Facility_Object->name;?></td>
				<td><?php echo @$order_types[$order->Code];?></td>
				<td><?php echo date('d-M-Y',strtotime($period_begin));?></td>
				<td><?php echo date('d-M-Y',strtotime($period_end));?></td>
				<td align="center"><?php echo $numberDays; ?> Day (s)</td>
				<td style="text-align: center"><a href="<?php echo base_url()."order_rationalization/view_order/".$order->id;?>" >View</a>
					<?php if(($quick_link != 1 && $quick_link != 3) ||$quick_link == 2 ){?>
					 <a href="<?php echo base_url()."order_rationalization/edit_order/".$order->id;?>">Edit</a>
					<?php }?>
					<?php if($quick_link == 0){?>
					 <a order="<?php echo $order->id;?>" class="delete">Delete</a></div></td>
					<?php }?>
					
			</tr>
		<?php }
		?>
	</tbody>
</table>
</div>
<div title="Confirm Delete!" id="confirm_delete" style="width: 300px; height: 150px; margin: 5px auto 5px auto;">
	Are you sure you want to delete this order?
</div>

<div title="Confirm Delete Aggregated Order!" id="confirm_delete_aggregated">
	<span style="color:orange" >This order is linked to an aggregated order. Deleting it would delete the relevant aggregated order.</span>
	
</div>
