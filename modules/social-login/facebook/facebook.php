<?php
/*
 * Import the Facebook SDK and load all the classes
 */
include (plugin_dir_path(__FILE__) . 'facebook-sdk/autoload.php');

/*
 * Classes required to call the Facebook API
 * They will be used by our class
 */

use Facebook\Facebook;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Exceptions\FacebookResponseException;

/**
 * Class JobsearchFacebook
 */
class JobsearchFacebook {

    /**
     * Facebook APP ID
     *
     * @var string
     */
    private $app_id = '';

    /**
     * Facebook APP Secret
     *
     * @var string
     */
    private $app_secret = '';

    /**
     * Callback URL used by the API
     *
     * @var string
     */
    private $callback_url = '';

    /**
     * Access token from Facebook
     *
     * @var string
     */
    private $access_token;

    /**
     * Where we redirect our user after the process
     *
     * @var string
     */
    private $redirect_url;

    /**
     * User details from the API
     */
    private $facebook_details;

    /**
     * JobsearchFacebook constructor.
     */
    public function __construct() {
        global $jobsearch_plugin_options;

        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $facebook_app_id = isset($jobsearch_plugin_options['jobsearch-facebook-app-id']) ? $jobsearch_plugin_options['jobsearch-facebook-app-id'] : '';
        $facebook_app_secret = isset($jobsearch_plugin_options['jobsearch-facebook-app-secret']) ? $jobsearch_plugin_options['jobsearch-facebook-app-secret'] : '';
        
        $user_login_page_id = isset($jobsearch_plugin_options['user-login-template-page']) ? $jobsearch_plugin_options['user-login-template-page'] : '';
        $user_login_page_id = jobsearch__get_post_id($user_login_page_id, 'page');

        $user_login_page_url = $user_login_page_id > 0 ? get_permalink($user_login_page_id) : home_url('/');

        $real_redirect_url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        if ($real_redirect_url == admin_url('admin-ajax.php')) {
            $real_redirect_url = $user_login_page_url;
        }

        setcookie('facebook_redirect_url', $real_redirect_url, time() + (360), "/");

        $this->app_id = $facebook_app_id;
        $this->app_secret = $facebook_app_secret;

        $this->callback_url = admin_url('admin-ajax.php?action=jobsearch_facebook');
        // We register our shortcode
        add_shortcode('jobsearch_facebook_login', array($this, 'renderShortcode'));

        if (!isset($_GET['jobsearch_instagram_login'])) {
            // Callback URL
            add_action('wp_ajax_jobsearch_facebook', array($this, 'apiCallback'));
            add_action('wp_ajax_nopriv_jobsearch_facebook', array($this, 'apiCallback'));

            //
            add_action('jobsearch_apply_job_with_fb', array($this, 'apply_job_with_fb'), 10, 1);

            add_action('wp_ajax_jobsearch_applying_job_with_facebook', array($this, 'applying_job_with_facebook'));
            add_action('wp_ajax_nopriv_jobsearch_applying_job_with_facebook', array($this, 'applying_job_with_facebook'));

            //
            add_action('jobsearch_do_apply_job_fb', array($this, 'do_apply_job_with_facebook'), 10, 1);
        }
    }

    /**
     * Render the shortcode [jobsearch_facebook/]
     *
     * It displays our Login / Register button
     */
    public function renderShortcode() {
        global $jobsearch_plugin_options;
        $user_dashboard_page = isset($jobsearch_plugin_options['user-dashboard-template-page']) ? $jobsearch_plugin_options['user-dashboard-template-page'] : '';
        $user_dashboard_page = jobsearch__get_post_id($user_dashboard_page, 'page');

        $dashboard_page_url = $user_dashboard_page > 0 ? get_permalink($user_dashboard_page) : home_url('/');
        // No need for the button is the user is already logged
        if (is_user_logged_in())
            return;

        // We save the URL for the redirection:
        if (!get_transient('jobsearch_facebook_url')) {
            set_transient('jobsearch_facebook_url', $dashboard_page_url, 60 * 60 * 24 * 30);
        }

        // Different labels according to whether the user is allowed to register or not
        if (get_option('users_can_register')) {
            $button_label = __('Login or Register with Facebook', 'wp-jobsearch');
        } else {
            $button_label = __('Login with Facebook', 'wp-jobsearch');
        }

        $html = '';

        // Messages
        if (get_transient('jobsearch_facebook_message')) {

            $message = get_transient('jobsearch_facebook_message');
            if (isset($message['content'])) {
                $message = $message['content'];
            }
            
            $html .= '<div id="jobsearch-facebook-message" class="alert alert-danger">' . $message . '</div>';
            // We remove them from the session
            delete_transient('jobsearch_facebook_message');
        }

        // Button
        if ($this->app_id != '' && $this->app_secret != '') {
            $html .= '<li><a class="jobsearch-facebook-bg" href="' . $this->getLoginUrl() . '"><i class="fa fa-facebook"></i>' . __('Login with Facebook', 'wp-jobsearch') . '</a></li>';
        }

        // Write it down
        return $html;
    }

    /**
     * Init the API Connection
     *
     * @return Facebook
     */
    private function initApi() {

        $facebook = new Facebook([
            'app_id' => $this->app_id,
            'app_secret' => $this->app_secret,
            'default_graph_version' => 'v2.10',
            'persistent_data_handler' => 'session'
        ]);

        return $facebook;
    }

    /**
     * Login URL to Facebook API
     *
     * @return string
     */
    private function getLoginUrl() {

        $fb = $this->initApi();

        $helper = $fb->getRedirectLoginHelper();

        // Optional permissions
        $permissions = ['email'];

        $url = $helper->getLoginUrl($this->callback_url, $permissions);

        return esc_url($url);
    }

    /**
     * API call back running whenever we hit /wp-admin/admin-ajax.php?action=jobsearch_facebook
     * This code handles the Login / Regsitration part
     */
    public function apiCallback() {
        global $jobsearch_plugin_options;

        if (isset($_COOKIE['facebook_redirect_url']) && $_COOKIE['facebook_redirect_url'] != '') {
            $real_redirect_url = $_COOKIE['facebook_redirect_url'];
            unset($_COOKIE['facebook_redirect_url']);
            setcookie('facebook_redirect_url', null, -1, '/');
        } else {
            $real_redirect_url = home_url('/');
        }

        $user_dashboard_page = isset($jobsearch_plugin_options['user-dashboard-template-page']) ? $jobsearch_plugin_options['user-dashboard-template-page'] : '';
        $user_dashboard_page = jobsearch__get_post_id($user_dashboard_page, 'page');
        
        $jobsearch_login_page = isset($jobsearch_plugin_options['user-login-template-page']) ? $jobsearch_plugin_options['user-login-template-page'] : '';
        $jobsearch_login_page = jobsearch__get_post_id($jobsearch_login_page, 'page');

        if ($jobsearch_login_page > 0 && $real_redirect_url == get_permalink($jobsearch_login_page)) {
            $dashboard_page_url = $user_dashboard_page > 0 ? get_permalink($user_dashboard_page) : home_url('/');
            $real_redirect_url = $dashboard_page_url;
        }

        $this->redirect_url = $real_redirect_url;

        // We start the connection
        $fb = $this->initApi();

        // We save the token in our instance
        $this->access_token = $this->getToken($fb);

        // We get the user details
        $this->facebook_details = $this->getUserDetails($fb);

        // We first try to login the user
        $this->loginUser();

        // Otherwise, we create a new account
        $this->createUser();

        // Redirect the user
        header("Location: " . $this->redirect_url, true);
        die();
    }

    /**
     * Get a TOKEN from the Facebook API
     * Or redirect back if there is an error
     *
     * @param $fb Facebook
     * @return string - The Token
     */
    private function getToken($fb) {

        // Assign the Session variable for Facebook
        $_SESSION['FBRLH_state'] = $_GET['state'];

        // Load the Facebook SDK helper
        $helper = $fb->getRedirectLoginHelper();

        // Try to get an access token
        try {
            $accessToken = $helper->getAccessToken(admin_url('admin-ajax.php?action=jobsearch_facebook'));
        }
        // When Graph returns an error
        catch (FacebookResponseException $e) {
            $error = __('Graph returned an error: ', 'wp-jobsearch') . $e->getMessage();
            $message = array(
                'type' => 'error',
                'content' => $error
            );
        }
        // When validation fails or other local issues
        catch (FacebookSDKException $e) {
            $error = __('Facebook SDK returned an error: ', 'wp-jobsearch') . $e->getMessage();
            $message = array(
                'type' => 'error',
                'content' => $error
            );
        }

        // If we don't got a token, it means we had an error
        if (!isset($accessToken)) {
            // Report our errors

            set_transient('jobsearch_facebook_message', $message, 60 * 60 * 24 * 30);

            // Redirect
            header("Location: " . $this->redirect_url, true);
            die();
        }

        return $accessToken->getValue();
    }

    /**
     * Get user details through the Facebook API
     *
     * @link https://developers.facebook.com/docs/facebook-login/permissions#reference-public_profile
     * @param $fb Facebook
     * @return \Facebook\GraphNodes\GraphUser
     */
    private function getUserDetails($fb) {

        try {
            $response = $fb->get('/me?fields=id,name,first_name,last_name,email,link', $this->access_token);
        } catch (FacebookResponseException $e) {
            $message = __('Graph returned an error: ', 'wp-jobsearch') . $e->getMessage();
            $message = array(
                'type' => 'error',
                'content' => $error
            );
        } catch (FacebookSDKException $e) {
            $message = __('Facebook SDK returned an error: ', 'wp-jobsearch') . $e->getMessage();
            $message = array(
                'type' => 'error',
                'content' => $error
            );
        }

        // If we caught an error
        if (isset($message)) {
            // Report our errors
            set_transient('jobsearch_facebook_message', $message, 60 * 60 * 24 * 30);
            
            // Redirect
            header("Location: " . $this->redirect_url, true);
            die();
        }

        return $response->getGraphUser();
    }

    /**
     * Login an user to WordPress
     *
     * @link https://codex.wordpress.org/Function_Reference/get_users
     * @return bool|void
     */
    private function loginUser() {

        // We look for the `eo_facebook_id` to see if there is any match
        $wp_users = get_users(array(
            'meta_key' => 'jobsearch_facebook_id',
            'meta_value' => $this->facebook_details['id'],
            'number' => 1,
            'count_total' => false,
            'fields' => 'id',
        ));

        if (empty($wp_users[0])) {
            return false;
        }

        // Log the user ?
        wp_set_auth_cookie($wp_users[0]);
        
        // apply job
        do_action('jobsearch_do_apply_job_fb', $wp_users[0]);
        
        header("Location: " . $this->redirect_url, true);
        exit();
    }

    /**
     * Create a new WordPress account using Facebook Details
     */
    private function createUser() {


        $fb_user = $this->facebook_details;

        // Create an username
        $username = sanitize_user(str_replace(' ', '_', strtolower($this->facebook_details['name'])));
        
        $_social_user_obj = get_user_by('email', $fb_user['email']);
        if (is_object($_social_user_obj) && isset($_social_user_obj->ID)) {
            update_user_meta($_social_user_obj->ID, 'jobsearch_facebook_id', $fb_user['id']);
            $this->loginUser();
        }

        if (username_exists($username)) {
            $username .= '_' . rand(10000, 99999);
        }

        // Creating our user
        $new_user = wp_create_user($username, wp_generate_password(), $fb_user['email']);

        if (is_wp_error($new_user)) {
            // Report our errors
            set_transient('jobsearch_facebook_message', $new_user->get_error_message(), 60 * 60 * 24 * 30);
            echo $new_user->get_error_message();
            die;
        } else {

            // user role
            $user_role = 'jobsearch_candidate';
            wp_update_user(array('ID' => $new_user, 'role' => $user_role));

            // apply job
            do_action('jobsearch_do_apply_job_fb', $new_user);
        
            // Setting the meta
            update_user_meta($new_user, 'first_name', $fb_user['first_name']);
            update_user_meta($new_user, 'last_name', $fb_user['last_name']);
            update_user_meta($new_user, 'user_url', $fb_user['link']);
            update_user_meta($new_user, 'jobsearch_facebook_id', $fb_user['id']);

            // Log the user ?
            wp_set_auth_cookie($new_user);
        }
    }

    public function do_apply_job_with_facebook($user_id) {

        if (isset($_COOKIE['jobsearch_apply_fb_jobid']) && $_COOKIE['jobsearch_apply_fb_jobid'] > 0) {
            $job_id = $_COOKIE['jobsearch_apply_fb_jobid'];

            //
            $user_is_candidate = jobsearch_user_is_candidate($user_id);

            if ($user_is_candidate) {
                $candidate_id = jobsearch_get_user_candidate_id($user_id);
                
                jobsearch_create_user_meta_list($job_id, 'jobsearch-user-jobs-applied-list', $user_id);
                $job_applicants_list = get_post_meta($job_id, 'jobsearch_job_applicants_list', true);
                if ($job_applicants_list != '') {
                    $job_applicants_list = explode(',', $job_applicants_list);
                    if (!in_array($candidate_id, $job_applicants_list)) {
                        $job_applicants_list[] = $candidate_id;
                    }
                    $job_applicants_list = implode(',', $job_applicants_list);
                } else {
                    $job_applicants_list = $candidate_id;
                }
                update_post_meta($job_id, 'jobsearch_job_applicants_list', $job_applicants_list);
                $c_user = get_user_by('ID', $user_id);
                do_action('jobsearch_job_applied_to_employer', $c_user, $job_id);
            }
            
            unset($_COOKIE['jobsearch_apply_fb_jobid']);
            setcookie('jobsearch_apply_fb_jobid', null, -1, '/');
        }
    }

    public function applying_job_with_facebook() {
        $job_id = isset($_POST['job_id']) ? $_POST['job_id'] : '';
        if ($job_id > 0 && get_post_type($job_id) == 'job') {
            $real_redirect_url = get_permalink($job_id);
            setcookie('jobsearch_apply_fb_jobid', $job_id, time() + (180), "/");
            setcookie('facebook_redirect_url', $real_redirect_url, time() + (360), "/");
            echo json_encode(array('redirect_url' => $this->getLoginUrl()));
            die;
        } else {
            echo json_encode(array('msg' => esc_html__('There is some problem.', 'wp-jobsearch')));
            die;
        }
    }

    public function apply_job_with_fb($args = array()) {
        global $jobsearch_plugin_options;
        $facebook_login = isset($jobsearch_plugin_options['facebook-social-login']) ? $jobsearch_plugin_options['facebook-social-login'] : '';
        if ($this->app_id != '' && $this->app_secret != '' && $facebook_login == 'on') {
            $job_id = isset($args['job_id']) ? $args['job_id'] : '';
            ?>
            <li><a href="javascript:void(0);" class="jobsearch-applyjob-fb-btn" data-id="<?php echo ($job_id) ?>"><i class="jobsearch-icon jobsearch-facebook-logo-1"></i> <?php esc_html_e('Facebook', 'wp-jobsearch') ?></a></li>
            <?php
        }
    }

}

/*
 * Starts our plugins, easy!
 */
new JobsearchFacebook();
