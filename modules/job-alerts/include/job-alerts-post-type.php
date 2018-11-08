<?php
/*
  Class : Job Alerts Post Type
 */


// this is an include only WP file
if (!defined('ABSPATH')) {
    die;
}

// main plugin class
class JobSearch_Job_Alerts_Post {

// hook things up
    public function __construct() {

        add_action('init', array($this, 'register_post'));
        //
        add_action('add_meta_boxes', array($this, 'meta_box_for_job_alerts'));
    }

    public function register_post() {

        $labels = array(
            'name' => _x('Job Alerts', 'post type general name', 'wp-jobsearch'),
            'singular_name' => _x('Job Alert', 'post type singular name', 'wp-jobsearch'),
            'menu_name' => _x('Job Alerts', 'admin menu', 'wp-jobsearch'),
            'name_admin_bar' => _x('Job Alert', 'add new on admin bar', 'wp-jobsearch'),
            'add_new' => _x('Add New', 'book', 'wp-jobsearch'),
            'add_new_item' => esc_html__('Add New Job Alert', 'wp-jobsearch'),
            'new_item' => esc_html__('New Job Alert', 'wp-jobsearch'),
            'edit_item' => esc_html__('Edit Job Alert', 'wp-jobsearch'),
            'view_item' => esc_html__('View Job Alert', 'wp-jobsearch'),
            'all_items' => esc_html__('Job Alerts', 'wp-jobsearch'),
            'search_items' => esc_html__('Search Job Alerts', 'wp-jobsearch'),
            'parent_item_colon' => esc_html__('Parent Job Alerts:', 'wp-jobsearch'),
            'not_found' => esc_html__('No Job Alerts found.', 'wp-jobsearch'),
            'not_found_in_trash' => esc_html__('No Job Alerts found in Trash.', 'wp-jobsearch'),
        );

        $args = array(
            'labels' => $labels,
            'description' => esc_html__('This allows user to manage job alerts.', 'wp-jobsearch'),
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => 'edit.php?post_type=job',
            'query_var' => true,
            'capability_type' => 'post',
            'publicly_queryable' => false,
            'exclude_from_search' => true,
            'hierarchical' => false,
            'rewrite' => array('slug' => 'job-alert'),
            'supports' => false,
            'has_archive' => false,
        );

        // Register custom post type.
        register_post_type("job-alert", $args);
    }

    public function meta_box_for_job_alerts() {
        add_meta_box('job_alert_meta_options', esc_html__('Job Alert Options', 'wp-jobsearch'), array($this, 'meta_box_options'), 'job-alert', 'normal', 'high');
    }
    
    public function meta_box_options() {
        global $jobsearch_form_fields;
        
        ?>
        <div class="jobsearch-post-settings">
            <div class="jobsearch-element-field">
                <div class="elem-label">
                    <label><?php esc_html_e('Email', 'wp-jobsearch') ?></label>
                </div>
                <div class="elem-field">
                    <?php
                    $field_params = array(
                        'name' => 'alert_email',
                    );
                    $jobsearch_form_fields->input_field($field_params);
                    ?>
                </div>
            </div>
            <div class="jobsearch-element-field">
                <div class="elem-label">
                    <label><?php esc_html_e('Name', 'wp-jobsearch') ?></label>
                </div>
                <div class="elem-field">
                    <?php
                    $field_params = array(
                        'name' => 'alert_name',
                    );
                    $jobsearch_form_fields->input_field($field_params);
                    ?>
                </div>
            </div>
            <div class="jobsearch-element-field">
                <div class="elem-label">
                    <label><?php esc_html_e('Alert Query', 'wp-jobsearch') ?></label>
                </div>
                <div class="elem-field">
                    <?php
                    $field_params = array(
                        'name' => 'alert_query',
                    );
                    $jobsearch_form_fields->textarea_field($field_params);
                    ?>
                </div>
            </div>
            <div class="jobsearch-element-field">
                <div class="elem-label">
                    <label><?php esc_html_e('Annually Frequency', 'wp-jobsearch') ?></label>
                </div>
                <div class="elem-field">
                    <?php
                    $field_params = array(
                        'name' => 'alert_annually',
                    );
                    $jobsearch_form_fields->checkbox_field($field_params);
                    ?>
                </div>
            </div>
            <div class="jobsearch-element-field">
                <div class="elem-label">
                    <label><?php esc_html_e('Biannually Frequency', 'wp-jobsearch') ?></label>
                </div>
                <div class="elem-field">
                    <?php
                    $field_params = array(
                        'name' => 'alert_biannually',
                    );
                    $jobsearch_form_fields->checkbox_field($field_params);
                    ?>
                </div>
            </div>
            <div class="jobsearch-element-field">
                <div class="elem-label">
                    <label><?php esc_html_e('Monthly Frequency', 'wp-jobsearch') ?></label>
                </div>
                <div class="elem-field">
                    <?php
                    $field_params = array(
                        'name' => 'alert_monthly',
                    );
                    $jobsearch_form_fields->checkbox_field($field_params);
                    ?>
                </div>
            </div>
            <div class="jobsearch-element-field">
                <div class="elem-label">
                    <label><?php esc_html_e('Fortnightly Frequency', 'wp-jobsearch') ?></label>
                </div>
                <div class="elem-field">
                    <?php
                    $field_params = array(
                        'name' => 'alert_fortnightly',
                    );
                    $jobsearch_form_fields->checkbox_field($field_params);
                    ?>
                </div>
            </div>
            <div class="jobsearch-element-field">
                <div class="elem-label">
                    <label><?php esc_html_e('Weekly Frequency', 'wp-jobsearch') ?></label>
                </div>
                <div class="elem-field">
                    <?php
                    $field_params = array(
                        'name' => 'alert_weekly',
                    );
                    $jobsearch_form_fields->checkbox_field($field_params);
                    ?>
                </div>
            </div>
            <div class="jobsearch-element-field">
                <div class="elem-label">
                    <label><?php esc_html_e('Daily Frequency', 'wp-jobsearch') ?></label>
                </div>
                <div class="elem-field">
                    <?php
                    $field_params = array(
                        'name' => 'alert_daily',
                    );
                    $jobsearch_form_fields->checkbox_field($field_params);
                    ?>
                </div>
            </div>
            <div class="jobsearch-element-field">
                <div class="elem-label">
                    <label><?php esc_html_e('Never', 'wp-jobsearch') ?></label>
                </div>
                <div class="elem-field">
                    <?php
                    $field_params = array(
                        'name' => 'alert_never',
                    );
                    $jobsearch_form_fields->checkbox_field($field_params);
                    ?>
                </div>
            </div>
        </div>
        <?php
    }

}

// Class JobSearch_Job_Alerts_Hooks
$JobSearch_Job_Alerts_Post_obj = new JobSearch_Job_Alerts_Post();
global $JobSearch_Job_Alerts_Post_obj;
