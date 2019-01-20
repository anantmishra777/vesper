<?php
	session_start();

	//redirect to login page if admin isn't logged in
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

	//include config file
	include '../class/config.php';

	//including mysql functions file
    include '../class/class.base.php';
    $base = new base();

    //log errors
	ini_set("log_errors", 1);
	ini_set("error_log", "error.log");

	function dec($string)
	{
		return openssl_decrypt($string, 'AES-256-CBC', '48f5d1ba295d17e6ecc0cd508b6a242c501f1aff', true, '48f5d1ba295d17e6');
	}
?>



<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">	
		<meta name="description" content="">
		<meta name="viewport" content="width=device-width">
		<title id="title_admin_panel">Admin Panel - Vesper Group</title>
		<link rel="stylesheet" href="../theme/css/bootstrap.css">
		<link rel="stylesheet" href="../theme/css/bootstrap-responsive.css">
		<link rel="stylesheet" href="../theme/css/jquery.fancybox.css">
		<link rel="stylesheet" href="../theme/css/style.css">		
	</head>
	<body class="position1">		
		<?php
			if( isset($_GET['token']) )
				echo '<input type="hidden" name="addClient_token" id="addClient_token" value="1">';
		?>

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
						<a href="addClient.php">Add Client</a>
					</li>
				</ul>
			</div>
		</div>
		<!-- End Second Topbar -->

		<div class="main">
			<div class="container-fluid">

				<!--Sidebar-->
				<?php include 'sidebar.php'; ?>
				<!--End Sidebar-->

				<div class="content">

					<!-- Pending Onboard Requests Table -->
					<div class="row-fluid">
						<div class="span6">
							<div class="box">
								<div class="box-head">
									<h3 id="pending_requests">Pending Onboard Requests</h3>
								</div>
								<div class="box-content box-nomargin">									
									<table class="table table-striped table-bordered" id="pending_requests_table">
										<thead>
											<tr>
												<th><strong id="th_company_name">COMPANY NAME</strong></th>
												<th><strong id="th_contact_name">CONTACT NAME</strong></th>
												<th><strong id="th_email">EMAIL</strong></th>
												<th><strong id="th_contact_number">CONTACT NUMBER</strong></th>
												<th><strong id="th_date">DATE</strong></th>
												<th><strong id="th_actions">ACTIONS</strong></th>												
											</tr>
										</thead>
										<tbody>
											<!-- Fetch rows from table 'temp' -->
											<?php
												try
												{
													$query = $conn2->query('SELECT * FROM temp');
													$query->execute();
												}
												catch(PDOException $e)
												{
													$e->getMessage();
												}

												$row = $query->fetchAll(PDO::FETCH_ASSOC);

												for($i=0; $i<$query->rowCount(); $i++)
												{
													$id = $row[$i]['index_id'];
													?>
													<tr>
														<td>
															<?php echo htmlspecialchars(dec($row[$i]['company_name'])); ?>	
														</td>

														<td>
															<?php echo htmlspecialchars(dec($row[$i]['contact_name'])); ?>
														</td>

														<td >
															<?php echo htmlspecialchars(dec($row[$i]['email'])); ?>
														</td>

														<td >
															<?php echo htmlspecialchars(dec($row[$i]['contact_number'])); ?>
														</td>														
														
														<td>
															<?php echo htmlspecialchars(dec($row[$i]['date'])); ?>
														</td>

														<td class='actions'>
															<div class="btn-group">
																<a id="add_client_<?php echo htmlspecialchars($id); ?>" class='color_blue'>Add</a>
																<span> or </span>
																<a id="remove_client_<?php echo htmlspecialchars($id); ?>" class='color_red'>Remove</a>
															</div>
														</td>
													</tr>
													<?php
												}
											?>																						
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
					<!-- End Pending Onboard Requests Table -->

					<!--Add Client Form-->
					<div class="row-fluid" id="div_add_client">
						<div class="span12">
							<div class="box">
								<div class="box-head"><h3 id="h_add_client">Add Client</h3></div>
								<div class="box-content">
									<form name="add_client_form" id="add_client_form">
										<input type="hidden" name="index_id" id="index_id" value="">
										<input type="hidden" name="flag" id="flag" value="4">
										<!-- CSRF token -->
										<input type="hidden" name="csrf_token" id="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>"> 
										<table>
											<tr>
												<td><label for="company_name" id="l_company_name">Company Name</label></td>
												<td><input class='form-control' type="text" name="company_name" id="company_name" required /></td>
											</tr>
											
											<tr>
												<td><label for="contact_name" id="l_contact_name">Contact Name</label></td>
												<td><input class='form-control' type="text" name="contact_name" id="contact_name" required/></td>
											</tr>

											<tr>
												<td><label for="email" id="l_email">Email</label></td>
												<td><input class='form-control' type="text" name="email" id="email" required/></td>
											</tr>

											<tr>
												<td><label for="contact_number" id="l_contact_number">Contact Number</label></td>
												<td><input class='form-control' type="text" name="contact_number" id="contact_number" required/></td>
											</tr>			
											
											<tr>
												<td><button class="btn btn-red5" name="add_client_btn" id="add_client_btn">Add</button></td>
											</tr>																					
										</table>
									</form>									
								</div>
							</div>
						</div>
					</div>
					<!--End Add Client Form-->
					
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
		<script src="../theme/js/jquery.cookie.js"></script>
		<script src="../theme/js/custom.js"></script>
		<script src="../theme/js/demo.js"></script>
		<script src="../theme/js/main.js"></script>
	</body>
	<script>
		$('#li_clients').addClass('active');
		$('#collapsed_nav_clients').css('display', 'block');
	</script>	
</html>