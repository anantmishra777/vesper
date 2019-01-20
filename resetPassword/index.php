<?php
	session_start();	

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

	if( !isset($_GET['token']) || !strlen($_GET['token']) || $base->getRowCount($conn2, 'tokens', array('token' => $base->enc($_GET['token'])))==0 )
	{
		header('Location: ../logout');
		die();
	}	
?>

<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>Reset Password - Vesper Group</title>
		<meta name="description" content="">

		<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no" />

		<link rel="stylesheet" href="../theme/css/bootstrap.css">
		<link rel="stylesheet" href="../theme/css/jquery.fancybox.css">
		<link rel="stylesheet" href="../theme/css/login.css">
		<style>
			input[type='password']
			{
				width: 180px !important;
			}
			label
			{
				width: 125px !important;
			}
			a
			{
				text-decoration: none;
			}
			a:hover
			{
				text-decoration: underline;
			}
		</style>
	</head>
	<body class='login_body'>
		<div class="wrap" id="login_wrap">
			<h2 style="margin-top: 20px;">Reset Password</h2>
			<form autocomplete="off" class="validate" id="reset_password_form" name="reset_password_form">
				<!-- csrf token -->
				<input type="hidden" name="csrf_token" id="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">				
				
				<!-- Token -->
				<input type="hidden" name="token" id="token" value="<?php echo htmlspecialchars($_GET['token']); ?>">		

				<div class="login">
					<div class="email">
						<label for="new_password">New Password</label>
						<div class="control-group">
							<div class="input-prepend">
								<input type="password" id="new_password" name="new_password" class="{required:true}">
							</div>
						</div>						
					</div>

					<div class="email">
						<label for="confirm_password">Confirm Password</label>
						<div class="control-group">
							<div class="input-prepend">
								<input type="password" id="confirm_password" name="confirm_password" class="{required:true}">
							</div>
						</div>						
					</div>

					<div class="message_">
					</div>	
				</div>

				<div class="submit">
					<button class="btn btn-red5" type="submit" name="reset_password_btn" id="reset_password_btn">Reset Password</button>				
				</div>
			</form>
		</div>
		<script src="../theme/js/jquery.js"></script>
		<script src="../theme/js/jquery.validate.min.js"></script>
		<script src="../theme/js/jquery.metadata.js"></script>
		<script src="../theme/js/error.js"></script>
		<script src="../theme/js/main.js"></script>
	</body>
</html>