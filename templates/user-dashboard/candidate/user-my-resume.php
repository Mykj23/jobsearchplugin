<?php
global $jobsearch_plugin_options, $Jobsearch_User_Dashboard_Settings, $diff_form_errs;
$user_id = get_current_user_id();
$user_obj = get_user_by('ID', $user_id);

$page_id = $user_dashboard_page = isset($jobsearch_plugin_options['user-dashboard-template-page']) ? $jobsearch_plugin_options['user-dashboard-template-page'] : '';
$page_id = $user_dashboard_page = jobsearch__get_post_id($user_dashboard_page, 'page');
$page_url = jobsearch_wpml_lang_page_permalink($page_id, 'page'); //get_permalink($page_id);

$candidate_id = jobsearch_get_user_candidate_id($user_id);

$reults_per_page = isset($jobsearch_plugin_options['user-dashboard-per-page']) && $jobsearch_plugin_options['user-dashboard-per-page'] > 0 ? $jobsearch_plugin_options['user-dashboard-per-page'] : 10;

$page_num = isset($_GET['page_num']) ? $_GET['page_num'] : 1;

if ($candidate_id > 0) {
    $cover_letter = get_post_meta($candidate_id, 'jobsearch_field_resume_cover_letter', true);
    ?>
    <form method="post" class="jobsearch-candidate-dasboard" action="<?php echo add_query_arg(array('tab' => 'my-resume'), $page_url) ?>">
        <div class="jobsearch-employer-box-section">
            <div class="jobsearch-profile-title">
                <h2><?php esc_html_e('My Resume', 'wp-jobsearch') ?></h2>
            </div>

            <div class="jobsearch-candidate-section">
                <?php
                if (isset($_POST['user_resume_form']) && $_POST['user_resume_form'] == '1') {
                    if (isset($diff_form_errs['user_not_allow_mod']) && $diff_form_errs['user_not_allow_mod'] == true) {
                        ?>
                        <div class="jobsearch-alert jobsearch-error-alert">
                            <p><?php echo wp_kses(__('<strong>Error!</strong> You are not allowed to modify settings.', 'wp-jobsearch'), array('strong' => array())) ?></p>
                        </div>
                        <?php
                    }
                }
                ?>
                <div class="jobsearch-candidate-title"> <h2><i class="jobsearch-icon jobsearch-resume-1"></i> <?php esc_html_e('Cover Letter', 'wp-jobsearch') ?></h2> </div>
                <div class="jobsearch-candidate-dashboard-editor">
                    <?php
                    $settings = array(
                        'media_buttons' => false,
                        'quicktags' => array('buttons' => 'strong,em,del,ul,ol,li,close'),
                        'tinymce' => array(
                            'toolbar1' => 'bold,bullist,numlist,italic,underline,alignleft,aligncenter,alignright,separator,link,unlink,undo,redo',
                            'toolbar2' => '',
                            'toolbar3' => '',
                        ),
                    );

                    wp_editor($cover_letter, 'jobsearch_field_resume_cover_letter', $settings);
                    ?>
                </div>
                <?php echo apply_filters('jobsearch_candidate_dash_resume_after_cover', '', $candidate_id) ?>

                <div class="jobsearch-candidate-resume-wrap">    
                    <div class="jobsearch-candidate-title"> 
                        <h2>
                            <i class="jobsearch-icon jobsearch-mortarboard"></i> <?php esc_html_e('Education', 'wp-jobsearch') ?> 
                            <a href="javascript:void(0)" class="jobsearch-resume-addbtn"><span class="fa fa-plus"></span> <?php esc_html_e('Add education', 'wp-jobsearch') ?></a>
                        </h2>
                    </div>

                    <div class="jobsearch-add-popup jobsearch-add-resume-item-popup">
                        <span class="close-popup-item"><i class="fa fa-times"></i></span>
                        <ul class="jobsearch-row jobsearch-employer-profile-form">
                            <li class="jobsearch-column-12">
                                <label><?php esc_html_e('Title *', 'wp-jobsearch') ?></label>
                                <input id="add-edu-title" class="jobsearch-req-field" type="text">
                            </li>
                            <li class="jobsearch-column-6">
                                <label><?php esc_html_e('Year *', 'wp-jobsearch') ?></label>
                                <input id="add-edu-year" class="jobsearch-req-field" type="text">
                            </li>
                            <li class="jobsearch-column-6">
                                <label><?php esc_html_e('Institute *', 'wp-jobsearch') ?></label>
                                <input id="add-edu-institute" class="jobsearch-req-field" type="text">
                            </li>
                            <li class="jobsearch-column-12">
                                <label><?php esc_html_e('Description', 'wp-jobsearch') ?></label>
                                <textarea id="add-edu-desc"></textarea>
                            </li>
                            <li class="jobsearch-column-12">
                                <input id="add-education-btn" type="submit" value="<?php esc_html_e('Add education', 'wp-jobsearch') ?>">
                                <span class="edu-loding-msg"></span>
                            </li>
                        </ul>
                    </div>
                    <div id="jobsearch-resume-edu-con" class="jobsearch-resume-education">
                        <ul class="jobsearch-row">
                            <?php
                            $exfield_list = get_post_meta($candidate_id, 'jobsearch_field_education_title', true);
                            $exfield_list_val = get_post_meta($candidate_id, 'jobsearch_field_education_description', true);
                            $education_academyfield_list = get_post_meta($candidate_id, 'jobsearch_field_education_academy', true);
                            $education_yearfield_list = get_post_meta($candidate_id, 'jobsearch_field_education_year', true);
                            if (is_array($exfield_list) && sizeof($exfield_list) > 0) {

                                $exfield_counter = 0;
                                foreach ($exfield_list as $exfield) {
                                    $rand_num = rand(1000000, 99999999);

                                    $exfield_val = isset($exfield_list_val[$exfield_counter]) ? $exfield_list_val[$exfield_counter] : '';
                                    $education_academyfield_val = isset($education_academyfield_list[$exfield_counter]) ? $education_academyfield_list[$exfield_counter] : '';
                                    $education_yearfield_val = isset($education_yearfield_list[$exfield_counter]) ? $education_yearfield_list[$exfield_counter] : '';
                                    ?>
                                    <li class="jobsearch-column-12 resume-list-item resume-list-edu">
                                        <div class="jobsearch-resume-education-wrap">
                                            <small><?php echo ($education_yearfield_val) ?></small>
                                            <h2><a><?php echo ($exfield) ?></a></h2>
                                            <span><?php echo ($education_academyfield_val) ?></span>
                                        </div>
                                        <div class="jobsearch-resume-education-btn">
                                            <a href="javascript:void(0);" class="jobsearch-icon jobsearch-edit update-resume-item"></a>
                                            <a href="javascript:void(0);" class="jobsearch-icon jobsearch-rubbish del-resume-item"></a>
                                        </div>
                                        <div class="jobsearch-add-popup jobsearch-update-resume-items-sec">
                                            <span class="close-popup-item"><i class="fa fa-times"></i></span>
                                            <ul class="jobsearch-row jobsearch-employer-profile-form">
                                                <li class="jobsearch-column-12">
                                                    <label><?php esc_html_e('Title *', 'wp-jobsearch') ?></label>
                                                    <input name="jobsearch_field_education_title[]" type="text" value="<?php echo ($exfield) ?>">
                                                </li>
                                                <li class="jobsearch-column-6">
                                                    <label><?php esc_html_e('Year *', 'wp-jobsearch') ?></label>
                                                    <input name="jobsearch_field_education_year[]" type="text" value="<?php echo ($education_yearfield_val) ?>">
                                                </li>
                                                <li class="jobsearch-column-6">
                                                    <label><?php esc_html_e('Institute *', 'wp-jobsearch') ?></label>
                                                    <input name="jobsearch_field_education_academy[]" type="text" value="<?php echo ($education_academyfield_val) ?>">
                                                </li>
                                                <li class="jobsearch-column-12">
                                                    <label><?php esc_html_e('Description', 'wp-jobsearch') ?></label>
                                                    <textarea name="jobsearch_field_education_description[]"><?php echo ($exfield_val) ?></textarea>
                                                </li>
                                                <li class="jobsearch-column-12">
                                                    <input class="update-resume-list-btn" type="submit" value="<?php esc_html_e('Update', 'wp-jobsearch') ?>">
                                                </li>
                                            </ul>
                                        </div>
                                    </li>
                                    <?php
                                    $exfield_counter++;
                                }
                            }
                            ?>
                        </ul>
                    </div>
                </div>
                <div class="jobsearch-candidate-resume-wrap">    
                    <div class="jobsearch-candidate-title"> 
                        <h2>
                            <i class="jobsearch-icon jobsearch-social-media"></i> <?php esc_html_e('Experience', 'wp-jobsearch') ?> 
                            <a href="javascript:void(0)" class="jobsearch-resume-addbtn"><span class="fa fa-plus"></span> <?php esc_html_e('Add experience', 'wp-jobsearch') ?></a>
                        </h2> 
                    </div>

                    <div class="jobsearch-add-popup jobsearch-add-resume-item-popup">
                        <span class="close-popup-item"><i class="fa fa-times"></i></span>
                        <script>
                            jQuery(document).ready(function () {
                                jQuery('#add-expr-date-start').datetimepicker({
                                    timepicker: false,
                                    format: 'Y-m-d',
                                    onShow: function (ct) {
                                        this.setOptions({
                                            maxDate: jQuery('#add-expr-date-end').val() ? jQuery('#add-expr-date-end').val() : false
                                        })
                                    },
                                });
                                jQuery('#add-expr-date-end').datetimepicker({
                                    timepicker: false,
                                    format: 'Y-m-d',
                                    onShow: function (ct) {
                                        this.setOptions({
                                            minDate: jQuery('#add-expr-date-start').val() ? jQuery('#add-expr-date-start').val() : false
                                        })
                                    },
                                });
                                jQuery('input[name^="jobsearch_field_experience_start_date"]').datetimepicker({
                                    timepicker: false,
                                    format: 'Y-m-d',
                                    onShow: function (ct) {
                                        this.setOptions({
                                            maxDate: jQuery('input[name^="jobsearch_field_experience_end_date"]').val() ? jQuery('input[name^="jobsearch_field_experience_end_date"]').val() : false
                                        })
                                    },
                                });
                                jQuery('input[name^="jobsearch_field_experience_end_date"]').datetimepicker({
                                    timepicker: false,
                                    format: 'Y-m-d',
                                    onShow: function (ct) {
                                        this.setOptions({
                                            minDate: jQuery('input[name^="jobsearch_field_experience_start_date"]').val() ? jQuery('input[name^="jobsearch_field_experience_start_date"]').val() : false
                                        })
                                    },
                                });
                            });
                        </script>
                        <ul class="jobsearch-row jobsearch-employer-profile-form">
                            <li class="jobsearch-column-12">
                                <label><?php esc_html_e('Title *', 'wp-jobsearch') ?></label>
                                <input id="add-expr-title" class="jobsearch-req-field" type="text">
                            </li>
                            <li class="jobsearch-column-6">
                                <label><?php esc_html_e('From Date *', 'wp-jobsearch') ?></label>
                                <input id="add-expr-date-start" class="jobsearch-req-field" type="text">
                            </li>
                            <li class="jobsearch-column-6">
                                <label><?php esc_html_e('To Date', 'wp-jobsearch') ?></label>
                                <input id="add-expr-date-end" type="text">
                            </li>
                            <li class="jobsearch-column-12">
                                <label><?php esc_html_e('Company *', 'wp-jobsearch') ?></label>
                                <input id="add-expr-company" class="jobsearch-req-field" type="text">
                            </li>
                            <li class="jobsearch-column-12">
                                <label><?php esc_html_e('Description', 'wp-jobsearch') ?></label>
                                <textarea id="add-expr-desc"></textarea>
                            </li>
                            <li class="jobsearch-column-12">
                                <input id="add-experience-btn" type="submit" value="<?php esc_html_e('Add experience', 'wp-jobsearch') ?>">
                                <span class="expr-loding-msg edu-loding-msg"></span>
                            </li>
                        </ul>
                    </div>
                    <div id="jobsearch-resume-expr-con" class="jobsearch-resume-education">
                        <ul class="jobsearch-row">
                            <?php
                            $exfield_list = get_post_meta($candidate_id, 'jobsearch_field_experience_title', true);
                            $exfield_list_val = get_post_meta($candidate_id, 'jobsearch_field_experience_description', true);
                            $experience_start_datefield_list = get_post_meta($candidate_id, 'jobsearch_field_experience_start_date', true);
                            $experience_end_datefield_list = get_post_meta($candidate_id, 'jobsearch_field_experience_end_date', true);
                            $experience_company_field_list = get_post_meta($candidate_id, 'jobsearch_field_experience_company', true);
                            if (is_array($exfield_list) && sizeof($exfield_list) > 0) {

                                $exfield_counter = 0;
                                foreach ($exfield_list as $exfield) {
                                    $rand_num = rand(1000000, 99999999);

                                    $exfield_val = isset($exfield_list_val[$exfield_counter]) ? $exfield_list_val[$exfield_counter] : '';
                                    $experience_start_datefield_val = isset($experience_start_datefield_list[$exfield_counter]) ? $experience_start_datefield_list[$exfield_counter] : '';
                                    $experience_end_datefield_val = isset($experience_end_datefield_list[$exfield_counter]) ? $experience_end_datefield_list[$exfield_counter] : '';
                                    $experience_end_companyfield_val = isset($experience_company_field_list[$exfield_counter]) ? $experience_company_field_list[$exfield_counter] : '';
                                    ?>
                                    <li class="jobsearch-column-12 resume-list-item resume-list-exp">
                                        <div class="jobsearch-resume-education-wrap">
                                            <small><?php echo ($experience_start_datefield_val != '' ? date_i18n('d M, Y', strtotime($experience_start_datefield_val)) : '') ?></small>
                                            <h2><a><?php echo ($exfield) ?></a></h2>
                                            <span><?php echo ($experience_end_companyfield_val) ?></span>
                                        </div>
                                        <div class="jobsearch-resume-education-btn">
                                            <a href="javascript:void(0);" class="jobsearch-icon jobsearch-edit update-resume-item"></a>
                                            <a href="javascript:void(0);" class="jobsearch-icon jobsearch-rubbish del-resume-item"></a>
                                        </div>
                                        <div class="jobsearch-add-popup jobsearch-update-resume-items-sec">
                                            <span class="close-popup-item"><i class="fa fa-times"></i></span>
                                            <ul class="jobsearch-row jobsearch-employer-profile-form">
                                                <li class="jobsearch-column-12">
                                                    <label><?php esc_html_e('Title *', 'wp-jobsearch') ?></label>
                                                    <input name="jobsearch_field_experience_title[]" type="text" value="<?php echo ($exfield) ?>">
                                                </li>
                                                <li class="jobsearch-column-6">
                                                    <label><?php esc_html_e('Start Date *', 'wp-jobsearch') ?></label>
                                                    <input name="jobsearch_field_experience_start_date[]" type="text" value="<?php echo ($experience_start_datefield_val) ?>">
                                                </li>
                                                <li class="jobsearch-column-6">
                                                    <label><?php esc_html_e('End Date', 'wp-jobsearch') ?></label>
                                                    <input name="jobsearch_field_experience_end_date[]" type="text" value="<?php echo ($experience_end_datefield_val) ?>">
                                                </li>
                                                <li class="jobsearch-column-6">
                                                    <label><?php esc_html_e('Company *', 'wp-jobsearch') ?></label>
                                                    <input name="jobsearch_field_experience_company[]" type="text" value="<?php echo ($experience_end_companyfield_val) ?>">
                                                </li>
                                                <li class="jobsearch-column-12">
                                                    <label><?php esc_html_e('Description', 'wp-jobsearch') ?></label>
                                                    <textarea name="jobsearch_field_experience_description[]"><?php echo ($exfield_val) ?></textarea>
                                                </li>
                                                <li class="jobsearch-column-12">
                                                    <input class="update-resume-list-btn" type="submit" value="<?php esc_html_e('Update', 'wp-jobsearch') ?>">
                                                </li>
                                            </ul>
                                        </div>
                                    </li>
                                    <?php
                                    $exfield_counter++;
                                }
                            }
                            ?>
                        </ul>
                    </div>
                </div>
                <div class="jobsearch-candidate-resume-wrap">    
                    <div class="jobsearch-candidate-title"> 
                        <h2>
                            <i class="jobsearch-icon jobsearch-briefcase"></i> <?php esc_html_e('Portfolio', 'wp-jobsearch') ?>
                            <a href="javascript:void(0)" class="jobsearch-resume-addbtn jobsearch-portfolio-add-btn"><span class="fa fa-plus"></span> <?php esc_html_e('Add Portfolio', 'wp-jobsearch') ?> </a>
                        </h2> 
                    </div>
                    <div class="jobsearch-add-popup jobsearch-add-resume-item-popup">
                        <span class="close-popup-item"><i class="fa fa-times"></i></span>
                        <ul class="jobsearch-row jobsearch-employer-profile-form">
                            <li class="jobsearch-column-12">
                                <label><?php esc_html_e('Title *', 'wp-jobsearch') ?></label>
                                <input id="add-portfolio-title" class="jobsearch-req-field" type="text">
                            </li>
                            <li class="jobsearch-column-6">
                                <label><?php esc_html_e('Image *', 'wp-jobsearch') ?></label>
                                <div class="upload-img-holder-sec">
                                    <span class="file-loader"></span>
                                    <img src="" alt="">
                                    <br>
                                    <input name="add_portfolio_img" type="file" style="display: none;">
                                    <input type="hidden" id="add-portfolio-img-input" class="jobsearch-req-field">
                                    <a href="javascript:void(0)" class="upload-port-img-btn"><i class="jobsearch-icon jobsearch-add"></i> <?php esc_html_e('Upload Photo', 'wp-jobsearch') ?></a>
                                </div>
                            </li>
                            <li class="jobsearch-column-12">
                                <label><?php esc_html_e('Video URL', 'wp-jobsearch') ?></label>
                                <input id="add-portfolio-vurl" type="text">
                                <em><?php esc_html_e('Add video url of youtube, vimeo.', 'wp-jobsearch') ?></em>
                            </li>
                            <li class="jobsearch-column-12">
                                <label><?php esc_html_e('URL', 'wp-jobsearch') ?></label>
                                <input id="add-portfolio-url" type="text">
                            </li>
                            <li class="jobsearch-column-12">
                                <input type="submit" id="add-resume-portfolio-btn" value="<?php esc_html_e('Add Portfolio', 'wp-jobsearch') ?>">
                                <span class="portfolio-loding-msg edu-loding-msg"></span>
                            </li>
                        </ul>
                    </div>

                    <div id="jobsearch-resume-portfolio-con" class="jobsearch-company-gallery">
                        <ul class="jobsearch-row jobsearch-portfolios-list-con">

                            <?php
                            $exfield_list = get_post_meta($candidate_id, 'jobsearch_field_portfolio_title', true);
                            $exfield_list_val = get_post_meta($candidate_id, 'jobsearch_field_portfolio_image', true);
                            $exfield_portfolio_url = get_post_meta($candidate_id, 'jobsearch_field_portfolio_url', true);
                            $exfield_portfolio_vurl = get_post_meta($candidate_id, 'jobsearch_field_portfolio_vurl', true);
                            if (is_array($exfield_list) && sizeof($exfield_list) > 0) {

                                $exfield_counter = 0;
                                foreach ($exfield_list as $exfield) {
                                    $rand_num = rand(1000000, 99999999);

                                    $portfolio_img = isset($exfield_list_val[$exfield_counter]) ? $exfield_list_val[$exfield_counter] : '';
                                    $portfolio_url = isset($exfield_portfolio_url[$exfield_counter]) ? $exfield_portfolio_url[$exfield_counter] : '';
                                    $portfolio_vurl = isset($exfield_portfolio_vurl[$exfield_counter]) ? $exfield_portfolio_vurl[$exfield_counter] : '';
                                    ?>
                                    <li class="jobsearch-column-3 resume-list-item resume-list-port">
                                        <figure>
                                            <a class="portfolio-img-holder"><span style="background-image: url('<?php echo ($portfolio_img) ?>');"></span></a>
                                            <figcaption>
                                                <span><?php echo ($exfield) ?></span>
                                                <div class="jobsearch-company-links">
                                                    <a href="javascript:void(0);" class="jobsearch-icon jobsearch-edit update-resume-item"></a>
                                                    <a href="javascript:void(0);" class="jobsearch-icon jobsearch-rubbish del-resume-item"></a>
                                                </div>
                                            </figcaption>
                                        </figure>
                                        <div class="jobsearch-add-popup jobsearch-update-resume-items-sec">
                                            <span class="close-popup-item"><i class="fa fa-times"></i></span>
                                            <ul class="jobsearch-row jobsearch-employer-profile-form">
                                                <li class="jobsearch-column-12">
                                                    <label><?php esc_html_e('Title *', 'wp-jobsearch') ?></label>
                                                    <input name="jobsearch_field_portfolio_title[]" type="text" value="<?php echo ($exfield) ?>">
                                                </li>
                                                <li class="jobsearch-column-6">
                                                    <label><?php esc_html_e('Image *', 'wp-jobsearch') ?></label>
                                                    <div class="upload-img-holder-sec">
                                                        <span class="file-loader"></span>
                                                        <img src="<?php echo ($portfolio_img) ?>" alt="">
                                                        <br>
                                                        <input name="add_portfolio_img" type="file" style="display: none;">
                                                        <input type="hidden" class="img-upload-save-field" name="jobsearch_field_portfolio_image[]" value="<?php echo ($portfolio_img) ?>">
                                                        <a href="javascript:void(0)" class="upload-port-img-btn"><i class="jobsearch-icon jobsearch-add"></i> <?php esc_html_e('Upload Photo', 'wp-jobsearch') ?></a>
                                                    </div>
                                                </li>
                                                <li class="jobsearch-column-12">
                                                    <label><?php esc_html_e('Video URL', 'wp-jobsearch') ?></label>
                                                    <input name="jobsearch_field_portfolio_vurl[]" type="text" value="<?php echo ($portfolio_vurl) ?>">
                                                    <em><?php esc_html_e('Add video url of youtube, vimeo.', 'wp-jobsearch') ?></em>
                                                </li>
                                                <li class="jobsearch-column-12">
                                                    <label><?php esc_html_e('URL', 'wp-jobsearch') ?></label>
                                                    <input name="jobsearch_field_portfolio_url[]" type="text" value="<?php echo ($portfolio_url) ?>">
                                                </li>
                                                <li class="jobsearch-column-12">
                                                    <input class="update-resume-list-btn" type="submit" value="<?php esc_html_e('Update', 'wp-jobsearch') ?>">
                                                </li>
                                            </ul>
                                        </div>
                                    </li>
                                    <?php
                                    $exfield_counter++;
                                }
                            }
                            ?>

                        </ul>
                    </div>
                </div>
                <div class="jobsearch-candidate-resume-wrap">    
                    <div class="jobsearch-candidate-title"> 
                        <h2>
                            <i class="jobsearch-icon jobsearch-design-skills"></i> <?php esc_html_e('Skills', 'wp-jobsearch') ?> 
                            <a href="javascript:void(0)" class="jobsearch-resume-addbtn"><span class="fa fa-plus"></span> <?php esc_html_e('Add Skills', 'wp-jobsearch') ?></a>
                        </h2> 
                    </div>

                    <div class="jobsearch-add-popup jobsearch-add-resume-item-popup">
                        <span class="close-popup-item"><i class="fa fa-times"></i></span>
                        <ul class="jobsearch-row jobsearch-employer-profile-form">
                            <li class="jobsearch-column-12">
                                <label><?php esc_html_e('Skill Heading *', 'wp-jobsearch') ?></label>
                                <input id="add-skill-title" class="jobsearch-req-field" type="text">
                            </li>
                            <li class="jobsearch-column-12">
                                <label><?php esc_html_e('Percentage *', 'wp-jobsearch') ?></label>
                                <input id="add-skill-percentage" class="jobsearch-req-field" type="text">
                            </li>
                            <li class="jobsearch-column-12">
                                <input type="submit" id="add-resume-skills-btn" value="<?php esc_html_e('Add Skills', 'wp-jobsearch') ?>">
                                <span class="skills-loding-msg edu-loding-msg"></span>
                            </li>
                        </ul>
                    </div>
                    <div id="jobsearch-resume-skills-con" class="jobsearch-add-skills">
                        <ul class="jobsearch-row">
                            <?php
                            $exfield_list = get_post_meta($candidate_id, 'jobsearch_field_skill_title', true);
                            $skill_percentagefield_list = get_post_meta($candidate_id, 'jobsearch_field_skill_percentage', true);
                            if (is_array($exfield_list) && sizeof($exfield_list) > 0) {

                                $exfield_counter = 0;
                                foreach ($exfield_list as $exfield) {
                                    $rand_num = rand(1000000, 99999999);

                                    $skill_percentagefield_val = isset($skill_percentagefield_list[$exfield_counter]) ? $skill_percentagefield_list[$exfield_counter] : '';
                                    ?>
                                    <li class="jobsearch-column-12 resume-list-item resume-list-skill">
                                        <div class="jobsearch-add-skills-wrap">
                                            <span><?php echo ($skill_percentagefield_val) ?></span>
                                            <h2><a><?php echo ($exfield) ?></a></h2>
                                        </div>
                                        <div class="jobsearch-resume-education-btn">
                                            <a href="javascript:void(0);" class="jobsearch-icon jobsearch-edit update-resume-item"></a>
                                            <a href="javascript:void(0);" class="jobsearch-icon jobsearch-rubbish del-resume-item"></a>
                                        </div>
                                        <div class="jobsearch-add-popup jobsearch-update-resume-items-sec">
                                            <span class="close-popup-item"><i class="fa fa-times"></i></span>
                                            <ul class="jobsearch-row jobsearch-employer-profile-form">
                                                <li class="jobsearch-column-12">
                                                    <label><?php esc_html_e('Title *', 'wp-jobsearch') ?></label>
                                                    <input name="jobsearch_field_skill_title[]" type="text" value="<?php echo ($exfield) ?>">
                                                </li>
                                                <li class="jobsearch-column-6">
                                                    <label><?php esc_html_e('Percentage *', 'wp-jobsearch') ?></label>
                                                    <input name="jobsearch_field_skill_percentage[]" type="text" value="<?php echo ($skill_percentagefield_val) ?>">
                                                </li>
                                                <li class="jobsearch-column-12">
                                                    <input class="update-resume-list-btn" type="submit" value="<?php esc_html_e('Update', 'wp-jobsearch') ?>">
                                                </li>
                                            </ul>
                                        </div>
                                    </li>
                                    <?php
                                    $exfield_counter++;
                                }
                            }
                            ?>
                        </ul>
                    </div>
                </div>
                <div class="jobsearch-candidate-resume-wrap">    
                    <div class="jobsearch-candidate-title"> 
                        <h2>
                            <i class="jobsearch-icon jobsearch-trophy"></i> <?php esc_html_e('Honors & Awards', 'wp-jobsearch') ?> 
                            <a href="javascript:void(0)" class="jobsearch-resume-addbtn"><span class="fa fa-plus"></span> <?php esc_html_e('Add Award', 'wp-jobsearch') ?></a>
                        </h2> 
                    </div>

                    <div class="jobsearch-add-popup jobsearch-add-resume-item-popup">
                        <span class="close-popup-item"><i class="fa fa-times"></i></span>
                        <ul class="jobsearch-row jobsearch-employer-profile-form">
                            <li class="jobsearch-column-6">
                                <label><?php esc_html_e('Award Title *', 'wp-jobsearch') ?></label>
                                <input id="add-award-title" class="jobsearch-req-field" type="text">
                            </li>
                            <li class="jobsearch-column-6">
                                <label><?php esc_html_e('Year *', 'wp-jobsearch') ?></label>
                                <input id="add-award-year" class="jobsearch-req-field" type="text">
                            </li>
                            <li class="jobsearch-column-12">
                                <label><?php esc_html_e('Description', 'wp-jobsearch') ?></label>
                                <textarea id="add-award-desc"></textarea>
                            </li>
                            <li class="jobsearch-column-12">
                                <input id="add-resume-awards-btn" type="submit" value="<?php esc_html_e('Add Award', 'wp-jobsearch') ?>">
                                <span class="awards-loding-msg edu-loding-msg"></span>
                            </li>
                        </ul>
                    </div>
                    <div id="jobsearch-resume-awards-con" class="jobsearch-resume-education jobsearch-resume-awards">
                        <ul class="jobsearch-row">
                            <?php
                            $exfield_list = get_post_meta($candidate_id, 'jobsearch_field_award_title', true);
                            $exfield_list_val = get_post_meta($candidate_id, 'jobsearch_field_award_description', true);
                            $award_yearfield_list = get_post_meta($candidate_id, 'jobsearch_field_award_year', true);
                            if (is_array($exfield_list) && sizeof($exfield_list) > 0) {

                                $exfield_counter = 0;
                                foreach ($exfield_list as $exfield) {
                                    $rand_num = rand(1000000, 99999999);

                                    $exfield_val = isset($exfield_list_val[$exfield_counter]) ? $exfield_list_val[$exfield_counter] : '';
                                    $award_yearfield_val = isset($award_yearfield_list[$exfield_counter]) ? $award_yearfield_list[$exfield_counter] : '';
                                    ?>
                                    <li class="jobsearch-column-12 resume-list-item resume-list-award">
                                        <div class="jobsearch-resume-education-wrap">
                                            <small><?php echo ($award_yearfield_val) ?></small>
                                            <h2><a><?php echo ($exfield) ?></a></h2>
                                        </div>
                                        <div class="jobsearch-resume-education-btn">
                                            <a href="javascript:void(0);" class="jobsearch-icon jobsearch-edit update-resume-item"></a>
                                            <a href="javascript:void(0);" class="jobsearch-icon jobsearch-rubbish del-resume-item"></a>
                                        </div>
                                        <div class="jobsearch-add-popup jobsearch-update-resume-items-sec">
                                            <span class="close-popup-item"><i class="fa fa-times"></i></span>
                                            <ul class="jobsearch-row jobsearch-employer-profile-form">
                                                <li class="jobsearch-column-6">
                                                    <label><?php esc_html_e('Title *', 'wp-jobsearch') ?></label>
                                                    <input name="jobsearch_field_award_title[]" type="text" value="<?php echo ($exfield) ?>">
                                                </li>
                                                <li class="jobsearch-column-6">
                                                    <label><?php esc_html_e('Year *', 'wp-jobsearch') ?></label>
                                                    <input name="jobsearch_field_award_year[]" type="text" value="<?php echo ($award_yearfield_val) ?>">
                                                </li>
                                                <li class="jobsearch-column-12">
                                                    <label><?php esc_html_e('Description', 'wp-jobsearch') ?></label>
                                                    <textarea name="jobsearch_field_award_description[]"><?php echo ($exfield_val) ?></textarea>
                                                </li>
                                                <li class="jobsearch-column-12">
                                                    <input class="update-resume-list-btn" type="submit" value="<?php esc_html_e('Update', 'wp-jobsearch') ?>">
                                                </li>
                                            </ul>
                                        </div>
                                    </li>
                                    <?php
                                    $exfield_counter++;
                                }
                            }
                            ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" name="user_resume_form" value="1">
        <input type="submit" class="jobsearch-employer-profile-submit" value="<?php esc_html_e('Update Resume', 'wp-jobsearch') ?>">
    </form>
    <?php
}