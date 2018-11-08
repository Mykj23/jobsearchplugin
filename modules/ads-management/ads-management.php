<?php

/*
  Class : JobSearch_Ads_management
 */


// this is an include only WP file
if (!defined('ABSPATH')) {
    die;
}

// main plugin class
class JobSearch_Ads_management {

    // hook things up
    public function __construct() {
        $this->load_files();
        add_action('wp_enqueue_scripts', array($this, 'front_enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'backend_enqueue_scripts'));
    }

    public function backend_enqueue_scripts() {

        global $jobsearch_plugin_options;
        $ads_management_switch = isset($jobsearch_plugin_options['ads_management_switch']) ? $jobsearch_plugin_options['ads_management_switch'] : '';
    }

    public function front_enqueue_scripts() {

        global $jobsearch_plugin_options;
        $ads_management_switch = isset($jobsearch_plugin_options['ads_management_switch']) ? $jobsearch_plugin_options['ads_management_switch'] : '';

        if ($ads_management_switch == 'on') {
            wp_enqueue_style('jobsearch-ads-management-styles', jobsearch_plugin_get_url('modules/ads-management/css/ads-management.css'), array(), JobSearch_plugin::get_version());
            wp_register_script('jobsearch-ads-management-scripts', jobsearch_plugin_get_url('modules/ads-management/js/ads-management.js'), array(), JobSearch_plugin::get_version(), true);
            $jobsearch_plugin_arr = array(
                'plugin_url' => jobsearch_plugin_get_url(),
                'ajax_url' => admin_url('admin-ajax.php'),
                'error_msg' => esc_html__('There is some problem.', 'wp-jobsearch'),
                'submit_txt' => esc_html__('Submit', 'wp-jobsearch'),
            );

            wp_localize_script('jobsearch-ads-management-scripts', 'jobsearch_ads_manage_vars', $jobsearch_plugin_arr);
        }
    }

    public function load_files() {
        include plugin_dir_path(dirname(__FILE__)) . 'ads-management/include/redux-ext/loader.php';
        include plugin_dir_path(dirname(__FILE__)) . 'ads-management/include/ads-management-hooks.php';
    }

}

// Class JobSearch_Ads_management
$JobSearch_Ads_management_obj = new JobSearch_Ads_management();
