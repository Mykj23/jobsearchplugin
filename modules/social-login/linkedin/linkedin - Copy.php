<?php
require_once('linkedin_oauth2.class.php');

/**
 * Class JobsearchLinkedin
 */
class JobsearchLinkedin {

    //
    private $redirect_url = '';
    private $linkedin_details;

    public function __construct() {
        global $jobsearch_plugin_options;
        $user_login_page_id = isset($jobsearch_plugin_options['user-login-template-page']) ? $jobsearch_plugin_options['user-login-template-page'] : '';

        $user_login_page_url = $user_login_page_id > 0 ? get_permalink($user_login_page_id) : home_url('/');

        $real_redirect_url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        if ($real_redirect_url == admin_url('admin-ajax.php')) {
            $real_redirect_url = $user_login_page_url;
        }
        setcookie('linkedin_redirect_url', $real_redirect_url, time() + (360), "/");

        $this->redirect_url = home_url('/');

        // We register our shortcode
        add_shortcode('jobsearch_linkedin_login', array($this, 'renderShortcode'));

        if (isset($_GET['code']) && isset($_GET['state']) && !isset($_GET['srtype']) && !isset($_GET['action']) && !isset($_GET['redirect_from'])) {

            header("Location: " . admin_url('admin-ajax.php?action=jobsearch_linkedin&linkedin=yes&ltype=initiate&srtype=second&code=' . $_GET['code'] . '&state=' . $_GET['state'] . ''), true);
            exit();
        }
        //
        add_action('wp_ajax_jobsearch_linkedin', array($this, 'linkedin_callback'));
        add_action('wp_ajax_nopriv_jobsearch_linkedin', array($this, 'linkedin_callback'));
        
        //
        add_action('jobsearch_apply_job_with_linkedin', array($this, 'apply_job_with_linkedin'), 10, 1);

        add_action('wp_ajax_jobsearch_applying_job_with_linkedin', array($this, 'applying_job_with_linkedin'));
        add_action('wp_ajax_nopriv_jobsearch_applying_job_with_linkedin', array($this, 'applying_job_with_linkedin'));

        //
        add_action('jobsearch_do_apply_job_linkedin', array($this, 'do_apply_job_with_linkedin'), 10, 1);
    }

    public function renderShortcode() {

        echo '<li><a class="jobsearch-linkedin-bg" data-original-title="linkedin" href="' . admin_url('admin-ajax.php?action=jobsearch_linkedin&linkedin=yes&ltype=initiate') . '"><i class="fa fa-linkedin"></i>' . __('Login with Linkedin', 'wp-jobsearch') . '</a></li>';
    }

    public function linkedin_callback() {

        global $jobsearch_plugin_options;

        $_REQUEST['state'] = (isset($_REQUEST['state'])) ? $_REQUEST['state'] : '';
        $state = base64_decode($_REQUEST['state']);
        $state = json_decode($state);

        if ((isset($_REQUEST['linkedin']) && $_REQUEST['linkedin'] == 'yes') || (isset($_SESSION['linkedin']) && $_SESSION['linkedin'] == 'yes') || (isset($state->linkedin) && $state->linkedin == 'yes')) {

            global $jobsearch_plugin_options, $wpdb;

            if (isset($_REQUEST['linkedin'])) {
                $_SESSION['linkedin'] = $_REQUEST['linkedin'];
            } else {
                unset($_SESSION['linkedin']);
            }

            $linkedin_app_id = isset($jobsearch_plugin_options['jobsearch_linkedin_app_id']) ? $jobsearch_plugin_options['jobsearch_linkedin_app_id'] : '';

            $linkedin_secret = isset($jobsearch_plugin_options['jobsearch_linkedin_secret']) ? $jobsearch_plugin_options['jobsearch_linkedin_secret'] : '';

            try {
                if (!isset($_SESSION)) {
                    throw new LinkedInException(esc_html__('This script requires session support, which appears to be disabled according to session_start().', 'wp-jobsearch'));
                }

                // display constants
                $API_CONFIG = array(
                    'appKey' => $linkedin_app_id,
                    'appSecret' => $linkedin_secret,
                );

                define('PORT_HTTP', '80');
                define('PORT_HTTP_SSL', '443');

                // set index
                $_REQUEST['ltype'] = (isset($_REQUEST['ltype'])) ? $_REQUEST['ltype'] : '';
                $_REQUEST['ltype'] = (isset($state->ltype)) ? $state->ltype : $_REQUEST['ltype'];

                switch ($_REQUEST['ltype']) {
                    case 'initiate':
                        /**
                         * Handle user initiated LinkedIn connection, create the LinkedIn object.
                         */
                        // check for the correct http protocol (i.e. is this script being served via http or https)
                        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
                            $protocol = 'https';
                        } else {
                            $protocol = 'http';
                        }
                        // set the callback url
                        $API_CONFIG['callbackUrl'] = $this->redirect_url;

                        $OBJ_linkedin = new LinkedIn($linkedin_app_id, $linkedin_secret, $API_CONFIG['callbackUrl'], 'r_basicprofile,r_emailaddress');

                        // check for response from LinkedIn

                        $_GET['lResponse'] = (isset($_GET['lResponse'])) ? $_GET['lResponse'] : '0';
                        $_GET['lResponse'] = (isset($state->lResponse)) ? $state->lResponse : $_GET['lResponse'];
                        $_REQUEST['ltype'] = (isset($_REQUEST['ltype'])) ? $_REQUEST['ltype'] : '';
                        $_REQUEST['linkedin'] = (isset($_REQUEST['linkedin'])) ? $_REQUEST['linkedin'] : '';

                        if (!$_GET['lResponse']) {
                            $stateString = json_encode(
                                    array(
                                        "ltype" => $_REQUEST["ltype"],
                                        "lResponse" => 1,
                                        "linkedin" => $_REQUEST["linkedin"],
                                        "state" => 'qsmjnlkeulolpo4556kjd',
                                    )
                            );
                            $OBJ_linkedin->addState(base64_encode($stateString));
                            // LinkedIn hasn't sent us a response, the user is initiating the connection
                            // send a request for a LinkedIn access token
                            $OBJ_linkedin->resetToken();
                            if (false === $OBJ_linkedin->authorize()) {
                                // bad token request
                                ?>
                                <script>
                                    alert("<?php esc_html_e('Request token retrieval failed. Please check your settings and then try again.!', 'wp-jobsearch'); ?>");
                                    window.opener.location.reload();
                                    window.close();
                                </script>
                                <?php
                            }
                        } else {

                            // LinkedIn has sent a response, user has granted permission, take the temp access token, the user's secret and the verifier to request the user's real secret key
                            $_REQUEST['state'] = (isset($_REQUEST['state'])) ? $_REQUEST['state'] : '';
                            $state = base64_decode($_REQUEST['state']);
                            $state = json_decode($state);

                            $OBJ_linkedin->authorize();

                            $response = $OBJ_linkedin->fetch('GET', '/v1/people/~:(id,first-name,last-name,picture-url,email-address,phone-numbers,headline)');
                            $OBJ_linkedin->resetToken();

                            if (isset($response->id) && $response->id !== '') {
                                // the request went through without an error, gather user's 'access' tokens
                                $_SESSION['oauth']['linkedin']['access'] = $response;
                                // set the user as authorized for future quick reference
                                $_SESSION['oauth']['linkedin']['authorized'] = TRUE;
                                $_SESSION['oauth']['linkedin']['authorized'] = (isset($_SESSION['oauth']['linkedin']['authorized'])) ? $_SESSION['oauth']['linkedin']['authorized'] : FALSE;

                                if (isset($response->id) && $response->id !== '') {

                                    $linkedin_id = (string) $response->id;
                                    if (isset($response->firstName) && '' !== $response->firstName) {
                                        $linkedin_firstname = (string) $response->firstName;
                                    }
                                    if (isset($response->lastName) && '' !== $response->lastName) {
                                        $linkedin_lastname = (string) $response->lastName;
                                    }
                                    if (isset($response->pictureUrl) && '' !== $response->pictureUrl) {
                                        $linkedin_picture_url = (string) $response->pictureUrl;
                                    }
                                    if (isset($response->emailAddress) && '' !== $response->emailAddress) {
                                        $linkedin_email = (string) $response->emailAddress;
                                    }
                                    if (isset($response->phoneNumbers) && '' !== $response->phoneNumbers) {
                                        $linkedin_phone = (string) $response->phoneNumbers;
                                    }

                                    #############################################
                                    #       Login / register as guest user      #
                                    #############################################
                                    $email = filter_var($linkedin_email, FILTER_SANITIZE_EMAIL);
                                    if (!is_user_logged_in()) {
                                        $this->linkedin_details = $response;

                                        // We first try to login the user
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
                                        $jobsearch_login_page = isset($jobsearch_plugin_options['user-login-template-page']) ? $jobsearch_plugin_options['user-login-template-page'] : '';

                                        if ($jobsearch_login_page > 0 && $real_redirect_url == get_permalink($jobsearch_login_page)) {
                                            $dashboard_page_url = $user_dashboard_page > 0 ? get_permalink($user_dashboard_page) : home_url('/');
                                            $real_redirect_url = $dashboard_page_url;
                                        }
                                        $this->redirect_url = $real_redirect_url;
                                        //

                                        header("Location: " . $this->redirect_url, true);
                                        die();
                                    } else {
                                        $user_info = wp_get_current_user();
                                        set_site_transient($user_info->ID . '_jobsearch_linkedin_admin_notice', esc_html__('This Linked-in profile is already linked with other account. Linking process failed!', 'wp-jobsearch'), 3600);
                                    }
                                }
                            } else {
                                // bad token access
                                //
                                if (isset($_COOKIE['linkedin_redirect_url']) && $_COOKIE['linkedin_redirect_url'] != '') {
                                    $real_redirect_url = $_COOKIE['linkedin_redirect_url'];
                                    unset($_COOKIE['linkedin_redirect_url']);
                                    setcookie('linkedin_redirect_url', null, -1, '/');
                                } else {
                                    $real_redirect_url = home_url('/');
                                }

                                $user_dashboard_page = isset($jobsearch_plugin_options['user-dashboard-template-page']) ? $jobsearch_plugin_options['user-dashboard-template-page'] : '';
                                $jobsearch_login_page = isset($jobsearch_plugin_options['user-login-template-page']) ? $jobsearch_plugin_options['user-login-template-page'] : '';

                                if ($jobsearch_login_page > 0 && $real_redirect_url == get_permalink($jobsearch_login_page)) {
                                    $dashboard_page_url = $user_dashboard_page > 0 ? get_permalink($user_dashboard_page) : home_url('/');
                                    $real_redirect_url = $dashboard_page_url;
                                }
                                $this->redirect_url = $real_redirect_url;
                                //

                                header("Location: " . $this->redirect_url, true);
                                die();
                            }
                        }//exit;
                        break;

                    default:
                        // nothing being passed back, display demo page
                        // check PHP version
                        if (version_compare(PHP_VERSION, '5.0.0', '<')) {
                            throw new LinkedInException(esc_html__('You must be running version 5.x or greater of PHP to use this library.', 'wp-jobsearch'));
                        }

                        // check for cURL
                        if (extension_loaded('curl')) {
                            $curl_version = curl_version();
                            $curl_version = $curl_version['version'];
                        } else {
                            throw new LinkedInException(esc_html__('You must load the cURL extension to use this library.', 'wp-jobsearch'));
                        }
                        break;
                }
            } catch (LinkedInException $e) {
                // exception raised by library call
                echo $e->getMessage();
            }
        }
        die;
    }

    private function loginUser() {
        global $jobsearch_plugin_options;
        // We look for the `eo_linkedin_id` to see if there is any match
        $wp_users = get_users(array(
            'meta_key' => 'jobsearch_linkedin_id',
            'meta_value' => $this->linkedin_details->id,
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
        $jobsearch_login_page = isset($jobsearch_plugin_options['user-login-template-page']) ? $jobsearch_plugin_options['user-login-template-page'] : '';

        if ($jobsearch_login_page > 0 && $real_redirect_url == get_permalink($jobsearch_login_page)) {
            $dashboard_page_url = $user_dashboard_page > 0 ? get_permalink($user_dashboard_page) : home_url('/');
            $real_redirect_url = $dashboard_page_url;
        }
        $this->redirect_url = $real_redirect_url;
        //
        // Log the user ?
        wp_set_auth_cookie($wp_users[0]);
        
        // apply job
        do_action('jobsearch_do_apply_job_linkedin', $wp_users[0]);
        
        header("Location: " . $this->redirect_url, true);
        exit();
    }

    /**
     * Create a new WordPress account using Google Details
     */
    private function createUser() {


        $linkedin_user = $this->linkedin_details;

        $user_id = isset($linkedin_user->id) ? $linkedin_user->id : '';

        $first_name = isset($linkedin_user->firstName) ? $linkedin_user->firstName : '';
        $last_name = isset($linkedin_user->lastName) ? $linkedin_user->lastName : '';

        $name = isset($linkedin_user->name) ? $linkedin_user->name : '';
        $email = isset($linkedin_user->emailAddress) ? $linkedin_user->emailAddress : '';

        // Create an username
        if ($name == '') {
            $name = $first_name . '_' . $last_name;
            $name = str_replace(array(' '), array('_'), $name);
        }
        $username = sanitize_user(str_replace(' ', '_', strtolower($name)));
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
            do_action('jobsearch_do_apply_job_linkedin', $new_user);

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
            echo json_encode(array('redirect_url' => admin_url('admin-ajax.php?action=jobsearch_linkedin&linkedin=yes&ltype=initiate')));
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

new JobsearchLinkedin();
