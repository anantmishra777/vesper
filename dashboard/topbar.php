<?php
	//connect database
	include '../class/dbconnector1.php';
	include '../class/dbconnector2.php';

    //set timezone
    date_default_timezone_set('Europe/Stockholm');

    //log errors
	ini_set("log_errors", 1);
	ini_set("error_log", "error.log");
	
	//fetch data from table 'adminSettings'
	$query = $base->select('adminsettings', '*', array('index_id'=>1));
?>

<div class="topbar">
	<div class="container-fluid">
		<a href="../adminPanel" class='company'><img src="../theme/img/v1.png" style="margin-top: -11px;height: 2em;width: 6em;"></a>			
		<ul class='mini'>			
			<li>
				<a href="settings.php">
					<img src="../theme/img/icons/fugue/gear.png">
					Inställningar
				</a>
			</li>
			<li>
				<a href="../logout">
					<img src="../theme/img/icons/fugue/control-power.png">
					Logout
				</a>
			</li>
		</ul>
	</div>
</div>