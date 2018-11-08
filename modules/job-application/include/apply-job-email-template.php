<?php
/**
 * File Type: Job Alerts Email Templates
 * For trigger email use following hook
 * 
 * do_action('jobsearch_apply_job_by_email', $apply_detail);
 * 
 */
if (!class_exists('jobsearch_apply_job_by_email_template')) {

    class jobsearch_apply_job_by_email_template {

        public $email_template_type;
        public $codes;
        public $type;
        public $group;
        public $apply_detail;
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

            add_action('init', array($this, 'jobsearch_apply_job_by_email_template_init'), 1, 0);
            add_filter('jobsearch_apply_job_by_email_filter', array($this, 'jobsearch_apply_job_by_email_filter_callback'), 1, 4);
            add_filter('jobsearch_email_template_settings', array($this, 'template_settings_callback'), 12, 1);
            add_action('jobsearch_new_apply_job_by_email', array($this, 'jobsearch_apply_job_by_email_callback'), 10, 1);
        }

        public function jobsearch_apply_job_by_email_template_init() {
            $this->apply_detail = array();
            $this->rand = rand(0, 99999);
            $this->group = 'job';
            $this->type = 'apply_job_by_email';
            $this->filter = 'apply_job_by_email';
            $this->email_template_db_id = 'apply_job_by_email';
            $this->switch_label = esc_html__('Apply job by Email', 'wp-jobsearch');
            $this->default_subject = esc_html__('Apply job by Email', 'wp-jobsearch');
            $this->default_recipients = '';
            $default_content = esc_html__('Default content', 'wp-jobsearch');
            $default_content = apply_filters('jobsearch_apply_job_by_email_filter', $default_content, 'html', 'apply-job-by-email', '');
            $this->default_content = $default_content;
            $this->email_template_prefix = 'apply_job_by_email';
            $this->codes = array(
                // value_callback replace with function_callback tag replace with var
                array(
                    'var' => '{job_title}',
                    'display_text' => esc_html__('Job Title', 'wp-jobsearch'),
                    'function_callback' => array($this, 'get_job_title'),
                ),
                array(
                    'var' => '{job_url}',
                    'display_text' => esc_html__('Job URL', 'wp-jobsearch'),
                    'function_callback' => array($this, 'get_job_url'),
                ),
                array(
                    'var' => '{username}',
                    'display_text' => esc_html__('User Name', 'wp-jobsearch'),
                    'function_callback' => array($this, 'get_username'),
                ),
                array(
                    'var' => '{user_surname}',
                    'display_text' => esc_html__('User Surname', 'wp-jobsearch'),
                    'function_callback' => array($this, 'get_user_surname'),
                ),
                array(
                    'var' => '{user_email}',
                    'display_text' => esc_html__('User Email', 'wp-jobsearch'),
                    'function_callback' => array($this, 'get_user_email'),
                ),
                array(
                    'var' => '{user_phone}',
                    'display_text' => esc_html__('User Phone', 'wp-jobsearch'),
                    'function_callback' => array($this, 'get_user_phone'),
                ),
                array(
                    'var' => '{user_message}',
                    'display_text' => esc_html__('User Message', 'wp-jobsearch'),
                    'function_callback' => array($this, 'get_user_msg'),
                ),
                array(
                    'var' => '{accepts_email_communication}',
                    'display_text' => esc_html__('User accepts email communication', 'wp-jobsearch'),
                    'function_callback' => array($this, 'user_accepts_email_communication'),
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

        public function jobsearch_apply_job_by_email_callback($apply_detail = array()) {

            global $sitepress;
            $lang_code = '';
            if ( function_exists('icl_object_id') ) {
                $lang_code = $sitepress->get_current_language();
            }
            
            $this->apply_detail = $apply_detail;
            $job_id = isset($this->apply_detail['id']) ? $this->apply_detail['id'] : 0;
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
                $subject = (isset($template['subject']) && $template['subject'] != '' ) ? $template['subject'] : __('Apply Job by Email', 'wp-jobsearch');
                $from = (isset($sender_detail_header) && $sender_detail_header != '') ? $sender_detail_header : esc_attr($blogname) . ' <' . $admin_email . '>';
                $recipients = (isset($template['recipients']) && $template['recipients'] != '') ? $template['recipients'] : (isset($this->apply_detail['email']) ? $this->apply_detail['email'] : '');
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
                if (isset($apply_detail['att_file_path'])) {
                    $args['att_file_path'] = $apply_detail['att_file_path'];
                }
                do_action('jobsearch_send_mail', $args);
                jobsearch_apply_job_by_email_template::$is_email_sent1 = $this->is_email_sent;
            }
        }

        public static function template_path() {
            return apply_filters('jobsearch_plugin_template_path', 'wp-jobsearch/');
        }

        public function jobsearch_apply_job_by_email_filter_callback($html, $slug = '', $name = '', $ext_template = '') {
            ob_start();
            $html = '';
            $template = '';
            if ($ext_template != '') {
                $ext_template = trailingslashit($ext_template);
            }
            if ($name) {
                $template = locate_template(array("{$slug}-{$name}.php", self::template_path() . "{$ext_template}/{$slug}-{$name}.php"));
            }
            if (!$template && $name && file_exists(jobsearch_plugin_get_path() . "modules/job-application/templates/{$ext_template}/{$slug}-{$name}.php")) {
                $template = jobsearch_plugin_get_path() . "modules/job-application/templates/{$ext_template}{$slug}-{$name}.php";
            }
            if (!$template) {
                $template = locate_template(array("{$slug}.php", self::template_path() . "{$ext_template}/{$slug}.php"));
            }
            //echo $template; exit;
            if ($template) {
                load_template($template, false);
            }
            $html = ob_get_clean();
            return $html;
        }

        public function template_settings_callback($email_template_options) {

            $rand = rand(123, 8787987);
            $email_template_options['apply_job_by_email']['rand'] = $this->rand;
            $email_template_options['apply_job_by_email']['email_template_prefix'] = $this->email_template_prefix;
            $email_template_options['apply_job_by_email']['default_var'] = $this->default_var;
            return $email_template_options;
        }

        public function get_template() {
            return JobSearch_plugin::get_template($this->email_template_db_id, $this->codes, $this->default_content);
        }

        public function get_job_title() {
            if (isset($this->apply_detail['id'])) {
                $job_id = $this->apply_detail['id'];
                return get_the_title($job_id);
            }
        }
        
        public function get_job_url() {
            
            if (isset($this->apply_detail['id'])) {
                $job_id = $this->apply_detail['id'];
                return '<a href="' . get_permalink($job_id) . '">' . get_permalink($job_id) . '</a>';
            }
        }

        public function get_username() {
            if (isset($this->apply_detail['username'])) {
                $username = $this->apply_detail['username'];
                return $username;
            }
        }
        
        public function get_user_surname() {
            
            if (isset($this->apply_detail['user_surname'])) {
                $user_surname = $this->apply_detail['user_surname'];
                return $user_surname;
            }
        }

        public function get_user_email() {
            if (isset($this->apply_detail['user_email'])) {
                $user_email = $this->apply_detail['user_email'];
                return $user_email;
            }
        }

        public function get_user_phone() {
            if (isset($this->apply_detail['user_phone'])) {
                $user_phone = $this->apply_detail['user_phone'];
                return $user_phone;
            }
        }

        public function get_user_msg() {
            if (isset($this->apply_detail['user_msg'])) {
                $user_msg = $this->apply_detail['user_msg'];
                return $user_msg;
            }
        }
        
        public function user_accepts_email_communication() {
            $txt = esc_html__('No', 'wp-jobsearch');
            if ($this->apply_detail['email_commun_check']) {
                $txt = esc_html__('Yes', 'wp-jobsearch');
            }
            return $txt;
        }

    }

    new jobsearch_apply_job_by_email_template();
}