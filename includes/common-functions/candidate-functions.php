<?php
if (!function_exists('jobsearch_candidate_get_profile_image')) {

    function jobsearch_candidate_get_profile_image($candidate_id) {
        $post_thumbnail_id = '';
        if (isset($candidate_id) && $candidate_id != '' && has_post_thumbnail($candidate_id)) {
            $post_thumbnail_id = get_post_thumbnail_id($candidate_id);
        }
        return $post_thumbnail_id;
    }

}

if (!function_exists('jobsearch_candidate_get_company_name')) {

    function jobsearch_candidate_get_company_name($candidate_id, $before_title = '', $after_title = '') {
        $company_name_str = '';
        $candidate_field_user = get_post_meta($candidate_id, 'jobsearch_field_candidate_posted_by', true);
        if (isset($candidate_field_user) && $candidate_field_user != '') {
            $company_name_str = '<a href="' . get_permalink($candidate_field_user) . '">' . $before_title . get_the_title($candidate_field_user) . $after_title . '</a>';
        }
        return $company_name_str;
    }

}

function jobsearch_get_candidate_salary_format($candidate_id = 0, $price = 0, $cur_tag = '') {

    global $jobsearch_currencies_list, $jobsearch_plugin_options;
    $job_custom_currency_switch = isset($jobsearch_plugin_options['job_custom_currency']) ? $jobsearch_plugin_options['job_custom_currency'] : '';
    $candidate_currency = get_post_meta($candidate_id, 'jobsearch_field_candidate_salary_currency', true);
    if ($candidate_currency != 'default' && $job_custom_currency_switch == 'on') {
        $candidate_currency = isset($jobsearch_currencies_list[$candidate_currency]['symbol']) ? $jobsearch_currencies_list[$candidate_currency]['symbol'] : jobsearch_get_currency_symbol();
    } else {
        $candidate_currency = 'default';
    }
    $cur_pos = get_post_meta($candidate_id, 'jobsearch_field_candidate_salary_pos', true);
    $candidate_salary_sep = get_post_meta($candidate_id, 'jobsearch_field_candidate_salary_sep', true);
    $candidate_salary_deci = get_post_meta($candidate_id, 'jobsearch_field_candidate_salary_deci', true);

    $candidate_salary_deci = $candidate_salary_deci < 10 ? absint($candidate_salary_deci) : 2;

    if ($candidate_currency == 'default') {
        $ret_price = jobsearch_get_price_format($price);
    } else {
        $price = $price > 0 ? trim($price) : 0;
        $price = preg_replace("/[^0-9,.]+/iu", "", $price);
        if ($cur_pos == 'left_space') {
            $ret_price = ($cur_tag != '' ? '<' . $cur_tag . '>' : '') . $candidate_currency . ' ' . ($cur_tag != '' ? '</' . $cur_tag . '>' : '') . number_format($price, $candidate_salary_deci, ".", $candidate_salary_sep);
        } else if ($cur_pos == 'right') {
            $ret_price = number_format($price, $candidate_salary_deci, ".", $candidate_salary_sep) . ($cur_tag != '' ? '<' . $cur_tag . '>' : '') . $candidate_currency . ($cur_tag != '' ? '</' . $cur_tag . '>' : '');
        } else if ($cur_pos == 'right_space') {
            $ret_price = number_format($price, $candidate_salary_deci, ".", $candidate_salary_sep) . ($cur_tag != '' ? '<' . $cur_tag . '>' : '') . ' ' . $candidate_currency . ($cur_tag != '' ? '</' . $cur_tag . '>' : '');
        } else {
            $ret_price = ($cur_tag != '' ? '<' . $cur_tag . '>' : '') . $candidate_currency . ($cur_tag != '' ? '</' . $cur_tag . '>' : '') . number_format($price, $candidate_salary_deci, ".", $candidate_salary_sep);
        }
    }
    return $ret_price;
}

if (!function_exists('jobsearch_candidate_current_salary')) {

    function jobsearch_candidate_current_salary($id, $before_str = '', $after_str = '', $cur_tag = '') {
        global $jobsearch_plugin_options, $sitepress;

        $lang_code = '';
        if (function_exists('icl_object_id')) {
            $lang_code = $sitepress->get_current_language();
        }

        $job_salary_types = isset($jobsearch_plugin_options['job-salary-types']) ? $jobsearch_plugin_options['job-salary-types'] : '';

        $salary_str = $before_str;
        $_job_salary_type = get_post_meta($id, 'jobsearch_field_candidate_salary_type', true);
        $_candidate_salary = get_post_meta($id, 'jobsearch_field_candidate_salary', true);

        $salary_type_val_str = '';
        if (!empty($job_salary_types)) {
            $slar_type_count = 1;
            foreach ($job_salary_types as $job_salary_typ) {
                $job_salary_typ = apply_filters('wpml_translate_single_string', $job_salary_typ, 'JobSearch Options', 'Salary Type - ' . $job_salary_typ, $lang_code);
                if ($_job_salary_type == 'type_' . $slar_type_count) {
                    $salary_type_val_str = $job_salary_typ;
                }
                $slar_type_count++;
            }
        }

        if ($_candidate_salary != '') {
            $salary_str .= jobsearch_get_candidate_salary_format($id, $_candidate_salary, $cur_tag) . ($salary_type_val_str != '' ? ' / ' . $salary_type_val_str : '');
        }
        $salary_str .= $after_str;
        return $salary_str;
    }

}

if (!function_exists('jobsearch_candidate_age')) {

    function jobsearch_candidate_age($id) {
        global $jobsearch_plugin_options;

        $dob_dd = get_post_meta($id, 'jobsearch_field_user_dob_dd', true);
        $dob_mm = get_post_meta($id, 'jobsearch_field_user_dob_mm', true);
        $dob_yy = get_post_meta($id, 'jobsearch_field_user_dob_yy', true);


        if ($dob_dd != '' && $dob_mm != '' && $dob_yy != '') {
            //date in mm/dd/yyyy format; or it can be in other formats as well
            $birthDate = "{$dob_mm}/{$dob_dd}/{$dob_yy}";
            //explode the date to get month, day and year
            $birthDate = explode("/", $birthDate);
            //get age from date or birthdate
            $age = (date("md", date("U", mktime(0, 0, 0, $birthDate[0], $birthDate[1], $birthDate[2]))) > date("md") ? ((date("Y") - $birthDate[2]) - 1) : (date("Y") - $birthDate[2]));
            return $age;
        }

        $dob_dd = $dob_dd <= 0 ? 1 : $dob_dd;
        $dob_mm = $dob_mm <= 0 ? 1 : $dob_mm;

        $current_year = date('Y');
        if ($dob_yy > 0 && $dob_yy < $current_year) {
            $age = ($current_year - $dob_yy);
            return $age;
        }
    }

}

if (!function_exists('jobsearch_candidate_get_all_candidatetypes')) {

    function jobsearch_candidate_get_all_candidatetypes($candidate_id, $link_class = 'jobsearch-option-btn', $before_title = '', $after_title = '', $before_tag = '', $after_tag = '') {

        $candidate_type = wp_get_post_terms($candidate_id, 'candidatetype');
        ob_start();
        $html = '';
        if (!empty($candidate_type)) {
            $link_class_str = '';
            if ($link_class != '') {
                $link_class_str = 'class="' . $link_class . '"';
            }
            echo ($before_tag);
            foreach ($candidate_type as $term) :
                $candidatetype_color = get_term_meta($term->term_id, 'jobsearch_field_candidatetype_color', true);
                $candidatetype_textcolor = get_term_meta($term->term_id, 'jobsearch_field_candidatetype_textcolor', true);
                $candidatetype_color_str = '';
                if ($candidatetype_color != '') {
                    $candidatetype_color_str = ' style="background-color: ' . esc_attr($candidatetype_color) . '; color: ' . esc_attr($candidatetype_textcolor) . ' "';
                }
                ?>
                <a <?php echo force_balance_tags($link_class_str) ?> <?php echo force_balance_tags($candidatetype_color_str); ?>>
                    <?php
                    echo ($before_title);
                    echo esc_html($term->name);
                    echo ($after_title);
                    ?>
                </a>
                <?php
            endforeach;
            echo ($after_tag);
        }
        $html .= ob_get_clean();
        return $html;
    }

}

if (!function_exists('jobsearch_candidate_not_allow_to_mod')) {

    function jobsearch_candidate_not_allow_to_mod($user_id = 0) {
        global $jobsearch_plugin_options;
        if ($user_id <= 0 && is_user_logged_in()) {
            $user_id = get_current_user_id();
        }
        $user_is_candidate = jobsearch_user_is_candidate($user_id);
        if ($user_is_candidate) {
            $demo_user_login = isset($jobsearch_plugin_options['demo_user_login']) ? $jobsearch_plugin_options['demo_user_login'] : '';
            $demo_user_mod = isset($jobsearch_plugin_options['demo_user_mod']) ? $jobsearch_plugin_options['demo_user_mod'] : '';
            $demo_candidate = isset($jobsearch_plugin_options['demo_candidate']) ? $jobsearch_plugin_options['demo_candidate'] : '';
            $_demo_user_obj = get_user_by('login', $demo_candidate);
            $_demo_user_id = isset($_demo_user_obj->ID) ? $_demo_user_obj->ID : '';
            if ($user_id == $_demo_user_id && $demo_user_login == 'on' && $demo_user_mod != 'on') {
                return true;
            }
        }
        return false;
    }

}

if (!function_exists('jobsearch_candidate_get_all_sectors')) {

    function jobsearch_candidate_get_all_sectors($candidate_id, $link_class = '', $before_title = '', $after_title = '', $before_tag = '', $after_tag = '') {

        $sectors = wp_get_post_terms($candidate_id, 'sector');
        ob_start();
        $html = '';
        if (!empty($sectors)) {
            $link_class_str = '';
            if ($link_class != '') {
                $link_class_str = 'class="' . $link_class . '"';
            }
            echo ($before_tag);
            $flag = 0;
            foreach ($sectors as $term) :
                if ($flag > 0) {
                    echo ", ";
                }
                ?>
                <a class="<?php echo force_balance_tags($link_class) ?>">
                    <?php
                    echo ($before_title);
                    echo esc_html($term->name);
                    echo ($after_title);
                    ?>
                </a>
                <?php
                $flag++;
            endforeach;
            echo ($after_tag);
        }
        $html .= ob_get_clean();
        return $html;
    }

}

if (!function_exists('jobsearch_get_candidate_item_count')) {

    function jobsearch_get_candidate_item_count($left_filter_count_switch, $args, $count_arr, $candidate_short_counter, $field_meta_key, $open_house = '') {
        if ($left_filter_count_switch == 'yes') {
            global $jobsearch_shortcode_candidates_frontend;


            // get all arguments from getting flters
            $left_filter_arr = array();
            $left_filter_arr = $jobsearch_shortcode_candidates_frontend->get_filter_arg($candidate_short_counter, $field_meta_key);
            if (!empty($count_arr)) {
                // check if count array has multiple condition
                foreach ($count_arr as $count_arr_single) {
                    $left_filter_arr[] = $count_arr_single;
                }
            }

            $post_ids = '';
            if (!empty($left_filter_arr)) {
                // apply all filters and get ids
                $post_ids = $jobsearch_shortcode_candidates_frontend->get_candidate_id_by_filter($left_filter_arr);
            }

            if (isset($_REQUEST['location']) && $_REQUEST['location'] != '' && !isset($_REQUEST['loc_polygon_path'])) {
                $post_ids = $jobsearch_shortcode_candidates_frontend->candidate_location_filter($post_ids);
                if (empty($post_ids)) {
                    $post_ids = array(0);
                }
            }

            $all_post_ids = $post_ids;
            if (!empty($all_post_ids)) {
                $args['post__in'] = $all_post_ids;
            }

            $restaurant_loop_obj = jobsearch_get_cached_obj('candidate_result_cached_loop_count_obj', $args, 12, false, 'wp_query');
            $restaurant_totnum = $restaurant_loop_obj->found_posts;
            return $restaurant_totnum;
        }
    }

}

if (!function_exists('jobsearch_candidate_skills_set_array')) {

    function jobsearch_candidate_skills_set_array() {

        $skills_array = array(
            'jobsearch_display_name' => array(
                'name' => esc_html__('Full Name', 'wp-jobsearch'),
            ),
            'jobsearch_user_img' => array(
                'name' => esc_html__('Profile Image', 'wp-jobsearch'),
            ),
            'jobsearch_job_title' => array(
                'name' => esc_html__('Job Title', 'wp-jobsearch'),
            ),
            'jobsearch_minimum_salary' => array(
                'name' => esc_html__('Salary', 'wp-jobsearch'),
            ),
            'jobsearch_sectors' => array(
                'name' => esc_html__('Sectors', 'wp-jobsearch'),
            ),
            'jobsearch_description' => array(
                'name' => esc_html__('Description', 'wp-jobsearch'),
            ),
            'jobsearch_social_network' => array(
                'name' => esc_html__('Social Network', 'wp-jobsearch'),
                'list' => array(
                    'jobsearch_facebook' => array(
                        'name' => esc_html__('Facebook', 'wp-jobsearch'),
                    ),
                    'jobsearch_twitter' => array(
                        'name' => esc_html__('Twitter', 'wp-jobsearch'),
                    ),
                    'jobsearch_google_plus' => array(
                        'name' => esc_html__('Google Plus', 'wp-jobsearch'),
                    ),
                    'jobsearch_linkedin' => array(
                        'name' => esc_html__('Linkedin', 'wp-jobsearch'),
                    ),
                ),
            ),
            'contact_info' => array(
                'name' => esc_html__('Contact Information', 'wp-jobsearch'),
                'list' => array(
                    'jobsearch_user_phone' => array(
                        'name' => esc_html__('Phone Number', 'wp-jobsearch'),
                    ),
                    'jobsearch_user_email' => array(
                        'name' => esc_html__('Email', 'wp-jobsearch'),
                    ),
                    'jobsearch_location_address' => array(
                        'name' => esc_html__('Complete Address', 'wp-jobsearch'),
                    ),
                ),
            ),
            'resume' => array(
                'name' => esc_html__('Resume', 'wp-jobsearch'),
                'list' => array(
                    'jobsearch_education_title' => array(
                        'name' => esc_html__('Education', 'wp-jobsearch'),
                    ),
                    'jobsearch_experience_title' => array(
                        'name' => esc_html__('Experience', 'wp-jobsearch'),
                    ),
                    'jobsearch_portfolio_title' => array(
                        'name' => esc_html__('Portfolio', 'wp-jobsearch'),
                    ),
                    'jobsearch_skill_title' => array(
                        'name' => esc_html__('Skills', 'wp-jobsearch'),
                    ),
                    'jobsearch_award_title' => array(
                        'name' => esc_html__('Honors & Awards', 'wp-jobsearch'),
                    ),
                ),
            ),
            'cv_cover_letter' => array(
                'name' => esc_html__('CV &amp; Cover Letter', 'wp-jobsearch'),
                'list' => array(
                    'jobsearch_candidate_cv' => array(
                        'name' => esc_html__('CV', 'wp-jobsearch'),
                    ),
                    'jobsearch_cover_letter' => array(
                        'name' => esc_html__('Cover Letter', 'wp-jobsearch'),
                    ),
                ),
            ),
        );
        $skills_array = apply_filters('jobsearch_custom_fields_load_precentage_array', 'candidate', $skills_array);
        return $skills_array;
    }

}

if (!function_exists('jobsearch_candidate_skill_percent_count')) {

    function jobsearch_candidate_skill_percent_count($user_id, $return_type = 'return') {
        global $jobsearch_plugin_options;
        $skills_perc = 0;

        $msgs_array = array();

        $is_candidate = jobsearch_user_is_candidate($user_id);
        if ($is_candidate) {
            $candidate_id = jobsearch_get_user_candidate_id($user_id);
            $skills_array = jobsearch_candidate_skills_set_array();
            foreach ($skills_array as $skill_key => $skill_val) {
                if ($skill_key == 'jobsearch_display_name') {
                    $this_opt_id = str_replace('jobsearch_', '', $skill_key) . '_skill';
                    $def_percentage = isset($jobsearch_plugin_options[$this_opt_id]) ? $jobsearch_plugin_options[$this_opt_id] : '';
                    $candidate_name_title = get_the_title($candidate_id);
                    if ($candidate_name_title != '') {
                        $skills_perc += ($def_percentage > 0 ? $def_percentage : 0);
                    } else {
                        if ($def_percentage > 0) {
                            $msgs_array[] = sprintf(__('<small>%s</small> Skills increased by Full Name.', 'wp-jobsearch'), $def_percentage . '%');
                        }
                    }
                }
                if ($skill_key == 'jobsearch_user_img') {
                    $this_opt_id = str_replace('jobsearch_', '', $skill_key) . '_skill';
                    $def_percentage = isset($jobsearch_plugin_options[$this_opt_id]) ? $jobsearch_plugin_options[$this_opt_id] : '';
                    $candidate_img_thumb_id = get_post_thumbnail_id($candidate_id);
                    if ($candidate_img_thumb_id != '') {
                        $skills_perc += ($def_percentage > 0 ? $def_percentage : 0);
                    } else {
                        if ($def_percentage > 0) {
                            $msgs_array[] = sprintf(__('<small>%s</small> Skills increased by Profile Image.', 'wp-jobsearch'), $def_percentage . '%');
                        }
                    }
                }
                if ($skill_key == 'jobsearch_job_title') {
                    $this_opt_id = str_replace('jobsearch_', '', $skill_key) . '_skill';
                    $def_percentage = isset($jobsearch_plugin_options[$this_opt_id]) ? $jobsearch_plugin_options[$this_opt_id] : '';
                    $candidate_job_title = get_post_meta($candidate_id, 'jobsearch_field_candidate_jobtitle', true);
                    if ($candidate_job_title != '') {
                        $skills_perc += ($def_percentage > 0 ? $def_percentage : 0);
                    } else {
                        if ($def_percentage > 0) {
                            $msgs_array[] = sprintf(__('<small>%s</small> Skills increased by Job Title.', 'wp-jobsearch'), $def_percentage . '%');
                        }
                    }
                }
                if ($skill_key == 'jobsearch_minimum_salary') {
                    $this_opt_id = str_replace('jobsearch_', '', $skill_key) . '_skill';
                    $def_percentage = isset($jobsearch_plugin_options[$this_opt_id]) ? $jobsearch_plugin_options[$this_opt_id] : '';
                    $candidate_salary = get_post_meta($candidate_id, 'jobsearch_field_candidate_salary', true);
                    if ($candidate_salary != '') {
                        $skills_perc += ($def_percentage > 0 ? $def_percentage : 0);
                    } else {
                        if ($def_percentage > 0) {
                            $msgs_array[] = sprintf(__('<small>%s</small> Skills increased by Salary.', 'wp-jobsearch'), $def_percentage . '%');
                        }
                    }
                }
                if ($skill_key == 'jobsearch_sectors') {
                    $this_opt_id = str_replace('jobsearch_', '', $skill_key) . '_skill';
                    $def_percentage = isset($jobsearch_plugin_options[$this_opt_id]) ? $jobsearch_plugin_options[$this_opt_id] : '';
                    $candidate_sectors = wp_get_post_terms($candidate_id, 'sector');
                    if (!empty($candidate_sectors)) {
                        $skills_perc += ($def_percentage > 0 ? $def_percentage : 0);
                    } else {
                        if ($def_percentage > 0) {
                            $msgs_array[] = sprintf(__('<small>%s</small> Skills increased by Sector.', 'wp-jobsearch'), $def_percentage . '%');
                        }
                    }
                }
                if ($skill_key == 'jobsearch_description') {
                    $this_opt_id = str_replace('jobsearch_', '', $skill_key) . '_skill';
                    $def_percentage = isset($jobsearch_plugin_options[$this_opt_id]) ? $jobsearch_plugin_options[$this_opt_id] : '';
                    $candidate_obj = get_post($candidate_id);
                    $candidate_desc = isset($candidate_obj->post_content) ? $candidate_obj->post_content : '';
                    if ($candidate_desc != '') {
                        $skills_perc += ($def_percentage > 0 ? $def_percentage : 0);
                    } else {
                        if ($def_percentage > 0) {
                            $msgs_array[] = sprintf(__('<small>%s</small> Skills increased by Description.', 'wp-jobsearch'), $def_percentage . '%');
                        }
                    }
                }
                if ($skill_key == 'jobsearch_social_network') {
                    if (isset($skill_val['list'])) {
                        foreach ($skill_val['list'] as $skill_social_key => $skill_social_val) {
                            $this_opt_id = str_replace('jobsearch_', '', $skill_social_key) . '_skill';
                            $def_percentage = isset($jobsearch_plugin_options[$this_opt_id]) ? $jobsearch_plugin_options[$this_opt_id] : '';
                            //
                            $this_meta_id = 'jobsearch_field_user_' . str_replace('jobsearch_', '', $skill_social_key) . '_url';
                            $candidate_social_val = get_post_meta($candidate_id, $this_meta_id, true);
                            if ($candidate_social_val != '') {
                                $skills_perc += ($def_percentage > 0 ? $def_percentage : 0);
                            } else {
                                if ($def_percentage > 0) {
                                    $msgs_array[] = sprintf(__('<small>%s</small> Skills increased by %s.', 'wp-jobsearch'), $def_percentage . '%', $skill_social_val['name']);
                                }
                            }
                        }
                    }
                }
                if ($skill_key == 'contact_info') {
                    if (isset($skill_val['list'])) {
                        foreach ($skill_val['list'] as $skill_contact_key => $skill_contact_val) {
                            $this_opt_id = str_replace('jobsearch_', '', $skill_contact_key) . '_skill';
                            $def_percentage = isset($jobsearch_plugin_options[$this_opt_id]) ? $jobsearch_plugin_options[$this_opt_id] : '';
                            //
                            if ($skill_contact_key != 'jobsearch_user_email' && $skill_contact_key != 'jobsearch_user_url') {
                                $this_meta_id = str_replace('jobsearch_', 'jobsearch_field_', $skill_contact_key);
                                $candidate_contact_val = get_post_meta($candidate_id, $this_meta_id, true);
                                if ($candidate_contact_val != '') {
                                    $skills_perc += ($def_percentage > 0 ? $def_percentage : 0);
                                } else {
                                    if ($def_percentage > 0) {
                                        $msgs_array[] = sprintf(__('<small>%s</small> Skills increased by %s.', 'wp-jobsearch'), $def_percentage . '%', $skill_contact_val['name']);
                                    }
                                }
                            } else {
                                $user_obj = get_user_by('ID', $user_id);
                                if ($skill_contact_key == 'jobsearch_user_email' && isset($user_obj->user_email) && $user_obj->user_email != '') {
                                    $skills_perc += ($def_percentage > 0 ? $def_percentage : 0);
                                } else {
                                    if ($def_percentage > 0) {
                                        $msgs_array[] = sprintf(__('<small>%s</small> Skills increased by %s.', 'wp-jobsearch'), $def_percentage . '%', $skill_contact_val['name']);
                                    }
                                }
                            }
                        }
                    }
                }
                if ($skill_key == 'resume') {
                    if (isset($skill_val['list'])) {
                        foreach ($skill_val['list'] as $skill_resume_key => $skill_resume_val) {
                            $this_opt_id = str_replace('jobsearch_', '', $skill_resume_key) . '_skill';
                            $def_percentage = isset($jobsearch_plugin_options[$this_opt_id]) ? $jobsearch_plugin_options[$this_opt_id] : '';
                            //
                            $this_meta_id = str_replace('jobsearch_', 'jobsearch_field_', $skill_resume_key);
                            $candidate_resume_val = get_post_meta($candidate_id, $this_meta_id, true);
                            if (!empty($candidate_resume_val)) {
                                $skills_perc += ($def_percentage > 0 ? $def_percentage : 0);
                            } else {
                                if ($def_percentage > 0) {
                                    $msgs_array[] = sprintf(__('<small>%s</small> Skills increased by %s.', 'wp-jobsearch'), $def_percentage . '%', $skill_resume_val['name']);
                                }
                            }
                        }
                    }
                }
                if ($skill_key == 'cv_cover_letter') {
                    if (isset($skill_val['list'])) {
                        foreach ($skill_val['list'] as $skill_cv_key => $skill_cv_val) {
                            $this_opt_id = str_replace('jobsearch_', '', $skill_cv_key) . '_skill';
                            $def_percentage = isset($jobsearch_plugin_options[$this_opt_id]) ? $jobsearch_plugin_options[$this_opt_id] : '';
                            //
                            if ($skill_cv_key == 'jobsearch_candidate_cv') {
                                $candidate_cv_file = get_post_meta($candidate_id, 'candidate_cv_file', true);
                                $multiple_cv_files_allow = isset($jobsearch_plugin_options['multiple_cv_uploads']) ? $jobsearch_plugin_options['multiple_cv_uploads'] : '';
                                $ca_at_cv_files = get_post_meta($candidate_id, 'candidate_cv_files', true);
                                if ($multiple_cv_files_allow == 'on' && !empty($ca_at_cv_files)) {
                                    $skills_perc += ($def_percentage > 0 ? $def_percentage : 0);
                                } else if (!empty($candidate_cv_file)) {
                                    $skills_perc += ($def_percentage > 0 ? $def_percentage : 0);
                                } else {
                                    if ($def_percentage > 0) {
                                        $msgs_array[] = sprintf(__('<small>%s</small> Skills increased by CV.', 'wp-jobsearch'), $def_percentage . '%');
                                    }
                                }
                            }
                            if ($skill_cv_key == 'jobsearch_cover_letter') {
                                $candidate_cover = get_post_meta($candidate_id, 'jobsearch_field_resume_cover_letter', true);
                                if (!empty($candidate_cover)) {
                                    $skills_perc += ($def_percentage > 0 ? $def_percentage : 0);
                                } else {
                                    if ($def_percentage > 0) {
                                        $msgs_array[] = sprintf(__('<small>%s</small> Skills increased by Cover Letter.', 'wp-jobsearch'), $def_percentage . '%');
                                    }
                                }
                            }
                        }
                    }
                }
                if ($skill_key == 'custom_fields') {
                    $field_db_slug = "jobsearch_custom_field_candidate";
                    $jobsearch_post_cus_fields = get_option($field_db_slug);
                    if (is_array($jobsearch_post_cus_fields) && sizeof($jobsearch_post_cus_fields) > 0) {
                        foreach ($jobsearch_post_cus_fields as $custom_field) {
                            $custom_meta_key = isset($custom_field['name']) ? $custom_field['name'] : '';
                            $custom_field_name = isset($custom_field['label']) ? $custom_field['label'] : '';

                            if ($custom_meta_key != '') {
                                $this_opt_id = str_replace('jobsearch_', '', $custom_meta_key) . '_skill';
                                $def_percentage = isset($jobsearch_plugin_options[$this_opt_id]) ? $jobsearch_plugin_options[$this_opt_id] : '';
                                //
                                $custom_f_val = get_post_meta($candidate_id, $custom_meta_key, true);
                                if (!empty($custom_f_val)) {
                                    $skills_perc += ($def_percentage > 0 ? $def_percentage : 0);
                                } else {
                                    if ($def_percentage > 0) {
                                        $msgs_array[] = sprintf(__('<small>%s</small> Skills increased by %s.', 'wp-jobsearch'), $def_percentage . '%', $custom_field_name);
                                    }
                                }
                            }
                        }
                    }
                }
                //
            }
            update_post_meta($candidate_id, 'overall_skills_percentage', $skills_perc);
        }

        if ($skills_perc > 100) {
            $skills_perc = 100;
        }

        if ($return_type == 'return') {
            return $skills_perc;
        }
        if ($return_type == 'msgs') {
            return $msgs_array;
        }
    }

}

if (!function_exists('jobsearch_candidate_skills_set_plugin_option_array')) {

    add_filter('redux/options/jobsearch_plugin_options/sections', 'jobsearch_candidate_skills_set_plugin_option_array', 1);

    function jobsearch_candidate_skills_set_plugin_option_array($sections) {
        $skills_array = jobsearch_candidate_skills_set_array();
        $jobsearch_setting_options = array();
        $jobsearch_setting_options[] = array(
            'id' => 'jobsearch_candidate_skills',
            'type' => 'button_set',
            'title' => __('Candidate Skills', 'wp-jobsearch'),
            'subtitle' => '',
            'options' => array(
                'on' => __('On', 'wp-jobsearch'),
                'off' => __('Off', 'wp-jobsearch'),
            ),
            'desc' => '',
            'default' => 'off',
        );
        $jobsearch_setting_options[] = array(
            'id' => 'jobsearch-candidate-skills-percentage',
            'type' => 'text',
            'title' => __('Minimum Skills Percentage', 'wp-jobsearch'),
            'subtitle' => __("Set Candidate Skills Percentage such as 50. If Candidate's Skills Percentage less than this Percentage then He/She will not able to apply any Job.", 'wp-jobsearch'),
            'desc' => '',
            'default' => '50',
        );
        $jobsearch_setting_options[] = array(
            'id' => 'skill_low_set_color',
            'type' => 'color',
            'transparent' => false,
            'title' => __('Low Profile Color', 'wp-jobsearch'),
            'subtitle' => '',
            'desc' => __("Set color for Low Profile. Skills percentage from 0 to 25%.", 'wp-jobsearch'),
            'default' => '#ff5b5b',
        );
        $jobsearch_setting_options[] = array(
            'id' => 'skill_med_set_color',
            'type' => 'color',
            'transparent' => false,
            'title' => __('Basic Profile Color', 'wp-jobsearch'),
            'subtitle' => '',
            'desc' => __("Set color for Basic Profile. Skills percentage from 26% to 50%.", 'wp-jobsearch'),
            'default' => '#ffbb00',
        );
        $jobsearch_setting_options[] = array(
            'id' => 'skill_high_set_color',
            'type' => 'color',
            'transparent' => false,
            'title' => __('Professional Profile Color', 'wp-jobsearch'),
            'subtitle' => '',
            'desc' => __("Set color for Professional Profile. Skills percentage from 51% to 75%.", 'wp-jobsearch'),
            'default' => '#13b5ea',
        );
        $jobsearch_setting_options[] = array(
            'id' => 'skill_ahigh_set_color',
            'type' => 'color',
            'transparent' => false,
            'title' => __('Complete Profile Color', 'wp-jobsearch'),
            'subtitle' => '',
            'desc' => __("Set color for Complete Profile. Skills percentage from 76% to 100%.", 'wp-jobsearch'),
            'default' => '#40d184',
        );
        if (is_array($skills_array) && sizeof($skills_array) > 0) {

            foreach ($skills_array as $skills_array_key => $skills_array_set) {

                if (array_key_exists('list', $skills_array_set) && is_array($skills_array_set['list'])) {

                    $skill_sec_name = isset($skills_array_set['name']) ? $skills_array_set['name'] : '';
                    if ($skill_sec_name != '' && $skills_array_key != '') {
                        $jobsearch_setting_options[] = array(
                            'id' => "tab-settings-$skills_array_key-skill",
                            'type' => 'section',
                            'title' => $skill_sec_name,
                            'subtitle' => '',
                            'indent' => true,
                        );
                    }
                    foreach ($skills_array_set['list'] as $skill_list_key => $skill_list_set) {
                        $skill_name = isset($skill_list_set['name']) ? $skill_list_set['name'] : '';
                        if ($skill_list_key != '' && $skill_name != '') {

                            $this_opt_id = str_replace('jobsearch_', '', $skill_list_key) . '_skill';

                            $jobsearch_setting_options[] = array(
                                'id' => $this_opt_id,
                                'type' => 'text',
                                'title' => $skill_name,
                                'desc' => '',
                                'default' => '',
                            );
                        }
                    }
                } else {
                    $skill_name = isset($skills_array_set['name']) ? $skills_array_set['name'] : '';
                    if ($skills_array_key != '' && $skill_name != '') {
                        $this_opt_id = str_replace('jobsearch_', '', $skills_array_key) . '_skill';
                        $jobsearch_setting_options[] = array(
                            'id' => $this_opt_id,
                            'type' => 'text',
                            'title' => $skill_name,
                            'desc' => '',
                            'default' => '',
                        );
                    }
                }
            }
        }


        $sections[] = array(
            'title' => __('Required Skill', 'wp-jobsearch'),
            'id' => 'required-skill-set',
            'desc' => '',
            'icon' => 'el el-tasks',
            'fields' => $jobsearch_setting_options,
        );
        return $sections;
    }

}

function jobsearch_upload_candidate_cv($Fieldname = 'file', $post_id = 0, $user_dir_filter = true) {

    if (isset($_FILES[$Fieldname]) && $_FILES[$Fieldname] != '') {

        if ($user_dir_filter === true) {
            add_filter('upload_dir', 'jobsearch_user_upload_files_path');
        }

        // Get the path to the upload directory.
        $wp_upload_dir = wp_upload_dir();

        $upload_file = $_FILES[$Fieldname];

        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';

        $allowed_file_types = array(
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'pdf' => 'application/pdf',
        );

        //
        $candidate_username = 'candidate_cv';
        if (get_post_type($post_id) == 'candidate') {
            $candidate_user_id = jobsearch_get_candidate_user_id($post_id);
            $candidate_user_obj = get_user_by('ID', $candidate_user_id);
            $candidate_username = $candidate_user_obj->user_login . '_cv';
        }

        $file_ex_name = $candidate_username . '_' . rand(1000000000, 9999999999) . '_';

        if (isset($upload_file['name'])) {
            $upload_file['name'] = $file_ex_name . $upload_file['name'];
        }

        $status_upload = wp_handle_upload($upload_file, array('test_form' => false, 'mimes' => $allowed_file_types));

        if (empty($status_upload['error'])) {

            $file_url = isset($status_upload['url']) ? $status_upload['url'] : '';

            $upload_file_path = $wp_upload_dir['path'] . '/' . basename($file_url);

            // Check the type of file. We'll use this as the 'post_mime_type'.
            $filetype = wp_check_filetype(basename($file_url), null);

            // Prepare an array of post data for the attachment.
            $attachment = array(
                'guid' => $wp_upload_dir['url'] . '/' . basename($upload_file_path),
                'post_mime_type' => $filetype['type'],
                'post_title' => preg_replace('/\.[^.]+$/', '', ($upload_file['name'])),
                'post_content' => '',
                'post_status' => 'inherit'
            );

            // Insert the attachment.
            $attach_id = wp_insert_attachment($attachment, $upload_file_path, $post_id);

            // Generate the metadata for the attachment, and update the database record.
            $attach_data = wp_generate_attachment_metadata($attach_id, $upload_file_path);
            wp_update_attachment_metadata($attach_id, $attach_data);

            return $attach_id;
        }

        if ($user_dir_filter === true) {
            remove_filter('upload_dir', 'jobsearch_user_upload_files_path');
        }
    }

    return false;
}

// get user package used apps
function jobsearch_pckg_order_used_apps($order_id = 0) {
    $apps_list_count = 0;
    if ($order_id > 0) {
        $total_apps = get_post_meta($order_id, 'num_of_apps', true);
        $apps_list = get_post_meta($order_id, 'jobsearch_order_apps_list', true);

        if (!empty($apps_list)) {
            $apps_list_count = count(explode(',', $apps_list));
        }
    }

    return $apps_list_count;
}

// get user package remaining apps
function jobsearch_pckg_order_remaining_apps($order_id = 0) {
    $remaining_apps = 0;
    if ($order_id > 0) {
        $total_apps = get_post_meta($order_id, 'num_of_apps', true);
        $used_apps = jobsearch_pckg_order_used_apps($order_id);

        $remaining_apps = $total_apps > $used_apps ? $total_apps - $used_apps : 0;
    }

    return $remaining_apps;
}

// check if user package subscribed
function jobsearch_app_pckg_is_subscribed($pckg_id = 0, $user_id = 0) {
    if ($user_id <= 0 && is_user_logged_in()) {
        $user_id = get_current_user_id();
    }
    $args = array(
        'post_type' => 'shop_order',
        'posts_per_page' => '-1',
        'post_status' => 'wc-completed',
        'order' => 'DESC',
        'orderby' => 'ID',
        'fields' => 'ids',
        'meta_query' => array(
            array(
                'key' => 'package_type',
                'value' => 'candidate',
                'compare' => '=',
            ),
            array(
                'key' => 'jobsearch_order_package',
                'value' => $pckg_id,
                'compare' => '=',
            ),
            array(
                'key' => 'package_expiry_timestamp',
                'value' => strtotime(current_time('d-m-Y H:i:s', 1)),
                'compare' => '>',
            ),
            array(
                'key' => 'jobsearch_order_user',
                'value' => $user_id,
                'compare' => '=',
            ),
        ),
    );
    $pkgs_query = new WP_Query($args);

    $pkgs_query_posts = $pkgs_query->posts;
    if (!empty($pkgs_query_posts)) {
        foreach ($pkgs_query_posts as $order_post_id) {
            $remaining_apps = jobsearch_pckg_order_remaining_apps($order_post_id);
            if ($remaining_apps > 0) {
                return $order_post_id;
            }
        }
    }
    return false;
}

// check if user package subscribed
function jobsearch_candidate_first_subscribed_app_pkg($user_id = 0) {
    if ($user_id <= 0 && is_user_logged_in()) {
        $user_id = get_current_user_id();
    }
    $args = array(
        'post_type' => 'shop_order',
        'posts_per_page' => '-1',
        'post_status' => 'wc-completed',
        'order' => 'ASC',
        'orderby' => 'ID',
        'fields' => 'ids',
        'meta_query' => array(
            array(
                'key' => 'jobsearch_order_attach_with',
                'value' => 'package',
                'compare' => '=',
            ),
            array(
                'key' => 'package_type',
                'value' => 'candidate',
                'compare' => '=',
            ),
            array(
                'key' => 'package_expiry_timestamp',
                'value' => strtotime(current_time('d-m-Y H:i:s', 1)),
                'compare' => '>',
            ),
            array(
                'key' => 'jobsearch_order_user',
                'value' => $user_id,
                'compare' => '=',
            ),
        ),
    );
    $pkgs_query = new WP_Query($args);

    $pkgs_query_posts = $pkgs_query->posts;
    if (!empty($pkgs_query_posts)) {
        foreach ($pkgs_query_posts as $order_post_id) {
            $remaining_apps = jobsearch_pckg_order_remaining_apps($order_post_id);
            if ($remaining_apps > 0) {
                return $order_post_id;
            }
        }
    }
    return false;
}

// check if user app package expired
function jobsearch_app_pckg_order_is_expired($order_id = 0) {

    $order_post_id = $order_id;
    $expiry_timestamp = get_post_meta($order_post_id, 'package_expiry_timestamp', true);


    if ($expiry_timestamp <= strtotime(current_time('d-m-Y H:i:s', 1))) {
        return true;
    }

    $remaining_apps = jobsearch_pckg_order_remaining_apps($order_post_id);
    if ($remaining_apps < 1) {
        return true;
    }
    return false;
}

add_filter('jobsearch_user_attach_cv_file_url', 'jobsearch_user_attach_cv_file_url', 10, 3);

function jobsearch_user_attach_cv_file_url($cv_file_url, $candidate_id, $job_id = 0) {
    global $jobsearch_plugin_options;
    $multiple_cv_files_allow = isset($jobsearch_plugin_options['multiple_cv_uploads']) ? $jobsearch_plugin_options['multiple_cv_uploads'] : '';
    if ($multiple_cv_files_allow == 'on') {
        $ca_at_cv_files = get_post_meta($candidate_id, 'candidate_cv_files', true);
        if (!empty($ca_at_cv_files)) {
            $files_counter = 1;
            foreach ($ca_at_cv_files as $cv_file_key => $cv_file_val) {
                $file_attach_id = isset($cv_file_val['file_id']) ? $cv_file_val['file_id'] : '';
                $file_url = isset($cv_file_val['file_url']) ? $cv_file_val['file_url'] : '';
                $cv_primary = isset($cv_file_val['primary']) ? $cv_file_val['primary'] : '';
                if ($files_counter == 1 && $file_url != '') {
                    $cv_file_url = $file_url;
                }
                if ($cv_primary == 'yes' && $file_url != '') {
                    $cv_file_url = $file_url;
                }
                $files_counter++;
            }
            if ($job_id > 0) {
                $get_job_apps_cv_att = get_post_meta($job_id, 'job_apps_cv_att', true);
                $attach_cv_job = isset($get_job_apps_cv_att[$candidate_id]) ? $get_job_apps_cv_att[$candidate_id] : '';
                if ($attach_cv_job > 0) {
                    $att_file_post = get_post($attach_cv_job);
                    if (is_object($att_file_post) && isset($att_file_post->ID)) {
                        $cv_file_url = $att_file_post->guid;
                    }
                }
            }
        }
    }
    return $cv_file_url;
}

add_filter('jobsearch_user_attach_cv_file_title', 'jobsearch_user_attach_cv_file_title', 10, 3);

function jobsearch_user_attach_cv_file_title($cv_file_title, $candidate_id, $job_id = 0) {
    global $jobsearch_plugin_options;
    $multiple_cv_files_allow = isset($jobsearch_plugin_options['multiple_cv_uploads']) ? $jobsearch_plugin_options['multiple_cv_uploads'] : '';
    if ($multiple_cv_files_allow == 'on') {
        $ca_at_cv_files = get_post_meta($candidate_id, 'candidate_cv_files', true);
        if (!empty($ca_at_cv_files)) {
            $files_counter = 1;
            foreach ($ca_at_cv_files as $cv_file_key => $cv_file_val) {
                $file_attach_id = isset($cv_file_val['file_id']) ? $cv_file_val['file_id'] : '';
                $file_url = isset($cv_file_val['file_url']) ? $cv_file_val['file_url'] : '';
                $cv_primary = isset($cv_file_val['primary']) ? $cv_file_val['primary'] : '';
                $att_file_post = get_post($file_attach_id);
                if (is_object($att_file_post) && isset($att_file_post->ID)) {
                    if ($files_counter == 1) {
                        $cv_file_title = get_the_title($att_file_post->ID);
                    }
                    if ($cv_primary == 'yes') {
                        $cv_file_title = get_the_title($att_file_post->ID);
                    }
                }
                $files_counter++;
            }
            if ($job_id > 0) {
                $get_job_apps_cv_att = get_post_meta($job_id, 'job_apps_cv_att', true);
                $attach_cv_job = isset($get_job_apps_cv_att[$candidate_id]) ? $get_job_apps_cv_att[$candidate_id] : '';
                if ($attach_cv_job > 0) {
                    $att_file_post = get_post($attach_cv_job);
                    if (is_object($att_file_post) && isset($att_file_post->ID)) {
                        $cv_file_title = get_the_title($att_file_post->ID);
                    }
                }
            }
        }
    }
    return $cv_file_title;
}
