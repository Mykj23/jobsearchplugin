<?php

/**
 * @Manage Columns
 * @return
 *
 */
if (!class_exists('post_type_candidate')) {

    class post_type_candidate {

        // The Constructor
        public function __construct() {
            // Adding columns
            add_filter('manage_candidate_posts_columns', array($this, 'jobsearch_candidate_columns_add'));
            add_action('manage_candidate_posts_custom_column', array($this, 'jobsearch_candidate_columns'), 10, 2);
            add_filter('list_table_primary_column', array($this, 'jobsearch_primary_column'), 10, 2);
            add_action('init', array($this, 'jobsearch_candidate_register')); // post type register
            add_action('init', array($this, 'jobsearch_candidate_sector'), 0);
            add_filter('post_row_actions', array($this, 'jobsearch_candidate_row_actions'));
            add_filter('manage_edit-candidate_sortable_columns', array($this, 'jobsearch_candidate_sortable_columns'));
            add_filter('request', array($this, 'jobsearch_candidate_sort_columns'));

            add_action('admin_head', array($this, 'my_admin_custom_styles'));
            //add_action('init', array($this, 'jobsearch_candidate_tags'), 0);
            //
            add_action('views_edit-candidate', array($this, 'modified_views_so'), 0);
            add_filter('parse_query', array($this, 'candidates_query_filter'), 11, 1);
            add_filter('bulk_actions-edit-candidate', array($this, 'custom_job_filters'));
            add_action('handle_bulk_actions-edit-candidate', array($this, 'jobs_bulk_actions_handle'), 10, 3);
        }

        function my_admin_custom_styles() {
            $output_css = '<style type="text/css"> 
                .column-candidate_title { width:500px !important; overflow:hidden }
                .column-featured { width:10px !important; overflow:hidden }
                .column-filled { width:30px !important; overflow:hidden }
                .column-status { width:30px !important; overflow:hidden }
                .column-action { text-align:right !important; width:150px !important; overflow:hidden }
            </style>';
            echo $output_css;
        }

        public function jobsearch_candidate_register() {
            $labels = array(
                'name' => _x('Candidates', 'post type general name', 'wp-jobsearch'),
                'singular_name' => _x('Candidate', 'post type singular name', 'wp-jobsearch'),
                'menu_name' => _x('Candidates', 'admin menu', 'wp-jobsearch'),
                'name_admin_bar' => _x('Candidate', 'add new on admin bar', 'wp-jobsearch'),
                'add_new' => _x('Add New', 'candidate', 'wp-jobsearch'),
                'add_new_item' => __('Add New Candidate', 'wp-jobsearch'),
                'new_item' => __('New Candidate', 'wp-jobsearch'),
                'edit_item' => __('Edit Candidate', 'wp-jobsearch'),
                'view_item' => __('View Candidate', 'wp-jobsearch'),
                'all_items' => __('All Candidates', 'wp-jobsearch'),
                'search_items' => __('Search Candidates', 'wp-jobsearch'),
                'parent_item_colon' => __('Parent Candidates:', 'wp-jobsearch'),
                'not_found' => __('No candidates found.', 'wp-jobsearch'),
                'not_found_in_trash' => __('No candidates found in Trash.', 'wp-jobsearch')
            );

            $args = array(
                'labels' => $labels,
                'description' => __('Description.', 'wp-jobsearch'),
                'public' => true,
                'publicly_queryable' => true,
                'show_ui' => true,
                'show_in_menu' => true,
                'query_var' => true,
                'rewrite' => array('slug' => 'candidate'),
                'capability_type' => 'post',
                'has_archive' => false,
                'exclude_from_search' => true,
                'hierarchical' => false,
                'menu_position' => 27,
                'supports' => array('title', 'editor', 'excerpt', 'thumbnail')
            );

            register_post_type('candidate', $args);
        }

        public function jobsearch_candidate_row_actions($actions) {
            if ('candidate' == get_post_type()) {
                return array();
            }
            return $actions;
        }
        
        public function custom_job_filters($actions) {
            if (is_array($actions) && isset($actions['trash'])) {
                $actions['approved'] = esc_html__('Approved', 'wp-jobsearch');
                $actions['pending'] = esc_html__('Pending', 'wp-jobsearch');
            }
            return $actions;
        }

        function jobs_bulk_actions_handle($redirect_to, $doaction, $post_ids) {
            if ($doaction == 'approved' || $doaction == 'pending') {
                if (!empty($post_ids)) {
                    foreach ($post_ids as $candidate_id) {
                        $do_save = $doaction == 'approved' ? 'on' : '';
                        update_post_meta($candidate_id, 'jobsearch_field_candidate_approved', $do_save);
                    }
                }
            }
            return $redirect_to;
        }

        public function candidates_query_filter($query) {
            global $pagenow;

            $custom_filter_arr = array();
            if (is_admin() && $pagenow == 'edit.php' && isset($_GET['post_type']) && $_GET['post_type'] == 'candidate' && isset($_GET['candidate_status']) && $_GET['candidate_status'] != '') {
                if ($_GET['candidate_status'] == 'approved') {
                    $custom_filter_arr[] = array(
                        'key' => 'jobsearch_field_candidate_approved',
                        'value' => 'on',
                        'compare' => '=',
                    );
                } else {
                    $custom_filter_arr[] = array(
                        'key' => 'jobsearch_field_candidate_approved',
                        'value' => 'on',
                        'compare' => '!=',
                    );
                }
            }
            if (!empty($custom_filter_arr)) {
                $query->set('meta_query', $custom_filter_arr);
            }
        }

        public function modified_views_so($views) {

            remove_filter('parse_query', array(&$this, 'candidates_query_filter'), 11, 1);
            $args = array(
                'post_type' => 'candidate',
                'posts_per_page' => '1',
                'post_status' => 'publish',
                'fields' => 'ids',
                'meta_query' => array(
                    array(
                        'key' => 'jobsearch_field_candidate_approved',
                        'value' => 'on',
                        'compare' => '!=',
                    ),
                ),
            );
            $jobs_query = new WP_Query($args);
            $pending_jobs = $jobs_query->found_posts;
            wp_reset_postdata();

            $args = array(
                'post_type' => 'candidate',
                'posts_per_page' => '1',
                'post_status' => 'publish',
                'fields' => 'ids',
                'meta_query' => array(
                    array(
                        'key' => 'jobsearch_field_candidate_approved',
                        'value' => 'on',
                        'compare' => '=',
                    ),
                ),
            );
            $jobs_query = new WP_Query($args);
            $approve_jobs = $jobs_query->found_posts;
            wp_reset_postdata();

            $views['approved'] = '<a href="edit.php?post_type=candidate&candidate_status=approved">' . esc_html__('Approved', 'wp-jobsearch') . '</a> (' . absint($approve_jobs) . ')';
            $views['pending'] = '<a href="edit.php?post_type=candidate&candidate_status=pending">' . esc_html__('Pending', 'wp-jobsearch') . '</a> (' . absint($pending_jobs) . ')';

            return $views;
        }

        public function jobsearch_candidate_columns_add($columns) {
            global $sitepress;
            $new_columns = array();
            $new_columns['cb'] = '<input type="checkbox" />';
            $new_columns['candidate_title'] = esc_html('Candidate', 'wp-jobsearch');
            if (function_exists('icl_object_id')) {
                $languages = icl_get_languages('skip_missing=0&orderby=title');
                if ( is_array($languages) && sizeof($languages) > 0 ) {
                    $wpml_options = get_option( 'icl_sitepress_settings' );
                    $default_lang = isset($wpml_options['default_language']) ? $wpml_options['default_language'] : '';
                    $flags_html = '';
                    foreach ( $languages as $lang_code => $language ) {
                        if ($default_lang == $lang_code) {
                            continue;
                        }
                        $flag_url = ICL_PLUGIN_URL . '/res/flags/' . $lang_code . '.png';
                        $flags_html .= '<img src="' . $flag_url . '" width="18" height="12" alt="' . (isset($language['translated_name']) ? $language['translated_name'] : '') . '" title="' . (isset($language['translated_name']) ? $language['translated_name'] : '') . '" style="margin:2px">';
                    }
                    $new_columns['icl_translations'] = $flags_html;
                }
            }
            $new_columns['location'] = esc_html__('Location', 'wp-jobsearch');
            $new_columns['jobtitle'] = esc_html__('Job Title', 'wp-jobsearch');
            //$new_columns['featured'] = force_balance_tags('<strong class="jobsearch-tooltip" title="' . esc_html__('Featured', 'wp-jobsearch') . '"><i class="dashicons dashicons-star-filled"></i></strong>');
            $new_columns['status'] = force_balance_tags('<strong class="jobsearch-tooltip" title="' . esc_html__('Status', 'wp-jobsearch') . '"><i class="dashicons dashicons-clock"></i></strong>');
            $new_columns['action'] = esc_html__('Action', 'wp-jobsearch');
            //return array_merge($columns, $new_columns);
            return $new_columns;
        }

        public function jobsearch_candidate_columns($column) {
            global $post;
            switch ($column) {
                case 'candidate_title' :
                    echo '<div class="candidate_position">';

                    $src = '';
                    if (has_post_thumbnail($post->ID)) {
                        $src = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'thumbnail');
                        $src = isset($src[0]) ? $src[0] : '';
                    }
                    if ($src != '') {
                        echo '<div class="company-logo">';
                        echo '<img src="' . esc_attr($src) . '" alt="' . esc_attr(get_the_title($post->ID)) . '" />';
                        echo '</div>';
                        // Before 1.24.0, logo URLs were stored in post meta.
                    }

                    echo '<a href="' . admin_url('post.php?post=' . $post->ID . '&action=edit') . '" class="candidate_title" class="jobsearch-tooltip" title="' . sprintf(__('ID: %d', 'wp-jobsearch'), $post->ID) . '">' . ucfirst(get_the_title($post->ID)) . '</a>';

                    echo '<div class="sector-list">';
                    $candidatetype_list = get_the_term_list($post->ID, 'sector', '', ',', '');
                    if ($candidatetype_list) {
                        printf('%1$s', $candidatetype_list);
                    }
                    echo '</div>';

                    echo '</div>';
                    break;

                case 'location' :
                    $location1 = get_post_meta($post->ID, 'jobsearch_field_location_location1', true);
                    echo ucfirst(str_replace("-", " ", $location1));
                    break;
                case 'featured' :
                    $candidate_featured = get_post_meta($post->ID, 'jobsearch_field_candidate_featured', true);
                    if ($candidate_featured == 'on') {
                        echo force_balance_tags('<a href="javascript:void(0);" class="jobsearch-tooltip candidate-featured-option" data-option="un-feature" data-candidateid="' . esc_attr($post->ID) . '" title="' . esc_html__('No', 'wp-jobsearch') . '"><i class="dashicons dashicons-star-filled" aria-hidden="true"></i></a>');
                    } else {
                        echo force_balance_tags('<a href="javascript:void(0);" class="jobsearch-tooltip candidate-featured-option" data-option="featured" data-candidateid="' . esc_attr($post->ID) . '" title="' . esc_html__('Yes', 'wp-jobsearch') . '"><i class="dashicons dashicons-star-empty" aria-hidden="true"></i></a>');
                    }
                    break;
                case 'jobtitle' :
                    $jobtitle = get_post_meta($post->ID, 'jobsearch_field_candidate_jobtitle', true);
                    echo esc_html($jobtitle);
                    break;
                case "status" :
                    global $jobsearch_plugin_options;
                    $approved_color = isset($jobsearch_plugin_options['jobsearch-approved-color']) ? $jobsearch_plugin_options['jobsearch-approved-color'] : '';
                    $pending_color = isset($jobsearch_plugin_options['jobsearch-pending-color']) ? $jobsearch_plugin_options['jobsearch-pending-color'] : '';
                    $canceled_color = isset($jobsearch_plugin_options['jobsearch-canceled-color']) ? $jobsearch_plugin_options['jobsearch-canceled-color'] : '';
                    $approved_color_str = '';
                    if ($approved_color != '') {
                        $approved_color_str = 'style="color:' . $approved_color . '"';
                    }
                    $pending_color_str = '';
                    if ($pending_color != '') {
                        $pending_color_str = 'style="color:' . $pending_color . '"';
                    }
                    $canceled_color_str = '';
                    if ($canceled_color != '') {
                        $canceled_color_str = 'style="color:' . $canceled_color . '"';
                    }

                    $candidate_status = get_post_meta($post->ID, 'jobsearch_field_candidate_approved', true);
                    if ($candidate_status == 'on') {
                        echo force_balance_tags('<a href="javascript:void(0);" class="jobsearch-tooltip" title="' . esc_html__('Approved', 'wp-jobsearch') . '"><i ' . $approved_color_str . ' class="dashicons dashicons-yes" aria-hidden="true"></i></a>');
                    } else {
                        echo force_balance_tags('<a href="javascript:void(0);" class="jobsearch-tooltip" title="' . esc_html__('Pending', 'wp-jobsearch') . '"><i ' . $pending_color_str . ' class="dashicons dashicons-clock fa-spin fa-lg" aria-hidden="true"></i></a>');
                    }
                    break;
                case 'action' :
                    echo '<div class="actions">';

                    if ($post->post_status !== 'trash') {
                        if (current_user_can('read_post', $post->ID)) {
                            $admin_actions['view'] = array(
                                'action' => 'view',
                                'name' => __('View', 'wp-jobsearch'),
                                'icon' => '<i class="dashicons dashicons-visibility" aria-hidden="true"></i>',
                                'url' => get_permalink($post->ID)
                            );
                        }
                        if (current_user_can('edit_post', $post->ID)) {
                            $admin_actions['edit'] = array(
                                'action' => 'edit',
                                'name' => __('Edit', 'wp-jobsearch'),
                                'icon' => '<i class="dashicons dashicons-edit" aria-hidden="true"></i>',
                                'url' => get_edit_post_link($post->ID)
                            );
                        }
                        if (current_user_can('delete_post', $post->ID)) {
                            $admin_actions['delete'] = array(
                                'action' => 'delete',
                                'name' => __('Delete', 'wp-jobsearch'),
                                'icon' => '<i class="dashicons dashicons-trash" aria-hidden="true"></i>',
                                'url' => get_delete_post_link($post->ID)
                            );
                        }
                    }

                    if (isset($admin_actions) && !empty($admin_actions)) {
                        foreach ($admin_actions as $action) {
                            if (is_array($action)) {
                                printf('<a class="button button-icon jobsearch-tooltip" href="%2$s" data-tip="%3$s" title="%4$s">%5$s</a>', $action['action'], esc_url($action['url']), esc_attr($action['name']), esc_html($action['name']), force_balance_tags($action['icon']));
                            } else {
                                echo str_replace('class="', 'class="button ', $action);
                            }
                        }
                    }

                    echo '</div>';
                    break;
            }
        }

        public function jobsearch_primary_column($column, $screen) {
            if ('edit-candidate' === $screen) {
                $column = 'candidate_title';
            }
            return $column;
        }

        public function jobsearch_candidate_sortable_columns($columns) {
            $custom = array(
                'jobtitle' => 'jobtitle',
                'candidate_title' => 'title',
                'location' => 'location',
                'status' => 'status',
            );
            return wp_parse_args($custom, $columns);
        }

        public function jobsearch_candidate_sort_columns($vars) {
            if (isset($vars['orderby']) && isset($_GET['post_type']) && $_GET['post_type'] == 'candidate') {
                if ('jobtitle' === $vars['orderby']) {
                    $vars = array_merge($vars, array(
                        'meta_key' => 'jobsearch_field_candidate_jobtitle',
                        'orderby' => 'meta_value'
                    ));
                } else if ('location' === $vars['orderby']) {
                    $vars = array_merge($vars, array(
                        'meta_key' => 'jobsearch_field_location_location1',
                        'orderby' => 'meta_value'
                    ));
                } else if ('status' === $vars['orderby']) {
                    $vars = array_merge($vars, array(
                        'meta_key' => 'jobsearch_field_candidate_approved',
                        'orderby' => 'meta_value'
                    ));
                }
            }
            return $vars;
        }

        public function jobsearch_candidate_sector() {
            // Add new taxonomy, make it hierarchical (like sectors)
            $labels = array(
                'name' => _x('Sectors', 'taxonomy general name', 'wp-jobsearch'),
                'singular_name' => _x('Sector', 'taxonomy singular name', 'wp-jobsearch'),
                'search_items' => __('Search Sectors', 'wp-jobsearch'),
                'all_items' => __('All Sectors', 'wp-jobsearch'),
                'parent_item' => __('Parent Sector', 'wp-jobsearch'),
                'parent_item_colon' => __('Parent Sector:', 'wp-jobsearch'),
                'edit_item' => __('Edit Sector', 'wp-jobsearch'),
                'update_item' => __('Update Sector', 'wp-jobsearch'),
                'add_new_item' => __('Add New Sector', 'wp-jobsearch'),
                'new_item_name' => __('New Sector Name', 'wp-jobsearch'),
                'menu_name' => __('Sector', 'wp-jobsearch'),
            );

            $args = array(
                'hierarchical' => true,
                'labels' => $labels,
                'show_ui' => true,
                'show_admin_column' => true,
                'query_var' => true,
                'rewrite' => array('slug' => 'sector'),
            );

            register_taxonomy('sector', array('candidate', 'candidate', 'employer'), $args);
        }

        public function jobsearch_candidate_tags() {
            // Add new taxonomy, make it hierarchical (like tags)
            $labels = array(
                'name' => _x('Tags', 'taxonomy general name', 'wp-jobsearch'),
                'singular_name' => _x('Tag', 'taxonomy singular name', 'wp-jobsearch'),
                'search_items' => __('Search Tags', 'wp-jobsearch'),
                'all_items' => __('All Tags', 'wp-jobsearch'),
                'parent_item' => __('Parent Tag', 'wp-jobsearch'),
                'parent_item_colon' => __('Parent Tag:', 'wp-jobsearch'),
                'edit_item' => __('Edit Tag', 'wp-jobsearch'),
                'update_item' => __('Update Tag', 'wp-jobsearch'),
                'add_new_item' => __('Add New Tag', 'wp-jobsearch'),
                'new_item_name' => __('New Tag Name', 'wp-jobsearch'),
                'menu_name' => __('Tag', 'wp-jobsearch'),
            );

            $args = array(
                'hierarchical' => true,
                'labels' => $labels,
                'show_ui' => true,
                'show_admin_column' => true,
                'query_var' => true,
                'rewrite' => array('slug' => 'tag'),
            );

            register_taxonomy('tag', array('candidate'), $args);
        }

    }

    return new post_type_candidate();
}
