<?php
/*
  Class : Packages
 */

// this is an include only WP file
if (!defined('ABSPATH')) {
    die;
}

// main plugin class
class Jobsearch_Packages {

// hook things up
    public function __construct() {

        $this->load_files();
        add_action('admin_enqueue_scripts', array($this, 'admin_style_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'front_style_scripts'));
        add_action('add_meta_boxes', array($this, 'packages_meta_box'));

        //
        add_action('save_post', array($this, 'update_package_product_meta'), 10, 1);
    }

    private function load_files() {
        include plugin_dir_path(dirname(__FILE__)) . 'packages/include/package-post-type.php';
        include plugin_dir_path(dirname(__FILE__)) . 'packages/include/custom-fields.php';
        include plugin_dir_path(dirname(__FILE__)) . 'packages/include/vc-shortcodes.php';
        include plugin_dir_path(dirname(__FILE__)) . 'packages/shortcodes/packages-shortcode.php';
        include plugin_dir_path(dirname(__FILE__)) . 'packages/include/package-functions.php';
    }

    public function admin_style_scripts() {
        wp_enqueue_script('jobsearch-packages-scripts', plugin_dir_url(dirname(__FILE__)) . 'packages/js/packages-admin.js', array(), '', true);
    }

    public function front_style_scripts() {
        wp_register_script('jobsearch-packages-scripts', plugin_dir_url(dirname(__FILE__)) . 'packages/js/packages.js', array(), '', true);
        $jobsearch_plugin_arr = array(
            'plugin_url' => jobsearch_plugin_get_url(),
            'ajax_url' => admin_url('admin-ajax.php'),
            'error_msg' => esc_html__('There is some problem.', 'wp-jobsearch'),
        );
        wp_localize_script('jobsearch-packages-scripts', 'jobsearch_packages_vars', $jobsearch_plugin_arr);
    }

    public function packages_meta_box() {
        add_meta_box('jobsearch-package-options', esc_html__('Package Options', 'wp-jobsearch'), array($this, 'package_options_box'), 'package', 'normal');
    }

    public function package_options_box() {
        global $post, $jobsearch_form_fields, $Jobsearch_Package_Custom_Fields;

        $package_type = get_post_meta($post->ID, 'jobsearch_field_package_type', true);
        $charges_type = get_post_meta($post->ID, 'jobsearch_field_charges_type', true);
        ?>
        <div class="jobsearch-post-settings">
            <div class="jobsearch-element-field">
                <div class="elem-label">
                    <label><?php esc_html_e('Feature Package', 'wp-jobsearch') ?></label>
                </div>
                <div class="elem-field">
                    <?php
                    $field_params = array(
                        'id' => 'jobsearch-is-feature-pkg',
                        'name' => 'feature_pkg',
                        'options' => array(
                            'no' => esc_html__('No', 'wp-jobsearch'),
                            'yes' => esc_html__('Yes', 'wp-jobsearch'),
                        ),
                    );
                    $jobsearch_form_fields->select_field($field_params);
                    ?>
                </div>
            </div>
            <div class="jobsearch-element-field">
                <div class="elem-label">
                    <label><?php esc_html_e('Charges Type', 'wp-jobsearch') ?></label>
                </div>
                <div class="elem-field">
                    <?php
                    $field_params = array(
                        'id' => 'jobsearch-package-charges-type',
                        'name' => 'charges_type',
                        'ext_attr' => 'onchange="jobsearch_onchange_package_price_type(this.value)"',
                        'options' => array(
                            'paid' => esc_html__('Paid', 'wp-jobsearch'),
                            'free' => esc_html__('Free', 'wp-jobsearch'),
                        ),
                    );
                    $jobsearch_form_fields->select_field($field_params);
                    ?>
                </div>
            </div>
            <div class="jobsearch-package-price-fields" style="display: <?php echo ($charges_type == 'free' ? 'none' : 'block') ?>;">
                <div class="jobsearch-element-field">
                    <div class="elem-label">
                        <label><?php esc_html_e('Package Price', 'wp-jobsearch') ?></label>
                    </div>
                    <div class="elem-field">
                        <?php
                        $field_params = array(
                            'id' => 'jobsearch-package-price',
                            'name' => 'package_price',
                            'std' => '50',
                        );
                        $jobsearch_form_fields->input_field($field_params);
                        ?>
                    </div>
                </div>
            </div>
            <div class="jobsearch-element-field">
                <div class="elem-label">
                    <label><?php esc_html_e('Package Type', 'wp-jobsearch') ?></label>
                </div>
                <div class="elem-field">
                    <?php
                    $field_params = array(
                        'name' => 'package_type',
                        'ext_attr' => 'onchange="jobsearch_onchange_package_type(this.value)"',
                        'options' => apply_filters('jobsearch_admin_change_package_types', array(
                            'job' => esc_html__('Jobs Package', 'wp-jobsearch'),
                            'cv' => esc_html__('CV\'s Package', 'wp-jobsearch'),
                            'feature_job' => esc_html__('Featured Job Package', 'wp-jobsearch'),
                            'candidate' => esc_html__('Candidate\'s Package', 'wp-jobsearch'),
                        )),
                    );
                    $jobsearch_form_fields->select_field($field_params);
                    ?>
                </div>
            </div>
            <div class="jobsearch-element-field">
                <div class="elem-label">
                    <label><?php esc_html_e('Package Expiry Time', 'wp-jobsearch') ?></label>
                </div>
                <div class="elem-field">
                    <div class="input-select-field input-f">
                        <?php
                        $field_params = array(
                            'name' => 'package_expiry_time',
                            'std' => '10',
                        );
                        $jobsearch_form_fields->input_field($field_params);
                        ?>
                    </div>
                    <div class="input-select-field select-f">
                        <?php
                        $field_params = array(
                            'name' => 'package_expiry_time_unit',
                            'options' => array(
                                'days' => esc_html__('Days', 'wp-jobsearch'),
                                'weeks' => esc_html__('Weeks', 'wp-jobsearch'),
                                'months' => esc_html__('Months', 'wp-jobsearch'),
                                'years' => esc_html__('Years', 'wp-jobsearch'),
                            ),
                        );
                        $jobsearch_form_fields->select_field($field_params);
                        ?>
                    </div>
                </div>
            </div>
            <?php do_action('jobsearch_admin_package_meta_fields', $post->ID); ?>
            <div id="candidate_package_fields" class="candidate-package-fields specific-pkges-fields" style="display: <?php echo ($package_type == 'candidate' ? 'block' : 'none') ?>;">
                <div class="jobsearch-element-field">
                    <div class="elem-label">
                        <label><?php esc_html_e('Number of Applications', 'wp-jobsearch') ?></label>
                    </div>
                    <div class="elem-field">
                        <?php
                        $field_params = array(
                            'name' => 'num_of_apps',
                            'std' => '50',
                        );
                        $jobsearch_form_fields->input_field($field_params);
                        ?>
                    </div>
                </div>
            </div>
            <?php
            ob_start();
            ?>
            <div id="job_package_fields" class="job-package-fields specific-pkges-fields" style="display: <?php echo ($package_type == '' || $package_type == 'job' ? 'block' : 'none') ?>;">
                <div class="jobsearch-element-field">
                    <div class="elem-label">
                        <label><?php esc_html_e('Number of Jobs', 'wp-jobsearch') ?></label>
                    </div>
                    <div class="elem-field">
                        <?php
                        $field_params = array(
                            'name' => 'num_of_jobs',
                            'std' => '10',
                        );
                        $jobsearch_form_fields->input_field($field_params);
                        ?>
                    </div>
                </div>
                <?php
                ob_start();
                ?>
                <div class="jobsearch-element-field">
                    <div class="elem-label">
                        <label><?php esc_html_e('Job Expiry Time', 'wp-jobsearch') ?></label>
                    </div>
                    <div class="elem-field">
                        <div class="input-select-field input-f">
                            <?php
                            $field_params = array(
                                'name' => 'job_expiry_time',
                                'std' => '7',
                            );
                            $jobsearch_form_fields->input_field($field_params);
                            ?>
                        </div>
                        <div class="input-select-field select-f">
                            <?php
                            $field_params = array(
                                'name' => 'job_expiry_time_unit',
                                'options' => array(
                                    'days' => esc_html__('Days', 'wp-jobsearch'),
                                    'weeks' => esc_html__('Weeks', 'wp-jobsearch'),
                                    'months' => esc_html__('Months', 'wp-jobsearch'),
                                    'years' => esc_html__('Years', 'wp-jobsearch'),
                                ),
                            );
                            $jobsearch_form_fields->select_field($field_params);
                            ?>
                        </div>
                    </div>
                </div>
                <?php
                $pkg_job_exp_field = ob_get_clean();
                echo apply_filters('jobsearch_pkgs_job_exp_meta_field', $pkg_job_exp_field);
                ?>
                <?php echo ($Jobsearch_Package_Custom_Fields->init_fields('job_package')); ?>
            </div>
            <?php
            $job_meta_fields = ob_get_clean();
            echo apply_filters('jobsearch_pkg_admin_job_meta_fields', $job_meta_fields);
            ?>
            <div id="cv_package_fields" class="cv-package-fields specific-pkges-fields" style="display: <?php echo ($package_type == 'cv' ? 'block' : 'none') ?>;">
                <div class="jobsearch-element-field">
                    <div class="elem-label">
                        <label><?php esc_html_e('Number of CV\'s', 'wp-jobsearch') ?></label>
                    </div>
                    <div class="elem-field">
                        <?php
                        $field_params = array(
                            'name' => 'num_of_cvs',
                            'std' => '10',
                        );
                        $jobsearch_form_fields->input_field($field_params);
                        ?>
                    </div>
                </div>
                <?php echo force_balance_tags($Jobsearch_Package_Custom_Fields->init_fields('cv_package')); ?>
            </div>
            <div id="feature_job_package_fields" class="feature-job-package-fields specific-pkges-fields" style="display: <?php echo ($package_type == 'feature_job' ? 'block' : 'none') ?>;">
                <?php echo force_balance_tags($Jobsearch_Package_Custom_Fields->init_fields('feature_job_package')); ?>
            </div>
            <div class="pckg-extra-fields-con">
                <?php
                $pkg_exfield_title = get_post_meta($post->ID, 'jobsearch_field_package_exfield_title', true);
                $pkg_exfield_val = get_post_meta($post->ID, 'jobsearch_field_package_exfield_val', true);
                $pkg_exfield_status = get_post_meta($post->ID, 'jobsearch_field_package_exfield_status', true);
                if (!empty($pkg_exfield_title)) {
                    $_exf_counter = 0;
                    foreach ($pkg_exfield_title as $_exfield_title) {
                        $_exfield_val = isset($pkg_exfield_val[$_exf_counter]) ? $pkg_exfield_val[$_exf_counter] : '';
                        $_exfield_status = isset($pkg_exfield_status[$_exf_counter]) ? $pkg_exfield_status[$_exf_counter] : '';
                        ?>
                        <div class="pckg-extra-field-item">
                            <div class="field-heder">
                                <a class="drag-point"><i class="dashicons dashicons-image-flip-vertical"></i></a>
                                <h2><?php esc_html_e('Extra Field', 'wp-jobsearch') ?></h2>
                            </div>
                            <div class="field-remove-con">
                                <a href="javascript:void(0);" class="field-remove-btn"><i class="dashicons dashicons-no-alt"></i></a>
                            </div>
                            <div class="jobsearch-element-field">
                                <div class="elem-label">
                                    <label><?php esc_html_e('Field Title', 'wp-jobsearch') ?></label>
                                </div>
                                <div class="elem-field">
                                    <input type="text" name="jobsearch_field_package_exfield_title[]" value="<?php echo ($_exfield_title) ?>">
                                </div>
                            </div>
                            <div class="jobsearch-element-field">
                                <div class="elem-label">
                                    <label><?php esc_html_e('Field Value', 'wp-jobsearch') ?></label>
                                </div>
                                <div class="elem-field">
                                    <input type="text" name="jobsearch_field_package_exfield_val[]" value="<?php echo ($_exfield_val) ?>">
                                </div>
                            </div>
                            <div class="jobsearch-element-field">
                                <div class="elem-label">
                                    <label><?php esc_html_e('Field Status', 'wp-jobsearch') ?></label>
                                </div>
                                <div class="elem-field">
                                    <select name="jobsearch_field_package_exfield_status[]">
                                        <option value="active"<?php echo ($_exfield_status == 'active' ? ' selected="selected"' : '') ?>><?php esc_html_e('Active', 'wp-jobsearch') ?></option>
                                        <option value="inactive"<?php echo ($_exfield_status == 'inactive' ? ' selected="selected"' : '') ?>><?php esc_html_e('Inactive', 'wp-jobsearch') ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <?php
                        $_exf_counter++;
                    }
                }
                ?>
            </div>
            <div class="jobsearch-element-field">
                <div class="elem-label">&nbsp;</div>
                <div class="elem-field">
                    <a href="javascript:void(0);" class="button button-primary button-large add-pkg-more-fields"><?php esc_html_e('Add More Fields', 'wp-jobsearch') ?></a>
                </div>
            </div>
        </div>
        <?php
    }

    public function update_package_product_meta($post_id = '') {
        global $post;

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return false;
        }

        if (!class_exists('WooCommerce')) {
            return false;
        }

        if (!isset($_POST['post_title'])) {
            return false;
        }

        if (isset($_POST['jobsearch_field_charges_type']) && $_POST['jobsearch_field_charges_type'] == 'free' && get_post_type($post_id) == 'package') {
            $package_product = get_post_meta($post_id, 'jobsearch_package_product', true);

            $package_product_obj = $package_product != '' ? get_page_by_path($package_product, 'OBJECT', 'product') : '';

            if ($package_product != '' && is_object($package_product_obj)) {
                $product_id = $package_product_obj->ID;
                wp_delete_post($product_id, true);

                // update package product
                update_post_meta($post_id, 'jobsearch_package_product', '');
            }
        }

        if (get_post_type($post_id) == 'package') {
            $package_price = isset($_POST['jobsearch_field_package_price']) ? $_POST['jobsearch_field_package_price'] : '';
            $package_title = isset($_POST['post_title']) ? $_POST['post_title'] : '';

            $package_charges_type = isset($_POST['jobsearch_field_charges_type']) ? $_POST['jobsearch_field_charges_type'] : '';
            //
            $package_obj = get_post($post_id);
            $package_name = $package_obj->post_name;

            $package_product = get_post_meta($post_id, 'jobsearch_package_product', true);

            $package_product_obj = $package_product != '' ? get_page_by_path($package_product, 'OBJECT', 'product') : '';

            if ($package_product != '' && is_object($package_product_obj)) {
                $product_id = $package_product_obj->ID;

                // Product Title
                $prod_post = array(
                    'ID' => $product_id,
                    'post_title' => wp_strip_all_tags($package_title),
                    'post_content' => '',
                );
                wp_update_post($prod_post);

                $_product = wc_get_product($product_id);
                $_product->set_catalog_visibility('hidden');
                $_product->save();
            } else {
                $post_args = array(
                    'post_title' => wp_strip_all_tags($package_title),
                    'post_content' => '',
                    'post_status' => "publish",
                    'post_type' => "product",
                );

                $product_id = wp_insert_post($post_args);

                $_product = wc_get_product($product_id);
                $_product->set_catalog_visibility('hidden');
                $_product->save();

                wp_set_object_terms($product_id, 'simple', 'product_type');

                update_post_meta($product_id, '_visibility', 'visible');
                update_post_meta($product_id, '_stock_status', 'instock');
                update_post_meta($product_id, 'total_sales', '0');
            }

            $prod_obj = get_post($product_id);

            // update package product
            update_post_meta($post_id, 'jobsearch_package_product', $prod_obj->post_name);

            // update product attach type -> package
            update_post_meta($product_id, 'jobsearch_attach_with', 'package');
            update_post_meta($product_id, 'jobsearch_attach_package', $package_name);

            // Price
            if ($package_charges_type != 'paid') {
                $package_price = 0;
            }
            if ($package_price > 0) {
                $price_amount = $package_price;
            } else {
                $price_amount = '0';
            }
            $price_amount = (float) $price_amount;
            update_post_meta($product_id, '_regular_price', $price_amount);
            update_post_meta($product_id, '_price', $price_amount);
        }
    }

}

// class Jobsearch_Packages 
global $Jobsearch_Packages_obj;
$Jobsearch_Packages_obj = new Jobsearch_Packages();
