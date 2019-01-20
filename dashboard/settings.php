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

	//fetching client details
	$client_ = $base->select('clients', '*', array('c_clientID'=> $base->enc($_SESSION['clientID'])));


	$_SESSION['uploaded_file_names']='';
?>



<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">	
		<meta name="description" content="">
		<meta name="viewport" content="width=device-width">
		<title>Dashboard - Vesper Group</title>
		<link rel="stylesheet" href="../theme/css/bootstrap.css">
		<link rel="stylesheet" href="../theme/css/bootstrap-responsive.css">
		<link rel="stylesheet" href="../theme/css/jquery.fancybox.css">
		<link rel="stylesheet" href="../theme/css/style.css">

		<!-- blueimp Gallery styles -->
		<link rel="stylesheet" href="https://blueimp.github.io/Gallery/css/blueimp-gallery.min.css">
		<!-- CSS to style the file input field as button and adjust the Bootstrap progress bars -->
		<link rel="stylesheet" href="../theme/css/jquery.fileupload.css">
		<link rel="stylesheet" href="../theme/css/jquery.fileupload-ui.css">		
		<style>
			td
			{
				padding: 5px;
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
						<a href="settings.php">Inställningar</a>
					</li>
				</ul>
			</div>
		</div>
		<!-- End Second Topbar -->

		<!-- Success message -->
		<div class="success_message3">
			Changes saved.
		</div>
		<div class="success_message4" id="2fa_status">
			2FA Status Successfully Changed!
		</div>		

		<div class="main">
			<div class="container-fluid">

				<!--Sidebar-->
				<?php include 'sidebar2.php'; ?>
				<!--End Sidebar-->			

				<div class="content">		
					<!-- Change Password -->
					<div class="row-fluid">
                        <div class="span6">
                           <div class="box">
								<div class="box-head">
									<h3>Ändra lösenord</h3>
								</div>
								<div class="box-content">
									<!--Change Password Form-->
									<form name="change_client_password_form" id="change_client_password_form">

										<!-- CSRF token -->
										<input type="hidden" name="csrf_token" id="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>"> 
										
										<!-- Flag -->
										<input type="hidden" name="flag" id="flag" value="5"> 

										<table>	
											<tr>
												<td><label for="new_password">Nytt lösenord</label></td>
												<td><input class='form-control' type="password" name="new_password" id="new_password" required/></td>
											</tr>

											<tr>
												<td><label for="confirm_password">Bekräfta lösenord</label></td>
												<td><input class='form-control' type="password" name="confirm_password" id="confirm_password" required/></td>
											</tr>

											<tr>
												<td><button class="btn btn-red5" name="change_client_password_btn" id="change_client_password_btn">Skicka</button></td>
											</tr>											
										</table>
									</form>									
								</div>
							</div>
                        </div>
                    </div>

                    <!-- Change Contact Number -->
					<div class="row-fluid">
                        <div class="span6">
                           <div class="box">
								<div class="box-head">
									<h3>Kontaktnummer</h3>
								</div>
								<div class="box-content">
									<!--Change Password Form-->
									<form name="change_mobile_form" id="change_mobile_form">

										<!-- CSRF token -->
										<input type="hidden" name="csrf_token" id="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>"> 
										
										<!-- Flag -->
										<input type="hidden" name="flag" id="flag" value="7"> 

										<table>	
											<tr>
												<td><label for="mobile">Mobilnummer</label></td>

												<td><input class='form-control' type="text" name="mobile" id="mobile" value="<?php echo htmlspecialchars($base->dec($client_->c_contactNumber)); ?>" required/></td>
											</tr>

											<tr>
												<td><button class="btn btn-red5" name="change_mobile_btn" id="change_mobile_btn">Uppdatering</button></td>
											</tr>													
										</table>
									</form>									
								</div>
							</div>
                        </div>
                    </div>                    
				</div><!-- content end -->
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
		<!-- <script src="../theme/js/demo.js"></script> -->
		<script src="../theme/js/main.js"></script>
	</body>
	<script>
		$('#li_settings').addClass('active');
	</script>
</html>