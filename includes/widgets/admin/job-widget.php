<?php
/**
 * JobSearch Job Admin Widget Class
 *
 * @package Job Admin Widget
 */
if (!class_exists('JobSearch_Job_Widget')) {

    /**
      JobSearch  Image Ads class used to implement the Custom flicker gallery widget.
     */
    class JobSearch_Job_Widget {

        /**
         * Sets up a new jobsearch job widget instance.
         */
        public function __construct() {
            add_action('wp_dashboard_setup', array($this, 'jobsearch_register_job_dashboard_widget'));
        }

        public function jobsearch_register_job_dashboard_widget() {
            wp_add_dashboard_widget(
                    'jobsearch_job_dashboard_widget', esc_html__('Job Statistics', 'wp-jobsearch'), array($this, 'jobsearch_job_dashboard_widget_display')
            );
        }

        public function jobsearch_job_dashboard_widget_display() {
            // total number of jobs
            $current_timestamp = current_time('timestamp');

            $total_jobs = jobsearch_count_custom_post_with_filter('job');
            $arg = array(
                array(
                    'key' => 'jobsearch_field_job_publish_date',
                    'value' => $current_timestamp,
                    'compare' => '<=',
                ),
                array(
                    'key' => 'jobsearch_field_job_expiry_date',
                    'value' => $current_timestamp,
                    'compare' => '>=',
                ),
                array(
                    'key' => 'jobsearch_field_job_status',
                    'value' => 'approved',
                    'compare' => '=',
                ),
            );
            $total_active_jobs = jobsearch_count_custom_post_with_filter('job', $arg);
            $arg = array(
                array(
                    'key' => 'jobsearch_field_job_status',
                    'value' => 'approved',
                    'compare' => '!=',
                ),
            );
            $total_pending_jobs = jobsearch_count_custom_post_with_filter('job', $arg);
            $arg = array(
                array(
                    'key' => 'jobsearch_field_job_expiry_date',
                    'value' => $current_timestamp,
                    'compare' => '<=',
                ),
            );
            $total_expired_jobs = jobsearch_count_custom_post_with_filter('job', $arg);
            ?>
            <ul class="jobsearch-job-admin-widget">	
                <li class="tot-jobs">
                    <a href="<?php echo admin_url('/edit.php?post_type=job') ?>"><strong><i class="fa fa-globe fa-lg"></i> <?php echo absint($total_jobs); ?></strong> <?php echo esc_html__('Jobs', 'wp-jobsearch') ?></a>				
                </li>
                <li class="tot-active-jobs">
                    <a href="<?php echo admin_url('/edit.php?post_type=job&job_status=approved') ?>"><strong><i class="fa fa-check-circle-o fa-lg"></i> <?php echo absint($total_active_jobs); ?></strong> <?php echo esc_html__('Active Jobs', 'wp-jobsearch') ?></a>
                </li>
                <li class="tot-pending-jobs">
                    <a href="<?php echo admin_url('/edit.php?post_type=job&job_status=pending') ?>"><strong><i class="fa fa-clock-o fa-lg"></i> <?php echo absint($total_pending_jobs); ?></strong> <?php echo esc_html__('Pending Jobs', 'wp-jobsearch') ?></a>
                </li>
                <li class="tot-expiry-jobs">
                    <strong><i class="fa fa-calendar-times-o fa-lg"></i> <?php echo absint($total_expired_jobs); ?></strong> <?php echo esc_html__('Expired Jobs', 'wp-jobsearch') ?>    
                </li> 
            </ul>
            <?php
        }

    }

    // class Jobsearch_CustomField 
    $JobSearch_Job_Widget_obj = new JobSearch_Job_Widget();
    global $JobSearch_Job_Widget_obj;
} 

