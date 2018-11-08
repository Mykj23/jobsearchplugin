<?php

Class WpJobSearchLogin {

    const _AUTHORIZE_URL = 'https://www.linkedin.com/uas/oauth2/authorization';
    const _TOKEN_URL = 'https://www.linkedin.com/uas/oauth2/accessToken';
    const _BASE_URL = 'https://api.linkedin.com/v1';

    // LinkedIn Application Key
    public $li_api_key;
    // LinkedIn Application Secret
    public $li_secret_key;
    // Stores Access Token
    public $access_token;
    // Stores OAuth Object
    public $oauth;
    // Stores the user redirect after login
    public $user_redirect = false;
    private $redirect_url = '';
    private $linkedin_details;
    // Stores our LinkedIn options 
    public $li_options;

    public function __construct() {

        global $jobsearch_plugin_options;
        $user_login_page_id = isset($jobsearch_plugin_options['user-login-template-page']) ? $jobsearch_plugin_options['user-login-template-page'] : '';
        $user_login_page_id = jobsearch__get_post_id($user_login_page_id, 'page');

        $linkedin_app_id = isset($jobsearch_plugin_options['jobsearch_linkedin_app_id']) ? $jobsearch_plugin_options['jobsearch_linkedin_app_id'] : '';

        $linkedin_secret = isset($jobsearch_plugin_options['jobsearch_linkedin_secret']) ? $jobsearch_plugin_options['jobsearch_linkedin_secret'] : '';

        $user_login_page_url = $user_login_page_id > 0 ? get_permalink($user_login_page_id) : home_url('/');

        $real_redirect_url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        if ($real_redirect_url == admin_url('admin-ajax.php')) {
            $real_redirect_url = $user_login_page_url;
        }
        setcookie('linkedin_redirect_url', $real_redirect_url, time() + (360), "/");

        $this->redirect_url = home_url('/');

        // This action displays the LinkedIn Login button on the default WordPress Login Page
        add_shortcode('jobsearch_linkedin_login', array($this, 'display_login_button'));

        // This action processes any LinkedIn Login requests
        //add_action('init', array($this, 'process_login'));
        // Set LinkedIn keys class variables - These will be used throughout the class
        $this->li_api_key = $linkedin_app_id;
        $this->li_secret_key = $linkedin_secret;

        // Get plugin options
        $this->li_options = array(
            'li_cancel_redirect_url' => '',
            'li_redirect_url' => '',
            'li_auto_profile_update' => '',
            'li_registration_redirect_url' => '',
            'li_logged_in_message' => '',
        );

        // Require OAuth2 client to process authentications
        require_once('linkedin_oauth2.class.php');

        // Create new Oauth client
        $this->oauth = new Wp_JobSearch_OAuth2Client($this->li_api_key, $this->li_secret_key);

        // Set Oauth URLs
        $this->oauth->redirect_uri = home_url('/') . '?action=linkedin_login';
        $this->oauth->authorize_url = self::_AUTHORIZE_URL;
        $this->oauth->token_url = self::_TOKEN_URL;
        $this->oauth->api_base_url = self::_BASE_URL;

        // Set user token if user is logged in
        if (get_current_user_id()) {
            $this->oauth->access_token = get_user_meta(get_current_user_id(), 'jobsearch_access_token', true);
        }

        if (!isset($_GET['jobsearch_instagram_login'])) {
            // Start session
            if (!session_id()) {
                session_start();
            }

            $this->process_login();

            //
            add_action('jobsearch_apply_job_with_linkedin', array($this, 'apply_job_with_linkedin'), 10, 1);

            add_action('wp_ajax_jobsearch_applying_job_with_linkedin', array($this, 'applying_job_with_linkedin'));
            add_action('wp_ajax_nopriv_jobsearch_applying_job_with_linkedin', array($this, 'applying_job_with_linkedin'));

            //
            add_action('jobsearch_do_apply_job_linkedin', array($this, 'do_apply_job_with_linkedin'), 10, 1);
        }
    }

    // Returns LinkedIn authorization URL
    public function get_auth_url($redirect = false) {

        $state = wp_generate_password(12, false);
        $authorize_url = $this->oauth->authorizeUrl(array('scope' => 'r_basicprofile r_emailaddress',
            'state' => $state));

        // Store state in database in temporarily till checked back
        if (!isset($_SESSION['li_api_state'])) {
            $_SESSION['li_api_state'] = $state;
        }

        // Store redirect URL in session
        $_SESSION['li_api_redirect'] = $redirect;

        return $authorize_url;
    }

    // This function displays the login button on the default WP login page
    public function display_login_button() {

        // User is not logged in, display login button
        echo '<li><a class="jobsearch-linkedin-bg" href="' . $this->get_auth_url() . '" data-original-title="linkedin"><i class="fa fa-linkedin"></i>' . __('Login with Linkedin', 'wp-jobsearch') . '</a></li>';
    }

    // Logs in a user after he has authorized his LinkedIn account
    function process_login() {

        // If this is not a linkedin sign-in request, do nothing
        if (!$this->is_linkedin_signin()) {
            return;
        }

        // If this is a user sign-in request, but the user denied granting access, redirect to login URL
        if (isset($_REQUEST['error']) && $_REQUEST['error'] == 'access_denied') {

            // Get our cancel redirect URL
            $cancel_redirect_url = $this->li_options['li_cancel_redirect_url'];

            // Redirect to login URL if left blank
            if (empty($cancel_redirect_url)) {
                wp_redirect(home_url('/'));
            }

            // Redirect to our given URL
            wp_safe_redirect($cancel_redirect_url);
        }

        // Another error occurred, create an error log entry
        if (isset($_REQUEST['error'])) {
            $error = $_REQUEST['error'];
            $error_description = $_REQUEST['error_description'];
            error_log("WP_LinkedIn Login Error\nError: $error\nDescription: $error_description");
        }


        // Get profile XML response
        $xml = $this->get_linkedin_profile();

        //wp_logout();

        $xml = json_decode(json_encode((array)$xml), true);
        //var_dump($xml); die;
        
        if (!is_array($xml) || !isset($xml['id'])) {
            return false;
        }

        $this->linkedin_details = $xml;

        // Get the user's email address
        $email = isset($xml['email-address']) ? $xml['email-address'] : '';

        // Get the user's application-specific LinkedIn ID
        $linkedin_id = isset($xml['id']) ? $xml['id'] : '';

        // See if a user with the above LinkedIn ID exists in our database
        $user_by_id = get_users(array('meta_key' => 'jobsearch_linkedin_id',
            'meta_value' => $linkedin_id));

        $this->loginUser();
        // Otherwise, we create a new account
        $this->createUser();
        //
        if (isset($_COOKIE['linkedin_redirect_url']) && $_COOKIE['linkedin_redirect_url'] != '') {
            $real_redirect_url = $_COOKIE['linkedin_redirect_url'];
            unset($_COOKIE['linkedin_redirect_url']);
            setcookie('linkedin_redirect_url', null, -1, '/');
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
        //

        header("Location: " . $this->redirect_url, true);
        die();
    }

    /*
     * Get the user LinkedIN profile and return it as XML
     */

    private function get_linkedin_profile() {

        // Use GET method since POST isn't working
        $this->oauth->curl_authenticate_method = 'GET';

        // Request access token
        $response = $this->oauth->authenticate($_REQUEST['code']);
        $this->access_token = $response->{'access_token'};

        // Get first name, last name and email address, and load 
        // response into XML object
        $xml = simplexml_load_string($this->oauth->get('https://api.linkedin.com/v1/people/~:(id,first-name,last-name,email-address,headline,specialties,positions:(id,title,summary,start-date,end-date,is-current,company),summary,site-standard-profile-request,picture-url,location:(name,country:(code)),industry)'));

        return $xml;
    }

    /*
     * Checks if this is a LinkedIn sign-in request for our plugin
     */

    private function is_linkedin_signin() {

        // If no action is requested or the action is not ours
        if (!isset($_REQUEST['action']) || ($_REQUEST['action'] != "linkedin_login")) {
            return false;
        }

        // If a code is not returned, and no error as well, then OAuth did not proceed properly
        if (!isset($_REQUEST['code']) && !isset($_REQUEST['error'])) {
            return false;
        }
        /*
         * Temporarily disabled this because we're getting two different states at random times

          // If state is not set, or it is different than what we expect there might be a request forgery
          if ( ! isset($_SESSION['li_api_state'] ) || $_REQUEST['state'] != $_SESSION['li_api_state']) {
          return false;
          }
         */

        // This is a LinkedIn signing-request - unset state and return true
        unset($_SESSION['li_api_state']);

        return true;
    }

    private function loginUser() {
        global $jobsearch_plugin_options;
        $linkedin_user = $this->linkedin_details;
        $user_id = isset($linkedin_user['id']) ? $linkedin_user['id'] : '';

        // We look for the `eo_linkedin_id` to see if there is any match
        $wp_users = get_users(array(
            'meta_key' => 'jobsearch_linkedin_id',
            'meta_value' => $user_id,
            'number' => 1,
            'count_total' => false,
            'fields' => 'id',
        ));

        if (empty($wp_users[0])) {
            return false;
        }

        //
        if (isset($_COOKIE['linkedin_redirect_url']) && $_COOKIE['linkedin_redirect_url'] != '') {
            $real_redirect_url = $_COOKIE['linkedin_redirect_url'];
            unset($_COOKIE['linkedin_redirect_url']);
            setcookie('linkedin_redirect_url', null, -1, '/');
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
        //
        // Log the user ?
        wp_set_auth_cookie($wp_users[0]);

        // apply job
        //do_action('jobsearch_do_apply_job_linkedin', $wp_users[0]);
        $this->do_apply_job_with_linkedin($wp_users[0]);

        header("Location: " . $this->redirect_url, true);
        exit();
    }

    /**
     * Create a new WordPress account using Google Details
     */
    private function createUser() {

        $linkedin_user = $this->linkedin_details;
        //$linkedin_user = json_decode(json_encode((array)$linkedin_user), true);
        
        $user_id = isset($linkedin_user['id']) ? $linkedin_user['id'] : '';
        
        $first_name = isset($linkedin_user['first-name']) ? $linkedin_user['first-name'] : '';
        $last_name = isset($linkedin_user['last-name']) ? $linkedin_user['last-name'] : '';

        $email = isset($linkedin_user['email-address']) ? $linkedin_user['email-address'] : '';
        
        $_social_user_obj = get_user_by('email', $email);
        if (is_object($_social_user_obj) && isset($_social_user_obj->ID)) {
            update_user_meta($_social_user_obj->ID, 'jobsearch_linkedin_id', $user_id);
            $this->loginUser();
        }

        if ($first_name != '' && $last_name != '') {
            $name = $first_name . '_' . $last_name;
            $name = str_replace(array(' '), array('_'), $name);
            $username = sanitize_user(str_replace(' ', '_', strtolower($name)));
        } else {
            $username = $email;
        }

        if (username_exists($username)) {
            $username .= '_' . rand(10000, 99999);
        }

        // Creating our user
        $new_user = wp_create_user($username, wp_generate_password(), $email);

        if (is_wp_error($new_user)) {
            // Report our errors
            set_transient('jobsearch_linkedin_message', $new_user->get_error_message(), 60 * 60 * 24 * 30);
            echo $new_user->get_error_message();
            die;
        } else {

            // user role
            $user_role = 'jobsearch_candidate';
            wp_update_user(array('ID' => $new_user, 'role' => $user_role));

            // apply job
            //do_action('jobsearch_do_apply_job_linkedin', $new_user);
            $this->do_apply_job_with_linkedin($new_user);

            // Setting the meta
            update_user_meta($new_user, 'first_name', $first_name);
            update_user_meta($new_user, 'last_name', $last_name);
            update_user_meta($new_user, 'jobsearch_linkedin_id', $user_id);
            // Log the user ?
            wp_set_auth_cookie($new_user);
        }
    }

    public function do_apply_job_with_linkedin($user_id) {

        if (isset($_COOKIE['jobsearch_apply_linkedin_jobid']) && $_COOKIE['jobsearch_apply_linkedin_jobid'] > 0) {
            $job_id = $_COOKIE['jobsearch_apply_linkedin_jobid'];

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

            unset($_COOKIE['jobsearch_apply_linkedin_jobid']);
            setcookie('jobsearch_apply_linkedin_jobid', null, -1, '/');
        }
    }

    public function applying_job_with_linkedin() {
        $job_id = isset($_POST['job_id']) ? $_POST['job_id'] : '';
        if ($job_id > 0 && get_post_type($job_id) == 'job') {
            $real_redirect_url = get_permalink($job_id);
            setcookie('jobsearch_apply_linkedin_jobid', $job_id, time() + (180), "/");
            setcookie('linkedin_redirect_url', $real_redirect_url, time() + (360), "/");
            echo json_encode(array('redirect_url' => $this->get_auth_url()));
            die;
        } else {
            echo json_encode(array('msg' => esc_html__('There is some problem.', 'wp-jobsearch')));
            die;
        }
    }

    public function apply_job_with_linkedin($args = array()) {
        global $jobsearch_plugin_options;
        $linkedin_login = isset($jobsearch_plugin_options['linkedin-social-login']) ? $jobsearch_plugin_options['linkedin-social-login'] : '';
        if ($linkedin_login == 'on') {
            $job_id = isset($args['job_id']) ? $args['job_id'] : '';
            ?>
            <li><a href="javascript:void(0);" class="jobsearch-applyjob-linkedin-btn" data-id="<?php echo ($job_id) ?>"><i class="jobsearch-icon jobsearch-linkedin-logo"></i> <?php esc_html_e('Linkedin', 'wp-jobsearch') ?></a></li>
            <?php
        }
    }

}

$wp_jobsearch_login = new WpJobSearchLogin();
