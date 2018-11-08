<?php
add_action('jobsearch_user_account_links', 'jobsearch_user_account_links', 10, 1);

function jobsearch_user_account_links($args = array()) {
    global $jobsearch_plugin_options;

    $is_popup = isset($args['is_popup']) ? $args['is_popup'] : '';

    $jobsearch_login_page = isset($jobsearch_plugin_options['user-login-template-page']) ? $jobsearch_plugin_options['user-login-template-page'] : '';
    $jobsearch_login_page = jobsearch__get_post_id($jobsearch_login_page, 'page');
    $user_dashboard_page = isset($jobsearch_plugin_options['user-dashboard-template-page']) ? $jobsearch_plugin_options['user-dashboard-template-page'] : '';
    $user_dashboard_page = jobsearch__get_post_id($user_dashboard_page, 'page');

    $page_id = $user_dashboard_page = isset($jobsearch_plugin_options['user-dashboard-template-page']) ? $jobsearch_plugin_options['user-dashboard-template-page'] : '';
    $page_id = $user_dashboard_page = jobsearch__get_post_id($user_dashboard_page, 'page');
    //$page_url = get_permalink($page_id);
    $page_url = jobsearch_wpml_lang_page_permalink($page_id, 'page');

    $pckg_transaction_links = isset($jobsearch_plugin_options['packages_menu_links']) ? $jobsearch_plugin_options['packages_menu_links'] : '';

    $get_tab = isset($_REQUEST['tab']) ? $_REQUEST['tab'] : '';

    if (is_user_logged_in()) {
        $user_id = get_current_user_id();
        if (isset($user_dashboard_page) && $user_dashboard_page != '') {
            $user_is_candidate = jobsearch_user_is_candidate($user_id);
            $user_is_employer = jobsearch_user_is_employer($user_id);
            ?>
            <li>
                <a href="<?php echo esc_url(get_permalink($user_dashboard_page)) ?>" class="jobsearch-color active"><?php echo esc_html__('My Account', 'wp-jobsearch'); ?></a>
                <ul>
                    <?php
                    if ($user_is_employer) {
                        ?>
                        <li<?php echo ($get_tab == '' ? ' class="active"' : '') ?>>
                            <a href="<?php echo ($page_url) ?>">
                                <i class="jobsearch-icon jobsearch-group"></i>
                                <?php esc_html_e('Dashboard', 'wp-jobsearch') ?>
                            </a>
                        </li>
                        <li<?php echo ($get_tab == 'dashboard-settings' ? ' class="active"' : '') ?>>
                            <a href="<?php echo add_query_arg(array('tab' => 'dashboard-settings'), $page_url) ?>">
                                <i class="jobsearch-icon jobsearch-user"></i>
                                <?php esc_html_e('Company Profile', 'wp-jobsearch') ?>
                            </a>
                        </li>
                        <li<?php echo ($get_tab == 'user-job' ? ' class="active"' : '') ?>>
                            <a href="<?php echo add_query_arg(array('tab' => 'user-job'), $page_url) ?>">
                                <i class="jobsearch-icon jobsearch-plus"></i>
                                <?php esc_html_e('Post a New Job', 'wp-jobsearch') ?>
                            </a>
                        </li>
                        <li<?php echo ($get_tab == 'manage-jobs' ? ' class="active"' : '') ?>>
                            <a href="<?php echo add_query_arg(array('tab' => 'manage-jobs'), $page_url) ?>">
                                <i class="jobsearch-icon jobsearch-briefcase-1"></i>
                                <?php esc_html_e('Manage Jobs', 'wp-jobsearch') ?>
                            </a>
                        </li>
                        <li<?php echo ($get_tab == 'user-resumes' ? ' class="active"' : '') ?>>
                            <a href="<?php echo add_query_arg(array('tab' => 'user-resumes'), $page_url) ?>">
                                <i class="jobsearch-icon jobsearch-heart"></i>
                                <?php esc_html_e('Shortlisted Resumes', 'wp-jobsearch') ?>
                            </a>
                        </li>
                        <?php
                        if ($pckg_transaction_links == 'on') {
                            ?>
                            <li<?php echo ($get_tab == 'user-packages' ? ' class="active"' : '') ?>>
                                <a href="<?php echo add_query_arg(array('tab' => 'user-packages'), $page_url) ?>">
                                    <i class="jobsearch-icon jobsearch-credit-card-1"></i>
                                    <?php esc_html_e('Packages', 'wp-jobsearch') ?>
                                </a>
                            </li>
                            <li<?php echo ($get_tab == 'user-transactions' ? ' class="active"' : '') ?>>
                                <a href="<?php echo add_query_arg(array('tab' => 'user-transactions'), $page_url) ?>">
                                    <i class="jobsearch-icon jobsearch-salary"></i>
                                    <?php esc_html_e('Transactions', 'wp-jobsearch') ?>
                                </a>
                            </li>
                            <?php
                        }
                        ?>
                        <?php echo apply_filters('jobsearch_dashboard_menu_items_ext', '', $get_tab, $page_url) ?>
                        <li<?php echo ($get_tab == 'change-password' ? ' class="active"' : '') ?>>
                            <a href="<?php echo add_query_arg(array('tab' => 'change-password'), $page_url) ?>">
                                <i class="jobsearch-icon jobsearch-multimedia"></i>
                                <?php esc_html_e('Change Password', 'wp-jobsearch') ?>
                            </a>
                        </li>
                        <?php
                    }
                    if ($user_is_candidate) {
                        ?>
                        <li<?php echo ($get_tab == '' ? ' class="active"' : '') ?>>
                            <a href="<?php echo ($page_url) ?>">
                                <i class="jobsearch-icon jobsearch-group"></i>
                                <?php esc_html_e('Dashboard', 'wp-jobsearch') ?>
                            </a>
                        </li>
                        <li<?php echo ($get_tab == 'dashboard-settings' ? ' class="active"' : '') ?>>
                            <a href="<?php echo add_query_arg(array('tab' => 'dashboard-settings'), $page_url) ?>">
                                <i class="jobsearch-icon jobsearch-user"></i>
                                <?php esc_html_e('My Profile', 'wp-jobsearch') ?>
                            </a>
                        </li>
                        <li<?php echo ($get_tab == 'my-resume' ? ' class="active"' : '') ?>>
                            <a href="<?php echo add_query_arg(array('tab' => 'my-resume'), $page_url) ?>">
                                <i class="jobsearch-icon jobsearch-resume"></i>
                                <?php esc_html_e('My Resume', 'wp-jobsearch') ?>
                            </a>
                        </li>
                        <li<?php echo ($get_tab == 'favourite-jobs' ? ' class="active"' : '') ?>>
                            <a href="<?php echo add_query_arg(array('tab' => 'favourite-jobs'), $page_url) ?>">
                                <i class="jobsearch-icon jobsearch-heart"></i>
                                <?php esc_html_e('Favourite Jobs', 'wp-jobsearch') ?>
                            </a>
                        </li>
                        <li<?php echo ($get_tab == 'cv-manager' ? ' class="active"' : '') ?>>
                            <a href="<?php echo add_query_arg(array('tab' => 'cv-manager'), $page_url) ?>">
                                <i class="jobsearch-icon jobsearch-id-card"></i>
                                <?php esc_html_e('CV Manager', 'wp-jobsearch') ?>
                            </a>
                        </li>
                        <li<?php echo ($get_tab == 'applied-jobs' ? ' class="active"' : '') ?>>
                            <a href="<?php echo add_query_arg(array('tab' => 'applied-jobs'), $page_url) ?>">
                                <i class="jobsearch-icon jobsearch-briefcase-1"></i>
                                <?php esc_html_e('Applied Jobs', 'wp-jobsearch') ?>
                            </a>
                        </li>
                        <?php
                        if ($pckg_transaction_links == 'on') {
                            ?>
                            <li<?php echo ($get_tab == 'user-packages' ? ' class="active"' : '') ?>>
                                <a href="<?php echo add_query_arg(array('tab' => 'user-packages'), $page_url) ?>">
                                    <i class="jobsearch-icon jobsearch-credit-card-1"></i>
                                    <?php esc_html_e('Packages', 'wp-jobsearch') ?>
                                </a>
                            </li>
                            <li<?php echo ($get_tab == 'user-transactions' ? ' class="active"' : '') ?>>
                                <a href="<?php echo add_query_arg(array('tab' => 'user-transactions'), $page_url) ?>">
                                    <i class="jobsearch-icon jobsearch-salary"></i>
                                    <?php esc_html_e('Transactions', 'wp-jobsearch') ?>
                                </a>
                            </li>
                            <?php
                        }
                        ?>
                        <?php echo apply_filters('jobsearch_dashboard_menu_items_ext', '', $get_tab, $page_url) ?>
                        <li<?php echo ($get_tab == 'change-password' ? ' class="active"' : '') ?>>
                            <a href="<?php echo add_query_arg(array('tab' => 'change-password'), $page_url) ?>">
                                <i class="jobsearch-icon jobsearch-multimedia"></i>
                                <?php esc_html_e('Change Password', 'wp-jobsearch') ?>
                            </a>
                        </li>
                        <?php
                    }
                    ?>
                    <li>
                        <a href="<?php echo wp_logout_url(home_url('/')); ?>">
                            <i class="jobsearch-icon jobsearch-logout"></i>
                            <?php esc_html_e('Logout', 'wp-jobsearch') ?>
                        </a>
                    </li>
                </ul>
            </li> 
            <?php
        }
    } else {
        $op_register_form_allow = isset($jobsearch_plugin_options['login_register_form']) ? $jobsearch_plugin_options['login_register_form'] : '';
        $op_cand_register_allow = isset($jobsearch_plugin_options['login_candidate_register']) ? $jobsearch_plugin_options['login_candidate_register'] : '';
        $op_emp_register_allow = isset($jobsearch_plugin_options['login_employer_register']) ? $jobsearch_plugin_options['login_employer_register'] : '';

        $register_link_view = true;
        if ($op_register_form_allow == 'off') {
            $register_link_view = false;
        }
        if ($op_cand_register_allow == 'no' && $op_emp_register_allow == 'no') {
            $register_link_view = false;
        }
        ob_start();
        if ($is_popup) {
            if ($register_link_view === true) {
                ?>
                <li><a href="javascript:void(0);" class="jobsearch-color jobsearch-open-register-tab"><?php echo esc_html__('Register', 'wp-jobsearch'); ?></a></li>
                <?php
            }
            ?>
            <li><a href="javascript:void(0);" class="jobsearch-color jobsearch-open-signin-tab"><?php echo esc_html__('Sign In', 'wp-jobsearch'); ?></a></li>
            <?php
        } else {
            if ($register_link_view === true) {
                ?>
                <li><a href="<?php echo esc_url(get_permalink($jobsearch_login_page)) ?>"><?php echo esc_html__('Register', 'wp-jobsearch'); ?></a></li>
                <?php
            }
            ?>
            <li><a href="<?php echo esc_url(get_permalink($jobsearch_login_page)) ?>"><?php echo esc_html__('Sign In', 'wp-jobsearch'); ?></a></li>
            <?php
        }
        $links_html = ob_get_clean();
        echo apply_filters('jobsearch_top_login_links', $links_html, $register_link_view);
    }
}

add_filter('wp_nav_menu_items', 'jobsearch_login_menu_items', 10, 2);

function jobsearch_login_menu_items($items, $args) {
    global $jobsearch_plugin_options;
    $menu_location = isset($jobsearch_plugin_options['user-login-links-menu']) ? $jobsearch_plugin_options['user-login-links-menu'] : '';
    $menu_links = isset($jobsearch_plugin_options['user-login-dashboard-links']) ? $jobsearch_plugin_options['user-login-dashboard-links'] : '';
    if ($menu_links == 'on' && $args->theme_location == $menu_location) {

        ob_start();
        do_action('jobsearch_user_account_links', array());
        $items_html = ob_get_clean();

        $items .= $items_html;
    }
    return $items;
}
