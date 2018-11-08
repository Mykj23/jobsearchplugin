<?php
/**
 * Define Meta boxes for plugin
 * and theme.
 *
 */
add_action('save_post', 'jobsearch_employers_meta_save');

function jobsearch_employers_meta_save($post_id) {
    global $pagenow;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    $post_type = '';
    if ($pagenow == 'post.php') {
        $post_type = get_post_type();
    }
    if (isset($_POST)) {
        if ($post_type == 'employer') {
            // extra save
            // Employer jobs status change according his/her status
            do_action('jobsearch_employer_update_jobs_status', $post_id);
        }
    }
}

if (class_exists('JobSearchMultiPostThumbnails')) {
    new JobSearchMultiPostThumbnails(array(
        'label' => 'Cover Image',
        'id' => 'cover-image',
        'post_type' => 'employer',
            )
    );
}

/**
 * Employer settings meta box.
 */
function jobsearch_employers_settings_meta_boxes() {
    add_meta_box('jobsearch-employers-settings', esc_html__('Employer Settings', 'wp-jobsearch'), 'jobsearch_employers_meta_settings', 'employer', 'normal');
}

/**
 * Employer settings meta box callback.
 */
function jobsearch_employers_meta_settings() {
    global $post, $jobsearch_form_fields, $jobsearch_plugin_options;
    $rand_num = rand(1000000, 99999999);
    $_post_id = $post->ID;
    $employer_posted_by = get_post_meta($post->ID, 'jobsearch_field_users', true);

    $employer_user_id = get_post_meta($post->ID, 'jobsearch_user_id', true);
    ?>
    <script>
        jQuery(document).ready(function () {
            jQuery('#jobsearch_employer_publish_date').datetimepicker({
                timepicker: true,
                format: 'd-m-Y H:i:s'
            });
            jQuery('#jobsearch_employer_expiry_date').datetimepicker({
                timepicker: true,
                format: 'd-m-Y H:i:s'
            });
        });
    </script>
    <div class="jobsearch-post-settings">
        <?php
        $get_user_emp_id = get_user_meta($employer_user_id, 'jobsearch_employer_id', true);
        update_user_meta($employer_user_id, 'jobsearch_employer_id', filter_var($get_user_emp_id, FILTER_SANITIZE_NUMBER_INT));
        if ($get_user_emp_id != '' && $post->ID == $get_user_emp_id) {
            $user_obj = get_user_by('ID', $employer_user_id);

            if (is_object($user_obj)) {
                ?>
                <br><br>
                <div class="jobsearch-element-field">
                    <div class="elem-label">
                        <label><?php esc_html_e('Attached User', 'wp-jobsearch') ?></label>
                    </div>
                    <div class="elem-field">
                        <?php
                        echo '<strong>' . ($user_obj->user_login) . '</strong>';
                        //
                        $user_phone = get_post_meta($_post_id, 'jobsearch_field_user_phone', true);
                        echo '<p>' . sprintf(esc_html__('User email : %s', 'wp-jobsearch'), $user_obj->user_email) . '</p>';
                        if ($user_phone != '') {
                            echo '<p>' . sprintf(esc_html__('User Phone : %s', 'wp-jobsearch'), $user_phone) . '</p>';
                        }
                        ?>
                    </div>
                </div>
                <br><br>
                <?php
            }
        }

        $sdate_format = jobsearch_get_wp_date_simple_format();

        $days = array();
        for ($day = 1; $day <= 31; $day++) {
            $days[$day] = $day;
        }
        $months = array();
        for ($month = 1; $month <= 12; $month++) {
            $months[$month] = $month;
        }
        $years = array();
        for ($year = 1900; $year <= date('Y'); $year++) {
            $years[$year] = $year;
        }
        ?>
        <div class="jobsearch-element-field">
            <div class="elem-label">
                <label><?php esc_html_e('Founded Date', 'wp-jobsearch') ?></label>
            </div>
            <div class="elem-field">
                <?php
                ob_start();
                ?>
                <div style="float:left; margin-right: 4px; width: 80px;">
                    <?php
                    $field_params = array(
                        'std' => date('d'),
                        'name' => 'user_dob_dd',
                        'options' => $days,
                    );
                    $jobsearch_form_fields->select_field($field_params);
                    ?>
                </div>
                <?php
                $dob_dd_html = ob_get_clean();
                ob_start();
                ?>
                <div style="float:left; margin-right: 4px; width: 80px;">
                    <?php
                    $field_params = array(
                        'std' => date('m'),
                        'name' => 'user_dob_mm',
                        'options' => $months,
                    );
                    $jobsearch_form_fields->select_field($field_params);
                    ?>
                </div>
                <?php
                $dob_mm_html = ob_get_clean();
                ob_start();
                ?>
                <div style="float:left; margin-right: 4px; width: 80px;">
                    <?php
                    $field_params = array(
                        'std' => date('Y'),
                        'name' => 'user_dob_yy',
                        'options' => $years,
                    );
                    $jobsearch_form_fields->select_field($field_params);
                    ?>
                </div>
                <?php
                $dob_yy_html = ob_get_clean();
                //
                if ($sdate_format == 'm-d-y') {
                    echo ($dob_mm_html);
                    echo ($dob_dd_html);
                    echo ($dob_yy_html);
                } else if ($sdate_format == 'y-m-d') {
                    echo ($dob_yy_html);
                    echo ($dob_mm_html);
                    echo ($dob_dd_html);
                } else {
                    echo ($dob_dd_html);
                    echo ($dob_mm_html);
                    echo ($dob_yy_html);
                }
                ?>
            </div>
        </div>

        <div class="jobsearch-element-field">
            <div class="elem-label">
                <label><?php esc_html_e('Phone', 'wp-jobsearch') ?></label>
            </div>
            <div class="elem-field">
                <?php
                $field_params = array(
                    'name' => 'user_phone',
                );
                $jobsearch_form_fields->input_field($field_params);
                ?>
            </div>
        </div>
        <div class="jobsearch-element-field">
            <div class="elem-label">
                <label><?php esc_html_e('Approved', 'wp-jobsearch') ?></label>
            </div>
            <div class="elem-field">
                <?php
                $field_params = array(
                    'std' => 'on',
                    'name' => 'employer_approved',
                );
                $jobsearch_form_fields->checkbox_field($field_params);
                ?>
            </div>
        </div>

        <?php
        // load custom fields which is configured in employer custom fields
        do_action('jobsearch_custom_fields_load', $post->ID, 'employer');
        ?>
        <div class="jobsearch-elem-heading">
            <h2><?php esc_html_e('Social Links', 'wp-jobsearch') ?></h2>
        </div>
        <div class="jobsearch-element-field">
            <div class="elem-label">
                <label><?php esc_html_e('Facebook', 'wp-jobsearch') ?></label>
            </div>
            <div class="elem-field">
                <?php
                $field_params = array(
                    'name' => 'user_facebook_url',
                );
                $jobsearch_form_fields->input_field($field_params);
                ?>
            </div>
        </div>
        <div class="jobsearch-element-field">
            <div class="elem-label">
                <label><?php esc_html_e('Twitter', 'wp-jobsearch') ?></label>
            </div>
            <div class="elem-field">
                <?php
                $field_params = array(
                    'name' => 'user_twitter_url',
                );
                $jobsearch_form_fields->input_field($field_params);
                ?>
            </div>
        </div>
        <div class="jobsearch-element-field">
            <div class="elem-label">
                <label><?php esc_html_e('Google Plus', 'wp-jobsearch') ?></label>
            </div>
            <div class="elem-field">
                <?php
                $field_params = array(
                    'name' => 'user_google_plus_url',
                );
                $jobsearch_form_fields->input_field($field_params);
                ?>
            </div>
        </div>
        <div class="jobsearch-element-field">
            <div class="elem-label">
                <label><?php esc_html_e('Linkedin', 'wp-jobsearch') ?></label>
            </div>
            <div class="elem-field">
                <?php
                $field_params = array(
                    'name' => 'user_linkedin_url',
                );
                $jobsearch_form_fields->input_field($field_params);
                ?>
            </div>
        </div>
        <div class="jobsearch-element-field">
            <div class="elem-label">
                <label><?php esc_html_e('Dribbble', 'wp-jobsearch') ?></label>
            </div>
            <div class="elem-field">
                <?php
                $field_params = array(
                    'name' => 'user_dribbble_url',
                );
                $jobsearch_form_fields->input_field($field_params);
                ?>
            </div>
        </div>

        <div class="jobsearch-elem-heading">
            <h2><?php esc_html_e('Location', 'wp-jobsearch') ?></h2>
        </div>
        <?php
        do_action('jobsearch_admin_location_map', $post->ID);

        // employer multi meta fields
        do_action('employer_multi_fields_meta', $post);
        ?>
        <div class="jobsearch-elem-heading">
            <h2><?php esc_html_e('Profile Photos', 'wp-jobsearch') ?></h2>
        </div>
        <div class="jobsearch-element-field">
            <?php
            jobsearch_admin_gallery('company_gallery_imgs', esc_html__('Add Photos', 'wp-jobsearch'));
            ?>
        </div>
        <?php
        //
        $security_questions = isset($jobsearch_plugin_options['jobsearch-security-questions']) ? $jobsearch_plugin_options['jobsearch-security-questions'] : '';
        if (!empty($security_questions) && sizeof($security_questions) >= 3) {

            $sec_questions = get_post_meta($post->ID, 'user_security_questions', true);
            if (!empty($sec_questions)) {
                ?>
                <div class="jobsearch-elem-heading"> <h2><?php esc_html_e('Security Questions', 'wp-jobsearch') ?></h2> </div>
                <?php
                $answer_to_ques = isset($sec_questions['answers']) ? $sec_questions['answers'] : '';
                $qcount = 0;
                $qcount_num = 1;
                if (!empty($answer_to_ques)) {
                    foreach ($answer_to_ques as $sec_ans) {
                        $_ques = isset($sec_questions['questions'][$qcount]) ? $sec_questions['questions'][$qcount] : '';
                        $_answer_to_ques = $sec_ans;
                        ?>
                        <div class="jobsearch-element-field">
                            <div class="elem-label">
                                <label><?php printf(esc_html__('Question No %s :', 'wp-jobsearch'), $qcount_num) ?> <span><?php echo ($_ques) ?></span></label>
                            </div>
                            <div class="elem-field">
                                <input type="hidden" name="user_security_questions[questions][]" value="<?php echo ($_ques) ?>">
                                <input type="text" name="user_security_questions[answers][]" disabled="disabled" value="<?php echo ($_answer_to_ques) ?>">
                            </div>
                        </div>
                        <?php
                        $qcount_num++;
                        $qcount++;
                    }
                }
            }
        }

        //
        $employer_id = $_post_id;
        $reults_per_page = isset($jobsearch_plugin_options['user-dashboard-per-page']) && $jobsearch_plugin_options['user-dashboard-per-page'] > 0 ? $jobsearch_plugin_options['user-dashboard-per-page'] : 10;
        $page_num = isset($_GET['page_num']) ? $_GET['page_num'] : 1;

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

        $jobs_query = new WP_Query($args);

        $total_jobs = $jobs_query->found_posts;

        $free_jobs_allow = isset($jobsearch_plugin_options['free-jobs-allow']) ? $jobsearch_plugin_options['free-jobs-allow'] : '';
        if ($jobs_query->have_posts()) {
            global $Jobsearch_User_Dashboard_Settings;

            $page_url = admin_url('post.php');
            wp_enqueue_script('jobsearch-user-dashboard');
            ?>
            <div class="jobsearch-elem-heading">
                <h2><?php esc_html_e('Manage Jobs', 'wp-jobsearch') ?></h2>
            </div>
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

                                <div class="jobsearch-table-cell"><a <?php echo ($job_applicants_count > 0 ? 'href="' . add_query_arg(array('post' => $job_id, 'action' => 'edit', 'view' => 'applicants'), $page_url) . '"' : '') ?> class="jobsearch-managejobs-appli"><?php printf(__('%s Application(s)', 'wp-jobsearch'), $job_applicants_count) ?></a></div>
                                <div class="jobsearch-table-cell">
                                    <a><i class="<?php echo ($job_is_feature == 'on' ? 'fa fa-star' : 'fa fa-star-o') ?>"></i></a>
                                </div>
                                <div class="jobsearch-table-cell"><span class="jobsearch-managejobs-option <?php echo ($job_status == 'approved' ? 'active' : '') ?><?php echo ($job_status == 'expired' || $job_status == 'canceled' ? 'expired' : '') ?>"><?php echo ($status_txt) ?></span></div>
                                <div class="jobsearch-table-cell">
                                    <div class="jobsearch-managejobs-links">
                                        <a href="<?php echo get_permalink($job_id) ?>" class="jobsearch-icon jobsearch-view dashicons dashicons-visibility"></a>
                                        <a href="<?php echo add_query_arg(array('post' => $job_id, 'action' => 'edit'), $page_url) ?>" class="jobsearch-icon jobsearch-edit dashicons dashicons-edit"></a>
                                        <a href="javascript:void(0);" data-id="<?php echo ($job_id) ?>" class="jobsearch-icon jobsearch-rubbish dashicons dashicons-trash jobsearch-trash-job"></a>
                                    </div>
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
        }
        ?>

    </div> 
    <?php
}
