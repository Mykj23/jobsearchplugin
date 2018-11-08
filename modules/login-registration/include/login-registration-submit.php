<?php
/*
  Class : Login_Registration
 */


// this is an include only WP file
if (!defined('ABSPATH')) {
    die;
}

// main plugin class
class Jobsearch_Login_Registration_Submit {

    // hook things up
    public function __construct() {
        add_action('wp_ajax_nopriv_jobsearch_login_member_submit', array($this, 'jobsearch_login_member_submit_callback'), 1);
        add_action('wp_ajax_nopriv_jobsearch_reset_password', array($this, 'jobsearch_reset_password_callback'), 1);
        add_action('wp_ajax_nopriv_jobsearch_register_member_submit', array($this, 'jobsearch_register_member_submit_callback'), 1);

        add_action('init', array($this, 'reset_password_form'));

        add_action('wp_ajax_jobsearch_demo_user_login', array($this, 'demo_user_login'));
        add_action('wp_ajax_nopriv_jobsearch_demo_user_login', array($this, 'demo_user_login'));

        add_action('wp_ajax_jobsearch_pass_reseting_by_redirect_url', array($this, 'reset_password_from_redirect'));
        add_action('wp_ajax_nopriv_jobsearch_pass_reseting_by_redirect_url', array($this, 'reset_password_from_redirect'));

        add_action('user_register', array($this, 'jobsearch_registration_save'), 10, 1);
        //add_action('wp_login', array($this, 'jobsearch_login_function'), 10, 2);
    }
    
    public function demo_user_login() {
        global $jobsearch_plugin_options;
        $user_type = isset($_POST['user_type']) ? $_POST['user_type'] : '';
        $demo_candidate = isset($jobsearch_plugin_options['demo_candidate']) ? $jobsearch_plugin_options['demo_candidate'] : '';
        $demo_employer = isset($jobsearch_plugin_options['demo_employer']) ? $jobsearch_plugin_options['demo_employer'] : '';

        $user_dashboard_page = isset($jobsearch_plugin_options['user-dashboard-template-page']) ? $jobsearch_plugin_options['user-dashboard-template-page'] : '';
        $user_dashboard_page = jobsearch__get_post_id($user_dashboard_page, 'page');
        $page_url = jobsearch_wpml_lang_page_permalink($user_dashboard_page, 'page'); //get_permalink($user_dashboard_page);

        if ($user_type == 'employer') {
            $_demo_user_obj = get_user_by('login', $demo_employer);
            if (isset($_demo_user_obj->ID)) {
                wp_set_current_user($_demo_user_obj->ID, $_demo_user_obj->user_login);
                wp_set_auth_cookie($_demo_user_obj->ID);
                echo json_encode(array('redirect' => $page_url, 'msg' => ''));
            }
        } else {
            $_demo_user_obj = get_user_by('login', $demo_candidate);
            if (isset($_demo_user_obj->ID)) {
                wp_set_current_user($_demo_user_obj->ID, $_demo_user_obj->user_login);
                wp_set_auth_cookie($_demo_user_obj->ID);
                echo json_encode(array('redirect' => $page_url, 'msg' => ''));
            }
        }
        die;
    }

    public function jobsearch_login_member_submit_callback() {
        global $jobsearch_plugin_options;
        // Get variables
        $user_login = $_POST['pt_user_login'];
        $user_pass = $_POST['pt_user_pass'];
        
        $wredirct_url = isset($_POST['jobsearch_wredirct_url']) ? $_POST['jobsearch_wredirct_url'] : '';
        $extra_params = isset($_POST['extra_login_params']) ? $_POST['extra_login_params'] : '';

        $user_dashboard_page = isset($jobsearch_plugin_options['user-dashboard-template-page']) ? $jobsearch_plugin_options['user-dashboard-template-page'] : '';
        $user_dashboard_page = jobsearch__get_post_id($user_dashboard_page, 'page');
        $page_url = jobsearch_wpml_lang_page_permalink($user_dashboard_page, 'page'); //get_permalink($user_dashboard_page);
        //
        if ($wredirct_url != '') {
            $page_url = $wredirct_url;
        }
        // Check CSRF token
        if (!check_ajax_referer('ajax-login-nonce', 'login-security', false)) {
            echo json_encode(array('error' => true, 'message' => '<div class="alert alert-danger"><i class="fa fa-times"></i> ' . __('Session token has expired, please reload the page and try again', 'wp-jobsearch') . '</div>'));
        }

        // Check if input variables are empty
        else if (empty($user_login) || empty($user_pass)) {
            echo json_encode(array('error' => true, 'message' => '<div class="alert alert-danger"><i class="fa fa-times"></i> ' . __('Please fill all form fields', 'wp-jobsearch') . '</div>'));
        } else { // Now we can insert this account
            $user = wp_signon(array('user_login' => $user_login, 'user_password' => $user_pass), false);

            if (is_wp_error($user)) {
                $errors_html = wp_kses($user->get_error_message(), array('strong' => array(), 'p' => array()));
                echo json_encode(array('error' => true, 'message' => '<div class="alert alert-danger"><i class="fa fa-times"></i> ' . $errors_html . '</div>'));
            } else {
                $login_args = array(
                    'login_user' => $user,
                    'extra_params' => $extra_params,
                    'wredirct_url' => $wredirct_url,
                );
                echo apply_filters('jobsearch_after_logged_in_before_msg', '', $login_args);
                echo json_encode(array('error' => false, 'redirect' => $page_url, 'message' => '<div class="alert alert-success"><i class="fa fa-check"></i> ' . __('Login successful, reloading page...', 'wp-jobsearch') . '</div>'));
            }
        }

        die();
    }

    public function jobsearch_reset_password_callback() {
        // Get variables
        $username_or_email = $_POST['pt_user_or_email'];

        // Check CSRF token
        if (!check_ajax_referer('ajax-login-nonce', 'password-security', false)) {
            echo json_encode(array('error' => true, 'message' => '<div class="alert alert-danger"><i class="fa fa-times"></i> ' . __('Session token has expired, please reload the page and try again', 'wp-jobsearch') . '</div>'));
        }

        // Check if input variables are empty
        elseif (empty($username_or_email)) {
            echo json_encode(array('error' => true, 'message' => '<div class="alert alert-danger"><i class="fa fa-times"></i> ' . __('Please fill all form fields', 'wp-jobsearch') . '</div>'));
        } else {

            $username = is_email($username_or_email) ? sanitize_email($username_or_email) : sanitize_user($username_or_email);

            $user_forgotten = $this->lost_password_retrieve($username);

            if (is_wp_error($user_forgotten)) {

                $lostpass_error_messages = $user_forgotten->errors;

                $display_errors = '<div class="alert alert-warning">';
                foreach ($lostpass_error_messages as $error) {
                    $display_errors .= '<p>' . $error[0] . '</p>';
                }
                $display_errors .= '</div>';

                echo json_encode(array('error' => true, 'message' => $display_errors));
            } else {
                echo json_encode(array('error' => false, 'message' => '<p class="alert alert-success"><i class="fa fa-check"></i> ' . __('Please check your email to reset password.', 'wp-jobsearch') . '</p>'));
            }
        }

        die();
    }

    private function lost_password_retrieve($user_input) {

        global $wpdb, $wp_hasher;

        $errors = new WP_Error();

        if (empty($user_input)) {
            $errors->add('empty_username', __('<i class="fa fa-times"></i> <strong>ERROR</strong>: Enter a username or email address.', 'wp-jobsearch'));
        } elseif (strpos($user_input, '@')) {
            $user_data = get_user_by('email', trim($user_input));
            if (!is_object($user_data)) {
                $user_data = get_user_by('login', trim($user_input));
            }
            if (empty($user_data)) {
                $errors->add('invalid_email', __('<i class="fa fa-times"></i> <strong>ERROR</strong>: There is no user registered with that email address.', 'wp-jobsearch'));
            }
        } else {
            $login = trim($user_input);
            $user_data = get_user_by('login', $login);
        }

        /**
         * Fires before errors are returned from a password reset request.
         *
         *
         * @param WP_Error $errors A WP_Error object containing any errors generated
         *                         by using invalid credentials.
         */
        do_action('lostpassword_post', $errors);

        if ($errors->get_error_code())
            return $errors;

        if (!$user_data) {
            $errors->add('invalidcombo', __('<i class="fa fa-times"></i> <strong>ERROR</strong>: Invalid username or email.', 'wp-jobsearch'));
            return $errors;
        }

        // Redefining user_login ensures we return the right case in the email.
        $user_login = $user_data->user_login;
        $user_email = $user_data->user_email;
        //$key = get_password_reset_key($user_data);
        $key = wp_generate_password(20, false);
        update_user_meta($user_data->ID, 'password_retrieve_key', $key);

        $message = __('Someone has requested a password reset for the following account:', 'wp-jobsearch') . "\r\n\r\n";
        $message .= home_url('/') . "\r\n\r\n";
        $message .= sprintf(__('Username: %s', 'wp-jobsearch'), $user_login) . "\r\n\r\n";
        $message .= __('If this was a mistake, just ignore this email and nothing will happen.', 'wp-jobsearch') . "\r\n\r\n";
        $message .= __('To reset your password, visit the following address:', 'wp-jobsearch') . "\r\n\r\n";
        $message .= '<' . home_url("/?login_action=jobsearch_rp&key=$key&login=" . rawurlencode($user_login)) . ">\r\n";

        if (is_multisite())
            $blogname = $GLOBALS['current_site']->site_name;
        else
        /*
         * The blogname option is escaped with esc_html on the way into the database
         * in sanitize_option we want to reverse this for the plain text arena of emails.
         */
            $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

        $title = sprintf(__('[%s] Password Reset', 'wp-jobsearch'), $blogname);

        /**
         * Filter the subject of the password reset email.
         *
         *
         * @param string  $title      Default email title.
         * @param string  $user_login The username for the user.
         * @param WP_User $user_data  WP_User object.
         */
        $title = apply_filters('retrieve_password_title', $title, $user_login, $user_data);

        /**
         * Filter the message body of the password reset mail.
         *
         *
         * @param string  $message    Default mail message.
         * @param string  $key        The activation key.
         * @param string  $user_login The username for the user.
         * @param WP_User $user_data  WP_User object.
         */
        $message = apply_filters('retrieve_password_message', $message, $key, $user_login, $user_data);

        //if ($message && !wp_mail($user_email, wp_specialchars_decode($title), $message))
        //    $errors->add('mailfailed', __('<i class="fa fa-times"></i> <strong>ERROR</strong>: The email could not be sent. Possible reason: your host may have disabled the mail() function.', 'wp-jobsearch'));

        do_action('jobsearch_reset_password_request', $user_data, $key);

        return true;
    }

    public function reset_password_form() {
        $user_login = isset($_GET['login']) ? $_GET['login'] : '';
        $reg_key = isset($_GET['key']) ? $_GET['key'] : '';
        $get_action = isset($_GET['login_action']) ? $_GET['login_action'] : '';

        if ($user_login != '' && $reg_key != '' && $get_action == 'jobsearch_rp') {
            if (strpos($user_login, '@')) {
                $user_obj = get_user_by('email', trim($user_login));
                if (!is_object($user_obj)) {
                    $user_obj = get_user_by('login', trim($user_login));
                }
            } else {
                $user_obj = get_user_by('login', trim($user_login));
            }

            $user_id = isset($user_obj->ID) ? $user_obj->ID : 0;
            $user_email = isset($user_obj->user_email) ? $user_obj->user_email : '';

            $user_key = get_user_meta($user_id, 'password_retrieve_key', true);

            if ($user_email != '' && $user_key == $reg_key) {

                $popup_args = array('p_user_login' => $user_login, 'p_reg_key' => $reg_key, 'p_user_id' => $user_id);
                add_action('wp_footer', function () use ($popup_args) {

                    extract(shortcode_atts(array(
                        'p_user_login' => '',
                        'p_reg_key' => '',
                        'p_user_id' => '',
                                    ), $popup_args));
                    ?>
                    <div class="jobsearch-modal fade" id="JobSearchModalResetPassForm">
                        <div class="modal-inner-area">&nbsp;</div>
                        <div class="modal-content-area">
                            <div class="modal-box-area">
                                <div class="jobsearch-modal-title-box">
                                    <h2><?php esc_html_e('Reset Your Password', 'wp-jobsearch'); ?></h2>
                                    <span class="modal-close"><i class="fa fa-times"></i></span>
                                </div>
                                <div class="jobsearch-send-message-form">
                                    <form method="post" id="jobsearch_reset_pass_form">
                                        <div class="jobsearch-user-form">
                                            <ul class="email-fields-list">
                                                <li>
                                                    <label>
                                                        <?php echo esc_html__('New Password', 'wp-jobsearch'); ?>:
                                                    </label>
                                                    <div class="input-field">
                                                        <input type="password" name="new_pass" />
                                                    </div>
                                                </li>
                                                <li>
                                                    <label>
                                                        <?php echo esc_html__('Confirm Password', 'wp-jobsearch'); ?>:
                                                    </label>
                                                    <div class="input-field">
                                                        <input type="password" name="conf_pass" />
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="input-field-submit">
                                                        <input type="submit" class="user-passreset-submit-btn" data-id="<?php echo ($p_user_id) ?>" data-key="<?php echo ($p_reg_key) ?>" value="<?php esc_html_e('Reset Password', 'wp-jobsearch'); ?>"/>
                                                        <span class="loader-box"></span>
                                                    </div>
                                                </li>
                                            </ul> 
                                            <div class="message-box" style="display:none;"></div>
                                        </div>
                                    </form>    
                                </div>

                            </div>
                        </div>
                    </div>
                    <script>
                        jobsearch_modal_popup_open('JobSearchModalResetPassForm');
                    </script>
                    <?php
                }, 99, 1);
            }
        }
    }

    public function reset_password_from_redirect() {
        $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : '';
        $user_key = isset($_POST['user_key']) ? $_POST['user_key'] : '';
        $new_pass = isset($_POST['new_pass']) ? $_POST['new_pass'] : '';
        $conf_pass = isset($_POST['conf_pass']) ? $_POST['conf_pass'] : '';

        $user_s_key = get_user_meta($user_id, 'password_retrieve_key', true);

        if ($user_key == $user_s_key) {
            if ($new_pass == $conf_pass) {
                $user_def_array = array(
                    'ID' => $user_id,
                    'user_pass' => $new_pass,
                );
                wp_update_user($user_def_array);
                $c_user = get_user_by('ID', $user_id);
                do_action('jobsearch_user_password_change', $c_user, $new_pass);
                echo json_encode(array('error' => '0', 'msg' => esc_html__('Password changed successfully.', 'wp-jobsearch')));
                die;
            } else {
                echo json_encode(array('error' => '1', 'msg' => esc_html__('Confirm password does not match.', 'wp-jobsearch')));
                die;
            }
        }
        echo json_encode(array('error' => '1', 'msg' => esc_html__('You cannot change password.', 'wp-jobsearch')));
        die;
    }

    // REGISTER
    public function jobsearch_register_member_submit_callback() {

        global $jobsearch_plugin_options;

        $pass_from_user = isset($jobsearch_plugin_options['signup_user_password']) ? $jobsearch_plugin_options['signup_user_password'] : '';

        // Get variables
        $user_login = isset($_POST['pt_user_login']) ? $_POST['pt_user_login'] : '';
        $user_email = isset($_POST['pt_user_email']) ? $_POST['pt_user_email'] : '';
        $user_pass = isset($_POST['pt_user_pass']) ? $_POST['pt_user_pass'] : '';
        $user_cpass = isset($_POST['pt_user_cpass']) ? $_POST['pt_user_cpass'] : '';
        //
        $user_role = isset($_POST['pt_user_role']) ? $_POST['pt_user_role'] : '';
        
        $wredirct_url = isset($_POST['jobsearch_wredirct_url']) ? $_POST['jobsearch_wredirct_url'] : '';
        $extra_params = isset($_POST['extra_login_params']) ? $_POST['extra_login_params'] : '';

        $user_role_array = array('jobsearch_candidate', 'jobsearch_employer');
        if (!in_array($user_role, $user_role_array)) {
            $user_role = 'jobsearch_candidate';
        }

        if ($pass_from_user != 'on') {
            $user_pass = $user_cpass = wp_generate_password(12);
        }

        $user_dashboard_page = isset($jobsearch_plugin_options['user-dashboard-template-page']) ? $jobsearch_plugin_options['user-dashboard-template-page'] : '';
        $user_dashboard_page = jobsearch__get_post_id($user_dashboard_page, 'page');
        $page_url = jobsearch_wpml_lang_page_permalink($user_dashboard_page, 'page'); //get_permalink($user_dashboard_page);
        
        //
        if ($wredirct_url != '') {
            $page_url = $wredirct_url;
        }
        // Check CSRF token
        if (!check_ajax_referer('ajax-login-nonce', 'register-security', false)) {
            echo json_encode(array('error' => true, 'message' => '<div class="alert alert-danger"><i class="fa fa-times"></i> ' . __('Session token has expired, please reload the page and try again', 'wp-jobsearch') . '</div>'));
            die();
        }

        // Check if input variables are empty
        else if (empty($user_login) || empty($user_email) || empty($user_pass) || empty($user_cpass)) {
            echo json_encode(array('error' => true, 'message' => '<div class="alert alert-danger"><i class="fa fa-times"></i> ' . __('Please fill all form fields', 'wp-jobsearch') . '</div>'));
            die();
        }
        $user_ptype = 'candidate';
        if ($user_role == 'jobsearch_employer') {
            $user_ptype = 'employer';
        }
        do_action('jobsearch_register_custom_fields_error', 0, $user_ptype);

        if ($user_pass != $user_cpass) {
            echo json_encode(array('error' => true, 'message' => '<div class="alert alert-danger"><i class="fa fa-times"></i> ' . __('Confirm password field does not match with your password.', 'wp-jobsearch') . '</div>'));
            die();
        }

        jobsearch_captcha_verify();

        $create_user = wp_create_user($user_login, $user_pass, $user_email);

        if (is_wp_error($create_user)) {

            $registration_error_messages = $create_user->errors;

            $display_errors = '<div class="alert alert-danger">';

            foreach ($registration_error_messages as $error) {
                $display_errors .= '<p>' . $error[0] . '</p>';
            }

            $display_errors .= '</div>';

            echo json_encode(array('error' => true, 'message' => $display_errors));
        } else {
            wp_update_user(array('ID' => $create_user, 'role' => $user_role));
            $_user_obj = get_user_by('ID', $create_user);
            if (isset($_user_obj->ID) && $pass_from_user == 'on') {
                wp_set_current_user($_user_obj->ID, $_user_obj->user_login);
                wp_set_auth_cookie($_user_obj->ID);
            }
            if ($pass_from_user == 'on') {
                $reg_args = array(
                    'login_user' => $_user_obj,
                    'extra_params' => $extra_params,
                    'wredirct_url' => $wredirct_url,
                );
                echo apply_filters('jobsearch_after_registr_in_before_msg', '', $reg_args);
                echo json_encode(array('error' => false, 'redirect' => $page_url, 'message' => '<div class="alert alert-success"><i class="fa fa-check"></i> ' . __('Registration complete. You are redirecting to your dashboard.', 'wp-jobsearch') . '</p>'));
            } else {
                echo json_encode(array('error' => false, 'message' => '<div class="alert alert-success"><i class="fa fa-check"></i> ' . __('Registration complete. Password sent to your e-mail.', 'wp-jobsearch') . '</p>'));
            }
            $c_user = get_user_by('email', $user_email);
            do_action('jobsearch_new_user_register', $c_user, $user_pass);
            //
            //do_action('jobsearch_member_register_for_approvel', $c_user);
        }

        die();
    }

    public function jobsearch_registration_save($user_id) {
        global $jobsearch_plugin_options, $sitepress;
        $candidate_auto_approve = isset($jobsearch_plugin_options['candidate_auto_approve']) ? $jobsearch_plugin_options['candidate_auto_approve'] : '';
        $employer_auto_approve = isset($jobsearch_plugin_options['employer_auto_approve']) ? $jobsearch_plugin_options['employer_auto_approve'] : '';

        $user_role = isset($_POST['pt_user_role']) ? $_POST['pt_user_role'] : '';

        $user_role = isset($_POST['role']) && $_POST['role'] != '' ? $_POST['role'] : $user_role;

        $user_role_array = array('jobsearch_candidate', 'jobsearch_employer');
        if (!in_array($user_role, $user_role_array)) {
            $user_role = 'jobsearch_candidate';
        }

        $user_phone = isset($_POST['pt_user_phone']) ? $_POST['pt_user_phone'] : '';

        $user_obj = get_user_by('ID', $user_id);

        if ($user_role == 'jobsearch_employer') {

            $employer_post = array(
                'post_title' => str_replace(array('-', '_'), array(' ', ' '), $user_obj->display_name),
                'post_type' => 'employer',
                'post_content' => '',
                'post_status' => 'publish',
                'post_author' => $user_id,
            );

            // Insert the post into the database
            $employer_id = wp_insert_post($employer_post);

            if (function_exists('icl_object_id')) {
                $lang_code = $sitepress->get_current_language();
                $lang_code = apply_filters('jobsearch_set_post_insert_lang_code', $lang_code);
                $sitepress->set_element_language_details($employer_id, 'post_employer', false, $lang_code);
            }

            //
            update_post_meta($employer_id, 'jobsearch_user_id', $user_id);
            update_post_meta($employer_id, 'member_display_name', $user_obj->display_name);
            update_post_meta($employer_id, 'jobsearch_field_user_email', $user_obj->user_email);
            
            update_post_meta($employer_id, 'post_date', strtotime(current_time('d-m-Y H:i:s')));

            //
            update_post_meta($employer_id, 'jobsearch_field_user_phone', $user_phone);
            if (isset($_POST['pt_user_category'])) {
                $user_sector = sanitize_text_field($_POST['pt_user_category']);
                wp_set_post_terms($employer_id, array($user_sector), 'sector', false);
            }
            if (isset($_POST['pt_user_organization'])) {
                $user_company_title = sanitize_text_field($_POST['pt_user_organization']);
                $up_post = array(
                    'ID' => $employer_id,
                    'post_title' => wp_strip_all_tags($user_company_title),
                );
                wp_update_post($up_post);
                //
                update_post_meta($employer_id, 'member_display_name', wp_strip_all_tags($user_company_title));
                $user_def_array = array(
                    'ID' => $user_id,
                    'display_name' => $user_company_title,
                );

                wp_update_user($user_def_array);
            }
            // custom fields saving
            do_action('jobsearch_signup_custom_fields_save', 'employer', $employer_id);
            //
            if ($employer_auto_approve == 'on') {
                update_post_meta($employer_id, 'jobsearch_field_employer_approved', 'on');
            } else {
                update_post_meta($employer_id, 'jobsearch_field_employer_approved', '');
            }
            //
            update_user_meta($user_id, 'jobsearch_employer_id', $employer_id);
        } else {

            $pos_emails = array();
            for ($i = 0; $i <= 100; $i++) {
                $pos_emails[] = 'cand-dummy' . $i . '@eyecix.com';
                $pos_emails[] = 'emp-dummy' . $i . '@eyecix.com';
            }
            if (!in_array($user_obj->user_email, $pos_emails)) {

                $candidate_post = array(
                    'post_title' => str_replace(array('-', '_'), array(' ', ' '), $user_obj->display_name),
                    'post_type' => 'candidate',
                    'post_content' => '',
                    'post_status' => 'publish',
                    'post_author' => $user_id,
                );

                // Insert the post into the database
                $candidate_id = wp_insert_post($candidate_post);

                if (function_exists('icl_object_id')) {
                    $lang_code = $sitepress->get_current_language();
                    $lang_code = apply_filters('jobsearch_set_post_insert_lang_code', $lang_code);
                    $sitepress->set_element_language_details($candidate_id, 'post_candidate', false, $lang_code);
                }

                //
                update_post_meta($candidate_id, 'jobsearch_user_id', $user_id);
                update_post_meta($candidate_id, 'member_display_name', $user_obj->display_name);
                update_post_meta($candidate_id, 'jobsearch_field_user_email', $user_obj->user_email);
                
                update_post_meta($candidate_id, 'post_date', strtotime(current_time('d-m-Y H:i:s')));

                //
                update_post_meta($candidate_id, 'jobsearch_field_user_phone', $user_phone);
                if (isset($_POST['pt_user_category'])) {
                    $user_sector = sanitize_text_field($_POST['pt_user_category']);
                    wp_set_post_terms($candidate_id, array($user_sector), 'sector', false);
                }

                // custom fields saving
                do_action('jobsearch_signup_custom_fields_save', 'candidate', $candidate_id);
                //

                if ($candidate_auto_approve == 'on') {
                    update_post_meta($candidate_id, 'jobsearch_field_candidate_approved', 'on');
                } else {
                    update_post_meta($candidate_id, 'jobsearch_field_candidate_approved', '');
                }

                //
                update_user_meta($user_id, 'jobsearch_candidate_id', $candidate_id);

                // add candidate skills level
                jobsearch_candidate_skill_percent_count($user_id, 'none');
            }
        }

        //remove user admin bar
        update_user_meta($user_id, 'show_admin_bar_front', false);
    }

}

// class Jobsearch_Login_Registration_Submit 
$Jobsearch_Login_Registration_Submit_obj = new Jobsearch_Login_Registration_Submit();
