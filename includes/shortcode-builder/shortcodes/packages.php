<?php

$jobsearch_builder_shortcodes['jobsearch_packages'] = array(
    'title' => esc_html__('Packages', 'wp-jobsearch'),
    'id' => 'jobsearch-packages-shortcode',
    'template' => '[jobsearch_packages {{attributes}}] {{content}} [/jobsearch_packages]',
    'params' => array(
        'title' => array(
            'std' => '',
            'type' => 'text',
            'label' => esc_html__('Title', 'wp-jobsearch'),
            'desc' => '',
        ),
        'packages_type' => array(
            'type' => 'select',
            'label' => esc_html__('Package Type', 'wp-jobsearch'),
            'desc' => esc_html__('Please Select Package type.', 'wp-jobsearch'),
            'options' => array(
                'cv' => esc_html__('CV Packages', 'wp-jobsearch'),
                'job' => esc_html__('Job Packages', 'wp-jobsearch'),
                'candidate' => esc_html__('Candidate Packages', 'wp-jobsearch'),
            )
        ),
        'num_packages' => array(
            'std' => '',
            'type' => 'text',
            'label' => esc_html__('Number of Packages', 'wp-jobsearch'),
            'desc' => '',
        ),
    )
);
