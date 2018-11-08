<?php
$output = '';
//echo '<pre>'; print_r($args_count); echo '</pre>';
$left_filter_count_switch = 'no';
?>
<div class="jobsearch-column-3 jobsearch-typo-wrap">
    <?php
    $sh_atts = isset($employer_arg['atts']) ? $employer_arg['atts'] : '';
    if (isset($sh_atts['employer_filters_count']) && $sh_atts['employer_filters_count'] == 'yes') {
        $left_filter_count_switch = 'yes';
    }
    /*
     * add filter box for job locations filter 
     */
    $output .= apply_filters('jobsearch_employer_filter_location_box_html', '', $global_rand_id, $args_count, $left_filter_count_switch, $sh_atts);
    /*
     * add filter box for location fields filter 
     */
    $output .= apply_filters('jobsearch_location_field_filter_box_html', '', 'employer', $global_rand_id, $args_count, 'jobsearch_employer_content_load');
    /*
     * add filter box for date posted filter 
     */
    $output .= apply_filters('jobsearch_employer_filter_date_posted_box_html', '', $global_rand_id, $args_count, $left_filter_count_switch, $sh_atts);
    /*
     * add filter box for employer types filter 
     */
    $output .= apply_filters('jobsearch_employer_filter_employertype_box_html', '', $global_rand_id, $args_count, $left_filter_count_switch, $sh_atts);
    /*
     * add filter box for sectors filter 
     */
    $output .= apply_filters('jobsearch_employer_filter_sector_box_html', '', $global_rand_id, $args_count, $left_filter_count_switch, $sh_atts);
    /*
     * add filter box for custom fields filter 
     */
    $output .= apply_filters('jobsearch_custom_fields_filter_box_html', '', 'employer', $global_rand_id, $args_count, $left_filter_count_switch, 'jobsearch_employer_content_load');
    /*
     * add filter box for team fields filter 
     */
    $output .= apply_filters('jobsearch_team_size_filter_box_html', '', $global_rand_id, $args_count, $left_filter_count_switch, $sh_atts);

    echo force_balance_tags($output);
    ?>
</div>
