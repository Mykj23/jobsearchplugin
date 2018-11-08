<?php
if (!function_exists('jobsearch_candidate_portfolio_fields_save_callback')) {

    function jobsearch_candidate_portfolio_fields_save_callback($post_id) {
        global $pagenow;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (isset($_POST)) {
            if (get_post_type($post_id) == 'candidate' && $pagenow == 'post.php') {
                // services save
                $jobsearch_field_portfolio_title = 'jobsearch_field_portfolio_title';
                $jobsearch_field_portfolio_image = 'jobsearch_field_portfolio_image';
                $jobsearch_field_portfolio_url = 'jobsearch_field_portfolio_url';
                $jobsearch_field_portfolio_vurl = 'jobsearch_field_portfolio_vurl';
                $portfolio_title = isset($_POST[$jobsearch_field_portfolio_title]) && !empty($_POST[$jobsearch_field_portfolio_title]) ? $_POST[$jobsearch_field_portfolio_title] : array();
                $portfolio_image = isset($_POST[$jobsearch_field_portfolio_image]) && !empty($_POST[$jobsearch_field_portfolio_image]) ? $_POST[$jobsearch_field_portfolio_image] : array();
                $portfolio_url = isset($_POST[$jobsearch_field_portfolio_url]) && !empty($_POST[$jobsearch_field_portfolio_url]) ? $_POST[$jobsearch_field_portfolio_url] : array();
                $portfolio_vurl = isset($_POST[$jobsearch_field_portfolio_vurl]) && !empty($_POST[$jobsearch_field_portfolio_vurl]) ? $_POST[$jobsearch_field_portfolio_vurl] : array();
                update_post_meta($post_id, $jobsearch_field_portfolio_title, $portfolio_title);
                update_post_meta($post_id, $jobsearch_field_portfolio_image, $portfolio_image);
                update_post_meta($post_id, $jobsearch_field_portfolio_url, $portfolio_url);
            }
        }
    }

    add_action('save_post', 'jobsearch_candidate_portfolio_fields_save_callback');
}
if (!function_exists('portfolio_meta_fields_callback')) {

    function portfolio_meta_fields_callback($post) {
        global $jobsearch_form_fields;
        wp_enqueue_script('jobsearch-plugin-custom-multi-meta-fields');
        $rand_num = rand(1000000, 99999999);
        ?> 
        <div class="jobsearch-portfolios">
            <div class="jobsearch-elem-heading">
                <h2><?php echo esc_html__('Portfolio', 'wp-jobsearch') ?></h2>
            </div> 
            <div class="jobsearch-bk-multi-fields">
                <div class="multi-list-add">
                    <a class="jobsearch-bk-btn open-add-box" href="javascript:void(0)"><?php esc_html_e('Add Portfolio', 'wp-jobsearch') ?></a>
                </div>
                <div class="multi-list-add-box" style="display:none;">
                    <div class="close-box"><a href="javascript:void(0)"><i class="dashicons dashicons-no-alt"></i></a></div>
                    <div class="jobsearch-element-field">
                        <div class="elem-label">
                            <label><?php esc_html_e('Title', 'wp-jobsearch') ?> *</label>
                        </div>
                        <div class="elem-field">
                            <?php
                            $field_params = array(
                                'id' => 'portfolio_title',
                            );
                            $jobsearch_form_fields->input_field($field_params);
                            ?>
                        </div>
                    </div> 
                    <div class="jobsearch-element-field">
                        <div class="elem-label">
                            <label><?php esc_html_e('Portfolio Image', 'wp-jobsearch') ?></label>
                        </div>
                        <div class="elem-field">
                            <?php
                            $field_params = array(
                                'id' => 'portfolio_image_' . $rand_num ,
                            );
                            $jobsearch_form_fields->image_upload_field($field_params);
                            ?>   
                        </div>
                    </div> 
                    <div class="jobsearch-element-field">
                        <div class="elem-label">
                            <label><?php esc_html_e('Video URL', 'wp-jobsearch') ?></label>
                        </div>
                        <div class="elem-field">
                            <?php
                            $field_params = array(
                                'id' => 'portfolio_vurl',
                            );
                            $jobsearch_form_fields->input_field($field_params);
                            ?>
                        </div>
                    </div>
                    <div class="jobsearch-element-field">
                        <div class="elem-label">
                            <label><?php esc_html_e('URL', 'wp-jobsearch') ?></label>
                        </div>
                        <div class="elem-field">
                            <?php
                            $field_params = array(
                                'id' => 'portfolio_url',
                            );
                            $jobsearch_form_fields->input_field($field_params);
                            ?>
                        </div>
                    </div>
                    <div class="addto-list-btn"><a id="jobsearch-add-portfolio-exfield" data-id="<?php echo absint($rand_num) ?>" class="jobsearch-bk-btn" href="javascript:void(0)"><?php esc_html_e('Add to List', 'wp-jobsearch') ?></a><span class="ajax-loader"></span></div>
                </div>
                <?php
                $exfield_list = get_post_meta($post->ID, 'jobsearch_field_portfolio_title', true);
                $exfield_list_val = get_post_meta($post->ID, 'jobsearch_field_portfolio_image', true);
                $exfield_portfolio_url = get_post_meta($post->ID, 'jobsearch_field_portfolio_url', true);
                $exfield_portfolio_vurl = get_post_meta($post->ID, 'jobsearch_field_portfolio_vurl', true);
                ?>
                <ul id="jobsearch-portfoliofields-con" class="jobsearch-bk-sortable">
                    <?php
                    if (is_array($exfield_list) && sizeof($exfield_list) > 0) {

                        $exfield_counter = 0;
                        foreach ($exfield_list as $exfield) {
                            $rand_num = rand(1000000, 99999999);

                            $portfolio_image = isset($exfield_list_val[$exfield_counter]) ? $exfield_list_val[$exfield_counter] : '';
                            $portfolio_url = isset($exfield_portfolio_url[$exfield_counter]) ? $exfield_portfolio_url[$exfield_counter] : '';
                            $portfolio_vurl = isset($exfield_portfolio_vurl[$exfield_counter]) ? $exfield_portfolio_vurl[$exfield_counter] : '';
                            ?>
                            <li id="list-<?php echo absint($rand_num) ?>"> 
                                <div class="multi-list-header" id="list-head-<?php echo absint($rand_num) ?>">
                                    <ul>
                                        <li class="drag-point"><a><i class="dashicons dashicons-image-flip-vertical"></i></a></li>
                                        <li class="list-title"><?php echo wp_trim_words($exfield, 5, '...') ?></li>
                                        <li class="list-actions">
                                            <a class="list-open" data-visible="close" data-id="<?php echo absint($rand_num) ?>" href="javascript:void(0)"><i class="dashicons dashicons-arrow-down-alt2"></i></a>
                                            <a class="list-delete" data-id="<?php echo absint($rand_num) ?>" href="javascript:void(0)"><i class="dashicons dashicons-trash"></i></a>
                                        </li>
                                    </ul>
                                </div>
                                <div id="list-content-<?php echo absint($rand_num) ?>" class="multi-list-content" style="display:none;">
                                    <div class="jobsearch-element-field">
                                        <div class="elem-label">
                                            <label><?php esc_html_e('Title', 'wp-jobsearch') ?></label>
                                        </div>
                                        <div class="elem-field">
                                            <?php
                                            $field_params = array(
                                                'name' => 'portfolio_title[]',
                                                'force_std' => $exfield,
                                            );
                                            $jobsearch_form_fields->input_field($field_params);
                                            ?>
                                        </div>
                                    </div> 
                                    <div class="jobsearch-element-field">
                                        <div class="elem-label">
                                            <label><?php esc_html_e('Portfolio Image', 'wp-jobsearch') ?></label>
                                        </div>
                                        <div class="elem-field">
                                            <?php
                                            $field_params = array(
                                                'id' => 'portfolio_image_' . $rand_num . $exfield_counter,
                                                'name' => 'portfolio_image[]',
                                                'force_std' => $portfolio_image,
                                            );
                                            $jobsearch_form_fields->image_upload_field($field_params);
                                            ?>
                                        </div>
                                    </div> 
                                    <div class="jobsearch-element-field">
                                        <div class="elem-label">
                                            <label><?php esc_html_e('Video URL', 'wp-jobsearch') ?></label>
                                        </div>
                                        <div class="elem-field">
                                            <?php
                                            $field_params = array(
                                                'name' => 'portfolio_vurl[]',
                                                'force_std' => $portfolio_vurl,
                                            );
                                            $jobsearch_form_fields->input_field($field_params);
                                            ?>
                                        </div>
                                    </div> 
                                    <div class="jobsearch-element-field">
                                        <div class="elem-label">
                                            <label><?php esc_html_e('URL', 'wp-jobsearch') ?></label>
                                        </div>
                                        <div class="elem-field">
                                            <?php
                                            $field_params = array(
                                                'name' => 'portfolio_url[]',
                                                'force_std' => $portfolio_url,
                                            );
                                            $jobsearch_form_fields->input_field($field_params);
                                            ?>
                                        </div>
                                    </div> 
                                    <div class="multi-list-update">
                                        <a class="jobsearch-bk-btn" href="javascript:void(0)"><?php esc_html_e('Update', 'wp-jobsearch') ?></a>
                                    </div>

                                </div>
                            </li>
                            <?php
                            $exfield_counter ++;
                        }
                    }
                    ?>
                </ul>
            </div>

        </div>
        <?php
    }

    add_action('candidate_multi_fields_meta', 'portfolio_meta_fields_callback', 1, 11);
}
if (!function_exists('jobsearch_add_project_portfoliofield')) {

    /*
     * Doctor extra fields ajax
     * @return html
     */

    function jobsearch_add_project_portfoliofield($post_id = '', $excerpt_length = '') {
        global $jobsearch_form_fields;
        $title = isset($_POST['portfolio_title']) ? $_POST['portfolio_title'] : '';
        $portfolio_image = isset($_POST['portfolio_image']) ? $_POST['portfolio_image'] : '';
        $portfolio_url = isset($_POST['portfolio_url']) ? $_POST['portfolio_url'] : '';
        $portfolio_vurl = isset($_POST['portfolio_vurl']) ? $_POST['portfolio_vurl'] : '';

        $rand_num = rand(1000000, 99999999);

        ob_start();
        ?>
        <li id="list-<?php echo absint($rand_num) ?>">
            <div class="multi-list-header" id="list-head-<?php echo absint($rand_num) ?>">
                <ul>
                    <li class="drag-point"><a><i class="dashicons dashicons-image-flip-vertical"></i></a></li>
                    <li class="list-title"><?php echo wp_trim_words($title, 5, '...') ?></li>
                    <li class="list-actions">
                        <a class="list-open" data-visible="close" data-id="<?php echo absint($rand_num) ?>" href="javascript:void(0)"><i class="dashicons dashicons-arrow-down-alt2"></i></a>
                        <a class="list-delete" data-id="<?php echo absint($rand_num) ?>" href="javascript:void(0)"><i class="dashicons dashicons-trash"></i></a>
                    </li>
                </ul>
            </div>
            <div id="list-content-<?php echo absint($rand_num) ?>" class="multi-list-content" style="display:none;">
                <div class="jobsearch-element-field">
                    <div class="elem-label">
                        <label><?php esc_html_e('Title', 'wp-jobsearch') ?></label>
                    </div>
                    <div class="elem-field">
                        <?php
                        $field_params = array(
                            'name' => 'portfolio_title[]',
                            'force_std' => $title,
                        );
                        $jobsearch_form_fields->input_field($field_params);
                        ?>
                    </div>
                </div> 
                <div class="jobsearch-element-field">
                    <div class="elem-label">
                        <label><?php esc_html_e('Portfolio Image', 'wp-jobsearch') ?></label>   
                    </div>
                    <div class="elem-field">
                        <?php
                        $field_params = array(
                            'id' => 'portfolio_image_' . $rand_num,
                            'name' => 'portfolio_image[]',
                            'force_std' => $portfolio_image,
                        );
                        $jobsearch_form_fields->image_upload_field($field_params);
                        ?> 
                    </div>
                </div> 
                <div class="jobsearch-element-field">
                    <div class="elem-label">
                        <label><?php esc_html_e('Video URL', 'wp-jobsearch') ?></label>
                    </div>
                    <div class="elem-field">
                        <?php
                        $field_params = array(
                            'name' => 'portfolio_vurl[]',
                            'force_std' => $portfolio_vurl,
                        );
                        $jobsearch_form_fields->input_field($field_params);
                        ?>
                    </div>
                </div>
                <div class="jobsearch-element-field">
                    <div class="elem-label">
                        <label><?php esc_html_e('URL', 'wp-jobsearch') ?></label>
                    </div>
                    <div class="elem-field">
                        <?php
                        $field_params = array(
                            'name' => 'portfolio_url[]',
                            'force_std' => $portfolio_url,
                        );
                        $jobsearch_form_fields->input_field($field_params);
                        ?>
                    </div>
                </div>
                <div class="multi-list-update">
                    <a class="jobsearch-bk-btn" href="javascript:void(0)"><?php esc_html_e('Update', 'wp-jobsearch') ?></a>
                </div>
            </div>
        </li>
        <?php
        $html = ob_get_clean();
        echo json_encode(array('html' => $html));
        die;
    }

    add_action('wp_ajax_jobsearch_add_project_portfoliofield', 'jobsearch_add_project_portfoliofield');
} 