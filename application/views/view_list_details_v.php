<div class="center-content">
	<div>
		<ul class="breadcrumb">
		  <li><a href="<?php echo site_url().'picking_list_management' ?>">Picking Lists</a> <span class="divider">/</span></li>
		 
		  	<?php
		  	if(isset($page_title)){
		  		?>
		  		 <li class="active" id="actual_page"><?php echo $page_title;?> </li>
		  		<?php
		  	}
		  	?>
		 
		</ul>
	</div>
<table class="table table-bordered ">
	<thead style="background:#2B597E;">
		<tr>
			<th width="80px">List No:</th>
			<th width="80px">Pipeline</th>
			<th>List Name</th>
			<th>Created By</th>
			<th width="130px">Created On</th>
			<th>No. of Orders</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><span class="_green"><?php echo $list -> id;?></span></td>
			<td><span class="_green"><?php echo $list -> Pipeline;?></span></td>
			<td><span class="_green"><?php echo $list -> Name;?></span></td>
			<td><span class="_green"><?php echo $list -> User_Object -> Name;?></span></td>
			<td><span class="_green"><?php echo date('d-F-Y h:i:s a', $list -> Timestamp);?></span></td>
			<td><span class="_green"><?php echo count($list -> Order_Objects);?></span></td>
		</tr>
	</tbody>
</table> 


<?php
//First retrieve the orders
$orders = $list->Order_Objects;
//Loop through them to retrieve their particulars
foreach($orders as $order){
?>
<table class="table table-bordered table-striped dataTables">
	<caption><strong>Commodity Details for Order (s) No: <?php echo $order->id;?></strong></caption>
	<thead>
		<tr>
			<th>Commodity</th>
			<th>Quantity for Resupply</th>
			<th>Packs/Bottles/Tins</th> 
		</tr>
	</thead>
	<tbody> 
		<?php
		//Retrieve the ordered commodities
		$commodities = $order->Commodity_Objects;
		//Loop through the commodities to display their particulars
		foreach($commodities as $commodity){?>
		<tr>
			<td><?php echo $commodity -> Drug_Id;?></td>
			<td><?php echo $commodity ->Resupply;?></td> 
			<td><?php if($commodity -> Drugcode_Object->Drug_Unit->Name == "Bottle"){echo "Bottle";} else{echo "Packs";};?></td> 
		</tr>
		<?php }
		?>
	</tbody>
</table>
<?php }?>
</div>