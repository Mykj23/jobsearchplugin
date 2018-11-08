<?php

/**
 * File Type: Job Change Status Email Templates
 * Description: If job status will change with approved then trigger to employer
 * For trigger email use following hook
 * 
 * $user_data = wp_get_current_user();
 * do_action('jobsearch_job_applied_to_employer', $user_data, $post_id);
 * 
 */
if (!class_exists('jobsearch_job_applied_to_employer_template')) {

    class jobsearch_job_applied_to_employer_template {

        public $email_template_type;
        public $codes;
        public $type;
        public $group;
        public $user;
        public $job_id;
        public $is_email_sent;
        public $email_template_prefix;
        public $default_content;
        public $default_subject;
        public $default_recipients;
        public $switch_label;
        public $email_template_db_id;
        public $default_var;
        public $rand;
        public static $is_email_sent1;

        public function __construct() {

            add_action('init', array($this, 'jobsearch_job_applied_to_employer_template_init'), 1, 0);
            add_filter('jobsearch_job_applied_to_employer_filter', array($this, 'jobsearch_job_applied_to_employer_filter_callback'), 1, 4);
            add_filter('jobsearch_email_template_settings', array($this, 'template_settings_callback'), 12, 1);
            add_action('jobsearch_job_applied_to_employer', array($this, 'jobsearch_job_applied_to_employer_callback'), 10, 2);
        }

        public function jobsearch_job_applied_to_employer_template_init() {
            $this->user = array();
            $this->rand = rand(0, 99999);
            $this->group = 'job';
            $this->type = 'job_applied_to_employer';
            $this->filter = 'job_applied_to_employer';
            $this->email_template_db_id = 'job_applied_to_employer';
            $this->switch_label = esc_html__('Job Applied by candidate to employer', 'wp-jobsearch');
            $this->default_subject = esc_html__('Job Applied by candidate to employer', 'wp-jobsearch');
            $this->default_recipients = '';
            $default_content = esc_html__('Default content', 'wp-jobsearch');
            $default_content = apply_filters('jobsearch_job_applied_to_employer_filter', $default_content, 'html', 'job-applied-to-employer', 'email-templates');
            $this->default_content = $default_content;
            $this->email_template_prefix = 'job_applied_to_employer';
            $this->codes = array(
                // value_callback replace with function_callback tag replace with var
                array(
                    'var' => '{job_title}',
                    'display_text' => esc_html__('job title', 'wp-jobsearch'),
                    'function_callback' => array($this, 'get_job_added_jobtitle'),
                ),
                array(
                    'var' => '{candidate_name}',
                    'display_text' => esc_html__('candidate name', 'wp-jobsearch'),
                    'function_callback' => array($this, 'get_candidate_name'),
                ),
                array(
                    'var' => '{job_posted_by}',
                    'display_text' => esc_html__('posted by', 'wp-jobsearch'),
                    'function_callback' => array($this, 'get_job_added_posted_by'),
                ),
                array(
                    'var' => '{job_posted_by_logo}',
                    'display_text' => esc_html__('posted by logo', 'wp-jobsearch'),
                    'function_callback' => array($this, 'get_job_added_employer_logo'),
                ),
            );

            $this->default_var = array(
                'switch_label' => $this->switch_label,
                'default_subject' => $this->default_subject,
                'default_recipients' => $this->default_recipients,
                'default_content' => $this->default_content,
                'group' => $this->group,
                'type' => $this->type,
                'filter' => $this->filter,
                'codes' => $this->codes,
            );
        }

        public function jobsearch_job_applied_to_employer_callback($user = '', $job_id = '') {

            global $sitepress;
            $lang_code = '';
            if ( function_exists('icl_object_id') ) {
                $lang_code = $sitepress->get_current_language();
            }
            
            $this->user = $user;
            $this->job_id = $job_id;
            $template = $this->get_template();
            // checking email notification is enable/disable
            if (isset($template['switch']) && $template['switch'] == 1) {

                $blogname = get_option('blogname');
                $admin_email = get_option('admin_email');
                $sender_detail_header = '';
                if (isset($template['from']) && $template['from'] != '') {
                    $sender_detail_header = $template['from'];
                    if (isset($template['from_name']) && $template['from_name'] != '') {
                        $sender_detail_header = $template['from_name'] . ' <' . $sender_detail_header . '> ';
                    }
                }

                // getting template fields
                $subject = (isset($template['subject']) && $template['subject'] != '' ) ? $template['subject'] : __('Job Approved', 'wp-jobsearch');
                $from = (isset($sender_detail_header) && $sender_detail_header != '') ? $sender_detail_header : esc_attr($blogname) . ' <' . $admin_email . '>';
                $recipients = (isset($template['recipients']) && $template['recipients'] != '') ? $template['recipients'] : $this->get_job_added_email();
                $email_type = (isset($template['email_type']) && $template['email_type'] != '') ? $template['email_type'] : 'html';
                
                $email_message = isset($template['email_template']) ? $template['email_template'] : '';
                
                if ( function_exists('icl_object_id') ) {
                    $temp_trnaslated = get_option('jobsearch_translate_email_templates');
                    $template_type = $this->type;
                    if (isset($temp_trnaslated[$template_type]['lang_' . $lang_code]['subject'])) {
                        $subject = $temp_trnaslated[$template_type]['lang_' . $lang_code]['subject'];
                    }
                    if (isset($temp_trnaslated[$template_type]['lang_' . $lang_code]['content'])) {
                        $email_message = $temp_trnaslated[$template_type]['lang_' . $lang_code]['content'];
                        $email_message = JobSearch_plugin::jobsearch_replace_variables($email_message, $this->codes);
                    }
                }

                $args = array(
                    'to' => $recipients,
                    'subject' => $subject,
                    'from' => $from,
                    'message' => $email_message,
                    'email_type' => $email_type,
                    'class_obj' => $this, // temprary comment
                );
                do_action('jobsearch_send_mail', $args);
                jobsearch_job_applied_to_employer_template::$is_email_sent1 = $this->is_email_sent;
            }
        }

        public static function template_path() {
            return apply_filters('jobsearch_plugin_template_path', 'wp-jobsearch/');
        }

        public function jobsearch_job_applied_to_employer_filter_callback($html, $slug = '', $name = '', $ext_template = '') {
            ob_start();
            $html = '';
            $template = '';
            if ($ext_template != '') {
                $ext_template = trailingslashit($ext_template);
            }
            if ($name) {
                $template = locate_template(array("{$slug}-{$name}.php", self::template_path() . "templates/{$ext_template}/{$slug}-{$name}.php"));
            }
            if (!$template && $name && file_exists(jobsearch_plugin_get_path() . "templates/{$ext_template}/{$slug}-{$name}.php")) {
                $template = jobsearch_plugin_get_path() . "templates/{$ext_template}{$slug}-{$name}.php";
            }
            if (!$template) {
                $template = locate_template(array("{$slug}.php", self::template_path() . "{$ext_template}/{$slug}.php"));
            }
            //echo $template;exit;
            if ($template) {
                load_template($template, false);
            }
            $html = ob_get_clean();
            return $html;
        }

        public function template_settings_callback($email_template_options) {

            $rand = rand(123, 8787987);
            $email_template_options['job_applied_to_employer']['rand'] = $this->rand;
            $email_template_options['job_applied_to_employer']['email_template_prefix'] = $this->email_template_prefix;
            $email_template_options['job_applied_to_employer']['default_var'] = $this->default_var;
            return $email_template_options;
        }

        public function get_template() {
            return JobSearch_plugin::get_template($this->email_template_db_id, $this->codes, $this->default_content);
        }

        public function get_job_added_email() {

            $job_posted_by = get_post_meta($this->job_id, 'jobsearch_field_job_posted_by', true);
            if ($job_posted_by) {
                $employer_user_id = jobsearch_get_employer_user_id($job_posted_by);
                $user_obj = get_user_by('ID', $employer_user_id);

                $email = $user_obj->user_email;
                return $email;
            }
        }

        public function get_candidate_name() {

            $user_name = $this->user->display_name;
            $user_obj = $this->user;
            $user_name = apply_filters('jobsearch_user_display_name', $user_name, $user_obj);
            return $user_name;
        }

        public function get_job_added_jobtitle() {
            $job_title = get_the_title($this->job_id);
            return $job_title;
        }

        public function get_job_added_posted_by() {
            $job_posted_by = get_post_meta($this->job_id, 'jobsearch_field_job_posted_by', true);
            $job_posted_by_user = get_the_title($job_posted_by);
            return $job_posted_by_user;
        }

        public function get_job_added_employer_logo() {
            $job_posted_by = get_post_meta($this->job_id, 'jobsearch_field_job_posted_by', true);
            $post_thumbnail_id = get_post_thumbnail_id($job_posted_by);
            $post_thumbnail_image = wp_get_attachment_image_src($post_thumbnail_id, 'large');
            $post_thumbnail_src = isset($post_thumbnail_image[0]) && esc_url($post_thumbnail_image[0]) != '' ? $post_thumbnail_image[0] : '';
            $image_html = '';
            if ($post_thumbnail_src != '') {
                $image_html .= '<img src="' . esc_url($post_thumbnail_src) . '" alt="">';
            }
            return $image_html;
        }

    }

    new jobsearch_job_applied_to_employer_template();
}