
﻿/* Swedish initialisation for the jQuery UI date picker plugin. */
/* Written by Anders Ekdahl ( anders@nomadiz.se). */
jQuery(function($){
    $.datepicker.regional['sv'] = {
                closeText: 'Stäng',
        prevText: '&laquo;Förra',
                nextText: 'Nästa&raquo;',
                currentText: 'Idag',
        monthNames: ['Januari','Februari','Mars','April','Maj','Juni',
        'Juli','Augusti','September','Oktober','November','December'],
        monthNamesShort: ['Jan','Feb','Mar','Apr','Maj','Jun',
        'Jul','Aug','Sep','Okt','Nov','Dec'],
                dayNamesShort: ['Sön','Mån','Tis','Ons','Tor','Fre','Lör'],
                dayNames: ['Söndag','Måndag','Tisdag','Onsdag','Torsdag','Fredag','Lördag'],
                dayNamesMin: ['Sö','Må','Ti','On','To','Fr','Lö'],
                weekHeader: 'Ve',
        dateFormat: 'yy-mm-dd',
                firstDay: 1,
                isRTL: false,
                showMonthAfterYear: true,
                yearSuffix: ''};
    $.datepicker.setDefaults($.datepicker.regional['sv']);
});

$(function() {
	$( "#dateinputex1" ).datepicker({ maxDate: "0", 
										changeMonth: true,
										changeYear: true });
	});
$(function() {
	$( "#dateinputex2" ).datepicker({ maxDate: "0", 
										changeMonth: true,
										changeYear: true });
	});


$(document).ready(function(){
	$('a#close').click(function(){
	  $('#stockChooser').hide('fast');
	})   

	var closed=true;
	$('input#stockActivate').click(function(){
	  if(closed) {
		closed=false;
		$('#stockChooser').show();
		$('#backgroundBlank').show();
	  } else { 
		closed=true;
		$('#stockChooser').hide();
		$('#backgroundBlank').hide();
	  }
	});
});

function recalculateSum()
{
	var num1 = document.getElementById("antal").value;
	var num2 = document.getElementById("value").value;
	var num3 = document.getElementById("courtage").value;
	num1 = num1.replace(",", ".");
	num2 = num2.replace(",", ".");
	num3 = num3.replace(",", ".");
	document.getElementById("sum").value = parseFloat(num1) * parseFloat(num2) + parseFloat(num3);

	if (isNaN(num1) || num1 == 0) {
		$('#antal').css({'background-color' : '#F99'});
		num_error1 = true;
	} else {
		$('#antal').css({'background-color' : '#FFF'});
		num_error1 = false;
	}
	if (isNaN(num2)) {
		$('#value').css({'background-color' : '#F99'});
		num_error2 = true;
	} else {
		$('#value').css({'background-color' : '#FFF'});
		num_error2 = false;
	}
	if (isNaN(num3)) {
		$('#courtage').css({'background-color' : '#F99'});
		num_error3 = true;
	} else {
		$('#courtage').css({'background-color' : '#FFF'});
		num_error3 = false;
	}
}
	
function ownAmount() {
	$('#form_info').html('<img src="img/load1.gif" alt="LOADING" />');
	form_data2 = $("#form_process").serialize();
	$.ajax({
	  type: "POST",
	  url: "ajax.php?ownAmount=",
	  data: form_data2,
	  success: function(response) {
			$('#form_info').html(response);
		}
 });
}

function iniBut() {
  $(document).on("click", ".button", function(){
		recalculateSum();
		if(num_error1 || num_error2 || num_error3){
			$('#form_error').html('<hr><div style="background-color:#F99; border-radius: 4px; padding:5px;">Felaktig inmatning<br> Försök igen om du kan</div>');
		} else {
		form_data = $("#form_process").serialize();
		$('#m_content').html('Detta kommer ta tid...<br><br><img src="img/load1.gif" alt="LOADING" />');
		$.ajax({
		  type: "POST",
		  url: "ajax.php",
		  data: form_data,
		  success: function(response) {
			$('#m_content').html("<div id='message'></div>");
			$('#message').html(response)
			.hide()
			.fadeIn(300, function() {
			  $('#message').append(" ");
			});
			}
     });
	 }
    return false;
	});
	
	$(document).on("click", "#removeActivity", function(){
		$('#form_info').html('<img src="img/load1.gif" alt="LOADING" />');
		$.ajax({
		  type: "POST",
		  data: 'removeActivity=',
		  url: "ajax.php",
		  success: function(response) {
				$('#form_info').html(response);
			}
     });
	});
	
	$(document).on("click", ".button2", function(){
		form_data = $("#form_process").serialize();

		$('#m_content').html('Detta kommer ta tid...<br><br><img src="img/load1.gif" alt="LOADING" />');
		$.ajax({
		  type: "POST",
		  url: "ajax.php",
		  data: form_data,
		  success: function(response) {
			$('#m_content').html("<div id='message'></div>");
			$('#message').html(response)
			.hide()
			.fadeIn(300, function() {
			  $('#message').append(" ");
			});
			}
     });
	    return false;
	});
	
	$(document).on("click", "#reload", function(){
		location.reload();
	});	
};

      function initiPop(){
        var closed=true;
		$('.popup').click(function(){
			var target = $(this).attr("id");
			  if(closed) {
			  	$('#m_content').html('Laddar<br><br><img src="img/load1.gif" alt="LOADING" />');
				closed=false;
				data_transid = $(this).data("transid");
				data_stockid = $(this).data("stockid");
				$.ajax({
				  type: "POST",
				  data: 'stockID='+data_stockid+'&div='+target+'&transid='+data_transid,
				  url: "ajax.php",
				  success: function(response) {
							$('#m_content').html(response);
							$( "#date" ).datepicker({ maxDate: "0", 
														changeMonth: true,
														changeYear: true });
						}
				 });
				 
				$('#backgroundBlank').fadeIn(200);
				$('#stockActivityAddW').fadeIn(200);
			  } else { 
				closed=true;
				$('#stockActivityAddW').fadeOut(200);
				$('#backgroundBlank').fadeOut(200);
			  }
        });
 	  };
	
