<?php
    //log errors
	ini_set("log_errors", 1);
	ini_set("error_log", "error.log");

	//fetch client details
	$s = $base->select('clients', '*', array('c_clientID' => $base->enc($_SESSION['clientID'])));
	$role = $base->dec($s->c_role);
	$is_parent = $base->dec($s->c_is_parent);
?>



<div class="navi">
	<ul class='main-nav'>
		<li id="li_dashboard">
			<a href="../dashboard" class='light'>
				<div class="ico"><i class="icon-home icon-white"></i></div>
				Översiktsvy
			</a>
		</li>
		
		<!-- validating client's role -->
		<?php			
			if( $role!=2 )
			{
				?>
				<li id="li_myOrders">
					<a href="myOrders.php" class='light'>
						<div class="ico"><i class="icon-tasks icon-white"></i></div>
						Mina beställningar
					</a>
				</li>	
				<?php
			}
		?>

		<!-- is parent validation -->
		<?php
			if( $is_parent==0 )
			{
				?>
				<li id="li_addUsers">
					<a href="#" class='light toggle-collapsed'>
						<div class="ico"><i class="icon-th-large icon-white"></i></div>
						Användare
						<img src="../theme/img/toggle-subnav-down.png" alt="">
					</a>
					<ul class='collapsed-nav closed'>
						<li>
							<a href="addUsers.php">
								Lägg till användare
							</a>
						</li>
						<li>
							<a href="manageUsers.php">
								Hantera användare
							</a>
						</li>
					</ul>
				</li>
				<?php
			}
		?>

		<li id="li_settings">
			<a href="settings.php" class='light'>
				<div class="ico"><img style="height: 25px; margin-top: 0 !important;" src="../theme/img/icons/fugue/gear.png"/></div>
				Inställningar
			</a>
		</li>

		<li id="li_security">
            <a href="security.php" class='light'>
                <div class="ico"></div>
                Säkerhet
            </a>
        </li>
	</ul>
</div>