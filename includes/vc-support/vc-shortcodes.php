<?php

/**
 * visual composer shortcodes mapping
 * @config
 */
/**
 * list all hooks adding
 * @return hooks
 */
add_action('vc_before_init', 'jobsearch_vc_user_job_shortcode');

/**
 * adding user job shortcode
 * @return markup
 */
function jobsearch_vc_user_job_shortcode() {
    $attributes = array(
        "name" => esc_html__("Post New Job", "wp-jobsearch"),
        "base" => "jobsearch_user_job",
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
        )
    );

    if (function_exists('vc_map')) {
        vc_map($attributes);
    }
}
