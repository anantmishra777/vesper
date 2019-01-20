<?php
	session_start();

	//redirect to login page if admin isn't logged in
	if( !isset($_SESSION['admin_email']) )
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

	//fetching admin details
	$adminInfo = $base->select('adminaccounts', '*', array('ac_adminID'=> $base->enc($_SESSION['adminID'])));
?>

<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">	
		<meta name="description" content="">
		<meta name="viewport" content="width=device-width">
		<title id="title_settings">Settings - Vesper Group</title>
		<link rel="stylesheet" href="../theme/css/bootstrap.css">
		<link rel="stylesheet" href="../theme/css/bootstrap-responsive.css">
		<link rel="stylesheet" href="../theme/css/jquery.fancybox.css">
		<link rel="stylesheet" href="../theme/css/style.css">		
		<style>
			td
			{
				padding-right: 10px;
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
						<a href="adminSettings.php">Admin Settings</a>
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

				<!-- Success message -->
				<div class="success_message4" id="message_password_changed">
					Password changed.
				</div>

				<div class="success_message3" id="message_changes_saved">
					Changes saved.
				</div>

				<div class="success_message5" id="2fa_status">
					2FA Status Successfully Changed!
				</div>

				<div class="content">
					<div class="row-fluid" >
						<div class="span6">
							<div class="box">
								<div class="box-head">
									<h3 id="title_settings">Settings</h3>
								</div>
								<div class="box-content">
									<!--Change Settings Form-->
									<form name="settings_form" id="settings_form">

										<!-- CSRF token -->
										<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>"> 
										
										<!-- Flag -->
										<input type="hidden" name="flag" value="6"> 

										<!-- fetching data from table 'adminSettings' -->
										<?php
											$query = $base->select('adminSettings', '*', array('index_id' =>1 ));
										?>

										<table>
											<tr>
												<td><label for="file_deletion_date" id="l_deletion_date">File Deletion Duration</label></td>
												<td><input class='form-control' type="number" name="file_deletion_date" id="file_deletion_date" value="<?php echo htmlspecialchars($base->dec($query->file_deletion_date)); ?>" required/>Days</td>
											</tr>
											
											<!-- <tr>
												<td><label for="medgivande_sample" id="l_medgivande_sample">CV Template </label></td>
												<td><input class='form-control' type="text" name="medgivande_sample" id="medgivande_sample" value="<?php //echo htmlspecialchars(basename($query->medgivande_sample)); ?>" readonly/></td>
												<td><input type="file" name="medgivande_sample_upload" id="medgivande_sample_upload" /></td>
											</tr> -->

											<tr>
												<td><button class="btn btn-red5" name="settings_btn" id="settings_btn">Update Settings</button></td>
											</tr>													
										</table>
									</form>									
								</div>
							</div>

							<!-- Change Contact Number -->
							<div class="row-fluid">
		                        <div class="span6">
		                           <div class="box">
										<div class="box-head">
											<h3 id="l_contact_number">Contact Number</h3>
										</div>
										<div class="box-content">
											<!--Change Password Form-->
											<form name="change_contact_number" id="change_contact_number">

												<!-- CSRF token -->
												<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>"> 
												
												<!-- Flag -->
												<input type="hidden" name="flag" value="10">

												<table>
													<tr>
														<td><label for="mobile" id="mobile_number">Mobile Number</label></td>
														<td><input class='form-control' type="text" name="mobile" id="mobile" value="<?php echo htmlspecialchars($base->dec($adminInfo->ac_contactNumber)); ?>" required/></td>
													</tr>

													<tr>
														<td><button class="btn btn-red5">Update</button></td>
													</tr>
												</table>
											</form>
										</div>
									</div>
		                        </div>
		                    </div>

							<div class="box">
								<div class="box-head">
									<h3 id="h_change_pwd">Change Password</h3>
								</div>
								<div class="box-content">
									<!--Change Password Form-->
									<form name="change_password_form" id="change_password_form">

										<!-- CSRF token -->
										<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>"> 
										
										<!-- Flag -->
										<input type="hidden" name="flag" value="9"> 

										<table>	
											<tr>
												<td><label for="new_password" id="new_pwd">New Password</label></td>
												<td><input class='form-control' type="password" name="new_password" id="new_password" required/></td>
											</tr>										

											<tr>
												<td><label for="confirm_password" id="confirm_pwd">Confirm Password</label></td>
												<td><input class='form-control' type="password" name="confirm_password" id="confirm_password" required/></td>
											</tr>

											<tr>
												<td><button class="btn btn-red5" name="change_password_btn" id="change_password_btn">Submit</button></td>
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
		<script src="../theme/js/jquery.cookie.js"></script>
		<script src="../theme/js/custom.js"></script>
		<script src="../theme/js/demo.js"></script>
		<script src="../theme/js/main.js"></script>
	</body>
	<script>
		$('#li_adminSettings').addClass('active');		
	</script>	
</html>