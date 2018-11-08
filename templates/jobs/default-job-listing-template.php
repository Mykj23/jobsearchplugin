<?php
/*
 * Job Default page template
 */

// execute short code
wp_enqueue_style('jobsearch-datetimepicker-style');
wp_enqueue_script('jobsearch-datetimepicker-script');
wp_enqueue_script('jquery-ui');
wp_enqueue_script('jobsearch-job-functions-script');

$output = do_shortcode('[jobsearch_job_shortcode
            job_cat = ""
            job_view = "view-default"
            job_excerpt = "20"
            job_order = "DESC"
            job_orderby = "date"
            job_sort_by = "yes"
            job_pagination = "yes"
            job_per_page = "10"            
            job_filters = "yes"
            job_type = "" ]');
get_header();
global $jobsearch_plugin_options;
$plugin_default_view = isset($jobsearch_plugin_options['jobsearch-default-page-view']) ? $jobsearch_plugin_options['jobsearch-default-page-view'] : 'full';
$plugin_default_view_with_str = '';
if ($plugin_default_view == 'boxed') {

    $plugin_default_view_with_str = isset($jobsearch_plugin_options['jobsearch-boxed-view-width']) && $jobsearch_plugin_options['jobsearch-boxed-view-width'] != '' ? $jobsearch_plugin_options['jobsearch-boxed-view-width'] : '1140px';
    if ($plugin_default_view_with_str != '') {
        $plugin_default_view_with_str = ' style="width:' . $plugin_default_view_with_str . '"';
    }
}
?>
<div class="jobsearch-plugin-default-container" <?php echo force_balance_tags($plugin_default_view_with_str); ?>>
    <!--// Main Section \\-->
    <div class="jobsearch-plugin-section"> 
        <?php echo force_balance_tags($output); ?>
    </div>
    <!--// Main Section \\-->
</div>
<?php
get_footer();
