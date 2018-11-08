<?php

/**
 * visual composer shortcodes mapping
 * @config
 */
/**
 * list all hooks adding
 * @return hooks
 */
add_action('vc_before_init', 'jobsearch_vc_packages_shortcode');

/**
 * adding packages shortcode
 * @return markup
 */
function jobsearch_vc_packages_shortcode() {

    $attributes = array(
        "name" => esc_html__("Packages", "wp-jobsearch"),
        "base" => "jobsearch_packages",
        "class" => "",
        "category" => esc_html__("Wp JobSearch", "wp-jobsearch"),
        "params" => array(
            array(
                'type' => 'textfield',
                'heading' => esc_html__("Title", "wp-jobsearch"),
                'param_name' => 'title',
                'value' => '',
                'description' => ''
            ),
            array(
                'type' => 'dropdown',
                'heading' => esc_html__("Package Type", "wp-jobsearch"),
                'param_name' => 'packages_type',
                'value' => array(
                    esc_html__("CV Packages", "wp-jobsearch") => 'cv',
                    esc_html__("Job Packages", "wp-jobsearch") => 'job',
                    esc_html__("Candidate Packages", "wp-jobsearch") => 'candidate',
                ),
                'description' => esc_html__("Please Select Package type.", "wp-jobsearch")
            ),
            array(
                'type' => 'textfield',
                'heading' => esc_html__("Number of Packages", "wp-jobsearch"),
                'param_name' => 'num_packages',
                'value' => '',
                'description' => ''
            ),
        )
    );

    if (function_exists('vc_map')) {
        vc_map($attributes);
    }
}
