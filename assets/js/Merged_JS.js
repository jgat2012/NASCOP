
/**
 *Change password validation
 */
$(document).ready(function() {
	setTimeout(function() {
		$(".message,.alert").fadeOut("2000");
	}, 30000);
	var base_url = $("#base_url").val();
	
	
	//Progress Bar
	function progress(percent, $element) {
		var progressBarWidth = percent * $element.width() / 100;
		$element.find('.bar').animate({
			width : progressBarWidth
		}, 500).html(percent + "%&nbsp;");

	}


	$(".delete").live("click", function() {
		var check = confirm("Are you sure?");
		if(check) {
			return true;
		} else {
			return false;
		}
	});
	/*Ensure Correct Phone format is used*/
	$(".phone").live("change", function() {
		var phone = $(this).val();
		var phone_length = phone.length;
		var number_length = 10;
		/*
		 * 1.Check Number Length
		 * 2.If yes,check if first characters are 07{
		 * 3.if matches 07 alert successful
		 * 4.if no match alert your phone number should start with 07}
		 * 5.if no,alert incorrect phone format used
		 */
		if(phone_length == number_length) {
			var first_char = phone.substr(0, 2);
			if(first_char != 07) {
				alert("your phone number should start with 07")
			}
		} else {
			alert("incorrect phone format used");
		}
	});
	
	$('.dataTables').dataTable({
		"bJQueryUI" : true,
		"sPaginationType" : "full_numbers",
		"sDom" : '<"H"Tfr>t<"F"ip>',
		"oTableTools" : {
			"sSwfPath" : base_url + "scripts/datatable/copy_csv_xls_pdf.swf",
			"aButtons" : ["copy", "print", "xls", "pdf"]
		},
		"bProcessing" : true,
		"bServerSide" : false,
	});
	//syncOrders("13050");

	var base_url = $("#base_url").val();
	$("#change_password_link").click(function() {
		$("#old_password").attr("value", "");
		$("#new_password").attr("value", "");
		$("#new_password_confirm").attr("value", "");
		$(".error").html("");
		$(".error").css("display", "none");
		$("#result").html("");
	});

	$(".error").css("display", "none");
	$('#new_password').keyup(function() {
		$('#result').html(checkStrength($('#new_password').val()));
	});
	function checkStrength(password) {

		//initial strength
		var strength = 0;

		//if the password length is less than 6, return message.
		if(password.length < 6) {
			$('#result').removeClass();
			$('#result').addClass('short');
			return 'Too short';
		}

		//length is ok, lets continue.

		//if length is 8 characters or more, increase strength value
		if(password.length > 7)
			strength += 1;
		if(password.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/))
			strength += 1;
		if(password.match(/([a-zA-Z])/) && password.match(/([0-9])/))
			strength += 1;
		if(password.match(/([!,%,&,@,#,$,^,*,?,_,~])/))
			strength += 1;
		if(password.match(/(.*[!,%,&,@,#,$,^,*,?,_,~].*[!,",%,&,@,#,$,^,*,?,_,~])/))
			strength += 1;
		if(strength < 2) {
			$('#result').removeClass();
			$('#result').addClass('weak');
			return 'Weak';
		} else if(strength == 2) {
			$('#result').removeClass();
			$('#result').addClass('good');
			return 'Good';
		} else {
			$('#result').removeClass();
			$('#result').addClass('strong');
			return 'Strong';
		}
	}


	$("#btn_submit_change_pass").click(function(event) {
		var base_url = $("#base_url").val();
		$(".error").css("display", "none");
		$('#result_confirm').html("");
		event.preventDefault();
		var old_password = $("#old_password").attr("value");
		var new_password = $("#new_password").attr("value");
		var new_password_confirm = $("#new_password_confirm").attr("value");

		if(new_password == "" || new_password_confirm == "" || old_password == "") {
			$(".error").css("display", "block");
			$("#error_msg_change_pass").html("All fields are required !");
		} else if($('#new_password').val().length < 6) {
			$(".error").css("display", "block");
			$("#error_msg_change_pass").html("Your password must have more than 6 characters!");
		} else if($("#result").attr("class") == "weak") {
			$(".error").css("display", "block");
			$("#error_msg_change_pass").html("Please enter a strong password!");
		} else if(new_password != new_password_confirm) {
			$(".error").css("display", "block");
			$('#result_confirm').removeClass();
			$('#result_confirm').addClass('short');
			$("#error_msg_change_pass").html("You passwords do not match !");
		} else {
			$(".error").css("display", "none");
			$("#m_loadingDiv").css("display", "block");
			//$("#fmChangePassword").submit();
			var _url = base_url + "user_management/save_new_password/2";
			var request = $.ajax({
				url : _url,
				type : 'post',
				data : {
					"old_password" : old_password,
					"new_password" : new_password
				},
				dataType : "json"
			});
			request.done(function(data) {
				$("#m_loadingDiv").css("display", "none");
				$.each(data, function(key, value) {
					if(value == "password_no_exist") {
						$("#error_msg_change_pass").css("display", "block");
						$("#error_msg_change_pass").html("You entered a wrong password!");
					} else if(value == "password_exist") {
						$("#error_msg_change_pass").css("display", "block");
						$("#error_msg_change_pass").html("Your new password matches one of your three pevious passwords!");
					} else if(value == "password_changed") {
						$("#error_msg_change_pass").css("display", "block");
						$("#error_msg_change_pass").removeClass("error");
						$("#error_msg_change_pass").addClass("success");
						$("#error_msg_change_pass").html("Your password was successfully updated!");
						window.setTimeout('location.reload()', 3000);
					} else {
						alert(value);
					}
				});
			});
			request.fail(function(jqXHR, textStatus) {
				alert("An error occured while updating your password : " + textStatus + ". Please try again or contact your system administrator!");
			});
		}
	});
	$.extend($.gritter.options, {
		position : 'bottom-right', // defaults to 'top-right' but can be 'bottom-left', 'bottom-right', 'top-left', 'top-right' (added in 1.7.1)
		fade_in_speed : 'medium', // how fast notifications fade in (string or int)
		fade_out_speed : 2000, // how fast the notices fade out
		time : 6000 // hang on the screen for...
	});

});
/**
 * End Change password validation
 */

/*
 * Auto logout
 */
var timer = 0;
function set_interval() {
	/** the interval 'timer' is set as soon as the page loads
	the figure '7200000' above indicates how many milliseconds the timer be set to.
	Eg: to set it to 5 mins, calculate 3min = 3x60 = 180 sec = 180,000 millisec.
	So set it to 180000
	*/
	//timer = setInterval("auto_logout()", 7200000);
}

function reset_interval() {
	//resets the timer. The timer is reset on each of the below events:
	// 1. mousemove   2. mouseclick   3. key press 4. scroliing
	//first step: clear the existing timer

	if(timer != 0) {
		clearInterval(timer);
		timer = 0;
		// second step: implement the timer again
		timer = setInterval("auto_logout()", 3600000);
		// completed the reset of the timer
	}
}

function auto_logout() {
	var base_url = $("#base_url").val();
	// this function will redirect the user to the logout script
	window.location = base_url + "user_management/logout";
}

/*
* Auto logout end
*/

//Function to get data for ordering(Cdrr)
function getPeriodDrugBalance(count, drug, start_date, end_date) {
	var href = window.location.href;
	var base_url = href.substr(href.lastIndexOf('http://'), href.lastIndexOf('/ADT'));
	var _href = href.substr(href.lastIndexOf('/') + 1);
	var link = base_url + '/ADT/order_management/getPeriodDrugBalance/' + drug + '/' + start_date + '/' + end_date;
	$.ajax({
		url : link,
		type : 'POST',
		dataType : 'json',
		success : function(data) {
			var total_received = 0;
			var total_dispensed = 0;
			var drug_id = 0;
			$.each(data, function(i, jsondata) {
				total_received = jsondata.total_received;
				total_dispensed = jsondata.total_dispensed;
				drug_id = jsondata.drug;
			});
			var total_received_div = "#received_in_period_" + drug_id;
			var total_dispensed_div = "#dispensed_in_period_" + drug_id;
			$(total_received_div).attr("value", total_received);
			$(total_dispensed_div).attr("value", total_dispensed);
			calculateResupply($(total_dispensed_div));
			//Once the calculations are done for the whole table, put back the pagination
			if($(".ordered_drugs").length == count) {
				$('#generate_order').dataTable({
					"sDom" : "<'row'r>t<'row'<'span5'i><'span7'p>>",
					"sPaginationType" : "bootstrap",
					"bSort" : false,
					'bDestroy' : true
				});
			}

		}
	});
}

//Function to get data for ordering(Maps)
function getPeriodRegimenPatients(start_date, end_date) {
	var href = window.location.href;
	var base_url = href.substr(href.lastIndexOf('http://'), href.lastIndexOf('/ADT'));
	var _href = href.substr(href.lastIndexOf('/') + 1);
	var link = base_url + '/ADT/order_management/getPeriodRegimenPatients/' + start_date + '/' + end_date;
	$.ajax({
		url : link,
		type : 'POST',
		dataType : 'json',
		success : function(data) {
			var total_patients = 0;
			var total_patients_div = "";
			$.each(data, function(i, jsondata) {
				total_patients = jsondata.patients;
				total_patients_div = "#patient_numbers_" + jsondata.regimen;
				$(total_patients_div).attr("value", total_patients);
			});
		}
	});

}

function getPercentage(count, total) {
	return (count / total) * 100;
}

/*
 * Sysnchronization of Orders
 */
function syncOrders() {
	var href = window.location.href;
	var base_url = href.substr(href.lastIndexOf('http://'), href.lastIndexOf('/ADT'));
	var _href = href.substr(href.lastIndexOf('/') + 1);
	var link = base_url + "/ADT/synchronization_management/startSync";
	$.ajax({
		url : link,
		type : 'POST',
		success : function(data) {

			$.gritter.add({
				// (string | mandatory) the heading of the notification
				title : 'Synchronization.',
				// (string | mandatory) the text inside the notification
				text : data,
				// (string | optional) the image to display on the left
				// (bool | optional) if you want it to fade out on its own or just sit there
				sticky : false,
				// (int | optional) the time you want it to be alive for before fading out
				time : ''
			});

			//alert(data)
		}
	});

}

/*
 *Synchronizes drug stock balance
 */
function synch_drug_balance(stock_type) {
	var base_url = $("#base_url").val();
	$(".bar").css("width", "0%");
	$(".sync_complete").html("");
	$(".modal-footer").css("display", "none");
	//Get number total number of drugs
	var _url = base_url + "drug_stock_balance_sync/getDrugs";
	var stock_type = stock_type;
	$.ajax({
		url : _url,
		type : 'POST',
		data : {
			"check_if_malicious_posted" : "1"
		},
		success : function(data) {
			data = $.parseJSON(data);
			//Count number of drugs
			var count_drugs = data.count;

			$("#div_tot_drugs").css("display", "block");
			$("#tot_drugs").html(count_drugs);

			var remaining_drugs = 1;
			$.each(data.drugs, function(key, value) {

				//Start synch
				var drug_id = value.id;
				var link = base_url + "drug_stock_balance_sync/synch_balance";
				var div_width = (remaining_drugs / count_drugs) * 100;
				$.ajax({
					url : link,
					type : 'POST',
					data : {
						"check_if_malicious_posted" : "1",
						"drug_id" : drug_id,
						"stock_type" : stock_type
					},
					success : function(data1) {
						remaining_drugs += 1;
						div_width1 = (remaining_drugs / count_drugs) * 100;
						div_width = div_width1 + "%";
						if(stock_type == 1) {
							$(".bar_store").css("width", div_width);
						} else if(stock_type == 2) {
							$(".bar_pharmacy").css("width", div_width);
						}

						//div_percentage=div_width1.toFixed(0);
						//$(".bar").html(div_percentage);
						if(remaining_drugs == count_drugs) {
							//$(".icon_drug_balance").css("display","block");
							//$(".progress").removeClass("active");
							//Start sync for pharmacy
							if(stock_type == 1) {
								synch_drug_balance(2);
							} else if(stock_type == 2) {
								//$(".sync_complete").html("Synchronization successfully completed !<i class='icon-ok'></i>");
								//$(".modal-footer").css("display","block");
								synch_drug_movement_balance("1");
							}

						}
					}
				});

			});
		}
	});
}

//Synchronizes drug stock  movement balance
function synch_drug_movement_balance(stock_type) {
	var base_url = $("#base_url").val();
	$(".bar_dsm").css("width", "0%");
	//$(".sync_complete").html("");
	$(".modal-footer").css("display", "none");
	//Get number total number of drugs
	var _url = base_url + "drug_stock_balance_sync/getDrugs";
	var stock_type = stock_type;
	$.ajax({
		url : _url,
		type : 'POST',
		data : {
			"check_if_malicious_posted" : "1"
		},
		success : function(data) {
			data = $.parseJSON(data);
			//Count number of drugs
			var count_drugs = data.count;

			//$("#div_tot_drugs").css("display","block");
			//$("#tot_drugs").html(count_drugs);

			var remaining_drugs = 1;
			$.each(data.drugs, function(key, value) {

				//Start synch
				var drug_id = value.id;
				var link = base_url + "drug_stock_balance_sync/drug_stock_movement_balance";
				var div_width = (remaining_drugs / count_drugs) * 100;
				$.ajax({
					url : link,
					type : 'POST',
					data : {
						"check_if_malicious_posted" : "1",
						"drug_id" : drug_id,
						"stock_type" : stock_type
					},
					success : function(data1) {
						remaining_drugs += 1;
						div_width1 = (remaining_drugs / count_drugs) * 100;
						div_width = div_width1 + "%";
						if(stock_type == 1) {
							$(".bar_store_dsm").css("width", div_width);
						} else if(stock_type == 2) {
							$(".bar_pharmacy_dsm").css("width", div_width);
						}

						//div_percentage=div_width1.toFixed(0);
						//$(".bar").html(div_percentage);
						if(remaining_drugs == count_drugs) {
							//$(".icon_drug_balance").css("display","block");
							//$(".progress").removeClass("active");
							//Start sync for pharmacy
							if(stock_type == 1) {
								synch_drug_movement_balance(2);
							} else if(stock_type == 2) {
								//$(".sync_complete").html("Synchronization successfully completed !<i class='icon-ok'></i>");
								//$(".modal-footer").css("display","block");
								drug_cons_synch();
							}

						}
					}
				});

			});
		}
	});
}

//Drug consumption balance
function drug_cons_synch() {
	var base_url = $("#base_url").val();
	$(".bar_dcb").css("width", "0%");
	$(".modal-footer").css("display", "none");
	//Get number total number of drugs
	var _url = base_url + "drug_stock_balance_sync/get_drug_details_cons";
	var stock_type = 2;
	$.ajax({
		url : _url,
		type : 'POST',
		data : {
			"check_if_malicious_posted" : "1"
		},
		success : function(data) {
			console.log(data);
			data = $.parseJSON(data);
			//Count number of drugs
			var count_drugs = data.count;
			var remaining_drugs = 1;
			$.each(data.drugs, function(key, value) {

				//Start synch
				var drug_id = value.drug_id;
				var period = value.period;
				var total = value.total;
				var link = base_url + "drug_stock_balance_sync/drug_consumption";
				var div_width = (remaining_drugs / count_drugs) * 100;
				$.ajax({
					url : link,
					type : 'POST',
					data : {
						"check_if_malicious_posted" : "1",
						"drug_id" : drug_id,
						"stock_type" : stock_type,
						"period" : period,
						"total" : total
					},
					success : function(data1) {
						remaining_drugs += 1;
						div_width1 = (remaining_drugs / count_drugs) * 100;
						div_width = div_width1 + "%";
						$(".bar_dcb").css("width", div_width);

						if(remaining_drugs == count_drugs) {
							$(".sync_complete").html("Synchronization successfully completed !<i class='icon-ok'></i>");
							$(".modal-footer").css("display", "block");
						}

					}
				});

			});
		}
	});
}

/*
*Reports JS
*/
//-------- Date picker -------------------------

$(document).ready(function() {

	var href = window.location.href;
	var _href = href.substr(href.lastIndexOf('/') + 1);
	var href_final = _href.split('.');
	//Hide current page from menus
	var _id = "#" + href_final[0];
	$(".select_types").css("display", "none");

	/*
	 * Reports JS
	 */

	$(".generate_btn").live('click', function() {

		var base_url = $("#base_url").val();
		if($(".input-medium").is(":visible") || $(".report_type").is(":visible") || $(".report_type_1").is(":visible") || $(".input_year").is(":visible") || $(".input_dates").is(":visible") || $(".donor_input_dates_from").is(":visible") || $(".input_dates_from").is(":visible") || $(".donor_input_dates_to").is(":visible") || $(".input_dates_to").is(":visible")) {

			if($(".input_year").is(":visible") && $(".input_year").val() == "") {
				alert("Please enter the year");
				return;
			}
			//Dates not selected
			if($(".input_dates").is(":visible") && $(".input_dates").val() == "") {
				alert("Please select the date");
			}
			//Dates not selected
			else if($(".input_dates_from").is(":visible") && $(".input_dates_from").val() == "") {
				alert("Please select the starting date");
			}
			//Dates not selected
			else if($(".donor_input_dates_from").is(":visible") && $(".donor_input_dates_from").val() == "") {
				alert("Please select the starting date");
			}
			//Dates not selected
			else if($(".input_dates_to").is(":visible") && $(".input_dates_to").val() == "") {
				alert("Please select the end date");
			}
			//Dates not selected
			else if($(".donor_input_dates_to").is(":visible") && $(".donor_input_dates_to").val() == "") {
				alert("Please select the end date");
			}

			//Dropdown not chosen
			else if($("#commodity_summary_report_type").is(":visible") && $("#commodity_summary_report_type").val() == 0) {
				alert("Please select the report type");
			} else if($("#commodity_summary_report_type_1").is(":visible") && $("#commodity_summary_report_type_1").val() == 0) {
				alert("Please select the report type");
			}
			//If everything is ok,generatea report
			else {

				var id = $(this).attr("id");
				if(id == "generate_date_range_report") {
					var report = $(".select_report:visible").attr("value");
					var from = $("#date_range_from").attr("value");
					var to = $("#date_range_to").attr("value");
					if($(".report_type").is(":visible")) {
						report = report + "/" + $(".report_type:visible").attr("value");
					}
					var report_url = base_url + "report_management/" + report + "/" + from + "/" + to;
					window.location = report_url;
				} else if(id == "generate_month_range_report") {
					var report = $(".select_report:visible").attr("value");
					var from = $("#period_start_date").attr("value");
					var to = $("#period_end_date").attr("value");
					if($(".report_type").is(":visible")) {
						report = report + "/" + $(".report_type:visible").attr("value");
					}
					var report_url = base_url + "report_management/" + report + "/" + from + "/" + to;
					window.location = report_url;
				} else if(id == "generate_single_date_report") {
					var report = $(".select_report:visible").attr("value");
					var selected_date = $("#single_date_filter").attr("value");
					var report_url = base_url + "report_management/" + report + "/" + selected_date;
					window.location = report_url;
				} else if(id == "generate_single_year_report") {
					var report = $(".select_report:visible").attr("value");
					var selected_year = $("#single_year_filter").attr("value");
					var report_url = base_url + "report_management/" + report + "/" + selected_year;
					window.location = report_url;
				} else if(id == "generate_no_filter_report") {
					var report = $(".select_report:visible").attr("value");
					var stock_type = "";
					if($("#commodity_summary_report_type_1")) {
						stock_type = $("#commodity_summary_report_type_1").attr("value");
					}
					var report_url = base_url + "report_management/" + report + "/" + stock_type;
					window.location = report_url;
				} else if(id == "donor_generate_date_range_report") {
					var report = $(".select_report:visible").attr("value");
					var from = $("#donor_date_range_from").attr("value");
					var to = $("#donor_date_range_to").attr("value");
					var donor = $("#donor").attr("value");
					var report_url = base_url + "report_management/" + report + "/" + from + "/" + to + "/" + donor;
					window.location = report_url;
				}
			}
		}

	});
	/*
	* Reports generation end
	*/

	//Add datepicker
	$("#date_range_from").datepicker({
		changeMonth : true,
		changeYear : true,
		dateFormat : 'dd-M-yy',
		onSelect : function(selected) {
			$("#date_range_to").datepicker("option", "minDate", selected);
		}
	});
	$("#single_date_filter").datepicker({
		changeMonth : true,
		changeYear : true,
		dateFormat : 'dd-M-yy'
	});
	$("#date_range_to").datepicker({
		changeMonth : true,
		changeYear : true,
		dateFormat : 'dd-M-yy',
		onSelect : function(selected) {
			$("#date_range_from").datepicker("option", "maxDate", selected);
		}
	});

	$("#donor_date_range_from").datepicker({
		changeMonth : true,
		changeYear : true,
		dateFormat : 'dd-M-yy',
		onSelect : function(selected) {
			$("#donor_date_range_to").datepicker("option", "minDate", selected);
		}
	});
	$("#donor_date_range_to").datepicker({
		changeMonth : true,
		changeYear : true,
		dateFormat : 'dd-M-yy',
		onSelect : function(selected) {
			$("#donor_date_range_from").datepicker("option", "maxDate", selected);
		}
	});
	$("#single_year_filter").datepicker({
		changeMonth : false,
		changeYear : true,
		dateFormat : 'yy',
		showButtonPanel : true,
		onClose : function(dateText, inst) {
			var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
			$(this).datepicker('setDate', new Date(year, 1));
		}
	});
	$("#single_year_filter").focus(function() {
		$(".ui-datepicker-month").hide();
		$(".ui-datepicker-calendar").hide();
	});

	$(".reports_types").css("display", "none");
	$("#standard_report_row").css("display", "block");

	//Reports types
	$(".reports_tabs").click(function() {
		$(".select_types").css("display", "none");
		//Reset all texts and selects
		$("select").val("0");
		$("input:text").val("");

		//Standard report selected
		if($(this).attr("id") == 'standard_report') {

			$(".active").removeClass();
			$(this).addClass("active");
			$(".reports_types").css("display", "none");
			$("#standard_report_row").css("display", "block");
		}
		//Visiting report tab selected
		else if($(this).attr("id") == 'visiting_patient') {
			$(".active").removeClass();
			$(this).addClass("active");
			$(".reports_types").css("display", "none");
			$("#visiting_patient_report_row").css("display", "block");
		} else if($(this).attr("id") == 'early_warning_indicators') {
			$(".active").removeClass();
			$(this).addClass("active");
			$(".reports_types").css("display", "none");
			$("#early_warning_report_row").css("display", "block");
		} else if($(this).attr("id") == 'drug_inventory') {
			$(".active").removeClass();
			$(this).addClass("active");
			$(".reports_types").css("display", "none");
			$("#drug_inventory_report_row").css("display", "block");
		}
	});
	//Features to select
	$(".select_report").change(function() {
		var get_type = $("option:selected", this).attr("class");
		var get_id = $("option:selected", this).attr("id");

		if(get_type == "none") {
			$(".select_types").css("display", "none");
			return;
		}
		if(get_type == "donor_date_range_report") {
			$(".select_types").css("display", "none");
			$("#donor_date_range_report").css("display", "block");
		} else if(get_type == "annual_report") {
			$(".select_types").css("display", "none");
			$("#year").css("display", "block");
		} else if(get_type == "single_date_report") {
			$(".select_types").css("display", "none");
			$("#single_date").css("display", "block");
		} else if(get_type == "date_range_report") {
			$(".select_types").css("display", "none");
			$("#date_range_report").css("display", "block");
			//If report is drug_consumption report, display select report type
			if(get_id == 'drug_stock_on_hand' || get_id == 'expiring_drugs' || get_id == 'expired_drugs' || get_id == 'getDrugsIssued' || get_id == 'getDrugsReceived' || get_id == 'commodity_summary') {
				$(".show_report_type").show();
			} else {
				$(".show_report_type").hide();
			}
		} else if(get_type == "month_range_report") {
			$(".select_types").css("display", "none");
			$("#month_range_report").css("display", "block");
			//If report is drug_consumption report, display select report type
			if(get_id == 'drug_stock_on_hand' || get_id == 'expiring_drugs' || get_id == 'expired_drugs' || get_id == 'getDrugsIssued' || get_id == 'getDrugsReceived' || get_id == 'commodity_summary') {
				$(".show_report_type").show();
			} else {
				$(".show_report_type").hide();
			}
		} else if(get_type == "no_filter") {
			$(".select_types").css("display", "none");
			$("#no_filter").css("display", "block");
			$("#selected_report").attr("value", $(this).attr("id"));
			//If report is drug_consumption report, display select report type
			if(get_id == 'drug_stock_on_hand' || get_id == 'expiring_drugs' || get_id == 'expired_drugs') {
				$(".show_report_type").show();
			} else {
				$(".show_report_type").hide();
			}
		}
	});
	
	
});
/*
 *Reports JS End
 */