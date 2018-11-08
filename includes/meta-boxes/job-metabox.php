<?php
/**
 * Define Meta boxes for plugin
 * and theme.
 *
 */
if (!function_exists('jobsearch_delete_job_callback')) {

    function jobsearch_delete_job_callback($post_id) {

        if (get_post_type($post_id) == 'job') {
            $job_employer_id = get_post_meta($post_id, 'jobsearch_field_job_posted_by', true); // get job employer
            $employer_job_count = get_post_meta($job_employer_id, 'jobsearch_field_employer_job_count', true); // get jobs count in employer profile
            if ($employer_job_count != '' && $employer_job_count > 0) {
                $employer_job_count--;
            }
            if ($employer_job_count < 0 || $employer_job_count == '') {
                $employer_job_count = 0;
            }
            update_post_meta($job_employer_id, 'jobsearch_field_employer_job_count', $employer_job_count); // update jobs count in employer
            update_post_meta($post_id, 'jobsearch_field_job_employer_count_updated', 'no'); // update count status in job
        }
    }

    add_action('wp_trash_post', 'jobsearch_delete_job_callback');
    add_action('delete_post', 'jobsearch_delete_job_callback');
}
if (!function_exists('jobsearch_jobs_save')) {

    function jobsearch_jobs_save($post_id, $post, $update) {
        global $pagenow;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        $post_type = '';
        if ($pagenow == 'post.php') {
            $post_type = get_post_type();
        }
        if (isset($_REQUEST)) {
            if ($post_type == 'job') {

                // extra save 
                if (isset($_POST['jobsearch_field_job_publish_date'])) {
                    if ($_POST['jobsearch_field_job_publish_date'] != '') {
                        $_posted_time = strtotime($_POST['jobsearch_field_job_publish_date']);
                        if ($_posted_time > current_time('timestamp')) {
                            $_posted_time = current_time('timestamp');
                        }
                        update_post_meta($post_id, 'jobsearch_field_job_publish_date', $_posted_time);
                    }
                }
                if (isset($_POST['jobsearch_field_job_expiry_date'])) {
                    if ($_POST['jobsearch_field_job_expiry_date'] != '') {
                        $_POST['jobsearch_field_job_expiry_date'] = strtotime($_POST['jobsearch_field_job_expiry_date']);
                        update_post_meta($post_id, 'jobsearch_field_job_expiry_date', $_POST['jobsearch_field_job_expiry_date']);
                    }
                }
                if (isset($_POST['jobsearch_field_job_application_deadline_date'])) {
                    if ($_POST['jobsearch_field_job_application_deadline_date'] != '') {
                        $_POST['jobsearch_field_job_application_deadline_date'] = strtotime($_POST['jobsearch_field_job_application_deadline_date']);
                        update_post_meta($post_id, 'jobsearch_field_job_application_deadline_date', $_POST['jobsearch_field_job_application_deadline_date']);
                    }
                }
                $post_status = get_post($post_id)->post_status;

                $user_data = wp_get_current_user();
                // update employer job count
                $job_employer_count_updated = get_post_meta($post_id, 'jobsearch_field_job_employer_count_updated', true);
                $job_employer_id = get_post_meta($post_id, 'jobsearch_field_job_posted_by', true); // get job employer 
                if ((!isset($job_employer_count_updated) || $job_employer_count_updated != 'yes' || empty($job_employer_count_updated)) && $job_employer_id != '') {

                    $employer_job_count = get_post_meta($job_employer_id, 'jobsearch_field_employer_job_count', true); // get jobs count in employer profile
                    if ($employer_job_count != '' && $employer_job_count > 0) {
                        $employer_job_count++;
                    } else {
                        $employer_job_count = 1;
                    }
                    update_post_meta($job_employer_id, 'jobsearch_field_employer_job_count', $employer_job_count); // update jobs count in employer
                    update_post_meta($post_id, 'jobsearch_field_job_employer_count_updated', 'yes'); // update count status in job
                }

                // Email Employer at Job approved by admin
                $prev_job_status = isset($_POST['jobsearch_job_presnt_status']) ? $_POST['jobsearch_job_presnt_status'] : '';
                if ($prev_job_status != '') {
                    update_post_meta($post_id, 'jobsearch_job_presnt_status', $prev_job_status);
                }

                // Employer jobs status change according his/her status
                do_action('jobsearch_employer_update_jobs_status', $job_employer_id);
            }
        }
    }

    add_action('save_post', 'jobsearch_jobs_save', 999, 3);
}

/**
 * Job settings meta box.
 */
function jobsearch_jobs_settings_meta_boxes() {
    add_meta_box('jobsearch-jobs-settings', esc_html__('Job Settings', 'wp-jobsearch'), 'jobsearch_jobs_meta_settings', 'job', 'normal');
}

/**
 * Job settings meta box callback.
 */
function jobsearch_jobs_meta_settings() {
    global $post, $jobsearch_form_fields, $jobsearch_plugin_options, $jobsearch_currencies_list;
    $rand_num = rand(1000000, 99999999);

    $job_salary_types = isset($jobsearch_plugin_options['job-salary-types']) ? $jobsearch_plugin_options['job-salary-types'] : '';

    $job_apply_deadline_sw = isset($jobsearch_plugin_options['job_appliction_deadline']) ? $jobsearch_plugin_options['job_appliction_deadline'] : '';

    $_post_id = $post->ID;
    $job_custom_currency_switch = isset($jobsearch_plugin_options['job_custom_currency']) ? $jobsearch_plugin_options['job_custom_currency'] : '';

    $job_posted_by = get_post_meta($post->ID, 'jobsearch_field_job_posted_by', true);
    $job_publish_date = get_post_meta($post->ID, 'jobsearch_field_job_publish_date', true);
    $job_publish_date = isset($job_publish_date) && $job_publish_date != '' ? date('d-m-Y H:i:s', $job_publish_date) : '';
    $job_expiry_date = get_post_meta($post->ID, 'jobsearch_field_job_expiry_date', true);
    $job_expiry_date = isset($job_expiry_date) && $job_expiry_date != '' ? date('d-m-Y H:i:s', $job_expiry_date) : '';
    $job_app_deadline_date = get_post_meta($post->ID, 'jobsearch_field_job_application_deadline_date', true);
    $job_app_deadline_date = isset($job_app_deadline_date) && $job_app_deadline_date != '' ? date('d-m-Y H:i:s', $job_app_deadline_date) : '';

    $salar_cur_list = array('default' => esc_html__('Default', 'wp-jobsearch'));
    if (!empty($jobsearch_currencies_list)) {
        foreach ($jobsearch_currencies_list as $jobsearch_curr_key => $jobsearch_curr_item) {
            $cus_cur_name = isset($jobsearch_curr_item['name']) ? $jobsearch_curr_item['name'] : '';
            $cus_cur_symbol = isset($jobsearch_curr_item['symbol']) ? $jobsearch_curr_item['symbol'] : '';
            $salar_cur_list[$jobsearch_curr_key] = $cus_cur_name . ' - ' . $cus_cur_symbol;
        }
    }

    $job_employer_id = get_post_meta($post->ID, 'jobsearch_field_job_posted_by', true);
    $job_status = get_post_meta($post->ID, 'jobsearch_field_job_status', true);
    $prev_job_status = get_post_meta($post->ID, 'jobsearch_job_presnt_status', true);
    ?>
    <script>
        jQuery(document).ready(function () {
            jQuery('#jobsearch_job_publish_date').datetimepicker({
                timepicker: true,
                format: 'd-m-Y H:i:s'
            });
            jQuery('#jobsearch_job_expiry_date').datetimepicker({
                timepicker: true,
                format: 'd-m-Y H:i:s'
            });
            jQuery('#job_application_deadline_date').datetimepicker({
                timepicker: true,
                format: 'd-m-Y H:i:s'
            });
        });
    </script>
    <div class="jobsearch-post-settings">
        <div class="jobsearch-element-field">
            <div class="elem-label">
                <label><?php esc_html_e('Publish Date', 'wp-jobsearch'); ?></label>
            </div>
            <div class="elem-field">
                <?php
                $field_params = array(
                    'force_std' => $job_publish_date,
                    'id' => 'jobsearch_job_publish_date',
                    'name' => 'job_publish_date',
                );
                $jobsearch_form_fields->input_field($field_params);
                ?>
            </div>
        </div>
        <div class="jobsearch-element-field">
            <div class="elem-label">
                <label><?php esc_html_e('Expiry Date', 'wp-jobsearch') ?></label>
            </div>
            <div class="elem-field">
                <?php
                $field_params = array(
                    'force_std' => $job_expiry_date,
                    'id' => 'jobsearch_job_expiry_date',
                    'name' => 'job_expiry_date',
                );
                $jobsearch_form_fields->input_field($field_params);
                ?>
            </div>
        </div> 

        <?php
        if ($job_apply_deadline_sw == 'on') {
            ?>
            <div class="jobsearch-element-field">
                <div class="elem-label">
                    <label><?php esc_html_e('Application Deadline Date', 'wp-jobsearch') ?></label>
                </div>
                <div class="elem-field">
                    <?php
                    $field_params = array(
                        'force_std' => $job_app_deadline_date,
                        'id' => 'job_application_deadline_date',
                        'name' => 'job_application_deadline_date',
                    );
                    $jobsearch_form_fields->input_field($field_params);
                    ?>
                </div>
            </div>
            <?php
        }
        $job_extrnal_apply_switch = isset($jobsearch_plugin_options['job-apply-extrnal-url']) ? $jobsearch_plugin_options['job-apply-extrnal-url'] : '';
        if ($job_extrnal_apply_switch == 'on') {
            ?>
            <div class="jobsearch-element-field">
                <div class="elem-label">
                    <label><?php esc_html_e('Job Apply Type', 'wp-jobsearch') ?></label>
                </div>
                <div class="elem-field">
                    <?php
                    $field_params = array(
                        'name' => 'job_apply_type',
                        'options' => array(
                            'internal' => esc_html__('Internal', 'wp-jobsearch'),
                            'external' => esc_html__('External URL', 'wp-jobsearch'),
                            'with_email' => esc_html__('By Email', 'wp-jobsearch'),
                        ),
                    );
                    $jobsearch_form_fields->select_field($field_params);
                    ?>
                </div>
            </div>
            <div class="jobsearch-element-field">
                <div class="elem-label">
                    <label><?php esc_html_e('External URL for Apply Job', 'wp-jobsearch') ?></label>
                </div>
                <div class="elem-field">
                    <?php
                    $field_params = array(
                        'name' => 'job_apply_url',
                    );
                    $jobsearch_form_fields->input_field($field_params);
                    ?>
                </div>
            </div>
            <div class="jobsearch-element-field">
                <div class="elem-label">
                    <label><?php esc_html_e('Job Apply Email', 'wp-jobsearch') ?></label>
                </div>
                <div class="elem-field">
                    <?php
                    $field_params = array(
                        'name' => 'job_apply_email',
                    );
                    $jobsearch_form_fields->input_field($field_params);
                    ?>
                </div>
            </div>
            <?php
        }
        $salary_onoff_switch = isset($jobsearch_plugin_options['salary_onoff_switch']) ? $jobsearch_plugin_options['salary_onoff_switch'] : '';
        if ($salary_onoff_switch == 'on') {
            ?>
            <div class="jobsearch-element-field">
                <div class="elem-label">
                    <label><?php esc_html_e('Min. Salary', 'wp-jobsearch') ?></label>
                </div>
                <div class="elem-field">
                    <?php
                    $field_params = array(
                        'name' => 'job_salary',
                    );
                    $jobsearch_form_fields->input_field($field_params);
                    ?>
                </div>
            </div>
            <div class="jobsearch-element-field">
                <div class="elem-label">
                    <label><?php esc_html_e('Max. Salary', 'wp-jobsearch') ?></label>
                </div>
                <div class="elem-field">
                    <?php
                    $field_params = array(
                        'name' => 'job_max_salary',
                    );
                    $jobsearch_form_fields->input_field($field_params);
                    ?>
                </div>
            </div>
            <?php
            if (!empty($job_salary_types)) {
                $salar_types = array();
                $slar_type_count = 1;
                foreach ($job_salary_types as $job_salary_type) {
                    $salar_types['type_' . $slar_type_count] = $job_salary_type;
                    $slar_type_count++;
                }
                ?>
                <div class="jobsearch-element-field">
                    <div class="elem-label">
                        <label><?php esc_html_e('Salary Type', 'wp-jobsearch') ?></label>
                    </div>
                    <div class="elem-field">
                        <?php
                        $field_params = array(
                            'name' => 'job_salary_type',
                            'options' => $salar_types,
                        );
                        $jobsearch_form_fields->select_field($field_params);
                        ?>
                    </div>
                </div>
                <?php
            }

            if ($job_custom_currency_switch == 'on') {
                ?>
                <div class="jobsearch-element-field">
                    <div class="elem-label">
                        <label><?php esc_html_e('Salary Currency', 'wp-jobsearch') ?></label>
                    </div>
                    <div class="elem-field">
                        <?php
                        $field_params = array(
                            'name' => 'job_salary_currency',
                            'options' => $salar_cur_list,
                        );
                        $jobsearch_form_fields->select_field($field_params);
                        ?>
                    </div>
                </div>
                <div class="jobsearch-element-field">
                    <div class="elem-label">
                        <label><?php esc_html_e('Currency position', 'wp-jobsearch') ?></label>
                    </div>
                    <div class="elem-field">
                        <?php
                        $field_params = array(
                            'name' => 'job_salary_pos',
                            'options' => array(
                                'left' => esc_html__('Left', 'wp-jobsearch'),
                                'right' => esc_html__('Right', 'wp-jobsearch'),
                                'left_space' => esc_html__('Left with space', 'wp-jobsearch'),
                                'right_space' => esc_html__('Right with space', 'wp-jobsearch'),
                            ),
                        );
                        $jobsearch_form_fields->select_field($field_params);
                        ?>
                    </div>
                </div>
                <div class="jobsearch-element-field">
                    <div class="elem-label">
                        <label><?php esc_html_e('Thousand separator', 'wp-jobsearch') ?></label>
                    </div>
                    <div class="elem-field">
                        <?php
                        $field_params = array(
                            'std' => ',',
                            'name' => 'job_salary_sep',
                        );
                        $jobsearch_form_fields->input_field($field_params);
                        ?>
                    </div>
                </div>
                <div class="jobsearch-element-field">
                    <div class="elem-label">
                        <label><?php esc_html_e('Number of decimals', 'wp-jobsearch') ?></label>
                    </div>
                    <div class="elem-field">
                        <?php
                        $field_params = array(
                            'std' => '2',
                            'name' => 'job_salary_deci',
                        );
                        $jobsearch_form_fields->input_field($field_params);
                        ?>
                    </div>
                </div>
                <?php
            }
        }
        ?>
        <div class="jobsearch-element-field">
            <div class="elem-label">
                <label><?php esc_html_e('Featured', 'wp-jobsearch') ?></label>
            </div>
            <div class="elem-field">
                <?php
                $field_params = array(
                    'std' => 'on',
                    'name' => 'job_featured',
                );
                $jobsearch_form_fields->checkbox_field($field_params);
                ?>
            </div>
        </div>
        <div class="jobsearch-element-field">
            <div class="elem-label">
                <label><?php esc_html_e('Filled', 'wp-jobsearch') ?></label>
            </div>
            <div class="elem-field">
                <?php
                $field_params = array(
                    'std' => 'on',
                    'name' => 'job_filled',
                    'field_desc' => esc_html__('Filled listings will no longer accept applications.', 'wp-jobsearch'),
                );
                $jobsearch_form_fields->checkbox_field($field_params);
                ?>
            </div>
        </div>
        <div class="jobsearch-element-field">
            <div class="elem-label">
                <label><?php esc_html_e('Posted By', 'wp-jobsearch') ?></label>
            </div>
            <div class="elem-field">
                <?php
                jobsearch_get_custom_post_field($job_posted_by, 'employer', esc_html__('employer', 'wp-jobsearch'), 'job_posted_by');
                //
                $job_employer_id = get_post_meta($_post_id, 'jobsearch_field_job_posted_by', true);
                if ($job_employer_id != '') {
                    $user_phone = get_post_meta($job_employer_id, 'jobsearch_field_user_phone', true);
                    $employer_user_id = jobsearch_get_employer_user_id($job_employer_id);
                    $emp_user_obj = get_user_by('ID', $employer_user_id);
                    echo '<p>' . sprintf(esc_html__('User email : %s', 'wp-jobsearch'), $emp_user_obj->user_email) . '</p>';
                    if ($user_phone != '') {
                        echo '<p>' . sprintf(esc_html__('User Phone : %s', 'wp-jobsearch'), $user_phone) . '</p>';
                    }
                }
                ?>

            </div>
        </div>

        <div class="jobsearch-element-field">
            <div class="elem-label">
                <label><?php esc_html_e('Status', 'wp-jobsearch') ?></label>
            </div>
            <div class="elem-field">
                <?php
                $status_options = array(
                    'admin-review' => esc_html__('Admin Review', 'wp-jobsearch'),
                    'pending' => esc_html__('Pending', 'wp-jobsearch'),
                    'approved' => esc_html__('Approved', 'wp-jobsearch'),
                    'canceled' => esc_html__('Canceled', 'wp-jobsearch'),
                );
                $field_params = array(
                    'name' => 'job_status',
                    'options' => $status_options,
                );
                $jobsearch_form_fields->select_field($field_params);
                ?>
                <input type="hidden" name="jobsearch_job_presnt_status" value="<?php echo ($job_status) ?>">
            </div>
        </div>
        <?php
        // load custom fields which is configured in job custom fields
        do_action('jobsearch_custom_fields_load', $post->ID, 'job');

        //
        do_action('jobsearch_job_meta_ext_fields', $post->ID);

        // before location
        do_action('jobsearch_job_admin_meta_before_location', $post->ID);
        ?>
        <div class="jobsearch-elem-heading">
            <h2><?php esc_html_e('Location', 'wp-jobsearch') ?></h2>
        </div>
        <?php do_action('jobsearch_admin_location_map', $post->ID); ?> 
        <?php
        //
        $job_packages_arr = get_post_meta($_post_id, 'attach_packages_array', true);
        if (!empty($job_packages_arr)) {
            $attached_pkg = end($job_packages_arr);
            ?>
            <div class="jobsearch-elem-heading">
                <h2><?php esc_html_e('Attached Package Info', 'wp-jobsearch') ?></h2>
            </div>
            <ul class="job-attached-pinfo">
                <?php
                if (isset($attached_pkg['package_name'])) {
                    $pkge_name = $attached_pkg['package_name'];
                    echo '<li><span class="pinfo-title">' . esc_html__('Package Name:', 'wp-jobsearch') . '</span> <span class="pinfo-value">' . $pkge_name . '</span></li>';
                }
                if (isset($attached_pkg['package_price'])) {
                    $pkge_price = $attached_pkg['package_price'];
                    echo '<li><span class="pinfo-title">' . esc_html__('Package Price:', 'wp-jobsearch') . '</span> <span class="pinfo-value">' . jobsearch_get_price_format($pkge_price) . '</span></li>';
                }
                if (isset($attached_pkg['num_of_jobs'])) {
                    $pkge_num_jobs = $attached_pkg['num_of_jobs'];
                    echo '<li><span class="pinfo-title">' . esc_html__('Number of Jobs:', 'wp-jobsearch') . '</span> <span class="pinfo-value">' . $pkge_num_jobs . '</span></li>';
                }
                if (isset($attached_pkg['job_expiry_time']) && isset($attached_pkg['job_expiry_time_unit'])) {
                    $pkg_exp_dur = $attached_pkg['job_expiry_time'];
                    $pkg_exp_dur_unit = $attached_pkg['job_expiry_time_unit'];
                    echo '<li><span class="pinfo-title">' . esc_html__('Job exipre in:', 'wp-jobsearch') . '</span> <span class="pinfo-value">' . (absint($pkg_exp_dur) . ' ' . jobsearch_get_duration_unit_str($pkg_exp_dur_unit)) . '</span></li>';
                }
                ?>
            </ul>
            <?php
        }
        //
        $job_applicants_list = jobsearch_job_applicants_sort_list($_post_id);
        if (!empty($job_applicants_list)) {
            wp_enqueue_script('jobsearch-user-dashboard');
            ?>
            <div class="jobsearch-elem-heading">
                <h2><?php esc_html_e('Applicants', 'wp-jobsearch') ?></h2>
            </div>
            <?php
            global $Jobsearch_User_Dashboard_Settings;

            $employer_user_id = jobsearch_get_employer_user_id($job_employer_id);

            $user_obj = get_user_by('ID', $employer_user_id);

            $employer_id = $job_employer_id;

            $page_url = admin_url('post.php');
            $page_num = isset($_GET['page_num']) ? $_GET['page_num'] : 1;
            $reults_per_page = isset($jobsearch_plugin_options['user-dashboard-per-page']) && $jobsearch_plugin_options['user-dashboard-per-page'] > 0 ? $jobsearch_plugin_options['user-dashboard-per-page'] : 10;

            $_job_id = $_post_id;

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
                                    add_action('admin_footer', function () use ($popup_args) {

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
                                                                    add_action('admin_footer', function () use ($popup_args) {

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
        }
        ?>
    </div> 
    <?php
    //
    if ($job_employer_id > 0 && $prev_job_status != 'approved' && $job_status == 'approved') {
        $employer_user_id = jobsearch_get_employer_user_id($job_employer_id);
        $user_obj = get_user_by('ID', $employer_user_id);
        if (isset($user_obj->ID)) {
            update_post_meta($_post_id, 'jobsearch_job_presnt_status', 'approved');
            do_action('jobsearch_job_approved_to_employer', $user_obj, $_post_id);
        }
    }
}
