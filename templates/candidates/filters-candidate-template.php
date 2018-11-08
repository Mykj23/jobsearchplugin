<?php
$output = '';
$left_filter_count_switch = 'no';
?>
<div class="jobsearch-column-3 jobsearch-typo-wrap">
    <?php
    $sh_atts = isset($candidate_arg['atts']) ? $candidate_arg['atts'] : '';
    if (isset($sh_atts['candidate_filters_count']) && $sh_atts['candidate_filters_count'] == 'yes') {
        $left_filter_count_switch = 'yes';
    }
    /*
     * add filter box for location fields filter 
     */
    $output .= apply_filters('jobsearch_location_field_filter_box_html', '', 'candidate', $global_rand_id, $args_count, 'jobsearch_candidate_content_load');
    /*
     * add filter box for date posted filter 
     */
    $output .= apply_filters('jobsearch_candidate_filter_date_posted_box_html', '', $global_rand_id, $args_count, $left_filter_count_switch, $sh_atts);
    /*
     * add filter box for candidate types filter 
     */
    $output .= apply_filters('jobsearch_candidate_filter_candidatetype_box_html', '', $global_rand_id, $args_count, $left_filter_count_switch, $sh_atts);
    /*
     * add filter box for sectors filter 
     */
    $output .= apply_filters('jobsearch_candidate_filter_sector_box_html', '', $global_rand_id, $args_count, $left_filter_count_switch, $sh_atts);
    /*
     * add filter box for custom fields filter 
     */
    $output .= apply_filters('jobsearch_custom_fields_filter_box_html', '', 'candidate', $global_rand_id, $args_count, $left_filter_count_switch, 'jobsearch_candidate_content_load');
    /*
     * add filter box for location fields filter 
     */
   // $output .= apply_filters('jobsearch_location_field_filter_box_html', '', 'candidate', $global_rand_id, $args_count, 'jobsearch_candidate_content_load');

    echo force_balance_tags($output);
    ?>
</div>
