<?php
global $jobsearch_plugin_options, $Jobsearch_User_Dashboard_Settings;
$user_id = get_current_user_id();
$user_obj = get_user_by('ID', $user_id);

$page_id = $user_dashboard_page = isset($jobsearch_plugin_options['user-dashboard-template-page']) ? $jobsearch_plugin_options['user-dashboard-template-page'] : '';
$page_id = $user_dashboard_page = jobsearch__get_post_id($user_dashboard_page, 'page');
$page_url = jobsearch_wpml_lang_page_permalink($page_id, 'page'); //get_permalink($page_id);

$candidate_id = jobsearch_get_user_candidate_id($user_id);

$reults_per_page = isset($jobsearch_plugin_options['user-dashboard-per-page']) && $jobsearch_plugin_options['user-dashboard-per-page'] > 0 ? $jobsearch_plugin_options['user-dashboard-per-page'] : 10;

$page_num = isset($_GET['page_num']) ? $_GET['page_num'] : 1;

if ($candidate_id > 0) {
    $candidate_cv_file = get_post_meta($candidate_id, 'candidate_cv_file', true);
    $multiple_cv_files_allow = isset($jobsearch_plugin_options['multiple_cv_uploads']) ? $jobsearch_plugin_options['multiple_cv_uploads'] : '';
    $max_cvs_allow = isset($jobsearch_plugin_options['max_cvs_allow']) && absint($jobsearch_plugin_options['max_cvs_allow']) > 0 ? absint($jobsearch_plugin_options['max_cvs_allow']) : 5;
    
    $ca_at_cv_files = get_post_meta($candidate_id, 'candidate_cv_files', true);
    ?>
    <div class="jobsearch-employer-box-section">
        <div class="jobsearch-profile-title">
            <h2><?php esc_html_e('CV Manager', 'wp-jobsearch') ?></h2>
        </div>
        <?php
        if ($multiple_cv_files_allow == 'on') {
            ?>
            <div id="com-file-holder">
                <?php
                if (!empty($ca_at_cv_files)) {
                    $cv_files_count = count($ca_at_cv_files);
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

                        if ($file_attach_id > 0) {
                            ?>
                            <div class="jobsearch-cv-manager-list">
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
                                                <a href="javascript:void(0);" class="jobsearch-cv-manager-link jobsearch-del-user-cv" data-id="<?php echo ($file_attach_id) ?>"><i class="jobsearch-icon jobsearch-rubbish"></i></a>
                                                <a href="<?php echo ($file_url) ?>" class="jobsearch-cv-manager-link jobsearch-cv-manager-download" download="<?php echo ($cv_file_title) ?>"><i class="jobsearch-icon jobsearch-download-arrow"></i></a>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                            <?php
                        }
                    }
                }
                ?>
            </div>
            <?php
            if (isset($cv_files_count) && $cv_files_count >= $max_cvs_allow) {
                ?>
                <div id="jobsearch-upload-cv-reached" class="jobsearch-upload-cv">
                    <p><?php esc_html_e('You have uploaded maximum CV files. Remove one of your CV files to upload new file.', 'wp-jobsearch') ?></p>
                </div>
                <?php
            } else {
                ?>
                <div id="jobsearch-upload-cv-main" class="jobsearch-upload-cv">
                    <small><?php esc_html_e('Curriculum Vitae', 'wp-jobsearch') ?></small>
                    <input class="jobsearch-disabled-input" id="jobsearch-uploadfile" placeholder="Samaple_CV.pdf" disabled="disabled">
                    <div class="jobsearch-cvupload-file">
                        <span><i class="jobsearch-icon jobsearch-arrows-2"></i> <?php esc_html_e('Upload CV', 'wp-jobsearch') ?></span>
                        <input id="jobsearch-uploadbtn" type="file" name="candidate_cv_file" class="jobsearch-upload-btn">
                        <div class="fileUpLoader"></div>
                    </div>
                    <p><?php esc_html_e('Suitable files are .doc,.docx,.pdf', 'wp-jobsearch') ?></p>
                </div>
                <?php
            }
        } else {
            ?>
            <div id="jobsearch-upload-cv-main" class="jobsearch-upload-cv" style="display: <?php echo (empty($candidate_cv_file) ? 'block' : 'none') ?>;">
                 <small><?php esc_html_e('Curriculum Vitae', 'wp-jobsearch') ?></small>
                 <input class="jobsearch-disabled-input" id="jobsearch-uploadfile" placeholder="Samaple_CV.pdf" disabled="disabled">
                 <div class="jobsearch-cvupload-file">
                    <span><i class="jobsearch-icon jobsearch-arrows-2"></i> <?php esc_html_e('Upload CV', 'wp-jobsearch') ?></span>
                    <input id="jobsearch-uploadbtn" type="file" name="candidate_cv_file" class="jobsearch-upload-btn">
                    <div class="fileUpLoader"></div>
                </div>
                <p><?php esc_html_e('Suitable files are .doc,.docx,.pdf', 'wp-jobsearch') ?></p>
            </div>
            <div id="com-file-holder">
                <?php
                if (!empty($candidate_cv_file)) {
                    $file_attach_id = isset($candidate_cv_file['file_id']) ? $candidate_cv_file['file_id'] : '';
                    $file_url = isset($candidate_cv_file['file_url']) ? $candidate_cv_file['file_url'] : '';

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

                    if ($file_attach_id > 0) {
                        ?>
                        <div class="jobsearch-cv-manager-list">
                            <ul class="jobsearch-row">
                                <li class="jobsearch-column-12">
                                    <div class="jobsearch-cv-manager-wrap">
                                        <a class="jobsearch-cv-manager-thumb"><i class="<?php echo ($attach_icon) ?>"></i></a>
                                        <div class="jobsearch-cv-manager-text">
                                            <div class="jobsearch-cv-manager-left">
                                                <h2><a href="<?php echo ($file_url) ?>" download="<?php echo ($cv_file_title) ?>"><?php echo ($cv_file_title) ?></a></h2>
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
                                            <a href="javascript:void(0);" class="jobsearch-cv-manager-link jobsearch-del-user-cv" data-id="<?php echo ($file_attach_id) ?>"><i class="jobsearch-icon jobsearch-rubbish"></i></a>
                                            <a href="<?php echo ($file_url) ?>" class="jobsearch-cv-manager-link jobsearch-cv-manager-download" download="<?php echo ($cv_file_title) ?>"><i class="jobsearch-icon jobsearch-download-arrow"></i></a>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
            <?php
        }
        echo apply_filters('jobsearch_dashboard_after_cv_upload_files', '');
        ?>
    </div>
    <?php
}    