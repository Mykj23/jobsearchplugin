<?php
global $jobsearch_plugin_options;
$get_tab = isset($_REQUEST['tab']) ? $_REQUEST['tab'] : '';
$page_id = $user_dashboard_page = isset($jobsearch_plugin_options['user-dashboard-template-page']) ? $jobsearch_plugin_options['user-dashboard-template-page'] : '';
$page_id = $user_dashboard_page = jobsearch__get_post_id($user_dashboard_page, 'page');
$page_url = jobsearch_wpml_lang_page_permalink($page_id, 'page'); //get_permalink($page_id);

$pckg_transaction_links = isset($jobsearch_plugin_options['packages_menu_links']) ? $jobsearch_plugin_options['packages_menu_links'] : '';

$candidate_skills = isset($jobsearch_plugin_options['jobsearch_candidate_skills']) ? $jobsearch_plugin_options['jobsearch_candidate_skills'] : '';

$user_id = get_current_user_id();
$user_obj = get_user_by('ID', $user_id);
$user_def_avatar_url = get_avatar_url($user_id, array('size' => 132));

$user_displayname = isset($user_obj->display_name) ? $user_obj->display_name : '';
$user_displayname = apply_filters('jobsearch_user_display_name', $user_displayname, $user_obj);

$user_is_candidate = jobsearch_user_is_candidate($user_id);
$user_is_employer = jobsearch_user_is_employer($user_id);

if ($user_is_employer) {
    $employer_id = jobsearch_get_user_employer_id($user_id);
    $user_avatar_id = get_post_thumbnail_id($employer_id);
    if ($user_avatar_id > 0) {
        $user_thumbnail_image = wp_get_attachment_image_src($user_avatar_id, 'thumbnail');
        $user_def_avatar_url = isset($user_thumbnail_image[0]) && esc_url($user_thumbnail_image[0]) != '' ? $user_thumbnail_image[0] : '';
    }
    $user_def_avatar_url = $user_def_avatar_url == '' ? jobsearch_employer_image_placeholder() : $user_def_avatar_url;
    $user_type = 'emp';
} else {
    $candidate_id = jobsearch_get_user_candidate_id($user_id);
    $user_avatar_id = get_post_thumbnail_id($candidate_id);
    if ($user_avatar_id > 0) {
        $user_thumbnail_image = wp_get_attachment_image_src($user_avatar_id, 'thumbnail');
        $user_def_avatar_url = isset($user_thumbnail_image[0]) && esc_url($user_thumbnail_image[0]) != '' ? $user_thumbnail_image[0] : '';
    }
    $user_def_avatar_url = $user_def_avatar_url == '' ? jobsearch_candidate_image_placeholder() : $user_def_avatar_url;
    $user_type = 'cand';
}
?>
<aside class="jobsearch-column-3 jobsearch-typo-wrap">
    <div class="jobsearch-typo-wrap">
        <div class="jobsearch-employer-dashboard-nav">
            <figure>
                <?php
                if ($user_is_candidate) {
                    if ($candidate_skills == 'on') {
                        ?>
                        <style>
                            #circle {
                                width: 150px;
                                height: 150px;
                                position: relative;
                            }
                            #circle img {
                                border-radius: 100%;
                                position: absolute;
                                left: 9px;
                                top: 9px;
                            }
                        </style>
                        <?php
                        wp_enqueue_script('jobsearch-circle-progressbar');
                    }
                    ?>
                    <a id="com-img-holder" href="<?php echo ($page_url) ?>" class="employer-dashboard-thumb"><?php if ($candidate_skills == 'on') { ?><div id="circle"><?php } ?><img src="<?php echo ($user_def_avatar_url) ?>" alt="" style="max-width: 132px;"><?php if ($candidate_skills == 'on') { ?></div><?php } ?></a>
                    <?php
                } else {
                    ?>
                    <a id="com-img-holder" href="<?php echo ($page_url) ?>" class="employer-dashboard-thumb"><img src="<?php echo ($user_def_avatar_url) ?>" alt="" style="max-width: 132px;"></a>
                    <?php
                }
                
                if ($user_is_candidate) {
                    $uplod_txt = esc_html__('Upload Photo', 'wp-jobsearch');
                } else if ($user_is_employer) {
                    $uplod_txt = esc_html__('Upload Company Logo', 'wp-jobsearch');
                }
                ?>

                <figcaption>
                    <span class="fileUpLoader"></span>
                    <div class="jobsearch-fileUpload">
                        <span><i class="jobsearch-icon jobsearch-add"></i> <?php echo ($uplod_txt) ?></span>
                        <input type="file" id="user_avatar" name="user_avatar" class="jobsearch-upload">
                    </div>
                    <h2><a href="<?php echo ($page_url) ?>"><?php echo ($user_displayname) ?></a></h2>
                    <?php
                    if ($user_is_candidate) {

                        ob_start();
                        $job_title = get_post_meta($candidate_id, 'jobsearch_field_candidate_jobtitle', true);
                        ?>
                        <span class="jobsearch-dashboard-subtitle"><?php echo ($job_title) ?></span>
                        <?php
                        $job_title_html = ob_get_clean();
                        $job_title_html = apply_filters('jobsearch_candidate_dash_side_job_title_html', $job_title_html, $candidate_id);
                        echo ($job_title_html);
                        if ($candidate_skills == 'on') {
                            $overall_candidate_skills = get_post_meta($candidate_id, 'overall_skills_percentage', true);
                            ?>
                            <div class="required-skills-detail">
                                <?php
                                $all_skill_msgs = jobsearch_candidate_skill_percent_count($user_id, 'msgs');
                                if (!empty($all_skill_msgs) && $overall_candidate_skills < 100) {
                                    if (isset($all_skill_msgs[0])) {
                                        ?>
                                        <span class="skills-perc"><?php echo ($all_skill_msgs[0]) ?></span>
                                        <?php
                                    }

                                    if (count($all_skill_msgs) > 1) {
                                        ?>
                                        <a id="skill-detail-popup-btn" href="javascript:void(0);" class="get-skill-detail-btn"><?php esc_html_e('Complete Required Skills', 'wp-jobsearch') ?></a>
                                        <?php
                                        $popup_args = array(
                                            'p_all_skill_msgs' => $all_skill_msgs,
                                            'p_overall_skills' => $overall_candidate_skills,
                                        );
                                        add_action('wp_footer', function () use ($popup_args) {

                                            global $jobsearch_plugin_options;
                                            extract(shortcode_atts(array(
                                                'p_all_skill_msgs' => '',
                                                'p_overall_skills' => '',
                                                            ), $popup_args));

                                            $candidate_min_skill = isset($jobsearch_plugin_options['jobsearch-candidate-skills-percentage']) && $jobsearch_plugin_options['jobsearch-candidate-skills-percentage'] > 0 ? $jobsearch_plugin_options['jobsearch-candidate-skills-percentage'] : 0;
                                            $p_overall_skills = $p_overall_skills > 0 ? $p_overall_skills : 0;

                                            $low_skills_clr = isset($jobsearch_plugin_options['skill_low_set_color']) && $jobsearch_plugin_options['skill_low_set_color'] != '' ? $jobsearch_plugin_options['skill_low_set_color'] : '#13b5ea';
                                            $med_skills_clr = isset($jobsearch_plugin_options['skill_med_set_color']) && $jobsearch_plugin_options['skill_med_set_color'] != '' ? $jobsearch_plugin_options['skill_med_set_color'] : '#13b5ea';
                                            $high_skills_clr = isset($jobsearch_plugin_options['skill_high_set_color']) && $jobsearch_plugin_options['skill_high_set_color'] != '' ? $jobsearch_plugin_options['skill_high_set_color'] : '#13b5ea';
                                            $comp_skills_clr = isset($jobsearch_plugin_options['skill_ahigh_set_color']) && $jobsearch_plugin_options['skill_ahigh_set_color'] != '' ? $jobsearch_plugin_options['skill_ahigh_set_color'] : '#13b5ea';

                                            $final_color = '#13b5ea';
                                            if ($p_overall_skills <= 25) {
                                                $final_color = $low_skills_clr;
                                            } else if ($p_overall_skills > 25 && $p_overall_skills <= 50) {
                                                $final_color = $med_skills_clr;
                                            } else if ($p_overall_skills > 50 && $p_overall_skills <= 75) {
                                                $final_color = $high_skills_clr;
                                            } else if ($p_overall_skills > 75) {
                                                $final_color = $comp_skills_clr;
                                            }
                                            ?>
                                            <div class="jobsearch-modal fade" id="JobSearchModalSkillsDetail">
                                                <div class="modal-inner-area">&nbsp;</div>
                                                <div class="modal-content-area">
                                                    <div class="modal-box-area">
                                                        <span class="modal-close"><i class="fa fa-times"></i></span>
                                                        <div class="jobsearch-skills-set-popup">
                                                            <div class="complet-title">
                                                                <h5><?php esc_html_e('Profile Completion', 'wp-jobsearch') ?></h5>
                                                            </div>
                                                            <div class="profile-completion-con">
                                                                <div class="complet-percent">
                                                                    <span class="percent-num" style="color: <?php echo ($final_color) ?>;"><?php echo ($p_overall_skills) ?>%</span>
                                                                    <div class="percent-bar">
                                                                        <span style="width: <?php echo ($p_overall_skills) ?>%; background-color: <?php echo ($final_color) ?>;"></span>
                                                                    </div>
                                                                </div>
                                                                <div class="minimum-percent">
                                                                    <span><?php esc_html_e('Minimum Required', 'wp-jobsearch') ?></span>
                                                                    <small><?php echo ($candidate_min_skill) ?>%</small>
                                                                </div>
                                                            </div>
                                                            <div class="profile-improve-con">
                                                                <div class="improve-title">
                                                                    <h5><?php esc_html_e('Improve your profile', 'wp-jobsearch') ?></h5>
                                                                </div>
                                                                <ul>
                                                                    <?php
                                                                    foreach ($p_all_skill_msgs as $all_skill_msg) {
                                                                        ?>
                                                                        <li><?php echo ($all_skill_msg) ?></li>
                                                                        <?php
                                                                    }
                                                                    ?>
                                                                </ul>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                            <?php
                                        }, 11, 1);
                                    }
                                }
                                ?>
                            </div>
                            <?php
                        }
                    }
                    ?>
                </figcaption>
            </figure>
            <ul>
                <?php
                if ($user_is_candidate) {
                    ?>

                    <li<?php echo ($get_tab == '' ? ' class="active"' : '') ?>>
                        <a href="<?php echo ($page_url) ?>">
                            <i class="jobsearch-icon jobsearch-group"></i>
                            <?php esc_html_e('Dashboard', 'wp-jobsearch') ?>
                        </a>
                    </li>
                    <li<?php echo ($get_tab == 'dashboard-settings' ? ' class="active"' : '') ?>>
                        <a href="<?php echo add_query_arg(array('tab' => 'dashboard-settings'), $page_url) ?>">
                            <i class="jobsearch-icon jobsearch-user"></i>
                            <?php esc_html_e('My Profile', 'wp-jobsearch') ?>
                        </a>
                    </li>
                    <li<?php echo ($get_tab == 'my-resume' ? ' class="active"' : '') ?>>
                        <a href="<?php echo add_query_arg(array('tab' => 'my-resume'), $page_url) ?>">
                            <i class="jobsearch-icon jobsearch-resume"></i>
                            <?php esc_html_e('My Resume', 'wp-jobsearch') ?>
                        </a>
                    </li>
                    <li<?php echo ($get_tab == 'applied-jobs' ? ' class="active"' : '') ?>>
                        <a href="<?php echo add_query_arg(array('tab' => 'applied-jobs'), $page_url) ?>">
                            <i class="jobsearch-icon jobsearch-briefcase-1"></i>
                            <?php esc_html_e('Applied Jobs', 'wp-jobsearch') ?>
                        </a>
                    </li>
                    <li<?php echo ($get_tab == 'cv-manager' ? ' class="active"' : '') ?>>
                        <a href="<?php echo add_query_arg(array('tab' => 'cv-manager'), $page_url) ?>">
                            <i class="jobsearch-icon jobsearch-id-card"></i>
                            <?php esc_html_e('CV Manager', 'wp-jobsearch') ?>
                        </a>
                    </li>
                    <li<?php echo ($get_tab == 'favourite-jobs' ? ' class="active"' : '') ?>>
                        <a href="<?php echo add_query_arg(array('tab' => 'favourite-jobs'), $page_url) ?>">
                            <i class="jobsearch-icon jobsearch-heart"></i>
                            <?php esc_html_e('Favourite Jobs', 'wp-jobsearch') ?>
                        </a>
                    </li>
                    <?php
                    if ($pckg_transaction_links == 'on') {
                        ?>
                        <li<?php echo ($get_tab == 'user-packages' ? ' class="active"' : '') ?>>
                            <a href="<?php echo add_query_arg(array('tab' => 'user-packages'), $page_url) ?>">
                                <i class="jobsearch-icon jobsearch-credit-card-1"></i>
                                <?php esc_html_e('Packages', 'wp-jobsearch') ?>
                            </a>
                        </li>
                        <li<?php echo ($get_tab == 'user-transactions' ? ' class="active"' : '') ?>>
                            <a href="<?php echo add_query_arg(array('tab' => 'user-transactions'), $page_url) ?>">
                                <i class="jobsearch-icon jobsearch-salary"></i>
                                <?php esc_html_e('Transactions', 'wp-jobsearch') ?>
                            </a>
                        </li>
                        <?php
                    }
                    ?>
                    <?php echo apply_filters('jobsearch_dashboard_menu_items_ext', '', $get_tab, $page_url) ?>
                    <li<?php echo ($get_tab == 'change-password' ? ' class="active"' : '') ?>>
                        <a href="<?php echo add_query_arg(array('tab' => 'change-password'), $page_url) ?>">
                            <i class="jobsearch-icon jobsearch-multimedia"></i>
                            <?php esc_html_e('Change Password', 'wp-jobsearch') ?>
                        </a>
                    </li>
                    <?php
                }
                if ($user_is_employer) {
                    ?>
                    <li<?php echo ($get_tab == '' ? ' class="active"' : '') ?>>
                        <a href="<?php echo ($page_url) ?>">
                            <i class="jobsearch-icon jobsearch-group"></i>
                            <?php esc_html_e('Dashboard', 'wp-jobsearch') ?>
                        </a>
                    </li>
                    <li<?php echo ($get_tab == 'dashboard-settings' ? ' class="active"' : '') ?>>
                        <a href="<?php echo add_query_arg(array('tab' => 'dashboard-settings'), $page_url) ?>">
                            <i class="jobsearch-icon jobsearch-user"></i>
                            <?php esc_html_e('Company Profile', 'wp-jobsearch') ?>
                        </a>
                    </li>
                    <li<?php echo ($get_tab == 'user-job' ? ' class="active"' : '') ?>>
                        <a href="<?php echo add_query_arg(array('tab' => 'user-job'), $page_url) ?>">
                            <i class="jobsearch-icon jobsearch-plus"></i>
                            <?php esc_html_e('Post a New Job', 'wp-jobsearch') ?>
                        </a>
                    </li>
                    <li<?php echo ($get_tab == 'manage-jobs' ? ' class="active"' : '') ?>>
                        <a href="<?php echo add_query_arg(array('tab' => 'manage-jobs'), $page_url) ?>">
                            <i class="jobsearch-icon jobsearch-briefcase-1"></i>
                            <?php esc_html_e('Manage Jobs', 'wp-jobsearch') ?>
                        </a>
                    </li>
                    <li<?php echo ($get_tab == 'user-resumes' ? ' class="active"' : '') ?>>
                        <a href="<?php echo add_query_arg(array('tab' => 'user-resumes'), $page_url) ?>">
                            <i class="jobsearch-icon jobsearch-heart"></i>
                            <?php esc_html_e('Shortlisted Resumes', 'wp-jobsearch') ?>
                        </a>
                    </li>
                    <?php
                    if ($pckg_transaction_links == 'on') {
                        ?>
                        <li<?php echo ($get_tab == 'user-packages' ? ' class="active"' : '') ?>>
                            <a href="<?php echo add_query_arg(array('tab' => 'user-packages'), $page_url) ?>">
                                <i class="jobsearch-icon jobsearch-credit-card-1"></i>
                                <?php esc_html_e('Packages', 'wp-jobsearch') ?>
                            </a>
                        </li>
                        <li<?php echo ($get_tab == 'user-transactions' ? ' class="active"' : '') ?>>
                            <a href="<?php echo add_query_arg(array('tab' => 'user-transactions'), $page_url) ?>">
                                <i class="jobsearch-icon jobsearch-salary"></i>
                                <?php esc_html_e('Transactions', 'wp-jobsearch') ?>
                            </a>
                        </li>
                        <?php
                    }
                    ?>
                    <?php echo apply_filters('jobsearch_dashboard_menu_items_ext', '', $get_tab, $page_url) ?>
                    <li<?php echo ($get_tab == 'change-password' ? ' class="active"' : '') ?>>
                        <a href="<?php echo add_query_arg(array('tab' => 'change-password'), $page_url) ?>">
                            <i class="jobsearch-icon jobsearch-multimedia"></i>
                            <?php esc_html_e('Change Password', 'wp-jobsearch') ?>
                        </a>
                    </li>
                    <?php
                }
                ?>
                <li>
                    <a href="<?php echo wp_logout_url(home_url('/')); ?>">
                        <i class="jobsearch-icon jobsearch-logout"></i>
                        <?php esc_html_e('Logout', 'wp-jobsearch') ?>
                    </a>
                </li>
                <li class="profile-del-btnlink">
                    <a class="jobsearch-userdel-profilebtn" href="javascript:void(0);"><i class="fa fa-trash-o"></i><?php esc_html_e('Delete Profile', 'wp-jobsearch') ?></a>
                </li>
            </ul>
            <?php
            $popup_args = array('p_user_type' => $user_type);
            add_action('wp_footer', function () use ($popup_args) {

                extract(shortcode_atts(array(
                    'p_user_type' => '',
                                ), $popup_args));
                ?>
                <div class="jobsearch-modal fade" id="JobSearchModalUserProfileDel">
                    <div class="modal-inner-area">&nbsp;</div>
                    <div class="modal-content-area">
                        <div class="modal-box-area">
                            <span class="modal-close"><i class="fa fa-times"></i></span>
                            <div class="jobsearch-user-profiledel-pop">
                                <p class="conf-msg"><?php esc_html_e('Are you sure! You want to delete your profile.', 'wp-jobsearch') ?></p>
                                <p class="undone-msg"><?php esc_html_e('This can\'t be undone!', 'wp-jobsearch') ?></p>
                                <div class="profile-del-con">
                                    <div class="pass-user-ara">
                                        <p><?php esc_html_e('Please enter your login Password to confirm', 'wp-jobsearch') ?>:</p>
                                        <label><?php esc_html_e('Password', 'wp-jobsearch') ?></label>
                                        <input id="d_user_pass" type="password" placeholder="Password">
                                        <i class="jobsearch-icon jobsearch-multimedia"></i>
                                    </div>
                                    <div class="del-action-btns">
                                        <a class="jobsearch-userdel-profile" href="javascript:void(0);" data-type="<?php echo ($p_user_type) ?>"><?php esc_html_e('Delete Profile', 'wp-jobsearch') ?></a>
                                        <a class="jobsearch-userdel-cancel modal-close" href="javascript:void(0);"><?php esc_html_e('Cancel', 'wp-jobsearch') ?></a>
                                    </div>
                                    <span class="loader-con"></span>
                                    <span class="msge-con"></span>
                                </div>
                                <?php
                                jobsearch_terms_and_con_link_txt();
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            }, 11, 1);
            if ($user_is_candidate && $candidate_skills == 'on') {
                //
                $overall_candidate_skills = get_post_meta($candidate_id, 'overall_skills_percentage', true);
                $overall_skills_perc = 0;
                if ($overall_candidate_skills > 0) {
                    $overall_skills_perc = $overall_candidate_skills / 100;
                }
                //
                $low_skills_clr = isset($jobsearch_plugin_options['skill_low_set_color']) && $jobsearch_plugin_options['skill_low_set_color'] != '' ? $jobsearch_plugin_options['skill_low_set_color'] : '#13b5ea';
                $med_skills_clr = isset($jobsearch_plugin_options['skill_med_set_color']) && $jobsearch_plugin_options['skill_med_set_color'] != '' ? $jobsearch_plugin_options['skill_med_set_color'] : '#13b5ea';
                $high_skills_clr = isset($jobsearch_plugin_options['skill_high_set_color']) && $jobsearch_plugin_options['skill_high_set_color'] != '' ? $jobsearch_plugin_options['skill_high_set_color'] : '#13b5ea';
                $comp_skills_clr = isset($jobsearch_plugin_options['skill_ahigh_set_color']) && $jobsearch_plugin_options['skill_ahigh_set_color'] != '' ? $jobsearch_plugin_options['skill_ahigh_set_color'] : '#13b5ea';

                $final_color = '#13b5ea';
                if ($overall_candidate_skills <= 25) {
                    $final_color = $low_skills_clr;
                } else if ($overall_candidate_skills > 25 && $overall_candidate_skills <= 50) {
                    $final_color = $med_skills_clr;
                } else if ($overall_candidate_skills > 50 && $overall_candidate_skills <= 75) {
                    $final_color = $high_skills_clr;
                } else if ($overall_candidate_skills > 75) {
                    $final_color = $comp_skills_clr;
                }
                ?>
                <script>
                    jQuery(document).ready(function () {
                        var bar = new ProgressBar.Circle(circle, {
                            color: '<?php echo ($final_color) ?>',
                            trailColor: '#f7f7f7',
                            trailWidth: 4,
                            duration: 1400,
                            strokeWidth: 4,
                            from: {color: '<?php echo ($final_color) ?>', a: 0},
                            to: {color: '<?php echo ($final_color) ?>', a: 1},
                            // Set default step function for all animate calls
                            step: function (state, circle) {
                                circle.path.setAttribute('stroke', state.color);
                                var value = Math.round(circle.value() * 100);
                                if (value === 0) {
                                    circle.setText('');
                                } else {
                                    circle.setText(value + '%');
                                }
                            }
                        });

                        bar.animate(<?php echo ($overall_skills_perc) ?>);  // Number from 0.0 to 1.0
                        bar.text.style.left = '0';
                        bar.text.style.right = '80%';
                        bar.text.style.top = '5%';
                        bar.text.style.bottom = '100%';
                        bar.text.style.color = '<?php echo ($final_color) ?>';
                        bar.text.style.fontSize = '16px';
                        bar.text.style.fontWeight = 'bold';
                    });
                </script>
                <?php
            }
            ?>
        </div>

    </div>					
</aside>