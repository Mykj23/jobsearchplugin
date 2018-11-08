<?php

/**
 * @Manage Columns
 * @return
 *
 */
if (!class_exists('jobsearch_packages_functions')) {

    class jobsearch_packages_functions {

        // The Constructor
        public function __construct() {

            add_action('wp_ajax_jobsearch_user_cv_pckg_subscribe', array($this, 'user_cv_pckg_subscribe'));
            add_action('wp_ajax_nopriv_jobsearch_user_cv_pckg_subscribe', array($this, 'user_cv_pckg_subscribe'));

            add_action('wp_ajax_jobsearch_user_candidate_pckg_subscribe', array($this, 'user_candidate_pckg_subscribe'));
            add_action('wp_ajax_nopriv_jobsearch_user_candidate_pckg_subscribe', array($this, 'user_candidate_pckg_subscribe'));

            add_action('wp_ajax_jobsearch_user_job_pckg_subscribe', array($this, 'user_job_pckg_subscribe'));
            add_action('wp_ajax_nopriv_jobsearch_user_job_pckg_subscribe', array($this, 'user_job_pckg_subscribe'));

            //
            add_action('jobsearch_create_free_packg_order', array($this, 'create_free_packg_order'), 10, 2);

            //
            add_action('jobsearch_add_candidate_resume_id_to_order', array($this, 'add_candidate_resume_id_to_order'), 10, 2);
            //
            add_action('jobsearch_add_candidate_apply_job_id_to_order', array($this, 'add_candidate_apply_job_id_to_order'), 10, 2);
        }

        public function user_cv_pckg_subscribe() {
            $user_id = get_current_user_id();
            $user_is_employer = jobsearch_user_is_employer($user_id);
            if ($user_is_employer) {
                $pkg_id = isset($_POST['pkg_id']) ? $_POST['pkg_id'] : '';
                $employer_id = jobsearch_get_user_employer_id($user_id);
                if (jobsearch_cv_pckg_is_subscribed($pkg_id, $user_id)) {
                    echo json_encode(array('msg' => esc_html__('You have already subscribed this package.', 'wp-jobsearch'), 'error' => '1'));
                    die;
                }
                if (!class_exists('WooCommerce')) {
                    echo json_encode(array('msg' => esc_html__('WooCommerce Plugin not exist.', 'wp-jobsearch'), 'error' => '1'));
                    die;
                }
                $pkg_charges_type = get_post_meta($pkg_id, 'jobsearch_field_charges_type', true);
                $pkg_attach_product = get_post_meta($pkg_id, 'jobsearch_package_product', true);
                if ($pkg_charges_type == 'paid') {
                    $package_product_obj = $pkg_attach_product != '' ? get_page_by_path($pkg_attach_product, 'OBJECT', 'product') : '';

                    if ($pkg_attach_product != '' && is_object($package_product_obj)) {
                        $product_id = $package_product_obj->ID;
                    } else {
                        echo json_encode(array('msg' => esc_html__('Selected Package Product not found.', 'wp-jobsearch'), 'error' => '1'));
                        die;
                    }

                    // add to cart and checkout
                    ob_start();
                    do_action('jobsearch_woocommerce_payment_checkout', $pkg_id, 'checkout_url');
                    $checkout_url = ob_get_clean();
                    echo json_encode(array('msg' => esc_html__('redirecting...', 'wp-jobsearch'), 'redirect_url' => $checkout_url));
                    die;
                } else {
                    do_action('jobsearch_create_free_packg_order', $pkg_id);
                    echo json_encode(array('msg' => esc_html__('Package Subscribed Successfully.', 'wp-jobsearch')));
                    die;
                }
                //
            } else {
                echo json_encode(array('msg' => esc_html__('You are not an employer.', 'wp-jobsearch'), 'error' => '1'));
                die;
            }
        }

        public function user_candidate_pckg_subscribe() {
            $user_id = get_current_user_id();
            $user_is_candidate = jobsearch_user_is_candidate($user_id);
            if ($user_is_candidate) {
                $pkg_id = isset($_POST['pkg_id']) ? $_POST['pkg_id'] : '';
                $candidate_id = jobsearch_get_user_candidate_id($user_id);
                if (jobsearch_app_pckg_is_subscribed($pkg_id, $user_id)) {
                    echo json_encode(array('msg' => esc_html__('You have already subscribed this package.', 'wp-jobsearch'), 'error' => '1'));
                    die;
                }
                if (!class_exists('WooCommerce')) {
                    echo json_encode(array('msg' => esc_html__('WooCommerce Plugin not exist.', 'wp-jobsearch'), 'error' => '1'));
                    die;
                }
                $pkg_charges_type = get_post_meta($pkg_id, 'jobsearch_field_charges_type', true);
                $pkg_attach_product = get_post_meta($pkg_id, 'jobsearch_package_product', true);
                if ($pkg_charges_type == 'paid') {
                    $package_product_obj = $pkg_attach_product != '' ? get_page_by_path($pkg_attach_product, 'OBJECT', 'product') : '';

                    if ($pkg_attach_product != '' && is_object($package_product_obj)) {
                        $product_id = $package_product_obj->ID;
                    } else {
                        echo json_encode(array('msg' => esc_html__('Selected Package Product not found.', 'wp-jobsearch'), 'error' => '1'));
                        die;
                    }

                    // add to cart and checkout
                    ob_start();
                    do_action('jobsearch_woocommerce_payment_checkout', $pkg_id, 'checkout_url');
                    $checkout_url = ob_get_clean();
                    echo json_encode(array('msg' => esc_html__('redirecting...', 'wp-jobsearch'), 'redirect_url' => $checkout_url));
                    die;
                } else {
                    do_action('jobsearch_create_free_packg_order', $pkg_id);
                    echo json_encode(array('msg' => esc_html__('Package Subscribed Successfully.', 'wp-jobsearch')));
                    die;
                }
                //
            } else {
                echo json_encode(array('msg' => esc_html__('You are not a candidate.', 'wp-jobsearch'), 'error' => '1'));
                die;
            }
        }

        public function user_job_pckg_subscribe() {
            $user_id = get_current_user_id();
            $user_is_employer = jobsearch_user_is_employer($user_id);
            if ($user_is_employer) {
                $pkg_id = isset($_POST['pkg_id']) ? $_POST['pkg_id'] : '';
                $employer_id = jobsearch_get_user_employer_id($user_id);
                if (jobsearch_pckg_is_subscribed($pkg_id, $user_id)) {
                    echo json_encode(array('msg' => esc_html__('You have already subscribed this package.', 'wp-jobsearch'), 'error' => '1'));
                    die;
                }
                if (!class_exists('WooCommerce')) {
                    echo json_encode(array('msg' => esc_html__('WooCommerce Plugin not exist.', 'wp-jobsearch'), 'error' => '1'));
                    die;
                }
                $pkg_charges_type = get_post_meta($pkg_id, 'jobsearch_field_charges_type', true);
                $pkg_attach_product = get_post_meta($pkg_id, 'jobsearch_package_product', true);
                if ($pkg_charges_type == 'paid') {
                    $package_product_obj = $pkg_attach_product != '' ? get_page_by_path($pkg_attach_product, 'OBJECT', 'product') : '';

                    if ($pkg_attach_product != '' && is_object($package_product_obj)) {
                        $product_id = $package_product_obj->ID;
                    } else {
                        echo json_encode(array('msg' => esc_html__('Selected Package Product not found.', 'wp-jobsearch'), 'error' => '1'));
                        die;
                    }

                    // add to cart and checkout
                    ob_start();
                    do_action('jobsearch_woocommerce_payment_checkout', $pkg_id, 'checkout_url');
                    $checkout_url = ob_get_clean();
                    echo json_encode(array('msg' => esc_html__('redirecting...', 'wp-jobsearch'), 'redirect_url' => $checkout_url));
                    die;
                } else {
                    do_action('jobsearch_create_free_packg_order', $pkg_id);
                    echo json_encode(array('msg' => esc_html__('Package Subscribed Successfully.', 'wp-jobsearch')));
                    die;
                }
                //
            } else {
                echo json_encode(array('msg' => esc_html__('You are not an employer.', 'wp-jobsearch'), 'error' => '1'));
                die;
            }
        }

        public function create_free_packg_order($pckg_id, $member_type = 'employer') {
            global $woocommerce;

            $user_id = get_current_user_id();
            $user_obj = get_user_by('ID', $user_id);
            $user_displayname = $user_obj->display_name;
            $user_displayname = apply_filters('jobsearch_user_display_name', $user_displayname, $user_obj);
            $user_bio = $user_obj->description;
            $user_website = $user_obj->user_url;
            $user_email = $user_obj->user_email;
            $user_fname = $user_obj->first_name;
            $user_lname = $user_obj->last_name;

            $first_name = $user_fname;
            $last_name = $user_lname;
            if ($user_fname == '' && $user_lname == '') {
                $first_name = $user_displayname;
                $last_name = '';
            }

            if ($member_type == 'candidate') {
                $member_id = jobsearch_get_user_candidate_id($user_id);
            } else {
                $member_id = jobsearch_get_user_employer_id($user_id);
            }

            $user_phone = get_post_meta($member_id, 'jobsearch_field_user_phone', true);
            $user_address = get_post_meta($member_id, 'jobsearch_field_location_address', true);
            $user_city = get_post_meta($member_id, 'jobsearch_field_location_location3', true);
            $user_state = get_post_meta($member_id, 'jobsearch_field_location_location2', true);
            $user_country = get_post_meta($member_id, 'jobsearch_field_location_location1', true);

            $product_id = 0;
            $package_product = get_post_meta($pckg_id, 'jobsearch_package_product', true);
            $package_product_obj = $package_product != '' ? get_page_by_path($package_product, 'OBJECT', 'product') : '';
            if ($package_product != '' && is_object($package_product_obj)) {
                $product_id = $package_product_obj->ID;
            }

            if ($product_id > 0 && get_post_type($product_id) == 'product') {

                $address = array(
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'company' => '',
                    'email' => $user_email,
                    'phone' => $user_phone,
                    'address_1' => $user_address,
                    'address_2' => '',
                    'city' => $user_city,
                    'state' => $user_state,
                    'postcode' => '',
                    'country' => $user_country
                );

                // Now we create the order
                $order = wc_create_order();

                $order->add_product(wc_get_product($product_id), 1);
                $order->set_address($address, 'billing');
                //
                $order->calculate_totals();
                $order_id = $order->get_ID();

                $order->update_status('processing');
                //
                update_post_meta($order_id, 'jobsearch_order_attach_with', 'package');
                update_post_meta($order_id, 'jobsearch_order_package', $pckg_id);
                update_post_meta($order_id, 'jobsearch_order_user', $user_id);
                //
                // For free package
                update_post_meta($order_id, 'jobsearch_order_transaction_type', 'free');
                //
                $order->update_status('completed');
            }
        }

        public function add_candidate_resume_id_to_order($candidate_id, $order_id) {
            if ($candidate_id > 0 && $order_id > 0) {
                $order_cvs = get_post_meta($order_id, 'jobsearch_order_cvs_list', true);
                if ($order_cvs != '') {
                    $order_cvs = explode(',', $order_cvs);
                    $order_cvs[] = $candidate_id;
                    $order_cvs = implode(',', $order_cvs);
                } else {
                    $order_cvs = $candidate_id;
                }
                update_post_meta($order_id, 'jobsearch_order_cvs_list', $order_cvs);
            }
        }

        public function add_candidate_apply_job_id_to_order($candidate_id, $order_id) {
            if ($candidate_id > 0 && $order_id > 0) {
                $order_apps = get_post_meta($order_id, 'jobsearch_order_apps_list', true);
                if ($order_apps != '') {
                    $order_apps = explode(',', $order_apps);
                    $order_apps[] = $candidate_id;
                    $order_apps = implode(',', $order_apps);
                } else {
                    $order_apps = $candidate_id;
                }
                update_post_meta($order_id, 'jobsearch_order_apps_list', $order_apps);
            }
        }

    }

    return new jobsearch_packages_functions();
}
