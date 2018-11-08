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
                'value' => 'feature_job',
                'compare' => '=',
            ),
        ),
    );
    $fpkgs_query = new WP_Query($args);
    wp_reset_postdata();

    $args = array(
        'post_type' => 'job',
        'posts_per_page' => $reults_per_page,
        'paged' => $page_num,
        'post_status' => 'publish',
        'order' => 'DESC',
        'orderby' => 'ID',
        'meta_query' => array(
            array(
                'key' => 'jobsearch_field_job_posted_by',
                'value' => $employer_id,
                'compare' => '=',
            ),
        ),
    );

    if (isset($_GET['keyword']) && $_GET['keyword'] != '') {
        $args['s'] = sanitize_text_field($_GET['keyword']);
    }

    $jobs_query = new WP_Query($args);

    $total_jobs = $jobs_query->found_posts;
    ?>
    <div class="jobsearch-employer-dasboard">
        <div class="jobsearch-employer-box-section">
            <?php
            if (isset($_GET['view']) && $_GET['view'] == 'applicants' && isset($_GET['job_id']) && $_GET['job_id'] > 0) {
                $_job_id = $_GET['job_id'];

                $job_applicants_list = get_post_meta($_job_id, 'jobsearch_job_applicants_list', true);
                $job_applicants_list = jobsearch_is_post_ids_array($job_applicants_list, 'candidate');
                if (empty($job_applicants_list)) {
                    $job_applicants_list = array();
                }

                $job_applicants_count = !empty($job_applicants_list) ? count($job_applicants_list) : 0;

                $viewed_candidates = get_post_meta($_job_id, 'jobsearch_viewed_candidates', true);
                if (empty($viewed_candidates)) {
                    $viewed_candidates = array();
                }
                $viewed_candidates = jobsearch_is_post_ids_array($viewed_candidates, 'candidate');

                $job_short_int_list = get_post_meta($_job_id, '_job_short_interview_list', true);
                $job_short_int_list = $job_short_int_list != '' ? explode(',', $job_short_int_list) : '';
                if (empty($job_short_int_list)) {
                    $job_short_int_list = array();
                }
                $job_short_int_list = jobsearch_is_post_ids_array($job_short_int_list, 'candidate');
                $job_short_int_list_c = !empty($job_short_int_list) ? count($job_short_int_list) : 0;

                $job_reject_int_list = get_post_meta($_job_id, '_job_reject_interview_list', true);
                $job_reject_int_list = $job_reject_int_list != '' ? explode(',', $job_reject_int_list) : '';
                if (empty($job_reject_int_list)) {
                    $job_reject_int_list = array();
                }
                $job_reject_int_list = jobsearch_is_post_ids_array($job_reject_int_list, 'candidate');
                $job_reject_int_list_c = !empty($job_reject_int_list) ? count($job_reject_int_list) : 0;

                $_selected_view = isset($_GET['ap_view']) && $_GET['ap_view'] != '' ? $_GET['ap_view'] : 'less';

                $_mod_tab = isset($_GET['mod']) && $_GET['mod'] != '' ? $_GET['mod'] : 'applicants';
                $_sort_selected = isset($_GET['sort_by']) && $_GET['sort_by'] != '' ? $_GET['sort_by'] : '';
                ?>
                <div class="jobsearch-profile-title">
                    <h2><?php printf(esc_html__('Job "%s" Applicants', 'wp-jobsearch'), get_the_title($_job_id)) ?></h2>
                </div>
                <div class="jobsearch-applicants-tabs">
                    <script>
                        jQuery(document).on('click', '.jobsearch-modelemail-btn-<?php echo ($_job_id) ?>', function () {
                            jobsearch_modal_popup_open('JobSearchModalSendEmail<?php echo ($_job_id) ?>');
                        });
                    </script>
                    <ul class="tabs-list">
                        <li <?php echo ($_mod_tab == '' || $_mod_tab == 'applicants' ? 'class="active"' : '') ?>><a href="<?php echo add_query_arg(array('tab' => 'manage-jobs', 'view' => 'applicants', 'job_id' => $_job_id), $page_url) ?>"><?php printf(esc_html__('Applicants (%s)', 'wp-jobsearch'), $job_applicants_count) ?></a></li>
                        <li <?php echo ($_mod_tab == 'shortlisted' ? 'class="active"' : '') ?>><a href="<?php echo add_query_arg(array('tab' => 'manage-jobs', 'view' => 'applicants', 'job_id' => $_job_id, 'mod' => 'shortlisted'), $page_url) ?>"><?php printf(esc_html__('Shortlisted for Interview (%s)', 'wp-jobsearch'), $job_short_int_list_c) ?></a></li>
                        <li <?php echo ($_mod_tab == 'rejected' ? 'class="active"' : '') ?>><a href="<?php echo add_query_arg(array('tab' => 'manage-jobs', 'view' => 'applicants', 'job_id' => $_job_id, 'mod' => 'rejected'), $page_url) ?>"><?php printf(esc_html__('Rejected (%s)', 'wp-jobsearch'), $job_reject_int_list_c) ?></a></li>
                    </ul>
                    <div class="applied-jobs-sort">
                        <div class="sort-select-all">
                            <input type="checkbox" id="select-all-job-app">
                            <label for="select-all-job-app"></label>
                        </div>
                        <small><?php esc_html_e('Select all', 'wp-jobsearch') ?></small>
                        <?php
                        ob_start();
                        ?>
                        <div class="sort-by-option">
                            <form id="jobsearch-applicants-form" method="get">
                                <input type="hidden" name="tab" value="manage-jobs">
                                <input type="hidden" name="view" value="applicants">
                                <input type="hidden" name="job_id" value="<?php echo absint($_job_id) ?>">
                                <input type="hidden" name="mod" value="<?php echo ($_mod_tab) ?>">
                                <input type="hidden" name="ap_view" value="<?php echo ($_selected_view) ?>">
                                <?php
                                if (isset($_GET['page_num']) && $_GET['page_num'] != '') {
                                    ?>
                                    <input type="hidden" name="page_num" value="<?php echo ($_GET['page_num']) ?>">
                                    <?php
                                }
                                ?>
                                <select id="jobsearch-applicants-sort" class="selectize-select" name="sort_by">
                                    <option value=""><?php esc_html_e('Sort by', 'wp-jobsearch') ?></option>
                                    <option value="recent"<?php echo ($_sort_selected == 'recent' ? ' selected="selected"' : '') ?>><?php esc_html_e('Recent', 'wp-jobsearch') ?></option>
                                    <option value="alphabetic"<?php echo ($_sort_selected == 'alphabetic' ? ' selected="selected"' : '') ?>><?php esc_html_e('Alphabet Order', 'wp-jobsearch') ?></option>
                                    <option value="salary"<?php echo ($_sort_selected == 'salary' ? ' selected="selected"' : '') ?>><?php esc_html_e('Expected Salary', 'wp-jobsearch') ?></option>
                                    <option value="viewed"<?php echo ($_sort_selected == 'viewed' ? ' selected="selected"' : '') ?>><?php esc_html_e('Viewed', 'wp-jobsearch') ?></option>
                                    <option value="unviewed"<?php echo ($_sort_selected == 'unviewed' ? ' selected="selected"' : '') ?>><?php esc_html_e('Unviewed', 'wp-jobsearch') ?></option>
                                </select>

                            </form>
                        </div>
                        <?php
                        $sort_by_dropdown = ob_get_clean();
                        $sort_by_args = array(
                            'job_id' => $_job_id,
                            'sort_selected' => $_sort_selected,
                            'mob_tab' => $_mod_tab,
                            'selected_view' => $_selected_view,
                        );
                        echo apply_filters('jobsearch_applicants_sortby_dropdown', $sort_by_dropdown, $sort_by_args);
                        ?>
                        <div id="sort-more-field-sec" class="sort-more-fields" style="display: none;">
                            <div class="more-fields-act-btn">
                                <a href="javascript:void(0);" class="more-actions"><?php esc_html_e('More', 'wp-jobsearch') ?> <span><i class="careerfy-icon careerfy-down-arrow"></i></span></a>
                                <ul style="display: none;">
                                    <li>
                                        <a href="javascript:void(0);" class="jobsearch-modelemail-btn-<?php echo ($_job_id) ?>"><?php esc_html_e('Email to Candidates', 'wp-jobsearch') ?></a>
                                        <?php
                                        $popup_args = array('p_job_id' => $_job_id, 'p_emp_id' => $employer_id);
                                        add_action('wp_footer', function () use ($popup_args) {

                                            extract(shortcode_atts(array(
                                                'p_job_id' => '',
                                                'p_emp_id' => '',
                                                            ), $popup_args));
                                            ?>
                                            <div class="jobsearch-modal fade" id="JobSearchModalSendEmail<?php echo ($p_job_id) ?>">
                                                <div class="modal-inner-area">&nbsp;</div>
                                                <div class="modal-content-area">
                                                    <div class="modal-box-area">
                                                        <span class="modal-close"><i class="fa fa-times"></i></span>
                                                        <div class="jobsearch-send-message-form">
                                                            <form method="post" id="jobsearch_send_email_form<?php echo esc_html($p_job_id); ?>">
                                                                <div class="jobsearch-user-form">
                                                                    <ul class="email-fields-list">
                                                                        <li>
                                                                            <label>
                                                                                <?php echo esc_html__('Subject', 'wp-jobsearch'); ?>:
                                                                            </label>
                                                                            <div class="input-field">
                                                                                <input type="text" name="send_message_subject" value="" />
                                                                            </div>
                                                                        </li>
                                                                        <li>
                                                                            <label>
                                                                                <?php echo esc_html__('Message', 'wp-jobsearch'); ?>:
                                                                            </label>
                                                                            <div class="input-field">
                                                                                <textarea name="send_message_content"></textarea>
                                                                            </div>
                                                                        </li>
                                                                        <li>
                                                                            <div class="input-field-submit">
                                                                                <input type="submit" class="multi-applicantsto-email-submit" data-jid="<?php echo absint($p_job_id); ?>" data-eid="<?php echo absint($p_emp_id); ?>" name="send_message_content" value="Send"/>
                                                                                <span class="loader-box loader-box-<?php echo esc_html($p_job_id); ?>"></span>
                                                                            </div>
                                                                            <?php jobsearch_terms_and_con_link_txt(); ?>
                                                                        </li>
                                                                    </ul> 
                                                                    <div class="message-box message-box-<?php echo esc_html($p_job_id); ?>" style="display:none;"></div>
                                                                </div>
                                                            </form>    
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                            <?php
                                        }, 11, 1);
                                        ?>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="shortlist-cands-to-intrview ajax-enable" data-jid="<?php echo absint($_job_id); ?>"><?php esc_html_e('Shortlist', 'wp-jobsearch') ?> <span class="app-loader"></span></a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="reject-cands-to-intrview ajax-enable" data-jid="<?php echo absint($_job_id); ?>"><?php esc_html_e('Reject', 'wp-jobsearch') ?> <span class="app-loader"></span></a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="sort-list-view">
                            <a href="javascript:void(0);" class="apps-view-btn<?php echo ($_selected_view == 'more' ? '' : ' active') ?>" data-view="less"><i class="fa fa-list"></i></a>
                            <a href="javascript:void(0);" class="apps-view-btn<?php echo ($_selected_view == 'more' ? ' active' : '') ?>" data-view="more"><i class="fa fa-bars"></i></a>
                        </div>
                    </div>
                    <?php
                    if ($_mod_tab == 'shortlisted') {
                        $job_applicants_list = jobsearch_job_applicants_sort_list($_job_id, $_sort_selected, '_job_short_interview_list');
                    } else if ($_mod_tab == 'rejected') {
                        $job_applicants_list = jobsearch_job_applicants_sort_list($_job_id, $_sort_selected, '_job_reject_interview_list');
                    } else {
                        $job_applicants_list = jobsearch_job_applicants_sort_list($_job_id, $_sort_selected);
                    }

                    $total_records = !empty($job_applicants_list) ? count($job_applicants_list) : 0;

                    $start = ($page_num - 1) * ($reults_per_page);
                    $offset = $reults_per_page;
                    $job_applicants_list = array_slice($job_applicants_list, $start, $offset);
                    ?>
                    <div class="jobsearch-applied-jobs">
                        <?php
                        if (!empty($job_applicants_list)) {
                            ?>
                            <ul class="jobsearch-row">
                                <?php
                                foreach ($job_applicants_list as $_candidate_id) {
                                    $candidate_user_id = jobsearch_get_candidate_user_id($_candidate_id);
                                    if (absint($candidate_user_id) <= 0) {
                                        continue;
                                    }
                                    $user_def_avatar_url = get_avatar_url($candidate_user_id, array('size' => 69));
                                    $user_avatar_id = get_post_thumbnail_id($_candidate_id);
                                    if ($user_avatar_id > 0) {
                                        $user_thumbnail_image = wp_get_attachment_image_src($user_avatar_id, 'thumbnail');
                                        $user_def_avatar_url = isset($user_thumbnail_image[0]) && esc_url($user_thumbnail_image[0]) != '' ? $user_thumbnail_image[0] : '';
                                    }
                                    $user_def_avatar_url = $user_def_avatar_url == '' ? jobsearch_no_image_placeholder() : $user_def_avatar_url;

                                    $candidate_jobtitle = get_post_meta($_candidate_id, 'jobsearch_field_candidate_jobtitle', true);
                                    $get_candidate_location = get_post_meta($_candidate_id, 'jobsearch_field_location_address', true);

                                    $candidate_city_title = '';
                                    $get_candidate_city = get_post_meta($_candidate_id, 'jobsearch_field_location_location3', true);
                                    if ($get_candidate_city == '') {
                                        $get_candidate_city = get_post_meta($_candidate_id, 'jobsearch_field_location_location2', true);
                                    }
                                    if ($get_candidate_city == '') {
                                        $get_candidate_city = get_post_meta($_candidate_id, 'jobsearch_field_location_location1', true);
                                    }

                                    $candidate_city_tax = $get_candidate_city != '' ? get_term_by('slug', $get_candidate_city, 'job-location') : '';
                                    if (is_object($candidate_city_tax)) {
                                        $candidate_city_title = $candidate_city_tax->name;
                                    }

                                    $sectors = wp_get_post_terms($_candidate_id, 'sector');
                                    $candidate_sector = isset($sectors[0]->name) ? $sectors[0]->name : '';

                                    $candidate_salary = jobsearch_candidate_current_salary($_candidate_id);
                                    $candidate_age = jobsearch_candidate_age($_candidate_id);

                                    $candidate_phone = get_post_meta($_candidate_id, 'jobsearch_field_user_phone', true);

                                    $send_message_form_rand = rand(100000, 999999);
                                    ?>
                                    <li class="jobsearch-column-12">
                                        <script>
                                            jQuery(document).on('click', '.jobsearch-modelemail-btn-<?php echo ($send_message_form_rand) ?>', function () {
                                                jobsearch_modal_popup_open('JobSearchModalSendEmail<?php echo ($send_message_form_rand) ?>');
                                            });
                                        </script>
                                        <div class="jobsearch-applied-jobs-wrap">
                                            <div class="candidate-select-box">
                                                <input type="checkbox" name="app_candidate_sel[]" id="app_candidate_sel_<?php echo $_candidate_id ?>" value="<?php echo $_candidate_id ?>">
                                                <label for="app_candidate_sel_<?php echo $_candidate_id ?>"></label>
                                            </div>
                                            <a class="jobsearch-applied-jobs-thumb">
                                                <img src="<?php echo ($user_def_avatar_url) ?>" alt="">
                                            </a>
                                            <div class="jobsearch-applied-jobs-text">
                                                <div class="jobsearch-applied-jobs-left">
                                                    <?php
                                                    if ($candidate_jobtitle != '') {
                                                        ?>
                                                        <span> <?php echo ($candidate_jobtitle) ?></span>
                                                        <?php
                                                    }

                                                    if (in_array($_candidate_id, $viewed_candidates)) {
                                                        ?>
                                                        <small class="profile-view viewed"><?php esc_html_e('(Viewed)', 'wp-jobsearch') ?></small>
                                                        <?php
                                                    } else {
                                                        ?>
                                                        <small class="profile-view unviewed"><?php esc_html_e('(Unviewed)', 'wp-jobsearch') ?></small>
                                                        <?php
                                                    }
                                                    echo apply_filters('jobsearch_applicants_list_before_title', '', $_candidate_id, $_job_id);
                                                    ?>
                                                    <h2>
                                                        <a href="<?php echo get_permalink($_candidate_id) ?>"><?php echo get_the_title($_candidate_id) ?></a>
                                                        <?php
                                                        if ($candidate_age != '') {
                                                            ?>
                                                            <small><?php echo apply_filters('jobsearch_dash_applicants_age_html', sprintf(esc_html__('(Age: %s years)', 'wp-jobsearch'), $candidate_age)) ?></small>
                                                            <?php
                                                        }
                                                        if ($candidate_phone != '') {
                                                            ?>
                                                            <small><?php printf(esc_html__('Phone: %s', 'wp-jobsearch'), $candidate_phone) ?></small>
                                                            <?php
                                                        }
                                                        ?>
                                                    </h2>
                                                    <ul>
                                                        <?php
                                                        if ($candidate_salary != '') {
                                                            ?>
                                                            <li><i class="fa fa-money"></i> <?php printf(esc_html__('Salary: %s', 'wp-jobsearch'), $candidate_salary) ?></li>
                                                            <?php
                                                        }
                                                        if ($candidate_city_title != '') {
                                                            ?>
                                                            <li><i class="fa fa-map-marker"></i> <?php echo ($candidate_city_title) ?></li>
                                                            <?php
                                                        }
                                                        if ($candidate_sector != '') {
                                                            ?>
                                                            <li><i class="jobsearch-icon jobsearch-filter-tool-black-shape"></i> <a><?php echo ($candidate_sector) ?></a></li>
                                                            <?php
                                                        }
                                                        ?>
                                                    </ul>
                                                </div>
                                                <div class="jobsearch-applied-job-btns">
                                                    <ul>
                                                        <li>
                                                            <a href="<?php echo add_query_arg(array('job_id' => $_job_id, 'employer_id' => $employer_id, 'action' => 'preview_profile'), get_permalink($_candidate_id)) ?>" class="preview-candidate-profile"><i class="fa fa-eye"></i> <?php esc_html_e('Preview', 'wp-jobsearch') ?></a>
                                                        </li>
                                                        <li>
                                                            <div class="candidate-more-acts-con">
                                                                <a href="javascript:void(0);" class="more-actions"><?php esc_html_e('Actions', 'wp-jobsearch') ?> <i class="fa fa-angle-down"></i></a>
                                                                <ul>
                                                                    <?php
                                                                    $multiple_cv_files_allow = isset($jobsearch_plugin_options['multiple_cv_uploads']) ? $jobsearch_plugin_options['multiple_cv_uploads'] : '';
                                                                    $candidate_cv_file = get_post_meta($_candidate_id, 'candidate_cv_file', true);

                                                                    if ($multiple_cv_files_allow == 'on') {
                                                                        $ca_at_cv_files = get_post_meta($_candidate_id, 'candidate_cv_files', true);
                                                                        if (!empty($ca_at_cv_files)) {
                                                                            ?>
                                                                            <li><a href="<?php echo apply_filters('jobsearch_user_attach_cv_file_url', '', $_candidate_id, $_job_id) ?>" download="<?php echo apply_filters('jobsearch_user_attach_cv_file_title', '', $_candidate_id, $_job_id) ?>"><?php esc_html_e('Download CV', 'wp-jobsearch') ?></a></li>
                                                                            <?php
                                                                        }
                                                                    } else if (!empty($candidate_cv_file)) {
                                                                        $file_attach_id = isset($candidate_cv_file['file_id']) ? $candidate_cv_file['file_id'] : '';
                                                                        $file_url = isset($candidate_cv_file['file_url']) ? $candidate_cv_file['file_url'] : '';

                                                                        $cv_file_title = get_the_title($file_attach_id);
                                                                        ?>
                                                                        <li><a href="<?php echo ($file_url) ?>" download="<?php echo ($cv_file_title) ?>"><?php esc_html_e('Download CV', 'wp-jobsearch') ?></a></li>
                                                                        <?php
                                                                    }
                                                                    echo apply_filters('employer_dash_apps_acts_list_after_download_link', '', $_candidate_id, $_job_id);
                                                                    ?>
                                                                    <li>
                                                                        <a href="javascript:void(0);" class="jobsearch-modelemail-btn-<?php echo ($send_message_form_rand) ?>"><?php esc_html_e('Email to Candidate', 'wp-jobsearch') ?></a>
                                                                        <?php
                                                                        $popup_args = array('p_job_id' => $_job_id, 'cand_id' => $_candidate_id, 'p_emp_id' => $employer_id, 'p_masg_rand' => $send_message_form_rand);
                                                                        add_action('wp_footer', function () use ($popup_args) {

                                                                            extract(shortcode_atts(array(
                                                                                'p_job_id' => '',
                                                                                'p_emp_id' => '',
                                                                                'cand_id' => '',
                                                                                'p_masg_rand' => ''
                                                                                            ), $popup_args));
                                                                            ?>
                                                                            <div class="jobsearch-modal fade" id="JobSearchModalSendEmail<?php echo ($p_masg_rand) ?>">
                                                                                <div class="modal-inner-area">&nbsp;</div>
                                                                                <div class="modal-content-area">
                                                                                    <div class="modal-box-area">
                                                                                        <span class="modal-close"><i class="fa fa-times"></i></span>
                                                                                        <div class="jobsearch-send-message-form">
                                                                                            <form method="post" id="jobsearch_send_email_form<?php echo esc_html($p_masg_rand); ?>">
                                                                                                <div class="jobsearch-user-form">
                                                                                                    <ul class="email-fields-list">
                                                                                                        <li>
                                                                                                            <label>
                                                                                                                <?php echo esc_html__('Subject', 'wp-jobsearch'); ?>:
                                                                                                            </label>
                                                                                                            <div class="input-field">
                                                                                                                <input type="text" name="send_message_subject" value="" />
                                                                                                            </div>
                                                                                                        </li>
                                                                                                        <li>
                                                                                                            <label>
                                                                                                                <?php echo esc_html__('Message', 'wp-jobsearch'); ?>:
                                                                                                            </label>
                                                                                                            <div class="input-field">
                                                                                                                <textarea name="send_message_content"></textarea>
                                                                                                            </div>
                                                                                                        </li>
                                                                                                        <li>
                                                                                                            <div class="input-field-submit">
                                                                                                                <input type="submit" class="applicantto-email-submit-btn" data-jid="<?php echo absint($p_job_id); ?>" data-eid="<?php echo absint($p_emp_id); ?>" data-cid="<?php echo absint($cand_id); ?>" data-randid="<?php echo esc_html($p_masg_rand); ?>" name="send_message_content" value="Send"/>
                                                                                                                <span class="loader-box loader-box-<?php echo esc_html($p_masg_rand); ?>"></span>
                                                                                                            </div>
                                                                                                            <?php jobsearch_terms_and_con_link_txt(); ?>
                                                                                                        </li>
                                                                                                    </ul> 
                                                                                                    <div class="message-box message-box-<?php echo esc_html($p_masg_rand); ?>" style="display:none;"></div>
                                                                                                </div>
                                                                                            </form>    
                                                                                        </div>

                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <?php
                                                                        }, 11, 1);
                                                                        ?>
                                                                    </li>
                                                                    <li>
                                                                        <?php
                                                                        if (in_array($_candidate_id, $job_short_int_list)) {
                                                                            ?>
                                                                            <a href="javascript:void(0);" class="shortlist-cand-to-intrview"><?php esc_html_e('Shortlisted', 'wp-jobsearch') ?></a>
                                                                            <?php
                                                                        } else {
                                                                            ?>
                                                                            <a href="javascript:void(0);" class="shortlist-cand-to-intrview ajax-enable" data-jid="<?php echo absint($_job_id); ?>" data-cid="<?php echo absint($_candidate_id); ?>"><?php esc_html_e('Shortlist for Interview', 'wp-jobsearch') ?> <span class="app-loader"></span></a>
                                                                            <?php
                                                                        }
                                                                        ?>
                                                                    </li>
                                                                    <li>
                                                                        <?php
                                                                        if (in_array($_candidate_id, $job_reject_int_list)) {
                                                                            ?>
                                                                            <a href="javascript:void(0);" class="reject-cand-to-intrview"><?php esc_html_e('Rejected', 'wp-jobsearch') ?></a>
                                                                            <?php
                                                                        } else {
                                                                            ?>
                                                                            <a href="javascript:void(0);" class="reject-cand-to-intrview ajax-enable" data-jid="<?php echo absint($_job_id); ?>" data-cid="<?php echo absint($_candidate_id); ?>"><?php esc_html_e('Reject', 'wp-jobsearch') ?> <span class="app-loader"></span></a>
                                                                            <?php
                                                                        }
                                                                        ?>
                                                                    </li>
                                                                    <li>
                                                                        <a href="javascript:void(0);" class="delete-cand-from-job ajax-enable" data-jid="<?php echo absint($_job_id); ?>" data-cid="<?php echo absint($_candidate_id); ?>"><?php esc_html_e('Delete', 'wp-jobsearch') ?> <span class="app-loader"></span></a>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    <?php
                                }
                                ?>
                            </ul>
                            <?php
                        }
                        ?>
                    </div>
                    <?php
                    if (!empty($job_applicants_list)) {
                        $total_pages = 1;
                        if ($total_records > 0 && $reults_per_page > 0 && $total_records > $reults_per_page) {
                            $total_pages = ceil($total_records / $reults_per_page);
                            ?>
                            <div class="jobsearch-pagination-blog">
                                <?php $Jobsearch_User_Dashboard_Settings->pagination($total_pages, $page_num, $page_url) ?>
                            </div>
                            <?php
                        }
                    }
                    ?>
                </div>
                <?php
            } else {
                ?>
                <div class="jobsearch-profile-title">
                    <h2><?php esc_html_e('Manage Jobs', 'wp-jobsearch') ?></h2>
                    <?php
                    if ($jobs_query->have_posts()) {
                        ?>
                        <form method="get" class="jobsearch-employer-search" action="<?php echo ($page_url) ?>">
                            <input type="hidden" name="tab" value="manage-jobs">
                            <input placeholder="<?php esc_html_e('Search job', 'wp-jobsearch') ?>" name="keyword" type="text" value="<?php echo (isset($_GET['keyword']) ? $_GET['keyword'] : '') ?>">
                            <input type="submit" value="">
                            <i class="jobsearch-icon jobsearch-search"></i>
                        </form>
                        <?php
                    }
                    ?>
                </div>
                <?php
                $free_jobs_allow = isset($jobsearch_plugin_options['free-jobs-allow']) ? $jobsearch_plugin_options['free-jobs-allow'] : '';
                if ($jobs_query->have_posts()) {
                    ?>
                    <script>
                        jQuery(function () {
                            jQuery('.jobsearch-fill-the-job').tooltip();
                        });
                    </script>
                    <div class="jobsearch-jobs-list-holder">
                        <div class="jobsearch-managejobs-list">
                            <!-- Manage Jobs Header -->
                            <div class="jobsearch-table-layer jobsearch-managejobs-thead">
                                <div class="jobsearch-table-row">
                                    <div class="jobsearch-table-cell"><?php esc_html_e('Job Title', 'wp-jobsearch') ?></div>
                                    <div class="jobsearch-table-cell"><?php esc_html_e('Applications', 'wp-jobsearch') ?></div>
                                    <div class="jobsearch-table-cell"><?php esc_html_e('Featured', 'wp-jobsearch') ?></div>
                                    <div class="jobsearch-table-cell"><?php esc_html_e('Status', 'wp-jobsearch') ?></div>
                                    <div class="jobsearch-table-cell"></div>
                                    <div class="jobsearch-table-cell"></div>
                                </div>
                            </div>
                            <?php
                            while ($jobs_query->have_posts()) : $jobs_query->the_post();
                                $job_id = get_the_ID();

                                $sectors = wp_get_post_terms($job_id, 'sector');
                                $job_sector = isset($sectors[0]->name) ? $sectors[0]->name : '';

                                $jobtypes = wp_get_post_terms($job_id, 'jobtype');
                                $job_type = isset($jobtypes[0]->term_id) ? $jobtypes[0]->term_id : '';

                                $get_job_location = get_post_meta($job_id, 'jobsearch_field_location_address', true);

                                $job_publish_date = get_post_meta($job_id, 'jobsearch_field_job_publish_date', true);
                                $job_expiry_date = get_post_meta($job_id, 'jobsearch_field_job_expiry_date', true);

                                $job_filled = get_post_meta($job_id, 'jobsearch_field_job_filled', true);

                                $job_status = 'pending';
                                $job_status = get_post_meta($job_id, 'jobsearch_field_job_status', true);

                                if ($job_expiry_date != '' && $job_expiry_date <= strtotime(current_time('d-m-Y H:i:s', 1))) {
                                    $job_status = 'expired';
                                }

                                $status_txt = '';
                                if ($job_status == 'pending') {
                                    $status_txt = esc_html__('Pending', 'wp-jobsearch');
                                } else if ($job_status == 'expired') {
                                    $status_txt = esc_html__('Expired', 'wp-jobsearch');
                                } else if ($job_status == 'canceled') {
                                    $status_txt = esc_html__('Canceled', 'wp-jobsearch');
                                } else if ($job_status == 'approved') {
                                    $status_txt = esc_html__('Approved', 'wp-jobsearch');
                                } else if ($job_status == 'admin-review') {
                                    $status_txt = esc_html__('Admin Review', 'wp-jobsearch');
                                }

                                $job_is_feature = get_post_meta($job_id, 'jobsearch_field_job_featured', true);

                                $job_applicants_list = get_post_meta($job_id, 'jobsearch_job_applicants_list', true);
                                $job_applicants_list = jobsearch_is_post_ids_array($job_applicants_list, 'candidate');
                                if (empty($job_applicants_list)) {
                                    $job_applicants_list = array();
                                }

                                $job_applicants_count = !empty($job_applicants_list) ? count($job_applicants_list) : 0;
                                ?>
                                <div class="jobsearch-table-layer jobsearch-managejobs-tbody">
                                    <div class="jobsearch-table-row">
                                        <div class="jobsearch-table-cell">
                                            <h6><a href="<?php echo get_permalink($job_id) ?>"><?php echo get_the_title() ?></a> <span class="job-filled"><?php echo ($job_filled == 'on' ? esc_html__('(Filled)', 'wp-jobsearch') : '') ?></span></h6>

                                            <ul>
                                                <?php
                                                if ($job_publish_date != '') {
                                                    ?>
                                                    <li><i class="jobsearch-icon jobsearch-calendar"></i> <?php printf(wp_kses(__('Created: <span>%s</span>', 'wp-jobsearch'), array('span' => array())), date_i18n('M d, Y', $job_publish_date)) ?></li>
                                                    <?php
                                                }
                                                if ($job_expiry_date != '') {
                                                    ?>
                                                    <li><i class="jobsearch-icon jobsearch-calendar"></i> <?php printf(wp_kses(__('Expiry: <span>%s</span>', 'wp-jobsearch'), array('span' => array())), date_i18n('M d, Y', $job_expiry_date)) ?></li>
                                                    <?php
                                                }
                                                if ($get_job_location != '') {
                                                    ?>
                                                    <li><i class="jobsearch-icon jobsearch-maps-and-flags"></i> <?php echo ($get_job_location) ?></li>
                                                    <?php
                                                }
                                                if ($job_sector != '') {
                                                    ?>
                                                    <li><i class="jobsearch-icon jobsearch-filter-tool-black-shape"></i> <a><?php echo ($job_sector) ?></a></li>
                                                    <?php
                                                }
                                                ?>
                                            </ul>
                                        </div>

                                        <div class="jobsearch-table-cell"><a <?php echo ($job_applicants_count > 0 ? 'href="' . add_query_arg(array('tab' => 'manage-jobs', 'view' => 'applicants', 'job_id' => $job_id), $page_url) . '"' : '') ?> class="jobsearch-managejobs-appli"><?php printf(__('%s Application(s)', 'wp-jobsearch'), $job_applicants_count) ?></a></div>
                                        <div class="jobsearch-table-cell">
                                            <?php
                                            if ($job_is_feature == 'on') {
                                                $job_feature_link = 'href="javascript:void(0);"';
                                            } else {
                                                if ($free_jobs_allow == 'on') {
                                                    $job_feature_link = 'href="javascript:void(0);"';
                                                } else {
                                                    $job_feature_link = 'href="' . add_query_arg(array('tab' => 'user-job', 'job_id' => $job_id, 'action' => 'update', 'step' => 'package'), $page_url) . '"';
                                                    $fpkgs_posts = $fpkgs_query->posts;
                                                    if (!empty($fpkgs_posts)) {
                                                        $job_feature_link = 'href="javascript:void(0);" class="jobsearch-jobfeture-btn-' . ($job_id) . '"';
                                                        ?>
                                                        <script>
                                                            jQuery(document).on('click', '.jobsearch-jobfeture-btn-<?php echo ($job_id) ?>', function () {
                                                                jobsearch_modal_popup_open('JobSearchModalFeatureJob<?php echo ($job_id) ?>');
                                                            });
                                                        </script>
                                                        <?php
                                                        $popup_args = array('p_job_id' => $job_id, 'p_fpkgs_posts' => $fpkgs_posts);
                                                        add_action('wp_footer', function () use ($popup_args) {

                                                            extract(shortcode_atts(array(
                                                                'p_job_id' => '',
                                                                'p_fpkgs_posts' => '',
                                                                            ), $popup_args));
                                                            ?>
                                                            <div class="jobsearch-modal fade" id="JobSearchModalFeatureJob<?php echo ($p_job_id) ?>">
                                                                <div class="modal-inner-area">&nbsp;</div>
                                                                <div class="modal-content-area">
                                                                    <div class="modal-box-area">
                                                                        <span class="modal-close"><i class="fa fa-times"></i></span>
                                                                        <div id="fpkgs-lista-<?php echo ($p_job_id) ?>" class="jobsearch-feat-job-form">
                                                                            <div class="model-heading"><h2><?php esc_html_e('Select Package', 'wp-jobsearch') ?></h2></div>
                                                                            <ul>
                                                                                <?php
                                                                                foreach ($p_fpkgs_posts as $fpkg_post) {
                                                                                    $fpkg_price = get_post_meta($fpkg_post, 'jobsearch_field_package_price', true);
                                                                                    ?>
                                                                                    <li><label for="<?php echo ('fpkg-' . $fpkg_post . '-' . $p_job_id) ?>"><?php echo get_the_title($fpkg_post) ?> - <span><?php echo jobsearch_get_price_format($fpkg_price); ?></span></label> <input id="<?php echo ('fpkg-' . $fpkg_post . '-' . $p_job_id) ?>" type="radio" name="feature_pkg" value="<?php echo ($fpkg_post) ?>"></li>
                                                                                    <?php
                                                                                }
                                                                                ?>
                                                                            </ul>
                                                                            <a href="javascript:void(0);" class="jobsearch-feature-pkg-sbtn" data-id="<?php echo ($p_job_id) ?>"><?php esc_html_e('Checkout', 'wp-jobsearch') ?></a>
                                                                            <span class="fpkgs-loader"></span>
                                                                            <div class="fpkgs-msg"></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <?php
                                                        }, 11, 1);
                                                    }
                                                }
                                            }
                                            ?>
                                            <a <?php echo ($job_feature_link) ?>><i class="<?php echo ($job_is_feature == 'on' ? 'fa fa-star' : 'fa fa-star-o') ?>"></i></a>
                                        </div>
                                        <div class="jobsearch-table-cell"><span class="jobsearch-managejobs-option <?php echo ($job_status == 'approved' ? 'active' : '') ?><?php echo ($job_status == 'expired' || $job_status == 'canceled' ? 'expired' : '') ?>"><?php echo ($status_txt) ?></span></div>
                                        <div class="jobsearch-table-cell">
                                            <div class="jobsearch-managejobs-links">
                                                <a href="<?php echo get_permalink($job_id) ?>" class="jobsearch-icon jobsearch-view"></a>
                                                <a href="<?php echo add_query_arg(array('tab' => 'user-job', 'job_id' => $job_id, 'action' => 'update'), $page_url) ?>" class="jobsearch-icon jobsearch-edit"></a>
                                                <a href="javascript:void(0);" data-id="<?php echo ($job_id) ?>" class="jobsearch-icon jobsearch-rubbish jobsearch-trash-job"></a>
                                            </div>
                                        </div>
                                        <div class="jobsearch-table-cell">
                                            <?php
                                            $job_status = get_post_meta($job_id, 'jobsearch_field_job_status', true);
                                            if ($job_status == 'approved') {
                                                ?>
                                                <div class="jobsearch-filledjobs-links">
                                                    <?php
                                                    if ($job_filled == 'on') {
                                                        ?>
                                                        <a class="jobsearch-fill-the-job" title="<?php esc_html_e('Filled Job', 'wp-jobsearch') ?>"><span></span><i class="fa fa-check"></i></a>
                                                        <?php
                                                    } else {
                                                        ?>
                                                        <a href="javascript:void(0);" title="<?php esc_html_e('Fill this Job', 'wp-jobsearch') ?>" data-id="<?php echo ($job_id) ?>" class="jobsearch-fill-the-job ajax-enable"><span></span><span class="fill-job-loader"></span></a>
                                                        <?php
                                                    }
                                                    ?>
                                                </div>
                                                <?php
                                            }
                                            ?>
                                        </div>
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
                    if ($total_jobs > 0 && $reults_per_page > 0 && $total_jobs > $reults_per_page) {
                        $total_pages = ceil($total_jobs / $reults_per_page);
                        ?>
                        <div class="jobsearch-pagination-blog">
                            <?php $Jobsearch_User_Dashboard_Settings->pagination($total_pages, $page_num, $page_url) ?>
                        </div>
                        <?php
                    }
                } else {
                    ?>
                    <p><?php esc_html_e('No job found.', 'wp-jobsearch') ?></p>
                    <?php
                }
            }
            ?>

        </div>
    </div>
    <?php
}