<?php

include 'config.php';



$service = $_GET['service'];

global $app_contact_no;

//Include header

include 'layouts/header.php';

$date = date('m-d-Y');

$date1 = str_replace('-', '/', $date);

?>

<!-- ********** Component :: Main Slider ********** -->

<div class="c-mainSlider">

    <div class="js-mainSlider owl-carousel">

        <!-- Slide1 -->

        <?php

        $images = (($cached_array[0]->cms_data[0]->banner_images) != '' && ($cached_array[0]->cms_data[0]->banner_images) != NULL) ? json_decode($cached_array[0]->cms_data[0]->banner_images) : [];

        for ($i = 0; $i < sizeof($images); $i++) {

            $url = $images[$i]->image_url;

            $pos = strstr($url, 'uploads');

            if ($pos != false) {

                $newUrl1 = preg_replace('/(\/+)/', '/', $images[$i]->image_url);

                $download_url = BASE_URL . str_replace('../', '', $newUrl1);
            } else {

                $download_url =  $images[$i]->image_url;
            }

        ?>

            <div class="item">

                <img src="<?php echo $download_url ?>" alt="<?= $app_name ?>" style="border: none;"/>

            </div>

        <?php } ?>

    </div>
    <!-- ********** Component :: Info Section ********** -->
    <!-- ********** Removed filter from here ********** -->
    <!-- ********** Component :: Info Section ********** -->



</div>

<!-- ********** Component :: Main Slider End ********** -->





<!-- Who we are Section Start -->

<section class="ts-who-are-section">

    <div class="container">

        <div class="row">

            <div class="col col-12 col-md-6">

                <div class="ts-who-are-content">

                    <div class="ts-section-heading">

                        <div class="ts-section-subtitle-content" style="padding-left: 0px;">

                            <h2 class="ts-section-subtitle">KNOW MORE</h2>

                            <span class="ts-section-subtitle-icon"><img src="images/traveler.png" alt="traveler" classimg-fluid></span>

                        </div>

                        <h2 class="ts-section-title" style="text-align:left;">WHO WE ARE</h2>

                        <p class="ts-section-description">Discovery is a great company to work with & we have been around for 20 years and more. There are a lot of firsts we have undertaken, bringing smiles and complete satisfaction in the faces of our clients, travellers and everybody who touches us.</p>
                        
                        <p class="ts-section-description">Our main focuses are - Individual travellers, Groups of companies (Meetings, Incentives, and Conferences), social (marriages, anniversaries, and birthdays) or just about any kind groups travelling and cruising. We take care of people anywhere and everywhere around the world.</p>

                    </div>

                </div>

            </div>

            <div class="col col-12 col-md-6">

                <div class="ts-who-are-img">

                    <img src="images/whoweare.png" alt="whoweare" class="img-fluid">

                </div>

            </div>

        </div>

    </div>

</section>

<!-- Who we are Section End -->



<!-- Adventure Section Start -->

<section class="ts-adventure-section">

    <div class="container">

        <h2 class="ta-section-title">EXPLORE THE BEAUTY OF NATURE</h2><br>
        <h6>Find Best deals for Tour Packages, Hotels, Holidays, Bus Reservations for India & International travel, B2b Travel Services.</h6>
        
        <ul>
        <li style="padding-left: 40%;">
                    <a class="btn header-btn-cta" href="<?= BASE_URL_B2C . 'offers.php' ?>">VIEW MORE</a>
                  </li>
        </ul>

    </div>

</section>

<!-- Adventure Section End -->

<?php

$package_tour_data = (($cached_array[0]->cms_data[0]->popular_dest) != '' && $cached_array[0]->cms_data[0]->popular_dest != 'null') ? json_decode($cached_array[0]->cms_data[0]->popular_dest) : [];

$package_tours = (($cached_array[0]->package_tour_data) != '') ? $cached_array[0]->package_tour_data : [];

if (sizeof($package_tour_data) != 0) { ?>

    <!-- Destinations Section Start -->

    <section class="ts-destinations-section">

        <div class="container">

            <div class="ts-section-subtitle-content">

                <h2 class="ts-section-subtitle">PACK AND GO</h2>

                <span class="ts-section-subtitle-icon"><img src="images/traveler.png" alt="traveler" classimg-fluid></span>

            </div>

            <h2 class="ts-section-title">DREAM DESTINATIONS FOR HOLIDAY</h2>


            <div class="ts-blog-content dream-destinations">

                <div class="row">

                    <?php

                    for ($i = 0; $i < sizeof($package_tour_data); $i++) {

                        if ($i > 5) {

                            break;
                        }

                        $package_id = $package_tour_data[$i]->package_id;

                        $url = $package_tour_data[$i]->url;

                        $pos = strstr($url, 'uploads');

                        if ($pos != false) {

                            $newUrl = preg_replace('/(\/+)/', '/', $url);

                            $newUrl1 = BASE_URL . str_replace('../', '', $newUrl);
                        } else {

                            $newUrl1 =  $url;
                        }

                        // Gettign package info

                        $package_array = array();

                        foreach ($package_tours as $subarray) {

                            if (isset($subarray->package_id) && intval($subarray->package_id) == intval($package_id)) {

                                array_push($package_array, $subarray);

                                break;
                            }
                        }

                        //Package file name

                        $package_name = $package_array[0]->package_name;

                        $package_fname = str_replace(' ', '_', $package_name);

                        $file_name = 'package_tours/' . $package_fname . '-' . $package_id . '.php';



                        $total_days = $package_array[0]->total_days;

                        $total_nights = $package_array[0]->total_nights;

                        $note = $package_array[0]->note;



                        $package_name1 = substr($package_name, 0, 22) . '..';

                    ?>

                        <div class="col col-12 col-md-6 col-lg-4">

                            <div class="ts-blog-card">

                                <div class="ts-blog-card-img">

                                    <a href="<?= $file_name ?>" class="ts-blog-card-img-link">

                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                            <path d="M500.3 443.7l-119.7-119.7c27.22-40.41 40.65-90.9 33.46-144.7c-12.23-91.55-87.28-166-178.9-177.6c-136.2-17.24-250.7 97.28-233.4 233.4c11.6 91.64 86.07 166.7 177.6 178.9c53.81 7.191 104.3-6.235 144.7-33.46l119.7 119.7c15.62 15.62 40.95 15.62 56.57 .0003C515.9 484.7 515.9 459.3 500.3 443.7zM288 232H231.1V288c0 13.26-10.74 24-23.1 24C194.7 312 184 301.3 184 288V232H127.1C114.7 232 104 221.3 104 208s10.74-24 23.1-24H184V128c0-13.26 10.74-24 23.1-24S231.1 114.7 231.1 128v56h56C301.3 184 312 194.7 312 208S301.3 232 288 232z" fill="#ffffff" />
                                        </svg>

                                    </a>

                                    <img src="<?= $newUrl1 ?>" alt="Package Image" class="img-fluid ">

                                </div>

                                <div class="ts-blog-card-body">

                                    <a href="<?= $file_name ?>" class="ts-blog-card-title"><?= $package_name1 ?></a>

                                    <p class="ts-blog-time">

                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                            <path d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8zm0 448c-110.5 0-200-89.5-200-200S145.5 56 256 56s200 89.5 200 200-89.5 200-200 200zm61.8-104.4l-84.9-61.7c-3.1-2.3-4.9-5.9-4.9-9.7V116c0-6.6 5.4-12 12-12h32c6.6 0 12 5.4 12 12v141.7l66.8 48.6c5.4 3.9 6.5 11.4 2.6 16.8L334.6 349c-3.9 5.3-11.4 6.5-16.8 2.6z" fill="#f68c34" />
                                        </svg>

                                        <span><?= $total_nights ?> Nights, <?= $total_days ?> Days</span>

                                    </p>

                                    <p class="ts-blog-card-description"><?= $note ?> </p>

                                </div>

                                <div class="ts-blog-card-footer">

                                    <a href="<?= $file_name ?>" target="_blank" class="ts-blog-card-link"> VIEW MORE</a>

                                </div>

                            </div>

                        </div>

                    <?php } ?>

                </div>

            </div>

        </div>

    </section>

    <!-- Destinations Section End -->

<?php } ?>

<?php

$group_tour_data = ($cached_array[0]->cms_data[0]->popular_tours != '' && $cached_array[0]->cms_data[0]->popular_tours != 'null') ? json_decode($cached_array[0]->cms_data[0]->popular_tours) : [];

$group_tours = (($cached_array[0]->group_tour_data) != '') ? $cached_array[0]->group_tour_data : [];

if (sizeof($group_tour_data) != 0) { ?>

    <!-- Destinations Section Start -->

    <section class="ts-destinations-section">

        <div class="container">

            <div class="ts-section-subtitle-content">

                <h2 class="ts-section-subtitle">PACK AND GO</h2>

                <span class="ts-section-subtitle-icon"><img src="images/traveler.png" alt="traveler" classimg-fluid></span>

            </div>

            <h2 class="ts-section-title">DREAM DESTINATIONS FOR GROUP TOUR</h2>



            <div class="ts-blog-content dream-destinations">

                <div class="row">

                    <?php

                    $today_date = strtotime(date('Y-m-d'));

                    for ($i = 0; $i < sizeof($group_tour_data); $i++) {

                        if ($i > 5) {

                            break;
                        }

                        $tour_id = $group_tour_data[$i]->tour_id;

                        $url = $group_tour_data[$i]->url;

                        $pos = strstr($url, 'uploads');

                        if ($pos != false) {

                            $newUrl = preg_replace('/(\/+)/', '/', $url);

                            $newUrl1 = BASE_URL . str_replace('../', '', $newUrl);
                        } else {

                            $newUrl1 =  $url;
                        }

                        // Gettign package info

                        $group_array = array();

                        $tour_groups_array = array();

                        foreach ($group_tours as $subarray) {

                            if (isset($subarray->tour_id) && intval($subarray->tour_id) == intval($tour_id)) {

                                array_push($tour_groups_array, $subarray->tour_groups_array);

                                array_push($group_array, $subarray);

                                break;
                            }
                        }

                        $tour_groups_array = json_decode($tour_groups_array[0]);

                        $valid_dates_array = array();

                        $valid_count = 0;

                        for ($t = 0; $t < sizeof($tour_groups_array); $t++) {



                            $date1_ts = strtotime($tour_groups_array[$t]->from_date);

                            if ($today_date < $date1_ts) {

                                if ($valid_count < 3) {

                                    array_push($valid_dates_array, $tour_groups_array[$t]);

                                    $valid_count++;
                                } else break;
                            }
                        }

                        //Package file name

                        $tour_name = $group_array[0]->tour_name;

                        $package_fname = str_replace(' ', '_', $tour_name);

                        $file_name = 'group_tours/' . $package_fname . '-' . $tour_id . '.php';



                        $total_days = $group_array[0]->total_days;

                        $total_nights = $group_array[0]->total_nights;

                        $note = $group_array[0]->note;



                        $tour_name1 = substr($tour_name, 0, 22) . '..';

                    ?>

                        <div class="col col-12 col-md-6 col-lg-4">

                            <div class="ts-blog-card">

                                <div class="ts-blog-card-img">

                                    <a href="<?= $file_name ?>" class="ts-blog-card-img-link">

                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                            <path d="M500.3 443.7l-119.7-119.7c27.22-40.41 40.65-90.9 33.46-144.7c-12.23-91.55-87.28-166-178.9-177.6c-136.2-17.24-250.7 97.28-233.4 233.4c11.6 91.64 86.07 166.7 177.6 178.9c53.81 7.191 104.3-6.235 144.7-33.46l119.7 119.7c15.62 15.62 40.95 15.62 56.57 .0003C515.9 484.7 515.9 459.3 500.3 443.7zM288 232H231.1V288c0 13.26-10.74 24-23.1 24C194.7 312 184 301.3 184 288V232H127.1C114.7 232 104 221.3 104 208s10.74-24 23.1-24H184V128c0-13.26 10.74-24 23.1-24S231.1 114.7 231.1 128v56h56C301.3 184 312 194.7 312 208S301.3 232 288 232z" fill="#ffffff" />
                                        </svg>

                                    </a>

                                    <img src="<?= $newUrl1 ?>" alt="Package Image" class="img-fluid">

                                </div>

                                <div class="ts-blog-card-body">

                                    <a href="<?= $file_name ?>" class="ts-blog-card-title"><?= $tour_name1 ?></a>

                                    <p class="ts-blog-time">

                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                            <path d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8zm0 448c-110.5 0-200-89.5-200-200S145.5 56 256 56s200 89.5 200 200-89.5 200-200 200zm61.8-104.4l-84.9-61.7c-3.1-2.3-4.9-5.9-4.9-9.7V116c0-6.6 5.4-12 12-12h32c6.6 0 12 5.4 12 12v141.7l66.8 48.6c5.4 3.9 6.5 11.4 2.6 16.8L334.6 349c-3.9 5.3-11.4 6.5-16.8 2.6z" fill="#f68c34" />
                                        </svg>

                                        <span><?= $tour_groups_array[0]->days - 1 ?> Nights, <?= $tour_groups_array[0]->days ?> Days</span>

                                    </p>

                                    <p class="ts-blog-card-description ts-blog-card-description-date">

                                        <?php

                                        for ($v = 0; $v < sizeof($valid_dates_array); $v++) {

                                            echo '<i class="fa fa-calendar" aria-hidden="true"></i>  ' . $valid_dates_array[$v]->from_date . ' To ' . $valid_dates_array[$v]->to_date . '<br/>';
                                        }

                                        ?>

                                    </p>

                                </div>

                                <div class="ts-blog-card-footer">

                                    <a href="<?= $file_name ?>" target="_blank" class="ts-blog-card-link"> READ MORE</a>

                                </div>

                            </div>

                        </div>

                    <?php } ?>

                </div>

            </div>

        </div>

    </section>

    <!-- Destinations Section End -->

<?php } ?>



<!-- Rate Section Start -->

<section class="ts-rate-section">

    <div class="container">

        <div class="row">

            <div class="col col-12 col-md-8 col-lg-5" style="margin-left:0px;">

                <div class="ts-rate-content">

                    <h2 class="ts-section-title">BEST TRAVEL SERVICES</h2>

                    <p class="ts-section-description">Get the best available price for every Packages, Hotel, Flight, Visa or Vehicle booking.<br /> No hidden charges.</p>

                    <ul class="ts-available-rate-list">

                        <li class="ts-available-rate-item">

                            <span class="ts-available-rate-icon">

                                <img src="images/plane.png" alt="plane" class="img-fluid">

                            </span>

                            <h4 class="ts-available-rate-title">FLIGHT</h4>

                        </li>

                        <li class="ts-available-rate-item">

                            <span class="ts-available-rate-icon">

                                <img src="images/bed.png" alt="bed" class="img-fluid">

                            </span>

                            <h4 class="ts-available-rate-title">HOTEL</h4>

                        </li>

                        <li class="ts-available-rate-item">

                            <span class="ts-available-rate-icon">

                                <img src="images/file.svg" alt="file" class="img-fluid">

                            </span>

                            <h4 class="ts-available-rate-title">VISA</h4>

                        </li>

                    </ul>

                </div>

            </div>

        </div>

    </div>

</section>

<!-- Rate Section End -->

<?php

$b2c_testm = ($cached_array[0]->cms_data[4] != '') ? $cached_array[0]->cms_data[4] : [];

$b2c_testmtest = ($b2c_testm->customer_testimonials != '') ? $b2c_testm->customer_testimonials : [];

if (sizeof($b2c_testmtest) != 0) {

?>

    <!-- Testimonial Section Start -->

    <section class="ts-testimonial-section">

        <div class="container">

            <div class="ts-section-subtitle-content">

                <h2 class="ts-section-subtitle">RELAX AND ENJOY</h2>

                <span class="ts-section-subtitle-icon"><img src="images/traveler.png" alt="traveler" classimg-fluid></span>

            </div>

            <h2 class="ts-section-title">HAPPY CUSTOMERS</h2>

            <div class="ts-testimonial-slider owl-carousel">

                <!-- Item Start -->

                <?php

                $testm = $b2c_testmtest;

                for ($testm_count = 0; $testm_count <= sizeof($testm) - 1; $testm_count++) {

                    //Image

                    $url = $testm[$testm_count]->image;

                    $pos = strstr($url, 'uploads');

                    if ($pos != false) {

                        $newUrl = preg_replace('/(\/+)/', '/', $url);

                        $newUrl1 = BASE_URL . str_replace('../', '', $newUrl);
                    } else {

                        $newUrl1 =  $url;
                    }

                    $name = ($testm[$testm_count]->designation != '') ? $testm[$testm_count]->name . '(' . $testm[$testm_count]->designation . ')' : $testm[$testm_count]->name;

                    if ($name != '') {

                ?>

                        <div class="item">

                            <div class="row justify-content-center">

                                <div class="col col-12 col-md-6 col-lg-5">

                                    <div class="ts-testimonial-img">

                                        <img src="<?= $newUrl1 ?>" alt="Customer Image" class="img-fluid">

                                    </div>

                                </div>

                                <div class="col col-12 col-md-6 col-lg-5">

                                    <div class="ts-testimonial-content">

                                        <h3 class="ts-testimonial-name"><?= $name ?></h3>

                                        <p class="ts-testimonial-description"><?= substr($testm[$testm_count]->testm, 0, 400) . '[�K]' ?></p>

                                        <a target='_blank' href="testimonials.php" class="ts-readmore-link">

                                            <span>Read More</span>

                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 192 512">
                                                <!-- Font Awesome Pro 5.15.4 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) -->
                                                <path d="M0 384.662V127.338c0-17.818 21.543-26.741 34.142-14.142l128.662 128.662c7.81 7.81 7.81 20.474 0 28.284L34.142 398.804C21.543 411.404 0 402.48 0 384.662z" fill="#f68c34" />
                                            </svg>

                                        </a>

                                    </div>

                                </div>

                            </div>

                        </div>

                <?php

                    }
                }

                ?>

                <!-- Item End -->

            </div>

        </div>

    </section>

    <!-- Testimonial Section End -->

<?php } ?>



<?php

$b2c_blogs = $cached_array[0]->cms_data[3];

$b2c_blog = ($b2c_blogs->b2c_blogs != '') ? $b2c_blogs->b2c_blogs : [];

if (sizeof($b2c_blog) != 0) { ?>

    <!-- Updates blog Section Start -->

    <section class="ts-update-section">

        <div class="container">

            <div class="ts-section-subtitle-content">

                <h2 class="ts-section-subtitle">EXPLORATIONAL STORIES </h2>

                <span class="ts-section-subtitle-icon"><img src="images/traveler.png" alt="traveler" classimg-fluid></span>

            </div>

            <h2 class="ts-section-title">BLOG AND UPDATES</h2>

            <div class="ts-testimonial-slider owl-carousel">

                <?php

                foreach ($b2c_blogs as $blog) {



                    for ($blog_count = 0; $blog_count < sizeof($blog); $blog_count++) {

                        if ($blog_count > 5) {

                            break;
                        }

                        //Image

                        $url = $blog[$blog_count]->image;

                        $pos = strstr($url, 'uploads');

                        if ($pos != false) {

                            $newUrl = preg_replace('/(\/+)/', '/', $url);

                            $newUrl1 = BASE_URL . str_replace('../', '', $newUrl);
                        } else {

                            $newUrl1 =  $url;
                        }

                ?>

                        <div class="row justify-content-center">

                            <div class="col col-12 col-md-6 col-lg-5">

                                <div class="ts-update-img">

                                    <img src="<?= $newUrl1 ?>" alt="Blog Image" class="img-fluid">

                                </div>

                            </div>

                            <div class="col col-12 col-md-6 col-lg-5">

                                <div class="ts-update-content">

                                    <a href="single-blog.php?blog_id=<?= $blog[$blog_count]->entry_id ?>" target="_blank" class="ts-update-title"><?= $blog[$blog_count]->title ?></a>

                                    <!-- <ul class="ts-update-info-list">

                                        <li class="ts-update-info-item">

                                            <i class="fa fa-user-o"></i>

                                            <a href="#" class="ts-update-info-text">Gauri Salvi</a>

                                        </li>

                                        <li class="ts-update-info-item">

                                        <i class="fa fa-calendar"></i>

                                            <a href="#" class="ts-update-info-text"> August 7, 2019 </a>

                                        </li>

                                        <li class="ts-update-info-item">

                                        <i class="fa fa-files-o"></i>

                                            <a href="#" class="ts-update-info-text">Piligrim Tour</a>

                                        </li>

                                        <li class="ts-update-info-item">

                                            <i class="fa fa-comments-o"></i>

                                            <a href="#" class="ts-update-info-text">0 Comment</a>

                                        </li>

                                    </ul> -->

                                    <p class="ts-updates-description">

                                    <div class="custom_texteditor">

                                        <?= substr($blog[$blog_count]->description, 0, 500) . '[�K]' ?>

                                    </div>
                                    </p>

                                    <a href="single-blog.php?blog_id=<?= $blog[$blog_count]->entry_id ?>" target="_blank" class="ts-readmore-link">

                                        <span>Read More</span>

                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 192 512">
                                            <path d="M0 384.662V127.338c0-17.818 21.543-26.741 34.142-14.142l128.662 128.662c7.81 7.81 7.81 20.474 0 28.284L34.142 398.804C21.543 411.404 0 402.48 0 384.662z" fill="#f68c34" />
                                        </svg>

                                    </a>

                                </div>

                            </div>

                        </div>

                <?php }
                } ?>

            </div>

            <div class="text-center">

                <a href="blog.php" target="_blank" class="btn btn-primary">Read more blogs</a>

            </div>

        </div>

    </section>

    <!-- Updates Section End -->

<?php } ?>



<!-- Contact Section Start -->

<section class="ts-contact-section">

    <div class="container">

        <div class="ts-section-subtitle-content">

            <h2 class="ts-section-subtitle">CONTACT US </h2>

            <span class="ts-section-subtitle-icon"><img src="images/traveler.png" alt="traveler" classimg-fluid></span>

        </div>

        <h2 class="ts-section-title">GET IN TOUCH</h2>

        <div class="row">

            <div class="col col-12 col-md-6 col-lg-5">

                <ul class="ts-contact-info-list">

                    <li class="ts-contact-info-item">

                        <span class="ts-contact-info-icon">

                            <i class="fa fa-map-marker"></i>

                        </span>

                        <spam class="ts-contact-info-link"><?= $cached_array[0]->company_profile_data[0]->address ?></spam>

                    </li>

                    <li class="ts-contact-info-item">

                        <span class="ts-contact-info-icon">

                            <i class="fa fa-phone"></i>

                        </span>

                        <a <?= $cached_array[0]->company_profile_data[0]->contact_no ?>" class="ts-contact-info-link"><?= $cached_array[0]->company_profile_data[0]->contact_no ?></a>

                    </li>

                    <li class="ts-contact-info-item">

                        <span class="ts-contact-info-icon">

                            <i class="fa fa-envelope-o"></i>

                        </span>

                        <a href="mailto:<?= $cached_array[0]->company_profile_data[0]->email_id ?>" class="ts-contact-info-link"><?= $cached_array[0]->company_profile_data[0]->email_id ?></a>

                    </li>

                    <li class="ts-contact-info-item">

                        <span class="ts-contact-info-icon">

                            <i class="fa fa-clock-o"></i>

                        </span>

                        <spam class="ts-contact-info-link"><?= $cached_array[0]->cms_data[0]->header_strip_note ?></spam>

                    </li>

                </ul>

                <ul class="ts-social-media-list">

                    <?php

                    if ($social_media[0]->fb != '') { ?>

                        <li class="ts-social-media-item">

                            <a target="_blank" href="<?= $social_media[0]->fb ?>" class="ts-social-media-link">

                                <span class="ts-contact-info-icon">

                                    <i class="fa fa-facebook"></i>

                                </span>

                            </a>

                        </li>

                    <?php }

                    if ($social_media[0]->inst != '') { ?>

                        <li class="ts-social-media-item">

                            <a target="_blank" href="<?= $social_media[0]->inst ?>" class="ts-social-media-link">

                                <span class="ts-contact-info-icon">

                                    <i class="fa fa-instagram"></i>

                                </span>

                            </a>

                        </li>

                    <?php }

                    if ($social_media[0]->wa != '') { ?>

                        <li class="ts-social-media-item">

                            <a target="_blank" href="<?= $social_media[0]->wa ?>" class="ts-social-media-link">

                                <span class="ts-contact-info-icon">

                                    <i class="fa fa-whatsapp"></i>

                                </span>

                            </a>

                        </li>

                    <?php } ?>

                </ul>

            </div>

            <div class="col col-12 col-md-6 col-lg-7">

                <div class="ts-contact-form">

                    <form id="contact_us_form" class="needs-validation" novalidate>

                        <div class="form-row">

                            <div class="form-group col-md-6">

                                <label for="inputName">Name *</label>

                                <input type="text" class="form-control" id="inputName" name="inputName" placeholder="Name" onkeypress="return blockSpecialChar(event)" required>

                            </div>

                            <div class="form-group col-md-6">

                                <label for="inputEmail">Email *</label>

                                <input type="email" class="form-control" id="inputEmail1" name="inputEmail1" placeholder="Email" required>

                            </div>

                            <div class="form-group col-md-6">

                                <label for="inputPhone">Phone *</label>

                                <input type="number" class="form-control" id="inputPhone" name="inputPhone" placeholder="Phone" required>

                            </div>

                            <div class="form-group col-md-6">

                                <label for="inputState">Interested In *</label>

                                <select id="inputState" name="inputState" class="form-control" required>

                                    <option value="">Select</option>

                                    <option value="Group Tour Package">Group Tour Package</option>

                                    <option value="Holiday Tour Package">Holiday Tour Package</option>

                                    <option value="Hotel Booking">Hotel Booking</option>

                                    <option value="Activities">Activities</option>

                                    <option value="Visa">Visa</option>

                                    <option value="Transfer">Transfer</option>

                                    <option value="Cruise">Cruise</option>

                                    <option value="Other">Other</option>

                                </select>

                            </div>

                        </div>

                        <div class="form-group">

                            <label for="InputMessage">Message*</label>

                            <textarea id="inputMessage" name="inputMessage" rows="8" class="form-control" placeholder="Message" required></textarea>

                        </div>

                        <button type="submit" id="contact_form_send" class="btn btn-primary">Send Message</button>

                    </form>

                </div>

            </div>

        </div>

    </div>

</section>

<!-- Contact Section End -->

<a href="#" class="scrollup">Scroll</a>

<script>
    // Example starter JavaScript for disabling form submissions if there are invalid fields

    (function() {

        'use strict';

        window.addEventListener('load', function() {

            // Fetch all the forms we want to apply custom Bootstrap validation styles to

            var forms = document.getElementsByClassName('needs-validation');

            // Loop over them and prevent submission

            var validation = Array.prototype.filter.call(forms, function(form) {

                form.addEventListener('submit', function(event) {

                    if (form.checkValidity() === false) {

                        event.preventDefault();

                        event.stopPropagation();

                    }

                    form.classList.add('was-validated');

                }, false);

            });

        }, false);

    })();
</script>

<?php

include 'layouts/footer.php';

?>

<script type="text/javascript" src="view/hotel/js/index.js"></script>

<script type="text/javascript" src="view/transfer/js/index.js"></script>

<script type="text/javascript" src="view/activities/js/index.js"></script>

<script type="text/javascript" src="view/tours/js/index.js"></script>

<script type="text/javascript" src="view/group_tours/js/index.js"></script>

<script type="text/javascript" src="js/scripts.js"></script>

<script>
    $(document).ready(function() {

        /////// Next 10th day onwards date display

        var tomorrow = new Date();

        tomorrow.setDate(tomorrow.getDate() + 10);

        var day = tomorrow.getDate();

        var month = tomorrow.getMonth() + 1

        var year = tomorrow.getFullYear();

        $('#travelDate').datetimepicker({
            timepicker: false,
            format: 'm/d/Y',
            minDate: tomorrow
        });



        $('#checkInDate, #checkOutDate, #checkDate').datetimepicker({
            timepicker: false,
            format: 'm/d/Y',
            minDate: new Date()
        });

        $('#pickup_date').datetimepicker({
            format: 'm/d/Y H:i',
            minDate: new Date()
        });

        document.getElementById('return_date').readOnly = true;



        var service = '<?php echo $service; ?>';

        if (service && (service !== '' || service !== undefined)) {

            var checkLink = $('.c-searchContainer .c-search-tabs li');

            var checkTab = $('.c-searchContainer .search-tab-content .tab-pane');

            checkLink.each(function() {

                var child = $(this).children('.nav-link');

                if (child.data('service') === service) {

                    $(this).siblings().children('.nav-link').removeClass('active');

                    child.addClass('active');

                }

            });

            checkTab.each(function() {

                if ($(this).data('service') === service) {

                    $(this).addClass('active show').siblings().removeClass('active show');

                }

            })

        }

    });
</script>