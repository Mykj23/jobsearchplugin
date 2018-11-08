<?php
/*
  Class : Location
 */


// this is an include only WP file
if (!defined('ABSPATH')) {
    die;
}

// main plugin class
class Jobsearch_Location {

// hook things up
    public function __construct() {
        add_action('jobsearch_admin_location_map', array($this, 'jobsearch_admin_location_map_callback'), 10, 1);
        add_action('jobsearch_dashboard_location_map', array($this, 'jobsearch_dashboard_location_map_callback'), 10, 2);
        add_action('wp_ajax_jobsearch_location_load_location2_data', array($this, 'jobsearch_location_load_location2_data_callback'));
        add_action('wp_ajax_nopriv_jobsearch_location_load_location2_data', array($this, 'jobsearch_location_load_location2_data_callback'));
        add_filter('redux/options/jobsearch_plugin_options/sections', array($this, 'jobsearch_location_plugin_option_fields'));
        add_action('init', array($this, 'titles_translation'));
        $this->load_files();
    }

    public function titles_translation() {
        global $jobsearch_plugin_options;

        $label_location1 = isset($jobsearch_plugin_options['jobsearch-location-label-location1']) ? $jobsearch_plugin_options['jobsearch-location-label-location1'] : esc_html__('Country', 'wp-jobsearch');
        do_action('wpml_register_single_string', 'JobSearch Options', 'Location First Field - ' . $label_location1, $label_location1);
        $label_location2 = isset($jobsearch_plugin_options['jobsearch-location-label-location2']) ? $jobsearch_plugin_options['jobsearch-location-label-location2'] : esc_html__('State', 'wp-jobsearch');
        do_action('wpml_register_single_string', 'JobSearch Options', 'Location Second Field - ' . $label_location2, $label_location2);
        $label_location3 = isset($jobsearch_plugin_options['jobsearch-location-label-location3']) ? $jobsearch_plugin_options['jobsearch-location-label-location3'] : esc_html__('Region', 'wp-jobsearch');
        do_action('wpml_register_single_string', 'JobSearch Options', 'Location Third Field - ' . $label_location3, $label_location3);
        $label_location4 = isset($jobsearch_plugin_options['jobsearch-location-label-location4']) ? $jobsearch_plugin_options['jobsearch-location-label-location4'] : esc_html__('City', 'wp-jobsearch');
        do_action('wpml_register_single_string', 'JobSearch Options', 'Location Forth Field - ' . $label_location4, $label_location4);
    }

    public function location_front_enqueue_scripts() {
        
    }

    public function load_files() {
        include plugin_dir_path(dirname(__FILE__)) . 'locations/include/register-taxonomy.php';
        include plugin_dir_path(dirname(__FILE__)) . 'locations/include/locations-html.php';
    }

    public function jobsearch_admin_location_map_callback($id = '') {
        global $jobsearch_form_fields, $jobsearch_plugin_options, $sitepress;

        $lang_code = '';
        $admin_ajax_url = admin_url('admin-ajax.php');
        if (function_exists('icl_object_id')) {
            $lang_code = $sitepress->get_current_language();
            $admin_ajax_url = add_query_arg(array('lang' => $lang_code), $admin_ajax_url);
        }

        wp_register_script('jobsearch-location', jobsearch_plugin_get_url('modules/locations/js/location-functions.js'), array('jquery'), '', true);
        // Localize the script
        $jobsearch_location_common_arr = array(
            'plugin_url' => jobsearch_plugin_get_url(),
            'ajax_url' => $admin_ajax_url,
        );
        $required_fields_count = isset($jobsearch_plugin_options['jobsearch-location-required-fields-count']) ? $jobsearch_plugin_options['jobsearch-location-required-fields-count'] : 'all';
        $label_location1 = isset($jobsearch_plugin_options['jobsearch-location-label-location1']) ? apply_filters('wpml_translate_single_string', $jobsearch_plugin_options['jobsearch-location-label-location1'], 'JobSearch Options', 'Location First Field - ' . $jobsearch_plugin_options['jobsearch-location-label-location1'], $lang_code) : esc_html__('Country', 'wp-jobsearch');
        $label_location2 = isset($jobsearch_plugin_options['jobsearch-location-label-location2']) ? apply_filters('wpml_translate_single_string', $jobsearch_plugin_options['jobsearch-location-label-location2'], 'JobSearch Options', 'Location Second Field - ' . $jobsearch_plugin_options['jobsearch-location-label-location2'], $lang_code) : esc_html__('State', 'wp-jobsearch');
        $label_location3 = isset($jobsearch_plugin_options['jobsearch-location-label-location3']) ? apply_filters('wpml_translate_single_string', $jobsearch_plugin_options['jobsearch-location-label-location3'], 'JobSearch Options', 'Location Third Field - ' . $jobsearch_plugin_options['jobsearch-location-label-location3'], $lang_code) : esc_html__('Region', 'wp-jobsearch');
        $label_location4 = isset($jobsearch_plugin_options['jobsearch-location-label-location4']) ? apply_filters('wpml_translate_single_string', $jobsearch_plugin_options['jobsearch-location-label-location4'], 'JobSearch Options', 'Location Forth Field - ' . $jobsearch_plugin_options['jobsearch-location-label-location4'], $lang_code) : esc_html__('City', 'wp-jobsearch');

        $default_location = isset($jobsearch_plugin_options['jobsearch-location-default-address']) ? $jobsearch_plugin_options['jobsearch-location-default-address'] : '';

        $allow_full_address = isset($jobsearch_plugin_options['location-allow-full-address']) ? $jobsearch_plugin_options['location-allow-full-address'] : '';
        $allow_location_map = isset($jobsearch_plugin_options['location-allow-map']) ? $jobsearch_plugin_options['location-allow-map'] : '';

        $def_map_zoom = isset($jobsearch_plugin_options['jobsearch-location-map-zoom']) && $jobsearch_plugin_options['jobsearch-location-map-zoom'] > 0 ? absint($jobsearch_plugin_options['jobsearch-location-map-zoom']) : '12';

        $map_styles = isset($jobsearch_plugin_options['jobsearch-location-map-style']) ? $jobsearch_plugin_options['jobsearch-location-map-style'] : '';

        wp_localize_script('jobsearch-location', 'jobsearch_location_common_vars', $jobsearch_location_common_arr);
        wp_enqueue_script('jobsearch-location');
        wp_enqueue_script('jobsearch-google-map');
        $rand_num = rand(1000000, 99999999);
        $loc_location1 = get_post_meta($id, 'jobsearch_field_location_location1', true);
        $loc_location2 = get_post_meta($id, 'jobsearch_field_location_location2', true);
        $loc_location3 = get_post_meta($id, 'jobsearch_field_location_location3', true);
        $loc_location4 = get_post_meta($id, 'jobsearch_field_location_location4', true);
        $loc_address = get_post_meta($id, 'jobsearch_field_location_address', true);
        $loc_lat = get_post_meta($id, 'jobsearch_field_location_lat', true);
        $loc_lng = get_post_meta($id, 'jobsearch_field_location_lng', true);
        $loc_zoom = get_post_meta($id, 'jobsearch_field_location_zoom', true);
        $map_height = get_post_meta($id, 'jobsearch_field_map_height', true);
        $marker_image = get_post_meta($id, 'jobsearch_field_marker_image', true);
        if (($loc_lat == '' || $loc_lng == '') && $default_location != '') {

            $loc_geo_cords = jobsearch_address_to_cords($default_location);
            $loc_lat = isset($loc_geo_cords['lat']) ? $loc_geo_cords['lat'] : '';
            $loc_lng = isset($loc_geo_cords['lng']) ? $loc_geo_cords['lng'] : '';
        }

        if ($loc_lat == '' || $loc_lng == '') {
            $loc_lat = '37.090240';
            $loc_lng = '-95.712891';
        }

        if ($map_height == '' || $map_height <= 100) {
            $map_height = 250;
        }
        if ($loc_zoom == '') {
            $loc_zoom = $def_map_zoom;
        }
        $please_select = esc_html__('Please select', 'wp-jobsearch');
        $location_location1 = array('' => $please_select . ' ' . $label_location1);
        $location_location2 = array('' => $please_select . ' ' . $label_location2);
        $location_location3 = array('' => $please_select . ' ' . $label_location3);
        $location_location4 = array('' => $please_select . ' ' . $label_location4);
        $location_obj = get_terms('job-location', array(
            'orderby' => 'count',
            'hide_empty' => 0,
            'parent' => 0,
        ));
        foreach ($location_obj as $country_arr) {
            $location_location1[$country_arr->slug] = $country_arr->name;
            // get all state, region and city
            // not neccessory for first load, it will populate on seelct country
        }
        ?>
        <div class="jobsearch-element-field">
            <div class="elem-label">
                <label><?php echo esc_html($label_location1) ?></label>
            </div>
            <div class="elem-field">
                <?php
                $field_params = array(
                    'classes' => 'location_location1',
                    'id' => 'location_location1_' . $rand_num,
                    'name' => 'location_location1',
                    'options' => $location_location1,
                    'force_std' => $loc_location1,
                    'ext_attr' => ' data-randid="' . $rand_num . '" data-nextfieldelement="' . $please_select . ' ' . $label_location2 . '" data-nextfieldval="' . $loc_location2 . '"',
                );
                $jobsearch_form_fields->select_field($field_params);
                ?>
            </div>
        </div> 
        <?php if ($required_fields_count > 1 || $required_fields_count == 'all') { ?>
            <div class="jobsearch-element-field">
                <div class="elem-label">
                    <label><?php echo esc_html($label_location2) ?></label>
                </div>
                <div class="elem-field">
                    <?php
                    $field_params = array(
                        'classes' => 'location_location2',
                        'id' => 'location_location2_' . $rand_num,
                        'name' => 'location_location2',
                        'options' => $location_location2,
                        'force_std' => $loc_location2,
                        'ext_attr' => ' data-randid="' . $rand_num . '" data-nextfieldelement="' . $please_select . ' ' . $label_location3 . '" data-nextfieldval="' . $loc_location3 . '"',
                    );
                    $jobsearch_form_fields->select_field($field_params);
                    ?>
                    <span class="jobsearch-field-loader location_location2_<?php echo absint($rand_num); ?>"></span>
                </div>
            </div>
        <?php }if ($required_fields_count > 2 || $required_fields_count == 'all') { ?>
            <div class="jobsearch-element-field">
                <div class="elem-label">
                    <label><?php echo esc_html($label_location3) ?></label>
                </div>
                <div class="elem-field">
                    <?php
                    $field_params = array(
                        'classes' => 'location_location3',
                        'id' => 'location_location3_' . $rand_num,
                        'name' => 'location_location3',
                        'options' => $location_location3,
                        'force_std' => $loc_location3,
                        'ext_attr' => ' data-randid="' . $rand_num . '" data-nextfieldelement="' . $please_select . ' ' . $label_location4 . '" data-nextfieldval="' . $loc_location4 . '"',
                    );
                    $jobsearch_form_fields->select_field($field_params);
                    ?>
                    <span class="jobsearch-field-loader location_location3_<?php echo absint($rand_num); ?>"></span>
                </div>
            </div>
        <?php }if ($required_fields_count > 3 || $required_fields_count == 'all') { ?>
            <div class="jobsearch-element-field">
                <div class="elem-label">
                    <label><?php echo esc_html($label_location4) ?></label>
                </div>
                <div class="elem-field">
                    <?php
                    $field_params = array(
                        'classes' => 'location_location4',
                        'id' => 'location_location4_' . $rand_num,
                        'name' => 'location_location4',
                        'options' => $location_location4,
                        'force_std' => $loc_location4,
                        'ext_attr' => ' data-randid="' . $rand_num . '"',
                    );
                    $jobsearch_form_fields->select_field($field_params);
                    ?>
                    <span class="jobsearch-field-loader location_location4_<?php echo absint($rand_num); ?>"></span>
                </div>
            </div>
            <?php
        }
        if ($allow_full_address == 'yes') {
            ?>
            <div class="jobsearch-element-field">
                <div class="elem-label">
                    <label><?php esc_html_e('Address', 'wp-jobsearch') ?></label>
                </div>
                <div class="elem-field">
                    <?php
                    $field_params = array(
                        'id' => 'jobsearch_location_address_' . $rand_num,
                        'name' => 'location_address',
                        'force_std' => $loc_address,
                    );
                    $jobsearch_form_fields->input_field($field_params);
                    ?>
                </div>
            </div>
            <?php
        }
        if ($allow_location_map == 'yes') {
            ?>
            <div class="jobsearch-element-field">
                <div class="elem-label">
                    <label><?php esc_html_e('Latitude', 'wp-jobsearch') ?></label>
                </div>
                <div class="elem-field">
                    <?php
                    $field_params = array(
                        'id' => 'jobsearch_location_lat_' . $rand_num,
                        'name' => 'location_lat',
                        'force_std' => $loc_lat,
                    );
                    $jobsearch_form_fields->input_field($field_params);
                    ?>
                </div>
            </div>
            <div class="jobsearch-element-field">
                <div class="elem-label">
                    <label><?php esc_html_e('Longitude', 'wp-jobsearch') ?></label>
                </div>
                <div class="elem-field">
                    <?php
                    $field_params = array(
                        'id' => 'jobsearch_location_lng_' . $rand_num,
                        'name' => 'location_lng',
                        'force_std' => $loc_lng,
                    );
                    $jobsearch_form_fields->input_field($field_params);
                    ?>
                </div>
            </div>
            <div class="jobsearch-element-field">
                <div class="elem-label">
                    <label><?php esc_html_e('Zoom Level', 'wp-jobsearch') ?></label>
                </div>
                <div class="elem-field">
                    <?php
                    $field_params = array(
                        'id' => 'jobsearch_location_zoom_' . $rand_num,
                        'name' => 'location_zoom',
                        'force_std' => $loc_zoom,
                    );
                    $jobsearch_form_fields->input_field($field_params);
                    ?>
                </div>
            </div>
            <div class="jobsearch-element-field">
                <div class="elem-label">
                    <label><?php esc_html_e('Map Height', 'wp-jobsearch') ?></label>
                </div>
                <div class="elem-field">
                    <?php
                    $field_params = array(
                        'name' => 'map_height',
                        'force_std' => $map_height,
                    );
                    $jobsearch_form_fields->input_field($field_params);
                    ?>
                </div>
            </div>
            <div class="jobsearch-element-field">
                <div class="elem-label">
                    <label><?php esc_html_e('Marker', 'wp-jobsearch') ?></label>
                </div>
                <div class="elem-field">
                    <?php
                    $field_params = array(
                        'id' => 'marker_image_' . $rand_num,
                        'name' => 'marker_image',
                        'force_std' => $marker_image,
                    );
                    $jobsearch_form_fields->image_upload_field($field_params);
                    ?>
                </div>
            </div>
            <div id="jobsearch-map-<?php echo absint($rand_num); ?>" style="width: 100%; height: <?php echo esc_html($map_height) ?>px;"></div>
            <script>
                var map;
                var markers = [];
                jQuery(document).ready(function () {
                    function jobsearch_map_autocomplete_fields_<?php echo ($rand_num) ?>() {
                        var autocomplete_input = document.getElementById('jobsearch_location_address_<?php echo ($rand_num) ?>');
                        var autocomplete = new google.maps.places.Autocomplete(autocomplete_input);
                    }
                    google.maps.event.addDomListener(window, 'load', jobsearch_map_autocomplete_fields_<?php echo ($rand_num) ?>);
            <?php
            if ($loc_lat != '' && $loc_lng != '' && $loc_zoom != '') {
                ?>
                        function initMap<?php echo ($rand_num) ?>() {
                            var myLatLng = {lat: <?php echo esc_js($loc_lat) ?>, lng: <?php echo esc_js($loc_lng) ?>};
                            map = new google.maps.Map(document.getElementById('jobsearch-map-<?php echo absint($rand_num); ?>'), {
                                zoom: <?php echo esc_js($loc_zoom) ?>,
                                center: myLatLng,
                                streetViewControl: false,
                                scrollwheel: false,
                                mapTypeControl: false,
                            });
                <?php
                if ($map_styles != '') {
                    $map_styles = stripslashes($map_styles);
                    $map_styles = preg_replace('/\s+/', ' ', trim($map_styles));
                    ?>
                                var styles = '<?php echo ($map_styles) ?>';
                                if (styles != '') {
                                    styles = jQuery.parseJSON(styles);
                                    var styledMap = new google.maps.StyledMapType(
                                            styles,
                                            {name: 'Styled Map'}
                                    );
                                    map.mapTypes.set('map_style', styledMap);
                                    map.setMapTypeId('map_style');
                                }
                    <?php
                }
                ?>

                            var marker = new google.maps.Marker({
                                position: myLatLng,
                                map: map,
                                draggable: true,
                                title: '',
                                icon: '<?php echo esc_js($marker_image) ?>',
                            });

                            markers.push(marker);

                            google.maps.event.addListener(map, 'zoom_changed', function () {
                                var zoom_lvl = map.getZoom();
                                document.getElementById("jobsearch_location_zoom_<?php echo absint($rand_num); ?>").value = zoom_lvl;
                            });
                            google.maps.event.addListener(marker, 'dragend', function (event) {
                                document.getElementById("jobsearch_location_lat_<?php echo absint($rand_num); ?>").value = this.getPosition().lat();
                                document.getElementById("jobsearch_location_lng_<?php echo absint($rand_num); ?>").value = this.getPosition().lng();
                            });
                        }

                        google.maps.event.addDomListener(window, 'load', initMap<?php echo ($rand_num) ?>);

                        jQuery(document).on('change', '#jobsearch_location_address_<?php echo ($rand_num) ?>', function () {
                            var $ = jQuery;
                            var geocoder = new google.maps.Geocoder();
                            var addres = $(this).val();
                            var lat_con = $('#jobsearch_location_lat_<?php echo ($rand_num) ?>');
                            var lng_con = $('#jobsearch_location_lng_<?php echo ($rand_num) ?>');
                            geocoder.geocode({address: addres}, function (results, status) {
                                if (status == google.maps.GeocoderStatus.OK) {
                                    var new_latitude = results[0].geometry.location.lat();
                                    var new_longitude = results[0].geometry.location.lng();
                                    lat_con.val(new_latitude);
                                    lng_con.val(new_longitude);
                                    map.setCenter(results[0].geometry.location);//center the map over the result

                                    // clear markers
                                    for (var i = 0; i < markers.length; i++) {
                                        markers[i].setMap(null);
                                    }
                                    //

                                    //place a marker at the location
                                    var marker = new google.maps.Marker({
                                        map: map,
                                        position: results[0].geometry.location,
                                        draggable: true,
                                        title: '',
                                        icon: '<?php echo esc_js($marker_image) ?>',
                                    });

                                    markers.push(marker);

                                    google.maps.event.addListener(marker, 'dragend', function (event) {
                                        document.getElementById("jobsearch_location_lat_<?php echo ($rand_num) ?>").value = this.getPosition().lat();
                                        document.getElementById("jobsearch_location_lng_<?php echo ($rand_num) ?>").value = this.getPosition().lng();
                                    });
                                }
                            });
                        });
                <?php
            }
// load next location
            if ($loc_location1 != '') {
                ?>
                        jQuery('.location_location1').trigger('change');
                <?php
            }
            ?>
                });
            // load state

            </script>
            <?php
        }
    }

    public function jobsearch_dashboard_location_map_callback($id = '', $require_full_addr = false) {
        global $jobsearch_form_fields, $jobsearch_plugin_options, $sitepress;

        $lang_code = '';
        $admin_ajax_url = admin_url('admin-ajax.php');
        if (function_exists('icl_object_id')) {
            $lang_code = $sitepress->get_current_language();
            $admin_ajax_url = add_query_arg(array('lang' => $lang_code), $admin_ajax_url);
        }

        wp_register_script('jobsearch-location', jobsearch_plugin_get_url('modules/locations/js/location-functions.js'), array('jquery'), '', true);
        // Localize the script
        $jobsearch_location_common_arr = array(
            'plugin_url' => jobsearch_plugin_get_url(),
            'ajax_url' => $admin_ajax_url,
        );
        $required_fields_count = isset($jobsearch_plugin_options['jobsearch-location-required-fields-count']) ? $jobsearch_plugin_options['jobsearch-location-required-fields-count'] : 'all';
        $label_location1 = isset($jobsearch_plugin_options['jobsearch-location-label-location1']) ? apply_filters('wpml_translate_single_string', $jobsearch_plugin_options['jobsearch-location-label-location1'], 'JobSearch Options', 'Location First Field - ' . $jobsearch_plugin_options['jobsearch-location-label-location1'], $lang_code) : esc_html__('Country', 'wp-jobsearch');
        $label_location2 = isset($jobsearch_plugin_options['jobsearch-location-label-location2']) ? apply_filters('wpml_translate_single_string', $jobsearch_plugin_options['jobsearch-location-label-location2'], 'JobSearch Options', 'Location Second Field - ' . $jobsearch_plugin_options['jobsearch-location-label-location2'], $lang_code) : esc_html__('State', 'wp-jobsearch');
        $label_location3 = isset($jobsearch_plugin_options['jobsearch-location-label-location3']) ? apply_filters('wpml_translate_single_string', $jobsearch_plugin_options['jobsearch-location-label-location3'], 'JobSearch Options', 'Location Third Field - ' . $jobsearch_plugin_options['jobsearch-location-label-location3'], $lang_code) : esc_html__('Region', 'wp-jobsearch');
        $label_location4 = isset($jobsearch_plugin_options['jobsearch-location-label-location4']) ? apply_filters('wpml_translate_single_string', $jobsearch_plugin_options['jobsearch-location-label-location4'], 'JobSearch Options', 'Location Forth Field - ' . $jobsearch_plugin_options['jobsearch-location-label-location4'], $lang_code) : esc_html__('City', 'wp-jobsearch');

        $default_location = isset($jobsearch_plugin_options['jobsearch-location-default-address']) ? $jobsearch_plugin_options['jobsearch-location-default-address'] : '';

        $map_styles = isset($jobsearch_plugin_options['jobsearch-location-map-style']) ? $jobsearch_plugin_options['jobsearch-location-map-style'] : '';

        $allow_full_address = isset($jobsearch_plugin_options['location-allow-full-address']) ? $jobsearch_plugin_options['location-allow-full-address'] : '';
        $allow_location_map = isset($jobsearch_plugin_options['location-allow-map']) ? $jobsearch_plugin_options['location-allow-map'] : '';

        $def_map_zoom = isset($jobsearch_plugin_options['jobsearch-location-map-zoom']) && $jobsearch_plugin_options['jobsearch-location-map-zoom'] > 0 ? absint($jobsearch_plugin_options['jobsearch-location-map-zoom']) : '12';

        wp_localize_script('jobsearch-location', 'jobsearch_location_common_vars', $jobsearch_location_common_arr);
        wp_enqueue_script('jobsearch-location');
        wp_enqueue_script('jobsearch-google-map');
        $rand_num = rand(1000000, 99999999);
        $loc_location1 = get_post_meta($id, 'jobsearch_field_location_location1', true);
        $loc_location2 = get_post_meta($id, 'jobsearch_field_location_location2', true);
        $loc_location3 = get_post_meta($id, 'jobsearch_field_location_location3', true);
        $loc_location4 = get_post_meta($id, 'jobsearch_field_location_location4', true);
        $loc_address = get_post_meta($id, 'jobsearch_field_location_address', true);
        $loc_lat = get_post_meta($id, 'jobsearch_field_location_lat', true);
        $loc_lng = get_post_meta($id, 'jobsearch_field_location_lng', true);
        $loc_zoom = get_post_meta($id, 'jobsearch_field_location_zoom', true);
        $map_height = get_post_meta($id, 'jobsearch_field_map_height', true);
        $marker_image = get_post_meta($id, 'jobsearch_field_marker_image', true);

        if (($loc_lat == '' || $loc_lng == '') && $default_location != '') {

            $loc_geo_cords = jobsearch_address_to_cords($default_location);
            $loc_lat = isset($loc_geo_cords['lat']) ? $loc_geo_cords['lat'] : '';
            $loc_lng = isset($loc_geo_cords['lng']) ? $loc_geo_cords['lng'] : '';
        }

        if ($loc_lat == '' || $loc_lng == '') {
            $loc_lat = '37.090240';
            $loc_lng = '-95.712891';
        }

        if ($map_height == '' || $map_height <= 100) {
            $map_height = 250;
        }
        if ($loc_zoom == '') {
            $loc_zoom = $def_map_zoom;
        }
        $please_select = esc_html__('Please select', 'wp-jobsearch');
        $location_location1 = array('' => $please_select . ' ' . $label_location1);
        $location_location2 = array('' => $please_select . ' ' . $label_location2);
        $location_location3 = array('' => $please_select . ' ' . $label_location3);
        $location_location4 = array('' => $please_select . ' ' . $label_location4);
        $location_obj = get_terms('job-location', array(
            'orderby' => 'name',
            'order' => 'ASC',
            'hide_empty' => 0,
            'parent' => 0,
        ));
        foreach ($location_obj as $country_arr) {
            $location_location1[$country_arr->slug] = $country_arr->name;
            // get all state, region and city
            // not neccessory for first load, it will populate on select country
        }
        ?>
        <div class="jobsearch-employer-box-section">
            <div class="jobsearch-profile-title"><h2><?php esc_html_e('Address / Location', 'wp-jobsearch') ?></h2></div>
            <ul class="jobsearch-row jobsearch-employer-profile-form">
                <li class="jobsearch-column-6">
                    <label><?php echo esc_html($label_location1) ?><?php echo ($require_full_addr == true ? ' *' : '') ?></label>
                    <div class="jobsearch-profile-select">
                        <?php
                        $field_params = array(
                            'classes' => 'location_location1 selectize-select',
                            'id' => 'location_location1_' . $rand_num,
                            'name' => 'location_location1',
                            'options' => $location_location1,
                            'force_std' => $loc_location1,
                            'ext_attr' => ' data-randid="' . $rand_num . '" data-nextfieldelement="' . $please_select . ' ' . $label_location2 . '" data-nextfieldval="' . $loc_location2 . '"' . ($require_full_addr == true ? ' required="required"' : ''),
                        );
                        $jobsearch_form_fields->select_field($field_params);
                        ?>
                    </div>
                </li>
                <?php if ($required_fields_count > 1 || $required_fields_count == 'all') { ?>
                    <li class="jobsearch-column-6">
                        <label><?php echo esc_html($label_location2) ?><?php echo ($require_full_addr == true ? ' *' : '') ?></label>
                        <div class="jobsearch-profile-select">
                            <?php
                            $field_params = array(
                                'classes' => 'location_location2 location_location2_selectize',
                                'id' => 'location_location2_' . $rand_num,
                                'name' => 'location_location2',
                                'options' => $location_location2,
                                'force_std' => $loc_location2,
                                'ext_attr' => ' data-randid="' . $rand_num . '" data-nextfieldelement="' . $please_select . ' ' . $label_location3 . '" data-nextfieldval="' . $loc_location3 . '"' . ($require_full_addr == true ? ' required="required"' : ''),
                            );
                            $jobsearch_form_fields->select_field($field_params);
                            ?>
                            <span class="jobsearch-field-loader location_location2_<?php echo absint($rand_num); ?>"></span>
                        </div>
                    </li>
                <?php } if ($required_fields_count > 2 || $required_fields_count == 'all') { ?>
                    <li class="jobsearch-column-6">
                        <label><?php echo esc_html($label_location3) ?><?php echo ($require_full_addr == true ? ' *' : '') ?></label>
                        <div class="jobsearch-profile-select">
                            <?php
                            $field_params = array(
                                'classes' => 'location_location3 location_location3_selectize',
                                'id' => 'location_location3_' . $rand_num,
                                'name' => 'location_location3',
                                'options' => $location_location3,
                                'force_std' => $loc_location3,
                                'ext_attr' => ' data-randid="' . $rand_num . '" data-nextfieldelement="' . $please_select . ' ' . $label_location4 . '" data-nextfieldval="' . $loc_location4 . '"' . ($require_full_addr == true ? ' required="required"' : ''),
                            );
                            $jobsearch_form_fields->select_field($field_params);
                            ?>
                            <span class="jobsearch-field-loader location_location3_<?php echo absint($rand_num); ?>"></span>
                        </div>
                    </li>
                <?php }if ($required_fields_count > 3 || $required_fields_count == 'all') { ?>
                    <li class="jobsearch-column-6">
                        <label><?php echo esc_html($label_location4) ?><?php echo ($require_full_addr == true ? ' *' : '') ?></label>
                        <div class="jobsearch-profile-select">
                            <?php
                            $field_params = array(
                                'classes' => 'location_location4 location_location4_selectize',
                                'id' => 'location_location4_' . $rand_num,
                                'name' => 'location_location4',
                                'options' => $location_location4,
                                'force_std' => $loc_location4,
                                'ext_attr' => ' data-randid="' . $rand_num . '"' . ($require_full_addr == true ? ' required="required"' : ''),
                            );
                            $jobsearch_form_fields->select_field($field_params);
                            ?>
                            <span class="jobsearch-field-loader location_location4_<?php echo absint($rand_num); ?>"></span>
                        </div>
                    </li>
                    <?php
                }
                if ($allow_full_address == 'yes') {
                    $full_addr_title = esc_html__('Full Address', 'wp-jobsearch');
                    //
                    if ($require_full_addr == true) {
                        $full_addr_title = esc_html__('Full Address *', 'wp-jobsearch');
                    }
                    ?>
                    <li class="jobsearch-column-10">
                        <label><?php echo ($full_addr_title) ?></label>
                        <?php
                        $field_params = array(
                            'id' => 'jobsearch_location_address_' . $rand_num,
                            'name' => 'location_address',
                            'classes' => '' . ($require_full_addr == true ? ' jobsearch-req-field' : ''),
                            'force_std' => $loc_address,
                        );
                        $jobsearch_form_fields->input_field($field_params);
                        ?>
                    </li>
                    <?php
                }
                if ($allow_location_map == 'yes') {
                    ?>
                    <li class="jobsearch-column-2">
                        <button id="jobsearch-findmap-<?php echo absint($rand_num); ?>" class="jobsearch-findmap-btn"><?php esc_html_e('Find on Map', 'wp-jobsearch') ?></button>
                    </li>
                    <li class="jobsearch-column-4">
                        <label><?php esc_html_e('Latitude', 'wp-jobsearch') ?></label>
                        <?php
                        $field_params = array(
                            'id' => 'jobsearch_location_lat_' . $rand_num,
                            'name' => 'location_lat',
                            'force_std' => $loc_lat,
                        );
                        $jobsearch_form_fields->input_field($field_params);
                        ?>
                    </li>
                    <li class="jobsearch-column-4">
                        <label><?php esc_html_e('Longitude', 'wp-jobsearch') ?></label>
                        <?php
                        $field_params = array(
                            'id' => 'jobsearch_location_lng_' . $rand_num,
                            'name' => 'location_lng',
                            'force_std' => $loc_lng,
                        );
                        $jobsearch_form_fields->input_field($field_params);
                        ?>
                    </li>
                    <li class="jobsearch-column-4">
                        <label><?php esc_html_e('Zoom', 'wp-jobsearch') ?></label>
                        <?php
                        $field_params = array(
                            'id' => 'jobsearch_location_zoom_' . $rand_num,
                            'name' => 'location_zoom',
                            'force_std' => $loc_zoom,
                        );
                        $jobsearch_form_fields->input_field($field_params);
                        ?>
                    </li>
                    <li class="jobsearch-column-12">
                        <div class="jobsearch-profile-map"><div id="jobsearch-map-<?php echo absint($rand_num); ?>" style="width: 100%; height: <?php echo esc_html($map_height) ?>px;"></div></div>
                        <span class="jobsearch-short-message"><?php esc_html_e('For the precise location, you can drag and drop the pin.', 'wp-jobsearch') ?></span>
                    </li>
                    <?php
                }
                ?>
            </ul>
        </div>
        <?php
        if ($allow_location_map == 'yes') {
            ?>
            <script>
                var map;
                var markers = [];
                jQuery(document).ready(function () {
                    function jobsearch_map_autocomplete_fields_<?php echo ($rand_num) ?>() {
                        var autocomplete_input = document.getElementById('jobsearch_location_address_<?php echo ($rand_num) ?>');
                        var autocomplete = new google.maps.places.Autocomplete(autocomplete_input);
                    }
                    google.maps.event.addDomListener(window, 'load', jobsearch_map_autocomplete_fields_<?php echo ($rand_num) ?>);
            <?php
            if ($loc_lat != '' && $loc_lng != '' && $loc_zoom != '') {
                ?>
                        function initMap<?php echo ($rand_num) ?>() {
                            var myLatLng = {lat: <?php echo esc_js($loc_lat) ?>, lng: <?php echo esc_js($loc_lng) ?>};
                            map = new google.maps.Map(document.getElementById('jobsearch-map-<?php echo absint($rand_num); ?>'), {
                                zoom: <?php echo esc_js($loc_zoom) ?>,
                                center: myLatLng,
                                streetViewControl: false,
                                scrollwheel: false,
                                mapTypeControl: false,
                            });
                <?php
                if ($map_styles != '') {
                    $map_styles = stripslashes($map_styles);
                    $map_styles = preg_replace('/\s+/', ' ', trim($map_styles));
                    ?>
                                var styles = '<?php echo ($map_styles) ?>';
                                if (styles != '') {
                                    styles = jQuery.parseJSON(styles);
                                    var styledMap = new google.maps.StyledMapType(
                                            styles,
                                            {name: 'Styled Map'}
                                    );
                                    map.mapTypes.set('map_style', styledMap);
                                    map.setMapTypeId('map_style');
                                }
                    <?php
                }
                ?>

                            var marker = new google.maps.Marker({
                                position: myLatLng,
                                map: map,
                                draggable: true,
                                title: '',
                                icon: '<?php echo esc_js($marker_image) ?>',
                            });

                            markers.push(marker);

                            google.maps.event.addListener(map, 'zoom_changed', function () {
                                var zoom_lvl = map.getZoom();
                                document.getElementById("jobsearch_location_zoom_<?php echo absint($rand_num); ?>").value = zoom_lvl;
                            });
                            google.maps.event.addListener(marker, 'dragend', function (event) {
                                document.getElementById("jobsearch_location_lat_<?php echo absint($rand_num); ?>").value = this.getPosition().lat();
                                document.getElementById("jobsearch_location_lng_<?php echo absint($rand_num); ?>").value = this.getPosition().lng();
                            });
                        }

                        google.maps.event.addDomListener(window, 'load', initMap<?php echo ($rand_num) ?>);

                        function find_on_map<?php echo ($rand_num) ?>(_this) {
                            var $ = jQuery;
                            var geocoder = new google.maps.Geocoder();
                            var addres = _this.val();
                            var lat_con = $('#jobsearch_location_lat_<?php echo ($rand_num) ?>');
                            var lng_con = $('#jobsearch_location_lng_<?php echo ($rand_num) ?>');
                            geocoder.geocode({address: addres}, function (results, status) {
                                if (status == google.maps.GeocoderStatus.OK) {
                                    var new_latitude = results[0].geometry.location.lat();
                                    var new_longitude = results[0].geometry.location.lng();
                                    lat_con.val(new_latitude);
                                    lng_con.val(new_longitude);
                                    map.setCenter(results[0].geometry.location);//center the map over the result

                                    // clear markers
                                    for (var i = 0; i < markers.length; i++) {
                                        markers[i].setMap(null);
                                    }
                                    //

                                    //place a marker at the location
                                    var marker = new google.maps.Marker({
                                        map: map,
                                        position: results[0].geometry.location,
                                        draggable: true,
                                        title: '',
                                        icon: '<?php echo esc_js($marker_image) ?>',
                                    });

                                    markers.push(marker);

                                    google.maps.event.addListener(marker, 'dragend', function (event) {
                                        document.getElementById("jobsearch_location_lat_<?php echo ($rand_num) ?>").value = this.getPosition().lat();
                                        document.getElementById("jobsearch_location_lng_<?php echo ($rand_num) ?>").value = this.getPosition().lng();
                                    });
                                }
                            });
                        }

                        jQuery(document).on('change', '#jobsearch_location_address_<?php echo ($rand_num) ?>', function () {
                            find_on_map<?php echo ($rand_num) ?>($(this));
                        });

                        jQuery(document).on('click', '#jobsearch-findmap-<?php echo absint($rand_num); ?>', function (e) {
                            e.preventDefault();
                            find_on_map<?php echo ($rand_num) ?>($('#jobsearch_location_address_<?php echo ($rand_num) ?>'));
                            false;
                        });
                <?php
            }
// load next location
//if ($loc_location1 != '') {
            ?>
                    jQuery('.location_location1').trigger('change');
            <?php
//}
            ?>
                });
                // load state

            </script>
            <?php
        }
    }

    public function jobsearch_location_load_location2_data_callback() {
        $nextfieldelement = $_POST['nextfieldelement'];
        $nextfieldval = $_POST['nextfieldval'];
        $html .= "<option value=\"\">" . $nextfieldelement . "</option>" . "\n";
        if (isset($_POST['location_location']) && $_POST['location_location'] != '') {
            $location = $_POST['location_location'];
            $term = get_term_by('slug', $location, 'job-location');
            if (!empty($term)) {

                $location_obj = get_terms('job-location', array(
                    'orderby' => 'name',
                    'order' => 'ASC',
                    'hide_empty' => 0,
                    'parent' => $term->term_id,
                ));
                if (!empty($location_obj)) {
                    foreach ($location_obj as $country_arr) {
                        $selected = $country_arr->slug == $nextfieldval ? ' selected="selected"' : '';
                        $html .= "<option{$selected} value=\"{$country_arr->slug}\">{$country_arr->name}</option>" . "\n";
                    }
                }
            }
        }
        echo json_encode(array('html' => $html));

        wp_die();
    }

    public function jobsearch_location_plugin_option_fields($sections) {
        //$sections = array(); // Delete this if you want to keep original sections!
        $sections[] = array(
            'title' => __('Location Settings', 'wp-jobsearch'),
            'id' => 'location-settings',
            'desc' => __('Location fields setting.', 'wp-jobsearch'),
            'icon' => 'el el-map-marker',
            'fields' => array(
                array(
                    'id' => 'location-settings-section',
                    'type' => 'section',
                    'title' => __('Location fields settings', 'wp-jobsearch'),
                    'subtitle' => '',
                    'indent' => true,
                ),
                array(
                    'id' => 'jobsearch-location-required-fields-count',
                    'type' => 'select',
                    'title' => __('Required Fields', 'wp-jobsearch'),
                    'subtitle' => __('Select Required fields count for location.', 'wp-jobsearch'),
                    'desc' => '',
                    'options' => array(
                        'all' => __('All Fields', 'wp-jobsearch'),
                        '1' => __('One Field only', 'wp-jobsearch'),
                        '2' => __('Two Fields', 'wp-jobsearch'),
                        '3' => __('Three Fields', 'wp-jobsearch'),
                    ),
                    'default' => 'all',
                ),
                array(
                    'id' => 'jobsearch-location-label-location1',
                    'type' => 'text',
                    'title' => __('First Field', 'wp-jobsearch'),
                    'subtitle' => __('First location field label', 'wp-jobsearch'),
                    'desc' => '',
                    'default' => 'Country',
                ),
                array(
                    'id' => 'jobsearch-location-label-location2',
                    'type' => 'text',
                    'title' => __('Second Field', 'wp-jobsearch'),
                    'subtitle' => __('Second location field label', 'wp-jobsearch'),
                    'desc' => '',
                    'default' => 'State',
                ),
                array(
                    'id' => 'jobsearch-location-label-location3',
                    'type' => 'text',
                    'title' => __('Third Field', 'wp-jobsearch'),
                    'subtitle' => __('Third location field label', 'wp-jobsearch'),
                    'desc' => '',
                    'default' => 'Region',
                ),
                array(
                    'id' => 'jobsearch-location-label-location4',
                    'type' => 'text',
                    'title' => __('Fourth Field', 'wp-jobsearch'),
                    'subtitle' => __('Fourth location field label', 'wp-jobsearch'),
                    'desc' => '',
                    'default' => 'City',
                ),
                array(
                    'id' => 'location-allow-full-address',
                    'type' => 'button_set',
                    'title' => __('Allow Full Address', 'wp-jobsearch'),
                    'subtitle' => __('Allow users to enter full address.', 'wp-jobsearch'),
                    'desc' => '',
                    'options' => array(
                        'yes' => __('Yes', 'wp-jobsearch'),
                        'no' => __('No', 'wp-jobsearch'),
                    ),
                    'default' => 'yes',
                ),
                array(
                    'id' => 'jobsearch-location-default-address',
                    'type' => 'text',
                    'required' => array('location-allow-full-address', 'equals', 'yes'),
                    'title' => __('Default Address', 'wp-jobsearch'),
                    'subtitle' => __('Set Default Locaion address for your site.', 'wp-jobsearch'),
                    'desc' => '',
                    'default' => '',
                ),
                array(
                    'id' => 'location-allow-map',
                    'type' => 'button_set',
                    'title' => __('Allow Map', 'wp-jobsearch'),
                    'subtitle' => '',
                    'desc' => '',
                    'options' => array(
                        'yes' => __('Yes', 'wp-jobsearch'),
                        'no' => __('No', 'wp-jobsearch'),
                    ),
                    'default' => 'yes',
                ),
                array(
                    'id' => 'jobsearch-location-map-zoom',
                    'type' => 'text',
                    'required' => array('location-allow-map', 'equals', 'yes'),
                    'title' => __('Default Map Zoom', 'wp-jobsearch'),
                    'subtitle' => __('Set Default Zoom Level for Map.', 'wp-jobsearch'),
                    'desc' => '',
                    'default' => '12',
                ),
                array(
                    'id' => 'jobsearch-location-map-style',
                    'type' => 'textarea',
                    'title' => __('Map Style', 'wp-jobsearch'),
                    'required' => array('location-allow-map', 'equals', 'yes'),
                    'subtitle' => sprintf(__('You can get all map styles from <a href="%s" target="_blank">here</a>', 'wp-jobsearch'), 'https://snazzymaps.com/'),
                    'desc' => '',
                    'default' => '',
                ),
            ),
        );
        // echo '<pre>'; print_r($sections);echo '</pre>'; 
        return $sections;
    }

}

// class Jobsearch_Location 
$Jobsearch_Location_obj = new Jobsearch_Location();
global $Jobsearch_Location_obj;

