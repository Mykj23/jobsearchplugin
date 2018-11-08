<?php

/**
 * Directory Plus JobApplications Module
 */
// Direct access not allowed.
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Jobsearch_JobApplication')) {

    class Jobsearch_JobApplication {

        public function __construct() {
            add_action('init', array($this, 'init'), 12);
            Jobsearch_JobApplication::load_files();
        }

        static function load_files() {
            // email template
            include plugin_dir_path(dirname(__FILE__)) . 'job-application/include/apply-job-email-template.php';
            //
            include plugin_dir_path(dirname(__FILE__)) . 'job-application/include/job-application-load.php';
        }

        public function init() {


            // Add hook for dashboard member top menu links.

            add_action('jobsearch_job_application_abc', array($this, 'jobsearch_job_application_abc_callback'), 10, 1);
            add_action('wp_enqueue_scripts', array($this, 'jobsearch_job_application_enqueue_scripts'), 52);
        }

        public function jobsearch_job_application_enqueue_scripts() {
            global $sitepress;

            $admin_ajax_url = admin_url('admin-ajax.php');
            if ( function_exists('icl_object_id') ) {
                $lang_code = $sitepress->get_current_language();
                $admin_ajax_url = add_query_arg(array('lang' => $lang_code), $admin_ajax_url);
            }
        
            // Enqueue JS
            wp_register_script('jobsearch-job-application-functions-script', plugins_url('assets/js/job-application-functions.js', __FILE__), '', '', true);
            wp_localize_script('jobsearch-job-application-functions-script', 'jobsearch_job_application', array(
                'admin_url' => $admin_ajax_url,
                'error_msg' => esc_html__('There is some error.', 'wp-jobsearch'),
                'confirm_msg' => esc_html__('jobsearch_job_application_conform_msg'),
                'com_img_size' => esc_html__('Image size should not greater than 1 MB.', 'wp-jobsearch'),
                'com_file_size' => esc_html__('File size should not greater than 1 MB.', 'wp-jobsearch'),
                'cv_file_types' => esc_html__('Suitable files are .doc,.docx,.pdf', 'wp-jobsearch'),
            ));
            wp_enqueue_script('jobsearch-job-application-functions-script');
        }

        public function jobsearch_job_application_abc_callback($permissions = array(), $abc) {
            
        }

    }

    global $jobsearch_job_application;
    $jobsearch_job_application = new Jobsearch_JobApplication();
}

function jobsearch_job_applicants_sort_list($job_id, $sort_by = '', $list_meta_key = 'jobsearch_job_applicants_list') {

    $job_applicants_list = get_post_meta($job_id, $list_meta_key, true);
    if ($job_applicants_list != '') {
        $job_applicants_list = explode(',', $job_applicants_list);
    }

    $retrn_array = $ret_array = array();

    if (!empty($job_applicants_list)) {
        if ($sort_by == 'alphabetic' && !empty($job_applicants_list)) {
            $appl_array = $appl_ret_array = array();
            foreach ($job_applicants_list as $job_applicant) {
                $appl_array[$job_applicant] = get_the_title($job_applicant);
            }
            asort($appl_array);
            if (!empty($appl_array)) {
                foreach ($appl_array as $appl_arr_key => $appl_arr) {
                    $appl_ret_array[] = $appl_arr_key;
                }
            }
            $ret_array = $appl_ret_array;
        } else if ($sort_by == 'salary' && !empty($job_applicants_list)) {
            $appl_array = $appl_ret_array = array();
            foreach ($job_applicants_list as $job_applicant) {
                $apl_salary = get_post_meta($job_applicant, 'jobsearch_field_candidate_salary', true);
                $apl_salary = $apl_salary > 0 ? $apl_salary : 0;
                $appl_array[$job_applicant] = $apl_salary;
            }
            arsort($appl_array);
            if (!empty($appl_array)) {
                foreach ($appl_array as $appl_arr_key => $appl_arr) {
                    $appl_ret_array[] = $appl_arr_key;
                }
            }
            $ret_array = $appl_ret_array;
        } else if ($sort_by == 'viewed' && !empty($job_applicants_list)) {
            $viewed_candidates = get_post_meta($job_id, 'jobsearch_viewed_candidates', true);
            if (empty($viewed_candidates)) {
                $viewed_candidates = array();
            }
            $appl_array = $appl_ret_array = array();
            foreach ($job_applicants_list as $job_applicant) {
                if (in_array($job_applicant, $viewed_candidates)) {
                    $appl_array[] = $job_applicant;
                } else {
                    $appl_ret_array[] = $job_applicant;
                }
            }
            $merge_arr = array_merge($appl_array, $appl_ret_array);
            $ret_array = $merge_arr;
        } else if ($sort_by == 'unviewed' && !empty($job_applicants_list)) {
            $viewed_candidates = get_post_meta($job_id, 'jobsearch_viewed_candidates', true);
            if (empty($viewed_candidates)) {
                $viewed_candidates = array();
            }
            $appl_array = $appl_ret_array = array();
            foreach ($job_applicants_list as $job_applicant) {
                if (in_array($job_applicant, $viewed_candidates)) {
                    $appl_array[] = $job_applicant;
                } else {
                    $appl_ret_array[] = $job_applicant;
                }
            }
            $merge_arr = array_merge($appl_ret_array, $appl_array);
            $ret_array = $merge_arr;
        } else if ($sort_by == 'recent' && !empty($job_applicants_list)) {
            arsort($job_applicants_list);
            $ret_array = $job_applicants_list;
        } else {
            $ret_array = $job_applicants_list;
        }

        $retrn_array = jobsearch_is_post_ids_array($ret_array, 'candidate');
        $retrn_array = apply_filters('jobsearch_applicants_sortby_list_arry', $retrn_array, $job_id, $sort_by);
    }

    return $retrn_array;
}
