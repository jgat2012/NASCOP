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
			<div class="row-fluid">
				<div class="span2">
					<a  href="<?php echo site_url("settings/modal/sync_drug");?>" data-target="#modal_template" role="button" id="add_btn" class="btn btn-primary modal_btn" data-toggle="modal"><i class="icon-plus-sign"></i> <span id="create_setting"> add drug</span></a>
				</div>
				<div class="span10">
					<?php  echo $this -> session -> flashdata("alert_message");?>
				</div>
			</div>
			<div id="table_grid" class="table-responsive"></div>
			<!-- Modal -->
			<div id="modal_template" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
				<form class="form-horizontal" action="<?php echo base_url() . 'settings/save/sync_drug';?>" id="modal_action" method="post">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
							×
						</button>
						<h3 id="myModalLabel"><span id="modal_header">Add Drug</span></h3>
					</div>
					<div class="modal-body"></div>
					<div class="modal-footer">
						<button class="btn" data-dismiss="modal" aria-hidden="true">
							Close
						</button>
						<button class="btn btn-primary">
							Save changes
						</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
	$(document).ready(function() {
		var my_url = "<?php echo base_url(); ?>";
		//default link
		var type = "<?php if($this -> session -> userdata("nav_link") !=""){echo $this -> session -> userdata("nav_link");}else{echo "sync_drug";} ?>";
		var url = my_url + "settings/get/" + type;
		var div_id = "#table_grid";

		getTable(type, url, div_id);

		//set default active in nav list
		$("#settings_list>li").removeClass("active");
		$('#settings_list li').each(function(n, v) {
			var active_nav = $(this).find("a[id=" + type + "]");
			active_nav.closest('li').addClass('active');
		});
		//add button label
		if(type == "sync_drug") {
			$("#create_setting").text("add drug");
			$("#modal_header").text("Add Drug");
		} else if(type == "sync_facility") {
			$("#create_setting").text("add facility");
			$("#modal_header").text("Add Facility");
		} else if(type == "sync_regimen") {
			$("#create_setting").text("add regimen");
			$("#modal_header").text("Add Regimen");
		} else if(type == "sync_user") {
			$("#create_setting").text("add user");
			$("#modal_header").text("Add User");
		}

		var link = my_url + "settings/modal/" + type
		$(".modal_btn").attr("href", link);
		$(".modal-body").load(link);
		var action_link = my_url + "settings/save/" + type
		$("#modal_action").attr("action", action_link);

		//load default modal
		var link = my_url + "settings/modal/" + type
		$(".modal-body").load(link);

		//Set Current setting breadcrumb
		$("#current_setting").text(type);

		//function onclick to select grid to display
		$(".setting_link").live("click", function() {
			//Change active
			$("#settings_list>li").removeClass("active");
			$(this).closest('li').addClass('active');

			var type = $(this).attr("id");
			var url = my_url + "settings/get/" + type;

			getTable(type, url, div_id);

			//Set Current setting breadcrumb
			$("#current_setting").text(type);

			//add button label
			if(type == "sync_drug") {
				$("#create_setting").text("add drug");
				$("#modal_header").text("Add Drug");
			} else if(type == "sync_facility") {
				$("#create_setting").text("add facility");
				$("#modal_header").text("Add Facility");
			} else if(type == "sync_regimen") {
				$("#create_setting").text("add regimen");
				$("#modal_header").text("Add Regimen");
			} else if(type == "sync_user") {
				$("#create_setting").text("add user");
				$("#modal_header").text("Add User");
			}
			var link = my_url + "settings/modal/" + type
			$(".modal_btn").attr("href", link);
			$(".modal-body").load(link);
			var action_link = my_url + "settings/save/" + type
			$("#modal_action").attr("action", action_link);

		});
		//End of function

		$(".edit_item").live("click", function() {
			var my_array = $(this).data("mydata");
			var current = $("#current_setting").text();

			$.each(my_array, function(i, v) {
				$("#" + current + "_" + i).val(v);
				if(i == "id") {
					var action_link = my_url + "settings/save/" + current + "/" + v
					$("#modal_action").attr("action", action_link);
				}
			});
			$("#modal_template").modal('show');
		});

		$("#add_btn").live("click", function() {
			var current = $("#current_setting").text();
			var action_link = my_url + "settings/save/" + current

			$("#modal_template :input").val("");
			$("#modal_action").attr("action", action_link);
		});
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
