<?php
if (!class_exists("ReduxFramework")) {
    return;
}
if (!class_exists("Redux_Framework_options_config")) {

    class Redux_Framework_options_config {

        public $args = array();
        public $sections = array();
        public $theme;
        public $ReduxFramework;

        public function __construct() {
            global $pagenow;

            require_once( ABSPATH . "wp-includes/pluggable.php" );

            add_action('admin_enqueue_scripts', array($this, 'admin_style_scripts'));

            // Just for demo purposes. Not needed per say.
            $this->theme = wp_get_theme();
            // Set the default arguments
            $this->setArguments();

            // Set a few help tabs so you can see how it's done
            $this->setHelpTabs();
            // Create the sections and fields
            $this->setSections();

            if (!isset($this->args['opt_name'])) { // No errors please
                return;
            }

            //
            if (isset($_GET['page']) && $_GET['page'] == 'jobsearch_options') {
                add_action('admin_footer', array($this, 'map_autocomplete_fields'));
            }

            $this->ReduxFramework = new ReduxFramework($this->sections, $this->args);

            add_filter('redux/options/' . $this->args['opt_name'] . '/sections', array($this, 'dynamic_section'));
        }

        public function admin_style_scripts() {
            wp_dequeue_style('jquery-ui');
        }

        /**
          This is a test function that will let you see when the compiler hook occurs.
          It only runs if a field	set with compiler=>true is changed.
         * */
        function compiler_action($options, $css) {
            echo "<h1>The compiler hook has run!";
        }

        /**

          Custom function for filtering the sections array. Good for child themes to override or add to the sections.
          Simply include this function in the child themes functions.php file.

          NOTE: the defined constants for URLs, and directories will NOT be available at this point in a child theme,
          so you must use get_template_directory_uri() if you want to use any of the built in icons

         * */
        function dynamic_section($sections) {

            $sections[] = array(
                'title' => __('Section via hook', 'wp-jobsearch'),
                'desc' => __('<p class="description">This is a section created by adding a filter to the sections array. Can be used by child themes to add/remove sections from the options.</p>', 'wp-jobsearch'),
                'icon' => 'el-icon-paper-clip',
                // Leave this as a blank section, no options just some intro text set above.
                'fields' => array()
            );
            return $sections;
        }

        /**
          Filter hook for filtering the args. Good for child themes to override or add to the args array. Can also be used in other functions.
         * */
        function change_arguments($args) {

            return $args;
        }

        /**
          Filter hook for filtering the default value of any given field. Very useful in development mode.
         * */
        function change_defaults($defaults) {
            $defaults['str_replace'] = "Testing filter hook!";

            return $defaults;
        }

        // Remove the demo link and the notice of integrated demo from the redux-framework plugin
        function remove_demo() {

            // Used to hide the demo mode link from the plugin page. Only used when Redux is a plugin.
            if (class_exists('ReduxFrameworkPlugin')) {
                remove_filter('plugin_row_meta', array(ReduxFrameworkPlugin::get_instance(), 'plugin_meta_demo_mode_link'), null, 2);
            }
            // Used to hide the activation notice informing users of the demo panel. Only used when Redux is a plugin.
            remove_action('admin_notices', array(ReduxFrameworkPlugin::get_instance(), 'admin_notices'));
        }

        public function map_autocomplete_fields() {

            wp_enqueue_script('jobsearch-google-map');
            ?>
            <script type="text/javascript">
                jQuery(document).ready(function () {
                    function jobsearch_map_autocomplete_fields() {
                        var autocomplete_input = document.getElementById('jobsearch-location-default-address');
                        var autocomplete = new google.maps.places.Autocomplete(autocomplete_input);
                    }
                    google.maps.event.addDomListener(window, 'load', jobsearch_map_autocomplete_fields);
                });
            </script>
            <?php
        }

        public function setSections() {
            /**
              Used within different fields. Simply examples. Search for ACTUAL DECLARATION for field examples
             * */
            // Background Patterns Reader
            $sample_patterns_path = ReduxFramework::$_dir . '../sample/patterns/';
            $sample_patterns_url = ReduxFramework::$_url . '../sample/patterns/';
            $sample_patterns = array();
            if (is_dir($sample_patterns_path)) :

                if ($sample_patterns_dir = opendir($sample_patterns_path)) :
                    $sample_patterns = array();
                    while (( $sample_patterns_file = readdir($sample_patterns_dir) ) !== false) {
                        if (stristr($sample_patterns_file, '.png') !== false || stristr($sample_patterns_file, '.jpg') !== false) {
                            $name = explode(".", $sample_patterns_file);
                            $name = str_replace('.' . end($name), '', $sample_patterns_file);
                            $sample_patterns[] = array('alt' => $name, 'img' => $sample_patterns_url . $sample_patterns_file);
                        }
                    }
                endif;
            endif;
            ob_start();
            $ct = wp_get_theme();
            $this->theme = $ct;
            $item_name = $this->theme->get('Name');
            $tags = $this->theme->Tags;
            $screenshot = $this->theme->get_screenshot();
            $class = $screenshot ? 'has-screenshot' : '';
            $customize_title = sprintf(__('Customize &#8220;%s&#8221;', 'wp-jobsearch'), $this->theme->display('Name'));
            ?>
            <div id="current-theme" class="<?php echo esc_attr($class); ?>">

                <h4>
                    <?php echo $this->theme->display('Name'); ?>
                </h4>

                <div>
                    <ul class="theme-info">
                        <li><?php printf(__('By %s', 'wp-jobsearch'), $this->theme->display('Author')); ?></li>
                        <li><?php printf(__('Version %s', 'wp-jobsearch'), $this->theme->display('Version')); ?></li>
                        <li><?php echo '<strong>' . __('Tags', 'wp-jobsearch') . ':</strong> '; ?><?php printf($this->theme->display('Tags')); ?></li>
                    </ul>
                    <p class="theme-description"><?php echo $this->theme->display('Description'); ?></p>
                    <?php
                    if ($this->theme->parent()) {
                        printf(' <p class="howto">' . __('This <a href="%1$s">child theme</a> requires its parent theme, %2$s.') . '</p>', __('http://codex.wordpress.org/Child_Themes', 'wp-jobsearch'), $this->theme->parent()->display('Name'));
                    }
                    ?>

                </div>

            </div>

            <?php
            $item_info = ob_get_contents();

            ob_end_clean();
            $sampleHTML = '';
            if (file_exists(dirname(__FILE__) . '/info-html.html')) {
                /** @global WP_Filesystem_Direct $wp_filesystem  */
                global $wp_filesystem;
                if (empty($wp_filesystem)) {
                    require_once(ABSPATH . '/wp-admin/includes/file.php');
                    WP_Filesystem();
                }
                $sampleHTML = $wp_filesystem->get_contents(dirname(__FILE__) . '/info-html.html');
            }
            // ACTUAL DECLARATION OF SECTIONS

            $wp_menus = get_terms('nav_menu', array('hide_empty' => true));
            $wp_menus_array = array('' => __('Select Menu', 'wp-jobsearch'));
            foreach ($wp_menus as $wp_menu) {

                if (is_object($wp_menu) && isset($wp_menu->term_id)) {
                    $wp_menus_array[$wp_menu->term_id] = $wp_menu->name;
                }
            }

            $cv_pckgs = array();

            $args = array(
                'post_type' => 'package',
                'posts_per_page' => -1,
                'post_status' => 'publish',
                'fields' => 'ids',
                'order' => 'ASC',
                'orderby' => 'title',
                'meta_query' => array(
                    array(
                        'key' => 'jobsearch_field_package_type',
                        'value' => 'cv',
                        'compare' => '=',
                    ),
                ),
            );
            $pkgs_query = new WP_Query($args);

            if ($pkgs_query->found_posts > 0) {
                $pkgs_list = $pkgs_query->posts;

                if (!empty($pkgs_list)) {
                    foreach ($pkgs_list as $pkg_item) {
                        $cv_pkg_post = get_post($pkg_item);
                        $cv_pkg_post_name = isset($cv_pkg_post->post_name) ? $cv_pkg_post->post_name : '';
                        $cv_pckgs[$cv_pkg_post_name] = get_the_title($pkg_item);
                    }
                }
            }
            wp_reset_postdata();

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
                    $all_page[$page->post_name] = $page->post_title;
                }
            }

            $sec_array = array();
            $sec_array[] = array(
                'id' => 'status-settings-section',
                'type' => 'section',
                'title' => __('Status Settings', 'wp-jobsearch'),
                'subtitle' => __('Status settings.', 'wp-jobsearch'),
                'indent' => true,
            );
            $sec_array[] = array(
                'id' => 'jobsearch-approved-color',
                'type' => 'color',
                'transparent' => false,
                'title' => __('Approved Color', 'wp-jobsearch'),
                'subtitle' => __('Approved Status Color.', 'wp-jobsearch'),
                'desc' => '',
                'default' => '#0cd61a'
            );
            $sec_array[] = array(
                'id' => 'jobsearch-pending-color',
                'type' => 'color',
                'transparent' => false,
                'title' => __('Pending Color', 'wp-jobsearch'),
                'subtitle' => __('Pending Status Color.', 'wp-jobsearch'),
                'desc' => '',
                'default' => '#110de2'
            );
            $sec_array[] = array(
                'id' => 'jobsearch-canceled-color',
                'type' => 'color',
                'transparent' => false,
                'title' => __('Canceled Color', 'wp-jobsearch'),
                'subtitle' => __('Canceled Status Color.', 'wp-jobsearch'),
                'desc' => '',
                'default' => '#e50d0d'
            );
            $sec_array[] = array(
                'id' => 'sectorscat-settings-section',
                'type' => 'section',
                'title' => __('Sectors Settings', 'wp-jobsearch'),
                'subtitle' => '',
                'indent' => true,
            );
            $sec_array[] = array(
                'id' => 'sectors_onoff_switch',
                'type' => 'button_set',
                'title' => __('Sectors On/Off', 'wp-jobsearch'),
                'subtitle' => __('It will disable all sectors from site.', 'wp-jobsearch'),
                'desc' => '',
                'options' => array(
                    'on' => __('On', 'wp-jobsearch'),
                    'off' => __('Off', 'wp-jobsearch'),
                ),
                'default' => 'on',
            );
            $sec_array[] = array(
                'id' => 'email-log-settings-section',
                'type' => 'section',
                'title' => __('Email Log Settings', 'wp-jobsearch'),
                'subtitle' => __('Email log settings.', 'wp-jobsearch'),
                'indent' => true,
            );
            $sec_array[] = array(
                'id' => 'jobsearch-email-log-switch',
                'type' => 'button_set',
                'title' => __('Email Log Switch', 'wp-jobsearch'),
                'subtitle' => __('If you want to log every email then switch on.', 'wp-jobsearch'),
                'desc' => '',
                'options' => array(
                    'on' => __('On', 'wp-jobsearch'),
                    'off' => __('Off', 'wp-jobsearch'),
                ),
                'default' => 'off',
            );
            $sec_array[] = array(
                'id' => 'job-apply-settings-section',
                'type' => 'section',
                'title' => __('Jobs', 'wp-jobsearch'),
                'subtitle' => __('Jobs settings.', 'wp-jobsearch'),
                'indent' => true,
            );
            $sec_array[] = array(
                'id' => 'job-apply-without-login',
                'type' => 'button_set',
                'title' => __('Apply job without login', 'wp-jobsearch'),
                'subtitle' => __('Enable if you want to users can apply job without login.', 'wp-jobsearch'),
                'desc' => '',
                'options' => array(
                    'on' => __('On', 'wp-jobsearch'),
                    'off' => __('Off', 'wp-jobsearch'),
                ),
                'default' => 'off',
            );
            $sec_array[] = array(
                'id' => 'job_custom_currency',
                'type' => 'button_set',
                'title' => __('Salary Custom Currency', 'wp-jobsearch'),
                'subtitle' => '',
                'desc' => __('Allow users to select Custom Currency for job and candidate salary.', 'wp-jobsearch'),
                'options' => array(
                    'on' => __('On', 'wp-jobsearch'),
                    'off' => __('Off', 'wp-jobsearch'),
                ),
                'default' => 'on',
            );
            $sec_array[] = array(
                'id' => 'jobsearch_search_list_page',
                'type' => 'select',
                'title' => __('Search Result Page', 'wp-jobsearch'),
                'subtitle' => __('Select Search Result Page.', 'wp-jobsearch'),
                'desc' => '',
                'options' => $all_page,
                'default' => '',
            );
            $sec_array[] = array(
                'id' => 'job_det_contact_form',
                'type' => 'button_set',
                'title' => __('Job Detail Contact Form', 'wp-jobsearch'),
                'subtitle' => '',
                'desc' => __('Allow candidates to contact employer at job detail page.', 'wp-jobsearch'),
                'options' => array(
                    'on' => __('On', 'wp-jobsearch'),
                    'off' => __('Off', 'wp-jobsearch'),
                ),
                'default' => 'on',
            );
            $sec_array[] = array(
                'id' => 'job_views_publish_date',
                'type' => 'button_set',
                'title' => __('Jobs Publish date', 'wp-jobsearch'),
                'subtitle' => __('Enable/Disable job publish date in jobs listing and detail pages.', 'wp-jobsearch'),
                'desc' => '',
                'options' => array(
                    'on' => __('On', 'wp-jobsearch'),
                    'off' => __('Off', 'wp-jobsearch'),
                ),
                'default' => 'on',
            );
            $sec_array[] = array(
                'id' => 'listin_map_marker_img',
                'type' => 'media',
                'url' => true,
                'title' => __('Jobs Map Marker Icon', 'wp-jobsearch'),
                'compiler' => 'true',
                'desc' => '',
                'subtitle' => '',
                'default' => array('url' => ''),
            );
            $sec_array[] = array(
                'id' => 'listin_map_cluster_img',
                'type' => 'media',
                'url' => true,
                'title' => __('Jobs Map Cluster Icon', 'wp-jobsearch'),
                'compiler' => 'true',
                'desc' => '',
                'subtitle' => '',
                'default' => array('url' => ''),
            );
            $sec_array[] = array(
                'id' => 'employer-gensettings-section',
                'type' => 'section',
                'title' => __('Employers', 'wp-jobsearch'),
                'subtitle' => __('Employers settings.', 'wp-jobsearch'),
                'indent' => true,
            );
            $sec_array[] = array(
                'id' => 'emp_det_contact_form',
                'type' => 'button_set',
                'title' => __('Employer Detail Contact Form', 'wp-jobsearch'),
                'subtitle' => '',
                'desc' => __('Allow candidates to contact employer at Employers detail page.', 'wp-jobsearch'),
                'options' => array(
                    'on' => __('On', 'wp-jobsearch'),
                    'off' => __('Off', 'wp-jobsearch'),
                ),
                'default' => 'on',
            );
            $sec_array[] = array(
                'id' => 'elistin_map_marker_img',
                'type' => 'media',
                'url' => true,
                'title' => __('Employers Map Marker Icon', 'wp-jobsearch'),
                'compiler' => 'true',
                'desc' => '',
                'subtitle' => '',
                'default' => array('url' => ''),
            );
            $sec_array[] = array(
                'id' => 'elistin_map_cluster_img',
                'type' => 'media',
                'url' => true,
                'title' => __('Employers Map Cluster Icon', 'wp-jobsearch'),
                'compiler' => 'true',
                'desc' => '',
                'subtitle' => '',
                'default' => array('url' => ''),
            );
            $sec_array[] = array(
                'id' => 'candidate-gensettings-section',
                'type' => 'section',
                'title' => __('Candidates', 'wp-jobsearch'),
                'subtitle' => __('Candidates settings.', 'wp-jobsearch'),
                'indent' => true,
            );
            $sec_array[] = array(
                'id' => 'cand_det_contact_form',
                'type' => 'button_set',
                'title' => __('Candidate Detail Contact Form', 'wp-jobsearch'),
                'subtitle' => '',
                'desc' => __('Allow employer to contact candidates at Candidate detail page.', 'wp-jobsearch'),
                'options' => array(
                    'on' => __('On', 'wp-jobsearch'),
                    'off' => __('Off', 'wp-jobsearch'),
                ),
                'default' => 'on',
            );
            $sec_array[] = array(
                'id' => 'clistin_map_marker_img',
                'type' => 'media',
                'url' => true,
                'title' => __('Candidates Map Marker Icon', 'wp-jobsearch'),
                'compiler' => 'true',
                'desc' => '',
                'subtitle' => '',
                'default' => array('url' => ''),
            );
            $sec_array[] = array(
                'id' => 'clistin_map_cluster_img',
                'type' => 'media',
                'url' => true,
                'title' => __('Candidates Map Cluster Icon', 'wp-jobsearch'),
                'compiler' => 'true',
                'desc' => '',
                'subtitle' => '',
                'default' => array('url' => ''),
            );
            $sec_array[] = array(
                'id' => 'salary-types-settings-section',
                'type' => 'section',
                'title' => __('Salary', 'wp-jobsearch'),
                'subtitle' => __('Default salary settings for jobs and candidates.', 'wp-jobsearch'),
                'indent' => true,
            );
            $sec_array[] = array(
                'id' => 'salary_onoff_switch',
                'type' => 'button_set',
                'title' => __('Salary', 'wp-jobsearch'),
                'subtitle' => '',
                'desc' => __('It will completely disable/enable the salary fields.', 'wp-jobsearch'),
                'options' => array(
                    'on' => __('On', 'wp-jobsearch'),
                    'off' => __('Off', 'wp-jobsearch'),
                ),
                'default' => 'on',
            );
            $sec_array[] = array(
                'id' => 'job-salary-types',
                'type' => 'multi_text',
                'title' => __('Salary Types', 'wp-jobsearch'),
                'subtitle' => '',
                'default' => array(__('Monthly', 'wp-jobsearch'), __('Weekly', 'wp-jobsearch'), __('Hourly', 'wp-jobsearch')),
                'desc' => __('Set salary types.', 'wp-jobsearch'),
            );
            $sec_array[] = array(
                'id' => 'default-view-settings-section',
                'type' => 'section',
                'title' => __('Default View Settings', 'wp-jobsearch'),
                'subtitle' => __('Default view settings.', 'wp-jobsearch'),
                'indent' => true,
            );
            $sec_array[] = array(
                'id' => 'jobsearch-default-page-view',
                'type' => 'button_set',
                'title' => __('Default View', 'wp-jobsearch'),
                'subtitle' => __('If you want to change plugin default pages view.', 'wp-jobsearch'),
                'desc' => '',
                'options' => array(
                    'full' => __('Full Width', 'wp-jobsearch'),
                    'boxed' => __('Boxed', 'wp-jobsearch'),
                ),
                'default' => 'full',
            );
            $sec_array[] = array(
                'id' => 'jobsearch-boxed-view-width',
                'type' => 'text',
                'title' => __('Boxed View Width', 'wp-jobsearch'),
                'subtitle' => __("Boxed view default width with unit like px, pt...etc, it will only apply on 'Boxed' view.", 'wp-jobsearch'),
                'desc' => '',
                'default' => '1140px',
            );

            $sec_array[] = array(
                'id' => 'default-no-image-section',
                'type' => 'section',
                'title' => __('Image Placeholder Settings', 'wp-jobsearch'),
                'subtitle' => '',
                'indent' => true,
            );
            $sec_array[] = array(
                'id' => 'default_no_img',
                'type' => 'media',
                'url' => true,
                'title' => __('Job Image Placeholder', 'wp-jobsearch'),
                'compiler' => 'true',
                'desc' => '',
                'subtitle' => '',
                'default' => array('url' => jobsearch_plugin_get_url('images/no-image.jpg')),
            );
            $sec_array[] = array(
                'id' => 'terms-cond-page-section',
                'type' => 'section',
                'title' => __('Terms and Conditions', 'wp-jobsearch'),
                'subtitle' => '',
                'indent' => true,
            );
            $sec_array[] = array(
                'id' => 'terms_conditions_page',
                'type' => 'select',
                'title' => __('Terms and Conditions Page', 'wp-jobsearch'),
                'subtitle' => __('Select Terms and Conditions Page.', 'wp-jobsearch'),
                'desc' => '',
                'options' => $all_page,
                'default' => '',
            );

            $this->sections[] = array(
                'title' => __('General Options', 'wp-jobsearch'),
                'id' => 'general-options',
                'desc' => __('These are really basic options!', 'wp-jobsearch'),
                'icon' => 'el el-home',
                'fields' => $sec_array,
            );



            $jobsearch_mailchimp_list = array();
            if (isset($jobsearch_plugin_options['jobsearch-mailchimp-api-key'])) {
                $mailchimp_key = $jobsearch_plugin_options['jobsearch-mailchimp-api-key'];
                if ($mailchimp_key <> '') {

                    if (function_exists('jobsearch_mailchimp_list')) {
                        $mc_list = jobsearch_mailchimp_list($mailchimp_key);

                        if (is_array($mc_list) && isset($mc_list['data'])) {
                            foreach ($mc_list['data'] as $list) {
                                $jobsearch_mailchimp_list[$list['id']] = $list['name'];
                            }
                        }
                    }
                }
            }

            $section_settings = array(
                'title' => __('API Settings', 'wp-jobsearch'),
                'id' => 'api-settings',
                'desc' => __('Set API\'s for theme.', 'wp-jobsearch'),
                'icon' => 'el el-idea',
                'fields' => array(
                    array(
                        'id' => 'twitter-api-section',
                        'type' => 'section',
                        'title' => __('Twitter API settings section.', 'wp-jobsearch'),
                        'subtitle' => sprintf(__('Callback URL is: %s', 'wp-jobsearch'), admin_url('admin-ajax.php?action=jobsearch_twitter')),
                        'indent' => true,
                    ),
                    array(
                        'id' => 'jobsearch-twitter-consumer-key',
                        'type' => 'text',
                        'transparent' => false,
                        'title' => __('Consumer Key', 'wp-jobsearch'),
                        'subtitle' => __('Set Consumer Key for twitter.', 'wp-jobsearch'),
                        'desc' => '',
                        'default' => ''
                    ),
                    array(
                        'id' => 'jobsearch-twitter-consumer-secret',
                        'type' => 'text',
                        'transparent' => false,
                        'title' => __('Consumer Secret', 'wp-jobsearch'),
                        'subtitle' => __('Set Consumer Secret for twitter.', 'wp-jobsearch'),
                        'desc' => '',
                        'default' => ''
                    ),
                    array(
                        'id' => 'jobsearch-twitter-access-token',
                        'type' => 'text',
                        'transparent' => false,
                        'title' => __('Access Token', 'wp-jobsearch'),
                        'subtitle' => __('Set Access Token for twitter.', 'wp-jobsearch'),
                        'desc' => '',
                        'default' => ''
                    ),
                    array(
                        'id' => 'jobsearch-twitter-token-secret',
                        'type' => 'text',
                        'transparent' => false,
                        'title' => __('Token Secret', 'wp-jobsearch'),
                        'subtitle' => __('Set Token Secret for twitter.', 'wp-jobsearch'),
                        'desc' => '',
                        'default' => ''
                    ),
                    array(
                        'id' => 'google-captcha-api-section',
                        'type' => 'section',
                        'title' => __('Google Captcha API settings section.', 'wp-jobsearch'),
                        'subtitle' => '',
                        'indent' => true,
                    ),
                    array(
                        'id' => 'captcha_switch',
                        'type' => 'button_set',
                        'title' => __('Google Captcha', 'wp-jobsearch'),
                        'subtitle' => __('Google Captcha Switch.', 'wp-jobsearch'),
                        'desc' => '',
                        'options' => array(
                            'on' => __('On', 'wp-jobsearch'),
                            'off' => __('Off', 'wp-jobsearch'),
                        ),
                        'default' => 'off',
                    ),
                    array(
                        'id' => 'captcha_sitekey',
                        'type' => 'text',
                        'transparent' => false,
                        'title' => __('Site Key', 'wp-jobsearch'),
                        'subtitle' => '',
                        'desc' => __('Put your site key for captcha. You can get this site key after registering your site on Google.', 'wp-jobsearch'),
                        'default' => ''
                    ),
                    array(
                        'id' => 'captcha_secretkey',
                        'type' => 'text',
                        'transparent' => false,
                        'title' => __('Secret Key', 'wp-jobsearch'),
                        'subtitle' => '',
                        'desc' => __('Put your site Secret key for captcha. You can get this Secret Key after registering your site on Google.', 'wp-jobsearch'),
                        'default' => ''
                    ),
                    array(
                        'id' => 'google-api-section',
                        'type' => 'section',
                        'title' => __('Google API settings section.', 'wp-jobsearch'),
                        'subtitle' => sprintf(__('Callback URL is: %s', 'wp-jobsearch'), home_url('/')),
                        'indent' => true,
                    ),
                    array(
                        'id' => 'jobsearch-google-api-key',
                        'type' => 'text',
                        'transparent' => false,
                        'title' => __('API Key', 'wp-jobsearch'),
                        'subtitle' => __('Please enter API key of your Google account.', 'wp-jobsearch'),
                        'desc' => '',
                        'default' => ''
                    ),
                )
            );
            $this->sections[] = apply_filters('jobsearch_api_settings_section', $section_settings);

            $section_settings = apply_filters('jobsearch_login_settings_section', array());
            if (isset($section_settings['title'])) {
                $this->sections[] = $section_settings;
            }

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
                    $all_page[$page->post_name] = $page->post_title;
                }
            }

            $section_settings = array(
                'title' => __('User Dashboard', 'wp-jobsearch'),
                'id' => 'user-dashboard',
                'desc' => __('User Dashboard Settings', 'wp-jobsearch'),
                'icon' => 'el el-user',
                'fields' => array(
                    array(
                        'id' => 'user-dashboard-template-page',
                        'type' => 'select',
                        'title' => __('User Dashboard Page', 'wp-jobsearch'),
                        'subtitle' => __('Select User Dashboard Page.', 'wp-jobsearch'),
                        'desc' => '',
                        'options' => $all_page,
                        'default' => '',
                    ),
                    array(
                        'id' => 'user-dashboard-per-page',
                        'type' => 'text',
                        'title' => __('Resluts Per Page', 'wp-jobsearch'),
                        'subtitle' => '',
                        'desc' => __('Set Resluts Per Page in user dashboard pages.', 'wp-jobsearch'),
                        'default' => '10',
                    ),
                    array(
                        'id' => 'packages_menu_links',
                        'type' => 'button_set',
                        'title' => __('Packages and Transactions', 'wp-jobsearch'),
                        'subtitle' => '',
                        'desc' => __('Packages and Transactions menu links in dashboard.', 'wp-jobsearch'),
                        'options' => array(
                            'on' => __('On', 'wp-jobsearch'),
                            'off' => __('Off', 'wp-jobsearch'),
                        ),
                        'default' => 'on',
                    ),
                )
            );
            $this->sections[] = $section_settings;

            $section_settings = array(
                'title' => __('Employer Settings', 'wp-jobsearch'),
                'id' => 'user-dashboard',
                'desc' => __('Employer Common Settings', 'wp-jobsearch'),
                'icon' => 'el el-user',
                'fields' => array(
                    array(
                        'id' => 'employer_auto_approve',
                        'type' => 'button_set',
                        'title' => __('Employer Auto Approve', 'wp-jobsearch'),
                        'subtitle' => '',
                        'desc' => __('Allow employers to auto approved after registeration.', 'wp-jobsearch'),
                        'options' => array(
                            'on' => __('On', 'wp-jobsearch'),
                            'off' => __('Off', 'wp-jobsearch'),
                        ),
                        'default' => 'on',
                    ),
                    array(
                        'id' => 'unapproverd_employer_txt',
                        'type' => 'editor',
                        'args' => array(
                            'teeny' => true,
                            'media_buttons' => false,
                        ),
                        'title' => __('Unapproved Employer Text', 'wp-jobsearch'),
                        'required' => array('employer_auto_approve', 'equals', 'off'),
                        'subtitle' => __('This text will show in unapproved employer dashboard.', 'wp-jobsearch'),
                        'desc' => '',
                        'default' => '<strong>ACCOUNT ACTIVATION REQUIRED BY ADMIN !</strong>

<strong>Your account is In-active!</strong>

Your membership account is awaiting approval by the site administrator. You will not be able to fully interact with the account functions and aspects of this website until your account is approved. Once approved by admin or denied you will receive an email notice.',
                    ),
                    array(
                        'id' => 'free-shortlist-allow',
                        'type' => 'button_set',
                        'title' => __('Free Shortlist', 'wp-jobsearch'),
                        'subtitle' => '',
                        'desc' => __('Allow employers to shortlist candidates absolutely package free.', 'wp-jobsearch'),
                        'options' => array(
                            'on' => __('On', 'wp-jobsearch'),
                            'off' => __('Off', 'wp-jobsearch'),
                        ),
                        'default' => 'on',
                    ),
                    array(
                        'id' => 'allow_team_members',
                        'type' => 'button_set',
                        'title' => __('Employer Team Members', 'wp-jobsearch'),
                        'subtitle' => '',
                        'desc' => __('Allow Employer to add Team Members.', 'wp-jobsearch'),
                        'options' => array(
                            'on' => __('On', 'wp-jobsearch'),
                            'off' => __('Off', 'wp-jobsearch'),
                        ),
                        'default' => 'on',
                    ),
                    array(
                        'id' => 'resume_package_page',
                        'type' => 'select',
                        'title' => __('Resume Packages Page', 'wp-jobsearch'),
                        'required' => array('free-shortlist-allow', 'equals', 'off'),
                        'subtitle' => __('Select Resume Packages Page. It will redirect employers at selected page to buy package.', 'wp-jobsearch'),
                        'desc' => '',
                        'options' => $all_page,
                        'default' => '',
                    ),
                    array(
                        'id' => 'max_gal_imgs_allow',
                        'type' => 'text',
                        'title' => __('Maximum Gallery images allowed', 'wp-jobsearch'),
                        'subtitle' => '',
                        'desc' => __('Set Maximum Gallery images allowed.', 'wp-jobsearch'),
                        'default' => '5',
                    ),
                    array(
                        'id' => 'employer_no_img',
                        'type' => 'media',
                        'url' => true,
                        'title' => __('Employer Image Placeholder', 'wp-jobsearch'),
                        'compiler' => 'true',
                        'desc' => '',
                        'subtitle' => '',
                        'default' => array('url' => jobsearch_plugin_get_url('images/no-image.jpg')),
                    ),
                ),
            );
            $this->sections[] = $section_settings;

            $section_settings = array(
                'title' => __('Candidate Settings', 'wp-jobsearch'),
                'id' => 'user-dashboard',
                'desc' => __('Candidate Common Settings', 'wp-jobsearch'),
                'icon' => 'el el-user',
                'fields' => apply_filters('jobsearch_options_candidate_setings_fields', array(
                    array(
                        'id' => 'candidate_auto_approve',
                        'type' => 'button_set',
                        'title' => __('Candidate Auto Approve', 'wp-jobsearch'),
                        'subtitle' => '',
                        'desc' => __('Allow candidates to auto approved after registeration.', 'wp-jobsearch'),
                        'options' => array(
                            'on' => __('On', 'wp-jobsearch'),
                            'off' => __('Off', 'wp-jobsearch'),
                        ),
                        'default' => 'on',
                    ),
                    array(
                        'id' => 'unapproverd_candidate_txt',
                        'type' => 'editor',
                        'args' => array(
                            'teeny' => true,
                            'media_buttons' => false,
                        ),
                        'title' => __('Unapproved Candidate Text', 'wp-jobsearch'),
                        'required' => array('candidate_auto_approve', 'equals', 'off'),
                        'subtitle' => __('This text will show in unapproved candidate dashboard.', 'wp-jobsearch'),
                        'desc' => '',
                        'default' => '<strong>ACCOUNT ACTIVATION REQUIRED BY ADMIN !</strong>

<strong>Your account is In-active!</strong>

Your membership account is awaiting approval by the site administrator. You will not be able to fully interact with the account functions and aspects of this website until your account is approved. Once approved by admin or denied you will receive an email notice.',
                    ),
                    array(
                        'id' => 'free-job-apply-allow',
                        'type' => 'button_set',
                        'title' => __('Free Job Apply', 'wp-jobsearch'),
                        'subtitle' => '',
                        'desc' => __('Allow candidates to apply jobs absolutely package free.', 'wp-jobsearch'),
                        'options' => array(
                            'on' => __('On', 'wp-jobsearch'),
                            'off' => __('Off', 'wp-jobsearch'),
                        ),
                        'default' => 'on',
                    ),
                    array(
                        'id' => 'candidate_package_page',
                        'type' => 'select',
                        'title' => __('Candidate Packages Page', 'wp-jobsearch'),
                        'required' => array('free-job-apply-allow', 'equals', 'off'),
                        'subtitle' => __('Select Candidate Packages Page. It will redirect candidates at selected page to buy package.', 'wp-jobsearch'),
                        'desc' => '',
                        'options' => $all_page,
                        'default' => '',
                    ),
                    array(
                        'id' => 'apply_social_platforms',
                        'type' => 'button_set',
                        'multi' => true,
                        'title' => __('Apply Job with Social Platforms', 'wp-jobsearch'),
                        'subtitle' => '',
                        'options' => array(
                            'facebook' => __('Facebook', 'wp-jobsearch'),
                            'linkedin' => __('Linkedin', 'wp-jobsearch'),
                            'google' => __('Google', 'wp-jobsearch'),
                        ),
                        'default' => array('facebook', 'linkedin'),
                        'desc' => __('Select Social Platforms to apply job.', 'wp-jobsearch'),
                    ),
                    array(
                        'id' => 'max_portfolio_allow',
                        'type' => 'text',
                        'title' => __('Maximum Portfolios allowed', 'wp-jobsearch'),
                        'subtitle' => '',
                        'desc' => __('Set Maximum Portfolios allowed for candidate.', 'wp-jobsearch'),
                        'default' => '5',
                    ),
                    array(
                        'id' => 'multiple_cv_uploads',
                        'type' => 'button_set',
                        'title' => __('Multiple CV Upload', 'wp-jobsearch'),
                        'subtitle' => '',
                        'desc' => __('Allow candidates to Upload Multiple CV files.', 'wp-jobsearch'),
                        'options' => array(
                            'on' => __('On', 'wp-jobsearch'),
                            'off' => __('Off', 'wp-jobsearch'),
                        ),
                        'default' => 'off',
                    ),
                    array(
                        'id' => 'max_cvs_allow',
                        'type' => 'text',
                        'title' => __('Maximum CVs allowed', 'wp-jobsearch'),
                        'subtitle' => '',
                        'desc' => __('Set Maximum CVs allowed for candidate.', 'wp-jobsearch'),
                        'default' => '5',
                    ),
                    array(
                        'id' => 'restrict_candidates',
                        'type' => 'button_set',
                        'title' => __('Restrict Candidate Detail', 'wp-jobsearch'),
                        'subtitle' => __('Restrict Candidates detail page for all users except employers.', 'wp-jobsearch'),
                        'desc' => '',
                        'options' => array(
                            'on' => __('On', 'wp-jobsearch'),
                            'off' => __('Off', 'wp-jobsearch'),
                        ),
                        'default' => 'off',
                    ),
                    array(
                        'id' => 'restrict_candidates_list',
                        'type' => 'button_set',
                        'title' => __('Restrict Candidates Listing', 'wp-jobsearch'),
                        'subtitle' => __('Restrict Candidates Listing page for all users except employers.', 'wp-jobsearch'),
                        'desc' => '',
                        'options' => array(
                            'on' => __('On', 'wp-jobsearch'),
                            'off' => __('Off', 'wp-jobsearch'),
                        ),
                        'default' => 'off',
                    ),
                    array(
                        'id' => 'restrict_cand_msg',
                        'type' => 'textarea',
                        'title' => __('Restrict page Message', 'wp-jobsearch'),
                        'subtitle' => __('Message for restrict candidate page.', 'wp-jobsearch'),
                        'desc' => '',
                        'default' => __('THE PAGE IS RESTRICTED ONLY FOR SUBSCRIBED EMPLOYERS', 'wp-jobsearch'),
                    ),
                    array(
                        'id' => 'restrict_candidates_for_users',
                        'type' => 'button_set',
                        'title' => __('Restrict for Employers', 'wp-jobsearch'),
                        //'required' => array('restrict_candidates', 'equals', 'on'),
                        'subtitle' => __('1. All registered employers can view candidates. <br> 2. Registered employers who purchased resume package can view candidates. <br> 3. Employer can view only their own applicants candidates.', 'wp-jobsearch'),
                        'desc' => '',
                        'options' => array(
                            'register' => __('1. Register Employers', 'wp-jobsearch'),
                            'register_resume' => __('2. Register Employers with package', 'wp-jobsearch'),
                            'only_applicants' => __('3. Only Applicants', 'wp-jobsearch'),
                        ),
                        'default' => 'register',
                    ),
                    array(
                        'id' => 'restrict_cv_packages',
                        'type' => 'select',
                        'multi' => true,
                        'title' => __('Cv Packages', 'wp-jobsearch'),
                        'required' => array(
                            //array('restrict_candidates', 'equals', 'on'),
                            array('restrict_candidates_for_users', 'equals', 'register_resume'),
                        ),
                        'subtitle' => '',
                        'options' => $cv_pckgs,
                        'default' => '',
                        'desc' => __('Select Cv packages for employers.', 'wp-jobsearch'),
                    ),
                    array(
                        'id' => 'candidate_restrict_img',
                        'type' => 'media',
                        'url' => true,
                        'title' => __('Restriction Image', 'wp-jobsearch'),
                        //'required' => array('restrict_candidates', 'equals', 'on'),
                        'compiler' => 'true',
                        'desc' => __('Candidate Restriction Image', 'wp-jobsearch'),
                        'subtitle' => '',
                        'default' => array('url' => jobsearch_plugin_get_url('images/restrict-candidate.png')),
                    ),
                    array(
                        'id' => 'candidate_no_img',
                        'type' => 'media',
                        'url' => true,
                        'title' => __('Candidate Image Placeholder', 'wp-jobsearch'),
                        'compiler' => 'true',
                        'desc' => '',
                        'subtitle' => '',
                        'default' => array('url' => jobsearch_plugin_get_url('images/no-image.jpg')),
                    ),
                )),
            );
            $this->sections[] = $section_settings;

            $cand_custom_fileds = $empl_custom_fileds = array();
            $custom_fields_candidate = get_option('jobsearch_custom_field_candidate');
            if (is_array($custom_fields_candidate) && sizeof($custom_fields_candidate) > 0) {
                foreach ($custom_fields_candidate as $post_cf) {
                    $field_name = isset($post_cf['name']) ? $post_cf['name'] : '';
                    $field_label = isset($post_cf['label']) ? $post_cf['label'] : '';
                    if ($field_name != '' && $field_label != '') {
                        $cand_custom_fileds[$field_name] = $field_label;
                    }
                }
            }
            $custom_fields_employer = get_option('jobsearch_custom_field_employer');
            if (is_array($custom_fields_employer) && sizeof($custom_fields_employer) > 0) {
                foreach ($custom_fields_employer as $post_cf) {
                    $field_name = isset($post_cf['name']) ? $post_cf['name'] : '';
                    $field_label = isset($post_cf['label']) ? $post_cf['label'] : '';
                    if ($field_name != '' && $field_label != '') {
                        $empl_custom_fileds[$field_name] = $field_label;
                    }
                }
            }
            $section_settings = array(
                'title' => __('Sign up Settings', 'wp-jobsearch'),
                'id' => 'sign-up-settings',
                'desc' => __('Sign up Settings', 'wp-jobsearch'),
                'icon' => 'el el-user',
                'fields' => apply_filters('jobsearch_options_signup_setings_fields', array(
                    array(
                        'id' => 'signup_user_password',
                        'type' => 'button_set',
                        'title' => __('Password from User', 'wp-jobsearch'),
                        'subtitle' => '',
                        'desc' => __('Allow users to set password from register form.', 'wp-jobsearch'),
                        'options' => array(
                            'on' => __('On', 'wp-jobsearch'),
                            'off' => __('Off', 'wp-jobsearch'),
                        ),
                        'default' => 'on',
                    ),
                    array(
                        'id' => 'signup_custom_fields',
                        'type' => 'button_set',
                        'title' => __('Custom Fields in Register Form', 'wp-jobsearch'),
                        'subtitle' => '',
                        'desc' => __('Allow Custom Fields in Register Form.', 'wp-jobsearch'),
                        'options' => array(
                            'on' => __('On', 'wp-jobsearch'),
                            'off' => __('Off', 'wp-jobsearch'),
                        ),
                        'default' => 'off',
                    ),
                    array(
                        'id' => 'candidate_custom_fields',
                        'type' => 'select',
                        'multi' => true,
                        'title' => __('Candidate Custom Fields', 'wp-jobsearch'),
                        'subtitle' => '',
                        'options' => $cand_custom_fileds,
                        'default' => '',
                        'desc' => __('Select Candidate Custom Fields which will show in registeration form.', 'wp-jobsearch'),
                    ),
                    array(
                        'id' => 'employer_custom_fields',
                        'type' => 'select',
                        'multi' => true,
                        'title' => __('Employer Custom Fields', 'wp-jobsearch'),
                        'subtitle' => '',
                        'options' => $empl_custom_fileds,
                        'default' => '',
                        'desc' => __('Select Employer Custom Fields which will show in registeration form.', 'wp-jobsearch'),
                    ),
                    array(
                        'id' => 'signup_user_sector',
                        'type' => 'button_set',
                        'title' => __('User Sector', 'wp-jobsearch'),
                        'subtitle' => '',
                        'desc' => '',
                        'options' => array(
                            'on' => __('On', 'wp-jobsearch'),
                            'off' => __('Off', 'wp-jobsearch'),
                        ),
                        'default' => 'on',
                    ),
                    array(
                        'id' => 'signup_user_phone',
                        'type' => 'button_set',
                        'title' => __('User Phone Number', 'wp-jobsearch'),
                        'subtitle' => '',
                        'desc' => '',
                        'options' => array(
                            'on' => __('On', 'wp-jobsearch'),
                            'off' => __('Off', 'wp-jobsearch'),
                        ),
                        'default' => 'on',
                    ),
                    array(
                        'id' => 'signup_organization_name',
                        'type' => 'button_set',
                        'title' => __('Organization Name', 'wp-jobsearch'),
                        'subtitle' => '',
                        'desc' => __('Get Organization Name from employer.', 'wp-jobsearch'),
                        'options' => array(
                            'on' => __('On', 'wp-jobsearch'),
                            'off' => __('Off', 'wp-jobsearch'),
                        ),
                        'default' => 'on',
                    ),
                )),
            );
            $this->sections[] = $section_settings;

            $section_settings = array(
                'title' => __('Job Post Settings', 'wp-jobsearch'),
                'id' => 'user-job-posting',
                'desc' => __('User Job Post Settings', 'wp-jobsearch'),
                'icon' => 'el el-check',
                'fields' => apply_filters('jobsearch_poptions_post_job_sett_fields', array(
                    array(
                        'id' => 'free-job-post-expiry',
                        'type' => 'text',
                        'title' => __('Job Expiry Days', 'wp-jobsearch'),
                        'subtitle' => __('Set default time period for job expiry.', 'wp-jobsearch'),
                        'desc' => __('Enter only number. This time period will consider in days only. i.e 1 day, 3 days, 7 days or 30 days.', 'wp-jobsearch'),
                        'default' => '15',
                    ),
                    array(
                        'id' => 'job-default-status',
                        'type' => 'select',
                        'title' => __('Job Status', 'wp-jobsearch'),
                        'subtitle' => '',
                        'options' => array(
                            'approved' => __('Approved', 'wp-jobsearch'),
                            'admin-review' => __('Admin Review', 'wp-jobsearch'),
                        ),
                        'desc' => __('Set default status for every new posting job ad.', 'wp-jobsearch'),
                        'default' => 'approved',
                    ),
                    array(
                        'id' => 'job-post-wout-reg',
                        'type' => 'button_set',
                        'title' => __('Job Post without Registration', 'wp-jobsearch'),
                        'subtitle' => '',
                        'desc' => '',
                        'options' => array(
                            'on' => __('On', 'wp-jobsearch'),
                            'off' => __('Off', 'wp-jobsearch'),
                        ),
                        'default' => 'on',
                    ),
                    array(
                        'id' => 'job_post_restrict_img',
                        'type' => 'media',
                        'url' => true,
                        'title' => __('Restriction Image', 'wp-jobsearch'),
                        'required' => array('job-post-wout-reg', 'equals', 'off'),
                        'compiler' => 'true',
                        'desc' => __('Job Post Restriction Image', 'wp-jobsearch'),
                        'subtitle' => '',
                        'default' => array('url' => jobsearch_plugin_get_url('images/restrict-candidate.png')),
                    ),
                    array(
                        'id' => 'free-jobs-allow',
                        'type' => 'button_set',
                        'title' => __('Free Jobs', 'wp-jobsearch'),
                        'subtitle' => '',
                        'desc' => __('Allow users to post absolutely package free jobs.', 'wp-jobsearch'),
                        'options' => array(
                            'on' => __('On', 'wp-jobsearch'),
                            'off' => __('Off', 'wp-jobsearch'),
                        ),
                        'default' => 'on',
                    ),
                    array(
                        'id' => 'job_appliction_deadline',
                        'type' => 'button_set',
                        'title' => __('Job application deadline', 'wp-jobsearch'),
                        'subtitle' => '',
                        'desc' => __('Allow users to add apply deadline date for job.', 'wp-jobsearch'),
                        'options' => array(
                            'on' => __('On', 'wp-jobsearch'),
                            'off' => __('Off', 'wp-jobsearch'),
                        ),
                        'default' => 'on',
                    ),
                    array(
                        'id' => 'job-apply-extrnal-url',
                        'type' => 'button_set',
                        'title' => __('Apply Job with External Methods', 'wp-jobsearch'),
                        'subtitle' => '',
                        'desc' => __('Allow candidates to apply jobs with External Methods too.', 'wp-jobsearch'),
                        'options' => array(
                            'on' => __('On', 'wp-jobsearch'),
                            'off' => __('Off', 'wp-jobsearch'),
                        ),
                        'default' => 'on',
                    ),
                    array(
                        'id' => 'job-skill-switch',
                        'type' => 'button_set',
                        'title' => __('Job Skills', 'wp-jobsearch'),
                        'subtitle' => '',
                        'desc' => __('Allow users to add skills during post job.', 'wp-jobsearch'),
                        'options' => array(
                            'on' => __('On', 'wp-jobsearch'),
                            'off' => __('Off', 'wp-jobsearch'),
                        ),
                        'default' => 'on',
                    ),
                    array(
                        'id' => 'job_max_skills',
                        'type' => 'text',
                        'title' => __('Max. Skills allow', 'wp-jobsearch'),
                        'required' => array('job-skill-switch', 'equals', 'on'),
                        'subtitle' => '',
                        'desc' => '',
                        'default' => '5',
                    ),
                    array(
                        'id' => 'job_sugg_skills',
                        'type' => 'text',
                        'title' => __('Max. Suggested Skills Show', 'wp-jobsearch'),
                        'required' => array('job-skill-switch', 'equals', 'on'),
                        'subtitle' => '',
                        'desc' => '',
                        'default' => '15',
                    ),
                    array(
                        'id' => 'job_title_length',
                        'type' => 'text',
                        'title' => __('Job Title Max. Length', 'wp-jobsearch'),
                        'subtitle' => '',
                        'desc' => __('Define Job Title Max. Length in charachters.', 'wp-jobsearch'),
                        'default' => '1000',
                    ),
                    array(
                        'id' => 'job_desc_length',
                        'type' => 'text',
                        'title' => __('Job Description Max. Length', 'wp-jobsearch'),
                        'subtitle' => '',
                        'desc' => __('Define Job Description Max. Length in charachters.', 'wp-jobsearch'),
                        'default' => '5000',
                    ),
                    array(
                        'id' => 'job-attachments-settings',
                        'type' => 'section',
                        'title' => __('Job Attachments', 'wp-jobsearch'),
                        'subtitle' => __('Job Attachments settings.', 'wp-jobsearch'),
                        'indent' => true,
                    ),
                    array(
                        'id' => 'job_attachments',
                        'type' => 'button_set',
                        'title' => __('Job Attachments', 'wp-jobsearch'),
                        'subtitle' => '',
                        'desc' => __('Allow users to attach files while posting jobs.', 'wp-jobsearch'),
                        'options' => array(
                            'on' => __('On', 'wp-jobsearch'),
                            'off' => __('Off', 'wp-jobsearch'),
                        ),
                        'default' => 'on',
                    ),
                    array(
                        'id' => 'number_of_attachments',
                        'type' => 'text',
                        'title' => __('Number of Attachments', 'wp-jobsearch'),
                        'required' => array('job_attachments', 'equals', 'on'),
                        'subtitle' => '',
                        'desc' => '',
                        'default' => '5',
                    ),
                    array(
                        'id' => 'job_attachment_types',
                        'type' => 'select',
                        'multi' => true,
                        'title' => __('Attachmets File Types', 'wp-jobsearch'),
                        'required' => array('job_attachments', 'equals', 'on'),
                        'subtitle' => '',
                        'options' => array(
                            'text/plain' => __('text', 'wp-jobsearch'),
                            'image/jpeg' => __('jpeg', 'wp-jobsearch'),
                            'image/png' => __('png', 'wp-jobsearch'),
                            'application/msword' => __('doc', 'wp-jobsearch'),
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => __('docx', 'wp-jobsearch'),
                            'application/vnd.ms-excel' => __('xls', 'wp-jobsearch'),
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => __('xlsx', 'wp-jobsearch'),
                            'application/pdf' => __('pdf', 'wp-jobsearch'),
                        ),
                        'default' => array('application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/pdf'),
                        'desc' => __('Select file formats.', 'wp-jobsearch'),
                    ),
                    array(
                        'id' => 'attach_file_size',
                        'type' => 'select',
                        'title' => __('Max. File Size', 'wp-jobsearch'),
                        'required' => array('job_attachments', 'equals', 'on'),
                        'subtitle' => '',
                        'options' => array(
                            '1024' => __('1 Mb', 'wp-jobsearch'),
                            '2048' => __('2 Mb', 'wp-jobsearch'),
                            '3072' => __('3 Mb', 'wp-jobsearch'),
                            '4096' => __('4 Mb', 'wp-jobsearch'),
                            '5120' => __('5 Mb', 'wp-jobsearch'),
                        ),
                        'desc' => '',
                        'default' => '1024',
                    ),
                    array(
                        'id' => 'job-submit-settings',
                        'type' => 'section',
                        'title' => __('Job Submission', 'wp-jobsearch'),
                        'subtitle' => __('Job Submission settings.', 'wp-jobsearch'),
                        'indent' => true,
                    ),
                    array(
                        'id' => 'job-submit-title',
                        'type' => 'text',
                        'title' => __('Job Submission Title', 'wp-jobsearch'),
                        'subtitle' => '',
                        'desc' => __('This title will show when a user will submit a new job.', 'wp-jobsearch'),
                        'default' => __('Thank you for submitting', 'wp-jobsearch'),
                    ),
                    array(
                        'id' => 'job-submit-msge',
                        'type' => 'textarea',
                        'title' => __('Job Submission Message', 'wp-jobsearch'),
                        'subtitle' => '',
                        'desc' => __('This message will show when a user will submit a new job.', 'wp-jobsearch'),
                        'default' => sprintf(__('Thank you for submitting, your job has been published. If you need help please contact us via email %s', 'wp-jobsearch'), get_bloginfo('admin_email')),
                    ),
                    array(
                        'id' => 'job-submit-img',
                        'type' => 'media',
                        'url' => true,
                        'title' => __('Job Submission Image', 'wp-jobsearch'),
                        'compiler' => 'true',
                        'desc' => __('Confirmation Tab Image', 'wp-jobsearch'),
                        'subtitle' => '',
                        'default' => array('url' => jobsearch_plugin_get_url('images/employer-confirmation-icon.png')),
                    ),
                ))
            );
            $this->sections[] = $section_settings;

            $section_settings = array(
                'title' => __('Security Questions', 'wp-jobsearch'),
                'id' => 'security-questions-sec',
                'desc' => __('Security Questions Settings', 'wp-jobsearch'),
                'icon' => 'el el-question-sign',
                'fields' => array(
                    array(
                        'id' => 'security-questions-switch',
                        'type' => 'button_set',
                        'title' => __('Security Questions', 'wp-jobsearch'),
                        'subtitle' => '',
                        'desc' => __('Add security questions.', 'wp-jobsearch'),
                        'options' => array(
                            'on' => __('On', 'wp-jobsearch'),
                            'off' => __('Off', 'wp-jobsearch'),
                        ),
                        'default' => 'on',
                    ),
                    array(
                        'id' => 'jobsearch-security-questions',
                        'type' => 'multi_text',
                        'title' => __('Questions', 'wp-jobsearch'),
                        'subtitle' => __('Create Dynamic List of Questions.', 'wp-jobsearch'),
                        'desc' => __('These Questions will use for security purposes like password change.', 'wp-jobsearch'),
                        'default' => array(
                            __('What is your first pet name?', 'wp-jobsearch'),
                            __('What is your uncle name?', 'wp-jobsearch'),
                            __('What is your teacher name?', 'wp-jobsearch'),
                            __('What is your place of birth?', 'wp-jobsearch'),
                        ),
                    ),
                )
            );
            $this->sections[] = $section_settings;
        }

        public function setHelpTabs() {
            // Custom page help tabs, displayed using the help API. Tabs are shown in order of definition.
            $this->args['help_tabs'][] = array(
                'id' => 'redux-opts-1',
                'title' => __('Theme Information 1', 'wp-jobsearch'),
                'content' => __('<p>This is the tab content, HTML is allowed.</p>', 'wp-jobsearch')
            );
            $this->args['help_tabs'][] = array(
                'id' => 'redux-opts-2',
                'title' => __('Theme Information 2', 'wp-jobsearch'),
                'content' => __('<p>This is the tab content, HTML is allowed.</p>', 'wp-jobsearch')
            );
            // Set the help sidebar
            $this->args['help_sidebar'] = __('<p>This is the sidebar content, HTML is allowed.</p>', 'wp-jobsearch');
        }

        /**

          All the possible arguments for Redux.
          For full documentation on arguments, please refer to: https://github.com/ReduxFramework/ReduxFramework/wiki/Arguments
         * */
        public function setArguments() {

            $theme = wp_get_theme(); // For use with some settings. Not necessary.
            $this->args = array(
                // TYPICAL -> Change these values as you need/desire
                'opt_name' => 'jobsearch_plugin_options', // This is where your data is stored in the database and also becomes your global variable name.
                'display_name' => __('JobSearch', 'wp-jobsearch'), // Name that appears at the top of your panel
                'display_version' => JobSearch_plugin::get_version(), // Version that appears at the top of your panel
                'menu_type' => 'menu', //Specify if the admin menu should appear or not. Options: menu or submenu (Under appearance only)
                'allow_sub_menu' => true, // Show the sections below the admin menu item or not
                'menu_title' => __('JobSearch Options', 'wp-jobsearch'),
                'page' => __('JobSearch Options', 'wp-jobsearch'),
                'google_api_key' => '', // Must be defined to add google fonts to the typography module
                'global_variable' => '', // Set a different name for your global variable other than the opt_name
                'dev_mode' => false, // Show the time the page took to load, etc
                'customizer' => true, // Enable basic customizer support
                // OPTIONAL -> Give you extra features
                'page_priority' => 33, // Order where the menu appears in the admin area. If there is any conflict, something will not show. Warning.
                'page_parent' => 'themes.php', // For a full list of options, visit: http://codex.wordpress.org/Function_Reference/add_submenu_page#Parameters
                'page_permissions' => 'manage_options', // Permissions needed to access the options panel.
                'menu_icon' => '', // Specify a custom URL to an icon
                'last_tab' => '', // Force your panel to always open to a specific tab (by id)
                'page_icon' => 'icon-themes', // Icon displayed in the admin panel next to your menu_title
                'page_slug' => 'jobsearch_options', // Page slug used to denote the panel
                'save_defaults' => true, // On load save the defaults to DB before user clicks save or not
                'default_show' => false, // If true, shows the default value next to each field that is not the default value.
                'default_mark' => '', // What to print by the field's title if the value shown is default. Suggested: *
                // CAREFUL -> These options are for advanced use only
                'transient_time' => 60 * MINUTE_IN_SECONDS,
                'output' => true, // Global shut-off for dynamic CSS output by the framework. Will also disable google fonts output
                'output_tag' => true, // Allows dynamic CSS to be generated for customizer and google fonts, but stops the dynamic CSS from going to the head
                //'domain'             	=> 'redux-framework', // Translation domain key. Don't change this unless you want to retranslate all of Redux.
                //'footer_credit'      	=> '', // Disable the footer credit of Redux. Please leave if you can help it.
                // FUTURE -> Not in use yet, but reserved or partially implemented. Use at your own risk.
                'database' => '', // possible: options, theme_mods, theme_mods_expanded, transient. Not fully functional, warning!
                'show_import_export' => true, // REMOVE
                'system_info' => false, // REMOVE
                'help_tabs' => array(),
                'help_sidebar' => '', // __( '', $this->args['domain'] );
            );


            // Panel Intro text -> before the form
            if (!isset($this->args['global_variable']) || $this->args['global_variable'] !== false) {
                if (!empty($this->args['global_variable'])) {
                    $v = $this->args['global_variable'];
                } else {
                    $v = str_replace("-", "_", $this->args['opt_name']);
                }
                //$this->args['intro_text'] = sprintf(__('<p>Did you know that Redux sets a global variable for you? To access any of your saved options from within your code you can use your global variable: <strong>$%1$s</strong></p>', 'wp-jobsearch'), $v);
            } else {
                //$this->args['intro_text'] = __('<p>This text is displayed above the options panel. It isn\'t required, but more info is always better! The intro_text field accepts all HTML.</p>', 'wp-jobsearch');
            }
            // Add content after the form.
            //$this->args['footer_text'] = __('<p>This text is displayed below the options panel. It isn\'t required, but more info is always better! The footer_text field accepts all HTML.</p>', 'wp-jobsearch');
        }

    }

    global $JobsearchReduxFramework;
    $JobsearchReduxFramework = new Redux_Framework_options_config();
}
/**
  Custom function for the callback referenced above
 */
if (!function_exists('redux_my_custom_field')):

    function redux_my_custom_field($field, $value) {
        print_r($field);
        print_r($value);
    }

endif;
/**

  Custom function for the callback validation referenced above
 * */
if (!function_exists('redux_validate_callback_function')) {

    function redux_validate_callback_function($field, $value, $existing_value) {
        $error = false;
        $value = 'just testing';
        /*
          do your validation

          if(something) {
          $value = $value;
          } elseif(something else) {
          $error = true;
          $value = $existing_value;
          $field['msg'] = 'your custom error message';
          }
         */

        $return['value'] = $value;
        if ($error == true) {
            $return['error'] = $field;
        }
        return $return;
    }

}