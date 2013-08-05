//Load datatables settings

/**
 * End of datatables settings
 */

/**
 *Change password validation
 */
$(document).ready(function() {
	  
	
	
	
	var base_url = $("#base_url").val();
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
	/*
	 * Reports generation
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

	})
	/*
	 * Reports generation end
	 */
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
		$('#result').html(checkStrength($('#new_password').val()))
	})
	function checkStrength(password) {

		//initial strength
		var strength = 0

		//if the password length is less than 6, return message.
		if(password.length < 6) {
			$('#result').removeClass()
			$('#result').addClass('short')
			return 'Too short'
		}

		//length is ok, lets continue.

		//if length is 8 characters or more, increase strength value
		if(password.length > 7)
			strength += 1
		if(password.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/))
			strength += 1
		if(password.match(/([a-zA-Z])/) && password.match(/([0-9])/))
			strength += 1
		if(password.match(/([!,%,&,@,#,$,^,*,?,_,~])/))
			strength += 1
		if(password.match(/(.*[!,%,&,@,#,$,^,*,?,_,~].*[!,",%,&,@,#,$,^,*,?,_,~])/))
			strength += 1
		if(strength < 2) {
			$('#result').removeClass()
			$('#result').addClass('weak')
			return 'Weak'
		} else if(strength == 2) {
			$('#result').removeClass()
			$('#result').addClass('good')
			return 'Good'
		} else {
			$('#result').removeClass()
			$('#result').addClass('strong')
			return 'Strong'
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
});
/**
 * End Change password validation
 */

/*
 * Auto logout
 */
var timer = 0;
function set_interval() {
	// the interval 'timer' is set as soon as the page loads
	timer = setInterval("auto_logout()", 600000);
	// the figure '180000' above indicates how many milliseconds the timer be set to.
	// Eg: to set it to 5 mins, calculate 3min = 3x60 = 180 sec = 180,000 millisec.
	// So set it to 180000
}

function reset_interval() {
	//resets the timer. The timer is reset on each of the below events:
	// 1. mousemove   2. mouseclick   3. key press 4. scroliing
	//first step: clear the existing timer

	if(timer != 0) {
		clearInterval(timer);
		timer = 0;
		// second step: implement the timer again
		timer = setInterval("auto_logout()", 10000);
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

/*
 * Sysnchronization of Orders
 */
function syncOrders(facility,session_id) {
	var href = window.location.href;
	var base_url = href.substr(href.lastIndexOf('http://'), href.lastIndexOf('/ADT'));
	var _href = href.substr(href.lastIndexOf('/') + 1);
	var link = "http://localhost/NASCOP/synchronization_management/getSQL/" + facility;
	$.ajax({
		url : link,
		type : 'POST',
		success : function(data) {
			link = base_url + "/ADT/synchronization_management/uploadSQL/"+session_id;
			$.ajax({
				url : link,
				type : 'POST',
				data : {
					"sql" : data
				},
				success : function(data) {
					link = base_url + "/ADT/synchronization_management/synchronize_orders";
					$.ajax({
						url : link,
						type : 'POST',
						success : function(data) {
							
							link = "http://localhost/NASCOP/synchronization_management/getSQL/" + facility;
							$.ajax({
								url : link,
								type : 'POST',
								data : {
									"sql" : data
								},
								success : function(data) {
									link = base_url + "/ADT/synchronization_management/uploadSQL/"+session_id;
									$.ajax({
										url : link,
										type : 'POST',
										data : {
											"sql" : data
										},
										success : function(data) {
											alert("Successful Order Synchronization");
										}
									});
								}
							});
						}
					});

				}
			});

		}
	});

}