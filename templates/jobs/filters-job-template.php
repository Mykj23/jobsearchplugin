<?php
$output = '';
$left_filter_count_switch = 'no';

?>
<div class="jobsearch-column-3 jobsearch-typo-wrap">
    <?php
    $sh_atts = isset($job_arg['atts']) ? $job_arg['atts'] : '';
    if (isset($sh_atts['job_filters_count']) && $sh_atts['job_filters_count'] == 'yes') {
        $left_filter_count_switch = 'yes';
    }
    do_action('jobsearch_jobs_listing_filters_before', array('sh_atts' => $sh_atts));
    
    /*
     * add filter box for job locations filter 
     */
    $output .= apply_filters('jobsearch_job_filter_joblocation_box_html', '', $global_rand_id, $args_count, $left_filter_count_switch, $sh_atts);
    /*
     * add filter box for location fields filter 
     */
    $output .= apply_filters('jobsearch_location_field_filter_box_html', '', 'job', $global_rand_id, $args_count, 'jobsearch_job_content_load');
    /*
     * add filter box for date posted filter 
     */
    $output .= apply_filters('jobsearch_job_filter_date_posted_box_html', '', $global_rand_id, $args_count, $left_filter_count_switch, $sh_atts);
    /*
     * add filter box for job types filter 
     */
    $output .= apply_filters('jobsearch_job_filter_jobtype_box_html', '', $global_rand_id, $args_count, $left_filter_count_switch, $sh_atts);
    /*
     * add filter box for sectors filter 
     */
    $output .= apply_filters('jobsearch_job_filter_sector_box_html', '', $global_rand_id, $args_count, $left_filter_count_switch, $sh_atts);
    /*
     * add filter box for custom fields filter 
     */
    $output .= apply_filters('jobsearch_custom_fields_filter_box_html', '', 'job', $global_rand_id, $args_count, $left_filter_count_switch, 'jobsearch_job_content_load');

    echo force_balance_tags($output);
    ?>
</div>
