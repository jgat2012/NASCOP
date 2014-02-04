<style type="text/css">
	.ui-iggrid .ui-iggrid-footer, .ui-iggrid .ui-iggrid-toolbar {
		background-color: #2B597E;
	}
	.ui-state-default, .ui-widget-header .ui-state-default {
		background: #666;
	}
	.full-content {
		width: 95%;
		zoom: 100%;
	}
	.ui-iggrid-scrolldiv tbody {
		font-size: 12px;
	}
	.ui-iggrid-pagedropdowncontainer{
		display:none;
	}
</style>
<div class="container-fluid full-content">
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
				<script id="rowEditDialogRowTemplate1" type="text/x-jquery-tmpl">
					<tr>
					<td class="labelBackGround">${headerText}
					{{if ${dataKey} == 'BirthDate'}}<span style="color: red;">*</span>{{/if}}
					</td>
					<td data-key='${dataKey}'>
					<input />
					</td>
					</tr>
				</script>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
	$(document).ready(function() {
		var base_url = "http://localhost/NASCOP/";

		// Grid
		$("#grid").igGrid({
			primaryKey : "name",
			width : "100%",
			dataSource : base_url + "settings/get/sync_regimen",
			//updateUrl : base_url + "settings/update/sync_regimen",
			autoGenerateColumns : true,
			autoAdjustHeight : false,
			alternateRowStyles : true,
			autofitLastColumn : true,
			fixedHeaders : true,
			height : "300px",
			width : "100%",
			enableHoverStyles : true,
			features : [{
				name : "Sorting",
				type : "local",
				mode : "multi"
			}, {
				name : "Filtering",
				type : "local",
				mode : "advanced",
				advancedModeEditorsVisible : true,
				nullTexts : {
					contains : "",
					equals : ""
				}

			}, {
				name : "Hiding"
			}, {
				name : "ColumnMoving"
			}, {
				name : 'Paging',
				type : "local",
				pageSize : 10,
				defaultDropDownWidth : 70
			}, {
				name : "Updating",
				enableAddRow : true,
				editMode : "rowedittemplate",
				rowEditDialogWidth : 600,
				rowEditDialogHeight : '400',
				rowEditDialogContentHeight : 300,
				rowEditDialogFieldWidth : 350,
				rowEditDialogContainment : "window",
				rowEditDialogRowTemplateID : "rowEditDialogRowTemplate1",
				enableDeleteRow : false,
				showReadonlyEditors : false,
				showDoneCancelButtons : true,
				enableDataDirtyException : false,
				columnSettings : [{
					columnKey : "name",
					editorOptions : {
						type : "text",
						disabled : false
					}
				}]
			}, {
				name : "Selection",
				mode : "row"
			}]
		});

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
