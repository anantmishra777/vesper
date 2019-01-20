<?php
	session_start();

	//redirect to login page if client isn't logged in
	if( !isset($_SESSION['email']) )
		header('Location: ../login');
	
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
	$query = $base->select('clients', '*', array('c_clientID' => $base->enc($_SESSION['clientID'])));
	if( $query->c_is_parent==1 )
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
		<title id="title_add_users">Add Users - Vesper Group</title>
		<link rel="stylesheet" href="../theme/css/bootstrap.css">
		<link rel="stylesheet" href="../theme/css/bootstrap-responsive.css">
		<link rel="stylesheet" href="../theme/css/jquery.fancybox.css">
		<link rel="stylesheet" href="../theme/css/style.css">		
		<style>
			td
			{
				padding-right: 15px;
				padding-bottom: 5px;
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
						<a href="addUsers.php">Lägg till användare</a>
					</li>
				</ul>
			</div>
		</div>
		<!-- End Second Topbar -->

		<!-- Success message -->
		<div class="success_message4" id="message_user_added">
			User added successfully!
		</div>		

		<div class="main">
			<div class="container-fluid">

				<!--Sidebar-->
				<?php include 'sidebar2.php'; ?>
				<!--End Sidebar-->
				
				<div class="content">
					
					<!-- Place New Order -->
					<div class="row-fluid">
						<div class="span12">
							<div class="box">
								<div class="box-head">
									<h3>Lägg till ny användare</h3>									
								</div>
								<div class="box-content">
									<!-- Add new user form -->
									<form name="addUser_form" id="addUser_form">
										<!-- CSRF token -->
										<input type="hidden" name="csrf_token" id="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
										
										<!-- Flag -->
										<input type="hidden" name="flag" id="flag" value="9">

										<table>											
											<tr>
												<td><label for="addUser_name">Namn</label></td>
												<td><input class='form-control' type="text" name="addUser_name" id="addUser_name" required /></td>
											</tr>

											<tr>
												<td><label for="addUser_email">Epost</label></td>
												<td><input class='form-control' type="email" name="addUser_email" id="addUser_email" required/></td>
											</tr>

											<tr>
												<td><label for="addUser_role">Telefonnummer</label></td>
												<td><input class='form-control' type="text" name="addUser_number" id="addUser_number" required/></td>
											</tr>
											
											<tr>
												<td><label for="addUser_role" id="role">Roll</label></td>
												<td>
													<select class='form-control' name="addUser_role" id="addUser_role" required>
														<option value="1" id="read_only">Endast läs</option>
														<option value="2" id="order_only">Endast beställning</option>
														<option value="3" id="full_access">Full tillgång</option>
													</select>
												</td>
											</tr>

											<tr>
												<td><button class="btn btn-red5" name="addUser_btn" id="addUser_btn">Lägg till användare</button></td>
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
		$('#li_addUsers').addClass('active');
		$('.collapsed-nav').css('display', 'block');
	</script>	
</html>