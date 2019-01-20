<?php
	session_start();

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
?>


<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>Forgot Password - Vesper Group</title>
		<meta name="description" content="">

		<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no" />

		<link rel="stylesheet" href="../theme/css/bootstrap.css">
		<link rel="stylesheet" href="../theme/css/jquery.fancybox.css">
		<link rel="stylesheet" href="../theme/css/login.css">
		<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css">
	</head>
	<body class='login_body'>
		<div class="wrap" id="login_wrap">

			<div class="success_message2">
				Password reset link has been sent to your email.
			</div>	

			<h2 style="margin-top: 20px;">Forgot Password</h2>
			<form autocomplete="off" class="validate" id="forgot_pwd_form" name="forgot_pwd_form">				
				<!-- csrf token -->
				<input type="hidden" name="csrf_token" id="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">				

				<div class="login">
					<div class="email">
						<label for="email_">Email</label>
						<div class="control-group">
							<div class="input-prepend">
								<span class="add-on"><i class="icon-envelope"></i></span>
								<input type="text" id="email_" name="email_" class="{required:true}">
							</div>
						</div>						
					</div>

					<div class="message_">			
					</div>	
				</div>

				<div class="submit">
					<button class="btn btn-red5" type="submit" name="forgot_pwd_btn" id="forgot_pwd_btn">Reset Password</button>				
				</div>
			</form>
		</div>
		<script src="../theme/js/jquery.js"></script>
		<script src="../theme/js/jquery.validate.min.js"></script>
		<script src="../theme/js/jquery.metadata.js"></script>
		<script src="../theme/js/error.js"></script>
		<script src="../theme/js/toastr.min.js"></script>
		<script src="../theme/js/main.js"></script>
	</body>
</html>