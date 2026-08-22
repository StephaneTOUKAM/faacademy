var status = 0;
jQuery(document).on("click", ".saveInsurance", function(e){
    e.preventDefault();
    jQuery('.saveInsurance').html('Loading...');
    if (jQuery('.password').val() == jQuery('.cpassword').val()) {
        jQuery.ajax(jQuery('.saveInsurance').attr('attr-url'), {
            type: 'POST',  // http method
            data: { 
                firstname: jQuery('.form-wizardino .firstname').val(),
                lastname: jQuery('.form-wizardino .lastname').val(),
                dob: jQuery('.form-wizardino .dob').val(),
                idnumber: jQuery('.form-wizardino .idnumber').val(),
                email: jQuery('.form-wizardino .email').val(),
                city: jQuery('.form-wizardino .city').val(),
                zipcode: jQuery('.form-wizardino .zipcode').val(),
                pays: jQuery('.form-wizardino .pays').children("option:selected").text(),
                cellphone: jQuery('.form-wizardino .cellphone').val(),
                homephone: jQuery('.form-wizardino .homephone').val(),
                mailaddress: jQuery('.form-wizardino .mailaddress').val(),
                corigin: jQuery('.form-wizardino .corigin').children("option:selected").text(),
                assocname: jQuery('.form-wizardino .assocname').val(),
                regnumber: jQuery('.form-wizardino .regnumber').val(),
                dateformed: jQuery('.form-wizardino .dateformed').val(),
                physicaddress: jQuery('.form-wizardino .physicaddress').val(),
                cityentreprise: jQuery('.form-wizardino .cityentreprise').val(),
                zipcodeentreprise: jQuery('.form-wizardino .zipcodeentreprise').val(),
                numberaffiliate: jQuery('.form-wizardino .numberaffiliate').children("option:selected").text(),
                paysentreprise: jQuery('.form-wizardino .paysentreprise').children("option:selected").text(),
                password: jQuery('.form-wizardino .password').val(),
                cpassword: jQuery('.form-wizardino .cpassword').val(),
                firstnameperson: jQuery('.form-wizardino .firstnameperson').val(),
                lastnameperson: jQuery('.form-wizardino .lastnameperson').val(),
                emailperson: jQuery('.form-wizardino .emailperson').val(),
                mailperson: jQuery('.form-wizardino .mailperson').val(),
                cityperson: jQuery('.form-wizardino .cityperson').val(),
                zipcodeperson: jQuery('.form-wizardino .zipcodeperson').val(),
                cellphoneperson: jQuery('.form-wizardino .cellphoneperson').val(),
                status: jQuery('.saveInsurance').attr('attr-status'),
                paye: jQuery('.form-wizardino .cardnumber').val() ? 'Yes': 'No',
            },  // data to submit
            success: function (data, status, xhr) {
                data = JSON.parse(data);
                if (data.type == "success") {
                    if (parseInt(jQuery('.saveInsurance').attr('attr-status')) == 1) {
                      toastr.success('Welcome Mr/Ms '+jQuery('.form-wizardino .firstname').val(), 'Bravo')
                    } else {
                      toastr.success('Welcome '+jQuery('.form-wizardino .assocname').val(), 'Bravo')
                    }
                    setTimeout(function(){window.location.href = "https://venantcorp.org/wp-login.php";}, 2000);
                }else{
                    toastr.error('An error occured, please try again later.', 'Error !')
                }
    		        jQuery('.saveInsurance').html('Submit');
            },
            error: function (jqXhr, textStatus, errorMessage) {
                console.log(errorMessage);
                toastr.error('An error occured, please try again later.', 'Error !')
            }
        });
    } else {
        toastr.error('Your passwords are not matching', 'Error !');
    	jQuery('.saveInsurance').html('Submit');
    }
})

jQuery(document).on("click", ".insurtext", function(e){
    e.preventDefault();
    jQuery('.particularlyre-text').slideToggle( "slow" );
})

// jQuery(document).on("click", ".particulier", function(e){
//     jQuery(this).addClass('active');
//     jQuery('.associer').removeClass('active');
//     jQuery('.particularlyre').show();
//     jQuery('.form-wizardino').show(400);
//     jQuery('.associationyre').hide();
//     status = 1;
// })

// jQuery(document).on("click", ".associer", function(e){
//     jQuery(this).addClass('active');
//     jQuery('.particulier').removeClass('active');
//     jQuery('.associationyre').show();
//     jQuery('.form-wizardino').show(400);
//     jQuery('.particularlyre').hide();
//     status = 2;
// })

"use strict";
function scroll_to_class(element_class, removed_height) {
	var scroll_to = jQuery(element_class).offset().top - removed_height;
	if(jQuery(window).scrollTop() != scroll_to) {
		jQuery('.form-wizard').stop().animate({scrollTop: scroll_to}, 0);
	}
}

function bar_progress(progress_line_object, direction) {
	var number_of_steps = progress_line_object.data('number-of-steps');
	var now_value = progress_line_object.data('now-value');
	var new_value = 0;
	if(direction == 'right') {
		new_value = now_value + ( 100 / number_of_steps );
	}
	else if(direction == 'left') {
		new_value = now_value - ( 100 / number_of_steps );
	}
	progress_line_object.attr('style', 'width: ' + new_value + '%;').data('now-value', new_value);
}

jQuery(document).ready(function() {
    
    /*
        Form
    */
    jQuery('.form-wizard fieldset:first').fadeIn('slow');
    
    jQuery('.form-wizard .required').on('focus', function() {
    	jQuery(this).removeClass('input-error');
    });
    
    // next step
    jQuery('.form-wizard .btn-next').on('click', function() {
    	var parent_fieldset = jQuery(this).parents('fieldset');
    	var next_step = true;
    	// navigation steps / progress steps
    	var current_active_step = jQuery(this).parents('.form-wizard').find('.form-wizard-step.active');
    	var progress_line = jQuery(this).parents('.form-wizard').find('.form-wizard-progress-line');
    	
    	// fields validation
    	parent_fieldset.find('.required').each(function() {
    		if( jQuery(this).val() == "" ) {
    			jQuery(this).addClass('input-error');
    			next_step = false;
    		}
    		else {
    			jQuery(this).removeClass('input-error');
    		}
    	});
    	// fields validation
    	
    	if( next_step ) {
    		parent_fieldset.fadeOut(400, function() {
    			// change icons
    			current_active_step.removeClass('active').addClass('activated').next().addClass('active');
    			// progress bar
    			bar_progress(progress_line, 'right');
    			// show next step
	    		jQuery(this).next().fadeIn();
	    		// scroll window to beginning of the form
    			scroll_to_class( jQuery('.form-wizard'), 20 );
	    	});
    	}
    	
    });
    
    // previous step
    jQuery('.form-wizard .btn-previous').on('click', function() {
    	// navigation steps / progress steps
    	var current_active_step = jQuery(this).parents('.form-wizard').find('.form-wizard-step.active');
    	var progress_line = jQuery(this).parents('.form-wizard').find('.form-wizard-progress-line');
    	
    	jQuery(this).parents('fieldset').fadeOut(400, function() {
    		// change icons
    		current_active_step.removeClass('active').prev().removeClass('activated').addClass('active');
    		// progress bar
    		bar_progress(progress_line, 'left');
    		// show previous step
    		jQuery(this).prev().fadeIn();
    		// scroll window to beginning of the form
			scroll_to_class( jQuery('.form-wizard'), 20 );
    	});
    });
    
    // submit
    jQuery('.form-wizard').on('submit', function(e) {
    	
    	// fields validation
    	jQuery(this).find('.required').each(function() {
    		if( jQuery(this).val() == "" ) {
    			e.preventDefault();
    			jQuery(this).addClass('input-error');
    		}
    		else {
    			jQuery(this).removeClass('input-error');
    		}
    	});
    	// fields validation
    	
    });
    
    
});





// image uploader scripts 

var jQuerydropzone = jQuery('.image_picker'),
    jQuerydroptarget = jQuery('.drop_target'),
    jQuerydropinput = jQuery('#inputFile'),
    jQuerydropimg = jQuery('.image_preview'),
    jQueryremover = jQuery('[data-action="remove_current_image"]');

jQuerydropzone.on('dragover', function() {
  jQuerydroptarget.addClass('dropping');
  return false;
});

jQuerydropzone.on('dragend dragleave', function() {
  jQuerydroptarget.removeClass('dropping');
  return false;
});

jQuerydropzone.on('drop', function(e) {
  jQuerydroptarget.removeClass('dropping');
  jQuerydroptarget.addClass('dropped');
  jQueryremover.removeClass('disabled');
  e.preventDefault();
  
  var file = e.originalEvent.dataTransfer.files[0],
      reader = new FileReader();

  reader.onload = function(event) {
    jQuerydropimg.css('background-image', 'url(' + event.target.result + ')');
  };
  
  console.log(file);
  reader.readAsDataURL(file);

  return false;
});

jQuerydropinput.change(function(e) {
  jQuerydroptarget.addClass('dropped');
  jQueryremover.removeClass('disabled');
  jQuery('.image_title input').val('');
  
  var file = jQuerydropinput.get(0).files[0],
      reader = new FileReader();
  
  reader.onload = function(event) {
    jQuerydropimg.css('background-image', 'url(' + event.target.result + ')');
  }
  
  reader.readAsDataURL(file);
});

jQueryremover.on('click', function() {
  jQuerydropimg.css('background-image', '');
  jQuerydroptarget.removeClass('dropped');
  jQueryremover.addClass('disabled');
  jQuery('.image_title input').val('');
});

jQuery('.image_title input').blur(function() {
  if (jQuery(this).val() != '') {
    jQuerydroptarget.removeClass('dropped');
  }
});

// image uploader scripts