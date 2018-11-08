<?php
/**
 * JobSearch Candidate Admin Widget Class
 *
 * @package Candidate Admin Widget
 */
if (!class_exists('JobSearch_Candidate_Widget')) {

    /**
      JobSearch  Image Ads class used to implement the Custom flicker gallery widget.
     */
    class JobSearch_Candidate_Widget {

        /**
         * Sets up a new jobsearch candidate widget instance.
         */
        public function __construct() {
            add_action('wp_dashboard_setup', array($this, 'jobsearch_register_candidate_dashboard_widget'));
        }

        public function jobsearch_register_candidate_dashboard_widget() {
            wp_add_dashboard_widget(
                    'jobsearch_candidate_dashboard_widget', esc_html__('Candidate Statistics', 'wp-jobsearch'), array($this, 'jobsearch_candidate_dashboard_widget_display')
            );
        }

        public function jobsearch_candidate_dashboard_widget_display() {
            // total number of candidate
            $current_timestamp = current_time('timestamp');
            $total_candidates = jobsearch_count_custom_post_with_filter('candidate');
            
            $arg = array(
                array(
                    'key' => 'jobsearch_field_candidate_approved',
                    'value' => 'on',
                    'compare' => '=',
                ),
            );
            $total_active_candidates = jobsearch_count_custom_post_with_filter('candidate', $arg);
            
            $arg = array(
                array(
                    'key' => 'jobsearch_field_candidate_approved',
                    'value' => 'on',
                    'compare' => '!=',
                ),
            );
            $total_pending_candidates = jobsearch_count_custom_post_with_filter('candidate', $arg);
            ?>
            <ul class="jobsearch-candidates-admin-widget jobsearch-job-admin-widget">	
                <li class="tot-jobs">
                    <a href="<?php echo admin_url('/edit.php?post_type=candidate') ?>"><strong><i class="fa fa-globe fa-lg"></i> <?php echo absint($total_candidates); ?></strong> <?php echo esc_html__('Candidates', 'wp-jobsearch') ?></a>
                </li> 
                <li class="tot-active-jobs">
                    <a href="<?php echo admin_url('/edit.php?post_type=candidate&candidate_status=approved') ?>"><strong><i class="fa fa-check-circle-o fa-lg"></i> <?php echo absint($total_active_candidates); ?></strong> <?php echo esc_html__('Active Candidates', 'wp-jobsearch') ?></a>
                </li>
                <li class="tot-pending-jobs">
                    <a href="<?php echo admin_url('/edit.php?post_type=candidate&candidate_status=pending') ?>"><strong><i class="fa fa-clock-o fa-lg"></i> <?php echo absint($total_pending_candidates); ?></strong> <?php echo esc_html__('Pending Candidates', 'wp-jobsearch') ?></a>
                </li>
            </ul>
            <?php
        }

    }

    // class Candidatesearch_CustomField 
    $JobSearch_Candidate_Widget_obj = new JobSearch_Candidate_Widget();
    global $JobSearch_Candidate_Widget_obj;
} 

