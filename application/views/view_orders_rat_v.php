<script type="text/javascript">
	$(document).ready(function(){
		setTimeout(function(){
			$(".message").fadeOut("2000");
		},6000);
	});
</script>
<div class="center-content">
	<div>
		<ul class="breadcrumb">
		  <li><a href="<?php echo site_url().'order_rationalization' ?>">Orders</a> <span class="divider">/</span></li>
		 
		  	<?php
		  	if(isset($page_title)){
		  		?>
		  		 <li class="active" id="actual_page"><?php echo $page_title;?> </li>
		  		<?php
		  	}
		  	?>
		 
		</ul>
	</div>
	<div>
	<?php
  	if($this->session->userdata("msg_success")){
  		?>
  		<span class="message success"><?php echo $this->session->userdata("msg_success")  ?></span>
  	<?php
  	$this->session->unset_userdata("msg_success");
	}
  		
  	elseif($this->session->userdata("msg_error")){
  		?>
  		<span class="message error"><?php echo $this->session->userdata("msg_error")  ?></span>
  	<?php
  	$this->session->unset_userdata("msg_error");
  	}
	?>
	</div>
	<?php
	$this->load->view('orders_rat_sub_menu');
	?>
<table id="pending_orders" class="table table-striped table-bordered dataTables">
	<thead>
		<tr>
			<th width="80px">Order No</th>
			<th >Facility Name</th>
			<th>Type of Order</th>
			<th>Reporting Period</th>
			<th>Days pending <div style="<?php if($days_pending=="Approval"){ ?> color:rgb(255, 167, 11);  <?php } elseif ($days_pending=="Dispatched") { ?> color:green; <?php } elseif ($days_pending=="Resubmission") { ?> color:red; <?php } elseif ($days_pending=="Delivery") { ?> color:rgb(1, 167, 146); <?php } ?>">(<?php echo $days_pending ?>)</div></th>
			<th width="80px">Action</th>
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
				<td><b><?php echo @$order_types[$order->Code];?></b></td>
				<td><?php echo date('M-Y',strtotime($period_begin));?></td>
				<td align="center"><?php echo $numberDays; ?> Day (s)</td>			
				<td style="text-align: center"><a href="<?php echo base_url()."order_rationalization/rationalize_order/".$order->id;?>"><?php if($quick_link != 2){?>View<?php }?></a></td>			
			</tr>
		<?php }
		?>
	</tbody>
</table>
</div>