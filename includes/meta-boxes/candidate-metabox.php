<?php
/**
 * Define Meta boxes for plugin
 * and theme.
 *
 */
add_action('save_post', 'jobsearch_candidates_time_save');

function jobsearch_candidates_time_save($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (isset($_POST)) {
        if (get_post_type($post_id) == 'candidate') {
            // extra save
            if (isset($_POST['jobsearch_field_user_cv_attachment']) && $_POST['jobsearch_field_user_cv_attachment'] != '') {
                $cv_file_url = $_POST['jobsearch_field_user_cv_attachment'];
                $cv_file_id = jobsearch_get_attachment_id_from_url($cv_file_url);
                if ($cv_file_id) {
                    $arg_arr = array(
                        'file_id' => $cv_file_id,
                        'file_url' => $cv_file_url,
                    );
                    update_post_meta($post_id, 'candidate_cv_file', $arg_arr);
                }
            } else {
                update_post_meta($post_id, 'candidate_cv_file', '');
            }
        }
    }
}

/**
 * Candidate settings meta box.
 */
function jobsearch_candidates_settings_meta_boxes() {
    add_meta_box('jobsearch-candidates-settings', esc_html__('Candidate Settings', 'wp-jobsearch'), 'jobsearch_candidates_meta_settings', 'candidate', 'normal');
}

/**
 * Candidate settings meta box callback.
 */
function jobsearch_candidates_meta_settings() {
    global $post, $jobsearch_form_fields, $jobsearch_plugin_options, $jobsearch_currencies_list;
    $rand_num = rand(1000000, 99999999);
    $_post_id = $post->ID;

    $job_salary_types = isset($jobsearch_plugin_options['job-salary-types']) ? $jobsearch_plugin_options['job-salary-types'] : '';

    $job_custom_currency_switch = isset($jobsearch_plugin_options['job_custom_currency']) ? $jobsearch_plugin_options['job_custom_currency'] : '';

    $candidate_posted_by = get_post_meta($post->ID, 'jobsearch_field_users', true);

    $candidate_user_id = get_post_meta($post->ID, 'jobsearch_user_id', true);

    $salar_cur_list = array('default' => esc_html__('Default', 'wp-jobsearch'));
    if (!empty($jobsearch_currencies_list)) {
        foreach ($jobsearch_currencies_list as $jobsearch_curr_key => $jobsearch_curr_item) {
            $cus_cur_name = isset($jobsearch_curr_item['name']) ? $jobsearch_curr_item['name'] : '';
            $cus_cur_symbol = isset($jobsearch_curr_item['symbol']) ? $jobsearch_curr_item['symbol'] : '';
            $salar_cur_list[$jobsearch_curr_key] = $cus_cur_name . ' - ' . $cus_cur_symbol;
        }
    }
    ?>
    <script>
        jQuery(document).ready(function () {
            jQuery('#jobsearch_candidate_publish_date').datetimepicker({
                timepicker: true,
                format: 'd-m-Y H:i:s'
            });
            jQuery('#jobsearch_candidate_expiry_date').datetimepicker({
                timepicker: true,
                format: 'd-m-Y H:i:s'
            });
        });
    </script>
    <div class="jobsearch-post-settings">
        <?php
        //
        do_action('jobsearch_candidate_meta_box_inbefore', $_post_id, $candidate_user_id);
        //
        $get_user_cand_id = get_user_meta($candidate_user_id, 'jobsearch_candidate_id', true);
        if ($get_user_cand_id != '' && $post->ID == $get_user_cand_id) {
            $user_obj = get_user_by('ID', $candidate_user_id);

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

        do_action('jobsearch_candidate_admin_meta_fields_before', $post->ID);

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
                <label><?php esc_html_e('Date of Birth', 'wp-jobsearch') ?></label>
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
                    'name' => 'candidate_approved',
                );
                $jobsearch_form_fields->checkbox_field($field_params);
                ?>
            </div>
        </div>
        <div class="jobsearch-element-field">
            <div class="elem-label">
                <label><?php esc_html_e('Job Title', 'wp-jobsearch') ?></label>
            </div>
            <div class="elem-field">
                <?php
                $field_params = array(
                    'name' => 'candidate_jobtitle',
                );
                $jobsearch_form_fields->input_field($field_params);
                ?>
            </div>
        </div>
        <?php
        $salary_onoff_switch = isset($jobsearch_plugin_options['salary_onoff_switch']) ? $jobsearch_plugin_options['salary_onoff_switch'] : '';
        if ($salary_onoff_switch == 'on') {
            ?>
            <div class="jobsearch-element-field">
                <div class="elem-label">
                    <label><?php esc_html_e('Salary', 'wp-jobsearch') ?></label>
                </div>
                <div class="elem-field">
                    <?php
                    $field_params = array(
                        'name' => 'candidate_salary',
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
                            'name' => 'candidate_salary_type',
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
                            'name' => 'candidate_salary_currency',
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
                            'name' => 'candidate_salary_pos',
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
                            'name' => 'candidate_salary_sep',
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
                            'name' => 'candidate_salary_deci',
                        );
                        $jobsearch_form_fields->input_field($field_params);
                        ?>
                    </div>
                </div>
                <?php
            }
        }

        // load custom fields which is configured in candidate custom fields
        do_action('jobsearch_custom_fields_load', $post->ID, 'candidate');
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
        <?php do_action('jobsearch_cand_admin_meta_after_social', $post->ID); ?>
        <div class="jobsearch-elem-heading">
            <h2><?php esc_html_e('Location', 'wp-jobsearch') ?></h2>
        </div>
        <?php
        do_action('jobsearch_admin_location_map', $post->ID);
        // candidate multi meta fields
        do_action('candidate_multi_fields_meta', $post);
        ?>
        <div class="jobsearch-element-field">
            <div class="elem-label">
                <label><?php esc_html_e('CV Attachment', 'wp-jobsearch') ?></label>
            </div>
            <div class="elem-field">
                <?php
                $field_params = array(
                    'id' => 'user_cv_attachment' . rand(10000000, 999999999),
                    'name' => 'user_cv_attachment',
                );
                $jobsearch_form_fields->file_upload_field($field_params);
                ?>
            </div>
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
        ?> 

    </div> 
    <?php
}
