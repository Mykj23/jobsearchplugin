<?php
/**
 * @Manage Columns
 * @return
 *
 */
if (!class_exists('post_type_job')) {

    class post_type_job {

        // The Constructor
        public function __construct() {
            // Adding columns
            add_filter('manage_job_posts_columns', array($this, 'jobsearch_job_columns_add'));
            add_action('manage_job_posts_custom_column', array($this, 'jobsearch_job_columns'), 10, 2);
            add_filter('list_table_primary_column', array($this, 'jobsearch_primary_column'), 10, 2);
            add_action('init', array($this, 'jobsearch_job_register'), 2, 0); // post type register
            add_action('init', array($this, 'jobsearch_job_sector'), 3, 0);
            add_filter('post_row_actions', array($this, 'jobsearch_job_row_actions'));
            add_filter('manage_edit-job_sortable_columns', array($this, 'jobsearch_job_sortable_columns'));
            add_filter('request', array($this, 'jobsearch_job_sort_columns'));
            add_action('init', array($this, 'jobsearch_job_jobtype'), 0);
            // job type extra fields
            add_action('create_jobtype', array($this, 'jobsearch_job_save_jobtype_fields_added_callback'));
            add_action('edited_jobtype', array($this, 'jobsearch_job_save_jobtype_fields_updated_callback'));
            add_action('jobtype_edit_form_fields', array($this, 'jobsearch_job_edit_jobtype_fields_callback'));
            add_action('jobtype_add_form_fields', array($this, 'jobsearch_job_jobtype_fields_callback'));
            add_action('admin_head', array($this, 'jobsearch_job_admin_custom_styles'));
            add_action('init', array($this, 'jobsearch_job_skills'), 0);

            add_action('views_edit-job', array($this, 'modified_views_so'), 0);
            add_filter('parse_query', array($this, 'job_query_filter'), 11, 1);
            add_filter('bulk_actions-edit-job', array($this, 'custom_job_filters'));
            add_action('handle_bulk_actions-edit-job', array($this, 'jobs_bulk_actions_handle'), 10, 3);
        }

        public function jobsearch_job_admin_custom_styles() {
            $output_css = '<style type="text/css"> 
                .column-job_title { width:500px !important; overflow:hidden }
                .column-featured { width:30px !important; overflow:hidden }
                .column-filled { width:30px !important; overflow:hidden }
                .column-status { width:30px !important; overflow:hidden }
                .column-action { text-align:right !important; width:150px !important; overflow:hidden; }
            </style>';
            echo $output_css;
        }

        public function jobsearch_job_register() {
            $labels = array(
                'name' => _x('Jobs', 'post type general name', 'wp-jobsearch'),
                'singular_name' => _x('Job', 'post type singular name', 'wp-jobsearch'),
                'menu_name' => _x('Jobs', 'admin menu', 'wp-jobsearch'),
                'name_admin_bar' => _x('Job', 'add new on admin bar', 'wp-jobsearch'),
                'add_new' => _x('Add New', 'job', 'wp-jobsearch'),
                'add_new_item' => __('Add New Job', 'wp-jobsearch'),
                'new_item' => __('New Job', 'wp-jobsearch'),
                'edit_item' => __('Edit Job', 'wp-jobsearch'),
                'view_item' => __('View Job', 'wp-jobsearch'),
                'all_items' => __('All Jobs', 'wp-jobsearch'),
                'search_items' => __('Search Jobs', 'wp-jobsearch'),
                'parent_item_colon' => __('Parent Jobs:', 'wp-jobsearch'),
                'not_found' => __('No jobs found.', 'wp-jobsearch'),
                'not_found_in_trash' => __('No jobs found in Trash.', 'wp-jobsearch')
            );

            $args = array(
                'labels' => $labels,
                'description' => __('Description.', 'wp-jobsearch'),
                'public' => true,
                'publicly_queryable' => true,
                'show_ui' => true,
                'show_in_menu' => true,
                'query_var' => true,
                'rewrite' => array('slug' => 'job'),
                'capability_type' => 'post',
                'has_archive' => false,
                'exclude_from_search' => true,
                'hierarchical' => false,
                'menu_position' => 25,
                'supports' => array('title', 'editor', 'excerpt')
            );

            register_post_type('job', $args);
        }

        public function jobsearch_job_row_actions($actions) {
            if ('job' == get_post_type()) {
                return array();
            }
            return $actions;
        }

        public function modified_views_so($views) {

            remove_filter('parse_query', array(&$this, 'job_query_filter'), 11, 1);
            $args = array(
                'post_type' => 'job',
                'posts_per_page' => '1',
                'post_status' => 'publish',
                'fields' => 'ids',
                'meta_query' => array(
                    array(
                        'key' => 'jobsearch_field_job_status',
                        'value' => 'approved',
                        'compare' => '!=',
                    ),
                ),
            );
            $jobs_query = new WP_Query($args);
            $pending_jobs = $jobs_query->found_posts;
            wp_reset_postdata();

            $args = array(
                'post_type' => 'job',
                'posts_per_page' => '1',
                'post_status' => 'publish',
                'fields' => 'ids',
                'meta_query' => array(
                    array(
                        'key' => 'jobsearch_field_job_status',
                        'value' => 'admin-review',
                        'compare' => '=',
                    ),
                ),
            );
            $jobs_query = new WP_Query($args);
            $review_jobs = $jobs_query->found_posts;
            wp_reset_postdata();

            $args = array(
                'post_type' => 'job',
                'posts_per_page' => '1',
                'post_status' => 'publish',
                'fields' => 'ids',
                'meta_query' => array(
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
            $jobs_query = new WP_Query($args);
            $approve_jobs = $jobs_query->found_posts;
            wp_reset_postdata();

            $views['approved'] = '<a href="edit.php?post_type=job&job_status=approved">' . sprintf(esc_html__('Approved (%s)', 'wp-jobsearch'), absint($approve_jobs)) . '</a>';
            $views['pending'] = '<a href="edit.php?post_type=job&job_status=pending">' . sprintf(esc_html__('Pending (%s)', 'wp-jobsearch'), absint($pending_jobs)) . '</a>';
            $views['admin-review'] = '<a href="edit.php?post_type=job&job_status=admin-review">' . sprintf(esc_html__('Admin Review (%s)', 'wp-jobsearch'), absint($review_jobs)) . '</a>';

            return $views;
        }

        public function job_query_filter($query) {
            global $pagenow;

            $custom_filter_arr = array();
            if (is_admin() && $pagenow == 'edit.php' && isset($_GET['post_type']) && $_GET['post_type'] == 'job' && isset($_GET['job_status']) && $_GET['job_status'] != '') {
                $custom_filter_arr[] = array(
                    'key' => 'jobsearch_field_job_status',
                    'value' => $_GET['job_status'],
                    'compare' => '=',
                );
            }
            if (!empty($custom_filter_arr)) {
                $query->set('meta_query', $custom_filter_arr);
            }
        }

        public function custom_job_filters($actions) {
            if (is_array($actions) && isset($actions['trash'])) {
                $actions['approved'] = esc_html__('Approved', 'wp-jobsearch');
                $actions['pending'] = esc_html__('Pending', 'wp-jobsearch');
                $actions['admin-review'] = esc_html__('Admin Review', 'wp-jobsearch');
            }
            return $actions;
        }

        function jobs_bulk_actions_handle($redirect_to, $doaction, $post_ids) {
            if ($doaction == 'approved' || $doaction == 'pending' || $doaction == 'admin-review') {
                if (!empty($post_ids)) {
                    foreach ($post_ids as $job_id) {
                        update_post_meta($job_id, 'jobsearch_field_job_status', $doaction);
                        if ($doaction == 'approved') {
                            $job_employer_id = get_post_meta($job_id, 'jobsearch_field_job_posted_by', true);
                            // Employer jobs status change according his/her status
                            do_action('jobsearch_employer_update_jobs_status', $job_employer_id);

                            //
                            $employer_user_id = jobsearch_get_employer_user_id($job_employer_id);
                            $user_obj = get_user_by('ID', $employer_user_id);
                            if (isset($user_obj->ID)) {
                                do_action('jobsearch_job_approved_to_employer', $user_obj, $job_id);
                            }
                        }
                    }
                }
            }
            return $redirect_to;
        }

        public function jobsearch_job_columns_add($columns) {
            global $sitepress;
            $new_columns = array();
            $new_columns['cb'] = '<input type="checkbox" />';
            $new_columns['job_title'] = esc_html('Position', 'wp-jobsearch');
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
            $new_columns['job_type'] = esc_html__('Type', 'wp-jobsearch');
            $new_columns['location'] = esc_html__('Location', 'wp-jobsearch');
            $new_columns['posted'] = esc_html__('Posted On', 'wp-jobsearch');
            $new_columns['expiry'] = esc_html__('Expiry', 'wp-jobsearch');
            $new_columns['featured'] = force_balance_tags('<strong class="jobsearch-tooltip" title="' . esc_html__('Featured', 'wp-jobsearch') . '"><i class="dashicons dashicons-star-filled"></i></strong>');
            $new_columns['filled'] = force_balance_tags('<strong class="jobsearch-tooltip" title="' . esc_html__('Filled', 'wp-jobsearch') . '"><i class="dashicons dashicons-admin-users"></i></strong>');
            $new_columns['status'] = force_balance_tags('<strong class="jobsearch-tooltip" title="' . esc_html__('Status', 'wp-jobsearch') . '"><i class="dashicons dashicons-info"></i></strong>');
            $new_columns['action'] = esc_html__('Action', 'wp-jobsearch');

            return $new_columns;
        }

        public function jobsearch_job_columns($column) {
            global $post;
            switch ($column) {
                case 'job_title' :
                    echo '<div class="job_position">';
                    $src = '';
                    $job_field_user = get_post_meta($post->ID, 'jobsearch_field_job_posted_by', true);
                    $post_thumbnail_id = jobsearch_job_get_profile_image($post->ID);

                    if (isset($post_thumbnail_id) && $post_thumbnail_id != '') {
                        $src = wp_get_attachment_image_src($post_thumbnail_id, 'thumbnail');
                        $src = isset($src[0]) ? $src[0] : '';
                    }
                    if ($src != '') {
                        echo '<div class="company-logo">';
                        echo '<img src="' . esc_attr($src) . '" alt="' . esc_attr(get_the_title($job_field_user)) . '" />';
                        echo '</div>';
                        // Before 1.24.0, logo URLs were stored in post meta.
                    }

                    echo '<a href="' . admin_url('post.php?post=' . $post->ID . '&action=edit') . '" class="job_title" class="jobsearch-tooltip" title="' . sprintf(__('ID: %d', 'wp-jobsearch'), $post->ID) . '">' . ucfirst(get_the_title($post->ID)) . '</a>';

                    echo '<div class="sector-list">';
                    $jobtype_list = get_the_term_list($post->ID, 'sector', '', ',', '');
                    if ($jobtype_list) {
                        printf('%1$s', $jobtype_list);
                    }
                    echo '</div>';

                    echo '</div>';
                    break;
                case 'job_type' :

                    $terms = wp_get_post_terms($post->ID, 'jobtype');

                    if (!empty($terms)) {
                        ?>

                        <?php
                        foreach ($terms as $term) :
                            $jobtype_color = get_term_meta($term->term_id, 'jobsearch_field_jobtype_color', true);
                            $jobtype_textcolor = get_term_meta($term->term_id, 'jobsearch_field_jobtype_textcolor', true);
                            $jobtype_color_str = '';
                            if ($jobtype_color != '') {
                                $jobtype_color_str = ' style="background-color: ' . esc_attr($jobtype_color) . '; color: ' . esc_attr($jobtype_textcolor) . ' "';
                            }
                            ?>

                            <a class="<?php echo $term->slug; ?>">
                                <span class="jobtype-bg" <?php echo force_balance_tags($jobtype_color_str); ?>><?php echo $term->name; ?></span>
                            </a>
                            <?php
                        endforeach;
                    } else {
                        echo esc_html('-');
                    };
                    break;
                case 'location' :
                    $location1 = get_post_meta($post->ID, 'jobsearch_field_location_location1', true);
                    if ($location1 != '') {
                        echo ucfirst(str_replace("-", " ", $location1));
                    } else {
                        echo esc_html('-');
                    }
                    break;
                case 'posted' :
                    $posted = get_post_meta($post->ID, 'jobsearch_field_job_publish_date', true);
                    $posted = $posted == '' ? strtotime(current_time('Y-m-d H:i:s')) : $posted;
                    echo date_i18n(get_option('date_format'), $posted);
                    break;
                case 'expiry' :
                    $expiry = get_post_meta($post->ID, 'jobsearch_field_job_expiry_date', true);
                    $expiry = $expiry == '' ? strtotime(current_time('Y-m-d H:i:s')) : $expiry;
                    echo date_i18n(get_option('date_format'), $expiry);
                    break;
                case 'featured' :
                    $job_featured = get_post_meta($post->ID, 'jobsearch_field_job_featured', true);
                    if ($job_featured == 'on') {
                        echo ('<a href="javascript:void(0);" class="jobsearch-tooltip job-featured-option" data-option="un-feature" data-jobid="' . esc_attr($post->ID) . '" title="' . esc_html__('No', 'wp-jobsearch') . '"><i class="dashicons dashicons-star-filled" aria-hidden="true"></i></a>');
                    } else {
                        echo ('<a href="javascript:void(0);" class="jobsearch-tooltip job-featured-option" data-option="featured" data-jobid="' . esc_attr($post->ID) . '" title="' . esc_html__('Yes', 'wp-jobsearch') . '"><i class="dashicons dashicons-star-empty" aria-hidden="true"></i></a>');
                    }
                    break;
                case 'filled' :
                    $filled = get_post_meta($post->ID, 'jobsearch_field_job_filled', true);
                    if ($filled == 'on') {
                        echo force_balance_tags('<a href="javascript:void(0);" class="jobsearch-tooltip" title="' . esc_html__('Filled', 'wp-jobsearch') . '"><i class="dashicons dashicons-yes" aria-hidden="true"></i></a>');
                    } else {
                        echo esc_html('-');
                    }
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

                    $job_status = get_post_meta($post->ID, 'jobsearch_field_job_status', true);
                    if ($job_status == 'approved') {
                        echo force_balance_tags('<a href="javascript:void(0);" class="jobsearch-tooltip" title="' . esc_html__('Approved', 'wp-jobsearch') . '"><i ' . $approved_color_str . ' class="dashicons dashicons-yes" aria-hidden="true"></i></a>');
                    } elseif ($job_status == 'canceled') {
                        echo force_balance_tags('<a href="javascript:void(0);" class="jobsearch-tooltip" title="' . esc_html__('Canceled', 'wp-jobsearch') . '"><i ' . $canceled_color_str . ' class="dashicons dashicons-warning" aria-hidden="true"></i></a>');
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
            if ('edit-job' === $screen) {
                $column = 'job_title';
            }
            return $column;
        }

        public function jobsearch_job_sortable_columns($columns) {
            $custom = array(
                'featured' => 'featured',
                'filled' => 'filled',
                'status' => 'status',
                'job_title' => 'title',
                'location' => 'location',
                'posted' => 'posted',
                'expiry' => 'expiry',
            );
            return wp_parse_args($custom, $columns);
        }

        public function jobsearch_job_sort_columns($vars) {
            global $wpdb;
            
            if (isset($vars['orderby']) && isset($_GET['post_type']) && $_GET['post_type'] == 'job') {
                if ('expiry' === $vars['orderby']) {
                    $vars = array_merge($vars, array(
                        'meta_key' => 'jobsearch_field_job_expiry_date',
                        'orderby' => 'meta_value'
                    ));
                } elseif ('posted' === $vars['orderby']) {
                    $vars = array_merge($vars, array(
                        'meta_key' => 'jobsearch_field_job_publish_date',
                        'orderby' => 'meta_value'
                    ));
                } elseif ('location' === $vars['orderby']) {
                    $vars = array_merge($vars, array(
                        'meta_key' => 'jobsearch_field_location_location1',
                        'orderby' => 'meta_value'
                    ));
                } elseif ('featured' === $vars['orderby']) {
                    $vars = array_merge($vars, array(
                        'meta_key' => 'jobsearch_field_job_featured',
                        'orderby' => 'meta_value'
                    ));
                } elseif ('filled' === $vars['orderby']) {
                    $vars = array_merge($vars, array(
                        'meta_key' => 'jobsearch_field_job_filled',
                        'orderby' => 'meta_value'
                    ));
                } elseif ('status' === $vars['orderby']) {
                    $vars = array_merge($vars, array(
                        'meta_key' => 'jobsearch_field_job_status',
                        'orderby' => 'meta_value'
                    ));
                }
            }
            return $vars;
        }

        public function jobsearch_job_sort_orderby_taxonomy($clauses, $wp_query) {
            if (!is_admin()) {
                return;
            }
            global $wpdb;

            if (isset($wp_query->query['orderby']) && 'jobtype' == $wp_query->query['orderby']) {

                $clauses['join'] .= "
                LEFT OUTER JOIN {$wpdb->term_relationships} ON {$wpdb->posts}.ID={$wpdb->term_relationships}.object_id
                LEFT OUTER JOIN {$wpdb->term_taxonomy} USING (term_taxonomy_id)
                LEFT OUTER JOIN {$wpdb->terms} USING (term_id)";

                $clauses['where'] .= " AND (taxonomy = 'jobtype' OR taxonomy IS NULL)";
                $clauses['groupby'] = "object_id";
                $clauses['orderby'] = "GROUP_CONCAT({$wpdb->terms}.name ORDER BY name ASC) ";
                $clauses['orderby'] .= ( 'ASC' == strtoupper($wp_query->get('order')) ) ? 'ASC' : 'DESC';
            }

            return $clauses;
        }

        public function jobsearch_job_sector() {
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

            register_taxonomy('sector', array('job', 'candidate', 'employer'), $args);
        }

        public function jobsearch_job_jobtype() {
            // Add new taxonomy, make it hierarchical (like jobtypes)
            $labels = array(
                'name' => _x('Job Types', 'taxonomy general name', 'wp-jobsearch'),
                'singular_name' => _x('Job Type', 'taxonomy singular name', 'wp-jobsearch'),
                'search_items' => __('Search Job Types', 'wp-jobsearch'),
                'all_items' => __('All Job Types', 'wp-jobsearch'),
                'parent_item' => __('Parent Job Type', 'wp-jobsearch'),
                'parent_item_colon' => __('Parent Job Type:', 'wp-jobsearch'),
                'edit_item' => __('Edit Job Type', 'wp-jobsearch'),
                'update_item' => __('Update Job Type', 'wp-jobsearch'),
                'add_new_item' => __('Add New Job Type', 'wp-jobsearch'),
                'new_item_name' => __('New Job Type Name', 'wp-jobsearch'),
                'menu_name' => __('Job Type', 'wp-jobsearch'),
            );

            $args = array(
                'hierarchical' => true,
                'labels' => $labels,
                'show_ui' => true,
                'show_admin_column' => true,
                'query_var' => true,
                'rewrite' => array('slug' => 'jobtype'),
            );

            register_taxonomy('jobtype', array('job'), $args);
        }

        public function jobsearch_job_save_jobtype_fields_added_callback($term_id) {
            if (isset($_POST['jobsearch_field_jobtype_image_meta']) && $_POST['jobsearch_field_jobtype_image_meta'] == '1') {
                if (isset($_POST['jobsearch_field_jobtype_color'])) {
                    $jobtype_color = $_POST['jobsearch_field_jobtype_color'];
                    add_term_meta($term_id, 'jobsearch_field_jobtype_color', $jobtype_color, true);
                }
                if (isset($_POST['jobsearch_field_jobtype_textcolor'])) {
                    $jobtype_textcolor = $_POST['jobsearch_field_jobtype_textcolor'];
                    add_term_meta($term_id, 'jobsearch_field_jobtype_textcolor', $jobtype_textcolor, true);
                }
                if (isset($_POST['jobsearch_field_jobtype_img_field'])) {
                    $jobtype_img_field = $_POST['jobsearch_field_jobtype_img_field'];
                    add_term_meta($term_id, 'jobsearch_field_jobtype_img_field', $jobtype_img_field, true);
                }
            }
        }

        public function jobsearch_job_save_jobtype_fields_updated_callback($term_id) {
            if (isset($_POST['jobsearch_field_jobtype_image_meta']) and $_POST['jobsearch_field_jobtype_image_meta'] == '1') {
                if (isset($_POST['jobsearch_field_jobtype_color'])) {
                    $jobtype_color = $_POST['jobsearch_field_jobtype_color'];
                    update_term_meta($term_id, 'jobsearch_field_jobtype_color', $jobtype_color);
                }
                if (isset($_POST['jobsearch_field_jobtype_textcolor'])) {
                    $jobtype_textcolor = $_POST['jobsearch_field_jobtype_textcolor'];
                    update_term_meta($term_id, 'jobsearch_field_jobtype_textcolor', $jobtype_textcolor);
                }
                if (isset($_POST['jobsearch_field_jobtype_img_field'])) {
                    $jobtype_img_field = $_POST['jobsearch_field_jobtype_img_field'];
                    update_term_meta($term_id, 'jobsearch_field_jobtype_img_field', $jobtype_img_field);
                }
            }
        }

        public function jobsearch_job_edit_jobtype_fields_callback($tag) { //check for existing featured ID
            global $jobsearch_form_fields;
            $jobtype_color = "";
            $jobtype_textcolor = "";
            wp_enqueue_media();
            $jobtype_coordinates = "";
            $jobtype_url = '';
            if (isset($tag->term_id)) {
                $term_id = $tag->term_id;

                $jobtype_color = get_term_meta($term_id, 'jobsearch_field_jobtype_color', true);
                $jobtype_textcolor = get_term_meta($term_id, 'jobsearch_field_jobtype_textcolor', true);
                $jobtype_url = get_term_meta($term_id, 'jobsearch_field_jobtype_img_field', true);
            }
            $opt_array = array(
                'id' => 'jobtype_image_meta',
                'force_std' => "1",
                'name' => "jobtype_image_meta",
                'return' => false,
            );
            $jobsearch_form_fields->input_hidden_field($opt_array);
            ?>
            <tr>
                <th><label for="cat_f_img_url"> <?php echo esc_html__('Job Type Color', 'wp-jobsearch'); ?></label></th>
                <td>
                    <?php
                    $field_params = array(
                        'name' => 'jobtype_color',
                        'classes' => 'color-picker',
                        'ext_attr' => 'data-alpha="true"',
                        'force_std' => esc_attr($jobtype_color),
                    );
                    $jobsearch_form_fields->input_field($field_params);
                    ?> 
                </td>
            </tr>
            <tr>
                <th><label for="cat_f_img_url"> <?php echo esc_html__('Job Type Text Color', 'wp-jobsearch'); ?></label></th>
                <td>
                    <?php
                    $field_params = array(
                        'name' => 'jobtype_textcolor',
                        'classes' => 'color-picker',
                        'force_std' => esc_attr($jobtype_textcolor),
                    );
                    $jobsearch_form_fields->input_field($field_params);
                    ?> 
                </td>
            </tr>
            <tr>
                <th><label for="cat_f_img_url"><?php echo esc_html__('Job Type Image', 'wp-jobsearch'); ?></label></th>
                <td class="jobtype-img-field">
                    <?php
                    $field_params = array(
                        'id' => rand(100000, 999999),
                        'name' => 'jobtype_img_field',
                        'force_std' => esc_url($jobtype_url),
                    );
                    $jobsearch_form_fields->image_upload_field($field_params);
                    ?>
                </td>
            </tr>


            <?php
        }

        public function jobsearch_job_jobtype_fields_callback($tag) { //check for existing featured ID
            global $jobsearch_form_fields;
            wp_enqueue_media();
            if (isset($tag->term_id)) {
                $t_id = $tag->term_id;
            } else {
                $t_id = '';
            }
            $jobtype_image = '';
            $jobtype_color = '';
            $jobtype_textcolor = '';
            ?>
            <div class="form-field">

                <label><?php echo esc_html__('Job Type Color', 'wp-jobsearch'); ?></label>
                <ul class="form-elements" style="margin:0; padding:0;">
                    <li class="to-field" style="width:100%;">
                        <?php
                        $field_params = array(
                            'name' => 'jobtype_color',
                            'classes' => 'color-picker',
                            'ext_attr' => 'data-alpha="true"',
                        );
                        $jobsearch_form_fields->input_field($field_params);
                        ?> 
                    </li>
                </ul>
                <br> <br>
            </div>
            <div class="form-field">

                <label><?php echo esc_html__('Job Type Text Color', 'wp-jobsearch'); ?></label>
                <ul class="form-elements" style="margin:0; padding:0;">
                    <li class="to-field" style="width:100%;">
                        <?php
                        $field_params = array(
                            'name' => 'jobtype_textcolor',
                            'classes' => 'color-picker',
                        );
                        $jobsearch_form_fields->input_field($field_params);
                        ?> 
                    </li>
                </ul>
                <br> <br>
            </div>
            <div class="form-field jobtype-img-field">
                <label><?php echo esc_html__('Job Type image', 'wp-jobsearch'); ?></label>
                <ul class="form-elements" style="margin:0; padding:0;">
                    <li class="to-field" style="width:100%;">
                        <?php
                        $field_params = array(
                            'id' => rand(100000, 999999),
                            'name' => 'jobtype_img_field',
                            'force_std' => '',
                        );
                        $jobsearch_form_fields->image_upload_field($field_params);
                        ?>
                    </li>
                </ul> 
            </div> 
            <?php
            $opt_array = array(
                'id' => 'jobtype_image_meta',
                'force_std' => "1",
                'name' => "jobtype_image_meta",
                'return' => false,
            );
            $jobsearch_form_fields->input_hidden_field($opt_array);
        }

        public function jobsearch_job_skills() {
            // Add new taxonomy, make it hierarchical (like skills)
            $labels = array(
                'name' => _x('Skills', 'taxonomy general name', 'wp-jobsearch'),
                'singular_name' => _x('Skill', 'taxonomy singular name', 'wp-jobsearch'),
                'search_items' => __('Search Skills', 'wp-jobsearch'),
                'all_items' => __('All Skills', 'wp-jobsearch'),
                'parent_item' => __('Parent Skill', 'wp-jobsearch'),
                'parent_item_colon' => __('Parent Skill:', 'wp-jobsearch'),
                'edit_item' => __('Edit Skill', 'wp-jobsearch'),
                'update_item' => __('Update Skill', 'wp-jobsearch'),
                'add_new_item' => __('Add New Skill', 'wp-jobsearch'),
                'new_item_name' => __('New Skill Name', 'wp-jobsearch'),
                'menu_name' => __('Skills', 'wp-jobsearch'),
            );

            $args = array(
                'hierarchical' => false,
                'labels' => $labels,
                'show_ui' => true,
                'show_admin_column' => true,
                'query_var' => true,
                'rewrite' => array('slug' => 'skill'),
            );

            register_taxonomy('skill', array('job'), $args);
        }

    }

    return new post_type_job();
}
