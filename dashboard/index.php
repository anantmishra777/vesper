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
	$query = $base->select('clients', '*', array('c_clientID'=> $base->enc($_SESSION['clientID'])));
	$role_ = $base->dec($query->c_role);
	$is_parent_ = $base->dec($query->c_is_parent);

	$_SESSION['uploaded_file_names']='';
?>



<!doctype html>
<html>
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
		<style>
			td
			{
				padding-right: 20px;
			}
			#info_modal
			{
				display: none;
			}
			#view_template_sw, #view_template_en
			{
				cursor: pointer;
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
						<a href="index.php">Översiktsvy</a>
					</li>
				</ul>
			</div>
		</div>
		<!-- End Second Topbar -->

		<!-- Modal 1-->
		<div id="info_modal" class="modal">
		    <div class="modal-dialog">
		        <div class="modal-content">
		            <div class="modal-body">
		                <p>När informationen är ifylld och skickad så kommer Vesper Group hantera förfrågan och påbörja genomförandet av kontrollen. När kontrollen är genomförd kommer beställaren få ett meddelande om detta på mailadressen som kontot är kopplat till. Beställaren kan då logga in på portalen och hämta ut sin rapport. Rapporten kommer sparas på portalen i 14 dagar, sen kommer all information raderas på grund av restriktioner inom GDPR. Vesper ansvarar inte för att lagra informationen och rapporten är därmed helt raderad efter 14 dagar. Fakturering sker månadsvis. Kunden kan komma att kontaktas om faktureringsuppgifter saknas.</p>
		                <button type="button" class="btn btn-default" data-dismiss="modal" style="text-align: center;" id="close_modal">Stänga</button>
		            </div>
		        </div>
		    </div>
		</div>

		<!-- Overlay -->
		<div id="body_overlay"></div>

		<div class="main">
			<div class="container-fluid">

				<!--Sidebar-->
				<?php include 'sidebar2.php'; ?>
				<!--End Sidebar-->			

				<div class="content">
					<!-- validating client's role -->
					<?php
						if($role_!=2)
						{
							?>
							<div class="row-fluid no-margin">
				                <div class="span12">
				                    <ul class="quickstats">
				                        <li>
				                            <div class="small-chart" data-color="2c5b96" data-stroke="102c50" data-type="bar">2,5,4,6,5,4,7,8</div>
				                            <div class="chart-detail">
				                                <span class="amount" id='total_orders'><?php echo htmlspecialchars($base->select('orders', '*', array('c_clientID'=>$_SESSION['clientID']), 1, 0)); ?></span>
				                                <span class="description" id="_total_orders">Totalt antal beställningar</span>
				                            </div>
				                        </li>

				                        <li>
				                            <div class="small-chart" data-color="2c5b96" data-stroke="102c50" data-type="bar">2,5,4,6,5,4,7,8</div>
				                            <div class="chart-detail">
				                                <span class="amount" id='pending_orders'><?php echo htmlspecialchars($base->select('orders', '*', array('c_clientID'=>$_SESSION['clientID'], 'o_orderStatus'=>1), 1, 0)); ?></span>
				                                <span class="description">Avvaktande beställning</span>
				                            </div>
				                        </li>

				                        <li>
				                           <div class="small-chart" data-color="2c5b96" data-stroke="102c50" data-type="bar">2,5,4,6,5,4,7,8</div>
				                            <div class="chart-detail">
				                                <span class="amount" id='orders_progress'><?php echo htmlspecialchars($base->select('orders', '*', array('c_clientID'=>$_SESSION['clientID'], 'o_orderStatus'=>2), 1, 0)); ?></span>
				                                <span class="description">Beställning pågår</span>
				                            </div>
				                        </li>
				                    </ul>
				                </div>
		            		</div>
		            		<?php
		            	}
		            ?>
					
					<div class="row-fluid">
						<div class="span12">
							<div class="box">
								<div class="box-content">
									Vesper Group erbjuder systematiskt genomförda bakgrundskontroller vid rekryteringar, företagsförvärv, inköp, samt vid andra affärsprocesser eller utredningar. Vi erbjuder kontroller inom Sverige men även utomlands. Kontroller inom Sverige finns i tre nivåer där den högsta nivån angränsar till ett utredningsarbete där målsättningarna sätts i nära samarbete med våra uppdragsgivare. Alla kontroller rapporteras i form av en sammanställning samt med rekommendationer om fortsatt hantering. Vesper Groups information är till största del hämtad från offentliga källor. Vesper Group kan därmed ej garantera att all information är korrekt eller fullständig.
								</div>
							</div>
						</div>
					</div>

					<!-- Place New Order -->
					<div class="row-fluid">
						<div class="span12">
							<div class="box">								
								<!-- validating client's role -->
								<?php
									if($role_!=1)
									{//can place orders
										?>
										<div class="box-head">
											<h3 id="h_place_order">Lägg ny beställning</h3>										
										</div>
										<div class="box-content">
											<div class = "btn-group">
												<button type = "button" class = "btn btn-primary dropdown-toggle" data-toggle = "dropdown" id="language">
													Sverige  
													<span class = "caret"></span>
												</button>
												<ul class = "dropdown-menu" role = "menu">
													<li><a href = "#" id="swedish">Sverige</a></li>
													<li><a href = "#" id="english">Utomlands</a></li>	
												</ul>   
											</div>

											<hr style="border-top: 1px solid #999;">
											
											<div class = "btn-group">
												<button type ="button" class ="btn btn-primary dropdown-toggle" data-toggle ="dropdown" id="select_form_level">
													Nivå 1  
													<span class = "caret"></span>
												</button>

												<ul class = "dropdown-menu" role = "menu">
													<li><a href = "#" id="formLevel_1">Nivå 1</a></li>
													<li><a href = "#" id="formLevel_2">Nivå 2</a></li>
													<li><a href = "#" id="formLevel_3">Nivå 3</a></li>
												</ul>								
											</div>				

											<div>
												<p id="level1_desciption" class="level_desciption">Nivå 1 kontroller beställs förslagsvis vid anställning av medarbetare utan finansiell insyn, mandat för överföringar samt liten insyn i sekretessbelagda intressen.<br/>En färdig kontroll skickas till kund inom två arbetsdagar från beställningen.<br/>• Folkbokföringsuppgifter<br/>• Civilstånd<br/>• Uppgifter från Kronofogdemyndigheten<br/>• Betalningsanmärkningar<br/>• Taxeringsuppgifter<br/>• Fastighetsinnehav<br/>• Bolagsengagemang<br/>• Rättsliga förehavanden<br/><br/>Rapportering sker i form av en sammanställning samt med rekommendationer om fortsatt hantering.</p>

												<p id="level2_desciption" class="level_desciption" hidden>
													Nivå 2 kontroller beställs förslagsvis vid anställning av medarbetare med finansiell insyn, mandat för överföringar samt insyn i sekretessbelagda intressen.<br/>
													En färdig kontroll skickas till kund inom fyra arbetsdagar från beställningen.<br/><br/>
													• Folkbokföringsuppgifter<br/>
													• Civilstånd<br/>
													• Uppgifter från Kronofogdemyndigheten<br/>
													• Betalningsanmärkningar<br/>
													• Taxeringsuppgifter<br/>
													• Fastighetsinnehav<br/>
													• Bolagsengagemang<br/>
													• Rättsliga förehavanden<br/>
													• Ekonomisk status på de bolag individen är engagerad i<br/>
													• CV kontroll<br/>
													• Djupare kontroll av eventuella domar<br/>
													• Personalia avseende familj<br/>
													• Media och internetsökning<br/><br/>Rapportering av nivå 2 sker i form av en sammanställning samt med rekommendationer om fortsatt hantering.</p>

												<p id="level3_desciption" class="level_desciption" hidden>
													Kontrollen anpassas till de förutsättningar vi får av er som uppdragsgivare och offereras enskilt beroende på omfattning och syfte. Fyll i en sammanfattning av er förfrågan samt fyll i kontaktuppgifter så kontaktar vi er för vidare diskussion och hantering.
												</p>

												<p id="level4_desciption" class="level_desciption" hidden>
													Kontrollen anpassas till de förutsättningar vi får av er som uppdragsgivare och offereras enskilt beroende på omfattning och syfte. Fyll i en sammanfattning av er förfrågan samt fyll i kontaktuppgifter så kontaktar vi er för vidare diskussion och hantering.
												</p>
											</div>											

											<form name="place_order_form_swe1" id="place_order_form_swe1">
												<table>	
													<!-- CSRF token -->
													<input type="hidden" name="csrf_token" id="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

													<!-- Set Flag -->
													<input type="hidden" name="flag" id="flag" value="1">

													<tr>
														<td><label for="namn">Namn</label></td>
														<td><input class='form-control' type="text" name="namn" id="namn" required /></td>
													</tr>

													<tr>
														<td><label for="personnummer">Personnummer</label></td>
														<td><input class='form-control' type="text" name="personnummer" id="personnummer" required/></td>
													</tr>

													<tr>
														<td><label for="bifoga_medgivande">Ladda upp fil (medgivande från kund)</label></td>
														<td><input type="file" name="bifoga_medgivande" id="bifoga_medgivande" placeholder="abe" required/></td>
														<td><a id='view_template_sw'>Hämta mall (Svenska)</a></td>
														<td><a id='view_template_en'>Hämta mall (English)</a></td>
													</tr>

													<tr>
														<td><label for="invoice_info">Kostnadsställe</label></td>
														<td><input type="text" name="invoice_info"/></td>
													</tr>

													<tr>
														<td><label for="report_lang">Preferred Language</label></td>
														<td>
															<select name="report_lang" required>															
																<option value='1'>Svenska</option>
																<option value='2'>English</option>
															</select>
														</td>
													</tr>				

													<tr>
														<td>
															<button type='submit' class='btn btn-red5' id="place_order_btn_swe1" name="place_order_btn_swe1">Skicka beställning</button>
														</td>
													</tr>								
												</table>		
											</form>

											<form name="place_order_form_swe2" id="place_order_form_swe2" hidden>
												<table>
													<!-- CSRF token -->
													<input type="hidden" name="csrf_token" id="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">					
													<!-- Set Flag -->
													<input type="hidden" name="flag" id="flag" value="2">

													<tr>
														<td><label for="namn">Namn</label></td>
														<td><input class='form-control' type="text" name="namn" id="namn" required /></td>
													</tr>

													<tr>
														<td><label for="personnummer">Personnummer</label></td>
														<td><input class='form-control' type="text" name="personnummer" id="personnummer" required/></td>
													</tr>

													<tr>
														<td><label for="bifoga_medgivande">Ladda upp fil (medgivande från kund)</label></td>
														<td><input type="file" name="bifoga_medgivande" id="bifoga_medgivande" required/></td>
														<td><a id='view_template_sw'>Hämta mall (Svenska)</a></td>
														<td><a id='view_template_en'>Hämta mall (English)</a></td>
													</tr>

													<tr>
														<td><label for="bifoga_cv">Ladda upp fil (CV från kunden)</label></td>
														<td>
															<input type="file" name="bifoga_cv" id="bifoga_cv" required />
														</td>			
													</tr>

													<tr>
														<td><label for="invoice_info">Kostnadsställe</label></td>
														<td><input type="text" name="invoice_info"/></td>
													</tr>
													
													<tr>
														<td><label for="report_lang">Preferred Language</label></td>
														<td>
															<select name="report_lang" required>															
																<option value='1'>Svenska</option>
																<option value='2'>English</option>
															</select>
														</td>
													</tr>		

													<tr>
														<td>
															<button type='submit' class='btn btn-red5' id="place_order_btn_swe2" name="place_order_btn_swe2">Skicka beställning</button>
														</td>
													</tr>	
												</table>
											</form>
											
											<form name="place_order_form_swe3" id="place_order_form_swe3" hidden>
												<table>
													<!-- CSRF token -->
													<input type="hidden" name="csrf_token" id="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">				
													<!-- Set Flag -->
													<input type="hidden" name="flag" id="flag" value="3">

													<tr>
														<td><label for="request_description">Beskrivning</label></td>
														<td><textarea rows="5" cols="15" id="request_description" name="request_description" required></textarea></td>
													</tr>

													<tr>
														<td><label for="contact_number">Kontaktnummer</label></td>
														<td><input class='form-control' type="text" name="contact_number" id="contact_number"  required/></td>
													</tr>

													<tr>
														<td><label for="email">E-postadress</label></td>
														<td><input class='form-control' type="email" name="email" id="email"  required/></td>
													</tr>

													<tr>
														<td><label for="invoice_info">Kostnadsställe</label></td>
														<td><input type="text" name="invoice_info"/></td>
													</tr>

													<tr>
														<td><label for="report_lang">Preferred Language</label></td>
														<td>
															<select name="report_lang" required>															
																<option value='1'>Svenska</option>
																<option value='2'>English</option>
															</select>
														</td>
													</tr>	

													<tr>
														<td>
															<button type='submit' class='btn btn-red5' id="place_order_btn_swe3" name="place_order_btn_swe3">Skicka beställning</button>
														</td>
													</tr>
												</table>
											</form>

											<form class="showHide" name="place_order_form_eng" id="place_order_form_eng" hidden>
												<!-- CSRF token -->
												<input type="hidden" name="csrf_token" id="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

												<!-- Set Flag -->
												<input type="hidden" name="flag" id="flag" value="4">

												<table>
													<tr>
														<td><label for="request_description">Beskrivning av beställning/förfrågan</label></td>
														<td><textarea rows="5" cols="15" id="request_description" name="request_description" required></textarea></td>
													</tr>
													<tr>
														<td><label for="contact_number">Telefonnummer</label></td>
														<td><input class='form-control' type="text" name="contact_number" id="contact_number" required /></td>
													</tr>
													<tr>
														<td><label for="email">Epost</label></td>
														<td><input class='form-control' type="email" name="email" id="email" required /></td>
													</tr>
													<tr>
														<td><label for="invoice_info">Kostnadsställe</label></td>
														<td><input type="text" name="invoice_info"/></td>
													</tr>
													<tr>
														<td><label for="report_lang">Preferred Language</label></td>
														<td>
															<select name="report_lang" required>															
																<option value='1'>Svenska</option>
																<option value='2'>English</option>
															</select>
														</td>
													</tr>	
													<tr>
														<td>
															<button type='submit' class='btn btn-red5' id="place_order_btn_eng" name="place_order_btn_eng">Skicka beställning</button>
														</td>
													</tr>
												</table>
											</form>		
										</div>
										<?php
									}
								?>
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
	<script>
		$('#swedish').click();
		$('#li_dashboard').addClass('active');
	</script>	
</html>