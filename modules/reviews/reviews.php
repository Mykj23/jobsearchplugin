<?php

/*
  Class : Reviews
 */


// this is an include only WP file
if (!defined('ABSPATH')) {
    die;
}

// main plugin class
class Jobsearch_Reviews {

// hook things up
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'front_enqueue_scripts'));
        add_filter('redux/options/jobsearch_plugin_options/sections', array($this, 'jobsearch_location_plugin_option_fields'));
        add_action('init', array($this, 'titles_translation'));
        $this->load_files();
    }

    public function front_enqueue_scripts() {
        global $sitepress;

        $admin_ajax_url = admin_url('admin-ajax.php');
        if ( function_exists('icl_object_id') ) {
            $lang_code = $sitepress->get_current_language();
            $admin_ajax_url = add_query_arg(array('lang' => $lang_code), $admin_ajax_url);
        }

        wp_enqueue_style('fontawesome-stars', jobsearch_plugin_get_url('modules/reviews/css/fontawesome-stars.css'), array());
        wp_enqueue_style('jobsearch-reviews', jobsearch_plugin_get_url('modules/reviews/css/reviews-style.css'), array());
        
        //
        wp_register_script('jobsearch-add-review', jobsearch_plugin_get_url('modules/reviews/js/reviews-functions.js'), array(), '', true);
        $jobsearch_plugin_arr = array(
            'plugin_url' => jobsearch_plugin_get_url(),
            'ajax_url' => $admin_ajax_url,
            'error_msg' => esc_html__('There is some problem.', 'wp-jobsearch'),
            'loading' => esc_html__('Loading...', 'wp-jobsearch'),
        );

        wp_localize_script('jobsearch-add-review', 'jobsearch_reviews_vars', $jobsearch_plugin_arr);
        wp_register_script('jobsearch-barrating', jobsearch_plugin_get_url('modules/reviews/js/jquery.barrating.js'), array(), '', true);
    }

    public function load_files() {
        include plugin_dir_path(dirname(__FILE__)) . 'reviews/include/frontend.php';
    }

    public function titles_translation() {
        global $jobsearch_plugin_options;
        $review_titles = isset($jobsearch_plugin_options['reviews_titles']) ? $jobsearch_plugin_options['reviews_titles'] : '';
        if (!empty($review_titles)) {
            foreach ($review_titles as $review_title) {
                do_action('wpml_register_single_string', 'JobSearch Options', 'Review Title - ' . $review_title, $review_title);
            }
        }
    }

    public function jobsearch_location_plugin_option_fields($sections) {

        $sections[] = array(
            'title' => __('Reviews Settings', 'wp-jobsearch'),
            'id' => 'reviews-settings',
            'desc' => '',
            'icon' => 'el el-star',
            'fields' => array(
                array(
                    'id' => 'reviews_switch',
                    'type' => 'button_set',
                    'title' => __('Reviews', 'wp-jobsearch'),
                    'subtitle' => __('Switch On/Off Reviews.', 'wp-jobsearch'),
                    'desc' => '',
                    'options' => array(
                        'on' => __('On', 'wp-jobsearch'),
                        'off' => __('Off', 'wp-jobsearch'),
                    ),
                    'default' => 'on',
                ),
                array(
                    'id' => 'candidate_reviews_switch',
                    'type' => 'button_set',
                    'title' => __('Candidate Reviews', 'wp-jobsearch'),
                    'subtitle' => __('Switch On/Off Reviews at candidate detail page.', 'wp-jobsearch'),
                    'desc' => '',
                    'options' => array(
                        'on' => __('On', 'wp-jobsearch'),
                        'off' => __('Off', 'wp-jobsearch'),
                    ),
                    'default' => 'off',
                ),
                array(
                    'id' => 'reviews_titles',
                    'type' => 'multi_text',
                    'title' => __('Review Titles', 'wp-jobsearch'),
                    'subtitle' => '',
                    'desc' => __('Add multiple reviews titles.', 'wp-jobsearch'),
                    'default' => array(__('Quality', 'wp-jobsearch'), __('Communication', 'wp-jobsearch'), __('Goodwill', 'wp-jobsearch')),
                ),
                array(
                    'id' => 'review_text_length',
                    'type' => 'text',
                    'title' => __('Review Text Length', 'wp-jobsearch'),
                    'subtitle' => '',
                    'desc' => __('Define Maximum characters Length for Review Text.', 'wp-jobsearch'),
                    'default' => '500',
                ),
            ),
        );
        return $sections;
    }

}

// class Jobsearch_Reviews 
$Jobsearch_Reviews_obj = new Jobsearch_Reviews();
global $Jobsearch_Reviews_obj;

