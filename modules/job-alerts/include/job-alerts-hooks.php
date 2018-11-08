<?php
/*
  Class : Job Alerts Hooks
 */


// this is an include only WP file
if (!defined('ABSPATH')) {
    die;
}

// main plugin class
class JobSearch_Job_Alerts_Hooks {

// hook things up
    public function __construct() {

        add_filter('redux/options/jobsearch_plugin_options/sections', array($this, 'plugin_option_fields'));

        //
        add_action('jobsearch_jobs_listing_filters_before', array($this, 'frontend_before_filters_ui_callback'), 10, 1);
        //
        add_action('jobsearch_jobs_listing_before', array($this, 'frontend_before_listings_ui_callback'), 10, 1);

        add_action('jobsearch_after_jobs_listing_content', array($this, 'after_jobs_listing_callback'), 10, 2);

        //
        add_action('wp_ajax_jobsearch_create_job_alert', array($this, 'create_job_alert_callback'));
        add_action('wp_ajax_nopriv_jobsearch_create_job_alert', array($this, 'create_job_alert_callback'));

        // job listings vc shortcode params hook
        add_filter('jobsearch_job_listings_vcsh_params', array($this, 'vc_shortcode_params_add'), 10, 1);

        // job listings editor shortcode params hook
        add_filter('jobsearch_job_listings_sheb_params', array($this, 'editor_shortcode_params_add'), 10, 1);

        // job listings editor shortcode top params hook
        add_filter('jobsearch_job_listings_sheb_params', array($this, 'editor_shortcode_top_params_add'), 10, 1);

        // jobsearch menu tab link add hook
        add_filter('jobsearch_dashboard_menu_items_ext', array($this, 'dashboard_menu_items_ext'), 10, 3);

        // jobsearch dashboard tab content add hook
        add_filter('jobsearch_dashboard_tab_content_ext', array($this, 'dashboard_tab_content_add'), 10, 2);

        add_action('wp_ajax_jobsearch_unsubscribe_job_alert', array($this, 'unsubscribe_job_alert'));
        add_action('wp_ajax_nopriv_jobsearch_unsubscribe_job_alert', array($this, 'unsubscribe_job_alert'));

        add_action('wp_ajax_jobsearch_user_job_alert_delete', array($this, 'remove_job_alert'));
    }

    public function plugin_option_fields($sections) {

        $sections[] = array(
            'title' => __('Job Alerts Settings', 'wp-jobsearch'),
            'id' => 'job-alerts-settings',
            'desc' => '',
            'icon' => 'el el-bell',
            'fields' => array(
                array(
                    'id' => 'job_alerts_switch',
                    'type' => 'button_set',
                    'title' => __('Job Alerts', 'wp-jobsearch'),
                    'subtitle' => __('Switch On/Off Job Alerts.', 'wp-jobsearch'),
                    'desc' => '',
                    'options' => array(
                        'on' => __('On', 'wp-jobsearch'),
                        'off' => __('Off', 'wp-jobsearch'),
                    ),
                    'default' => 'off',
                ),
                array(
                    'id' => 'job-alerts-frequencies-section',
                    'type' => 'section',
                    'title' => __('Set Alert Frequencies', 'wp-jobsearch'),
                    'subtitle' => '',
                    'indent' => true,
                ),
                array(
                    'id' => 'job_alerts_frequency_annually',
                    'type' => 'button_set',
                    'title' => __('Annually', 'wp-jobsearch'),
                    'subtitle' => __('Do you want to allow user to set alert frequency to annually?', 'wp-jobsearch'),
                    'desc' => '',
                    'options' => array(
                        'on' => __('Yes', 'wp-jobsearch'),
                        'off' => __('No', 'wp-jobsearch'),
                    ),
                    'default' => 'off',
                ),
                array(
                    'id' => 'job_alerts_frequency_biannually',
                    'type' => 'button_set',
                    'title' => __('Biannually', 'wp-jobsearch'),
                    'subtitle' => __('Do you want to allow user to set alert frequency to biannually?', 'wp-jobsearch'),
                    'desc' => '',
                    'options' => array(
                        'on' => __('Yes', 'wp-jobsearch'),
                        'off' => __('No', 'wp-jobsearch'),
                    ),
                    'default' => 'off',
                ),
                array(
                    'id' => 'job_alerts_frequency_monthly',
                    'type' => 'button_set',
                    'title' => __('Monthly', 'wp-jobsearch'),
                    'subtitle' => __('Do you want to allow user to set alert frequency to monthly?', 'wp-jobsearch'),
                    'desc' => '',
                    'options' => array(
                        'on' => __('Yes', 'wp-jobsearch'),
                        'off' => __('No', 'wp-jobsearch'),
                    ),
                    'default' => 'off',
                ),
                array(
                    'id' => 'job_alerts_frequency_fortnightly',
                    'type' => 'button_set',
                    'title' => __('Fortnightly', 'wp-jobsearch'),
                    'subtitle' => __('Do you want to allow user to set alert frequency to fortnightly?', 'wp-jobsearch'),
                    'desc' => '',
                    'options' => array(
                        'on' => __('Yes', 'wp-jobsearch'),
                        'off' => __('No', 'wp-jobsearch'),
                    ),
                    'default' => 'off',
                ),
                array(
                    'id' => 'job_alerts_frequency_weekly',
                    'type' => 'button_set',
                    'title' => __('Weekly', 'wp-jobsearch'),
                    'subtitle' => __('Do you want to allow user to set alert frequency to weekly?', 'wp-jobsearch'),
                    'desc' => '',
                    'options' => array(
                        'on' => __('Yes', 'wp-jobsearch'),
                        'off' => __('No', 'wp-jobsearch'),
                    ),
                    'default' => 'off',
                ),
                array(
                    'id' => 'job_alerts_frequency_daily',
                    'type' => 'button_set',
                    'title' => __('Daily', 'wp-jobsearch'),
                    'subtitle' => __('Do you want to allow user to set alert frequency to daily?', 'wp-jobsearch'),
                    'desc' => '',
                    'options' => array(
                        'on' => __('Yes', 'wp-jobsearch'),
                        'off' => __('No', 'wp-jobsearch'),
                    ),
                    'default' => 'off',
                ),
                array(
                    'id' => 'job_alerts_frequency_never',
                    'type' => 'button_set',
                    'title' => __('Never', 'wp-jobsearch'),
                    'subtitle' => __('Do you want to allow user to set alert frequency to never?', 'wp-jobsearch'),
                    'desc' => '',
                    'options' => array(
                        'on' => __('Yes', 'wp-jobsearch'),
                        'off' => __('No', 'wp-jobsearch'),
                    ),
                    'default' => 'off',
                ),
            ),
        );
        return $sections;
    }

    public function array_insert($array, $values, $offset) {
        return array_slice($array, 0, $offset, true) + $values + array_slice($array, $offset, NULL, true);
    }

    public function vc_shortcode_params_add($params = array()) {
        global $jobsearch_plugin_options;
        $job_alerts_switch = isset($jobsearch_plugin_options['job_alerts_switch']) ? $jobsearch_plugin_options['job_alerts_switch'] : '';

        if ($job_alerts_switch == 'on') {
            $new_element = array(
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__("Job Alerts Top", "wp-jobsearch"),
                    'param_name' => 'job_alerts_top',
                    'value' => array(
                        esc_html__("No", "wp-jobsearch") => 'no',
                        esc_html__("Yes", "wp-jobsearch") => 'yes',
                    ),
                    'description' => esc_html__("Show/hide job alerts section at top of listings.", "wp-jobsearch"),
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__("Job Alerts", "wp-jobsearch"),
                    'param_name' => 'job_alerts',
                    'value' => array(
                        esc_html__("Yes", "wp-jobsearch") => 'yes',
                        esc_html__("No", "wp-jobsearch") => 'no',
                    ),
                    'description' => esc_html__("Show/hide job alerts section in filters of listings.", "wp-jobsearch"),
                    'group' => esc_html__("Filters Settings", "wp-jobsearch"),
                )
            );
            array_splice($params, 4, 0, $new_element);
        }

        return $params;
    }

    public function editor_shortcode_params_add($params = array()) {
        global $jobsearch_plugin_options;
        $job_alerts_switch = isset($jobsearch_plugin_options['job_alerts_switch']) ? $jobsearch_plugin_options['job_alerts_switch'] : '';

        if ($job_alerts_switch == 'on') {
            $new_element = array(
                'job_alerts' => array(
                    'type' => 'select',
                    'label' => esc_html__('Job Alerts', 'wp-jobsearch'),
                    'desc' => esc_html__('Show/hide job alerts section in filters of listings.', 'wp-jobsearch'),
                    'options' => array(
                        'yes' => esc_html__('Yes', 'wp-jobsearch'),
                        'no' => esc_html__('No', 'wp-jobsearch'),
                    )
                ),
            );
            $params = $this->array_insert($params, $new_element, 3);
        }

        return $params;
    }

    public function editor_shortcode_top_params_add($params = array()) {
        global $jobsearch_plugin_options;
        $job_alerts_switch = isset($jobsearch_plugin_options['job_alerts_switch']) ? $jobsearch_plugin_options['job_alerts_switch'] : '';

        if ($job_alerts_switch == 'on') {
            $new_element = array(
                'job_alerts_top' => array(
                    'type' => 'select',
                    'label' => esc_html__('Job Alerts Top', 'wp-jobsearch'),
                    'desc' => esc_html__('Show/hide job alerts section at top of listings.', 'wp-jobsearch'),
                    'options' => array(
                        'no' => esc_html__('No', 'wp-jobsearch'),
                        'yes' => esc_html__('Yes', 'wp-jobsearch'),
                    )
                ),
            );
            $params = $this->array_insert($params, $new_element, 2);
        }

        return $params;
    }

    public function dashboard_menu_items_ext($html = '', $get_tab = '', $page_url = '') {
        global $jobsearch_plugin_options;
        $job_alerts_switch = isset($jobsearch_plugin_options['job_alerts_switch']) ? $jobsearch_plugin_options['job_alerts_switch'] : '';

        if ($job_alerts_switch == 'on') {
            ob_start();

            $user_id = get_current_user_id();
            $is_employer = jobsearch_user_is_employer($user_id);
            if (!$is_employer) {
                ?>
                <li<?php echo ($get_tab == 'job-alerts' ? ' class="active"' : '') ?>>
                    <a href="<?php echo add_query_arg(array('tab' => 'job-alerts'), $page_url) ?>">
                        <i class="jobsearch-icon jobsearch-alarm"></i>
                        <?php esc_html_e('Job Alerts', 'wp-jobsearch') ?>
                    </a>
                </li>
                <?php
            }
            $html .= ob_get_clean();
        }

        return $html;
    }

    public function dashboard_tab_content_add($html = '', $get_tab = '') {
        global $jobsearch_plugin_options, $Jobsearch_User_Dashboard_Settings;
        $job_alerts_switch = isset($jobsearch_plugin_options['job_alerts_switch']) ? $jobsearch_plugin_options['job_alerts_switch'] : '';

        $user_id = get_current_user_id();
        $is_employer = jobsearch_user_is_employer($user_id);
        if ($job_alerts_switch == 'on' && $get_tab == 'job-alerts' && !$is_employer) {
            wp_enqueue_script('jobsearch-job-alerts-scripts');
            $user_id = get_current_user_id();
            $page_id = isset($jobsearch_plugin_options['user-dashboard-template-page']) ? $jobsearch_plugin_options['user-dashboard-template-page'] : '';
            $page_id = jobsearch__get_post_id($page_id, 'page');
            $page_url = jobsearch_wpml_lang_page_permalink($page_id, 'page'); //get_permalink($page_id);
            $reults_per_page = isset($jobsearch_plugin_options['user-dashboard-per-page']) && $jobsearch_plugin_options['user-dashboard-per-page'] > 0 ? $jobsearch_plugin_options['user-dashboard-per-page'] : 10;

            $page_num = isset($_GET['page_num']) ? $_GET['page_num'] : 1;
            ob_start();
            ?>
            <div class="jobsearch-employer-box-section">
                <div class="jobsearch-profile-title">
                    <h2><?php esc_html_e('Job Alerts', 'wp-jobsearch') ?></h2>
                </div>
                <div class="jobsearch-job-alerts">
                    <div class="jobsearch-job-alerts-wrap">
                        <?php
                        $args = array(
                            'author' => $user_id,
                            'post_type' => 'job-alert',
                            'posts_per_page' => $reults_per_page,
                            'paged' => $page_num,
                            'orderby' => 'post_date',
                            'order' => 'DESC',
                        );
                        $job_alerts = new WP_Query($args);

                        $total_jobs = $job_alerts->found_posts;

                        if ($job_alerts->have_posts()) {
                            ?>
                            <table>
                                <thead>
                                    <tr>
                                        <th><?php esc_html_e('Title', 'wp-jobsearch') ?></th>
                                        <th><?php esc_html_e('Criteria', 'wp-jobsearch') ?></th>
                                        <th><?php esc_html_e('Created Date', 'wp-jobsearch') ?></th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    while ($job_alerts->have_posts()) : $job_alerts->the_post();

                                        $alert_id = get_the_ID();

                                        $search_criteria = get_post_meta($alert_id, 'jobsearch_field_alert_query', true);
                                        $alert_page_url = get_post_meta($alert_id, 'jobsearch_field_alert_page_url', true);
                                        ?>
                                        <tr>
                                            <td>
                                                <span><?php echo get_the_title($alert_id) ?></span>
                                            </td>
                                            <td><?php echo $this->alert_criteria_breakdown($search_criteria) ?></td>
                                            <td><?php echo get_the_date() ?></td>
                                            <td>
                                                <a href="javascript:void(0);" class="jobsearch-savedjobs-links jobsearch-del-user-job-alert" data-id="<?php echo ($alert_id) ?>"><i class="jobsearch-icon jobsearch-rubbish"></i></a>
                                                <a href="<?php echo ($alert_page_url) ?>" class="jobsearch-savedjobs-links"><i class="jobsearch-icon jobsearch-view"></i></a>
                                            </td>
                                        </tr>
                                        <?php
                                    endwhile;
                                    wp_reset_postdata();
                                    ?>
                                </tbody>
                            </table>
                            <?php
                            $total_pages = 1;
                            if ($total_jobs > 0 && $reults_per_page > 0 && $total_jobs > $reults_per_page) {
                                $total_pages = ceil($total_jobs / $reults_per_page);
                                ?>
                                <div class="jobsearch-pagination-blog">
                                    <?php $Jobsearch_User_Dashboard_Settings->pagination($total_pages, $page_num, $page_url) ?>
                                </div>
                                <?php
                            }
                        } else {
                            echo '<p>' . esc_html__('No record found.', 'wp-jobsearch') . '</p>';
                        }
                        ?>
                    </div>
                </div>
            </div>
            <?php
            $html .= ob_get_clean();
        }

        return $html;
    }

    public function after_jobs_listing_callback($jobs_query, $sort_by) {
        echo '<div class="jobs_query" style="display:none;">' . json_encode($jobs_query) . '</div>';
    }

    public function frontend_before_listings_ui_callback($args = array()) {
        global $jobsearch_plugin_options;

        $job_alerts_switch = isset($jobsearch_plugin_options['job_alerts_switch']) ? $jobsearch_plugin_options['job_alerts_switch'] : '';

        $sh_atts = isset($args['sh_atts']) ? $args['sh_atts'] : '';
        $job_alerts_param = isset($sh_atts['job_alerts_top']) ? $sh_atts['job_alerts_top'] : '';

        if ($job_alerts_param == 'yes' && $job_alerts_switch == 'on') {
            wp_enqueue_script('jobsearch-job-alerts-scripts');

            $frequencies = array(
                'job_alerts_frequency_daily' => esc_html__('Daily', 'wp-jobsearch'),
                'job_alerts_frequency_weekly' => esc_html__('Weekly', 'wp-jobsearch'),
                'job_alerts_frequency_fortnightly' => esc_html__('Fortnightly', 'wp-jobsearch'),
                'job_alerts_frequency_monthly' => esc_html__('Monthly', 'wp-jobsearch'),
                'job_alerts_frequency_biannually' => esc_html__('Biannually', 'wp-jobsearch'),
                'job_alerts_frequency_annually' => esc_html__('Annually', 'wp-jobsearch'),
                'job_alerts_frequency_never' => esc_html__('Never', 'wp-jobsearch'),
            );
            $options_str = '';
            $is_one_checked = false;
            $checked = 'checked="checked"';
            foreach ($frequencies as $frequency => $label) {

                $rand_id = rand(10000000, 99999999);
                if (isset($jobsearch_plugin_options[$frequency]) && 'on' == $jobsearch_plugin_options[$frequency]) {
                    $options_str .= '<li><input id="frequency' . $rand_id . '" name="alert-frequency" class="radio-frequency" maxlength="75" type="radio" value="' . ($frequency) . '" ' . $checked . '> <label for="frequency' . $rand_id . '"><span></span>' . $label . '</label></li>';
                    if (false == $is_one_checked) {
                        $checked = '';
                        $is_one_checked = true;
                    }
                }
            }

            $user = wp_get_current_user();
            $disabled = '';
            $email = '';
            if ($user->ID > 0) {
                $email = $user->user_email;
                $disabled = ' disabled="disabled"';
            }
            echo '
            <div class="jobsearch-alert-in-content job-alerts-sec">
            <div class="email-me-top">
                <button class="email-jobs-top"><i class="fa fa-envelope"></i> ' . esc_html__('Email me new jobs', 'wp-jobsearch') . '</button>
            </div>
            <div class="jobsearch-search-filter-wrap jobsearch-without-toggle jobsearch-add-padding">
            <div class="job-alert-box job-alert job-alert-container-top">
                <div class="alerts-fields">
                    <ul>
                        <li>
                            <input name="alerts-name" placeholder="' . esc_html__('Job alert name...', 'wp-jobsearch') . '" class="name-input-top" maxlength="75" type="text">
                        </li>
                        <li>
                            <input type="email" class="email-input-top alerts-email" placeholder=' . esc_html__("example@email.com", 'wp-jobsearch') . ' name="alerts-email" value="' . $email . '" ' . $disabled . '>
                        </li>
                        <li>
                            <button class="jobalert-submit" type="submit">' . esc_html__('Save Job Alert', 'wp-jobsearch') . '</button>
                        </li>
                    </ul>
                </div>' . (
                strlen($options_str) == 0 ? '' : (
                        '<div class="alert-frequency">
                            <ul class="jobsearch-checkbox">
                            ' . $options_str . '
                            </ul>
                        </div>'
                        )
                ) .
                '<div class="validation error" style="display:none;">
                    <label for="alerts-email-top">' . esc_html__('Please enter a valid email', 'wp-jobsearch') . '</label>
                </div>
            </div>
            </div>
            </div>';
        }
    }

    public function alert_criteria_breakdown($criteria) {
        $html = '';
        $items_html = '';
        if ($criteria != '') {
            $disalow_keys = array('ajax_filter', 'posted', 'sort-by', 'alerts-name');
            $criteria_arr = explode('&', $criteria);
            if (!empty($criteria_arr)) {
                $criteria_arr = array_unique($criteria_arr);
                foreach ($criteria_arr as $crite_item) {
                    $item_expl = explode('=', $crite_item);
                    if (isset($item_expl[0]) && isset($item_expl[1]) && $item_expl[0] != '' && $item_expl[1] != '') {
                        $item_key = $item_expl[0];
                        $item_val = $item_expl[1];
                        if (!in_array($item_key, $disalow_keys)) {
                            if (strpos($item_val, 'job_alerts_frequency_') !== false) {
                                $item_val = str_replace('job_alerts_frequency_', '', $item_val);
                            }
                            $items_html .= '<li>' . $item_key . '=' . $item_val . '</li>';
                        }
                    }
                }
                if ($items_html != '') {
                    $html .= '<ul>' . $items_html . '</ul>' . "\n";
                }
            }
            //
        }
        return $html;
    }

    public function frontend_before_filters_ui_callback($args = array()) {
        global $jobsearch_plugin_options;

        $job_alerts_switch = isset($jobsearch_plugin_options['job_alerts_switch']) ? $jobsearch_plugin_options['job_alerts_switch'] : '';

        $sh_atts = isset($args['sh_atts']) ? $args['sh_atts'] : '';
        $job_alerts_param = isset($sh_atts['job_alerts']) ? $sh_atts['job_alerts'] : '';

        if ($job_alerts_param != 'no' && $job_alerts_switch == 'on') {
            wp_enqueue_script('jobsearch-job-alerts-scripts');

            $frequencies = array(
                'job_alerts_frequency_daily' => esc_html__('Daily', 'wp-jobsearch'),
                'job_alerts_frequency_weekly' => esc_html__('Weekly', 'wp-jobsearch'),
                'job_alerts_frequency_fortnightly' => esc_html__('Fortnightly', 'wp-jobsearch'),
                'job_alerts_frequency_monthly' => esc_html__('Monthly', 'wp-jobsearch'),
                'job_alerts_frequency_biannually' => esc_html__('Biannually', 'wp-jobsearch'),
                'job_alerts_frequency_annually' => esc_html__('Annually', 'wp-jobsearch'),
                'job_alerts_frequency_never' => esc_html__('Never', 'wp-jobsearch'),
            );
            $options_str = '';
            $is_one_checked = false;
            $checked = 'checked="checked"';
            foreach ($frequencies as $frequency => $label) {

                $rand_id = rand(10000000, 99999999);
                if (isset($jobsearch_plugin_options[$frequency]) && 'on' == $jobsearch_plugin_options[$frequency]) {
                    $options_str .= '<li><input id="frequency' . $rand_id . '" name="alert-frequency" class="radio-frequency" maxlength="75" type="radio" value="' . ($frequency) . '" ' . $checked . '> <label for="frequency' . $rand_id . '"><span></span>' . $label . '</label></li>';
                    if (false == $is_one_checked) {
                        $checked = '';
                        $is_one_checked = true;
                    }
                }
            }

            $user = wp_get_current_user();
            $disabled = '';
            $email = '';
            if ($user->ID > 0) {
                $email = $user->user_email;
                $disabled = ' disabled="disabled"';
            }
            echo '
            <div class="jobsearch-filter-responsive-wrap job-alerts-sec">
            <div class="email-me-top">
                <button class="email-jobs-top"><i class="fa fa-envelope"></i> ' . esc_html__('Email me new jobs', 'wp-jobsearch') . '</button>
            </div>
            <div class="jobsearch-search-filter-wrap jobsearch-without-toggle jobsearch-add-padding">
            <div class="job-alert-box job-alert job-alert-container-top">
                    <div class="alerts-fields">
                        <input name="alerts-name" placeholder="' . esc_html__('Job alert name...', 'wp-jobsearch') . '" class="name-input-top" maxlength="75" type="text">
                        <input type="email" class="email-input-top alerts-email" placeholder=' . esc_html__("example@email.com", 'wp-jobsearch') . ' name="alerts-email" value="' . $email . '" ' . $disabled . '>
                    </div>' . (
                strlen($options_str) == 0 ? '' : (
                        '<div class="alert-frequency">
                            <ul class="jobsearch-checkbox">
                            ' . $options_str . '
                            </ul>
                        </div>'
                        )
                ) .
                '<div class="validation error" style="display:none;">
                    <label for="alerts-email-top">' . esc_html__('Please enter a valid email', 'wp-jobsearch') . '</label>
                </div>
                <button class="jobalert-submit" type="submit">' . esc_html__('Save Job Alert', 'wp-jobsearch') . '</button>
            </div>
            </div>
            </div>';
        }
    }

    public function create_job_alert_callback() {

        global $sitepress;
        
        // Read data from user input.
        $email = sanitize_text_field($_POST['email']);
        $name = sanitize_text_field($_POST['name']);
        $location = sanitize_text_field($_POST['window_location']);
        $query = end(explode('?', $location));
        $frequency = sanitize_text_field($_POST['frequency']);
        if ($frequency != '' && strpos($frequency, 'job_alerts_frequency_') !== false) {
            $frequency = str_replace('job_alerts_frequency_', 'alert_', $frequency);
        }

        $jobs_query = isset($_POST['search_query']) ? $_POST['search_query'] : '';

        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            $is_employer = jobsearch_user_is_employer($user_id);
            if ($is_employer) {
                $return = array('success' => false, "message" => esc_html__("You cannot create job alert.", 'wp-jobsearch'));
                echo json_encode($return);
                wp_die();
            }
        }

        if (empty($name) || empty($email) || empty($query) || empty($frequency)) {
            $return = array('success' => false, "message" => esc_html__("Provided data is incomplete.", 'wp-jobsearch'));
        } else {
            $meta_query = array(
                array(
                    'key' => 'jobsearch_field_alert_email',
                    'value' => $email,
                    'compare' => '=',
                ),
                array(
                    'key' => 'jobsearch_field_' . $frequency,
                    'value' => 'on',
                    'compare' => '=',
                ),
            );
            if ($jobs_query <> '') {
                $meta_query[] = array(
                    'key' => 'jobsearch_field_alert_jobs_query',
                    'value' => stripslashes($jobs_query),
                    'compare' => '=',
                );
            }
            $args = array(
                'post_type' => 'job-alert',
                'meta_query' => $meta_query,
            );
            $obj_query = new WP_Query($args);
            $count = $obj_query->post_count;
            if ($count > 0) {
                $return = array('success' => false, "message" => esc_html__("Alert already exists with this criteria", 'wp-jobsearch'));
            } else {
                // Insert Job Alert as a post.
                $job_alert_data = array(
                    'post_title' => $name,
                    'post_status' => 'publish',
                    'post_type' => 'job-alert',
                    'comment_status' => 'closed',
                    'post_author' => get_current_user_id(),
                );
                $job_alert_id = wp_insert_post($job_alert_data);
                if (function_exists('icl_object_id')) {
                    $lang_code = $sitepress->get_default_language();
                    $lang_code = $lang_code;
                    $sitepress->set_element_language_details($job_alert_id, 'post_job-alert', false, $lang_code);
                }

                // Update email.
                update_post_meta($job_alert_id, 'jobsearch_field_alert_email', $email);
                // Update name.
                update_post_meta($job_alert_id, 'jobsearch_field_alert_name', $name);
                // Update frequencies.
                update_post_meta($job_alert_id, 'jobsearch_field_' . $frequency, 'on');

                // Update query.
                update_post_meta($job_alert_id, 'jobsearch_field_alert_query', $query);

                // Update listings url.
                update_post_meta($job_alert_id, 'jobsearch_field_alert_page_url', $location);

                // Last time email sent.
                update_post_meta($job_alert_id, 'last_time_email_sent', 0);

                // Query.
                update_post_meta($job_alert_id, 'jobsearch_field_alert_jobs_query', stripslashes($jobs_query));

                $return = array('success' => true, "message" => esc_html__("Job alert successfully added.", 'wp-jobsearch'));
            }
        }
        echo json_encode($return);
        wp_die();
    }

    public function unsubscribe_job_alert() {
        if (isset($_REQUEST['jaid'])) {
            $job_alert_id = sanitize_text_field($_REQUEST['jaid']);
            $post_data = get_post($job_alert_id);
            if ($post_data) {
                wp_delete_post($job_alert_id, true);
                echo '<div class="job_alert_unsubscribe_msg" style="text-align: center;"><h3>' . esc_html__('Job alert successfully unsubscribed.', 'wp-jobsearch') . '</h3></div>';
            } else {
                echo '<div class="job_alert_unsubscribe_msg" style="text-align: center;"><h3>' . esc_html__('Sorry! Job alert already unsubscribed.', 'wp-jobsearch') . '</h3></div>';
            }
        }
        die();
    }

    public function remove_job_alert() {
        if (isset($_REQUEST['alert_id'])) {
            if (jobsearch_candidate_not_allow_to_mod()) {
                $msg = esc_html__('You are not allowed to delete this.', 'wp-jobsearch');
                echo json_encode(array('msg' => $msg));
                die;
            }
            if (jobsearch_employer_not_allow_to_mod()) {
                $msg = esc_html__('You are not allowed to delete this.', 'wp-jobsearch');
                echo json_encode(array('msg' => $msg));
                die;
            }
            $job_alert_id = sanitize_text_field($_REQUEST['alert_id']);
            $post_data = get_post($job_alert_id);
            if ($post_data) {
                wp_delete_post($job_alert_id, true);
            }
        }
        die();
    }

}

// Class JobSearch_Job_Alerts_Hooks
$JobSearch_Job_Alerts_Hooks_obj = new JobSearch_Job_Alerts_Hooks();
global $JobSearch_Job_Alerts_Hooks_obj;
