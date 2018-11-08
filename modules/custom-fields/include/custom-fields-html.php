<?php
/*
  Class : CustomFieldHTML
 */


// this is an include only WP file
if (!defined('ABSPATH')) {
    die;
}

// main plugin class
class Jobsearch_CustomFieldHTML {

// hook things up
    public function __construct() {

        add_filter('jobsearch_custom_field_text_html', array($this, 'jobsearch_custom_field_text_html_callback'), 1, 3);
        add_filter('jobsearch_custom_field_dropdown_html', array($this, 'jobsearch_custom_field_dropdown_html_callback'), 1, 3);
        add_filter('jobsearch_custom_field_heading_html', array($this, 'jobsearch_custom_field_heading_html_callback'), 1, 3);
        add_filter('jobsearch_custom_field_textarea_html', array($this, 'jobsearch_custom_field_textarea_html_callback'), 1, 3);
        add_filter('jobsearch_custom_field_email_html', array($this, 'jobsearch_custom_field_email_html_callback'), 1, 3);
        add_filter('jobsearch_custom_field_number_html', array($this, 'jobsearch_custom_field_number_html_callback'), 1, 3);
        add_filter('jobsearch_custom_field_date_html', array($this, 'jobsearch_custom_field_date_html_callback'), 1, 3);
        add_filter('jobsearch_custom_field_range_html', array($this, 'jobsearch_custom_field_range_html_callback'), 1, 3);
        add_filter('jobsearch_custom_field_salary_html', array($this, 'jobsearch_custom_field_salary_html_callback'), 1, 3);
        add_filter('jobsearch_custom_field_actions_html', array($this, 'jobsearch_custom_field_actions_html_callback'), 1, 4);
    }

    static function jobsearch_custom_field_heading_html_callback($html, $global_custom_field_counter, $field_data) {
        $field_counter = $global_custom_field_counter;
        ob_start();
        $rand = $field_counter;
        $field_for_non_reg_user = isset($field_data['non_reg_user']) ? $field_data['non_reg_user'] : '';
        $heading_field_label = isset($field_data['label']) ? $field_data['label'] : '';
        ?>
        <div class="jobsearch-custom-filed-container jobsearch-custom-filed-heading-container">
            <div class="field-intro">
                <span class="drag-handle"><i class="dashicons dashicons-editor-textcolor" aria-hidden="true"></i></span>
                <?php $field_dyn_name = $heading_field_label != '' ? '<b>(' . $heading_field_label . ')</b>' : '' ?>
                <a href="javascript:void(0);" class="heading-field<?php echo esc_html($rand); ?>" ><?php echo wp_kses(sprintf(__('Heading %s', 'wp-jobsearch'), $field_dyn_name), array('b' => array())); ?></a>
            </div>
            <div class="field-data" id="heading-field-wraper<?php echo esc_html($rand); ?>" style="display:none;">
                <input type="hidden" name="jobsearch-custom-fields-type[]" value="heading" />
                <input type="hidden" name="jobsearch-custom-fields-id[]" value="<?php echo esc_html($field_counter); ?>" />
                
                <label>
                    <?php echo esc_html__('For Register/Non-Register Member', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <select name="jobsearch-custom-fields-heading[non_reg_user][]">
                        <option <?php if ($field_for_non_reg_user == 'default') echo ('selected="selected"'); ?> value="default"><?php echo esc_html__('Default', 'wp-jobsearch'); ?></option>
                        <option <?php if ($field_for_non_reg_user == 'for_reg') echo ('selected="selected"'); ?> value="for_reg"><?php echo esc_html__('Register User Only', 'wp-jobsearch'); ?></option> 
                    </select>
                </div>
                
                <label>
                    <?php echo esc_html__('Label', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <input name="jobsearch-custom-fields-heading[label][]" value="<?php echo esc_html($heading_field_label); ?>" />
                </div>
            </div>
            <script>
                jQuery(document).ready(function () {
                    jQuery(document).on('click', '.heading-field<?php echo esc_html($rand); ?>', function () {
                        jQuery('#heading-field-wraper<?php echo esc_html($rand); ?>').slideToggle("slow");
                    });
                });
            </script>
        </div>
        <?php
        $html .= ob_get_clean();

        return $html;
    }

    static function jobsearch_custom_field_text_html_callback($html, $global_custom_field_counter, $field_data) {

        $field_counter = $global_custom_field_counter;
        ob_start();
        $rand = $field_counter;
        $field_for_non_reg_user = isset($field_data['non_reg_user']) ? $field_data['non_reg_user'] : '';
        $text_field_name = isset($field_data['name']) ? $field_data['name'] : '';
        $text_field_required = isset($field_data['required']) ? $field_data['required'] : '';
        $text_field_label = isset($field_data['label']) ? $field_data['label'] : '';
        $text_field_placeholder = isset($field_data['placeholder']) ? $field_data['placeholder'] : '';
        $text_field_classes = isset($field_data['classes']) ? $field_data['classes'] : '';
        $text_field_enable_search = isset($field_data['enable-search']) ? $field_data['enable-search'] : '';
        $text_field_icon = isset($field_data['icon']) ? $field_data['icon'] : '';
        $text_field_collapse_search = isset($field_data['collapse-search']) ? $field_data['collapse-search'] : '';
        ?>
        <div class="jobsearch-custom-filed-container jobsearch-custom-filed-text-container">
            <div class="field-intro">
                <span class="drag-handle"><i class="dashicons dashicons-media-text" aria-hidden="true"></i></span>
                <?php $field_dyn_name = $text_field_label != '' ? '<b>(' . $text_field_label . ')</b>' : '' ?>
                <a href="javascript:void(0);" class="text-field<?php echo esc_html($rand); ?>" ><?php echo wp_kses(sprintf(__('Text Field %s', 'wp-jobsearch'), $field_dyn_name), array('b' => array())); ?></a>
            </div>
            <?php //var_dump($field_data); ?>
            <div class="field-data" id="text-field-wraper<?php echo esc_html($rand); ?>" style="display:none;">
                <input type="hidden" name="jobsearch-custom-fields-type[]" value="text" />
                <input type="hidden" name="jobsearch-custom-fields-id[]" value="<?php echo esc_html($field_counter); ?>" />

                <label>
                    <?php echo esc_html__('For Register/Non-Register Member', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <select name="jobsearch-custom-fields-text[non_reg_user][]">
                        <option <?php if ($field_for_non_reg_user == 'default') echo ('selected="selected"'); ?> value="default"><?php echo esc_html__('Default', 'wp-jobsearch'); ?></option>
                        <option <?php if ($field_for_non_reg_user == 'for_reg') echo ('selected="selected"'); ?> value="for_reg"><?php echo esc_html__('Register User Only', 'wp-jobsearch'); ?></option> 
                    </select>
                </div>
                <?php do_action('jobsearch_custom_fields_text_plus_1', $field_counter, $field_data, 'jobsearch-custom-fields-text') ?>
                <label>
                    <?php echo esc_html__('Label', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <input name="jobsearch-custom-fields-text[label][]" value="<?php echo esc_html($text_field_label); ?>" />
                </div>

                <label>
                    <?php echo esc_html__('Name', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <input class="check-name-availability" name="jobsearch-custom-fields-text[name][]" value="<?php echo esc_html($text_field_name); ?>" />
                    <span class="available-msg"><i class="dashicons dashicons-dismiss"></i></span>
                </div>

                <label>
                    <?php echo esc_html__('Placeholder', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <input name="jobsearch-custom-fields-text[placeholder][]" value="<?php echo esc_html($text_field_placeholder); ?>" />
                </div>

                <label>
                    <?php echo esc_html__('Classes', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <input name="jobsearch-custom-fields-text[classes][]" value="<?php echo esc_html($text_field_classes); ?>" />
                </div>

                <label>
                    <?php echo esc_html__('Required', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <select name="jobsearch-custom-fields-text[required][]" >
                        <option <?php if ($text_field_required == 'no') echo esc_html('selected'); ?> value="no"><?php echo esc_html__('No', 'wp-jobsearch'); ?></option>
                        <option <?php if ($text_field_required == 'yes') echo esc_html('selected'); ?> value="yes"><?php echo esc_html__('Yes', 'wp-jobsearch'); ?></option> 
                    </select>
                </div>

                <label>
                    <?php echo esc_html__('Enable in Search', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <select name="jobsearch-custom-fields-text[enable-search][]" >
                        <option <?php if ($text_field_enable_search == 'no') echo esc_html('selected'); ?> value="no"><?php echo esc_html__('No', 'wp-jobsearch'); ?></option>
                        <option <?php if ($text_field_enable_search == 'yes') echo esc_html('selected'); ?> value="yes"><?php echo esc_html__('Yes', 'wp-jobsearch'); ?></option>
                    </select>
                </div>
                <label>
                    <?php echo esc_html__('Collapse in Search', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <select name="jobsearch-custom-fields-text[collapse-search][]" >
                        <option <?php if ($text_field_collapse_search == 'no') echo esc_html('selected'); ?> value="no"><?php echo esc_html__('No', 'wp-jobsearch'); ?></option>
                        <option <?php if ($text_field_collapse_search == 'yes') echo esc_html('selected'); ?> value="yes"><?php echo esc_html__('Yes', 'wp-jobsearch'); ?></option>
                    </select>
                </div>

                <label>
                    <?php echo esc_html__('Icon', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <?php
                    $icon_id = rand(1000000, 99999999);

                    echo jobsearch_icon_picker($text_field_icon, $icon_id, 'jobsearch-custom-fields-text[icon][]');
                    ?>
                </div>
            </div>
            <script>
                jQuery(document).ready(function () {
                    jQuery(document).on('click', '.text-field<?php echo esc_html($rand); ?>', function () {
                        jQuery('#text-field-wraper<?php echo esc_html($rand); ?>').slideToggle("slow");
                    });
                });
            </script>
        </div>
        <?php
        $html .= ob_get_clean();

        return $html;
    }

    static function jobsearch_custom_field_email_html_callback($html, $global_custom_field_counter, $field_data) {
        $field_counter = $global_custom_field_counter;
        ob_start();
        $rand = $field_counter;
        $field_for_non_reg_user = isset($field_data['non_reg_user']) ? $field_data['non_reg_user'] : '';
        $email_field_name = isset($field_data['name']) ? $field_data['name'] : '';
        $email_field_required = isset($field_data['required']) ? $field_data['required'] : '';
        $email_field_label = isset($field_data['label']) ? $field_data['label'] : '';
        $email_field_placeholder = isset($field_data['placeholder']) ? $field_data['placeholder'] : '';
        $email_field_classes = isset($field_data['classes']) ? $field_data['classes'] : '';
        $email_field_enable_search = isset($field_data['enable-search']) ? $field_data['enable-search'] : '';
        $email_field_icon = isset($field_data['icon']) ? $field_data['icon'] : '';
        $email_field_collapse_search = isset($field_data['collapse-search']) ? $field_data['collapse-search'] : '';
        ?>
        <div class="jobsearch-custom-filed-container jobsearch-custom-filed-email-container">
            <div class="field-intro">
                <span class="drag-handle"><i class="dashicons dashicons-email-alt" aria-hidden="true"></i></span>
                <?php $field_dyn_name = $email_field_label != '' ? '<b>(' . $email_field_label . ')</b>' : '' ?>
                <a href="javascript:void(0);" class="email-field<?php echo esc_html($rand); ?>" ><?php echo wp_kses(sprintf(__('Email Field %s', 'wp-jobsearch'), $field_dyn_name), array('b' => array())); ?></a>
            </div>
            <div class="field-data" id="email-field-wraper<?php echo esc_html($rand); ?>" style="display:none;">
                <input type="hidden" name="jobsearch-custom-fields-type[]" value="email" />
                <input type="hidden" name="jobsearch-custom-fields-id[]" value="<?php echo esc_html($field_counter); ?>" />
                
                <label>
                    <?php echo esc_html__('For Register/Non-Register Member', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <select name="jobsearch-custom-fields-email[non_reg_user][]">
                        <option <?php if ($field_for_non_reg_user == 'default') echo ('selected="selected"'); ?> value="default"><?php echo esc_html__('Default', 'wp-jobsearch'); ?></option>
                        <option <?php if ($field_for_non_reg_user == 'for_reg') echo ('selected="selected"'); ?> value="for_reg"><?php echo esc_html__('Register User Only', 'wp-jobsearch'); ?></option> 
                    </select>
                </div>
                <?php do_action('jobsearch_custom_fields_email_plus_1', $field_counter, $field_data, 'jobsearch-custom-fields-email') ?>
                <label>
                    <?php echo esc_html__('Label', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <input name="jobsearch-custom-fields-email[label][]" value="<?php echo esc_html($email_field_label); ?>" />
                </div>

                <label>
                    <?php echo esc_html__('Name', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <input class="check-name-availability" name="jobsearch-custom-fields-email[name][]" value="<?php echo esc_html($email_field_name); ?>" />
                    <span class="available-msg"><i class="dashicons dashicons-dismiss"></i></span>
                </div>

                <label>
                    <?php echo esc_html__('Placeholder', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <input name="jobsearch-custom-fields-email[placeholder][]" value="<?php echo esc_html($email_field_placeholder); ?>" />
                </div>

                <label>
                    <?php echo esc_html__('Classes', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <input name="jobsearch-custom-fields-email[classes][]" value="<?php echo esc_html($email_field_classes); ?>" />
                </div>

                <label>
                    <?php echo esc_html__('Required', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <select name="jobsearch-custom-fields-email[required][]" >
                        <option <?php if ($email_field_required == 'no') echo esc_html('selected'); ?> value="no"><?php echo esc_html__('No', 'wp-jobsearch'); ?></option>
                        <option <?php if ($email_field_required == 'yes') echo esc_html('selected'); ?> value="yes"><?php echo esc_html__('Yes', 'wp-jobsearch'); ?></option>
                    </select>
                </div>

                <label>
                    <?php echo esc_html__('Enable in Search', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <select name="jobsearch-custom-fields-email[enable-search][]" >
                        <option <?php if ($email_field_enable_search == 'no') echo esc_html('selected'); ?> value="no"><?php echo esc_html__('No', 'wp-jobsearch'); ?></option>
                        <option <?php if ($email_field_enable_search == 'yes') echo esc_html('selected'); ?> value="yes"><?php echo esc_html__('Yes', 'wp-jobsearch'); ?></option>
                    </select>
                </div>
                <label>
                    <?php echo esc_html__('Collapse in Search', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <select name="jobsearch-custom-fields-email[collapse-search][]" >
                        <option <?php if ($email_field_collapse_search == 'no') echo esc_html('selected'); ?> value="no"><?php echo esc_html__('No', 'wp-jobsearch'); ?></option>
                        <option <?php if ($email_field_collapse_search == 'yes') echo esc_html('selected'); ?> value="yes"><?php echo esc_html__('Yes', 'wp-jobsearch'); ?></option>
                    </select>
                </div>

                <label>
                    <?php echo esc_html__('Icon', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <?php
                    $icon_id = rand(1000000, 99999999);

                    echo jobsearch_icon_picker($email_field_icon, $icon_id, 'jobsearch-custom-fields-email[icon][]');
                    ?>
                </div>
            </div>
            <script>
                jQuery(document).ready(function () {
                    jQuery(document).on('click', '.email-field<?php echo esc_html($rand); ?>', function () {
                        jQuery('#email-field-wraper<?php echo esc_html($rand); ?>').slideToggle("slow");
                    });
                });
            </script>
        </div>
        <?php
        $html .= ob_get_clean();

        return $html;
    }

    static function jobsearch_custom_field_number_html_callback($html, $global_custom_field_counter, $field_data) {
        $field_counter = $global_custom_field_counter;
        ob_start();
        $rand = $field_counter;
        $field_for_non_reg_user = isset($field_data['non_reg_user']) ? $field_data['non_reg_user'] : '';
        $number_field_name = isset($field_data['name']) ? $field_data['name'] : '';
        $number_field_required = isset($field_data['required']) ? $field_data['required'] : '';
        $number_field_label = isset($field_data['label']) ? $field_data['label'] : '';
        $number_field_placeholder = isset($field_data['placeholder']) ? $field_data['placeholder'] : '';
        $number_field_classes = isset($field_data['classes']) ? $field_data['classes'] : '';
        $number_field_enable_search = isset($field_data['enable-search']) ? $field_data['enable-search'] : '';
        $number_field_icon = isset($field_data['icon']) ? $field_data['icon'] : '';
        $number_field_collapse_search = isset($field_data['collapse-search']) ? $field_data['collapse-search'] : '';
        ?>
        <div class="jobsearch-custom-filed-container jobsearch-custom-filed-number-container">
            <div class="field-intro">
                <span class="drag-handle"><i class="dashicons dashicons-editor-ol" aria-hidden="true"></i></span>
                <?php $field_dyn_name = $number_field_label != '' ? '<b>(' . $number_field_label . ')</b>' : '' ?>
                <a href="javascript:void(0);" class="number-field<?php echo esc_html($rand); ?>" ><?php echo wp_kses(sprintf(__('Number Field %s', 'wp-jobsearch'), $field_dyn_name), array('b' => array())); ?></a>
            </div>
            <div class="field-data" id="number-field-wraper<?php echo esc_html($rand); ?>" style="display:none;">
                <input type="hidden" name="jobsearch-custom-fields-type[]" value="number" />
                <input type="hidden" name="jobsearch-custom-fields-id[]" value="<?php echo esc_html($field_counter); ?>" />
                
                <label>
                    <?php echo esc_html__('For Register/Non-Register Member', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <select name="jobsearch-custom-fields-number[non_reg_user][]">
                        <option <?php if ($field_for_non_reg_user == 'default') echo ('selected="selected"'); ?> value="default"><?php echo esc_html__('Default', 'wp-jobsearch'); ?></option>
                        <option <?php if ($field_for_non_reg_user == 'for_reg') echo ('selected="selected"'); ?> value="for_reg"><?php echo esc_html__('Register User Only', 'wp-jobsearch'); ?></option> 
                    </select>
                </div>
                <?php do_action('jobsearch_custom_fields_number_plus_1', $field_counter, $field_data, 'jobsearch-custom-fields-number') ?>
                <label>
                    <?php echo esc_html__('Label', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <input name="jobsearch-custom-fields-number[label][]" value="<?php echo esc_html($number_field_label); ?>" />
                </div>

                <label>
                    <?php echo esc_html__('Name', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <input class="check-name-availability" name="jobsearch-custom-fields-number[name][]" value="<?php echo esc_html($number_field_name); ?>" />
                    <span class="available-msg"><i class="dashicons dashicons-dismiss"></i></span>
                </div>

                <label>
                    <?php echo esc_html__('Placeholder', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <input name="jobsearch-custom-fields-number[placeholder][]" value="<?php echo esc_html($number_field_placeholder); ?>" />
                </div>

                <label>
                    <?php echo esc_html__('Classes', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <input name="jobsearch-custom-fields-number[classes][]" value="<?php echo esc_html($number_field_classes); ?>" />
                </div>

                <label>
                    <?php echo esc_html__('Required', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <select name="jobsearch-custom-fields-number[required][]" >
                        <option <?php if ($number_field_required == 'no') echo esc_html('selected'); ?> value="no"><?php echo esc_html__('No', 'wp-jobsearch'); ?></option>
                        <option <?php if ($number_field_required == 'yes') echo esc_html('selected'); ?> value="yes"><?php echo esc_html__('Yes', 'wp-jobsearch'); ?></option> 
                    </select>
                </div>

                <label>
                    <?php echo esc_html__('Enable in Search', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <select name="jobsearch-custom-fields-number[enable-search][]" >
                        <option <?php if ($number_field_enable_search == 'no') echo esc_html('selected'); ?> value="no"><?php echo esc_html__('No', 'wp-jobsearch'); ?></option>
                        <option <?php if ($number_field_enable_search == 'yes') echo esc_html('selected'); ?> value="yes"><?php echo esc_html__('Yes', 'wp-jobsearch'); ?></option> 
                    </select>
                </div>
                <label>
                    <?php echo esc_html__('Collapse in Search', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <select name="jobsearch-custom-fields-number[collapse-search][]" >
                        <option <?php if ($number_field_collapse_search == 'no') echo esc_html('selected'); ?> value="no"><?php echo esc_html__('No', 'wp-jobsearch'); ?></option>
                        <option <?php if ($number_field_collapse_search == 'yes') echo esc_html('selected'); ?> value="yes"><?php echo esc_html__('Yes', 'wp-jobsearch'); ?></option> 
                    </select>
                </div>

                <label>
                    <?php echo esc_html__('Icon', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <?php
                    $icon_id = rand(1000000, 99999999);

                    echo jobsearch_icon_picker($number_field_icon, $icon_id, 'jobsearch-custom-fields-number[icon][]');
                    ?>
                </div>
            </div>
            <script>
                jQuery(document).ready(function () {
                    jQuery(document).on('click', '.number-field<?php echo esc_html($rand); ?>', function () {
                        jQuery('#number-field-wraper<?php echo esc_html($rand); ?>').slideToggle("slow");
                    });
                });
            </script>
        </div>
        <?php
        $html .= ob_get_clean();

        return $html;
    }

    static function jobsearch_custom_field_date_html_callback($html, $global_custom_field_counter, $field_data) {
        $field_counter = $global_custom_field_counter;
        ob_start();
        
        $rand = $field_counter;
        $field_for_non_reg_user = isset($field_data['non_reg_user']) ? $field_data['non_reg_user'] : '';
        $date_field_name = isset($field_data['name']) ? $field_data['name'] : '';
        $date_field_required = isset($field_data['required']) ? $field_data['required'] : '';
        $date_field_label = isset($field_data['label']) ? $field_data['label'] : '';
        $date_field_placeholder = isset($field_data['placeholder']) ? $field_data['placeholder'] : '';
        $date_field_date_format = isset($field_data['date-format']) ? $field_data['date-format'] : '';
        $date_field_classes = isset($field_data['classes']) ? $field_data['classes'] : '';
        $date_field_enable_search = isset($field_data['enable-search']) ? $field_data['enable-search'] : '';
        $date_field_icon = isset($field_data['icon']) ? $field_data['icon'] : '';
        $date_field_collapse_search = isset($field_data['collapse-search']) ? $field_data['collapse-search'] : '';
        ?>
        <div class="jobsearch-custom-filed-container jobsearch-custom-filed-date-container">
            <div class="field-intro">
                <span class="drag-handle"><i class="dashicons dashicons-calendar-alt" aria-hidden="true"></i></span>
                <?php $field_dyn_name = $date_field_label != '' ? '<b>(' . $date_field_label . ')</b>' : '' ?>
                <a href="javascript:void(0);" class="date-field<?php echo esc_html($rand); ?>" ><?php echo wp_kses(sprintf(__('Date Field %s', 'wp-jobsearch'), $field_dyn_name), array('b' => array())); ?></a>
            </div>
            <div class="field-data" id="date-field-wraper<?php echo esc_html($rand); ?>" style="display:none;">
                <input type="hidden" name="jobsearch-custom-fields-type[]" value="date" />
                <input type="hidden" name="jobsearch-custom-fields-id[]" value="<?php echo esc_html($field_counter); ?>" />
                
                <label>
                    <?php echo esc_html__('For Register/Non-Register Member', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <select name="jobsearch-custom-fields-date[non_reg_user][]">
                        <option <?php if ($field_for_non_reg_user == 'default') echo ('selected="selected"'); ?> value="default"><?php echo esc_html__('Default', 'wp-jobsearch'); ?></option>
                        <option <?php if ($field_for_non_reg_user == 'for_reg') echo ('selected="selected"'); ?> value="for_reg"><?php echo esc_html__('Register User Only', 'wp-jobsearch'); ?></option> 
                    </select>
                </div>
                <?php do_action('jobsearch_custom_fields_date_plus_1', $field_counter, $field_data, 'jobsearch-custom-fields-date') ?>
                <label>
                    <?php echo esc_html__('Label', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <input name="jobsearch-custom-fields-date[label][]" value="<?php echo esc_html($date_field_label); ?>" />
                </div>

                <label>
                    <?php echo esc_html__('Name', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <input class="check-name-availability" name="jobsearch-custom-fields-date[name][]" value="<?php echo esc_html($date_field_name); ?>" />
                    <span class="available-msg"><i class="dashicons dashicons-dismiss"></i></span>
                </div>

                <label>
                    <?php echo esc_html__('Placeholder', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <input name="jobsearch-custom-fields-date[placeholder][]" value="<?php echo esc_html($date_field_placeholder); ?>" />
                </div>

                <label>
                    <?php echo esc_html__('Date Fromat', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <input type="text" name="jobsearch-custom-fields-date[date-format][]" value="<?php echo esc_html($date_field_date_format); ?>" />
                </div>

                <label>
                    <?php echo esc_html__('Classes', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <input name="jobsearch-custom-fields-date[classes][]" value="<?php echo esc_html($date_field_classes); ?>" />
                </div>

                <label>
                    <?php echo esc_html__('Required', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <select name="jobsearch-custom-fields-date[required][]" >
                        <option <?php if ($date_field_required == 'no') echo esc_html('selected'); ?> value="no"><?php echo esc_html__('No', 'wp-jobsearch'); ?></option>
                        <option <?php if ($date_field_required == 'yes') echo esc_html('selected'); ?> value="yes"><?php echo esc_html__('Yes', 'wp-jobsearch'); ?></option> 
                    </select>
                </div>

                <label>
                    <?php echo esc_html__('Enable in Search', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <select name="jobsearch-custom-fields-date[enable-search][]" >
                        <option <?php if ($date_field_enable_search == 'no') echo esc_html('selected'); ?> value="no"><?php echo esc_html__('No', 'wp-jobsearch'); ?></option>
                        <option <?php if ($date_field_enable_search == 'yes') echo esc_html('selected'); ?> value="yes"><?php echo esc_html__('Yes', 'wp-jobsearch'); ?></option> 
                    </select>
                </div>
                <label>
                    <?php echo esc_html__('Collapse in Search', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <select name="jobsearch-custom-fields-date[collapse-search][]" >
                        <option <?php if ($date_field_collapse_search == 'no') echo esc_html('selected'); ?> value="no"><?php echo esc_html__('No', 'wp-jobsearch'); ?></option>
                        <option <?php if ($date_field_collapse_search == 'yes') echo esc_html('selected'); ?> value="yes"><?php echo esc_html__('Yes', 'wp-jobsearch'); ?></option> 
                    </select>
                </div>

                <label>
                    <?php echo esc_html__('Icon', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <?php
                    $icon_id = rand(1000000, 99999999);

                    echo jobsearch_icon_picker($date_field_icon, $icon_id, 'jobsearch-custom-fields-date[icon][]');
                    ?>
                </div>
            </div>
            <script>
                jQuery(document).ready(function () {
                    jQuery(document).on('click', '.date-field<?php echo esc_html($rand); ?>', function () {
                        jQuery('#date-field-wraper<?php echo esc_html($rand); ?>').slideToggle("slow");
                    });
                });
            </script>
        </div>
        <?php
        $html .= ob_get_clean();

        return $html;
    }

    static function jobsearch_custom_field_range_html_callback($html, $global_custom_field_counter, $field_data) {
        $field_counter = $global_custom_field_counter;
        ob_start();
        $rand = $field_counter;
        $field_for_non_reg_user = isset($field_data['non_reg_user']) ? $field_data['non_reg_user'] : '';
        $range_field_name = isset($field_data['name']) ? $field_data['name'] : '';
        $range_field_required = isset($field_data['required']) ? $field_data['required'] : '';
        $range_field_label = isset($field_data['label']) ? $field_data['label'] : '';
        $range_field_placeholder = isset($field_data['placeholder']) ? $field_data['placeholder'] : '';
        $range_field_classes = isset($field_data['classes']) ? $field_data['classes'] : '';
        $range_field_field_style = isset($field_data['field-style']) ? $field_data['field-style'] : '';
        $range_field_min = isset($field_data['min']) ? $field_data['min'] : '';
        $range_field_laps = isset($field_data['laps']) ? $field_data['laps'] : '';
        $range_field_interval = isset($field_data['interval']) ? $field_data['interval'] : '';
        $range_field_enable_search = isset($field_data['enable-search']) ? $field_data['enable-search'] : '';
        $range_field_icon = isset($field_data['icon']) ? $field_data['icon'] : '';
        $range_field_collapse_search = isset($field_data['collapse-search']) ? $field_data['collapse-search'] : '';
        ?>
        <div class="jobsearch-custom-filed-container jobsearch-custom-filed-range-container">
            <div class="field-intro">
                <span class="drag-handle"><i class="dashicons dashicons-image-flip-horizontal" aria-hidden="true"></i></span>
                <?php $field_dyn_name = $range_field_label != '' ? '<b>(' . $range_field_label . ')</b>' : '' ?>
                <a href="javascript:void(0);" class="range-field<?php echo esc_html($rand); ?>" ><?php echo wp_kses(sprintf(__('Range Field %s', 'wp-jobsearch'), $field_dyn_name), array('b' => array())); ?></a>
            </div>
            <div class="field-data" id="range-field-wraper<?php echo esc_html($rand); ?>" style="display:none;">
                <input type="hidden" name="jobsearch-custom-fields-type[]" value="range" />
                <input type="hidden" name="jobsearch-custom-fields-id[]" value="<?php echo esc_html($field_counter); ?>" />
                
                <label>
                    <?php echo esc_html__('For Register/Non-Register Member', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <select name="jobsearch-custom-fields-range[non_reg_user][]">
                        <option <?php if ($field_for_non_reg_user == 'default') echo ('selected="selected"'); ?> value="default"><?php echo esc_html__('Default', 'wp-jobsearch'); ?></option>
                        <option <?php if ($field_for_non_reg_user == 'for_reg') echo ('selected="selected"'); ?> value="for_reg"><?php echo esc_html__('Register User Only', 'wp-jobsearch'); ?></option> 
                    </select>
                </div>
                <?php do_action('jobsearch_custom_fields_range_plus_1', $field_counter, $field_data, 'jobsearch-custom-fields-range') ?>
                <label>
                    <?php echo esc_html__('Label', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <input name="jobsearch-custom-fields-range[label][]" value="<?php echo esc_html($range_field_label); ?>" />
                </div>

                <label>
                    <?php echo esc_html__('Name', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <input class="check-name-availability" name="jobsearch-custom-fields-range[name][]" value="<?php echo esc_html($range_field_name); ?>" />
                    <span class="available-msg"><i class="dashicons dashicons-dismiss"></i></span>
                </div>

                <label>
                    <?php echo esc_html__('Placeholder', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <input name="jobsearch-custom-fields-range[placeholder][]" value="<?php echo esc_html($range_field_placeholder); ?>" />
                </div>

                <label>
                    <?php echo esc_html__('Min', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <input name="jobsearch-custom-fields-range[min][]" value="<?php echo esc_html($range_field_min); ?>" />
                </div>

                <label>
                    <?php echo esc_html__('Total Laps', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <input name="jobsearch-custom-fields-range[laps][]" value="<?php echo esc_html($range_field_laps); ?>" />
                </div>

                <label>
                    <?php echo esc_html__('Interval', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <input name="jobsearch-custom-fields-range[interval][]" value="<?php echo esc_html($range_field_interval); ?>" />
                </div>

                <label>
                    <?php echo esc_html__('Style', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <select name="jobsearch-custom-fields-range[field-style][]" >                        
                        <option <?php if ($range_field_field_style == 'simple') echo esc_html('selected'); ?> value="simple"><?php echo esc_html__('Simple', 'wp-jobsearch'); ?></option>
                        <option <?php if ($range_field_field_style == 'slider') echo esc_html('selected'); ?> value="slider"><?php echo esc_html__('Slider', 'wp-jobsearch'); ?></option> 
                    </select>
                </div>

                <label>
                    <?php echo esc_html__('Classes', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <input name="jobsearch-custom-fields-range[classes][]" value="<?php echo esc_html($range_field_classes); ?>" />
                </div>

                <label>
                    <?php echo esc_html__('Required', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <select name="jobsearch-custom-fields-range[required][]" >                        
                        <option <?php if ($range_field_required == 'no') echo esc_html('selected'); ?> value="no"><?php echo esc_html__('No', 'wp-jobsearch'); ?></option>
                        <option <?php if ($range_field_required == 'yes') echo esc_html('selected'); ?> value="yes"><?php echo esc_html__('Yes', 'wp-jobsearch'); ?></option> 
                    </select>
                </div>

                <label>
                    <?php echo esc_html__('Enable in Search', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <select name="jobsearch-custom-fields-range[enable-search][]" >
                        <option <?php if ($range_field_enable_search == 'no') echo esc_html('selected'); ?> value="no"><?php echo esc_html__('No', 'wp-jobsearch'); ?></option>
                        <option <?php if ($range_field_enable_search == 'yes') echo esc_html('selected'); ?> value="yes"><?php echo esc_html__('Yes', 'wp-jobsearch'); ?></option> 
                    </select>
                </div>
                <label>
                    <?php echo esc_html__('Collapse in Search', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <select name="jobsearch-custom-fields-range[collapse-search][]" >
                        <option <?php if ($range_field_collapse_search == 'no') echo esc_html('selected'); ?> value="no"><?php echo esc_html__('No', 'wp-jobsearch'); ?></option>
                        <option <?php if ($range_field_collapse_search == 'yes') echo esc_html('selected'); ?> value="yes"><?php echo esc_html__('Yes', 'wp-jobsearch'); ?></option> 
                    </select>
                </div>

                <label>
                    <?php echo esc_html__('Icon', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <?php
                    $icon_id = rand(1000000, 99999999);

                    echo jobsearch_icon_picker($range_field_icon, $icon_id, 'jobsearch-custom-fields-range[icon][]');
                    ?>
                </div>
            </div>
            <script>
                jQuery(document).ready(function () {
                    jQuery(document).on('click', '.range-field<?php echo esc_html($rand); ?>', function () {
                        jQuery('#range-field-wraper<?php echo esc_html($rand); ?>').slideToggle("slow");
                    });
                });
            </script>
        </div>
        <?php
        $html .= ob_get_clean();

        return $html;
    }

    static function jobsearch_custom_field_salary_html_callback($html, $global_custom_field_counter, $field_data) {
        $field_counter = $global_custom_field_counter;
        ob_start();
        $rand = $field_counter;
        $field_for_non_reg_user = isset($field_data['non_reg_user']) ? $field_data['non_reg_user'] : '';
        $salary_field_label = isset($field_data['label']) ? $field_data['label'] : '';
        $salary_field_field_style = isset($field_data['field-style']) ? $field_data['field-style'] : '';
        $salary_field_min = isset($field_data['min']) ? $field_data['min'] : '';
        $salary_field_laps = isset($field_data['laps']) ? $field_data['laps'] : '';
        $salary_field_interval = isset($field_data['interval']) ? $field_data['interval'] : '';
        $salary_field_collapse_search = isset($field_data['collapse-search']) ? $field_data['collapse-search'] : '';
        ?>
        <div class="jobsearch-custom-filed-container jobsearch-custom-filed-salary-container">
            <div class="field-intro">
                <span class="drag-handle"><i class="dashicons dashicons-vault" aria-hidden="true"></i></span>
                <?php $field_dyn_name = $salary_field_label != '' ? '<b>(' . $salary_field_label . ')</b>' : '' ?>
                <a href="javascript:void(0);" class="salary-field<?php echo esc_html($rand); ?>" ><?php echo wp_kses(sprintf(__('Salary Field (For Search) %s', 'wp-jobsearch'), $field_dyn_name), array('b' => array())); ?></a>
            </div>
            <div class="field-data" id="salary-field-wraper<?php echo esc_html($rand); ?>" style="display:none;">
                <input type="hidden" name="jobsearch-custom-fields-type[]" value="salary" />
                <input type="hidden" name="jobsearch-custom-fields-id[]" value="<?php echo esc_html($field_counter); ?>" />

                <label>
                    <?php echo esc_html__('Label', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <input name="jobsearch-custom-fields-salary[label][]" value="<?php echo esc_html($salary_field_label); ?>" />
                </div>

                <label>
                    <?php echo esc_html__('Min', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <input name="jobsearch-custom-fields-salary[min][]" value="<?php echo esc_html($salary_field_min); ?>" />
                </div>

                <label>
                    <?php echo esc_html__('Interval', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <input name="jobsearch-custom-fields-salary[interval][]" value="<?php echo esc_html($salary_field_interval); ?>" />
                </div>

                <label>
                    <?php echo esc_html__('Total Laps', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <input name="jobsearch-custom-fields-salary[laps][]" value="<?php echo esc_html($salary_field_laps); ?>" />
                </div>

                <label>
                    <?php echo esc_html__('Style', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <select name="jobsearch-custom-fields-salary[field-style][]" >                        
                        <option <?php if ($salary_field_field_style == 'simple') echo esc_html('selected'); ?> value="simple"><?php echo esc_html__('Simple', 'wp-jobsearch'); ?></option>
                        <option <?php if ($salary_field_field_style == 'slider') echo esc_html('selected'); ?> value="slider"><?php echo esc_html__('Slider', 'wp-jobsearch'); ?></option> 
                    </select>
                </div>

                <label>
                    <?php echo esc_html__('Collapse in Search', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <select name="jobsearch-custom-fields-salary[collapse-search][]" >
                        <option <?php if ($salary_field_collapse_search == 'no') echo esc_html('selected'); ?> value="no"><?php echo esc_html__('No', 'wp-jobsearch'); ?></option>
                        <option <?php if ($salary_field_collapse_search == 'yes') echo esc_html('selected'); ?> value="yes"><?php echo esc_html__('Yes', 'wp-jobsearch'); ?></option> 
                    </select>
                </div>

            </div>
            <script>
                jQuery(document).ready(function () {
                    jQuery(document).on('click', '.salary-field<?php echo esc_html($rand); ?>', function () {
                        jQuery('#salary-field-wraper<?php echo esc_html($rand); ?>').slideToggle("slow");
                    });
                });
            </script>
        </div>
        <?php
        $html .= ob_get_clean();

        return $html;
    }

    static function jobsearch_custom_field_dropdown_html_callback($html, $global_custom_field_counter, $field_data) {
        $field_counter = $global_custom_field_counter;
        ob_start();
        $rand = $field_counter;
        $field_for_non_reg_user = isset($field_data['non_reg_user']) ? $field_data['non_reg_user'] : '';
        $dropdown_field_name = isset($field_data['name']) ? $field_data['name'] : '';
        $dropdown_field_required = isset($field_data['required']) ? $field_data['required'] : '';
        $dropdown_field_label = isset($field_data['label']) ? $field_data['label'] : '';
        $dropdown_field_placeholder = isset($field_data['placeholder']) ? $field_data['placeholder'] : '';
        $dropdown_field_classes = isset($field_data['classes']) ? $field_data['classes'] : '';
        $dropdown_field_enable_search = isset($field_data['enable-search']) ? $field_data['enable-search'] : '';
        $dropdown_field_multi = isset($field_data['multi']) ? $field_data['multi'] : '';
        $dropdown_field_post_multi = isset($field_data['post-multi']) ? $field_data['post-multi'] : '';
        $dropdown_field_icon = isset($field_data['icon']) ? $field_data['icon'] : '';
        $dropdown_field_collapse_search = isset($field_data['collapse-search']) ? $field_data['collapse-search'] : '';
        $dropdown_field_options = isset($field_data['options']) ? $field_data['options'] : '';
        ?>
        <div class="jobsearch-custom-filed-container jobsearch-custom-filed-dropdown-container">
            <div class="field-intro">
                <span class="drag-handle"><i class="dashicons dashicons-arrow-down-alt" aria-hidden="true"></i></span>
                <?php $field_dyn_name = $dropdown_field_label != '' ? '<b>(' . $dropdown_field_label . ')</b>' : '' ?>
                <a href="javascript:void(0);" class="dropdown-field<?php echo esc_html($rand); ?>" ><?php echo wp_kses(sprintf(__('Dropdown Field %s', 'wp-jobsearch'), $field_dyn_name), array('b' => array())); ?></a>
            </div>
            <div class="field-data" id="dropdown-field-wraper<?php echo esc_html($rand); ?>" style="display:none;">
                <input type="hidden" name="jobsearch-custom-fields-type[]" value="dropdown" />
                <input type="hidden" name="jobsearch-custom-fields-id[]" value="<?php echo esc_html($field_counter); ?>" />
                
                <label>
                    <?php echo esc_html__('For Register/Non-Register Member', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <select name="jobsearch-custom-fields-dropdown[non_reg_user][]">
                        <option <?php if ($field_for_non_reg_user == 'default') echo ('selected="selected"'); ?> value="default"><?php echo esc_html__('Default', 'wp-jobsearch'); ?></option>
                        <option <?php if ($field_for_non_reg_user == 'for_reg') echo ('selected="selected"'); ?> value="for_reg"><?php echo esc_html__('Register User Only', 'wp-jobsearch'); ?></option> 
                    </select>
                </div>
                <?php do_action('jobsearch_custom_fields_dropdown_plus_1', $field_counter, $field_data, 'jobsearch-custom-fields-dropdown') ?>
                <label>
                    <?php echo esc_html__('Label', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <input name="jobsearch-custom-fields-dropdown[label][]" value="<?php echo esc_html($dropdown_field_label); ?>" />
                </div>

                <label>
                    <?php echo esc_html__('Name', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <input class="check-name-availability" name="jobsearch-custom-fields-dropdown[name][]" value="<?php echo esc_html($dropdown_field_name); ?>" />
                    <span class="available-msg"><i class="dashicons dashicons-dismiss"></i></span>
                </div>

                <label>
                    <?php echo esc_html__('Placeholder', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <input name="jobsearch-custom-fields-dropdown[placeholder][]" value="<?php echo esc_html($dropdown_field_placeholder); ?>" />
                </div>

                <label>
                    <?php echo esc_html__('Classes', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <input name="jobsearch-custom-fields-dropdown[classes][]" value="<?php echo esc_html($dropdown_field_classes); ?>" />
                </div>

                <label>
                    <?php echo esc_html__('Required', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <select name="jobsearch-custom-fields-dropdown[required][]" >
                        <option <?php if ($dropdown_field_required == 'no') echo esc_html('selected'); ?> value="no"><?php echo esc_html__('No', 'wp-jobsearch'); ?></option>
                        <option <?php if ($dropdown_field_required == 'yes') echo esc_html('selected'); ?> value="yes"><?php echo esc_html__('Yes', 'wp-jobsearch'); ?></option> 
                    </select>
                </div>

                <label>
                    <?php echo esc_html__('Enable in Search', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <select name="jobsearch-custom-fields-dropdown[enable-search][]" >
                        <option <?php if ($dropdown_field_enable_search == 'no') echo esc_html('selected'); ?> value="no"><?php echo esc_html__('No', 'wp-jobsearch'); ?></option>
                        <option <?php if ($dropdown_field_enable_search == 'yes') echo esc_html('selected'); ?> value="yes"><?php echo esc_html__('Yes', 'wp-jobsearch'); ?></option> 
                    </select>
                </div>

                <label>
                    <?php echo esc_html__('Multi Select at Search', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <select name="jobsearch-custom-fields-dropdown[multi][]" >
                        <option <?php if ($dropdown_field_multi == 'no') echo esc_html('selected'); ?> value="no"><?php echo esc_html__('No', 'wp-jobsearch'); ?></option>
                        <option <?php if ($dropdown_field_multi == 'yes') echo esc_html('selected'); ?> value="yes"><?php echo esc_html__('Yes', 'wp-jobsearch'); ?></option> 
                    </select>
                </div>

                <label>
                    <?php echo esc_html__('Multi Select at Submit', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <select name="jobsearch-custom-fields-dropdown[post-multi][]" >
                        <option <?php if ($dropdown_field_post_multi == 'no') echo esc_html('selected'); ?> value="no"><?php echo esc_html__('No', 'wp-jobsearch'); ?></option>
                        <option <?php if ($dropdown_field_post_multi == 'yes') echo esc_html('selected'); ?> value="yes"><?php echo esc_html__('Yes', 'wp-jobsearch'); ?></option> 
                    </select>
                </div>

                <label>
                    <?php echo esc_html__('Collapse in Search', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <select name="jobsearch-custom-fields-dropdown[collapse-search][]" >
                        <option <?php if ($dropdown_field_collapse_search == 'no') echo esc_html('selected'); ?> value="no"><?php echo esc_html__('No', 'wp-jobsearch'); ?></option>
                        <option <?php if ($dropdown_field_collapse_search == 'yes') echo esc_html('selected'); ?> value="yes"><?php echo esc_html__('Yes', 'wp-jobsearch'); ?></option> 
                    </select>
                </div>

                <label>
                    <?php echo esc_html__('Icon', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <?php
                    $icon_id = rand(1000000, 99999999);

                    echo jobsearch_icon_picker($dropdown_field_icon, $icon_id, 'jobsearch-custom-fields-dropdown[icon][]');
                    ?>
                </div>

                <label>
                    <?php echo esc_html__('Options', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <?php
                    if (isset($dropdown_field_options['value'])) {
                        $opt_counter = 0;
                        $radio_counter = 1;
                        foreach ($dropdown_field_options['value'] as $option_val) {
                            $option_label = $dropdown_field_options['label'][$opt_counter];
                            ?>
                            <div class="field-options-list">
                                <input name="jobsearch-custom-fields-dropdown[options][label][<?php echo esc_html($field_counter); ?>][]" value="<?php echo esc_html($option_label); ?>" placeholder="<?php echo esc_html__('Text', 'wp-jobsearch'); ?>" /> - <input name="jobsearch-custom-fields-dropdown[options][value][<?php echo esc_html($field_counter); ?>][]" value="<?php echo esc_html($option_val); ?>" placeholder="<?php echo esc_html__('Value', 'wp-jobsearch'); ?>" />
                                <a href="javascript:void(0);" class="option-field-add-btn"><i class="dashicons dashicons-plus"></i></a> 
                                <a href="javascript:void(0);" class="option-field-remove"><i class="dashicons dashicons-no-alt"></i></a>                               
                            </div>
                            <?php
                            $opt_counter++;
                        }
                    } else {
                        ?>
                        <div class="field-options-list">
                            <input name="jobsearch-custom-fields-dropdown[options][label][<?php echo esc_html($field_counter); ?>][]" value="" placeholder="<?php echo esc_html__('Text', 'wp-jobsearch'); ?>" /> - <input name="jobsearch-custom-fields-dropdown[options][value][<?php echo esc_html($field_counter); ?>][]" value="" placeholder="<?php echo esc_html__('Value', 'wp-jobsearch'); ?>" />
                            <a href="javascript:void(0);" class="option-field-add-btn"><i class="dashicons dashicons-plus"></i></a> 
                            <a href="javascript:void(0);" class="option-field-remove"><i class="dashicons dashicons-no-alt"></i></a>                               
                        </div>
                        <?php
                    }
                    ?> 
                </div>
            </div>
            <script>
                jQuery(document).ready(function () {
                    jQuery(document).on('click', '.dropdown-field<?php echo esc_html($rand); ?>', function () {
                        jQuery('#dropdown-field-wraper<?php echo esc_html($rand); ?>').slideToggle("slow");
                    });
                });
            </script>
        </div>
        <?php
        $html .= ob_get_clean();

        return $html;
    }

    static function jobsearch_custom_field_textarea_html_callback($html, $global_custom_field_counter, $field_data) {
        $field_counter = $global_custom_field_counter;
        ob_start();
        $field_for_non_reg_user = isset($field_data['non_reg_user']) ? $field_data['non_reg_user'] : '';
        $rand = $field_counter;
        $textarea_field_name = isset($field_data['name']) ? $field_data['name'] : '';
        $textarea_field_required = isset($field_data['required']) ? $field_data['required'] : '';
        $textarea_field_label = isset($field_data['label']) ? $field_data['label'] : '';
        $textarea_field_placeholder = isset($field_data['placeholder']) ? $field_data['placeholder'] : '';
        $textarea_field_classes = isset($field_data['classes']) ? $field_data['classes'] : '';
        $textarea_field_enable_search = isset($field_data['enable-search']) ? $field_data['enable-search'] : '';
        $textarea_field_icon = isset($field_data['icon']) ? $field_data['icon'] : '';
        $textarea_field_collapse_search = isset($field_data['collapse-search']) ? $field_data['collapse-search'] : '';
        ?>
        <div class="jobsearch-custom-filed-container jobsearch-custom-filed-textarea-container">
            <div class="field-intro">
                <span class="drag-handle"><i class="dashicons dashicons-editor-alignleft" aria-hidden="true"></i></span>
                <?php $field_dyn_name = $textarea_field_label != '' ? '<b>(' . $textarea_field_label . ')</b>' : '' ?>
                <a href="javascript:void(0);" class="textarea-field<?php echo esc_html($rand); ?>" ><?php echo wp_kses(sprintf(__('Textarea Field %s', 'wp-jobsearch'), $field_dyn_name), array('b' => array())); ?></a>
            </div>
            <div class="field-data" id="textarea-field-wraper<?php echo esc_html($rand); ?>" style="display:none;">
                <input type="hidden" name="jobsearch-custom-fields-type[]" value="textarea" />
                <input type="hidden" name="jobsearch-custom-fields-id[]" value="<?php echo esc_html($field_counter); ?>" />
                
                <label>
                    <?php echo esc_html__('For Register/Non-Register Member', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <select name="jobsearch-custom-fields-textarea[non_reg_user][]">
                        <option <?php if ($field_for_non_reg_user == 'default') echo ('selected="selected"'); ?> value="default"><?php echo esc_html__('Default', 'wp-jobsearch'); ?></option>
                        <option <?php if ($field_for_non_reg_user == 'for_reg') echo ('selected="selected"'); ?> value="for_reg"><?php echo esc_html__('Register User Only', 'wp-jobsearch'); ?></option> 
                    </select>
                </div>
                <?php do_action('jobsearch_custom_fields_textarea_plus_1', $field_counter, $field_data, 'jobsearch-custom-fields-textarea') ?>
                <label>
                    <?php echo esc_html__('Label', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <input name="jobsearch-custom-fields-textarea[label][]" value="<?php echo esc_html($textarea_field_label); ?>" />
                </div>

                <label>
                    <?php echo esc_html__('Name', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <input class="check-name-availability" name="jobsearch-custom-fields-textarea[name][]" value="<?php echo esc_html($textarea_field_name); ?>" />
                    <span class="available-msg"><i class="dashicons dashicons-dismiss"></i></span>
                </div>

                <label>
                    <?php echo esc_html__('Placeholder', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <input name="jobsearch-custom-fields-textarea[placeholder][]" value="<?php echo esc_html($textarea_field_placeholder); ?>" />
                </div>

                <label>
                    <?php echo esc_html__('Classes', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <input name="jobsearch-custom-fields-textarea[classes][]" value="<?php echo esc_html($textarea_field_classes); ?>" />
                </div>

                <label>
                    <?php echo esc_html__('Required', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <select name="jobsearch-custom-fields-textarea[required][]" >
                        <option <?php if ($textarea_field_required == 'no') echo esc_html('selected'); ?> value="no"><?php echo esc_html__('No', 'wp-jobsearch'); ?></option>
                        <option <?php if ($textarea_field_required == 'yes') echo esc_html('selected'); ?> value="yes"><?php echo esc_html__('Yes', 'wp-jobsearch'); ?></option> 
                    </select>
                </div>

                <label>
                    <?php echo esc_html__('Enable in Search', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <select name="jobsearch-custom-fields-textarea[enable-search][]" >
                        <option <?php if ($textarea_field_enable_search == 'no') echo esc_html('selected'); ?> value="no"><?php echo esc_html__('No', 'wp-jobsearch'); ?></option>
                        <option <?php if ($textarea_field_enable_search == 'yes') echo esc_html('selected'); ?> value="yes"><?php echo esc_html__('Yes', 'wp-jobsearch'); ?></option>     
                    </select>
                </div>

                <label>
                    <?php echo esc_html__('Collapse in Search', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <select name="jobsearch-custom-fields-textarea[collapse-search][]" >
                        <option <?php if ($textarea_field_collapse_search == 'no') echo esc_html('selected'); ?> value="no"><?php echo esc_html__('No', 'wp-jobsearch'); ?></option>
                        <option <?php if ($textarea_field_collapse_search == 'yes') echo esc_html('selected'); ?> value="yes"><?php echo esc_html__('Yes', 'wp-jobsearch'); ?></option> 
                    </select>
                </div>

                <label>
                    <?php echo esc_html__('Icon', 'wp-jobsearch'); ?>:
                </label>
                <div class="input-field">
                    <?php
                    $icon_id = rand(1000000, 99999999);
                    echo jobsearch_icon_picker($textarea_field_icon, $icon_id, 'jobsearch-custom-fields-textarea[icon][]');
                    ?>
                </div>
            </div>
            <script>
                jQuery(document).ready(function () {
                    jQuery(document).on('click', '.textarea-field<?php echo esc_html($rand); ?>', function () {
                        jQuery('#textarea-field-wraper<?php echo esc_html($rand); ?>').slideToggle("slow");
                    });
                });
            </script>
        </div>
        <?php
        $html .= ob_get_clean();

        return $html;
    }

    static function jobsearch_custom_field_actions_html_callback($li_rand, $rand, $field_type) {
        $html = '';
        ob_start();
        ?>
        <div class="actions">
            <a href="javascript:void(0);" class="custom-fields-edit <?php echo esc_html($field_type); ?>-field<?php echo esc_html($rand); ?>" ><i  class="dashicons dashicons-edit" aria-hidden="true"></i></a>
            <a href="javascript:void(0);" class="custom-fields-remove" data-randid="<?php echo esc_html($li_rand) ?>" ><i  class="dashicons dashicons-trash" aria-hidden="true"></i></a>
        </div>
        <?php
        $html .= ob_get_clean();

        return $html;
    }

}

// class Jobsearch_CustomFieldHTML 
$Jobsearch_CustomFieldHTML_obj = new Jobsearch_CustomFieldHTML();
global $Jobsearch_CustomFieldHTML_obj;
