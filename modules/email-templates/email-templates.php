<?php
/*
  Class : EmailTemplate
 */


// this is an include only WP file
if (!defined('ABSPATH')) {
    die;
}

// main plugin class
class Jobsearch_EmailTemplate {

    public static $email_template_options;

// hook things up
    public function __construct() {


        Jobsearch_EmailTemplate::load_files();
        add_action('init', array($this, 'init'), 5);
    }

    public function init() {
        $this->load_email_template_options();
        add_action('admin_enqueue_scripts', array($this, 'emailtemplate_admin_enqueue_scripts'));
        add_action('jobsearch_load_email_templates', array($this, 'jobsearch_load_email_templates_callback'), 1);
    }

    /**
     * Load email template types.
     */
    public function load_email_template_options() {
        self::$email_template_options = array();
        self::$email_template_options = apply_filters('jobsearch_email_template_settings', self::$email_template_options, 1);
    }

    static function load_files() {
        include plugin_dir_path(dirname(__FILE__)) . 'email-templates/include/email-templates-ajax.php';
        include plugin_dir_path(dirname(__FILE__)) . 'email-templates/include/email-templates-html.php';
    }

    public function emailtemplate_admin_enqueue_scripts() {
        global $sitepress;
        
        $admin_ajax_url = admin_url('admin-ajax.php');
        if ( function_exists('icl_object_id') ) {
            $lang_code = $sitepress->get_current_language();
            $admin_ajax_url = add_query_arg(array('lang' => $lang_code), $admin_ajax_url);
        }
        wp_register_style('jobsearch-email-template-css', jobsearch_plugin_get_url('modules/email-templates/css/email-template.css'), array(), '');
        wp_register_script('jobsearch-email-template', jobsearch_plugin_get_url('modules/email-templates/js/email-template-functions.js'), array('jquery'), '', true);
        // Localize the script
        $jobsearch_emailtemplate_common_arr = array(
            'plugin_url' => jobsearch_plugin_get_url(),
            'ajax_url' => $admin_ajax_url,
        );
        wp_localize_script('jobsearch-email-template', 'jobsearch_emailtemplate_common_vars', $jobsearch_emailtemplate_common_arr);
        wp_enqueue_script('jobsearch-email-template'); 
    }

    public function jobsearch_load_email_templates_callback($email_template_entity) {
        global $sitepress;
        $lang_code = '';
        if ( function_exists('icl_object_id') ) {
            $lang_code = $sitepress->get_current_language();
        }
        
        wp_enqueue_style('jobsearch-email-template-css');
        $rand_id = rand(123, 8787987);
        // load all saved fields 
        $email_templates_settings = self::$email_template_options;
        $field_db_slug = "jobsearch_" . $email_template_entity;
        $email_all_templates_saved_data = get_option($field_db_slug);
        $sender_name = isset($email_all_templates_saved_data['jobsearch_email_template_sender_name']) ? $email_all_templates_saved_data['jobsearch_email_template_sender_name'] : '';
        $sender_email = isset($email_all_templates_saved_data['jobsearch_email_template_sender_email']) ? $email_all_templates_saved_data['jobsearch_email_template_sender_email'] : '';
        $email_send_as = isset($email_all_templates_saved_data['jobsearch_email_template_email_send_as']) ? $email_all_templates_saved_data['jobsearch_email_template_email_send_as'] : '';
        
        if ( function_exists('icl_object_id') ) {
            $temp_trnaslated = get_option('jobsearch_translate_email_templates');
            if (isset($temp_trnaslated['global_settings']['lang_' . $lang_code]['sender_name'])) {
                $sender_name = $temp_trnaslated['global_settings']['lang_' . $lang_code]['sender_name'];
            }
        }
        ?>
        <div class="container" >
            <div data-force="30" class="layer block" > 
                <div class="jobsearch-email-template-form">
                    <form id="jobsearch-email-template-form-<?php echo absint($rand_id); ?>" action="" method="post">
                        <div class="layer email-template-title"><?php echo esc_html('List of Fields', 'wp-jobsearch'); ?></div>
                        <ul id="foo<?php echo absint($rand_id); ?>" class="block__list block__list_words"> 
                            <li class="jobsearch-element-field">
                                <div class="elem-label">
                                    <label><?php echo esc_html('Sender name', 'wp-jobsearch'); ?></label>
                                </div>
                                <div class="elem-field">
                                    <input id="sender_name" name="jobsearch_email_template_sender_name" class="form-control" value="<?php echo esc_html($sender_name); ?>" type="text">
                                </div>
                            </li>
                            <li class="jobsearch-element-field">
                                <div class="elem-label">
                                    <label><?php echo esc_html('Sender email', 'wp-jobsearch'); ?></label>
                                </div>
                                <div class="elem-field">
                                    <input id="sender_name" name="jobsearch_email_template_sender_email" class="form-control" value="<?php echo esc_html($sender_email); ?>" type="text">
                                </div>
                            </li>
                            <li class="jobsearch-element-field">
                                <div class="elem-label">
                                    <label><?php echo esc_html('Send emails as', 'wp-jobsearch'); ?></label>
                                </div>
                                <div class="elem-field">
                                    <select class="form-control" name="jobsearch_email_template_email_send_as" id="bookly_email_send_as">
                                        <option <?php
                                        if (isset($email_send_as) && $email_send_as == 'html') {
                                            echo force_balance_tags('selected="selected"');
                                        }
                                        ?> value="html"><?php echo esc_html('HTML', 'wp-jobsearch'); ?></option>
                                        <option <?php
                                        if (isset($email_send_as) && $email_send_as == 'text') {
                                            echo force_balance_tags('selected="selected"');
                                        }
                                        ?> value="text"><?php echo esc_html('Text', 'wp-jobsearch'); ?></option>
                                    </select>
                                </div>
                            </li>
                            <?php
                            $output = '';
                            $prefix = 'jobsearch_email_template';
                            if (!empty($email_templates_settings)) {
                                foreach ($email_templates_settings as $single_email_templates_setting) {
                                    $default_var = $single_email_templates_setting['default_var'] ? $single_email_templates_setting['default_var'] : '';
                                    $rand = $single_email_templates_setting['rand'] ? $single_email_templates_setting['rand'] : '';
                                    $email_template_prefix = $single_email_templates_setting['email_template_prefix'] ? $single_email_templates_setting['email_template_prefix'] : '';
                                    $output .= apply_filters('jobsearch_email_template_fields_html', '', $rand, $prefix, $email_template_prefix, $default_var, $email_all_templates_saved_data);
                                }
                            }
                            echo force_balance_tags($output);
                            ?>
                        </ul> 
                        <div class="jobsearch-email-templates-submit">                        
                            <button class="email-templates-submit" data-randid="<?php echo absint($rand_id); ?>" data-entitytype="<?php echo esc_html($email_template_entity); ?>" data-btntext="<?php echo esc_html('Save Fields', 'wp-jobsearch'); ?>"><?php echo esc_html('Save Fields', 'wp-jobsearch'); ?></button>
                            <div class="email-template-msg email-template-msg-<?php echo absint($rand_id); ?>" style="display: none;"></div>
                        </div>
                    </form>
                </div>
            </div> 
        </div>
        <?php
    }

}

// class Jobsearch_EmailTemplate 
$Jobsearch_EmailTemplate_obj = new Jobsearch_EmailTemplate();
global $Jobsearch_EmailTemplate_obj;
