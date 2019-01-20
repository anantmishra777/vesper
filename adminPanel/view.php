<?php
	session_start();

	//redirect to login page if client isn't logged in
	// if( !isset($_SESSION['admin_email']) )
	// 	header('Location: ../logout');
	
    //set language
    if( !isset($_SESSION['language']) )
        $_SESSION['language']='en';
    
	//creating CRSF token if not already set
	if( !isset($_SESSION['csrf_token']) )
		$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

	//connect database
	include '../class/dbconnector1.php';
	include '../class/dbconnector2.php';

	//including mysql functions file
    include '../class/class.base.php';
    $base = new base();

    //log errors
	ini_set("log_errors", 1);
	ini_set("error_log", "error.log");

	//get order ID now
	$orderID = isset($_GET['orderID']) ? $_GET['orderID'] : '';
	if(!$base->field_exists("o_orderID", $base->enc($orderID), "orders"))
	{
		//orderID doesn't exist, redirect to homepage
		header("Location:../adminPanel");
	}


	//control reached here, means orderID exists, fetch details about the order and client
	$orderDetails = $base->select("orders", "*", array("o_orderID" => $base->enc($orderID)));
	$clientDetails = $base->select("clients", "*", array("c_clientID" => $orderDetails->c_clientID));
?>


<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title id="title_admin_panel">Admin Panel - Vesper Group</title>
		<meta name="description" content="">
		<meta name="viewport" content="width=device-width">
		<link rel="stylesheet" href="../theme/css/bootstrap.css">
		<link rel="stylesheet" href="../theme/css/bootstrap-responsive.css">
		<link rel="stylesheet" href="../theme/css/jquery.fancybox.css">
		<link rel="stylesheet" href="../theme/css/style.css" media="all">

		<!-- Include CSS for icons. -->
		<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.4.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
		<!-- Include Editor style. -->
		<link href="https://cdnjs.cloudflare.com/ajax/libs/froala-editor/2.5.1/css/froala_editor.pkgd.min.css" rel="stylesheet" type="text/css" />
		<link href="https://cdnjs.cloudflare.com/ajax/libs/froala-editor/2.5.1/css/froala_style.min.css" rel="stylesheet" type="text/css" />
		<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
		<style>
			p
			{
				margin-bottom: 20em;
			}

			.fr-toolbar
			{
				margin-top: 3em !important;
			}

			#send_email_form td
			{
				padding-top: 15px;
			}

			#followUp_date
			{
				width: 122px !important; 
			}

			.userprofile
			{
				margin-left: 0 !important;
			}

			#set_followUp_date_btn
			{
				margin-bottom: 7px;
				padding: 1px 7px;
			}

			#ui-datepicker-div
			{
				z-index: 5 !important;
			}
		</style>
	</head>
	<body class="position1">
		<!--Topbar-->
		<?php include 'topbar.php'; ?>
		<!--End Topbar-->	
			
		<!-- Second Topbar -->
		<div class="breadcrumbs">
			<div class="container-fluid">
				<ul class="bread pull-left">
					<li>
						<a href="../adminPanel"><i class="icon-home icon-white"></i></a>
					</li>
					<li >
						<a href="view.php">Browse Order</a>
					</li>
				</ul>
			</div>
		</div>
		<!-- End Second Topbar -->


		<!-- Success message -->
		<div class="success_message3" id="email_sent">
			Email Sent!
		</div>	

		<!-- Success message -->
		<div class="success_message4">
			Changes saved.
		</div>

		<div class="main" >
			<div class="container-fluid">
				<!--Sidebar-->
				<?php include 'sidebar.php'; ?>
				<!--End Sidebar-->

				<div class="content">
					<div class="row-fluid">
						<div class="span12">
							<div class="box">
								<div class="box-head">
									<h3 id="order_data">Order Data</h3>
								</div>
								<div class="box-content">
									<div class="cl">
										<div class="details userprofile">
											<h3 class='divide' id="customer_info">Customer Information</h3>
											<table class="table table-striped table-detail">
												<tr>
													<th><strong id="client_id">Client ID: </strong></th>
													<td><?php echo htmlspecialchars($base->dec($clientDetails->c_clientID)); ?></td>
												</tr>
												<tr>
													<th><strong id="company_name">Company Name: </strong></th>
													<td><?php echo htmlspecialchars($base->dec($clientDetails->c_companyName)); ?></td>
												</tr>
												<tr>
													<th><strong id="contact_person">Contact Person: </strong></th>
													<td><?php echo htmlspecialchars($base->dec($clientDetails->c_contactName)); ?></td>
												</tr>
												<tr>
													<th><strong id="contact_email">Contact Email: </strong></th>
													<td><?php echo htmlspecialchars($base->dec($clientDetails->c_email)); ?></td>
												</tr>										
											</table>
										</div>
									</div>
									<h3 class='divide' id="order_info">Order Information</h3>

									<form id="set_followUp_date" name="set_followUp_date">
										<table class="table table-striped table-detail">
											<tr>
												<th><strong id="th_orderID">Order ID</strong></th>
												<td><?php echo htmlspecialchars($base->dec($orderDetails->o_orderID)); ?></td>
											</tr>

											<?php
												if( $base->dec($orderDetails->o_formLevel)<3 )
												{
													?>
													<tr>
														<th><strong id="country">Country:</strong></th>
														<td>Sverige</td>
													</tr>
													<tr>
														<th><strong>Namn:</strong></th>
														<td><?php echo htmlspecialchars($base->dec($orderDetails->o_name)); ?></td>
													</tr>
													<tr>
														<th><strong id="personnummer">Personnummer:</strong></th>
														<td><?php echo htmlspecialchars($base->dec($orderDetails->o_personnummer)); ?></td>
													</tr>
													<tr>
														<th><strong>Medgivande:</strong></th>
														<td>
															<a href="download.php?url=<?php echo htmlspecialchars($base->dec($orderDetails->o_bifoga_medgivande)); ?>">Download</a>
														</td>
													</tr>

													<?php 
														if( $base->dec($orderDetails->o_bifoga_cv)!='' )
														{
															?>
															<tr>
																<th>CV:</th>
																<td>
																	<a href="download.php?url=<?php echo htmlspecialchars($base->dec($orderDetails->o_bifoga_cv)); ?>">Download</a>
																</td>
															</tr>
															<?php
														}
												}
												else
												{
													?>
													<tr>
														<th id="country">Country:</th>
														<td><?php echo htmlspecialchars(($base->dec($orderDetails->o_formLevel) == 3 ? 'Sverige' : 'Utomlands')); ?></td>
													</tr>
													<tr>
														<th id="request_description">Request Description:</th>
														<td><?php echo htmlspecialchars($base->dec($orderDetails->o_request_description)); ?></td>
													</tr>
													<tr>
														<th id="l_contact_number">Contact Number:</th>
														<td><?php echo htmlspecialchars($base->dec($orderDetails->o_contact_number)); ?></td>
													</tr>
													<tr>
														<th id="email">Email:</th>
														<td><?php echo htmlspecialchars($base->dec($orderDetails->o_email)); ?></td>
													</tr>
													<?php
												}
											?>

											<tr>
												<th id="th_order_date">Order Date:</th>
												<td>
													<?php
														$o_d = $base->dec($orderDetails->o_orderDateTime);
														echo htmlspecialchars(substr($o_d, 0, strpos($o_d, ' ')));
													?>
												</td>
											</tr>
											<tr>
												<th id="th_current_status">Current Status: </th>
												<td>
													<?php
														switch($base->dec($orderDetails->o_orderStatus))
														{
															case 1:
																echo '<span class="label label-info">Pending</span>';
																break;
															case 2:
																echo '<span class="label label-warning">In Progress</span>';
																break;
															case 3:
																echo '<span class="label label-success">Completed</span>';
																break;
														}
													?>
												</td>
											</tr>

											<tr>
												<th>Current Follow-up Date:</th>
												<td id="current_followUp_date"><?php echo htmlspecialchars($base->dec($orderDetails->o_followUp_date)); ?></td>
											</tr>

									
											<tr>
												<!-- CSRF token -->
												<input type="hidden" name="csrf_token" id="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
												
												<!-- Set flag -->
												<input type="hidden" name="flag" value="11">

												<!-- Order ID -->
												<input type="hidden" name="orderID_" value="<?php echo htmlspecialchars($base->dec($orderDetails->o_orderID)); ?>">

												<th id="l_followUp_date">Change Follow-up Date: </th>
												<td>
													<input type="text" name="followUp_date" id="followUp_date" size="10" required>
													<button type="submit" class="btn btn-primary" id="set_followUp_date_btn" name="set_followUp_date_btn">Submit</button>
												</td>
											</tr>
										</table>
									</form>						

									<h3 class="divide" style="margin-top: 2.5em;">Email</h3>
									<form name="send_email_form" id="send_email_form" >
										<!-- CSRF token -->
										<input type="hidden" name="csrf_token" id="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
										
										<!-- Set flag -->
										<input type="hidden" name="flag" value="7">
											
										<!-- Order ID -->
										<input type="hidden" name="orderID" id="orderID" value="<?php echo htmlspecialchars($_GET['orderID']); ?>">

										<table name='send_email_table' id="send_email_table">
											<tr>
												<td><label for='email_client' id="l_send_email_to">Send to: </label></td>
												<td><input type="text" name="send_email_to" id="send_email_to" value="<?php echo htmlspecialchars($base->dec($clientDetails->c_email)); ?>" required></td>
											</tr>		
											<tr>
												<td><label for='send_email_subject' id="l_subject">Subject: </label></td>
												<td><input type="text" name="send_email_subject" id="send_email_subject" required></td>
											</tr>	
											<tr>
												<td><label for='upload_sample_report' id="l_sample_report">Sample Report: </label></td>
												<td><input type="file" name="upload_sample_report" id="upload_sample_report" ></td>
											</tr>	
											<tr>
												<td><label for='upload_full_report' id="l_full_report">Full Report: </label></td>
												<td><input type="file" name="upload_full_report" id="upload_full_report" ></td>
											</tr>	

											<tr>
												<td><label for="change_order_status" id="th_current_status">Current Status: </label></td>
												<td>
													<select name="change_order_status" id="change_order_status" required>
														<option value="1">Pending</option>
														<option value="2">In Progress</option>
														<option value="3">Completed</option>
													</select>
												</td>
											</tr>
											<tr>
												<textarea id="send_email_body" name='send_email_body'></textarea>
											</tr>							
											<tr>
												<td>
													<button type="submit" class="btn btn-primary" id="send_email_btn" name="send_email_btn">Send Email</button>
												</td>
											</tr>	
										</table>
									</form>									
								</div>
							</div>
						</div>
					</div>
				</div>	
			</div>
		</div>

		<!--Footer-->
		<?php include 'footer.php'; ?>	

		<script src="../theme/js/jquery.js"></script>
		<script src="../theme/js/less.js"></script>
		<script src="../theme/js/bootstrap.min.js"></script>
		<script src="../theme/js/jquery.peity.js"></script>
		<script src="../theme/js/jquery.fancybox.js"></script>
		<script src="../theme/js/jquery.flot.js"></script>
		<script src="../theme/js/jquery.color.js"></script>
		<script src="../theme/js/jquery.flot.resize.js"></script>
		<script src="../theme/js/jquery.cookie.js"></script>
		<script src="../theme/js/custom.js"></script>
		<script src="../theme/js/demo.js"></script>		
		<script src="../theme/js/main.js"></script>		

		<!-- Include jQuery lib. -->
		<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>

		<!-- Include Editor JS files. -->
		<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/froala-editor/2.5.1//js/froala_editor.pkgd.min.js"></script>

		<!-- Date Picker -->		
		<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

		<!-- Initialize the editor. -->
		<script>
		    $('textarea').froalaEditor();
		    $('.fr-placeholder').html('Write Email Here'); 	

		    $(document).ready(function()
		    {
		    	$("#followUp_date").datepicker();
			    $("#followUp_date").datepicker( "option", "dateFormat", 'dd-M-yy' );			    
			});
		</script>
	</body>
</html>