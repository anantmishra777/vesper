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

	//include config file
	include '../class/config.php';

	//including mysql functions file
    include '../class/class.base.php';
    $base = new base();

    //log errors
	ini_set("log_errors", 1);
	ini_set("error_log", "error.log");

	//fetching client details
	$query = $base->select('clients', '*', array('c_clientID'=> $_SESSION['clientID']));
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
						<a href="security.php">Säkerhet</a>
					</li>
				</ul>
			</div>
		</div>
		<!-- End Second Topbar -->
	
		<div class="main">
			<div class="container-fluid">

				<!--Sidebar-->
				<?php include 'sidebar2.php'; ?>
				<!--End Sidebar-->			
	
				<div class="content">
					<!-- Log table -->
					<div class="row-fluid">
                        <div class="span6">
                            <div class="box">
                                <div class="box-head">
                                    <h3>Beställningslogg</h3>
                                </div>
                                <div class="box-content box-nomargin">
                                    <table class="table table-striped table-bordered">
                                        <thead>
                                        <tr>
                                            <th><strong>Logg ID</strong></th>
                                            <th><strong>Beskrivning</strong></th>
                                            <th><strong>Datum</strong></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            	//fetch logs of client and that of users added by client 
                                            	$accessLevel=0;
                                            	try
                                            	{
                                            		$s1 =$conn2->prepare("SELECT * FROM logs WHERE l_accessLevel=:l_accessLevel ORDER BY l_logID DESC");
                                            		$s1->bindParam(':l_accessLevel', $accessLevel);
                                            		$s1->execute();
                                            	}
                                            	catch(PDOException $e)
                                            	{
                                            		$e->getMessage();
                                            	}                             	

                                                while($log = $s1->fetch(PDO::FETCH_OBJ))
                                                {
                                                	if($log->l_user==$_SESSION['email'])
                                                	{
                                                		?>
	                                                    <tr>
	                                                        <td><?php echo htmlspecialchars($log->l_logID); ?></td>
	                                                        <td><?php echo htmlspecialchars($log->l_logDescription); ?></td>
	                                                        <td><?php echo htmlspecialchars($log->l_dateTime); ?></td>
	                                                    </tr>
	                                                    <?php
                                                	}

                                                	//verifying is_parent
                                                	else
                                                	{
                                                		$s2 = $base->select('clients', '*', array('c_email'=> $log->l_user));
                                                		if($s2->c_is_parent==1 && $s2->c_parentID == $_SESSION['clientID'])
                                                		{
                                                			?>
		                                                    <tr>
		                                                        <td><?php echo htmlspecialchars($log->l_logID); ?></td>
		                                                        <td><?php echo htmlspecialchars($log->l_logDescription); ?></td>
		                                                        <td><?php echo htmlspecialchars($log->l_dateTime); ?></td>                                   
		                                                    </tr>
		                                                    <?php
                                                		}         
                                                	}                                               	                   
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
		<!-- <script src="../theme/js/demo.js"></script> -->
		<script src="../theme/js/main.js"></script>
	</body>
	<script type="text/javascript"></script>
	<script>
		$('#li_security').addClass('active');
	</script>	
</html>