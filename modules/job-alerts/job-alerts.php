<?php

/*
  Class : Job Alerts
 */


// this is an include only WP file
if (!defined('ABSPATH')) {
    die;
}

// main plugin class
class JobSearch_Job_Alerts {

    public static $job_details = array();

    // hook things up
    public function __construct() {
        $this->load_files();
        add_action('wp_enqueue_scripts', array($this, 'front_enqueue_scripts'));

        add_filter('cron_schedules', array($this, 'add_cron_recurrence_interval'));
        add_action('jobsearch_job_alerts_schedule', array($this, 'job_alerts_schedule_callback'));

        //
        add_action('init', array($this, 'create_alerts_schedule'));
        register_activation_hook(__FILE__, array($this, 'create_alerts_schedule'));
    }
    
    public function front_enqueue_scripts() {

        global $jobsearch_plugin_options, $sitepress;

        $admin_ajax_url = admin_url('admin-ajax.php');
        if (function_exists('icl_object_id')) {
            $lang_code = $sitepress->get_current_language();
            //$admin_ajax_url = add_query_arg(array('lang' => $lang_code), $admin_ajax_url);
        }

        $job_alerts_switch = isset($jobsearch_plugin_options['job_alerts_switch']) ? $jobsearch_plugin_options['job_alerts_switch'] : '';

        if ($job_alerts_switch == 'on') {
            wp_enqueue_style('jobsearch-job-alerts', jobsearch_plugin_get_url('modules/job-alerts/css/job-alerts.css'));
        }

        //
        if ($job_alerts_switch == 'on') {
            wp_enqueue_script('jobsearch-job-alerts-scripts', jobsearch_plugin_get_url('modules/job-alerts/js/job-alerts.js'), array(), JobSearch_plugin::get_version(), true);
            $jobsearch_plugin_arr = array(
                'plugin_url' => jobsearch_plugin_get_url(),
                'ajax_url' => $admin_ajax_url,
                'error_msg' => esc_html__('There is some problem.', 'wp-jobsearch'),
                'submit_txt' => esc_html__('Save Job Alert', 'wp-jobsearch'),
            );

            wp_localize_script('jobsearch-job-alerts-scripts', 'jobsearch_jobalerts_vars', $jobsearch_plugin_arr);
        }
    }

    public function load_files() {
        include plugin_dir_path(dirname(__FILE__)) . 'job-alerts/include/job-alerts-hooks.php';
        include plugin_dir_path(dirname(__FILE__)) . 'job-alerts/include/job-alerts-post-type.php';
        include plugin_dir_path(dirname(__FILE__)) . 'job-alerts/include/job-alerts-email-template.php';
    }

    function add_cron_recurrence_interval($schedules) {

        $schedules['every_five_sec'] = array(
            'interval' => 5,
            'display' => __('Every 5 sec', 'wp-jobsearch')
        );

        return $schedules;
    }

    public function create_alerts_schedule() {
        global $jobsearch_plugin_options;
        $job_alerts_switch = isset($jobsearch_plugin_options['job_alerts_switch']) ? $jobsearch_plugin_options['job_alerts_switch'] : '';
        // Use wp_next_scheduled to check if the event is already scheduled.
        $timestamp = wp_next_scheduled('jobsearch_job_alerts_schedule');
        if (!$timestamp && $job_alerts_switch == 'on') {
            wp_schedule_event(time(), 'daily', 'jobsearch_job_alerts_schedule');
        } else if ($job_alerts_switch != 'on') {
            wp_unschedule_event($timestamp, 'jobsearch_job_alerts_schedule');
        }
    }

    public function job_alerts_schedule_callback() {
        // Get alerts
        $args = array(
            'post_type' => 'job-alert',
            'post_status' => 'publish',
            'fields' => 'ids',
            'order' => 'DESC',
            'orderby' => 'ID',
        );
        $job_details = array();
        $job_alerts = new WP_Query($args);
        if ($job_alerts->have_posts()) {
            while ($job_alerts->have_posts()) : $job_alerts->the_post();

                $alert_id = get_the_ID();
                
                $frequency_annually = get_post_meta($alert_id, 'jobsearch_field_alert_annually', true);
                $frequency_biannually = get_post_meta($alert_id, 'jobsearch_field_alert_biannually', true);
                $frequency_monthly = get_post_meta($alert_id, 'jobsearch_field_alert_monthly', true);
                $frequency_fortnightly = get_post_meta($alert_id, 'jobsearch_field_alert_fortnightly', true);
                $frequency_weekly = get_post_meta($alert_id, 'jobsearch_field_alert_weekly', true);
                $frequency_daily = get_post_meta($alert_id, 'jobsearch_field_alert_daily', true);
                $frequency_never = get_post_meta($alert_id, 'jobsearch_field_alert_never', true);
                $last_time_email_sent = get_post_meta($alert_id, 'last_time_email_sent', true);

                $set_frequency = '';
                if (!empty($frequency_annually)) {
                    $selected_frequency = '+365 days';
                    $set_frequency = esc_html__('Annually', 'wp-jobsearch');
                } else if (!empty($frequency_biannually)) {
                    $selected_frequency = '+182 days';
                    $set_frequency = esc_html__('Biannually', 'wp-jobsearch');
                } else if (!empty($frequency_monthly)) {
                    $selected_frequency = '+30 days';
                    $set_frequency = esc_html__('Monthly', 'wp-jobsearch');
                } else if (!empty($frequency_fortnightly)) {
                    $selected_frequency = '+15 days';
                    $set_frequency = esc_html__('Fortnightly', 'wp-jobsearch');
                } else if (!empty($frequency_weekly)) {
                    $selected_frequency = '+7 days';
                    $set_frequency = esc_html__('Weekly', 'wp-jobsearch');
                } else if (!empty($frequency_daily)) {
                    $selected_frequency = '+1 days';
                    $set_frequency = esc_html__('Daily', 'wp-jobsearch');
                } else if (!empty($frequency_never)) {
                    $selected_frequency = false;
                    $set_frequency = esc_html__('Never', 'wp-jobsearch');
                } else {
                    $selected_frequency = false;
                    $set_frequency = '';
                }
                if ($selected_frequency != false) {

                    if (time() > strtotime($selected_frequency, intval($last_time_email_sent))) {
                        // Set this for email data.
                        $gjobs_query = get_post_meta($alert_id, 'jobsearch_field_alert_jobs_query', true);
                        $gjobs_query = str_replace('< =', '<=', $gjobs_query);
                        self::$job_details = array(
                            'id' => $alert_id,
                            'title' => get_the_title(),
                            'jobs_query' => json_decode($gjobs_query, true),
                            'email' => get_post_meta($alert_id, 'jobsearch_field_alert_email', true),
                            'page_url' => get_post_meta($alert_id, 'jobsearch_field_alert_page_url', true),
                            'url_query' => get_post_meta($alert_id, 'jobsearch_field_alert_query', true),
                            'frequency' => $selected_frequency,
                            'set_frequency' => $set_frequency,
                        );
                        $template = '';
                        // Checking email notification is enabled/disabled.
                        $al_jobs_count = self::get_job_alerts_count(self::$job_details['jobs_query'], self::$job_details['frequency']);
                        
                        if ($al_jobs_count > 0) {
                            do_action('jobsearch_new_job_alerts_email', self::$job_details);
                        }
                    }
                }
            endwhile;
        }
        wp_reset_postdata();
    }

    public static function get_job_alerts_count($jobs_query, $frequency) {
        $frequency = str_replace('+', '-', $frequency);
        
        $jobs_query['posts_per_page'] = 1;
        $loop_count = new WP_Query($jobs_query);
        return $loop_count->found_posts;
    }

}

global $JobSearch_Job_Alerts_obj;
$JobSearch_Job_Alerts_obj = new JobSearch_Job_Alerts();
