<?php
	session_start();
	
	//redirect to login page if admin isn't logged in
	if( !isset($_SESSION['admin_email']) )
		echo json_encode(array('response' => -1));

	//connect database
	include '../class/dbconnector1.php';
	include '../class/dbconnector2.php';

	//include config file
	include '../class/config.php';

	//including mysql functions file
    include '../class/class.base.php';
    $base = new base();

    //include PHPMailer
    include '../PHPmailer/vendor/autoload.php';
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    //set timezone
    date_default_timezone_set('Europe/Stockholm');

    //log errors
	ini_set("log_errors", 1);
	ini_set("error_log", "error.log");


	function uniqueFilename()
	{
		global $base, $conn2;
		$files = $base->select('clients', '*', null, 0, 1);
		$filename = bin2hex(random_bytes(5));
		for($i=0; $i<$base->getRowCount($conn2, 'clients'); $i++)
		{
			if($files[$i]->cv_template_sw_doc==$filename || $files[$i]->cv_template_sw_pdf==$filename || $files[$i]->cv_template_en_doc==$filename || $files[$i]->cv_template_en_pdf==$filename)
				uniqueFilename(); 
		}
		return $filename;
	}


	//verify CSRF token
	if( isset($_POST['flag']) && strcmp($_SESSION['csrf_token'], $_POST['csrf_token'])==0 )
	{
		switch( $_POST['flag'] )
		{
			//update number of pending onboard requests 
			case 1:
				$query = $base->select('temp', '*', null, 1, 0);
				echo json_encode(array('response' => $query));
				break;


			//remove row from table 'temp' on clicking 'remove' [addClient.php]
			case 2:
				//logging activity: onboard request removed 
				$insert_log = array('l_user' => $base->enc($_SESSION['admin_email']),
									'l_logDescription' => $base->enc('Onboard request removed by '.$_SESSION['admin_email']), 
									'l_logType' => $base->enc('onboard_remove'), 
									'l_accessLevel' => $base->enc('1'), 
									'l_sourceFile' => $base->enc(BASE_URL.'/adminPanel/addClient.php'),
									'l_dateTime' => $base->enc(date('d-M-Y H:i:s')));
				$base->insert('logs', $insert_log);

				$indexid = htmlspecialchars($_POST['index_id']);
				try
				{
					$query = $conn1->prepare('DELETE FROM temp WHERE index_id=:index_id');
					$query->bindParam(':index_id', $indexid);
					$query->execute();
				}
				catch(PDOException $e)
				{
					$e->getMessage();
				}

				echo json_encode(array('response' => 1));
				break;


			//return row from table 'temp' (return onboard request details) [addClient.php]
			case 3:
				$query = $base->select( 'temp', '*', array('index_id'=>htmlspecialchars($_POST['index_id'])));

				echo json_encode(array('response' => 1, 'company_name' => $base->dec($query->company_name), 'contact_name' => $base->dec($query->contact_name), 'email' => $base->dec($query->email), 'contact_number' => $base->dec($query->contact_number), 'index_id' => htmlspecialchars($_POST['index_id']) ));
				break;


			//move data from table 'temp' to table 'clients' (Accepting onboard request) [addClient.php]
			case 4:
				//checking if email already exists
				if( $base->select('clients', 'c_email', array('c_email'=>$base->enc(htmlspecialchars($_POST['email']))))==false )
				{
					//logging activity: add new client 
					$insert_log = array('l_user' => $base->enc($_SESSION['admin_email']),
										'l_logDescription' => $base->enc('New client '.htmlspecialchars($_POST['email']).' added by '.$_SESSION['admin_email']), 
										'l_logType' => $base->enc('add_client'), 
										'l_accessLevel' => $base->enc('1'), 
										'l_sourceFile' => $base->enc(BASE_URL.'/adminPanel/addClient.php'),
										'l_dateTime' => $base->enc(date('d-M-Y H:i:s')));
					$base->insert('logs', $insert_log);


					//delete row from table 'temp'
					$temp_id = substr(htmlspecialchars($_POST['index_id']), -2);
					try
					{
						$query2 = $conn1->prepare('DELETE FROM temp WHERE index_id=:index_id');
						$query2->bindParam(':index_id', $temp_id);
						$query2->execute();
					}
					catch(PDOException $e)
					{
						$e->getMessage();
					}

					//insert row in table 'clients'
					$clientID = $base->recursive_generator('c_clientID', null, 'clients');
					$password = bin2hex(random_bytes(7));
					$companyName = htmlspecialchars($_POST['company_name']);
					$contactName = htmlspecialchars($_POST['contact_name']);

					$insert_array = array('c_clientID' => $base->enc($clientID), 			
										  'c_companyName' => $base->enc($companyName),
										  'c_contactName' => $base->enc($contactName),
										  'c_is_parent' => $base->enc('0'),
										  'status2FA' => $base->enc('0'),
										  'c_email' => $base->enc(htmlspecialchars($_POST['email'])),
										  'c_role' => $base->enc('3'),
										  'c_password' => $base->enc(hash('sha256', $password)),
										  'c_contactNumber' => $base->enc(htmlspecialchars($_POST['contact_number'])),
										  'c_registeredOn' => $base->enc(date('d-M-Y  H:i:s')));
					$base->insert('clients', $insert_array);

					//send email to client with password
					send_email(4, htmlspecialchars($_POST['email']), $password);

					echo json_encode(array('response' => 1));
				}
				else
					echo json_encode(array('response' => 0));
				break;


			//return index_id from table 'temp' for given token
			case 5:			
				$query = $base->select( 'temp', 'index_id', array('token'=>$base->enc(htmlspecialchars($_POST['token']))) );
				echo json_encode(array('response' => 1, 'index_id' => $query->index_id ));
				break;


			//change admin settings [adminSettings.php]
			case 6:
				if(!empty($_POST['file_deletion_date']))
				{
					// if( $_FILES['medgivande_sample_upload']['tmp_name']!='' )
					// {	
					// 	$ext = substr($_FILES['medgivande_sample_upload']['name'], strrpos($_FILES['medgivande_sample_upload']['name'], '.'));			
					// 	$attachmentName = bin2hex(random_bytes(5)).$ext;
					// 	move_uploaded_file($_FILES['medgivande_sample_upload']['tmp_name'], 'documents/' . $attachmentName);

					// 	$insert_array = array('admin_email'=> $base->enc(htmlspecialchars($_POST['admin_email'])),
					// 						  'file_deletion_date'=> $base->enc(htmlspecialchars($_POST['file_deletion_date'])),
					// 						  'medgivande_sample'=> $base->enc(BASE_URL.'/adminPanel/documents/'.$attachmentName));
					// 	$base->updateMQ('adminsettings', $insert_array, 'index_id', $base->enc('1'));
					// }		
					
					// else
					// {
					$insert_array = array('file_deletion_date'=> $base->enc(htmlspecialchars($_POST['file_deletion_date'])));
					$base->updateMQ('adminsettings', $insert_array, 'index_id', 1);
					echo json_encode(array('response' => 1));
				//}
				}			
				break;


			//send email to client after order completed [view.php]
			case 7:
				$attachmentName='';
				$reportDateTime= date('d-M-Y  H:i:s');
				$current_timestamp = time();

				$sample_report_name = $_FILES['upload_sample_report']['name'];

				if( $sample_report_name!='' )
				{
					//move sample report to directory 'adminPanel/reports'
					$ext = substr($_FILES['upload_sample_report']['name'], strrpos($_FILES['upload_sample_report']['name'], '.'));		
					$attachmentName = $base->recursive_generator('r_reportName', 'report', 'reports').$ext;
					move_uploaded_file($_FILES['upload_sample_report']['tmp_name'], 'reports/' . $attachmentName);

					//create unique reportID
					$reportID = $base->recursive_generator('r_reportID', null, 'reports');

					//logging activity: upload sample report
					$insert_log = array('l_user' => $base->enc($_SESSION['admin_email']),
										'l_logDescription' => $base->enc("Report uploaded by ".$_SESSION['admin_email'].".".PHP_EOL."(Report ID: ".$reportID.")"),
										'l_logType' => $base->enc('upload_report'),
										'l_accessLevel' => $base->enc('1'),
										'l_sourceFile' => $base->enc(BASE_URL.'/adminPanel/view.php'),
										'l_dateTime' => $base->enc(date('d-M-Y H:i:s')));
					$base->insert('logs', $insert_log);

					//upload sample report
					$insert_array = array('r_reportID'=> $base->enc($reportID),
										  'o_orderID'=> $base->enc(htmlspecialchars($_POST['orderID'])),
										  'r_reportName'=> $base->enc($attachmentName),
										  'r_reportLocation'=> $base->enc(BASE_URL.'/adminPanel/reports/'.$attachmentName),
										  'r_reportType'=> $base->enc('sample'),
										  'o_orderStatus'=> $base->enc(htmlspecialchars($_POST['change_order_status'])),
										  'r_reportDateTime'=> $base->enc($reportDateTime),
										  'r_reportDateTime_ts'=> $base->enc($current_timestamp));
					$base->insert('reports', $insert_array);

					//update report URL in 'orders' table
					$base->updateMQ('orders', array('r_reportLocation'=>$base->enc(BASE_URL.'/adminPanel/reports/'.$attachmentName)), 'o_orderID', $base->enc(htmlspecialchars($_POST['orderID'])));
				}				
				

				if( $_FILES['upload_full_report']['name']!='' )
				{
					//move full report to directory 'adminPanel/reports'
					$ext = substr($_FILES['upload_full_report']['name'], strrpos($_FILES['upload_full_report']['name'], '.'));			
					$attachmentName2 = $base->recursive_generator('r_reportName', 'report', 'reports').$ext;
					move_uploaded_file($_FILES['upload_full_report']['tmp_name'], 'reports/' . $attachmentName2);

					//create unique reportID
					$reportID = $base->recursive_generator('r_reportID', null, 'reports');

					//logging activity: upload full report 
					$insert_log = array('l_user' => $base->enc($_SESSION['admin_email']),
										'l_logDescription' => $base->enc("Report uploaded by ".$_SESSION['admin_email'].".".PHP_EOL."(Report ID: ".$reportID.")"),
										'l_logType' => $base->enc('upload_report'),
										'l_accessLevel' => $base->enc('1'), 
										'l_sourceFile' => $base->enc(BASE_URL.'/adminPanel/view.php'),
										'l_dateTime' => $base->enc(date('d-M-Y H:i:s')));
					$base->insert('logs', $insert_log);

					//upload full report
					$insert_array = array('r_reportID'=> $base->enc($reportID), 
										  'o_orderID'=> $base->enc(htmlspecialchars($_POST['orderID'])),
										  'r_reportName'=> $base->enc($attachmentName2),
										  'r_reportLocation'=> $base->enc(BASE_URL.'/adminPanel/reports/'.$attachmentName2),
										  'r_reportType'=> $base->enc('full'),
										  'o_orderStatus'=> $base->enc(htmlspecialchars($_POST['change_order_status'])),
										  'r_reportDateTime'=> $base->enc($reportDateTime),
										  'r_reportDateTime_ts'=> $base->enc($current_timestamp));
					$base->insert('reports', $insert_array);

					//update report URL in 'orders' table
					$base->updateMQ('orders', array('r_reportLocation'=> $base->enc(BASE_URL.'/adminPanel/reports/'.$attachmentName2)), 'o_orderID', $base->enc(htmlspecialchars($_POST['orderID'])));
				}
			

				//change order status in table 'orders'
				$base->updateMQ('orders', array('o_orderStatus'=> $base->enc(htmlspecialchars($_POST['change_order_status']))), 'o_orderID', $base->enc(htmlspecialchars($_POST['orderID'])));

				$body = str_replace('<p>', '', $_POST['send_email_body']);
				$body = str_replace('</p>', '', $body);
				$body = html_entity_decode($body);


				if( $sample_report_name!='' )
					$response = send_email(7, htmlspecialchars($_POST['send_email_to']), null, $attachmentName, htmlspecialchars($_POST['send_email_subject']), $body);
				else
					$response = send_email(7, htmlspecialchars($_POST['send_email_to']), null, null, htmlspecialchars($_POST['send_email_subject']), $body);

				echo json_encode(array('response' => $response));
				break;
	

			//remove client [manageClients.php]
			case 8:
				//logging activity: client removed 
				$insert_log = array('l_user' => $base->enc($_SESSION['admin_email']),
									 'l_logDescription' => $base->enc('Client removed by '.$_SESSION['admin_email'].'.'.PHP_EOL."(Client ID: ".htmlspecialchars($_POST['clientID']).')'), 
									 'l_logType' => $base->enc('client_remove'), 
									 'l_accessLevel' => $base->enc('1'), 
									 'l_sourceFile' => $base->enc(BASE_URL.'/adminPanel/manageClients.php'),
									 'l_dateTime' => $base->enc(date('d-M-Y H:i:s')));
				$base->insert('logs', $insert_log);

				$clientID = $base->enc(htmlspecialchars($_POST['clientID']));
				try
				{
					$query = $conn1->prepare('DELETE FROM clients WHERE c_clientID=:c_clientID');
					$query->bindParam(':c_clientID', $clientID);
					$query->execute();
				}
				catch(PDOException $e)
				{
					$e->getMessage();
				}

				echo json_encode(array('response' => 1));
				break;


			//change admin password [adminSettings.php]
			case 9:
				if(!empty($_POST['new_password']))
				{
					$p = htmlspecialchars($_POST['new_password']);
					$p = hash('sha256', $p);

					$base->updateMQ('adminaccounts', array('ac_password'=>$base->enc($p)), 'ac_adminID', $base->enc($_SESSION['adminID']));
					echo json_encode(array('response' => 1));
				}				
				break;


			//change admin contact number [adminSettings.php]
			case 10:
				if(!empty($_POST['mobile']))
				{
					$base->updateMQ('adminaccounts', array('ac_contactNumber'=> $base->enc(htmlspecialchars($_POST['mobile']))), 'ac_adminID', $base->enc($_SESSION['adminID']));
					echo json_encode(array('response' => 1));
				}
				break;


			//update follow up date in table 'orders' if any [view.php]
			case 11:	
				if( htmlspecialchars($_POST['followUp_date'])!='' )
				{
					$base->updateMQ('orders', array('o_followUp_date'=> $base->enc(htmlspecialchars($_POST['followUp_date']))), 'o_orderID', $base->enc(htmlspecialchars($_POST['orderID_'])));
					echo json_encode(array('response' => 1, 'd' => htmlspecialchars($_POST['followUp_date'])));
				}
				break;

			
			//update templates
			case 12:
				if(!empty(htmlspecialchars($_POST['client_email'])) && isset($_FILES))
				{
					$email = htmlspecialchars($_POST['client_email']);

					if(strlen(trim($_FILES['newCVT1']['tmp_name'])))
					{
						//uploading CVT1

						$file = trim($_FILES['newCVT1']['tmp_name']);
						$name = $_FILES['newCVT1']['name'];
						//check file format
						if(substr($name, strrpos($name, '.'))!='.doc' && substr($name, strrpos($name, '.'))!='.docx')
						{
							echo json_encode(array('response'=>2));
							die();
						}
						else
						{
							$ext = substr($name, strrpos($name, '.'));			
							$filename = uniqueFilename().$ext;
							if(move_uploaded_file($file, 'documents/'.$filename))					
								$base->updateMQ('clients', array('cv_template_sw_doc'=> BASE_URL.'/adminPanel/documents/'.$filename), 'c_email', $base->enc($email));
						}
					}

					if(strlen(trim($_FILES['newCVT2']['tmp_name'])))
					{
						//uploading CVT2

						$file = trim($_FILES['newCVT2']['tmp_name']);
						$name = $_FILES['newCVT2']['name'];
						//check file format
						if(substr($name, strrpos($name, '.'))!='.pdf')
						{
							echo json_encode(array('response'=>3, 'name'=>substr($name, strrpos($name, '.'))));
							die();
						}
						else
						{
							$ext = substr($name, strrpos($name, '.'));			
							$filename = uniqueFilename().$ext;
							if(move_uploaded_file($file, 'documents/'.$filename))			
								$base->updateMQ('clients', array('cv_template_sw_pdf'=> BASE_URL.'/adminPanel/documents/'.$filename), 'c_email', $base->enc($email));
						}
					}

					if(strlen(trim($_FILES['newCVT3']['tmp_name'])))
					{
						//uploading CVT3

						$file = trim($_FILES['newCVT3']['tmp_name']);
						$name = $_FILES['newCVT3']['name'];
						//check file format
						if(substr($name, strrpos($name, '.'))!='.doc' && substr($name, strrpos($name, '.'))!='.docx')
						{
							echo json_encode(array('response'=>2));
							die();
						}
						else
						{
							$ext = substr($name, strrpos($name, '.'));			
							$filename = uniqueFilename().$ext;
							move_uploaded_file($file, 'documents/'.$filename);							
							$base->updateMQ('clients', array('cv_template_en_doc'=> BASE_URL.'/adminPanel/documents/'.$filename), 'c_email', $base->enc($email));
						}
					}


					if(strlen(trim($_FILES['newCVT4']['tmp_name'])))
					{
						//uploading CVT4

						$file = trim($_FILES['newCVT4']['tmp_name']);
						$name = $_FILES['newCVT4']['name'];
						//check file format
						if(substr($name, strrpos($name, '.'))!='.pdf')
						{
							echo json_encode(array('response'=>3));
							die();
						}
						else
						{
							$ext = substr($name, strrpos($name, '.'));			
							$filename = uniqueFilename().$ext;
							move_uploaded_file($file, 'documents/'.$filename);							
							$base->updateMQ('clients', array('cv_template_en_pdf'=> BASE_URL.'/adminPanel/documents/'.$filename), 'c_email', $base->enc($email));
						}
					}

					echo json_encode(array('response'=>1));
				}
				break;


			default:
				break;
		

			//update 2faStatus [adminSettings.php]
			// case 200:	
			// 	//get all the data here first
			// 	$new2FAStatusVal = htmlspecialchars($_POST['newStatus']);
			// 	$ac_adminID = htmlspecialchars($_POST['aid']);

			// 	try
			// 	{
			// 		$s = $conn1->prepare("UPDATE adminaccounts SET status2FA = :s WHERE ac_adminID = :a");
			// 		$s->bindParam(":s", $new2FAStatusVal);
			// 		$s->bindParam(":a", $ac_adminID);
			// 		$s->execute();
			// 	}
			// 	catch(PDOException $e)
			// 	{
			// 		$e->getMessage();
			// 	}

			// 	echo json_encode(array('response'=>1));
			// 	break;
		}
	}
	
	

	//function to send email to client [addClient.php & view.php]
	function send_email($flag, $email, $password, $attachment=null, $subject=null, $body=null)
    {	
    	global $base;

		$mail = new PHPMailer(true);                     // Passing `true` enables exceptions
		try 
		{	
			$content = '<!DOCTYPE html>
						<html>
							<head>
								<meta charset="utf-8">
								<meta http-equiv="X-UA-Compatible" content="IE=edge">
								<meta http-equiv="pragma" content="no-cache">
								<meta name="viewport" content="user-scalable=0, width=device-width, initial-scale=1.0, maximum-scale=1.0">
							</head>
							<body>
								<table align="center" cellpadding="0" border="0" cellspacing="0" style="width: 100%;" style="text-align:center;">
									<tr>
										<td style="max-width:700px; display: block; clear: both;text-align:center;margin: 0px auto;padding:0px;border-top:15px solid #075a98;border-left:15px solid #075a98;border-right:15px solid #075a98">
											<table align="center" cellpadding="0" border="0" cellspacing="0" style="width: 100%;">
												<tr>
													<td height="20px" style="font-size:1px;line-height:30px;">&nbsp;</td>
												</tr>
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
														<p style="padding: 10px 25px;text-align: justify;font-size: 16px;line-height: 26px;margin-bottom:5px;">(text1)</p>
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

			$mail->setFrom('request@vesper.group');
			//Recipients			
			$mail->addAddress($email);			
			//Content
			$mail->isHTML(true);                         // Set email format to HTML
			

			if( $flag==4 ) //addClient.php
			{
				$search = array('(heading)', '(text1)', '(text2)', '(button_href)', '(button)', '(logo)');
				$replace = array('Välkommen till Vesper Group', 
								 'Din ansökan har blivit godkänd. Logga in genom att använda uppgifterna nedan:',
								 'Epost - ' . $email . '<br/>' . htmlentities('Lösenord') . ' - ' . $password,
								  BASE_URL.'/login',
								 'LOGIN',
								  BASE_URL.'/onboard/image/logo.png');

				//create log
				$insert_array = array( 'l_user' => $base->enc($_SESSION['admin_email']),
									   'l_logDescription' => $base->enc("Login details e-mailed to new client ".$email),
									   'l_logType' => $base->enc('email_newClient'),
									   'l_accessLevel' => $base->enc('1'),
									   'l_sourceFile' => $base->enc(BASE_URL.'/adminPanel/addClient.php'),
									   'l_dateTime' => $base->enc(date('d-M-Y H:i:s')));
				$base->insert('logs', $insert_array);

				$mail->CharSet = "UTF-8";
				$mail->Subject = htmlentities("Välkommen till Vesper Group");
				$mail->Body = str_replace($search, $replace, $content);
			}
			else  //view.php
			{
				$search = array('(heading)', '(text1)', '(text2)', '(button_href)', '(button)', '(logo)');
				$replace = array('Order update', 
								 $body,
								 ' ',
								 BASE_URL.'/login',
								 'LOGIN',
								 BASE_URL.'/onboard/image/logo.png');

				//create log
				$insert_array = array( 'l_user' => $base->enc($_SESSION['admin_email']),
									   'l_logDescription' => $base->enc("Details regarding an order e-mailed to ".$email),
									   'l_logType' => $base->enc('email_orderDetails'),
									   'l_accessLevel' => $base->enc('1'),
									   'l_sourceFile' => $base->enc(BASE_URL.'/adminPanel/view.php'),
									   'l_dateTime' => $base->enc(date('d-M-Y H:i:s')));
				$base->insert('logs', $insert_array);

				//Attachments
				if( $attachment )
    				$mail->addAttachment('reports/'.$attachment);

				$mail->CharSet = "UTF-8";
				$mail->Subject = $subject;
				$mail->Body = str_replace($search, $replace, $content);
			}

			return $mail->send();
		}
		catch (Exception $e) 
		{
			return $mail->ErrorInfo;
		}
	}
?>