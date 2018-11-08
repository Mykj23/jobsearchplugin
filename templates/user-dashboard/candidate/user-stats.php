<?php
global $jobsearch_plugin_options, $Jobsearch_User_Dashboard_Settings;
$user_id = get_current_user_id();
$user_obj = get_user_by('ID', $user_id);

$page_id = $user_dashboard_page = isset($jobsearch_plugin_options['user-dashboard-template-page']) ? $jobsearch_plugin_options['user-dashboard-template-page'] : '';
$page_id = $user_dashboard_page = jobsearch__get_post_id($user_dashboard_page, 'page');
$page_url = jobsearch_wpml_lang_page_permalink($page_id, 'page'); //get_permalink($page_id);

$candidate_id = jobsearch_get_user_candidate_id($user_id);

$reults_per_page = isset($jobsearch_plugin_options['user-dashboard-per-page']) && $jobsearch_plugin_options['user-dashboard-per-page'] > 0 ? $jobsearch_plugin_options['user-dashboard-per-page'] : 10;

$page_num = isset($_GET['page_num']) ? $_GET['page_num'] : 1;

if ($candidate_id > 0) {

    $rand_id = rand(1000000, 9999999);

    $page_num = isset($_GET['page_num']) ? $_GET['page_num'] : 1;

    $user_applied_jobs_list = array();
    $user_applied_jobs_liste = get_user_meta($user_id, 'jobsearch-user-jobs-applied-list', true);
    if (!empty($user_applied_jobs_liste)) {
        foreach ($user_applied_jobs_liste as $er_applied_jobs_list_key => $er_applied_jobs_list_val) {
            $job_id = isset($er_applied_jobs_list_val['post_id']) ? $er_applied_jobs_list_val['post_id'] : 0;
            if (get_post_type($job_id) == 'job') {
                $user_applied_jobs_list[$er_applied_jobs_list_key] = $er_applied_jobs_list_val;
            }
        }
    }
    
    $user_applied_jobs_count = empty($user_applied_jobs_list) ? 0 : count($user_applied_jobs_list);

    $fav_jobs_list = array();
    $candidate_fav_jobs_liste = get_post_meta($candidate_id, 'jobsearch_fav_jobs_list', true);
    $candidate_fav_jobs_liste = $candidate_fav_jobs_liste != '' ? explode(',', $candidate_fav_jobs_liste) : array();
    if (!empty($candidate_fav_jobs_liste)) {
        foreach ($candidate_fav_jobs_liste as $er_fav_job_list) {
            $job_id = $er_fav_job_list;
            if (get_post_type($job_id) == 'job') {
                $fav_jobs_list[] = $job_id;
            }
        }
    }
    if (!empty($fav_jobs_list)) {
        $fav_jobs_list = implode(',', $fav_jobs_list);
    } else {
        $fav_jobs_list = '';
    }
    
    $fav_jobs_list = $fav_jobs_list != '' ? explode(',', $fav_jobs_list) : array();
    $fav_jobs_list_count = empty($fav_jobs_list) ? 0 : count($fav_jobs_list);

    $args = array(
        'author' => $user_id,
        'post_type' => 'job-alert',
        'posts_per_page' => 1,
        'orderby' => 'post_date',
        'order' => 'DESC',
    );
    $job_alerts = new WP_Query($args);

    $total_alerts = $job_alerts->found_posts;

    wp_reset_postdata();

    $args = array(
        'post_type' => 'shop_order',
        'posts_per_page' => 1,
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
                'value' => array('candidate'),
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
    wp_reset_postdata();
    ?>
    <div class="jobsearch-employer-dasboard">
        <div class="jobsearch-employer-box-section">

            <div class="jobsearch-profile-title">
                <h2><?php esc_html_e('Applications Statistics', 'wp-jobsearch') ?></h2>
            </div>
            <div class="jobsearch-stats-list">
                <ul>
                    <li>
                        <div class="jobsearch-stats-list-wrap">
                            <h6><?php esc_html_e('Applied jobs', 'wp-jobsearch') ?></h6>
                            <span><?php echo absint($user_applied_jobs_count) ?></span>
                            <small><?php esc_html_e('to find career', 'wp-jobsearch') ?></small>
                        </div>
                    </li>
                    <li>
                        <div class="jobsearch-stats-list-wrap green">
                            <h6><?php esc_html_e('Favourite Jobs', 'wp-jobsearch') ?></h6>
                            <span><?php echo absint($fav_jobs_list_count) ?></span>
                            <small><?php esc_html_e('against opportunities', 'wp-jobsearch') ?></small>
                        </div>
                    </li>
                    <li>
                        <div class="jobsearch-stats-list-wrap light-blue">
                            <h6><?php esc_html_e('Job Alerts', 'wp-jobsearch') ?></h6>
                            <span><?php echo absint($total_alerts) ?></span>
                            <small><?php esc_html_e('to get latest updates', 'wp-jobsearch') ?></small>
                        </div>
                    </li>
                    <li>
                        <div class="jobsearch-stats-list-wrap dark-blue">
                            <h6><?php esc_html_e('Packages', 'wp-jobsearch') ?></h6>
                            <span><?php echo absint($total_pkgs) ?></span>
                            <small><?php esc_html_e('to apply jobs', 'wp-jobsearch') ?></small>
                        </div>
                    </li>
                </ul>
            </div>
            <?php
            wp_enqueue_script('morris');
            wp_enqueue_script('raphael');
            ?>
            <div class="jobsearch-applicants-graph">
                <div class="jobsearch-chart" id="chart-<?php echo absint($rand_id) ?>"></div>
                <script>
                    jQuery(function () {
                        Morris.Bar({
                            element: 'chart-<?php echo absint($rand_id) ?>',
                            data: [
                                {y: '<?php printf(esc_html__('Applied Jobs: %s', 'wp-jobsearch'), $user_applied_jobs_count) ?>, <?php printf(esc_html__('Favourite Jobs: %s', 'wp-jobsearch'), $fav_jobs_list_count) ?>, <?php printf(esc_html__('Job Alerts: %s', 'wp-jobsearch'), $total_alerts) ?>', item_1: <?php echo ($user_applied_jobs_count) ?>, item_2: <?php echo ($fav_jobs_list_count) ?>, item_3: <?php echo ($total_alerts) ?>, },
                                            ],
                                            barColors: [
                                                "#008dc9", "#a869d6", "#84c15a"],
                                            xkey: 'y',
                                            ykeys: ["item_1", "item_2", "item_3", ],
                                            labels: [
                                                "<?php esc_html_e('Applied Jobs', 'wp-jobsearch') ?>",
                                                "<?php esc_html_e('Favourite Jobs', 'wp-jobsearch') ?>",
                                                "<?php esc_html_e('Job Alerts', 'wp-jobsearch') ?>"
                                            ]
                                        });
                                    });
                </script>
            </div>
            <div class="jobsearch-applicants-stats">
                <div class="jobsearch-applicants-stats-wrap">
                    <i class="fa fa-users"></i>
                    <span><?php echo absint($user_applied_jobs_count) ?></span>
                    <small><?php esc_html_e('Total Applied Jobs', 'wp-jobsearch') ?></small>
                </div>
                <ul>
                    <li><i class="fa fa-circle dark-blue"></i> <?php esc_html_e('Applied Jobs', 'wp-jobsearch') ?></li>
                    <li><i class="fa fa-circle light-blue"></i> <?php esc_html_e('Favourite Jobs', 'wp-jobsearch') ?></li>
                    <li><i class="fa fa-circle"></i> <?php esc_html_e('Job Alerts', 'wp-jobsearch') ?></li>
                </ul>
            </div>
        </div>
        <div class="jobsearch-employer-box-section">

            <div class="jobsearch-profile-title">
                <h2><?php esc_html_e('Recent Apply Jobs', 'wp-jobsearch') ?></h2>
            </div>
            <?php
            if (!empty($user_applied_jobs_list)) {
                $total_records = count($user_applied_jobs_list);
                arsort($user_applied_jobs_list);
                $start = ($page_num - 1) * ($reults_per_page);
                $offset = $reults_per_page;
                $user_applied_jobs_list = array_slice($user_applied_jobs_list, $start, $offset);
                foreach ($user_applied_jobs_list as $_job_apply) {

                    $_job_id = isset($_job_apply['post_id']) ? $_job_apply['post_id'] : 0;
                    $job_applicants_list = get_post_meta($_job_id, 'jobsearch_job_applicants_list', true);
                    $job_applicants_list = $job_applicants_list != '' ? explode(',', $job_applicants_list) : array();
                    $job_applicants_count = count($job_applicants_list);

                    $job_short_int_list = get_post_meta($_job_id, '_job_short_interview_list', true);
                    $job_short_int_list = $job_short_int_list != '' ? explode(',', $job_short_int_list) : '';
                    if (empty($job_short_int_list)) {
                        $job_short_int_list = array();
                    }

                    if (!empty($job_applicants_list)) {
                        arsort($job_applicants_list);
                        $job_views_count = get_post_meta($_job_id, 'jobsearch_job_views_count', true);
                        $job_expiry_date = get_post_meta($_job_id, 'jobsearch_field_job_expiry_date', true);
                        $job_expiry_date = $job_expiry_date == '' ? strtotime(current_time('Y-m-d H:i:s')) : $job_expiry_date;
                        $job_salary = jobsearch_job_offered_salary($_job_id);
                        ?>
                        <div class="jobsearch-recent-applicants-nav">
                            <ul>
                                <li><span><?php echo absint($job_applicants_count) ?></span> <small><?php esc_html_e('Total applicants', 'wp-jobsearch') ?></small></li>
                                <?php
                                if ($job_salary != '') {
                                    ?>
                                    <li><small><?php echo ($job_salary) ?> <?php esc_html_e('Job Salary', 'wp-jobsearch') ?></small></li>
                                    <?php
                                }
                                ?>
                                <li><span><?php echo absint($job_views_count) ?></span> <small><?php esc_html_e('Total visits', 'wp-jobsearch') ?></small></li>
                                <li><small><?php printf(esc_html__('Expiry Date: %s', 'wp-jobsearch'), date_i18n('M d, Y', $job_expiry_date)) ?></small></li>
                            </ul>
                        </div>
                        <div class="jobsearch-candidate jobsearch-candidate-default  jobsearch-applicns-candidate">
                            <ul class="jobsearch-row">
                                <?php
                                $job_post_date = get_post_meta($_job_id, 'jobsearch_field_job_publish_date', true);
                                $job_location = get_post_meta($_job_id, 'jobsearch_field_location_address', true);
                                $job_post_employer = get_post_meta($_job_id, 'jobsearch_field_job_posted_by', true);

                                $job_post_user = jobsearch_get_employer_user_id($job_post_employer);
                                $user_def_avatar_url = get_avatar_url($job_post_user, array('size' => 69));
                                $user_avatar_id = get_post_thumbnail_id($job_post_employer);
                                if ($user_avatar_id > 0) {
                                    $user_thumbnail_image = wp_get_attachment_image_src($user_avatar_id, 'thumbnail');
                                    $user_def_avatar_url = isset($user_thumbnail_image[0]) && esc_url($user_thumbnail_image[0]) != '' ? $user_thumbnail_image[0] : '';
                                }
                                $user_def_avatar_url = $user_def_avatar_url == '' ? jobsearch_no_image_placeholder() : $user_def_avatar_url;

                                $job_city_title = '';
                                $get_job_city = get_post_meta($_job_id, 'jobsearch_field_location_location3', true);
                                if ($get_job_city == '') {
                                    $get_job_city = get_post_meta($_job_id, 'jobsearch_field_location_location2', true);
                                }
                                if ($get_job_city != '') {
                                    $get_job_country = get_post_meta($_job_id, 'jobsearch_field_location_location1', true);
                                }

                                $job_city_tax = $get_job_city != '' ? get_term_by('slug', $get_job_city, 'job-location') : '';
                                if (is_object($job_city_tax)) {
                                    $job_city_title = isset($job_city_tax->name) ? $job_city_tax->name : '';

                                    $job_country_tax = $get_job_country != '' ? get_term_by('slug', $get_job_country, 'job-location') : '';
                                    if (is_object($job_country_tax)) {
                                        $job_city_title .= isset($job_country_tax->name) ? ', ' . $job_country_tax->name : '';
                                    }
                                }

                                $sectors = wp_get_post_terms($_job_id, 'sector');
                                $job_sector = isset($sectors[0]->name) ? $sectors[0]->name : '';
                                ?>
                                <li class="jobsearch-column-12">
                                    <div class="jobsearch-candidate-default-wrap">
                                        <?php
                                        if ($user_def_avatar_url != '') {
                                            ?>
                                            <figure>
                                                <a href="<?php the_permalink($_job_id); ?>">
                                                    <img src="<?php echo esc_url($user_def_avatar_url) ?>" alt="">
                                                </a>
                                            </figure>
                                            <?php
                                        }
                                        ?>
                                        <div class="jobsearch-candidate-default-text">
                                            <div class="jobsearch-candidate-default-left">
                                                <h2>
                                                    <a href="<?php echo esc_url(get_permalink($_job_id)); ?>">
                                                        <?php echo esc_html(wp_trim_words(get_the_title($_job_id), 5)); ?>
                                                    </a>
                                                </h2>
                                                <ul>
                                                    <?php if ($job_post_date != '') { ?>
                                                        <li><i class="jobsearch-icon jobsearch-calendar"></i> <?php echo date_i18n('d M, Y', $job_post_date); ?></li>
                                                        <?php
                                                    }
                                                    if ($job_sector != '') {
                                                        ?>
                                                        <li><i class="jobsearch-icon jobsearch-filter-tool-black-shape"></i> <a><?php echo ($job_sector) ?></a></li>
                                                        <?php
                                                    }
                                                    if (!empty($job_city_title)) {
                                                        ?>
                                                        <li><i class="fa fa-map-marker"></i> <?php echo esc_html($job_city_title); ?></li>
                                                        <?php
                                                    }
                                                    ?>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </li>

                            </ul>
                        </div>
                        <?php
                    }
                }
                $total_pages = 1;
                if ($total_records > 0 && $reults_per_page > 0 && $total_records > $reults_per_page) {
                    $total_pages = ceil($total_records / $reults_per_page);
                    ?>
                    <div class="jobsearch-pagination-blog">
                        <?php $Jobsearch_User_Dashboard_Settings->pagination($total_pages, $page_num, $page_url) ?>
                    </div>
                    <?php
                }
            } else {
                ?>
                <p><?php esc_html_e('No Applications found.', 'wp-jobsearch') ?></p>
                <?php
            }
            ?>
        </div>
    </div>
    <?php
}    