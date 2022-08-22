<?php
include "../../../model/model.php";
$purchase_id = $_POST['purchase_id'];
$str="select * from excursion_inventory_master where entry_id='$purchase_id'";
$query = mysqlQuery($str);
?>
<div class="modal fade" id="history_modal" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static" data-keyboard="false">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title" id="myModalLabel">Activity Inventory History</h4>
		</div>
		<div class="modal-body">
				<div class="row"> <div class="col-md-12"> <div class="table-responsive">
				<table class="table" id="table_paid" style="margin: 20px 0 !important;">
					<thead>
						<tr class="table-heading-row">
							<th>S_No.</th>
							<th>Service</th>
							<th>Booking_id</th>
							<th>Activity_datetime</th>
							<th>Customer_name</th>
							<th>Total_tickets</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$count = 0;
						while($row_ser = mysqli_fetch_assoc($query)){
							$sql_temp=mysqlQuery("select * from excursion_master_entries where city_id='$row_ser[city_id]' and exc_name='$row_ser[exc_id]' and (exc_date between '$row_ser[valid_from_date]' and '$row_ser[valid_to_date]')");
							while($sql = mysqli_fetch_assoc($sql_temp)){
								$check_in=$sql['exc_date'];
								$str1="select * from excursion_master where exc_id=$sql[exc_id]";
								$sql_cust=mysqli_fetch_assoc(mysqlQuery($str1));
								$date = $sql_cust['created_at'];
								$yr = explode("-", $date);
								$year = $yr[0];
								$sql_Cust_details=mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id=$sql_cust[customer_id]"));
								if($sql_Cust_details['type']=='Corporate'||$sql_Cust_details['type'] == 'B2B'){
									$customer_name = $sql_Cust_details['company_name'];
								}else{
									$customer_name = $sql_Cust_details['first_name'].' '.$sql_Cust_details['last_name'];
								}
							?>
							<tr class="<?= $bg ?>">
								<td><?= ++$count ?></td>
								<td>Activity</td>
								<td><?= get_exc_booking_id($sql_cust['exc_id'],$year)?></td>
								<td><?= date("d-m-Y H:i", strtotime($check_in)) ?></td>
								<td><?= $customer_name; ?></td>
								<td><?= $sql['total_adult']+$sql['total_child'] ?></td>
							</tr>
							<?php
							}
							
							$sq_hotel_c1 = mysqlQuery("select * from package_tour_excursion_master where city_id= '$row_ser[city_id]' and exc_id = '$row_ser[exc_id]' and booking_id in(select booking_id from package_tour_booking_master where tour_from_date between '$row_ser[valid_from_date]' and '$row_ser[valid_to_date]')");

							while($row_hotel_c1= mysqli_fetch_assoc($sq_hotel_c1)){
								$check_in=$sql['exc_date'];
								$str1="select * from package_tour_booking_master where booking_id='$row_hotel_c1[booking_id]'";
								$sql_cust=mysqli_fetch_assoc(mysqlQuery($str1));
								$date = $sql_cust['booking_date'];
								$yr = explode("-", $date);
								$year = $yr[0];
								$sql_Cust_details=mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id=$sql_cust[customer_id]"));
								if($sql_Cust_details['type']=='Corporate'||$sql_Cust_details['type'] == 'B2B'){
									$customer_name = $sql_Cust_details['company_name'];
								}else{
									$customer_name = $sql_Cust_details['first_name'].' '.$sql_Cust_details['last_name'];
								}
								$pax = mysqli_num_rows(mysqlQuery("select count(traveler_id) from package_travelers_details where booking_id='$row_hotel_c1[booking_id]'"));
								?>
								<tr class="<?= $bg ?>">
									<td><?= ++$count ?></td>
									<td>Package</td>
									<td><?= get_package_booking_id($row_hotel_c1['booking_id'],$year)?></td>
									<td><?= 'NA' ?></td>
									<td><?= $customer_name; ?></td>
									<td><?= $pax ?></td>
								</tr>
								<?php
								}?>
						<?php }
						?>
					</tbody>
				</table>
				</div> </div> </div>
	</div>
</div>
</div>
</div>

<script>
$('#history_modal').modal('show');
</script>

<script src="<?= BASE_URL ?>js/app/footer_scripts.js"></script>