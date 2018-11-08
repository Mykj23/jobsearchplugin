<?php
add_action('jobsearch_translate_job_with_wpml_btn', 'jobsearch_translate_job_with_wpml_btn', 10, 1);

function jobsearch_translate_job_with_wpml_btn($job_id = 0) {
    global $jobsearch_plugin_options, $sitepress;
    if (function_exists('icl_object_id') && $job_id > 0) {
        $page_id = isset($jobsearch_plugin_options['user-dashboard-template-page']) ? $jobsearch_plugin_options['user-dashboard-template-page'] : '';
        $page_id = jobsearch__get_post_id($page_id, 'page');

        $current_lang = $sitepress->get_current_language();
        $languages = icl_get_languages('skip_missing=0&orderby=code');
        if (is_array($languages) && sizeof($languages) > 0) {
            $real_job_id = $job_id;
            $job_id = jobsearch_wpml_is_original($job_id, 'post_job');
            $job_id = isset($job_id['original_ID']) && $job_id['original_ID'] > 0 ? $job_id['original_ID'] : $real_job_id;
            foreach ($languages as $lang_code => $language) {
                $sitepress->switch_lang($lang_code);
                $page_url = jobsearch_wpml_lang_page_permalink($page_id, 'page'); //get_permalink($page_id);
                $page_url = apply_filters('wpml_permalink', $page_url, $lang_code);
                $icl_post_id = icl_object_id($job_id, 'candidate', false, $lang_code);
                $sitepress->switch_lang($current_lang);

                if ($icl_post_id <= 0) {
                    ?>
                    <a class="other-lang-translate-post" href="<?php echo add_query_arg(array('tab' => 'user-job', 'job_id' => $job_id, 'action' => 'update', 'lang' => $lang_code), $page_url) ?>"><?php printf(esc_html__('Translate in %s', 'wp-jobsearch'), (isset($language['translated_name']) ? $language['translated_name'] : '')) ?></a>
                    <?php
                }
            }
        }
    }
}

add_shortcode('jobsearch_user_job', 'jobsearch_user_job_shortcode');

function jobsearch_user_job_shortcode($atts) {
    global $jobsearch_plugin_options, $job_form_errs, $package_form_errs, $jobsearch_currencies_list, $sitepress;
    extract(shortcode_atts(array(
        'title' => '',
                    ), $atts));

    wp_enqueue_script('jobsearch-user-job-posting');
    ob_start();
    $user_id = get_current_user_id();

    $is_candidate = false;
    $is_employer = false;
    if (is_user_logged_in()) {
        $candidate_id = jobsearch_get_user_candidate_id($user_id);
        $is_candidate = $candidate_id > 0 ? true : false;

        $employer_id = jobsearch_get_user_employer_id($user_id);
        $is_employer = $employer_id > 0 ? true : false;
    }

    $lang_code = '';
    if (function_exists('icl_object_id')) {
        $lang_code = $sitepress->get_current_language();
    }

    $job_submit_title = isset($jobsearch_plugin_options['job-submit-title']) ? $jobsearch_plugin_options['job-submit-title'] : '';
    $job_submit_title = apply_filters('wpml_translate_single_string', $job_submit_title, 'JobSearch Options', 'Job Submit Title - ' . $job_submit_title, $lang_code);
    $job_submit_msg = isset($jobsearch_plugin_options['job-submit-msge']) ? $jobsearch_plugin_options['job-submit-msge'] : '';
    $job_submit_msg = apply_filters('wpml_translate_single_string', $job_submit_msg, 'JobSearch Options', 'Job Submit Message - ' . $job_submit_msg, $lang_code);
    $job_submit_img = isset($jobsearch_plugin_options['job-submit-img']) ? $jobsearch_plugin_options['job-submit-img'] : '';

    $job_submit_img_url = isset($job_submit_img['url']) ? $job_submit_img['url'] : '';

    $free_jobs_allow = isset($jobsearch_plugin_options['free-jobs-allow']) ? $jobsearch_plugin_options['free-jobs-allow'] : '';

    $page_id = $user_dashboard_page = isset($jobsearch_plugin_options['user-dashboard-template-page']) ? $jobsearch_plugin_options['user-dashboard-template-page'] : '';
    $page_id = $user_dashboard_page = jobsearch__get_post_id($user_dashboard_page, 'page');
    $page_url = jobsearch_wpml_lang_page_permalink($page_id, 'page'); //get_permalink($page_id);

    $job_salary_types = isset($jobsearch_plugin_options['job-salary-types']) ? $jobsearch_plugin_options['job-salary-types'] : '';

    $is_updating = false;
    $job_id = 0;

    $job_content = '';
    $job_sector = '';
    $job_type = '';
    $_job_filled = '';
    $_job_salary_currency = '';
    $_job_salary_pos = '';
    $_job_salary_deci = '';
    $_job_salary_sep = '';

    if (isset($_GET['job_id']) && $_GET['job_id'] > 0 && jobsearch_is_employer_job($_GET['job_id'])) {
        $real_job_id = $job_id = $_GET['job_id'];

        if (function_exists('icl_object_id')) {
            $job_id = jobsearch_wpml_is_original($job_id, 'post_job');
            $job_id = isset($job_id['original_ID']) && $job_id['original_ID'] > 0 ? $job_id['original_ID'] : $real_job_id;
        }

        $is_updating = true;


        $tr_lang_hap = true;
        if (isset($_GET['step']) && $_GET['step'] == 'confirm') {
            $tr_lang_hap = false;
        }
        $tr_lang_hap = apply_filters('jobsearch_allowflag_translate_job_with_wpml', $tr_lang_hap);
        if (function_exists('icl_object_id') && $tr_lang_hap === true) {
            $current_lang = $sitepress->get_current_language();

            $found_job = icl_object_id($job_id, 'job', false, $current_lang);

            if ($found_job <= 0) {
                $tr_job_obj = get_post($job_id);
                $tr_job_content = $tr_job_obj->post_content;
                $tr_job_content = apply_filters('the_content', $tr_job_content);
                $def_trid = $sitepress->get_element_trid($job_id);
                $ru_args = array(
                    'post_title' => get_the_title($job_id),
                    'post_content' => $tr_job_content,
                    'post_status' => 'publish',
                    'post_type' => 'job'
                );

                $ru_post_id = wp_insert_post($ru_args);
                $sitepress->set_element_language_details($ru_post_id, 'post_job', $def_trid, $current_lang);

                // employer assign
                $g_job_posted_by = get_post_meta($job_id, 'jobsearch_field_job_posted_by', true);
                if ($g_job_posted_by > 0) {
                    $g_job_posted_by = icl_object_id($g_job_posted_by, 'employer', true, $current_lang);
                    update_post_meta($ru_post_id, 'jobsearch_field_job_posted_by', $g_job_posted_by);
                }
                //

                $sector_terms = wp_get_post_terms($job_id, 'sector');
                if (!empty($sector_terms)) {
                    $set_to_terms = array();
                    foreach ($sector_terms as $sector_term) {
                        $tr_tax_id = icl_object_id($sector_term->term_id, 'sector', true, $current_lang);
                        $set_to_terms[] = $sector_term->term_id;
                    }
                    wp_set_post_terms($ru_post_id, $set_to_terms, 'sector', false);
                }

                $type_terms = wp_get_post_terms($job_id, 'jobtype');
                if (!empty($type_terms)) {
                    $set_to_terms = array();
                    foreach ($type_terms as $type_term) {
                        $tr_tax_id = icl_object_id($type_term->term_id, 'jobtype', true, $current_lang);
                        $set_to_terms[] = $type_term->term_id;
                    }
                    wp_set_post_terms($ru_post_id, $set_to_terms, 'jobtype', false);
                }

                $skill_terms = wp_get_post_terms($job_id, 'skill');
                if (!empty($skill_terms)) {
                    $set_to_terms = array();
                    foreach ($skill_terms as $skill_term) {
                        $tr_tax_id = icl_object_id($skill_term->term_id, 'skill', true, $current_lang);
                        $set_to_terms[] = $skill_term->term_id;
                    }
                    wp_set_post_terms($ru_post_id, $set_to_terms, 'skill', false);
                }


                // location
                $loc1_val = get_post_meta($job_id, 'jobsearch_field_location_location1', true);
                if ($loc1_val != '') {
                    $loc_term_obj = get_term_by('slug', $loc1_val, 'job-location');
                    if (is_object($loc_term_obj) && isset($loc_term_obj->term_id)) {
                        $loc_term_id = $loc_term_obj->term_id;
                        $tr_tax_id = icl_object_id($loc_term_id, 'job-location', true, $current_lang);
                        $tr_loc_term_obj = get_term_by('id', $tr_tax_id, 'job-location');
                        update_post_meta($ru_post_id, 'jobsearch_field_location_location1', $tr_loc_term_obj->slug);
                    }
                }
                $loc1_val = get_post_meta($job_id, 'jobsearch_field_location_location2', true);
                if ($loc1_val != '') {
                    $loc_term_obj = get_term_by('slug', $loc1_val, 'job-location');
                    if (is_object($loc_term_obj) && isset($loc_term_obj->term_id)) {
                        $loc_term_id = $loc_term_obj->term_id;
                        $tr_tax_id = icl_object_id($loc_term_id, 'job-location', true, $current_lang);
                        $tr_loc_term_obj = get_term_by('id', $tr_tax_id, 'job-location');
                        update_post_meta($ru_post_id, 'jobsearch_field_location_location2', $tr_loc_term_obj->slug);
                    }
                }
                $loc1_val = get_post_meta($job_id, 'jobsearch_field_location_location3', true);
                if ($loc1_val != '') {
                    $loc_term_obj = get_term_by('slug', $loc1_val, 'job-location');
                    if (is_object($loc_term_obj) && isset($loc_term_obj->term_id)) {
                        $loc_term_id = $loc_term_obj->term_id;
                        $tr_tax_id = icl_object_id($loc_term_id, 'job-location', true, $current_lang);
                        $tr_loc_term_obj = get_term_by('id', $tr_tax_id, 'job-location');
                        update_post_meta($ru_post_id, 'jobsearch_field_location_location3', $tr_loc_term_obj->slug);
                    }
                }
                $loc1_val = get_post_meta($job_id, 'jobsearch_field_location_location4', true);
                if ($loc1_val != '') {
                    $loc_term_obj = get_term_by('slug', $loc1_val, 'job-location');
                    if (is_object($loc_term_obj) && isset($loc_term_obj->term_id)) {
                        $loc_term_id = $loc_term_obj->term_id;
                        $tr_tax_id = icl_object_id($loc_term_id, 'job-location', true, $current_lang);
                        $tr_loc_term_obj = get_term_by('id', $tr_tax_id, 'job-location');
                        update_post_meta($ru_post_id, 'jobsearch_field_location_location4', $tr_loc_term_obj->slug);
                    }
                }

                $application_deadline = get_post_meta($job_id, 'jobsearch_field_job_application_deadline_date', true);
                update_post_meta($ru_post_id, 'jobsearch_field_job_application_deadline_date', $application_deadline);

                $_job_filled = get_post_meta($job_id, 'jobsearch_field_job_filled', true);
                update_post_meta($ru_post_id, 'jobsearch_field_job_filled', $_job_filled);

                $_job_salary_type = get_post_meta($job_id, 'jobsearch_field_job_salary_type', true);
                update_post_meta($ru_post_id, 'jobsearch_field_job_salary_type', $_job_salary_type);

                $_job_apply_type = get_post_meta($job_id, 'jobsearch_field_job_apply_type', true);
                update_post_meta($ru_post_id, 'jobsearch_field_job_apply_type', $_job_apply_type);
                $_job_apply_url = get_post_meta($job_id, 'jobsearch_field_job_apply_url', true);
                update_post_meta($ru_post_id, 'jobsearch_field_job_apply_url', $_job_apply_url);
                $_job_apply_email = get_post_meta($job_id, 'jobsearch_field_job_apply_email', true);
                update_post_meta($ru_post_id, 'jobsearch_field_job_apply_email', $_job_apply_email);

                $_job_salary = get_post_meta($job_id, 'jobsearch_field_job_salary', true);
                update_post_meta($ru_post_id, 'jobsearch_field_job_salary', $_job_salary);
                $_job_max_salary = get_post_meta($job_id, 'jobsearch_field_job_max_salary', true);
                update_post_meta($ru_post_id, 'jobsearch_field_job_max_salary', $_job_max_salary);

                $_job_salary_currency = get_post_meta($job_id, 'jobsearch_field_job_salary_currency', true);
                update_post_meta($ru_post_id, 'jobsearch_field_job_salary_currency', $_job_salary_currency);
                $_job_salary_pos = get_post_meta($job_id, 'jobsearch_field_job_salary_pos', true);
                update_post_meta($ru_post_id, 'jobsearch_field_job_salary_pos', $_job_salary_pos);
                $_job_salary_deci = get_post_meta($job_id, 'jobsearch_field_job_salary_deci', true);
                update_post_meta($ru_post_id, 'jobsearch_field_job_salary_deci', $_job_salary_deci);
                $_job_salary_sep = get_post_meta($job_id, 'jobsearch_field_job_salary_sep', true);
                update_post_meta($ru_post_id, 'jobsearch_field_job_salary_sep', $_job_salary_sep);

                //
                do_action('jobsearch_dashboard_pass_values_to_duplicate_post', $job_id, $ru_post_id, 'job');

                //
                $tr_gal_ids_arr = get_post_meta($job_id, 'jobsearch_field_job_attachment_files', true);
                update_post_meta($ru_post_id, 'jobsearch_field_job_attachment_files', $tr_gal_ids_arr);

                //
                $tr_location_adres = get_post_meta($job_id, 'jobsearch_field_location_address', true);
                if ($tr_location_adres != '') {
                    update_post_meta($ru_post_id, 'jobsearch_field_location_address', $tr_location_adres);
                }
                $tr_location_lat = get_post_meta($job_id, 'jobsearch_field_location_lat', true);
                if ($tr_location_lat != '') {
                    update_post_meta($ru_post_id, 'jobsearch_field_location_lat', $tr_location_lat);
                }
                $tr_location_lng = get_post_meta($job_id, 'jobsearch_field_location_lng', true);
                if ($tr_location_lng != '') {
                    update_post_meta($ru_post_id, 'jobsearch_field_location_lng', $tr_location_lng);
                }
                $tr_location_zoom = get_post_meta($job_id, 'jobsearch_field_location_zoom', true);
                if ($tr_location_zoom != '') {
                    update_post_meta($ru_post_id, 'jobsearch_field_location_zoom', $tr_location_zoom);
                }
                $tr_location_hieght = get_post_meta($job_id, 'jobsearch_field_map_height', true);
                if ($tr_location_hieght != '') {
                    update_post_meta($ru_post_id, 'jobsearch_field_map_height', $tr_location_hieght);
                }
            } else {

                $ru_post_id = $found_job;
            }

            if ($ru_post_id > 0 && $ru_post_id != $job_id) {

                $job_tr_expiry = get_post_meta($job_id, 'jobsearch_field_job_expiry_date', true);
                update_post_meta($ru_post_id, 'jobsearch_field_job_expiry_date', $job_tr_expiry);

                $job_tr_posted = get_post_meta($job_id, 'jobsearch_field_job_publish_date', true);
                update_post_meta($ru_post_id, 'jobsearch_field_job_publish_date', $job_tr_posted);

                $job_tr_status = get_post_meta($job_id, 'jobsearch_field_job_status', true);
                update_post_meta($ru_post_id, 'jobsearch_field_job_status', $job_tr_status);
            }

            //
            $job_id = icl_object_id($job_id, 'job', true, $current_lang);
        }

        $post_obj = get_post($job_id);
        $job_content = $post_obj->post_content;

        $sectors = wp_get_post_terms($job_id, 'sector');
        $job_sector = isset($sectors[0]->term_id) ? $sectors[0]->term_id : '';

        $jobtypes = wp_get_post_terms($job_id, 'jobtype');
        $job_type = isset($jobtypes[0]->term_id) ? $jobtypes[0]->term_id : '';

        $application_deadline = get_post_meta($job_id, 'jobsearch_field_job_application_deadline_date', true);
        if ($application_deadline != '') {
            $application_deadline = date('d-m-Y H:i:s', $application_deadline);
        }

        $_job_filled = get_post_meta($job_id, 'jobsearch_field_job_filled', true);

        $_job_apply_type = get_post_meta($job_id, 'jobsearch_field_job_apply_type', true);
        $_job_apply_url = get_post_meta($job_id, 'jobsearch_field_job_apply_url', true);
        $_job_apply_email = get_post_meta($job_id, 'jobsearch_field_job_apply_email', true);

        $_job_salary_type = get_post_meta($job_id, 'jobsearch_field_job_salary_type', true);
        $_job_salary = get_post_meta($job_id, 'jobsearch_field_job_salary', true);
        $_job_max_salary = get_post_meta($job_id, 'jobsearch_field_job_max_salary', true);

        $_job_salary_currency = get_post_meta($job_id, 'jobsearch_field_job_salary_currency', true);
        $_job_salary_pos = get_post_meta($job_id, 'jobsearch_field_job_salary_pos', true);
        $_job_salary_deci = get_post_meta($job_id, 'jobsearch_field_job_salary_deci', true);
        $_job_salary_sep = get_post_meta($job_id, 'jobsearch_field_job_salary_sep', true);
    }
    ?>
    <div class="jobsearch-employer-dasboard jobsearch-typo-wrap">

        <?php
        $post_job_without_reg = isset($jobsearch_plugin_options['job-post-wout-reg']) ? $jobsearch_plugin_options['job-post-wout-reg'] : '';
        if (!is_user_logged_in() && $post_job_without_reg != 'on') {
            $restrict_img = isset($jobsearch_plugin_options['job_post_restrict_img']) ? $jobsearch_plugin_options['job_post_restrict_img'] : '';
            $restrict_img_url = isset($restrict_img['url']) ? $restrict_img['url'] : '';
            ?>
            <div class="restrict-candidate-sec">
                <img src="<?php echo ($restrict_img_url) ?>" alt="">
                <h2><?php esc_html_e('The Page is accessable only for Subscribed Employers', 'wp-jobsearch') ?></h2>
                <p><?php esc_html_e('If you are employer just login to post a new job.', 'wp-jobsearch') ?></p>
                <div class="login-btns">
                    <a href="javascript:void(0);" class="jobsearch-open-signin-tab"><i class="jobsearch-icon jobsearch-user"></i><?php esc_html_e('Login', 'wp-jobsearch') ?></a>
                    <a href="javascript:void(0);" class="jobsearch-open-register-tab"><i class="jobsearch-icon jobsearch-plus"></i><?php esc_html_e('Become a Employer', 'wp-jobsearch') ?></a>
                </div>
            </div>
            <?php
        } else {
            ?>
            <form id="job-posting-form" method="post"<?php echo (!is_user_logged_in() ? ' class="user-logform-err"' : '') ?> enctype="multipart/form-data">
                <div class="jobsearch-employer-box-section">

                    <?php
                    if (is_user_logged_in() && !$is_employer) {
                        ?>
                        <p><?php esc_html_e('You are not allowed to post job. Only an Employer can Post Job.', 'wp-jobsearch') ?></p>
                        <?php
                    } else {
                        ?>
                        <div class="jobsearch-profile-title">
                            <?php
                            if ($is_updating) {
                                ?>
                                <h2><?php printf(esc_html__('Update "%s"', 'wp-jobsearch'), get_the_title($job_id)) ?></h2>
                                <?php
                            } else {
                                ?>
                                <h2><?php esc_html_e('Post a New Job', 'wp-jobsearch') ?></h2>
                                <?php
                            }
                            ?>
                        </div>
                        <nav class="jobsearch-employer-jobnav">
                            <ul>
                                <li<?php echo (!isset($_GET['step']) ? ' class="active"' : '') ?>><a href="<?php echo ($is_updating && $free_jobs_allow != 'on' ? add_query_arg(array('tab' => 'user-job', 'job_id' => $job_id, 'action' => 'update'), $page_url) : 'javascript:void(0);') ?>"><i class="jobsearch-icon jobsearch-briefcase-1"></i> <span><?php esc_html_e('Job Detail', 'wp-jobsearch') ?></span></a></li>
                                <?php
                                if ($free_jobs_allow != 'on') {
                                    ?>
                                    <li<?php echo ($is_updating && isset($_GET['step']) && $_GET['step'] == 'package' ? ' class="active"' : '') ?>><a href="<?php echo ($is_updating ? add_query_arg(array('tab' => 'user-job', 'job_id' => $job_id, 'step' => 'package', 'action' => 'update'), $page_url) : 'javascript:void(0);') ?>"><i class="jobsearch-icon jobsearch-credit-card"></i> <span><?php esc_html_e('Package & Payments', 'wp-jobsearch') ?></span></a></li>
                                    <?php
                                }
                                ?>
                                <li<?php echo ($is_updating && isset($_GET['step']) && $_GET['step'] == 'confirm' ? ' class="active"' : '') ?>><a href="javascript:void(0);"><i class="jobsearch-icon jobsearch-checked"></i> <span><?php esc_html_e('Confirmation', 'wp-jobsearch') ?></span></a></li>
                            </ul>
                        </nav>
                        <?php
                        $job_detail_tab = true;
                        if ($is_updating && isset($_GET['step']) && $_GET['step'] == 'confirm') {
                            $job_detail_tab = false;
                            $job_confirm_tab = true;
                            ?>
                            <div class="jobsearch-employer-confitmation">
                                <?php
                                if ($job_submit_img_url != '') {
                                    ?>
                                    <img src="<?php echo ($job_submit_img_url) ?>" alt="">
                                    <?php
                                }
                                ?>
                                <h2><?php echo ($job_submit_title) ?></h2>
                                <p><?php echo ($job_submit_msg) ?></p>
                                <div class="clearfix"></div>
                                <a href="<?php echo add_query_arg(array('tab' => 'manage-jobs'), $page_url) ?>"><?php esc_html_e('Manage Jobs', 'wp-jobsearch') ?></a>
                                <a href="<?php echo get_permalink($job_id) ?>"><?php esc_html_e('View Job', 'wp-jobsearch') ?></a>
                            </div>
                            <?php
                        }
                        if ($free_jobs_allow != 'on' && $is_updating && isset($_GET['step']) && $_GET['step'] == 'package') {
                            $job_detail_tab = false;
                            $job_package_tab = true;

                            $job_is_active = jobsearch_check_job_approved_active($job_id);

                            // errors
                            if (isset($package_form_errs[0]) && $package_form_errs[0] != '') {
                                ?>
                                <div class="jobsearch-alert jobsearch-error-alert">
                                    <p><?php echo ($package_form_errs[0]) ?></p>
                                </div>
                                <?php
                            }

                            $args = array(
                                'post_type' => 'package',
                                'posts_per_page' => -1,
                                'post_status' => 'publish',
                                'order' => 'ASC',
                                'orderby' => 'title',
                                'meta_query' => array(
                                    array(
                                        'key' => 'jobsearch_field_package_type',
                                        'value' => 'feature_job',
                                        'compare' => '=',
                                    ),
                                ),
                            );
                            $pkgs_query = new WP_Query($args);

                            if ($pkgs_query->have_posts()) {
                                ?>
                                <div class="jobsearch-employer-payments">
                                    <h2><?php esc_html_e('Featured Job', 'wp-jobsearch') ?></h2>
                                    <?php ?>
                                    <table>
                                        <thead>
                                            <tr>
                                                <th><?php esc_html_e('Select', 'wp-jobsearch') ?></th>
                                                <th><?php esc_html_e('Title', 'wp-jobsearch') ?></th>
                                                <th><?php esc_html_e('Price', 'wp-jobsearch') ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            while ($pkgs_query->have_posts()) : $pkgs_query->the_post();
                                                $pkg_rand = rand(10000000, 99999999);
                                                $pkg_id = get_the_ID();

                                                $pkg_otype = get_post_meta($pkg_id, 'jobsearch_field_package_type', true);

                                                $pkg_type = get_post_meta($pkg_id, 'jobsearch_field_charges_type', true);
                                                $pkg_price = get_post_meta($pkg_id, 'jobsearch_field_package_price', true);

                                                $job_exp_dur = get_post_meta($pkg_id, 'jobsearch_field_job_expiry_time', true);
                                                $job_exp_dur_unit = get_post_meta($pkg_id, 'jobsearch_field_job_expiry_time_unit', true);

                                                $pkg_exp_dur = get_post_meta($pkg_id, 'jobsearch_field_package_expiry_time', true);
                                                $pkg_exp_dur_unit = get_post_meta($pkg_id, 'jobsearch_field_package_expiry_time_unit', true);
                                                ?>
                                                <tr>
                                                    <td>
                                                        <div class="jobsearch-payments-checkbox">
                                                            <input id="pkg-<?php echo absint($pkg_rand) ?>" name="job_package_featured" value="<?php echo absint($pkg_id) ?>" type="radio">
                                                            <label for="pkg-<?php echo absint($pkg_rand) ?>"><span></span></label>
                                                        </div>
                                                    </td>
                                                    <td><span><?php echo get_the_title($pkg_id) ?></span> <small><?php esc_html_e('Select this package to make your job Featured.', 'wp-jobsearch') ?></small></td>
                                                    <td>
                                                        <?php
                                                        if ($pkg_type == 'paid') {
                                                            echo jobsearch_get_price_format($pkg_price);
                                                        } else {
                                                            esc_html_e('Free', 'wp-jobsearch');
                                                        }
                                                        ?>
                                                    </td>
                                                </tr>
                                                <?php
                                            endwhile;
                                            wp_reset_postdata();
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php
                            }

                            $args = array(
                                'post_type' => 'shop_order',
                                'posts_per_page' => -1,
                                'post_status' => 'wc-completed',
                                'order' => 'DESC',
                                'orderby' => 'ID',
                                'meta_query' => array(
                                    array(
                                        'key' => 'package_type',
                                        'value' => apply_filters('jobsearch_emp_postjob_pkg_job_metakey', 'job'),
                                        'compare' => '=',
                                    ),
                                    array(
                                        'key' => 'package_expiry_timestamp',
                                        'value' => strtotime(current_time('d-m-Y H:i:s', 1)),
                                        'compare' => '>',
                                    ),
                                    array(
                                        'key' => 'jobsearch_order_user',
                                        'value' => $user_id,
                                        'compare' => '=',
                                    ),
                                ),
                            );
                            $pkgs_query = new WP_Query($args);
                            if ($pkgs_query->have_posts()) {
                                ?>
                                <div class="jobsearch-employer-payments">
                                    <h2><?php esc_html_e('Select Already Buy Package', 'wp-jobsearch') ?></h2>
                                    <table>
                                        <thead>
                                            <tr>
                                                <th><?php esc_html_e('Select', 'wp-jobsearch') ?></th>
                                                <th><?php esc_html_e('Title', 'wp-jobsearch') ?></th>
                                                <th><?php esc_html_e('Job Expiry', 'wp-jobsearch') ?></th>
                                                <th><?php echo apply_filters('jobsearch_emp_postjob_pkg_job_num_label', esc_html__('Total Jobs', 'wp-jobsearch')) ?></th>
                                                <th><?php esc_html_e('Used', 'wp-jobsearch') ?></th>
                                                <th><?php esc_html_e('Remaining', 'wp-jobsearch') ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            while ($pkgs_query->have_posts()) : $pkgs_query->the_post();
                                                $pkg_rand = rand(10000000, 99999999);
                                                $pkg_order_id = get_the_ID();
                                                $pkg_order_name = get_post_meta($pkg_order_id, 'package_name', true);

                                                $total_jobs = get_post_meta($pkg_order_id, 'num_of_jobs', true);
                                                $total_jobs = apply_filters('jobsearch_emp_dash_pkg_total_jobs_count', $total_jobs, $pkg_order_id);

                                                $job_exp_dur = get_post_meta($pkg_order_id, 'job_expiry_time', true);
                                                $job_exp_dur_unit = get_post_meta($pkg_order_id, 'job_expiry_time_unit', true);

                                                $used_jobs = jobsearch_pckg_order_used_jobs($pkg_order_id);
                                                $used_jobs = apply_filters('jobsearch_emp_dash_pkg_used_jobs_count', $used_jobs, $pkg_order_id);
                                                $remaining_jobs = jobsearch_pckg_order_remaining_jobs($pkg_order_id);
                                                $remaining_jobs = apply_filters('jobsearch_emp_dash_pkg_remain_jobs_count', $remaining_jobs, $pkg_order_id);

                                                $pkg_expired = apply_filters('jobsearch_emp_postjob_pkg_expiry_check', jobsearch_pckg_order_is_expired($pkg_order_id), $pkg_order_id);
                                                if ($pkg_expired) {
                                                    continue;
                                                }
                                                ob_start();
                                                ?>
                                                <tr>
                                                    <td>
                                                        <div class="jobsearch-payments-checkbox">
                                                            <input id="pkg-<?php echo absint($pkg_rand) ?>" name="job_subs_package" value="<?php echo absint($pkg_order_id) ?>" type="radio">
                                                            <label for="pkg-<?php echo absint($pkg_rand) ?>"><span></span></label>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        ob_start();
                                                        ?>
                                                        <span><?php echo ($pkg_order_name) ?></span>
                                                        <?php
                                                        $pkg_name_html = ob_get_clean();
                                                        echo apply_filters('jobsearch_emp_dashboard_pkgs_list_pkg_title', $pkg_name_html, $pkg_order_id);
                                                        ?>
                                                    </td>
                                                    <td><?php echo absint($job_exp_dur) . ' ' . jobsearch_get_duration_unit_str($job_exp_dur_unit) ?></td>
                                                    <td><?php echo absint($total_jobs) ?></td>
                                                    <td><?php echo absint($used_jobs) ?></td>
                                                    <td><?php echo absint($remaining_jobs) ?></td>
                                                </tr>
                                                <?php
                                                $buy_exst_item_html = ob_get_clean();
                                                $buy_exst_item_html = apply_filters('jobsearch_postjob_pkgs_buyexist_item_html', $buy_exst_item_html, $pkg_order_id, $job_id);
                                                echo ($buy_exst_item_html);
                                            endwhile;
                                            wp_reset_postdata();
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php
                                ob_start();
                                ?>
                                <div class="restrict-candidate-sec">
                                    <div class="jobsearch-box-title">
                                        <span><?php esc_html_e('OR', 'wp-jobsearch') ?></span>
                                    </div>
                                </div>
                                <?php
                                $buy_or_html = ob_get_clean();
                                $buy_or_html = apply_filters('jobsearch_postjob_pkgs_buyor_html', $buy_or_html, $job_id);
                                echo ($buy_or_html);
                            }
                            ob_start();
                            ?>
                            <div class="jobsearch-employer-payments">
                                <h2><?php esc_html_e('Buy new Package', 'wp-jobsearch') ?></h2>
                                <?php
                                $args = array(
                                    'post_type' => 'package',
                                    'posts_per_page' => -1,
                                    'post_status' => 'publish',
                                    'order' => 'ASC',
                                    'orderby' => 'title',
                                    'meta_query' => array(
                                        array(
                                            'key' => 'jobsearch_field_package_type',
                                            'value' => apply_filters('jobsearch_emp_postjob_pkg_job_metakey', 'job'),
                                            'compare' => '=',
                                        ),
                                    ),
                                );
                                $pkgs_query = new WP_Query($args);

                                if ($pkgs_query->have_posts()) {
                                    ?>
                                    <table>
                                        <thead>
                                            <tr>
                                                <th><?php esc_html_e('Select', 'wp-jobsearch') ?></th>
                                                <th><?php esc_html_e('Title', 'wp-jobsearch') ?></th>
                                                <th><?php esc_html_e('Price', 'wp-jobsearch') ?></th>
                                                <th><?php echo apply_filters('jobsearch_emp_postjob_pkg_job_num_label', esc_html__('Total Jobs', 'wp-jobsearch')) ?></th>
                                                <th><?php esc_html_e('Job Expiry', 'wp-jobsearch') ?></th>
                                                <th><?php esc_html_e('Package Expiry', 'wp-jobsearch') ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            while ($pkgs_query->have_posts()) : $pkgs_query->the_post();
                                                $pkg_rand = rand(10000000, 99999999);
                                                $pkg_id = get_the_ID();

                                                $pkg_otype = get_post_meta($pkg_id, 'jobsearch_field_package_type', true);

                                                $pkg_type = get_post_meta($pkg_id, 'jobsearch_field_charges_type', true);
                                                $pkg_price = get_post_meta($pkg_id, 'jobsearch_field_package_price', true);

                                                if ($pkg_otype == 'feature_job') {
                                                    $total_jobs = '-';
                                                } else {
                                                    $total_jobs = get_post_meta($pkg_id, 'jobsearch_field_num_of_jobs', true);
                                                    $total_jobs = apply_filters('jobsearch_emp_postjob_pkg_bp_jobs_count', $total_jobs, $pkg_id);
                                                }

                                                $job_exp_dur = get_post_meta($pkg_id, 'jobsearch_field_job_expiry_time', true);
                                                $job_exp_dur_unit = get_post_meta($pkg_id, 'jobsearch_field_job_expiry_time_unit', true);

                                                $pkg_exp_dur = get_post_meta($pkg_id, 'jobsearch_field_package_expiry_time', true);
                                                $pkg_exp_dur_unit = get_post_meta($pkg_id, 'jobsearch_field_package_expiry_time_unit', true);
                                                ob_start();
                                                ?>
                                                <tr>
                                                    <td>
                                                        <div class="jobsearch-payments-checkbox">
                                                            <input id="pkg-<?php echo absint($pkg_rand) ?>" name="job_package_new" value="<?php echo absint($pkg_id) ?>" type="radio">
                                                            <label for="pkg-<?php echo absint($pkg_rand) ?>"><span></span></label>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        ob_start();
                                                        ?>
                                                        <span><?php echo get_the_title($pkg_id) ?></span>
                                                        <?php
                                                        $pkg_name_html = ob_get_clean();
                                                        echo apply_filters('jobsearch_emp_dashboard_pkgs_list_pkg_title', $pkg_name_html, $pkg_id, 'package');
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        if ($pkg_type == 'paid') {
                                                            echo jobsearch_get_price_format($pkg_price);
                                                        } else {
                                                            esc_html_e('Free', 'wp-jobsearch');
                                                        }
                                                        ?>
                                                    </td>
                                                    <td><?php echo ($total_jobs) ?></td>
                                                    <td><?php echo absint($job_exp_dur) . ' ' . jobsearch_get_duration_unit_str($job_exp_dur_unit) ?></td>
                                                    <td><?php echo absint($pkg_exp_dur) . ' ' . jobsearch_get_duration_unit_str($pkg_exp_dur_unit) ?></td>
                                                </tr>
                                                <?php
                                                $buy_new_item_html = ob_get_clean();
                                                $buy_new_item_html = apply_filters('jobsearch_postjob_pkgs_buynew_item_html', $buy_new_item_html, $pkg_id, $job_id);
                                                echo ($buy_new_item_html);
                                            endwhile;
                                            wp_reset_postdata();
                                            ?>
                                        </tbody>
                                    </table>
                                    <?php
                                } else {
                                    ?>
                                    <p><?php esc_html_e('No Package found.', 'wp-jobsearch') ?></p>
                                    <?php
                                }
                                ?>
                            </div>
                            <?php
                            $buy_new_html = ob_get_clean();
                            $buy_new_html = apply_filters('jobsearch_postjob_pkgs_buynew_html', $buy_new_html, $job_id);
                            echo ($buy_new_html);
                        } else {
                            if ($job_detail_tab) {
                                if (isset($job_form_errs['post_errors']) && $job_form_errs['post_errors'] != '') {
                                    ?>
                                    <div class="jobsearch-alert jobsearch-error-alert">
                                        <p><?php echo ($job_form_errs['post_errors']) ?></p>
                                    </div>
                                    <?php
                                }
                                ?>
                                <script>
                                    jQuery(document).ready(function () {
                                        jQuery('#jobsearch_job_application_deadline').datetimepicker({
                                            timepicker: true,
                                            format: 'd-m-Y H:i:s'
                                        });
                                    });
                                </script>
                                <ul class="jobsearch-row jobsearch-employer-profile-form">
                                    <li class="jobsearch-column-12">
                                        <label><?php esc_html_e('Job Title *', 'wp-jobsearch') ?></label>
                                        <input id="ad-posting-title" class="jobsearch-req-field" <?php echo ($is_updating ? 'value="' . get_the_title($job_id) . '"' : '') ?> name="job_title" type="text">
                                        <span class="field-error"></span>
                                    </li>
                                    <li class="jobsearch-column-12">
                                        <label><?php esc_html_e('Description *', 'wp-jobsearch') ?></label>
                                        <?php
                                        $settings = array(
                                            'media_buttons' => false,
                                            'editor_class' => 'jobsearch-req-field',
                                            'quicktags' => array('buttons' => 'strong,em,del,ul,ol,li,close'),
                                            'tinymce' => array(
                                                'toolbar1' => 'bold,bullist,numlist,italic,underline,alignleft,aligncenter,alignright,separator,link,unlink,undo,redo',
                                                'toolbar2' => '',
                                                'toolbar3' => '',
                                            ),
                                        );

                                        wp_editor($job_content, 'job_detail', $settings);
                                        ?>
                                        <span class="field-error"></span>
                                    </li>
                                    <?php
                                    if (!is_user_logged_in()) {
                                        ?>
                                        <li class="jobsearch-column-6">
                                            <label><?php esc_html_e('Email Address *', 'wp-jobsearch') ?></label>
                                            <input type="text" name="reg_user_email" class="jobsearch-req-field">
                                            <span class="field-error"></span>
                                        </li>
                                        <li class="jobsearch-column-6">
                                            <label><?php esc_html_e('Username *', 'wp-jobsearch') ?></label>
                                            <input type="text" name="reg_user_uname" class="jobsearch-req-field">
                                            <span class="field-error"></span>
                                        </li>
                                        <?php
                                    }

                                    $sectors_enable_switch = isset($jobsearch_plugin_options['sectors_onoff_switch']) ? $jobsearch_plugin_options['sectors_onoff_switch'] : '';

                                    $job_apply_deadline_sw = isset($jobsearch_plugin_options['job_appliction_deadline']) ? $jobsearch_plugin_options['job_appliction_deadline'] : '';

                                    //
                                    $fil_col_class = 'jobsearch-column-4';
                                    $fill_field = false;
                                    if ($is_updating) {
                                        $job_status = get_post_meta($job_id, 'jobsearch_field_job_status', true);
                                        $job_applicants_list = get_post_meta($job_id, 'jobsearch_job_applicants_list', true);
                                        $job_applicants_list = jobsearch_is_post_ids_array($job_applicants_list, 'candidate');

                                        if ($job_status == 'approved') {
                                            if ($job_apply_deadline_sw == 'on') {
                                                $fil_col_class = 'jobsearch-column-6';
                                            }
                                            $fill_field = true;
                                        }
                                    } else if ($job_apply_deadline_sw != 'on') {
                                        $fil_col_class = 'jobsearch-column-6';
                                    }
                                    if ($job_apply_deadline_sw == 'on') {
                                        ?>
                                        <li class="<?php echo ($fil_col_class) ?>">
                                            <label><?php esc_html_e('Application Deadline', 'wp-jobsearch') ?></label>
                                            <input type="text" id="jobsearch_job_application_deadline" name="application_deadline" <?php echo ($is_updating ? 'value="' . ($application_deadline) . '"' : '') ?>>
                                        </li>
                                        <?php
                                    }
                                    if ($fill_field) {
                                        ?>
                                        <li class="<?php echo ($fil_col_class) ?>">
                                            <label><?php esc_html_e('Filled Job', 'wp-jobsearch') ?></label>
                                            <div class="jobsearch-profile-select">
                                                <select name="job_filled" class="selectize-select">
                                                    <option value="off" <?php echo ($_job_filled == 'off' ? 'selected="selected"' : '') ?>><?php esc_html_e('No', 'wp-jobsearch') ?></option>
                                                    <option value="on" <?php echo ($_job_filled == 'on' ? 'selected="selected"' : '') ?>><?php esc_html_e('Yes', 'wp-jobsearch') ?></option>
                                                </select>
                                            </div>
                                        </li>
                                        <?php
                                    }

                                    if ($sectors_enable_switch == 'on') {
                                        ?>
                                        <li class="<?php echo ($fil_col_class) ?>">
                                            <label><?php esc_html_e('Job Sector *', 'wp-jobsearch') ?></label>
                                            <div class="jobsearch-profile-select">
                                                <?php
                                                $sector_args = array(
                                                    'show_option_all' => esc_html__('Select Sector', 'wp-jobsearch'),
                                                    'show_option_none' => '',
                                                    'class' => 'jobsearch-req-field',
                                                    'option_none_value' => '',
                                                    'orderby' => 'title',
                                                    'order' => 'ASC',
                                                    'show_count' => 0,
                                                    'hide_empty' => 0,
                                                    'echo' => 1,
                                                    'selected' => $job_sector,
                                                    'hierarchical' => 1,
                                                    'id' => 'job-sector',
                                                    'name' => 'job_sector',
                                                    'depth' => 0,
                                                    'taxonomy' => 'sector',
                                                    'hide_if_empty' => false,
                                                    'value_field' => 'term_id',
                                                );
                                                wp_dropdown_categories($sector_args);
                                                ?>
                                            </div>
                                        </li>
                                        <?php
                                    }
                                    ?>
                                    <li class="<?php echo ($fil_col_class) ?>">
                                        <label><?php esc_html_e('Job Type *', 'wp-jobsearch') ?></label>
                                        <div class="jobsearch-profile-select">
                                            <?php
                                            $sector_args = array(
                                                'show_option_all' => esc_html__('Select Type', 'wp-jobsearch'),
                                                'show_option_none' => '',
                                                'class' => 'jobsearch-req-field',
                                                'option_none_value' => '',
                                                'orderby' => 'title',
                                                'order' => 'ASC',
                                                'show_count' => 0,
                                                'hide_empty' => 0,
                                                'echo' => 1,
                                                'selected' => $job_type,
                                                'hierarchical' => 1,
                                                'id' => 'job-type',
                                                'name' => 'job_type',
                                                'depth' => 0,
                                                'taxonomy' => 'jobtype',
                                                'hide_if_empty' => false,
                                                'value_field' => 'term_id',
                                            );
                                            wp_dropdown_categories($sector_args);
                                            ?>
                                        </div>
                                    </li>
                                    <?php
                                    $job_skills_switch = isset($jobsearch_plugin_options['job-skill-switch']) ? $jobsearch_plugin_options['job-skill-switch'] : '';
                                    $job_max_skills_allow = isset($jobsearch_plugin_options['job_max_skills']) && $jobsearch_plugin_options['job_max_skills'] > 0 ? $jobsearch_plugin_options['job_max_skills'] : 5;
                                    $job_sugg_skills_allow = isset($jobsearch_plugin_options['job_sugg_skills']) && $jobsearch_plugin_options['job_sugg_skills'] > 0 ? $jobsearch_plugin_options['job_sugg_skills'] : 0;

                                    if ($job_skills_switch == 'on') {
                                        ?>
                                        <li class="jobsearch-column-12">
                                            <?php
                                            wp_enqueue_script('jobsearch-tag-it');
                                            $job_saved_skills = array();
                                            if ($is_updating) {
                                                $job_saved_skills = wp_get_post_terms($job_id, 'skill');
                                            }
                                            ?>
                                            <div class="jobseach-skills-con">
                                                <script type="text/javascript">
                                                    jQuery(document).ready(function () {
                                                        jQuery('#job-skills').tagit({
                                                            allowSpaces: true,
                                                            tagLimit: '<?php echo ($job_max_skills_allow) ?>',
                                                            placeholderText: '<?php esc_html_e('Add Skills', 'wp-jobsearch') ?>',
                                                            fieldName: 'get_job_skills[]',
                                                            onTagLimitExceeded: function (event, ui) {
                                                                jQuery(".tagit-new input").val("");
                                                                alert('<?php printf(esc_html__('Only %s skills allowed.', 'wp-jobsearch'), $job_max_skills_allow) ?>');
                                                            }
                                                        });
                                                    });
                                                    jQuery(document).on('focus', '.tagit-new input', function () {
                                                        var _this = jQuery(this);
                                                        _this.parents('.jobseach-skills-con').find('.suggested-skills-con').slideDown();
                                                    });
                                                    jQuery(document).on('click', 'body', function (evt) {
                                                        var target = evt.target;
                                                        var this_box = jQuery('.jobseach-skills-con');
                                                        if (!this_box.is(evt.target) && this_box.has(evt.target).length === 0) {
                                                            this_box.find('.suggested-skills-con').slideUp();
                                                        }
                                                    });
                                                </script>
                                                <label><?php esc_html_e('Required Skills', 'wp-jobsearch') ?></label>
                                                <ul id="job-skills" class="jobseach-job-skills">
                                                    <?php
                                                    if (!empty($job_saved_skills)) {
                                                        foreach ($job_saved_skills as $job_saved_skill) {
                                                            ?>
                                                            <li><?php echo ($job_saved_skill->name) ?></li>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </ul>
                                                <?php
                                                if ($job_sugg_skills_allow > 0) {
                                                    $skills_terms = get_terms(array(
                                                        'taxonomy' => 'skill',
                                                        'orderby' => 'count',
                                                        'number' => $job_sugg_skills_allow,
                                                        'hide_empty' => false,
                                                    ));
                                                    if (!empty($skills_terms) && !is_wp_error($skills_terms)) {
                                                        ob_start();
                                                        ?>
                                                        <div class="suggested-skills-con">
                                                            <label><?php esc_html_e('Suggested Skills', 'wp-jobsearch') ?></label>
                                                            <ul class="suggested-skills">
                                                                <?php
                                                                foreach ($skills_terms as $skill_term) {
                                                                    ?>
                                                                    <li class="skills-cloud" onclick="jQuery('#job-skills').tagit('createTag', '<?php echo ($skill_term->name) ?>'); return false;"><?php echo ($skill_term->name) ?></li>
                                                                    <?php
                                                                }
                                                                ?>
                                                            </ul>
                                                        </div>
                                                        <?php
                                                        $html = ob_get_clean();
                                                        echo apply_filters('jobsearch_post_job_sugg_skills_html', $html, $skills_terms);
                                                    }
                                                }
                                                ?>
                                            </div>
                                        </li>
                                        <?php
                                    }
                                    $job_extrnal_apply_switch = isset($jobsearch_plugin_options['job-apply-extrnal-url']) ? $jobsearch_plugin_options['job-apply-extrnal-url'] : '';
                                    if ($job_extrnal_apply_switch == 'on') {
                                        ?>
                                        <li class="jobsearch-column-6">
                                            <label><?php esc_html_e('Job Apply Type', 'wp-jobsearch') ?></label>
                                            <div class="jobsearch-profile-select">
                                                <select id="jobsearch_job_apply_type" class="selectize-select" name="job_apply_type">
                                                    <option value="internal" <?php echo ($is_updating && $_job_apply_type == 'internal' ? 'selected="selected"' : '') ?>><?php esc_html_e('Internal', 'wp-jobsearch') ?></option>
                                                    <option value="external" <?php echo ($is_updating && $_job_apply_type == 'external' ? 'selected="selected"' : '') ?>><?php esc_html_e('External URL', 'wp-jobsearch') ?></option>
                                                    <option value="with_email" <?php echo ($is_updating && $_job_apply_type == 'with_email' ? 'selected="selected"' : '') ?>><?php esc_html_e('By Email', 'wp-jobsearch') ?></option>
                                                </select>
                                            </div>
                                        </li>
                                        <li id="job-apply-external-url" class="jobsearch-column-6" style="display: <?php echo ($is_updating && $_job_apply_type == 'external' ? 'inline-block' : 'none') ?>;">
                                            <label><?php esc_html_e('External URL for Apply Job', 'wp-jobsearch') ?></label>
                                            <input type="text" name="job_apply_url" <?php echo ($is_updating ? 'value="' . ($_job_apply_url) . '"' : '') ?>>
                                        </li>
                                        <li id="job-apply-by-email" class="jobsearch-column-6" style="display: <?php echo ($is_updating && $_job_apply_type == 'with_email' ? 'inline-block' : 'none') ?>;">
                                            <label><?php esc_html_e('Job Apply Email', 'wp-jobsearch') ?></label>
                                            <input type="text" name="job_apply_email" <?php echo ($is_updating ? 'value="' . ($_job_apply_email) . '"' : '') ?>>
                                        </li>
                                        <?php
                                    }
                                    $salary_onoff_switch = isset($jobsearch_plugin_options['salary_onoff_switch']) ? $jobsearch_plugin_options['salary_onoff_switch'] : '';
                                    if ($salary_onoff_switch == 'on') {
                                        ?>
                                        <li class="jobsearch-column-12">
                                            <label><?php esc_html_e('Salary *', 'wp-jobsearch') ?></label>
                                            <?php
                                            if (!empty($job_salary_types)) {
                                                ?>
                                                <div class="salary-type">
                                                    <div class="jobsearch-profile-select">
                                                        <select name="job_salary_type" class="selectize-select">
                                                            <?php
                                                            $slar_type_count = 1;
                                                            foreach ($job_salary_types as $job_salary_type) {
                                                                $job_salary_type = apply_filters('wpml_translate_single_string', $job_salary_type, 'JobSearch Options', 'Salary Type - ' . $job_salary_type, $lang_code);
                                                                ?>
                                                                <option value="type_<?php echo ($slar_type_count) ?>" <?php echo ($is_updating && $_job_salary_type == 'type_' . $slar_type_count ? 'selected="selected"' : '') ?>><?php echo ($job_salary_type) ?></option>
                                                                <?php
                                                                $slar_type_count++;
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <?php
                                                echo '<div class="salary-input">';
                                            } else {
                                                echo '<div class="salary-input salary-input-full">';
                                            }

                                            $_job_currency_sym = isset($jobsearch_currencies_list[$_job_salary_currency]['symbol']) ? $jobsearch_currencies_list[$_job_salary_currency]['symbol'] : jobsearch_get_currency_symbol();
                                            ?>
                                            <div class="min-salary">
                                                <span><?php echo ($_job_currency_sym); ?></span>
                                                <input type="text" placeholder="<?php esc_html_e('Min', 'wp-jobsearch') ?>" name="job_salary" <?php echo ($is_updating ? 'value="' . ($_job_salary) . '"' : '') ?>>
                                            </div>
                                            <div class="max-salary">
                                                <span><?php echo ($_job_currency_sym); ?></span>
                                                <input type="text" placeholder="<?php esc_html_e('Max', 'wp-jobsearch') ?>" name="job_max_salary" <?php echo ($is_updating ? 'value="' . ($_job_max_salary) . '"' : '') ?>>
                                            </div>
                                            <?php
                                            echo '</div>';
                                            ?>
                                        </li>
                                        <?php
                                        $job_custom_currency_switch = isset($jobsearch_plugin_options['job_custom_currency']) ? $jobsearch_plugin_options['job_custom_currency'] : '';
                                        if (!empty($jobsearch_currencies_list) && $job_custom_currency_switch == 'on') {
                                            ?>
                                            <li class="jobsearch-column-12">
                                                <div class="jobsearch-row">
                                                    <div class="jobsearch-column-3">
                                                        <label><?php esc_html_e('Salary Currency', 'wp-jobsearch') ?></label>
                                                        <div class="jobsearch-profile-select">
                                                            <select name="job_salary_currency" class="selectize-select">
                                                                <option value="default" data-cur="<?php echo jobsearch_get_currency_symbol() ?>"><?php esc_html_e('Default', 'wp-jobsearch') ?></option>
                                                                <?php
                                                                foreach ($jobsearch_currencies_list as $cus_currency_key => $cus_currency) {
                                                                    $cus_cur_name = isset($cus_currency['name']) ? $cus_currency['name'] : '';
                                                                    $cus_cur_symbol = isset($cus_currency['symbol']) ? $cus_currency['symbol'] : '';
                                                                    ?>
                                                                    <option value="<?php echo ($cus_currency_key) ?>" data-cur="<?php echo ($cus_cur_symbol) ?>" <?php echo ($_job_salary_currency == $cus_currency_key ? 'selected="selected"' : '') ?>><?php echo ($cus_cur_name . ' - ' . $cus_cur_symbol) ?></option>
                                                                    <?php
                                                                }
                                                                ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="jobsearch-column-3">
                                                        <label><?php esc_html_e('Currency position', 'wp-jobsearch') ?></label>
                                                        <div class="jobsearch-profile-select">
                                                            <select name="job_salary_pos" class="selectize-select">
                                                                <option value="left" <?php echo ($_job_salary_pos == 'left' ? 'selected="selected"' : '') ?>><?php esc_html_e('Left', 'wp-jobsearch') ?></option>
                                                                <option value="right" <?php echo ($_job_salary_pos == 'right' ? 'selected="selected"' : '') ?>><?php esc_html_e('Right', 'wp-jobsearch') ?></option>
                                                                <option value="left_space" <?php echo ($_job_salary_pos == 'left_space' ? 'selected="selected"' : '') ?>><?php esc_html_e('Left with space', 'wp-jobsearch') ?></option>
                                                                <option value="right_space" <?php echo ($_job_salary_pos == 'right_space' ? 'selected="selected"' : '') ?>><?php esc_html_e('Right with space', 'wp-jobsearch') ?></option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="jobsearch-column-3">
                                                        <label><?php esc_html_e('Thousand separator', 'wp-jobsearch') ?></label>
                                                        <input type="text" name="job_salary_sep" value="<?php echo ($_job_salary_sep != '' ? $_job_salary_sep : ',') ?>">
                                                    </div>
                                                    <div class="jobsearch-column-3">
                                                        <label><?php esc_html_e('Number of decimals', 'wp-jobsearch') ?></label>
                                                        <input type="text" name="job_salary_deci" value="<?php echo ($_job_salary_deci != '' && $_job_salary_deci > 0 ? $_job_salary_deci : '2') ?>">
                                                    </div>
                                                </div>
                                            </li>
                                            <?php
                                        }
                                    }
                                    ?>
                                </ul>
                                <?php
                            }
                        }
                    }
                    ?>
                </div>
                <?php
                if (!$is_candidate) {
                    if (isset($job_detail_tab) && $job_detail_tab) {
                        do_action('jobsearch_post_job_sh_before_custom_fields', $job_id);
                        
                        do_action('jobsearch_dashboard_custom_fields_load', $job_id, 'job');

                        $job_attachments_switch = isset($jobsearch_plugin_options['job_attachments']) ? $jobsearch_plugin_options['job_attachments'] : '';
                        if ($job_attachments_switch == 'on') {
                            ?>
                            <div class="jobsearch-employer-box-section">
                                <div class="jobsearch-profile-title"><h2><?php esc_html_e('File Attachments', 'wp-jobsearch') ?></h2></div>
                                <div class="jobsearch-fileUpload">
                                    <span><i class="jobsearch-icon jobsearch-upload"></i> <?php esc_html_e('Upload Files', 'wp-jobsearch') ?></span>
                                    <input id="job_attach_files" name="job_attach_files[]" type="file" class="upload jobsearch-upload" multiple="multiple" onchange="jobsearch_job_attach_files_url(event)" />
                                </div>
                                <div id="attach-files-holder" class="gallery-imgs-holder jobsearch-company-gallery">
                                    <?php
                                    $all_attach_files = get_post_meta($job_id, 'jobsearch_field_job_attachment_files', true);
                                    if (!empty($all_attach_files)) {
                                        ?>
                                        <ul>
                                            <?php
                                            foreach ($all_attach_files as $_attach_file) {
                                                $_attach_id = jobsearch_get_attachment_id_from_url($_attach_file);
                                                $_attach_post = get_post($_attach_id);
                                                $_attach_mime = isset($_attach_post->post_mime_type) ? $_attach_post->post_mime_type : '';
                                                $_attach_guide = isset($_attach_post->guid) ? $_attach_post->guid : '';
                                                $attach_name = basename($_attach_guide);

                                                $file_icon = 'fa fa-file-text-o';
                                                if ($_attach_mime == 'image/png' || $_attach_mime == 'image/jpeg') {
                                                    $file_icon = 'fa fa-file-image-o';
                                                } else if ($_attach_mime == 'application/msword' || $_attach_mime == 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
                                                    $file_icon = 'fa fa-file-word-o';
                                                } else if ($_attach_mime == 'application/vnd.ms-excel' || $_attach_mime == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
                                                    $file_icon = 'fa fa-file-excel-o';
                                                } else if ($_attach_mime == 'application/pdf') {
                                                    $file_icon = 'fa fa-file-pdf-o';
                                                }
                                                ?>
                                                <li class="jobsearch-column-3">
                                                    <a href="javascript:void(0);" class="fa fa-remove el-remove"></a>
                                                    <div class="file-container">
                                                        <a href="<?php echo ($_attach_file) ?>" download="<?php echo ($attach_name) ?>"><i class="<?php echo ($file_icon) ?>"></i> <?php echo ($attach_name) ?></a>
                                                    </div>
                                                    <input type="hidden" name="jobsearch_field_job_attachment_files[]" value="<?php echo ($_attach_file) ?>">
                                                </li>
                                                <?php
                                            }
                                            ?>
                                        </ul>
                                        <?php
                                    }
                                    ?>
                                </div>
                            </div>
                            <?php
                        }
                        do_action('jobsearch_dashboard_location_map', $job_id, true);
                        if (!is_user_logged_in()) {
                            ?>
                            <input type="hidden" name="pt_user_role" value="jobsearch_employer">
                            <?php
                        }
                        ?>
                        <input type="hidden" name="user_job_posting" value="1">
                        <?php
                        if ($is_updating) {
                            ?>
                            <input type="submit" class="jobsearch-employer-profile-submit" value="<?php esc_html_e('Update Job', 'wp-jobsearch') ?>">
                            <?php
                            ob_start();
                            do_action('jobsearch_translate_job_with_wpml_btn', $job_id);
                            $btns_html = ob_get_clean();
                            echo apply_filters('jobsearch_translate_job_with_wpml_btn_html', $btns_html);
                        } else {
                            jobsearch_terms_and_con_link_txt();
                            ?>
                            <input type="submit" class="jobsearch-employer-profile-submit" value="<?php esc_html_e('Post Job', 'wp-jobsearch') ?>">
                            <div class="clearfix"></div>
                            <?php
                        }
                    }
                    if (isset($job_package_tab)) {
                        ?>
                        <input type="hidden" name="user_job_package_chose" value="1">
                        <input type="submit" class="jobsearch-employer-profile-submit" value="<?php esc_html_e('Update Package', 'wp-jobsearch') ?>">
                        <?php
                    }
                }
                ?>
            </form>
            <?php
        }
        ?>
    </div>
    <?php
    $html = ob_get_clean();
    return $html;
}
