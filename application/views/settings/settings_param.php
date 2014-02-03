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
					<a href="#">REGIMENS</a>
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
			<div id="table_grid" class="table-responsive">
				
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
	$(document).ready(function() {
		var base_url = "http://localhost/NASCOP/";
		$(".setting_link").click(function() {
			var link_name = $(this).attr("id");
			var url = base_url + "settings/get/" + link_name;
			if(link_name == "sync_drug") {
				var columns = new Array("name", "abbreviation", "strength", "packsize", "formulation", "unit", "weight","options");
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
	});

</script>
