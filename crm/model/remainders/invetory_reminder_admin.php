<?php
include_once('../model.php');
global $model;
$type = $_POST['type'];
$today=date('Y-m-d');

if($type == 'hotel'){
  $sq_settings = mysqlQuery("select * from hotel_inventory_master where reminder1='$today'");
  while($row_setting = mysqli_fetch_assoc($sq_settings)){
    email($type,$row_setting['entry_id']);
  }
  $sq_settings2 = mysqlQuery("select * from hotel_inventory_master where reminder2='$today'");
  while($row_setting2 = mysqli_fetch_assoc($sq_settings2)){
    email($type,$row_setting2['entry_id']);
  }
  $row=mysqlQuery("SELECT max(id) as max from remainder_status");
  $value=mysqli_fetch_assoc($row);
  $max=$value['max']+1;
  $sq_check_status=mysqlQuery("INSERT INTO `remainder_status`(`id`, `remainder_name`, `date`, `status`) VALUES ('$max','hotel_inventory','$today','Done')");
}
else if($type == 'excursion'){
  $sq_settings3 = mysqlQuery("select * from excursion_inventory_master where reminder1='$today' or reminder2='$today'");
  while($row_setting3 = mysqli_fetch_assoc($sq_settings3)){
    email($type,$row_setting3['entry_id']);
  }
  $row=mysqlQuery("SELECT max(id) as max from remainder_status");
  $value=mysqli_fetch_assoc($row);
  $max=$value['max']+1;
  $sq_check_status=mysqlQuery("INSERT INTO `remainder_status`(`id`, `remainder_name`, `date`, `status`) VALUES ('$max','excursion_inventory','$today','Done')");
}

function email($type,$entry_id){
  global $app_email_id,$model;
  if($type == 'hotel'){
    $avai_rooms = 0;
    $type_id = '102';
    $subject = 'Hotel Pre Booking Inventory Cancellation Reminder!';
    $sq_hotel = mysqli_fetch_assoc(mysqlQuery("select * from hotel_inventory_master where entry_id='$entry_id'"));
    $sq_hotel_n = mysqli_fetch_assoc(mysqlQuery("select hotel_name from hotel_master where hotel_id='$sq_hotel[hotel_id]'"));

    //Available Rooms 
    $sq_package = mysqli_fetch_assoc(mysqlQuery("select sum(rooms) as package_rooms from package_hotel_accomodation_master where city_id= '$sq_hotel[city_id]' and hotel_id	= '$sq_hotel[hotel_id]' and (from_date between '$sq_hotel[valid_from_date]' and '$sq_hotel[valid_to_date]')"));
    $sq_hotel_c = mysqli_fetch_assoc(mysqlQuery("select sum(rooms) as hotel_rooms from hotel_booking_entries where city_id= '$sq_hotel[city_id]' and hotel_id	= '$sq_hotel[hotel_id]'  and (check_in between '$sq_hotel[valid_from_date]' and '$sq_hotel[valid_to_date]')"));
    $avai_rooms = $sq_hotel['total_rooms'] - ($sq_package['package_rooms'] + $sq_hotel_c['hotel_rooms']);
    $avai_rooms = ($avai_rooms<0)?0:$avai_rooms;
    $for = $sq_hotel_n['hotel_name'];
   
    $content = '
		<tr>
            <table width="85%" cellspacing="0" cellpadding="5" style="color: #888888;border: 1px solid #888888;margin: 0px auto;margin-top:20px; min-width: 100%;" role="presentation">
              <tr><td style="text-align:left;border: 1px solid #888888;">Hotel Name </td>   <td style="text-align:left;border: 1px solid #888888;">'.$sq_hotel_n['hotel_name'].'</td></tr>
              <tr><td style="text-align:left;border: 1px solid #888888;"> Total Rooms</td>   <td style="text-align:left;border: 1px solid #888888;" >'.$sq_hotel['total_rooms'].'</td></tr>
              <tr><td style="text-align:left;border: 1px solid #888888;"> Available Rooms</td>   <td style="text-align:left;border: 1px solid #888888;">'.$avai_rooms.'</td></tr>
              <tr><td style="text-align:left;border: 1px solid #888888;"> Cancellation Date</td>   <td style="text-align:left;border: 1px solid #888888;">'.get_date_user($sq_hotel['cancel_date']).'</td></tr>
            </table>
          </tr>';
  }
  elseif($type == 'excursion'){
    $type_id = '103';
    $subject = 'Activity Pre Booking Inventory Cancellation Reminder!';
    $sq_hotel = mysqli_fetch_assoc(mysqlQuery("select * from excursion_inventory_master where entry_id='$entry_id'"));
    $sq_exc = mysqli_fetch_assoc(mysqlQuery("select entry_id, excursion_name from excursion_master_tariff where entry_id='$sq_hotel[exc_id]'"));

    //Excursion Booking
    $sq_hotel_c = mysqlQuery("select total_adult, total_child from excursion_master_entries where city_id= '$sq_hotel[city_id]' and exc_name	= '$sq_hotel[exc_id]' and (exc_date between '$sq_hotel[valid_from_date]' and '$sq_hotel[valid_to_date]')");
    while($row_hotel_c = mysqli_fetch_assoc($sq_hotel_c)){
      $total_pax += $row_hotel_c['total_adult'] + $row_hotel_c['total_child'];
    }
    //Package booking
    $sq_hotel_c1 = mysqlQuery("select * from package_tour_excursion_master where city_id= '$sq_hotel[city_id]' and exc_id = '$sq_hotel[exc_id]' and booking_id in(select booking_id from package_tour_booking_master where tour_from_date between '$sq_hotel[valid_from_date]' and '$sq_hotel[valid_to_date]')");
    while($row_hotel_c1= mysqli_fetch_assoc($sq_hotel_c1)){
      $pax = mysqli_num_rows(mysqlQuery("select count(traveler_id) from package_travelers_details where booking_id='$row_hotel_c1[booking_id]'"));
      $total_pax1 =$total_pax1 + $pax;
    }
    
    $avai_rooms = $sq_hotel['total_tickets'] - $total_pax - $total_pax1;
    $avai_rooms = ($avai_rooms<0)?0:$avai_rooms;
    $for = $sq_exc['excursion_name'];

        $content = '
		<tr>
            <table width="85%" cellspacing="0" cellpadding="5" style="color: #888888;border: 1px solid #888888;margin: 0px auto;margin-top:20px; min-width: 100%;" role="presentation">
              <tr><td style="text-align:left;border: 1px solid #888888;">Activity Name </td>   <td style="text-align:left;border: 1px solid #888888;">'.$sq_exc['excursion_name'].'</td></tr>
              <tr><td style="text-align:left;border: 1px solid #888888;"> Total Tickets</td>   <td style="text-align:left;border: 1px solid #888888;" >'.$sq_hotel['total_tickets'].'</td></tr>
              <tr><td style="text-align:left;border: 1px solid #888888;"> Available Tickets</td>   <td style="text-align:left;border: 1px solid #888888;">'.$avai_rooms.'</td></tr>
              <tr><td style="text-align:left;border: 1px solid #888888;"> Cancellation Date</td>   <td style="text-align:left;border: 1px solid #888888;">'.get_date_user($sq_hotel['cancel_date']).'</td></tr>
            </table>
          </tr>';
  }
	$model->app_email_send($type_id,$for,$app_email_id, $content, $subject);
}