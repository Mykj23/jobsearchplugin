<?php
/**
 * JobSearch  Recent Post Class
 *
 * @package Recent Post
 */
if (!class_exists('JobSearch_Recent_Posts')) {

    /**
      JobSearch  Recent Post class used to implement the Custom flicker gallery widget.
     */
    class JobSearch_Recent_Posts extends WP_Widget {

        /**
         * Sets up a new jobsearch  flicker widget instance.
         */
        public function __construct() {
            parent::__construct(
                    'jobsearch_recent_posts', // Base ID.
                    __('JobSearch  Recent Posts', 'wp-jobsearch'), // Name.
                    array('classname' => 'widget_recent_post', 'description' => __('Recent Post widget for new posts.', 'wp-jobsearch'))
            );
        }

        /**
         * Outputs the jobsearch  flicker widget settings form.
         *
         * @param array $instance Current settings.
         */
        function form($instance) {
            global $jobsearch_form_fields;

            $instance = wp_parse_args((array) $instance, array('title' => ''));
            $title = $instance['title'];
            $view = isset($instance['view']) ? esc_attr($instance['view']) : '';
            $category = isset($instance['category']) ? esc_attr($instance['category']) : '';
            $no_of_posts = isset($instance['no_of_posts']) ? esc_attr($instance['no_of_posts']) : '';
            ?>
            <div class="jobsearch-element-field">
                <div class="elem-label">
                    <label><?php esc_html_e('Title', 'wp-jobsearch') ?></label>
                </div>
                <div class="elem-field">
                    <?php
                    $field_params = array(
                        'cus_name' => $this->get_field_name('title'),
                        'force_std' => $title,
                    );
                    $jobsearch_form_fields->input_field($field_params);
                    ?>
                </div>
            </div>
            <div class="jobsearch-element-field">
                <div class="elem-label">
                    <label><?php esc_html_e('View', 'wp-jobsearch') ?></label>
                </div>
                <div class="elem-field">

                    <?php
                    $field_params = array(
                        'force_std' => $view,
                        'cus_name' => $this->get_field_name('view'),
                        'options' => array(
                            'footer_style' => esc_html__('Footer Style', 'wp-jobsearch'),
                            'sidebar_style' => esc_html__('Sidebar Style', 'wp-jobsearch'),
                        ),
                    );

                    $jobsearch_form_fields->select_field($field_params);
                    ?>
                </div>
            </div>
            <div class="jobsearch-element-field">
                <div class="elem-label">
                    <label><?php esc_html_e('Category', 'wp-jobsearch') ?></label>
                </div>
                <div class="elem-field">
                    <?php
                    $categories = get_categories(array(
                        'orderby' => 'name',
                    ));

                    $cate_array = array('' => esc_html__("Select Category", "wp-jobsearch"));
                    if (is_array($categories) && sizeof($categories) > 0) {
                        foreach ($categories as $categ) {
                            $cate_array[$categ->slug] = $categ->cat_name;
                        }
                    }
                    $field_params = array(
                        'cus_name' => $this->get_field_name('category'),
                        'options' => $cate_array,
                        'force_std' => $category,
                    );
                    $jobsearch_form_fields->select_field($field_params);
                    ?>
                </div>
            </div>

            <div class="jobsearch-element-field">
                <div class="elem-label">
                    <label><?php esc_html_e('Number of Posts', 'wp-jobsearch') ?></label>
                </div>
                <div class="elem-field">
                    <?php
                    $field_params = array(
                        'cus_name' => $this->get_field_name('no_of_posts'),
                        'force_std' => $no_of_posts,
                    );
                    $jobsearch_form_fields->input_field($field_params);
                    ?>
                </div>
            </div>
            <?php
        }

        /**
         * Handles updating settings for the current jobsearch  flicker widget instance.
         *
         * @param array $new_instance New settings for this instance as input by the user.
         * @param array $old_instance Old settings for this instance.
         * @return array Settings to save or bool false to cancel saving.
         */
        function update($new_instance, $old_instance) {
            $instance = $old_instance;
            $instance['title'] = $new_instance['title'];
            $instance['view'] = $new_instance['view'];
            $instance['category'] = $new_instance['category'];
            $instance['no_of_posts'] = $new_instance['no_of_posts'];
            return $instance;
        }

        /**
         * Outputs the content for the current jobsearch  flicker widget instance.
         *
         * @param array $args Display arguments including 'before_title', 'after_title',
         * 'before_widget', and 'after_widget'.
         * @param array $instance Settings for the current Text widget instance.
         */
        function widget($args, $instance) {

            extract($args, EXTR_SKIP);

            $title = empty($instance['title']) ? '' : apply_filters('widget_title', $instance['title']);
            $title = htmlspecialchars_decode(stripslashes($title));
            $view = empty($instance['view']) ? '' : $instance['view'];
            $category = empty($instance['category']) ? '' : apply_filters('widget_title', $instance['category']);
            $no_of_posts = empty($instance['no_of_posts']) ? ' ' : apply_filters('widget_title', $instance['no_of_posts']);
            if ('' === $instance['no_of_posts']) {
                $instance['no_of_posts'] = '3';
            }
            $before_widget = isset($args['before_widget']) ? $args['before_widget'] : '';
            $after_widget = isset($args['after_widget']) ? $args['after_widget'] : '';

            $before_title = isset($args['before_title']) ? $args['before_title'] : '';
            $after_title = isset($args['after_title']) ? $args['after_title'] : '';

            echo ( $before_widget );
            if ('' !== $title) {
                echo ( $before_title ) . esc_html($title) . ( $after_title );
            }

            $args = array(
                'post_type' => 'post',
                'posts_per_page' => $no_of_posts,
                'post_status' => 'publish',
                'ignore_sticky_posts' => 1,
            );

            if ($category && $category != '' && $category != '0') {
                $args['tax_query'] = array(
                    array(
                        'taxonomy' => 'category',
                        'field' => 'slug',
                        'terms' => $category,
                    ),
                );
            }

            $blog_query = new WP_Query($args);

            if ($blog_query->have_posts()) {
                global $post;
                if ($view == 'sidebar_style') {
                    ?><div class="recent-post-sidebar">
                        <ul>
                            <?php
                            while ($blog_query->have_posts()) : $blog_query->the_post();

                                $post_thumbnail_id = get_post_thumbnail_id(get_the_ID());
                                $post_thumbnail_image = wp_get_attachment_image_src($post_thumbnail_id, 'thumbnail');
                                $post_thumbnail_src = isset($post_thumbnail_image[0]) && esc_url($post_thumbnail_image[0]) != '' ? $post_thumbnail_image[0] : '';
                                ?>
                                <li>
                                    <figure>
                                        <a title="<?php echo get_the_title(get_the_ID()) ?>" href="<?php echo esc_url(get_permalink(get_the_ID())) ?>">
                                            <img src="<?php echo esc_url($post_thumbnail_src) ?>" alt="<?php echo get_the_title(get_the_ID()) ?>"></a>
                                    </figure>
                                    <div class="jobsearch-widget-popularnews">
                                        <time><i class="jobsearch-icon-time"></i><?php echo get_the_date() ?></time>
                                        <h6><a href="<?php echo esc_url(get_permalink(get_the_ID())) ?>"><?php echo wp_trim_words(get_the_title(get_the_ID()), 5, '...') ?></a></h6>
                                    </div>
                                </li>
                                <?php
                            endwhile;
                            wp_reset_postdata();
                            ?>
                        </ul>
                    </div>
                <?php } else {
                    ?>
                    <div class="recent-post-footer">
                        <ul>
                            <?php
                            while ($blog_query->have_posts()) : $blog_query->the_post();

                                $post_thumbnail_id = get_post_thumbnail_id(get_the_ID());
                                $post_thumbnail_image = wp_get_attachment_image_src($post_thumbnail_id, 'thumbnail');
                                $post_thumbnail_src = isset($post_thumbnail_image[0]) && esc_url($post_thumbnail_image[0]) != '' ? $post_thumbnail_image[0] : '';
                                ?>
                                <li>
                                    <figure><a title="<?php echo get_the_title(get_the_ID()) ?>" href="<?php echo esc_url(get_permalink(get_the_ID())) ?>">
                                            <img src="<?php echo esc_url($post_thumbnail_src) ?>" alt="<?php echo get_the_title(get_the_ID()) ?>"></a>
                                    </figure>
                                    <div class="jobsearch-recent_post_text">
                                        <time><i class="jobsearch-icon-time"></i> <?php echo get_the_date() ?></time>
                                        <h5><a href="<?php echo esc_url(get_permalink(get_the_ID())) ?>"><?php echo wp_trim_words(get_the_title(get_the_ID()), 5, '...') ?></a></h5>
                                        <p><?php echo jobsearch_excerpt(5) ?></p>
                                    </div>
                                </li> 

                                <?php
                            endwhile;
                            wp_reset_postdata();
                            ?>
                        </ul>
                    </div>
                    <?php
                }
            }

            echo ( $after_widget );
        }

    }

}
add_action('widgets_init', function() {return register_widget("jobsearch_recent_posts");});
