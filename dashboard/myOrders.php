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

	//validating client's role		
	$query1 = $base->select('clients', '*', array('c_clientID'=> $base->enc($_SESSION['clientID'])));
	if($query1->c_role==2)
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
		<title id="title_my_orders">My Orders - Vesper Group</title>
		<link rel="stylesheet" href="../theme/css/bootstrap.css">
		<link rel="stylesheet" href="../theme/css/bootstrap-responsive.css">
		<link rel="stylesheet" href="../theme/css/jquery.fancybox.css">
		<link rel="stylesheet" href="../theme/css/style.css">
		<link href="cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css">
		<style>
			#myOrders_table_paginate
			{
				cursor: pointer;
			}

			#myOrders_table_paginate span a, #myOrders_table_first, #myOrders_table_previous, #myOrders_table_next, #myOrders_table_last
			{
				padding-right: 5px;
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
						<a href="myOrders.php">Mina beställningar</a>
					</li>
				</ul>
			</div>
		</div>
		<!-- End Second Topbar -->

		<div class="main" >
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
									<h3>Mina beställningar</h3>
								</div>
								<div class="box-content box-nomargin">
									<table class="table table-striped table-bordered" id="myOrders_table">
										<thead>
											<tr style="width: auto;">
												<th><strong>ORDER ID - ORDERNUMMER</strong></th>
												<th><strong>COUNTRY – LAND</strong></th>
												<th><strong>LEVEL - NIVÅ</strong></th>
												<th><strong>NAME - NAMN</strong></th>
												<th><strong>SOCIAL SECURITY NUMBER - PERSONNUMMER</strong></th>
												<th><strong>REQUEST DESCRIPTION – BESKRIVNING</strong></th>
												<th><strong>CONTACT NUMBER – KONTAKTNUMMER</strong></th>
												<th><strong>EMAIL – E-POSTADRESS</strong></th>
												<th><strong>DATE – DATUM</strong></th>
												<th><strong>ORDER STATUS</strong></th>
												<th><strong>REPORT - RAPPORT</strong></th>
											</tr>
										</thead>
										<tbody>
											<!-- Fetch rows from table 'orders' -->
											<?php
												if($query1->c_is_parent==1)
													$clientID = $base->enc($query1->c_parentID);
												else
													$clientID = $base->enc($_SESSION['clientID']);


												try
												{
													$query = $conn2->prepare('SELECT * FROM orders WHERE c_clientID=:c_clientID');
													$query->bindParam(':c_clientID', $clientID);
													$query->execute();
												}
												catch(PDOException $e)
												{
													$e->getMessage();
												}

												$row = $query->fetchAll(PDO::FETCH_ASSOC);

												for($i=0; $i<$query->rowCount(); $i++)
												{
													$id = $base->dec($row[$i]['o_indexid']);
													?>
													<tr>
														<td>
															<?php 
																if( $base->dec($row[$i]['o_orderID'])!='' )
																	echo htmlspecialchars($base->dec($row[$i]['o_orderID'])); 
															?>
														</td>

														<td>
															<?php 
																if( $base->dec($row[$i]['o_formLevel']) < 4)
																	echo 'Sverige'; 
																else
																	echo 'Utomlands';
															?>
														</td>

														<td>
															<?php 
																echo htmlspecialchars($base->dec($row[$i]['o_formLevel'])); 
															?>
														</td>

														<td>
															<?php 
																if( $base->dec($row[$i]['o_name'])!='' )
																	echo htmlspecialchars($base->dec($row[$i]['o_name'])); 
															?>
														</td>

														<td >
															<?php 
																if( $base->dec($row[$i]['o_personnummer'])!='')
																	echo htmlspecialchars($base->dec($row[$i]['o_personnummer'])); 
															?>
														</td>


														<td >
															<?php echo htmlspecialchars($base->dec($row[$i]['o_request_description'])); ?>
														</td>

														<td >
															<?php echo htmlspecialchars($base->dec($row[$i]['o_contact_number'])); ?>
														</td>

														<td >
															<?php echo htmlspecialchars($base->dec($row[$i]['o_email'])); ?>
														</td>

														<td>
															<?php echo htmlspecialchars(substr($base->dec($row[$i]['o_orderDateTime']), 0, strpos($base->dec($row[$i]['o_orderDateTime']), ' '))); ?>
														</td>

														<td >
															<?php
																switch( $base->dec($row[$i]['o_orderStatus']) )
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
														<td>
															<?php
																if($base->dec($row[$i]['r_reportLocation'])!='')
																{
																	?>
																	<a href="download.php?url=<?php echo htmlspecialchars($base->dec($row[$i]['r_reportLocation'])); ?>">Download</a>
																	<?php
																}	
															?>
														</td>
																	<!-- <td class='actions'>
																		<div class="btn-group">
																			<a id="browse_order_<?php echo $id; ?>">Browse Order</a>
																		</div>
																	</td> -->
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
		        	<!-- End my orders table -->		               		
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
		<script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
		<script src="../theme/js/main.js"></script>
	</body>
	<script>
		$('#li_myOrders').addClass('active');	
		$(document).ready(function() 
		{
		    $('#myOrders_table').DataTable({"aaSorting": [], "language": {"search": "Sök:", "lengthMenu": "Visa _MENU_"}, "pagingType": "full_numbers"});
		});
	</script>	
</html>