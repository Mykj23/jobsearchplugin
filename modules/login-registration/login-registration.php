<?php

/*
  Class : Login_Registration
 */


// this is an include only WP file
if (!defined('ABSPATH')) {
    die;
}

// main plugin class
class Jobsearch_Login_Registration {

    // hook things up
    public function __construct() {
        add_action('vc_before_init', array($this, 'jobsearch_vc_login_registration_shortcode'), 1); // for vc shortcode
        add_action('wp_enqueue_scripts', array($this, 'front_enqueue_scripts'), 53);
        $this->load_files();

        add_filter('jobsearch_login_settings_section', array($this, 'login_general_settings'), 10, 1);

        //
        add_filter('jobsearch_templates_list_set', array($this, 'login_template'), 10, 1);
        add_filter('jobsearch_template_page_file', array($this, 'login_template_page_file'), 10, 1);

        add_action('init', array($this, 'auto_generate_user_login_page'), 3, 0);
        add_action('init', array($this, 'auto_translated_login_pages'), 15, 0);
    }

    // login default template
    public function login_template($templates = array()) {
        $templates['user-login-template.php'] = __('User Login', 'wp-jobsearch');
        return $templates;
    }

    // login default template
    public function login_template_page_file($template = '') {
        global $post;
        if (isset($post->ID) && 'user-login-template.php' === get_post_meta($post->ID, '_wp_page_template', true)) {

            $file = jobsearch_plugin_get_path('modules/login-registration/templates/' . get_post_meta($post->ID, '_wp_page_template', true));
            if (file_exists($file)) {
                $template = $file;
            }
        }
        return $template;
    }

    public function login_general_settings($section_settings = array()) {

        $all_page = array();
        $args = array(
            'sort_order' => 'asc',
            'sort_column' => 'post_title',
            'hierarchical' => 1,
            'exclude' => '',
            'include' => '',
            'meta_key' => '',
            'meta_value' => '',
            'authors' => '',
            'child_of' => 0,
            'parent' => -1,
            'exclude_tree' => '',
            'number' => '',
            'offset' => 0,
            'post_type' => 'page',
            'post_status' => 'publish'
        );
        $pages = get_pages($args);
        if (!empty($pages)) {
            $all_page[''] = __('Select Page', 'wp-jobsearch');
            foreach ($pages as $page) {
                if (is_object($page)) {
                    $all_page[$page->post_name] = $page->post_title;
                }
            }
        }

        $theme_menus = array();
        $theme_menus_locs = get_nav_menu_locations();
        if (!empty($theme_menus_locs)) {
            foreach ($theme_menus_locs as $menu_loc_slug => $menu_id) {
                $menu_obj = wp_get_nav_menu_object($menu_id);
                if (is_object($menu_obj)) {
                    $theme_menus[$menu_loc_slug] = $menu_obj->name;
                }
            }
        }

        $show_demo_users = false;
        if (is_user_logged_in()) {
            $cur_user_obj = wp_get_current_user();
            $cur_user_login = $cur_user_obj->user_login;
            if ($cur_user_login == 'jobsearch-admin') {
                $show_demo_users = true;
            }
        }

        $login_settings_arr = array();

        $login_settings_arr[] = array(
            'id' => 'user-login-template-page',
            'type' => 'select',
            'title' => __('User Login Page', 'wp-jobsearch'),
            'subtitle' => __('Select User Login Page.', 'wp-jobsearch'),
            'desc' => '',
            'options' => $all_page,
            'default' => '',
        );
        $login_settings_arr[] = array(
            'id' => 'user-login-dashboard-links',
            'type' => 'button_set',
            'title' => __('Login/Dashboard Links', 'wp-jobsearch'),
            'subtitle' => __('Enable/Disable User Login links in Menu.', 'wp-jobsearch'),
            'desc' => __('It will show user login/register links in a selected Menu. For example in Top Navigation of your theme.', 'wp-jobsearch'),
            'options' => array(
                'on' => __('On', 'wp-jobsearch'),
                'off' => __('Off', 'wp-jobsearch'),
            ),
            'default' => 'off',
        );
        $login_settings_arr[] = array(
            'id' => 'user-login-links-menu',
            'type' => 'select',
            'title' => __('Select Login Links Menu', 'wp-jobsearch'),
            'subtitle' => __('Select a Menu in which you want to show User Login links.', 'wp-jobsearch'),
            'desc' => __('Note: Menu must be created and assigned a position', 'wp-jobsearch'),
            'options' => $theme_menus,
            'default' => '',
        );
        if ($show_demo_users) {
            $login_settings_arr[] = array(
                'id' => 'demo-user-login-sec',
                'type' => 'section',
                'title' => __('Demo User Login', 'wp-jobsearch'),
                'subtitle' => '',
                'indent' => true,
            );
            $login_settings_arr[] = array(
                'id' => 'demo_user_login',
                'type' => 'button_set',
                'title' => __('Demo Users', 'wp-jobsearch'),
                'subtitle' => __('Enable/Disable Demo User Logins.', 'wp-jobsearch'),
                'desc' => __('It will show Demo User Login buttons in login form.', 'wp-jobsearch'),
                'options' => array(
                    'on' => __('On', 'wp-jobsearch'),
                    'off' => __('Off', 'wp-jobsearch'),
                ),
                'default' => 'off',
            );
            $login_settings_arr[] = array(
                'id' => 'demo_user_mod',
                'type' => 'button_set',
                'title' => __('Demo User Modifications', 'wp-jobsearch'),
                'subtitle' => __('Allow Demo Users to update settings.', 'wp-jobsearch'),
                'desc' => '',
                'options' => array(
                    'on' => __('Yes', 'wp-jobsearch'),
                    'off' => __('No', 'wp-jobsearch'),
                ),
                'default' => 'off',
            );
            $login_settings_arr[] = array(
                'id' => 'demo_candidate',
                'type' => 'jobsearch_ajax_users',
                'title' => __('Demo Candidate User', 'wp-jobsearch'),
                'subtitle' => __('Select Demo Candidate User.', 'wp-jobsearch'),
                'user_role' => 'jobsearch_candidate',
                'desc' => '',
                'default' => '',
            );
            $login_settings_arr[] = array(
                'id' => 'demo_employer',
                'type' => 'jobsearch_ajax_users',
                'title' => __('Demo Employer User', 'wp-jobsearch'),
                'subtitle' => __('Select Demo Employer User.', 'wp-jobsearch'),
                'user_role' => 'jobsearch_employer',
                'desc' => '',
                'default' => '',
            );
        }
        $login_settings_arr[] = array(
            'id' => 'login-form-settings-sec',
            'type' => 'section',
            'title' => __('Login Form Settings', 'wp-jobsearch'),
            'subtitle' => '',
            'indent' => true,
        );
        $login_settings_arr[] = array(
            'id' => 'login_register_form',
            'type' => 'button_set',
            'title' => __('Register Form', 'wp-jobsearch'),
            'subtitle' => __('Allow users to register a new account.', 'wp-jobsearch'),
            'desc' => '',
            'options' => array(
                'on' => __('On', 'wp-jobsearch'),
                'off' => __('Off', 'wp-jobsearch'),
            ),
            'default' => 'on',
        );
        $login_settings_arr[] = array(
            'id' => 'login_candidate_register',
            'type' => 'button_set',
            'title' => __('Enable Candidate Registration', 'wp-jobsearch'),
            'subtitle' => __('Allow users to register a candidate account.', 'wp-jobsearch'),
            'desc' => '',
            'options' => array(
                'yes' => __('Yes', 'wp-jobsearch'),
                'no' => __('No', 'wp-jobsearch'),
            ),
            'default' => 'yes',
        );
        $login_settings_arr[] = array(
            'id' => 'login_employer_register',
            'type' => 'button_set',
            'title' => __('Enable Employer Registration', 'wp-jobsearch'),
            'subtitle' => __('Allow users to register an employer account.', 'wp-jobsearch'),
            'desc' => '',
            'options' => array(
                'yes' => __('Yes', 'wp-jobsearch'),
                'no' => __('No', 'wp-jobsearch'),
            ),
            'default' => 'yes',
        );

        $section_settings = array(
            'title' => __('Login Settings', 'wp-jobsearch'),
            'id' => 'login-sec-settings',
            'desc' => '',
            'icon' => 'el el-globe',
            'fields' => $login_settings_arr,
        );
        return apply_filters('jobsearch_social_login_settings', $section_settings);
    }

    public function front_enqueue_scripts() {

        global $sitepress;
        wp_register_script('jobsearch-login-register', jobsearch_plugin_get_url('modules/login-registration/js/login-registration.js'), array('jquery'), '', true);
        // Localize the script
        $admin_ajax_url = admin_url('admin-ajax.php');
        if (function_exists('icl_object_id')) {
            $lang_code = $sitepress->get_current_language();
            $admin_ajax_url = add_query_arg(array('lang' => $lang_code), $admin_ajax_url);
        }
        $jobsearch_login_register_common_arr = array(
            'plugin_url' => jobsearch_plugin_get_url(),
            'ajax_url' => apply_filters('jobsearch_js_file_ajax_url', $admin_ajax_url),
        );
        wp_localize_script('jobsearch-login-register', 'jobsearch_login_register_common_vars', $jobsearch_login_register_common_arr);
        wp_enqueue_script('jobsearch-login-register');
    }

    public function load_files() {
        include plugin_dir_path(dirname(__FILE__)) . 'login-registration/vc-shortcodes/login-registration-shortcode.php';
        include plugin_dir_path(dirname(__FILE__)) . 'login-registration/templates/login-registration-template.php';
        include plugin_dir_path(dirname(__FILE__)) . 'login-registration/templates/login-registration-popup.php';
        include plugin_dir_path(dirname(__FILE__)) . 'login-registration/include/login-registration-submit.php';
    }

    /**
     * Login Registration Form shortcode
     * @return markup
     */
    public function jobsearch_vc_login_registration_shortcode() {

        $attributes = array(
            "name" => esc_html__("Login Registration Form", "wp-jobsearch"),
            "base" => "jobsearch_login_registration",
            "class" => "",
            "category" => esc_html__("Wp JobSearch", "wp-jobsearch"),
            "params" => array(
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__("Title", "wp-jobsearch"),
                    'param_name' => 'login_registration_title',
                    'value' => '',
                    'description' => ''
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__("Register Form", "wp-jobsearch"),
                    'param_name' => 'login_register_form',
                    'value' => array(
                        esc_html__("On", "wp-jobsearch") => 'on',
                        esc_html__("Off", "wp-jobsearch") => 'off',
                    ),
                    'description' => ''
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__("Enable Candidate Registration", "wp-jobsearch"),
                    'param_name' => 'login_candidate_register',
                    'value' => array(
                        esc_html__("Yes", "wp-jobsearch") => 'yes',
                        esc_html__("No", "wp-jobsearch") => 'no',
                    ),
                    'description' => ''
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__("Enable Employer Registration", "wp-jobsearch"),
                    'param_name' => 'login_employer_register',
                    'value' => array(
                        esc_html__("Yes", "wp-jobsearch") => 'yes',
                        esc_html__("No", "wp-jobsearch") => 'no',
                    ),
                    'description' => ''
                ),
            )
        );

        if (function_exists('vc_map')) {
            vc_map($attributes);
        }
    }

    public function auto_translated_login_pages() {
        global $jobsearch_plugin_options, $sitepress;

        if (function_exists('icl_object_id')) {

            $wpml_lang_pages = get_option('jobsearch_wpml_lang_pages_ids');
            $options_pages_ids = (!empty($wpml_lang_pages) ? $wpml_lang_pages : array());

            $login_page_id = isset($jobsearch_plugin_options['user-login-template-page']) ? $jobsearch_plugin_options['user-login-template-page'] : '';
            $login_page_id = jobsearch__get_post_id($login_page_id, 'page');

            if ($login_page_id > 0) {
                $dash_pages_ids = (isset($options_pages_ids['login_pages_ids']) && !empty($options_pages_ids['login_pages_ids']) ? $options_pages_ids['login_pages_ids'] : array());

                $def_trid = $sitepress->get_element_trid($login_page_id);

                $wpml_options = get_option('icl_sitepress_settings');
                $default_lang = isset($wpml_options['default_language']) ? $wpml_options['default_language'] : '';
                $languages = icl_get_languages('skip_missing=0&orderby=code');
                if (is_array($languages) && sizeof($languages) > 0) {
                    foreach ($languages as $lang_code => $language) {
                        if ($default_lang == $lang_code) {
                            continue;
                        }

                        if (!array_key_exists($lang_code . '_page_id', $dash_pages_ids)) {
                            $ru_args = array(
                                'post_title' => $lang_code . ' ' . wp_strip_all_tags('User Login'),
                                'post_content' => '',
                                'post_status' => 'publish',
                                'post_type' => 'page'
                            );
                            //creating post with arguments above and assign post id to $ru_post_id
                            $ru_post_id = wp_insert_post($ru_args);
                            $options_pages_ids['login_pages_ids'][$lang_code . '_page_id'] = $ru_post_id;
                            update_post_meta($ru_post_id, '_wp_page_template', 'user-login-template.php');

                            //change language and trid of second post to match russian and default post trid
                            $sitepress->set_element_language_details($ru_post_id, 'post_page', $def_trid, $lang_code);
                        }
                    }
                }
                //
            }
            //

            update_option('jobsearch_wpml_lang_pages_ids', $options_pages_ids);
        }
    }

    public function auto_generate_user_login_page() {
        global $jobsearch_plugin_options, $JobsearchReduxFramework;

        $user_login_page_id = isset($jobsearch_plugin_options['user-login-template-page']) ? $jobsearch_plugin_options['user-login-template-page'] : '';

        $page_path = 'user-login';
        $user_login_page = get_page_by_path($page_path, OBJECT, 'page');

        if ($user_login_page_id == '' && empty($user_login_page)) {
            $page_args = array(
                'post_title' => wp_strip_all_tags('User Login'),
                'post_type' => 'page',
                'post_status' => 'publish',
                'post_content' => '',
            );
            // Insert the post into the database
            $page_id = wp_insert_post($page_args);

            update_post_meta($page_id, '_wp_page_template', 'user-login-template.php');

            $JobsearchReduxFramework->ReduxFramework->set('user-login-template-page', $page_path);
        } else if ($user_login_page_id == '' && is_object($user_login_page)) {
            $page_id = $user_login_page->ID;

            update_post_meta($page_id, '_wp_page_template', 'user-login-template.php');

            $JobsearchReduxFramework->ReduxFramework->set('user-login-template-page', $page_path);
        }
    }

}

// class Jobsearch_Login_Registration 
$Jobsearch_Login_Registration_obj = new Jobsearch_Login_Registration();
