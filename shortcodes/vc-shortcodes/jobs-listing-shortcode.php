<?php
/**
 * File Type: Jobs Shortcode Frontend
 */
if (!class_exists('Jobsearch_Shortcode_Jobs_Frontend')) {

    class Jobsearch_Shortcode_Jobs_Frontend {

        /**
         * Start construct Functions
         */
        public function __construct() {
            add_shortcode('jobsearch_job_shortcode', array($this, 'jobsearch_jobs_shortcode_callback'));
            add_action('wp_ajax_jobsearch_jobs_content', array($this, 'jobsearch_jobs_content'));
            add_action('wp_ajax_nopriv_jobsearch_jobs_content', array($this, 'jobsearch_jobs_content'));
            add_action('wp_ajax_jobsearch_job_view_switch', array($this, 'jobsearch_job_view_switch'), 11, 1);
            add_action('wp_ajax_nopriv_jobsearch_job_view_switch', array($this, 'jobsearch_job_view_switch'), 11, 1);
            add_action('jobsearch_job_pagination', array($this, 'jobsearch_job_pagination_callback'), 11, 1);
            add_action('jobsearch_draw_search_element', array($this, 'jobsearch_draw_search_element_callback'), 11, 1);
            add_filter('jobsearch_search_keyword', array($this, 'jobsearch_search_keyword_callback'), 11, 2);
        }

        /*
         * Shortcode View on Frontend
         */

        public function jobsearch_jobs_shortcode_callback($atts, $content = "") {
            extract(shortcode_atts(array(
                'job_cat' => '',
                'job_view' => 'view-default',
                'featured_only' => '',
                'job_sort_by' => '',
                'job_top_search' => '',
                'job_excerpt' => '20',
                'job_order' => 'DESC',
                'job_orderby' => 'date',
                'job_pagination' => 'yes',
                'job_per_page' => '3',
                'job_type' => '',
                'job_filters' => 'yes',
                            ), $atts));

            if (empty($atts) && !is_array($atts)) {
                $atts = array();
            }
            if (!isset($atts['job_cat'])) {
                $atts['job_cat'] = '';
            }
            if (!isset($atts['job_view'])) {
                $atts['job_view'] = 'view-default';
            }
            if (!isset($atts['featured_only'])) {
                $atts['featured_only'] = '';
            }
            if (!isset($atts['job_sort_by'])) {
                $atts['job_sort_by'] = '';
            }
            if (!isset($atts['job_top_search'])) {
                $atts['job_top_search'] = '';
            }
            if (!isset($atts['job_excerpt'])) {
                $atts['job_excerpt'] = '20';
            }
            if (!isset($atts['job_order'])) {
                $atts['job_order'] = 'DESC';
            }
            if (!isset($atts['job_orderby'])) {
                $atts['job_orderby'] = 'date';
            }
            if (!isset($atts['job_pagination'])) {
                $atts['job_pagination'] = 'yes';
            }
            if (!isset($atts['job_per_page'])) {
                $atts['job_per_page'] = '10';
            }
            if (!isset($atts['job_type'])) {
                $atts['job_type'] = '';
            }
            if (!isset($atts['job_filters'])) {
                $atts['job_filters'] = 'yes';
            }
            if (!isset($atts['job_filters_loc'])) {
                $atts['job_filters_loc'] = 'yes';
            }
            if (!isset($atts['job_filters_date'])) {
                $atts['job_filters_date'] = 'yes';
            }
            if (!isset($atts['job_filters_type'])) {
                $atts['job_filters_type'] = 'yes';
            }
            if (!isset($atts['job_filters_sector'])) {
                $atts['job_filters_sector'] = 'yes';
            }

            ob_start();
            wp_enqueue_style('datetimepicker-style');
            wp_enqueue_script('datetimepicker-script');
            wp_enqueue_script('jquery-ui');
            wp_enqueue_script('jobsearch-job-functions-script');
            do_action('jobsearch_notes_frontend_modal_popup');
            $job_short_counter = isset($atts['job_counter']) && $atts['job_counter'] != '' ? ( $atts['job_counter'] ) : rand(1000, 9999); // for shortcode counter
            if (false === ( $job_view = jobsearch_get_transient_obj('jobsearch_job_view' . $job_short_counter) )) {
                $job_view = isset($atts['job_view']) ? $atts['job_view'] : '';
            }
            jobsearch_set_transient_obj('jobsearch_job_view' . $job_short_counter, $job_view);
            $job_map_counter = rand(10000000, 99999999);
            $element_job_footer = isset($atts['job_footer']) ? $atts['job_footer'] : '';
            $element_job_map_position = isset($atts['job_map_position']) ? $atts['job_map_position'] : '';
            $map_change_class = '';
            if ($job_view == 'map') {
                if ($element_job_footer == 'yes') {
                    echo '<script>';
                    echo 'jQuery(document).ready(function () {'
                    . 'jQuery("footer#footer").hide();'
                    . '});';
                    echo '</script>';
                }
            }
            wp_reset_query();
            do_action('job_checks_enquire_lists_submit');
            do_action('jobsearch_job_compare_sidebar');
            do_action('jobsearch_job_enquiries_sidebar');

            $page_url = get_permalink(get_the_ID());
            ?>
            <div class="wp-dp-job-content" id="wp-dp-job-content-<?php echo esc_html($job_short_counter); ?>">
                <div class="dev-map-class-changer<?php echo ($map_change_class); ?>">
                    <div id="Job-content-<?php echo esc_html($job_short_counter); ?>">
                        <?php
                        $job_arg = array(
                            'job_short_counter' => $job_short_counter,
                            'atts' => $atts,
                            'content' => $content,
                            'job_map_counter' => $job_map_counter,
                            'page_url' => $page_url,
                        );
                        $this->jobsearch_jobs_content($job_arg);
                        ?>
                    </div>
                </div>
            </div> 
            <?php
            $html = ob_get_clean();
            return $html;
        }

        public function jobsearch_jobs_content($job_arg = '') {

            global $wpdb, $jobsearch_form_fields, $jobsearch_search_fields, $jobsearch_plugin_options, $sitepress;
            if (function_exists('icl_object_id')) {
                $trans_able_options = $sitepress->get_setting('custom_posts_sync_option', array());
            }
            wp_enqueue_script('jobsearch-job-functions-script');
            wp_enqueue_style('datetimepicker-style');
            wp_enqueue_script('datetimepicker-script');
            wp_enqueue_script('jquery-ui');

            do_action('jobsearch_notes_frontend_modal_popup');
            // getting arg array from ajax

            $all_post_ids = array();
            if (isset($_REQUEST['job_arg']) && $_REQUEST['job_arg']) {
                $job_arg = stripslashes(html_entity_decode($_REQUEST['job_arg']));
                $job_arg = json_decode($job_arg);
                $job_arg = $this->toArray($job_arg);
            }
            if (isset($job_arg) && $job_arg != '' && !empty($job_arg)) {
                extract($job_arg);
            }
            $default_date_time_formate = 'd-m-Y H:i:s';
            // getting if user set it with his choice
            if (false === ( $job_view = jobsearch_get_transient_obj('jobsearch_job_view' . $job_short_counter) )) {
                $job_view = isset($atts['job_view']) ? $atts['job_view'] : '';
            }

            $element_job_sort_by = isset($atts['job_sort_by']) ? $atts['job_sort_by'] : 'no';
            $element_job_topmap = '';
            $element_job_map_position = isset($atts['job_map_position']) ? $atts['job_map_position'] : 'full';
            $element_job_layout_switcher = isset($atts['job_layout_switcher']) ? $atts['job_layout_switcher'] : 'no';
            $element_job_layout_switcher_view = isset($atts['job_layout_switcher_view']) ? $atts['job_layout_switcher_view'] : 'grid';
            $element_job_map_height = isset($atts['job_map_height']) ? $atts['job_map_height'] : 400;
            $element_job_footer = isset($atts['job_footer']) ? $atts['job_footer'] : 'no';
            $element_job_search_keyword = isset($atts['job_search_keyword']) ? $atts['job_search_keyword'] : 'no';

            $featured_only = isset($atts['featured_only']) ? $atts['featured_only'] : 'no';
            $element_job_recent_switch = isset($atts['job_recent_switch']) ? $atts['job_recent_switch'] : 'no';
            $job_job_urgent = isset($atts['job_urgent']) ? $atts['job_urgent'] : 'all';
            $job_type = isset($atts['job_type']) ? $atts['job_type'] : 'all';
            $job_filters_sidebar = isset($atts['job_filters']) ? $atts['job_filters'] : '';
            $job_right_sidebar_content = isset($content) ? $content : '';
            $jobsearch_job_sidebar = isset($atts['jobsearch_job_sidebar']) ? $atts['jobsearch_job_sidebar'] : '';
            $jobsearch_map_position = isset($atts['jobsearch_map_position']) && $atts['jobsearch_map_position'] != '' ? ( $atts['jobsearch_map_position'] ) : 'right';

            $job_desc = isset($atts['job_desc']) ? $atts['job_desc'] : '';
            $job_cus_fields = isset($atts['job_cus_fields']) ? $atts['job_cus_fields'] : 'yes';

            $job_per_page = '-1';
            $pagination = 'no';
            $job_per_page = isset($atts['job_per_page']) ? $atts['job_per_page'] : '-1';
            $job_per_page = isset($_REQUEST['per-page']) ? $_REQUEST['per-page'] : $job_per_page;
            $pagination = isset($atts['job_pagination']) ? $atts['job_pagination'] : 'no';
            $filter_arr = array();
            $qryvar_sort_by_column = '';
            $element_filter_arr = array();
            $content_columns = 'jobsearch-column-12 jobsearch-typo-wrap'; // if filteration not true
            $paging_var = 'job_page';
            // Element fields in filter

            if (isset($_REQUEST['job_type']) && $_REQUEST['job_type'] != '') {
                $job_type = $_REQUEST['job_type'];
            }

            $skill_in = '';
            if (isset($_REQUEST['skill_in']) && $_REQUEST['skill_in'] != '') {
                $skill_in = $_REQUEST['skill_in'];
            }

            // posted date check
            $element_filter_arr[] = array(
                'key' => 'jobsearch_field_job_publish_date',
                'value' => strtotime(current_time($default_date_time_formate)),
                'compare' => '<=',
            );

            $element_filter_arr[] = array(
                'key' => 'jobsearch_field_job_expiry_date',
                'value' => strtotime(current_time($default_date_time_formate)),
                'compare' => '>=',
            );

            $element_filter_arr[] = array(
                'key' => 'jobsearch_field_job_status',
                'value' => 'approved',
                'compare' => '=',
            );

            $element_filter_arr[] = array(
                'key' => 'jobsearch_job_employer_status',
                'value' => 'approved',
                'compare' => '=',
            );

            $job_feat_jobs_top = isset($atts['job_feat_jobs_top']) ? $atts['job_feat_jobs_top'] : '';

            if ($featured_only == 'yes') {
                if ($job_feat_jobs_top != 'yes') {
                    $element_filter_arr[] = array(
                        'key' => 'jobsearch_field_job_featured',
                        'value' => 'on',
                        'compare' => '=',
                    );
                }
            } else if (isset($_REQUEST['sort-by']) && $_REQUEST['sort-by'] == 'featured') {
                $element_filter_arr[] = array(
                    'relation' => 'OR',
                    array(
                        'key' => 'jobsearch_field_job_featured',
                        'compare' => 'EXISTS',
                    ),
                    array(
                        'key' => 'jobsearch_field_job_featured',
                        'compare' => 'NOT EXISTS',
                        'value' => '',
                    ),
                );
            }
            if ($job_feat_jobs_top == 'yes') {
//                $element_filter_arr[] = array(
//                    'key' => 'jobsearch_field_job_featured',
//                    'value' => 'on',
//                    'compare' => '!=',
//                );
            }

            if (function_exists('jobsearch_visibility_query_args')) {
                $element_filter_arr = jobsearch_visibility_query_args($element_filter_arr);
            }

            if (!isset($_REQUEST[$paging_var])) {
                $_REQUEST[$paging_var] = '';
            }

            // Get all arguments from getting flters.
            $left_filter_arr = $this->get_filter_arg($job_short_counter);

            $post_ids = array();
            if (!empty($left_filter_arr)) {
                // apply all filters and get ids
                $post_ids = $this->get_job_id_by_filter($left_filter_arr);
            }

            //
            $post_ids = $this->job_location_filter($post_ids);
            //

            $loc_polygon_path = '';
            if (isset($_REQUEST['loc_polygon_path']) && $_REQUEST['loc_polygon_path'] != '') {
                $loc_polygon_path = $_REQUEST['loc_polygon_path'];
            }

            if (!empty($post_ids)) {
                $all_post_ids = $post_ids;
            }

            $search_title = isset($_REQUEST['search_title']) ? $_REQUEST['search_title'] : '';


            /*
             * used for relevance sort by filter
             */

            if (isset($_REQUEST['loc_radius']) && $_REQUEST['loc_radius'] > 0 && isset($_REQUEST['location'])) {

                $jobsearch_loc_address = $_REQUEST['location'];
                $radius = $_REQUEST['loc_radius'];

                $location_response = jobsearch_address_to_cords($jobsearch_loc_address);
                $lat = isset($location_response['lat']) ? $location_response['lat'] : '';
                $lng = isset($location_response['lng']) ? $location_response['lng'] : '';

                if ($lat != '' && $lng != '') {
                    $radiusCheck = new RadiusCheck($lat, $lng, $radius);
                    $minLat = $radiusCheck->MinLatitude();
                    $maxLat = $radiusCheck->MaxLatitude();
                    $minLong = $radiusCheck->MinLongitude();
                    $maxLong = $radiusCheck->MaxLongitude();
                    $jobsearch_compare_type = 'CHAR';
                    if ($radius > 0) {
                        //$jobsearch_compare_type = 'DECIMAL(10,6)';
                    }
                    $element_filter_arr[] = array(
                        'relation' => 'OR',
                        array(
                            'key' => 'jobsearch_field_location_lat',
                            'value' => array($minLat, $maxLat),
                            'compare' => 'BETWEEN',
                            'type' => $jobsearch_compare_type
                        ),
                        array(
                            'key' => 'jobsearch_field_location_lng',
                            'value' => array($minLong, $maxLong),
                            'compare' => 'BETWEEN',
                            'type' => $jobsearch_compare_type
                        ),
                    );
                }
            }

            $element_filter_arr = apply_filters('jobsearch_jobs_listing_query_meta_fields', $element_filter_arr);

            /*
             * End used for relevance sort by filter
             */

            $args_count = array(
                'posts_per_page' => "1",
                'post_type' => 'job',
                'post_status' => 'publish',
                'suppress_filters' => false,
                'fields' => 'ids', // only load ids
                'meta_query' => array(
                    $element_filter_arr,
                ),
            );

            if (isset($_REQUEST['sector_cat']) && $_REQUEST['sector_cat'] != '') {
                $args_count['tax_query'][] = array(
                    'taxonomy' => 'sector',
                    'field' => 'slug',
                    'terms' => urldecode($_REQUEST['sector_cat'])
                );
            } else if (isset($atts['job_cat']) && $atts['job_cat'] != '') {
                $args_count['tax_query'][] = array(
                    'taxonomy' => 'sector',
                    'field' => 'slug',
                    'terms' => $atts['job_cat']
                );
            }

            if ($job_type != '' && $job_type != 'all') {
                $args_count['tax_query'][] = array(
                    'taxonomy' => 'jobtype',
                    'field' => 'slug',
                    'terms' => $job_type,
                );
            }
            if ($skill_in != '' && $skill_in != 'all') {
                $args_count['tax_query'][] = array(
                    'taxonomy' => 'skill',
                    'field' => 'slug',
                    'terms' => $skill_in,
                );
            }
            $job_sort_by = 'recent'; // default value
            $job_sort_order = 'desc'; // default value

            if (isset($_REQUEST['sort-by']) && $_REQUEST['sort-by'] != '') {
                $job_sort_by = $_REQUEST['sort-by'];
            }
            $meta_key = 'jobsearch_field_job_publish_date';
            $qryvar_job_sort_type = 'DESC';
            $qryvar_sort_by_column = 'meta_value_num';
            if ($job_sort_by == 'recent') {
                $meta_key = 'jobsearch_field_job_publish_date';
                $qryvar_job_sort_type = 'DESC';
                $qryvar_sort_by_column = 'meta_value_num';
            } elseif ($job_sort_by == 'alphabetical') {
                $qryvar_job_sort_type = 'ASC';
                $qryvar_sort_by_column = 'post_title';
            } elseif ($job_sort_by == 'most_viewed') {
                $qryvar_job_sort_type = 'DESC';
                $qryvar_sort_by_column = 'meta_value_num';
                $meta_key = 'jobsearch_job_views_count';
            } elseif ($job_sort_by == 'featured') {
                $qryvar_job_sort_type = 'DESC';
                $qryvar_sort_by_column = 'meta_value';
                $meta_key = 'jobsearch_field_job_featured';
            }

            $args = array(
                'posts_per_page' => $job_per_page,
                'paged' => $_REQUEST[$paging_var],
                'post_type' => 'job',
                'post_status' => 'publish',
                'order' => $qryvar_job_sort_type,
                'orderby' => $qryvar_sort_by_column,
                'meta_key' => $meta_key,
                'fields' => 'ids', // only load ids
                'meta_query' => array(
                    $element_filter_arr,
                ),
            );

            if (isset($_REQUEST['sector_cat']) && $_REQUEST['sector_cat'] != '') {
                $args['tax_query'][] = array(
                    'taxonomy' => 'sector',
                    'field' => 'slug',
                    'terms' => urldecode($_REQUEST['sector_cat'])
                );
            } else if (isset($atts['job_cat']) && $atts['job_cat'] != '') {
                $args['tax_query'][] = array(
                    'taxonomy' => 'sector',
                    'field' => 'slug',
                    'terms' => $atts['job_cat']
                );
            }

            if ($job_type != '' && $job_type != 'all') {
                $args['tax_query'][] = array(
                    'taxonomy' => 'jobtype',
                    'field' => 'slug',
                    'terms' => $job_type,
                );
            }
            if ($skill_in != '' && $skill_in != 'all') {
                $args['tax_query'][] = array(
                    'taxonomy' => 'skill',
                    'field' => 'slug',
                    'terms' => $skill_in,
                );
            }

            if (isset($_REQUEST['loc_polygon_path']) && $_REQUEST['loc_polygon_path'] != '') {
                $loc_polygon_path = $_REQUEST['loc_polygon_path'];
                $all_post_ids = $this->job_polygon_filter($loc_polygon_path, $all_post_ids, $element_filter_arr);
            }

            if (isset($search_title) && $search_title != '') {

                $query_2_params = array(
                    'posts_per_page' => '-1',
                    'fields' => 'ids',
                    'post_type' => 'job',
                    's' => $search_title
                );
                if (!empty($all_post_ids)) {
                    $query_2_params['post__in'] = $all_post_ids;
                }

                $query_2 = get_posts($query_2_params);
                //$all_post_ids = $query_2;
                //$all_post_ids = empty($all_post_ids) ? array(0) : $all_post_ids;
            }

            // recent job query end

            if (!empty($all_post_ids)) {
                $args_count['post__in'] = $all_post_ids;
                $args['post__in'] = $all_post_ids;
            }

            $args = apply_filters('jobsearch_job_listing_query_args_array', $args, $atts);

            //echo '<pre>';
            //var_dump($args);
            //echo '</pre>';
            add_filter('posts_where', 'jobsearch_search_query_results_filter', 10, 2);
            $job_loop_obj = jobsearch_get_cached_obj('job_result_cached_loop_obj1', $args, 12, false, 'wp_query');
            remove_filter('posts_where', 'jobsearch_search_query_results_filter', 10);
            $wpml_job_totnum = $job_totnum = $job_loop_obj->found_posts;

            if (function_exists('icl_object_id') && $wpml_job_totnum == 0 && isset($trans_able_options['job']) && $trans_able_options['job'] == '2') {
                $sitepress_def_lang = $sitepress->get_default_language();
                $sitepress_curr_lang = $sitepress->get_current_language();
                $sitepress->switch_lang($sitepress_def_lang, true);

                add_filter('posts_where', 'jobsearch_search_query_results_filter', 10, 2);
                $job_loop_obj = jobsearch_get_cached_obj('job_result_cached_loop_obj1', $args, 12, false, 'wp_query');
                remove_filter('posts_where', 'jobsearch_search_query_results_filter', 10);
                $job_totnum = $job_loop_obj->found_posts;

                //
                $sitepress->switch_lang($sitepress_curr_lang, true);
            }
            ?>
            <form id="jobsearch_job_frm_<?php echo absint($job_short_counter); ?>">
                <?php
                $listing_top_map = isset($atts['job_top_map']) ? $atts['job_top_map'] : '';
                $listing_top_map_zoom = isset($atts['job_top_map_zoom']) && $atts['job_top_map_zoom'] > 0 ? $atts['job_top_map_zoom'] : 8;
                $listing_top_map_height = isset($atts['job_top_map_height']) && $atts['job_top_map_height'] > 0 ? $atts['job_top_map_height'] : 450;
                if ($listing_top_map == 'yes') {
                    wp_enqueue_script('jobsearch-google-map');
                    wp_enqueue_script('jobsearch-map-infobox');
                    wp_enqueue_script('jobsearch-map-markerclusterer');
                    wp_enqueue_script('jobsearch-job-lists-map');
                    $map_style = isset($jobsearch_plugin_options['jobsearch-location-map-style']) ? $jobsearch_plugin_options['jobsearch-location-map-style'] : '';
                    $map_zoom = $listing_top_map_zoom;
                    $loc_def_adres = isset($jobsearch_plugin_options['jobsearch-location-default-address']) ? $jobsearch_plugin_options['jobsearch-location-default-address'] : '';

                    $map_latitude = '51.2';
                    $map_longitude = '0.2';

                    if ($loc_def_adres != '') {
                        $adre_to_cords = jobsearch_address_to_cords($loc_def_adres);
                        $map_latitude = isset($adre_to_cords['lat']) && $adre_to_cords['lat'] != '' ? $adre_to_cords['lat'] : $map_latitude;
                        $map_longitude = isset($adre_to_cords['lng']) && $adre_to_cords['lng'] != '' ? $adre_to_cords['lng'] : $map_longitude;
                    }
                    $map_marker_icon = isset($jobsearch_plugin_options['listin_map_marker_img']['url']) ? $jobsearch_plugin_options['listin_map_marker_img']['url'] : '';
                    if ($map_marker_icon == '') {
                        $map_marker_icon = jobsearch_plugin_get_url('images/job_map_marker.png');
                    }
                    $map_cluster_icon = isset($jobsearch_plugin_options['listin_map_cluster_img']['url']) ? $jobsearch_plugin_options['listin_map_cluster_img']['url'] : '';
                    if ($map_cluster_icon == '') {
                        $map_cluster_icon = jobsearch_plugin_get_url('images/map_cluster.png');
                    }
                    //
                    $map_list_arr = array();
                    $job_all_posts = $job_loop_obj->posts;
                    foreach ($job_all_posts as $job_post) {
                        $listing_latitude = get_post_meta($job_post, 'jobsearch_field_location_lat', true);
                        $listing_longitude = get_post_meta($job_post, 'jobsearch_field_location_lng', true);

                        if ($listing_latitude != '' && $listing_longitude != '') {
                            //employer html
                            $pos_employer = get_post_meta($job_post, 'jobsearch_field_job_posted_by', true);
                            $pos_employer_html = '';
                            if ($pos_employer > 0) {
                                $pos_employer_html = esc_html__('Posted by', 'wp-jobsearch') . ' <a href="' . get_permalink($pos_employer) . '">' . get_the_title($pos_employer) . '</a>';
                            }
                            //sectors html
                            $get_pos_sectrs = wp_get_post_terms($job_post, 'sector');
                            $map_pos_sectrs_html = '';
                            if (!empty($get_pos_sectrs)) {
                                $map_secpage_id = isset($jobsearch_plugin_options['jobsearch_search_list_page']) ? $jobsearch_plugin_options['jobsearch_search_list_page'] : '';
                                $map_secpage_id = jobsearch__get_post_id($map_secpage_id, 'page');
                                $map_secresult_page = get_permalink($map_secpage_id);
                                $map_pos_sectrs_html .= ' ' . esc_html__('in', 'wp-jobsearch') . ' ';
                                foreach ($get_pos_sectrs as $get_pos_sectr) {
                                    $map_pos_sectrs_html .= '<a href="' . add_query_arg(array('sector_cat' => $get_pos_sectr->slug), $map_secresult_page) . '">' . $get_pos_sectr->name . '</a> ';
                                }
                            }
                            //logo img
                            $map_pos_thum_id = jobsearch_job_get_profile_image($job_post);
                            $map_pos_thumb_image = wp_get_attachment_image_src($map_pos_thum_id, 'thumbnail');
                            $map_pos_thumb_src = isset($map_pos_thumb_image[0]) && esc_url($map_pos_thumb_image[0]) != '' ? $map_pos_thumb_image[0] : '';
                            $map_pos_thumb_src = $map_pos_thumb_src == '' ? jobsearch_no_image_placeholder() : $map_pos_thumb_src;

                            //address
                            $map_posadres = jobsearch_job_item_address($job_post);
                            if ($map_posadres != '') {
                                $map_posadres = '<div class="map-info-adres"><i class="jobsearch-icon jobsearch-maps-and-flags"></i> ' . $map_posadres . '</div>';
                            }

                            $map_list_arr[] = array(
                                'lat' => $listing_latitude,
                                'long' => $listing_longitude,
                                'id' => $job_post,
                                'title' => wp_trim_words(get_the_title($job_post), 5),
                                'link' => get_permalink($job_post),
                                'logo_img_url' => $map_pos_thumb_src,
                                'address' => $map_posadres,
                                'employer' => $pos_employer_html,
                                'sector' => $map_pos_sectrs_html,
                                'marker' => $map_marker_icon,
                            );
                        }
                    }
                    //
                    $listn_map_arr = array(
                        'map_id' => $job_short_counter,
                        'map_zoom' => $map_zoom,
                        'map_style' => $map_style,
                        'latitude' => $map_latitude,
                        'longitude' => $map_longitude,
                        'cluster_icon' => $map_cluster_icon,
                        'cords_list' => $map_list_arr,
                    );
                    $listn_map_obj = json_encode($listn_map_arr);
                    ?>
                    <script>
                        var jobsearch_listing_map;
                        var reset_top_map_marker = [];
                        var markerClusterers;
                        var jobsearch_listing_dataobj = jQuery.parseJSON('<?php echo addslashes($listn_map_obj) ?>');
                <?php
                if (isset($_REQUEST['ajax_filter']) && $_REQUEST['ajax_filter'] == 'true' && isset($_REQUEST['action']) && $_REQUEST['action'] == 'jobsearch_jobs_content') {
                    ?>
                            jobsearch_listing_top_map(jobsearch_listing_dataobj, 'true');
                    <?php
                }
                ?>
                        jQuery(document).ready(function () {
                            jobsearch_listing_top_map(jobsearch_listing_dataobj, '');
                        });
                    </script>
                    <div class="jobsearch-listing-mapcon <?php echo ($atts['job_top_search'] != 'no' ? 'with-serch-map-both' : '') ?>">
                        <div id="listings-map-<?php echo absint($job_short_counter); ?>" class="jobsearch-joblist-map" style="height: <?php echo ($listing_top_map_height) ?>px;"></div>
                    </div>
                    <?php
                    echo '<div class="container">';
                }
                ?>
                <div style="display:none" id='job_arg<?php echo absint($job_short_counter); ?>'>
                    <?php
                    echo json_encode($job_arg);
                    ?>
                </div>
                <?php
                if ($atts['job_top_search'] != 'no') {

                    wp_enqueue_script('jobsearch-google-map');
                    wp_enqueue_script('jobsearch-location-autocomplete');

                    //
                    wp_enqueue_script('jobsearch-search-box-sugg');

                    $top_serch_style = isset($atts['job_top_search_view']) ? $atts['job_top_search_view'] : '';
                    //
                    $search_title_val = isset($_REQUEST['search_title']) ? $_REQUEST['search_title'] : '';
                    $location_val = isset($_REQUEST['location']) ? $_REQUEST['location'] : '';
                    $cat_sector_val = isset($_REQUEST['sector_cat']) ? urldecode($_REQUEST['sector_cat']) : '';

                    $search_main_class = '';
                    if ($top_serch_style == 'advance') {
                        $search_main_class = 'jobsearch-advance-search-holdr';
                    }
                    if ($listing_top_map == 'yes') {
                        $search_main_class .= ' search-with-map';
                    }
                    $job_filters_sector = isset($atts['job_filters_sector']) ? $atts['job_filters_sector'] : '';
                    $without_sectr_class = 'search-cat-off';
                    if ($job_filters_sector == 'yes') {
                        $without_sectr_class = '';
                    }
                    ?>
                    <div class="jobsearch-top-searchbar jobsearch-typo-wrap <?php echo ($search_main_class) ?>">
                        <!-- Sub Header Form -->
                        <div class="jobsearch-subheader-form">
                            <div class="jobsearch-banner-search <?php echo ($without_sectr_class) ?>">
                                <ul>
                                    <li>
                                        <div class="jobsearch-sugges-search">
                                            <input placeholder="<?php esc_html_e('Job Title, Keywords, or Phrase', 'wp-jobsearch') ?>" name="search_title" value="<?php echo ($search_title_val) ?>" data-type="job" type="text">
                                            <span class="sugg-search-loader"></span>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="jobsearch_searchloc_div">
                                            <span class="loc-loader"></span>
                                            <input placeholder="<?php esc_html_e('City, State or ZIP', 'wp-jobsearch') ?>" autocomplete="off" class="jobsearch_search_location_field" value="<?php echo urldecode($location_val) ?>" type="text">
                                            <input type="hidden" class="loc_search_keyword" name="location" value="<?php echo urldecode($location_val) ?>">
                                        </div>
                                        <a href="javascript:void(0);" class="geolction-btn" onclick="JobsearchGetClientLocation()"><i class="jobsearch-icon jobsearch-location"></i></a>
                                    </li>
                                    <?php
                                    $job_filters_sector = isset($atts['job_filters_sector']) ? $atts['job_filters_sector'] : '';
                                    if ($job_filters_sector == 'yes') {
                                        $all_sectors = get_terms(array(
                                            'taxonomy' => 'sector',
                                            'hide_empty' => false,
                                        ));
                                        ?>
                                        <li>
                                            <div class="jobsearch-select-style">
                                                <select name="sector_cat" class="selectize-select" placeholder="<?php esc_html_e('Select Sector', 'wp-jobsearch') ?>">
                                                    <option value=""><?php esc_html_e('Select Sector', 'wp-jobsearch') ?></option>
                                                    <?php
                                                    if (!empty($all_sectors) && !is_wp_error($all_sectors)) {
                                                        foreach ($all_sectors as $term_sector) {
                                                            ?>
                                                            <option <?php echo ($cat_sector_val == $term_sector->slug ? 'selected="selected"' : '') ?> value="<?php echo ($term_sector->slug) ?>"><?php echo ($term_sector->name) ?></option>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </li>
                                        <?php
                                    }
                                    if ($top_serch_style == 'advance') {
                                        ?>
                                        <li class="adv-srch-toggler"><a href="javascript:void(0);" class="adv-srch-toggle-btn"><span>+</span> <?php esc_html_e('Advance Search', 'wp-jobsearch') ?></a></li>
                                        <?php
                                    }
                                    ?>
                                    <li class="jobsearch-banner-submit">
                                        <input type="hidden" name="ajax_filter" value="true">
                                        <input type="submit" value=""> <i class="jobsearch-icon jobsearch-search"></i> 
                                    </li>
                                </ul>
                                <?php
                                if ($top_serch_style == 'advance') {
                                    $sh_atts = isset($job_arg['atts']) ? $job_arg['atts'] : '';
                                    ?>
                                    <div class="adv-search-options">
                                        <ul>
                                            <li class="srch-radius-slidr">
                                                <?php
                                                wp_enqueue_style('jquery-ui');
                                                wp_enqueue_script('jquery-ui');
                                                $tprand_id = rand(1000000, 99999999);
                                                $tpsrch_min = 0;
                                                $tpsrch_field_max = 500;
                                                $tpsrch_complete_str_first = "";
                                                $tpsrch_complete_str_second = "";
                                                $tpsrch_complete_str = '0';
                                                $tpsrch_complete_str_first = $tpsrch_min;
                                                $tpsrch_complete_str_second = $tpsrch_field_max;
                                                $tpsrch_str_var_name = 'loc_radius';
                                                if (isset($_REQUEST[$tpsrch_str_var_name])) {
                                                    $tpsrch_complete_str = $_REQUEST[$tpsrch_str_var_name];
                                                    $tpsrch_complete_str_arr = explode("-", $tpsrch_complete_str);
                                                    $tpsrch_complete_str_first = isset($tpsrch_complete_str_arr[0]) ? $tpsrch_complete_str_arr[0] : '';
                                                    $tpsrch_complete_str_second = isset($tpsrch_complete_str_arr[1]) ? $tpsrch_complete_str_arr[1] : '';
                                                }
                                                ?>
                                                <div class="filter-slider-range">
                                                    <span class="radius-txt"><?php esc_html_e('Radius:', 'wp-jobsearch') ?></span>
                                                    <span id="radius-num-<?php echo esc_html($tpsrch_str_var_name . $tprand_id) ?>" class="radius-numvr-holdr"><?php echo esc_html($tpsrch_complete_str); ?></span>
                                                    <span class="radius-punit"><?php esc_html_e('km', 'wp-jobsearch') ?></span>
                                                    <input type="hidden" name="loc_radius" id="<?php echo esc_html($tpsrch_str_var_name . $tprand_id) ?>" value="<?php echo esc_html($tpsrch_complete_str); ?>" />
                                                </div>

                                                <div id="slider-tpsrch<?php echo esc_html($tpsrch_str_var_name . $tprand_id) ?>"></div>
                                                <script>
                                                    jQuery(document).ready(function () {

                                                        jQuery("#slider-tpsrch<?php echo esc_html($tpsrch_str_var_name . $tprand_id) ?>").slider({
                                                            tpsrch: true,
                                                            min: <?php echo absint($tpsrch_min); ?>,
                                                            max: <?php echo absint($tpsrch_field_max); ?>,
                                                            values: [<?php echo absint($tpsrch_complete_str_first); ?>],
                                                            slide: function (event, ui) {
                                                                jQuery("#<?php echo esc_html($tpsrch_str_var_name . $tprand_id) ?>").val(ui.values[0]);
                                                                jQuery("#radius-num-<?php echo esc_html($tpsrch_str_var_name . $tprand_id) ?>").html(ui.values[0]);
                                                            },
                                                        });
                                                        jQuery("#<?php echo esc_html($tpsrch_str_var_name . $tprand_id) ?>").val(jQuery("#slider-tpsrch<?php echo esc_html($tpsrch_str_var_name . $tprand_id) ?>").slider("values", 0));

                                                    });
                                                </script>
                                            </li>
                                            <?php
                                            echo apply_filters('jobsearch_job_top_filter_date_posted_box_html', '', $job_short_counter, $sh_atts);
                                            echo apply_filters('jobsearch_job_top_filter_jobtype_box_html', '', $job_short_counter, $sh_atts);
                                            //echo apply_filters('jobsearch_job_top_filter_sector_box_html', '', $job_short_counter, $sh_atts);
                                            echo apply_filters('jobsearch_custom_fields_top_filters_html', '', 'job', $job_short_counter);
                                            ?>
                                        </ul>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                        </div>
                        <!-- Sub Header Form -->
                    </div>
                    <?php
                }
                ?>
                <div class="jobsearch-row">
                    <?php
                    set_query_var('job_type', $job_type);
                    set_query_var('job_short_counter', $job_short_counter);
                    set_query_var('job_arg', $job_arg);
                    set_query_var('job_view', $job_view);
                    set_query_var('args_count', $args_count);
                    if (isset($job_right_sidebar_content)) {
                        set_query_var('job_right_sidebar_content', $job_right_sidebar_content);
                    }
                    set_query_var('atts', $atts);
                    set_query_var('job_totnum', $job_totnum);
                    set_query_var('page_url', $page_url);
                    set_query_var('job_loop_obj', $job_loop_obj);
                    set_query_var('global_rand_id', $job_short_counter);
                    if (( $job_filters_sidebar == 'yes' ) || (!empty($jobsearch_job_sidebar) )) {  // if sidebar on from element
                        jobsearch_get_template_part('filters', 'job-template', 'jobs');
                        $content_columns = 'jobsearch-column-9 jobsearch-typo-wrap';
                    } else if (isset($job_right_sidebar_content) && $job_right_sidebar_content != '') {
                        $content_columns = 'jobsearch-column-12 jobsearch-typo-wrap';
                    }
                    ?>
                    <div class="<?php echo esc_html($content_columns); ?>">
                        <?php do_action('jobsearch_jobs_listing_before', array('sh_atts' => (isset($job_arg['atts']) ? $job_arg['atts'] : ''))); ?>
                        <div class="wp-jobsearch-job-content jobsearch-listing-maincon wp-jobsearch-dev-job-content" id="jobsearch-data-job-content-<?php echo esc_html($job_short_counter); ?>" data-id="<?php echo esc_html($job_short_counter); ?>">
                            <div id="jobsearch-loader-<?php echo esc_html($job_short_counter); ?>"></div>
                            <?php
                            $jobs_title = isset($atts['jobs_title']) ? $atts['jobs_title'] : '';
                            $jobs_subtitle = isset($atts['jobs_subtitle']) ? $atts['jobs_subtitle'] : '';
                            $jobs_title_alignment = isset($atts['jobs_title_alignment']) ? $atts['jobs_title_alignment'] : '';
                            $job_element_seperator = isset($atts['jobsearch_jobs_seperator_style']) ? $atts['jobsearch_jobs_seperator_style'] : '';
                            $jobsearch_jobs_element_title_color = isset($atts['jobsearch_jobs_element_title_color']) ? $atts['jobsearch_jobs_element_title_color'] : '';
                            $jobsearch_jobs_element_subtitle_color = isset($atts['jobsearch_jobs_element_subtitle_color']) ? $atts['jobsearch_jobs_element_subtitle_color'] : '';
                            $element_title_color = '';
                            if (isset($jobsearch_jobs_element_title_color) && $jobsearch_jobs_element_title_color != '') {
                                $element_title_color = ' style="color:' . $jobsearch_jobs_element_title_color . ' ! important;"';
                            }
                            $element_subtitle_color = '';
                            if (isset($jobsearch_jobs_element_subtitle_color) && $jobsearch_jobs_element_subtitle_color != '') {
                                $element_subtitle_color = ' style="color:' . $jobsearch_jobs_element_subtitle_color . ' ! important;"';
                            }
                            if ($jobs_title != '' || $jobs_subtitle != '') {
                                ?>
                                <div class="row">
                                    <div class="jobsearch-column-12 jobsearch-typo-wrap">
                                        <div class="element-title <?php echo ($jobs_title_alignment); ?>">
                                            <?php
                                            if ($jobs_title != '' || $jobs_subtitle != '') {
                                                if ($jobs_title != '') {
                                                    ?>
                                                    <h2<?php echo force_balance_tags($element_title_color); ?>><?php echo esc_html($jobs_title); ?></h2>
                                                    <?php
                                                }
                                                if ($jobs_subtitle != '') {
                                                    ?>
                                                    <p <?php echo force_balance_tags($element_subtitle_color); ?>><?php echo esc_html($jobs_subtitle); ?></p>
                                                    <?php
                                                }
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                            // only ajax request procced
                            if (isset($job_view)) {
                                $search_keyword_html = apply_filters('jobsearch_search_keyword', '', $page_url);
                                echo force_balance_tags($search_keyword_html);
                                // sorting fields
                                $this->job_search_sort_fields($atts, $job_sort_by, $job_short_counter, $job_view, $job_totnum, $job_per_page);
                            }

                            // top featured jobs
                            $job_feat_jobs_top = isset($atts['job_feat_jobs_top']) ? $atts['job_feat_jobs_top'] : '';
                            if ($job_feat_jobs_top == 'yes') {
                                $num_of_feat_jobs = isset($atts['num_of_feat_jobs']) ? $atts['num_of_feat_jobs'] : '';
                                $num_of_feat_jobs = $num_of_feat_jobs > 0 ? $num_of_feat_jobs : 5;
                                $fjobs_args = array(
                                    'posts_per_page' => $num_of_feat_jobs,
                                    'paged' => isset($_REQUEST[$paging_var]) ? $_REQUEST[$paging_var] : 1,
                                    'post_type' => 'job',
                                    'post_status' => 'publish',
                                    'order' => 'ID',
                                    'orderby' => 'DESC',
                                    'fields' => 'ids', // only load ids
                                    'meta_query' => array(
                                        array(
                                            'key' => 'jobsearch_field_job_publish_date',
                                            'value' => strtotime(current_time($default_date_time_formate)),
                                            'compare' => '<=',
                                        ),
                                        array(
                                            'key' => 'jobsearch_field_job_expiry_date',
                                            'value' => strtotime(current_time($default_date_time_formate)),
                                            'compare' => '>=',
                                        ),
                                        array(
                                            'key' => 'jobsearch_field_job_featured',
                                            'value' => 'on',
                                            'compare' => '=',
                                        ),
                                        array(
                                            'key' => 'jobsearch_field_job_status',
                                            'value' => 'approved',
                                            'compare' => '=',
                                        ),
                                        array(
                                            'key' => 'jobsearch_job_employer_status',
                                            'value' => 'approved',
                                            'compare' => '=',
                                        ),
                                    ),
                                );
                                $fjobs_query = new WP_Query($fjobs_args);
                                $fjobs_posts = $fjobs_query->posts;
                                //$fjobs_posts = array();
                                wp_reset_postdata();
                                set_query_var('featjobs_posts', $fjobs_posts);
                            }

                            set_query_var('job_loop_obj', $job_loop_obj);
                            set_query_var('job_view', $job_view);
                            set_query_var('job_desc', $job_desc);
                            set_query_var('job_cus_fields', $job_cus_fields);
                            set_query_var('job_short_counter', $job_short_counter);
                            set_query_var('atts', $atts);
                            jobsearch_get_template_part($job_view, 'job-template', 'jobs');
                            wp_reset_postdata();
                            ?>
                        </div>
                        <?php
                        // apply paging
                        $paging_args = array('total_posts' => $job_totnum,
                            'job_per_page' => $job_per_page,
                            'paging_var' => $paging_var,
                            'show_pagination' => $pagination,
                            'job_short_counter' => $job_short_counter,
                        );
                        $this->jobsearch_job_pagination_callback($paging_args);
                        ?>
                    </div>
                </div>
                <?php
                if ($loc_polygon_path != '') {
                    $jobsearch_form_fields->input_hidden_field(
                            array(
                                'simple' => true,
                                'cust_id' => "loc_polygon_path",
                                'cust_name' => 'loc_polygon_path',
                                'std' => $loc_polygon_path,
                            )
                    );
                }
                $jobsearch_form_fields->input_hidden_field(
                        array(
                            'return' => false,
                            'cust_name' => '',
                            'classes' => 'job-counter',
                            'std' => $job_short_counter,
                        )
                );

                //
                if ($listing_top_map == 'yes') {
                    echo '</div>';
                }
                ?>

            </form>
            <?php
            do_action('jobsearch_after_jobs_listing_content', $args, $job_sort_by);
            // only for ajax request
            if (isset($_REQUEST['action']) && strpos($_REQUEST['action'], 'jobsearch_') !== false) {
                die();
            }
        }

        public function job_polygon_filter($polygon_pathstr, $post_ids, $custom_meta_array = '') {
            global $wpdb;
            if (empty($post_ids)) {
                if (isset($custom_meta_array) && !empty($custom_meta_array) && is_array($custom_meta_array)) {
                    $post_ids = jobsearch_get_query_whereclase_by_array($custom_meta_array);
                }
            }
            $polygon_path = array();
            $polygon_path = explode('||', $polygon_pathstr);
            if (count($polygon_path) > 0) {
                array_walk($polygon_path, function(&$val) {
                    $val = explode(',', $val);
                });
            }
            $new_post_ids = array();
            $th_counter = 0;
            foreach ($post_ids as $job_id) {
                $qry = "SELECT meta_value FROM $wpdb->postmeta WHERE 1=1 AND post_id='" . $job_id . "' AND meta_key='jobsearch_field_location_lat'";
                $job_latitude_arr = $wpdb->get_col($qry);
                $job_latitude = isset($job_latitude_arr[0]) ? $job_latitude_arr[0] : '';

                $qry = "SELECT meta_value FROM $wpdb->postmeta WHERE 1=1 AND post_id='" . $job_id . "' AND meta_key='jobsearch_field_location_lng'";
                $job_longitude_arr = $wpdb->get_col($qry);
                $job_longitude = isset($job_longitude_arr[0]) ? $job_longitude_arr[0] : '';

                if ($this->pointInPolygon(array($job_latitude, $job_longitude), $polygon_path)) {
                    $new_post_ids[] = $job_id;
                }
                if ($th_counter > 3000) {
                    break;
                }
                $th_counter ++;
            }
            return $new_post_ids;
        }

        public function pointInPolygon($point, $polygon) {
            $return = false;
            foreach ($polygon as $k => $p) {
                if (!$k)
                    $k_prev = count($polygon) - 1;
                else
                    $k_prev = $k - 1;

                if (($p[1] < $point[1] && $polygon[$k_prev][1] >= $point[1] || $polygon[$k_prev][1] < $point[1] && $p[1] >= $point[1]) && ($p[0] <= $point[0] || $polygon[$k_prev][0] <= $point[0])) {
                    if ($p[0] + ($point[1] - $p[1]) / ($polygon[$k_prev][1] - $p[1]) * ($polygon[$k_prev][0] - $p[0]) < $point[0]) {
                        $return = !$return;
                    }
                }
            }
            return $return;
        }

        public function get_filter_arg($job_short_counter = '', $exclude_meta_key = '') {
            global $jobsearch_post_job_types;
            $filter_arr = array();
            $posted = '';
            $default_date_time_formate = 'd-m-Y H:i:s';
            $current_timestamp = current_time('timestamp');
            if (isset($_REQUEST['posted'])) {
                $posted = $_REQUEST['posted'];
            }
            if ($posted != '') {
                $lastdate = '';
                $now = '';
                if ($posted == 'lasthour') {
                    $now = date($default_date_time_formate, $current_timestamp);
                    $lastdate = date($default_date_time_formate, strtotime('-1 hours', $current_timestamp));
                } elseif ($posted == 'last24') {
                    $now = date($default_date_time_formate, $current_timestamp);
                    $lastdate = date($default_date_time_formate, strtotime('-24 hours', $current_timestamp));
                } elseif ($posted == '7days') {
                    $now = date($default_date_time_formate, $current_timestamp);
                    $lastdate = date($default_date_time_formate, strtotime('-7 days', $current_timestamp));
                } elseif ($posted == '14days') {
                    $now = date($default_date_time_formate, $current_timestamp);
                    $lastdate = date($default_date_time_formate, strtotime('-14 days', $current_timestamp));
                } elseif ($posted == '30days') {
                    $now = date($default_date_time_formate, $current_timestamp);
                    $lastdate = date($default_date_time_formate, strtotime('-30 days', $current_timestamp));
                }
                if ($lastdate != '' && $now != '') {
                    $filter_arr[] = array(
                        'key' => 'jobsearch_field_job_publish_date',
                        'value' => strtotime($lastdate),
                        'compare' => '>=',
                    );
                }
            }
            // custom field array for filteration from custom field module
            $filter_arr = apply_filters('jobsearch_custom_fields_load_filter_array_html', 'job', $filter_arr, $exclude_meta_key);
            return $filter_arr;
        }

        public function get_job_id_by_filter($left_filter_arr, $post_type = 'job') {
            global $wpdb;
            $meta_post_ids_arr = '';
            $job_id_condition = '';

            if (isset($left_filter_arr) && !empty($left_filter_arr)) {
                $meta_post_ids_arr = jobsearch_get_query_whereclase_by_array($left_filter_arr);
                // if no result found in filtration 
                if (empty($meta_post_ids_arr)) {
                    $meta_post_ids_arr = array(0);
                }
                if (isset($_REQUEST['loc_polygon_path']) && $_REQUEST['loc_polygon_path'] != '' && $meta_post_ids_arr != '') {
                    $meta_post_ids_arr = $this->job_polygon_filter($_REQUEST['loc_polygon_path'], $meta_post_ids_arr);
                    if (empty($meta_post_ids_arr)) {
                        $meta_post_ids_arr = '';
                    }
                }
                $ids = $meta_post_ids_arr != '' ? implode(",", $meta_post_ids_arr) : '0';
                $job_id_condition = " ID in (" . $ids . ") AND ";
            }

            $post_ids = $wpdb->get_col("SELECT ID FROM $wpdb->posts WHERE " . $job_id_condition . " post_type='" . $post_type . "' AND post_status='publish'");

            if (empty($post_ids)) {
                $post_ids = array(0);
            }
            return $post_ids;
        }

        public function job_search_sort_fields($atts, $job_sort_by, $job_short_counter, $view = '', $job_totnum = '', $job_per_page = '') {
            global $jobsearch_form_fields;

            $counter = isset($atts['job_counter']) && $atts['job_counter'] != '' ? $atts['job_counter'] : '';
            $transient_view = jobsearch_get_transient_obj('jobsearch_job_view' . $counter);
            $view = isset($transient_view) && $transient_view != '' ? $transient_view : $view;

            $job_type_slug = isset($_REQUEST['job_type']) ? $_REQUEST['job_type'] : '';
            $job_type_text = $job_type_slug;
            if (isset($job_type_slug) && !empty($job_type_slug) && $job_type_slug != 'all') {
                if ($post = get_page_by_path($job_type_slug, OBJECT, 'job-type')) {
                    $id = $post->ID;
                    $job_type_text = get_the_title($id);
                }
            }

            $view_type = '';

            $job_feat_jobs_top = isset($atts['job_feat_jobs_top']) ? $atts['job_feat_jobs_top'] : '';

            if (( isset($atts['job_sort_by']) && $atts['job_sort_by'] != 'no')) {
                ?>
                <div class="jobsearch-filterable jobsearch-filter-sortable">
                    <h2>
                        <?php
                        echo absint($job_totnum) . '&nbsp;';
                        if ($job_totnum == 1) {
                            echo esc_html__('Job Found', 'wp-jobsearch');
                        } else {
                            echo esc_html__('Jobs Found', 'wp-jobsearch');
                        }
                        ?>
                    </h2> 
                    <ul class="jobsearch-sort-section">
                        <li>
                            <i class="jobsearch-icon jobsearch-sort"></i>
                            <div class="jobsearch-filterable-select"> 

                                <?php
                                $sortby_option = array('recent' => esc_html__('Most Recent', 'wp-jobsearch'),
                                    'featured' => esc_html__('Featured', 'wp-jobsearch'),
                                    'alphabetical' => esc_html__('Alphabet Order', 'wp-jobsearch'),
                                    'most_viewed' => esc_html__('Most Viewed', 'wp-jobsearch')
                                );
                                $sortby_option = apply_filters('job_hunt_jobs_sort_options', $sortby_option);
                                $cs_opt_array = array(
                                    'cus_id' => '',
                                    'cus_name' => 'sort-by',
                                    'force_std' => $job_sort_by,
                                    'desc' => '',
                                    'classes' => 'selectize-select',
                                    'ext_attr' => ' onchange="jobsearch_job_content_load(\'' . esc_js($job_short_counter) . '\')" placeholder="' . esc_html__('Most Recent', 'wp-jobsearch') . '"',
                                    'options' => $sortby_option,
                                );
                                $jobsearch_form_fields->select_field($cs_opt_array);
                                ?>
                            </div>
                        </li>
                        <li>
                            <i class="jobsearch-icon jobsearch-sort"></i>
                            <div class="jobsearch-filterable-select">
                                <?php
                                $paging_options = array();
                                $paging_options[""] = esc_html__("Records Per Page", "wp-jobsearch");
                                $paging_options["10"] = '10 ' . esc_html__("Per Page", "wp-jobsearch");
                                $paging_options["20"] = '20 ' . esc_html__("Per Page", "wp-jobsearch");
                                $paging_options["30"] = '30 ' . esc_html__("Per Page", "wp-jobsearch");
                                $paging_options["50"] = '50 ' . esc_html__("Per Page", "wp-jobsearch");
                                $paging_options["70"] = '70 ' . esc_html__("Per Page", "wp-jobsearch");
                                $paging_options["100"] = '100 ' . esc_html__("Per Page", "wp-jobsearch");
                                $paging_options["200"] = '200 ' . esc_html__("Per Page", "wp-jobsearch");
                                $cs_opt_array = array(
                                    'cus_id' => '',
                                    'cus_name' => 'per-page',
                                    'force_std' => $job_per_page,
                                    'desc' => '',
                                    'classes' => 'sort-records-per-page',
                                    'ext_attr' => ' onchange="jobsearch_job_content_load(\'' . esc_js($job_short_counter) . '\')" placeholder="' . esc_html__('Records Per Page', 'wp-jobsearch') . '"',
                                    'options' => $paging_options,
                                );

                                $jobsearch_form_fields->select_field($cs_opt_array);
                                ?>
                            </div>
                        </li>
                    </ul>
                    <?php
                    $this->job_layout_switcher_fields($atts, $job_short_counter, $view = '');
                    ?>          
                </div>
                <!-- filter-moving -->
                <?php
                $adv_filter_toggle = isset($_REQUEST['adv_filter_toggle']) ? $_REQUEST['adv_filter_toggle'] : 'false';

                $args_more = array(
                    'job_type' => $atts['job_type'],
                    'job_filters' => $atts['job_filters'],
                    'jobsearch_map_position' => isset($atts['jobsearch_map_position']) && $atts['jobsearch_map_position'] != '' ? ( $atts['jobsearch_map_position'] ) : 'right',
                    'job_short_counter' => $job_short_counter,
                    'job_sort_by' => $atts['job_sort_by'],
                    'adv_filter_toggle' => $adv_filter_toggle,
                );
                do_action('jobsearch_search_more_filter', $args_more);
                $jobsearch_form_fields->input_hidden_field(
                        array(
                            'simple' => true,
                            'classes' => "adv_filter_toggle",
                            'cust_name' => 'adv_filter_toggle',
                            'std' => $adv_filter_toggle,
                        )
                );
            }
        }

        public function job_layout_switcher_fields($atts, $job_short_counter, $view = '', $frc_view = false) {

            $counter = isset($atts['job_counter']) && $atts['job_counter'] != '' ? $atts['job_counter'] : '';
            $transient_view = jobsearch_get_transient_obj('jobsearch_job_view' . $counter);

            if ($frc_view == true) {
                $view = $view;
            } else {
                if (false === ( $view = jobsearch_get_transient_obj('jobsearch_job_view' . $counter) )) {
                    $view = isset($atts['job_view']) ? $atts['job_view'] : '';
                }
            }
            if (( isset($atts['job_layout_switcher']) && $atts['job_layout_switcher'] != 'no')) {

                if (isset($atts['job_layout_switcher_view']) && !empty($atts['job_layout_switcher_view'])) {
                    $job_layout_switcher_views = array(
                        'grid' => esc_html__('grid', 'wp-jobsearch'),
                        'list' => esc_html__('list', 'wp-jobsearch'),
                    );
                    ?> 
                    <ul class="jobs-views-switcher-holder">
                        <li><?php echo esc_html__('jobsearch_view_jobs_by_switcher'); ?></li>
                        <?php
                        $element_job_layout_switcher_view = explode(',', $atts['job_layout_switcher_view']);

                        if (!empty($element_job_layout_switcher_view) && is_array($element_job_layout_switcher_view)) {
                            $views_counter = 0;
                            foreach ($element_job_layout_switcher_view as $single_layout_view) {
                                $case_for_list = $single_layout_view;
                                if ($single_layout_view == 'list') {
                                    $case_for_list = 'listed';
                                }
                                if ($single_layout_view == 'grid-medern') {
                                    $case_for_list = 'grid-medern';
                                }
                                switch ($case_for_list) {
                                    case 'grid':
                                        $icon = '<i class="icon-th-large"></i> ';
                                        $icon .= esc_html__('grid', 'wp-jobsearch');
                                        $view_class = 'grid-view';
                                        break;
                                    case 'listed':
                                        $icon = '<i class="icon-th-list"></i> ';
                                        $icon .= esc_html__('list', 'wp-jobsearch');
                                        $view_class = 'list-view';
                                        break;
                                    case 'grid-medern':
                                        $icon = '<i class="icon-th"></i> ';
                                        $icon .= esc_html__('modern grid', 'wp-jobsearch');
                                        $view_class = 'grid-modern-view';
                                        break;
                                    case 'grid-classic':
                                        $icon = '<i class="icon-grid_on"></i> ';
                                        $icon .= esc_html__('classic grid', 'wp-jobsearch');
                                        $view_class = 'grid-classic-view';
                                        break;
                                    case 'grid-default':
                                        $icon = '<i class="icon-menu4"></i> ';
                                        $icon .= esc_html__('default grid', 'wp-jobsearch');
                                        $view_class = 'grid-default-view';
                                        break;
                                    case 'list-modern':
                                        $icon = '<i class="icon-list5"></i> ';
                                        $icon .= esc_html__('modern list', 'wp-jobsearch');
                                        $view_class = 'list-modern-view';
                                        break;
                                    default:
                                        $icon = '<i class="icon-th-list"></i> ';
                                        $icon .= esc_html__('list', 'wp-jobsearch');
                                        $view_class = 'list-view';
                                }
                                if (empty($view) && $views_counter === 0) {
                                    ?>
                                    <li><a href="javascript:void(0);" class="active"><i class="icon-th-list"></i><?php echo esc_html($job_layout_switcher_views[$single_layout_view]); ?></a></li>
                                    <?php
                                } else {
                                    $view_type = '';
                                    ?>
                                    <li class="<?php echo esc_html($view_class); ?>"><a href="javascript:void(0);" <?php if ($view == $single_layout_view) echo 'class="active"'; ?> <?php if ($view != $single_layout_view) { ?> onclick="jobsearch_job_view_switch('<?php echo esc_html($single_layout_view) ?>', '<?php echo esc_html($job_short_counter); ?>', '<?php echo esc_html($counter); ?>', '<?php echo esc_html($view_type); ?>');"<?php } ?>><?php echo force_balance_tags($icon); ?></a></li>
                                    <?php
                                }
                                $views_counter ++;
                            }
                        }
                        ?>
                    </ul>
                    <?php
                }
            }
        }

        public function jobsearch_job_view_switch() {
            $view = jobsearch_get_input('view', NULL, 'STRING');
            $job_short_counter = jobsearch_get_input('job_short_counter', NULL, 'STRING');
            jobsearch_set_transient_obj('jobsearch_job_view' . $job_short_counter, $view);
            echo 'success';
            wp_die();
        }

        public function job_location_filter($all_post_ids) {

            global $sitepress;

            $radius = isset($_REQUEST['loc_radius']) ? $_REQUEST['loc_radius'] : '';
            $search_type = isset($_REQUEST['location_location1']) ? $_REQUEST['location_location1'] : '';

            $location_rslt = $all_post_ids;

            if (isset($_REQUEST['location']) && $_REQUEST['location'] != '') {
                $location_condition_arr = array(
                    'relation' => 'OR',
                );

                $location_condition_arr[] = array(
                    'key' => 'jobsearch_field_location_address',
                    'value' => isset($_REQUEST['location']) ? str_replace('-', ' ', $_REQUEST['location']) : '',
                    'compare' => 'LIKE',
                );
                $location_condition_arr[] = array(
                    'key' => 'jobsearch_field_location_location1',
                    'value' => isset($_REQUEST['location']) ? str_replace(' ', '-', $_REQUEST['location']) : '',
                    'compare' => 'LIKE',
                );
                $location_condition_arr[] = array(
                    'key' => 'jobsearch_field_location_location2',
                    'value' => isset($_REQUEST['location']) ? str_replace(' ', '-', $_REQUEST['location']) : '',
                    'compare' => 'LIKE',
                );
                $location_condition_arr[] = array(
                    'key' => 'jobsearch_field_location_location3',
                    'value' => isset($_REQUEST['location']) ? str_replace(' ', '-', $_REQUEST['location']) : '',
                    'compare' => 'LIKE',
                );
                $location_condition_arr[] = array(
                    'key' => 'jobsearch_field_location_location4',
                    'value' => isset($_REQUEST['location']) ? str_replace(' ', '-', $_REQUEST['location']) : '',
                    'compare' => 'LIKE',
                );

                //$element_filters_arr[] = $location_condition_arr;

                $args_count = array(
                    'posts_per_page' => "-1",
                    'post_type' => 'job',
                    'post_status' => 'publish',
                    'fields' => 'ids', // only load ids
                    'meta_query' => array(
                        $location_condition_arr,
                    ),
                );

                if (!empty($all_post_ids)) {
                    $args_count['post__in'] = $all_post_ids;
                }
                $location_rslt = get_posts($args_count);
                if (function_exists('icl_object_id')) {
                    $trans_able_options = $sitepress->get_setting('custom_posts_sync_option', array());
                    if (empty($location_rslt) && isset($trans_able_options['job']) && $trans_able_options['job'] == '2') {
                        $sitepress_def_lang = $sitepress->get_default_language();
                        $sitepress_curr_lang = $sitepress->get_current_language();
                        $sitepress->switch_lang($sitepress_def_lang, true);

                        $location = isset($_REQUEST['location']) ? $_REQUEST['location'] : '';
                        if ($location != '') {
                            $loc_taxnomy = get_term_by('slug', $location, 'job-location');
                            if (is_object($loc_taxnomy) && isset($loc_taxnomy->slug)) {
                                $args_count['meta_query'][0][0]['value'] = $loc_taxnomy->slug;
                                $args_count['meta_query'][0][1]['value'] = $loc_taxnomy->slug;
                                $args_count['meta_query'][0][2]['value'] = $loc_taxnomy->slug;
                                $args_count['meta_query'][0][3]['value'] = $loc_taxnomy->slug;
                                $args_count['meta_query'][0][4]['value'] = $loc_taxnomy->slug;
                            }
                        }

                        $location_query = new WP_Query($args_count);
                        wp_reset_postdata();
                        $location_rslt = $location_query->posts;

                        $sitepress->switch_lang($sitepress_curr_lang, true);
                    }
                }
                if (empty($location_rslt)) {
                    $location_rslt = array(0);
                }
            } else if (isset($_REQUEST['location_location1']) || isset($_REQUEST['location_location2']) || isset($_REQUEST['location_location3']) || isset($_REQUEST['location_location4'])) {

                $location_condition_arr = array(
                    'relation' => 'AND',
                );
                if (isset($_REQUEST['location_location1']) && $_REQUEST['location_location1'] != '' && isset($_REQUEST['location_location2']) && $_REQUEST['location_location2'] == 'other-cities') {
                    $location_condition_arr[] = array(
                        'key' => 'jobsearch_field_location_location1',
                        'value' => isset($_REQUEST['location_location1']) ? $_REQUEST['location_location1'] : '',
                        'compare' => '!=',
                    );
                } else {
                    if (isset($_REQUEST['location_location1']) && $_REQUEST['location_location1'] != '') {
                        $location_condition_arr[] = array(
                            'key' => 'jobsearch_field_location_location1',
                            'value' => isset($_REQUEST['location_location1']) ? $_REQUEST['location_location1'] : '',
                            'compare' => 'LIKE',
                        );
                    }
                    if (isset($_REQUEST['location_location2']) && $_REQUEST['location_location2'] != '') {
                        $location_condition_arr[] = array(
                            'key' => 'jobsearch_field_location_location2',
                            'value' => isset($_REQUEST['location_location2']) ? $_REQUEST['location_location2'] : '',
                            'compare' => 'LIKE',
                        );
                    }
                    if (isset($_REQUEST['location_location3']) && $_REQUEST['location_location3'] != '') {
                        $location_condition_arr[] = array(
                            'key' => 'jobsearch_field_location_location3',
                            'value' => isset($_REQUEST['location_location3']) ? $_REQUEST['location_location3'] : '',
                            'compare' => 'LIKE',
                        );
                    }
                    if (isset($_REQUEST['location_location4']) && $_REQUEST['location_location4'] != '') {
                        $location_condition_arr[] = array(
                            'key' => 'jobsearch_field_location_location4',
                            'value' => isset($_REQUEST['location_location4']) ? $_REQUEST['location_location4'] : '',
                            'compare' => 'LIKE',
                        );
                    }
                }

                //$element_filters_arr[] = $location_condition_arr;

                $args_count = array(
                    'posts_per_page' => "-1",
                    'post_type' => 'job',
                    'post_status' => 'publish',
                    'fields' => 'ids', // only load ids
                    'meta_query' => array(
                        $location_condition_arr,
                    ),
                );

                if (!empty($all_post_ids)) {
                    $args_count['post__in'] = $all_post_ids;
                }
                $location_rslt = get_posts($args_count);
                if (function_exists('icl_object_id')) {
                    $trans_able_options = $sitepress->get_setting('custom_posts_sync_option', array());
                    if (empty($location_rslt) && isset($trans_able_options['job']) && $trans_able_options['job'] == '2') {
                        $sitepress_def_lang = $sitepress->get_default_language();
                        $sitepress_curr_lang = $sitepress->get_current_language();
                        $sitepress->switch_lang($sitepress_def_lang, true);

                        $location_query = new WP_Query($args_count);
                        wp_reset_postdata();
                        $location_rslt = $location_query->posts;

                        $sitepress->switch_lang($sitepress_curr_lang, true);
                    }
                }
                if (empty($location_rslt)) {
                    $location_rslt = array(0);
                }
                //print_r($location_rslt);
            }
            if ($radius > 0) {
                return $all_post_ids;
            }
            return $location_rslt;
        }

        public function job_geolocation_filter($location_slug, $all_post_ids, $radius) {
            global $jobsearch_plugin_options;
            $distance_symbol = isset($jobsearch_plugin_options['jobsearch_distance_measure_by']) ? $jobsearch_plugin_options['jobsearch_distance_measure_by'] : 'km';
            if ($distance_symbol == 'km') {
                $radius = $radius / 1.60934; // 1.60934 == 1 Mile
            }
            if (isset($location_slug) && $location_slug != '') {
                $Jobsearch_Locations = new Jobsearch_Locations();
                $location_response = $Jobsearch_Locations->jobsearch_get_geolocation_latlng_callback($location_slug);
                $lat = isset($location_response->lat) ? $location_response->lat : '';
                $lng = isset($location_response->lng) ? $location_response->lng : '';
                $radiusCheck = new RadiusCheck($lat, $lng, $radius);
                $minLat = $radiusCheck->MinLatitude();
                $maxLat = $radiusCheck->MaxLatitude();
                $minLong = $radiusCheck->MinLongitude();
                $maxLong = $radiusCheck->MaxLongitude();
                $jobsearch_compare_type = 'CHAR';
                if ($radius > 0) {
                    $jobsearch_compare_type = 'DECIMAL(10,6)';
                }
                $location_condition_arr = array(
                    'relation' => 'OR',
                    array(
                        'key' => 'jobsearch_field_location_lat',
                        'value' => array($minLat, $maxLat),
                        'compare' => 'BETWEEN',
                        'type' => $jobsearch_compare_type
                    ),
                    array(
                        'key' => 'jobsearch_field_location_lng',
                        'value' => array($minLong, $maxLong),
                        'compare' => 'BETWEEN',
                        'type' => $jobsearch_compare_type
                    ),
                    array(
                        'key' => 'jobsearch_field_location_location1',
                        'value' => $location_slug,
                        'compare' => 'LIKE',
                    ),
                    array(
                        'key' => 'jobsearch_field_location_location1',
                        'value' => sanitize_title($location_slug),
                        'compare' => 'LIKE',
                    ),
                );
                $args_count = array(
                    'posts_per_page' => "-1",
                    'post_type' => 'job',
                    'post_status' => 'publish',
                    'fields' => 'ids', // only load ids
                    'meta_query' => array(
                        $location_condition_arr,
                    ),
                );
                if (!empty($all_post_ids)) {
                    $args_count['post__in'] = $all_post_ids;
                }
                $location_rslt = get_posts($args_count);
                return $location_rslt;
                $rslt = '';
            }
        }

        public function toArray($obj) {
            if (is_object($obj)) {
                $obj = (array) $obj;
            }
            if (is_array($obj)) {
                $new = array();
                foreach ($obj as $key => $val) {
                    $new[$key] = $this->toArray($val);
                }
            } else {
                $new = $obj;
            }

            return $new;
        }

        public function jobsearch_job_pagination_callback($args) {
            global $jobsearch_form_fields;
            $total_posts = '';
            $job_per_page = '5';
            $paging_var = 'job_page';
            $show_pagination = 'yes';
            $job_short_counter = '';
            extract($args);
            $view_type = '';

            $ajax_filter = ( isset($_REQUEST['ajax_filter']) || isset($_REQUEST['search_type']) ) ? 'true' : 'false';

            if ($show_pagination <> 'yes') {
                return;
            } else if ($total_posts <= $job_per_page) {
                return;
            } else {
                if (!isset($_REQUEST[$paging_var])) {
                    $_REQUEST[$paging_var] = '';
                }
                $html = '';
                $dot_pre = '';
                $dot_more = '';
                $total_page = 0;
                if ($total_posts > 0 && $job_per_page > 0) {
                    $total_page = ceil($total_posts / $job_per_page);
                }
                $paged_id = 1;
                if (isset($_REQUEST[$paging_var]) && $_REQUEST[$paging_var] != '') {
                    $paged_id = $_REQUEST[$paging_var];
                }
                $loop_start = $paged_id - 2;

                $loop_end = $paged_id + 2;

                if ($paged_id < 3) {

                    $loop_start = 1;

                    if ($total_page < 5)
                        $loop_end = $total_page;
                    else
                        $loop_end = 5;
                }
                else if ($paged_id >= $total_page - 1) {

                    if ($total_page < 5)
                        $loop_start = 1;
                    else
                        $loop_start = $total_page - 4;

                    $loop_end = $total_page;
                }
                $html .= $jobsearch_form_fields->input_hidden_field(
                        array(
                            'cus_id' => $paging_var . '-' . $job_short_counter,
                            'cus_name' => $paging_var,
                            'std' => '',
                        )
                );
                $html .= '<div class="jobsearch-pagination-blog"><ul class="page-numbers">';
                if ($paged_id > 1) {
                    $html .= '<li>'
                            . '<a class="prev page-numbers" onclick="jobsearch_job_pagenation_ajax(\'' . $paging_var . '\', \'' . ($paged_id - 1) . '\', \'' . ($job_short_counter) . '\', \'' . ($ajax_filter) . '\', \'' . ($view_type) . '\');" href="javascript:void(0);">';
                    $html .= '<span><i class="jobsearch-icon jobsearch-arrows4"><i></span>'
                            . '</a>'
                            . '</li>';
                } else {
                    
                }

                if ($paged_id > 3 && $total_page > 5) {
                    $html .= '<li><a class="page-numbers" onclick="jobsearch_job_pagenation_ajax(\'' . $paging_var . '\', \'' . (1) . '\', \'' . ($job_short_counter) . '\', \'' . ($ajax_filter) . '\', \'' . ($view_type) . '\');" href="javascript:void(0);">';
                    $html .= '1</a></li>';
                }
                if ($paged_id > 4 && $total_page > 6) {
                    $html .= '<li class="disabled"><span><a>. . .</a></span><li>';
                }

                if ($total_page > 1) {

                    for ($i = $loop_start; $i <= $loop_end; $i ++) {

                        if ($i <> $paged_id) {

                            $html .= '<li><a class="page-numbers" onclick="jobsearch_job_pagenation_ajax(\'' . $paging_var . '\', \'' . ($i) . '\', \'' . ($job_short_counter) . '\', \'' . ($ajax_filter) . '\', \'' . ($view_type) . '\');" href="javascript:void(0);">';
                            $html .= $i . '</a></li>';
                        } else {
                            $html .= '<li><span class="page-numbers current">' . $i . '</span></li>';
                        }
                    }
                }
                if ($loop_end <> $total_page && $loop_end <> $total_page - 1) {
                    $html .= '<li class="no-border"><a>. . .</a></li>';
                }
                if ($loop_end <> $total_page) {
                    $html .= '<li><a class="page-numbers" onclick="jobsearch_job_pagenation_ajax(\'' . $paging_var . '\', \'' . ($total_page) . '\', \'' . ($job_short_counter) . '\', \'' . ($ajax_filter) . '\', \'' . ($view_type) . '\');" href="javascript:void(0);">';
                    $html .= $total_page . '</a></li>';
                }
                if ($total_posts > 0 && $job_per_page > 0 && $paged_id < ($total_posts / $job_per_page)) {
                    $html .= '<li>'
                            . '<a class="next page-numbers" onclick="jobsearch_job_pagenation_ajax(\'' . $paging_var . '\', \'' . ($paged_id + 1) . '\', \'' . ($job_short_counter) . '\', \'' . ($ajax_filter) . '\', \'' . ($view_type) . '\');" href="javascript:void(0);">';
                    $html .= '<span><i class="jobsearch-icon jobsearch-arrows4"></i></span>'
                            . '</a>'
                            . '</li>';
                } else {
                    
                }
                $html .= "</ul></div>";
                echo force_balance_tags($html);
            }
        }

        public function jobsearch_job_filter_categories($job_type, $category_request_val) {
            $jobsearch_job_type_category_array = array();
            $parent_cate_array = array();
            if ($category_request_val != '') {
                $category_request_val_arr = explode(",", $category_request_val);
                $category_request_val = isset($category_request_val_arr[0]) && $category_request_val_arr[0] != '' ? $category_request_val_arr[0] : '';
                $single_term = get_term_by('slug', $category_request_val, 'sector');
                $single_term_id = isset($single_term->term_id) && $single_term->term_id != '' ? $single_term->term_id : '0';
                $parent_cate_array = $this->jobsearch_job_parent_categories($single_term_id);
            }
            $jobsearch_job_type_category_array = $this->jobsearch_job_categories_list($job_type, $parent_cate_array);
            return $jobsearch_job_type_category_array;
        }

        public function jobsearch_job_parent_categories($category_id) {
            $parent_cate_array = array();
            $category_obj = get_term_by('id', $category_id, 'sector');
            if (isset($category_obj->parent) && $category_obj->parent != '0') {
                $parent_cate_array .= $this->jobsearch_job_parent_categories($category_obj->parent);
            }
            $parent_cate_array .= isset($category_obj->slug) ? $category_obj->slug . ',' : '';
            return $parent_cate_array;
        }

        public function jobsearch_job_categories_list($job_type, $parent_cate_string) {
            $cate_list_found = 0;
            $jobsearch_job_type_category_array = array();
            if ($parent_cate_string != '') {
                $category_request_val_arr = explode(",", $parent_cate_string);
                $count_arr = sizeof($category_request_val_arr);
                while ($count_arr >= 0) {
                    if (isset($category_request_val_arr[$count_arr]) && $category_request_val_arr[$count_arr] != '') {
                        if ($cate_list_found == 0) {
                            $single_term = get_term_by('slug', $category_request_val_arr[$count_arr], 'sector');
                            $single_term_id = isset($single_term->term_id) && $single_term->term_id != '' ? $single_term->term_id : '0';
                            $jobsearch_category_array = get_terms('sector', array(
                                'hide_empty' => false,
                                'parent' => $single_term_id,
                                    )
                            );
                            if (is_array($jobsearch_category_array) && sizeof($jobsearch_category_array) > 0) {
                                foreach ($jobsearch_category_array as $dir_tag) {
                                    $jobsearch_job_type_category_array['cate_list'][] = $dir_tag->slug;
                                }
                                $cate_list_found ++;
                            }
                        }
                        if ($cate_list_found > 0) {
                            $jobsearch_job_type_category_array['parent_list'][] = $category_request_val_arr[$count_arr];
                        }
                    }
                    $count_arr --;
                }
            }

            if ($cate_list_found == 0 && $job_type != '') {
                $job_type_post = get_posts(array('posts_per_page' => '1', 'post_type' => 'job-type', 'name' => "$job_type", 'post_status' => 'publish', 'fields' => 'ids'));
                $job_type_post_id = isset($job_type_post[0]) ? $job_type_post[0] : 0;
                $jobsearch_job_type_category_array['cate_list'] = get_post_meta($job_type_post_id, 'jobsearch_job_type_cats', true);
            }
            return $jobsearch_job_type_category_array;
        }

        public function jobsearch_job_body_classes($classes) {
            $classes[] = 'job-with-full-map';
            return $classes;
        }

        public function jobsearch_job_map_coords_obj($job_ids) {
            $map_cords = array();

            if (is_array($job_ids) && sizeof($job_ids) > 0) {
                foreach ($job_ids as $job_id) {
                    global $jobsearch_member_profile;

                    $Jobsearch_Locations = new Jobsearch_Locations();
                    $job_type = get_post_meta($job_id, 'jobsearch_job_type', true);
                    $job_type_obj = get_page_by_path($job_type, OBJECT, 'job-type');
                    $job_type_id = isset($job_type_obj->ID) ? $job_type_obj->ID : '';
                    $job_type_id = jobsearch_wpml_lang_page_id($job_type_id, 'job-type');
                    $job_location = $Jobsearch_Locations->get_location_by_job_id($job_id);
                    $jobsearch_job_username = get_post_meta($job_id, 'jobsearch_job_username', true);
                    $jobsearch_profile_image = $jobsearch_member_profile->member_get_profile_image($jobsearch_job_username);
                    $job_latitude = get_post_meta($job_id, 'jobsearch_field_location_lat', true);
                    $job_longitude = get_post_meta($job_id, 'jobsearch_field_location_lng', true);
                    $job_marker = get_post_meta($job_type_id, 'jobsearch_job_type_marker_image', true);

                    if ($job_marker != '') {
                        $job_marker = wp_get_attachment_url($job_marker);
                    } else {
                        $job_marker = esc_url(wp_dp::plugin_url() . 'assets/frontend/images/map-marker.png');
                    }
                    $jobsearch_job_is_urgent = jobsearch_check_promotion_status($job_id, 'urgent');
                    $jobsearch_job_type = get_post_meta($job_id, 'jobsearch_job_type', true);
                    $jobsearch_user_reviews = get_post_meta($job_type_id, 'jobsearch_user_reviews', true);

                    // end checking review on in job type 

                    if (has_post_thumbnail()) {
                        $img_atr = array('class' => 'img-map-info');
                        $job_info_img = get_the_post_thumbnail($job_id, 'jobsearch_cs_media_5', $img_atr);
                    } else {
                        $no_image_url = esc_url(wp_dp::plugin_url() . 'assets/frontend/images/no-image4x3.jpg');
                        $job_info_img = '<img class="img-map-info" src="' . $no_image_url . '" />';
                    }
                    $job_info_address = '';
                    if ($job_location != '') {
                        $job_info_address = '<span class="info-address">' . $job_location . '</span>';
                    }

                    ob_start();
                    $favourite_label = '';
                    $favourite_label = '';
                    $figcaption_div = true;
                    $book_mark_args = array(
                        'before_label' => $favourite_label,
                        'after_label' => $favourite_label,
                        'before_icon' => '<i class="icon-heart-o"></i>',
                        'after_icon' => '<i class="icon-heart5"></i>',
                    );
                    do_action('jobsearch_favourites_frontend_button', $job_id, $book_mark_args, $figcaption_div);
                    $list_favourite = ob_get_clean();

                    $job_featured = '';
                    if ($jobsearch_job_is_urgent == 'on') {
                        $job_featured .= '
                        <div class="featured-job">
                            <span class="bgcolor">' . esc_html__('jobsearch_jobs_urgent') . '</span>
                        </div>';
                    }

                    $job_member = $jobsearch_job_username != '' && get_the_title($jobsearch_job_username) != '' ? '<span class="info-member">' . sprintf(esc_html__('jobsearch_jobs_members'), get_the_title($jobsearch_job_username)) . '</span>' : '';

                    $ratings_data = array(
                        'overall_rating' => 0.0,
                        'count' => 0,
                    );
                    $ratings_data = apply_filters('reviews_ratings_data', $ratings_data, $job_id);

                    if ($job_latitude != '' && $job_longitude != '') {
                        $map_cords[] = array(
                            'lat' => $job_latitude,
                            'long' => $job_longitude,
                            'id' => $job_id,
                            'title' => get_the_title($job_id),
                            'link' => get_permalink($job_id),
                            'img' => $job_info_img,
                            'address' => $job_info_address,
                            'favourite' => $list_favourite,
                            'featured' => $job_featured,
                            'member' => $job_member,
                            'marker' => $job_marker,
                        );
                    }
                }
            }
            return $map_cords;
        }

        public function jobsearch_draw_search_element_callback($draw_on_map_url = '') {
            if ($draw_on_map_url != '') {
                ?>
                <div class="email-me-top">
                    <a href="<?php echo esc_url($draw_on_map_url); ?>" class="email-alert-btn draw-your-search-btn"><?php echo esc_html__('jobsearch_jobs_draw_search'); ?></a>
                </div>
                <?php
            }
        }

        public function wp_jobsearch_duplicate_post_as_draft() {
            set_time_limit(0);
            global $wpdb;
            if (!( isset($_REQUEST['post']) && ( isset($_REQUEST['action']) && 'wp_jobsearch_duplicate_post_as_draft' == $_REQUEST['action'] ) )) {
                wp_die('No post to duplicate has been supplied!');
            }
            echo 'wp_jobsearch_duplicate_post_as_draft 3';
            $count = 1;
            /*
             * get the original post id
             */
            $post_id = (isset($_GET['post']) ? absint($_GET['post']) : absint($_POST['post']) );
            /*
             * and all the original post data then
             */
            $post = get_post($post_id);

            /*
             * if you don't want current user to be the new post author,
             * then change next couple of lines to this: $new_post_author = $post->post_author;
             */
            //$current_user = wp_get_current_user();
            //$new_post_author = $current_user->ID;

            /*
             * if post data exists, create the post duplicate
             */
            if (isset($post) && $post != null) {

                /*
                 * new post data array
                 */
                $args = array(
                    'post_content' => $post->post_content,
                    'post_name' => $post->post_name,
                    'post_status' => 'publish',
                    'post_title' => 'Dupplicate - ' . $count . $post->post_title,
                    'post_type' => $post->post_type,
                );

                /*
                 * insert the post by wp_insert_post() function
                 */
                $new_post_id = wp_insert_post($args);

                /*
                 * duplicate all post meta just in two SQL queries
                 */
                $post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id");
                if (count($post_meta_infos) != 0) {
                    $sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
                    foreach ($post_meta_infos as $meta_info) {
                        $meta_key = $meta_info->meta_key;
                        $meta_value = addslashes($meta_info->meta_value);
                        $sql_query_sel[] = "SELECT $new_post_id, '$meta_key', '$meta_value'";
                    }
                    $sql_query .= implode(" UNION ALL ", $sql_query_sel);
                    $wpdb->query($sql_query);
                }

                echo 'added ';
                /*
                 * finally, redirect to the edit post screen for the new draft
                 */
                exit;
            } else {
                wp_die('Post creation failed, could not find original post: ' . $post_id);
            }
        }

        public function jobsearch_all_jobs_by_s($s, $record = '-1') {

            $default_date_time_formate = 'd-m-Y H:i:s';
            // posted date check
            $element_filter_arr[] = array(
                'key' => 'jobsearch_field_job_publish_date',
                'value' => strtotime(current_time($default_date_time_formate)),
                'compare' => '<=',
            );

            $element_filter_arr[] = array(
                'key' => 'jobsearch_field_job_expiry_date',
                'value' => strtotime(current_time($default_date_time_formate)),
                'compare' => '>=',
            );

            $element_filter_arr[] = array(
                'key' => 'jobsearch_field_job_status',
                'value' => 'approved',
                'compare' => '=',
            );
            $element_filter_arr[] = array(
                'key' => 'jobsearch_job_employer_status',
                'value' => 'approved',
                'compare' => '=',
            );
            if (function_exists('jobsearch_visibility_query_args')) {
                $element_filter_arr = jobsearch_visibility_query_args($element_filter_arr);
            }
            $args = array(
                'posts_per_page' => $record,
                'post_type' => array('job', 'job_type'),
                'post_status' => 'publish',
                's' => $s,
                'fields' => 'ids', // only load ids
                'meta_query' => array(
                    $element_filter_arr,
                ),
            );

            $job_loop_obj = jobsearch_get_cached_obj('job_autocomplete_result_cached_loop_obj', $args, 12, false, 'wp_query');
            return $job_loop_obj;
        }

        public function jobsearch_search_keyword_callback($html, $page_url = '') {
            global $jobsearch_plugin_options, $sitepress;

            $lang_code = '';
            if (function_exists('icl_object_id')) {
                $lang_code = $sitepress->get_current_language();
            }

            $qrystr = http_build_query($_REQUEST);
            $remove_item_list = array(
                'job_arg',
                'action',
                'job_page',
            );
            foreach ($remove_item_list as $remove_item_list_single) {
                $qrystr = jobsearch_remove_qrystr_extra_var($qrystr, $remove_item_list_single, true);
            }
            $visibility = '';
            if (isset($qrystr) && $qrystr == '') {
                $visibility = 'style="display: none;"';
            }
            $job_salary_types = isset($jobsearch_plugin_options['job-salary-types']) ? $jobsearch_plugin_options['job-salary-types'] : '';
            ob_start();
            $keyword_html = '';
            if (isset($qrystr) && $qrystr != '') {

                $to_trans_array = jobsearch_keywords_to_translate_arr();
                //get all query string
                if (isset($qrystr)) {
                    $qrystr_arr = getMultipleParameters($qrystr);
                    foreach ($qrystr_arr as $qry_var => $qry_val) {
                        if ('job_page' == $qry_var || 'page_id' == $qry_var || 'lang' == $qry_var || 'per-page' == $qry_var || 'action' == $qry_var || 'ajax_filter' == $qry_var || 'advanced_search' == $qry_var || 'job_arg' == $qry_var || 'action' == $qry_var || 'alert-frequency' == $qry_var || 'alerts-name' == $qry_var || 'loc_polygon' == $qry_var || 'alerts-email' == $qry_var || 'loc_polygon_path' == $qry_var)
                            continue;
                        if ('job_salary_type' == $qry_var && !empty($job_salary_types)) {
                            if ($qry_val != '') {
                                $salary_type_val_str = '';
                                $salary_type_val = isset($qry_val[0]) ? $qry_val[0] : '';
                                $slar_type_count = 1;
                                foreach ($job_salary_types as $job_salary_typ) {
                                    $job_salary_typ = apply_filters('wpml_translate_single_string', $job_salary_typ, 'JobSearch Options', 'Salary Type - ' . $job_salary_typ, $lang_code);
                                    if ($salary_type_val == 'type_' . $slar_type_count) {
                                        $salary_type_val_str = $job_salary_typ;
                                    }
                                    $slar_type_count++;
                                }
                                $keyword_html .= '<li>';
                                $keyword_html .= '<a href="' . remove_query_arg('lang', jobsearch_remove_qrystr_extra_var($qrystr, $qry_var)) . '" title="' . ucwords(str_replace(array("+", "-", "_"), " ", $qry_var)) . '">' . $salary_type_val_str . ' <i class="fa fa-window-close"></i></a>';
                                $keyword_html .= '</li>';
                            }
                        } else {
                            if ($qry_val != '') {
                                if (!is_array($qry_val)) {
                                    if (strpos($qry_val, ',') !== FALSE) {
                                        $qry_val = explode(",", $qry_val);
                                    }
                                }
                                if (is_array($qry_val)) {
                                    foreach ($qry_val as $qry_val_var => $qry_val_value) {
                                        if ($qry_val_value != '') {
                                            $qry_val_value = urldecode($qry_val_value);
                                            $keyword_html .= '<li>';
                                            $qrystr1 = str_replace("&" . $qry_var . '[]=' . $qry_val_value, "", $qrystr);
                                            $qrystr1 = str_replace("&" . $qry_var . '=' . $qry_val_value, "", $qrystr);
                                            $qry_val_str = ucwords(str_replace(array("+", "-"), " ", $qry_val_value));
                                            if (!empty($to_trans_array) && isset($to_trans_array[$qry_val_value])) {
                                                $qry_val_str = $to_trans_array[$qry_val_value];
                                            }
                                            $keyword_html .= '<a href="' . remove_query_arg('lang', jobsearch_remove_qrystr_extra_var($qrystr1, $qry_var)) . '" title="' . ucwords(str_replace(array("+", "-", "_"), " ", str_replace('jobsearch_field_', '', $qry_var))) . '">' . $qry_val_str . ' <i class="fa fa-window-close"></i></a>';
                                            $keyword_html .= '</li>';
                                        }
                                    }
                                } else {
                                    $qry_val_str = ucwords(str_replace(array("+", "-"), " ", $qry_val));
                                    if (!empty($to_trans_array) && isset($to_trans_array[$qry_val])) {
                                        $qry_val_str = $to_trans_array[$qry_val];
                                    }
                                    $keyword_html .= '<li>';
                                    $keyword_html .= '<a href="' . remove_query_arg('lang', jobsearch_remove_qrystr_extra_var($qrystr, $qry_var)) . '" title="' . ucwords(str_replace(array("+", "-", "_"), " ", str_replace('jobsearch_field_', '', $qry_var))) . '">' . $qry_val_str . ' <i class="fa fa-window-close"></i></a>';
                                    $keyword_html .= '</li>';
                                }
                            }
                        }
                    }
                }
            }
            if ($keyword_html != '') {
                $page_url = remove_query_arg(array('lang'), $page_url);
                ?>
                <div class="jobsearch-filterable"  <?php echo ($visibility); ?>>
                    <ul class="filtration-tags">
                        <?php
                        echo force_balance_tags($keyword_html);
                        ?>
                    </ul>
                    <a class="clear-tags" href="<?php echo esc_url($page_url); ?>" title="<?php esc_html_e('Clear all', 'wp-jobsearch') ?>"><?php esc_html_e('Clear all', 'wp-jobsearch') ?></a>
                </div>
                <?php
            }
            $html .= ob_get_clean();
            return $html;
        }

    }

    global $jobsearch_shortcode_jobs_frontend;
    $jobsearch_shortcode_jobs_frontend = new Jobsearch_Shortcode_Jobs_Frontend();
} 
