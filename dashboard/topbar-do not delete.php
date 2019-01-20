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
			<li class="dropdown dropdown-noclose supportContainer">
				<a href="#" class="dropdown-toggle" data-toggle="dropdown" id="lang">
					<img src="../theme/img/icons/fugue/control-270.png" alt="">
					<?php
						if( $_SESSION['language']=='en' )
							echo "<span>Language: English</span>";
						else
							echo "<span>Language: Swedish</span>";
					?>					
				</a>
				<ul class="dropdown-menu pull-right custom custom-dark" style="background-color: #333333; color: white; width: 0;">
					<li class="custom on_hover" id="en" style="min-width: 0;">
						<div class="title">
							English
						</div>
					</li>
					<li class="custom on_hover" id='sw' style="min-width: 0;">
						<div class="title">
							Swedish
						</div>
					</li>					
				</ul>
			</li>
			
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