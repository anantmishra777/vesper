<?php
	session_start();
	
	//redirect to login page if admin isn't logged in
	if( !isset($_SESSION['email']) )
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


	//verify CSRF token and flag
	if( isset($_POST['flag']) && strcmp($_SESSION['csrf_token'], $_POST['csrf_token'])==0 )
	{
		switch ($_POST['flag'])
		{
			//insert order details in table 'orders' and table 'orderattachments' (for Nivå 1) [dashboard.php]
			case 1:
				//create orderID
				$orderID = $base->recursive_generator('o_orderID', null, 'orders');
				
				$dateTime = date('d-M-Y  H:i:s');
				$current_timestamp = time();

				$ext = substr($_FILES['bifoga_medgivande']['name'], strrpos($_FILES['bifoga_medgivande']['name'], '.'));			
				$attachmentName = $base->setFilename().$ext;
				if( 1==1 )
					move_uploaded_file($_FILES['bifoga_medgivande']['tmp_name'], 'attachments/'.$attachmentName );

				//create attachmentID for Medgivande
				$attachmentID = $base->recursive_generator('oa_attachmentID', 'file', 'orderattachments');

				//logging activity: upload medgivande 
				$insert_log = array('l_user' => $base->enc($_SESSION['email']),
									'l_logDescription' => $base->enc('Medgivande uploaded by '.$_SESSION['email'].PHP_EOL.'(Attachment ID: '.$attachmentID.')'),
									'l_logType' => $base->enc('upload_attachment'), 
									'l_accessLevel' => $base->enc('0'),
									'l_sourceFile' => $base->enc(BASE_URL.'/dashboard/index.php'),
									'l_dateTime' => $base->enc(date('d-M-Y H:i:s')));
				$base->insert('logs', $insert_log);

				$insert_array1 = array('oa_attachmentID' => $base->enc($attachmentID),
									   'oa_attachmentName' => $base->enc($attachmentName), 			
									   'oa_attachmentDesc' => $base->enc('Medgivande'),
									   'oa_attachmentLocation' => $base->enc(BASE_URL.'/dashboard/attachments/'.$attachmentName),
									   'o_orderID' => $base->enc($orderID),
									   'oa_dateTime' => $base->enc($dateTime),
									   'oa_dateTime_ts' => $base->enc($current_timestamp));


				$insert_array2 = array('o_orderID' => $base->enc($orderID), 		
									   'c_clientID' => $base->enc($_SESSION['clientID']), 			
									   'o_formLevel' => $base->enc('1'),
								       'o_name' => $base->enc(htmlspecialchars($_POST['namn'])),
									   'o_personnummer' => $base->enc(htmlspecialchars($_POST['personnummer'])),
									   'o_bifoga_medgivande' => $base->enc(BASE_URL.'/dashboard/attachments/'.$attachmentName),
									   'o_bifoga_cv' => '',
									   'o_invoice' => $base->enc(htmlspecialchars($_POST['invoice_info'])),
									   'o_orderDateTime' => $base->enc($dateTime),
									   'o_orderDateTime_ts' => $base->enc(time()),
									   'o_reportLanguage'=> $base->enc(htmlspecialchars($_POST['report_lang'])),
									   'o_orderStatus' => $base->enc('1'));

				$base->insert('orderattachments', $insert_array1);	

				//logging activity: order placed by client 
				$insert_log = array('l_user' => $base->enc($_SESSION['email']),
									'l_logDescription' => $base->enc('Order placed by '.$_SESSION['email'].PHP_EOL.'(Order ID: '.$orderID.')'),
									'l_logType' => $base->enc('order_placed'),
									'l_accessLevel' => $base->enc('0'),
									'l_sourceFile' => $base->enc(BASE_URL.'/dashboard/index.php'),
									'l_dateTime' => $base->enc(date('d-M-Y H:i:s')));
				$base->insert('logs', $insert_log);

				$base->insert('orders', $insert_array2);


				//send email to client with new order details		
				if(send_email($_SESSION['clientID'], $_SESSION['email']))
				{
					//upadate total orders and pending orders
					$total_orders = $base->getRowCount($conn2, 'orders', array('c_clientID'=>$base->enc($_SESSION['clientID'])));
					$pending_orders = $base->getRowCount($conn2, 'orders', array('c_clientID'=>$base->enc($_SESSION['clientID']), 'o_orderStatus'=>$base->enc('1')));
					$orders_progress = $base->getRowCount($conn2, 'orders', array('c_clientID'=>$base->enc($_SESSION['clientID']), 'o_orderStatus'=>$base->enc('2')));

					echo json_encode(array('response' => 1, 'total_orders' => $total_orders, 'pending_orders' => $pending_orders, 'orders_progress' => $orders_progress));
				}				
				break;


			//insert order details in table 'orders' and table 'orderattachments' (for Nivå 2) [dashboard.php]
			case 2:

				//create orderID
				$orderID = $base->recursive_generator('o_orderID', null, 'orders');
				
				$dateTime = date('d-M-Y  H:i:s');		
				$current_timestamp = time();

				$bifoga_medgivande_name = $_FILES['bifoga_medgivande']['tmp_name'];
				$bifoga_cv_name = $_FILES['bifoga_cv']['tmp_name'];

				$ext = substr($_FILES['bifoga_medgivande']['name'], strrpos($_FILES['bifoga_medgivande']['name'], '.'));			
				$attachmentName1 = $base->setFilename().$ext;

				$ext = substr($_FILES['bifoga_cv']['name'], strrpos($_FILES['bifoga_cv']['name'], '.'));			
				$attachmentName2 = $base->setFilename().$ext;

				if( 1==1 )
					move_uploaded_file($bifoga_medgivande_name, 'attachments/'.$attachmentName1);				

				//create attachmentID for Medgivande
				$attachmentID = $base->recursive_generator('oa_attachmentID', 'file', 'orderattachments');

				//logging activity: upload medgivande 
				$insert_log = array('l_user' => $base->enc($_SESSION['email']),
									'l_logDescription' => $base->enc('Medgivande uploaded by '.$_SESSION['email'].PHP_EOL.'(Attachment ID: '.$attachmentID.')'),
									'l_logType' => $base->enc('upload_attachment'),
									'l_accessLevel' => $base->enc('0'),
									'l_sourceFile' => $base->enc(BASE_URL.'/dashboard/index.php'),
									'l_dateTime' => $base->enc(date('d-M-Y H:i:s')));
				$base->insert('logs', $insert_log);

				$insert_array1 = array('oa_attachmentID' => $base->enc($attachmentID), 		
									   'oa_attachmentName' => $base->enc($attachmentName1),
									   'oa_attachmentDesc' => $base->enc('Medgivande'),
									   'oa_attachmentLocation' => $base->enc(BASE_URL.'/dashboard/attachments/'.$attachmentName1),
									   'o_orderID' => $base->enc($orderID),
									   'oa_dateTime' => $base->enc($dateTime),
									   'oa_dateTime_ts' => $base->enc($current_timestamp));

				$base->insert('orderattachments', $insert_array1);	

				if( 1==1 )
					move_uploaded_file($bifoga_cv_name, 'attachments/'.$attachmentName2);
				
				//create attachmentID for CV
				$attachmentID = $base->recursive_generator('oa_attachmentID', 'file', 'orderattachments');

				//logging activity: upload CV
				$insert_log = array('l_user' => $base->enc($_SESSION['email']),
									'l_logDescription' => $base->enc('CV uploaded by '.$_SESSION['email'].PHP_EOL.'(Attachment ID: '.$attachmentID.')'),
									'l_logType' => $base->enc('upload_attachment'), 
									'l_accessLevel' => $base->enc('0'), 
									'l_sourceFile' => $base->enc(BASE_URL.'/dashboard/index.php'),
									'l_dateTime' => $base->enc(date('d-M-Y H:i:s')));
				$base->insert('logs', $insert_log);

				$insert_array1 = array('oa_attachmentID' => $base->enc($attachmentID),
									   'oa_attachmentName' => $base->enc($attachmentName2),
									   'oa_attachmentDesc' => $base->enc('CV'),
									   'oa_attachmentLocation' => $base->enc(BASE_URL.'/dashboard/attachments/'.$attachmentName2),
									   'o_orderID' => $base->enc($orderID),
									   'oa_dateTime' => $base->enc($dateTime),
									   'oa_dateTime_ts' => $base->enc($current_timestamp));

				$base->insert('orderattachments', $insert_array1);	


				$insert_array2 = array('o_orderID' => $base->enc($orderID),
									   'c_clientID' => $base->enc($_SESSION['clientID']),
									   'o_formLevel' => $base->enc('2'),
									   'o_name' => $base->enc(htmlspecialchars($_POST['namn'])),
									   'o_personnummer' => $base->enc(htmlspecialchars($_POST['personnummer'])),
									   'o_bifoga_medgivande' => $base->enc(BASE_URL.'/dashboard/attachments/'.$attachmentName1),
									   'o_bifoga_cv' => $base->enc(BASE_URL.'/dashboard/attachments/'.$attachmentName2),
									   'o_invoice' => $base->enc(htmlspecialchars($_POST['invoice_info'])),
									   'o_orderDateTime' => $base->enc($dateTime),
									   'o_orderDateTime_ts' => $base->enc(time()),
									   'o_orderStatus' => $base->enc('1'),									   
									   'o_reportLanguage'=> $base->enc(htmlspecialchars($_POST['report_lang'])));				

				$base->insert('orders', $insert_array2);

				//logging activity: order placed by client 
				$insert_log = array('l_user' => $base->enc($_SESSION['email']),
									'l_logDescription' => $base->enc('Order placed by '.$_SESSION['email'].PHP_EOL.'(Order ID: '.$orderID.')'),
									'l_logType' => $base->enc('order_placed'), 
									'l_accessLevel' => $base->enc('0'), 
									'l_sourceFile' => $base->enc(BASE_URL.'/dashboard/index.php'),
									'l_dateTime' => $base->enc(date('d-M-Y H:i:s')));
				$base->insert('logs', $insert_log);


					

				//send email to client with new order details		
				send_email($_SESSION['clientID'], $_SESSION['email']);

				//upadate total orders and pending orders
				$total_orders = $base->getRowCount($conn2, 'orders', array('c_clientID'=>$base->enc($_SESSION['clientID'])));
				$pending_orders = $base->getRowCount($conn2, 'orders', array('c_clientID'=>$base->enc($_SESSION['clientID']), 'o_orderStatus'=>$base->enc('1')));
				$orders_progress = $base->getRowCount($conn2, 'orders', array('c_clientID'=>$base->enc($_SESSION['clientID']), 'o_orderStatus'=>$base->enc('2')));

				echo json_encode(array('response' => 1, 'total_orders' => $total_orders, 'pending_orders' => $pending_orders, 'orders_progress' => $orders_progress));
				break;
			

			//insert order details in table 'orders' and table 'orderattachments' (for Nivå 3) [dashboard.php]
			case 3:
		
				//create orderID
				$orderID = $base->recursive_generator('o_orderID', null, 'orders');
				
				$dateTime = date('d-M-Y  H:i:s');
				$current_timestamp = time();

				$insert_array = array('o_orderID' => $base->enc($orderID),
									   'c_clientID' => $base->enc($_SESSION['clientID']),
									   'o_formLevel' => $base->enc(htmlspecialchars($_POST['flag'])),
									   'o_request_description' => $base->enc(htmlspecialchars($_POST['request_description'])),
									   'o_contact_number' => $base->enc(htmlspecialchars($_POST['contact_number'])),
									   'o_email' => $base->enc(htmlspecialchars($_POST['email'])),
									   'o_invoice' => $base->enc(htmlspecialchars($_POST['invoice_info'])),
									   'o_reportLanguage'=> $base->enc(htmlspecialchars($_POST['report_lang'])),
									   'o_orderDateTime' => $base->enc($dateTime),
									   'o_orderDateTime_ts' => $base->enc(time()),
									   'o_orderStatus' => $base->enc('1'));

				$base->insert('orders', $insert_array);	

				//logging activity: order placed by client 
				$insert_log = array('l_user' => $base->enc($_SESSION['email']),
									'l_logDescription' => $base->enc('Order placed by '.$_SESSION['email'].PHP_EOL.'(Order ID: '.$orderID.')'),
									'l_logType' => $base->enc('order_placed'),
									'l_accessLevel' => $base->enc('0'),
									'l_sourceFile' => $base->enc(BASE_URL.'/dashboard/index.php'),
									'l_dateTime' => $base->enc(date('d-M-Y H:i:s')));
				$base->insert('logs', $insert_log);
					

				//send email to client with new order details		
				if(send_email($_SESSION['clientID'], $_SESSION['email']))
				{
					//upadate total orders and pending orders
					$total_orders = $base->getRowCount($conn2, 'orders', array('c_clientID'=>$base->enc($_SESSION['clientID'])));
					$pending_orders = $base->getRowCount($conn2, 'orders', array('c_clientID'=>$base->enc($_SESSION['clientID']), 'o_orderStatus'=>$base->enc('1')));
					$orders_progress = $base->getRowCount($conn2, 'orders', array('c_clientID'=>$base->enc($_SESSION['clientID']), 'o_orderStatus'=>$base->enc('2')));

					echo json_encode(array('response' => 1, 'total_orders' => $total_orders, 'pending_orders' => $pending_orders, 'orders_progress' => $orders_progress));
				}				
				break;


			//insert order details in table 'orders' and table 'orderattachments' (for Utomlands) [dashboard.php]
			case 4:
		
				//create orderID
				$orderID = $base->recursive_generator('o_orderID', null, 'orders');
				
				$dateTime = date('d-M-Y  H:i:s');
				$current_timestamp = time();

				$insert_array = array('o_orderID' => $base->enc($orderID),
									   'c_clientID' => $base->enc($_SESSION['clientID']),
									   'o_formLevel' => $base->enc(htmlspecialchars($_POST['flag'])),
									   'o_request_description' => $base->enc(htmlspecialchars($_POST['request_description'])),
									   'o_contact_number' => $base->enc(htmlspecialchars($_POST['contact_number'])),
									   'o_email' => $base->enc(htmlspecialchars($_POST['email'])),
									   'o_invoice' => $base->enc(htmlspecialchars($_POST['invoice_info'])),
									   'o_reportLanguage'=> $base->enc(htmlspecialchars($_POST['report_lang'])),
									   'o_orderDateTime' => $base->enc($dateTime),
									   'o_orderDateTime_ts' => $base->enc(time()),
									   'o_orderStatus' => $base->enc('1'));

				$base->insert('orders', $insert_array);	

				//logging activity: order placed by client 
				$insert_log = array('l_user' => $base->enc($_SESSION['email']),
									'l_logDescription' => $base->enc('Order placed by '.$_SESSION['email'].PHP_EOL.'(Order ID: '.$orderID.')'),
									'l_logType' => $base->enc('order_placed'),
									'l_accessLevel' => $base->enc('0'),
									'l_sourceFile' => $base->enc(BASE_URL.'/dashboard/index.php'),
									'l_dateTime' => $base->enc(date('d-M-Y H:i:s')));
				$base->insert('logs', $insert_log);
					

				//send email to client with new order details		
				send_email($_SESSION['clientID'], $_SESSION['email']);		

				//upadate total orders and pending orders
				$total_orders = $base->getRowCount($conn2, 'orders', array('c_clientID'=>$base->enc($_SESSION['clientID'])));
				$pending_orders = $base->getRowCount($conn2, 'orders', array('c_clientID'=>$base->enc($_SESSION['clientID']), 'o_orderStatus'=>$base->enc('1')));
				$orders_progress = $base->getRowCount($conn2, 'orders', array('c_clientID'=>$base->enc($_SESSION['clientID']), 'o_orderStatus'=>$base->enc('2')));

				echo json_encode(array('response' => 1, 'total_orders' => $total_orders, 'pending_orders' => $pending_orders, 'orders_progress' => $orders_progress));
				break;


			//change client password [settings.php]
			case 5:
				if(!empty($_POST['new_password']))
				{
					$pass = htmlspecialchars($_POST['new_password']);
					$pass = hash('sha256', $pass);
					$base->updateMQ('clients', array('c_password'=> $base->enc($pass)), 'c_clientID', $base->enc($_SESSION['clientID']));
					echo json_encode(array('response' => 1));
				}				
				break;


			//update 2faStatus [settings.php]
			case 6:
				//get all the data here first
				// $new2FAStatusVal = htmlspecialchars($_POST['newStatus']);
				// $c_clientID = htmlspecialchars($_POST['cid']);

				/*
				//update status now
				$base->updateMQ("clients", array("status2FA" => $base->enc($new2FAStatusVal)), "c_clientID", $base->enc($c_clientID));
				// echo json_encode(array('response' => 1));
				*/

				// try
				// {
				// 	$s = $conn1->prepare("UPDATE clients set status2FA = :s where c_clientID = :c");
				// 	$s->bindParam(":s", $new2FAStatusVal);
				// 	$s->bindParam(":c", $c_clientID);
				// 	$s->execute();
				// }
				// catch(PDOException $e)
				// {
				// 	echo $e->getMessage();
				// }
				break;


			//change client contact number [settings.php]
			case 7:
				$base->updateMQ('clients', array('c_contactNumber'=> $base->enc(htmlspecialchars($_POST['mobile']))), 'c_clientID', $base->enc($_SESSION['clientID']));
				echo json_encode(array('response' => 1));
				break;


			//manage users [manageUsers.php & manageClients.php]
			case 8:
				if(!empty($_POST['email']))
				{
					$email = $base->enc(htmlspecialchars($_POST['email']));
					try
					{
						$query = $conn1->prepare('DELETE FROM clients WHERE c_email=:c_email');
						$query->bindParam(':c_email', $email);
						$query->execute();
					}
					catch(PDOException $e)
					{
						$e->getMessage();
					}

					echo json_encode(array('response'=>1));
				}				
				break;


			//add new user [addUsers.php]
			case 9:
				if( $base->select('clients', 'c_email', array('c_email'=> $base->enc(htmlspecialchars($_POST['addUser_email']))))==false )
				{
					$clientID = $base->recursive_generator('c_clientID', null, 'clients');
					$password = bin2hex(random_bytes(7));
					
					$insert_array = array('c_clientID' => $base->enc($clientID), 		
										  'c_contactName' => $base->enc(htmlspecialchars($_POST['addUser_name'])),
										  'c_email' => $base->enc(htmlspecialchars($_POST['addUser_email'])), 		
										  'c_password' => $base->enc(hash('sha256' ,$password)), 	
										  'c_contactNumber' => $base->enc(htmlspecialchars($_POST['addUser_number'])),
										  'status2FA'=>$base->enc('1'), 
										  'c_is_parent' => $base->enc('1'), 
										  'c_parentID' => $base->enc($_SESSION['clientID']), 
										  'c_role' => $base->enc(htmlspecialchars($_POST['addUser_role'])), 
										  'c_registeredOn' => $base->enc(date('d-M-Y  H:i:s')));			
				
					$base->insert('clients', $insert_array);

					//send email to new user with login details		
					send_email_addUser(htmlspecialchars($_POST['addUser_email']), $password);

					//upadate total orders and pending orders
					echo json_encode(array('response' => 1));
				}
				else
					echo json_encode(array('response' => 0));		
				break;


			//download templates
			case 13:
				if(!empty($_POST['type']))
				{
					if($_POST['type']==1)
					{
						$file_doc=$base->getColumnValue('clients', 'cv_template_sw_doc', array('c_clientID'=>$base->enc($_SESSION['clientID'])));
						$file_pdf=$base->getColumnValue('clients', 'cv_template_sw_pdf', array('c_clientID'=>$base->enc($_SESSION['clientID'])));
						echo json_encode(array('response'=>1, 'file_doc'=>$file_doc, 'file_pdf'=>$file_pdf));
					}
					else if($_POST['type']==2)
					{
						$file_doc=$base->getColumnValue('clients', 'cv_template_en_doc', array('c_clientID'=>$base->enc($_SESSION['clientID'])));
						$file_pdf=$base->getColumnValue('clients', 'cv_template_en_pdf', array('c_clientID'=>$base->enc($_SESSION['clientID'])));
						echo json_encode(array('response'=>1, 'file_doc'=>$file_doc, 'file_pdf'=>$file_pdf));
					}
				}
				break;

			default:				
				break;
		}
	}


	//function to send email to admin about new order [index.php]
	function send_email($clientID, $clientEmail)
    {	
    	global $base;

		$mail = new PHPMailer(true);                     // Passing `true` enables exceptions
		try 
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

			$search = array('(heading)', '(text1)', '(text2)', '(button_href)', '(button)', '(logo)');
			$replace = array('New order on Vesper Group.',
							 'A new order has been placed on Vesper Group.',
							 "Client Email - {$clientEmail}<br/>Client ID - {$clientID}",
							 BASE_URL.'/login',
							 'LOGGA IN',
							 BASE_URL.'/onboard/image/logo.png');
			//sender info
			$mail->setFrom('request@vesper.group');

			//fetching admin email from table 'adminsettings'
			$query = $base->select('adminsettings', 'admin_email', array('index_id'=>1));
			$sendEmailTo = $base->dec($query->admin_email);
			
			//create log
			$insert_array = array( 'l_user' => $base->enc($_SESSION['email']),
								   'l_logDescription' => $base->enc("New order details e-mailed to ".$sendEmailTo),
								   'l_logType' => $base->enc('email_newOrder'),
								   'l_accessLevel' => $base->enc('1'),
								   'l_sourceFile' => $base->enc(BASE_URL.'/dashboard/index.php'),
								   'l_dateTime' => $base->enc(date('d-M-Y H:i:s')));
			$base->insert('logs', $insert_array);


			//Recipients
			$mail->addAddress($sendEmailTo);      

			//Content
			$mail->isHTML(true);                         // Set email format to HTML
			$mail->CharSet = "UTF-8";
			$mail->Subject = 'New order on Vesper Group.';
			$mail->Body = str_replace($search, $replace, $content);

			return $mail->send();
		}
		catch (Exception $e) 
		{
			return $mail->ErrorInfo;
		}
	}


	//function to send email to new user [addUsers.php]
	function send_email_addUser($email, $password)
    {	
    	global $base;

		$mail = new PHPMailer(true);                     // Passing `true` enables exceptions
		try 
		{

			$content = '<!DOCTYPE html>
							<html>
								<head>
									<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
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

			$search = array('(heading)', '(text1)', '(text2)', '(button_href)', '(button)', '(logo)');
			$replace = array('Welcome to Vesper Group.', 
							 'Your profile has been created on Vesper Group.',
							 "You can log in using these credentials:<br/>Email - {$email}<br/>Password - {$password}",
							 BASE_URL.'/login',
							 'LOGIN',
							 BASE_URL.'/onboard/image/logo.png');

			//create log
			$insert_array = array( 'l_user' => $base->enc($_SESSION['email']),
								   'l_logDescription' => $base->enc("Login details e-mailed to new user ".$email),
								   'l_logType' => $base->enc('email_newUser'),
								   'l_accessLevel' => $base->enc('1'),
								   'l_sourceFile' => $base->enc(BASE_URL.'/dashboard/addUsers.php'),
								   'l_dateTime' => $base->enc(date('d-M-Y H:i:s')));
			$base->insert('logs', $insert_array);

			//Recipients
			$mail->setFrom('request@vesper.group');
			$mail->addAddress($email);

			//Content
			$mail->isHTML(true);                         // Set email format to HTML
			$mail->CharSet = "UTF-8";
			$mail->Subject = 'Welcome to Vesper Group';
			$mail->Body = str_replace($search, $replace, $content);

			return $mail->send();
		}
		catch (Exception $e) 
		{
			return $mail->ErrorInfo;
		}
	}
?>