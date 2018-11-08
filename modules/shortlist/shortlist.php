<?php
/**
 * Directory Plus Shortlists Module
 */
// Direct access not allowed.
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Jobsearch_Shortlist')) {

    class Jobsearch_Shortlist {

        /**
         * Start construct Functions
         */
        public function __construct() {

            // Initialize Addon
            add_action('init', array($this, 'init'));
        }

        /**
         * Initialize application, load text domain, enqueue scripts and bind hooks
         */
        public function init() {

            // Add actions
            add_action('jobsearch_job_shortlist_button_frontend', array($this, 'jobsearch_job_shortlist_button_callback'), 11, 1);
            add_action('wp_ajax_jobsearch_add_candidate_job_to_favourite', array($this, 'jobsearch_shortlist_submit_callback'), 11);
            add_action('wp_ajax_jobsearch_removed_shortlist', array($this, 'jobsearch_removed_shortlist_callback'), 11);
            add_action('wp_enqueue_scripts', array($this, 'jobsearch_shortlist_enqueue_scripts'), 10);
        }

        public function jobsearch_shortlist_enqueue_scripts() {
            global $sitepress;

            $admin_ajax_url = admin_url('admin-ajax.php');
            if ( function_exists('icl_object_id') ) {
                $lang_code = $sitepress->get_current_language();
                $admin_ajax_url = add_query_arg(array('lang' => $lang_code), $admin_ajax_url);
            }
            
            // Enqueue JS 
            wp_register_script('jobsearch-shortlist-functions-script', plugins_url('assets/js/shortlist-functions.js', __FILE__), '', '', true);
            wp_localize_script('jobsearch-shortlist-functions-script', 'jobsearch_shortlist_vars', array(
                'admin_url' => $admin_ajax_url,
                'plugin_url' => jobsearch_plugin_get_url(),
            ));
            wp_enqueue_script('jobsearch-shortlist-functions-script');
        }

        /**
         * Member Shortlists Frontend Button
         * @ shortlists frontend buuton based on job id
         */
        public function jobsearch_job_shortlist_button_callback($args = '') {
            wp_enqueue_script('jobsearch-shortlist-script');

            $job_id = isset($args['job_id']) ? $args['job_id'] : '';
            $before_icon = isset($args['before_icon']) ? $args['before_icon'] : '';
            $after_icon = isset($args['after_icon']) ? $args['after_icon'] : '';
            $container_class = isset($args['container_class']) ? $args['container_class'] : '';
            $anchor_class = isset($args['anchor_class']) ? $args['anchor_class'] : '';

            if ($anchor_class == '') {
                $anchor_class = 'jobsearch-job-like';
            }

            if (!is_user_logged_in()) {
                ?>
                <div class="like-btn <?php echo ($container_class) ?>">
                    <a href="javascript:void(0);" class="shortlist jobsearch-open-signin-tab <?php echo ($anchor_class) ?>">
                        <i class="fa fa-heart-o"></i>
                    </a>
                </div>
                <?php
            } else {
                $user_id = get_current_user_id();
                $user_is_candidate = jobsearch_user_is_candidate($user_id);
                $candidate_fav_jobs_list = array();
                if ($user_is_candidate) {
                    $candidate_id = jobsearch_get_user_candidate_id($user_id);
                    $candidate_fav_jobs_list = get_post_meta($candidate_id, 'jobsearch_fav_jobs_list', true);
                    $candidate_fav_jobs_list = explode(',', $candidate_fav_jobs_list);
                }
                ?>
                <div class="like-btn <?php echo ($container_class) ?>">
                    <a href="javascript:void(0);" class="shortlist <?php echo in_array($job_id, $candidate_fav_jobs_list) ? '' : 'jobsearch-add-job-to-favourite' ?> <?php echo ($anchor_class) ?>" data-id="<?php echo ($job_id) ?>" data-before-icon="<?php echo ($before_icon) ?>" data-after-icon="<?php echo ($after_icon) ?>">
                        <i class="<?php echo in_array($job_id, $candidate_fav_jobs_list) ? 'fa fa-heart' : 'fa fa-heart-o' ?>"></i>
                    </a>
                    <span class="job-to-fav-msg-con"></span>
                </div>
                <?php
            }
        }

        /**
         * Member Shortlists
         * @ added member shortlists based on job id
         */
        public function jobsearch_shortlist_submit_callback() {
            if (!is_user_logged_in()) {
                echo json_encode(array('msg' => esc_html__('You are not logged in.', 'wp-jobsearch'), 'error' => '1'));
                die;
            }

            //
            $job_id = isset($_POST['job_id']) ? $_POST['job_id'] : '0';
            $user_id = get_current_user_id();
            $user_is_candidate = jobsearch_user_is_candidate($user_id);
            if ($user_is_candidate) {
                $candidate_id = jobsearch_get_user_candidate_id($user_id);
                $candidate_fav_jobs_list = get_post_meta($candidate_id, 'jobsearch_fav_jobs_list', true);

                if ($candidate_fav_jobs_list != '') {
                    $candidate_fav_jobs_list = explode(',', $candidate_fav_jobs_list);
                    if (!in_array($job_id, $candidate_fav_jobs_list)) {
                        $candidate_fav_jobs_list[] = $job_id;
                    }
                    $candidate_fav_jobs_list = implode(',', $candidate_fav_jobs_list);
                } else {
                    $candidate_fav_jobs_list = $job_id;
                }
                update_post_meta($candidate_id, 'jobsearch_fav_jobs_list', $candidate_fav_jobs_list);
                echo json_encode(array('msg' => esc_html__('Job added to list.', 'wp-jobsearch')));
                die;
            } else {
                echo json_encode(array('msg' => esc_html__('You are not a candidate.', 'wp-jobsearch'), 'error' => '1'));
                die;
            }
        }

        /**
         * Member Removed Shortlist
         * @ removed member shortlists based on job id
         */
        public function jobsearch_removed_shortlist_callback() {
            
        }

    }

    global $jobsearch_shortlist;
    $jobsearch_shortlist = new Jobsearch_Shortlist();
}