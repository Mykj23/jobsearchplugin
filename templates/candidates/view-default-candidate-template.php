<?php
/**
 * Listing search box
 *
 */
global $jobsearch_post_candidate_types, $jobsearch_plugin_options;

$user_id = $user_company = '';
if (is_user_logged_in()) {
    $user_id = get_current_user_id();
    $user_company = get_user_meta($user_id, 'jobsearch_company', true);
}

$default_candidate_no_custom_fields = isset($jobsearch_plugin_options['jobsearch_candidate_no_custom_fields']) ? $jobsearch_plugin_options['jobsearch_candidate_no_custom_fields'] : '';
if (false === ( $candidate_view = jobsearch_get_transient_obj('jobsearch_candidate_view' . $candidate_short_counter) )) {
    $candidate_view = isset($atts['candidate_view']) ? $atts['candidate_view'] : '';
}
$candidates_excerpt_length = isset($atts['candidates_excerpt_length']) ? $atts['candidates_excerpt_length'] : '18';
$jobsearch_split_map_title_limit = '10';

$candidate_no_custom_fields = isset($atts['candidate_no_custom_fields']) ? $atts['candidate_no_custom_fields'] : $default_candidate_no_custom_fields;
if ($candidate_no_custom_fields == '' || !is_numeric($candidate_no_custom_fields)) {
    $candidate_no_custom_fields = 3;
}
$candidate_filters = isset($atts['candidate_filters']) ? $atts['candidate_filters'] : '';
$jobsearch_candidates_title_limit = isset($atts['candidates_title_limit']) ? $atts['candidates_title_limit'] : '5';
// start ads script
$candidate_ads_switch = isset($atts['candidate_ads_switch']) ? $atts['candidate_ads_switch'] : 'no';
if ($candidate_ads_switch == 'yes') {
    $candidate_ads_after_list_series = isset($atts['candidate_ads_after_list_count']) ? $atts['candidate_ads_after_list_count'] : '5';
    if ($candidate_ads_after_list_series != '') {
        $candidate_ads_list_array = explode(",", $candidate_ads_after_list_series);
    }
    $candidate_ads_after_list_array_count = sizeof($candidate_ads_list_array);
    $candidate_ads_after_list_flag = 0;
    $i = 0;
    $array_i = 0;
    $candidate_ads_after_list_array_final = '';
    while ($candidate_ads_after_list_array_count > $array_i) {
        if (isset($candidate_ads_list_array[$array_i]) && $candidate_ads_list_array[$array_i] != '') {
            $candidate_ads_after_list_array[$i] = $candidate_ads_list_array[$array_i];
            $i ++;
        }
        $array_i ++;
    }
    // new count 
    $candidate_ads_after_list_array_count = sizeof($candidate_ads_after_list_array);
}

$candidates_ads_array = array();
if ($candidate_ads_switch == 'yes' && $candidate_ads_after_list_array_count > 0) {
    $list_count = 0;
    for ($i = 0; $i <= $candidate_loop_obj->found_posts; $i ++) {
        if ($list_count == $candidate_ads_after_list_array[$candidate_ads_after_list_flag]) {
            $list_count = 1;
            $candidates_ads_array[] = $i;
            $candidate_ads_after_list_flag ++;
            if ($candidate_ads_after_list_flag >= $candidate_ads_after_list_array_count) {
                $candidate_ads_after_list_flag = $candidate_ads_after_list_array_count - 1;
            }
        } else {
            $list_count ++;
        }
    }
}
$paging_var = 'candidate_page';
$candidate_page = isset($_REQUEST[$paging_var]) && $_REQUEST[$paging_var] != '' ? $_REQUEST[$paging_var] : 1;
$candidate_per_page = isset($atts['candidate_per_page']) ? $atts['candidate_per_page'] : '-1';
$candidate_per_page = isset($_REQUEST['per-page']) ? $_REQUEST['per-page'] : $candidate_per_page;
$counter = 1;
if ($candidate_page >= 2) {
    $counter = (
            ($candidate_page - 1) *
            $candidate_per_page ) +
            1;
}
// end ads script

$sectors_enable_switch = isset($jobsearch_plugin_options['sectors_onoff_switch']) ? $jobsearch_plugin_options['sectors_onoff_switch'] : '';

$columns_class = 'jobsearch-column-12';

$http_request = jobsearch_server_protocol();
?>
<div class="jobsearch-candidate jobsearch-candidate-default" id="jobsearch-candidate-<?php echo absint($candidate_short_counter) ?>">

    <ul class="jobsearch-row">
        <?php
        if ($candidate_loop_obj->have_posts()) {
            $flag_number = 1;

            while ($candidate_loop_obj->have_posts()) : $candidate_loop_obj->the_post();
                global $post, $jobsearch_member_profile;
                $candidate_id = $post;
                $post_thumbnail_id = jobsearch_candidate_get_profile_image($candidate_id);
                $post_thumbnail_image = wp_get_attachment_image_src($post_thumbnail_id, 'thumbnail');
                $post_thumbnail_src = isset($post_thumbnail_image[0]) && esc_url($post_thumbnail_image[0]) != '' ? $post_thumbnail_image[0] : '';
                $post_thumbnail_src = $post_thumbnail_src == '' ? jobsearch_candidate_image_placeholder() : $post_thumbnail_src;
                $jobsearch_candidate_approved = get_post_meta($candidate_id, 'jobsearch_field_candidate_approved', true);
                $get_candidate_location = get_post_meta($candidate_id, 'jobsearch_field_location_address', true);
                $jobsearch_candidate_jobtitle = get_post_meta($candidate_id, 'jobsearch_field_candidate_jobtitle', true);
                $jobsearch_candidate_company_name = get_post_meta($candidate_id, 'jobsearch_field_candidate_company_name', true);
                $jobsearch_candidate_company_url = get_post_meta($candidate_id, 'jobsearch_field_candidate_company_url', true);
                $candidate_company_str = '';
                if ($jobsearch_candidate_jobtitle != '') {
                    $candidate_company_str .= $jobsearch_candidate_jobtitle;
                }
                $sector_str = jobsearch_candidate_get_all_sectors($candidate_id, '', '', '', '<li><i class="jobsearch-icon jobsearch-filter-tool-black-shape"></i>', '</li>');

                $final_color = '';
                $candidate_skills = isset($jobsearch_plugin_options['jobsearch_candidate_skills']) ? $jobsearch_plugin_options['jobsearch_candidate_skills'] : '';
                if ($candidate_skills == 'on') {

                    $low_skills_clr = isset($jobsearch_plugin_options['skill_low_set_color']) && $jobsearch_plugin_options['skill_low_set_color'] != '' ? $jobsearch_plugin_options['skill_low_set_color'] : '';
                    $med_skills_clr = isset($jobsearch_plugin_options['skill_med_set_color']) && $jobsearch_plugin_options['skill_med_set_color'] != '' ? $jobsearch_plugin_options['skill_med_set_color'] : '';
                    $high_skills_clr = isset($jobsearch_plugin_options['skill_high_set_color']) && $jobsearch_plugin_options['skill_high_set_color'] != '' ? $jobsearch_plugin_options['skill_high_set_color'] : '';
                    $comp_skills_clr = isset($jobsearch_plugin_options['skill_ahigh_set_color']) && $jobsearch_plugin_options['skill_ahigh_set_color'] != '' ? $jobsearch_plugin_options['skill_ahigh_set_color'] : '';

                    $overall_candidate_skills = get_post_meta($candidate_id, 'overall_skills_percentage', true);
                    if ($overall_candidate_skills <= 25 && $low_skills_clr != '') {
                        $final_color = 'style="color: ' . $low_skills_clr . ';"';
                    } else if ($overall_candidate_skills > 25 && $overall_candidate_skills <= 50 && $med_skills_clr != '') {
                        $final_color = 'style="color: ' . $med_skills_clr . ';"';
                    } else if ($overall_candidate_skills > 50 && $overall_candidate_skills <= 75 && $high_skills_clr != '') {
                        $final_color = 'style="color: ' . $high_skills_clr . ';"';
                    } else if ($overall_candidate_skills > 75 && $comp_skills_clr != '') {
                        $final_color = 'style="color: ' . $comp_skills_clr . ';"';
                    }
                }
                ?>
                <li class="<?php echo esc_html($columns_class); ?>">
                    <div class="jobsearch-candidate-default-wrap">
                        <?php if ($post_thumbnail_src != '') { ?> 
                            <figure>
                                <a href="<?php the_permalink(); ?>">
                                    <img src="<?php echo esc_url($post_thumbnail_src) ?>" alt="">
                                </a>
                            </figure> 
                        <?php } ?> 
                        <div class="jobsearch-candidate-default-text">
                            <div class="jobsearch-candidate-default-left">
                                <h2>
                                    <a href="<?php echo esc_url(get_permalink($candidate_id)); ?>">
                                        <?php echo esc_html(wp_trim_words(get_the_title($candidate_id), $jobsearch_split_map_title_limit)); ?>
                                    </a>
                                    <i class="jobsearch-icon jobsearch-check-mark" <?php echo ($final_color) ?>></i>
                                </h2>
                                <ul>
                                    <?php if ($candidate_company_str != '') { ?>
                                        <li><?php echo force_balance_tags($candidate_company_str); ?></li>
                                        <?php
                                    }
                                    if (!empty($get_candidate_location)) {
                                        ?>
                                        <li><i class="fa fa-map-marker"></i> <?php echo esc_html($get_candidate_location); ?></li>
                                        <?php
                                    }
                                    if ($sector_str != '' && $sectors_enable_switch == 'on') {
                                        echo force_balance_tags($sector_str);
                                    }
                                    ?>
                                </ul>
                            </div>
                            <?php do_action('jobsearch_add_employer_resume_to_list_btn', array('id' => $candidate_id)); ?>
                        </div>
                    </div>
                </li> 
                <?php
                do_action('jobsearch_random_ad_banners', $atts, $candidate_loop_obj, $counter, 'candidate_listing');
                $counter ++;
                $flag_number ++; // number variable for candidate
            endwhile;
        } else {
            echo '
            <li class="' . esc_html($columns_class) . '">
                <div class="no-candidate-match-error">
                    <strong>' . esc_html__('No Record', 'wp-jobsearch') . '</strong>
                    <span>' . esc_html__('Sorry!', 'wp-jobsearch') . '&nbsp; ' . esc_html__('Does not match rcord with your keyword', 'wp-jobsearch') . ' </span>
                    <span>' . esc_html__('Change your filter keywords to re-submit', 'wp-jobsearch') . '</span>
                    <em>' . esc_html__('OR', 'wp-jobsearch') . '</em>
                    <a href="' . esc_url($page_url) . '">' . esc_html__('Reset Filters', 'wp-jobsearch') . '</a>
                </div>
            </li>';
        }
        ?> 
    </ul>
</div>