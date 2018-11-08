<?php
global $jobsearch_plugin_options, $diff_form_errs;
$get_tab = isset($_REQUEST['tab']) ? $_REQUEST['tab'] : '';
$page_id = $user_dashboard_page = isset($jobsearch_plugin_options['user-dashboard-template-page']) ? $jobsearch_plugin_options['user-dashboard-template-page'] : '';
$page_id = $user_dashboard_page = jobsearch__get_post_id($user_dashboard_page, 'page');
$page_url = jobsearch_wpml_lang_page_permalink($page_id, 'page'); //get_permalink($page_id);

$current_user = wp_get_current_user();
$user_id = get_current_user_id();
$user_obj = get_user_by('ID', $user_id);

$employer_id = jobsearch_get_user_employer_id($user_id);

$user_displayname = $user_obj->display_name;
$user_displayname = apply_filters('jobsearch_user_display_name', $user_displayname, $user_obj);
$user_bio = $user_obj->description;
$user_website = $user_obj->user_url;
$user_email = $user_obj->user_email;

//
$user_dob_dd = get_post_meta($employer_id, 'jobsearch_field_user_dob_dd', true);
$user_dob_mm = get_post_meta($employer_id, 'jobsearch_field_user_dob_mm', true);
$user_dob_yy = get_post_meta($employer_id, 'jobsearch_field_user_dob_yy', true);

$user_phone = get_post_meta($employer_id, 'jobsearch_field_user_phone', true);
//

$emp_post_obj = get_post($employer_id);
$employer_content = isset($emp_post_obj->post_content) ? $emp_post_obj->post_content : '';
$employer_content = apply_filters('the_content', $employer_content);

$user_facebook_url = get_post_meta($employer_id, 'jobsearch_field_user_facebook_url', true);
$user_twitter_url = get_post_meta($employer_id, 'jobsearch_field_user_twitter_url', true);
$user_google_plus_url = get_post_meta($employer_id, 'jobsearch_field_user_google_plus_url', true);
$user_youtube_url = get_post_meta($employer_id, 'jobsearch_field_user_youtube_url', true);
$user_dribbble_url = get_post_meta($employer_id, 'jobsearch_field_user_dribbble_url', true);
$user_linkedin_url = get_post_meta($employer_id, 'jobsearch_field_user_linkedin_url', true);
//
//
$sectors = wp_get_post_terms($employer_id, 'sector');
$employer_sector = isset($sectors[0]->term_id) ? $sectors[0]->term_id : '';

$user_def_avatar_url = get_avatar_url($user_id, array('size' => 128));

$user_avatar_id = get_user_meta($user_id, 'jobsearch_user_avatar_id', true);

if ($user_avatar_id > 0) {
    $user_thumbnail_image = wp_get_attachment_image_src($user_avatar_id, 'thumbnail');
    $user_def_avatar_url = isset($user_thumbnail_image[0]) && esc_url($user_thumbnail_image[0]) != '' ? $user_thumbnail_image[0] : '';
}
?>
<div class="jobsearch-typo-wrap">
    <form class="jobsearch-employer-dasboard" method="post" action="<?php echo add_query_arg(array('tab' => 'dashboard-settings'), $page_url) ?>" enctype="multipart/form-data">
        <div class="jobsearch-employer-box-section">
            <div class="jobsearch-profile-title"><h2><?php esc_html_e('Basic Information', 'wp-jobsearch') ?></h2></div>
            <?php
            if (isset($_POST['user_settings_form']) && $_POST['user_settings_form'] == '1') {
                if (empty($diff_form_errs)) {
                    ?>
                    <div class="jobsearch-alert jobsearch-success-alert">
                        <p><?php echo wp_kses(__('<strong>Success!</strong> All changes updated.', 'wp-jobsearch'), array('strong' => array())) ?></p>
                    </div>
                    <?php
                } else if (isset($diff_form_errs['user_not_allow_mod']) && $diff_form_errs['user_not_allow_mod'] == true) {
                    ?>
                    <div class="jobsearch-alert jobsearch-error-alert">
                        <p><?php echo wp_kses(__('<strong>Error!</strong> You are not allowed to modify settings.', 'wp-jobsearch'), array('strong' => array())) ?></p>
                    </div>
                    <?php
                }
            }

            //
            $user_cover_img_url = '';
            if ($employer_id != '') {
                if (class_exists('JobSearchMultiPostThumbnails')) {
                    $employer_cover_image_src = JobSearchMultiPostThumbnails::get_post_thumbnail_url('employer', 'cover-image', $employer_id);
                    if ($employer_cover_image_src != '') {
                        $user_cover_img_url = $employer_cover_image_src;
                    }
                }
            }
            ?>
            <div class="jobsearch-employer-cvr-img">

                <figure>
                    <div class="img-cont-sec">
                        <a href="javascript:void(0);" class="employer-remove-coverimg" style="display: <?php echo ($user_cover_img_url == '' ? 'none' : 'block') ?>;"><i class="fa fa-times"></i> <?php esc_html_e('Delete Cover', 'wp-jobsearch') ?></a>    
                        <a id="com-cvrimg-holder" class="employer-dashboard-cvr">
                            <img src="<?php echo ($user_cover_img_url) ?>" alt="" style="max-width: 100%;">
                        </a>
                    </div>
                    <figcaption>
                        <span class="file-loader"></span>
                        <div class="jobsearch-fileUpload">
                            <span><i class="jobsearch-icon jobsearch-add"></i> <?php esc_html_e('Upload Jobs Cover Photo', 'wp-jobsearch') ?></span>
                            <input type="file" id="user_cvr_photo" name="user_cvr_photo" class="jobsearch-upload">
                        </div>
                    </figcaption>
                </figure>
            </div>
            <ul class="jobsearch-row jobsearch-employer-profile-form">
                <li class="jobsearch-column-6">
                    <label><?php esc_html_e('Company Name *', 'wp-jobsearch') ?></label>
                    <input type="text" name="display_name" value="<?php echo ($user_displayname) ?>">
                </li>
                <li class="jobsearch-column-6">
                    <label><?php esc_html_e('Email', 'wp-jobsearch') ?></label>
                    <input value="<?php echo ($user_email) ?>" type="text" readonly="readonly">
                </li>

                <li class="jobsearch-column-6">
                    <label><?php esc_html_e('Phone', 'wp-jobsearch') ?></label>
                    <input value="<?php echo ($user_phone) ?>" onkeyup="javascript:jobsearch_is_valid_phone_number(this)" type="text" name="user_phone">
                </li>
                <li class="jobsearch-column-6">
                    <label><?php esc_html_e('Website', 'wp-jobsearch') ?></label>
                    <input value="<?php echo ($user_website) ?>" type="text" name="user_website">
                </li>
                <?php
                $sectors_enable_switch = isset($jobsearch_plugin_options['sectors_onoff_switch']) ? $jobsearch_plugin_options['sectors_onoff_switch'] : '';
                if ($sectors_enable_switch == 'on') {
                    ?>
                    <li class="jobsearch-column-6">
                        <label><?php esc_html_e('Sector', 'wp-jobsearch') ?></label>
                        <div class="jobsearch-profile-select">
                            <?php
                            $sector_args = array(
                                'show_option_all' => esc_html__('Select Sector', 'wp-jobsearch'),
                                'show_option_none' => '',
                                'option_none_value' => '',
                                'orderby' => 'title',
                                'order' => 'ASC',
                                'show_count' => 0,
                                'hide_empty' => 0,
                                'echo' => 1,
                                'selected' => $employer_sector,
                                'hierarchical' => 1,
                                'id' => 'user-sector',
                                'class' => 'postform selectize-select',
                                'name' => 'user_sector',
                                'depth' => 0,
                                'taxonomy' => 'sector',
                                'hide_if_empty' => false,
                                'value_field' => 'term_id',
                            );
                            wp_dropdown_categories($sector_args);
                            ?>
                        </div>
                    </li>
                    <?php
                }
                $sdate_format = jobsearch_get_wp_date_simple_format();
                ?>
                <li class="jobsearch-column-6">
                    <label><?php esc_html_e('Founded Date', 'wp-jobsearch') ?></label>
                    <div class="jobsearch-three-column-row">
                        <?php
                        ob_start();
                        ?>
                        <div class="jobsearch-profile-select jobsearch-three-column">
                            <select name="user_dob_dd" class="selectize-select" placeholder="<?php esc_html_e('Day', 'wp-jobsearch') ?>">
                                <?php
                                for ($dd = 1; $dd <= 31; $dd++) {
                                    $db_val = $user_dob_dd != '' ? $user_dob_dd : date('d');
                                    ?>
                                    <option <?php echo ($db_val == $dd ? 'selected="selected"' : '') ?> value="<?php echo ($dd) ?>"><?php echo ($dd) ?></option>
                                    <?php
                                }
                                ?>
                            </select>
                        </div>
                        <?php
                        $dob_dd_html = ob_get_clean();
                        ob_start();
                        ?>
                        <div class="jobsearch-profile-select jobsearch-three-column">
                            <select name="user_dob_mm" class="selectize-select" placeholder="<?php esc_html_e('Month', 'wp-jobsearch') ?>">
                                <?php
                                for ($mm = 1; $mm <= 12; $mm++) {
                                    $db_val = $user_dob_mm != '' ? $user_dob_mm : date('m');
                                    ?>
                                    <option <?php echo ($db_val == $mm ? 'selected="selected"' : '') ?> value="<?php echo ($mm) ?>"><?php echo ($mm) ?></option>
                                    <?php
                                }
                                ?>
                            </select>
                        </div>
                        <?php
                        $dob_mm_html = ob_get_clean();
                        ob_start();
                        ?>
                        <div class="jobsearch-profile-select jobsearch-three-column">
                            <select name="user_dob_yy" class="selectize-select" placeholder="<?php esc_html_e('Year', 'wp-jobsearch') ?>">
                                <?php
                                for ($yy = 1900; $yy <= date('Y'); $yy++) {
                                    $db_val = $user_dob_yy != '' ? $user_dob_yy : date('Y');
                                    ?>
                                    <option <?php echo ($db_val == $yy ? 'selected="selected"' : '') ?> value="<?php echo ($yy) ?>"><?php echo ($yy) ?></option>
                                    <?php
                                }
                                ?>
                            </select>
                        </div>
                        <?php
                        $dob_yy_html = ob_get_clean();
                        //
                        if ($sdate_format == 'm-d-y') {
                            echo ($dob_mm_html);
                            echo ($dob_dd_html);
                            echo ($dob_yy_html);
                        } else if ($sdate_format == 'y-m-d') {
                            echo ($dob_yy_html);
                            echo ($dob_mm_html);
                            echo ($dob_dd_html);
                        } else {
                            echo ($dob_dd_html);
                            echo ($dob_mm_html);
                            echo ($dob_yy_html);
                        }
                        ?>
                    </div>
                </li>
                <li class="jobsearch-column-12">
                    <label><?php esc_html_e('About Company', 'wp-jobsearch') ?></label>
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

                    wp_editor($employer_content, 'user_bio', $settings);
                    ?>
                </li>
            </ul>
        </div>
        <?php do_action('jobsearch_dashboard_custom_fields_load', $employer_id, 'employer'); ?>

        <div class="jobsearch-employer-box-section">
            <div class="jobsearch-profile-title"><h2><?php esc_html_e('Company Social', 'wp-jobsearch') ?></h2></div>
            <ul class="jobsearch-row jobsearch-employer-profile-form">
                <li class="jobsearch-column-6">
                    <label><?php esc_html_e('Facebook', 'wp-jobsearch') ?></label>
                    <input value="<?php echo ($user_facebook_url) ?>" name="jobsearch_field_user_facebook_url" type="text">
                </li>
                <li class="jobsearch-column-6">
                    <label><?php esc_html_e('Twitter', 'wp-jobsearch') ?></label>
                    <input value="<?php echo ($user_twitter_url) ?>" name="jobsearch_field_user_twitter_url" type="text">
                </li>
                <li class="jobsearch-column-6">
                    <label><?php esc_html_e('Google Plus', 'wp-jobsearch') ?></label>
                    <input value="<?php echo ($user_google_plus_url) ?>" name="jobsearch_field_user_google_plus_url" type="text">
                </li>
                <li class="jobsearch-column-6">
                    <label><?php esc_html_e('Linkedin', 'wp-jobsearch') ?></label>
                    <input value="<?php echo ($user_linkedin_url) ?>" name="jobsearch_field_user_linkedin_url" type="text">
                </li>
                <li class="jobsearch-column-6">
                    <label><?php esc_html_e('Dribbble', 'wp-jobsearch') ?></label>
                    <input value="<?php echo ($user_dribbble_url) ?>" name="jobsearch_field_user_dribbble_url" type="text">
                </li>
            </ul>
        </div>

        <?php do_action('jobsearch_dashboard_location_map', $employer_id); ?>

        <?php
        $_allow_team_add = isset($jobsearch_plugin_options['allow_team_members']) ? $jobsearch_plugin_options['allow_team_members'] : '';
        if ($_allow_team_add == 'on') {
            ?>
            <div class="jobsearch-employer-box-section">
                <div class="jobsearch-candidate-resume-wrap">    
                    <div class="jobsearch-candidate-title"> 
                        <h2>
                            <i class="jobsearch-icon jobsearch-group"></i> <?php esc_html_e('Team Members', 'wp-jobsearch') ?>
                            <a href="javascript:void(0)" class="jobsearch-resume-addbtn jobsearch-portfolio-add-btn"><span class="fa fa-plus"></span> <?php esc_html_e('Add Team Member', 'wp-jobsearch') ?> </a>
                        </h2> 
                    </div>
                    <div class="jobsearch-add-popup jobsearch-add-resume-item-popup">
                        <span class="close-popup-item"><i class="fa fa-times"></i></span>
                        <ul class="jobsearch-row jobsearch-employer-profile-form">
                            <li class="jobsearch-column-6">
                                <label><?php esc_html_e('Member Title *', 'wp-jobsearch') ?></label>
                                <input id="team_title" class="jobsearch-req-field" type="text">
                            </li>
                            <li class="jobsearch-column-6">
                                <label><?php esc_html_e('Designation *', 'wp-jobsearch') ?></label>
                                <input id="team_designation" class="jobsearch-req-field" type="text">
                            </li>
                            <li class="jobsearch-column-6">
                                <label><?php esc_html_e('Experience *', 'wp-jobsearch') ?></label>
                                <input id="team_experience" class="jobsearch-req-field" type="text">
                            </li>
                            <li class="jobsearch-column-6">
                                <label><?php esc_html_e('Image *', 'wp-jobsearch') ?></label>
                                <div class="upload-img-holder-sec">
                                    <span class="file-loader"></span>
                                    <img src="" alt="">
                                    <input name="team_image" type="file" style="display: none;">
                                    <input type="hidden" id="team_image_input" class="jobsearch-req-field">
                                    <a href="javascript:void(0)" class="upload-port-img-btn"><i class="jobsearch-icon jobsearch-add"></i> <?php esc_html_e('Upload Photo', 'wp-jobsearch') ?></a>
                                </div>
                            </li>
                            <li class="jobsearch-column-6">
                                <label><?php esc_html_e('Facebook URL', 'wp-jobsearch') ?></label>
                                <input id="team_facebook" type="text">
                            </li>
                            <li class="jobsearch-column-6">
                                <label><?php esc_html_e('Google+ URL', 'wp-jobsearch') ?></label>
                                <input id="team_google" type="text">
                            </li>
                            <li class="jobsearch-column-6">
                                <label><?php esc_html_e('Twitter URL', 'wp-jobsearch') ?></label>
                                <input id="team_twitter" type="text">
                            </li>
                            <li class="jobsearch-column-6">
                                <label><?php esc_html_e('Linkedin URL', 'wp-jobsearch') ?></label>
                                <input id="team_linkedin" type="text">
                            </li>
                            <li class="jobsearch-column-12">
                                <label><?php esc_html_e('Description', 'wp-jobsearch') ?></label>
                                <textarea id="team_description"></textarea>
                            </li>
                            <li class="jobsearch-column-12">
                                <input type="submit" id="add-team-member-btn" value="<?php esc_html_e('Add Team Member', 'wp-jobsearch') ?>">
                                <span class="portfolio-loding-msg edu-loding-msg"></span>
                            </li>
                        </ul>
                    </div>

                    <div id="jobsearch-team-members-con" class="jobsearch-company-gallery">
                        <ul class="jobsearch-row jobsearch-team-list-con">
                            <?php
                            $exfield_list = get_post_meta($employer_id, 'jobsearch_field_team_title', true);
                            $exfield_list_val = get_post_meta($employer_id, 'jobsearch_field_team_description', true);
                            $team_designationfield_list = get_post_meta($employer_id, 'jobsearch_field_team_designation', true);
                            $team_experiencefield_list = get_post_meta($employer_id, 'jobsearch_field_team_experience', true);
                            $team_imagefield_list = get_post_meta($employer_id, 'jobsearch_field_team_image', true);
                            $team_facebookfield_list = get_post_meta($employer_id, 'jobsearch_field_team_facebook', true);
                            $team_googlefield_list = get_post_meta($employer_id, 'jobsearch_field_team_google', true);
                            $team_twitterfield_list = get_post_meta($employer_id, 'jobsearch_field_team_twitter', true);
                            $team_linkedinfield_list = get_post_meta($employer_id, 'jobsearch_field_team_linkedin', true);
                            if (is_array($exfield_list) && sizeof($exfield_list) > 0) {

                                $exfield_counter = 0;
                                foreach ($exfield_list as $exfield) {
                                    $rand_num = rand(1000000, 99999999);

                                    $exfield_val = isset($exfield_list_val[$exfield_counter]) ? $exfield_list_val[$exfield_counter] : '';
                                    $team_designationfield_val = isset($team_designationfield_list[$exfield_counter]) ? $team_designationfield_list[$exfield_counter] : '';
                                    $team_experiencefield_val = isset($team_experiencefield_list[$exfield_counter]) ? $team_experiencefield_list[$exfield_counter] : '';
                                    $team_imagefield_val = isset($team_imagefield_list[$exfield_counter]) ? $team_imagefield_list[$exfield_counter] : '';
                                    $team_facebookfield_val = isset($team_facebookfield_list[$exfield_counter]) ? $team_facebookfield_list[$exfield_counter] : '';
                                    $team_googlefield_val = isset($team_googlefield_list[$exfield_counter]) ? $team_googlefield_list[$exfield_counter] : '';
                                    $team_twitterfield_val = isset($team_twitterfield_list[$exfield_counter]) ? $team_twitterfield_list[$exfield_counter] : '';
                                    $team_linkedinfield_val = isset($team_linkedinfield_list[$exfield_counter]) ? $team_linkedinfield_list[$exfield_counter] : '';
                                    ?>
                                    <li class="jobsearch-column-3">
                                        <figure>
                                            <a class="portfolio-img-holder"><span style="background-image: url('<?php echo ($team_imagefield_val) ?>');"></span></a>
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
                                                <li class="jobsearch-column-6">
                                                    <label><?php esc_html_e('Member Title *', 'wp-jobsearch') ?></label>
                                                    <input name="jobsearch_field_team_title[]" type="text" value="<?php echo ($exfield) ?>">
                                                </li>
                                                <li class="jobsearch-column-6">
                                                    <label><?php esc_html_e('Designation *', 'wp-jobsearch') ?></label>
                                                    <input name="jobsearch_field_team_designation[]" type="text" value="<?php echo ($team_designationfield_val) ?>">
                                                </li>
                                                <li class="jobsearch-column-6">
                                                    <label><?php esc_html_e('Experience *', 'wp-jobsearch') ?></label>
                                                    <input name="jobsearch_field_team_experience[]" type="text" value="<?php echo ($team_experiencefield_val) ?>">
                                                </li>
                                                <li class="jobsearch-column-6">
                                                    <label><?php esc_html_e('Image *', 'wp-jobsearch') ?></label>
                                                    <div class="upload-img-holder-sec">
                                                        <span class="file-loader"></span>
                                                        <img src="<?php echo ($team_imagefield_val) ?>" alt="">
                                                        <br>
                                                        <input name="team_image" type="file" style="display: none;">
                                                        <input type="hidden" class="img-upload-save-field" name="jobsearch_field_team_image[]" value="<?php echo ($team_imagefield_val) ?>">
                                                        <a href="javascript:void(0)" class="upload-port-img-btn"><i class="jobsearch-icon jobsearch-add"></i> <?php esc_html_e('Upload Photo', 'wp-jobsearch') ?></a>
                                                    </div>
                                                </li>
                                                <li class="jobsearch-column-6">
                                                    <label><?php esc_html_e('Facebook URL', 'wp-jobsearch') ?></label>
                                                    <input name="jobsearch_field_team_facebook[]" type="text" value="<?php echo ($team_facebookfield_val) ?>">
                                                </li>
                                                <li class="jobsearch-column-6">
                                                    <label><?php esc_html_e('Google+ URL', 'wp-jobsearch') ?></label>
                                                    <input name="jobsearch_field_team_google[]" type="text" value="<?php echo ($team_googlefield_val) ?>">
                                                </li>
                                                <li class="jobsearch-column-6">
                                                    <label><?php esc_html_e('TwitterURL', 'wp-jobsearch') ?></label>
                                                    <input name="jobsearch_field_team_twitter[]" type="text" value="<?php echo ($team_twitterfield_val) ?>">
                                                </li>
                                                <li class="jobsearch-column-6">
                                                    <label><?php esc_html_e('Linkedin URL', 'wp-jobsearch') ?></label>
                                                    <input name="jobsearch_field_team_linkedin[]" type="text" value="<?php echo ($team_linkedinfield_val) ?>">
                                                </li>
                                                <li class="jobsearch-column-12">
                                                    <label><?php esc_html_e('Description', 'wp-jobsearch') ?></label>
                                                    <textarea name="jobsearch_field_team_description[]"><?php echo ($exfield_val) ?></textarea>
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
            <?php
        }

        $max_gal_imgs_allow = isset($jobsearch_plugin_options['max_gal_imgs_allow']) && $jobsearch_plugin_options['max_gal_imgs_allow'] > 0 ? $jobsearch_plugin_options['max_gal_imgs_allow'] : 5;
        $number_of_gal_imgs = $max_gal_imgs_allow;
        $company_gal_imgs = get_post_meta($employer_id, 'jobsearch_field_company_gallery_imgs', true);
        $company_gal_videos = get_post_meta($employer_id, 'jobsearch_field_company_gallery_videos', true);
        ?>
        <div class="jobsearch-employer-box-section">
            <div class="jobsearch-profile-title"><h2><?php esc_html_e('Company Photos/Videos', 'wp-jobsearch') ?></h2></div>
            <div class="jobsearch-company-photo jobsearch-company-gal-photo" style="display: <?php echo (!empty($company_gal_imgs) ? 'none' : 'block') ?>;">
                <img src="<?php echo jobsearch_plugin_get_url('images/employer-profile-nonphoto.png') ?>" alt="">
                <h2><?php esc_html_e('Upload profile Photos here.', 'wp-jobsearch') ?></h2>
                <small><?php printf(esc_html__('You can upload up to %s images under your profile.', 'wp-jobsearch'), $number_of_gal_imgs) ?></small>
                <div class="jobsearch-fileUpload">
                    <span><i class="jobsearch-icon jobsearch-upload"></i> <?php esc_html_e('Upload Images', 'wp-jobsearch') ?></span>
                    <input id="company_gallery_imgs" name="user_profile_gallery_imgs[]" type="file" class="upload jobsearch-upload" multiple="multiple" onchange="jobsearch_gallry_read_file_url(event)" />
                </div>
            </div>
            <div class="jobsearch-gallery-main">
                <div id="gallery-imgs-holder" class="gallery-imgs-holder jobsearch-company-gallery">
                    <?php
                    if (!empty($company_gal_imgs)) {
                        ?>
                        <ul class="jobsearch-row gal-all-imgs">
                            <?php
                            $gal_counter = 0;
                            foreach ($company_gal_imgs as $company_gal_img) {
                                $rand_id = rand(100000, 9999999);
                                if ($company_gal_img != '' && absint($company_gal_img) <= 0) {
                                    $company_gal_img = jobsearch_get_attachment_id_from_url($company_gal_img);
                                }
                                $gal_thumbnail_image = wp_get_attachment_image_src($company_gal_img, 'medium');
                                $gal_thumb_image_src = isset($gal_thumbnail_image[0]) && esc_url($gal_thumbnail_image[0]) != '' ? $gal_thumbnail_image[0] : '';

                                $gal_video_url = isset($company_gal_videos[$gal_counter]) && esc_url($company_gal_videos[$gal_counter]) != '' ? $company_gal_videos[$gal_counter] : '';
                                ?>
                                <li class="jobsearch-column-3 gal-item">
                                    <script>
                                        jQuery(document).on('click', '.el-update-btn-<?php echo ($rand_id) ?>', function () {
                                            jobsearch_modal_popup_open('JobSearchModalEmployerGallery<?php echo ($rand_id) ?>');
                                        });
                                        jQuery(document).on('click', '#gallery-update-<?php echo ($rand_id) ?>', function () {
                                            var _this = jQuery(this);
                                            var this_id = _this.attr('data-id');
                                            var galery_video_val = jQuery('#gallery-video-to-get-' + this_id).val();
                                            jQuery('#gallery-video-to-put-' + this_id).val(galery_video_val);
                                            _this.parents('.jobsearch-modal').find('.modal-close').trigger('click');
                                        });
                                    </script>
                                    <figure>
                                        <a><img src="<?php echo esc_url($gal_thumb_image_src) ?>" alt=""></a>
                                        <figcaption>
                                            <div class="jobsearch-company-links">
                                                <a href="javascript:void(0);" class="fa fa-arrows el-drag"></a>
                                                <a href="javascript:void(0);" class="fa fa-pencil el-update-btn-<?php echo ($rand_id) ?>"></a>
                                                <?php
                                                $popup_args = array('p_gal_counter' => $gal_counter, 'p_rand_id' => $rand_id, 'p_video_url' => $gal_video_url);
                                                add_action('wp_footer', function () use ($popup_args) {

                                                    extract(shortcode_atts(array(
                                                        'p_gal_counter' => '',
                                                        'p_rand_id' => '',
                                                        'p_video_url' => '',
                                                                    ), $popup_args));
                                                    ?>
                                                    <div class="jobsearch-modal fade" id="JobSearchModalEmployerGallery<?php echo ($p_rand_id) ?>">
                                                        <div class="modal-inner-area">&nbsp;</div>
                                                        <div class="modal-content-area">
                                                            <div class="modal-box-area">
                                                                <span class="modal-close"><i class="fa fa-times"></i></span>
                                                                <div class="jobsearch-send-message-form">
                                                                    <div class="jobsearch-user-form">
                                                                        <ul class="email-fields-list">
                                                                            <li>
                                                                                <label>
                                                                                    <?php echo esc_html__('Video URL', 'wp-jobsearch'); ?>:
                                                                                </label>
                                                                                <div class="input-field">
                                                                                    <input type="text" id="gallery-video-to-get-<?php echo ($p_rand_id) ?>" value="<?php echo ($p_video_url) ?>" />
                                                                                    <em><?php esc_html_e('Add video url of youtube, vimeo.', 'wp-jobsearch') ?></em>
                                                                                </div>
                                                                            </li>
                                                                            <li>
                                                                                <div class="input-field-submit">
                                                                                    <a href="javascript:void(0);" id="gallery-update-<?php echo ($p_rand_id) ?>" data-id="<?php echo ($p_rand_id) ?>" class="careerfy-classic-btn jobsearch-bgcolor"><?php echo esc_html__('Update', 'wp-jobsearch'); ?></a>
                                                                                </div>
                                                                            </li>
                                                                        </ul>
                                                                    </div>   
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <?php
                                                }, 11, 1);
                                                ?>
                                                <a href="javascript:void(0);" class="jobsearch-icon jobsearch-rubbish el-remove"></a>
                                                <input type="hidden" name="company_gallery_imgs[]" value="<?php echo absint($company_gal_img) ?>">
                                                <input type="hidden" id="gallery-video-to-put-<?php echo ($rand_id) ?>" name="jobsearch_field_company_gallery_videos[]" value="<?php echo ($gal_video_url) ?>">
                                            </div>
                                        </figcaption>
                                    </figure>
                                </li>
                                <?php
                                $gal_counter ++;
                            }
                            ?>
                        </ul>
                        <?php
                    }
                    ?>
                </div>
            </div>
            <a id="upload-more-gal-imgs" href="javascript:void(0)" class="jobsearch-add-more-imgs jobsearch-employer-profile-submit" style="display: <?php echo (!empty($company_gal_imgs) ? 'block' : 'none') ?>;"> <?php esc_html_e('Upload More Images', 'wp-jobsearch') ?> </a>
        </div>

        <input type="hidden" name="user_settings_form" value="1">
        <input type="submit" class="jobsearch-employer-profile-submit" value="<?php esc_html_e('Save Settings', 'wp-jobsearch') ?>">
        <?php
        ob_start();
        do_action('jobsearch_translate_profile_with_wpml_btn', $employer_id, 'employer', 'dashboard-settings');
        $btns_html = ob_get_clean();
        echo apply_filters('jobsearch_translate_eprofile_with_wpml_btn_html', $btns_html);
        //
        jobsearch_terms_and_con_link_txt();
        ?>
    </form>
</div>
