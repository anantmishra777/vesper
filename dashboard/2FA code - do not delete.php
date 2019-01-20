<!-- 2fa toggle -->
                    <div class="row-fluid">
                        <div class="span6">
                           <div class="box">
								<div class="box-head">
									<h3>Ändra Tvåstegs autentisering</h3>
								</div>
								<div class="box-content" style="text-align: center;">
									<table>
										<tr>
											<td id="2FA_status" style="padding-right: 10px;">Nuvarande Tvåstegs autentisering: </td>												
											<td>										
												<?php
													$btName = $idVal = $btColor = "";
													if($query->status2FA == 1)
													{
														$btName = "Inaktivera";
														$idVal = 0;
														$btColor = "danger";
														//enabled
														?>
														<span class="label label-success">Aktivara</span>
														<?php
													}
													else
													{
														$btName = "Aktivera";
														$idVal = 1;
														$btColor = "success";
														//disabled
														?>
														<span class="label label-danger">Inaktivera</span>
														<?php
													}
												?>
											</td>
										</tr>
									</table>
									<button style="margin-top: 15px;" id="<?php echo $idVal; ?>" class="btn btn-<?php echo $btColor; ?> btn2FA"><?php echo $btName; ?> 2FA</button>
									<input type="hidden" name="csrf_token" id="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
									<input type="hidden" id="c_clientID" name="c_clientID" value="<?php echo $query->c_clientID; ?>">
								</div>
							</div>
                        </div>
                    </div>