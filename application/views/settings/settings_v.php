<style type="text/css">
	.full-content {
		width: 95%;
		zoom: 95%;
	}
	#facilities_map{
		width:200px;
	}
	#settings_list{
		font-size:0.8em;
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
					<a href="#" class="setting_link" id="sync_facility">KEMSA FACILITIES</a>
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
					<a href="#" class="setting_link" id="escm_facility">eSCM FACILITIES</a>
				</li>
				<li>
					<a href="#" id="api_sync" class="api_sync">eSCM Settings</a>
				</li>
				<li>
					<a href="#" id="order_sync" class="api_sync">eSCM Orders</a>
				</li>
				<li class="divider"></li>
				<li class="nav-header">
					EID SETTINGS
				</li>
				<li>
					<a href="#" class="api_sync" id="eid_sync">EID/HEI Sync</a>
				</li>
				<li>
					<a href="#" class="setting_link" id="eid_mail">EID Mailing Lists</a>
				</li>
				<li>
					<a href="#" class="setting_link" id="casco_list">CASCO Lists</a>
				</li>
				<li>
					<a href="#" class="setting_link" id="casco_mail">CASCO Mailing Lists</a>
				</li>
				<li class="divider"></li>
				<li class="nav-header">
					COMMON SETTINGS
				</li>
				<li class="active">
					<a href="#" class="setting_link" id="drugcode">DRUGS</a>
				</li>
				<li>
					<a href="#" class="setting_link" id="facilities">FACILITIES</a>
				</li>
				<li>
					<a href="#" class="setting_link" id="regimen">REGIMENS</a>
				</li>
				<li>
					<a href="#" class="setting_link" id="mail_list">MAILING LISTS</a>
				</li>
				<li>
					<a href="#" class="setting_link" id="user_emails">USER EMAILS</a>
				</li>
				<li>
					<a href="#" class="setting_link" id="gitlog">VERSIONS</a>
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
					<a  href="<?php echo site_url("settings/modal/sync_drug");?>"  role="button" id="add_btn" class="btn btn-primary modal_btn" data-toggle="modal"><i class="icon-plus-sign"></i> <span id="create_setting"> add drug</span></a>
				</div>
				<div class="span10">
					<?php echo $this -> session -> flashdata("alert_message");?>
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
		$("#facilities_map").searchable();
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
		$("#add_btn").show();
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
		}else if(type == "mail_list") {
				$("#create_setting").text("add mail list");
				$("#modal_header").text("Add Mail List");
		}else if(type == "user_emails") {
				$("#create_setting").text("add email");
				$("#modal_header").text("Add User Email");
		}else if(type == "facilities") {
				$("#create_setting").text("add facility");
				$("#modal_header").text("Add Facility");
		}else if(type == "drugcode") {
				$("#create_setting").text("add drug");
				$("#modal_header").text("Add Drug");
	    }else if(type == "regimen") {
				$("#create_setting").text("add regimen");
				$("#modal_header").text("Add Regimen");
		}else if(type == "gitlog") {
			$("#add_btn").hide();
			$("#modal_header").text("Add Log");
		}else if(type == "escm_facility") {
			$("#add_btn").hide();
			$("#modal_header").text("Add Facility");
		}else if(type == "eid_mail") {
			$("#create_setting").text("add email");
			$("#modal_header").text("Add EID Email");
		}else if(type == "casco_list") {
			$("#create_setting").text("add List");
			$("#modal_header").text("Add CASCO List");
		}else if(type == "casco_mail") {
			$("#create_setting").text("add email");
			$("#modal_header").text("Add CASCO Email");
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

			$("#add_btn").show();

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
			}else if(type == "mail_list") {
				$("#create_setting").text("add mail list");
				$("#modal_header").text("Add Mail List");
			}else if(type == "user_emails") {
				$("#create_setting").text("add email");
				$("#modal_header").text("Add User Email");
			}else if(type == "facilities") {
				$("#create_setting").text("add facility");
				$("#modal_header").text("Add Facility");
			}else if(type == "drugcode") {
				$("#create_setting").text("add drug");
				$("#modal_header").text("Add Drug");
			}else if(type == "regimen") {
				$("#create_setting").text("add regimen");
				$("#modal_header").text("Add Regimen");
			}else if(type == "escm_facility") {
				$("#add_btn").hide();
				$("#modal_header").text("Add Facility");
			}else if(type == "gitlog") {
			    $("#add_btn").hide();
			    $("#modal_header").text("Add Log");
		    }else if(type == "eid_mail") {
				$("#create_setting").text("add email");
				$("#modal_header").text("Add EID Email");
		    }else if(type == "casco_list") {
				$("#create_setting").text("add List");
				$("#modal_header").text("Add CASCO List");
			}else if(type == "casco_mail") {
				$("#create_setting").text("add email");
				$("#modal_header").text("Add CASCO Email");
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
			
			// multifilter
			if(current == "sync_user") {
				$("#sync_user_facilities").multiselect().multiselectfilter();
				$("#sync_user_facilities").multiselect("uncheckAll");
			}else if(current == "user_emails") {
				$("#user_emails_mail_list").multiselect().multiselectfilter();
				$("#user_emails_mail_list").multiselect("uncheckAll");
			}

			$.each(my_array, function(i, v) {
				$("#" + current + "_" + i).val(v);
				if(i == "id") {
					var action_link = my_url + "settings/save/" + current + "/" + v
					$("#modal_action").attr("action", action_link);
				} else if(i == "facility" && v !=null && type=="sync_user") {
					var family_planning = $.parseJSON(v);
					if(family_planning != null || family_planning != " ") {
						var fplan = family_planning.split(',');
						for(var i = 0; i < fplan.length; i++) {
							$("select#sync_user_facilities").multiselect("widget").find(":checkbox[value='" + fplan[i] + "']").each(function() {
								$(this).click();
							});
						}
					}
				}else if(i == "mail_list" && v !=null) {
					var family_planning = $.parseJSON(v);
					if(family_planning != null || family_planning != " ") {
						var fplan = family_planning.split(',');
						for(var i = 0; i < fplan.length; i++) {
							$("select#user_emails_mail_list").multiselect("widget").find(":checkbox[value='" + fplan[i] + "']").each(function() {
								$(this).click();
							});
						}
					}
				}
			});
			$("#modal_template").modal('show');
			$("#facilities_map").searchable();
		});

		$("#add_btn").click(function() {
			var current = $("#current_setting").text();
			var action_link = my_url + "settings/save/" + current

			$("#modal_template :input").val("");
			$("#modal_action").attr("action", action_link);
			
			if(current=="sync_user"){
				$("#sync_user_facilities").multiselect().multiselectfilter();
				$("#sync_user_facilities").multiselect("uncheckAll");
				$("#modal_template").modal('show');		
			}else if(current=="user_emails"){
				$("#user_emails_mail_list").multiselect().multiselectfilter();
				$("#user_emails_mail_list").multiselect("uncheckAll");
				$("#modal_template").modal('show');		
			}else{
			    $("#modal_template").modal('show');			
			}
		});
		//escm sync function
		$(".api_sync").click(function() {
			var type=$(this).attr("id");
			if(type=="api_sync"){
			   var url = my_url + "settings/api_sync";		
			}else if(type=="order_sync"){
			   var url = my_url + "settings/get_updates";				
			}else if(type=="eid_sync"){
			   var url = my_url + "settings/eid_sync";				
			}
			$.ajax({
				url : url,
				type : 'POST',
				success : function(data) {
					window.location = my_url + "settings";
				}
			});
		});
		//form submit
		$("#modal_action").submit(function() {
			var current = $("#current_setting").text();
			if(current == "sync_user") {
				var facilities = $("select#sync_user_facilities").multiselect("getChecked").map(function() {
					return this.value;
				}).get();
				$("#facilities_holder").val(facilities);
			}else if(current == "user_emails") {
				var facilities = $("select#user_emails_mail_list").multiselect("getChecked").map(function() {
					return this.value;
				}).get();
				$("#mail_list_holder").val(facilities);
			}
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
		} else if(type == "mail_list") {
			var columns = new Array("name", "created by","total emails","options");
		} else if(type == "user_emails") {
			var columns = new Array("email_address","total lists","options");
		} else if(type == "drugcode") {
			var columns = new Array("name","unit","pack size","Price(USD)","Comments","options");
		} else if(type == "facilities") {
			var columns = new Array("mfl code", "name", "category","county","options");
		} else if(type == "regimen") {
			var columns = new Array("code", "name", "category","options");
		}else if(type == "gitlog") {
			var columns = new Array("Facility", "hash value","Status","last update");
		}else if(type == "escm_facility") {
			var columns = new Array("mfl code", "name", "category", "services", "options");
		}else if(type == "eid_mail") {
			var columns = new Array("Email Address", "Facility","options");
		}else if(type == "casco_list") {
			var columns = new Array("Name", "County","options");
		}else if(type == "casco_mail") {
			var columns = new Array("Email Address", "Casco","County","options");
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
