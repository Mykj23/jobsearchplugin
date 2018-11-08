<?php

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 */
class JobSearch_plugin {

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    public static $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public static $type = 'general';
    public static $default_content = 'Hello! I am general email template by {COMPANY_NAME}.';
    public static $codes = array(
        array(
            'var' => '{SITE_NAME}',
            'display_text' => 'Site Name',
            'function_callback' => array('JobSearch_plugin', 'jobsearch_get_site_name'),
        ),
        array(
            'var' => '{ADMIN_EMAIL}',
            'display_text' => 'Admin Email',
            'function_callback' => array('JobSearch_plugin', 'jobsearch_get_admin_email'),
        ),
        array(
            'var' => '{SITE_URL}',
            'display_text' => 'SITE URL',
            'function_callback' => array('JobSearch_plugin', 'jobsearch_get_site_url'),
        ),
        array(
            'var' => '{COPYRIGHT_TEXT}',
            'display_text' => 'COPYRIGHT TEXT',
            'function_callback' => array('JobSearch_plugin', 'jobsearch_get_site_copyright'),
        ),
    );

    public function __construct() {

        self::$version = '1.2.4';

        $this->set_locale();
        do_action('jobsearch_trigger_hook_after_locale');
        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->load_post_types();
        $this->load_shortcodes();
        $this->load_widgets();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     * - JobSearch_plugin_i18n. Defines internationalization functionality.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {

        /**
         * The class responsible for defining options functionality
         * of the plugin.
         */
        if (!class_exists('Careerfy_framework')) {
            if (!class_exists('TGM_Plugin_Activation')) {
                include plugin_dir_path(dirname(__FILE__)) . 'includes/classes/class-tgm-plugin-activation.php';
            }
            include plugin_dir_path(dirname(__FILE__)) . 'envato_setup/envato_setup.php';
            include plugin_dir_path(dirname(__FILE__)) . 'envato_setup/envato_setup_init.php';
        }
        
        include plugin_dir_path(dirname(__FILE__)) . 'includes/classes/class-location-check.php';
        include plugin_dir_path(dirname(__FILE__)) . 'includes/json/currencies.php';
        include plugin_dir_path(dirname(__FILE__)) . 'includes/class-form-fields.php';
        // common functions file
        include plugin_dir_path(dirname(__FILE__)) . 'includes/common-functions/common-functions.php';
        include plugin_dir_path(dirname(__FILE__)) . 'includes/common-functions/job-functions.php';
        include plugin_dir_path(dirname(__FILE__)) . 'includes/common-functions/employer-functions.php';
        include plugin_dir_path(dirname(__FILE__)) . 'includes/common-functions/candidate-functions.php';
        // user dashboard links
        include plugin_dir_path(dirname(__FILE__)) . 'includes/user-account-links.php';
        // visual composer files
        include plugin_dir_path(dirname(__FILE__)) . 'includes/vc-support/vc-actions.php';
        include plugin_dir_path(dirname(__FILE__)) . 'includes/vc-support/vc-shortcodes.php';
        // Mailchimp
        include plugin_dir_path(dirname(__FILE__)) . 'includes/mailchimp/mailchimp-class.php';
        include plugin_dir_path(dirname(__FILE__)) . 'includes/mailchimp/mailchimp-functions.php';
        // Templates
        include plugin_dir_path(dirname(__FILE__)) . 'includes/class-page-templates.php';
        // User Dashboard Functions
        include plugin_dir_path(dirname(__FILE__)) . 'includes/class-user-dashboard.php';
        // User job Functions
        include plugin_dir_path(dirname(__FILE__)) . 'includes/class-user-jobs.php';
        // User admin files
        include plugin_dir_path(dirname(__FILE__)) . 'admin/user/user-custom-fields.php';
        // twitter oauth
        include plugin_dir_path(dirname(__FILE__)) . 'includes/twitter-tweets/twitteroauth.php';

        include plugin_dir_path(dirname(__FILE__)) . 'includes/classes/class-email.php';
        include plugin_dir_path(dirname(__FILE__)) . 'includes/classes/class-job-top-filters.php';
        include plugin_dir_path(dirname(__FILE__)) . 'includes/classes/class-job-filters.php';
        include plugin_dir_path(dirname(__FILE__)) . 'includes/classes/class-employer-filters.php';
        include plugin_dir_path(dirname(__FILE__)) . 'includes/classes/class-employer-top-filters.php';
        include plugin_dir_path(dirname(__FILE__)) . 'includes/classes/class-candidate-filters.php';
        include plugin_dir_path(dirname(__FILE__)) . 'includes/classes/class-candidate-top-filters.php';

        include plugin_dir_path(dirname(__FILE__)) . 'includes/classes/email-templates/class-job-submitted-admin.php';
        include plugin_dir_path(dirname(__FILE__)) . 'includes/classes/email-templates/class-job-approved-to-employer.php';
        include plugin_dir_path(dirname(__FILE__)) . 'includes/classes/email-templates/class-candidate-message-employer.php';
        include plugin_dir_path(dirname(__FILE__)) . 'includes/classes/email-templates/class-job-update-to-employer.php';
        include plugin_dir_path(dirname(__FILE__)) . 'includes/classes/email-templates/class-job-expire-to-employer.php';
        include plugin_dir_path(dirname(__FILE__)) . 'includes/classes/email-templates/class-new-user-register.php';
        include plugin_dir_path(dirname(__FILE__)) . 'includes/classes/email-templates/class-reset-password-request.php';
        include plugin_dir_path(dirname(__FILE__)) . 'includes/classes/email-templates/class-user-password-change.php';
        include plugin_dir_path(dirname(__FILE__)) . 'includes/classes/email-templates/class-user-shortlist-to-candidate.php';
        include plugin_dir_path(dirname(__FILE__)) . 'includes/classes/email-templates/class-user-shortlist-to-employer.php';
        include plugin_dir_path(dirname(__FILE__)) . 'includes/classes/email-templates/class-user-shortlist-for-interview.php';
        include plugin_dir_path(dirname(__FILE__)) . 'includes/classes/email-templates/class-job-applied-to-employer.php';
        include plugin_dir_path(dirname(__FILE__)) . 'includes/classes/email-templates/class-employer-contact-form.php';
        include plugin_dir_path(dirname(__FILE__)) . 'includes/classes/email-templates/class-candidate-contact-form.php';

        // redux frameworks extensions
        include plugin_dir_path(dirname(__FILE__)) . 'admin/redux-ext/loader.php';

        // packages modules
        include plugin_dir_path(dirname(__FILE__)) . 'modules/packages/packages.php';
        // shortlist modules
        include plugin_dir_path(dirname(__FILE__)) . 'modules/shortlist/shortlist.php';
        // job application modules
        include plugin_dir_path(dirname(__FILE__)) . 'modules/job-application/job-application.php';
        // email template modules
        include plugin_dir_path(dirname(__FILE__)) . 'modules/email-templates/email-templates.php';
        // location modules
        include plugin_dir_path(dirname(__FILE__)) . 'modules/locations/locations.php';
        // login register modules
        include plugin_dir_path(dirname(__FILE__)) . 'modules/login-registration/login-registration.php';
        // social login module
        include plugin_dir_path(dirname(__FILE__)) . 'modules/social-login/social-login.php';
        // woocommerce checkout
        include plugin_dir_path(dirname(__FILE__)) . 'modules/woocommerce-checkout/woocommerce-checkout.php';
        //custom fields module
        include plugin_dir_path(dirname(__FILE__)) . 'modules/custom-fields/custom-fields.php';
        //reviews module
        include plugin_dir_path(dirname(__FILE__)) . 'modules/reviews/reviews.php';
        //multiple post thumbnails module
        include plugin_dir_path(dirname(__FILE__)) . 'modules/multi-featured-thumbnails/multi-featured-thumbnails.php';
        //job alerts
        include plugin_dir_path(dirname(__FILE__)) . 'modules/job-alerts/job-alerts.php';
        //import locations
        include plugin_dir_path(dirname(__FILE__)) . 'modules/import-locations/import-locations.php';
        //indeed jobs
        include plugin_dir_path(dirname(__FILE__)) . 'modules/indeed-jobs-import/indeed-jobs.php';
        //ads management
        include plugin_dir_path(dirname(__FILE__)) . 'modules/ads-management/ads-management.php';

        // redux frameworks files
        include plugin_dir_path(dirname(__FILE__)) . 'admin/ReduxFramework/ReduxCore/framework.php';
        include plugin_dir_path(dirname(__FILE__)) . 'admin/ReduxFramework/jobsearch-options/options-config.php';

        //shortcode builder
        include plugin_dir_path(dirname(__FILE__)) . 'includes/shortcode-builder/shortcodes-builder.php';
    }

    /**
     * Load the shortcodes for this plugin.
     *
     * describe shortcodes markup
     *
     * @since    1.0.0
     * @access   public
     */
    public function load_shortcodes() {

        /**
         * The class responsible for loading shortcodes
         * of the plugin.
         */
        include plugin_dir_path(dirname(__FILE__)) . 'shortcodes/vc-shortcodes/jobs-listing-shortcode.php';
        include plugin_dir_path(dirname(__FILE__)) . 'shortcodes/vc-shortcodes/user-job-shortcode.php';
        include plugin_dir_path(dirname(__FILE__)) . 'shortcodes/vc-shortcodes/employer-shortcode.php';
        include plugin_dir_path(dirname(__FILE__)) . 'shortcodes/vc-shortcodes/candidate-shortcode.php';
        include plugin_dir_path(dirname(__FILE__)) . 'shortcodes/vc-shortcodes/advance-search.php';
    }

    /**
     * Load Widgets.
     *
     * Widgets markup
     *
     * @since    1.0.0
     * @access   public
     */
    public function load_widgets() {

        /**
         * The function responsible for Widgets
         * of the plugin.
         */
        include plugin_dir_path(dirname(__FILE__)) . 'includes/widgets/admin/job-widget.php';
        include plugin_dir_path(dirname(__FILE__)) . 'includes/widgets/admin/employer-widget.php';
        include plugin_dir_path(dirname(__FILE__)) . 'includes/widgets/admin/candidate-widget.php';
    }

    /**
     * Load the shortcodes for this plugin.
     *
     * describe post types and metaboxes
     *
     * @since    1.0.0
     * @access   public
     */
    public function load_post_types() {

        /**
         * The class responsible for loading post types
         * of the plugin.
         */
        include plugin_dir_path(dirname(__FILE__)) . 'includes/meta-boxes/ajax-metabox.php';
        include plugin_dir_path(dirname(__FILE__)) . 'includes/post-types/job.php';
        include plugin_dir_path(dirname(__FILE__)) . 'includes/meta-boxes/job-metabox.php';
        include plugin_dir_path(dirname(__FILE__)) . 'includes/post-types/candidate.php';
        include plugin_dir_path(dirname(__FILE__)) . 'includes/meta-boxes/candidate-metabox.php';
        include plugin_dir_path(dirname(__FILE__)) . 'includes/meta-boxes/candidate-multi-fields/candidate-education-fields.php';
        include plugin_dir_path(dirname(__FILE__)) . 'includes/meta-boxes/candidate-multi-fields/candidate-experience-fields.php';
        include plugin_dir_path(dirname(__FILE__)) . 'includes/meta-boxes/candidate-multi-fields/candidate-portfolio-fields.php';
        include plugin_dir_path(dirname(__FILE__)) . 'includes/meta-boxes/candidate-multi-fields/candidate-award-fields.php';
        include plugin_dir_path(dirname(__FILE__)) . 'includes/meta-boxes/candidate-multi-fields/candidate-skill-fields.php';

        include plugin_dir_path(dirname(__FILE__)) . 'includes/post-types/employer.php';
        include plugin_dir_path(dirname(__FILE__)) . 'includes/meta-boxes/employer-metabox.php';
        include plugin_dir_path(dirname(__FILE__)) . 'includes/meta-boxes/employer-multi-fields/employer-team-fields.php';

        include plugin_dir_path(dirname(__FILE__)) . 'includes/post-types/email.php';
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the JobSearch_plugin_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale() {

        $locale = apply_filters('plugin_locale', get_locale(), 'wp-jobsearch');

        load_textdomain('wp-jobsearch', WP_LANG_DIR . '/plugins/wp-jobsearch-' . $locale . '.mo');
        load_plugin_textdomain('wp-jobsearch', false, dirname(dirname(plugin_basename(__FILE__))) . '/languages');
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {

        //
        if (!class_exists('Careerfy_framework')) {
            add_action('tgmpa_register', array($this, 'register_required_plugins'));
        }
        //
        add_action('init', array($this, 'image_sizes'), 10, 0);
        // custom fields
        add_action('admin_menu', array($this, 'jobsearch_job_fields_create_menu'));
        add_action('admin_menu', array($this, 'jobsearch_employer_fields_create_menu'));
        add_action('admin_menu', array($this, 'jobsearch_candidate_fields_create_menu'));

        add_action('admin_menu', array($this, 'jobsearch_email_templates_fields_create_menu'));
        add_action('admin_menu', array($this, 'jobsearch_email_logs_post_type_menu'));

        add_action('admin_enqueue_scripts', array($this, 'admin_style_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'front_style_scripts'), 1);

        // jobs metaboxes
        add_action('add_meta_boxes', 'jobsearch_jobs_settings_meta_boxes');

        // candidate metaboxes
        add_action('add_meta_boxes', 'jobsearch_candidates_settings_meta_boxes');

        // employer metaboxes
        add_action('add_meta_boxes', 'jobsearch_employers_settings_meta_boxes');
    }

    static function jobsearch_job_fields_create_menu() {
        //create new top-level menu
        add_menu_page(esc_html__('Job Custom Fields Settings', 'wp-jobsearch'), esc_html__('Custom Fields', 'wp-jobsearch'), 'administrator', 'jobsearch-job-fields', array('JobSearch_plugin', 'jobsearch_job_fields_settings_page'), '', 31);
    }

    static function jobsearch_job_fields_settings_page() {
        do_action('jobsearch_load_custom_fields', 'job');
    }

    static function jobsearch_candidate_fields_create_menu() {
        //create new top-level menu
        add_submenu_page('jobsearch-job-fields', esc_html__('Candidate Custom Fields Settings', 'wp-jobsearch'), esc_html__('Candidate Custom Fields', 'wp-jobsearch'), 'administrator', 'jobsearch-candidate-fields', array('JobSearch_plugin', 'jobsearch_candidate_fields_settings_page'));
    }

    static function jobsearch_candidate_fields_settings_page() {
        do_action('jobsearch_load_custom_fields', 'candidate');
    }

    static function jobsearch_employer_fields_create_menu() {
        //create new top-level menu
        add_submenu_page('jobsearch-job-fields', esc_html__('Employer Custom Fields Settings', 'wp-jobsearch'), esc_html__('Employer Custom Fields', 'wp-jobsearch'), 'administrator', 'jobsearch-employer-fields', array('JobSearch_plugin', 'jobsearch_employer_fields_settings_page'));
    }

    static function jobsearch_employer_fields_settings_page() {
        do_action('jobsearch_load_custom_fields', 'employer');
    }

    static function jobsearch_email_templates_fields_create_menu() {
        //create new top-level menu
        add_menu_page(esc_html__('Email Templates', 'wp-jobsearch'), esc_html__('Email Templates', 'wp-jobsearch'), 'administrator', 'jobsearch-email-templates-fields', array('JobSearch_plugin', 'jobsearch_email_templates_fields_settings_page'), '', 31);
    }

    static function jobsearch_email_logs_post_type_menu() {
        add_submenu_page('jobsearch-email-templates-fields', esc_html__('Email Logs', 'wp-jobsearch'), esc_html__('Email Logs', 'wp-jobsearch'), 'administrator', 'email-logs', array('post_type_email', 'email_logs_post_type_redirect'));
    }

    static function jobsearch_email_templates_fields_settings_page() {
        do_action('jobsearch_load_email_templates', 'email_templates');
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {

        add_filter('template_include', array($this, 'single_template'));
    }

    public function register_required_plugins() {
        /*
         * Array of plugin arrays. Required keys are name and slug.
         * If the source is NOT from the .org repo, then source is also required.
         */
        $plugins = array();
        // This is an example of how to include a plugin bundled with a theme.
        $plugins[] = array(
            'name' => esc_html__('Wp Jobsearch Demo Data', 'wp-jobsearch'), // The plugin name.
            'slug' => 'wp-jobsearch-demo-data', // The plugin slug (typically the folder name).
            'source' => 'http://careerfy.net/download-plugins/wp-jobsearch-demo-data.zip', // The plugin source.
            'required' => true, // If false, the plugin is only 'recommended' instead of required.
            'version' => '1.0.0', // E.g. 1.0.0. If set, the active plugin must be this version or higher. If the plugin version is higher than the plugin version installed, the user will be notified to update the plugin.
            'force_activation' => false, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch.
            'force_deactivation' => false, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins.
            'external_url' => '', // If set, overrides default API URL and points to an external URL.
            'is_callable' => '', // If set, this callable will be be checked for availability to determine if a plugin is active.
        );

        /*
         * Array of configuration settings. Amend each line as needed.
         *
         * TGMPA will start providing localized text strings soon. If you already have translations of our standard
         * strings available, please help us make TGMPA even better by giving us access to these translations or by
         * sending in a pull-request with .po file(s) with the translations.
         *
         * Only uncomment the strings in the config array if you want to customize the strings.
         */
        $config = array(
            'id' => 'wp-jobsearch', // Unique ID for hashing notices for multiple instances of TGMPA.
            'default_path' => '', // Default absolute path to bundled plugins.
            'menu' => 'tgmpa-install-plugins', // Menu slug.
            'has_notices' => true, // Show admin notices or not.
            'dismissable' => true, // If false, a user cannot dismiss the nag message.
            'dismiss_msg' => '', // If 'dismissable' is false, this message will be output at top of nag.
            'is_automatic' => false, // Automatically activate plugins after installation or not.
            'message' => '', // Message to output right before the plugins table.
            'strings' => array(
                'page_title' => esc_html__('Install Required Plugins', 'wp-jobsearch'),
                'menu_title' => esc_html__('Install Plugins', 'wp-jobsearch'),
                /* translators: %s: plugin name. */
                'installing' => esc_html__('Installing Plugin: %s', 'wp-jobsearch'),
                /* translators: %s: plugin name. */
                'updating' => esc_html__('Updating Plugin: %s', 'wp-jobsearch'),
                'oops' => esc_html__('Something went wrong with the plugin API.', 'wp-jobsearch'),
                'notice_can_install_required' => _n_noop(
                        /* translators: 1: plugin name(s). */
                        'This theme requires the following plugin: %1$s.', 'This theme requires the following plugins: %1$s.', 'wp-jobsearch'
                ),
                'notice_can_install_recommended' => _n_noop(
                        /* translators: 1: plugin name(s). */
                        'This theme recommends the following plugin: %1$s.', 'This theme recommends the following plugins: %1$s.', 'wp-jobsearch'
                ),
                'notice_ask_to_update' => _n_noop(
                        /* translators: 1: plugin name(s). */
                        'The following plugin needs to be updated to its latest version to ensure maximum compatibility with this theme: %1$s.', 'The following plugins need to be updated to their latest version to ensure maximum compatibility with this theme: %1$s.', 'wp-jobsearch'
                ),
                'notice_ask_to_update_maybe' => _n_noop(
                        /* translators: 1: plugin name(s). */
                        'There is an update available for: %1$s.', 'There are updates available for the following plugins: %1$s.', 'wp-jobsearch'
                ),
                'notice_can_activate_required' => _n_noop(
                        /* translators: 1: plugin name(s). */
                        'The following required plugin is currently inactive: %1$s.', 'The following required plugins are currently inactive: %1$s.', 'wp-jobsearch'
                ),
                'notice_can_activate_recommended' => _n_noop(
                        /* translators: 1: plugin name(s). */
                        'The following recommended plugin is currently inactive: %1$s.', 'The following recommended plugins are currently inactive: %1$s.', 'wp-jobsearch'
                ),
                'install_link' => _n_noop(
                        'Begin installing plugin', 'Begin installing plugins', 'wp-jobsearch'
                ),
                'update_link' => _n_noop(
                        'Begin updating plugin', 'Begin updating plugins', 'wp-jobsearch'
                ),
                'activate_link' => _n_noop(
                        'Begin activating plugin', 'Begin activating plugins', 'wp-jobsearch'
                ),
                'return' => esc_html__('Return to Required Plugins Installer', 'wp-jobsearch'),
                'plugin_activated' => esc_html__('Plugin activated successfully.', 'wp-jobsearch'),
                'activated_successfully' => esc_html__('The following plugin was activated successfully:', 'wp-jobsearch'),
                /* translators: 1: plugin name. */
                'plugin_already_active' => esc_html__('No action taken. Plugin %1$s was already active.', 'wp-jobsearch'),
                /* translators: 1: plugin name. */
                'plugin_needs_higher_version' => esc_html__('Plugin not activated. A higher version of %s is needed for this theme. Please update the plugin.', 'wp-jobsearch'),
                /* translators: 1: dashboard link. */
                'complete' => esc_html__('All plugins installed and activated successfully. %1$s', 'wp-jobsearch'),
                'dismiss' => esc_html__('Dismiss this notice', 'wp-jobsearch'),
                'notice_cannot_install_activate' => esc_html__('There are one or more required or recommended plugins to install, update or activate.', 'wp-jobsearch'),
                'contact_admin' => esc_html__('Please contact the administrator of this site for help.', 'wp-jobsearch'),
                'nag_type' => '', // Determines admin notice type - can only be one of the typical WP notice classes, such as 'updated', 'update-nag', 'notice-warning', 'notice-info' or 'error'. Some of which may not work as expected in older WP versions.
            ),
        );

        tgmpa($plugins, $config);
    }

    /**
     * Register all of the single pages
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    public function single_template($single_template) {
        global $post;
        if (is_single()) {
            if (get_post_type() == 'job') {
                $single_template = plugin_dir_path(__FILE__) . 'single-pages/single-job.php';
            }
            if (get_post_type() == 'candidate') {
                $single_template = plugin_dir_path(__FILE__) . 'single-pages/single-candidate.php';
            }
            if (get_post_type() == 'employer') {
                $single_template = plugin_dir_path(__FILE__) . 'single-pages/single-employer.php';
            }
        }
        return $single_template;
    }

    /**
     * Register all of the front styles and scripts
     * of the plugin.
     *
     * @since    1.0.0
     * @access   public
     */
    public function front_style_scripts() {
        global $jobsearch_plugin_options, $careerfy_framework_options, $sitepress;

        $admin_ajax_url = admin_url('admin-ajax.php');
        if (function_exists('icl_object_id')) {
            $lang_code = $sitepress->get_current_language();
            $admin_ajax_url = add_query_arg(array('lang' => $lang_code), $admin_ajax_url);
        }

        $careerfy_theme_color = isset($careerfy_framework_options['careerfy-main-color']) && $careerfy_framework_options['careerfy-main-color'] != '' ? $careerfy_framework_options['careerfy-main-color'] : '#13b5ea';
        $google_api_key = isset($jobsearch_plugin_options['jobsearch-google-api-key']) ? $jobsearch_plugin_options['jobsearch-google-api-key'] : '';
        wp_enqueue_style('fullcalendar', jobsearch_plugin_get_url('css/fullcalendar.css'), array(), JobSearch_plugin::get_version());
        // required for frontend embading
        wp_enqueue_style('fancybox', jobsearch_plugin_get_url('css/fancybox.css'), array(), JobSearch_plugin::get_version());
        wp_enqueue_style('wp-jobsearch-flaticon', jobsearch_plugin_get_url('icon-picker/css/flaticon.css'), array(), JobSearch_plugin::get_version());
        wp_enqueue_style('wp-jobsearch-font-awesome', jobsearch_plugin_get_url('icon-picker/css/font-awesome.css'), array(), JobSearch_plugin::get_version());
        wp_enqueue_style('wp-jobsearch-selectize-def', jobsearch_plugin_get_url('css/selectize.default.css'), array(), JobSearch_plugin::get_version());
        wp_enqueue_style('wp-jobsearch-css', jobsearch_plugin_get_url('css/plugin.css'), array(), JobSearch_plugin::get_version());
        wp_enqueue_style('jobsearch-color-style', jobsearch_plugin_get_url('css/color.css'), array(), JobSearch_plugin::get_version());
        wp_enqueue_style('jobsearch-morris', jobsearch_plugin_get_url('css/morris.css'), array(), JobSearch_plugin::get_version());
        // responsive
        wp_enqueue_style('plugin-responsive-styles', jobsearch_plugin_get_url('css/plugin-responsive.css'), array(), JobSearch_plugin::get_version());
        // rtl
        if (is_rtl()) {
            wp_enqueue_style('plugin-rtl-styles', jobsearch_plugin_get_url('css/plugin-rtl.css'), array(), JobSearch_plugin::get_version());
        }

        wp_enqueue_script('jobsearch-plugin-scripts', jobsearch_plugin_get_url('js/jobsearch-plugin.js'), array('jquery'), JobSearch_plugin::get_version(), true);
        // Localize the script
        $jobsearch_plugin_arr = array(
            'plugin_url' => jobsearch_plugin_get_url(),
            'ajax_url' => $admin_ajax_url,
            'careerfy_theme_color' => $careerfy_theme_color,
            'com_img_size' => esc_html__('Image size should not greater than 1 MB.', 'wp-jobsearch'),
            'com_file_size' => esc_html__('File size should not greater than 1 MB.', 'wp-jobsearch'),
            'cv_file_types' => esc_html__('Suitable files are .doc,.docx,.pdf', 'wp-jobsearch'),
            'com_word_title' => esc_html__('Title', 'wp-jobsearch'),
            'see_less_txt' => esc_html__('- see less', 'wp-jobsearch'),
            'see_more_txt' => esc_html__('+ see more', 'wp-jobsearch'),
            'com_word_description' => esc_html__('Description', 'wp-jobsearch'),
            'com_word_save' => esc_html__('Save', 'wp-jobsearch'),
            'error_msg' => esc_html__('There is some problem.', 'wp-jobsearch'),
            'shortlisted_str' => esc_html__('Shortlisted', 'wp-jobsearch'),
            'select_sector' => esc_html__('Select Sector', 'wp-jobsearch'),
            'loading' => esc_html__('Loading...', 'wp-jobsearch'),
            'accpt_terms_cond' => esc_html__('Please accept our terms and conditions.', 'wp-jobsearch'),
            'var_address_str' => esc_html__('Address', 'wp-jobsearch'),
            'var_other_locs_str' => esc_html__('Other Locations', 'wp-jobsearch'),
        );

        wp_localize_script('jobsearch-plugin-scripts', 'jobsearch_plugin_vars', $jobsearch_plugin_arr);
        wp_enqueue_script('jobsearch-plugin-scripts', jobsearch_plugin_get_url('js/jobsearch-plugin.js'), array('jquery'), JobSearch_plugin::get_version(), true);

        $user_dashboard_page = isset($jobsearch_plugin_options['user-dashboard-template-page']) ? $jobsearch_plugin_options['user-dashboard-template-page'] : '';
        $user_dashboard_page = jobsearch__get_post_id($user_dashboard_page, 'page');
        $dashboard_page_url = jobsearch_wpml_lang_page_permalink($user_dashboard_page, 'page'); //get_permalink($user_dashboard_page);

        $multiple_cv_files_allow = isset($jobsearch_plugin_options['multiple_cv_uploads']) ? $jobsearch_plugin_options['multiple_cv_uploads'] : '';
        $max_portfolio_allow = isset($jobsearch_plugin_options['max_gal_imgs_allow']) && $jobsearch_plugin_options['max_gal_imgs_allow'] > 0 ? $jobsearch_plugin_options['max_gal_imgs_allow'] : 5;
        wp_register_script('jobsearch-user-dashboard', jobsearch_plugin_get_url('js/jobsearch-dashboard.js'), array(), JobSearch_plugin::get_version(), true);
        $jobsearch_plugin_arr = array(
            'plugin_url' => jobsearch_plugin_get_url(),
            'ajax_url' => $admin_ajax_url,
            'dashboard_url' => $dashboard_page_url,
            'multiple_cvs_allow' => $multiple_cv_files_allow,
            'max_portfolio_allow' => $max_portfolio_allow,
            'max_portfolio_allow_msg' => sprintf(esc_html__('You can upload upto "%s" portfolio files only.', 'wp-jobsearch'), $max_portfolio_allow),
            'com_img_size' => esc_html__('Image size should not greater than 1 MB.', 'wp-jobsearch'),
            'com_file_size' => esc_html__('File size should not greater than 1 MB.', 'wp-jobsearch'),
            'cv_file_types' => esc_html__('Suitable files are .doc,.docx,.pdf', 'wp-jobsearch'),
            'error_msg' => esc_html__('There is some problem.', 'wp-jobsearch'),
            'fill_nec_fields' => esc_html__('Please fill required fields.', 'wp-jobsearch'),
            'del_prof_txt' => esc_html__('Are you sure! You want to delete your profile.', 'wp-jobsearch'),
        );
        wp_localize_script('jobsearch-user-dashboard', 'jobsearch_dashboard_vars', $jobsearch_plugin_arr);

        $job_title_max_len = isset($jobsearch_plugin_options['job_title_length']) && $jobsearch_plugin_options['job_title_length'] > 0 ? $jobsearch_plugin_options['job_title_length'] : 1000;
        $job_desc_max_len = isset($jobsearch_plugin_options['job_desc_length']) && $jobsearch_plugin_options['job_desc_length'] > 0 ? $jobsearch_plugin_options['job_desc_length'] : 5000;
        $max_number_of_attachments = isset($jobsearch_plugin_options['number_of_attachments']) && $jobsearch_plugin_options['number_of_attachments'] > 0 ? $jobsearch_plugin_options['number_of_attachments'] : 5;
        $max_attachment_size = isset($jobsearch_plugin_options['attach_file_size']) && $jobsearch_plugin_options['attach_file_size'] > 0 ? $jobsearch_plugin_options['attach_file_size'] : 1024;
        $job_attachment_types = isset($jobsearch_plugin_options['job_attachment_types']) && !empty($jobsearch_plugin_options['job_attachment_types']) ? $jobsearch_plugin_options['job_attachment_types'] : array('application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/pdf');
        $job_attachment_types_str = implode('|', $job_attachment_types);
        wp_register_script('jobsearch-user-job-posting', jobsearch_plugin_get_url('js/jobsearch-job-posting.js'), array(), JobSearch_plugin::get_version(), true);
        $jobsearch_plugin_arr = array(
            'plugin_url' => jobsearch_plugin_get_url(),
            'ajax_url' => $admin_ajax_url,
            'dashboard_url' => $dashboard_page_url,
            'error_msg' => esc_html__('There is some problem.', 'wp-jobsearch'),
            'blank_field_msg' => esc_html__('This field should not be blank.', 'wp-jobsearch'),
            'title_len_exceed_msg' => sprintf(esc_html__('Title length should not be exceeds from %s characters.', 'wp-jobsearch'), $job_title_max_len),
            'title_len_less_msg' => esc_html__('Title length should be greater than 1 characters.', 'wp-jobsearch'),
            'title_txt_cont_msg' => esc_html__('Title field can contain only alphanumeric characters, underscore(_), dash(-) and space.', 'wp-jobsearch'),
            'desc_len_exceed_msg' => sprintf(esc_html__('Description length should not be exceeds from %s characters.', 'wp-jobsearch'), $job_desc_max_len),
            'desc_len_exceed_num' => $job_desc_max_len,
            'file_type_error' => esc_html__('This file format is not allowed.', 'wp-jobsearch'),
            'file_size_error' => sprintf(esc_html__('Your file is too large in size. Max size allowed is %s kb', 'wp-jobsearch'), $max_attachment_size),
            'job_files_mime_types' => $job_attachment_types_str,
            'job_files_max_size' => $max_attachment_size,
            'job_num_files_allow' => $max_number_of_attachments,
        );
        wp_localize_script('jobsearch-user-job-posting', 'jobsearch_posting_vars', $jobsearch_plugin_arr);

        wp_enqueue_script('jobsearch-plugin-common', jobsearch_plugin_get_url('js/jobsearch-common.js'), array('jquery'), JobSearch_plugin::get_version(), true);
        wp_enqueue_script('fancybox-pack', jobsearch_plugin_get_url('js/fancybox.pack.js'), array(), JobSearch_plugin::get_version(), true);
        wp_enqueue_script('jobsearch-selectize', jobsearch_plugin_get_url('js/selectize.min.js'), array(), JobSearch_plugin::get_version(), true);
        wp_register_script('isotope-min', jobsearch_plugin_get_url('js/isotope.min.js'), array(), JobSearch_plugin::get_version(), true);
        wp_enqueue_script('moment', jobsearch_plugin_get_url('js/moment.min.js'), array(), JobSearch_plugin::get_version(), true);
        wp_enqueue_script('fullcalendar', jobsearch_plugin_get_url('js/fullcalendar.min.js'), array(), JobSearch_plugin::get_version(), true);
        wp_register_script('jobsearch-map-infobox', jobsearch_plugin_get_url('js/map-infobox.js'), array(), JobSearch_plugin::get_version(), true);
        wp_register_script('jobsearch-google-map', 'https://maps.googleapis.com/maps/api/js?key=' . $google_api_key . '&libraries=places', array(), JobSearch_plugin::get_version(), true);
        wp_register_script('jobsearch-map-markerclusterer', jobsearch_plugin_get_url('js/markerclusterer.js'), array(), JobSearch_plugin::get_version(), true);
        wp_register_script('jobsearch-location-autocomplete', jobsearch_plugin_get_url('js/jquery.location-autocomplete.js'), array(), JobSearch_plugin::get_version(), true);
        wp_register_script('jobsearch_google_recaptcha', 'https://www.google.com/recaptcha/api.js?onload=jobsearch_multicap_all_functions&amp;render=explicit', array(), JobSearch_plugin::get_version(), true);
        wp_register_script('jobsearch-addthis', 'https://s7.addthis.com/js/250/addthis_widget.js', array(), JobSearch_plugin::get_version(), true);
        wp_register_script('jobsearch-search-box-sugg', jobsearch_plugin_get_url('js/search-box-autocomplete.js'), array(), JobSearch_plugin::get_version(), true);
        wp_enqueue_style('datetimepicker-style', jobsearch_plugin_get_url('css/jquery.datetimepicker.css'), array(), JobSearch_plugin::get_version());
        wp_enqueue_script('datetimepicker-script', jobsearch_plugin_get_url('js/jquery.datetimepicker.full.min.js'), array('jquery'), JobSearch_plugin::get_version(), true);
        wp_enqueue_script('jquery-ui', jobsearch_plugin_get_url('admin/js/jquery-ui.js'), array(), JobSearch_plugin::get_version(), false);
        wp_enqueue_script('jobsearch-job-functions-script', jobsearch_plugin_get_url('js/job-functions.js'), array('jquery'), JobSearch_plugin::get_version(), true);
        wp_register_script('jobsearch-job-lists-map', jobsearch_plugin_get_url('js/job-listing-map.js'), array('jquery'), JobSearch_plugin::get_version(), true);
        wp_register_script('jobsearch-employer-lists-map', jobsearch_plugin_get_url('js/employer-listing-map.js'), array('jquery'), JobSearch_plugin::get_version(), true);
        wp_register_script('jobsearch-candidate-lists-map', jobsearch_plugin_get_url('js/candidate-listing-map.js'), array('jquery'), JobSearch_plugin::get_version(), true);
        wp_enqueue_script('jobsearch-employer-functions-script', jobsearch_plugin_get_url('js/employer-functions.js'), array('jquery'), JobSearch_plugin::get_version(), true);
        wp_enqueue_script('jobsearch-candidate-functions-script', jobsearch_plugin_get_url('js/candidate-functions.js'), array('jquery'), JobSearch_plugin::get_version(), true);
        wp_enqueue_script('jobsearch-morris', jobsearch_plugin_get_url('js/morris.js'), array(), JobSearch_plugin::get_version(), true);
        wp_enqueue_script('jobsearch-raphael', jobsearch_plugin_get_url('js/raphael-min.js'), array(), JobSearch_plugin::get_version(), true);

        wp_register_script('jobsearch-progressbar', jobsearch_plugin_get_url('js/progressbar.js'), array(), JobSearch_plugin::get_version(), true);
        wp_register_script('jobsearch-circle-progressbar', jobsearch_plugin_get_url('js/progressbar.min.js'), array(), JobSearch_plugin::get_version(), true);
        wp_enqueue_style('jobsearch-tag-it', jobsearch_plugin_get_url('css/jquery.tagit.css'), array(), JobSearch_plugin::get_version());
        wp_register_script('jobsearch-tag-it', jobsearch_plugin_get_url('js/tag-it.js'), array(), JobSearch_plugin::get_version(), true);
    }

    /**
     * Register all of the admin styles and scripts
     * of the plugin.
     *
     * @since    1.0.0
     * @access   public
     */
    public function admin_style_scripts() {
        global $jobsearch_plugin_options, $sitepress;
        wp_enqueue_style('wp-color-picker');

        $admin_ajax_url = admin_url('admin-ajax.php');
        if (function_exists('icl_object_id')) {
            $lang_code = $sitepress->get_current_language();
            $admin_ajax_url = add_query_arg(array('lang' => $lang_code), $admin_ajax_url);
        }

        $google_api_key = isset($jobsearch_plugin_options['jobsearch-google-api-key']) ? $jobsearch_plugin_options['jobsearch-google-api-key'] : '';

        wp_enqueue_style('font-awesome', jobsearch_plugin_get_url('icon-picker/css/font-awesome.css'), array(), JobSearch_plugin::get_version());
        wp_enqueue_style('fonticonpicker', jobsearch_plugin_get_url('icon-picker/font/jquery.fonticonpicker.min.css'), array(), JobSearch_plugin::get_version());
        wp_enqueue_style('wp-jobsearch-flaticon', jobsearch_plugin_get_url('icon-picker/css/flaticon.css'), array(), JobSearch_plugin::get_version());
        wp_enqueue_style('fonticonpicker.bootstrap', jobsearch_plugin_get_url('icon-picker/theme/bootstrap-theme/jquery.fonticonpicker.bootstrap.css'), array(), JobSearch_plugin::get_version());
        wp_enqueue_style('wp-jobsearch-datetimepicker', jobsearch_plugin_get_url('css/jquery.datetimepicker.css'), array(), JobSearch_plugin::get_version());
        wp_enqueue_style('wp-jobsearch-admin', jobsearch_plugin_get_url('admin/css/admin.css'), array(), JobSearch_plugin::get_version());
        wp_enqueue_style('jobsearch-fancybox-style', jobsearch_plugin_get_url('css/fancybox.css'), array(), JobSearch_plugin::get_version(), true);
        wp_enqueue_script('wp-jobsearch-icons', jobsearch_plugin_get_url('icon-picker/js/icons-load.js'), array('jquery'), JobSearch_plugin::get_version());
        // Localize the script
        $jobsearch_icons_arr = array(
            'plugin_url' => jobsearch_plugin_get_url(),
        );
        wp_localize_script('wp-jobsearch-icons', 'wp_jobsearch_icons_vars', $jobsearch_icons_arr);
        wp_enqueue_script('jobsearch-plugin-admin', jobsearch_plugin_get_url('admin/js/admin.js'), array('jquery'), JobSearch_plugin::get_version(), true);
        // Localize the script
        $jobsearch_plugin_arr = array(
            'plugin_url' => jobsearch_plugin_get_url(),
            'ajax_url' => $admin_ajax_url,
            'are_you_sure' => __('Are you sure!', 'wp-jobsearch'),
            'require_fields' => __('Please fill the require fields.', 'wp-jobsearch'),
            'error_msg' => esc_html__('There is some problem.', 'wp-jobsearch'),
        );
        wp_localize_script('jobsearch-plugin-admin', 'jobsearch_plugin_vars', $jobsearch_plugin_arr);
        wp_enqueue_script('jobsearch-plugin-common', jobsearch_plugin_get_url('js/jobsearch-common.js'), array('jquery'), JobSearch_plugin::get_version(), true);
        wp_register_script('jobsearch-fancybox-script', jobsearch_plugin_get_url('js/fancybox.pack.js'), array('jquery'), JobSearch_plugin::get_version(), true);
        wp_enqueue_script('fonticonpicker', jobsearch_plugin_get_url('icon-picker/js/jquery.fonticonpicker.min.js'), array(), JobSearch_plugin::get_version(), true);
        wp_enqueue_script('jqueryui', jobsearch_plugin_get_url('admin/js/jquery-ui.js'), array('jquery'), JobSearch_plugin::get_version());
        wp_enqueue_script('wp-jobsearch-datetimepicker', jobsearch_plugin_get_url('js/jquery.datetimepicker.full.min.js'), array(), JobSearch_plugin::get_version(), true);

        wp_register_script('jobsearch-plugin-custom-multi-meta-fields', jobsearch_plugin_get_url('js/custom-multi-meta-fields.js'), array('jquery'), JobSearch_plugin::get_version(), true);
        wp_register_script('jobsearch-google-map', 'https://maps.googleapis.com/maps/api/js?key=' . $google_api_key . '&libraries=places', array(), JobSearch_plugin::get_version(), true);
        // enqueue style
        // 
        // enqueue scripts
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker-alpha', jobsearch_plugin_get_url('admin/js/wp-color-picker-alpha.min.js'), array('wp-color-picker'), JobSearch_plugin::get_version(), true);
        
        //
        wp_register_script('jobsearch-user-dashboard', jobsearch_plugin_get_url('js/jobsearch-dashboard.js'), array(), JobSearch_plugin::get_version(), true);
        $jobsearch_plugin_arr = array(
            'plugin_url' => jobsearch_plugin_get_url(),
            'ajax_url' => $admin_ajax_url,
            'dashboard_url' => admin_url('post.php'),
            'com_img_size' => esc_html__('Image size should not greater than 1 MB.', 'wp-jobsearch'),
            'com_file_size' => esc_html__('File size should not greater than 1 MB.', 'wp-jobsearch'),
            'cv_file_types' => esc_html__('Suitable files are .doc,.docx,.pdf', 'wp-jobsearch'),
            'error_msg' => esc_html__('There is some problem.', 'wp-jobsearch'),
            'fill_nec_fields' => esc_html__('Please fill required fields.', 'wp-jobsearch'),
            'del_prof_txt' => esc_html__('Are you sure! You want to delete your profile.', 'wp-jobsearch'),
        );
        wp_localize_script('jobsearch-user-dashboard', 'jobsearch_dashboard_vars', $jobsearch_plugin_arr);
    }

    /**
     * Register all image sizes required
     * for the plugin.
     *
     * @since    1.0.0
     * @access   public
     */
    public function image_sizes() {
        add_image_size('jobsearch-job-medium', 267, 258, true); // posts for team grid and medium
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public static function get_version() {
        return self::$version;
    }

    public function email_template_settings_callback($email_template_options) {
        $email_template_options['types'][] = self::$type;
        $email_template_options['templates']['general'] = self::$default_content;
        $email_template_options['variables']['General'] = self::$codes;

        return $email_template_options;
    }

    public static function jobsearch_get_site_name() {
        return get_bloginfo('name');
    }

    public static function jobsearch_get_admin_email() {
        return get_bloginfo('admin_email');
    }

    public static function jobsearch_get_site_url() {
        return get_bloginfo('url');
    }

    public static function jobsearch_get_site_copyright() {
        global $careerfy_framework_options;
        $copyright_text = isset($careerfy_framework_options['careerfy-footer-copyright-text']) ? $careerfy_framework_options['careerfy-footer-copyright-text'] : '';
        if ($copyright_text == '') {
            $copyright_text = '&copy; ' . get_bloginfo('name') . ' ' . date('Y') . '.';
        }
        return $copyright_text;
    }

    public static function jobsearch_replace_variables($template, $variables) {
        // Add general variables to the list
        $variables = array_merge(self::$codes, $variables);
        foreach ($variables as $key => $variable) {
            $callback_exists = false;
            // Check if function/method exists.
            if (is_array($variable['function_callback'])) { // If it is a method of a class.
                $callback_exists = method_exists($variable['function_callback'][0], $variable['function_callback'][1]);
            } else { // If it is a function.
                $callback_exists = function_exists($variable['function_callback']);
            }
            // Substitute values in place of tags if callback exists.
            if (true == $callback_exists) {
                // Make a call to callback to get value.                
                $value = call_user_func($variable['function_callback']);
                // If we have some value to substitute then use that.
                if (false != $value) {
                    $template = str_replace($variable['var'], $value, $template);
                }
            }
        }
        return $template;
    }

    public static function get_template($email_template_index, $codes, $default_content) {

        global $sitepress;
        $lang_code = '';
        if (function_exists('icl_object_id')) {
            $lang_code = $sitepress->get_current_language();
        }

        $email_template = '';
        $field_db_slug = "jobsearch_email_templates";
        $email_all_templates_saved_data = get_option($field_db_slug);
        $from = isset($email_all_templates_saved_data['jobsearch_email_template_sender_email']) ? $email_all_templates_saved_data['jobsearch_email_template_sender_email'] : '';
        $from_name = isset($email_all_templates_saved_data['jobsearch_email_template_sender_name']) ? $email_all_templates_saved_data['jobsearch_email_template_sender_name'] : '';
        $email_type = isset($email_all_templates_saved_data['jobsearch_email_template_email_send_as']) ? $email_all_templates_saved_data['jobsearch_email_template_email_send_as'] : '';

        if (function_exists('icl_object_id')) {
            $temp_trnaslated = get_option('jobsearch_translate_email_templates');
            if (isset($temp_trnaslated['global_settings']['lang_' . $lang_code]['sender_name'])) {
                $from_name = $temp_trnaslated['global_settings']['lang_' . $lang_code]['sender_name'];
            }
        }

        $email_all_templates_saved_data = $email_all_templates_saved_data['jobsearch_email_template'];
        $jh_from = '';
        $template_data = array('subject' => '', 'from' => $from, 'from_name' => $from_name, 'recipients' => '', 'switch' => '', 'email_type' => $email_type, 'email_template' => '');
        // Check if there is a template select else go with default template. 
        $selected_saved_template = isset($email_all_templates_saved_data[$email_template_index]) ? ($email_all_templates_saved_data[$email_template_index]) : '';
        if (!empty($selected_saved_template)) {
            // Check if a temlate selected else default template is used.
            if (count($selected_saved_template) > 0) {
                // $selected_saved_template = get_post($selected_saved_template);
                if ($selected_saved_template != null) {
                    $email_template = isset($selected_saved_template['content']) ? $selected_saved_template['content'] : '';
                    $template_data['subject'] = isset($selected_saved_template['subject']) ? $selected_saved_template['subject'] : '';
                    $template_data['recipients'] = isset($selected_saved_template['recipients']) ? $selected_saved_template['recipients'] : '';
                    $template_data['switch'] = isset($selected_saved_template['switch']) ? $selected_saved_template['switch'] : '';
                }
            } else {
                // Get default template.
                $email_template = $default_content;
                $template_data['switch'] = 1;
            }
        } else {
            $email_template = $default_content;
            $template_data['switch'] = 1;
        }

        $email_template = str_replace(array('\"'), array('"'), $email_template);
        $email_template = jobsearch_remove_extra_slashes($email_template);

        $email_template = JobSearch_plugin::jobsearch_replace_variables($email_template, $codes);
        $template_data['email_template'] = $email_template;
        return $template_data;
    }

}
