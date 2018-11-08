<?php

/*
 * Import the Google SDK and load all the classes
 */
include (plugin_dir_path(__FILE__) . 'google-sdk/autoload.php');

/**
 * Class JobsearchGoogle
 */
class JobsearchGoogle {

    /**
     * Google APP ID
     *
     * @var string
     */
    private $client_id = '';
    private $client_secret = '';
    private $redirect_url = '';
    private $login_redirect_url = '';
    //
    private $google_details;

    /**
     * JobsearchGoogle constructor.
     */
    public function __construct() {

        global $jobsearch_plugin_options;

        $login_page = isset($jobsearch_plugin_options['user-login-template-page']) ? $jobsearch_plugin_options['user-login-template-page'] : '';
        $login_page = jobsearch__get_post_id($login_page, 'page');

        $client_id = isset($jobsearch_plugin_options['jobsearch-google-client-id']) ? $jobsearch_plugin_options['jobsearch-google-client-id'] : '';
        $client_secret = isset($jobsearch_plugin_options['jobsearch-google-client-secret']) ? $jobsearch_plugin_options['jobsearch-google-client-secret'] : '';

        $this->redirect_url = home_url('/');

        if (get_post_type($login_page) == 'page') {
            $this->login_redirect_url = get_permalink($login_page);
        }
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;

        $this->set_access_tokes();

        // We register our shortcode
        add_shortcode('jobsearch_google_login', array($this, 'renderShortcode'));

        if (!isset($_GET['jobsearch_instagram_login'])) {
            //
            add_action('wp_ajax_jobsearch_google_login', array($this, 'google_callback'));
            add_action('wp_ajax_nopriv_jobsearch_google_login', array($this, 'google_callback'));

            //
            add_action('jobsearch_apply_job_with_google', array($this, 'apply_job_with_google'), 10, 1);

            add_action('wp_ajax_jobsearch_applying_job_with_google', array($this, 'applying_job_with_google'));
            add_action('wp_ajax_nopriv_jobsearch_applying_job_with_google', array($this, 'applying_job_with_google'));
        }
    }

    private function set_access_tokes() {
        $client = new Google_Client();
        $client->setClientId($this->client_id);
        $client->setClientSecret($this->client_secret);
        $client->setRedirectUri($this->redirect_url);
        $client->addScope("email");
        $client->addScope("profile");

        /*         * **********************************************
          If we have a code back from the OAuth 2.0 flow,
          we need to exchange that with the authenticate()
          function. We store the resultant access token
          bundle in the session, and redirect to ourself.
         */

        if (isset($_GET['code']) && !isset($_GET['state']) && !isset($_GET['redirect_from']) && !isset($_GET['jobsearch_instagram_login'])) {

            $client->authenticate($_GET['code']);
            set_transient('access_token', $client->getAccessToken(), 60 * 60 * 24 * 30);
            header("Location: " . admin_url('admin-ajax.php?action=jobsearch_google_login'), true);
            exit();
        }
    }

    public function google_callback() {
        
        global $jobsearch_plugin_options;
        
        if (isset($_GET['logout'])) {
            delete_transient('access_token');
        }

        $client = new Google_Client();
        $client->setClientId($this->client_id);
        $client->setClientSecret($this->client_secret);
        $client->setRedirectUri($this->redirect_url);
        $client->addScope("email");
        $client->addScope("profile");

        /*         * **********************************************
          When we create the service here, we pass the
          client to it. The client then queries the service
          for the required scopes, and uses that when
          generating the authentication URL later.
         * ********************************************** */
        $service = new Google_Service_Oauth2($client);

        if (get_transient('access_token')) {
            $client->setAccessToken(get_transient('access_token'));
            $this->google_details = $service->userinfo->get();

            // We first try to login the user
            $this->loginUser();

            // Otherwise, we create a new account
            $this->createUser();
        }
        
        if (isset($_COOKIE['google_redirect_url']) && $_COOKIE['google_redirect_url'] != '') {
            $real_redirect_url = $_COOKIE['google_redirect_url'];
            unset($_COOKIE['google_redirect_url']);
            setcookie('google_redirect_url', null, -1, '/');
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
        
        header("Location: " . $this->redirect_url, true);
        die();
    }

    /**
     * Render the shortcode [jobsearch_google/]
     *
     * It displays our Login / Register button
     */
    public function renderShortcode() {

        if (isset($_GET['logout'])) {
            delete_transient('access_token');
        }

        $client = new Google_Client();
        $client->setClientId($this->client_id);
        $client->setClientSecret($this->client_secret);
        $client->setRedirectUri($this->redirect_url);
        $client->addScope("email");
        $client->addScope("profile");
        $client->setApprovalPrompt("force");

        $service = new Google_Service_Oauth2($client);

        /*********************************************
          If we have an access token, we can make
          requests, else we generate an authentication URL.
         *********************************************/

        $authUrl = $client->createAuthUrl();


        if (isset($authUrl)) {
            echo '<li><a class="jobsearch-google-plus-bg" data-original-title="google" href="' . $authUrl . '"><i class="fa fa-google-plus"></i>' . __('Login with Google', 'wp-jobsearch') . '</a></li>';
        }
    }

    private function loginUser() {

        // We look for the `eo_google_id` to see if there is any match
        $wp_users = get_users(array(
            'meta_key' => 'jobsearch_google_id',
            'meta_value' => $this->google_details->id,
            'number' => 1,
            'count_total' => false,
            'fields' => 'id',
        ));

        if (empty($wp_users[0])) {
            return false;
        }

        // Log the user ?
        wp_set_auth_cookie($wp_users[0]);
        
        $this->do_apply_job_with_google($wp_users[0]);
        
        if (isset($_COOKIE['google_redirect_url']) && $_COOKIE['google_redirect_url'] != '') {
            $real_redirect_url = $_COOKIE['google_redirect_url'];
            unset($_COOKIE['google_redirect_url']);
            setcookie('google_redirect_url', null, -1, '/');
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
        
        header("Location: " . $this->redirect_url, true);
        exit();
    }

    /**
     * Create a new WordPress account using Google Details
     */
    private function createUser() {


        $google_user = $this->google_details;
        
        $user_id = isset($google_user->id) ? $google_user->id : '';
        
        $first_name = isset($google_user->given_name) ? $google_user->given_name : '';
        $last_name = isset($google_user->family_name) ? $google_user->family_name : '';
        
        $name = isset($google_user->name) ? $google_user->name : '';
        $email = isset($google_user->email) ? $google_user->email : '';
        
        $_social_user_obj = get_user_by('email', $email);
        if (is_object($_social_user_obj) && isset($_social_user_obj->ID)) {
            update_user_meta($_social_user_obj->ID, 'jobsearch_google_id', $user_id);
            $this->loginUser();
        }
        
        // Create a username
        $username = sanitize_user(str_replace(' ', '_', strtolower($name)));
        if ($username == '') {
            $username = 'user_' . rand(10000, 99999);
        }
        if (username_exists($username)) {
            $username .= '_' . rand(10000, 99999);
        }
        
        // Creating our user
        $new_user = wp_create_user($username, wp_generate_password(), $email);
        
        if (is_wp_error($new_user)) {
            // Report our errors
            set_transient('jobsearch_google_message', $new_user->get_error_message(), 60 * 60 * 24 * 30);
            echo $new_user->get_error_message();
            die;
        } else {

            // user role
            $user_role = 'jobsearch_candidate';
            wp_update_user( array( 'ID' => $new_user, 'role' => $user_role ) );
            
            $this->do_apply_job_with_google($new_user);
            
            // Setting the meta
            update_user_meta($new_user, 'first_name', $first_name);
            update_user_meta($new_user, 'last_name', $last_name);
            update_user_meta($new_user, 'jobsearch_google_id', $user_id);

            // Log the user ?
            wp_set_auth_cookie($new_user);
        }
    }

    public function do_apply_job_with_google($user_id) {

        if (isset($_COOKIE['jobsearch_apply_google_jobid']) && $_COOKIE['jobsearch_apply_google_jobid'] > 0) {
            $job_id = $_COOKIE['jobsearch_apply_google_jobid'];

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
            }

            unset($_COOKIE['jobsearch_apply_google_jobid']);
            setcookie('jobsearch_apply_google_jobid', null, -1, '/');
        }
    }

    public function applying_job_with_google() {
        $job_id = isset($_POST['job_id']) ? $_POST['job_id'] : '';
        if ($job_id > 0 && get_post_type($job_id) == 'job') {
            
            $real_redirect_url = get_permalink($job_id);
            setcookie('jobsearch_apply_google_jobid', $job_id, time() + (180), "/");
            setcookie('google_redirect_url', $real_redirect_url, time() + (360), "/");
            
            //
            $client = new Google_Client();
            $client->setClientId($this->client_id);
            $client->setClientSecret($this->client_secret);
            $client->setRedirectUri($this->redirect_url);
            $client->addScope("email");
            $client->addScope("profile");
            $client->setApprovalPrompt("force");

            $service = new Google_Service_Oauth2($client);

            /*********************************************
              If we have an access token, we can make
              requests, else we generate an authentication URL.
             *********************************************/

            $authUrl = $client->createAuthUrl();
            //
            
            echo json_encode(array('redirect_url' => $authUrl));
            die;
        } else {
            echo json_encode(array('msg' => esc_html__('There is some problem.', 'wp-jobsearch')));
            die;
        }
    }

    public function apply_job_with_google($args = array()) {
        global $jobsearch_plugin_options;
        $google_login = isset($jobsearch_plugin_options['google-social-login']) ? $jobsearch_plugin_options['google-social-login'] : '';
        if ($google_login == 'on') {
            
            $job_id = isset($args['job_id']) ? $args['job_id'] : '';
            ?>
            <li><a href="javascript:void(0);" class="jobsearch-applyjob-google-btn" data-id="<?php echo ($job_id) ?>"><i class="fa fa-google-plus"></i> <?php esc_html_e('Google', 'wp-jobsearch') ?></a></li>
            <?php
        }
    }

}

/*
 * Starts our plugins, easy!
 */
new JobsearchGoogle();
