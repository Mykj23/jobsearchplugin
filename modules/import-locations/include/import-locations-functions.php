<?php
// Direct access not allowed.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * JobSearch_Import_Locs_functions
 */
class JobSearch_Import_Locs_functions {

    public function __construct() {
        add_action('admin_menu', array($this, 'import_page_menu'));
        add_action('wp_ajax_jobsearch_import_locations_in_bulk', array($this, 'import_locations_taxonomies'));
        //
        add_action('wp_ajax_jobsearch_import_locations_states_cities', array($this, 'import_state_cities'));
    }

    public function import_page_menu() {

        add_submenu_page('edit.php?post_type=job', esc_html__('Import Locations', 'wp-jobsearch'), esc_html__('Import Location', 'wp-jobsearch'), 'manage_options', 'jobsearch-import-locs', array($this, 'import_locations'));
    }

    public function import_locations() {
        global $wp_filesystem;
        wp_enqueue_script('jobsearch-import-locs');

        require_once ABSPATH . '/wp-admin/includes/file.php';

        if (false === ($creds = request_filesystem_credentials(wp_nonce_url('post.php'), '', false, false, array()) )) {
            return true;
        }
        if (!WP_Filesystem($creds)) {
            request_filesystem_credentials(wp_nonce_url('post.php'), '', true, false, array());
            return true;
        }

        $countries_file = jobsearch_plugin_get_path('modules/import-locations/data/countries.json');
        $get_json_data = $wp_filesystem->get_contents($countries_file);

        $countries_data = json_decode($get_json_data, true);
        ?>
        <div id="wrapper" class="jobsearch-post-settings jobsearch-locs-import-sec">
            <h2><?php esc_html_e('Import Locations', 'wp-jobsearch') ?></h2>
            <div class="load-all-contries">

                <div class="import-locs-btns-con">

                    <div class="import-all-countries-hold">
                        <h1><?php esc_html_e('Import All World Locations', 'wp-jobsearch') ?></h1>
                        <a href="javascript:void(0);" class="import-locsall-data-btn"><?php esc_html_e('Import all countries data', 'wp-jobsearch') ?></a>
                    </div>

                    <div class="import-only-country-hold">
                        <h1><?php esc_html_e('Import Specific Country Locations', 'wp-jobsearch') ?></h1>
                        <div class="import-selc-country-hold">
                            <label><?php esc_html_e('Countries', 'wp-jobsearch') ?></label>
                            <select id="load-select-countries">
                                <option value=""><?php esc_html_e('Select Country', 'wp-jobsearch') ?></option>
                                <?php
                                if (isset($countries_data['countries']) && !empty($countries_data['countries'])) {
                                    foreach ($countries_data['countries'] as $country_data) {
                                        ?>
                                        <option value="<?php echo ($country_data['id']) ?>"><?php echo ($country_data['name']) ?></option>
                                        <?php
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div class="clearfix"></div>
                        <a href="javascript:void(0);" class="import-locscontry-data-btn"><?php esc_html_e('Import selected country data', 'wp-jobsearch') ?></a>
                    </div>
                </div>
                <span class="loader-con"></span>
                <div class="all-breakdown-con"></div>
                <div class="msge-con"></div>
            </div>
        </div>
        <?php
    }

    public function import_state_cities() {
        global $wp_filesystem;
        require_once ABSPATH . '/wp-admin/includes/file.php';

        if (false === ($creds = request_filesystem_credentials(wp_nonce_url('post.php'), '', false, false, array()) )) {
            return true;
        }
        if (!WP_Filesystem($creds)) {
            request_filesystem_credentials(wp_nonce_url('post.php'), '', true, false, array());
            return true;
        }

        //
        $countries_file = jobsearch_plugin_get_path('modules/import-locations/data/countries.json');
        $get_json_data = $wp_filesystem->get_contents($countries_file);
        $countries_data = json_decode($get_json_data, true);
        $all_countries_data = isset($countries_data['countries']) ? $countries_data['countries'] : array();

        //
        $states_file = jobsearch_plugin_get_path('modules/import-locations/data/states.json');
        $get_json_data = $wp_filesystem->get_contents($states_file);
        $states_data = json_decode($get_json_data, true);
        $all_states_data = isset($states_data['states']) ? $states_data['states'] : array();

        //
        $cities_file = jobsearch_plugin_get_path('modules/import-locations/data/cities.json');
        $get_json_data = $wp_filesystem->get_contents($cities_file);
        $cities_data = json_decode($get_json_data, true);

        $country_id = isset($_POST['country_id']) ? $_POST['country_id'] : '';
        $state_id = isset($_POST['state_id']) ? $_POST['state_id'] : '';

        if ($country_id > 0 && $state_id > 0) {
            //
            $_country_key = array_search($country_id, array_column($all_countries_data, 'id'));
            $country_name = isset($all_countries_data[$_country_key]['name']) ? $all_countries_data[$_country_key]['name'] : '';

            $country_term = term_exists($country_name, 'job-location', 0);
            if (isset($country_term['term_id'])) {
                $country_term = $country_term;
            } else {
                $country_term = wp_insert_term($country_name, 'job-location');
            }
            //

            $_state_key = array_search($state_id, array_column($all_states_data, 'id'));
            $state_name = isset($all_states_data[$_state_key]['name']) ? $all_states_data[$_state_key]['name'] : '';
            if ($state_name != '') {
                $state_term = term_exists($state_name, 'job-location', $country_term['term_id']);
                if (isset($state_term['term_id'])) {
                    $state_term = $state_term;
                } else {
                    $state_term = wp_insert_term($state_name, 'job-location', array('parent' => $country_term['term_id']));
                }
                //
                if (isset($cities_data['cities']) && !empty($cities_data['cities'])) {
                    foreach ($cities_data['cities'] as $city_data) {
                        if ($city_data['state_id'] == $state_id) {
                            $city_name = $city_data['name'];
                            $city_id = $city_data['id'];
                            //
                            $city_term = term_exists($city_name, 'job-location', $state_term['term_id']);
                            if (isset($city_term['term_id'])) {
                                $city_term = $city_term;
                            } else {
                                $city_term = wp_insert_term($city_name, 'job-location', array('parent' => $state_term['term_id']));
                            }
                            //
                        }
                    }
                }
                //
            }
            //
            $msg = '';
            $next_state_id = $state_id + 1;
            $next_state_key = array_search($next_state_id, array_column($all_states_data, 'id'));
            $next_state_country_id = isset($all_states_data[$next_state_key]['country_id']) ? $all_states_data[$next_state_key]['country_id'] : 0;
            if ($next_state_country_id == $country_id) {
                $next_state_name = isset($all_states_data[$next_state_key]['name']) ? $all_states_data[$next_state_key]['name'] : 0;
                $msg = '<script>jobseacrh_import_state_cities(\'' . $country_id . '\', \'' . $next_state_id . '\', \'' . $next_state_name . '\');</script>';
            }
            echo json_encode(array('msg' => $msg));
            die;
        }
        die;
    }

    public function import_locations_taxonomies() {
        global $wp_filesystem;

//        $terms = get_terms(array(
//            'taxonomy' => 'job-location',
//            'hide_empty' => false,
//        ));
//        foreach ($terms as $term) {
//            wp_delete_term( $term->term_id, 'job-location' );
//        }
//        die;

        require_once ABSPATH . '/wp-admin/includes/file.php';

        if (false === ($creds = request_filesystem_credentials(wp_nonce_url('post.php'), '', false, false, array()) )) {
            return true;
        }
        if (!WP_Filesystem($creds)) {
            request_filesystem_credentials(wp_nonce_url('post.php'), '', true, false, array());
            return true;
        }
        $all_countries = array();
        $countries_file = jobsearch_plugin_get_path('modules/import-locations/data/countries.json');
        $get_json_data = $wp_filesystem->get_contents($countries_file);
        $countries_data = json_decode($get_json_data, true);
        if (isset($countries_data['countries']) && !empty($countries_data['countries'])) {
            foreach ($countries_data['countries'] as $country_data) {
                $all_countries[$country_data['id']] = $country_data['name'];
            }
        }

        $states_data = array();
        $states_file = jobsearch_plugin_get_path('modules/import-locations/data/states.json');
        $get_json_data = $wp_filesystem->get_contents($states_file);
        $states_data = json_decode($get_json_data, true);

        $cities_data = array();
        $cities_file = jobsearch_plugin_get_path('modules/import-locations/data/cities.json');
        $get_json_data = $wp_filesystem->get_contents($cities_file);
        $cities_data = json_decode($get_json_data, true);

        $sel_country = isset($_POST['sel_country']) ? $_POST['sel_country'] : '';
        if ($sel_country == 'all') {
            $sel_country = '';
        }
        if ($sel_country != '' && array_key_exists($sel_country, $all_countries)) {
            $sel_country_name = $all_countries[$sel_country];
            $country_name = $sel_country_name;
            $country_id = $sel_country;
            $country_term = term_exists($country_name, 'job-location', 0);
            if (isset($country_term['term_id'])) {
                $country_term = $country_term;
            } else {
                $country_term = wp_insert_term($country_name, 'job-location');
            }
            if (isset($states_data['states']) && !empty($states_data['states'])) {
                $all_states_data = $states_data['states'];
                $states_counter = 1;
                foreach ($states_data['states'] as $state_data) {
                    if ($state_data['country_id'] == $country_id) {
                        $state_name = $state_data['name'];
                        $state_id = $state_data['id'];
                        //
                        $state_term = term_exists($state_name, 'job-location', $country_term['term_id']);
                        if (isset($state_term['term_id'])) {
                            $state_term = $state_term;
                        } else {
                            $state_term = wp_insert_term($state_name, 'job-location', array('parent' => $country_term['term_id']));
                        }
                        //
                        if (isset($cities_data['cities']) && !empty($cities_data['cities'])) {
                            foreach ($cities_data['cities'] as $city_data) {
                                if ($city_data['state_id'] == $state_id) {
                                    $city_name = $city_data['name'];
                                    $city_id = $city_data['id'];
                                    //
                                    $city_term = term_exists($city_name, 'job-location', $state_term['term_id']);
                                    if (isset($city_term['term_id'])) {
                                        $city_term = $city_term;
                                    } else {
                                        $city_term = wp_insert_term($city_name, 'job-location', array('parent' => $state_term['term_id']));
                                    }
                                    //
                                }
                            }
                        }
                        //
                        $states_counter++;
                        if ($states_counter > 1) {
                            break;
                        }
                    }
                }
                $msg = '';
                $next_state_id = $state_id + 1;
                $next_state_key = array_search($next_state_id, array_column($all_states_data, 'id'));
                $next_state_country_id = isset($all_states_data[$next_state_key]['country_id']) ? $all_states_data[$next_state_key]['country_id'] : 0;
                if ($next_state_country_id == $country_id) {
                    $next_state_name = isset($all_states_data[$next_state_key]['name']) ? $all_states_data[$next_state_key]['name'] : 0;
                    $msg = '<script>jobseacrh_import_state_cities(\'' . $country_id . '\', \'' . $next_state_id . '\', \'' . $next_state_name . '\');</script>';
                }
                echo json_encode(array('msg' => $msg, 'country_id' => $country_id, 'state_name' => $state_name, 'state_id' => $state_id));
                die;
            }
            echo json_encode(array('msg' => esc_html__('There is some problem in import locations.', 'wp-jobsearch')));
            die;
        } else {
            if (isset($countries_data['countries']) && !empty($countries_data['countries'])) {

                foreach ($countries_data['countries'] as $country_data) {
                    $country_name = $country_data['name'];
                    $country_id = $country_data['id'];
                    $country_term = term_exists($country_name, 'job-location', 0);
                    if (isset($country_term['term_id'])) {
                        $country_term = $country_term;
                    } else {
                        $country_term = wp_insert_term($country_name, 'job-location');
                    }
                    if (isset($states_data['states']) && !empty($states_data['states'])) {
                        foreach ($states_data['states'] as $state_data) {
                            if ($state_data['country_id'] == $country_id) {
                                $state_name = $state_data['name'];
                                $state_id = $state_data['id'];
                                //
                                $state_term = term_exists($state_name, 'job-location', $country_term['term_id']);
                                if (isset($state_term['term_id'])) {
                                    $state_term = $state_term;
                                } else {
                                    $state_term = wp_insert_term($state_name, 'job-location', array('parent' => $country_term['term_id']));
                                }
                                //
                                if (isset($cities_data['cities']) && !empty($cities_data['cities'])) {
                                    foreach ($cities_data['cities'] as $city_data) {
                                        if ($city_data['state_id'] == $state_id) {
                                            $city_name = $city_data['name'];
                                            $city_id = $city_data['id'];
                                            //
                                            $city_term = term_exists($city_name, 'job-location', $state_term['term_id']);
                                            if (isset($city_term['term_id'])) {
                                                $city_term = $city_term;
                                            } else {
                                                $city_term = wp_insert_term($city_name, 'job-location', array('parent' => $state_term['term_id']));
                                            }
                                            //
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                echo json_encode(array('msg' => esc_html__('All Locations imported successfully.', 'wp-jobsearch')));
                die;
            }
        }
        echo json_encode(array('msg' => esc_html__('There is some problem in import locations.', 'wp-jobsearch')));
        die;
    }

}

$JobSearch_Import_Locs_functions_obj = new JobSearch_Import_Locs_functions();
