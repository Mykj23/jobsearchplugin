<?php
/**
 * The template for displaying all single posts.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 */
global $post, $jobsearch_plugin_options;
$employer_id = $post->ID;

jobsearch_employer_views_count($employer_id);
get_header();

$captcha_switch = isset($jobsearch_plugin_options['captcha_switch']) ? $jobsearch_plugin_options['captcha_switch'] : '';
$jobsearch_sitekey = isset($jobsearch_plugin_options['captcha_sitekey']) ? $jobsearch_plugin_options['captcha_sitekey'] : '';

$plugin_default_view = isset($jobsearch_plugin_options['jobsearch-default-page-view']) ? $jobsearch_plugin_options['jobsearch-default-page-view'] : 'full';
$plugin_default_view_with_str = '';
if ($plugin_default_view == 'boxed') {

    $plugin_default_view_with_str = isset($jobsearch_plugin_options['jobsearch-boxed-view-width']) && $jobsearch_plugin_options['jobsearch-boxed-view-width'] != '' ? $jobsearch_plugin_options['jobsearch-boxed-view-width'] : '1140px';
    if ($plugin_default_view_with_str != '') {
        $plugin_default_view_with_str = ' style="width:' . $plugin_default_view_with_str . '"';
    }
}

$employer_views_count = get_post_meta($employer_id, "jobsearch_employer_views_count", true);

//
$user_facebook_url = get_post_meta($employer_id, 'jobsearch_field_user_facebook_url', true);
$user_twitter_url = get_post_meta($employer_id, 'jobsearch_field_user_twitter_url', true);
$user_google_plus_url = get_post_meta($employer_id, 'jobsearch_field_user_google_plus_url', true);
$user_youtube_url = get_post_meta($employer_id, 'jobsearch_field_user_youtube_url', true);
$user_dribbble_url = get_post_meta($employer_id, 'jobsearch_field_user_dribbble_url', true);
$user_linkedin_url = get_post_meta($employer_id, 'jobsearch_field_user_linkedin_url', true);

$sectors_enable_switch = isset($jobsearch_plugin_options['sectors_onoff_switch']) ? $jobsearch_plugin_options['sectors_onoff_switch'] : '';

$employer_obj = get_post($employer_id);
$employer_content = $employer_obj->post_content;
$employer_content = apply_filters('the_content', $employer_content);

$employer_join_date = isset($employer_obj->post_date) ? $employer_obj->post_date : '';

$employer_address = get_post_meta($employer_id, 'jobsearch_field_location_address', true);
$employer_phone = get_post_meta($employer_id, 'jobsearch_field_user_phone', true);

$user_id = jobsearch_get_employer_user_id($employer_id);
$user_obj = get_user_by('ID', $user_id);
$user_displayname = isset($user_obj->display_name) ? $user_obj->display_name : '';
$user_displayname = apply_filters('jobsearch_user_display_name', $user_displayname, $user_obj);

$user_def_avatar_url = get_avatar_url($user_id, array('size' => 140));

$user_avatar_id = get_post_thumbnail_id($employer_id);
if ($user_avatar_id > 0) {
    $user_thumbnail_image = wp_get_attachment_image_src($user_avatar_id, 'thumbnail');
    $user_def_avatar_url = isset($user_thumbnail_image[0]) && esc_url($user_thumbnail_image[0]) != '' ? $user_thumbnail_image[0] : '';
}
$user_def_avatar_url = $user_def_avatar_url == '' ? jobsearch_employer_image_placeholder() : $user_def_avatar_url;
wp_enqueue_script('isotope-min');
?>

<div class="jobsearch-main-content">

    <!-- Main Section -->
    <div class="jobsearch-main-section">
        <div class="jobsearch-plugin-default-container" <?php echo force_balance_tags($plugin_default_view_with_str); ?>>
            <div class="jobsearch-row">

                <div class="jobsearch-column-12 jobsearch-typo-wrap">
                    <figure class="jobsearch-jobdetail-list">
                        <span class="jobsearch-jobdetail-listthumb"><img src="<?php echo ($user_def_avatar_url) ?>" alt=""></span>
                        <figcaption>
                            <?php
                            $post_avg_review_args = array(
                                'post_id' => $employer_id,
                            );
                            do_action('jobsearch_post_avg_rating', $post_avg_review_args);
                            ?>
                            <h2><?php echo ($user_displayname) ?></h2>
                            <ul class="jobsearch-jobdetail-options">
                                <?php
                                if (!empty($employer_address)) {
                                    $google_mapurl = 'https://www.google.com/maps/search/' . str_replace(' ', '+', $employer_address);
                                    ?>
                                    <li><i class="fa fa-map-marker"></i> <?php echo ($employer_address) ?> <a href="<?php echo ($google_mapurl) ?>" target="_blank" class="jobsearch-jobdetail-view"><?php esc_html_e('View on Map', 'wp-jobsearch') ?></a></li>
                                    <?php
                                }
                                if (isset($user_obj->user_url) && $user_obj->user_url != '') {
                                    ?>
                                    <li><i class="jobsearch-icon jobsearch-internet"></i> <a href="<?php echo esc_url($user_obj->user_url) ?>"><?php echo esc_url($user_obj->user_url) ?></a></li>
                                    <?php
                                }
                                if (isset($user_obj->user_email) && $user_obj->user_email != '') {
                                    $tr_email = sprintf(__('<a href="mailto: %s">Email: %s</a>', 'wp-jobsearch'), $user_obj->user_email, $user_obj->user_email);
                                    ?>
                                    <li><i class="jobsearch-icon jobsearch-mail"></i> <?php echo wp_kses($tr_email, array('a' => array('href' => array(), 'target' => array(), 'title' => array()))) ?></li>
                                    <?php
                                }
                                if ($employer_phone != '') {
                                    ?>
                                    <li><i class="jobsearch-icon jobsearch-technology"></i> <?php printf(esc_html__('Telephone: %s', 'wp-jobsearch'), $employer_phone) ?></li>
                                    <?php
                                }
                                ?>
                            </ul>
                            <?php
                            $add_review_args = array(
                                'post_id' => $employer_id,
                            );
                            do_action('jobsearch_add_review_btn', $add_review_args);
                            if ($user_facebook_url != '' || $user_twitter_url != '' || $user_linkedin_url != '' || $user_google_plus_url != '' || $user_dribbble_url != '') {
                                ?>
                                <ul class="jobsearch-jobdetail-media jobsearch-add-space">
                                    <li><span><?php esc_html_e('Social Links:', 'wp-jobsearch') ?></span></li>
                                    <?php
                                    if ($user_facebook_url != '') {
                                        ?>
                                        <li><a href="<?php echo ($user_facebook_url) ?>" data-original-title="facebook" class="jobsearch-icon jobsearch-facebook-logo"></a></li>
                                        <?php
                                    }
                                    if ($user_twitter_url != '') {
                                        ?>
                                        <li><a href="<?php echo ($user_twitter_url) ?>" data-original-title="twitter" class="jobsearch-icon jobsearch-twitter-logo"></a></li>
                                        <?php
                                    }
                                    if ($user_linkedin_url != '') {
                                        ?>
                                        <li><a href="<?php echo ($user_linkedin_url) ?>" data-original-title="linkedin" class="jobsearch-icon jobsearch-linkedin-button"></a></li>
                                        <?php
                                    }
                                    if ($user_google_plus_url != '') {
                                        ?>
                                        <li><a href="<?php echo ($user_google_plus_url) ?>" data-original-title="google-plus" class="jobsearch-icon jobsearch-google-plus-logo-button"></a></li>
                                        <?php
                                    }
                                    if ($user_dribbble_url != '') {
                                        ?>
                                        <li><a href="<?php echo ($user_dribbble_url) ?>" data-original-title="dribbble" class="jobsearch-icon jobsearch-dribbble-logo"></a></li>
                                        <?php
                                    }
                                    ?>
                                </ul>
                                <?php
                            }
                            ?>
                        </figcaption>
                    </figure>
                </div>
                <!-- Job Detail List -->

                <!-- Job Detail Content -->
                <div class="jobsearch-column-8 jobsearch-typo-wrap">
                    <?php
                    $custom_all_fields = get_option('jobsearch_custom_field_employer');
                    if (!empty($custom_all_fields) || $employer_content != '') {
                        ?>
                        <div class="jobsearch-jobdetail-content jobsearch-employerdetail-content">
                            <?php
                            if (!empty($custom_all_fields)) {
                                $sector_str = jobsearch_employer_get_all_sectors($employer_id, '', '', '', '<small>', '</small>');
                                ?>
                                <div class="jobsearch-content-title"><h2><?php esc_html_e('Overview', 'wp-jobsearch') ?></h2></div>
                                <div class="jobsearch-jobdetail-services">
                                    <ul class="jobsearch-row">
                                        <?php
                                        if ($sectors_enable_switch == 'on') {
                                            ?>
                                            <li class="jobsearch-column-4">
                                                <i class="jobsearch-icon jobsearch-folder"></i>
                                                <div class="jobsearch-services-text"><?php esc_html_e('Sectors', 'wp-jobsearch') ?> <?php echo wp_kses($sector_str, array('small' => array())) ?></div>
                                            </li>
                                            <?php
                                        }
                                        ?>
                                        <li class="jobsearch-column-4">
                                            <i class="jobsearch-icon jobsearch-briefcase"></i>
                                            <div class="jobsearch-services-text"><?php esc_html_e('Posted Jobs', 'wp-jobsearch') ?> <small><?php echo jobsearch_employer_total_jobs_posted($employer_id) ?></small></div>
                                        </li>
                                        <li class="jobsearch-column-4">
                                            <i class="jobsearch-icon jobsearch-view"></i>
                                            <div class="jobsearch-services-text"><?php esc_html_e('Viewed', 'wp-jobsearch') ?> <small><?php echo ($employer_views_count) ?></small></div>
                                        </li>
                                        <?php
                                        $cus_fields = array('content' => '');
                                        $cus_fields = apply_filters('jobsearch_custom_fields_list', 'employer', $employer_id, $cus_fields, '<li class="jobsearch-column-4">', '</li>');
                                        if (isset($cus_fields['content']) && $cus_fields['content'] != '') {
                                            echo ($cus_fields['content']);
                                        }
                                        ?>
                                    </ul>
                                </div>
                                <?php
                            }
                            if ($employer_content != '') {
                                ?>
                                <div class="jobsearch-content-title"><h2><?php esc_html_e('Company Description', 'wp-jobsearch') ?></h2></div>
                                <div class="jobsearch-description">
                                    <?php echo ($employer_content) ?>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                        <?php
                    }
                    $exfield_list = get_post_meta($employer_id, 'jobsearch_field_team_title', true);
                    $exfield_list_val = get_post_meta($employer_id, 'jobsearch_field_team_description', true);
                    $team_designationfield_list = get_post_meta($employer_id, 'jobsearch_field_team_designation', true);
                    $team_experiencefield_list = get_post_meta($employer_id, 'jobsearch_field_team_experience', true);
                    $team_imagefield_list = get_post_meta($employer_id, 'jobsearch_field_team_image', true);
                    $team_facebookfield_list = get_post_meta($employer_id, 'jobsearch_field_team_facebook', true);
                    $team_googlefield_list = get_post_meta($employer_id, 'jobsearch_field_team_google', true);
                    $team_twitterfield_list = get_post_meta($employer_id, 'jobsearch_field_team_twitter', true);
                    $team_linkedinfield_list = get_post_meta($employer_id, 'jobsearch_field_team_linkedin', true);

                    if (is_array($exfield_list) && sizeof($exfield_list) > 0) {
                        $total_team = sizeof($exfield_list);

                        $rand_num_ul = rand(1000000, 99999999);
                        ?>
                        <div class="jobsearch-employer-wrap-section">
                            <div class="jobsearch-content-title jobsearch-addmore-space"><h2><?php printf(esc_html__('Team Members (%s)', 'wp-jobsearch'), $total_team); ?></h2></div>
                            <div class="jobsearch-candidate jobsearch-candidate-grid">
                                <ul id="members-holder-<?php echo absint($rand_num_ul) ?>" class="jobsearch-row">
                                    <?php
                                    $exfield_counter = 0;
                                    foreach ($exfield_list as $exfield) {
                                        $rand_num = rand(1000000, 99999999);

                                        $exfield_val = isset($exfield_list_val[$exfield_counter]) ? $exfield_list_val[$exfield_counter] : '';
                                        $team_designationfield_val = isset($team_designationfield_list[$exfield_counter]) ? $team_designationfield_list[$exfield_counter] : '';
                                        $team_experiencefield_val = isset($team_experiencefield_list[$exfield_counter]) ? $team_experiencefield_list[$exfield_counter] : '';
                                        $team_imagefield_val = isset($team_imagefield_list[$exfield_counter]) ? $team_imagefield_list[$exfield_counter] : '';
                                        $team_facebookfield_val = isset($team_facebookfield_list[$exfield_counter]) ? $team_facebookfield_list[$exfield_counter] : '';
                                        $team_googlefield_val = isset($team_googlefield_list[$exfield_counter]) ? $team_googlefield_list[$exfield_counter] : '';
                                        $team_twitterfield_val = isset($team_twitterfield_list[$exfield_counter]) ? $team_twitterfield_list[$exfield_counter] : '';
                                        $team_linkedinfield_val = isset($team_linkedinfield_list[$exfield_counter]) ? $team_linkedinfield_list[$exfield_counter] : '';
                                        ?>
                                        <li class="jobsearch-column-4">
                                            <script>
                                                jQuery(document).ready(function () {
                                                    jQuery('a[id^="fancybox_notes"]').fancybox({
                                                        'titlePosition': 'inside',
                                                        'transitionIn': 'elastic',
                                                        'transitionOut': 'elastic',
                                                        'width': 400,
                                                        'height': 250,
                                                        'padding': 40,
                                                        'autoSize': false
                                                    });
                                                });
                                            </script>
                                            <figure>
                                                <a id="fancybox_notes<?php echo ($rand_num) ?>" href="#notes<?php echo ($rand_num) ?>" class="jobsearch-candidate-grid-thumb"><img src="<?php echo ($team_imagefield_val) ?>" alt=""> <span class="jobsearch-candidate-grid-status"></span></a>
                                                <figcaption>
                                                    <h2><a id="fancybox_notes_txt<?php echo ($rand_num) ?>" href="#notes<?php echo ($rand_num) ?>"><?php echo ($exfield) ?></a></h2>
                                                    <p><?php echo ($team_designationfield_val) ?></p>
                                                    <?php
                                                    if ($team_experiencefield_val != '') {
                                                        echo '<span>' . sprintf(esc_html__('Experience: %s', 'wp-jobsearch'), $team_experiencefield_val) . '</span>';
                                                    }
                                                    ?>
                                                </figcaption>
                                            </figure>

                                            <div id="notes<?php echo ($rand_num) ?>" style="display: none;"><?php echo ($exfield_val) ?></div>
                                            <?php
                                            if ($team_facebookfield_val != '' || $team_googlefield_val != '' || $team_twitterfield_val != '' || $team_linkedinfield_val != '') {
                                                ?>
                                                <ul class="jobsearch-social-icons">
                                                    <?php
                                                    if ($team_facebookfield_val != '') {
                                                        ?>
                                                        <li><a href="<?php echo ($team_facebookfield_val) ?>" data-original-title="facebook" class="jobsearch-icon jobsearch-facebook-logo"></a></li>
                                                        <?php
                                                    }
                                                    if ($team_googlefield_val != '') {
                                                        ?>
                                                        <li><a href="<?php echo ($team_googlefield_val) ?>" data-original-title="google-plus" class="jobsearch-icon jobsearch-google-plus-logo-button"></a></li>
                                                        <?php
                                                    }
                                                    if ($team_twitterfield_val != '') {
                                                        ?>
                                                        <li><a href="<?php echo ($team_twitterfield_val) ?>" data-original-title="twitter" class="jobsearch-icon jobsearch-twitter-logo"></a></li>
                                                        <?php
                                                    }
                                                    if ($team_linkedinfield_val != '') {
                                                        ?>
                                                        <li><a href="<?php echo ($team_linkedinfield_val) ?>" data-original-title="linkedin" class="jobsearch-icon jobsearch-linkedin-button"></a></li>
                                                        <?php
                                                    }
                                                    ?>
                                                </ul>
                                                <?php
                                            }
                                            ?>
                                        </li>
                                        <?php
                                        $exfield_counter++;

                                        if ($exfield_counter >= 3) {
                                            break;
                                        }
                                    }
                                    ?>
                                </ul>
                            </div>
                            <?php
                            $reults_per_page = 3;
                            $total_pages = 1;
                            if ($total_team > 0 && $reults_per_page > 0 && $total_team > $reults_per_page) {
                                $total_pages = ceil($total_team / $reults_per_page);
                                ?>
                                <div class="jobsearch-load-more">
                                    <a class="load-more-team" href="javascript:void(0);" data-id="<?php echo ($employer_id) ?>" data-rand="<?php echo ($rand_num_ul) ?>" data-pages="<?php echo ($total_pages) ?>" data-page="1"><?php esc_html_e('Load More', 'wp-jobsearch') ?></a>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                        <?php
                    }
                    //
                    $company_gal_imgs = get_post_meta($employer_id, 'jobsearch_field_company_gallery_imgs', true);
                    $company_gal_videos = get_post_meta($employer_id, 'jobsearch_field_company_gallery_videos', true);
                    if (!empty($company_gal_imgs)) {
                        ?>
                        <div class="jobsearch-employer-wrap-section">
                            <div class="jobsearch-content-title jobsearch-addmore-space"><h2><?php esc_html_e('Office Photos', 'wp-jobsearch') ?></h2></div>
                            <div class="jobsearch-gallery jobsearch-simple-gallery">
                                <ul class="jobsearch-row grid">
                                    <?php
                                    $profile_gal_counter = 1;
                                    $_gal_img_counter = 0;
                                    foreach ($company_gal_imgs as $company_gal_img) {
                                        if ($company_gal_img != '' && absint($company_gal_img) <= 0) {
                                            $company_gal_img = jobsearch_get_attachment_id_from_url($company_gal_img);
                                        }
                                        $gal_thumbnail_image = wp_get_attachment_image_src($company_gal_img, 'large');
                                        $gal_thumb_image_src = isset($gal_thumbnail_image[0]) && esc_url($gal_thumbnail_image[0]) != '' ? $gal_thumbnail_image[0] : '';

                                        $gal_video_url = isset($company_gal_videos[$_gal_img_counter]) && ($company_gal_videos[$_gal_img_counter]) != '' ? $company_gal_videos[$_gal_img_counter] : '';
                                        if ($gal_video_url != '') {
                                            if (strpos($gal_video_url, '?') !== false) {
                                                $gal_video_url .= '&autoplay=1';
                                            } else {
                                                $gal_video_url .= '?autoplay=1';
                                            }
                                        }

                                        $gal_full_image = wp_get_attachment_image_src($company_gal_img, 'full');
                                        $gal_full_image_src = isset($gal_full_image[0]) && esc_url($gal_full_image[0]) != '' ? $gal_full_image[0] : '';
                                        ?>
                                        <li class="grid-item <?php echo ($profile_gal_counter == 2 ? 'jobsearch-column-6' : 'jobsearch-column-3') ?>"> 
                                            <figure>
                                                <span class="grid-item-thumb"><small style="background-image: url('<?php echo ($gal_thumb_image_src) ?>');"></small></span>
                                                <figcaption>
                                                    <div class="img-icons">
                                                        <a href="<?php echo ($gal_video_url != '' ? $gal_video_url : $gal_full_image_src) ?>" class="<?php echo ($gal_video_url != '' ? 'fancybox-video' : 'fancybox') ?>" <?php echo ($gal_video_url != '' ? 'data-fancybox-type="iframe"' : '') ?> data-fancybox-group="group"><i class="<?php echo ($gal_video_url != '' ? 'fa fa-play' : 'fa fa-image') ?>"></i></a>
                                                    </div>
                                                </figcaption>
                                            </figure>
                                        </li>
                                        <?php
                                        $profile_gal_counter++;
                                        $_gal_img_counter++;
                                    }
                                    ?>
                                </ul>
                            </div>
                        </div>
                        <?php
                    }

                    $post_reviews_args = array(
                        'post_id' => $employer_id,
                        'list_label' => esc_html__('Company Reviews', 'wp-jobsearch'),
                    );
                    do_action('jobsearch_post_reviews_list', $post_reviews_args);

                    $review_form_args = array(
                        'post_id' => $employer_id,
                    );
                    do_action('jobsearch_add_review_form', $review_form_args);

                    //
                    $default_date_time_formate = 'd-m-Y H:i:s';
                    $args = array(
                        'posts_per_page' => 20,
                        'paged' => 1,
                        'post_type' => 'job',
                        'post_status' => 'publish',
                        'meta_key' => $meta_key,
                        'order' => 'DESC',
                        'orderby' => 'ID',
                        'meta_query' => array(
                            array(
                                'key' => 'jobsearch_field_job_expiry_date',
                                'value' => strtotime(current_time($default_date_time_formate, 1)),
                                'compare' => '>=',
                            ),
                            array(
                                'key' => 'jobsearch_field_job_status',
                                'value' => 'approved',
                                'compare' => '=',
                            ),
                            array(
                                'key' => 'jobsearch_field_job_posted_by',
                                'value' => $employer_id,
                                'compare' => '=',
                            ),
                        ),
                    );
                    $args = apply_filters('jobsearch_employer_rel_jobs_query_args', $args);
                    $jobs_query = new WP_Query($args);

                    if ($jobs_query->have_posts()) {
                        ?>
                        <div class="jobsearch-margin-top">
                            <div class="jobsearch-section-title"><h2><?php printf(esc_html__('Active Jobs From %s', 'wp-jobsearch'), $user_displayname) ?></h2></div>

                            <?php
                            ob_start();
                            ?>
                            <div class="jobsearch-job jobsearch-joblisting-classic jobsearch-jobdetail-joblisting">
                                <ul class="jobsearch-row">
                                    <?php
                                    while ($jobs_query->have_posts()) : $jobs_query->the_post();
                                        $job_id = get_the_ID();
                                        $post_thumbnail_id = jobsearch_job_get_profile_image($job_id);
                                        $post_thumbnail_image = wp_get_attachment_image_src($post_thumbnail_id, 'thumbnail');
                                        $post_thumbnail_src = isset($post_thumbnail_image[0]) && esc_url($post_thumbnail_image[0]) != '' ? $post_thumbnail_image[0] : '';

                                        $company_name = jobsearch_job_get_company_name($job_id, '@ ');
                                        $jobsearch_job_featured = get_post_meta($job_id, 'jobsearch_field_job_featured', true);
                                        $get_job_location = get_post_meta($job_id, 'jobsearch_field_location_address', true);

                                        $job_city_title = '';
                                        $get_job_city = get_post_meta($job_id, 'jobsearch_field_location_location3', true);
                                        if ($get_job_city == '') {
                                            $get_job_city = get_post_meta($job_id, 'jobsearch_field_location_location2', true);
                                        }
                                        if ($get_job_city == '') {
                                            $get_job_city = get_post_meta($job_id, 'jobsearch_field_location_location1', true);
                                        }

                                        $job_city_tax = $get_job_city != '' ? get_term_by('slug', $get_job_city, 'job-location') : '';
                                        if (is_object($job_city_tax)) {
                                            $job_city_title = $job_city_tax->name;
                                        }

                                        $sector_str = jobsearch_job_get_all_sectors($job_id, '', '', '', '<li><i class="jobsearch-icon jobsearch-filter-tool-black-shape"></i>', '</li>');

                                        $job_type_str = jobsearch_job_get_all_jobtypes($job_id, 'jobsearch-option-btn');
                                        ?>
                                        <li class="jobsearch-column-12">
                                            <div class="jobsearch-joblisting-classic-wrap">
                                                <figure><a href="<?php echo get_permalink($job_id) ?>"><img src="<?php echo ($post_thumbnail_src) ?>" alt=""></a></figure>
                                                <div class="jobsearch-joblisting-text">
                                                    <div class="jobsearch-list-option">
                                                        <h2>
                                                            <a href="<?php echo get_permalink($job_id) ?>"><?php echo get_the_title($job_id) ?></a> 
                                                            <?php
                                                            if ($jobsearch_job_featured == 'on') {
                                                                ?>
                                                                <span><?php echo esc_html__('Featured', 'wp-jobsearch'); ?></span>
                                                                <?php
                                                            }
                                                            ?>
                                                        </h2>
                                                        <ul>
                                                            <?php
                                                            if ($company_name != '') {
                                                                ?>
                                                                <li><?php echo force_balance_tags($company_name); ?></li>
                                                                <?php
                                                            }
                                                            if (!empty($job_city_title)) {
                                                                ?>
                                                                <li><i class="jobsearch-icon jobsearch-maps-and-flags"></i><?php echo esc_html($job_city_title); ?></li>
                                                                <?php
                                                            }

                                                            if (!empty($sector_str) && $sectors_enable_switch == 'on') {
                                                                echo force_balance_tags($sector_str);
                                                            }
                                                            ?>
                                                        </ul>
                                                    </div>
                                                    <div class="jobsearch-job-userlist">
                                                        <?php
                                                        if ($job_type_str != '') {
                                                            echo force_balance_tags($job_type_str);
                                                        }
                                                        $book_mark_args = array(
                                                            'job_id' => $job_id,
                                                            'before_icon' => 'fa fa-heart-o',
                                                            'after_icon' => 'fa fa-heart',
                                                            'anchor_class' => 'jobsearch-job-like'
                                                        );
                                                        do_action('jobsearch_job_shortlist_button_frontend', $book_mark_args);
                                                        ?>
                                                    </div>
                                                    <div class="clearfix"></div>
                                                </div>
                                            </div>
                                        </li>
                                        <?php
                                    endwhile;
                                    wp_reset_postdata();
                                    ?>
                                </ul>
                            </div>
                            <?php
                            $activ_jobs_html = ob_get_clean();
                            echo apply_filters('jobsearch_employer_detail_active_jobs_html', $activ_jobs_html, $jobs_query);
                            ?>
                        </div>
                        <?php
                    }
                    ?>
                </div>
                <!-- Job Detail Content -->
                <!-- Job Detail SideBar -->
                <aside class="jobsearch-column-4 jobsearch-typo-wrap">
                    <?php do_action('jobsearch_employer_detail_side_before_contact_form', array('id' => $employer_id)); ?>
                    <?php
                    $emp_det_contact_form = isset($jobsearch_plugin_options['emp_det_contact_form']) ? $jobsearch_plugin_options['emp_det_contact_form'] : '';
                    if ($emp_det_contact_form == 'on') {
                        ?>
                        <div class="widget widget_contact_form">
                            <?php
                            $cnt_counter = rand(1000000, 9999999);
                            ?>
                            <div class="jobsearch-widget-title"><h2><?php esc_html_e('Contact Form', 'wp-jobsearch') ?></h2></div>
                            <form id="ct-form-<?php echo absint($cnt_counter) ?>" data-uid="<?php echo absint($user_id) ?>" method="post">
                                <ul>
                                    <li>
                                        <label><?php esc_html_e('User Name:', 'wp-jobsearch') ?></label>
                                        <input name="u_name" placeholder="<?php esc_html_e('Enter Your Name', 'wp-jobsearch') ?>" type="text">
                                        <i class="jobsearch-icon jobsearch-user"></i>
                                    </li>
                                    <li>
                                        <label><?php esc_html_e('Email Address:', 'wp-jobsearch') ?></label>
                                        <input name="u_email" placeholder="<?php esc_html_e('Enter Your Email Address', 'wp-jobsearch') ?>" type="text">
                                        <i class="jobsearch-icon jobsearch-mail"></i>
                                    </li>
                                    <li>
                                        <label><?php esc_html_e('Phone Number:', 'wp-jobsearch') ?></label>
                                        <input name="u_number" placeholder="<?php esc_html_e('Enter Your Phone Number', 'wp-jobsearch') ?>" type="text">
                                        <i class="jobsearch-icon jobsearch-technology"></i>
                                    </li>
                                    <li>
                                        <label><?php esc_html_e('Message:', 'wp-jobsearch') ?></label>
                                        <textarea name="u_msg" placeholder="<?php esc_html_e('Type Your Message here', 'wp-jobsearch') ?>"></textarea>
                                    </li>
                                    <?php
                                    if ($captcha_switch == 'on') {
                                        wp_enqueue_script('jobsearch_google_recaptcha');
                                        ?>
                                        <li>
                                            <script>
                                                var recaptcha_empl_contact;
                                                var jobsearch_multicap = function () {
                                                    //Render the recaptcha_empl_contact on the element with ID "recaptcha1"
                                                    recaptcha_empl_contact = grecaptcha.render('recaptcha_empl_contact', {
                                                        'sitekey': '<?php echo ($jobsearch_sitekey); ?>', //Replace this with your Site key
                                                        'theme': 'light'
                                                    });
                                                };
                                                jQuery(document).ready(function () {
                                                    jQuery('.recaptcha-reload-a').click();
                                                });
                                            </script>
                                            <div class="recaptcha-reload" id="recaptcha_empl_contact_div">
                                                <?php echo jobsearch_recaptcha('recaptcha_empl_contact'); ?>
                                            </div>
                                        </li>
                                        <?php
                                    }
                                    ?>
                                    <li>
                                        <?php
                                        jobsearch_terms_and_con_link_txt();
                                        ?>
                                        <input type="submit" class="jobsearch-employer-ct-form" data-id="<?php echo absint($cnt_counter) ?>" value="<?php esc_html_e('Send now', 'wp-jobsearch') ?>">
                                        <?php
                                        if (!is_user_logged_in()) {
                                            ?>
                                            <a class="jobsearch-open-signin-tab" style="display: none;"><?php esc_html_e('login', 'wp-jobsearch') ?></a>
                                            <?php
                                        }
                                        ?>
                                    </li>
                                </ul>
                                <span class="jobsearch-ct-msg"></span>
                            </form>
                        </div>
                        <?php
                    }
                    ?>
                    <div class="widget jobsearch_widget_map">
                        <?php
                        jobsearch_google_map_with_directions($employer_id);
                        ?>
                    </div>
                </aside>

            </div>
        </div>
    </div>
    <!-- Main Section -->

</div>

<?php
get_footer();
