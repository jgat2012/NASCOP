<?php
if (!$this -> session -> userdata('user_id')) {
	redirect("User_Management/login");
}
if (!isset($link)) {
	$link = null;
}
$access_level = $this -> session -> userdata('user_indicator');
$user_is_administrator = false;
$user_is_nascop = false;
$user_is_pharmacist = false;

if ($access_level == "system_administrator") {
	$user_is_administrator = true;
}
if ($access_level == "pharmacist") {
	$user_is_pharmacist = true;

}
if ($access_level == "nascop_staff") {
	$user_is_nascop = true;
}
?>
<!DOCTYPE html">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $title;?></title>
<link rel="SHORTCUT ICON" href="<?php echo base_url().'Images/favicon.ico'?>">
<link href="<?php echo base_url().'CSS/style.css'?>" type="text/css" rel="stylesheet"/> 
<link href="<?php echo base_url().'CSS/jquery-ui.css'?>" type="text/css" rel="stylesheet"/>

<link href="<?php echo base_url().'CSS/datatable/jquery.dataTables.css'?>" type="text/css" rel="stylesheet"/>
<link href="<?php echo base_url().'CSS/datatable/jquery.dataTables_themeroller.css'?>" type="text/css" rel="stylesheet"/>
<link href="<?php echo base_url().'CSS/datatable/demo_table.css" type="text/css'?>" rel="stylesheet"/>

<!-- Bootstrap -->
<link href="<?php echo base_url().'Scripts/bootstrap/css/bootstrap.min.css'?>" rel="stylesheet" media="screen">
<link href="<?php echo base_url().'Scripts/bootstrap/css/bootstrap-responsive.min.css'?>" rel="stylesheet" media="screen">

<script src="<?php echo base_url().'Scripts/jquery.js'?>" type="text/javascript"></script> 
<script src="<?php echo base_url().'Scripts/jquery-ui.js'?>" type="text/javascript"></script> 
<script src="<?php echo base_url().'Scripts/jquery.form.js'?>" type="text/javascript"></script>
<!-- Datatables -->
<script type="text/javascript" src="<?php echo base_url().'Scripts/datatable/jquery.dataTables.min.js'?>"></script>
<!-- Datatables end --> 

<script type="text/javascript" src="<?php echo base_url().'Scripts/bootstrap/js/paging.js'?>"></script>



<?php
if ($user_is_pharmacist) {
	echo "<script src=\"" . base_url() . "Scripts/offline_database.js\" type=\"text/javascript\"></script>";
}
if (isset($script_urls)) {
	foreach ($script_urls as $script_url) {
		echo "<script src=\"" . $script_url . "\" type=\"text/javascript\"></script>";
	}
}
?>

<?php
if (isset($scripts)) {
	foreach ($scripts as $script) {
		echo "<script src=\"" . base_url() . "Scripts/" . $script . "\" type=\"text/javascript\"></script>";
	}
}
?>


 
<?php
if (isset($styles)) {
	foreach ($styles as $style) {
		echo "<link href=\"" . base_url() . "CSS/" . $style . "\" type=\"text/css\" rel=\"stylesheet\"/>";
	}
}
?>  

<style>
	#my_profile_link_container .generated_link{
		display: none;
	}
	
	#my_profile_link_containe{
		min-width: 200px !important;
		background-color: red;
		height:100px;
	}
	.temp_link{
		font-size: 10px;
		width:100px !important;
		background-color: #B80000;  
		margin:0px;
	}
	.dataTables_wrapper{
		width: 80%;
		margin:0 auto;
	}


	table.setting_table{
		border:solid;
		border-color: grey;
		border-width: 1px;
	}
	.setting_table td{

		max-width: 300px;
	}

	.ui-widget-header{
		background:rgb(140, 214, 140);
	}
	table.dataTable tr.odd{
		background-color:rgb(234,255,232);
	}
	table.dataTable tr.odd td sorting_1{
		background-color:rgb(234,255,232);
	}
	.btn-quickmenu{
		width:115px;
		height:35px;
		margin-bottom:3px;
		padding-left:0px;
	}
	.span3{
		margin-top: 0px;
	}
	#quick_menu{
		margin-top: 0px;
	}
	#quick_menu a{
		color:#FFF;
		text-decoration: none;
	}
	#quick_menu a:hover{
		color:#94B0BE;
	}
	legend{
		font-size:20px;
	}
	
	/*Data tables css*/
	.dataTables_length{
		width:auto;
	}
	 
	 
	div.dataTables_length select {
	    width: 75px;
	}
	.dataTables_filter{
		width:auto;
	}
	 
	div.dataTables_filter label {
	    float: right;
	    width: 460px;
	}
	 
	div.dataTables_info {
	    padding-top: 8px;
	}
 
	div.dataTables_paginate {
	    float: right;
	    margin: 0;
	}
	
	.table th, .table td{
		padding:5px;
	}
	.row{
		margin-left:0px;
	}
	.span5,.span7{
		width:450px;
		margin-left:0px;
		float:none;
	}
	.span7{
		width:500px;
		float:right;
	}
	.span12 legend{
		margin-bottom:10px;
	}
	.span12 .btn-group,.order_details .btn-group{
		margin-bottom:10px;
	}
	.btn a{
		color:#FFF;
		
	}
	.btn-small{
		padding:3px;
	}
	.btn-small a{
		font-size:14px;
		
	}
	.btn a:hover,a:hover{
		text-decoration:none;
	}
	#menu_container{
		margin-bottom: 30px;
	}
	
	table .btn a{
		font-size:13px;
		margin-left:5px;
		margin-right:5px;
	}
	table .btn-small{
		padding:0px
	}
	._green{
		color:#00B831;
		font-weight:bold;
	}
	 /*Data tables end*/
	caption{
		color:rgb(7, 51, 226);
		font-weight:bold;
		font-size:16px;
		margin-bottom:6px;
	}
	table.dataTable thead th{
		border-bottom: none;
	}
	.btn .caret {
		margin:0 auto;
		vertical-align:middle;
		margin-left:10px;
	}
	#profile_list{
		right:0;
		left:auto;
	}
</style>


</head>

<body>

	<div id="top-panel" style="margin:0px;">

		<div class="logo">
			<a class="logo" href="<?php echo base_url();?>" ></a> 
</div>
<?php if ($user_is_pharmacist) {?>
	<div id="synchronize">
		<div id="loadingDiv"></div>
		<div id="dataDiv" style="display: none;">
		<span style="display: block; font-size: 12px; margin: 10px 5px;">Number of Local Patients: <span id="total_number_local"></span></span>
		<span style="display: block; font-size: 12px; margin: 10px 5px;">Number of Patients Registered: <span id="total_number_registered"></span></span>
		</div>
		<a class="action_button" id="synchronize_button" href="<?php echo base_url();?>synchronize_pharmacy">Synchronize Now</a>
	</div>
	<?php }?>

				<div id="system_title">
					<span style="display: block; font-weight: bold; font-size: 14px; margin:2px;">Ministry of Health</span>
					<span style="display: block; font-size: 12px;">ARV Drugs Supply Chain Management Tool</span>
					<?php
					if ($user_is_pharmacist) {?>
						<style>
							#facility_name {
								color: green;
								margin-top: 5px;
								font-weight: bold;
							}
							#synchronize_button{
								display: none;
								width: 200px;
								margin: 0;
								height: 40px;
								position: absolute;
								top:3.5px;
								left:30px;		
								line-height: 40px;
														
							}
							
						</style>
						<div id="facility_name">
							
							<span style="display: block; font-size: 14px;"><?php echo $this -> session -> userdata('facility_name');?></span>
						</div>
					<?php }?>
				</div>
				<div class="banner_text" style="font-size: 22px;"><?php echo $banner_text;?></div>
 <div id="top_menu"> 

 	<?php
		//Code to loop through all the menus available to this user!
		//Fet the current domain
		$menus = $this -> session -> userdata('menu_items');
		$current = $this -> router -> class;
		$counter = 0;
	?>
 	<a href="<?php echo site_url('home_controller');?>" class="top_menu_link  first_link <?php
	if ($current == "home_controller") {echo " top_menu_active ";
	}
?>">Home </a>
<?php
foreach($menus as $menu){?>
	<a href = "<?php echo base_url().$menu['url'];?>" class="top_menu_link <?php
	if ($current == $menu['url'] || $menu['url'] == $link) {echo " top_menu_active ";
	}
?>"><?php echo $menu['text']; if($menu['offline'] == "1"){?>
	 <span class="alert red_alert">off</span></a>
	
<?php } else{?>
	 <span class="alert green_alert">on</span></a>
<?php }
$counter++;
}
	?>
<script type="text/javascript">
	$(document).ready(function(){
		$("#my_profile").click(function(){
			$("#profile_list").toggle();
		})
	})
</script>
<div  class="btn-group" id="div_profile" >
<a href="#" class="top_menu_link btn dropdown-toggle" data-toggle="dropdown"  id="my_profile"><i class="icon-user icon-black"></i> Profile  <span class="caret"></span></a>
<ul class="dropdown-menu" id="profile_list" role="menu">
	<li><a href="<?php echo base_url().'user_management/profile' ?>"><i class="icon-edit"></i> Edit Profile</a></li>
	<li><a href="<?php echo base_url().'user_management/change_password' ?>"><i class=" icon-asterisk"></i> Change Password</a></li>
</ul>
</div>
<div class="welcome_msg">
	<span>Welcome <b style="font-weight: bolder;font-size: 20px;"><?php echo $this -> session -> userdata('full_name');?></b>. <a href="<?php echo base_url().'user_management/logout' ?>">Logout</a></span><br>
	<br><span><?php echo date('l, jS \of F Y') ?></span>
</div>
 </div>

</div>

<div id="inner_wrapper">
	 
	<div class="row">
		<div class="span3">
	      <?php
	      if($_type=="order"){
	      	$this->load->view("orders_sub_menu"); 
	      }
	      elseif($_type=="picking"){
	      	$this->load->view("picking_list_sub_menu");
	      }
		  elseif($_type=="order_facility"){
	      	if(isset($parent)){
	      		if($parent -> parent == $central_facility){
	      			$this->load->view("facility_orders_sub_menu");
	      		}
	      	}	
	      	
	      }
	      ?> 
	    </div>
	</div>

	
  <div class="row" >
  	
  	<?php
  	//If order_details page,widen the page
  	if(isset($order_details_page)){
  	?>
  	<script type="text/javascript">
  	//Use bootstrap pagination and datatables 
	$(document).ready(function() {
			$('.table_order_details').dataTable( {
		        "sDom": "<'row'r>t<'row'<'span5'i><'span7'p>>",
		        "sPaginationType": "bootstrap",
		        "bSort": false
		        //"aaSorting": []
		    } );
		    
	});
	
	
	</script>
	<div class="order_details" style="width:96%;float:none;margin: 0 auto">
  	<?php	
  	}
	//If not order details page, use normal page structure
	else{
	?>
	<div class="span12" style="float:none;margin: 0 auto">
	<?php	
	}
  	?>
  		<?php
  		
  		//If message comes from
  		if(isset($message)){
  		?>
  		<script>
  			$(document).ready(function(){
  				setTimeout(function(){
  					$('#order_save_message').fadeOut(2000);
  				},4000);
  				
  			});
  		</script>
  		<?php
  			//echo $message;
  		}
  		?>
  		
    	<?php if(isset($page_title)){
    		//If page has a title, display it
	    	?>
	    	<legend><?php echo $page_title ?></legend>
	    	<?php
	    }
		?>
    	
    	<?php
    	if($_type=="order_facility"){
    		
			if (isset($parent)) {
				//If facility is a central facility, display all the oprtions,otherwise, display only create new facility
				if($parent -> parent == $central_facility){
					?>
					<div class="btn-group">
						<button style="margin-right: 2px;" class="btn btn-small btn-info"><a href="<?php echo base_url().'order_management/new_central_order/0'?>">Create Central Order</a></button>
						<button style="margin-right: 2px;" class="btn btn-small btn-info"><a id="btn_new_satellite_order" href="<?php echo base_url().'order_management/new_satellite_order'?>">Create Satellite Order</a></button>
						<button class="btn btn-small btn-info"><a href="<?php echo base_url().'order_management/new_central_order/1'?>">Create Aggregated Order</a></button>
						
					</div>
					<?php
				}
				else{
		    		?>
		    		<div id="menu_container">
						<button class="btn btn-small btn-info"><a href="<?php echo base_url().'order_management/new_satellite_order'?>">New Satellite Facility Order</a></button>
					</div> 
		    		<?php
				}
			}
			
    	}
		?>
    	
    
   	  <?php $this -> load -> view($content_view);?>
    </div>
  </div>

 
 
  <!--End Wrapper div--></div>
    <div id="bottom_ribbon" style="top:20px; width:90%;">
        <div id="footer">
 <?php $this -> load -> view("footer_v");?>
    </div>
    </div>
    
    <!-- Satellite Facility selection ------------------------------ -->
    <?php
		echo validation_errors('
		<p class="error">','</p>
		'); 
		?>
		<script type="text/javascript">
			$(document).ready(function(){
				$(".data_import").dialog({
					width : 500,
					modal : true,
					height: 150,
					autoOpen : false
				 });
				$("#excel_upload").click(function(){
					 $(".data_import").dialog("open");
				});
				$("#satellite_facility_selection").dialog({
					width : 400,
					modal : true,
					height: 170,
					autoOpen : false
				 });
				$("#btn_new_satellite_order").click(function(event){
					var base_url="<?php echo base_url(); ?>";
					event.preventDefault();
					$.ajax({
				        type: "POST",
						url: base_url+ "order_management/new_satellite_order",
						dataType: "json",
						success: function(data){
							$("#satelitte_facility_list").html("");
							$("#satelitte_facility_list").append("<option value='0' >Select facility</option>");
							for(var x in data['facilities']){
								$("#satelitte_facility_list").append("<option value="+data['facilities'][x]['facilitycode']+" >"+data['facilities'][x]['name']+"</option>");
								
							}
							$("#satellite_facility_selection").dialog("open");
						}
						
			      	});
				});
				//Validate satellite facility selection before submitting form
				$("#btn_new_satellite_order_proceed").click(function(){
					var select_facility=$("#satelitte_facility_list").val();
					if(select_facility=="0"){
						alert("Plesae select a facility before proceeding !");
					}
					else{
						$("#frm_satellite_facility_selection").submit();
					}
				})
			});	
		</script>
		
		
			<div id="satellite_facility_selection">
				<form id="frm_satellite_facility_selection" action="<?php echo base_url().'order_management/new_satellite_order'?>" method="POST" style="margin:0 auto;">
			
					<table width="100%"  cellpadding="5" align="center">
						<tr>
							<td colspan='2'>
								<select name="satellite_facility" id="satelitte_facility_list" style="width:300px;height:35px;"></select> 
							</td>
						</tr>
						<tr>
							<td>
							<input type="button" class="btn btn-success btn-large" name="proceed" id="btn_new_satellite_order_proceed" value="Fill Order Form" />
							</td>
							<td>
				         	<button class="btn btn-success btn-large" id="excel_upload">Upload Excel</button>
							</td>	
						</tr>
					</table>
				</form>
			</div>
		
		    
		<div class="data_import" title="Excel Upload">
			<form name="frm" method="post" enctype="multipart/form-data" id="frm" action="<?php echo base_url()."fcdrr_management/data_upload"?>">
			<p>
						<input type="file"  name="file" size="30"  required="required" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"/>
						<input name="btn_save" class="button" type="submit"  value="Save"  style="width:80px; height:30px;"/>
			</p>		
			</form>	
		</div>	
    
    <!-- Satell8te facility selection end --------------------------- -->
    
</body>
</html>
