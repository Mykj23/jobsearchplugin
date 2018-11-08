<?php
/*
  Class : Login_Registration
 */


// this is an include only WP file
if (!defined('ABSPATH')) {
    die;
}

// main plugin class
class Jobsearch_Login_Registration_Template {

    // hook things up
    public function __construct() {
        add_action('login_registration_form_html', array($this, 'login_registration_form_html_callback'), 1);
        add_action('login_form_html', array($this, 'login_form_html_callback'), 1);
        add_action('registration_form_html', array($this, 'registration_form_html_callback'), 1);

        //
        add_action('login_reg_popup_html', array($this, 'popup_login_reg_form_html_callback'), 10, 1);
    }

    public function login_registration_form_html_callback($arg) {
        ob_start();
        global $jobsearch_plugin_options;
        $op_register_form_allow = isset($jobsearch_plugin_options['login_register_form']) ? $jobsearch_plugin_options['login_register_form'] : '';
        $op_cand_register_allow = isset($jobsearch_plugin_options['login_candidate_register']) ? $jobsearch_plugin_options['login_candidate_register'] : '';
        $op_emp_register_allow = isset($jobsearch_plugin_options['login_employer_register']) ? $jobsearch_plugin_options['login_employer_register'] : '';
        $register_form_allow = isset($arg['login_register_form']) ? $arg['login_register_form'] : '';
        $cand_register_allow = isset($arg['login_candidate_register']) ? $arg['login_candidate_register'] : '';
        $emp_register_allow = isset($arg['login_employer_register']) ? $arg['login_employer_register'] : '';

        $register_form_view = true;
        if ($op_register_form_allow == 'off') {
            $register_form_view = false;
        }
        if ($op_cand_register_allow == 'no' && $op_emp_register_allow == 'no') {
            $register_form_view = false;
        }

        if ($register_form_allow == 'off') {
            $register_form_view = false;
        } else {
            $register_form_view = true;
        }
        if ($cand_register_allow == 'no' && $emp_register_allow == 'no') {
            $register_form_view = false;
        } else {
            if ($register_form_allow != 'off') {
                $register_form_view = true;
            }
        }

        $html = '';
        ?>
        <div class="jobsearch-row">
            <div class="jobsearch-column-6">
                <div class="jobsearch-login-box">
                    <?php do_action('login_form_html', $arg); ?>
                    <?php do_action('social_login_html', $arg); ?>                      
                </div> 

            </div>
            <?php
            if (!is_user_logged_in()) {
                if ($register_form_view === true) {
                    ?>
                    <div class="jobsearch-column-6">
                        <div class="jobsearch-login-box">
                            <?php do_action('registration_form_html', $arg); ?>
                        </div>                            
                    </div>
                    <?php
                } else {
                    echo '<div class="alert alert-warning">' . __('Registration is disabled.', 'wp-jobsearch') . '</div>';
                }
            }
            ?>
        </div>
        <?php
        $html = ob_get_clean();
        echo force_balance_tags($html);
    }

    public function popup_login_reg_form_html_callback($args = array()) {
        global $jobsearch_plugin_options;
        $captcha_switch = isset($jobsearch_plugin_options['captcha_switch']) ? $jobsearch_plugin_options['captcha_switch'] : '';
        $jobsearch_sitekey = isset($jobsearch_plugin_options['captcha_sitekey']) ? $jobsearch_plugin_options['captcha_sitekey'] : '';

        $demo_user_login = isset($jobsearch_plugin_options['demo_user_login']) ? $jobsearch_plugin_options['demo_user_login'] : '';
        $demo_candidate = isset($jobsearch_plugin_options['demo_candidate']) ? $jobsearch_plugin_options['demo_candidate'] : '';
        $demo_employer = isset($jobsearch_plugin_options['demo_employer']) ? $jobsearch_plugin_options['demo_employer'] : '';

        $adm_user_obj = get_user_by('login', 'jobsearch-admin');
        if (is_object($adm_user_obj) && isset($adm_user_obj->ID) && in_array('administrator', jobsearch_get_user_roles_by_user_id($adm_user_obj->ID))) {
            $demo_user_login = $demo_user_login;
        } else {
            $demo_user_login = 'off';
        }

        $op_register_form_allow = isset($jobsearch_plugin_options['login_register_form']) ? $jobsearch_plugin_options['login_register_form'] : '';
        $op_cand_register_allow = isset($jobsearch_plugin_options['login_candidate_register']) ? $jobsearch_plugin_options['login_candidate_register'] : '';
        $op_emp_register_allow = isset($jobsearch_plugin_options['login_employer_register']) ? $jobsearch_plugin_options['login_employer_register'] : '';

        $register_form_view = true;
        if ($op_register_form_allow == 'off') {
            $register_form_view = false;
        }
        if ($op_cand_register_allow == 'no' && $op_emp_register_allow == 'no') {
            $register_form_view = false;
        }

        //
        $signup_user_sector = isset($jobsearch_plugin_options['signup_user_sector']) ? $jobsearch_plugin_options['signup_user_sector'] : '';
        $signup_org_name = isset($jobsearch_plugin_options['signup_organization_name']) ? $jobsearch_plugin_options['signup_organization_name'] : '';
        $signup_user_phone = isset($jobsearch_plugin_options['signup_user_phone']) ? $jobsearch_plugin_options['signup_user_phone'] : '';
        $pass_from_user = isset($jobsearch_plugin_options['signup_user_password']) ? $jobsearch_plugin_options['signup_user_password'] : '';

        ob_start();
        $html = '';
        $rand_numb = rand(1000000, 9999999);
        ?>
        <div class="login-form-<?php echo absint($rand_numb) ?>">

            <div class="jobsearch-modal-title-box">
                <h2><?php _e('Login to your account', 'wp-jobsearch') ?></h2>
                <span class="modal-close"><i class="fa fa-times"></i></span>
            </div>
            <form id="login-form-<?php echo absint($rand_numb) ?>" action="<?php echo home_url('/'); ?>" method="post">
                <?php
                if ($demo_user_login == 'on') {
                    $_demo_candidate_obj = get_user_by('login', $demo_candidate);
                    $_demo_candidate_id = isset($_demo_candidate_obj->ID) ? $_demo_candidate_obj->ID : '';

                    $_demo_employer_obj = get_user_by('login', $demo_employer);
                    $_demo_employer_id = isset($_demo_employer_obj->ID) ? $_demo_employer_obj->ID : '';

                    if ($_demo_candidate_id != '' || $_demo_employer_id != '') {
                        ?>
                        <div class="jobsearch-box-title">
                            <span><?php esc_html_e('Choose your Account Type', 'wp-jobsearch') ?></span>
                        </div>
                        <div class="demo-login-btns jobsearch-user-options">
                            <ul class="jobsearch-user-type-choose">
                                <?php
                                if ($_demo_candidate_id != '') {
                                    ?>
                                    <li class="candidate-login active"> 
                                        <a href="javascript:void(0);" class="jobsearch-demo-login-btn candidate-login-btn">
                                            <i class="jobsearch-icon jobsearch-user"></i>
                                            <span><?php esc_html_e('Demo Candidate', 'wp-jobsearch') ?></span>
                                            <small><?php esc_html_e('Logged in as Candidate', 'wp-jobsearch') ?></small>
                                        </a>
                                    </li>
                                    <?php
                                }
                                if ($_demo_employer_id != '') {
                                    ?>
                                    <li class="employer-login"> 
                                        <a href="javascript:void(0);" class="jobsearch-demo-login-btn employer-login-btn">
                                            <i class="jobsearch-icon jobsearch-building"></i>
                                            <span><?php esc_html_e('Demo Employer', 'wp-jobsearch') ?></span>
                                            <small><?php esc_html_e('Logged in as Employer', 'wp-jobsearch') ?></small>
                                        </a>
                                    </li>
                                    <?php
                                }
                                ?>
                            </ul>
                        </div>
                        <?php
                    }
                }
                ?>
                <div class="jobsearch-user-form">
                    <ul> 
                        <li> 
                            <label><?php _e('Username/Email Address:', 'wp-jobsearch') ?></label>
                            <input class="required" name="pt_user_login" type="text" placeholder="<?php _e('Username', 'wp-jobsearch') ?>" />
                            <i class="jobsearch-icon jobsearch-user"></i>
                        </li>
                        <li> 
                            <label><?php _e('Password:', 'wp-jobsearch') ?></label>
                            <input class="required" name="pt_user_pass" type="password" placeholder="<?php _e('Password', 'wp-jobsearch') ?>">
                            <i class="jobsearch-icon jobsearch-multimedia"></i>
                        </li>
                        <li class="jobsearch-user-form-coltwo-full">
                            <input type="hidden" name="action" value="jobsearch_login_member_submit">
                            <?php
                            ob_start();
                            ?>
                            <input data-id="<?php echo absint($rand_numb) ?>" class="jobsearch-login-submit-btn" data-loading-text="<?php _e('Loading...', 'wp-jobsearch') ?>" type="submit" value="<?php _e('Sign In', 'wp-jobsearch'); ?>">
                            <div class="form-loader"></div>
                            <div class="jobsearch-user-form-info">
                                <p><a href="javascript:void(0);" class="lost-password" data-id="<?php echo absint($rand_numb) ?>"><?php echo esc_html__("Forgot Password?", "wp-jobsearch"); ?></a><?php if ($register_form_view === true) { ?> | <a href="javascript:void(0);" class="register-form" data-id="<?php echo absint($rand_numb) ?>"><?php _e('Sign Up', 'wp-jobsearch') ?></a><?php } ?></p>
                                <div class="jobsearch-checkbox">
                                    <input type="checkbox" id="r-<?php echo ($rand_numb) ?>" name="remember_password">
                                    <label for="r-<?php echo ($rand_numb) ?>"><span></span> <?php _e('Remember Password', 'wp-jobsearch') ?></label>
                                </div>
                            </div>
                            <?php
                            $html = ob_get_clean();
                            echo apply_filters('jobsearch_login_popup_remember_info_links', $html, $rand_numb, $register_form_view);
                            ?>
                        </li>
                    </ul>
                    <?php
                    $allow_args = array(
                        'input' => array(
                            'name' => array(),
                            'value' => array(),
                            'type' => array(),
                        ),
                    );
                    ob_start();
                    wp_nonce_field('ajax-login-nonce', 'login-security');
                    $secur_field = ob_get_clean();
                    echo wp_kses($secur_field, $allow_args);
                    ?>
                    <div class="login-reg-errors"></div>
                </div>
                <?php do_action('social_login_html', $args); ?>
            </form>
        </div>
        <div class="jobsearch-reset-password reset-password-<?php echo absint($rand_numb) ?>" style="display:none;">

            <div class="jobsearch-modal-title-box">
                <h2><?php _e('Reset Password', 'wp-jobsearch') ?></h2>
                <span class="modal-close"><i class="fa fa-times"></i></span>
            </div>

            <form id="reset-password-form-<?php echo absint($rand_numb) ?>" action="<?php echo home_url('/'); ?>" method="post">
                <div class="jobsearch-user-form">
                    <ul>
                        <li class="jobsearch-user-form-coltwo-full">
                            <label><?php _e('Username/Email Address:', 'wp-jobsearch') ?></label>
                            <input id="pt_user_or_email_<?php echo absint($rand_numb) ?>" class="required" name="pt_user_or_email" type="text" placeholder="<?php _e('Username or E-mail', 'wp-jobsearch') ?>" />
                            <i class="jobsearch-icon jobsearch-mail"></i>
                        </li>
                        <li class="jobsearch-user-form-coltwo-full">
                            <input type="hidden" name="action" value="jobsearch_reset_password"/>
                            <input data-id="<?php echo absint($rand_numb) ?>" class="jobsearch-reset-password-submit-btn" type="submit" value="<?php _e('Get new password', 'wp-jobsearch'); ?>">

                            <div class="form-loader"></div>
                            <div class="jobsearch-user-form-info">
                                <p><a href="javascript:void(0);" class="login-form-btn" data-id="<?php echo absint($rand_numb) ?>"><?php echo esc_html__("Already have an account? Login", "wp-jobsearch"); ?></a></p>
                            </div>
                        </li>
                    </ul>

                    <p><?php _e('Enter the username or e-mail you used in your profile. A password reset link will be sent to you by email.', 'wp-jobsearch'); ?></p>

                    <?php
                    $allow_args = array(
                        'input' => array(
                            'name' => array(),
                            'value' => array(),
                            'type' => array(),
                        ),
                    );
                    ob_start();
                    wp_nonce_field('ajax-login-nonce', 'password-security');
                    $secur_field = ob_get_clean();
                    echo wp_kses($secur_field, $allow_args);
                    ?>
                    <div class="reset-password-errors"></div>
                </div>
            </form>

        </div>
        <?php
        if ($register_form_view === true) {
            ?>
            <div class="jobsearch-register-form register-<?php echo absint($rand_numb) ?>" style="display:none;">
                <div class="jobsearch-modal-title-box">
                    <h2><?php _e('Signup to your Account', 'wp-jobsearch') ?></h2>
                    <span class="modal-close"><i class="fa fa-times"></i></span>
                </div>
                <form id="registration-form-<?php echo absint($rand_numb) ?>" action="<?php echo home_url('/'); ?>" method="POST">
                    <?php
                    if ($op_cand_register_allow == 'no') {
                        ?>
                        <input type="hidden" name="pt_user_role" value="jobsearch_employer">
                        <?php
                    } else if ($op_emp_register_allow == 'no') {
                        ?>
                        <input type="hidden" name="pt_user_role" value="jobsearch_candidate">
                        <?php
                    } else {
                        ?>
                        <div class="jobsearch-box-title">
                            <span><?php _e('Choose your Account Type', 'wp-jobsearch') ?></span>
                            <input type="hidden" name="pt_user_role" value="jobsearch_candidate">
                        </div>
                        <div class="jobsearch-user-options">
                            <ul class="jobsearch-user-type-choose">
                                <li class="active">
                                    <a href="javascript:void(0);" class="user-type-chose-btn" data-type="jobsearch_candidate">
                                        <i class="jobsearch-icon jobsearch-user"></i>
                                        <span><?php _e('Candidate', 'wp-jobsearch') ?></span>
                                        <small><?php _e('I want to discover awesome companies.', 'wp-jobsearch') ?></small>
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="user-type-chose-btn" data-type="jobsearch_employer">
                                        <i class="jobsearch-icon jobsearch-building"></i>
                                        <span><?php _e('Employer', 'wp-jobsearch') ?></span>
                                        <small><?php _e('I want to attract the best talent.', 'wp-jobsearch') ?></small>
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <?php
                    }
                    ?>
                    <div class="jobsearch-user-form jobsearch-user-form-coltwo">
                        <ul>
                            <?php do_action('jobsearch_registration_extra_fields_start') ?>
                            <li>
                                <label><?php _e('Username:', 'wp-jobsearch') ?></label>
                                <input class="required" name="pt_user_login" type="text" placeholder="<?php _e('Username', 'wp-jobsearch'); ?>" />
                                <i class="jobsearch-icon jobsearch-user"></i>
                            </li>
                            <li>
                                <label><?php _e('Email Address:', 'wp-jobsearch') ?></label>
                                <input class="required" name="pt_user_email" id="pt_user_email_<?php echo absint($rand_numb) ?>" type="email" placeholder="<?php _e('Email', 'wp-jobsearch'); ?>" />
                                <i class="jobsearch-icon jobsearch-mail"></i>
                            </li>
                            <?php
                            if ($pass_from_user == 'on') {
                                ?>
                                <li>
                                    <label><?php _e('Password:', 'wp-jobsearch') ?></label>
                                    <input class="required" name="pt_user_pass" id="pt_user_pass_<?php echo absint($rand_numb) ?>" type="password" placeholder="<?php _e('Password', 'wp-jobsearch'); ?>" />
                                    <i class="jobsearch-icon jobsearch-multimedia"></i>
                                </li>
                                <li>
                                    <label><?php _e('Confirm Password:', 'wp-jobsearch') ?></label>
                                    <input class="required" name="pt_user_cpass" id="pt_user_cpass_<?php echo absint($rand_numb) ?>" type="password" placeholder="<?php _e('Confirm Password', 'wp-jobsearch'); ?>" />
                                    <i class="jobsearch-icon jobsearch-multimedia"></i>
                                </li>
                                <?php
                            }
                            if ($signup_user_phone == 'on') {
                                ?>
                                <li class="jobsearch-user-form-coltwo-full">
                                    <label><?php _e('Phone:', 'wp-jobsearch') ?></label>
                                    <input class="required" name="pt_user_phone" id="pt_user_phone_<?php echo absint($rand_numb) ?>" type="text" placeholder="<?php _e('Phone Number', 'wp-jobsearch'); ?>" />
                                    <i class="jobsearch-icon jobsearch-technology"></i>
                                </li>
                                <?php
                            }
                            if ($signup_org_name == 'on') {
                                ?>
                                <li class="user-employer-spec-field jobsearch-user-form-coltwo-full" style="display: <?php echo ($op_emp_register_allow != 'no' && $op_cand_register_allow == 'no' ? 'block' : 'none') ?>;">
                                    <label><?php _e('Organization Name:', 'wp-jobsearch') ?></label>
                                    <input class="required" name="pt_user_organization" id="pt_user_organization_<?php echo absint($rand_numb) ?>" type="text" placeholder="<?php _e('Organization Name', 'wp-jobsearch'); ?>" />
                                    <i class="jobsearch-icon jobsearch-briefcase"></i>
                                </li>
                                <?php
                            }
                            if ($signup_user_sector == 'on') {
                                ?>
                                <li class="jobsearch-user-form-coltwo-full">
                                    <label><?php _e('Select Sector:', 'wp-jobsearch') ?></label>
                                    <div class="jobsearch-profile-select">
                                        <?php
                                        $sector_args = array(
                                            'show_option_all' => esc_html__('Select Sector', 'wp-jobsearch'),
                                            'show_option_none' => '',
                                            'option_none_value' => '',
                                            'orderby' => 'title',
                                            'order' => 'ASC',
                                            'show_count' => 0,
                                            'hide_empty' => 0,
                                            'echo' => 1,
                                            'selected' => '',
                                            'hierarchical' => 1,
                                            'id' => 'pt_user_category_' . absint($rand_numb),
                                            'class' => 'postform selectize-select',
                                            'name' => 'pt_user_category',
                                            'depth' => 0,
                                            'taxonomy' => 'sector',
                                            'hide_if_empty' => false,
                                            'value_field' => 'term_id',
                                        );
                                        wp_dropdown_categories($sector_args);
                                        ?>
                                    </div>
                                    <script>
                                        jQuery('#pt_user_category_<?php echo absint($rand_numb) ?>').find('option').first().val('');
                                        jQuery('#pt_user_category_<?php echo absint($rand_numb) ?>').attr('placeholder', '<?php esc_html_e('Select Sector', 'wp-jobsearch') ?>');
                                    </script>
                                </li>
                                <?php
                            }
                            $c_fields_dis = 'none';
                            if ($op_emp_register_allow != 'no' && $op_cand_register_allow == 'no') {
                                $c_fields_dis = 'block';
                            }
                            do_action('jobsearch_signup_custom_fields_load', 0, 'candidate', '');
                            do_action('jobsearch_signup_custom_fields_load', 0, 'employer', $c_fields_dis);

                            if ($captcha_switch == 'on' && !is_user_logged_in()) {
                                wp_enqueue_script('jobsearch_google_recaptcha');
                                ?>
                                <li class="jobsearch-user-form-coltwo-full">
                                    <script>
                                        var recaptcha_popup;
                                        var jobsearch_multicap = function () {
                                            //Render the recaptcha_popup on the element with ID "recaptcha_popup"
                                            recaptcha_popup = grecaptcha.render('recaptcha_popup', {
                                                'sitekey': '<?php echo ($jobsearch_sitekey); ?>', //Replace this with your Site key
                                                'theme': 'light'
                                            });
                                        };
                                        jQuery(document).ready(function () {
                                            jQuery('.recaptcha-reload-a').click();
                                        });
                                    </script>
                                    <div class="recaptcha-reload" id="recaptcha_popup_div">
                                        <?php echo jobsearch_recaptcha('recaptcha_popup'); ?>
                                    </div>
                                </li>
                                <?php
                            }
                            ?>
                            <?php do_action('jobsearch_registration_extra_fields_end') ?>
                            <li class="jobsearch-user-form-coltwo-full">
                                <?php
                                jobsearch_terms_and_con_link_txt();
                                ?>
                                <input type="hidden" name="action" value="jobsearch_register_member_submit">
                                <input data-id="<?php echo absint($rand_numb) ?>" class="jobsearch-register-submit-btn" data-loading-text="<?php _e('Loading...', 'wp-jobsearch') ?>" type="submit" value="<?php _e('Sign up', 'wp-jobsearch'); ?>">
                                
                                <div class="form-loader"></div>

                                <div class="jobsearch-user-form-info">
                                    <p><a href="javascript:void(0);" class="reg-tologin-btn" data-id="<?php echo absint($rand_numb) ?>"><?php echo esc_html__("Already have an account? Login", "wp-jobsearch"); ?></a></p>
                                </div>
                            </li>
                        </ul>
                        <div class="clearfix"></div>

                        <?php
                        $allow_args = array(
                            'input' => array(
                                'name' => array(),
                                'value' => array(),
                                'type' => array(),
                            ),
                        );
                        ob_start();
                        wp_nonce_field('ajax-login-nonce', 'register-security');
                        $secur_field = ob_get_clean();
                        echo wp_kses($secur_field, $allow_args);
                        ?>
                        <div class="registration-errors"></div>
                    </div>
                    <?php do_action('social_login_html', $args); ?>
                </form>
            </div>
            <?php
        }
        $html = ob_get_clean();
        echo force_balance_tags($html);
    }

    public function login_form_html_callback($arg) {
        global $jobsearch_plugin_options;
        ob_start();
        $html = '';
        $rand_numb = rand(1000000, 9999999);
        if (!is_user_logged_in()) { // only show the registration/login form to non-logged-in members
            $demo_user_login = isset($jobsearch_plugin_options['demo_user_login']) ? $jobsearch_plugin_options['demo_user_login'] : '';
            $demo_candidate = isset($jobsearch_plugin_options['demo_candidate']) ? $jobsearch_plugin_options['demo_candidate'] : '';
            $demo_employer = isset($jobsearch_plugin_options['demo_employer']) ? $jobsearch_plugin_options['demo_employer'] : '';

            $adm_user_obj = get_user_by('login', 'jobsearch-admin');
            if (is_object($adm_user_obj) && isset($adm_user_obj->ID) && in_array('administrator', jobsearch_get_user_roles_by_user_id($adm_user_obj->ID))) {
                $demo_user_login = $demo_user_login;
            } else {
                $demo_user_login = 'off';
            }
            ?>
            <div class="login-form login-form-<?php echo absint($rand_numb) ?>">
                <h2><?php _e('Login to our site', 'wp-jobsearch') ?></h2>
                <?php
                if ($demo_user_login == 'on') {
                    $_demo_candidate_obj = get_user_by('login', $demo_candidate);
                    $_demo_candidate_id = isset($_demo_candidate_obj->ID) ? $_demo_candidate_obj->ID : '';

                    $_demo_employer_obj = get_user_by('login', $demo_employer);
                    $_demo_employer_id = isset($_demo_employer_obj->ID) ? $_demo_employer_obj->ID : '';

                    if ($_demo_candidate_id != '' || $_demo_employer_id != '') {
                        ?>
                        <div class="demo-login-pbtns jobsearch-roles-container">
                            <?php
                            if ($_demo_candidate_id != '') {
                                ?>
                                <div class="jobsearch-radio-checkbox candidate-login active">
                                    <a href="javascript:void(0);" class="jobsearch-demo-login-btn candidate-login-btn"><i class="jobsearch-icon jobsearch-user"></i> <?php esc_html_e('Demo Candidate', 'wp-jobsearch') ?></a>
                                </div>
                                <?php
                            }
                            if ($_demo_employer_id != '') {
                                ?>
                                <div class="jobsearch-radio-checkbox employer-login"> 
                                    <a href="javascript:void(0);" class="jobsearch-demo-login-btn employer-login-btn"><i class="jobsearch-icon jobsearch-building"></i> <?php esc_html_e('Demo Employer', 'wp-jobsearch') ?></a>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                        <?php
                    }
                }
                ?>
                <span class="enter-userpass-txt"><?php _e('Enter username and password to login:', 'wp-jobsearch') ?></span>  
                <form id="login-form-<?php echo absint($rand_numb) ?>" action="<?php echo home_url('/'); ?>" method="post">
                    <ul> 
                        <li> 
                            <input class="form-control input-lg required" name="pt_user_login" type="text" placeholder="<?php _e('Username', 'wp-jobsearch') ?>" />
                        </li>
                        <li> 
                            <input class="form-control input-lg required" name="pt_user_pass" type="password" placeholder="<?php _e('Password', 'wp-jobsearch') ?>">
                        </li>
                        <li>
                            <a href="javascript:void(0);" class="lost-password" data-id="<?php echo absint($rand_numb) ?>"><?php echo esc_html__("Forgot your password?", "wp-jobsearch"); ?></a>
                            <input type="hidden" name="action" value="jobsearch_login_member_submit"/>
                            <button data-id="<?php echo absint($rand_numb) ?>" class="jobsearch-login-submit-btn btn btn-theme btn-lg" data-loading-text="<?php _e('Loading...', 'wp-jobsearch') ?>" type="submit"><?php _e('Login', 'wp-jobsearch'); ?></button> 
                            <div class="form-loader"></div>
                        </li>
                    </ul>

                    <?php
                    $allow_args = array(
                        'input' => array(
                            'name' => array(),
                            'value' => array(),
                            'type' => array(),
                        ),
                    );
                    ob_start();
                    wp_nonce_field('ajax-login-nonce', 'login-security');
                    $secur_field = ob_get_clean();
                    echo wp_kses($secur_field, $allow_args);
                    ?>
                    <div class="login-reg-errors"></div>
                </form>
            </div>
            <!-- Lost Password form -->
            <div class="pt-reset-password reset-password-<?php echo absint($rand_numb) ?>" style="display:none;">

                <h2><?php _e('Reset Password', 'wp-jobsearch'); ?></h2>

                <form id="reset-password-form-<?php echo absint($rand_numb) ?>" action="<?php echo home_url('/'); ?>" method="post">
                    <ul> 
                        <li> 
                            <input id="pt_user_or_email" class="form-control input-lg required" name="pt_user_or_email" type="text" placeholder="<?php _e('Username or E-mail', 'wp-jobsearch') ?>" />
                        </li>
                        <li>
                            <a href="javascript:void(0);" class="login-form-btn" data-id="<?php echo absint($rand_numb) ?>"><?php echo esc_html__("Already have an account? Login", "wp-jobsearch"); ?></a>
                            <input type="hidden" name="action" value="jobsearch_reset_password"/>
                            <button data-id="<?php echo absint($rand_numb) ?>" class="jobsearch-reset-password-submit-btn btn btn-theme btn-lg" data-loading-text="<?php _e('Loading...', 'wp-jobsearch') ?>" type="submit"><?php _e('Get new password', 'wp-jobsearch'); ?></button>
                            <div class="form-loader"></div>
                        </li>
                    </ul>

                    <p><?php _e('Enter the username or e-mail you used in your profile. A password reset link will be sent to you by email.', 'wp-jobsearch'); ?></p>

                    <?php
                    $allow_args = array(
                        'input' => array(
                            'name' => array(),
                            'value' => array(),
                            'type' => array(),
                        ),
                    );
                    ob_start();
                    wp_nonce_field('ajax-login-nonce', 'password-security');
                    $secur_field = ob_get_clean();
                    echo wp_kses($secur_field, $allow_args);
                    ?>
                    <div class="reset-password-errors"></div>
                </form>

            </div>

            <div class="pt-loading" style="display:none;">
                <p><i class="fa fa-refresh fa-spin"></i><br><?php _e('Loading...', 'wp-jobsearch') ?></p>
            </div>
            <?php
        } else {
            ?> 
            <div class="login-reg-logout">							
                <div class="alert alert-info">
                    <?php
                    $current_user = wp_get_current_user();
                    printf(__('You have already logged in as %1$s. <a href="%2$s">Logout?</a>', 'wp-jobsearch'), $current_user->user_login, wp_logout_url(home_url('/')));
                    ?>
                </div>
                <div class="pt-errors"></div>
            </div> 
            <?php
        }
        $html = ob_get_clean();
        echo force_balance_tags($html);
    }

    public function registration_form_html_callback($arg) {
        global $jobsearch_plugin_options;
        $captcha_switch = isset($jobsearch_plugin_options['captcha_switch']) ? $jobsearch_plugin_options['captcha_switch'] : '';
        $jobsearch_sitekey = isset($jobsearch_plugin_options['captcha_sitekey']) ? $jobsearch_plugin_options['captcha_sitekey'] : '';

        $op_cand_register_allow = isset($jobsearch_plugin_options['login_candidate_register']) ? $jobsearch_plugin_options['login_candidate_register'] : '';
        $op_emp_register_allow = isset($jobsearch_plugin_options['login_employer_register']) ? $jobsearch_plugin_options['login_employer_register'] : '';
        $cand_register_allow = isset($arg['login_candidate_register']) ? $arg['login_candidate_register'] : '';
        $emp_register_allow = isset($arg['login_employer_register']) ? $arg['login_employer_register'] : '';

        $cand_register_view = true;
        if ($op_cand_register_allow == 'no') {
            $cand_register_view = false;
        }
        if ($cand_register_allow == 'no') {
            $cand_register_view = false;
        } else {
            $cand_register_view = true;
        }

        $emp_register_view = true;
        if ($op_emp_register_allow == 'no') {
            $emp_register_view = false;
        }
        if ($emp_register_allow == 'no') {
            $emp_register_view = false;
        } else {
            $emp_register_view = true;
        }

        ob_start();
        $html = '';
        $rand_numb = rand(1000000, 9999999);

        if (!is_user_logged_in()) {

            //
            $signup_user_sector = isset($jobsearch_plugin_options['signup_user_sector']) ? $jobsearch_plugin_options['signup_user_sector'] : '';
            $signup_org_name = isset($jobsearch_plugin_options['signup_organization_name']) ? $jobsearch_plugin_options['signup_organization_name'] : '';
            $signup_user_phone = isset($jobsearch_plugin_options['signup_user_phone']) ? $jobsearch_plugin_options['signup_user_phone'] : '';
            $pass_from_user = isset($jobsearch_plugin_options['signup_user_password']) ? $jobsearch_plugin_options['signup_user_password'] : '';
            ?>
            <!-- Register form -->
            <div class="pt-register"> 
                <?php //if (get_option('users_can_register')) { ?>
                    <h2><?php _e('Sign up now', 'wp-jobsearch'); ?></h2>
                    <span><?php _e('Fill the form below to get instant access:', 'wp-jobsearch'); ?></span> 
                    <form id="registration-form-<?php echo absint($rand_numb) ?>" action="<?php echo home_url('/'); ?>" method="POST">

                        <ul>
                            <?php do_action('jobsearch_registration_extra_fields_start') ?>

                            <li>
                                <?php
                                if ($cand_register_view === false) {
                                    ?>
                                    <input type="hidden" name="pt_user_role" value="jobsearch_employer">
                                    <?php
                                } else if ($emp_register_view === false) {
                                    ?>
                                    <input type="hidden" name="pt_user_role" value="jobsearch_candidate">
                                    <?php
                                } else {
                                    ?>
                                    <div class="jobsearch-roles-container">
                                        <div class="jobsearch-radio-checkbox">
                                            <input id="candidate-role-<?php echo ($rand_numb) ?>" type="radio" name="pt_user_role" value="jobsearch_candidate" checked="checked"> <label for="candidate-role-<?php echo ($rand_numb) ?>"><i class="jobsearch-icon jobsearch-user"></i> <?php esc_html_e('Candidate', 'wp-jobsearch') ?></label>
                                        </div>
                                        <div class="jobsearch-radio-checkbox">
                                            <input id="employer-role-<?php echo ($rand_numb) ?>" type="radio" name="pt_user_role" value="jobsearch_employer"> <label for="employer-role-<?php echo ($rand_numb) ?>"><i class="jobsearch-icon jobsearch-building"></i> <?php esc_html_e('Employer', 'wp-jobsearch') ?></label> 
                                        </div>
                                    </div>
                                    <?php
                                }
                                ?>
                            </li>
                            <li>
                                <input class="form-control input-lg required" name="pt_user_login" type="text" placeholder="<?php _e('Username', 'wp-jobsearch'); ?>" />
                            </li>
                            <li>
                                <input class="form-control input-lg required" name="pt_user_email" id="pt_user_email" type="email" placeholder="<?php _e('Email', 'wp-jobsearch'); ?>" />
                            </li>
                            <?php
                            if ($signup_user_phone == 'on') {
                                ?>
                                <li>
                                    <input class="required" name="pt_user_phone" id="pt_user_phone" type="text" placeholder="<?php _e('Phone Number', 'wp-jobsearch'); ?>" />
                                </li>
                                <?php
                            }
                            if ($signup_org_name == 'on') {
                                ?>
                                <li class="user-employer-spec-field" style="display: <?php echo ($emp_register_allow !== false && $cand_register_view === false ? 'block' : 'none') ?>;">
                                    <input class="required" name="pt_user_organization" id="pt_user_organization" type="text" placeholder="<?php _e('Organization Name', 'wp-jobsearch'); ?>" />
                                </li>
                                <?php
                            }
                            if ($signup_user_sector == 'on') {
                                ?>
                                <li>
                                    <div class="jobsearch-profile-select">
                                        <?php
                                        $sector_args = array(
                                            'show_option_all' => esc_html__('Select Sector', 'wp-jobsearch'),
                                            'show_option_none' => '',
                                            'option_none_value' => '',
                                            'orderby' => 'title',
                                            'order' => 'ASC',
                                            'show_count' => 0,
                                            'hide_empty' => 0,
                                            'echo' => 1,
                                            'selected' => '',
                                            'hierarchical' => 1,
                                            'id' => 'pt_user_category',
                                            'class' => 'postform selectize-select',
                                            'name' => 'pt_user_category',
                                            'depth' => 0,
                                            'taxonomy' => 'sector',
                                            'hide_if_empty' => false,
                                            'value_field' => 'term_id',
                                        );
                                        wp_dropdown_categories($sector_args);
                                        ?>
                                    </div>
                                    <script>
                                        jQuery('#pt_user_category').find('option').first().val('');
                                        jQuery('#pt_user_category').attr('placeholder', '<?php esc_html_e('Select Sector', 'wp-jobsearch') ?>');
                                    </script>
                                </li>
                                <?php
                            }
                            $c_fields_dis = 'none';
                            if ($emp_register_allow !== false && $cand_register_view === false) {
                                $c_fields_dis = 'block';
                            }
                            do_action('jobsearch_signup_custom_fields_load', 0, 'candidate', '');
                            do_action('jobsearch_signup_custom_fields_load', 0, 'employer', $c_fields_dis);

                            if ($captcha_switch == 'on' && !is_user_logged_in()) {
                                wp_enqueue_script('jobsearch_google_recaptcha');
                                ?>
                                <li>
                                    <script>
                                        var recaptcha1;
                                        var jobsearch_multicap = function () {
                                            //Render the recaptcha1 on the element with ID "recaptcha1"
                                            recaptcha1 = grecaptcha.render('recaptcha1', {
                                                'sitekey': '<?php echo ($jobsearch_sitekey); ?>', //Replace this with your Site key
                                                'theme': 'light'
                                            });
                                        };
                                        jQuery(document).ready(function () {
                                            jQuery('.recaptcha-reload-a').click();
                                        });
                                    </script>
                                    <div class="recaptcha-reload" id="recaptcha1_div">
                                        <?php echo jobsearch_recaptcha('recaptcha1'); ?>
                                    </div>
                                </li>
                                <?php
                            }

                            do_action('jobsearch_registration_extra_fields_end');

                            if ($pass_from_user == 'on') {
                                ?>
                                <li>
                                    <input class="form-control input-lg required" name="pt_user_pass" type="password" placeholder="<?php _e('Password', 'wp-jobsearch'); ?>" />
                                </li>
                                <li>
                                    <input class="form-control input-lg required" name="pt_user_cpass" type="password" placeholder="<?php _e('Confirm Password', 'wp-jobsearch'); ?>" />
                                </li>
                                <?php
                            }
                            ?>
                            <li>
                                <input type="hidden" name="action" value="jobsearch_register_member_submit"/>
                                <button data-id="<?php echo absint($rand_numb) ?>" class="jobsearch-register-submit-btn btn btn-theme btn-lg" data-loading-text="<?php _e('Loading...', 'wp-jobsearch') ?>" type="submit"><?php _e('Sign up', 'wp-jobsearch'); ?></button>
                                <?php
                                jobsearch_terms_and_con_link_txt();
                                ?>
                                <div class="form-loader"></div>

                            </li>
                        </ul>
                        <?php
                        $allow_args = array(
                            'input' => array(
                                'name' => array(),
                                'value' => array(),
                                'type' => array(),
                            ),
                        );
                        ob_start();
                        wp_nonce_field('ajax-login-nonce', 'register-security');
                        $secur_field = ob_get_clean();
                        echo wp_kses($secur_field, $allow_args);
                        ?>
                        <div class="registration-errors"></div>
                    </form>

                    <?php
                //} else {

                    //echo '<div class="alert alert-warning">' . __('Registration is disabled.', 'wp-jobsearch') . '</div>';
                //}
                ?>

            </div>
            <?php
        }
        $html = ob_get_clean();
        echo force_balance_tags($html);
    }

}

// class Jobsearch_Login_Registration_Template 
$Jobsearch_Login_Registration_Template_obj = new Jobsearch_Login_Registration_Template();
