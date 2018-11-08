<?php
/**
 * Advance Search Shortcode
 * @return html
 */
add_shortcode('jobsearch_advance_search', 'jobsearch_advance_search_shortcode');

function jobsearch_advance_search_shortcode($atts) {
    global $jobsearch_plugin_options, $sitepress;

    $lang_code = '';
    if (function_exists('icl_object_id')) {
        $lang_code = $sitepress->get_current_language();
    }

    extract(shortcode_atts(array(
        'result_page' => '',
        'keyword_field' => 'show',
        'loc_field' => 'show',
        'loc_field1' => '',
        'loc_field2' => '',
        'loc_field3' => '',
        'loc_field4' => '',
        'loc_locate_1' => '',
        'cat_field' => 'show',
        'serch_txt_color' => '',
        'serch_bg_color' => '',
        'serch_hov_color' => '',
        'serch_btn_txt' => '',
                    ), $atts));

    $html = '';
    if ($keyword_field == 'show' || $loc_field == 'show' || $cat_field == 'show') {
        ob_start();

        $rand_num = rand(1000000, 9999999);
        if ($result_page != '') {
            $result_page_obj = jobsearch_get_page_by_slug($result_page, 'OBJECT', 'page');
            $result_page = isset($result_page_obj->ID) ? $result_page_obj->ID : 0;
        }

        $loc_location1 = isset($_REQUEST['location_location1']) ? $_REQUEST['location_location1'] : '';

        if ($loc_locate_1 != '') {
            $loc_location1 = $loc_locate_1;
        }

        $loc_location2 = isset($_REQUEST['location_location2']) ? $_REQUEST['location_location2'] : '';
        $loc_location3 = isset($_REQUEST['location_location3']) ? $_REQUEST['location_location3'] : '';
        $loc_location4 = isset($_REQUEST['location_location4']) ? $_REQUEST['location_location4'] : '';

        $required_fields_count = isset($jobsearch_plugin_options['jobsearch-location-required-fields-count']) ? $jobsearch_plugin_options['jobsearch-location-required-fields-count'] : 'all';
        $label_location1 = isset($jobsearch_plugin_options['jobsearch-location-label-location1']) ? apply_filters('wpml_translate_single_string', $jobsearch_plugin_options['jobsearch-location-label-location1'], 'JobSearch Options', 'Location First Field - ' . $jobsearch_plugin_options['jobsearch-location-label-location1'], $lang_code) : esc_html__('Country', 'wp-jobsearch');
        $label_location2 = isset($jobsearch_plugin_options['jobsearch-location-label-location2']) ? apply_filters('wpml_translate_single_string', $jobsearch_plugin_options['jobsearch-location-label-location2'], 'JobSearch Options', 'Location Second Field - ' . $jobsearch_plugin_options['jobsearch-location-label-location2'], $lang_code) : esc_html__('State', 'wp-jobsearch');
        $label_location3 = isset($jobsearch_plugin_options['jobsearch-location-label-location3']) ? apply_filters('wpml_translate_single_string', $jobsearch_plugin_options['jobsearch-location-label-location3'], 'JobSearch Options', 'Location Third Field - ' . $jobsearch_plugin_options['jobsearch-location-label-location3'], $lang_code) : esc_html__('Region', 'wp-jobsearch');
        $label_location4 = isset($jobsearch_plugin_options['jobsearch-location-label-location4']) ? apply_filters('wpml_translate_single_string', $jobsearch_plugin_options['jobsearch-location-label-location4'], 'JobSearch Options', 'Location Forth Field - ' . $jobsearch_plugin_options['jobsearch-location-label-location4'], $lang_code) : esc_html__('City', 'wp-jobsearch');

        $please_select = esc_html__('Please select', 'wp-jobsearch');
        $location_location1 = array('' => apply_filters('jobsearch_adv_srch_sh_front_contry_label', $please_select . ' ' . $label_location1));
        $location_location2 = array('' => apply_filters('jobsearch_adv_srch_sh_front_state_label', $please_select . ' ' . $label_location2));
        $location_location3 = array('' => apply_filters('jobsearch_adv_srch_sh_front_region_label', $please_select . ' ' . $label_location3));
        $location_location4 = array('' => apply_filters('jobsearch_adv_srch_sh_front_city_label', $please_select . ' ' . $label_location4));

        $location_obj = get_terms('job-location', array(
            'orderby' => 'count',
            'hide_empty' => 0,
            'parent' => 0,
        ));
        foreach ($location_obj as $country_arr) {
            $location_location1[$country_arr->slug] = $country_arr->name;
        }

        //
        if ($loc_locate_1 != '') {
            $tax2_getby = get_term_by('slug', $loc_locate_1, 'job-location');
            $location2_obj = get_terms('job-location', array(
                'orderby' => 'count',
                'hide_empty' => 0,
                'parent' => $tax2_getby->term_id,
            ));
            foreach ($location2_obj as $city_arr) {
                $location_location2[$city_arr->slug] = $city_arr->name;
            }
            $location_location2['other-cities'] = esc_html__('Other Locations', 'wp-jobsearch');
        }
        //

        if ($serch_hov_color != '') {
            ?>
            <style>
                .jobsearch-search-container .jobsearch-banner-search input[type="submit"]:hover,
                .dynamic-class-<?php echo ($rand_num) ?> input:hover {
                    background : <?php echo ($serch_hov_color) ?> !important;
                }
            </style>
            <?php
        }
        ?>
        <div class="jobsearch-search-container">
            <form class="jobsearch-banner-search" method="get" action="<?php echo (get_permalink($result_page)); ?>">
                <ul>
                    <?php
                    ob_start();
                    if ($keyword_field == 'show') {
                        ?>
                        <li>
                            <input placeholder="<?php echo apply_filters('jobsearch_own_sh_adv_srch_keywords_str', esc_html__('Job Title, Keywords, or Phrase', 'wp-jobsearch')) ?>" name="search_title" type="text">
                        </li>
                        <?php
                    }
                    $srchbox_html = ob_get_clean();

                    ob_start();
                    if ($loc_field == 'show') {
                        if ($loc_locate_1 != '') {
                            ?>
                            <li>
                                <input type="hidden" name="location_location1" value="<?php echo ($loc_location1) ?>">
                                <div class="jobsearch-select-style location-level-select">
                                    <select id="location_location2_<?php echo ($rand_num) ?>" name="location_location2" class="location_location2 selectize-select" data-randid="<?php echo ($rand_num) ?>" data-nextfieldelement="<?php echo ($please_select . ' ' . $label_location3) ?>" data-nextfieldval="<?php echo ($loc_location3) ?>">
                                        <?php
                                        if (!empty($location_location2)) {
                                            foreach ($location_location2 as $loc2_key => $loc2_val) {
                                                ?>
                                                <option value="<?php echo ($loc2_key) ?>"<?php echo ($loc_location2 == $loc2_key ? ' selected="selected"' : '') ?>><?php echo ($loc2_val) ?></option>
                                                <?php
                                            }
                                        }
                                        ?>
                                    </select>
                                    <span class="jobsearch-field-loader location_location2_<?php echo absint($rand_num); ?>"></span>
                                </div>
                            </li>
                            <?php
                        } else {
                            if ($loc_field1 == 'show') {
                                ?>
                                <li>
                                    <div class="jobsearch-select-style location-level-select">
                                        <select id="location_location1_<?php echo ($rand_num) ?>" name="location_location1" class="location_location1 selectize-select" data-randid="<?php echo ($rand_num) ?>" data-nextfieldelement="<?php echo ($please_select . ' ' . $label_location2) ?>" data-nextfieldval="<?php echo ($loc_location2) ?>">
                                            <?php
                                            if (!empty($location_location1)) {
                                                foreach ($location_location1 as $loc1_key => $loc1_val) {
                                                    ?>
                                                    <option value="<?php echo ($loc1_key) ?>"<?php echo ($loc_location1 == $loc1_key ? ' selected="selected"' : '') ?>><?php echo ($loc1_val) ?></option>
                                                    <?php
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <?php
                                    if ($loc_location1 != '') {
                                        ?>
                                        <script>
                                            jQuery(document).ready(function () {
                                                jQuery('.location_location1').trigger('change');
                                            });
                                        </script>
                                        <?php
                                    }
                                    ?>
                                </li>
                                <?php
                            }
                            if (($required_fields_count > 1 || $required_fields_count == 'all') && $loc_field2 == 'show') {
                                ?>
                                <li>
                                    <div class="jobsearch-select-style location-level-select">
                                        <select id="location_location2_<?php echo ($rand_num) ?>" name="location_location2" class="location_location2 location_location2_selectize" data-randid="<?php echo ($rand_num) ?>" data-nextfieldelement="<?php echo ($please_select . ' ' . $label_location3) ?>" data-nextfieldval="<?php echo ($loc_location3) ?>">
                                            <?php
                                            if (!empty($location_location2)) {
                                                foreach ($location_location2 as $loc2_key => $loc2_val) {
                                                    ?>
                                                    <option value="<?php echo ($loc2_key) ?>"<?php echo ($loc_location2 == $loc2_key ? ' selected="selected"' : '') ?>><?php echo ($loc2_val) ?></option>
                                                    <?php
                                                }
                                            }
                                            ?>
                                        </select>
                                        <span class="jobsearch-field-loader location_location2_<?php echo absint($rand_num); ?>"></span>
                                    </div>
                                </li>
                                <?php
                            }
                            if (($required_fields_count > 2 || $required_fields_count == 'all') && $loc_field3 == 'show') {
                                ?>
                                <li>
                                    <div class="jobsearch-select-style location-level-select">
                                        <select id="location_location3_<?php echo ($rand_num) ?>" name="location_location3" class="location_location3 location_location3_selectize" data-randid="<?php echo ($rand_num) ?>" data-nextfieldelement="<?php echo ($please_select . ' ' . $label_location4) ?>" data-nextfieldval="<?php echo ($loc_location4) ?>">
                                            <?php
                                            if (!empty($location_location3)) {
                                                foreach ($location_location3 as $loc3_key => $loc3_val) {
                                                    ?>
                                                    <option value="<?php echo ($loc3_key) ?>"<?php echo ($loc_location3 == $loc3_key ? ' selected="selected"' : '') ?>><?php echo ($loc3_val) ?></option>
                                                    <?php
                                                }
                                            }
                                            ?>
                                        </select>
                                        <span class="jobsearch-field-loader location_location3_<?php echo absint($rand_num); ?>"></span>
                                    </div>
                                </li>
                                <?php
                            }
                            if (($required_fields_count > 3 || $required_fields_count == 'all') && $loc_field4 == 'show') {
                                ?>
                                <li>
                                    <div class="jobsearch-select-style location-level-select">
                                        <select id="location_location4_<?php echo ($rand_num) ?>" name="location_location4" class="location_location4 location_location4_selectize" data-randid="<?php echo ($rand_num) ?>">
                                            <?php
                                            if (!empty($location_location4)) {
                                                foreach ($location_location4 as $loc4_key => $loc4_val) {
                                                    ?>
                                                    <option value="<?php echo ($loc4_key) ?>"<?php echo ($loc_location4 == $loc4_key ? ' selected="selected"' : '') ?>><?php echo ($loc4_val) ?></option>
                                                    <?php
                                                }
                                            }
                                            ?>
                                        </select>
                                        <span class="jobsearch-field-loader location_location4_<?php echo absint($rand_num); ?>"></span>
                                    </div>
                                </li>
                                <?php
                            }
                        }
                    }
                    $locations_html = ob_get_clean();

                    ob_start();
                    $all_sectors = get_terms(array(
                        'taxonomy' => 'sector',
                        'hide_empty' => false,
                    ));

                    if (!empty($all_sectors) && !is_wp_error($all_sectors) && $cat_field == 'show') {
                        ?>
                        <li>
                            <div class="jobsearch-select-style">
                                <select name="sector_cat" class="selectize-select">
                                    <option value=""><?php echo apply_filters('jobsearch_own_sh_adv_srch_select_cat_str', esc_html__('Select Sector', 'jobsearch-frame')) ?></option>
                                    <?php
                                    foreach ($all_sectors as $term_sector) {
                                        ?>
                                        <option value="<?php echo ($term_sector->slug) ?>"><?php echo ($term_sector->name) ?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                            </div>
                        </li>
                        <?php
                    }
                    $sectors_html = ob_get_clean();

                    $ov_serch_html = $srchbox_html . $locations_html . $sectors_html;

                    echo apply_filters('jobsearch_adv_srch_front_sh_html', $ov_serch_html, $srchbox_html, $locations_html, $sectors_html);

                    $srch_btn_style = '';
                    if ($serch_txt_color != '') {
                        $srch_btn_style .= ' color: ' . $serch_txt_color . ';';
                    }
                    if ($serch_bg_color != '') {
                        $srch_btn_style .= ' background-color: ' . $serch_bg_color . ';';
                    }

                    if ($srch_btn_style != '') {
                        $srch_btn_style = ' style="' . $srch_btn_style . '"';
                    }
                    if ($serch_btn_txt != '') {
                        ?>
                        <li class="jobsearch-banner-submit with-btn-txt dynamic-class-<?php echo ($rand_num) ?>"> <input type="submit" value="<?php echo ($serch_btn_txt) ?>"<?php echo ($srch_btn_style) ?>> </li>
                        <?php
                    } else {
                        ?>
                        <li class="jobsearch-banner-submit"> <input type="submit" value=""> <i class="jobsearch-icon jobsearch-search"<?php echo ($srch_btn_style) ?>></i> </li>
                            <?php
                        }
                        ?>
                </ul>
            </form>
        </div>
        <?php
        $html = ob_get_clean();
    }

    return $html;
}
