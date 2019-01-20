<?php
	session_start();    

	//connect database
	include '../class/dbconnector1.php';
	include '../class/dbconnector2.php';

	//including mysql functions file
    include '../class/class.base.php';
    $base = new base();

    //include config file
    include '../class/config.php';

    //set timezone
    date_default_timezone_set('Europe/Stockholm');
    
	//include PHPMailer
    include '../PHPmailer/vendor/autoload.php';
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    //log errors
	ini_set("log_errors", 1);
	ini_set("error_log", "error.log");


    //verifying form submission and CSRF token
	if( isset( $_POST['email_'] ) && $_POST['csrf_token']==$_SESSION['csrf_token'] )
	{
		//verifying email in table 'adminaccounts' and table 'clients'
		if( $base->select('adminaccounts', '*', array('ac_email' => $base->enc($_POST['email_']))) !=false || $base->select('clients', '*', array('c_email' => $base->enc($_POST['email_']))) != false )
		{
			//create unique token
			$token = $base->recursive_generator('token', 'token', 'tokens');
			$base->insert('tokens', array('token'=>$base->enc($token), 'type'=>$base->enc('forgot_pwd'), 'user'=> $base->enc($_POST['email_']), 'created_on'=>$base->enc(date('d-M-Y H:i:s'))));

			send_email( $_POST['email_'], $token );

			//create log
			$insert_array = array( 'l_user' => $base->enc($_POST['email_']),
								   'l_logDescription' => $base->enc("Password reset link e-mailed to ".$_POST['email_']),
								   'l_logType' => $base->enc('email_resetPassword'),
								   'l_accessLevel' => $base->enc('1'),
								   'l_sourceFile' => $base->enc(BASE_URL.'/forgotPassword/index.php'),
								   'l_dateTime' => $base->enc(date('d-M-Y H:i:s')));
			$base->insert('logs', $insert_array);

			$data = array('response'=>1);
			echo json_encode($data);
			die();
		}

		else
		{
			$data = array('response'=>0);
			echo json_encode($data);
		}
	}



	//send email with password reset link
	function send_email($email, $token)
    {
    	$content = '<!DOCTYPE html>
							<html>
								<head>
									<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
									<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
								</head>
								<body>
									        <table align="center" cellpadding="0" border="0" cellspacing="0" style="width: 100%;" style="text-align:center;">
									        	<tr>
									        		<td style="max-width:700px; display: block; clear: both;text-align:center;margin: 0px auto;padding:0px;border-top:15px solid #075a98;border-left:15px solid #075a98;border-right:15px solid #075a98">
									        			<table align="center" cellpadding="0" border="0" cellspacing="0" style="width: 100%;">
									        				<tr><td height="20px" style="font-size:1px;line-height:30px;">&nbsp;</td></tr>
									    					<tr>
									    						<td align="center" style="padding:0px;text-align:center;"><img src=(logo) alt="" style="max-width:100%;"></td>
									    					</tr>
									    					<tr><td height="20px" style="font-size:1px;line-height:20px;">&nbsp;</td></tr>
									    				</table>
									        		</td>
									        	</tr>
									        	<tr>
									        		<td style="max-width:700px; display: block; clear: both;text-align:center;margin: 0px auto;padding:0px;border-left:15px solid #075a98;border-right:15px solid #075a98;">
									        			<table align="center" cellpadding="0" border="0" cellspacing="0" style="width: 100%;">
									        				<tr><td height="20px" style="font-size:1px;line-height:20px;">&nbsp;</td></tr>
									    					<tr>
									    						<td align="center" style="padding:0px 10px;text-align:center;">
									    							<h2 style="font-size: 27px;line-height: 35px;">(heading)</h2>
									    							<p style="padding: 10px 25px;text-align: center;font-size: 16px;line-height: 26px;margin-bottom:5px;">(text1)</p>
									    							<p style="padding:0px 25px;text-align: justify;font-size: 16px;line-height: 26px;margin:0px;">(text2)</p>
									    						</td>
									    					</tr>
									    					<tr><td height="20px" style="font-size:1px;line-height:20px;">&nbsp;</td></tr>
									    				</table>
									        		</td>
									        	</tr>
									        	<tr>
									        		<td style="max-width:700px; display: block; clear: both;text-align:center;margin: 0px auto;padding:0px;border-left:15px solid #075a98;border-right:15px solid #075a98;">
									        			<table align="center" cellpadding="0" border="0" cellspacing="0" style="width: 100%;">
									        				<tr><td height="20px" style="font-size:1px;line-height:20px;">&nbsp;</td></tr>
									    					<tr>
									    						<td align="center" style="padding:0px 10px;text-align:center;">
									    							<a href="(button_href)" style="text-decoration: none;padding: 10px 35px;background: #075a98;color: white;border-radius: 6px;text-align: center;">(button)</a>
									    						</td>
									    					</tr>
									    					<tr><td height="50px" style="font-size:1px;line-height:50px;">&nbsp;</td></tr>
									    				</table>

									        		</td>
									        	</tr>
									        	<tr>
									        		<td style="max-width:700px; display: block; clear: both;text-align:center;margin: 0px auto;padding:0px;border-left:15px solid #075a98;border-right:15px solid #075a98;border-bottom:15px solid #075a98;background-color:#fafafa;">
									        			<table bgcolor="#fafafa" align="center" cellpadding="0" border="0" cellspacing="0" style="width: 100%;background-color:#fafafa;">
									        				<tr><td height="10px" style="font-size:1px;line-height:5px;">&nbsp;</td></tr>
									    					<tr>
									    						<td align="center" style="padding:0px 10px;text-align:center;">
									    							<p style="font-size: 12px;margin: 0px;">(C) 2018 Vesper Group</p>
									    						</td>
									    					</tr>
									    					<tr><td height="10px" style="font-size:1px;line-height:5px;">&nbsp;</td></tr>
									    				</table>

									        		</td>
									        	</tr>
									        </table>        
								</body>
							</html>';

		$search = array('(heading)', '(text1)', '(text2)', '(button_href)', '(button)', '(logo)');
		$replace = array('Reset your password.', 
						 'Click on the button below to reset your password.',
						 '',
					     BASE_URL."/resetPassword?token=".$token,
						 'RESET PASSWORD',
						 BASE_URL.'/onboard/image/logo.png');

		$mail = new PHPMailer(true);                     // Passing `true` enables exceptions
		try 
		{
			//Recipients
			$mail->setFrom('request@vesper.group');
			$mail->addAddress($email);      

			//Content
			$mail->isHTML(true);                         // Set email format to HTML
			$mail->CharSet = "UTF-8";
			$mail->Subject = 'Reset your password on Vesper';
			$mail->Body = str_replace($search, $replace, $content);

			return $mail->send();
		}
		catch (Exception $e) 
		{
			return $mail->ErrorInfo;
		}
	}
?>


