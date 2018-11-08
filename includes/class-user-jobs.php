<?php

class Jobsearch_User_Job_Functions {
    /*
     * Class Construct
     * @return
     */

    public function __construct() {
        //
        add_action('wp_loaded', array($this, 'user_job_header'));

        //
        add_action('jobsearch_add_new_package_fields_for_job', array($this, 'add_new_package_fields_for_job'), 10, 2);
        add_action('jobsearch_add_subscribed_package_fields_for_job', array($this, 'add_subscribed_package_fields_for_job'), 10, 2);
        add_action('jobsearch_add_package_fields_for_order', array($this, 'add_package_fields_for_order'), 10, 3);

        add_action('jobsearch_set_job_expiry_and_status', array($this, 'set_job_expiry_and_status'), 10, 1);
        //
        add_action('jobsearch_add_job_id_to_order', array($this, 'add_job_id_to_order'), 10, 2);

        //
        add_action('jobsearch_create_new_job_packg_order', array($this, 'create_new_job_packg_order'), 10, 2);
        //
        add_action('jobsearch_create_featured_job_packg_order', array($this, 'create_new_featured_job_packg_order'), 10, 3);

        //
        add_action('wp_ajax_jobsearch_user_dashboard_job_delete', array($this, 'remove_user_job_from_dashboard'));
    }

    /*
     * User job header
     * @return html
     */

    public function user_job_header() {
        global $jobsearch_plugin_options, $sitepress, $job_form_errs, $package_form_errs;

        $free_jobs_allow = isset($jobsearch_plugin_options['free-jobs-allow']) ? $jobsearch_plugin_options['free-jobs-allow'] : '';
        $page_id = $user_dashboard_page = isset($jobsearch_plugin_options['user-dashboard-template-page']) ? $jobsearch_plugin_options['user-dashboard-template-page'] : '';
        $page_id = $user_dashboard_page = jobsearch__get_post_id($page_id, 'page');
        $page_url = jobsearch_wpml_lang_page_permalink($page_id, 'page'); //get_permalink($page_id);
        // job post/update actions
        $job_form_errs = $package_form_errs = array();
        if (isset($_POST['user_job_posting']) && $_POST['user_job_posting'] == '1') {

            $do_insert_job = $do_update_job = false;

            $user_id = get_current_user_id();
            $user_obj = get_user_by('ID', $user_id);

            if (jobsearch_employer_not_allow_to_mod()) {
                $job_form_errs['post_errors'] = wp_kses(__('<strong>Error!</strong> You are not allowed to add or update any job.', 'wp-jobsearch'), array('strong' => array()));
                return false;
            }
            if (jobsearch_candidate_not_allow_to_mod()) {
                $job_form_errs['post_errors'] = wp_kses(__('<strong>Error!</strong> You are not allowed to add or update any job.', 'wp-jobsearch'), array('strong' => array()));
                return false;
            }

            $is_updating = false;
            $job_id = 0;
            if (isset($_GET['job_id']) && $_GET['job_id'] > 0 && jobsearch_is_employer_job($_GET['job_id'])) {
                $real_job_id = $job_id = $_GET['job_id'];
                $is_updating = true;
            }
            $job_title = isset($_POST['job_title']) ? $_POST['job_title'] : '';
            $job_desc = isset($_POST['job_detail']) ? $_POST['job_detail'] : '';

            //
            $user_is_employer = jobsearch_user_is_employer($user_id);
            $employer_id = '';
            if (is_user_logged_in() && $user_is_employer) {
                $employer_id = jobsearch_get_user_employer_id($user_id);
                if ($employer_id <= 0) {
                    $job_form_errs['post_errors'] = esc_html__('Only an employer can post job.', 'wp-jobsearch');
                }
            }
            if (!is_user_logged_in() && isset($_POST['reg_user_uname']) && isset($_POST['reg_user_email'])) {
                $reguser_name = sanitize_text_field($_POST['reg_user_uname']);
                $reguser_email = sanitize_text_field($_POST['reg_user_email']);

                if ($reguser_name == '') {
                    $job_form_errs['post_errors'] = esc_html__('Username field should not be blank.', 'wp-jobsearch');
                }
                if ($reguser_email == '' || !filter_var($reguser_email, FILTER_VALIDATE_EMAIL)) {
                    $job_form_errs['post_errors'] = esc_html__('Please enter proper user Email Address.', 'wp-jobsearch');
                }

                $new_reguser = wp_create_user($reguser_name, wp_generate_password(), $reguser_email);

                if (is_wp_error($new_reguser)) {
                    $job_form_errs['post_errors'] = $new_reguser->get_error_message();
                } else {
                    //
                    $user_id = $new_reguser;
                    wp_update_user(array('ID' => $user_id, 'role' => 'jobsearch_employer'));
                    wp_set_current_user($user_id);
                    wp_set_auth_cookie($user_id, true);
                    $user_obj = get_user_by('ID', $user_id);
                    $user_is_employer = jobsearch_user_is_employer($user_id);
                    $employer_id = '';
                    if (is_user_logged_in() && $user_is_employer) {
                        $employer_id = jobsearch_get_user_employer_id($user_id);
                        if ($employer_id <= 0) {
                            $job_form_errs['post_errors'] = esc_html__('Only an employer can post job.', 'wp-jobsearch');
                        }
                    }
                }
            }
            //

            $job_title_max_len = isset($jobsearch_plugin_options['job_title_length']) && $jobsearch_plugin_options['job_title_length'] > 0 ? $jobsearch_plugin_options['job_title_length'] : 1000;
            $job_desc_max_len = isset($jobsearch_plugin_options['job_desc_length']) && $jobsearch_plugin_options['job_desc_length'] > 0 ? $jobsearch_plugin_options['job_desc_length'] : 5000;
            if ($job_title == '') {
                $job_form_errs['post_errors'] = esc_html__('Title field should not be blank.', 'wp-jobsearch');
            }
            if (strlen($job_title) < 1 || strlen($job_title) > $job_title_max_len) {
                $job_form_errs['post_errors'] = sprintf(esc_html__('Title length should be between 1 to %s characters.', 'wp-jobsearch'), $job_title_max_len);
            }
            if ($job_desc == '') {
                $job_form_errs['post_errors'] = esc_html__('Description field should not be blank.', 'wp-jobsearch');
            }
            if (strlen($job_desc) > $job_desc_max_len) {
                $job_form_errs['post_errors'] = sprintf(esc_html__('Description length should not be exceeds from %s characters.', 'wp-jobsearch'), $job_desc_max_len);
            }

            if (empty($job_form_errs)) {
                if ($job_id > 0) {

                    if (function_exists('icl_object_id')) {
                        $current_lang = $sitepress->get_current_language();
                        $job_id = icl_object_id($job_id, 'job', true, $current_lang);
                    }

                    $up_post = array(
                        'ID' => $job_id,
                        'post_title' => ($job_title),
                        'post_content' => $job_desc,
                    );
                    wp_update_post($up_post);

                    $do_update_job = true;
                } else {
                    $ins_post = array(
                        'post_type' => 'job',
                        'post_status' => 'publish',
                        'post_title' => wp_strip_all_tags($job_title),
                        'post_content' => $job_desc,
                    );
                    $job_id = wp_insert_post($ins_post);

                    update_post_meta($job_id, 'jobsearch_field_job_featured', '');

                    if (function_exists('icl_object_id')) {
                        $lang_code = $sitepress->get_current_language();
                        $lang_code = apply_filters('jobsearch_set_post_insert_lang_code', $lang_code);
                        $sitepress->set_element_language_details($job_id, 'post_job', false, $lang_code);
                    }
                    $do_insert_job = true;
                }

                // update job employer
                update_post_meta($job_id, 'jobsearch_field_job_posted_by', $employer_id);

                // Employer jobs status change according his/her status
                do_action('jobsearch_employer_update_jobs_status', $employer_id);

                $job_expired = true;
                if (!$is_updating) {
                    // job insert time
                    update_post_meta($job_id, 'jobsearch_field_job_publish_date', strtotime(current_time('d-m-Y H:i:s', 1)));
                    if ($free_jobs_allow == 'on') {
                        //
                    } else {
                        update_post_meta($job_id, 'jobsearch_field_job_status', 'pending');
                    }
                } else {
                    $job_expiry_date = get_post_meta($job_id, 'jobsearch_field_job_expiry_date', true);
                    if ($job_expiry_date != '' && $job_expiry_date > strtotime(current_time('d-m-Y H:i:s', 1))) {
                        $job_expired = false;
                    } else {
                        //
                        $c_user = wp_get_current_user();
                        do_action('jobsearch_job_expire_to_employer', $c_user, $job_id);
                    }
                }

                // job skills
                $job_skills_switch = isset($jobsearch_plugin_options['job-skill-switch']) ? $jobsearch_plugin_options['job-skill-switch'] : '';
                if ($job_skills_switch == 'on') {
                    $job_max_skills_allow = isset($jobsearch_plugin_options['job_max_skills']) && $jobsearch_plugin_options['job_max_skills'] > 0 ? $jobsearch_plugin_options['job_max_skills'] : 5;
                    $tags_limit = $job_max_skills_allow;
                    $job_skills = isset($_POST['get_job_skills']) && !empty($_POST['get_job_skills']) ? $_POST['get_job_skills'] : array();
                    if (absint($tags_limit) > 0 && !empty($job_skills) && count($job_skills) > $tags_limit) {
                        $job_skills = array_slice($job_skills, 0, $tags_limit, true);
                    }
                    wp_set_post_terms($job_id, $job_skills, 'skill', FALSE);
                    update_post_meta($job_id, 'jobsearch_job_skills', $job_skills);
                }

                //
                if (isset($_POST['job_sector'])) {
                    $job_sector = sanitize_text_field($_POST['job_sector']);
                    wp_set_post_terms($job_id, array($job_sector), 'sector', false);
                }
                // job filled
                if (isset($_POST['job_filled'])) {
                    $job_filled = sanitize_text_field($_POST['job_filled']);
                    update_post_meta($job_id, 'jobsearch_field_job_filled', $job_filled);
                }

                // job apply type
                if (isset($_POST['job_apply_type'])) {
                    $job_apply_type = sanitize_text_field($_POST['job_apply_type']);
                    update_post_meta($job_id, 'jobsearch_field_job_apply_type', $job_apply_type);
                }
                if (isset($_POST['job_apply_url'])) {
                    $job_apply_url = sanitize_text_field($_POST['job_apply_url']);
                    update_post_meta($job_id, 'jobsearch_field_job_apply_url', $job_apply_url);
                }
                if (isset($_POST['job_apply_email'])) {
                    $job_apply_email = sanitize_text_field($_POST['job_apply_email']);
                    update_post_meta($job_id, 'jobsearch_field_job_apply_email', $job_apply_email);
                }

                // job min salary
                if (isset($_POST['job_salary'])) {
                    $job_salary = sanitize_text_field($_POST['job_salary']);
                    update_post_meta($job_id, 'jobsearch_field_job_salary', $job_salary);
                }
                // job max salary
                if (isset($_POST['job_max_salary'])) {
                    $job_max_salary = sanitize_text_field($_POST['job_max_salary']);
                    update_post_meta($job_id, 'jobsearch_field_job_max_salary', $job_max_salary);
                }
                // job salary type
                if (isset($_POST['job_salary_type'])) {
                    $job_salary_type = sanitize_text_field($_POST['job_salary_type']);
                    update_post_meta($job_id, 'jobsearch_field_job_salary_type', $job_salary_type);
                }
                // job salary currency
                if (isset($_POST['job_salary_currency'])) {
                    $job_salary_type = ($_POST['job_salary_currency']);
                    update_post_meta($job_id, 'jobsearch_field_job_salary_currency', $job_salary_type);
                }
                // job salary currency pos
                if (isset($_POST['job_salary_pos'])) {
                    $job_salary_type = sanitize_text_field($_POST['job_salary_pos']);
                    update_post_meta($job_id, 'jobsearch_field_job_salary_pos', $job_salary_type);
                }
                // job salary currency decimal
                if (isset($_POST['job_salary_deci'])) {
                    $job_salary_type = sanitize_text_field($_POST['job_salary_deci']);
                    update_post_meta($job_id, 'jobsearch_field_job_salary_deci', $job_salary_type);
                }
                // job salary currency sep
                if (isset($_POST['job_salary_sep'])) {
                    $job_salary_type = sanitize_text_field($_POST['job_salary_sep']);
                    update_post_meta($job_id, 'jobsearch_field_job_salary_sep', $job_salary_type);
                }

                // application deadline
                if (isset($_POST['application_deadline']) && $_POST['application_deadline'] != '') {
                    $application_deadline = sanitize_text_field($_POST['application_deadline']);
                    update_post_meta($job_id, 'jobsearch_field_job_application_deadline_date', strtotime($application_deadline));
                }

                // Attachments ////////////////////////
                $gal_ids_arr = array();

                $max_gal_imgs_allow = isset($jobsearch_plugin_options['number_of_attachments']) && $jobsearch_plugin_options['number_of_attachments'] > 0 ? $jobsearch_plugin_options['number_of_attachments'] : 5;

                if (isset($_POST['jobsearch_field_job_attachment_files']) && !empty($_POST['jobsearch_field_job_attachment_files'])) {
                    $gal_ids_arr = array_merge($gal_ids_arr, $_POST['jobsearch_field_job_attachment_files']);
                }

                $gal_imgs_count = 0;
                if (!empty($gal_ids_arr)) {
                    $gal_imgs_count = sizeof($gal_ids_arr);
                }

                $gall_ids = jobsearch_attachments_upload('job_attach_files', $gal_imgs_count);
                if (!empty($gall_ids)) {
                    $gal_ids_arr = array_merge($gal_ids_arr, $gall_ids);
                }
                if (!empty($gal_ids_arr) && $max_gal_imgs_allow > 0) {
                    $gal_ids_arr = array_slice($gal_ids_arr, 0, $max_gal_imgs_allow, true);
                }
                update_post_meta($job_id, 'jobsearch_field_job_attachment_files', $gal_ids_arr);
                //
                //
                if (isset($_POST['job_type'])) {
                    $job_type = sanitize_text_field($_POST['job_type']);
                    wp_set_post_terms($job_id, array($job_type), 'jobtype', false);
                }
                
                // after saving all fields
                do_action('jobsearch_job_dash_save_after', $job_id);

                if (!$is_updating && $free_jobs_allow == 'on') {
                    do_action('jobsearch_set_job_expiry_and_status', $job_id);
                }

                //
                if ($do_insert_job === true) {
                    $users_query = new WP_User_Query(array(
                        'role' => 'administrator',
                        'orderby' => 'display_name'
                    ));
                    $users_result = $users_query->get_results();
                    $adm_user_obj = isset($users_result[0]) ? $users_result[0] : array();
                    do_action('jobsearch_job_submitted_admin', $adm_user_obj, $job_id);
                }

                //
                if ($do_update_job === true) {
                    $c_user = wp_get_current_user();
                    do_action('jobsearch_job_update_to_employer', $c_user, $job_id);
                }

                if ($free_jobs_allow != 'on') {
                    if (is_user_logged_in() && $user_is_employer && !$is_updating) {
                        $redirect_url = add_query_arg(array('tab' => 'user-job', 'job_id' => $job_id, 'step' => 'package', 'action' => 'update'), $page_url);
                        wp_safe_redirect($redirect_url);
                        exit();
                    }
                    if (is_user_logged_in() && $user_is_employer && $is_updating && $job_expired) {
                        $redirect_url = add_query_arg(array('tab' => 'user-job', 'job_id' => $job_id, 'step' => 'package', 'action' => 'update'), $page_url);
                        wp_safe_redirect($redirect_url);
                        exit();
                    }
                    if (is_user_logged_in() && $user_is_employer && $is_updating && !$job_expired) {
                        $redirect_url = add_query_arg(array('tab' => 'user-job', 'job_id' => $job_id, 'step' => 'confirm', 'action' => 'update'), $page_url);
                        wp_safe_redirect($redirect_url);
                        exit();
                    }
                } else {
                    if (is_user_logged_in() && $user_is_employer) {
                        $redirect_url = add_query_arg(array('tab' => 'user-job', 'job_id' => $job_id, 'step' => 'confirm', 'action' => 'update'), $page_url);
                        wp_safe_redirect($redirect_url);
                        exit();
                    }
                }
            }
        }
        //

        if (isset($_POST['user_job_package_chose']) && $_POST['user_job_package_chose'] == '1') {
            $is_updating = false;
            $job_id = 0;
            if (isset($_GET['job_id']) && $_GET['job_id'] > 0 && jobsearch_is_employer_job($_GET['job_id'])) {
                $job_id = $_GET['job_id'];
                $is_updating = true;
            }

            $go_to_confirm = false;
            
            //
            if (isset($_POST['job_subs_package'])) {
                // For Subscribed Package actions
                $package_order_id = $_POST['job_subs_package'];
                $pkg_type = get_post_meta($package_order_id, 'package_type', true);
                if ($is_updating && empty($package_form_errs) && $pkg_type == 'job' && jobsearch_pckg_order_is_expired($package_order_id) === false) {
                    // Saving Package Fields and Values in Job
                    do_action('jobsearch_add_subscribed_package_fields_for_job', $package_order_id, $job_id);
                    do_action('jobsearch_add_job_id_to_order', $job_id, $package_order_id);
                    do_action('jobsearch_set_job_expiry_and_status', $job_id);
                    
                    // if feature pckg too selected
                    if (isset($_POST['job_package_featured']) && $_POST['job_package_featured'] != '') {
                        $package_id = $_POST['job_package_featured'];
                        $pkg_charges_type = get_post_meta($package_id, 'jobsearch_field_charges_type', true);
                        $pkg_attach_product = get_post_meta($package_id, 'jobsearch_package_product', true);
                        if (!class_exists('WooCommerce')) {
                            $package_form_errs[] = esc_html__('WooCommerce Plugin not exist.', 'wp-jobsearch');
                        }
                        if ($pkg_charges_type == 'paid') {
                            $package_product_obj = $pkg_attach_product != '' ? get_page_by_path($pkg_attach_product, 'OBJECT', 'product') : '';

                            if ($pkg_attach_product != '' && is_object($package_product_obj)) {
                                $product_id = $package_product_obj->ID;
                            } else {
                                $package_form_errs[] = esc_html__('Selected Package Product not found.', 'wp-jobsearch');
                            }
                            if (empty($package_form_errs)) {
                                // add to cart and checkout
                                do_action('jobsearch_woocommerce_payment_checkout', $package_id, 'redirect', $job_id);
                            }
                        }
                    }
                    //
                }
                //
                $go_to_confirm = true;
                $conf_args = array(
                    'is_updating' => $is_updating,
                    'package_order_id' => $package_order_id,
                    'job_id' => $job_id,
                );
                $go_to_confirm = apply_filters('jobsearch_set_subs_pkg_goto_confirm', $go_to_confirm, $conf_args);
                //
            }

            if (isset($_POST['job_package_featured'])) {
                $package_id = $_POST['job_package_featured'];
                $pkg_charges_type = get_post_meta($package_id, 'jobsearch_field_charges_type', true);
                $pkg_attach_product = get_post_meta($package_id, 'jobsearch_package_product', true);
                if (!class_exists('WooCommerce')) {
                    $package_form_errs[] = esc_html__('WooCommerce Plugin not exist.', 'wp-jobsearch');
                }
                if ($pkg_charges_type == 'paid') {
                    $package_product_obj = $pkg_attach_product != '' ? get_page_by_path($pkg_attach_product, 'OBJECT', 'product') : '';

                    if ($pkg_attach_product != '' && is_object($package_product_obj)) {
                        $product_id = $package_product_obj->ID;
                    } else {
                        $package_form_errs[] = esc_html__('Selected Package Product not found.', 'wp-jobsearch');
                    }

                    if ($is_updating && empty($package_form_errs)) {
                        // add to cart and checkout
                        if (isset($_POST['job_package_new']) && $_POST['job_package_new'] != '') {
                            do_action('jobsearch_woocommerce_payment_checkout', $package_id, 'no_where', $job_id);
                        } else {
                            do_action('jobsearch_woocommerce_payment_checkout', $package_id, 'redirect', $job_id);
                        }
                    }
                } else {
                    if ($is_updating && empty($package_form_errs)) {
                        // creating order and adding product to order
                        do_action('jobsearch_create_new_job_packg_order', $package_id, $job_id);
                        $go_to_confirm = true;
                    }
                }
            }

            if (isset($_POST['job_package_new'])) {

                $package_id = isset($_POST['job_package_new']) ? $_POST['job_package_new'] : '';

                if (jobsearch_pckg_is_subscribed($package_id)) {
                    $package_form_errs[] = sprintf(esc_html__('Selected Package "%s" is already subscribed.', 'wp-jobsearch'), get_the_title($package_id));
                }

                $pkg_charges_type = get_post_meta($package_id, 'jobsearch_field_charges_type', true);
                $pkg_attach_product = get_post_meta($package_id, 'jobsearch_package_product', true);

                // For Paid Package actions
                if ($pkg_charges_type == 'paid') {
                    if (!class_exists('WooCommerce')) {
                        $package_form_errs[] = esc_html__('WooCommerce Plugin not exist.', 'wp-jobsearch');
                    }

                    $package_product_obj = $pkg_attach_product != '' ? get_page_by_path($pkg_attach_product, 'OBJECT', 'product') : '';

                    if ($pkg_attach_product != '' && is_object($package_product_obj)) {
                        $product_id = $package_product_obj->ID;
                    } else {
                        $package_form_errs[] = esc_html__('Selected Package Product not found.', 'wp-jobsearch');
                    }

                    if ($is_updating && empty($package_form_errs)) {
                        //
                        $checkout_process = true;
                        $checkout_process = apply_filters('jobsearch_new_job_post_before_checkout', $checkout_process, $package_id, $job_id);

                        // add to cart and checkout
                        if ($checkout_process === true) {
                            if (isset($_POST['job_package_featured']) && $_POST['job_package_featured'] != '') {
                                do_action('jobsearch_woocommerce_payment_checkout', $package_id, 'redirect', $job_id, false);
                            } else {
                                do_action('jobsearch_woocommerce_payment_checkout', $package_id, 'redirect', $job_id);
                            }
                        }
                    }
                } else {
                    // For Free Package actions
                    if (!class_exists('WooCommerce')) {
                        $package_form_errs[] = esc_html__('WooCommerce Plugin not exist.', 'wp-jobsearch');
                    }
                    if ($is_updating && empty($package_form_errs)) {
                        // creating order and adding product to order
                        do_action('jobsearch_create_new_job_packg_order', $package_id, $job_id);
                        $go_to_confirm = true;
                    }
                }
            }
            //
            if ($go_to_confirm) {
                $redirect_url = add_query_arg(array('tab' => 'user-job', 'job_id' => $job_id, 'step' => 'confirm', 'action' => 'update'), $page_url);
                wp_safe_redirect($redirect_url);
                exit();
            }
            //
        }
    }

    public function add_new_package_fields_for_job($package_id, $job_id) {
        $job_package_fields = apply_filters('jobsearch_get_job_package_fields_list', array());

        $pkg_type = get_post_meta($package_id, 'jobsearch_field_package_type', true);
        $job_package_fields = apply_filters('jobsearch_set_package_fields_ch_list', $job_package_fields, $pkg_type);

        $packge_fields_arr = array(
            'package_name' => get_the_title($package_id),
            'package_charges_type' => get_post_meta($package_id, 'jobsearch_field_charges_type', true),
            'package_type' => get_post_meta($package_id, 'jobsearch_field_package_type', true),
            'package_price' => get_post_meta($package_id, 'jobsearch_field_package_price', true),
        );
        if ($packge_fields_arr['package_charges_type'] == 'free') {
            $packge_fields_arr['package_price'] = 0;
        }
        if (!empty($job_package_fields)) {
            foreach ($job_package_fields as $job_package_field) {
                $value = get_post_meta($package_id, 'jobsearch_field_' . $job_package_field, true);
                $packge_fields_arr[$job_package_field] = $value;
            }
        }

        $job_packages_arr = get_post_meta($job_id, 'attach_packages_array', true);
        if (empty($job_packages_arr)) {
            $job_packages_arr = array($packge_fields_arr);
            update_post_meta($job_id, 'attach_packages_array', $job_packages_arr);
        } else {
            $job_packages_arr[] = $packge_fields_arr;
            update_post_meta($job_id, 'attach_packages_array', $job_packages_arr);
        }
    }

    public function add_subscribed_package_fields_for_job($package_id, $job_id) {
        $job_package_fields = apply_filters('jobsearch_get_job_package_fields_list', array());

        $pkg_type = get_post_meta($package_id, 'jobsearch_field_package_type', true);
        $job_package_fields = apply_filters('jobsearch_set_package_fields_ch_list', $job_package_fields, $pkg_type);

        $packge_fields_arr = array(
            'package_name' => get_post_meta($package_id, 'package_name', true),
            'package_type' => get_post_meta($package_id, 'package_type', true),
            'package_price' => get_post_meta($package_id, 'package_price', true),
        );
        if (!empty($job_package_fields)) {
            foreach ($job_package_fields as $job_package_field) {
                $value = get_post_meta($package_id, $job_package_field, true);
                $packge_fields_arr[$job_package_field] = $value;
            }
        }

        $job_packages_arr = get_post_meta($job_id, 'attach_packages_array', true);
        if (empty($job_packages_arr)) {
            $job_packages_arr = array($packge_fields_arr);
            update_post_meta($job_id, 'attach_packages_array', $job_packages_arr);
        } else {
            $job_packages_arr[] = $packge_fields_arr;
            update_post_meta($job_id, 'attach_packages_array', $job_packages_arr);
        }
    }

    public function add_package_fields_for_order($package_id, $order_id, $pkg_type = 'job') {
        if ($pkg_type == 'cv') {
            $_package_fields = apply_filters('jobsearch_get_cv_package_fields_list', array());
        } else if ($pkg_type == 'candidate') {
            $_package_fields = apply_filters('jobsearch_get_candidate_package_fields_list', array());
        } else if ($pkg_type == 'feature_job') {
            $_package_fields = apply_filters('jobsearch_get_feature_job_package_fields_list', array());
        } else {
            $_package_fields = apply_filters('jobsearch_get_job_package_fields_list', array());
        }

        $_package_fields = apply_filters('jobsearch_set_package_fields_ch_list', $_package_fields, $pkg_type);

        $packge_fields_arr = array(
            'package_name' => get_the_title($package_id),
            'package_type' => get_post_meta($package_id, 'jobsearch_field_package_type', true),
            'package_price' => get_post_meta($package_id, 'jobsearch_field_package_price', true),
        );

        $pkg_chrgs_type = get_post_meta($package_id, 'jobsearch_field_charges_type', true);
        if ($pkg_chrgs_type == 'free') {
            $packge_fields_arr['package_price'] = 0;
        }

        if (!empty($_package_fields)) {
            foreach ($_package_fields as $_package_field) {
                $value = get_post_meta($package_id, 'jobsearch_field_' . $_package_field, true);
                $packge_fields_arr[$_package_field] = $value;
            }
        }

        if (isset($packge_fields_arr['package_expiry_time']) && $packge_fields_arr['package_expiry_time'] > 0 && isset($packge_fields_arr['package_expiry_time_unit'])) {
            $pkg_expiry = $packge_fields_arr['package_expiry_time'];
            $pkg_expiry_unit = $packge_fields_arr['package_expiry_time_unit'];
            $pkg_expiry_time = strtotime("+" . $pkg_expiry . " " . $pkg_expiry_unit, strtotime(current_time('d-m-Y H:i:s', 1)));
        } else {
            $pkg_expiry_time = strtotime(current_time('d-m-Y H:i:s', 1));
        }
        $packge_fields_arr['package_expiry_timestamp'] = $pkg_expiry_time;

        //
        $packge_fields_arr = apply_filters('jobsearch_package_fields_arr_before_order_set', $packge_fields_arr, $order_id, $package_id, $pkg_type);
        foreach ($packge_fields_arr as $fields_arr_key => $fields_arr_val) {
            update_post_meta($order_id, $fields_arr_key, $fields_arr_val);
        }
        //
    }

    public function set_job_expiry_and_status($job_id) {
        global $jobsearch_plugin_options;
        $free_jobs_allow = isset($jobsearch_plugin_options['free-jobs-allow']) ? $jobsearch_plugin_options['free-jobs-allow'] : '';
        $job_def_status = isset($jobsearch_plugin_options['job-default-status']) ? $jobsearch_plugin_options['job-default-status'] : '';

        if ($free_jobs_allow == 'on') {
            // job expiry in days
            $job_expiry_days = isset($jobsearch_plugin_options['free-job-post-expiry']) ? $jobsearch_plugin_options['free-job-post-expiry'] : '';
            // job expiry time
            if ($job_expiry_days > 0) {
                $job_expiry_date = strtotime("+" . $job_expiry_days . " day", strtotime(current_time('d-m-Y H:i:s', 1)));
                update_post_meta($job_id, 'jobsearch_field_job_expiry_date', $job_expiry_date);
            }
        } else {
            $job_packages_arr = get_post_meta($job_id, 'attach_packages_array', true);
            if (!empty($job_packages_arr)) {
                $job_package_fields = end($job_packages_arr);
                $pkg_job_expiry = isset($job_package_fields['job_expiry_time']) ? $job_package_fields['job_expiry_time'] : 0;
                $pkg_job_expiry_unit = isset($job_package_fields['job_expiry_time_unit']) ? $job_package_fields['job_expiry_time_unit'] : 'days';
                if ($pkg_job_expiry > 0) {
                    $job_expiry_date = strtotime("+" . $pkg_job_expiry . " " . $pkg_job_expiry_unit, strtotime(current_time('d-m-Y H:i:s', 1)));
                    update_post_meta($job_id, 'jobsearch_field_job_expiry_date', $job_expiry_date);
                }
            }
        }

        $employer_id = get_post_meta($job_id, 'jobsearch_field_job_posted_by', true);

        // job default status
        $job_status = get_post_meta($job_id, 'jobsearch_field_job_status', true);

        // Check if job status is already approved
        // then don't change status
        if ($job_status != 'approved') {
            if ($job_def_status == 'admin-review') {
                update_post_meta($job_id, 'jobsearch_field_job_status', 'admin-review');
            } else {
                $employer_status = get_post_meta($employer_id, 'jobsearch_field_employer_approved', true);

                if ($employer_status == 'on') {
                    update_post_meta($job_id, 'jobsearch_field_job_status', 'approved');
                    $c_user = wp_get_current_user();
                    do_action('jobsearch_job_approved_to_employer', $c_user, $job_id);
                } else {
                    update_post_meta($job_id, 'jobsearch_field_job_status', 'admin-review');
                }
            }
        }
    }

    public function create_new_job_packg_order($pckg_id, $job_id) {
        global $woocommerce;

        $user_id = get_current_user_id();
        $user_obj = get_user_by('ID', $user_id);
        $user_displayname = $user_obj->display_name;
        $user_displayname = apply_filters('jobsearch_user_display_name', $user_displayname, $user_obj);
        $user_bio = $user_obj->description;
        $user_website = $user_obj->user_url;
        $user_email = $user_obj->user_email;
        $user_fname = $user_obj->first_name;
        $user_lname = $user_obj->last_name;

        $first_name = $user_fname;
        $last_name = $user_lname;
        if ($user_fname == '' && $user_lname == '') {
            $first_name = $user_displayname;
            $last_name = '';
        }
        $employer_id = jobsearch_get_user_employer_id($user_id);

        $user_phone = get_post_meta($employer_id, 'jobsearch_field_user_phone', true);
        $user_address = get_post_meta($employer_id, 'jobsearch_field_location_address', true);
        $user_city = get_post_meta($employer_id, 'jobsearch_field_location_location3', true);
        $user_state = get_post_meta($employer_id, 'jobsearch_field_location_location2', true);
        $user_country = get_post_meta($employer_id, 'jobsearch_field_location_location1', true);

        $product_id = 0;
        $package_product = get_post_meta($pckg_id, 'jobsearch_package_product', true);
        $package_product_obj = $package_product != '' ? get_page_by_path($package_product, 'OBJECT', 'product') : '';
        if ($package_product != '' && is_object($package_product_obj)) {
            $product_id = $package_product_obj->ID;
        }

        if ($product_id > 0 && get_post_type($product_id) == 'product') {

            $address = array(
                'first_name' => $first_name,
                'last_name' => $last_name,
                'company' => '',
                'email' => $user_email,
                'phone' => $user_phone,
                'address_1' => $user_address,
                'address_2' => '',
                'city' => $user_city,
                'state' => $user_state,
                'postcode' => '',
                'country' => $user_country
            );

            // Now we create the order
            $order = wc_create_order();

            $order->add_product(wc_get_product($product_id), 1);
            $order->set_address($address, 'billing');
            //
            $order->calculate_totals();
            $order_id = $order->get_ID();

            $order->update_status('processing');
            //
            update_post_meta($order_id, 'jobsearch_order_attach_with', 'package');
            update_post_meta($order_id, 'jobsearch_order_package', $pckg_id);
            update_post_meta($order_id, 'jobsearch_order_user', $user_id);
            //
            update_post_meta($order_id, 'jobsearch_order_attach_job_id', $job_id);
            // For free package
            update_post_meta($order_id, 'jobsearch_order_transaction_type', 'free');
            //
            $order->update_status('completed');
        }
    }

    public function create_new_featured_job_packg_order($pckg_id, $job_id, $user_id) {
        global $woocommerce;

        $user_obj = get_user_by('ID', $user_id);
        $user_displayname = $user_obj->display_name;
        $user_displayname = apply_filters('jobsearch_user_display_name', $user_displayname, $user_obj);
        $user_bio = $user_obj->description;
        $user_website = $user_obj->user_url;
        $user_email = $user_obj->user_email;
        $user_fname = $user_obj->first_name;
        $user_lname = $user_obj->last_name;

        $first_name = $user_fname;
        $last_name = $user_lname;
        if ($user_fname == '' && $user_lname == '') {
            $first_name = $user_displayname;
            $last_name = '';
        }
        $employer_id = jobsearch_get_user_employer_id($user_id);

        $user_phone = get_post_meta($employer_id, 'jobsearch_field_user_phone', true);
        $user_address = get_post_meta($employer_id, 'jobsearch_field_location_address', true);
        $user_city = get_post_meta($employer_id, 'jobsearch_field_location_location3', true);
        $user_state = get_post_meta($employer_id, 'jobsearch_field_location_location2', true);
        $user_country = get_post_meta($employer_id, 'jobsearch_field_location_location1', true);

        $product_id = 0;
        $package_product = get_post_meta($pckg_id, 'jobsearch_package_product', true);
        $package_product_obj = $package_product != '' ? get_page_by_path($package_product, 'OBJECT', 'product') : '';
        if ($package_product != '' && is_object($package_product_obj)) {
            $product_id = $package_product_obj->ID;
        }

        if ($product_id > 0 && get_post_type($product_id) == 'product') {

            $address = array(
                'first_name' => $first_name,
                'last_name' => $last_name,
                'company' => '',
                'email' => $user_email,
                'phone' => $user_phone,
                'address_1' => $user_address,
                'address_2' => '',
                'city' => $user_city,
                'state' => $user_state,
                'postcode' => '',
                'country' => $user_country
            );

            // Now we create the order
            $order = wc_create_order();

            $order->add_product(wc_get_product($product_id), 1);
            $order->set_address($address, 'billing');
            //
            $order->calculate_totals();
            $order_id = $order->get_ID();

            $order->update_status('processing');
            //
            update_post_meta($order_id, 'jobsearch_order_attach_with', 'package');
            update_post_meta($order_id, 'jobsearch_order_package', $pckg_id);
            update_post_meta($order_id, 'jobsearch_order_user', $user_id);
            //
            update_post_meta($order_id, 'jobsearch_order_attach_job_id', $job_id);
            // For paid package
            update_post_meta($order_id, 'jobsearch_order_transaction_type', 'paid');
            update_post_meta($job_id, 'jobsearch_field_job_featured', 'on');
            //
            $order->update_status('completed');
        }
    }

    public function add_job_id_to_order($job_id, $order_id) {
        if ($job_id > 0 && $order_id > 0) {
            $order_jobs = get_post_meta($order_id, 'jobsearch_order_jobs_list', true);
            if ($order_jobs != '') {
                $order_jobs = explode(',', $order_jobs);
                $order_jobs[] = $job_id;
                $order_jobs = implode(',', $order_jobs);
            } else {
                $order_jobs = $job_id;
            }
            update_post_meta($order_id, 'jobsearch_order_jobs_list', $order_jobs);
        }
    }

    public function remove_user_job_from_dashboard() {
        $job_id = isset($_POST['job_id']) ? ($_POST['job_id']) : '';

        $user_id = get_current_user_id();
        $employer_id = jobsearch_get_user_employer_id($user_id);

        if (jobsearch_employer_not_allow_to_mod()) {
            $msg = esc_html__('You are not allowed to delete this.', 'wp-jobsearch');
            echo json_encode(array('err_msg' => $msg));
            die;
        }

        $job_employer = get_post_meta($job_id, 'jobsearch_field_job_posted_by', true);
        if ($job_employer == $employer_id) {
            wp_delete_post($job_id, true);
            echo json_encode(array('msg' => esc_html__('deleted', 'wp-jobsearch')));
        } else {
            echo json_encode(array('msg' => esc_html__('You are not allowed to delete this.', 'wp-jobsearch')));
        }
        die;
    }

}

global $Jobsearch_User_Job_Functions;
$Jobsearch_User_Job_Functions = new Jobsearch_User_Job_Functions();
