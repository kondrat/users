jQuery(document).ready( function(){
	
    var $reg_username = $("#UserUsername");
    var $reg_email = $("#UserEmail");
    var $reg_token = $("input[id^='Token']");
    var $reg_validEmail = '';
    var $reg_usercaptcha = $("#UserCaptcha");
    var $reg_userpass = {
            pass1: $("#UserPassword1"),
            pass2: $("#UserPassword2")
        };




    //getting new captcha
    $('.capReset span, #capImg').click( function() {
        var Stamp = new Date();
        $('#capImg').attr( {
            src: path+"/users/users/kcaptcha/"+Stamp.getTime()
        } );
    });


	
    $("#UserRegForm input").blur(function(){
        if( $(this).val().length === 0 ) {
            $(this).parents(".inputFormWrap").find(".formWrapTip div").hide();
        }
    });		
    $("#UserRegForm input").focus(function(){	
        if( $(this).val().length === 0 ) {
            $(this).parents(".inputFormWrap").find(".formWrapTip div").hide();
            $(this).parents(".inputFormWrap").find(".formWrapTip div:first").show();
        }	
    });
	

	
    var inpStrTimer;
    $reg_username.keyup( function(e) {
		
        //alert(e.which);
        /*
	  var chr = (String.fromCharCode(e.which));
	  rexp = /([^a-zA-Z0-9])/; 
	  if( rexp.test(chr) && e.which !== 8 ) {
	    return false;
	  } 
	  */
		
        var InputStr = $(this).val();
		
        $("#rName div").hide();	
        $("#rNameCheck").show();
				
        window.clearInterval(inpStrTimer);
        inpStrTimer = window.setInterval( function() {
			
            if( InputStr.length > 0 ){
                $.ajax({
                    type: "POST",
                    url: path+"/users/users/userNameCheck/",
                    data: {
                        "data[User][username]": InputStr, 
                        "data[_Token][key]": $reg_token.val()
                    },
                    dataType: "json",
									
                    success: function (data) {
                        if (data.stat == 1) {
										  	
                            $('#rName div').hide();
                            $("#rNameOk").show();
                        } else {
                            $('#rName div').hide();
                            $("#rNameError").show();
                            $.each(rErr.username , function(key,value){
                                if( key === data.error ) {
                                    var ret = value;
                                    if ( data.stW ) {
                                        ret = value+' "'+data.stW+'"';
                                    }
                                    $("#rNameError").text(ret);
                                }
                            });
										  	
                        }
                    },
                    error: function(response, status) {
                        alert('An unexpected error has occurred! ');
                    //$('.tempTest').html('Problem with the server. Try again later.');
                    }

									
                });
					
            } else {
                $("#rNameCheck").hide();
                $("#rNameTip").show();
            }
					
            window.clearInterval(inpStrTimer);
        }, 1000
        );
		


    });





    $('#UserPassword1').passStrengthCheck(
        "#rPass1Check",															        		
        {
            username: function(){
                return $reg_username.val();
            },
            minlength: 4,
            maxlength: 16
        }																				
        ).passIdentCheck(1,$reg_userpass);






    $('#UserPassword2').passIdentCheck(2,$reg_userpass);
																        	





    $reg_email.blur( function() {
		
        var InputStr = $(this).val();
		
        //var emailRegEx = /^[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+)*@(?:[a-z0-9][-a-z0-9]*\.)*(?:[a-z0-9][-a-z0-9]{0,62})\.(?:(?:[a-z]{2}\.)?[a-z]{2,4}|museum|travel)$/i;
        var emailRegEx =/.+@.+\..+/; 
			
        if( InputStr === '' ) {
            $("#rEmail div").hide();

        } else if( !InputStr.match(emailRegEx) ) {				
            $("#rEmail div").hide();				
            $("#rEmailError").show().text(rErr.email.email);
        } else {
            if( InputStr !== $reg_validEmail ){
                $.ajax({
                    type: "POST",
                    url: path+"/users/users/userNameCheck/",
                    data: {
                        "data[User][email]": InputStr, 
                        "data[_Token][key]": $reg_token.val()
                    },
                    dataType: "json",									
                    success: function (data) {
                        if (data.stat == 1) {
                            // Success!
                            $reg_validEmail = InputStr;
                            $('#rEmail div').hide();
                            $("#rEmailOk").show();
                        } else {
                            $('#rEmail div').hide();
                            $("#rEmailError").show();
                            $.each(rErr.email , function(key,value){
                                if( key === data.error ) {
                                    $("#rEmailError").text(value);
                                }
                            });
										  	
                        }
                    },
                    error: function(response, status) {
                        alert('An unexpected error has occurred! ');
                    //$('.tempTest').html('Problem with the server. Try again later.');
                    }

									
                });	
            }			
        }
    }
    );




	

    $("form").submit(function() {

        if ( $reg_usercaptcha.val() === '' || $reg_usercaptcha.val().length < 5 ) {
            $("#rCap div").hide();
            $("#rCapError").show();
					
            return false;
        } else {
            $("#rCap div").hide();
            return true;
        }
    });

    //???????????????????
    $("img").error(function(){
        $(this).hide();
    });

									
}
);