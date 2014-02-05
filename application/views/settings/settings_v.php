<style type="text/css">
	.full-content {
		width: 95%;
		zoom: 100%;
	}
</style>
<div class="container-fluid full-content">
	<!--Grid-->
	<div class="row-fluid">
		<div class="span3">
			<ul id="settings_list" class="nav nav-list">
				<li class="nav-header">
					NASCOP SETTINGS
				</li>
				<li class="active">
					<a href="#" class="setting_link" id="sync_drug">DRUGS</a>
				</li>
				<li>
					<a href="#" class="setting_link" id="sync_facility">FACILITIES</a>
				</li>
				<li>
					<a href="#" class="setting_link" id="sync_regimen">REGIMENS</a>
				</li>
				<li>
					<a href="#" class="setting_link" id="sync_user">USERS</a>
				</li>
				<li class="divider"></li>
				<li class="nav-header">
					eSCM SETTINGS
				</li>
				<li>
					<a href="#" class="api_sync">eSCM SYNC</a>
				</li>
			</ul>
		</div>
		<div class="span9">
			<!--BreadCrumb-->
			<ul class="breadcrumb">
				<li>
					<a href="<?php echo site_url("home_controller/home");?>">HOME</a><span class="divider">/</span>
				</li>
				<li>
					<a href="<?php echo site_url("settings");?>">SETTINGS</a><span class="divider">/</span>
				</li>
				<li class="active">
					<span id="current_setting"></span></a>
				</li>
			</ul>
			<!--Tables-->
			<a href="#myModal" role="button" class="btn btn-info" data-toggle="modal">new <span id="create_setting">drug</span></a>
			<div id="table_grid" class="table-responsive"></div>
		</div>
	</div>
</div>
<script type="text/javascript">
	$(document).ready(function() {
		var base_url = "http://localhost/NASCOP/";
		//default link
		var type = "sync_drug";
		var url = base_url + "settings/get/" + type;
		var div_id = "#table_grid";

		getTable(type, url, div_id);

		//Set Current setting breadcrumb
		$("#current_setting").text(type);

		//function onclick to select grid to display
		$(".setting_link").click(function() {
			//Change active
			$("#settings_list>li").removeClass("active");
			$(this).closest('li').addClass('active');

			var type = $(this).attr("id");
			var url = base_url + "settings/get/" + type;

			getTable(type, url, div_id);

			//Set Current setting breadcrumb
			$("#current_setting").text(type);
		});
		//End of function
	});
	function getTable(type, url, div_id) {
		if(type == "sync_drug") {
			var columns = new Array("name", "abbreviation", "strength", "packsize", "formulation", "unit", "weight", "options");
		} else if(type == "sync_facility") {
			var columns = new Array("mfl code", "name", "category", "services", "options");
		} else if(type == "sync_regimen") {
			var columns = new Array("code", "name", "description", "options");
		} else if(type == "sync_user") {
			var columns = new Array("name", "email", "role", "phone", "options");
		}
		//Generate Columns
		var thead = "<table id='setting_grid' class='table table-bordered table-hover table-condensed'><thead><tr>";
		$.each(columns, function(i, v) {
			thead += "<th>" + v + "</th>";
		});
		thead += "</tr></thead></table>";
		$(div_id).empty();
		$(div_id).append(thead);

		$('#setting_grid').dataTable({
			"bProcessing" : true,
			"bServerSide" : true,
			"sAjaxSource" : url,
			"bJQueryUI" : true,
			"sPaginationType" : "full_numbers",
			"bAutoWidth" : false,
			"bDeferRender" : true,
			"bInfo" : true,
			"bProcessing" : true,
			"bSort" : true,
			"bSortClasses" : true,
			"bStateSave" : true,
			"sScrollX" : "100%",
			"bScrollCollapse" : true,
			"sScrollY" : "200px",
			"bDestroy" : true,
			"iCookieDuration" : 60 * 30,
			"oSearch" : {
				"bRegex" : true
			},
			"sCookiePrefix" : type + "_",
			"aoColumnDefs" : [{
				"iDataSort" : 0,
				"aTargets" : [0]
			}]
		});
	}
</script>
