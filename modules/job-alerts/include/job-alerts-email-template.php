<?php
/**
 * File Type: Job Alerts Email Templates
 * For trigger email use following hook
 * 
 * do_action('jobsearch_job_alerts_email', $alert_detail);
 * 
 */
if (!class_exists('jobsearch_job_alerts_email_template')) {

    class jobsearch_job_alerts_email_template {

        public $email_template_type;
        public $codes;
        public $type;
        public $group;
        public $alert_detail;
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

            add_action('init', array($this, 'jobsearch_job_alerts_email_template_init'), 1, 0);
            add_filter('jobsearch_job_alerts_email_filter', array($this, 'jobsearch_job_alerts_email_filter_callback'), 1, 4);
            add_filter('jobsearch_email_template_settings', array($this, 'template_settings_callback'), 12, 1);
            add_action('jobsearch_new_job_alerts_email', array($this, 'jobsearch_job_alerts_email_callback'), 10, 1);
        }

        public function jobsearch_job_alerts_email_template_init() {
            $this->alert_detail = array();
            $this->rand = rand(0, 99999);
            $this->group = 'job';
            $this->type = 'job_alerts_email';
            $this->filter = 'job_alerts_email';
            $this->email_template_db_id = 'job_alerts_email';
            $this->switch_label = esc_html__('New Jobs Alert Email', 'wp-jobsearch');
            $this->default_subject = esc_html__('New Jobs Alert Email', 'wp-jobsearch');
            $this->default_recipients = '';
            $default_content = esc_html__('Default content', 'wp-jobsearch');
            $default_content = apply_filters('jobsearch_job_alerts_email_filter', $default_content, 'html', 'new-job-alerts', '');
            $this->default_content = $default_content;
            $this->email_template_prefix = 'job_alerts_email';
            $this->codes = array(
                // value_callback replace with function_callback tag replace with var
                array(
                    'var' => '{job_alert_title)',
                    'display_text' => esc_html__('Job Alert Title', 'wp-jobsearch'),
                    'function_callback' => array($this, 'get_job_alert_title'),
                ),
                array(
                    'var' => '{jobs_list}',
                    'display_text' => esc_html__('Filtered Jobs List', 'wp-jobsearch'),
                    'function_callback' => array($this, 'get_filtered_jobs_list'),
                ),
                array(
                    'var' => '{total_jobs_count}',
                    'display_text' => esc_html__('Total Jobs Found', 'wp-jobsearch'),
                    'function_callback' => array($this, 'get_total_jobs_count'),
                ),
                array(
                    'var' => '{unsubscribe_list}',
                    'display_text' => esc_html__('Job Alert Unsubscribe Link', 'wp-jobsearch'),
                    'function_callback' => array($this, 'get_unsubscribe_link'),
                ),
                array(
                    'var' => '{job_alert_frequency}',
                    'display_text' => esc_html__('Job Alert Frequency', 'wp-jobsearch'),
                    'function_callback' => array($this, 'get_frequency'),
                ),
                array(
                    'var' => '{new_listing_url}',
                    'display_text' => esc_html__('Job Alert Full Listing URL', 'wp-jobsearch'),
                    'function_callback' => array($this, 'get_full_listing_url'),
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

        public function jobsearch_job_alerts_email_callback($alert_detail = array()) {

            global $sitepress;
            $lang_code = '';
            if ( function_exists('icl_object_id') ) {
                $lang_code = $sitepress->get_current_language();
            }
            
            $this->alert_detail = $alert_detail;
            $job_id = isset($this->alert_detail['id']) ? $this->alert_detail['id'] : 0;
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
                $subject = (isset($template['subject']) && $template['subject'] != '' ) ? $template['subject'] : __('Jobs alert from ', 'wp-jobsearch') . get_bloginfo('name');
                $from = (isset($sender_detail_header) && $sender_detail_header != '') ? $sender_detail_header : esc_attr($blogname) . ' <' . $admin_email . '>';
                $recipients = (isset($template['recipients']) && $template['recipients'] != '') ? $template['recipients'] : isset($this->alert_detail['email']) ? $this->alert_detail['email'] : '';
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
                update_post_meta($job_id, 'last_time_email_sent', time());
                jobsearch_job_alerts_email_template::$is_email_sent1 = $this->is_email_sent;
            }
        }

        public static function template_path() {
            return apply_filters('jobsearch_plugin_template_path', 'wp-jobsearch/');
        }

        public function jobsearch_job_alerts_email_filter_callback($html, $slug = '', $name = '', $ext_template = '') {
            ob_start();
            $html = '';
            $template = '';
            if ($ext_template != '') {
                $ext_template = trailingslashit($ext_template);
            }
            if ($name) {
                $template = locate_template(array("{$slug}-{$name}.php", self::template_path() . "{$ext_template}/{$slug}-{$name}.php"));
            }
            if (!$template && $name && file_exists(jobsearch_plugin_get_path() . "modules/job-alerts/templates/{$ext_template}/{$slug}-{$name}.php")) {
                $template = jobsearch_plugin_get_path() . "modules/job-alerts/templates/{$ext_template}{$slug}-{$name}.php";
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
            $email_template_options['job_alerts_email']['rand'] = $this->rand;
            $email_template_options['job_alerts_email']['email_template_prefix'] = $this->email_template_prefix;
            $email_template_options['job_alerts_email']['default_var'] = $this->default_var;
            return $email_template_options;
        }

        public function get_template() {
            return JobSearch_plugin::get_template($this->email_template_db_id, $this->codes, $this->default_content);
        }

        public function get_job_alert_title() {
            if (isset($this->alert_detail['title'])) {
                return ucfirst($this->alert_detail['title']);
            }
            return false;
        }

        public function get_filtered_jobs_list() {
            if (isset($this->alert_detail['jobs_query'])) {
                $jobs_query = $this->alert_detail['jobs_query'];
                $frequency = str_replace('+', '-', $this->alert_detail['frequency']);
                $jobs_query['meta_query'][] = array(
                    'key' => 'jobsearch_field_job_publish_date',
                    'value' => strtotime(date('Y-m-d', strtotime($frequency))),
                    'compare' => '>=',
                );
                $jobs_query['posts_per_page'] = 10;
                $loop = new WP_Query($jobs_query);
                ob_start();
                ?>
                <table cellpadding="0px" cellspacing="0px">
                    <?php while ($loop->have_posts()) : $loop->the_post(); ?>
                        <tr><td style="padding: 5px 0 0 0;"><a href="<?php echo get_post_permalink(); ?>"><?php echo the_title(); ?></a></td></tr>
                            <?php endwhile; ?>
                </table>
                <?php
                $html1 = ob_get_clean();
                return $html1;
            }
            return false;
        }

        public static function get_job_alerts_count($jobs_query, $frequency) {
            $frequency = str_replace('+', '-', $frequency);

            $jobs_query['meta_query'][] = array(
                'key' => 'jobsearch_field_job_publish_date',
                'value' => strtotime(date('Y-m-d', strtotime($frequency))),
                'compare' => '>=',
            );
            $jobs_query['posts_per_page'] = -1;
            $loop_count = new WP_Query($jobs_query);
            return $loop_count->found_posts;
        }

        public function get_total_jobs_count() {

            if (isset($this->alert_detail['jobs_query'])) {
                return self::get_job_alerts_count($this->alert_detail['jobs_query'], $this->$alert_detail['frequency']);
            }
            return false;
        }

        public function get_unsubscribe_link() {
            if (isset($this->alert_detail['id'])) {
                return '<a href="' . admin_url('admin-ajax.php') . '?action=jobsearch_unsubscribe_job_alert&jaid=' . $this->alert_detail['id'] . '">' . esc_html__('Unsubscribe', 'wp-jobsearch') . '</a>';
            }
            return false;
        }

        public function get_frequency() {
            if (isset($this->alert_detail['set_frequency'])) {
                return $this->alert_detail['set_frequency'];
            }
            return false;
        }

        public function get_full_listing_url() {
            if (isset($this->alert_detail['page_url']) && $this->alert_detail['page_url'] != '') {
                
                return '<a href="' . $this->alert_detail['page_url'] . '">' . esc_html__('View Full Listing', 'wp-jobsearch') . '</a>';
            }
            return false;
        }

    }

    new jobsearch_job_alerts_email_template();
}