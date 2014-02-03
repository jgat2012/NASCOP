<div class="container-fluid">
	<!--Grid-->
	<div class="row-fluid">
		<div class="span3">
			<ul class="nav nav-list">
				<li class="nav-header">
					NASCOP SETTINGS
				</li>
				<li class="active">
					<a href="#" class="setting_link" id="nascop_drugs">DRUGS</a>
				</li>
				<li>
					<a href="#">FACILITIES</a>
				</li>
				<li>
					<a href="#">REGIMENS</a>
				</li>
				<li>
					<a href="#">USERS</a>
				</li>
				<li class="divider"></li>
				<li class="nav-header">
					eSCM SETTINGS
				</li>
				<li class="active">
					<a href="#">DRUGS</a>
				</li>
				<li>
					<a href="#">FACILITIES</a>
				</li>
				<li>
					<a href="#">REGIMENS</a>
				</li>
			</ul>
		</div>
		<div class="span9">
			<!--BreadCrumb-->
			<ul class="breadcrumb">
				<li>
					<a href="#">HOME</a><span class="divider">/</span>
				</li>
				<li class="active">
					<a href="#">SETTINGS</a>
				</li>
			</ul>
			<!--Filter-->
			<div class="filtering">
				<form>
					Name:
					<input type="text" name="name" id="name" />
					<button type="submit" id="LoadRecordsButton">
						Load records
					</button>
				</form>
			</div>
			<!--Tables-->
			<div id="setting_grid" class="table-responsive"></div>
		</div>
	</div>
</div>
<script type="text/javascript">
	$(document).ready(function() {
		var base_url = "http://localhost/NASCOP/";
		$(".setting_link").click(function() {
			var link_name = $(this).attr("id");
			$('#setting_grid').jtable({
				title : 'NASCOP DRUGS',
				paging : true, //Enable paging
				pageSize : 10, //Set page size (default: 10)
				sorting : true, //Enable sorting
				defaultSorting : 'id ASC', //Set default sorting
				actions : {
					listAction : base_url + 'settings/get/' + link_name,
					createAction : base_url + 'settings/create' + link_name,
					updateAction : base_url + 'settings/update' + link_name,
					deleteAction : base_url + 'settings/delete/' + link_name
				},
				fields : {
					id : {
						key : true,
						list : false
					},
					name : {
						title : 'Drug Name',
						width : '40%'
					},
					abbreviation : {
						title : 'Abbreviation',
						width : '20%'
					},
					strength : {
						title : 'Strength',
						width : '20%'
					},
					packsize : {
						title : 'Packsize',
						width : '20%'
					},
					formulation : {
						title : 'Formulation',
						width : '20%'
					},
					unit : {
						title : 'Unit',
						width : '20%'
					},
					weight : {
						title : 'Weight',
						width : '30%',
					}
				}
			});
			$('#setting_grid').jtable('load');
		});
		//End of function
		//Re-load records when user click 'load records' button.
		$('#LoadRecordsButton').click(function(e) {
			e.preventDefault();
			$('#setting_grid').jtable('load', {
				name : $('#name').val(),
			});
		});
	});
</script>
