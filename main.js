/* JS for login/index.php */
//submit login form
$('#login_form').submit(function(e)
{
    e.preventDefault();
    l = $("#l").val();
    if(l==1)
    {
        if( $('#email_').val().length >0 && $('#password').val().length >0 )
        {
            $('#submit_login_btn').html('<img src="../theme/img/loader3.gif" height="20" width="20" />');
            $('#submit_login_btn').prop('disabled', true);

            $.ajax(
            { 
                type: 'POST',
                url: '../login/ajax.php',            
                dataType: 'JSON',
                data: $('#login_form').serialize(),
                success: function(data)
                {
                    switch(data.response)
                    {
                        case 0:                         
                            //display alert message
                            $('.message_').html("<span class='error_message'><strong>Error!</strong> User doesn't exist.</span>");       
                            $('.message_').css('display', 'inline');     
                            $('.message_').delay(3000).fadeOut(1500);   
                            $('#submit_login_btn').prop('disabled', false);  
                            $('#submit_login_btn').html('Logga In');
                            break;

                        case 1:
                            //display alert message
                            $('.message_').html("<span class='error_message'><strong>Error!</strong> Password incorrect.</span>");
                            $('.message_').css('display', 'inline');     
                            $('.message_').delay(3000).fadeOut(1500);   
                            $('#submit_login_btn').prop('disabled', false);  
                            $('#submit_login_btn').html('Logga In');
                            break;

                        case 2:
                            if(data.r == 1)
                                window.open("../adminPanel", "_self");
                            else
                                window.open("verifyOTPadmin.php?a="+data.a, "_self");                        
                            break;

                        case 3:
                            if(data.r == 1)
                                window.open("../dashboard", "_self");
                            else
                                window.open("verifyOTP.php?c="+data.c, "_self");
                            break;

                        default:
                            break;
                    }                  
                }
            });        
        }   
    }   
});


//verify OTP form
$(document).on("submit", "#verifyOTP", function(e)
{
    e.preventDefault();

    $('#submit_login_btn').html('<img src="../theme/img/loader3.gif" height="20" width="20" />');
    $('#submit_login_btn').prop('disabled', true);

    email = $("#email").val();
    otp_number = $("#otp_number").val();

    $.ajax(
    {
        type: 'POST',
        url: 'ajax.php',
        data: {act:1, email:email, otp_number:otp_number},
        success: function (data) 
        {            
            dataSplit = data.split("/");
            if(dataSplit[0] == 1)
            {
                if(dataSplit[1] == 0)
                    window.open('../dashboard', '_self');
                else
                    window.open('../adminPanel', '_self');
            }
            else
                alert("Wrong OTP entered. Please try again!");

            $('#submit_login_btn').html('Logga In');
            $('#submit_login_btn').prop('disabled', false);
        }
    });
});



/* JS for onboard/index.php */ 
$('#submit_onboard_btn').hover(function()
{
    $(this).css('color', 'white');
});



//submit onboard request form
$('#onboard_form').submit(function(e)
{
    e.preventDefault();
    $('#submit_onboard_btn').html('<img src="../theme/img/loader3.gif" height="20" width="20" />');
    $('#submit_onboard_btn').prop('disabled', true);

    $.ajax(
    {
        url: '../onboard/ajax.php',
        type: 'post',
        data: $('#onboard_form').serialize(),
        dataType: 'json',
        success: function(data)
        {            
            $('#submit_onboard_btn').html('Ansök');      
            $('#submit_onboard_btn').prop('disabled', false);      
            
            if( data.response==1 )
            {
                $('.success_message').css('display', 'inline');
                $('.success_message').animate({right: '0'}, 500 , 'linear');
                $('.success_message').delay(8000).fadeOut(1000);
            }            
            else
                alert('Try again later.');
        }
    });  
    $('#onboard_form')[0].reset(); 
});






/* JS for adminPanel/index.php and adminPanel/addClient.php */
//removing onboard request from table 'temp'
$('.color_red').click(function(e)
{
    index_id = ($(this).attr('id')).slice(-2);
    $('#add_client_btn').prop('disabled', true);

    $.ajax(
    {
        type: 'post',
        url: '../adminPanel/ajax.php',
        data: {'flag' : 2, 'index_id': index_id, 'csrf_token': $('#csrf_token').val()},
        dataType: 'json',
        success: function(data)
        {
            $('#add_client_btn').prop('disabled', false);

            if(data.response != -1)
                location.reload();
            else
                window.open('../logout', '_self');
        }
    });
});


//show add client form on clicking 'add'
$('.color_blue').click(function(e)
{
    $('#add_client_btn').prop('disabled', true);

    index_id = ($(this).attr('id')).slice(-2);
    $.ajax(
    {
        type: 'post',
        url: '../adminPanel/ajax.php',
        data: {'flag' : 3, 'index_id': index_id, 'csrf_token': $('#csrf_token').val()},
        dataType: 'json',
        success: function(data)
        {
            if(data.response != -1)
            {
                $('#add_client_btn').prop('disabled', false);
                
                $('#company_name').val(data.company_name);
                $('#contact_name').val(data.contact_name);
                $('#email').val(data.email);
                $('#contact_number').val(data.contact_number);
                $('#index_id').val(data.index_id);
            }            
            else
               window.open('../logout', '_self');
        }
    });   
});


//add client details to table 'clients' (accept onboard request) 
$('#add_client_form').submit(function(e)
{
    e.preventDefault();    

    $('#add_client_btn').html('<img src="../theme/img/loader3.gif" height="20" width="20" />');
    $('#add_client_btn').prop('disabled', true);

    $.ajax(
    {
        type: 'post',
        url: '../adminPanel/ajax.php',
        data: $('#add_client_form').serialize(),
        dataType: 'json',
        success: function(data)
        {
            $('#add_client_btn').html('Add');
            $('#add_client_btn').prop('disabled', false);

            if(data.response == -1)
                window.open('../logout', '_self');                
            else if(data.response == 0)
                alert('Email already exists!');
            else
                location.reload();                
        }
    });   
});






/* JS for adminPanel/addClient.php */
if( $('#addClient_token').val()==1 )
{
    url  = (window.location.href).split('?token=');     
    window.history.pushState("object or string", "Title", url[0]);

    if( url[1].length>0 )
    {
        $.ajax(
        {
            url: '../adminPanel/ajax.php',
            type: 'POST',
            data: {'flag': 5, 'token': url[1], 'csrf_token': $('#csrf_token').val()},
            dataType: 'JSON',
            success: function (data)
            {
                if(data.response != -1)
                    $('#add_client_'+data.index_id).click();
                else
                    window.open('../logout', '_self');
            }   
        });
    }       
}


/* JS for adminPanel/manageClients.php */
$('.removeClient').click(function(e)
{
    e.preventDefault();

    clientID = $(this).attr('id');
    $(this).prop('disabled', true);

    $.ajax(
    {
        type: 'post',
        url: '../adminPanel/ajax.php',
        data: {'flag' : 8, 'clientID': clientID, 'csrf_token': $('#csrf_token').val()},
        dataType: 'json',
        success: function(data)
        {
            $(this).prop('disabled', false);

            if(data.response != -1)
                location.reload();
            else
                window.open('../logout', '_self');
        }
    });
});


/* JS for dashboard/manageUsers.php */
$('.remove_user').click(function(e)
{
    e.preventDefault();

    email = ($(this).attr('id')).split('_')[1];
    $(this).prop('disabled', true);

    $.ajax(
    {
        type: 'post',
        url: '../dashboard/ajax.php',
        data: {'flag' : 8, 'email': email, 'csrf_token': $('#csrf_token').val()},
        dataType: 'json',
        success: function(data)
        {
            if(data.response != -1)
                location.reload();
            else
                window.open('../logout', '_self');
        }
    });
});




/* JS for forgotPassword/index.php */
$('#forgot_pwd_form').submit(function(e)
{
    e.preventDefault();

    if( $('#email_').val().length >0  )
    {
        $('#forgot_pwd_btn').html('<img src="../theme/img/loader3.gif" height="20" width="20" />');
        $('#forgot_pwd_btn').prop('disabled', true);

        $.ajax(
        { 
            type: 'POST',
            url: '../forgotPassword/ajax.php',            
            dataType: 'JSON',
            data: $('#forgot_pwd_form').serialize(),
            success: function(data)
            {        
                $('#forgot_pwd_btn').html('Reset Password');
                $('#forgot_pwd_btn').prop('disabled', false);

                if(data.response==0)
                    toastr.error("User doesn't exist");
                else
                    toastr.success("Password reset link sent to your email");                
            }
        });        
    }   
});



/* JS for resetPassword/index.php */
$('#reset_password_form').submit(function(e)
{
    e.preventDefault();

    if( $('#new_password').val() != $('#confirm_password').val() )
        alert('Passwords do not match!');
    else
    {
        $('#reset_password_btn').html('<img src="../theme/img/loader3.gif" height="20" width="20" />');
        $('#reset_password_btn').prop('disabled', true);

        $.ajax(
        { 
            type: 'POST',
            url: '../resetPassword/ajax.php',            
            dataType: 'JSON',
            data: $('#reset_password_form').serialize(),
            success: function(data)
            {        
                if(data.response==1)
                    $('.wrap').html("<p style='padding: 4em; font-size: 18px; text-align: center;'>Your password has been reset!<br/><a href='../login'>Return to login page.</a></p>");
                else
                {
                    $('#reset_password_btn').html('Reset Password');
                    $('#reset_password_btn').prop('disabled', false);
                    alert('Something went wrong!');
                }
            }
        });        
    }  
});



/* JS for dashboard/index.php */
//download and view consent template
$(document).on('click', '#view_template_sw', function(e)
{   
    e.preventDefault();
    $.ajax(
    { 
        type: 'POST',
        url: '../dashboard/ajax.php',            
        dataType: 'json',
        data: {'csrf_token': jQuery('#csrf_token').val(), 'flag': 13, 'type': 1},
        success: function(data)
        {        
            if(data.response==1)
            {
                if(data.file_doc!=='')
                    window.open(data.file_doc, '_self');
                if(data.file_pdf!=='')
                    window.open(data.file_pdf);
            }            
        }
    });        
});

$(document).on('click', '#view_template_en', function(e)
{   
    e.preventDefault();
    $.ajax(
    { 
        type: 'POST',
        url: '../dashboard/ajax.php',            
        dataType: 'JSON',
        data: {'csrf_token':jQuery('#csrf_token').val(), 'flag': 13, 'type': 2},
        success: function(data)
        {        
            if(data.response==1)
            {
                if(data.file_doc!=='')
                    window.open(data.file_doc, '_self');
                if(data.file_pdf!=='')
                    window.open(data.file_pdf);
            }
        }
    });        
});
//download and view consent template end



$('#lang').click(function()
{
    $(this).css(
    {
        'background-color': '#222222',
        'color': 'white'
    });
});

$('.on_hover').mouseenter(function()
{
    $(this).css('color', 'black');
});


$('.on_hover').mouseleave(function()
{
    $(this).css('color', 'white');
});



$('#swedish').click(function(e)
{
    $('#language').html('Sverige <span class = "caret"></span>');
    $('#select_form_level').css('display', 'inline');
   
    $('#place_order_form_swe1').css('display', 'block');
    $('#place_order_form_swe2').css('display', 'none');
    $('#place_order_form_swe3').css('display', 'none');
    $('#place_order_form_eng').css('display', 'none');
    $('#formLevel_1').click();
});

$('#english').click(function(e)
{
    $('#language').html('Utomlands <span class = "caret"></span>');
    $('#select_form_level').css('display', 'none');
    
    $('#place_order_form_swe1').css('display', 'none');
    $('#place_order_form_swe2').css('display', 'none');
    $('#place_order_form_swe3').css('display', 'none');
    $('#place_order_form_eng').css('display', 'block');

    $('#level1_desciption').css('display', 'none');
    $('#level2_desciption').css('display', 'none');
    $('#level3_desciption').css('display', 'none');
    $('#level4_desciption').css('display', 'block');
});


$('#formLevel_1').click(function(e)
{
    $('#select_form_level').html('Nivå 1 <span class = "caret"></span>');
    $('#place_order_form_swe1').css('display', 'block');
    $('#place_order_form_swe2').css('display', 'none');
    $('#place_order_form_swe3').css('display', 'none');

    $('#level1_desciption').css('display', 'block');
    $('#level2_desciption').css('display', 'none');
    $('#level3_desciption').css('display', 'none');
    $('#level4_desciption').css('display', 'none');
});

$('#formLevel_2').click(function(e)
{
    $('#select_form_level').html('Nivå 2 <span class = "caret"></span>');
    $('#place_order_form_swe1').css('display', 'none');
    $('#place_order_form_swe2').css('display', 'block');
    $('#place_order_form_swe3').css('display', 'none');

    $('#level1_desciption').css('display', 'none');
    $('#level2_desciption').css('display', 'block');
    $('#level3_desciption').css('display', 'none');
    $('#level4_desciption').css('display', 'none');
});

$('#formLevel_3').click(function(e)
{
    $('#select_form_level').html('Nivå 3 <span class = "caret"></span>');
    $('#place_order_form_swe1').css('display', 'none');
    $('#place_order_form_swe2').css('display', 'none');
    $('#place_order_form_swe3').css('display', 'block');

    $('#level1_desciption').css('display', 'none');
    $('#level2_desciption').css('display', 'none');
    $('#level3_desciption').css('display', 'block');
    $('#level4_desciption').css('display', 'none');
});



//place order
$('#place_order_form_swe1').submit(function(e)
{
    e.preventDefault(); 

    if (confirm("Are you sure if it's the correct name and social security number?"))
    {
        $('#place_order_btn_swe1').html('<img src="../theme/img/loader3.gif" height="20" width="20" /> Uploading files');
        $('#place_order_btn_swe1').prop('disabled', true);

        fData = new FormData( $('#place_order_form_swe1')[0] );

        $.ajax(
        { 
            type: 'POST',
            url: '../dashboard/ajax.php',        
            dataType: 'JSON',
            data: fData,
            cache: false,
            contentType: false,
            processData: false,
            success: function(data)
            {          
                $('#place_order_btn_swe1').html('Skicka beställning');
                $('#place_order_btn_swe1').prop('disabled', false);

                if(data.response != -1)
                {
                    $('#place_order_form_swe1')[0].reset();

                    //upadate total orders and pending orders
                    $('#total_orders').html(data.total_orders);
                    $('#pending_orders').html(data.pending_orders);
                    $('#orders_progress').html(data.orders_progress);
                            
                    //display alert message
                    $('#info_modal').css('display', 'block');         

                    //show overlay
                    $('#body_overlay').css('display', 'block');                             
                }
                else
                    window.open('../logout', '_self');
            }
        });     
    } 
});


$('#place_order_form_swe2').submit(function(e)
{
    e.preventDefault();

    if (confirm("Are you sure if it's the correct name and social security number?"))
    {
        $('#place_order_btn_swe2').html('<img src="../theme/img/loader3.gif" height="20" width="20" /> Uploading files');
        $('#place_order_btn_swe2').prop('disabled', true);

        fData = new FormData( $('#place_order_form_swe2')[0] );

        $.ajax(
        { 
            type: 'POST',
            url: '../dashboard/ajax.php',        
            dataType: 'JSON',
            data: fData,
            cache: false,
            contentType: false,
            processData: false,
            success: function(data)
            {          
                $('#place_order_btn_swe2').html('Skicka beställning');
                $('#place_order_btn_swe2').prop('disabled', false);

                if(data.response != -1)
                {
                    $('#place_order_form_swe2')[0].reset();

                    //upadate total orders and pending orders
                    $('#total_orders').html(data.total_orders);
                    $('#pending_orders').html(data.pending_orders);
                    $('#orders_progress').html(data.orders_progress);

                    //display alert message
                    $('#info_modal').css('display', 'block');     

                    //show overlay
                    $('#body_overlay').css('display', 'block');
                }
                else
                    window.open('../logout', '_self');
            }
        });
    }
});


$('#place_order_form_swe3').submit(function(e)
{
    e.preventDefault(); 
  
    $('#place_order_btn_swe3').html('<img src="../theme/img/loader3.gif" height="20" width="20" />');
    $('#place_order_btn_swe3').prop('disabled', true);

    $.ajax(
    { 
        type: 'POST',
        url: '../dashboard/ajax.php',        
        dataType: 'JSON',
        data: $('#place_order_form_swe3').serialize(),
        success: function(data)
        {          
            $('#place_order_btn_swe3').html('Skicka beställning');
            $('#place_order_btn_swe3').prop('disabled', false);

            if(data.response != -1)
            {
                $('#place_order_form_swe3')[0].reset();

                //upadate total orders and pending orders
                $('#total_orders').html(data.total_orders);
                $('#pending_orders').html(data.pending_orders);
                $('#orders_progress').html(data.orders_progress);

                //display alert message
                $('#info_modal').css('display', 'block');     

                //show overlay
                $('#body_overlay').css('display', 'block');
            }
            else
                window.open('../logout', '_self');
        }
    });     
});


$('#place_order_form_eng').submit(function(e)
{
    e.preventDefault(); 

    $('#place_order_btn_eng').html('<img src="../theme/img/loader3.gif" height="20" width="20" />');
    $('#place_order_btn_eng').prop('disabled', true);

    $.ajax(
    { 
        type: 'POST',
        url: '../dashboard/ajax.php',        
        dataType: 'JSON',
        data: $('#place_order_form_eng').serialize(),
        success: function(data)
        {          
            $('#place_order_btn_eng').html('Skicka beställning');
            $('#place_order_btn_eng').prop('disabled', false);

            if(data.response != -1)
            {
                $('#place_order_form_eng')[0].reset();

                //upadate total orders and pending orders
                $('#total_orders').html(data.total_orders);
                $('#pending_orders').html(data.pending_orders);
                $('#orders_progress').html(data.orders_progress);

                //display alert message
                $('#info_modal').css('display', 'block');     

                //show overlay
                $('#body_overlay').css('display', 'block');
            }
            else
                window.open('../logout', '_self');
        }
    }); 
});


//close modal
$(document).on('click', '#close_modal', function()
{    
    $('#info_modal').css('display', 'none');
    //hide overlay
    $('#body_overlay').css('display', 'none');       
});




/* JS for dashboard/addUsers.php */
$('#addUser_form').submit(function(e)
{
    e.preventDefault();
    $('#addUser_btn').prepend('<img src="../theme/img/loader3.gif" height="20" width="20" />');
    $('#addUser_btn').prop('disabled', true);

    $.ajax(
    {
        type: 'post',
        url: '../dashboard/ajax.php',
        data: $('#addUser_form').serialize(),
        dataType: 'json',
        success: function(data)
        {
            $('#addUser_btn').html('Lägg till användare');
            $('#addUser_btn').prop('disabled', false);

            if(data.response ==1)
            {
                $('#addUser_form')[0].reset();

                //display alert message
                $('.success_message4').css('display', 'inline');
                $('.success_message4').animate({right: '-2em'}, 500 , 'linear');
                $('.success_message4').delay(6000).fadeOut(1000);       
            }
            else if(data.response ==0)
                alert('This email ID already exists!')
            else
                window.open('../logout', '_self');
        }
    });    
});



/* JS for adminPanel/adminSettings.php */
$('#settings_form').submit(function(e)
{
    e.preventDefault();

    // if( $('#medgivande_sample_upload').val()!=='' )
    //     $('#settings_btn').html('<img src="../theme/img/loader3.gif" height="20" width="20" /> Uploading file');
    // else
    $('#settings_btn').html('<img src="../theme/img/loader3.gif" height="20" width="20" />');

    $('#settings_btn').css('disabled', true);

    formData = new FormData( $('#settings_form')[0] );

    $.ajax(
    {
        type: 'post',
        url: '../adminPanel/ajax.php',
        data: formData,
        dataType: 'json',
        cache: false,
        processData: false,
        contentType: false,
        success: function(data)
        {
            if(data.response != -1)
                location.reload(); 
            else
                window.open('../logout', '_self');
        }
    });   

    //$('#settings_btn').html('Update Settings');
});


/* JS for adminPanel/adminSettings.php */
$('#change_password_form').submit(function(e)
{
    e.preventDefault();

    if( $('#new_password').val()!=$('#confirm_password').val() )
        alert('Passwords do not match!');
    else
    {
        $('#change_password_btn').html('<img src="../theme/img/loader3.gif" height="20" width="20" />');
        $('#change_password_btn').css('disabled', true);

        $.ajax(
        {
            type: 'post',
            url: '../adminPanel/ajax.php',
            data: $('#change_password_form').serialize(),
            dataType: 'json',
            success: function(data)
            {
                $('#change_password_btn').html('Submit');
                $('#change_password_btn').prop('disabled', false);
                
                if(data.response != -1)
                {
                    $('#change_password_form')[0].reset();

                    //display alert message
                    $('.success_message4').css('display', 'inline');
                    $('.success_message4').animate({right: '-2em'}, 500 , 'linear');
                    $('.success_message4').delay(5000).fadeOut(1000);   
                }
                else
                    window.open('../logout', '_self');
                
               // console.log(data);
            }
        });
    }   
});


//change mobile number
$('#change_contact_number').submit(function(e)
{
    e.preventDefault();

    $('#change_contact_number button').html('<img src="../theme/img/loader3.gif" height="20" width="20" />');
    $('#change_contact_number button').css('disabled', true);

    $.ajax(
    {
        type: 'post',
        url: '../adminPanel/ajax.php',
        data: $('#change_contact_number').serialize(),
        dataType: 'json',
        success: function(data)
        {
            if(data.response != -1)
                location.reload();
            else
                window.open('../logout', '_self');
        }
    });
});
/* JS for adminPanel/adminSettings.php end*/





/* JS for dashboard/settings.php */
//change password
$('#change_client_password_form').submit(function(e)
{
    e.preventDefault();

    if( $('#new_password').val()!=$('#confirm_password').val() )
        alert('Passwords do not match!');
    else
    {
        $('#change_client_password_btn').html('<img src="../theme/img/loader3.gif" height="20" width="20" />');
        $('#change_client_password_btn').css('disabled', true);

        $.ajax(
        {
            type: 'post',
            url: '../dashboard/ajax.php',
            data: $('#change_client_password_form').serialize(),
            dataType: 'json',
            success: function(data)
            {
                $('#change_client_password_btn').html('Skicka');
                $('#change_client_password_btn').prop('disabled', false);

                if(data.response != -1)
                {
                    $('#change_client_password_form')[0].reset();

                    //display alert message
                    $('.success_message3').css('display', 'inline');
                    $('.success_message3').animate({right: '-2em'}, 500 , 'linear');
                    $('.success_message3').delay(5000).fadeOut(1000);       
                }
                else
                    window.open('../logout', '_self');
            }
        });
    }   
});

//change mobile number
$('#change_mobile_form').submit(function(e)
{
    e.preventDefault();

    $('#change_mobile_btn').html('<img src="../theme/img/loader3.gif" height="20" width="20" />');
    $('#change_mobile_btn').css('disabled', true);

    $.ajax(
    {
        type: 'post',
        url: '../dashboard/ajax.php',
        data: $('#change_mobile_form').serialize(),
        dataType: 'json',
        success: function(data)
        {
            $('#change_mobile_btn').html('Uppdatering');
            $('#change_mobile_btn').prop('disabled', false);

            if(data.response != -1)
            {
                //display alert message
                $('.success_message3').css('display', 'inline');
                $('.success_message3').animate({right: '-2em'}, 500 , 'linear');
                $('.success_message3').delay(5000).fadeOut(1000);                 
            }
            else
                window.open('../logout', '_self');
        }
    });
});




/* JS for adminPanel/view.php */
$('#set_followUp_date').on('submit', function(e)
{
    e.preventDefault();
    
    $('#set_followUp_date button').html('<img src="../theme/img/loader3.gif" height="20" width="20" />');
    $('#set_followUp_date button').prop('disabled', true);

    $.ajax(
    {
        type: 'post',
        url: '../adminPanel/ajax.php',
        data: jQuery('#set_followUp_date').serialize(),
        dataType: 'json',
        success: function(data)
        {
            location.reload();
        }
    });         
});


$('#send_email_form').on('submit', function(e)
{
    e.preventDefault();

    if( $('#send_email_body').val().length==0 )       
        alert('Email content cannot be empty!');
    else
    {
        $('#send_email_btn').html('<img src="../theme/img/loader3.gif" height="20" width="20" />');
        $('#send_email_btn').prop('disabled', true);

        formData = new FormData( $('#send_email_form')[0] );

        $.ajax(
        {
            type: 'post',
            url: '../adminPanel/ajax.php',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(data)
            {
                $('#send_email_btn').html('Send Email');
                $('#send_email_btn').prop('disabled', false);

                if(data.response != -1)
                {
                    $('#send_email_form')[0].reset();

                    //display alert message
                    $('.success_message3').css('display', 'inline');
                    $('.success_message3').animate({right: '-2em'}, 500 , 'linear');
                    $('.success_message3').delay(5000).fadeOut(1000);   
                }
                else
                    window.open('../logout', '_self');
            }
        });         
    } 
});
/* JS for adminPanel/view.php end */


/* JS for adminPanel/manageClients.php */
jQuery('#templates_form input, #templates_form select').bind('keyup change', function()
{
    if(jQuery('#client_email').val() && (jQuery('#newCVT1').val() || jQuery('#newCVT2').val() || jQuery('#newCVT3').val() || jQuery('#newCVT4').val()))
        jQuery('#templates_form button').prop('disabled', false);
    else
        jQuery('#templates_form button').prop('disabled', true);
});


$('#templates_form').on('submit', function(e)
{
    toastr.remove();
    e.preventDefault();
    $('#templates_form button').html('Uploading...  <img src="../theme/img/loader3.gif" height="20" width="20" />');
    $('#templates_form button').prop('disabled', true);

    formData = new FormData($('#templates_form')[0]);

    $.ajax(
    {
        type: 'post',
        url: '../adminPanel/ajax.php',
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        dataType: 'json',
        success: function(data)
        {
            $('#templates_form button').html('Update Templates');
            $('#templates_form button').prop('disabled', false);

            if(data.response==1)
            {
                $('#templates_form')[0].reset();

                //display alert message
                toastr.success('Templates uploaded!');
            }
            else if(data.response==2)
            {
                $('#newCVT1').val('');
                $('#newCVT3').val('');
                toastr.error('Allowed file types: .doc, .docx');
            }
            else if(data.response==3)
            {
                $('#newCVT2').val('');
                $('#newCVT4').val('');
                toastr.error('Allowed file types: .pdf');
            }
        }
    });         
});







//2fa disable / enable settings
/* JS for dashboard/settings.php */
// $('.btn2FA').click(function(e)
// {
//         // $('.btn2FA').html('<img src="../theme/img/loader3.gif" height="20" width="20" />');
//         // $('.btn2FA').css('disabled', true);
//         //get value to be changed, either enable or disable
//         csrf_token = $("#csrf_token").val();
//         newStatus = $(this).attr('id');
//         cid = $("#c_clientID").val();
//         flag = 6;
//         $.ajax(
//         {
//             type: 'post',
//             url: '../dashboard/ajax.php',
//             data: {csrf_token:csrf_token, flag:flag, newStatus:newStatus, cid:cid},
//             // dataType: 'json',
//             success: function(data)
//             {
//                //display alert message
//                 $(".success_message4").show();
//                 $('.success_message4').css('display', 'inline');
//                 $('.success_message4').animate({right: '-2em'}, 500 , 'linear');
//                 $('.success_message4').delay(5000).fadeOut(1000);       

//                 // $('.btn2FA').html('Submit');
//                 // $('.btn2FA').prop('disabled', false);
//             }
//         });
// });//end

//2fa disable / enable settings --admin
/* JS for dashboard/settings.php */
// $('.btn2FAdmin').click(function(e)
// {
//         csrf_token = $("#csrf_token").val();
//         newStatus = $(this).attr('id');
//         aid = $("#ac_adminID").val();
//         flag = 200;
//         $.ajax(
//         {
//             type: 'post',
//             url: '../adminPanel/ajax.php',
//             data: {csrf_token:csrf_token, flag:flag, newStatus:newStatus, aid:aid},
//             // dataType: 'json',
//             success: function(data)
//             {
//                 //display alert message
//                 $(".success_message5").show();
//                 $('.success_message5').css('display', 'inline');
//                 $('.success_message5').animate({right: '-2em'}, 500 , 'linear');
//                 $('.success_message5').delay(5000).fadeOut(1000);       
//             }
//         });
// });//end