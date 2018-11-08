<?php
global $jobsearch_plugin_options, $Jobsearch_User_Dashboard_Settings;
$user_id = get_current_user_id();
$user_obj = get_user_by('ID', $user_id);

$page_id = $user_dashboard_page = isset($jobsearch_plugin_options['user-dashboard-template-page']) ? $jobsearch_plugin_options['user-dashboard-template-page'] : '';
$page_id = $user_dashboard_page = jobsearch__get_post_id($user_dashboard_page, 'page');
$page_url = jobsearch_wpml_lang_page_permalink($page_id, 'page'); //get_permalink($page_id);

$employer_id = jobsearch_get_user_employer_id($user_id);

$reults_per_page = isset($jobsearch_plugin_options['user-dashboard-per-page']) && $jobsearch_plugin_options['user-dashboard-per-page'] > 0 ? $jobsearch_plugin_options['user-dashboard-per-page'] : 10;

$page_num = isset($_GET['page_num']) ? $_GET['page_num'] : 1;

if ($employer_id > 0) {
    ?>
    <div class="jobsearch-employer-dasboard">
        <div class="jobsearch-employer-box-section">

            <div class="jobsearch-profile-title">
                <h2><?php esc_html_e('Packages', 'wp-jobsearch') ?></h2>
            </div>
            <?php
            $args = array(
                'post_type' => 'shop_order',
                'posts_per_page' => $reults_per_page,
                'paged' => $page_num,
                'post_status' => 'wc-completed',
                'order' => 'DESC',
                'orderby' => 'ID',
                'meta_query' => array(
                    array(
                        'key' => 'jobsearch_order_attach_with',
                        'value' => 'package',
                        'compare' => '=',
                    ),
                    array(
                        'key' => 'package_type',
                        'value' => apply_filters('jobsearch_emp_dash_pkg_types_in_query', array('job', 'cv')),
                        'compare' => 'IN',
                    ),
                    array(
                        'key' => 'jobsearch_order_user',
                        'value' => $user_id,
                        'compare' => '=',
                    ),
                ),
            );
            $pkgs_query = new WP_Query($args);
            $total_pkgs = $pkgs_query->found_posts;
            if ($pkgs_query->have_posts()) {
                ?>
                <div class="jobsearch-packages-list-holder">
                    <div class="jobsearch-employer-packages">
                        <div class="jobsearch-table-layer jobsearch-packages-thead">
                            <div class="jobsearch-table-row">
                                <div class="jobsearch-table-cell"><?php esc_html_e('Order ID', 'wp-jobsearch') ?></div>
                                <div class="jobsearch-table-cell"><?php esc_html_e('Package', 'wp-jobsearch') ?></div>
                                <div class="jobsearch-table-cell"><?php echo apply_filters('jobsearch_emp_dash_pkg_job_num_label', esc_html__('Total Jobs/CVs', 'wp-jobsearch')) ?></div>
                                <div class="jobsearch-table-cell"><?php esc_html_e('Used', 'wp-jobsearch') ?></div>
                                <div class="jobsearch-table-cell"><?php esc_html_e('Remaining', 'wp-jobsearch') ?></div>
                                <div class="jobsearch-table-cell"><?php esc_html_e('Package Expiry', 'wp-jobsearch') ?></div>
                                <div class="jobsearch-table-cell"><?php esc_html_e('Satus', 'wp-jobsearch') ?></div>
                            </div>
                        </div>
                        <?php
                        while ($pkgs_query->have_posts()) : $pkgs_query->the_post();
                            $pkg_rand = rand(10000000, 99999999);
                            $pkg_order_id = get_the_ID();
                            $pkg_order_name = get_post_meta($pkg_order_id, 'package_name', true);

                            //
                            $pkg_type = get_post_meta($pkg_order_id, 'package_type', true);

                            if ($pkg_type == 'cv') {
                                $total_cvs = get_post_meta($pkg_order_id, 'num_of_cvs', true);

                                $used_cvs = jobsearch_pckg_order_used_cvs($pkg_order_id);
                                $remaining_cvs = jobsearch_pckg_order_remaining_cvs($pkg_order_id);
                            } else {
                                $total_jobs = get_post_meta($pkg_order_id, 'num_of_jobs', true);
                                $total_jobs = apply_filters('jobsearch_emp_dash_pkg_total_jobs_count', $total_jobs, $pkg_order_id);

                                $job_exp_dur = get_post_meta($pkg_order_id, 'job_expiry_time', true);
                                $job_exp_dur_unit = get_post_meta($pkg_order_id, 'job_expiry_time_unit', true);

                                $used_jobs = jobsearch_pckg_order_used_jobs($pkg_order_id);
                                $used_jobs = apply_filters('jobsearch_emp_dash_pkg_used_jobs_count', $used_jobs, $pkg_order_id);
                                $remaining_jobs = jobsearch_pckg_order_remaining_jobs($pkg_order_id);
                                $remaining_jobs = apply_filters('jobsearch_emp_dash_pkg_remain_jobs_count', $remaining_jobs, $pkg_order_id);
                            }
                            $pkg_exp_dur = get_post_meta($pkg_order_id, 'package_expiry_time', true);
                            $pkg_exp_dur_unit = get_post_meta($pkg_order_id, 'package_expiry_time_unit', true);

                            $status_txt = esc_html__('Active', 'wp-jobsearch');
                            $status_class = '';
                            if ($pkg_type == 'cv') {
                                if (jobsearch_cv_pckg_order_is_expired($pkg_order_id)) {
                                    $status_txt = esc_html__('Expired', 'wp-jobsearch');
                                    $status_class = 'jobsearch-packages-pending';
                                }
                            } else {
                                if (jobsearch_pckg_order_is_expired($pkg_order_id)) {
                                    $status_txt = esc_html__('Expired', 'wp-jobsearch');
                                    $status_class = 'jobsearch-packages-pending';
                                }
                                $status_txt = apply_filters('jobsearch_emp_dash_jobpkgs_list_status_txt', $status_txt, $pkg_order_id);
                                $status_class = apply_filters('jobsearch_emp_dash_jobpkgs_list_status_class', $status_class, $pkg_order_id);
                            }
                            ?>
                            <div class="jobsearch-table-layer jobsearch-packages-tbody">
                                <div class="jobsearch-table-row">
                                    <div class="jobsearch-table-cell">#<?php echo ($pkg_order_id) ?></div>
                                    <div class="jobsearch-table-cell">
                                        <?php
                                        ob_start();
                                        ?>
                                        <span><?php echo ($pkg_order_name) ?></span>
                                        <?php
                                        $pkg_name_html = ob_get_clean();
                                        echo apply_filters('jobsearch_emp_dashboard_pkgs_list_pkg_title', $pkg_name_html, $pkg_order_id);
                                        ?>
                                    </div>
                                    <?php
                                    if ($pkg_type == 'cv') {
                                        ?>
                                        <div class="jobsearch-table-cell"><?php echo ($total_cvs) ?></div>
                                        <div class="jobsearch-table-cell"><?php echo ($used_cvs) ?></div>
                                        <div class="jobsearch-table-cell"><?php echo ($remaining_cvs) ?></div>
                                        <?php
                                    } else {
                                        ?>
                                        <div class="jobsearch-table-cell"><?php echo ($total_jobs) ?></div>
                                        <div class="jobsearch-table-cell"><?php echo ($used_jobs) ?></div>
                                        <div class="jobsearch-table-cell"><?php echo ($remaining_jobs) ?></div>
                                        <?php
                                    }
                                    ?>
                                    <div class="jobsearch-table-cell"><?php echo absint($pkg_exp_dur) . ' ' . jobsearch_get_duration_unit_str($pkg_exp_dur_unit) ?></div>
                                    <div class="jobsearch-table-cell"><i class="fa fa-circle <?php echo ($status_class) ?>"></i> <?php echo ($status_txt) ?></div>
                                </div>
                            </div>
                            <?php
                        endwhile;
                        wp_reset_postdata();
                        ?>
                    </div>
                </div>
                <?php
                $total_pages = 1;
                if ($total_pkgs > 0 && $reults_per_page > 0 && $total_pkgs > $reults_per_page) {
                    $total_pages = ceil($total_pkgs / $reults_per_page);
                    ?>
                    <div class="jobsearch-pagination-blog">
                        <?php $Jobsearch_User_Dashboard_Settings->pagination($total_pages, $page_num, $page_url) ?>
                    </div>
                    <?php
                }
            } else {
                ?>
                <p><?php esc_html_e('No record found.', 'wp-jobsearch') ?></p>
                <?php
            }
            ?>
        </div>
    </div>
    <?php
}    