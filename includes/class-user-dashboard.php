<?php

class Jobsearch_User_Dashboard_Settings {

    // Dashboard Path
    protected $dashboard_template;
    // Candidate Dashboard Path
    protected $candidate_dashboard_template;
    // Employer Dashboard Path
    protected $employer_dashboard_template;
    // user types
    protected $dashboard_user_types = array('candidate', 'employer');

    /*
     * Class Construct
     * @return
     */

    public function __construct() {
        $this->dashboard_template = 'user-dashboard';
        $this->candidate_dashboard_template = 'candidate';
        $this->employer_dashboard_template = 'employer';

        add_action('wp_ajax_jobsearch_user_dashboard_show_template', array($this, 'show_template_part'));
        add_action('jobsearch_user_dashboard_header', array($this, 'dashboard_header'));

        add_action('wp_ajax_jobsearch_employer_cover_img_remove', array($this, 'employer_cover_img_remove'));
        
        //
        add_action('wp_ajax_jobsearch_dashboard_updating_user_avatar_img', array($this, 'user_avatar_upload_ajax'));
        add_action('wp_ajax_jobsearch_dashboard_updating_employer_cover_img', array($this, 'employer_cover_img_upload'));
        add_action('wp_ajax_jobsearch_dashboard_updating_candidate_cv_file', array($this, 'candidate_cv_upload_ajax'));
        //
        add_action('wp_ajax_jobsearch_dashboard_adding_portfolio_img_url', array($this, 'dashboard_adding_portfolio_img_url'));
        //
        add_action('wp_ajax_jobsearch_dashboard_adding_team_img_url', array($this, 'dashboard_adding_team_img_url'));

        //
        add_action('wp_ajax_jobsearch_user_dashboard_candidate_delete', array($this, 'user_candidate_delete'));
        //
        add_action('wp_ajax_jobsearch_remove_user_fav_candidate_from_list', array($this, 'remove_user_fav_candidate_from_list'));

        add_action('wp_ajax_jobsearch_remove_user_applied_candidate_from_list', array($this, 'remove_user_applied_candidate_from_list'));

        add_action('wp_ajax_jobsearch_remove_user_fav_job_from_list', array($this, 'remove_candidate_fav_job_from_list'));

        add_action('wp_ajax_jobsearch_remove_user_applied_job_from_list', array($this, 'remove_candidate_applied_job_from_list'));

        //
        add_action('wp_ajax_jobsearch_add_resume_education_to_list', array($this, 'add_resume_education_to_list'));
        //
        add_action('wp_ajax_jobsearch_add_resume_experience_to_list', array($this, 'add_resume_experience_to_list'));
        //
        add_action('wp_ajax_jobsearch_add_resume_skill_to_list', array($this, 'add_resume_skill_to_list'));
        //
        add_action('wp_ajax_jobsearch_add_resume_portfolio_to_list', array($this, 'add_resume_portfolio_to_list'));
        //
        add_action('wp_ajax_jobsearch_add_team_member_to_list', array($this, 'add_team_member_to_list'));
        //
        add_action('wp_ajax_jobsearch_add_resume_award_to_list', array($this, 'add_resume_award_to_list'));

        //
        add_action('wp_ajax_jobsearch_candidate_contact_form_submit', array($this, 'candidate_contact_form_submit'));
        //
        add_action('wp_ajax_jobsearch_employer_contact_form_submit', array($this, 'employer_contact_form_submit'));

        add_action('wp_ajax_jobsearch_act_user_cv_delete', array($this, 'candidate_cv_delete_ajax'));

        add_action('wp_ajax_jobsearch_user_profile_delete_for', array($this, 'user_profile_delete_for'));

        //
        add_filter('wp_ajax_jobsearch_doing_mjobs_feature_job', array($this, 'doing_mjobs_feature_job'));
        add_filter('wp_ajax_nopriv_jobsearch_doing_mjobs_feature_job', array($this, 'doing_mjobs_feature_job'));
    }

    /*
     * User profile delete
     * @return bool
     */

    public function user_profile_delete_for() {
        global $jobsearch_plugin_options;
        $u_pass = isset($_POST['u_pass']) ? $_POST['u_pass'] : '';
        $user_id = get_current_user_id();
        $user_obj = get_user_by('ID', $user_id);
        $user_is_candidate = jobsearch_user_is_candidate($user_id);
        $user_is_employer = jobsearch_user_is_employer($user_id);

        if ($u_pass == '') {
            echo json_encode(array('success' => '0', 'msg' => esc_html__('Please Enter the password.', 'wp-jobsearch')));
            wp_die();
        }
        if ($user_obj && wp_check_password($u_pass, $user_obj->data->user_pass, $user_id)) {
            // good
        } else {
            echo json_encode(array('success' => '0', 'msg' => esc_html__('Please Enter the correct password.', 'wp-jobsearch')));
            wp_die();
        }

        if ($user_is_employer) {
            $employer_id = jobsearch_get_user_employer_id($user_id);
            //
            $demo_employer = isset($jobsearch_plugin_options['demo_employer']) ? $jobsearch_plugin_options['demo_employer'] : '';
            if ($demo_employer != '') {
                $_demo_user_obj = get_user_by('login', $demo_employer);
                $_demo_user_id = isset($_demo_user_obj->ID) ? $_demo_user_obj->ID : '';
                if ($user_id == $_demo_user_id) {
                    echo json_encode(array('success' => '0', 'msg' => esc_html__('You are not allowed to delete profile.', 'wp-jobsearch')));
                    wp_die();
                }
            }
            //
            $args = array(
                'post_type' => 'job',
                'posts_per_page' => '-1',
                'fields' => 'ids',
                'meta_query' => array(
                    array(
                        'key' => 'jobsearch_field_job_posted_by',
                        'value' => $employer_id,
                        'compare' => '=',
                    ),
                ),
            );
            $jobs_query = new WP_Query($args);
            wp_reset_postdata();
            if (isset($jobs_query->posts) && !empty($jobs_query->posts)) {
                $all_posts = $jobs_query->posts;
                foreach ($all_posts as $_post_id) {
                    wp_delete_post($_post_id);
                }
            }
            wp_delete_user($user_id);
            wp_delete_post($employer_id);
            echo json_encode(array('success' => '1', 'msg' => esc_html__('Your profile deleted successfully.', 'wp-jobsearch')));
        } else if ($user_is_candidate) {
            $candidate_id = jobsearch_get_user_candidate_id($user_id);
            //
            $demo_candidate = isset($jobsearch_plugin_options['demo_candidate']) ? $jobsearch_plugin_options['demo_candidate'] : '';
            if ($demo_candidate != '') {
                $_demo_user_obj = get_user_by('login', $demo_candidate);
                $_demo_user_id = isset($_demo_user_obj->ID) ? $_demo_user_obj->ID : '';
                if ($user_id == $_demo_user_id) {
                    echo json_encode(array('success' => '0', 'msg' => esc_html__('You are not allowed to delete profile.', 'wp-jobsearch')));
                    wp_die();
                }
            }
            //
            wp_delete_user($user_id);
            wp_delete_post($candidate_id);
            echo json_encode(array('success' => '1', 'msg' => esc_html__('Your profile deleted successfully.', 'wp-jobsearch')));
        }
        wp_die();
    }

    /*
     * User profile info
     * @return html
     */

    public function show_template_part($user_type = '', $template_name = '') {

        $ajax = false;
        if ($user_type == '' && $template_name == '') {
            $ajax = true;
            $user_type = isset($_POST['user_type']) ? $_POST['user_type'] : '';
            $template_name = isset($_POST['template_name']) ? $_POST['template_name'] : '';
        }

        $template_ext = '';
        if ($user_type == 'employer') {
            $template_ext = $this->employer_dashboard_template;
        }
        if ($user_type == 'candidate') {
            $template_ext = $this->candidate_dashboard_template;
        }

        if ($user_type != '' && !in_array($user_type, $this->dashboard_user_types)) {
            return false;
        }

        ob_start();
        jobsearch_get_template_part('user', $template_name, $this->dashboard_template . ($template_ext != '' ? '/' . $template_ext : ''));
        $html = ob_get_clean();

        if ($ajax == true) {
            echo json_encode(array('template_html' => $html));
            wp_die();
        } else {
            return $html;
        }
    }

    /*
     * User dashboard header
     * @return html
     */

    public function dashboard_header() {
        global $jobsearch_plugin_options, $diff_form_errs;

        $diff_form_errs = array();

        $signup_page_id = isset($jobsearch_plugin_options['user-login-template-page']) ? $jobsearch_plugin_options['user-login-template-page'] : '';
        $signup_page_id = jobsearch__get_post_id($signup_page_id, 'page');
        $signup_page_url = get_permalink($signup_page_id);

        if (!is_user_logged_in()) {
            if ($signup_page_id > 0 && !empty($signup_page_url)) {
                wp_safe_redirect($signup_page_url);
            } else {
                wp_safe_redirect(home_url('/'));
            }
        }

        $page_id = $user_dashboard_page = isset($jobsearch_plugin_options['user-dashboard-template-page']) ? $jobsearch_plugin_options['user-dashboard-template-page'] : '';
        $page_id = $user_dashboard_page = jobsearch__get_post_id($page_id, 'page');
        $page_url = jobsearch_wpml_lang_page_permalink($page_id, 'page'); //get_permalink($page_id);

        $current_user = wp_get_current_user();
        $user_id = get_current_user_id();
        $user_obj = get_user_by('ID', $user_id);

        $user_displayname = isset($user_obj->display_name) ? $user_obj->display_name : '';
        $user_displayname = apply_filters('jobsearch_user_display_name', $user_displayname, $user_obj);
        $user_email = isset($user_obj->user_email) ? $user_obj->user_email : '';

        //
        $user_is_candidate = jobsearch_user_is_candidate($user_id);
        $user_is_employer = jobsearch_user_is_employer($user_id);
        //
        if ($user_is_employer) {
            $employer_id = jobsearch_get_user_employer_id($user_id);
            if (jobsearch_employer_not_allow_to_mod()) {
                $diff_form_errs['user_not_allow_mod'] = true;
                return false;
            }
        } else if ($user_is_candidate) {
            $candidate_id = jobsearch_get_user_candidate_id($user_id);
            if (jobsearch_candidate_not_allow_to_mod()) {
                $diff_form_errs['user_not_allow_mod'] = true;
                return false;
            }
        }
        //

        if (isset($_POST['user_resume_form']) && $_POST['user_resume_form'] == '1') {
            if (isset($_POST['jobsearch_field_resume_cover_letter'])) {
                update_post_meta($candidate_id, 'jobsearch_field_resume_cover_letter', $_POST['jobsearch_field_resume_cover_letter']);
            }

            //
            if (isset($_POST['jobsearch_field_education_title'])) {
                update_post_meta($candidate_id, 'jobsearch_field_education_title', $_POST['jobsearch_field_education_title']);
            } else {
                update_post_meta($candidate_id, 'jobsearch_field_education_title', '');
            }
            if (isset($_POST['jobsearch_field_education_year'])) {
                update_post_meta($candidate_id, 'jobsearch_field_education_year', $_POST['jobsearch_field_education_year']);
            } else {
                update_post_meta($candidate_id, 'jobsearch_field_education_year', '');
            }
            if (isset($_POST['jobsearch_field_education_academy'])) {
                update_post_meta($candidate_id, 'jobsearch_field_education_academy', $_POST['jobsearch_field_education_academy']);
            } else {
                update_post_meta($candidate_id, 'jobsearch_field_education_academy', '');
            }
            if (isset($_POST['jobsearch_field_education_description'])) {
                update_post_meta($candidate_id, 'jobsearch_field_education_description', $_POST['jobsearch_field_education_description']);
            } else {
                update_post_meta($candidate_id, 'jobsearch_field_education_description', '');
            }

            //
            if (isset($_POST['jobsearch_field_experience_title'])) {
                update_post_meta($candidate_id, 'jobsearch_field_experience_title', $_POST['jobsearch_field_experience_title']);
            } else {
                update_post_meta($candidate_id, 'jobsearch_field_experience_title', '');
            }
            if (isset($_POST['jobsearch_field_experience_start_date'])) {
                update_post_meta($candidate_id, 'jobsearch_field_experience_start_date', $_POST['jobsearch_field_experience_start_date']);
            } else {
                update_post_meta($candidate_id, 'jobsearch_field_experience_start_date', '');
            }
            if (isset($_POST['jobsearch_field_experience_end_date'])) {
                update_post_meta($candidate_id, 'jobsearch_field_experience_end_date', $_POST['jobsearch_field_experience_end_date']);
            } else {
                update_post_meta($candidate_id, 'jobsearch_field_experience_end_date', '');
            }
            if (isset($_POST['jobsearch_field_experience_company'])) {
                update_post_meta($candidate_id, 'jobsearch_field_experience_company', $_POST['jobsearch_field_experience_company']);
            } else {
                update_post_meta($candidate_id, 'jobsearch_field_experience_company', '');
            }
            if (isset($_POST['jobsearch_field_experience_description'])) {
                update_post_meta($candidate_id, 'jobsearch_field_experience_description', $_POST['jobsearch_field_experience_description']);
            } else {
                update_post_meta($candidate_id, 'jobsearch_field_experience_description', '');
            }

            //
            if (isset($_POST['jobsearch_field_skill_title'])) {
                update_post_meta($candidate_id, 'jobsearch_field_skill_title', $_POST['jobsearch_field_skill_title']);
            } else {
                update_post_meta($candidate_id, 'jobsearch_field_skill_title', '');
            }
            if (isset($_POST['jobsearch_field_skill_percentage'])) {
                update_post_meta($candidate_id, 'jobsearch_field_skill_percentage', $_POST['jobsearch_field_skill_percentage']);
            } else {
                update_post_meta($candidate_id, 'jobsearch_field_skill_percentage', '');
            }

            //
            if (isset($_POST['jobsearch_field_award_title'])) {
                update_post_meta($candidate_id, 'jobsearch_field_award_title', $_POST['jobsearch_field_award_title']);
            } else {
                update_post_meta($candidate_id, 'jobsearch_field_award_title', '');
            }
            if (isset($_POST['jobsearch_field_award_year'])) {
                update_post_meta($candidate_id, 'jobsearch_field_award_year', $_POST['jobsearch_field_award_year']);
            } else {
                update_post_meta($candidate_id, 'jobsearch_field_award_year', '');
            }
            if (isset($_POST['jobsearch_field_award_description'])) {
                update_post_meta($candidate_id, 'jobsearch_field_award_description', $_POST['jobsearch_field_award_description']);
            } else {
                update_post_meta($candidate_id, 'jobsearch_field_award_description', '');
            }

            //
            if (isset($_POST['jobsearch_field_portfolio_title'])) {
                update_post_meta($candidate_id, 'jobsearch_field_portfolio_title', $_POST['jobsearch_field_portfolio_title']);
            } else {
                update_post_meta($candidate_id, 'jobsearch_field_portfolio_title', '');
            }
            if (isset($_POST['jobsearch_field_portfolio_image'])) {
                update_post_meta($candidate_id, 'jobsearch_field_portfolio_image', $_POST['jobsearch_field_portfolio_image']);
            } else {
                update_post_meta($candidate_id, 'jobsearch_field_portfolio_image', '');
            }
            if (isset($_POST['jobsearch_field_portfolio_url'])) {
                update_post_meta($candidate_id, 'jobsearch_field_portfolio_url', $_POST['jobsearch_field_portfolio_url']);
            } else {
                update_post_meta($candidate_id, 'jobsearch_field_portfolio_url', '');
            }
            if (isset($_POST['jobsearch_field_portfolio_vurl'])) {
                update_post_meta($candidate_id, 'jobsearch_field_portfolio_vurl', $_POST['jobsearch_field_portfolio_vurl']);
            } else {
                update_post_meta($candidate_id, 'jobsearch_field_portfolio_vurl', '');
            }

            jobsearch_candidate_skill_percent_count($user_id, 'none');
            
            do_action('jobsearch_candidate_dash_resume_save_after', $candidate_id);
        }

        if (isset($_POST['user_settings_form']) && $_POST['user_settings_form'] == '1') {

            $user_bio = isset($_POST['user_bio']) ? ($_POST['user_bio']) : '';

            if ($user_is_candidate) {

                // updating user email to member
                update_post_meta($candidate_id, 'jobsearch_field_user_email', ($user_email));

                //
                $display_name = isset($_POST['display_name']) ? sanitize_text_field($_POST['display_name']) : '';

                //
                if ($display_name != '') {
                    $up_post = array(
                        'ID' => $candidate_id,
                        'post_title' => wp_strip_all_tags($display_name),
                    );
                    wp_update_post($up_post);
                    //
                    update_post_meta($candidate_id, 'member_display_name', wp_strip_all_tags($display_name));
                }

                $up_post = array(
                    'ID' => $candidate_id,
                    'post_content' => $user_bio,
                );
                wp_update_post($up_post);

                //
                if (isset($_POST['user_sector'])) {
                    $user_sector = sanitize_text_field($_POST['user_sector']);
                    wp_set_post_terms($candidate_id, array($user_sector), 'sector', false);
                }

                //
                if (isset($_POST['candidate_salary_type'])) {
                    update_post_meta($candidate_id, 'jobsearch_field_candidate_salary_type', $_POST['candidate_salary_type']);
                }
                if (isset($_POST['candidate_salary'])) {
                    update_post_meta($candidate_id, 'jobsearch_field_candidate_salary', $_POST['candidate_salary']);
                }

                //
                // candidate salary currency
                if (isset($_POST['candidate_salary_currency'])) {
                    $candidate_salary_type = ($_POST['candidate_salary_currency']);
                    update_post_meta($candidate_id, 'jobsearch_field_candidate_salary_currency', $candidate_salary_type);
                }
                // candidate salary currency pos
                if (isset($_POST['candidate_salary_pos'])) {
                    $candidate_salary_type = sanitize_text_field($_POST['candidate_salary_pos']);
                    update_post_meta($candidate_id, 'jobsearch_field_candidate_salary_pos', $candidate_salary_type);
                }
                // candidate salary currency decimal
                if (isset($_POST['candidate_salary_deci'])) {
                    $candidate_salary_type = sanitize_text_field($_POST['candidate_salary_deci']);
                    update_post_meta($candidate_id, 'jobsearch_field_candidate_salary_deci', $candidate_salary_type);
                }
                // candidate salary currency sep
                if (isset($_POST['candidate_salary_sep'])) {
                    $candidate_salary_type = sanitize_text_field($_POST['candidate_salary_sep']);
                    update_post_meta($candidate_id, 'jobsearch_field_candidate_salary_sep', $candidate_salary_type);
                }

                //
                if (isset($_POST['user_dob_dd'])) {
                    update_post_meta($candidate_id, 'jobsearch_field_user_dob_dd', $_POST['user_dob_dd']);
                }
                if (isset($_POST['user_dob_mm'])) {
                    update_post_meta($candidate_id, 'jobsearch_field_user_dob_mm', $_POST['user_dob_mm']);
                }
                if (isset($_POST['user_dob_yy'])) {
                    update_post_meta($candidate_id, 'jobsearch_field_user_dob_yy', $_POST['user_dob_yy']);
                }

                if (isset($_POST['user_phone'])) {
                    $user_inp_phone = $_POST['user_phone'];
                    $user_inp_phone = jobsearch_is_valid_phone_number($user_inp_phone);
                    if (isset($user_inp_phone[0]) && $user_inp_phone[0] != '') {
                        update_post_meta($candidate_id, 'jobsearch_field_user_phone', $user_inp_phone[0]);
                    }
                }
                //
                jobsearch_candidate_skill_percent_count($user_id, 'none');
                //
                
                do_action('jobsearch_candidate_profile_save_after', $candidate_id);
                
            } else if ($user_is_employer) {

                // Gallery ////////////////////////
                $gal_ids_arr = array();

                $max_gal_imgs_allow = isset($jobsearch_plugin_options['max_gal_imgs_allow']) && $jobsearch_plugin_options['max_gal_imgs_allow'] > 0 ? $jobsearch_plugin_options['max_gal_imgs_allow'] : 5;
                $number_of_gal_imgs = $max_gal_imgs_allow;

                if (isset($_POST['company_gallery_imgs']) && !empty($_POST['company_gallery_imgs'])) {
                    $gal_ids_arr = array_merge($gal_ids_arr, $_POST['company_gallery_imgs']);
                }

                $gal_imgs_count = 0;
                if (!empty($gal_ids_arr)) {
                    $gal_imgs_count = sizeof($gal_ids_arr);
                }

                $gall_ids = jobsearch_gallery_upload_attach('user_profile_gallery_imgs', $gal_imgs_count, 'ids');

                if (!empty($gall_ids)) {
                    $gal_ids_arr = array_merge($gal_ids_arr, $gall_ids);
                }

                if (!empty($gal_ids_arr) && $number_of_gal_imgs > 0) {
                    $gal_ids_arr = array_slice($gal_ids_arr, 0, $number_of_gal_imgs, true);
                }

                update_post_meta($employer_id, 'jobsearch_field_company_gallery_imgs', $gal_ids_arr);

                //
                // updating user email to member
                update_post_meta($employer_id, 'jobsearch_field_user_email', ($user_email));

                //
                $display_name = isset($_POST['display_name']) ? sanitize_text_field($_POST['display_name']) : '';

                //
                if ($display_name != '') {
                    $up_post = array(
                        'ID' => $employer_id,
                        'post_title' => wp_strip_all_tags($display_name),
                    );
                    wp_update_post($up_post);
                    //
                    update_post_meta($employer_id, 'member_display_name', wp_strip_all_tags($display_name));
                }

                $up_post = array(
                    'ID' => $employer_id,
                    'post_content' => $user_bio,
                );
                wp_update_post($up_post);

                //
                if (isset($_POST['user_sector'])) {
                    $user_sector = sanitize_text_field($_POST['user_sector']);
                    wp_set_post_terms($employer_id, array($user_sector), 'sector', false);
                }

                //
                if (isset($_POST['user_dob_dd'])) {
                    update_post_meta($employer_id, 'jobsearch_field_user_dob_dd', $_POST['user_dob_dd']);
                }
                if (isset($_POST['user_dob_mm'])) {
                    update_post_meta($employer_id, 'jobsearch_field_user_dob_mm', $_POST['user_dob_mm']);
                }
                if (isset($_POST['user_dob_yy'])) {
                    update_post_meta($employer_id, 'jobsearch_field_user_dob_yy', $_POST['user_dob_yy']);
                }

                if (isset($_POST['user_phone'])) {
                    $user_inp_phone = $_POST['user_phone'];
                    $user_inp_phone = jobsearch_is_valid_phone_number($user_inp_phone);
                    if (isset($user_inp_phone[0]) && $user_inp_phone[0] != '') {
                        update_post_meta($employer_id, 'jobsearch_field_user_phone', $user_inp_phone[0]);
                    }
                }
                //
            }


            $user_bio = isset($_POST['user_bio']) ? sanitize_text_field($_POST['user_bio']) : '';
            $user_website = isset($_POST['user_website']) ? sanitize_text_field($_POST['user_website']) : '';
            $user_def_array = array(
                'ID' => $user_id,
                'description' => $user_bio,
                'user_url' => $user_website,
            );
            if ($display_name != '') {
                $user_def_array['display_name'] = $display_name;
            }

            wp_update_user($user_def_array);
            //
        }
        //
        if (isset($_POST['user_password_change_form']) && $_POST['user_password_change_form'] == '1') {

            $old_pass = isset($_POST['old_pass']) ? $_POST['old_pass'] : '';
            $new_pass = isset($_POST['new_pass']) ? $_POST['new_pass'] : '';

            $security_switch = isset($jobsearch_plugin_options['security-questions-switch']) ? $jobsearch_plugin_options['security-questions-switch'] : '';

            $security_questions = isset($jobsearch_plugin_options['jobsearch-security-questions']) ? $jobsearch_plugin_options['jobsearch-security-questions'] : '';

            if ($security_switch == 'on') {
                //
                if ($user_is_employer) {
                    $sec_questions = get_post_meta($employer_id, 'user_security_questions', true);
                } else {
                    $sec_questions = get_post_meta($candidate_id, 'user_security_questions', true);
                }

                if (!empty($security_questions) && sizeof($security_questions) >= 3 && empty($sec_questions)) {
                    $input_quest_answers = isset($_POST['user_security_questions']) ? $_POST['user_security_questions'] : '';
                    $_input_ques = isset($input_quest_answers['questions']) ? $input_quest_answers['questions'] : '';
                    $_input_answers = isset($input_quest_answers['answers']) ? $input_quest_answers['answers'] : '';
                    $minimum_ans_num = 2;
                    if (!empty($_input_answers)) {
                        $_fill_ans_count = 0;
                        foreach ($_input_answers as $_inp_ans) {
                            $_fill_ans_count = $_inp_ans != '' ? $_fill_ans_count + 1 : $_fill_ans_count;
                        }
                        if ($_fill_ans_count < $minimum_ans_num) {
                            $diff_form_errs['min_questions_err'] = $minimum_ans_num;
                        }
                    }
                    //
                } else if (!empty($security_questions) && sizeof($security_questions) >= 3 && !empty($sec_questions) && $old_pass != '' && $new_pass != '') {
                    $answer_to_ques = isset($sec_questions['answers']) ? $sec_questions['answers'] : '';
                    $input_quest_answers = isset($_POST['user_security_quests']) ? $_POST['user_security_quests'] : '';
                    $_input_answers = isset($input_quest_answers['answers']) ? $input_quest_answers['answers'] : '';
                    if (!empty($_input_answers) && !empty($answer_to_ques)) {
                        $ans_count = 0;
                        foreach ($_input_answers as $_inp_ans) {
                            $ans_to_ques = isset($sec_questions['answers'][$ans_count]) ? $sec_questions['answers'][$ans_count] : '';
                            if ($ans_to_ques != '' && $ans_to_ques != $_inp_ans) {
                                $diff_form_errs['wrong_ans_err'] = true;
                            }
                            $ans_count++;
                        }
                    }
                }

                if (empty($diff_form_errs)) {
                    if ($user_is_employer) {
                        $sec_questions = get_post_meta($employer_id, 'user_security_questions', true);
                        //
                        if (isset($_POST['user_security_questions'])) {
                            update_post_meta($employer_id, 'user_security_questions', ($_POST['user_security_questions']));
                        }
                        //
                    } else {
                        $sec_questions = get_post_meta($candidate_id, 'user_security_questions', true);
                        //
                        if (isset($_POST['user_security_questions'])) {
                            update_post_meta($candidate_id, 'user_security_questions', ($_POST['user_security_questions']));
                        }
                        //
                    }
                }
            }

            if ($old_pass != '' && $new_pass != '') {
                if ($user_obj && wp_check_password($old_pass, $user_obj->data->user_pass, $user_obj->ID)) {
                    //
                } else {
                    $diff_form_errs['old_pass_not_matched'] = true;
                }
            }

            if (empty($diff_form_errs)) {
                $old_pass = isset($_POST['old_pass']) ? $_POST['old_pass'] : '';
                $new_pass = isset($_POST['new_pass']) ? $_POST['new_pass'] : '';
                $pass_changed = false;
                if ($old_pass != '' && $new_pass != '') {
                    if ($user_obj && wp_check_password($old_pass, $user_obj->data->user_pass, $user_obj->ID)) {
                        $user_def_array = array();
                        $user_def_array['user_pass'] = $new_pass;
                        wp_update_user($user_def_array);
                        $pass_changed = true;
                    } else {
                        $diff_form_errs['old_pass_not_matched'] = true;
                    }
                }
            }
            //
        }
        //
    }

    public function user_avatar_upload_ajax() {

        $user_id = get_current_user_id();

        $user_is_candidate = jobsearch_user_is_candidate($user_id);
        $user_is_employer = jobsearch_user_is_employer($user_id);

        if (jobsearch_candidate_not_allow_to_mod()) {
            $msg = esc_html__('You are not allowed to upload profile image.', 'wp-jobsearch');
            echo json_encode(array('err_msg' => $msg));
            die;
        }
        if (jobsearch_employer_not_allow_to_mod()) {
            $msg = esc_html__('You are not allowed to upload profile image.', 'wp-jobsearch');
            echo json_encode(array('err_msg' => $msg));
            die;
        }
        if ($user_is_employer) {
            $employer_id = jobsearch_get_user_employer_id($user_id);
            $atach_id = jobsearch_insert_upload_attach('avatar_file', $employer_id);
        } else {
            $candidate_id = jobsearch_get_user_candidate_id($user_id);
            $atach_id = jobsearch_insert_upload_attach('avatar_file', $candidate_id);
        }

        if ($atach_id > 0) {
            $user_thumbnail_image = wp_get_attachment_image_src($atach_id, 'thumbnail');
            $user_def_avatar_url = isset($user_thumbnail_image[0]) && esc_url($user_thumbnail_image[0]) != '' ? $user_thumbnail_image[0] : '';

            echo json_encode(array('imgUrl' => $user_def_avatar_url));
        }
        wp_die();
    }

    public function employer_cover_img_upload() {

        $user_id = get_current_user_id();

        $user_is_employer = jobsearch_user_is_employer($user_id);

        if (jobsearch_employer_not_allow_to_mod()) {
            $msg = esc_html__('You are not allowed to upload cover image.', 'wp-jobsearch');
            echo json_encode(array('err_msg' => $msg));
            die;
        }
        if ($user_is_employer) {
            $employer_id = jobsearch_get_user_employer_id($user_id);
            $atach_id = jobsearch_insert_upload_attach('user_cvr_photo', 0);
        }

        if (isset($atach_id) && $atach_id > 0) {
            if (class_exists('JobSearchMultiPostThumbnails')) {
                JobSearchMultiPostThumbnails::set_front_thumbnail($employer_id, $atach_id, 'cover-image');
            }

            $user_thumbnail_image = wp_get_attachment_image_src($atach_id, 'full');
            $user_def_avatar_url = isset($user_thumbnail_image[0]) && esc_url($user_thumbnail_image[0]) != '' ? $user_thumbnail_image[0] : '';

            echo json_encode(array('imgUrl' => $user_def_avatar_url));
        }
        wp_die();
    }
    
    public function employer_cover_img_remove() {

        $user_id = get_current_user_id();

        $user_is_employer = jobsearch_user_is_employer($user_id);

        if (jobsearch_employer_not_allow_to_mod()) {
            $msg = esc_html__('You are not allowed to upload cover image.', 'wp-jobsearch');
            echo json_encode(array('err_msg' => $msg));
            die;
        }
        if ($user_is_employer) {
            $employer_id = jobsearch_get_user_employer_id($user_id);
            JobSearchMultiPostThumbnails::remove_front_thumbnail($employer_id, 'cover-image');
            echo json_encode(array('success' => '1'));
        }

        wp_die();
    }

    public function candidate_cv_upload_ajax() {

        global $jobsearch_plugin_options;

        $user_id = get_current_user_id();

        $user_is_candidate = jobsearch_user_is_candidate($user_id);

        if ($user_is_candidate) {
            if (jobsearch_candidate_not_allow_to_mod()) {
                $msg = esc_html__('You are not allowed to upload file.', 'wp-jobsearch');
                echo json_encode(array('err_msg' => $msg));
                die;
            }
            $multiple_cv_files_allow = isset($jobsearch_plugin_options['multiple_cv_uploads']) ? $jobsearch_plugin_options['multiple_cv_uploads'] : '';

            $candidate_id = jobsearch_get_user_candidate_id($user_id);
            $atach_id = jobsearch_upload_candidate_cv('candidate_cv_file', $candidate_id);

            if ($atach_id > 0) {
                $file_url = wp_get_attachment_url($atach_id);
                if ($file_url) {
                    if ($multiple_cv_files_allow == 'on') {
                        $arg_arr = array(
                            'file_id' => $atach_id,
                            'file_url' => $file_url,
                            'primary' => '',
                        );
                        $ca_at_cv_files = get_post_meta($candidate_id, 'candidate_cv_files', true);
                        $ca_jat_cv_files = get_post_meta($candidate_id, 'jobsearch_field_user_cv_attachments', true);
                        $ca_at_cv_files = !empty($ca_at_cv_files) ? $ca_at_cv_files : array();
                        $ca_jat_cv_files = !empty($ca_jat_cv_files) ? $ca_jat_cv_files : array();

                        $ca_at_cv_files[$atach_id] = $arg_arr;
                        $ca_jat_cv_files[$atach_id] = $arg_arr;
                        update_post_meta($candidate_id, 'candidate_cv_files', $ca_at_cv_files);
                        update_post_meta($candidate_id, 'jobsearch_field_user_cv_attachments', $ca_jat_cv_files);
                    } else {
                        $arg_arr = array(
                            'file_id' => $atach_id,
                            'file_url' => $file_url,
                        );
                        update_post_meta($candidate_id, 'candidate_cv_file', $arg_arr);
                        update_post_meta($candidate_id, 'jobsearch_field_user_cv_attachment', $file_url);
                    }
                }

                $cv_file_title = get_the_title($atach_id);
                $attach_post = get_post($atach_id);

                $attach_date = isset($attach_post->post_date) ? $attach_post->post_date : '';
                $attach_mime = isset($attach_post->post_mime_type) ? $attach_post->post_mime_type : '';

                if ($attach_mime == 'application/pdf') {
                    $attach_icon = 'fa fa-file-pdf-o';
                } else if ($attach_mime == 'application/msword' || $attach_mime == 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
                    $attach_icon = 'fa fa-file-word-o';
                } else {
                    $attach_icon = 'fa fa-file-word-o';
                }

                ob_start();
                ?>
                <div class="jobsearch-cv-manager-list"<?php echo ($multiple_cv_files_allow == 'on' ? '' : ' style="display:none;"') ?>>
                    <ul class="jobsearch-row">
                        <li class="jobsearch-column-12">
                            <div class="jobsearch-cv-manager-wrap">
                                <a class="jobsearch-cv-manager-thumb"><i class="<?php echo ($attach_icon) ?>"></i></a>
                                <div class="jobsearch-cv-manager-text">
                                    <div class="jobsearch-cv-manager-left">
                                        <h2><a href="<?php echo ($file_url) ?>" download="<?php echo ($cv_file_title) ?>"><?php echo (strlen($cv_file_title) > 40 ? substr($cv_file_title, 0, 40) . '...' : $cv_file_title) ?></a></h2>
                                        <?php
                                        if ($attach_date != '') {
                                            ?>
                                            <ul>
                                                <li><i class="fa fa-calendar"></i> <?php echo date_i18n(get_option('date_format'), strtotime($attach_date)) . ' ' . date_i18n(get_option('time_format'), strtotime($attach_date)) ?></li>
                                            </ul>
                                            <?php
                                        }
                                        ?>
                                    </div>
                                    <a href="javascript:void(0);" class="jobsearch-cv-manager-link jobsearch-del-user-cv" data-id="<?php echo ($atach_id) ?>"><i class="jobsearch-icon jobsearch-rubbish"></i></a>
                                    <a href="<?php echo ($file_url) ?>" class="jobsearch-cv-manager-link jobsearch-cv-manager-download" download="<?php echo ($cv_file_title) ?>"><i class="jobsearch-icon jobsearch-download-arrow"></i></a>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
                <?php
                $file_html = ob_get_clean();

                echo json_encode(array('fileUrl' => $file_url, 'filehtml' => $file_html));
            }
        }
        wp_die();
    }

    public function candidate_cv_delete_ajax() {
        global $jobsearch_plugin_options;

        $user_id = get_current_user_id();

        $user_is_candidate = jobsearch_user_is_candidate($user_id);

        $multiple_cv_files_allow = isset($jobsearch_plugin_options['multiple_cv_uploads']) ? $jobsearch_plugin_options['multiple_cv_uploads'] : '';

        if ($user_is_candidate) {
            if (jobsearch_candidate_not_allow_to_mod()) {
                $msg = esc_html__('You are not allowed to delete this.', 'wp-jobsearch');
                echo json_encode(array('err_msg' => $msg));
                die;
            }
            $candidate_id = jobsearch_get_user_candidate_id($user_id);

            if ($multiple_cv_files_allow == 'on') {
                $attach_id = isset($_POST['attach_id']) ? $_POST['attach_id'] : '';
                $ca_at_cv_files = get_post_meta($candidate_id, 'candidate_cv_files', true);
                $ca_jat_cv_files = get_post_meta($candidate_id, 'jobsearch_field_user_cv_attachments', true);
                $ca_at_cv_files = !empty($ca_at_cv_files) ? $ca_at_cv_files : array();
                $ca_jat_cv_files = !empty($ca_jat_cv_files) ? $ca_jat_cv_files : array();

                if ($attach_id > 0 && isset($ca_at_cv_files[$attach_id])) {
                    unset($ca_at_cv_files[$attach_id]);
                    update_post_meta($candidate_id, 'candidate_cv_files', $ca_at_cv_files);
                }
                if ($attach_id > 0 && isset($ca_jat_cv_files[$attach_id])) {
                    unset($ca_jat_cv_files[$attach_id]);
                    update_post_meta($candidate_id, 'jobsearch_field_user_cv_attachments', $ca_jat_cv_files);
                }
                wp_delete_attachment($attach_id, true);
            } else {
                $candidate_cv_file = get_post_meta($candidate_id, 'candidate_cv_file', true);
                $file_attach_id = isset($candidate_cv_file['file_id']) ? $candidate_cv_file['file_id'] : '';
                $file_url = isset($candidate_cv_file['file_url']) ? $candidate_cv_file['file_url'] : '';

                if ($file_attach_id) {
                    wp_delete_attachment($file_attach_id, true);
                }

                update_post_meta($candidate_id, 'candidate_cv_file', '');
                update_post_meta($candidate_id, 'jobsearch_field_user_cv_attachment', '');
            }

            echo json_encode(array('delete' => '1'));
        }
        wp_die();
    }

    public function user_candidate_delete() {
        $candidate_id = isset($_POST['candidate_id']) ? ($_POST['candidate_id']) : '';
        if (jobsearch_employer_not_allow_to_mod()) {
            $msg = esc_html__('You are not allowed to delete this.', 'wp-jobsearch');
            echo json_encode(array('err_msg' => $msg));
            die;
        }
        if (jobsearch_is_employer_job($candidate_id)) {

            wp_delete_post($candidate_id, true);
            echo json_encode(array('msg' => 'deleted'));
        }
        wp_die();
    }

    public function remove_user_fav_candidate_from_list() {
        $candidate_id = isset($_POST['candidate_id']) ? ($_POST['candidate_id']) : '';

        $user_id = get_current_user_id();
        if (jobsearch_candidate_not_allow_to_mod()) {
            $msg = esc_html__('You are not allowed to delete this.', 'wp-jobsearch');
            echo json_encode(array('err_msg' => $msg));
            die;
        }
        $candidate_id = jobsearch_get_user_candidate_id($user_id);
        if ($candidate_id > 0) {
            $candidate_fav_jobs_list = get_post_meta($candidate_id, 'jobsearch_fav_jobs_list', true);
            if ($candidate_fav_jobs_list != '') {
                $candidate_fav_jobs_list = explode(',', $candidate_fav_jobs_list);
                if (in_array($candidate_id, $candidate_fav_jobs_list)) {
                    $candidate_key = array_search($candidate_id, $candidate_fav_jobs_list);
                    unset($candidate_fav_jobs_list[$candidate_key]);

                    $candidate_fav_jobs_list = implode(',', $candidate_fav_jobs_list);
                    update_post_meta($candidate_id, 'jobsearch_fav_jobs_list', $candidate_fav_jobs_list);
                }
            }
        }
        echo json_encode(array('msg' => esc_html__('removed.', 'wp-jobsearch')));
        die;
    }

    public function remove_user_applied_candidate_from_list() {
        $candidate_id = isset($_POST['candidate_id']) ? ($_POST['candidate_id']) : '';
        $candidate_key = isset($_POST['candidate_key']) ? ($_POST['candidate_key']) : '';

        $user_id = get_current_user_id();
        $user_applied_jobs_list = get_user_meta($user_id, 'jobsearch-user-jobs-applied-list', true);

        if (jobsearch_candidate_not_allow_to_mod()) {
            $msg = esc_html__('You are not allowed to delete this.', 'wp-jobsearch');
            echo json_encode(array('err_msg' => $msg));
            die;
        }

        if (!empty($user_applied_jobs_list)) {

            $finded_row = jobsearch_find_in_multiarray($candidate_id, $user_applied_jobs_list, 'post_id');

            if ($finded_row) {
                $user_applied_jobs_list = remove_index_from_array($user_applied_jobs_list, $finded_row);
                update_user_meta($user_id, 'jobsearch-user-jobs-applied-list', $user_applied_jobs_list);
            }
        }

        echo json_encode(array('msg' => esc_html__('removed.', 'wp-jobsearch')));
        die;
    }

    public function remove_candidate_fav_job_from_list() {
        $job_id = isset($_POST['job_id']) ? ($_POST['job_id']) : '';

        $user_id = get_current_user_id();
        $candidate_id = jobsearch_get_user_candidate_id($user_id);
        $candidate_fav_jobs_list = get_post_meta($candidate_id, 'jobsearch_fav_jobs_list', true);
        $candidate_fav_jobs_list = $candidate_fav_jobs_list != '' ? explode(',', $candidate_fav_jobs_list) : array();

        if (jobsearch_candidate_not_allow_to_mod()) {
            $msg = esc_html__('You are not allowed to delete this.', 'wp-jobsearch');
            echo json_encode(array('err_msg' => $msg));
            die;
        }

        if (!empty($candidate_fav_jobs_list)) {

            if (($key = array_search($job_id, $candidate_fav_jobs_list)) !== false) {
                unset($candidate_fav_jobs_list[$key]);

                $candidate_fav_jobs_list = implode(',', $candidate_fav_jobs_list);
                update_post_meta($candidate_id, 'jobsearch_fav_jobs_list', $candidate_fav_jobs_list);
            }
        }

        echo json_encode(array('msg' => esc_html__('removed.', 'wp-jobsearch')));
        die;
    }

    public function remove_candidate_applied_job_from_list() {
        $job_id = isset($_POST['job_id']) ? ($_POST['job_id']) : '';

        $user_id = get_current_user_id();
        $candidate_id = jobsearch_get_user_candidate_id($user_id);
        $job_applicants_list = get_post_meta($job_id, 'jobsearch_job_applicants_list', true);
        $job_applicants_list = $job_applicants_list != '' ? explode(',', $job_applicants_list) : array();

        if (jobsearch_candidate_not_allow_to_mod()) {
            $msg = esc_html__('You are not allowed to delete this.', 'wp-jobsearch');
            echo json_encode(array('err_msg' => $msg));
            die;
        }

        if (!empty($job_applicants_list)) {

            if (($key = array_search($candidate_id, $job_applicants_list)) !== false) {
                unset($job_applicants_list[$key]);

                $job_applicants_list = implode(',', $job_applicants_list);
                update_post_meta($job_id, 'jobsearch_job_applicants_list', $job_applicants_list);
                jobsearch_remove_user_meta_list($job_id, 'jobsearch-user-jobs-applied-list', $user_id);
            }
        }

        echo json_encode(array('msg' => esc_html__('removed.', 'wp-jobsearch')));
        die;
    }

    public function add_resume_education_to_list() {
        $title = isset($_POST['title']) ? ($_POST['title']) : '';
        $year = isset($_POST['year']) ? ($_POST['year']) : '';
        $institute = isset($_POST['institute']) ? ($_POST['institute']) : '';
        $desc = isset($_POST['desc']) ? ($_POST['desc']) : '';

        if ($title != '' && $year != '' && $institute != '') {
            $html = '
            <li class="jobsearch-column-12 resume-list-item resume-list-edu">
                <div class="jobsearch-resume-education-wrap">
                    <small>' . $year . '</small>
                    <h2><a>' . $title . '</a></h2>
                    <span>' . $institute . '</span>
                </div>
                <div class="jobsearch-resume-education-btn">
                    <a href="javascript:void(0);" class="jobsearch-icon jobsearch-edit update-resume-item"></a>
                    <a href="javascript:void(0);" class="jobsearch-icon jobsearch-rubbish del-resume-item"></a>
                </div>
                <div class="jobsearch-add-popup jobsearch-update-resume-items-sec">
                    <ul class="jobsearch-row jobsearch-employer-profile-form">
                        <li class="jobsearch-column-12">
                            <label>' . esc_html__('Title *', 'wp-jobsearch') . '</label>
                            <input name="jobsearch_field_education_title[]" type="text" value="' . $title . '">
                        </li>
                        <li class="jobsearch-column-6">
                            <label>' . esc_html__('Year *', 'wp-jobsearch') . '</label>
                            <input name="jobsearch_field_education_year[]" type="text" value="' . $year . '">
                        </li>
                        <li class="jobsearch-column-6">
                            <label>' . esc_html__('Institute *', 'wp-jobsearch') . '</label>
                            <input name="jobsearch_field_education_academy[]" type="text" value="' . $institute . '">
                        </li>
                        <li class="jobsearch-column-12">
                            <label>' . esc_html__('Description', 'wp-jobsearch') . '</label>
                            <textarea name="jobsearch_field_education_description[]">' . $desc . '</textarea>
                        </li>
                        <li class="jobsearch-column-12">
                            <input class="update-resume-list-btn" type="submit" value="' . esc_html__('Update', 'wp-jobsearch') . '">
                        </li>
                    </ul>
                </div>
            </li>';

            echo json_encode(array('msg' => esc_html__('Added Successfully.', 'wp-jobsearch'), 'html' => $html));
            die;
        }

        echo json_encode(array('msg' => esc_html__('Please fill necessary fields.', 'wp-jobsearch'), 'error' => '1'));
        die;
    }

    public function add_resume_experience_to_list() {
        $rand_num = rand(1000000, 99999999);
        $title = isset($_POST['title']) ? ($_POST['title']) : '';
        $start_date = isset($_POST['start_date']) ? ($_POST['start_date']) : '';
        $end_date = isset($_POST['end_date']) ? ($_POST['end_date']) : '';
        $company = isset($_POST['company']) ? ($_POST['company']) : '';
        $desc = isset($_POST['desc']) ? ($_POST['desc']) : '';

        if ($title != '' && $start_date != '' && $company != '') {
            $html = '
            <li class="jobsearch-column-12 resume-list-item resume-list-exp">
                <div class="jobsearch-resume-education-wrap">
                    <small>' . ($start_date != '' ? date_i18n('d M, Y', strtotime($start_date)) : '') . '</small>
                    <h2><a>' . $title . '</a></h2>
                    <span>' . $company . '</span>
                </div>
                <div class="jobsearch-resume-education-btn">
                    <a href="javascript:void(0);" class="jobsearch-icon jobsearch-edit update-resume-item"></a>
                    <a href="javascript:void(0);" class="jobsearch-icon jobsearch-rubbish del-resume-item"></a>
                </div>
                <div class="jobsearch-add-popup jobsearch-update-resume-items-sec">
                    <script>
                        jQuery(document).ready(function () {
                            jQuery("#date-start-' . $rand_num . '").datetimepicker({
                                timepicker: false,
                                format: "d-m-Y"
                            });
                            jQuery("#date-end-' . $rand_num . '").datetimepicker({
                                timepicker: false,
                                format: "d-m-Y"
                            });
                        });
                    </script>
                    <ul class="jobsearch-row jobsearch-employer-profile-form">
                        <li class="jobsearch-column-12">
                            <label>' . esc_html__('Title *', 'wp-jobsearch') . '</label>
                            <input name="jobsearch_field_experience_title[]" type="text" value="' . $title . '">
                        </li>
                        <li class="jobsearch-column-6">
                            <label>' . esc_html__('Start Date *', 'wp-jobsearch') . '</label>
                            <input id="date-start-' . $rand_num . '" name="jobsearch_field_experience_start_date[]" type="text" value="' . $start_date . '">
                        </li>
                        <li class="jobsearch-column-6">
                            <label>' . esc_html__('End Date', 'wp-jobsearch') . '</label>
                            <input id="date-end-' . $rand_num . '" name="jobsearch_field_experience_end_date[]" type="text" value="' . $end_date . '">
                        </li>
                        <li class="jobsearch-column-12">
                            <label>' . esc_html__('Company *', 'wp-jobsearch') . '</label>
                            <input name="jobsearch_field_experience_company[]" type="text" value="' . $company . '">
                        </li>
                        <li class="jobsearch-column-12">
                            <label>' . esc_html__('Description', 'wp-jobsearch') . '</label>
                            <textarea name="jobsearch_field_experience_description[]">' . $desc . '</textarea>
                        </li>
                        <li class="jobsearch-column-12">
                            <input class="update-resume-list-btn" type="submit" value="' . esc_html__('Update', 'wp-jobsearch') . '">
                        </li>
                    </ul>
                </div>
            </li>';

            echo json_encode(array('msg' => esc_html__('Added Successfully.', 'wp-jobsearch'), 'html' => $html));
            die;
        }

        echo json_encode(array('msg' => esc_html__('Please fill necessary fields.', 'wp-jobsearch'), 'error' => '1'));
        die;
    }

    public function add_resume_skill_to_list() {
        $rand_num = rand(1000000, 99999999);
        $title = isset($_POST['title']) ? ($_POST['title']) : '';
        $skill_percentage = isset($_POST['skill_percentage']) ? ($_POST['skill_percentage']) : '';

        if ($title != '' && $skill_percentage != '') {
            $html = '
            <li class="jobsearch-column-12 resume-list-item resume-list-skill">
                <div class="jobsearch-add-skills-wrap">
                    <small>' . $skill_percentage . '</small>
                    <h2><a>' . $title . '</a></h2>
                </div>
                <div class="jobsearch-resume-education-btn">
                    <a href="javascript:void(0);" class="jobsearch-icon jobsearch-edit update-resume-item"></a>
                    <a href="javascript:void(0);" class="jobsearch-icon jobsearch-rubbish del-resume-item"></a>
                </div>
                <div class="jobsearch-add-popup jobsearch-update-resume-items-sec">
                    <ul class="jobsearch-row jobsearch-employer-profile-form">
                        <li class="jobsearch-column-12">
                            <label>' . esc_html__('Title *', 'wp-jobsearch') . '</label>
                            <input name="jobsearch_field_skill_title[]" type="text" value="' . $title . '">
                        </li>
                        <li class="jobsearch-column-6">
                            <label>' . esc_html__('Percentage *', 'wp-jobsearch') . '</label>
                            <input name="jobsearch_field_skill_percentage[]" type="text" value="' . $skill_percentage . '">
                        </li>
                        <li class="jobsearch-column-12">
                            <input class="update-resume-list-btn" type="submit" value="' . esc_html__('Update', 'wp-jobsearch') . '">
                        </li>
                    </ul>
                </div>
            </li>';

            echo json_encode(array('msg' => esc_html__('Added Successfully.', 'wp-jobsearch'), 'html' => $html));
            die;
        }

        echo json_encode(array('msg' => esc_html__('Please fill necessary fields.', 'wp-jobsearch'), 'error' => '1'));
        die;
    }

    public function add_resume_award_to_list() {
        $rand_num = rand(1000000, 99999999);
        $title = isset($_POST['title']) ? ($_POST['title']) : '';
        $award_year = isset($_POST['award_year']) ? ($_POST['award_year']) : '';
        $award_desc = isset($_POST['award_desc']) ? ($_POST['award_desc']) : '';

        if ($title != '' && $award_year != '') {
            $html = '
            <li class="jobsearch-column-12 resume-list-item resume-list-award">
                <div class="jobsearch-add-skills-wrap">
                    <small>' . $award_year . '</small>
                    <h2><a>' . $title . '</a></h2>
                </div>
                <div class="jobsearch-resume-education-btn">
                    <a href="javascript:void(0);" class="jobsearch-icon jobsearch-edit update-resume-item"></a>
                    <a href="javascript:void(0);" class="jobsearch-icon jobsearch-rubbish del-resume-item"></a>
                </div>
                <div class="jobsearch-add-popup jobsearch-update-resume-items-sec">
                    <ul class="jobsearch-row jobsearch-employer-profile-form">
                        <li class="jobsearch-column-6">
                            <label>' . esc_html__('Title *', 'wp-jobsearch') . '</label>
                            <input name="jobsearch_field_award_title[]" type="text" value="' . $title . '">
                        </li>
                        <li class="jobsearch-column-6">
                            <label>' . esc_html__('Year *', 'wp-jobsearch') . '</label>
                            <input name="jobsearch_field_award_year[]" type="text" value="' . $award_year . '">
                        </li>
                        <li class="jobsearch-column-12">
                            <label>' . esc_html__('Description', 'wp-jobsearch') . '</label>
                            <textarea name="jobsearch_field_award_description[]">' . $award_desc . '</textarea>
                        </li>
                        <li class="jobsearch-column-12">
                            <input class="update-resume-list-btn" type="submit" value="' . esc_html__('Update', 'wp-jobsearch') . '">
                        </li>
                    </ul>
                </div>
            </li>';

            echo json_encode(array('msg' => esc_html__('Added Successfully.', 'wp-jobsearch'), 'html' => $html));
            die;
        }

        echo json_encode(array('msg' => esc_html__('Please fill necessary fields.', 'wp-jobsearch'), 'error' => '1'));
        die;
    }

    public function add_resume_portfolio_to_list() {
        $rand_num = rand(1000000, 99999999);
        $title = isset($_POST['title']) ? ($_POST['title']) : '';
        $portfolio_img = isset($_POST['portfolio_img']) ? ($_POST['portfolio_img']) : '';
        $portfolio_url = isset($_POST['portfolio_url']) ? ($_POST['portfolio_url']) : '';
        $portfolio_vurl = isset($_POST['portfolio_vurl']) ? ($_POST['portfolio_vurl']) : '';

        if ($title != '' && $portfolio_img != '') {
            $html = '
            <li class="jobsearch-column-3 resume-list-item resume-list-port">
                <figure>
                    <a class="portfolio-img-holder"><span style="background-image: url(\'' . $portfolio_img . '\');"></span></a>
                    <figcaption>
                        <span>' . $title . '</span>
                        <div class="jobsearch-company-links">
                            <a href="javascript:void(0);" class="jobsearch-icon jobsearch-edit update-resume-item"></a>
                            <a href="javascript:void(0);" class="jobsearch-icon jobsearch-rubbish del-resume-item"></a>
                        </div>
                    </figcaption>
                </figure>
                <div class="jobsearch-add-popup jobsearch-update-resume-items-sec">
                    <ul class="jobsearch-row jobsearch-employer-profile-form">
                        <li class="jobsearch-column-12">
                            <label>' . esc_html__('Title *', 'wp-jobsearch') . '</label>
                            <input name="jobsearch_field_portfolio_title[]" type="text" value="' . $title . '">
                        </li>
                        <li class="jobsearch-column-6">
                            <label>' . esc_html__('Image *', 'wp-jobsearch') . '</label>
                            <div class="upload-img-holder-sec">
                                <span class="file-loader"></span>
                                <img src="' . $portfolio_img . '" alt="">
                                <input name="add_portfolio_img" type="file" style="display: none;">
                                <input type="hidden" class="img-upload-save-field" name="jobsearch_field_portfolio_image[]" value="' . $portfolio_img . '">
                                <a href="javascript:void(0)" class="upload-port-img-btn"><i class="jobsearch-icon jobsearch-add"></i> ' . esc_html__('Upload Photo', 'wp-jobsearch') . '</a>
                            </div>
                        </li>
                        <li class="jobsearch-column-12">
                            <label>' . esc_html__('Video URL', 'wp-jobsearch') . '</label>
                            <input name="jobsearch_field_portfolio_vurl[]" type="text" value="' . $portfolio_vurl . '">
                            <em>' . esc_html__('Add video url of youtube, vimeo.', 'wp-jobsearch') . '</em>
                        </li>
                        <li class="jobsearch-column-12">
                            <label>' . esc_html__('URL', 'wp-jobsearch') . '</label>
                            <input name="jobsearch_field_portfolio_url[]" type="text" value="' . $portfolio_url . '">
                        </li>
                        <li class="jobsearch-column-12">
                            <input class="update-resume-list-btn" type="submit" value="' . esc_html__('Update', 'wp-jobsearch') . '">
                        </li>
                    </ul>
                </div>
            </li>';

            echo json_encode(array('msg' => esc_html__('Added Successfully.', 'wp-jobsearch'), 'html' => $html));
            die;
        }

        echo json_encode(array('msg' => esc_html__('Please fill necessary fields.', 'wp-jobsearch'), 'error' => '1'));
        die;
    }

    public function add_team_member_to_list() {
        $rand_num = rand(1000000, 99999999);
        $title = isset($_POST['title']) ? ($_POST['title']) : '';
        $team_designation = isset($_POST['team_designation']) ? ($_POST['team_designation']) : '';
        $team_experience = isset($_POST['team_experience']) ? ($_POST['team_experience']) : '';
        $team_image = isset($_POST['team_image']) ? ($_POST['team_image']) : '';
        $team_facebook = isset($_POST['team_facebook']) ? ($_POST['team_facebook']) : '';
        $team_google = isset($_POST['team_google']) ? ($_POST['team_google']) : '';
        $team_twitter = isset($_POST['team_twitter']) ? ($_POST['team_twitter']) : '';
        $team_linkedin = isset($_POST['team_linkedin']) ? ($_POST['team_linkedin']) : '';
        $team_description = isset($_POST['team_description']) ? ($_POST['team_description']) : '';

        if ($title != '' && $team_designation != '' && $team_experience != '' && $team_image != '') {
            $html = '
            <li class="jobsearch-column-3">
                <figure>
                    <a class="portfolio-img-holder"><span style="background-image: url(\'' . $team_image . '\');"></span></a>
                    <figcaption>
                        <span>' . $title . '</span>
                        <div class="jobsearch-company-links">
                            <a href="javascript:void(0);" class="jobsearch-icon jobsearch-edit update-resume-item"></a>
                            <a href="javascript:void(0);" class="jobsearch-icon jobsearch-rubbish del-resume-item"></a>
                        </div>
                    </figcaption>
                </figure>
                <div class="jobsearch-add-popup jobsearch-update-resume-items-sec">
                    <ul class="jobsearch-row jobsearch-employer-profile-form">
                        <li class="jobsearch-column-6">
                            <label>' . esc_html__('Member Title *', 'wp-jobsearch') . '</label>
                            <input name="jobsearch_field_team_title[]" type="text" value="' . $title . '">
                        </li>
                        <li class="jobsearch-column-6">
                            <label>' . esc_html__('Designation *', 'wp-jobsearch') . '</label>
                            <input name="jobsearch_field_team_designation[]" type="text" value="' . $team_designation . '">
                        </li>
                        <li class="jobsearch-column-6">
                            <label>' . esc_html__('Experience *', 'wp-jobsearch') . '</label>
                            <input name="jobsearch_field_team_experience[]" type="text" value="' . $team_experience . '">
                        </li>
                        <li class="jobsearch-column-6">
                            <label>' . esc_html__('Image *', 'wp-jobsearch') . '</label>
                            <div class="upload-img-holder-sec">
                                <span class="file-loader"></span>
                                <img src="' . $team_image . '" alt="">
                                <input name="team_image" type="file" style="display: none;">
                                <input type="hidden" class="img-upload-save-field" name="jobsearch_field_team_image[]" value="' . $team_image . '">
                                <a href="javascript:void(0)" class="upload-port-img-btn"><i class="jobsearch-icon jobsearch-add"></i> ' . esc_html__('Upload Photo', 'wp-jobsearch') . '</a>
                            </div>
                        </li>
                        <li class="jobsearch-column-6">
                            <label>' . esc_html__('Facebook URL', 'wp-jobsearch') . '</label>
                            <input name="jobsearch_field_team_facebook[]" type="text" value="' . ($team_facebook) . '">
                        </li>
                        <li class="jobsearch-column-6">
                            <label>' . esc_html__('Google+ URL', 'wp-jobsearch') . '</label>
                            <input name="jobsearch_field_team_google[]" type="text" value="' . ($team_google) . '">
                        </li>
                        <li class="jobsearch-column-6">
                            <label>' . esc_html__('TwitterURL', 'wp-jobsearch') . '</label>
                            <input name="jobsearch_field_team_twitter[]" type="text" value="' . ($team_twitter) . '">
                        </li>
                        <li class="jobsearch-column-6">
                            <label>' . esc_html__('Linkedin URL', 'wp-jobsearch') . '</label>
                            <input name="jobsearch_field_team_linkedin[]" type="text" value="' . ($team_linkedin) . '">
                        </li>
                        <li class="jobsearch-column-12">
                            <label>' . esc_html__('Description', 'wp-jobsearch') . '</label>
                            <textarea name="jobsearch_field_team_description[]">' . ($team_description) . '</textarea>
                        </li>
                        <li class="jobsearch-column-12">
                            <input class="update-resume-list-btn" type="submit" value="' . esc_html__('Update', 'wp-jobsearch') . '">
                        </li>
                    </ul>
                </div>
            </li>';

            echo json_encode(array('msg' => esc_html__('Added Successfully.', 'wp-jobsearch'), 'html' => $html));
            die;
        }

        echo json_encode(array('msg' => esc_html__('Please fill necessary fields.', 'wp-jobsearch'), 'error' => '1'));
        die;
    }

    public function dashboard_adding_portfolio_img_url() {

        $atach_id = jobsearch_insert_upload_attach('add_portfolio_img');

        if ($atach_id > 0) {
            $thumb_image = wp_get_attachment_image_src($atach_id, 'full');
            $img_url = isset($thumb_image[0]) && esc_url($thumb_image[0]) != '' ? $thumb_image[0] : '';

            echo json_encode(array('img_url' => $img_url));
        }
        wp_die();
    }

    public function dashboard_adding_team_img_url() {

        $atach_id = jobsearch_insert_upload_attach('team_image');

        if ($atach_id > 0) {
            $thumb_image = wp_get_attachment_image_src($atach_id, 'thumbnail');
            $img_url = isset($thumb_image[0]) && esc_url($thumb_image[0]) != '' ? $thumb_image[0] : '';

            echo json_encode(array('img_url' => $img_url));
        }
        wp_die();
    }

    public function candidate_contact_form_submit() {
        global $jobsearch_plugin_options;

        $cur_user_id = get_current_user_id();

        $uname = isset($_POST['u_name']) ? $_POST['u_name'] : '';
        $uemail = isset($_POST['u_email']) ? $_POST['u_email'] : '';
        $uphone = isset($_POST['u_phone']) ? $_POST['u_phone'] : '';
        $umsg = isset($_POST['u_msg']) ? $_POST['u_msg'] : '';
        $user_id = isset($_POST['u_candidate_id']) ? $_POST['u_candidate_id'] : '';

        $user_obj = get_user_by('ID', $user_id);

        $cnt_email = $user_obj->user_email;

        $error = 0;
        $msg = '';

        $user_is_employer = jobsearch_user_is_employer($cur_user_id);
        if (!$user_is_employer) {
            $error = 1;
            $msg = esc_html__('Only an employer can contact this user.', 'wp-jobsearch');

            echo json_encode(array('msg' => $msg));
            wp_die();
        }

        jobsearch_captcha_verify();

        if ($umsg != '' && $error == 0) {
            $umsg = esc_html($umsg);
        } else {
            $error = 1;
            $msg = esc_html__('Please type your Message.', 'wp-jobsearch');
            echo json_encode(array('msg' => $msg));
            wp_die();
        }

        if ($uemail != '' && $error == 0 && filter_var($uemail, FILTER_VALIDATE_EMAIL)) {
            $uemail = esc_html($uemail);
        } else {
            $error = 1;
            $msg = esc_html__('Please Enter a valid email.', 'wp-jobsearch');
            echo json_encode(array('msg' => $msg));
            wp_die();
        }
        if ($uname != '' && $error == 0) {
            $uname = esc_html($uname);
        } else {
            $error = 1;
            $msg = esc_html__('Please Enter your Name.', 'wp-jobsearch');
            echo json_encode(array('msg' => $msg));
            wp_die();
        }

        if ($msg == '' && $error == 0) {

            $subject = sprintf(__('%s - Contact Form Message', 'wp-jobsearch'), get_bloginfo('name'));

            $headers = "From: " . ($uemail) . "\r\n";
            $headers .= "Reply-To: " . ($uemail) . "\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

            $email_message = sprintf(esc_html__('Name : %s', 'wp-jobsearch'), $uname) . "<br>";
            $email_message .= sprintf(esc_html__('Email : %s', 'wp-jobsearch'), $uemail) . "<br>";
            $email_message .= sprintf(esc_html__('Phone Number : %s', 'wp-jobsearch'), $uphone) . "<br>";
            $email_message .= sprintf(esc_html__('Message : %s', 'wp-jobsearch'), $umsg) . "<br>";

            do_action('jobsearch_candidate_contact_form', $user_obj, $uname, $uemail, $uphone, $umsg);
            $msg = esc_html__('Mail sent successfully', 'wp-jobsearch');
        }

        echo json_encode(array('msg' => $msg));
        wp_die();
    }

    public function employer_contact_form_submit() {
        global $jobsearch_plugin_options;

        $cur_user_id = get_current_user_id();

        $uname = isset($_POST['u_name']) ? $_POST['u_name'] : '';
        $uemail = isset($_POST['u_email']) ? $_POST['u_email'] : '';
        $uphone = isset($_POST['u_phone']) ? $_POST['u_phone'] : '';
        $umsg = isset($_POST['u_msg']) ? $_POST['u_msg'] : '';
        $user_id = isset($_POST['u_employer_id']) ? $_POST['u_employer_id'] : '';

        $user_obj = get_user_by('ID', $user_id);

        $cnt_email = $user_obj->user_email;

        $error = 0;
        $msg = '';

        $user_is_candidate = jobsearch_user_is_candidate($cur_user_id);
        if (!$user_is_candidate) {
            $error = 1;
            $msg = esc_html__('Only a candidate can contact this user.', 'wp-jobsearch');
            echo json_encode(array('msg' => $msg));
            wp_die();
        } else {
            $user_candidate_id = jobsearch_get_user_candidate_id($cur_user_id);
            if ($user_candidate_id > 0) {
                $candidate_status = get_post_meta($user_candidate_id, 'jobsearch_field_candidate_approved', true);
                if ($candidate_status != 'on') {
                    $error = 1;
                    $msg = esc_html__('Your profile is not approved yet.', 'wp-jobsearch');
                    echo json_encode(array('msg' => $msg));
                    wp_die();
                }
            }
        }

        jobsearch_captcha_verify();

        if ($umsg != '' && $error == 0) {
            $umsg = esc_html($umsg);
        } else {
            $error = 1;
            $msg = esc_html__('Please type your Message.', 'wp-jobsearch');
            echo json_encode(array('msg' => $msg));
            wp_die();
        }

        if ($uemail != '' && $error == 0 && filter_var($uemail, FILTER_VALIDATE_EMAIL)) {
            $uemail = esc_html($uemail);
        } else {
            $error = 1;
            $msg = esc_html__('Please Enter a valid email.', 'wp-jobsearch');
            echo json_encode(array('msg' => $msg));
            wp_die();
        }
        if ($uname != '' && $error == 0) {
            $uname = esc_html($uname);
        } else {
            $error = 1;
            $msg = esc_html__('Please Enter your Name.', 'wp-jobsearch');
            echo json_encode(array('msg' => $msg));
            wp_die();
        }

        if ($msg == '' && $error == 0) {

            $subject = sprintf(__('%s - Contact Form Message', 'wp-jobsearch'), get_bloginfo('name'));

            $headers = "From: " . ($uemail) . "\r\n";
            $headers .= "Reply-To: " . ($uemail) . "\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

            $email_message = sprintf(esc_html__('Name : %s', 'wp-jobsearch'), $uname) . "<br>";
            $email_message .= sprintf(esc_html__('Email : %s', 'wp-jobsearch'), $uemail) . "<br>";
            $email_message .= sprintf(esc_html__('Phone Number : %s', 'wp-jobsearch'), $uphone) . "<br>";
            $email_message .= sprintf(esc_html__('Message : %s', 'wp-jobsearch'), $umsg) . "<br>";

            do_action('jobsearch_employer_contact_form', $user_obj, $uname, $uemail, $uphone, $umsg);
            $msg = esc_html__('Mail sent successfully', 'wp-jobsearch');
        }

        echo json_encode(array('msg' => $msg));
        wp_die();
    }

    public function pagination($total_pages = 1, $page = 1, $url = '', $return = false) {

        $query_string = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';

        if ($url != '') {
            $base = $url . '?' . remove_query_arg('page_num', $query_string) . '%_%';
        } else {
            $base = get_permalink() . '?' . remove_query_arg('page_num', $query_string) . '%_%';
        }

        $pagination = paginate_links(array(
            'base' => $base, // the base URL, including query arg
            'format' => '&page_num=%#%',
            'total' => $total_pages, // the total number of pages we have
            'current' => $page, // the current page
            'end_size' => 1,
            'mid_size' => 2,
            'type' => 'array',
            'prev_text' => '<span><i class="jobsearch-icon jobsearch-arrows4"></i></span>',
            'next_text' => '<span><i class="jobsearch-icon jobsearch-arrows4"></i></span>',
        ));
        $html = '';
        if (is_array($pagination) && sizeof($pagination) > 0) {
            $html .= '<ul class="page-numbers">';
            foreach ($pagination as $link) {
                if (strpos($link, 'current') !== false) {
                    $html .= '<li><span class="page-numbers current">' . preg_replace("/[^0-9]/", "", $link) . '</span></li>';
                } else {
                    $html .= '<li>' . $link . '</li>';
                }
            }
            $html .= '</ul>';
        }

        if ($return == true) {
            return $html;
        } else {
            echo force_balance_tags($html);
        }
    }

    public function doing_mjobs_feature_job() {
        $response = array();

        $pkg_id = isset($_POST['pkg_id']) ? $_POST['pkg_id'] : '';
        $job_id = isset($_POST['job_id']) ? $_POST['job_id'] : '';
        if ($job_id > 0 && $pkg_id) {

            $package_id = $pkg_id;
            $pkg_charges_type = get_post_meta($package_id, 'jobsearch_field_charges_type', true);
            $pkg_attach_product = get_post_meta($package_id, 'jobsearch_package_product', true);
            if (!class_exists('WooCommerce')) {
                $response['error'] = '1';
                $response['msg'] = esc_html__('WooCommerce Plugin not exist.', 'wp-jobsearch');
                echo json_encode($response);
                wp_die();
            }
            if ($pkg_charges_type == 'paid') {
                $package_product_obj = $pkg_attach_product != '' ? get_page_by_path($pkg_attach_product, 'OBJECT', 'product') : '';

                if ($pkg_attach_product != '' && is_object($package_product_obj)) {
                    $product_id = $package_product_obj->ID;
                } else {
                    $response['error'] = '1';
                    $response['msg'] = esc_html__('Selected Package Product not found.', 'wp-jobsearch');
                    echo json_encode($response);
                    wp_die();
                }
                // add to cart and checkout
                ob_start();
                do_action('jobsearch_woocommerce_payment_checkout', $package_id, 'checkout_url', $job_id);
                $checkout_url = ob_get_clean();
            } else {
                // creating order and adding product to order
                do_action('jobsearch_create_new_job_packg_order', $package_id, $job_id);
                $response['error'] = '0';
                $reloding_script = '<script>window.location.reload(true);</script>';
                $response['msg'] = esc_html__('reloading...', 'wp-jobsearch') . $reloding_script;
                echo json_encode($response);
                wp_die();
            }

            $reloding_script = '<script>window.location.href = \'' . $checkout_url . '\';</script>';
            $response['error'] = '0';
            $response['msg'] = esc_html__('redirecting...', 'wp-jobsearch')  . $reloding_script;
        } else {
            $response['error'] = '1';
            $response['msg'] = esc_html__('Please select a package first.', 'wp-jobsearch');
        }
        echo json_encode($response);
        wp_die();
    }

}

global $Jobsearch_User_Dashboard_Settings;
$Jobsearch_User_Dashboard_Settings = new Jobsearch_User_Dashboard_Settings();
