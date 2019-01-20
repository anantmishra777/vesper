<!-- 2fa toggle -->
                    <div class="row-fluid">
                        <div class="span6">
                           <div class="box">
								<div class="box-head">
									<h3 id="h_2FA">Change 2FA Settings</h3>
								</div>
								<div class="box-content" style="text-align: center;">
									<table>
										<tr>
											<td id="2FA_status" style="padding-right: 10px;">Current status: </td>
											<td>
												<?php
													$btName = $idVal = $btColor = "";

													if($adminInfo->status2FA == 1)
													{
														$btName = "Disable";
														$idVal = 0;
														$btColor = "danger";
														//enabled
														?>
														<span class="label label-success">Enabled</span>
														<?php
													}
													else
													{
														$btName = "Enable";
														$idVal = 1;
														$btColor = "success";
														//disabled
														?>
														<span class="label label-danger">Disabled</span>
														<?php
													}
												?>
											</td>
										</tr>
									</table>
									<button style="margin-top: 15px;" id="<?php echo $idVal; ?>" class="btn btn-<?php echo $btColor; ?> btn2FAdmin">
										<?php echo $btName; ?> 2FA
									</button>									
									
									<input type="hidden" name="csrf_token" id="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
									<input type="hidden" id="ac_adminID" name="ac_adminID" value="<?php echo $adminInfo->ac_adminID; ?>">
								</div>
							</div>
                        </div>
                    </div>
                    <!-- 2fa toggle end -->	