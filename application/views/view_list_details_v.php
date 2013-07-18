<table class="table table-bordered ">
	<thead>
		<tr>
			<th width="80px">List No</th>
			<th>List Name</th>
			<th>Created By</th>
			<th width="130px">Created On</th>
			<th>Orders</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><span class="_green"><?php echo $list -> id;?></span></td>
			<td><span class="_green"><?php echo $list -> Name;?></span></td>
			<td><span class="_green"><?php echo $list -> User_Object -> Name;?></span></td>
			<td><span class="_green"><?php echo date('d-F-Y', $list -> Timestamp);?></span></td>
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
<table class="table table-bordered table-striped">
	<caption>Commodity Details for Order (s) No <?php echo $order->id;?></caption>
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
			<td><?php echo $commodity -> Drugcode_Object->Drug;?></td>
			<td><?php echo $commodity ->Resupply;?></td> 
			<td><?php if($commodity -> Drugcode_Object->Drug_Unit->Name == "Bottle"){echo "Bottle";} else{echo "Packs";};?></td> 
		</tr>
		<?php }
		?>
	</tbody>
</table>
<?php }?>