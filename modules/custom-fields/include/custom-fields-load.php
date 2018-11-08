<?php
/*
  Class : CustomFieldLoad
 */


// this is an include only WP file
if (!defined('ABSPATH')) {
    die;
}

// main plugin class
class Jobsearch_CustomFieldLoad {

// hook things up
    public function __construct() {

        // Save custom fields
        add_action('jobsearch_custom_fields_load', array($this, 'jobsearch_custom_fields_load_callback'), 1, 2);
        add_filter('jobsearch_custom_field_text_load', array($this, 'jobsearch_custom_field_text_load_callback'), 1, 4);
        add_filter('jobsearch_custom_field_dropdown_load', array($this, 'jobsearch_custom_field_dropdown_load_callback'), 1, 4);
        add_filter('jobsearch_custom_field_heading_load', array($this, 'jobsearch_custom_field_heading_load_callback'), 1, 4);
        add_filter('jobsearch_custom_field_textarea_load', array($this, 'jobsearch_custom_field_textarea_load_callback'), 1, 4);
        add_filter('jobsearch_custom_field_email_load', array($this, 'jobsearch_custom_field_email_load_callback'), 1, 4);
        add_filter('jobsearch_custom_field_number_load', array($this, 'jobsearch_custom_field_number_load_callback'), 1, 4);
        add_filter('jobsearch_custom_field_date_load', array($this, 'jobsearch_custom_field_date_load_callback'), 1, 4);
        add_filter('jobsearch_custom_field_range_load', array($this, 'jobsearch_custom_field_range_load_callback'), 1, 4);

        // For frontend dashboard custom fields
        add_action('jobsearch_dashboard_custom_fields_load', array($this, 'jobsearch_dashboard_custom_fields_load_callback'), 1, 2);
        add_filter('jobsearch_dashboard_custom_field_text_load', array($this, 'jobsearch_dashboard_custom_field_text_load_callback'), 1, 4);
        add_filter('jobsearch_dashboard_custom_field_dropdown_load', array($this, 'jobsearch_dashboard_custom_field_dropdown_load_callback'), 1, 4);
        add_filter('jobsearch_dashboard_custom_field_heading_load', array($this, 'jobsearch_dashboard_custom_field_heading_load_callback'), 1, 4);
        add_filter('jobsearch_dashboard_custom_field_textarea_load', array($this, 'jobsearch_dashboard_custom_field_textarea_load_callback'), 1, 4);
        add_filter('jobsearch_dashboard_custom_field_email_load', array($this, 'jobsearch_dashboard_custom_field_email_load_callback'), 1, 4);
        add_filter('jobsearch_dashboard_custom_field_number_load', array($this, 'jobsearch_dashboard_custom_field_number_load_callback'), 1, 4);
        add_filter('jobsearch_dashboard_custom_field_date_load', array($this, 'jobsearch_dashboard_custom_field_date_load_callback'), 1, 4);
        add_filter('jobsearch_dashboard_custom_field_range_load', array($this, 'jobsearch_dashboard_custom_field_range_load_callback'), 1, 4);
        //
        // For simple form custom fields
        add_action('jobsearch_form_custom_fields_load', array($this, 'jobsearch_form_custom_fields_load_callback'), 1, 2);
        add_action('jobsearch_signup_custom_fields_load', array($this, 'jobsearch_signup_custom_fields_load_callback'), 1, 3);
        add_action('jobsearch_register_custom_fields_error', array($this, 'register_custom_fields_error'), 10, 2);

        add_filter('jobsearch_form_custom_field_text_load', array($this, 'jobsearch_form_custom_field_text_load_callback'), 1, 6);
        add_filter('jobsearch_form_custom_field_dropdown_load', array($this, 'jobsearch_form_custom_field_dropdown_load_callback'), 1, 6);
        add_filter('jobsearch_form_custom_field_heading_load', array($this, 'jobsearch_form_custom_field_heading_load_callback'), 1, 6);
        add_filter('jobsearch_form_custom_field_textarea_load', array($this, 'jobsearch_form_custom_field_textarea_load_callback'), 1, 6);
        add_filter('jobsearch_form_custom_field_email_load', array($this, 'jobsearch_form_custom_field_email_load_callback'), 1, 6);
        add_filter('jobsearch_form_custom_field_number_load', array($this, 'jobsearch_form_custom_field_number_load_callback'), 1, 6);
        add_filter('jobsearch_form_custom_field_date_load', array($this, 'jobsearch_form_custom_field_date_load_callback'), 1, 6);
        add_filter('jobsearch_form_custom_field_range_load', array($this, 'jobsearch_form_custom_field_range_load_callback'), 1, 6);
        //
        // For translate custom fields
        add_action('init', array($this, 'custom_fields_translation'), 10);
        add_action('jobsearch_custom_field_heading_translate', array($this, 'jobsearch_custom_field_heading_translate'), 10, 1);
        add_action('jobsearch_custom_field_text_translate', array($this, 'jobsearch_custom_field_text_translate'), 10, 1);
        add_action('jobsearch_custom_field_email_translate', array($this, 'jobsearch_custom_field_email_translate'), 10, 1);
        add_action('jobsearch_custom_field_textarea_translate', array($this, 'jobsearch_custom_field_textarea_translate'), 10, 1);
        add_action('jobsearch_custom_field_date_translate', array($this, 'jobsearch_custom_field_date_translate'), 10, 1);
        add_action('jobsearch_custom_field_number_translate', array($this, 'jobsearch_custom_field_number_translate'), 10, 1);
        add_action('jobsearch_custom_field_range_translate', array($this, 'jobsearch_custom_field_range_translate'), 10, 1);
        add_action('jobsearch_custom_field_dropdown_translate', array($this, 'jobsearch_custom_field_dropdown_translate'), 10, 1);
        add_action('jobsearch_custom_field_salary_translate', array($this, 'jobsearch_custom_field_dropdown_translate'), 10, 1);
        //
        // Save custom fields values to duplicate post
        add_action('jobsearch_dashboard_pass_values_to_duplicate_post', array($this, 'pass_values_to_duplicate_post'), 10, 3);
        //

        add_filter('jobsearch_custom_fields_list', array($this, 'jobsearch_custom_fields_list_callback'), 11, 8);
        add_filter('jobsearch_custom_fields_filter_box_html', array($this, 'jobsearch_custom_fields_filter_box_html_callback'), 1, 6);
        add_filter('jobsearch_custom_fields_top_filters_html', array($this, 'custom_fields_top_filter_box_html_callback'), 1, 3);
        add_filter('jobsearch_custom_fields_load_filter_array_html', array($this, 'jobsearch_custom_fields_load_filter_array_html_callback'), 1, 3);
        add_filter('jobsearch_custom_fields_load_precentage_array', array($this, 'jobsearch_custom_fields_load_precentage_array_callback'), 1, 2);

        // Save custom fields values in signup form
        add_action('jobsearch_signup_custom_fields_save', array($this, 'signup_custom_fields_save'), 10, 2);
        //
    }

    public function signup_custom_fields_save($custom_field_entity, $post_id) {
        // load all saved fields
        $field_db_slug = "jobsearch_custom_field_" . $custom_field_entity;
        $custom_all_fields_saved_data = get_option($field_db_slug);

        if (is_array($custom_all_fields_saved_data) && sizeof($custom_all_fields_saved_data) > 0) {

            foreach ($custom_all_fields_saved_data as $f_key => $custom_field_saved_data) {

                $field_name = isset($custom_field_saved_data['name']) ? $custom_field_saved_data['name'] : '';
                if ($field_name != '' && isset($_POST[$field_name])) {
                    update_post_meta($post_id, $field_name, $_POST[$field_name]);
                }
            }
        }
    }

    public function pass_values_to_duplicate_post($post_id, $duplicate_post_id, $custom_field_entity) {
        // load all saved fields
        $field_db_slug = "jobsearch_custom_field_" . $custom_field_entity;
        $custom_all_fields_saved_data = get_option($field_db_slug);

        if (is_array($custom_all_fields_saved_data) && sizeof($custom_all_fields_saved_data) > 0) {

            foreach ($custom_all_fields_saved_data as $f_key => $custom_field_saved_data) {

                $field_name = isset($custom_field_saved_data['name']) ? $custom_field_saved_data['name'] : '';
                if ($field_name != '') {
                    $field_name_db_val = get_post_meta($post_id, $field_name, true);
                    update_post_meta($duplicate_post_id, $field_name, $field_name_db_val);
                }
            }
        }
    }

    public function custom_fields_translation() {
        $custom_field_entities = array('job', 'candidate', 'employer');

        foreach ($custom_field_entities as $custom_field_entity) {
            $field_db_slug = "jobsearch_custom_field_" . $custom_field_entity;
            $custom_all_fields_saved_data = get_option($field_db_slug);
            $count_node = time();
            $all_fields_name_str = '';
            if (is_array($custom_all_fields_saved_data) && sizeof($custom_all_fields_saved_data) > 0) {
                $field_names_counter = 0;
                foreach ($custom_all_fields_saved_data as $f_key => $custom_field_saved_data) {

                    if (isset($custom_field_saved_data['type']) && $custom_field_saved_data['type'] == "heading") {
                        do_action('jobsearch_custom_field_heading_translate', $custom_field_saved_data);
                    } elseif (isset($custom_field_saved_data['type']) && $custom_field_saved_data['type'] == "text") {
                        do_action('jobsearch_custom_field_text_translate', $custom_field_saved_data);
                    } else if (isset($custom_field_saved_data['type']) && $custom_field_saved_data['type'] == "email") {
                        do_action('jobsearch_custom_field_email_translate', $custom_field_saved_data);
                    } else if (isset($custom_field_saved_data['type']) && $custom_field_saved_data['type'] == "textarea") {
                        do_action('jobsearch_custom_field_textarea_translate', $custom_field_saved_data);
                    } else if (isset($custom_field_saved_data['type']) && $custom_field_saved_data['type'] == "date") {
                        do_action('jobsearch_custom_field_date_translate', $custom_field_saved_data);
                    } else if (isset($custom_field_saved_data['type']) && $custom_field_saved_data['type'] == "number") {
                        do_action('jobsearch_custom_field_number_translate', $custom_field_saved_data);
                    } else if (isset($custom_field_saved_data['type']) && $custom_field_saved_data['type'] == "range") {
                        do_action('jobsearch_custom_field_range_translate', $custom_field_saved_data);
                    } else if (isset($custom_field_saved_data['type']) && $custom_field_saved_data['type'] == "dropdown") {
                        do_action('jobsearch_custom_field_dropdown_translate', $custom_field_saved_data);
                    } else if (isset($custom_field_saved_data['type']) && $custom_field_saved_data['type'] == "salary") {
                        do_action('jobsearch_custom_field_salary_translate', $custom_field_saved_data);
                    }
                }
            }
        }
    }

    public function jobsearch_custom_field_salary_translate($custom_field_saved_data) {

        global $sitepress;

        $text_field_label = isset($custom_field_saved_data['label']) ? $custom_field_saved_data['label'] : '';
        $lang_code = '';
        if (function_exists('icl_object_id')) {
            $lang_code = $sitepress->get_current_language();
        }
        do_action('wpml_register_single_string', 'Custom Fields', 'Salary Label - ' . $text_field_label, $text_field_label);
    }

    public function jobsearch_custom_field_text_translate($custom_field_saved_data) {

        global $sitepress;

        $text_field_label = isset($custom_field_saved_data['label']) ? $custom_field_saved_data['label'] : '';
        $text_field_placeholder = isset($custom_field_saved_data['placeholder']) ? $custom_field_saved_data['placeholder'] : '';
        $lang_code = '';
        if (function_exists('icl_object_id')) {
            $lang_code = $sitepress->get_current_language();
        }
        do_action('wpml_register_single_string', 'Custom Fields', 'Text Field Label - ' . $text_field_label, $text_field_label);
        do_action('wpml_register_single_string', 'Custom Fields', 'Text Field Placeholder - ' . $text_field_placeholder, $text_field_placeholder);
    }

    public function jobsearch_custom_field_email_translate($custom_field_saved_data) {

        global $sitepress;

        $email_field_label = isset($custom_field_saved_data['label']) ? $custom_field_saved_data['label'] : '';
        $email_field_placeholder = isset($custom_field_saved_data['placeholder']) ? $custom_field_saved_data['placeholder'] : '';

        $lang_code = '';
        if (function_exists('icl_object_id')) {
            $lang_code = $sitepress->get_current_language();
        }
        do_action('wpml_register_single_string', 'Custom Fields', 'Email Field Label - ' . $email_field_label, $email_field_label);
        do_action('wpml_register_single_string', 'Custom Fields', 'Email Field Placeholder - ' . $email_field_placeholder, $email_field_placeholder);
    }

    public function jobsearch_custom_field_number_translate($custom_field_saved_data) {

        global $sitepress;

        $number_field_label = isset($custom_field_saved_data['label']) ? $custom_field_saved_data['label'] : '';
        $number_field_placeholder = isset($custom_field_saved_data['placeholder']) ? $custom_field_saved_data['placeholder'] : '';

        $lang_code = '';
        if (function_exists('icl_object_id')) {
            $lang_code = $sitepress->get_current_language();
        }
        do_action('wpml_register_single_string', 'Custom Fields', 'Number Field Label - ' . $number_field_label, $number_field_label);
        do_action('wpml_register_single_string', 'Custom Fields', 'Number Field Placeholder - ' . $number_field_placeholder, $number_field_placeholder);
    }

    public function jobsearch_custom_field_date_translate($custom_field_saved_data) {

        global $sitepress;

        $date_field_label = isset($custom_field_saved_data['label']) ? $custom_field_saved_data['label'] : '';
        $date_field_placeholder = isset($custom_field_saved_data['placeholder']) ? $custom_field_saved_data['placeholder'] : '';

        $lang_code = '';
        if (function_exists('icl_object_id')) {
            $lang_code = $sitepress->get_current_language();
        }
        do_action('wpml_register_single_string', 'Custom Fields', 'Date Field Label - ' . $date_field_label, $date_field_label);
        do_action('wpml_register_single_string', 'Custom Fields', 'Date Field Placeholder - ' . $date_field_placeholder, $date_field_placeholder);
    }

    public function jobsearch_custom_field_range_translate($custom_field_saved_data) {

        global $sitepress;
        $range_field_label = isset($custom_field_saved_data['label']) ? $custom_field_saved_data['label'] : '';
        $range_field_placeholder = isset($custom_field_saved_data['placeholder']) ? $custom_field_saved_data['placeholder'] : '';
        $lang_code = '';
        if (function_exists('icl_object_id')) {
            $lang_code = $sitepress->get_current_language();
        }
        do_action('wpml_register_single_string', 'Custom Fields', 'Range Field Label - ' . $range_field_label, $range_field_label);
        do_action('wpml_register_single_string', 'Custom Fields', 'Range Field Placeholder - ' . $range_field_placeholder, $range_field_placeholder);
    }

    public function jobsearch_custom_field_dropdown_translate($custom_field_saved_data) {

        global $sitepress;

        $dropdown_field_label = isset($custom_field_saved_data['label']) ? $custom_field_saved_data['label'] : '';
        $dropdown_field_placeholder = isset($custom_field_saved_data['placeholder']) ? $custom_field_saved_data['placeholder'] : '';
        $dropdown_field_options = isset($custom_field_saved_data['options']) ? $custom_field_saved_data['options'] : '';

        if (isset($dropdown_field_options['value']) && count($dropdown_field_options['value']) > 0) {
            $option_counter = 0;
            foreach ($dropdown_field_options['value'] as $option) {
                if ($option != '') {
                    $option = ltrim(rtrim($option));
                    if ($dropdown_field_options['label'][$option_counter] != '' && str_replace(" ", "-", $option) != '') {
                        $option_label = $dropdown_field_options['label'][$option_counter];

                        $lang_code = '';
                        if (function_exists('icl_object_id')) {
                            $lang_code = $sitepress->get_current_language();
                        }
                        do_action('wpml_register_single_string', 'Custom Fields', 'Dropdown Option Label - ' . $option_label, $option_label);
                    }
                }
                $option_counter ++;
            }
        }

        $lang_code = '';
        if (function_exists('icl_object_id')) {
            $lang_code = $sitepress->get_current_language();
        }
        do_action('wpml_register_single_string', 'Custom Fields', 'Dropdown Field Label - ' . $dropdown_field_label, $dropdown_field_label);
        do_action('wpml_register_single_string', 'Custom Fields', 'Dropdown Field Placeholder - ' . $dropdown_field_placeholder, $dropdown_field_placeholder);
    }

    public function jobsearch_custom_field_textarea_translate($custom_field_saved_data) {

        global $sitepress;

        $textarea_field_label = isset($custom_field_saved_data['label']) ? $custom_field_saved_data['label'] : '';
        $textarea_field_placeholder = isset($custom_field_saved_data['placeholder']) ? $custom_field_saved_data['placeholder'] : '';

        $lang_code = '';
        if (function_exists('icl_object_id')) {
            $lang_code = $sitepress->get_current_language();
        }
        do_action('wpml_register_single_string', 'Custom Fields', 'Textarea Field Label - ' . $textarea_field_label, $textarea_field_label);
        do_action('wpml_register_single_string', 'Custom Fields', 'Textarea Field Placeholder - ' . $textarea_field_placeholder, $textarea_field_placeholder);
    }

    public function jobsearch_custom_field_heading_translate($custom_field_saved_data) {

        global $sitepress;

        $heading_field_label = isset($custom_field_saved_data['label']) ? $custom_field_saved_data['label'] : '';

        $lang_code = '';
        if (function_exists('icl_object_id')) {
            $lang_code = $sitepress->get_current_language();
        }
        do_action('wpml_register_single_string', 'Custom Fields', 'Heading Field Label - ' . $heading_field_label, $heading_field_label);
    }

    static function jobsearch_custom_fields_load_callback($post_id, $custom_field_entity) {
        // load all saved fields
        $field_db_slug = "jobsearch_custom_field_" . $custom_field_entity;
        $custom_all_fields_saved_data = get_option($field_db_slug);
        $count_node = time();
        $all_fields_name_str = '';
        if (is_array($custom_all_fields_saved_data) && sizeof($custom_all_fields_saved_data) > 0) {
            $field_names_counter = 0;
            $fields_prefix = '';
            $output = '';
            foreach ($custom_all_fields_saved_data as $f_key => $custom_field_saved_data) {

                if (isset($custom_field_saved_data['type']) && $custom_field_saved_data['type'] == "heading") {
                    $output .= apply_filters('jobsearch_custom_field_heading_load', '', $post_id, $custom_field_saved_data, $fields_prefix);
                } elseif (isset($custom_field_saved_data['type']) && $custom_field_saved_data['type'] == "text") {
                    $output .= apply_filters('jobsearch_custom_field_text_load', '', $post_id, $custom_field_saved_data, $fields_prefix);
                } else if (isset($custom_field_saved_data['type']) && $custom_field_saved_data['type'] == "email") {
                    $output .= apply_filters('jobsearch_custom_field_email_load', '', $post_id, $custom_field_saved_data, $fields_prefix);
                } else if (isset($custom_field_saved_data['type']) && $custom_field_saved_data['type'] == "textarea") {
                    $output .= apply_filters('jobsearch_custom_field_textarea_load', '', $post_id, $custom_field_saved_data, $fields_prefix);
                } else if (isset($custom_field_saved_data['type']) && $custom_field_saved_data['type'] == "date") {
                    $output .= apply_filters('jobsearch_custom_field_date_load', '', $post_id, $custom_field_saved_data, $fields_prefix);
                } else if (isset($custom_field_saved_data['type']) && $custom_field_saved_data['type'] == "number") {
                    $output .= apply_filters('jobsearch_custom_field_number_load', '', $post_id, $custom_field_saved_data, $fields_prefix);
                } else if (isset($custom_field_saved_data['type']) && $custom_field_saved_data['type'] == "range") {
                    $output .= apply_filters('jobsearch_custom_field_range_load', '', $post_id, $custom_field_saved_data, $fields_prefix);
                } else if (isset($custom_field_saved_data['type']) && $custom_field_saved_data['type'] == "dropdown") {
                    $output .= apply_filters('jobsearch_custom_field_dropdown_load', '', $post_id, $custom_field_saved_data, $fields_prefix);
                }
            }
            $output .= apply_filters('jobsearch_custom_fields_load_after', '', $post_id, $custom_field_entity);
            echo force_balance_tags($output);
        }
    }

    static function jobsearch_custom_field_text_load_callback($html, $post_id, $custom_field_saved_data, $fields_prefix) {

        ob_start();
        $field_for_wuser = isset($custom_field_saved_data['non_reg_user']) ? $custom_field_saved_data['non_reg_user'] : '';
        $text_field_label = isset($custom_field_saved_data['label']) ? $custom_field_saved_data['label'] : '';
        $text_field_name = isset($custom_field_saved_data['name']) ? $fields_prefix . $custom_field_saved_data['name'] : '';
        $text_field_classes = isset($custom_field_saved_data['classes']) ? $custom_field_saved_data['classes'] : '';
        $text_field_placeholder = isset($custom_field_saved_data['placeholder']) ? $custom_field_saved_data['placeholder'] : '';
        $text_field_required = isset($custom_field_saved_data['required']) ? $custom_field_saved_data['required'] : '';
        $text_field_required_str = '';
        if ($text_field_required == 'yes') {
            $text_field_required_str = 'required="required"';
        }
        // get db value if saved
        $text_field_name_db_val = get_post_meta($post_id, $text_field_name, true);
        ?>
        <div class="jobsearch-element-field">
            <div class="elem-label">
                <label><?php echo esc_html($text_field_label) ?></label>
            </div>
            <div class="elem-field">
                <input type="text" name="<?php echo esc_html($text_field_name) ?>" class="<?php echo esc_html($text_field_classes) ?>" placeholder="<?php echo esc_html($text_field_placeholder) ?>" <?php echo force_balance_tags($text_field_required_str) ?> value="<?php echo esc_html($text_field_name_db_val) ?>" />
            </div>
        </div>
        <?php
        $html .= ob_get_clean();

        if ($field_for_wuser == 'non_reg' && is_user_logged_in()) {
            $html = '';
        } else if ($field_for_wuser == 'for_reg' && !is_user_logged_in()) {
            $html = '';
        }

        return $html;
    }

    static function jobsearch_custom_field_email_load_callback($html, $post_id, $custom_field_saved_data, $fields_prefix) {
        ob_start();
        $field_for_wuser = isset($custom_field_saved_data['non_reg_user']) ? $custom_field_saved_data['non_reg_user'] : '';
        $email_field_label = isset($custom_field_saved_data['label']) ? $custom_field_saved_data['label'] : '';
        $email_field_name = isset($custom_field_saved_data['name']) ? $fields_prefix . $custom_field_saved_data['name'] : '';
        $email_field_classes = isset($custom_field_saved_data['classes']) ? $custom_field_saved_data['classes'] : '';
        $email_field_placeholder = isset($custom_field_saved_data['placeholder']) ? $custom_field_saved_data['placeholder'] : '';
        $email_field_required = isset($custom_field_saved_data['required']) ? $custom_field_saved_data['required'] : '';
        $email_field_required_str = '';
        if ($email_field_required == 'yes') {
            $email_field_required_str = 'required="required"';
        }
        // get db value if saved
        $email_field_name_db_val = get_post_meta($post_id, $email_field_name, true);
        ?>
        <div class="jobsearch-element-field">
            <div class="elem-label">
                <label><?php echo esc_html($email_field_label) ?></label>
            </div>
            <div class="elem-field">
                <input type="email" name="<?php echo esc_html($email_field_name) ?>" class="<?php echo esc_html($email_field_classes) ?>" placeholder="<?php echo esc_html($email_field_placeholder) ?>" <?php echo force_balance_tags($email_field_required_str) ?> value="<?php echo esc_html($email_field_name_db_val) ?>" />
            </div>
        </div>
        <?php
        $html .= ob_get_clean();

        if ($field_for_wuser == 'non_reg' && is_user_logged_in()) {
            $html = '';
        } else if ($field_for_wuser == 'for_reg' && !is_user_logged_in()) {
            $html = '';
        }

        return $html;
    }

    static function jobsearch_custom_field_number_load_callback($html, $post_id, $custom_field_saved_data, $fields_prefix) {
        ob_start();
        $field_for_wuser = isset($custom_field_saved_data['non_reg_user']) ? $custom_field_saved_data['non_reg_user'] : '';
        $number_field_label = isset($custom_field_saved_data['label']) ? $custom_field_saved_data['label'] : '';
        $number_field_name = isset($custom_field_saved_data['name']) ? $fields_prefix . $custom_field_saved_data['name'] : '';
        $number_field_classes = isset($custom_field_saved_data['classes']) ? $custom_field_saved_data['classes'] : '';
        $number_field_placeholder = isset($custom_field_saved_data['placeholder']) ? $custom_field_saved_data['placeholder'] : '';
        $number_field_required = isset($custom_field_saved_data['required']) ? $custom_field_saved_data['required'] : '';
        $number_field_required_str = '';
        if ($number_field_required == 'yes') {
            $number_field_required_str = 'required="required"';
        }
        // get db value if saved
        $number_field_name_db_val = get_post_meta($post_id, $number_field_name, true);
        ?>
        <div class="jobsearch-element-field">
            <div class="elem-label">
                <label><?php echo esc_html($number_field_label) ?></label>
            </div>
            <div class="elem-field">
                <input type="number" name="<?php echo esc_html($number_field_name) ?>" class="<?php echo esc_html($number_field_classes) ?>" placeholder="<?php echo esc_html($number_field_placeholder) ?>" <?php echo force_balance_tags($number_field_required_str) ?> value="<?php echo esc_html($number_field_name_db_val) ?>" />
            </div>
        </div>
        <?php
        $html .= ob_get_clean();

        if ($field_for_wuser == 'non_reg' && is_user_logged_in()) {
            $html = '';
        } else if ($field_for_wuser == 'for_reg' && !is_user_logged_in()) {
            $html = '';
        }

        return $html;
    }

    static function jobsearch_custom_field_date_load_callback($html, $post_id, $custom_field_saved_data, $fields_prefix) {
        ob_start();
        $field_rand_id = rand(454, 999999);
        $field_for_wuser = isset($custom_field_saved_data['non_reg_user']) ? $custom_field_saved_data['non_reg_user'] : '';
        $date_field_label = isset($custom_field_saved_data['label']) ? $custom_field_saved_data['label'] : '';
        $date_field_name = isset($custom_field_saved_data['name']) ? $fields_prefix . $custom_field_saved_data['name'] : '';
        $date_field_classes = isset($custom_field_saved_data['classes']) ? $custom_field_saved_data['classes'] : '';
        $date_field_placeholder = isset($custom_field_saved_data['placeholder']) ? $custom_field_saved_data['placeholder'] : '';
        $date_field_required = isset($custom_field_saved_data['required']) ? $custom_field_saved_data['required'] : '';
        $date_field_date_format = isset($custom_field_saved_data['date-format']) ? $custom_field_saved_data['date-format'] : 'd-m-Y';
        $date_field_required_str = '';
        if ($date_field_required == 'yes') {
            $date_field_required_str = 'required="required"';
        }
        // get db value if saved
        $date_field_name_db_val = get_post_meta($post_id, $date_field_name, true);
        if ($date_field_name_db_val != '') {
            $date_field_name_db_val = date($date_field_date_format, $date_field_name_db_val);
        }
        ?>
        <div class="jobsearch-element-field">
            <div class="elem-label">
                <label><?php echo esc_html($date_field_label) ?></label>
            </div>
            <div class="elem-field">
                <input type="text" id="<?php echo esc_html($date_field_name . $field_rand_id) ?>" name="<?php echo esc_html($date_field_name) ?>" class="<?php echo esc_html($date_field_classes) ?>" placeholder="<?php echo esc_html($date_field_placeholder) ?>" <?php echo force_balance_tags($date_field_required_str) ?> value="<?php echo esc_html($date_field_name_db_val) ?>" />
            </div>
        </div>
        <script>
            jQuery(document).ready(function () {
                jQuery('#<?php echo esc_html($date_field_name . $field_rand_id) ?>').datetimepicker({
                    format: '<?php echo esc_html($date_field_date_format) ?>'
                });
            });
        </script>
        <?php
        $html .= ob_get_clean();

        if ($field_for_wuser == 'non_reg' && is_user_logged_in()) {
            $html = '';
        } else if ($field_for_wuser == 'for_reg' && !is_user_logged_in()) {
            $html = '';
        }

        return $html;
    }

    static function jobsearch_custom_field_range_load_callback($html, $post_id, $custom_field_saved_data, $fields_prefix) {
        ob_start();
        $field_for_wuser = isset($custom_field_saved_data['non_reg_user']) ? $custom_field_saved_data['non_reg_user'] : '';
        $range_field_label = isset($custom_field_saved_data['label']) ? $custom_field_saved_data['label'] : '';
        $range_field_name = isset($custom_field_saved_data['name']) ? $fields_prefix . $custom_field_saved_data['name'] : '';
        $range_field_classes = isset($custom_field_saved_data['classes']) ? $custom_field_saved_data['classes'] : '';
        $range_field_placeholder = isset($custom_field_saved_data['placeholder']) ? $custom_field_saved_data['placeholder'] : '';
        $range_field_required = isset($custom_field_saved_data['required']) ? $custom_field_saved_data['required'] : '';
        $range_field_min = isset($custom_field_saved_data['min']) ? $custom_field_saved_data['min'] : '0';
        $range_field_laps = isset($custom_field_saved_data['laps']) ? $custom_field_saved_data['laps'] : '20';
        $range_field_interval = isset($custom_field_saved_data['interval']) ? $custom_field_saved_data['interval'] : '10000';
        $rand_id = rand(123, 123467);
        $range_field_required_str = '';
        if ($range_field_required == 'yes') {
            $range_field_required_str = 'required="required"';
        }
        // get db value if saved
        $range_field_name_db_val = get_post_meta($post_id, $range_field_name, true);
        wp_enqueue_style('jquery-ui');
        wp_enqueue_script('jquery-ui');
        $range_field_max = $range_field_min;
        $i = 0;
        while ($range_field_laps > $i) {
            $range_field_max = $range_field_max + $range_field_interval;
            $i++;
        }
        ?>
        <script>
            jQuery(document).ready(function () {
                jQuery("#slider-range<?php echo esc_html($range_field_name . $rand_id) ?>").slider({
                    range: "max",
                    min: <?php echo absint($range_field_min); ?>,
                    max: <?php echo absint($range_field_max); ?>,
                    value: <?php echo absint($range_field_name_db_val); ?>,
                    slide: function (event, ui) {
                        jQuery("#<?php echo esc_html($range_field_name . $rand_id) ?>").val(ui.value);
                    }
                });
                jQuery("#<?php echo esc_html($range_field_name . $rand_id) ?>").val(jQuery("#slider-range<?php echo esc_html($range_field_name . $rand_id) ?>").slider("value"));
            });
        </script>
        <div class="jobsearch-element-field">
            <div class="elem-label">
                <label><?php echo esc_html($range_field_label) ?></label>
            </div>
            <div class="elem-field">
                <input type="text" id="<?php echo esc_html($range_field_name . $rand_id) ?>" name="<?php echo esc_html($range_field_name) ?>" value="" readonly style="border:0; color:#f6931f; font-weight:bold;" />
                <div id="slider-range<?php echo esc_html($range_field_name . $rand_id) ?>"></div>
            </div>
        </div>
        <?php
        $html .= ob_get_clean();

        if ($field_for_wuser == 'non_reg' && is_user_logged_in()) {
            $html = '';
        } else if ($field_for_wuser == 'for_reg' && !is_user_logged_in()) {
            $html = '';
        }

        return $html;
    }

    static function jobsearch_custom_field_dropdown_load_callback($html, $post_id, $custom_field_saved_data, $fields_prefix) {
        ob_start();
        $field_for_wuser = isset($custom_field_saved_data['non_reg_user']) ? $custom_field_saved_data['non_reg_user'] : '';
        $dropdown_field_label = isset($custom_field_saved_data['label']) ? $custom_field_saved_data['label'] : '';
        $dropdown_field_name = isset($custom_field_saved_data['name']) ? $fields_prefix . $custom_field_saved_data['name'] : '';
        $dropdown_field_classes = isset($custom_field_saved_data['classes']) ? $custom_field_saved_data['classes'] : '';
        $dropdown_field_placeholder = isset($custom_field_saved_data['placeholder']) ? $custom_field_saved_data['placeholder'] : '';
        $dropdown_field_required = isset($custom_field_saved_data['required']) ? $custom_field_saved_data['required'] : '';
        $field_post_multi = isset($custom_field_saved_data['post-multi']) ? $custom_field_saved_data['post-multi'] : '';
        $dropdown_field_options = isset($custom_field_saved_data['options']) ? $custom_field_saved_data['options'] : '';
        $dropdown_field_required_str = '';
        if ($dropdown_field_required == 'yes') {
            $dropdown_field_required_str = 'required="required"';
        }
        // get db value if saved
        $dropdown_field_name_db_val = get_post_meta($post_id, $dropdown_field_name, true);
        // creat options string
        $dropdown_field_options_str = '';
        if (isset($dropdown_field_options['value']) && count($dropdown_field_options['value']) > 0) {
            $option_counter = 0;
            foreach ($dropdown_field_options['value'] as $option) {
                if ($option != '') {
                    $option = ltrim(rtrim($option));
                    if ($dropdown_field_options['label'][$option_counter] != '' && str_replace(" ", "-", $option) != '') {
                        $option_val = strtolower(str_replace(" ", "-", $option));
                        $option_label = $dropdown_field_options['label'][$option_counter];
                        if (is_array($dropdown_field_name_db_val)) {
                            $option_selected = in_array($option_val, $dropdown_field_name_db_val) ? ' selected="selected"' : '';
                        } else {
                            $option_selected = $dropdown_field_name_db_val == $option_val ? ' selected="selected"' : '';
                        }
                        $dropdown_field_options_str .= '<option ' . force_balance_tags($option_selected) . ' value="' . esc_html($option_val) . '">' . esc_html($option_label) . '</option>';
                    }
                }
                $option_counter ++;
            }
        }
        ?>
        <div class="jobsearch-element-field">
            <div class="elem-label">
                <label><?php echo esc_html($dropdown_field_label) ?></label>
            </div>

            <div class="elem-field">
                <?php
                if ($dropdown_field_options_str != '') {
                    ?>
                    <select <?php echo ($field_post_multi == 'yes' ? 'multiple="multiple" ' : '') ?>name="<?php echo esc_html($dropdown_field_name) ?><?php echo ($field_post_multi == 'yes' ? '[]' : '') ?>" class="<?php echo esc_html($dropdown_field_classes) ?>" placeholder="<?php echo esc_html($dropdown_field_placeholder) ?>" <?php echo force_balance_tags($dropdown_field_required_str) ?>>
                        <?php
                        echo force_balance_tags($dropdown_field_options_str);
                        ?>
                    </select>
                <?php } else {
                    ?>
                    <span><?php echo esc_html__('Field did not configure properly', 'wp-jobsearch'); ?></span>
                    <?php
                }
                ?>
            </div>
        </div>
        <?php
        $html .= ob_get_clean();

        if ($field_for_wuser == 'non_reg' && is_user_logged_in()) {
            $html = '';
        } else if ($field_for_wuser == 'for_reg' && !is_user_logged_in()) {
            $html = '';
        }

        return $html;
    }

    static function jobsearch_custom_field_textarea_load_callback($html, $post_id, $custom_field_saved_data, $fields_prefix) {
        ob_start();
        $field_for_wuser = isset($custom_field_saved_data['non_reg_user']) ? $custom_field_saved_data['non_reg_user'] : '';
        $textarea_field_label = isset($custom_field_saved_data['label']) ? $custom_field_saved_data['label'] : '';
        $textarea_field_name = isset($custom_field_saved_data['name']) ? $fields_prefix . $custom_field_saved_data['name'] : '';
        $textarea_field_classes = isset($custom_field_saved_data['classes']) ? $custom_field_saved_data['classes'] : '';
        $textarea_field_placeholder = isset($custom_field_saved_data['placeholder']) ? $custom_field_saved_data['placeholder'] : '';
        $textarea_field_required = isset($custom_field_saved_data['required']) ? $custom_field_saved_data['required'] : '';
        $textarea_field_required_str = '';
        if ($textarea_field_required == 'yes') {
            $textarea_field_required_str = 'required="required"';
        }
        // get db value if saved
        $textarea_field_name_db_val = get_post_meta($post_id, $textarea_field_name, true);
        ?>
        <div class="jobsearch-element-field">
            <div class="elem-label">
                <label><?php echo esc_html($textarea_field_label) ?></label>
            </div>
            <div class="elem-field">
                <?php
                $wped_settings = array(
                    'media_buttons' => false,
                    'editor_class' => $textarea_field_classes,
                    'quicktags' => array('buttons' => 'strong,em,del,ul,ol,li,close'),
                    'tinymce' => array(
                        'toolbar1' => 'bold,bullist,numlist,italic,underline,alignleft,aligncenter,alignright,separator,link,unlink,undo,redo',
                        'toolbar2' => '',
                        'toolbar3' => '',
                    ),
                );
                wp_editor($textarea_field_name_db_val, $textarea_field_name, $wped_settings);
                ?>
            </div>
        </div>
        <?php
        $html .= ob_get_clean();

        if ($field_for_wuser == 'non_reg' && is_user_logged_in()) {
            $html = '';
        } else if ($field_for_wuser == 'for_reg' && !is_user_logged_in()) {
            $html = '';
        }

        return $html;
    }

    static function jobsearch_custom_field_heading_load_callback($html, $post_id, $custom_field_saved_data, $fields_prefix) {
        ob_start();
        $field_for_wuser = isset($custom_field_saved_data['non_reg_user']) ? $custom_field_saved_data['non_reg_user'] : '';
        $heading_field_label = isset($custom_field_saved_data['label']) ? $custom_field_saved_data['label'] : '';
        ?>
        <div class="jobsearch-elem-heading">
            <h2><?php echo esc_html($heading_field_label) ?></h2>
        </div>
        <?php
        $html .= ob_get_clean();

        if ($field_for_wuser == 'non_reg' && is_user_logged_in()) {
            $html = '';
        } else if ($field_for_wuser == 'for_reg' && !is_user_logged_in()) {
            $html = '';
        }

        return $html;
    }

    static function jobsearch_dashboard_custom_fields_load_callback($post_id, $custom_field_entity) {
        // load all saved fields
        $field_db_slug = "jobsearch_custom_field_" . $custom_field_entity;
        $custom_all_fields_saved_data = get_option($field_db_slug);
        $count_node = time();
        $all_fields_name_str = '';
        if (is_array($custom_all_fields_saved_data) && sizeof($custom_all_fields_saved_data) > 0) {
            $field_names_counter = 0;
            $fields_prefix = '';
            $output = '
            <div class="jobsearch-employer-box-section">
            <div class="jobsearch-profile-title"><h2>' . esc_html__('Other Information', 'wp-jobsearch') . '</h2></div>
            <ul class="jobsearch-row jobsearch-employer-profile-form">';
            foreach ($custom_all_fields_saved_data as $f_key => $custom_field_saved_data) {

                if (isset($custom_field_saved_data['type']) && $custom_field_saved_data['type'] == "heading") {
                    $output .= apply_filters('jobsearch_dashboard_custom_field_heading_load', '', $post_id, $custom_field_saved_data, $fields_prefix);
                } elseif (isset($custom_field_saved_data['type']) && $custom_field_saved_data['type'] == "text") {
                    $output .= apply_filters('jobsearch_dashboard_custom_field_text_load', '', $post_id, $custom_field_saved_data, $fields_prefix);
                } else if (isset($custom_field_saved_data['type']) && $custom_field_saved_data['type'] == "email") {
                    $output .= apply_filters('jobsearch_dashboard_custom_field_email_load', '', $post_id, $custom_field_saved_data, $fields_prefix);
                } else if (isset($custom_field_saved_data['type']) && $custom_field_saved_data['type'] == "textarea") {
                    $output .= apply_filters('jobsearch_dashboard_custom_field_textarea_load', '', $post_id, $custom_field_saved_data, $fields_prefix);
                } else if (isset($custom_field_saved_data['type']) && $custom_field_saved_data['type'] == "date") {
                    $output .= apply_filters('jobsearch_dashboard_custom_field_date_load', '', $post_id, $custom_field_saved_data, $fields_prefix);
                } else if (isset($custom_field_saved_data['type']) && $custom_field_saved_data['type'] == "number") {
                    $output .= apply_filters('jobsearch_dashboard_custom_field_number_load', '', $post_id, $custom_field_saved_data, $fields_prefix);
                } else if (isset($custom_field_saved_data['type']) && $custom_field_saved_data['type'] == "range") {
                    $output .= apply_filters('jobsearch_dashboard_custom_field_range_load', '', $post_id, $custom_field_saved_data, $fields_prefix);
                } else if (isset($custom_field_saved_data['type']) && $custom_field_saved_data['type'] == "dropdown") {
                    $output .= apply_filters('jobsearch_dashboard_custom_field_dropdown_load', '', $post_id, $custom_field_saved_data, $fields_prefix);
                }
            }

            $output .= '
            </ul>
            </div>';
            $output .= apply_filters('jobsearch_dashboard_custom_fields_after', '', $post_id, $custom_field_entity);
            echo force_balance_tags($output);
        }
    }

    static function jobsearch_dashboard_custom_field_text_load_callback($html, $post_id, $custom_field_saved_data, $fields_prefix) {

        global $sitepress;

        ob_start();
        $field_for_wuser = isset($custom_field_saved_data['non_reg_user']) ? $custom_field_saved_data['non_reg_user'] : '';
        $text_field_label = isset($custom_field_saved_data['label']) ? $custom_field_saved_data['label'] : '';
        $text_field_name = isset($custom_field_saved_data['name']) ? $fields_prefix . $custom_field_saved_data['name'] : '';
        $text_field_classes = isset($custom_field_saved_data['classes']) ? $custom_field_saved_data['classes'] : '';
        $text_field_placeholder = isset($custom_field_saved_data['placeholder']) ? $custom_field_saved_data['placeholder'] : '';
        $text_field_required = isset($custom_field_saved_data['required']) ? $custom_field_saved_data['required'] : '';
        $text_field_required_str = '';
        if ($text_field_required == 'yes') {
            $text_field_required_str = 'required="required"';
            $text_field_label = $text_field_label . ' *';
        }
        // get db value if saved
        $text_field_name_db_val = get_post_meta($post_id, $text_field_name, true);
        $lang_code = '';
        if (function_exists('icl_object_id')) {
            $lang_code = $sitepress->get_current_language();
        }
        ?>
        <li class="jobsearch-column-6">
            <label><?php echo apply_filters('wpml_translate_single_string', $text_field_label, 'Custom Fields', 'Text Field Label - ' . $text_field_label, $lang_code) ?></label>
            <input type="text" name="<?php echo esc_html($text_field_name) ?>" class="<?php echo esc_html($text_field_classes) ?>" placeholder="<?php echo apply_filters('wpml_translate_single_string', $text_field_placeholder, 'Custom Fields', 'Text Field Placeholder - ' . $text_field_placeholder, $lang_code) ?>" <?php echo force_balance_tags($text_field_required_str) ?> value="<?php echo esc_html($text_field_name_db_val) ?>" />
        </li>
        <?php
        $html .= ob_get_clean();

        if ($field_for_wuser == 'non_reg' && is_user_logged_in()) {
            $html = '';
        } else if ($field_for_wuser == 'for_reg' && !is_user_logged_in()) {
            $html = '';
        }

        return $html;
    }

    static function jobsearch_dashboard_custom_field_email_load_callback($html, $post_id, $custom_field_saved_data, $fields_prefix) {

        global $sitepress;

        ob_start();
        $field_for_wuser = isset($custom_field_saved_data['non_reg_user']) ? $custom_field_saved_data['non_reg_user'] : '';
        $email_field_label = isset($custom_field_saved_data['label']) ? $custom_field_saved_data['label'] : '';
        $email_field_name = isset($custom_field_saved_data['name']) ? $fields_prefix . $custom_field_saved_data['name'] : '';
        $email_field_classes = isset($custom_field_saved_data['classes']) ? $custom_field_saved_data['classes'] : '';
        $email_field_placeholder = isset($custom_field_saved_data['placeholder']) ? $custom_field_saved_data['placeholder'] : '';
        $email_field_required = isset($custom_field_saved_data['required']) ? $custom_field_saved_data['required'] : '';
        $email_field_required_str = '';
        if ($email_field_required == 'yes') {
            $email_field_required_str = 'required="required"';
            $email_field_label = $email_field_label . ' *';
        }
        // get db value if saved
        $email_field_name_db_val = get_post_meta($post_id, $email_field_name, true);

        $lang_code = '';
        if (function_exists('icl_object_id')) {
            $lang_code = $sitepress->get_current_language();
        }
        ?>
        <li class="jobsearch-column-6">
            <label><?php echo apply_filters('wpml_translate_single_string', $email_field_label, 'Custom Fields', 'Email Field Label - ' . $email_field_label, $lang_code) ?></label>
            <input type="email" name="<?php echo esc_html($email_field_name) ?>" class="<?php echo esc_html($email_field_classes) ?>" placeholder="<?php echo apply_filters('wpml_translate_single_string', $email_field_placeholder, 'Custom Fields', 'Email Field Placeholder - ' . $email_field_placeholder, $lang_code) ?>" <?php echo force_balance_tags($email_field_required_str) ?> value="<?php echo esc_html($email_field_name_db_val) ?>" />
        </li>
        <?php
        $html .= ob_get_clean();

        if ($field_for_wuser == 'non_reg' && is_user_logged_in()) {
            $html = '';
        } else if ($field_for_wuser == 'for_reg' && !is_user_logged_in()) {
            $html = '';
        }

        return $html;
    }

    static function jobsearch_dashboard_custom_field_number_load_callback($html, $post_id, $custom_field_saved_data, $fields_prefix) {

        global $sitepress;

        ob_start();
        $field_for_wuser = isset($custom_field_saved_data['non_reg_user']) ? $custom_field_saved_data['non_reg_user'] : '';
        $number_field_label = isset($custom_field_saved_data['label']) ? $custom_field_saved_data['label'] : '';
        $number_field_name = isset($custom_field_saved_data['name']) ? $fields_prefix . $custom_field_saved_data['name'] : '';
        $number_field_classes = isset($custom_field_saved_data['classes']) ? $custom_field_saved_data['classes'] : '';
        $number_field_placeholder = isset($custom_field_saved_data['placeholder']) ? $custom_field_saved_data['placeholder'] : '';
        $number_field_required = isset($custom_field_saved_data['required']) ? $custom_field_saved_data['required'] : '';
        $number_field_required_str = '';
        if ($number_field_required == 'yes') {
            $number_field_required_str = 'required="required"';
            $number_field_label = $number_field_label . ' *';
        }
        // get db value if saved
        $number_field_name_db_val = get_post_meta($post_id, $number_field_name, true);

        $lang_code = '';
        if (function_exists('icl_object_id')) {
            $lang_code = $sitepress->get_current_language();
        }
        ?>
        <li class="jobsearch-column-6">
            <label><?php echo apply_filters('wpml_translate_single_string', $number_field_label, 'Custom Fields', 'Number Field Label - ' . $number_field_label, $lang_code) ?></label>
            <input type="number" name="<?php echo esc_html($number_field_name) ?>" class="<?php echo esc_html($number_field_classes) ?>" placeholder="<?php echo apply_filters('wpml_translate_single_string', $number_field_placeholder, 'Custom Fields', 'Number Field Placeholder - ' . $number_field_placeholder, $lang_code) ?>" <?php echo force_balance_tags($number_field_required_str) ?> value="<?php echo esc_html($number_field_name_db_val) ?>" />
        </li>
        <?php
        $html .= ob_get_clean();

        if ($field_for_wuser == 'non_reg' && is_user_logged_in()) {
            $html = '';
        } else if ($field_for_wuser == 'for_reg' && !is_user_logged_in()) {
            $html = '';
        }

        return $html;
    }

    static function jobsearch_dashboard_custom_field_date_load_callback($html, $post_id, $custom_field_saved_data, $fields_prefix) {

        global $sitepress;

        ob_start();
        $field_rand_id = rand(454, 999999);
        $field_for_wuser = isset($custom_field_saved_data['non_reg_user']) ? $custom_field_saved_data['non_reg_user'] : '';
        $date_field_label = isset($custom_field_saved_data['label']) ? $custom_field_saved_data['label'] : '';
        $date_field_name = isset($custom_field_saved_data['name']) ? $fields_prefix . $custom_field_saved_data['name'] : '';
        $date_field_classes = isset($custom_field_saved_data['classes']) ? $custom_field_saved_data['classes'] : '';
        $date_field_placeholder = isset($custom_field_saved_data['placeholder']) ? $custom_field_saved_data['placeholder'] : '';
        $date_field_required = isset($custom_field_saved_data['required']) ? $custom_field_saved_data['required'] : '';
        $date_field_date_format = isset($custom_field_saved_data['date-format']) ? $custom_field_saved_data['date-format'] : 'd-m-Y';
        $date_field_required_str = '';
        if ($date_field_required == 'yes') {
            $date_field_required_str = 'required="required"';
            $date_field_label = $date_field_label . ' *';
        }
        // get db value if saved
        $date_field_name_db_val = get_post_meta($post_id, $date_field_name, true);
        if ($date_field_name_db_val != '') {
            $date_field_name_db_val = date($date_field_date_format, $date_field_name_db_val);
        }

        $lang_code = '';
        if (function_exists('icl_object_id')) {
            $lang_code = $sitepress->get_current_language();
        }
        ?>
        <li class="jobsearch-column-6">
            <label><?php echo apply_filters('wpml_translate_single_string', $date_field_label, 'Custom Fields', 'Date Field Label - ' . $date_field_label, $lang_code) ?></label>
            <input type="text" id="<?php echo esc_html($date_field_name . $field_rand_id) ?>" name="<?php echo esc_html($date_field_name) ?>" class="<?php echo esc_html($date_field_classes) ?>" placeholder="<?php echo apply_filters('wpml_translate_single_string', $date_field_placeholder, 'Custom Fields', 'Date Field Placeholder - ' . $date_field_placeholder, $lang_code) ?>" <?php echo force_balance_tags($date_field_required_str) ?> value="<?php echo esc_html($date_field_name_db_val) ?>" />
        </li>
        <script>
            jQuery(document).ready(function () {
                jQuery('#<?php echo esc_html($date_field_name . $field_rand_id) ?>').datetimepicker({
                    format: '<?php echo esc_html($date_field_date_format) ?>'
                });
            });
        </script>
        <?php
        $html .= ob_get_clean();

        if ($field_for_wuser == 'non_reg' && is_user_logged_in()) {
            $html = '';
        } else if ($field_for_wuser == 'for_reg' && !is_user_logged_in()) {
            $html = '';
        }

        return $html;
    }

    static function jobsearch_dashboard_custom_field_range_load_callback($html, $post_id, $custom_field_saved_data, $fields_prefix) {
        ob_start();
        global $sitepress;
        $field_for_wuser = isset($custom_field_saved_data['non_reg_user']) ? $custom_field_saved_data['non_reg_user'] : '';
        $range_field_label = isset($custom_field_saved_data['label']) ? $custom_field_saved_data['label'] : '';
        $range_field_name = isset($custom_field_saved_data['name']) ? $fields_prefix . $custom_field_saved_data['name'] : '';
        $range_field_classes = isset($custom_field_saved_data['classes']) ? $custom_field_saved_data['classes'] : '';
        $range_field_placeholder = isset($custom_field_saved_data['placeholder']) ? $custom_field_saved_data['placeholder'] : '';
        $range_field_required = isset($custom_field_saved_data['required']) ? $custom_field_saved_data['required'] : '';
        $range_field_min = isset($custom_field_saved_data['min']) ? $custom_field_saved_data['min'] : '0';
        $range_field_laps = isset($custom_field_saved_data['laps']) ? $custom_field_saved_data['laps'] : '20';
        $range_field_interval = isset($custom_field_saved_data['interval']) ? $custom_field_saved_data['interval'] : '10000';
        $rand_id = rand(123, 123467);
        $range_field_required_str = '';
        if ($range_field_required == 'yes') {
            $range_field_required_str = 'required="required"';
            $range_field_label = $range_field_label . ' *';
        }
        // get db value if saved
        $range_field_name_db_val = get_post_meta($post_id, $range_field_name, true);
        wp_enqueue_style('jquery-ui');
        wp_enqueue_script('jquery-ui');
        $range_field_max = $range_field_min;
        $i = 0;
        while ($range_field_laps > $i) {
            $range_field_max = $range_field_max + $range_field_interval;
            $i++;
        }
        ?>
        <script>
            jQuery(document).ready(function () {
                jQuery("#slider-range<?php echo esc_html($range_field_name . $rand_id) ?>").slider({
                    range: "max",
                    min: <?php echo absint($range_field_min); ?>,
                    max: <?php echo absint($range_field_max); ?>,
                    value: <?php echo absint($range_field_name_db_val); ?>,
                    slide: function (event, ui) {
                        jQuery("#<?php echo esc_html($range_field_name . $rand_id) ?>").val(ui.value);
                    }
                });
                jQuery("#<?php echo esc_html($range_field_name . $rand_id) ?>").val(jQuery("#slider-range<?php echo esc_html($range_field_name . $rand_id) ?>").slider("value"));
            });
        </script>
        <li class="jobsearch-column-6">
            <?php
            $lang_code = '';
            if (function_exists('icl_object_id')) {
                $lang_code = $sitepress->get_current_language();
            }
            ?>
            <label><?php echo apply_filters('wpml_translate_single_string', $range_field_label, 'Custom Fields', 'Range Field Label - ' . $range_field_label, $lang_code) ?></label>
            <div class="range-field-container">
                <input type="text" id="<?php echo esc_html($range_field_name . $rand_id) ?>" name="<?php echo esc_html($range_field_name) ?>" value="" readonly style="border:0; color:#f6931f; font-weight:bold;" />
                <div id="slider-range<?php echo esc_html($range_field_name . $rand_id) ?>"></div>
            </div>
        </li>
        <?php
        $html .= ob_get_clean();

        if ($field_for_wuser == 'non_reg' && is_user_logged_in()) {
            $html = '';
        } else if ($field_for_wuser == 'for_reg' && !is_user_logged_in()) {
            $html = '';
        }

        return $html;
    }

    static function jobsearch_dashboard_custom_field_dropdown_load_callback($html, $post_id, $custom_field_saved_data, $fields_prefix) {

        global $sitepress;

        ob_start();
        $field_for_wuser = isset($custom_field_saved_data['non_reg_user']) ? $custom_field_saved_data['non_reg_user'] : '';
        $dropdown_field_label = isset($custom_field_saved_data['label']) ? $custom_field_saved_data['label'] : '';
        $dropdown_field_name = isset($custom_field_saved_data['name']) ? $fields_prefix . $custom_field_saved_data['name'] : '';
        $dropdown_field_classes = isset($custom_field_saved_data['classes']) ? $custom_field_saved_data['classes'] : '';
        $dropdown_field_placeholder = isset($custom_field_saved_data['placeholder']) ? $custom_field_saved_data['placeholder'] : '';
        $dropdown_field_required = isset($custom_field_saved_data['required']) ? $custom_field_saved_data['required'] : '';
        $field_post_multi = isset($custom_field_saved_data['post-multi']) ? $custom_field_saved_data['post-multi'] : '';
        $dropdown_field_options = isset($custom_field_saved_data['options']) ? $custom_field_saved_data['options'] : '';
        $dropdown_field_required_str = '';
        if ($dropdown_field_required == 'yes') {
            $dropdown_field_required_str = 'required="required"';
            $dropdown_field_label = $dropdown_field_label . ' *';
        }
        // get db value if saved
        $dropdown_field_name_db_val = get_post_meta($post_id, $dropdown_field_name, true);
        // creat options string
        $dropdown_field_options_str = '';
        if ($dropdown_field_required == 'yes') {
            $dropdown_field_options_str = '<option value="">' . esc_html__('Select Value', 'wp-jobsearch') . '</option>';
        }
        if (isset($dropdown_field_options['value']) && count($dropdown_field_options['value']) > 0) {
            $option_counter = 0;
            foreach ($dropdown_field_options['value'] as $option) {
                if ($option != '') {
                    $option = ltrim(rtrim($option));
                    if ($dropdown_field_options['label'][$option_counter] != '' && str_replace(" ", "-", $option) != '') {
                        $option_val = strtolower(str_replace(" ", "-", $option));
                        $option_label = $dropdown_field_options['label'][$option_counter];

                        if (is_array($dropdown_field_name_db_val)) {
                            $option_selected = in_array($option_val, $dropdown_field_name_db_val) ? ' selected="selected"' : '';
                        } else {
                            $option_selected = $dropdown_field_name_db_val == $option_val ? ' selected="selected"' : '';
                        }
                        $lang_code = '';
                        if (function_exists('icl_object_id')) {
                            $lang_code = $sitepress->get_current_language();
                        }
                        do_action('wpml_register_single_string', 'Custom Fields', 'Dropdown Option Label - ' . $option_label, $option_label);

                        $dropdown_field_options_str .= '<option ' . force_balance_tags($option_selected) . ' value="' . esc_html($option_val) . '">' . apply_filters('wpml_translate_single_string', $option_label, 'Custom Fields', 'Dropdown Option Label - ' . $option_label, $lang_code) . '</option>';
                    }
                }
                $option_counter ++;
            }
        }

        $lang_code = '';
        if (function_exists('icl_object_id')) {
            $lang_code = $sitepress->get_current_language();
        }
        ?>
        <li class="jobsearch-column-6">
            <label><?php echo apply_filters('wpml_translate_single_string', $dropdown_field_label, 'Custom Fields', 'Dropdown Field Label - ' . $dropdown_field_label, $lang_code) ?></label>
            <?php
            if ($dropdown_field_options_str != '') {
                ?>
                <div class="jobsearch-profile-select">
                    <select <?php echo ($field_post_multi == 'yes' ? 'multiple="multiple" ' : '') ?>name="<?php echo esc_html($dropdown_field_name) ?><?php echo ($field_post_multi == 'yes' ? '[]' : '') ?>" class="<?php echo esc_html($dropdown_field_classes) ?> selectize-select" <?php echo force_balance_tags($dropdown_field_required_str) ?>>
                        <?php
                        echo force_balance_tags($dropdown_field_options_str);
                        ?>
                    </select>
                </div>
            <?php } else {
                ?>
                <span><?php echo esc_html__('Field did not configure properly', 'wp-jobsearch'); ?></span>
                <?php
            }
            ?>
        </li>
        <?php
        $html .= ob_get_clean();

        if ($field_for_wuser == 'non_reg' && is_user_logged_in()) {
            $html = '';
        } else if ($field_for_wuser == 'for_reg' && !is_user_logged_in()) {
            $html = '';
        }

        return $html;
    }

    static function jobsearch_dashboard_custom_field_textarea_load_callback($html, $post_id, $custom_field_saved_data, $fields_prefix) {

        global $sitepress;

        ob_start();
        $field_for_wuser = isset($custom_field_saved_data['non_reg_user']) ? $custom_field_saved_data['non_reg_user'] : '';
        $textarea_field_label = isset($custom_field_saved_data['label']) ? $custom_field_saved_data['label'] : '';
        $textarea_field_name = isset($custom_field_saved_data['name']) ? $fields_prefix . $custom_field_saved_data['name'] : '';
        $textarea_field_classes = isset($custom_field_saved_data['classes']) ? $custom_field_saved_data['classes'] : '';
        $textarea_field_placeholder = isset($custom_field_saved_data['placeholder']) ? $custom_field_saved_data['placeholder'] : '';
        $textarea_field_required = isset($custom_field_saved_data['required']) ? $custom_field_saved_data['required'] : '';
        $textarea_field_required_str = '';
        if ($textarea_field_required == 'yes') {
            $textarea_field_required_str = 'required="required"';
            $textarea_field_label = $textarea_field_label . ' *';
        }
        // get db value if saved
        $textarea_field_name_db_val = get_post_meta($post_id, $textarea_field_name, true);

        $lang_code = '';
        if (function_exists('icl_object_id')) {
            $lang_code = $sitepress->get_current_language();
        }
        ?>
        <li class="jobsearch-column-12">
            <label><?php echo apply_filters('wpml_translate_single_string', $textarea_field_label, 'Custom Fields', 'Textarea Field Label - ' . $textarea_field_label, $lang_code) ?></label>
            <?php
            $wped_settings = array(
                'media_buttons' => false,
                'editor_class' => $textarea_field_classes,
                'quicktags' => array('buttons' => 'strong,em,del,ul,ol,li,close'),
                'tinymce' => array(
                    'toolbar1' => 'bold,bullist,numlist,italic,underline,alignleft,aligncenter,alignright,separator,link,unlink,undo,redo',
                    'toolbar2' => '',
                    'toolbar3' => '',
                ),
            );
            wp_editor($textarea_field_name_db_val, $textarea_field_name, $wped_settings);
            ?>
        </li>
        <?php
        $html .= ob_get_clean();

        if ($field_for_wuser == 'non_reg' && is_user_logged_in()) {
            $html = '';
        } else if ($field_for_wuser == 'for_reg' && !is_user_logged_in()) {
            $html = '';
        }

        return $html;
    }

    static function jobsearch_dashboard_custom_field_heading_load_callback($html, $post_id, $custom_field_saved_data, $fields_prefix) {

        global $sitepress;

        ob_start();
        $field_for_wuser = isset($custom_field_saved_data['non_reg_user']) ? $custom_field_saved_data['non_reg_user'] : '';
        $heading_field_label = isset($custom_field_saved_data['label']) ? $custom_field_saved_data['label'] : '';

        $lang_code = '';
        if (function_exists('icl_object_id')) {
            $lang_code = $sitepress->get_current_language();
        }
        ?>
        <div class="jobsearch-dashboard-heading">
            <h2><?php echo apply_filters('wpml_translate_single_string', $heading_field_label, 'Custom Fields', 'Heading Field Label - ' . $heading_field_label, $lang_code) ?></h2>
        </div>
        <?php
        $html .= ob_get_clean();

        if ($field_for_wuser == 'non_reg' && is_user_logged_in()) {
            $html = '';
        } else if ($field_for_wuser == 'for_reg' && !is_user_logged_in()) {
            $html = '';
        }

        return $html;
    }

    static function jobsearch_form_custom_fields_load_callback($post_id, $custom_field_entity) {
        // load all saved fields
        $field_db_slug = "jobsearch_custom_field_" . $custom_field_entity;
        $custom_all_fields_saved_data = get_option($field_db_slug);
        $count_node = time();
        $all_fields_name_str = '';
        if (is_array($custom_all_fields_saved_data) && sizeof($custom_all_fields_saved_data) > 0) {
            $field_names_counter = 0;
            $fields_prefix = '';
            $output = '';
            foreach ($custom_all_fields_saved_data as $f_key => $custom_field_saved_data) {

                if (isset($custom_field_saved_data['type']) && $custom_field_saved_data['type'] == "heading") {
                    $output .= apply_filters('jobsearch_form_custom_field_heading_load', '', $post_id, $custom_field_saved_data, $fields_prefix);
                } elseif (isset($custom_field_saved_data['type']) && $custom_field_saved_data['type'] == "text") {
                    $output .= apply_filters('jobsearch_form_custom_field_text_load', '', $post_id, $custom_field_saved_data, $fields_prefix);
                } else if (isset($custom_field_saved_data['type']) && $custom_field_saved_data['type'] == "email") {
                    $output .= apply_filters('jobsearch_form_custom_field_email_load', '', $post_id, $custom_field_saved_data, $fields_prefix);
                } else if (isset($custom_field_saved_data['type']) && $custom_field_saved_data['type'] == "textarea") {
                    $output .= apply_filters('jobsearch_form_custom_field_textarea_load', '', $post_id, $custom_field_saved_data, $fields_prefix);
                } else if (isset($custom_field_saved_data['type']) && $custom_field_saved_data['type'] == "date") {
                    $output .= apply_filters('jobsearch_form_custom_field_date_load', '', $post_id, $custom_field_saved_data, $fields_prefix);
                } else if (isset($custom_field_saved_data['type']) && $custom_field_saved_data['type'] == "number") {
                    $output .= apply_filters('jobsearch_form_custom_field_number_load', '', $post_id, $custom_field_saved_data, $fields_prefix);
                } else if (isset($custom_field_saved_data['type']) && $custom_field_saved_data['type'] == "range") {
                    $output .= apply_filters('jobsearch_form_custom_field_range_load', '', $post_id, $custom_field_saved_data, $fields_prefix);
                } else if (isset($custom_field_saved_data['type']) && $custom_field_saved_data['type'] == "dropdown") {
                    $output .= apply_filters('jobsearch_form_custom_field_dropdown_load', '', $post_id, $custom_field_saved_data, $fields_prefix);
                }
            }

            echo ($output);
        }
    }

    static function jobsearch_signup_custom_fields_load_callback($post_id, $custom_field_entity, $f_display = 'block') {
        global $jobsearch_plugin_options;

        $reg_custom_fields = isset($jobsearch_plugin_options['signup_custom_fields']) ? $jobsearch_plugin_options['signup_custom_fields'] : '';

        if ($reg_custom_fields == 'on') {

            if ($custom_field_entity == 'employer') {
                $selected_fields = isset($jobsearch_plugin_options['employer_custom_fields']) ? $jobsearch_plugin_options['employer_custom_fields'] : '';
                $con_class = 'employer-cus-field';
            } else {
                $selected_fields = isset($jobsearch_plugin_options['candidate_custom_fields']) ? $jobsearch_plugin_options['candidate_custom_fields'] : '';
                $con_class = 'candidate-cus-field';
            }

            // load all saved fields
            $field_db_slug = "jobsearch_custom_field_" . $custom_field_entity;
            $custom_all_fields_saved_data = get_option($field_db_slug);
            $count_node = time();
            $all_fields_name_str = '';
            if (is_array($custom_all_fields_saved_data) && sizeof($custom_all_fields_saved_data) > 0 && !empty($selected_fields)) {
                $field_names_counter = 0;
                $fields_prefix = '';
                $output = '';
                foreach ($custom_all_fields_saved_data as $f_key => $custom_field_saved_data) {
                    $field_name = isset($custom_field_saved_data['name']) ? $custom_field_saved_data['name'] : '';
                    if (isset($custom_field_saved_data['type']) && $custom_field_saved_data['type'] == "heading" && ($field_name != '' && in_array($field_name, $selected_fields))) {
                        $output .= apply_filters('jobsearch_form_custom_field_heading_load', '', $post_id, $custom_field_saved_data, $fields_prefix, $con_class, $f_display);
                    } elseif (isset($custom_field_saved_data['type']) && $custom_field_saved_data['type'] == "text" && ($field_name != '' && in_array($field_name, $selected_fields))) {
                        $output .= apply_filters('jobsearch_form_custom_field_text_load', '', $post_id, $custom_field_saved_data, $fields_prefix, $con_class, $f_display);
                    } else if (isset($custom_field_saved_data['type']) && $custom_field_saved_data['type'] == "email" && ($field_name != '' && in_array($field_name, $selected_fields))) {
                        $output .= apply_filters('jobsearch_form_custom_field_email_load', '', $post_id, $custom_field_saved_data, $fields_prefix, $con_class, $f_display);
                    } else if (isset($custom_field_saved_data['type']) && $custom_field_saved_data['type'] == "textarea" && ($field_name != '' && in_array($field_name, $selected_fields))) {
                        $output .= apply_filters('jobsearch_form_custom_field_textarea_load', '', $post_id, $custom_field_saved_data, $fields_prefix, $con_class, $f_display);
                    } else if (isset($custom_field_saved_data['type']) && $custom_field_saved_data['type'] == "date" && ($field_name != '' && in_array($field_name, $selected_fields))) {
                        $output .= apply_filters('jobsearch_form_custom_field_date_load', '', $post_id, $custom_field_saved_data, $fields_prefix, $con_class, $f_display);
                    } else if (isset($custom_field_saved_data['type']) && $custom_field_saved_data['type'] == "number" && ($field_name != '' && in_array($field_name, $selected_fields))) {
                        $output .= apply_filters('jobsearch_form_custom_field_number_load', '', $post_id, $custom_field_saved_data, $fields_prefix, $con_class, $f_display);
                    } else if (isset($custom_field_saved_data['type']) && $custom_field_saved_data['type'] == "range" && ($field_name != '' && in_array($field_name, $selected_fields))) {
                        $output .= apply_filters('jobsearch_form_custom_field_range_load', '', $post_id, $custom_field_saved_data, $fields_prefix, $con_class, $f_display);
                    } else if (isset($custom_field_saved_data['type']) && $custom_field_saved_data['type'] == "dropdown" && ($field_name != '' && in_array($field_name, $selected_fields))) {
                        $output .= apply_filters('jobsearch_form_custom_field_dropdown_load', '', $post_id, $custom_field_saved_data, $fields_prefix, $con_class, $f_display);
                    }
                }

                echo ($output);
            }
        }
    }

    public function register_custom_fields_error($post_id, $custom_field_entity) {
        global $jobsearch_plugin_options;

        $reg_custom_fields = isset($jobsearch_plugin_options['signup_custom_fields']) ? $jobsearch_plugin_options['signup_custom_fields'] : '';

        if ($reg_custom_fields == 'on') {

            if ($custom_field_entity == 'employer') {
                $selected_fields = isset($jobsearch_plugin_options['employer_custom_fields']) ? $jobsearch_plugin_options['employer_custom_fields'] : '';
                $con_class = 'employer-cus-field';
            } else {
                $selected_fields = isset($jobsearch_plugin_options['candidate_custom_fields']) ? $jobsearch_plugin_options['candidate_custom_fields'] : '';
                $con_class = 'candidate-cus-field';
            }

            // load all saved fields
            $field_db_slug = "jobsearch_custom_field_" . $custom_field_entity;
            $custom_all_fields_saved_data = get_option($field_db_slug);
            $count_node = time();
            $all_fields_name_str = '';
            if (is_array($custom_all_fields_saved_data) && sizeof($custom_all_fields_saved_data) > 0 && !empty($selected_fields)) {
                $field_names_counter = 0;
                $fields_prefix = '';
                $output = '';
                foreach ($custom_all_fields_saved_data as $f_key => $custom_field_saved_data) {
                    $field_label = isset($custom_field_saved_data['label']) ? $custom_field_saved_data['label'] : '';
                    $field_name = isset($custom_field_saved_data['name']) ? $custom_field_saved_data['name'] : '';
                    $field_required = isset($custom_field_saved_data['required']) ? $custom_field_saved_data['required'] : '';

                    if ($field_name != '' && in_array($field_name, $selected_fields)) {
                        if ($field_required == 'yes' && isset($_POST[$field_name]) && $_POST[$field_name] == '') {
                            echo json_encode(array('error' => true, 'message' => '<div class="alert alert-danger"><i class="fa fa-times"></i> ' . sprintf(__('%s value should not be blank.', 'wp-jobsearch'), $field_label) . '</div>'));
                            die();
                        }
                    }
                }
            }
        }
    }

    static function jobsearch_form_custom_field_text_load_callback($html, $post_id, $custom_field_saved_data, $fields_prefix, $con_class = '', $f_display = '') {

        global $sitepress;

        ob_start();
        $field_for_wuser = isset($custom_field_saved_data['non_reg_user']) ? $custom_field_saved_data['non_reg_user'] : '';
        $text_field_label = isset($custom_field_saved_data['label']) ? $custom_field_saved_data['label'] : '';
        $text_field_name = isset($custom_field_saved_data['name']) ? $fields_prefix . $custom_field_saved_data['name'] : '';
        $text_field_classes = isset($custom_field_saved_data['classes']) ? $custom_field_saved_data['classes'] : '';
        $text_field_placeholder = isset($custom_field_saved_data['placeholder']) ? $custom_field_saved_data['placeholder'] : '';
        $text_field_required = isset($custom_field_saved_data['required']) ? $custom_field_saved_data['required'] : '';
        $text_field_required_str = '';
        if ($text_field_required == 'yes') {
            $text_field_required_str = 'required="required"';
        }

        $con_clas_attr = '';
        if ($con_class != '') {
            $con_clas_attr = ' class="' . $con_class . '"';
        }

        $con_f_style = '';
        if ($f_display != '') {
            $con_f_style = ' style="display: ' . $f_display . ';"';
        }

        // get db value if saved
        $text_field_name_db_val = get_post_meta($post_id, $text_field_name, true);

        $lang_code = '';
        if (function_exists('icl_object_id')) {
            $lang_code = $sitepress->get_current_language();
        }
        do_action('wpml_register_single_string', 'Custom Fields', 'Text Field Label - ' . $text_field_label, $text_field_label);
        ?>
        <li<?php echo ($con_clas_attr) ?><?php echo ($con_f_style) ?>>
            <label><?php echo apply_filters('wpml_translate_single_string', $text_field_label, 'Custom Fields', 'Text Field Label - ' . $text_field_label, $lang_code) ?></label>
            <input type="text" name="<?php echo esc_html($text_field_name) ?>" class="<?php echo esc_html($text_field_classes) ?>" placeholder="<?php echo esc_html($text_field_placeholder) ?>" <?php echo force_balance_tags($text_field_required_str) ?> value="<?php echo esc_html($text_field_name_db_val) ?>" />
        </li>
        <?php
        $html .= ob_get_clean();

        if ($field_for_wuser == 'non_reg' && is_user_logged_in()) {
            $html = '';
        } else if ($field_for_wuser == 'for_reg' && !is_user_logged_in()) {
            $html = '';
        }

        return $html;
    }

    static function jobsearch_form_custom_field_email_load_callback($html, $post_id, $custom_field_saved_data, $fields_prefix, $con_class = '', $f_display = '') {

        global $sitepress;

        ob_start();
        $field_for_wuser = isset($custom_field_saved_data['non_reg_user']) ? $custom_field_saved_data['non_reg_user'] : '';
        $email_field_label = isset($custom_field_saved_data['label']) ? $custom_field_saved_data['label'] : '';
        $email_field_name = isset($custom_field_saved_data['name']) ? $fields_prefix . $custom_field_saved_data['name'] : '';
        $email_field_classes = isset($custom_field_saved_data['classes']) ? $custom_field_saved_data['classes'] : '';
        $email_field_placeholder = isset($custom_field_saved_data['placeholder']) ? $custom_field_saved_data['placeholder'] : '';
        $email_field_required = isset($custom_field_saved_data['required']) ? $custom_field_saved_data['required'] : '';
        $email_field_required_str = '';
        if ($email_field_required == 'yes') {
            $email_field_required_str = 'required="required"';
        }

        $con_clas_attr = '';
        if ($con_class != '') {
            $con_clas_attr = ' class="' . $con_class . '"';
        }

        $con_f_style = '';
        if ($f_display != '') {
            $con_f_style = ' style="display: ' . $f_display . ';"';
        }

        // get db value if saved
        $email_field_name_db_val = get_post_meta($post_id, $email_field_name, true);

        $lang_code = '';
        if (function_exists('icl_object_id')) {
            $lang_code = $sitepress->get_current_language();
        }
        do_action('wpml_register_single_string', 'Custom Fields', 'Email Field Label - ' . $email_field_label, $email_field_label);
        ?>
        <li<?php echo ($con_clas_attr) ?><?php echo ($con_f_style) ?>>
            <label><?php echo apply_filters('wpml_translate_single_string', $email_field_label, 'Custom Fields', 'Email Field Label - ' . $email_field_label, $lang_code) ?></label>
            <input type="email" name="<?php echo esc_html($email_field_name) ?>" class="<?php echo esc_html($email_field_classes) ?>" placeholder="<?php echo esc_html($email_field_placeholder) ?>" <?php echo force_balance_tags($email_field_required_str) ?> value="<?php echo esc_html($email_field_name_db_val) ?>" />
        </li>
        <?php
        $html .= ob_get_clean();

        if ($field_for_wuser == 'non_reg' && is_user_logged_in()) {
            $html = '';
        } else if ($field_for_wuser == 'for_reg' && !is_user_logged_in()) {
            $html = '';
        }

        return $html;
    }

    static function jobsearch_form_custom_field_number_load_callback($html, $post_id, $custom_field_saved_data, $fields_prefix, $con_class = '', $f_display = '') {

        global $sitepress;

        ob_start();
        $field_for_wuser = isset($custom_field_saved_data['non_reg_user']) ? $custom_field_saved_data['non_reg_user'] : '';
        $number_field_label = isset($custom_field_saved_data['label']) ? $custom_field_saved_data['label'] : '';
        $number_field_name = isset($custom_field_saved_data['name']) ? $fields_prefix . $custom_field_saved_data['name'] : '';
        $number_field_classes = isset($custom_field_saved_data['classes']) ? $custom_field_saved_data['classes'] : '';
        $number_field_placeholder = isset($custom_field_saved_data['placeholder']) ? $custom_field_saved_data['placeholder'] : '';
        $number_field_required = isset($custom_field_saved_data['required']) ? $custom_field_saved_data['required'] : '';
        $number_field_required_str = '';
        if ($number_field_required == 'yes') {
            $number_field_required_str = 'required="required"';
        }

        $con_clas_attr = '';
        if ($con_class != '') {
            $con_clas_attr = ' class="' . $con_class . '"';
        }

        $con_f_style = '';
        if ($f_display != '') {
            $con_f_style = ' style="display: ' . $f_display . ';"';
        }

        // get db value if saved
        $number_field_name_db_val = get_post_meta($post_id, $number_field_name, true);

        $lang_code = '';
        if (function_exists('icl_object_id')) {
            $lang_code = $sitepress->get_current_language();
        }
        do_action('wpml_register_single_string', 'Custom Fields', 'Number Field Label - ' . $number_field_label, $number_field_label);
        ?>
        <li<?php echo ($con_clas_attr) ?><?php echo ($con_f_style) ?>>
            <label><?php echo apply_filters('wpml_translate_single_string', $number_field_label, 'Custom Fields', 'Number Field Label - ' . $number_field_label, $lang_code) ?></label>
            <input type="number" name="<?php echo esc_html($number_field_name) ?>" class="<?php echo esc_html($number_field_classes) ?>" placeholder="<?php echo esc_html($number_field_placeholder) ?>" <?php echo force_balance_tags($number_field_required_str) ?> value="<?php echo esc_html($number_field_name_db_val) ?>" />
        </li>
        <?php
        $html .= ob_get_clean();

        if ($field_for_wuser == 'non_reg' && is_user_logged_in()) {
            $html = '';
        } else if ($field_for_wuser == 'for_reg' && !is_user_logged_in()) {
            $html = '';
        }

        return $html;
    }

    static function jobsearch_form_custom_field_date_load_callback($html, $post_id, $custom_field_saved_data, $fields_prefix, $con_class = '', $f_display = '') {

        global $sitepress;

        ob_start();
        $field_rand_id = rand(454, 999999);
        $field_for_wuser = isset($custom_field_saved_data['non_reg_user']) ? $custom_field_saved_data['non_reg_user'] : '';
        $date_field_label = isset($custom_field_saved_data['label']) ? $custom_field_saved_data['label'] : '';
        $date_field_name = isset($custom_field_saved_data['name']) ? $fields_prefix . $custom_field_saved_data['name'] : '';
        $date_field_classes = isset($custom_field_saved_data['classes']) ? $custom_field_saved_data['classes'] : '';
        $date_field_placeholder = isset($custom_field_saved_data['placeholder']) ? $custom_field_saved_data['placeholder'] : '';
        $date_field_required = isset($custom_field_saved_data['required']) ? $custom_field_saved_data['required'] : '';
        $date_field_date_format = isset($custom_field_saved_data['date-format']) ? $custom_field_saved_data['date-format'] : 'd-m-Y';
        $date_field_required_str = '';
        if ($date_field_required == 'yes') {
            $date_field_required_str = 'required="required"';
        }

        $con_clas_attr = '';
        if ($con_class != '') {
            $con_clas_attr = ' class="' . $con_class . '"';
        }

        $con_f_style = '';
        if ($f_display != '') {
            $con_f_style = ' style="display: ' . $f_display . ';"';
        }

        // get db value if saved
        $date_field_name_db_val = get_post_meta($post_id, $date_field_name, true);
        if ($date_field_name_db_val != '') {
            $date_field_name_db_val = date($date_field_date_format, $date_field_name_db_val);
        }

        $lang_code = '';
        if (function_exists('icl_object_id')) {
            $lang_code = $sitepress->get_current_language();
        }
        do_action('wpml_register_single_string', 'Custom Fields', 'Date Field Label - ' . $date_field_label, $date_field_label);
        ?>
        <li<?php echo ($con_clas_attr) ?><?php echo ($con_f_style) ?>>
            <label><?php echo apply_filters('wpml_translate_single_string', $date_field_label, 'Custom Fields', 'Date Field Label - ' . $date_field_label, $lang_code) ?></label>
            <input type="text" id="<?php echo esc_html($date_field_name . $field_rand_id) ?>" name="<?php echo esc_html($date_field_name) ?>" class="<?php echo esc_html($date_field_classes) ?>" placeholder="<?php echo esc_html($date_field_placeholder) ?>" <?php echo force_balance_tags($date_field_required_str) ?> value="<?php echo esc_html($date_field_name_db_val) ?>" />
        </li>
        <script>
            jQuery(document).ready(function () {
                jQuery('#<?php echo esc_html($date_field_name . $field_rand_id) ?>').datetimepicker({
                    format: '<?php echo esc_html($date_field_date_format) ?>'
                });
            });
        </script>
        <?php
        $html .= ob_get_clean();

        if ($field_for_wuser == 'non_reg' && is_user_logged_in()) {
            $html = '';
        } else if ($field_for_wuser == 'for_reg' && !is_user_logged_in()) {
            $html = '';
        }

        return $html;
    }

    static function jobsearch_form_custom_field_range_load_callback($html, $post_id, $custom_field_saved_data, $fields_prefix, $con_class = '', $f_display = '') {
        global $sitepress;

        ob_start();
        $field_for_wuser = isset($custom_field_saved_data['non_reg_user']) ? $custom_field_saved_data['non_reg_user'] : '';
        $range_field_label = isset($custom_field_saved_data['label']) ? $custom_field_saved_data['label'] : '';
        $range_field_name = isset($custom_field_saved_data['name']) ? $fields_prefix . $custom_field_saved_data['name'] : '';
        $range_field_classes = isset($custom_field_saved_data['classes']) ? $custom_field_saved_data['classes'] : '';
        $range_field_placeholder = isset($custom_field_saved_data['placeholder']) ? $custom_field_saved_data['placeholder'] : '';
        $range_field_required = isset($custom_field_saved_data['required']) ? $custom_field_saved_data['required'] : '';
        $range_field_min = isset($custom_field_saved_data['min']) ? $custom_field_saved_data['min'] : '0';
        $range_field_laps = isset($custom_field_saved_data['laps']) ? $custom_field_saved_data['laps'] : '20';
        $range_field_interval = isset($custom_field_saved_data['interval']) ? $custom_field_saved_data['interval'] : '10000';
        $rand_id = rand(123, 123467);
        $range_field_required_str = '';
        if ($range_field_required == 'yes') {
            $range_field_required_str = 'required="required"';
        }

        $con_clas_attr = '';
        if ($con_class != '') {
            $con_clas_attr = $con_class;
        }

        $con_f_style = '';
        if ($f_display != '') {
            $con_f_style = ' style="display: ' . $f_display . ';"';
        }

        // get db value if saved
        $range_field_name_db_val = get_post_meta($post_id, $range_field_name, true);
        wp_enqueue_style('jquery-ui');
        wp_enqueue_script('jquery-ui');
        $range_field_max = $range_field_min;
        $i = 0;
        while ($range_field_laps > $i) {
            $range_field_max = $range_field_max + $range_field_interval;
            $i++;
        }

        $lang_code = '';
        if (function_exists('icl_object_id')) {
            $lang_code = $sitepress->get_current_language();
        }
        do_action('wpml_register_single_string', 'Custom Fields', 'Range Field Label - ' . $range_field_label, $range_field_label);
        ?>
        <li class="range-in-user-form <?php echo ($con_clas_attr) ?>"<?php echo ($con_f_style) ?>>
            <script>
                jQuery(document).ready(function () {
                    jQuery("#slider-range<?php echo esc_html($range_field_name . $rand_id) ?>").slider({
                        range: "max",
                        min: <?php echo absint($range_field_min); ?>,
                        max: <?php echo absint($range_field_max); ?>,
                        value: <?php echo absint($range_field_name_db_val); ?>,
                        slide: function (event, ui) {
                            jQuery("#<?php echo esc_html($range_field_name . $rand_id) ?>").val(ui.value);
                        }
                    });
                    jQuery("#<?php echo esc_html($range_field_name . $rand_id) ?>").val(jQuery("#slider-range<?php echo esc_html($range_field_name . $rand_id) ?>").slider("value"));
                });
            </script>
            <label><?php echo apply_filters('wpml_translate_single_string', $range_field_label, 'Custom Fields', 'Range Field Label - ' . $range_field_label, $lang_code) ?></label>
            <input type="text" id="<?php echo esc_html($range_field_name . $rand_id) ?>" name="<?php echo esc_html($range_field_name) ?>" value="" readonly style="border:0; color:#f6931f; font-weight:bold;" />
            <div id="slider-range<?php echo esc_html($range_field_name . $rand_id) ?>"></div>
        </li>
        <?php
        $html .= ob_get_clean();

        if ($field_for_wuser == 'non_reg' && is_user_logged_in()) {
            $html = '';
        } else if ($field_for_wuser == 'for_reg' && !is_user_logged_in()) {
            $html = '';
        }

        return $html;
    }

    static function jobsearch_form_custom_field_dropdown_load_callback($html, $post_id, $custom_field_saved_data, $fields_prefix, $con_class = '', $f_display = '') {

        global $sitepress;

        ob_start();
        $field_for_wuser = isset($custom_field_saved_data['non_reg_user']) ? $custom_field_saved_data['non_reg_user'] : '';
        $dropdown_field_label = isset($custom_field_saved_data['label']) ? $custom_field_saved_data['label'] : '';
        $dropdown_field_name = isset($custom_field_saved_data['name']) ? $fields_prefix . $custom_field_saved_data['name'] : '';
        $dropdown_field_classes = isset($custom_field_saved_data['classes']) ? $custom_field_saved_data['classes'] : '';
        $dropdown_field_placeholder = isset($custom_field_saved_data['placeholder']) ? $custom_field_saved_data['placeholder'] : '';
        $dropdown_field_required = isset($custom_field_saved_data['required']) ? $custom_field_saved_data['required'] : '';
        $dropdown_field_options = isset($custom_field_saved_data['options']) ? $custom_field_saved_data['options'] : '';
        $dropdown_field_required_str = '';
        if ($dropdown_field_required == 'yes') {
            $dropdown_field_required_str = 'required="required"';
        }

        $con_clas_attr = '';
        if ($con_class != '') {
            $con_clas_attr = ' class="' . $con_class . '"';
        }

        $con_f_style = '';
        if ($f_display != '') {
            $con_f_style = ' style="display: ' . $f_display . ';"';
        }

        // get db value if saved
        $dropdown_field_name_db_val = get_post_meta($post_id, $dropdown_field_name, true);
        // creat options string
        $dropdown_field_options_str = '';
        if ($dropdown_field_required == 'yes') {
            $dropdown_field_options_str = '<option value="">' . esc_html__('Select Value', 'wp-jobsearch') . '</option>';
        }
        if (isset($dropdown_field_options['value']) && count($dropdown_field_options['value']) > 0) {
            $option_counter = 0;
            foreach ($dropdown_field_options['value'] as $option) {
                if ($option != '') {
                    $option = ltrim(rtrim($option));
                    if ($dropdown_field_options['label'][$option_counter] != '' && str_replace(" ", "-", $option) != '') {
                        $option_val = strtolower(str_replace(" ", "-", $option));
                        $option_label = $dropdown_field_options['label'][$option_counter];
                        $option_selected = $dropdown_field_name_db_val == $option_val ? ' selected="selected"' : '';

                        $lang_code = '';
                        if (function_exists('icl_object_id')) {
                            $lang_code = $sitepress->get_current_language();
                        }
                        do_action('wpml_register_single_string', 'Custom Fields', 'Dropdown Option Label - ' . $option_label, $option_label);

                        $dropdown_field_options_str .= '<option ' . force_balance_tags($option_selected) . ' value="' . esc_html($option_val) . '">' . apply_filters('wpml_translate_single_string', $option_label, 'Custom Fields', 'Dropdown Option Label - ' . $option_label, $lang_code) . '</option>';
                    }
                }
                $option_counter ++;
            }
        }

        $lang_code = '';
        if (function_exists('icl_object_id')) {
            $lang_code = $sitepress->get_current_language();
        }
        do_action('wpml_register_single_string', 'Custom Fields', 'Dropdown Field Label - ' . $dropdown_field_label, $dropdown_field_label);
        ?>
        <li<?php echo ($con_clas_attr) ?><?php echo ($con_f_style) ?>>
            <label><?php echo apply_filters('wpml_translate_single_string', $dropdown_field_label, 'Custom Fields', 'Dropdown Field Label - ' . $dropdown_field_label, $lang_code) ?></label>
            <?php
            if ($dropdown_field_options_str != '') {
                ?>
                <div class="jobsearch-profile-select">
                    <select name="<?php echo esc_html($dropdown_field_name) ?>" class="<?php echo esc_html($dropdown_field_classes) ?> selectize-select" <?php echo ($dropdown_field_required_str) ?>>
                        <?php
                        echo force_balance_tags($dropdown_field_options_str);
                        ?>
                    </select>
                </div>
            <?php } else {
                ?>
                <span><?php echo esc_html__('Field did not configure properly', 'wp-jobsearch'); ?></span>
                <?php
            }
            ?>
        </li>
        <?php
        $html .= ob_get_clean();

        if ($field_for_wuser == 'non_reg' && is_user_logged_in()) {
            $html = '';
        } else if ($field_for_wuser == 'for_reg' && !is_user_logged_in()) {
            $html = '';
        }

        return $html;
    }

    static function jobsearch_form_custom_field_textarea_load_callback($html, $post_id, $custom_field_saved_data, $fields_prefix, $con_class = '', $f_display = '') {

        global $sitepress;

        ob_start();
        $field_for_wuser = isset($custom_field_saved_data['non_reg_user']) ? $custom_field_saved_data['non_reg_user'] : '';
        $textarea_field_label = isset($custom_field_saved_data['label']) ? $custom_field_saved_data['label'] : '';
        $textarea_field_name = isset($custom_field_saved_data['name']) ? $fields_prefix . $custom_field_saved_data['name'] : '';
        $textarea_field_classes = isset($custom_field_saved_data['classes']) ? $custom_field_saved_data['classes'] : '';
        $textarea_field_placeholder = isset($custom_field_saved_data['placeholder']) ? $custom_field_saved_data['placeholder'] : '';
        $textarea_field_required = isset($custom_field_saved_data['required']) ? $custom_field_saved_data['required'] : '';
        $textarea_field_required_str = '';
        if ($textarea_field_required == 'yes') {
            $textarea_field_required_str = 'required="required"';
        }

        $con_clas_attr = '';
        if ($con_class != '') {
            $con_clas_attr = $con_class;
        }

        $con_f_style = '';
        if ($f_display != '') {
            $con_f_style = ' style="display: ' . $f_display . ';"';
        }

        // get db value if saved
        $textarea_field_name_db_val = get_post_meta($post_id, $textarea_field_name, true);

        $lang_code = '';
        if (function_exists('icl_object_id')) {
            $lang_code = $sitepress->get_current_language();
        }
        do_action('wpml_register_single_string', 'Custom Fields', 'Textarea Field Label - ' . $textarea_field_label, $textarea_field_label);
        ?>
        <li class="jobsearch-user-form-coltwo-full form-textarea <?php echo ($con_clas_attr) ?>"<?php echo ($con_f_style) ?>>
            <label><?php echo apply_filters('wpml_translate_single_string', $textarea_field_label, 'Custom Fields', 'Textarea Field Label - ' . $textarea_field_label, $lang_code) ?></label>
            <?php
            $wped_settings = array(
                'media_buttons' => false,
                'editor_class' => $textarea_field_classes,
                'quicktags' => array('buttons' => 'strong,em,del,ul,ol,li,close'),
                'tinymce' => array(
                    'toolbar1' => 'bold,bullist,numlist,italic,underline,alignleft,aligncenter,alignright,separator,link,unlink,undo,redo',
                    'toolbar2' => '',
                    'toolbar3' => '',
                ),
            );
            wp_editor($textarea_field_name_db_val, $textarea_field_name, $wped_settings);
            ?>
        </li>
        <?php
        $html .= ob_get_clean();

        if ($field_for_wuser == 'non_reg' && is_user_logged_in()) {
            $html = '';
        } else if ($field_for_wuser == 'for_reg' && !is_user_logged_in()) {
            $html = '';
        }

        return $html;
    }

    static function jobsearch_form_custom_field_heading_load_callback($html, $post_id, $custom_field_saved_data, $fields_prefix, $con_class = '', $f_display = '') {

        global $sitepress;

        ob_start();
        $field_for_wuser = isset($custom_field_saved_data['non_reg_user']) ? $custom_field_saved_data['non_reg_user'] : '';
        $heading_field_label = isset($custom_field_saved_data['label']) ? $custom_field_saved_data['label'] : '';

        $con_clas_attr = '';
        if ($con_class != '') {
            $con_clas_attr = $con_class;
        }

        $con_f_style = '';
        if ($f_display != '') {
            $con_f_style = ' style="display: ' . $f_display . ';"';
        }

        $lang_code = '';
        if (function_exists('icl_object_id')) {
            $lang_code = $sitepress->get_current_language();
        }
        do_action('wpml_register_single_string', 'Custom Fields', 'Heading Field Label - ' . $heading_field_label, $heading_field_label);
        ?>
        <li class="jobsearch-user-form-coltwo-full <?php echo ($con_clas_attr) ?>"<?php echo ($con_f_style) ?>>
            <h2><?php echo apply_filters('wpml_translate_single_string', $heading_field_label, 'Custom Fields', 'Heading Field Label - ' . $heading_field_label, $lang_code) ?></h2>
        </li>
        <?php
        $html .= ob_get_clean();

        if ($field_for_wuser == 'non_reg' && is_user_logged_in()) {
            $html = '';
        } else if ($field_for_wuser == 'for_reg' && !is_user_logged_in()) {
            $html = '';
        }

        return $html;
    }

    static function jobsearch_custom_fields_list_callback($custom_field_entity = '', $post_id = '', $custom_fields = array(), $before_html = '<li>', $after_html = '</li>', $fields_number = '', $field_label = true, $field_icon = true, $custom_value_position = true) {
        global $post, $jobsearch_post_post_types, $sitepress;

        if ($post_id == '') {
            $post_id = $post->ID;
        }
        $fields_prefix = ''; // 'jobsearch_field_' . $custom_field_entity . '_';
        $content = '';

        $lang_code = '';
        if (function_exists('icl_object_id')) {
            $lang_code = $sitepress->get_current_language();
        }

        if ($post_id != '' && (($fields_number != '' && $fields_number > 0) || $fields_number == '') && $custom_field_entity != '') {

            $jobsearch_post_type_cus_fields = get_option("jobsearch_custom_field_" . $custom_field_entity);

            if (is_array($jobsearch_post_type_cus_fields) && isset($jobsearch_post_type_cus_fields) && !empty($jobsearch_post_type_cus_fields)) {

                ob_start();
                $custom_field_flag = 1;
                foreach ($jobsearch_post_type_cus_fields as $cus_fieldvar => $cus_field) {
                    if (isset($cus_field['name']) && $cus_field['name'] <> '') {
                        $field_name = $fields_prefix . $cus_field['name'];
                        $cus_field_value_arr = get_post_meta($post_id, $field_name, true);
                        $cus_field_label_arr = isset($cus_field['label']) ? $cus_field['label'] : '';
                        $cus_field_icon_arr = isset($cus_field['icon']) ? $cus_field['icon'] : '';
                        $cus_format = isset($cus_field['date-format']) ? $cus_field['date-format'] : '';
                        $type = isset($cus_field['type']) ? $cus_field['type'] : '';

                        if ($type == 'text') {
                            $cus_field_label_arr = apply_filters('wpml_translate_single_string', $cus_field_label_arr, 'Custom Fields', 'Text Field Label - ' . $cus_field_label_arr, $lang_code);
                        } else if ($type == 'email') {
                            $cus_field_label_arr = apply_filters('wpml_translate_single_string', $cus_field_label_arr, 'Custom Fields', 'Email Field Label - ' . $cus_field_label_arr, $lang_code);
                        } else if ($type == 'number') {
                            $cus_field_label_arr = apply_filters('wpml_translate_single_string', $cus_field_label_arr, 'Custom Fields', 'Number Field Label - ' . $cus_field_label_arr, $lang_code);
                        } else if ($type == 'date') {
                            $cus_field_label_arr = apply_filters('wpml_translate_single_string', $cus_field_label_arr, 'Custom Fields', 'Date Field Label - ' . $cus_field_label_arr, $lang_code);
                        } else if ($type == 'dropdown') {
                            $cus_field_label_arr = apply_filters('wpml_translate_single_string', $cus_field_label_arr, 'Custom Fields', 'Dropdown Field Label - ' . $cus_field_label_arr, $lang_code);
                        } else if ($type == 'range') {
                            $cus_field_label_arr = apply_filters('wpml_translate_single_string', $cus_field_label_arr, 'Custom Fields', 'Range Field Label - ' . $cus_field_label_arr, $lang_code);
                        } else if ($type == 'textarea') {
                            $cus_field_label_arr = apply_filters('wpml_translate_single_string', $cus_field_label_arr, 'Custom Fields', 'Textarea Field Label - ' . $cus_field_label_arr, $lang_code);
                        } else if ($type == 'heading') {
                            $cus_field_label_arr = apply_filters('wpml_translate_single_string', $cus_field_label_arr, 'Custom Fields', 'Heading Field Label - ' . $cus_field_label_arr, $lang_code);
                        }

                        if ($type == 'dropdown') {
                            $drop_down_arr = array();
                            $cut_field_flag = 0;
                            foreach ($cus_field['options']['value'] as $key => $cus_field_options_value) {

                                $drop_down_arr[$cus_field_options_value] = (apply_filters('wpml_translate_single_string', $cus_field['options']['label'][$cut_field_flag], 'Custom Fields', 'Dropdown Option Label - ' . $cus_field['options']['label'][$cut_field_flag], $lang_code));
                                $cut_field_flag ++;
                            }
                        }

                        if (is_array($cus_field_value_arr)) {
                            $cus_field_value_arr = array_filter($cus_field_value_arr);
                        }
                        if (isset($cus_field_value_arr) && (is_array($cus_field_value_arr) && !empty($cus_field_value_arr)) || (!is_array($cus_field_value_arr) && $cus_field_value_arr <> '')) {
                            echo $before_html;
                            if (isset($cus_field_icon_arr) && $cus_field_icon_arr <> '' && $field_icon == true) {
                                ?>
                                <i class="<?php echo esc_html($cus_field_icon_arr) ?>"></i>
                                <?php
                            }

                            echo '<div class="jobsearch-services-text">';
                            if (is_array($cus_field_value_arr)) {
                                if (isset($cus_field_label_arr) && $cus_field_label_arr <> '') {
                                    echo esc_html($cus_field_label_arr) . ' ';
                                }
                                foreach ($cus_field_value_arr as $key => $single_value) {
                                    if ($single_value != '') {
                                        if (isset($cus_format) && $cus_format != '') {
                                            echo '<small>';
                                            echo date($cus_format, $single_value);
                                            echo '</small>';
                                        } else if ($type == 'dropdown' && isset($drop_down_arr[$single_value]) && $drop_down_arr[$single_value] != '') {
                                            echo '<small>';
                                            echo '<span>' . esc_html($drop_down_arr[$single_value]) . '</span>';
                                            echo '</small>';
                                        } else {
                                            echo '<small>';
                                            echo '<span>' . esc_html(ucwords(str_replace("-", " ", $single_value))) . '</span>';
                                            echo '</small>';
                                        }
                                    }
                                }
                                if (isset($cus_field_label_arr) && $cus_field_label_arr <> '' && $type != 'dropdown' && $type != 'date') {
                                    echo '<span>' . esc_html($cus_field_label_arr) . ' </span>';
                                }
                            } else {

                                if (isset($cus_field_label_arr) && $cus_field_label_arr <> '') {
                                    if ($custom_value_position) {
                                        if ($field_label == true) {
                                            echo esc_html($cus_field_label_arr) . ' ';
                                        }
                                    }
                                }

                                if (isset($cus_format) && $cus_format != '') {
                                    echo '<small>';
                                    echo date($cus_format, $cus_field_value_arr);
                                    echo '</small>';
                                } else if ($type == 'dropdown' && isset($drop_down_arr[$cus_field_value_arr]) && $drop_down_arr[$cus_field_value_arr] != '') {
                                    echo '<small>';
                                    echo esc_html($drop_down_arr[$cus_field_value_arr]);
                                    echo '</small>';
                                } else {
                                    if ($custom_value_position) {
                                        echo '<small>';
                                        echo (ucwords(str_replace("-", " ", $cus_field_value_arr)));
                                        echo '</small>';
                                    }
                                }
                            }
                            echo '</div>';
                            echo $after_html;
                            $custom_field_flag ++;
                            if ($custom_field_flag > $fields_number && $fields_number != '') {
                                break;
                            }
                        }
                    }
                }
                $content = ob_get_clean();
            }
        }
        $custom_fields['content'] = $content;
        return $custom_fields;
    }

    static function jobsearch_custom_fields_filter_box_html_callback($html, $custom_field_entity = '', $global_rand_id, $args_count, $left_filter_count_switch, $submit_js_function) {
        global $jobsearch_form_fields, $jobsearch_plugin_options, $sitepress;

        $lang_code = '';
        if (function_exists('icl_object_id')) {
            $lang_code = $sitepress->get_current_language();
        }

        $submit_js_function_str = '';
        if ($submit_js_function != '') {
            $submit_js_function_str = $submit_js_function . '(' . $global_rand_id . ')';
        }
        
        //
        $salary_onoff_switch = isset($jobsearch_plugin_options['salary_onoff_switch']) ? $jobsearch_plugin_options['salary_onoff_switch'] : '';

        $job_cus_fields = get_option("jobsearch_custom_field_" . $custom_field_entity);
        ob_start();
        $custom_field_flag = 11;
        if (!empty($job_cus_fields)) {
            foreach ($job_cus_fields as $cus_fieldvar => $cus_field) {
                $all_item_empty = 0;
                if (isset($cus_field['options']['value']) && is_array($cus_field['options']['value'])) {
                    foreach ($cus_field['options']['value'] as $cus_field_options_value) {

                        if ($cus_field_options_value != '') {
                            $all_item_empty = 0;
                            break;
                        } else {
                            $all_item_empty = 1;
                        }
                    }
                }
                if ($cus_field['type'] == 'salary') {
                    $cus_field['enable-search'] = 'yes';
                }
                if (isset($cus_field['enable-search']) && $cus_field['enable-search'] == 'yes' && ($all_item_empty == 0)) {
                    if ($cus_field['type'] == 'salary') {
                        $query_str_var_name = 'jobsearch_field_job_salary';
                        $str_salary_type_name = 'job_salary_type';
                        if ($custom_field_entity == 'candidate') {
                            $query_str_var_name = 'jobsearch_field_candidate_salary';
                            $str_salary_type_name = 'candidate_salary_type';
                        }
                    } else {
                        $query_str_var_name = $cus_field['name'];
                    }
                    $collapse_condition = 'no';
                    if (isset($cus_field['collapse-search'])) {
                        $collapse_condition = $cus_field['collapse-search'];
                    }

                    $cus_field_label_arr = isset($cus_field['label']) ? $cus_field['label'] : '';
                    $type = isset($cus_field['type']) ? $cus_field['type'] : '';

                    if ($type == 'text') {
                        $cus_field_label_arr = apply_filters('wpml_translate_single_string', $cus_field_label_arr, 'Custom Fields', 'Text Field Label - ' . $cus_field_label_arr, $lang_code);
                    } else if ($type == 'email') {
                        $cus_field_label_arr = apply_filters('wpml_translate_single_string', $cus_field_label_arr, 'Custom Fields', 'Email Field Label - ' . $cus_field_label_arr, $lang_code);
                    } else if ($type == 'number') {
                        $cus_field_label_arr = apply_filters('wpml_translate_single_string', $cus_field_label_arr, 'Custom Fields', 'Number Field Label - ' . $cus_field_label_arr, $lang_code);
                    } else if ($type == 'date') {
                        $cus_field_label_arr = apply_filters('wpml_translate_single_string', $cus_field_label_arr, 'Custom Fields', 'Date Field Label - ' . $cus_field_label_arr, $lang_code);
                    } else if ($type == 'dropdown') {
                        $cus_field_label_arr = apply_filters('wpml_translate_single_string', $cus_field_label_arr, 'Custom Fields', 'Dropdown Field Label - ' . $cus_field_label_arr, $lang_code);
                    } else if ($type == 'range') {
                        $cus_field_label_arr = apply_filters('wpml_translate_single_string', $cus_field_label_arr, 'Custom Fields', 'Range Field Label - ' . $cus_field_label_arr, $lang_code);
                    } else if ($type == 'textarea') {
                        $cus_field_label_arr = apply_filters('wpml_translate_single_string', $cus_field_label_arr, 'Custom Fields', 'Textarea Field Label - ' . $cus_field_label_arr, $lang_code);
                    } else if ($type == 'heading') {
                        $cus_field_label_arr = apply_filters('wpml_translate_single_string', $cus_field_label_arr, 'Custom Fields', 'Heading Field Label - ' . $cus_field_label_arr, $lang_code);
                    } else if ($type == 'salary') {
                        $cus_field_label_arr = apply_filters('wpml_translate_single_string', $cus_field_label_arr, 'Custom Fields', 'Salary Label - ' . $cus_field_label_arr, $lang_code);
                    }
                    ?>
                    <div class="jobsearch-filter-responsive-wrap">
                        <div class="jobsearch-search-filter-wrap <?php echo ($collapse_condition == 'yes' ? 'jobsearch-search-filter-toggle jobsearch-remove-padding' : 'jobsearch-search-filter-toggle') ?>">
                            <h2>
                                <a href="javascript:void(0);" class="jobsearch-click-btn">
                                    <?php echo esc_html($cus_field_label_arr); ?>
                                </a>
                            </h2>
                            <div class="jobsearch-checkbox-toggle" <?php echo ($collapse_condition == 'yes' ? 'style="display: none;"' : '') ?>>   
                                <?php
                                if ($cus_field['type'] == 'dropdown') {
                                    $request_val = isset($_REQUEST[$query_str_var_name]) ? $_REQUEST[$query_str_var_name] : '';
                                    $request_val_arr = explode(",", $request_val);
                                    ?>
                                    <input type="hidden" value="<?php echo esc_html($request_val); ?>" name="<?php echo esc_html($query_str_var_name); ?>" id="hidden_input-<?php echo esc_html($query_str_var_name); ?>" class="<?php echo esc_html($query_str_var_name); ?>" />
                                    <ul class="jobsearch-checkbox">
                                        <?php
                                        $number_option_flag = 1;
                                        $cut_field_flag = 0;
                                        foreach ($cus_field['options']['value'] as $cus_field_options_value) {
                                            if ($cus_field['options']['value'][$cut_field_flag] == '' || $cus_field['options']['label'][$cut_field_flag] == '') {
                                                $cut_field_flag ++;
                                                continue;
                                            }
                                            // get count of each item
                                            // extra condidation
                                            if (isset($cus_field['post-multi']) && $cus_field['post-multi'] == 'yes') {

                                                $dropdown_count_arr = array(
                                                    array(
                                                        'key' => $query_str_var_name,
                                                        'value' => ($cus_field_options_value),
                                                        'compare' => 'Like',
                                                    )
                                                );
                                            } else {
                                                $dropdown_count_arr = array(
                                                    array(
                                                        'key' => $query_str_var_name,
                                                        'value' => $cus_field_options_value,
                                                        'compare' => '=',
                                                    )
                                                );
                                            }
                                            // main query array $args_count 
                                            $dropdown_totnum = jobsearch_get_item_count($left_filter_count_switch, $args_count, $dropdown_count_arr, $global_rand_id, $query_str_var_name, $custom_field_entity);
                                            if ($cus_field_options_value != '') {
                                                if (isset($cus_field['multi']) && $cus_field['multi'] == 'yes') {
                                                    $checked = '';
                                                    if (!empty($request_val_arr) && in_array($cus_field_options_value, $request_val_arr)) {
                                                        $checked = ' checked="checked"';
                                                    }
                                                    ?>
                                                    <li class="<?php echo ($number_option_flag > 6 ? 'filter-more-fields' : '') ?><?php echo ($left_filter_count_switch != 'yes' ? ' no-filter-counts' : '') ?>">
                                                        <script>
                                                            jQuery(function () {
                                                                'use strict'
                                                                var $checkboxes = jQuery("input[type=checkbox].<?php echo esc_html($query_str_var_name); ?>");
                                                                $checkboxes.on('change', function () {
                                                                    var ids = $checkboxes.filter(':checked').map(function () {
                                                                        return this.value;
                                                                    }).get().join(',');
                                                                    jQuery('#hidden_input-<?php echo esc_html($query_str_var_name); ?>').val(ids);
                                                                    <?php echo force_balance_tags($submit_js_function_str); ?>
                                                                });

                                                            });
                                                        </script> 
                                                        <input type="checkbox" id="<?php echo esc_html($query_str_var_name . '_' . $number_option_flag); ?>" value="<?php echo esc_html($cus_field_options_value); ?>" class="<?php echo esc_html($query_str_var_name); ?>" <?php echo esc_html($checked); ?> />
                                                        <label for="<?php echo force_balance_tags($query_str_var_name . '_' . $number_option_flag) ?>">
                                                            <span></span><?php echo (apply_filters('wpml_translate_single_string', $cus_field['options']['label'][$cut_field_flag], 'Custom Fields', 'Dropdown Option Label - ' . $cus_field['options']['label'][$cut_field_flag], $lang_code)); ?>
                                                        </label>
                                                        <?php if ($left_filter_count_switch == 'yes') { ?>
                                                            <span class="filter-post-count"><?php echo absint($dropdown_totnum); ?></span>
                                                        <?php } ?>
                                                    </li>
                                                    <?php
//                                                 
                                                } else {
                                                    //get count for this itration
                                                    $dropdown_arr = array();
                                                    if (isset($cus_field['post-multi']) && $cus_field['post-multi'] == 'yes') {
                                                        $dropdown_arr = array(
                                                            'key' => $query_str_var_name,
                                                            'value' => serialize($cus_field_options_value),
                                                            'compare' => 'Like',
                                                        );
                                                    } else {
                                                        $dropdown_arr = array(
                                                            'key' => $query_str_var_name,
                                                            'value' => $cus_field_options_value,
                                                            'compare' => '=',
                                                        );
                                                    }

                                                    $custom_dropdown_selected = '';
                                                    if (isset($_REQUEST[$query_str_var_name]) && $_REQUEST[$query_str_var_name] == $cus_field_options_value) {
                                                        $custom_dropdown_selected = ' checked="checked"';
                                                    }
                                                    ?> 
                                                    <li class="<?php echo ($number_option_flag > 6 ? 'filter-more-fields' : '') ?><?php echo ($left_filter_count_switch != 'yes' ? ' no-filter-counts' : '') ?>">
                                                        <input type="radio" name="<?php echo esc_html($query_str_var_name); ?>" id="<?php echo esc_html($query_str_var_name . '_' . $number_option_flag); ?>" value="<?php echo esc_html($cus_field_options_value); ?>" <?php echo esc_html($custom_dropdown_selected); ?> onchange="<?php echo force_balance_tags($submit_js_function_str); ?>" />
                                                        <label for="<?php echo esc_html($query_str_var_name . '_' . $number_option_flag); ?>">
                                                            <span></span><?php echo (apply_filters('wpml_translate_single_string', $cus_field['options']['label'][$cut_field_flag], 'Custom Fields', 'Dropdown Option Label - ' . $cus_field['options']['label'][$cut_field_flag], $lang_code)); ?>
                                                        </label>
                                                        <?php if ($left_filter_count_switch == 'yes') { ?>
                                                            <span class="filter-post-count"><?php echo absint($dropdown_totnum); ?></span>
                                                            <?php
                                                        }
                                                        ?>
                                                    </li>
                                                    <?php
                                                }
                                            }
                                            $number_option_flag ++;
                                            $cut_field_flag ++;
                                        }
                                        ?>
                                    </ul>
                                    <?php
                                    if ($number_option_flag > 6) {
                                        echo '<a href="javascript:void(0);" class="show-toggle-filter-list">' . esc_html__('+ see more', 'wp-jobsearch') . '</a>';
                                    }
                                    //
                                } else if ($cus_field['type'] == 'text' || $cus_field['type'] == 'textarea' || $cus_field['type'] == 'email') {
                                    $text_field_req_val = isset($_REQUEST[$query_str_var_name]) ? $_REQUEST[$query_str_var_name] : '';
                                    ?>
                                    <ul class="jobsearch-checkbox">
                                        <li>
                                            <input type="text" name="<?php echo esc_html($query_str_var_name); ?>" id="<?php echo esc_html($query_str_var_name); ?>" value="<?php echo esc_html($text_field_req_val); ?>" onchange="<?php echo force_balance_tags($submit_js_function_str); ?>" />
                                        </li>
                                    </ul>
                                    <?php
                                } else if ($cus_field['type'] == 'number') {
                                    $number_field_req_val = isset($_REQUEST[$query_str_var_name]) ? $_REQUEST[$query_str_var_name] : '';
                                    ?> 
                                    <ul class="jobsearch-checkbox">
                                        <li>
                                            <input type="number" name="<?php echo esc_html($query_str_var_name); ?>" id="<?php echo esc_html($query_str_var_name); ?>" value="<?php echo esc_html($number_field_req_val); ?>" onchange="<?php echo force_balance_tags($submit_js_function_str); ?>" />
                                        </li>
                                    </ul>
                                    <?php
                                } else if ($cus_field['type'] == 'date') {
                                    $fromdate_field_req_val = isset($_REQUEST['from-' . $query_str_var_name]) ? $_REQUEST['from-' . $query_str_var_name] : '';
                                    $todate_field_req_val = isset($_REQUEST['to-' . $query_str_var_name]) ? $_REQUEST['to-' . $query_str_var_name] : '';
                                    wp_enqueue_style('datetimepicker-style');
                                    wp_enqueue_script('datetimepicker-script');
                                    wp_enqueue_script('jquery-ui');
                                    $cus_field_date_formate_arr = explode(" ", $cus_field['date-format']);
                                    ?>

                                    <ul class="jobsearch-checkbox">
                                        <li>
                                            <script>
                                                jQuery(document).ready(function () {
                                                    jQuery("#from<?php echo esc_html($query_str_var_name); ?>").datetimepicker({
                                                        format: "<?php echo esc_html($cus_field_date_formate_arr[0]); ?>",
                                                        timepicker: false
                                                    });
                                                    jQuery("#to<?php echo esc_html($query_str_var_name); ?>").datetimepicker({
                                                        format: "<?php echo esc_html($cus_field_date_formate_arr[0]); ?>",
                                                        timepicker: false
                                                    });
                                                });
                                            </script>
                                            <input type="text" name="from-<?php echo esc_html($query_str_var_name); ?>" id="from<?php echo esc_html($query_str_var_name); ?>" value="<?php echo esc_html($fromdate_field_req_val); ?>" onchange="<?php echo force_balance_tags($submit_js_function_str); ?>" />
                                            - 
                                            <input type="text" name="to-<?php echo esc_html($query_str_var_name); ?>" id="to<?php echo esc_html($query_str_var_name); ?>" value="<?php echo esc_html($todate_field_req_val); ?>" onchange="<?php echo force_balance_tags($submit_js_function_str); ?>" />
                                        </li>
                                    </ul>
                                    <?php
                                } elseif ($cus_field['type'] == 'range') {

                                    $range_min = $cus_field['min'];
                                    $range_laps = $cus_field['laps'];
                                    $range_interval = $cus_field['interval'];
                                    $range_field_type = isset($cus_field['field-style']) ? $cus_field['field-style'] : 'simple'; //input, slider, input_slider

                                    if (strpos($range_field_type, '-') !== FALSE) {
                                        $range_field_type_arr = explode("_", $range_field_type);
                                    } else {
                                        $range_field_type_arr[0] = $range_field_type;
                                    }
                                    $range_flag = 0;
                                    while (count($range_field_type_arr) > $range_flag) {
                                        if ($range_field_type_arr[$range_flag] == 'simple') { // if input style
                                            $filter_more_counter = 1;
                                            ?>
                                            <ul class="jobsearch-checkbox">
                                                <?php
                                                $loop_flag = 1;
                                                while ($loop_flag <= $range_laps) {
                                                    ?> 
                                                    <li class="<?php echo ($filter_more_counter > 6 ? 'filter-more-fields' : '') ?><?php echo ($left_filter_count_switch != 'yes' ? ' no-filter-counts' : '') ?>">
                                                        <?php
                                                        // main query array $args_count 
                                                        $range_first = $range_min + 1;
                                                        $range_seond = $range_min + $range_interval;
                                                        $range_count_arr = array(
                                                            array(
                                                                'key' => $query_str_var_name,
                                                                'value' => $range_first,
                                                                'compare' => '>=',
                                                                'type' => 'numeric'
                                                            ),
                                                            array(
                                                                'key' => $query_str_var_name,
                                                                'value' => $range_seond,
                                                                'compare' => '<=',
                                                                'type' => 'numeric'
                                                            )
                                                        );
                                                        $range_totnum = jobsearch_get_item_count($left_filter_count_switch, $args_count, $range_count_arr, $global_rand_id, $query_str_var_name, $custom_field_entity);
                                                        $custom_slider_selected = '';
                                                        if (isset($_REQUEST[$query_str_var_name]) && $_REQUEST[$query_str_var_name] == (($range_min + 1) . "-" . ($range_min + $range_interval))) {
                                                            $custom_slider_selected = ' checked="checked"';
                                                        }
                                                        ?>
                                                        <input type="radio" name="<?php echo esc_html($query_str_var_name); ?>" id="<?php echo esc_html($query_str_var_name . $loop_flag); ?>" value="<?php echo esc_html(( ($range_min + 1) . "-" . ($range_min + $range_interval))); ?>" <?php echo esc_html($custom_slider_selected); ?> onchange="<?php echo force_balance_tags($submit_js_function_str); ?>" />
                                                        <label for="<?php echo esc_html($query_str_var_name . $loop_flag); ?>"><span></span><?php echo force_balance_tags(( ($range_min + 1) . " - " . ($range_min + $range_interval))); ?></label>
                                                        <?php if ($left_filter_count_switch == 'yes') { ?>
                                                            <span class="filter-post-count"><?php echo absint($range_totnum); ?></span> 
                                                        <?php } ?>
                                                    </li><?php
                                                    $range_min = $range_min + $range_interval;
                                                    $loop_flag++;
                                                    $filter_more_counter ++;
                                                }
                                                ?>
                                            </ul>
                                            <?php
                                            if ($filter_more_counter > 6) {
                                                echo '<a href="javascript:void(0);" class="show-toggle-filter-list">' . esc_html__('+ see more', 'wp-jobsearch') . '</a>';
                                            }
                                        } elseif ($range_field_type_arr[$range_flag] == 'slider') { // if slider style 
                                            wp_enqueue_style('jquery-ui');
                                            wp_enqueue_script('jquery-ui');
                                            $rand_id = rand(123, 1231231);
                                            $range_field_max = $range_min;
                                            $i = 0;
                                            while ($range_laps > $i) {
                                                $range_field_max = $range_field_max + $range_interval;
                                                $i++;
                                            }
                                            $range_complete_str_first = "";
                                            $range_complete_str_second = "";
                                            $range_complete_str = '';
                                            $range_complete_str_first = $range_min;
                                            $range_complete_str_second = $range_field_max;
                                            if (isset($_REQUEST[$query_str_var_name])) {
                                                $range_complete_str = $_REQUEST[$query_str_var_name];
                                                $range_complete_str_arr = explode("-", $range_complete_str);
                                                $range_complete_str_first = isset($range_complete_str_arr[0]) ? $range_complete_str_arr[0] : '';
                                                $range_complete_str_second = isset($range_complete_str_arr[1]) ? $range_complete_str_arr[1] : '';
                                            }
                                            ?>
                                            <ul class="jobsearch-checkbox">
                                                <li>
                                                    <input type="text" name="<?php echo esc_html($query_str_var_name) ?>" id="<?php echo esc_html($query_str_var_name . $rand_id) ?>" value="<?php echo esc_html($range_complete_str); ?>" readonly style="border:0; color:#f6931f; font-weight:bold;" />
                                                    <div id="slider-range<?php echo esc_html($query_str_var_name . $rand_id) ?>"></div>
                                                    <script>
                                                        jQuery(document).ready(function () {



                                                            jQuery("#slider-range<?php echo esc_html($query_str_var_name . $rand_id) ?>").slider({
                                                                range: true,
                                                                min: <?php echo absint($range_min); ?>,
                                                                max: <?php echo absint($range_field_max); ?>,
                                                                values: [<?php echo absint($range_complete_str_first); ?>, <?php echo absint($range_complete_str_second); ?>],
                                                                slide: function (event, ui) {
                                                                    jQuery("#<?php echo esc_html($query_str_var_name . $rand_id) ?>").val(ui.values[ 0 ] + "-" + ui.values[ 1 ]);
                                                                },
                                                                stop: function (event, ui) {
                                <?php echo force_balance_tags($submit_js_function_str); ?>;
                                                                }
                                                            });
                                                            jQuery("#<?php echo esc_html($query_str_var_name . $rand_id) ?>").val(jQuery("#slider-range<?php echo esc_html($query_str_var_name . $rand_id) ?>").slider("values", 0) +
                                                                    "-" + jQuery("#slider-range<?php echo esc_html($query_str_var_name . $rand_id) ?>").slider("values", 1));
                                                        });
                                                    </script>
                                                </li>
                                            </ul>
                                            <?php
                                        }
                                        $range_flag ++;
                                    }
                                } elseif ($cus_field['type'] == 'salary' && $salary_onoff_switch == 'on') {

                                    $job_salary_types = isset($jobsearch_plugin_options['job-salary-types']) ? $jobsearch_plugin_options['job-salary-types'] : '';

                                    $salary_min = $cus_field['min'];
                                    $salary_laps = $cus_field['laps'];
                                    $salary_interval = $cus_field['interval'];
                                    $salary_field_type = isset($cus_field['field-style']) ? $cus_field['field-style'] : 'simple'; //input, slider, input_slider

                                    if (strpos($salary_field_type, '-') !== FALSE) {
                                        $salary_field_type_arr = explode("_", $salary_field_type);
                                    } else {
                                        $salary_field_type_arr[0] = $salary_field_type;
                                    }

                                    // Salary Types
                                    if (!empty($job_salary_types)) {
                                        $slar_type_count = 1;
                                        ?>
                                        <div class="jobsearch-salary-types-filter">
                                            <ul>
                                                <?php
                                                foreach ($job_salary_types as $job_salary_type) {
                                                    $job_salary_type = apply_filters('wpml_translate_single_string', $job_salary_type, 'JobSearch Options', 'Salary Type - ' . $job_salary_type, $lang_code);
                                                    $slalary_type_selected = '';
                                                    if (isset($_REQUEST[$str_salary_type_name]) && $_REQUEST[$str_salary_type_name] == 'type_' . $slar_type_count) {
                                                        $slalary_type_selected = ' checked="checked"';
                                                    }
                                                    ?>
                                                    <li class="salary-type-radio">
                                                        <input type="radio" id="salary_type_<?php echo ($slar_type_count) ?>" name="<?php echo ($str_salary_type_name) ?>" class="job_salary_type"<?php echo ($slalary_type_selected) ?> value="type_<?php echo ($slar_type_count) ?>" onchange="<?php echo force_balance_tags($submit_js_function_str); ?>">
                                                        <label for="salary_type_<?php echo ($slar_type_count) ?>">
                                                            <span></span><small><?php echo ($job_salary_type) ?></small>
                                                        </label>
                                                    </li>
                                                    <?php
                                                    $slar_type_count++;
                                                }
                                                ?>
                                            </ul>
                                        </div>
                                        <?php
                                    }
                                    //

                                    $salary_flag = 0;
                                    while (count($salary_field_type_arr) > $salary_flag) {
                                        if ($salary_field_type_arr[$salary_flag] == 'simple') { // if input style
                                            $filter_more_counter = 1;
                                            ?>
                                            <ul class="jobsearch-checkbox">
                                                <?php
                                                $loop_flag = 1;
                                                while ($loop_flag <= $salary_laps) {
                                                    ?> 
                                                    <li class="<?php echo ($filter_more_counter > 6 ? 'filter-more-fields' : '') ?><?php echo ($left_filter_count_switch != 'yes' ? ' no-filter-counts' : '') ?>">
                                                        <?php
                                                        // main query array $args_count 
                                                        $salary_first = $salary_min + 1;
                                                        $salary_seond = $salary_min + $salary_interval;
                                                        $salary_count_arr = array(
                                                            array(
                                                                'key' => $query_str_var_name,
                                                                'value' => $salary_first,
                                                                'compare' => '>=',
                                                                'type' => 'numeric'
                                                            ),
                                                            array(
                                                                'key' => $query_str_var_name,
                                                                'value' => $salary_seond,
                                                                'compare' => '<=',
                                                                'type' => 'numeric'
                                                            )
                                                        );
                                                        $salary_totnum = jobsearch_get_item_count($left_filter_count_switch, $args_count, $salary_count_arr, $global_rand_id, $query_str_var_name, $custom_field_entity);
                                                        $custom_slider_selected = '';
                                                        if (isset($_REQUEST[$query_str_var_name]) && $_REQUEST[$query_str_var_name] == (($salary_min + 1) . "-" . ($salary_min + $salary_interval))) {
                                                            $custom_slider_selected = ' checked="checked"';
                                                        }
                                                        ?>
                                                        <input type="radio" name="<?php echo esc_html($query_str_var_name); ?>" id="<?php echo esc_html($query_str_var_name . $loop_flag); ?>" value="<?php echo esc_html(( ($salary_min + 1) . "-" . ($salary_min + $salary_interval))); ?>" <?php echo esc_html($custom_slider_selected); ?> onchange="<?php echo force_balance_tags($submit_js_function_str); ?>" />
                                                        <?php
                                                        $salary_from = ($salary_min + 1);
                                                        $salary_upto = ($salary_min + $salary_interval);
                                                        ?>
                                                        <label for="<?php echo esc_html($query_str_var_name . $loop_flag); ?>"><span></span><?php echo (( ($salary_from) . " - " . ($salary_upto))); ?></label>
                                                        <?php if ($left_filter_count_switch == 'yes') { ?>
                                                            <span class="filter-post-count"><?php echo absint($salary_totnum); ?></span> 
                                                        <?php } ?>
                                                    </li><?php
                                                    $salary_min = $salary_min + $salary_interval;
                                                    $loop_flag++;
                                                    $filter_more_counter ++;
                                                }
                                                ?>
                                            </ul>
                                            <?php
                                            if ($filter_more_counter > 6) {
                                                echo '<a href="javascript:void(0);" class="show-toggle-filter-list">' . esc_html__('+ see more', 'wp-jobsearch') . '</a>';
                                            }
                                        } elseif ($salary_field_type_arr[$salary_flag] == 'slider') { // if slider style 
                                            wp_enqueue_style('jquery-ui');
                                            wp_enqueue_script('jquery-ui');
                                            $rand_id = rand(1231110, 9231231);
                                            $salary_field_max = $salary_min;
                                            $i = 0;
                                            while ($salary_laps > $i) {
                                                $salary_field_max = $salary_field_max + $salary_interval;
                                                $i++;
                                            }
                                            $salary_complete_str_first = "";
                                            $salary_complete_str_second = "";
                                            $salary_complete_str = '';
                                            $salary_complete_str_first = $salary_min;
                                            $salary_complete_str_second = $salary_field_max;
                                            if (isset($_REQUEST[$query_str_var_name])) {
                                                $salary_complete_str = $_REQUEST[$query_str_var_name];
                                                $salary_complete_str_arr = explode("-", $salary_complete_str);
                                                $salary_complete_str_first = isset($salary_complete_str_arr[0]) ? $salary_complete_str_arr[0] : '';
                                                $salary_complete_str_second = isset($salary_complete_str_arr[1]) ? $salary_complete_str_arr[1] : '';
                                            }
                                            ?>
                                            <ul class="jobsearch-checkbox">
                                                <li class="salary-filter-slider">
                                                    <div class="filter-slider-range">
                                                        <input type="text" name="<?php echo esc_html($query_str_var_name) ?>" id="<?php echo esc_html($query_str_var_name . $rand_id) ?>" value="<?php echo esc_html($salary_complete_str); ?>" readonly style="border:0; color:#f6931f; font-weight:bold;" />
                                                    </div>
                                                    <div id="slider-salary<?php echo esc_html($query_str_var_name . $rand_id) ?>"></div>
                                                    <script>
                                                        jQuery(document).ready(function () {

                                                            jQuery("#slider-salary<?php echo esc_html($query_str_var_name . $rand_id) ?>").slider({
                                                                salary: true,
                                                                min: <?php echo absint($salary_min); ?>,
                                                                max: <?php echo absint($salary_field_max); ?>,
                                                                values: [<?php echo absint($salary_complete_str_first); ?>, <?php echo absint($salary_complete_str_second); ?>],
                                                                slide: function (event, ui) {
                                                                    jQuery("#<?php echo esc_html($query_str_var_name . $rand_id) ?>").val(ui.values[ 0 ] + "-" + ui.values[ 1 ]);
                                                                },
                                                                stop: function (event, ui) {
                                <?php echo force_balance_tags($submit_js_function_str); ?>;
                                                                }
                                                            });
                                                            jQuery("#<?php echo esc_html($query_str_var_name . $rand_id) ?>").val(jQuery("#slider-salary<?php echo esc_html($query_str_var_name . $rand_id) ?>").slider("values", 0) +
                                                                    "-" + jQuery("#slider-salary<?php echo esc_html($query_str_var_name . $rand_id) ?>").slider("values", 1));

                                                        });
                                                    </script>
                                                </li>
                                            </ul>
                                            <?php
                                        }
                                        $salary_flag ++;
                                    }
                                }
                                ?> 

                            </div>
                        </div>
                    </div>
                    <?php
                }
            }
        }
        $html .= ob_get_clean();
        return $html;
    }

    static function custom_fields_top_filter_box_html_callback($html, $custom_field_entity = '', $global_rand_id) {
        global $jobsearch_form_fields, $jobsearch_plugin_options, $sitepress;

        $lang_code = '';
        if (function_exists('icl_object_id')) {
            $lang_code = $sitepress->get_current_language();
        }
        
        //
        $salary_onoff_switch = isset($jobsearch_plugin_options['salary_onoff_switch']) ? $jobsearch_plugin_options['salary_onoff_switch'] : '';

        $job_cus_fields = get_option("jobsearch_custom_field_" . $custom_field_entity);
        ob_start();
        $custom_field_flag = 11;
        if (!empty($job_cus_fields)) {
            foreach ($job_cus_fields as $cus_fieldvar => $cus_field) {
                $all_item_empty = 0;
                if (isset($cus_field['options']['value']) && is_array($cus_field['options']['value'])) {
                    foreach ($cus_field['options']['value'] as $cus_field_options_value) {

                        if ($cus_field_options_value != '') {
                            $all_item_empty = 0;
                            break;
                        } else {
                            $all_item_empty = 1;
                        }
                    }
                }
                if ($cus_field['type'] == 'salary') {
                    $cus_field['enable-search'] = 'yes';
                }
                if (isset($cus_field['enable-search']) && $cus_field['enable-search'] == 'yes' && ($all_item_empty == 0)) {
                    if ($cus_field['type'] == 'salary') {
                        $query_str_var_name = 'jobsearch_field_job_salary';
                        $str_salary_type_name = 'job_salary_type';
                        if ($custom_field_entity == 'candidate') {
                            $query_str_var_name = 'jobsearch_field_candidate_salary';
                            $str_salary_type_name = 'candidate_salary_type';
                        }
                    } else {
                        $query_str_var_name = $cus_field['name'];
                    }
                    $collapse_condition = 'no';
                    if (isset($cus_field['collapse-search'])) {
                        $collapse_condition = $cus_field['collapse-search'];
                    }

                    $cus_field_label_arr = isset($cus_field['label']) ? $cus_field['label'] : '';
                    $type = isset($cus_field['type']) ? $cus_field['type'] : '';

                    if ($type == 'text') {
                        $cus_field_label_arr = apply_filters('wpml_translate_single_string', $cus_field_label_arr, 'Custom Fields', 'Text Field Label - ' . $cus_field_label_arr, $lang_code);
                    } else if ($type == 'email') {
                        $cus_field_label_arr = apply_filters('wpml_translate_single_string', $cus_field_label_arr, 'Custom Fields', 'Email Field Label - ' . $cus_field_label_arr, $lang_code);
                    } else if ($type == 'number') {
                        $cus_field_label_arr = apply_filters('wpml_translate_single_string', $cus_field_label_arr, 'Custom Fields', 'Number Field Label - ' . $cus_field_label_arr, $lang_code);
                    } else if ($type == 'date') {
                        $cus_field_label_arr = apply_filters('wpml_translate_single_string', $cus_field_label_arr, 'Custom Fields', 'Date Field Label - ' . $cus_field_label_arr, $lang_code);
                    } else if ($type == 'dropdown') {
                        $cus_field_label_arr = apply_filters('wpml_translate_single_string', $cus_field_label_arr, 'Custom Fields', 'Dropdown Field Label - ' . $cus_field_label_arr, $lang_code);
                    } else if ($type == 'range') {
                        $cus_field_label_arr = apply_filters('wpml_translate_single_string', $cus_field_label_arr, 'Custom Fields', 'Range Field Label - ' . $cus_field_label_arr, $lang_code);
                    } else if ($type == 'textarea') {
                        $cus_field_label_arr = apply_filters('wpml_translate_single_string', $cus_field_label_arr, 'Custom Fields', 'Textarea Field Label - ' . $cus_field_label_arr, $lang_code);
                    } else if ($type == 'heading') {
                        $cus_field_label_arr = apply_filters('wpml_translate_single_string', $cus_field_label_arr, 'Custom Fields', 'Heading Field Label - ' . $cus_field_label_arr, $lang_code);
                    } else if ($type == 'salary') {
                        $cus_field_label_arr = apply_filters('wpml_translate_single_string', $cus_field_label_arr, 'Custom Fields', 'Salary Label - ' . $cus_field_label_arr, $lang_code);
                    }

                    if ($cus_field['type'] == 'dropdown') {
                        $request_val = isset($_REQUEST[$query_str_var_name]) ? $_REQUEST[$query_str_var_name] : '';
                        $request_val_arr = explode(",", $request_val);

                        $number_option_flag = 1;
                        $cut_field_flag = 0;
                        if (isset($cus_field['multi']) && $cus_field['multi'] == 'yes') {
                            $select_param = 'multiple="multiple"';
                        } else {
                            $select_param = '';
                        }
                        ?>
                        <li>
                            <div class="jobsearch-select-style">
                                <select name="<?php echo esc_html($query_str_var_name); ?>" class="selectize-select" <?php echo ($select_param) ?> placeholder="<?php echo ($cus_field_label_arr); ?>">
                                    <?php
                                    $cutsf_field_flag = 1;
                                    foreach ($cus_field['options']['value'] as $cus_field_options_value) {
                                        if ($cus_field['options']['value'][$cut_field_flag] == '' || $cus_field['options']['label'][$cut_field_flag] == '') {
                                            $cut_field_flag ++;
                                            continue;
                                        }
                                        if ($cus_field_options_value != '') {
                                            if (isset($cus_field['multi']) && $cus_field['multi'] == 'yes') {
                                                $checked = '';
                                                if (!empty($request_val_arr) && in_array($cus_field_options_value, $request_val_arr)) {
                                                    $checked = ' selected="selected"';
                                                }
                                                ?>
                                                <option value="<?php echo esc_html($cus_field_options_value); ?>" <?php echo ($checked) ?>><?php echo (apply_filters('wpml_translate_single_string', $cus_field['options']['label'][$cut_field_flag], 'Custom Fields', 'Dropdown Option Label - ' . $cus_field['options']['label'][$cut_field_flag], $lang_code)); ?></option>
                                                <?php
                                            } else {

                                                if ($cutsf_field_flag == 1) {
                                                    ?>
                                                    <option value=""><?php echo ($cus_field_label_arr); ?></option>
                                                    <?php
                                                }
                                                $custom_dropdown_selected = '';
                                                if (isset($_REQUEST[$query_str_var_name]) && $_REQUEST[$query_str_var_name] == $cus_field_options_value) {
                                                    $custom_dropdown_selected = ' selected="selected"';
                                                }
                                                ?>
                                                <option value="<?php echo esc_html($cus_field_options_value); ?>" <?php echo ($custom_dropdown_selected) ?>><?php echo (apply_filters('wpml_translate_single_string', $cus_field['options']['label'][$cut_field_flag], 'Custom Fields', 'Dropdown Option Label - ' . $cus_field['options']['label'][$cut_field_flag], $lang_code)); ?></option>
                                                <?php
                                                $cutsf_field_flag++;
                                            }
                                        }
                                        $number_option_flag ++;
                                        $cut_field_flag ++;
                                    }
                                    ?>
                                </select>
                            </div>
                        </li>
                        <?php
                    } else if ($cus_field['type'] == 'text' || $cus_field['type'] == 'textarea' || $cus_field['type'] == 'email') {
                        $text_field_req_val = isset($_REQUEST[$query_str_var_name]) ? $_REQUEST[$query_str_var_name] : '';
                        ?>
                        <li>
                            <input type="text" name="<?php echo esc_html($query_str_var_name); ?>" id="<?php echo esc_html($query_str_var_name); ?>" value="<?php echo esc_html($text_field_req_val); ?>" />
                        </li>
                        <?php
                    } else if ($cus_field['type'] == 'number') {
                        $number_field_req_val = isset($_REQUEST[$query_str_var_name]) ? $_REQUEST[$query_str_var_name] : '';
                        ?>
                        <li>
                            <input type="number" name="<?php echo esc_html($query_str_var_name); ?>" id="<?php echo esc_html($query_str_var_name); ?>" value="<?php echo esc_html($number_field_req_val); ?>" />
                        </li>
                        <?php
                    } else if ($cus_field['type'] == 'date') {
                        $fromdate_field_req_val = isset($_REQUEST['from-' . $query_str_var_name]) ? $_REQUEST['from-' . $query_str_var_name] : '';
                        $todate_field_req_val = isset($_REQUEST['to-' . $query_str_var_name]) ? $_REQUEST['to-' . $query_str_var_name] : '';
                        wp_enqueue_style('datetimepicker-style');
                        wp_enqueue_script('datetimepicker-script');
                        wp_enqueue_script('jquery-ui');
                        $cus_field_date_formate_arr = explode(" ", $cus_field['date-format']);
                        ?>
                        <li>
                            <script>
                                jQuery(document).ready(function () {
                                    jQuery("#from<?php echo esc_html($query_str_var_name); ?>").datetimepicker({
                                        format: "<?php echo esc_html($cus_field_date_formate_arr[0]); ?>",
                                        timepicker: false
                                    });
                                    jQuery("#to<?php echo esc_html($query_str_var_name); ?>").datetimepicker({
                                        format: "<?php echo esc_html($cus_field_date_formate_arr[0]); ?>",
                                        timepicker: false
                                    });
                                });
                            </script>
                            <input type="text" name="from-<?php echo esc_html($query_str_var_name); ?>" id="from<?php echo esc_html($query_str_var_name); ?>" value="<?php echo esc_html($fromdate_field_req_val); ?>" />
                            - 
                            <input type="text" name="to-<?php echo esc_html($query_str_var_name); ?>" id="to<?php echo esc_html($query_str_var_name); ?>" value="<?php echo esc_html($todate_field_req_val); ?>" />
                        </li>
                        <?php
                    } elseif ($cus_field['type'] == 'range') {

                        $range_min = $cus_field['min'];
                        $range_laps = $cus_field['laps'];
                        $range_interval = $cus_field['interval'];
                        $range_field_type = isset($cus_field['field-style']) ? $cus_field['field-style'] : 'simple'; //input, slider, input_slider

                        if (strpos($range_field_type, '-') !== FALSE) {
                            $range_field_type_arr = explode("_", $range_field_type);
                        } else {
                            $range_field_type_arr[0] = $range_field_type;
                        }
                        $range_flag = 0;
                        while (count($range_field_type_arr) > $range_flag) {
                            if ($range_field_type_arr[$range_flag] == 'simple') { // if input style
                                $filter_more_counter = 1;
                                $loop_flag = 1;
                                ?> 
                                <li>
                                    <div class="jobsearch-select-style">
                                        <select name="<?php echo esc_html($query_str_var_name); ?>" class="selectize-select" placeholder="<?php echo ($cus_field_label_arr); ?>">
                                            <?php
                                            while ($loop_flag <= $range_laps) {

                                                // main query array $args_count 
                                                $range_first = $range_min + 1;
                                                $range_seond = $range_min + $range_interval;

                                                $custom_slider_selected = '';
                                                if (isset($_REQUEST[$query_str_var_name]) && $_REQUEST[$query_str_var_name] == (($range_min + 1) . "-" . ($range_min + $range_interval))) {
                                                    $custom_slider_selected = ' selected="selected"';
                                                }
                                                if ($loop_flag == 1) {
                                                    ?>
                                                    <option value=""><?php echo ($cus_field_label_arr); ?></option>
                                                    <?php
                                                }
                                                ?>
                                                <option value="<?php echo esc_html(( ($range_min + 1) . "-" . ($range_min + $range_interval))); ?>" <?php echo ($custom_slider_selected) ?>><?php echo force_balance_tags(( ($range_min + 1) . " - " . ($range_min + $range_interval))); ?></option>
                                                <?php
                                                $range_min = $range_min + $range_interval;
                                                $loop_flag++;
                                                $filter_more_counter ++;
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </li>
                                <?php
                                if ($filter_more_counter > 6) {
                                    echo '<a href="javascript:void(0);" class="show-toggle-filter-list">' . esc_html__('+ see more', 'wp-jobsearch') . '</a>';
                                }
                            } elseif ($range_field_type_arr[$range_flag] == 'slider') { // if slider style 
                                wp_enqueue_style('jquery-ui');
                                wp_enqueue_script('jquery-ui');
                                $rand_id = rand(123, 1231231);
                                $range_field_max = $range_min;
                                $i = 0;
                                while ($range_laps > $i) {
                                    $range_field_max = $range_field_max + $range_interval;
                                    $i++;
                                }
                                $range_complete_str_first = "";
                                $range_complete_str_second = "";
                                $range_complete_str = '';
                                $range_complete_str_first = $range_min;
                                $range_complete_str_second = $range_field_max;
                                if (isset($_REQUEST[$query_str_var_name])) {
                                    $range_complete_str = $_REQUEST[$query_str_var_name];
                                    $range_complete_str_arr = explode("-", $range_complete_str);
                                    $range_complete_str_first = isset($range_complete_str_arr[0]) ? $range_complete_str_arr[0] : '';
                                    $range_complete_str_second = isset($range_complete_str_arr[1]) ? $range_complete_str_arr[1] : '';
                                }
                                ?>
                                <li>
                                    <input type="text" name="<?php echo esc_html($query_str_var_name) ?>" id="<?php echo esc_html($query_str_var_name . $rand_id) ?>" value="<?php echo esc_html($range_complete_str); ?>" readonly style="border:0; color:#f6931f; font-weight:bold;" />
                                    <div id="slider-range<?php echo esc_html($query_str_var_name . $rand_id) ?>"></div>
                                    <script>
                                        jQuery(document).ready(function () {



                                            jQuery("#slider-range<?php echo esc_html($query_str_var_name . $rand_id) ?>").slider({
                                                range: true,
                                                min: <?php echo absint($range_min); ?>,
                                                max: <?php echo absint($range_field_max); ?>,
                                                values: [<?php echo absint($range_complete_str_first); ?>, <?php echo absint($range_complete_str_second); ?>],
                                                slide: function (event, ui) {
                                                    jQuery("#<?php echo esc_html($query_str_var_name . $rand_id) ?>").val(ui.values[ 0 ] + "-" + ui.values[ 1 ]);
                                                },
                                                stop: function (event, ui) {
                                <?php echo force_balance_tags($submit_js_function_str); ?>;
                                                }
                                            });
                                            jQuery("#<?php echo esc_html($query_str_var_name . $rand_id) ?>").val(jQuery("#slider-range<?php echo esc_html($query_str_var_name . $rand_id) ?>").slider("values", 0) +
                                                    "-" + jQuery("#slider-range<?php echo esc_html($query_str_var_name . $rand_id) ?>").slider("values", 1));
                                        });
                                    </script>
                                </li>
                                <?php
                            }
                            $range_flag ++;
                        }
                    } elseif ($cus_field['type'] == 'salary' && $salary_onoff_switch == 'on') {

                        $job_salary_types = isset($jobsearch_plugin_options['job-salary-types']) ? $jobsearch_plugin_options['job-salary-types'] : '';

                        $salary_min = $cus_field['min'];
                        $salary_laps = $cus_field['laps'];
                        $salary_interval = $cus_field['interval'];
                        $salary_field_type = isset($cus_field['field-style']) ? $cus_field['field-style'] : 'simple'; //input, slider, input_slider

                        if (strpos($salary_field_type, '-') !== FALSE) {
                            $salary_field_type_arr = explode("_", $salary_field_type);
                        } else {
                            $salary_field_type_arr[0] = $salary_field_type;
                        }

                        // Salary Types
                        if (!empty($job_salary_types)) {
                            $slar_type_count = 1;
                            ?>
                            <li>
                                <div class="jobsearch-select-style">
                                    <select name="<?php echo esc_html($str_salary_type_name); ?>" class="selectize-select" placeholder="<?php esc_html_e('Salary Type', 'wp-josearch') ?>">
                                        <option value=""><?php esc_html_e('Salary Type', 'wp-josearch') ?></option>
                                        <?php
                                        foreach ($job_salary_types as $job_salary_type) {
                                            $job_salary_type = apply_filters('wpml_translate_single_string', $job_salary_type, 'JobSearch Options', 'Salary Type - ' . $job_salary_type, $lang_code);
                                            $slalary_type_selected = '';
                                            if (isset($_REQUEST[$str_salary_type_name]) && $_REQUEST[$str_salary_type_name] == 'type_' . $slar_type_count) {
                                                $slalary_type_selected = ' selected="selected"';
                                            }
                                            ?>
                                            <option value="type_<?php echo ($slar_type_count) ?>" <?php echo ($slalary_type_selected) ?>><?php echo ($job_salary_type); ?></option>
                                            <?php
                                            $slar_type_count++;
                                        }
                                        ?>
                                    </select>
                                </div>
                            </li>
                            <?php
                        }
                        //
                        ?>
                        <li>
                            <?php
                            $salary_flag = 0;
                            while (count($salary_field_type_arr) > $salary_flag) {
                                if ($salary_field_type_arr[$salary_flag] == 'simple') { // if input style
                                    $filter_more_counter = 1;
                                    ?>
                                    <div class="jobsearch-select-style">
                                        <select name="<?php echo esc_html($query_str_var_name); ?>" class="selectize-select" placeholder="<?php echo ($cus_field_label_arr); ?>">
                                            <?php
                                            $loop_flag = 1;
                                            while ($loop_flag <= $salary_laps) {

                                                // main query array $args_count 
                                                $salary_first = $salary_min + 1;
                                                $salary_seond = $salary_min + $salary_interval;

                                                $custom_slider_selected = '';
                                                if (isset($_REQUEST[$query_str_var_name]) && $_REQUEST[$query_str_var_name] == (($salary_min + 1) . "-" . ($salary_min + $salary_interval))) {
                                                    $custom_slider_selected = ' selected="selected"';
                                                }
                                                if ($loop_flag == 1) {
                                                    ?>
                                                    <option value=""><?php echo ($cus_field_label_arr); ?></option>
                                                    <?php
                                                }
                                                ?>
                                                <option value="<?php echo esc_html(( ($salary_min + 1) . "-" . ($salary_min + $salary_interval))); ?>" <?php echo ($custom_slider_selected) ?>><?php echo force_balance_tags(( ($salary_min + 1) . " - " . ($salary_min + $salary_interval))); ?></option>
                                                <?php
                                                $salary_min = $salary_min + $salary_interval;
                                                $loop_flag++;
                                                $filter_more_counter ++;
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <?php
                                } elseif ($salary_field_type_arr[$salary_flag] == 'slider') { // if slider style 
                                    wp_enqueue_style('jquery-ui');
                                    wp_enqueue_script('jquery-ui');
                                    $rand_id = rand(1231110, 9231231);
                                    $salary_field_max = $salary_min;
                                    $i = 0;
                                    while ($salary_laps > $i) {
                                        $salary_field_max = $salary_field_max + $salary_interval;
                                        $i++;
                                    }
                                    $salary_complete_str_first = "";
                                    $salary_complete_str_second = "";
                                    $salary_complete_str = '';
                                    $salary_complete_str_first = $salary_min;
                                    $salary_complete_str_second = $salary_field_max;
                                    if (isset($_REQUEST[$query_str_var_name])) {
                                        $salary_complete_str = $_REQUEST[$query_str_var_name];
                                        $salary_complete_str_arr = explode("-", $salary_complete_str);
                                        $salary_complete_str_first = isset($salary_complete_str_arr[0]) ? $salary_complete_str_arr[0] : '';
                                        $salary_complete_str_second = isset($salary_complete_str_arr[1]) ? $salary_complete_str_arr[1] : '';
                                    }
                                    ?>
                                    <div class="salary-filter-slider">
                                        <div class="filter-slider-range">
                                            <input type="text" name="<?php echo esc_html($query_str_var_name) ?>" id="<?php echo esc_html($query_str_var_name . $rand_id) ?>" value="<?php echo esc_html($salary_complete_str); ?>" readonly style="border:0; color:#f6931f; font-weight:bold;" />
                                        </div>
                                        <div id="slider-salary<?php echo esc_html($query_str_var_name . $rand_id) ?>"></div>
                                        <script>
                                            jQuery(document).ready(function () {

                                                jQuery("#slider-salary<?php echo esc_html($query_str_var_name . $rand_id) ?>").slider({
                                                    salary: true,
                                                    min: <?php echo absint($salary_min); ?>,
                                                    max: <?php echo absint($salary_field_max); ?>,
                                                    values: [<?php echo absint($salary_complete_str_first); ?>, <?php echo absint($salary_complete_str_second); ?>],
                                                    slide: function (event, ui) {
                                                        jQuery("#<?php echo esc_html($query_str_var_name . $rand_id) ?>").val(ui.values[ 0 ] + "-" + ui.values[ 1 ]);
                                                    },
                                                    stop: function (event, ui) {
                                <?php echo force_balance_tags($submit_js_function_str); ?>;
                                                    }
                                                });
                                                jQuery("#<?php echo esc_html($query_str_var_name . $rand_id) ?>").val(jQuery("#slider-salary<?php echo esc_html($query_str_var_name . $rand_id) ?>").slider("values", 0) +
                                                        "-" + jQuery("#slider-salary<?php echo esc_html($query_str_var_name . $rand_id) ?>").slider("values", 1));

                                            });
                                        </script>
                                    </div>
                                    <?php
                                }
                                $salary_flag ++;
                            }
                            ?>
                        </li>
                        <?php
                    }
                }
            }
        }
        $html .= ob_get_clean();
        return $html;
    }

    static function jobsearch_custom_fields_load_filter_array_html_callback($custom_field_entity = '', $filter_arr, $exclude_meta_key) {
        $field_db_slug = "jobsearch_custom_field_" . $custom_field_entity;

        $jobsearch_post_cus_fields = get_option($field_db_slug);
        if (is_array($jobsearch_post_cus_fields) && sizeof($jobsearch_post_cus_fields) > 0) {
            $custom_field_flag = 1;
            foreach ($jobsearch_post_cus_fields as $cus_fieldvar => $cus_field) {
                if ($cus_field['type'] == 'salary') {
                    $cus_field['enable-search'] = 'yes';
                }
                if (isset($cus_field['enable-search']) && $cus_field['enable-search'] == 'yes') {

                    if ($cus_field['type'] == 'salary') {
                        $query_str_var_name = 'jobsearch_field_job_salary';
                        $str_salary_type_name = 'job_salary_type';
                        if (isset($_REQUEST['jobsearch_field_candidate_salary'])) {
                            $query_str_var_name = 'jobsearch_field_candidate_salary';
                        }
                        if (isset($_REQUEST['candidate_salary_type'])) {
                            $str_salary_type_name = 'candidate_salary_type';
                        }
                    } else {
                        $query_str_var_name = trim(str_replace(' ', '', $cus_field['name']));
                    }

                    // only for date type field need to change field name
                    if ($exclude_meta_key != $query_str_var_name) {
                        if ($cus_field['type'] == 'date') {
                            if ($cus_field['type'] == 'date') {

                                $from_date = 'from-' . $query_str_var_name;
                                $to_date = 'to-' . $query_str_var_name;
                                if (isset($_REQUEST[$from_date]) && $_REQUEST[$from_date] != '') {
                                    $filter_arr[] = array(
                                        'key' => $query_str_var_name,
                                        'value' => strtotime($_REQUEST[$from_date]),
                                        'compare' => '>=',
                                    );
                                }
                                if (isset($_REQUEST[$to_date]) && $_REQUEST[$to_date] != '') {
                                    $filter_arr[] = array(
                                        'key' => $query_str_var_name,
                                        'value' => strtotime($_REQUEST[$to_date]),
                                        'compare' => '<=',
                                    );
                                }
                            }
                        } else if (isset($_REQUEST[$query_str_var_name]) && $_REQUEST[$query_str_var_name] != '') {

                            if ($cus_field['type'] == 'dropdown') {
                                if (isset($cus_field['multi']) && $cus_field['multi'] == 'yes') {
                                    $filter_multi_arr = array();
                                    $filter_multi_arr ['relation'] = 'OR';
                                    $dropdown_query_str_var_name = explode(",", $_REQUEST[$query_str_var_name]);
                                    foreach ($dropdown_query_str_var_name as $query_str_var_name_key) {
                                        if (isset($cus_field['post-multi']) && $cus_field['post-multi'] == 'yes') {
                                            $filter_multi_arr[] = array(
                                                'key' => $query_str_var_name,
                                                'value' => ($query_str_var_name_key),
                                                'compare' => 'LIKE',
                                            );
                                        } else {
                                            $filter_multi_arr[] = array(
                                                'key' => $query_str_var_name,
                                                'value' => $query_str_var_name_key,
                                                'compare' => 'LIKE',
                                            );
                                        }
                                    }
                                    $filter_arr[] = array(
                                        $filter_multi_arr
                                    );
                                } else {
                                    if (isset($cus_field['post-multi']) && $cus_field['post-multi'] == 'yes') {

                                        $filter_arr[] = array(
                                            'key' => $query_str_var_name,
                                            'value' => ($_REQUEST[$query_str_var_name]),
                                            'compare' => 'LIKE',
                                        );
                                    } else {
                                        $filter_arr[] = array(
                                            'key' => $query_str_var_name,
                                            'value' => $_REQUEST[$query_str_var_name],
                                            'compare' => '=',
                                        );
                                    }
                                }
                            } elseif ($cus_field['type'] == 'text' || $cus_field['type'] == 'email') {
                                if ($_REQUEST[$query_str_var_name] != '') {
                                    $filter_arr[] = array(
                                        'key' => $query_str_var_name,
                                        'value' => $_REQUEST[$query_str_var_name],
                                        'compare' => 'LIKE',
                                    );
                                }
                            } elseif ($cus_field['type'] == 'number') {
                                if ($_REQUEST[$query_str_var_name] != 0 && $_REQUEST[$query_str_var_name] != '') {
                                    $filter_arr[] = array(
                                        'key' => $query_str_var_name,
                                        'value' => $_REQUEST[$query_str_var_name],
                                        'compare' => '>=',
                                        'type' => 'numeric'
                                    );
                                }
                            } elseif ($cus_field['type'] == 'range') {
                                $ranges_str_arr = explode("-", $_REQUEST[$query_str_var_name]);
                                if (!isset($ranges_str_arr[1])) {
                                    $ranges_str_arr = explode("-", $ranges_str_arr[0]);
                                }
                                $range_first = $ranges_str_arr[0];
                                $range_seond = $ranges_str_arr[1];
                                $filter_arr[] = array(
                                    'key' => $query_str_var_name,
                                    'value' => $range_first,
                                    'compare' => '>=',
                                    'type' => 'numeric'
                                );
                                $filter_arr[] = array(
                                    'key' => $query_str_var_name,
                                    'value' => $range_seond,
                                    'compare' => '<=',
                                    'type' => 'numeric'
                                );
                            }
                        }
                        if ($cus_field['type'] == 'salary') {

                            if (isset($_REQUEST[$query_str_var_name]) && $_REQUEST[$query_str_var_name] != '') {
                                $salarys_str_arr = explode("-", $_REQUEST[$query_str_var_name]);
                                if (!isset($salarys_str_arr[1])) {
                                    $salarys_str_arr = explode("-", $salarys_str_arr[0]);
                                }
                                $salary_first = isset($salarys_str_arr[0]) ? $salarys_str_arr[0] : '';
                                $salary_seond = isset($salarys_str_arr[1]) ? $salarys_str_arr[1] : '';
                                $filter_arr[] = array(
                                    'key' => $query_str_var_name,
                                    'value' => $salary_first,
                                    'compare' => '>=',
                                    'type' => 'numeric'
                                );
                                $filter_arr[] = array(
                                    'key' => $query_str_var_name,
                                    'value' => $salary_seond,
                                    'compare' => '<=',
                                    'type' => 'numeric'
                                );
                            }

                            $salary_type_str = isset($_REQUEST[$str_salary_type_name]) ? $_REQUEST[$str_salary_type_name] : '';
                            if ($salary_type_str != '') {
                                $filter_arr[] = array(
                                    'key' => 'jobsearch_field_' . $str_salary_type_name,
                                    'value' => $salary_type_str,
                                    'compare' => '=',
                                );
                            }
                            //
                        }
                    }
                }
                $custom_field_flag ++;
            }
        }
        return $filter_arr;
    }

    static function jobsearch_custom_fields_load_precentage_array_callback($custom_field_entity = '', $skills_array = array()) {
        $field_db_slug = "jobsearch_custom_field_" . $custom_field_entity;

        $jobsearch_post_cus_fields = get_option($field_db_slug);
        if (is_array($jobsearch_post_cus_fields) && sizeof($jobsearch_post_cus_fields) > 0) {
            $custom_fields_array = array();

            $skills_array['custom_fields']['name'] = esc_html__('Custom Fields', 'wp-jobsearch');
            foreach ($jobsearch_post_cus_fields as $job_field) {
                $meta_key = isset($job_field['name']) ? $job_field['name'] : '';
                $field_label = isset($job_field['label']) ? $job_field['label'] : '';
                if ($meta_key != '' && $field_label != '') {
                    $custom_fields_array[$meta_key] = array(
                        'name' => $field_label,
                    );
                }
            }
            $skills_array['custom_fields']['list'] = $custom_fields_array;
            if (empty($custom_fields_array)) {
                unset($skills_array['custom_fields']);
            }
        }
        return $skills_array;
    }

}

// class Jobsearch_CustomFieldLoad 
$Jobsearch_CustomFieldLoad_obj = new Jobsearch_CustomFieldLoad();
global $Jobsearch_CustomFieldLoad_obj;
