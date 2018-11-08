<?php
/**
 * JobSearch Employer Admin Widget Class
 *
 * @package Employer Admin Widget
 */
if (!class_exists('JobSearch_Employer_Widget')) {

    /**
      JobSearch  Image Ads class used to implement the Custom flicker gallery widget.
     */
    class JobSearch_Employer_Widget {

        /**
         * Sets up a new jobsearch employer widget instance.
         */
        public function __construct() {
            add_action('wp_dashboard_setup', array($this, 'jobsearch_register_employer_dashboard_widget'));
        }

        public function jobsearch_register_employer_dashboard_widget() {
            wp_add_dashboard_widget(
                    'jobsearch_employer_dashboard_widget', esc_html__('Employer Statistics', 'wp-jobsearch'), array($this, 'jobsearch_employer_dashboard_widget_display')
            );
        }

        public function jobsearch_employer_dashboard_widget_display() {
            // total number of employer
            $current_timestamp = current_time('timestamp');
            $total_employers = jobsearch_count_custom_post_with_filter('employer');
            
            $arg = array(
                array(
                    'key' => 'jobsearch_field_employer_approved',
                    'value' => 'on',
                    'compare' => '=',
                ),
            );
            $total_active_employers = jobsearch_count_custom_post_with_filter('employer', $arg);
            
            $arg = array(
                array(
                    'key' => 'jobsearch_field_employer_approved',
                    'value' => 'on',
                    'compare' => '!=',
                ),
            );
            $total_pending_employers = jobsearch_count_custom_post_with_filter('employer', $arg);
            ?>
            <ul class="jobsearch-employers-admin-widget jobsearch-job-admin-widget">	
                <li class="tot-employers">
                    <a href="<?php echo admin_url('/edit.php?post_type=employer') ?>"><strong><i class="fa fa-globe fa-lg"></i> <?php echo absint($total_employers); ?></strong> <?php echo esc_html__('Employers', 'wp-jobsearch') ?></a>
                </li>
                <li class="tot-active-jobs">
                    <a href="<?php echo admin_url('/edit.php?post_type=employer&employer_status=approved') ?>"><strong><i class="fa fa-check-circle-o fa-lg"></i> <?php echo absint($total_active_employers); ?></strong> <?php echo esc_html__('Active Employers', 'wp-jobsearch') ?></a>
                </li>
                <li class="tot-pending-jobs">
                    <a href="<?php echo admin_url('/edit.php?post_type=employer&employer_status=pending') ?>"><strong><i class="fa fa-clock-o fa-lg"></i> <?php echo absint($total_pending_employers); ?></strong> <?php echo esc_html__('Pending Employers', 'wp-jobsearch') ?></a>
                </li>
            </ul>
            <?php
        }

    }

    // class Employersearch_CustomField 
    $JobSearch_Employer_Widget_obj = new JobSearch_Employer_Widget();
    global $JobSearch_Employer_Widget_obj;
} 

