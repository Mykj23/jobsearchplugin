<?php
global $jobsearch_plugin_options, $Jobsearch_User_Dashboard_Settings;
$user_id = get_current_user_id();
$user_obj = get_user_by('ID', $user_id);

$page_id = $user_dashboard_page = isset($jobsearch_plugin_options['user-dashboard-template-page']) ? $jobsearch_plugin_options['user-dashboard-template-page'] : '';
$page_id = $user_dashboard_page = jobsearch__get_post_id($user_dashboard_page, 'page');
$page_url = jobsearch_wpml_lang_page_permalink($page_id, 'page'); //get_permalink($page_id);

$employer_id = jobsearch_get_user_employer_id($user_id);

$reults_per_page = isset($jobsearch_plugin_options['user-dashboard-per-page']) && $jobsearch_plugin_options['user-dashboard-per-page'] > 0 ? $jobsearch_plugin_options['user-dashboard-per-page'] : 10;

$page_num = isset($_GET['page_num']) ? $_GET['page_num'] : 1;

if ($employer_id > 0) {

    $rand_id = rand(1000000, 9999999);
    $args = array(
        'post_type' => 'job',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'fields' => 'ids',
        'order' => 'DESC',
        'orderby' => 'ID',
        'meta_query' => array(
            array(
                'key' => 'jobsearch_field_job_posted_by',
                'value' => $employer_id,
                'compare' => '=',
            ),
            array(
                'key' => 'jobsearch_field_job_status',
                'value' => 'approved',
                'compare' => '=',
            ),
        ),
    );

    $jobs_query = new WP_Query($args);

    $total_jobs = $jobs_query->found_posts;

    $_job_posts = $jobs_query->posts;

    $overall_viewed_cands = 0;
    $job_short_int_count = 0;
    $job_appls_count = 0;
    $job_unviewed_appls_count = 0;

    if (!empty($_job_posts)) {
        foreach ($_job_posts as $_job_post) {
            $viewed_candidates = get_post_meta($_job_post, 'jobsearch_viewed_candidates', true);
            if (empty($viewed_candidates)) {
                $viewed_candidates = array();
            }
            $viewed_candidates = jobsearch_is_post_ids_array($viewed_candidates, 'candidate');
            $viewed_candidates_count = empty($viewed_candidates) ? 0 : count($viewed_candidates);
            $overall_viewed_cands += $viewed_candidates_count;
            //
            $job_short_int_list = get_post_meta($_job_post, '_job_short_interview_list', true);
            $job_short_int_list = $job_short_int_list != '' ? explode(',', $job_short_int_list) : array();
            $job_short_int_list = jobsearch_is_post_ids_array($job_short_int_list, 'candidate');
            $job_short_int_count += count($job_short_int_list);

            //
            $job_applicants_list = get_post_meta($_job_post, 'jobsearch_job_applicants_list', true);
            $job_applicants_list = $job_applicants_list != '' ? explode(',', $job_applicants_list) : array();
            $job_applicants_list = jobsearch_is_post_ids_array($job_applicants_list, 'candidate');
            $job_appls_count += count($job_applicants_list);
        }
        if ($job_appls_count > 0 && $job_appls_count > $overall_viewed_cands) {
            $job_unviewed_appls_count = $job_appls_count - $overall_viewed_cands;
        }
    }
    wp_reset_postdata();

    $employer_resumes_count = 0;
    $employer_resumes_list = get_post_meta($employer_id, 'jobsearch_candidates_list', true);
    if ($employer_resumes_list != '') {
        $employer_resumes_list = explode(',', $employer_resumes_list);
        $employer_resumes_count = count($employer_resumes_list);
    }
    ?>
    <div class="jobsearch-employer-dasboard">
        <div class="jobsearch-employer-box-section">

            <div class="jobsearch-profile-title">
                <h2><?php esc_html_e('Applications statistics', 'wp-jobsearch') ?></h2>
            </div>
            <div class="jobsearch-stats-list">
                <ul>
                    <li>
                        <div class="jobsearch-stats-list-wrap">
                            <h6><?php esc_html_e('Posted jobs', 'wp-jobsearch') ?></h6>
                            <span><?php echo absint($total_jobs) ?></span>
                            <small><?php esc_html_e('to find talent', 'wp-jobsearch') ?></small>
                        </div>
                    </li>
                    <li>
                        <div class="jobsearch-stats-list-wrap green">
                            <h6><?php esc_html_e('Reviewed', 'wp-jobsearch') ?></h6>
                            <span><?php echo absint($overall_viewed_cands) ?></span>
                            <small><?php esc_html_e('CVs against opportunities', 'wp-jobsearch') ?></small>
                        </div>
                    </li>
                    <li>
                        <div class="jobsearch-stats-list-wrap light-blue">
                            <h6><?php esc_html_e('Shortlisted', 'wp-jobsearch') ?></h6>
                            <span><?php echo absint($employer_resumes_count) ?></span>
                            <small><?php esc_html_e('candidates against jobs', 'wp-jobsearch') ?></small>
                        </div>
                    </li>
                    <li>
                        <div class="jobsearch-stats-list-wrap dark-blue">
                            <h6><?php esc_html_e('Interviews', 'wp-jobsearch') ?></h6>
                            <span><?php echo absint($job_short_int_count) ?></span>
                            <small><?php esc_html_e('candidates', 'wp-jobsearch') ?></small>
                        </div>
                    </li>
                </ul>
            </div>
            <?php
            wp_enqueue_script('morris');
            wp_enqueue_script('raphael');
            ?>
            <div class="jobsearch-applicants-graph">
                <div class="jobsearch-chart" id="chart-<?php echo absint($rand_id) ?>"></div>
                <script>
                    jQuery(function () {
                        Morris.Bar({
                            element: 'chart-<?php echo absint($rand_id) ?>',
                            data: [
                                {y: '<?php printf(esc_html__('Total CVs: %s', 'wp-jobsearch'), $job_appls_count) ?>, <?php printf(esc_html__('Unviewed CVs: %s', 'wp-jobsearch'), $job_unviewed_appls_count) ?>, <?php printf(esc_html__('Viewed CVs: %s', 'wp-jobsearch'), $overall_viewed_cands) ?>', item_1: <?php echo ($job_appls_count) ?>, item_2: <?php echo ($job_unviewed_appls_count) ?>, item_3: <?php echo ($overall_viewed_cands) ?>, },
                                            ],
                                            barColors: [
                                                "#008dc9", "#a869d6", "#84c15a"],
                                            xkey: 'y',
                                            ykeys: ["item_1", "item_2", "item_3", ],
                                            labels: [
                                                "<?php esc_html_e('Total CVs', 'wp-jobsearch') ?>",
                                                "<?php esc_html_e('Unviewed CVs', 'wp-jobsearch') ?>",
                                                "<?php esc_html_e('Viewed CVs', 'wp-jobsearch') ?>"
                                            ]
                                        });
                                    });
                </script>
            </div>
            <div class="jobsearch-applicants-stats">
                <div class="jobsearch-applicants-stats-wrap">
                    <i class="fa fa-users"></i>
                    <span><?php echo absint($job_appls_count) ?></span>
                    <small><?php esc_html_e('Total Applicants', 'wp-jobsearch') ?></small>
                </div>
                <ul>
                    <li><i class="fa fa-circle"></i> <?php esc_html_e('Viewed CVs', 'wp-jobsearch') ?></li>
                    <li><i class="fa fa-circle light-blue"></i> <?php esc_html_e('Unviewed CVs', 'wp-jobsearch') ?></li>
                    <li><i class="fa fa-circle dark-blue"></i> <?php esc_html_e('Total CVs', 'wp-jobsearch') ?></li>
                </ul>
            </div>
        </div>
        <div class="jobsearch-employer-box-section">

            <div class="jobsearch-profile-title">
                <h2><?php esc_html_e('Recent Applicants', 'wp-jobsearch') ?></h2>
            </div>
            <?php
            $have_jobs = $have_candidates = false;
            if (!empty($_job_posts)) {
                $have_jobs = true;
                foreach ($_job_posts as $_job_id) {
                    $job_applicants_list = get_post_meta($_job_id, 'jobsearch_job_applicants_list', true);
                    $job_applicants_list = $job_applicants_list != '' ? explode(',', $job_applicants_list) : array();
                    $job_applicants_list = jobsearch_is_post_ids_array($job_applicants_list, 'candidate');
                    $job_applicants_count = count($job_applicants_list);

                    $job_short_int_list = get_post_meta($_job_id, '_job_short_interview_list', true);
                    $job_short_int_list = $job_short_int_list != '' ? explode(',', $job_short_int_list) : '';
                    if (empty($job_short_int_list)) {
                        $job_short_int_list = array();
                    }

                    if (!empty($job_applicants_list)) {
                        arsort($job_applicants_list);
                        $job_views_count = get_post_meta($_job_id, 'jobsearch_job_views_count', true);
                        $job_expiry_date = get_post_meta($_job_id, 'jobsearch_field_job_expiry_date', true);
                        $job_expiry_date = $job_expiry_date == '' ? strtotime(current_time('Y-m-d H:i:s')) : $job_expiry_date;
                        $job_salary = jobsearch_job_offered_salary($_job_id);
                        ?>
                        <div class="jobsearch-job-title">
                            <h2><a href="<?php echo add_query_arg(array('tab' => 'manage-jobs', 'view' => 'applicants', 'job_id' => $_job_id), $page_url) ?>"><?php echo get_the_title($_job_id) ?></a></h2>
                        </div>
                        <div class="jobsearch-recent-applicants-nav">
                            <ul>
                                <li><span><?php echo absint($job_applicants_count) ?></span> <small><?php esc_html_e('Total applicants', 'wp-jobsearch') ?></small></li>
                                <?php
                                if ($job_salary != '') {
                                    ?>
                                    <li><small><?php echo ($job_salary) ?> <?php esc_html_e('Job Salary', 'wp-jobsearch') ?></small></li>
                                    <?php
                                }
                                ?>
                                <li><span><?php echo absint($job_views_count) ?></span> <small><?php esc_html_e('Total visits', 'wp-jobsearch') ?></small></li>
                                <li><small><?php printf(esc_html__('Expiry Date: %s', 'wp-jobsearch'), date_i18n('M d, Y', $job_expiry_date)) ?></small></li>
                            </ul>
                        </div>
                        <div class="jobsearch-candidate jobsearch-candidate-default  jobsearch-applicns-candidate">
                            <ul class="jobsearch-row">
                                <?php
                                $app_counter = 1;
                                foreach ($job_applicants_list as $candidate_id) {
                                    $have_candidates = true;
                                    $candidate_user_id = jobsearch_get_candidate_user_id($candidate_id);
                                    if (absint($candidate_user_id) <= 0) {
                                        continue;
                                    }
                                    $user_def_avatar_url = get_avatar_url($candidate_user_id, array('size' => 69));
                                    $post_thumbnail_id = jobsearch_candidate_get_profile_image($candidate_id);
                                    $post_thumbnail_image = wp_get_attachment_image_src($post_thumbnail_id, 'jobsearch-candidate-medium');
                                    $post_thumbnail_src = isset($post_thumbnail_image[0]) && esc_url($post_thumbnail_image[0]) != '' ? $post_thumbnail_image[0] : $user_def_avatar_url;
                                    $post_thumbnail_src = $post_thumbnail_src == '' ? jobsearch_candidate_image_placeholder() : $post_thumbnail_src;
                                    $jobsearch_candidate_approved = get_post_meta($candidate_id, 'jobsearch_field_candidate_approved', true);
                                    $get_candidate_location = get_post_meta($candidate_id, 'jobsearch_field_location_address', true);

                                    $candidate_city_title = '';
                                    $get_candidate_city = get_post_meta($candidate_id, 'jobsearch_field_location_location3', true);
                                    if ($get_candidate_city == '') {
                                        $get_candidate_city = get_post_meta($candidate_id, 'jobsearch_field_location_location2', true);
                                    }
                                    if ($get_candidate_city != '') {
                                        $get_candidate_country = get_post_meta($candidate_id, 'jobsearch_field_location_location1', true);
                                    }

                                    $candidate_city_tax = $get_candidate_city != '' ? get_term_by('slug', $get_candidate_city, 'job-location') : '';
                                    if (is_object($candidate_city_tax)) {
                                        $candidate_city_title = isset($candidate_city_tax->name) ? $candidate_city_tax->name : '';

                                        $candidate_country_tax = $get_candidate_country != '' ? get_term_by('slug', $get_candidate_country, 'job-location') : '';
                                        if (is_object($candidate_country_tax)) {
                                            $candidate_city_title .= isset($candidate_country_tax->name) ? ', ' . $candidate_country_tax->name : '';
                                        }
                                    }
                                    $jobsearch_candidate_jobtitle = get_post_meta($candidate_id, 'jobsearch_field_candidate_jobtitle', true);
                                    $jobsearch_candidate_company_name = get_post_meta($candidate_id, 'jobsearch_field_candidate_company_name', true);
                                    $jobsearch_candidate_company_url = get_post_meta($candidate_id, 'jobsearch_field_candidate_company_url', true);
                                    $candidate_company_str = '';
                                    if ($jobsearch_candidate_jobtitle != '') {
                                        $candidate_company_str .= $jobsearch_candidate_jobtitle;
                                    }
                                    $candidate_user_obj = get_user_by('ID', $candidate_user_id);
                                    $candidate_user_email = isset($candidate_user_obj->user_email) ? $candidate_user_obj->user_email : '';

                                    $final_color = '';
                                    $candidate_skills = isset($jobsearch_plugin_options['jobsearch_candidate_skills']) ? $jobsearch_plugin_options['jobsearch_candidate_skills'] : '';
                                    if ($candidate_skills == 'on') {

                                        $low_skills_clr = isset($jobsearch_plugin_options['skill_low_set_color']) && $jobsearch_plugin_options['skill_low_set_color'] != '' ? $jobsearch_plugin_options['skill_low_set_color'] : '';
                                        $med_skills_clr = isset($jobsearch_plugin_options['skill_med_set_color']) && $jobsearch_plugin_options['skill_med_set_color'] != '' ? $jobsearch_plugin_options['skill_med_set_color'] : '';
                                        $high_skills_clr = isset($jobsearch_plugin_options['skill_high_set_color']) && $jobsearch_plugin_options['skill_high_set_color'] != '' ? $jobsearch_plugin_options['skill_high_set_color'] : '';
                                        $comp_skills_clr = isset($jobsearch_plugin_options['skill_ahigh_set_color']) && $jobsearch_plugin_options['skill_ahigh_set_color'] != '' ? $jobsearch_plugin_options['skill_ahigh_set_color'] : '';

                                        $overall_candidate_skills = get_post_meta($candidate_id, 'overall_skills_percentage', true);
                                        if ($overall_candidate_skills <= 25 && $low_skills_clr != '') {
                                            $final_color = 'style="color: ' . $low_skills_clr . ';"';
                                        } else if ($overall_candidate_skills > 25 && $overall_candidate_skills <= 50 && $med_skills_clr != '') {
                                            $final_color = 'style="color: ' . $med_skills_clr . ';"';
                                        } else if ($overall_candidate_skills > 50 && $overall_candidate_skills <= 75 && $high_skills_clr != '') {
                                            $final_color = 'style="color: ' . $high_skills_clr . ';"';
                                        } else if ($overall_candidate_skills > 75 && $comp_skills_clr != '') {
                                            $final_color = 'style="color: ' . $comp_skills_clr . ';"';
                                        }
                                    }
                                    ?>
                                    <li class="jobsearch-column-12">
                                        <div class="jobsearch-candidate-default-wrap">
                                            <?php
                                            if ($post_thumbnail_src != '') {
                                                ?>
                                                <figure>
                                                    <a href="<?php the_permalink(); ?>">
                                                        <img src="<?php echo esc_url($post_thumbnail_src) ?>" alt="">
                                                    </a>
                                                </figure>
                                                <?php
                                            }
                                            ?>
                                            <div class="jobsearch-candidate-default-text">
                                                <div class="jobsearch-candidate-default-left">
                                                    <h2>
                                                        <a href="<?php echo esc_url(get_permalink($candidate_id)); ?>">
                                                            <?php echo esc_html(wp_trim_words(get_the_title($candidate_id), 5)); ?>
                                                        </a>
                                                        <?php
                                                        if ($jobsearch_candidate_approved == 'on') {
                                                            ?>
                                                            <i class="jobsearch-icon jobsearch-check-mark" <?php echo ($final_color) ?>></i>
                                                            <?php
                                                        }
                                                        //
                                                        echo apply_filters('jobsearch_dash_stats_apps_list_slist_btn', '', $candidate_id, $_job_id);
                                                        ?>
                                                    </h2>
                                                    <ul>
                                                        <?php
                                                        if ($candidate_company_str != '') {
                                                            ?>
                                                            <li><?php echo ($candidate_company_str); ?></li>
                                                            <?php
                                                        }
                                                        if (!empty($candidate_city_title)) {
                                                            ?>
                                                            <li><i class="fa fa-map-marker"></i> <?php echo esc_html($candidate_city_title); ?></li>
                                                            <?php
                                                        }
                                                        if ($candidate_user_email != '') {
                                                            ?>
                                                            <li>
                                                                <i class="fa fa-envelope"></i> <a href="mailto:<?php echo ($candidate_user_email) ?>"><?php echo ($candidate_user_email) ?></a>
                                                            </li>
                                                            <?php
                                                        }
                                                        ?>
                                                    </ul>
                                                </div>
                                                <?php
                                                if (in_array($candidate_id, $job_short_int_list)) {
                                                    ?>
                                                    <a href="javascript:void(0);" class="jobsearch-candidate-default-btn"><i class="jobsearch-icon jobsearch-add-list"></i> <?php esc_html_e('Shortlisted', 'wp-jobsearch') ?></a>
                                                    <?php
                                                } else {
                                                    ?>
                                                    <a href="javascript:void(0);" class="jobsearch-candidate-default-btn shortlist-cand-to-intrview ajax-enable" data-jid="<?php echo ($_job_id) ?>" data-cid="<?php echo ($candidate_id) ?>"><i class="jobsearch-icon jobsearch-add-list"></i> <?php esc_html_e('Shortlist', 'wp-jobsearch') ?> <span class="app-loader"></span></a>
                                                        <?php
                                                    }
                                                    ?>
                                            </div>
                                        </div>
                                    </li>
                                    <?php
                                    if ($app_counter >= 3) {
                                        break;
                                    }
                                    $app_counter++;
                                }
                                ?>
                            </ul>
                        </div>
                        <?php
                    }
                }
            } else {
                ?>
                <p><?php esc_html_e('No Applicants found.', 'wp-jobsearch') ?></p>
                <?php
            }
            if ($have_jobs === true && $have_candidates === false) {
                ?>
                <p><?php esc_html_e('No Applicants found.', 'wp-jobsearch') ?></p>
                <?php
            }
            ?>
        </div>
    </div>
    <?php
}    