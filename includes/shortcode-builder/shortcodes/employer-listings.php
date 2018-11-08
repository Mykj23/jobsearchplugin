<?php

$categories = get_terms(array(
    'taxonomy' => 'sector',
    'hide_empty' => false,
        ));

$cate_array = array('' => esc_html__("Select Sector", "wp-jobsearch"));
if (is_array($categories) && sizeof($categories) > 0) {
    foreach ($categories as $category) {
        $cate_array[$category->slug] = $category->name;
    }
}

$jobsearch_builder_shortcodes['employer_listings'] = array(
    'title' => esc_html__('Employer Listing', 'wp-jobsearch'),
    'id' => 'jobsearch-employer-listings-shortcode',
    'template' => '[jobsearch_employer_shortcode {{attributes}}] {{content}} [/jobsearch_employer_shortcode]',
    'params' => apply_filters('jobsearch_employer_listings_sheb_params', array(
        'employer_cat' => array(
            'type' => 'select',
            'label' => esc_html__('Sector', 'wp-jobsearch'),
            'desc' => esc_html__('Select Sector.', 'wp-jobsearch'),
            'options' => $cate_array
        ),
        'employer_filters' => array(
            'type' => 'select',
            'label' => esc_html__('Filters', 'wp-jobsearch'),
            'desc' => esc_html__('Employers searching filters switch.', 'wp-jobsearch'),
            'options' => array(
                'yes' => esc_html__('Yes', 'wp-jobsearch'),
                'no' => esc_html__('No', 'wp-jobsearch'),
            )
        ),
        'employer_filters_count' => array(
            'type' => 'select',
            'label' => esc_html__('Filters Count', 'wp-jobsearch'),
            'desc' => esc_html__('Show result counts in front of every filter.', 'wp-jobsearch'),
            'options' => array(
                'no' => esc_html__('No', 'wp-jobsearch'),
                'yes' => esc_html__('Yes', 'wp-jobsearch'),
            )
        ),
        'employer_filters_loc' => array(
            'type' => 'select',
            'label' => esc_html__('Locations Filter', 'wp-jobsearch'),
            'desc' => esc_html__('Employers searching filters "Locations" switch.', 'wp-jobsearch'),
            'options' => array(
                'yes' => esc_html__('Yes', 'wp-jobsearch'),
                'no' => esc_html__('No', 'wp-jobsearch'),
            )
        ),
        'employer_filters_loc_collapse' => array(
            'type' => 'select',
            'label' => esc_html__('Locations Filter Collapse', 'wp-jobsearch'),
            'desc' => '',
            'options' => array(
                'no' => esc_html__('No', 'wp-jobsearch'),
                'yes' => esc_html__('Yes', 'wp-jobsearch'),
            )
        ),
        'employer_filters_loc_view' => array(
            'type' => 'select',
            'label' => esc_html__('Locations Filter Style', 'wp-jobsearch'),
            'desc' => esc_html__('Employers searching filters "Locations" Style.', 'wp-jobsearch'),
            'options' => array(
                'checkboxes' => esc_html__('Checkbox List', 'wp-jobsearch'),
                'input' => esc_html__('Input Field', 'wp-jobsearch'),
            )
        ),
        'employer_filters_date' => array(
            'type' => 'select',
            'label' => esc_html__('Posted Date Filter', 'wp-jobsearch'),
            'desc' => esc_html__('Employers searching filters "Posted Date" switch.', 'wp-jobsearch'),
            'options' => array(
                'yes' => esc_html__('Yes', 'wp-jobsearch'),
                'no' => esc_html__('No', 'wp-jobsearch'),
            )
        ),
        'employer_filters_date_collapse' => array(
            'type' => 'select',
            'label' => esc_html__('Posted Date Filter Collapse', 'wp-jobsearch'),
            'desc' => '',
            'options' => array(
                'no' => esc_html__('No', 'wp-jobsearch'),
                'yes' => esc_html__('Yes', 'wp-jobsearch'),
            )
        ),
        'employer_filters_sector' => array(
            'type' => 'select',
            'label' => esc_html__('Employer Sector Filter', 'wp-jobsearch'),
            'desc' => esc_html__('Employers searching filters "Sector" switch.', 'wp-jobsearch'),
            'options' => array(
                'yes' => esc_html__('Yes', 'wp-jobsearch'),
                'no' => esc_html__('No', 'wp-jobsearch'),
            )
        ),
        'employer_filters_sector_collapse' => array(
            'type' => 'select',
            'label' => esc_html__('Employer Sector Filter Collapse', 'wp-jobsearch'),
            'desc' => '',
            'options' => array(
                'no' => esc_html__('No', 'wp-jobsearch'),
                'yes' => esc_html__('Yes', 'wp-jobsearch'),
            )
        ),
        'employer_filters_team' => array(
            'type' => 'select',
            'label' => esc_html__('Team Size Filter', 'wp-jobsearch'),
            'desc' => esc_html__('Employers searching filters "Team Size" switch.', 'wp-jobsearch'),
            'options' => array(
                'yes' => esc_html__('Yes', 'wp-jobsearch'),
                'no' => esc_html__('No', 'wp-jobsearch'),
            )
        ),
        'employer_filters_team_collapse' => array(
            'type' => 'select',
            'label' => esc_html__('Team Size Filter Collapse', 'wp-jobsearch'),
            'desc' => '',
            'options' => array(
                'no' => esc_html__('No', 'wp-jobsearch'),
                'yes' => esc_html__('Yes', 'wp-jobsearch'),
            )
        ),
        'emp_top_map' => array(
            'type' => 'select',
            'label' => esc_html__('Top Map', 'wp-jobsearch'),
            'desc' => esc_html__('Employers top map switch.', 'wp-jobsearch'),
            'options' => array(
                'no' => esc_html__('No', 'wp-jobsearch'),
                'yes' => esc_html__('Yes', 'wp-jobsearch'),
            )
        ),
        'emp_top_map_height' => array(
            'std' => '450',
            'type' => 'text',
            'label' => esc_html__('Map Height', 'wp-jobsearch'),
            'desc' => esc_html__('Employers top map height.', 'wp-jobsearch'),
        ),
        'emp_top_map_zoom' => array(
            'std' => '8',
            'type' => 'text',
            'label' => esc_html__('Map Zoom', 'wp-jobsearch'),
            'desc' => esc_html__('Employers top map zoom.', 'wp-jobsearch'),
        ),
        'emp_top_search' => array(
            'type' => 'select',
            'label' => esc_html__('Top Search Bar', 'wp-jobsearch'),
            'desc' => esc_html__('Results top search bar section switch.', 'wp-jobsearch'),
            'options' => array(
                'no' => esc_html__('No', 'wp-jobsearch'),
                'yes' => esc_html__('Yes', 'wp-jobsearch'),
            )
        ),
        'emp_top_search_view' => array(
            'type' => 'select',
            'label' => esc_html__('Top Search Style', 'wp-jobsearch'),
            'desc' => '',
            'options' => array(
                'simple' => esc_html__('Simple', 'wp-jobsearch'),
                'advance' => esc_html__('Advance Search', 'wp-jobsearch'),
            )
        ),
        'employer_sort_by' => array(
            'type' => 'select',
            'label' => esc_html__('Sort by Fields', 'wp-jobsearch'),
            'desc' => esc_html__('Results search sorting section switch.', 'wp-jobsearch'),
            'options' => array(
                'yes' => esc_html__('Yes', 'wp-jobsearch'),
                'no' => esc_html__('No', 'wp-jobsearch'),
            )
        ),
        'employer_excerpt' => array(
            'std' => '20',
            'type' => 'text',
            'label' => esc_html__('Excerpt Length', 'wp-jobsearch'),
            'desc' => esc_html__('Set number of words you want show for excerpt.', 'wp-jobsearch'),
        ),
        'employer_order' => array(
            'type' => 'select',
            'label' => esc_html__('Order', 'wp-jobsearch'),
            'desc' => esc_html__('Choose employer list items order.', 'wp-jobsearch'),
            'options' => array(
                'DESC' => esc_html__('Descending', 'wp-jobsearch'),
                'ASC' => esc_html__('Ascending', 'wp-jobsearch'),
            )
        ),
        'employer_orderby' => array(
            'type' => 'select',
            'label' => esc_html__('Orderby', 'wp-jobsearch'),
            'desc' => esc_html__('Choose employer list items orderby.', 'wp-jobsearch'),
            'options' => array(
                'ID' => esc_html__('Date', 'wp-jobsearch'),
                'title' => esc_html__('Title', 'wp-jobsearch'),
            )
        ),
        'employer_pagination' => array(
            'type' => 'select',
            'label' => esc_html__('Pagination', 'wp-jobsearch'),
            'desc' => esc_html__('Choose yes if you want to show pagination for employer items.', 'wp-jobsearch'),
            'options' => array(
                'yes' => esc_html__('Yes', 'wp-jobsearch'),
                'no' => esc_html__('No', 'wp-jobsearch'),
            )
        ),
        'employer_per_page' => array(
            'std' => '10',
            'type' => 'text',
            'label' => esc_html__('Records per Page', 'wp-jobsearch'),
            'desc' => esc_html__('Set number that how much employers you want to show per page. Leave it blank for all employers on a single page.', 'wp-jobsearch'),
        ),
    ))
);
