<?php
include "../../model/model.php"; 
include "../../model/attractions_offers_enquiry/fourth_coming_attraction_master.php"; 

$title = $_POST['title'];
$description = $_POST['description'];
$valid_date = $_POST['valid_date'];
$sight_image_path_array = $_POST['sight_image_path_array'];

$fourth_coming_attraction_master = new fourth_coming_attraction_master();
$fourth_coming_attraction_master->fourth_coming_attraction_master_save($title, $description, $valid_date,$sight_image_path_array);
?>