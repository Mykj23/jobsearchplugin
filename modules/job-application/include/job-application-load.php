<?php
/**
 * Directory Plus JobApplicationLoads Module
 */
// Direct access not allowed.
if (!defined('ABSPATH')) {
    exit;
}
if (!class_exists('Jobsearch_JobApplicationLoad')) {

    class Jobsearch_JobApplicationLoad {

        public function __construct() {
            add_filter('jobsearch_job_applications_btn', array($this, 'jobsearch_job_applications_btn_callback'), 11, 2);
            add_action('wp_ajax_jobsearch_job_application_submit', array($this, 'jobsearch_job_application_submit_callback'));

            //
            add_action('wp_ajax_jobsearch_apply_job_with_cv_file', array($this, 'apply_job_with_cv_file'));

            //
            add_filter('jobsearch_job_detail_before_footer', array($this, 'job_application_popup_form'), 10, 1);
            //
            add_filter('wp_ajax_jobsearch_job_apply_without_login', array($this, 'job_apply_without_login'));
            add_filter('wp_ajax_nopriv_jobsearch_job_apply_without_login', array($this, 'job_apply_without_login'));
            //
            add_filter('wp_ajax_jobsearch_applying_job_with_email', array($this, 'job_apply_with_email'));
            add_filter('wp_ajax_nopriv_jobsearch_applying_job_with_email', array($this, 'job_apply_with_email'));
        }

        public function apply_job_with_cv_file() {
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
                $atach_id = jobsearch_upload_candidate_cv('on_apply_cv_file', $candidate_id);

                if ($atach_id > 0) {
                    $file_url = wp_get_attachment_url($atach_id);
                    if ($file_url) {
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
                    <li>
                        <i class="<?php echo ($attach_icon) ?>"></i> 
                        <label for="cv_file_<?php echo ($atach_id) ?>">
                            <input id="cv_file_<?php echo ($atach_id) ?>" type="radio" class="cv_file_item" name="cv_file_item" value="<?php echo ($atach_id) ?>">
                            <?php echo (strlen($cv_file_title) > 40 ? substr($cv_file_title, 0, 40) . '...' : $cv_file_title) ?>
                            <span class="upload-datetime"><i class="fa fa-calendar"></i> <?php echo date_i18n(get_option('date_format'), strtotime($attach_date)) . ' ' . date_i18n(get_option('time_format'), strtotime($attach_date)) ?></span>
                        </label>
                    </li>
                    <?php
                    $file_html = ob_get_clean();

                    echo json_encode(array('fileUrl' => $file_url, 'filehtml' => $file_html));
                }
            }
            wp_die();
        }

        public function jobsearch_job_applications_btn_callback($html, $arg = array()) {
            global $jobsearch_plugin_options;
            $rand_id = rand(123400, 9999999);
            extract(shortcode_atts(array(
                'classes' => 'jobsearch-applyjob-btn',
                'btn_after_label' => '',
                'btn_before_label' => '',
                'btn_applied_label' => '',
                'job_id' => ''
                            ), $arg));

            $job_extrnal_apply_switch = isset($jobsearch_plugin_options['job-apply-extrnal-url']) ? $jobsearch_plugin_options['job-apply-extrnal-url'] : '';
            $job_aply_type = get_post_meta($job_id, 'jobsearch_field_job_apply_type', true);
            $job_aply_extrnal_url = get_post_meta($job_id, 'jobsearch_field_job_apply_url', true);

            $apply_without_login = isset($jobsearch_plugin_options['job-apply-without-login']) ? $jobsearch_plugin_options['job-apply-without-login'] : '';

            $multiple_cv_files_allow = isset($jobsearch_plugin_options['multiple_cv_uploads']) ? $jobsearch_plugin_options['multiple_cv_uploads'] : '';

            if ($job_id != '') {
                $classes_str = 'jobsearch-open-signin-tab';
                $multi_cvs = false;
                if (is_user_logged_in()) {
                    if (jobsearch_user_is_candidate()) {
                        if ($multiple_cv_files_allow == 'on') {
                            $multi_cvs = true;
                        }
                        $classes_str = 'jobsearch-apply-btn';
                    } else {
                        $classes_str = 'jobsearch-other-role-btn jobsearch-applyjob-msg-popup-btn';
                    }
                }
                ob_start();
                $jobsearch_applied_list = array();
                $btn_text = $btn_before_label;
                $is_applied = false;
                if (is_user_logged_in()) {
                    $finded_result_list = jobsearch_find_index_user_meta_list($job_id, 'jobsearch-user-jobs-applied-list', 'post_id', jobsearch_get_user_id());
                    if (is_array($finded_result_list) && !empty($finded_result_list)) {
                        $classes_str = 'jobsearch-applied-btn';
                        $btn_text = $btn_applied_label;
                        $is_applied = true;
                    }
                }

                if ($apply_without_login == 'on' && !is_user_logged_in()) {
                    $classes_str = 'jobsearch-nonuser-apply-btn';
                }

                if ($job_extrnal_apply_switch == 'on' && $job_aply_type == 'with_email') {
                    ?>
                    <a href="javascript:void(0);" class="<?php echo esc_html($classes); ?> <?php echo ('aply-withemail-btn-' . $rand_id) ?>"><?php echo esc_html($btn_text) ?></a>
                    <script>
                        jQuery(document).on('click', '.aply-withemail-btn-<?php echo ($rand_id) ?>', function () {
                            jobsearch_modal_popup_open('JobSearchModalApplyWithEmail<?php echo ($rand_id) ?>');
                        });
                    </script>
                    <?php
                    $popup_args = array(
                        'p_job_id' => $job_id,
                        'p_rand_id' => $rand_id,
                        'p_btn_text' => $btn_text,
                        'p_classes' => $classes,
                        'p_classes_str' => $classes_str,
                        'p_btn_after_label' => $btn_after_label,
                    );
                    add_action('wp_footer', function () use ($popup_args) {

                        extract(shortcode_atts(array(
                            'p_job_id' => '',
                            'p_rand_id' => '',
                            'p_btn_text' => '',
                            'p_classes' => '',
                            'p_classes_str' => '',
                            'p_btn_after_label' => '',
                                        ), $popup_args));

                        $user_dname = '';
                        $user_demail = '';
                        
                        if (is_user_logged_in()) {
                            $cuser_id = get_current_user_id();
                            $cuser_obj = get_user_by('ID', $cuser_id);
                            $user_dname = $cuser_obj->display_name;
                            $user_demail = $cuser_obj->user_email;
                        }
                        ?>
                        <div class="jobsearch-modal fade" id="JobSearchModalApplyWithEmail<?php echo ($p_rand_id) ?>">
                            <div class="modal-inner-area">&nbsp;</div>
                            <div class="modal-content-area">
                                <div class="modal-box-area">
                                    <span class="modal-close"><i class="fa fa-times"></i></span>
                                    <form id="apply-withemail-<?php echo ($p_rand_id) ?>">
                                        <div class="jobsearch-apply-withemail-con jobsearch-user-form jobsearch-user-form-coltwo">
                                            <ul class="apply-fields-list"> 
                                                <li> 
                                                    <label><?php esc_html_e('Name *', 'wp-jobsearch') ?> :</label>
                                                    <input class="required" name="user_fullname" value="<?php echo ($user_dname) ?>" type="text" placeholder="<?php esc_html_e('Name', 'wp-jobsearch') ?>">
                                                </li>
                                                <li> 
                                                    <label><?php esc_html_e('Surname', 'wp-jobsearch') ?>:</label>
                                                    <input class="required" name="user_surname" type="text" placeholder="<?php esc_html_e('Surname', 'wp-jobsearch') ?>">
                                                </li>
                                                <li> 
                                                    <label><?php esc_html_e('Email *', 'wp-jobsearch') ?> :</label>
                                                    <input class="required" name="user_email" value="<?php echo ($user_demail) ?>" type="text" placeholder="<?php esc_html_e('Email Address', 'wp-jobsearch') ?>">
                                                </li>
                                                <li> 
                                                    <label><?php esc_html_e('Phone', 'wp-jobsearch') ?>:</label>
                                                    <input class="required" name="user_phone" type="text" placeholder="<?php esc_html_e('Phone', 'wp-jobsearch') ?>">
                                                </li>
                                                <li class="form-textarea jobsearch-user-form-coltwo-full"> 
                                                    <label><?php esc_html_e('Message', 'wp-jobsearch') ?>:</label>
                                                    <textarea name="user_msg" placeholder="<?php esc_html_e('Type your Message', 'wp-jobsearch') ?>"></textarea>
                                                </li>
                                                <li class="jobsearch-user-form-coltwo-full">
                                                    <div id="jobsearch-upload-cv-main" class="jobsearch-upload-cv jobsearch-applyjob-upload-cv">
                                                        <label><?php esc_html_e('Curriculum Vitae', 'wp-jobsearch') ?></label>
                                                        <input class="jobsearch-disabled-input" id="jobsearch-uploadfile-<?php echo absint($p_rand_id); ?>" placeholder="Samaple_CV.pdf" disabled="disabled">
                                                        <div class="jobsearch-cvupload-file">
                                                            <span><?php esc_html_e('Upload CV', 'wp-jobsearch') ?></span>
                                                            <input id="jobsearch-uploadbtn-<?php echo absint($p_rand_id); ?>" type="file" data-randid="<?php echo absint($p_rand_id); ?>" name="cuser_cv_file" class="jobsearch-upload-btn">
                                                        </div>
                                                        <p><?php esc_html_e('Suitable files are .doc,.docx,.pdf', 'wp-jobsearch') ?></p>
                                                    </div>
                                                </li>
                                                <li class="jobsearch-user-form-coltwo-full">
                                                    <input type="hidden" name="job_id" value="<?php echo ($p_job_id) ?>">
                                                    <input type="hidden" name="action" value="jobsearch_applying_job_with_email">
                                                    <?php jobsearch_terms_and_con_link_txt() ?>
                                                    <div class="terms-priv-chek-con">
                                                        <p><input type="checkbox" name="email_commun_check"> <?php esc_html_e('You accepts email communication.', 'wp-jobsearch') ?></p>
                                                    </div>
                                                    <a href="javascript:void(0);" class="<?php echo esc_html($p_classes); ?> jobsearch-applyin-withemail" data-randid="<?php echo absint($p_rand_id); ?>" data-jobid="<?php echo absint($p_job_id); ?>" data-btnafterlabel="<?php echo esc_html($p_btn_after_label) ?>" data-btnbeforelabel="<?php echo esc_html($p_btn_text) ?>"><?php echo esc_html($p_btn_text) ?></a>
                                                </li>
                                            </ul>
                                            <div class="apply-job-form-msg"></div>
                                            <div class="apply-job-loader"></div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php
                    }, 11, 1);
                } else if ($job_extrnal_apply_switch == 'on' && $job_aply_type == 'external' && $job_aply_extrnal_url != '') {
                    ?>
                    <a href="<?php echo ($job_aply_extrnal_url) ?>" class="<?php echo esc_html($classes); ?>" target="_blank"><?php echo esc_html($btn_text) ?></a>
                    <?php
                } else {
                    if ($multi_cvs === true) {
                        ?>
                        <script>
                            jQuery(document).on('click', '.jobsearch-modelcvs-btn-<?php echo ($rand_id) ?>', function () {
                                jobsearch_modal_popup_open('JobSearchModalMultiCVs<?php echo ($rand_id) ?>');
                            });
                        </script>
                        <a href="javascript:void(0);" class="<?php echo esc_html($classes); ?> <?php echo ($is_applied ? '' : 'jobsearch-modelcvs-btn-' . $rand_id) ?>"><?php echo esc_html($btn_text) ?></a>
                        <?php
                        $max_cvs_allow = isset($jobsearch_plugin_options['max_cvs_allow']) && absint($jobsearch_plugin_options['max_cvs_allow']) > 0 ? absint($jobsearch_plugin_options['max_cvs_allow']) : 5;
                        $popup_args = array(
                            'p_job_id' => $job_id,
                            'p_rand_id' => $rand_id,
                            'p_btn_text' => $btn_text,
                            'p_classes' => $classes,
                            'p_classes_str' => $classes_str,
                            'p_btn_after_label' => $btn_after_label,
                            'max_cvs_allow' => $max_cvs_allow,
                        );
                        add_action('wp_footer', function () use ($popup_args) {

                            extract(shortcode_atts(array(
                                'p_job_id' => '',
                                'p_rand_id' => '',
                                'p_btn_text' => '',
                                'p_classes' => '',
                                'p_classes_str' => '',
                                'p_btn_after_label' => '',
                                'max_cvs_allow' => '',
                                            ), $popup_args));
                            ?>
                            <div class="jobsearch-modal fade" id="JobSearchModalMultiCVs<?php echo ($p_rand_id) ?>">
                                <div class="modal-inner-area">&nbsp;</div>
                                <div class="modal-content-area">
                                    <div class="modal-box-area">
                                        <span class="modal-close"><i class="fa fa-times"></i></span>
                                        <div class="jobsearch-apply-withcvs">
                                            <?php
                                            $user_id = get_current_user_id();
                                            $candidate_id = jobsearch_get_user_candidate_id($user_id);
                                            $ca_at_cv_files = get_post_meta($candidate_id, 'candidate_cv_files', true);
                                            $cv_files_count = 0;
                                            if (!empty($ca_at_cv_files)) {
                                                $cv_files_count = count($ca_at_cv_files);
                                                ?>
                                                <div class="jobsearch-modal-title-box">
                                                    <h2><?php esc_html_e('Select CV', 'wp-jobsearch') ?></h2>
                                                </div>
                                                <ul class="user-cvs-list">
                                                    <?php
                                                    foreach ($ca_at_cv_files as $cv_file_key => $cv_file_val) {
                                                        $file_attach_id = isset($cv_file_val['file_id']) ? $cv_file_val['file_id'] : '';
                                                        $file_url = isset($cv_file_val['file_url']) ? $cv_file_val['file_url'] : '';
                                                        $cv_primary = isset($cv_file_val['primary']) ? $cv_file_val['primary'] : '';
                                                        $cv_file_title = get_the_title($file_attach_id);
                                                        $attach_post = get_post($file_attach_id);

                                                        $attach_date = isset($attach_post->post_date) ? $attach_post->post_date : '';
                                                        $attach_mime = isset($attach_post->post_mime_type) ? $attach_post->post_mime_type : '';

                                                        if ($attach_mime == 'application/pdf') {
                                                            $attach_icon = 'fa fa-file-pdf-o';
                                                        } else if ($attach_mime == 'application/msword' || $attach_mime == 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
                                                            $attach_icon = 'fa fa-file-word-o';
                                                        } else {
                                                            $attach_icon = 'fa fa-file-word-o';
                                                        }
                                                        ?>
                                                        <li<?php echo ($cv_primary == 'yes' ? ' class="active"' : '') ?>>
                                                            <i class="<?php echo ($attach_icon) ?>"></i> 
                                                            <label for="cv_file_<?php echo ($cv_file_key) ?>">
                                                                <input id="cv_file_<?php echo ($cv_file_key) ?>" type="radio" class="cv_file_item" name="cv_file_item" <?php echo ($cv_primary == 'yes' ? 'checked="checked"' : '') ?> value="<?php echo ($cv_file_key) ?>">
                                                                <?php echo (strlen($cv_file_title) > 40 ? substr($cv_file_title, 0, 40) . '...' : $cv_file_title) ?>
                                                                <span class="upload-datetime"><i class="fa fa-calendar"></i> <?php echo date_i18n(get_option('date_format'), strtotime($attach_date)) . ' ' . date_i18n(get_option('time_format'), strtotime($attach_date)) ?></span>
                                                            </label>
                                                        </li>
                                                        <?php
                                                    }
                                                    ?>
                                                </ul>
                                                <?php
                                                if (isset($cv_files_count) && $cv_files_count < $max_cvs_allow) {
                                                    ?>
                                                    <div class="upload-cvs-sep">
                                                        <div class="jobsearch-box-title">
                                                            <span><?php esc_html_e('OR', 'wp-jobsearch') ?></span>
                                                        </div>
                                                    </div>
                                                    <?php
                                                }
                                            } else {
                                                ?>
                                                <ul class="user-cvs-list"></ul>
                                                <?php
                                            }
                                            if (isset($cv_files_count) && $cv_files_count < $max_cvs_allow) {
                                                ?>
                                                <div class="upload-new-cv-sec">
                                                    <h4><?php esc_html_e('Upload New CV', 'wp-jobsearch') ?></h4>
                                                    <div id="jobsearch-upload-cv-main" class="jobsearch-upload-cv">
                                                        <input class="jobsearch-disabled-input" id="jobsearch-uploadfile" placeholder="Samaple_CV.pdf" disabled="disabled">
                                                        <div class="jobsearch-cvupload-file">
                                                            <span><i class="jobsearch-icon jobsearch-arrows-2"></i> <?php esc_html_e('Upload CV', 'wp-jobsearch') ?></span>
                                                            <input id="jobsearch-uploadbtn" type="file" name="on_apply_cv_file" class="jobsearch-upload-btn">
                                                            <div class="fileUpLoader"></div>
                                                        </div>
                                                    </div>
                                                    <p><?php esc_html_e('Suitable files are .doc,.docx,.pdf', 'wp-jobsearch') ?></p>
                                                </div>
                                                <?php
                                            }
                                            echo apply_filters('jobsearch_applying_job_after_cv_upload_file', '');
                                            echo apply_filters('jobsearch_applying_job_before_apply', '');
                                            ?>
                                            <a href="javascript:void(0);" class="<?php echo esc_html($p_classes_str); ?> jobsearch-apply-btn-<?php echo absint($p_rand_id); ?> <?php echo esc_html($p_classes); ?>" data-randid="<?php echo absint($p_rand_id); ?>" data-jobid="<?php echo absint($p_job_id); ?>" data-btnafterlabel="<?php echo esc_html($p_btn_after_label) ?>" data-btnbeforelabel="<?php echo esc_html($p_btn_text) ?>"><?php echo esc_html($p_btn_text) ?></a>
                                            <small class="apply-bmsg"></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }, 11, 1);
                    } else {
                        ?>
                        <a href="javascript:void(0);" class="<?php echo esc_html($classes_str); ?> jobsearch-apply-btn-<?php echo absint($rand_id); ?> <?php echo esc_html($classes); ?>" data-randid="<?php echo absint($rand_id); ?>" data-jobid="<?php echo absint($job_id); ?>" data-btnafterlabel="<?php echo esc_html($btn_after_label) ?>" data-btnbeforelabel="<?php echo esc_html($btn_text) ?>"><?php echo esc_html($btn_text) ?></a>
                        <small class="apply-bmsg"></small>
                        <?php
                    }
                }
            }
            $html .= ob_get_clean();
            return $html;
        }

        public function job_application_popup_form($job_id) {
            global $jobsearch_plugin_options;

            $rand_num = rand(100000, 9999999);

            $apply_without_login = isset($jobsearch_plugin_options['job-apply-without-login']) ? $jobsearch_plugin_options['job-apply-without-login'] : '';
            if ($apply_without_login == 'on' && !is_user_logged_in()) {
                ?>
                <div class="jobsearch-modal jobsearch-typo-wrap fade" id="JobSearchNonuserApplyModal">
                    <div class="modal-inner-area">&nbsp;</div>
                    <div class="modal-content-area">
                        <div class="modal-box-area">
                            <div class="jobsearch-modal-title-box">
                                <h2><?php esc_html_e('Apply for this Job', 'wp-jobsearch') ?></h2>
                                <span class="modal-close"><i class="fa fa-times"></i></span>
                            </div>
                            <form id="apply-form-<?php echo absint($rand_num) ?>" method="post">
                                <div class="jobsearch-user-form jobsearch-user-form-coltwo">
                                    <ul class="apply-fields-list"> 
                                        <li> 
                                            <label><?php esc_html_e('Full Name:', 'wp-jobsearch') ?></label>
                                            <input class="required" name="user_fullname" type="text" placeholder="<?php esc_html_e('Full Name', 'wp-jobsearch') ?>">
                                        </li>
                                        <li> 
                                            <label><?php esc_html_e('Email:', 'wp-jobsearch') ?></label>
                                            <input class="required" name="user_email" type="text" placeholder="<?php esc_html_e('Email Address', 'wp-jobsearch') ?>">
                                        </li>
                                        <li> 
                                            <label><?php esc_html_e('Phone:', 'wp-jobsearch') ?></label>
                                            <input class="required" name="user_phone" type="text" placeholder="<?php esc_html_e('Phone Number', 'wp-jobsearch') ?>">
                                        </li>
                                        <li> 
                                            <label><?php esc_html_e('Current Job Title:', 'wp-jobsearch') ?></label>
                                            <input class="required" name="user_job_title" type="text" placeholder="<?php esc_html_e('Current Job Title', 'wp-jobsearch') ?>">
                                        </li>
                                        <li> 
                                            <label><?php esc_html_e('Current Salary:', 'wp-jobsearch') ?></label>
                                            <input class="required" name="user_salary" type="text" placeholder="<?php esc_html_e('Current Salary', 'wp-jobsearch') ?>">
                                        </li>
                                        <?php do_action('jobsearch_form_custom_fields_load', 0, 'candidate'); ?>
                                        <li class="jobsearch-user-form-coltwo-full">
                                            <div id="jobsearch-upload-cv-main" class="jobsearch-upload-cv jobsearch-applyjob-upload-cv">
                                                <label><?php esc_html_e('Curriculum Vitae', 'wp-jobsearch') ?></label>
                                                <input class="jobsearch-disabled-input" id="jobsearch-uploadfile" placeholder="Samaple_CV.pdf" disabled="disabled">
                                                <div class="jobsearch-cvupload-file">
                                                    <span><?php esc_html_e('Upload CV', 'wp-jobsearch') ?></span>
                                                    <input id="jobsearch-uploadbtn" type="file" name="candidate_cv_file" class="jobsearch-upload-btn">
                                                </div>
                                                <p><?php esc_html_e('Suitable files are .doc,.docx,.pdf', 'wp-jobsearch') ?></p>
                                            </div>
                                        </li>
                                        <li class="jobsearch-user-form-coltwo-full">
                                            <input type="hidden" name="action" value="jobsearch_job_apply_without_login">
                                            <input type="hidden" name="job_id" value="<?php echo absint($job_id) ?>">
                                            <?php jobsearch_terms_and_con_link_txt() ?>
                                            <input class="jobsearch-apply-woutreg-btn" data-id="<?php echo absint($rand_num) ?>" type="submit" value="<?php esc_html_e('Apply Job', 'wp-jobsearch') ?>">
                                            <div class="form-loader"></div>
                                        </li>
                                    </ul>
                                    <div class="apply-job-form-msg"></div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php
            }
        }

        public function job_apply_without_login() {

            $job_id = isset($_POST['job_id']) ? $_POST['job_id'] : '';

            $user_name = isset($_POST['user_fullname']) ? $_POST['user_fullname'] : '';
            $user_email = isset($_POST['user_email']) ? $_POST['user_email'] : '';

            $error = 0;

            if ($user_email != '' && $error == 0 && filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
                $user_email = esc_html($user_email);
            } else {
                $error = 1;
                $msg = esc_html__('Please Enter a valid email.', 'wp-jobsearch');
            }
            if ($user_name != '' && $error == 0) {
                $user_name = esc_html($user_name);
            } else {
                $error = 1;
                $msg = esc_html__('Please Enter your Name.', 'wp-jobsearch');
            }

            if ($error == 1) {
                echo json_encode(array('error' => '1', 'msg' => $msg));
                die;
            }

            $email_parts = explode("@", $user_email);
            $user_login = isset($email_parts[0]) ? $email_parts[0] : '';
            if (username_exists($user_login)) {
                $user_login .= '_' . rand(10000, 99999);
            }

            $user_pass = wp_generate_password(12);

            $create_user = wp_create_user($user_login, $user_pass, $user_email);

            if (is_wp_error($create_user)) {

                $registration_error_messages = $create_user->errors;

                $display_errors = '';
                foreach ($registration_error_messages as $error) {
                    $display_errors .= $error[0];
                }

                echo json_encode(array('error' => '1', 'msg' => $display_errors));
                die;
            } else {
                wp_update_user(array('ID' => $create_user, 'role' => 'jobsearch_candidate'));
                if ($user_name != '') {
                    $user_def_array = array(
                        'ID' => $create_user,
                        'display_name' => $user_name,
                    );
                    wp_update_user($user_def_array);
                }

                $candidate_id = $this->jobsearch_job_apply_by_job_id($job_id, $create_user);

                if ($candidate_id > 0) {
                    if (isset($_POST['user_phone'])) {
                        update_post_meta($candidate_id, 'jobsearch_field_user_phone', $_POST['user_phone']);
                    }
                    if (isset($_POST['user_job_title'])) {
                        update_post_meta($candidate_id, 'jobsearch_field_candidate_jobtitle', $_POST['user_job_title']);
                    }
                    if (isset($_POST['user_salary'])) {
                        update_post_meta($candidate_id, 'jobsearch_field_candidate_salary', $_POST['user_salary']);
                    }

                    $atach_id = jobsearch_upload_candidate_cv('candidate_cv_file', $candidate_id);

                    if ($atach_id > 0) {
                        $file_url = wp_get_attachment_url($atach_id);
                        if ($file_url) {
                            $arg_arr = array(
                                'file_id' => $atach_id,
                                'file_url' => $file_url,
                            );
                            update_post_meta($candidate_id, 'candidate_cv_file', $arg_arr);
                            update_post_meta($candidate_id, 'jobsearch_field_user_cv_attachment', $file_url);
                        }
                    }

                    echo json_encode(array('error' => '0', 'msg' => __('Applied Successfully. You can view it after logged in your account. Please check your e-mail address.', 'wp-jobsearch')));
                    $c_user = get_user_by('email', $user_email);
                    do_action('jobsearch_new_user_register', $c_user, $user_pass);
                } else {
                    echo json_encode(array('error' => '1', 'msg' => __('You cannot apply this job.', 'wp-jobsearch')));
                }
            }
            die;
        }

        public function jobsearch_job_apply_by_job_id($job_id, $user_id = '') {
            $candidate_id = jobsearch_get_user_candidate_id($user_id);
            if ($job_id > 0 && $candidate_id > 0) {

                $default_args = array('status' => 1, 'msg' => '');
                $dealine_response = apply_filters('jobsearch_check_job_deadline_date', $default_args, $job_id);

                $job_filled = get_post_meta($job_id, 'jobsearch_field_job_filled', true);
                if ($job_filled == 'on') {
                    return false;
                }
                if ($dealine_response['status'] == 1) {

                    jobsearch_create_user_meta_list($job_id, 'jobsearch-user-jobs-applied-list', $user_id);

                    //
                    $job_applicants_list = get_post_meta($job_id, 'jobsearch_job_applicants_list', true);
                    if ($job_applicants_list != '') {
                        $job_applicants_list = explode(',', $job_applicants_list);
                        if (!in_array($candidate_id, $job_applicants_list)) {
                            $job_applicants_list[] = $candidate_id;
                        }
                        $job_applicants_list = implode(',', $job_applicants_list);
                    } else {
                        $job_applicants_list = $candidate_id;
                    }
                    update_post_meta($job_id, 'jobsearch_job_applicants_list', $job_applicants_list);

                    $user_obj = get_user_by('ID', $user_id);
                    do_action('jobsearch_job_applied_to_employer', $user_obj, $job_id);

                    return $candidate_id;
                }
            }
        }

        public function jobsearch_job_application_submit_callback() {
            $job_id = $_REQUEST['job_id'];

            global $jobsearch_plugin_options;
            $user = jobsearch_get_user_id();
            $response = array();
            if (isset($user) && $user <> '') {

                $free_job_apply = isset($jobsearch_plugin_options['free-job-apply-allow']) ? $jobsearch_plugin_options['free-job-apply-allow'] : '';
                $candidate_pkgs_page = isset($jobsearch_plugin_options['candidate_package_page']) ? $jobsearch_plugin_options['candidate_package_page'] : '';

                $candidate_pkgs_page_url = '';
                if ($candidate_pkgs_page != '') {
                    $candidate_pkgs_page_obj = get_page_by_path($candidate_pkgs_page);
                    if (is_object($candidate_pkgs_page_obj) && isset($candidate_pkgs_page_obj->ID)) {
                        $candidate_pkgs_page_url = get_permalink($candidate_pkgs_page_obj->ID);
                    }
                }

                $candidate_id = jobsearch_get_user_candidate_id($user);

                if ((isset($job_id) && $job_id <> '') && $candidate_id > 0) {

                    $candidate_skills = isset($jobsearch_plugin_options['jobsearch_candidate_skills']) ? $jobsearch_plugin_options['jobsearch_candidate_skills'] : '';
                    if ($candidate_skills == 'on') {
                        $candidate_approve_skill = isset($jobsearch_plugin_options['jobsearch-candidate-skills-percentage']) ? $jobsearch_plugin_options['jobsearch-candidate-skills-percentage'] : 0;
                        $candidate_skill_perc = get_post_meta($candidate_id, 'overall_skills_percentage', true);
                        if (($candidate_approve_skill > 0 && $candidate_skill_perc < $candidate_approve_skill)) {
                            $response['status'] = 0;
                            $response['msg'] = sprintf(esc_html__('You must have atleast %s skills set to apply this job.', 'wp-jobsearch'), $candidate_approve_skill . '%');
                            echo json_encode($response);
                            wp_die();
                        }
                    }

                    $default_args = array('status' => 1, 'msg' => '');

                    $job_filled = get_post_meta($job_id, 'jobsearch_field_job_filled', true);
                    if ($job_filled == 'on') {
                        $response['status'] = 0;
                        $response['msg'] = esc_html__('This job is filled and no longer accepting applications.', 'wp-jobsearch');
                        echo json_encode($response);
                        wp_die();
                    }

                    $dealine_response = apply_filters('jobsearch_check_job_deadline_date', $default_args, $job_id);

                    if ($dealine_response['status'] == 1) {

                        $candidate_status = get_post_meta($candidate_id, 'jobsearch_field_candidate_approved', true);
                        if ($candidate_status != 'on') {
                            $response['status'] = 0;
                            $response['msg'] = esc_html__('Your profile is not approved yet.', 'wp-jobsearch');
                            echo json_encode($response);
                            wp_die();
                        }

                        $job_applicants_list = get_post_meta($job_id, 'jobsearch_job_applicants_list', true);
                        $job_applicants_list = $job_applicants_list != '' ? explode(',', $job_applicants_list) : array();
                        if ($free_job_apply != 'on' && !in_array($candidate_id, $job_applicants_list)) {
                            $user_app_pkg = jobsearch_candidate_first_subscribed_app_pkg();
                            if ($user_app_pkg) {
                                do_action('jobsearch_add_candidate_apply_job_id_to_order', $candidate_id, $user_app_pkg);
                            } else {
                                $response['status'] = 0;
                                if ($candidate_pkgs_page_url != '') {
                                    $response['msg'] = wp_kses(sprintf(__('You have no package. <a href="%s">Click here</a> to subscribe a package.', 'wp-jobsearch'), $candidate_pkgs_page_url), array('a' => array('href' => array())));
                                } else {
                                    $response['msg'] = esc_html__('You have no package. Please subscribe a package first.', 'wp-jobsearch');
                                }
                                echo json_encode($response);
                                wp_die();
                            }
                        }

                        //
                        do_action('jobsearch_job_applying_before_action', $candidate_id, $job_id);
                        //

                        $job_employer = get_post_meta($job_id, 'jobsearch_job_username', true);

                        jobsearch_create_user_meta_list($job_id, 'jobsearch-user-jobs-applied-list', $user);

                        //
                        if (!in_array($candidate_id, $job_applicants_list)) {
                            $job_applicants_list[] = $candidate_id;
                        }
                        if (!empty($job_applicants_list)) {
                            $job_applicants_list = implode(',', $job_applicants_list);
                        } else {
                            $job_applicants_list = '';
                        }
                        update_post_meta($job_id, 'jobsearch_job_applicants_list', $job_applicants_list);
                        if (isset($_POST['attach_cv']) && $_POST['attach_cv'] > 0) {
                            $get_job_apps_cv_att = get_post_meta($job_id, 'job_apps_cv_att', true);
                            $get_job_apps_cv_att = !empty($get_job_apps_cv_att) ? $get_job_apps_cv_att : array();
                            $get_job_apps_cv_att[$candidate_id] = $_POST['attach_cv'];
                            update_post_meta($job_id, 'job_apps_cv_att', $get_job_apps_cv_att);
                        }
                        //
                        do_action('jobsearch_job_applying_save_action', $candidate_id, $job_id);
                        //
                        //
                        $c_user = wp_get_current_user();
                        do_action('jobsearch_job_applied_to_employer', $c_user, $job_id);

                        $response['status'] = 1;
                        $response['msg'] = '<i class="icon-thumbsup"></i><span>' . esc_html__('Applied', 'wp-jobsearch') . '</span>';
                    } else {
                        $response['status'] = 0;
                        $response['msg'] = esc_html__('Application deadline is closed.', 'wp-jobsearch');
                    }
                } else {
                    $response['status'] = 0;
                    $response['msg'] = esc_html__('You are not authorised', 'wp-jobsearch');
                }
            } else {
                $response['status'] = 0;
                $response['msg'] = esc_html__('You have to login first.', 'wp-jobsearch');
            }
            echo json_encode($response);

            wp_die();
        }

        public function job_apply_with_email() {
            $response = array();

            $user_name = isset($_POST['user_fullname']) ? $_POST['user_fullname'] : '';
            $user_surname = isset($_POST['user_surname']) ? $_POST['user_surname'] : '';
            $user_email = isset($_POST['user_email']) ? $_POST['user_email'] : '';
            $user_phone = isset($_POST['user_phone']) ? $_POST['user_phone'] : '';
            $user_msg = isset($_POST['user_msg']) ? $_POST['user_msg'] : '';
            $email_commun_check = isset($_POST['email_commun_check']) ? $_POST['email_commun_check'] : '';

            $job_id = isset($_POST['job_id']) ? $_POST['job_id'] : '';
            
            if ($job_id > 0) {
                $employer_id = get_post_meta($job_id, 'jobsearch_field_job_posted_by', true);
                $job_apply_email = get_post_meta($job_id, 'jobsearch_field_job_apply_email', true);
                if ($job_apply_email == '') {
                    $emp_user_id = jobsearch_get_employer_user_id($employer_id);
                    $user_obj = get_user_by('ID', $emp_user_id);
                    $job_apply_email = $user_obj->user_email;
                }
                // cv file
                $att_file_path = '';
                if (isset($_FILES['cuser_cv_file']) && !empty($_FILES['cuser_cv_file'])) {
                    $att_file_path = jobsearch_cv_attachment_upload_path('cuser_cv_file');
                }
                //
                $apply_data = array(
                    'id' => $job_id,
                    'email' => $job_apply_email,
                    'username' => $user_name,
                    'user_surname' => $user_surname,
                    'user_email' => $user_email,
                    'user_phone' => $user_phone,
                    'user_msg' => $user_msg,
                    'att_file_path' => $att_file_path,
                    'email_commun_check' => $email_commun_check,
                );
                do_action('jobsearch_new_apply_job_by_email', $apply_data);

                $response['error'] = '0';
                $response['msg'] = esc_html__('Job applied Successfully.', 'wp-jobsearch');
            } else {
                $response['error'] = '1';
                $response['msg'] = esc_html__('No job found.', 'wp-jobsearch');
            }
            echo json_encode($response);
            wp_die();
        }

    }

    global $jobsearch_job_application_load;
    $jobsearch_job_application_load = new Jobsearch_JobApplicationLoad();
}