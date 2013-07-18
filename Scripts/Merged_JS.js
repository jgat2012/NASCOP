//Load datatables settings

 
/**
 * End of datatables settings
 */

	
	/**
	 *Change password validation 
	 */
	$(document).ready(function() {
		var base_url=$("#base_url").val();
		   $('.dataTables').dataTable( {
		   		"bJQueryUI": true,
	        	"sPaginationType": "full_numbers",
		        "sDom": '<"H"Tfr>t<"F"ip>',
		   		"oTableTools": {
					"sSwfPath": base_url+"scripts/datatable/copy_csv_xls_pdf.swf",
					"aButtons": [ "copy", "print","xls","pdf" ]
				},
		   		"bProcessing": true,
				"bServerSide": false,
			});
		/*
		 * Reports generation
		 */
		
		$(".generate_btn").live('click', function() {
			
			var base_url=$("#base_url").val();
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
				}
				else if($("#commodity_summary_report_type_1").is(":visible") && $("#commodity_summary_report_type_1").val() == 0) {
						alert("Please select the report type");
				}
				//If everything is ok,generatea report
				else {

					var id = $(this).attr("id");
					if(id == "generate_date_range_report") {
						
						var report = $(".select_report:visible").attr("value");
						var from = $("#date_range_from").attr("value");
						var to = $("#date_range_to").attr("value");
						if($(".report_type").is(":visible")){
							report=report+"/"+$(".report_type:visible").attr("value");
						}
						
						var report_url =base_url+ "report_management/" + report+"/" + from + "/" + to;
						window.location = report_url;
					} else if(id == "generate_single_date_report") {
						var report = $(".select_report:visible").attr("value");
						var selected_date = $("#single_date_filter").attr("value");
						var report_url = base_url+"report_management/" + report + "/" + selected_date;
						window.location = report_url;
					} else if(id == "generate_single_year_report") {
						var report = $(".select_report:visible").attr("value");
						var selected_year = $("#single_year_filter").attr("value");
						var report_url = base_url+"report_management/" + report + "/" + selected_year;
						window.location = report_url;
					} else if(id == "generate_no_filter_report") {
						var report = $(".select_report:visible").attr("value");
						var stock_type = "";
						if($("#commodity_summary_report_type_1")) {
							stock_type = $("#commodity_summary_report_type_1").attr("value");
						}
						var report_url = base_url+"report_management/" + report + "/" + stock_type;
						window.location = report_url;
					} else if(id == "donor_generate_date_range_report") {
						var report = $(".select_report:visible").attr("value");
						var from = $("#donor_date_range_from").attr("value");
						var to = $("#donor_date_range_to").attr("value");
						var donor = $("#donor").attr("value");
						var report_url =base_url+ "report_management/" + report + "/" + from + "/" + to + "/" + donor;
						window.location = report_url;
					}
				}
			}

		})
		/*
		 * Reports generation end
		 */
			var base_url=$("#base_url").val();
			$("#change_password_link").click(function(){
				$("#old_password").attr("value","");
				$("#new_password").attr("value","");
				$("#new_password_confirm").attr("value","");
				$(".error").html("");
				$(".error").css("display","none");
				$("#result").html("");
			});
			
			$(".error").css("display","none");
			$('#new_password').keyup(function() {
				$('#result').html(checkStrength($('#new_password').val()))
			})
			function checkStrength(password) {
	
				//initial strength
				var strength = 0
	
				//if the password length is less than 6, return message.
				if (password.length < 6) {
					$('#result').removeClass()
					$('#result').addClass('short')
					return 'Too short'
				}
	
				//length is ok, lets continue.
	
				//if length is 8 characters or more, increase strength value
				if (password.length > 7)
					strength += 1
	
				//if password contains both lower and uppercase characters, increase strength value
				if (password.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/))
					strength += 1
	
				//if it has numbers and characters, increase strength value
				if (password.match(/([a-zA-Z])/) && password.match(/([0-9])/))
					strength += 1
	
				//if it has one special character, increase strength value
				if (password.match(/([!,%,&,@,#,$,^,*,?,_,~])/))
					strength += 1
	
				//if it has two special characters, increase strength value
				if (password.match(/(.*[!,%,&,@,#,$,^,*,?,_,~].*[!,",%,&,@,#,$,^,*,?,_,~])/))
					strength += 1
	
				//now we have calculated strength value, we can return messages
	
				//if value is less than 2
				if (strength < 2) {
					$('#result').removeClass()
					$('#result').addClass('weak')
					return 'Weak'
				} else if (strength == 2) {
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
				var base_url=$("#base_url").val();
				$(".error").css("display","none");
				$('#result_confirm').html("");
				event.preventDefault();
				var old_password = $("#old_password").attr("value");
				var new_password = $("#new_password").attr("value");
				var new_password_confirm = $("#new_password_confirm").attr("value");
				
				if (new_password == "" || new_password_confirm=="" || old_password=="") {
					$(".error").css("display","block");
					$("#error_msg_change_pass").html("All fields are required !");
				} 
				else if($('#new_password').val().length < 6){
					$(".error").css("display","block");
					$("#error_msg_change_pass").html("Your password must have more than 6 characters!");
				}
				else if ($("#result").attr("class") == "weak") {
					$(".error").css("display","block");
					$("#error_msg_change_pass").html("Please enter a strong password!");
				} else if (new_password != new_password_confirm) {
					$(".error").css("display","block");
					$('#result_confirm').removeClass();
					$('#result_confirm').addClass('short');
					$("#error_msg_change_pass").html("You passwords do not match !");
				} else {
					$(".error").css("display","none");
					//$("#fmChangePassword").submit();
					var _url=base_url+"user_management/save_new_password";
					var request=$.ajax({
					     url: _url,
					     type: 'post',
					     data: {"old_password":old_password,"new_password":new_password},
					     dataType: "json"
				    });
				     request.done(function(data){
				     	$.each(data,function(key,value){
				     		if(value=="password_no_exist"){
				     			$("#error_msg_change_pass").css("display","block");
				     			$("#error_msg_change_pass").html("You entered a wrong password!");
				     		}
				    		else if(value=="password_exist"){
				    			$("#error_msg_change_pass").css("display","block");
				     			$("#error_msg_change_pass").html("Your new password matches one of your three pevious passwords!");
				    		}
				    		else if(value=="password_changed"){
				    			$("#error_msg_change_pass").css("display","block");
				    			$("#error_msg_change_pass").removeClass("error");
				    			$("#error_msg_change_pass").addClass("success");
				    			$("#error_msg_change_pass").html("Your password was successfully updated!");
				    			window.setTimeout('location.reload()', 3000);
				    		}
				    		else{
				    			alert(value);
				    		}
				   		});
				     });
				     request.fail(function(jqXHR, textStatus) {
					  alert( "An error occured while updating your password : " + textStatus+". Please try again or contact your system administrator!" );
					});
				}
			});
	
		});
		
		/**
	 * End Change password validation 
	 */

	
  	
		     