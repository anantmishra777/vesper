<?php
	session_start();

	//redirect to login page if client isn't logged in
	if( !isset($_SESSION['email']) )
		header('Location: ../logout');
	
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

	//is parent validation
	$client_ = $base->select('clients', '*', array('c_clientID' => $base->enc($_SESSION['clientID'])));
	if( $client_->c_is_parent==1 )
	{
		header('Location: ../logout');
		die();
	}
?>



<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">	
		<meta name="description" content="">
		<meta name="viewport" content="width=device-width">
		<title id="title_manage_users">Manage Users - Vesper Group</title>
		<link rel="stylesheet" href="../theme/css/bootstrap.css">
		<link rel="stylesheet" href="../theme/css/bootstrap-responsive.css">
		<link rel="stylesheet" href="../theme/css/jquery.fancybox.css">
		<link rel="stylesheet" href="../theme/css/style.css">
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
						<a href="manageUsers.php">Hantera användare</a>
					</li>
				</ul>
			</div>
		</div>
		<!-- End Second Topbar -->

		<!-- CSRF token -->
		<input type="hidden" name="csrf_token" id="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

		<div class="main">
			<div class="container-fluid">

				<!--Sidebar-->
				<?php include 'sidebar2.php'; ?>
				<!--End Sidebar-->

				<div class="content">				
		       		<!-- My orders table -->
		       		<div class="row-fluid">
						<div class="span6">
							<div class="box">
								<div class="box-head">
									<h3>Hantera användare</h3>
								</div>
								<div class="box-content box-nomargin">									
									<table class="table table-striped table-bordered" id="manageUsers_table">
										<thead>
											<tr style="width: auto;">
												<th><strong>Namn</strong></th>
												<th><strong>Epost</strong></th>
												<th><strong>Telefonnummer</strong></th>
												<th><strong id="registered_on">Registrerad</strong></th>	
												<th><strong>Åtgärd</strong></th>
											</tr>
										</thead>
										<tbody>
											<!-- Fetch rows from table 'clients' -->
											<?php	
												try
												{
													$query = $conn2->prepare('SELECT * FROM clients WHERE c_parentID=:c_parentID');
													$query->bindParam(':c_parentID', $client_->c_clientID);
													$query->execute();
												}
												catch(PDOException $e)
												{
													$e->getMessage();
												}

												$row = $query->fetchAll(PDO::FETCH_ASSOC);

												for($i=0; $i<$query->rowCount(); $i++)
												{
													$id = $row[$i]['c_indexid'];
													?>
													<tr>
														<td>
															<?php echo htmlspecialchars($base->dec($row[$i]['c_contactName'])); ?>	
														</td>

														<td>
															<?php echo htmlspecialchars($base->dec($row[$i]['c_email'])); ?>
														</td>

														<td >
															<?php echo htmlspecialchars($base->dec($row[$i]['c_contactNumber'])); ?>
														</td>

														<td >
															<?php echo htmlspecialchars($base->dec($row[$i]['c_registeredOn'])); ?>
														</td>

														<td class='actions'>		
															<div class="btn-group">
																<button class='remove_user' id="<?php echo htmlspecialchars('user_'.$base->dec($row[$i]['c_email'])); ?>">Remove</button>
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
		$('#li_addUsers').addClass('active');
		$('.collapsed-nav').css('display', 'block');
	</script>	
</html>