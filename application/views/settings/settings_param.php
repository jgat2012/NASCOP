<div class="container-fluid">
	<!--Grid-->
	<div class="row-fluid">
		<div class="span3">
			<ul class="nav nav-list">
				<li class="nav-header">
					NASCOP SETTINGS
				</li>
				<li class="active">
					<a href="#" class="setting_link" id="sync_drug">DRUGS</a>
				</li>
				<li>
					<a href="#">FACILITIES</a>
				</li>
				<li>
					<a href="#" class="setting_link" id="sync_regimen">REGIMENS</a>
				</li>
				<li>
					<a href="#">USERS</a>
				</li>
				<li class="divider"></li>
				<li class="nav-header">
					eSCM SETTINGS
				</li>
				<li>
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
			<!--Tables-->
			<div id="table_grid" class="table-responsive"></div>
			<div id="gridChartContainer" class="table-responsive">
				<table id="grid"></table>
				<div id="chart"></div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
	$(document).ready(function() {

		// Data
		var populationData = [{
			"CountryName" : "China",
			"1995" : 1216,
			"2005" : 1297,
			"2015" : 1361,
			"2025" : 1394
		}, {
			"CountryName" : "India",
			"1995" : 920,
			"2005" : 1090,
			"2015" : 1251,
			"2025" : 1396
		}, {
			"CountryName" : "United States",
			"1995" : 266,
			"2005" : 295,
			"2015" : 322,
			"2025" : 351
		}, {
			"CountryName" : "Indonesia",
			"1995" : 197,
			"2005" : 229,
			"2015" : 256,
			"2025" : 277
		}, {
			"CountryName" : "Brazil",
			"1995" : 161,
			"2005" : 186,
			"2015" : 204,
			"2025" : 218
		}];

		// Grid
		$("#grid").igGrid({
			width : "100%",
			dataSource : populationData,
			autoGenerateColumns : true,
			features : [{
				name : "Sorting",
				type : "local",
				mode : "multi"
			}, {
				name : "Filtering",
				type : "local",
				mode : "advanced"
			}, {
				name : "Hiding"
			}, {
				name : "ColumnMoving"
			}, {
				name : 'Paging',
				type : "local",
				pageSize : 10
			}]
		});

		var base_url = "http://localhost/NASCOP/";
		$(".setting_link").click(function() {
			var link_name = $(this).attr("id");
			var url = base_url + "settings/get/" + link_name;
			if(link_name == "sync_drug") {
				var columns = new Array("name", "abbreviation", "strength", "packsize", "formulation", "unit", "weight", "options");
			}
			//Generate Columns
			var thead = "<table id='setting_grid'><thead><tr>";
			$.each(columns, function(i, v) {
				thead += "<th>" + v + "</th>";
			});
			thead += "</tr></thead></table>";
			$("#table_grid").empty();
			$("#table_grid").append(thead);

			$('#setting_grid').dataTable({
				"bProcessing" : true,
				"bServerSide" : true,
				"sAjaxSource" : url,
				"bJQueryUI" : true,
				"sPaginationType" : "full_numbers"
			});

		});
		//End of function
		$(".setting_link1").click(function() {
			/*
			 var link_name = $(this).attr("id");
			 var url = base_url + "settings/get/" + link_name;

			 $('#table_grid').jtable({
			 title : 'Table of people',
			 actions : {
			 listAction : '/settings/get/' + link_name,
			 createAction : '/GettingStarted/CreatePerson',
			 updateAction : '/GettingStarted/UpdatePerson',
			 deleteAction : '/GettingStarted/DeletePerson'
			 },
			 fields : {
			 PersonId : {
			 key : true,
			 list : false
			 },
			 Name : {
			 title : 'Author Name',
			 width : '40%'
			 },
			 Age : {
			 title : 'Age',
			 width : '20%'
			 },
			 RecordDate : {
			 title : 'Record date',
			 width : '30%',
			 type : 'date',
			 create : false,
			 edit : false
			 }
			 }
			 });
			 */
		});
		//End of function
	});

</script>
