<?php

/**
 * @Manage Columns
 * @return
 *
 */
if (!class_exists('post_type_employer')) {

    class post_type_employer {

        // The Constructor
        public function __construct() {
            // Adding columns
            add_filter('manage_employer_posts_columns', array($this, 'jobsearch_employer_columns_add'));
            add_action('manage_employer_posts_custom_column', array($this, 'jobsearch_employer_columns'), 10, 2);
            add_filter('list_table_primary_column', array($this, 'jobsearch_primary_column'), 10, 2);
            add_action('init', array($this, 'jobsearch_employer_register')); // post type register
            add_filter('post_row_actions', array($this, 'jobsearch_employer_row_actions'));
            add_filter('manage_edit-employer_sortable_columns', array($this, 'jobsearch_employer_sortable_columns'));
            add_filter('request', array($this, 'jobsearch_employer_sort_columns'));
            add_action('admin_head', array($this, 'my_admin_custom_styles'));
            //add_action('init', array($this, 'jobsearch_employer_tags'), 0);
            //
            add_action('views_edit-employer', array($this, 'modified_views_so'), 0);
            add_filter('parse_query', array($this, 'employers_query_filter'), 11, 1);
            add_filter('bulk_actions-edit-employer', array($this, 'custom_job_filters'));
            add_action('handle_bulk_actions-edit-employer', array($this, 'jobs_bulk_actions_handle'), 10, 3);
        }

        function my_admin_custom_styles() {
            $output_css = '<style type="text/css"> 
                .column-employer_title { width:450px !important; overflow:hidden }
                .column-featured { width:30px !important; overflow:hidden } 
                .column-status { width:30px !important; overflow:hidden }
                .column-action { text-align:right !important; width:150px !important; overflow:hidden }
            </style>';
            echo $output_css;
        }

        public function jobsearch_employer_register() {
            $labels = array(
                'name' => _x('Employers', 'post type general name', 'wp-jobsearch'),
                'singular_name' => _x('Employer', 'post type singular name', 'wp-jobsearch'),
                'menu_name' => _x('Employers', 'admin menu', 'wp-jobsearch'),
                'name_admin_bar' => _x('Employer', 'add new on admin bar', 'wp-jobsearch'),
                'add_new' => _x('Add New', 'employer', 'wp-jobsearch'),
                'add_new_item' => __('Add New Employer', 'wp-jobsearch'),
                'new_item' => __('New Employer', 'wp-jobsearch'),
                'edit_item' => __('Edit Employer', 'wp-jobsearch'),
                'view_item' => __('View Employer', 'wp-jobsearch'),
                'all_items' => __('All Employers', 'wp-jobsearch'),
                'search_items' => __('Search Employers', 'wp-jobsearch'),
                'parent_item_colon' => __('Parent Employers:', 'wp-jobsearch'),
                'not_found' => __('No employers found.', 'wp-jobsearch'),
                'not_found_in_trash' => __('No employers found in Trash.', 'wp-jobsearch')
            );

            $args = array(
                'labels' => $labels,
                'description' => __('Description.', 'wp-jobsearch'),
                'public' => true,
                'publicly_queryable' => true,
                'show_ui' => true,
                'show_in_menu' => true,
                'query_var' => true,
                'rewrite' => array('slug' => 'employer'),
                'capability_type' => 'post',
                'has_archive' => false,
                'exclude_from_search' => true,
                'hierarchical' => false,
                'menu_position' => 26,
                'supports' => array('title', 'editor', 'excerpt', 'thumbnail')
            );

            register_post_type('employer', $args);
        }

        public function jobsearch_employer_row_actions($actions) {
            if ('employer' == get_post_type()) {
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
                    foreach ($post_ids as $employer_id) {
                        $do_save = $doaction == 'approved' ? 'on' : '';
                        update_post_meta($employer_id, 'jobsearch_field_employer_approved', $do_save);
                        if ($doaction == 'approved') {
                            // Employer jobs status change according his/her status
                            do_action('jobsearch_employer_update_jobs_status', $employer_id);
                        }
                    }
                }
            }
            return $redirect_to;
        }

        public function employers_query_filter($query) {
            global $pagenow;

            $custom_filter_arr = array();
            if (is_admin() && $pagenow == 'edit.php' && isset($_GET['post_type']) && $_GET['post_type'] == 'employer' && isset($_GET['employer_status']) && $_GET['employer_status'] != '') {
                if ($_GET['employer_status'] == 'approved') {
                    $custom_filter_arr[] = array(
                        'key' => 'jobsearch_field_employer_approved',
                        'value' => 'on',
                        'compare' => '=',
                    );
                } else {
                    $custom_filter_arr[] = array(
                        'key' => 'jobsearch_field_employer_approved',
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

            remove_filter('parse_query', array(&$this, 'employers_query_filter'), 11, 1);
            $args = array(
                'post_type' => 'employer',
                'posts_per_page' => '1',
                'post_status' => 'publish',
                'fields' => 'ids',
                'meta_query' => array(
                    array(
                        'key' => 'jobsearch_field_employer_approved',
                        'value' => 'on',
                        'compare' => '!=',
                    ),
                ),
            );
            $jobs_query = new WP_Query($args);
            $pending_jobs = $jobs_query->found_posts;
            wp_reset_postdata();

            $args = array(
                'post_type' => 'employer',
                'posts_per_page' => '1',
                'post_status' => 'publish',
                'fields' => 'ids',
                'meta_query' => array(
                    array(
                        'key' => 'jobsearch_field_employer_approved',
                        'value' => 'on',
                        'compare' => '=',
                    ),
                ),
            );
            $jobs_query = new WP_Query($args);
            $approve_jobs = $jobs_query->found_posts;
            wp_reset_postdata();

            $views['approved'] = '<a href="edit.php?post_type=employer&employer_status=approved">' . esc_html__('Approved', 'wp-jobsearch') . '</a> (' . absint($approve_jobs) . ')';
            $views['pending'] = '<a href="edit.php?post_type=employer&employer_status=pending">' . esc_html__('Pending', 'wp-jobsearch') . '</a> (' . absint($pending_jobs) . ')';

            return $views;
        }

        public function jobsearch_employer_columns_add($columns) {
            global $sitepress;
            $new_columns = array();
            $new_columns['cb'] = '<input type="checkbox" />';
            $new_columns['employer_title'] = esc_html__('Employer', 'wp-jobsearch');
            if (function_exists('icl_object_id')) {
                $languages = icl_get_languages('skip_missing=0&orderby=title');
                if (is_array($languages) && sizeof($languages) > 0) {
                    $wpml_options = get_option('icl_sitepress_settings');
                    $default_lang = isset($wpml_options['default_language']) ? $wpml_options['default_language'] : '';
                    $flags_html = '';
                    foreach ($languages as $lang_code => $language) {
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
            //$new_columns['featured'] = force_balance_tags('<strong class="jobsearch-tooltip" title="' . esc_html__('Featured', 'wp-jobsearch') . '"><i class="dashicons dashicons-star-filled"></i></strong>');
            $new_columns['status'] = force_balance_tags('<strong class="jobsearch-tooltip" title="' . esc_html__('Status', 'wp-jobsearch') . '"><i class="dashicons dashicons-info"></i></strong>');
            $new_columns['action'] = esc_html__('Action', 'wp-jobsearch');
            //return array_merge($columns, $new_columns);
            return $new_columns;
        }

        public function jobsearch_employer_columns($column) {
            global $post;
            switch ($column) {
                case 'employer_title' :
                    echo '<div class="employer_position">';

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

                    echo '<a href="' . admin_url('post.php?post=' . $post->ID . '&action=edit') . '" class="employer_title" class="jobsearch-tooltip" title="' . sprintf(__('ID: %d', 'wp-jobsearch'), $post->ID) . '">' . ucfirst(get_the_title($post->ID)) . '</a>';

                    echo '<div class="sector-list">';
                    $employertype_list = get_the_term_list($post->ID, 'sector', '', ',', '');
                    if ($employertype_list) {
                        printf('%1$s', $employertype_list);
                    }
                    echo '</div>';

                    echo '</div>';
                    break;

                case 'location' :
                    $location1 = get_post_meta($post->ID, 'jobsearch_field_location_location1', true);
                    echo ucfirst(str_replace("-", " ", $location1));
                    break;
                case 'featured' :
                    $employer_featured = get_post_meta($post->ID, 'jobsearch_field_employer_featured', true);
                    if ($employer_featured == 'on') {
                        echo force_balance_tags('<a href="javascript:void(0);" class="jobsearch-tooltip employer-featured-option" data-option="un-feature" data-employerid="' . esc_attr($post->ID) . '" title="' . esc_html__('No', 'wp-jobsearch') . '"><i class="dashicons dashicons-star-filled" aria-hidden="true"></i></a>');
                    } else {
                        echo force_balance_tags('<a href="javascript:void(0);" class="jobsearch-tooltip employer-featured-option" data-option="featured" data-employerid="' . esc_attr($post->ID) . '" title="' . esc_html__('Yes', 'wp-jobsearch') . '"><i class="dashicons dashicons-star-empty" aria-hidden="true"></i></a>');
                    }
                    break;
                case 'status' :
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

                    $employer_status = get_post_meta($post->ID, 'jobsearch_field_employer_approved', true);
                    if ($employer_status == 'on') {
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
            if ('edit-employer' === $screen) {
                $column = 'employer_title';
            }
            return $column;
        }

        public function jobsearch_employer_sortable_columns($columns) {
            $custom = array(
                'employer_title' => 'title',
                'location' => 'location',
                'featured' => 'featured',
                'status' => 'status',
            );
            return wp_parse_args($custom, $columns);
        }

        public function jobsearch_employer_sort_columns($vars) {
            if (isset($vars['orderby']) && isset($_GET['post_type']) && $_GET['post_type'] == 'employer') {
                if ('location' === $vars['orderby']) {
                    $vars = array_merge($vars, array(
                        'meta_key' => 'jobsearch_field_location_location1',
                        'orderby' => 'meta_value'
                    ));
                } else if ('featured' === $vars['orderby']) {
                    $vars = array_merge($vars, array(
                        'meta_key' => 'jobsearch_field_employer_featured',
                        'orderby' => 'meta_value'
                    ));
                } else if ('status' === $vars['orderby']) {
                    $vars = array_merge($vars, array(
                        'meta_key' => 'jobsearch_field_employer_approved',
                        'orderby' => 'meta_value'
                    ));
                }
            }
            return $vars;
        }

        public function jobsearch_employer_tags() {
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

            register_taxonomy('tag', array('employer'), $args);
        }

    }

    return new post_type_employer();
}
